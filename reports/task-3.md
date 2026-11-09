# Task 3 — Completion Report

## Task Number
Task 3 (Project 4: Location-Based Pet Listings — named zone: `FILTER`)

## What Was Required
Add a location-based way to browse pets into the 7 category pages (`cats.html`, `dogs.html`, `birds.html`, `reptiles.html`, `aquariums.html`, `livestocks.html`, `small-mammals.html`) plus `pets.html`, researched against how Petfinder handles location/distance search, reusing the existing Bottom Sheet + Filter Button + Search Bar system rather than building a separate filter UI next to it, using only free/no-cost tools — specifically: a manual state-and-city filter, and a way to see pets near the visitor or within a chosen radius.

## Files Changed

| File | Shared with another task? |
|---|---|
| `cats.html` | Yes — Task 5 (`CTA` zone), Task 3b (extends this same `FILTER` zone next) |
| `dogs.html` | Yes — Task 5 (`CTA` zone), Task 3b |
| `birds.html` | Yes — Task 5 (`CTA` zone), Task 3b |
| `reptiles.html` | Yes — Task 5 (`CTA` zone), Task 3b |
| `aquariums.html` | Yes — Task 5 (`CTA` zone), Task 3b |
| `livestocks.html` | Yes — Task 5 (`CTA` zone), Task 3b |
| `small-mammals.html` | Yes — Task 5 (`CTA` zone), Task 3b |
| `pets.html` | Yes — Task 5 (`CTA` zone), Task 3b |

No new files were created — every change below is a modification of an existing, previously-supplied file, so the "new file" exception to code documentation does not apply anywhere in this task.

## Code Changes

### `cats.html`, `dogs.html`, `birds.html`, `reptiles.html`, `aquariums.html`, `livestocks.html`, `small-mammals.html`
All 7 category pages are byte-identical in structure (confirmed by diff before editing — they differ only in category-specific title/banner text and per-category icon SVGs, none of which this task touched). The same 10 code changes below were applied **verbatim, in the same order, to all 7 files** — each one was verified present in every file after patching (`grep`/string-count check on every anchor and every full new-code block, all returning exactly 1 match per file). They are documented once here to avoid repeating identical code seven times; the anchor comments below are copied exactly as they appear in the real files.
**CSS — Near Me input styling (added to the `<style>` block)**

*Old code:*
```css
.pc-chip.selected::before { content: "✓"; font-weight: 700; }
```

*New code:*
```css
.pc-chip.selected::before { content: "✓"; font-weight: 700; }

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

**HTML — hidden state holders for State/City/Radius/coordinates (inside the filter form, next to `filterCompatible`)**

*Old code:*
```html
      <option value="other_pets">Other Pets</option>
      <option value="untamed">Untamed</option>
    </select>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
```

*New code:*
```html
      <option value="other_pets">Other Pets</option>
      <option value="untamed">Untamed</option>
    </select>
    <!-- TASK-3 START — Location filter state holders. filterState/filterCity are
         plain string matches against pet.location's "City, State" text (populated
         dynamically in populateFilters(), same pattern as filterBreed). filterRadius
         plus the two hidden coordinate fields back the "near me" distance search:
         the coordinates are filled in by geocoding (OpenStreetMap Nominatim) only
         when a radius search is actually run — see the Apply button handler below. -->
    <select class="hidden-select" id="filterState" name="state" style="display:none;">
      <option value="">All States</option>
    </select>
    <select class="hidden-select" id="filterCity" name="city" style="display:none;">
      <option value="">All Cities</option>
    </select>
    <select class="hidden-select" id="filterRadius" name="radius" style="display:none;">
      <option value="">Any Distance</option>
      <option value="25">Within 25 miles</option>
      <option value="60">Within 60 miles</option>
      <option value="100">Within 100 miles</option>
      <option value="250">Within 250 miles</option>
      <option value="500">Within 500 miles</option>
    </select>
    <input type="hidden" id="filterNearMeLat" value="">
    <input type="hidden" id="filterNearMeLng" value="">
    <!-- TASK-3 END -->

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
```

**HTML — new "Location" filter group in the bottom sheet body (after the "Compatible With" group)**

*Old code:*
```html
            <button type="button" class="pc-chip" data-value="untamed">Untamed</button>
          </div>
        </div>
      </div>

    </div>
