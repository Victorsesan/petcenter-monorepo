/*
  PetCenter Accessibility Widget
  ------------------------------
  Single embeddable script. Include with one line on any page:
    <script src="/assets/js/accessibility-widget.js" defer><\/script>
  and it builds its own CSS + markup at runtime -- no per-page HTML edits
  needed. This deliberately replaces the earlier approach (Task 9 + the
  Color Vision Accessibility task), where the widget's full style/markup/
  script block was copied verbatim into every page and had to be manually
  re-synced across all of them on every revision (this happened twice
  already during Task 10). One script file means the next revision is a
  one-line <script src> swap on each page instead of a 15-file resync.

  Schema owner: localStorage key "preferences", now extended to:
    {
      lang, opacity, widgetOpacity, colorVision,   -- carried over as-is
      fontSize, contrast, saturation, textSpacing, -- new
      hideImages, highlightLinks, invertColors,    -- new
      bigCursor, readingGuide, widgetPosition,     -- new
      activeProfile                                -- new, UI state only
    }
  `lang` keeps its exact existing meaning/values (en/es/pt/fr/ar/zh-Hans) --
  untouched, since Admin-Project #4 and the i18n runtime both depend on it.

  NOTE on `widgetOpacity`: this field's MEANING has changed from the
  previous build. It used to be real CSS `opacity` on the whole widget
  (trigger + open panel together). Per this revision's brief ("Widget Icon
  Opacity" as its own control under More Settings, separate from the
  panel's background), it now applies ONLY to the trigger button when the
  panel is closed -- the open panel is always fully visible, since a
  half-transparent settings panel while you're actively using it is a
  usability problem the earlier design didn't need to have. The field name
  is kept the same to avoid a second silent schema migration; the meaning
  change is intentional and documented here.

  NOTE on font size: this codebase's CSS is mostly fixed px, not rem, so
  the common "scale root rem" trick most accessibility widgets use would
  barely do anything here. Implemented instead via `zoom` (not
  `transform: scale`, which breaks document flow/scrollbars), which
  actually scales px-based layouts. `zoom` has real but improving browser
  support (Chromium/Edge/Safari 17+/modern Firefox) -- worth re-checking
  against current browser support before this ships, not assumed perfect.
*/
(function () {
  'use strict';

  var PREFS_KEY = 'preferences';
  var ADMIN_DEFAULTS_KEY = 'pc_admin_settings_defaults';

  var DEFAULTS = {
    lang: 'en',
    opacity: 90,
    widgetOpacity: 100,
    colorVision: 'normal',
    theme: 'light',
    fontSize: 100,
    contrast: 'normal',
    saturation: 'normal',
    textSpacing: 'normal',
    hideImages: false,
    highlightLinks: false,
    invertColors: false,
    bigCursor: false,
    readingGuide: false,
    fastReading: false,
    widgetPosition: 'right',
    activeProfile: null
  };

  var LANG_FOLDER_MAP = { en: '/', es: '/es/', pt: '/pt/', fr: '/fr/', ar: '/ar/', 'zh-Hans': '/zh/' };
  var LANG_LABELS = { en: 'English', es: 'Español', pt: 'Português', fr: 'Français', ar: 'العربية', 'zh-Hans': '简体中文' };
  var VISION_LABELS = { normal: 'Normal Vision', deuteranopia: 'Deuteranopia', protanopia: 'Protanopia', tritanopia: 'Tritanopia', grayscale: 'Grayscale' };
  var CONTRAST_LABELS = { normal: 'Normal', dark: 'Dark Contrast', light: 'Light Contrast' };
  var SATURATION_LABELS = { normal: 'Normal', low: 'Low Saturation', high: 'High Saturation', desaturate: 'Desaturate' };
  var SPACING_LABELS = { normal: 'Normal', light: 'Light Spacing', moderate: 'Moderate Spacing', heavy: 'Heavy Spacing' };
  var CONTRAST_CYCLE = ['normal', 'dark', 'light'];
  var SATURATION_CYCLE = ['normal', 'low', 'high', 'desaturate'];
  var SPACING_CYCLE = ['normal', 'light', 'moderate', 'heavy'];

  // ---- Bionic ("Fast") Reading: bolds the first ~50% of each word so the
  // eye can fixate less per word. This is the one control here that isn't
  // a CSS effect -- it has to walk and rewrite real text nodes, then
  // cleanly reverse that rewrite on toggle-off. Verified this specific
  // technique against how real Bionic Reading extensions describe their
  // own approach (word-fixation bolding) rather than inventing one from
  // the screenshot's label alone.
  var BIONIC_CLASS = 'pc-a11y-bionic';
  var BIONIC_SKIP_TAGS = { SCRIPT: 1, STYLE: 1, TEXTAREA: 1, INPUT: 1, NOSCRIPT: 1, IFRAME: 1 };

  function bionicApply(root) {
    if (!root || root.getAttribute('data-bionic-applied') === '1') return;
    var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
      acceptNode: function (node) {
        if (!node.nodeValue || !node.nodeValue.trim()) return NodeFilter.FILTER_REJECT;
        var p = node.parentNode;
        if (!p || BIONIC_SKIP_TAGS[p.tagName] || (p.classList && p.classList.contains(BIONIC_CLASS))) return NodeFilter.FILTER_REJECT;
        return NodeFilter.FILTER_ACCEPT;
      }
    });
    var nodes = [];
    var n;
    while ((n = walker.nextNode())) nodes.push(n);
    nodes.forEach(function (textNode) {
      var parts = textNode.nodeValue.split(/(\s+)/);
      var frag = document.createDocumentFragment();
      parts.forEach(function (part) {
        if (part === '' || /^\s+$/.test(part)) { frag.appendChild(document.createTextNode(part)); return; }
        var boldLen = Math.ceil(part.length * 0.5);
        var b = document.createElement('b');
        b.className = BIONIC_CLASS;
        b.textContent = part.slice(0, boldLen);
        frag.appendChild(b);
        frag.appendChild(document.createTextNode(part.slice(boldLen)));
      });
      textNode.parentNode.replaceChild(frag, textNode);
    });
    root.setAttribute('data-bionic-applied', '1');
  }

  function bionicRemove(root) {
    if (!root || root.getAttribute('data-bionic-applied') !== '1') return;
    root.querySelectorAll('b.' + BIONIC_CLASS).forEach(function (b) {
      var parent = b.parentNode;
      while (b.firstChild) parent.insertBefore(b.firstChild, b);
      parent.removeChild(b);
      parent.normalize();
    });
    root.removeAttribute('data-bionic-applied');
  }

  // Same simulation-verified colors from the Color Vision Accessibility
  // task -- not re-picked here, carried over exactly.
  var VISION_CSS = {
    'deuteranopia,protanopia': {
      '--success': '#009E73', '--success-bg': 'rgba(0,158,115,0.12)',
      '--error': '#D55E00', '--error-bg': 'rgba(213,94,0,0.12)',
      '--warning': '#E69F00', '--warning-bg': 'rgba(230,159,0,0.12)',
      '--info': '#0072B2', '--info-bg': 'rgba(0,114,178,0.12)',
      '--lm-ice': '#0072B2', '--lm-periwinkle': '#E69F00', '--lm-lavender': '#009E73', '--lm-orchid': '#CC79A7', '--lm-pale': '#56B4E9'
    },
    tritanopia: {
      '--info': '#7c3aed', '--info-bg': 'rgba(124,58,237,0.12)',
      '--lm-ice': '#D7263D', '--lm-periwinkle': '#2E7D32', '--lm-lavender': '#F77F00', '--lm-orchid': '#6A4C93', '--lm-pale': '#1B1B1B'
    }
  };

  // ---- Profiles: each is a bundle of the individual controls above. ----
  var PROFILES = {
    'low-vision': { fontSize: 150, contrast: 'dark', bigCursor: true },
    dyslexia: { textSpacing: 'moderate', fontSize: 115, dyslexiaFont: true, fastReading: true, _reduceMotion: true },
    // ADHD: reduces visual clutter/distraction. This is one of two places
    // motion gets reduced -- deliberately not a standalone "Pause
    // Animations" tile (excluded from this build), but a profile is a
    // different UI element, and an ADHD profile that didn't touch motion
    // at all would not actually do what it's named for. Added `contrast:
    // 'dark'` per the user's explicit call to match the reference site's
    // ADHD bundle (Hide Images + High Contrast) using what already
    // existed here rather than building a separate "High Contrast" mode
    // that isn't otherwise needed.
    adhd: { hideImages: true, highlightLinks: true, fastReading: true, contrast: 'dark', _reduceMotion: true },
    'motor-impairment': { bigCursor: true, _largeTargets: true },
    // Seizure & Epileptic: safety-critical. Same reasoning as ADHD above,
    // stronger justification -- a seizure-safety profile that doesn't
    // stop flashing/animated content would be worse than not having one.
    'seizure-epileptic': { _reduceMotion: true, saturation: 'low' }
  };
  var PROFILE_LABELS = {
    'low-vision': 'Low Vision', dyslexia: 'Dyslexia', adhd: 'ADHD',
    'motor-impairment': 'Motor Impairment', 'seizure-epileptic': 'Seizure & Epileptic'
  };

  function readAdminDefaults() {
    try {
      var raw = localStorage.getItem(ADMIN_DEFAULTS_KEY);
      var parsed = raw ? JSON.parse(raw) : {};
      var o = Number(parsed.opacity), w = Number(parsed.widgetOpacity);
      return {
         <= 100) ? o : DEFAULTS.opacity,
         <= 100) ? w : DEFAULTS.widgetOpacity
      };
    } catch (e) { return { opacity: DEFAULTS.opacity, widgetOpacity: DEFAULTS.widgetOpacity }; }
  }

  function getPreferences() {
    var adminDefaults = readAdminDefaults();
    try {
      var raw = localStorage.getItem(PREFS_KEY);
      var parsed = raw ? JSON.parse(raw) : {};
      var out = {};
      for (var k in DEFAULTS) {
        if (k === 'opacity') { out[k] = (typeof parsed.opacity === 'number') ? parsed.opacity : adminDefaults.opacity; continue; }
        if (k === 'widgetOpacity') { out[k] = (typeof parsed.widgetOpacity === 'number') ? parsed.widgetOpacity : adminDefaults.widgetOpacity; continue; }
        out[k] = (parsed[k] !== undefined && parsed[k] !== null) ? parsed[k] : DEFAULTS[k];
      }
      return out;
    } catch (e) {
      var d = {};
      for (var j in DEFAULTS) d[j] = DEFAULTS[j];
      d.opacity = adminDefaults.opacity;
      d.widgetOpacity = adminDefaults.widgetOpacity;
      return d;
    }
  }

  function setPreferences(partial) {
    var current = getPreferences();
    var next = {};
    for (var k in current) next[k] = current[k];
    for (var p in partial) if (Object.prototype.hasOwnProperty.call(partial, p)) next[p] = partial[p];
    try { localStorage.setItem(PREFS_KEY, JSON.stringify(next)); } catch (e) { /* storage unavailable -- in-memory state still applies for this tab */ }
    return next;
  }

  // ======================================================================
  // CSS injection
  // ======================================================================
  var CSS = ''
    + ':root{--pc-a11y-bg-opacity:0.9;--pc-a11y-icon-opacity:1;}'
    // ---- Widget shell ----
    + '.pc-a11y-widget{position:fixed;top:50%;transform:translateY(-50%);z-index:6100;font-family:"Sora",sans-serif;}'
    + '.pc-a11y-widget[data-position="right"]{right:16px;}'
    + '.pc-a11y-widget[data-position="left"]{left:16px;}'
    + '.pc-a11y-trigger{width:52px;height:52px;border-radius:50%;border:none;cursor:pointer;background:#10214b;color:#d7bd88;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(16,33,75,0.35);opacity:var(--pc-a11y-icon-opacity);transition:transform .3s cubic-bezier(.4,0,.2,1),background .3s cubic-bezier(.4,0,.2,1),opacity .2s ease;}'
    + '.pc-a11y-trigger:hover{transform:rotate(20deg);background:#1a2f5c;opacity:1;}'
    + '.pc-a11y-trigger[aria-expanded="true"]{background:#ED7624;color:#fff;opacity:1;}'
    + '.pc-a11y-panel{position:absolute;top:50%;transform:translateY(-50%);width:300px;max-width:calc(100vw - 32px);max-height:min(640px,80vh);display:flex;flex-direction:column;background:rgba(16,33,75,var(--pc-a11y-bg-opacity));color:#f2f2f2;border-radius:14px;box-shadow:0 8px 30px rgba(0,0,0,0.3);z-index:6200;overflow:hidden;}'
    + '.pc-a11y-widget[data-position="right"] .pc-a11y-panel{right:calc(100% + 12px);}'
    + '.pc-a11y-widget[data-position="left"] .pc-a11y-panel{left:calc(100% + 12px);}'
    + '.pc-a11y-panel[hidden]{display:none;}'
    + '.pc-a11y-panel__header{flex-shrink:0;display:flex;align-items:center;justify-content:space-between;padding:14px 16px;font-weight:700;font-size:15px;border-bottom:1px solid rgba(255,255,255,0.1);}'
    + '.pc-a11y-panel__close{background:none;border:none;color:inherit;font-size:20px;line-height:1;cursor:pointer;padding:2px 6px;}'
    // overflow-y:auto + overscroll-behavior:contain + -webkit-overflow-
    // scrolling:touch is this codebase's own documented scrollable-overlay
    // pattern (Section 2.4's mobile hamburger menu, .pc-mobile-menu.open in
    // cats.html and every other page) -- extracted verbatim rather than
    // reinvented, since it's the exact fix already proven here for "touch
    // to scroll an overlay instead scrolls the page behind it," which is
    // precisely the bug reported against this panel.
    + '.pc-a11y-panel__body{overflow-y:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch;padding:8px 10px 14px;flex:1;}'
    // statement button under header
    + '.pc-a11y-statement-btn{margin:10px 16px 0;font-size:11.5px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.16);color:#d7bd88;border-radius:8px;padding:7px 10px;cursor:pointer;text-align:left;flex-shrink:0;}'
    + '.pc-a11y-statement-btn:hover{background:rgba(255,255,255,0.14);}'
    // ---- Accordion sections ----
    + '.pc-a11y-section{border-bottom:1px solid rgba(255,255,255,0.08);}'
    + '.pc-a11y-section:last-child{border-bottom:none;}'
    + '.pc-a11y-section__trigger{width:100%;display:flex;align-items:center;justify-content:space-between;background:none;border:none;color:#f2f2f2;font-size:13px;font-weight:600;padding:12px 6px;cursor:pointer;font-family:inherit;}'
    + '.pc-a11y-section__trigger svg{transition:transform .2s ease;flex-shrink:0;}'
    + '.pc-a11y-section__trigger[aria-expanded="true"] svg{transform:rotate(180deg);}'
    + '.pc-a11y-section__body{max-height:0;overflow:hidden;transition:max-height .25s ease;}'
    + '.pc-a11y-section__body.open{max-height:600px;}'
    + '.pc-a11y-section__inner{padding:2px 6px 14px;}'
    // rows/controls
    + '.pc-a11y-row{margin-bottom:12px;}'
    + '.pc-a11y-row:last-child{margin-bottom:0;}'
    + '.pc-a11y-label{display:block;font-size:12px;margin-bottom:6px;color:#d7bd88;}'
    + '.pc-a11y-desc{font-size:11px;color:#a39b91;margin:-3px 0 8px;line-height:1.4;}'
    // dropdown (reused pattern from language/vision dropdowns)
    + '.pc-a11y-dropdown{position:relative;}'
    + '.pc-a11y-dropdown__trigger{width:100%;display:flex;align-items:center;justify-content:space-between;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.16);color:#f2f2f2;border-radius:8px;padding:8px 12px;font-size:13px;cursor:pointer;font-family:inherit;}'
    + '.pc-a11y-dropdown__panel{position:absolute;top:calc(100% + 6px);left:0;right:0;background:#10214b;border:1px solid rgba(255,255,255,0.14);border-radius:0 8px 8px 8px;list-style:none;margin:0;padding:4px;max-height:180px;overflow-y:auto;z-index:6300;}'
    + '.pc-a11y-dropdown__panel[hidden]{display:none;}'
    + '.pc-a11y-dropdown__panel li{padding:8px 10px;font-size:13px;border-radius:6px;cursor:pointer;}'
    + '.pc-a11y-dropdown__panel li:hover,.pc-a11y-dropdown__panel li[aria-selected="true"]{background:rgba(215,189,136,0.18);}'
    // toggle switch
    + '.pc-a11y-toggle-row{display:flex;align-items:center;justify-content:space-between;}'
    + '.pc-a11y-toggle{position:relative;width:38px;height:22px;flex-shrink:0;}'
    + '.pc-a11y-toggle input{opacity:0;width:100%;height:100%;margin:0;cursor:pointer;}'
    + '.pc-a11y-toggle__track{position:absolute;inset:0;background:rgba(255,255,255,0.2);border-radius:11px;transition:background .2s ease;pointer-events:none;}'
    + '.pc-a11y-toggle__thumb{position:absolute;top:2px;left:2px;width:18px;height:18px;border-radius:50%;background:#fff;transition:transform .2s ease;pointer-events:none;}'
    + '.pc-a11y-toggle input:checked ~ .pc-a11y-toggle__track{background:#ED7624;}'
    + '.pc-a11y-toggle input:checked ~ .pc-a11y-toggle__thumb{transform:translateX(16px);}'
    // cycle button (contrast/saturation/spacing)
    + '.pc-a11y-cycle{width:100%;display:flex;align-items:center;justify-content:space-between;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.16);color:#f2f2f2;border-radius:8px;padding:8px 12px;font-size:13px;cursor:pointer;font-family:inherit;}'
    + '.pc-a11y-cycle:hover{background:rgba(255,255,255,0.14);}'
    + '.pc-a11y-cycle__dots{display:flex;gap:3px;}'
    + '.pc-a11y-cycle__dot{width:5px;height:5px;border-radius:50%;background:rgba(255,255,255,0.25);}'
    + '.pc-a11y-cycle__dot.active{background:#d7bd88;}'
    // font size stepper
    + '.pc-a11y-stepper{display:flex;align-items:center;gap:8px;}'
    + '.pc-a11y-stepper__btn{width:32px;height:32px;border-radius:8px;border:1px solid rgba(255,255,255,0.16);background:rgba(255,255,255,0.08);color:#f2f2f2;font-size:16px;cursor:pointer;flex-shrink:0;}'
    + '.pc-a11y-stepper__btn:hover{background:rgba(255,255,255,0.14);}'
    + '.pc-a11y-stepper__value{flex:1;text-align:center;font-size:13px;font-weight:600;}'
    // profile grid
    + '.pc-a11y-profile-btn{width:100%;text-align:left;display:flex;align-items:center;justify-content:space-between;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.14);color:#f2f2f2;border-radius:8px;padding:9px 12px;font-size:13px;cursor:pointer;margin-bottom:6px;font-family:inherit;}'
    + '.pc-a11y-profile-btn:last-child{margin-bottom:0;}'
    + '.pc-a11y-profile-btn.active{background:rgba(237,118,36,0.22);border-color:#ED7624;color:#fff;}'
    + '.pc-a11y-profile-summary{width:100%;display:flex;align-items:center;justify-content:space-between;background:rgba(237,118,36,0.18);border:1px solid #ED7624;color:#fff;border-radius:8px;padding:9px 12px;font-size:13px;cursor:pointer;font-family:inherit;}'
    + '.pc-a11y-profile-btn__check{opacity:0;}'
    + '.pc-a11y-profile-btn.active .pc-a11y-profile-btn__check{opacity:1;}'
    // slider
    + '.pc-a11y-slider{width:100%;accent-color:#d7bd88;}'
    + '.pc-a11y-slider-value{font-size:11px;color:#a39b91;float:right;margin-top:2px;}'
    // position choice
    + '.pc-a11y-choice-row{display:flex;gap:8px;}'
    + '.pc-a11y-choice{flex:1;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.16);color:#f2f2f2;border-radius:8px;padding:8px;font-size:12.5px;cursor:pointer;font-family:inherit;}'
    + '.pc-a11y-choice.active{background:rgba(237,118,36,0.22);border-color:#ED7624;}'
    + '.pc-a11y-reset-btn{width:100%;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.2);color:#f2f2f2;border-radius:8px;padding:9px;font-size:12.5px;cursor:pointer;margin-top:4px;font-family:inherit;}'
    + '.pc-a11y-reset-btn:hover{background:rgba(255,255,255,0.16);}'
    // ---- Statement panel (slides in over the same widget shell) ----
    + '.pc-a11y-statement{position:absolute;top:50%;transform:translateY(-50%);width:300px;max-width:calc(100vw - 32px);max-height:min(640px,80vh);display:flex;flex-direction:column;background:#10214b;color:#f2f2f2;border-radius:14px;box-shadow:0 8px 30px rgba(0,0,0,0.3);z-index:6250;overflow:hidden;}'
    + '.pc-a11y-widget[data-position="right"] .pc-a11y-statement{right:calc(100% + 12px);}'
    + '.pc-a11y-widget[data-position="left"] .pc-a11y-statement{left:calc(100% + 12px);}'
    + '.pc-a11y-statement[hidden]{display:none;}'
    + '.pc-a11y-statement__header{flex-shrink:0;display:flex;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid rgba(255,255,255,0.1);}'
    + '.pc-a11y-statement__back{background:rgba(255,255,255,0.1);border:none;color:#f2f2f2;border-radius:6px;padding:5px 9px;font-size:12px;cursor:pointer;}'
    + '.pc-a11y-statement__body{overflow-y:auto;padding:14px 16px;font-size:12.5px;line-height:1.6;color:#e8e4dd;}'
    + '.pc-a11y-statement__body h4{font-size:13.5px;color:#d7bd88;margin:14px 0 6px;}'
    + '.pc-a11y-statement__body h4:first-child{margin-top:0;}'
    + '.pc-a11y-statement__body ul{margin:6px 0 0;padding-left:18px;}'
    // mobile
    + '@media (max-width:600px){.pc-a11y-panel,.pc-a11y-statement{top:calc(100% + 10px);transform:none;width:calc(100vw - 48px);}'
    + '.pc-a11y-widget[data-position="right"] .pc-a11y-panel,.pc-a11y-widget[data-position="right"] .pc-a11y-statement{right:0;left:auto;}'
    + '.pc-a11y-widget[data-position="left"] .pc-a11y-panel,.pc-a11y-widget[data-position="left"] .pc-a11y-statement{left:0;right:auto;}}'
    // ---- Global page effects (applied to the PAGE, not the widget) ----
    + '.pc-a11y-widget,.pc-a11y-widget *{letter-spacing:normal!important;word-spacing:normal!important;line-height:normal!important;font-family:"Sora",sans-serif!important;}'
    // ---- Real Dark Mode (Design System Section 19) -- exact documented
    // palette, verbatim, not a filter approximation. This is a genuinely
    // different thing from the "Dark Contrast" quick-filter tile above
    // (which is UserWay's own literal control name, kept as-is): Dark
    // Contrast is a display filter; Dark Mode switches PetCenter's own
    // theme tokens, per the five rules Section 19 documents (own shadow
    // handling, softened whites, #F2F2F2-on-#121212 rather than pure
    // white-on-black, desaturated brand/functional colors, brand colors
    // as accents not fills). Both controls coexist with distinct
    // descriptions so they're not confused for each other.
    + ':root[data-theme="dark"]{--dark-bg:#121212;--dark-surface:#1c1c1c;--dark-text:#F2F2F2;--dark-text-muted:#a39b91;--dark-border:rgba(255,255,255,0.08);--dark-shadow:rgba(0,0,0,0.6);--dark-navy-accent:#6d85c4;--dark-gold-accent:#d7bd88;--dark-orange-cta:#f0925a;--dark-success:#4ade80;--dark-error:#f87171;--dark-warning:#fbbf24;--dark-info:#60a5fa;}'
    // Re-points this PAGE's own base tokens (--bg/--surface/--text/--muted
    // etc, wherever this page already uses them via var()) at the dark
    // equivalents -- covers every spot already variable-driven for free.
    // The DOM-walk pass below (bionicApply-style, real element-level
    // remapping) covers everything still hardcoded to a literal hex value,
    // which is most of this codebase's own CSS (confirmed: 144 hardcoded
    // color declarations vs. 7 uses of the page's own CSS variables --
    // a variable-only approach would have left almost the whole page
    // untouched).
    + '[data-theme="dark"]{--bg:var(--dark-bg);--surface:var(--dark-surface);--text:var(--dark-text);--muted:var(--dark-text-muted);--success:var(--dark-success);--error:var(--dark-error);--warning:var(--dark-warning);--info:var(--dark-info);}'
    + '[data-theme="dark"] #pcA11yPageWrap,[data-theme="dark"] #pcA11yPageWrap *{transition:background-color 0.3s cubic-bezier(.4,0,.2,1),color 0.3s cubic-bezier(.4,0,.2,1),border-color 0.3s cubic-bezier(.4,0,.2,1)!important;}'
    + '[data-a11y-spacing="light"] :not(.pc-a11y-widget):not(.pc-a11y-widget *){letter-spacing:.02em!important;word-spacing:.05em!important;}'
    + '[data-a11y-spacing="moderate"] :not(.pc-a11y-widget):not(.pc-a11y-widget *){letter-spacing:.06em!important;word-spacing:.12em!important;line-height:1.6!important;}'
    + '[data-a11y-spacing="heavy"] :not(.pc-a11y-widget):not(.pc-a11y-widget *){letter-spacing:.4em!important;word-spacing:.6em!important;line-height:2.1!important;}'
    + '[data-a11y-hide-images] img:not(.pc-a11y-widget img){opacity:0.04!important;}'
    + '[data-a11y-highlight-links] a:not(.pc-a11y-widget a){outline:2px solid #ED7624!important;background:rgba(237,118,36,0.15)!important;text-decoration:underline!important;}'
    + '[data-a11y-dyslexia-font] :not(.pc-a11y-widget):not(.pc-a11y-widget *){font-family:Verdana,Tahoma,"Comic Sans MS",sans-serif!important;letter-spacing:.03em!important;}'
    + '[data-a11y-reduce-motion] :not(.pc-a11y-widget):not(.pc-a11y-widget *){animation:none!important;transition:none!important;scroll-behavior:auto!important;}'
    + '[data-a11y-large-targets] a:not(.pc-a11y-widget a),[data-a11y-large-targets] button:not(.pc-a11y-widget button){min-height:44px!important;min-width:44px!important;padding:10px 14px!important;}'
    + 'b.pc-a11y-bionic{font-weight:700!important;}'
    + '#pcA11yReadingGuide{position:fixed;left:0;right:0;height:36px;background:rgba(237,118,36,0.22);border-top:2px solid #ED7624;border-bottom:2px solid #ED7624;pointer-events:none;z-index:6050;display:none;}'
    // colorblind-safe overrides (identical values to the earlier build)
    + '[data-vision="protanopia"],[data-vision="deuteranopia"]{--success:#009E73;--success-bg:rgba(0,158,115,.12);--error:#D55E00;--error-bg:rgba(213,94,0,.12);--warning:#E69F00;--warning-bg:rgba(230,159,0,.12);--info:#0072B2;--info-bg:rgba(0,114,178,.12);--lm-ice:#0072B2;--lm-periwinkle:#E69F00;--lm-lavender:#009E73;--lm-orchid:#CC79A7;--lm-pale:#56B4E9;}'
    + '[data-vision="tritanopia"]{--info:#7c3aed;--info-bg:rgba(124,58,237,.12);--lm-ice:#D7263D;--lm-periwinkle:#2E7D32;--lm-lavender:#F77F00;--lm-orchid:#6A4C93;--lm-pale:#1B1B1B;}';

  function injectCSS() {
    var style = document.createElement('style');
    style.id = 'pc-a11y-styles';
    style.textContent = CSS;
    document.head.appendChild(style);
  }

  // Big cursor -- a plain, high-contrast SVG arrow, large enough to be
  // genuinely easier to track than the OS default. Hotspot pinned at 4,4
  // (top-left of the arrow tip) via the trailing "4 4" in the cursor URL.
  var BIG_CURSOR_SVG = 'data:image/svg+xml;utf8,' + encodeURIComponent(
    '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24">'
    + '<path d="M4 2 L4 20 L9 15.5 L12.5 22 L15 20.5 L11.5 14 L18 14 Z" fill="#10214b" stroke="#ffffff" stroke-width="1.2"/>'
    + '</svg>'
  );

  // ======================================================================
  // HTML injection
  // ======================================================================
  function buildDropdown(id, labelId, currentLabel) {
    return '<div class="pc-a11y-dropdown" id="' + id + '">'
      + '<button type="button" class="pc-a11y-dropdown__trigger" id="' + id + 'Trigger" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="' + labelId + '" aria-controls="' + id + 'Listbox">'
      + '<span id="' + id + 'CurrentLabel">' + currentLabel + '</span>'
      + '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>'
      + '</button>'
      + '<ul class="pc-a11y-dropdown__panel" id="' + id + 'Listbox" role="listbox" aria-labelledby="' + labelId + '" tabindex="-1" hidden></ul>'
      + '</div>';
  }

  function buildWidgetHTML() {
    var wrap = document.createElement('div');
    wrap.className = 'pc-a11y-widget';
    wrap.id = 'pcA11yWidget';
    wrap.innerHTML = ''
      + '<button type="button" class="pc-a11y-trigger" id="pcA11yTrigger" aria-haspopup="true" aria-expanded="false" aria-controls="pcA11yPanel" aria-label="Open accessibility settings">'
      + '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>'
      + '</button>'

      + '<div class="pc-a11y-panel" id="pcA11yPanel" role="dialog" aria-modal="false" aria-label="Accessibility settings" hidden>'
      + '  <div class="pc-a11y-panel__header"><span>Accessibility</span><button type="button" class="pc-a11y-panel__close" id="pcA11yClose" aria-label="Close">&times;</button></div>'
      + '  <button type="button" class="pc-a11y-statement-btn" id="pcA11yStatementBtn">Accessibility Statement</button>'
      + '  <div class="pc-a11y-row pc-a11y-toggle-row" style="padding:10px 16px 0;">'
      + '    <div><label class="pc-a11y-label" style="margin:0;" for="pcDarkModeToggle">Dark Mode</label>'
      + '    <p class="pc-a11y-desc" style="margin:2px 0 0;">Switches the interface to a low-light color scheme.</p></div>'
      + '    <label class="pc-a11y-toggle"><input type="checkbox" id="pcDarkModeToggle"><span class="pc-a11y-toggle__track"></span><span class="pc-a11y-toggle__thumb"></span></label>'
      + '  </div>'
      + '  <div class="pc-a11y-panel__body">'

      // Language
      + '    <div class="pc-a11y-section"><button type="button" class="pc-a11y-section__trigger" aria-expanded="false" data-section="lang">Language<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg></button>'
      + '      <div class="pc-a11y-section__body" data-section-body="lang"><div class="pc-a11y-section__inner">'
      + '        <div class="pc-a11y-row" id="pcLangLabel">' + buildDropdown('pcLangDropdown', 'pcLangLabel', 'English') + '</div>'
      + '      </div></div></div>'

      // Profiles
      + '    <div class="pc-a11y-section"><button type="button" class="pc-a11y-section__trigger" aria-expanded="false" data-section="profiles">Profiles<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg></button>'
      + '      <div class="pc-a11y-section__body" data-section-body="profiles"><div class="pc-a11y-section__inner" id="pcProfileList"></div></div></div>'

      // Visual Adjustments
      + '    <div class="pc-a11y-section"><button type="button" class="pc-a11y-section__trigger" aria-expanded="false" data-section="visual">Visual Adjustments<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg></button>'
      + '      <div class="pc-a11y-section__body" data-section-body="visual"><div class="pc-a11y-section__inner">'
      + '        <div class="pc-a11y-row"><label class="pc-a11y-label" id="pcFontSizeLabel">Font Size</label>'
      + '          <div class="pc-a11y-stepper"><button type="button" class="pc-a11y-stepper__btn" id="pcFontSizeDown" aria-label="Decrease font size">&minus;</button>'
      + '          <span class="pc-a11y-stepper__value" id="pcFontSizeValue" aria-live="polite">100%</span>'
      + '          <button type="button" class="pc-a11y-stepper__btn" id="pcFontSizeUp" aria-label="Increase font size">+</button></div></div>'
      + '        <div class="pc-a11y-row"><label class="pc-a11y-label">Contrast</label>'
      + '          <button type="button" class="pc-a11y-cycle" id="pcContrastCycle"><span id="pcContrastValue">Normal</span><span class="pc-a11y-cycle__dots" id="pcContrastDots"></span></button></div>'
      + '        <div class="pc-a11y-row"><label class="pc-a11y-label">Saturation</label>'
      + '          <button type="button" class="pc-a11y-cycle" id="pcSaturationCycle"><span id="pcSaturationValue">Normal</span><span class="pc-a11y-cycle__dots" id="pcSaturationDots"></span></button></div>'
      + '        <div class="pc-a11y-row"><label class="pc-a11y-label">Text Spacing</label>'
      + '          <button type="button" class="pc-a11y-cycle" id="pcSpacingCycle"><span id="pcSpacingValue">Normal</span><span class="pc-a11y-cycle__dots" id="pcSpacingDots"></span></button></div>'
      + '        <div class="pc-a11y-row pc-a11y-toggle-row"><label class="pc-a11y-label" style="margin:0;" for="pcHideImages">Hide Images</label><label class="pc-a11y-toggle"><input type="checkbox" id="pcHideImages"><span class="pc-a11y-toggle__track"></span><span class="pc-a11y-toggle__thumb"></span></label></div>'
      + '        <div class="pc-a11y-row pc-a11y-toggle-row"><label class="pc-a11y-label" style="margin:0;" for="pcHighlightLinks">Highlight Links</label><label class="pc-a11y-toggle"><input type="checkbox" id="pcHighlightLinks"><span class="pc-a11y-toggle__track"></span><span class="pc-a11y-toggle__thumb"></span></label></div>'
      + '        <div class="pc-a11y-row pc-a11y-toggle-row"><label class="pc-a11y-label" style="margin:0;" for="pcInvertColors">Invert Colors</label><label class="pc-a11y-toggle"><input type="checkbox" id="pcInvertColors"><span class="pc-a11y-toggle__track"></span><span class="pc-a11y-toggle__thumb"></span></label></div>'
      + '        <div class="pc-a11y-row pc-a11y-toggle-row"><label class="pc-a11y-label" style="margin:0;" for="pcDyslexiaFont">Dyslexia Friendly Font</label><label class="pc-a11y-toggle"><input type="checkbox" id="pcDyslexiaFont"><span class="pc-a11y-toggle__track"></span><span class="pc-a11y-toggle__thumb"></span></label></div>'
      + '        <div class="pc-a11y-row pc-a11y-toggle-row"><label class="pc-a11y-label" style="margin:0;" for="pcFastReading">Fast Reading</label><label class="pc-a11y-toggle"><input type="checkbox" id="pcFastReading"><span class="pc-a11y-toggle__track"></span><span class="pc-a11y-toggle__thumb"></span></label></div>'
      + '      </div></div></div>'

      // Color & Vision
      + '    <div class="pc-a11y-section"><button type="button" class="pc-a11y-section__trigger" aria-expanded="false" data-section="vision">Color &amp; Vision<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg></button>'
      + '      <div class="pc-a11y-section__body" data-section-body="vision"><div class="pc-a11y-section__inner">'
      + '        <div class="pc-a11y-row"><label class="pc-a11y-label" id="pcVisionLabel">Color Vision</label>'
      + '        <p class="pc-a11y-desc">Adjusts status colors, alerts, and charts for your vision type.</p>'
      + buildDropdown('pcVisionDropdown', 'pcVisionLabel', 'Normal Vision')
      + '        </div></div></div></div>'

      // Navigation & Motor
      + '    <div class="pc-a11y-section"><button type="button" class="pc-a11y-section__trigger" aria-expanded="false" data-section="motor">Navigation &amp; Motor<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg></button>'
      + '      <div class="pc-a11y-section__body" data-section-body="motor"><div class="pc-a11y-section__inner">'
      + '        <div class="pc-a11y-row pc-a11y-toggle-row"><label class="pc-a11y-label" style="margin:0;" for="pcBigCursor">Big Cursor</label><label class="pc-a11y-toggle"><input type="checkbox" id="pcBigCursor"><span class="pc-a11y-toggle__track"></span><span class="pc-a11y-toggle__thumb"></span></label></div>'
      + '        <div class="pc-a11y-row pc-a11y-toggle-row"><label class="pc-a11y-label" style="margin:0;" for="pcReadingGuide">Reading Guide</label><label class="pc-a11y-toggle"><input type="checkbox" id="pcReadingGuide"><span class="pc-a11y-toggle__track"></span><span class="pc-a11y-toggle__thumb"></span></label></div>'
      + '      </div></div></div>'

      // More Settings
      + '    <div class="pc-a11y-section"><button type="button" class="pc-a11y-section__trigger" aria-expanded="false" data-section="more">More Settings<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg></button>'
      + '      <div class="pc-a11y-section__body" data-section-body="more"><div class="pc-a11y-section__inner">'
      + '        <div class="pc-a11y-row"><label class="pc-a11y-label">Widget Position</label>'
      + '          <div class="pc-a11y-choice-row"><button type="button" class="pc-a11y-choice" id="pcPositionLeft">Left</button><button type="button" class="pc-a11y-choice" id="pcPositionRight">Right</button></div></div>'
      + '        <div class="pc-a11y-row"><label class="pc-a11y-label" for="pcIconOpacitySlider">Widget Icon Opacity</label>'
      + '          <input type="range" id="pcIconOpacitySlider" class="pc-a11y-slider"  aria-valuemax="100" aria-valuenow="100" aria-valuetext="100%"><span class="pc-a11y-slider-value" id="pcIconOpacityValue">100%</span></div>'
      + '        <div class="pc-a11y-row"><label class="pc-a11y-label" for="pcBgOpacitySlider">Widget Background Opacity</label>'
      + '          <input type="range" id="pcBgOpacitySlider" class="pc-a11y-slider"  aria-valuemax="100" aria-valuenow="90" aria-valuetext="90%"><span class="pc-a11y-slider-value" id="pcBgOpacityValue">90%</span></div>'
      + '        <button type="button" class="pc-a11y-reset-btn" id="pcResetAll">Reset All Accessibility Settings</button>'
      + '      </div></div></div>'

      + '  </div>' // panel__body
      + '</div>' // panel

      + '<div class="pc-a11y-statement" id="pcA11yStatement" hidden>'
      + '  <div class="pc-a11y-statement__header"><button type="button" class="pc-a11y-statement__back" id="pcStatementBack">&larr; Back</button><span>Accessibility Statement</span></div>'
      + '  <div class="pc-a11y-statement__body">'
      + '    <h4>Our Commitment</h4>'
      + '    <p>PetCenter is committed to ensuring digital accessibility for people of all abilities. We are continually improving the user experience for everyone and applying the relevant accessibility standards.</p>'
      + '    <h4>Accessibility Standards</h4>'
      + '    <p>This site strives to conform to the following standards:</p>'
      + '    <ul><li><strong>WCAG 2.1 Level AA</strong> &mdash; Web Content Accessibility Guidelines</li><li><strong>ADA</strong> &mdash; Americans with Disabilities Act</li><li><strong>Section 508</strong> &mdash; US Federal accessibility</li></ul>'
      + '    <h4>Feedback</h4>'
      + '    <p>If you encounter any barriers using this site, please contact us so we can address them.</p>'
      + '  </div>'
      + '</div>';
    document.body.appendChild(wrap);

    var guide = document.createElement('div');
    guide.id = 'pcA11yReadingGuide';
    document.body.appendChild(guide);
  }

  // ======================================================================
  // Apply state to the DOM
  // ======================================================================
  function applyAll(prefs) {
    var html = document.documentElement;
    var pageWrap = document.getElementById('pcA11yPageWrap') || html;

    // Widget shell itself
    var widget = document.getElementById('pcA11yWidget');
    if (widget) widget.setAttribute('data-position', prefs.widgetPosition);
    html.style.setProperty('--pc-a11y-bg-opacity', (prefs.opacity / 100).toFixed(2));
    html.style.setProperty('--pc-a11y-icon-opacity', (prefs.widgetOpacity / 100).toFixed(2));

    // Real Dark Mode (Section 19) -- see applyRealDarkMode()'s own comment
    // for exactly what this does and does not cover.
    html.setAttribute('data-theme', prefs.theme);
    applyRealDarkMode(pageWrap, prefs.theme === 'dark');

    // Font size via zoom (see file header note on why zoom, not rem) --
    // applied to the page wrapper, not <html>, so the widget's own size
    // stays constant and predictable regardless of the page's zoom level.
    pageWrap.style.zoom = (prefs.fontSize / 100);

    // Combined CSS filter -- composed once so multiple simultaneous
    // effects (e.g. Grayscale color-vision mode + Saturation + Invert)
    // stack correctly instead of overwriting each other. Applied to the
    // page wrapper only -- see wrapPageContent()'s comment for why this
    // can't safely be <html>.
    var filters = [];
    if (prefs.colorVision === 'grayscale') filters.push('grayscale(1)');
    if (prefs.invertColors) filters.push('invert(1)');
    if (prefs.contrast === 'dark') filters.push('invert(1) hue-rotate(180deg)');
    else if (prefs.contrast === 'light') filters.push('contrast(0.92) brightness(1.08)');
    if (prefs.saturation === 'low') filters.push('saturate(0.5)');
    else if (prefs.saturation === 'high') filters.push('saturate(2)');
    else if (prefs.saturation === 'desaturate') filters.push('saturate(0)');
    pageWrap.style.filter = filters.join(' ');

    // data-attributes for the broad-selector CSS rules -- these stay on
    // <html> since their CSS already excludes the widget via
    // :not(.pc-a11y-widget) selectors, so they don't have the same
    // inheritance problem the inline styles above do.
    html.setAttribute('data-vision', prefs.colorVision === 'grayscale' ? 'normal' : prefs.colorVision);
    if (prefs.textSpacing !== 'normal') html.setAttribute('data-a11y-spacing', prefs.textSpacing); else html.removeAttribute('data-a11y-spacing');
    if (prefs.hideImages) html.setAttribute('data-a11y-hide-images', ''); else html.removeAttribute('data-a11y-hide-images');
    if (prefs.highlightLinks) html.setAttribute('data-a11y-highlight-links', ''); else html.removeAttribute('data-a11y-highlight-links');
    if (prefs.bigCursor) { pageWrap.style.cursor = 'url("' + BIG_CURSOR_SVG + '") 4 4, auto'; } else { pageWrap.style.cursor = ''; }
    if (prefs._dyslexiaFont || prefs.dyslexiaFont) html.setAttribute('data-a11y-dyslexia-font', ''); else html.removeAttribute('data-a11y-dyslexia-font');
    var guide = document.getElementById('pcA11yReadingGuide');
    if (guide) guide.style.display = prefs.readingGuide ? 'block' : 'none';
    if (prefs.fastReading) bionicApply(pageWrap); else bionicRemove(pageWrap);

    // Profile-only effects (reduced motion / large click targets) --
    // active only while a profile that needs them is the last one applied.
    var profileEffects = prefs.activeProfile ? (PROFILES[prefs.activeProfile] || {}) : {};
    if (profileEffects._reduceMotion) html.setAttribute('data-a11y-reduce-motion', ''); else html.removeAttribute('data-a11y-reduce-motion');
    if (profileEffects._largeTargets) html.setAttribute('data-a11y-large-targets', ''); else html.removeAttribute('data-a11y-large-targets');

    // UI reflect
    var langLabelEl = document.getElementById('pcLangDropdownCurrentLabel');
    if (langLabelEl) langLabelEl.textContent = LANG_LABELS[prefs.lang] || 'English';
    document.querySelectorAll('#pcLangDropdownListbox li').forEach(function (o) { o.setAttribute('aria-selected', o.getAttribute('data-value') === prefs.lang ? 'true' : 'false'); });

    var visionLabelEl = document.getElementById('pcVisionDropdownCurrentLabel');
    if (visionLabelEl) visionLabelEl.textContent = VISION_LABELS[prefs.colorVision] || 'Normal Vision';
    document.querySelectorAll('#pcVisionDropdownListbox li').forEach(function (o) { o.setAttribute('aria-selected', o.getAttribute('data-value') === prefs.colorVision ? 'true' : 'false'); });

    var fsVal = document.getElementById('pcFontSizeValue'); if (fsVal) fsVal.textContent = prefs.fontSize + '%';
    var cVal = document.getElementById('pcContrastValue'); if (cVal) cVal.textContent = CONTRAST_LABELS[prefs.contrast];
    renderDots('pcContrastDots', CONTRAST_CYCLE, prefs.contrast);
    var sVal = document.getElementById('pcSaturationValue'); if (sVal) sVal.textContent = SATURATION_LABELS[prefs.saturation];
    renderDots('pcSaturationDots', SATURATION_CYCLE, prefs.saturation);
    var spVal = document.getElementById('pcSpacingValue'); if (spVal) spVal.textContent = SPACING_LABELS[prefs.textSpacing];
    renderDots('pcSpacingDots', SPACING_CYCLE, prefs.textSpacing);

    setToggle('pcHideImages', prefs.hideImages);
    setToggle('pcHighlightLinks', prefs.highlightLinks);
    setToggle('pcInvertColors', prefs.invertColors);
    setToggle('pcDyslexiaFont', !!prefs.dyslexiaFont);
    setToggle('pcFastReading', !!prefs.fastReading);
    setToggle('pcDarkModeToggle', prefs.theme === 'dark');
    setToggle('pcBigCursor', prefs.bigCursor);
    setToggle('pcReadingGuide', prefs.readingGuide);

    var iconSlider = document.getElementById('pcIconOpacitySlider'); var iconVal = document.getElementById('pcIconOpacityValue');
    if (iconSlider) { iconSlider.value = prefs.widgetOpacity; iconSlider.setAttribute('aria-valuenow', prefs.widgetOpacity); iconSlider.setAttribute('aria-valuetext', prefs.widgetOpacity + '%'); }
    if (iconVal) iconVal.textContent = prefs.widgetOpacity + '%';
    var bgSlider = document.getElementById('pcBgOpacitySlider'); var bgVal = document.getElementById('pcBgOpacityValue');
    if (bgSlider) { bgSlider.value = prefs.opacity; bgSlider.setAttribute('aria-valuenow', prefs.opacity); bgSlider.setAttribute('aria-valuetext', prefs.opacity + '%'); }
    if (bgVal) bgVal.textContent = prefs.opacity + '%';

    var leftBtn = document.getElementById('pcPositionLeft'); var rightBtn = document.getElementById('pcPositionRight');
    if (leftBtn && rightBtn) { leftBtn.classList.toggle('active', prefs.widgetPosition === 'left'); rightBtn.classList.toggle('active', prefs.widgetPosition === 'right'); }

    renderProfileList(prefs);
  }

  // Dots fill up to (but not including) the current index -- index 0 is
  // always "normal"/off for every cycle here, so it must render with ZERO
  // filled dots. The original `i <= idx` filled dot 0 even at idx=0,
  // making the default/off state look like something was already
  // selected -- a real bug caught during live testing, not a style
  // choice: reported as "features toggled on by default" when the actual
  // toggle switches were correctly off; this dots-always-show-1-segment
  // rendering was the (still real) part of that report.
  function renderDots(id, cycle, current) {
    var el = document.getElementById(id);
    if (!el) return;
    var idx = cycle.indexOf(current);
    el.innerHTML = cycle.map(function (c, i) { return '<span class="pc-a11y-cycle__dot' + (i > 0 && i <= idx ? ' active' : '') + '"></span>'; }).join('');
  }
  function setToggle(id, checked) { var el = document.getElementById(id); if (el) el.checked = !!checked; }

  // ---- Real Dark Mode (Section 19) -- DOM-level remap for the ~144
  // hardcoded-hex color declarations this codebase's own CSS uses (vs.
  // only 7 uses of its own CSS variables, confirmed by grep before
  // building this rather than assumed). Deliberately scoped to
  // `background-color` only, not `color`: background-color does not
  // inherit, so getComputedStyle reliably reports only what THIS specific
  // element (or a rule targeting it) actually set -- a trustworthy signal
  // to act on. `color` DOES inherit, so the same check can't tell "this
  // element's own dark navy text" from "inherited from a parent," and a
  // blanket rewrite risked breaking more than it fixed (e.g. text that's
  // already light-on-dark inside a button). Text color is instead handled
  // by re-pointing this page's own --text/--muted variables (above) and
  // letting normal CSS inheritance carry that down -- correct for
  // anything that doesn't set its own color, which is most body text.
  // Known limitation, stated plainly rather than glossed over: a heading
  // or accent that hardcodes its own navy/brown text color directly won't
  // be remapped by this pass and may read as lower-contrast against the
  // new dark surfaces -- a real follow-up item, not silently "handled."
  var DARK_LUM_THRESHOLD = 0.6;
  function colorLuminance(rgbStr) {
    var m = rgbStr && rgbStr.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
    if (!m) return null;
    return (0.299 * parseInt(m[1], 10) + 0.587 * parseInt(m[2], 10) + 0.114 * parseInt(m[3], 10)) / 255;
  }
  function applyRealDarkMode(root, enable) {
    if (!root) return;
    if (enable) {
      if (root.getAttribute('data-dark-applied') === '1') return;
      var els = root.querySelectorAll('*');
      els.forEach(function (el) {
        var bg = getComputedStyle(el).backgroundColor;
        var lum = colorLuminance(bg);
        if (lum !== null && lum > DARK_LUM_THRESHOLD) {
          el.setAttribute('data-a11y-orig-bg', el.style.backgroundColor || '\u0000none');
          el.style.setProperty('background-color', 'var(--dark-surface)', 'important');
        }
      });
      root.setAttribute('data-dark-applied', '1');
    } else {
      if (root.getAttribute('data-dark-applied') !== '1') return;
      root.querySelectorAll('[data-a11y-orig-bg]').forEach(function (el) {
        var orig = el.getAttribute('data-a11y-orig-bg');
        if (orig === '\u0000none') el.style.removeProperty('background-color'); else el.style.setProperty('background-color', orig);
        el.removeAttribute('data-a11y-orig-bg');
      });
      root.removeAttribute('data-dark-applied');
    }
  }

  // ======================================================================
  // Wiring
  // ======================================================================
  function wireDropdown(id, options, onSelect) {
    var trigger = document.getElementById(id + 'Trigger');
    var listbox = document.getElementById(id + 'Listbox');
    if (!trigger || !listbox) return;
    listbox.innerHTML = options.map(function (o) {
      return '<li role="option" data-value="' + o.value + '" aria-selected="false" tabindex="0">' + o.label + '</li>';
    }).join('');
    trigger.addEventListener('click', function (e) {
      e.stopPropagation();
      var isOpen = !listbox.hidden;
      closeAllDropdowns();
      listbox.hidden = isOpen;
      trigger.setAttribute('aria-expanded', String(!isOpen));
    });
    listbox.addEventListener('click', function (e) {
      var opt = e.target.closest('li[role="option"]');
      if (!opt) return;
      onSelect(opt.getAttribute('data-value'));
      listbox.hidden = true;
      trigger.setAttribute('aria-expanded', 'false');
    });
    listbox.addEventListener('keydown', function (e) {
      var opts = Array.prototype.slice.call(listbox.querySelectorAll('li[role="option"]'));
      var idx = opts.indexOf(document.activeElement);
      if (e.key === 'ArrowDown') { e.preventDefault(); opts[Math.min(opts.length - 1, idx + 1)].focus(); }
      else if (e.key === 'ArrowUp') { e.preventDefault(); opts[Math.max(0, idx - 1)].focus(); }
      else if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); if (document.activeElement && document.activeElement.click) document.activeElement.click(); }
      else if (e.key === 'Escape') { listbox.hidden = true; trigger.setAttribute('aria-expanded', 'false'); trigger.focus(); }
    });
  }

  function closeAllDropdowns() {
    document.querySelectorAll('.pc-a11y-dropdown__panel').forEach(function (p) { p.hidden = true; });
    document.querySelectorAll('.pc-a11y-dropdown__trigger').forEach(function (t) { t.setAttribute('aria-expanded', 'false'); });
  }

  function wireAccordion() {
    document.querySelectorAll('.pc-a11y-section__trigger').forEach(function (trigger) {
      trigger.addEventListener('click', function () {
        var name = trigger.getAttribute('data-section');
        var body = document.querySelector('[data-section-body="' + name + '"]');
        var isOpen = body.classList.contains('open');
        // Only one section open at a time -- keeps the scrollable panel
        // short and predictable rather than one long page (this was the
        // explicit complaint about the reference sites' desktop widget).
        document.querySelectorAll('.pc-a11y-section__body').forEach(function (b) { b.classList.remove('open'); });
        document.querySelectorAll('.pc-a11y-section__trigger').forEach(function (t) { t.setAttribute('aria-expanded', 'false'); });
        if (!isOpen) { body.classList.add('open'); trigger.setAttribute('aria-expanded', 'true'); }
      });
    });
  }

  function wireProfiles() {
    var list = document.getElementById('pcProfileList');
    if (!list) return;
    list.addEventListener('click', function (e) {
      e.stopPropagation();
      // stopPropagation matters here specifically: this handler replaces
      // list.innerHTML synchronously (detaching e.target from the DOM)
      // before the event finishes bubbling. Without stopping it, the
      // document-level "click outside closes the panel" handler further
      // up would run against a now-parentless e.target, find no
      // #pcA11yWidget ancestor, and incorrectly close the whole panel --
      // a real bug caught via Playwright while testing the profile-
      // collapse feature, not something anticipated in advance.
      // Collapsed-summary row ("X active ▾") -- clicking it re-expands the
      // full picker so the user can switch to a different profile without
      // first having to deactivate the current one. Matches the
      // space-saving pattern UserWay uses once a profile is selected,
      // instead of always showing all 5 buttons expanded (image 10/13 in
      // the reference set -- missed on the first pass, added here).
      if (e.target.closest('#pcProfileSummary')) {
        list.setAttribute('data-expanded', '1');
        renderProfileList(getPreferences());
        return;
      }
      var btn = e.target.closest('.pc-a11y-profile-btn');
      if (!btn) return;
      var key = btn.getAttribute('data-profile');
      var current = getPreferences();
      var partial;
      // Toggle off if the same profile is clicked again -- reverts just
      // the fields that profile set, back to defaults for those fields
      // only (doesn't touch language, opacity, or anything the user set
      // independently of a profile).
      if (current.activeProfile === key) {
        partial = {};
        for (var f in PROFILES[key]) if (f.indexOf('_') !== 0) partial[f] = DEFAULTS[f];
        partial.activeProfile = null;
      } else {
        partial = {};
        for (var f2 in PROFILES[key]) partial[f2] = PROFILES[key][f2];
        partial.activeProfile = key;
      }
      list.setAttribute('data-expanded', '0');
      applyAll(setPreferences(partial));
    });
  }

  function renderProfileList(prefs) {
    var list = document.getElementById('pcProfileList');
    if (!list) return;
    var expanded = list.getAttribute('data-expanded') === '1';
    var activeKey = prefs.activeProfile;
    if (activeKey && !expanded) {
      list.innerHTML = '<button type="button" class="pc-a11y-profile-summary" id="pcProfileSummary">'
        + '<span>' + PROFILE_LABELS[activeKey] + ' active</span>'
        + '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>'
        + '</button>';
    } else {
      list.innerHTML = Object.keys(PROFILE_LABELS).map(function (key) {
        return '<button type="button" class="pc-a11y-profile-btn' + (key === activeKey ? ' active' : '') + '" data-profile="' + key + '">' + PROFILE_LABELS[key] + '<span class="pc-a11y-profile-btn__check">&check;</span></button>';
      }).join('');
    }
  }

  function wireControls() {
    var trigger = document.getElementById('pcA11yTrigger');
    var panel = document.getElementById('pcA11yPanel');
    var closeBtn = document.getElementById('pcA11yClose');
    var statement = document.getElementById('pcA11yStatement');
    var statementBtn = document.getElementById('pcA11yStatementBtn');
    var statementBack = document.getElementById('pcStatementBack');

    // Body-scroll lock matches cats.html's mobile hamburger menu exactly
    // (openMenu()/closeMenu(), "canonical implementation Section 2.4"):
    // document.body.style.overflow = 'hidden' while the overlay is open,
    // restored on close. Extracted for full fidelity with that proven
    // pattern rather than a partial copy. Guarded with a local flag
    // (rather than writing/clearing overflow unconditionally) so this
    // widget can't clobber a lock the page's OWN hamburger menu already
    // holds if both happen to be open at once -- a real conflict this
    // widget introduces by reusing the same direct-overflow technique
    // cats.html's menu uses, not something cats.html itself had to solve
    // since it's the only thing on its page touching body overflow.
    var pcA11yLockedScroll = false;
    function openPanel() {
      panel.hidden = false; trigger.setAttribute('aria-expanded', 'true');
      if (document.body.style.overflow !== 'hidden') { pcA11yLockedScroll = true; document.body.style.overflow = 'hidden'; }
    }
    function closePanel() {
      panel.hidden = true; trigger.setAttribute('aria-expanded', 'false'); statement.hidden = true; closeAllDropdowns();
      if (pcA11yLockedScroll) { document.body.style.overflow = ''; pcA11yLockedScroll = false; }
    }

    trigger.addEventListener('click', function () { panel.hidden ? openPanel() : closePanel(); });
    closeBtn.addEventListener('click', closePanel);
    statementBtn.addEventListener('click', function () { panel.hidden = true; statement.hidden = false; });
    statementBack.addEventListener('click', function () { statement.hidden = true; panel.hidden = false; });
    document.addEventListener('click', function (e) { if (!e.target.closest('#pcA11yWidget')) closePanel(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closePanel(); });

    wireDropdown('pcLangDropdown', Object.keys(LANG_LABELS).map(function (k) { return { value: k, label: LANG_LABELS[k] }; }), function (val) {
      applyAll(setPreferences({ lang: val }));
      window.location.href = LANG_FOLDER_MAP[val] || '/';
    });
    wireDropdown('pcVisionDropdown', Object.keys(VISION_LABELS).map(function (k) { return { value: k, label: VISION_LABELS[k] }; }), function (val) {
      applyAll(setPreferences({ colorVision: val }));
    });

    document.getElementById('pcDarkModeToggle').addEventListener('change', function (e) {
      applyAll(setPreferences({ theme: e.target.checked ? 'dark' : 'light' }));
    });

    document.getElementById('pcFontSizeDown').addEventListener('click', function () {
      var v = Math.max(50, getPreferences().fontSize - 10);
      applyAll(setPreferences({ fontSize: v }));
    });
    document.getElementById('pcFontSizeUp').addEventListener('click', function () {
      var v = Math.min(200, getPreferences().fontSize + 10);
      applyAll(setPreferences({ fontSize: v }));
    });

    function wireCycle(btnId, field, cycle) {
      document.getElementById(btnId).addEventListener('click', function () {
        var current = getPreferences()[field];
        var next = cycle[(cycle.indexOf(current) + 1) % cycle.length];
        var partial = {}; partial[field] = next;
        applyAll(setPreferences(partial));
      });
    }
    wireCycle('pcContrastCycle', 'contrast', CONTRAST_CYCLE);
    wireCycle('pcSaturationCycle', 'saturation', SATURATION_CYCLE);
    wireCycle('pcSpacingCycle', 'textSpacing', SPACING_CYCLE);

    function wireToggle(id, field) {
      document.getElementById(id).addEventListener('change', function (e) {
        var partial = {}; partial[field] = e.target.checked;
        applyAll(setPreferences(partial));
      });
    }
    wireToggle('pcHideImages', 'hideImages');
    wireToggle('pcHighlightLinks', 'highlightLinks');
    wireToggle('pcInvertColors', 'invertColors');
    wireToggle('pcDyslexiaFont', 'dyslexiaFont');
    wireToggle('pcFastReading', 'fastReading');
    wireToggle('pcBigCursor', 'bigCursor');
    wireToggle('pcReadingGuide', 'readingGuide');

    document.getElementById('pcIconOpacitySlider').addEventListener('input', function (e) {
      applyAll(setPreferences({ widgetOpacity: Number(e.target.value) }));
    });
    document.getElementById('pcBgOpacitySlider').addEventListener('input', function (e) {
      applyAll(setPreferences({ opacity: Number(e.target.value) }));
    });
    document.getElementById('pcPositionLeft').addEventListener('click', function () { applyAll(setPreferences({ widgetPosition: 'left' })); });
    document.getElementById('pcPositionRight').addEventListener('click', function () { applyAll(setPreferences({ widgetPosition: 'right' })); });

    document.getElementById('pcResetAll').addEventListener('click', function () {
      try { localStorage.removeItem(PREFS_KEY); } catch (e) { /* ignore */ }
      applyAll(getPreferences());
    });

    document.addEventListener('mousemove', function (e) {
      var guide = document.getElementById('pcA11yReadingGuide');
      if (guide && guide.style.display === 'block') guide.style.top = (e.clientY - 18) + 'px';
    });

    window.addEventListener('storage', function (e) {
      if (e.key !== PREFS_KEY) return;
      applyAll(getPreferences());
    });
  }

  function init() {
    injectCSS();
    wrapPageContent();
    buildWidgetHTML();
    wireAccordion();
    wireProfiles();
    wireControls();
    applyAll(getPreferences());
  }

  // Wraps everything already in <body> (before the widget is injected) in
  // one element, so page-level visual effects (filter, zoom, cursor) can
  // be applied to THAT wrapper instead of <html> or <body>. This was a
  // real bug caught during this build's own Playwright verification: with
  // the filter on <html>, Dark Contrast's invert(1) hue-rotate(180deg)
  // was inherited by the widget too (its own descendant), turning the
  // navy panel light lavender and hard to read -- exactly the opposite of
  // what an accessibility control should do to itself. Attribute-based
  // rules (data-a11y-spacing etc.) don't have this problem since their
  // CSS already scopes with :not(.pc-a11y-widget); inline styles like
  // filter/zoom/cursor have no equivalent "exclude this descendant" CSS
  // mechanism, so the wrapper is the fix.
  function wrapPageContent() {
    // The wrapper needs an opaque background of its own -- without one,
    // any part of the page with no explicit background (very common; most
    // elements are transparent by default) would show the real <body>
    // background showing through from OUTSIDE the filtered subtree,
    // leaving those areas untouched by Dark Contrast/Invert/Grayscale.
    // Caught this during the same verification pass as the widget-
    // inheriting-the-filter bug above -- pixel-sampled a supposedly-
    // inverted light background and found it hadn't actually changed.
    var bodyBg = getComputedStyle(document.body).backgroundColor;
    var htmlBg = getComputedStyle(document.documentElement).backgroundColor;
    var resolvedBg = (bodyBg && bodyBg !== 'rgba(0, 0, 0, 0)') ? bodyBg : ((htmlBg && htmlBg !== 'rgba(0, 0, 0, 0)') ? htmlBg : '#ffffff');
    var wrapper = document.createElement('div');
    wrapper.id = 'pcA11yPageWrap';
    wrapper.style.minHeight = '100vh';
    wrapper.style.background = resolvedBg;
    while (document.body.firstChild) wrapper.appendChild(document.body.firstChild);
    document.body.appendChild(wrapper);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
