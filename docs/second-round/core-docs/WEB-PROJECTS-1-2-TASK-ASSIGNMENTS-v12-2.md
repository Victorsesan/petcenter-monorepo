# SITEWIDE TRANSLATION, SETTINGS WIDGET & CROSS-PAGE STATE PERSISTENCE — TASK ASSIGNMENTS (v12)

**Supersedes v11. Two changes: (1) a real bug fixed — Tasks 5–8 were
missing `index.html` from their file scope, meaning four of five
languages never translated the homepage; (2) every task now has a
"Recommendations & Enhancements" subsection, pulled from this project's
earlier research and reconciled against everything learned since
(structural consolidation, the VDM-2 cross-project findings, the
Chinese decision).**

## How This File Is Structured — read this before your task
Every task below has two parts: **the task itself** (unchanged from
what it's always been, this is the actual assignment) and a
**Recommendations & Enhancements subsection** underneath it. Read both —
the assignment isn't complete if you only read the first part.

⚠️ **The enhancements are a headstart, not a final answer.** They come
from research done before any worker had the actual files or code in
front of them. You own the code now, and you have context this document
doesn't. Treat every enhancement below as a starting point to evaluate,
not an instruction to implement as-is — think it through yourself, and
use web search if you need to check whether it's still the right call
given what you're actually looking at.


## TABLE OF CONTENTS
1. [Introduction](#introduction)
2. [Warning & Instructions](#warning-instructions)
3. [Page Content Reference](#page-reference)
4. [Task 1 — Architecture + Sitewide Key Extraction + Homepage Pilot](#task-1)
5. [Task 2 — Accept-Language First-Visit Detection](#task-2)
6. [Task 3 — CSS Logical Properties Audit](#task-3)
7. [Task 4 — Spanish, Whole Site](#task-4)
8. [Task 5 — Portuguese, Whole Site](#task-5)
9. [Task 6 — French, Whole Site](#task-6)
10. [Task 7 — Arabic, Whole Site + RTL](#task-7)
11. [Task 8 — Chinese, Whole Site](#task-8)
12. [Task 9 — Widget + Persistence Core Build](#task-9)
13. [Task 10 — Widget Rollout, All Remaining Pages](#task-10)
14. [Full Dependency Graph](#dependency-graph)
15. [Model & Settings Recommendations](#model-settings)

---

## 1. Introduction <a name="introduction"></a>
Pet Center runs `adopt.petcenterco.com` — plain HTML/CSS/JS/PHP, no
framework — plus an internal `admin-dashboard.html`. This covers sitewide
translation into **Spanish, Portuguese, French, Arabic, and Chinese**,
plus a floating Settings Widget and the mechanism keeping its settings
across page navigation. Two documents underpin every task:
`adoption-website-project-summary.md` (full site documentation) and
`ADMIN_DASHBOARD_PROJECT_SUMMARY.md` (the admin tool).

---

## 2. Warning & Instructions <a name="warning-instructions"></a>

1. **Self-question before and after every task.**
2. **Never use emojis anywhere in the page or the code.** Icons only.
3. **Never create a `.md` or `.txt` file unless explicitly told to** —
   except your required completion file.
4. **Carry these instructions through every command you run.**
5. **Every page is deployed as a folder path, never a bare filename in
   the URL** — e.g. `pet-detail.html` → `/pets/detail/`. Refer to files
   by their real source filename.
6. **Write code comments on everything you touch.**
7. **Never recreate a feature from scratch.** Reuse existing code for the
   hamburger menu (canonical: `cats.html`), Bottom Sheet + Filter +
   Search, signature pad, PDF generation, admin patterns.
8. **Task completion file required**, named `Task-[N]-Completion.md`
   matching your assigned task number exactly. Contents: Task Number;
   What Was Required; Files Changed (table, noting anything shared with
   another task); Code Changes (old/new snippet per edit, wrapped in
   `<!-- TASK-[N] START/END -->`); Bugs Found Outside Scope (or "None
   found"); Self-Check Confirmation.
9. **Scope Lock.** Touch only the files listed in your assigned task.
10. **Consult `PETCENTER_DESIGN_SYSTEM_v1.0.md`** before any real
    UI/styling/animation decision — plain content translation generally
    doesn't trigger this.
11. **Image/reference-material gate.** Missing a needed reference? Stop
    and wait.
12. **Use web search during implementation.**
13. **Sequential File Ownership Chain with Anchor Comments** for any
    file more than one task touches — named zones,
    `<!-- TASK-[N] START/END -->` blocks, pull the most recent version
    if not first in line.
14. **Use `cats.html`** as the canonical header/footer/hamburger
    blueprint for any pet-related page.
15. **Files come from the CEO via chat, by exact filename** — never
    paraphrased.
16. **Shared schemas fixed once, reused, never reinvented:** the
    `preferences` object (`{lang, opacity}`, defined in Task 9) and the
    sitewide string-key inventory (extracted once in Task 1, reused
    verbatim by every language).
17. **Two existing sitewide elements matter to new floating UI:** a
    floating contact button at `z-index: 4000` (fixed, bottom-right);
    filter/form sheets at `z-index: 6000`/`6001` (current highest).

---

## 3. Page Content Reference <a name="page-reference"></a>
Written once here; every translation task translates all of these plus
`admin-dashboard.html`.

- **`index.html` (homepage):** hero CTA "Looking to adopt? Find available
  pets →"; promo carousel, 6 services in fixed order (Flexible
  Payments/BNPL, Shipping & Delivery, Foster or List a Pet, Health
  Guarantee, Adoption Starter Kit, Post-Adoption Support), each with a
  "Read More" button; testimonials carousel; pet-showcase with the
  sitewide "ADOPTED" badge; paw-icon preloader.
- **`pets/index.html`:** navy hero, breadcrumb, 7-card category grid,
  each with a live "N available"/"Check back soon" count.
- **`services/index.html`:** full write-ups of the same 6 services.
- **7 category pages** (`cats.html`, `dogs.html`, `birds.html`,
  `reptiles.html`, `small-mammals.html`, `livestocks.html`,
  `aquariums.html`): identical structure across all 7 — banner + search +
  Filters button, card grid, Filter bottom sheet (Sort By, Breed,
  Adoption Fee, Age, Size, Compatible With), "Posted on" date + "ADOPTED"
  badge + "Signin" button per card, two distinct empty states.
- **`pet-detail.html`:** image gallery, Tips card, info panel
  (Breed/Physical Traits/Behavior/Health/Compatibility/Actions),
  compatibility ring, vibe badge, sticky price+Apply bar, "You Might Also
  Like" grid, full-page Adopted treatment.
- **`Testimonials.html`:** star rating + review count, live Open/Closed
  status, review cards (rating, relative time, topic chips, "X people
  agreed to this"/"Y people didn't agree to this"), admin replies,
  submission form.
- **`adoption-application.html`:** 5-step form, multi-pet carousel, file
  uploads, signature pad, generated PDF confirmation — **PDF in scope.**
- **`adoptions/status/index.html`:** PENDING/APPROVED states; APPROVED
  payment copy is **business-approved wording — translate precisely.**
- **`signin/index.html`:** "Coming Soon" placeholder, "Notify Me" form,
  4 feature blurbs.
- **`blog-home.html`, `blog-single.html`, `blogs-data.js`:** landing
  grid + category filter, article page + tag system, both driven by
  exported helper functions in `blogs-data.js` — reuse, don't re-derive.
- **`admin-dashboard.html`:** internal admin tool, in translation scope
  same as every public page.

All translation tasks: content only — never alter HTML tags, class
names, IDs, or CSS.

---

## Task 1 — Architecture + Sitewide Key Extraction + Homepage Pilot <a name="task-1"></a>
**Read First:** `adoption-website-project-summary.md`, in full.
**Files to Request:** `adoption-website-project-summary.md`, all page
sources (Section 3), `admin-dashboard.html`.
**Files touched:** `index.html` (translated, Spanish) + `/en/`–`/zh/`
scaffolding.

Build the JSON string-manifest schema + stamp script, `hreflang`
generation, and the `localStorage` preference/redirect system
(`preferences.lang`, from Task 9's schema). Extract the canonical
string-key inventory for the entire site plus `admin-dashboard.html` —
keys assigned once here, reused by every language task. Apply the
finished pattern to the homepage only, translated into Spanish, as
proof it works end-to-end. **Largest single task here** — flag if not
substantially done by day 2.

**Recommendations & Enhancements for Task 1:**
⚠️ Headstart only, not final — see the note at the top of this file.
- Two hreflang details worth getting right from the start, since every later language inherits whatever pattern you set here: each language page's canonical tag should point at *itself*, not at the English page — pointing everything at English tells search engines the translated pages are duplicate content. Also include an `x-default` entry for visitors whose browser language matches none of the five.
- When assigning keys during the sitewide extraction, give the 7 category-listing pages (`cats.html`, `dogs.html`, etc.) the *same* keys for their shared elements (filter labels, sort labels, empty-state text) rather than 7 separate per-page keys — this is what makes the "one shared strings file" idea further down actually work for whoever translates them.
- Whatever the reasoning was for `localStorage` over cookies, put it in a code comment — a one-liner is enough. It stops someone "fixing" it into a cookie later and accidentally triggering a consent-banner requirement that didn't need to exist.

## Task 2 — Accept-Language First-Visit Detection <a name="task-2"></a>
**Read First:** `adoption-website-project-summary.md` — Section 1.
**Files to Request:** `adoption-website-project-summary.md`.
**Depends on:** Task 1.
Redirect a first-time visitor by browser `Accept-Language`. Confirm PHP
availability per-page first; escalate if it would need per-page
duplication with no shared include.

**Recommendations & Enhancements for Task 2:**
⚠️ Headstart only, not final — see the note at the top of this file.
None beyond what's already in the task description above — this task exists specifically because of earlier research, so there's nothing further sitting outside its scope.

## Task 3 — CSS Logical Properties Audit (sitewide, once) <a name="task-3"></a>
**Read First:** `PETCENTER_DESIGN_SYSTEM_v1.0.md`,
`adoption-website-project-summary.md`.
**Files to Request:** `PETCENTER_DESIGN_SYSTEM_v1.0.md`, the site's CSS
(confirm shared vs. inline per-page with the CEO first).
Convert physical properties to logical equivalents so `dir="rtl"` works
without a parallel stylesheet. Must complete before Task 7.

**Recommendations & Enhancements for Task 3:**
⚠️ Headstart only, not final — see the note at the top of this file.
None — this entire task exists because of earlier research; there's nothing left outside it.

## Task 4 — Spanish, Whole Site <a name="task-4"></a>
**Read First:** `adoption-website-project-summary.md`, in full.
**Files to Request:** all pages in Section 3 except `index.html`,
`admin-dashboard.html`.
**Depends on:** Task 1, Task 10.
Translate everything remaining using Task 1's key inventory — don't
invent new keys. Up to 30% longer than English; audit overflow against
the rendered page.

**Recommendations & Enhancements for Task 4:**
⚠️ Headstart only, not final — see the note at the top of this file.
- Content scope, stated explicitly since it's easy to under-read "translate the page": this includes toast notifications, warning banners, form validation error messages, `alt` text, `title` attributes, `aria-label`s, and placeholder text — not just the text visibly sitting on the page.
- If Task 1 assigned shared keys to the 7 category pages, use one shared strings reference for all 7 rather than translating the same filter/sort/empty-state text 7 separate times.
- `Intl.NumberFormat('es')` for any displayed counts, `Intl.DateTimeFormat`/`Intl.RelativeTimeFormat('es')` for adoption dates and "posted X days ago" — both native browser APIs, no dependency needed.
- Star-rating controls need a translated `aria-label` specifically ("4 out of 5 stars" or equivalent) — easy to miss since it's invisible in a normal visual read-through.
- Blog content (`blog-home.html`, `blog-single.html`) is editorial prose, not UI strings — worth a distinct proofread pass before calling it done, since a long-form error is more visible and more damaging than a mistranslated button.

## Task 5 — Portuguese, Whole Site <a name="task-5"></a>
**Files to Request:** all pages in Section 3 **including `index.html`**,
`admin-dashboard.html`. (Unlike Task 4, this language has no homepage
pilot to inherit from — `index.html` is fully in scope here.)
Same dependencies as Task 4. **Not dependent on Task 4** — fully parallel.

**Recommendations & Enhancements for Task 5:**
⚠️ Headstart only, not final — see the note at the top of this file.
Same items as Task 4's list, applied with `'pt'` in place of `'es'` for the `Intl` calls — content scope, shared strings for the 7 category pages, star-rating `aria-label`s, blog proofread pass.

## Task 6 — French, Whole Site <a name="task-6"></a>
Same as Task 5 — **`index.html` included**, same reason — plus
non-breaking space before `! ? : ;` and inside `« guillemets »`.

**Recommendations & Enhancements for Task 6:**
⚠️ Headstart only, not final — see the note at the top of this file.
Same items as Task 4/5's list (with `'fr'`), plus: the non-breaking-space check is most visible on `Testimonials.html`'s review quotes and on the application form's validation messages — worth checking those two specifically rather than assuming a general pass caught it.

## Task 7 — Arabic, Whole Site + RTL <a name="task-7"></a>
Same scope/files as Task 5 — **`index.html` included**. **Depends on
Task 3** as well as 1 and 10. Full
RTL: `dir="rtl"`, Cairo or Tajawal webfont, carousel/gallery/filter icons
mirror, star ratings and the signature-pad canvas do **not** mirror,
email/phone inputs stay `dir="ltr"`, deliberate Western vs. Eastern
Arabic-Indic numeral choice. **Flagged honestly:** the one task least
certain to fit 3 days even under this model — RTL verification is real
per-page work independent of translation volume. Split it if it doesn't
fit; don't force it.

**Recommendations & Enhancements for Task 7:**
⚠️ Headstart only, not final — see the note at the top of this file.
- Same content-scope, shared-strings, and blog-proofread items as Task 4's list.
- `Intl.NumberFormat`/`Intl.DateTimeFormat` calls should use whichever Arabic locale subtag matches your numeral decision (e.g. `'ar-SA'` renders Eastern Arabic-Indic digits by default, `'ar-EG'` varies) — check the actual output against your numeral choice rather than assuming the locale tag alone settles it.
- Star-rating `aria-label`s need Arabic translation same as every other language — and, per the note already in this task's core scope, the rating *display* itself should stay unmirrored even though the label text is translated.

## Task 8 — Chinese, Whole Site <a name="task-8"></a>
**Language code: `zh-Hans` (Simplified Chinese) — confirmed by the CEO,
unconditional.** Use `zh-Hans` — not a bare `zh` — everywhere this
project stores or exposes the Chinese language value, including
`preferences.lang` (Task 9's schema) and any directory/hreflang naming.
This value is also read by Admin-Project #4's `preferred_chat_language`
field (a separate, independent project) — confirmed as matching on both
sides as of this version.
Same scope/files as Task 5 — **`index.html` included** (same reason:
no homepage pilot exists for Chinese). Plus: the 30%-overflow rule doesn't apply —
CJK text tends to run more compact than English, so check font
legibility/line-height instead. No RTL, no dependency on Task 3.

**Recommendations & Enhancements for Task 8:**
⚠️ Headstart only, not final — see the note at the top of this file.
- Same content-scope, shared-strings, star-rating `aria-label`, and blog-proofread items as the other languages' lists, with `'zh-Hans'` as the locale tag for any `Intl` calls.
- Since CJK translations tend to run shorter than English rather than longer, it may be worth checking whether elements sized for the 30%-overflow case (buttons, fixed-width containers) look sparse or oddly spaced at the other end — not a defect the other four languages' QA process would catch, since it's the opposite direction.

## Task 9 — Widget + Persistence Core Build (Homepage pilot) <a name="task-9"></a>
**Read First:** `adoption-website-project-summary.md`,
`ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, `PETCENTER_DESIGN_SYSTEM_v1.0.md`.
**Files to Request:** all three documents, `index.html`,
`admin-dashboard.html`.
Build the Settings Widget (gear icon, central position — z-index checked
against the site's real highest value, 6001, not just the chat button's
4000), language switcher + opacity slider, admin-dashboard default-
opacity control. `rgba()` backgrounds, not container `opacity`;
`role="slider"` + keyboard support; cross-document View Transitions API
(`@view-transition { navigation: auto; }`); **defines the shared
`preferences` object** — `{lang, opacity}`; native `storage` event for
cross-tab sync. **`preferences.lang` is now read by a second, independent
project** (Admin-Project #4's chat language preference, as its
default-source priority check). Confirmed format on both sides: storage
mechanism `localStorage`, key `preferences`, field `lang`; values are
lowercase ISO 639-1 codes (`es`, `pt`, `fr`, `ar`) plus `zh-Hans` for
Chinese (tagged, not bare `zh`, confirmed by the CEO). Any future change
to this schema must be communicated to Admin-Project #4 before shipping,
not just documented here.

**Recommendations & Enhancements for Task 9:**
⚠️ Headstart only, not final — see the note at the top of this file.
None beyond what's already in the task description — everything researched for this task (View Transitions, the unified schema, `rgba()` backgrounds, slider accessibility, the `storage` event) is already a stated requirement above, not an optional extra.

## Task 10 — Widget Rollout, All Remaining Pages <a name="task-10"></a>
**Read First:** `adoption-website-project-summary.md`,
`PETCENTER_DESIGN_SYSTEM_v1.0.md`.
**Files to Request:** every page in Section 3 except `index.html`.
**Depends on:** Task 9 only.
Insert widget markup + View Transitions/`preferences` wiring into every
remaining page, Playwright-verified, plus an `axe-core` scan on the same
pass. Every translation task (4–8) depends on this — get it done early.
Confirm no collision with the signature pad/toasts or any blog scroll
indicator (unverified — check the real file).

**Recommendations & Enhancements for Task 10:**
⚠️ Headstart only, not final — see the note at the top of this file.
None beyond what's already in the task description — the `axe-core` scan and both collision checks are already stated requirements, not optional additions.

---

## Full Dependency Graph <a name="dependency-graph"></a>
9 → 10 · 1 → 2 · (1 + 10) → 4, 5, 6, 8 · (1 + 10 + 3) → 7.
**Tasks 4, 5, 6, 7, and 8 have no dependency on each other — fully
parallel once their shared prerequisites are done.**

## Model & Settings Recommendations <a name="model-settings"></a>
Claude Sonnet 5. Extended thinking on for Tasks 1, 3, 7, 9. Web search on
for Task 2 and for Task 8's Simplified/Traditional decision and current
CJK web-font guidance.
