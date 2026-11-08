# Task A3 — Faceted Search/Filter Panel — Completion Report

## Task Number
A3 (Phase A — Core Data Model)

## What Was Required
Build the faceted search/filter panel on the browse/search page (confirmed by the CEO to be `cats.html` and its six per-category sibling clones). Each filter needed its own independent control (not one combined search box), all combining with AND logic across groups (OR within a multi-select group): Species (dynamic checkbox list), Age range (min/max), Size (checkbox list), Distance/location radius (numeric + unit, no paid maps API), "Good with kids" / "Good with other pets" (checkboxes on a structured sub-field, not keyword-matched free text), Urgent-only toggle, Verified-only toggle (reads merchant `verification_status` from B2, not listing status), Listing type toggle (permanent/foster), and a default browse view that excludes any listing with `held_for_review: true` or a non-`active` `status`. Results update without a full page reload.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `cats.html` | `pets/cats/index.html` | Changed | Yes — also carries Task A2's bonded-pair/needs-extra-love code (untouched by this task) and the original pre-task baseline header/footer/hamburger (also untouched). |
| `dogs.html` | `pets/dogs/index.html` | Changed | Yes — same as above |
| `birds.html` | `pets/birds/index.html` | Changed | Yes — same as above |
| `reptiles.html` | `pets/reptiles/index.html` | Changed | Yes — same as above |
| `small-mammals.html` | `pets/small-mammals/index.html` | Changed | Yes — same as above |
| `livestocks.html` | `pets/livestocks/index.html` | Changed | Yes — same as above |
| `aquariums.html` | `pets/aquariums/index.html` | Changed | Yes — same as above |

No new files were created for this task — see "New Files" below. All seven files received the same code change (generated as a single unified diff off `cats.html`, then applied to the six siblings), since they are true clones differing only in category-specific text/icons. **The Code Changes section below is written once, against `cats.html`, and applies verbatim at each of the six other live paths listed above** — every old-code snippet below was verified by exact string match to occur exactly once in all seven of the original source files before this document was written (see Self-Check Confirmation).

## Code Changes

### `cats.html`  →  live path `pets/cats/index.html`
*(identical change also applies at `pets/dogs/index.html`, `pets/birds/index.html`, `pets/reptiles/index.html`, `pets/small-mammals/index.html`, `pets/livestocks/index.html`, `pets/aquariums/index.html`)*

Every block below is copied directly out of the real files, not retyped from memory. Every "Old code" is the exact, unmodified text of the original `cats.html` you gave me for this task, and every "New code" was verified byte-for-byte against the real submitted file before this document was written.

---

#### 1. CSS — new control shapes (range inputs, checkbox rows, toggle switches)
Purely new code; shown with the exact existing lines it was inserted between.

<!-- TASK-A3 START -->
**Old code:**
```css
.pc-chip:hover { border-color: rgba(215,189,136,.5); }
.pc-chip.selected { background: var(--gold); color: var(--navy); border-color: var(--gold); font-weight: 600; }
.pc-chip.selected::before { content: "✓"; font-weight: 700; }

/* Scroll-to-bottom floating hint button */
```
**New code:**
```css
.pc-chip:hover { border-color: rgba(215,189,136,.5); }
.pc-chip.selected { background: var(--gold); color: var(--navy); border-color: var(--gold); font-weight: 600; }
.pc-chip.selected::before { content: "✓"; font-weight: 700; }

/* TASK-A3 START — Faceted search/filter panel: CSS for the new control
   types this task adds (a multi-select chip is just .pc-chip/.selected
   reused, per Rule 7 — no new class needed for that). Range-input pair
   (Age min/max, Distance), a checkbox row (Good With Kids/Other Pets),
   and a toggle switch (Urgent Only / Verified Merchants Only) are new
   shapes not covered by the existing chip system, so they're added here.
   All reuse the sheet's existing dark-theme tokens (--gold/--navy/
   --warm-gray) rather than introducing new colors, per the design
   system's "same connected system" rule (PETCENTER_DESIGN_SYSTEM_v1.0.md
   Section 30). */
.pc-range-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.pc-range-input {
  width: 84px; background: #1c2c56; border: 1px solid rgba(255,255,255,.15);
  color: #fff; border-radius: 8px; padding: 9px 10px;
  font-family: 'Sora', sans-serif; font-size: 13.5px;
}
.pc-range-input:focus { outline: none; border-color: var(--gold); }
.pc-range-sep { color: rgba(255,255,255,.5); font-size: 13px; }
.pc-locate-btn {
  display: inline-flex; align-items: center; gap: 6px;
  background: transparent; border: 1.5px solid var(--warm-gray); color: var(--gold);
  border-radius: 999px; padding: 8px 14px; font-family: 'Sora', sans-serif;
  font-size: 12.5px; font-weight: 600; cursor: pointer; transition: border-color .15s ease;
}
.pc-locate-btn:hover { border-color: var(--gold-dark); }
.pc-locate-btn.busy { opacity: .6; pointer-events: none; }
.pc-locate-status { font-size: 12px; color: rgba(255,255,255,.55); margin-top: 6px; }

.pc-checkbox-filter-row { display: flex; flex-direction: column; gap: 12px; }
.pc-checkbox-filter {
  display: flex; align-items: center; gap: 10px; cursor: pointer;
  font-family: 'Sora', sans-serif; font-size: 13.5px; color: #e5e5e5;
}
.pc-checkbox-filter input[type="checkbox"] {
  width: 18px; height: 18px; accent-color: var(--gold); cursor: pointer; flex-shrink: 0;
}

.pc-toggle-filter-row { display: flex; flex-direction: column; gap: 14px; }
.pc-toggle-filter {
  display: flex; align-items: center; justify-content: space-between; gap: 12px;
  font-family: 'Sora', sans-serif; font-size: 13.5px; color: #e5e5e5;
}
.pc-toggle-switch {
  position: relative; width: 42px; height: 24px; flex-shrink: 0;
  background: rgba(255,255,255,.15); border-radius: 999px; border: none;
  cursor: pointer; transition: background .2s ease;
}
.pc-toggle-switch::after {
  content: ""; position: absolute; top: 3px; left: 3px; width: 18px; height: 18px;
  background: #fff; border-radius: 50%; transition: transform .2s ease;
}
.pc-toggle-switch.on { background: var(--gold); }
.pc-toggle-switch.on::after { transform: translateX(18px); background: var(--navy); }
/* TASK-A3 END */

/* Scroll-to-bottom floating hint button */
```
<!-- TASK-A3 END -->

---

#### 2. Hidden state-holder inputs (single source of truth `applyAndRender()` reads)
Added directly after the existing `filterCompatible` hidden select.

<!-- TASK-A3 START -->
**Old code:**
```html
<select class="hidden-select" id="filterCompatible" name="compatible" style="display:none;">
      <option value="">Any</option>
      <option value="kids">Kids</option>
      <option value="dogs">Dogs</option>
      <option value="cats">Cats</option>
      <option value="other_pets">Other Pets</option>
      <option value="untamed">Untamed</option>
    </select>
```
**New code:**
```html
<select class="hidden-select" id="filterCompatible" name="compatible" style="display:none;">
      <option value="">Any</option>
      <option value="kids">Kids</option>
      <option value="dogs">Dogs</option>
      <option value="cats">Cats</option>
      <option value="other_pets">Other Pets</option>
      <option value="untamed">Untamed</option>
    </select>

    <!-- TASK-A3 START — Hidden state-holders for the new faceted filters
         (Task A3). Follows the exact same convention as the hidden
         <select> elements above (per Rule 7): applyAndRender() reads
         these via getElementById(...).value/checked; the sheet's chips/
         inputs write into them on Apply. Species and Size are stored as
         comma-joined strings since they're multi-select (existing
         convention has no multi-select precedent, so this is the
         simplest extension of the single-value pattern above). -->
    <input type="hidden" id="filterSpecies" value="">
    <input type="hidden" id="filterSizeMulti" value="">
    <input type="hidden" id="filterAgeMin" value="">
    <input type="hidden" id="filterAgeMax" value="">
    <input type="hidden" id="filterGoodKids" value="">
    <input type="hidden" id="filterGoodOtherPets" value="">
    <input type="hidden" id="filterDistance" value="">
    <input type="hidden" id="filterDistanceUnit" value="mi">
    <input type="hidden" id="filterListingType" value="">
    <input type="hidden" id="filterUrgentOnly" value="">
    <input type="hidden" id="filterVerifiedOnly" value="">
    <!-- TASK-A3 END -->
```
<!-- TASK-A3 END -->

