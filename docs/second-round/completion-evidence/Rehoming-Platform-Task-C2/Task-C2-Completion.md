# Task C2 — Completion File

> ⚠️ **NEW FOLDER REQUIRED — NOT YET CREATED, NO EXISTING ENTRY IN EITHER
> SUMMARY DOC** ⚠️
> Both files in this submission need a folder called **`rehome/`** created
> at the **site root** (sibling to `apply/`, `adoptions/`, `pets/`,
> `blog/`, `services/`, `signin/`, etc. — see Section 1 of
> `adoption-website-project-summary.md`). This folder does not exist in
> Section 1 of `adoption-website-project-summary.md` or anywhere in
> `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` — it is **proposed, not confirmed**.
> See "Live-path flag" below for the full reasoning. **Whoever places the
> real listing-upload handler and chat message handler must confirm or
> correct this folder name/location before the integrator runs** — both
> files inside this zip `require_once` a sibling file by relative path, so
> a wrong folder placement breaks them silently (no visible error, same
> failure mode called out for `adoption-application.php`'s `dirname(__DIR__)`
> logic in Section 1).

## Task Number
TASK C2 — Anti-fraud detection layer

## What Was Required
Four independent detectors: (1) cross-account duplicate-photo detection on
listing upload, (2) a posting-velocity flag for new merchant accounts that
publish more than 3 listings in 24 hours (which also sets `held_for_review`
on those listings), (3) regex-based off-platform-contact detection on the
first 5 messages of a new chat thread, and (4) a scheduled reputation-anomaly
check for merchants receiving 3+ reviews/reports in 24 hours. All four write
Report records (`source: system_flag`) rather than building any queue UI —
Task C1b owns the queue. Each detector checks the target merchant's
`is_test_account` first and writes nothing if true.

**Real blocker, raised and resolved with the CEO before building (recorded
honestly, not smoothed over):** per Directive 15/8b, I requested the real
listing-upload handler and this project's own chat message handler before
writing any integration code. Neither was confirmed to exist yet — Task A1's
own completion file documents that the listing-publish endpoint
(`merchant-publish-listing.php`) and upload endpoint (`merchant-upload-temp.php`)
are not yet live, and no task document anywhere names this project's own
chat-send endpoint. The CEO authorized proceeding on sensible defaults
rather than blocking further. So this submission is the detector module
itself plus documented, copy-paste call sites — not edits to either real
handler, since editing a file that doesn't exist isn't possible.

**This hand-in only repackages that same work into the new format — no code
was changed, redone, or added beyond what the guide itself asks for (see
item 3 below, which is a packaging change, not new logic).**

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `anti-fraud-detectors.php` | **Not confirmed — see flag below.** Proposed: `rehome/anti-fraud-detectors.php` | New | No other task currently in this file zone. Its four storage functions may need replacing if Task C1b (Report record) or Task C6 (Review record) pick a different real storage layer than the flat-file JSON assumed here — see Self-Check Confirmation. |
| `anti-fraud-reputation-cron.php` | **Not confirmed — see flag below.** Proposed: `rehome/anti-fraud-reputation-cron.php` | New | No — thin wrapper, calls only this task's own `cfRunReputationAnomalyCheck()`. Needs wiring into whichever cron mechanism A2/B3 already use, per this task's own "can share the same cron pattern" note — confirm the real crontab entry point at integration time. |

`anti-fraud-integration-notes.md` is documentation for the integrator
(copy-paste call sites for wiring the two files above into the real
listing-upload handler and chat message handler once those are named), not
a file that runs on the live site — it sits at the **zip root**, alongside
this completion file itself, not inside `rehome/`, since neither is a live
site file. (Earlier guidance on this task had the completion file and this
notes file sent as separate attachments outside the zip; per the most
recent correction, everything new for this task — code and documents alike
— is now joined into one zip, named for the project and task number, so
nothing is missing when sorted. Noted here as a packaging correction, not a
reopening of the underlying work.)

No existing file was touched. `publish-pet.php`, `admin-upload-temp.php`,
and `chat-send.php` were read as reference only (per Rule 7) and were never
edited — they are not this project's files in scope for C2 (the first two
are the super-admin originals; the third is the separate existing
customer-support chat system, not this project's own messaging endpoint).

