#!/usr/bin/env bash
set -euo pipefail

input=$(cat)
mkdir -p .harness

ts=$(date -u +%Y-%m-%dT%H:%M:%S.%3NZ)
branch=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "?")

# enriquece o evento original com timestamp e branch
echo "$input" | jq -c \
  --arg ts "$ts" \
  --arg branch "$branch" \
  '{ts: $ts, branch: $branch} + .' \
  >> .harness/events.jsonl
exit 0
