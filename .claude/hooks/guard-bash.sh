#!/usr/bin/env bash
# PreToolUse guard for Bash commands in Dotkernel projects.
# Catches installs and destructive git even when hidden inside compound commands
# ("cd x && composer require y"), which path-based permission rules cannot see.
# Quoted arguments, comments and heredoc bodies are data and are not matched, so
# grep "rm -rf" docs/ is allowed. Quoted code is still checked: the script after a -c
# option (sh -c "..."), the arguments of eval, ssh and watch, and a heredoc or
# here-string fed to a shell.
# This matches text, not a parsed shell: an unusual spelling can still get past it.
# bash, sed and awk; jq is used for the payload when installed.
# Exit 0 = allow, exit 2 = block.

set -u
export LC_ALL=C

payload=$(cat)

# Print tool_input.command with its JSON escapes decoded.
extract_command() {
  if command -v jq >/dev/null 2>&1 \
    && printf '%s' "$payload" | jq -r '.tool_input.command // empty' 2>/dev/null; then
    return
  fi
  # No jq: park the two escapes that can hide a quote (\\ and \") on control bytes,
  # which valid JSON never contains raw, so [^"]* ends exactly at the closing quote.
  local bs='\' sl='/' esc_bs=$'\002' esc_q=$'\001' s
  s=$(printf '%s' "$payload" | tr -d '\n' | sed -n \
    -e 's/\\\\/'"$esc_bs"'/g' \
    -e 's/\\"/'"$esc_q"'/g' \
    -e 's/.*"command"[[:space:]]*:[[:space:]]*"\([^"]*\)".*/\1/p')
  s=${s//"${bs}n"/$'\n'}
  s=${s//"${bs}t"/$'\t'}
  s=${s//"${bs}r"/$'\r'}
  s=${s//"$bs$sl"/$sl}
  s=${s//"$esc_q"/\"}
  s=${s//"$esc_bs"/$bs}
  printf '%s' "$s"
}

cmd=$(extract_command)
[ -z "$cmd" ] && exit 0

# Reduce the command to the text that would run. Quoted strings, comments and heredoc
# bodies become the placeholder Q, and ; & | ( ) ` and newlines become separate words.
# Code inside quotes or a heredoc (the script after a -c option, the arguments of eval,
# ssh or watch, a heredoc or here-string fed to a shell) is reduced the same way and
# appended after a " ; ". An unterminated quote leaves the rest of the command unmasked.
IFS= read -r -d '' SHELL_VIEW <<'AWK' || true
{ src = (NR == 1) ? $0 : src "\n" $0 }
END { printf "%s", scan(src, 0) }

# out: the reduced command; seg0: where the current simple command starts in out;
# ws: the next character starts a word (so # opens a comment).
function scan(s, depth,    n, i, c, j, out, seg0, ws, extra, body, q, seg, code, strip,
                           d, nd, hd, hs, hf, h, lines, nl, ln, pos, found, cmp, start) {
  n = length(s); i = 1; out = ""; seg0 = 1; ws = 1; extra = ""; nd = 0
  while (i <= n) {
    if (match(substr(s, i, 512), /^[^\\'"$#<;&|()`\n]+/)) {   # ordinary text, in runs
      out = out substr(s, i, RLENGTH); i += RLENGTH
      ws = (substr(out, length(out), 1) ~ /[ \t]/); continue
    }
    c = substr(s, i, 1)

    if (c == "\\") {                       # escaped character, or line continuation
      if (substr(s, i + 1, 1) != "\n") { out = out substr(s, i + 1, 1); ws = 0 }
      i += 2; continue
    }

    if (c == "'" || c == "\"" || (c == "$" && substr(s, i + 1, 1) == "'")) {
      q = c; j = i + 1
      if (c == "$") { q = "'"; j = i + 2 }
      if (c == "'") {
        j = index(substr(s, j), "'"); if (j) j += i
      } else {                             # "..." and $'...' honour backslash escapes
        while (j <= n && substr(s, j, 1) != q) j += (substr(s, j, 1) == "\\") ? 2 : 1
        if (j > n) j = 0
      }
      if (!j) { out = out substr(s, i); break }
      body = substr(s, i + 1, j - i - 1)
      seg = substr(out, seg0); code = 0
      if (seg ~ /(^|[ \t])-[a-z]*c[ \t]*$/ || seg ~ /(^|[ \t\/])(eval|ssh|watch)[ \t]/) code = 1
      else if (sub(/<<<[ \t]*$/, "", seg)) code = feeds_shell(seg)
      if (code && c != "$") extra = extra " ; " nested((c == "\"") ? undq(body) : body, depth)
      out = out "Q"; i = j + 1; ws = 0; continue
    }

    if (c == "#" && ws) {                  # comment, up to the end of the line
      j = index(substr(s, i), "\n")
      if (!j) break
      i += j - 1; continue
    }

    if (c == "<" && substr(s, i, 3) == "<<<") { out = out "<<<"; i += 3; ws = 1; continue }
    if (c == "<" && substr(s, i, 2) == "<<") {                            # heredoc
      j = i + 2; strip = 0
      if (substr(s, j, 1) == "-") { strip = 1; j++ }
      while (substr(s, j, 1) == " " || substr(s, j, 1) == "\t") j++
      d = ""; q = substr(s, j, 1)
      if (q == "'" || q == "\"") {
        c = index(substr(s, j + 1), q)
        if (c) { d = substr(s, j + 1, c - 1); j += c + 1 }
      } else {
        while (j <= n && substr(s, j, 1) !~ /[ \t\n;&|()<>'"]/) {
          if (substr(s, j, 1) == "\\") j++
          d = d substr(s, j, 1); j++
        }
      }
      if (d == "") { out = out "<<"; i += 2; ws = 1; continue }
      nd++; hd[nd] = d; hs[nd] = strip; hf[nd] = feeds_shell(substr(out, seg0))
      out = out " << Q "; i = j; ws = 0; continue
    }

    if (c == "\n") {
      out = out " ; "; seg0 = length(out) + 1; ws = 1; i++
      if (nd) {                            # bodies of the heredocs opened on that line
        nl = split(substr(s, i), lines, "\n"); ln = 1; pos = i
        for (h = 1; h <= nd; h++) {
          start = pos; found = 0
          for (; ln <= nl; ln++) {
            cmp = lines[ln]
            if (hs[h]) sub(/^\t+/, "", cmp)
            if (cmp == hd[h]) { found = 1; break }
            pos += length(lines[ln]) + 1
          }
          if (!found) { pos = start; break }   # no terminator: scan it as commands
          if (hf[h]) extra = extra " ; " nested(substr(s, start, pos - start), depth)
          pos += length(lines[ln]) + 1; ln++
        }
        i = pos; nd = 0
      }
      continue
    }

    if (index(";&|()`", c)) { out = out " " c " "; seg0 = length(out) + 1; ws = 1; i++; continue }
    out = out c; ws = 0; i++
  }
  return out extra
}

# The command so far ends with a shell that reads its script from stdin.
function feeds_shell(seg) { return seg ~ /(^|[ \t\/])(ba|z|da|k|a)?sh([ \t]+-[a-z]+)*[ \t]*$/ }

function nested(s, depth) { return (depth < 4) ? scan(s, depth + 1) : s }

function undq(s,    r, i, n, c, nx) {      # remove the escapes "..." honours
  r = ""; n = length(s)
  for (i = 1; i <= n; i++) {
    c = substr(s, i, 1)
    if (c == "\\" && i < n) {
      nx = substr(s, i + 1, 1)
      if (nx == "\n") { i++; continue }
      if (index("\"\\$`", nx)) { r = r nx; i++; continue }
    }
    r = r c
  }
  return r
}
AWK

lc=$(printf '%s' "$cmd" | tr '[:upper:]' '[:lower:]')
view=$(printf '%s' "$lc" | awk "$SHELL_VIEW") || view=$lc
norm=$(printf '%s' "$view" | tr '\t\r\n' '   ' | tr -s ' ')

block() {
  local shown=$cmd
  [ ${#shown} -gt 500 ] && shown="${shown:0:500}..."
  printf 'BLOCKED command: %s\n%s\n' "$shown" "$1" >&2
  exit 2
}

set -f
# shellcheck disable=SC2206  # word splitting is the point; globbing is off
words=($norm)
set +f
nw=${#words[@]}

# composer [options] [global] <command>. Composer resolves any unambiguous prefix of a
# command name (req, upd, inst, rem) and the aliases r, i, u, upgrade, rm, uninstall.
for ((i = 0; i < nw; i++)); do
  case ${words[i]} in
    composer|composer.phar|*/composer|*/composer.phar) ;;
    *) continue ;;
  esac
  j=$((i + 1))
  while ((j < nw)); do
    case ${words[j]} in
      -d|--working-dir) j=$((j + 2)) ;;
      -*) j=$((j + 1)) ;;
      *) [[ global == "${words[j]}"* ]] && j=$((j + 1)) || break ;;
    esac
  done
  sub=${words[j]:-}
  [[ $sub =~ ^[a-z]+$ ]] || continue
  for name in require remove update upgrade install uninstall rm; do
    [[ $name == "$sub"* ]] \
      && block "Dependency changes need my explicit approval. Tell me the package and why, and I will run it."
  done
done

case " $norm " in
  *"npm install"*|*"npm i "*|*"npm uninstall"*|*"npm update"*|*"npm ci"*|\
  *"yarn add"*|*"yarn remove"*|*"pnpm add"*|*"pnpm remove"*|*"pip install"*)
    block "Package installs need my explicit approval." ;;
esac

case " $norm " in
  *"git push"*|*"git reset --hard"*|*"git clean -"*|*"git checkout -- "*|\
  *"git submodule"*|*"git filter-branch"*|*"git rebase"*)
    block "Destructive or publishing git operations are mine to run." ;;
esac

# rm with both a recursive and a force flag, in any spelling: -rf, -fr, -r -f, -Rf,
# --recursive --force. Its arguments end at a separator, at -- and at find's "{} +".
for ((i = 0; i < nw; i++)); do
  case ${words[i]} in rm|*/rm) ;; *) continue ;; esac
  rec=0 force=0
  for ((j = i + 1; j < nw; j++)); do
    w=${words[j]}
    case $w in
      ';'|'&'|'|'|'('|')'|'`'|--|+) break ;;
      --?*) [[ recursive == "${w#--}"* ]] && rec=1
            [[ force == "${w#--}"* ]] && force=1 ;;
      -?*) [[ $w == *r* ]] && rec=1
           [[ $w == *f* ]] && force=1 ;;
    esac
  done
  ((rec && force)) && block "Refusing destructive filesystem command."
done

case " $norm " in
  *"mkfs"*|*"chmod -r 777"*)
    block "Refusing destructive filesystem command." ;;
esac

case " $norm " in
  *"doctrine-migrations migrate"*|*"doctrine-migrations execute"*|\
  *"fixtures:execute"*|*"schema:drop"*|*"schema:update"*)
    block "Database-mutating commands need my approval. Show me the command and what it will change." ;;
esac

case " $norm " in
  *"development-enable"*|*"development-disable"*)
    block "Toggling development mode changes committed config state. Ask me first." ;;
esac

exit 0
