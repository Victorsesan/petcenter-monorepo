# Task 8 — Completion File

## Task Number
Task 8 — Project 3: All-Category Breed/Species Match Quiz

## What Was Required
Build a new quiz page that spans all seven of Pet Center's pet categories
(dogs, cats, birds, reptiles, small mammals, livestocks, aquariums) — unlike
Petfinder's dogs-only version of this feature. The quiz asks a short series
of questions and narrows down to a shortlist of currently-listed pets
matching the visitor's answers. It must use `cats.html`'s header, footer,
and hamburger menu as the canonical blueprint (Rule 13), follow
`PETCENTER_DESIGN_SYSTEM_v1.0.md` for layout/styling, query the same live
pet data the category pages already use (no separate or stale copy), and
wire into the navigation per Task 1's plan — following the Sequential File
Ownership Chain (Rule 12) since the nav lives in a shared file.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `quiz.html` | ⚠️ `pets/quiz/index.html` — **not in the Section 3 mapping table or in `adoption-website-project-summary.md`'s folder-structure tree; this is a brand-new folder.** `pets/` currently only documents `index.html`, `detail/`, and the 7 category folders — `quiz/` does not exist yet anywhere in that tree. Please create the folder `pets/quiz/` and place this file inside it as `index.html`, matching the same pattern every other page in `pets/` already uses (a named folder containing `index.html`, never a loose `name.html`). Flagging this heavily per instruction, since it's a path the integrator won't find already listed anywhere. | New | No — this file itself is exclusive to Task 8 |
| `cats.html` | `pets/cats/index.html` | Changed (2 small additions only, inside my named zone) | **Yes** — `cats.html` is in the shared `NAV-LINKS` zone chain: Task 1 (plan) → Task 2 (builds the plan into nav) → **Task 8** (extends Task 2's zone). See "Bugs Found Outside Task Scope" below for why this was applied only to `cats.html` and not the other 7 nav-carrying files Task 2 would normally have already updated. |

## Code Changes

### `cats.html`  →  live path `pets/cats/index.html`

Two small additions, each wrapped in `<!-- TASK-8 START -->` / `<!-- TASK-8 END -->`
anchor comments, adding a "Find Your Match" link pointing at `/pets/quiz/` to both the
desktop "Our Pets" dropdown and the mobile hamburger menu, directly below the existing
7 category links in both places — matching the same pattern the existing
`<!-- TASK-2 START -->` Livestocks/Aquariums additions immediately above each of mine
already use. Both snippets below were copied character-for-character from the original
`cats.html` as supplied, and each appears exactly once in that file.

**Change A — desktop "Our Pets" dropdown**

<!-- TASK-8 START -->
**Old code:**
```html
        <!-- TASK-2 START — Livestocks & Aquariums added to Our Pets dropdown -->
        <li><a href="/pets/livestocks/">Livestocks</a></li>
        <li><a href="/pets/aquariums/">Aquariums</a></li>
        <!-- TASK-2 END -->
      </ul>
    </li>
```
**New code:**
```html
        <!-- TASK-2 START — Livestocks & Aquariums added to Our Pets dropdown -->
        <li><a href="/pets/livestocks/">Livestocks</a></li>
        <li><a href="/pets/aquariums/">Aquariums</a></li>
        <!-- TASK-2 END -->
        <!-- TASK-8 START -->
        <li><a href="/pets/quiz/">Find Your Match</a></li>
        <!-- TASK-8 END -->
      </ul>
    </li>
```
<!-- TASK-8 END -->

**Change B — mobile hamburger menu**

<!-- TASK-8 START -->
**Old code:**
```html
    <!-- TASK-2 START — Livestocks & Aquariums added to mobile menu -->
    <a href="/pets/livestocks/">Livestocks</a>
    <a href="/pets/aquariums/">Aquariums</a>
    <!-- TASK-2 END -->
    <a href="/blog/">Blog</a>
```
**New code:**
```html
    <!-- TASK-2 START — Livestocks & Aquariums added to mobile menu -->
    <a href="/pets/livestocks/">Livestocks</a>
    <a href="/pets/aquariums/">Aquariums</a>
    <!-- TASK-2 END -->
    <!-- TASK-8 START -->
    <a href="/pets/quiz/">Find Your Match</a>
    <!-- TASK-8 END -->
    <a href="/blog/">Blog</a>
```
<!-- TASK-8 END -->

## New Files

- `pets/quiz/index.html` — New file — in the zip. The quiz page itself: a 9-step wizard
  (Category → Living Situation → Breed → Size → Age → Activity → Experience/Senior →
  Household → Budget) that queries live pet data across all 7 categories via
  `/adoptions/pets/{category}/index.json` + per-pet `pet.json`, the same pattern
  `cats.html` itself already uses for its one category. Full build detail (matching
  model, icon sourcing, breed data sourcing, every verification step) is preserved below
  under "Build Detail — `pets/quiz/index.html`" since none of it is a code-diff the
  integrator needs, but it documents what's inside the new file and should stay attached
  to this task's record.

## Bugs Found Outside Task Scope

**Task 2's completion file was not available in this session — nav wiring applied to
`cats.html` only, not the full shared nav surface.** Per Task 8's assignment and the
Shared File Coordination table, Task 8's `NAV-LINKS` zone extends Task 2's zone and runs
after Task 2 completes; Task 8 is supposed to pull the most recently submitted version of
the shared nav file(s). Task 2's completion file (which would show Task 1's full page
list already wired into every page's nav) was never provided in this session — only
`cats.html` as originally uploaded was available, with no Task 2 anchor comments present
in any other category page. I did not attempt to guess at or fabricate what Task 2's nav
changes would have looked like on those other pages, and did not touch `dogs.html`,
`birds.html`, `reptiles.html`, `small-mammals.html`, `livestocks.html`, `aquariums.html`,
`pets/index.html`, or any other page carrying the same nav — those are outside what was
provided to me. **Once Task 2's actual completed files are available, the same two-line
"Find Your Match" addition documented above needs to be replicated into every other
page's copy of the nav** (all of which should be identical to `cats.html`'s, per Section
2/2.6/Rule 13), not just `cats.html`.

