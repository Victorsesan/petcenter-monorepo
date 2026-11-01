# Task-F5-Completion.md

## Task Number
F5 — Free resource integrations

## What Was Required
Three link-outs, routed by the merchant/visitor's stated country:
1. Spay/neuter referral resources (SpayUSA for US, National Desexing Network
   for Australia, a Canadian directory, RSPCA/Dogs Trust for UK).
2. Pet transport network resources (Pilots N Paws for US, Furry Hobos 'N
   Hiway Heroes for Canada, Nickoftime.org.uk-coordinated networks for UK,
   Fly4Paws for Australia).
3. A crisis-resources destination for the Urgent Rehoming flag, linking to
   established domestic-violence, housing/eviction, and military-deployment
   pet-care resources per country — compiled and verified live via web
   search at build time, not hardcoded from the task document, per
   Directive 11.

File zone given: "listing page, new resources page." Per the CEO's
clarification, "the listing page" means Pet Center's seven pet-category
listing pages (`cats.html`, `dogs.html`, `birds.html`, `reptiles.html`,
`small-mammals.html`, `livestocks.html`, `aquariums.html`) — the pages
where listed pets are shown in card form by pet type. `pet-detail.html`
was later added to scope on the CEO's explicit instruction (a light patch,
not a full task) so a visitor landing on one specific pet's page directly
has the same path to these resources. The new resources page's route was
left to my judgment; built as `resources.html`, deployed at
`resources/index.html` (see live-path note below), with in-page anchors
(`#spay-neuter`, `#transport`, `#crisis`) for deep-linking.

**Live-path discrepancy, flagged per the delivery guide's "do not guess
silently" instruction:** `adoption-website-project-summary.md`'s Section 1
folder tree shows the seven category pages as loose files directly inside
`pets/` (e.g. `pets/cats.html`), while `WORKER-DELIVERY-GUIDE.md`'s source→
live-path table (Section 3) maps each to its own `pets/<name>/index.html`.
I followed the delivery guide's table below, since it's the document
stated to be authoritative for this column, and because it matches the
site's own stated rule that every page is a folder containing `index.html`
("never a loose `name.html` in the URL"). Worth a second pair of eyes to
confirm `adoption-website-project-summary.md`'s folder tree isn't out of
date.

## Shared Contracts Built Against
- **Listing core schema (owned by Task A1):** `is_urgent` (bool) — a
  SHARED CONTRACTS stub field this task was explicitly listed as building
  against in parallel with A1. Read in `renderPets()` on the seven category
  pages (drives a per-card "URGENT" badge) and in `populatePetInfo()` on
  `pet-detail.html` (drives a conditional banner). Both degrade to "show
  nothing extra" for any pet record that doesn't have the field yet, so
  nothing breaks before A1's real field lands; reconciles automatically
  once it does, since the field name already matches.
- No other shared contract fields were read or written by this task.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `resources.html` | `resources/index.html` | **New** — in the zip | Not shared — new file, sole owner Task F5. |
| `cats.html` | `pets/cats/index.html` | Changed | Shared — Task A3 already has its own anchor-commented zone in this file (urgent-only filter toggle); my additions are in a separate `TASK-F5` zone and do not touch A3's. |
| `dogs.html` | `pets/dogs/index.html` | Changed | Same as `cats.html`. |
| `birds.html` | `pets/birds/index.html` | Changed | Same as `cats.html`. |
| `reptiles.html` | `pets/reptiles/index.html` | Changed | Same as `cats.html`. |
| `small-mammals.html` | `pets/small-mammals/index.html` | Changed | Same as `cats.html`. |
| `livestocks.html` | `pets/livestocks/index.html` | Changed | Same as `cats.html`. |
| `aquariums.html` | `pets/aquariums/index.html` | Changed | Same as `cats.html`. |
| `pet-detail.html` | `pets/detail/index.html` | Changed | Shared — not yet modified by A1/B4/G1 in the version I was given (no `health_status`/`temperament`/`reason_for_rehoming`/`is_urgent` rendering present), so no existing zones to avoid; my `TASK-F5` zone is ready for theirs to sit alongside it. |

The modification to all seven category pages is byte-for-byte identical —
verified by diff — except for the surrounding file content (title,
category name, empty-state icon), none of which this task touched. The
two diffs below are shown in full once, under `cats.html`, and referenced
for the other six.