---

#### 3. Old `filterSize`/`filterAge` single-value hidden selects — removed, replaced by item 2 above
These two elements are fully superseded by `filterSizeMulti` and `filterAgeMin`/`filterAgeMax` added in item 2. They were removed from the live page and kept only as a commented-out historical record in the same spot.

<!-- TASK-A3 START -->
**Old code:**
```html
<select class="hidden-select" id="filterSize" name="size" style="display:none;">
      <option value="">Any Size</option>
      <option value="Small">Small</option>
      <option value="Medium">Medium</option>
      <option value="Large">Large</option>
    </select>
    <select class="hidden-select" id="filterAge" name="age" style="display:none;">
      <option value="">Any Age</option>
      <option value="0-1">Under 1 year</option>
      <option value="1-3">1–3 years</option>
      <option value="3-6">3–6 years</option>
      <option value="6+">6+ years</option>
    </select>
    <select class="hidden-select" id="filterFee" name="fee" style="display:none;">
```
**New code:**
```html
<!-- TASK-A3 START — old bucketed filterSize/filterAge single-value
         hidden <select>s removed: superseded by filterSizeMulti (below,
         multi-select checkbox list) and filterAgeMin/filterAgeMax (below,
         numeric range), per Task A3's explicit spec for these two fields.
         Old code being replaced (kept here for the record, per Directive
         8a's "old snippet + new final code" requirement — see
         Task-A3-Completion.md for the full diff):
      <select class="hidden-select" id="filterSize" name="size" style="display:none;">
        <option value="">Any Size</option>
        <option value="Small">Small</option>
        <option value="Medium">Medium</option>
        <option value="Large">Large</option>
      </select>
      <select class="hidden-select" id="filterAge" name="age" style="display:none;">
        <option value="">Any Age</option>
        <option value="0-1">Under 1 year</option>
        <option value="1-3">1–3 years</option>
        <option value="3-6">3–6 years</option>
        <option value="6+">6+ years</option>
      </select>
    -->
    <!-- TASK-A3 END -->
    <select class="hidden-select" id="filterFee" name="fee" style="display:none;">
```
<!-- TASK-A3 END -->

---

#### 4. Species filter (new group — dynamic multi-select checkboxes)
Added directly before the existing "Adoption Fee" group.

<!-- TASK-A3 START -->
**Old code:**
```html
<div class="pc-sheet-filter-group" data-filter="filterFee">
        <div class="pc-sheet-filter-group__label">Adoption Fee</div>
```
**New code:**
```html
<!-- TASK-A3 START — Species filter (Task A3 spec: "checkbox list,
           populated from whatever species values exist in the data — no
           hardcoded species list"). Built exactly like the existing
           Breed group above (dynamic chips via populateFilters(), same
           .pc-chip-scroll cap), per Rule 7 — the only difference is
           data-mode="multi" so more than one chip can be selected at
           once (a real checkbox list, not a single-select), since a
           category page like reptiles/small-mammals/livestocks/
           aquariums can hold more than one species. -->
      <div class="pc-sheet-filter-group" data-filter="filterSpecies">
        <div class="pc-sheet-filter-group__label">Species</div>
        <div class="pc-chip-scroll">
          <div class="pc-chip-row" data-target="filterSpecies" data-mode="multi" id="pcSpeciesChipRow">
            <!-- Rows injected by populateFilters(), same pattern as breed chips -->
          </div>
        </div>
      </div>
      <!-- TASK-A3 END -->

      <div class="pc-sheet-filter-group" data-filter="filterFee">
        <div class="pc-sheet-filter-group__label">Adoption Fee</div>
```
<!-- TASK-A3 END -->

---

#### 5. Age filter (fixed buckets → min/max number inputs) and Size filter (single-select → multi-select checkboxes)
These two existing groups are replaced in place — a real text replacement, not an addition.

<!-- TASK-A3 START -->
**Old code:**
```html
<div class="pc-sheet-filter-group" data-filter="filterAge">
        <div class="pc-sheet-filter-group__label">Age</div>
        <div class="pc-chip-scroll">
          <div class="pc-chip-row" data-target="filterAge">
            <button type="button" class="pc-chip selected" data-value="">Any Age</button>
            <button type="button" class="pc-chip" data-value="0-1">Under 1 year</button>
            <button type="button" class="pc-chip" data-value="1-3">1–3 years</button>
            <button type="button" class="pc-chip" data-value="3-6">3–6 years</button>
            <button type="button" class="pc-chip" data-value="6+">6+ years</button>
          </div>
        </div>
      </div>

      <div class="pc-sheet-filter-group" data-filter="filterSize">
        <div class="pc-sheet-filter-group__label">Size</div>
        <div class="pc-chip-scroll">
          <div class="pc-chip-row" data-target="filterSize">
            <button type="button" class="pc-chip selected" data-value="">Any Size</button>
            <button type="button" class="pc-chip" data-value="Small">Small</button>
            <button type="button" class="pc-chip" data-value="Medium">Medium</button>
            <button type="button" class="pc-chip" data-value="Large">Large</button>
          </div>
        </div>
      </div>
```
**New code:**
```html
<!-- TASK-A3 START — Age filter changed from the pre-existing fixed
           buckets (Under 1 year / 1-3 / 3-6 / 6+) to task A3's explicit
           spec: "min/max slider or two number inputs, reading existing
           age field." Implemented as two number inputs (simpler and more
           precise than a slider for exact-year filtering, and consistent
           with the Distance group below using the same input style) —
           this is a deliberate deviation from the pre-existing bucket UI
           documented in adoption-website-project-summary.md Section
           15.1, made because A3's task text explicitly asks for a
           different control type for this field, not a guess. -->
      <div class="pc-sheet-filter-group" data-filter="filterAgeRange">
        <div class="pc-sheet-filter-group__label">Age (years)</div>
        <div class="pc-range-row">
          <input type="number" class="pc-range-input" id="pcAgeMinInput" min="0" step="0.1" placeholder="Min">
          <span class="pc-range-sep">to</span>
          <input type="number" class="pc-range-input" id="pcAgeMaxInput" min="0" step="0.1" placeholder="Max">
        </div>
      </div>
      <!-- TASK-A3 END -->

      <!-- TASK-A3 START — Size changed from single-select chips to a real
           multi-select checkbox list per Task A3 spec ("Size (checkbox:
           small/medium/large...)"). Existing Small/Medium/Large values
           kept exactly as-is (per spec: "confirm existing size field
           values, don't invent new categories if one exists") — only the
           selection behavior changes (data-mode="multi"), not the
           option set. -->
      <div class="pc-sheet-filter-group" data-filter="filterSizeMulti">
        <div class="pc-sheet-filter-group__label">Size</div>
        <div class="pc-chip-scroll">
          <div class="pc-chip-row" data-target="filterSizeMulti" data-mode="multi">
            <button type="button" class="pc-chip" data-value="Small">Small</button>
            <button type="button" class="pc-chip" data-value="Medium">Medium</button>
            <button type="button" class="pc-chip" data-value="Large">Large</button>
          </div>
        </div>
      </div>
      <!-- TASK-A3 END -->
```
<!-- TASK-A3 END -->

---

#### 6. "Good With", Distance, Listing Type, and Quick Filters groups (all new — added after the existing "Compatible With" group)

