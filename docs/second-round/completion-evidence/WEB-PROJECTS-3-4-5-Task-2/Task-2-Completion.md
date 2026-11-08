# Task 2 — Completion File

## Task Number
**TASK 2 — Project 3: Build the New Content Pages** (from `WEB-PROJECTS-3-4-5-TASK-ASSIGNMENTS.md`). Task id: **2**. The assignment's splitting rule would normally divide this across several workers; in this session the CEO had one worker build the whole finalized page list, so this file covers all of it.

## What Was Required
Build the new content pages from Task 1's finalized list and wire them into the site's header, mobile menu and footer, reusing `cats.html`'s header/footer/hamburger and following `PETCENTER_DESIGN_SYSTEM_v1.0.md`. Pages built: Volunteer, Foster a Pet, Events & Adoption Days, About Us, Lost & Found Pets (`/lost-and-found/`), Can't Keep Your Pet? (`/rehoming-resources/`), Species Care Readiness Guide, Success Stories, and — at the CEO's later explicit request — Vet & Rescue Partners, Sponsor a Pet and Gift My Adoption. Every page is a folder containing `index.html`.

## NEW FOLDERS TO CREATE — READ THIS FIRST (integrator)
**None of the paths below exist in `adoption-website-project-summary.md` or `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`.** Every one is a **new top-level folder in the site root** (`public_html/adopt/`), a sibling of the existing `blog/` and `testimonials/` folders. Unzipping the zip on top of the site root creates them; if you copy by hand, create each folder first and put the file inside it **named exactly `index.html`** (every live page is a folder containing `index.html`, never a loose `name.html`).

| New folder to create (site root) | Contains | Public URL |
|---|---|---|
| `volunteer/` | `index.html` | `adopt.petcenterco.com/volunteer/` |
| `foster/` | `index.html` | `adopt.petcenterco.com/foster/` |
| `events/` | `index.html`, `events-data.json` | `adopt.petcenterco.com/events/` |
| `about/` | `index.html` | `adopt.petcenterco.com/about/` |
| `lost-and-found/` | `index.html`, `lost-and-found-data.json` | `adopt.petcenterco.com/lost-and-found/` |
| `rehoming-resources/` | `index.html` | `adopt.petcenterco.com/rehoming-resources/` |
| `species-guide/` | `index.html` | `adopt.petcenterco.com/species-guide/` |
| `success-stories/` | `index.html`, `success-stories-data.json` | `adopt.petcenterco.com/success-stories/` |
| `partners/` | `index.html` | `adopt.petcenterco.com/partners/` |
| `sponsor-a-pet/` | `index.html` | `adopt.petcenterco.com/sponsor-a-pet/` |
| `gift-my-adoption/` | `index.html` | `adopt.petcenterco.com/gift-my-adoption/` |

The three `*-data.json` files are new admin-hand-edited data files (same idea as `testimonials.json`); each page fetches its own file by the exact path above, so they must stay in their page's folder. No existing folder in the documented structure is modified or replaced. The `/blog/`, `/testimonials/` and `/signin/` links used in the shared header/footer already exist and were not changed.

**`Task-2-Completion.md` (this file) is also inside the zip, at the zip root, so the hand-in travels as one piece. It is documentation only — read it, then do not publish it to the live site.**

## Files Changed
| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `cats.html` | `pets/cats/index.html` | Changed | Yes — Task 3 (filter zone) and Task 5 (CTA zone) also edit this file; this task touched only header/footer nav markup, the header dropdown CSS, the footer grid line and one script block |
| `volunteer.html` | `volunteer/index.html` | New | No |
| `foster.html` | `foster/index.html` | New | No |
| `events.html` | `events/index.html` | New | No |
| `about.html` | `about/index.html` | New | No |
| `lost-and-found.html` | `lost-and-found/index.html` | New | No |
| `rehoming-resources.html` | `rehoming-resources/index.html` | New | No |
| `species-guide.html` | `species-guide/index.html` | New | No |
| `success-stories.html` | `success-stories/index.html` | New | No |
| `partners.html` | `partners/index.html` | New | No |
| `sponsor-a-pet.html` | `sponsor-a-pet/index.html` | New | No |
| `gift-my-adoption.html` | `gift-my-adoption/index.html` | New | No |
| `events-data.json` | `events/events-data.json` | New | No |
| `lost-and-found-data.json` | `lost-and-found/lost-and-found-data.json` | New | No |
| `success-stories-data.json` | `success-stories/success-stories-data.json` | New | No |
| `Task-2-Completion.md` | `Task-2-Completion.md` (zip root — documentation only, do not publish) | New | No |

