# Task F6 — Cookie Consent Banner — Completion File

**Hand-in format note:** this file has been rebuilt to match `WORKER-DELIVERY-GUIDE.md` (four-column
Files Changed table with live paths, per-file live-path headers in Code Changes, completion file now
bundled inside the zip per the CEO's follow-up notice rather than sent separately). No code was redone or
changed — only the hand-in packaging. The actual work (what was built, tested, and the bug found and fixed
during re-verification) is unchanged from the prior submission.

## Task Number
F6

## What Was Required
A site-wide, dismissible cookie consent banner that appears on a visitor's first visit, reading:
"We use essential cookies to run this site, and optional cookies for analytics. Accept / Reject non-essential."
The visitor's choice (Accept or Reject) must be stored so the banner never reappears on later visits, and
essential session/login cookies must keep working regardless of which choice the visitor makes.

**Scoping decision (flagged, not silently assumed):** the task's own file zone/request line ("the site-wide
include/partial file used across pages") does not correspond to an actual file in this codebase —
`adoption-website-project-summary.md` documents the public site as static, hand-authored HTML with no
build step and no include/partial system; header/footer/hamburger are duplicated per page rather than
pulled from one shared file. The only real precedent for a "site-wide" file is `blogs-data.js`, a shared
script loaded via a plain `<script src="...">` tag. I raised this with the VDM before building; the
response was "no [separate include file], do what's recommended best." Implemented approach: one new
shared script, `cookie-consent.js`, added via a single script tag to every public-facing page.
`admin-dashboard.html` was intentionally excluded — it's the internal, staff-only super-admin tool, not
part of the public "site" a visitor-facing cookie banner is scoped to.

## ⚠️ Implementation Warnings — Read Before Deploying Or Extending This

1. **`cookie-consent.js`'s live path is flagged, not a clean match to the guide's mapping table — see the
   Files Changed table and the note under "New Files" below before deploying it.** Deploying it at the
   wrong path breaks the banner on all 17 pages at once.
2. **There is no shared include/partial file for this site.** Any new page added to the site in the future
   will NOT automatically get the cookie banner — whoever builds that page must manually add the same line
   used on the other 17 pages (see Code Changes) immediately before that page's closing `</body>` tag.
3. **Before scripting a bulk insertion before `</body>` on any file, check for more than one literal
   `</body>` occurrence first.** `pet-detail.html` has two harmless-looking extra occurrences of that exact
   substring sitting inside a CSS comment and an HTML comment (unrelated developer notes about
   `chat-widget.js`). A naive first-match find/replace on bare `</body>` puts the script tag inside the
   comment instead of before the real closing tag, so it silently never runs — this happened in my first
   pass and was caught and fixed during re-verification (see below). Every old-code snippet in this file
   anchors on the literal two-line sequence `</body>\n</html>`, which I've verified occurs exactly once in
   every one of the 17 original files, pet-detail.html included — anchor on that same sequence, not on
   bare `</body>` alone.
4. **No z-index value for a persistent banner is defined anywhere in `PETCENTER_DESIGN_SYSTEM_v1_0.md`**
   (it only documents Modal: 9999, Dropdown: 999, Bottom Sheet: 1400/1500). `cookie-consent.js` currently
   uses `z-index: 1000` as a placeholder. If Design assigns an official banner z-index later, or any future
   fixed-position element is built with a z-index between 1000 and 1400, check for overlap with this banner.
5. **This script does not gate any analytics/tracking script by itself** — there are none in the codebase
   yet. Any future analytics integration (GA, GTM, etc.) must check the visitor's choice before firing, via
   either `localStorage.getItem('pc_cookie_consent')` (or the `pc_cookie_consent` cookie as a fallback) or by
   listening for the `petcenter:cookieConsent` custom event this script dispatches
   (`event.detail.status` is `'accepted'` or `'rejected'`). Without that check, an analytics script would
   load regardless of the visitor's choice.
6. **Pre-existing, unrelated bug on `testimonials.html` (not fixed here, needs its own owner):** its
   `chat-widget.js` reference is missing the `https://` scheme. See "Bugs Found Outside Task Scope" below.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `cookie-consent.js` | **FLAGGED — see note below the table.** Deployed/referenced as `/cookie-consent.js` (site root). | New | No |
| `index.html` | `index.html` | Changed | No |
| `cats.html` | `pets/cats/index.html` | Changed | No |
| `dogs.html` | `pets/dogs/index.html` | Changed | No |
| `birds.html` | `pets/birds/index.html` | Changed | No |
| `reptiles.html` | `pets/reptiles/index.html` | Changed | No |
| `aquariums.html` | `pets/aquariums/index.html` | Changed | No |
| `livestocks.html` | `pets/livestocks/index.html` | Changed | No |
| `small-mammals.html` | `pets/small-mammals/index.html` | Changed | No |
| `pets.html` | `pets/index.html` | Changed | No |
| `pet-detail.html` | `pets/detail/index.html` | Changed | No |
| `services.html` | `services/index.html` | Changed | No |
| `testimonials.html` | `testimonials/index.html` (guide's mapping table lists this source as `Testimonials.html`, capital T — same file, case difference only) | Changed | No |
| `status.html` | `adoptions/status/index.html` | Changed | No |
| `adoption-application.html` | `apply/index.html` | Changed | No |
| `installment-plan.html` | **FLAGGED — see note below the table.** Proposed `installment-plan/index.html`. | Changed | No |
| `blog-home.html` | `blog/index.html` | Changed | No |
| `blog-single.html` | `blog/post/index.html` | Changed | No |

`admin-dashboard.html` was **not** touched (see scoping decision above).

**Flag 1 — `cookie-consent.js` live path.** The guide's mapping table's catch-all for new shared scripts is
"Shared CSS/JS under `assets/`" → `assets/js/...`, which would put this file at `assets/js/cookie-consent.js`.
I have **not** moved it there, and I'm flagging this rather than guessing: every one of the 17 pages
already references it as `<script src="/cookie-consent.js">` (root-absolute, confirmed necessary and
tested, since the 17 pages deploy at different folder depths — root, `/pets/...`, `/blog/...`,
`/blog/post/...`, etc. — and a root-absolute path is the only reference that resolves correctly from all of
them). Moving the file into `assets/js/` now would require re-editing the `<script src="...">` line in all
17 files to `/assets/js/cookie-consent.js`, which is a code change, not a hand-in fix, and the CEO's
instruction for this round was "do not redo or change your work." **Integrator: please either (a) deploy
`cookie-consent.js` at the literal site root (`adopt.petcenterco.com/cookie-consent.js`), matching what's
actually in the 17 files right now, or (b) tell me to do a follow-up round moving it to `assets/js/` and
updating all 17 references to match.** Don't place it at `assets/js/cookie-consent.js` while the 17 pages
still say `/cookie-consent.js` — that combination breaks the banner everywhere.

**Flag 2 — `installment-plan.html` live path.** This source file is not in the guide's mapping table, and
I could not find it in `adoption-website-project-summary.md`'s folder-structure section either (checked by
searching the document for "installment-plan" and "installment" directly — no path is documented anywhere
I have access to). The path above, `installment-plan/index.html`, is my proposal only, based on the same
folder-containing-index.html pattern every other page in the mapping table follows — it is **not**
confirmed. **Integrator: please confirm this is the correct live path (or correct it) before merging this
file's change.**

## Verification Pass — Bug Found In My Own Work And Fixed
A second, deliberate re-read of `adoption-website-project-summary.md` plus a byte-level audit of every
touched file (counting literal `</body>` occurrences per file before trusting the earlier insertion)
turned up a real bug in the first pass, now corrected:

- **`pet-detail.html` has three literal occurrences of the substring `</body>`** — the real closing tag at
  the end of the file, plus two unrelated mentions of it inside a CSS comment and an HTML comment
  (developer notes about where `chat-widget.js`'s launcher gets re-parented). My original insertion command
  replaced only the *first* occurrence it found in the file, which was the CSS-comment one, not the real
  closing tag. Net effect: the `<script src="/cookie-consent.js">` tag landed as inert text inside a
  `/* ... */` CSS comment inside a `<style>` block (never parsed as HTML, never executed) while also
  garbling that comment's wording — so the banner silently never loaded on the pet-detail page at all,
  the single highest-traffic page type on the site.
- **Fix applied:** restored the CSS comment to its original wording, and re-inserted the same
  anchor-commented script tag at the actual closing `</body>` tag at the end of the file. Re-verified
  twice: a file-level diff against the untouched original showed exactly the intended 3-line addition and
  nothing else, and a fresh headless-browser load of the corrected file confirmed `#pc-cookie-consent` is
  present in the DOM.
- All other 16 files were re-audited the same way and each has exactly one `</body>` occurrence, so this
  risk was unique to `pet-detail.html`. For this hand-in, every file's old-code snippet below anchors on
  the literal `</body>\n</html>` two-line sequence specifically because it's verified-unique per file
  (see Implementation Warning #3), which is the safer anchor and avoids repeating that mistake.

## Code Changes

Every block below is wrapped in `<!-- TASK-F6 START -->` / `<!-- TASK-F6 END -->` anchor comments. The old
snippet in every file is the literal, verified-unique two-line sequence `</body>\n</html>` at the true end
of that file (confirmed to occur exactly once per file, pet-detail.html included — see Implementation
Warning #3).

### `index.html` → live path `index.html`

<!-- TASK-F6 START -->
**Old code:**
```html
</body>
</html>
```
**New code:**
```html
<!-- TASK-F6 START -->
<script src="/cookie-consent.js"></script>
<!-- TASK-F6 END -->
</body>
</html>
```
<!-- TASK-F6 END -->

### `cats.html` → live path `pets/cats/index.html`

<!-- TASK-F6 START -->
**Old code:**
```html
</body>
</html>
```
**New code:**
```html
<!-- TASK-F6 START -->
<script src="/cookie-consent.js"></script>
<!-- TASK-F6 END -->
</body>
</html>
```
<!-- TASK-F6 END -->

### `dogs.html` → live path `pets/dogs/index.html`

<!-- TASK-F6 START -->
**Old code:**
```html
</body>
</html>
```
**New code:**
```html
<!-- TASK-F6 START -->
<script src="/cookie-consent.js"></script>
<!-- TASK-F6 END -->
</body>
</html>
```
<!-- TASK-F6 END -->

### `birds.html` → live path `pets/birds/index.html`

<!-- TASK-F6 START -->
**Old code:**
```html
</body>
</html>
```
**New code:**
```html
<!-- TASK-F6 START -->
<script src="/cookie-consent.js"></script>
<!-- TASK-F6 END -->
</body>
</html>
```
<!-- TASK-F6 END -->

### `reptiles.html` → live path `pets/reptiles/index.html`

<!-- TASK-F6 START -->
**Old code:**
```html
</body>
</html>
```
**New code:**
```html
<!-- TASK-F6 START -->
<script src="/cookie-consent.js"></script>
<!-- TASK-F6 END -->
</body>
</html>
```
<!-- TASK-F6 END -->

### `aquariums.html` → live path `pets/aquariums/index.html`

<!-- TASK-F6 START -->
**Old code:**
```html
</body>
</html>
```
**New code:**
```html
<!-- TASK-F6 START -->
<script src="/cookie-consent.js"></script>
<!-- TASK-F6 END -->
</body>
</html>
```
<!-- TASK-F6 END -->

### `livestocks.html` → live path `pets/livestocks/index.html`

<!-- TASK-F6 START -->
**Old code:**
```html
</body>
</html>
```
**New code:**
```html
<!-- TASK-F6 START -->
<script src="/cookie-consent.js"></script>
<!-- TASK-F6 END -->
</body>
</html>
```
<!-- TASK-F6 END -->

### `small-mammals.html` → live path `pets/small-mammals/index.html`

<!-- TASK-F6 START -->
**Old code:**
```html
</body>
</html>
```
**New code:**
```html
<!-- TASK-F6 START -->
<script src="/cookie-consent.js"></script>
<!-- TASK-F6 END -->
</body>
</html>
```
<!-- TASK-F6 END -->

### `pets.html` → live path `pets/index.html`

<!-- TASK-F6 START -->
**Old code:**
```html
</body>
</html>
```
**New code:**
```html
<!-- TASK-F6 START -->
<script src="/cookie-consent.js"></script>
<!-- TASK-F6 END -->
</body>
</html>
```
<!-- TASK-F6 END -->

### `pet-detail.html` → live path `pets/detail/index.html`

This file needs special care — see Implementation Warning #3 and the Verification Pass section above. The
old-code anchor below (`</body>\n</html>`) is the literal end-of-file sequence, verified to occur exactly
once in the original file even though the bare substring `</body>` occurs three times in it.

<!-- TASK-F6 START -->
**Old code:**
```html
</body>
</html>
```
**New code:**
```html
<!-- TASK-F6 START -->
<script src="/cookie-consent.js"></script>
<!-- TASK-F6 END -->
</body>
</html>
```
<!-- TASK-F6 END -->

### `services.html` → live path `services/index.html`

<!-- TASK-F6 START -->
**Old code:**
```html
</body>
</html>
```
**New code:**
```html
<!-- TASK-F6 START -->
<script src="/cookie-consent.js"></script>
<!-- TASK-F6 END -->
</body>
</html>
```
<!-- TASK-F6 END -->

### `testimonials.html` → live path `testimonials/index.html`

<!-- TASK-F6 START -->
**Old code:**
```html
</body>
</html>
```
**New code:**
```html
<!-- TASK-F6 START -->
<script src="/cookie-consent.js"></script>
<!-- TASK-F6 END -->
</body>
</html>
```
<!-- TASK-F6 END -->

### `status.html` → live path `adoptions/status/index.html`

<!-- TASK-F6 START -->
**Old code:**
```html
</body>
</html>
```
**New code:**
```html
<!-- TASK-F6 START -->
<script src="/cookie-consent.js"></script>
<!-- TASK-F6 END -->
</body>
</html>
```
<!-- TASK-F6 END -->

### `adoption-application.html` → live path `apply/index.html`

<!-- TASK-F6 START -->
**Old code:**
```html
</body>
</html>
```
**New code:**
```html
<!-- TASK-F6 START -->
<script src="/cookie-consent.js"></script>
<!-- TASK-F6 END -->
</body>
</html>
```
<!-- TASK-F6 END -->

### `installment-plan.html` → live path `installment-plan/index.html` (PROPOSED — see Flag 2 above, unconfirmed)

<!-- TASK-F6 START -->
**Old code:**
```html
</body>
</html>
```
**New code:**
```html
<!-- TASK-F6 START -->
<script src="/cookie-consent.js"></script>
<!-- TASK-F6 END -->
</body>
</html>
```
<!-- TASK-F6 END -->

### `blog-home.html` → live path `blog/index.html`

<!-- TASK-F6 START -->
**Old code:**
```html
</body>
</html>
```
**New code:**
```html
<!-- TASK-F6 START -->
<script src="/cookie-consent.js"></script>
<!-- TASK-F6 END -->
</body>
</html>
```
<!-- TASK-F6 END -->

### `blog-single.html` → live path `blog/post/index.html`

<!-- TASK-F6 START -->
**Old code:**
```html
</body>
</html>
```
**New code:**
```html
<!-- TASK-F6 START -->
<script src="/cookie-consent.js"></script>
<!-- TASK-F6 END -->
</body>
</html>
```
<!-- TASK-F6 END -->

## New Files

- **`cookie-consent.js`** — New file — in the zip. Self-contained site-wide cookie consent banner script
  (storage, markup injection, scoped styling, Accept/Reject handlers, a `petcenter:cookieConsent` custom
  event for future analytics integrations to hook into). **Live path is flagged — see Flag 1 under Files
  Changed above before deploying it; do not place it at `assets/js/cookie-consent.js` without also updating
  all 17 page references, since they currently point at `/cookie-consent.js`.**

## Bugs Found Outside Task Scope
- **`testimonials.html` line 494** (pre-existing, not introduced by this task): the shared chat widget is
  loaded as `<script src="adopt.petcenterco.com/admin/chat-widget.js"></script>` — missing the `https://`
  scheme that every other page uses (`<script src="/admin/chat-widget.js">`). Without a protocol, the
  browser treats this as a relative path from the testimonials page's own URL rather than a site-root
  path, so the chat widget almost certainly fails to load on this page specifically. Logged here, not
  fixed — `/admin/chat-widget.js` is outside F6's file zone.

## Self-Check Confirmation
- Only the 17 public pages listed above and the new `cookie-consent.js` were touched. `admin-dashboard.html`
  and every project reference document (`adoption-website-project-summary.md`,
  `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, `PETCENTER_DESIGN_SYSTEM_v1_0.md`,
  `Rehoming-Platform-Task-Assignments.md`, `WORKER-DELIVERY-GUIDE.md`) were read only, never edited.
- Every code block carries the `<!-- TASK-F6 START -->` / `<!-- TASK-F6 END -->` anchor comment pair; the
  new `cookie-consent.js` file carries the same anchor pair around its entire contents and is fully
  commented per Rule 6 (every function documented).
- Every "Old code" snippet above (`</body>\n</html>`) was copied character for character from the original
  file I was given, and verified by script to occur **exactly once** in each of the 17 original files
  before being used as the match target — including `pet-detail.html`, where the plain substring `</body>`
  alone occurs three times but this exact two-line sequence occurs only once. This was re-confirmed for
  this hand-in, not assumed from the earlier round.
- No dropdown menus in this task.
- No SHARED CONTRACTS from the current task doc apply to F6 (F6 is standalone; it does not call
  `showAuthPrompt()` or reference the listing/merchant/status contracts), so no reconciliation pass was
  needed.
- Verified with the Playwright headless browser (not just visual inspection): banner renders correctly on
  first load (desktop and mobile viewports, screenshotted), clicking Accept sets
  `localStorage.pc_cookie_consent = "accepted"`, reloading confirms the banner does not reappear, and
  `pet-detail.html` specifically was re-checked after the fix (DOM query confirms `#pc-cookie-consent` is
  present).
- Confirmed the script path choice: `/cookie-consent.js` (root-absolute) is deliberate, not arbitrary — per
  `adoption-website-project-summary.md`'s folder structure, these 17 pages deploy at different nesting
  depths, so a root-absolute path resolves correctly from every depth; flagged for the integrator under
  Flag 1 since it doesn't cleanly match the guide's generic `assets/js/` convention for new shared scripts.
- Re-confirmed no existing element/class on any of the 17 pages collides with the new `#pc-cookie-consent`
  container ID or `.pc-cc-*` class names.
- Zip contents checked against this table: `cookie-consent.js` and this completion file are the only two
  items in `rehoming-platform-task-f6.zip`, and both are listed above. No changed existing file is in the
  zip — per the guide's "one rule," every changed file is documented here as old code + new code only.
  No secrets, no `config.php`, no `pet.json`, no `applications/`, no `.env` anywhere in the zip.
