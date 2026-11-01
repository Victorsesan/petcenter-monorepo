# Task 3 — Completion File

## Task Number
Task 3 — Harden the Live Adoption Status Exposure

## What Was Required
`adopt.petcenterco.com`'s existing status checker fetched `application.json`
directly, client-side, with no authentication and no rate limiting — that
JSON contains the applicant's full name, phone number, and home address,
and the `AD-#####-####` code space is small enough to be practically
guessable at scale. Required: add rate limiting on the lookup, and require
a second identifier (matching Task 2's approach, for consistency) before
real data is returned — restructuring the lookup from "fetch a static JSON
file directly" to "call a small PHP endpoint that checks the second
identifier server-side before returning the data." Preserve the exact
current UX for legitimate users (same inputs, same look) — a security fix,
not a redesign.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `status.html` | `adoptions/status/index.html` | Changed | No |
| `adoption-status-check.php` | `adoptions/status/adoption-status-check.php` | New | No |
| `rate-limit-data/.htaccess` | `adoptions/status/rate-limit-data/.htaccess` | New | No |

## Code Changes

### `status.html`  →  live path `adoptions/status/index.html`

**1. `.dash-lookup` / `.dash-lookup-card` CSS — widened and made the row wrap for the new field**

<!-- TASK-3 START -->
**Old code:**
```css
.dash-lookup {
  max-width: 560px; margin: -32px auto 0; padding: 0 24px;
  position: relative; z-index: 5;
}
.dash-lookup-card {
  background: #fff; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg);
  padding: 22px; display: flex; gap: 10px;
}
.dash-lookup-card input {
  flex: 1; border: 1.5px solid #ece6dd; border-radius: 9px; padding: 12px 16px;
  font-family: 'Sora', sans-serif; font-size: 15px; color: var(--navy); outline: none;
  transition: border-color 0.2s;
}
.dash-lookup-card input:focus { border-color: var(--orange); }
.dash-lookup-card button {
  background: var(--orange); color: #fff; border: none; border-radius: 9px;
  padding: 12px 24px; font-family: 'Sora', sans-serif; font-size: 13.5px; font-weight: 600;
  cursor: pointer; white-space: nowrap; transition: background 0.2s;
}
.dash-lookup-card button:hover { background: #d4621a; }
@media (max-width: 480px) { .dash-lookup-card { flex-direction: column; } .dash-lookup-card button { width: 100%; } }
```
**New code:**
```css
.dash-lookup {
  max-width: 680px; margin: -32px auto 0; padding: 0 24px;
  position: relative; z-index: 5;
}
/* TASK-3 START: was a single-field row (code + button); now holds a
   second required input (last name). Widened .dash-lookup's max-width
   above (560px -> 680px) and switched this row to wrap so the same card
   styling still reads comfortably with three controls instead of two --
   no change to the colors/borders/radius/shadow the card already used. */
.dash-lookup-card {
  background: #fff; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg);
  padding: 22px; display: flex; flex-wrap: wrap; gap: 10px;
}
.dash-lookup-card input {
  flex: 1 1 180px; min-width: 0; border: 1.5px solid #ece6dd; border-radius: 9px; padding: 12px 16px;
  font-family: 'Sora', sans-serif; font-size: 15px; color: var(--navy); outline: none;
  transition: border-color 0.2s;
}
/* TASK-3 END */
.dash-lookup-card input:focus { border-color: var(--orange); }
.dash-lookup-card button {
  background: var(--orange); color: #fff; border: none; border-radius: 9px;
  padding: 12px 24px; font-family: 'Sora', sans-serif; font-size: 13.5px; font-weight: 600;
  cursor: pointer; white-space: nowrap; transition: background 0.2s;
}
.dash-lookup-card button:hover { background: #d4621a; }
/* TASK-3 START: stack fully at a wider breakpoint than before (was 480px)
   -- with three controls in the row instead of two, mid-width mobile
   screens now stack cleanly too rather than cramming three items in. */
@media (max-width: 640px) { .dash-lookup-card { flex-direction: column; } .dash-lookup-card button { width: 100%; } }
/* TASK-3 END */
```
<!-- TASK-3 END -->

**2. Lookup card markup — added the required Last Name field**

<!-- TASK-3 START -->
**Old code:**
```html
<!-- LOOKUP CARD -->
<div class="dash-lookup">
  <div class="dash-lookup-card">
    <input type="text" id="codeInput" placeholder="e.g. AD-47293-2026" maxlength="16" autocomplete="off" spellcheck="false">
    <button id="lookupBtn" onclick="lookupStatus()">Check Status</button>
  </div>
</div>
```
**New code:**
```html
<!-- LOOKUP CARD -->
<!-- TASK-3 START: added a required Last Name field alongside the existing
     Adoption Code field. This is the second identifier the hardened
     lookup endpoint now requires before returning any application data
     (see adoption-status-check.php) -- the code field itself, styling,
     and button are otherwise unchanged from the original single-field
     card, per the task's "preserve exact UX, this is a security fix not
     a redesign" instruction. -->
<div class="dash-lookup">
  <div class="dash-lookup-card">
    <input type="text" id="codeInput" placeholder="e.g. AD-47293-2026" maxlength="16" autocomplete="off" spellcheck="false">
    <input type="text" id="lastNameInput" placeholder="Last name on application" maxlength="80" autocomplete="off" spellcheck="false">
    <button id="lookupBtn" onclick="lookupStatus()">Check Status</button>
  </div>
</div>
<!-- TASK-3 END -->
```
<!-- TASK-3 END -->

**3. `lookupStatus()` and `fetchAndRender()` — validate the new field, call the new endpoint instead of the raw JSON file**

<!-- TASK-3 START -->
**Old code:**
```js
function lookupStatus() {
  var input = document.getElementById('codeInput');
  var code = input.value.trim().toUpperCase();
  if (!code) return;
  if (!code.match(/^AD-\d{5}-\d{4}$/)) {
    showDashState('<p style="color:var(--error);">Please enter a valid adoption code (e.g.\u00a0AD-47293-2026).</p>');
    return;
  }
  // Reflect code in URL without reloading, so the page is shareable/bookmarkable
  if (window.history && window.history.replaceState) {
    var url = new URL(window.location.href);
    url.searchParams.set('code', code);
    window.history.replaceState({}, '', url);
  }
  fetchAndRender(code);
}

function fetchAndRender(code) {
  showDashState('<p>Checking status&hellip;</p>');
  fetch('/adoptions/applications/' + encodeURIComponent(code) + '/application.json', { cache: 'no-store' })
    .then(function (r) {
      if (!r.ok) throw new Error('not-found');
      return r.json();
    })
    .then(function (d) { renderDashboard(d, code); })
    .catch(function () {
      showDashState('<p class="error" style="color:var(--error);">Code not found. Please check your adoption code and try again.</p>');
    });
}
```
**New code:**
```js
// TASK-3 START
// lookupStatus(): now also requires the Last Name field (the second
// identifier the hardened backend needs) before attempting a lookup --
// previously this only validated the code format. Both fields must be
// filled before fetchAndRender() is even called, so an obviously
// incomplete lookup never reaches the network/rate-limit layer at all.
function lookupStatus() {
  var input = document.getElementById('codeInput');
  var lastNameInput = document.getElementById('lastNameInput');
  var code = input.value.trim().toUpperCase();
  var lastName = lastNameInput.value.trim();
  if (!code && !lastName) return;
  if (!code.match(/^AD-\d{5}-\d{4}$/)) {
    showDashState('<p style="color:var(--error);">Please enter a valid adoption code (e.g.\u00a0AD-47293-2026).</p>');
    return;
  }
  if (!lastName) {
    showDashState('<p style="color:var(--error);">Please enter the last name used on the application.</p>');
    return;
  }
  // Reflect the code (only -- never the last name) in the URL without
  // reloading, so the page stays shareable/bookmarkable the same way it
  // always was. The last name is deliberately never put in the URL: a
  // shared/bookmarked link should still require the visitor to type the
  // second identifier themselves, not carry it along automatically.
  if (window.history && window.history.replaceState) {
    var url = new URL(window.location.href);
    url.searchParams.set('code', code);
    window.history.replaceState({}, '', url);
  }
  fetchAndRender(code, lastName);
}

// fetchAndRender(): previously did a raw, unauthenticated GET of the
// static application.json file directly. Now POSTs the code + last name
// to the new server-side endpoint (adoption-status-check.php), which
// rate-limits, verifies the second identifier, and returns only the
// fields this page actually renders. A 429 (rate limited) is shown with
// its own distinct message rather than being folded into the generic
// "not found" text, so a legitimate user searching too quickly isn't told
// their code might be wrong.
function fetchAndRender(code, lastName) {
  showDashState('<p>Checking status&hellip;</p>');
  fetch('/adoptions/status/adoption-status-check.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    cache: 'no-store',
    body: JSON.stringify({ code: code, last_name: lastName })
  })
    .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, status: r.status, data: d }; }); })
    .then(function (result) {
      if (result.ok && result.data && result.data.success) {
        renderDashboard(result.data, code);
      } else if (result.status === 429) {
        showDashState('<p class="error" style="color:var(--error);">Too many lookups. Please wait a moment and try again.</p>');
      } else {
        showDashState('<p class="error" style="color:var(--error);">No application found matching that code and last name. Please double-check both and try again.</p>');
      }
    })
    .catch(function () {
      showDashState('<p class="error" style="color:var(--error);">Something went wrong checking your status. Please try again in a moment.</p>');
    });
}
// TASK-3 END
```
<!-- TASK-3 END -->

**4. Auto-lookup from `?code=` — prefill only, never auto-submit without the second identifier**

<!-- TASK-3 START -->
**Old code:**
```js
// ─── Auto-lookup from ?code= URL param on page load ────────────────────────
(function () {
  var params = new URLSearchParams(window.location.search);
  var code = params.get('code');
  if (code) {
    code = code.trim().toUpperCase();
    document.getElementById('codeInput').value = code;
    if (code.match(/^AD-\d{5}-\d{4}$/)) {
      fetchAndRender(code);
    } else {
      showDashState('<p style="color:var(--error);">Invalid adoption code in URL.</p>');
    }
  }
  document.getElementById('codeInput').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') lookupStatus();
  });
})();
```
**New code:**
```js
// ─── Auto-lookup from ?code= URL param on page load ────────────────────────
// TASK-3 START
// Auto-lookup from ?code= URL param on page load: previously this fetched
// immediately once a valid code was in the URL. It no longer can -- the
// last name is required and is deliberately never included in the URL
// (see lookupStatus() above), so a bookmarked/shared link now prefills
// the code field and focuses the Last Name field, prompting the visitor
// to supply the second identifier themselves rather than auto-completing
// the lookup for them.
(function () {
  var params = new URLSearchParams(window.location.search);
  var code = params.get('code');
  if (code) {
    code = code.trim().toUpperCase();
    document.getElementById('codeInput').value = code;
    if (code.match(/^AD-\d{5}-\d{4}$/)) {
      showDashState('<p>Enter the last name on the application above to view this status.</p>');
      var lastNameInput = document.getElementById('lastNameInput');
      if (lastNameInput) lastNameInput.focus();
    } else {
      showDashState('<p style="color:var(--error);">Invalid adoption code in URL.</p>');
    }
  }
  document.getElementById('codeInput').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') lookupStatus();
  });
  var lastNameEl = document.getElementById('lastNameInput');
  if (lastNameEl) {
    lastNameEl.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') lookupStatus();
    });
  }
})();
// TASK-3 END
```
<!-- TASK-3 END -->

## New files

- `adoptions/status/adoption-status-check.php` — New file — in the zip.
  Server-side endpoint that rate-limits lookups per IP (10 / 10 min,
  `flock()`'d), requires a `last_name` second identifier matched against
  `application.json`'s `applicant_name` (same last-whitespace-token
  approach as Task 2, for consistency), returns one identical generic
  "not found" response for every failure case, a distinct 429 for rate
  limiting, and — on success — only the subset of fields `status.html`
  actually renders, never the full `application.json` record. Deploys
  alongside `index.html` in the same `adoptions/status/` folder, since
  the frontend calls it as a same-folder relative endpoint. This folder
  itself is not new — `adoption-website-project-summary.md` Section 7
  already documents `adoptions/status/index.html` as a live, existing
  file at this path, so this new file just joins it there.
- `adoptions/status/rate-limit-data/.htaccess` — New file — in the zip.
  Deny-all protection (`Require all denied` / `Deny from all`, both
  Apache 2.4 and 2.2 forms) for a folder this task introduces — see
  "New folder — not in either project summary doc" immediately below.

## New folder — not in either project summary doc ⚠️

**`adoptions/status/rate-limit-data/`** does not appear anywhere in
`adoption-website-project-summary.md` or `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`
— confirmed by searching both files directly for any existing
rate-limit-storage path; neither document one. This is a brand new
folder this task introduces, flagged heavily per hand-in requirements:

- **Create it at:** `adoptions/status/rate-limit-data/` — i.e. as a new
  subfolder directly inside the already-existing, already-documented
  `adoptions/status/` folder (the same folder `index.html` and the new
  `adoption-status-check.php` live in).
- **What it's for:** `adoption-status-check.php` writes its per-IP
  rate-limit counters and a single failed-lookup counter file into this
  folder at runtime (it attempts to create the folder itself via
  `@mkdir()` on first use if it doesn't already exist, but the
  integrator should not rely on that alone — see note below).
- **What's in the zip for it:** only its `.htaccess` (deny-all
  protection) — the folder must exist on the live server with write
  permission for the PHP process, or the rate limiter fails open (logs
  an error and skips limiting rather than blocking legitimate users —
  see `adoption-status-check.php`'s own comments at the rate-limiting
  block) rather than hard-failing, but this means a missing/unwritable
  folder would silently leave that one protection layer inactive
  without an obvious symptom. **Recommend the integrator either
  pre-create this folder with the right ownership/permissions during
  deployment, or confirm after deploy that it got auto-created and is
  writable** — this wasn't verifiable from this sandbox (no live server
  access).