## Code Changes

Only one existing file was changed: `cats.html`. The other 6 category pages and `pets.html` were not supplied and were not touched (see Bugs Found Outside Task Scope).

### `cats.html`  →  live path `pets/cats/index.html`

**Header dropdown CSS**

**Old code:**
```css
.pc-header__nav .has-drop > ul {
  display: none;
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  background: #10214b;
  border-radius: 8px;
  list-style: none;
  min-width: 168px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.22);
  padding: 6px 0;
  z-index: 100;
}
.pc-header__nav .has-drop:hover > ul { display: block; }
.pc-header__nav .has-drop > ul a { padding: 9px 18px; font-size: 13px; border-radius: 0; }
```
**New code:**
```css
/* TASK-2c START — Section 24 Custom Dropdown System ("Message Bubble" locked shell) replaces the old
   hover-only .has-drop rules: click-to-open trigger, rotating chevron, 5-item scroll cap, flip-up if no room */
.pc-nav-dropdown { position: relative; }
.pc-nav-dropdown__trigger { display: flex; align-items: center; gap: 6px; background: none; border: none; cursor: pointer; padding: 8px 11px; font-family: 'Sora', sans-serif; font-size: 13px; font-weight: 500; color: rgba(255,255,255,0.9); border-radius: 6px; transition: color 0.2s, background 0.2s; }
.pc-nav-dropdown__trigger:hover, .pc-nav-dropdown__trigger.active { color: #d7bd88; background: rgba(255,255,255,0.08); }
.pc-nav-dropdown__chevron { transition: transform 0.2s cubic-bezier(.4,0,.2,1); flex-shrink: 0; }
.pc-nav-dropdown__trigger.active .pc-nav-dropdown__chevron { transform: rotate(180deg); }
.pc-nav-dropdown__panel { position: absolute; top: 100%; left: 0; margin-top: 6px; min-width: 190px; background: #ffffff; border: 1.5px solid rgba(16,33,75,0.10); border-radius: 14px 0 14px 14px; box-shadow: 0 12px 32px rgba(16,33,75,0.18), 0 2px 8px rgba(16,33,75,0.08); overflow: hidden; z-index: 999; opacity: 0; visibility: hidden; transform: translateY(-8px) scale(0.98); transform-origin: top left; transition: opacity 0.2s ease, transform 0.2s cubic-bezier(.4,0,.2,1), visibility 0.2s; pointer-events: none; }
.pc-nav-dropdown__panel.open { opacity: 1; visibility: visible; transform: translateY(0) scale(1); pointer-events: auto; }
.pc-nav-dropdown__panel.flip-up { top: auto; bottom: 100%; margin-top: 0; margin-bottom: 6px; transform-origin: bottom left; border-radius: 14px 14px 14px 0; }
.pc-nav-dropdown__scroll { max-height: 225px; overflow-y: auto; padding: 6px 0; }
.pc-nav-dropdown__row { display: flex; align-items: center; padding: 11px 16px; border-left: 3px solid transparent; border-bottom: 1px solid rgba(16,33,75,0.06); font-family: 'Sora', sans-serif; font-size: 13.5px; color: var(--navy); text-decoration: none; transition: background 0.15s ease, border-color 0.15s ease; }
.pc-nav-dropdown__row:last-child { border-bottom: none; }
.pc-nav-dropdown__row:hover { background: rgba(16,33,75,0.05); color: var(--navy); border-left-color: var(--gold); }
/* TASK-2c END */
```

