# PET CENTER — WORKER GIT & SYNC PROTOCOL

**This document is not specific to any one project.** It is the permanent, reusable protocol for how every worker on every current and future Pet Center project writes and shares code. Your task tells you *what* to build; this document tells you *how your work is shared, kept in sync with everyone else, and checked.*

---

## THE ENTIRE POINT OF THIS DOCUMENT IS SYNCHRONIZATION

Dozens of separate AI sessions, with no memory of each other, build one product together. To make that work, **everyone reads and writes the same real repository** instead of passing code around in documents. You can see every other worker's latest work, and they can see yours. No pasting code into completion files, no manual re-typing by anyone.

**If anything in a project summary document (for example the old "completion file" rules 14a/14b/14c in the adoption summary or 13a/13b/13c in the admin summary) conflicts with this document about how work is delivered, this document wins.** In particular: you do **not** write a long completion document with pasted code, and you do **not** create a separate file for a second round. Everything else in those summaries (Scope Lock intent, anchor comments, design rules, hamburger/header rules, testing tools) still applies in full.

---

## THE REPOSITORY (already set up — you only use it)

- Repo: `Victorsesan/petcenter-monorepo` on GitHub.
- Folders on `main`: `adopt/` (public adoption site and admin dashboard), `docs/`, `petpay/`, `reports/`.
- **Core files at the repo root that every department must have read:**
  - `adoption-website-project-summary.md` — how the public adoption site works
  - `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` — how the admin dashboard works
  - `PETCENTER_DESIGN_SYSTEM_v1.0.md` — the design system; the visual source of truth
  - `TODO.md` — the shared inbox (see below)
  - `ROADBLOCKS-AND-FIXES.md` — every wall already hit while setting this up, with the exact fix. **Skim it before your first command** so you do not waste time and tokens on a problem that is already solved.
  - the **task project documents** for your project, uploaded by your VDM at the repo root (they tell you exactly what each worker was asked to do, and Integrators use them to check your work)
  - the three protocol documents (`WORKER-…`, `VDM-…`, `INTEGRATOR-…`)
- Server-only private files (`config.php`, `pet.json`, `applications/`) are **not** in the repo and must **never** be added to it. Never put tokens, passwords or personal data in any file, report, comment or `TODO.md` entry.
- Live deployment is separate: merging into `main` alone does not change the live website. The Integrator publishes it. You never touch the deploy branches (`adopt-deploy`, `docs-deploy`, `petpay-deploy`).

---

## WHAT EACH DEPARTMENT DOES (SO YOU KNOW WHO WILL LOOK AT YOUR WORK)

- **Workers (you):** write the code and comments for your task, test it yourself, and publish it (finished or still in progress) on your own branch while staying in sync with every other worker.
- **VDMs:** hand out the tasks, check that each worker did exactly what they were asked, and keep track of improvements for their projects.
- **Integrators:** the final inspectors. They read what your task asked for, then go through your code line by line with the same tools you have, and only then approve it for the live site.

**You are not allowed to rely on the Integrator to catch your mistakes.** You have the same tools, skills and plugins they have. Test your own work properly (see "Test your own work" below). The Integrator is the perfectionist final check, not your first test.

---

## STEP 1 — SETUP, EVERY SESSION

**How to get the repository — follow these in order, and do not stop at the first failure:**

1. **Claude Code session (tested, no token needed).** If you are working in a Claude Code session (claude.ai/code), attach `Victorsesan/petcenter-monorepo` with push access; it clones and pushes through its own GitHub connection. Clone in one go with a generous timeout (the repo is large; up to 10 minutes; do not interrupt it).
2. **The CEO's access token (the standard fallback; use it whenever step 1 is not available).** If you are in an ordinary chat, or your session says it has no GitHub credentials, use the access token the CEO gave you. It is in the CEO's handover message to you and starts with `github_pat_`. The CEO created it for exactly this purpose and authorized every department to use it for this repository, so using it is part of your job, not something to ask permission for. Having no GitHub login in your session is **not** a reason to stop: this step exists for that case. Write the token only in the clone command; never put it in any file in the repo, in `TODO.md`, or in a report. If you cannot find a token in your handover message, ask the CEO for it once and carry on with whatever else you can do meanwhile. First turn on **Code execution and file creation** and **Allow network egress**, then (replace `[TOKEN]` with the token):
```
git clone --filter=blob:limit=1m --no-checkout https://Victorsesan:[TOKEN]@github.com/Victorsesan/petcenter-monorepo.git
cd petcenter-monorepo
git sparse-checkout init --no-cone
printf '/*\n!*.mp4\n!*.jpg\n!*.jpeg\n!*.png\n!*.webp\n!*.ttf\n!*.afm\n!*.ufm\n' | git sparse-checkout set --no-cone --stdin
git checkout main
```
   This is the **light clone**: it takes about 3 seconds and about 25 MB instead of several minutes and 600+ MB, because the repo holds about 600 MB of videos and photos that code work does not need (the code itself is about 18 MB). Every branch and the full history are still there. If you really need one excluded file (for example a photo you must look at), fetch just that one: `git sparse-checkout add "/adopt/path/to/file.jpg"`.
   The token is stored in the clone's remote address, so later `git fetch`, `git pull` and `git push` work with no extra login. This was tested: a VDM session that first reported "no GitHub credentials" cloned the full repo (2,435 files) and saw every branch as soon as it used the token.