**A related finding, corrected here rather than left as a loose claim:**
earlier drafts of this completion file described the `.htaccess`
protection as "mirroring the pattern already established by Task 1's
`api/.htaccess`." On this re-check, that precedent is not something
written down in either foundational summary doc — it's precedent from
Task 1's own prior delivery within this same multi-task project (a
different worker's output I only know about from this project's own
running notes, not from `adoption-website-project-summary.md` or
`ADMIN_DASHBOARD_PROJECT_SUMMARY.md`). The underlying security reasoning
for deny-all-protecting a runtime data folder stands on its own either
way, but flagging the correction so the file-path claim isn't taken as
"this is documented in the project's foundational docs" when it isn't.

## ⚠️ Possible path-prefix mismatch

Separate work on this project found that in the actual monorepo,
`adopt.petcenterco.com`'s files are rooted under a top-level `adopt/`
folder (e.g. `adopt/adoptions/status/index.html`,
`adopt/admin/index.html`), not bare `adoptions/...` at the repo root.
`WORKER-DELIVERY-GUIDE.md` doesn't address this, and every live path in
this completion file and in the zip (`adoptions/status/...`) is written
without that `adopt/` prefix, matching the live-path style
`adoption-website-project-summary.md` itself uses (which describes
`adopt.petcenterco.com` as its own site root, not as a subfolder of
something else). If these files are being merged into that same
monorepo rather than deployed straight onto `adopt.petcenterco.com`'s
own web root, every path here may need an `adopt/` prefix added
(`adopt/adoptions/status/adoption-status-check.php`,
`adopt/adoptions/status/rate-limit-data/.htaccess`). Flagging this
rather than guessing either way — same ambiguity already flagged on
Task 2's hand-in for this project.

