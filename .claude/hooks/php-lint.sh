#!/usr/bin/env bash
# PostToolUse: syntax-check a PHP file right after it is written.
# Report-only by design — it does NOT reformat. Reformatting a file that Claude
# just wrote invalidates its in-memory copy and breaks the next targeted edit,
# so phpcbf is deliberately left to `composer cs-fix` / /dk-check at the end.
# Exit 2 surfaces the parse error to Claude immediately.

set -u

payload=$(cat)

raw_path=$(printf '%s' "$payload" | tr -d '\n' \
  | sed -n 's/.*"file_path"[[:space:]]*:[[:space:]]*"\([^"]*\)".*/\1/p')

[ -z "$raw_path" ] && exit 0

path=${raw_path//\\\\/\\}
path=${path//\\//}

case "$path" in
  *.php) ;;
  *) exit 0 ;;
esac
case "$path" in
  */vendor/*|*/node_modules/*) exit 0 ;;
esac
[ -f "$path" ] || exit 0
command -v php >/dev/null 2>&1 || exit 0

if ! output=$(php -l "$path" 2>&1); then
  printf 'PHP syntax error in %s\n%s\n' "$path" "$output" >&2
  exit 2
fi

exit 0