<!-- TASK-A3 START -->
**Old code:**
```html
<div class="pc-sheet-filter-group" data-filter="filterCompatible">
        <div class="pc-sheet-filter-group__label">Compatible With</div>
        <div class="pc-chip-scroll">
          <div class="pc-chip-row" data-target="filterCompatible">
            <button type="button" class="pc-chip selected" data-value="">Any</button>
            <button type="button" class="pc-chip" data-value="kids">Kids</button>
            <button type="button" class="pc-chip" data-value="dogs">Dogs</button>
            <button type="button" class="pc-chip" data-value="cats">Cats</button>
            <button type="button" class="pc-chip" data-value="other_pets">Other Pets</button>
            <button type="button" class="pc-chip" data-value="untamed">Untamed</button>
          </div>
        </div>
      </div>

    </div>
```
**New code:**
```html
<div class="pc-sheet-filter-group" data-filter="filterCompatible">
        <div class="pc-sheet-filter-group__label">Compatible With</div>
        <div class="pc-chip-scroll">
          <div class="pc-chip-row" data-target="filterCompatible">
            <button type="button" class="pc-chip selected" data-value="">Any</button>
            <button type="button" class="pc-chip" data-value="kids">Kids</button>
            <button type="button" class="pc-chip" data-value="dogs">Dogs</button>
            <button type="button" class="pc-chip" data-value="cats">Cats</button>
            <button type="button" class="pc-chip" data-value="other_pets">Other Pets</button>
            <button type="button" class="pc-chip" data-value="untamed">Untamed</button>
          </div>
        </div>
      </div>

      <!-- TASK-A3 START — "Good with kids" / "Good with other pets"
           checkboxes (Task A3 spec: reads a separate structured yes/no/
           unknown sub-field, not keyword-matching the free-text
           temperament field). The real schema already has these as
           separate structured fields (compatible_kids, compatible_other_
           pets — confirmed in pet-schema-v1.json / Task A1's completion
           file), so no new schema field was needed; this just adds two
           independent checkboxes distinct from the existing single-select
           "Compatible With" group above (which is OR-of-one-value; these
           two are independent AND-able boolean filters, per spec). -->
      <div class="pc-sheet-filter-group" data-filter="filterGoodWith">
        <div class="pc-sheet-filter-group__label">Good With</div>
        <div class="pc-checkbox-filter-row">
          <label class="pc-checkbox-filter">
            <input type="checkbox" id="pcGoodKidsCheckbox">
            <span>Good with kids</span>
          </label>
          <label class="pc-checkbox-filter">
            <input type="checkbox" id="pcGoodOtherPetsCheckbox">
            <span>Good with other pets</span>
          </label>
        </div>
      </div>
      <!-- TASK-A3 END -->

      <!-- TASK-A3 START — Distance/location radius filter (Task A3 spec:
           "numeric input + unit, using the visitor's position against
           each listing's stored location — no paid maps API needed").
           The real pet.json `location` field (e.g. "Los Angeles, CA") is
           free text with no coordinates, and there's no network access
           in this build/verification sandbox to call an external
           geocoding service per Directive 11's "evaluate real quality,
           not just free" — and a per-listing call to a third-party API
           would also be a runtime dependency this static browse page
           doesn't otherwise have. Chosen approach: a small built-in
           city-name -> lat/long lookup table (PC_CITY_COORDS, see script
           below) matched against each pet's `location` string, combined
           with the visitor's own position via the browser's native
           Geolocation API (no key, no network dependency beyond the
           browser itself) and a standard Haversine distance calculation.
           Flagged limitation, not silently glossed over: this only
           covers the cities in that lookup table — a listing whose city
           isn't in the table is not excluded by this filter (treated as
           "unknown distance," not "too far"), so this should be revisited
           once real listings carry actual merchant-entered coordinates. -->
      <div class="pc-sheet-filter-group" data-filter="filterDistanceGroup">
        <div class="pc-sheet-filter-group__label">Distance</div>
        <div class="pc-range-row">
          <input type="number" class="pc-range-input" id="pcDistanceInput" min="0" step="1" placeholder="Radius">
          <!-- Unit picker: a two-value Section 30 single-select chip row
               (same mechanism as Listing Type below), NOT a dropdown and
               NOT a native <select> — PETCENTER_DESIGN_SYSTEM_v1.0.md
               Section 24 says filter/sort UI uses Section 30, and a
               two-option choice needs no dropdown panel at all. Writes to
               the hidden filterDistanceUnit holder via the shared chip
               draft/apply/clear machinery (Clear resets to "mi", the
               first chip). -->
          <div class="pc-chip-row" data-target="filterDistanceUnit">
            <button type="button" class="pc-chip selected" data-value="mi">miles</button>
            <button type="button" class="pc-chip" data-value="km">km</button>
          </div>
          <button type="button" class="pc-locate-btn" id="pcLocateBtn">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>
            Use my location
          </button>
        </div>
        <div class="pc-locate-status" id="pcLocateStatus"></div>
      </div>
      <!-- TASK-A3 END -->

      <!-- TASK-A3 START — Listing type toggle (Task A3 spec: "permanent/
           foster, from A1"). Built as a single-select chip row, same
           pattern as the existing Sort By / Adoption Fee groups above
           (per Rule 7 — reusing the exact existing single-select chip
           mechanism rather than inventing a new toggle for a 3-value
           choice: Any/Permanent/Foster). Reads A1's real `listing_type`
           enum field verbatim. -->
      <div class="pc-sheet-filter-group" data-filter="filterListingType">
        <div class="pc-sheet-filter-group__label">Listing Type</div>
        <div class="pc-chip-row" data-target="filterListingType">
          <button type="button" class="pc-chip selected" data-value="">Any</button>
          <button type="button" class="pc-chip" data-value="permanent">Permanent</button>
          <button type="button" class="pc-chip" data-value="foster">Foster</button>
        </div>
      </div>
      <!-- TASK-A3 END -->

      <!-- TASK-A3 START — Urgent-only and Verified-only toggles (Task A3
           spec: "Urgent-only toggle (reads the urgent-rehoming flag)" and
           "Verified-only toggle (reads the merchant verification_status
           from B2, not listing status)"). Built as real on/off switches
           (.pc-toggle-switch) rather than chips, since these are
           standalone booleans, not part of a value set — matches the
           design system's existing toggle affordance used elsewhere on
           the site (e.g. the posting form's own checkbox toggles), just
           styled as a switch to read clearly at a glance among the
           chip-heavy groups above. Verified-only builds against a stub
           field (`merchant_verification_status`) per the Shared
           Contracts mechanism, since Task B2 (which owns the real
           verification_status value) has not shipped merchant records
           yet — see Task-A3-Completion.md for the reconciliation note. -->
      <div class="pc-sheet-filter-group" data-filter="filterQuickToggles">
        <div class="pc-sheet-filter-group__label">Quick Filters</div>
        <div class="pc-toggle-filter-row">
          <div class="pc-toggle-filter">
            <span>Urgent rehoming only</span>
            <button type="button" class="pc-toggle-switch" id="pcUrgentToggle" role="switch" aria-checked="false" aria-label="Urgent rehoming only"></button>
          </div>
          <div class="pc-toggle-filter">
            <span>Verified merchants only</span>
            <button type="button" class="pc-toggle-switch" id="pcVerifiedToggle" role="switch" aria-checked="false" aria-label="Verified merchants only"></button>
          </div>
        </div>
      </div>
      <!-- TASK-A3 END -->

    </div>
```
<!-- TASK-A3 END -->

---

#### 7. Draft-state machinery — multi-select support + wiring the new non-chip controls into the sheet lifecycle
The entire pre-existing single-select draft-state/click/open/close/clear/apply mechanism is replaced in place (the multi-select branching touches nearly every line, so the full function set is shown rather than a partial patch).

