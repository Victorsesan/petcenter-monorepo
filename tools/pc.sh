#!/usr/bin/env bash
# =====================================================================
# pc — Pet Center CEO helper for Termux (or any Linux/Mac shell with git)
# Built to use almost NO mobile data: it never downloads the repo's photos,
# videos or code files. It only downloads the history (about 0.3 MB) and
# fetches single files on demand.
#
#   pc setup            one-time: tiny clone (history only, about 0.3 MB)
#   pc status           who is doing what: branches, open TODO items, is live current?
#   pc show FILE [REF]  read one file from any branch, e.g.  pc show TODO.md   or   pc show adopt/index.html origin/task/status-1
#   pc merge ID         merge the approved branch task/ID into main (asks before pushing)
#   pc deploy           publish main's adopt/ folder to the live site (asks before pushing)
#   pc import ID FILES  put a worker's files (zip or folder) on branch task/ID when that worker cannot reach GitHub
#   pc live [path]      HTTP status of the live site, e.g.  pc live /pets/
#
# Needs: git, bash, curl.   Termux:  pkg install git curl   (and unzip for "pc import")
# =====================================================================
set -u
REPO="Victorsesan/petcenter-monorepo"
DIR="${PC_DIR:-$HOME/petcenter}"
SITE="${PC_SITE:-https://adopt.petcenterco.com}"
DEPLOY_BRANCH="${PC_DEPLOY_BRANCH:-adopt-deploy}"
APP_DIR="${PC_APP_DIR:-adopt}"

say() { printf '%s\n' "$*"; }
die() { printf 'ERROR: %s\n' "$*" >&2; exit 1; }
ask() { printf '%s [y/N] ' "$1"; read -r a; [ "$a" = "y" ] || [ "$a" = "Y" ]; }
g()   { git -C "$DIR" "$@"; }

cmd_setup() {
  [ -d "$DIR/.git" ] && die "$DIR already exists. Use: pc status"
  TOKEN="${PC_TOKEN:-}"
  if [ -z "$TOKEN" ]; then printf 'Paste the GitHub token (it will not be shown): '; read -rs TOKEN; echo; fi
  # --filter=blob:none : download NO file contents at all, only commits and folder listings.
  # --no-checkout      : do not create any working files. Everything below works from git objects.
  git clone --filter=blob:none --no-checkout "https://Victorsesan:${TOKEN}@github.com/${REPO}.git" "$DIR" || die "clone failed"
  g config user.name  "Pet Center CEO"
  g config user.email "ceo@petcenterco.com"
  say "Done (about 0.3 MB). Folder: $DIR      Next: pc status"
}

need() { [ -d "$DIR/.git" ] || die "Not set up yet. Run: pc setup"; g fetch -q --prune origin || die "fetch failed (check internet / token)"; }

# The folder listing the live deploy branch SHOULD have: main's adopt/ folder at the root, plus main's .gitignore.
# Built from git objects only, so no files are downloaded or checked out.
want_tree() {
  { g ls-tree "origin/main:${APP_DIR}"; g ls-tree origin/main | awk -F'\t' '$2==".gitignore"'; } | g mktree --missing
}

