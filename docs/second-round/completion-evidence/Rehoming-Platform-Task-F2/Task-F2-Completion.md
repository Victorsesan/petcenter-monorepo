# Task F2 — Compatibility Quiz — Completion

## Task Number
F2

## What Was Required
A short lifestyle/compatibility quiz, open to every visitor (logged in or not) for the current session, that scores each pet listing against the visitor's answers using a simple, transparent weighted-match point system — not a black-box algorithm — and displays the result as one of three labels ("Good match" / "Might work" / "Less likely"), never a raw numeric score. The quiz covers 5 fixed questions (energy level, home type, experience, other pets in the home, kids in the home). Mismatches on hard constraints (the pet's own `compatible_kids`/`compatible_dogs`/`compatible_cats`/`compatible_other_pets` fields vs. the visitor's answers) drop a listing's score sharply rather than excluding it outright, since merchants may under-describe a pet. Per the revised VISIBILITY & ACCESS MODEL, saving quiz results (or applying them to a specific enquiry) requires an account — a logged-out visitor still gets match labels on listings for the session, but the "Save My Results" action calls `showAuthPrompt()`, Task A4's shared component, which this task does not implement or stub a replacement for.

File zone (per the task doc): new quiz page/component, and the listing display pages (badge/label addition only).

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `pc-compat-quiz.js` | `assets/js/pc-compat-quiz.js` | New file — in the zip | Yes — loaded by both the quiz page and all 7 listing pages below, so they always score/label identically |
| `compatibility-quiz.html` | `quiz/index.html` | New file — in the zip | No |
| `cats.html` | `pets/cats/index.html` | Changed | Yes — shared display file (A1/A2/A3 already have zones in this file; this task's zone is additive only) |
| `dogs.html` | `pets/dogs/index.html` | Changed | Yes — same as above |
| `birds.html` | `pets/birds/index.html` | Changed | Yes — same as above |
| `reptiles.html` | `pets/reptiles/index.html` | Changed | Yes — same as above |
| `small-mammals.html` | `pets/small-mammals/index.html` | Changed | Yes — same as above |
| `livestocks.html` | `pets/livestocks/index.html` | Changed | Yes — same as above (pre-existing local comment wording at the insertion point differs slightly from the other 6 files; not introduced by this task) |
| `aquariums.html` | `pets/aquariums/index.html` | Changed | Yes — same as above (same pre-existing comment-wording note as `livestocks.html`) |

No other files were touched. Per Directive 8b (Scope Lock), `pets/detail/index.html` (source `pet-detail.html`) and `pets/index.html` were deliberately left untouched even though a badge there would be a natural future extension — they were not named in this task's File Zone.

## ⚠️⚠️ NEW FOLDERS THE INTEGRATOR MUST CREATE (not documented in either summary doc) ⚠️⚠️

Neither `adoption-website-project-summary.md` (Section 1, Server Folder Structure) nor `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` lists the paths below. Create them under the site root (`public_html/adopt/`) if they do not already exist on the live server:

| New folder to create | Where | Why | File that goes inside |
|---|---|---|---|
| **`quiz/`** | Site root: `public_html/adopt/quiz/` | The Compatibility Quiz page. Folder + `index.html` per Rule 5, so it serves at the clean URL `adopt.petcenterco.com/quiz/`. All 7 category pages link to `/quiz/`. | `quiz/index.html` (**strictly named `index.html`** — source name was `compatibility-quiz.html`; rename is already done inside the zip) |
| **`assets/js/`** | Site root: `public_html/adopt/assets/js/` | Shared scripts folder. The summary documents `/assets/images/` only; an `assets/js/` subfolder is **not documented anywhere**. If `assets/` or `assets/js/` already exists on the live server, just add the file; otherwise create it. | `assets/js/pc-compat-quiz.js` |

Both new files are inside the zip at exactly these paths. The 7 category pages load `/assets/js/pc-compat-quiz.js` and link to `/quiz/`, so **both must be deployed together** with the 7 category-page changes or the badge/entry link will silently do nothing (badge) or 404 (link).

**New files (in `Rehoming-Platform-Task-F2.zip`):**
- `assets/js/pc-compat-quiz.js` — New file — in the zip. Shared scoring/labeling module (no build step on this site, so it's a plain `<script>` attaching globals to `window`, matching every other JS file here).
- `quiz/index.html` — New file — in the zip. The quiz page itself (source file `compatibility-quiz.html`), deployed per Rule 5's folder-path convention so it serves at the clean URL `/quiz/`.

## Code Changes

### `cats.html`  →  live path `pets/cats/index.html`

**1. `<head>` — load the shared scoring module**

<!-- TASK-F2 START -->
**Old code:**
```html
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
```
**New code:**
```html
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- TASK-F2 START — shared Compatibility Quiz scoring module (see compatibility-quiz.html, /quiz/).
       Loaded here so this page can render a match-quality badge on each pet card whenever the
       visitor has taken the quiz this session (sessionStorage-backed, see pc-compat-quiz.js). -->
  <script src="/assets/js/pc-compat-quiz.js"></script>
  <!-- TASK-F2 END -->
  <style>
```
<!-- TASK-F2 END -->

**2. CSS — match-quality badge, appended right after the existing `.pc-pet-card__placeholder` rule**

<!-- TASK-F2 START -->
**Old code:**
```css
.pc-pet-card__placeholder { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; color: var(--muted); }
```
**New code:**
```css
.pc-pet-card__placeholder { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; color: var(--muted); }
/* TASK-F2 START — Compatibility Quiz match-quality badge. Rendered on a
   card only when the visitor has taken the quiz this session (see
   pcQuizMatchBadgeHTML() in pc-compat-quiz.js) — cards render exactly as
   before this task otherwise. Colors follow
   PETCENTER_DESIGN_SYSTEM_v1.0.md Section 18's functional-color logic;
   "Might work" introduces Warning Amber (#f59e0b, the Design System's
   documented warning/caution color) since this file has no local
   equivalent already defined; "Less likely" uses --muted rather than
   --error since a soft, rough-signal mismatch isn't an error state. */
.pc-pet-card__match-badge {
  display: inline-flex; align-items: center;
  font-family: 'Sora', sans-serif; font-size: 11px; font-weight: 700;
  letter-spacing: 0.03em; text-transform: uppercase;
  padding: 5px 12px; border-radius: 999px; margin-top: 10px; width: fit-content;
}
.pc-pet-card__match-badge--good     { background: var(--success-bg); color: var(--success); }
.pc-pet-card__match-badge--maybe    { background: rgba(245,158,11,0.12); color: #f59e0b; }
.pc-pet-card__match-badge--unlikely { background: rgba(122,109,95,0.12); color: var(--muted); }
/* TASK-F2 END */
```
<!-- TASK-F2 END -->

**3. Controls row — "Compatibility Quiz" entry link, right after the Filters button**

<!-- TASK-F2 START -->
**Old code:**
```html
        <span class="pc-filter-btn__label">Filters</span>
      </button>
    </div>
```
**New code:**
```html
        <span class="pc-filter-btn__label">Filters</span>
      </button>
      <!-- TASK-F2 START — Compatibility Quiz entry point. Ghost-styled
           per Section 17's Button Hierarchy (Filters stays the only
           filled/bordered primary-weight action in this row; the quiz
           link is secondary) — links to the new standalone quiz page
           (Task F2's File Zone: "new quiz page/component"), never
           reimplemented inline on this page, per Rule 7. -->
      <a href="/quiz/" class="pc-btn pc-btn--ghost pc-btn--sm">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        Compatibility Quiz
      </a>
      <!-- TASK-F2 END -->
    </div>
```
<!-- TASK-F2 END -->

**4a. Card render — compute the badge**

<!-- TASK-F2 START -->
**Old code:**
```js
      var postedText = formatPostedDate(p.published_date);
      var postedHTML = postedText ? '<div class="pc-pet-card__posted">' + iconPosted + '<span>Posted ' + postedText + '</span></div>' : '';
      // TASK-2 END
```
**New code:**
```js
      var postedText = formatPostedDate(p.published_date);
      var postedHTML = postedText ? '<div class="pc-pet-card__posted">' + iconPosted + '<span>Posted ' + postedText + '</span></div>' : '';
      // TASK-2 END

      // TASK-F2 START — Compatibility Quiz match badge. Returns '' when the
      // visitor hasn't taken the quiz this session (see pc-compat-quiz.js),
      // so cards render identically to before this task until they do.
      var matchBadgeHTML = window.pcQuizMatchBadgeHTML ? window.pcQuizMatchBadgeHTML(p) : '';
      // TASK-F2 END
```
<!-- TASK-F2 END -->

**4b. Card render — insert the badge into the template string, right before the diary link**

<!-- TASK-F2 START -->
**Old code:**
```js
          // TASK-2 START — Posted-on date, rendered only when the pet record has one
          postedHTML +
          // TASK-2 END
          '<span class="pc-pet-card__diary">Read ' + petName + '\'s diary!</span>' +
```
**New code:**
```js
          // TASK-2 START — Posted-on date, rendered only when the pet record has one
          postedHTML +
          // TASK-2 END
          // TASK-F2 START — Compatibility Quiz match badge
          matchBadgeHTML +
          // TASK-F2 END
          '<span class="pc-pet-card__diary">Read ' + petName + '\'s diary!</span>' +
```
<!-- TASK-F2 END -->

---

### `dogs.html`  →  live path `pets/dogs/index.html`
### `birds.html`  →  live path `pets/birds/index.html`
### `reptiles.html`  →  live path `pets/reptiles/index.html`
### `small-mammals.html`  →  live path `pets/small-mammals/index.html`

All four blocks above (1, 2, 3, 4a, 4b) are byte-identical in these four files to the `cats.html` blocks shown, same old/new text, same anchors. Confirmed programmatically: each "Old code" snippet above appears exactly once in each of these four source files as received.

---

### `livestocks.html`  →  live path `pets/livestocks/index.html`
### `aquariums.html`  →  live path `pets/aquariums/index.html`

Blocks 1, 2, 3, and 4a are byte-identical to the `cats.html` blocks above. Block 4b differs only in the pre-existing `// TASK-2 START` comment text one line above `postedHTML +`, which these two files already had before this task (not introduced by this task):

**4b. Card render — insert the badge into the template string, right before the diary link (livestocks.html / aquariums.html variant)**

<!-- TASK-F2 START -->
**Old code:**
```js
          // TASK-2 START — insert Posted-on caption into the card template
          postedHTML +
          // TASK-2 END
          '<span class="pc-pet-card__diary">Read ' + petName + '\'s diary!</span>' +
```
**New code:**
```js
          // TASK-2 START — insert Posted-on caption into the card template
          postedHTML +
          // TASK-2 END
          // TASK-F2 START — Compatibility Quiz match badge
          matchBadgeHTML +
          // TASK-F2 END
          '<span class="pc-pet-card__diary">Read ' + petName + '\'s diary!</span>' +
```
<!-- TASK-F2 END -->

## New Files
- `assets/js/pc-compat-quiz.js` — New file — in the zip. Shared quiz question data, scoring, labeling, and the `pcQuizMatchBadgeHTML()` helper used by the quiz page and all 7 category pages.
- `quiz/index.html` — New file — in the zip. The Compatibility Quiz page itself (source `compatibility-quiz.html`).

## Bugs Found Outside Task Scope
None found. One false alarm worth recording so it isn't re-investigated later: an automated tag-balance check on the original `cats.html` (and its siblings) initially flagged a `<a>`/`</a>` count mismatch (57 open vs. 56 close). Traced to source — it's a false positive from a regex matching the literal text `<a>` inside a plain-English CSS comment at `cats.html` line 225 ("...can't be a single `<a>` the way every other pet card is...", part of Task A2's bonded-pair note), not a real unclosed tag. No actual markup defect exists.

## Pending, Open Items & Doubts (everything that still sits, or that I am not certain about)

1. **PENDING — Task A4 (`showAuthPrompt`) not yet built.** `quiz/index.html`'s "Save My Results" button calls `window.showAuthPrompt(message)` when it exists; otherwise it falls back to a plain `alert()` (clearly commented as temporary). **Integrator action once A4 ships:** add A4's `<script>` tag to `quiz/index.html` and delete the commented `alert()` fallback block. Until then, saving results shows an alert, not the real sign-up/log-in modal.
2. **PENDING — Task A1 `temperament` field not yet on real data.** `pc-compat-quiz.js` reads `temperament` defensively (safe fallback to the existing `personality` field), so scoring works today and improves automatically when A1 data lands. Needs a re-check at the A1 integration checkpoint.
3. **PENDING — Task D1 `rehoming_status` not yet on real data.** Quiz and badge already exclude non-`active` listings via the same forward-compat stub Task A3 uses; a no-op today, activates when D1 ships. Needs a re-check at the D1 checkpoint.
4. **DOUBT — live paths of the 7 category pages.** The delivery guide's mapping says `cats.html` → `pets/cats/index.html`, but the Section 1 folder tree in `adoption-website-project-summary.md` shows them as loose files in `pets/` (`pets/cats.html` …), while Rule 5 and the header links use `/pets/cats/`. I followed the guide's mapping. The integrator should apply the changes to whichever file the repo really holds — the old-code snippets are identical either way.
5. **DOUBT — not tested in a real browser.** No Playwright/browser run was possible in my environment. Verified by syntax checks, markup/CSS balance checks, and a Node scoring harness against the sample `pet.json`. Visual layout of the quiz page and the new badge/button on the 7 category pages has not been seen rendered. Please eyeball `/quiz/` and one category page after deploy.
6. **NOTE — header/footer on the quiz page are a verbatim copy of `cats.html`'s** (Rule 13). If the canonical header/footer changes later, `quiz/index.html` must be updated with it.
7. **NOTE — no nav-menu link to `/quiz/`.** The only entry points are the "Compatibility Quiz" button on the 7 category pages. Adding it to the header menu would touch every page's shared header, which is outside F2's file zone; the integrator/CEO can decide whether to add one.
8. **NOTE — `pet-detail` (`pets/detail/index.html`) and `pets/index.html` show no match badge.** Not in F2's file zone (Scope Lock); a natural future extension.
9. **NOTE — quiz answers are kept in `sessionStorage` only** (per the Access Gate: labels last "for that session"); nothing is saved server-side, so "Save My Results" has no backend yet by design.

## Self-Check Confirmation
- Only the files listed under **Files Changed** were touched: `pc-compat-quiz.js` (new), `compatibility-quiz.html` (new), and additive edits to `cats.html`, `dogs.html`, `birds.html`, `reptiles.html`, `small-mammals.html`, `livestocks.html`, `aquariums.html`. `pet-detail.html`, `pets/index.html`, and every admin-dashboard file were left untouched per Directive 8b, even though nothing about this task required changing them.
- Every code block added to a shared/existing file carries a `TASK-F2 START`/`TASK-F2 END` anchor comment (HTML-comment form in markup, `/* */` or `//` form in CSS/JS as appropriate).
- Every "Old code" snippet above was copied character-for-character from the original source files as received (`received-files/`, not my own edited output) and verified programmatically to appear exactly once in its file before this task's changes.
- Every file in `Rehoming-Platform-Task-F2.zip` (`assets/js/pc-compat-quiz.js`, `quiz/index.html`) is listed above as "New file", at the same path; `unzip -l` confirms the zip contains exactly these two files plus their parent directory entries, rooted at the site root, with no wrapper folder.
- Confirmed nothing in the zip is a secret, `config.php`, `pet.json`, `.env`, or anything under `applications/` or `uploads/` — the zip holds only the two new files named above.
- **No dropdown menus in this task** — the 5 quiz questions use single-select "answer card" buttons (an enlarged `.pc-chip` variant), not a `<select>` or any dropdown component, so Directive 9a's modern-dropdown-component requirement doesn't apply here.
- Verified: inline JS in all 7 patched files and in `quiz/index.html` parses without a syntax error (`new Function(...)` on every extracted `<script>` block); CSS brace-balance checked (0 net) in all 8 files; per-file patch-point counts (5 per category file) confirmed exactly 1 match each, no double-application.
- `showAuthPrompt()` is called, not reimplemented — per the VDM's note, Task A4's real component is expected to be wired in later; this file's fallback is explicitly commented as temporary and named for removal at that integration checkpoint.
- Field-sourcing verified against the actual uploaded `pet.json` sample: `category`, `personality`, `size`, `compatible_kids`/`compatible_dogs`/`compatible_cats`/`compatible_other_pets`, `adoption_fee`, `single_image_url`, and `status` are all real, existing fields used as-is; `temperament` (Task A1's contract field, not yet real on disk) is read defensively with a safe fallback, matching the same stub pattern this codebase already uses elsewhere for other A1/A2/D1 contract fields (e.g. `p.is_urgent`, `p.rehoming_status` already present in `applyAndRender()`).
- **Integration checkpoint (pending):** once Task A4's completion file exists and its shared component's `<script>` tag is added to the 8 files this task touches, the `showAuthPrompt` fallback block in `quiz/index.html` should be deleted and this noted as a new verification-round addition to this file, per Directive 8c — not done yet since A4 has not shipped.

## SECOND VERIFICATION ROUND

**Trigger:** live testing (screenshot from the CEO) surfaced an uncaught, page-breaking error on the quiz page: `Uncaught ReferenceError: PC_QUIZ_QUESTIONS is not defined`, thrown the moment the quiz controller ran.

**Root cause:** the quiz page loads its scoring module via `<script src="/assets/js/pc-compat-quiz.js">` — an absolute, root-relative path per Rule 5's folder-path deployment convention. That path only resolves once the page is actually served from the real site root. In the CEO's test, the file was previewed in isolation (no site root behind it), so the request 404'd. A failed `<script src>` load throws no catchable error of its own — it simply never defines `PC_QUIZ_QUESTIONS` or the module's other globals — so the first thing the quiz controller did (render the first question) hit an unguarded reference to `PC_QUIZ_QUESTIONS` and threw, leaving a dead page.

**Why this was worth fixing (not just a preview artifact):** even in real production, an uncaught reference error from a missing external dependency is a bad failure mode regardless of *why* the script failed to load (a bad deploy, a CDN hiccup, a path typo introduced elsewhere later) — it should degrade to a clear, user-facing message, not a silent crash with no on-page indication anything went wrong.

**Fix (scoped to `quiz/index.html` only — a new file, so the fixed version simply replaces it in the zip):** added a `TASK-F2 START/END`-wrapped dependency guard at the top of the quiz controller IIFE. It checks that `PC_QUIZ_QUESTIONS` and the three scoring functions (`pcSaveQuizAnswers`, `pcScoreListing`, `pcMatchLabel`) actually exist before doing anything else; if any are missing, it logs a specific `console.error` naming the real cause (so a future debugger doesn't have to rediscover it) and replaces the quiz card's contents with the same friendly `.pc-empty-state` pattern already used elsewhere on this page for the zero-results case (Section 15.3's convention: no raw technical error in user-facing markup), then returns before any of the crash-prone code runs. Since this file is new (not an existing file being merged), the guard is simply part of the current `quiz/index.html` in the zip — no old/new snippet needed here.

**Verification this round (no Playwright available in this environment, so verified what could be verified directly):**
- Confirmed the 7 category pages were never at risk of this same crash: their badge call site was already written defensively (`window.pcQuizMatchBadgeHTML ? window.pcQuizMatchBadgeHTML(p) : ''`), so a missing module there just means no badge renders, not a crash. No change needed to those 7 files this round.
- Re-ran the inline-JS syntax check (`new Function(...)` on the extracted `<script>` block) and the CSS brace-balance / `<div>` tag-balance checks against the updated file — all pass.
- Actually exercised the scoring engine logic (not just syntax) against the real uploaded `pet.json` sample (Kira, a Bird) through a small Node harness, across 4 realistic answer scenarios plus one hard-constraint test:
  - A strong-fit scenario scored 15 ("Good match").
  - The same pet with the visitor having kids (pet's `compatible_kids: "yes"`) scored 18 ("Good match") — confirms the positive bonus fires.
  - A mismatched scenario (first-time owner, apartment, expects low energy, against this high-energy bird) scored 6 ("Less likely") — confirms energy-mismatch and first-time-vs-high-energy penalties both fire correctly.
  - Forcing `compatible_kids` to `"no"` on the same pet with a visitor who has kids dropped the score from 18 to 9, crossing from "Good match" down to "Might work" — confirms the hard-constraint sharp-drop behavior specified in the task doc is real and working, not just present in comments.
  - Confirmed `pcQuizMatchBadgeHTML()` returns the correct badge markup when quiz answers exist in (stubbed) `sessionStorage`, and an empty string when they don't — the exact no-op behavior the 7 category pages depend on.
- Did **not** need to touch `pc-compat-quiz.js` itself or any of the 7 category pages this round — the bug and its fix were fully contained to `quiz/index.html`, so Scope Lock (Directive 8b) holds without expansion.

**Files touched this round:** `quiz/index.html` only (new file — already in the zip, replaced in place).

## THIRD VERIFICATION ROUND

**Trigger:** CEO instruction to re-read `adoption-website-project-summary.md` and `Rehoming-Platform-Task-Assignments.md` in full, re-read Task F2, and hunt for minor/hidden bugs, missing work, and missing file requirements. `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` was reviewed for relevance only: Task F2 is website-only work (Directive 14), and nothing in the admin dashboard is touched by or feeds this task, so no change resulted from it.

**Result:** 5 defects found in my own earlier work and fixed. All were in the two new files (`pc-compat-quiz.js`, `quiz/index.html`), so the fixes are simply the current versions of those two files in the zip. The 7 category pages needed no change, because the badge fixes live in the shared helper. Scope Lock (Directive 8b) holds.

### Fix 1: Energy keywords were not the real Section 13.5 lists (Rule 7) — `pc-compat-quiz.js`
```js
var PC_HIGH_ENERGY_WORDS = ['playful', 'energetic', 'active', 'athletic', 'high-energy'];
var PC_LOW_ENERGY_WORDS  = ['gentle', 'calm', 'quiet', 'shy', 'timid', 'reserved'];
```
These are copied verbatim from the 'Adventure Buddy' / 'Gentle Soul' archetypes in Section 13.5 (replacing an earlier draft that had invented words not in the source taxonomy). `pcDeriveEnergyLevel` now also accepts `personality` as either a JSON array (the real `pet.json` shape) or a comma-separated string (the shape Section 13.5 describes).

### Fix 2: Empty-state copy was a paraphrase (Section 15.3) — `quiz/index.html`
Replaced an earlier paraphrase ("No pets to match against right now") with the exact mandated copy: "All our pets here have found their homes" / "Please check back soon — new arrivals are added often."

### Fix 3: Empty-state icon was hand-drawn (Directive 11) — `quiz/index.html`
Replaced an earlier improvised magnifier SVG with the official Font Awesome Solid `paw` path (`viewBox="0 0 512 512"`, `fill="currentColor"`), checked against `@fortawesome/free-solid-svg-icons` and identical to the path already used in `cats.html`. The universal paw suits this page because its empty state spans all 7 categories.

### Fix 4: Quiz page ignored the D1 forward-compat stub (parity with Task A3) — `quiz/index.html`, `fetchCategoryPets()`
```js
return (pets || []).filter(function (p) {
  if (!p) return false;
  if (p.status === 'Adopted') return false;
  if (p.rehoming_status && p.rehoming_status !== 'active') return false;
  return true;
});
```
(Replacing an earlier version that only checked `p.status !== 'Adopted'`.) `rehoming_status` is a no-op today and activates automatically when Task D1 ships. It matches the pattern Task A3 already uses in `applyAndRender()`.

### Fix 5: "Good match" badge could appear beside an "ADOPTED" badge — `pc-compat-quiz.js`, `pcQuizMatchBadgeHTML()`
```js
if (!answers) return '';
if (pet.status === 'Adopted') return '';
if (pet.rehoming_status && pet.rehoming_status !== 'active') return '';
```
One central fix covers the quiz page and all 7 category pages.

### Verification this round
- Syntax check (`new Function`) on `pc-compat-quiz.js` and every inline script in `quiz/index.html`: pass. CSS brace balance and `<div>` balance: pass.
- Node harness against the real `pet.json` sample (Kira, Bird), 6 scenarios, all as expected: active strong fit gives "Good match"; Adopted gives no badge; `pending_placement` gives no badge; explicit `active` still gives a badge; no quiz taken gives no badge; mismatched answers give "Less likely".

### File-request completeness check
Files received (7 category pages, `pet.json` sample, both project summaries, design system, task tracker) were sufficient. Nothing further is needed to make F2 complete:
- `pet-detail.html` / `pets/index.html`: not in F2's File Zone, and the quiz needs nothing from them.
- Task A4 (`showAuthPrompt`): not yet shipped. This is the one pending integration checkpoint, already recorded above.
- Task A1 (`temperament`) and D1 (`rehoming_status`): consumed via defensive stubs per SHARED CONTRACTS, to be reconciled at their checkpoints.
- Deployment note, not a missing file: this is why `quiz/index.html` and `assets/js/pc-compat-quiz.js` are shipped in `Rehoming-Platform-Task-F2.zip` at those exact live paths, so the integrator places them correctly.

### Bugs Found Outside Task Scope (this round)
None.

## FOURTH ROUND — Hand-in format update

**Trigger:** the CEO's delivery process changed from git/branches to a completion-file + zip hand-in (`WORKER-DELIVERY-GUIDE.md`), then was clarified: the completion file and all new files travel together in ONE zip, named `[Project-Name]-Task-[id].zip`. No code was changed in this round.

- Zip name: **`Rehoming-Platform-Task-F2.zip`** (project name taken from the project file `Rehoming-Platform-Task-Assignments.md`; task number `F2`).
- Zip contents (rooted at the site root): `Task-F2-Completion.md` (this file, at the zip root), `assets/js/pc-compat-quiz.js`, `quiz/index.html`. The completion file is documentation for the integrator and is not a site file; do not deploy it.
- Files Changed table has four columns with live paths; new files are listed as "New file", their code is not pasted here; the 7 changed category pages stay here as old/new code (never in the zip).
- Every "Old code" snippet was re-verified character-for-character against the originals as received and appears exactly once in each file.
- Added the **New Folders** section (`quiz/`, `assets/js/` are not in either summary doc) and the **Pending, Open Items & Doubts** section.
- Zip checked: no `config.php`, `pet.json`, `.env`, nothing under `applications/` or `uploads/`.