**`pets/quiz/` is a new folder not yet reflected in `adoption-website-project-summary.md`.**
See the flag in Files Changed above — once this is integrated, that summary doc's
folder-structure tree (Section 1) should be updated to list `pets/quiz/` alongside
`pets/index.html` and `pets/detail/`, so the next worker who reads it doesn't hit the
same "not in the table" gap I did.

**Saltwater/marine aquarium species are not covered by the quiz's breed dropdown.** Not a
bug so much as a scope boundary worth flagging: `pet.json` has no field distinguishing a
freshwater tank from a saltwater/reef one, so the Aquariums breed list in the quiz only
covers freshwater species. If the platform does list marine fish or reef livestock, that
list should be extended once there's a real field to gate it on — adding marine species
now, with nothing to distinguish them from freshwater ones in the underlying data, risked
implying a level of tank-type support the site doesn't actually have.

No other bugs or issues were found outside this task's scope.

## Self-Check Confirmation

Only the two files listed under "Files Changed" above were touched: `cats.html` (two
small nav-only additions inside my named `NAV-LINKS` zone) and `pets/quiz/index.html`
(new file, in the zip). No other file was created or modified. Both blocks in `cats.html`
are wrapped in `TASK-8 START`/`END` anchor comments; every "Old code" snippet above was
copied character-for-character from the original `cats.html` as supplied and verified
(by exact string search) to appear exactly once in that file, so the integrator's
text-match will land in the right and only place. `pets/quiz/index.html` in the zip
matches the file tested below exactly — same file, not a re-export.

`quiz.html` passed structural lint on every revision (balanced tags, balanced
braces/parens in its `<style>` and main `<script>`, zero duplicate `id`s outside inert
metadata ids belonging to the source SVG icon files, which are never referenced by any
selector or script on the page) and was verified with a real, headless-Chromium
Playwright session throughout (network access for `playwright install` was unavailable
in this sandbox, so an existing cached Chrome binary was located and used directly as a
real-browser substitute, noted here rather than presenting untested work as verified):

- Full 9-step flow driven end-to-end against stubbed `index.json`/`pet.json` responses
  matching the real schema — reached the results view with zero console/page errors,
  correct step count and labels at every step, and the progress bar filling correctly.
- Category, breed, size, age, budget, and household hard filters verified as genuine
  exclusions (a combination that doesn't exist in the stub data correctly returned zero
  results; a genuinely matching combination correctly returned exactly the matching stub
  pet).
- Soft-signal scoring (living situation, activity/temperament, experience level,
  openness to a senior pet) verified by hand-checking the math against a known stub
  pet's real fields, confirming the resulting "*n*/*m* Matches" ribbon matched that
  arithmetic exactly.
- Breed dropdown verified to render the correct, real per-category list at every length
  (19 entries for birds up to 108 including "Any breed" for dogs), to correctly filter
  as text is typed into its search field, and to show every row including the true last
  one, on both a small mobile viewport and desktop, after a real clipping bug (the
  panel's lower rows being cut off by a parent card's `overflow: hidden`) was caught and
  fixed by switching the panel to `position: fixed` with JS-computed coordinates.
- Icon rendering verified in both states (unselected and selected) for all 10
  quiz-specific icons, confirming mono icons correctly invert to white on Navy and the 4
  multi-color icons correctly keep their real palette rather than flattening to a single
  tone.
- Recap chips verified to show exactly the questions actually answered, in the
  visitor's own words, and to jump back to the correct step on click.
- No-match state verified against a genuinely impossible combination: confirmed it lists
  "Loosen" suggestions only for the specific hard-filter questions actually answered, and
  confirmed the "Request a Pet Match" link to `/pet-match/` is present and correctly
  targeted.
- Fetch-failure path verified — simulated 404s on every category's `index.json`; no
  internal folder path or raw HTTP status reaches the visible DOM.
- Mobile viewport re-verified at the full 9-step length — hamburger menu, progress bar,
  icon-card grids (3-column desktop, 2-column mobile), and the breed dropdown all render
  and function correctly.