<!-- TASK-A3 START -->
**Old code:**
```javascript
  // Draft state: target hidden-select id -> selected value
  var draft = {};

  function seedDraftFromChips() {
    draft = {};
    sheet.querySelectorAll('.pc-chip-row').forEach(function(row) {
      var target = row.getAttribute('data-target');
      var selected = row.querySelector('.pc-chip.selected');
      draft[target] = selected ? selected.getAttribute('data-value') : '';
    });
  }

  function renderChipSelection() {
    sheet.querySelectorAll('.pc-chip-row').forEach(function(row) {
      var target = row.getAttribute('data-target');
      var val = draft[target] || '';
      row.querySelectorAll('.pc-chip').forEach(function(chip) {
        chip.classList.toggle('selected', chip.getAttribute('data-value') === val);
      });
    });
  }

  // Click handling for all chip rows (event delegation, works for dynamically
  // added breed chips too)
  sheetBody.addEventListener('click', function(e) {
    var chip = e.target.closest('.pc-chip');
    if (!chip) return;
    var row = chip.closest('.pc-chip-row');
    if (!row) return;
    var target = row.getAttribute('data-target');
    draft[target] = chip.getAttribute('data-value');
    renderChipSelection();
  });

  function openSheet() {
    seedDraftFromChips();
    // Sync draft with whatever the hidden selects currently hold (in case
    // Apply was hit before, or search/other logic changed them)
    Object.keys(draft).forEach(function(id) {
      var el = document.getElementById(id);
      if (el) draft[id] = el.value;
    });
    renderChipSelection();
    sheet.classList.add('open');
    backdrop.classList.add('show');
    triggerBtn.classList.add('active');
    triggerBtn.setAttribute('aria-expanded', 'true');
    pcLockBodyScroll();
    if (window.pcLayoutAllChipRows) window.pcLayoutAllChipRows();
    setTimeout(updateScrollHint, 350);
  }
  function closeSheet() {
    sheet.classList.remove('open');
    backdrop.classList.remove('show');
    triggerBtn.classList.remove('active');
    triggerBtn.setAttribute('aria-expanded', 'false');
    pcUnlockBodyScroll();
  }

  triggerBtn.addEventListener('click', openSheet);
  closeBtn.addEventListener('click', closeSheet);
  backdrop.addEventListener('click', function(e){ if (e.target === backdrop) closeSheet(); });
  pcEnableDragToClose(sheet, document.getElementById('pcSheetDragZone'), closeSheet);

  clearBtn.addEventListener('click', function() {
    // Reset draft to defaults (first chip in each row is always the "any/all" default)
    sheet.querySelectorAll('.pc-chip-row').forEach(function(row) {
      var target = row.getAttribute('data-target');
      var firstChip = row.querySelector('.pc-chip');
      draft[target] = firstChip ? firstChip.getAttribute('data-value') : '';
    });
    renderChipSelection();
    // Sheet stays open (spec requirement)
  });

  applyBtn.addEventListener('click', function() {
    Object.keys(draft).forEach(function(id) {
      var el = document.getElementById(id);
      if (el) el.value = draft[id];
    });
    if (window.pcApplyAndRender) window.pcApplyAndRender();
    closeSheet();
  });
```
**New code:**
```javascript
  // Draft state: target hidden-select id -> selected value
  var draft = {};

  // TASK-A3 START — multi-select support. A row with data-mode="multi"
  // (Species, Size) stores an ARRAY of selected values in draft[target]
  // instead of a single string; every other row keeps the original
  // single-select behavior untouched. isMultiRow() is the one shared
  // check every function below branches on, so the two behaviors never
  // drift apart.
  function isMultiRow(row) { return row.getAttribute('data-mode') === 'multi'; }
  // TASK-A3 END

  function seedDraftFromChips() {
    draft = {};
    sheet.querySelectorAll('.pc-chip-row').forEach(function(row) {
      var target = row.getAttribute('data-target');
      if (isMultiRow(row)) { // TASK-A3
        draft[target] = Array.prototype.map.call(
          row.querySelectorAll('.pc-chip.selected'),
          function(c) { return c.getAttribute('data-value'); }
        );
      } else {
        var selected = row.querySelector('.pc-chip.selected');
        draft[target] = selected ? selected.getAttribute('data-value') : '';
      }
    });
  }

  function renderChipSelection() {
    sheet.querySelectorAll('.pc-chip-row').forEach(function(row) {
      var target = row.getAttribute('data-target');
      if (isMultiRow(row)) { // TASK-A3 — membership check against the array, not equality
        var arr = draft[target] || [];
        row.querySelectorAll('.pc-chip').forEach(function(chip) {
          chip.classList.toggle('selected', arr.indexOf(chip.getAttribute('data-value')) !== -1);
        });
      } else {
        var val = draft[target] || '';
        row.querySelectorAll('.pc-chip').forEach(function(chip) {
          chip.classList.toggle('selected', chip.getAttribute('data-value') === val);
        });
      }
    });
  }

  // TASK-A3 START — the new non-chip controls (Age min/max, Good With
  // checkboxes, Distance + unit, Listing Type reuses the chip mechanism
  // above so it needs no extra code here, Urgent/Verified toggle
  // switches) get their own small seed/render/apply/clear helpers,
  // wired into the exact same sheet lifecycle (openSheet/clearBtn/
  // applyBtn) as the chip system, per Rule 12's "one connected system"
  // expectation rather than a second, separate flow the user would
  // experience inconsistently (e.g. Clear not clearing everything).
  var pcAgeMinInput = document.getElementById('pcAgeMinInput');
  var pcAgeMaxInput = document.getElementById('pcAgeMaxInput');
  var pcGoodKidsCheckbox = document.getElementById('pcGoodKidsCheckbox');
  var pcGoodOtherPetsCheckbox = document.getElementById('pcGoodOtherPetsCheckbox');
  var pcDistanceInput = document.getElementById('pcDistanceInput');
  var pcUrgentToggle = document.getElementById('pcUrgentToggle');
  var pcVerifiedToggle = document.getElementById('pcVerifiedToggle');

  function setToggleVisual(btn, on) {
    if (!btn) return;
    btn.classList.toggle('on', !!on);
    btn.setAttribute('aria-checked', on ? 'true' : 'false');
  }

  function seedA3ControlsFromHidden() {
    if (pcAgeMinInput) pcAgeMinInput.value = document.getElementById('filterAgeMin').value || '';
    if (pcAgeMaxInput) pcAgeMaxInput.value = document.getElementById('filterAgeMax').value || '';
    if (pcGoodKidsCheckbox) pcGoodKidsCheckbox.checked = document.getElementById('filterGoodKids').value === '1';
    if (pcGoodOtherPetsCheckbox) pcGoodOtherPetsCheckbox.checked = document.getElementById('filterGoodOtherPets').value === '1';
    if (pcDistanceInput) pcDistanceInput.value = document.getElementById('filterDistance').value || '';
    setToggleVisual(pcUrgentToggle, document.getElementById('filterUrgentOnly').value === '1');
    setToggleVisual(pcVerifiedToggle, document.getElementById('filterVerifiedOnly').value === '1');
  }

  function applyA3ControlsToHidden() {
    if (pcAgeMinInput) document.getElementById('filterAgeMin').value = pcAgeMinInput.value || '';
    if (pcAgeMaxInput) document.getElementById('filterAgeMax').value = pcAgeMaxInput.value || '';
    if (pcGoodKidsCheckbox) document.getElementById('filterGoodKids').value = pcGoodKidsCheckbox.checked ? '1' : '';
    if (pcGoodOtherPetsCheckbox) document.getElementById('filterGoodOtherPets').value = pcGoodOtherPetsCheckbox.checked ? '1' : '';
    if (pcDistanceInput) document.getElementById('filterDistance').value = pcDistanceInput.value || '';
    document.getElementById('filterUrgentOnly').value = (pcUrgentToggle && pcUrgentToggle.classList.contains('on')) ? '1' : '';
    document.getElementById('filterVerifiedOnly').value = (pcVerifiedToggle && pcVerifiedToggle.classList.contains('on')) ? '1' : '';
  }

  function clearA3Controls() {
    if (pcAgeMinInput) pcAgeMinInput.value = '';
    if (pcAgeMaxInput) pcAgeMaxInput.value = '';
    if (pcGoodKidsCheckbox) pcGoodKidsCheckbox.checked = false;
    if (pcGoodOtherPetsCheckbox) pcGoodOtherPetsCheckbox.checked = false;
    if (pcDistanceInput) pcDistanceInput.value = '';
    setToggleVisual(pcUrgentToggle, false);
    setToggleVisual(pcVerifiedToggle, false);
  }

  if (pcUrgentToggle) pcUrgentToggle.addEventListener('click', function() {
    setToggleVisual(this, !this.classList.contains('on'));
  });
  if (pcVerifiedToggle) pcVerifiedToggle.addEventListener('click', function() {
    setToggleVisual(this, !this.classList.contains('on'));
  });

  // "Use my location" — browser Geolocation API only, no third-party
  // network call (see the CSS-block comment above for why). Stores the
  // visitor's coordinates on window.pcVisitorCoords for applyAndRender()'s
  // distance calculation to read; this is intentionally session-only
  // (never persisted), since it's a live "where am I right now" input,
  // not a saved preference.
  var pcLocateBtn = document.getElementById('pcLocateBtn');
  var pcLocateStatus = document.getElementById('pcLocateStatus');
  if (pcLocateBtn) pcLocateBtn.addEventListener('click', function() {
    if (!navigator.geolocation) {
      if (pcLocateStatus) pcLocateStatus.textContent = 'Location is not supported by this browser.';
      return;
    }
    pcLocateBtn.classList.add('busy');
    if (pcLocateStatus) pcLocateStatus.textContent = 'Getting your location…';
    navigator.geolocation.getCurrentPosition(function(pos) {
      window.pcVisitorCoords = { lat: pos.coords.latitude, lng: pos.coords.longitude };
      pcLocateBtn.classList.remove('busy');
      if (pcLocateStatus) pcLocateStatus.textContent = 'Location set.';
    }, function() {
      pcLocateBtn.classList.remove('busy');
      if (pcLocateStatus) pcLocateStatus.textContent = 'Could not get your location — check browser permissions.';
    }, { timeout: 10000 });
  });
  // TASK-A3 END

  // Click handling for all chip rows (event delegation, works for dynamically
  // added breed chips too)
  sheetBody.addEventListener('click', function(e) {
    var chip = e.target.closest('.pc-chip');
    if (!chip) return;
    var row = chip.closest('.pc-chip-row');
    if (!row) return;
    var target = row.getAttribute('data-target');
    if (isMultiRow(row)) { // TASK-A3 — toggle membership instead of replacing the whole value
      var value = chip.getAttribute('data-value');
      var arr = draft[target] || [];
      var idx = arr.indexOf(value);
      if (idx === -1) arr.push(value); else arr.splice(idx, 1);
      draft[target] = arr;
    } else {
      draft[target] = chip.getAttribute('data-value');
    }
    renderChipSelection();
  });

  function openSheet() {
    seedDraftFromChips();
    // Sync draft with whatever the hidden selects currently hold (in case
    // Apply was hit before, or search/other logic changed them)
    Object.keys(draft).forEach(function(id) {
      var el = document.getElementById(id);
      if (!el) return;
      if (Array.isArray(draft[id])) draft[id] = el.value ? el.value.split(',') : []; // TASK-A3 — multi-select values are comma-joined in the hidden input
      else draft[id] = el.value;
    });
    renderChipSelection();
    seedA3ControlsFromHidden(); // TASK-A3
    sheet.classList.add('open');
    backdrop.classList.add('show');
    triggerBtn.classList.add('active');
    triggerBtn.setAttribute('aria-expanded', 'true');
    pcLockBodyScroll();
    if (window.pcLayoutAllChipRows) window.pcLayoutAllChipRows();
    setTimeout(updateScrollHint, 350);
  }
  function closeSheet() {
    sheet.classList.remove('open');
    backdrop.classList.remove('show');
    triggerBtn.classList.remove('active');
    triggerBtn.setAttribute('aria-expanded', 'false');
    pcUnlockBodyScroll();
  }

  triggerBtn.addEventListener('click', openSheet);
  closeBtn.addEventListener('click', closeSheet);
  backdrop.addEventListener('click', function(e){ if (e.target === backdrop) closeSheet(); });
  pcEnableDragToClose(sheet, document.getElementById('pcSheetDragZone'), closeSheet);

  clearBtn.addEventListener('click', function() {
    // Reset draft to defaults (first chip in each row is always the "any/all" default)
    sheet.querySelectorAll('.pc-chip-row').forEach(function(row) {
      var target = row.getAttribute('data-target');
      if (isMultiRow(row)) { draft[target] = []; return; } // TASK-A3 — no chips selected = no filter
      var firstChip = row.querySelector('.pc-chip');
      draft[target] = firstChip ? firstChip.getAttribute('data-value') : '';
    });
    renderChipSelection();
    clearA3Controls(); // TASK-A3
    // Sheet stays open (spec requirement)
  });

  applyBtn.addEventListener('click', function() {
    Object.keys(draft).forEach(function(id) {
      var el = document.getElementById(id);
      if (!el) return;
      el.value = Array.isArray(draft[id]) ? draft[id].join(',') : draft[id]; // TASK-A3 — comma-join multi-select arrays
    });
    applyA3ControlsToHidden(); // TASK-A3
    if (window.pcApplyAndRender) window.pcApplyAndRender();
    closeSheet();
  });
```
<!-- TASK-A3 END -->

