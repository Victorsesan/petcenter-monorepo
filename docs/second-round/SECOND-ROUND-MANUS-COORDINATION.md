# Second-Round Manus Account Coordination Plan

> **CURRENT-WAVE NOTICE — 2026-11-07:** This document preserves the historical 11-account second-round coordination plan. It is not the active assignment plan for the current wave. The current wave has five web accounts (`WEB-01`–`WEB-05`) and two redesign accounts (`DESIGN-01`–`DESIGN-02`). Use [`WEB-ACCOUNTS-OPERATIONAL-BRIEF.md`](WEB-ACCOUNTS-OPERATIONAL-BRIEF.md) and [`REDESIGN-ACCOUNTS-OPERATIONAL-BRIEF.md`](REDESIGN-ACCOUNTS-OPERATIONAL-BRIEF.md) for current scopes, branches, synchronization, reading gates, and deliverables. Historical reports and evidence remain available for reconciliation.

## Mission

This document coordinates the second debugging round after mechanical integration. The goal is to verify each assigned task against its original project brief, create genuinely missing deliverables that were never completed, reconcile cross-worker contracts, verify every issue in the master register, and report each outcome without guessing.

The source documents are preserved beside this plan in `docs/second-round/core-docs/`. The pasted CEO instructions are preserved as `SECOND-ROUND-REQUEST.md`. The extracted worker-package inventory and issue register are the starting point for every account.

## Repository evidence availability

The worker-package inventory was generated from the original intake workspace. The uploaded worker archive has now been imported as Markdown evidence under `docs/second-round/completion-evidence/`, with per-archive subdirectories and an `IMPORT-MANIFEST.md`; nested source-code files were not imported. This applies to every account, not only MANUS-01. Each account must check the evidence files named for its scope, use the core assignment, issue register, Git history, and current source for independent verification, and still create its report. If a specific evidence file is not present in the manifest, record that exact gap and classify affected items as **Blocked** or **Needs My Review**.

## Non-negotiable operating rules

1. Read the relevant core assignment, project summary, worker protocol, current `INTEGRATION-PROGRESS.md`, the task completion file, and the current repository files before editing.
2. Work from the current `main`; fetch first, never force-push, never rewrite history, and never edit another account's active zone.
3. Verify every worker-reported bug against the real merged repository. Do not fix a report merely because it appears in a completion file.
4. Preserve task anchors and add a separate `BUGFIX-*` or second-round task anchor around every debugging change. Record exact old/new snippets in the account report.
5. Never open, copy, modify, or commit `.env`, `config.php`, `pet.json`, or anything under `applications/`, `uploads/`, `chat-media/`, or `logs/` unless a later CEO-authorized protocol explicitly changes that restriction.
6. Do not create a missing implementation from a completion-file description alone. Confirm the original assignment, expected path, shared contract, and required inputs first; otherwise record a blocker.
7. Run the applicable PHP, JavaScript, HTML, structural, security, and browser checks before committing. Test desktop and phone widths when a page or responsive component is involved.
8. Each account must publish a report under `reports/second-round/<account-id>.md` containing: files inspected, issues verified, changes made, tests run, unresolved blockers, and exact commit hashes.
9. The final coordinator must classify every register entry as **Fixed**, **False Alarm/Already Resolved**, or **Needs My Review** and must not leave silent omissions.

## Work packages

