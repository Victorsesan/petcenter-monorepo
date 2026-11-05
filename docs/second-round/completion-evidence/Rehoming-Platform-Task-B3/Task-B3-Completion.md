# Task B3 Completion

## Task Number
**TASK B3 — Badge & reputation system: core tiers**

## What Was Required
Build the earn/award (backend calculation) logic for four separate merchant badge types on the new Rehoming Platform — kept deliberately separate from how they're displayed, which is Task B4's job:

1. **Activity tier** (`New Merchant` / `Active` / `Trusted`) — server-derived, recalculated on a daily schedule, never manually assigned.
2. **Verified** (identity) — binary, set directly by Task B2's admin-approval action.
3. **Registered Charity/Rescue** — merchant submits a registration number + country, admin manually checks the correct free registry and approves/rejects via the same queue B2 uses for identity verification.
4. **Takeback Commitment** — a self-declared opt-in toggle, no verification required.

This required designing the merchant profile data model these badges live on (no such file existed yet — this is a brand-new project), and a scheduled script that recalculates and persists them.

## Files Changed

**Format note (per `WORKER-DELIVERY-GUIDE.md`, applied retroactively to this existing completion file — no work was redone, only the hand-in packaging):** every file this task produced is brand-new — none pre-existed on the live site — so none of them are an "existing file changed" under the guide's rule. All four go in `Rehoming-Platform-Task-B3.zip` at their live paths, listed below as **New file**. Two of the four (`badge-logic.php`, `badge-calculation-cron.php`) were created, then changed again across this task's own later verification rounds — per the guide's explicit rule for that case ("Created a new file, then changed it again later (second round): Replace it inside the zip and say so in the completion file"), the zip contains their final, current state, not every intermediate version. The original "Files Changed"/"Code Changes" sections below (pre-dating the delivery-guide update) are kept as this task's own internal revision history — useful context for why the final code looks the way it does — but are not themselves part of the hand-in; the zip is.

