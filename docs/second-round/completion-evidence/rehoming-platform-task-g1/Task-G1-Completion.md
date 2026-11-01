# Task-G1 Completion

## Task Number
G1 — Donation/wishlist links + checkout-link integration

## What Was Required
Merchant profile settings needed two new optional link fields — a donation link and an Amazon wishlist
link — displayed on the merchant's public profile, and (only if the merchant opts in for that specific
listing) also on the listing page itself. Separately, a merchant needed a way to have the platform
auto-generate a Stripe Payment Link for their listing's adoption fee, shown as a "Pay Adoption Fee" button
on the listing. Per the task doc, the merchant profile page itself is Task B6's contract (a named
`MERCHANT-PROFILE: donation-links` section), not a file to request — built against that contract as a
standalone fragment, ready to merge in once B6 exists.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `pet-detail.html` | `pets/detail/index.html` | Changed | Yes — A1 also touches this file (display side of A1's new listing fields) |
| `create-fee-checkout-link.php` | `merchant/create-fee-checkout-link.php` ⚠️ NEW FOLDER | New | No |
| `update-merchant-donation-links.php` | `merchant/update-merchant-donation-links.php` ⚠️ NEW FOLDER | New | No |
| `merchant-profile-donation-links-section.html` | `_task-g1-reference/merchant-profile-donation-links-section.html` ⚠️ NOT A LIVE PATH | New | Yes — content for B6's page |
| `listing-edit-generate-payment-link-control.html` | `_task-g1-reference/listing-edit-generate-payment-link-control.html` ⚠️ NOT A LIVE PATH | New | Yes — content for A1's page |

### ⚠️ NEW FOLDER — `merchant/` (not found in either summary doc — please confirm)

Neither `adoption-website-project-summary.md` nor `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` has a `merchant/`
folder anywhere in their documented folder structure — this task is proposing it as new. Reasoning: this
codebase's existing pattern is that a functional backend PHP file is co-located with the page that calls
it (e.g. `adoption-application.php` lives in `apply/` alongside `apply/index.html`, not at site root).
Both of this task's PHP files are merchant-facing utility endpoints called from two different future
pages (B6's merchant profile, A1's listing edit panel) rather than from one single page, so no existing
folder is the obvious right home for them — a new shared `merchant/` folder seemed like the most sensible
new location, since A1's and B6's own future pages will likely want to live under that same folder too
(e.g. a plausible `merchant/profile/index.html`, `merchant/listings/edit/index.html` later). **This is a
proposal, not a confirmed path** — the fetch() URLs inside this task's own fragments have been written to
match it (`/merchant/create-fee-checkout-link.php`, `/merchant/update-merchant-donation-links.php`) so the
delivered files are internally consistent, but if a different folder is decided, those two fetch() calls
need a matching one-line update, which is a small, contained follow-up once the real path is confirmed —
flagging now rather than guessing silently and rather than leaving an inconsistency unflagged.

### ⚠️ NOT A LIVE PATH — `_task-g1-reference/`

`merchant-profile-donation-links-section.html` and `listing-edit-generate-payment-link-control.html` are
**not real pages** — both say so explicitly in their own header comments. They are content prepared for
Task B6 and Task A1 to merge directly into pages that don't exist yet (this is exactly the situation the
task doc itself anticipated when it described G1's file zone as "`pet-detail.html`, plus B6's merchant
profile page at the named section," not a file to request as its own page). Since the instruction is that
every new file must ship inside this zip regardless, `_task-g1-reference/` is a deliberately
non-production-looking holding folder so the integrator doesn't mistake either file for a deployable route
— **the leading underscore is intentional and this folder should never be unzipped onto the live, public
site as its own URL.** Per the live-page convention, if either of these were a real deployable page they'd
need `index.html` naming inside a route folder; they're deliberately *not* named that way here, specifically
so nothing about their packaging implies they're ready to go live as-is. Recommended handling: once A1 or
B6 publishes their real page, copy the relevant file's content into the named section it documents in its
own header, then this staging folder can be deleted — it was never meant to persist as a permanent part of
the site structure.

## Code Changes

### `pet-detail.html` → live path `pets/detail/index.html`

**Block 1 — CSS.** Unchanged since the first delivery.

<!-- TASK-G1 START -->
**Old code:**
```css
.pc-pet-id-row strong {
  font-family: monospace;
  color: var(--navy);
  font-size: 13px;
  margin-left: 4px;
}

/* ─── Fixed Bottom Bar ───────────────────────────────────────────────────────── */
```
**New code:**
```css
.pc-pet-id-row strong {
  font-family: monospace;
  color: var(--navy);
  font-size: 13px;
  margin-left: 4px;
}

/* TASK-G1 START — Support This Pet's Merchant: donation/wishlist links +
   Pay Adoption Fee button. Reuses the existing .pc-info-section /
   .pc-btn patterns already defined above (Section 8 of
   adoption-website-project-summary.md) rather than introducing new
   component styles, per Warning & Instructions Rule 7. Kept on the core
   Navy/Gold/Orange palette deliberately — the design system's
   donation/emotional palette (Romantic Blend / Magenta Bloom) is
   explicitly excluded from transactional/functional UI like this, and
   this page's header/footer/palette are locked per Directive 9. */
.pc-support-merchant__row {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 4px;
}
.pc-support-merchant__row .pc-btn { flex: 1 1 auto; justify-content: center; }
.pc-support-merchant__note {
  font-size: 12px;
  color: var(--muted);
  margin-top: 10px;
  line-height: 1.5;
}
/* TASK-G1 END */

/* ─── Fixed Bottom Bar ───────────────────────────────────────────────────────── */
```
<!-- TASK-G1 END -->

**Block 2 — HTML.** Unchanged since the first delivery.

<!-- TASK-G1 START -->
**Old code:**
```html
          </div>

          <!-- Adopted message -->
```
**New code:**
```html
          </div>

          <!-- TASK-G1 START — Support This Pet's Merchant section: optional
               donation link, Amazon wishlist link (both merchant-opt-in,
               per Task G1 spec), and a Stripe-generated "Pay adoption fee"
               button. Whole section is hidden by default and only shown
               once populateSupportMerchantSection() finds at least one
               thing to display, so pets with nothing configured (like
               Pet Center's own public listings, which aren't
               merchant-posted) never show an empty section. -->
          <div class="pc-info-section" id="supportMerchantSection" style="display:none;">
            <div class="pc-info-section__title">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
              Support <span id="supportMerchantPetName">This Pet</span>
            </div>

            <div class="pc-support-merchant__row">
              <!-- Donation link — only rendered if the merchant opted in for this listing (show_donation_link) and set a URL on their profile -->
              <a href="#" id="supportDonationBtn" class="pc-btn pc-btn--ghost pc-btn--sm" style="display:none;" target="_blank" rel="noopener noreferrer nofollow">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                Donate to <span id="supportDonationMerchantName">this merchant</span>
              </a>
              <!-- Wishlist link — only rendered if the merchant opted in for this listing (show_wishlist_link) and set a URL on their profile -->
              <a href="#" id="supportWishlistBtn" class="pc-btn pc-btn--ghost pc-btn--sm" style="display:none;" target="_blank" rel="noopener noreferrer nofollow">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13"/><path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"/><path d="M7.5 8a2.5 2.5 0 0 1 0-5C11 3 12 8 12 8z"/><path d="M16.5 8a2.5 2.5 0 0 0 0-5C13 3 12 8 12 8z"/></svg>
                View Amazon Wishlist
              </a>
              <!-- Pay adoption fee — only rendered if the merchant has generated a Stripe Payment Link for this specific listing's fee (fee_checkout_url) -->
              <a href="#" id="supportPayFeeBtn" class="pc-btn pc-btn--orange pc-btn--sm" style="display:none;" target="_blank" rel="noopener noreferrer">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                Pay Adoption Fee<span id="supportPayFeeAmount"></span>
              </a>
            </div>
            <div class="pc-support-merchant__note">
              Donation and wishlist links are provided directly by the listing's merchant and are not managed or vetted by Pet Center. The adoption fee button opens a secure Stripe payment page in a new tab.
            </div>
          </div>
          <!-- TASK-G1 END -->

          <!-- Adopted message -->
```
<!-- TASK-G1 END -->

**Block 3 — JS call site.** Unchanged since the first delivery.

<!-- TASK-G1 START -->
**Old code:**
```js
        // Populate all pet info fields
        populatePetInfo(pet, category);
        
        // Setup adoption actions
```
**New code:**
```js
        // Populate all pet info fields
        populatePetInfo(pet, category);

        // TASK-G1 START — render donation/wishlist links + Pay Adoption Fee button
        populateSupportMerchantSection(pet);
        // TASK-G1 END

        // Setup adoption actions
```
<!-- TASK-G1 END -->

**Block 4 — JS function definition.** Revised in the second verification round — see that section below
for why. This is the current, final version; it supersedes anything shown for this block in any earlier
copy of this file.

<!-- TASK-G1 START -->
**Old code:**
```js
  function setupAdoptionActions(pet, category, petId) {
```
**New code:**
```js
  // TASK-G1 START — Support This Pet's Merchant section (donation link,
  // Amazon wishlist link, Pay Adoption Fee button).
  //
  // Field contract this reads (SHARED CONTRACTS / stub — none of these
  // fields exist on A1's real listing schema or a real merchant record
  // yet, since A1 and B6 haven't been merged at the time this task was
  // built; this task builds against the local stub shape below and
  // reconciles at the integration checkpoint once A1's and B6's
  // completion files exist, per the platform's SHARED CONTRACTS
  // mechanism):
  //   pet.show_donation_link   (bool)   — per-listing opt-in toggle this
  //                                        task's spec calls for; NOT YET
  //                                        a real field on A1's posting
  //                                        panel — flagged in this task's
  //                                        completion file for A1 to add
  //                                        a form toggle for at
  //                                        reconciliation.
  //   pet.show_wishlist_link   (bool)   — same as above, wishlist toggle.
  //   pet.fee_checkout_url          (string) — Stripe Payment Link generated
  //                                             by create-fee-checkout-link.php.
  //   pet.fee_checkout_url_amount   (number) — the adoption_fee value the
  //                                             link above was generated
  //                                             for, snapshotted at
  //                                             generation time.
  //   pet.fee_checkout_url_currency (string) — the currency_code the link
  //                                             above was generated in.
  //   pet.merchant             (object) — stub merchant record:
  //       { name, donation_link, wishlist_link }
  //   pet.currency_code        (string) — A1's contract (USD/CAD/GBP/AUD);
  //                                        falls back to USD if absent
  //                                        since A1 isn't merged yet.
  function populateSupportMerchantSection(pet) {
    var section = document.getElementById('supportMerchantSection');
    if (!section) return;

    var merchant = pet.merchant || null;
    var shown = false; // tracks whether at least one control ends up visible

    // Section heading uses the pet's name, matching the pattern already
    // used elsewhere on this page (e.g. compatTitle in populatePetInfo).
    var nameSpan = document.getElementById('supportMerchantPetName');
    if (nameSpan) nameSpan.textContent = (pet.name || 'This Pet') + "'s Rehoming";

    // Donation link — requires both the merchant's own URL AND this
    // listing's opt-in flag to be true (merchant may have a donation
    // link on their profile but choose not to show it on every listing).
    var donationBtn = document.getElementById('supportDonationBtn');
    if (donationBtn) {
      var showDonation = pet.show_donation_link === true && merchant && isValidHttpUrl(merchant.donation_link);
      if (showDonation) {
        donationBtn.href = merchant.donation_link;
        var donationNameEl = document.getElementById('supportDonationMerchantName');
        if (donationNameEl) donationNameEl.textContent = merchant.name || 'this merchant';
        donationBtn.style.display = 'inline-flex';
        shown = true;
      } else {
        donationBtn.style.display = 'none';
      }
    }

    // Wishlist link — same opt-in pattern as donation link above.
    var wishlistBtn = document.getElementById('supportWishlistBtn');
    if (wishlistBtn) {
      var showWishlist = pet.show_wishlist_link === true && merchant && isValidHttpUrl(merchant.wishlist_link);
      if (showWishlist) {
        wishlistBtn.href = merchant.wishlist_link;
        wishlistBtn.style.display = 'inline-flex';
        shown = true;
      } else {
        wishlistBtn.style.display = 'none';
      }
    }

    // Pay Adoption Fee — only appears once the merchant has actually
    // generated a Stripe Payment Link for this listing via
    // create-fee-checkout-link.php (pet.fee_checkout_url), AND that link
    // was generated for the fee/currency the listing *currently* shows.
    // create-fee-checkout-link.php snapshots fee_checkout_url_amount and
    // fee_checkout_url_currency at generation time rather than writing
    // adoption_fee itself (adoption_fee stays owned by the normal listing
    // edit flow — see that file's header for why). If a merchant edits
    // their fee afterward on the normal edit form, that snapshot goes
    // stale, and without this check the button would still silently
    // charge the OLD amount while the page displays the NEW one — so a
    // mismatch here hides the button entirely rather than show a link
    // that could charge the wrong amount. Amount shown next to the
    // button label uses the same currency-symbol map F7 is expected to
    // own; a small local fallback map is used here since F7 hasn't
    // published a shared helper yet — flagged for reconciliation.
    var payFeeBtn = document.getElementById('supportPayFeeBtn');
    if (payFeeBtn) {
      var feeNum = parseFloat(pet.adoption_fee);
      var linkAmount = parseFloat(pet.fee_checkout_url_amount);
      var linkCurrency = pet.fee_checkout_url_currency || 'USD';
      var currentCurrency = pet.currency_code || 'USD';
      var linkIsCurrent = !isNaN(feeNum) && !isNaN(linkAmount)
        && feeNum === linkAmount
        && linkCurrency === currentCurrency;

      if (isValidHttpUrl(pet.fee_checkout_url) && linkIsCurrent) {
        payFeeBtn.href = pet.fee_checkout_url;
        var amountEl = document.getElementById('supportPayFeeAmount');
        if (amountEl) {
          var symbols = { USD: '$', CAD: 'CA$', GBP: '£', AUD: 'AU$' };
          var symbol = symbols[currentCurrency] || '$';
          amountEl.textContent = ' (' + symbol + feeNum.toLocaleString('en-US') + ')';
        }
        payFeeBtn.style.display = 'inline-flex';
        shown = true;
      } else {
        payFeeBtn.style.display = 'none';
      }
    }

    // Only reveal the whole section if there's actually something to show.
    section.style.display = shown ? 'block' : 'none';
  }

  // Small shared URL guard so a malformed/empty merchant-supplied link
  // never becomes a live href pointing at "#" + garbage, or a
  // javascript: URL — used by all three controls above.
  function isValidHttpUrl(value) {
    if (!value || typeof value !== 'string') return false;
    try {
      var u = new URL(value);
      return u.protocol === 'http:' || u.protocol === 'https:';
    } catch (e) {
      return false;
    }
  }
  // TASK-G1 END

  function setupAdoptionActions(pet, category, petId) {
```
<!-- TASK-G1 END -->

## New files

All four are in the zip, alongside this completion file itself.

- `create-fee-checkout-link.php` — New file — in the zip at `merchant/create-fee-checkout-link.php`
  (⚠️ new folder — see note under Files Changed). Backend: generates a Stripe Payment Link for a listing's
  existing adoption fee and stores it on the listing record.
- `update-merchant-donation-links.php` — New file — in the zip at
  `merchant/update-merchant-donation-links.php` (⚠️ new folder — see note under Files Changed). Backend:
  GET/POST for a merchant's saved donation/wishlist link settings.
- `merchant-profile-donation-links-section.html` — New file — in the zip at
  `_task-g1-reference/merchant-profile-donation-links-section.html` (⚠️ not a live path — see note under
  Files Changed). Content to merge into B6's page once it exists.
- `listing-edit-generate-payment-link-control.html` — New file — in the zip at
  `_task-g1-reference/listing-edit-generate-payment-link-control.html` (⚠️ not a live path — see note under
  Files Changed). Content to merge into A1's page once it exists.

## Bugs Found Outside Task Scope
None found in any file outside this task's own files. (Two cross-task dependency gaps were found and are
documented, not as bugs in someone else's code, but as fields/anchors G1 needs that other tasks haven't
published yet — see the Second Verification Round section and the earlier revision notes inside
`create-fee-checkout-link.php`'s and `pet-detail.html`'s own comments: A1 needs `show_donation_link` /
`show_wishlist_link` fields and a named anchor comment on its posting panel; B1's merchant session system
doesn't exist yet for either PHP file's auth to attach to.)