| Account | Ownership | Initial scope | Required deliverable |
|---|---|---|---|
| Manus-01 | Admin live-chat security | Admin-Project-1 Tasks 1–7, shared auth/session/token/PIN/lock/password/reset files | Verify auth boundaries, token invalidation, rate limiting, session ownership, admin bulk actions, shared contracts, and security tests. Report shared-file conflicts before editing. |
| Manus-02 | Admin234 chat features | Admin Projects 2/3/4 Tasks 1a, 1b, 2, 3 | Verify transcription, read-aloud, dark/light mode, language preference, shared options panel ownership, merge order, browser behavior, and cross-project storage contracts. |
| Manus-03 | Web Projects 1–2 | WEB-PROJECTS-1-2 task assignments and completion packages | Build a task/completion matrix, verify missing pages and handlers, inspect navigation/header/footer consistency, and resolve only confirmed defects in that zone. |
| Manus-04 | Web Projects 3–5 | WEB-PROJECTS-3-4-5 task assignments and completion packages | Verify project 3/4/5 deliverables, duplicate or superseded tasks, design fidelity, form/security behavior, and missing completion packages. |
| Manus-05 | Rehoming A–C | Rehoming tasks A1–C6, including stopped/prohibited and completion-less packages | Reconcile listing, verification, fraud, onboarding, chat-safety, and merchant-contract dependencies. Verify stopped A1/A2/C1/C5/C6 items without touching prohibited data. |
| Manus-06 | Rehoming D–G | Rehoming tasks D1–G1 and all E/F/G completion packages | Verify placement lifecycle, merchant dashboard, support/FAQ, account lifecycle, notifications, analytics, missing E6/F6/G1 work, and cross-task data schemas. |
| Manus-07 | Unified status dashboard | Unified Status Dashboard Tasks 1–12 | Compare assignment tracker, available completions, current dashboard files, and shared status contracts. Identify missing tasks and conflicting ownership before implementation. |
| Manus-08 | Accessibility and design system | Accessibility Widget, Widget Positioning, design-system and page rebrand requirements | Verify widget placement, keyboard/focus behavior, reduced motion, contrast, dark/light mode, responsive layouts, and visual consistency across representative pages. |
| Manus-09 | Cross-cutting security and PHP/HTML quality | Master issue register entries involving XSS, unsafe input, file storage, auth, upload handling, PHP output, HTML validity, and secrets | Independently reproduce security findings, run static checks, and coordinate fixes with the owning account. Do not duplicate fixes in shared files. |
| Manus-10 | Browser and integration QA | Full merged application, desktop and phone widths | Run Playwright smoke/regression tests, console/network checks, route checks, form behavior, and anchor/script timing checks. File reproducible defects against the owning package. |
| Manus-11 | Final coordinator/reconciliation | All account reports and unresolved blockers | Maintain the master status table, reconcile shared-file changes, validate commit ordering, classify every issue, and prepare the CEO-facing final report. |

Accounts may run in parallel only when they do not edit the same file or shared contract. Shared files require an explicit ownership chain and a handoff in the corresponding report.

## Required setup for every account

```bash
cd /home/ubuntu/workspace/petcenter-monorepo
git fetch origin
git checkout main
git pull --ff-only origin main
python3 --version
php --version
node --version
git --version
unzip -v | head -n 2
jq --version
rsync --version
```

Use the repository's current source-of-truth documents and do not commit the outer ZIP or the extracted intake workspace. Keep any temporary fixtures outside the repository unless a task explicitly declares them as test assets.

## Recommended skills and tools

Already available in the Manus environment: `code-reviewer`, `github-integration`, `webdev`, `webdev-seo`, `persistent-computing`, `image-processing`, `office-documents`, `skill-creator`, and the project-specific managed utilities. Use only those relevant to the account's work.

The core documents also require these practical checks:

- PHP: `php -l`; add PHPStan or an equivalent static analysis tool only after checking the repository's existing dependency policy.
- JavaScript: `node --check`; use the existing test runner if present; inspect console errors and failed network requests.
- HTML/CSS: structural tag/brace/ID checks, route/link checks, accessibility checks, and a validator where available.
- Browser: Playwright or the available Manus browser tooling at desktop and phone widths, including screenshot/reference comparison for design tasks.
- Security: OWASP-oriented review for XSS, CSRF, path traversal, upload validation, authorization, session fixation, token leakage, unsafe file storage, and secret exposure.
- Git: explicit-path staging, task-scoped commits, no force-push, and a report with exact hashes.

A dedicated security-audit or PHP/WordPress managed skill was not included in the available environment. Accounts must therefore use the direct security checklist above and record that limitation rather than claiming a missing skill was installed.

## Reporting template

Every account report must include:

- Account ID and assigned scope.
- Source documents and completion files read.
- Repository commit at start and end.
- Files inspected and exact task/bug anchors.
- Issue-register entries verified, with classification.
- Changes made, including old/new behavior and why the change is in scope.
- Tests and browser checks, including viewport and result.
- Cross-account dependencies and unresolved blockers.
- Commit hashes and next handoff account.

## Handoff order

1. Manus-01 and Manus-02 reconcile admin shared contracts.
2. Manus-03 and Manus-04 work on independent web zones.
3. Manus-05 and Manus-06 reconcile rehoming contracts in task order.
4. Manus-07 audits unified dashboard coverage after its dependencies are identified.
5. Manus-08 reviews shared visual/accessibility behavior without redesigning feature logic.
6. Manus-09 and Manus-10 verify the merged state and file confirmed defects against owners.
7. Manus-11 performs final reconciliation and produces the CEO-facing report.