**Live-path flag (genuine gap, not a guess made silently):** Section 1 of
`adoption-website-project-summary.md` — the only Server Folder Structure
documented anywhere — has no entry for the Rehoming Platform's own backend
at all yet. It confirms `/signin/` (formerly `/rehome/`) is only "the
foundation for the Rehoming Platform project," with nothing built out
behind it. These two files aren't in the guide's source→live-path mapping
table either. `rehome/` above is a proposed sibling folder to the other
top-level folders (`apply/`, `adoptions/`, `pets/`, `signin/`, etc.),
consistent with `/signin/` being named as that project's entry point — but
it is **not confirmed**, and both files' own `require_once`/relative
`__DIR__` paths (see New files below) depend on sitting next to wherever the
real listing-upload handler and chat message handler end up. Whoever places
those two real files should confirm or correct this folder before the
integrator runs, since a wrong guess here breaks the `require_once` in the
real handlers, not just this file's own paths.

## Code Changes
No existing file was changed — both files below are brand new. Per the
guide's own rule ("Created a brand-new file → in the zip... Do not paste its
code"), their code isn't pasted here; see "New files" below and the zip.

## New files
- `rehome/anti-fraud-detectors.php` (path not yet confirmed — ⚠️ see NEW FOLDER banner at the top of this document) — New file — in the zip, inside `rehome/`. The four detector functions plus shared helpers (test-account check, Report-record writer/reader, `held_for_review` setter). Full inline documentation inside the file itself, including the two flagged storage/library assumptions.
- `rehome/anti-fraud-reputation-cron.php` (path not yet confirmed — ⚠️ see NEW FOLDER banner at the top of this document) — New file — in the zip, inside `rehome/`. Three-line wrapper that calls `cfRunReputationAnomalyCheck()` and logs the result; this is detector 4's scheduled entry point.
- `anti-fraud-integration-notes.md` — New file — in the zip, at the zip root (documentation, not a live-site file — see note under Files Changed).

**No HTML pages in this task**, so the strict `index.html`-per-folder
deployment rule doesn't apply to anything C2 produced — noted for
completeness, not a deviation.

## Bugs Found Outside Task Scope
None found in the files read as reference (`publish-pet.php`,
`admin-upload-temp.php`, `chat-send.php`, `pet.json`) — read-only review,
nothing touched, nothing broken. The missing live-path/handler items above
are process/sequencing gaps, not bugs in existing code, and were raised to
the CEO before proceeding per Directive 8b rather than being fixed or
guessed around silently.

## Self-Check Confirmation
Only the two files in "Files Changed" were created; no existing file was
touched or is claimed as touched. Both are new files, so neither carries
`<!-- TASK-C2 START/END -->` anchors (per the guide's own new-file
exception — anchors are for merges into existing files); every function
inside each file has its own explanatory comment instead, confirmed by
re-reading both files end to end just now. **No dropdown menus in this
task.** Everything in the zip is listed above with matching paths; nothing
in the zip is a secret, `config.php`, `pet.json`, `.env`, or under
`applications/`/`uploads/` — confirmed by listing the zip contents
(`rehome/anti-fraud-detectors.php`, `rehome/anti-fraud-reputation-cron.php`
only) before sending.

**Flagged deviations requiring reconciliation, per SHARED CONTRACTS** (unchanged from the prior round of this same work — repeated here for completeness, not reopened):
1. **Storage layer for Report/Review records** is assumed to be flat-file JSON (one file per record, matching `pet.json`'s existing pattern). If Task C1b or Task C6 instead builds on the chat system's DB/PDO pattern (as `chat-send.php` does), only four functions inside `anti-fraud-detectors.php` need replacing — every detector calls only those four for storage.
2. **Account record shape** (`is_test_account`, `created_at`) is assumed to live at `data/accounts/{account_id}.json`. Task E5 owns the real account record schema — reconcile once E5's completion file exists.
3. **`held_for_review`** is set directly on the listing's `pet.json`, matching this task's ownership of that field per SHARED CONTRACTS — confirming for completeness, not a deviation.
4. **Duplicate-photo library**: recommended `intervention/imagehash` (evaluated against two smaller alternatives found in the same search), with a dependency-free GD-based average-hash fallback included and used automatically if the library isn't installed.
5. **Integration itself did not happen** — both real handler files were unconfirmed at submission time. This submission is the detector module plus documented call sites (in the companion notes file), ready to wire in on confirmation, not a completed end-to-end integration.
6. **(New this round) Live path for both files is a proposed best guess, flagged above, not a confirmed fact** — this is the one genuinely new piece of information this hand-in round surfaced, since the delivery guide's path-mapping exercise is what revealed no folder exists yet for this platform at all.

**Verification performed:** unchanged from the prior round — structural
review (balanced braces/parens; no live server exists in this sandbox to
invoke the handlers against, since neither real handler file exists yet to
test against) plus hand-tracing each function against its spec line in the
task doc. Re-confirmed this round: `unzip -l` on the final zip matches the
Files Changed table exactly, and a scan of the zip's file list for
`config.php`/`pet.json`/`.env`/`applications/`/`uploads/` returned none.
