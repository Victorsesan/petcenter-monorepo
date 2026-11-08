# Task 3b — Completion Report

## Task Number
Task 3b (Project 4: Extend Location Filter to Canada, UK & Australia — named zone: `FILTER` extension, country-selector and geocoding-lookup logic only)

## What Was Required
Confirmed target markets for the location filter are USA, Canada, UK, and Australia (Task 3 was scoped and built before that was confirmed as US-only). This task extends Task 3's finished filter — not a new one — with a country selector defaulting to the visitor's own country when reasonably inferable, USA otherwise; a single free, no-API-key geocoding integration covering all four countries rather than a different lookup per country; confirmation that the existing radius/distance calculation works unchanged for non-US postal formats; and reuse of whatever caching Task 3 already had in place.

Per Task 3b's own Recommended Enhancements note, three items from Task 3's enhancements list were folded in here since this task already reopens the same file/zone: a "Use my location" geolocation button, and caching resolved lookups (already covered — see below). Server-side Haversine was **not** built — see Bugs/Scope note below.

## Files Changed

| File | Shared with another task? |
|---|---|
| `cats.html` | Yes — Task 5 (`CTA` zone) |
| `dogs.html` | Yes — Task 5 (`CTA` zone) |
| `birds.html` | Yes — Task 5 (`CTA` zone) |
| `reptiles.html` | Yes — Task 5 (`CTA` zone) |
| `aquariums.html` | Yes — Task 5 (`CTA` zone) |
| `livestocks.html` | Yes — Task 5 (`CTA` zone) |
| `small-mammals.html` | Yes — Task 5 (`CTA` zone) |

`pets.html` was **not** touched by this task — it only forwards a plain state string as a query parameter and does no geocoding, so the country dimension doesn't apply to it. No new files were created.

## Code Changes

### `cats.html`, `dogs.html`, `birds.html`, `reptiles.html`, `aquariums.html`, `livestocks.html`, `small-mammals.html`
Applied on top of each file's already-submitted Task 3 code (pulled from Task 3's own completion file / delivered files, not the pre-Task-3 originals). All 8 changes below were applied verbatim to all 7 category pages — verified present with an exact string-count check (1 match per file) after patching. `pets.html` is untouched by this task: it only carries a plain state string forward as a query param, with no geocoding or country dimension involved.
**HTML — `filterCountry` hidden `<select>` (US/Canada/UK/Australia), added next to the other Task 3 state holders**

*Old code:*
```html
    <input type="hidden" id="filterNearMeLat" value="">
    <input type="hidden" id="filterNearMeLng" value="">
    <!-- TASK-3 END -->

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
```

*New code:*
```html
    <input type="hidden" id="filterNearMeLat" value="">
    <input type="hidden" id="filterNearMeLng" value="">
    <!-- TASK-3b START — Country selector state holder. Confirmed target markets
         are USA/Canada/UK/Australia; this narrows Nominatim's countrycodes
         param (see pcGeocodeLocation) instead of always searching US-only. -->
    <select class="hidden-select" id="filterCountry" name="country" style="display:none;">
      <option value="us">United States</option>
      <option value="ca">Canada</option>
      <option value="gb">United Kingdom</option>
      <option value="au">Australia</option>
    </select>
    <!-- TASK-3b END -->
    <!-- TASK-3 END -->

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
```

**HTML — Country chip row, "Use my location" button, and postal-code hint added to the Near Me group in the sheet**

*Old code:*
```html
      <div class="pc-sheet-filter-group" data-filter="filterNearMe">
        <div class="pc-sheet-filter-group__label">Near Me</div>
        <div class="pc-nearme-row">
          <input type="text" id="pcNearMeInput" class="pc-nearme-input" placeholder="Enter a city, state or ZIP" autocomplete="off">
        </div>
        <div class="pc-chip-scroll">
          <div class="pc-chip-row" data-target="filterRadius" id="pcRadiusChipRow">
```

*New code:*
```html
      <div class="pc-sheet-filter-group" data-filter="filterNearMe">
        <div class="pc-sheet-filter-group__label">Near Me</div>

        <!-- TASK-3b START — Country selector: narrows the geocoding search to
             the selected country (Nominatim covers all four target markets
             through the same single integration Task 3 already built — only
             the countrycodes param needs to vary). Defaults to the visitor's
             own country when guessable from navigator.language, USA otherwise. -->
        <div class="pc-chip-scroll">
          <div class="pc-chip-row" data-target="filterCountry" id="pcCountryChipRow">
            <button type="button" class="pc-chip selected" data-value="us">USA</button>
            <button type="button" class="pc-chip" data-value="ca">Canada</button>
            <button type="button" class="pc-chip" data-value="gb">UK</button>
            <button type="button" class="pc-chip" data-value="au">Australia</button>
          </div>
        </div>
        <!-- TASK-3b END -->

        <div class="pc-nearme-row">
          <input type="text" id="pcNearMeInput" class="pc-nearme-input" placeholder="Enter a city, state or ZIP" autocomplete="off">
          <!-- TASK-3b START — "Use my location": one of Task 3's own Recommended
               Enhancements, folded into this task since it already reopens this
               file/zone. Skips geocoding entirely — the browser returns
               coordinates directly, so this works the same regardless of country. -->
          <button type="button" class="pc-nearme-geo-btn" id="pcUseMyLocationBtn" title="Use my current location" aria-label="Use my current location">📍</button>
          <!-- TASK-3b END -->
        </div>
        <!-- TASK-3b START — Postal-code format hint, text swaps with the selected country -->
        <div class="pc-postal-hint" id="pcPostalHint">Format: ZIP code, e.g. 90210</div>
        <!-- TASK-3b END -->
        <div class="pc-chip-scroll">
          <div class="pc-chip-row" data-target="filterRadius" id="pcRadiusChipRow">
```

**CSS — layout for the geo button next to the Near Me input, plus the postal-hint text style**

*Old code:*
```css
/* TASK-3 START — Location filter: "Near Me" free-text input + status line */
.pc-nearme-row { margin-bottom: 10px; }
.pc-nearme-input {
  width: 100%; background: #1c2c56; border: 1px solid rgba(255,255,255,.15);
  color: #fff; border-radius: 10px; padding: 11px 14px;
  font-family: 'Sora', sans-serif; font-size: 13.5px; outline: none;
}
.pc-nearme-input::placeholder { color: rgba(255,255,255,.45); }
.pc-nearme-input:focus { border-color: var(--gold); }
.pc-nearme-status {
  font-family: 'Sora', sans-serif; font-size: 12px; color: rgba(255,255,255,.6);
  margin-top: 8px; min-height: 14px;
}
/* TASK-3 END */
```

*New code:*
```css
/* TASK-3 START — Location filter: "Near Me" free-text input + status line */
.pc-nearme-row { margin-bottom: 10px; display: flex; gap: 8px; align-items: stretch; }
.pc-nearme-input {
  width: 100%; background: #1c2c56; border: 1px solid rgba(255,255,255,.15);
  color: #fff; border-radius: 10px; padding: 11px 14px;
  font-family: 'Sora', sans-serif; font-size: 13.5px; outline: none;
}
.pc-nearme-input::placeholder { color: rgba(255,255,255,.45); }
.pc-nearme-input:focus { border-color: var(--gold); }
.pc-nearme-status {
  font-family: 'Sora', sans-serif; font-size: 12px; color: rgba(255,255,255,.6);
  margin-top: 8px; min-height: 14px;
}
/* TASK-3 END */

/* TASK-3b START — Use My Location button + postal-code format hint */
.pc-nearme-geo-btn {
  flex: 0 0 auto; width: 42px; background: #1c2c56; border: 1px solid rgba(255,255,255,.15);
  border-radius: 10px; font-size: 16px; cursor: pointer; color: #fff;
}
.pc-nearme-geo-btn:hover { border-color: var(--gold); }
.pc-postal-hint {
  font-family: 'Sora', sans-serif; font-size: 11.5px; color: rgba(255,255,255,.45);
  margin: -4px 0 10px 2px;
}
/* TASK-3b END */
```

**JS — `pcGeocodeLocation()` now takes a `countryCode` and passes it to Nominatim's `countrycodes` param instead of hardcoding `us`**

*Old code:*
```javascript
function pcGeocodeLocation(query) {
  var key = (query || '').trim().toLowerCase();
  if (!key) return Promise.resolve(null);
  if (Object.prototype.hasOwnProperty.call(pcGeocodeCache, key)) {
    return Promise.resolve(pcGeocodeCache[key]);
  }
  pcGeocodeQueue = pcGeocodeQueue.then(function() {
    return new Promise(function(resolve) {
      setTimeout(function() {
        fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=us&q=' + encodeURIComponent(query))
```

