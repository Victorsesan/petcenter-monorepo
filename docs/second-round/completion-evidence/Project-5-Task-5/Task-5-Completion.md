# Task-5-Completion.md

## Task Number
**Task 5 — Naming, Visitor Form & Category CTAs** (from `WEB-PROJECTS-3-4-5-TASK-ASSIGNMENTS.md`, Project 5: Pet-Match Request feature)

## What Was Required
Decide final terminology for the placeholder terms "pet request" / "requested pet" / "requested applications" across the whole feature, then build a new public-facing page where a visitor who can't find what they want in the listings — or finds the listings too expensive — can describe the exact pet they're looking for. The form needed to mirror the same attribute fields as `pet-detail.html`/`pet.json` (in reverse: describing a wanted pet rather than an available one), include a free-text detail area, and include an *optional* reference-image upload reusing the site's existing file-upload/preview feature verbatim, with original professional copy covering that the image is optional, must be owned/rights-cleared, only improves matching accuracy, and is gated behind a required agreement checkbox. A CTA linking to this new page also had to be added to all seven pet category pages, using the terminology decided at the top of the task. The notification-preference radio buttons that will live on this same form belong to Task 7a, not this task — Task 5 only needed to leave a stable hook point for that section without restructuring the page.

## Terminology Decided
| Placeholder term | Final term |
|---|---|
| "pet request" | **Pet Match Request** |
| "requested pet" | **Requested Pet** |
| "requested applications" (admin-side) | **Pet Match Requests** |
| CTA button label | **"Can't Find Your Match? Request One"** |
| New page URL | **`/pet-match/`** |

## Files Changed

