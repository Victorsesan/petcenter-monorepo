# Task Completion — Accessibility Widget Rebuild

## Task Number
No number in `WEB-PROJECTS-1-2-TASK-ASSIGNMENTS-v12.md` — this was a direct follow-on request, same category as the earlier Color Vision Accessibility task.

## What Was Required
A full rebuild of the Settings Widget into a real accessibility widget, using two competing products (UserWay, Accessora) as design reference — but not a copy of either: a specific, curated feature set drawn from both, deliberately excluding several of their controls, restructured into a compact accordion instead of their long static desktop panel, plus an architecture decision on widget-script vs. per-page embedding.

## Architecture Decision
**Single embeddable script** (`assets/js/accessibility-widget.js`), loaded via one `<script src>` tag, building its own HTML/CSS at runtime. Reasoning: Task 10 already paid the real cost of the copy-into-every-page approach once — 15 files, resynced twice as the widget changed. A single script file means the next revision is a one-line include swap, not a 15-file resync. This rebuild is itself evidence for the decision: it replaces a widget that had already drifted across two files and needed careful re-synchronization even for this response.

## What's In, What's Out, and Why

| Section | Included | Deliberately excluded |
|---|---|---|
| Language | dropdown (carried over unchanged) | — |
| Profiles | Low Vision, Dyslexia, ADHD, Motor Impairment, Seizure & Epileptic — each a preset bundle of the controls below | Blind, Cognitive (redundant with the above given this site's scope) |
| Visual Adjustments | Font Size stepper (50–200%, Accessora-style, not UserWay's single toggle), Contrast (cycle: Normal/Dark/Light), Saturation (cycle: Normal/Low/High/Desaturate), Text Spacing (cycle: Normal/Light/Moderate/Heavy), Hide Images, Highlight Links, Invert Colors, Dyslexia Friendly Font | Dictionary, Screen Reader, Line Height, Page Structure, Text Align, standalone Pause Animations — all per your explicit list |
| Color & Vision | the already-verified Deuteranopia/Protanopia/Tritanopia system, plus Grayscale added as a fifth option | — |
| Navigation & Motor | Big Cursor, Reading Guide | — |
| More Settings | Widget Position (Left/Right), Widget Icon Opacity, Widget Background Opacity, Reset All | (renamed from "Move Accessibility Widget") |
| Accessibility Statement | button + back-navigable panel, placeholder WCAG 2.1 AA / ADA / Section 508 content | — |

**On ADHD and Seizure & Epileptic specifically**: both need motion reduction to do anything real (that's the entire point of a seizure-safety profile), but you explicitly excluded a standalone "Pause Animations" tile. Resolution: motion reduction lives *only* inside those two profiles as an internal effect (`data-a11y-reduce-motion`), never exposed as its own control. A seizure-safety profile that didn't actually stop flashing/animated content would be worse than not shipping one, so this felt like the right place to diverge from a literal reading of the exclusion list rather than skip the profile's actual purpose.

## Files Changed

| File | What changed |
|---|---|
| `assets/js/accessibility-widget.js` | **New file.** The entire widget — CSS, markup, and logic — self-contained, ~550 lines. |
| `index.html` | Removed the old inline Settings Widget block (Task 9 + Color Vision Accessibility task's CSS/HTML/JS, now redundant/superseded) and the now-unused `--warning`/`--info` variables + `[data-vision]` override blocks (confirmed nothing else in this file used them). Added one `<script src="/assets/js/accessibility-widget.js" defer>` tag. Kept the `@view-transition` block (Task 9) — unrelated, independent functionality. |
| `admin-dashboard.html` | Removed the "Your Preferences" personal Color Vision dropdown from the Site Settings popover (markup, its CSS, and its JS wiring) — superseded by the full widget now also loaded on this page. **Kept** the two admin-wide default-opacity sliders (Default Widget Background Opacity, Default Widget Opacity) — genuinely different concept (what first-time visitors see), no equivalent in the new script. **Kept** `--warning`/`--info` variables and their `[data-vision]` overrides — these are load-bearing for `.pc-badge--pending`, `.pc-alert--warning`, and `PC.toast()`, unlike in `index.html`. Added the same single script tag near the end of `<body>`. |

## Two Real Bugs Found and Fixed During This Build's Own Verification

Both were caught by actually testing the built widget against real page content with Playwright, not by inspection:

1. **The widget inherited its own page-level filter effects.** Dark Contrast (`invert(1) hue-rotate(180deg)`) was first applied to `<html>`, and since the widget is a DOM descendant of `<html>`, it got inverted too — the navy panel turned pale lavender and became hard to read, exactly backwards for an accessibility control. Screenshotted, confirmed, and fixed by wrapping all pre-existing page content in a dedicated `#pcA11yPageWrap` element at init time and applying `filter`/`zoom`/`cursor` to that wrapper instead of `<html>` — the widget itself (appended as a sibling, not a child, of that wrapper) never inherits them. `data-a11y-*` attribute-based CSS rules didn't have this problem since their selectors already excluded `.pc-a11y-widget` explicitly.

2. **The wrapper fix above had its own gap**: with no explicit background of its own, page areas with no set background color (very common) showed the real, unfiltered `<body>` background through the transparent gaps — meaning Dark Contrast silently failed to invert those regions. Caught by pixel-sampling a supposedly-inverted light area and finding it unchanged. Fixed by reading `<body>`'s (or `<html>`'s) real computed background color at init and setting it explicitly as the wrapper's own opaque background, so the whole page is genuinely inside the filtered subtree.

## One Honesty Flag: Font Size via `zoom`, Not `rem`

This codebase's CSS is mostly fixed `px`, not `rem` — the usual accessibility-widget trick of scaling root `rem` would barely move anything here. Implemented via `zoom` instead (scales `px` layouts correctly, unlike `transform: scale` which breaks document flow/scrollbars). `zoom` has real but improving cross-browser support (Chromium/Edge, Safari 17+, modern Firefox) — this is the correct call for what this codebase actually needs, but it's worth a fresh check against current browser support numbers before this ships, not assumed permanently settled.

## Schema Changes

`preferences` extended from `{lang, opacity, widgetOpacity, colorVision}` to also include `fontSize, contrast, saturation, textSpacing, hideImages, highlightLinks, invertColors, bigCursor, readingGuide, widgetPosition, activeProfile, dyslexiaFont`. `lang`'s existing meaning/values are completely unchanged (still consumed by `i18n-core.js`/`lang-redirect.js` and Admin-Project #4's `preferred_chat_language`).

**`widgetOpacity`'s meaning changed**, same field name: it used to mean "opacity of the whole widget, trigger and open panel together" (Task 9's original build). Per this rebuild's brief ("Widget Icon Opacity" as its own control, separate from the panel background), it now applies only to the trigger button — the open panel is always fully visible, since a half-transparent control panel while you're actively using it is a usability problem worth not having. Documented in the script's own header comment so this isn't a silent, undiscoverable change.

## Verification Performed
- Structural lint (BeautifulSoup, zero duplicate IDs) and JS syntax check (`new Function()`) on all three files after every edit.
- Built a standalone test harness page first to verify the widget script in isolation (accordion behavior — confirmed only one section open at a time, not a timing illusion; profile apply/toggle-off correctly bundles and reverts only its own fields; font stepper; contrast/saturation cycles; statement modal open/back; widget position switch with bounding-box confirmation; reading guide following the mouse; grayscale color-vision option; full-cycle reload persistence).
- Re-verified against the **real** `index.html` and `admin-dashboard.html` (not just the harness) — root-absolute `/assets/js/...` script paths don't resolve under `file://` (expected; they're the correct, already-established pattern for real deployment, matching `i18n-core.js`/`chat-widget.js`), so verification used a relative-path test copy for the `file://` sandbox only, confirmed against the actual page content, nav, and fonts, zero console errors either time.
- Confirmed the admin-only "Site Settings" defaults popover still works correctly and independently alongside the new full widget on the same page, with the removed Color Vision sub-section's markup/JS genuinely gone (`#dashVisionTrigger` count: 0).

