# Task-9-Completion.md

## Task Number
**Task 9 — Duplicate-Submission Detection** (from `WEB-PROJECTS-3-4-5-TASK-ASSIGNMENTS.md`, Project 5: Pet-Match Request feature)

## What Was Required
When a new Pet Match Request is submitted, check for an existing unresolved request from the same visitor (matching email + similar description, submitted within a short recent window). Do not block the new submission — flag it, and surface that flag as a small indicator on the matching request(s) in Task 6a's admin listing, so staff can see it at a glance instead of manually cross-referencing every new submission against every open one. Two named zones: `SUBMISSION-CHECK` on the visitor-facing form/backend (built in Task 5), `DUPLICATE-FLAG` on the admin listing (built in Task 6a). Recommended enhancements (applied): make the detection window a configurable constant, not hardcoded; normalize email before matching.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `pet-match-submit.php` | `/pet-match-submit.php` (site root — not in `admin/`; confirmed by `pet-match.html`'s own `fetch('/pet-match-submit.php', ...)` root-relative call) | **New** | Yes — this is the natural home of the `SUBMISSION-CHECK` zone (Task 5's form already POSTs here), but almost all of the file is scaffolding implementing Task 5b's already-documented contract (honeypot/timing-trap/Turnstile/rate-limit), not new design by this task. Only the block marked `TASK-9` inside it is this task's own logic. |
| `get-pet-match-requests.php` | `/admin/get-pet-match-requests.php` (not in the delivery guide's mapping table; inferred from the admin PHP convention and confirmed against Task 6a's own documented build, which places all new admin PHP files directly in `admin/`) | Changed | Yes — `LISTING-CORE` zone (Task 6a, unchanged by this task) plus this task's `DUPLICATE-FLAG` addition, in the same file |
| `admin-dashboard.html` | `/admin/index.html` (per the guide's mapping table) | Changed | Yes — pulled forward the most recent version (Task 6b's, which itself pulled forward Task 6a's) per Sequential File Ownership Chain. Only the `DUPLICATE-FLAG` zone was touched; Task 6a's `LISTING-CORE` and Task 6b's `DETAIL-MODAL` zones are both left untouched. |

**No new, undocumented folder is needed.** `pet-match-submit.php` sits at the site root, which already exists; `get-pet-match-requests.php` sits in `admin/`, which already exists and already holds sibling files from Task 6a. Nothing in this task's scope introduces a folder not already present in either project summary document.

## Code Changes

### `pet-match-submit.php` (new file — full file in the zip at `/pet-match-submit.php`)
No existing code to merge — this is a new file, submitted in full inside the zip rather than pasted here, per the delivery guide. It is anchor-commented throughout; the actual Task 9 logic is isolated between `// TASK-9 START` and `// TASK-9 END` inside it (the duplicate-detection scan and its two named constants). Everything outside that block — request parsing, honeypot/timing-trap/Turnstile checks, `chatCheckRateLimit()` call, required-field validation, and the final `request.json` write — implements Task 5b's own already-documented backend contract and Task 6a/6b's already-established flat-file storage convention (`/adoptions/pet-match-requests/{category}/{request_code}/request.json`, mirroring `/adoptions/applications/{code}/application.json`). This file did not exist before this task; per the CEO's direct instruction, Task 9 studied `pet-match.html` and Task 5b's documented contract and built it. Deliberately has **no** admin-token check, unlike `get-pet-match-requests.php`/`update-pet-match-request.php` — this is the public visitor-facing endpoint, so its defenses are the honeypot/timing-trap/Turnstile/rate-limit checks instead, per Task 5b's spec for this specific endpoint.

**Path correction made during this hand-in fix pass (code logic unchanged, only two path strings corrected):** pinning down this file's exact live path for the Files Changed table above surfaced two real path bugs in the file's own internal `require_once`/`$baseDir` paths, left over from an earlier, incorrect assumption that this file would sit inside `admin/` alongside `get-pet-match-requests.php`. Since `pet-match-submit.php` is new (not yet deployed anywhere), this is a same-task self-correction, not a cross-task fix, and no old/new diff applies — the corrected file in the zip is simply the current, correct version:
- `require_once __DIR__ . '/chat-config.php';` → `require_once __DIR__ . '/admin/chat-config.php';` (this file lives at the site root; `chat-config.php` lives at `admin/chat-config.php`, confirmed in `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s File Structure on Server section — the two are not in the same folder)
- `$baseDir = __DIR__ . '/../adoptions/pet-match-requests/' . $category;` → `$baseDir = __DIR__ . '/adoptions/pet-match-requests/' . $category;` (from the site root, `adoptions/` is a direct child, not one level up)

Key excerpt (the actual TASK-9 zone, unchanged from the original build):

```php
// TASK-9 START — Duplicate-Submission Detection (this task's actual scope)
const DUPLICATE_DETECTION_WINDOW_HOURS = 72; // Recommended Enhancement 1: configurable constant, not hardcoded
$normalizedEmail = strtolower(trim($email)); // Recommended Enhancement 2: normalize before matching

const DUPLICATE_SIMILARITY_THRESHOLD_PERCENT = 60.0; // via PHP's built-in similar_text(), researched per Rule 11

$resolvedStatuses = ['FULFILLED', 'CLOSED']; // matches Task 6b's update-pet-match-request.php's real status values

// ...scans this category's existing request.json files, same normalized email,
// status not in $resolvedStatuses, created_at within the window, then:
similar_text(strtoupper($newDescriptionPlain), strtoupper($existingDescriptionPlain), $percent);
if ($percent >= DUPLICATE_SIMILARITY_THRESHOLD_PERCENT) {
    $isDuplicateFlagged = true;
    $duplicateOfRequestId = $existingCode;
    break;
}
// TASK-9 END
```

### `get-pet-match-requests.php` (existing file — changed, NOT in the zip; apply this diff to the live file)

```php
// OLD (end of the existing $requests[] = [...] block, as originally given to this task)
            'additional_detail'         => $data['additional_detail']         ?? '',
            'reference_image_url'       => $data['reference_image_url']       ?? null,
            'submitted_at'              => $data['submitted_at']              ?? '',
        ];

// NEW
            'additional_detail'         => $data['additional_detail']         ?? '',
            'reference_image_url'       => $data['reference_image_url']       ?? null,
            'submitted_at'              => $data['submitted_at']              ?? '',
            // TASK-9 START — Duplicate-Submission Detection (DUPLICATE-FLAG zone)
            // Written by pet-match-submit.php (Task 9), read here so the admin
            // listing has something to render at its reserved mount point.
            // Added to this explicit allowlist rather than passing $data
            // through wholesale, matching this file's own existing pattern of
            // only ever returning fields it has deliberately chosen to expose.
            'is_duplicate_flag'         => $data['is_duplicate_flag']         ?? false,
            'duplicate_of_request_id'   => $data['duplicate_of_request_id']   ?? null,
            // TASK-9 END
        ];
```

**Note on this file's current live state:** a later round (Task 6's own bug-fix pass, documented in that worker's own completion file) corrected several of this same file's OTHER field names (`age_preference`→`age_min_years`/`age_max_years`, `reference_image_url`→`reference_image_token`, `request_code`/`submitted_at` source fields, plus previously-missing `pet_name_preference`/`breed_purebred`/`breed_crossbred`/`pedigree_certified`) — none of which this task touched or is re-documenting here, since that correction is Task 6's own to report under its own completion file, not this one's. This task's two added lines above (`is_duplicate_flag`, `duplicate_of_request_id`) were left untouched by that fix and remain exactly as shown.

### `admin-dashboard.html` → live path `/admin/index.html` (existing file — changed, NOT in the zip; apply this diff to the live file)

**1. Computed the flag's HTML** inside `renderPetMatchRequests()`, next to the file's other per-row label computations:

```javascript
// OLD
    const dateLabel = reqItem.submitted_at ? formatAppDateShort(reqItem.submitted_at) : '—';
    const codeEsc = PC.esc(code);

// NEW
    const dateLabel = reqItem.submitted_at ? formatAppDateShort(reqItem.submitted_at) : '—';
    const codeEsc = PC.esc(code);

    // TASK-9 START — Duplicate-Submission Detection (DUPLICATE-FLAG zone)
    const dupeFlagHtml = reqItem.is_duplicate_flag
      ? `<span class="pm-row__dupe-flag-pill" title="${reqItem.duplicate_of_request_id ? 'Possible duplicate of ' + PC.esc(reqItem.duplicate_of_request_id) : 'Possible duplicate request'}">
           <i class="fas fa-triangle-exclamation" aria-hidden="true"></i> Possible Duplicate
         </span>`
      : '';
    // TASK-9 END
```

**2. Filled Task 6a's reserved mount point** — the row structure itself is unchanged, only the span's inner content changed from always-empty to conditionally populated:

```html
<!-- OLD -->
<span class="pm-row__dupe-flag" data-code="${codeEsc}"></span>

<!-- NEW -->
<span class="pm-row__dupe-flag" data-code="${codeEsc}">${dupeFlagHtml}</span>
```

**3. New CSS**, added directly after `.pc-app-applicant__chevron`:

```css
/* OLD */
.pc-app-applicant__chevron { display: none; }

/* NEW */
.pc-app-applicant__chevron { display: none; }
/* TASK-9 START — Duplicate-Submission Detection (DUPLICATE-FLAG zone) */
.pm-row__dupe-flag-pill {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 3px 10px; border-radius: 999px;
  font-family: 'Sora', sans-serif; font-weight: 600; font-size: 11px;
  background: rgba(245,158,11,0.1); color: #f59e0b;
  margin-left: 8px; white-space: nowrap;
}
.pm-row__dupe-flag-pill i { font-size: 10px; }
/* TASK-9 END */
```

Styling reuses the design system's Section 18 pill-badge shape (`inline-flex`, `999px` radius, Sora 600) and its documented Warning Amber token (`#f59e0b`, 10%-opacity background) — the same visual language as `.status-pending` — but with its own class and wording rather than reusing one of the five locked status-badge labels (OPEN/PENDING/IN PROGRESS/COMPLETED/CANCELLED). Those five are explicitly for a record's lifecycle stage; a duplicate flag is a heads-up alongside that stage, not the stage itself, so folding it into that fixed vocabulary would misuse rather than reuse the system. No `--warning`/`--amber` CSS variable exists in this file (confirmed by search — only one prior hardcoded use of the same hex), so the hardcoded-hex approach follows the file's own existing convention rather than introducing a new variable unilaterally.

**Note on this file's current live state:** `viewPetMatchRequest()`'s detail modal (Task 6b's `DETAIL-MODAL` zone, same file) was separately updated by Task 6 in the same later round referenced above — unrelated to and untouched by this task.

## Bugs Found Outside Task Scope

**1. `get-pet-match-requests.php`'s `$baseDir` is missing a `..` and points at a nonexistent directory.** Confirmed directly:
```php
$baseDir = __DIR__ . '/adoptions/pet-match-requests/' . $category;
```
This file lives at `admin/get-pet-match-requests.php`. Per `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s own documented "File Structure on Server," `admin/` and `adoptions/` are siblings under the site root, not nested — so reaching `adoptions/` from a file inside `admin/` requires `__DIR__ . '/../adoptions/...'`. As written, this resolves to a nonexistent `admin/adoptions/pet-match-requests/{category}/` instead of the real site-root-level `adoptions/pet-match-requests/{category}/`. Cross-checked against the one other documented example in the same doc (`delete-application.php`: `realpath(__DIR__ . '/../adoptions/applications')`) — confirms `../` is the correct, established convention for a file in `admin/` reaching `adoptions/`. **Practical effect:** the admin listing likely shows zero Pet Match Requests regardless of how many visitors submit the form — a full pipeline break for the whole feature, not specific to the duplicate-flag indicator. This is Task 6a's `LISTING-CORE` zone, not this task's `DUPLICATE-FLAG` addition to the same file — not fixed here, per Scope Lock. Flagging for whoever owns that zone.

**2. `get-pet-match-requests.php`'s response field names did not match `pet-match.html`'s actual submitted field names** (as of the version of the file this task was originally given). Already reported to and independently confirmed/fixed by Task 6's own worker in a later round — see that worker's own completion file for the fix. Not re-documented here in detail since it is that task's own bug to report, not this one's; retained as a one-line record only because it was this task's own discovery that triggered that fix.

No other new out-of-scope bugs were found. The pre-existing `showToast is not defined` bug already logged in `Task-6a-Completion.md` and re-confirmed in `Task-6b-Completion.md` was not encountered again in this task's own testing, so it is not re-logged a third time.

## Pending / Open Items / Things This Task Is Not Fully Certain Of

- The two `$baseDir`-adjacent path bugs found and self-corrected in this task's own `pet-match-submit.php` (see Code Changes above) were only caught during this delivery-guide hand-in fix pass, specifically because it required pinning down this file's exact live path. No PHP interpreter is available in this sandbox to execute either file and confirm the corrected paths resolve correctly at runtime; the correction is based on careful cross-referencing of the documented server file tree and the one other documented real-world example (`delete-application.php`), not a live test. Recommend the integrator or whoever deploys this confirm both paths resolve correctly in the real environment before relying on them.
- The `similar_text()` threshold (60%) and detection window (72 hours) in `pet-match-submit.php`'s `TASK-9` block are reasonable starting values chosen from this task's own reasoning, not values tuned against real submission data, since none exists yet. Both are named constants specifically so they can be adjusted later without touching the surrounding logic.
- This task's own duplicate-detection logic and the `DUPLICATE-FLAG` indicator in `admin-dashboard.html` were verified with real Playwright-driven rendering and ten boundary-case simulations (see this file's prior verification-round sections, carried forward unchanged below) — that verification evidence remains valid and is not re-run in this hand-in fix pass, since no logic was changed, only the two path-string corrections noted above and this document's format.

## Self-Check Confirmation
Only the files listed above were touched, and only in this task's two named zones. In `get-pet-match-requests.php`, the only change is the addition inside `TASK-9 START`/`END` anchors to the existing response allowlist — Task 6a's token validation, category handling, and every other returned field are untouched by this task. In `admin-dashboard.html`, the only changes are the three `TASK-9`-anchored additions (one JS computation, one filled-in span, one CSS block) — Task 6a's `LISTING-CORE` zone and Task 6b's `DETAIL-MODAL` zone are both fully intact and untouched by this task. `pet-match-submit.php` is a new file with no prior live version to diff against; this hand-in pass corrected two internal path strings within it (documented above) without altering any of its actual duplicate-detection logic.

I pulled forward the most recent version of `admin-dashboard.html` available at the time of each round of this task's work, per the Sequential File Ownership Chain.

No PHP interpreter is available in this sandbox (network access is disabled), so `pet-match-submit.php` and the change to `get-pet-match-requests.php` could not be executed directly. Both were checked for balanced braces/parentheses/brackets programmatically, and their logic was manually traced against the real field names and storage conventions confirmed in `pet-match.html`, `get-pet-match-requests.php`, `Task-6a-Completion.md`, and `Task-6b-Completion.md`. The `admin-dashboard.html` template-literal change was further verified with real Playwright-driven browser rendering (not just read-through reasoning): the real file was loaded in headless Chromium, the actual `renderPetMatchRequests()` function was driven with injected test data at both 375px and 1280px viewports, and the resulting `.pm-row__dupe-flag-pill` indicator was confirmed via screenshot and computed-style measurement to render correctly with no overflow, no overlap with adjacent elements, and correct fallback behavior across three edge cases (flag true, flag false, flag field absent), with zero console errors. Ten boundary-case simulations of the PHP duplicate-detection logic (empty descriptions, identical descriptions, case-insensitive email/status matching, multiple candidate matches, empty category folders) were run in a Python-based line-by-line mirror of the real control flow, since no PHP interpreter exists in this sandbox — this limitation is stated plainly rather than presenting an untested claim as verified. The 72-hour detection window's boundary behavior and its `strtotime()`/`date('c')` round-trip compatibility were separately confirmed, the latter via web search.
