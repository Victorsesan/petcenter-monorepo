# Task-6a-Completion.md

## Task Number
**Task 6a — Admin Listing & Filtering Structure** (Pet Center project, `WEB-PROJECTS-3-4-5-TASK-ASSIGNMENTS.md`, Project 5: Pet-Match Request feature)

## What Was Required
Build a new admin dashboard section for managing submitted Pet Match Requests, organized into sub-sections by pet category — mirroring the existing category-based organization already used for published pets — with independent filtering per category by recency, price, and breed, reusing the site's existing filter pattern rather than rebuilding one. Scope stops at a working, filterable list per category; the detail modal and admin actions are Task 6b's responsibility, not this task's.

## Files Changed

| Source name in my task | Live path | Changed or new | Shared with another task? |
|---|---|---|---|
| `admin-dashboard.html` | `admin/index.html` | Changed | Yes — LISTING-CORE zone (this task). Also touched by Task 6b (DETAIL-MODAL zone, depends on this file) and Task 9 (DUPLICATE-FLAG zone, depends on this file + Task 5) |
| `get-pet-match-requests.php` | `admin/get-pet-match-requests.php` | New | No — new backend file, not touched by another task at time of writing |

## New Folder Not in Either Summary Doc — Flagged
This task introduces a storage path that does **not** appear in `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s documented folder structure: `/adoptions/pet-match-requests/{category}/{request_code}/request.json`. The closest existing documented patterns are `/adoptions/applications/{code}/` (no category subfolder) and `/adoptions/pets/{category}/{pet_id}/` (category subfolder, different record type) — this task's structure merges both conventions since Pet Match Requests are both per-submission (like applications) and category-organized (like pets), but neither summary doc names this exact path. **The integrator needs to create `/adoptions/pet-match-requests/` as a new top-level folder under `/adoptions/`**, with one subfolder per category slug (`dogs`, `cats`, `birds`, `reptiles`, `small-mammals`, `livestocks`, `aquariums`), each containing one subfolder per submission named by that submission's `request_code`/`reference_code`, each holding a single `request.json`. This path is also written to by `pet-match-submit.php` (Task 5b's zone) and read/written by Task 6b's and Task 9's files — all of which must agree on this same path.

## Code Changes

A note on sourcing: by the time this hand-in format was issued, my working copy of `admin-dashboard.html` had already been edited across this task, Task 6b, and a post-submission field-name correction (made after Task 9 and Task 5 flagged a real mismatch against the live `pet-match.html` — documented in this file's Revision Note below). The true zero-edit original I was first given is no longer in my possession. Every "Old code" snippet below is therefore copied exactly from the parts of my current file that were never touched by any of those three edits (verified by checking each snippet is the real, pre-existing sibling code immediately surrounding my insertion points, not anything I wrote) — not reconstructed from memory. Where I can't show a true "before" for something that is itself new content (the panel markup, the JS block), I've marked it as new insertion with no prior counterpart, per the guide's own allowance for that case.

### `admin-dashboard.html`

**1. Sidebar icon-rail entry** — new button inserted between Applications and Admin Profiles.

Old code (copied exactly from the file, unmodified sibling buttons either side of my insertion):
```html
<button class="admin-icon-rail__item" data-panel="applications" onclick="switchPanel('applications')" title="Applications"><i class="fas fa-file-lines"></i></button>
<button class="admin-icon-rail__item" data-panel="adminProfiles" onclick="switchPanel('adminProfiles')" title="Admin Profiles"><i class="fas fa-user-shield"></i></button>
```

New code:
```html
<button class="admin-icon-rail__item" data-panel="applications" onclick="switchPanel('applications')" title="Applications"><i class="fas fa-file-lines"></i></button>
<!-- TASK-6a START: sidebar entry point for the new Pet Match Requests
     section. Positioned directly after Applications since both are
     visitor-submission review workflows. Reuses the exact same
     admin-icon-rail__item + data-panel + switchPanel(...) pattern as
     every other rail button — no new sidebar mechanism introduced.
     This single button also becomes the mobile drawer's matching entry
     automatically via renderMobileIconMenu(), which clones every
     .admin-icon-rail__item — no separate mobile markup needed. -->
<button class="admin-icon-rail__item" data-panel="petMatch" onclick="switchPanel('petMatch')" title="Pet Match Requests"><i class="fas fa-paw"></i></button>
<!-- TASK-6a END -->
<button class="admin-icon-rail__item" data-panel="adminProfiles" onclick="switchPanel('adminProfiles')" title="Admin Profiles"><i class="fas fa-user-shield"></i></button>
```

**2. `switchPanel()` — new case added inside the existing shared function.**

Old code (copied exactly, the real unmodified pair of `if` blocks either side of my insertion):
```javascript
if (panel === 'applications') { 
  upgradeNativeSelectsToCustom();
  initCustomSelects();
  loadApplications(); 
}
if (panel === 'liveChat') {
  initChatPanel();
}
```

New code:
```javascript
if (panel === 'applications') { 
  upgradeNativeSelectsToCustom();
  initCustomSelects();
  loadApplications(); 
}
// TASK-6a START: hook the new Pet Match Requests panel into switchPanel(),
// the same shared dispatcher every other panel uses. Mirrors Applications'
// own init sequence exactly (upgrade any native <select> filters to the
// custom-dropdown component, then load data) since the petMatch panel
// reuses that same filter-select pattern.
if (panel === 'petMatch') {
  upgradeNativeSelectsToCustom();
  initCustomSelects();
  loadPetMatchRequests();
}
// TASK-6a END
if (panel === 'liveChat') {
  initChatPanel();
}
```

**3. New `petMatchPanel` markup** — inserted as a new sibling panel immediately after the Applications panel closes. This is new content with no prior counterpart at this location; the surrounding structure (unchanged) is shown for placement context only:

Old code (copied exactly, the real unmodified closing lines of the Applications panel, immediately followed by the start of the next section comment):
```html
        <div id="applicationsList"></div>
        <div id="applicationsEmpty" style="display:none;padding:48px 24px;text-align:center;background:#fff;border-radius:0 0 8px 8px;border:1px solid #eee;border-top:none;">
          <p style="color:#aaa;font-size:14px;">No applications match the current filters.</p>
        </div>
        </div>
      </div>

    </div>

    <!-- LIVE CHAT PANEL -->
