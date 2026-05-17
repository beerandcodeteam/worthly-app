---
description: Revisa o diff de uma fase contra CLAUDE.md e roda security-auditor
argument-hint: <numero-da-fase>
allowed-tools: Read, Grep, Bash(git diff:*), Bash(git log:*), Bash(git show:*), Task
---

# Revisar Fase $1

## 1. Localize o commit da fase

!git log --oneline | grep -iE "(fase|phase)[[:space:]]*$1" | head -5

## 2. Diff da fase

!git log --oneline | grep -iE "(fase|phase)[[:space:]]*$1" | head -1 | awk '{print $1}' | xargs -I {} git show --stat {}

## 3. Revisão de convenção

Leia `CLAUDE.md` na íntegra (use Read). Para cada arquivo do diff:
- Aponte violações com formato `[V<N>] <arquivo>:<linha> — Regra: <citação> — Encontrado: <descrição>`
- Se não houver violações, escreva `Sem violações de convenção.`

## 4. Revisão de segurança

Use o subagent `security-auditor` (via Task tool) passando a lista de arquivos alterados sob `routes/`, `app/Http/`, `app/Models/`.

## 5. Relatório final

Combine as duas saídas em duas seções: **Convenção** e **Segurança**.

Não conserte nada. Apenas reporte.
