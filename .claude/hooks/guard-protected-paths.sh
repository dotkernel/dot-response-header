#!/usr/bin/env bash
# PreToolUse guard for Dotkernel projects.
# Blocks writes to vendored, generated, secret or dependency-manifest files.
# Pure bash, no jq — works in Git Bash on Windows.
# Exit 0 = allow, exit 2 = block (stderr is shown to Claude).

set -u

payload=$(cat)

# Pull the target path out of tool_input without a JSON parser: "file_path" for
# Edit, Write and MultiEdit, "notebook_path" for NotebookEdit.
path_field() {
  printf '%s' "$payload" \
    | tr -d '\n' \
    | sed -n 's/.*"'"$1"'"[[:space:]]*:[[:space:]]*"\([^"]*\)".*/\1/p'
}
raw_path=$(path_field file_path)
[ -z "$raw_path" ] && raw_path=$(path_field notebook_path)

[ -z "$raw_path" ] && exit 0

# Normalise: unescape JSON backslashes, turn \ into /, drop a drive prefix, lowercase.
path=${raw_path//\\\\/\\}
path=${path//\\//}
path=${path#[A-Za-z]:}
lower=$(printf '%s' "$path" | tr '[:upper:]' '[:lower:]')

block() {
  printf 'BLOCKED: %s\n%s\n' "$raw_path" "$1" >&2
  exit 2
}

case "$lower" in
  */vendor/*|vendor/*)
    block "vendor/ is Composer-managed. Change the dependency or your own code instead." ;;
  */node_modules/*|node_modules/*)
    block "node_modules/ is npm-managed." ;;
  */migration/*|*/migrations/*)
    block "Migrations are generated. Adjust the entity and regenerate with 'doctrine-migrations diff'." ;;
  */data/cache/*|*/data/lock/*|*/data/oauth/*|data/cache/*|data/lock/*|data/oauth/*)
    block "data/ holds generated cache, locks and keys. Never hand-edit." ;;
  */log/*|log/*)
    block "log/ is runtime output." ;;
  */public/uploads/*|public/uploads/*)
    block "public/uploads/ is user-uploaded content." ;;
  *composer.json|*composer.lock|*package.json|*package-lock.json|*yarn.lock|*pnpm-lock.yaml)
    block "Dependency manifests require explicit user approval. Propose the change in chat first." ;;
  *.local.php|*/local.php|local.php|*/local.test.php|local.test.php)
    block "Local config holds environment secrets and is git-ignored. Edit the .dist template and let the user apply it." ;;
  *.phpcs-cache|*.phpunit.result.cache|*/.git/*)
    block "Tool cache / git internals." ;;
  *.png|*.jpg|*.jpeg|*.gif|*.svg|*.ico|*.zip|*.phar|*.key|*.pem)
    block "Binary or key material." ;;
esac

exit 0