| Source name | Live path | Changed or new | Shared with another task? |
|---|---|---|---|
| `pet-match.html` | `pet-match/index.html` | **New** | Yes — FORM-CORE zone (this task). Also touched directly by Task 7a (NOTIFICATION-PREFS, inside `#notifyPreferenceHook`), and depended on by Task 9 (SUBMISSION-CHECK) and Task 5b (FORM-HARDENING, layered into this same file) |
| `cats.html` | `pets/cats/index.html` | Changed | Yes — CTA zone (this task). Also touched by Task 3's separate FILTER zone, and by Task 7a's own later edits to this same file (notification stub/mobile menu — outside this task's zone) |
| `dogs.html` | `pets/dogs/index.html` | Changed | Yes — same as above (Task 3's FILTER zone) |
| `birds.html` | `pets/birds/index.html` | Changed | Yes — same as above |
| `reptiles.html` | `pets/reptiles/index.html` | Changed | Yes — same as above |
| `small-mammals.html` | `pets/small-mammals/index.html` | Changed | Yes — same as above |
| `livestocks.html` | `pets/livestocks/index.html` | Changed | Yes — same as above |
| `aquariums.html` | `pets/aquariums/index.html` | Changed | Yes — same as above |

### ⚠️ New folder path — not documented in either project summary
`pet-match/` is a **brand-new top-level folder at the site root**, holding this task's one new file as `pet-match/index.html` (per the project's own strict rule that every HTML page is served as `index.html` inside its own folder path, never a bare filename). Neither `adoption-website-project-summary.md` nor `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` documents this folder anywhere, since the whole Pet Match Request feature post-dates both documents. It should be created as a **sibling of the site's existing top-level folders** — `pets/`, `blog/`, `adoptions/`, `apply/`, `signin/`, `services/`, `testimonials/`, `contact-us/`, `admin/`, `assets/` — i.e. directly under the site root, not nested inside any of those. The CTA links added to all seven category pages below already point to `/pet-match/`, so this folder must exist at that exact path for those links to resolve.

## Code Changes

### `pet-match.html` — New file
Per the delivery guide's rule for brand-new files: this file is **in the zip**, at its live path `pet-match/index.html`, and its code is not pasted here. See the zip manifest in the Self-Check section below.

For context on what it actually contains (not a substitute for opening the file): a 7-step wizard — category + contact info, pet-name/gender preference, breed (via the site's Custom Dropdown System, real per-category species taxonomy), size/age range, budget/personality/household compatibility, location/free-text detail (Rich Text Editor) + the empty `#notifyPreferenceHook` container for Task 7a, and an optional reference-image upload + required terms checkbox + Task 5b's hardening fields. Full history of how this file reached its current state is kept below under Revision Notes 1–4 and the Full Self-Audit, since that context remains useful even though it's no longer the primary hand-in format.

### `cats.html`, `dogs.html`, `birds.html`, `reptiles.html`, `small-mammals.html`, `livestocks.html`, `aquariums.html` — Changed
Identical single-line change applied to all seven files: the existing Signin button (added under Task 1b) is now wrapped in a flex container alongside the new Pet Match Request CTA, positioned immediately to its left. Verified this exact block still exists, unchanged, exactly once in each of the seven files as of this hand-in.

```html
<!-- OLD (all 7 files) -->
      <a href="/signin/" class="pc-btn pc-btn--signin">Signin</a>

<!-- NEW (all 7 files) -->
      <!-- TASK-1 START -->
      <!-- TASK 1b: "List Out A Pet" renamed to "Signin" per Dev Manager Task 1 assignment, restyled to match the site's existing Signin button look (.pc-btn--signin, gold border/text); link target updated from /rehome/ to /signin/ per CEO's request to retire the "rehome" URL sitewide -->
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <!-- TASK-5 START — Pet Match Request CTA, added to all 7 category pages per Task 5. Terminology ("Pet Match Request") finalized in Task-5-Completion.md. Points to the new /pet-match/ page built in this same task. -->
        <a href="/pet-match/" class="pc-btn pc-btn--orange pc-btn--sm">Can't Find Your Match? Request One</a>
        <!-- TASK-5 END -->
        <a href="/signin/" class="pc-btn pc-btn--signin">Signin</a>
      </div>
      <!-- TASK-1 END -->
```

## New files
| File | Live path |
|---|---|
| `pet-match.html` | `pet-match/index.html` |

## Revision History (supplementary context, kept from prior hand-in format)

The sections below are retained from this file's history before the hand-in format changed. They're no longer the primary record of what's in the file — the Files Changed / Code Changes sections above are — but they document real decisions and fixes worth keeping visible rather than deleting.

### Revision Note 1 — Post-Submission Rebuild
`pet-match.html` was rebuilt from a single long scrolling form into the current 7-step wizard after the CEO supplied 9 reference screenshots of a comparable app's flow, and flagged that the original submission had used a native `<select>` instead of the site's documented Custom Dropdown System ("Message Bubble" shell, Design System Section 24). The rebuild also replaced `age_preference` with a true `age_min_years`/`age_max_years` range and added `pet_name_preference`, `breed_purebred`, `breed_crossbred`, and `pedigree_certified` — none of which existed in the very first submission. The Custom Dropdown System was implemented per Section 24, Variant A (Searchable Single-Select), with the species list swapping per category.

### Revision Note 2 — Real Species Taxonomy + Rich Text Editor
`merchandise.html` was supplied as the canonical source for the site's real pet-type/species taxonomy. Reptiles, Small Mammals, and Livestocks (categories covering many genuinely different animals under one heading) were switched from an invented placeholder list to the real per-category species lists `merchandise.html` itself uses. The "Tell us more" field was converted from a plain textarea to a Rich Text Editor (Quill.js, Design System Section 25's 13-control Phase-1 toolbar), since it's a genuine paragraph-length narrative field, the exact case that component is scoped to.

### Revision Note 3 — Field List Correction
Task 6a's worker found that `get-pet-match-requests.php` had been built against this file's originally-documented payload, which had gone stale after Revisions 1–2 changed the live field names without a matching doc update. Reissued the full field list here, pulled directly from the live `collectPayload()` function rather than from memory: `age_preference` → `age_min_years`/`age_max_years`; `reference_image_url` → `reference_image_token` (this one was wrong from the very first draft, not just outdated); `additional_detail` is Quill-generated HTML, not plain text; `pet_name_preference`/`breed_purebred`/`breed_crossbred`/`pedigree_certified` are new fields with no equivalent in the original submission.

### Revision Note 4 — Real Icons + Real Breed Data from Task 8, Dropdown Clipping Fix
Task 8's `quiz.html` was flagged as a close sibling of this form with genuinely well-sourced assets worth adopting: 7 professional SVG category icons and a real, 273-entry species/breed taxonomy. Both were extracted programmatically from `quiz.html` and inserted byte-for-byte (verified count: 107 dogs / 47 cats / 20 birds / 26 reptiles / 20 small mammals / 31 livestocks / 22 aquariums = 273, matching Task 8's own completion doc exactly), replacing this file's earlier hand-built icons and shorter invented lists. These icons use `fill="#000000"` hardcoded rather than `currentColor`, so recoloring required Task 8's own CSS-filter technique rather than editing the SVGs themselves, scoped to a new `.pm-card-grid--species` class so it doesn't affect the separate `currentColor`-based gender icon cards. While porting this in, found and fixed a real dropdown-clipping bug: this wizard's own scroll container (`overflow-y: auto`) and rounded-corner container (`overflow: hidden` at desktop widths) would have silently clipped the breed dropdown's lower rows now that a 107-entry list exists — ported Task 8's own fix (`position: fixed` panel with JS-driven positioning).

