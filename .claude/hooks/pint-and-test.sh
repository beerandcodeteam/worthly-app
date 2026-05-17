#!/usr/bin/env bash
set -euo pipefail

input=$(cat)
file=$(echo "$input" | jq -r '.tool_input.file_path // empty')

# só age em .php fora de vendor/node_modules
[[ "$file" == *.php ]] || exit 0
[[ "$file" == */vendor/* || "$file" == */node_modules/* ]] && exit 0

# Sail precisa estar up; se não estiver, sai silenciosamente
vendor/bin/sail ps 2>/dev/null | grep -q "Up" || exit 0

# 1) Pint só no arquivo editado
container_path="${file#$PWD/}"
vendor/bin/sail bin pint "$container_path" --format agent >&2 || true

# 2) Se for arquivo de teste, roda o filtro
if [[ "$file" == *"/tests/"*.php ]]; then
  test_name=$(basename "$file" .php)
  echo "→ Rodando $test_name" >&2
  if ! vendor/bin/sail artisan test --compact --filter="$test_name" >&2; then
    echo "↑ Teste falhou — corrija antes de prosseguir." >&2
    exit 2
  fi
fi
exit 0