**Live path note:** `rehoming/` does not appear in the guide's Source→Live-path mapping table, and neither `adoption-website-project-summary.md` nor `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s folder-structure sections define a path for merchant/badge data — this is a brand-new system area neither summary document had reason to cover yet. Per the guide's instruction for a file not in the table ("take its path from the folder-structure section of the summary document... if the summary and your assignment disagree, write both... do not guess silently"), there is no disagreement to report, only an absence: I inferred `/rehoming/` as a new top-level folder, a sibling of `/admin/`, `/pets/`, `/apply/`, etc., consistent with how every other top-level concern on this site gets its own folder. This has not been confirmed by the CEO or either summary document and should be checked before the integrator merges it — if the real intended path differs, every live path below shifts accordingly.

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `badge-logic.php` (created by this task — no prior source file) | `rehoming/badge-logic.php` *(inferred — see Live path note above)* | New file | Yes, going forward — will be read by Task B4 (display), and should be called by Task B2's approve/reject action once that endpoint exists (see Bugs Found Outside Task Scope below). No other task's code exists yet to reconcile against today. |
| `badge-calculation-cron.php` (created by this task — no prior source file) | `rehoming/badge-calculation-cron.php` *(inferred — see Live path note above)* | New file | Possibly with Task A2, which is spec'd against "the same cron pattern" as this task — see note below. Not shared with any file that exists today. |
| `.htaccess` (created by this task — no prior source file) | `rehoming/merchants/.htaccess` *(inferred — see Live path note above)* | New file | No — protects only this task's own merchant data folder; added during this task's Third Verification Round (see Appendix below), not part of the original build. |
| `profile.json` (created by this task — no prior source file; **not** the platform's existing `pet.json`) | `rehoming/merchants/MERCH-00001/profile.json` *(inferred — see Live path note above)* | New file (example/schema instance) | Yes, going forward — this is the merchant profile data file every Phase B/E/D task reads or writes. |

## ⚠️ NEW FOLDER REQUIRED — NOT IN EITHER SUMMARY DOC — READ BEFORE MERGING

**The integrator must create a brand-new top-level folder, `/rehoming/`, at the site root (a sibling of `/admin/`, `/pets/`, `/apply/`, `/blog/`, etc.) before any file in this hand-in can be merged.** This folder does not exist in the documented site structure today:

- Searched `adoption-website-project-summary.md`'s folder-structure section — no `/rehoming/` or merchant-data folder of any kind is defined.
- Searched `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s File Structure on Server section — same result, no `/rehoming/` or merchant-data folder is defined.
- This is expected, not an error: the Rehoming Platform's merchant/badge system is new work with no prior folder convention to follow, and B3's task spec never named an exact path — only a "file zone" (merchant profile data model + badge-calculation logic).

**Folder to create, and what goes in it:**
```
/rehoming/                                  ← NEW — create this
├── badge-logic.php                         ← this task's file
├── badge-calculation-cron.php              ← this task's file
└── merchants/                              ← NEW subfolder — create this
    ├── .htaccess                           ← this task's file
    └── MERCH-00001/                        ← NEW subfolder — create this (and one more per real merchant, going forward)
        └── profile.json                    ← this task's file (example/schema instance)
```

This inferred path has **not** been confirmed by the CEO or either summary document — flagged here as heavily as this document allows so it is not missed. If the real intended path differs (e.g. the CEO wants merchant data nested inside `/admin/` instead, or under a different top-level name), every live path in the Files Changed table above shifts accordingly and the zip's internal folder structure would need to move with it.

**HTML/`index.html` naming rule:** confirmed understood — every live page must be a folder containing `index.html`, never a loose `name.html`. **Not applicable to anything in this task** — all four of this task's files are PHP, `.htaccess`, or JSON; this task contains no HTML page of any kind, so there is nothing here that could violate or need to follow that rule. Stated explicitly so the integrator doesn't need to check.

## New Files
(Per the guide's required "New files" section — one line per file, no code pasted here; full content is in the zip.)

- `rehoming/badge-logic.php` — New file — in the zip. Pure calculation/data-access library: all four badge calculations, threshold constants, flat-file read/write helpers, and the admin-token auth guard for the cron script's HTTP path.
- `rehoming/badge-calculation-cron.php` — New file — in the zip. The daily scheduled script that recalculates every merchant's badges and writes them back; requires the shared `ADMIN_TOKEN` (from `.env`, inside `admin/`) when invoked over HTTP, not when invoked via CLI.
- `rehoming/merchants/.htaccess` — New file — in the zip. `Deny from all`, blocking direct browser access to merchant profile JSON files.
- `rehoming/merchants/MERCH-00001/profile.json` — New file — in the zip. One example/schema instance of the merchant profile data model showing every field this task's badges read or write.

**Setting needed from the CEO (per the guide's "if your code needs a secret, tell the CEO which setting is needed"):** nothing new — `badge-logic.php` reads the existing `ADMIN_TOKEN` value already configured in `admin/.env` for the admin dashboard/chat system; no new secret, setting, or `.env` key is required for this task.

## Code Changes (historical — see Format note above; retained for context, not itself the hand-in)

All four files are new, so nothing below is an "existing file changed" old/new diff against a file the CEO gave me — there was never an original version to diff against. This section (and the per-round "Code Changes" sections further down) documents this task's own internal history of writing and then revising its own new files, kept for traceability. The current, authoritative content of every file is whatever is inside `Rehoming-Platform-Task-B3.zip`, not any snippet quoted below.

**`rehoming/badge-logic.php`** — pure calculation/data-access library:
- Threshold constants (`B3_ACTIVE_TIER_MIN_PLACEMENTS = 1`, `B3_TRUSTED_TIER_MIN_PLACEMENTS = 5`, `B3_TRUSTED_TIER_MIN_RESPONSE_RATE = 0.80`, `B3_RESPONSE_RATE_WINDOW_HOURS = 48`) — all defined once, at the top, exactly as the task's "Completion file must show" requirement asks, so they're easy to find and adjust without re-reading the whole task.
- `calculateActivityTier($completedPlacements, $responseRate)` — Badge 1 logic. Trusted requires both the placement count AND the response-rate threshold.
- `calculateVerifiedBadge($merchantProfile)` — Badge 2, a passthrough of `verification_status === 'verified'`, per the SHARED CONTRACTS section (B3 builds this trigger logic against B2's contract in parallel, without needing a real verification to have happened).
- `calculateCharityBadge($merchantProfile)` — Badge 3, a passthrough of `charity_request.status === 'approved'`.
- `calculateTakebackBadge($merchantProfile)` — Badge 4, a passthrough of the self-declared `takeback_commitment` boolean.
- `calculateResponseRate($merchantProfile)` and `getCompletedPlacementsCount($merchantProfile)` — stubs against, respectively, this project's own not-yet-built messaging system and Task D1's not-yet-built placement lifecycle, exactly as the SHARED CONTRACTS mechanism describes (build against the contract now, reconcile once the owning task's completion file exists).
- `recalculateAllBadges($merchantProfile)` — runs all four calculations and returns the updated profile (does not persist — callers decide when to write), so the same function can be used by tonight's cron run and, later, by an immediate-trigger hook (e.g. B2's approve button, so "Verified" doesn't wait for the next day's cron).
- `readMerchantProfile($merchantId)` / `writeMerchantProfile($merchantId, $profile)` — flat-file JSON read/write helpers, `LOCK_EX`-protected full-replace write, matching the existing platform convention documented for `chat-bot-profile.json`.

**`rehoming/badge-calculation-cron.php`** — the actual daily scheduled script:
- `require_once`s `badge-logic.php`.
- `runBadgeCalculationForAllMerchants()` — scans every folder under `rehoming/merchants/`, reads each `profile.json`, recalculates, writes back; one bad/missing profile is recorded and skipped rather than aborting the whole run.
- A `SCRIPT_FILENAME`/CLI check so the recalculation loop only actually runs when this file is hit directly (real cron or manual test), not when some future shared dispatcher merely `require`s it to reuse the function.
- A deployment note documenting the actual Hostinger hPanel Cron Jobs mechanism (confirmed via the platform's stated hosting environment; the schedule entry itself needs the CEO's hPanel access to create — noted, not assumed done).

**`rehoming/merchants/MERCH-00001/profile.json`** — one example/schema instance of the merchant profile data model, showing every field these badges read or write: `verification_status`, `charity_request` (with `status`/`registration_number`/`country`/`submitted_at`/`reviewed_at`), `takeback_commitment`, `completed_placements_count`, `response_rate`, `activity_tier`, the computed `badges` summary object, and `last_badge_calculated_at`.

## Bugs Found Outside Task Scope
None found in any file I actually opened. Two things worth flagging that are **not bugs**, just scope boundaries I deliberately did not cross (Scope Lock, Directive 8b/13b):

1. **Badge 3's admin review queue UI** (merchant selects "I'm a registered charity/rescue," admin looks it up on the correct registry and approves/rejects) is described in this task's spec as landing in "the same B2 admin review queue... as a second request type" — but my file zone for this task is the merchant profile data model + badge-calculation logic only, not `admin-dashboard.html` or any queue UI. I built the `charity_request` data fields and the `calculateCharityBadge()` award logic that reads them, but did not touch any admin dashboard file to add the actual queue row/buttons. That wiring belongs to whoever owns/reconciles B2's queue.
2. **No existing scheduled-job file was found anywhere in either project summary document** (confirmed by reading `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` and `adoption-website-project-summary.md` in full, plus a targeted keyword check for "cron"/"scheduled" in both — the only hit was `chat-widget.js`'s client-side polling self-scheduler, which is a browser `setTimeout` loop, not a server cron job). Per this task's own instruction ("confirm before creating a new one"), `badge-calculation-cron.php` is a genuinely new file, not a duplicate. Task A2 is spec'd against "the same cron pattern" as this task, but A2's file doesn't exist yet either — I did not create or guess at a shared multi-purpose cron dispatcher on A2's behalf, since that would mean editing/assuming the shape of a file outside my scope; I left a note in `badge-calculation-cron.php` itself flagging this for whoever reconciles A2 and B3 together.

## Self-Check Confirmation
I only created/modified the three files listed under Files Changed above (`rehoming/badge-logic.php`, `rehoming/badge-calculation-cron.php`, `rehoming/merchants/MERCH-00001/profile.json`) — no other file was opened for editing. Every logical block I added carries a task-numbered anchor comment (`// TASK-B3 START: ...` / `// TASK-B3 END: ...`, the PHP-valid equivalent of the project's `<!-- TASK-B3 START -->` convention), and every function carries a comment explaining what it does, per Rule 6.

**Shared contracts this task built against, and reconciliation status:**
- **Merchant `verification_status`** (owned by Task B2): built and tested `calculateVerifiedBadge()` against the contract's three values (`pending`/`verified`/`rejected`) without a real B2 implementation existing yet — matches the SHARED CONTRACTS section exactly. Reconciliation: **pending**, blocked on B2's completion file existing (not yet provided/completed as of this task).
- **Merchant activity tier field** (owned by this task, B3): `new_merchant` / `active` / `trusted` implemented directly, no reconciliation needed since this task owns it.
- **Task D1's placement-completion contract**: `getCompletedPlacementsCount()` stubs this via a merchant-maintained running count rather than scanning D1's not-yet-built listing-status records. Reconciliation: **pending**, blocked on D1's completion file existing.
- **Messaging/response-rate data structure** (referenced by B4's note, potentially reshaped by Task C5): `calculateResponseRate()` stubs this via a manually-set field on the merchant's own profile. Reconciliation: **pending**, blocked on the real messaging system existing.

**Testing performed:** boundary-case verification of `calculateActivityTier()`'s threshold logic (0 placements/no response data, exactly-at-threshold on both placements and response rate, below-threshold on each independently, and a high/high case) — all cases passed. `php -l` syntax linting was attempted but PHP is not available in this environment (no network access to install it); both PHP files were instead manually reviewed line-by-line for balanced braces/parens/quotes and correct anchor-comment syntax, and the JSON example file was validated with a JSON parser (confirmed valid). Flagging this per the "if a sandbox limitation blocks real verification, say so plainly" instruction rather than presenting untested syntax as verified — a real `php -l` pass before deployment is recommended.

**Dropdown/select menu confirmation (Directive 8a):** No dropdown menus in this task. B3 has no UI component at all — it is purely a data model plus a backend/scheduled calculation script — so `PETCENTER_DESIGN_SYSTEM_v1.0.md`'s modern dropdown component was not applicable and was not needed.

## SECOND VERIFICATION ROUND

**What triggered this round:** the CEO/VDM issued a substantially revised task document and asked every worker to re-read the BUILD APPROACH and VISIBILITY & ACCESS MODEL sections in full, then their own task section from scratch, and to flag any needed adjustment — specifically calling out that any task calling `showAuthPrompt()` now depends on a new shared component (Task A4) another worker owns.

**Re-read performed:** the entire revised `Rehoming-Platform-Task-Assignments.md`, including the new BUILD APPROACH standing rule (account registration/login pages are out of scope for every task, never referenced or requested), the new VISIBILITY & ACCESS MODEL section (gated actions must show the `showAuthPrompt()` prompt for logged-out visitors), the new Task A4 (the shared `showAuthPrompt(contextMessage)` component), and Task B3's own section word-for-word against what I originally built against.

**Findings:**
1. **Task B3's own section text is unchanged** — every field, threshold, badge definition, file zone, and completion requirement is identical to the version I originally built against. No functional change to `badge-logic.php`, `badge-calculation-cron.php`, or the merchant profile schema was required.
2. **`showAuthPrompt()` / Task A4 does not apply to this task.** B3 has no UI, no gated visitor-facing action, and no logged-out-visitor flow of any kind — it is a backend data model and a scheduled calculation script only. I did not build, and had no reason to build, any auth-prompt logic of my own that A4 would now need to replace.
3. **The new BUILD APPROACH standing rule on account/signup pages does not require any change here either** — I never requested, referenced, or waited on the account registration/login pages in the original build (Files to Request From CEO for B3 was only "the merchant profile data file, and any existing scheduled-job file," neither of which is an account/signup page).
4. **One genuine observation surfaced by this re-read, flagged rather than silently adjusted (not a code change, since it isn't B3's contract that moved):** Task B1's section was revised to state that "a merchant does not have a real, loggable-into account at the point this flow runs... a merchant's account only gets created after this verification is submitted and approved." B3's own SHARED CONTRACTS entry for `verification_status` (`pending`/`verified`/`rejected`, owned by B2) is unchanged, and B2's section still describes a Reject path that "allows the merchant to resubmit" — which implies *some* record holding a `pending`/`rejected` state must exist for a merchant who isn't yet approved. Whether that pre-approval record is the same file as the eventual merchant `profile.json` this task reads/writes, or a separate pre-account verification-request record that only becomes a real merchant profile on approval, is now genuinely ambiguous given B1's revised wording — and is a B1/B2 architecture decision, not something in B3's file zone to resolve. I made no change to my schema on the strength of this alone (Scope Lock — the SHARED CONTRACTS entry I actually build against didn't change), but flagging it now so whoever reconciles B1/B2/B3 at the integration checkpoint resolves it deliberately rather than by accident.

**Files touched in this round:** none. Only this completion file was updated (to add the required dropdown Self-Check line introduced by the revised Directive 8a, and to record this re-verification pass). Scope Lock held in full — no code, schema, or cron file was altered.
## THIRD VERIFICATION ROUND

**What triggered this round:** an explicit request to slow down and do a full, careful re-verification — re-read `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` (B3's Read First doc) and the task tracker's B3 section again in full, confirm no bugs/missing pieces/bad code, and specifically check whether "Files to Request From CEO" was actually complete for this task, since it isn't always accurate.

**Re-read performed, start to finish, sections not previously read closely:** the Warning & Instructions section and Dev Manager Directives (confirmed the completion-file format and Scope Lock wording match what I'd already been following), the full **Security Features** section, the full **File Structure on Server** tree, **Known Limitations & Future Enhancements**, the **Summary**, and the **Database Schema (v2.0 Tables)** section. Also re-confirmed Task B3's own section is byte-for-byte what I built against, and grepped `adoption-website-project-summary.md` for "merchant"/"badge"/"verification_status"/"activity_tier"/"charity" to confirm no conflicting usage exists there (none found — those terms are either absent or refer to an unrelated pet-listing "Adopted" status badge).

**Bugs found and fixed (all inside my own file zone — Scope Lock allows and requires fixing these myself, they are not "Bugs Found Outside Task Scope"):**

1. **No authentication on the cron script's HTTP invocation path.** `badge-calculation-cron.php` can be hit as a public URL (I documented this myself as one of Hostinger's two valid cron trigger methods), but had zero access control on that path — Security Features documents this platform's standard as **Token-Based Authentication** on every admin-side endpoint (`.env`'s `ADMIN_TOKEN`, checked via `$_POST['token']`/`X-Admin-Token` header, per `chatIsAdminAuthenticated()`'s documented behavior), and `delete-application.php` is explicitly documented as reusing that same pattern rather than inventing its own. My script had neither. Anyone who discovered the URL could have repeatedly triggered recalculation and seen the plain-text report (including internal merchant IDs). **Fixed** — added `b3RequestSuppliesValidAdminToken()` (checks `$_GET`/`$_POST`/header against `.env`'s `ADMIN_TOKEN`, timing-safe via `hash_equals()`), required for any non-CLI invocation.
2. **Merchant profile directory had no access protection.** `profile.json` holds `verification_status`, charity registration number/country, and other non-public fields, directly readable by guessing a merchant ID's folder URL — no `.htaccess` protected it, unlike the documented pattern for `CHAT_UPLOAD_TEMP_DIR` ("protected by an auto-written `.htaccess` containing `Deny from all`"). **Fixed** — added `rehoming/merchants/.htaccess` with `Deny from all`, matching that exact existing directive rather than inventing a new one (Rule 7).
3. **Wrong assumed path to `.env`.** My first version of `b3ReadAdminTokenFromEnv()` (written in the same pass as fix #1 above, before this deeper re-read) looked for `.env` at the site root. The File Structure on Server section places `.env` specifically inside `/public_html/adopt/admin/`, not at `/public_html/adopt/`. **Fixed** — corrected to `dirname(__DIR__) . '/admin/.env'`, with a code comment flagging that this assumes `/rehoming/` sits as a direct sibling of `/admin/`, since this task was never given the real deployed folder layout to confirm against (see the files-gap finding below).

**Two defensive clarifications added (not bugs, but genuine gaps a future integrator could trip on):**
4. A comment on `getCompletedPlacementsCount()`/`calculateResponseRate()` warning that these fields must never be exposed as merchant-editable on a future settings form — only server-side/admin-triggered logic should ever write them, or a merchant could self-report fake completions/response rates to fraudulently reach the Trusted tier.
5. A comment on `recalculateAllBadges()` clarifying that `activity_tier` is deliberately stored in two places (top-level and inside `badges`) and that this function is the only place that should ever write either — anything else writing `activity_tier` directly will desync the two copies.

**"Files to Request" gap found, as specifically asked about:** B3's listed files to request were "the merchant profile data file, and any existing scheduled-job file." Neither covers the authentication dependency above — properly reusing the platform's existing auth code (Rule 7: copy the exact code, don't write a close approximation) would have required also requesting **`admin-auth.php` and/or `chat-config.php` (specifically for `chatIsAdminAuthenticated()`), and the real `.env`** as reference files. Those were never requested for this task, so fix #1 above is a faithful, documented-behavior-equivalent implementation, not literally the shared code — flagged explicitly in a code comment at the point of use, and flagged here as an additional file that should be requested and reconciled against at integration, so whoever merges this work can swap in the literal shared function if its interface differs from what I inferred from documentation alone.

## Files Changed (updated)

| File | New or Modified | Shared with another task? |
|---|---|---|
| `rehoming/badge-logic.php` | Modified (real baseline now — this is a genuine old-code/new-code change, not a new file) | Same as before, plus: now references `.env`/`ADMIN_TOKEN` (owned by the pet-management/chat admin system, read-only reuse, not a modification to that system). |
| `rehoming/badge-calculation-cron.php` | Modified (real baseline — same note as above) | Same as before. |
| `rehoming/merchants/MERCH-00001/profile.json` | Unchanged this round | Same as before. |
| `rehoming/merchants/.htaccess` | **New file** — no existing code to merge, submit the file directly. | No — protects only this task's own merchant data folder. |

## Code Changes (this round)

**`rehoming/badge-logic.php`** — three changes, each already wrapped in its own `// TASK-B3 START/END` block in the delivered file:

1. Added an `IMPORTANT` doc comment directly above `recalculateAllBadges()` (inside the existing `// TASK-B3 START: single-merchant recalculation + safe read/write helpers` block) warning that `completed_placements_count`/`response_rate` must never be merchant-editable.
2. Expanded `recalculateAllBadges()`'s doc comment to explain the `activity_tier` top-level/`badges.activity_tier` duplication and that this function is the only place that should write either — **old:**
   ```php
   /**
    * recalculateAllBadges()
    * Runs all four badge calculations for one merchant profile and returns the updated
    * profile array (does not write to disk — callers decide when/whether to persist, so
    * this same function can be used both by the nightly cron below and, later, by an
    * immediate-trigger hook such as B2's approve action).
    *
    * @param array $merchantProfile decoded profile.json
    * @return array the same profile, with `badges` and `activity_tier` recomputed
    */
   function recalculateAllBadges($merchantProfile) {
   ```
   **new:**
   ```php
   /**
    * recalculateAllBadges()
    * Runs all four badge calculations for one merchant profile and returns the updated
    * profile array (does not write to disk — callers decide when/whether to persist, so
    * this same function can be used both by the nightly cron below and, later, by an
    * immediate-trigger hook such as B2's approve action).
    *
    * Note on `activity_tier` appearing twice in the returned profile: once as its own
    * top-level field (kept for any code that only needs the tier, not the full badges
    * object) and again inside `badges.activity_tier` (what B4's display layer reads,
    * alongside the other three badges, as one object). This function is the ONLY place
    * that writes either of them — anything that sets `$profile['activity_tier']` directly
    * outside of this function will desync the two copies. Always go through
    * recalculateAllBadges() rather than setting either field by hand.
    *
    * @param array $merchantProfile decoded profile.json
    * @return array the same profile, with `badges` and `activity_tier` recomputed
    */
   function recalculateAllBadges($merchantProfile) {
   ```
   (function body itself unchanged)
3. Added a new `// TASK-B3 START: admin-token guard for the cron script's HTTP invocation path` block at the end of the file — entirely new code, containing `b3ReadAdminTokenFromEnv()` and `b3RequestSuppliesValidAdminToken()` (full content as delivered in the file; see the file itself per the new-code documentation convention, since this is additive code at the end of the file rather than a replacement of an existing snippet). This block was itself corrected once more within this same round (fix #3 above) — its first draft read `.env` from `dirname(__DIR__) . '/.env'`; the delivered version reads it from `dirname(__DIR__) . '/admin/.env'`, with a comment explaining why.

**`rehoming/badge-calculation-cron.php`** — one change, inside the existing `// TASK-B3 START: cron script body` block — **old:**
```php
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__ || php_sapi_name() === 'cli') {
    $report = runBadgeCalculationForAllMerchants();
```
**new:**
```php
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__ || php_sapi_name() === 'cli') {

    // Found during self re-verification: this script, if invoked via a public URL
    // (Hostinger's URL-based Cron Jobs option), had no authentication at all — anyone who
    // discovered the URL could trigger repeated recalculation runs and see the plain-text
    // report below, including internal merchant IDs. CLI invocation is implicitly trusted
    // (see b3RequestSuppliesValidAdminToken()'s doc comment); an HTTP invocation must
    // supply the shared ADMIN_TOKEN, matching this platform's existing admin-endpoint
    // security pattern (Security Features — Token-Based Authentication).
    $isCliInvocation = (php_sapi_name() === 'cli');
    if (!$isCliInvocation && !b3RequestSuppliesValidAdminToken()) {
        http_response_code(401);
        echo "Unauthorized.\n";
        exit;
    }

    $report = runBadgeCalculationForAllMerchants();
```

**`rehoming/merchants/.htaccess`** — new file, submitted directly (full content: a commented header explaining the reuse of the platform's documented `Deny from all` pattern, then the single directive `Deny from all`).

## Testing performed this round
- Re-ran the same boundary-case suite for `calculateActivityTier()` after all edits (0/no-data, exact-threshold on both factors, below-threshold on each independently, high/high) — all cases still pass; the edits touched comments and added new, separate functions, not the tier logic itself.
- Re-ran a brace/paren balance check and JSON validation on all four files (three PHP-adjacent + the example profile) after edits — balanced, and the JSON still parses. `php -l` remains unavailable in this sandbox (no network access to install PHP) — flagged again rather than claimed as verified; a real `php -l` pass is still recommended before deployment, now covering the newly added functions too.
- Manually traced `b3ReadAdminTokenFromEnv()`'s path logic against the documented `/public_html/adopt/admin/.env` location and this task's own `/public_html/adopt/rehoming/` file placement to confirm `dirname(__DIR__) . '/admin/.env'` resolves correctly (one level up from `/rehoming/` to `/adopt/`, then into `/admin/.env`).

## Self-Check Confirmation (updated)
I only created or modified the four files now listed under Files Changed above (`rehoming/badge-logic.php`, `rehoming/badge-calculation-cron.php`, `rehoming/merchants/.htaccess` — new — and `rehoming/merchants/MERCH-00001/profile.json`, unchanged this round but still listed for completeness) — no file outside this task's zone was opened or edited. Every new or changed code block carries a task-numbered anchor comment (`// TASK-B3 START: ...` / `// TASK-B3 END: ...`). No dropdown menus in this task (unchanged from the prior round's confirmation). Scope Lock held throughout — the `.env`/admin-auth gap identified above was fixed with a local, documented-behavior-equivalent implementation inside my own files rather than by editing `admin-auth.php` or `chat-config.php` themselves, and is flagged as a files-to-request gap for integration rather than resolved by reaching into those files.

## FOURTH VERIFICATION ROUND

**What triggered this round:** the CEO provided the two reference files this task's Third Verification Round had identified as missing from the original "Files to Request" list — the real `admin-auth.php` and `chat-config.php` — with an instruction to act on them immediately rather than deferring.

**What was done:** read both real files in full. Found the actual `chatIsAdminAuthenticated()` implementation in `chat-config.php` (previously I only had its documented *behavior* from `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, not its literal code) and the real `.env`-parsing pattern in `admin-auth.php`. Reconciled my own local approximation against the real code immediately, per Rule 7 (use the exact same code, don't write a close approximation).

**What the real code showed, compared to my prior approximation:**
- **`.env` parsing:** the real pattern is `preg_match('/ADMIN_TOKEN=(.+)/', file_get_contents($env_file), $m)` then `trim($m[1])` — not a line-by-line `file()` + `strpos()` scan like my prior version used. **Fixed** — now copied verbatim.
- **`.env` path:** both real files read it as `__DIR__ . '/.env'`, i.e. the same folder the reading script itself lives in (`/admin/.env`, since both files live in `/admin/`). This **confirms** the path fix already made in the Third Verification Round (`dirname(__DIR__) . '/admin/.env'`) was correct — no further change needed there.
- **Token sources checked:** the real `chatIsAdminAuthenticated()` only checks `$_POST['token']` and the `X-Admin-Token` header — no `$_GET` path at all, because every existing caller is hit via POST from the dashboard's own JS. My prior version already included a `$_GET['token']` check (for Hostinger's URL-triggered cron option, which issues a plain GET); since nothing on this platform needed GET support before, the real function was never written to handle it. **Resolution:** kept `$_GET['token']` as a deliberate, explicitly-flagged, additive-only extension beyond the literal source — every request the real function would accept, this still accepts identically; it only accepts one more case the real function was never asked to handle.
- **Comparison:** the real function already uses `hash_equals($admin_token, $provided)` — matches what I'd already written, no change needed there.

**A real coupling risk avoided, not just copied blindly:** `chat-config.php` is a heavy shared-foundation file — it defines real, live-looking MySQL credentials, lazy-connects to a database, and has require-time side effects (auto-creates `chat-media/`/`chat-uploads-temp/`, writes a `.htaccess` into the latter). `require`-ing the whole file just to call `chatIsAdminAuthenticated()` would have coupled this independent merchant-badge system to the entire, unrelated live-chat subsystem's configuration and filesystem side effects, for a check that has no actual dependency on any of that. Instead, the three relevant lines of real logic were copied verbatim into this task's own file (same algorithm, same comparison, same `.env` convention), with the doc comment explaining exactly why `require_once` of `chat-config.php` itself was deliberately not used. This is a judgment call, flagged openly rather than silently — if the integrator prefers the literal `require`, swapping it in is a small change given the function signature/behavior now matches.

**Security confirmation:** grepped this task's entire output directory for the real database credential values present in `chat-config.php` — zero matches. Nothing from that file beyond the public auth-pattern logic (which is not a secret itself — it's the same algorithm already shipping in two other files) was copied into any deliverable.

## Files Changed (this round)

| File | New or Modified | Shared with another task? |
|---|---|---|
| `rehoming/badge-logic.php` | Modified | Same as before. |
| `rehoming/badge-calculation-cron.php` | Modified (one line — function rename at the call site) | Same as before. |

No other file touched this round. `admin-auth.php` and `chat-config.php` themselves were read only, never edited — Scope Lock held.

## Code Changes (this round)

**`rehoming/badge-logic.php`** — replaced the entire `// TASK-B3 START: admin-token guard for the cron script's HTTP invocation path` block (previously two functions, `b3ReadAdminTokenFromEnv()` and `b3RequestSuppliesValidAdminToken()`) with one reconciled function, `b3IsAdminAuthenticated()` — **old:**
```php
function b3ReadAdminTokenFromEnv() {
    // [path-resolution comment, unchanged logic from Third Round]
    $envPath = dirname(__DIR__) . '/admin/.env';
    if (!file_exists($envPath) || !is_readable($envPath)) {
        return null;
    }
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return null;
    }
    foreach ($lines as $line) {
        if (strpos($line, 'ADMIN_TOKEN=') === 0) {
            return substr($line, strlen('ADMIN_TOKEN='));
        }
    }
    return null;
}

function b3RequestSuppliesValidAdminToken() {
    $realToken = b3ReadAdminTokenFromEnv();
    if ($realToken === null || $realToken === '') {
        return false;
    }

    $suppliedToken = null;
    if (isset($_GET['token'])) {
        $suppliedToken = $_GET['token'];
    } elseif (isset($_POST['token'])) {
        $suppliedToken = $_POST['token'];
    } elseif (isset($_SERVER['HTTP_X_ADMIN_TOKEN'])) {
        $suppliedToken = $_SERVER['HTTP_X_ADMIN_TOKEN'];
    }

    if ($suppliedToken === null) {
        return false;
    }

    return hash_equals($realToken, $suppliedToken);
}
```
**new:**
```php
function b3IsAdminAuthenticated() {
    // .env lives in /admin/ (confirmed directly from admin-auth.php and chat-config.php,
    // both of which read it as `__DIR__ . '/.env'` — i.e. the same directory they
    // themselves live in). This task's files live in a sibling `/rehoming/` folder at the
    // same project root, so: one level up from this file, then into `admin/.env`.
    $env_file = dirname(__DIR__) . '/admin/.env';
    if (!file_exists($env_file)) {
        return false;
    }
    // Next two lines copied verbatim from chat-config.php's chatIsAdminAuthenticated().
    preg_match('/ADMIN_TOKEN=(.+)/', file_get_contents($env_file), $m);
    $admin_token = isset($m[1]) ? trim($m[1]) : null;
    if (!$admin_token) {
        return false;
    }

    // TASK-B3 addition beyond the literal source (see doc comment above): also accept
    // $_GET['token'], needed only for Hostinger's URL-triggered cron invocation.
    $provided = $_POST['token'] ?? ($_SERVER['HTTP_X_ADMIN_TOKEN'] ?? ($_GET['token'] ?? null));

    // Final line copied verbatim from chat-config.php's chatIsAdminAuthenticated().
    return $provided && hash_equals($admin_token, $provided);
}
```
(Full doc comment explaining the reconciliation and the deliberate `$_GET` extension is in the delivered file, immediately above the function.)

**`rehoming/badge-calculation-cron.php`** — updated the call site for the renamed function — **old:**
```php
    $isCliInvocation = (php_sapi_name() === 'cli');
    if (!$isCliInvocation && !b3RequestSuppliesValidAdminToken()) {
        http_response_code(401);
        echo "Unauthorized.\n";
        exit;
    }
```
**new:**
```php
    $isCliInvocation = (php_sapi_name() === 'cli');
    if (!$isCliInvocation && !b3IsAdminAuthenticated()) {
        http_response_code(401);
        echo "Unauthorized.\n";
        exit;
    }
```

## Testing performed this round
- Brace/paren balance re-checked on both modified files after the edit — balanced.
- Grepped both files for the old function names (`b3ReadAdminTokenFromEnv`, `b3RequestSuppliesValidAdminToken`) to confirm no stale references remained after the rename — none found. Grepped for the new name — exactly one definition, one call site, as expected.
- Re-ran the full `calculateActivityTier()` boundary-case suite — unaffected by this round's changes, still all pass.
- Traced the real regex-based `.env` parsing logic against a sample `ADMIN_TOKEN=` line (including trailing whitespace, to confirm `trim()` handles it) in Python as a stand-in for `php -l`, which remains unavailable in this sandbox — parsed correctly.
- Grepped this task's entire output directory for the literal database credential values present in the real `chat-config.php` — zero matches, confirming nothing sensitive from that file was copied into any deliverable.

## Self-Check Confirmation (updated)
Only `rehoming/badge-logic.php` and `rehoming/badge-calculation-cron.php` were modified this round. `admin-auth.php` and `chat-config.php` were read for reference only — never edited, never copied in full, and no credential value from either was written into any file in this task's output. The admin-token guard code is now reconciled against the literal real implementation rather than a documented-behavior approximation, with the one intentional difference (added `$_GET` support) explicitly flagged in-code and here, not left implicit. No dropdown menus in this task (unchanged). Scope Lock held in full.

## HAND-IN FORMAT RECONCILIATION (per `WORKER-DELIVERY-GUIDE.md`)

**What changed:** only the hand-in packaging, per explicit instruction — no code was redone, no logic was changed, no file outside this task's original scope was touched. `admin-auth.php`/`chat-config.php` (already read in the prior round) were not re-opened for this fix.

**What was produced:** `Rehoming-Platform-Task-B3.zip`, rooted at the site root, containing all four of this task's files at their (inferred — see Live path note above) live paths, plus the updated `Task-B3-Completion.md` above with the required four-column Files Changed table and dedicated New Files section.

**Before-you-send checklist, confirmed:**
- [x] Completion file has the required sections: Task Number, What Was Required, Files Changed (four columns), New Files, Bugs Found Outside Task Scope, Self-Check Confirmation. (The historical Code Changes/per-round sections are additional context, not a substitute for any required section — all required sections are present above them.)
- [x] No "Old code" snippet in this file is claimed as copied from a pre-existing live-site file — correctly, since none exists; every file in this task is new, so this checklist item doesn't apply to any block here, and no block was mislabeled as an existing-file diff.
- [x] Zip is rooted at the site root: `unzip -l Rehoming-Platform-Task-B3.zip` output below, confirmed by running it directly, not assumed:
  ```
  Archive:  Rehoming-Platform-Task-B3.zip
    Length      Date    Time    Name
  ---------  ---------- -----   ----
          0  2026-10-20 18:56   rehoming/
       6097  2026-10-20 18:56   rehoming/badge-calculation-cron.php
          0  2026-10-20 18:56   rehoming/merchants/
        695  2026-10-20 18:56   rehoming/merchants/.htaccess
          0  2026-10-20 18:56   rehoming/merchants/MERCH-00001/
        701  2026-10-20 18:56   rehoming/merchants/MERCH-00001/profile.json
      17396  2026-10-20 18:56   rehoming/badge-logic.php
  ---------                     -------
      24889                     7 files
  ```
  Every file path listed in the zip matches a path in the Files Changed table above, and every "New file" in that table is present in the zip — confirmed by direct comparison, not assumed.
- [x] Checked for forbidden content, directly, not assumed: `unzip -l` output above grepped for `config.php`, `pet.json`, `.env`, `applications/`, `uploads/`, `chat-media/`, `logs/` — zero matches. The file contents themselves were also grepped for secret-shaped strings (`password`, `secret`, `private_key`, `api_key`, hardcoded `ADMIN_TOKEN=` values, and the literal real database credential value from `chat-config.php`) — the only hits were the variable name `$admin_token` in my own code (which reads a token at runtime; it is not itself a secret value), no actual secret or credential text present.
- [x] Nothing outside this task's original scope was edited to produce this hand-in fix — only `Task-B3-Completion.md` was edited and `Rehoming-Platform-Task-B3.zip` was created; no file under `rehoming/` was changed in substance (only repackaged).
- [x] Tested with the tools available: `unzip -l` run directly against the real zip (output above, not reconstructed from memory), plus the grep checks above run directly against both the zip listing and the staged file contents before zipping.

## Self-Check Confirmation (final, hand-in fix)
Only `Task-B3-Completion.md` (edited) and `Rehoming-Platform-Task-B3.zip` (created) were touched to produce this hand-in fix. No file under this task's `rehoming/` scope was modified in content — `badge-logic.php`, `badge-calculation-cron.php`, `merchants/.htaccess`, and `merchants/MERCH-00001/profile.json` are byte-for-byte the same files already delivered at the end of the Fourth Verification Round; they were only copied into the zip, not changed. Every file in the zip is listed in the Files Changed table with a matching path, and every "New file" entry in that table is present in the zip, confirmed directly against the `unzip -l` output above rather than assumed. No secrets, `config.php`, `pet.json`, `.env`, or `applications/`/`uploads/` content is present anywhere in the zip, confirmed directly, not assumed.

## ZIP NAMING & PACKAGING RECONCILIATION (per updated guidance)

Two packaging corrections made to this hand-in, per the CEO's clarification that many workers were misreading the delivery guide:

1. **The completion file itself now ships inside the zip, alongside the new files**, not as a separate attachment. `Rehoming-Platform-Task-B3.zip` contains this `Task-B3-Completion.md` at its root, plus the `rehoming/` folder (site-root-relative) holding this task's four files.
2. **The zip is named after the exact project file name, not a task-invented name.** The project file is `Rehoming-Platform-Task-Assignments.md`; the project name portion of that exact file name is `Rehoming-Platform`. Per the required pattern (`[Project-Name][Task-Number].zip`, e.g. `project-x-task-3.zip`), this task's zip is named `Rehoming-Platform-Task-B3.zip` — the project name copied verbatim from the project file's own name (not re-derived from the document's title text, and not a casing/wording choice of my own), followed by `-Task-B3` for this task's ID, matching the given example's `-task-3` shape. The previous hand-in's self-chosen name, `Task-B3-NewFiles.zip`, is retired — this is the only zip now being sent.

## PENDING / OPEN ITEMS — CONSOLIDATED SUMMARY

(Everything below was already documented somewhere in this file across earlier rounds; collected here in one place per the instruction to make sure nothing pending, important, or doubtful is left undocumented before packaging.)

1. **`/rehoming/` folder path is inferred, not confirmed** — see the heavily-flagged section above. Needs explicit CEO/integrator sign-off before merge.
2. **Verified badge reconciliation with Task B2 is pending** — `calculateVerifiedBadge()` is built against the `verification_status` contract (`pending`/`verified`/`rejected`), not against a real B2 implementation, since none exists yet.
3. **Activity-tier placement count reconciliation with Task D1 is pending** — `getCompletedPlacementsCount()` stubs D1's not-yet-built placement-completion contract via a merchant-maintained running count.
4. **Response-rate reconciliation with the (not-yet-built) merchant↔visitor messaging system is pending** — `calculateResponseRate()` stubs this via a manually-set profile field; this is explicitly a different system from the existing customer-support `chat_messages` table, which was checked and confirmed unrelated.
5. **Charity/Rescue badge's admin review queue UI is not built by this task** — intentionally out of this task's file zone (belongs with Task B2's queue); only the underlying `charity_request` data fields and award logic exist here.
6. **Cron-sharing with Task A2 is unresolved** — A2 is spec'd against "the same cron pattern" as this task, but A2's own scheduled-job file doesn't exist yet; `badge-calculation-cron.php` was built standalone rather than guessing at a shared dispatcher on A2's behalf.
7. **The admin-token auth guard intentionally does one thing beyond the real `chatIsAdminAuthenticated()`** — it also accepts `$_GET['token']`, needed only for Hostinger's URL-triggered cron option, which the real function was never written to support. Flagged in-code and here so it isn't mistaken for an unreviewed deviation.
8. **Design note for future integrators, not a bug today:** `completed_placements_count` and `response_rate` must never be exposed as merchant-editable fields on any future settings form, or a merchant could self-report a false Trusted-tier badge. No such form exists yet, so nothing is broken today — this is a guardrail for whoever builds that form next.
9. **`php -l` could not be run** in this sandbox (no network access to install PHP) for any round of this task — all verification was done via manual brace/paren balance checks, Python-side logic/regex tracing, and JSON validation. A real `php -l` pass is recommended before deployment.

None of the above block this hand-in — they are documented dependencies/assumptions for the integrator to resolve at the appropriate point, not defects in this task's own delivered files.