## Bugs Found Outside This Task's Scope
None found beyond what's already logged in the Task 9 and Color Vision Accessibility completion files (Lavender Mist palette discrepancy pending CEO confirmation; admin login gate needs a live backend unavailable in-sandbox).

## Self-Check Confirmation
- Every screenshot described in the request was accounted for in the feature table above, with an explicit include/exclude decision for each, not a partial pass.
- No emojis anywhere in code, comments, or this file.
- Scope stayed to the three files above; nothing else touched.

## Revision 1 — re-review pass, per user follow-up

User pushed back on the widget-vs-code architecture and asked for a genuine screenshot-by-screenshot re-review rather than a restated summary. Findings and fixes:

**Genuine misses found:**
1. **"Fast Reading" (Accessora) is Bionic Reading** — bolds the first ~50% of each word. Confirmed via search rather than left as a guess (the user's own instruction from the original request). Added as a real control: walks text nodes under the page wrapper, wraps the bolded portion in `<b class="pc-a11y-bionic">`, and cleanly reverses it on toggle-off (`parent.normalize()` re-merges text nodes so the DOM is byte-identical to before, verified by direct text comparison, not assumed). Wired into the ADHD and Dyslexia profiles, both of which are specifically documented (and shown in the UserWay reference) as benefiting from it.
2. **UserWay collapses its Profile picker into a single "[Profile] active ▾" row once one is selected**, instead of leaving all profile buttons expanded (missed originally because I was only checking "what controls does the profile flip," not "how does the panel's own layout change"). Implemented the same behavior: selecting a profile collapses the list to one summary row; clicking it re-expands the full picker without deactivating the current profile.
3. **Heavy Text Spacing in the reference is far more extreme** (near letter-by-letter) than originally built. Intensified `letter-spacing`/`word-spacing`/`line-height` for the heavy tier to match.

**Two real bugs caught fixing the above (both via Playwright, not inspection):**
- Replacing the profile list's HTML synchronously inside its own click handler detached the clicked button from the DOM mid-event; the document-level "click outside closes the panel" listener then read the now-parentless button as an outside click and closed the whole panel. Fixed with `e.stopPropagation()` on the list's click handler.
- The heavy-spacing (and dyslexia-font) rules use `:not(.pc-a11y-widget):not(.pc-a11y-widget *)` to exclude the widget — which correctly stops the rule from directly *targeting* anything inside the widget, but `letter-spacing`/`word-spacing`/`line-height`/`font-family` are all *inherited* CSS properties. The rule still applies to `<body>` (which isn't excluded, since the widget is `body`'s descendant, not the reverse), and inheritance carried it straight down into the widget regardless of the exclusion. Confirmed by pixel/style inspection showing the widget's own header text inheriting 6.4px letter-spacing. Fixed with an explicit `.pc-a11y-widget, .pc-a11y-widget * { letter-spacing: normal !important; ... }` firewall rule, which wins because it directly targets those elements rather than relying on exclusion + inheritance.

**Judgment calls surfaced, not silently changed:** Accessora's ADHD profile bundle (Hide Images + High Contrast) vs. this build's (Hide Images + Highlight Links + now Fast Reading) — kept the existing choice since forcing a full contrast/theme change felt like a bigger visual change than the profile implies, but flagged as a genuine either-way call rather than presented as settled.

**On the architecture question itself:** reaffirmed with concrete mechanics (CSS `filter` is a real pixel-level transform with no server-side equivalent for instant, reversible, per-visitor display changes; the site's own color-vision system swaps real CSS variables, not a blanket filter) plus external validation — searched several real accessibility-widget codebases (Sienna, ja11y, AccessiBit, Mobirise) and confirmed every one is architected the same way, client-side CSS/DOM manipulation. No further code change from this point; it was a reasoning question, not an implementation gap.

## Revision 2 — Real Dark Mode (Section 19), plus the ADHD fix

**What was missed and why it's a real miss, not a style difference:** Section 19 documents a complete, ready-to-build Dark Mode spec — five rules, exact palette, exact CSS variables, exact toggle behavior and transition timing — and unlike Color Vision (which the same section explicitly defers to "a full task brief... provided separately"), nothing about Dark Mode was deferred. It was fully specified the entire time. I had already built the Settings Widget itself by the time of the Accessibility Widget Rebuild, which is exactly where Section 19 says this control belongs ("once that component is specified") — so the one condition the document set for building it was already satisfied, and it still didn't get built. That's a genuine oversight, not a scope judgment call.

**What "Dark Contrast" (already built) is NOT the same as:** the existing Contrast cycle's "Dark" option is a `invert(1) hue-rotate(180deg)` filter — UserWay's own literal control, kept as-is. It doesn't follow Section 19's rules at all: colors stay fully saturated (violates Rule 4), there's no dedicated shadow handling (Rule 1), and it doesn't use the documented `#121212`/`#1c1c1c`/`#F2F2F2` palette. Presenting that as "Dark Mode" would have been a second, deeper miss, so this is a genuinely separate, additional control, not a rename of something that already existed.

**What was actually checked before implementing:** grepped this file's own CSS and found 144 hardcoded hex color declarations against only 7 uses of its own CSS variables. A variable-only implementation (just defining `:root[data-theme="dark"]` and calling it done) would have visibly changed almost nothing on the real page. Confirmed before writing any code, not discovered by trial and error.

**What was built:**
- The exact documented CSS variables (`--dark-bg: #121212`, `--dark-surface: #1c1c1c`, `--dark-text: #F2F2F2`, etc.) and the exact 0.3s cubic-bezier transition Section 19 specifies.
- This page's own base tokens (`--bg`, `--surface`, `--text`, `--muted`, and the functional colors) re-pointed at the dark equivalents under `[data-theme="dark"]` — covers every spot already variable-driven for free, `<body>` included.
- A real DOM-level pass (`applyRealDarkMode()`) for the other 137+ hardcoded spots: walks every element under the page wrapper, and for any element whose own `background-color` computes to a light color, remaps it to `var(--dark-surface)`, storing the original for a clean, verified reversal on toggle-off. Deliberately scoped to `background-color` only — `color` inherits, so the same check can't reliably tell "this element's own dark text" from "inherited from a parent," and a blanket rewrite risked breaking already-correct light-on-dark text (e.g. inside buttons). This is a real, disclosed limitation, not a silent gap: a heading or accent that hardcodes its own navy/brown text color won't be remapped and may read as lower-contrast against the new dark surfaces — confirmed visible in testing (e.g. "Check Your Application" heading), not discovered after the fact.
- The toggle itself as a top-level control (not buried in an accordion section, matching Section 19's own framing of it as a primary widget control) using the exact "Dark Mode / Switches the interface to a low-light color scheme" wording from the document's own example.
- `preferences.theme` (`'light'|'dark'`), persisted and cross-tab synced the same as every other field.

**Verified via Playwright against the real `index.html`** (not a harness): screenshotted before/after — header and hero stay visually similar since they're already dark-styled by the site's own brand design (correct, expected, not a bug); the "Check Your Application" and card sections below genuinely convert to the documented dark palette, not an inverted/hue-shifted look; brand orange CTAs stay as small accents, not full fills, matching Rule 5; a full toggle-on-then-off cycle leaves zero leftover override markers and the page pixel-identical to before, confirmed by comparing computed background-color, not assumed.

## ADHD Profile — per your instruction to match the reference where simple

Added `contrast: 'dark'` to the ADHD profile bundle, using the Contrast cycle that already existed rather than building a new "High Contrast" mode — matches Accessora's Hide Images + High Contrast bundle closely enough without extra scope. ADHD is now: Hide Images, Highlight Links, Fast Reading, Dark Contrast, plus the existing internal motion reduction.

## Revision 3 — two bugs from live testing: default state looked pre-selected, and the panel didn't scroll correctly on touch

### "Some features looked toggled on by default" — a real bug, isolated and confirmed

Tested with `localStorage` fully cleared (a genuinely fresh visitor) before assuming anything: every real toggle (Hide Images, Highlight Links, Invert Colors, Dyslexia Font, Fast Reading, Big Cursor, Reading Guide, Dark Mode) was confirmed **off**, and every cycle control (Contrast, Saturation, Text Spacing) was confirmed at **Normal**. So the underlying preference state was correct — but the visual rendering of the cycle controls' progress-dots was not: `renderDots()` filled dot index 0 whenever the current value's index was `>= 0`, which is true even at the default (index 0) state, so **every cycle control showed one segment lit even when nothing had been changed from default.** That's a real, confirmed rendering bug, not a misunderstanding — fixed so index 0 (always "Normal"/off for every cycle here) renders with zero dots filled, and dots only start filling from the first non-default step onward. Re-verified against a freshly-cleared page: all three cycles now show zero active dots at default.

The separate possibility — that the user's browser had genuinely saved a non-default `preferences` value from an earlier round of testing on the same published artifact, which would look identical to "pre-selected" without being a code bug — can't be ruled out from here, since `localStorage` persists per-viewer across visits. Both explanations likely contributed; only the dots bug was something in the code to fix, and it's fixed.

### The panel didn't scroll correctly on touch — extracted this codebase's own existing fix rather than inventing one

Per the explicit instruction, read `cats.html`'s mobile hamburger menu (`.pc-mobile-menu.open`) rather than solving this from scratch — its CSS already carries the exact fix this exact class of bug needs:

```css
overflow-y: auto;
overscroll-behavior: contain;
-webkit-overflow-scrolling: touch;
```

Applied the same three declarations to `.pc-a11y-panel__body`, the widget's own scrollable container. `overscroll-behavior: contain` is the specific property that stops a scroll gesture from "chaining" past the end of the panel's own scroll range into scrolling the page underneath it — the exact symptom reported.

Also extracted the **other** half of `cats.html`'s pattern, not just the CSS: its `openMenu()`/`closeMenu()` functions lock the whole page's scroll (`document.body.style.overflow = 'hidden'`) while the menu is open, restored on close — its own code comment calls this the "canonical implementation (Section 2.4)". Added the same lock to the widget's `openPanel()`/`closePanel()`, guarded with a local flag so the widget won't clobber a lock that might already be held by something else on the page (see below for why that guard mattered in practice, not just in theory).

**A real bug found while verifying this, not before shipping it:** using a `MutationObserver` on `document.body`'s `style` attribute (a synthetic mouse-drag was tried first to simulate the touch scroll and gave a misleading pass/fail signal, since it doesn't trigger the same browser scroll-containment behavior a real touch gesture does — redone with a proper wheel-scroll instead), the lock was observed being set to `hidden` and then immediately cleared back to empty within the same click, on the very first test. Root cause was on the *page's* side, not the widget's: `index.html`'s own hamburger-menu outside-click handler was unconditionally resetting `body.style.overflow` on every click anywhere outside `<header>`, whether its menu was open or not — and the widget's trigger button lives outside `<header>` too, so clicking it triggered that handler and wiped out the widget's own lock in the same event cycle. That page-level bug is fixed and documented in `Task-9-Completion.md`'s own "Revision 3", since it's that page's header code, not this widget's.

**Verified, not assumed:** a real wheel-scroll targeted inside the open panel, at a mobile viewport with touch emulation enabled, confirmed the panel's own `scrollTop` advances while `window.scrollY` stays at exactly 0; `body.style.overflow` confirmed to read `hidden` while the panel is open (after the page-level bug above was fixed) and empty again immediately after closing it.

## Revision 4 — five live-testing fixes: nested dropdowns hidden, panel cut off on mobile, visual redesign, fade transitions

### Nested dropdowns (Language, Color Vision) weren't showing their contents

The Language and Color Vision controls used a floating `position:absolute` listbox overlaying page content. Reported as "click it and nothing visibly happens." Changed to an inline, in-document-flow list: clicking the dropdown trigger now pushes the rest of the panel's content down (same idea as the accordion sections it lives inside), capped to show ~4 rows before it scrolls internally (Language has 6 options, Color Vision has 5). Verified by checking the "Profiles" section trigger's own Y position before and after opening the Language dropdown above it — it visibly moved down (y=403 → y=454), confirming real push-down, not a floating overlay.

### Panel still cut off / not scrollable on mobile — two compounding real bugs, not one

First attempt: switched the mobile panel from trigger-relative positioning to `position: fixed` with explicit viewport offsets, so it would open upward and never depend on the trigger's arbitrary vertical position. Testing it immediately turned up two real, separate bugs:

1. **A CSS containing-block gotcha**: `.pc-a11y-widget` (the panel's parent) has `transform: translateY(-50%)` for vertical centering. Any `transform` on an ancestor creates a new containing block for `position: fixed` descendants — so the panel wasn't actually fixed to the real viewport at all, it was fixed relative to the 56px-tall trigger box. Confirmed via computed-style inspection: `top: -618px`, `width: 0px`. Fixed by replacing the ancestor's `transform: translateY(-50%)` with `margin-top: -28px` (same visual centering, no containing-block side effect).
2. **A specificity conflict**: the desktop rule `.pc-a11y-widget[data-position="right"] .pc-a11y-panel { right: calc(100% + 12px); } ` is more specific (attribute selector + descendant combinator) than a plain `.pc-a11y-panel` rule, so the mobile media-query override was losing to it even though it came later in the stylesheet — specificity beats source order. Fixed with `!important` on the mobile override block, which is a deliberate, justified use here (a breakpoint-specific reset overriding desktop-only positioning rules), not a general habit.

Re-verified after both fixes: panel bounding box on a 390×700 viewport now reports real width/height, fully within the viewport (`y: 198, height: 418` — bottom at 616, inside 700), confirmed both via computed style and a rendered screenshot.

### "Scrollable as a whole" — the statement button and Dark Mode row weren't actually inside the scrolling container

They were siblings of `.pc-a11y-panel__body`, not children — meaning they occupied fixed vertical space above the part that scrolled, and never scrolled with everything else. Moved both inside `.pc-a11y-panel__body` in the markup, so the entire panel content below the title bar (statement button, Dark Mode toggle, all six accordion sections) is now one contiguous scroll area. Also found and fixed the same missing `overscroll-behavior`/`-webkit-overflow-scrolling` fix on the Accessibility Statement panel's own body — it had never gotten the fix applied to the main panel, a second instance of the identical bug caught by checking every scrollable surface this time, not just the one originally reported.

### Visual redesign to match the chat widget

Trigger resized to 56px with a 3px gold (`#d7bd88`) ring border (was borderless), and the gear icon changed from an outline/stroke SVG to a filled solid one — matching the chat widget's gold-ringed, filled-bubble-icon appearance from the reference screenshot.

### Fade transitions

Both the main panel and the Accessibility Statement panel switched from the `hidden` attribute (which forces `display: none` with no possible transition) to a CSS-class-based `opacity`/`visibility`/`transform` transition (`.open` class), so opening and closing now fade rather than snap.

## Cross-Page Persistence — how it will/won't carry over once this script is on other pages

Confirmed behavior tested (closing and reopening the browser and seeing identical settings) is `localStorage`, not session storage — `localStorage` is scoped **per browser origin** (e.g. everything under `adopt.petcenterco.com`), not per page and not per browser session. That's exactly why it survived a full browser restart.

Practical consequence: once `assets/js/accessibility-widget.js` is included via the same `<script src="/assets/js/accessibility-widget.js" defer>` tag on any other page on that same domain, it will automatically read and write the exact same `localStorage.preferences` value — no additional wiring needed for cross-page sync itself. A choice made on `index.html` will already be in effect the moment the visitor lands on, say, `cats.html`, purely because the browser's `localStorage` for that domain is shared, not because of anything specific to this script.

What *is* still an open, real gap: the 15 pages from the earlier Widget Rollout task (`cats.html`, `dogs.html`, etc.) still carry the **old, pre-rebuild** widget (language + opacity only) — they don't have this script at all yet, so a visitor's Dark Mode / Color Vision / any other new-widget choice won't visibly apply there until those pages are updated to the current external `<script src>` include. That update is a one-line swap per page (not a resync of any widget code, since it's all in the one external file now) but hasn't been done as part of this revision.

## Revision 5 — icon swap, real Color Filters, 6-zone drag/D-pad positioning, sticky footer, fade timing

### Settings icon replaced with the CEO-supplied SVG

Copied the path data from `settings-svgrepo-com.svg` byte-for-byte (viewBox `0 0 482.2 482.2`, the gear + person mark) into the trigger button's markup. Only the `fill` attribute was changed — the source file has `fill="#000000"`, changed here to `fill="currentColor"` (the trigger's own `color: #fff`), so the drawn strokes render white per the instruction ("dark part... white"). The instruction's other half — "transparent part fill navy" — needed no separate navy fill in the icon markup at all: the trigger button's own solid navy (`#10214b`) circle background already sits behind the icon, so its negative space was already navy before this change.

### Color Filters — Grayscale was the only one actually filtering the page; now all four do

Confirmed the bug first rather than assuming: the pre-existing `colorVision` dropdown (Normal/Deuteranopia/Protanopia/Tritanopia/Grayscale) only pushed a real CSS `filter` for the `grayscale` option (`filters.push('grayscale(1)')`); the other three only swapped this site's own `--success`/`--error`/etc. CSS variables, which is real and useful (from the earlier Color Vision task) but does nothing to the page's actually-rendered colors — matching exactly what "only grayscale is working" describes.

Searched the web rather than inventing matrix values, per instruction. The matrices below are the standard, widely-published Viénot/Brettel-derived colorblindness simulation matrices — cross-checked against three independent sources (DaltonLens's own CVD-simulation writeup, the `hail2u/color-blindness-emulation` SVG filter set, and Ashley Sheridan's accessibility-plugin article), and structurally the same approach Chrome DevTools' own colorblindness emulation uses (an SVG `<filter>` with `feColorMatrix`, referenced from CSS via `filter: url(#id)`):

```
Protanopia:   0.56667 0.43333 0 0 0 / 0.55833 0.44167 0 0 0 / 0 0.24167 0.75833 0 0 / 0 0 0 1 0
Deuteranopia: 0.625 0.375 0 0 0 / 0.7 0.3 0 0 0 / 0 0.3 0.7 0 0 / 0 0 0 1 0
Tritanopia:   0.95 0.05 0 0 0 / 0 0.433 0.567 0 0 / 0 0.475 0.525 0 0 / 0 0 0 1 0
```

These are injected once (`injectColorFilterDefs()`, called from `init()`) as a hidden `<svg>` with three `<filter>` defs, and `applyAll()` now pushes `url(#pcA11yFilter<Type>)` into the page's `filter` chain for whichever of the three is active — the CSS-variable swap from the earlier task stays in place alongside it, so both effects apply together.

Also rebuilt the section's own UI to match the reference screenshot: the single-select dropdown was replaced with a 2×2 grid of pill buttons (`#pcVisionFilterGrid`), each with a half-and-half color swatch. The screenshot's labels — "Red/Green", "Blue/Yellow", "Green/Red" — are **not** placeholders or guesses: confirmed via web search that this is Apple's own published naming for these same three filters (their accessibility "Color Filters" setting), which is why the pill labels use that wording while the underlying `colorVision` value keeps the clinical names (`protanopia`/`tritanopia`/`deuteranopia`) internally. Still single-select, same as the dropdown it replaces — clicking the already-active pill again clears back to Normal.

### Widget positioning — full rebuild, from always-centered/left-right-only to a 6-zone drag + D-pad system

This is the largest change in this revision. Previously the widget was always vertically centered (`top:50%`) with only a left/right choice. Now `widgetPosition` is a `"<vzone>-<hside>"` pair — `vzone` one of `top`/`middle`/`bottom`, `hside` one of `left`/`right` — six positions total. **Default changed from middle-right to bottom-left**, per explicit instruction ("settings widget will now be at the bottom left while our chat widget at the right, as it is already") — a deliberate default change, not an oversight; a visitor with an old stored `left`/`right`-only value gets migrated to `middle-<side>` on read (that was the only vzone that existed before this revision, so that's the correct migration target, not a guess).

**Panel-opening direction now follows the widget's own zone**, on both mobile and desktop:
- Bottom zone → panel opens above.
- Top zone → panel opens below.
- Middle zone → opens to the side (left/right, matching desktop's previous always-middle behavior) on screens wider than 600px; on 600px and under, opens as a centered overlay with the trigger left visible behind it, per the task's own spec for this one case.

**Drag-to-reposition** is built on Pointer Events (`pointerdown`/`pointermove`/`pointerup`), which is what lets one implementation handle both mouse-drag on desktop and touch-drag/swipe on mobile without separate code paths, matching the confirmed answer that both should behave the same way. A 6px movement threshold distinguishes an actual drag from an ordinary click (which still just opens/closes the panel); a `justDragged` flag suppresses the synthetic `click` event a browser fires after a genuine drag's `pointerup`, so releasing a drag never also toggles the panel. **Snap trigger** (confirmed as "do what's recommended"): viewport thirds for the vertical zone, viewport halves for the horizontal side — the standard approach for this kind of free-drag-then-snap placement, comparable to how OS-level window-snap pickers divide the screen.

**The D-pad** (More Settings → Widget Position) replaces the old two-button Left/Right choice with a dropdown revealing a 5-button grid (up/down/left/right + a center button), icons only, matching the task's own illustration and the widget's no-emoji rule. Up/down step the vzone through top→middle→bottom (clamped at either end); left/right set the hside directly. **The center button's exact meaning wasn't specified** beyond the illustration showing a distinct 5th button — interpreted here as "reset to this widget's default position" (bottom-left), the least ambiguous reading available for a position picker that has no other obvious use for a 5th control. Flagging this interpretation explicitly rather than presenting it as something confirmed.

**Collision/swap rule**, confirmed and implemented identically for both the drag gesture and the D-pad (both call the same `moveWidgetTo()`): moving to a zone the chat widget currently occupies swaps them — the chat widget takes the settings widget's previous spot, rather than the two ever landing on the same zone at once. Coordination happens through a small side-channel `localStorage` key, `pc_widget_positions` (`{a11y, chat}`), separate from the existing `preferences.widgetPosition` field (which stays this widget's own authoritative value; the side-channel exists only so each widget can see where the other currently is). A custom `pc:widget-positions-change` event covers same-tab reactivity (`storage` events only fire in *other* tabs than the one that wrote the change) — `chat-widget.js` listens for both; see `Task-WidgetPositioning-Completion.md` for that file's specific changes.

### Sticky footer: Reset + Powered-by

Reset moved out of More Settings into a persistent footer (`.pc-a11y-panel__footer`), a sibling of `.pc-a11y-panel__body` rather than a child of it, so it never scrolls away with the rest of the panel's content — matching the explicit instruction that it shouldn't be "hidden inside more settings." Added a "Powered by [logo]" row beneath it, using the site's own real logo file (`/assets/images/logo-petcenter-icon.png` — the same file `index.html`'s own header already references, not a placeholder), with an `onerror` fallback to plain orange "Pet Center" text if that image fails to load, per instruction.

### Fade transition — timing fix, not a missing-code fix

The fade mechanism itself (opacity/visibility/transform, driven by the `.open` class) was already correct from Revision 4 — confirmed by inspecting the CSS rather than assuming it was broken. What made it "not visible" was timing: `visibility: 0s linear .22s` combined with `opacity`/`transform` animating over that same `.22s` meant `visibility` flipped to `hidden` at nearly the same instant the fade itself would have been visible, on a fast click. Slowed both panels' transition from `.22s` to `.28s`. This is a timing tweak, not a structural rebuild — worth stating plainly since it would have been easy to over-describe this as a bigger fix than it was.

### `index.html` hamburger-menu scroll leak — re-diffed against `cats.html`, found the actual difference

Reported as still present despite the earlier fix logged in Revision 3 above. Re-read `cats.html`'s canonical block word-for-word rather than assuming the earlier fix covered everything: the JS (`openMenu()`/`closeMenu()`, the body-scroll lock) was already byte-identical between the two pages — that wasn't the gap. The actual difference was one CSS value: `cats.html`'s `.pc-mobile-menu.open` pads its bottom by `calc(100vh - 68px - 56px)`, while `index.html`'s had a plain `24px`. That large padding is what *guarantees* the menu's content is always taller than its own `max-height`, so it's genuinely scrollable — `overscroll-behavior: contain` (present in both files) only stops a touch gesture from scrolling the page **behind** the menu once the menu itself has started scrolling; with a short link list and only `24px` of padding, there was often nothing to scroll inside the menu at all, so the touch passed straight through to the page underneath instead. Copied the exact value from `cats.html` rather than re-deriving it.

### Flagged for future work (not built this revision)

Per instruction, noting here rather than building it now: a possible future merge of the settings widget and the chat widget into a single shared dropdown/control surface, rather than two separate floating triggers. No design decisions made toward this yet — flagged only.

### Self-check

`node -c` clean on `accessibility-widget.js`, on `chat-widget.js`, and on the widget script extracted from `index.html`'s inline copy after re-syncing it. Grepped the built widget markup for duplicate element IDs (none found) and manually re-traced the panel/statement/footer `<div>` nesting after the footer was added. No Playwright or other live-browser tool was available in this environment for this revision, so nothing above was interactively click-tested in an actual browser — everything here is static/structural verification, not a substitute for one. Saying so plainly rather than letting the level of detail above imply more than was actually checked.

## Revision 6 — real whole-page coverage, dark mode corrected twice, font-size overflow root-caused, tritone color filters

Covers two consecutive live-testing rounds. First round: color filters and dark mode weren't reaching the header/footer/hamburger menu, the status popup stayed light in dark mode, font-size caused a right-side overflow gutter, and the four non-dichromacy color filters were too close to each other visually. Second round (after the first pass fixed most but not all of it): font-size/spacing overflow still happened above 170%/at Heavy spacing specifically, the four color filters were confirmed functionally correct but visually bland (strictly two colors, no variation for buttons/borders/text), and the chat widget's close-scroll bug was narrowed down with much better repro clues.

### Color filter / dark mode coverage gap — a regression I introduced, found and fixed

`wrapPageContent()`'s fix for the chat-widget-hiding bug (Revision 5) excluded any `position: fixed` element from being wrapped — correct for the chat widget, but it also silently excluded `.pc-header` (and `.pc-mobile-menu` nested inside it), which is `position: fixed` for entirely legitimate layout reasons. That's why Color Filters and Dark Mode stopped reaching the header/hamburger. Fixed by keeping the wrap-exclusion (still needed to protect `position: fixed` elements from the containing-block problem) but separately applying the same `filter` string directly to `.pc-header` and to `.pc-status-overlay` (the in-page "Check Status" modal, also independently `position: fixed`) — CSS custom properties for Dark Mode already cascade to both regardless of wrapping, since they're set on `<html>`, so only the `filter` property needed this treatment.

### Dark Mode — rebuilt twice

Revision 5's version used a layered bg/surface token hierarchy. Corrected per explicit direction: ONE flat dark navy (`#0c1830`) for everything, no layered surfaces, no gradients anywhere — confirmed the actual gradient bug by reading the CSS (`.pc-testimonials-section` blended a hardcoded light gray with the dark `--bg` token). Added a forceful safety-net CSS block scoped to the three independently-fixed regions (page wrap, header, status modal) that strips every `background-image` and forces a flat `background-color` on structural containers, as a backstop beyond the token conversion. Also caught and fixed a `background: #fff` on the status popup's own header that the Revision 5 token pass had missed (`#ffffff` wasn't in that pass's color list).

### Font-size — `zoom` replaced; the actual overflow trigger was `white-space: nowrap`

`zoom` is a documented cause of exactly this "trapped in a fixed container" bug (WCAG 1.4.4's own guidance warns about it). Replaced with a direct per-element font-size rewrite (caches each element's original computed size once, scales from that baseline every time — same end effect `rem` scaling would have, without requiring this codebase's hardcoded-px CSS to be rewritten first). That alone wasn't the full story: live testing found the overflow still happened above 170% / at Heavy spacing specifically. Root cause: four real `white-space: nowrap` elements on the page (a nav pill, a menu link, a CTA button, a truncating badge) that don't wrap — fine normally, but past that threshold the combined letter-spacing + font-size needs more room than they allow. Added a forceful `white-space: normal` override tied to those exact two triggers (`data-a11y-fontsize-high` at ≥170%, `data-a11y-spacing="heavy"`) rather than patching each of the four selectors individually, so a future fifth nowrap element can't reintroduce the same bug silently.

### Color filters — duotone, then upgraded to tritone

Reported problem: Protanopia/Deuteranopia and Protanomaly/Deuteranomaly were different matrices but close enough in color space to look like the same filter twice. Rebuilt four of the six (keeping Tritanopia and Grayscale, confirmed already correct) as true duotone filters — desaturate to luminance, remap via `feComponentTransfer`, using four explicitly-provided two-color palettes (Mauve/Ivory, Rose/Cocoa, Milk/Plum, Melon/Astral Blue). Second round: confirmed a duotone can only ever show two colors by definition, which read as "bland" with buttons/borders/text showing no variation. Upgraded all four to genuine 3-point gradients (shadows → researched accent color → highlights) — a 3-value `feComponentTransfer` table interpolates in two segments automatically, so mid-luminance content (this site's actual button/badge colors live around 0.35-0.7 relative luminance) now resolves near the new third color instead of being pulled to one extreme. Accent colors are cited real pairings, not invented: gold with the mauve palette, terracotta with rose/cocoa, a warmer gold with plum (literal complement is chartreuse, too neon for UI), a bridging teal for melon/navy.

### Profiles

Low Vision gained `focusReading: true` and `theme: 'dark'` (Revision 5's rebuild made both genuinely worth defaulting on for that persona). Checked again this round — font-size/spacing/color-filter fixes are corrections to existing mechanics, not new fields, so no profile needed updating for them.

### Chat widget — scroll-on-close: NOT RESOLVED, flagged for whoever picks this up next

See `Task-WidgetPositioning-Completion.md` for the full detail (two fix attempts, both insufficient, plus what the symptom actually narrows down to) — not duplicated here since that file owns all `chat-widget.js` documentation. Short version: still happens. Don't re-attempt a stale-saved-scroll-position theory or a scroll-anchoring theory without reading that file first — both were tried.

### Self-check

`node -c` clean on `accessibility-widget.js`, `chat-widget.js`, and the widget script re-extracted from `index.html`'s inline copy after each re-sync. Manually re-counted `{`/`}` balance in `index.html`'s `<style>` block after every CSS edit this round (caught and fixed one stray leftover brace from an incomplete `str_replace` mid-revision). No live-browser tool was available for most of this revision; the chat-widget scroll bug specifically was diagnosed and partially iterated against real user-reported symptoms from live testing, not from a tool here, which is why it still isn't resolved — the actual mechanism was never directly observed, only inferred from secondhand description.
