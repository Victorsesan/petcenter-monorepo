# Task 10 Completion — Widget Rollout, All Remaining Pages

## Task Number
Task 10, quoted in full from the assignment:

> ## Task 10 — Widget Rollout, All Remaining Pages
> **Read First:** `adoption-website-project-summary.md`, `PETCENTER_DESIGN_SYSTEM_v1.0.md`.
> **Files to Request:** every page in Section 3 except `index.html`.
> **Depends on:** Task 9 only.
> Insert widget markup + View Transitions/`preferences` wiring into every remaining page, Playwright-verified, plus an `axe-core` scan on the same pass. Every translation task (4–8) depends on this — get it done early. Confirm no collision with the signature pad/toasts or any blog scroll indicator (unverified — check the real file).

## What Was Required
Put the Settings Widget (built in Task 9 on `index.html`) and the cross-document View Transitions wiring into every remaining page, verify with Playwright, run an `axe-core` scan, and confirm the widget does not collide with the signature pad, toasts or any blog scroll indicator. The widget code is reused verbatim from `index.html` (no new authorship), so the two inserted blocks below are byte-identical in all 15 files. The widget block is the **current** `index.html` version (right-edge position and Color Vision dropdown), per the Addendum at the end of this file.

## Files Changed

All 15 rows are existing files that were changed. Nothing was created (see New files).

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `cats.html` | `pets/cats/index.html` | Changed | Shared with Tasks 4-8 (translation) once they land. |
| `dogs.html` | `pets/dogs/index.html` | Changed | Shared with Tasks 4-8 (translation) once they land. |
| `birds.html` | `pets/birds/index.html` | Changed | Shared with Tasks 4-8 (translation) once they land. |
| `reptiles.html` | `pets/reptiles/index.html` | Changed | Shared with Tasks 4-8 (translation) once they land. |
| `small-mammals.html` | `pets/small-mammals/index.html` | Changed | Shared with Tasks 4-8 (translation) once they land. |
| `livestocks.html` | `pets/livestocks/index.html` | Changed | Shared with Tasks 4-8 (translation) once they land. |
| `aquariums.html` | `pets/aquariums/index.html` | Changed | Shared with Tasks 4-8 (translation) once they land. |
| `pets.html` | `pets/index.html` | Changed | Shared with Tasks 4-8 (translation) once they land. |
| `services.html` | `services/index.html` | Changed | Shared with Tasks 4-8 (translation) once they land. |
| `pet-detail.html` | `pets/detail/index.html` | Changed | Shared with Tasks 4-8 (translation) once they land. Checked: no collision with the sticky price+Apply bar or the full-page modal. |
| `testimonials.html` | `testimonials/index.html` | Changed | Shared with Tasks 4-8 (translation) once they land. |
| `adoption-application.html` | `apply/index.html` | Changed | Shared with Tasks 4-8 (translation) once they land. Checked: no collision with the signature pad (inline) or the sticky nav footer. |
| `status.html` | `adoptions/status/index.html` | Changed | Shared with Tasks 4-8 (translation) once they land. |
| `blog-home.html` | `blog/index.html` | Changed | Shared with Tasks 4-8 (translation) once they land. Checked: no blog scroll/reading-progress indicator exists to collide with. |
| `blog-single.html` | `blog/post/index.html` | Changed | Shared with Tasks 4-8 (translation) once they land. Checked: no blog scroll/reading-progress indicator exists to collide with. |

**Not completed — file never supplied:** `signin.html` (live path `signin/index.html`) is listed in the assignment's page reference but was never provided in any upload this task received. It was skipped rather than guessed at. It still needs the same two insertions; see Bugs Found Outside Task Scope.

**Deliberately not touched:** `index.html` and `admin-dashboard.html` (Task 9 already covers them), and `blogs-data.js` (a data file, not a page; no widget applies).

## Code Changes

Two insertions were made in each of the 15 files. Each was the same text in every file: Block V before the closing `</head>`, Block W before the closing `</body>`. Every file below shows its own exact old snippet (copied from the original file supplied, and occurring only once in that file) and the exact new final code. The wrapper anchors below are `TASK-10`; the code inside keeps its original `TASK-9 START/END` anchors, because it is Task 9's code reused verbatim (rule: reuse existing code, never recreate). Each block carries its own comments describing what it does.

Applying every old/new pair below to the original files reproduces the 15 delivered files byte for byte (checked by script).

### `cats.html`  →  live path `pets/cats/index.html`

<!-- TASK-10 START -->
**Change 1 of 2 — View Transitions style block, before `</head>`**

**Old code:**
```html
  </head>
```
**New code:**
```html
<!-- TASK-9 START: cross-document View Transitions API, enabled sitewide.
     Animates same-origin full-page navigations (e.g. the Settings Widget's
     language switcher navigating between /, /es/, /pt/, /fr/, /ar/, /zh/)
     in browsers that support the cross-document View Transitions API
     (Chromium 126+). Browsers without support ignore this at-rule and
     navigate normally with no error -- this is a progressive enhancement,
     not a dependency. -->
<style>
@view-transition {
  navigation: auto;
}
</style>
<!-- TASK-9 END -->
  </head>
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Change 2 of 2 — Settings Widget (style + markup + script), before `</body>`**

**Old code:**
```html
</body>
```
**New code:**
```html
<!-- TASK-9 START: Settings Widget -- gear-icon trigger + panel housing the
     sitewide language switcher and the personal widget-background-opacity
     slider. Defines the shared `preferences` object (localStorage key
     "preferences", fields {lang, opacity}) that this page's own
     lang-redirect.js / i18n-core.js (Task 1) already read `preferences.lang`
     from, and that Admin-Project #4's `preferred_chat_language` field also
     reads independently -- the exact field name/shape/values documented in
     this task must not change without notifying that project first.
     Z-INDEX NOTE: the site's real, documented highest z-index anywhere is
     6001 (the filter/form bottom-sheet system), not the floating contact
     button's 4000 -- this widget's trigger/panel/dropdown are placed at
     6100/6200/6300 so they always sit above every existing floating
     element, including both of those. -->
<style>
/* Renamed from --pc-widget-opacity (added in this task's first pass) to
   --pc-widget-bg-opacity, and a second, distinct --pc-widget-opacity
   variable added for the whole widget's own opacity -- per user follow-up,
   "Widget Background Opacity" (panel's rgba background only, children
   stay fully legible) and "Widget Opacity" (the entire trigger + panel,
   via real CSS `opacity` on the container) are two separate controls. */
:root { --pc-widget-bg-opacity: 0.90; --pc-widget-opacity: 1; }

/* "Central position" = vertically centered on the viewport's right edge.
   User follow-up moved this from the left edge (this task's original
   build) to the right edge -- everything else about the placement logic
   from that first pass still applies: vertically centered avoids the
   hero's own CTA buttons (the collision found and fixed when this was
   briefly bottom-center), and vertical centering keeps clear of the
   bottom-right floating contact button (z-index 4000), re-confirmed by
   Playwright screenshot below since moving to the same edge as that
   button is exactly the kind of collision worth re-checking, not
   assuming safe by reasoning alone. */
.pc-settings-widget {
  position: fixed;
  top: 50%;
  right: 16px;
  transform: translateY(-50%);
  z-index: 6100;
  font-family: 'Sora', sans-serif;
  /* Whole-widget opacity (trigger + panel together), separate from the
     panel-only rgba background opacity below. Real CSS `opacity`, by
     design, fades everything inside -- that's the intended effect of this
     specific control, unlike the background one. */
  opacity: var(--pc-widget-opacity);
  transition: opacity 0.2s ease;
}

.pc-settings-trigger {
  width: 52px; height: 52px; border-radius: 50%; border: none; cursor: pointer;
  background: #10214b; color: #d7bd88;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 16px rgba(16,33,75,0.35);
  transition: transform 0.3s cubic-bezier(.4,0,.2,1), background 0.3s cubic-bezier(.4,0,.2,1);
}
.pc-settings-trigger:hover { transform: rotate(35deg); background: #1a2f5c; }
.pc-settings-trigger[aria-expanded="true"] { background: #ED7624; color: #fff; }

.pc-settings-panel {
  position: absolute;
  top: 50%;
  /* Opens to the LEFT of the trigger now that the trigger sits on the
     right edge (was `left: calc(100% + 12px)` opening rightward off a
     left-edge trigger). */
  right: calc(100% + 12px);
  transform: translateY(-50%);
  width: 280px;
  max-width: calc(100vw - 32px);
  /* rgba() background driven by the Widget Background Opacity slider --
     never container `opacity`, which would also fade the panel's
     text/children (Task 9's explicit requirement: "rgba() backgrounds,
     not container opacity"). The separate whole-widget opacity control
     lives on .pc-settings-widget above. */
  background: rgba(16, 33, 75, var(--pc-widget-bg-opacity));
  color: #f2f2f2;
  border-radius: 14px;
  padding: 16px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.3);
  z-index: 6200;
}
.pc-settings-panel[hidden] { display: none; }

.pc-settings-panel__header {
  display: flex; align-items: center; justify-content: space-between;
  font-weight: 700; margin-bottom: 12px; font-size: 15px;
}
.pc-settings-panel__close {
  background: none; border: none; color: inherit; font-size: 20px; line-height: 1;
  cursor: pointer; padding: 2px 6px;
}

.pc-settings-row { margin-bottom: 14px; }
.pc-settings-row:last-child { margin-bottom: 0; }
.pc-settings-label { display: block; font-size: 12.5px; margin-bottom: 6px; color: #d7bd88; }
/* One-line description under a consequential setting, per Design System
   Section 19's "Settings Widget Control Pattern -- Toggle + Description"
   rule (pair every consequential control with a plain-language line
   explaining its effect, since a user shouldn't have to guess). */
.pc-settings-desc { font-size: 11.5px; color: #a39b91; margin: -2px 0 8px; line-height: 1.4; }

.pc-lang-dropdown { position: relative; }
.pc-lang-dropdown__trigger {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.16);
  color: #f2f2f2; border-radius: 8px; padding: 8px 12px; font-size: 13.5px; cursor: pointer;
  font-family: 'Sora', sans-serif;
}
.pc-lang-dropdown__panel {
  position: absolute; top: calc(100% + 6px); left: 0; right: 0;
  background: #10214b; border: 1px solid rgba(255,255,255,0.14);
  /* Sharp corner nearest the trigger (top-left here, since the trigger
     sits above this downward-opening panel) -- the "message bubble" corner
     rule from PETCENTER_DESIGN_SYSTEM_v1_0.md Section 24 (v1.25). */
  border-radius: 0 8px 8px 8px;
  list-style: none; margin: 0; padding: 4px; max-height: 200px; overflow-y: auto;
  z-index: 6300;
}
.pc-lang-dropdown__panel[hidden] { display: none; }
.pc-lang-dropdown__panel li { padding: 8px 10px; font-size: 13.5px; border-radius: 6px; cursor: pointer; }
.pc-lang-dropdown__panel li:hover,
.pc-lang-dropdown__panel li[aria-selected="true"] { background: rgba(215,189,136,0.18); }

.pc-opacity-slider { width: 100%; accent-color: #d7bd88; }
.pc-opacity-value { font-size: 12px; color: #a39b91; float: right; margin-top: 2px; }

@media (max-width: 600px) {
  /* Below 600px, opening the panel to the right of a left-edge trigger
     would push a 280px-wide panel off the right edge of the viewport on
     most phones. Anchor the panel to open downward from the trigger
     instead, still clear of both the hero content and the bottom-right
     contact button. Anchored via `right: 0` (not `left: 0`) since the
     trigger itself now sits near the right edge -- `left: 0` would push
     a near-full-width panel further right and off-screen. */
  .pc-settings-panel {
    top: calc(100% + 10px);
    right: 0;
    left: auto;
    transform: none;
    width: calc(100vw - 48px);
  }
}
</style>

<div class="pc-settings-widget" id="pcSettingsWidget">
  <button type="button" class="pc-settings-trigger" id="pcSettingsTrigger"
          aria-haspopup="true" aria-expanded="false" aria-controls="pcSettingsPanel"
          aria-label="Open site settings">
    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <circle cx="12" cy="12" r="3"></circle>
      <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
    </svg>
  </button>

  <div class="pc-settings-panel" id="pcSettingsPanel" role="dialog" aria-modal="false"
       aria-label="Site settings" hidden="">
    <div class="pc-settings-panel__header">
      <span data-i18n="common.settingsWidget.1">Settings</span>
      <button type="button" class="pc-settings-panel__close" id="pcSettingsClose"
              aria-label="Close settings">&times;</button>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcLangLabel" data-i18n="common.settingsWidget.2">Language</label>
      <div class="pc-lang-dropdown" id="pcLangDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcLangTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcLangLabel"
                aria-controls="pcLangListbox">
          <span id="pcLangCurrentLabel">English</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcLangListbox" role="listbox"
            aria-labelledby="pcLangLabel" tabindex="-1" hidden="">
          <li role="option" data-lang="en" aria-selected="true" tabindex="0">English</li>
          <li role="option" data-lang="es" aria-selected="false" tabindex="0">Español</li>
          <li role="option" data-lang="pt" aria-selected="false" tabindex="0">Português</li>
          <li role="option" data-lang="fr" aria-selected="false" tabindex="0">Français</li>
          <li role="option" data-lang="ar" aria-selected="false" tabindex="0">العربية</li>
          <li role="option" data-lang="zh-Hans" aria-selected="false" tabindex="0">简体中文</li>
        </ul>
      </div>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcOpacitySlider" id="pcOpacityLabel" data-i18n="common.settingsWidget.3">Widget Background Opacity</label>
      <input type="range" id="pcOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="90"
             role="slider" aria-labelledby="pcOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="90" aria-valuetext="90%">
      <span class="pc-opacity-value" id="pcOpacityValue" aria-hidden="true">90%</span>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcWidgetOpacitySlider" id="pcWidgetOpacityLabel" data-i18n="common.settingsWidget.4">Widget Opacity</label>
      <input type="range" id="pcWidgetOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="100"
             role="slider" aria-labelledby="pcWidgetOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="100" aria-valuetext="100%">
      <span class="pc-opacity-value" id="pcWidgetOpacityValue" aria-hidden="true">100%</span>
    </div>

    <!-- TASK (Color Vision Accessibility) START: brief calls for this
         control to sit "alongside the existing dark mode toggle" -- no dark
         mode toggle exists anywhere in this codebase yet (Design System
         Section 19 documents its intended look/behavior but confirms it
         is not yet built; searched before assuming otherwise). Not adding
         a placeholder/stub toggle for a feature that hasn't been
         specified as buildable here -- flagging the gap in this task's
         completion file instead, per "flag it rather than inventing." -->
    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcVisionLabel" data-i18n="common.settingsWidget.5">Color Vision</label>
      <p class="pc-settings-desc" id="pcVisionDesc" data-i18n="common.settingsWidget.6">Adjusts status colors, alerts, and charts sitewide for your selected vision type.</p>
      <div class="pc-lang-dropdown" id="pcVisionDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcVisionTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcVisionLabel"
                aria-controls="pcVisionListbox">
          <span id="pcVisionCurrentLabel">Normal Vision</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcVisionListbox" role="listbox"
            aria-labelledby="pcVisionLabel" tabindex="-1" hidden="">
          <li role="option" data-vision="normal" aria-selected="true" tabindex="0">Normal Vision</li>
          <li role="option" data-vision="deuteranopia" aria-selected="false" tabindex="0">Deuteranopia</li>
          <li role="option" data-vision="protanopia" aria-selected="false" tabindex="0">Protanopia</li>
          <li role="option" data-vision="tritanopia" aria-selected="false" tabindex="0">Tritanopia</li>
        </ul>
      </div>
    </div>
    <!-- TASK (Color Vision Accessibility) END -->
  </div>
</div>

<script>
// TASK-9 START: Settings Widget logic + `preferences` schema owner.
(function () {
  'use strict';

  var PREFS_KEY = 'preferences';
  // Written by admin-dashboard.html's "Default Widget Background Opacity"
  // and "Default Widget Opacity" controls (this task's admin-side
  // deliverable). Read here only as a fallback default for a visitor with
  // no personal preference saved yet -- a visitor's own saved choice in
  // `preferences.opacity` / `preferences.widgetOpacity` always wins.
  var ADMIN_DEFAULTS_KEY = 'pc_admin_settings_defaults';

  // LANG_FOLDER_MAP mirrors Task 1's /en/-/zh/ folder scaffolding -- English
  // is the un-prefixed root (never a redirect target), the rest are their
  // own language-code folders. 'zh-Hans' (tagged, not bare 'zh') is the
  // CEO-confirmed value for Chinese everywhere this schema is used.
  var LANG_FOLDER_MAP = { en: '/', es: '/es/', pt: '/pt/', fr: '/fr/', ar: '/ar/', 'zh-Hans': '/zh/' };
  var LANG_LABELS = { en: 'English', es: 'Español', pt: 'Português', fr: 'Français', ar: 'العربية', 'zh-Hans': '简体中文' };
  var VISION_LABELS = { normal: 'Normal Vision', deuteranopia: 'Deuteranopia', protanopia: 'Protanopia', tritanopia: 'Tritanopia' };

  function readAdminDefaultOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 90;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.opacity);
      return (val >= 20 && val <= 100) ? val : 90;
    } catch (e) { return 90; }
  }

  function readAdminDefaultWidgetOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 100;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.widgetOpacity);
      return (val >= 20 && val <= 100) ? val : 100;
    } catch (e) { return 100; }
  }

  function getPreferences() {
    try {
      var raw = localStorage.getItem(PREFS_KEY);
      var parsed = raw ? JSON.parse(raw) : {};
      return {
        lang: (typeof parsed.lang === 'string' && parsed.lang) ? parsed.lang : 'en',
        opacity: (typeof parsed.opacity === 'number') ? parsed.opacity : readAdminDefaultOpacity(),
        widgetOpacity: (typeof parsed.widgetOpacity === 'number') ? parsed.widgetOpacity : readAdminDefaultWidgetOpacity(),
        colorVision: (typeof parsed.colorVision === 'string' && VISION_LABELS[parsed.colorVision]) ? parsed.colorVision : 'normal'
      };
    } catch (e) {
      return { lang: 'en', opacity: readAdminDefaultOpacity(), widgetOpacity: readAdminDefaultWidgetOpacity(), colorVision: 'normal' };
    }
  }

  function setPreferences(partial) {
    var current = getPreferences();
    var next = { lang: current.lang, opacity: current.opacity, widgetOpacity: current.widgetOpacity, colorVision: current.colorVision };
    for (var k in partial) { if (Object.prototype.hasOwnProperty.call(partial, k)) next[k] = partial[k]; }
    try { localStorage.setItem(PREFS_KEY, JSON.stringify(next)); } catch (e) { /* storage unavailable -- fail silently, in-memory state still applies for this tab */ }
    return next;
  }

  // Panel-only rgba() background opacity -- children stay fully legible
  // regardless of this value.
  function applyBackgroundOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcOpacitySlider');
    var valueEl = document.getElementById('pcOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  // Whole-widget opacity -- real CSS `opacity` on .pc-settings-widget,
  // fades the trigger button and everything inside the panel together.
  function applyWidgetOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcWidgetOpacitySlider');
    var valueEl = document.getElementById('pcWidgetOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  function applyLangSelection(code) {
    var currentLabelEl = document.getElementById('pcLangCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = LANG_LABELS[code] || 'English';
    var options = document.querySelectorAll('#pcLangListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-lang') === code ? 'true' : 'false');
    });
  }

  // Sets the `data-vision` attribute on <html>, which every
  // [data-vision="..."] CSS override block above (and in admin-dashboard.html)
  // keys off of. "normal" matches no override rule, so it's a no-op that
  // simply leaves the default colors in place -- no separate "clear" branch
  // needed.
  function applyVisionSelection(vision) {
    document.documentElement.setAttribute('data-vision', vision);
    var currentLabelEl = document.getElementById('pcVisionCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = VISION_LABELS[vision] || 'Normal Vision';
    var options = document.querySelectorAll('#pcVisionListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-vision') === vision ? 'true' : 'false');
    });
  }

  function navigateToLang(code) {
    var target = LANG_FOLDER_MAP[code] || '/';
    // Cross-document View Transitions (enabled sitewide via the
    // `@view-transition { navigation: auto; }` at-rule in <head>) animate
    // this navigation automatically in supporting browsers -- a plain
    // same-origin navigation is all that's needed to trigger it.
    window.location.href = target;
  }

  // Apply stored/default preferences immediately (before DOMContentLoaded)
  // so both CSS variables are correct before first paint -- avoids a flash
  // of the defaults on a return visitor with different saved values. The
  // `data-vision` attribute is likewise set here, before first paint, so
  // there's no flash of un-adjusted colors either.
  (function initFromStoredPreferencesEarly() {
    var prefs = getPreferences();
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (prefs.opacity / 100).toFixed(2));
    document.documentElement.style.setProperty('--pc-widget-opacity', (prefs.widgetOpacity / 100).toFixed(2));
    document.documentElement.setAttribute('data-vision', prefs.colorVision);
  })();

  document.addEventListener('DOMContentLoaded', function () {
    var trigger = document.getElementById('pcSettingsTrigger');
    var panel = document.getElementById('pcSettingsPanel');
    var closeBtn = document.getElementById('pcSettingsClose');
    var langTrigger = document.getElementById('pcLangTrigger');
    var langListbox = document.getElementById('pcLangListbox');
    var opacitySlider = document.getElementById('pcOpacitySlider');
    var widgetOpacitySlider = document.getElementById('pcWidgetOpacitySlider');
    var visionTrigger = document.getElementById('pcVisionTrigger');
    var visionListbox = document.getElementById('pcVisionListbox');

    if (!trigger || !panel) return;

    var prefs = getPreferences();
    applyBackgroundOpacity(prefs.opacity);
    applyWidgetOpacity(prefs.widgetOpacity);
    applyLangSelection(prefs.lang);
    applyVisionSelection(prefs.colorVision);

    function openPanel() {
      panel.hidden = false;
      trigger.setAttribute('aria-expanded', 'true');
    }
    function closeAllDropdowns() {
      if (langListbox && !langListbox.hidden) {
        langListbox.hidden = true;
        langTrigger.setAttribute('aria-expanded', 'false');
      }
      if (visionListbox && !visionListbox.hidden) {
        visionListbox.hidden = true;
        visionTrigger.setAttribute('aria-expanded', 'false');
      }
    }
    function closePanel() {
      panel.hidden = true;
      trigger.setAttribute('aria-expanded', 'false');
      closeAllDropdowns();
    }

    trigger.addEventListener('click', function () {
      panel.hidden ? openPanel() : closePanel();
    });
    if (closeBtn) closeBtn.addEventListener('click', closePanel);

    document.addEventListener('click', function (e) {
      if (!e.target.closest('#pcSettingsWidget')) closePanel();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closePanel();
    });

    // Language dropdown: open/close + selection.
    if (langTrigger && langListbox) {
      langTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !langListbox.hidden;
        langListbox.hidden = isOpen;
        langTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      langListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var code = opt.getAttribute('data-lang');
        setPreferences({ lang: code });
        applyLangSelection(code);
        closeAllDropdowns();
        navigateToLang(code);
      });
      // Keyboard support: ArrowUp/ArrowDown move focus between options,
      // Enter/Space activates the focused option.
      langListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(langListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          langTrigger.focus();
        }
      });
    }

    // Color Vision dropdown: same open/close/selection/keyboard pattern as
    // the language dropdown above -- unlike language, selecting an option
    // here does NOT navigate anywhere, it just applies immediately.
    if (visionTrigger && visionListbox) {
      visionTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !visionListbox.hidden;
        visionListbox.hidden = isOpen;
        visionTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      visionListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var vision = opt.getAttribute('data-vision');
        setPreferences({ colorVision: vision });
        applyVisionSelection(vision);
        closeAllDropdowns();
      });
      visionListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(visionListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          visionTrigger.focus();
        }
      });
    }

    // Both opacity sliders -- native <input type="range"> already provides
    // full keyboard support (Arrow keys, Home/End, PageUp/PageDown); role
    // and aria-value* attributes are set explicitly per this task's spec.
    if (opacitySlider) {
      opacitySlider.addEventListener('input', function () {
        var value = Number(opacitySlider.value);
        applyBackgroundOpacity(value);
        setPreferences({ opacity: value });
      });
    }
    if (widgetOpacitySlider) {
      widgetOpacitySlider.addEventListener('input', function () {
        var value = Number(widgetOpacitySlider.value);
        applyWidgetOpacity(value);
        setPreferences({ widgetOpacity: value });
      });
    }

    // Native `storage` event -- fires in OTHER tabs/windows on this origin
    // when `preferences` changes there, and vice versa. This is the
    // cross-tab sync mechanism this task requires; no polling needed.
    window.addEventListener('storage', function (e) {
      if (e.key !== PREFS_KEY) return;
      var updated = getPreferences();
      applyBackgroundOpacity(updated.opacity);
      applyWidgetOpacity(updated.widgetOpacity);
      applyLangSelection(updated.lang);
      applyVisionSelection(updated.colorVision);
    });
  });
})();
// TASK-9 END
</script>
</body>
```
<!-- TASK-10 END -->

### `dogs.html`  →  live path `pets/dogs/index.html`

<!-- TASK-10 START -->
**Change 1 of 2 — View Transitions style block, before `</head>`**

**Old code:**
```html
  </head>
```
**New code:**
```html
<!-- TASK-9 START: cross-document View Transitions API, enabled sitewide.
     Animates same-origin full-page navigations (e.g. the Settings Widget's
     language switcher navigating between /, /es/, /pt/, /fr/, /ar/, /zh/)
     in browsers that support the cross-document View Transitions API
     (Chromium 126+). Browsers without support ignore this at-rule and
     navigate normally with no error -- this is a progressive enhancement,
     not a dependency. -->
<style>
@view-transition {
  navigation: auto;
}
</style>
<!-- TASK-9 END -->
  </head>
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Change 2 of 2 — Settings Widget (style + markup + script), before `</body>`**

**Old code:**
```html
</body>
```
**New code:**
```html
<!-- TASK-9 START: Settings Widget -- gear-icon trigger + panel housing the
     sitewide language switcher and the personal widget-background-opacity
     slider. Defines the shared `preferences` object (localStorage key
     "preferences", fields {lang, opacity}) that this page's own
     lang-redirect.js / i18n-core.js (Task 1) already read `preferences.lang`
     from, and that Admin-Project #4's `preferred_chat_language` field also
     reads independently -- the exact field name/shape/values documented in
     this task must not change without notifying that project first.
     Z-INDEX NOTE: the site's real, documented highest z-index anywhere is
     6001 (the filter/form bottom-sheet system), not the floating contact
     button's 4000 -- this widget's trigger/panel/dropdown are placed at
     6100/6200/6300 so they always sit above every existing floating
     element, including both of those. -->
<style>
/* Renamed from --pc-widget-opacity (added in this task's first pass) to
   --pc-widget-bg-opacity, and a second, distinct --pc-widget-opacity
   variable added for the whole widget's own opacity -- per user follow-up,
   "Widget Background Opacity" (panel's rgba background only, children
   stay fully legible) and "Widget Opacity" (the entire trigger + panel,
   via real CSS `opacity` on the container) are two separate controls. */
:root { --pc-widget-bg-opacity: 0.90; --pc-widget-opacity: 1; }

/* "Central position" = vertically centered on the viewport's right edge.
   User follow-up moved this from the left edge (this task's original
   build) to the right edge -- everything else about the placement logic
   from that first pass still applies: vertically centered avoids the
   hero's own CTA buttons (the collision found and fixed when this was
   briefly bottom-center), and vertical centering keeps clear of the
   bottom-right floating contact button (z-index 4000), re-confirmed by
   Playwright screenshot below since moving to the same edge as that
   button is exactly the kind of collision worth re-checking, not
   assuming safe by reasoning alone. */
.pc-settings-widget {
  position: fixed;
  top: 50%;
  right: 16px;
  transform: translateY(-50%);
  z-index: 6100;
  font-family: 'Sora', sans-serif;
  /* Whole-widget opacity (trigger + panel together), separate from the
     panel-only rgba background opacity below. Real CSS `opacity`, by
     design, fades everything inside -- that's the intended effect of this
     specific control, unlike the background one. */
  opacity: var(--pc-widget-opacity);
  transition: opacity 0.2s ease;
}

.pc-settings-trigger {
  width: 52px; height: 52px; border-radius: 50%; border: none; cursor: pointer;
  background: #10214b; color: #d7bd88;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 16px rgba(16,33,75,0.35);
  transition: transform 0.3s cubic-bezier(.4,0,.2,1), background 0.3s cubic-bezier(.4,0,.2,1);
}
.pc-settings-trigger:hover { transform: rotate(35deg); background: #1a2f5c; }
.pc-settings-trigger[aria-expanded="true"] { background: #ED7624; color: #fff; }

.pc-settings-panel {
  position: absolute;
  top: 50%;
  /* Opens to the LEFT of the trigger now that the trigger sits on the
     right edge (was `left: calc(100% + 12px)` opening rightward off a
     left-edge trigger). */
  right: calc(100% + 12px);
  transform: translateY(-50%);
  width: 280px;
  max-width: calc(100vw - 32px);
  /* rgba() background driven by the Widget Background Opacity slider --
     never container `opacity`, which would also fade the panel's
     text/children (Task 9's explicit requirement: "rgba() backgrounds,
     not container opacity"). The separate whole-widget opacity control
     lives on .pc-settings-widget above. */
  background: rgba(16, 33, 75, var(--pc-widget-bg-opacity));
  color: #f2f2f2;
  border-radius: 14px;
  padding: 16px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.3);
  z-index: 6200;
}
.pc-settings-panel[hidden] { display: none; }

.pc-settings-panel__header {
  display: flex; align-items: center; justify-content: space-between;
  font-weight: 700; margin-bottom: 12px; font-size: 15px;
}
.pc-settings-panel__close {
  background: none; border: none; color: inherit; font-size: 20px; line-height: 1;
  cursor: pointer; padding: 2px 6px;
}

.pc-settings-row { margin-bottom: 14px; }
.pc-settings-row:last-child { margin-bottom: 0; }
.pc-settings-label { display: block; font-size: 12.5px; margin-bottom: 6px; color: #d7bd88; }
/* One-line description under a consequential setting, per Design System
   Section 19's "Settings Widget Control Pattern -- Toggle + Description"
   rule (pair every consequential control with a plain-language line
   explaining its effect, since a user shouldn't have to guess). */
.pc-settings-desc { font-size: 11.5px; color: #a39b91; margin: -2px 0 8px; line-height: 1.4; }

.pc-lang-dropdown { position: relative; }
.pc-lang-dropdown__trigger {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.16);
  color: #f2f2f2; border-radius: 8px; padding: 8px 12px; font-size: 13.5px; cursor: pointer;
  font-family: 'Sora', sans-serif;
}
.pc-lang-dropdown__panel {
  position: absolute; top: calc(100% + 6px); left: 0; right: 0;
  background: #10214b; border: 1px solid rgba(255,255,255,0.14);
  /* Sharp corner nearest the trigger (top-left here, since the trigger
     sits above this downward-opening panel) -- the "message bubble" corner
     rule from PETCENTER_DESIGN_SYSTEM_v1_0.md Section 24 (v1.25). */
  border-radius: 0 8px 8px 8px;
  list-style: none; margin: 0; padding: 4px; max-height: 200px; overflow-y: auto;
  z-index: 6300;
}
.pc-lang-dropdown__panel[hidden] { display: none; }
.pc-lang-dropdown__panel li { padding: 8px 10px; font-size: 13.5px; border-radius: 6px; cursor: pointer; }
.pc-lang-dropdown__panel li:hover,
.pc-lang-dropdown__panel li[aria-selected="true"] { background: rgba(215,189,136,0.18); }

.pc-opacity-slider { width: 100%; accent-color: #d7bd88; }
.pc-opacity-value { font-size: 12px; color: #a39b91; float: right; margin-top: 2px; }

@media (max-width: 600px) {
  /* Below 600px, opening the panel to the right of a left-edge trigger
     would push a 280px-wide panel off the right edge of the viewport on
     most phones. Anchor the panel to open downward from the trigger
     instead, still clear of both the hero content and the bottom-right
     contact button. Anchored via `right: 0` (not `left: 0`) since the
     trigger itself now sits near the right edge -- `left: 0` would push
     a near-full-width panel further right and off-screen. */
  .pc-settings-panel {
    top: calc(100% + 10px);
    right: 0;
    left: auto;
    transform: none;
    width: calc(100vw - 48px);
  }
}
</style>

<div class="pc-settings-widget" id="pcSettingsWidget">
  <button type="button" class="pc-settings-trigger" id="pcSettingsTrigger"
          aria-haspopup="true" aria-expanded="false" aria-controls="pcSettingsPanel"
          aria-label="Open site settings">
    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <circle cx="12" cy="12" r="3"></circle>
      <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
    </svg>
  </button>

  <div class="pc-settings-panel" id="pcSettingsPanel" role="dialog" aria-modal="false"
       aria-label="Site settings" hidden="">
    <div class="pc-settings-panel__header">
      <span data-i18n="common.settingsWidget.1">Settings</span>
      <button type="button" class="pc-settings-panel__close" id="pcSettingsClose"
              aria-label="Close settings">&times;</button>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcLangLabel" data-i18n="common.settingsWidget.2">Language</label>
      <div class="pc-lang-dropdown" id="pcLangDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcLangTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcLangLabel"
                aria-controls="pcLangListbox">
          <span id="pcLangCurrentLabel">English</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcLangListbox" role="listbox"
            aria-labelledby="pcLangLabel" tabindex="-1" hidden="">
          <li role="option" data-lang="en" aria-selected="true" tabindex="0">English</li>
          <li role="option" data-lang="es" aria-selected="false" tabindex="0">Español</li>
          <li role="option" data-lang="pt" aria-selected="false" tabindex="0">Português</li>
          <li role="option" data-lang="fr" aria-selected="false" tabindex="0">Français</li>
          <li role="option" data-lang="ar" aria-selected="false" tabindex="0">العربية</li>
          <li role="option" data-lang="zh-Hans" aria-selected="false" tabindex="0">简体中文</li>
        </ul>
      </div>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcOpacitySlider" id="pcOpacityLabel" data-i18n="common.settingsWidget.3">Widget Background Opacity</label>
      <input type="range" id="pcOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="90"
             role="slider" aria-labelledby="pcOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="90" aria-valuetext="90%">
      <span class="pc-opacity-value" id="pcOpacityValue" aria-hidden="true">90%</span>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcWidgetOpacitySlider" id="pcWidgetOpacityLabel" data-i18n="common.settingsWidget.4">Widget Opacity</label>
      <input type="range" id="pcWidgetOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="100"
             role="slider" aria-labelledby="pcWidgetOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="100" aria-valuetext="100%">
      <span class="pc-opacity-value" id="pcWidgetOpacityValue" aria-hidden="true">100%</span>
    </div>

    <!-- TASK (Color Vision Accessibility) START: brief calls for this
         control to sit "alongside the existing dark mode toggle" -- no dark
         mode toggle exists anywhere in this codebase yet (Design System
         Section 19 documents its intended look/behavior but confirms it
         is not yet built; searched before assuming otherwise). Not adding
         a placeholder/stub toggle for a feature that hasn't been
         specified as buildable here -- flagging the gap in this task's
         completion file instead, per "flag it rather than inventing." -->
    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcVisionLabel" data-i18n="common.settingsWidget.5">Color Vision</label>
      <p class="pc-settings-desc" id="pcVisionDesc" data-i18n="common.settingsWidget.6">Adjusts status colors, alerts, and charts sitewide for your selected vision type.</p>
      <div class="pc-lang-dropdown" id="pcVisionDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcVisionTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcVisionLabel"
                aria-controls="pcVisionListbox">
          <span id="pcVisionCurrentLabel">Normal Vision</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcVisionListbox" role="listbox"
            aria-labelledby="pcVisionLabel" tabindex="-1" hidden="">
          <li role="option" data-vision="normal" aria-selected="true" tabindex="0">Normal Vision</li>
          <li role="option" data-vision="deuteranopia" aria-selected="false" tabindex="0">Deuteranopia</li>
          <li role="option" data-vision="protanopia" aria-selected="false" tabindex="0">Protanopia</li>
          <li role="option" data-vision="tritanopia" aria-selected="false" tabindex="0">Tritanopia</li>
        </ul>
      </div>
    </div>
    <!-- TASK (Color Vision Accessibility) END -->
  </div>
</div>

<script>
// TASK-9 START: Settings Widget logic + `preferences` schema owner.
(function () {
  'use strict';

  var PREFS_KEY = 'preferences';
  // Written by admin-dashboard.html's "Default Widget Background Opacity"
  // and "Default Widget Opacity" controls (this task's admin-side
  // deliverable). Read here only as a fallback default for a visitor with
  // no personal preference saved yet -- a visitor's own saved choice in
  // `preferences.opacity` / `preferences.widgetOpacity` always wins.
  var ADMIN_DEFAULTS_KEY = 'pc_admin_settings_defaults';

  // LANG_FOLDER_MAP mirrors Task 1's /en/-/zh/ folder scaffolding -- English
  // is the un-prefixed root (never a redirect target), the rest are their
  // own language-code folders. 'zh-Hans' (tagged, not bare 'zh') is the
  // CEO-confirmed value for Chinese everywhere this schema is used.
  var LANG_FOLDER_MAP = { en: '/', es: '/es/', pt: '/pt/', fr: '/fr/', ar: '/ar/', 'zh-Hans': '/zh/' };
  var LANG_LABELS = { en: 'English', es: 'Español', pt: 'Português', fr: 'Français', ar: 'العربية', 'zh-Hans': '简体中文' };
  var VISION_LABELS = { normal: 'Normal Vision', deuteranopia: 'Deuteranopia', protanopia: 'Protanopia', tritanopia: 'Tritanopia' };

  function readAdminDefaultOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 90;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.opacity);
      return (val >= 20 && val <= 100) ? val : 90;
    } catch (e) { return 90; }
  }

  function readAdminDefaultWidgetOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 100;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.widgetOpacity);
      return (val >= 20 && val <= 100) ? val : 100;
    } catch (e) { return 100; }
  }

  function getPreferences() {
    try {
      var raw = localStorage.getItem(PREFS_KEY);
      var parsed = raw ? JSON.parse(raw) : {};
      return {
        lang: (typeof parsed.lang === 'string' && parsed.lang) ? parsed.lang : 'en',
        opacity: (typeof parsed.opacity === 'number') ? parsed.opacity : readAdminDefaultOpacity(),
        widgetOpacity: (typeof parsed.widgetOpacity === 'number') ? parsed.widgetOpacity : readAdminDefaultWidgetOpacity(),
        colorVision: (typeof parsed.colorVision === 'string' && VISION_LABELS[parsed.colorVision]) ? parsed.colorVision : 'normal'
      };
    } catch (e) {
      return { lang: 'en', opacity: readAdminDefaultOpacity(), widgetOpacity: readAdminDefaultWidgetOpacity(), colorVision: 'normal' };
    }
  }

  function setPreferences(partial) {
    var current = getPreferences();
    var next = { lang: current.lang, opacity: current.opacity, widgetOpacity: current.widgetOpacity, colorVision: current.colorVision };
    for (var k in partial) { if (Object.prototype.hasOwnProperty.call(partial, k)) next[k] = partial[k]; }
    try { localStorage.setItem(PREFS_KEY, JSON.stringify(next)); } catch (e) { /* storage unavailable -- fail silently, in-memory state still applies for this tab */ }
    return next;
  }

  // Panel-only rgba() background opacity -- children stay fully legible
  // regardless of this value.
  function applyBackgroundOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcOpacitySlider');
    var valueEl = document.getElementById('pcOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  // Whole-widget opacity -- real CSS `opacity` on .pc-settings-widget,
  // fades the trigger button and everything inside the panel together.
  function applyWidgetOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcWidgetOpacitySlider');
    var valueEl = document.getElementById('pcWidgetOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  function applyLangSelection(code) {
    var currentLabelEl = document.getElementById('pcLangCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = LANG_LABELS[code] || 'English';
    var options = document.querySelectorAll('#pcLangListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-lang') === code ? 'true' : 'false');
    });
  }

  // Sets the `data-vision` attribute on <html>, which every
  // [data-vision="..."] CSS override block above (and in admin-dashboard.html)
  // keys off of. "normal" matches no override rule, so it's a no-op that
  // simply leaves the default colors in place -- no separate "clear" branch
  // needed.
  function applyVisionSelection(vision) {
    document.documentElement.setAttribute('data-vision', vision);
    var currentLabelEl = document.getElementById('pcVisionCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = VISION_LABELS[vision] || 'Normal Vision';
    var options = document.querySelectorAll('#pcVisionListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-vision') === vision ? 'true' : 'false');
    });
  }

  function navigateToLang(code) {
    var target = LANG_FOLDER_MAP[code] || '/';
    // Cross-document View Transitions (enabled sitewide via the
    // `@view-transition { navigation: auto; }` at-rule in <head>) animate
    // this navigation automatically in supporting browsers -- a plain
    // same-origin navigation is all that's needed to trigger it.
    window.location.href = target;
  }

  // Apply stored/default preferences immediately (before DOMContentLoaded)
  // so both CSS variables are correct before first paint -- avoids a flash
  // of the defaults on a return visitor with different saved values. The
  // `data-vision` attribute is likewise set here, before first paint, so
  // there's no flash of un-adjusted colors either.
  (function initFromStoredPreferencesEarly() {
    var prefs = getPreferences();
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (prefs.opacity / 100).toFixed(2));
    document.documentElement.style.setProperty('--pc-widget-opacity', (prefs.widgetOpacity / 100).toFixed(2));
    document.documentElement.setAttribute('data-vision', prefs.colorVision);
  })();

  document.addEventListener('DOMContentLoaded', function () {
    var trigger = document.getElementById('pcSettingsTrigger');
    var panel = document.getElementById('pcSettingsPanel');
    var closeBtn = document.getElementById('pcSettingsClose');
    var langTrigger = document.getElementById('pcLangTrigger');
    var langListbox = document.getElementById('pcLangListbox');
    var opacitySlider = document.getElementById('pcOpacitySlider');
    var widgetOpacitySlider = document.getElementById('pcWidgetOpacitySlider');
    var visionTrigger = document.getElementById('pcVisionTrigger');
    var visionListbox = document.getElementById('pcVisionListbox');

    if (!trigger || !panel) return;

    var prefs = getPreferences();
    applyBackgroundOpacity(prefs.opacity);
    applyWidgetOpacity(prefs.widgetOpacity);
    applyLangSelection(prefs.lang);
    applyVisionSelection(prefs.colorVision);

    function openPanel() {
      panel.hidden = false;
      trigger.setAttribute('aria-expanded', 'true');
    }
    function closeAllDropdowns() {
      if (langListbox && !langListbox.hidden) {
        langListbox.hidden = true;
        langTrigger.setAttribute('aria-expanded', 'false');
      }
      if (visionListbox && !visionListbox.hidden) {
        visionListbox.hidden = true;
        visionTrigger.setAttribute('aria-expanded', 'false');
      }
    }
    function closePanel() {
      panel.hidden = true;
      trigger.setAttribute('aria-expanded', 'false');
      closeAllDropdowns();
    }

    trigger.addEventListener('click', function () {
      panel.hidden ? openPanel() : closePanel();
    });
    if (closeBtn) closeBtn.addEventListener('click', closePanel);

    document.addEventListener('click', function (e) {
      if (!e.target.closest('#pcSettingsWidget')) closePanel();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closePanel();
    });

    // Language dropdown: open/close + selection.
    if (langTrigger && langListbox) {
      langTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !langListbox.hidden;
        langListbox.hidden = isOpen;
        langTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      langListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var code = opt.getAttribute('data-lang');
        setPreferences({ lang: code });
        applyLangSelection(code);
        closeAllDropdowns();
        navigateToLang(code);
      });
      // Keyboard support: ArrowUp/ArrowDown move focus between options,
      // Enter/Space activates the focused option.
      langListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(langListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          langTrigger.focus();
        }
      });
    }

    // Color Vision dropdown: same open/close/selection/keyboard pattern as
    // the language dropdown above -- unlike language, selecting an option
    // here does NOT navigate anywhere, it just applies immediately.
    if (visionTrigger && visionListbox) {
      visionTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !visionListbox.hidden;
        visionListbox.hidden = isOpen;
        visionTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      visionListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var vision = opt.getAttribute('data-vision');
        setPreferences({ colorVision: vision });
        applyVisionSelection(vision);
        closeAllDropdowns();
      });
      visionListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(visionListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          visionTrigger.focus();
        }
      });
    }

    // Both opacity sliders -- native <input type="range"> already provides
    // full keyboard support (Arrow keys, Home/End, PageUp/PageDown); role
    // and aria-value* attributes are set explicitly per this task's spec.
    if (opacitySlider) {
      opacitySlider.addEventListener('input', function () {
        var value = Number(opacitySlider.value);
        applyBackgroundOpacity(value);
        setPreferences({ opacity: value });
      });
    }
    if (widgetOpacitySlider) {
      widgetOpacitySlider.addEventListener('input', function () {
        var value = Number(widgetOpacitySlider.value);
        applyWidgetOpacity(value);
        setPreferences({ widgetOpacity: value });
      });
    }

    // Native `storage` event -- fires in OTHER tabs/windows on this origin
    // when `preferences` changes there, and vice versa. This is the
    // cross-tab sync mechanism this task requires; no polling needed.
    window.addEventListener('storage', function (e) {
      if (e.key !== PREFS_KEY) return;
      var updated = getPreferences();
      applyBackgroundOpacity(updated.opacity);
      applyWidgetOpacity(updated.widgetOpacity);
      applyLangSelection(updated.lang);
      applyVisionSelection(updated.colorVision);
    });
  });
})();
// TASK-9 END
</script>
</body>
```
<!-- TASK-10 END -->

### `birds.html`  →  live path `pets/birds/index.html`

<!-- TASK-10 START -->
**Change 1 of 2 — View Transitions style block, before `</head>`**

**Old code:**
```html
  </head>
```
**New code:**
```html
<!-- TASK-9 START: cross-document View Transitions API, enabled sitewide.
     Animates same-origin full-page navigations (e.g. the Settings Widget's
     language switcher navigating between /, /es/, /pt/, /fr/, /ar/, /zh/)
     in browsers that support the cross-document View Transitions API
     (Chromium 126+). Browsers without support ignore this at-rule and
     navigate normally with no error -- this is a progressive enhancement,
     not a dependency. -->
<style>
@view-transition {
  navigation: auto;
}
</style>
<!-- TASK-9 END -->
  </head>
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Change 2 of 2 — Settings Widget (style + markup + script), before `</body>`**

**Old code:**
```html
</body>
```
**New code:**
```html
<!-- TASK-9 START: Settings Widget -- gear-icon trigger + panel housing the
     sitewide language switcher and the personal widget-background-opacity
     slider. Defines the shared `preferences` object (localStorage key
     "preferences", fields {lang, opacity}) that this page's own
     lang-redirect.js / i18n-core.js (Task 1) already read `preferences.lang`
     from, and that Admin-Project #4's `preferred_chat_language` field also
     reads independently -- the exact field name/shape/values documented in
     this task must not change without notifying that project first.
     Z-INDEX NOTE: the site's real, documented highest z-index anywhere is
     6001 (the filter/form bottom-sheet system), not the floating contact
     button's 4000 -- this widget's trigger/panel/dropdown are placed at
     6100/6200/6300 so they always sit above every existing floating
     element, including both of those. -->
<style>
/* Renamed from --pc-widget-opacity (added in this task's first pass) to
   --pc-widget-bg-opacity, and a second, distinct --pc-widget-opacity
   variable added for the whole widget's own opacity -- per user follow-up,
   "Widget Background Opacity" (panel's rgba background only, children
   stay fully legible) and "Widget Opacity" (the entire trigger + panel,
   via real CSS `opacity` on the container) are two separate controls. */
:root { --pc-widget-bg-opacity: 0.90; --pc-widget-opacity: 1; }

/* "Central position" = vertically centered on the viewport's right edge.
   User follow-up moved this from the left edge (this task's original
   build) to the right edge -- everything else about the placement logic
   from that first pass still applies: vertically centered avoids the
   hero's own CTA buttons (the collision found and fixed when this was
   briefly bottom-center), and vertical centering keeps clear of the
   bottom-right floating contact button (z-index 4000), re-confirmed by
   Playwright screenshot below since moving to the same edge as that
   button is exactly the kind of collision worth re-checking, not
   assuming safe by reasoning alone. */
.pc-settings-widget {
  position: fixed;
  top: 50%;
  right: 16px;
  transform: translateY(-50%);
  z-index: 6100;
  font-family: 'Sora', sans-serif;
  /* Whole-widget opacity (trigger + panel together), separate from the
     panel-only rgba background opacity below. Real CSS `opacity`, by
     design, fades everything inside -- that's the intended effect of this
     specific control, unlike the background one. */
  opacity: var(--pc-widget-opacity);
  transition: opacity 0.2s ease;
}

.pc-settings-trigger {
  width: 52px; height: 52px; border-radius: 50%; border: none; cursor: pointer;
  background: #10214b; color: #d7bd88;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 16px rgba(16,33,75,0.35);
  transition: transform 0.3s cubic-bezier(.4,0,.2,1), background 0.3s cubic-bezier(.4,0,.2,1);
}
.pc-settings-trigger:hover { transform: rotate(35deg); background: #1a2f5c; }
.pc-settings-trigger[aria-expanded="true"] { background: #ED7624; color: #fff; }

.pc-settings-panel {
  position: absolute;
  top: 50%;
  /* Opens to the LEFT of the trigger now that the trigger sits on the
     right edge (was `left: calc(100% + 12px)` opening rightward off a
     left-edge trigger). */
  right: calc(100% + 12px);
  transform: translateY(-50%);
  width: 280px;
  max-width: calc(100vw - 32px);
  /* rgba() background driven by the Widget Background Opacity slider --
     never container `opacity`, which would also fade the panel's
     text/children (Task 9's explicit requirement: "rgba() backgrounds,
     not container opacity"). The separate whole-widget opacity control
     lives on .pc-settings-widget above. */
  background: rgba(16, 33, 75, var(--pc-widget-bg-opacity));
  color: #f2f2f2;
  border-radius: 14px;
  padding: 16px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.3);
  z-index: 6200;
}
.pc-settings-panel[hidden] { display: none; }

.pc-settings-panel__header {
  display: flex; align-items: center; justify-content: space-between;
  font-weight: 700; margin-bottom: 12px; font-size: 15px;
}
.pc-settings-panel__close {
  background: none; border: none; color: inherit; font-size: 20px; line-height: 1;
  cursor: pointer; padding: 2px 6px;
}

.pc-settings-row { margin-bottom: 14px; }
.pc-settings-row:last-child { margin-bottom: 0; }
.pc-settings-label { display: block; font-size: 12.5px; margin-bottom: 6px; color: #d7bd88; }
/* One-line description under a consequential setting, per Design System
   Section 19's "Settings Widget Control Pattern -- Toggle + Description"
   rule (pair every consequential control with a plain-language line
   explaining its effect, since a user shouldn't have to guess). */
.pc-settings-desc { font-size: 11.5px; color: #a39b91; margin: -2px 0 8px; line-height: 1.4; }

.pc-lang-dropdown { position: relative; }
.pc-lang-dropdown__trigger {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.16);
  color: #f2f2f2; border-radius: 8px; padding: 8px 12px; font-size: 13.5px; cursor: pointer;
  font-family: 'Sora', sans-serif;
}
.pc-lang-dropdown__panel {
  position: absolute; top: calc(100% + 6px); left: 0; right: 0;
  background: #10214b; border: 1px solid rgba(255,255,255,0.14);
  /* Sharp corner nearest the trigger (top-left here, since the trigger
     sits above this downward-opening panel) -- the "message bubble" corner
     rule from PETCENTER_DESIGN_SYSTEM_v1_0.md Section 24 (v1.25). */
  border-radius: 0 8px 8px 8px;
  list-style: none; margin: 0; padding: 4px; max-height: 200px; overflow-y: auto;
  z-index: 6300;
}
.pc-lang-dropdown__panel[hidden] { display: none; }
.pc-lang-dropdown__panel li { padding: 8px 10px; font-size: 13.5px; border-radius: 6px; cursor: pointer; }
.pc-lang-dropdown__panel li:hover,
.pc-lang-dropdown__panel li[aria-selected="true"] { background: rgba(215,189,136,0.18); }

.pc-opacity-slider { width: 100%; accent-color: #d7bd88; }
.pc-opacity-value { font-size: 12px; color: #a39b91; float: right; margin-top: 2px; }

@media (max-width: 600px) {
  /* Below 600px, opening the panel to the right of a left-edge trigger
     would push a 280px-wide panel off the right edge of the viewport on
     most phones. Anchor the panel to open downward from the trigger
     instead, still clear of both the hero content and the bottom-right
     contact button. Anchored via `right: 0` (not `left: 0`) since the
     trigger itself now sits near the right edge -- `left: 0` would push
     a near-full-width panel further right and off-screen. */
  .pc-settings-panel {
    top: calc(100% + 10px);
    right: 0;
    left: auto;
    transform: none;
    width: calc(100vw - 48px);
  }
}
</style>

<div class="pc-settings-widget" id="pcSettingsWidget">
  <button type="button" class="pc-settings-trigger" id="pcSettingsTrigger"
          aria-haspopup="true" aria-expanded="false" aria-controls="pcSettingsPanel"
          aria-label="Open site settings">
    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <circle cx="12" cy="12" r="3"></circle>
      <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
    </svg>
  </button>

  <div class="pc-settings-panel" id="pcSettingsPanel" role="dialog" aria-modal="false"
       aria-label="Site settings" hidden="">
    <div class="pc-settings-panel__header">
      <span data-i18n="common.settingsWidget.1">Settings</span>
      <button type="button" class="pc-settings-panel__close" id="pcSettingsClose"
              aria-label="Close settings">&times;</button>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcLangLabel" data-i18n="common.settingsWidget.2">Language</label>
      <div class="pc-lang-dropdown" id="pcLangDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcLangTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcLangLabel"
                aria-controls="pcLangListbox">
          <span id="pcLangCurrentLabel">English</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcLangListbox" role="listbox"
            aria-labelledby="pcLangLabel" tabindex="-1" hidden="">
          <li role="option" data-lang="en" aria-selected="true" tabindex="0">English</li>
          <li role="option" data-lang="es" aria-selected="false" tabindex="0">Español</li>
          <li role="option" data-lang="pt" aria-selected="false" tabindex="0">Português</li>
          <li role="option" data-lang="fr" aria-selected="false" tabindex="0">Français</li>
          <li role="option" data-lang="ar" aria-selected="false" tabindex="0">العربية</li>
          <li role="option" data-lang="zh-Hans" aria-selected="false" tabindex="0">简体中文</li>
        </ul>
      </div>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcOpacitySlider" id="pcOpacityLabel" data-i18n="common.settingsWidget.3">Widget Background Opacity</label>
      <input type="range" id="pcOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="90"
             role="slider" aria-labelledby="pcOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="90" aria-valuetext="90%">
      <span class="pc-opacity-value" id="pcOpacityValue" aria-hidden="true">90%</span>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcWidgetOpacitySlider" id="pcWidgetOpacityLabel" data-i18n="common.settingsWidget.4">Widget Opacity</label>
      <input type="range" id="pcWidgetOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="100"
             role="slider" aria-labelledby="pcWidgetOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="100" aria-valuetext="100%">
      <span class="pc-opacity-value" id="pcWidgetOpacityValue" aria-hidden="true">100%</span>
    </div>

    <!-- TASK (Color Vision Accessibility) START: brief calls for this
         control to sit "alongside the existing dark mode toggle" -- no dark
         mode toggle exists anywhere in this codebase yet (Design System
         Section 19 documents its intended look/behavior but confirms it
         is not yet built; searched before assuming otherwise). Not adding
         a placeholder/stub toggle for a feature that hasn't been
         specified as buildable here -- flagging the gap in this task's
         completion file instead, per "flag it rather than inventing." -->
    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcVisionLabel" data-i18n="common.settingsWidget.5">Color Vision</label>
      <p class="pc-settings-desc" id="pcVisionDesc" data-i18n="common.settingsWidget.6">Adjusts status colors, alerts, and charts sitewide for your selected vision type.</p>
      <div class="pc-lang-dropdown" id="pcVisionDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcVisionTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcVisionLabel"
                aria-controls="pcVisionListbox">
          <span id="pcVisionCurrentLabel">Normal Vision</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcVisionListbox" role="listbox"
            aria-labelledby="pcVisionLabel" tabindex="-1" hidden="">
          <li role="option" data-vision="normal" aria-selected="true" tabindex="0">Normal Vision</li>
          <li role="option" data-vision="deuteranopia" aria-selected="false" tabindex="0">Deuteranopia</li>
          <li role="option" data-vision="protanopia" aria-selected="false" tabindex="0">Protanopia</li>
          <li role="option" data-vision="tritanopia" aria-selected="false" tabindex="0">Tritanopia</li>
        </ul>
      </div>
    </div>
    <!-- TASK (Color Vision Accessibility) END -->
  </div>
</div>

<script>
// TASK-9 START: Settings Widget logic + `preferences` schema owner.
(function () {
  'use strict';

  var PREFS_KEY = 'preferences';
  // Written by admin-dashboard.html's "Default Widget Background Opacity"
  // and "Default Widget Opacity" controls (this task's admin-side
  // deliverable). Read here only as a fallback default for a visitor with
  // no personal preference saved yet -- a visitor's own saved choice in
  // `preferences.opacity` / `preferences.widgetOpacity` always wins.
  var ADMIN_DEFAULTS_KEY = 'pc_admin_settings_defaults';

  // LANG_FOLDER_MAP mirrors Task 1's /en/-/zh/ folder scaffolding -- English
  // is the un-prefixed root (never a redirect target), the rest are their
  // own language-code folders. 'zh-Hans' (tagged, not bare 'zh') is the
  // CEO-confirmed value for Chinese everywhere this schema is used.
  var LANG_FOLDER_MAP = { en: '/', es: '/es/', pt: '/pt/', fr: '/fr/', ar: '/ar/', 'zh-Hans': '/zh/' };
  var LANG_LABELS = { en: 'English', es: 'Español', pt: 'Português', fr: 'Français', ar: 'العربية', 'zh-Hans': '简体中文' };
  var VISION_LABELS = { normal: 'Normal Vision', deuteranopia: 'Deuteranopia', protanopia: 'Protanopia', tritanopia: 'Tritanopia' };

  function readAdminDefaultOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 90;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.opacity);
      return (val >= 20 && val <= 100) ? val : 90;
    } catch (e) { return 90; }
  }

  function readAdminDefaultWidgetOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 100;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.widgetOpacity);
      return (val >= 20 && val <= 100) ? val : 100;
    } catch (e) { return 100; }
  }

  function getPreferences() {
    try {
      var raw = localStorage.getItem(PREFS_KEY);
      var parsed = raw ? JSON.parse(raw) : {};
      return {
        lang: (typeof parsed.lang === 'string' && parsed.lang) ? parsed.lang : 'en',
        opacity: (typeof parsed.opacity === 'number') ? parsed.opacity : readAdminDefaultOpacity(),
        widgetOpacity: (typeof parsed.widgetOpacity === 'number') ? parsed.widgetOpacity : readAdminDefaultWidgetOpacity(),
        colorVision: (typeof parsed.colorVision === 'string' && VISION_LABELS[parsed.colorVision]) ? parsed.colorVision : 'normal'
      };
    } catch (e) {
      return { lang: 'en', opacity: readAdminDefaultOpacity(), widgetOpacity: readAdminDefaultWidgetOpacity(), colorVision: 'normal' };
    }
  }

  function setPreferences(partial) {
    var current = getPreferences();
    var next = { lang: current.lang, opacity: current.opacity, widgetOpacity: current.widgetOpacity, colorVision: current.colorVision };
    for (var k in partial) { if (Object.prototype.hasOwnProperty.call(partial, k)) next[k] = partial[k]; }
    try { localStorage.setItem(PREFS_KEY, JSON.stringify(next)); } catch (e) { /* storage unavailable -- fail silently, in-memory state still applies for this tab */ }
    return next;
  }

  // Panel-only rgba() background opacity -- children stay fully legible
  // regardless of this value.
  function applyBackgroundOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcOpacitySlider');
    var valueEl = document.getElementById('pcOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  // Whole-widget opacity -- real CSS `opacity` on .pc-settings-widget,
  // fades the trigger button and everything inside the panel together.
  function applyWidgetOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcWidgetOpacitySlider');
    var valueEl = document.getElementById('pcWidgetOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  function applyLangSelection(code) {
    var currentLabelEl = document.getElementById('pcLangCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = LANG_LABELS[code] || 'English';
    var options = document.querySelectorAll('#pcLangListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-lang') === code ? 'true' : 'false');
    });
  }

  // Sets the `data-vision` attribute on <html>, which every
  // [data-vision="..."] CSS override block above (and in admin-dashboard.html)
  // keys off of. "normal" matches no override rule, so it's a no-op that
  // simply leaves the default colors in place -- no separate "clear" branch
  // needed.
  function applyVisionSelection(vision) {
    document.documentElement.setAttribute('data-vision', vision);
    var currentLabelEl = document.getElementById('pcVisionCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = VISION_LABELS[vision] || 'Normal Vision';
    var options = document.querySelectorAll('#pcVisionListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-vision') === vision ? 'true' : 'false');
    });
  }

  function navigateToLang(code) {
    var target = LANG_FOLDER_MAP[code] || '/';
    // Cross-document View Transitions (enabled sitewide via the
    // `@view-transition { navigation: auto; }` at-rule in <head>) animate
    // this navigation automatically in supporting browsers -- a plain
    // same-origin navigation is all that's needed to trigger it.
    window.location.href = target;
  }

  // Apply stored/default preferences immediately (before DOMContentLoaded)
  // so both CSS variables are correct before first paint -- avoids a flash
  // of the defaults on a return visitor with different saved values. The
  // `data-vision` attribute is likewise set here, before first paint, so
  // there's no flash of un-adjusted colors either.
  (function initFromStoredPreferencesEarly() {
    var prefs = getPreferences();
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (prefs.opacity / 100).toFixed(2));
    document.documentElement.style.setProperty('--pc-widget-opacity', (prefs.widgetOpacity / 100).toFixed(2));
    document.documentElement.setAttribute('data-vision', prefs.colorVision);
  })();

  document.addEventListener('DOMContentLoaded', function () {
    var trigger = document.getElementById('pcSettingsTrigger');
    var panel = document.getElementById('pcSettingsPanel');
    var closeBtn = document.getElementById('pcSettingsClose');
    var langTrigger = document.getElementById('pcLangTrigger');
    var langListbox = document.getElementById('pcLangListbox');
    var opacitySlider = document.getElementById('pcOpacitySlider');
    var widgetOpacitySlider = document.getElementById('pcWidgetOpacitySlider');
    var visionTrigger = document.getElementById('pcVisionTrigger');
    var visionListbox = document.getElementById('pcVisionListbox');

    if (!trigger || !panel) return;

    var prefs = getPreferences();
    applyBackgroundOpacity(prefs.opacity);
    applyWidgetOpacity(prefs.widgetOpacity);
    applyLangSelection(prefs.lang);
    applyVisionSelection(prefs.colorVision);

    function openPanel() {
      panel.hidden = false;
      trigger.setAttribute('aria-expanded', 'true');
    }
    function closeAllDropdowns() {
      if (langListbox && !langListbox.hidden) {
        langListbox.hidden = true;
        langTrigger.setAttribute('aria-expanded', 'false');
      }
      if (visionListbox && !visionListbox.hidden) {
        visionListbox.hidden = true;
        visionTrigger.setAttribute('aria-expanded', 'false');
      }
    }
    function closePanel() {
      panel.hidden = true;
      trigger.setAttribute('aria-expanded', 'false');
      closeAllDropdowns();
    }

    trigger.addEventListener('click', function () {
      panel.hidden ? openPanel() : closePanel();
    });
    if (closeBtn) closeBtn.addEventListener('click', closePanel);

    document.addEventListener('click', function (e) {
      if (!e.target.closest('#pcSettingsWidget')) closePanel();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closePanel();
    });

    // Language dropdown: open/close + selection.
    if (langTrigger && langListbox) {
      langTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !langListbox.hidden;
        langListbox.hidden = isOpen;
        langTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      langListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var code = opt.getAttribute('data-lang');
        setPreferences({ lang: code });
        applyLangSelection(code);
        closeAllDropdowns();
        navigateToLang(code);
      });
      // Keyboard support: ArrowUp/ArrowDown move focus between options,
      // Enter/Space activates the focused option.
      langListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(langListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          langTrigger.focus();
        }
      });
    }

    // Color Vision dropdown: same open/close/selection/keyboard pattern as
    // the language dropdown above -- unlike language, selecting an option
    // here does NOT navigate anywhere, it just applies immediately.
    if (visionTrigger && visionListbox) {
      visionTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !visionListbox.hidden;
        visionListbox.hidden = isOpen;
        visionTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      visionListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var vision = opt.getAttribute('data-vision');
        setPreferences({ colorVision: vision });
        applyVisionSelection(vision);
        closeAllDropdowns();
      });
      visionListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(visionListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          visionTrigger.focus();
        }
      });
    }

    // Both opacity sliders -- native <input type="range"> already provides
    // full keyboard support (Arrow keys, Home/End, PageUp/PageDown); role
    // and aria-value* attributes are set explicitly per this task's spec.
    if (opacitySlider) {
      opacitySlider.addEventListener('input', function () {
        var value = Number(opacitySlider.value);
        applyBackgroundOpacity(value);
        setPreferences({ opacity: value });
      });
    }
    if (widgetOpacitySlider) {
      widgetOpacitySlider.addEventListener('input', function () {
        var value = Number(widgetOpacitySlider.value);
        applyWidgetOpacity(value);
        setPreferences({ widgetOpacity: value });
      });
    }

    // Native `storage` event -- fires in OTHER tabs/windows on this origin
    // when `preferences` changes there, and vice versa. This is the
    // cross-tab sync mechanism this task requires; no polling needed.
    window.addEventListener('storage', function (e) {
      if (e.key !== PREFS_KEY) return;
      var updated = getPreferences();
      applyBackgroundOpacity(updated.opacity);
      applyWidgetOpacity(updated.widgetOpacity);
      applyLangSelection(updated.lang);
      applyVisionSelection(updated.colorVision);
    });
  });
})();
// TASK-9 END
</script>
</body>
```
<!-- TASK-10 END -->

### `reptiles.html`  →  live path `pets/reptiles/index.html`

<!-- TASK-10 START -->
**Change 1 of 2 — View Transitions style block, before `</head>`**

**Old code:**
```html
  </head>
```
**New code:**
```html
<!-- TASK-9 START: cross-document View Transitions API, enabled sitewide.
     Animates same-origin full-page navigations (e.g. the Settings Widget's
     language switcher navigating between /, /es/, /pt/, /fr/, /ar/, /zh/)
     in browsers that support the cross-document View Transitions API
     (Chromium 126+). Browsers without support ignore this at-rule and
     navigate normally with no error -- this is a progressive enhancement,
     not a dependency. -->
<style>
@view-transition {
  navigation: auto;
}
</style>
<!-- TASK-9 END -->
  </head>
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Change 2 of 2 — Settings Widget (style + markup + script), before `</body>`**

**Old code:**
```html
</body>
```
**New code:**
```html
<!-- TASK-9 START: Settings Widget -- gear-icon trigger + panel housing the
     sitewide language switcher and the personal widget-background-opacity
     slider. Defines the shared `preferences` object (localStorage key
     "preferences", fields {lang, opacity}) that this page's own
     lang-redirect.js / i18n-core.js (Task 1) already read `preferences.lang`
     from, and that Admin-Project #4's `preferred_chat_language` field also
     reads independently -- the exact field name/shape/values documented in
     this task must not change without notifying that project first.
     Z-INDEX NOTE: the site's real, documented highest z-index anywhere is
     6001 (the filter/form bottom-sheet system), not the floating contact
     button's 4000 -- this widget's trigger/panel/dropdown are placed at
     6100/6200/6300 so they always sit above every existing floating
     element, including both of those. -->
<style>
/* Renamed from --pc-widget-opacity (added in this task's first pass) to
   --pc-widget-bg-opacity, and a second, distinct --pc-widget-opacity
   variable added for the whole widget's own opacity -- per user follow-up,
   "Widget Background Opacity" (panel's rgba background only, children
   stay fully legible) and "Widget Opacity" (the entire trigger + panel,
   via real CSS `opacity` on the container) are two separate controls. */
:root { --pc-widget-bg-opacity: 0.90; --pc-widget-opacity: 1; }

/* "Central position" = vertically centered on the viewport's right edge.
   User follow-up moved this from the left edge (this task's original
   build) to the right edge -- everything else about the placement logic
   from that first pass still applies: vertically centered avoids the
   hero's own CTA buttons (the collision found and fixed when this was
   briefly bottom-center), and vertical centering keeps clear of the
   bottom-right floating contact button (z-index 4000), re-confirmed by
   Playwright screenshot below since moving to the same edge as that
   button is exactly the kind of collision worth re-checking, not
   assuming safe by reasoning alone. */
.pc-settings-widget {
  position: fixed;
  top: 50%;
  right: 16px;
  transform: translateY(-50%);
  z-index: 6100;
  font-family: 'Sora', sans-serif;
  /* Whole-widget opacity (trigger + panel together), separate from the
     panel-only rgba background opacity below. Real CSS `opacity`, by
     design, fades everything inside -- that's the intended effect of this
     specific control, unlike the background one. */
  opacity: var(--pc-widget-opacity);
  transition: opacity 0.2s ease;
}

.pc-settings-trigger {
  width: 52px; height: 52px; border-radius: 50%; border: none; cursor: pointer;
  background: #10214b; color: #d7bd88;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 16px rgba(16,33,75,0.35);
  transition: transform 0.3s cubic-bezier(.4,0,.2,1), background 0.3s cubic-bezier(.4,0,.2,1);
}
.pc-settings-trigger:hover { transform: rotate(35deg); background: #1a2f5c; }
.pc-settings-trigger[aria-expanded="true"] { background: #ED7624; color: #fff; }

.pc-settings-panel {
  position: absolute;
  top: 50%;
  /* Opens to the LEFT of the trigger now that the trigger sits on the
     right edge (was `left: calc(100% + 12px)` opening rightward off a
     left-edge trigger). */
  right: calc(100% + 12px);
  transform: translateY(-50%);
  width: 280px;
  max-width: calc(100vw - 32px);
  /* rgba() background driven by the Widget Background Opacity slider --
     never container `opacity`, which would also fade the panel's
     text/children (Task 9's explicit requirement: "rgba() backgrounds,
     not container opacity"). The separate whole-widget opacity control
     lives on .pc-settings-widget above. */
  background: rgba(16, 33, 75, var(--pc-widget-bg-opacity));
  color: #f2f2f2;
  border-radius: 14px;
  padding: 16px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.3);
  z-index: 6200;
}
.pc-settings-panel[hidden] { display: none; }

.pc-settings-panel__header {
  display: flex; align-items: center; justify-content: space-between;
  font-weight: 700; margin-bottom: 12px; font-size: 15px;
}
.pc-settings-panel__close {
  background: none; border: none; color: inherit; font-size: 20px; line-height: 1;
  cursor: pointer; padding: 2px 6px;
}

.pc-settings-row { margin-bottom: 14px; }
.pc-settings-row:last-child { margin-bottom: 0; }
.pc-settings-label { display: block; font-size: 12.5px; margin-bottom: 6px; color: #d7bd88; }
/* One-line description under a consequential setting, per Design System
   Section 19's "Settings Widget Control Pattern -- Toggle + Description"
   rule (pair every consequential control with a plain-language line
   explaining its effect, since a user shouldn't have to guess). */
.pc-settings-desc { font-size: 11.5px; color: #a39b91; margin: -2px 0 8px; line-height: 1.4; }

.pc-lang-dropdown { position: relative; }
.pc-lang-dropdown__trigger {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.16);
  color: #f2f2f2; border-radius: 8px; padding: 8px 12px; font-size: 13.5px; cursor: pointer;
  font-family: 'Sora', sans-serif;
}
.pc-lang-dropdown__panel {
  position: absolute; top: calc(100% + 6px); left: 0; right: 0;
  background: #10214b; border: 1px solid rgba(255,255,255,0.14);
  /* Sharp corner nearest the trigger (top-left here, since the trigger
     sits above this downward-opening panel) -- the "message bubble" corner
     rule from PETCENTER_DESIGN_SYSTEM_v1_0.md Section 24 (v1.25). */
  border-radius: 0 8px 8px 8px;
  list-style: none; margin: 0; padding: 4px; max-height: 200px; overflow-y: auto;
  z-index: 6300;
}
.pc-lang-dropdown__panel[hidden] { display: none; }
.pc-lang-dropdown__panel li { padding: 8px 10px; font-size: 13.5px; border-radius: 6px; cursor: pointer; }
.pc-lang-dropdown__panel li:hover,
.pc-lang-dropdown__panel li[aria-selected="true"] { background: rgba(215,189,136,0.18); }

.pc-opacity-slider { width: 100%; accent-color: #d7bd88; }
.pc-opacity-value { font-size: 12px; color: #a39b91; float: right; margin-top: 2px; }

@media (max-width: 600px) {
  /* Below 600px, opening the panel to the right of a left-edge trigger
     would push a 280px-wide panel off the right edge of the viewport on
     most phones. Anchor the panel to open downward from the trigger
     instead, still clear of both the hero content and the bottom-right
     contact button. Anchored via `right: 0` (not `left: 0`) since the
     trigger itself now sits near the right edge -- `left: 0` would push
     a near-full-width panel further right and off-screen. */
  .pc-settings-panel {
    top: calc(100% + 10px);
    right: 0;
    left: auto;
    transform: none;
    width: calc(100vw - 48px);
  }
}
</style>

<div class="pc-settings-widget" id="pcSettingsWidget">
  <button type="button" class="pc-settings-trigger" id="pcSettingsTrigger"
          aria-haspopup="true" aria-expanded="false" aria-controls="pcSettingsPanel"
          aria-label="Open site settings">
    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <circle cx="12" cy="12" r="3"></circle>
      <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
    </svg>
  </button>

  <div class="pc-settings-panel" id="pcSettingsPanel" role="dialog" aria-modal="false"
       aria-label="Site settings" hidden="">
    <div class="pc-settings-panel__header">
      <span data-i18n="common.settingsWidget.1">Settings</span>
      <button type="button" class="pc-settings-panel__close" id="pcSettingsClose"
              aria-label="Close settings">&times;</button>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcLangLabel" data-i18n="common.settingsWidget.2">Language</label>
      <div class="pc-lang-dropdown" id="pcLangDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcLangTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcLangLabel"
                aria-controls="pcLangListbox">
          <span id="pcLangCurrentLabel">English</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcLangListbox" role="listbox"
            aria-labelledby="pcLangLabel" tabindex="-1" hidden="">
          <li role="option" data-lang="en" aria-selected="true" tabindex="0">English</li>
          <li role="option" data-lang="es" aria-selected="false" tabindex="0">Español</li>
          <li role="option" data-lang="pt" aria-selected="false" tabindex="0">Português</li>
          <li role="option" data-lang="fr" aria-selected="false" tabindex="0">Français</li>
          <li role="option" data-lang="ar" aria-selected="false" tabindex="0">العربية</li>
          <li role="option" data-lang="zh-Hans" aria-selected="false" tabindex="0">简体中文</li>
        </ul>
      </div>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcOpacitySlider" id="pcOpacityLabel" data-i18n="common.settingsWidget.3">Widget Background Opacity</label>
      <input type="range" id="pcOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="90"
             role="slider" aria-labelledby="pcOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="90" aria-valuetext="90%">
      <span class="pc-opacity-value" id="pcOpacityValue" aria-hidden="true">90%</span>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcWidgetOpacitySlider" id="pcWidgetOpacityLabel" data-i18n="common.settingsWidget.4">Widget Opacity</label>
      <input type="range" id="pcWidgetOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="100"
             role="slider" aria-labelledby="pcWidgetOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="100" aria-valuetext="100%">
      <span class="pc-opacity-value" id="pcWidgetOpacityValue" aria-hidden="true">100%</span>
    </div>

    <!-- TASK (Color Vision Accessibility) START: brief calls for this
         control to sit "alongside the existing dark mode toggle" -- no dark
         mode toggle exists anywhere in this codebase yet (Design System
         Section 19 documents its intended look/behavior but confirms it
         is not yet built; searched before assuming otherwise). Not adding
         a placeholder/stub toggle for a feature that hasn't been
         specified as buildable here -- flagging the gap in this task's
         completion file instead, per "flag it rather than inventing." -->
    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcVisionLabel" data-i18n="common.settingsWidget.5">Color Vision</label>
      <p class="pc-settings-desc" id="pcVisionDesc" data-i18n="common.settingsWidget.6">Adjusts status colors, alerts, and charts sitewide for your selected vision type.</p>
      <div class="pc-lang-dropdown" id="pcVisionDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcVisionTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcVisionLabel"
                aria-controls="pcVisionListbox">
          <span id="pcVisionCurrentLabel">Normal Vision</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcVisionListbox" role="listbox"
            aria-labelledby="pcVisionLabel" tabindex="-1" hidden="">
          <li role="option" data-vision="normal" aria-selected="true" tabindex="0">Normal Vision</li>
          <li role="option" data-vision="deuteranopia" aria-selected="false" tabindex="0">Deuteranopia</li>
          <li role="option" data-vision="protanopia" aria-selected="false" tabindex="0">Protanopia</li>
          <li role="option" data-vision="tritanopia" aria-selected="false" tabindex="0">Tritanopia</li>
        </ul>
      </div>
    </div>
    <!-- TASK (Color Vision Accessibility) END -->
  </div>
</div>

<script>
// TASK-9 START: Settings Widget logic + `preferences` schema owner.
(function () {
  'use strict';

  var PREFS_KEY = 'preferences';
  // Written by admin-dashboard.html's "Default Widget Background Opacity"
  // and "Default Widget Opacity" controls (this task's admin-side
  // deliverable). Read here only as a fallback default for a visitor with
  // no personal preference saved yet -- a visitor's own saved choice in
  // `preferences.opacity` / `preferences.widgetOpacity` always wins.
  var ADMIN_DEFAULTS_KEY = 'pc_admin_settings_defaults';

  // LANG_FOLDER_MAP mirrors Task 1's /en/-/zh/ folder scaffolding -- English
  // is the un-prefixed root (never a redirect target), the rest are their
  // own language-code folders. 'zh-Hans' (tagged, not bare 'zh') is the
  // CEO-confirmed value for Chinese everywhere this schema is used.
  var LANG_FOLDER_MAP = { en: '/', es: '/es/', pt: '/pt/', fr: '/fr/', ar: '/ar/', 'zh-Hans': '/zh/' };
  var LANG_LABELS = { en: 'English', es: 'Español', pt: 'Português', fr: 'Français', ar: 'العربية', 'zh-Hans': '简体中文' };
  var VISION_LABELS = { normal: 'Normal Vision', deuteranopia: 'Deuteranopia', protanopia: 'Protanopia', tritanopia: 'Tritanopia' };

  function readAdminDefaultOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 90;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.opacity);
      return (val >= 20 && val <= 100) ? val : 90;
    } catch (e) { return 90; }
  }

  function readAdminDefaultWidgetOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 100;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.widgetOpacity);
      return (val >= 20 && val <= 100) ? val : 100;
    } catch (e) { return 100; }
  }

  function getPreferences() {
    try {
      var raw = localStorage.getItem(PREFS_KEY);
      var parsed = raw ? JSON.parse(raw) : {};
      return {
        lang: (typeof parsed.lang === 'string' && parsed.lang) ? parsed.lang : 'en',
        opacity: (typeof parsed.opacity === 'number') ? parsed.opacity : readAdminDefaultOpacity(),
        widgetOpacity: (typeof parsed.widgetOpacity === 'number') ? parsed.widgetOpacity : readAdminDefaultWidgetOpacity(),
        colorVision: (typeof parsed.colorVision === 'string' && VISION_LABELS[parsed.colorVision]) ? parsed.colorVision : 'normal'
      };
    } catch (e) {
      return { lang: 'en', opacity: readAdminDefaultOpacity(), widgetOpacity: readAdminDefaultWidgetOpacity(), colorVision: 'normal' };
    }
  }

  function setPreferences(partial) {
    var current = getPreferences();
    var next = { lang: current.lang, opacity: current.opacity, widgetOpacity: current.widgetOpacity, colorVision: current.colorVision };
    for (var k in partial) { if (Object.prototype.hasOwnProperty.call(partial, k)) next[k] = partial[k]; }
    try { localStorage.setItem(PREFS_KEY, JSON.stringify(next)); } catch (e) { /* storage unavailable -- fail silently, in-memory state still applies for this tab */ }
    return next;
  }

  // Panel-only rgba() background opacity -- children stay fully legible
  // regardless of this value.
  function applyBackgroundOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcOpacitySlider');
    var valueEl = document.getElementById('pcOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  // Whole-widget opacity -- real CSS `opacity` on .pc-settings-widget,
  // fades the trigger button and everything inside the panel together.
  function applyWidgetOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcWidgetOpacitySlider');
    var valueEl = document.getElementById('pcWidgetOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  function applyLangSelection(code) {
    var currentLabelEl = document.getElementById('pcLangCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = LANG_LABELS[code] || 'English';
    var options = document.querySelectorAll('#pcLangListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-lang') === code ? 'true' : 'false');
    });
  }

  // Sets the `data-vision` attribute on <html>, which every
  // [data-vision="..."] CSS override block above (and in admin-dashboard.html)
  // keys off of. "normal" matches no override rule, so it's a no-op that
  // simply leaves the default colors in place -- no separate "clear" branch
  // needed.
  function applyVisionSelection(vision) {
    document.documentElement.setAttribute('data-vision', vision);
    var currentLabelEl = document.getElementById('pcVisionCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = VISION_LABELS[vision] || 'Normal Vision';
    var options = document.querySelectorAll('#pcVisionListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-vision') === vision ? 'true' : 'false');
    });
  }

  function navigateToLang(code) {
    var target = LANG_FOLDER_MAP[code] || '/';
    // Cross-document View Transitions (enabled sitewide via the
    // `@view-transition { navigation: auto; }` at-rule in <head>) animate
    // this navigation automatically in supporting browsers -- a plain
    // same-origin navigation is all that's needed to trigger it.
    window.location.href = target;
  }

  // Apply stored/default preferences immediately (before DOMContentLoaded)
  // so both CSS variables are correct before first paint -- avoids a flash
  // of the defaults on a return visitor with different saved values. The
  // `data-vision` attribute is likewise set here, before first paint, so
  // there's no flash of un-adjusted colors either.
  (function initFromStoredPreferencesEarly() {
    var prefs = getPreferences();
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (prefs.opacity / 100).toFixed(2));
    document.documentElement.style.setProperty('--pc-widget-opacity', (prefs.widgetOpacity / 100).toFixed(2));
    document.documentElement.setAttribute('data-vision', prefs.colorVision);
  })();

  document.addEventListener('DOMContentLoaded', function () {
    var trigger = document.getElementById('pcSettingsTrigger');
    var panel = document.getElementById('pcSettingsPanel');
    var closeBtn = document.getElementById('pcSettingsClose');
    var langTrigger = document.getElementById('pcLangTrigger');
    var langListbox = document.getElementById('pcLangListbox');
    var opacitySlider = document.getElementById('pcOpacitySlider');
    var widgetOpacitySlider = document.getElementById('pcWidgetOpacitySlider');
    var visionTrigger = document.getElementById('pcVisionTrigger');
    var visionListbox = document.getElementById('pcVisionListbox');

    if (!trigger || !panel) return;

    var prefs = getPreferences();
    applyBackgroundOpacity(prefs.opacity);
    applyWidgetOpacity(prefs.widgetOpacity);
    applyLangSelection(prefs.lang);
    applyVisionSelection(prefs.colorVision);

    function openPanel() {
      panel.hidden = false;
      trigger.setAttribute('aria-expanded', 'true');
    }
    function closeAllDropdowns() {
      if (langListbox && !langListbox.hidden) {
        langListbox.hidden = true;
        langTrigger.setAttribute('aria-expanded', 'false');
      }
      if (visionListbox && !visionListbox.hidden) {
        visionListbox.hidden = true;
        visionTrigger.setAttribute('aria-expanded', 'false');
      }
    }
    function closePanel() {
      panel.hidden = true;
      trigger.setAttribute('aria-expanded', 'false');
      closeAllDropdowns();
    }

    trigger.addEventListener('click', function () {
      panel.hidden ? openPanel() : closePanel();
    });
    if (closeBtn) closeBtn.addEventListener('click', closePanel);

    document.addEventListener('click', function (e) {
      if (!e.target.closest('#pcSettingsWidget')) closePanel();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closePanel();
    });

    // Language dropdown: open/close + selection.
    if (langTrigger && langListbox) {
      langTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !langListbox.hidden;
        langListbox.hidden = isOpen;
        langTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      langListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var code = opt.getAttribute('data-lang');
        setPreferences({ lang: code });
        applyLangSelection(code);
        closeAllDropdowns();
        navigateToLang(code);
      });
      // Keyboard support: ArrowUp/ArrowDown move focus between options,
      // Enter/Space activates the focused option.
      langListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(langListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          langTrigger.focus();
        }
      });
    }

    // Color Vision dropdown: same open/close/selection/keyboard pattern as
    // the language dropdown above -- unlike language, selecting an option
    // here does NOT navigate anywhere, it just applies immediately.
    if (visionTrigger && visionListbox) {
      visionTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !visionListbox.hidden;
        visionListbox.hidden = isOpen;
        visionTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      visionListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var vision = opt.getAttribute('data-vision');
        setPreferences({ colorVision: vision });
        applyVisionSelection(vision);
        closeAllDropdowns();
      });
      visionListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(visionListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          visionTrigger.focus();
        }
      });
    }

    // Both opacity sliders -- native <input type="range"> already provides
    // full keyboard support (Arrow keys, Home/End, PageUp/PageDown); role
    // and aria-value* attributes are set explicitly per this task's spec.
    if (opacitySlider) {
      opacitySlider.addEventListener('input', function () {
        var value = Number(opacitySlider.value);
        applyBackgroundOpacity(value);
        setPreferences({ opacity: value });
      });
    }
    if (widgetOpacitySlider) {
      widgetOpacitySlider.addEventListener('input', function () {
        var value = Number(widgetOpacitySlider.value);
        applyWidgetOpacity(value);
        setPreferences({ widgetOpacity: value });
      });
    }

    // Native `storage` event -- fires in OTHER tabs/windows on this origin
    // when `preferences` changes there, and vice versa. This is the
    // cross-tab sync mechanism this task requires; no polling needed.
    window.addEventListener('storage', function (e) {
      if (e.key !== PREFS_KEY) return;
      var updated = getPreferences();
      applyBackgroundOpacity(updated.opacity);
      applyWidgetOpacity(updated.widgetOpacity);
      applyLangSelection(updated.lang);
      applyVisionSelection(updated.colorVision);
    });
  });
})();
// TASK-9 END
</script>
</body>
```
<!-- TASK-10 END -->

### `small-mammals.html`  →  live path `pets/small-mammals/index.html`

<!-- TASK-10 START -->
**Change 1 of 2 — View Transitions style block, before `</head>`**

**Old code:**
```html
  </head>
```
**New code:**
```html
<!-- TASK-9 START: cross-document View Transitions API, enabled sitewide.
     Animates same-origin full-page navigations (e.g. the Settings Widget's
     language switcher navigating between /, /es/, /pt/, /fr/, /ar/, /zh/)
     in browsers that support the cross-document View Transitions API
     (Chromium 126+). Browsers without support ignore this at-rule and
     navigate normally with no error -- this is a progressive enhancement,
     not a dependency. -->
<style>
@view-transition {
  navigation: auto;
}
</style>
<!-- TASK-9 END -->
  </head>
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Change 2 of 2 — Settings Widget (style + markup + script), before `</body>`**

**Old code:**
```html
</body>
```
**New code:**
```html
<!-- TASK-9 START: Settings Widget -- gear-icon trigger + panel housing the
     sitewide language switcher and the personal widget-background-opacity
     slider. Defines the shared `preferences` object (localStorage key
     "preferences", fields {lang, opacity}) that this page's own
     lang-redirect.js / i18n-core.js (Task 1) already read `preferences.lang`
     from, and that Admin-Project #4's `preferred_chat_language` field also
     reads independently -- the exact field name/shape/values documented in
     this task must not change without notifying that project first.
     Z-INDEX NOTE: the site's real, documented highest z-index anywhere is
     6001 (the filter/form bottom-sheet system), not the floating contact
     button's 4000 -- this widget's trigger/panel/dropdown are placed at
     6100/6200/6300 so they always sit above every existing floating
     element, including both of those. -->
<style>
/* Renamed from --pc-widget-opacity (added in this task's first pass) to
   --pc-widget-bg-opacity, and a second, distinct --pc-widget-opacity
   variable added for the whole widget's own opacity -- per user follow-up,
   "Widget Background Opacity" (panel's rgba background only, children
   stay fully legible) and "Widget Opacity" (the entire trigger + panel,
   via real CSS `opacity` on the container) are two separate controls. */
:root { --pc-widget-bg-opacity: 0.90; --pc-widget-opacity: 1; }

/* "Central position" = vertically centered on the viewport's right edge.
   User follow-up moved this from the left edge (this task's original
   build) to the right edge -- everything else about the placement logic
   from that first pass still applies: vertically centered avoids the
   hero's own CTA buttons (the collision found and fixed when this was
   briefly bottom-center), and vertical centering keeps clear of the
   bottom-right floating contact button (z-index 4000), re-confirmed by
   Playwright screenshot below since moving to the same edge as that
   button is exactly the kind of collision worth re-checking, not
   assuming safe by reasoning alone. */
.pc-settings-widget {
  position: fixed;
  top: 50%;
  right: 16px;
  transform: translateY(-50%);
  z-index: 6100;
  font-family: 'Sora', sans-serif;
  /* Whole-widget opacity (trigger + panel together), separate from the
     panel-only rgba background opacity below. Real CSS `opacity`, by
     design, fades everything inside -- that's the intended effect of this
     specific control, unlike the background one. */
  opacity: var(--pc-widget-opacity);
  transition: opacity 0.2s ease;
}

.pc-settings-trigger {
  width: 52px; height: 52px; border-radius: 50%; border: none; cursor: pointer;
  background: #10214b; color: #d7bd88;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 16px rgba(16,33,75,0.35);
  transition: transform 0.3s cubic-bezier(.4,0,.2,1), background 0.3s cubic-bezier(.4,0,.2,1);
}
.pc-settings-trigger:hover { transform: rotate(35deg); background: #1a2f5c; }
.pc-settings-trigger[aria-expanded="true"] { background: #ED7624; color: #fff; }

.pc-settings-panel {
  position: absolute;
  top: 50%;
  /* Opens to the LEFT of the trigger now that the trigger sits on the
     right edge (was `left: calc(100% + 12px)` opening rightward off a
     left-edge trigger). */
  right: calc(100% + 12px);
  transform: translateY(-50%);
  width: 280px;
  max-width: calc(100vw - 32px);
  /* rgba() background driven by the Widget Background Opacity slider --
     never container `opacity`, which would also fade the panel's
     text/children (Task 9's explicit requirement: "rgba() backgrounds,
     not container opacity"). The separate whole-widget opacity control
     lives on .pc-settings-widget above. */
  background: rgba(16, 33, 75, var(--pc-widget-bg-opacity));
  color: #f2f2f2;
  border-radius: 14px;
  padding: 16px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.3);
  z-index: 6200;
}
.pc-settings-panel[hidden] { display: none; }

.pc-settings-panel__header {
  display: flex; align-items: center; justify-content: space-between;
  font-weight: 700; margin-bottom: 12px; font-size: 15px;
}
.pc-settings-panel__close {
  background: none; border: none; color: inherit; font-size: 20px; line-height: 1;
  cursor: pointer; padding: 2px 6px;
}

.pc-settings-row { margin-bottom: 14px; }
.pc-settings-row:last-child { margin-bottom: 0; }
.pc-settings-label { display: block; font-size: 12.5px; margin-bottom: 6px; color: #d7bd88; }
/* One-line description under a consequential setting, per Design System
   Section 19's "Settings Widget Control Pattern -- Toggle + Description"
   rule (pair every consequential control with a plain-language line
   explaining its effect, since a user shouldn't have to guess). */
.pc-settings-desc { font-size: 11.5px; color: #a39b91; margin: -2px 0 8px; line-height: 1.4; }

.pc-lang-dropdown { position: relative; }
.pc-lang-dropdown__trigger {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.16);
  color: #f2f2f2; border-radius: 8px; padding: 8px 12px; font-size: 13.5px; cursor: pointer;
  font-family: 'Sora', sans-serif;
}
.pc-lang-dropdown__panel {
  position: absolute; top: calc(100% + 6px); left: 0; right: 0;
  background: #10214b; border: 1px solid rgba(255,255,255,0.14);
  /* Sharp corner nearest the trigger (top-left here, since the trigger
     sits above this downward-opening panel) -- the "message bubble" corner
     rule from PETCENTER_DESIGN_SYSTEM_v1_0.md Section 24 (v1.25). */
  border-radius: 0 8px 8px 8px;
  list-style: none; margin: 0; padding: 4px; max-height: 200px; overflow-y: auto;
  z-index: 6300;
}
.pc-lang-dropdown__panel[hidden] { display: none; }
.pc-lang-dropdown__panel li { padding: 8px 10px; font-size: 13.5px; border-radius: 6px; cursor: pointer; }
.pc-lang-dropdown__panel li:hover,
.pc-lang-dropdown__panel li[aria-selected="true"] { background: rgba(215,189,136,0.18); }

.pc-opacity-slider { width: 100%; accent-color: #d7bd88; }
.pc-opacity-value { font-size: 12px; color: #a39b91; float: right; margin-top: 2px; }

@media (max-width: 600px) {
  /* Below 600px, opening the panel to the right of a left-edge trigger
     would push a 280px-wide panel off the right edge of the viewport on
     most phones. Anchor the panel to open downward from the trigger
     instead, still clear of both the hero content and the bottom-right
     contact button. Anchored via `right: 0` (not `left: 0`) since the
     trigger itself now sits near the right edge -- `left: 0` would push
     a near-full-width panel further right and off-screen. */
  .pc-settings-panel {
    top: calc(100% + 10px);
    right: 0;
    left: auto;
    transform: none;
    width: calc(100vw - 48px);
  }
}
</style>

<div class="pc-settings-widget" id="pcSettingsWidget">
  <button type="button" class="pc-settings-trigger" id="pcSettingsTrigger"
          aria-haspopup="true" aria-expanded="false" aria-controls="pcSettingsPanel"
          aria-label="Open site settings">
    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <circle cx="12" cy="12" r="3"></circle>
      <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
    </svg>
  </button>

  <div class="pc-settings-panel" id="pcSettingsPanel" role="dialog" aria-modal="false"
       aria-label="Site settings" hidden="">
    <div class="pc-settings-panel__header">
      <span data-i18n="common.settingsWidget.1">Settings</span>
      <button type="button" class="pc-settings-panel__close" id="pcSettingsClose"
              aria-label="Close settings">&times;</button>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcLangLabel" data-i18n="common.settingsWidget.2">Language</label>
      <div class="pc-lang-dropdown" id="pcLangDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcLangTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcLangLabel"
                aria-controls="pcLangListbox">
          <span id="pcLangCurrentLabel">English</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcLangListbox" role="listbox"
            aria-labelledby="pcLangLabel" tabindex="-1" hidden="">
          <li role="option" data-lang="en" aria-selected="true" tabindex="0">English</li>
          <li role="option" data-lang="es" aria-selected="false" tabindex="0">Español</li>
          <li role="option" data-lang="pt" aria-selected="false" tabindex="0">Português</li>
          <li role="option" data-lang="fr" aria-selected="false" tabindex="0">Français</li>
          <li role="option" data-lang="ar" aria-selected="false" tabindex="0">العربية</li>
          <li role="option" data-lang="zh-Hans" aria-selected="false" tabindex="0">简体中文</li>
        </ul>
      </div>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcOpacitySlider" id="pcOpacityLabel" data-i18n="common.settingsWidget.3">Widget Background Opacity</label>
      <input type="range" id="pcOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="90"
             role="slider" aria-labelledby="pcOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="90" aria-valuetext="90%">
      <span class="pc-opacity-value" id="pcOpacityValue" aria-hidden="true">90%</span>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcWidgetOpacitySlider" id="pcWidgetOpacityLabel" data-i18n="common.settingsWidget.4">Widget Opacity</label>
      <input type="range" id="pcWidgetOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="100"
             role="slider" aria-labelledby="pcWidgetOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="100" aria-valuetext="100%">
      <span class="pc-opacity-value" id="pcWidgetOpacityValue" aria-hidden="true">100%</span>
    </div>

    <!-- TASK (Color Vision Accessibility) START: brief calls for this
         control to sit "alongside the existing dark mode toggle" -- no dark
         mode toggle exists anywhere in this codebase yet (Design System
         Section 19 documents its intended look/behavior but confirms it
         is not yet built; searched before assuming otherwise). Not adding
         a placeholder/stub toggle for a feature that hasn't been
         specified as buildable here -- flagging the gap in this task's
         completion file instead, per "flag it rather than inventing." -->
    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcVisionLabel" data-i18n="common.settingsWidget.5">Color Vision</label>
      <p class="pc-settings-desc" id="pcVisionDesc" data-i18n="common.settingsWidget.6">Adjusts status colors, alerts, and charts sitewide for your selected vision type.</p>
      <div class="pc-lang-dropdown" id="pcVisionDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcVisionTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcVisionLabel"
                aria-controls="pcVisionListbox">
          <span id="pcVisionCurrentLabel">Normal Vision</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcVisionListbox" role="listbox"
            aria-labelledby="pcVisionLabel" tabindex="-1" hidden="">
          <li role="option" data-vision="normal" aria-selected="true" tabindex="0">Normal Vision</li>
          <li role="option" data-vision="deuteranopia" aria-selected="false" tabindex="0">Deuteranopia</li>
          <li role="option" data-vision="protanopia" aria-selected="false" tabindex="0">Protanopia</li>
          <li role="option" data-vision="tritanopia" aria-selected="false" tabindex="0">Tritanopia</li>
        </ul>
      </div>
    </div>
    <!-- TASK (Color Vision Accessibility) END -->
  </div>
</div>

<script>
// TASK-9 START: Settings Widget logic + `preferences` schema owner.
(function () {
  'use strict';

  var PREFS_KEY = 'preferences';
  // Written by admin-dashboard.html's "Default Widget Background Opacity"
  // and "Default Widget Opacity" controls (this task's admin-side
  // deliverable). Read here only as a fallback default for a visitor with
  // no personal preference saved yet -- a visitor's own saved choice in
  // `preferences.opacity` / `preferences.widgetOpacity` always wins.
  var ADMIN_DEFAULTS_KEY = 'pc_admin_settings_defaults';

  // LANG_FOLDER_MAP mirrors Task 1's /en/-/zh/ folder scaffolding -- English
  // is the un-prefixed root (never a redirect target), the rest are their
  // own language-code folders. 'zh-Hans' (tagged, not bare 'zh') is the
  // CEO-confirmed value for Chinese everywhere this schema is used.
  var LANG_FOLDER_MAP = { en: '/', es: '/es/', pt: '/pt/', fr: '/fr/', ar: '/ar/', 'zh-Hans': '/zh/' };
  var LANG_LABELS = { en: 'English', es: 'Español', pt: 'Português', fr: 'Français', ar: 'العربية', 'zh-Hans': '简体中文' };
  var VISION_LABELS = { normal: 'Normal Vision', deuteranopia: 'Deuteranopia', protanopia: 'Protanopia', tritanopia: 'Tritanopia' };

  function readAdminDefaultOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 90;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.opacity);
      return (val >= 20 && val <= 100) ? val : 90;
    } catch (e) { return 90; }
  }

  function readAdminDefaultWidgetOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 100;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.widgetOpacity);
      return (val >= 20 && val <= 100) ? val : 100;
    } catch (e) { return 100; }
  }

  function getPreferences() {
    try {
      var raw = localStorage.getItem(PREFS_KEY);
      var parsed = raw ? JSON.parse(raw) : {};
      return {
        lang: (typeof parsed.lang === 'string' && parsed.lang) ? parsed.lang : 'en',
        opacity: (typeof parsed.opacity === 'number') ? parsed.opacity : readAdminDefaultOpacity(),
        widgetOpacity: (typeof parsed.widgetOpacity === 'number') ? parsed.widgetOpacity : readAdminDefaultWidgetOpacity(),
        colorVision: (typeof parsed.colorVision === 'string' && VISION_LABELS[parsed.colorVision]) ? parsed.colorVision : 'normal'
      };
    } catch (e) {
      return { lang: 'en', opacity: readAdminDefaultOpacity(), widgetOpacity: readAdminDefaultWidgetOpacity(), colorVision: 'normal' };
    }
  }

  function setPreferences(partial) {
    var current = getPreferences();
    var next = { lang: current.lang, opacity: current.opacity, widgetOpacity: current.widgetOpacity, colorVision: current.colorVision };
    for (var k in partial) { if (Object.prototype.hasOwnProperty.call(partial, k)) next[k] = partial[k]; }
    try { localStorage.setItem(PREFS_KEY, JSON.stringify(next)); } catch (e) { /* storage unavailable -- fail silently, in-memory state still applies for this tab */ }
    return next;
  }

  // Panel-only rgba() background opacity -- children stay fully legible
  // regardless of this value.
  function applyBackgroundOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcOpacitySlider');
    var valueEl = document.getElementById('pcOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  // Whole-widget opacity -- real CSS `opacity` on .pc-settings-widget,
  // fades the trigger button and everything inside the panel together.
  function applyWidgetOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcWidgetOpacitySlider');
    var valueEl = document.getElementById('pcWidgetOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  function applyLangSelection(code) {
    var currentLabelEl = document.getElementById('pcLangCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = LANG_LABELS[code] || 'English';
    var options = document.querySelectorAll('#pcLangListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-lang') === code ? 'true' : 'false');
    });
  }

  // Sets the `data-vision` attribute on <html>, which every
  // [data-vision="..."] CSS override block above (and in admin-dashboard.html)
  // keys off of. "normal" matches no override rule, so it's a no-op that
  // simply leaves the default colors in place -- no separate "clear" branch
  // needed.
  function applyVisionSelection(vision) {
    document.documentElement.setAttribute('data-vision', vision);
    var currentLabelEl = document.getElementById('pcVisionCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = VISION_LABELS[vision] || 'Normal Vision';
    var options = document.querySelectorAll('#pcVisionListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-vision') === vision ? 'true' : 'false');
    });
  }

  function navigateToLang(code) {
    var target = LANG_FOLDER_MAP[code] || '/';
    // Cross-document View Transitions (enabled sitewide via the
    // `@view-transition { navigation: auto; }` at-rule in <head>) animate
    // this navigation automatically in supporting browsers -- a plain
    // same-origin navigation is all that's needed to trigger it.
    window.location.href = target;
  }

  // Apply stored/default preferences immediately (before DOMContentLoaded)
  // so both CSS variables are correct before first paint -- avoids a flash
  // of the defaults on a return visitor with different saved values. The
  // `data-vision` attribute is likewise set here, before first paint, so
  // there's no flash of un-adjusted colors either.
  (function initFromStoredPreferencesEarly() {
    var prefs = getPreferences();
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (prefs.opacity / 100).toFixed(2));
    document.documentElement.style.setProperty('--pc-widget-opacity', (prefs.widgetOpacity / 100).toFixed(2));
    document.documentElement.setAttribute('data-vision', prefs.colorVision);
  })();

  document.addEventListener('DOMContentLoaded', function () {
    var trigger = document.getElementById('pcSettingsTrigger');
    var panel = document.getElementById('pcSettingsPanel');
    var closeBtn = document.getElementById('pcSettingsClose');
    var langTrigger = document.getElementById('pcLangTrigger');
    var langListbox = document.getElementById('pcLangListbox');
    var opacitySlider = document.getElementById('pcOpacitySlider');
    var widgetOpacitySlider = document.getElementById('pcWidgetOpacitySlider');
    var visionTrigger = document.getElementById('pcVisionTrigger');
    var visionListbox = document.getElementById('pcVisionListbox');

    if (!trigger || !panel) return;

    var prefs = getPreferences();
    applyBackgroundOpacity(prefs.opacity);
    applyWidgetOpacity(prefs.widgetOpacity);
    applyLangSelection(prefs.lang);
    applyVisionSelection(prefs.colorVision);

    function openPanel() {
      panel.hidden = false;
      trigger.setAttribute('aria-expanded', 'true');
    }
    function closeAllDropdowns() {
      if (langListbox && !langListbox.hidden) {
        langListbox.hidden = true;
        langTrigger.setAttribute('aria-expanded', 'false');
      }
      if (visionListbox && !visionListbox.hidden) {
        visionListbox.hidden = true;
        visionTrigger.setAttribute('aria-expanded', 'false');
      }
    }
    function closePanel() {
      panel.hidden = true;
      trigger.setAttribute('aria-expanded', 'false');
      closeAllDropdowns();
    }

    trigger.addEventListener('click', function () {
      panel.hidden ? openPanel() : closePanel();
    });
    if (closeBtn) closeBtn.addEventListener('click', closePanel);

    document.addEventListener('click', function (e) {
      if (!e.target.closest('#pcSettingsWidget')) closePanel();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closePanel();
    });

    // Language dropdown: open/close + selection.
    if (langTrigger && langListbox) {
      langTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !langListbox.hidden;
        langListbox.hidden = isOpen;
        langTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      langListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var code = opt.getAttribute('data-lang');
        setPreferences({ lang: code });
        applyLangSelection(code);
        closeAllDropdowns();
        navigateToLang(code);
      });
      // Keyboard support: ArrowUp/ArrowDown move focus between options,
      // Enter/Space activates the focused option.
      langListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(langListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          langTrigger.focus();
        }
      });
    }

    // Color Vision dropdown: same open/close/selection/keyboard pattern as
    // the language dropdown above -- unlike language, selecting an option
    // here does NOT navigate anywhere, it just applies immediately.
    if (visionTrigger && visionListbox) {
      visionTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !visionListbox.hidden;
        visionListbox.hidden = isOpen;
        visionTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      visionListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var vision = opt.getAttribute('data-vision');
        setPreferences({ colorVision: vision });
        applyVisionSelection(vision);
        closeAllDropdowns();
      });
      visionListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(visionListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          visionTrigger.focus();
        }
      });
    }

    // Both opacity sliders -- native <input type="range"> already provides
    // full keyboard support (Arrow keys, Home/End, PageUp/PageDown); role
    // and aria-value* attributes are set explicitly per this task's spec.
    if (opacitySlider) {
      opacitySlider.addEventListener('input', function () {
        var value = Number(opacitySlider.value);
        applyBackgroundOpacity(value);
        setPreferences({ opacity: value });
      });
    }
    if (widgetOpacitySlider) {
      widgetOpacitySlider.addEventListener('input', function () {
        var value = Number(widgetOpacitySlider.value);
        applyWidgetOpacity(value);
        setPreferences({ widgetOpacity: value });
      });
    }

    // Native `storage` event -- fires in OTHER tabs/windows on this origin
    // when `preferences` changes there, and vice versa. This is the
    // cross-tab sync mechanism this task requires; no polling needed.
    window.addEventListener('storage', function (e) {
      if (e.key !== PREFS_KEY) return;
      var updated = getPreferences();
      applyBackgroundOpacity(updated.opacity);
      applyWidgetOpacity(updated.widgetOpacity);
      applyLangSelection(updated.lang);
      applyVisionSelection(updated.colorVision);
    });
  });
})();
// TASK-9 END
</script>
</body>
```
<!-- TASK-10 END -->

### `livestocks.html`  →  live path `pets/livestocks/index.html`

<!-- TASK-10 START -->
**Change 1 of 2 — View Transitions style block, before `</head>`**

**Old code:**
```html
  </head>
```
**New code:**
```html
<!-- TASK-9 START: cross-document View Transitions API, enabled sitewide.
     Animates same-origin full-page navigations (e.g. the Settings Widget's
     language switcher navigating between /, /es/, /pt/, /fr/, /ar/, /zh/)
     in browsers that support the cross-document View Transitions API
     (Chromium 126+). Browsers without support ignore this at-rule and
     navigate normally with no error -- this is a progressive enhancement,
     not a dependency. -->
<style>
@view-transition {
  navigation: auto;
}
</style>
<!-- TASK-9 END -->
  </head>
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Change 2 of 2 — Settings Widget (style + markup + script), before `</body>`**

**Old code:**
```html
</body>
```
**New code:**
```html
<!-- TASK-9 START: Settings Widget -- gear-icon trigger + panel housing the
     sitewide language switcher and the personal widget-background-opacity
     slider. Defines the shared `preferences` object (localStorage key
     "preferences", fields {lang, opacity}) that this page's own
     lang-redirect.js / i18n-core.js (Task 1) already read `preferences.lang`
     from, and that Admin-Project #4's `preferred_chat_language` field also
     reads independently -- the exact field name/shape/values documented in
     this task must not change without notifying that project first.
     Z-INDEX NOTE: the site's real, documented highest z-index anywhere is
     6001 (the filter/form bottom-sheet system), not the floating contact
     button's 4000 -- this widget's trigger/panel/dropdown are placed at
     6100/6200/6300 so they always sit above every existing floating
     element, including both of those. -->
<style>
/* Renamed from --pc-widget-opacity (added in this task's first pass) to
   --pc-widget-bg-opacity, and a second, distinct --pc-widget-opacity
   variable added for the whole widget's own opacity -- per user follow-up,
   "Widget Background Opacity" (panel's rgba background only, children
   stay fully legible) and "Widget Opacity" (the entire trigger + panel,
   via real CSS `opacity` on the container) are two separate controls. */
:root { --pc-widget-bg-opacity: 0.90; --pc-widget-opacity: 1; }

/* "Central position" = vertically centered on the viewport's right edge.
   User follow-up moved this from the left edge (this task's original
   build) to the right edge -- everything else about the placement logic
   from that first pass still applies: vertically centered avoids the
   hero's own CTA buttons (the collision found and fixed when this was
   briefly bottom-center), and vertical centering keeps clear of the
   bottom-right floating contact button (z-index 4000), re-confirmed by
   Playwright screenshot below since moving to the same edge as that
   button is exactly the kind of collision worth re-checking, not
   assuming safe by reasoning alone. */
.pc-settings-widget {
  position: fixed;
  top: 50%;
  right: 16px;
  transform: translateY(-50%);
  z-index: 6100;
  font-family: 'Sora', sans-serif;
  /* Whole-widget opacity (trigger + panel together), separate from the
     panel-only rgba background opacity below. Real CSS `opacity`, by
     design, fades everything inside -- that's the intended effect of this
     specific control, unlike the background one. */
  opacity: var(--pc-widget-opacity);
  transition: opacity 0.2s ease;
}

.pc-settings-trigger {
  width: 52px; height: 52px; border-radius: 50%; border: none; cursor: pointer;
  background: #10214b; color: #d7bd88;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 16px rgba(16,33,75,0.35);
  transition: transform 0.3s cubic-bezier(.4,0,.2,1), background 0.3s cubic-bezier(.4,0,.2,1);
}
.pc-settings-trigger:hover { transform: rotate(35deg); background: #1a2f5c; }
.pc-settings-trigger[aria-expanded="true"] { background: #ED7624; color: #fff; }

.pc-settings-panel {
  position: absolute;
  top: 50%;
  /* Opens to the LEFT of the trigger now that the trigger sits on the
     right edge (was `left: calc(100% + 12px)` opening rightward off a
     left-edge trigger). */
  right: calc(100% + 12px);
  transform: translateY(-50%);
  width: 280px;
  max-width: calc(100vw - 32px);
  /* rgba() background driven by the Widget Background Opacity slider --
     never container `opacity`, which would also fade the panel's
     text/children (Task 9's explicit requirement: "rgba() backgrounds,
     not container opacity"). The separate whole-widget opacity control
     lives on .pc-settings-widget above. */
  background: rgba(16, 33, 75, var(--pc-widget-bg-opacity));
  color: #f2f2f2;
  border-radius: 14px;
  padding: 16px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.3);
  z-index: 6200;
}
.pc-settings-panel[hidden] { display: none; }

.pc-settings-panel__header {
  display: flex; align-items: center; justify-content: space-between;
  font-weight: 700; margin-bottom: 12px; font-size: 15px;
}
.pc-settings-panel__close {
  background: none; border: none; color: inherit; font-size: 20px; line-height: 1;
  cursor: pointer; padding: 2px 6px;
}

.pc-settings-row { margin-bottom: 14px; }
.pc-settings-row:last-child { margin-bottom: 0; }
.pc-settings-label { display: block; font-size: 12.5px; margin-bottom: 6px; color: #d7bd88; }
/* One-line description under a consequential setting, per Design System
   Section 19's "Settings Widget Control Pattern -- Toggle + Description"
   rule (pair every consequential control with a plain-language line
   explaining its effect, since a user shouldn't have to guess). */
.pc-settings-desc { font-size: 11.5px; color: #a39b91; margin: -2px 0 8px; line-height: 1.4; }

.pc-lang-dropdown { position: relative; }
.pc-lang-dropdown__trigger {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.16);
  color: #f2f2f2; border-radius: 8px; padding: 8px 12px; font-size: 13.5px; cursor: pointer;
  font-family: 'Sora', sans-serif;
}
.pc-lang-dropdown__panel {
  position: absolute; top: calc(100% + 6px); left: 0; right: 0;
  background: #10214b; border: 1px solid rgba(255,255,255,0.14);
  /* Sharp corner nearest the trigger (top-left here, since the trigger
     sits above this downward-opening panel) -- the "message bubble" corner
     rule from PETCENTER_DESIGN_SYSTEM_v1_0.md Section 24 (v1.25). */
  border-radius: 0 8px 8px 8px;
  list-style: none; margin: 0; padding: 4px; max-height: 200px; overflow-y: auto;
  z-index: 6300;
}
.pc-lang-dropdown__panel[hidden] { display: none; }
.pc-lang-dropdown__panel li { padding: 8px 10px; font-size: 13.5px; border-radius: 6px; cursor: pointer; }
.pc-lang-dropdown__panel li:hover,
.pc-lang-dropdown__panel li[aria-selected="true"] { background: rgba(215,189,136,0.18); }

.pc-opacity-slider { width: 100%; accent-color: #d7bd88; }
.pc-opacity-value { font-size: 12px; color: #a39b91; float: right; margin-top: 2px; }

@media (max-width: 600px) {
  /* Below 600px, opening the panel to the right of a left-edge trigger
     would push a 280px-wide panel off the right edge of the viewport on
     most phones. Anchor the panel to open downward from the trigger
     instead, still clear of both the hero content and the bottom-right
     contact button. Anchored via `right: 0` (not `left: 0`) since the
     trigger itself now sits near the right edge -- `left: 0` would push
     a near-full-width panel further right and off-screen. */
  .pc-settings-panel {
    top: calc(100% + 10px);
    right: 0;
    left: auto;
    transform: none;
    width: calc(100vw - 48px);
  }
}
</style>

<div class="pc-settings-widget" id="pcSettingsWidget">
  <button type="button" class="pc-settings-trigger" id="pcSettingsTrigger"
          aria-haspopup="true" aria-expanded="false" aria-controls="pcSettingsPanel"
          aria-label="Open site settings">
    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <circle cx="12" cy="12" r="3"></circle>
      <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
    </svg>
  </button>

  <div class="pc-settings-panel" id="pcSettingsPanel" role="dialog" aria-modal="false"
       aria-label="Site settings" hidden="">
    <div class="pc-settings-panel__header">
      <span data-i18n="common.settingsWidget.1">Settings</span>
      <button type="button" class="pc-settings-panel__close" id="pcSettingsClose"
              aria-label="Close settings">&times;</button>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcLangLabel" data-i18n="common.settingsWidget.2">Language</label>
      <div class="pc-lang-dropdown" id="pcLangDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcLangTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcLangLabel"
                aria-controls="pcLangListbox">
          <span id="pcLangCurrentLabel">English</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcLangListbox" role="listbox"
            aria-labelledby="pcLangLabel" tabindex="-1" hidden="">
          <li role="option" data-lang="en" aria-selected="true" tabindex="0">English</li>
          <li role="option" data-lang="es" aria-selected="false" tabindex="0">Español</li>
          <li role="option" data-lang="pt" aria-selected="false" tabindex="0">Português</li>
          <li role="option" data-lang="fr" aria-selected="false" tabindex="0">Français</li>
          <li role="option" data-lang="ar" aria-selected="false" tabindex="0">العربية</li>
          <li role="option" data-lang="zh-Hans" aria-selected="false" tabindex="0">简体中文</li>
        </ul>
      </div>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcOpacitySlider" id="pcOpacityLabel" data-i18n="common.settingsWidget.3">Widget Background Opacity</label>
      <input type="range" id="pcOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="90"
             role="slider" aria-labelledby="pcOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="90" aria-valuetext="90%">
      <span class="pc-opacity-value" id="pcOpacityValue" aria-hidden="true">90%</span>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcWidgetOpacitySlider" id="pcWidgetOpacityLabel" data-i18n="common.settingsWidget.4">Widget Opacity</label>
      <input type="range" id="pcWidgetOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="100"
             role="slider" aria-labelledby="pcWidgetOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="100" aria-valuetext="100%">
      <span class="pc-opacity-value" id="pcWidgetOpacityValue" aria-hidden="true">100%</span>
    </div>

    <!-- TASK (Color Vision Accessibility) START: brief calls for this
         control to sit "alongside the existing dark mode toggle" -- no dark
         mode toggle exists anywhere in this codebase yet (Design System
         Section 19 documents its intended look/behavior but confirms it
         is not yet built; searched before assuming otherwise). Not adding
         a placeholder/stub toggle for a feature that hasn't been
         specified as buildable here -- flagging the gap in this task's
         completion file instead, per "flag it rather than inventing." -->
    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcVisionLabel" data-i18n="common.settingsWidget.5">Color Vision</label>
      <p class="pc-settings-desc" id="pcVisionDesc" data-i18n="common.settingsWidget.6">Adjusts status colors, alerts, and charts sitewide for your selected vision type.</p>
      <div class="pc-lang-dropdown" id="pcVisionDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcVisionTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcVisionLabel"
                aria-controls="pcVisionListbox">
          <span id="pcVisionCurrentLabel">Normal Vision</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcVisionListbox" role="listbox"
            aria-labelledby="pcVisionLabel" tabindex="-1" hidden="">
          <li role="option" data-vision="normal" aria-selected="true" tabindex="0">Normal Vision</li>
          <li role="option" data-vision="deuteranopia" aria-selected="false" tabindex="0">Deuteranopia</li>
          <li role="option" data-vision="protanopia" aria-selected="false" tabindex="0">Protanopia</li>
          <li role="option" data-vision="tritanopia" aria-selected="false" tabindex="0">Tritanopia</li>
        </ul>
      </div>
    </div>
    <!-- TASK (Color Vision Accessibility) END -->
  </div>
</div>

<script>
// TASK-9 START: Settings Widget logic + `preferences` schema owner.
(function () {
  'use strict';

  var PREFS_KEY = 'preferences';
  // Written by admin-dashboard.html's "Default Widget Background Opacity"
  // and "Default Widget Opacity" controls (this task's admin-side
  // deliverable). Read here only as a fallback default for a visitor with
  // no personal preference saved yet -- a visitor's own saved choice in
  // `preferences.opacity` / `preferences.widgetOpacity` always wins.
  var ADMIN_DEFAULTS_KEY = 'pc_admin_settings_defaults';

  // LANG_FOLDER_MAP mirrors Task 1's /en/-/zh/ folder scaffolding -- English
  // is the un-prefixed root (never a redirect target), the rest are their
  // own language-code folders. 'zh-Hans' (tagged, not bare 'zh') is the
  // CEO-confirmed value for Chinese everywhere this schema is used.
  var LANG_FOLDER_MAP = { en: '/', es: '/es/', pt: '/pt/', fr: '/fr/', ar: '/ar/', 'zh-Hans': '/zh/' };
  var LANG_LABELS = { en: 'English', es: 'Español', pt: 'Português', fr: 'Français', ar: 'العربية', 'zh-Hans': '简体中文' };
  var VISION_LABELS = { normal: 'Normal Vision', deuteranopia: 'Deuteranopia', protanopia: 'Protanopia', tritanopia: 'Tritanopia' };

  function readAdminDefaultOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 90;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.opacity);
      return (val >= 20 && val <= 100) ? val : 90;
    } catch (e) { return 90; }
  }

  function readAdminDefaultWidgetOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 100;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.widgetOpacity);
      return (val >= 20 && val <= 100) ? val : 100;
    } catch (e) { return 100; }
  }

  function getPreferences() {
    try {
      var raw = localStorage.getItem(PREFS_KEY);
      var parsed = raw ? JSON.parse(raw) : {};
      return {
        lang: (typeof parsed.lang === 'string' && parsed.lang) ? parsed.lang : 'en',
        opacity: (typeof parsed.opacity === 'number') ? parsed.opacity : readAdminDefaultOpacity(),
        widgetOpacity: (typeof parsed.widgetOpacity === 'number') ? parsed.widgetOpacity : readAdminDefaultWidgetOpacity(),
        colorVision: (typeof parsed.colorVision === 'string' && VISION_LABELS[parsed.colorVision]) ? parsed.colorVision : 'normal'
      };
    } catch (e) {
      return { lang: 'en', opacity: readAdminDefaultOpacity(), widgetOpacity: readAdminDefaultWidgetOpacity(), colorVision: 'normal' };
    }
  }

  function setPreferences(partial) {
    var current = getPreferences();
    var next = { lang: current.lang, opacity: current.opacity, widgetOpacity: current.widgetOpacity, colorVision: current.colorVision };
    for (var k in partial) { if (Object.prototype.hasOwnProperty.call(partial, k)) next[k] = partial[k]; }
    try { localStorage.setItem(PREFS_KEY, JSON.stringify(next)); } catch (e) { /* storage unavailable -- fail silently, in-memory state still applies for this tab */ }
    return next;
  }

  // Panel-only rgba() background opacity -- children stay fully legible
  // regardless of this value.
  function applyBackgroundOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcOpacitySlider');
    var valueEl = document.getElementById('pcOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  // Whole-widget opacity -- real CSS `opacity` on .pc-settings-widget,
  // fades the trigger button and everything inside the panel together.
  function applyWidgetOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcWidgetOpacitySlider');
    var valueEl = document.getElementById('pcWidgetOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  function applyLangSelection(code) {
    var currentLabelEl = document.getElementById('pcLangCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = LANG_LABELS[code] || 'English';
    var options = document.querySelectorAll('#pcLangListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-lang') === code ? 'true' : 'false');
    });
  }

  // Sets the `data-vision` attribute on <html>, which every
  // [data-vision="..."] CSS override block above (and in admin-dashboard.html)
  // keys off of. "normal" matches no override rule, so it's a no-op that
  // simply leaves the default colors in place -- no separate "clear" branch
  // needed.
  function applyVisionSelection(vision) {
    document.documentElement.setAttribute('data-vision', vision);
    var currentLabelEl = document.getElementById('pcVisionCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = VISION_LABELS[vision] || 'Normal Vision';
    var options = document.querySelectorAll('#pcVisionListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-vision') === vision ? 'true' : 'false');
    });
  }

  function navigateToLang(code) {
    var target = LANG_FOLDER_MAP[code] || '/';
    // Cross-document View Transitions (enabled sitewide via the
    // `@view-transition { navigation: auto; }` at-rule in <head>) animate
    // this navigation automatically in supporting browsers -- a plain
    // same-origin navigation is all that's needed to trigger it.
    window.location.href = target;
  }

  // Apply stored/default preferences immediately (before DOMContentLoaded)
  // so both CSS variables are correct before first paint -- avoids a flash
  // of the defaults on a return visitor with different saved values. The
  // `data-vision` attribute is likewise set here, before first paint, so
  // there's no flash of un-adjusted colors either.
  (function initFromStoredPreferencesEarly() {
    var prefs = getPreferences();
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (prefs.opacity / 100).toFixed(2));
    document.documentElement.style.setProperty('--pc-widget-opacity', (prefs.widgetOpacity / 100).toFixed(2));
    document.documentElement.setAttribute('data-vision', prefs.colorVision);
  })();

  document.addEventListener('DOMContentLoaded', function () {
    var trigger = document.getElementById('pcSettingsTrigger');
    var panel = document.getElementById('pcSettingsPanel');
    var closeBtn = document.getElementById('pcSettingsClose');
    var langTrigger = document.getElementById('pcLangTrigger');
    var langListbox = document.getElementById('pcLangListbox');
    var opacitySlider = document.getElementById('pcOpacitySlider');
    var widgetOpacitySlider = document.getElementById('pcWidgetOpacitySlider');
    var visionTrigger = document.getElementById('pcVisionTrigger');
    var visionListbox = document.getElementById('pcVisionListbox');

    if (!trigger || !panel) return;

    var prefs = getPreferences();
    applyBackgroundOpacity(prefs.opacity);
    applyWidgetOpacity(prefs.widgetOpacity);
    applyLangSelection(prefs.lang);
    applyVisionSelection(prefs.colorVision);

    function openPanel() {
      panel.hidden = false;
      trigger.setAttribute('aria-expanded', 'true');
    }
    function closeAllDropdowns() {
      if (langListbox && !langListbox.hidden) {
        langListbox.hidden = true;
        langTrigger.setAttribute('aria-expanded', 'false');
      }
      if (visionListbox && !visionListbox.hidden) {
        visionListbox.hidden = true;
        visionTrigger.setAttribute('aria-expanded', 'false');
      }
    }
    function closePanel() {
      panel.hidden = true;
      trigger.setAttribute('aria-expanded', 'false');
      closeAllDropdowns();
    }

    trigger.addEventListener('click', function () {
      panel.hidden ? openPanel() : closePanel();
    });
    if (closeBtn) closeBtn.addEventListener('click', closePanel);

    document.addEventListener('click', function (e) {
      if (!e.target.closest('#pcSettingsWidget')) closePanel();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closePanel();
    });

    // Language dropdown: open/close + selection.
    if (langTrigger && langListbox) {
      langTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !langListbox.hidden;
        langListbox.hidden = isOpen;
        langTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      langListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var code = opt.getAttribute('data-lang');
        setPreferences({ lang: code });
        applyLangSelection(code);
        closeAllDropdowns();
        navigateToLang(code);
      });
      // Keyboard support: ArrowUp/ArrowDown move focus between options,
      // Enter/Space activates the focused option.
      langListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(langListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          langTrigger.focus();
        }
      });
    }

    // Color Vision dropdown: same open/close/selection/keyboard pattern as
    // the language dropdown above -- unlike language, selecting an option
    // here does NOT navigate anywhere, it just applies immediately.
    if (visionTrigger && visionListbox) {
      visionTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !visionListbox.hidden;
        visionListbox.hidden = isOpen;
        visionTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      visionListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var vision = opt.getAttribute('data-vision');
        setPreferences({ colorVision: vision });
        applyVisionSelection(vision);
        closeAllDropdowns();
      });
      visionListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(visionListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          visionTrigger.focus();
        }
      });
    }

    // Both opacity sliders -- native <input type="range"> already provides
    // full keyboard support (Arrow keys, Home/End, PageUp/PageDown); role
    // and aria-value* attributes are set explicitly per this task's spec.
    if (opacitySlider) {
      opacitySlider.addEventListener('input', function () {
        var value = Number(opacitySlider.value);
        applyBackgroundOpacity(value);
        setPreferences({ opacity: value });
      });
    }
    if (widgetOpacitySlider) {
      widgetOpacitySlider.addEventListener('input', function () {
        var value = Number(widgetOpacitySlider.value);
        applyWidgetOpacity(value);
        setPreferences({ widgetOpacity: value });
      });
    }

    // Native `storage` event -- fires in OTHER tabs/windows on this origin
    // when `preferences` changes there, and vice versa. This is the
    // cross-tab sync mechanism this task requires; no polling needed.
    window.addEventListener('storage', function (e) {
      if (e.key !== PREFS_KEY) return;
      var updated = getPreferences();
      applyBackgroundOpacity(updated.opacity);
      applyWidgetOpacity(updated.widgetOpacity);
      applyLangSelection(updated.lang);
      applyVisionSelection(updated.colorVision);
    });
  });
})();
// TASK-9 END
</script>
</body>
```
<!-- TASK-10 END -->

### `aquariums.html`  →  live path `pets/aquariums/index.html`

<!-- TASK-10 START -->
**Change 1 of 2 — View Transitions style block, before `</head>`**

**Old code:**
```html
  </head>
```
**New code:**
```html
<!-- TASK-9 START: cross-document View Transitions API, enabled sitewide.
     Animates same-origin full-page navigations (e.g. the Settings Widget's
     language switcher navigating between /, /es/, /pt/, /fr/, /ar/, /zh/)
     in browsers that support the cross-document View Transitions API
     (Chromium 126+). Browsers without support ignore this at-rule and
     navigate normally with no error -- this is a progressive enhancement,
     not a dependency. -->
<style>
@view-transition {
  navigation: auto;
}
</style>
<!-- TASK-9 END -->
  </head>
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Change 2 of 2 — Settings Widget (style + markup + script), before `</body>`**

**Old code:**
```html
</body>
```
**New code:**
```html
<!-- TASK-9 START: Settings Widget -- gear-icon trigger + panel housing the
     sitewide language switcher and the personal widget-background-opacity
     slider. Defines the shared `preferences` object (localStorage key
     "preferences", fields {lang, opacity}) that this page's own
     lang-redirect.js / i18n-core.js (Task 1) already read `preferences.lang`
     from, and that Admin-Project #4's `preferred_chat_language` field also
     reads independently -- the exact field name/shape/values documented in
     this task must not change without notifying that project first.
     Z-INDEX NOTE: the site's real, documented highest z-index anywhere is
     6001 (the filter/form bottom-sheet system), not the floating contact
     button's 4000 -- this widget's trigger/panel/dropdown are placed at
     6100/6200/6300 so they always sit above every existing floating
     element, including both of those. -->
<style>
/* Renamed from --pc-widget-opacity (added in this task's first pass) to
   --pc-widget-bg-opacity, and a second, distinct --pc-widget-opacity
   variable added for the whole widget's own opacity -- per user follow-up,
   "Widget Background Opacity" (panel's rgba background only, children
   stay fully legible) and "Widget Opacity" (the entire trigger + panel,
   via real CSS `opacity` on the container) are two separate controls. */
:root { --pc-widget-bg-opacity: 0.90; --pc-widget-opacity: 1; }

/* "Central position" = vertically centered on the viewport's right edge.
   User follow-up moved this from the left edge (this task's original
   build) to the right edge -- everything else about the placement logic
   from that first pass still applies: vertically centered avoids the
   hero's own CTA buttons (the collision found and fixed when this was
   briefly bottom-center), and vertical centering keeps clear of the
   bottom-right floating contact button (z-index 4000), re-confirmed by
   Playwright screenshot below since moving to the same edge as that
   button is exactly the kind of collision worth re-checking, not
   assuming safe by reasoning alone. */
.pc-settings-widget {
  position: fixed;
  top: 50%;
  right: 16px;
  transform: translateY(-50%);
  z-index: 6100;
  font-family: 'Sora', sans-serif;
  /* Whole-widget opacity (trigger + panel together), separate from the
     panel-only rgba background opacity below. Real CSS `opacity`, by
     design, fades everything inside -- that's the intended effect of this
     specific control, unlike the background one. */
  opacity: var(--pc-widget-opacity);
  transition: opacity 0.2s ease;
}

.pc-settings-trigger {
  width: 52px; height: 52px; border-radius: 50%; border: none; cursor: pointer;
  background: #10214b; color: #d7bd88;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 16px rgba(16,33,75,0.35);
  transition: transform 0.3s cubic-bezier(.4,0,.2,1), background 0.3s cubic-bezier(.4,0,.2,1);
}
.pc-settings-trigger:hover { transform: rotate(35deg); background: #1a2f5c; }
.pc-settings-trigger[aria-expanded="true"] { background: #ED7624; color: #fff; }

.pc-settings-panel {
  position: absolute;
  top: 50%;
  /* Opens to the LEFT of the trigger now that the trigger sits on the
     right edge (was `left: calc(100% + 12px)` opening rightward off a
     left-edge trigger). */
  right: calc(100% + 12px);
  transform: translateY(-50%);
  width: 280px;
  max-width: calc(100vw - 32px);
  /* rgba() background driven by the Widget Background Opacity slider --
     never container `opacity`, which would also fade the panel's
     text/children (Task 9's explicit requirement: "rgba() backgrounds,
     not container opacity"). The separate whole-widget opacity control
     lives on .pc-settings-widget above. */
  background: rgba(16, 33, 75, var(--pc-widget-bg-opacity));
  color: #f2f2f2;
  border-radius: 14px;
  padding: 16px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.3);
  z-index: 6200;
}
.pc-settings-panel[hidden] { display: none; }

.pc-settings-panel__header {
  display: flex; align-items: center; justify-content: space-between;
  font-weight: 700; margin-bottom: 12px; font-size: 15px;
}
.pc-settings-panel__close {
  background: none; border: none; color: inherit; font-size: 20px; line-height: 1;
  cursor: pointer; padding: 2px 6px;
}

.pc-settings-row { margin-bottom: 14px; }
.pc-settings-row:last-child { margin-bottom: 0; }
.pc-settings-label { display: block; font-size: 12.5px; margin-bottom: 6px; color: #d7bd88; }
/* One-line description under a consequential setting, per Design System
   Section 19's "Settings Widget Control Pattern -- Toggle + Description"
   rule (pair every consequential control with a plain-language line
   explaining its effect, since a user shouldn't have to guess). */
.pc-settings-desc { font-size: 11.5px; color: #a39b91; margin: -2px 0 8px; line-height: 1.4; }

.pc-lang-dropdown { position: relative; }
.pc-lang-dropdown__trigger {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.16);
  color: #f2f2f2; border-radius: 8px; padding: 8px 12px; font-size: 13.5px; cursor: pointer;
  font-family: 'Sora', sans-serif;
}
.pc-lang-dropdown__panel {
  position: absolute; top: calc(100% + 6px); left: 0; right: 0;
  background: #10214b; border: 1px solid rgba(255,255,255,0.14);
  /* Sharp corner nearest the trigger (top-left here, since the trigger
     sits above this downward-opening panel) -- the "message bubble" corner
     rule from PETCENTER_DESIGN_SYSTEM_v1_0.md Section 24 (v1.25). */
  border-radius: 0 8px 8px 8px;
  list-style: none; margin: 0; padding: 4px; max-height: 200px; overflow-y: auto;
  z-index: 6300;
}
.pc-lang-dropdown__panel[hidden] { display: none; }
.pc-lang-dropdown__panel li { padding: 8px 10px; font-size: 13.5px; border-radius: 6px; cursor: pointer; }
.pc-lang-dropdown__panel li:hover,
.pc-lang-dropdown__panel li[aria-selected="true"] { background: rgba(215,189,136,0.18); }

.pc-opacity-slider { width: 100%; accent-color: #d7bd88; }
.pc-opacity-value { font-size: 12px; color: #a39b91; float: right; margin-top: 2px; }

@media (max-width: 600px) {
  /* Below 600px, opening the panel to the right of a left-edge trigger
     would push a 280px-wide panel off the right edge of the viewport on
     most phones. Anchor the panel to open downward from the trigger
     instead, still clear of both the hero content and the bottom-right
     contact button. Anchored via `right: 0` (not `left: 0`) since the
     trigger itself now sits near the right edge -- `left: 0` would push
     a near-full-width panel further right and off-screen. */
  .pc-settings-panel {
    top: calc(100% + 10px);
    right: 0;
    left: auto;
    transform: none;
    width: calc(100vw - 48px);
  }
}
</style>

<div class="pc-settings-widget" id="pcSettingsWidget">
  <button type="button" class="pc-settings-trigger" id="pcSettingsTrigger"
          aria-haspopup="true" aria-expanded="false" aria-controls="pcSettingsPanel"
          aria-label="Open site settings">
    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <circle cx="12" cy="12" r="3"></circle>
      <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
    </svg>
  </button>

  <div class="pc-settings-panel" id="pcSettingsPanel" role="dialog" aria-modal="false"
       aria-label="Site settings" hidden="">
    <div class="pc-settings-panel__header">
      <span data-i18n="common.settingsWidget.1">Settings</span>
      <button type="button" class="pc-settings-panel__close" id="pcSettingsClose"
              aria-label="Close settings">&times;</button>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcLangLabel" data-i18n="common.settingsWidget.2">Language</label>
      <div class="pc-lang-dropdown" id="pcLangDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcLangTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcLangLabel"
                aria-controls="pcLangListbox">
          <span id="pcLangCurrentLabel">English</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcLangListbox" role="listbox"
            aria-labelledby="pcLangLabel" tabindex="-1" hidden="">
          <li role="option" data-lang="en" aria-selected="true" tabindex="0">English</li>
          <li role="option" data-lang="es" aria-selected="false" tabindex="0">Español</li>
          <li role="option" data-lang="pt" aria-selected="false" tabindex="0">Português</li>
          <li role="option" data-lang="fr" aria-selected="false" tabindex="0">Français</li>
          <li role="option" data-lang="ar" aria-selected="false" tabindex="0">العربية</li>
          <li role="option" data-lang="zh-Hans" aria-selected="false" tabindex="0">简体中文</li>
        </ul>
      </div>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcOpacitySlider" id="pcOpacityLabel" data-i18n="common.settingsWidget.3">Widget Background Opacity</label>
      <input type="range" id="pcOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="90"
             role="slider" aria-labelledby="pcOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="90" aria-valuetext="90%">
      <span class="pc-opacity-value" id="pcOpacityValue" aria-hidden="true">90%</span>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcWidgetOpacitySlider" id="pcWidgetOpacityLabel" data-i18n="common.settingsWidget.4">Widget Opacity</label>
      <input type="range" id="pcWidgetOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="100"
             role="slider" aria-labelledby="pcWidgetOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="100" aria-valuetext="100%">
      <span class="pc-opacity-value" id="pcWidgetOpacityValue" aria-hidden="true">100%</span>
    </div>

    <!-- TASK (Color Vision Accessibility) START: brief calls for this
         control to sit "alongside the existing dark mode toggle" -- no dark
         mode toggle exists anywhere in this codebase yet (Design System
         Section 19 documents its intended look/behavior but confirms it
         is not yet built; searched before assuming otherwise). Not adding
         a placeholder/stub toggle for a feature that hasn't been
         specified as buildable here -- flagging the gap in this task's
         completion file instead, per "flag it rather than inventing." -->
    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcVisionLabel" data-i18n="common.settingsWidget.5">Color Vision</label>
      <p class="pc-settings-desc" id="pcVisionDesc" data-i18n="common.settingsWidget.6">Adjusts status colors, alerts, and charts sitewide for your selected vision type.</p>
      <div class="pc-lang-dropdown" id="pcVisionDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcVisionTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcVisionLabel"
                aria-controls="pcVisionListbox">
          <span id="pcVisionCurrentLabel">Normal Vision</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcVisionListbox" role="listbox"
            aria-labelledby="pcVisionLabel" tabindex="-1" hidden="">
          <li role="option" data-vision="normal" aria-selected="true" tabindex="0">Normal Vision</li>
          <li role="option" data-vision="deuteranopia" aria-selected="false" tabindex="0">Deuteranopia</li>
          <li role="option" data-vision="protanopia" aria-selected="false" tabindex="0">Protanopia</li>
          <li role="option" data-vision="tritanopia" aria-selected="false" tabindex="0">Tritanopia</li>
        </ul>
      </div>
    </div>
    <!-- TASK (Color Vision Accessibility) END -->
  </div>
</div>

<script>
// TASK-9 START: Settings Widget logic + `preferences` schema owner.
(function () {
  'use strict';

  var PREFS_KEY = 'preferences';
  // Written by admin-dashboard.html's "Default Widget Background Opacity"
  // and "Default Widget Opacity" controls (this task's admin-side
  // deliverable). Read here only as a fallback default for a visitor with
  // no personal preference saved yet -- a visitor's own saved choice in
  // `preferences.opacity` / `preferences.widgetOpacity` always wins.
  var ADMIN_DEFAULTS_KEY = 'pc_admin_settings_defaults';

  // LANG_FOLDER_MAP mirrors Task 1's /en/-/zh/ folder scaffolding -- English
  // is the un-prefixed root (never a redirect target), the rest are their
  // own language-code folders. 'zh-Hans' (tagged, not bare 'zh') is the
  // CEO-confirmed value for Chinese everywhere this schema is used.
  var LANG_FOLDER_MAP = { en: '/', es: '/es/', pt: '/pt/', fr: '/fr/', ar: '/ar/', 'zh-Hans': '/zh/' };
  var LANG_LABELS = { en: 'English', es: 'Español', pt: 'Português', fr: 'Français', ar: 'العربية', 'zh-Hans': '简体中文' };
  var VISION_LABELS = { normal: 'Normal Vision', deuteranopia: 'Deuteranopia', protanopia: 'Protanopia', tritanopia: 'Tritanopia' };

  function readAdminDefaultOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 90;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.opacity);
      return (val >= 20 && val <= 100) ? val : 90;
    } catch (e) { return 90; }
  }

  function readAdminDefaultWidgetOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 100;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.widgetOpacity);
      return (val >= 20 && val <= 100) ? val : 100;
    } catch (e) { return 100; }
  }

  function getPreferences() {
    try {
      var raw = localStorage.getItem(PREFS_KEY);
      var parsed = raw ? JSON.parse(raw) : {};
      return {
        lang: (typeof parsed.lang === 'string' && parsed.lang) ? parsed.lang : 'en',
        opacity: (typeof parsed.opacity === 'number') ? parsed.opacity : readAdminDefaultOpacity(),
        widgetOpacity: (typeof parsed.widgetOpacity === 'number') ? parsed.widgetOpacity : readAdminDefaultWidgetOpacity(),
        colorVision: (typeof parsed.colorVision === 'string' && VISION_LABELS[parsed.colorVision]) ? parsed.colorVision : 'normal'
      };
    } catch (e) {
      return { lang: 'en', opacity: readAdminDefaultOpacity(), widgetOpacity: readAdminDefaultWidgetOpacity(), colorVision: 'normal' };
    }
  }

  function setPreferences(partial) {
    var current = getPreferences();
    var next = { lang: current.lang, opacity: current.opacity, widgetOpacity: current.widgetOpacity, colorVision: current.colorVision };
    for (var k in partial) { if (Object.prototype.hasOwnProperty.call(partial, k)) next[k] = partial[k]; }
    try { localStorage.setItem(PREFS_KEY, JSON.stringify(next)); } catch (e) { /* storage unavailable -- fail silently, in-memory state still applies for this tab */ }
    return next;
  }

  // Panel-only rgba() background opacity -- children stay fully legible
  // regardless of this value.
  function applyBackgroundOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcOpacitySlider');
    var valueEl = document.getElementById('pcOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  // Whole-widget opacity -- real CSS `opacity` on .pc-settings-widget,
  // fades the trigger button and everything inside the panel together.
  function applyWidgetOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcWidgetOpacitySlider');
    var valueEl = document.getElementById('pcWidgetOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  function applyLangSelection(code) {
    var currentLabelEl = document.getElementById('pcLangCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = LANG_LABELS[code] || 'English';
    var options = document.querySelectorAll('#pcLangListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-lang') === code ? 'true' : 'false');
    });
  }

  // Sets the `data-vision` attribute on <html>, which every
  // [data-vision="..."] CSS override block above (and in admin-dashboard.html)
  // keys off of. "normal" matches no override rule, so it's a no-op that
  // simply leaves the default colors in place -- no separate "clear" branch
  // needed.
  function applyVisionSelection(vision) {
    document.documentElement.setAttribute('data-vision', vision);
    var currentLabelEl = document.getElementById('pcVisionCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = VISION_LABELS[vision] || 'Normal Vision';
    var options = document.querySelectorAll('#pcVisionListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-vision') === vision ? 'true' : 'false');
    });
  }

  function navigateToLang(code) {
    var target = LANG_FOLDER_MAP[code] || '/';
    // Cross-document View Transitions (enabled sitewide via the
    // `@view-transition { navigation: auto; }` at-rule in <head>) animate
    // this navigation automatically in supporting browsers -- a plain
    // same-origin navigation is all that's needed to trigger it.
    window.location.href = target;
  }

  // Apply stored/default preferences immediately (before DOMContentLoaded)
  // so both CSS variables are correct before first paint -- avoids a flash
  // of the defaults on a return visitor with different saved values. The
  // `data-vision` attribute is likewise set here, before first paint, so
  // there's no flash of un-adjusted colors either.
  (function initFromStoredPreferencesEarly() {
    var prefs = getPreferences();
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (prefs.opacity / 100).toFixed(2));
    document.documentElement.style.setProperty('--pc-widget-opacity', (prefs.widgetOpacity / 100).toFixed(2));
    document.documentElement.setAttribute('data-vision', prefs.colorVision);
  })();

  document.addEventListener('DOMContentLoaded', function () {
    var trigger = document.getElementById('pcSettingsTrigger');
    var panel = document.getElementById('pcSettingsPanel');
    var closeBtn = document.getElementById('pcSettingsClose');
    var langTrigger = document.getElementById('pcLangTrigger');
    var langListbox = document.getElementById('pcLangListbox');
    var opacitySlider = document.getElementById('pcOpacitySlider');
    var widgetOpacitySlider = document.getElementById('pcWidgetOpacitySlider');
    var visionTrigger = document.getElementById('pcVisionTrigger');
    var visionListbox = document.getElementById('pcVisionListbox');

    if (!trigger || !panel) return;

    var prefs = getPreferences();
    applyBackgroundOpacity(prefs.opacity);
    applyWidgetOpacity(prefs.widgetOpacity);
    applyLangSelection(prefs.lang);
    applyVisionSelection(prefs.colorVision);

    function openPanel() {
      panel.hidden = false;
      trigger.setAttribute('aria-expanded', 'true');
    }
    function closeAllDropdowns() {
      if (langListbox && !langListbox.hidden) {
        langListbox.hidden = true;
        langTrigger.setAttribute('aria-expanded', 'false');
      }
      if (visionListbox && !visionListbox.hidden) {
        visionListbox.hidden = true;
        visionTrigger.setAttribute('aria-expanded', 'false');
      }
    }
    function closePanel() {
      panel.hidden = true;
      trigger.setAttribute('aria-expanded', 'false');
      closeAllDropdowns();
    }

    trigger.addEventListener('click', function () {
      panel.hidden ? openPanel() : closePanel();
    });
    if (closeBtn) closeBtn.addEventListener('click', closePanel);

    document.addEventListener('click', function (e) {
      if (!e.target.closest('#pcSettingsWidget')) closePanel();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closePanel();
    });

    // Language dropdown: open/close + selection.
    if (langTrigger && langListbox) {
      langTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !langListbox.hidden;
        langListbox.hidden = isOpen;
        langTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      langListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var code = opt.getAttribute('data-lang');
        setPreferences({ lang: code });
        applyLangSelection(code);
        closeAllDropdowns();
        navigateToLang(code);
      });
      // Keyboard support: ArrowUp/ArrowDown move focus between options,
      // Enter/Space activates the focused option.
      langListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(langListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          langTrigger.focus();
        }
      });
    }

    // Color Vision dropdown: same open/close/selection/keyboard pattern as
    // the language dropdown above -- unlike language, selecting an option
    // here does NOT navigate anywhere, it just applies immediately.
    if (visionTrigger && visionListbox) {
      visionTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !visionListbox.hidden;
        visionListbox.hidden = isOpen;
        visionTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      visionListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var vision = opt.getAttribute('data-vision');
        setPreferences({ colorVision: vision });
        applyVisionSelection(vision);
        closeAllDropdowns();
      });
      visionListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(visionListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          visionTrigger.focus();
        }
      });
    }

    // Both opacity sliders -- native <input type="range"> already provides
    // full keyboard support (Arrow keys, Home/End, PageUp/PageDown); role
    // and aria-value* attributes are set explicitly per this task's spec.
    if (opacitySlider) {
      opacitySlider.addEventListener('input', function () {
        var value = Number(opacitySlider.value);
        applyBackgroundOpacity(value);
        setPreferences({ opacity: value });
      });
    }
    if (widgetOpacitySlider) {
      widgetOpacitySlider.addEventListener('input', function () {
        var value = Number(widgetOpacitySlider.value);
        applyWidgetOpacity(value);
        setPreferences({ widgetOpacity: value });
      });
    }

    // Native `storage` event -- fires in OTHER tabs/windows on this origin
    // when `preferences` changes there, and vice versa. This is the
    // cross-tab sync mechanism this task requires; no polling needed.
    window.addEventListener('storage', function (e) {
      if (e.key !== PREFS_KEY) return;
      var updated = getPreferences();
      applyBackgroundOpacity(updated.opacity);
      applyWidgetOpacity(updated.widgetOpacity);
      applyLangSelection(updated.lang);
      applyVisionSelection(updated.colorVision);
    });
  });
})();
// TASK-9 END
</script>
</body>
```
<!-- TASK-10 END -->

### `pets.html`  →  live path `pets/index.html`

<!-- TASK-10 START -->
**Change 1 of 2 — View Transitions style block, before `</head>`**

**Old code:**
```html
</head>
```
**New code:**
```html
<!-- TASK-9 START: cross-document View Transitions API, enabled sitewide.
     Animates same-origin full-page navigations (e.g. the Settings Widget's
     language switcher navigating between /, /es/, /pt/, /fr/, /ar/, /zh/)
     in browsers that support the cross-document View Transitions API
     (Chromium 126+). Browsers without support ignore this at-rule and
     navigate normally with no error -- this is a progressive enhancement,
     not a dependency. -->
<style>
@view-transition {
  navigation: auto;
}
</style>
<!-- TASK-9 END -->
</head>
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Change 2 of 2 — Settings Widget (style + markup + script), before `</body>`**

**Old code:**
```html
</body>
```
**New code:**
```html
<!-- TASK-9 START: Settings Widget -- gear-icon trigger + panel housing the
     sitewide language switcher and the personal widget-background-opacity
     slider. Defines the shared `preferences` object (localStorage key
     "preferences", fields {lang, opacity}) that this page's own
     lang-redirect.js / i18n-core.js (Task 1) already read `preferences.lang`
     from, and that Admin-Project #4's `preferred_chat_language` field also
     reads independently -- the exact field name/shape/values documented in
     this task must not change without notifying that project first.
     Z-INDEX NOTE: the site's real, documented highest z-index anywhere is
     6001 (the filter/form bottom-sheet system), not the floating contact
     button's 4000 -- this widget's trigger/panel/dropdown are placed at
     6100/6200/6300 so they always sit above every existing floating
     element, including both of those. -->
<style>
/* Renamed from --pc-widget-opacity (added in this task's first pass) to
   --pc-widget-bg-opacity, and a second, distinct --pc-widget-opacity
   variable added for the whole widget's own opacity -- per user follow-up,
   "Widget Background Opacity" (panel's rgba background only, children
   stay fully legible) and "Widget Opacity" (the entire trigger + panel,
   via real CSS `opacity` on the container) are two separate controls. */
:root { --pc-widget-bg-opacity: 0.90; --pc-widget-opacity: 1; }

/* "Central position" = vertically centered on the viewport's right edge.
   User follow-up moved this from the left edge (this task's original
   build) to the right edge -- everything else about the placement logic
   from that first pass still applies: vertically centered avoids the
   hero's own CTA buttons (the collision found and fixed when this was
   briefly bottom-center), and vertical centering keeps clear of the
   bottom-right floating contact button (z-index 4000), re-confirmed by
   Playwright screenshot below since moving to the same edge as that
   button is exactly the kind of collision worth re-checking, not
   assuming safe by reasoning alone. */
.pc-settings-widget {
  position: fixed;
  top: 50%;
  right: 16px;
  transform: translateY(-50%);
  z-index: 6100;
  font-family: 'Sora', sans-serif;
  /* Whole-widget opacity (trigger + panel together), separate from the
     panel-only rgba background opacity below. Real CSS `opacity`, by
     design, fades everything inside -- that's the intended effect of this
     specific control, unlike the background one. */
  opacity: var(--pc-widget-opacity);
  transition: opacity 0.2s ease;
}

.pc-settings-trigger {
  width: 52px; height: 52px; border-radius: 50%; border: none; cursor: pointer;
  background: #10214b; color: #d7bd88;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 16px rgba(16,33,75,0.35);
  transition: transform 0.3s cubic-bezier(.4,0,.2,1), background 0.3s cubic-bezier(.4,0,.2,1);
}
.pc-settings-trigger:hover { transform: rotate(35deg); background: #1a2f5c; }
.pc-settings-trigger[aria-expanded="true"] { background: #ED7624; color: #fff; }

.pc-settings-panel {
  position: absolute;
  top: 50%;
  /* Opens to the LEFT of the trigger now that the trigger sits on the
     right edge (was `left: calc(100% + 12px)` opening rightward off a
     left-edge trigger). */
  right: calc(100% + 12px);
  transform: translateY(-50%);
  width: 280px;
  max-width: calc(100vw - 32px);
  /* rgba() background driven by the Widget Background Opacity slider --
     never container `opacity`, which would also fade the panel's
     text/children (Task 9's explicit requirement: "rgba() backgrounds,
     not container opacity"). The separate whole-widget opacity control
     lives on .pc-settings-widget above. */
  background: rgba(16, 33, 75, var(--pc-widget-bg-opacity));
  color: #f2f2f2;
  border-radius: 14px;
  padding: 16px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.3);
  z-index: 6200;
}
.pc-settings-panel[hidden] { display: none; }

.pc-settings-panel__header {
  display: flex; align-items: center; justify-content: space-between;
  font-weight: 700; margin-bottom: 12px; font-size: 15px;
}
.pc-settings-panel__close {
  background: none; border: none; color: inherit; font-size: 20px; line-height: 1;
  cursor: pointer; padding: 2px 6px;
}

.pc-settings-row { margin-bottom: 14px; }
.pc-settings-row:last-child { margin-bottom: 0; }
.pc-settings-label { display: block; font-size: 12.5px; margin-bottom: 6px; color: #d7bd88; }
/* One-line description under a consequential setting, per Design System
   Section 19's "Settings Widget Control Pattern -- Toggle + Description"
   rule (pair every consequential control with a plain-language line
   explaining its effect, since a user shouldn't have to guess). */
.pc-settings-desc { font-size: 11.5px; color: #a39b91; margin: -2px 0 8px; line-height: 1.4; }

.pc-lang-dropdown { position: relative; }
.pc-lang-dropdown__trigger {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.16);
  color: #f2f2f2; border-radius: 8px; padding: 8px 12px; font-size: 13.5px; cursor: pointer;
  font-family: 'Sora', sans-serif;
}
.pc-lang-dropdown__panel {
  position: absolute; top: calc(100% + 6px); left: 0; right: 0;
  background: #10214b; border: 1px solid rgba(255,255,255,0.14);
  /* Sharp corner nearest the trigger (top-left here, since the trigger
     sits above this downward-opening panel) -- the "message bubble" corner
     rule from PETCENTER_DESIGN_SYSTEM_v1_0.md Section 24 (v1.25). */
  border-radius: 0 8px 8px 8px;
  list-style: none; margin: 0; padding: 4px; max-height: 200px; overflow-y: auto;
  z-index: 6300;
}
.pc-lang-dropdown__panel[hidden] { display: none; }
.pc-lang-dropdown__panel li { padding: 8px 10px; font-size: 13.5px; border-radius: 6px; cursor: pointer; }
.pc-lang-dropdown__panel li:hover,
.pc-lang-dropdown__panel li[aria-selected="true"] { background: rgba(215,189,136,0.18); }

.pc-opacity-slider { width: 100%; accent-color: #d7bd88; }
.pc-opacity-value { font-size: 12px; color: #a39b91; float: right; margin-top: 2px; }

@media (max-width: 600px) {
  /* Below 600px, opening the panel to the right of a left-edge trigger
     would push a 280px-wide panel off the right edge of the viewport on
     most phones. Anchor the panel to open downward from the trigger
     instead, still clear of both the hero content and the bottom-right
     contact button. Anchored via `right: 0` (not `left: 0`) since the
     trigger itself now sits near the right edge -- `left: 0` would push
     a near-full-width panel further right and off-screen. */
  .pc-settings-panel {
    top: calc(100% + 10px);
    right: 0;
    left: auto;
    transform: none;
    width: calc(100vw - 48px);
  }
}
</style>

<div class="pc-settings-widget" id="pcSettingsWidget">
  <button type="button" class="pc-settings-trigger" id="pcSettingsTrigger"
          aria-haspopup="true" aria-expanded="false" aria-controls="pcSettingsPanel"
          aria-label="Open site settings">
    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <circle cx="12" cy="12" r="3"></circle>
      <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
    </svg>
  </button>

  <div class="pc-settings-panel" id="pcSettingsPanel" role="dialog" aria-modal="false"
       aria-label="Site settings" hidden="">
    <div class="pc-settings-panel__header">
      <span data-i18n="common.settingsWidget.1">Settings</span>
      <button type="button" class="pc-settings-panel__close" id="pcSettingsClose"
              aria-label="Close settings">&times;</button>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcLangLabel" data-i18n="common.settingsWidget.2">Language</label>
      <div class="pc-lang-dropdown" id="pcLangDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcLangTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcLangLabel"
                aria-controls="pcLangListbox">
          <span id="pcLangCurrentLabel">English</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcLangListbox" role="listbox"
            aria-labelledby="pcLangLabel" tabindex="-1" hidden="">
          <li role="option" data-lang="en" aria-selected="true" tabindex="0">English</li>
          <li role="option" data-lang="es" aria-selected="false" tabindex="0">Español</li>
          <li role="option" data-lang="pt" aria-selected="false" tabindex="0">Português</li>
          <li role="option" data-lang="fr" aria-selected="false" tabindex="0">Français</li>
          <li role="option" data-lang="ar" aria-selected="false" tabindex="0">العربية</li>
          <li role="option" data-lang="zh-Hans" aria-selected="false" tabindex="0">简体中文</li>
        </ul>
      </div>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcOpacitySlider" id="pcOpacityLabel" data-i18n="common.settingsWidget.3">Widget Background Opacity</label>
      <input type="range" id="pcOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="90"
             role="slider" aria-labelledby="pcOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="90" aria-valuetext="90%">
      <span class="pc-opacity-value" id="pcOpacityValue" aria-hidden="true">90%</span>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcWidgetOpacitySlider" id="pcWidgetOpacityLabel" data-i18n="common.settingsWidget.4">Widget Opacity</label>
      <input type="range" id="pcWidgetOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="100"
             role="slider" aria-labelledby="pcWidgetOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="100" aria-valuetext="100%">
      <span class="pc-opacity-value" id="pcWidgetOpacityValue" aria-hidden="true">100%</span>
    </div>

    <!-- TASK (Color Vision Accessibility) START: brief calls for this
         control to sit "alongside the existing dark mode toggle" -- no dark
         mode toggle exists anywhere in this codebase yet (Design System
         Section 19 documents its intended look/behavior but confirms it
         is not yet built; searched before assuming otherwise). Not adding
         a placeholder/stub toggle for a feature that hasn't been
         specified as buildable here -- flagging the gap in this task's
         completion file instead, per "flag it rather than inventing." -->
    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcVisionLabel" data-i18n="common.settingsWidget.5">Color Vision</label>
      <p class="pc-settings-desc" id="pcVisionDesc" data-i18n="common.settingsWidget.6">Adjusts status colors, alerts, and charts sitewide for your selected vision type.</p>
      <div class="pc-lang-dropdown" id="pcVisionDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcVisionTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcVisionLabel"
                aria-controls="pcVisionListbox">
          <span id="pcVisionCurrentLabel">Normal Vision</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcVisionListbox" role="listbox"
            aria-labelledby="pcVisionLabel" tabindex="-1" hidden="">
          <li role="option" data-vision="normal" aria-selected="true" tabindex="0">Normal Vision</li>
          <li role="option" data-vision="deuteranopia" aria-selected="false" tabindex="0">Deuteranopia</li>
          <li role="option" data-vision="protanopia" aria-selected="false" tabindex="0">Protanopia</li>
          <li role="option" data-vision="tritanopia" aria-selected="false" tabindex="0">Tritanopia</li>
        </ul>
      </div>
    </div>
    <!-- TASK (Color Vision Accessibility) END -->
  </div>
</div>

<script>
// TASK-9 START: Settings Widget logic + `preferences` schema owner.
(function () {
  'use strict';

  var PREFS_KEY = 'preferences';
  // Written by admin-dashboard.html's "Default Widget Background Opacity"
  // and "Default Widget Opacity" controls (this task's admin-side
  // deliverable). Read here only as a fallback default for a visitor with
  // no personal preference saved yet -- a visitor's own saved choice in
  // `preferences.opacity` / `preferences.widgetOpacity` always wins.
  var ADMIN_DEFAULTS_KEY = 'pc_admin_settings_defaults';

  // LANG_FOLDER_MAP mirrors Task 1's /en/-/zh/ folder scaffolding -- English
  // is the un-prefixed root (never a redirect target), the rest are their
  // own language-code folders. 'zh-Hans' (tagged, not bare 'zh') is the
  // CEO-confirmed value for Chinese everywhere this schema is used.
  var LANG_FOLDER_MAP = { en: '/', es: '/es/', pt: '/pt/', fr: '/fr/', ar: '/ar/', 'zh-Hans': '/zh/' };
  var LANG_LABELS = { en: 'English', es: 'Español', pt: 'Português', fr: 'Français', ar: 'العربية', 'zh-Hans': '简体中文' };
  var VISION_LABELS = { normal: 'Normal Vision', deuteranopia: 'Deuteranopia', protanopia: 'Protanopia', tritanopia: 'Tritanopia' };

  function readAdminDefaultOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 90;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.opacity);
      return (val >= 20 && val <= 100) ? val : 90;
    } catch (e) { return 90; }
  }

  function readAdminDefaultWidgetOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 100;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.widgetOpacity);
      return (val >= 20 && val <= 100) ? val : 100;
    } catch (e) { return 100; }
  }

  function getPreferences() {
    try {
      var raw = localStorage.getItem(PREFS_KEY);
      var parsed = raw ? JSON.parse(raw) : {};
      return {
        lang: (typeof parsed.lang === 'string' && parsed.lang) ? parsed.lang : 'en',
        opacity: (typeof parsed.opacity === 'number') ? parsed.opacity : readAdminDefaultOpacity(),
        widgetOpacity: (typeof parsed.widgetOpacity === 'number') ? parsed.widgetOpacity : readAdminDefaultWidgetOpacity(),
        colorVision: (typeof parsed.colorVision === 'string' && VISION_LABELS[parsed.colorVision]) ? parsed.colorVision : 'normal'
      };
    } catch (e) {
      return { lang: 'en', opacity: readAdminDefaultOpacity(), widgetOpacity: readAdminDefaultWidgetOpacity(), colorVision: 'normal' };
    }
  }

  function setPreferences(partial) {
    var current = getPreferences();
    var next = { lang: current.lang, opacity: current.opacity, widgetOpacity: current.widgetOpacity, colorVision: current.colorVision };
    for (var k in partial) { if (Object.prototype.hasOwnProperty.call(partial, k)) next[k] = partial[k]; }
    try { localStorage.setItem(PREFS_KEY, JSON.stringify(next)); } catch (e) { /* storage unavailable -- fail silently, in-memory state still applies for this tab */ }
    return next;
  }

  // Panel-only rgba() background opacity -- children stay fully legible
  // regardless of this value.
  function applyBackgroundOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcOpacitySlider');
    var valueEl = document.getElementById('pcOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  // Whole-widget opacity -- real CSS `opacity` on .pc-settings-widget,
  // fades the trigger button and everything inside the panel together.
  function applyWidgetOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcWidgetOpacitySlider');
    var valueEl = document.getElementById('pcWidgetOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  function applyLangSelection(code) {
    var currentLabelEl = document.getElementById('pcLangCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = LANG_LABELS[code] || 'English';
    var options = document.querySelectorAll('#pcLangListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-lang') === code ? 'true' : 'false');
    });
  }

  // Sets the `data-vision` attribute on <html>, which every
  // [data-vision="..."] CSS override block above (and in admin-dashboard.html)
  // keys off of. "normal" matches no override rule, so it's a no-op that
  // simply leaves the default colors in place -- no separate "clear" branch
  // needed.
  function applyVisionSelection(vision) {
    document.documentElement.setAttribute('data-vision', vision);
    var currentLabelEl = document.getElementById('pcVisionCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = VISION_LABELS[vision] || 'Normal Vision';
    var options = document.querySelectorAll('#pcVisionListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-vision') === vision ? 'true' : 'false');
    });
  }

  function navigateToLang(code) {
    var target = LANG_FOLDER_MAP[code] || '/';
    // Cross-document View Transitions (enabled sitewide via the
    // `@view-transition { navigation: auto; }` at-rule in <head>) animate
    // this navigation automatically in supporting browsers -- a plain
    // same-origin navigation is all that's needed to trigger it.
    window.location.href = target;
  }

  // Apply stored/default preferences immediately (before DOMContentLoaded)
  // so both CSS variables are correct before first paint -- avoids a flash
  // of the defaults on a return visitor with different saved values. The
  // `data-vision` attribute is likewise set here, before first paint, so
  // there's no flash of un-adjusted colors either.
  (function initFromStoredPreferencesEarly() {
    var prefs = getPreferences();
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (prefs.opacity / 100).toFixed(2));
    document.documentElement.style.setProperty('--pc-widget-opacity', (prefs.widgetOpacity / 100).toFixed(2));
    document.documentElement.setAttribute('data-vision', prefs.colorVision);
  })();

  document.addEventListener('DOMContentLoaded', function () {
    var trigger = document.getElementById('pcSettingsTrigger');
    var panel = document.getElementById('pcSettingsPanel');
    var closeBtn = document.getElementById('pcSettingsClose');
    var langTrigger = document.getElementById('pcLangTrigger');
    var langListbox = document.getElementById('pcLangListbox');
    var opacitySlider = document.getElementById('pcOpacitySlider');
    var widgetOpacitySlider = document.getElementById('pcWidgetOpacitySlider');
    var visionTrigger = document.getElementById('pcVisionTrigger');
    var visionListbox = document.getElementById('pcVisionListbox');

    if (!trigger || !panel) return;

    var prefs = getPreferences();
    applyBackgroundOpacity(prefs.opacity);
    applyWidgetOpacity(prefs.widgetOpacity);
    applyLangSelection(prefs.lang);
    applyVisionSelection(prefs.colorVision);

    function openPanel() {
      panel.hidden = false;
      trigger.setAttribute('aria-expanded', 'true');
    }
    function closeAllDropdowns() {
      if (langListbox && !langListbox.hidden) {
        langListbox.hidden = true;
        langTrigger.setAttribute('aria-expanded', 'false');
      }
      if (visionListbox && !visionListbox.hidden) {
        visionListbox.hidden = true;
        visionTrigger.setAttribute('aria-expanded', 'false');
      }
    }
    function closePanel() {
      panel.hidden = true;
      trigger.setAttribute('aria-expanded', 'false');
      closeAllDropdowns();
    }

    trigger.addEventListener('click', function () {
      panel.hidden ? openPanel() : closePanel();
    });
    if (closeBtn) closeBtn.addEventListener('click', closePanel);

    document.addEventListener('click', function (e) {
      if (!e.target.closest('#pcSettingsWidget')) closePanel();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closePanel();
    });

    // Language dropdown: open/close + selection.
    if (langTrigger && langListbox) {
      langTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !langListbox.hidden;
        langListbox.hidden = isOpen;
        langTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      langListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var code = opt.getAttribute('data-lang');
        setPreferences({ lang: code });
        applyLangSelection(code);
        closeAllDropdowns();
        navigateToLang(code);
      });
      // Keyboard support: ArrowUp/ArrowDown move focus between options,
      // Enter/Space activates the focused option.
      langListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(langListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          langTrigger.focus();
        }
      });
    }

    // Color Vision dropdown: same open/close/selection/keyboard pattern as
    // the language dropdown above -- unlike language, selecting an option
    // here does NOT navigate anywhere, it just applies immediately.
    if (visionTrigger && visionListbox) {
      visionTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !visionListbox.hidden;
        visionListbox.hidden = isOpen;
        visionTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      visionListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var vision = opt.getAttribute('data-vision');
        setPreferences({ colorVision: vision });
        applyVisionSelection(vision);
        closeAllDropdowns();
      });
      visionListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(visionListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          visionTrigger.focus();
        }
      });
    }

    // Both opacity sliders -- native <input type="range"> already provides
    // full keyboard support (Arrow keys, Home/End, PageUp/PageDown); role
    // and aria-value* attributes are set explicitly per this task's spec.
    if (opacitySlider) {
      opacitySlider.addEventListener('input', function () {
        var value = Number(opacitySlider.value);
        applyBackgroundOpacity(value);
        setPreferences({ opacity: value });
      });
    }
    if (widgetOpacitySlider) {
      widgetOpacitySlider.addEventListener('input', function () {
        var value = Number(widgetOpacitySlider.value);
        applyWidgetOpacity(value);
        setPreferences({ widgetOpacity: value });
      });
    }

    // Native `storage` event -- fires in OTHER tabs/windows on this origin
    // when `preferences` changes there, and vice versa. This is the
    // cross-tab sync mechanism this task requires; no polling needed.
    window.addEventListener('storage', function (e) {
      if (e.key !== PREFS_KEY) return;
      var updated = getPreferences();
      applyBackgroundOpacity(updated.opacity);
      applyWidgetOpacity(updated.widgetOpacity);
      applyLangSelection(updated.lang);
      applyVisionSelection(updated.colorVision);
    });
  });
})();
// TASK-9 END
</script>
</body>
```
<!-- TASK-10 END -->

### `services.html`  →  live path `services/index.html`

<!-- TASK-10 START -->
**Change 1 of 2 — View Transitions style block, before `</head>`**

**Old code:**
```html
  </head>
```
**New code:**
```html
<!-- TASK-9 START: cross-document View Transitions API, enabled sitewide.
     Animates same-origin full-page navigations (e.g. the Settings Widget's
     language switcher navigating between /, /es/, /pt/, /fr/, /ar/, /zh/)
     in browsers that support the cross-document View Transitions API
     (Chromium 126+). Browsers without support ignore this at-rule and
     navigate normally with no error -- this is a progressive enhancement,
     not a dependency. -->
<style>
@view-transition {
  navigation: auto;
}
</style>
<!-- TASK-9 END -->
  </head>
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Change 2 of 2 — Settings Widget (style + markup + script), before `</body>`**

**Old code:**
```html
</body>
```
**New code:**
```html
<!-- TASK-9 START: Settings Widget -- gear-icon trigger + panel housing the
     sitewide language switcher and the personal widget-background-opacity
     slider. Defines the shared `preferences` object (localStorage key
     "preferences", fields {lang, opacity}) that this page's own
     lang-redirect.js / i18n-core.js (Task 1) already read `preferences.lang`
     from, and that Admin-Project #4's `preferred_chat_language` field also
     reads independently -- the exact field name/shape/values documented in
     this task must not change without notifying that project first.
     Z-INDEX NOTE: the site's real, documented highest z-index anywhere is
     6001 (the filter/form bottom-sheet system), not the floating contact
     button's 4000 -- this widget's trigger/panel/dropdown are placed at
     6100/6200/6300 so they always sit above every existing floating
     element, including both of those. -->
<style>
/* Renamed from --pc-widget-opacity (added in this task's first pass) to
   --pc-widget-bg-opacity, and a second, distinct --pc-widget-opacity
   variable added for the whole widget's own opacity -- per user follow-up,
   "Widget Background Opacity" (panel's rgba background only, children
   stay fully legible) and "Widget Opacity" (the entire trigger + panel,
   via real CSS `opacity` on the container) are two separate controls. */
:root { --pc-widget-bg-opacity: 0.90; --pc-widget-opacity: 1; }

/* "Central position" = vertically centered on the viewport's right edge.
   User follow-up moved this from the left edge (this task's original
   build) to the right edge -- everything else about the placement logic
   from that first pass still applies: vertically centered avoids the
   hero's own CTA buttons (the collision found and fixed when this was
   briefly bottom-center), and vertical centering keeps clear of the
   bottom-right floating contact button (z-index 4000), re-confirmed by
   Playwright screenshot below since moving to the same edge as that
   button is exactly the kind of collision worth re-checking, not
   assuming safe by reasoning alone. */
.pc-settings-widget {
  position: fixed;
  top: 50%;
  right: 16px;
  transform: translateY(-50%);
  z-index: 6100;
  font-family: 'Sora', sans-serif;
  /* Whole-widget opacity (trigger + panel together), separate from the
     panel-only rgba background opacity below. Real CSS `opacity`, by
     design, fades everything inside -- that's the intended effect of this
     specific control, unlike the background one. */
  opacity: var(--pc-widget-opacity);
  transition: opacity 0.2s ease;
}

.pc-settings-trigger {
  width: 52px; height: 52px; border-radius: 50%; border: none; cursor: pointer;
  background: #10214b; color: #d7bd88;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 16px rgba(16,33,75,0.35);
  transition: transform 0.3s cubic-bezier(.4,0,.2,1), background 0.3s cubic-bezier(.4,0,.2,1);
}
.pc-settings-trigger:hover { transform: rotate(35deg); background: #1a2f5c; }
.pc-settings-trigger[aria-expanded="true"] { background: #ED7624; color: #fff; }

.pc-settings-panel {
  position: absolute;
  top: 50%;
  /* Opens to the LEFT of the trigger now that the trigger sits on the
     right edge (was `left: calc(100% + 12px)` opening rightward off a
     left-edge trigger). */
  right: calc(100% + 12px);
  transform: translateY(-50%);
  width: 280px;
  max-width: calc(100vw - 32px);
  /* rgba() background driven by the Widget Background Opacity slider --
     never container `opacity`, which would also fade the panel's
     text/children (Task 9's explicit requirement: "rgba() backgrounds,
     not container opacity"). The separate whole-widget opacity control
     lives on .pc-settings-widget above. */
  background: rgba(16, 33, 75, var(--pc-widget-bg-opacity));
  color: #f2f2f2;
  border-radius: 14px;
  padding: 16px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.3);
  z-index: 6200;
}
.pc-settings-panel[hidden] { display: none; }

.pc-settings-panel__header {
  display: flex; align-items: center; justify-content: space-between;
  font-weight: 700; margin-bottom: 12px; font-size: 15px;
}
.pc-settings-panel__close {
  background: none; border: none; color: inherit; font-size: 20px; line-height: 1;
  cursor: pointer; padding: 2px 6px;
}

.pc-settings-row { margin-bottom: 14px; }
.pc-settings-row:last-child { margin-bottom: 0; }
.pc-settings-label { display: block; font-size: 12.5px; margin-bottom: 6px; color: #d7bd88; }
/* One-line description under a consequential setting, per Design System
   Section 19's "Settings Widget Control Pattern -- Toggle + Description"
   rule (pair every consequential control with a plain-language line
   explaining its effect, since a user shouldn't have to guess). */
.pc-settings-desc { font-size: 11.5px; color: #a39b91; margin: -2px 0 8px; line-height: 1.4; }

.pc-lang-dropdown { position: relative; }
.pc-lang-dropdown__trigger {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.16);
  color: #f2f2f2; border-radius: 8px; padding: 8px 12px; font-size: 13.5px; cursor: pointer;
  font-family: 'Sora', sans-serif;
}
.pc-lang-dropdown__panel {
  position: absolute; top: calc(100% + 6px); left: 0; right: 0;
  background: #10214b; border: 1px solid rgba(255,255,255,0.14);
  /* Sharp corner nearest the trigger (top-left here, since the trigger
     sits above this downward-opening panel) -- the "message bubble" corner
     rule from PETCENTER_DESIGN_SYSTEM_v1_0.md Section 24 (v1.25). */
  border-radius: 0 8px 8px 8px;
  list-style: none; margin: 0; padding: 4px; max-height: 200px; overflow-y: auto;
  z-index: 6300;
}
.pc-lang-dropdown__panel[hidden] { display: none; }
.pc-lang-dropdown__panel li { padding: 8px 10px; font-size: 13.5px; border-radius: 6px; cursor: pointer; }
.pc-lang-dropdown__panel li:hover,
.pc-lang-dropdown__panel li[aria-selected="true"] { background: rgba(215,189,136,0.18); }

.pc-opacity-slider { width: 100%; accent-color: #d7bd88; }
.pc-opacity-value { font-size: 12px; color: #a39b91; float: right; margin-top: 2px; }

@media (max-width: 600px) {
  /* Below 600px, opening the panel to the right of a left-edge trigger
     would push a 280px-wide panel off the right edge of the viewport on
     most phones. Anchor the panel to open downward from the trigger
     instead, still clear of both the hero content and the bottom-right
     contact button. Anchored via `right: 0` (not `left: 0`) since the
     trigger itself now sits near the right edge -- `left: 0` would push
     a near-full-width panel further right and off-screen. */
  .pc-settings-panel {
    top: calc(100% + 10px);
    right: 0;
    left: auto;
    transform: none;
    width: calc(100vw - 48px);
  }
}
</style>

<div class="pc-settings-widget" id="pcSettingsWidget">
  <button type="button" class="pc-settings-trigger" id="pcSettingsTrigger"
          aria-haspopup="true" aria-expanded="false" aria-controls="pcSettingsPanel"
          aria-label="Open site settings">
    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <circle cx="12" cy="12" r="3"></circle>
      <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
    </svg>
  </button>

  <div class="pc-settings-panel" id="pcSettingsPanel" role="dialog" aria-modal="false"
       aria-label="Site settings" hidden="">
    <div class="pc-settings-panel__header">
      <span data-i18n="common.settingsWidget.1">Settings</span>
      <button type="button" class="pc-settings-panel__close" id="pcSettingsClose"
              aria-label="Close settings">&times;</button>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcLangLabel" data-i18n="common.settingsWidget.2">Language</label>
      <div class="pc-lang-dropdown" id="pcLangDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcLangTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcLangLabel"
                aria-controls="pcLangListbox">
          <span id="pcLangCurrentLabel">English</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcLangListbox" role="listbox"
            aria-labelledby="pcLangLabel" tabindex="-1" hidden="">
          <li role="option" data-lang="en" aria-selected="true" tabindex="0">English</li>
          <li role="option" data-lang="es" aria-selected="false" tabindex="0">Español</li>
          <li role="option" data-lang="pt" aria-selected="false" tabindex="0">Português</li>
          <li role="option" data-lang="fr" aria-selected="false" tabindex="0">Français</li>
          <li role="option" data-lang="ar" aria-selected="false" tabindex="0">العربية</li>
          <li role="option" data-lang="zh-Hans" aria-selected="false" tabindex="0">简体中文</li>
        </ul>
      </div>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcOpacitySlider" id="pcOpacityLabel" data-i18n="common.settingsWidget.3">Widget Background Opacity</label>
      <input type="range" id="pcOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="90"
             role="slider" aria-labelledby="pcOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="90" aria-valuetext="90%">
      <span class="pc-opacity-value" id="pcOpacityValue" aria-hidden="true">90%</span>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcWidgetOpacitySlider" id="pcWidgetOpacityLabel" data-i18n="common.settingsWidget.4">Widget Opacity</label>
      <input type="range" id="pcWidgetOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="100"
             role="slider" aria-labelledby="pcWidgetOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="100" aria-valuetext="100%">
      <span class="pc-opacity-value" id="pcWidgetOpacityValue" aria-hidden="true">100%</span>
    </div>

    <!-- TASK (Color Vision Accessibility) START: brief calls for this
         control to sit "alongside the existing dark mode toggle" -- no dark
         mode toggle exists anywhere in this codebase yet (Design System
         Section 19 documents its intended look/behavior but confirms it
         is not yet built; searched before assuming otherwise). Not adding
         a placeholder/stub toggle for a feature that hasn't been
         specified as buildable here -- flagging the gap in this task's
         completion file instead, per "flag it rather than inventing." -->
    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcVisionLabel" data-i18n="common.settingsWidget.5">Color Vision</label>
      <p class="pc-settings-desc" id="pcVisionDesc" data-i18n="common.settingsWidget.6">Adjusts status colors, alerts, and charts sitewide for your selected vision type.</p>
      <div class="pc-lang-dropdown" id="pcVisionDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcVisionTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcVisionLabel"
                aria-controls="pcVisionListbox">
          <span id="pcVisionCurrentLabel">Normal Vision</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcVisionListbox" role="listbox"
            aria-labelledby="pcVisionLabel" tabindex="-1" hidden="">
          <li role="option" data-vision="normal" aria-selected="true" tabindex="0">Normal Vision</li>
          <li role="option" data-vision="deuteranopia" aria-selected="false" tabindex="0">Deuteranopia</li>
          <li role="option" data-vision="protanopia" aria-selected="false" tabindex="0">Protanopia</li>
          <li role="option" data-vision="tritanopia" aria-selected="false" tabindex="0">Tritanopia</li>
        </ul>
      </div>
    </div>
    <!-- TASK (Color Vision Accessibility) END -->
  </div>
</div>

<script>
// TASK-9 START: Settings Widget logic + `preferences` schema owner.
(function () {
  'use strict';

  var PREFS_KEY = 'preferences';
  // Written by admin-dashboard.html's "Default Widget Background Opacity"
  // and "Default Widget Opacity" controls (this task's admin-side
  // deliverable). Read here only as a fallback default for a visitor with
  // no personal preference saved yet -- a visitor's own saved choice in
  // `preferences.opacity` / `preferences.widgetOpacity` always wins.
  var ADMIN_DEFAULTS_KEY = 'pc_admin_settings_defaults';

  // LANG_FOLDER_MAP mirrors Task 1's /en/-/zh/ folder scaffolding -- English
  // is the un-prefixed root (never a redirect target), the rest are their
  // own language-code folders. 'zh-Hans' (tagged, not bare 'zh') is the
  // CEO-confirmed value for Chinese everywhere this schema is used.
  var LANG_FOLDER_MAP = { en: '/', es: '/es/', pt: '/pt/', fr: '/fr/', ar: '/ar/', 'zh-Hans': '/zh/' };
  var LANG_LABELS = { en: 'English', es: 'Español', pt: 'Português', fr: 'Français', ar: 'العربية', 'zh-Hans': '简体中文' };
  var VISION_LABELS = { normal: 'Normal Vision', deuteranopia: 'Deuteranopia', protanopia: 'Protanopia', tritanopia: 'Tritanopia' };

  function readAdminDefaultOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 90;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.opacity);
      return (val >= 20 && val <= 100) ? val : 90;
    } catch (e) { return 90; }
  }

  function readAdminDefaultWidgetOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 100;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.widgetOpacity);
      return (val >= 20 && val <= 100) ? val : 100;
    } catch (e) { return 100; }
  }

  function getPreferences() {
    try {
      var raw = localStorage.getItem(PREFS_KEY);
      var parsed = raw ? JSON.parse(raw) : {};
      return {
        lang: (typeof parsed.lang === 'string' && parsed.lang) ? parsed.lang : 'en',
        opacity: (typeof parsed.opacity === 'number') ? parsed.opacity : readAdminDefaultOpacity(),
        widgetOpacity: (typeof parsed.widgetOpacity === 'number') ? parsed.widgetOpacity : readAdminDefaultWidgetOpacity(),
        colorVision: (typeof parsed.colorVision === 'string' && VISION_LABELS[parsed.colorVision]) ? parsed.colorVision : 'normal'
      };
    } catch (e) {
      return { lang: 'en', opacity: readAdminDefaultOpacity(), widgetOpacity: readAdminDefaultWidgetOpacity(), colorVision: 'normal' };
    }
  }

  function setPreferences(partial) {
    var current = getPreferences();
    var next = { lang: current.lang, opacity: current.opacity, widgetOpacity: current.widgetOpacity, colorVision: current.colorVision };
    for (var k in partial) { if (Object.prototype.hasOwnProperty.call(partial, k)) next[k] = partial[k]; }
    try { localStorage.setItem(PREFS_KEY, JSON.stringify(next)); } catch (e) { /* storage unavailable -- fail silently, in-memory state still applies for this tab */ }
    return next;
  }

  // Panel-only rgba() background opacity -- children stay fully legible
  // regardless of this value.
  function applyBackgroundOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcOpacitySlider');
    var valueEl = document.getElementById('pcOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  // Whole-widget opacity -- real CSS `opacity` on .pc-settings-widget,
  // fades the trigger button and everything inside the panel together.
  function applyWidgetOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcWidgetOpacitySlider');
    var valueEl = document.getElementById('pcWidgetOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  function applyLangSelection(code) {
    var currentLabelEl = document.getElementById('pcLangCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = LANG_LABELS[code] || 'English';
    var options = document.querySelectorAll('#pcLangListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-lang') === code ? 'true' : 'false');
    });
  }

  // Sets the `data-vision` attribute on <html>, which every
  // [data-vision="..."] CSS override block above (and in admin-dashboard.html)
  // keys off of. "normal" matches no override rule, so it's a no-op that
  // simply leaves the default colors in place -- no separate "clear" branch
  // needed.
  function applyVisionSelection(vision) {
    document.documentElement.setAttribute('data-vision', vision);
    var currentLabelEl = document.getElementById('pcVisionCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = VISION_LABELS[vision] || 'Normal Vision';
    var options = document.querySelectorAll('#pcVisionListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-vision') === vision ? 'true' : 'false');
    });
  }

  function navigateToLang(code) {
    var target = LANG_FOLDER_MAP[code] || '/';
    // Cross-document View Transitions (enabled sitewide via the
    // `@view-transition { navigation: auto; }` at-rule in <head>) animate
    // this navigation automatically in supporting browsers -- a plain
    // same-origin navigation is all that's needed to trigger it.
    window.location.href = target;
  }

  // Apply stored/default preferences immediately (before DOMContentLoaded)
  // so both CSS variables are correct before first paint -- avoids a flash
  // of the defaults on a return visitor with different saved values. The
  // `data-vision` attribute is likewise set here, before first paint, so
  // there's no flash of un-adjusted colors either.
  (function initFromStoredPreferencesEarly() {
    var prefs = getPreferences();
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (prefs.opacity / 100).toFixed(2));
    document.documentElement.style.setProperty('--pc-widget-opacity', (prefs.widgetOpacity / 100).toFixed(2));
    document.documentElement.setAttribute('data-vision', prefs.colorVision);
  })();

  document.addEventListener('DOMContentLoaded', function () {
    var trigger = document.getElementById('pcSettingsTrigger');
    var panel = document.getElementById('pcSettingsPanel');
    var closeBtn = document.getElementById('pcSettingsClose');
    var langTrigger = document.getElementById('pcLangTrigger');
    var langListbox = document.getElementById('pcLangListbox');
    var opacitySlider = document.getElementById('pcOpacitySlider');
    var widgetOpacitySlider = document.getElementById('pcWidgetOpacitySlider');
    var visionTrigger = document.getElementById('pcVisionTrigger');
    var visionListbox = document.getElementById('pcVisionListbox');

    if (!trigger || !panel) return;

    var prefs = getPreferences();
    applyBackgroundOpacity(prefs.opacity);
    applyWidgetOpacity(prefs.widgetOpacity);
    applyLangSelection(prefs.lang);
    applyVisionSelection(prefs.colorVision);

    function openPanel() {
      panel.hidden = false;
      trigger.setAttribute('aria-expanded', 'true');
    }
    function closeAllDropdowns() {
      if (langListbox && !langListbox.hidden) {
        langListbox.hidden = true;
        langTrigger.setAttribute('aria-expanded', 'false');
      }
      if (visionListbox && !visionListbox.hidden) {
        visionListbox.hidden = true;
        visionTrigger.setAttribute('aria-expanded', 'false');
      }
    }
    function closePanel() {
      panel.hidden = true;
      trigger.setAttribute('aria-expanded', 'false');
      closeAllDropdowns();
    }

    trigger.addEventListener('click', function () {
      panel.hidden ? openPanel() : closePanel();
    });
    if (closeBtn) closeBtn.addEventListener('click', closePanel);

    document.addEventListener('click', function (e) {
      if (!e.target.closest('#pcSettingsWidget')) closePanel();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closePanel();
    });

    // Language dropdown: open/close + selection.
    if (langTrigger && langListbox) {
      langTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !langListbox.hidden;
        langListbox.hidden = isOpen;
        langTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      langListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var code = opt.getAttribute('data-lang');
        setPreferences({ lang: code });
        applyLangSelection(code);
        closeAllDropdowns();
        navigateToLang(code);
      });
      // Keyboard support: ArrowUp/ArrowDown move focus between options,
      // Enter/Space activates the focused option.
      langListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(langListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          langTrigger.focus();
        }
      });
    }

    // Color Vision dropdown: same open/close/selection/keyboard pattern as
    // the language dropdown above -- unlike language, selecting an option
    // here does NOT navigate anywhere, it just applies immediately.
    if (visionTrigger && visionListbox) {
      visionTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !visionListbox.hidden;
        visionListbox.hidden = isOpen;
        visionTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      visionListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var vision = opt.getAttribute('data-vision');
        setPreferences({ colorVision: vision });
        applyVisionSelection(vision);
        closeAllDropdowns();
      });
      visionListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(visionListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          visionTrigger.focus();
        }
      });
    }

    // Both opacity sliders -- native <input type="range"> already provides
    // full keyboard support (Arrow keys, Home/End, PageUp/PageDown); role
    // and aria-value* attributes are set explicitly per this task's spec.
    if (opacitySlider) {
      opacitySlider.addEventListener('input', function () {
        var value = Number(opacitySlider.value);
        applyBackgroundOpacity(value);
        setPreferences({ opacity: value });
      });
    }
    if (widgetOpacitySlider) {
      widgetOpacitySlider.addEventListener('input', function () {
        var value = Number(widgetOpacitySlider.value);
        applyWidgetOpacity(value);
        setPreferences({ widgetOpacity: value });
      });
    }

    // Native `storage` event -- fires in OTHER tabs/windows on this origin
    // when `preferences` changes there, and vice versa. This is the
    // cross-tab sync mechanism this task requires; no polling needed.
    window.addEventListener('storage', function (e) {
      if (e.key !== PREFS_KEY) return;
      var updated = getPreferences();
      applyBackgroundOpacity(updated.opacity);
      applyWidgetOpacity(updated.widgetOpacity);
      applyLangSelection(updated.lang);
      applyVisionSelection(updated.colorVision);
    });
  });
})();
// TASK-9 END
</script>
</body>
```
<!-- TASK-10 END -->

### `pet-detail.html`  →  live path `pets/detail/index.html`

<!-- TASK-10 START -->
**Change 1 of 2 — View Transitions style block, before `</head>`**

**Old code:**
```html
  </head>
```
**New code:**
```html
<!-- TASK-9 START: cross-document View Transitions API, enabled sitewide.
     Animates same-origin full-page navigations (e.g. the Settings Widget's
     language switcher navigating between /, /es/, /pt/, /fr/, /ar/, /zh/)
     in browsers that support the cross-document View Transitions API
     (Chromium 126+). Browsers without support ignore this at-rule and
     navigate normally with no error -- this is a progressive enhancement,
     not a dependency. -->
<style>
@view-transition {
  navigation: auto;
}
</style>
<!-- TASK-9 END -->
  </head>
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Change 2 of 2 — Settings Widget (style + markup + script), before `</body>`**

**Old code:**
```html
</body>
```
**New code:**
```html
<!-- TASK-9 START: Settings Widget -- gear-icon trigger + panel housing the
     sitewide language switcher and the personal widget-background-opacity
     slider. Defines the shared `preferences` object (localStorage key
     "preferences", fields {lang, opacity}) that this page's own
     lang-redirect.js / i18n-core.js (Task 1) already read `preferences.lang`
     from, and that Admin-Project #4's `preferred_chat_language` field also
     reads independently -- the exact field name/shape/values documented in
     this task must not change without notifying that project first.
     Z-INDEX NOTE: the site's real, documented highest z-index anywhere is
     6001 (the filter/form bottom-sheet system), not the floating contact
     button's 4000 -- this widget's trigger/panel/dropdown are placed at
     6100/6200/6300 so they always sit above every existing floating
     element, including both of those. -->
<style>
/* Renamed from --pc-widget-opacity (added in this task's first pass) to
   --pc-widget-bg-opacity, and a second, distinct --pc-widget-opacity
   variable added for the whole widget's own opacity -- per user follow-up,
   "Widget Background Opacity" (panel's rgba background only, children
   stay fully legible) and "Widget Opacity" (the entire trigger + panel,
   via real CSS `opacity` on the container) are two separate controls. */
:root { --pc-widget-bg-opacity: 0.90; --pc-widget-opacity: 1; }

/* "Central position" = vertically centered on the viewport's right edge.
   User follow-up moved this from the left edge (this task's original
   build) to the right edge -- everything else about the placement logic
   from that first pass still applies: vertically centered avoids the
   hero's own CTA buttons (the collision found and fixed when this was
   briefly bottom-center), and vertical centering keeps clear of the
   bottom-right floating contact button (z-index 4000), re-confirmed by
   Playwright screenshot below since moving to the same edge as that
   button is exactly the kind of collision worth re-checking, not
   assuming safe by reasoning alone. */
.pc-settings-widget {
  position: fixed;
  top: 50%;
  right: 16px;
  transform: translateY(-50%);
  z-index: 6100;
  font-family: 'Sora', sans-serif;
  /* Whole-widget opacity (trigger + panel together), separate from the
     panel-only rgba background opacity below. Real CSS `opacity`, by
     design, fades everything inside -- that's the intended effect of this
     specific control, unlike the background one. */
  opacity: var(--pc-widget-opacity);
  transition: opacity 0.2s ease;
}

.pc-settings-trigger {
  width: 52px; height: 52px; border-radius: 50%; border: none; cursor: pointer;
  background: #10214b; color: #d7bd88;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 16px rgba(16,33,75,0.35);
  transition: transform 0.3s cubic-bezier(.4,0,.2,1), background 0.3s cubic-bezier(.4,0,.2,1);
}
.pc-settings-trigger:hover { transform: rotate(35deg); background: #1a2f5c; }
.pc-settings-trigger[aria-expanded="true"] { background: #ED7624; color: #fff; }

.pc-settings-panel {
  position: absolute;
  top: 50%;
  /* Opens to the LEFT of the trigger now that the trigger sits on the
     right edge (was `left: calc(100% + 12px)` opening rightward off a
     left-edge trigger). */
  right: calc(100% + 12px);
  transform: translateY(-50%);
  width: 280px;
  max-width: calc(100vw - 32px);
  /* rgba() background driven by the Widget Background Opacity slider --
     never container `opacity`, which would also fade the panel's
     text/children (Task 9's explicit requirement: "rgba() backgrounds,
     not container opacity"). The separate whole-widget opacity control
     lives on .pc-settings-widget above. */
  background: rgba(16, 33, 75, var(--pc-widget-bg-opacity));
  color: #f2f2f2;
  border-radius: 14px;
  padding: 16px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.3);
  z-index: 6200;
}
.pc-settings-panel[hidden] { display: none; }

.pc-settings-panel__header {
  display: flex; align-items: center; justify-content: space-between;
  font-weight: 700; margin-bottom: 12px; font-size: 15px;
}
.pc-settings-panel__close {
  background: none; border: none; color: inherit; font-size: 20px; line-height: 1;
  cursor: pointer; padding: 2px 6px;
}

.pc-settings-row { margin-bottom: 14px; }
.pc-settings-row:last-child { margin-bottom: 0; }
.pc-settings-label { display: block; font-size: 12.5px; margin-bottom: 6px; color: #d7bd88; }
/* One-line description under a consequential setting, per Design System
   Section 19's "Settings Widget Control Pattern -- Toggle + Description"
   rule (pair every consequential control with a plain-language line
   explaining its effect, since a user shouldn't have to guess). */
.pc-settings-desc { font-size: 11.5px; color: #a39b91; margin: -2px 0 8px; line-height: 1.4; }

.pc-lang-dropdown { position: relative; }
.pc-lang-dropdown__trigger {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.16);
  color: #f2f2f2; border-radius: 8px; padding: 8px 12px; font-size: 13.5px; cursor: pointer;
  font-family: 'Sora', sans-serif;
}
.pc-lang-dropdown__panel {
  position: absolute; top: calc(100% + 6px); left: 0; right: 0;
  background: #10214b; border: 1px solid rgba(255,255,255,0.14);
  /* Sharp corner nearest the trigger (top-left here, since the trigger
     sits above this downward-opening panel) -- the "message bubble" corner
     rule from PETCENTER_DESIGN_SYSTEM_v1_0.md Section 24 (v1.25). */
  border-radius: 0 8px 8px 8px;
  list-style: none; margin: 0; padding: 4px; max-height: 200px; overflow-y: auto;
  z-index: 6300;
}
.pc-lang-dropdown__panel[hidden] { display: none; }
.pc-lang-dropdown__panel li { padding: 8px 10px; font-size: 13.5px; border-radius: 6px; cursor: pointer; }
.pc-lang-dropdown__panel li:hover,
.pc-lang-dropdown__panel li[aria-selected="true"] { background: rgba(215,189,136,0.18); }

.pc-opacity-slider { width: 100%; accent-color: #d7bd88; }
.pc-opacity-value { font-size: 12px; color: #a39b91; float: right; margin-top: 2px; }

@media (max-width: 600px) {
  /* Below 600px, opening the panel to the right of a left-edge trigger
     would push a 280px-wide panel off the right edge of the viewport on
     most phones. Anchor the panel to open downward from the trigger
     instead, still clear of both the hero content and the bottom-right
     contact button. Anchored via `right: 0` (not `left: 0`) since the
     trigger itself now sits near the right edge -- `left: 0` would push
     a near-full-width panel further right and off-screen. */
  .pc-settings-panel {
    top: calc(100% + 10px);
    right: 0;
    left: auto;
    transform: none;
    width: calc(100vw - 48px);
  }
}
</style>

<div class="pc-settings-widget" id="pcSettingsWidget">
  <button type="button" class="pc-settings-trigger" id="pcSettingsTrigger"
          aria-haspopup="true" aria-expanded="false" aria-controls="pcSettingsPanel"
          aria-label="Open site settings">
    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <circle cx="12" cy="12" r="3"></circle>
      <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
    </svg>
  </button>

  <div class="pc-settings-panel" id="pcSettingsPanel" role="dialog" aria-modal="false"
       aria-label="Site settings" hidden="">
    <div class="pc-settings-panel__header">
      <span data-i18n="common.settingsWidget.1">Settings</span>
      <button type="button" class="pc-settings-panel__close" id="pcSettingsClose"
              aria-label="Close settings">&times;</button>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcLangLabel" data-i18n="common.settingsWidget.2">Language</label>
      <div class="pc-lang-dropdown" id="pcLangDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcLangTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcLangLabel"
                aria-controls="pcLangListbox">
          <span id="pcLangCurrentLabel">English</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcLangListbox" role="listbox"
            aria-labelledby="pcLangLabel" tabindex="-1" hidden="">
          <li role="option" data-lang="en" aria-selected="true" tabindex="0">English</li>
          <li role="option" data-lang="es" aria-selected="false" tabindex="0">Español</li>
          <li role="option" data-lang="pt" aria-selected="false" tabindex="0">Português</li>
          <li role="option" data-lang="fr" aria-selected="false" tabindex="0">Français</li>
          <li role="option" data-lang="ar" aria-selected="false" tabindex="0">العربية</li>
          <li role="option" data-lang="zh-Hans" aria-selected="false" tabindex="0">简体中文</li>
        </ul>
      </div>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcOpacitySlider" id="pcOpacityLabel" data-i18n="common.settingsWidget.3">Widget Background Opacity</label>
      <input type="range" id="pcOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="90"
             role="slider" aria-labelledby="pcOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="90" aria-valuetext="90%">
      <span class="pc-opacity-value" id="pcOpacityValue" aria-hidden="true">90%</span>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcWidgetOpacitySlider" id="pcWidgetOpacityLabel" data-i18n="common.settingsWidget.4">Widget Opacity</label>
      <input type="range" id="pcWidgetOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="100"
             role="slider" aria-labelledby="pcWidgetOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="100" aria-valuetext="100%">
      <span class="pc-opacity-value" id="pcWidgetOpacityValue" aria-hidden="true">100%</span>
    </div>

    <!-- TASK (Color Vision Accessibility) START: brief calls for this
         control to sit "alongside the existing dark mode toggle" -- no dark
         mode toggle exists anywhere in this codebase yet (Design System
         Section 19 documents its intended look/behavior but confirms it
         is not yet built; searched before assuming otherwise). Not adding
         a placeholder/stub toggle for a feature that hasn't been
         specified as buildable here -- flagging the gap in this task's
         completion file instead, per "flag it rather than inventing." -->
    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcVisionLabel" data-i18n="common.settingsWidget.5">Color Vision</label>
      <p class="pc-settings-desc" id="pcVisionDesc" data-i18n="common.settingsWidget.6">Adjusts status colors, alerts, and charts sitewide for your selected vision type.</p>
      <div class="pc-lang-dropdown" id="pcVisionDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcVisionTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcVisionLabel"
                aria-controls="pcVisionListbox">
          <span id="pcVisionCurrentLabel">Normal Vision</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcVisionListbox" role="listbox"
            aria-labelledby="pcVisionLabel" tabindex="-1" hidden="">
          <li role="option" data-vision="normal" aria-selected="true" tabindex="0">Normal Vision</li>
          <li role="option" data-vision="deuteranopia" aria-selected="false" tabindex="0">Deuteranopia</li>
          <li role="option" data-vision="protanopia" aria-selected="false" tabindex="0">Protanopia</li>
          <li role="option" data-vision="tritanopia" aria-selected="false" tabindex="0">Tritanopia</li>
        </ul>
      </div>
    </div>
    <!-- TASK (Color Vision Accessibility) END -->
  </div>
</div>

<script>
// TASK-9 START: Settings Widget logic + `preferences` schema owner.
(function () {
  'use strict';

  var PREFS_KEY = 'preferences';
  // Written by admin-dashboard.html's "Default Widget Background Opacity"
  // and "Default Widget Opacity" controls (this task's admin-side
  // deliverable). Read here only as a fallback default for a visitor with
  // no personal preference saved yet -- a visitor's own saved choice in
  // `preferences.opacity` / `preferences.widgetOpacity` always wins.
  var ADMIN_DEFAULTS_KEY = 'pc_admin_settings_defaults';

  // LANG_FOLDER_MAP mirrors Task 1's /en/-/zh/ folder scaffolding -- English
  // is the un-prefixed root (never a redirect target), the rest are their
  // own language-code folders. 'zh-Hans' (tagged, not bare 'zh') is the
  // CEO-confirmed value for Chinese everywhere this schema is used.
  var LANG_FOLDER_MAP = { en: '/', es: '/es/', pt: '/pt/', fr: '/fr/', ar: '/ar/', 'zh-Hans': '/zh/' };
  var LANG_LABELS = { en: 'English', es: 'Español', pt: 'Português', fr: 'Français', ar: 'العربية', 'zh-Hans': '简体中文' };
  var VISION_LABELS = { normal: 'Normal Vision', deuteranopia: 'Deuteranopia', protanopia: 'Protanopia', tritanopia: 'Tritanopia' };

  function readAdminDefaultOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 90;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.opacity);
      return (val >= 20 && val <= 100) ? val : 90;
    } catch (e) { return 90; }
  }

  function readAdminDefaultWidgetOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 100;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.widgetOpacity);
      return (val >= 20 && val <= 100) ? val : 100;
    } catch (e) { return 100; }
  }

  function getPreferences() {
    try {
      var raw = localStorage.getItem(PREFS_KEY);
      var parsed = raw ? JSON.parse(raw) : {};
      return {
        lang: (typeof parsed.lang === 'string' && parsed.lang) ? parsed.lang : 'en',
        opacity: (typeof parsed.opacity === 'number') ? parsed.opacity : readAdminDefaultOpacity(),
        widgetOpacity: (typeof parsed.widgetOpacity === 'number') ? parsed.widgetOpacity : readAdminDefaultWidgetOpacity(),
        colorVision: (typeof parsed.colorVision === 'string' && VISION_LABELS[parsed.colorVision]) ? parsed.colorVision : 'normal'
      };
    } catch (e) {
      return { lang: 'en', opacity: readAdminDefaultOpacity(), widgetOpacity: readAdminDefaultWidgetOpacity(), colorVision: 'normal' };
    }
  }

  function setPreferences(partial) {
    var current = getPreferences();
    var next = { lang: current.lang, opacity: current.opacity, widgetOpacity: current.widgetOpacity, colorVision: current.colorVision };
    for (var k in partial) { if (Object.prototype.hasOwnProperty.call(partial, k)) next[k] = partial[k]; }
    try { localStorage.setItem(PREFS_KEY, JSON.stringify(next)); } catch (e) { /* storage unavailable -- fail silently, in-memory state still applies for this tab */ }
    return next;
  }

  // Panel-only rgba() background opacity -- children stay fully legible
  // regardless of this value.
  function applyBackgroundOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcOpacitySlider');
    var valueEl = document.getElementById('pcOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  // Whole-widget opacity -- real CSS `opacity` on .pc-settings-widget,
  // fades the trigger button and everything inside the panel together.
  function applyWidgetOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcWidgetOpacitySlider');
    var valueEl = document.getElementById('pcWidgetOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  function applyLangSelection(code) {
    var currentLabelEl = document.getElementById('pcLangCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = LANG_LABELS[code] || 'English';
    var options = document.querySelectorAll('#pcLangListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-lang') === code ? 'true' : 'false');
    });
  }

  // Sets the `data-vision` attribute on <html>, which every
  // [data-vision="..."] CSS override block above (and in admin-dashboard.html)
  // keys off of. "normal" matches no override rule, so it's a no-op that
  // simply leaves the default colors in place -- no separate "clear" branch
  // needed.
  function applyVisionSelection(vision) {
    document.documentElement.setAttribute('data-vision', vision);
    var currentLabelEl = document.getElementById('pcVisionCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = VISION_LABELS[vision] || 'Normal Vision';
    var options = document.querySelectorAll('#pcVisionListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-vision') === vision ? 'true' : 'false');
    });
  }

  function navigateToLang(code) {
    var target = LANG_FOLDER_MAP[code] || '/';
    // Cross-document View Transitions (enabled sitewide via the
    // `@view-transition { navigation: auto; }` at-rule in <head>) animate
    // this navigation automatically in supporting browsers -- a plain
    // same-origin navigation is all that's needed to trigger it.
    window.location.href = target;
  }

  // Apply stored/default preferences immediately (before DOMContentLoaded)
  // so both CSS variables are correct before first paint -- avoids a flash
  // of the defaults on a return visitor with different saved values. The
  // `data-vision` attribute is likewise set here, before first paint, so
  // there's no flash of un-adjusted colors either.
  (function initFromStoredPreferencesEarly() {
    var prefs = getPreferences();
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (prefs.opacity / 100).toFixed(2));
    document.documentElement.style.setProperty('--pc-widget-opacity', (prefs.widgetOpacity / 100).toFixed(2));
    document.documentElement.setAttribute('data-vision', prefs.colorVision);
  })();

  document.addEventListener('DOMContentLoaded', function () {
    var trigger = document.getElementById('pcSettingsTrigger');
    var panel = document.getElementById('pcSettingsPanel');
    var closeBtn = document.getElementById('pcSettingsClose');
    var langTrigger = document.getElementById('pcLangTrigger');
    var langListbox = document.getElementById('pcLangListbox');
    var opacitySlider = document.getElementById('pcOpacitySlider');
    var widgetOpacitySlider = document.getElementById('pcWidgetOpacitySlider');
    var visionTrigger = document.getElementById('pcVisionTrigger');
    var visionListbox = document.getElementById('pcVisionListbox');

    if (!trigger || !panel) return;

    var prefs = getPreferences();
    applyBackgroundOpacity(prefs.opacity);
    applyWidgetOpacity(prefs.widgetOpacity);
    applyLangSelection(prefs.lang);
    applyVisionSelection(prefs.colorVision);

    function openPanel() {
      panel.hidden = false;
      trigger.setAttribute('aria-expanded', 'true');
    }
    function closeAllDropdowns() {
      if (langListbox && !langListbox.hidden) {
        langListbox.hidden = true;
        langTrigger.setAttribute('aria-expanded', 'false');
      }
      if (visionListbox && !visionListbox.hidden) {
        visionListbox.hidden = true;
        visionTrigger.setAttribute('aria-expanded', 'false');
      }
    }
    function closePanel() {
      panel.hidden = true;
      trigger.setAttribute('aria-expanded', 'false');
      closeAllDropdowns();
    }

    trigger.addEventListener('click', function () {
      panel.hidden ? openPanel() : closePanel();
    });
    if (closeBtn) closeBtn.addEventListener('click', closePanel);

    document.addEventListener('click', function (e) {
      if (!e.target.closest('#pcSettingsWidget')) closePanel();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closePanel();
    });

    // Language dropdown: open/close + selection.
    if (langTrigger && langListbox) {
      langTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !langListbox.hidden;
        langListbox.hidden = isOpen;
        langTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      langListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var code = opt.getAttribute('data-lang');
        setPreferences({ lang: code });
        applyLangSelection(code);
        closeAllDropdowns();
        navigateToLang(code);
      });
      // Keyboard support: ArrowUp/ArrowDown move focus between options,
      // Enter/Space activates the focused option.
      langListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(langListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          langTrigger.focus();
        }
      });
    }

    // Color Vision dropdown: same open/close/selection/keyboard pattern as
    // the language dropdown above -- unlike language, selecting an option
    // here does NOT navigate anywhere, it just applies immediately.
    if (visionTrigger && visionListbox) {
      visionTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !visionListbox.hidden;
        visionListbox.hidden = isOpen;
        visionTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      visionListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var vision = opt.getAttribute('data-vision');
        setPreferences({ colorVision: vision });
        applyVisionSelection(vision);
        closeAllDropdowns();
      });
      visionListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(visionListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          visionTrigger.focus();
        }
      });
    }

    // Both opacity sliders -- native <input type="range"> already provides
    // full keyboard support (Arrow keys, Home/End, PageUp/PageDown); role
    // and aria-value* attributes are set explicitly per this task's spec.
    if (opacitySlider) {
      opacitySlider.addEventListener('input', function () {
        var value = Number(opacitySlider.value);
        applyBackgroundOpacity(value);
        setPreferences({ opacity: value });
      });
    }
    if (widgetOpacitySlider) {
      widgetOpacitySlider.addEventListener('input', function () {
        var value = Number(widgetOpacitySlider.value);
        applyWidgetOpacity(value);
        setPreferences({ widgetOpacity: value });
      });
    }

    // Native `storage` event -- fires in OTHER tabs/windows on this origin
    // when `preferences` changes there, and vice versa. This is the
    // cross-tab sync mechanism this task requires; no polling needed.
    window.addEventListener('storage', function (e) {
      if (e.key !== PREFS_KEY) return;
      var updated = getPreferences();
      applyBackgroundOpacity(updated.opacity);
      applyWidgetOpacity(updated.widgetOpacity);
      applyLangSelection(updated.lang);
      applyVisionSelection(updated.colorVision);
    });
  });
})();
// TASK-9 END
</script>
</body>
```
<!-- TASK-10 END -->

### `testimonials.html`  →  live path `testimonials/index.html`

<!-- TASK-10 START -->
**Change 1 of 2 — View Transitions style block, before `</head>`**

**Old code:**
```html
</head>
```
**New code:**
```html
<!-- TASK-9 START: cross-document View Transitions API, enabled sitewide.
     Animates same-origin full-page navigations (e.g. the Settings Widget's
     language switcher navigating between /, /es/, /pt/, /fr/, /ar/, /zh/)
     in browsers that support the cross-document View Transitions API
     (Chromium 126+). Browsers without support ignore this at-rule and
     navigate normally with no error -- this is a progressive enhancement,
     not a dependency. -->
<style>
@view-transition {
  navigation: auto;
}
</style>
<!-- TASK-9 END -->
</head>
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Change 2 of 2 — Settings Widget (style + markup + script), before `</body>`**

**Old code:**
```html
</body>
```
**New code:**
```html
<!-- TASK-9 START: Settings Widget -- gear-icon trigger + panel housing the
     sitewide language switcher and the personal widget-background-opacity
     slider. Defines the shared `preferences` object (localStorage key
     "preferences", fields {lang, opacity}) that this page's own
     lang-redirect.js / i18n-core.js (Task 1) already read `preferences.lang`
     from, and that Admin-Project #4's `preferred_chat_language` field also
     reads independently -- the exact field name/shape/values documented in
     this task must not change without notifying that project first.
     Z-INDEX NOTE: the site's real, documented highest z-index anywhere is
     6001 (the filter/form bottom-sheet system), not the floating contact
     button's 4000 -- this widget's trigger/panel/dropdown are placed at
     6100/6200/6300 so they always sit above every existing floating
     element, including both of those. -->
<style>
/* Renamed from --pc-widget-opacity (added in this task's first pass) to
   --pc-widget-bg-opacity, and a second, distinct --pc-widget-opacity
   variable added for the whole widget's own opacity -- per user follow-up,
   "Widget Background Opacity" (panel's rgba background only, children
   stay fully legible) and "Widget Opacity" (the entire trigger + panel,
   via real CSS `opacity` on the container) are two separate controls. */
:root { --pc-widget-bg-opacity: 0.90; --pc-widget-opacity: 1; }

/* "Central position" = vertically centered on the viewport's right edge.
   User follow-up moved this from the left edge (this task's original
   build) to the right edge -- everything else about the placement logic
   from that first pass still applies: vertically centered avoids the
   hero's own CTA buttons (the collision found and fixed when this was
   briefly bottom-center), and vertical centering keeps clear of the
   bottom-right floating contact button (z-index 4000), re-confirmed by
   Playwright screenshot below since moving to the same edge as that
   button is exactly the kind of collision worth re-checking, not
   assuming safe by reasoning alone. */
.pc-settings-widget {
  position: fixed;
  top: 50%;
  right: 16px;
  transform: translateY(-50%);
  z-index: 6100;
  font-family: 'Sora', sans-serif;
  /* Whole-widget opacity (trigger + panel together), separate from the
     panel-only rgba background opacity below. Real CSS `opacity`, by
     design, fades everything inside -- that's the intended effect of this
     specific control, unlike the background one. */
  opacity: var(--pc-widget-opacity);
  transition: opacity 0.2s ease;
}

.pc-settings-trigger {
  width: 52px; height: 52px; border-radius: 50%; border: none; cursor: pointer;
  background: #10214b; color: #d7bd88;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 16px rgba(16,33,75,0.35);
  transition: transform 0.3s cubic-bezier(.4,0,.2,1), background 0.3s cubic-bezier(.4,0,.2,1);
}
.pc-settings-trigger:hover { transform: rotate(35deg); background: #1a2f5c; }
.pc-settings-trigger[aria-expanded="true"] { background: #ED7624; color: #fff; }

.pc-settings-panel {
  position: absolute;
  top: 50%;
  /* Opens to the LEFT of the trigger now that the trigger sits on the
     right edge (was `left: calc(100% + 12px)` opening rightward off a
     left-edge trigger). */
  right: calc(100% + 12px);
  transform: translateY(-50%);
  width: 280px;
  max-width: calc(100vw - 32px);
  /* rgba() background driven by the Widget Background Opacity slider --
     never container `opacity`, which would also fade the panel's
     text/children (Task 9's explicit requirement: "rgba() backgrounds,
     not container opacity"). The separate whole-widget opacity control
     lives on .pc-settings-widget above. */
  background: rgba(16, 33, 75, var(--pc-widget-bg-opacity));
  color: #f2f2f2;
  border-radius: 14px;
  padding: 16px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.3);
  z-index: 6200;
}
.pc-settings-panel[hidden] { display: none; }

.pc-settings-panel__header {
  display: flex; align-items: center; justify-content: space-between;
  font-weight: 700; margin-bottom: 12px; font-size: 15px;
}
.pc-settings-panel__close {
  background: none; border: none; color: inherit; font-size: 20px; line-height: 1;
  cursor: pointer; padding: 2px 6px;
}

.pc-settings-row { margin-bottom: 14px; }
.pc-settings-row:last-child { margin-bottom: 0; }
.pc-settings-label { display: block; font-size: 12.5px; margin-bottom: 6px; color: #d7bd88; }
/* One-line description under a consequential setting, per Design System
   Section 19's "Settings Widget Control Pattern -- Toggle + Description"
   rule (pair every consequential control with a plain-language line
   explaining its effect, since a user shouldn't have to guess). */
.pc-settings-desc { font-size: 11.5px; color: #a39b91; margin: -2px 0 8px; line-height: 1.4; }

.pc-lang-dropdown { position: relative; }
.pc-lang-dropdown__trigger {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.16);
  color: #f2f2f2; border-radius: 8px; padding: 8px 12px; font-size: 13.5px; cursor: pointer;
  font-family: 'Sora', sans-serif;
}
.pc-lang-dropdown__panel {
  position: absolute; top: calc(100% + 6px); left: 0; right: 0;
  background: #10214b; border: 1px solid rgba(255,255,255,0.14);
  /* Sharp corner nearest the trigger (top-left here, since the trigger
     sits above this downward-opening panel) -- the "message bubble" corner
     rule from PETCENTER_DESIGN_SYSTEM_v1_0.md Section 24 (v1.25). */
  border-radius: 0 8px 8px 8px;
  list-style: none; margin: 0; padding: 4px; max-height: 200px; overflow-y: auto;
  z-index: 6300;
}
.pc-lang-dropdown__panel[hidden] { display: none; }
.pc-lang-dropdown__panel li { padding: 8px 10px; font-size: 13.5px; border-radius: 6px; cursor: pointer; }
.pc-lang-dropdown__panel li:hover,
.pc-lang-dropdown__panel li[aria-selected="true"] { background: rgba(215,189,136,0.18); }

.pc-opacity-slider { width: 100%; accent-color: #d7bd88; }
.pc-opacity-value { font-size: 12px; color: #a39b91; float: right; margin-top: 2px; }

@media (max-width: 600px) {
  /* Below 600px, opening the panel to the right of a left-edge trigger
     would push a 280px-wide panel off the right edge of the viewport on
     most phones. Anchor the panel to open downward from the trigger
     instead, still clear of both the hero content and the bottom-right
     contact button. Anchored via `right: 0` (not `left: 0`) since the
     trigger itself now sits near the right edge -- `left: 0` would push
     a near-full-width panel further right and off-screen. */
  .pc-settings-panel {
    top: calc(100% + 10px);
    right: 0;
    left: auto;
    transform: none;
    width: calc(100vw - 48px);
  }
}
</style>

<div class="pc-settings-widget" id="pcSettingsWidget">
  <button type="button" class="pc-settings-trigger" id="pcSettingsTrigger"
          aria-haspopup="true" aria-expanded="false" aria-controls="pcSettingsPanel"
          aria-label="Open site settings">
    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <circle cx="12" cy="12" r="3"></circle>
      <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
    </svg>
  </button>

  <div class="pc-settings-panel" id="pcSettingsPanel" role="dialog" aria-modal="false"
       aria-label="Site settings" hidden="">
    <div class="pc-settings-panel__header">
      <span data-i18n="common.settingsWidget.1">Settings</span>
      <button type="button" class="pc-settings-panel__close" id="pcSettingsClose"
              aria-label="Close settings">&times;</button>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcLangLabel" data-i18n="common.settingsWidget.2">Language</label>
      <div class="pc-lang-dropdown" id="pcLangDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcLangTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcLangLabel"
                aria-controls="pcLangListbox">
          <span id="pcLangCurrentLabel">English</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcLangListbox" role="listbox"
            aria-labelledby="pcLangLabel" tabindex="-1" hidden="">
          <li role="option" data-lang="en" aria-selected="true" tabindex="0">English</li>
          <li role="option" data-lang="es" aria-selected="false" tabindex="0">Español</li>
          <li role="option" data-lang="pt" aria-selected="false" tabindex="0">Português</li>
          <li role="option" data-lang="fr" aria-selected="false" tabindex="0">Français</li>
          <li role="option" data-lang="ar" aria-selected="false" tabindex="0">العربية</li>
          <li role="option" data-lang="zh-Hans" aria-selected="false" tabindex="0">简体中文</li>
        </ul>
      </div>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcOpacitySlider" id="pcOpacityLabel" data-i18n="common.settingsWidget.3">Widget Background Opacity</label>
      <input type="range" id="pcOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="90"
             role="slider" aria-labelledby="pcOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="90" aria-valuetext="90%">
      <span class="pc-opacity-value" id="pcOpacityValue" aria-hidden="true">90%</span>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcWidgetOpacitySlider" id="pcWidgetOpacityLabel" data-i18n="common.settingsWidget.4">Widget Opacity</label>
      <input type="range" id="pcWidgetOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="100"
             role="slider" aria-labelledby="pcWidgetOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="100" aria-valuetext="100%">
      <span class="pc-opacity-value" id="pcWidgetOpacityValue" aria-hidden="true">100%</span>
    </div>

    <!-- TASK (Color Vision Accessibility) START: brief calls for this
         control to sit "alongside the existing dark mode toggle" -- no dark
         mode toggle exists anywhere in this codebase yet (Design System
         Section 19 documents its intended look/behavior but confirms it
         is not yet built; searched before assuming otherwise). Not adding
         a placeholder/stub toggle for a feature that hasn't been
         specified as buildable here -- flagging the gap in this task's
         completion file instead, per "flag it rather than inventing." -->
    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcVisionLabel" data-i18n="common.settingsWidget.5">Color Vision</label>
      <p class="pc-settings-desc" id="pcVisionDesc" data-i18n="common.settingsWidget.6">Adjusts status colors, alerts, and charts sitewide for your selected vision type.</p>
      <div class="pc-lang-dropdown" id="pcVisionDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcVisionTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcVisionLabel"
                aria-controls="pcVisionListbox">
          <span id="pcVisionCurrentLabel">Normal Vision</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcVisionListbox" role="listbox"
            aria-labelledby="pcVisionLabel" tabindex="-1" hidden="">
          <li role="option" data-vision="normal" aria-selected="true" tabindex="0">Normal Vision</li>
          <li role="option" data-vision="deuteranopia" aria-selected="false" tabindex="0">Deuteranopia</li>
          <li role="option" data-vision="protanopia" aria-selected="false" tabindex="0">Protanopia</li>
          <li role="option" data-vision="tritanopia" aria-selected="false" tabindex="0">Tritanopia</li>
        </ul>
      </div>
    </div>
    <!-- TASK (Color Vision Accessibility) END -->
  </div>
</div>

<script>
// TASK-9 START: Settings Widget logic + `preferences` schema owner.
(function () {
  'use strict';

  var PREFS_KEY = 'preferences';
  // Written by admin-dashboard.html's "Default Widget Background Opacity"
  // and "Default Widget Opacity" controls (this task's admin-side
  // deliverable). Read here only as a fallback default for a visitor with
  // no personal preference saved yet -- a visitor's own saved choice in
  // `preferences.opacity` / `preferences.widgetOpacity` always wins.
  var ADMIN_DEFAULTS_KEY = 'pc_admin_settings_defaults';

  // LANG_FOLDER_MAP mirrors Task 1's /en/-/zh/ folder scaffolding -- English
  // is the un-prefixed root (never a redirect target), the rest are their
  // own language-code folders. 'zh-Hans' (tagged, not bare 'zh') is the
  // CEO-confirmed value for Chinese everywhere this schema is used.
  var LANG_FOLDER_MAP = { en: '/', es: '/es/', pt: '/pt/', fr: '/fr/', ar: '/ar/', 'zh-Hans': '/zh/' };
  var LANG_LABELS = { en: 'English', es: 'Español', pt: 'Português', fr: 'Français', ar: 'العربية', 'zh-Hans': '简体中文' };
  var VISION_LABELS = { normal: 'Normal Vision', deuteranopia: 'Deuteranopia', protanopia: 'Protanopia', tritanopia: 'Tritanopia' };

  function readAdminDefaultOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 90;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.opacity);
      return (val >= 20 && val <= 100) ? val : 90;
    } catch (e) { return 90; }
  }

  function readAdminDefaultWidgetOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 100;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.widgetOpacity);
      return (val >= 20 && val <= 100) ? val : 100;
    } catch (e) { return 100; }
  }

  function getPreferences() {
    try {
      var raw = localStorage.getItem(PREFS_KEY);
      var parsed = raw ? JSON.parse(raw) : {};
      return {
        lang: (typeof parsed.lang === 'string' && parsed.lang) ? parsed.lang : 'en',
        opacity: (typeof parsed.opacity === 'number') ? parsed.opacity : readAdminDefaultOpacity(),
        widgetOpacity: (typeof parsed.widgetOpacity === 'number') ? parsed.widgetOpacity : readAdminDefaultWidgetOpacity(),
        colorVision: (typeof parsed.colorVision === 'string' && VISION_LABELS[parsed.colorVision]) ? parsed.colorVision : 'normal'
      };
    } catch (e) {
      return { lang: 'en', opacity: readAdminDefaultOpacity(), widgetOpacity: readAdminDefaultWidgetOpacity(), colorVision: 'normal' };
    }
  }

  function setPreferences(partial) {
    var current = getPreferences();
    var next = { lang: current.lang, opacity: current.opacity, widgetOpacity: current.widgetOpacity, colorVision: current.colorVision };
    for (var k in partial) { if (Object.prototype.hasOwnProperty.call(partial, k)) next[k] = partial[k]; }
    try { localStorage.setItem(PREFS_KEY, JSON.stringify(next)); } catch (e) { /* storage unavailable -- fail silently, in-memory state still applies for this tab */ }
    return next;
  }

  // Panel-only rgba() background opacity -- children stay fully legible
  // regardless of this value.
  function applyBackgroundOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcOpacitySlider');
    var valueEl = document.getElementById('pcOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  // Whole-widget opacity -- real CSS `opacity` on .pc-settings-widget,
  // fades the trigger button and everything inside the panel together.
  function applyWidgetOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcWidgetOpacitySlider');
    var valueEl = document.getElementById('pcWidgetOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  function applyLangSelection(code) {
    var currentLabelEl = document.getElementById('pcLangCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = LANG_LABELS[code] || 'English';
    var options = document.querySelectorAll('#pcLangListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-lang') === code ? 'true' : 'false');
    });
  }

  // Sets the `data-vision` attribute on <html>, which every
  // [data-vision="..."] CSS override block above (and in admin-dashboard.html)
  // keys off of. "normal" matches no override rule, so it's a no-op that
  // simply leaves the default colors in place -- no separate "clear" branch
  // needed.
  function applyVisionSelection(vision) {
    document.documentElement.setAttribute('data-vision', vision);
    var currentLabelEl = document.getElementById('pcVisionCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = VISION_LABELS[vision] || 'Normal Vision';
    var options = document.querySelectorAll('#pcVisionListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-vision') === vision ? 'true' : 'false');
    });
  }

  function navigateToLang(code) {
    var target = LANG_FOLDER_MAP[code] || '/';
    // Cross-document View Transitions (enabled sitewide via the
    // `@view-transition { navigation: auto; }` at-rule in <head>) animate
    // this navigation automatically in supporting browsers -- a plain
    // same-origin navigation is all that's needed to trigger it.
    window.location.href = target;
  }

  // Apply stored/default preferences immediately (before DOMContentLoaded)
  // so both CSS variables are correct before first paint -- avoids a flash
  // of the defaults on a return visitor with different saved values. The
  // `data-vision` attribute is likewise set here, before first paint, so
  // there's no flash of un-adjusted colors either.
  (function initFromStoredPreferencesEarly() {
    var prefs = getPreferences();
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (prefs.opacity / 100).toFixed(2));
    document.documentElement.style.setProperty('--pc-widget-opacity', (prefs.widgetOpacity / 100).toFixed(2));
    document.documentElement.setAttribute('data-vision', prefs.colorVision);
  })();

  document.addEventListener('DOMContentLoaded', function () {
    var trigger = document.getElementById('pcSettingsTrigger');
    var panel = document.getElementById('pcSettingsPanel');
    var closeBtn = document.getElementById('pcSettingsClose');
    var langTrigger = document.getElementById('pcLangTrigger');
    var langListbox = document.getElementById('pcLangListbox');
    var opacitySlider = document.getElementById('pcOpacitySlider');
    var widgetOpacitySlider = document.getElementById('pcWidgetOpacitySlider');
    var visionTrigger = document.getElementById('pcVisionTrigger');
    var visionListbox = document.getElementById('pcVisionListbox');

    if (!trigger || !panel) return;

    var prefs = getPreferences();
    applyBackgroundOpacity(prefs.opacity);
    applyWidgetOpacity(prefs.widgetOpacity);
    applyLangSelection(prefs.lang);
    applyVisionSelection(prefs.colorVision);

    function openPanel() {
      panel.hidden = false;
      trigger.setAttribute('aria-expanded', 'true');
    }
    function closeAllDropdowns() {
      if (langListbox && !langListbox.hidden) {
        langListbox.hidden = true;
        langTrigger.setAttribute('aria-expanded', 'false');
      }
      if (visionListbox && !visionListbox.hidden) {
        visionListbox.hidden = true;
        visionTrigger.setAttribute('aria-expanded', 'false');
      }
    }
    function closePanel() {
      panel.hidden = true;
      trigger.setAttribute('aria-expanded', 'false');
      closeAllDropdowns();
    }

    trigger.addEventListener('click', function () {
      panel.hidden ? openPanel() : closePanel();
    });
    if (closeBtn) closeBtn.addEventListener('click', closePanel);

    document.addEventListener('click', function (e) {
      if (!e.target.closest('#pcSettingsWidget')) closePanel();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closePanel();
    });

    // Language dropdown: open/close + selection.
    if (langTrigger && langListbox) {
      langTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !langListbox.hidden;
        langListbox.hidden = isOpen;
        langTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      langListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var code = opt.getAttribute('data-lang');
        setPreferences({ lang: code });
        applyLangSelection(code);
        closeAllDropdowns();
        navigateToLang(code);
      });
      // Keyboard support: ArrowUp/ArrowDown move focus between options,
      // Enter/Space activates the focused option.
      langListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(langListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          langTrigger.focus();
        }
      });
    }

    // Color Vision dropdown: same open/close/selection/keyboard pattern as
    // the language dropdown above -- unlike language, selecting an option
    // here does NOT navigate anywhere, it just applies immediately.
    if (visionTrigger && visionListbox) {
      visionTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !visionListbox.hidden;
        visionListbox.hidden = isOpen;
        visionTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      visionListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var vision = opt.getAttribute('data-vision');
        setPreferences({ colorVision: vision });
        applyVisionSelection(vision);
        closeAllDropdowns();
      });
      visionListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(visionListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          visionTrigger.focus();
        }
      });
    }

    // Both opacity sliders -- native <input type="range"> already provides
    // full keyboard support (Arrow keys, Home/End, PageUp/PageDown); role
    // and aria-value* attributes are set explicitly per this task's spec.
    if (opacitySlider) {
      opacitySlider.addEventListener('input', function () {
        var value = Number(opacitySlider.value);
        applyBackgroundOpacity(value);
        setPreferences({ opacity: value });
      });
    }
    if (widgetOpacitySlider) {
      widgetOpacitySlider.addEventListener('input', function () {
        var value = Number(widgetOpacitySlider.value);
        applyWidgetOpacity(value);
        setPreferences({ widgetOpacity: value });
      });
    }

    // Native `storage` event -- fires in OTHER tabs/windows on this origin
    // when `preferences` changes there, and vice versa. This is the
    // cross-tab sync mechanism this task requires; no polling needed.
    window.addEventListener('storage', function (e) {
      if (e.key !== PREFS_KEY) return;
      var updated = getPreferences();
      applyBackgroundOpacity(updated.opacity);
      applyWidgetOpacity(updated.widgetOpacity);
      applyLangSelection(updated.lang);
      applyVisionSelection(updated.colorVision);
    });
  });
})();
// TASK-9 END
</script>
</body>
```
<!-- TASK-10 END -->

### `adoption-application.html`  →  live path `apply/index.html`

<!-- TASK-10 START -->
**Change 1 of 2 — View Transitions style block, before `</head>`**

**Old code:**
```html
</head>
```
**New code:**
```html
<!-- TASK-9 START: cross-document View Transitions API, enabled sitewide.
     Animates same-origin full-page navigations (e.g. the Settings Widget's
     language switcher navigating between /, /es/, /pt/, /fr/, /ar/, /zh/)
     in browsers that support the cross-document View Transitions API
     (Chromium 126+). Browsers without support ignore this at-rule and
     navigate normally with no error -- this is a progressive enhancement,
     not a dependency. -->
<style>
@view-transition {
  navigation: auto;
}
</style>
<!-- TASK-9 END -->
</head>
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Change 2 of 2 — Settings Widget (style + markup + script), before `</body>`**

**Old code:**
```html
</body>
```
**New code:**
```html
<!-- TASK-9 START: Settings Widget -- gear-icon trigger + panel housing the
     sitewide language switcher and the personal widget-background-opacity
     slider. Defines the shared `preferences` object (localStorage key
     "preferences", fields {lang, opacity}) that this page's own
     lang-redirect.js / i18n-core.js (Task 1) already read `preferences.lang`
     from, and that Admin-Project #4's `preferred_chat_language` field also
     reads independently -- the exact field name/shape/values documented in
     this task must not change without notifying that project first.
     Z-INDEX NOTE: the site's real, documented highest z-index anywhere is
     6001 (the filter/form bottom-sheet system), not the floating contact
     button's 4000 -- this widget's trigger/panel/dropdown are placed at
     6100/6200/6300 so they always sit above every existing floating
     element, including both of those. -->
<style>
/* Renamed from --pc-widget-opacity (added in this task's first pass) to
   --pc-widget-bg-opacity, and a second, distinct --pc-widget-opacity
   variable added for the whole widget's own opacity -- per user follow-up,
   "Widget Background Opacity" (panel's rgba background only, children
   stay fully legible) and "Widget Opacity" (the entire trigger + panel,
   via real CSS `opacity` on the container) are two separate controls. */
:root { --pc-widget-bg-opacity: 0.90; --pc-widget-opacity: 1; }

/* "Central position" = vertically centered on the viewport's right edge.
   User follow-up moved this from the left edge (this task's original
   build) to the right edge -- everything else about the placement logic
   from that first pass still applies: vertically centered avoids the
   hero's own CTA buttons (the collision found and fixed when this was
   briefly bottom-center), and vertical centering keeps clear of the
   bottom-right floating contact button (z-index 4000), re-confirmed by
   Playwright screenshot below since moving to the same edge as that
   button is exactly the kind of collision worth re-checking, not
   assuming safe by reasoning alone. */
.pc-settings-widget {
  position: fixed;
  top: 50%;
  right: 16px;
  transform: translateY(-50%);
  z-index: 6100;
  font-family: 'Sora', sans-serif;
  /* Whole-widget opacity (trigger + panel together), separate from the
     panel-only rgba background opacity below. Real CSS `opacity`, by
     design, fades everything inside -- that's the intended effect of this
     specific control, unlike the background one. */
  opacity: var(--pc-widget-opacity);
  transition: opacity 0.2s ease;
}

.pc-settings-trigger {
  width: 52px; height: 52px; border-radius: 50%; border: none; cursor: pointer;
  background: #10214b; color: #d7bd88;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 16px rgba(16,33,75,0.35);
  transition: transform 0.3s cubic-bezier(.4,0,.2,1), background 0.3s cubic-bezier(.4,0,.2,1);
}
.pc-settings-trigger:hover { transform: rotate(35deg); background: #1a2f5c; }
.pc-settings-trigger[aria-expanded="true"] { background: #ED7624; color: #fff; }

.pc-settings-panel {
  position: absolute;
  top: 50%;
  /* Opens to the LEFT of the trigger now that the trigger sits on the
     right edge (was `left: calc(100% + 12px)` opening rightward off a
     left-edge trigger). */
  right: calc(100% + 12px);
  transform: translateY(-50%);
  width: 280px;
  max-width: calc(100vw - 32px);
  /* rgba() background driven by the Widget Background Opacity slider --
     never container `opacity`, which would also fade the panel's
     text/children (Task 9's explicit requirement: "rgba() backgrounds,
     not container opacity"). The separate whole-widget opacity control
     lives on .pc-settings-widget above. */
  background: rgba(16, 33, 75, var(--pc-widget-bg-opacity));
  color: #f2f2f2;
  border-radius: 14px;
  padding: 16px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.3);
  z-index: 6200;
}
.pc-settings-panel[hidden] { display: none; }

.pc-settings-panel__header {
  display: flex; align-items: center; justify-content: space-between;
  font-weight: 700; margin-bottom: 12px; font-size: 15px;
}
.pc-settings-panel__close {
  background: none; border: none; color: inherit; font-size: 20px; line-height: 1;
  cursor: pointer; padding: 2px 6px;
}

.pc-settings-row { margin-bottom: 14px; }
.pc-settings-row:last-child { margin-bottom: 0; }
.pc-settings-label { display: block; font-size: 12.5px; margin-bottom: 6px; color: #d7bd88; }
/* One-line description under a consequential setting, per Design System
   Section 19's "Settings Widget Control Pattern -- Toggle + Description"
   rule (pair every consequential control with a plain-language line
   explaining its effect, since a user shouldn't have to guess). */
.pc-settings-desc { font-size: 11.5px; color: #a39b91; margin: -2px 0 8px; line-height: 1.4; }

.pc-lang-dropdown { position: relative; }
.pc-lang-dropdown__trigger {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.16);
  color: #f2f2f2; border-radius: 8px; padding: 8px 12px; font-size: 13.5px; cursor: pointer;
  font-family: 'Sora', sans-serif;
}
.pc-lang-dropdown__panel {
  position: absolute; top: calc(100% + 6px); left: 0; right: 0;
  background: #10214b; border: 1px solid rgba(255,255,255,0.14);
  /* Sharp corner nearest the trigger (top-left here, since the trigger
     sits above this downward-opening panel) -- the "message bubble" corner
     rule from PETCENTER_DESIGN_SYSTEM_v1_0.md Section 24 (v1.25). */
  border-radius: 0 8px 8px 8px;
  list-style: none; margin: 0; padding: 4px; max-height: 200px; overflow-y: auto;
  z-index: 6300;
}
.pc-lang-dropdown__panel[hidden] { display: none; }
.pc-lang-dropdown__panel li { padding: 8px 10px; font-size: 13.5px; border-radius: 6px; cursor: pointer; }
.pc-lang-dropdown__panel li:hover,
.pc-lang-dropdown__panel li[aria-selected="true"] { background: rgba(215,189,136,0.18); }

.pc-opacity-slider { width: 100%; accent-color: #d7bd88; }
.pc-opacity-value { font-size: 12px; color: #a39b91; float: right; margin-top: 2px; }

@media (max-width: 600px) {
  /* Below 600px, opening the panel to the right of a left-edge trigger
     would push a 280px-wide panel off the right edge of the viewport on
     most phones. Anchor the panel to open downward from the trigger
     instead, still clear of both the hero content and the bottom-right
     contact button. Anchored via `right: 0` (not `left: 0`) since the
     trigger itself now sits near the right edge -- `left: 0` would push
     a near-full-width panel further right and off-screen. */
  .pc-settings-panel {
    top: calc(100% + 10px);
    right: 0;
    left: auto;
    transform: none;
    width: calc(100vw - 48px);
  }
}
</style>

<div class="pc-settings-widget" id="pcSettingsWidget">
  <button type="button" class="pc-settings-trigger" id="pcSettingsTrigger"
          aria-haspopup="true" aria-expanded="false" aria-controls="pcSettingsPanel"
          aria-label="Open site settings">
    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <circle cx="12" cy="12" r="3"></circle>
      <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
    </svg>
  </button>

  <div class="pc-settings-panel" id="pcSettingsPanel" role="dialog" aria-modal="false"
       aria-label="Site settings" hidden="">
    <div class="pc-settings-panel__header">
      <span data-i18n="common.settingsWidget.1">Settings</span>
      <button type="button" class="pc-settings-panel__close" id="pcSettingsClose"
              aria-label="Close settings">&times;</button>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcLangLabel" data-i18n="common.settingsWidget.2">Language</label>
      <div class="pc-lang-dropdown" id="pcLangDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcLangTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcLangLabel"
                aria-controls="pcLangListbox">
          <span id="pcLangCurrentLabel">English</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcLangListbox" role="listbox"
            aria-labelledby="pcLangLabel" tabindex="-1" hidden="">
          <li role="option" data-lang="en" aria-selected="true" tabindex="0">English</li>
          <li role="option" data-lang="es" aria-selected="false" tabindex="0">Español</li>
          <li role="option" data-lang="pt" aria-selected="false" tabindex="0">Português</li>
          <li role="option" data-lang="fr" aria-selected="false" tabindex="0">Français</li>
          <li role="option" data-lang="ar" aria-selected="false" tabindex="0">العربية</li>
          <li role="option" data-lang="zh-Hans" aria-selected="false" tabindex="0">简体中文</li>
        </ul>
      </div>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcOpacitySlider" id="pcOpacityLabel" data-i18n="common.settingsWidget.3">Widget Background Opacity</label>
      <input type="range" id="pcOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="90"
             role="slider" aria-labelledby="pcOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="90" aria-valuetext="90%">
      <span class="pc-opacity-value" id="pcOpacityValue" aria-hidden="true">90%</span>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcWidgetOpacitySlider" id="pcWidgetOpacityLabel" data-i18n="common.settingsWidget.4">Widget Opacity</label>
      <input type="range" id="pcWidgetOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="100"
             role="slider" aria-labelledby="pcWidgetOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="100" aria-valuetext="100%">
      <span class="pc-opacity-value" id="pcWidgetOpacityValue" aria-hidden="true">100%</span>
    </div>

    <!-- TASK (Color Vision Accessibility) START: brief calls for this
         control to sit "alongside the existing dark mode toggle" -- no dark
         mode toggle exists anywhere in this codebase yet (Design System
         Section 19 documents its intended look/behavior but confirms it
         is not yet built; searched before assuming otherwise). Not adding
         a placeholder/stub toggle for a feature that hasn't been
         specified as buildable here -- flagging the gap in this task's
         completion file instead, per "flag it rather than inventing." -->
    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcVisionLabel" data-i18n="common.settingsWidget.5">Color Vision</label>
      <p class="pc-settings-desc" id="pcVisionDesc" data-i18n="common.settingsWidget.6">Adjusts status colors, alerts, and charts sitewide for your selected vision type.</p>
      <div class="pc-lang-dropdown" id="pcVisionDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcVisionTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcVisionLabel"
                aria-controls="pcVisionListbox">
          <span id="pcVisionCurrentLabel">Normal Vision</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcVisionListbox" role="listbox"
            aria-labelledby="pcVisionLabel" tabindex="-1" hidden="">
          <li role="option" data-vision="normal" aria-selected="true" tabindex="0">Normal Vision</li>
          <li role="option" data-vision="deuteranopia" aria-selected="false" tabindex="0">Deuteranopia</li>
          <li role="option" data-vision="protanopia" aria-selected="false" tabindex="0">Protanopia</li>
          <li role="option" data-vision="tritanopia" aria-selected="false" tabindex="0">Tritanopia</li>
        </ul>
      </div>
    </div>
    <!-- TASK (Color Vision Accessibility) END -->
  </div>
</div>

<script>
// TASK-9 START: Settings Widget logic + `preferences` schema owner.
(function () {
  'use strict';

  var PREFS_KEY = 'preferences';
  // Written by admin-dashboard.html's "Default Widget Background Opacity"
  // and "Default Widget Opacity" controls (this task's admin-side
  // deliverable). Read here only as a fallback default for a visitor with
  // no personal preference saved yet -- a visitor's own saved choice in
  // `preferences.opacity` / `preferences.widgetOpacity` always wins.
  var ADMIN_DEFAULTS_KEY = 'pc_admin_settings_defaults';

  // LANG_FOLDER_MAP mirrors Task 1's /en/-/zh/ folder scaffolding -- English
  // is the un-prefixed root (never a redirect target), the rest are their
  // own language-code folders. 'zh-Hans' (tagged, not bare 'zh') is the
  // CEO-confirmed value for Chinese everywhere this schema is used.
  var LANG_FOLDER_MAP = { en: '/', es: '/es/', pt: '/pt/', fr: '/fr/', ar: '/ar/', 'zh-Hans': '/zh/' };
  var LANG_LABELS = { en: 'English', es: 'Español', pt: 'Português', fr: 'Français', ar: 'العربية', 'zh-Hans': '简体中文' };
  var VISION_LABELS = { normal: 'Normal Vision', deuteranopia: 'Deuteranopia', protanopia: 'Protanopia', tritanopia: 'Tritanopia' };

  function readAdminDefaultOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 90;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.opacity);
      return (val >= 20 && val <= 100) ? val : 90;
    } catch (e) { return 90; }
  }

  function readAdminDefaultWidgetOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 100;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.widgetOpacity);
      return (val >= 20 && val <= 100) ? val : 100;
    } catch (e) { return 100; }
  }

  function getPreferences() {
    try {
      var raw = localStorage.getItem(PREFS_KEY);
      var parsed = raw ? JSON.parse(raw) : {};
      return {
        lang: (typeof parsed.lang === 'string' && parsed.lang) ? parsed.lang : 'en',
        opacity: (typeof parsed.opacity === 'number') ? parsed.opacity : readAdminDefaultOpacity(),
        widgetOpacity: (typeof parsed.widgetOpacity === 'number') ? parsed.widgetOpacity : readAdminDefaultWidgetOpacity(),
        colorVision: (typeof parsed.colorVision === 'string' && VISION_LABELS[parsed.colorVision]) ? parsed.colorVision : 'normal'
      };
    } catch (e) {
      return { lang: 'en', opacity: readAdminDefaultOpacity(), widgetOpacity: readAdminDefaultWidgetOpacity(), colorVision: 'normal' };
    }
  }

  function setPreferences(partial) {
    var current = getPreferences();
    var next = { lang: current.lang, opacity: current.opacity, widgetOpacity: current.widgetOpacity, colorVision: current.colorVision };
    for (var k in partial) { if (Object.prototype.hasOwnProperty.call(partial, k)) next[k] = partial[k]; }
    try { localStorage.setItem(PREFS_KEY, JSON.stringify(next)); } catch (e) { /* storage unavailable -- fail silently, in-memory state still applies for this tab */ }
    return next;
  }

  // Panel-only rgba() background opacity -- children stay fully legible
  // regardless of this value.
  function applyBackgroundOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcOpacitySlider');
    var valueEl = document.getElementById('pcOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  // Whole-widget opacity -- real CSS `opacity` on .pc-settings-widget,
  // fades the trigger button and everything inside the panel together.
  function applyWidgetOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcWidgetOpacitySlider');
    var valueEl = document.getElementById('pcWidgetOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  function applyLangSelection(code) {
    var currentLabelEl = document.getElementById('pcLangCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = LANG_LABELS[code] || 'English';
    var options = document.querySelectorAll('#pcLangListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-lang') === code ? 'true' : 'false');
    });
  }

  // Sets the `data-vision` attribute on <html>, which every
  // [data-vision="..."] CSS override block above (and in admin-dashboard.html)
  // keys off of. "normal" matches no override rule, so it's a no-op that
  // simply leaves the default colors in place -- no separate "clear" branch
  // needed.
  function applyVisionSelection(vision) {
    document.documentElement.setAttribute('data-vision', vision);
    var currentLabelEl = document.getElementById('pcVisionCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = VISION_LABELS[vision] || 'Normal Vision';
    var options = document.querySelectorAll('#pcVisionListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-vision') === vision ? 'true' : 'false');
    });
  }

  function navigateToLang(code) {
    var target = LANG_FOLDER_MAP[code] || '/';
    // Cross-document View Transitions (enabled sitewide via the
    // `@view-transition { navigation: auto; }` at-rule in <head>) animate
    // this navigation automatically in supporting browsers -- a plain
    // same-origin navigation is all that's needed to trigger it.
    window.location.href = target;
  }

  // Apply stored/default preferences immediately (before DOMContentLoaded)
  // so both CSS variables are correct before first paint -- avoids a flash
  // of the defaults on a return visitor with different saved values. The
  // `data-vision` attribute is likewise set here, before first paint, so
  // there's no flash of un-adjusted colors either.
  (function initFromStoredPreferencesEarly() {
    var prefs = getPreferences();
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (prefs.opacity / 100).toFixed(2));
    document.documentElement.style.setProperty('--pc-widget-opacity', (prefs.widgetOpacity / 100).toFixed(2));
    document.documentElement.setAttribute('data-vision', prefs.colorVision);
  })();

  document.addEventListener('DOMContentLoaded', function () {
    var trigger = document.getElementById('pcSettingsTrigger');
    var panel = document.getElementById('pcSettingsPanel');
    var closeBtn = document.getElementById('pcSettingsClose');
    var langTrigger = document.getElementById('pcLangTrigger');
    var langListbox = document.getElementById('pcLangListbox');
    var opacitySlider = document.getElementById('pcOpacitySlider');
    var widgetOpacitySlider = document.getElementById('pcWidgetOpacitySlider');
    var visionTrigger = document.getElementById('pcVisionTrigger');
    var visionListbox = document.getElementById('pcVisionListbox');

    if (!trigger || !panel) return;

    var prefs = getPreferences();
    applyBackgroundOpacity(prefs.opacity);
    applyWidgetOpacity(prefs.widgetOpacity);
    applyLangSelection(prefs.lang);
    applyVisionSelection(prefs.colorVision);

    function openPanel() {
      panel.hidden = false;
      trigger.setAttribute('aria-expanded', 'true');
    }
    function closeAllDropdowns() {
      if (langListbox && !langListbox.hidden) {
        langListbox.hidden = true;
        langTrigger.setAttribute('aria-expanded', 'false');
      }
      if (visionListbox && !visionListbox.hidden) {
        visionListbox.hidden = true;
        visionTrigger.setAttribute('aria-expanded', 'false');
      }
    }
    function closePanel() {
      panel.hidden = true;
      trigger.setAttribute('aria-expanded', 'false');
      closeAllDropdowns();
    }

    trigger.addEventListener('click', function () {
      panel.hidden ? openPanel() : closePanel();
    });
    if (closeBtn) closeBtn.addEventListener('click', closePanel);

    document.addEventListener('click', function (e) {
      if (!e.target.closest('#pcSettingsWidget')) closePanel();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closePanel();
    });

    // Language dropdown: open/close + selection.
    if (langTrigger && langListbox) {
      langTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !langListbox.hidden;
        langListbox.hidden = isOpen;
        langTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      langListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var code = opt.getAttribute('data-lang');
        setPreferences({ lang: code });
        applyLangSelection(code);
        closeAllDropdowns();
        navigateToLang(code);
      });
      // Keyboard support: ArrowUp/ArrowDown move focus between options,
      // Enter/Space activates the focused option.
      langListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(langListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          langTrigger.focus();
        }
      });
    }

    // Color Vision dropdown: same open/close/selection/keyboard pattern as
    // the language dropdown above -- unlike language, selecting an option
    // here does NOT navigate anywhere, it just applies immediately.
    if (visionTrigger && visionListbox) {
      visionTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !visionListbox.hidden;
        visionListbox.hidden = isOpen;
        visionTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      visionListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var vision = opt.getAttribute('data-vision');
        setPreferences({ colorVision: vision });
        applyVisionSelection(vision);
        closeAllDropdowns();
      });
      visionListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(visionListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          visionTrigger.focus();
        }
      });
    }

    // Both opacity sliders -- native <input type="range"> already provides
    // full keyboard support (Arrow keys, Home/End, PageUp/PageDown); role
    // and aria-value* attributes are set explicitly per this task's spec.
    if (opacitySlider) {
      opacitySlider.addEventListener('input', function () {
        var value = Number(opacitySlider.value);
        applyBackgroundOpacity(value);
        setPreferences({ opacity: value });
      });
    }
    if (widgetOpacitySlider) {
      widgetOpacitySlider.addEventListener('input', function () {
        var value = Number(widgetOpacitySlider.value);
        applyWidgetOpacity(value);
        setPreferences({ widgetOpacity: value });
      });
    }

    // Native `storage` event -- fires in OTHER tabs/windows on this origin
    // when `preferences` changes there, and vice versa. This is the
    // cross-tab sync mechanism this task requires; no polling needed.
    window.addEventListener('storage', function (e) {
      if (e.key !== PREFS_KEY) return;
      var updated = getPreferences();
      applyBackgroundOpacity(updated.opacity);
      applyWidgetOpacity(updated.widgetOpacity);
      applyLangSelection(updated.lang);
      applyVisionSelection(updated.colorVision);
    });
  });
})();
// TASK-9 END
</script>
</body>
```
<!-- TASK-10 END -->

### `status.html`  →  live path `adoptions/status/index.html`

<!-- TASK-10 START -->
**Change 1 of 2 — View Transitions style block, before `</head>`**

**Old code:**
```html
</head>
```
**New code:**
```html
<!-- TASK-9 START: cross-document View Transitions API, enabled sitewide.
     Animates same-origin full-page navigations (e.g. the Settings Widget's
     language switcher navigating between /, /es/, /pt/, /fr/, /ar/, /zh/)
     in browsers that support the cross-document View Transitions API
     (Chromium 126+). Browsers without support ignore this at-rule and
     navigate normally with no error -- this is a progressive enhancement,
     not a dependency. -->
<style>
@view-transition {
  navigation: auto;
}
</style>
<!-- TASK-9 END -->
</head>
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Change 2 of 2 — Settings Widget (style + markup + script), before `</body>`**

**Old code:**
```html
</body>
```
**New code:**
```html
<!-- TASK-9 START: Settings Widget -- gear-icon trigger + panel housing the
     sitewide language switcher and the personal widget-background-opacity
     slider. Defines the shared `preferences` object (localStorage key
     "preferences", fields {lang, opacity}) that this page's own
     lang-redirect.js / i18n-core.js (Task 1) already read `preferences.lang`
     from, and that Admin-Project #4's `preferred_chat_language` field also
     reads independently -- the exact field name/shape/values documented in
     this task must not change without notifying that project first.
     Z-INDEX NOTE: the site's real, documented highest z-index anywhere is
     6001 (the filter/form bottom-sheet system), not the floating contact
     button's 4000 -- this widget's trigger/panel/dropdown are placed at
     6100/6200/6300 so they always sit above every existing floating
     element, including both of those. -->
<style>
/* Renamed from --pc-widget-opacity (added in this task's first pass) to
   --pc-widget-bg-opacity, and a second, distinct --pc-widget-opacity
   variable added for the whole widget's own opacity -- per user follow-up,
   "Widget Background Opacity" (panel's rgba background only, children
   stay fully legible) and "Widget Opacity" (the entire trigger + panel,
   via real CSS `opacity` on the container) are two separate controls. */
:root { --pc-widget-bg-opacity: 0.90; --pc-widget-opacity: 1; }

/* "Central position" = vertically centered on the viewport's right edge.
   User follow-up moved this from the left edge (this task's original
   build) to the right edge -- everything else about the placement logic
   from that first pass still applies: vertically centered avoids the
   hero's own CTA buttons (the collision found and fixed when this was
   briefly bottom-center), and vertical centering keeps clear of the
   bottom-right floating contact button (z-index 4000), re-confirmed by
   Playwright screenshot below since moving to the same edge as that
   button is exactly the kind of collision worth re-checking, not
   assuming safe by reasoning alone. */
.pc-settings-widget {
  position: fixed;
  top: 50%;
  right: 16px;
  transform: translateY(-50%);
  z-index: 6100;
  font-family: 'Sora', sans-serif;
  /* Whole-widget opacity (trigger + panel together), separate from the
     panel-only rgba background opacity below. Real CSS `opacity`, by
     design, fades everything inside -- that's the intended effect of this
     specific control, unlike the background one. */
  opacity: var(--pc-widget-opacity);
  transition: opacity 0.2s ease;
}

.pc-settings-trigger {
  width: 52px; height: 52px; border-radius: 50%; border: none; cursor: pointer;
  background: #10214b; color: #d7bd88;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 16px rgba(16,33,75,0.35);
  transition: transform 0.3s cubic-bezier(.4,0,.2,1), background 0.3s cubic-bezier(.4,0,.2,1);
}
.pc-settings-trigger:hover { transform: rotate(35deg); background: #1a2f5c; }
.pc-settings-trigger[aria-expanded="true"] { background: #ED7624; color: #fff; }

.pc-settings-panel {
  position: absolute;
  top: 50%;
  /* Opens to the LEFT of the trigger now that the trigger sits on the
     right edge (was `left: calc(100% + 12px)` opening rightward off a
     left-edge trigger). */
  right: calc(100% + 12px);
  transform: translateY(-50%);
  width: 280px;
  max-width: calc(100vw - 32px);
  /* rgba() background driven by the Widget Background Opacity slider --
     never container `opacity`, which would also fade the panel's
     text/children (Task 9's explicit requirement: "rgba() backgrounds,
     not container opacity"). The separate whole-widget opacity control
     lives on .pc-settings-widget above. */
  background: rgba(16, 33, 75, var(--pc-widget-bg-opacity));
  color: #f2f2f2;
  border-radius: 14px;
  padding: 16px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.3);
  z-index: 6200;
}
.pc-settings-panel[hidden] { display: none; }

.pc-settings-panel__header {
  display: flex; align-items: center; justify-content: space-between;
  font-weight: 700; margin-bottom: 12px; font-size: 15px;
}
.pc-settings-panel__close {
  background: none; border: none; color: inherit; font-size: 20px; line-height: 1;
  cursor: pointer; padding: 2px 6px;
}

.pc-settings-row { margin-bottom: 14px; }
.pc-settings-row:last-child { margin-bottom: 0; }
.pc-settings-label { display: block; font-size: 12.5px; margin-bottom: 6px; color: #d7bd88; }
/* One-line description under a consequential setting, per Design System
   Section 19's "Settings Widget Control Pattern -- Toggle + Description"
   rule (pair every consequential control with a plain-language line
   explaining its effect, since a user shouldn't have to guess). */
.pc-settings-desc { font-size: 11.5px; color: #a39b91; margin: -2px 0 8px; line-height: 1.4; }

.pc-lang-dropdown { position: relative; }
.pc-lang-dropdown__trigger {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.16);
  color: #f2f2f2; border-radius: 8px; padding: 8px 12px; font-size: 13.5px; cursor: pointer;
  font-family: 'Sora', sans-serif;
}
.pc-lang-dropdown__panel {
  position: absolute; top: calc(100% + 6px); left: 0; right: 0;
  background: #10214b; border: 1px solid rgba(255,255,255,0.14);
  /* Sharp corner nearest the trigger (top-left here, since the trigger
     sits above this downward-opening panel) -- the "message bubble" corner
     rule from PETCENTER_DESIGN_SYSTEM_v1_0.md Section 24 (v1.25). */
  border-radius: 0 8px 8px 8px;
  list-style: none; margin: 0; padding: 4px; max-height: 200px; overflow-y: auto;
  z-index: 6300;
}
.pc-lang-dropdown__panel[hidden] { display: none; }
.pc-lang-dropdown__panel li { padding: 8px 10px; font-size: 13.5px; border-radius: 6px; cursor: pointer; }
.pc-lang-dropdown__panel li:hover,
.pc-lang-dropdown__panel li[aria-selected="true"] { background: rgba(215,189,136,0.18); }

.pc-opacity-slider { width: 100%; accent-color: #d7bd88; }
.pc-opacity-value { font-size: 12px; color: #a39b91; float: right; margin-top: 2px; }

@media (max-width: 600px) {
  /* Below 600px, opening the panel to the right of a left-edge trigger
     would push a 280px-wide panel off the right edge of the viewport on
     most phones. Anchor the panel to open downward from the trigger
     instead, still clear of both the hero content and the bottom-right
     contact button. Anchored via `right: 0` (not `left: 0`) since the
     trigger itself now sits near the right edge -- `left: 0` would push
     a near-full-width panel further right and off-screen. */
  .pc-settings-panel {
    top: calc(100% + 10px);
    right: 0;
    left: auto;
    transform: none;
    width: calc(100vw - 48px);
  }
}
</style>

<div class="pc-settings-widget" id="pcSettingsWidget">
  <button type="button" class="pc-settings-trigger" id="pcSettingsTrigger"
          aria-haspopup="true" aria-expanded="false" aria-controls="pcSettingsPanel"
          aria-label="Open site settings">
    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <circle cx="12" cy="12" r="3"></circle>
      <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
    </svg>
  </button>

  <div class="pc-settings-panel" id="pcSettingsPanel" role="dialog" aria-modal="false"
       aria-label="Site settings" hidden="">
    <div class="pc-settings-panel__header">
      <span data-i18n="common.settingsWidget.1">Settings</span>
      <button type="button" class="pc-settings-panel__close" id="pcSettingsClose"
              aria-label="Close settings">&times;</button>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcLangLabel" data-i18n="common.settingsWidget.2">Language</label>
      <div class="pc-lang-dropdown" id="pcLangDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcLangTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcLangLabel"
                aria-controls="pcLangListbox">
          <span id="pcLangCurrentLabel">English</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcLangListbox" role="listbox"
            aria-labelledby="pcLangLabel" tabindex="-1" hidden="">
          <li role="option" data-lang="en" aria-selected="true" tabindex="0">English</li>
          <li role="option" data-lang="es" aria-selected="false" tabindex="0">Español</li>
          <li role="option" data-lang="pt" aria-selected="false" tabindex="0">Português</li>
          <li role="option" data-lang="fr" aria-selected="false" tabindex="0">Français</li>
          <li role="option" data-lang="ar" aria-selected="false" tabindex="0">العربية</li>
          <li role="option" data-lang="zh-Hans" aria-selected="false" tabindex="0">简体中文</li>
        </ul>
      </div>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcOpacitySlider" id="pcOpacityLabel" data-i18n="common.settingsWidget.3">Widget Background Opacity</label>
      <input type="range" id="pcOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="90"
             role="slider" aria-labelledby="pcOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="90" aria-valuetext="90%">
      <span class="pc-opacity-value" id="pcOpacityValue" aria-hidden="true">90%</span>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcWidgetOpacitySlider" id="pcWidgetOpacityLabel" data-i18n="common.settingsWidget.4">Widget Opacity</label>
      <input type="range" id="pcWidgetOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="100"
             role="slider" aria-labelledby="pcWidgetOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="100" aria-valuetext="100%">
      <span class="pc-opacity-value" id="pcWidgetOpacityValue" aria-hidden="true">100%</span>
    </div>

    <!-- TASK (Color Vision Accessibility) START: brief calls for this
         control to sit "alongside the existing dark mode toggle" -- no dark
         mode toggle exists anywhere in this codebase yet (Design System
         Section 19 documents its intended look/behavior but confirms it
         is not yet built; searched before assuming otherwise). Not adding
         a placeholder/stub toggle for a feature that hasn't been
         specified as buildable here -- flagging the gap in this task's
         completion file instead, per "flag it rather than inventing." -->
    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcVisionLabel" data-i18n="common.settingsWidget.5">Color Vision</label>
      <p class="pc-settings-desc" id="pcVisionDesc" data-i18n="common.settingsWidget.6">Adjusts status colors, alerts, and charts sitewide for your selected vision type.</p>
      <div class="pc-lang-dropdown" id="pcVisionDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcVisionTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcVisionLabel"
                aria-controls="pcVisionListbox">
          <span id="pcVisionCurrentLabel">Normal Vision</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcVisionListbox" role="listbox"
            aria-labelledby="pcVisionLabel" tabindex="-1" hidden="">
          <li role="option" data-vision="normal" aria-selected="true" tabindex="0">Normal Vision</li>
          <li role="option" data-vision="deuteranopia" aria-selected="false" tabindex="0">Deuteranopia</li>
          <li role="option" data-vision="protanopia" aria-selected="false" tabindex="0">Protanopia</li>
          <li role="option" data-vision="tritanopia" aria-selected="false" tabindex="0">Tritanopia</li>
        </ul>
      </div>
    </div>
    <!-- TASK (Color Vision Accessibility) END -->
  </div>
</div>

<script>
// TASK-9 START: Settings Widget logic + `preferences` schema owner.
(function () {
  'use strict';

  var PREFS_KEY = 'preferences';
  // Written by admin-dashboard.html's "Default Widget Background Opacity"
  // and "Default Widget Opacity" controls (this task's admin-side
  // deliverable). Read here only as a fallback default for a visitor with
  // no personal preference saved yet -- a visitor's own saved choice in
  // `preferences.opacity` / `preferences.widgetOpacity` always wins.
  var ADMIN_DEFAULTS_KEY = 'pc_admin_settings_defaults';

  // LANG_FOLDER_MAP mirrors Task 1's /en/-/zh/ folder scaffolding -- English
  // is the un-prefixed root (never a redirect target), the rest are their
  // own language-code folders. 'zh-Hans' (tagged, not bare 'zh') is the
  // CEO-confirmed value for Chinese everywhere this schema is used.
  var LANG_FOLDER_MAP = { en: '/', es: '/es/', pt: '/pt/', fr: '/fr/', ar: '/ar/', 'zh-Hans': '/zh/' };
  var LANG_LABELS = { en: 'English', es: 'Español', pt: 'Português', fr: 'Français', ar: 'العربية', 'zh-Hans': '简体中文' };
  var VISION_LABELS = { normal: 'Normal Vision', deuteranopia: 'Deuteranopia', protanopia: 'Protanopia', tritanopia: 'Tritanopia' };

  function readAdminDefaultOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 90;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.opacity);
      return (val >= 20 && val <= 100) ? val : 90;
    } catch (e) { return 90; }
  }

  function readAdminDefaultWidgetOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 100;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.widgetOpacity);
      return (val >= 20 && val <= 100) ? val : 100;
    } catch (e) { return 100; }
  }

  function getPreferences() {
    try {
      var raw = localStorage.getItem(PREFS_KEY);
      var parsed = raw ? JSON.parse(raw) : {};
      return {
        lang: (typeof parsed.lang === 'string' && parsed.lang) ? parsed.lang : 'en',
        opacity: (typeof parsed.opacity === 'number') ? parsed.opacity : readAdminDefaultOpacity(),
        widgetOpacity: (typeof parsed.widgetOpacity === 'number') ? parsed.widgetOpacity : readAdminDefaultWidgetOpacity(),
        colorVision: (typeof parsed.colorVision === 'string' && VISION_LABELS[parsed.colorVision]) ? parsed.colorVision : 'normal'
      };
    } catch (e) {
      return { lang: 'en', opacity: readAdminDefaultOpacity(), widgetOpacity: readAdminDefaultWidgetOpacity(), colorVision: 'normal' };
    }
  }

  function setPreferences(partial) {
    var current = getPreferences();
    var next = { lang: current.lang, opacity: current.opacity, widgetOpacity: current.widgetOpacity, colorVision: current.colorVision };
    for (var k in partial) { if (Object.prototype.hasOwnProperty.call(partial, k)) next[k] = partial[k]; }
    try { localStorage.setItem(PREFS_KEY, JSON.stringify(next)); } catch (e) { /* storage unavailable -- fail silently, in-memory state still applies for this tab */ }
    return next;
  }

  // Panel-only rgba() background opacity -- children stay fully legible
  // regardless of this value.
  function applyBackgroundOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcOpacitySlider');
    var valueEl = document.getElementById('pcOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  // Whole-widget opacity -- real CSS `opacity` on .pc-settings-widget,
  // fades the trigger button and everything inside the panel together.
  function applyWidgetOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcWidgetOpacitySlider');
    var valueEl = document.getElementById('pcWidgetOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  function applyLangSelection(code) {
    var currentLabelEl = document.getElementById('pcLangCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = LANG_LABELS[code] || 'English';
    var options = document.querySelectorAll('#pcLangListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-lang') === code ? 'true' : 'false');
    });
  }

  // Sets the `data-vision` attribute on <html>, which every
  // [data-vision="..."] CSS override block above (and in admin-dashboard.html)
  // keys off of. "normal" matches no override rule, so it's a no-op that
  // simply leaves the default colors in place -- no separate "clear" branch
  // needed.
  function applyVisionSelection(vision) {
    document.documentElement.setAttribute('data-vision', vision);
    var currentLabelEl = document.getElementById('pcVisionCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = VISION_LABELS[vision] || 'Normal Vision';
    var options = document.querySelectorAll('#pcVisionListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-vision') === vision ? 'true' : 'false');
    });
  }

  function navigateToLang(code) {
    var target = LANG_FOLDER_MAP[code] || '/';
    // Cross-document View Transitions (enabled sitewide via the
    // `@view-transition { navigation: auto; }` at-rule in <head>) animate
    // this navigation automatically in supporting browsers -- a plain
    // same-origin navigation is all that's needed to trigger it.
    window.location.href = target;
  }

  // Apply stored/default preferences immediately (before DOMContentLoaded)
  // so both CSS variables are correct before first paint -- avoids a flash
  // of the defaults on a return visitor with different saved values. The
  // `data-vision` attribute is likewise set here, before first paint, so
  // there's no flash of un-adjusted colors either.
  (function initFromStoredPreferencesEarly() {
    var prefs = getPreferences();
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (prefs.opacity / 100).toFixed(2));
    document.documentElement.style.setProperty('--pc-widget-opacity', (prefs.widgetOpacity / 100).toFixed(2));
    document.documentElement.setAttribute('data-vision', prefs.colorVision);
  })();

  document.addEventListener('DOMContentLoaded', function () {
    var trigger = document.getElementById('pcSettingsTrigger');
    var panel = document.getElementById('pcSettingsPanel');
    var closeBtn = document.getElementById('pcSettingsClose');
    var langTrigger = document.getElementById('pcLangTrigger');
    var langListbox = document.getElementById('pcLangListbox');
    var opacitySlider = document.getElementById('pcOpacitySlider');
    var widgetOpacitySlider = document.getElementById('pcWidgetOpacitySlider');
    var visionTrigger = document.getElementById('pcVisionTrigger');
    var visionListbox = document.getElementById('pcVisionListbox');

    if (!trigger || !panel) return;

    var prefs = getPreferences();
    applyBackgroundOpacity(prefs.opacity);
    applyWidgetOpacity(prefs.widgetOpacity);
    applyLangSelection(prefs.lang);
    applyVisionSelection(prefs.colorVision);

    function openPanel() {
      panel.hidden = false;
      trigger.setAttribute('aria-expanded', 'true');
    }
    function closeAllDropdowns() {
      if (langListbox && !langListbox.hidden) {
        langListbox.hidden = true;
        langTrigger.setAttribute('aria-expanded', 'false');
      }
      if (visionListbox && !visionListbox.hidden) {
        visionListbox.hidden = true;
        visionTrigger.setAttribute('aria-expanded', 'false');
      }
    }
    function closePanel() {
      panel.hidden = true;
      trigger.setAttribute('aria-expanded', 'false');
      closeAllDropdowns();
    }

    trigger.addEventListener('click', function () {
      panel.hidden ? openPanel() : closePanel();
    });
    if (closeBtn) closeBtn.addEventListener('click', closePanel);

    document.addEventListener('click', function (e) {
      if (!e.target.closest('#pcSettingsWidget')) closePanel();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closePanel();
    });

    // Language dropdown: open/close + selection.
    if (langTrigger && langListbox) {
      langTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !langListbox.hidden;
        langListbox.hidden = isOpen;
        langTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      langListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var code = opt.getAttribute('data-lang');
        setPreferences({ lang: code });
        applyLangSelection(code);
        closeAllDropdowns();
        navigateToLang(code);
      });
      // Keyboard support: ArrowUp/ArrowDown move focus between options,
      // Enter/Space activates the focused option.
      langListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(langListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          langTrigger.focus();
        }
      });
    }

    // Color Vision dropdown: same open/close/selection/keyboard pattern as
    // the language dropdown above -- unlike language, selecting an option
    // here does NOT navigate anywhere, it just applies immediately.
    if (visionTrigger && visionListbox) {
      visionTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !visionListbox.hidden;
        visionListbox.hidden = isOpen;
        visionTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      visionListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var vision = opt.getAttribute('data-vision');
        setPreferences({ colorVision: vision });
        applyVisionSelection(vision);
        closeAllDropdowns();
      });
      visionListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(visionListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          visionTrigger.focus();
        }
      });
    }

    // Both opacity sliders -- native <input type="range"> already provides
    // full keyboard support (Arrow keys, Home/End, PageUp/PageDown); role
    // and aria-value* attributes are set explicitly per this task's spec.
    if (opacitySlider) {
      opacitySlider.addEventListener('input', function () {
        var value = Number(opacitySlider.value);
        applyBackgroundOpacity(value);
        setPreferences({ opacity: value });
      });
    }
    if (widgetOpacitySlider) {
      widgetOpacitySlider.addEventListener('input', function () {
        var value = Number(widgetOpacitySlider.value);
        applyWidgetOpacity(value);
        setPreferences({ widgetOpacity: value });
      });
    }

    // Native `storage` event -- fires in OTHER tabs/windows on this origin
    // when `preferences` changes there, and vice versa. This is the
    // cross-tab sync mechanism this task requires; no polling needed.
    window.addEventListener('storage', function (e) {
      if (e.key !== PREFS_KEY) return;
      var updated = getPreferences();
      applyBackgroundOpacity(updated.opacity);
      applyWidgetOpacity(updated.widgetOpacity);
      applyLangSelection(updated.lang);
      applyVisionSelection(updated.colorVision);
    });
  });
})();
// TASK-9 END
</script>
</body>
```
<!-- TASK-10 END -->

### `blog-home.html`  →  live path `blog/index.html`

<!-- TASK-10 START -->
**Change 1 of 2 — View Transitions style block, before `</head>`**

**Old code:**
```html
</head>
```
**New code:**
```html
<!-- TASK-9 START: cross-document View Transitions API, enabled sitewide.
     Animates same-origin full-page navigations (e.g. the Settings Widget's
     language switcher navigating between /, /es/, /pt/, /fr/, /ar/, /zh/)
     in browsers that support the cross-document View Transitions API
     (Chromium 126+). Browsers without support ignore this at-rule and
     navigate normally with no error -- this is a progressive enhancement,
     not a dependency. -->
<style>
@view-transition {
  navigation: auto;
}
</style>
<!-- TASK-9 END -->
</head>
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Change 2 of 2 — Settings Widget (style + markup + script), before `</body>`**

**Old code:**
```html
</body>
```
**New code:**
```html
<!-- TASK-9 START: Settings Widget -- gear-icon trigger + panel housing the
     sitewide language switcher and the personal widget-background-opacity
     slider. Defines the shared `preferences` object (localStorage key
     "preferences", fields {lang, opacity}) that this page's own
     lang-redirect.js / i18n-core.js (Task 1) already read `preferences.lang`
     from, and that Admin-Project #4's `preferred_chat_language` field also
     reads independently -- the exact field name/shape/values documented in
     this task must not change without notifying that project first.
     Z-INDEX NOTE: the site's real, documented highest z-index anywhere is
     6001 (the filter/form bottom-sheet system), not the floating contact
     button's 4000 -- this widget's trigger/panel/dropdown are placed at
     6100/6200/6300 so they always sit above every existing floating
     element, including both of those. -->
<style>
/* Renamed from --pc-widget-opacity (added in this task's first pass) to
   --pc-widget-bg-opacity, and a second, distinct --pc-widget-opacity
   variable added for the whole widget's own opacity -- per user follow-up,
   "Widget Background Opacity" (panel's rgba background only, children
   stay fully legible) and "Widget Opacity" (the entire trigger + panel,
   via real CSS `opacity` on the container) are two separate controls. */
:root { --pc-widget-bg-opacity: 0.90; --pc-widget-opacity: 1; }

/* "Central position" = vertically centered on the viewport's right edge.
   User follow-up moved this from the left edge (this task's original
   build) to the right edge -- everything else about the placement logic
   from that first pass still applies: vertically centered avoids the
   hero's own CTA buttons (the collision found and fixed when this was
   briefly bottom-center), and vertical centering keeps clear of the
   bottom-right floating contact button (z-index 4000), re-confirmed by
   Playwright screenshot below since moving to the same edge as that
   button is exactly the kind of collision worth re-checking, not
   assuming safe by reasoning alone. */
.pc-settings-widget {
  position: fixed;
  top: 50%;
  right: 16px;
  transform: translateY(-50%);
  z-index: 6100;
  font-family: 'Sora', sans-serif;
  /* Whole-widget opacity (trigger + panel together), separate from the
     panel-only rgba background opacity below. Real CSS `opacity`, by
     design, fades everything inside -- that's the intended effect of this
     specific control, unlike the background one. */
  opacity: var(--pc-widget-opacity);
  transition: opacity 0.2s ease;
}

.pc-settings-trigger {
  width: 52px; height: 52px; border-radius: 50%; border: none; cursor: pointer;
  background: #10214b; color: #d7bd88;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 16px rgba(16,33,75,0.35);
  transition: transform 0.3s cubic-bezier(.4,0,.2,1), background 0.3s cubic-bezier(.4,0,.2,1);
}
.pc-settings-trigger:hover { transform: rotate(35deg); background: #1a2f5c; }
.pc-settings-trigger[aria-expanded="true"] { background: #ED7624; color: #fff; }

.pc-settings-panel {
  position: absolute;
  top: 50%;
  /* Opens to the LEFT of the trigger now that the trigger sits on the
     right edge (was `left: calc(100% + 12px)` opening rightward off a
     left-edge trigger). */
  right: calc(100% + 12px);
  transform: translateY(-50%);
  width: 280px;
  max-width: calc(100vw - 32px);
  /* rgba() background driven by the Widget Background Opacity slider --
     never container `opacity`, which would also fade the panel's
     text/children (Task 9's explicit requirement: "rgba() backgrounds,
     not container opacity"). The separate whole-widget opacity control
     lives on .pc-settings-widget above. */
  background: rgba(16, 33, 75, var(--pc-widget-bg-opacity));
  color: #f2f2f2;
  border-radius: 14px;
  padding: 16px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.3);
  z-index: 6200;
}
.pc-settings-panel[hidden] { display: none; }

.pc-settings-panel__header {
  display: flex; align-items: center; justify-content: space-between;
  font-weight: 700; margin-bottom: 12px; font-size: 15px;
}
.pc-settings-panel__close {
  background: none; border: none; color: inherit; font-size: 20px; line-height: 1;
  cursor: pointer; padding: 2px 6px;
}

.pc-settings-row { margin-bottom: 14px; }
.pc-settings-row:last-child { margin-bottom: 0; }
.pc-settings-label { display: block; font-size: 12.5px; margin-bottom: 6px; color: #d7bd88; }
/* One-line description under a consequential setting, per Design System
   Section 19's "Settings Widget Control Pattern -- Toggle + Description"
   rule (pair every consequential control with a plain-language line
   explaining its effect, since a user shouldn't have to guess). */
.pc-settings-desc { font-size: 11.5px; color: #a39b91; margin: -2px 0 8px; line-height: 1.4; }

.pc-lang-dropdown { position: relative; }
.pc-lang-dropdown__trigger {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.16);
  color: #f2f2f2; border-radius: 8px; padding: 8px 12px; font-size: 13.5px; cursor: pointer;
  font-family: 'Sora', sans-serif;
}
.pc-lang-dropdown__panel {
  position: absolute; top: calc(100% + 6px); left: 0; right: 0;
  background: #10214b; border: 1px solid rgba(255,255,255,0.14);
  /* Sharp corner nearest the trigger (top-left here, since the trigger
     sits above this downward-opening panel) -- the "message bubble" corner
     rule from PETCENTER_DESIGN_SYSTEM_v1_0.md Section 24 (v1.25). */
  border-radius: 0 8px 8px 8px;
  list-style: none; margin: 0; padding: 4px; max-height: 200px; overflow-y: auto;
  z-index: 6300;
}
.pc-lang-dropdown__panel[hidden] { display: none; }
.pc-lang-dropdown__panel li { padding: 8px 10px; font-size: 13.5px; border-radius: 6px; cursor: pointer; }
.pc-lang-dropdown__panel li:hover,
.pc-lang-dropdown__panel li[aria-selected="true"] { background: rgba(215,189,136,0.18); }

.pc-opacity-slider { width: 100%; accent-color: #d7bd88; }
.pc-opacity-value { font-size: 12px; color: #a39b91; float: right; margin-top: 2px; }

@media (max-width: 600px) {
  /* Below 600px, opening the panel to the right of a left-edge trigger
     would push a 280px-wide panel off the right edge of the viewport on
     most phones. Anchor the panel to open downward from the trigger
     instead, still clear of both the hero content and the bottom-right
     contact button. Anchored via `right: 0` (not `left: 0`) since the
     trigger itself now sits near the right edge -- `left: 0` would push
     a near-full-width panel further right and off-screen. */
  .pc-settings-panel {
    top: calc(100% + 10px);
    right: 0;
    left: auto;
    transform: none;
    width: calc(100vw - 48px);
  }
}
</style>

<div class="pc-settings-widget" id="pcSettingsWidget">
  <button type="button" class="pc-settings-trigger" id="pcSettingsTrigger"
          aria-haspopup="true" aria-expanded="false" aria-controls="pcSettingsPanel"
          aria-label="Open site settings">
    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <circle cx="12" cy="12" r="3"></circle>
      <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
    </svg>
  </button>

  <div class="pc-settings-panel" id="pcSettingsPanel" role="dialog" aria-modal="false"
       aria-label="Site settings" hidden="">
    <div class="pc-settings-panel__header">
      <span data-i18n="common.settingsWidget.1">Settings</span>
      <button type="button" class="pc-settings-panel__close" id="pcSettingsClose"
              aria-label="Close settings">&times;</button>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcLangLabel" data-i18n="common.settingsWidget.2">Language</label>
      <div class="pc-lang-dropdown" id="pcLangDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcLangTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcLangLabel"
                aria-controls="pcLangListbox">
          <span id="pcLangCurrentLabel">English</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcLangListbox" role="listbox"
            aria-labelledby="pcLangLabel" tabindex="-1" hidden="">
          <li role="option" data-lang="en" aria-selected="true" tabindex="0">English</li>
          <li role="option" data-lang="es" aria-selected="false" tabindex="0">Español</li>
          <li role="option" data-lang="pt" aria-selected="false" tabindex="0">Português</li>
          <li role="option" data-lang="fr" aria-selected="false" tabindex="0">Français</li>
          <li role="option" data-lang="ar" aria-selected="false" tabindex="0">العربية</li>
          <li role="option" data-lang="zh-Hans" aria-selected="false" tabindex="0">简体中文</li>
        </ul>
      </div>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcOpacitySlider" id="pcOpacityLabel" data-i18n="common.settingsWidget.3">Widget Background Opacity</label>
      <input type="range" id="pcOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="90"
             role="slider" aria-labelledby="pcOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="90" aria-valuetext="90%">
      <span class="pc-opacity-value" id="pcOpacityValue" aria-hidden="true">90%</span>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcWidgetOpacitySlider" id="pcWidgetOpacityLabel" data-i18n="common.settingsWidget.4">Widget Opacity</label>
      <input type="range" id="pcWidgetOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="100"
             role="slider" aria-labelledby="pcWidgetOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="100" aria-valuetext="100%">
      <span class="pc-opacity-value" id="pcWidgetOpacityValue" aria-hidden="true">100%</span>
    </div>

    <!-- TASK (Color Vision Accessibility) START: brief calls for this
         control to sit "alongside the existing dark mode toggle" -- no dark
         mode toggle exists anywhere in this codebase yet (Design System
         Section 19 documents its intended look/behavior but confirms it
         is not yet built; searched before assuming otherwise). Not adding
         a placeholder/stub toggle for a feature that hasn't been
         specified as buildable here -- flagging the gap in this task's
         completion file instead, per "flag it rather than inventing." -->
    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcVisionLabel" data-i18n="common.settingsWidget.5">Color Vision</label>
      <p class="pc-settings-desc" id="pcVisionDesc" data-i18n="common.settingsWidget.6">Adjusts status colors, alerts, and charts sitewide for your selected vision type.</p>
      <div class="pc-lang-dropdown" id="pcVisionDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcVisionTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcVisionLabel"
                aria-controls="pcVisionListbox">
          <span id="pcVisionCurrentLabel">Normal Vision</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcVisionListbox" role="listbox"
            aria-labelledby="pcVisionLabel" tabindex="-1" hidden="">
          <li role="option" data-vision="normal" aria-selected="true" tabindex="0">Normal Vision</li>
          <li role="option" data-vision="deuteranopia" aria-selected="false" tabindex="0">Deuteranopia</li>
          <li role="option" data-vision="protanopia" aria-selected="false" tabindex="0">Protanopia</li>
          <li role="option" data-vision="tritanopia" aria-selected="false" tabindex="0">Tritanopia</li>
        </ul>
      </div>
    </div>
    <!-- TASK (Color Vision Accessibility) END -->
  </div>
</div>

<script>
// TASK-9 START: Settings Widget logic + `preferences` schema owner.
(function () {
  'use strict';

  var PREFS_KEY = 'preferences';
  // Written by admin-dashboard.html's "Default Widget Background Opacity"
  // and "Default Widget Opacity" controls (this task's admin-side
  // deliverable). Read here only as a fallback default for a visitor with
  // no personal preference saved yet -- a visitor's own saved choice in
  // `preferences.opacity` / `preferences.widgetOpacity` always wins.
  var ADMIN_DEFAULTS_KEY = 'pc_admin_settings_defaults';

  // LANG_FOLDER_MAP mirrors Task 1's /en/-/zh/ folder scaffolding -- English
  // is the un-prefixed root (never a redirect target), the rest are their
  // own language-code folders. 'zh-Hans' (tagged, not bare 'zh') is the
  // CEO-confirmed value for Chinese everywhere this schema is used.
  var LANG_FOLDER_MAP = { en: '/', es: '/es/', pt: '/pt/', fr: '/fr/', ar: '/ar/', 'zh-Hans': '/zh/' };
  var LANG_LABELS = { en: 'English', es: 'Español', pt: 'Português', fr: 'Français', ar: 'العربية', 'zh-Hans': '简体中文' };
  var VISION_LABELS = { normal: 'Normal Vision', deuteranopia: 'Deuteranopia', protanopia: 'Protanopia', tritanopia: 'Tritanopia' };

  function readAdminDefaultOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 90;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.opacity);
      return (val >= 20 && val <= 100) ? val : 90;
    } catch (e) { return 90; }
  }

  function readAdminDefaultWidgetOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 100;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.widgetOpacity);
      return (val >= 20 && val <= 100) ? val : 100;
    } catch (e) { return 100; }
  }

  function getPreferences() {
    try {
      var raw = localStorage.getItem(PREFS_KEY);
      var parsed = raw ? JSON.parse(raw) : {};
      return {
        lang: (typeof parsed.lang === 'string' && parsed.lang) ? parsed.lang : 'en',
        opacity: (typeof parsed.opacity === 'number') ? parsed.opacity : readAdminDefaultOpacity(),
        widgetOpacity: (typeof parsed.widgetOpacity === 'number') ? parsed.widgetOpacity : readAdminDefaultWidgetOpacity(),
        colorVision: (typeof parsed.colorVision === 'string' && VISION_LABELS[parsed.colorVision]) ? parsed.colorVision : 'normal'
      };
    } catch (e) {
      return { lang: 'en', opacity: readAdminDefaultOpacity(), widgetOpacity: readAdminDefaultWidgetOpacity(), colorVision: 'normal' };
    }
  }

  function setPreferences(partial) {
    var current = getPreferences();
    var next = { lang: current.lang, opacity: current.opacity, widgetOpacity: current.widgetOpacity, colorVision: current.colorVision };
    for (var k in partial) { if (Object.prototype.hasOwnProperty.call(partial, k)) next[k] = partial[k]; }
    try { localStorage.setItem(PREFS_KEY, JSON.stringify(next)); } catch (e) { /* storage unavailable -- fail silently, in-memory state still applies for this tab */ }
    return next;
  }

  // Panel-only rgba() background opacity -- children stay fully legible
  // regardless of this value.
  function applyBackgroundOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcOpacitySlider');
    var valueEl = document.getElementById('pcOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  // Whole-widget opacity -- real CSS `opacity` on .pc-settings-widget,
  // fades the trigger button and everything inside the panel together.
  function applyWidgetOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcWidgetOpacitySlider');
    var valueEl = document.getElementById('pcWidgetOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  function applyLangSelection(code) {
    var currentLabelEl = document.getElementById('pcLangCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = LANG_LABELS[code] || 'English';
    var options = document.querySelectorAll('#pcLangListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-lang') === code ? 'true' : 'false');
    });
  }

  // Sets the `data-vision` attribute on <html>, which every
  // [data-vision="..."] CSS override block above (and in admin-dashboard.html)
  // keys off of. "normal" matches no override rule, so it's a no-op that
  // simply leaves the default colors in place -- no separate "clear" branch
  // needed.
  function applyVisionSelection(vision) {
    document.documentElement.setAttribute('data-vision', vision);
    var currentLabelEl = document.getElementById('pcVisionCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = VISION_LABELS[vision] || 'Normal Vision';
    var options = document.querySelectorAll('#pcVisionListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-vision') === vision ? 'true' : 'false');
    });
  }

  function navigateToLang(code) {
    var target = LANG_FOLDER_MAP[code] || '/';
    // Cross-document View Transitions (enabled sitewide via the
    // `@view-transition { navigation: auto; }` at-rule in <head>) animate
    // this navigation automatically in supporting browsers -- a plain
    // same-origin navigation is all that's needed to trigger it.
    window.location.href = target;
  }

  // Apply stored/default preferences immediately (before DOMContentLoaded)
  // so both CSS variables are correct before first paint -- avoids a flash
  // of the defaults on a return visitor with different saved values. The
  // `data-vision` attribute is likewise set here, before first paint, so
  // there's no flash of un-adjusted colors either.
  (function initFromStoredPreferencesEarly() {
    var prefs = getPreferences();
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (prefs.opacity / 100).toFixed(2));
    document.documentElement.style.setProperty('--pc-widget-opacity', (prefs.widgetOpacity / 100).toFixed(2));
    document.documentElement.setAttribute('data-vision', prefs.colorVision);
  })();

  document.addEventListener('DOMContentLoaded', function () {
    var trigger = document.getElementById('pcSettingsTrigger');
    var panel = document.getElementById('pcSettingsPanel');
    var closeBtn = document.getElementById('pcSettingsClose');
    var langTrigger = document.getElementById('pcLangTrigger');
    var langListbox = document.getElementById('pcLangListbox');
    var opacitySlider = document.getElementById('pcOpacitySlider');
    var widgetOpacitySlider = document.getElementById('pcWidgetOpacitySlider');
    var visionTrigger = document.getElementById('pcVisionTrigger');
    var visionListbox = document.getElementById('pcVisionListbox');

    if (!trigger || !panel) return;

    var prefs = getPreferences();
    applyBackgroundOpacity(prefs.opacity);
    applyWidgetOpacity(prefs.widgetOpacity);
    applyLangSelection(prefs.lang);
    applyVisionSelection(prefs.colorVision);

    function openPanel() {
      panel.hidden = false;
      trigger.setAttribute('aria-expanded', 'true');
    }
    function closeAllDropdowns() {
      if (langListbox && !langListbox.hidden) {
        langListbox.hidden = true;
        langTrigger.setAttribute('aria-expanded', 'false');
      }
      if (visionListbox && !visionListbox.hidden) {
        visionListbox.hidden = true;
        visionTrigger.setAttribute('aria-expanded', 'false');
      }
    }
    function closePanel() {
      panel.hidden = true;
      trigger.setAttribute('aria-expanded', 'false');
      closeAllDropdowns();
    }

    trigger.addEventListener('click', function () {
      panel.hidden ? openPanel() : closePanel();
    });
    if (closeBtn) closeBtn.addEventListener('click', closePanel);

    document.addEventListener('click', function (e) {
      if (!e.target.closest('#pcSettingsWidget')) closePanel();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closePanel();
    });

    // Language dropdown: open/close + selection.
    if (langTrigger && langListbox) {
      langTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !langListbox.hidden;
        langListbox.hidden = isOpen;
        langTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      langListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var code = opt.getAttribute('data-lang');
        setPreferences({ lang: code });
        applyLangSelection(code);
        closeAllDropdowns();
        navigateToLang(code);
      });
      // Keyboard support: ArrowUp/ArrowDown move focus between options,
      // Enter/Space activates the focused option.
      langListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(langListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          langTrigger.focus();
        }
      });
    }

    // Color Vision dropdown: same open/close/selection/keyboard pattern as
    // the language dropdown above -- unlike language, selecting an option
    // here does NOT navigate anywhere, it just applies immediately.
    if (visionTrigger && visionListbox) {
      visionTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !visionListbox.hidden;
        visionListbox.hidden = isOpen;
        visionTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      visionListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var vision = opt.getAttribute('data-vision');
        setPreferences({ colorVision: vision });
        applyVisionSelection(vision);
        closeAllDropdowns();
      });
      visionListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(visionListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          visionTrigger.focus();
        }
      });
    }

    // Both opacity sliders -- native <input type="range"> already provides
    // full keyboard support (Arrow keys, Home/End, PageUp/PageDown); role
    // and aria-value* attributes are set explicitly per this task's spec.
    if (opacitySlider) {
      opacitySlider.addEventListener('input', function () {
        var value = Number(opacitySlider.value);
        applyBackgroundOpacity(value);
        setPreferences({ opacity: value });
      });
    }
    if (widgetOpacitySlider) {
      widgetOpacitySlider.addEventListener('input', function () {
        var value = Number(widgetOpacitySlider.value);
        applyWidgetOpacity(value);
        setPreferences({ widgetOpacity: value });
      });
    }

    // Native `storage` event -- fires in OTHER tabs/windows on this origin
    // when `preferences` changes there, and vice versa. This is the
    // cross-tab sync mechanism this task requires; no polling needed.
    window.addEventListener('storage', function (e) {
      if (e.key !== PREFS_KEY) return;
      var updated = getPreferences();
      applyBackgroundOpacity(updated.opacity);
      applyWidgetOpacity(updated.widgetOpacity);
      applyLangSelection(updated.lang);
      applyVisionSelection(updated.colorVision);
    });
  });
})();
// TASK-9 END
</script>
</body>
```
<!-- TASK-10 END -->

### `blog-single.html`  →  live path `blog/post/index.html`

<!-- TASK-10 START -->
**Change 1 of 2 — View Transitions style block, before `</head>`**

**Old code:**
```html
</head>
```
**New code:**
```html
<!-- TASK-9 START: cross-document View Transitions API, enabled sitewide.
     Animates same-origin full-page navigations (e.g. the Settings Widget's
     language switcher navigating between /, /es/, /pt/, /fr/, /ar/, /zh/)
     in browsers that support the cross-document View Transitions API
     (Chromium 126+). Browsers without support ignore this at-rule and
     navigate normally with no error -- this is a progressive enhancement,
     not a dependency. -->
<style>
@view-transition {
  navigation: auto;
}
</style>
<!-- TASK-9 END -->
</head>
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Change 2 of 2 — Settings Widget (style + markup + script), before `</body>`**

**Old code:**
```html
</body>
```
**New code:**
```html
<!-- TASK-9 START: Settings Widget -- gear-icon trigger + panel housing the
     sitewide language switcher and the personal widget-background-opacity
     slider. Defines the shared `preferences` object (localStorage key
     "preferences", fields {lang, opacity}) that this page's own
     lang-redirect.js / i18n-core.js (Task 1) already read `preferences.lang`
     from, and that Admin-Project #4's `preferred_chat_language` field also
     reads independently -- the exact field name/shape/values documented in
     this task must not change without notifying that project first.
     Z-INDEX NOTE: the site's real, documented highest z-index anywhere is
     6001 (the filter/form bottom-sheet system), not the floating contact
     button's 4000 -- this widget's trigger/panel/dropdown are placed at
     6100/6200/6300 so they always sit above every existing floating
     element, including both of those. -->
<style>
/* Renamed from --pc-widget-opacity (added in this task's first pass) to
   --pc-widget-bg-opacity, and a second, distinct --pc-widget-opacity
   variable added for the whole widget's own opacity -- per user follow-up,
   "Widget Background Opacity" (panel's rgba background only, children
   stay fully legible) and "Widget Opacity" (the entire trigger + panel,
   via real CSS `opacity` on the container) are two separate controls. */
:root { --pc-widget-bg-opacity: 0.90; --pc-widget-opacity: 1; }

/* "Central position" = vertically centered on the viewport's right edge.
   User follow-up moved this from the left edge (this task's original
   build) to the right edge -- everything else about the placement logic
   from that first pass still applies: vertically centered avoids the
   hero's own CTA buttons (the collision found and fixed when this was
   briefly bottom-center), and vertical centering keeps clear of the
   bottom-right floating contact button (z-index 4000), re-confirmed by
   Playwright screenshot below since moving to the same edge as that
   button is exactly the kind of collision worth re-checking, not
   assuming safe by reasoning alone. */
.pc-settings-widget {
  position: fixed;
  top: 50%;
  right: 16px;
  transform: translateY(-50%);
  z-index: 6100;
  font-family: 'Sora', sans-serif;
  /* Whole-widget opacity (trigger + panel together), separate from the
     panel-only rgba background opacity below. Real CSS `opacity`, by
     design, fades everything inside -- that's the intended effect of this
     specific control, unlike the background one. */
  opacity: var(--pc-widget-opacity);
  transition: opacity 0.2s ease;
}

.pc-settings-trigger {
  width: 52px; height: 52px; border-radius: 50%; border: none; cursor: pointer;
  background: #10214b; color: #d7bd88;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 16px rgba(16,33,75,0.35);
  transition: transform 0.3s cubic-bezier(.4,0,.2,1), background 0.3s cubic-bezier(.4,0,.2,1);
}
.pc-settings-trigger:hover { transform: rotate(35deg); background: #1a2f5c; }
.pc-settings-trigger[aria-expanded="true"] { background: #ED7624; color: #fff; }

.pc-settings-panel {
  position: absolute;
  top: 50%;
  /* Opens to the LEFT of the trigger now that the trigger sits on the
     right edge (was `left: calc(100% + 12px)` opening rightward off a
     left-edge trigger). */
  right: calc(100% + 12px);
  transform: translateY(-50%);
  width: 280px;
  max-width: calc(100vw - 32px);
  /* rgba() background driven by the Widget Background Opacity slider --
     never container `opacity`, which would also fade the panel's
     text/children (Task 9's explicit requirement: "rgba() backgrounds,
     not container opacity"). The separate whole-widget opacity control
     lives on .pc-settings-widget above. */
  background: rgba(16, 33, 75, var(--pc-widget-bg-opacity));
  color: #f2f2f2;
  border-radius: 14px;
  padding: 16px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.3);
  z-index: 6200;
}
.pc-settings-panel[hidden] { display: none; }

.pc-settings-panel__header {
  display: flex; align-items: center; justify-content: space-between;
  font-weight: 700; margin-bottom: 12px; font-size: 15px;
}
.pc-settings-panel__close {
  background: none; border: none; color: inherit; font-size: 20px; line-height: 1;
  cursor: pointer; padding: 2px 6px;
}

.pc-settings-row { margin-bottom: 14px; }
.pc-settings-row:last-child { margin-bottom: 0; }
.pc-settings-label { display: block; font-size: 12.5px; margin-bottom: 6px; color: #d7bd88; }
/* One-line description under a consequential setting, per Design System
   Section 19's "Settings Widget Control Pattern -- Toggle + Description"
   rule (pair every consequential control with a plain-language line
   explaining its effect, since a user shouldn't have to guess). */
.pc-settings-desc { font-size: 11.5px; color: #a39b91; margin: -2px 0 8px; line-height: 1.4; }

.pc-lang-dropdown { position: relative; }
.pc-lang-dropdown__trigger {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.16);
  color: #f2f2f2; border-radius: 8px; padding: 8px 12px; font-size: 13.5px; cursor: pointer;
  font-family: 'Sora', sans-serif;
}
.pc-lang-dropdown__panel {
  position: absolute; top: calc(100% + 6px); left: 0; right: 0;
  background: #10214b; border: 1px solid rgba(255,255,255,0.14);
  /* Sharp corner nearest the trigger (top-left here, since the trigger
     sits above this downward-opening panel) -- the "message bubble" corner
     rule from PETCENTER_DESIGN_SYSTEM_v1_0.md Section 24 (v1.25). */
  border-radius: 0 8px 8px 8px;
  list-style: none; margin: 0; padding: 4px; max-height: 200px; overflow-y: auto;
  z-index: 6300;
}
.pc-lang-dropdown__panel[hidden] { display: none; }
.pc-lang-dropdown__panel li { padding: 8px 10px; font-size: 13.5px; border-radius: 6px; cursor: pointer; }
.pc-lang-dropdown__panel li:hover,
.pc-lang-dropdown__panel li[aria-selected="true"] { background: rgba(215,189,136,0.18); }

.pc-opacity-slider { width: 100%; accent-color: #d7bd88; }
.pc-opacity-value { font-size: 12px; color: #a39b91; float: right; margin-top: 2px; }

@media (max-width: 600px) {
  /* Below 600px, opening the panel to the right of a left-edge trigger
     would push a 280px-wide panel off the right edge of the viewport on
     most phones. Anchor the panel to open downward from the trigger
     instead, still clear of both the hero content and the bottom-right
     contact button. Anchored via `right: 0` (not `left: 0`) since the
     trigger itself now sits near the right edge -- `left: 0` would push
     a near-full-width panel further right and off-screen. */
  .pc-settings-panel {
    top: calc(100% + 10px);
    right: 0;
    left: auto;
    transform: none;
    width: calc(100vw - 48px);
  }
}
</style>

<div class="pc-settings-widget" id="pcSettingsWidget">
  <button type="button" class="pc-settings-trigger" id="pcSettingsTrigger"
          aria-haspopup="true" aria-expanded="false" aria-controls="pcSettingsPanel"
          aria-label="Open site settings">
    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <circle cx="12" cy="12" r="3"></circle>
      <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
    </svg>
  </button>

  <div class="pc-settings-panel" id="pcSettingsPanel" role="dialog" aria-modal="false"
       aria-label="Site settings" hidden="">
    <div class="pc-settings-panel__header">
      <span data-i18n="common.settingsWidget.1">Settings</span>
      <button type="button" class="pc-settings-panel__close" id="pcSettingsClose"
              aria-label="Close settings">&times;</button>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcLangLabel" data-i18n="common.settingsWidget.2">Language</label>
      <div class="pc-lang-dropdown" id="pcLangDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcLangTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcLangLabel"
                aria-controls="pcLangListbox">
          <span id="pcLangCurrentLabel">English</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcLangListbox" role="listbox"
            aria-labelledby="pcLangLabel" tabindex="-1" hidden="">
          <li role="option" data-lang="en" aria-selected="true" tabindex="0">English</li>
          <li role="option" data-lang="es" aria-selected="false" tabindex="0">Español</li>
          <li role="option" data-lang="pt" aria-selected="false" tabindex="0">Português</li>
          <li role="option" data-lang="fr" aria-selected="false" tabindex="0">Français</li>
          <li role="option" data-lang="ar" aria-selected="false" tabindex="0">العربية</li>
          <li role="option" data-lang="zh-Hans" aria-selected="false" tabindex="0">简体中文</li>
        </ul>
      </div>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcOpacitySlider" id="pcOpacityLabel" data-i18n="common.settingsWidget.3">Widget Background Opacity</label>
      <input type="range" id="pcOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="90"
             role="slider" aria-labelledby="pcOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="90" aria-valuetext="90%">
      <span class="pc-opacity-value" id="pcOpacityValue" aria-hidden="true">90%</span>
    </div>

    <div class="pc-settings-row">
      <label class="pc-settings-label" for="pcWidgetOpacitySlider" id="pcWidgetOpacityLabel" data-i18n="common.settingsWidget.4">Widget Opacity</label>
      <input type="range" id="pcWidgetOpacitySlider" class="pc-opacity-slider"
             min="20" max="100" step="5" value="100"
             role="slider" aria-labelledby="pcWidgetOpacityLabel"
             aria-valuemin="20" aria-valuemax="100" aria-valuenow="100" aria-valuetext="100%">
      <span class="pc-opacity-value" id="pcWidgetOpacityValue" aria-hidden="true">100%</span>
    </div>

    <!-- TASK (Color Vision Accessibility) START: brief calls for this
         control to sit "alongside the existing dark mode toggle" -- no dark
         mode toggle exists anywhere in this codebase yet (Design System
         Section 19 documents its intended look/behavior but confirms it
         is not yet built; searched before assuming otherwise). Not adding
         a placeholder/stub toggle for a feature that hasn't been
         specified as buildable here -- flagging the gap in this task's
         completion file instead, per "flag it rather than inventing." -->
    <div class="pc-settings-row">
      <label class="pc-settings-label" id="pcVisionLabel" data-i18n="common.settingsWidget.5">Color Vision</label>
      <p class="pc-settings-desc" id="pcVisionDesc" data-i18n="common.settingsWidget.6">Adjusts status colors, alerts, and charts sitewide for your selected vision type.</p>
      <div class="pc-lang-dropdown" id="pcVisionDropdown">
        <button type="button" class="pc-lang-dropdown__trigger" id="pcVisionTrigger"
                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="pcVisionLabel"
                aria-controls="pcVisionListbox">
          <span id="pcVisionCurrentLabel">Normal Vision</span>
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <ul class="pc-lang-dropdown__panel" id="pcVisionListbox" role="listbox"
            aria-labelledby="pcVisionLabel" tabindex="-1" hidden="">
          <li role="option" data-vision="normal" aria-selected="true" tabindex="0">Normal Vision</li>
          <li role="option" data-vision="deuteranopia" aria-selected="false" tabindex="0">Deuteranopia</li>
          <li role="option" data-vision="protanopia" aria-selected="false" tabindex="0">Protanopia</li>
          <li role="option" data-vision="tritanopia" aria-selected="false" tabindex="0">Tritanopia</li>
        </ul>
      </div>
    </div>
    <!-- TASK (Color Vision Accessibility) END -->
  </div>
</div>

<script>
// TASK-9 START: Settings Widget logic + `preferences` schema owner.
(function () {
  'use strict';

  var PREFS_KEY = 'preferences';
  // Written by admin-dashboard.html's "Default Widget Background Opacity"
  // and "Default Widget Opacity" controls (this task's admin-side
  // deliverable). Read here only as a fallback default for a visitor with
  // no personal preference saved yet -- a visitor's own saved choice in
  // `preferences.opacity` / `preferences.widgetOpacity` always wins.
  var ADMIN_DEFAULTS_KEY = 'pc_admin_settings_defaults';

  // LANG_FOLDER_MAP mirrors Task 1's /en/-/zh/ folder scaffolding -- English
  // is the un-prefixed root (never a redirect target), the rest are their
  // own language-code folders. 'zh-Hans' (tagged, not bare 'zh') is the
  // CEO-confirmed value for Chinese everywhere this schema is used.
  var LANG_FOLDER_MAP = { en: '/', es: '/es/', pt: '/pt/', fr: '/fr/', ar: '/ar/', 'zh-Hans': '/zh/' };
  var LANG_LABELS = { en: 'English', es: 'Español', pt: 'Português', fr: 'Français', ar: 'العربية', 'zh-Hans': '简体中文' };
  var VISION_LABELS = { normal: 'Normal Vision', deuteranopia: 'Deuteranopia', protanopia: 'Protanopia', tritanopia: 'Tritanopia' };

  function readAdminDefaultOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 90;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.opacity);
      return (val >= 20 && val <= 100) ? val : 90;
    } catch (e) { return 90; }
  }

  function readAdminDefaultWidgetOpacity() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      if (!raw) return 100;
      var parsed = JSON.parse(raw);
      var val = Number(parsed && parsed.widgetOpacity);
      return (val >= 20 && val <= 100) ? val : 100;
    } catch (e) { return 100; }
  }

  function getPreferences() {
    try {
      var raw = localStorage.getItem(PREFS_KEY);
      var parsed = raw ? JSON.parse(raw) : {};
      return {
        lang: (typeof parsed.lang === 'string' && parsed.lang) ? parsed.lang : 'en',
        opacity: (typeof parsed.opacity === 'number') ? parsed.opacity : readAdminDefaultOpacity(),
        widgetOpacity: (typeof parsed.widgetOpacity === 'number') ? parsed.widgetOpacity : readAdminDefaultWidgetOpacity(),
        colorVision: (typeof parsed.colorVision === 'string' && VISION_LABELS[parsed.colorVision]) ? parsed.colorVision : 'normal'
      };
    } catch (e) {
      return { lang: 'en', opacity: readAdminDefaultOpacity(), widgetOpacity: readAdminDefaultWidgetOpacity(), colorVision: 'normal' };
    }
  }

  function setPreferences(partial) {
    var current = getPreferences();
    var next = { lang: current.lang, opacity: current.opacity, widgetOpacity: current.widgetOpacity, colorVision: current.colorVision };
    for (var k in partial) { if (Object.prototype.hasOwnProperty.call(partial, k)) next[k] = partial[k]; }
    try { localStorage.setItem(PREFS_KEY, JSON.stringify(next)); } catch (e) { /* storage unavailable -- fail silently, in-memory state still applies for this tab */ }
    return next;
  }

  // Panel-only rgba() background opacity -- children stay fully legible
  // regardless of this value.
  function applyBackgroundOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcOpacitySlider');
    var valueEl = document.getElementById('pcOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  // Whole-widget opacity -- real CSS `opacity` on .pc-settings-widget,
  // fades the trigger button and everything inside the panel together.
  function applyWidgetOpacity(value) {
    document.documentElement.style.setProperty('--pc-widget-opacity', (value / 100).toFixed(2));
    var sliderEl = document.getElementById('pcWidgetOpacitySlider');
    var valueEl = document.getElementById('pcWidgetOpacityValue');
    if (sliderEl) {
      sliderEl.value = value;
      sliderEl.setAttribute('aria-valuenow', value);
      sliderEl.setAttribute('aria-valuetext', value + '%');
    }
    if (valueEl) valueEl.textContent = value + '%';
  }

  function applyLangSelection(code) {
    var currentLabelEl = document.getElementById('pcLangCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = LANG_LABELS[code] || 'English';
    var options = document.querySelectorAll('#pcLangListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-lang') === code ? 'true' : 'false');
    });
  }

  // Sets the `data-vision` attribute on <html>, which every
  // [data-vision="..."] CSS override block above (and in admin-dashboard.html)
  // keys off of. "normal" matches no override rule, so it's a no-op that
  // simply leaves the default colors in place -- no separate "clear" branch
  // needed.
  function applyVisionSelection(vision) {
    document.documentElement.setAttribute('data-vision', vision);
    var currentLabelEl = document.getElementById('pcVisionCurrentLabel');
    if (currentLabelEl) currentLabelEl.textContent = VISION_LABELS[vision] || 'Normal Vision';
    var options = document.querySelectorAll('#pcVisionListbox li');
    options.forEach(function (opt) {
      opt.setAttribute('aria-selected', opt.getAttribute('data-vision') === vision ? 'true' : 'false');
    });
  }

  function navigateToLang(code) {
    var target = LANG_FOLDER_MAP[code] || '/';
    // Cross-document View Transitions (enabled sitewide via the
    // `@view-transition { navigation: auto; }` at-rule in <head>) animate
    // this navigation automatically in supporting browsers -- a plain
    // same-origin navigation is all that's needed to trigger it.
    window.location.href = target;
  }

  // Apply stored/default preferences immediately (before DOMContentLoaded)
  // so both CSS variables are correct before first paint -- avoids a flash
  // of the defaults on a return visitor with different saved values. The
  // `data-vision` attribute is likewise set here, before first paint, so
  // there's no flash of un-adjusted colors either.
  (function initFromStoredPreferencesEarly() {
    var prefs = getPreferences();
    document.documentElement.style.setProperty('--pc-widget-bg-opacity', (prefs.opacity / 100).toFixed(2));
    document.documentElement.style.setProperty('--pc-widget-opacity', (prefs.widgetOpacity / 100).toFixed(2));
    document.documentElement.setAttribute('data-vision', prefs.colorVision);
  })();

  document.addEventListener('DOMContentLoaded', function () {
    var trigger = document.getElementById('pcSettingsTrigger');
    var panel = document.getElementById('pcSettingsPanel');
    var closeBtn = document.getElementById('pcSettingsClose');
    var langTrigger = document.getElementById('pcLangTrigger');
    var langListbox = document.getElementById('pcLangListbox');
    var opacitySlider = document.getElementById('pcOpacitySlider');
    var widgetOpacitySlider = document.getElementById('pcWidgetOpacitySlider');
    var visionTrigger = document.getElementById('pcVisionTrigger');
    var visionListbox = document.getElementById('pcVisionListbox');

    if (!trigger || !panel) return;

    var prefs = getPreferences();
    applyBackgroundOpacity(prefs.opacity);
    applyWidgetOpacity(prefs.widgetOpacity);
    applyLangSelection(prefs.lang);
    applyVisionSelection(prefs.colorVision);

    function openPanel() {
      panel.hidden = false;
      trigger.setAttribute('aria-expanded', 'true');
    }
    function closeAllDropdowns() {
      if (langListbox && !langListbox.hidden) {
        langListbox.hidden = true;
        langTrigger.setAttribute('aria-expanded', 'false');
      }
      if (visionListbox && !visionListbox.hidden) {
        visionListbox.hidden = true;
        visionTrigger.setAttribute('aria-expanded', 'false');
      }
    }
    function closePanel() {
      panel.hidden = true;
      trigger.setAttribute('aria-expanded', 'false');
      closeAllDropdowns();
    }

    trigger.addEventListener('click', function () {
      panel.hidden ? openPanel() : closePanel();
    });
    if (closeBtn) closeBtn.addEventListener('click', closePanel);

    document.addEventListener('click', function (e) {
      if (!e.target.closest('#pcSettingsWidget')) closePanel();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closePanel();
    });

    // Language dropdown: open/close + selection.
    if (langTrigger && langListbox) {
      langTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !langListbox.hidden;
        langListbox.hidden = isOpen;
        langTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      langListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var code = opt.getAttribute('data-lang');
        setPreferences({ lang: code });
        applyLangSelection(code);
        closeAllDropdowns();
        navigateToLang(code);
      });
      // Keyboard support: ArrowUp/ArrowDown move focus between options,
      // Enter/Space activates the focused option.
      langListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(langListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          langTrigger.focus();
        }
      });
    }

    // Color Vision dropdown: same open/close/selection/keyboard pattern as
    // the language dropdown above -- unlike language, selecting an option
    // here does NOT navigate anywhere, it just applies immediately.
    if (visionTrigger && visionListbox) {
      visionTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !visionListbox.hidden;
        visionListbox.hidden = isOpen;
        visionTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
      visionListbox.addEventListener('click', function (e) {
        var opt = e.target.closest('li[role="option"]');
        if (!opt) return;
        var vision = opt.getAttribute('data-vision');
        setPreferences({ colorVision: vision });
        applyVisionSelection(vision);
        closeAllDropdowns();
      });
      visionListbox.addEventListener('keydown', function (e) {
        var opts = Array.prototype.slice.call(visionListbox.querySelectorAll('li[role="option"]'));
        var idx = opts.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          idx = Math.min(opts.length - 1, idx + 1);
          opts[idx].focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          idx = Math.max(0, idx - 1);
          opts[idx].focus();
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (document.activeElement && document.activeElement.click) document.activeElement.click();
        } else if (e.key === 'Escape') {
          closeAllDropdowns();
          visionTrigger.focus();
        }
      });
    }

    // Both opacity sliders -- native <input type="range"> already provides
    // full keyboard support (Arrow keys, Home/End, PageUp/PageDown); role
    // and aria-value* attributes are set explicitly per this task's spec.
    if (opacitySlider) {
      opacitySlider.addEventListener('input', function () {
        var value = Number(opacitySlider.value);
        applyBackgroundOpacity(value);
        setPreferences({ opacity: value });
      });
    }
    if (widgetOpacitySlider) {
      widgetOpacitySlider.addEventListener('input', function () {
        var value = Number(widgetOpacitySlider.value);
        applyWidgetOpacity(value);
        setPreferences({ widgetOpacity: value });
      });
    }

    // Native `storage` event -- fires in OTHER tabs/windows on this origin
    // when `preferences` changes there, and vice versa. This is the
    // cross-tab sync mechanism this task requires; no polling needed.
    window.addEventListener('storage', function (e) {
      if (e.key !== PREFS_KEY) return;
      var updated = getPreferences();
      applyBackgroundOpacity(updated.opacity);
      applyWidgetOpacity(updated.widgetOpacity);
      applyLangSelection(updated.lang);
      applyVisionSelection(updated.colorVision);
    });
  });
})();
// TASK-9 END
</script>
</body>
```
<!-- TASK-10 END -->

## New files
No new files. No zip is sent. Nothing in this hand-in contains `config.php`, `pet.json`, `.env`, or anything from `applications/` or `uploads/`.

## Bugs Found Outside Task Scope
- **Open scope gap, not a bug:** `signin/index.html` (source `signin.html`) was never supplied, so it did not get the widget. The assignment's "Files to Request" for this task (every Section 3 page except `index.html`) included it, but it was not in the upload.
- No bugs found in the files this task touched.

Console errors observed during Playwright verification
(`net::ERR_FILE_NOT_FOUND` for `/assets/images/*.png`, `403`/blocked
requests for Google Fonts, `cdnjs`/`jsdelivr` CDN scripts on
`adoption-application.html`) are pre-existing and environmental — this
sandbox has no network access and wasn't given the actual image assets
— confirmed by reproducing the identical errors against each file's
*original*, pre-Task-10 version. Not caused by this task's edits, and
not something to fix under Scope Lock even if it were in scope.

## Self-Check Confirmation
- Re-read `adoption-website-project-summary.md`, `PETCENTER_DESIGN_SYSTEM_v1_0.md`,
  `Task-9-Completion-1.md`, and this task's own brief in full before
  writing any code, per Warning & Instructions rule 8.
- Confirmed the widget code did not actually exist in the first `index.html`/
  `admin-dashboard.html` pair supplied earlier in this task — correctly
  stopped and asked for the real post-Task-9 files rather than
  reconstructing the widget from `Task-9-Completion-1.md`'s (necessarily
  elided) documentation, per the reference-material gate.
- Structural lint on all 15 output files: BeautifulSoup parse (all parse
  cleanly), zero duplicate IDs (including a targeted check that every
  widget-specific ID — `pcSettingsWidget`, `pcSettingsTrigger`,
  `pcLangListbox`, etc. — appears exactly once per file), balanced
  `<script>`/`<style>` tag counts, and `new Function()` JS-syntax check
  on every inline script block in every file (zero errors).
- Playwright (headless Chromium) verification on all 15 files:
  - Desktop (1280×900): widget trigger renders at `left:16px`, vertically
    centered; `elementFromPoint` at the trigger's center resolves to the
    trigger itself on every page (no other element intercepts it —
    the explicit collision check this task's brief required); panel
    opens/closes on trigger click and outside click; both opacity
    sliders write independent, uncontaminated values to `localStorage.preferences`
    (`{"lang":"en","opacity":50,"widgetOpacity":60}` confirmed exactly).
  - Mobile (390×844): panel reflows downward from the trigger on every
    file, zero horizontal overflow past the 390px viewport edge on any
    page (confirmed by bounding-box check).
  - Language navigation: route-intercepted the `zh-Hans` selection on a
    representative page (`cats.html`) and confirmed the exact resulting
    request target is `/zh/`, matching Task 9's documented
    `LANG_FOLDER_MAP`/CEO-confirmed schema.
  - Collision checks required by this task's brief: confirmed
    `adoption-application.html`'s signature pad (not fixed-positioned)
    and sticky nav footer (`bottom:0`, full-width) don't overlap the
    widget; confirmed no scroll/reading-progress indicator exists on
    `blog-home.html`/`blog-single.html` to collide with in the first
    place (checked the real files directly, not assumed).
- **Axe-core scan — sandbox limitation, stated plainly rather than
  presented as done:** this sandbox has no network access, and neither
  `axe-core` (npm) nor a local copy of the library was available to
  install or load — `npm install axe-core` failed with a 403 from the
  registry, and no cached copy exists anywhere on the container. A full
  automated `axe-core` scan could not be run. As a partial substitute,
  confirmed manually that the reused widget markup's accessibility
  attributes (`role="slider"` + full `aria-value*` on both sliders,
  `role="listbox"`/`role="option"` + `aria-selected` on the language
  list, `aria-haspopup`/`aria-expanded`/`aria-controls` on both
  triggers, `aria-label` on the icon-only trigger and close buttons) are
  present unchanged in all 15 files, identical to the implementation
  Task 9 already Playwright-verified for keyboard operation. This is not
  a substitute for the real scan — flagging it for whoever has network
  access to run `axe-core` against the live/staged pages before this
  work is considered fully verified.
- Hand-in format check (per WORKER-DELIVERY-GUIDE.md): the Files Changed table has four columns; every old snippet is a whole line copied from the original file and occurs exactly once in it (checked by script on all 15 files); applying each old/new pair to the original reproduces the delivered file exactly; every block is wrapped in `TASK-10` anchors, with the original `TASK-9` anchors kept inside the reused code.
- Scope Lock held: only the 15 files listed above were touched; no
  content, tags, classes, or IDs unrelated to the widget insertion were
  altered on any page; `index.html` and `admin-dashboard.html` were
  correctly left untouched since Task 9 already covers them.
- No emojis anywhere in the inserted code (none existed in the
  Task-9 source either). Every inserted block carries its original
  `<!-- TASK-9 START/END -->` / `// TASK-9 START/END` anchor comments
  with all inline explanatory comments preserved verbatim.

## Addendum — Post-Delivery Resync (shared-widget drift)

After this task's original delivery above, `index.html`'s Settings
Widget changed twice more (both outside this task, via direct CEO
follow-up and a separate, unnumbered Color Vision Accessibility task):
the widget moved from vertically-centered-left-edge to
vertically-centered-right-edge, and gained a full Color Vision control
(4-option dropdown; new `preferences.colorVision` field; a
`document.documentElement.setAttribute('data-vision', ...)` call driving
`[data-vision="..."]` CSS overrides). Since this task's job is to keep
all 15 rolled-out pages identical to the one shared implementation
(not a point-in-time snapshot of it), the entire widget block — style,
markup, and script — was pulled fresh from the current `index.html` and
substituted in place of the original block in all 15 files, byte-for-
byte, under the same `TASK-9` anchors. The `@view-transition` head block
was checked and confirmed unchanged, so it was left untouched.

**Files re-touched:** the same 15 files as above — no new files added
to this task's scope, no other content changed on any of them.

**A real bug was caught and fixed during this resync, not shipped:** the
first extraction of the updated widget block from `index.html` was cut
one line short and omitted the block's closing `</script>` tag. This
was caught by the same balanced-tag lint this task already runs after
every edit (script open/close counts came back mismatched: e.g. 4
opens/3 closes on several files) before anything was verified further —
the affected working copies were discarded and rebuilt from this task's
original, already-verified pre-resync output plus a corrected
extraction, rather than patched over. Re-lint after the fix came back
balanced (equal `<script>`/`</script>` and `<style>`/`</style>` counts,
zero duplicate IDs including the six new Color Vision IDs, BeautifulSoup
parse clean) on all 15 files.

**Re-verification performed (Playwright, headless Chromium), all 15
files:**
- Desktop (1280×900): trigger sits within 20px of the right edge on
  every page (confirmed by bounding box, not assumed from the CSS
  alone); `elementFromPoint` at the trigger's center still resolves to
  the trigger itself (no obstruction from anything else on the page,
  same collision check this task's brief originally required); panel
  opens/closes correctly.
- Selecting "Deuteranopia" from the new Color Vision dropdown sets
  `<html data-vision="deuteranopia">` and writes
  `colorVision:"deuteranopia"` into `localStorage.preferences` alongside
  the existing `lang`/`opacity`/`widgetOpacity` fields, confirmed on
  every file (sample result:
  `{"lang":"en","opacity":90,"widgetOpacity":100,"colorVision":"deuteranopia"}`).
- Mobile (390×844): re-checked panel bounding box against the viewport
  edges on all 15 files — zero overflow left or right on any page (the
  panel now opens from a right-anchored trigger, so this specifically
  re-verifies the `right: 0; left: auto` mobile anchoring didn't
  reintroduce the overflow the original left-edge build had to avoid).
- Console errors seen this pass (`Fetch API cannot load
  .../index.json ... URL scheme "file" is not supported`, on the
  category pages' own pre-existing pet-loading calls; `blogs-data.js
  failed to load`, on the blog pages) are the `file://` protocol's
  well-known inability to run `fetch()` against local files — a sandbox
  artifact of testing static files directly rather than through a real
  HTTP server, unrelated to the widget resync and present regardless of
  it.
- `axe-core` remains unavailable in this sandbox for the same reason
  documented above (no network access); the same manual ARIA-attribute
  substitute check was re-run against the new Color Vision markup
  specifically (`role="listbox"`/`role="option"` + `aria-selected` on
  `#pcVisionListbox`, `aria-haspopup`/`aria-expanded`/`aria-controls` on
  `#pcVisionTrigger`) and found present and correctly wired on all 15
  files, matching the pattern already used by the language dropdown.
