# Task F7 — Completion File

## Task Number

**TASK F7 — Currency & locale routing** (Phase F — Discovery, Engagement & Growth), as assigned in `Rehoming-Platform-Task-Assignments.md`.

## What Was Required

Build the currency-code display logic for rehoming-platform listings (USD/CAD/GBP/AUD, per Task A1's shared `currency_code` listing-schema contract), and apply the finalized locale/URL routing pattern (`/en-us/`, `/en-ca/`, `/fr-ca/`, `/en-gb/`, `/en-au/`) to new rehoming-platform pages as they're built. Translation content/mechanism is explicitly out of scope (owned elsewhere). Delivered as a standalone, reusable script (`currency-locale.js`) plus the real wiring of that script into the three existing fee-display spots on the live pet-detail page, since no rehoming-platform pages existed when this task started.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `currency-locale.js` | `assets/js/currency-locale.js` ⚠️ **NEW FOLDER PATH — see flag below** | **New** — in the zip | No — new file, not yet used by anyone else. Other tasks that build against the same `currency_code` contract (A3, F4, F5, G1 — see Shared Contracts) may adopt it later; that's not yet reflected anywhere. |
| `currency-locale-demo.html` | *(not a live site file — see note below)* | New — **in the zip**, but under `_supporting-files/`, not under a live path | No |
| `pet-detail-v2.html` (= `pet-detail.html`) | `pets/detail/index.html` | **Changed** — 4 blocks, see Code Changes | Yes — shared file. Also carries Task A1's `TASK-A1`-anchored `pcA1Sections` block (per `Task-A1-Completion.md` — **not present** in the copy I was given/edited, see Bugs Found Outside Task Scope) and an earlier Task 2's `TASK-2`-anchored changes (Posted-on date work), which are present and untouched by this task. |
| `pet-posting-form-v2.html` | **Unconfirmed** — not in the Source → Live Path table (it's a new file created by Task A1, not a pre-existing adoption-site page), and A1's own completion file doesn't state a live path for it either. Not guessing silently per the guide's instruction. | **Not modified** | N/A — reviewed only, to resolve a scope question (see What Was Required / Bugs section); no change made, so it carries no F7 code and needs no live path from this task. |
| `Task-F7-Completion.md` (this file) | N/A — documentation, not a site file | New — **in the zip**, at zip root | No |

### ⚠️ New folder path — not found in either project summary doc

`assets/js/` (used for `currency-locale.js`'s live path above) does **not** appear anywhere in `adoption-website-project-summary.md`'s Section 1 "Server Folder Structure" or in `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s "File Structure on Server" — I checked both folder trees directly, line by line; neither documents an `assets/` folder of any kind, despite `/assets/images/...` paths being confirmably real and already working elsewhere in the live `pet-detail-v2.html` I was given (logo and placeholder images). **This path comes from a different, more recent source instead:** `WORKER-DELIVERY-GUIDE.md`'s own Source → Live Path mapping table explicitly states "Shared CSS/JS under `assets/`" → `assets/css/...`, `assets/js/...`. I'm following that mapping since it's the newer, more specific instruction for exactly this situation (a new shared script), but since it contradicts/extends what's in the two project summary docs, I'm flagging it heavily here rather than letting it pass as an already-documented path:

**→ Folder to create: `assets/js/` (a new subfolder under the existing `assets/` folder, which itself is confirmed real but undocumented in either summary doc). File to place in it: `currency-locale.js`.**

**Note on `currency-locale-demo.html`:** this is an internal test/demo harness used only to verify `currency-locale.js` renders and computes correctly (see Self-Check Confirmation) — it was never meant to be a live site page and has no live path, so it is **not** placed under `assets/` or any other live-site-shaped path inside the zip. Per this message's "all new files... joined together" instruction it's still included in the zip (previously it was sent separately, outside the zip) — but under a clearly non-live `_supporting-files/` folder at the zip root, so the integrator doesn't mistake it for something to deploy onto the live site.

## Code Changes

### `pet-detail.html` → live path `pets/detail/index.html`

**Block 1 of 4 — script include**

<!-- TASK-F7 START -->
**Old code:**
```html
  </div>
</footer>


<script>
'use strict';

// Header scroll effect
```
**New code:**
```html
  </div>
</footer>

<!-- TASK-F7 START — currency-code-aware amount formatting (Task F7).
     Loaded before the main inline script below so PCLocale is available
     the moment populatePetInfo()/setupStickyMiniBar()/loadRecommended()
     run. Path follows the site's existing /assets/... static-asset
     convention (see /assets/images/... references above in this file). -->
<script src="/assets/js/currency-locale.js"></script>
<!-- TASK-F7 END -->

<script>
'use strict';

// Header scroll effect
```
<!-- TASK-F7 END -->

**Block 2 of 4 — main hero fee display (inside `populatePetInfo()`)**

<!-- TASK-F7 START -->
**Old code:**
```javascript
    var feeEl = document.getElementById('petFee');
    var feeNum = parseFloat(pet.adoption_fee);
    if (feeEl) feeEl.textContent = !isNaN(feeNum) ? '$' + feeNum.toLocaleString('en-US') : (pet.adoption_fee || '[user-to-fill]');
```
**New code:**
```javascript
    // TASK-F7 START — currency-code-aware fee display. PCLocale.formatCurrency
    // formats at a fixed en-US base locale but lets the currency code itself
    // disambiguate ($/CA$/£/A$), per §A1's currency_code contract (see
    // Task-F7-Completion.md). Falls back to the pre-existing placeholder
    // behavior, unchanged, when adoption_fee isn't a valid number yet.
    var feeEl = document.getElementById('petFee');
    var feeNum = parseFloat(pet.adoption_fee);
    if (feeEl) feeEl.textContent = !isNaN(feeNum) ? PCLocale.formatCurrency(feeNum, pet.currency_code) : (pet.adoption_fee || '[user-to-fill]');
    // TASK-F7 END
```
<!-- TASK-F7 END -->

**Block 3 of 4 — sticky mini bar fee display (inside `setupStickyMiniBar()`)**

<!-- TASK-F7 START -->
**Old code:**
```javascript
    if (miniFee) {
      var miniFeeNum = parseFloat(pet.adoption_fee);
      miniFee.textContent = !isNaN(miniFeeNum) ? '$' + miniFeeNum.toLocaleString('en-US') : '';
    }
```
**New code:**
```javascript
    // TASK-F7 START — same currency-code-aware formatting as the main fee
    // display above; adoption-website-project-summary.md Section 13.7
    // requires these two displays to stay in sync if fee-formatting logic
    // ever changes, so both call the same PCLocale.formatCurrency().
    if (miniFee) {
      var miniFeeNum = parseFloat(pet.adoption_fee);
      miniFee.textContent = !isNaN(miniFeeNum) ? PCLocale.formatCurrency(miniFeeNum, pet.currency_code) : '';
    }
    // TASK-F7 END
```
<!-- TASK-F7 END -->

**Block 4 of 4 — recommended-pets card grid fee display (inside `loadRecommended()`)**

<!-- TASK-F7 START -->
**Old code:**
```javascript
            var fee   = (p.adoption_fee || 0).toLocaleString('en-US');
```
**New code:**
```javascript
            // TASK-F7 START — currency-code-aware fee display. Note: this
            // line was previously missing its currency-symbol prefix
            // entirely (plain toLocaleString with no "$"); fixed here as
            // part of switching to PCLocale.formatCurrency, since it's the
            // exact same line this task's fee-formatting change replaces —
            // see Task-F7-Completion.md, Bugs Found Outside Task Scope.
            var fee   = PCLocale.formatCurrency(p.adoption_fee, p.currency_code);
            // TASK-F7 END
```
<!-- TASK-F7 END -->

Every "Old code" block above was verified by exact string search against the original `pet-detail-v2.html` as uploaded (before any edit) and confirmed to occur **exactly once** in that file.

## New files

All three are packaged together in **`Rehoming-Platform-Task-F7.zip`**, per the updated hand-in instructions (project name taken exactly from `Rehoming-Platform-Task-Assignments.md`, the task assignment file this task comes from):

- `assets/js/currency-locale.js` — **New file — in the zip, at its live path.** Reusable `PCLocale` component: `formatCurrency()`/`renderCurrencyChip()` for the four A1-contract currencies (formatted at a fixed `en-US` base locale so CA$/A$/£/$ stay disambiguated), plus `localizePath()`/`getLocaleFromPath()`/`detectPreferredLocale()` for the five finalized locale prefixes. This is the file `pet-detail.html`'s script-include (Block 1 above) loads. **Note:** no HTML page naming is involved here — this is a `.js` asset, not a page, so the "every page is `index.html`" rule doesn't apply to it directly; it applies to `pet-detail.html`'s own live path (`pets/detail/index.html`, already correctly used throughout this completion file and confirmed unchanged — this task created no new HTML page, so there's no new page name to get wrong).
- `currency-locale-demo.html` — **New file — in the zip**, but deliberately placed at `_supporting-files/currency-locale-demo.html`, **not** at a live-site-shaped path. It's an internal verification page only (see Self-Check Confirmation) and was never going to be deployed, so it does not follow the `index.html`-per-folder convention — doing so would wrongly imply it's meant to go live somewhere.
- `Task-F7-Completion.md` (this file) — **in the zip**, at the zip root, per the updated instruction that the completion file now ships inside the same zip as the new files rather than as a separate attachment.

## Bugs Found Outside Task Scope

**`pet-detail-v2.html` as given appears to predate Task A1's own display-side changes to the same file.** `Task-A1-Completion.md` documents inserting a `<div id="pcA1Sections">` block (health status, temperament, reason-for-rehoming, foster details, wrapped in `<!-- TASK-A1 START/END -->`) immediately after the existing `fullDescSection` block (around where `fullDescSection`/`fullDescName`/`fullDescription` appear in the file). That exact anchor point **is** present in the copy I was given — confirming it's the same file lineage — but A1's inserted content is not: zero occurrences anywhere of `pcA1Sections`, `TASK-A1`, or A1's field names (`reason_for_rehoming`, `spay_neuter_status`, `listing_type`, `is_urgent`, etc.). An earlier task's anchors (`TASK-2`, the Posted-on date work, several locations through the file) **are** present and intact, so this copy is current with that task but stale against A1's.

This does not affect this task's own correctness — all four of this task's edits are to pre-existing fee-display elements that don't live inside or depend on A1's block, and the `pet.currency_code` field this task reads comes from fetched `pet.json` data, not from anything A1 added to this file's own markup. Not fixed here (not this task's file/content to add — Scope Lock). Flagging so that whoever integrates this hand-in merges **both** A1's `pcA1Sections` block and this task's four `TASK-F7` blocks into the one real live file — they don't overlap, so it's a clean combine, but only if both are actually applied; taking either hand-in alone as "the new current version" would silently drop the other.

## Self-Check Confirmation

- Touched only the files listed in Files Changed: `currency-locale.js` (new), `pet-detail-v2.html` (4 anchored blocks). `pet-posting-form-v2.html` was opened to read only, to resolve a scope question about whether a visible currency selector was needed (it already documents that currency is account-derived, not user-chosen — no selector needed, no change made). No other file was opened for editing.
- Every changed block in `pet-detail-v2.html` is wrapped in a matching `<!-- TASK-F7 START -->` / `<!-- TASK-F7 END -->` pair (or the `// TASK-F7 START` / `// TASK-F7 END` JS-comment equivalent inside `<script>` blocks) and carries a comment explaining what it does.
- Every "Old code" snippet above was copied character-for-character from the original `pet-detail-v2.html` upload (verified by exact string search, not retyped from memory) and confirmed to appear exactly once in that file.
- Zip named `Rehoming-Platform-Task-F7.zip` — project name taken exactly from `Rehoming-Platform-Task-Assignments.md` (the task assignment file), task number `F7`, per the updated naming instruction and its `project-name-task-number.zip` example pattern.
- Zip checked: `unzip -l Rehoming-Platform-Task-F7.zip` shows three entries — `assets/js/currency-locale.js` (live path, matches its Files Changed row), `_supporting-files/currency-locale-demo.html` (explicitly non-live, kept out of any path that looks deployable), and `Task-F7-Completion.md` at the zip root (this file, now shipped inside the zip rather than sent separately, per the updated instruction that all new files and the completion file travel together in one zip).
- Zip content scanned for forbidden material: no `config.php`, no `pet.json`, no `.env`, nothing under `applications/` or `uploads/`, and no secret-looking strings (API keys, passwords, tokens) anywhere in any zipped file's content or path — confirmed by direct grep against the zip contents before sending.
- The one path in this hand-in that isn't documented in either project summary doc (`assets/js/`) is heavily flagged in Files Changed above, with the exact folder to create and why, rather than silently assumed.
- `currency-locale.js` needs no secret/config value of any kind (pure client-side formatting logic) — nothing to flag for the CEO to configure.
- Tested with the tools available: Playwright/Chromium against a local server tree mirroring the real deployed folder structure (`pets/detail/index.html`, `assets/js/currency-locale.js`, `adoptions/pets/birds/{pet_id}/pet.json`), with stub listings covering all four currencies plus a missing-`currency_code` case (the realistic state of nearly every pre-A1 pet today). Confirmed actual rendered text, not just reasoned about the diff:

  | Location | Pet | currency_code | Rendered |
  |---|---|---|---|
  | Hero fee | Kira | CAD | `CA$650.99` |
  | Sticky mini bar | Kira | CAD | `CA$650.99` (matches hero) |
  | Recommended card | Milo | USD | `$500.00` |
  | Recommended card | Rio | *(missing)* | `$150.00` (correct USD fallback, console silent — see below) |
  | Recommended card | Percy | GBP | `£90.00` |
  | Recommended card | Suki | AUD | `A$2,500.00` |

  Also verified `formatCurrency()`'s fallback console behavior specifically: stays silent when `currency_code` is simply absent (the normal case for virtually every listing today, since A1 only just added this field), and still warns when a code is present but invalid (e.g. `"JPY"`) — confirmed both via Node directly and via the real integrated page in Playwright, with a `page.on('console', ...)` listener attached (zero `[PCLocale]` warnings logged for the missing-code fixture; exactly one for the invalid-code case). Full boundary-case sweep (zero, very large, negative, missing/undefined, NaN, lowercase/uppercase currency and locale codes, empty/null/no-leading-slash paths) run via Node — all correct.
