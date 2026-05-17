#!/usr/bin/env bash
set -euo pipefail

# carrega senha do .env local (gitignored)
[[ -f .claude/.env ]] && set -a && source .claude/.env && set +a

input=$(cat)
event=$(echo "$input"   | jq -r '.hook_event_name // "Unknown"')
msg=$(echo "$input"     | jq -r '.message // empty')
session=$(echo "$input" | jq -r '.session_id // empty')
branch=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "?")

# Eventos como Stop NÃO trazem .message no payload. Quando estamos rodando dentro
# do ralph.sh, sintetizamos a mensagem a partir das env vars exportadas pelo
# orquestrador (RALPH_PHASE_TITLE / RALPH_PHASE_NUM / RALPH_PHASE_TOTAL / RALPH_PHASE_ATTEMPT).
if [[ -z "$msg" ]]; then
  if [[ -n "${RALPH_PHASE_TITLE:-}" ]]; then
    phase_num="${RALPH_PHASE_NUM:-?}"
    phase_total="${RALPH_PHASE_TOTAL:-?}"
    attempt="${RALPH_PHASE_ATTEMPT:-1}"
    max_attempts="${RALPH_PHASE_MAX_ATTEMPTS:-?}"
    engine="${RALPH_ENGINE:-claude}"

    case "$event" in
      Stop)
        msg="[ralph/${engine}] Fase ${phase_num}/${phase_total} finalizou turno — ${RALPH_PHASE_TITLE} (tentativa ${attempt}/${max_attempts})"
        ;;
      Notification)
        msg="[ralph/${engine}] Fase ${phase_num}/${phase_total} requer atenção — ${RALPH_PHASE_TITLE}"
        ;;
      *)
        msg="[ralph/${engine}] ${event} — Fase ${phase_num}/${phase_total} ${RALPH_PHASE_TITLE}"
        ;;
    esac
  else
    msg="[claude-code] ${event} em ${branch}"
  fi
fi

payload=$(jq -n \
  --arg event   "$event" \
  --arg msg     "$msg" \
  --arg session "$session" \
  --arg branch  "$branch" \
  --arg cwd     "$PWD" \
  --arg ts      "$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
  --arg phase   "${RALPH_PHASE_TITLE:-}" \
  --arg phase_num "${RALPH_PHASE_NUM:-}" \
  --arg phase_total "${RALPH_PHASE_TOTAL:-}" \
  --arg attempt "${RALPH_PHASE_ATTEMPT:-}" \
  '{event: $event, message: $msg, session_id: $session, branch: $branch, cwd: $cwd, ts: $ts, phase: $phase, phase_num: $phase_num, phase_total: $phase_total, attempt: $attempt}')

# nunca trava o agente se o webhook estiver indisponível
curl -sS -X POST \
  -u "beerandcode:${HARNESS_NOTIFY_PASSWORD:-}" \
  -H "Content-Type: application/json" \
  -d "$payload" \
  https://n8n.bcode.live/webhook/ed7a3d7e-bdd4-481a-a748-1f6d30b90bfd \
  >/dev/null 2>&1 || true

exit 0