3. **Only if the clone fails with `Host not in allowlist: github.com`** (your session cannot reach GitHub at all), tell the CEO and wait. Do not try other routes.

**Whichever method you used, now set your git identity.** Without it your first commit fails with "Author identity unknown / Please tell me who you are":
```
git config user.name "Pet Center Worker"
git config user.email "worker@petcenterco.com"
```
**Right after cloning, run this once.** A shallow clone only knows about `main`, so without it you cannot see other workers' branches, and `git diff origin/main...origin/task/N` fails with "no merge base". This was tested from a fresh clone:
```
git remote set-branches origin '*'
git fetch --deepen=100 origin
git branch -r        # you should now see origin/main, origin/adopt-deploy and every worker's origin/task/... branch
```

Then: read your assigned task in full and note your **task number** — your branch, commits, comments and report all use it. Read the core files listed above that apply to your task **before you write anything**, then read the Table of Contents of `TODO.md` and open every entry addressed to you or to `ALL`.

---

## STEP 2 — ONE BRANCH PER TASK

Never work directly on `main`. Create your own branch, using the **exact branch name your VDM's tracker gives you**. Branch names are **project-scoped** so two projects can never collide: `task/[project]-[number]`, for example `task/status-1` or `task/geo-3b`. (Branches that predate this rule, such as `task/3`, stay as they are.)
```
git checkout -b task/[project]-[number]
```
One branch, one task.

**Wherever this document, a summary or a task says `task/N`, `task/[task-number]`, `reports/task-N.md` or `TASK-N` anchors, the "N" is that same project-scoped id.** So for the branch `task/status-1` your report is `reports/task-status-1.md` and your anchors are `TASK-status-1 START` / `TASK-status-1 END` (and `TASK-status-1-EXTRA`). If your VDM gave you a plain number with no project name, ask your VDM or the CEO for the scoped name before you create the branch.

---

## STEP 3 — GET THE LATEST FROM `main` BEFORE YOU START, AND AGAIN BEFORE YOU FINISH

```
git fetch origin
git merge origin/main
```
Do this before writing anything, and again right before you finish. It pulls in everyone else's merged work **and the latest `TODO.md`**, so you never build on a stale copy. If a conflict appears, resolve it yourself — you have the context of what you just built. When resolving, **keep every `TASK-N START/END` anchor comment from both sides.**

### Seeing what other workers are doing right now

Everyone's branches are visible to everyone. At any time (and always before touching a file another task also edits):
```
git fetch origin
git branch -r                                          # lists every worker's branch
git diff origin/main...origin/task/[other-number]      # see what that worker has changed so far
```
Push your own commits often so others can see yours the same way.

### Finding your files in the repo

Your task names files the way the site calls them (for example `cats.html`, `pets.html`), but in the repo they sit in folders and the real names can differ. Search the repo (case-insensitively, `find . -iname "*cats*"`) and check the file-structure section of the project summary. Example: the live category pages are `adopt/pets/<category>/index.html` (for example `adopt/pets/cats/index.html`), and `pets.html` is `adopt/pets/index.html`. Edit the file that is actually live, never a leftover copy (such as `Cats.html` or anything in a `-backup` or `Old` folder). If two files could both be right, do not guess: ask the CEO. A brand-new file goes where its URL implies (for example `/adoptions/pets/geo-filter.php` → `adopt/adoptions/pets/geo-filter.php`).

---

## STEP 4 — SCOPE LOCK, AND WHAT TO DO WITH BUGS OUTSIDE YOUR SCOPE

