# Integration Build Check — 2026-11-06

## Scope

This disposable integration check refreshed every remote worker ref, rehearsed worker merges against current `origin/main`, composed the MANUS-08 and MANUS-10 application updates, and ran browser-based RTL/responsive checks. No worker branch was merged into `main` during the check.

## Baseline and branch state

- Current `origin/main`: `5814eab` — merge of MANUS-04.
- MANUS-01: `22594e4`
- MANUS-02: `5833edc`
- MANUS-03: `ff4db2f`
- MANUS-04: `151d46b`
- MANUS-05: `c177938`
- MANUS-06: `ea021c3`
- MANUS-07: `b961a0b`
- MANUS-08: `9c49ce4`
- MANUS-09: `bd41d5b`
- MANUS-10: `0f0d459`
- MANUS-11: `30fe102`

The MANUS-03 worktree was clean and synchronized with `origin/task/second-round-MANUS-03`.

## Merge rehearsal results

Each branch was tested in a disposable worktree against current `origin/main`:

| Branch | Result | Conflict areas |
|---|---|---|
| MANUS-01 | Clean | — |
| MANUS-02 | Conflict | `docs/second-round/WORKER-PACKAGE-INVENTORY.md`; `reports/second-round/README.md` |
| MANUS-03 | Conflict | Seven category pages; worker-package inventory; two Git-sync protocol documents |
| MANUS-04 | Clean | — |
| MANUS-05 | Clean | — |
| MANUS-06 | Clean | — |
| MANUS-07 | Clean | — |
| MANUS-08 | Clean | — |
| MANUS-09 | Clean | — |
| MANUS-10 | Clean | — |
| MANUS-11 | Clean | — |

The result is **not** an all-branches-clean integration. MANUS-02 and MANUS-03 need deliberate integrator resolution. The conflicts were left unresolved only in disposable rehearsal trees and did not affect any remote branch.

A second disposable tree composed current `origin/main`, then MANUS-08, then MANUS-10. That cumulative merge was clean, with candidate merge commits `e400a96` and `c68d30d`.

## Cumulative browser verification

The cumulative MANUS-08 + MANUS-10 candidate was served from a local HTTP preview. Playwright tested these routes:

- `/pets/cats/`
- `/pets/dogs/`
- `/pets/birds/`
- `/pets/reptiles/`
- `/pets/small-mammals/`
- `/pets/livestocks/`
- `/pets/aquariums/`

Each route was tested at 1440×900, 390×844, and 360×800 in RTL mode by setting both the document and body direction to `rtl`.

**Result: 21/21 passed.**

Every case returned HTTP 200, had zero page errors, had zero document/body horizontal overflow, kept the Request-One CTA within the viewport, retained the accessibility trigger, and loaded the shared accessibility-widget script. The isolated candidate recorded zero failed network requests.

The MANUS-10 chat widget was then tested at all three viewport sizes on `/pets/cats/`. The page was scrolled to 420px, the real `.pcw-launcher-btn` was opened, and `.pcw-header-close` was clicked. The page restored exactly to 420px after closing at desktop and both mobile widths.

## Acceptance and handoff

The MANUS-08 and MANUS-10 updates are compatible in the cumulative candidate and are ready for normal integrator merge review. The integrator must still:

1. Resolve MANUS-02 documentation conflicts while preserving the latest inventory/report guidance.
2. Resolve MANUS-03 category-page conflicts in favor of a deliberate combination of MANUS-03 logical RTL/widget changes and MANUS-08 category UI fixes.
3. Re-run the full browser matrix after those conflict resolutions.
4. Keep protected content, credentials, and direct pushes to `main` out of the process.

This report records a build rehearsal and QA candidate, not a production merge or deployment approval.