## Pending / Open Items

- The path-prefix question immediately above.
- `adoptions/status/rate-limit-data/` needs to actually exist with
  write permission on the live server — see "New folder" section above
  for why this couldn't be verified from this sandbox.
- Nothing else pending; no other open questions or unresolved doubts
  for this task.

## Bugs Found Outside Task Scope

None found.

## Self-Check Confirmation

Only `status.html` (changed) and the two new files above were touched —
no other file on this project was opened for editing. `status.html`'s
original `application.json`/`adoption-application.php` data contract,
header/footer/hamburger markup, status-badge/carousel/payment-info
rendering, and all CSS/design tokens outside the lookup card were left
untouched. Every changed/new block in `status.html` carries a
`TASK-3 START` / `TASK-3 END` anchor comment explaining what it does, and
every "Old code" snippet above was copied character-for-character from
the original `status.html` I was given and appears exactly once in it
(each was isolated with a file-editing tool that itself requires a unique
match before it will apply an edit, so uniqueness is enforced, not just
asserted). Both new files are listed above with their exact zip paths,
and `unzip -l` on the zip confirms those are the only two files it
contains, at those exact paths, matching this list with nothing extra.
Neither new file contains `config.php`, `pet.json`, `.env`, a secret, a
password, a token/key value, or anything from `applications/` or
`uploads/` (checked directly; the one text match for the word "token" in
`adoption-status-check.php` is a code comment about name-matching logic,
not a credential). The one `.html` page this task touches (`status.html`)
was never renamed on disk locally, but its live path is confirmed as
`adoptions/status/index.html` — the strict `index.html` live-deployment
naming rule — matching exactly what `adoption-website-project-summary.md`
Section 7 already documents it as. No other `.html` file is part of this
delivery.