The two standalone helper blocks inserted into that mechanism (shown again individually for reference — both are already part of the full new code above):

```javascript
  // TASK-A3 START — multi-select support. A row with data-mode="multi"
  // (Species, Size) stores an ARRAY of selected values in draft[target]
  // instead of a single string; every other row keeps the original
  // single-select behavior untouched. isMultiRow() is the one shared
  // check every function below branches on, so the two behaviors never
  // drift apart.
  function isMultiRow(row) { return row.getAttribute('data-mode') === 'multi'; }
  // TASK-A3 END
```
```javascript
  // TASK-A3 START — the new non-chip controls (Age min/max, Good With
  // checkboxes, Distance + unit, Listing Type reuses the chip mechanism
  // above so it needs no extra code here, Urgent/Verified toggle
  // switches) get their own small seed/render/apply/clear helpers,
  // wired into the exact same sheet lifecycle (openSheet/clearBtn/
  // applyBtn) as the chip system, per Rule 12's "one connected system"
  // expectation rather than a second, separate flow the user would
  // experience inconsistently (e.g. Clear not clearing everything).
  var pcAgeMinInput = document.getElementById('pcAgeMinInput');
  var pcAgeMaxInput = document.getElementById('pcAgeMaxInput');
  var pcGoodKidsCheckbox = document.getElementById('pcGoodKidsCheckbox');
  var pcGoodOtherPetsCheckbox = document.getElementById('pcGoodOtherPetsCheckbox');
  var pcDistanceInput = document.getElementById('pcDistanceInput');
  var pcUrgentToggle = document.getElementById('pcUrgentToggle');
  var pcVerifiedToggle = document.getElementById('pcVerifiedToggle');

  function setToggleVisual(btn, on) {
    if (!btn) return;
    btn.classList.toggle('on', !!on);
    btn.setAttribute('aria-checked', on ? 'true' : 'false');
  }

  function seedA3ControlsFromHidden() {
    if (pcAgeMinInput) pcAgeMinInput.value = document.getElementById('filterAgeMin').value || '';
    if (pcAgeMaxInput) pcAgeMaxInput.value = document.getElementById('filterAgeMax').value || '';
    if (pcGoodKidsCheckbox) pcGoodKidsCheckbox.checked = document.getElementById('filterGoodKids').value === '1';
    if (pcGoodOtherPetsCheckbox) pcGoodOtherPetsCheckbox.checked = document.getElementById('filterGoodOtherPets').value === '1';
    if (pcDistanceInput) pcDistanceInput.value = document.getElementById('filterDistance').value || '';
    setToggleVisual(pcUrgentToggle, document.getElementById('filterUrgentOnly').value === '1');
    setToggleVisual(pcVerifiedToggle, document.getElementById('filterVerifiedOnly').value === '1');
  }

  function applyA3ControlsToHidden() {
    if (pcAgeMinInput) document.getElementById('filterAgeMin').value = pcAgeMinInput.value || '';
    if (pcAgeMaxInput) document.getElementById('filterAgeMax').value = pcAgeMaxInput.value || '';
    if (pcGoodKidsCheckbox) document.getElementById('filterGoodKids').value = pcGoodKidsCheckbox.checked ? '1' : '';
    if (pcGoodOtherPetsCheckbox) document.getElementById('filterGoodOtherPets').value = pcGoodOtherPetsCheckbox.checked ? '1' : '';
    if (pcDistanceInput) document.getElementById('filterDistance').value = pcDistanceInput.value || '';
    document.getElementById('filterUrgentOnly').value = (pcUrgentToggle && pcUrgentToggle.classList.contains('on')) ? '1' : '';
    document.getElementById('filterVerifiedOnly').value = (pcVerifiedToggle && pcVerifiedToggle.classList.contains('on')) ? '1' : '';
  }

  function clearA3Controls() {
    if (pcAgeMinInput) pcAgeMinInput.value = '';
    if (pcAgeMaxInput) pcAgeMaxInput.value = '';
    if (pcGoodKidsCheckbox) pcGoodKidsCheckbox.checked = false;
    if (pcGoodOtherPetsCheckbox) pcGoodOtherPetsCheckbox.checked = false;
    if (pcDistanceInput) pcDistanceInput.value = '';
    setToggleVisual(pcUrgentToggle, false);
    setToggleVisual(pcVerifiedToggle, false);
  }

  if (pcUrgentToggle) pcUrgentToggle.addEventListener('click', function() {
    setToggleVisual(this, !this.classList.contains('on'));
  });
  if (pcVerifiedToggle) pcVerifiedToggle.addEventListener('click', function() {
    setToggleVisual(this, !this.classList.contains('on'));
  });

  // "Use my location" — browser Geolocation API only, no third-party
  // network call (see the CSS-block comment above for why). Stores the
  // visitor's coordinates on window.pcVisitorCoords for applyAndRender()'s
  // distance calculation to read; this is intentionally session-only
  // (never persisted), since it's a live "where am I right now" input,
  // not a saved preference.
  var pcLocateBtn = document.getElementById('pcLocateBtn');
  var pcLocateStatus = document.getElementById('pcLocateStatus');
  if (pcLocateBtn) pcLocateBtn.addEventListener('click', function() {
    if (!navigator.geolocation) {
      if (pcLocateStatus) pcLocateStatus.textContent = 'Location is not supported by this browser.';
      return;
    }
    pcLocateBtn.classList.add('busy');
    if (pcLocateStatus) pcLocateStatus.textContent = 'Getting your location…';
    navigator.geolocation.getCurrentPosition(function(pos) {
      window.pcVisitorCoords = { lat: pos.coords.latitude, lng: pos.coords.longitude };
      pcLocateBtn.classList.remove('busy');
      if (pcLocateStatus) pcLocateStatus.textContent = 'Location set.';
    }, function() {
      pcLocateBtn.classList.remove('busy');
      if (pcLocateStatus) pcLocateStatus.textContent = 'Could not get your location — check browser permissions.';
    }, { timeout: 10000 });
  });
  // TASK-A3 END
```

