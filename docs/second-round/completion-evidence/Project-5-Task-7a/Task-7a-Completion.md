# Task-7a-Completion.md

## Task Number

Task 7a — Project 5: Pet-Match Request — Notification Preferences UI & Match Tagging (from `WEB-PROJECTS-3-4-5-TASK-ASSIGNMENTS.md`)

## Hand-in note (format corrected twice this pass — both corrections disclosed, not hidden)

This completion file went through two format corrections in direct response to feedback, both applied in full before this final version:

1. **First correction:** rebuilt into the `WORKER-DELIVERY-GUIDE.md` 4-column `Files Changed` format (source name / live path / changed-or-new / shared-with-another-task), with the one new file (`check-pet-match-status.php`) moved into a zip rather than pasted as code.
2. **Second correction:** the zip must contain the completion file itself alongside any new files (not sit outside it), the zip must be named `[Project-Name]-[Task-Number].zip` using the task doc's own self-identified project name, every `.html` page confirmed against strict `index.html`-at-live-path naming, and — most consequential — any new folder path not already documented in either project summary must be heavily flagged rather than assumed. Re-checking that last point against the new file's originally-assumed site-root placement surfaced a real, previously-shipped bug (below), not just a naming/packaging fix.

**Zip name used: `Project-5-Task-7a.zip`** — "Project 5" is this task's own self-identified project name (its own section header in `WEB-PROJECTS-3-4-5-TASK-ASSIGNMENTS.md` reads "Project 5: Pet-Match Request"), matched exactly to the casing/format Task 6a's and Task 6b's own completion files used for the same project (`Project-5-Task-6a.zip`, `Project-5-Task-6b.zip`), so every worker on this project sorts consistently rather than each inventing a slightly different name.

## What Was Required

Add a notification-preference choice (email, phone-text, phone-call, live-chat) to the visitor Pet Match Request form, with the two phone channels shown but disabled since no backend exists for them yet. Reuse the site's existing admin-to-user email pattern for the email channel; leave live-chat's actual send mechanism to Task 7b's separate design. Add a notification icon to the account UI — built as an explicit stub, since no real account system exists yet — and visibly tag a visitor's fulfilled request as "Matched" wherever the matching pet appears (its card, hero image, and recommendation cards), reusing the site's existing "Adopted" badge pattern rather than a new implementation.

This completion file also documents a self-audit pass (requested directly) that found and fixed several real issues in previously-delivered work: a pre-existing footer grid-column bug in two files this task edits, a genuine gap in this task's own notification-icon stub (desktop-only, never mirrored to the mobile menu, plus a panel-positioning bug the mobile fix then exposed), and — surfaced only while re-verifying this hand-in's new-folder-path flagging requirement — a real path bug in this task's own `matched_pet_id` lookup code in two files, caused by assuming the new file would sit at the bare site root when it does not.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `pet-match.html` | `pet-match/index.html` | Changed | Yes — NOTIFICATION-PREFS zone, depends on Task 5's FORM-CORE zone; file itself is owned/maintained by Task 5 |
| `cats.html` | `pets/cats/index.html` | Changed | Canonical public-page header/card blueprint — same pattern applies to the other 6 category pages, not touched by this task |
| `pet-detail-v1.html` | `pets/detail/index.html` | Changed | Standalone detail-page file |
| `admin-dashboard.html` | `admin/index.html` | Changed | Shared with Task 6b — this task's addition sits inside Task 6b's existing Fulfilled modal |
| `update-pet-match-request.php` | `admin/update-pet-match-request.php` | Changed | Shared with Task 6b — this is Task 6b's own backend file |
| `check-pet-match-status.php` | `adoptions/check-pet-match-status.php` — **NEW FOLDER PATTERN, FLAGGED BELOW** | New — in `Project-5-Task-7a.zip` | New — reads Task 6a/6b's request storage shape, does not modify their files |

### ⚠️ New-folder-path flag: `check-pet-match-status.php` at `/adoptions/check-pet-match-status.php`

Neither `adoption-website-project-summary.md` nor `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` documents **any** PHP file living directly inside `/adoptions/` itself (that folder is documented purely as a data folder — `/adoptions/pets/`, `/adoptions/pet-match-requests/` — never as a location that also holds an executable endpoint). Placing an endpoint there is a genuinely new pattern this task introduces, not something already established, so it's flagged explicitly rather than assumed fine:

- **Folder to use:** `/adoptions/` (already exists as a data folder; this does not require creating a new top-level folder, only adding a `.php` file inside an existing one that has so far only held subfolders).
- **Exact file:** `/adoptions/check-pet-match-status.php`.
- **Why this location and not the site root or `/admin/`:** this endpoint is public and unauthenticated (called by visitors' browsers via `cats.html`'s notification stub, not by admin staff), so `/admin/` is wrong. The first draft of this file assumed the bare site root — but neither summary doc shows any existing PHP file living there either, which would have been an equally new, equally undocumented pattern, just a different one. Sitting inside `/adoptions/` instead — next to the exact two data folders (`pets/`, `pet-match-requests/`) this file reads from — mirrors the one pattern both summary docs *do* already establish repeatedly (`adoption-application.php` sits inside `/apply/` alongside its own data; `update-adoption.php` sits inside `/admin/` alongside the admin data/config it needs).
- **Consequence of re-checking this:** moving the file here forced its internal path references to be re-derived correctly rather than left as they were — which is how the bug below was caught.

## Bugs Found Outside Task Scope

1. **This task's own path bug, caught while re-verifying the new-folder flag above (fixed — this one is squarely this task's own code, not another task's file).** Both `check-pet-match-status.php` (first draft) and the `matched_pet_id` block this task added to `update-pet-match-request.php` used `__DIR__ . '/pets/' . $category . ...` to look up a pet's `pet.json`. This only ever happened to "work" by coincidence of where each file was assumed to sit:
   - In `check-pet-match-status.php`'s first draft (assumed site root), `__DIR__ . '/pets/'` meant `{site-root}/pets/` — the **wrong** folder. `adoption-website-project-summary.md`'s own folder-structure section is explicit that `/adoptions/pets/` (data) is *not* the same folder as the page-serving `/pets/` near the site root. Moving the file into `/adoptions/` (per the flag above) makes the same literal string `__DIR__ . '/pets/'` now correctly mean `{site-root}/adoptions/pets/` — fixed as a side effect of the relocation, and called out explicitly in that file's own comments rather than left as an accidental, unexplained "it happens to work now."
   - In `update-pet-match-request.php` (confirmed live at `admin/update-pet-match-request.php`), the same `__DIR__ . '/pets/'` pattern meant `{site-root}/admin/pets/` — a folder that doesn't exist at all. **Fixed** to `__DIR__ . '/../adoptions/pets/'`, correctly climbing from `/admin/` up to the site root before descending into `/adoptions/pets/`.
   - Neither of these had ever been tested end-to-end before this pass (no PHP interpreter is available in this working environment to actually execute either file — disclosed here plainly rather than claimed as verified by running it), so this was caught by re-reading the project summary's own folder-structure section against the code's literal path strings, not by seeing a real error.