```

New code: the full `petMatchPanel` block (category picker using the exact `.pc-cat-btn` pattern from Edit Pets, filter row for recency/price/breed, bulk-action bar, and a listing table reusing the Applications panel's `.pc-apps-scroll`/`.pc-app-row` classes) was inserted between the closing `</div>\n\n</div>` and the `<!-- LIVE CHAT PANEL -->` comment, wrapped in `<!-- TASK-6a START -->` / `<!-- TASK-6a END -->`. Full markup is in the delivered file; not reproduced in full here as it is new content, per this guide's allowance for new insertions.

**4. New JS block** — inserted after `updateAppBulkActionBar()`, before `deleteApplicationsBulk()`. Also new content with no prior counterpart; shown for placement context:

Old code (copied exactly, the real unmodified end of `updateAppBulkActionBar()`):
```javascript
function updateAppBulkActionBar() {
  const bar = document.getElementById('appBulkActionBar');
  const count = selectedApplicationCodes.size;
  if (!bar) return;
  if (count > 0) {
    bar.style.display = 'flex';
    document.getElementById('appBulkSelectedCount').textContent = count + ' selected';
  } else {
    bar.style.display = 'none';
  }
}
```

New code: `allPetMatchRequests`/`selectedPetMatchCodes` state, `selectPetMatchCategory()`, `loadPetMatchRequests()`, `renderPetMatchRequests()` (breed filter + price/recency sort), `togglePmActionsPanel()`, `togglePmRowDetail()`, `toggleSelectAllPetMatch()`, `updatePmBulkActionBar()`, and a stub `viewPetMatchRequest()` (later replaced by Task 6b's real implementation — see that task's completion file), all wrapped in `// TASK-6a START` / `// TASK-6a END`. Full code is in the delivered file.

### `get-pet-match-requests.php` (new file)
New file — no existing code to merge. Submitted directly in the zip at its live path. Token validation mirrors the rest of the pet-management PHP family (reads `ADMIN_TOKEN` from `.env`, accepts it via `$_POST['token']` or the `X-Admin-Token` header, `hash_equals()` comparison). Requires a `category` POST field; scans `/adoptions/pet-match-requests/{category}/` for one `request.json` per submission subfolder and returns an allowlisted set of fields (see Revision Note below for a correction made to this allowlist after initial submission).

## Bugs Found Outside Task Scope
1. `showToast is not defined` fires in the browser console when `checkTokenBeforeAction()` runs without a valid session. Verified not introduced by this task — reproduced identically by clicking the pre-existing, untouched Applications panel under the same conditions, with none of this task's code involved. Not fixed, per scope lock.
2. The `.admin-content`-style wrapper around the Applications panel has a `</div>` closing tag immediately after the Applications panel's own closing `</div>` whose matching opening tag sits much earlier in the file — structurally unusual on inspection, pre-dates this task, not touched. Flagged for visibility only.

## Revision Note (post-submission correction)
After this task's original submission, Task 9's completion doc flagged that `get-pet-match-requests.php`'s response allowlist did not match the real `pet-match.html`/`pet-match-submit.php` payload (`age_preference` vs. the real `age_min_years`/`age_max_years`, `reference_image_url` vs. the real `reference_image_token`, `request_code`/`submitted_at` vs. the real `reference_code`/`created_at`, and missing `pet_name_preference`/`breed_purebred`/`breed_crossbred`/`pedigree_certified`/`status` entirely). Verified accurate directly against the real `pet-match.html` and `pet-match-submit.php` once supplied. Root cause: `pet-match.html` was revised by Task 5's worker after the `Task-5-Completion.md` I originally built against was written, and that revision wasn't reflected into an updated completion doc at the time. Corrected the response allowlist to the real field names; Task 9's own `is_duplicate_flag`/`duplicate_of_request_id` addition to this same file was preserved untouched. The delivered file in this zip includes this correction.

## Self-Check Confirmation
Only `admin-dashboard.html` (the four scoped additions above, each wrapped in `TASK-6a START/END` anchors) and the new file `get-pet-match-requests.php` were touched for this task. No file outside this list was modified. The panel was tested in a headless browser (category selection, row rendering, mobile row-expand, the 3-dot actions popover, bulk-select checkboxes, breed filtering, budget sorting) using synthetic in-memory data, since no live submissions exist to read yet. The new storage folder this task depends on does not exist in either summary doc and is flagged above for the integrator to create.
