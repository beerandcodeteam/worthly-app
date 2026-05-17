#!/bin/bash
#
# ralph.sh
#
# Orquestrador que le docs/project-phases.md, quebra em fases,
# e alimenta cada uma ao Codex CLI ou Claude Code para implementacao automatica.
#
# Uso:
#   chmod +x ralph.sh
#   ./ralph.sh [--engine codex|claude] [--start-at N] [caminho-do-arquivo]
#
# Exemplos:
#   ./ralph.sh                          # default: codex
#   ./ralph.sh --engine claude          # usa Claude Code
#   ./ralph.sh --engine codex docs/project-phases.md
#   ./ralph.sh --start-at 6             # comeca da "Phase 6" (marca fases com numero < 6 como done)
#
# Pre-requisitos:
#   - Codex: npm install -g @openai/codex + OPENAI_API_KEY
#   - Claude: npm install -g @anthropic-ai/claude-code + ANTHROPIC_API_KEY
#   - Estar na raiz do projeto Laravel (dentro de um repo git)

set -euo pipefail

ENGINE="codex"
INPUT_FILE=""
START_AT=""

while [[ $# -gt 0 ]]; do
  case "$1" in
    --engine)
      ENGINE="$2"
      shift 2
      ;;
    --engine=*)
      ENGINE="${1#*=}"
      shift
      ;;
    --start-at)
      START_AT="$2"
      shift 2
      ;;
    --start-at=*)
      START_AT="${1#*=}"
      shift
      ;;
    *)
      INPUT_FILE="$1"
      shift
      ;;
  esac
done

INPUT_FILE="${INPUT_FILE:-docs/project-phases.md}"

if [[ "$ENGINE" != "codex" && "$ENGINE" != "claude" ]]; then
  echo "Engine invalida: $ENGINE. Use 'codex' ou 'claude'."
  exit 1
fi

if [ -n "$START_AT" ] && ! [[ "$START_AT" =~ ^(0|[1-9][0-9]*)$ ]]; then
  echo "Valor invalido para --start-at: '$START_AT'. Use um inteiro nao-negativo (ex: --start-at 6)."
  exit 1
fi
PHASES_DIR=".phases"
LOG_DIR=".phases/logs"
PROMPT_DIR=".phases/prompts"
MANIFEST="$PHASES_DIR/manifest.txt"
PROGRESS_FILE="$PHASES_DIR/.progress"
MAX_RETRIES=2

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log()     { echo -e "${BLUE}[$(date '+%H:%M:%S')]${NC} $1"; }
success() { echo -e "${GREEN}[$(date '+%H:%M:%S')] $1${NC}"; }
warn()    { echo -e "${YELLOW}[$(date '+%H:%M:%S')] $1${NC}"; }
fail()    { echo -e "${RED}[$(date '+%H:%M:%S')] $1${NC}"; }

format_duration() {
  local total_seconds=$1
  local hours=$((total_seconds / 3600))
  local minutes=$(( (total_seconds % 3600) / 60 ))
  local seconds=$((total_seconds % 60))

  if [ $hours -gt 0 ]; then
    printf "%dh %dm %ds" $hours $minutes $seconds
  elif [ $minutes -gt 0 ]; then
    printf "%dm %ds" $minutes $seconds
  else
    printf "%ds" $seconds
  fi
}

preflight_checks() {
  if [[ "$ENGINE" == "codex" ]]; then
    if ! command -v codex &> /dev/null; then
      fail "codex CLI nao encontrado. Instale com: npm install -g @openai/codex"
      exit 1
    fi
  elif [[ "$ENGINE" == "claude" ]]; then
    if ! command -v claude &> /dev/null; then
      fail "Claude Code CLI nao encontrado. Instale com: npm install -g @anthropic-ai/claude-code"
      exit 1
    fi
  fi

  if [ ! -f "$INPUT_FILE" ]; then
    fail "Arquivo nao encontrado: $INPUT_FILE"
    exit 1
  fi

  if [ ! -f "artisan" ]; then
    warn "Nao parece ser a raiz de um projeto Laravel (artisan nao encontrado)"
    read -p "Continuar mesmo assim? (y/N) " -n 1 -r
    echo
    [[ $REPLY =~ ^[Yy]$ ]] || exit 1
  fi

  if ! git rev-parse --is-inside-work-tree &> /dev/null 2>&1; then
    fail "Requer um repositorio git."
    exit 1
  fi

  success "Pre-checks OK (engine: $ENGINE)"
}

