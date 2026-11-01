# Second-Round Account Report Index

This is the central register for reports from all 11 Manus accounts. Each account owns the report path shown below. Accounts must update their own report on their branch; the coordinator updates status here after reviewing the branch and evidence. No credentials belong in this file.

| Account ID | Assigned work package | Required report path | Status | Branch | Commit | Coordinator notes |
|---|---|---|---|---|---|---|
| `MANUS-01` | Admin Project 1 — Tasks 1–7: live-chat security, sessions, tokens, PIN, lock, password, reset, and auth-state | `reports/second-round/MANUS-01.md` | READY FOR REVIEW | `task/second-round-MANUS-01` | `53415c2` | Report and scoped rating-email fix; PIN/lock/password/reset items remain blocked or need review. |
| `MANUS-02` | Admin Projects 2/3/4 — Tasks 1a, 1b, 2, 3: transcription, read-aloud, dark/light mode, and chat-language preference | `reports/second-round/MANUS-02.md` | READY FOR REVIEW | `task/second-round-MANUS-02` | `37e3319` | Report submitted; current-source verification classifies the claimed features as blocked or needing review. |
| `MANUS-03` | Web Projects 1–2 — Tasks 1–10 and their completion packages | `reports/second-round/MANUS-03.md` | READY FOR REVIEW | `task/second-round-MANUS-03` | `8c745ed` | Archived Task 9 widget and Task 10 rollout applied; translation tree and signin source remain unresolved. |
| `MANUS-04` | Web Projects 3–5 — Tasks 1, 2, 3, 3b, 4, 4b, 5, 5b, 6a, 6b, 7a, 7b, 8, 9 | `reports/second-round/MANUS-04.md` | NOT STARTED | `task/second-round-MANUS-04` | — | — |
| `MANUS-05` | Rehoming Platform — Tasks A1–C6 | `reports/second-round/MANUS-05.md` | READY FOR REVIEW | `task/second-round-MANUS-05` | `852a05f` | Report submitted; no code changes; multiple dependencies remain blocked or need review. |
| `MANUS-06` | Rehoming Platform — Tasks D1–G1 | `reports/second-round/MANUS-06.md` | READY FOR REVIEW | `task/second-round-MANUS-06` | `90e9bdb` | Report and scoped D1/F5 fixes submitted; later tasks remain blocked or need review. |
| `MANUS-07` | Unified Status Dashboard — Tasks 1–12 | `reports/second-round/MANUS-07.md` | READY FOR REVIEW | `task/second-round-MANUS-07` | `f54defd` | Report submitted; most assigned dashboard/BNPL work is blocked or needs review. |
| `MANUS-08` | Accessibility, widget positioning, design system, responsive design, and rebranding consistency | `reports/second-round/MANUS-08.md` | NOT STARTED | `task/second-round-MANUS-08` | — | — |
| `MANUS-09` | Cross-cutting security, PHP quality, HTML quality, upload safety, authentication, and security issue-register findings | `reports/second-round/MANUS-09.md` | READY FOR REVIEW | `task/second-round-MANUS-09` | `9922055` | Report submitted; security findings are classified and handed to owning accounts. |
| `MANUS-10` | Browser and integration QA: Playwright, desktop/mobile behavior, console errors, network failures, routes, forms, and visual checks | `reports/second-round/MANUS-10.md` | NOT STARTED | `task/second-round-MANUS-10` | — | No branch or report found. |
| `MANUS-11` | Final coordinator: reconcile reports, shared-file changes, dependencies, issue classifications, commit order, and final CEO report | `reports/second-round/MANUS-11.md` | READY FOR REVIEW | `task/second-round-MANUS-11` | `efe7b72` | Coordinator report exists but predates the latest MANUS-03 branch work; reconciliation must be rerun. |

## Status vocabulary

- **NOT STARTED** — no report submitted.
- **IN PROGRESS** — account has reported its starting state.
- **BLOCKED** — account cannot proceed without a documented dependency or decision.
- **READY FOR REVIEW** — branch and report are available for coordinator review.
- **RECONCILED** — coordinator verified the report, tests, and handoff.

## Coordinator checklist

The coordinator must verify that every account report identifies its source documents, issue classifications, changed files, tests, branch, commit, and unresolved blockers. The coordinator must then classify every master-register item as Fixed, False Alarm/Already Resolved, or Needs My Review.
