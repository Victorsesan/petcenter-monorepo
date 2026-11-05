# Task F1 Completion — Pet Alerts + Favorites

**Delivery:** this file is INSIDE `Rehoming-Platform-Task-F1.zip` together with the new files (CEO's rule: completion file + all new files in one zip). Name = project file name `Rehoming-Platform-Task-Assignments.md` -> project `Rehoming-Platform`, + task `F1`. **INTEGRATOR: this completion file sits at the zip root for reading only. Do NOT copy it into the live site. Copy only the folders `account/`, `assets/`, `cron/`.**

*(Rewritten in full to match WORKER-DELIVERY-GUIDE.md's format, per its instruction that this replaces every earlier hand-in format. Earlier rounds' findings are preserved below under their matching new sections — nothing from prior rounds was dropped, just relocated.)*

## Task Number

F1 — Pet Alerts + Favorites

## What Was Required

Two gated, account-only visitor features: (1) **Pet Alerts** — a logged-in visitor saves a named search built from A3's filter set; a daily scheduled check compares newly-active listings against every saved search and sends one batched digest email per visitor, never one email per listing. (2) **Favorites** — a bookmark icon on listing cards lets a logged-in visitor save a listing, with its live `status` shown using the exact canonical enum values (`active`, `pending_placement`, `placement_complete`, `placement_fell_through`, `no_longer_available`), never a paraphrase. Both are gated behind `showAuthPrompt()` (Task A4's shared component) for logged-out visitors — this task does not build its own auth prompt. File zone: the visitor account area (built fresh — none existed) and the listing data model (read-only).

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `visitor-account.html` | `account/index.html` *(not in the guide's mapping table — my own choice, flagged below; please confirm or correct)* | New | Flagged — Task B5 also targets "the visitor account area" as its file zone; see note below. |
| `favorites-widget.js` | `assets/js/favorites-widget.js` *(per the guide's "Shared CSS/JS under assets/" rule)* | New | Built for insertion into A3's browse/search page and `pets/detail/` by whoever owns those files — not inserted by this task (Scope Lock). |
| `pet-alerts-widget.js` | `assets/js/pet-alerts-widget.js` | New | Same as above — insertion target is A3's Bottom Sheet, outside this task's file zone. |
| `pet-alerts-cron.php` | `cron/pet-alerts-cron.php` *(not in the guide's mapping table — my own choice, flagged below)* | New | Flagged — spec says "same cron pattern as A2/B3/C2"; no existing pattern exists yet to share. |

## NEW FOLDERS TO CREATE (NOT FOUND IN EITHER SUMMARY DOC) -- INTEGRATOR READ THIS

Checked both `adoption-website-project-summary.md` and `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`: **none of the paths below appear in either folder structure.**

| NEW folder to create | Where (inside site root = `public_html/adopt/`) | What goes in it | Status |
|---|---|---|---|
| **`account/`** | site root, next to `signin/`, `apply/`, `blog/` | `index.html` (visitor account page, live at `adopt.petcenterco.com/account/`) | **BRAND-NEW FOLDER. My own proposal, not CEO-confirmed. CEO must confirm the name.** |
| **`cron/`** | site root | `pet-alerts-cron.php` (must sit exactly one level below site root; the script finds the site root with `dirname(__DIR__)`) | **BRAND-NEW FOLDER. My own proposal, not CEO-confirmed.** Must NOT be publicly browsable; protect it (e.g. `.htaccess` deny, or only run via CLI cron). The script already returns 403 to web calls without a secret key. |
| **`assets/js/`** | inside existing `assets/` (which already holds `assets/images/`) | `favorites-widget.js`, `pet-alerts-widget.js` | **Not in either summary.** The delivery guide's mapping table names `assets/js/` for shared JS, so I used it; create the subfolder if it does not already exist. |
| `adoptions/pet-alerts/` | inside existing `adoptions/` | NOT in the zip. Created at runtime by the cron script (`mkdir`) and filled by the account backend. | Runtime-created data folder. Do not ship; do not make it web-readable (contains visitor emails). |

All pages follow the index.html rule: the account page is `account/index.html`, never a loose `account.html`.

No existing live file was changed by this task — every file above is brand-new, so there is no "old code" to find by exact-text match in any live file.

## Code Changes

Not applicable — no existing files were changed. All four deliverables are brand-new files; their full content is in `Rehoming-Platform-Task-F1.zip`, not pasted here, per the guide's rule that new-file code goes only in the zip.

## New files

- `account/index.html` — new visitor account area page (Favorites + Pet Alerts tabs). Now carries the real site header, hamburger menu, and footer, cloned verbatim from `cats.html` (Rule 13) — previously a placeholder, replaced this round.
- `assets/js/favorites-widget.js` — standalone bookmark-icon component with a documented insertion contract for A3's listing cards and `pets/detail/`.
- `assets/js/pet-alerts-widget.js` — standalone "Save this search" trigger with a documented insertion contract for A3's Bottom Sheet; captures A3's existing filter state rather than rebuilding filter UI.
- `cron/pet-alerts-cron.php` — daily digest script (CLI/cron entry point), using the platform's documented `mail()` pattern (matched against the uploaded `adoption-application.php`) and reading the existing `pet.json` folder structure.

All four are listed in `Rehoming-Platform-Task-F1.zip` at the paths above; verified with `unzip -l` before sending (output below).

```
account/index.html
assets/js/favorites-widget.js
assets/js/pet-alerts-widget.js
cron/pet-alerts-cron.php
```

## Bugs Found Outside Task Scope

None found in another task's existing file (no other task's live files were available to inspect). One cross-task design overlap worth flagging for awareness, not a bug and not fixed by me: Task F3's planned weekly digest explicitly folds in Pet-Alert matches (F1) into its own email — alongside this task's own daily Pet-Alert-only digest, a visitor could receive overlapping Pet-Alert content twice. Worth the CEO/VDM deciding before F3 is built.

## Self-Check Confirmation

- Only this task's four files were touched or created; no existing live file was edited.
- Every block in all four files carries its `TASK-F1` anchor comment (`<!-- TASK-F1 START/END -->` in the HTML, `/* TASK-F1 START/END */` in the `.js`/`.php` files).
- No "Code Changes" old/new blocks exist because nothing existing was changed — not omitted, genuinely not applicable.
- Every file in `Rehoming-Platform-Task-F1.zip` is listed (plus this completion file at the zip root, which is not a site file) in Files Changed with the same path, and every "New" row in Files Changed is in the zip — confirmed via `unzip -l`, shown above.
- No `config.php`, `pet.json`, `.env`, or anything from `applications/`/`uploads/`/`chat-media/`/`logs/` is in the zip.
- `pet-alerts-cron.php` needs secrets/config read from `config.php`, never hardcoded — it conditionally `require_once`s a `config.php` if present and reads these optional constants from it: `PET_ALERTS_CRON_KEY` (secret for web-triggered runs — cron/CLI runs don't need it), `PET_ALERTS_SITE_ROOT` (override if the script isn't deployed exactly at `cron/`), `PET_ALERTS_MANAGE_URL` (the account page's real link once confirmed), `LOG_FILE` (reused from the existing convention if defined).
- Tested with the tools available: `node --check` on all three JS/HTML-embedded-script files (syntax clean), HTML tag-balance check (header/footer/script all open exactly once, close exactly once), and a manual line-by-line diff of the cloned header/footer/hamburger CSS, markup, and JS against `cats.html` to confirm it's copied verbatim, not reconstructed. **Not tested:** `pet-alerts-cron.php` with `php -l` — PHP is not installed in my environment; please lint it on the server before scheduling.
- Two live paths (`account/index.html`, `cron/pet-alerts-cron.php`) are not in the guide's source-name → live-path table and were not found in either project summary's folder-structure section either — these are my own proposed paths, written here rather than guessed silently, per the guide's instruction. Please confirm or correct both before the integrator runs.

## Open items — nothing below is finished, each has a named owner

| # | Item | Why it can't close in this task | Hand-off |
|---|---|---|---|
| 1 | **Server-side storage for favorites and saved searches.** All three new files use `localStorage` as a stand-in. The cron script has nothing real to read until this exists. | Writing per-visitor data safely needs a verified login/session, which is CEO-owned and out of scope. | Whoever builds the account backend writes `/adoptions/pet-alerts/{sanitized email}.json` (shape documented at the top of `pet-alerts-cron.php`) and swaps the `Store`/widget storage calls for real API calls. **The digest sends nothing until this is done.** |
| 2 | `PET_ALERTS_MANAGE_URL` / browse-page link | Real deployed paths not yet confirmed for either. | Set in `config.php` / update the `href="#"` placeholder in `account/index.html` once confirmed. |
| 3 | `account/index.html` and `cron/pet-alerts-cron.php` live paths | Not in the guide's mapping table or either summary's folder structure — my own proposal. | CEO confirms or corrects; integrator should not assume these are final without that confirmation. |
| 4 | Stubs awaiting reconciliation: A4 `showAuthPrompt()`, A3's exact filter field names, D1/E2's status enum, B2's listing-level verified field, A1's `good_with_kids`/`good_with_other_pets` sub-fields | Owning tasks' completion files don't exist yet. | Integration-checkpoint reconciliation pass; all marked `RECONCILE` in code. |
| 5 | Insertion of `favorites-widget.js` / `pet-alerts-widget.js` into A3's Bottom Sheet and listing cards, and `pets/detail/` | Outside this task's file zone (Scope Lock). | A3's and `pets/detail/`'s owner follows each widget's header-comment insertion contract. |
| 6 | Shared cron runner (if A2/B3/C2 build one) | Doesn't exist yet. | Port `runDailyPetAlertsCheck()` into it instead of scheduling `cron/pet-alerts-cron.php` directly. |

## PENDING / UNRESOLVED -- ONE-GLANCE LIST (nothing hidden elsewhere)

1. **Digest sends nothing until server-side storage exists** (Open item 1). Biggest gap; blocked on the CEO-owned login/account backend.
2. **`cron/pet-alerts-cron.php` is not lint-tested** (no PHP in my environment). Run `php -l` before scheduling.
3. **Two proposed new folders (`account/`, `cron/`) need CEO confirmation**, plus `assets/js/` existence check (section above).
4. **Placeholders needing real values:** `PET_ALERTS_MANAGE_URL`, the browse-page `href="#"` in `account/index.html`.
5. **Stubs to reconcile** with A4, A3, D1/E2, B2, A1 completion files (all marked `RECONCILE`).
6. **Widgets not yet inserted** into A3's page / `pets/detail/` (Scope Lock); owners follow header-comment contracts.
7. **Overlap with F3's weekly digest** (design decision for CEO/VDM).
8. **Legacy status mapping:** cron maps legacy `"Available"` to `active` so it works on today's data; remove once A1/D1's new enum is live.
9. **Header "Sign Up" link** is cloned verbatim from `cats.html`; it does not change for logged-in visitors (header personalisation is outside F1).
