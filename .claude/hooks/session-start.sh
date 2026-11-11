#!/usr/bin/env bash
# SessionStart: print a short briefing that becomes part of Claude's context.
# Detects which Dotkernel project this is and what is not set up yet.
# Everything here is read-only. Keep the output compact.

set -u
cd "${CLAUDE_PROJECT_DIR:-.}" 2>/dev/null || exit 0

echo "## Project state"

# --- which Dotkernel project? ---
if [ -f composer.json ]; then
  pkg=$(sed -n 's/.*"name"[[:space:]]*:[[:space:]]*"\([^"]*\)".*/\1/p' composer.json | head -1)
  ns=$(grep '": "src/' composer.json 2>/dev/null | grep -v '"Core' | head -1 \
    | sed 's/^[^"]*"\([A-Za-z]*\).*/\1/')
  variant="unknown"
  case "$pkg" in
    */api)      variant="API (REST/HAL)" ;;
    */admin)    variant="Admin (templated, forms)" ;;
    */frontend) variant="Frontend (templated, forms)" ;;
    */light)    variant="Light (minimal starter)" ;;
    */queue)    variant="Queue (CLI/worker)" ;;
  esac
  if [ "$variant" = "unknown" ]; then
    hal=0 form=0
    grep -q 'mezzio/mezzio-hal' composer.json && hal=1
    grep -q 'laminas/laminas-form' composer.json && form=1
    case "$hal$form" in
      10) variant="API-style (has mezzio-hal)" ;;
      01) variant="templated-style (has laminas-form)" ;;
      11) variant="unclear (has mezzio-hal and laminas-form; infer the parent from the PSR-4 roots)" ;;
    esac
  fi
  echo "- package: ${pkg:-unknown}  |  variant: ${variant}"
  [ -n "$ns" ] && echo "- root namespace: ${ns}"
  guards=config/autoload/authorization-guards.global.php
  if [ -f "$guards" ]; then
    policy=$(sed -n "s/.*protection_policy.*\(POLICY_[A-Z]*\).*/\1/p" "$guards" | head -1)
    echo "- authorization: guards + RBAC (${guards##*/}${policy:+, $policy})"
    [ "$policy" = POLICY_ALLOW ] && echo "  unlisted routes are PUBLIC — every new route needs a guard rule"
  elif [ -f config/autoload/authorization.global.php ]; then
    echo "- authorization: RBAC (authorization.global.php) — unlisted routes are denied"
  fi
fi

# --- git ---
# Ask git rather than test for a .git directory: in a worktree or a submodule checkout
# .git is a file.
if command -v git >/dev/null 2>&1 \
  && [ "$(git rev-parse --is-inside-work-tree 2>/dev/null)" = true ]; then
  eol_bad=$(git ls-files --eol 2>/dev/null | grep -c 'w/crlf' || true)
  [ "${eol_bad:-0}" -gt 0 ] && echo "- WARNING: ${eol_bad} tracked file(s) have CRLF in the worktree; run /dk-hygiene"
  top=$(git rev-parse --show-toplevel 2>/dev/null)
  if [ -f "$top/.gitmodules" ]; then
    subs=$(git config -f "$top/.gitmodules" --get-regexp '^submodule\..*\.path$' 2>/dev/null \
      | cut -d' ' -f2- | tr '\n' ' ')
    subs=${subs% }
    case " $subs " in
      *" src/Core "*) echo "- git submodules: ${subs}. Core is one: commit inside src/Core, then bump the pointer here" ;;
      "  ") ;;
      *) echo "- git submodules: ${subs}" ;;
    esac
  fi
fi

# --- setup completeness ---
[ -f composer.json ] && [ ! -d vendor ] \
  && echo "- vendor/ missing — dependencies are not installed (run /dk-bootstrap)"

for d in config/autoload/*.dist config/*.dist; do
  [ -e "$d" ] || continue
  target=${d%.dist}
  [ -f "$target" ] && continue
  case "$target" in
    config/development.config.php) continue ;;   # managed by development-mode, not copied by hand
  esac
  echo "- missing ${target} (copy from ${d})"
done

for d in docs/features documentation/features; do
  [ -d "$d" ] || continue
  n=$(ls -1 "$d"/*.md 2>/dev/null | grep -vc '/README\.md$' || true)
  echo "- ${d}/: ${n:-0} feature doc(s) — read the relevant one before adding behaviour there"
done

[ -f config/development.config.php ] && echo "- development mode is ENABLED"
[ -f data/cache/config-cache.php ] && echo "- config cache present; clear it after config edits"

exit 0