## Code Changes

### `resources.html` → live path `resources/index.html`
**New file — in the zip at `resources/index.html`.** No existing code to
merge; see "New files" section below for a structural summary.

---

### `cats.html` → live path `pets/cats/index.html`

**Diff 1 of 2 — Helpful Resources link-out strip**

<!-- TASK-F5 START -->
**Old code:**
```html
    <div class="pc-pet-grid" id="petGrid">
```
**New code:**
```html
    <!-- TASK-F5 START — Helpful Resources link-out strip (Task F5: Free
         resource integrations). Three link-outs to the new resources.html
         page's sections: spay/neuter assistance, pet transport networks,
         and crisis rehoming support (gated conceptually on A1's is_urgent
         listing flag, though this strip itself is shown to every visitor
         on every listing page, not just on urgent listings, since a visitor
         could need this help regardless of which specific pet they're
         viewing). Country-specific routing lives on resources.html itself
         (its country filter), not here. -->
    <div class="pc-f5-resources-strip">
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
      </svg>
      <span>Need help?</span>
      <a href="/resources/#spay-neuter">Spay/neuter assistance</a>
      <span class="pc-f5-sep">&middot;</span>
      <a href="/resources/#transport">Pet transport help</a>
      <span class="pc-f5-sep">&middot;</span>
      <a href="/resources/#crisis">Urgent rehoming? Get crisis support</a>
    </div>
    <!-- TASK-F5 END -->

    <div class="pc-pet-grid" id="petGrid">
```
<!-- TASK-F5 END -->

