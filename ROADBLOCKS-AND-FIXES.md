# PET CENTER — ROADBLOCKS AND FIXES (READ THIS BEFORE YOU RUN YOUR FIRST COMMAND)

Everything below was **actually hit and solved** while the shared repo, the light clone, the Hostinger auto-deploy and the three role protocols were set up and tested. Every department follows the same steps, so every department will meet the same walls. Each entry says what you see, why, and the exact fix. **Do the fix straight away. Do not spend time or tokens rediscovering it.** Where a fix is "ask the CEO", ask once and carry on with other work while you wait.

If you meet a wall that is **not** listed here, solve it, then add an entry to `TODO.md` addressed to `ALL` (type Notice) with the symptom and the fix, so the next department does not lose time on it.

**The one thing every department must do:** keep your work in the shared repo (`Victorsesan/petcenter-monorepo`) so every other department can see it, read it and, when needed, change it. Everything else (merging into `main`, publishing to the live site) is done by the CEO from a phone with one command each; you never need to work out how.

---

## 0. WHO CAN SEE AND CHANGE WHAT (OPEN ON PURPOSE)

- **Everyone can read everything:** every branch, every report, `TODO.md`, all documents. Run `git fetch origin` and `git branch -r` to see all branches.
- **Everyone's access is write access.** Use it with these limits so work does not get overwritten: push to **your own** branch; change **another department's** branch or files only when a `TODO.md` entry addressed to you says to, or the CEO tells you to (an entry addressed to you counts as permission); never force-push; never rewrite someone else's history.

---

## 1. GETTING THE REPOSITORY

| What you see | Why | Fix (tested) |
|---|---|---|
| Your session says "no GitHub credentials", or a clone asks for a username/password | Ordinary chats and some Claude Code sessions have no GitHub login of their own | Use the CEO's access token from your handover message in the clone address (protocol Step 1, point 2). A VDM session that first said "no credentials" cloned all 2,435 files this way. Never write the token into a file, `TODO.md` or a report. |
| Clone takes many minutes, or is huge (about 330 MB downloaded, 640 MB on disk) | The repo holds about 600 MB of videos and photos; the code is only about 18 MB | Use the **light clone** from your protocol: `git clone --filter=blob:limit=1m --no-checkout ...` then the `sparse-checkout` lines, then `git checkout main`. Measured: about 3 seconds, about 25 MB of files. All branches and history are still there. |
| You need one photo or video that the light clone left out (for example to check a design) | The light clone skips `.mp4 .jpg .jpeg .png .webp .ttf .afm .ufm` | `git sparse-checkout add "/adopt/path/to/file.jpg"` (tested: the file appears). Local browser tests show broken images for the skipped ones; that is expected. |
| `HTTP 429` or "Too many concurrent git operations" | Only one clone at a time is allowed | Wait 10 seconds and retry **once**. Never start two clones in parallel. Use a timeout of up to 10 minutes for a full clone. |
| `Host not in allowlist: github.com` | Your session cannot reach GitHub at all | Nothing you can change. Tell the CEO and wait. Do not try other routes. |
| You are on a free account without Claude Code | Some worker accounts only have ordinary chat | Ordinary chat works: turn on **Code execution and file creation** and **Allow network egress**, then use the token clone. |

## 2. SEEING OTHER DEPARTMENTS' BRANCHES

| What you see | Fix (tested) |
|---|---|
| `git branch -r` shows only `origin/main` | The clone is shallow and single-branch. Run `git remote set-branches origin '*'` then `git fetch --deepen=100 origin`. On a light clone this is harmless. |
| `fatal: ... no merge base` when running `git diff origin/main...origin/task/N` | Same cause. Same fix, then repeat the diff. |
| `git fetch origin task/3` appears to work but `origin/task/3` is still unknown | A plain fetch of one branch only sets `FETCH_HEAD`. Use `git fetch origin task/3:refs/remotes/origin/task/3`, or the fix above. |
| `git switch adopt-deploy` says "invalid reference" | The branch was never fetched. Run the fix in the first row, or `git fetch origin adopt-deploy:refs/remotes/origin/adopt-deploy`, then switch. |
| Your project number collides with another project's branch (`task/3` already exists for a different VDM) | Branches are now **project-scoped: `task/[project]-[number]`**, for example `task/status-1`. Check `git branch -r` before you name one. The report is `reports/task-[project]-[number].md` and anchors are `TASK-[project]-[number]`. Old branches such as `task/3` keep their names. |

## 3. COMMITTING AND PUSHING

| What you see | Fix (tested) |
|---|---|
| Commit fails: "Author identity unknown" / "Please tell me who you are" | Run `git config user.name "Pet Center [Role]"` and `git config user.email "[role]@petcenterco.com"` right after cloning, whichever way you cloned. This stopped a VDM halfway through a task. |
| `error: cannot pull with rebase: You have unstaged changes` | Commit or `git stash` your edits first, then `git pull --rebase origin main`. |
| Push rejected | `git fetch origin`, merge (or `git pull --rebase origin main`), resolve, push again. Never force-push. |
| Two people added a `TODO.md` entry at the same moment | `git pull --rebase origin main`, keep **both** entries, renumber yours to the next free number, fix your Table of Contents row, `git add TODO.md`, `git rebase --continue`, push. |
| You edited `TODO.md` on your task branch | Don't. `TODO.md` is edited only on `main`. Commit your own work first, `git switch main`, pull, make the entry, push, then `git switch` back to your branch (steps are in `TODO.md`). |
| Wrong or missing file names in `reports/` | Report name = the branch id: `task/status-1` gives `reports/task-status-1.md`. |

