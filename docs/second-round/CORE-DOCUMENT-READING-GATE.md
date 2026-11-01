# Second-Round Core-Document and Evidence Reading Gate

This document is mandatory process guidance for every `MANUS-01` through `MANUS-11` account. It is a hard gate, not optional background reading. An account must finish the complete reading sequence below before inspecting application source, comparing code, making a branch, or editing any implementation file.

## Required reading order

After repository access succeeds, synchronize all refs before reading:

```bash
git remote set-branches origin '*'
git fetch --all --prune
git checkout main
git pull --ff-only origin main
git branch -r
```

Then read the following in full, in this order:

1. `adoption-website-project-summary.md`
2. `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`
3. `PETCENTER_DESIGN_SYSTEM_v1.0.md`
4. `docs/second-round/core-docs/ROADBLOCKS-AND-FIXES-2.md`
5. `docs/second-round/core-docs/TODO-5.md` — read its Table of Contents first, then every entry addressed to the account or `ALL`.
6. `docs/second-round/core-docs/WORKER-GIT-SYNC-PROTOCOL-7.md`
7. `docs/second-round/core-docs/VDM-GIT-SYNC-COORDINATION-PROTOCOL-7.md`
8. `docs/second-round/core-docs/Admin-Code-Integrator-5.md`
9. `docs/second-round/core-docs/Web-Code-Integrator-5.md`
10. Every project task tracker in `docs/second-round/core-docs/`:
    - `Admin-Project-1-Worker-Task-Assignments-2.md`
    - `ADMIN_PROJECTS_2_3_4_TASK_ASSIGNMENTS-2.md`
    - `WEB-PROJECTS-1-2-TASK-ASSIGNMENTS-v12-2.md`
    - `WEB-PROJECTS-3-4-5-TASK-ASSIGNMENTS-2.md`
    - `Rehoming-Platform-Task-Assignments-9.md`
    - `Unified-Status-Dashboard-Task-Tracker-v3-FINAL-2.md`
11. The shared widget references:
    - `docs/second-round/core-docs/Task-AccessibilityWidget-Completion-4.md`
    - `docs/second-round/core-docs/Task-WidgetPositioning-Completion-2.md`
12. `docs/second-round/WORKER-PACKAGE-INVENTORY.md`
13. `docs/second-round/SECOND-ROUND-ISSUE-REGISTER.md`
14. `docs/second-round/ACCOUNT-WIDE-CORRECTION.md`
15. `docs/second-round/SECOND-ROUND-MANUS-COORDINATION.md`
16. `docs/second-round/SECOND-ROUND-REQUEST.md`
17. `docs/second-round/SECOND-ROUND-SETUP-AND-SKILLS.md`
18. `docs/second-round/README.md`

Also read the current root copies `ROADBLOCKS-AND-FIXES.md` and `TODO.md` before any code work. The root copies are the live shared workflow/inbox; the `-2` and `-5` files under `core-docs/` are the preserved core-document versions and historical reference. Handle every current root `TODO.md` entry addressed to the account or `ALL`, and read the Table of Contents again before finishing.

The two project summaries are the structural backbone. The adoption summary governs the public website, routes, reusable page patterns, and adoption-side data flow. The admin summary governs the dashboard, chat system, backend files, database contracts, and admin-side reuse. A worker must not infer either structure from a task summary alone.

## Protocol precedence and contradiction resolution

The core set contains historical documents written for different delivery eras. Apply them in this precedence order:

1. **Worker Git Sync Protocol** governs repository access, branch creation, synchronization, scope, testing, reports, and worker commits.
2. **VDM Git Sync Coordination Protocol** governs cross-account visibility, branch review, shared-file ownership, task sequencing, and VDM review. Workers perform the relevant synchronization and review steps themselves when acting as a combined worker/VDM/integrator account.
3. **Admin Code Integrator** and **Web Code Integrator** govern the content-review method: compare the original assignment with the evidence, apply documented changes in order, preserve anchors/comments, inspect shared-file interactions, and investigate out-of-scope bugs after the documented work is reconciled.