## Self-Check Confirmation
- Only this task's own files were touched or created: `pet-detail.html` (existing file, edited) and four
  new files. `admin-dashboard.html`, `pet.json`, and all project-summary/design-system docs were read only,
  never edited, and nothing outside this task's original scope was changed in this hand-in pass.
- Every block in `pet-detail.html`'s Code Changes section above is wrapped in `<!-- TASK-G1 START/END -->`
  (HTML/CSS) or `// TASK-G1 START/END` (JS) anchors, and each carries its own explanatory comment.
- Every "Old code" snippet above was copied character-for-character from the pristine original
  `pet-detail.html` this task was given (re-diffed against that original file, not against any
  already-edited copy, specifically for this hand-in) and confirmed to appear exactly once in it
  (checked with `grep -c` on each anchor line before finalizing this file).
- Per the corrected hand-in process, this completion file and all four new files ship together in one
  zip, `rehoming-platform-task-g1.zip` — project name taken from the shared task-assignments document's
  own filename (`Rehoming-Platform-Task-Assignments.md` → `rehoming-platform`, lowercased/hyphenated to
  match the given naming example), task number `g1`, so every worker reading the same task-assignments
  file derives the same project-name string independently.
- Zip contents confirmed with `unzip -l` before sending: `Task-G1-Completion.md` at the zip root, the two
  PHP files under `merchant/`, the two reference-only files under `_task-g1-reference/` — five files
  total, every one of them listed above in Files Changed with a matching path. `pet-detail.html` is **not**
  in the zip, since it's a changed existing file (documented as old/new snippets above instead), per the
  rule that a changed file never goes in the zip.
