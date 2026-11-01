# Task 2 — Adoption Application Status Checker

## Task Number
Task 2 — Adoption Application Status Checker

## What Was Required
Depends on Task 1. Build a server-side proxy on `status.petcenterco.com`
that fetches `application.json` from `adopt.petcenterco.com` (avoiding
CORS), plus an adapted checker UI reusing Task 1's page chrome. Rate-limit
lookups per IP. Return a generic "not found" that never distinguishes a
wrong code from a nonexistent one. Require a second identifier (e.g. last
name), matched server-side in the proxy, before returning applicant data.
Show status as a timeline (Submitted → Under Review → Approved/Rejected),
not a bare word. Distinguish "temporarily unavailable" (the cross-domain
call itself failed) from "not found" (no such code/name match).

**Note on live paths:** none of this task's files are `adopt.petcenterco.com`
pages, so none appear in WORKER-DELIVERY-GUIDE.md's Section 3 mapping
table (that table is for the main public site's pages). `status.petcenterco.com`
is its own separate subdomain/site root, established across Task 1 and
this task — its folder structure isn't in `adoption-website-project-summary.md`'s
or `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s folder-structure sections either
(neither document covers this subdomain). Per the guide's own fallback
("If your file is not in this table... take its path from the
folder-structure section of the summary document... If the summary and
your assignment disagree, write both... do not guess silently"), the live
paths below are taken from this subdomain's own established root — the
same root Task 1's and this task's own prior completion files already
used — flagged here explicitly rather than guessed against a table that
doesn't cover this subdomain.

## ⚠️ NEW FOLDER / PATH NOTICE — read before merging
None of this task's live paths exist in either `adoption-website-project-summary.md` or `ADMIN_DASHBOARD_PROJECT_SUMMARY.MD` — neither document covers `status.petcenterco.com` at all (it's a separate subdomain, established in Task 1 and extended here). Flagging every path the integrator needs to create, explicitly:

- **`/adoption/` — brand-new folder, does not exist yet.** Create it at the root of `status.petcenterco.com`. It holds exactly one file: `index.html` (this task's checker page). Per the project's strict rule that every live HTML page is named `index.html` inside its own folder, the source file `adoption-status.html` is **not** placed at the root as a bare filename — it deploys as `adoption/index.html`.
- **`/api/` — already exists** (created in Task 1 for `api/status.php`). This task adds one more file into that same existing folder: `api/adoption-status.php`. No new folder needed here, just a new file in an existing one.
- **`/data/ratelimit/` — brand-new subfolder, does not exist yet.** Create it *inside* the existing `/data/` folder (which already holds `status-categories.json` from Task 1 — do not touch that file's own folder-level access). It holds exactly one file: `.htaccess`, which blocks direct web access to the rate-limit files `api/adoption-status.php` writes there at runtime.

Naming convention used for this delivery's zip: `unified-status-dashboard-task-2.zip`, matching the tracker document's own project name (`Unified-Status-Dashboard-Task-Tracker-v3-FINAL.md`) — flagging this choice explicitly in case the intended canonical project-name string differs, so it can be corrected project-wide rather than each worker guessing independently.

**⚠️ Possible path-prefix mismatch — please confirm before merging.** This project has, at other points, also been tracked through a shared monorepo, where `adopt.petcenterco.com`'s files are rooted under a top-level `adopt/` folder (e.g. `adopt/adoptions/status/index.html`, `adopt/admin/index.html`). `WORKER-DELIVERY-GUIDE.md` doesn't mention that convention, and this hand-in treats `status.petcenterco.com` as its own zip root with no prefix (`adoption/index.html`, `api/adoption-status.php`, `data/ratelimit/.htaccess`). If the integrator is merging into that same monorepo rather than deploying `status.petcenterco.com`'s own document root directly, all three paths likely need a matching `status/` prefix instead (`status/adoption/index.html`, etc.). Flagging this explicitly rather than guessing — the files themselves don't change either way, only where they land.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `adoption-status.html` | `adoption/index.html` | New | Not shared. |
| `api/adoption-status.php` | `api/adoption-status.php` | New | Not shared. Task 7 (BNPL Public Status Checker) is spec'd to *extend* this same proxy endpoint later, per the tracker — that will be Task 7's own edit, not retroactive to this submission. |
| `data/ratelimit/.htaccess` | `data/ratelimit/.htaccess` | New | Not shared. |
| `data/status-categories.json` | `data/status-categories.json` | Changed | Pre-existing Task 1 file. One line changed at the CEO's explicit request (uploaded directly, outside this task's original scope) after this task's first round — see Code Changes below. |

## Code Changes

### `data/status-categories.json` → live path `data/status-categories.json`

The `adoption` category's `"available"` flag was flipped from `false` to
`true` now that this task's checker is live at `/adoption/` (its `"link"`
value was already `/adoption/`, so that line is unchanged). This was a
CEO-authorized fix to a Bug Found Outside Task Scope from this task's
first round (see below), not part of the two new files this task
otherwise produced — included here per the "changed existing file" rule
rather than in the zip.

<!-- TASK-2 START -->
**Old code:**
```json
    {
      "id": "adoption",
      "name": "Adoption Application Status",
      "description": "Check the status of a submitted pet adoption application.",
      "state": "operational",
      "link": "/adoption/",
      "available": false
    },
```
**New code:**
```json
    {
      "id": "adoption",
      "name": "Adoption Application Status",
      "description": "Check the status of a submitted pet adoption application.",
      "state": "operational",
      "link": "/adoption/",
      "available": true
    },
```
<!-- TASK-2 END -->

This block (identified by `"id": "adoption"`) appears only once in the file; `bnpl` and `uptime` are separate, untouched blocks later in the same array.

## New files
- `adoption/index.html` — New file — in the zip. The adoption-status checker page: a code + last-name lookup form, rendering status as a Submitted → Under Review → Approved/Rejected timeline. Copies Task 1's header/footer chrome block verbatim (not a shared stylesheet, per this subdomain's established single-file-page convention).
- `api/adoption-status.php` — New file — in the zip. The server-side proxy: fetches `application.json` from `adopt.petcenterco.com` over cURL (server-side, so no CORS issue reaches the browser), rate-limits 10 lookups/5 min per hashed IP, requires the last name to match before returning data, returns one identical generic "not found" for both a bad code and a name mismatch, and reports a real cross-domain failure separately as "temporarily unavailable." Also resolves each additional pet (`additional_pet_ids`) server-side via `pet.json`, for the same CORS reason (see SECOND VERIFICATION ROUND below).
- `data/ratelimit/.htaccess` — New file — in the zip. Blocks direct web access to the per-IP rate-limit timestamp files `api/adoption-status.php` writes at runtime, added in the SECOND VERIFICATION ROUND below. Sits one level inside the existing `data/` folder so it does not affect `data/status-categories.json`'s required web-readability.

## SECOND VERIFICATION ROUND
Re-read `adoption-website-project-summary.md` Section 7 and `status.html` in full again, specifically checking for any data the existing checker uses that the first pass dropped or would need a client-side cross-domain fetch to get. Found and fixed 2 real issues:

1. **Multi-pet applications were incomplete.** The existing on-domain checker (`status.html`, same-origin on `adopt.petcenterco.com`) resolves each additional pet's name/breed/age/photo with its own client-side fetch of `/adoptions/pets/{category}/{petId}/pet.json`. The first pass only showed a bare "Additional Pets: N" count — replicating that fetch client-side from `status.petcenterco.com` would hit the exact cross-domain problem this whole proxy exists to avoid for `application.json`, so it was silently skipped instead of routed through the proxy. **Fixed:** `api/adoption-status.php` now resolves each `additional_pet_ids` entry's `pet.json` server-side too (same cURL pattern, same category-prefix map copied verbatim from `status.html`'s own `catMap`, capped at 10 entries defensively, each pet degrading independently to `{id, error:true}` on failure rather than failing the whole lookup), and `adoption/index.html` now renders the resolved name/breed/age/photo/fee for each — as a stacked list rather than the original's swipeable carousel (simpler to build correctly and verify; same information, different layout, noted here rather than silently passed off as identical).
2. **The new rate-limit file store wasn't access-protected.** `api/adoption-status.php` writes hashed-IP timestamp files into `data/ratelimit/` at runtime — that subfolder had no `.htaccess`, so those files would have been directly fetchable/listable over the web. **Fixed:** added `data/ratelimit/.htaccess` (`Require all denied` / `Deny from all` fallback), mirroring Task 1's established `api/.htaccess` precedent for this exact kind of folder. It sits one level inside `data/`, below `status-categories.json`, so that file's required web-readability for the homepage fetch is unaffected.

Also cross-checked every field name the endpoint reads against `adoption-application.php`'s actual written keys (`additional_pet_ids`, `applicant_name`, `adoption_fee`, etc. — all confirmed exact matches) and against `status.html`'s own field usage for `pet.json` (`name`, `breed`, `age_years`, `single_image_url`, `adoption_fee` — all confirmed). No other missing wiring or bugs found.

Both new/changed files (`adoption/index.html`, `api/adoption-status.php`) in the zip and above already reflect this round's fixes as their final, submitted versions — there is nothing further to replace.

## Bugs Found Outside Task Scope
`data/status-categories.json`'s `adoption` entry was flagged here in this
task's first round and has since been resolved (see Code Changes above) —
no longer outstanding. No other bugs found outside this task's scope.

## Pending / Open Items
None. Everything found during both verification rounds (the `status-categories.json` flag and the two issues in SECOND VERIFICATION ROUND) has already been resolved and is baked into the files in this zip — nothing is left half-done, deferred, or still in doubt for this task.

## Self-Check Confirmation
- Only the 4 files listed above were touched: 3 new files (now in the zip) and 1 pre-existing file with a single documented line change (`data/status-categories.json`, in Code Changes, never in the zip).
- Every code block carries a `TASK-2 START`/`TASK-2 END` anchor comment.
- The one old-code snippet (`data/status-categories.json`'s `adoption` block) was copied character for character from the original file as uploaded, and confirmed to appear only once in that file (identified uniquely by `"id": "adoption"`).
- Every file in `Task-2-NewFiles.zip` is listed under New files above with the same path, and every "New file" entry is present in the zip — checked with `unzip -l Task-2-NewFiles.zip`: `adoption/index.html`, `api/adoption-status.php`, `data/ratelimit/.htaccess`, nothing else.
- Zip contains no `config.php`, `pet.json`, `.env`, secrets, tokens, or anything under `applications/`, `uploads/`, `chat-media/`, or `logs/` — confirmed by direct inspection of its contents (3 files total, all listed above).
- **Second-identifier gate implemented as the real access control, not just UI:** `api/adoption-status.php` requires both a valid `AD-#####-####` code *and* a matching last name before returning any applicant data. Since `application.json` has no dedicated last-name field (confirmed against `adoption-application.php` — `applicant_name` is one full-name string), the match is done by comparing the final whitespace-separated token of `applicant_name` against the submitted last name, case-insensitively.
- **Generic "not found" verified to be genuinely indistinguishable:** both "no such application folder" (a clean 404 from `adopt.petcenterco.com`) and "application exists but the last name didn't match" fall through to the exact same `genericNotFound()` function — same HTTP status, same `error_type`, same message string, same code path.
- **Distinguished from "temporarily unavailable":** a real cross-domain failure (cURL error — timeout, DNS, connection refused, TLS failure) is checked via `curl_errno()` before the HTTP-status/not-found logic runs, and returns a separate `error_type: "unavailable"` with its own message and a 502 status.
- **Rate limiting implemented:** 10 lookups per 5-minute window per IP, file-based (hashed IP as the filename, `flock()`-guarded read-modify-write), matching Task 1's `subscribe.php` pattern. Fails open if the rate-limit file can't be opened, so a disk/permissions issue never blocks legitimate lookups.
- **Status timeline built per Design System Section 16, Type 4 (Status Timeline Stepper)** — 3 stops (Submitted, Under Review, Approved/Rejected), each showing an explicit status word, not a bare status label.
- Structural lint re-run on the final versions of both files (after the Second Verification Round's fixes):
  - `adoption/index.html`: parsed cleanly via BeautifulSoup, no duplicate IDs; inline `<script>` syntax verified with `node --check` (valid).
  - `api/adoption-status.php`: manual brace/parenthesis balance check (26/26 braces, 125/125 parens, 1 opening `<?php` tag, no stray closing `?>` — intentional for a pure-PHP file).
- **Sandbox limitation, stated plainly rather than skipped:** this environment has no PHP binary at all and no network to install one, and no Chromium binary for Playwright with no network to fetch one. The mandatory Playwright visual/interaction check and a real `php -l` syntax check could not be run — the manual brace/paren balance check, BeautifulSoup structural lint, and `node --check` above stand in their place, flagged as unverified-by-real-execution rather than claimed as done. This task has no CEO-supplied reference image, so the image-reference gate doesn't apply the way it would on a design-fidelity task.
- Scope Lock held throughout, including this hand-in reformat: no file outside this task's original 4 was touched, and no code was changed while updating the hand-in format — only the completion document's structure and the zip packaging changed.