---

#### 8. `populateFilters()` — Species chip population, and the Distance lookup table/calculation added after it

<!-- TASK-A3 START -->
**Old code:**
```javascript
    // Build breed chips inside the filter sheet
    var breedChipRow = document.getElementById('pcBreedChipRow');
    if (breedChipRow) {
      // Keep the "All Breeds" default chip, remove any previously added breed chips
      breedChipRow.querySelectorAll('.pc-chip:not([data-value=""])').forEach(function(c) { c.remove(); });
      breeds.forEach(function(breed) {
        var chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'pc-chip';
        chip.setAttribute('data-value', breed);
        chip.textContent = breed;
        breedChipRow.appendChild(chip);
      });
      if (window.pcLayoutAllChipRows) window.pcLayoutAllChipRows();
    }
  }

```
**New code:**
```javascript
    // Build breed chips inside the filter sheet
    var breedChipRow = document.getElementById('pcBreedChipRow');
    if (breedChipRow) {
      // Keep the "All Breeds" default chip, remove any previously added breed chips
      breedChipRow.querySelectorAll('.pc-chip:not([data-value=""])').forEach(function(c) { c.remove(); });
      breeds.forEach(function(breed) {
        var chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'pc-chip';
        chip.setAttribute('data-value', breed);
        chip.textContent = breed;
        breedChipRow.appendChild(chip);
      });
      if (window.pcLayoutAllChipRows) window.pcLayoutAllChipRows();
    }

    // TASK-A3 START — Species chips, same dynamic-extraction pattern as
    // breed above (Task A3 spec: "no hardcoded species list"). Unlike
    // Breed, this row has no "All"/default chip to preserve (a
    // multi-select checkbox list has no default value to keep — zero
    // checked means "no species filter"), so every chip is rebuilt fresh
    // each call rather than skipping the first one.
    var species = allPets
      .map(function(p) { return p.species; })
      .filter(function(s) { return s && s.trim(); });
    species = [...new Set(species)].sort(function(a, b) { return a.localeCompare(b); });

    var speciesChipRow = document.getElementById('pcSpeciesChipRow');
    if (speciesChipRow) {
      speciesChipRow.innerHTML = '';
      species.forEach(function(sp) {
        var chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'pc-chip';
        chip.setAttribute('data-value', sp);
        chip.textContent = sp;
        speciesChipRow.appendChild(chip);
      });
      if (window.pcLayoutAllChipRows) window.pcLayoutAllChipRows();
    }
    // TASK-A3 END
  }

```
<!-- TASK-A3 END -->

Immediately after `populateFilters()` closes, the Distance filter's lookup table and distance calculation were added — purely new code, inserted directly before the pre-existing `var searchTerm = '';` line:

<!-- TASK-A3 START -->
**Old code:**
```javascript
  var searchTerm = '';
```
**New code:**
```javascript
  // TASK-A3 START — small built-in city -> lat/long lookup table for the
  // Distance filter (see the HTML-block comment above the Distance group
  // for why this approach was chosen over a live geocoding API call).
  // Matched against pet.json's free-text `location` field (e.g.
  // "Los Angeles, CA") by exact case-insensitive string match. Flagged
  // limitation: only covers these cities — expand as real merchant
  // locations are seen, or replace entirely once listings carry real
  // coordinates (see completion file).
  var PC_CITY_COORDS = {
    'los angeles, ca': { lat: 34.0522, lng: -118.2437 },
    'san francisco, ca': { lat: 37.7749, lng: -122.4194 },
    'san diego, ca': { lat: 32.7157, lng: -117.1611 },
    'new york, ny': { lat: 40.7128, lng: -74.0060 },
    'chicago, il': { lat: 41.8781, lng: -87.6298 },
    'houston, tx': { lat: 29.7604, lng: -95.3698 },
    'phoenix, az': { lat: 33.4484, lng: -112.0740 },
    'philadelphia, pa': { lat: 39.9526, lng: -75.1652 },
    'seattle, wa': { lat: 47.6062, lng: -122.3321 },
    'denver, co': { lat: 39.7392, lng: -104.9903 },
    'miami, fl': { lat: 25.7617, lng: -80.1918 },
    'atlanta, ga': { lat: 33.7490, lng: -84.3880 },
    'boston, ma': { lat: 42.3601, lng: -71.0589 },
    'toronto, on': { lat: 43.6532, lng: -79.3832 },
    'vancouver, bc': { lat: 49.2827, lng: -123.1207 },
    'london': { lat: 51.5074, lng: -0.1278 },
    'sydney, nsw': { lat: -33.8688, lng: 151.2093 },
    'melbourne, vic': { lat: -37.8136, lng: 144.9631 }
  };

  // Contract values of the listing `status` enum (Task D1 + E2) that must
  // never appear in default browse. `active` is the only shown value.
  var PC_NON_ACTIVE_STATUSES = ['pending_placement', 'placement_complete', 'placement_fell_through', 'no_longer_available'];

  // Haversine great-circle distance between two lat/long points.
  function pcHaversineDistance(lat1, lng1, lat2, lng2, unit) {
    var R = unit === 'km' ? 6371 : 3958.8; // Earth radius, km or miles
    var toRad = function(d) { return d * Math.PI / 180; };
    var dLat = toRad(lat2 - lat1);
    var dLng = toRad(lng2 - lng1);
    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
            Math.sin(dLng / 2) * Math.sin(dLng / 2);
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  }
  // TASK-A3 END

  var searchTerm = '';
```
<!-- TASK-A3 END -->

---

#### 9. `applyAndRender()` — full new filter-predicate logic
Every line inside the filter predicate changed or had new conditions added around it, so the full function is shown as one old/new pair rather than as partial patches.