split_phases() {
  log "Quebrando $INPUT_FILE em fases..."

  rm -rf "$PHASES_DIR"
  mkdir -p "$PHASES_DIR" "$LOG_DIR" "$PROMPT_DIR"
  > "$MANIFEST"

  local current_file=""
  local phase_count=0

  while IFS= read -r line || [ -n "$line" ]; do
    if [[ "$line" =~ ^##[[:space:]]+(Phase[[:space:]]+[0-9]+[^#]*) ]]; then
      phase_count=$((phase_count + 1))

      local raw_title="${BASH_REMATCH[1]}"
      raw_title="$(echo "$raw_title" | sed 's/[[:space:]]*$//')"

      local slug
      slug=$(echo "$raw_title" \
        | tr '[:upper:]' '[:lower:]' \
        | sed 's/phase[[:space:]]*/phase-/' \
        | sed 's/[^a-z0-9-]/-/g' \
        | sed 's/--*/-/g' \
        | sed 's/-$//' \
        | sed 's/^-//')
      slug=$(echo "$slug" | sed -E 's/phase-([0-9])$/phase-0\1/' | sed -E 's/phase-([0-9])-/phase-0\1-/')

      current_file="$PHASES_DIR/${slug}.md"
      echo "$line" > "$current_file"
      echo "${slug}.md|${raw_title}" >> "$MANIFEST"
      continue
    fi

    if [ -n "$current_file" ]; then
      echo "$line" >> "$current_file"
    fi
  done < "$INPUT_FILE"

  success "$phase_count fases extraidas"

  if [ -n "$START_AT" ]; then
    > "$PROGRESS_FILE"
    local skipped=0
    local found_target=false
    while IFS="|" read -r file title; do
      local phase_num
      phase_num=$(echo "$title" | sed -nE 's/^Phase[[:space:]]+([0-9]+).*/\1/p')

      if [ -z "$phase_num" ]; then
        continue
      fi

      if [ "$phase_num" -lt "$START_AT" ]; then
        echo "$file" >> "$PROGRESS_FILE"
        skipped=$((skipped + 1))
      else
        found_target=true
      fi
    done < "$MANIFEST"

    if ! $found_target; then
      fail "--start-at $START_AT nao encontrou nenhuma fase com numero >= $START_AT."
      exit 1
    fi

    success "Pulando $skipped fase(s) com numero < $START_AT (marcadas como done). Comecando na Phase $START_AT."
  fi
}

build_prompt_file() {
  local phase_file="$1"
  local prompt_file="$PROMPT_DIR/${phase_file%.md}.txt"

  cat > "$prompt_file" <<PROMPT
Voce e um desenvolvedor Laravel senior.

## Stack do projeto
- Laravel 13, PHP 8.5
- Pest PHP 4 (testes)
- NativePhp
- Livewire 4

## Arquivos de referencia importantes
- Voce tem acesso ao mem0 para entender o contexto do projeto e gerar insights sobre o projeto
- docs/project-phases.md — plano completo de fases
- docs/user-stories.md — user stories
- docs/project-description.md — descricao geral
- docs/worthly-handoff — designs de referencia (HTML + screenshots)

## Sua tarefa agora
Implemente COMPLETAMENTE a fase descrita abaixo.

Para cada item:
1. Implemente o codigo completo (nao deixe TODOs ou placeholders)
2. Crie os testes listados
3. Rode os testes com o subagent
4. Se um teste falhar, corrija o codigo e rode novamente
5. So passe pro proximo item quando os testes passarem

## Regras obrigatorias
- LEIA o CLAUDE.md antes de comecar — ele contem as convencoes do projeto
- Nomes de classes, arquivos e metodos devem seguir EXATAMENTE o que esta descrito
- Nao pule nenhum item marcado com [ ]
- Ao final utilize o subagent de testes para validar se esta tudo correto

## Fase a implementar
$(cat "$PHASES_DIR/$phase_file")
PROMPT

  echo "$prompt_file"
}

build_retry_prompt_file() {
  local phase_file="$1"
  local test_output="$2"
  local prompt_file="$PROMPT_DIR/${phase_file%.md}-retry.txt"

  cat > "$prompt_file" <<PROMPT
Os testes falharam apos a implementacao anterior. Corrija os erros.

Saida dos testes:
\`\`\`
$test_output
\`\`\`

Corrija o codigo para que todos os testes passem. Rode os testes novamente apos cada correcao.
PROMPT

  echo "$prompt_file"
}

run_engine() {
  local prompt_file="$1"
  local log_file="$2"

  # Exporta contexto da fase atual para os hooks (notify-n8n.sh usa quando .message vem vazio)
  export RALPH_ENGINE="$ENGINE"
  export RALPH_PHASE_TITLE="${RALPH_PHASE_TITLE:-}"
  export RALPH_PHASE_NUM="${RALPH_PHASE_NUM:-}"
  export RALPH_PHASE_TOTAL="${RALPH_PHASE_TOTAL:-}"
  export RALPH_PHASE_ATTEMPT="${RALPH_PHASE_ATTEMPT:-1}"
  export RALPH_PHASE_MAX_ATTEMPTS="$((MAX_RETRIES + 1))"

  if [[ "$ENGINE" == "codex" ]]; then
    cat "$prompt_file" | codex exec --sandbox danger-full-access - 2>&1 | tee "$log_file"
  elif [[ "$ENGINE" == "claude" ]]; then
    env -u CLAUDECODE claude --dangerously-skip-permissions -p "$(cat "$prompt_file")" --output-format text --verbose 2>&1 | tee "$log_file"
  fi
}

run_phase() {
  local phase_file="$1"
  local phase_title="$2"
  local phase_num="$3"
  local total_phases="$4"
  local log_file="$LOG_DIR/${phase_file%.md}.log"
  local phase_start
  phase_start=$(date +%s)

  export RALPH_PHASE_TITLE="$phase_title"
  export RALPH_PHASE_NUM="$phase_num"
  export RALPH_PHASE_TOTAL="$total_phases"

  echo ""
  log "[$phase_num/$total_phases] $phase_title"

  local attempt=0
  local phase_success=false

  while [ $attempt -le $MAX_RETRIES ]; do
    attempt=$((attempt + 1))
    export RALPH_PHASE_ATTEMPT="$attempt"

    if [ $attempt -gt 1 ]; then
      warn "Tentativa $attempt/$((MAX_RETRIES + 1))..."
    fi

    local prompt_file
    if [ $attempt -eq 1 ]; then
      prompt_file=$(build_prompt_file "$phase_file")
    fi

    if run_engine "$prompt_file" "$log_file"; then
      phase_success=true
      break
    else
      fail "$ENGINE retornou erro"
      if [ $attempt -le $MAX_RETRIES ]; then
        local test_output
        test_output=$(tail -30 "$log_file" 2>/dev/null || echo "Sem output disponivel")
        prompt_file=$(build_retry_prompt_file "$phase_file" "$test_output")
      fi
    fi
  done

  local phase_end
  phase_end=$(date +%s)
  local phase_duration=$((phase_end - phase_start))

  if $phase_success; then
    success "$phase_title — COMPLETA ($(format_duration $phase_duration))"

    if git rev-parse --is-inside-work-tree &> /dev/null 2>&1; then
      git add -A
      git commit -m "feat: $phase_title" --allow-empty
      log "Commit criado no git"
    fi

    echo "$phase_file" >> "$PROGRESS_FILE"
    return 0
  else
    fail "$phase_title — FALHOU apos $((MAX_RETRIES + 1)) tentativas ($(format_duration $phase_duration))"
    fail "Log disponivel em: $log_file"
    return 1
  fi
}

is_phase_done() {
  local phase_file="$1"
  [ -f "$PROGRESS_FILE" ] && grep -qF "$phase_file" "$PROGRESS_FILE"
}

main() {
  preflight_checks
  split_phases

  local total_phases
  total_phases=$(wc -l < "$MANIFEST")

  echo ""
  log "$total_phases fases para implementar"
  echo ""

  local num=0
  while IFS="|" read -r file title; do
    num=$((num + 1))
    if is_phase_done "$file"; then
      echo -e "  ${GREEN}[$num] $title (ja completada)${NC}"
    else
      echo -e "  ${YELLOW}[$num] $title${NC}"
    fi
  done < "$MANIFEST"

  echo ""
  read -p "Iniciar implementacao? (Y/n) " -n 1 -r
  echo
  [[ $REPLY =~ ^[Nn]$ ]] && exit 0

  local start_time
  start_time=$(date +%s)
  log "Inicio: $(date '+%d/%m/%Y %H:%M:%S')"

  local current=0
  local failed_phases=()
  local skipped_phases=()
  local completed_phases=()

  while IFS="|" read -r file title; do
    current=$((current + 1))

    if is_phase_done "$file"; then
      log "Pulando $title (ja completada)"
      skipped_phases+=("$title")
      continue
    fi

    if run_phase "$file" "$title" "$current" "$total_phases"; then
      completed_phases+=("$title")
    else
      failed_phases+=("$title")
      echo ""
      warn "Fase falhou: $title"
      read -p "Continuar para a proxima fase? (Y/n) " -n 1 -r
      echo
      [[ $REPLY =~ ^[Nn]$ ]] && break
    fi
  done < "$MANIFEST"

  local end_time
  end_time=$(date +%s)
  local total_duration=$((end_time - start_time))

  echo ""
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  log "RELATORIO FINAL (engine: $ENGINE)"
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

  if [ ${#completed_phases[@]} -gt 0 ]; then
    echo ""
    success "Completadas (${#completed_phases[@]}):"
    for phase in "${completed_phases[@]}"; do
      echo -e "    ${GREEN}$phase${NC}"
    done
  fi

  if [ ${#skipped_phases[@]} -gt 0 ]; then
    echo ""
    log "Puladas (${#skipped_phases[@]}):"
    for phase in "${skipped_phases[@]}"; do
      echo -e "    $phase"
    done
  fi

  if [ ${#failed_phases[@]} -gt 0 ]; then
    echo ""
    fail "Falharam (${#failed_phases[@]}):"
    for phase in "${failed_phases[@]}"; do
      echo -e "    ${RED}$phase${NC}"
    done
    echo ""
    fail "Verifique os logs em $LOG_DIR/"
  fi

  echo ""
  log "Inicio: $(date -d @$start_time '+%d/%m/%Y %H:%M:%S')"
  log "Fim:    $(date -d @$end_time '+%d/%m/%Y %H:%M:%S')"
  log "Duracao total: $(format_duration $total_duration)"
  echo ""
}

main