Also requires appending this CSS block just before the file's closing
`</style>` (old code shown is the file's actual closing tag sequence):

<!-- TASK-F5 START -->
**Old code:**
```css
  </style>
  </head>
```
**New code:**
```css

/* TASK-F5 START — Helpful Resources link-out strip styles (Task F5: Free
   resource integrations). New rule block, appended at the end of this
   page's existing <style>; nothing above this comment in the stylesheet
   was touched. */
.pc-f5-resources-strip {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px 14px;
  background: var(--orange-light);
  border: 1px solid rgba(237,118,36,0.25);
  border-radius: 10px;
  padding: 10px 16px;
  margin-bottom: 20px;
  font-family: 'Sora', sans-serif;
  font-size: 12.5px;
  color: var(--navy);
}
.pc-f5-resources-strip svg { flex-shrink: 0; color: var(--orange); }
.pc-f5-resources-strip a { color: var(--navy); font-weight: 600; border-bottom: 1px solid var(--gold); }
.pc-f5-resources-strip a:hover { color: var(--orange); border-color: var(--orange); }
.pc-f5-resources-strip .pc-f5-sep { color: rgba(16,33,75,0.3); }
/* TASK-F5 END */

  </style>
  </head>
```
<!-- TASK-F5 END -->

**Diff 2 of 2 — Urgent Rehoming badge (reads A1's `is_urgent` stub)**

<!-- TASK-F5 START -->
**Old code:**
```css
.pc-pet-card__nel-badge svg { width: 12px; height: 12px; fill: var(--gold); flex-shrink: 0; }
/* TASK-A2 END */
```
**New code:**
```css
.pc-pet-card__nel-badge svg { width: 12px; height: 12px; fill: var(--gold); flex-shrink: 0; }
/* TASK-A2 END */

/* TASK-F5 START — Urgent Rehoming badge. Reads A1's is_urgent flag (a
   SHARED CONTRACTS stub field this task builds against in parallel, per
   the BUILD APPROACH section — reconciles into A1's real listing file at
   integration). Deliberately placed at the BOTTOM-left corner of the
   image, not top-left/top-right like the ADOPTED and Needs-Extra-Love
   badges above, so it never collides with either if a pet record somehow
   has more than one flag true at once. Orange (var(--orange)), not navy,
   so it visually reads as an alert rather than a neutral label — matches
   the "urgent" meaning. This is a plain, non-clickable indicator (a div
   element, not a link) because it renders inside the card's own full-card
   <a> wrapper, and a nested <a> inside an <a> is invalid HTML that breaks
   the card's click-through — same constraint the ADOPTED/NEL badges above
   already follow. The actual link to crisis support lives in the "Need
   help?" strip at the top of this page (see Diff 1 above). */
.pc-pet-card__urgent-badge {
  position: absolute;
  bottom: 12px;
  left: 0;
  display: flex;
  align-items: center;
  gap: 5px;
  background: var(--orange);
  color: #fff;
  font-family: 'Sora', sans-serif;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  padding: 6px 14px 6px 12px;
  border-radius: 0 4px 4px 0;
  box-shadow: 0 2px 8px rgba(0,0,0,0.25);
}
.pc-pet-card__urgent-badge svg { width: 12px; height: 12px; flex-shrink: 0; }
/* TASK-F5 END */
```
<!-- TASK-F5 END -->

<!-- TASK-F5 START -->
**Old code:**
```js
var iconHeart = '<svg viewBox="0 0 24 24"><path d="M12 21s-7.5-4.6-10-9.1C.5 8.6 2 5 5.5 5c2 0 3.3 1 4.5 2.4C11.2 6 12.5 5 14.5 5 18 5 19.5 8.6 22 11.9 19.5 16.4 12 21 12 21z"/></svg>';
```
**New code:**
```js
var iconHeart = '<svg viewBox="0 0 24 24"><path d="M12 21s-7.5-4.6-10-9.1C.5 8.6 2 5 5.5 5c2 0 3.3 1 4.5 2.4C11.2 6 12.5 5 14.5 5 18 5 19.5 8.6 22 11.9 19.5 16.4 12 21 12 21z"/></svg>';
    // TASK-F5 START — Alert-triangle icon for the Urgent Rehoming badge
    // (inline SVG, no emoji, per Rule 2).
    var iconAlert = '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
    // TASK-F5 END
```
<!-- TASK-F5 END -->

<!-- TASK-F5 START -->
**Old code:**
```js
      // TASK-A2 START — Needs Extra Love badge (only when the flag is
      // present and true; older/pre-A2 pet records simply won't have the
      // field yet, so this degrades to "no badge" rather than erroring).
      var nelBadge = p.needs_extra_love
        ? '<div class="pc-pet-card__nel-badge">' + iconHeart + 'Needs Extra Love</div>'
        : '';
      // TASK-A2 END
```
**New code:**
```js
      // TASK-A2 START — Needs Extra Love badge (only when the flag is
      // present and true; older/pre-A2 pet records simply won't have the
      // field yet, so this degrades to "no badge" rather than erroring).
      var nelBadge = p.needs_extra_love
        ? '<div class="pc-pet-card__nel-badge">' + iconHeart + 'Needs Extra Love</div>'
        : '';
      // TASK-A2 END

      // TASK-F5 START — Urgent Rehoming badge (reads A1's is_urgent bool,
      // a SHARED CONTRACTS stub field — see the CSS block above for full
      // reasoning on placement/non-clickability). Degrades to "no badge"
      // for any pet record that doesn't have this field yet, same pattern
      // as nelBadge above.
      var urgentBadge = p.is_urgent
        ? '<div class="pc-pet-card__urgent-badge">' + iconAlert + 'Urgent</div>'
        : '';
      // TASK-F5 END
```
<!-- TASK-F5 END -->

<!-- TASK-F5 START -->
**Old code:**
```js
          adoptedBadge +
          nelBadge +
        '</div>'
```
**New code:**
```js
          adoptedBadge +
          nelBadge +
          urgentBadge +
        '</div>'
```
<!-- TASK-F5 END -->

---

### `dogs.html` → live path `pets/dogs/index.html`
Identical diffs to `cats.html` above (both Diff 1 and Diff 2, byte-for-byte
verified) — same old code, same new code, applied to this file.

### `birds.html` → live path `pets/birds/index.html`
Identical diffs to `cats.html` above — same old code, same new code.

### `reptiles.html` → live path `pets/reptiles/index.html`
Identical diffs to `cats.html` above — same old code, same new code.

### `small-mammals.html` → live path `pets/small-mammals/index.html`
Identical diffs to `cats.html` above — same old code, same new code.

### `livestocks.html` → live path `pets/livestocks/index.html`
Identical diffs to `cats.html` above — same old code, same new code.

### `aquariums.html` → live path `pets/aquariums/index.html`
Identical diffs to `cats.html` above — same old code, same new code.

---

### `pet-detail.html` → live path `pets/detail/index.html`

**Diff 1 of 3 — CSS (resources strip + urgent banner styles)**

<!-- TASK-F5 START -->
**Old code:**
```css
  body { padding-top: 0 !important; }
}
</style>
  </head>
```
**New code:**
```css
  body { padding-top: 0 !important; }
}

/* TASK-F5 START — Helpful Resources strip + Urgent Rehoming banner styles
   (Task F5: Free resource integrations). New rule block, appended at the
   end of this page's existing <style>; nothing above this comment was
   touched. Same visual language as the matching strip already shipped on
   the seven category listing pages (cats.html etc.), reused here so a
   visitor who lands directly on a single pet's page (not via a category
   grid) has the same path to these resources. */
.pc-f5-resources-strip {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px 14px;
  background: var(--orange-light);
  border: 1px solid rgba(237,118,36,0.25);
  border-radius: 10px;
  padding: 12px 18px;
  margin-top: 32px;
  font-family: 'Sora', sans-serif;
  font-size: 12.5px;
  color: var(--navy);
}
.pc-f5-resources-strip svg { flex-shrink: 0; color: var(--orange); }
.pc-f5-resources-strip a { color: var(--navy); font-weight: 600; border-bottom: 1px solid var(--gold); }
.pc-f5-resources-strip a:hover { color: var(--orange); border-color: var(--orange); }
.pc-f5-resources-strip .pc-f5-sep { color: rgba(16,33,75,0.3); }

/* Urgent Rehoming banner — hidden by default; populatePetInfo() shows it
   only when pet.is_urgent is true. Reads a SHARED CONTRACTS stub field
   this task builds against (see this task's Shared Contracts section
   above); since A1's is_urgent field doesn't exist in this page's real
   data yet, this degrades to "stays hidden" until that lands, same as the
   category pages' Urgent badge degrades to "no badge" for older records. */
.pc-f5-urgent-banner {
  display: none;
  align-items: center;
  gap: 10px;
  background: var(--orange);
  color: #fff;
  border-radius: 10px;
  padding: 14px 18px;
  margin-top: 32px;
  font-family: 'Sora', sans-serif;
  font-size: 13px;
  font-weight: 500;
}
.pc-f5-urgent-banner svg { flex-shrink: 0; }
.pc-f5-urgent-banner strong { font-weight: 700; }
.pc-f5-urgent-banner a { color: #fff; font-weight: 700; text-decoration: underline; text-underline-offset: 2px; }
.pc-f5-urgent-banner a:hover { color: var(--navy); }
/* TASK-F5 END */
</style>
  </head>
```
<!-- TASK-F5 END -->

**Diff 2 of 3 — HTML (strip + banner markup)**

<!-- TASK-F5 START -->
**Old code:**
```html
        </ul>
      </div>
      <!-- Recommended Pets -->
```
**New code:**
```html
        </ul>
      </div>

      <!-- TASK-F5 START — Urgent Rehoming banner (Task F5: Free resource
           integrations). Hidden by default (inline style + CSS above);
           populatePetInfo() shows it only when pet.is_urgent is true. -->
      <div id="petUrgentBanner" class="pc-f5-urgent-banner">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <span><strong>This is an urgent rehoming situation.</strong> If you're the one rehoming and need support right now, <a href="/resources/#crisis">get crisis resources here</a>.</span>
      </div>
      <!-- TASK-F5 END -->

      <!-- TASK-F5 START — Helpful Resources link-out strip (Task F5: Free
           resource integrations). Same three link-outs as the category
           pages' strip, so a visitor who reaches this specific pet's page
           directly still has a path to spay/neuter, transport, and crisis
           resources. -->
      <div class="pc-f5-resources-strip">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
        </svg>
        <span>Need help?</span>
        <a href="/resources/#spay-neuter">Spay/neuter assistance</a>
        <span class="pc-f5-sep">&middot;</span>
        <a href="/resources/#transport">Pet transport help</a>
        <span class="pc-f5-sep">&middot;</span>
        <a href="/resources/#crisis">Urgent rehoming? Get crisis support</a>
      </div>
      <!-- TASK-F5 END -->

      <!-- Recommended Pets -->
```
<!-- TASK-F5 END -->

**Diff 3 of 3 — JS (urgent banner toggle, inside `populatePetInfo()`)**

<!-- TASK-F5 START -->
**Old code:**
```js
    // Pet ID
    var petIdEl = document.getElementById('petId');
    if (petIdEl) petIdEl.textContent = petId;

    // Load recommended
    loadRecommended(category, petId, pet);
  }
```
**New code:**
```js
    // Pet ID
    var petIdEl = document.getElementById('petId');
    if (petIdEl) petIdEl.textContent = petId;

    // TASK-F5 START — Urgent Rehoming banner toggle (reads A1's is_urgent
    // bool, a SHARED CONTRACTS stub field — see this task's Shared
    // Contracts section above). Degrades to "stays hidden" if the field
    // isn't present yet on this page's data.
    var urgentBanner = document.getElementById('petUrgentBanner');
    if (urgentBanner) urgentBanner.style.display = pet.is_urgent ? 'flex' : 'none';
    // TASK-F5 END

    // Load recommended
    loadRecommended(category, petId, pet);
  }
```
<!-- TASK-F5 END -->

## New files

⚠️ **NEW FOLDER REQUIRED — not present in either
`adoption-website-project-summary.md` or `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`.**
Neither summary document's folder-structure section lists a `resources/`
folder anywhere in the site tree (checked both in full). The integrator
needs to **create a new top-level folder named `resources/` at the site
root** (a sibling of `pets/`, `blog/`, `services/`, `signin/`, etc.) and
place the file below inside it. Flagging this heavily, as instructed,
rather than assuming it's obvious.

- `resources/index.html` — New file — in the zip, at this new path. Named
  `index.html` per the strict live-deployment rule (no bare `name.html`
  URLs). A standalone "Rehoming Resources & Support" page: header/footer/
  hamburger copied verbatim from `cats.html` (the canonical blueprint), a
  plain button-group country filter (US/Canada/UK/Australia — not a
  `<select>`/dropdown), and three sections (`#spay-neuter`, `#transport`,
  `#crisis`) each with cards per country linking to verified, currently-
  live external organizations. Full sourcing/verification table is in the
  Second Verification Round section below.

## Bugs Found Outside Task Scope
- `.pc-footer__top`'s `grid-template-columns` is `2fr 1fr 1fr 1.5fr` (4
  values) but the actual footer markup has 5 columns
  (`.pc-footer__brand` + four `.pc-footer__col` divs), in `cats.html` and,
  since the CSS is identical sitewide, likely every page sharing this
  footer template. Pre-existing, not in this task's File zone — not fixed.
- `pet-detail.html`'s hero gallery image
  (`id="galleryMain"`, `onerror="this.src='/assets/images/pet-placeholder.jpg'"`)
  has no one-time guard: if the placeholder image itself is ever
  unavailable, re-setting `src` to the same failing URL re-fires `onerror`
  indefinitely (confirmed: 800+ repeated requests in local testing).
  Pre-existing, not in this task's File zone — not fixed. Recommend
  whoever owns this page's gallery code add a guard that clears the
  `onerror` handler after the first fallback attempt.

## Self-Check Confirmation
- Only the files listed in Files Changed above were touched:
  `resources.html` (new), the seven category listing pages, and
  `pet-detail.html` (added to scope on the CEO's explicit instruction to
  resolve an open question with a light patch, documented in the Second
  Verification Round below).
- Every block added to a shared file carries `<!-- TASK-F5 START -->` /
  `<!-- TASK-F5 END -->` (HTML) or the matching `//`/`/* */` comment form
  for CSS/JS, per Directive 12.
- Every "Old code" snippet above was copied character-for-character from
  the original files I was given and re-verified (via scripted exact-match
  count) to appear exactly once in each pristine original file — including
  the Round 2 and Round 3 additions, which were re-checked against the
  original uploads, not just the Round 1 output, specifically because this
  delivery guide requires it.
- `resources/index.html` is the only file in the zip; confirmed via
  `unzip -l` before sending. It is listed in Files Changed with the exact
  same path. Nothing else is new.
- Zip contains no `config.php`, `pet.json`, `.env`, secrets, or anything
  under `applications/` or `uploads/` — confirmed by inspection (the zip
  holds exactly one file, `resources/index.html`).
- No dropdown menus were built in this task (the country filter on
  `resources.html` is a plain button group, not a `<select>`), so the
  modern dropdown component requirement doesn't apply here.
- No emoji anywhere. All icons are inline SVG.

## SECOND VERIFICATION ROUND

Re-read, in full, from scratch, the revised `Rehoming-Platform-Task-
Assignments.md` (BUILD APPROACH, VISIBILITY & ACCESS MODEL, SHARED
CONTRACTS, Universal Requirements, every adjacent task) and re-checked this
task's output against it.

**Finding 1 — real gap, fixed:** SHARED CONTRACTS explicitly lists F5 as
building against the `is_urgent` stub. The first pass's resource strip
never actually read it. Fixed by adding the `is_urgent`-conditional
"URGENT" badge to the category-page cards (Diff 2 above) — same pattern as
A2's existing `needs_extra_love` badge, placed bottom-left so it never
collides with the existing top-left/top-right ADOPTED/NEL badges. Verified
with Playwright against three stub pet records (normal / needs_extra_love
/ is_urgent): all three badge states render correctly together, no visual
collision.

**Finding 2 — checked, not a bug:** anchor-link (`#spay-neuter`,
`#transport`, `#crisis`) scrolling against the fixed header. Initial
automated readings suggested the fixed header might cover the target
section's heading; turned out to be a Playwright artifact (reusing one
`Page` object across several same-origin hash navigations). Fresh-page
tests and screenshots confirm all three clear the header cleanly —
`.pc-section` already carries 72px of top padding against a 68px header.