```

*New code:*
```html
            <button type="button" class="pc-chip" data-value="untamed">Untamed</button>
          </div>
        </div>
      </div>

      <!-- TASK-3 START — Location filter: manual State/City chips (auto-populated
           from the pets actually loaded, same pattern as Breed) plus a "Near Me"
           radius search. Radius search geocodes the typed location and every
           distinct pet location via OpenStreetMap Nominatim (free, no API key)
           and filters by great-circle (Haversine) distance — see the script block
           below. State/City and radius are mutually exclusive: entering a Near Me
           search takes priority on Apply. -->
      <div class="pc-sheet-filter-group" data-filter="filterState">
        <div class="pc-sheet-filter-group__label">State</div>
        <div class="pc-chip-scroll">
          <div class="pc-chip-row" data-target="filterState" id="pcStateChipRow">
            <button type="button" class="pc-chip selected" data-value="">All States</button>
          </div>
        </div>
      </div>

      <div class="pc-sheet-filter-group" data-filter="filterCity">
        <div class="pc-sheet-filter-group__label">City</div>
        <div class="pc-chip-scroll">
          <div class="pc-chip-row" data-target="filterCity" id="pcCityChipRow">
            <button type="button" class="pc-chip selected" data-value="">All Cities</button>
          </div>
        </div>
      </div>

      <div class="pc-sheet-filter-group" data-filter="filterNearMe">
        <div class="pc-sheet-filter-group__label">Near Me</div>
        <div class="pc-nearme-row">
          <input type="text" id="pcNearMeInput" class="pc-nearme-input" placeholder="Enter a city, state or ZIP" autocomplete="off">
        </div>
        <div class="pc-chip-scroll">
          <div class="pc-chip-row" data-target="filterRadius" id="pcRadiusChipRow">
            <button type="button" class="pc-chip selected" data-value="">Any Distance</button>
            <button type="button" class="pc-chip" data-value="25">25 mi</button>
            <button type="button" class="pc-chip" data-value="60">60 mi</button>
            <button type="button" class="pc-chip" data-value="100">100 mi</button>
            <button type="button" class="pc-chip" data-value="250">250 mi</button>
            <button type="button" class="pc-chip" data-value="500">500 mi</button>
          </div>
        </div>
        <div class="pc-nearme-status" id="pcNearMeStatus" aria-live="polite"></div>
      </div>
      <!-- TASK-3 END -->

    </div>
```

**JS — geocoding (Nominatim) + Haversine distance helpers, added immediately before the "Pet Loading and Filtering" IIFE**

*Old code:*
```javascript
// Pet Loading and Filtering
(function() {
```

*New code:*
```javascript
// TASK-3 START — Location filter helpers: geocoding + distance math.
// Free, no-API-key geocoding via OpenStreetMap Nominatim (usage policy caps
// requests at ~1/sec, so calls are chained through a single shared queue
// rather than fired in parallel). Results are cached in memory for this page
// load only — a persistent shared cache is a Task 3b enhancement, not needed
// here since a single visitor rarely repeats the same search.
var pcGeocodeCache = {};
var pcGeocodeQueue = Promise.resolve();

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
          .then(function(r) { return r.ok ? r.json() : []; })
          .then(function(results) {
            var coords = (results && results[0])
              ? { lat: parseFloat(results[0].lat), lng: parseFloat(results[0].lon) }
              : null;
            pcGeocodeCache[key] = coords;
            resolve(coords);
          })
          .catch(function() {
            pcGeocodeCache[key] = null;
            resolve(null);
          });
      }, 1100); // stay comfortably under Nominatim's 1 req/sec usage policy
    });
  });
  return pcGeocodeQueue;
}

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
    return chain.then(function() { return pcGeocodeLocation(loc); });
  }, Promise.resolve());
}
// TASK-3 END

// Pet Loading and Filtering
(function() {
```

**JS — auto-populate State/City hidden `<select>`s and chip rows inside `populateFilters()`**

*Old code:*
```javascript
      if (window.pcLayoutAllChipRows) window.pcLayoutAllChipRows();
    }
  }

  var searchTerm = '';
