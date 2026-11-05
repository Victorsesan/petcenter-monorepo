# Task A1 — Completion File

## Task Number
TASK A1 — Merchant pet-posting panel: clone from admin-dashboard.html + add new fields

## What Was Required
Clone `admin-dashboard.html`'s existing "Publish Pets" panel (`id="publishPanel"`, `addPetForm()`) as the merchant-facing posting panel — per Rule 7, not a new build — then add seven new fields to the pet-listing data model (`health_status`, `temperament`, `reason_for_rehoming`, `spay_neuter_status`, `listing_type` with conditional `foster_duration_estimate`/`foster_covers`, `currency_code`, and `is_urgent`). Enforce real minimum-character-length validation on the three required free-text fields (not just "required"), and display those three text fields as their own visually distinct labeled sections on the pet-detail page — separate from the existing `description_full`/"About" block.

**Self-caught scope error, corrected mid-task:** my first attempt invented a standalone form with bespoke styling instead of cloning the real admin panel — wrong, per Rule 7. Corrected before this submission: the posting panel now clones `admin-dashboard.html`'s real CSS classes, field template, category-selection pattern, and upload machinery, with A1's fields merged into that same template. Full before/after detail is in the Code Changes section and the Verification Rounds at the end of this file.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `pet.json` | `adoptions/pets/{category}/{pet_id}/pet.json` | Changed | Yes — Task A2 adds different fields (`grouped_with`, `last_inquiry_date`, `needs_extra_love`) to this same file in parallel; reconcile at integration checkpoint once A2's completion file exists. |
| `pet-detail.html` | `pets/detail/index.html` | Changed | No other task currently in this file zone. |
| `admin-dashboard.html` | `admin/index.html` | **Read-only reference — not modified.** Cloned from, per Rule 7; the admin file itself is untouched. | — |
| `pet-posting-form-v2.html` (new merchant posting panel) | **Unconfirmed — see "Open Question" below.** Proposed: `rehoming/list-a-pet/index.html` | New | Task F4 (guided listing hints) and Task F7 (currency display) both build against this file in parallel per Shared Contracts; not shared at time of this submission. |

**Open question — live path not confirmed, flagged rather than guessed silently:** neither `adoption-website-project-summary.md`'s folder-structure section nor `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` assigns a folder path for a new Rehoming Platform merchant-facing page. The closest existing reference is `signin/index.html`, documented as "sign-in entry point for the future Rehoming Platform portal" — implying the rest of that portal's pages (including this one) haven't been placed in the tree yet. The zip ships the file at my best-guess path, `rehoming/list-a-pet/index.html` (sibling of `signin/`, consistent with Directive 5's folder-per-page rule), but this is a proposal, not a confirmed path — please confirm or correct before the integrator merges it, and I'll resubmit the zip at the corrected path if needed.

## Code Changes

### `pet.json`

`pet.json` has no comment syntax, so it cannot carry `<!-- TASK-A1 START/END -->` anchors — this table row and the diff below are its traceable boundary for A2's reconciliation instead.

**Old code** (full original file, for exact-match reference — nothing in it is multi-occurrence so each line below is matched individually by the integrator):
```json
{
    "pet_id": "BIRD-00032",
    "id": "BIRD-00032",
    "name": "Kira",
    "category": "birds",
    "species": "Bird",
    "breed": "White Belly Caique",
    "age_years": "2.3",
    "size": "Medium",
    "gender": "Female",
    "adoption_fee": "650.99",
    "status": "Available",
    "created_at": "2024-06-19T00:00:00Z",
    "updated_at": "2024-06-19T00:00:00Z",
    "description_short": "Kira is a young White Belly Caique. Energetic and curious, though can be unpredictable with handling.",
    "description_full": "Now available at Pet Center Foundation, Kira is a 2 year 3 months old female White Belly Caique. She's unpredictable with handling and can bite when startled or overstimulated. She's been adjusting well to our staff's patient training and has become more confident in routine interactions. She consumes nectar and fruits readily with good appetite. Requires experienced Caique owners with understanding of young bird behavior and patience for her developmental needs.",
    "health_info": "Healthy birds. Fully vaccinated.",
    "multiple_adoptable_options": "no",
```

