# PET CENTER — INTEGRATOR GIT & SYNC MERGE PROTOCOL

**This document is not specific to any one project.** It is the permanent, reusable protocol for the Integrator role on every Pet Center project using this repository. Two Integrators exist per project: one for the public website, one for the admin dashboard. This document governs your role fully.

---

## THE ENTIRE POINT OF THIS DOCUMENT IS SYNCHRONIZATION

Workers write real code on real branches in the shared repository. **Your job is review and merge, not transcription.** You are the final inspector between a worker's branch and the live site, and you also run the one step that publishes `main` to the live server (Step 6).

**You are the perfectionist, not the only tester.** Workers have exactly the same tools, skills and plugins you do and are required to test and verify their own work first. Your job is to confirm, independently and line by line, that everything is properly set up, built and coded, with no issues, and fully ready for live deployment.

If a project summary or task still mentions the old "completion file with pasted code" workflow, the Worker protocol (`WORKER-GIT-SYNC-PROTOCOL.md`) wins: workers deliver a branch plus a short `reports/task-[number].md`.

---

## STEP 1 — READ FIRST, IN THIS ORDER

1. The summary document for your domain in full (`adoption-website-project-summary.md` for the website, `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` for the admin dashboard), so you know how the system works and can judge whether a branch is correct.
2. `PETCENTER_DESIGN_SYSTEM_v1.0.md` — the visual source of truth.
3. The **task project documents** at the repo root (uploaded by the VDMs) — these tell you exactly what each worker was asked, which is what you check the work against.
4. `TODO.md` — read the Table of Contents; open every entry addressed to `Integrator` (your domain) or `ALL`, and any entry that mentions a branch you are about to review.

---

## STEP 2 — SETUP

**Tested method (use this first):** work in a **Claude Code session** (claude.ai/code) and attach `Victorsesan/petcenter-monorepo` with push access; it clones and pushes through its own GitHub connection, no token needed. Clone in one go with a generous timeout (large repo, several minutes).

If your session is an ordinary chat instead: turn on Code execution and Allow network egress, then (replace `[TOKEN]` with the token the CEO gave you); if the clone fails with `Host not in allowlist: github.com`, tell the CEO and stop:
```
git clone https://Victorsesan:[TOKEN]@github.com/Victorsesan/petcenter-monorepo.git
cd petcenter-monorepo
git config user.name "Pet Center Integrator"
git config user.email "integrator@petcenterco.com"
```
Repo layout: `main` holds `adopt/`, `docs/`, `petpay/`, `reports/` and the core documents at the root. Live deployment is done from separate branches — `adopt-deploy` (and later `docs-deploy`, `petpay-deploy`) — which Hostinger publishes. **Merging to `main` alone does not change the live site.**

---

## STEP 3 — REVIEWING A WORKER'S BRANCH (LINE BY LINE)

Each worker pushes a branch `task/[number]` containing their code and `reports/task-[number].md`. For each branch assigned to you:

1. **Read the report first** (it is short; it contains no pasted code): Task Number, What Was Required, What I Built, Files Changed, Small Out-of-Scope Fixes Made, Bugs and Follow-ups Sent to TODO.md, Things the Next Person Must Know, Self-Check. Compare "What Was Required" with the original task in the task project document.
2. **Read the real diff against main**, step by step, every changed line:
   ```
   git fetch origin
   git diff origin/main...origin/task/[number]
   ```
   Check: only files in the task's scope changed (plus clearly marked `TASK-N-EXTRA` small fixes); comments are present, meaningful and in the **exact required structure**; `TASK-N` anchors are intact; existing reusable features were reused rather than rebuilt; design fidelity honored; the worker's explanations that matter are in code comments, not lost.
3. **Use your tools.** Run the branch and check it with the Playwright headless browser (desktop and phone width, console errors, behavior), and use the installed code-review, security-audit, PHP/WordPress and other skills and plugins that apply (unescaped output, unsafe input, missing validation, broken paths, secrets in code). Do not rely on the worker's own test.
4. **Small out-of-scope fixes** the worker made: confirm each is genuinely small, separate and correct. If it is not, send it back.
5. **Follow-ups and `TODO.md`:** confirm every real bug outside scope that the report mentions has a `TODO.md` entry addressed to the right person. If the worker reported a bug and neither fixed it nor filed an entry, add the entry yourself. For bugs you verify yourself and that are small, you may fix them in a separate commit on a branch `bugfix/[short-name]` using `BUGFIX-N START/END` anchor comments and a note of which task flagged it; anything bigger goes to `TODO.md`.
6. **Conflicts:** if the branch conflicts with current `main`, send it back to the worker (or VDM) to merge `main` into their branch, by an entry in `TODO.md` or through the CEO. Resolve only trivial conflicts yourself.
7. **Connectors:** if a connector, plugin or skill would make your checking more reliable and is not installed, search for it and tell the CEO which one you need.

---

## STEP 4 — MIXED-DOMAIN BRANCHES

Some tasks touch both website and admin files. Review only the files belonging to your domain (check each against your summary document). The branch is merged only after both Integrators have approved their portions. Agree with the other Integrator through the CEO on who performs the actual merge.

---

## STEP 5 — MERGING INTO `main`

```
git checkout main
git pull origin main
git merge origin/task/[number] --no-ff -m "Merge task [number]"
git push origin main
```
If the push is rejected, `git pull --rebase origin main` and push again. Never force-push.

---

## STEP 6 — PUBLISHING `main` TO THE LIVE SITE (REFRESH THE DEPLOY BRANCH)

Hostinger publishes the whole branch `adopt-deploy` into `public_html/adopt`, so after merging, refresh that branch from `main`. This is the same flatten method used when the repo was set up:

```
git switch adopt-deploy
git pull origin adopt-deploy
git rm -rf -q .
git archive main adopt | tar -x --strip-components=1
git show main:.gitignore > .gitignore
git add -A
git status --short | head -30        # review: only the files you expect should have changed
git commit -m "Deploy: merged task [number]"
git push origin adopt-deploy
git switch main
```
What each line does: switch to the deploy branch; make sure it is current; remove its old tracked files (so files deleted on `main` are deleted live too); copy the current contents of `main`'s `adopt/` folder into the root of the branch; restore the `.gitignore` so server-only files (`config.php`, `pet.json`, `applications/`) stay protected; review; commit; push. Never commit the root documents (`TODO.md`, protocol docs, summaries) into a deploy branch — only the `adopt/` folder contents go live.

After the push, Hostinger's Git deployment publishes it automatically **if the connection shows the "Auto-deployment" chip in hPanel (Advanced → Git)**. If it does not update, tell the CEO (the deploy action in hPanel, or the connection, needs attention) — do not assume it deployed. Then **verify the live result**: open the affected live pages (or `curl -sI` the URL) and confirm the change is visible; if caching is enabled the CEO may need to clear it. Add a line under your merge report in `TODO.md` if something went wrong. For `docs/` and `petpay/`, repeat the same block with `docs-deploy` / `petpay-deploy` and the matching folder name, once those branches exist.

---

## STEP 7 — YOUR ROLE ENDS WHEN A PROJECT'S WORK IS DONE

When every task for your assigned project has been merged and published, your role for that project is finished. You may be assigned again on a future project, starting fresh from Step 1.