**Footer grid column template**

**Old code:**
```css
  grid-template-columns: 2fr 1fr 1fr 1.5fr;
```
**New code:**
```css
  /* TASK-2c START — footer grid corrected to 5 values to match the 5 footer columns (documented recurring bug,
     adoption-website-project-summary.md Section 8; canonical value from that section) */
  grid-template-columns: 2fr 1fr 1.2fr 1fr 1.3fr;
  /* TASK-2c END */
```

**Desktop header nav — About/Partners, Our Pets, Get Involved**

**Old code:**
```html
    <li><a href="/">Home</a></li>
    <li class="has-drop"><a href="#">Our Pets</a>
      <ul>
        <li><a href="/pets/dogs/">Dogs</a></li>
        <li><a href="/pets/cats/">Cats</a></li>
        <li><a href="/pets/birds/">Birds</a></li>
        <li><a href="/pets/reptiles/">Reptiles</a></li>
        <li><a href="/pets/small-mammals/">Small Mammals</a></li>
        <!-- TASK-2 START — Livestocks & Aquariums added to Our Pets dropdown -->
        <li><a href="/pets/livestocks/">Livestocks</a></li>
        <li><a href="/pets/aquariums/">Aquariums</a></li>
        <!-- TASK-2 END -->
      </ul>
    </li>
```
**New code:**
```html
    <li><a href="/">Home</a></li>
    <!-- TASK-2c START — About Us + Partners top-level links; Our Pets and Get Involved use the Section 24 dropdown shell
         (the original Our Pets items and their TASK-2 Livestocks/Aquariums anchors are preserved inside it) -->
    <li><a href="/about/">About Us</a></li>
    <li><a href="/partners/">Partners</a></li>
    <li class="pc-nav-dropdown">
      <button class="pc-nav-dropdown__trigger" aria-expanded="false" aria-haspopup="true">Our Pets
        <svg class="pc-nav-dropdown__chevron" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <div class="pc-nav-dropdown__panel">
        <div class="pc-nav-dropdown__scroll">
          <a class="pc-nav-dropdown__row" href="/pets/dogs/">Dogs</a>
          <a class="pc-nav-dropdown__row" href="/pets/cats/">Cats</a>
          <a class="pc-nav-dropdown__row" href="/pets/birds/">Birds</a>
          <a class="pc-nav-dropdown__row" href="/pets/reptiles/">Reptiles</a>
          <a class="pc-nav-dropdown__row" href="/pets/small-mammals/">Small Mammals</a>
          <!-- TASK-2 START — Livestocks & Aquariums added to Our Pets dropdown -->
          <a class="pc-nav-dropdown__row" href="/pets/livestocks/">Livestocks</a>
          <a class="pc-nav-dropdown__row" href="/pets/aquariums/">Aquariums</a>
          <!-- TASK-2 END -->
        </div>
      </div>
    </li>
    <li class="pc-nav-dropdown">
      <button class="pc-nav-dropdown__trigger" aria-expanded="false" aria-haspopup="true">Get Involved
        <svg class="pc-nav-dropdown__chevron" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <div class="pc-nav-dropdown__panel">
        <div class="pc-nav-dropdown__scroll">
          <a class="pc-nav-dropdown__row" href="/volunteer/">Volunteer</a>
          <a class="pc-nav-dropdown__row" href="/foster/">Foster a Pet</a>
          <a class="pc-nav-dropdown__row" href="/events/">Events &amp; Adoption Days</a>
          <a class="pc-nav-dropdown__row" href="/lost-and-found/">Lost &amp; Found Pets</a>
          <a class="pc-nav-dropdown__row" href="/sponsor-a-pet/">Sponsor a Pet</a>
        </div>
      </div>
    </li>
    <!-- TASK-2c END -->
```

**Desktop header nav — Success Stories**