*New code:*
```javascript
function pcGeocodeLocation(query, countryCode) {
  var trimmed = (query || '').trim();
  if (!trimmed) return Promise.resolve(null);
  var key = trimmed.toLowerCase() + '|' + (countryCode || 'us'); // TASK-3b — cache key now includes country so the same text in two countries doesn't collide
  if (Object.prototype.hasOwnProperty.call(pcGeocodeCache, key)) {
    return Promise.resolve(pcGeocodeCache[key]);
  }
  pcGeocodeQueue = pcGeocodeQueue.then(function() {
    return new Promise(function(resolve) {
      setTimeout(function() {
        // TASK-3b — countrycodes now driven by the selected country (us/ca/gb/au)
        // instead of being hardcoded to "us", covering all 4 confirmed target
        // markets through this same single Nominatim integration.
        fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=' + (countryCode || 'us') + '&q=' + encodeURIComponent(query))
```

**JS — `pcGeocodeAllPetLocations()` explicitly geocodes pet locations as `us` (Pet Center's pets are all US-located per `pet.json`), independent of whichever country the visitor is searching**

*Old code:*
```javascript
  return distinct.reduce(function(chain, loc) {
    return chain.then(function() { return pcGeocodeLocation(loc); });
  }, Promise.resolve());
```

*New code:*
```javascript
  return distinct.reduce(function(chain, loc) {
    return chain.then(function() { return pcGeocodeLocation(loc, 'us'); }); // TASK-3b — Pet Center's pets are all US-located (confirmed by pet.json), so pet-side geocoding stays US regardless of which country the visitor searches
  }, Promise.resolve());
```

**JS — postal-hint refresh wired into the existing chip-click delegation, plus new default-country detection (`navigator.language`) and the "Use my location" button handler**

*Old code:*
```javascript
  sheetBody.addEventListener('click', function(e) {
    var chip = e.target.closest('.pc-chip');
    if (!chip) return;
    var row = chip.closest('.pc-chip-row');
    if (!row) return;
    var target = row.getAttribute('data-target');
    draft[target] = chip.getAttribute('data-value');
    renderChipSelection();
  });
```

*New code:*
```javascript
  sheetBody.addEventListener('click', function(e) {
    var chip = e.target.closest('.pc-chip');
    if (!chip) return;
    var row = chip.closest('.pc-chip-row');
    if (!row) return;
    var target = row.getAttribute('data-target');
    draft[target] = chip.getAttribute('data-value');
    renderChipSelection();
    // TASK-3b START — refresh the postal-code hint whenever the country chip changes
    if (row.id === 'pcCountryChipRow') pcUpdatePostalHint(chip.getAttribute('data-value'));
    // TASK-3b END
  });

  // TASK-3b START — Country selector: postal-format hints + a country default
  // guessed from the visitor's browser locale (no IP lookup, no account —
  // per this task's Recommended Enhancement #2), USA if nothing usable comes back.
  var pcPostalHints = {
    us: 'Format: ZIP code, e.g. 90210',
    ca: 'Format: postal code, e.g. A1A 1A1',
    gb: 'Format: postcode, e.g. SW1A 1AA',
    au: 'Format: postcode, e.g. 2000'
  };
  function pcUpdatePostalHint(countryCode) {
    var hintEl = document.getElementById('pcPostalHint');
    if (hintEl) hintEl.textContent = pcPostalHints[countryCode] || pcPostalHints.us;
  }
  (function pcSetDefaultCountry() {
    var lang = (navigator.language || navigator.userLanguage || '').toLowerCase();
    var guess = 'us';
    if (lang.indexOf('en-ca') === 0 || lang.indexOf('fr-ca') === 0) guess = 'ca';
    else if (lang.indexOf('en-gb') === 0) guess = 'gb';
    else if (lang.indexOf('en-au') === 0) guess = 'au';
    var countrySelect = document.getElementById('filterCountry');
    var countryRow = document.getElementById('pcCountryChipRow');
    if (countrySelect) countrySelect.value = guess;
    if (countryRow) {
      countryRow.querySelectorAll('.pc-chip').forEach(function(c) {
        c.classList.toggle('selected', c.getAttribute('data-value') === guess);
      });
    }
    pcUpdatePostalHint(guess);
  })();
  // TASK-3b END

  // TASK-3b START — "Use my location": browser geolocation hands back
  // coordinates directly, so this skips Nominatim geocoding entirely and
  // works the same regardless of which country chip is selected.
  var pcUseMyLocationBtn = document.getElementById('pcUseMyLocationBtn');
  if (pcUseMyLocationBtn) {
    pcUseMyLocationBtn.addEventListener('click', function() {
      var statusEl = document.getElementById('pcNearMeStatus');
      var nearMeInputEl = document.getElementById('pcNearMeInput');
      if (!navigator.geolocation) {
        if (statusEl) statusEl.textContent = "Location services aren't available in this browser.";
        return;
      }
      if (statusEl) statusEl.textContent = 'Finding your location…';
      navigator.geolocation.getCurrentPosition(function(pos) {
        document.getElementById('filterNearMeLat').value = pos.coords.latitude;
        document.getElementById('filterNearMeLng').value = pos.coords.longitude;
        if (nearMeInputEl) nearMeInputEl.value = 'Current location';
        draft['filterNearMeLat'] = String(pos.coords.latitude);
        draft['filterNearMeLng'] = String(pos.coords.longitude);
        // A "near me" search needs a radius to actually filter by — default
        // to 25mi if the visitor hasn't already picked one.
        var radiusRow = document.getElementById('pcRadiusChipRow');
        var currentRadius = radiusRow && radiusRow.querySelector('.pc-chip.selected');
        if (radiusRow && (!currentRadius || currentRadius.getAttribute('data-value') === '')) {
          var twentyFive = radiusRow.querySelector('[data-value="25"]');
          if (twentyFive) {
            radiusRow.querySelectorAll('.pc-chip').forEach(function(c) { c.classList.remove('selected'); });
            twentyFive.classList.add('selected');
            draft['filterRadius'] = '25';
          }
        }
        if (statusEl) statusEl.textContent = 'Got it — hit Apply to see pets near you.';
      }, function() {
        if (statusEl) statusEl.textContent = "Couldn't get your location — try typing it instead.";
      });
    });
  }
  // TASK-3b END
```

**JS — Apply button now passes the selected country to geocoding, and skips re-geocoding when "Use my location" already supplied real coordinates**

*Old code:*
```javascript
    var radiusVal = document.getElementById('filterRadius')?.value || '';
    var nearMeInput = document.getElementById('pcNearMeInput');
    var nearMeText = nearMeInput ? nearMeInput.value.trim() : '';
    var statusEl = document.getElementById('pcNearMeStatus');

    closeSheet();

    if (radiusVal && nearMeText) {
      if (statusEl) statusEl.textContent = 'Finding pets near "' + nearMeText + '"…';
      if (window.pcShowSearchLoading) window.pcShowSearchLoading();
      pcGeocodeLocation(nearMeText).then(function(coords) {
```

*New code:*
```javascript
    var radiusVal = document.getElementById('filterRadius')?.value || '';
    var nearMeInput = document.getElementById('pcNearMeInput');
    var nearMeText = nearMeInput ? nearMeInput.value.trim() : '';
    var statusEl = document.getElementById('pcNearMeStatus');
    var countryVal = document.getElementById('filterCountry')?.value || 'us'; // TASK-3b

    closeSheet();

    // TASK-3b — "Use my location" already wrote real coordinates directly (no
    // geocoding involved), so Apply must not try to geocode the placeholder
    // "Current location" text over the top of them.
    var pcAlreadyHaveCoords = nearMeText === 'Current location' &&
      document.getElementById('filterNearMeLat')?.value &&
      document.getElementById('filterNearMeLng')?.value;

    if (pcAlreadyHaveCoords) {
      if (window.pcShowSearchLoading) window.pcShowSearchLoading();
      pcGeocodeAllPetLocations().then(function() {
        if (statusEl) statusEl.textContent = '';
        if (window.pcApplyAndRender) window.pcApplyAndRender();
      });
    } else if (radiusVal && nearMeText) {
      if (statusEl) statusEl.textContent = 'Finding pets near "' + nearMeText + '"…';
      if (window.pcShowSearchLoading) window.pcShowSearchLoading();
      pcGeocodeLocation(nearMeText, countryVal).then(function(coords) {
```

**JS — Clear button also resets the country selector back to USA and refreshes the postal hint**

*Old code:*
```javascript
    document.getElementById('filterNearMeLat').value = '';
    document.getElementById('filterNearMeLng').value = '';
    // TASK-3 END
```

*New code:*
```javascript
    document.getElementById('filterNearMeLat').value = '';
    document.getElementById('filterNearMeLng').value = '';
    // TASK-3b START — reset country back to USA and refresh the postal hint to match
    var countrySelectEl = document.getElementById('filterCountry');
    if (countrySelectEl) countrySelectEl.value = 'us';
    var countryRowEl = document.getElementById('pcCountryChipRow');
    if (countryRowEl) {
      countryRowEl.querySelectorAll('.pc-chip').forEach(function(c) {
        c.classList.toggle('selected', c.getAttribute('data-value') === 'us');
      });
    }
    pcUpdatePostalHint('us');
    // TASK-3b END
    // TASK-3 END
```



## Confirmation: radius/distance calculation works unchanged for non-US postal formats
Task 3's Haversine function (`pcHaversineMiles`) operates purely on latitude/longitude pairs — it has no awareness of postal-code format at all, so nothing about it needed to change for Canada/UK/Australia. The only country-dependent step is geocoding *into* those coordinates, which is what this task's `countrycodes` change actually addresses. Confirmed by reading the function again against this task's changes: no edit was needed or made to it.

## Confirmation: single geocoding integration, existing caching reused
Task 3 was already built on OpenStreetMap Nominatim, which covers all four confirmed target markets — so this task did **not** add a second lookup service per country, only made the existing `countrycodes` param dynamic (see `pcGeocodeLocation` above). Task 3's in-memory geocode cache is reused as-is; this task only widened its key (`text|countryCode`) so the same typed text in two different countries doesn't collide in the cache. Per this task's own instructions ("build on top of whatever caching Task 3 already put in place... so repeated searches for the same location don't exceed [Nominatim's rate limit]"), no new persistent cache was added.

## Bugs Found Outside Task Scope / Scope Decisions
- **Server-side Haversine calculation was not built.** It was listed as one of the three enhancement items folded into this task, but implementing it would mean adding a new backend (PHP) endpoint — not one of "the same files from Task 3" this task's scope is limited to. Flagging this as a scope decision rather than an oversight: building it would have required either violating Scope Lock or requesting a new file/task, neither of which this task's assignment authorizes on its own.
- Carrying forward, unchanged, the bug already logged in Task 3's completion file: `publish-pet.php` still does not write a `location` field to `pet.json`. Not re-fixed here for the same Scope Lock reason as before.

## Self-Check Confirmation
Confirmed: only the 7 category pages listed under Files Changed were modified — `pets.html`, `adoption-website-project-summary.md`, `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, `publish-pet.php`, and `pet.json` were not touched. Every code block added in this task, in every file, is wrapped in a `TASK-3b START` / `TASK-3b END` anchor comment (kept separate from Task 3's own `TASK-3` anchors so the two tasks' changes stay individually traceable) and carries its own explanatory comment, matching what's pasted above exactly — verified with a string-match check against the real files before this report was written.

## Coordination Note
Built directly on top of Task 3's already-submitted code (same 7 files, same versions delivered in Task 3's completion), per the Sequential File Ownership Chain. Task 5's `CTA` zone is still a separate, parallel zone in these same files — if Task 5 has since submitted and this doesn't already reflect it, these files need a re-diff before final merge, same as noted in Task 3's own completion file.


---

## UPDATE — Server-Side Haversine (originally deferred, built per Victor's follow-up instruction)

This task's original submission (above) deliberately did not build server-side Haversine, reasoning that it required a new backend file outside Task 3's originally-assigned file list, and logged that as a scope decision rather than an oversight. Victor followed up directly instructing it be built regardless of file-scope, on the basis that it's a Recommended Enhancement — so it has now been built and is documented below as an addition to this same task rather than a new task number, since it's completing this task's own enhancement list rather than starting new work.

### New file: `geo-filter.php`

Assumed deployment path: `/adoptions/pets/geo-filter.php`, a sibling of the per-category folders it reads from — this matches the exact relative structure the front-end already fetches (`/adoptions/pets/{category}/index.json`, then `/adoptions/pets/{category}/{petId}/pet.json`). If the real deployment path differs, only this file's location and the front-end's fetch URL (in the JS changes below) need to change.

This is a new file, so per the completion-doc format's own exception for new files, its full contents are given once below rather than as an old/new diff (there is no "old" version to diff against):

```php
<?php
/**
 * geo-filter.php — TASK-3b addition (moves Haversine off the client per the
 * Recommended Enhancement, and moves pet-location geocoding onto the server
 * with a disk-persisted cache instead of Task 3's in-memory, per-page-load
 * cache — same OpenStreetMap Nominatim integration Task 3 already used, just
 * run once here instead of once per visitor's browser).
 *
 * Deployment path assumed to be /adoptions/pets/geo-filter.php, a sibling of
 * the per-category folders it reads from (/adoptions/pets/{category}/...),
 * matching the exact same relative structure the front-end already fetches
 * (index.json -> per-pet pet.json). If the real deployment path differs,
 * only the two require paths below and the front-end's fetch URL need to
 * change — nothing else here is path-dependent.
 *
 * The visitor's own typed "near me" text is still geocoded client-side
 * (unchanged from Task 3b) since that's a single one-off call per search;
 * this endpoint is what makes the *pet* side and the distance math
 * server-side and persistently cached, which is the part that actually
 * repeats across every visitor and every search.
 *
 * GET params:
 *   category (required) — one of the 7 category slugs
 *   lat, lng (required) — the visitor's already-resolved coordinates
 *   radius   (required) — miles
 *
 * Response: { "ids": ["PET-ID-1", "PET-ID-2", ...] }  — nearest-first
 */

header('Content-Type: application/json');

$validCategories = ['cats', 'dogs', 'birds', 'reptiles', 'aquariums', 'livestocks', 'small-mammals'];

$category = isset($_GET['category']) ? $_GET['category'] : '';
$lat      = isset($_GET['lat']) && $_GET['lat'] !== '' ? floatval($_GET['lat']) : null;
$lng      = isset($_GET['lng']) && $_GET['lng'] !== '' ? floatval($_GET['lng']) : null;
$radius   = isset($_GET['radius']) && $_GET['radius'] !== '' ? floatval($_GET['radius']) : null;

if (!in_array($category, $validCategories, true) || $lat === null || $lng === null || !$radius) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid category, lat, lng, or radius']);
    exit;
}

$categoryDir = __DIR__ . '/pets/' . $category . '/';
$indexFile   = $categoryDir . 'index.json';

if (!file_exists($indexFile)) {
    echo json_encode(['ids' => []]);
    exit;
}

$index   = json_decode(file_get_contents($indexFile), true);
$petIds  = (is_array($index) && !empty($index['pets']) && is_array($index['pets'])) ? $index['pets'] : [];

// ─── Persistent, cross-visitor, cross-category geocode cache ───────────────
// Task 3's client-side cache reset on every page load; this file doesn't.
$cacheFile = __DIR__ . '/geo-cache.json';
$cache = file_exists($cacheFile) ? json_decode(file_get_contents($cacheFile), true) : [];
if (!is_array($cache)) {
    $cache = [];
}
$cacheDirty = false;

/**
 * Great-circle (Haversine) distance in miles between two lat/lng points.
 * Same formula as Task 3's client-side pcHaversineMiles — moved here per
 * this task's "server-side Haversine" requirement.
 */
function pc_haversine_miles($lat1, $lng1, $lat2, $lng2) {
    $earthRadiusMiles = 3958.8;
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
    return $earthRadiusMiles * 2 * atan2(sqrt($a), sqrt(1 - $a));
}

/**
 * Geocodes a "City, State" string via OpenStreetMap Nominatim (free, no API
 * key — same service Task 3 already used client-side), using and updating
 * the shared on-disk cache passed by reference. Pet Center's pets are all
 * US-located (confirmed via pet.json), so this is always restricted to "us"
 * regardless of which country a visitor is searching from — matching Task
 * 3b's own client-side logic for pet-side geocoding.
 */
function pc_geocode_pet_location($location, &$cache, &$cacheDirty) {
    $key = strtolower(trim($location));
    if ($key === '') {
        return null;
    }
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    // Nominatim's usage policy caps requests at ~1/sec. This only runs for a
    // location not already in the shared cache file, which — once any
    // visitor has ever searched near a given pet's location — should be rare.
    usleep(1100000);

    $url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=us&q=' . urlencode($location);
    $context = stream_context_create([
        'http' => [
            'header'  => "User-Agent: PetCenter-GeoFilter/1.0 (contact: admin@petcenter.example)\r\n",
            'timeout' => 5,
        ],
    ]);
    $response = @file_get_contents($url, false, $context);
    $results  = $response ? json_decode($response, true) : null;

    $coords = (is_array($results) && count($results) > 0)
        ? ['lat' => floatval($results[0]['lat']), 'lng' => floatval($results[0]['lon'])]
        : null;

    $cache[$key] = $coords;
    $cacheDirty = true;
    return $coords;
}

$matches = [];

foreach ($petIds as $petId) {
    $petFile = $categoryDir . $petId . '/pet.json';
    if (!file_exists($petFile)) {
        continue;
    }
    $pet = json_decode(file_get_contents($petFile), true);
    if (!is_array($pet) || empty($pet['location'])) {
        continue;
    }

    $coords = pc_geocode_pet_location($pet['location'], $cache, $cacheDirty);
    if (!$coords) {
        continue; // couldn't resolve this pet's location — leave it out rather than guess, matching Task 3's client-side behavior
    }

    $distance = pc_haversine_miles($lat, $lng, $coords['lat'], $coords['lng']);
    if ($distance <= $radius) {
        $matches[] = ['id' => isset($pet['id']) ? $pet['id'] : $petId, 'distance' => $distance];
    }
}

if ($cacheDirty) {
    // LOCK_EX avoids two concurrent requests corrupting the shared cache file
    file_put_contents($cacheFile, json_encode($cache), LOCK_EX);
}

usort($matches, function ($a, $b) {
    return $a['distance'] <=> $b['distance'];
});

echo json_encode([
    'ids' => array_map(function ($m) { return $m['id']; }, $matches),
]);

```

### JS changes to route the radius search through this endpoint

### `cats.html`, `dogs.html`, `birds.html`, `reptiles.html`, `aquariums.html`, `livestocks.html`, `small-mammals.html`
Applied on top of each file's already-submitted Task 3b code. All 4 changes below were applied verbatim to all 7 category pages — verified present with an exact string-count check (1 match per file) after patching.
**JS — Apply button now calls the new server endpoint (`pcRunServerRadiusSearch`) instead of geocoding every pet's location in the browser**

*Old code:*
```javascript
    // TASK-3b — "Use my location" already wrote real coordinates directly (no
    // geocoding involved), so Apply must not try to geocode the placeholder
    // "Current location" text over the top of them.
    var pcAlreadyHaveCoords = nearMeText === 'Current location' &&
      document.getElementById('filterNearMeLat')?.value &&
      document.getElementById('filterNearMeLng')?.value;

    if (pcAlreadyHaveCoords) {
      if (window.pcShowSearchLoading) window.pcShowSearchLoading();
      pcGeocodeAllPetLocations().then(function() {
        if (statusEl) statusEl.textContent = '';
        if (window.pcApplyAndRender) window.pcApplyAndRender();
      });
    } else if (radiusVal && nearMeText) {
      if (statusEl) statusEl.textContent = 'Finding pets near "' + nearMeText + '"…';
      if (window.pcShowSearchLoading) window.pcShowSearchLoading();
      pcGeocodeLocation(nearMeText, countryVal).then(function(coords) {
        if (!coords) {
          if (statusEl) statusEl.textContent = 'Couldn\'t find that location — showing all pets instead.';
          document.getElementById('filterRadius').value = '';
          if (window.pcApplyAndRender) window.pcApplyAndRender();
          return;
        }
        document.getElementById('filterNearMeLat').value = coords.lat;
        document.getElementById('filterNearMeLng').value = coords.lng;
        return pcGeocodeAllPetLocations().then(function() {
          if (statusEl) statusEl.textContent = '';
          if (window.pcApplyAndRender) window.pcApplyAndRender();
        });
      });
    } else {
```

*New code:*
```javascript
    // TASK-3c — "Use my location" already wrote real coordinates directly (no
    // geocoding involved), so Apply must not try to geocode the placeholder
    // "Current location" text over the top of them.
    var pcAlreadyHaveCoords = nearMeText === 'Current location' &&
      document.getElementById('filterNearMeLat')?.value &&
      document.getElementById('filterNearMeLng')?.value;

    if (pcAlreadyHaveCoords) {
      if (window.pcShowSearchLoading) window.pcShowSearchLoading();
      pcRunServerRadiusSearch(
        document.getElementById('filterNearMeLat').value,
        document.getElementById('filterNearMeLng').value,
        radiusVal
      ).then(function() {
        if (statusEl) statusEl.textContent = '';
        if (window.pcApplyAndRender) window.pcApplyAndRender();
      });
    } else if (radiusVal && nearMeText) {
      if (statusEl) statusEl.textContent = 'Finding pets near "' + nearMeText + '"…';
      if (window.pcShowSearchLoading) window.pcShowSearchLoading();
      pcGeocodeLocation(nearMeText, countryVal).then(function(coords) {
        if (!coords) {
          if (statusEl) statusEl.textContent = 'Couldn\'t find that location — showing all pets instead.';
          document.getElementById('filterRadius').value = '';
          if (window.pcApplyAndRender) window.pcApplyAndRender();
          return;
        }
        document.getElementById('filterNearMeLat').value = coords.lat;
        document.getElementById('filterNearMeLng').value = coords.lng;
        return pcRunServerRadiusSearch(coords.lat, coords.lng, radiusVal).then(function() {
          if (statusEl) statusEl.textContent = '';
          if (window.pcApplyAndRender) window.pcApplyAndRender();
        });
      });
    } else {
```

**JS — removed the client-side `pcHaversineMiles` and `pcGeocodeAllPetLocations` (superseded), added `pcRunServerRadiusSearch()` / `pcRadiusMatchIds` / `pcClearServerRadiusSearch()`**

*Old code:*
```javascript
// Great-circle (Haversine) distance in miles between two lat/lng points
function pcHaversineMiles(lat1, lng1, lat2, lng2) {
  var R = 3958.8;
  var dLat = (lat2 - lat1) * Math.PI / 180;
  var dLng = (lng2 - lng1) * Math.PI / 180;
  var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
          Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
          Math.sin(dLng / 2) * Math.sin(dLng / 2);
  return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

// pet.json stores location as free text, e.g. "Los Angeles, CA"
function pcParseLocation(loc) {
  if (!loc) return { city: '', state: '' };
  var parts = String(loc).split(',');
  return { city: (parts[0] || '').trim(), state: (parts[1] || '').trim() };
}

// Sequentially geocodes every distinct pet location not already cached.
// Only called right before a radius search actually needs to compare
// distances, so ordinary browsing never triggers any network calls.
function pcGeocodeAllPetLocations() {
  var pets = window.pcGetAllPets ? window.pcGetAllPets() : [];
  var distinct = [];
  pets.forEach(function(p) {
    var loc = (p.location || '').trim();
    if (loc && distinct.indexOf(loc) === -1) distinct.push(loc);
  });
  return distinct.reduce(function(chain, loc) {
    return chain.then(function() { return pcGeocodeLocation(loc, 'us'); }); // TASK-3b — Pet Center's pets are all US-located (confirmed by pet.json), so pet-side geocoding stays US regardless of which country the visitor searches
  }, Promise.resolve());
}
// TASK-3 END

// Pet Loading and Filtering
(function() {
```

*New code:*
```javascript
// TASK-3c START — Server-side Haversine: the Great-circle distance math and
// the per-pet geocoding both moved to geo-filter.php (persisted cache, one
// copy shared by every visitor, instead of Task 3's in-memory client cache).
// pcHaversineMiles was removed from here since nothing client-side computes
// distance anymore — see geo-filter.php for the same formula, server-side.
// TASK-3c END

// pet.json stores location as free text, e.g. "Los Angeles, CA"
function pcParseLocation(loc) {
  if (!loc) return { city: '', state: '' };
  var parts = String(loc).split(',');
  return { city: (parts[0] || '').trim(), state: (parts[1] || '').trim() };
}

// TASK-3c START — Calls geo-filter.php with the visitor's resolved
// coordinates + chosen radius; the server geocodes every pet in this
// category (using its own persisted cache) and returns just the IDs within
// range, nearest-first. Replaces Task 3b's pcGeocodeAllPetLocations(), which
// did this same work in the browser with a cache that reset every page load.
var pcRadiusMatchIds = null; // null = no server radius search active; array = active, filter to these IDs only
function pcRunServerRadiusSearch(lat, lng, radiusMiles) {
  var catalog = document.querySelector('[data-category]');
  var category = (catalog && catalog.getAttribute('data-category')) || 'cats';
  var url = '/adoptions/pets/geo-filter.php?category=' + encodeURIComponent(category) +
    '&lat=' + encodeURIComponent(lat) + '&lng=' + encodeURIComponent(lng) +
    '&radius=' + encodeURIComponent(radiusMiles);
  return fetch(url)
    .then(function(r) { return r.ok ? r.json() : { ids: [] }; })
    .then(function(data) { pcRadiusMatchIds = Array.isArray(data.ids) ? data.ids : []; })
    .catch(function() { pcRadiusMatchIds = []; });
}
function pcClearServerRadiusSearch() { pcRadiusMatchIds = null; }
window.pcRunServerRadiusSearch = pcRunServerRadiusSearch;
window.pcClearServerRadiusSearch = pcClearServerRadiusSearch;
// TASK-3c END
// TASK-3 END

// Pet Loading and Filtering
(function() {
```

**JS — `applyAndRender()`'s radius check now matches against the ID list `geo-filter.php` already computed, instead of doing Haversine client-side**

*Old code:*
```javascript
    var pcRadius = document.getElementById('filterRadius')?.value || '';
    var pcNearLat = parseFloat(document.getElementById('filterNearMeLat')?.value || '');
    var pcNearLng = parseFloat(document.getElementById('filterNearMeLng')?.value || '');
    var pcRadiusActive = pcRadius && !isNaN(pcNearLat) && !isNaN(pcNearLng);
    // TASK-3 END

    var filtered = allPets.filter(function(p) {
      if (breed && p.breed !== breed) return false;
      if (size && p.size !== size) return false;
      // TASK-3 START — Location filter: radius search (when a "near me" location
      // has been resolved) takes priority over the plain State/City chips.
      if (pcRadiusActive) {
        var petCoords = pcGeocodeCache[(p.location || '').trim().toLowerCase()];
        if (!petCoords) return false; // couldn't resolve this pet's location — leave it out rather than guess
        if (pcHaversineMiles(pcNearLat, pcNearLng, petCoords.lat, petCoords.lng) > parseFloat(pcRadius)) return false;
      } else if (pcState || pcCity) {
```

*New code:*
```javascript
    var pcRadius = document.getElementById('filterRadius')?.value || '';
    // TASK-3c — pcRadiusActive now reflects whether the server-side radius
    // search actually returned a result set (pcRadiusMatchIds is an array),
    // not whether coordinates merely exist — replaces Task 3's client-Haversine check.
    var pcRadiusActive = !!(pcRadius && Array.isArray(pcRadiusMatchIds));
    // TASK-3 END

    var filtered = allPets.filter(function(p) {
      if (breed && p.breed !== breed) return false;
      if (size && p.size !== size) return false;
      // TASK-3 START — Location filter: radius search (when a "near me" location
      // has been resolved) takes priority over the plain State/City chips.
      if (pcRadiusActive) {
        // TASK-3c — matching against the ID list geo-filter.php already computed server-side
        var petIdentifier = p.id || p.pet_id;
        if (pcRadiusMatchIds.indexOf(petIdentifier) === -1) return false;
      } else if (pcState || pcCity) {
```

**JS — Clear button also resets the server radius search result**

*Old code:*
```javascript
    pcUpdatePostalHint('us');
    // TASK-3b END
    // TASK-3 END
```

*New code:*
```javascript
    pcUpdatePostalHint('us');
    // TASK-3b END
    // TASK-3c START — clear the server radius search result so Clear actually clears it
    if (window.pcClearServerRadiusSearch) window.pcClearServerRadiusSearch();
    // TASK-3c END
    // TASK-3 END
```



### What actually moved server-side
- **Haversine distance calculation** — `pc_haversine_miles()` in `geo-filter.php`, same formula as the retired client-side `pcHaversineMiles`.
- **Pet-location geocoding** — now done server-side in `geo-filter.php`, cached to a shared disk file (`geo-cache.json`, sibling to `geo-filter.php`) instead of Task 3b's in-memory, per-page-load-only client cache. This also satisfies the "caching resolved lookups" enhancement more completely than the original in-memory version did, since the cache now persists across visitors and page loads, not just within one.
- **What stayed client-side, on purpose**: the visitor's own typed "near me" text is still geocoded in the browser (unchanged from Task 3b) — it's a one-off call per search, not something that repeats and benefits from server-side caching the way pet locations do.

### Updated Bugs Found Outside Task Scope / Scope Decisions
The original scope decision above (not building server-side Haversine) is superseded by this update. The remaining scope note from Task 3's own completion file still stands and was not touched here: `publish-pet.php` still does not write a `location` field to `pet.json`.

### Updated Self-Check Confirmation
Confirmed: this update touched the 7 category pages listed in this task's original Files Changed table (JS only, patched on top of what was already there) plus one new file, `geo-filter.php`. `pets.html`, `publish-pet.php`, and `pet.json` were read for reference (to confirm the real `index.json` → `pet.json` fetch structure and the `location`/`id` field names) but not modified. Every JS block changed is wrapped in `TASK-3c START` / `TASK-3c END` anchors (kept distinct from `TASK-3` and `TASK-3b`'s own anchors), and `geo-filter.php` carries an explanatory doc-comment block at its top plus inline comments on its non-obvious logic (the persistent cache, the country restriction, the nearest-first sort) — verified against the real files before this update was written, not just visually.


---

## SECOND VERIFICATION ROUND

Victor asked for a full re-check of this work against both project docs and the task text. Changes were made, so this section documents them in the same format as above. The Task 3 side of the same round (visible notice, Nearest First sort, location-text normalisation, empty-state hint) is documented in the same-named section of `Task-3-Completion.md`.

### What the re-check found
1. **Rule violation: an emoji.** The "Use my location" button was a pin emoji. This project's standing rule is no emojis anywhere, inline SVG icons only.
2. **Fragile "Use my location" detection.** Apply decided whether the visitor had used geolocation by comparing the input's text to the literal string `'Current location'`. A visitor typing those words, or any future wording change, would break it.
3. **A stale search could be reused.** Apply never cleared the previous result list, so changing only the radius with the location field emptied silently re-used the old list while claiming the new radius. Two quick Applies could also let a slow earlier request overwrite a newer one.
4. **A broken endpoint looked like "no pets nearby".** A 404/500 from `geo-filter.php` was treated as an empty result.
5. **A visitor's exact coordinates went into the request URL**, which ends up in server access logs.
6. **Every lone search was delayed a full 1.1 s** by the rate-limit timer, even when no other request had been made.
7. **My "confirmed" claim about non-US postal formats was weaker than I wrote.** The distance maths is format-independent (true), but whether OpenStreetMap can find a Canadian, UK or Australian *postcode* is a separate question. OpenStreetMap's own review of postcode coverage shows it is uneven by country. I could not run a live lookup from here. The input placeholder and hint now offer a city or town name first for every country, and the failure message says postal codes are not recognised everywhere. **This needs a real test on the live site** with a few real Canadian, UK and Australian postcodes before anyone relies on postcode search.
8. **`geo-filter.php` had real defects**, all fixed below: it cached every lookup failure as "no coordinates" **permanently** (one network blip would hide a pet from distance searches forever); a cold cache could run past PHP's 30 s time limit (each live lookup costs over a second); concurrent visitors could each call Nominatim at the same time, breaking its one-request-per-second policy site-wide; the pet ID from `index.json` was used in a file path without any check; coordinates and radius were not range-checked; and the User-Agent contained a placeholder email instead of identifying the real site.

### Files Changed

| File | Shared with another task? |
|---|---|
| `cats.html`, `dogs.html`, `birds.html`, `reptiles.html`, `aquariums.html`, `livestocks.html`, `small-mammals.html` | Yes: Task 5 (`CTA` zone), Task 2 (`NAV-LINKS` zone), Task 3 (`FILTER` zone) |
| `geo-filter.php` | No. Created in the earlier round of this task, so this change is documented as a real modification (old code/new code), not as a new file. |

`pets.html` was not touched. All blocks carry `TASK-3d START` / `TASK-3d END` anchors in HTML/CSS/JS; the PHP changes are marked with `TASK-3d` comments.

### Code Changes

#### The 7 category pages (`cats.html`, `dogs.html`, `birds.html`, `reptiles.html`, `aquariums.html`, `livestocks.html`, `small-mammals.html`)
Applied verbatim to all 7, verified with an exact one-match string check per file.

**HTML: emoji replaced by an inline SVG icon**

*Why:* Finding 1.

*Old code:*
```html
<button type="button" class="pc-nearme-geo-btn" id="pcUseMyLocationBtn" title="Use my current location" aria-label="Use my current location">📍</button>
```

*New code:*
```html
<button type="button" class="pc-nearme-geo-btn" id="pcUseMyLocationBtn" title="Use my current location" aria-label="Use my current location"><!-- TASK-3d — project rule: no emojis, inline SVG icons only (this was an emoji) --><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><circle cx="12" cy="12" r="8"/><line x1="12" y1="1" x2="12" y2="4"/><line x1="12" y1="20" x2="12" y2="23"/><line x1="1" y1="12" x2="4" y2="12"/><line x1="20" y1="12" x2="23" y2="12"/></svg></button>
```

**CSS: centre the SVG inside the button**

*Why:* Finding 1.

*Old code:*
```css
.pc-nearme-geo-btn {
  flex: 0 0 auto; width: 42px; background: #1c2c56; border: 1px solid rgba(255,255,255,.15);
  border-radius: 10px; font-size: 16px; cursor: pointer; color: #fff;
}
```

*New code:*
```css
.pc-nearme-geo-btn {
  flex: 0 0 auto; width: 42px; background: #1c2c56; border: 1px solid rgba(255,255,255,.15);
  border-radius: 10px; font-size: 16px; cursor: pointer; color: #fff;
  display: flex; align-items: center; justify-content: center; /* TASK-3d — centres the SVG icon that replaced the emoji */
}
```

**JS: geolocation success handler sets an explicit flag**

*Why:* Finding 2. The field text is now only a display label.

*Old code:*
```javascript
        if (nearMeInputEl) nearMeInputEl.value = 'Current location';

```

*New code:*
```javascript
        if (nearMeInputEl) nearMeInputEl.value = 'Current location'; // TASK-3d — display label only; the flag below is what Apply checks
        window.pcUsingGeolocation = true;

```

**JS: typing in the location field drops the geolocation flag and its stored coordinates**

*Why:* Finding 2.

*Old code:*
```javascript
  var pcUseMyLocationBtn = document.getElementById('pcUseMyLocationBtn');
  if (pcUseMyLocationBtn) {
```

*New code:*
```javascript
  // TASK-3d START — Apply used to detect "Use my location" by comparing the input's text to
  // the literal string 'Current location', so a visitor typing those words (or any future
  // wording change) would break it. An explicit flag replaces that: it is set by the
  // geolocation success handler and dropped the moment the visitor types in the field.
  window.pcUsingGeolocation = false;
  var pcNearMeInputForFlag = document.getElementById('pcNearMeInput');
  if (pcNearMeInputForFlag) {
    pcNearMeInputForFlag.addEventListener('input', function() {
      window.pcUsingGeolocation = false;
      document.getElementById('filterNearMeLat').value = '';
      document.getElementById('filterNearMeLng').value = '';
    });
  }
  // TASK-3d END
  var pcUseMyLocationBtn = document.getElementById('pcUseMyLocationBtn');
  if (pcUseMyLocationBtn) {
```

**JS: sheet Clear also drops the geolocation flag**

*Why:* Finding 2.

*Old code:*
```javascript
    if (window.pcClearServerRadiusSearch) window.pcClearServerRadiusSearch();
    // TASK-3c END
```

*New code:*
```javascript
    if (window.pcClearServerRadiusSearch) window.pcClearServerRadiusSearch();
    // TASK-3c END
    window.pcUsingGeolocation = false; // TASK-3d
```

**JS: server search helper rewritten (newest-search-wins token, real failure result, 2-decimal coordinates, visible-notice helpers)**

*Why:* Findings 3, 4 and 5. Also adds `pcSetLocationNotice()` and `pcClearDistanceFilter()`, which the notice from the Task 3 section uses.

*Old code:*
```javascript
// TASK-3c START — Calls geo-filter.php with the visitor's resolved
// coordinates + chosen radius; the server geocodes every pet in this
// category (using its own persisted cache) and returns just the IDs within
// range, nearest-first. Replaces Task 3b's pcGeocodeAllPetLocations(), which
// did this same work in the browser with a cache that reset every page load.
var pcRadiusMatchIds = null; // null = no server radius search active; array = active, filter to these IDs only
function pcRunServerRadiusSearch(lat, lng, radiusMiles) {
  var catalog = document.querySelector('[data-category]');
  var category = (catalog && catalog.getAttribute('data-category')) || 'cats';
  var url = '/adoptions/pets/geo-filter.php?category=' + encodeURIComponent(category) +
    '&lat=' + encodeURIComponent(lat) + '&lng=' + encodeURIComponent(lng) +
    '&radius=' + encodeURIComponent(radiusMiles);
  return fetch(url)
    .then(function(r) { return r.ok ? r.json() : { ids: [] }; })
    .then(function(data) { pcRadiusMatchIds = Array.isArray(data.ids) ? data.ids : []; })
    .catch(function() { pcRadiusMatchIds = []; });
}
function pcClearServerRadiusSearch() { pcRadiusMatchIds = null; }
window.pcRunServerRadiusSearch = pcRunServerRadiusSearch;
window.pcClearServerRadiusSearch = pcClearServerRadiusSearch;
// TASK-3c END
```

*New code:*
```javascript
// TASK-3c START — Calls geo-filter.php with the visitor's resolved
// coordinates + chosen radius; the server geocodes every pet in this
// category (using its own persisted cache) and returns just the IDs within
// range, nearest-first. Replaces Task 3b's pcGeocodeAllPetLocations(), which
// did this same work in the browser with a cache that reset every page load.
var pcRadiusMatchIds = null; // null = no server radius search active; array = active, filter to these IDs only
// TASK-3d START — second verification round hardening of the search helper:
//  - newest-search-wins token, so a slow earlier request can never overwrite a later one
//  - real failure handling: a missing/erroring endpoint no longer looks like "zero pets nearby"
//  - coordinates rounded to 2 decimals (~0.7 mi) before they go into the URL, because query
//    strings end up in server access logs and a visitor's exact position doesn't belong there;
//    the smallest radius offered is 25 mi, so nothing is lost
var pcRadiusSearchSeq = 0;
function pcRunServerRadiusSearch(lat, lng, radiusMiles) {
  var mySeq = ++pcRadiusSearchSeq;
  var catalog = document.querySelector('[data-category]');
  var category = (catalog && catalog.getAttribute('data-category')) || 'cats';
  var url = '/adoptions/pets/geo-filter.php?category=' + encodeURIComponent(category) +
    '&lat=' + encodeURIComponent(Number(lat).toFixed(2)) + '&lng=' + encodeURIComponent(Number(lng).toFixed(2)) +
    '&radius=' + encodeURIComponent(radiusMiles);
  return fetch(url)
    .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(function(data) {
      if (mySeq !== pcRadiusSearchSeq) return { ok: false, stale: true };
      if (!data || !Array.isArray(data.ids)) throw new Error('Unexpected response');
      pcRadiusMatchIds = data.ids;
      return { ok: true, pending: Number(data.pending) || 0 };
    })
    .catch(function() {
      if (mySeq === pcRadiusSearchSeq) pcRadiusMatchIds = null;
      return { ok: false, stale: mySeq !== pcRadiusSearchSeq };
    });
}
function pcClearServerRadiusSearch() { pcRadiusSearchSeq++; pcRadiusMatchIds = null; }

// Visible notice above the grid (the sheet is already closed when results arrive)
function pcSetLocationNotice(msg, withClear) {
  var el = document.getElementById('pcLocationNotice');
  if (!el) return;
  el.textContent = '';
  if (!msg) { el.hidden = true; return; }
  var span = document.createElement('span');
  span.textContent = msg;
  el.appendChild(span);
  if (withClear) {
    var b = document.createElement('button');
    b.type = 'button';
    b.className = 'pc-location-notice__clear';
    b.textContent = 'Clear';
    b.addEventListener('click', pcClearDistanceFilter);
    el.appendChild(b);
  }
  el.hidden = false;
}
// Clears only the distance search (not the other filters) and re-renders
function pcClearDistanceFilter() {
  pcClearServerRadiusSearch();
  var r = document.getElementById('filterRadius'); if (r) r.value = '';
  var la = document.getElementById('filterNearMeLat'); if (la) la.value = '';
  var ln = document.getElementById('filterNearMeLng'); if (ln) ln.value = '';
  var inp = document.getElementById('pcNearMeInput'); if (inp) inp.value = '';
  window.pcUsingGeolocation = false;
  pcSetLocationNotice('');
  if (window.pcApplyAndRender) window.pcApplyAndRender();
}
// TASK-3d END
window.pcRunServerRadiusSearch = pcRunServerRadiusSearch;
window.pcClearServerRadiusSearch = pcClearServerRadiusSearch;
// TASK-3c END
```

**JS: Apply handler distance branch rewritten**

*Why:* Findings 2, 3 and 4, and the invisible-message problem from the Task 3 section. Every Apply now starts with no active distance search; results and errors go to the visible notice; geolocation is detected by flag.

*Old code:*
```javascript
    // TASK-3 START — A radius value with no resolved "near me" text has nothing
    // to measure distance from, so it's geocoded here on Apply (not as-you-type)
    // to respect Nominatim's rate limit. State/City chips apply immediately since
    // they're plain string matches needing no network round-trip.
    var radiusVal = document.getElementById('filterRadius')?.value || '';
    var nearMeInput = document.getElementById('pcNearMeInput');
    var nearMeText = nearMeInput ? nearMeInput.value.trim() : '';
    var statusEl = document.getElementById('pcNearMeStatus');
    var countryVal = document.getElementById('filterCountry')?.value || 'us'; // TASK-3b

    closeSheet();

    // TASK-3c — "Use my location" already wrote real coordinates directly (no
    // geocoding involved), so Apply must not try to geocode the placeholder
    // "Current location" text over the top of them.
    var pcAlreadyHaveCoords = nearMeText === 'Current location' &&
      document.getElementById('filterNearMeLat')?.value &&
      document.getElementById('filterNearMeLng')?.value;

    if (pcAlreadyHaveCoords) {
      if (window.pcShowSearchLoading) window.pcShowSearchLoading();
      pcRunServerRadiusSearch(
        document.getElementById('filterNearMeLat').value,
        document.getElementById('filterNearMeLng').value,
        radiusVal
      ).then(function() {
        if (statusEl) statusEl.textContent = '';
        if (window.pcApplyAndRender) window.pcApplyAndRender();
      });
    } else if (radiusVal && nearMeText) {
      if (statusEl) statusEl.textContent = 'Finding pets near "' + nearMeText + '"…';
      if (window.pcShowSearchLoading) window.pcShowSearchLoading();
      pcGeocodeLocation(nearMeText, countryVal).then(function(coords) {
        if (!coords) {
          if (statusEl) statusEl.textContent = 'Couldn\'t find that location — showing all pets instead.';
          document.getElementById('filterRadius').value = '';
          if (window.pcApplyAndRender) window.pcApplyAndRender();
          return;
        }
        document.getElementById('filterNearMeLat').value = coords.lat;
        document.getElementById('filterNearMeLng').value = coords.lng;
        return pcRunServerRadiusSearch(coords.lat, coords.lng, radiusVal).then(function() {
          if (statusEl) statusEl.textContent = '';
          if (window.pcApplyAndRender) window.pcApplyAndRender();
        });
      });
    } else {
      if (radiusVal && !nearMeText && statusEl) {
        statusEl.textContent = 'Enter a city, state or ZIP to search by distance.';
      }
      if (window.pcApplyAndRender) window.pcApplyAndRender();
    }
    // TASK-3 END
  });
```

*New code:*
```javascript
    // TASK-3 START — A radius value with no resolved "near me" text has nothing
    // to measure distance from, so it's geocoded here on Apply (not as-you-type)
    // to respect Nominatim's rate limit. State/City chips apply immediately since
    // they're plain string matches needing no network round-trip.
    var radiusVal = document.getElementById('filterRadius')?.value || '';
    var nearMeInput = document.getElementById('pcNearMeInput');
    var nearMeText = nearMeInput ? nearMeInput.value.trim() : '';
    var countryVal = document.getElementById('filterCountry')?.value || 'us'; // TASK-3b

    // TASK-3d START — second verification round rewrite of the distance branch of Apply:
    //  1. Every Apply starts with NO active distance search. Before, an ID list from an
    //     earlier search survived, so changing only the radius (with the location field
    //     emptied) silently re-used the old list while claiming the new radius.
    //  2. Results/errors are reported in the visible notice above the grid. They used to be
    //     written to the in-sheet status line AFTER closeSheet(), i.e. never seen.
    //  3. "Use my location" is detected with window.pcUsingGeolocation, not by comparing the
    //     field's text to the literal 'Current location'.
    //  4. A failed or missing endpoint is reported instead of showing "no pets match".
    pcClearServerRadiusSearch();
    pcSetLocationNotice('');
    closeSheet();

    var latNow = document.getElementById('filterNearMeLat')?.value;
    var lngNow = document.getElementById('filterNearMeLng')?.value;
    var usingGeo = window.pcUsingGeolocation === true && !!latNow && !!lngNow;
    var render = function() { if (window.pcApplyAndRender) window.pcApplyAndRender(); };

    if (radiusVal && (usingGeo || nearMeText)) {
      if (window.pcShowSearchLoading) window.pcShowSearchLoading();
      var coordsPromise = usingGeo
        ? Promise.resolve({ lat: latNow, lng: lngNow })
        : pcGeocodeLocation(nearMeText, countryVal);
      coordsPromise.then(function(coords) {
        if (!coords) {
          pcSetLocationNotice("Couldn't find that location. Try a city and state or province; postal codes aren't recognised everywhere. Showing all pets.");
          document.getElementById('filterRadius').value = '';
          render();
          return;
        }
        document.getElementById('filterNearMeLat').value = coords.lat;
        document.getElementById('filterNearMeLng').value = coords.lng;
        return pcRunServerRadiusSearch(coords.lat, coords.lng, radiusVal).then(function(result) {
          if (result.stale) return; // a newer search owns the screen now
          if (!result.ok) {
            pcSetLocationNotice('Distance search is unavailable right now. Showing all pets.');
            document.getElementById('filterRadius').value = '';
          } else {
            var where = usingGeo ? 'your location' : nearMeText;
            var note = 'Showing pets within ' + radiusVal + ' miles of ' + where + '.';
            if (result.pending > 0) note += ' Some pet locations are still being looked up, so this list may be incomplete. Try again in a moment.';
            pcSetLocationNotice(note, true);
          }
          render();
        });
      });
    } else {
      if (radiusVal) {
        pcSetLocationNotice('Enter a city or postal code (or tap the location button) to search by distance.');
        document.getElementById('filterRadius').value = '';
      }
      render();
    }
    // TASK-3d END
    // TASK-3 END
  });
```

**JS: timestamp of the last geocoder call**

*Why:* Finding 6.

*Old code:*
```javascript
var pcGeocodeQueue = Promise.resolve();
```

*New code:*
```javascript
var pcGeocodeQueue = Promise.resolve();
var pcLastGeocodeAt = 0; // TASK-3d — timestamp of the last Nominatim call, so a lone search isn't delayed a full second for nothing
```

**JS: record the call time**

*Why:* Finding 6.

*Old code:*
```javascript
      setTimeout(function() {
        // TASK-3b — countrycodes now driven
```

*New code:*
```javascript
      setTimeout(function() {
        pcLastGeocodeAt = Date.now(); // TASK-3d
        // TASK-3b — countrycodes now driven
```

**JS: wait only as long as needed to stay under 1 request/second**

*Why:* Finding 6. Calls are still never closer than about 1.1 s apart.

*Old code:*
```javascript
      }, 1100); // stay comfortably under Nominatim's 1 req/sec usage policy
```

*New code:*
```javascript
      }, Math.max(0, 1100 - (Date.now() - pcLastGeocodeAt))); // TASK-3d — still never closer than ~1.1s apart (Nominatim's 1 req/sec policy), but no wait when the last call was long ago
```

**JS: the input placeholder now follows the selected country, city-first**

*Why:* Finding 7.

*Old code:*
```javascript
  function pcUpdatePostalHint(countryCode) {
    var hintEl = document.getElementById('pcPostalHint');
    if (hintEl) hintEl.textContent = pcPostalHints[countryCode] || pcPostalHints.us;
  }
```

*New code:*
```javascript
  function pcUpdatePostalHint(countryCode) {
    var hintEl = document.getElementById('pcPostalHint');
    if (hintEl) hintEl.textContent = pcPostalHints[countryCode] || pcPostalHints.us;
    // TASK-3d — the input's own placeholder now follows the country too. A city/town name is
    // offered first everywhere because OpenStreetMap postcode coverage varies by country.
    var inputEl = document.getElementById('pcNearMeInput');
    var placeholders = {
      us: 'City, State or ZIP code',
      ca: 'City, Province or postal code',
      gb: 'Town or postcode',
      au: 'Suburb, State or postcode'
    };
    if (inputEl) inputEl.placeholder = placeholders[countryCode] || placeholders.us;
  }
```

#### `geo-filter.php`
Path assumed to be `/adoptions/pets/geo-filter.php` as before. Three edits against the first version:

**PHP: response documentation**

*Why:* Documents the new `pending` field (finding 8).

*Old code:*
```php
 * Response: { "ids": ["PET-ID-1", "PET-ID-2", ...] }  — nearest-first
```

*New code:*
```php
 * Response: { "ids": ["PET-ID-1", "PET-ID-2", ...], "pending": 0 }  — nearest-first.
 *   "pending" (TASK-3d) = how many pets' locations could not be looked up on THIS request
 *   (lookup budget used up, or the geocoder was unreachable). They are simply retried on
 *   the next request; the front-end tells the visitor the list may be incomplete.
```

**PHP: input range checks**

*Why:* Finding 8.

*Old code:*
```php
if (!in_array($category, $validCategories, true) || $lat === null || $lng === null || !$radius) {
```

*New code:*
```php
// TASK-3d — range checks: reject impossible coordinates and absurd radii instead of computing with them
if (!in_array($category, $validCategories, true) || $lat === null || $lng === null || !$radius
    || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180 || $radius <= 0 || $radius > 20000) {
```

**PHP: geocoding function and main loop rewritten**

*Why:* Finding 8. Failures are no longer cached; live lookups are capped at 8 per request with leftovers reported as `pending`; one shared lock file serialises Nominatim calls across all requests; pet IDs are validated before being used in a path; `curl` is used when available; the User-Agent names the real site; a failed cache write is logged instead of silently ignored.

*Old code:*
```php
/**
 * Geocodes a "City, State" string via OpenStreetMap Nominatim (free, no API
 * key — same service Task 3 already used client-side), using and updating
 * the shared on-disk cache passed by reference. Pet Center's pets are all
 * US-located (confirmed via pet.json), so this is always restricted to "us"
 * regardless of which country a visitor is searching from — matching Task
 * 3b's own client-side logic for pet-side geocoding.
 */
function pc_geocode_pet_location($location, &$cache, &$cacheDirty) {
    $key = strtolower(trim($location));
    if ($key === '') {
        return null;
    }
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    // Nominatim's usage policy caps requests at ~1/sec. This only runs for a
    // location not already in the shared cache file, which — once any
    // visitor has ever searched near a given pet's location — should be rare.
    usleep(1100000);

    $url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=us&q=' . urlencode($location);
    $context = stream_context_create([
        'http' => [
            'header'  => "User-Agent: PetCenter-GeoFilter/1.0 (contact: admin@petcenter.example)\r\n",
            'timeout' => 5,
        ],
    ]);
    $response = @file_get_contents($url, false, $context);
    $results  = $response ? json_decode($response, true) : null;

    $coords = (is_array($results) && count($results) > 0)
        ? ['lat' => floatval($results[0]['lat']), 'lng' => floatval($results[0]['lon'])]
        : null;

    $cache[$key] = $coords;
    $cacheDirty = true;
    return $coords;
}

$matches = [];

foreach ($petIds as $petId) {
    $petFile = $categoryDir . $petId . '/pet.json';
    if (!file_exists($petFile)) {
        continue;
    }
    $pet = json_decode(file_get_contents($petFile), true);
    if (!is_array($pet) || empty($pet['location'])) {
        continue;
    }

    $coords = pc_geocode_pet_location($pet['location'], $cache, $cacheDirty);
    if (!$coords) {
        continue; // couldn't resolve this pet's location — leave it out rather than guess, matching Task 3's client-side behavior
    }

    $distance = pc_haversine_miles($lat, $lng, $coords['lat'], $coords['lng']);
    if ($distance <= $radius) {
        $matches[] = ['id' => isset($pet['id']) ? $pet['id'] : $petId, 'distance' => $distance];
    }
}

if ($cacheDirty) {
    // LOCK_EX avoids two concurrent requests corrupting the shared cache file
    file_put_contents($cacheFile, json_encode($cache), LOCK_EX);
}

usort($matches, function ($a, $b) {
    return $a['distance'] <=> $b['distance'];
});

echo json_encode([
    'ids' => array_map(function ($m) { return $m['id']; }, $matches),
]);

```

*New code:*
```php
/**
 * TASK-3d — small HTTP GET helper. Uses cURL when the server has it, otherwise
 * file_get_contents (needs allow_url_fopen). Returns the response body, or false
 * on ANY failure (network error, timeout, non-200 status) so callers can tell
 * "the geocoder was unreachable" apart from "the geocoder found nothing".
 * The User-Agent identifies this site, as Nominatim's usage policy requires.
 */
function pc_http_get($url) {
    $userAgent = 'PetCenter-GeoFilter/1.0 (https://adopt.petcenterco.com)';
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 6,
            CURLOPT_USERAGENT      => $userAgent,
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($body !== false && $code === 200) ? $body : false;
    }
    $context = stream_context_create(['http' => ['header' => "User-Agent: $userAgent\r\n", 'timeout' => 6]]);
    $body = @file_get_contents($url, false, $context);
    return $body === false ? false : $body;
}

/**
 * Geocodes a "City, State" string via OpenStreetMap Nominatim (free, no API
 * key — same service Task 3 already used client-side), using and updating
 * the shared on-disk cache passed by reference. Pet Center's pets are all
 * US-located (confirmed via pet.json), so this is always restricted to "us"
 * regardless of which country a visitor is searching from — matching Task
 * 3b's own client-side logic for pet-side geocoding.
 *
 * TASK-3d — return value is now three-way:
 *   array(lat,lng) = found            null  = Nominatim answered and found nothing (cached, not asked again)
 *   false          = could not look it up right now (budget spent / geocoder unreachable) — NOT cached,
 *                    so a transient outage can no longer permanently hide a pet from distance searches
 *                    (the first version cached every failure as "no coordinates", forever).
 */
function pc_geocode_pet_location($location, &$cache, &$cacheDirty, &$freshLookups, $maxFreshLookups, &$lockHandle, $cacheFile) {
    $key = strtolower(trim($location));
    if ($key === '') {
        return null;
    }
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    // TASK-3d — a cold cache must not make one request run for minutes (PHP's default
    // time limit is 30s and each live lookup costs >= 1.1s), so cap live lookups per request.
    // Whatever is left over is reported as "pending" and finished by later requests.
    if ($freshLookups >= $maxFreshLookups) {
        return false;
    }

    // TASK-3d — one lock file shared by every request, so simultaneous visitors cannot
    // each fire their own Nominatim calls (the 1 req/sec policy applies to the whole site,
    // not to each request). The cache is re-read once the lock is ours, because another
    // request may have resolved this very location while we waited.
    if ($lockHandle === null) {
        $lockHandle = @fopen(__DIR__ . '/geo-cache.lock', 'c');
        if ($lockHandle) {
            flock($lockHandle, LOCK_EX);
            $latest = file_exists($cacheFile) ? json_decode(file_get_contents($cacheFile), true) : [];
            if (is_array($latest)) {
                $cache = $latest + $cache; // "+" keeps string keys intact (array_merge would renumber numeric-looking ones)
            }
            if (array_key_exists($key, $cache)) {
                return $cache[$key];
            }
        } else {
            $lockHandle = false; // cannot create the lock file: carry on unlocked rather than fail the request
        }
    }

    // Nominatim's usage policy caps requests at ~1/sec.
    usleep(1100000);
    $freshLookups++;

    $url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=us&q=' . urlencode($location);
    $response = pc_http_get($url);
    if ($response === false) {
        return false; // transient failure: do NOT cache
    }
    $results = json_decode($response, true);
    if (!is_array($results)) {
        return false; // unusable response: do NOT cache
    }

    $coords = (count($results) > 0 && isset($results[0]['lat'], $results[0]['lon']))
        ? ['lat' => floatval($results[0]['lat']), 'lng' => floatval($results[0]['lon'])]
        : null;

    $cache[$key] = $coords;
    $cacheDirty = true;
    return $coords;
}

$matches        = [];
$pending        = 0;   // TASK-3d — pets whose location could not be resolved on this request
$freshLookups   = 0;
$maxFreshLookups = 8;  // TASK-3d — ~9-10s worst case per request, safely inside PHP's default 30s limit
$lockHandle     = null;

foreach ($petIds as $petId) {
    // TASK-3d — the ID is used to build a file path, so only plain ID characters are allowed
    // (index.json is admin-written, but a path built from data should never trust it blindly)
    if (!is_string($petId) || !preg_match('/^[A-Za-z0-9_-]+$/', $petId)) {
        continue;
    }
    $petFile = $categoryDir . $petId . '/pet.json';
    if (!file_exists($petFile)) {
        continue;
    }
    $pet = json_decode(file_get_contents($petFile), true);
    if (!is_array($pet) || empty($pet['location']) || !is_string($pet['location'])) {
        continue;
    }

    $coords = pc_geocode_pet_location($pet['location'], $cache, $cacheDirty, $freshLookups, $maxFreshLookups, $lockHandle, $cacheFile);
    if ($coords === false) {
        $pending++;
        continue; // not resolvable right now — retried on a later request, and reported via "pending"
    }
    if (!$coords) {
        continue; // Nominatim has no match for this location text — leave it out rather than guess
    }

    $distance = pc_haversine_miles($lat, $lng, $coords['lat'], $coords['lng']);
    if ($distance <= $radius) {
        $matches[] = ['id' => isset($pet['id']) ? $pet['id'] : $petId, 'distance' => $distance];
    }
}

if ($cacheDirty) {
    // The lock (held since the first live lookup) makes this the only writer; LOCK_EX is belt and braces.
    if (@file_put_contents($cacheFile, json_encode($cache), LOCK_EX) === false) {
        error_log('geo-filter.php: could not write ' . $cacheFile . ' - the web server user needs write access to this folder');
    }
}
if ($lockHandle) {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
}

usort($matches, function ($a, $b) {
    return $a['distance'] <=> $b['distance'];
});

echo json_encode([
    'ids'     => array_map(function ($m) { return $m['id']; }, $matches),
    'pending' => $pending,
]);

```

### Bugs Found Outside Task Scope
- **`publish-pet.php` (admin dashboard), the `$pet_data` array, roughly lines 154-186:** it never writes a `location` field. The real `pet.json` sample Victor supplied does contain one, and the same sample also contains ten other fields this file never writes (`created_at`, `updated_at`, `updated_date`, `posted_by`, `multiple_adoptable_options`, `can_ship`, `health_insurance_30_days`, `accepts_bnpl_pay_on_delivery`, `includes_starter_kits`, `starter_kits_detail`). So either the copy of `publish-pet.php` I was given is older than what is deployed, or another script adds them. Until that is checked, newly published pets may have no `location`, and would silently be missing from State, City and distance results. Not fixed (Scope Lock).
- **Files that would be needed to wire this feature fully, which the task text did not list and I have not seen:** the current deployed `publish-pet.php`, the pet edit script (`update-pet.php` or whatever the admin uses to edit a pet), and the admin dashboard's add/edit pet form (to confirm there is a location input and that it is required and uses a "City, State" shape). Without those I cannot confirm where `location` is entered.
- **Hosting setup the endpoint depends on, which I cannot check from here:** the web server user needs write access to `/adoptions/pets/` so `geo-filter.php` can create `geo-cache.json` and `geo-cache.lock`, and PHP needs cURL or `allow_url_fopen` to reach Nominatim. If the cache cannot be written, every search re-does the live lookups (it still works, only slowly; an error is logged).
- **Product question for the CEO, not built:** every pet is currently located in the US, so a visitor in Canada, the UK or Australia searching within 25 to 500 miles will always get zero results. The page now says so with a distance-specific message, but whether to point those visitors at shipping (the Task 4 calculator) or something else is a decision for the CEO.
- The `aquariums.html` / `livestocks.html` Task 1 anchor inconsistency and the unmerged Task 5 / Task 2 zones are logged in the Task 3 section above.

### Self-Check Confirmation
Confirmed: only the 7 category pages and `geo-filter.php` were modified in this round. `pets.html`, `publish-pet.php`, `pet.json` and both project summary docs were read only. Every JS/HTML/CSS block added carries a `TASK-3d` anchor comment and an explanatory comment, copied word for word from the real files.

**How this was verified.** The 26-check Chromium test described in `Task-3-Completion.md` covers everything above on the front end (all 7 pages pass). **`geo-filter.php` was NOT executed or linted: PHP is not installed in my environment.** I checked bracket balance and read it through by hand, but it needs a real run on the server (a call with a known US location, a call with the geocoder blocked to see `pending` and confirm nothing bad is cached, and a check that `geo-cache.json` appears) before it is relied on.
