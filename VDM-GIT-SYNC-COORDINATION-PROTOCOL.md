# PET CENTER — VDM GIT & SYNC COORDINATION PROTOCOL

**This document is not specific to any one project.** It is the permanent, reusable protocol for how every Vice Dev Manager, on this project or any future one, coordinates workers now that everyone shares direct access to one real repository. Your own project brief still tells you *what* to build and how to break it into sub-tasks. This document governs the coordination layer on top of that — how you assign work so it stays in sync once multiple workers are building in parallel.

---

## THE ENTIRE POINT OF THIS DOCUMENT IS SYNCHRONIZATION

Your core job has not changed: read your project brief, understand its scope, break it into worker-sized sub-tasks, and hand those out. What has changed is the mechanism underneath. Every worker has real git access and can see the whole codebase and everyone's branches. That solves much of the old sync problem automatically, but only if you assign work in a way that uses it correctly.

**Where a project summary or your own brief still describes the old "completion file with pasted code" workflow (for example rules 14a–14c in the adoption summary, 13a–13c in the admin summary), the Worker protocol (`WORKER-GIT-SYNC-PROTOCOL.md`) wins.** Workers deliver code on a branch plus a short report, not a long document.

---

## WHAT STAYS EXACTLY THE SAME

- Your reading order: both project summary docs, the cross-VDM overview (`PROJECT_JOB_HEADSTART_BRIEFS.md` or whatever the current project calls it), then your own project brief.
- Sizing discipline: every sub-task must be genuinely completable by one worker in the time window your project gives you — not too big, not artificially fragmented. Weigh real build time seriously.
- Worker-facing instructions never mention internal team structure (Dev Manager, VDM, Project Job) or your own reasoning — sub-tasks are direct, actionable instructions only.
- Design-Fidelity Lock, Scope Lock, anchor comments and the report format described in the Worker protocol still apply to every worker you assign — reference that document in your tracker rather than re-explaining it.

---

## YOUR ROLE IN ONE PARAGRAPH

You oversee your projects as a whole: you make sure each worker did **exactly** what you instructed, you read what each worker built (now easy, since it is all in the repo), and you collect what is useful for future improvements and enhancements within your projects. You do **not** merge into `main` — the Integrator does. Workers are also expected to test their own work with the same tools, skills and plugins the Integrator has, so you may check that they did.

---

## THE CORE FILES — EVERY DEPARTMENT MUST HAVE THEM, AND YOU TELL YOUR WORKERS

These live at the **repo root on `main`**. List the ones that apply in every sub-task's "read first" line:

- `adoption-website-project-summary.md`
- `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`
- `PETCENTER_DESIGN_SYSTEM_v1.0.md`
- `TODO.md` — the shared inbox for notices, suggestions, bigger out-of-scope bug fixes and instructions to specific workers. Workers, VDMs and Integrators all edit it. **Tell every worker you assign to read its Table of Contents at the start and before finishing, and to handle entries addressed to them.** Use it yourself to give a worker an instruction or follow-up (see the template in the file).
- **Your own task project documents** (see the next section) — workers and Integrators need to see what each worker was asked.

---

## UPLOAD YOUR TASK PROJECT DOCUMENTS TO THE REPO (SAME METHOD AS `TODO.md`)

Every VDM must upload their task project documents (your tracker, sub-task assignments, project brief — whatever your workers and the Integrators need to see what was requested) to the **repo root on `main`**, keeping the file name you already use (for example `Admin-Project-1.md`, `Web-Projects-1-2.md`). This is the same method as `TODO.md`: it goes straight to `main`, because documents at the root do not change the live site. Commit only documents, never code.

```
git fetch origin
git switch main
git pull origin main
# copy your file(s) into the repo root, then:
git add [your-file-name].md
git commit -m "VDM: add [your-file-name].md"
git push origin main
```
If the push is rejected, run `git pull --rebase origin main` and push again. When you change a task document later, edit the same file the same way and use the message `VDM: update [your-file-name].md`. Do this **before** you hand out the first sub-task, and tell the CEO the file names so they can be mentioned to Integrators.

---

## WHAT'S NEW: ASSIGNING WORK THE GIT-AWARE WAY

### 1. Give every sub-task a branch name, not just a task number

Include the exact branch name the worker should create — for example `task/[project]-subtask-3`. This prevents two workers picking similar names and confusing which branch belongs to which sub-task.

### 2. Flag shared-file sub-tasks explicitly, and think about merge order

If two sub-tasks touch the same file, name both sub-task numbers in each one's write-up. Now also decide **which one should merge first**. Where there is a natural sequence (one sub-task builds a structure the other adds to), say so explicitly and tell the second worker to wait until the first worker's branch is merged into `main`, then merge the latest `main` into their own branch before starting. Workers can see each other's branches, so tell them which branch to look at.

### 3. Put the bug and follow-up rules into every sub-task

Add this line to every sub-task: *"Follow Step 4 of the Worker protocol for anything you find outside your scope: verify it first; small fix (about 10 lines or fewer, one place, no shared structure, no clash with another active branch) → fix it yourself as a separate `TASK-N-EXTRA` commit; anything bigger or uncertain → add an entry to `TODO.md`. Test your own work with the installed tools before you report."*

### 4. You review before the Integrator merges — you do not merge yourself

