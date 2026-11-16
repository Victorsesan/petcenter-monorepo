# PET CENTER — INTEGRATOR HANDOFF

**Repository:** `Victorsesan/petcenter-monorepo`  
**Purpose:** Starting point for the Integrator continuing work in this repository.

## 1. Read these sources in order

1. The active task assignment uploaded by the owner in the current chat. Read it fully, including acceptance criteria, recommendations, shared-file notes and dependency graph. The current upload is authoritative.
2. The relevant root-level worker completion record(s) for the exact task being reviewed. Treat these as historical claims; verify them against current code.
3. The applicable root project summary: `adoption-website-project-summary.md` for public adoption-website work, or `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` for admin work.
4. `PETCENTER_DESIGN_SYSTEM_v1.0.md` for any UI, styling, layout, responsive or animation decision.
5. `VDM-GIT-SYNC-COORDINATION-PROTOCOL.md` and `INTEGRATOR-GIT-SYNC-MERGE-PROTOCOL.md` before worker coordination, branch review, merging or release.
6. The latest live repository state: main SHA, task branch, report, source files and actual diff.

Do not resurrect the removed `docs/second-round/` archive or rely on stale alternate trackers. Ignore all MANUS and WEB-01 through WEB-05 documents and all reports, completion claims, issue registers or conclusions originating from those accounts. Do not infer that their code exists. The owner has explicitly quarantined that material.

## 2. Source-of-truth rules

- Task scope, acceptance criteria and dependencies: active assignment supplied by the owner in the current chat.
- What code actually exists: current source files on latest `main` and the exact worker branch diff.
- Worker completion claims: relevant root completion record, verified against source.
- Architecture and conventions: applicable root project summary.
- UI and interaction standards: `PETCENTER_DESIGN_SYSTEM_v1.0.md`.
- Git and release procedure: the two current root-level Git protocols named above.

A task report, branch name, commit message or previous AI response is not proof that a feature exists, works, is integrated or is safe to deploy.

## 3. Integration rules

1. Inspect actual repository state before deciding what to do. Never guess file paths, branch names, task status or feature behaviour.
2. Follow the active assignment's dependency graph; do not skip prerequisites.
3. Review every changed line against the assigned scope. Integrate only verified task changes, never an entire recovery branch just because it contains the desired feature.
4. Keep website and admin changes in their actual current source files. If the assignment path does not exist, locate the current equivalent using the actual repository and relevant summary, and record the discrepancy.
5. Preserve required comments and task-specific START/END anchors. Update comments when behaviour changes; do not remove required anchors.
6. Preserve shared contracts, especially localStorage keys, JSON shapes, language codes and other cross-project interfaces. Notify affected projects before any contract change.
7. **Use TinyFish for visual/browser verification whenever the task concerns a live page, visible UI, navigation or interaction.** Open relevant menus/sections and inspect the actual displayed states. Do not change live settings while inspecting. Pair this with source review and automated tests; browser visuals alone are not enough.
8. Run the actual structural checks and relevant automated/browser tests. Record commands and results; do not treat a command finishing without error as proof of correctness.
9. Perform a second verification pass over the final diff, scope, comments/anchors, acceptance criteria and test outcomes.
10. Report precisely what was inspected, changed, tested, committed, merged or deployed. Never claim an action succeeded unless verified.
11. Repository integration and production deployment are separate. Never publish to a production deploy branch without the explicit owner approval and release gates in the Integrator protocol.
12. Keep owner communication direct and practical. Do not re-ask for information already supplied.

## 4. GitHub access

- Test actual write capability before promising repository changes. Permission metadata does not prove the current API connector can write.
- The owner's fine-grained token already exists; do not ask for token generation again and never ask for the token in chat.
- The standard GitHub API integration returned HTTP 403 for branch creation. The signed-in TinyFish browser successfully created a maintenance branch and used GitHub's direct `/delete/` route to commit file deletions.
- If API writes return 403, use the already-authorized signed-in GitHub browser workflow or an authorized local/Codex Git environment. Never claim a failed API write succeeded.
- Never force-push. Re-check a branch head before any ref update rejected by GitHub.

## 5. Current checkpoint — v13 website assignment

The active assignment in the current chat is `WEB-PROJECTS-1-2-TASK-ASSIGNMENTS-v13.md`. Its dependency graph places **Task 9 — Widget + Persistence Core Build** before **Task 10 — Widget Rollout**. Task 1 remains on owner hold; Task 2 depends on Task 1; Task 3 must precede Task 7.

### Documentation cleanup completed on the maintenance branch

- Branch: `maintenance/integrator-handoff-cleanup-2026-11-16`
- Base main SHA: `fa70c6636f070193cc99b4b94d92d47d7efe74fa`
- Cleanup head before this handoff-file update: `48109c090e44219b72b14621d8337e388ee52016`
- All 14 Markdown files under `docs/second-round/core-docs/` were deleted on this branch.
- Recursive Git tree verification found **zero paths** beginning `docs/second-round/`.
- The first deletion commit was `3aee1622756b22e5db976e7d7e1a9575a13f6667`; the final deletion commit was `48109c090e44219b72b14621d8337e388ee52016`.
- These are maintenance-branch commits only. They have not been merged to main or deployed.

### Task 9 — verified state

**Status: partially present on main; NOT verified complete.**

- v13 specifies worker branch `task/trans-9` and report `reports/task-trans-9.md`; neither was found in the current repository search.
- Current homepage source: `adopt/index.html`.
- Current shared widget source: `adopt/assets/js/accessibility-widget.js`.
- Current admin dashboard entry found: `adopt/admin/index.html`. The path `adopt/admin-dashboard.html` amed in v13 was not found in the current tree; reconcile this using `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` and current source before editing.
- TinyFish opened the live homepage widget. It exposed English, Spanish, Portuguese, French, Arabic and Simplified Chinese, plus user-facing Widget Icon Opacity and Widget Background Opacity sliders.
- Source shows those two user-facing sliders currently use a 20–100 range, while v13 requires 0–100.
- The widget source uses localStorage key `preferences`, retains the `lang` field, includes `opacity`, contains a native `storage` listener and reads the optional `pc_admin_settings_defaults` key.
- The homepage contains the cross-document `@view-transition { navigation: auto; }` rule.
- The current checked dashboard source did not show the matching admin-wide default-opacity controls or the save/write path for `pc_admin_settings_defaults`. Verify again after resolving the dashboard path; do not infer that the feature exists from an older completion claim.
- Therefore, do not mark Task 9 complete and do not start Task 10 yet.

### Next actions

1. Recheck latest `main`, `task/trans-9` and `reports/task-trans-9.md`.
2. Resolve the admin dashboard path against the current root summary and actual tree.
3. Verify and complete the admin-wide default-opacity controls/persistence and the required 0–100 user slider range without breaking the `preferences` contract (`{ lang, opacity }`, including `zh-Hans`).
4. Use TinyFish and Playwright/browser tests to verify the homepage and dashboard, keyboard operation, storage synchronisation, View Transitions and actual rendered states.
5. Complete the two-pass review and follow the Integrator protocol for merge approval.
6. Only after Task 9 is correctly merged, begin Task 10.

## 6. Required completion record

For every integrated task, record: task ID and acceptance criteria checked; base main SHA; exact worker branch/commit SHA; exact files changed; comment/anchor verification; tests and results; TinyFish/browser observations where relevant; unresolved issues or owner decisions; actual commit/PR details; and the next dependency-valid task.

Keep `INTEGRATION-PROGRESS.md` aligned with verified repository state. Do not create extra documentation unless the owner or active assignment explicitly requires it.