```

*New code:*
```javascript
      if (window.pcLayoutAllChipRows) window.pcLayoutAllChipRows();
    }

    // TASK-3 START — Location filter: dynamically extract distinct states/cities
    // from pet.location ("City, State"), same dedupe-and-sort pattern as Breed above.
    var states = [], cities = [];
    allPets.forEach(function(p) {
      var loc = pcParseLocation(p.location);
      if (loc.state && states.indexOf(loc.state) === -1) states.push(loc.state);
      if (loc.city && cities.indexOf(loc.city) === -1) cities.push(loc.city);
    });
    states.sort(function(a, b) { return a.localeCompare(b); });
    cities.sort(function(a, b) { return a.localeCompare(b); });

    function pcSyncSelectOptions(select, values) {
      if (!select) return;
      select.querySelectorAll('option:not([value=""])').forEach(function(o) { o.remove(); });
      values.forEach(function(v) {
        var o = document.createElement('option');
        o.value = v; o.textContent = v;
        select.appendChild(o);
      });
    }
    function pcSyncChipRow(rowId, values) {
      var row = document.getElementById(rowId);
      if (!row) return;
      row.querySelectorAll('.pc-chip:not([data-value=""])').forEach(function(c) { c.remove(); });
      values.forEach(function(v) {
        var chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'pc-chip';
        chip.setAttribute('data-value', v);
        chip.textContent = v;
        row.appendChild(chip);
      });
    }
    pcSyncSelectOptions(document.getElementById('filterState'), states);
    pcSyncSelectOptions(document.getElementById('filterCity'), cities);
    pcSyncChipRow('pcStateChipRow', states);
    pcSyncChipRow('pcCityChipRow', cities);
    if (window.pcLayoutAllChipRows) window.pcLayoutAllChipRows();
    // TASK-3 END
  }

  var searchTerm = '';
```

**JS — State/City/radius matching added to the `applyAndRender()` filter pipeline**

*Old code:*
```javascript
    var compatible = document.getElementById('filterCompatible')?.value || '';
    var normSearch = window.pcNormalizeSearch ? window.pcNormalizeSearch(searchTerm) : searchTerm.toLowerCase();

    var filtered = allPets.filter(function(p) {
      if (breed && p.breed !== breed) return false;
      if (size && p.size !== size) return false;
```

*New code:*
```javascript
    var compatible = document.getElementById('filterCompatible')?.value || '';
    var normSearch = window.pcNormalizeSearch ? window.pcNormalizeSearch(searchTerm) : searchTerm.toLowerCase();
    // TASK-3 START — Location filter state
    var pcState  = document.getElementById('filterState')?.value || '';
    var pcCity   = document.getElementById('filterCity')?.value || '';
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
        var petLoc = pcParseLocation(p.location);
        if (pcState && petLoc.state !== pcState) return false;
        if (pcCity && petLoc.city !== pcCity) return false;
      }
      // TASK-3 END
```

**JS — expose `window.pcGetAllPets()` so the geocoding pre-pass can read the loaded pets**

*Old code:*
```javascript
  window.pcApplySearch = function(term) {
    searchTerm = term || '';
    applyAndRender();
  };
```

*New code:*
```javascript
  window.pcApplySearch = function(term) {
    searchTerm = term || '';
    applyAndRender();
  };
  // TASK-3 START
  window.pcGetAllPets = function() { return allPets; };
  // TASK-3 END
```

**JS — Apply button handler now geocodes a Near Me search (when radius + text are both set) before rendering**

*Old code:*
```javascript
  applyBtn.addEventListener('click', function() {
    Object.keys(draft).forEach(function(id) {
      var el = document.getElementById(id);
      if (el) el.value = draft[id];
    });
    if (window.pcApplyAndRender) window.pcApplyAndRender();
    closeSheet();
  });
```

*New code:*
```javascript
  applyBtn.addEventListener('click', function() {
    Object.keys(draft).forEach(function(id) {
      var el = document.getElementById(id);
      if (el) el.value = draft[id];
    });

    // TASK-3 START — A radius value with no resolved "near me" text has nothing
    // to measure distance from, so it's geocoded here on Apply (not as-you-type)
    // to respect Nominatim's rate limit. State/City chips apply immediately since
    // they're plain string matches needing no network round-trip.
    var radiusVal = document.getElementById('filterRadius')?.value || '';
    var nearMeInput = document.getElementById('pcNearMeInput');
    var nearMeText = nearMeInput ? nearMeInput.value.trim() : '';
    var statusEl = document.getElementById('pcNearMeStatus');

    closeSheet();

    if (radiusVal && nearMeText) {
      if (statusEl) statusEl.textContent = 'Finding pets near "' + nearMeText + '"…';
      if (window.pcShowSearchLoading) window.pcShowSearchLoading();
      pcGeocodeLocation(nearMeText).then(function(coords) {
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
      if (radiusVal && !nearMeText && statusEl) {
        statusEl.textContent = 'Enter a city, state or ZIP to search by distance.';
      }
      if (window.pcApplyAndRender) window.pcApplyAndRender();
    }
    // TASK-3 END
  });
```

**JS — Clear button handler also resets the Near Me text input and resolved coordinates**

*Old code:*
```javascript
    renderChipSelection();
    // Sheet stays open (spec requirement)
  });
```

*New code:*
```javascript
    renderChipSelection();
    // TASK-3 START — Near Me text/coordinates aren't chip-driven, so Clear
    // needs to reset them explicitly alongside the chip rows above.
    var nearMeInput = document.getElementById('pcNearMeInput');
    if (nearMeInput) nearMeInput.value = '';
    var statusEl = document.getElementById('pcNearMeStatus');
    if (statusEl) statusEl.textContent = '';
    document.getElementById('filterNearMeLat').value = '';
    document.getElementById('filterNearMeLng').value = '';
    // TASK-3 END
    // Sheet stays open (spec requirement)
  });