### Full Self-Audit (2026-10-03)
Asked to slow down and re-read both project summary documents, this task's own text, and the actual delivered file in full, specifically warned that minor details can hide real bugs. Found and fixed 5 genuine issues:
1. **No site header/footer/hamburger at all** — silently dropped during the Revision 1 wizard rebuild, with only a self-generated (not task-grounded) justification in my own notes at the time. Confirmed as a real violation via three independent reinforcements in `adoption-website-project-summary.md` (Sections 13.9, 18, 20 — Section 20 describes this exact failure mode by name as a previously-caught issue on this project). Restored in full, verbatim from `cats.html`, including the matching CSS/JS and `body`'s `padding-top`.
2. While restoring the footer, found `cats.html`'s own `.pc-footer__top` CSS ships a 4-value `grid-template-columns` against 5 real child columns — the exact bug `adoption-website-project-summary.md` Section 8 names as recurring. Fixed in `pet-match.html`'s own copy of this CSS; `cats.html`'s own copy of the bug is logged below, not fixed there (outside this task's edit zone).
3. Duplicate SVG wrapper-group IDs across the real category icons from Revision 4 (genuine raw-export artifacts, confirmed never referenced via `url()`/`href` so zero actual rendering risk, but still invalid HTML) — namespaced the colliding wrapper IDs only, left all real path/shape data untouched.
4. Found the file still had my own unverified stand-in reconstruction of Task 7a's notification-preference section rather than their actual corrected version. Re-applied their three documented corrections: moved the radios inside `#notifyPreferenceHook` (they were sitting just before it), restored the dedicated `.pm-notify-opt` CSS/aria attributes, and corrected the stub-email `localStorage` key to the real `pc_stub_petmatch_email` (confirmed by Task 7a against `cats.html`'s own `STUB_EMAIL_KEY` constant) in place of my earlier guessed `pc_pet_match_email`.
5. The Rich Text Editor field's real submitted value lived in a plain `<input type="hidden">` — functionally correct, but not matching Section 25/5.10's documented `<textarea class="rte-source">` convention. Converted to match; no JS changes needed since `.value` works identically on both tag types.

## Bugs Found Outside Task Scope
1. `livestocks.html` and `aquariums.html` are both missing the `<!-- TASK-1 START -->` opening anchor comment on the Signin button block (the closing `<!-- TASK-1 END -->` is present in both, and the opening comment exists identically in `cats.html`, `dogs.html`, `birds.html`, `reptiles.html`, and `small-mammals.html`). Not fixed, per Scope Lock.
2. `pet-detail.html` (read for reference only, not edited) still links to `/rehome/` in at least one spot rather than `/signin/`, predating the Task 1b fix already live elsewhere. Not fixed, since `pet-detail.html` is not a file this task touches.
3. `cats.html`'s own `.pc-footer__top` CSS ships a 4-value `grid-template-columns` against its real 5-column footer markup — the bug `adoption-website-project-summary.md` Section 8 names as recurring (see Full Self-Audit item 2 above). **Checked again for this hand-in and still present** in the copy of `cats.html` I have. Note: Task 7a's own records describe having fixed this exact bug directly in `cats.html` during their own session (they were editing that file too, for the notification stub). I can't confirm that fix in the copy in front of me — the two copies of `cats.html` appear to have diverged across separate sessions, the same cross-session gap that's come up before on this project (e.g. Task 7a's notification-key fix not reaching my own copy of `pet-match.html` until I re-applied it manually). Not fixed here either way, since `cats.html`'s general CSS is outside this task's own edit scope.
4. **Pending, shared with Task 5b and Task 6a/9: `/pet-match-submit.php` and `/pet-match-upload-temp.php` still do not exist as real files anywhere in this project, as far as I can confirm.** `pet-match.html`'s own JS calls `fetch()` against both of these paths (form submission and the optional reference-image upload), and Task 6a built `get-pet-match-requests.php` to read whatever the submit endpoint is supposed to write — but the endpoint that would actually write that data was never built by this task, Task 5b, or (as far as my own records show) any task since. Without it, the live form cannot actually complete a real submission. This was flagged as a known, deliberate gap when Task 5b's hardening spec was written (rate limiting and the honeypot/timestamp/Turnstile checks are all things this still-missing endpoint would need to enforce), but it's worth restating plainly here since the hand-in format change is a natural point to confirm nothing's fallen through a crack: **this is still open, and someone needs to build it.**

## Self-Check Confirmation
I only modified the 8 files listed under Files Changed above (`pet-match.html`, created new, now delivered in the zip; the 7 category pages' CTA zone, documented above as old/new code). Every code block added to the 7 category pages is wrapped in a task-numbered `<!-- TASK-5 START -->` / `<!-- TASK-5 END -->` anchor comment. The new `/pet-match/` folder path is heavily flagged above since it exists in neither project summary document. The zip for this task (`Project-5-Task-5.zip`) contains `pet-match/index.html` and this completion file, and was scanned before delivery to confirm it holds no secrets, `.env`, `config.php`, `pet.json`, or any `applications/`/`uploads/` content. No file outside this list was touched.