The two Integrator documents still contain historical manual-upload wording such as “do not clone, fetch, push or deploy.” That wording conflicts with the repository-wide Git workflow and is superseded for this project. Do not follow the historical manual-upload instruction. Use the Git-aware Worker and VDM workflow, while retaining the Integrator documents’ review discipline, completion-evidence comparison, shared-file checks, and bug triage. No account may delete, rewrite, or silently ignore the conflicting historical text; record this precedence in its report.

The old completion-file rules inside some task trackers are also superseded by the Git workflow. Code belongs on the account branch and the short report belongs under `reports/`; the archived completion documents are evidence to read, not a new delivery format.

## Completion-evidence gate

After all core documents have been read, identify the account’s project and task numbers from `MANUS-ACCOUNT-PROMPTS.md`. Then search `docs/second-round/completion-evidence/` by both project name and task number. Read every matching completion or handoff document before opening the corresponding application source.

Examples:

- `MANUS-01` reads the Admin Project 1 tracker and every `Admin-Project-1-Task-*` evidence folder for Tasks 1–7.
- `MANUS-02` reads the Admin Projects 2/3/4 tracker and the matching `admin-dashboard-task-1a`, `admin-project-2-task-1a`, `admin-project-2-task-1b`, and `admin-project-4-task-3` evidence folders, plus any matching Project 5 evidence identified by the task tracker.
- `MANUS-03` reads the Web Projects 1–2 tracker and the `WEB-PROJECTS-1-2-Task-2`, `Web-project-1-2-task-9`, and `WEB-PROJECTS-1-2-Task-10` evidence folders, plus the shared widget evidence.
- `MANUS-04` reads the Web Projects 3–5 tracker and all matching `WEB-PROJECTS-3-4-5-*`, `Project-5-*`, and `pet-center-adoption-system-task-8` evidence folders relevant to its assignment.
- `MANUS-05` and `MANUS-06` read the Rehoming tracker and the matching A–G evidence folders.
- `MANUS-07` reads the Unified Status tracker and every `unified-status-*` evidence folder.
- `MANUS-08` reads the accessibility/widget references and every matching shared-widget evidence file before changing shared UI.
- `MANUS-09` reads all evidence documents relevant to security, PHP quality, HTML quality, uploads, and authentication, then checks the current source.
- `MANUS-10` reads all evidence documents relevant to browser, integration, route, form, console, network, and visual claims before QA.
- `MANUS-11` reads every evidence document and every account report before reconciliation.

Use filename/task-number matching, headings, and distinctive phrases from the account’s assignment to find additional matching evidence. Do not rely on the inventory table alone when a completion document has a different filename.

For each matching evidence document, record in the report:

- the exact evidence path read;
- what the previous worker claimed or completed;
- the files and anchors involved;
- what is already present on the current branch or another worker branch;
- what remains to be done;
- any conflict between the evidence and the original task tracker.

A missing evidence document is no longer a reason to stop because the current source and all other branches are visible. Record the missing path, continue all independently verifiable work, and do not invent undocumented contracts.

## Required role sequence for each account

Every account must act in this order:

1. **Worker:** read the assignment and evidence, inspect current `main` and relevant worker branches, implement only the assigned scope, test it, and push the account branch.
2. **VDM:** review the branch against the full tracker, summaries, evidence, shared-file ownership, report, tests, and other branches. Correct or request corrections using the repository workflow.
3. **Integrator:** reconcile the approved branch with `main`, preserve all task anchors/comments, investigate evidence-reported bugs in the fully merged source, run final structural/browser/security checks, and report what is fixed, false alarm, or still needs review. Do not publish a deploy branch unless the account is explicitly assigned that release action.

Before any role advances, the account must verify that it has read all documents in this gate. The final report must include the reading-gate completion statement, the protocol-precedence decision, the evidence paths reviewed, branch/ref synchronization details, tests, commits, and unresolved items.

## Scope lock for the current coordinator

This gate and evidence-documentation pass is complete before starting MANUS-03 Task 3. Do not begin Task 3 CSS logical-property implementation until this gate is committed and the account prompt has been updated to require it for all 11 accounts.