**Finding 3 — checked, not a bug:** a mobile full-page screenshot of
`cats.html` appeared to show the filter bottom-sheet rendered inline
mid-page. Reproduced the identical artifact against the completely
unmodified original file, confirming it's a pre-existing Chromium/
Playwright full-page-screenshot quirk with `position: fixed` elements, not
something this task introduced.

**Finding 4 — flagged, then resolved in the Third Round below:**
`pet-detail.html` was uploaded alongside the category pages but wasn't
originally confirmed as part of F5's File zone, and a visitor landing
there directly had no path to these resources. Flagged as a question
rather than acted on unilaterally — see Third Round for the resolution.

**Sources verified live (per Directive 11):**

| Category | Country | Organization | Link | Verification note |
|---|---|---|---|---|
| Spay/neuter | US | SpayUSA | spayusa.org | Confirmed current. |
| Spay/neuter | Australia | National Desexing Network | ndn.org.au | Confirmed current. |
| Spay/neuter | Canada | Humane Canada (substitute) | humanecanada.ca | **Deviation:** "Welfare for Animals" (WFA), named in the task doc, could not be confirmed as a live, reachable site. Substituted Humane Canada's spay/neuter guidance. |
| Spay/neuter | UK | RSPCA; Dogs Trust | rspca.org.uk; dogstrust.org.uk | Both confirmed current. |
| Transport | US | Pilots N Paws | pilotsnpaws.org | Confirmed current; matches "rescue relay" wording in the task doc. |
| Transport | Canada | Furry Hobos 'N Hiway Heroes | facebook.com/groups/FurryHobosNHiwayHeros | Confirmed real/active; operates via Facebook group, no standalone site. |
| Transport | UK | Nickoftime.org.uk-coordinated networks | nickoftime.org.uk | **Flagged:** real organization, but current (2025/2026) activity level unconfirmed. Noted inline on the page with a fallback. |
| Transport | Australia | Fly4Paws | fly4paws.org | Confirmed current. |
| Crisis (DV) | US | RedRover Relief | redrover.org/relief | Confirmed current. |
| Crisis (housing) | US | SAF-T | alliephillips.com/saf-tprogram/ | Confirmed current. |
| Crisis (military) | US | Dogs on Deployment | dogsondeployment.org | Confirmed current via direct fetch. |
| Crisis (DV) | Canada | Humane Canada → regional SPCA programs | humanecanada.ca | Confirmed current. Housing/military categories: **flagged**, no single reliable national link found. |
| Crisis (DV) | UK | Dogs Trust — Freedom Project | dogstrust.org.uk | Confirmed current, nationwide. Housing/military categories: **flagged**, same reasoning. |
| Crisis (DV) | Australia | RSPCA — Safe Beds for Pets (NSW linked as entry point) | rspcansw.org.au | Confirmed current; run per-state, noted on the page. |