**Old code:**
```html
    <li><a href="/testimonials/">Testimonials</a></li>
    <li class="nav-donate">
```
**New code:**
```html
    <li><a href="/testimonials/">Testimonials</a></li>
    <!-- TASK-2c START — Success Stories top-level link, after Testimonials -->
    <li><a href="/success-stories/">Success Stories</a></li>
    <!-- TASK-2c END -->
    <li class="nav-donate">
```

**Mobile menu — About Us / Partners**

**Old code:**
```html
  <nav class="pc-mobile-menu" id="pcMobileMenu" aria-label="Mobile navigation">
    <a href="/">Home</a>
```
**New code:**
```html
  <nav class="pc-mobile-menu" id="pcMobileMenu" aria-label="Mobile navigation">
    <a href="/">Home</a>
    <!-- TASK-2c START — About Us + Partners in the mobile menu -->
    <a href="/about/">About Us</a>
    <a href="/partners/">Partners</a>
    <!-- TASK-2c END -->
```

**Mobile menu — Get Involved links**

**Old code:**
```html
    <a href="/pets/aquariums/">Aquariums</a>
    <!-- TASK-2 END -->
    <a href="/blog/">Blog</a>
```
**New code:**
```html
    <a href="/pets/aquariums/">Aquariums</a>
    <!-- TASK-2 END -->
    <!-- TASK-2c START — Get Involved links in the mobile menu -->
    <a href="/volunteer/">Volunteer</a>
    <a href="/foster/">Foster a Pet</a>
    <a href="/events/">Events &amp; Adoption Days</a>
    <a href="/lost-and-found/">Lost &amp; Found Pets</a>
    <a href="/sponsor-a-pet/">Sponsor a Pet</a>
    <!-- TASK-2c END -->
    <a href="/blog/">Blog</a>
```

**Mobile menu — Success Stories**

**Old code:**
```html
    <a href="/testimonials/">Testimonials</a>
    <a href="https://docs.petcenterco.com/contact-us"
```
**New code:**
```html
    <a href="/testimonials/">Testimonials</a>
    <!-- TASK-2c START — Success Stories in the mobile menu -->
    <a href="/success-stories/">Success Stories</a>
    <!-- TASK-2c END -->
    <a href="https://docs.petcenterco.com/contact-us"
```

**Footer — Our Pets column**

**Old code:**
```html
          <li><a href="/pets/aquariums/">Aquariums</a></li>
          <!-- TASK-2 END -->
        </ul>
```
**New code:**
```html
          <li><a href="/pets/aquariums/">Aquariums</a></li>
          <!-- TASK-2 END -->
          <!-- TASK-2c START — Species Care Readiness Guide, footer-only, under Our Pets -->
          <li><a href="/species-guide/">Species Care Guide</a></li>
          <!-- TASK-2c END -->
        </ul>
```

**Footer — Information column**

**Old code:**
```html
          <li><a href="/">About Us</a></li>
          <li><a href="/blog/">Blog</a></li>
          <li><a href="/testimonials/">Testimonials</a></li>
```
**New code:**
```html
          <!-- TASK-2c START — About Us previously pointed at "/" (home); now points at the new /about/ page, plus Partners -->
          <li><a href="/about/">About Us</a></li>
          <li><a href="/partners/">Partners</a></li>
          <!-- TASK-2c END -->
          <li><a href="/blog/">Blog</a></li>
          <li><a href="/testimonials/">Testimonials</a></li>
          <!-- TASK-2c START — Success Stories -->
          <li><a href="/success-stories/">Success Stories</a></li>
          <!-- TASK-2c END -->
```

**Footer — Support column**

**Old code:**
```html
          <!-- TASK-1 END -->
          <li><a href="https://docs.petcenterco.com/returns-refunds/"
```
**New code:**
```html
          <!-- TASK-1 END -->
          <!-- TASK-2c START — new Get Involved / rehoming pages appended beneath Contact Us and Sign Up -->
          <li><a href="/volunteer/">Volunteer</a></li>
          <li><a href="/foster/">Foster a Pet</a></li>
          <li><a href="/events/">Events &amp; Adoption Days</a></li>
          <li><a href="/lost-and-found/">Lost &amp; Found Pets</a></li>
          <li><a href="/rehoming-resources/">Can't Keep Your Pet?</a></li>
          <li><a href="/sponsor-a-pet/">Sponsor a Pet</a></li>
          <li><a href="/gift-my-adoption/">Gift My Adoption</a></li>
          <!-- TASK-2c END -->
          <li><a href="https://docs.petcenterco.com/returns-refunds/"
```