<!-- TASK-A3 START -->
**Old code:**
```javascript
  function applyAndRender() {
    var breed      = document.getElementById('filterBreed')?.value || '';
    var size       = document.getElementById('filterSize')?.value || '';
    var fee        = document.getElementById('filterFee')?.value || '';
    var age        = document.getElementById('filterAge')?.value || '';
    var sort       = document.getElementById('sortBy')?.value || 'name';
    var compatible = document.getElementById('filterCompatible')?.value || '';
    var normSearch = window.pcNormalizeSearch ? window.pcNormalizeSearch(searchTerm) : searchTerm.toLowerCase();

    var filtered = allPets.filter(function(p) {
      if (breed && p.breed !== breed) return false;
      if (size && p.size !== size) return false;
      if (normSearch) {
        var breedNorm = (p.breed || '').toLowerCase();
        var idNorm = (p.pet_id || '').toLowerCase().replace(/-/g, '');
        var matchesBreed = breedNorm.indexOf(normSearch) !== -1;
        var matchesId = idNorm.indexOf(normSearch) !== -1;
        if (!matchesBreed && !matchesId) return false;
      }
      if (age) {
        var petAge = p.age_years || 0;
        if (age === '0-1' && petAge >= 1) return false;
        if (age === '1-3' && (petAge < 1 || petAge > 3)) return false;
        if (age === '3-6' && (petAge < 3 || petAge > 6)) return false;
        if (age === '6+' && petAge < 6) return false;
      }
      if (fee) {
        var petFee = p.adoption_fee || 0;
        if (fee === '0-100' && petFee > 100) return false;
        if (fee === '100-500' && (petFee < 100 || petFee > 500)) return false;
        if (fee === '500-1000' && (petFee < 500 || petFee > 1000)) return false;
        if (fee === '1000+' && petFee < 1000) return false;
      }
      if (compatible) {
        // Untamed: no to ALL compatibility fields
        if (compatible === 'untamed') {
          if (p.compatible_kids === 'yes' || p.compatible_dogs === 'yes' ||
              p.compatible_cats === 'yes' || p.compatible_other_pets === 'yes') return false;
        } else if (compatible === 'kids')       { if (p.compatible_kids !== 'yes')       return false; }
        else if (compatible === 'dogs')         { if (p.compatible_dogs !== 'yes')       return false; }
        else if (compatible === 'cats')         { if (p.compatible_cats !== 'yes')       return false; }
        else if (compatible === 'other_pets')   { if (p.compatible_other_pets !== 'yes') return false; }
      }
      return true;
    });

```
**New code:**
```javascript
  function applyAndRender() {
    var breed      = document.getElementById('filterBreed')?.value || '';
    var fee        = document.getElementById('filterFee')?.value || '';
    var sort       = document.getElementById('sortBy')?.value || 'name';
    var compatible = document.getElementById('filterCompatible')?.value || '';
    var normSearch = window.pcNormalizeSearch ? window.pcNormalizeSearch(searchTerm) : searchTerm.toLowerCase();

    // TASK-A3 START — read the new filter controls' current values.
    var speciesSel   = (document.getElementById('filterSpecies')?.value || '').split(',').filter(Boolean);
    var sizeSel      = (document.getElementById('filterSizeMulti')?.value || '').split(',').filter(Boolean);
    var ageMin       = parseFloat(document.getElementById('filterAgeMin')?.value);
    var ageMax       = parseFloat(document.getElementById('filterAgeMax')?.value);
    var goodKids     = document.getElementById('filterGoodKids')?.value === '1';
    var goodOtherPets = document.getElementById('filterGoodOtherPets')?.value === '1';
    var distanceVal  = parseFloat(document.getElementById('filterDistance')?.value);
    var distanceUnit = document.getElementById('filterDistanceUnit')?.value || 'mi';
    var listingType  = document.getElementById('filterListingType')?.value || '';
    var urgentOnly   = document.getElementById('filterUrgentOnly')?.value === '1';
    var verifiedOnly = document.getElementById('filterVerifiedOnly')?.value === '1';
    var visitorCoords = window.pcVisitorCoords || null;
    // TASK-A3 END

    var filtered = allPets.filter(function(p) {
      if (breed && p.breed !== breed) return false;
      // TASK-A3 START — Size is now multi-select (OR within the group,
      // AND with every other group, per spec): "matches ANY selected
      // size" rather than "matches THE selected size."
      if (sizeSel.length && sizeSel.indexOf(p.size) === -1) return false;
      // Species: same OR-within-group / AND-across-groups logic.
      if (speciesSel.length && speciesSel.indexOf(p.species) === -1) return false;
      // TASK-A3 END
      if (normSearch) {
        var breedNorm = (p.breed || '').toLowerCase();
        var idNorm = (p.pet_id || '').toLowerCase().replace(/-/g, '');
        var matchesBreed = breedNorm.indexOf(normSearch) !== -1;
        var matchesId = idNorm.indexOf(normSearch) !== -1;
        if (!matchesBreed && !matchesId) return false;
      }
      // TASK-A3 START — Age changed from fixed buckets to a real min/max
      // range read directly against age_years, per spec. Either bound
      // may be left blank (open-ended range).
      if (!isNaN(ageMin) && (p.age_years || 0) < ageMin) return false;
      if (!isNaN(ageMax) && (p.age_years || 0) > ageMax) return false;
      // TASK-A3 END
      if (fee) {
        var petFee = p.adoption_fee || 0;
        if (fee === '0-100' && petFee > 100) return false;
        if (fee === '100-500' && (petFee < 100 || petFee > 500)) return false;
        if (fee === '500-1000' && (petFee < 500 || petFee > 1000)) return false;
        if (fee === '1000+' && petFee < 1000) return false;
      }
      if (compatible) {
        // Untamed: no to ALL compatibility fields
        if (compatible === 'untamed') {
          if (p.compatible_kids === 'yes' || p.compatible_dogs === 'yes' ||
              p.compatible_cats === 'yes' || p.compatible_other_pets === 'yes') return false;
        } else if (compatible === 'kids')       { if (p.compatible_kids !== 'yes')       return false; }
        else if (compatible === 'dogs')         { if (p.compatible_dogs !== 'yes')       return false; }
        else if (compatible === 'cats')         { if (p.compatible_cats !== 'yes')       return false; }
        else if (compatible === 'other_pets')   { if (p.compatible_other_pets !== 'yes') return false; }
      }
      // TASK-A3 START — "Good with kids"/"Good with other pets": two
      // independent checkboxes, each ANDed in only when checked (per
      // spec — reads the existing structured compatible_kids/
      // compatible_other_pets sub-fields, not the free-text temperament).
      if (goodKids && p.compatible_kids !== 'yes') return false;
      if (goodOtherPets && p.compatible_other_pets !== 'yes') return false;

      // Urgent-only toggle — reads A1's real is_urgent bool.
      if (urgentOnly && !p.is_urgent) return false;

      // Verified-only toggle — reads the merchant verification_status
      // contract (owned by Task B2). No merchant record system exists
      // yet, so this reads a stub field (merchant_verification_status)
      // on the pet record per the Shared Contracts mechanism — see
      // Task-A3-Completion.md for the reconciliation note once B2 ships.
      if (verifiedOnly && p.merchant_verification_status !== 'verified') return false;

      // Listing type toggle — reads A1's real listing_type enum.
      if (listingType && p.listing_type !== listingType) return false;

      // Distance/location radius. Skips (does not exclude) any listing
      // whose city isn't in the built-in lookup table, or when the
      // visitor hasn't shared their location yet — an unknown distance
      // is treated as "don't know," never as "too far," per the
      // flagged limitation documented above PC_CITY_COORDS.
      if (!isNaN(distanceVal) && distanceVal > 0 && visitorCoords) {
        var key = (p.location || '').trim().toLowerCase();
        var petCoords = PC_CITY_COORDS[key];
        if (petCoords) {
          var dist = pcHaversineDistance(visitorCoords.lat, visitorCoords.lng, petCoords.lat, petCoords.lng, distanceUnit);
          if (dist > distanceVal) return false;
        }
      }

      // Default browse view exclusions, using the SHARED CONTRACTS field
      // names exactly (no invented names):
      // 1) held_for_review (owned by Task C2): bool, default false. A
      //    listing where it is true never appears in browse/search.
      //    Strict === true so an absent field (all current data) passes.
      if (p.held_for_review === true) return false;
      // 2) status (owned by Task D1/E2): only `active` listings appear.
      //    The four non-active contract values are excluded by name.
      //    Legacy values already in real data ("Available"/"Adopted") are
      //    deliberately not in this list, so today's behavior is
      //    unchanged and the existing ADOPTED-badge display (adoption-
      //    website-project-summary.md Section 15.5) is untouched — see
      //    Task-A3-Completion.md for the flagged legacy-vs-enum question.
      if (PC_NON_ACTIVE_STATUSES.indexOf(p.status) !== -1) return false;
      // TASK-A3 END

      return true;
    });

```
<!-- TASK-A3 END -->