**Verification performed:** `node --check` on extracted inline JS (all 8
files, no syntax errors); tag-balance/duplicate-id lint (all clean);
Playwright against local stub fixture data mirroring the real
`/adoptions/pets/<category>/...json` fetch pattern — resources strip,
all three badge states, country filter (DOM-count-verified, not just
visual), mobile viewport, and hamburger menu all confirmed working.

**Self-Check Confirmation (second round):** scope still limited to the
confirmed File zone at that time (`resources.html` + seven category
pages); `pet-detail.html` reviewed but not yet edited. No dropdowns added.
No emoji.

## THIRD VERIFICATION ROUND — `pet-detail.html` added

Per the CEO's explicit instruction not to leave Finding 4 pending, and to
act on it directly since it qualified as a light patch: added the same
"Need help?" strip to `pet-detail.html`, plus a conditional Urgent
Rehoming **banner** (full-width, not a corner badge — the right shape for
a single-pet page rather than a card grid) shown only when `pet.is_urgent`
is true. See Diffs 1–3 under `pet-detail.html` above for the exact code.

**Verification performed:** local test server with two stub fixtures
(`is_urgent: true` and no `is_urgent` field at all, testing the
field-absent degrade path). Confirmed via direct DOM evaluation:
`#petUrgentBanner` computed `display: flex` for the urgent pet, `display:
none` for the normal one. Playwright screenshots (after clearing this
page's own unrelated, intentional 5-second-minimum branded preloader —
confirmed by reading its source, not a bug) show the banner and strip both
rendering correctly, in the right order, for the urgent pet, and the strip
alone for the normal pet. While testing, found the infinite `onerror`
image-retry bug logged under Bugs Found Outside Task Scope above — real,
pre-existing, not fixed.

