# PET CENTER — AI INTEGRATOR HANDOFF & OPERATING RULES

**Purpose:** This is the first document every AI Integrator must read when continuing integration work in `Victorsesan/petcenter-monorepo`. It defines the working source set, integration discipline, and current restart point. Accuracy matters more than claiming progress.

## 1. Mandatory reading order

1. **This document.** Follow these rules before taking action.
2. **The active task/project assignment uploaded by the owner in the current chat.** Read the complete document, including the task's Recommendations & Enhancements section and dependency graph. The uploaded task file defines the task scope and acceptance criteria. Do not substitute an older assignment document from the repository.
3. **The relevant worker task-completion document(s) stored at the repository root.** Read only the records relevant to the task being integrated. Treat them as historical context, not proof that the reported code still exists or works.
4. **The relevant project summary:** `adoption-website-project-summary.md` for adoption website work and `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` for admin-dashboard work. Read the relevant summary whenever the task touches that project.
5. **`PETCENTER_DESIGN_SYSTEM_v1.0.md`** whenever the task touches UI, styling, layout, interaction design, or animation.
6. **`VDM-GIT-SYNC-COORDINATION-PROTOCOL.md`** and **`INTEGRATOR-GIT-SYNC-MERGE-PROTOCOL.md`**. Read both before coordinating or integrating worker branches, and follow their applicable Git procedures.
7. **The current repository state:** fetch the latest `main`, inspect the relevant worker branch, actual source files, diffs, and current commit IDs before making decisions.

The owner may upload a different project assignment in a future chat. Always use the task file attached to that active chat; do not assume the current v13 file remains the active assignment in every future session.

## 2. Source-of-truth rules

- **Task scope and acceptance:** the active project/task assignment uploaded by the owner.
- **What code actually exists:** the current repository source on the latest `main` and the actual worker branch diff.
- **Historical task context:** the relevant root-level worker completion document(s). Verify every important claim against current code.
- **System architecture and project conventions:** the relevant project summary document(s).
- **Visual and interaction standards:** `PETCENTER_DESIGN_SYSTEM_v1.0.md`.
- **Git coordination and merge procedure:** the VDM and Integrator protocols named above.

A completion document, worker report, branch name, previous AI message, or earlier commit message does not by itself prove that a feature exists, is correct, is integrated, or is safe to deploy. Inspect the real files and test the behavior.

## 3. Integration rules

1. **Choose the next task from the active assignment and its dependency graph.** Do not jump ahead of prerequisites or rely on a task recommendation copied from an old handoff. Verify that the task is still next and that the required worker branch/deliverable exists.
2. **Inspect before editing.** Confirm the repository, current `main` HEAD, branch heads, relevant paths, and current implementation. Never assume old commit hashes or earlier branch states are still current.
3. **Review actual deliverables.** Compare the worker branch against the latest `main`. Check every changed file and relevant changed line against the task scope and acceptance criteria.
4. **Integrate selectively.** Do not merge a whole candidate/recovery branch just because it contains the desired feature. Bring in only the necessary, verified task changes. Keep unrelated work out of the integration.
5. **Use the correct files and domain.** Website work belongs in the website source; admin-dashboard work belongs in the admin source. For mixed-domain work, follow the two-Integrator approval requirements in the Integrator protocol.
6. **Preserve code comments and task anchors.** Keep required explanatory comments and exact task-specific START/END anchor structure. Update comments when the implementation changes; do not remove required anchors or silently move code outside its assigned zone.
7. **Respect existing contracts and reusable components.** Check actual source and project summaries before building anything new. Do not recreate an existing feature from scratch or change a shared data contract without checking the task assignment and notifying affected projects as required.
8. **Test the actual result.** Run the task-required structural checks, relevant automated tests, and browser/interaction checks. Verify the affected pages and states. Record exact commands and outcomes. A command finishing without an error is not sufficient evidence.
9. **Verify twice before declaring completion.** Re-check the final diff, anchors/comments, file scope, and test outcomes after integration. Do not mark work complete while acceptance criteria remain unverified.
10. **Be exact in status reporting.** State what was actually inspected, changed, tested, merged, or deployed. Never claim success for an action that did not complete and was not independently verified.
11. **Keep repository integration separate from production deployment.** Follow the Integrator protocol's release gates. Do not deploy to the live site without the required explicit owner approval.

## 4. Owner communication and access

- Be direct and practical. Give a clear plan and executable steps instead of repeated explanations, unnecessary security lectures, or broad troubleshooting.
- Do not ask the owner to repeat information already provided. Ask a question only when an essential decision or input is genuinely missing.
- The owner has already generated a fine-grained GitHub token and selected repository permissions. Do not send the owner through token generation again without first verifying a specific permission gap.
- Never ask the owner to paste a secret token into the chat. Do not place tokens or other credentials in repository files, reports, logs, or commits. Use an authorized connection or a secure credential-entry method outside the conversation.
- Verify actual repository read/write capability before promising a commit, push, merge, or other write operation. Report access failures precisely and provide one concrete next step.

## 5. Current restart point — active v13 assignment

The active task file uploaded in this chat is `WEB-PROJECTS-1-2-TASK-ASSIGNMENTS-v13.md`. Its dependency graph places **Task 9 — Widget + Persistence Core Build** before Task 10, and Task 10 before the language rollout tasks. Therefore, Task 9 is the current next integration target, subject to a fresh check of repository state and the actual worker deliverable.

Task 9's assigned scope includes:
- the Settings Widget, language switcher, and 0–100% opacity slider on the homepage pilot;
- the admin-dashboard default-opacity control and persistence;
- RGBA/background alpha rather than applying CSS `opacity` to the entire content container;
- accessible slider semantics and keyboard support;
- cross-document View Transitions and cross-tab `storage` event behavior;
- the shared localStorage contract: key `preferences`, object shape `{ lang, opacity }`, and the language codes specified in v13, including `zh-Hans`;
- preservation of the contract's compatibility with Admin Project #4 as described in v13.

The assignment identifies the worker branch as `task/trans-9` and the report path as `reports/task-trans-9.md`. Verify that the branch and report actually exist before relying on them. Inspect the current `adopt/index.html` and `adopt/admin-dashboard.html` (confirm the live repository paths first), compare the worker's real diff with the latest `main`, and check every v13 acceptance criterion. Do not assume an earlier completion note proves Task 9 is done.

Task 10 must not be integrated before Task 9 is correctly integrated into `main`. Tasks 4–8 depend on Task 10 as stated in v13. Follow the uploaded assignment for all other dependencies and shared-file sequencing.

## 6. Required completion record

Before reporting an integration task as complete, provide a concise, evidence-based record containing:
- task identifier and acceptance criteria checked;
- current `main` base SHA and worker branch/commit SHA;
- exact files and changes integrated;
- confirmation that required comments and anchors remain intact;
- tests/checks actually run and their results;
- unresolved issues or owner decisions;
- commit/PR details, if a commit or PR was actually created;
- next dependency-valid task.

Do not create extra documentation or status files unless the owner or active assignment explicitly requires them. Keep this handoff document accurate when the owner authorizes a change to the integration process.
