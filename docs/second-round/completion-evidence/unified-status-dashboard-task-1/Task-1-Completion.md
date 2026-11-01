# Task 1 — status.petcenterco.com Subdomain Shell

## Task Number
Task 1 — status.petcenterco.com Subdomain Shell

## What Was Required
Build the base shell for a new subdomain, `status.petcenterco.com`: its own
header/footer (distinct from `cats.html`'s), a homepage listing the
status-check categories (adoption, BNPL, uptime), a shared page chrome/CSS
pattern for future pages on this subdomain to copy, status meaning that's
never color-alone (icon + color + text label), badge contrast checked
against the Navy/Gold/Orange palette, a shell kept light on third-party
CDN/font dependencies, a plain JSON endpoint mirroring the homepage's
status summary, and an email "subscribe to updates" capture form
(collection only — no sending).

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `index.html` | `index.html` | New | Not shared — Tasks 2, 7, 8 must *copy* this file's header/footer/CSS block into their own new pages, not edit this one. |
| `subscribe.php` | `subscribe.php` | New | Not shared — Task 9 *reads* the `data/subscribers.txt` file this writes at runtime, but does not touch this script. |
| `status.php` | `api/status.php` | New | Not shared. |
| `.htaccess` | `api/.htaccess` | New | Not shared. |
| `status-categories.json` | `data/status-categories.json` | New | Not shared as a file, but its `bnpl`/`uptime` entries are meant to be flipped to `"available": true` by whoever ships those checkers (Tasks 7 and 8) — see note below. |

**Live-path note:** this table isn't covered by the guide's source→live-path
mapping (that table is for `adopt.petcenterco.com`'s existing structure).
Task 1 built a brand-new subdomain with no prior live structure to map
against, so each live path above is simply the path relative to
`status.petcenterco.com`'s own site root — the same relative layout the
files were built and tested with. Per the guide's instruction for files not
in its table, stating this explicitly rather than guessing silently.

**New folder paths — none of this task's paths exist in either source doc,
flagging as heavily as asked:**
- Neither `adoption-website-project-summary.md`'s folder structure (which
  documents `public_html/adopt/...` only) nor
  `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s (the admin dashboard's own
  structure) contains any `status.petcenterco.com` path at all — this
  entire task is a brand-new subdomain, so **every single path in this
  zip is a new folder that needs creating**, not just one or two files
  inside an existing tree.
- **Create a new subdomain document root** for `status.petcenterco.com`
  on Hostinger (its own `public_html/`-equivalent folder, however
  Hostinger's subdomain setup names it — likely `public_html/status/`
  as a sibling to the existing `public_html/adopt/`, but that's a
  hosting-panel setup step, not something determinable from the project
  docs or this zip alone).
- Inside that new subdomain root, create two new subfolders:
  `api/` (holds `status.php` and `.htaccess`) and `data/` (holds
  `status-categories.json`, and will also end up holding
  `subscribers.txt` once `subscribe.php` runs for the first time —
  that file doesn't ship in the zip since it's runtime-generated, not
  a build artifact).
- `index.html` and `subscribe.php` sit directly in that new subdomain
  root — no subfolder for either.

**`index.html` naming convention — confirmed compliant:** the one HTML
page this task ships is already literally named `index.html` and deploys
at the subdomain's root (`status.petcenterco.com/` → `index.html`), per
the strict index.html-for-every-live-HTML-page convention. Future pages
built on this subdomain (Tasks 2/7/8's checkers) should follow the same
pattern this subdomain's own sibling project already uses elsewhere
(e.g. `/adoption/index.html`, not `/adoption/adoption-status.html`).

## Code Changes
None. Every file in this task is brand-new — Task 1's own spec states
"New files only, under a new status.petcenterco.com root. Does not touch
adopt.petcenterco.com or admin dashboard," so there is no existing file
with an old/new snippet to show. All five files are in
`Task-1-NewFiles.zip` instead, per the guide's rule that changed-existing
goes in this document and brand-new goes in the zip.

## New files
- `index.html` — New file — in the zip. The subdomain homepage: header/footer, category tiles for Adoption/BNPL/Uptime status, subscribe-to-updates form, all CSS/JS inline (no external CDN).
- `subscribe.php` — New file — in the zip. Validates and appends submitted emails to `data/subscribers.txt` for Task 9 to read later.
- `api/status.php` — New file — in the zip. Plain JSON endpoint mirroring the homepage's category status summary.
- `api/.htaccess` — New file — in the zip. Rewrites the clean URL `/api/status.json` to `status.php`.
- `data/status-categories.json` — New file — in the zip. Shared data source read by both `index.html` and `api/status.php`.

## Bugs Found Outside Task Scope
None found.

## Self-Check Confirmation
- Touched only the 5 files listed above — all new, nothing pre-existing was edited, consistent with Task 1 having no dependencies and no "files to request."
- No "Code Changes" blocks exist because nothing pre-existing was changed; nothing was force-fit into that section to satisfy the format.
- Every path in the zip is listed in Files Changed with the matching live path; every "New file" entry is in the zip — verified with `unzip -l`, 5 files, 5 matching rows.
- Ran a secrets scan (password/secret/api key/private key/AWS/DB password/PEM-header patterns) across all 5 files before zipping — none found. None of the excluded categories (`config.php`, `pet.json`, `.env`, `applications/`, `uploads/`, `chat-media/`, `logs/`) apply — this task never touches any of them.
- `htaccess.txt` (a renamed duplicate of `.htaccess` made earlier only as a download workaround for a tool that mishandled dotfiles) is deliberately **not** in the zip — it was never a real site file, only a copy for convenience, and shipping it would place a stray non-site file at the subdomain root.
- Badge-contrast math, PHP error-suppression fix, and the other findings from this task's prior verification rounds are unchanged by this hand-in-format update — no functional code was touched, only this document's structure and the packaging step.
- This completion doc is now bundled **inside** the zip itself (at the zip root, alongside `index.html`), not delivered as a separate file, per the corrected packaging rule.
- Zip name uses `unified-status-dashboard` as the project-name slug (lowercase), matching the exact slug other workers on this same project have already converged on for Tasks 2 and 3 — corrected from an initial attempt that used the full tracker filename verbatim (`Unified-Status-Dashboard-Task-Tracker-v3-FINAL`), since that would have reintroduced the exact cross-worker naming inconsistency this correction was meant to fix. Matching the already-established pattern is more useful for sorting than either worker's independent literal reading of the instruction.
- **Scope note on the "many workers" correction:** this session only has Task 1's actual files physically available to repackage. Task 2 and Task 3 were completed in separate later sessions (per project history) and their files aren't in this workspace — this corrected packaging format needs to reach whichever session/thread is holding those, or those files can be re-uploaded here for repackaging if preferred.