**Header dropdown JS controller**

**Old code:**
```js
// Mobile Menu Toggle — canonical implementation (Section 2.4)
```
**New code:**
```js
// TASK-2c START — Section 24 dropdown controller: click to open, outside-click/Escape to close,
// flips upward if there is no room below, closes on scroll/resize
(function () {
  var dropdowns = document.querySelectorAll('.pc-nav-dropdown');
  function closeAll() {
    dropdowns.forEach(function (d) {
      var t = d.querySelector('.pc-nav-dropdown__trigger'), p = d.querySelector('.pc-nav-dropdown__panel');
      t.classList.remove('active'); t.setAttribute('aria-expanded', 'false'); p.classList.remove('open', 'flip-up');
    });
  }
  dropdowns.forEach(function (dd) {
    var trigger = dd.querySelector('.pc-nav-dropdown__trigger'), panel = dd.querySelector('.pc-nav-dropdown__panel');
    trigger.addEventListener('click', function (e) {
      e.stopPropagation();
      var isOpen = panel.classList.contains('open');
      closeAll();
      if (!isOpen) {
        var rect = trigger.getBoundingClientRect();
        if (window.innerHeight - rect.bottom < 240 && rect.top > 240) { panel.classList.add('flip-up'); }
        panel.classList.add('open'); trigger.classList.add('active'); trigger.setAttribute('aria-expanded', 'true');
      }
    });
  });
  document.addEventListener('click', closeAll);
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeAll(); });
  window.addEventListener('scroll', closeAll, { passive: true });
  window.addEventListener('resize', closeAll);
})();
// TASK-2c END

// Mobile Menu Toggle — canonical implementation (Section 2.4)
```

## New files
- `volunteer/index.html` — New file — in the zip. Volunteer sign-up page: roles, requirements, volunteer FAQ and sign-up form.
- `foster/index.html` — New file — in the zip. Foster a Pet page: who needs fosters, what Pet Center provides, process, FAQ, interest form.
- `events/index.html` — New file — in the zip. Events & Adoption Days page with upcoming-events board read from events-data.json.
- `about/index.html` — New file — in the zip. About Us page: pet store + adoption center + rehoming platform, categories, values, visit info.
- `lost-and-found/index.html` — New file — in the zip. Lost & Found Pets board with report instructions, read from lost-and-found-data.json.
- `rehoming-resources/index.html` — New file — in the zip. Can't Keep Your Pet? page: alternatives, intake process, contact form.
- `species-guide/index.html` — New file — in the zip. Species Care Readiness Guide: per-species readiness meters, checklists, comparison table.
- `success-stories/index.html` — New file — in the zip. Success Stories page: filterable before/after stories read from success-stories-data.json.
- `partners/index.html` — New file — in the zip. Vet & Rescue Partners page: partner categories, featured partners, become-a-partner form.
- `sponsor-a-pet/index.html` — New file — in the zip. Sponsor a Pet page: tiers, pets who could use a sponsor, how it works, FAQ.
- `gift-my-adoption/index.html` — New file — in the zip. Gift My Adoption page: adoption-code lookup, how it works, sample registry preview, FAQ.
- `events/events-data.json` — New file — in the zip. Events board data (admin-edited flat file, same pattern as testimonials.json).
- `lost-and-found/lost-and-found-data.json` — New file — in the zip. Lost & Found board data (admin-edited flat file).
- `success-stories/success-stories-data.json` — New file — in the zip. Success Stories data (admin-edited flat file).