```

**JS — read `?state=` from the URL on page load and pre-select the matching State chip (the receiving end of pets.html's location bar)**

*Old code:*
```javascript
        populateFilters();
        applyAndRender();
        updatePetCount();
        return allPets;
```

*New code:*
```javascript
        populateFilters();
        // TASK-3 START — Pick up "?state=XX" carried over from pets.html's
        // location bar and pre-select it, case-insensitively, against whatever
        // states populateFilters() just found in this category's own pets.
        var pcStateParam = new URLSearchParams(window.location.search).get('state');
        if (pcStateParam) {
          var stateSelectEl = document.getElementById('filterState');
          var match = stateSelectEl && Array.prototype.find.call(stateSelectEl.options, function(o) {
            return o.value && o.value.toLowerCase() === pcStateParam.trim().toLowerCase();
          });
          if (match) stateSelectEl.value = match.value;
        }
        // TASK-3 END
        applyAndRender();
        updatePetCount();
        return allPets;
```


### `pets.html`
This file has no per-pet listing to filter, so it received a different, smaller set of changes (a state-only handoff bar) rather than the full sheet above.
**HTML — location bar section, inserted directly above the category grid**

*Old code:*
```html
<!-- ════════════════════════════════════════════════════
     CATEGORY GRID
     ════════════════════════════════════════════════════ -->
<section class="cats-section">
  <div class="cats-grid" id="catsGrid">
```

*New code:*
```html
<!-- TASK-3 START — Location filter: pets.html has no per-pet listing to filter
     (just category cards + counts), so it can't host the full State/City/Near-Me
     sheet the 7 category pages use. Instead this compact bar carries whatever
     the visitor types here forward as a "?state=" link param on every category
     card, and each category page auto-applies it on load against its own
     (already-built) State filter — reusing the same mechanism rather than
     building a second one. -->
<section class="pets-location-bar">
  <div class="pets-location-bar__inner">
    <span class="pets-location-bar__label">Looking in a specific state?</span>
    <input type="text" id="petsLocationInput" placeholder="e.g. CA" autocomplete="off" maxlength="30">
  </div>
</section>
<!-- TASK-3 END -->

<!-- ════════════════════════════════════════════════════
     CATEGORY GRID
     ════════════════════════════════════════════════════ -->
<section class="cats-section">
  <div class="cats-grid" id="catsGrid">
```

**CSS — location bar styling**

*Old code:*
```css
.pets-breadcrumb a { color: var(--muted); text-decoration: none; }
.pets-breadcrumb a:hover { color: var(--orange); }
.pets-breadcrumb span { color: var(--warm-gray); }

/* ═══════════════════════════════════════════════════════
   CATEGORY GRID SECTION
   ═══════════════════════════════════════════════════════ */
```

*New code:*
```css
.pets-breadcrumb a { color: var(--muted); text-decoration: none; }
.pets-breadcrumb a:hover { color: var(--orange); }
.pets-breadcrumb span { color: var(--warm-gray); }

/* TASK-3 START — Location filter bar */
.pets-location-bar { max-width: 1280px; margin: 0 auto; padding: 0 24px 18px; }
.pets-location-bar__inner {
  display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
  background: #fff; border: 1px solid rgba(16,33,75,.1); border-radius: 12px;
  padding: 12px 16px; box-shadow: 0 2px 10px rgba(16,33,75,.05);
}
.pets-location-bar__label {
  font-family: 'Sora', sans-serif; font-size: 13px; font-weight: 600; color: var(--navy);
}
.pets-location-bar__inner input {
  flex: 1; min-width: 140px; border: 1px solid rgba(16,33,75,.15); border-radius: 8px;
  padding: 9px 12px; font-family: 'Sora', sans-serif; font-size: 13.5px; color: var(--navy);
  outline: none;
}
.pets-location-bar__inner input:focus { border-color: var(--gold-dark); }
/* TASK-3 END */

/* ═══════════════════════════════════════════════════════
   CATEGORY GRID SECTION
   ═══════════════════════════════════════════════════════ */