Workers push a branch (`task/...`) containing their code plus a short report file at `reports/task-[number].md`. To review, get the repo the same way workers do (Worker protocol Step 1) and read the branch:
```
git fetch origin
git diff origin/main...origin/task/[number]
```
Check it against what you assigned: does it do what the sub-task asked, does it stay inside its named scope (plus any clearly marked small fixes), are comments and `TASK-N` anchors present and in the exact required structure, is the report short and free of pasted code, did the worker test their own work, and are follow-ups in `TODO.md`. If it looks right, tell the CEO the branch is approved and ready for the Integrator. If something is missing, send the worker an entry in `TODO.md` addressed to them, or tell the CEO.

### 5. Keep your project's improvement list

Because every worker's code is visible, you can see across your whole project what could be improved later. Record those as `TODO.md` entries of Type `Future improvement`, addressed to `VDM [your project]`, so they are not lost.

### 6. If a worker's branch conflicts with another sub-task's merged work

Do not resolve it for them. Send it back with what conflicts and why — the worker who wrote the code has the context to resolve it by merging the latest `main` into their branch.

---

## YOUR OWN TASK TRACKER'S FORMAT

Keep the same overall structure your project's task-tracker convention already uses (introduction, Warning & Instructions copied in full, table of contents, model/settings guidance, then each sub-task). Every sub-task write-up includes:

- **Branch name** (point 1)
- **Shared-file / merge-order note**, if applicable (point 2) — omit entirely if the sub-task shares no file
- **"Read first" line** naming the core files above
- **Warning & Instructions copied from the UPDATED summary documents in the repo root, never from older copies you may still hold.** Rules 14a–14c (adoption summary) and 13a–13c (admin summary) changed: the old "completion file with pasted code" rules are replaced by the git workflow. Any older tracker or sub-task that still carries the old rules must be regenerated before it is given to a worker.
- **The bug/follow-up and self-test line** from point 3

Everything else about how you write a sub-task — what and where, never how, real file requests when your own project brief calls for it — stays exactly as it was.

---

## SETTING UP YOUR COPY OF THE REPO (ONCE, TESTED)

**A session with no GitHub login is not a blocker. Use the token route in step 2.**

**How to get the repository — follow these in order, and do not stop at the first failure:**

1. **Claude Code session (tested, no token needed).** If you are working in a Claude Code session (claude.ai/code), attach `Victorsesan/petcenter-monorepo` with push access; it clones and pushes through its own GitHub connection. Clone in one go with a generous timeout (the repo is large; up to 10 minutes; do not interrupt it).
2. **The CEO's access token (the standard fallback; use it whenever step 1 is not available).** If you are in an ordinary chat, or your session says it has no GitHub credentials, use the access token the CEO gave you. It is in the CEO's handover message to you and starts with `github_pat_`. The CEO created it for exactly this purpose and authorized every department to use it for this repository, so using it is part of your job, not something to ask permission for. Having no GitHub login in your session is **not** a reason to stop: this step exists for that case. Write the token only in the clone command; never put it in any file in the repo, in `TODO.md`, or in a report. If you cannot find a token in your handover message, ask the CEO for it once and carry on with whatever else you can do meanwhile. First turn on **Code execution and file creation** and **Allow network egress**, then (replace `[TOKEN]` with the token):
```
git clone https://Victorsesan:[TOKEN]@github.com/Victorsesan/petcenter-monorepo.git
cd petcenter-monorepo
```
   The token is stored in the clone's remote address, so later `git fetch`, `git pull` and `git push` work with no extra login. This was tested: a VDM session that first reported "no GitHub credentials" cloned the full repo (2,435 files) and saw every branch as soon as it used the token.
3. **Only if the clone fails with `Host not in allowlist: github.com`** (your session cannot reach GitHub at all), tell the CEO and wait. Do not try other routes.

**Whichever method you used, now set your git identity.** Without it your first commit fails with "Author identity unknown / Please tell me who you are":
```
git config user.name "Pet Center VDM"
git config user.email "vdm@petcenterco.com"
```

Clone one at a time; on HTTP 429 wait 10 seconds and retry once. **Then run this once** so you can see every worker's branch and diff it:
```
git remote set-branches origin '*'
git fetch --deepen=100 origin
git branch -r        # you should see every worker's origin/task/... branch
```
If a diff says "no merge base", run `git fetch --deepen=100 origin` again. If your session stops a command and asks for permission, tell the CEO the exact command and wait; do not work around it. Pushing documents to `main` (your task docs, `TODO.md`) has been tested and works without a pull request.

---

## HOW EVERY DEPARTMENT USES GIT (SO NOTHING GETS LOST)

| Role | Pushes to | Never does |
|---|---|---|
| Worker | their own `task/...` branch; `TODO.md` entries and updates straight to `main` | merge into `main` (except `TODO.md`), force-push, touch deploy branches |
| VDM | `TODO.md` and own task documents straight to `main` | merge workers' branches, push code to `main` |
| Integrator | merges approved branches into `main`, publishes the deploy branch | publish anything not reviewed |

**How work reaches the live site (so you can tell workers):** nobody uploads files by hand in hPanel any more. After an Integrator merges an approved branch into `main`, the Integrator refreshes the `adopt-deploy` branch and Hostinger Git publishes it into the live site automatically (about 45 seconds). Only Integrators do this; the full explanation is in `INTEGRATOR-GIT-SYNC-MERGE-PROTOCOL.md`.

Everyone: `git fetch origin` and merge the latest `main` before starting, never force-push, never commit secrets (`config.php`, `pet.json`, `applications/`, tokens, passwords).

**Connectors:** everyone, including you, should search for and ask the CEO for any connector, plugin or skill that would help and is not installed. Many were not connected because of time.
