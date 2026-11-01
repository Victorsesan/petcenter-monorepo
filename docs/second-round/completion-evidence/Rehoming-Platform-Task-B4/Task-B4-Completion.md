# Task-B4-Completion.md

## Task Number
B4 — Badge & reputation display

## What Was Required
Show merchant reputation at two levels of detail, without recomputing anything B2/B3/D1 own.

- **Merchant profile page** (written into B6's `<!-- MERCHANT-PROFILE: badges -->` and `<!-- MERCHANT-PROFILE: impact-stats -->` sections): a row of small icon+label chips — precedence Verified/Charity, then activity tier, then Takeback Commitment last — plus the impact line "X pets rehomed · Y active listings · avg Z days to home", plus a plain-language response-rate label (never a number or percentage).
- **`pet-detail.html`**: only a reduced inline version beside the merchant's name — activity tier + Verified, not the full row.
- **Response-rate computation** (inbound message → first outbound merchant reply, rolling average), built against a labeled best-guess message shape (no real merchant-enquiry message store exists yet — see the Fifth-round note folded into Self-Check below), reconciled at whichever task's integration checkpoint actually owns that data model.

B6 had no owning task when B4 started; per the VDM, B4 builds against B6's published section names using a local verification stub (not a live file — see "New files") and reconciles into the real page once B6's completion file exists.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `pet-detail.html` | `pets/detail/index.html` | Changed | Yes — shared file (anchor comments required). Pre-existing `TASK-2` block (posted-on date) untouched. |
| `merchant-response-stats.php` | `rehoming/merchant-response-stats.php` *(see note below)* | New file — in the zip | Yes — called by Task B3's `badge-calculation-cron.php` (B3's worker wires the call; see Bugs/handoff notes). |
| `merchant-response-stats-v1.js` | `rehoming/merchant-response-stats-v1.js` *(see note below)* | New file — in the zip | No — JavaScript reference implementation/test tooling, not served to any page. |
| `merchant-response-stats.fixtures.json` | `rehoming/merchant-response-stats.fixtures.json` *(see note below)* | New file — in the zip | No — shared test fixtures so the JS reference and the PHP production file can't drift apart. |
| `merchant-response-stats.test-v1.js` | `rehoming/merchant-response-stats.test-v1.js` *(see note below)* | New file — in the zip | No — test runner (Node). |
| `merchant-response-stats.test.php` | `rehoming/merchant-response-stats.test.php` *(see note below)* | New file — in the zip | No — test runner (PHP CLI only). |
| `merchant-profile-b4-stub-v1.html` | **Not a live path — excluded from the zip** | New file — **NOT in the zip** | No. This is a throwaway verification page built only so this task had something to screenshot (explicitly not Task B6's real merchant profile page). It was never meant to be deployed, so including it at any live path would be wrong, not just unused. See "New files" below. |

---
**IMPORTANT — NEW FOLDER NOT FOUND IN EITHER SUMMARY DOCUMENT — CONFIRM BEFORE INTEGRATING**

This hand-in needs a folder called **`rehoming/`** created at the site root (sibling of `apply/`, `adoptions/`, `pets/`, `admin/` — i.e. inside `public_html/adopt/`, next to those). Searched both `adoption-website-project-summary.md` (Section 1, Server Folder Structure) and `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` in full — **neither documents a `rehoming/` folder.** It does not exist yet in either source of truth.

The five PHP/JS/JSON files in this zip's `rehoming/` folder are not HTML pages, so the index.html rule below does not apply to them — they are backend scripts and reference data, served to no browser directly.

Where this folder came from: Task B3's own completion file independently placed its own two files at `rehoming/badge-logic.php` and `rehoming/badge-calculation-cron.php` — also without that path existing in either summary document. This task's `merchant-response-stats.php` must sit in the same folder as B3's cron job to be reachable by a relative `require_once`, so it follows B3's same unconfirmed choice rather than inventing a third option. **Two workers independently guessing the same folder name is not the same as that folder name being confirmed — please have the CEO/integrator decide the real path and add it to Section 1 of the folder-structure document once decided, so the next worker who needs it doesn't have to guess a third time.**

---

**Note on the `rehoming/` path:** `adoption-website-project-summary.md`'s Section 1 (Server Folder Structure) does not document a `rehoming/` folder at all — the Rehoming Platform is new work layered on top of that existing structure. Task B3's own completion file independently placed its files at `rehoming/badge-logic.php` and `rehoming/badge-calculation-cron.php`; `merchant-response-stats.php` is `require_once`-able from that same cron job and must sit in the same folder to be found by a relative path, so the five `merchant-response-stats.*` files here follow B3's folder, not a confirmed entry in the summary document. Per this guide's instruction not to guess silently: **this path is inferred from a sibling task's own (also unconfirmed) choice, not verified against the folder-structure section.** Please confirm `rehoming/` with the CEO/integrator before relying on it.

**Also flagged, not decided by me:** `merchant-response-stats.test-v1.js`, `merchant-response-stats.test.php`, and `merchant-response-stats.fixtures.json` are development/test tooling (a Node test runner, a PHP CLI-only test runner, and their shared fixtures) — nothing on the live site serves or calls them at runtime. Only `merchant-response-stats.php` (required by B3's cron) and `merchant-response-stats-v1.js` (kept as the tested reference the PHP port must match) are load-bearing. Whether the three test/fixture files belong on the production server at all, versus only in whatever repo the integrator keeps outside the live webroot, is the integrator's call — included here rather than omitted, since the guide says every new file goes in the zip, but flagged so it isn't assumed to be a deliberate production-deployment decision.

## Index.html naming — confirmed understood, not applicable to this task's files
Every file this task adds or changes is a backend script, a JS/PHP library file, or reference data (`pet-detail.html` itself is unchanged in name — it already deploys as `pets/detail/index.html` per the existing mapping, nothing about that changed here). **None of this task's new files are HTML pages**, so the "every page is a folder containing `index.html`, never a loose `name.html`" rule has no file in this hand-in that it could apply to. Flagged explicitly so this is a confirmed "doesn't apply," not a missed check.

## Code Changes

### `pet-detail.html`  →  live path `pets/detail/index.html`

#### Change 1 of 4 — CSS (inserted immediately after the existing `.pc-badge--adopted` rule)

<!-- TASK-B4 START -->
**Old code:**
```css
.pc-badge--adopted { background: var(--bg); color: var(--muted); }
```
**New code:**
```css
.pc-badge--adopted { background: var(--bg); color: var(--muted); }

/* <!-- TASK-B4 START --> */
/* ─── TASK B4: Merchant reputation chips (reduced inline version) ───────────
   Icon+label chips shown beside the merchant name in the "Posted by" row.
   Same pill shape/weight as .pc-badge, but not uppercase status pills — these
   sit next to plain text, not in a table cell.
   NAMING NOTE: deliberately `.pc-merchant-chip`, NOT `.pc-chip`. The design
   system already owns `.pc-chip` / `.pc-chip-row` as an interactive, dark,
   multi-select FILTER chip (hover/selected/checkmark states, bottom sheets).
   Reusing those names for static reputation labels would collide with that
   component wherever both ship in one document. Colors use this page's own
   existing tokens (--success, --orange, --muted...) rather than the design
   system's generic example hex values, per the site's established palette.
   Full badge row / impact stats / response-rate styles live with B6's profile
   page sections, not here (this page never renders them). */
.pc-merchant-chip-row { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
.pc-merchant-chip {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 11px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  line-height: 1.4;
  white-space: nowrap;
}
.pc-merchant-chip svg { width: 12px; height: 12px; flex-shrink: 0; }
.pc-merchant-chip--verified         { background: var(--success-bg); color: var(--success); }
.pc-merchant-chip--tier-trusted      { background: #fff8e6; color: #b7791f; }
.pc-merchant-chip--tier-active       { background: var(--orange-light); color: var(--orange); }
.pc-merchant-chip--tier-new-merchant { background: var(--bg); color: var(--muted); }
/* Reduced inline size: smaller than the profile page's full-size chips so the
   listing page stays uncluttered (see B4 spec). */
.pc-merchant-chip--inline { font-size: 11px; padding: 2px 9px; }

/* Posted-by row: name + chips are wider than the narrow side-by-side layout
   allows (the label was wrapping "Posted / by" at 280px). Reuses this file's
   existing stacked-row pattern (see #petKitSpecifyRow): label on its own
   line, value below, left-aligned. Scoped by ID, and only when chips are
   present (class toggled in renderMerchantBadgeInline) so listings without
   merchant data keep the original side-by-side row untouched. */
#petPostedByRow.pc-has-merchant-chips {
  flex-direction: column;
  align-items: flex-start;
}
#petPostedByRow.pc-has-merchant-chips .pc-info-row__val {
  text-align: left;
  margin-top: 6px;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px 10px;
}
/* <!-- TASK-B4 END --> */
```
<!-- TASK-B4 END -->

#### Change 2 of 4 — HTML ("Posted by" row)

<!-- TASK-B4 START -->
**Old code:**
```html
<!-- Posted By — pen/nib icon ✒ -->
            <div class="pc-info-row">
              <span class="pc-info-row__label">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                Posted by
              </span>
              <span class="pc-info-row__val" id="petPostedBy">—</span>
            </div>
```
**New code:**
```html
<!-- Posted By — pen/nib icon ✒ -->
            <div class="pc-info-row" id="petPostedByRow"><!-- TASK-B4: id attribute added for stacked-layout scoping (single attribute change, not a code block) -->
              <span class="pc-info-row__label">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                Posted by
              </span>
              <span class="pc-info-row__val">
                <span id="petPostedBy">—</span>
                <!-- TASK-B4 START -->
                <!-- Reduced inline merchant badge (activity tier + Verified only, per spec —
                     NOT the full badge row, which lives on the merchant profile page).
                     Filled by renderMerchantBadgeInline() from pet.merchant (B2 + B3 fields). -->
                <span class="pc-merchant-chip-row" id="petMerchantBadgeInline"></span>
                <!-- TASK-B4 END -->
              </span>
            </div>
```
<!-- TASK-B4 END -->

#### Change 3 of 4 — JS render function (inserted after the existing `// TASK-2 END` block, before `function ynIcon`)

<!-- TASK-B4 START -->
**Old code:**
```js
  // TASK-2 END

  function ynIcon(val) {
```
**New code:**
```js
  // TASK-2 END

  /* <!-- TASK-B4 START --> */
  // ═══════════════════════════════════════════════════════════════════════════
  // TASK B4 — Reduced inline merchant badge (activity tier + Verified only)
  // This page shows ONLY the reduced version, per the B4 spec — the full badge
  // row, impact stats and response-rate label live on the merchant profile
  // page (B6's `badges` / `impact-stats` sections), not here.
  // Reads the `badges` object that B3's badge-logic.php writes onto the merchant record
  // (recalculateAllBadges() — B3 documents `badges` as "what B4's display layer reads").
  // It deliberately does NOT read raw `verification_status`: that field lives in profile.json,
  // which B3 blocks from direct browser access (it also holds charity registration numbers).
  //   merchant = { badges: { verified: bool, activity_tier: 'new_merchant'|'active'|'trusted',
  //                          charity: bool, takeback_commitment: bool } }
  // This reduced version uses only `verified` and `activity_tier`. Reconcile at integration
  // if B3 renames a field.
  // ═══════════════════════════════════════════════════════════════════════════
  var MERCHANT_BADGE_ICONS = {
    shield: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
    trophy: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4z"/><path d="M17 5h3a2 2 0 0 1-2 4M7 5H4a2 2 0 0 0 2 4"/></svg>',
    sprout: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/></svg>'
  };
  var ACTIVITY_TIER_LABEL = { new_merchant: 'New Merchant', active: 'Active', trusted: 'Trusted' };
  var ACTIVITY_TIER_ICON  = { new_merchant: 'sprout', active: 'shield', trusted: 'trophy' };

  // Renders the reduced chips into `containerId` and flags the "Posted by" row
  // so CSS can switch it to the stacked layout only when chips are present.
  // Order follows the spec's precedence: Verified first, then activity tier.
  function renderMerchantBadgeInline(merchant, containerId) {
    var el = document.getElementById(containerId);
    if (!el) return;
    var badges = merchant && merchant.badges;
    var html = '';
    if (badges) {
      if (badges.verified === true) {
        html += '<span class="pc-merchant-chip pc-merchant-chip--inline pc-merchant-chip--verified">' +
                MERCHANT_BADGE_ICONS.shield + 'Verified</span>';
      }
      // Whitelisted lookup: an unknown tier value renders nothing (never injected as HTML).
      if (badges.activity_tier && ACTIVITY_TIER_LABEL[badges.activity_tier]) {
        html += '<span class="pc-merchant-chip pc-merchant-chip--inline pc-merchant-chip--tier-' +
                badges.activity_tier.replace('_', '-') + '">' +
                MERCHANT_BADGE_ICONS[ACTIVITY_TIER_ICON[badges.activity_tier]] +
                ACTIVITY_TIER_LABEL[badges.activity_tier] + '</span>';
      }
    }
    el.innerHTML = html;
    var rowEl = document.getElementById('petPostedByRow');
    if (rowEl) rowEl.classList.toggle('pc-has-merchant-chips', html !== '');
  }
  /* <!-- TASK-B4 END --> */

  function ynIcon(val) {
```
<!-- TASK-B4 END -->

#### Change 4 of 4 — JS call site (inside `populatePetInfo`, immediately after `petPostedBy` is set)

<!-- TASK-B4 START -->
**Old code:**
```js
    var postedEl = document.getElementById('petPostedBy');
    if (postedEl) postedEl.textContent = pet.posted_by || '—';
```
**New code:**
```js
    var postedEl = document.getElementById('petPostedBy');
    if (postedEl) postedEl.textContent = pet.posted_by || '—';

    /* <!-- TASK-B4 START --> */
    // Inline reduced merchant badge (activity tier + Verified only)
    // PLACEHOLDER SOURCE: per the resolved contract, a listing stores only `merchant_id`, and the
    // merchant's CURRENT badges come from B3's merchant record at render time (never copied into
    // pet.json). B3's lookup does not exist yet, so `pet.merchant` stands in. At B3's integration
    // checkpoint, replace `pet.merchant` with the record fetched for `pet.merchant_id`.
    renderMerchantBadgeInline(pet.merchant || null, 'petMerchantBadgeInline');
    /* <!-- TASK-B4 END --> */
```
<!-- TASK-B4 END -->

## New files

- `rehoming/merchant-response-stats.php` *(path unconfirmed — see note above)* — New file — in the zip. Production PHP computation of a merchant's response rate (fraction 0.0–1.0, matching B3's threshold unit) and speed tier, for B3's daily cron to call.
- `rehoming/merchant-response-stats-v1.js` *(path unconfirmed — see note above)* — New file — in the zip. The tested JavaScript reference the PHP file is a line-for-line port of.
- `rehoming/merchant-response-stats.fixtures.json` *(path unconfirmed — see note above)* — New file — in the zip. 14 shared test cases both implementations are checked against.
- `rehoming/merchant-response-stats.test-v1.js` *(path unconfirmed — see note above)* — New file — in the zip. Node test runner (17 tests).
- `rehoming/merchant-response-stats.test.php` *(path unconfirmed — see note above)* — New file — in the zip. PHP CLI test runner against the same fixtures (not yet executed — no PHP available in this sandbox; see Self-Check).
- `merchant-profile-b4-stub-v1.html` — **New file, but NOT in the zip and has no live path.** Throwaway verification page only (badge row + impact stats rendered against B6's named sections, for the required completion-file screenshots). Deliberately excluded — this one was never meant to reach the live site at all, unlike every other file in this zip, which does have a real (if unconfirmed) live destination. Available on request if the CEO wants to inspect it, but it is not part of this hand-in.
- `Task-B4-Completion.md` (this file) — included inside `Rehoming-Platform-Task-B4.zip` itself at the zip's root, per the updated instruction to join the completion file and all new files into one zip. Note for the integrator: this file is documentation, not a site asset — if the zip is unzipped directly on top of the live site folder, this file will land at the site root alongside it and should be moved aside, not left as a live-served file.

## Bugs Found Outside Task Scope
None found in `pet-detail.html` or any other existing file. Everything below is a scope boundary or integration dependency in work this task does not own — never fixed myself, per Scope Lock:

1. **No lookup exists yet for a listing's current merchant badges.** `pet-detail.html`'s call site currently reads `pet.merchant` as a placeholder (see the comment in Change 4 above). Per the VDM's resolved answer, the real source is Task B3's merchant record, keyed by a `merchant_id` the listing stores — that lookup/endpoint doesn't exist as a file yet. Not fixed here; B3's file zone.
2. **B3's cron job does not yet call `merchant-response-stats.php`.** Wiring that call, and persisting `response_rate` / a new `response_tier` field onto the merchant record, is B3's file zone (`badge-logic.php` / `badge-calculation-cron.php`), not this task's. See the handoff note under "New files" / Self-Check.
3. **No real merchant-enquiry message store exists yet to compute response rate from.** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s `chat_messages` table (general visitor↔admin support chat) is a different, existing system — not this one. The actual merchant↔visitor enquiry messaging is Task D1's ("Messaging a merchant (Task D1's enquiry flow)"), not yet shipped. `merchant-response-stats.php`'s input shape is a labeled guess, not sourced from a real file — reconcile once D1 (or, per B4's own task text, Task C5 — the two disagree on which task owns this reconciliation; flagged for the CEO/VDM to confirm, not decided here) ships a real structure.
4. **B6's named sections have no slot for the response-rate label.** Placed inside `<!-- MERCHANT-PROFILE: impact-stats -->` as a second line — confirmed acceptable by the VDM in an earlier round of this task.

## Self-Check Confirmation
- Only `pet-detail.html` (an existing file) was changed; everything else listed is a new file. Confirmed by `diff` against the original `pet-detail.html`: exactly 5 hunks, 2 original lines altered (the two shown in Change 2 above) — nothing else in the file differs.
- Every old-code snippet above was copied character for character from the **original** `pet-detail.html` (the file as given to this task, before any of this task's edits) and confirmed by direct search to occur **exactly once** in that original file (verified programmatically, not by inspection alone).
- Every changed/added block carries `<!-- TASK-B4 START -->` / `<!-- TASK-B4 END -->` anchors (adapted to `/* ... */` where the surrounding language is CSS inside a `<style>` block — HTML-comment tokens are valid there as a CSS parser no-op, confirmed by rendering the file and checking the chip styles still applied).
- Every file in `Rehoming-Platform-Task-B4.zip` is listed above with the same path, and every "New file" above is in the zip, **except** `merchant-profile-b4-stub-v1.html` (deliberately excluded, explained above) — the one intentional exception, called out rather than silently mismatched. This completion file itself is also inside the zip, at its root, per the updated packaging instruction.
- Zip is named `Rehoming-Platform-Task-B4.zip` — project name taken exactly from the task-assignment source file's own name (`Rehoming-Platform-Task-Assignments.md`), task number `B4` exactly as assigned, matching the `[Project-Name][Task-Number].zip` pattern as instructed.
- No secrets, `config.php`, `pet.json`, `.env`, or anything under `applications/`/`uploads/` is in the zip — confirmed by listing the zip contents and checking each of the five file names and their content by hand; none reference a database credential, API key, or token value (the one credential-adjacent piece of logic, the admin-token check added by B3, lives in B3's own `badge-logic.php`, not in any file submitted here).
- No dropdown menus in this task.
- No emoji anywhere — SVG icons only.
- Response-rate computation: 17/17 JavaScript tests pass (`node merchant-response-stats.test-v1.js`, also re-run under non-UTC timezones with identical results). The PHP port (`merchant-response-stats.php` / `.test.php`) could **not** be executed — no PHP interpreter is available in this sandbox (the same limitation Task B3's worker reported). It was checked for balanced brackets/parens and that its logic is a line-for-line match of the tested JS version, but this is not a substitute for actually running `php merchant-response-stats.test.php`, which the CEO or integrator should do once before this is relied on in production.
- Chip rendering verified in a real browser (Playwright/Chromium) against the real `pet-detail.html`, including B3's actual delivered `profile.json` example as input: shows Verified + Trusted for a verified/trusted merchant, New Merchant only for an unverified new merchant, no chip for an unrecognized/malicious tier value (whitelisted lookup, nothing injected), and no visible change at all for a listing with no merchant data. Checked at 360px and 1100px — no horizontal overflow, label stays on one line.
- This hand-in format itself (four-column table, per-block anchors, zip) was built directly from `WORKER-DELIVERY-GUIDE.md`, read in full for this update; no git/branch/PR instructions from any other document were followed.