```

**JS — `pcLocationSuffix()` helper + card links now carry `?state=`**

*Old code:*
```javascript
/* ─── Build category cards ───────────────────────────── */
function buildCards(counts) {
  var grid = document.getElementById('catsGrid');
  if (!grid) return;

  grid.innerHTML = CATEGORIES.map(function (cat, i) {
    var cnt = counts[cat.slug] !== undefined ? counts[cat.slug] : null;
    var countLabel = cnt === null ? 'Loading…' : cnt > 0 ? cnt + ' available' : 'Check back soon';

    return (
      '<a href="' + cat.url + '" class="cat-card" style="animation:slideUp 0.45s cubic-bezier(0.22,1,0.36,1) ' + (i * 0.06) + 's both;">' +
```

*New code:*
```javascript
/* TASK-3 START — Location filter: append whatever's typed in the location
   bar to every card link as "?state=", so the category page can auto-select
   that state on arrival (see the matching read-on-load added to each of the
   7 category pages). Re-runs buildCards() on input so links stay in sync. */
function pcLocationSuffix() {
  var input = document.getElementById('petsLocationInput');
  var val = input ? input.value.trim() : '';
  return val ? '?state=' + encodeURIComponent(val) : '';
}
// TASK-3 END

/* ─── Build category cards ───────────────────────────── */
function buildCards(counts) {
  var grid = document.getElementById('catsGrid');
  if (!grid) return;

  grid.innerHTML = CATEGORIES.map(function (cat, i) {
    var cnt = counts[cat.slug] !== undefined ? counts[cat.slug] : null;
    var countLabel = cnt === null ? 'Loading…' : cnt > 0 ? cnt + ' available' : 'Check back soon';
    var cardUrl = cat.url + pcLocationSuffix(); // TASK-3

    return (
      '<a href="' + cardUrl + '" class="cat-card" style="animation:slideUp 0.45s cubic-bezier(0.22,1,0.36,1) ' + (i * 0.06) + 's both;">' +
```

**JS — cache last-fetched counts and re-render card links on input (no refetch)**

*Old code:*
```javascript
/* ─── Fetch pet counts from JSON files ───────────────── */
/* Data source: /adoptions/pets/{category}/index.json     */
(function loadCounts() {
  // Show skeletons removed → build cards first with null counts for instant render
  buildCards({});

  Promise.all(
    CATEGORIES.map(function (cat) {
      return fetch('/adoptions/pets/' + cat.slug + '/index.json')
        .then(function (r) {
          if (!r.ok) throw new Error('HTTP ' + r.status);
          return r.json();
        })
        .then(function (data) {
          // JSON format: { "count": N, "pets": [...] }
          var count = typeof data.count === 'number' ? data.count : (Array.isArray(data.pets) ? data.pets.length : 0);
          return { slug: cat.slug, count: count };
        })
        .catch(function () {
          return { slug: cat.slug, count: 0 };
        });
    })
  ).then(function (results) {
    var counts = {};
    results.forEach(function (r) { counts[r.slug] = r.count; });
    buildCards(counts);
  });
})();
```

*New code:*
```javascript
/* ─── Fetch pet counts from JSON files ───────────────── */
/* Data source: /adoptions/pets/{category}/index.json     */
var pcLastCounts = {}; // TASK-3 — kept so the location bar can re-render links without refetching
(function loadCounts() {
  // Show skeletons removed → build cards first with null counts for instant render
  buildCards({});

  Promise.all(
    CATEGORIES.map(function (cat) {
      return fetch('/adoptions/pets/' + cat.slug + '/index.json')
        .then(function (r) {
          if (!r.ok) throw new Error('HTTP ' + r.status);
          return r.json();
        })
        .then(function (data) {
          // JSON format: { "count": N, "pets": [...] }
          var count = typeof data.count === 'number' ? data.count : (Array.isArray(data.pets) ? data.pets.length : 0);
          return { slug: cat.slug, count: count };
        })
        .catch(function () {
          return { slug: cat.slug, count: 0 };
        });
    })
  ).then(function (results) {
    var counts = {};
    results.forEach(function (r) { counts[r.slug] = r.count; });
    pcLastCounts = counts; // TASK-3
    buildCards(counts);
  });
})();

// TASK-3 START — Rebuild card links (not a refetch) whenever the location text changes
(function () {
  var input = document.getElementById('petsLocationInput');
  if (!input) return;
  input.addEventListener('input', function () { buildCards(pcLastCounts); });
})();
// TASK-3 END
```



## Bugs Found Outside Task Scope
`publish-pet.php` (admin dashboard, not a file assigned to this task) does not write a `location` field into the `$pet_data` array it saves to `pet.json` — found while checking `pet.json`'s real schema (`publish-pet.php`, the block that assembles `$pet_data`, roughly the field-list section following the required-fields validation). The sample `pet.json` supplied this session does have a `"location"` field, so existing pets are fine, but any pet published through the current `publish-pet.php` going forward would save with no `location` value — meaning it would silently fail to appear in State/City chip results and would always be excluded from radius search (radius search deliberately excludes any pet whose location can't be resolved rather than guessing). Not fixed, per Scope Lock — `publish-pet.php` isn't one of this task's assigned files.

## Self-Check Confirmation
Confirmed: only the 8 files listed under Files Changed above were modified — `adoption-website-project-summary.md`, `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, `publish-pet.php`, and `pet.json` were read for reference only and were not edited. Every code block added in this task, in every file, is wrapped in a `TASK-3 START` / `TASK-3 END` anchor comment (`<!-- TASK-3 START -->`/`<!-- TASK-3 END -->` in HTML/CSS, `// TASK-3 START`/`// TASK-3 END` in JS) and carries its own explanatory comment, matching what's pasted above exactly — verified with a string-match check against the real files before this report was written, not just visually.

## Coordination Note
Built against the original (pre-Task-5) versions of these 8 files, since Task 5's `CTA`-zone submission wasn't reported as already merged in. If Task 5 has since submitted, these files need to be re-diffed against that version before merging — the TASK-3 anchor comments make the location-filter changes easy to isolate and reapply on top of Task 5's work.


---

## SECOND VERIFICATION ROUND

Victor asked for a full re-check of this work against both project docs and the task text. The re-check found real problems in code I had already submitted (listed below), so changes were made. Everything here is documented in the same format as above. Changes to the Task 3b / 3c parts of the same feature (country selector, geolocation button, server-side search, `geo-filter.php`) are documented in the same-named section of `Task-3b-Completion.md`, since that is where those changes were first submitted.

### What the re-check found in Task 3's own code
1. **Results and errors were invisible.** The Apply handler closed the sheet and *then* wrote messages ("couldn't find that location", "enter a city...") into a status line that lives inside the sheet. Nobody could see them. A failed lookup just showed every pet again with no explanation.
2. **Distance results were never ordered by distance.** The server returns nearest-first, but the page re-sorted by name, so that work was thrown away. Petfinder-style location search sorts by distance.
3. **Location text was matched too strictly.** `pet.location` is free text typed by an admin. "Los Angeles CA" (no comma) produced no state at all, and "ca" / "Ca" / "CA" would each have become a separate State chip. City names had the same capitalisation problem.
4. **A zero-result distance search showed the generic "No pets match your filters" text**, with no hint that the distance was the cause.

### Files Changed

| File | Shared with another task? |
|---|---|
| `cats.html` | Yes: Task 5 (`CTA` zone), Task 2 (`NAV-LINKS` zone), Task 3b/3c (same file, earlier rounds) |
| `dogs.html` | Yes: same as above |
| `birds.html` | Yes: same as above |
| `reptiles.html` | Yes: same as above |
| `aquariums.html` | Yes: same as above |
| `livestocks.html` | Yes: same as above |
| `small-mammals.html` | Yes: same as above |

`pets.html`, `geo-filter.php` and every other file were not touched in the Task 3 part of this round. All blocks below carry `TASK-3d START` / `TASK-3d END` anchors (new, so they stay separate from `TASK-3`, `TASK-3b`, `TASK-3c`).

### Code Changes

Applied verbatim to all 7 category pages (`cats.html`, `dogs.html`, `birds.html`, `reptiles.html`, `aquariums.html`, `livestocks.html`, `small-mammals.html`) unless a per-file variant is shown, and verified with an exact one-match string check per file.

**HTML: visible location notice above the pet grid**

*Why:* Gives results, errors and a one-tap "Clear" a place the visitor can actually see (finding 1). In `cats.html`, `dogs.html`, `birds.html`, `reptiles.html` and `small-mammals.html`.

*Old code:*
```html
      <!-- TASK-1 END -->
    </div>

```

*New code:*
```html
      <!-- TASK-1 END -->
    </div>
    <!-- TASK-3d START — Visible location/distance notice. The sheet closes on Apply, so anything
         written to the in-sheet status line (errors, "couldn't find that location") was never
         seen. This line sits above the pet grid and carries the active-distance summary plus a
         one-tap Clear. Text is always set with textContent (visitor-typed text goes in here). -->
    <p class="pc-location-notice" id="pcLocationNotice" role="status" aria-live="polite" hidden></p>
    <!-- TASK-3d END -->

```

**HTML: same notice, per-file variant for `aquariums.html` and `livestocks.html`**

*Why:* These two files do not have the `<!-- TASK-1 START -->` / `<!-- TASK-1 END -->` wrapper around the Task 1b comment that the other five have (logged under Bugs Found Outside Task Scope), so the anchor text used above does not exist there. The notice was anchored on the Signin link instead.

*Old code:*
```html
      <a href="/signin/" class="pc-btn pc-btn--signin">Signin</a>
    </div>

```

*New code:*
```html
      <a href="/signin/" class="pc-btn pc-btn--signin">Signin</a>
    </div>
    <!-- TASK-3d START — Visible location/distance notice. The sheet closes on Apply, so anything
         written to the in-sheet status line (errors, "couldn't find that location") was never
         seen. This line sits above the pet grid and carries the active-distance summary plus a
         one-tap Clear. Text is always set with textContent (visitor-typed text goes in here). -->
    <p class="pc-location-notice" id="pcLocationNotice" role="status" aria-live="polite" hidden></p>
    <!-- TASK-3d END -->

```

**CSS: notice styling**

*Why:* Styles the notice from finding 1.

*Old code:*
```css
/* TASK-3b END */
```

*New code:*
```css
/* TASK-3b END */

/* TASK-3d START — Visible distance/location notice above the pet grid */
.pc-location-notice {
  display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
  margin: -8px 0 18px; padding: 10px 14px; border-radius: 10px;
  background: rgba(215,189,136,.18); border: 1px solid rgba(184,153,104,.45);
  font-family: 'Sora', sans-serif; font-size: 13px; color: var(--navy);
}
.pc-location-notice[hidden] { display: none; }
.pc-location-notice__clear {
  background: none; border: 0; padding: 2px 4px; cursor: pointer;
  font-family: inherit; font-size: 13px; font-weight: 600; color: var(--navy); text-decoration: underline;
}
/* TASK-3d END */
```

**JS: `pcParseLocation()` tolerates missing commas and normalises state codes**

*Why:* Finding 3. A trailing two-letter code is peeled off when there is no comma, and short codes are upper-cased so one state never splits into several chips.

*Old code:*
```javascript
function pcParseLocation(loc) {
  if (!loc) return { city: '', state: '' };
  var parts = String(loc).split(',');
  return { city: (parts[0] || '').trim(), state: (parts[1] || '').trim() };
}
```

*New code:*
```javascript
function pcParseLocation(loc) {
  if (!loc) return { city: '', state: '' };
  var text = String(loc).trim();
  var parts = text.split(',');
  var city = (parts[0] || '').trim();
  var state = (parts[1] || '').trim();
  // TASK-3d START — pet.location is free text typed by an admin. Tolerate a missing comma
  // ("Los Angeles CA") by peeling a trailing 2-letter code off as the state, and upper-case
  // short codes so "ca" / "Ca" / "CA" never become three separate State chips.
  if (!state) {
    var m = text.match(/^(.*\S)\s+([A-Za-z]{2})$/);
    if (m) { city = m[1].trim(); state = m[2]; }
  }
  if (state.length <= 3) state = state.toUpperCase();
  // TASK-3d END
  return { city: city, state: state };
}
```

**JS: case-insensitive de-dupe when building the State/City chips**

*Why:* Finding 3.

*Old code:*
```javascript
      if (loc.state && states.indexOf(loc.state) === -1) states.push(loc.state);
      if (loc.city && cities.indexOf(loc.city) === -1) cities.push(loc.city);
```

*New code:*
```javascript
      // TASK-3d — case-insensitive de-dupe so differently-capitalised spellings share one chip
      if (loc.state && !states.some(function(s) { return s.toLowerCase() === loc.state.toLowerCase(); })) states.push(loc.state);
      if (loc.city && !cities.some(function(c) { return c.toLowerCase() === loc.city.toLowerCase(); })) cities.push(loc.city);
```

**JS: case-insensitive State/City comparison in `applyAndRender()`**

*Why:* Finding 3. Chips and the comparison now use the same normalisation, so a pet always matches its own chip.

*Old code:*
```javascript
        if (pcState && petLoc.state !== pcState) return false;
        if (pcCity && petLoc.city !== pcCity) return false;
```

*New code:*
```javascript
        if (pcState && petLoc.state.toLowerCase() !== pcState.toLowerCase()) return false; // TASK-3d — case-insensitive
        if (pcCity && petLoc.city.toLowerCase() !== pcCity.toLowerCase()) return false;
```

**HTML: "Nearest First" option on the hidden sort select**

*Why:* Finding 2.

*Old code:*
```html
      <option value="fee_desc">Highest Fee</option>
    </select>
    <select class="hidden-select" id="filterCompatible"
```

*New code:*
```html
      <option value="fee_desc">Highest Fee</option>
      <option value="distance">Nearest First</option><!-- TASK-3d — only meaningful while a distance search is active; otherwise treated as Name A-Z -->
    </select>
    <select class="hidden-select" id="filterCompatible"
```

**HTML: "Nearest First" chip in the sheet's Sort row**

*Why:* Finding 2. Reuses the existing sort chip row rather than adding a new control.

*Old code:*
```html
            <button type="button" class="pc-chip" data-value="fee_desc">Highest Fee</button>
          </div>
```

*New code:*
```html
            <button type="button" class="pc-chip" data-value="fee_desc">Highest Fee</button>
            <button type="button" class="pc-chip" data-value="distance">Nearest First</button><!-- TASK-3d -->
          </div>
```

**JS: Nearest First ordering, plus the distance-specific empty-state hint text**

*Why:* Finding 2 and 4. Orders by position in the list `geo-filter.php` already returned (the server's Haversine result). With no active distance search it falls back to Name A-Z, because there is nothing to be near.

*Old code:*
```javascript
    if (sort === 'name') {
      filtered.sort(function(a, b) { return (a.name || '').localeCompare(b.name || ''); });
```

*New code:*
```javascript
    // TASK-3d START — "Nearest First": geo-filter.php already returns IDs nearest-first, so
    // ordering by position in that list reuses the server's Haversine result. Without an
    // active distance search there is nothing to be near, so it falls back to Name A-Z.
    if (sort === 'distance' && !pcRadiusActive) sort = 'name';
    window.pcRadiusEmptyHint = pcRadiusActive
      ? 'No pets found within that distance. Try a larger radius, or clear the distance filter.'
      : '';
    if (sort === 'distance') {
      filtered.sort(function(a, b) {
        return pcRadiusMatchIds.indexOf(a.id || a.pet_id) - pcRadiusMatchIds.indexOf(b.id || b.pet_id);
      });
    } else
    // TASK-3d END
    if (sort === 'name') {
      filtered.sort(function(a, b) { return (a.name || '').localeCompare(b.name || ''); });
```

**JS: empty-state paragraph uses the distance hint when a distance search is active**

*Why:* Finding 4.

*Old code:*
```javascript
<h3>No pets match your filters</h3><p>Try adjusting your search criteria.</p></div>';
```

*New code:*
```javascript
<h3>No pets match your filters</h3><p>' + (window.pcRadiusEmptyHint || 'Try adjusting your search criteria.') + '</p></div>'; // TASK-3d — distance-specific hint when a radius search is active
```

### Bugs Found Outside Task Scope
- **`aquariums.html` and `livestocks.html`, around lines 1112-1114 (the header row above the pet grid):** the Task 1b comment about renaming "List Out A Pet" to "Signin" is not wrapped in `<!-- TASK-1 START -->` / `<!-- TASK-1 END -->`, while the other five category pages have the wrapper. Task 1's anchor convention is inconsistent across these files. Not fixed (Scope Lock); whoever owns the Task 1 merge should add it.
- **Zones not yet merged.** Task 5 (`CTA` zone) and Task 2 (`NAV-LINKS` zone) also edit these same 8 files. I have never been given either worker's submitted versions, so none of their changes are in the files above. Task 2's work is known to have changed the header/footer and dropdowns in `cats.html`, and the other category pages and `pets.html` still need the same patch. The final merge needs the latest submitted version of each of the 8 files from those tasks.
- Carried forward unchanged from the first submission: `publish-pet.php` does not write a `location` field (see below under Task 3b for the extra detail found this round).

### Self-Check Confirmation
Confirmed: only the 7 category pages listed above were modified in this part of the round. `pets.html`, `publish-pet.php`, `pet.json` and both project summary docs were read only. Every block added carries a `TASK-3d` anchor comment and an explanatory comment, copied word for word from the real files.

**How this was verified.** Each of the 7 category pages was loaded in a real headless Chromium (Playwright) with the pet data, geocoder and `geo-filter.php` responses mocked, and run through 26 checks: chip de-duplication, State filter, radius search, rounded coordinates, Nearest First ordering, stale-result regression, geocode failure, endpoint failure, empty result, "still being looked up" note, notice Clear button, "Use my location", typed-text-vs-geolocation, country placeholder, `?state=` prefill, and no uncaught JS errors. All 26 pass on all 7 pages. The same test run against the pre-round-2 `cats.html` fails at its first new-behaviour check.

**What was NOT verified.** No live network was available, so real OpenStreetMap lookups were not exercised, only mocked ones.