You can read every file in the repo. **You edit only the files your task names**, with one exception described below. If your task genuinely requires editing a file holding credentials (for example `config.php`), say so specifically in your report instead of editing it.

### When you notice a bug or problem outside your scope

1. **Verify it first.** Because you can see the whole codebase and everyone's work, check that it is real: read the code around it, run it, check whether another branch already fixed it (`git branch -r`, `git diff origin/main...origin/task/[other-number]`), and check `TODO.md` in case it is already reported. Do not report or fix something you have not confirmed.
2. **If it is NOT real,** note in your report that you checked and why it is a false alarm.
3. **If it IS real and the fix is small, fix it yourself.** A fix is **small** only if **all** of these are true:
   - it is about 10 changed lines or fewer, in one place in one file;
   - it creates no new file and changes no shared structure (header, footer, hamburger menu, shared CSS/JS, database or API format);
   - no other active branch is editing the same lines (check with `git diff origin/main...origin/task/[other-number]`);
   - it does not touch credentials or server-only files.

   Make the fix as its **own separate commit**, wrapped in anchor comments `TASK-[N]-EXTRA START` … `TASK-[N]-EXTRA END` with a comment saying what it fixes and why, and the commit message `Task [N]: out-of-scope fix — [what]`. List it in your report under "Small out-of-scope fixes made".
4. **If it is real but bigger than that — even slightly — or you are not sure, do not fix it.** Add an entry to `TODO.md` (see that file for the template and the exact steps), addressed to whoever should handle it (the worker whose task owns that file, the VDM, or the Integrator), with the file path, line range, branch, how to reproduce it, and the severity. List the entry number in your report.
5. **If something outside your scope is blocking your own task,** raise it in `TODO.md` addressed to the VDM and tell the CEO. Do not work around it by editing other files.

### Entries addressed to you in `TODO.md`

If a worker, VDM or Integrator addressed an entry to you, that is your instruction. Do it, even if the files it names are outside your original task, and use `TODO #N` in your commit message. Update the entry's `Status` when done.

---

## STEP 5 — COMMITS AND COMMENTS (COMMENTS ARE MANDATORY AND THE STRUCTURE IS EXACT)

- Small commits, each doing one clear thing; every commit message starts with your task number (for example `Task 9: add filter modal`).
- **Comments are required on everything you touch**, in the exact structure your task and the project summary's Warning & Instructions require: what the code does and **why**, wrapped in `TASK-N START` / `TASK-N END` anchor comments. Never strip, shorten, reword, reorder or "tidy" existing comments or anchors, yours or anyone else's.
- Push regularly so others can see your progress:
  ```
  git push origin task/[task-number]
  ```
  Pushing your own task branch is always allowed. If a push is rejected, run `git fetch origin` and `git merge origin/task/[task-number]` (or `origin/main` as in Step 3), resolve any conflict, and push again. **Never force-push. Never rewrite another worker's branch.**

---

## TEST YOUR OWN WORK (REQUIRED BEFORE YOU REPORT)

You have the same tools, skills and plugins as the Integrator. Before your final push, test and review your own work with them: the Playwright headless browser for visual checks and behaviour, and the installed code-review, security-audit and WordPress/PHP skills and plugins that apply to what you built (for example, check PHP for unescaped output, unsafe input and missing validation; check JavaScript for console errors; check pages at phone width). Fix what you find in your own scope.

**If a connector, plugin or skill would help your task and is not installed, search for it and tell the CEO exactly which one you need.** Many were not connected because of time; do not struggle without a tool that exists.

---

## STEP 6 — YOUR REPORT FILE (SHORT — IT NEVER CONTAINS PASTED CODE)

The code is in your branch, so the report is **not** a code document. Create `reports/task-[task-number].md` with these sections, in about **80 lines or fewer**:

```
## Task Number
[your task number, quoted in full]

## What Was Required
[short restatement of your task's scope, in your own words]

## What I Built
[what you did and why — decisions, trade-offs, anything unusual. No pasted code: name the files and anchors instead]

## Files Changed
[table: file | shared with another task? (name the task numbers)]

## Small Out-of-Scope Fixes Made
[each fix, with file and commit — or "None."]

## Bugs and Follow-ups Sent to TODO.md
[entry numbers and one line each — or "None."]

## Things the Next Person Must Know
[only what is STILL needed after someone reads your code and comments: gotchas, merge-order notes, things to re-test. Everything that belongs next to code must instead be written as a comment in the code itself.]

## Self-Check Confirmation
[confirm you only touched files inside your scope (plus the listed small fixes), that every block carries TASK-N anchor comments, that you tested your work with the tools, and that you merged the latest main before finishing]
```