- Confirmed nothing in the zip is a secret, `config.php`, `pet.json`, `.env`, or anything under
  `applications/` or `uploads/`: all files were grepped for hardcoded keys/passwords (none found — the
  Stripe key is read via `getenv()`, never hardcoded) and for any reference to `applications/` or
  `uploads/` paths (none found); no filename matches any forbidden name.
- Tested with Playwright (headless Chromium) against three fixtures after this round's edit: a listing
  with a fresh, currency/amount-matching payment link (section and all three buttons show, correct hrefs
  and formatted fee), a listing with a deliberately stale link — fee changed from 250 to 400 after the
  link was generated (Pay Adoption Fee button correctly hides; nothing else on the page affected), and the
  real unmodified Pet Center listing provided at the start of this task (section stays fully hidden, no
  regression). JS syntax-checked (`new Function()` against every `<script>` block) and div-tag balance
  re-verified on the final file after this round's edit, not just before it.

## SECOND VERIFICATION ROUND

A full re-read of both project-summary docs and the task doc, done after the first delivery, found four
real problems — three bugs and one missing piece — all fixed in the version shown above:

1. **Bug — redundant/conflicting fee-write path.** The first version of `create-fee-checkout-link.php`
   accepted its own `fee_amount` parameter and wrote it onto `adoption_fee`. Cross-checking against
   `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` (`update-pet.php`) showed `adoption_fee` is the listing's one
   existing, canonical fee field — already present on every pet and already editable through the normal
   edit flow — not a new field this task introduces. A second write path for the same field meant a
   merchant could change their fee on the normal form while an already-generated Payment Link kept
   charging the old amount, with no indication to anyone that the two had drifted apart. Fixed: the
   endpoint now only *reads* `adoption_fee` (and errors clearly if it's unset/zero, rather than silently
   creating a $0 link) and snapshots the amount/currency it generated the link for
   (`fee_checkout_url_amount`, `fee_checkout_url_currency`). `pet-detail.html`'s Block 4 above now compares
   that snapshot against the listing's current `adoption_fee`/`currency_code` and hides the Pay button on
   any mismatch. Verified with the three Playwright fixtures described in Self-Check above.