2. **Task 6b's own pre-existing path bug in `update-pet-match-request.php`, outside this task's scope — logged, NOT fixed.** While tracing the bug above, this task's review also surfaced that `update-pet-match-request.php`'s own pre-existing line (written by Task 6b, untouched by this task before or after) —
   ```php
   $requestFile = __DIR__ . '/adoptions/pet-match-requests/' . $category . '/' . $requestCode . '/request.json';
   ```
   — has the identical class of bug: if this file truly lives at `admin/update-pet-match-request.php` (confirmed via `admin-dashboard.html`'s own relative `fetch('./update-pet-match-request.php', ...)` call, which only resolves correctly if both files share a folder), then `__DIR__ . '/adoptions/...'` means `{site-root}/admin/adoptions/...` — a folder that doesn't exist. This is **not fixed in this delivery** — it is Task 6b's own pre-existing logic in a line this task never touched, and the line-editing risk of changing someone else's already-shipped backend logic (versus the comparatively low-risk, single-value CSS fix logged below) is a different category of change than this task's own Scope Lock allows unilaterally. Flagged here for whoever owns Task 6b's file next, with the exact line and exact fix (`__DIR__ . '/../adoptions/pet-match-requests/...'`) already identified so it doesn't need rediscovering.

3. **Footer grid-column count bug — `cats.html` and `pet-detail-v1.html`, `.pc-footer__top` CSS rule (pre-existing, fixed directly — disclosed as a deliberate exception, not hidden).** `grid-template-columns: 2fr 1fr 1fr 1.5fr` (4 values) against 5 real `.pc-footer__top` children (`.pc-footer__brand` + 4 `.pc-footer__col`). This is the exact recurring bug named by `adoption-website-project-summary.md` Section 8. Unlike item 2 above, this was fixed directly in both files — it's a single-line, zero-ambiguity CSS value correction (not a change to any logic or behavior) inside files this task was already actively editing for its own header/footer/badge work, caught only because this session's self-audit specifically re-checked against Section 8's own named bug class. If a stricter reading of Scope Lock is preferred for even this kind of trivial fix, it should be reverted and re-logged as log-only instead — flagging this explicitly rather than quietly presenting the fix as if it had always been this task's own job.

4. **This task's own incomplete original delivery — not a Scope Lock exception at all, since it's this task's own feature, not another task's file — fixed.** The notification-icon stub this task built was originally wired only into `cats.html`'s desktop nav list; the separate `#pcMobileMenu` list the canonical header pattern requires to stay in sync with it never got the same treatment, so a mobile visitor with a saved stub email had no way to see the icon at all. Fixing the mobile trigger then surfaced a second, related bug: the notification panel itself was nested inside the desktop-only list, which the page hides entirely below 900px width — so even after adding a mobile trigger, the panel it opened was rendering at a 0×0 size, confirmed via actual Playwright rendering (DOM showed `.open` applied and `display:block`, but `getBoundingClientRect()` returned all zeros) rather than assumed fixed from code inspection alone. Both are fixed: the panel markup moved to a location that's never `display:none` at any width, and its CSS given a mobile-specific position override, re-verified visually via Playwright screenshots at both a 375px and a 1280px viewport after the fix.

## Code Changes

### `pet-match.html` → live path `pet-match/index.html`

<!-- TASK-7a START -->
**Old code:**
```css
.ql-toolbar .ql-undo svg, .ql-toolbar .ql-redo svg { stroke: var(--primary); }

/* Honeypot (TASK-5b) — off-screen, not display:none, so bots that specifically
   skip display:none fields still get caught */
.pm-hp-field { position: absolute; left: -9999px; top: -9999px; width: 1px; height: 1px; overflow: hidden; }

  </style>
```
**New code:**
```css
.ql-toolbar .ql-undo svg, .ql-toolbar .ql-redo svg { stroke: var(--primary); }

/* Honeypot (TASK-5b) — off-screen, not display:none, so bots that specifically
   skip display:none fields still get caught */
.pm-hp-field { position: absolute; left: -9999px; top: -9999px; width: 1px; height: 1px; overflow: hidden; }

/* ─── Notification Preference (TASK-7a) — radio-pill row styled to match the
   existing .pm-radio-opt look used elsewhere on this form, with an added
   disabled/unavailable treatment for the two channels that have no backend
   yet (phone-text, phone-call). Grayed out and non-interactive rather than
   hidden, per Task 7a's own instruction that visitors should see the option
   exists even though it can't be selected yet — hiding it entirely would
   look like the form doesn't ask at all.
   RE-APPLIED 2026-09-23: Task 5's Revision 4 (icons/breed-data/dropdown fix)
   replaced this whole file again; a stand-in version had been reconstructed
   from this task's own written notes rather than its actual file (correctly
   flagged as a placeholder, not presented as final) — this replaces that
   stand-in with the verified original, now correctly nested inside the hook
   container itself rather than sitting just before it. ── */
.pm-notify-opt {
  display: flex; align-items: center; gap: 8px; cursor: pointer;
  padding: 10px 4px;
}
.pm-notify-opt input[type="radio"] {
  width: 18px; height: 18px; accent-color: var(--navy); flex-shrink: 0; margin: 0; cursor: pointer;
}
.pm-notify-opt span { font-size: 15px; color: var(--text); font-weight: 500; }
.pm-notify-opt--disabled { cursor: not-allowed; opacity: 0.5; }
.pm-notify-opt--disabled input[type="radio"] { cursor: not-allowed; accent-color: var(--muted); }
.pm-notify-opt--disabled span { color: var(--muted); }
.pm-notify-unavailable {
  font-size: 12px; font-style: italic; color: var(--muted); margin-left: 26px; margin-top: -6px; margin-bottom: 2px;
}

  </style>
```
<!-- TASK-7a END -->

<!-- TASK-7a START -->
**Old code:**
```html
      <input type="hidden" id="pmDetail" name="additional_detail" value="">

      <!-- TASK-7a START — notification-preference radios re-applied here
           2026-09-22 (content-identical to the version this task's own
           completion file documents as authoritative — radios went
           directly into the hook container the form left open; the exact
           field name was never guessable from pet-match.html alone, so
           cross-checking Task 7a's own file remains the authoritative
           source for it, same as before). Values: 'email' (default,
           functional), 'live-chat' (selectable, wiring is Task 7b's job
           entirely, not built here), 'phone-text' and 'phone-call' (shown
           disabled with a "currently unavailable" note — no backend exists
           for either yet). -->
      <div class="pm-q" style="margin-top:32px;">How should we notify you of a match?</div>
      <div class="pm-radio-row">
        <label class="pm-radio-opt">
          <input type="radio" name="notify_preference" value="email" checked>
          <span>Email</span>
        </label>
        <label class="pm-radio-opt">
          <input type="radio" name="notify_preference" value="live-chat">
          <span>Live Chat</span>
        </label>
        <label class="pm-radio-opt" style="opacity:0.5;cursor:not-allowed;">
          <input type="radio" name="notify_preference" value="phone-text" disabled>
          <span>Text Message <em style="font-style:normal;color:var(--muted);font-size:12.5px;">*currently unavailable</em></span>
        </label>
        <label class="pm-radio-opt" style="opacity:0.5;cursor:not-allowed;">
          <input type="radio" name="notify_preference" value="phone-call" disabled>
          <span>Phone Call <em style="font-style:normal;color:var(--muted);font-size:12.5px;">*currently unavailable</em></span>
        </label>
      </div>
      <!-- TASK-7a END -->

      <!-- Notification-preference hook point for Task 7a. Left intentionally
           empty per Task 5's coordination note, so Task 7a can slot its radio
           group in without restructuring the page. Do not remove this
           container. -->
      <div id="notifyPreferenceHook" data-task7a-hook="true" style="margin-top:24px;"></div>
    </div>
```
**New code:**
```html
      <input type="hidden" id="pmDetail" name="additional_detail" value="">

      <!-- TASK-7a START — Notification Preference. Slotted into Task 5's
           reserved #notifyPreferenceHook container without restructuring the
           page, per that task's coordination note. Four channels: email
           (fully wired — reuses the same admin-to-user email pattern the
           site already uses for adoption-application.php's confirmation
           email and chat-mail.php's admin-triggered notify), phone-text and
           phone-call (shown but disabled — no backend exists for either yet,
           per Task 7a's own scope), and live-chat (selectable, but its
           actual send mechanism is Task 7b's separate design — see
           Task-7b-Completion.md — not built here; selecting it just records
           the preference). Email is checked by default since it's the only
           channel that actually works today.
           RE-APPLIED 2026-09-23: Task 5's Revision 4 (icons/breed-data/
           dropdown fix) replaced this whole file again; a stand-in version
           had been reconstructed from this task's own written notes rather
           than its actual file (correctly flagged as a placeholder, not
           presented as final) — this replaces that stand-in with the
           verified original, now correctly nested inside the hook
           container itself rather than sitting just before it. -->
      <div id="notifyPreferenceHook" data-task7a-hook="true" style="margin-top:24px;">
        <div class="pm-q" style="margin-bottom:10px;">How should we notify you if we find a match?</div>
        <div class="pm-radio-row" role="radiogroup" aria-label="Notification preference">

          <label class="pm-notify-opt">
            <input type="radio" name="notify_preference" value="email" checked>
            <span>Email</span>
          </label>

          <label class="pm-notify-opt">
            <input type="radio" name="notify_preference" value="live-chat">
            <span>Live Chat</span>
          </label>

          <label class="pm-notify-opt pm-notify-opt--disabled">
            <input type="radio" name="notify_preference" value="phone-text" disabled aria-disabled="true">
            <span>Text Message</span>
          </label>
          <div class="pm-notify-unavailable">*currently unavailable</div>

          <label class="pm-notify-opt pm-notify-opt--disabled">
            <input type="radio" name="notify_preference" value="phone-call" disabled aria-disabled="true">
            <span>Phone Call</span>
          </label>
          <div class="pm-notify-unavailable">*currently unavailable</div>

        </div>
        <p class="pm-hint">You'll always be able to see your request's status by checking back with your reference code, regardless of which option you choose.</p>
      </div>
      <!-- TASK-7a END -->
    </div>
```
<!-- TASK-7a END -->

<!-- TASK-7a START -->
**Old code:**
```javascript
      .then(function (result) {
        nextBtn.disabled = false;
        if (result.ok && result.json && result.json.reference_code) {
          // TASK-7a START — re-applied 2026-09-22, content-identical to the
          // version documented as authoritative in Task-7a-Completion.md.
          // Stub account keyed on email, since no real account system exists
          // yet (signin/index.html is a placeholder per Section 18) — this
          // lets cats.html's notification-icon stub and matched-badge check
          // recognize this visitor's own submitted request(s) by email in a
          // later browser session on the same device, without a real login.
          // Explicitly a stub, not a finished integration — flagged as such
          // in Task-7a-Completion.md.
          try {
            var stubEmail = document.getElementById('pmEmail').value.trim();
            if (stubEmail) { localStorage.setItem('pc_pet_match_email', stubEmail); }
          } catch (e) { /* localStorage unavailable (private browsing, etc.) — non-fatal, submission already succeeded */ }
          // TASK-7a END
          showSuccessModal(result.json.reference_code);
        } else {
          alert((result.json && result.json.message) || 'Something went wrong submitting your request. Please try again.');
        }
      })
```
**New code:**
```javascript
      .then(function (result) {
        nextBtn.disabled = false;
        if (result.ok && result.json && result.json.reference_code) {
          // TASK-7a START — Writes the stub identity the header's
          // notification-icon stub (see cats.html) reads back. This is
          // NOT a session or account — it's a plain email string, matching
          // this stub's own documented trust level (no verification).
          // Wrapped in try/catch since localStorage can throw in private
          // browsing / disabled-storage contexts, and a storage failure
          // here must never block the success modal from showing.
          // RE-APPLIED 2026-09-23: Task 5's stand-in (correctly flagged as
          // unverified) used key 'pc_pet_match_email', a guess made without
          // access to this task's actual file. The real key cats.html reads
          // is 'pc_stub_petmatch_email' (confirmed directly against that
          // file's own STUB_EMAIL_KEY constant) — corrected here so the two
          // sides actually connect instead of writing/reading different
          // keys silently past each other.
          try { localStorage.setItem('pc_stub_petmatch_email', payload.email); } catch (e) { /* non-fatal */ }
          // TASK-7a END
          showSuccessModal(result.json.reference_code);
        } else {
          alert((result.json && result.json.message) || 'Something went wrong submitting your request. Please try again.');
        }
      })
```
<!-- TASK-7a END -->

---

### `cats.html` → live path `pets/cats/index.html`

<!-- TASK-7a START -->
**Old code:**
```css
  box-shadow: 0 2px 8px rgba(0,0,0,0.25);
}

/* Card body */
```
**New code:**
```css
  box-shadow: 0 2px 8px rgba(0,0,0,0.25);
}

/* TASK-7a START — "Matched" badge. Exact same flag-strip shell as the
   Adopted badge above (position/shape/typography all copied verbatim, per
   the rule to reuse the existing badge implementation rather than build a
   new one) with only the background color swapped to gold, so a Matched
   pet is visually distinct from an Adopted one if a visitor ever sees both
   in the same grid. Driven by pet.status === 'Matched' — the same status
   field the Adopted badge already reads, just a second value on it. */
.pc-pet-card__matched-badge {
  position: absolute;
  top: 12px;
  left: 0;
  background: #d7bd88;
  color: #10214b;
  font-family: 'Sora', sans-serif;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  padding: 6px 14px;
  border-radius: 0 4px 4px 0;
  box-shadow: 0 2px 8px rgba(0,0,0,0.25);
}
/* TASK-7a END */

/* Card body */
```
<!-- TASK-7a END -->

<!-- TASK-7a START -->
**Old code:**
```css
.pc-header__nav .nav-signup a:hover,
.pc-header__nav .nav-login a:hover { background: rgba(215,189,136,0.12); }
.pc-header__nav .has-drop > ul {
```
**New code:**
```css
.pc-header__nav .nav-signup a:hover,
.pc-header__nav .nav-login a:hover { background: rgba(215,189,136,0.12); }

/* TASK-7a START — Notification icon STUB. There is no real account/signup
   system yet (signin/index.html is a placeholder per the project's own
   docs) — the CEO is building real auth separately. This is a LOCAL,
   TEMPORARY stand-in only: it keys off an email address stored in
   localStorage (set today only by the pet-match form's success handler —
   see the JS below), NOT a verified session. It exists purely so the
   "matched" tag has somewhere to surface for a visitor who submitted a
   Pet Match Request from this browser. This entire block should be
   deleted and replaced once the CEO's real account system exists — do
   not treat this as the account UI itself. */
.pc-header__notify-stub {
  display: none;
  position: relative;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  margin-left: 4px;
  border-radius: 50%;
  color: #d7bd88;
  cursor: pointer;
  background: transparent;
  border: none;
  transition: background 0.2s ease;
}
.pc-header__notify-stub:hover { background: rgba(215,189,136,0.12); }
.pc-header__notify-stub svg { width: 20px; height: 20px; }
.pc-header__notify-stub__dot {
  position: absolute;
  top: 6px;
  right: 6px;
  width: 9px;
  height: 9px;
  border-radius: 50%;
  background: #ED7624;
  border: 2px solid #10214b;
  display: none;
}
.pc-header__notify-stub__panel {
  display: none;
  position: absolute;
  top: 48px;
  right: 0;
  width: 280px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 8px 40px rgba(16,33,75,0.20);
  padding: 16px;
  /* TASK-7a FIX (self-audit pass): raised from 50 to 1000 — the panel is
     now a direct <header> child (see the markup comment where it's
     placed) so it can be reached from the mobile trigger too, and the
     mobile menu itself sits at z-index:999 (see .pc-mobile-menu above).
     Since the mobile trigger's click handler always closes that menu
     before opening this panel, the two should never actually overlap in
     practice — but matching this to above the mobile-menu z-index removes
     any dependency on that ordering always firing correctly, consistent
     with Warning & Instructions rule 1 ("don't assume it worked because
     it didn't error") and the project's z-index-collision lesson
     documented in adoption-website-project-summary.md Section 15.1. */
  z-index: 1000;
}
.pc-header__notify-stub__panel.open { display: block; }
/* TASK-7a FIX (self-audit pass): on narrow screens, right:0 + width:280px
   can overflow the header's own 24px side padding on very small phones,
   and there's no longer a nearby desktop button to visually anchor under
   once the panel moved to a shared location (see markup comment). Centers
   the panel under the header instead, capped so it never exceeds the
   viewport width with margin, confirmed via actual Playwright rendering
   at a 375px mobile viewport during this fix. */
@media (max-width: 900px) {
  .pc-header__notify-stub__panel {
    top: 68px;
    right: 16px;
    left: 16px;
    width: auto;
    max-width: 320px;
    margin-left: auto;
  }
}
.pc-header__notify-stub__panel h4 {
  font-family: 'Sora', sans-serif;
  font-size: 13px;
  font-weight: 700;
  color: #10214b;
  margin-bottom: 8px;
}
.pc-header__notify-stub__panel p {
  font-size: 12px;
  color: #7a6d5f;
  line-height: 1.5;
}
.pc-header__notify-stub__panel a { font-weight: 600; }

.pc-header__nav .has-drop > ul {
```
<!-- TASK-7a END -->

<!-- TASK-7a START -->
**Old code:**
```html
    <!-- TASK-1 START -->
    <!-- Path changed from /rehome/ to /signin/ per CEO's request to retire the "rehome" URL sitewide (propagated for sitewide consistency during merge) -->
    <li class="nav-signup"><a href="/signin/">Sign Up</a></li>
    <!-- TASK-1 END -->
  </ul>
```
**New code:**
```html
    <!-- TASK-1 START -->
    <!-- Path changed from /rehome/ to /signin/ per CEO's request to retire the "rehome" URL sitewide (propagated for sitewide consistency during merge) -->
    <li class="nav-signup"><a href="/signin/">Sign Up</a></li>
    <!-- TASK-1 END -->
    <!-- TASK-7a START — Notification icon STUB (see the CSS comment above
         for full context: no real account system exists yet, this is a
         temporary local stand-in keyed on an email in localStorage, not a
         verified session). Hidden by default; JS below reveals it only if
         a stub email is already on this device. Do not build further on
         top of this — it's meant to be replaced wholesale once real auth
         exists. -->
    <li style="display:none;" id="pcNotifyStubLi">
      <button class="pc-header__notify-stub" id="pcNotifyStub" type="button" aria-label="Pet match notifications" aria-haspopup="true" aria-expanded="false">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        <span class="pc-header__notify-stub__dot" id="pcNotifyStubDot"></span>
      </button>
    </li>
    <!-- TASK-7a END (panel markup moved to just before </header> — see
         comment there for why: it used to live inside this li, but this
         li sits inside .pc-header__nav, which the page sets to
         display:none at mobile widths, collapsing the panel to a 0x0 box
         with no way to become visible even when its own .open class was
         toggled — confirmed via actual Playwright rendering during the
         self-audit pass, not just code inspection. Moving it to a
         never-hidden location fixes this for both triggers.) -->
  </ul>
```
<!-- TASK-7a END -->

<!-- TASK-7a START -->
**Old code:**
```html
    <!-- TASK-1 START -->
    <!-- Path changed from /rehome/ to /signin/ per CEO's request to retire the "rehome" URL sitewide (propagated for sitewide consistency during merge) -->
    <a href="/signin/">Sign Up</a>
    <!-- TASK-1 END -->
    <a href="https://donate.petcenterco.com" class="mobile-donate" target="_blank" rel="noopener">Donate</a>
  </nav>
```
**New code:**
```html
    <!-- TASK-1 START -->
    <!-- Path changed from /rehome/ to /signin/ per CEO's request to retire the "rehome" URL sitewide (propagated for sitewide consistency during merge) -->
    <a href="/signin/">Sign Up</a>
    <!-- TASK-1 END -->
    <!-- TASK-7a START — mobile-menu equivalent of the desktop notification
         icon stub above. FIX (self-audit pass): the original delivery only
         added the stub to the desktop pc-header__nav list, never to this
         separate #pcMobileMenu list the canonical pattern (Section 2.1 of
         adoption-website-project-summary.md) requires to stay in sync — a
         visitor on mobile with a saved stub email previously had no way to
         see the notification icon at all. This button is styled like the
         existing mobile-menu rows (not the desktop popover button, which
         wouldn't fit this list's plain-row pattern) and opens the exact
         same #pcNotifyStubPanel the desktop button does, via the shared
         script below — one panel, two possible triggers, so the content
         never has to be duplicated or kept in sync separately. Hidden by
         default, same as the desktop li; shown only if a stub email exists. -->
    <button type="button" id="pcNotifyStubMobile" style="display:none;width:100%;text-align:left;background:none;border:none;cursor:pointer;padding:12px 28px;font-family:'Sora',sans-serif;font-size:15px;font-weight:500;color:rgba(255,255,255,0.9);border-bottom:1px solid rgba(255,255,255,0.06);position:relative;" aria-haspopup="true" aria-expanded="false">
      Pet Match Updates
      <span id="pcNotifyStubMobileDot" style="display:none;position:absolute;top:14px;right:24px;width:9px;height:9px;border-radius:50%;background:#ED7624;"></span>
    </button>
    <!-- TASK-7a END -->
    <a href="https://donate.petcenterco.com" class="mobile-donate" target="_blank" rel="noopener">Donate</a>
  </nav>
  <!-- TASK-7a START — notification stub panel, re-homed here (FIX,
       self-audit pass). Previously lived inside the desktop-only
       #pcNotifyStubLi, a descendant of .pc-header__nav — which this page
       sets to display:none at <=900px, collapsing the panel to a 0x0 box
       that stayed functionally invisible no matter which trigger opened
       it (confirmed via Playwright: classList showed .open, computed
       style showed display:block/visibility:visible, but getBoundingClientRect
       was 0x0 — a real, rendered bug, not a hypothetical one). This
       location is a direct child of <header>, which is never display:none
       at any width, so the panel now works from both the desktop button
       and the mobile-menu row above, positioned via its own CSS below
       (fixed on mobile, absolute-to-header on desktop) rather than
       inheriting position from whichever trigger happens to be visible. -->
  <div class="pc-header__notify-stub__panel" id="pcNotifyStubPanel">
    <h4>Pet Match Updates</h4>
    <p id="pcNotifyStubBody">Checking for updates on your Pet Match Request&hellip;</p>
  </div>
  <!-- TASK-7a END -->
</header>
```
<!-- TASK-7a END -->

<!-- TASK-7a FIX (self-audit pass, pre-existing bug outside this task's original scope — see "Bugs Found Outside Task Scope" item 3 for why this one was fixed rather than only logged) -->
**Old code:**
```css
.pc-footer__inner { max-width: 1200px; margin: 0 auto; }
.pc-footer__top {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1.5fr;
  gap: 40px;
```
**New code:**
```css
.pc-footer__inner { max-width: 1200px; margin: 0 auto; }
.pc-footer__top {
  display: grid;
  /* TASK-7a FIX (self-audit pass): was `2fr 1fr 1fr 1.5fr` — only 4 track
     values against 5 real grid children (.pc-footer__brand + 4
     .pc-footer__col: Our Pets, Information, Support, Visit Us). This is
     the exact recurring bug named in adoption-website-project-summary.md
     Section 8 ("always verify the column count in the grid CSS matches
     the number of .pc-footer__col divs in the HTML") — pre-existing, not
     introduced by this task, but caught and fixed while already editing
     this file's header/footer area for the notification stub above.
     Matches Section 2.6's canonical `2fr 1fr 1.2fr 1fr 1.3fr` track list. */
  grid-template-columns: 2fr 1fr 1.2fr 1fr 1.3fr;
  gap: 40px;
```
<!-- TASK-7a END -->

<!-- TASK-7a START -->
**Old code:**
```javascript
  function closeMenu() {
    hamburger.classList.remove('active');
    mobileMenu.classList.remove('open');
    hamburger.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  }

  hamburger.addEventListener('click', function (e) {
```
**New code:**
```javascript
  function closeMenu() {
    hamburger.classList.remove('active');
    mobileMenu.classList.remove('open');
    hamburger.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  }
  // TASK-7a: exposed so the notification stub's mobile trigger (further
  // down this page) can close the mobile menu through the real function
  // rather than duplicating its four lines in a second place that could
  // silently drift out of sync with this one.
  window.pcCloseMobileMenu = closeMenu;

  hamburger.addEventListener('click', function (e) {
```
<!-- TASK-7a END -->

<!-- TASK-7a START -->
**Old code:**
```javascript
      // Adopted badge — "ADOPTED" strip style
      var adoptedBadge = p.status === 'Adopted'
        ? '<div class="pc-pet-card__adopted-badge">ADOPTED</div>'
        : '';
      
      return '<a href="' + url + '" class="pc-pet-card">' +
        '<div class="pc-pet-card__img-wrap">' +
          '<img src="' + img + '" alt="' + petName + '" class="pc-pet-card__img" loading="lazy">' +
          adoptedBadge +
        '</div>' +
        '<div class="pc-pet-card__body">' +
```
**New code:**
```javascript
      // Adopted badge — "ADOPTED" strip style
      var adoptedBadge = p.status === 'Adopted'
        ? '<div class="pc-pet-card__adopted-badge">ADOPTED</div>'
        : '';

      // TASK-7a START — "Matched" badge, same pattern as the Adopted badge
      // directly above (reads the same pet.json status field, just a
      // second value on it), for a pet that fulfilled a Pet Match Request
      // per Task 6b's admin action.
      var matchedBadge = p.status === 'Matched'
        ? '<div class="pc-pet-card__matched-badge">MATCHED</div>'
        : '';
      // TASK-7a END
      
      return '<a href="' + url + '" class="pc-pet-card">' +
        '<div class="pc-pet-card__img-wrap">' +
          '<img src="' + img + '" alt="' + petName + '" class="pc-pet-card__img" loading="lazy">' +
          adoptedBadge +
          matchedBadge +
        '</div>' +
        '<div class="pc-pet-card__body">' +
```
<!-- TASK-7a END -->

<!-- TASK-7a START -->
**Old code:**
```javascript
    applyAndRender();
  };

  // Load pets on page load
  loadPets();
})();
</script>
```
**New code:**
```javascript
    applyAndRender();
  };

  // Load pets on page load
  loadPets();
})();
</script>

<!-- TASK-7a START — Notification icon STUB script. Entirely self-contained
     and separate from the pet-grid script above on purpose: this whole
     block is meant to be deleted in one piece and replaced once the CEO's
     real account/auth system exists. It is NOT a session — it only checks
     a plain email string a visitor's browser remembered from the pet-match
     form, and asks the (also new, also part of this task) status-check
     endpoint whether any Pet Match Request submitted under that email has
     a Matched pet attached. Storage key deliberately namespaced
     "pc_stub_" so it's unmistakable in devtools/localStorage as temporary. -->
<script>
(function() {
  var STUB_EMAIL_KEY = 'pc_stub_petmatch_email';
  var STUB_SEEN_KEY  = 'pc_stub_petmatch_seen_matches'; // JSON array of request codes already viewed, so the dot only shows for NEW matches

  var btn   = document.getElementById('pcNotifyStub');
  var panel = document.getElementById('pcNotifyStubPanel');
  var dot   = document.getElementById('pcNotifyStubDot');
  var body  = document.getElementById('pcNotifyStubBody');
  if (!btn || !panel || !dot || !body) return;

  // TASK-7a: mobile-menu equivalent trigger (FIX, self-audit pass — see the
  // markup comment above for why this was missing). Optional lookups since
  // older cached copies of this file won't have these elements yet; the
  // desktop path above still works standalone if so.
  var mobileBtn = document.getElementById('pcNotifyStubMobile');
  var mobileDot = document.getElementById('pcNotifyStubMobileDot');

  var stubEmail = null;
  try { stubEmail = localStorage.getItem(STUB_EMAIL_KEY); } catch (e) { /* localStorage unavailable — stub silently stays hidden */ }
  if (!stubEmail) return; // No stub identity on this device — the notify icon's parent list item stays hidden (see pcNotifyStubLi below), matching Task 7a's own instruction that this only applies to a visitor who has an identity to check

  var li = document.getElementById('pcNotifyStubLi');
  if (li) li.style.display = 'flex';
  btn.style.display = 'inline-flex';
  if (mobileBtn) mobileBtn.style.display = 'block';

  function getSeen() {
    try { return JSON.parse(localStorage.getItem(STUB_SEEN_KEY) || '[]'); } catch (e) { return []; }
  }
  function markSeen(codes) {
    try { localStorage.setItem(STUB_SEEN_KEY, JSON.stringify(codes)); } catch (e) { /* non-fatal */ }
  }
  // TASK-7a: fulfilled_pet_note is admin-typed free text served back to a
  // public page and inserted via innerHTML below — escape it so it can
  // only ever render as text, never as markup, regardless of what an
  // admin happened to type into that field.
  function pmEscapeHtml(str) {
    var div = document.createElement('div');
    div.textContent = str == null ? '' : String(str);
    return div.innerHTML;
  }

  // TASK-7a: calls the matching stub-appropriate endpoint (see
  // check-pet-match-status.php, delivered in this task's zip) that looks
  // up Pet Match Requests by email — the same trust level as the rest of
  // this stub (an email string, not a verified session), consistent with
  // Task 7b's own confirmed principle that nothing in this feature should
  // pretend to a stronger identity guarantee than actually exists yet.
  // TASK-7a FIX (hand-in review pass): was '/check-pet-match-status.php'
  // (bare site root) — moved to /adoptions/ to sit alongside the data it
  // reads, since no existing PHP file in either project summary doc lives
  // directly at the site root; see that file's own LIVE LOCATION comment
  // for the full reasoning.
  fetch('/adoptions/check-pet-match-status.php?email=' + encodeURIComponent(stubEmail))
    .then(function(res) { return res.ok ? res.json() : { matches: [] }; })
    .then(function(data) {
      var matches = (data && data.matches) || [];
      if (!matches.length) {
        body.textContent = "No updates yet on your Pet Match Request. We'll show your matched pet here as soon as one's found.";
        return;
      }
      var seen = getSeen();
      var unseenCount = matches.filter(function(m) { return seen.indexOf(m.request_code) === -1; }).length;
      if (unseenCount > 0) {
        dot.style.display = 'block';
        if (mobileDot) mobileDot.style.display = 'block';
      }

      // TASK-7a: uses a real pet_name/pet_url when
      // check-pet-match-status.php resolved matched_pet_id to an actual
      // pet.json; falls back to the free-text fulfilled_pet_note alone
      // when no structured link exists yet (staff often won't have one —
      // matched_pet_id is optional on the admin's Fulfilled action).
      body.innerHTML = matches.map(function(m) {
        if (m.pet_name && m.pet_url) {
          return '<div style="margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid #f9f7f4;">' +
            '<strong style="color:#10214b;">' + pmEscapeHtml(m.pet_name) + '</strong> may be your match! ' +
            '<a href="' + pmEscapeHtml(m.pet_url) + '">View ' + pmEscapeHtml(m.pet_name) + '</a>' +
          '</div>';
        }
        var note = m.fulfilled_pet_note ? pmEscapeHtml(m.fulfilled_pet_note) : "We've found a potential match for your request!";
        return '<div style="margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid #f9f7f4;">' +
          '<strong style="color:#10214b;">Match found</strong><br>' +
          '<span>' + note + '</span><br>' +
          '<a href="/pets/' + encodeURIComponent(m.category) + '/">Browse ' + m.category + '</a>' +
        '</div>';
      }).join('');
    })
    .catch(function() {
      body.textContent = "Couldn't check for updates right now — try again shortly.";
    });

  btn.addEventListener('click', function() {
    var isOpen = panel.classList.toggle('open');
    btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    if (isOpen) {
      // Mark whatever's currently displayed as seen once the visitor opens the panel
      dot.style.display = 'none';
      if (mobileDot) mobileDot.style.display = 'none';
    }
  });
  // TASK-7a: mobile trigger opens the exact same panel (FIX, self-audit
  // pass). The panel itself is positioned relative to the desktop header
  // (see its CSS above), which sits behind the open mobile menu overlay —
  // so this closes the mobile menu first, via the real closeMenu()
  // (exposed as window.pcCloseMobileMenu by the hamburger's own IIFE
  // above) rather than a second copy of its logic, before opening the
  // panel, so the visitor actually sees it rather than it rendering
  // invisibly underneath the menu.
  if (mobileBtn) {
    mobileBtn.addEventListener('click', function() {
      if (window.pcCloseMobileMenu) window.pcCloseMobileMenu();
      panel.classList.add('open');
      btn.setAttribute('aria-expanded', 'true');
      mobileBtn.setAttribute('aria-expanded', 'true');
      dot.style.display = 'none';
      if (mobileDot) mobileDot.style.display = 'none';
    });
  }
  document.addEventListener('click', function(e) {
    if (!btn.contains(e.target) && !panel.contains(e.target) && !(mobileBtn && mobileBtn.contains(e.target))) {
      panel.classList.remove('open');
      btn.setAttribute('aria-expanded', 'false');
      if (mobileBtn) mobileBtn.setAttribute('aria-expanded', 'false');
    }
  });
})();
</script>
<!-- TASK-7a END -->
```
<!-- TASK-7a END -->

---

### `pet-detail-v1.html` → live path `pets/detail/index.html`

<!-- TASK-7a START -->
**Old code:**
```css
.pc-hero-adopted-badge {
  position: absolute;
  top: 16px;
  left: 0;
  background: var(--navy, #10214b);
  color: #fff;
  font-family: 'Sora', sans-serif;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  padding: 8px 18px;
  border-radius: 0 4px 4px 0;
  box-shadow: 0 2px 8px rgba(0,0,0,0.25);
  z-index: 2;
  pointer-events: none;
}

.pc-gallery-thumbs {
```
**New code:**
```css
.pc-hero-adopted-badge {
  position: absolute;
  top: 16px;
  left: 0;
  background: var(--navy, #10214b);
  color: #fff;
  font-family: 'Sora', sans-serif;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  padding: 8px 18px;
  border-radius: 0 4px 4px 0;
  box-shadow: 0 2px 8px rgba(0,0,0,0.25);
  z-index: 2;
  pointer-events: none;
}

/* TASK-7a START — Hero "Matched" badge. Exact copy of the Adopted hero
   badge shell directly above, gold background to match the card-grid
   Matched badge in cats.html (and to stay visually distinct from the navy
   Adopted badge). Driven by the same pet.status field, value 'Matched'. */
.pc-hero-matched-badge {
  position: absolute;
  top: 16px;
  left: 0;
  background: #d7bd88;
  color: var(--navy, #10214b);
  font-family: 'Sora', sans-serif;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  padding: 8px 18px;
  border-radius: 0 4px 4px 0;
  box-shadow: 0 2px 8px rgba(0,0,0,0.25);
  z-index: 2;
  pointer-events: none;
}
/* TASK-7a END */

.pc-gallery-thumbs {
```
<!-- TASK-7a END -->

<!-- TASK-7a START -->
**Old code:**
```css
.pc-rec-card:hover .pc-rec-card__img { transform: scale(1.05); }
.pc-rec-card__adopted-badge {
  position: absolute; top: 10px; left: 0;
  background: var(--navy); color: #fff;
  font-family: 'Sora', sans-serif; font-size: 10px; font-weight: 700;
  letter-spacing: 0.08em; text-transform: uppercase;
  padding: 4px 12px; border-radius: 0 4px 4px 0;
}
.pc-rec-card__body { padding: 14px 16px 16px; flex: 1; display: flex; flex-direction: column; }
```
**New code:**
```css
.pc-rec-card:hover .pc-rec-card__img { transform: scale(1.05); }
.pc-rec-card__adopted-badge {
  position: absolute; top: 10px; left: 0;
  background: var(--navy); color: #fff;
  font-family: 'Sora', sans-serif; font-size: 10px; font-weight: 700;
  letter-spacing: 0.08em; text-transform: uppercase;
  padding: 4px 12px; border-radius: 0 4px 4px 0;
}
/* TASK-7a START — rec-card Matched badge, same shell as the Adopted one
   directly above, gold background per the same convention used on the
   card-grid and hero badges. */
.pc-rec-card__matched-badge {
  position: absolute; top: 10px; left: 0;
  background: #d7bd88; color: var(--navy);
  font-family: 'Sora', sans-serif; font-size: 10px; font-weight: 700;
  letter-spacing: 0.08em; text-transform: uppercase;
  padding: 4px 12px; border-radius: 0 4px 4px 0;
}
/* TASK-7a END */
.pc-rec-card__body { padding: 14px 16px 16px; flex: 1; display: flex; flex-direction: column; }
```
<!-- TASK-7a END -->

<!-- TASK-7a FIX (self-audit pass, pre-existing bug outside this task's original scope — see "Bugs Found Outside Task Scope" item 3) -->
**Old code:**
```css
.pc-footer__inner { max-width: 1200px; margin: 0 auto; }
.pc-footer__top {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1.5fr;
  gap: 40px;
```
**New code:**
```css
.pc-footer__inner { max-width: 1200px; margin: 0 auto; }
.pc-footer__top {
  display: grid;
  /* TASK-7a FIX (self-audit pass): was `2fr 1fr 1fr 1.5fr` — only 4 track
     values against 5 real grid children (.pc-footer__brand + 4
     .pc-footer__col: Our Pets, Information, Support, Visit Us). This is
     the exact recurring bug named in adoption-website-project-summary.md
     Section 8 ("always verify the column count in the grid CSS matches
     the number of .pc-footer__col divs in the HTML") — pre-existing, not
     introduced by this task, but caught and fixed while already editing
     this file's hero/badge area above. Matches Section 2.6's canonical
     `2fr 1fr 1.2fr 1fr 1.3fr` track list. */
  grid-template-columns: 2fr 1fr 1.2fr 1fr 1.3fr;
  gap: 40px;
```
<!-- TASK-7a END -->

<!-- TASK-7a START -->
**Old code:**
```html
            <div id="heroAdoptedBadge" class="pc-hero-adopted-badge" style="display:none;">ADOPTED</div>
            <img id="galleryMain" src="/assets/images/pet-placeholder.jpg" alt="Pet photo" onerror="this.src='/assets/images/pet-placeholder.jpg'">
          </div>
```
**New code:**
```html
            <div id="heroAdoptedBadge" class="pc-hero-adopted-badge" style="display:none;">ADOPTED</div>
            <!-- TASK-7a START — Hero matched badge, same static-element
                 approach as heroAdoptedBadge above. Shown by
                 showMatchedStatus() when pet.status === 'Matched'. Unlike
                 Adopted, a Matched pet is only earmarked for the visitor
                 whose Pet Match Request it fulfilled — it hasn't completed
                 adoption yet, so this only shows the badge and leaves the
                 rest of the page (Adopt button, application form) fully
                 functional rather than retiring the listing. -->
            <div id="heroMatchedBadge" class="pc-hero-matched-badge" style="display:none;">MATCHED</div>
            <!-- TASK-7a END -->
            <img id="galleryMain" src="/assets/images/pet-placeholder.jpg" alt="Pet photo" onerror="this.src='/assets/images/pet-placeholder.jpg'">
          </div>
```
<!-- TASK-7a END -->

<!-- TASK-7a START -->
**Old code:**
```javascript
        // Handle adopted status
        if (pet.status === 'Adopted') showAdoptedStatus(category);
      }
```
**New code:**
```javascript
        // Handle adopted status
        if (pet.status === 'Adopted') showAdoptedStatus(category);
        // TASK-7a START — Handle matched status (independent of Adopted;
        // a pet can be Matched without yet being Adopted, since matching
        // just earmarks it for the requester who submitted the Pet Match
        // Request that led to it — adoption still has to be completed).
        if (pet.status === 'Matched') showMatchedStatus();
        // TASK-7a END
      }
```
<!-- TASK-7a END -->

<!-- TASK-7a START -->
**Old code:**
```javascript
    var heroBadge = document.getElementById('heroAdoptedBadge');
    if (heroBadge) heroBadge.style.display = 'block';
    // Set browse link
    var morePetsLink = document.getElementById('morePetsLink');
    if (morePetsLink && category) morePetsLink.href = '/pets/' + category + '/';
  }

  // "You Might Also Like" similarity scoring — modeled on how e-commerce
```
**New code:**
```javascript
    var heroBadge = document.getElementById('heroAdoptedBadge');
    if (heroBadge) heroBadge.style.display = 'block';
    // Set browse link
    var morePetsLink = document.getElementById('morePetsLink');
    if (morePetsLink && category) morePetsLink.href = '/pets/' + category + '/';
  }

  // TASK-7a START — showMatchedStatus(): deliberately narrower than
  // showAdoptedStatus() above. A Matched pet has been earmarked for the
  // visitor whose Pet Match Request it fulfilled, but adoption itself
  // hasn't completed — so this only reveals the hero badge and leaves the
  // info panel, Adopt button, and application form exactly as they are for
  // any other available pet. No page-level "no longer available" treatment.
  function showMatchedStatus() {
    var heroBadge = document.getElementById('heroMatchedBadge');
    if (heroBadge) heroBadge.style.display = 'block';
  }
  // TASK-7a END

  // "You Might Also Like" similarity scoring — modeled on how e-commerce
```
<!-- TASK-7a END -->

<!-- TASK-7a START -->
**Old code:**
```javascript
            var badge = p.status === 'Adopted' ? '<div class="pc-rec-card__adopted-badge">ADOPTED</div>' : '';
            // TASK-2 START — Posted-on date, rendered only when the pet record has one
            var postedText = formatPostedDate(p.published_date);
            var postedRow = postedText ? '<div class="pc-rec-card__row">' + iPosted + 'Posted ' + postedText + '</div>' : '';
            // TASK-2 END
            return '<a href="' + url + '" class="pc-rec-card"><div class="pc-rec-card__img-wrap"><img src="' + img + '" alt="' + name + '" class="pc-rec-card__img" loading="lazy">' + badge + '</div><div class="pc-rec-card__body"><div class="pc-rec-card__name">' + name + '</div><div class="pc-rec-card__details"><div class="pc-rec-card__row">' + iBreed + (p.breed || '—') + '</div><div class="pc-rec-card__row">' + iAge   + age + '</div><div class="pc-rec-card__row">' + iSize  + (p.size || '—') + '</div>' + postedRow + '</div><div class="pc-rec-card__fee-row">' + iFee + '<span class="pc-rec-card__fee">' + fee + '</span></div><span class="pc-rec-card__diary">Read ' + name + '\'s diary!</span></div></a>';
          }).join('');
```
**New code:**
```javascript
            var badge = p.status === 'Adopted' ? '<div class="pc-rec-card__adopted-badge">ADOPTED</div>' : '';
            // TASK-7a START — rec-card Matched badge, same pattern as the
            // Adopted badge directly above.
            var matchedBadge = p.status === 'Matched' ? '<div class="pc-rec-card__matched-badge">MATCHED</div>' : '';
            // TASK-7a END
            // TASK-2 START — Posted-on date, rendered only when the pet record has one
            var postedText = formatPostedDate(p.published_date);
            var postedRow = postedText ? '<div class="pc-rec-card__row">' + iPosted + 'Posted ' + postedText + '</div>' : '';
            // TASK-2 END
            return '<a href="' + url + '" class="pc-rec-card"><div class="pc-rec-card__img-wrap"><img src="' + img + '" alt="' + name + '" class="pc-rec-card__img" loading="lazy">' + badge + matchedBadge + '</div><div class="pc-rec-card__body"><div class="pc-rec-card__name">' + name + '</div><div class="pc-rec-card__details"><div class="pc-rec-card__row">' + iBreed + (p.breed || '—') + '</div><div class="pc-rec-card__row">' + iAge   + age + '</div><div class="pc-rec-card__row">' + iSize  + (p.size || '—') + '</div>' + postedRow + '</div><div class="pc-rec-card__fee-row">' + iFee + '<span class="pc-rec-card__fee">' + fee + '</span></div><span class="pc-rec-card__diary">Read ' + name + '\'s diary!</span></div></a>';
          }).join('');
```
<!-- TASK-7a END -->

---

### `admin-dashboard.html` → live path `admin/index.html`

<!-- TASK-7a START -->
**Old code:**
```html
    <label class="pc-form-label" style="margin-bottom:6px;">Matched Pet <span style="font-weight:400;text-transform:none;">(optional)</span></label>
    <input type="text" id="pmFulfillPetNote" class="pc-form-input" placeholder="e.g. Bella, Golden Retriever (pet ID PC-xxxxx)" style="margin-bottom:20px;">
    <div style="display:flex;gap:10px;">
```
**New code:**
```html
    <label class="pc-form-label" style="margin-bottom:6px;">Matched Pet <span style="font-weight:400;text-transform:none;">(optional)</span></label>
    <input type="text" id="pmFulfillPetNote" class="pc-form-input" placeholder="e.g. Bella, Golden Retriever (pet ID PC-xxxxx)" style="margin-bottom:14px;">
    <!-- TASK-7a START — Optional structured pet-ID link, added alongside
         the existing free-text note above rather than replacing it. The
         free-text note stays for the common case this modal's own design
         comment already calls out ("a visitor asking to be matched with a
         pet that doesn't exist in inventory yet") — there's often no real
         pet.json record to link to. This field is for the case where a
         real listed pet DOES exist: entering its ID here lets the public
         site (cats.html and the other category pages, plus
         pet-detail-v1.html) show a real "Matched" badge on that pet and
         let check-pet-match-status.php return a working link, instead of
         only the free-text note. Reuses the same category already
         selected for this request (pmCategorySelect) rather than adding a
         second category picker, since a request's matched pet is always
         within its own category. -->
    <label class="pc-form-label" style="margin-bottom:6px;">Matched Pet ID <span style="font-weight:400;text-transform:none;">(optional — links to a real pet listing)</span></label>
    <input type="text" id="pmFulfillPetId" class="pc-form-input" placeholder="e.g. PC-72939-89-MC" style="margin-bottom:20px;">
    <!-- TASK-7a END -->
    <div style="display:flex;gap:10px;">
```
<!-- TASK-7a END -->

<!-- TASK-7a START -->
**Old code:**
```javascript
      ${reqItem.fulfilled_pet_note ? `<div style="font-size:13px;color:#2c2416;margin-top:8px;">Matched pet: ${PC.esc(reqItem.fulfilled_pet_note)}</div>` : ''}
    </div>` : ''}
```
**New code:**
```javascript
      ${reqItem.fulfilled_pet_note ? `<div style="font-size:13px;color:#2c2416;margin-top:8px;">Matched pet: ${PC.esc(reqItem.fulfilled_pet_note)}</div>` : ''}
      <!-- TASK-7a START -->
      ${reqItem.matched_pet_id ? `<div style="font-size:13px;color:#2c2416;margin-top:4px;">Matched pet ID: <strong>${PC.esc(reqItem.matched_pet_id)}</strong></div>` : ''}
      <!-- TASK-7a END -->
    </div>` : ''}
```
<!-- TASK-7a END -->

<!-- TASK-7a START -->
**Old code:**
```javascript
  document.getElementById('pmFulfillByName').value = '';
  document.getElementById('pmFulfillByPosition').value = '';
  document.getElementById('pmFulfillPetNote').value = '';
  document.getElementById('pmFulfillModal').style.display = 'flex';
```
**New code:**
```javascript
  document.getElementById('pmFulfillByName').value = '';
  document.getElementById('pmFulfillByPosition').value = '';
  document.getElementById('pmFulfillPetNote').value = '';
  // TASK-7a START
  document.getElementById('pmFulfillPetId').value = '';
  // TASK-7a END
  document.getElementById('pmFulfillModal').style.display = 'flex';
```
<!-- TASK-7a END -->

<!-- TASK-7a START -->
**Old code:**
```javascript
  const fulfilledBy = document.getElementById('pmFulfillByName').value.trim();
  const fulfilledByPosition = document.getElementById('pmFulfillByPosition').value.trim();
  const petNote = document.getElementById('pmFulfillPetNote').value.trim();
  try {
```
**New code:**
```javascript
  const fulfilledBy = document.getElementById('pmFulfillByName').value.trim();
  const fulfilledByPosition = document.getElementById('pmFulfillByPosition').value.trim();
  const petNote = document.getElementById('pmFulfillPetNote').value.trim();
  // TASK-7a START — optional structured pet ID, sent alongside the
  // existing free-text note. Left blank whenever staff don't have (or
  // don't need) a real listed pet record to point to.
  const petId = document.getElementById('pmFulfillPetId').value.trim();
  // TASK-7a END
  try {
```
<!-- TASK-7a END -->

<!-- TASK-7a START -->
**Old code:**
```javascript
    fd.append('fulfilled_by', fulfilledBy);
    fd.append('fulfilled_by_position', fulfilledByPosition);
    fd.append('fulfilled_pet_note', petNote);
    window.dlog(`Fulfill Pet Match Request: code=${code}`, 'info');
```
**New code:**
```javascript
    fd.append('fulfilled_by', fulfilledBy);
    fd.append('fulfilled_by_position', fulfilledByPosition);
    fd.append('fulfilled_pet_note', petNote);
    // TASK-7a START
    fd.append('matched_pet_id', petId);
    // TASK-7a END
    window.dlog(`Fulfill Pet Match Request: code=${code}`, 'info');
```
<!-- TASK-7a END -->

<!-- TASK-7a START -->
**Old code:**
```javascript
    const json = await res.json();
    if (json.success) {
      PC.toast('Marked as fulfilled');
      window.dlog(`Fulfill Pet Match Request: success — code=${code}`, 'ok');
```
**New code:**
```javascript
    const json = await res.json();
    if (json.success) {
      PC.toast('Marked as fulfilled');
      // TASK-7a START — surfaces update-pet-match-request.php's
      // non-blocking warning (e.g. a typo'd Matched Pet ID) without
      // treating the action itself as failed, since it wasn't.
      if (json.warning) PC.toast(json.warning, 'error');
      // TASK-7a END
      window.dlog(`Fulfill Pet Match Request: success — code=${code}`, 'ok');
```
<!-- TASK-7a END -->

---

### `update-pet-match-request.php` → live path `admin/update-pet-match-request.php`

<!-- TASK-7a START -->
**Old code:**
```php
if ($status === 'FULFILLED') {
    $data['fulfilled_by'] = trim($_POST['fulfilled_by'] ?? '');
    $data['fulfilled_by_position'] = trim($_POST['fulfilled_by_position'] ?? '');
    $data['fulfilled_pet_note'] = trim($_POST['fulfilled_pet_note'] ?? '');
    $data['fulfilled_at'] = date('c');
} elseif ($status === 'CLOSED') {
```
**New code:**
```php
if ($status === 'FULFILLED') {
    $data['fulfilled_by'] = trim($_POST['fulfilled_by'] ?? '');
    $data['fulfilled_by_position'] = trim($_POST['fulfilled_by_position'] ?? '');
    $data['fulfilled_pet_note'] = trim($_POST['fulfilled_pet_note'] ?? '');
    $data['fulfilled_at'] = date('c');
    // TASK-7a START — Optional structured link to a real pet.json record,
    // added alongside the existing free-text fulfilled_pet_note rather
    // than replacing it (there's often no listed pet yet to link to, per
    // this file's own original design comment). Left blank/unset if staff
    // don't supply one, so nothing downstream (check-pet-match-status.php,
    // the "Matched" badge on cats.html/pet-detail-v1.html) breaks when
    // there's no real pet to point to. When supplied, it's validated
    // against this same request's own category (a request's matched pet
    // is always within its own category — no separate category input was
    // added for this field) before being trusted; an ID that doesn't
    // resolve to a real pet.json is silently NOT stored, rather than
    // stored anyway and left to break a public page later, since a
    // fulfilled request should still save successfully either way.
    $matchedPetId = trim($_POST['matched_pet_id'] ?? '');
    $matchedPetIdNotFound = false;
    if ($matchedPetId !== '') {
        // TASK-7a FIX (hand-in review pass): was __DIR__ . '/pets/' — this
        // file lives at admin/update-pet-match-request.php (confirmed via
        // ADMIN_DASHBOARD_PROJECT_SUMMARY.md's own File Structure on Server
        // tree, which places .env and update-adoption.php directly in
        // /admin/), and real pet data lives at /adoptions/pets/{category}/,
        // a sibling of /admin/ — NOT a nested /admin/pets/ folder, which
        // doesn't exist. The uncorrected path would have silently matched
        // zero pets, every time, regardless of what staff typed. See
        // "Bugs Found Outside Task Scope" item 1 for the full account,
        // including the related, NOT-fixed-here pre-existing bug on this
        // same file's $requestFile path a few lines above this block
        // (item 2 — that one is Task 6b's own code, outside this task's
        // edit).
        $matchedPetFile = __DIR__ . '/../adoptions/pets/' . $category . '/' . $matchedPetId . '/pet.json';
        if (is_file($matchedPetFile)) {
            $data['matched_pet_id'] = $matchedPetId;
        } else {
            $matchedPetIdNotFound = true;
        }
        // If it doesn't resolve, matched_pet_id is simply left unset on
        // $data — the free-text fulfilled_pet_note above still saves
        // normally either way, so a typo'd pet ID never blocks the
        // action itself. $matchedPetIdNotFound surfaces a warning in the
        // response instead (see below), so staff know to double-check it
        // rather than assuming it silently worked.
    }
    // TASK-7a END
} elseif ($status === 'CLOSED') {
```
<!-- TASK-7a END -->

<!-- TASK-7a START -->
**Old code:**
```php
echo json_encode(['success' => true]);
```
**New code:**
```php
// TASK-7a: success stays true even when matched_pet_id didn't resolve —
// the request itself saved fine either way — but a warning is included so
// the admin dashboard can flag it rather than silently drop the mismatch.
$response = ['success' => true];
if (!empty($matchedPetIdNotFound)) {
    $response['warning'] = 'Matched Pet ID was not found and was not saved. The request was still marked fulfilled.';
}
echo json_encode($response);
```
<!-- TASK-7a END -->

## New Files

- `check-pet-match-status.php` → live path `adoptions/check-pet-match-status.php` — **New file, in `Project-5-Task-7a.zip`** at that exact path. Public, unauthenticated endpoint the notification-icon stub calls to check whether any of a visitor's Pet Match Requests have been fulfilled with a linked pet; returns a real pet name/link when `matched_pet_id` resolves, otherwise falls back to the admin's free-text note. See the "⚠️ New-folder-path flag" note under Files Changed above for why this location (not the site root, not `/admin/`) was chosen, and "Bugs Found Outside Task Scope" item 1 for a path bug this relocation surfaced and fixed in the same pass.

## Self-Check Confirmation

Every code block above is wrapped in `<!-- TASK-7a START -->` / `<!-- TASK-7a END -->` (or the `//`/`/* */` equivalent for JS/CSS blocks nested inside an already-anchored HTML section), including every `TASK-7a FIX` block from this review pass. Every "Old code" snippet was copied character-for-character from the original file as supplied (`/mnt/user-data/uploads/pet-match.html`, `/mnt/user-data/uploads/cats.html`, `/mnt/user-data/uploads/pet-detail-v1.html`, `/mnt/user-data/uploads/admin-dashboard.html`, `/mnt/user-data/uploads/update-pet-match-request.php`), and each was confirmed to appear exactly once in that file via direct search before being used as an anchor. Only the five changed files listed in Files Changed were touched this task, across all of its revisions, plus the one new file. The new file sits inside `Project-5-Task-7a.zip` together with this completion file itself, at its confirmed live path with no unnecessary nesting, and contains no secrets, credentials, `.env` content, or data from `applications/`/`uploads/` — confirmed by direct inspection before packaging. Zip name (`Project-5-Task-7a.zip`) matches the casing/format already used by Task 6a's and Task 6b's own hand-ins for this same project.

Items fixed this pass that go beyond a literal "only touch files explicitly in my task" reading, every one disclosed above rather than folded in silently: the footer grid-column bug (Bugs item 3 — a deliberate, flagged exception on a trivial CSS value); the mobile-menu gap and panel-positioning bug in this task's own notification stub (Bugs item 4 — not a Scope Lock exception at all, since it's completing this task's own incomplete original delivery, caught by actually rendering with Playwright rather than syntax-checking alone); and this task's own `matched_pet_id` path bug across two files, caught only while re-verifying the new-folder-path flagging this hand-in format requires (Bugs item 1). One related bug was found but deliberately **not** fixed, since it belongs to Task 6b's own pre-existing, untouched code rather than this task's: logged in full, with the exact line and exact fix identified, in Bugs item 2.
