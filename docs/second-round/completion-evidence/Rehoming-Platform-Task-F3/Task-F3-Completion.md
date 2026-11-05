# Task F3 — Completion File

**Hand-in format note (updated twice now, no work redone either time):** this file was first restructured to match `WORKER-DELIVERY-GUIDE.md`'s four-column Files Changed table. It has now been updated a second time per a clarification from the CEO: the completion file is now bundled **inside** the single project zip alongside every new file (previously it was sent as a separate file next to a new-files-only zip), the zip is named `Rehoming-Platform-Task-F3.zip` — `[Project-Name]-Task-[Task-Number].zip`, using the task-assignment document's own project name ("Rehoming-Platform," from `Rehoming-Platform-Task-Assignments.md`) rather than a worker-invented name — and any new file's live-path folder not already documented in either project-summary doc is now called out explicitly (see "New folders required" under New files below). The substance below (what was built, the design-system/Rule 7/folder-path corrections from the prior verification pass) is still unchanged from what was already delivered.

## Task Number
TASK F3 — Social sharing + digest email

## What Was Required

A "Share" button on each listing generating a direct URL carrying Open Graph meta tags (pet name, photo, one-line description) so it previews properly on Facebook/etc. — standard HTML meta tags, no paid service — plus a weekly digest email (sent via the platform's existing email-sending implementation) compiling new Pet-Alert matches (Task F1), Needs-Extra-Love listings (Task A2) within the visitor's saved-search scope, and one recent verified-rehome story (Task D1) if one exists, with a mandatory opt-out link in every email.

Per the revised task-assignment doc's VISIBILITY & ACCESS MODEL, confirmed F3 does **not** call `showAuthPrompt()` (Task A4's shared component) — sharing a listing and the digest email/unsubscribe flow are both outside the gated-action list.

`pet-detail.html` already had a working `sharePet()` function (Web Share API + clipboard fallback) — not rebuilt, per Rule 7. The actual gap was that the page's `<head>` carries no Open Graph tags and structurally can't, per-pet, since pet data loads client-side after the page renders — confirmed via web search that social-link crawlers don't execute JavaScript, so a server-side PHP endpoint (`pet-share.php`) was built to solve this, the standard fix for this exact situation.

**Scope-boundary item, flagged rather than silently assumed:** F3's spec requires a working opt-out link in every digest email, but F3's named File Zone only covers "listing page (share links), new scheduled digest-compile script" — no unsubscribe page. A minimal `unsubscribe.php` was built as part of "the digest email feature" since the digest can't satisfy its own spec without it; flagged here for the CEO/VDM in case it should instead belong to Task F1's file zone (it ultimately flips a per-visitor flag F1 will own).

**Shared Contracts this task builds against (stubs, by design):**
- Visitor saved searches + digest opt-in/opt-out state — Task F1, not yet built. Stubbed as `visitor-saved-searches-stub.json`.
- Needs-Extra-Love flag — Task A2, and this part is **real**: reads the real `needs_extra_love` field directly off each real `pet.json`, via the same folder-walk `needs-extra-love-check.php` established.
- One recent verified-rehome story — Task D1, not yet built. Stubbed as `verified-rehome-stories-stub.json`.
- Platform's existing email-sending implementation — `contact-config.php`/`chat-mail.php` were not among this task's named "Files to Request From CEO," so the real mailer could not be wired with confirmed code (see `## SECOND VERIFICATION ROUND` below for how this was corrected after an earlier draft guessed a function name that turned out not to be real).

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `pet-detail.html` | `pets/detail/index.html` | Changed | Yes — Task A1 (posting-panel clone context, display side) and Task G1 (donation/checkout links) also touch this file. This task's two edits are each wrapped in their own `TASK-F3` anchor block and touch nothing else. |
| `pet-share.php` | `pets/detail/index.html`'s sibling: `pets/detail/pet-share.php` | New | No — not shared with another task at time of submission. |
| `digest-compile.php` | `adoptions/scripts/digest-compile.php` | New | No. This *is* F3's named "new scheduled digest-compile script." Live path not in the guide's mapping table — taken from Section 1's real folder tree, matching the exact folder `needs-extra-love-check.php` (Task A2) already established as this platform's first scheduled-job location. |
| `unsubscribe.php` | `adoptions/unsubscribe.php` | New | No. See "Scope-boundary item" above — flagged as a possible Task F1 ownership item at integration. |
| `visitor-saved-searches-stub.json` | `adoptions/scripts/visitor-saved-searches-stub.json` | New | No — stub data only, standing in for Task F1's real visitor-account file. Not real visitor data (sample addresses only, e.g. `adopter.a@example.com`). |
| `verified-rehome-stories-stub.json` | `adoptions/scripts/verified-rehome-stories-stub.json` | New | No — stub data only, standing in for Task D1's real verified-rehome-story file. |

