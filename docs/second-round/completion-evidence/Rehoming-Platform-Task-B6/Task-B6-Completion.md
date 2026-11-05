# Task B6 — Completion File

## Task Number
B6 — "TASK B6 — Merchant public profile page: base structure"

## What Was Required
Build the base structure of the merchant public profile page. The site has no organization/profile-style page (confirmed by the CEO), so the body is a fresh build. Header, footer, and hamburger come from `cats.html` (Directive 13) and the listings grid reuses `cats.html`'s real pet-card component (Rule 7). `cats.html` is both the header/footer blueprint and the "existing page that shows pet cards in a grid", so no second file was needed. The page publishes seven named, anchor-commented sections so B4, G1, C6 and C1a can build into it in parallel:

1. `header` — own content (merchant name + photo)
2. `badges` — reserved, Task B4
3. `impact-stats` — reserved, Task B4
4. `donation-links` — reserved, Task G1
5. `listings-grid` — own content (this merchant's active listings)
6. `reviews` — reserved, Task C6
7. `actions` — reserved, Task C1a (Report button) and Task C6 (thumbs-up/down)

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `merchant-profile-v1.html` (source name — new page, not in the source-name→live-path table) | `merchants/index.html` **— assumed, flagged below** | New | Yes — sections 2, 3, 4, 6, 7 are reserved anchor-commented zones for B4, G1, C6, C1a |
| `cats.html` | `pets/cats/index.html` (per the mapping table) | Neither — read-only clone source, not modified. Listed here for transparency only; nothing to merge. | No |

> ## NEW FOLDER REQUIRED — INTEGRATOR, READ THIS
> **Create a brand-new top-level folder `merchants/` in the site root** (the folder that holds `index.html`, `pets/`, `blog/`, etc. — `public_html/adopt/` on the server). It does **not exist** in `adoption-website-project-summary.md` (Section 1, Server Folder Structure) or in `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`.
> The new page goes at **`merchants/index.html`** (served at `adopt.petcenterco.com/merchants/?merchant_id={id}`). Per the site rule, the page is a folder containing `index.html`, never a loose `.html` file.
> Please also **add `merchants/` to the folder-structure tree in Section 1 of `adoption-website-project-summary.md`** so the tree stays the single source of truth.

**Why this path:** neither the delivery guide's mapping table nor the folder-structure sections list a path for the merchant public profile page (new page type from the Rehoming Platform project). `merchants/index.html` follows the single-template-page-plus-query-string pattern the site already uses for `pets/detail/?pet_id={id}&category={cat}`; the page reads `?merchant_id=` on that basis. This is my assumption, not a confirmed path — a different path is a one-line rename (CEO to confirm).

**Zip:** `Rehoming-Platform-Task-B6.zip` (named from the project file `Rehoming-Platform-Task-Assignments.md` + task number). It holds both the new file at its live path and this completion file at the zip root.

## Code Changes
No existing files were changed. See **New files** below.

## New files
- `merchants/index.html` — merchant public profile page, base structure. **New file, in a NEW folder `merchants/` (create it — see the notice above).** New file — in the zip (`Rehoming-Platform-Task-B6.zip`).
- `Task-B6-Completion.md` — this document, at the zip root. Documentation only: **do not deploy it to the live site.**

## Bugs Found Outside Task Scope
Nothing below was changed; each is logged per Directive 8b.
1. **No listing-to-merchant link in SHARED CONTRACTS.** The Listing core schema (A1) and relationships (A2) have no `merchant_id`-style field, yet B6's listings-grid (and A3, E1a) need one. B6 assumed `merchant_id` in its stub. Needs a decision from the CEO/A1.
2. **No merchant account record contract.** The header needs merchant name and photo; no task owns those field names or where the photo comes from. B6 stubs `name` and `photo_url` only.
3. **`cats.html` footer grid mismatch.** `.pc-footer__top` is 4 columns (`2fr 1fr 1fr 1.5fr`, cats.html lines 893-899) but the markup has 5 blocks (lines 1158-1210), so "Visit Us" wraps to a second row on desktop. Copied as-is.
4. **`cats.html` uses old status strings** (`'Adopted'`, line 2136) versus the contract enum (`active`, `pending_placement`, ...). The cloned card inherits the old ADOPTED check; D1/A3 must reconcile.
5. **Unescaped HTML in the cloned card renderer.** `cats.html` inserts name/breed/size/image into HTML unescaped, safe only while admins were the sole authors. Merchants are untrusted, so any page reusing this renderer needs escaping. B6 escapes inside its own page (`sanitizeListing`) without touching the cloned code; `cats.html` itself still has the issue.
6. **`PETCENTER_DESIGN_SYSTEM_v1_0.md` Section 26 is stale** versus `cats.html` (old `/rehome/` link, 5 categories, no Golden Triangle line, different footer CSS). `cats.html` was followed.
7. **No live path published for the merchant profile page** (see Live-path flag above) — not a code bug, a documentation gap.

### Pending, open items and doubts (work is complete for B6's scope, but these remain open)
1. **Confirm the live path** `merchants/index.html` (new folder `merchants/`) — assumption, see Files Changed.
2. **Stub data must be swapped for real endpoints.** `STUB_MERCHANT` (name, photo) and `STUB_LISTINGS` in the page's script are placeholders. Once A1 publishes the listing-to-merchant field and a merchant account record exists, replace them with real fetches. Until then the page shows demo data (a demo merchant and sample pets).
3. **`merchant_id` is my assumed field name** on listings and on the `?merchant_id=` URL parameter; reconcile with A1's completion file.
4. **Section hand-offs not yet done (by design):** `badges` and `impact-stats` (B4), `donation-links` (G1), `reviews` (C6), `actions` (C1a Report button, C6 thumbs). Each currently shows a labeled placeholder box; the owning task must replace the placeholder content inside its own `<!-- MERCHANT-PROFILE: name -->` block and remove the placeholder.
5. **Visitor link target unverified:** listing cards link to `/pets/detail/?pet_id=...&category=...` (the existing pet-detail page). Not tested against the live site.
6. **Images and logos** use the same `/assets/images/...` paths as `cats.html` (logos, `pet-placeholder.jpg`); they appear broken only in my sandbox, but I could not verify they exist on the live server.
7. **Old-status handling:** the copied card still checks the legacy status string `'Adopted'` (see Bugs, item 4); this page filters to `active` before rendering, so it is not affected today, but D1/A3 should reconcile.
8. **Merchant photo source is undecided** (upload location and who controls it are not defined by any task); the page uses an inline fallback silhouette when no photo is available.

## Self-Check Confirmation
- Only `merchants/index.html` (source: `merchant-profile-v1.html`) was created; no existing file was edited. Every new block carries `<!-- TASK-B6 START/END -->` (or `/* TASK-B6 */` in CSS/JS), and all seven sections carry `<!-- MERCHANT-PROFILE: name -->` / `<!-- END MERCHANT-PROFILE: name -->` pairs.
- **No dropdown menus in this task.**
- **Shared contracts:** built against Listing `status` enum and `held_for_review` (honored in the filter) and `grouped_with`/`needs_extra_love` (rendered via the cloned card). Did not write to the Report record, Review record, or `is_test_account`; B6 only reserves the DOM locations (`actions`, `reviews`). Reconciliation pass is pending until the owning tasks' completion files exist; the unconfirmed assumption is `merchant_id` (bug 1 above).
- Did not request the signup/login pages.
- **Machine verification against `cats.html`:** header and footer markup identical (only difference: `&amp;` instead of a bare `&` in three footer link labels, same rendering); every CSS rule outside the TASK-B6 block matches a `cats.html` rule exactly (checked rule by rule, 0 mismatches); hamburger and scroll JS present.
- **Behavior tests (Playwright):** 7 stub listings render as 3 cards (one bonded pair merged; held, pending and other-merchant listings correctly excluded); unknown `?merchant_id=` shows the empty state; no console errors; no horizontal overflow at 1440px or 390px; an HTML-injection test string in a listing name rendered as text, not markup; hamburger opens and closes.
- Design rules: no emojis, no borders on cards (shadows only), internal links are clean folder paths. Judgment call: body uses `cats.html`'s 1280px `.pc-container`, not the design system's untested Section 15 1100px grid.
- **Zip check:** `unzip -l Rehoming-Platform-Task-B6.zip` shows exactly two files: `merchants/index.html` (matches the one "New file" row and the one path in Files Changed) and `Task-B6-Completion.md` at the zip root (this document). Rooted at the site root, no wrapper folder, no spaces or capitals in paths. No `config.php`, `pet.json`, `.env`, secrets, or anything under `applications/` or `uploads/` is in the zip (source grepped for those terms; the only hits were code comments describing `pet.json`'s field shape).
- Screenshots on file from the build: `b6_desktop_top.png`, `b6_desktop_full.png`, `b6_mobile_top.png`, `b6_mobile_full.png`, `b6_mobile_menu_open.png`, `b6_listings_grid.png`, `cats_reference_top.png`.

## SECOND VERIFICATION ROUND
Re-read the task, both project summaries, SHARED CONTRACTS and the directives, then re-checked the build. Changes made to the page (then named `merchant-profile-v1.html`) only:
1. **Card was not actually verbatim.** The first version omitted the diary link, Needs-Extra-Love badge, bonded-pair card and their CSS, and used hand-typed footer CSS taken from the stale design-system copy. Replaced with exact blocks from `cats.html`, then machine-verified.
2. **Visitors could have seen held listings.** The contract says a `held_for_review` listing never appears to visitors; the first version had no such filter. Added it, plus the exact `active` status and per-merchant filter (first version used `'Available'`).
3. **Removed invented fields.** Location and member-since were not in the spec and had no contract; header is now name + photo only.
4. **Removed a dependency on a missing asset.** `merchant-placeholder.jpg` is not a known site file; replaced with an inline SVG fallback.
5. **Design rule fix.** Dashed borders, divider lines and the avatar border replaced with shadow-based styling.
6. **Added escaping** for merchant-authored text (bug 5 above) and function-level comments per Rule 6.
7. Corrected the earlier "None found" bug entry to the items above.

## THIRD — HAND-IN FORMAT UPDATE (per WORKER-DELIVERY-GUIDE.md and the CEO's follow-up on zip naming)
No code was changed in this pass — per the CEO's instruction, only the hand-in was fixed:
- Rewrote **Files Changed** as a four-column table (source name, live path, changed/new, shared) and added the **New files** section, both per the guide.
- Source file `merchant-profile-v1.html` renamed to its live path `merchants/index.html` inside the new-files zip (`Task-B6-NewFiles.zip`); no content change.
- `cats.html` added to Files Changed as a read-only reference row for transparency (it was never modified, so it is not in the zip and has no Code Changes entry).
- Flagged that the merchant-profile page's live path is not published anywhere and stated the assumption made (`merchants/index.html`).
- Re-confirmed via `unzip -l` and a secrets/forbidden-path grep that the zip contains exactly one file and nothing disallowed.
- CEO follow-up applied: a single zip named `Rehoming-Platform-Task-B6.zip` (project file name + task number) now holds both the new file and this completion file; the earlier `Task-B6-NewFiles.zip` is superseded and removed. Added the heavy new-folder notice, the index.html naming note, and the pending/doubts list above.