All 14 are in `WEB-PROJECTS-3-4-5-Task-2.zip`, rooted at the site root, together with this completion file (15 files in the zip). Each page's footer/header links use folder-style URLs (for example `/volunteer/`). The three JSON files are fetched by their pages at the same live paths (`/events/events-data.json`, `/lost-and-found/lost-and-found-data.json`, `/success-stories/success-stories-data.json`). No secret, setting or config value is needed by any new file.

## Bugs Found Outside Task Scope
1. `dogs.html`, `birds.html`, `reptiles.html`, `small-mammals.html`, `livestocks.html`, `aquariums.html` (live `pets/<category>/index.html`) and `pets.html` (live `pets/index.html`) still have the old header and footer. They need the same nav changes shown above for `cats.html` (About Us, Partners, the two Section 24 dropdowns, Success Stories, the footer additions and the dropdown script). They were not given to this task, so they were not touched.
2. In the original `cats.html`, the footer grid template had 4 columns for 5 footer blocks (about line 813, `grid-template-columns: 2fr 1fr 1fr 1.5fr`). It sits inside the footer zone this task edits, so it is corrected in the Code Changes above using the canonical value from the summary's Section 8; the same wrong value is likely in the other 6 category pages and `pets.html`.
3. `sponsor-a-pet/index.html` shows a short fixed list of example pets instead of live listings; wiring it to the real per-category `index.json` / `pet.json` needs confirmation of the exact data path and is outside this task.
4. `gift-my-adoption/index.html` has an adoption-code field with no endpoint to check a code against a real pending application, so it always shows "not found". A lookup endpoint in the application/status system is needed.
5. The forms on `volunteer`, `foster`, `rehoming-resources` and `partners` check their fields in the browser and then show a confirmation message pointing to `docs.petcenterco.com/contact-us`; nothing is sent to a server. A real handler (for example modelled on `apply/adoption-application.php`) is needed if submissions must be stored or emailed.

