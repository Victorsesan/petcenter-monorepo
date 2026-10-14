## Task Number
Task E2 — Account lifecycle handling (Rehoming Platform). Branch `task/rehoming-E2`.

## What Was Required
When a merchant deletes their account or an admin suspends it, all of that merchant's listings become `no_longer_available`, still viewable at the same URL with a "no longer available" treatment. Visitors with an open chat on those listings get one system message. Suspension is reversible (admin restores to `active`); self-deletion is not.

## What I Built
- `adopt/rehoming/account-lifecycle.php`: JSON endpoint with actions `self_delete`, `admin_suspend`, `admin_restore`. Sets listing status, closes open threads with one fixed system message, restores only suspended listings.
- `adopt/rehoming/listing-unavailable-snippet.html`: badge, banner, dimmed info panel, hidden action bar. It is a merge-ready block for the listing page (Task A1), which does not exist yet.
- `adopt/rehoming/listing-status-stub.json`: test shape for a listing record.
- `adopt/rehoming/data/.htaccess`: denies web access to the JSON data folder.
- Decisions: self-deletion is terminal (an admin suspend never overwrites it); IDs are sanitized and paths containment-checked (same pattern as `delete-application.php`); the prior status is saved, and `was_placement_complete` is a permanent marker so B3/B4 stats survive the status overwrite; restored listings reopen their threads, switchable with `RH_REOPEN_THREADS_ON_RESTORE`. Caller identity comes from generic session flags (`$_SESSION['merchant_id']`, `$_SESSION['is_admin']`). Token invalidation is left as the empty hook `accountLifecycleOnStatusChange()` for Task C5.

## Files Changed
| File | Shared with another task? |
|---|---|
| adopt/rehoming/account-lifecycle.php (new) | `status` field shared with D1; thread fields with C5 |
| adopt/rehoming/listing-unavailable-snippet.html (new) | to be merged into A1's listing page |
| adopt/rehoming/listing-status-stub.json (new) | No |
| adopt/rehoming/data/.htaccess (new) | No |

## Small Out-of-Scope Fixes Made
None.

## Bugs and Follow-ups Sent to TODO.md
None sent. D1, A1 and C5 have no code in the repo yet, so there was nothing to address to them; see below.

## Things the Next Person Must Know
- D1, A1 and C5 were not in the repo when this was written. After they merge, check: listing file layout, thread field names, session keys, and add `no_longer_available` to D1's status enum.
- B3/B4 should count verified rehomes from `was_placement_complete === true` OR `status === 'placement_complete'`.
- Wire C5's token invalidation into `accountLifecycleOnStatusChange()`.
- `RH_*_BASE` paths and thread reopening are assumptions to confirm against real code.
- Merge the snippet into A1's page; the badge's parent needs `position: relative`.

## Self-Check Confirmation
Only new files under `adopt/rehoming/` and this report were touched. Every block carries `TASK-rehoming-E2 START/END` anchors. Tested: PHP structure lint, a Python logic simulation of the PHP flow (no PHP interpreter in my sandbox, so the PHP itself was not executed), and Playwright at 390px on the snippet (badge, banner and hidden action bar all work, no console errors). Merged latest `main` before finishing (already up to date).