cmd_status() {
  need
  say "=== MAIN ==="; g log -1 --format='  %h  %ad  %an  %s' --date=format:'%d %b %H:%M' origin/main
  say; say "=== WORK BRANCHES (who is doing what) ==="
  found=0
  for b in $(g for-each-ref --format='%(refname:short)' 'refs/remotes/origin/task' 'refs/remotes/origin/bugfix'); do
    found=1
    ahead=$(g rev-list --count "origin/main..$b"); behind=$(g rev-list --count "$b..origin/main")
    files=$(g diff --no-renames --name-only "origin/main...$b" | wc -l | tr -d ' ')
    merged=""; [ "$ahead" = "0" ] && merged="  (already in main)"
    id=${b#origin/task/}
    rep=$(g ls-tree --name-only "$b" -- "reports/task-$id.md" 2>/dev/null | wc -l | tr -d ' ')
    g log -1 --format="  ${b#origin/}  |  %ad  |  %an  |  %s" --date=format:'%d %b %H:%M' "$b"
    say "      ahead $ahead / behind main $behind, $files files changed, report: $([ "$rep" -gt 0 ] && echo yes || echo NO)$merged"
  done
  [ $found = 0 ] && say "  (no task branches)"
  say; say "=== TODO.md — entries not DONE ==="
  g show origin/main:TODO.md | grep -E '^\| [0-9]+ \|' | grep -vE '\| (DONE|WON.T DO) \|' | sed 's/^/  /' || true
  say; say "=== LIVE SITE vs MAIN ==="
  if g rev-parse -q --verify "origin/${DEPLOY_BRANCH}" >/dev/null; then
    want=$(want_tree); have=$(g rev-parse "origin/${DEPLOY_BRANCH}^{tree}")
    if [ "$want" = "$have" ]; then say "  Live branch is CURRENT with main (nothing to deploy)."
    else n=$(g diff-tree -r --no-renames --name-only "$have" "$want" | wc -l | tr -d ' '); say "  Live branch is BEHIND main: $n file(s) differ. Run: pc deploy"; fi
  else say "  No ${DEPLOY_BRANCH} branch found."; fi
}

cmd_show() {
  f="${1:-}"; ref="${2:-origin/main}"; [ -n "$f" ] || die "Usage: pc show FILE [branch]"
  need; g show "$ref:$f"
}

# Merge without a working copy: git computes the merged folder listing itself.
cmd_merge() {
  n="${1:-}"; [ -n "$n" ] || die "Usage: pc merge ID   (merges origin/task/ID, for example: pc merge status-1)"
  need; b="origin/task/$n"
  g rev-parse -q --verify "$b" >/dev/null || die "No branch task/$n on GitHub"
  g merge-base --is-ancestor "$b" origin/main && { say "task/$n is already in main. Nothing to do."; exit 0; }
  say "Files task/$n changes compared with main:"; g diff --no-renames --name-status "origin/main...$b" | head -40
  ask "Merge task/$n into main and push?" || { say "Cancelled."; exit 0; }
  out=$(g merge-tree --write-tree origin/main "$b") || { say "$out" | head -15; die "Conflicts. Do not merge. Send it back to the worker/VDM to merge main into their branch."; }
  tree=$(printf '%s\n' "$out" | head -1)
  commit=$(g commit-tree "$tree" -p origin/main -p "$b" -m "Merge task $n") || die "commit failed"
  [ "${PC_DRY:-0}" = 1 ] && { say "(dry run: would push merge commit $commit to main)"; exit 0; }
  g push origin "$commit:refs/heads/main" && say "Merged and pushed. Next: pc deploy" \
    || die "Push refused. Someone else pushed to main a moment ago. Run the same command again."
}

cmd_deploy() {
  need
  want=$(want_tree) || die "could not build the deploy tree"
  have=$(g rev-parse "origin/${DEPLOY_BRANCH}^{tree}")
  [ "$want" = "$have" ] && { say "Live branch already matches main. Nothing to do."; exit 0; }
  say "Files that will change on the live site:"; g diff-tree -r --no-renames --name-status "$have" "$want" | head -40
  ask "Publish to ${DEPLOY_BRANCH} (goes live in about 45 seconds)?" || { say "Cancelled."; exit 0; }
  commit=$(g commit-tree "$want" -p "origin/${DEPLOY_BRANCH}" -m "Deploy: main $(g rev-parse --short origin/main)") || die "commit failed"
  [ "${PC_DRY:-0}" = 1 ] && { say "(dry run: would push $commit)"; exit 0; }
  g push origin "$commit:refs/heads/${DEPLOY_BRANCH}" && say "Pushed. Check in ~45 s with: pc live /"
}

# For a worker whose chat cannot reach GitHub at all: the worker gives the CEO the finished files
# (a .zip or a folder laid out exactly like the repo, plus reports/task-[id].md) and the CEO puts
# them on that worker's branch with one command. No working copy and no big download needed.
cmd_import() {
  id="${1:-}"; src="${2:-}"
  [ -n "$id" ] && [ -n "$src" ] || die "Usage: pc import ID ZIP-or-FOLDER   e.g. pc import status-4 ~/storage/downloads/worker.zip"
  need
  tmp=$(mktemp -d)
  if [ -d "$src" ]; then cp -a "$src"/. "$tmp"/
  else command -v unzip >/dev/null || die "Run first: pkg install unzip"; unzip -q "$src" -d "$tmp" || die "could not unzip $src"; fi
  # If everything sits inside one wrapper folder (for example worker/), step into it.
  while [ "$(ls -A "$tmp" | wc -l | tr -d ' ')" = "1" ] && [ -d "$tmp/$(ls -A "$tmp")" ] && \
        ! echo "$(ls -A "$tmp")" | grep -qxE 'adopt|docs|petpay|reports'; do tmp="$tmp/$(ls -A "$tmp")"; done
  list=$(cd "$tmp" && find . -type f | sed 's#^\./##' | grep -v '^__MACOSX/' | sort)
  [ -n "$list" ] || die "No files found in $src"
  bad=$( { echo "$list" | grep -vE '^(adopt|docs|petpay|reports)/'; echo "$list" | grep -E '(^|/)(config\.php|pet\.json|\.env)$|/applications/|\.\./'; } | sort -u)
  [ -z "$bad" ] || { say "Refused. These paths are not allowed (only adopt/, docs/, petpay/, reports/; never config.php, pet.json, applications/, .env):"; echo "$bad" | head -20; exit 1; }
  br="task/$id"
  if g rev-parse -q --verify "origin/$br" >/dev/null; then base="origin/$br"; else base="origin/main"; fi
  idx="$tmp.index"
  GIT_INDEX_FILE="$idx" g read-tree "$base" || die "could not read $base"
  echo "$list" | while IFS= read -r f; do
    blob=$(g hash-object -w "$tmp/$f") || exit 1
    GIT_INDEX_FILE="$idx" g update-index --add --cacheinfo "100644,$blob,$f" || exit 1
  done || die "could not add the files"
  tree=$(GIT_INDEX_FILE="$idx" g write-tree --missing-ok) || die "could not build the folder listing"
  [ "$tree" = "$(g rev-parse "$base^{tree}")" ] && { say "Those files are already on $base. Nothing to do."; exit 0; }
  say "Branch $br will get these changes:"; g diff-tree -r --no-renames --name-status "$base^{tree}" "$tree" | head -40
  ask "Commit and push to $br?" || { say "Cancelled (nothing pushed)."; exit 0; }
  commit=$(g commit-tree "$tree" -p "$base" -m "Task $id: files delivered through the CEO (worker had no GitHub access)") || die "commit failed"
  [ "${PC_DRY:-0}" = 1 ] && { say "(dry run: would push $commit to $br)"; exit 0; }
  g push origin "$commit:refs/heads/$br" && say "Pushed $br. Tell the VDM the branch is ready for review."
}

cmd_live() {
  p="${1:-/}"; code=$(curl -s -o /dev/null -w '%{http_code}' "${SITE}${p}?v=$(date +%s)")
  say "${SITE}${p}  ->  HTTP ${code}   (200 = live, 404 = not there)"
}

case "${1:-help}" in
  setup)  cmd_setup ;;
  status) cmd_status ;;
  show)   shift; cmd_show "$@" ;;
  merge)  shift; cmd_merge "$@" ;;
  deploy) cmd_deploy ;;
  import) shift; cmd_import "$@" ;;
  live)   shift; cmd_live "$@" ;;
  *) sed -n 2,20p "$0" ;;
esac