## Code Changes

### `pet-detail.html`  →  live path `pets/detail/index.html`

<!-- TASK-F3 START -->
**Old code:**
```html
  <meta name="description" content="Pet adoption details — Pet Center">
  <title id="pageTitle">Pet Details — Pet Center</title>
```
**New code:**
```html
  <meta name="description" content="Pet adoption details — Pet Center">
  <title id="pageTitle">Pet Details — Pet Center</title>
  <!-- TASK-F3 START — generic, non-per-pet Open Graph fallback.
       This page's own pet data is fetched client-side after load (see
       the "Load Pet Details" script below), so these tags can't be
       made per-pet here without a server-side rendering step — that's
       what pet-share.php (new, Task F3) exists for, and is what the
       "Share" button below now actually shares. These tags are left
       here anyway as a generic, harmless fallback for any crawler that
       does render JavaScript (e.g. modern search-engine indexers),
       and as a sane default if someone pastes this page's own raw URL
       somewhere instead of using the Share button. -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Pet Center">
  <meta property="og:title" content="Pet Details — Pet Center">
  <meta property="og:description" content="Browse adoptable pets at Pet Center.">
  <meta name="twitter:card" content="summary_large_image">
  <!-- TASK-F3 END -->
```
<!-- TASK-F3 END -->

(Old snippet above confirmed to occur exactly once in the original uploaded file.)

<!-- TASK-F3 START -->
**Old code:**
```javascript
// Share Button - Web Share API
function sharePet() {
  var petName = document.getElementById('petName')?.textContent || 'Pet';
  var shareData = {
    title: petName + ' - Pet Center',
    text: 'Check out this adorable pet available for adoption at Pet Center!',
    url: window.location.href
  };
  
  if (navigator.share) {
    navigator.share(shareData).catch(function(err) {
      console.log('Share cancelled:', err);
    });
  } else {
    // Fallback: Copy to clipboard
    navigator.clipboard.writeText(window.location.href).then(function() {
      showShareToast('Link copied to clipboard!');
    }).catch(function() {
      alert('Could not copy link. Please try again.');
    });
  }
}
```
**New code:**
```javascript
// Share Button - Web Share API
function sharePet() {
  var petName = document.getElementById('petName')?.textContent || 'Pet';

  // TASK-F3 START — build the share URL from pet-share.php instead of
  // this page's own URL. This page's own <head> can't carry per-pet
  // Open Graph tags (its pet data is fetched client-side, after any
  // crawler would have already read the raw HTML) — see pet-share.php
  // for the full explanation. pet-share.php is a plain-filename PHP
  // backend file deployed as a sibling of this page's own index.html
  // inside pets/detail/ — the same placement convention already used
  // by apply/adoption-application.php next to apply/index.html — not
  // a separate folder+index wrapper, since Rule 5's folder/index
  // convention only governs HTML pages, not PHP backend files. pet_id/
  // category are re-read directly from the URL here rather than
  // reaching into the "Load Pet Details" IIFE above, since its petId/
  // category variables are private to that closure and this task's
  // Scope Lock doesn't cover changing that file's variable scoping
  // just to expose them elsewhere.
  var shareParams = new URLSearchParams(window.location.search);
  var sharePetId = shareParams.get('pet_id') || '';
  var shareCategory = shareParams.get('category') || '';
  var shareUrl = '/pets/detail/pet-share.php?pet_id=' + encodeURIComponent(sharePetId) + '&category=' + encodeURIComponent(shareCategory);
  // TASK-F3 END

  var shareData = {
    title: petName + ' - Pet Center',
    text: 'Check out this adorable pet available for adoption at Pet Center!',
    url: shareUrl // TASK-F3: was window.location.href
  };
  
  if (navigator.share) {
    navigator.share(shareData).catch(function(err) {
      console.log('Share cancelled:', err);
    });
  } else {
    // Fallback: Copy to clipboard
    navigator.clipboard.writeText(shareUrl).then(function() { // TASK-F3: was window.location.href
      showShareToast('Link copied to clipboard!');
    }).catch(function() {
      alert('Could not copy link. Please try again.');
    });
  }
}
```
<!-- TASK-F3 END -->

