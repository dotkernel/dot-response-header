#!/usr/bin/env bash
# PostToolUse: markdownlint a .md file right after it is written.
# Report-only by design — it does NOT run --fix. Reformatting a file that Claude
# just wrote invalidates its in-memory copy and breaks the next targeted edit,
# so bulk fixing stays a deliberate end-of-task action.
# Exit 2 surfaces the violations to Claude immediately.
# Silent exit 0 when markdownlint-cli2 is not installed, so .claude/ stays
# drop-in with no npm requirement.

set -u

payload=$(cat)

raw_path=$(printf '%s' "$payload" | tr -d '\n' \
  | sed -n 's/.*"file_path"[[:space:]]*:[[:space:]]*"\([^"]*\)".*/\1/p')

[ -z "$raw_path" ] && exit 0

path=${raw_path//\\\\/\\}
path=${path//\\//}

lower=$(printf '%s' "$path" | tr '[:upper:]' '[:lower:]')

case "$lower" in
  *.md|*.markdown) ;;
  *) exit 0 ;;
esac
case "$lower" in
  */vendor/*|*/node_modules/*|*/.git/*|*/data/*|*/log/*) exit 0 ;;
esac
[ -f "$path" ] || exit 0

config="${CLAUDE_PROJECT_DIR:-.}/.claude/markdownlint.jsonc"
[ -f "$config" ] || exit 0

bin=""
if [ -x ./node_modules/.bin/markdownlint-cli2 ]; then
  bin=./node_modules/.bin/markdownlint-cli2
elif command -v markdownlint-cli2 >/dev/null 2>&1; then
  bin=markdownlint-cli2
fi
[ -z "$bin" ] && exit 0

if ! output=$("$bin" --config "$config" "$path" 2>&1); then
  printf 'markdownlint violations in %s\n%s\n' "$path" "$output" >&2
  exit 2
fi

exit 0