**Where information goes (nothing important may be lost):** anything that explains a piece of code (why it is built that way, a trap, a dependency) goes into a **comment in the code**, in the required structure. Anything someone must **act on** goes into **`TODO.md`**. Everything else is dropped. Do not paste code, old/new code blocks or long file listings into the report.

Then commit and push:
```
git add -A
git commit -m "Task [task-number]: done + report"
git push origin task/[task-number]
```
Tell the CEO your branch name is pushed. **Do not merge into `main` yourself** — the Integrator does that after review. (The only thing you may commit straight to `main` is a `TODO.md` entry, using the steps in that file.)

---

## STEP 7 — DESIGN-FIDELITY LOCK STILL APPLIES IN FULL

If your task has a reference image or design source, it is the literal target. Cross-check against `PETCENTER_DESIGN_SYSTEM_v1.0.md`. Use the Playwright headless browser to visually verify your finished page against the reference before you push your final commit.

---

## STEP 8 — IF YOU'RE ASKED FOR A SECOND ROUND

Push new commits to the *same branch* and add a short "Round 2" section to the *same* report file (never a new file). Scope Lock and the bug rules above still apply in full.

---

## KNOWN ROADBLOCKS AND HOW TO GET PAST THEM (ALREADY HIT AND SOLVED — DO NOT REPEAT THEM)

| Problem | What to do |
|---|---|
| Clone is slow or seems stuck | The repo is large. Use a generous timeout (up to 10 minutes) and do not interrupt it. |
| Clone fails with HTTP 429 / "Too many concurrent git operations" | Run only **one** clone at a time. Wait 10 seconds and retry **once**. Do not start a second clone in parallel. |
| `git branch -r` shows only `origin/main`, or another worker's branch is missing | Run the setup block in Step 1 (`git remote set-branches origin '*'` then `git fetch --deepen=100 origin`). Note that `git fetch origin task/3` on its own only fills a temporary pointer and does **not** create `origin/task/3`. |
| `fatal: ... no merge base` when diffing | Run `git fetch --deepen=100 origin`, then repeat the diff. |
| Push rejected | `git fetch origin`, merge, resolve conflicts, push again. Never force-push. |
| Your session stops a command and asks for permission (for example a push) | Tell the CEO the exact command and wait for approval. Do not try to disguise it or reach the same result another way. Pushing your own `task/...` branch and your `TODO.md` entries normally works without any prompt. |
| You cannot reach the live website from the shell | That is normal. You do not deploy and nobody uploads files by hand any more: after an Integrator merges your branch into `main`, the Integrator refreshes the `adopt-deploy` branch and Hostinger publishes it automatically about 45 seconds later (details in `INTEGRATOR-GIT-SYNC-MERGE-PROTOCOL.md`). Visual checks use the Playwright browser on your local copy. |
| Your session says it has no GitHub credentials, or a clone asks for a username | Use the CEO's access token from your handover message (Step 1, point 2). Do not stop. |
| Commit fails with "Author identity unknown" / "Please tell me who you are" | Run the two `git config user.name` / `user.email` lines from Step 1, then commit again. |
| `Host not in allowlist: github.com`, `Could not resolve host: github.com`, or `host_not_allowed` | Your chat's sandbox cannot reach GitHub. Do not hunt for ways around it. Open a **new chat** and test `curl -s -o /dev/null -w "%{http_code}" https://github.com`. If it is still blocked, finish the work and hand the CEO one `.zip` laid out like the repo (including `reports/task-[id].md`); the CEO puts it on your branch with `pc import`. Details in `ROADBLOCKS-AND-FIXES.md`, section 1. |

---

## QUICK COMMAND REFERENCE

| What you want | Command |
|---|---|
| Get everyone's latest work | `git fetch origin` then `git merge origin/main` |
| Start your branch | `git checkout -b task/[number]` |
| See all branches | `git branch -r` |
| See another worker's changes | `git diff origin/main...origin/task/[other-number]` |
| Save your work | `git add -A` then `git commit -m "Task [number]: [what]"` |
| Share your work | `git push origin task/[number]` |
| Push rejected | `git fetch origin` → `git merge origin/task/[number]` → fix conflicts → push again (never force) |
| Add a TODO entry | the steps in `TODO.md` ("How to add or update an entry") |