## 4. YOUR SESSION STOPS A COMMAND (SAFETY CHECKS)

These happen in Claude Code sessions. They are safety checks, not bugs.

| What you see | What it means and the fix |
|---|---|
| A push to `adopt-deploy` (or any deploy branch) is blocked as a "Production Deploy" | Only the CEO publishes now. Do not push deploy branches. If you are an Integrator told to do it, give the CEO the exact command and wait for the approval prompt. |
| A push of documents is blocked with a reason such as "Instruction Poisoning" | Happened on 2026-10-12 when documents told other AI sessions to use a credential. Typing "approved" in chat did **not** clear it; the same push went through once the CEO approved the permission prompt and it was run again. Tell the CEO the exact command and wait. Keep documents plain and factual. Do not split the commit, rename things or take another route to slip past it. |
| A stop message says "There are N unpushed commit(s)" | Your push is waiting for approval. Tell the CEO the exact command, wait, then run the same command again. |
| "The safety check is temporarily unavailable (timed out)" | Wait a minute and run the same command again. |
| A command that rewrites a document with a script is blocked, but you only wanted to edit text | Make the same edit with the file-editing tool (Edit). That is the normal way to change text and was never blocked. |

The rule for all of these: **tell the CEO the exact command, wait, run it again. Never disguise it or reach the same result another way.**

## 5. THE LIVE SITE AND HOSTINGER

How it works: worker branch → CEO merges into `main` → CEO publishes `adopt-deploy` → Hostinger Git deploys it into `public_html/adopt` about 45 seconds later. Nobody uploads by hand in hPanel any more.

| What you see | Fix / fact (tested) |
|---|---|
| Your shell cannot open the live site (`curl` returns `HTTP 000`) | Normal: the work sandbox cannot reach it. Check with your page-fetch or browser tool and add a changing `?v=1`, `?v=2` to the URL (pages are cached about 15 minutes). The CEO checks from Termux with `pc live /path` or over SSH with `curl -s -o /dev/null -w "%{http_code}\n" https://adopt.petcenterco.com/[path]` (200 = live, 404 = not there). |
| The homepage text shows "Loading pets…" | The page fills in with a script, so a plain page fetch cannot see the pets. That is not a failed deploy. Check in a real browser. |
| Blog cards on the homepage have no pictures | Real, older issue: only `blog1-grid.jpg` exists in the repo, while the data names `blog1` to `blog12`. Not caused by deploys. Tracked for a worker through `TODO.md`. |
| hPanel Git page shows an old "manual" deployment from 2026-09-24 | Normal history. Auto-deployment is switched on for branch `adopt-deploy`, directory `public_html/adopt`. |
| Files that exist only on the server (`config.php`, `pet.json`, `applications/`) | Tested: they survive every deploy because they are gitignored. Never add them to the repo. |
| A file deleted on `main` is still live | Deploys replace the whole folder contents, so refresh the deploy branch from `main` (CEO: `pc deploy`). A test file deleted this way disappeared from the live site. |
| Root documents (`TODO.md`, protocols, summaries) appeared in a deploy branch | Never commit them there. Only the contents of `main`'s `adopt/` folder (plus `.gitignore`) belong on `adopt-deploy`. |
| Manual flatten method (Integrator, if the CEO does not use `pc`) | `git switch adopt-deploy; git pull origin adopt-deploy; git rm -rf -q .; git archive main adopt \| tar -x --strip-components=1; git show main:.gitignore > .gitignore; git add -A; git commit; git push origin adopt-deploy`. Needs the full file contents, so it downloads the media; the CEO's `pc deploy` avoids that by building the tree from git objects (tested: no download). |

## 6. DOCUMENTS AND PROCESS

| What you see | Fix |
|---|---|
| Your copy of a protocol or summary says to write a long "completion file with pasted code" (rules 14a–14c adoption, 13a–13c admin) | Old copy. The git workflow replaced it: code on your branch plus a short report (about 80 lines, no pasted code). The old rule produced 961- and 1,539-line reports. **The files attached by the CEO and the copies at the repo root win over anything older.** |
| Rule 6 ("Write code comments") is missing from your adoption summary | Fixed on 2026-10-12 (it was missing in the original). Use the current repo copy. |
| A document is missing, or the design system will not be found | The design system must be named exactly `PETCENTER_DESIGN_SYSTEM_v1.0.md` (dot, not underscore; some uploads turn the dot into `_`). All core files are at the repo root on `main`: both summaries, the design system, `TODO.md`, this file, the three protocols, and the VDM task documents. |
| The design system says to check an "SVG Icon Library doc" first and you cannot find it | It has not been added to the repo yet. Ask the CEO for the icon you need; do not source or hand-draw icons yourself. |
| Skills or plugins are missing in your account | Claude cannot install them for you. Tell the CEO which you need; the CEO adds them in the app. |
| You are unsure whether something outside your task is yours to fix | Verify it first. About 10 lines or fewer in one place, no new file, no clash with another branch: fix it yourself as a `TASK-[id]-EXTRA` commit and list it in your report. Anything bigger: a `TODO.md` entry. |

## 7. NEVER

- Never force-push, never commit secrets (`config.php`, `pet.json`, `applications/`, tokens, passwords) and never paste the token into a file, `TODO.md` or a report.
- Never push to a deploy branch unless the CEO told you to; never edit a project summary directly.
- Never disguise a blocked command or reach the same result another way. Tell the CEO the exact command and wait.