## Pending, Open Items and Doubts (everything that still makes this work incomplete)
**Must be fixed or confirmed before go-live**
1. **All numbers, names and stories on the new pages are invented placeholder content, not real Pet Center data.** This includes: the stat strips (for example "180+ volunteers", "6,200 volunteer hours", "95 foster homes", "2,400+ adoptions", "98% still in their home", "4.9/5", the 18/9/6/12 partner counts, "40+ events", "1,900+ pets met"); the volunteer quote cards (Renee A., Marcus T., Priya D.); the featured partners (Beverly Hills Animal Hospital, Westside Small Animal Rescue, Pawsitive Steps Dog Training, SafePaws Transport Co., Golden Triangle Pet Supply, Beverly Hills Reptile & Aquatics); the sponsorship prices ($25 / $60 / any amount); the example lost/found animals, events and success stories in the three JSON files; and the example pets on the Sponsor a Pet page. None of this came from the project documents. The CEO must supply real figures and names, or the items must be removed, before the pages go live.
2. **Pet Center business facts I assumed and need confirmed:** that every pet is vaccinated, microchipped and spayed/neutered at events; that Pet Center takes owner surrenders by appointment (Can't Keep Your Pet? page); volunteer minimum age 16 and one-shift-per-month commitment; foster length of 3–6 weeks and that Pet Center covers food and vet care for fosters; that Pet Center places "livestocks" and aquarium pets through the same rehoming flows; that sponsorship money goes "100%" to the pet's care; that refunds/redirects of Gift My Adoption contributions work as described. These are all stated as fact in the page copy.
3. **No real submission, payment or lookup exists behind any of these pages:** the volunteer, foster, rehoming-resources and partner forms send nothing; the "Sponsor" buttons only link to `docs.petcenterco.com/contact-us` — there is no payment processing for sponsorships or gifts; the Gift My Adoption code field has no backend. Each needs a server endpoint (and for money, a payment provider) that does not exist yet and was not in this task's scope.
4. **Pages that were requested but were not on Task 1's revised finalized list:** Partners, Sponsor a Pet and Gift My Adoption were built because the CEO explicitly asked for them after the list changed. Web research (done before building) showed Sponsor a Pet is a very common existing program (for example the Petfinder Foundation), so it is not a unique idea; Gift My Adoption is close to personal adoption-crowdfunding pages and is distinct from Sponsor a Pet only because it is tied to one applicant's adoption code. If Task 1's revised list is the final word, these three folders can simply be left out of the deploy — nothing else depends on them except their links in the shared header/footer (`Partners`, `Sponsor a Pet`, `Gift My Adoption`), which would then need removing.
5. **Nav changes are only on `cats.html` plus the new pages** (see Bugs Found Outside Task Scope, item 1). Until the other 6 category pages and `pets.html` get the same patch, visitors will see two different headers depending on the page. The integrator should treat that patch as required follow-up work.

**New findings and smaller notes**
6. The "Partners" top-level link and the "Gift My Adoption" footer-only placement are my own decisions (Task 1's revised nav plan does not cover them). Please confirm or move them.
7. The Get Involved dropdown (5 items) and Our Pets dropdown (7 items) follow Section 24's rules; Our Pets scrolls because it is over the 5-item cap. The dropdown script is repeated inside each page (there is no shared JS file in the documented structure); if a shared file is wanted later it would be a new path under `assets/js/`.
8. The new pages load Font Awesome 6.4.2 from cdnjs themselves, the same version `chat-widget.js` injects, so it may be requested twice on a page. It is harmless but could be tidied when a shared head include exists.
9. Page images: the pages use only icons, no photos. Real photos (events, partners, success stories) would need new image files under `assets/images/`, which were not created.
10. The CEO asked at one point for real emoji; I did not use them because the design system's rule is "NO emojis — SVG icons only". Icons are Font Awesome (one of the three libraries Section 11 allows). If the rule has changed, the design system document should be updated first.
11. I could not test in a browser (dropdown clicks, mobile layout, the three JSON fetches). The JSON fetches only work once the JSON files are deployed at the paths in the table above.

## Self-Check Confirmation
- Only `cats.html` was changed among existing files; every other file I produced is brand new and listed above. Nothing else was touched.
- Every block in `cats.html` is wrapped in `TASK-2c START` / `TASK-2c END` anchors with a comment saying what it does (12 START and 12 END anchors in the final file); the original `TASK-1` and `TASK-2` anchors were kept in place.
- Every "Old code" snippet was copied from the original `cats.html` I was given, and I checked by script that each appears exactly once in it. I rebuilt the final `cats.html` by applying these exact blocks to the original, and its links and scripts match my working version.
- The zip `WEB-PROJECTS-3-4-5-Task-2.zip` holds 15 files: the 14 brand-new site files at live paths with the site root as the zip root, plus this completion file at the zip root (documentation only). `unzip -l` was checked against the Files Changed table: same paths, nothing missing, nothing extra. It contains no `config.php`, `pet.json`, `.env`, `applications/`, `uploads/`, `chat-media/` or `logs/`, and a search for password/key/token/secret strings found none.
- How I tested: I syntax-checked every page's inline JavaScript with `node --check`, checked tag balance and duplicate ids, checked that all 12 pages carry identical header dropdown, mobile menu and footer link sets (Our Pets 7 / Get Involved 5 / footers 8, 10 and 11 links), and validated the three JSON files. I could not open the pages in a browser, so dropdown clicks and layout were not visually tested.
- Corrections made while preparing this hand-in: (1) the dropdown click script was missing from 9 pages in my earlier delivery (so the dropdowns would not have opened); it is now on all 12 pages and in the `cats.html` block above. (2) I removed an unneeded Font Awesome link from `cats.html`, because `cats.html` uses no Font Awesome icons and the chat widget loads its own copy. (3) I reworded one stale comment above the Get Involved dropdown. The new pages load Font Awesome 6.4.2, the version the chat widget uses.

- Zip name: `WEB-PROJECTS-3-4-5-Task-2.zip` — project name taken from the assignment file `WEB-PROJECTS-3-4-5-TASK-ASSIGNMENTS.md` (Project 3, Task 2), per the CEO's naming instruction.