**New code:**
```json
{
    "pet_id": "BIRD-00032",
    "id": "BIRD-00032",
    "name": "Kira",
    "category": "birds",
    "species": "Bird",
    "breed": "White Belly Caique",
    "age_years": "2.3",
    "size": "Medium",
    "gender": "Female",
    "adoption_fee": "650.99",
    "currency_code": "USD",
    "status": "Available",
    "created_at": "2024-06-19T00:00:00Z",
    "updated_at": "2024-06-19T00:00:00Z",
    "description_short": "Kira is a young White Belly Caique. Energetic and curious, though can be unpredictable with handling.",
    "description_full": "Now available at Pet Center Foundation, Kira is a 2 year 3 months old female White Belly Caique. She's unpredictable with handling and can bite when startled or overstimulated. She's been adjusting well to our staff's patient training and has become more confident in routine interactions. She consumes nectar and fruits readily with good appetite. Requires experienced Caique owners with understanding of young bird behavior and patience for her developmental needs.",
    "health_info": "Healthy birds. Fully vaccinated.",
    "health_status": "Fully vaccinated and in excellent health. No known conditions. Not currently on any medication.",
    "temperament": "High energy and curious, but can be unpredictable with handling. Does best with an experienced Caique owner who understands young bird behavior.",
    "reason_for_rehoming": "Owner's work schedule changed and can no longer provide the daily attention a young Caique needs.",
    "spay_neuter_status": "unknown",
    "listing_type": "permanent",
    "is_urgent": false,
    "multiple_adoptable_options": "no",
```
(`health_status`/`temperament`/`reason_for_rehoming` carry example values here since this is the one real `pet.json` sample provided; on a real listing these come from the merchant's own form submission. `foster_duration_estimate`/`foster_covers` are intentionally absent from this example — this pet's `listing_type` is `permanent`, and those two fields only exist on a record when `listing_type` is `foster`, per the schema's own "present only if foster" rule.)

### `pet-detail.html`  →  live path `pets/detail/index.html`

**Block 1 — display markup.**

<!-- TASK-A1 START -->
**Old code:**
```html
      <!-- Full description section -->
      <div id="fullDescSection" style="margin-top:48px;display:none;">
        <h2 style="font-family:'Cormorant Garamond',serif;font-size:1.8rem;color:#10214b;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #d7bd88;">About <span id="fullDescName"></span></h2>
        <p id="fullDescription" style="color:#7a6d5f;font-size:15px;line-height:1.8;max-width:760px;"></p>
      </div>

      <!-- What's Included Section -->
```
**New code:**
```html
      <!-- Full description section -->
      <div id="fullDescSection" style="margin-top:48px;display:none;">
        <h2 style="font-family:'Cormorant Garamond',serif;font-size:1.8rem;color:#10214b;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #d7bd88;">About <span id="fullDescName"></span></h2>
        <p id="fullDescription" style="color:#7a6d5f;font-size:15px;line-height:1.8;max-width:760px;"></p>
      </div>

      <!-- TASK-A1 START — Structured content sections (Task A1: listing schema
           expansion). Each renders as its own labeled section, visually
           distinct from the free-text "About" block above, per task spec.
           Hidden by default; only shown once a matching field is present so
           an older pet.json with no A1 fields doesn't render empty boxes. -->
      <div id="pcA1Sections" style="margin-top:40px;display:flex;flex-direction:column;gap:28px;max-width:760px;">

        <div id="pcHealthStatusSection" style="display:none;">
          <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.3rem;color:#10214b;margin:0 0 10px;padding-bottom:8px;border-bottom:1.5px solid #d7bd88;">Health &amp; Medical History</h3>
          <p id="pcHealthStatusText" style="color:#7a6d5f;font-size:14.5px;line-height:1.7;margin:0;"></p>
        </div>

        <div id="pcTemperamentSection" style="display:none;">
          <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.3rem;color:#10214b;margin:0 0 10px;padding-bottom:8px;border-bottom:1.5px solid #d7bd88;">Temperament</h3>
          <p id="pcTemperamentText" style="color:#7a6d5f;font-size:14.5px;line-height:1.7;margin:0;"></p>
        </div>

        <div id="pcReasonForRehomingSection" style="display:none;">
          <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.3rem;color:#10214b;margin:0 0 10px;padding-bottom:8px;border-bottom:1.5px solid #d7bd88;">Why This Pet Is Being Rehomed</h3>
          <p id="pcReasonForRehomingText" style="color:#7a6d5f;font-size:14.5px;line-height:1.7;margin:0;"></p>
        </div>

        <div id="pcListingDetailsSection" style="display:none;">
          <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.3rem;color:#10214b;margin:0 0 10px;padding-bottom:8px;border-bottom:1.5px solid #d7bd88;">Listing Details</h3>
          <div style="display:flex;flex-wrap:wrap;gap:24px;font-size:14.5px;color:#7a6d5f;">
            <div><strong style="color:#10214b;">Spay/Neuter status:</strong> <span id="pcSpayNeuterStatusText">—</span></div>
            <div><strong style="color:#10214b;">Listing type:</strong> <span id="pcListingTypeText">—</span></div>
          </div>
          <div id="pcFosterDetails" style="display:none;margin-top:10px;font-size:14.5px;color:#7a6d5f;">
            <div><strong style="color:#10214b;">Estimated foster duration:</strong> <span id="pcFosterDurationText">—</span></div>
            <div style="margin-top:4px;"><strong style="color:#10214b;">What's covered:</strong> <span id="pcFosterCoversText">—</span></div>
          </div>
        </div>

      </div>
      <!-- TASK-A1 END -->

      <!-- What's Included Section -->
```
<!-- TASK-A1 END -->

**Block 2 — population script.**

<!-- TASK-A1 START -->
**Old code:**
```javascript
    // Pet ID
    var petIdEl = document.getElementById('petId');
    if (petIdEl) petIdEl.textContent = petId;
```
**New code:**
```javascript
    // TASK-A1 START — Populate the new structured content sections (Task A1).
    // Each section only shows if its field is actually present, since older
    // pets published before this schema addition won't have these fields.
    var spayNeuterLabels = { spayed_neutered: 'Spayed / Neutered', not_yet: 'Not Yet', unknown: 'Unknown' };
    var listingTypeLabels = { permanent: 'Permanent', foster: 'Foster' };

    function showA1TextSection(sectionId, textId, value) {
      var section = document.getElementById(sectionId);
      var textEl = document.getElementById(textId);
      if (section && textEl && value) {
        textEl.textContent = value;
        section.style.display = 'block';
      }
    }
    showA1TextSection('pcHealthStatusSection', 'pcHealthStatusText', pet.health_status);
    showA1TextSection('pcTemperamentSection', 'pcTemperamentText', pet.temperament);
    showA1TextSection('pcReasonForRehomingSection', 'pcReasonForRehomingText', pet.reason_for_rehoming);

    // Listing Details section (spay/neuter status + listing type + foster fields)
    if (pet.spay_neuter_status || pet.listing_type) {
      var listingSection = document.getElementById('pcListingDetailsSection');
      var spayEl = document.getElementById('pcSpayNeuterStatusText');
      var typeEl = document.getElementById('pcListingTypeText');
      if (spayEl) spayEl.textContent = spayNeuterLabels[pet.spay_neuter_status] || pet.spay_neuter_status || '—';
      if (typeEl) typeEl.textContent = listingTypeLabels[pet.listing_type] || pet.listing_type || '—';
      if (pet.listing_type === 'foster' && (pet.foster_duration_estimate || pet.foster_covers)) {
        var fosterWrap = document.getElementById('pcFosterDetails');
        var durEl = document.getElementById('pcFosterDurationText');
        var covEl = document.getElementById('pcFosterCoversText');
        if (durEl) durEl.textContent = pet.foster_duration_estimate || '—';
        if (covEl) covEl.textContent = pet.foster_covers || '—';
        if (fosterWrap) fosterWrap.style.display = 'block';
      }
      if (listingSection) listingSection.style.display = 'block';
    }
    // TASK-A1 END

    // Pet ID
    var petIdEl = document.getElementById('petId');
    if (petIdEl) petIdEl.textContent = petId;
```
<!-- TASK-A1 END -->

Both blocks were verified unique-in-file against the original before submission (each "Old code" anchor occurs exactly once in `pet-detail.html`).

## ⚠️ NEW FOLDER TO CREATE — INTEGRATOR, READ THIS FIRST

**This task introduces a folder that does NOT exist in either `adoption-website-project-summary.md` or `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`.**

| New folder to create | Where | File inside | Resulting URL |
|---|---|---|---|
| **`rehoming/`** (top-level parent folder, **new**) | `public_html/adopt/rehoming/` | — | `adopt.petcenterco.com/rehoming/` (not a page itself) |
| **`rehoming/list-a-pet/`** (**new**) | `public_html/adopt/rehoming/list-a-pet/` | **`index.html`** | `adopt.petcenterco.com/rehoming/list-a-pet/` |

- The page is deployed as **`index.html`** inside its folder (strict live-deployment naming). The source/working name was `pet-posting-form-v2.html`; it is renamed to `index.html` inside the zip.
- **Path is a PROPOSAL, not confirmed.** No document assigns a location for the merchant posting page. Nearest precedent: `signin/index.html` ("entry point for the future Rehoming Platform portal"). If the CEO/VDM picks a different folder, move `rehoming/list-a-pet/index.html` there; relative references inside the file (see Pending #2) must be re-checked after any move.

## ⏳ PENDING / OPEN ITEMS (things that still make this work incomplete or uncertain)

1. **Live path unconfirmed** — see the new-folder box above. Needs a CEO/VDM decision.
2. **`video-upload-manager.js` not supplied / not in the zip.** The cloned video-upload flow calls `VideoUploadManager.startUpload(...)` and loads `./video-upload-manager.js` by relative path. That file was never on A1's request list, is an existing file (not mine to include), and the page's relative `src` only works if the file sits in the same folder as the page. Until resolved, video upload on this page will not work. Decision needed: where the shared script lives for non-admin pages.
3. **Backend endpoints do not exist yet.** The page posts to `merchant-upload-temp.php` and `merchant-publish-listing.php` (merchant-scoped equivalents of the admin endpoints, which require an admin session merchants won't have). These are **not built and not in this task's scope**; submission and image/video upload cannot work end-to-end until another task builds them. Field names/tokens (`single_token`, `bulk_tokens`, `video_token`) match `publish-pet.php`.
4. **Auth check omitted.** Admin's `checkTokenBeforeAction`/`adminToken` calls were removed (merchants have no admin token); a `TODO` sits at each call site. Merchant auth must be wired by the task that owns merchant sessions.
5. **`currency_code` is hardcoded to `"USD"`** in the form (hidden input, `TODO` in code). The spec says it auto-fills from the merchant's stored country, but no merchant-country data exists yet. Needs the merchant-profile task.
6. **`pet.json` is shared with Task A2** (different fields added in parallel). Integrator must merge both sets into one object; no conflict in field names.
7. **Display of `is_urgent` on `pet-detail.html` intentionally not added** (not in A1's display requirement; likely A3/F5 territory).
8. **Existing `spayed_neutered` field vs. new `spay_neuter_status`** both exist in the schema (flagged earlier; spec-directed, but a future cleanup decision for the VDM).

## New files

- **`rehoming/list-a-pet/index.html`** (proposed path — see Open Question above) — New file, in the zip. The merchant-facing pet-posting panel: a clone of `admin-dashboard.html`'s "Publish Pets" panel (category selector, `addPetForm()`'s field template, image/video upload machinery) with A1's seven new fields merged in, plus real min-length/required validation for those fields and the design system's Section 24 custom-dropdown shell in place of native `<select>` elements. Full side-by-side kept/changed/removed/added detail against the admin panel it was cloned from is in the Verification Rounds below.

## Bugs Found Outside Task Scope

One real gap found and flagged, not fixed, per Scope Lock: `admin-dashboard.html` loads `<script src="./video-upload-manager.js" defer></script>` in its `<head>`, and the Publish Pet panel's video-upload flow (cloned verbatim into the new file) calls `VideoUploadManager.startUpload(...)` from it. This file was never listed on A1's "Files to Request From CEO" line. The calling convention used is copied directly from `admin-dashboard.html`'s own working call site, so the interface itself is correct — but the new file's relative reference (`./video-upload-manager.js`) only resolves if it's deployed in the same directory as this script on the server, which depends on folder decisions outside this task's scope. Flagged rather than guessed around.

## Self-Check Confirmation

- Only `pet.json` and `pet-detail.html` were changed as existing files; both appear in the Files Changed table with Live paths, and every block in `pet-detail.html` carries the `<!-- TASK-A1 START -->`/`<!-- TASK-A1 END -->` anchor pair. `pet.json` cannot carry anchor comments (no JSON comment syntax) — its boundary is the Files Changed table entry and the diff above, noted explicitly rather than silently omitted.
- Every "Old code" snippet above was copied character-for-character from the original files as delivered to this task (`pet.json`, `pet-detail.html`), confirmed via direct `diff` against the untouched uploads before writing this file, and each occurs exactly once in its source file.
- One new file was created (`rehoming/list-a-pet/index.html`, proposed path); it is listed under New Files and is the only site file in the accompanying zip (the completion file itself sits at the zip root beside the `rehoming/` folder) (`Rehoming-Platform-Task-A1.zip`, which also contains this completion file at its root). No existing file's content is duplicated into the zip.
- `admin-dashboard.html` was read as a clone source and never modified — confirmed not present in either the Files Changed table (as "Changed") or the zip.
- No secrets, `config.php`, `applications/`, or `.env` content is included anywhere in this submission.
- Only the files named above were touched; no other page, panel, or backend file was modified.

---

## SECOND VERIFICATION ROUND

Three follow-up items came in after initial submission — a factual check, a design-compliance fix, and a formally-assigned seventh field. Documented here per the Second-Verification-Round rule rather than a new file. Scope Lock still applied throughout: only the already-owned files were touched.

**1. Icon check (no code change).** Confirmed directly against `admin-dashboard.html` that the category-selector icons in the new merchant posting panel are identical Font Awesome classes to the admin panel's: `fa-dog`, `fa-cat`, `fa-dove`, `fa-dragon`, `fa-otter`, `fa-horse`, `fa-fish`. No discrepancy found — no change made.

**2. Design-system compliance fix — Section 24 Custom Dropdown System.** The eight single-select fields (`size`, `gender`, `house_trained`, `vaccinated`, `compatible_kids`, `compatible_dogs`, `compatible_cats`, `compatible_other_pets`) were originally left as native `<select>` elements (a known, flagged deviation in the original submission). Replaced with the design system's locked "message bubble" dropdown shell — rounded panel with the sharp top-right notch, layered shadow, gold active-row accent, fade+scale+slide open animation — via one reusable `initDropdowns()`/`closeAllDropdowns()` pair wired to every `[data-dropdown]` instance. Verified with Playwright: opens on trigger click, selecting a row updates the paired hidden `data-field` input and trigger label, closes all other open panels before opening a new one, closes on outside click and Escape, flips upward when there's insufficient room below.

```diff
- <select class="pc-form-input" data-field="size"><option value="">Select size</option>...</select>
+ <div class="pc-form-group" data-dropdown="size" data-dropdown-label="Select size"><label class="pc-form-label">Size *</label>
+   <div class="dropdown-trigger" tabindex="0" role="button" aria-haspopup="listbox" aria-expanded="false"><span class="dropdown-trigger__label">Select size</span><i class="fas fa-chevron-down chevron"></i></div>
+   <input type="hidden" data-field="size" value="">
+   <div class="dropdown-panel"><div class="dropdown-panel__scroll">
+     <div class="dropdown-panel__row" data-value="Small">Small</div>
+     <div class="dropdown-panel__row" data-value="Medium">Medium</div>
+     <div class="dropdown-panel__row" data-value="Large">Large</div>
+   </div></div>
+ </div>
```
(The same pattern was applied to the other seven fields; full markup is in the submitted file.) Added CSS copied exactly from `PETCENTER_DESIGN_SYSTEM_v1.0.md` Section 24 (`.dropdown-trigger`, `.dropdown-panel`, `.dropdown-panel__scroll`, `.dropdown-panel__row` and states) and the `initDropdowns()`/`closeAllDropdowns()` JS pair, called once per pet card.

**3. Field-completeness question, resolved by the CEO/VDM — `is_urgent` formally assigned as A1's seventh field.** Raised in the original submission as a flagged gap (referenced by A3's filter and F5's crisis page, owned by no task); the VDM confirmed this and the task document now assigns it to A1 explicitly, alongside a doc-level fix to the earlier "four required text fields" wording (now correctly reads "three"). Added:

```diff
     "spay_neuter_status": "unknown",
     "listing_type": "permanent",
+    "is_urgent": false,
     "multiple_adoptable_options": "no",
```

Form: a checkbox (not radio/dropdown — matches the field's bool/optional nature, defaults unchecked), labeled exactly per spec: "This is an urgent rehoming situation (eviction, health crisis, deployment, etc.)". No display work added for this field on `pet-detail.html` — the task's own Display requirement names only the three text fields, and by the same pattern A2's `needs_extra_love` badge lives on the browse page (A3's territory), not pet-detail; adding an urgent badge to pet-detail wasn't asked for here and would be guessing at scope other tasks may own, so it was deliberately left out rather than added preemptively. Also updated the field-collection loop to read checkboxes as real booleans (`data[f] = inp.checked`) rather than the string-value logic used for text/radio fields.

**Files touched this round:** the same two existing files plus the one new file from the original submission — no additional files.

---

## THIRD VERIFICATION ROUND

Requested full re-audit against both project summary docs and the final locked Task A1 spec, with explicit instruction to check whether "Files to Request" was actually complete. Re-read `adoption-website-project-summary.md` and `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` in full, re-read the entire locked A1 section fresh, and line-checked every field/label/token-name/variable-scope assumption against both. Found one real bug (fixed) and confirmed the one real missing-file gap already reported above still stands (not re-fixed, same reasoning).

**1. Bug found and fixed — `health_status` label didn't match the task's exact required label text.** A1's spec gives an exact label: *"Health & medical history — vaccinations, known conditions, medications."* The submitted form only used "Health & Medical History" in the visible label and buried the "vaccinations, known conditions, medications" clause in the placeholder text instead. Fixed to match the spec's exact wording in the label itself:
```diff
- <label class="pc-form-label">Health &amp; Medical History * <span style="font-weight:400;text-transform:none;">(min 20 characters)</span></label>
+ <label class="pc-form-label">Health &amp; medical history — vaccinations, known conditions, medications * <span style="font-weight:400;text-transform:none;">(min 20 characters)</span></label>
```
(`temperament`'s and `reason_for_rehoming`'s labels were checked against the spec too — both already contain the spec's required content.)

**2. Spot-checked and confirmed clean (no change needed):**
- Upload-token field names (`single_token`, `bulk_tokens`, `video_token`) in the submission payload match `publish-pet.php`'s actual `$_POST` reads exactly (re-confirmed directly against the file).
- The `pet` object referenced throughout the `pet-detail.html` insertion is correctly scoped to `populatePetInfo(pet, category)`'s own parameter — not a stray global or wrong-scope variable.
- Foster-only fields (`foster_duration_estimate`, `foster_covers`) are genuinely omitted from the submission payload when `listing_type !== 'foster'` (not left as empty strings), matching the schema's "present only if foster" rule.
- No Phosphor-icon classes are used anywhere inside the cloned Publish Pet panel markup specifically, so no missing-icon-stylesheet risk in the cloned subset.
- The pre-existing `spayed_neutered` field vs. the new `spay_neuter_status` field: confirmed this is spec-directed (A1's locked field list names `spay_neuter_status` with its own enum), not an accidental duplicate.

**Files touched this round:** the new file only (label text fix). No change to `pet.json`/`pet-detail.html` this round.

---

## FOURTH VERIFICATION ROUND — repackaged to the new Worker Delivery Guide format

The hand-in process changed (git/branches/reports retired in favor of a completion file + a new-files zip, per `WORKER-DELIVERY-GUIDE.md`). No task scope, field, validation, or display logic changed in this round — this is a format migration only:

- Rebuilt this entire completion file into the four-column Files Changed table (adding the **Live path** column), with every existing-file change shown as an exact old/new code block anchored and copied from the original uploaded files (re-confirmed via `diff` against those originals before writing), rather than the previous prose-plus-diff-fragment style.
- Moved the one brand-new file (the merchant posting panel) out of narrative description and into a proper **New files** section plus the single combined zip `Rehoming-Platform-Task-A1.zip` (completion file + new files together, per the CEO's clarification), with new files rooted at the site root.
- Surfaced the live-path gap explicitly as a flagged, unconfirmed proposal (`rehoming/list-a-pet/index.html`) rather than letting it stay implicit — checked both summary docs' folder-structure sections directly and found neither assigns a path for this new Rehoming Platform page; this is a genuine open question, not a guess made silently.
- Confirmed the zip's sole contents (`rehoming/list-a-pet/index.html`) match this file's Files Changed table and New Files section exactly, per the guide's pre-send checklist.

No bugs found this round beyond the already-reported, still-open `video-upload-manager.js` file gap.