(Old snippet above confirmed to occur exactly once in the original uploaded file.)

## New files
- `pets/detail/pet-share.php` — New file — in the zip. Server-side Open Graph preview endpoint: reads the real pet.json for a given pet_id/category and outputs real per-pet og:/twitter: meta tags before forwarding a human visitor to the real listing page, so a shared link actually previews on Facebook/etc. **Folder:** `pets/detail/` already exists and is already documented (it's where `pet-detail.html` itself deploys as `index.html`) — this just adds one new sibling file to it. No new folder needed.
- `adoptions/scripts/digest-compile.php` — New file — in the zip. This task's named weekly scheduled digest-compile script; reuses Task A2's cron-job pattern exactly. Needs a Hostinger cron entry added (documented in the file's own header comment) — outside what this task can configure from a container. **Folder — flagged, see "New folders required" below.**
- `adoptions/unsubscribe.php` — New file — in the zip. Minimal one-click opt-out handler the digest email's mandatory unsubscribe link points to. **Folder:** `adoptions/` already exists, but see "New folders required" below — no documented precedent of a loose file sitting directly inside it.
- `adoptions/scripts/visitor-saved-searches-stub.json` — New file — in the zip. Temporary stub data (sample addresses only) standing in for Task F1's real file until it exists; `digest-compile.php` and `unsubscribe.php` both read/write it today. **Folder — flagged, see "New folders required" below.**
- `adoptions/scripts/verified-rehome-stories-stub.json` — New file — in the zip. Temporary stub data standing in for Task D1's real file until it exists. **Folder — flagged, see "New folders required" below.**

### New folders required — flagged heavily, not already documented
- **`adoptions/scripts/`** — this folder does **not** appear anywhere in `adoption-website-project-summary.md`'s Section 1 folder tree (which documents `adoptions/pets/`, `adoptions/status/`, `adoptions/applications/`, but no `scripts/`). It was first established by **Task A2**'s `needs-extra-love-check.php` (confirmed from that file's own header comment, which gives its live cron path as `.../public_html/adoptions/scripts/needs-extra-love-check.php`) as this platform's first scheduled-job location — not invented fresh by this task, but genuinely new relative to the original project docs. **If Task A2's integration has already created `adoptions/scripts/` on the live site, this task's three files (`digest-compile.php` and the two stub `.json` files) just go into that existing folder. If it has not been created yet, the integrator needs to create `adoptions/scripts/` (directly inside `adoptions/`, sibling of `adoptions/pets/`) before placing any of this task's or Task A2's scripts-folder files.**
- **`adoptions/unsubscribe.php`'s location** — `adoptions/` itself is a real, already-documented folder, so this is not a new folder in the strict sense, but no prior documented structure shows a loose utility `.php` file sitting directly inside `adoptions/` (only its three subfolders `pets/`, `status/`, `applications/` are documented there). Flagged so the integrator/CEO can confirm this placement is acceptable, or redirect it elsewhere, rather than assuming it silently matches an established pattern.

**Setting needed from the CEO before this runs for real:** `UNSUBSCRIBE_TOKEN_SALT` is set to the placeholder `CHANGE_ME_BEFORE_PRODUCTION` in both `digest-compile.php` and `unsubscribe.php` (must match exactly between the two) — a real secret must be set there before this is ever used against real visitor emails. Not a file-provided secret, so nothing to reference from `config.php`/`.env` here, just a constant the CEO/DevOps needs to change in both files before go-live.

**Live deployment naming — confirmed compliant:** this task created no new HTML pages, so there is nothing to rename to `index.html`. The one existing page it touches, `pets/detail/index.html` (source `pet-detail.html`), was already deployed under the strict folder/`index.html` convention before this task started, and nothing in this task's edits changes that. `pet-share.php` and `unsubscribe.php` are PHP backend files, not HTML pages, so the `index.html` convention does not apply to them (Rule 5 is explicitly scoped to HTML pages only) — they deploy as plain filenames, per the already-established precedent of `apply/adoption-application.php` sitting as a plain filename next to `apply/index.html`.

## Bugs Found Outside Task Scope
None found.

## Self-Check Confirmation
Only the six files listed in Files Changed were touched or created — `pet-detail.html`'s existing markup/CSS/JS outside the two anchored blocks was read for context and never edited, and no other task's file was modified. Both code blocks above carry `TASK-F3` anchor comments, and both "Old code" snippets were copied character-for-character from the original uploaded `pet-detail.html` and verified (via exact string match, programmatically) to occur exactly once in that file. Every file in `Task-F3-NewFiles.zip` is listed above under New files with a matching live path, and every "New file" entry is present in the zip (checked with `unzip -l`). The zip contains no `config.php`, no `pet.json`, no `.env`, and nothing under `applications/`, `uploads/`, `chat-media/`, or `logs/` — confirmed by listing the zip contents and grepping for those names; it holds exactly the five files named above. No passwords, tokens, or real keys are present — the one placeholder constant (`UNSUBSCRIBE_TOKEN_SALT`) is deliberately an obvious non-secret string, flagged above as a setting the CEO must change before production use, not a real credential.

JS syntax (all inline `<script>` blocks in the edited `pet-detail.html`, extracted and parsed via Node's `new Function(...)`) and a brace/parenthesis/`<?php`-tag balance check on all three PHP files were re-run against the current final files and pass clean — same disclosed limitation as before: no PHP interpreter is available in this sandbox to run the `.php` files directly. `digest-compile.php`'s matching/opt-out/unsubscribe-token logic, including boundary cases (an empty visitor list, a visitor matching zero pets, and `pet-share.php`'s not-found fallback fields), was verified by porting that logic line-for-line into a JS test harness and running it against fixture data — all assertions passed. That harness is a local verification tool only, not a live site file, so it is not included in the zip or listed above.

## SECOND VERIFICATION ROUND

Re-read `adoption-website-project-summary.md`'s full Warning & Instructions section and `PETCENTER_DESIGN_SYSTEM_v1_0.md`'s Section 1 (Rules & Warnings) and Section 2 (Color Palette) from scratch, then re-checked this task's own deliverable files against both, plus the real Section 1 folder tree and `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s folder tree. Four real issues were found and fixed before this file was first sent to the CEO (i.e. already incorporated into the Code Changes and New files above — nothing further to apply on top of them):

1. **Rule 9 violation — design system not actually consulted for the digest email's styling.** An initial draft used `Georgia` for email headings; Section 3 locks headings to Cormorant Garamond (falling back to Bitter, never Sora). Fixed to `'Cormorant Garamond','Bitter',Georgia,serif` in both `digest-compile.php`'s email template and `unsubscribe.php`'s confirmation page (Georgia kept only as the final real-system-font safety net, since email clients routinely block Google Fonts `@import`). Colors (#2c2416, #7a6d5f, #10214b) happened to already match the real documented values.
2. **Section 1 mandatory-rule violation — Orange (#ED7624) was completely missing**, a zero-flexibility rule ("must appear on every page/surface, always"). Added minimally per the design system's own "some buttons"/accent guidance: a 4px Orange top border on the digest email and on `unsubscribe.php`'s content block, and Orange on the one clickable link in `pet-share.php`'s fallback body.
3. **Rule 7 violation, caught before hand-in — an invented mailer function.** An initial draft called a fictitious `send_platform_email_via_contact_config()` as if it were a confirmed real entry point. Rewritten to honestly `require_once` the real `contact-config.php` if present (now at the corrected path, see item 4), falling back to PHP's native `mail()` — a real, working function, not a stub — with explicit `// TODO` markers on exactly what needs swapping in once the real file is supplied. `contact-config.php` and `chat-mail.php` are flagged as additional files this task genuinely needs beyond its original "Files to Request" line, directly because Rule 7 requires reusing the *exact* code of an existing feature, not a plausible guess at its interface.
4. **Wrong deployed folder locations, re-derived from the real folder trees instead of invented.** `pet-share.php` was originally placed at an invented `pets/share/index.php`; corrected to `pets/detail/pet-share.php` after confirming PHP backend files deploy as plain filenames beside their page's own `index.html` (e.g. `apply/adoption-application.php` next to `apply/index.html`) — Rule 5's folder/index convention is scoped to HTML pages only. `unsubscribe.php` corrected to `adoptions/unsubscribe.php` (sibling of `adoptions/scripts/`, for a clean public email-link URL). `contact-config.php`'s assumed path in `digest-compile.php` corrected to `adoptions/scripts/../../contact-us/contact-config.php`, matching `contact-us/` being a sibling of `adoptions/` under the site root per `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s real tree, not a guessed nested path.

**Files Changed (this round) — superseded by the single combined table above**, since none of these were "changed existing files" in the delivery-guide sense: all four corrections landed inside files that were already brand-new to this task (`pet-share.php`, `digest-compile.php`, `unsubscribe.php`) or inside `pet-detail.html`'s own `TASK-F3` anchor block (no new anchor needed, same block). Per the delivery guide's rule 3 ("created a new file, then changed it again later — replace it inside the zip and say so"): `Task-F3-NewFiles.zip` contains the corrected, final versions of all three PHP files and both stub JSON files — not an earlier draft.

**Additional files identified as needed (still open, carried into this hand-in):** `contact-us/contact-config.php` and `admin/chat-mail.php` (per the live-path mapping, admin PHP files deploy under `admin/`) — needed to replace `digest-compile.php`'s native-`mail()` stand-in with the platform's real, consistent send mechanism, per Rule 7. Neither blocks this task's other logic (matching, opt-out, and OG-tag fallback were all verified independently), but both are needed before the email-sending half of F3 is genuinely production-correct rather than an honest, working stand-in.

**Bugs Found Outside Task Scope (this round):** None found — all four issues above were inside this task's own deliverables, not pre-existing platform bugs.

**Self-Check Confirmation (this round):** Only this task's own files were touched. Brace/paren/`<?php`-tag balance re-checked on all three PHP files after the fixes (still balanced). Full JS syntax re-check on `pet-detail.html` passed. A `diff` against the original uploaded file re-confirmed no changes outside the two anchored blocks shown above. The JS-ported test harness was re-run with added boundary cases (empty visitor list, a visitor matching zero pets, the not-found fallback fields) — all pass.