2. **Bug — path-traversal-weak ID sanitization.** Both PHP files used `basename()` on `category`/`pet_id`/a
   merchant ID before building a filesystem path. `basename('..')` returns `'..'` unchanged, so it doesn't
   actually stop a crafted `category=..` from reaching the path. Fixed to the pattern this codebase
   documents elsewhere for the same situation: sanitize to `[A-Za-z0-9-]` only via regex (not
   alphanumeric-only — real IDs/categories contain hyphens, e.g. `DOG-00001`, `small-mammals`), then
   independently verify the resolved real path is still inside the intended base directory.
3. **Bug — inconsistent response envelope.** Every existing endpoint documented in
   `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` returns `{success: true/false, ...}`. Several error responses in
   both of this task's PHP files were missing the explicit `success: false`. Fixed across both files.
4. **Missing piece — `create-fee-checkout-link.php` had no caller.** The first delivery built the backend
   that generates the Stripe Payment Link but nothing a merchant could actually click to invoke it. New
   file: `listing-edit-generate-payment-link-control.html` (see New files above).

Also corrected: both PHP-calling fragments previously used a custom `X-Merchant-ID` header / an
unauthenticated GET; both now pass a `merchant_token` the way this codebase's documented GET endpoints
pass their credential (as a param, not a header) — still a placeholder pending B1's real session system,
but shaped the way the rest of this codebase expects a credential to arrive.