## New Files
No new files were created for this task — every change was to an existing file (see Code Changes above). Per the corrected zip instructions, this completion file is still sent inside **`Rehoming-Platform-Task-A3.zip`** even though there are no new files to go with it; the zip contains only this one document, at its root, not nested under any live path.

**New folder paths:** none. All seven live paths this task touches (`pets/cats/index.html`, `pets/dogs/index.html`, `pets/birds/index.html`, `pets/reptiles/index.html`, `pets/small-mammals/index.html`, `pets/livestocks/index.html`, `pets/aquariums/index.html`) already exist on the live site and are already documented in `adoption-website-project-summary.md` — none is a path I introduced, so there is no new folder to flag or create.

**`index.html` naming:** every live path above already ends in `/index.html`, matching the required convention; this task did not add or rename any page.

## Bugs Found Outside Task Scope

1. **Legacy `status`/"Adopted"-badge vs. the new canonical status enum.** The default-browse exclusion only removes the four named non-`active` enum values (`pending_placement`, `placement_complete`, `placement_fell_through`, `no_longer_available`); it deliberately leaves the pre-existing legacy `status` values ("Available"/"Adopted") alone, so the existing navy "ADOPTED" badge display (adoption-website-project-summary.md Section 15.5) is unchanged. Once Task D1's real status enum is live site-wide, the CEO/D1 should decide whether placed/adopted listings should then disappear from browse entirely or keep showing with the badge — I did not resolve that myself.
2. **Distance filter's city-coverage table is a disclosed, not hidden, limitation.** `PC_CITY_COORDS` covers roughly 18 major cities; a listing whose `location` string isn't an exact match is never excluded by Distance (treated as "unknown," not "too far"). Should be revisited once listings carry real coordinates.
3. **No bugs found in Task A1's or Task A2's existing code** while reading through `cats.html` for this task.

**Open questions already sent to the CEO separately (not built around):**
- Whether Task A3 is meant to add a "Needs Extra Love only" toggle (one sentence in the SHARED CONTRACTS section implies this, but A3's own filter list never names one, and Task A2 already built the pin/badge behavior).
- Whether the Verified-only toggle should resolve the merchant via the listing's free-text `posted_by`, or whether Task B2 will copy `verification_status` onto the listing directly.

## Self-Check Confirmation

- [x] Only the seven confirmed browse/search page files were touched (Scope Lock respected); no other file was changed.
- [x] Every block above is wrapped in `<!-- TASK-A3 START -->` / `<!-- TASK-A3 END -->` and carries explanatory comments in place in the real file.
- [x] Every "Old code" snippet above was copied character-for-character from the original `cats.html` given for this task, and verified programmatically (Python exact-string-count) to occur **exactly once** in that original file before this document was written.
- [x] Every "New code" snippet above was verified programmatically (exact substring match) to occur byte-for-byte in the real, final submitted `cats.html` before this document was written — not retyped or reconstructed from memory.
- [x] The nine old-code snippets were additionally checked against the six sibling originals (`dogs.html`, `birds.html`, `reptiles.html`, `small-mammals.html`, `livestocks.html`, `aquariums.html`) and occur exactly once in each, confirming the single diff above is safe to apply at all seven live paths.
- [x] No duplicate `id` attributes, balanced HTML tag nesting, and clean JS syntax verified on all 7 final files (`node --check` on the extracted inline script; a Python `html.parser`-based tag-balance check).
- [x] "New file" entries in Files Changed — there are none, so nothing is listed there.
- [x] Zip sent as `Rehoming-Platform-Task-A3.zip`, named from the task-assignments file's own name plus this task's number, not a name I invented; it contains only this completion file (no new code files, since none were created).
- [x] Checked the zip's one file against `config.php`, `pet.json`, `.env`, `applications/`, and `uploads/`: none of these are in it, and none were touched anywhere in this hand-in.
- [x] Reviewed Bugs Found Outside Task Scope and the open questions below once more immediately before zipping, to make sure every pending item, doubt, and new finding is captured here rather than left undocumented.
- [x] **Sandbox limitation, disclosed rather than glossed over:** no network access in this container, so no real browser/Playwright run was possible. The exact filter-predicate logic from `applyAndRender()` was ported into a standalone Node.js test harness and run against constructed fixture pets (9 passing cases, including combined multi-filter scenarios, the `held_for_review`/status exclusion, and edge cases). This is logic-level verification of the real submitted code, not a rendered click-through.

## SECOND VERIFICATION ROUND

**Trigger:** the Rehoming Platform task document was revised after this task's first submission (new SHARED CONTRACTS entries including the listing `held_for_review` field; a new rule requiring any dropdown to use the design system's modern dropdown). This section is a narrative record of that audit and of a later full re-audit; the resulting code is already folded into the single Code Changes section above, so the integrator applies one clean diff rather than a layered one.

**What changed as a result (summary — full text already incorporated above):**
1. Added the `held_for_review === true` exclusion to the default browse view.
2. Corrected the status exclusion to read the real contract field `status` (an earlier draft had used an invented field name) against the four named non-active enum values.
3. Replaced a native `<select>` originally used for the Distance unit (miles/km) with a Section 30 single-select chip row, since the modern-dropdown rule requires the design system's own control and a native select is a plain dropdown. **Dropdown confirmation: there are no dropdown menus anywhere in this task's work** — every control is a Section 30 chip row, checkbox, number input, or toggle switch.

**Shared contracts built against (and reconciliation status):**
- Listing core schema (A1): `is_urgent`, `listing_type` read directly — reconciled against Task-A1-Completion.md and the real schema file.
- Listing relationships (A2): no dependency; A2's code untouched.
- Listing `status` (D1/E2) and `held_for_review` (C2): read by contract name; both owning tasks' completion files do not exist yet, so reconciliation is still pending on their side.
- Merchant `verification_status` (B2): still an open question (see above) — the toggle reads a placeholder field name and will return no real results until this is answered.

**Full re-audit (later pass) — no functional bugs found.** Re-checked against both project summaries, the real schema, and `pet-posting-form-v2.html`:
- Confirmed `size` (`Small`/`Medium`/`Large`), `listing_type` (`permanent`/`foster`), `is_urgent` (boolean), and `compatible_kids`/`compatible_other_pets` (`yes`/`no`/`unknown`, labeled "Good with Kids?"/"Good with Other Pets?" on A1's own posting form) all match this filter code exactly, including casing.
- Confirmed species is free text on the posting form (not a fixed list), supporting the dynamic/no-hardcoded-list approach.
- Confirmed this page's existing `loadPets()` already loads every listing client-side before filtering (no pagination), so continuing the existing client-side `allPets.filter()` approach — rather than introducing a new AJAX re-query — was the correct, confirmed choice.
- Checked for an existing, reusable geolocation pattern before building a new one: `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` documents an ip-api.com call, but it is server-side PHP used only for admin login security auditing — reusing it here would need a new PHP endpoint outside this task's File Zone, so the browser's own Geolocation API was used instead (flagged as a possible future enhancement, not built).
- Flagged, not changed: the pre-existing single-select "Compatible With" group and the new "Good With" checkboxes both read `compatible_kids`/`compatible_other_pets`, so a visitor currently has two different controls that can affect the same two fields. Left as-is since the old group predates this project and isn't in A3's own filter list; whether to merge them is a design decision for the CEO/Design team.