**Self-Check Confirmation (third round):** File zone now includes
`pet-detail.html`, added on explicit instruction, not a unilateral scope
expansion. New content anchor-commented in all three languages it touches
(HTML/CSS/JS). No dropdowns added. No emoji. No other file touched.

## FOURTH VERIFICATION ROUND — hand-in format only (no code changes)

Per `WORKER-DELIVERY-GUIDE.md`, restructured this completion file: Files
Changed now has four columns (source name, live path, changed/new,
shared-with), live paths taken from the guide's Section 3 mapping table
(and from `adoption-website-project-summary.md`'s folder-structure section
for `resources.html`, which isn't in that table — see the live-path
discrepancy note at the top of this file). Re-verified every "Old code"
snippet, including the Round 2 and Round 3 additions, as an exact,
single-occurrence match against the **pristine original files**, not the
intermediate Round 1 output — done via a scripted exact-match count
against `*-original.html`, not by eye. Moved `resources.html` out of the
completion file's Code Changes section and into `Task-F5-NewFiles.zip` at
`resources/index.html`, since it's a new file, not a change to an existing
one. No code was changed in this round — hand-in packaging only.

## FIFTH VERIFICATION ROUND — zip packaging correction (no code changes)

Per the CEO's follow-up correcting a sitewide worker misunderstanding of
the delivery guide: this completion file now ships **inside** the same zip
as the new files, rather than as a separate attachment, and the zip is
named `Rehoming-Platform-Task-F5.zip` (project name taken from the task
assignment file's own name, `Rehoming-Platform-Task-Assignments.md`, +
this task's number), not the earlier `Task-F5-NewFiles.zip`. The zip's
layout:

```
Rehoming-Platform-Task-F5.zip
├── Task-F5-Completion.md     ← this file, at the zip root (not part of
│                                the live site path — documentation only)
└── resources/
    └── index.html            ← becomes adopt.petcenterco.com/resources/
                                 (new top-level folder — see the ⚠️ note
                                 under "New files" above)
```

No code was changed in this round — packaging/hand-in correction only.
Re-confirmed before sending: the zip contains no `config.php`, `pet.json`,
`.env`, or anything under `applications/`/`uploads/`; every path inside it
is listed above with the same path; `resources/index.html` is the only
live-deployment file in the zip, everything else changed goes in this
completion file's Code Changes section as before, never in the zip.