---

## Build Detail — `pets/quiz/index.html`

Kept here for reference since this is a new file (no diff for the integrator to apply),
but documents what's actually inside it.

**The 9 steps, in order, and what each maps to:**

1. **Category** — icon cards, one per species, using real hand-built per-species icons
   (dog, cat, bird, reptile, small-mammal, livestock, aquarium). Hard filter.
2. **Living situation** (apartment / house with a yard / rural-farm / no preference) —
   icon cards. Soft signal against `size`/`category`. Not itself a `pet.json` field, but
   a real practical question any visitor would expect before committing to a pet.
3. **Breed** — a searchable single-select using the site's locked "Message Bubble"
   dropdown component (Design System Section 24), with real, comprehensive per-category
   breed/species lists (273 entries total across all 7 categories, sourced from the
   AKC's official breed list, the CFA's breed roster and popularity ranking, ARBA rabbit
   breeds, real pet bird species groupings, popular named chicken/goat breeds, and real
   popular freshwater aquarium species — not invented).
4. **Size** — Small/Medium/Large/no preference. Hard filter, maps directly to
   `pet.json`'s `size` field and the category pages' own Size filter.
5. **Age range** — separate min/max year fields. Hard filter on `age_years`.
6. **Activity level / temperament** (playful & energetic / balanced / calm & low-key /
   no preference) — icon cards, 3 using color source icons (lightning bolt, balance
   scale, moon) plus a distinct energy-level tick icon for "no preference". Soft signal,
   scored against each pet's real `personality` array using the same trait-grouping
   vocabulary as the site's own Vibe Badge archetypes.
7. **Experience level** (first-time / some experience / very experienced / no
   preference), plus a second question on this same step asking whether the visitor
   would consider a senior pet (8+ years, reusing the site's own already-established
   senior age bracket rather than a new field). Experience is scored against real
   "requires an experienced owner"-style language already present in listings'
   `description_full`/`health_info` text.
8. **Household compatibility** — four separate yes/no questions (kids, dogs, cats, other
   pets), each mapping 1:1 to `pet.json`'s `compatible_kids`/`compatible_dogs`/
   `compatible_cats`/`compatible_other_pets`. Hard filter on "yes" answers only.
9. **Budget** — single max-dollar field. Hard filter on `adoption_fee`.

**Matching model:** category, breed, size, age range, budget, and household
compatibility are hard filters — each is precise enough that a mismatch means the pet
genuinely isn't what was asked for, so non-matches are excluded outright. Living
situation, activity/temperament, experience level, and openness to a senior pet are soft
signals instead — scored rather than excluding, with results sorted best-score-first.
Each result card shows a "*n*/*m* Matches" ribbon reflecting how many of the *answered*
soft-signal questions it satisfies (omitted entirely if no soft-signal question was
answered).

**Live data:** `fetchCategoryPets()`/`loadAllPets()` call
`fetch('/adoptions/pets/{category}/index.json')` then per-pet
`fetch('/adoptions/pets/{category}/{petId}/pet.json')` across all 7 categories in
parallel — the identical pattern `cats.html` itself uses for its one category, extended
sitewide. Already-adopted pets (`status === "Adopted"`) are excluded from results. Fetch
failures log real error detail to `console.error`/`console.warn` only, never into the
visible DOM.

**Icons.** All 10 quiz-specific icons are exact, unaltered SVG source supplied for this
task. Two treatments apply depending on the source file's own palette, both via CSS
`filter` on the wrapper only, never by editing the SVG markup: Mono icons (7 category
icons + 3 living-situation + 1 tick) shift from Navy-tinted to solid white on select;
Color icons (lightning bolt, balance scale, moon, the Activity-Level tick) keep their
real palette and get a brightness/saturation boost plus drop-shadow on select instead,
since flattening them would erase the color entirely. One icon (Birds) had its
`stroke-width` attribute adjusted from `0.567345` to `2.5` — the one explicitly
authorized exception, to match its line weight to the rest of the set; no path or shape
data was changed.

**Breed dropdown.** Uses the site's locked "Message Bubble" dropdown shell with a live
search filter. The panel is `position: fixed`, positioned every time it opens from the
trigger's real on-screen coordinates in JS, rather than `position: absolute` — required
because the wizard's outer card has `overflow: hidden` for its rounded corners, which
would otherwise silently clip the panel's lower rows any time a category's list is long
enough to need them. This exact fix (and the bug it corrects) is also documented
independently in `PETCENTER_DESIGN_SYSTEM_v1_0.md` Section 24 and in the SVG Icon
Library doc, for reuse by any other worker building a dropdown from the same component.

**Recommended enhancements carried through from the original brief:** retake-one-answer
via clickable recap chips above the results grid (jumps straight back to that step
rather than forcing a full restart), a graceful "no strong matches" state that suggests
loosening one specific *answered* hard-filter question at a time, and a "Request a Pet
Match" link to `/pet-match/` (Task 5's request form) as a next step once nothing
currently listed matches, since the two tools solve opposite problems.