**Sandbox limitation, stated plainly rather than presented as verified:**
this environment has no PHP binary and no Chromium/browser available, so
`adoption-status-check.php` could not be run against a real PHP
interpreter and `status.html` could not be visually verified in an
actual rendered browser (no Playwright/CDP run was possible). In their
place: a manual brace/parenthesis balance check on the PHP file, a
manual read-through of the PHP for control-flow correctness, a
brace/paren balance check on the full HTML file, a duplicate-`id` scan,
and a `node --check` pass on the extracted inline `<script>` block
(syntax OK). These substitute for, but are not equivalent to, an actual
PHP run and a real rendered-browser check.

## SECOND VERIFICATION ROUND

Per a request to re-verify, re-read `adoption-website-project-summary.md`
in full, `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s Warning & Instructions/
Directives and Security Features sections, the full task tracker, and
Task 3's own spec again — confirmed sync; nothing missing against those.
Re-read both delivered files end to end, cross-checked every field name
`adoption-status-check.php` reads (`applicant_name`, `applicant_phone`,
`applicant_email`, `adoption_fee`, `pet_name`, `pet_breed`, `pet_age`,
`pet_image_url`, `adoption_code`, `status`, `applied_at`, `approved_by`,
`approved_position`, `approved_at`, `checkout_url`, `rejection_notes`,
`additional_pet_ids`) against the exact keys `adoption-application.php`
actually writes, and against every field `status.html`'s
`renderDashboard()` actually reads — all match exactly, nothing missing
or misspelled, and `additional_pet_ids`' pet-carousel resolution needs
nothing beyond that one array (the category prefix it needs is parsed
client-side from the pet ID itself via the existing `catMap`, not a
separate field from the endpoint).

Two real issues found and fixed this pass, both inside the brand-new
`adoption-status-check.php` — since that file was new (not yet an
existing live file at the time), both fixes were made directly in the
file now sitting in the zip, with no separate old/new code block needed
for them (per the hand-in rule for a new file revised before its first
delivery):

1. **Path-prefix check bug**: the `realpath()`/`strpos()` re-check
   compared the resolved application folder against `$APPLICATIONS_BASE`
   without a trailing separator, which would also have accepted a
   hypothetical sibling directory merely starting with the same string
   (e.g. `applications-evil` next to `applications`), not just genuine
   descendants. Fixed by comparing against
   `$APPLICATIONS_BASE . DIRECTORY_SEPARATOR`. Low real-world risk given
   `$code` is already regex-locked to `AD-#####-####` before this check
   ever runs, but wrong for the exact pattern it was meant to mirror from
   `delete-application.php`, on a task whose whole point is careful
   hardening — worth fixing outright.
2. **Missing `.htaccess` on the new rate-limit data folder** —
   `adoption-status-check.php` creates `rate-limit-data/` inside the
   public web root at runtime, and nothing stopped that folder from
   being requested directly over HTTP. Added
   `adoptions/status/rate-limit-data/.htaccess` (now listed under New
   files above), mirroring Task 1's `api/.htaccess` precedent.

The zip in this delivery already contains the corrected, final version
of `adoption-status-check.php` — no outdated version is included
anywhere.