Separately, after this second round was already complete, the Stripe-secret-storage question from the
first hand-in was resolved (not left open): `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s Security Features
section states the admin token is "stored in .env file (never in code)" — `create-fee-checkout-link.php`
already read `STRIPE_SECRET_KEY` via `getenv()`, matching that exact convention, so the leftover
"TODO(CEO): confirm where secrets are stored" comment next to it was removed as unnecessary. The
nonprofit-Stripe-rate question remains open deliberately — it's a real-world account/business fact (has
Pet Center or the merchant actually been approved for Stripe's nonprofit pricing), not a coding decision,
so there's no code default to pick; the code behaves identically either way regardless of the answer.

## Pending / Open Items

Everything still unresolved or needing a decision, gathered in one place:

1. **`merchant/` folder — needs confirmation.** New, not in either summary doc. Proposed location for
   both this task's PHP endpoints. See the ⚠️ NEW FOLDER note under Files Changed for full reasoning. If a
   different location is chosen, two fetch() URLs need a one-line update to match.
2. **Two reference-only files have no real live destination yet.** `_task-g1-reference/` in the zip is a
   non-production holding location only, not a page. Their real destination is inside A1's and B6's pages,
   once those exist — see the ⚠️ NOT A LIVE PATH note under Files Changed.
3. **A1 needs two new listing fields this task depends on:** `show_donation_link` and `show_wishlist_link`
   (bool), plus a named anchor comment on its posting panel so `listing-edit-generate-payment-link-control.html`
   has a confirmed spot to be merged into (B6 already has one; A1 doesn't yet). Not something I can add
   myself under Scope Lock — A1 owns that file.
4. **B1's merchant session/auth system doesn't exist yet.** Both PHP files have a placeholder
   `validateMerchantToken()` that trusts whatever token it's given, clearly marked `TODO(reconcile with
   B1)` in the files themselves. Must be wired to real session validation before this goes live — as
   written today, anyone could call either endpoint for any listing/merchant.
5. **No `merchant_id` field exists yet on the listing schema** to check that the merchant calling
   `create-fee-checkout-link.php` actually owns the listing they're generating a link for. Depends on
   A1/A2's schema. Also marked `TODO` directly in that file.
6. **Nonprofit Stripe rate eligibility** — open business question, not a code question (see above).
   Code behaves identically either way.

Nothing above blocks this hand-in — all are flagged, with a clear owner (CEO, A1, or B1) and a clear
reason they can't be resolved from inside this task's own scope.
