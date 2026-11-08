# Task F4 Completion — First-time-merchant guided listing flow

## Task Number
TASK F4 — First-time-merchant guided listing flow

## What Was Required
Add inline "first-time merchant" guidance to the merchant pet-posting
form (the same file Task A1 clones from `admin-dashboard.html`'s
publish-pet panel and builds out). Specifically: next to each of A1's
required free-text fields (`health_status`, `temperament`,
`reason_for_rehoming`), a small expandable "Why this matters" hint
explaining why that field is worth filling in carefully. Separately,
photo guidance: raise the photo requirement from "recommended" to a
hard minimum of 3 photos before a listing can publish, with a hint
line about lighting/angle quality.

Because Task A1 had not published a completion file yet at the time
this task started, this task built its own field-shape-matching stub
of A1's listing schema (per the SHARED CONTRACTS mechanism in
`Rehoming-Platform-Task-Assignments.md`) rather than waiting — the
stub exists only so the guidance hints have real fields to attach to,
and is explicitly not this task's implementation of those fields.

## ⚠️ NEW FOLDER / NEW PATH — NOT FOUND IN EITHER SUMMARY DOC — PLEASE CONFIRM

**Folder to create:** `merchant/` (new top-level folder, doesn't exist in
either summary doc's folder structure today)
**Then inside it:** `merchant/post-listing/` (new subfolder)
**File inside that:** `index.html` (strict index.html naming, not
`pet-posting-form.html` or any other bare filename)
**Resulting path:** `merchant/post-listing/index.html`

This is a **placeholder path, not a verified one** — see the full
reasoning immediately below. Please confirm before treating it as final.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `pet-posting-form.html` | `merchant/post-listing/index.html` **(new folder — unconfirmed, see callout above and note below)** | New | Yes — field stub shared with Task A1; page itself likely needs to merge into Task E1's merchant dashboard shell (neither built yet) |

**Live path note (flagging, not guessing silently):** this path is not in
the WORKER-DELIVERY-GUIDE mapping table, and neither
`adoption-website-project-summary.md` nor `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`
has a folder-structure entry for it, because the page this task modifies
is itself brand new — created in parallel by this task and Task A1, with
no live URL yet. There's a second, more structural uncertainty worth
flagging: in `admin-dashboard.html`, the equivalent "Publish Adoptable
Pets" panel is **not a separate page** — it's a panel inside the single
`admin/index.html` dashboard file. Task E1a's own wording ("a 'post new
listing' entry point **routes into** A1's cloned posting panel") reads the
same way — as an in-dashboard panel switch, not a navigation to a separate
URL. If that's correct, this file may not need its own live path at all;
it may need to be merged as a panel *into* Task E1's merchant dashboard
shell file once E1 builds it, the same way the admin version lives inside
`admin/index.html` rather than its own file.

Since that can't be confirmed from the documents on hand, the zip below
places it at `merchant/post-listing/index.html` — `merchant/` chosen only
as the parallel to admin's `admin/` top segment (not confirmed anywhere;
Task E1 hasn't named its directory yet), `post-listing/` per the
folder-per-page convention in case it does ship as its own page. **Please
confirm with Task E1's worker (or re-route this at integration) before
treating that path as final** — it's a best-effort placeholder, not a
verified path.

## Code Changes
No existing files were changed in this task — the only deliverable is
the brand-new file above. There is no old/new code pair to show; see
New Files below for what it contains and where the anchor comments are.

## New files
- `merchant/post-listing/index.html` *(placeholder path — see Live path
  note above)* — the merchant pet-posting form: a field-shape stub of
  Task A1's listing schema (name/species/breed/age + the three A1
  text fields this task's hints attach to), plus this task's own
  "Why this matters" expandable hints on `health_status`,
  `temperament`, and `reason_for_rehoming`, and a minimum-3-photos
  requirement (hard gate on Publish, not just a recommendation) built
  on Section 7's canonical file-upload component. All of this task's
  own additions are wrapped in `<!-- TASK-F4 START -->` /
  `<!-- TASK-F4 END -->` anchor comments at four locations inside the
  file (the hint component CSS, the three hint blocks, the
  minimum-photo CSS, and the photo-count/publish-gate markup + JS);
  the A1-contract stub portions are separately marked as stub
  placeholder rather than tagged as TASK-F4 work, so Task A1's worker
  can tell at a glance which parts are this task's real contribution.
  New file — in the zip (`Rehoming-Platform-Task-F4.zip`).

## Bugs Found Outside Task Scope
None found in any existing code (this task only ever read
`admin-dashboard.html` and the design system as clone sources — it never
edited them). Two pending/open items to track, neither a bug in anyone's
delivered code, both already flagged above but repeated here per the
"document anything pending in the completion doc" instruction:

1. **Live-path/merge-target uncertainty** (full detail above, under the
   ⚠️ callout and the Files Changed note) — this page's eventual home
   depends on decisions in Tasks A1 and E1, neither finished yet.
2. **Placeholder photo-upload endpoint.** The delivered file's photo
   field uploads to `PHOTO_UPLOAD_ENDPOINT = '/rehoming/photo-upload-temp.php'`,
   a guessed path modeled on the admin dashboard's own
   `admin-upload-temp.php` pattern — not a confirmed real endpoint. It's
   marked `// TODO (outside F4 scope): confirm real endpoint at
   integration` in the file itself. Whoever owns the merchant-side
   temp-upload backend (A1 or a backend task) needs to confirm the real
   path, or this field will fail to actually persist photos even though
   the UI and the minimum-3 gate both work correctly against the local
   file list.

## Self-Check Confirmation
- Only one file was touched/created: `pet-posting-form.html` → zipped as
  `merchant/post-listing/index.html` (placeholder path, flagged above) —
  matches this task's File zone (pet-posting form, shared with A1).
- No existing files were changed, so there are no "old code" snippets to
  verify against an original file — not applicable this round.
- Every block this task actually authored carries a
  `TASK-F4 START`/`TASK-F4 END` anchor comment; the A1-contract stub
  portions are marked separately as stub/placeholder, not tagged as
  TASK-F4, so they aren't miscredited to this task at integration.
- The zip (`Rehoming-Platform-Task-F4.zip`) contains exactly two files:
  this completion doc (`Task-F4-Completion.md`, at the zip root) and the
  one new file, `merchant/post-listing/index.html`, matching the one row
  in Files Changed above — no mismatch between the zip and the table.
- Zip contents checked against the forbidden list: no `config.php`, no
  `pet.json`, no `.env`, nothing under `applications/`, `uploads/`,
  `chat-media/`, or `logs/`, no secrets/passwords/tokens/keys. The zip
  holds a single static front-end HTML file with no credentials in it.
- No dropdown/select menus exist in the delivered file (removed during
  the second verification round — see below) — nothing to check against
  Directive 9a.
- Tested with the tools available: ran the file through a Python tag-balance
  check (div/svg/button counts, script/style excluded) and a Node syntax
  parse of the inline `<script>` block; both passed clean. No live browser
  was available in this environment, so the drag/keyboard upload
  interactions and hint expand/collapse were verified by reading the
  wired-up code path rather than by clicking through a rendered page —
  flagging that distinction rather than overstating what was tested.

## SECOND VERIFICATION ROUND

Re-read `adoption-website-project-summary.md`, `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`,
`Rehoming-Platform-Task-Assignments.md` (BUILD APPROACH, VISIBILITY & ACCESS
MODEL, SHARED CONTRACTS, Task F4's own section), and
`PETCENTER_DESIGN_SYSTEM_v1_0.md` Sections 1, 2, 6, 7, 8, 11, and 18 in full
before re-checking the delivered file against them. Found and fixed four real
issues in the first draft — none were cosmetic, so none were skipped:

1. **Directive 9a violation.** The A1-contract stub originally included two
   native `<select>` dropdowns (`spay_neuter_status`, `listing_type`).
   Directive 9a requires any dropdown built anywhere in this project to
   use the Section 24 custom dropdown shell, never a native `<select>` —
   and building a compliant custom dropdown for fields this task doesn't
   actually need would itself be scope creep (Directive 8b). Removed both;
   the stub now only includes the three text fields F4's hints attach to.
   Documented the reasoning inline so A1 knows why they're absent.

2. **Rule 7 violation on the photo upload control.** The first draft's photo
   "upload" was a bare `<input type="file" onchange=...>` with a naive
   incrementing counter — no drag support, no keyboard access, no
   validation, no remove, and the counter never decremented. The design
   system's Section 7 documents a complete, "mandatory base for any new
   file-upload field" component (already live on `adoption-application.html`)
   that Rule 7 requires reusing verbatim rather than reinventing a
   simplified version. Replaced the photo field with that exact component
   (drop zone CSS, click/drag/keyboard wiring, `handleFiles`/`preUploadFile`/
   `renderField`), with the minimum-3 gate now driven by the real file
   array length on every render — so removing a photo correctly re-locks
   Publish instead of a counter that only ever went up.

3. **Wrong icon on the warning state.** The "short of minimum" photo count
   used an info-circle icon in amber. Section 18 (Toast Notifications) and
   Section 11 both document Warning Amber as pairing with a
   triangle-exclamation icon specifically — the info-circle glyph is
   reserved for Info Blue. Swapped to the triangle-exclamation icon (the
   success state's checkmark was already correct and unchanged).

4. **Missing mandatory brand color.** Section 1's "Mandatory — no
   exceptions" list requires Orange (#ED7624) to appear on every
   page/surface, with zero exceptions — the first draft defined `--orange`
   as a CSS variable but never actually used it anywhere. Added a minimal
   accent rule under the page title (Section 2's guidance: orange used
   sparingly, not as a dominant color); the reused upload component's own
   "Uploading…" status icon is also orange per Section 7, so the color
   now appears in two deliberate, minimal places rather than zero.

Also explicitly flagged, not silently left out: this file carries no
header/footer/hamburger menu. The Section 1 mandatory rule for one verbatim
header/footer/hamburger implementation governs the public adoption site's
pages; this form is a merchant-dashboard panel (BUILD APPROACH: mirrors the
admin dashboard pattern, not the public site), meant to be embedded inside
Task E1's merchant dashboard shell rather than shipped as its own standalone
page — noted inline in the file itself so this isn't mistaken for an
oversight at integration.

Re-checked after fixes: tag balance (div/svg/button all matched, script/style
content excluded from the count to avoid false positives), JS parses without
syntax errors, and the three `TASK-F4` hint blocks plus the photo-minimum
block still carry accurate anchor comments. Self-Check Confirmation above
still holds — only `pet-posting-form.html` was touched, and the two
`<select>` elements that previously prompted the dropdown note in that
section are gone, so the file now has no dropdowns of any kind, matching
that confirmation more precisely than the first draft did.

No further bugs found outside this task's scope beyond what was already
flagged (the placeholder photo-upload endpoint, noted in the file's own
code comments as a cross-task/backend dependency).
