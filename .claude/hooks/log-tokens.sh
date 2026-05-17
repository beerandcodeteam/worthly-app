#!/usr/bin/env bash
# Stop hook — grava snapshot cumulativo de tokens por (sessão, modelo).
# Cada Stop adiciona N linhas em .harness/tokens.jsonl (uma por modelo usado).
# Os valores são CUMULATIVOS para a sessão — agregadores devem pegar o MAX por (session, model).

set -euo pipefail

input=$(cat)
transcript=$(echo "$input" | jq -r '.transcript_path // empty')
session=$(echo "$input"    | jq -r '.session_id // empty')

[[ -z "$transcript" || ! -f "$transcript" ]] && exit 0

mkdir -p .harness
ts=$(date -u +%Y-%m-%dT%H:%M:%S.%3NZ)

# Lê o transcript JSONL, filtra mensagens assistant com usage,
# agrupa por modelo e soma tokens. Cada modelo vira uma linha em tokens.jsonl.
jq -s -c --arg ts "$ts" --arg session "$session" '
  map(select(.type == "assistant" and .message.usage != null))
  | group_by(.message.model)
  | map({
      ts: $ts,
      session_id: $session,
      model: .[0].message.model,
      messages: length,
      input:          (map(.message.usage.input_tokens                // 0) | add),
      output:         (map(.message.usage.output_tokens               // 0) | add),
      cache_creation: (map(.message.usage.cache_creation_input_tokens // 0) | add),
      cache_read:     (map(.message.usage.cache_read_input_tokens     // 0) | add)
    })
  | .[]
' "$transcript" >> .harness/tokens.jsonl

exit 0
