#!/usr/bin/env bash
# PostToolUse normaliser for Dotkernel projects.
# Enforces: UTF-8 without BOM, LF line endings, no trailing whitespace,
# exactly one trailing newline. Pure bash + sed + awk, no jq.
# Rewrites the file in place, so its permissions, owner and links survive.
# Exit 0 always — this fixes silently rather than blocking.

set -u

payload=$(cat)

raw_path=$(printf '%s' "$payload" | tr -d '\n' \
  | sed -n 's/.*"file_path"[[:space:]]*:[[:space:]]*"\([^"]*\)".*/\1/p')

[ -z "$raw_path" ] && exit 0

path=${raw_path//\\\\/\\}
path=${path//\\//}

[ -f "$path" ] || exit 0

lower=$(printf '%s' "$path" | tr '[:upper:]' '[:lower:]')

# Skip binaries and anything not ours.
case "$lower" in
  *.png|*.jpg|*.jpeg|*.gif|*.ico|*.svg|*.zip|*.phar|*.pdf|*.woff|*.woff2|*.ttf|*.eot|*.key|*.pem)
    exit 0 ;;
  */vendor/*|*/node_modules/*|*/.git/*|*/data/*|*/log/*)
    exit 0 ;;
esac

# Text files only.
case "$lower" in
  *.php|*.phtml|*.twig|*.md|*.json|*.xml|*.neon|*.yml|*.yaml|*.sh|*.txt|*.dist|*.html|*.css|*.js|*.sql|*.editorconfig|*.gitattributes|*.gitignore) ;;
  *) exit 0 ;;
esac

changed=""
tmp=$(mktemp "${TMPDIR:-/tmp}/dk-normalize.XXXXXX") || exit 0
trap 'rm -f "$tmp"' EXIT

# Copy the fixed text back over the original. Writing through the existing file, rather
# than moving a new one into place, keeps its mode (an executable script stays
# executable), its owner, and any symlink or hard link to it.
write_back() { { cat "$tmp" > "$path"; } 2>/dev/null; }

# 1. Strip UTF-8 BOM.
bom=$(printf '\357\273\277')
if [ "$(head -c 3 "$path" 2>/dev/null)" = "$bom" ]; then
  tail -c +4 "$path" > "$tmp" && write_back && changed="${changed} BOM-removed"
fi

# 2. CRLF -> LF, then a lone CR (classic Mac line ending) -> LF.
if LC_ALL=C grep -qU $'\r' "$path" 2>/dev/null; then
  LC_ALL=C sed $'s/\r$//' "$path" | tr '\r' '\n' > "$tmp" && write_back \
    && changed="${changed} CRLF->LF"
fi

# 3. Trailing whitespace (skip markdown: two trailing spaces are a hard line break).
case "$lower" in
  *.md) ;;
  *)
    if LC_ALL=C grep -q '[[:space:]]$' "$path"; then
      sed 's/[[:space:]]*$//' "$path" > "$tmp" && write_back \
        && changed="${changed} trailing-ws-stripped"
    fi
    ;;
esac

# 4. Exactly one trailing newline: add a missing one, drop blank or whitespace-only lines
#    at the end. A file with nothing but whitespace is left alone.
if [ -s "$path" ]; then
  LC_ALL=C awk '
    /^[ \t\f\v]*$/ { held = held $0 "\n"; next }   # blank: hold until a line follows
    { printf "%s%s\n", held, $0; held = "" }        # still held at the end: dropped
  ' "$path" > "$tmp"
  if [ -s "$tmp" ] && ! cmp -s "$tmp" "$path"; then
    if [ "$(wc -c < "$tmp")" -gt "$(wc -c < "$path")" ]; then
      fix=newline-added
    else
      fix=trailing-blank-lines-removed
    fi
    write_back && changed="${changed} ${fix}"
  fi
fi

if [ -n "$changed" ]; then
  printf 'File hygiene applied to %s:%s\n' "$path" "$changed" >&2
fi

exit 0
