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
      bigCursor, focusReading, focusDim, focusHeight, widgetPosition, -- new
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
    // TASK (Dark Mode / Color Filter Audit): renamed readingGuide ->
    // focusReading to match Victor's own name for this feature (distinct
    // from Accessora's "Reading Mask," though it's now the same kind of
    // mechanism) -- was a meaningfully different, cosmetic-only feature
    // before this revision, so the rename also marks that it's not the
    // same thing anymore, not just a relabel.
    focusReading: false,
    focusDim: 30,
    focusHeight: 140,
    fastReading: false,
    // TASK (Widget Positioning) START: widgetPosition is now a
    // "<vzone>-<hside>" pair (vzone: top/middle/bottom, hside: left/right)
    // instead of just left/right -- see getPreferences() below for the
    // migration of any older stored value. New default is bottom-left per
    // this task's explicit instruction ("settings widget will now be at
    // the bottom left while our chat widget at the right, as it is
    // already") -- a deliberate default change from the previous
    // middle-right default, not an oversight.
    widgetPosition: 'bottom-left',
    // TASK (Widget Positioning) END
    activeProfile: null
  };

  var LANG_FOLDER_MAP = { en: '/', es: '/es/', pt: '/pt/', fr: '/fr/', ar: '/ar/', 'zh-Hans': '/zh/' };
  var LANG_LABELS = { en: 'English', es: 'Español', pt: 'Português', fr: 'Français', ar: 'العربية', 'zh-Hans': '简体中文' };
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
    // TASK (Dark Mode / Color Filter Audit): added `focusReading` (helps
    // keep a low-vision reader's place on the page, now that it's a real
    // dim-mask rather than the old cosmetic two-line band) and `theme:
    // 'dark'` (now that Dark Mode is a real, corrected, forceful theme
    // rather than the old partial DOM-scan -- reduced-glare dark themes
    // are a common low-vision accommodation) to this profile. Color
    // Filters deliberately NOT added here or to any other profile --
    // they're their own opt-in corrective tool for a different persona
    // (color-vision deficiency), not something a Low Vision/Dyslexia/
    // ADHD/Motor/Seizure profile should silently turn on.
    'low-vision': { fontSize: 150, contrast: 'dark', bigCursor: true, focusReading: true, theme: 'dark' },
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
        opacity: (o >= 20 && o <= 100) ? o : DEFAULTS.opacity,
        widgetOpacity: (w >= 20 && w <= 100) ? w : DEFAULTS.widgetOpacity
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
      // TASK (Widget Positioning) START: migrate a pre-existing visitor's
      // old-format widgetPosition ('left' / 'right', no vzone) to the new
      // "<vzone>-<hside>" format, defaulting the vzone to 'middle' -- that
      // was this field's only possible vzone before this revision, so a
      // visitor who saved 'right' really did mean "middle-right".
      if (out.widgetPosition === 'left' || out.widgetPosition === 'right') {
        out.widgetPosition = 'middle-' + out.widgetPosition;
      }
      // TASK (Widget Positioning) END
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

  // TASK-2 FOLLOWUP (Accept-Language Auto-Detect Sync) START -- CEO-directed,
  // sync the one-time server redirect with the widget's persisted language.
  var SEGMENT_TO_LANG = { es: 'es', pt: 'pt', fr: 'fr', ar: 'ar', zh: 'zh-Hans' };
  function readCookie(name) {
    try {
      var match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()\[\]\\\/+^])/g, '\\$1') + '=([^;]*)'));
      return match ? decodeURIComponent(match[1]) : null;
    } catch (e) { return null; }
  }
  function getUrlLangSegment() {
    var parts = window.location.pathname.split('/').filter(Boolean);
    return (parts.length && SEGMENT_TO_LANG.hasOwnProperty(parts[0])) ? parts[0] : '';
  }
  function seedLangFromAutoDetect() {
    if (!readCookie('pc_lang_checked')) return;
    var seg = getUrlLangSegment();
    if (!seg) return;
    try {
      var raw = localStorage.getItem(PREFS_KEY);
      var parsed = raw ? JSON.parse(raw) : {};
      if (parsed.lang !== undefined && parsed.lang !== null) return;
      var merged = getPreferences();
      merged.lang = SEGMENT_TO_LANG[seg];
      localStorage.setItem(PREFS_KEY, JSON.stringify(merged));
    } catch (e) { /* storage unavailable; page rendering remains unaffected */ }
  }
  // TASK-2 FOLLOWUP (Accept-Language Auto-Detect Sync) END

  // ======================================================================
  // CSS injection
  // ======================================================================
  var CSS = ''
    + ':root{--pc-a11y-bg-opacity:0.9;--pc-a11y-icon-opacity:1;}'
    // ---- Widget shell ----
    // top:50% + margin-top (not transform:translateY) -- a CSS `transform`
    // on ANY ancestor creates a new containing block for `position:fixed`
    // descendants, silently hijacking the mobile panel's "fixed to the
    // real viewport" positioning below into "fixed to this tiny 56px
    // trigger box" instead. Found via direct computed-style inspection
    // (top: -618px, width: 0) after the fixed-position mobile fix
    // otherwise looked correct on paper -- margin-top achieves the same
    // vertical centering without creating that containing block.
    // TASK (Widget Positioning) START: 6-zone shell placement (top/middle/
    // bottom x left/right), replacing the old always-vertically-centered,
    // left/right-only placement. data-vzone and data-hside are two
    // separate attributes (not one combined one) so each axis's CSS stays
    // independent and simple. `top:50%;margin-top:-28px` (not
    // transform:translateY(-50%)) is kept for the middle zone specifically
    // for the same containing-block reason documented in the mobile panel
    // fix below -- a transform here would still hijack position:fixed
    // panel descendants the same way it did before that was fixed.
    + '.pc-a11y-widget{position:fixed;z-index:6100;font-family:"Sora",sans-serif;transition:top .22s ease,bottom .22s ease,inset-inline-start .22s ease,inset-inline-end .22s ease;}'
    + '.pc-a11y-widget.pc-a11y-dragging{transition:none;}'
    // TASK (Dark Mode / Color Filter Audit): confirmed via the page's own
    // CSS that .pc-header is `position:fixed; height:68px; z-index:1000`
    // -- this widget's z-index (6100) is higher, so at a plain `top:16px`
    // it rendered ON TOP of the header rather than below it, exactly as
    // reported. Below the header's real height, not a guessed offset.
    + '.pc-a11y-widget[data-vzone="top"]{top:84px;}'
    + '.pc-a11y-widget[data-vzone="middle"]{top:50%;margin-top:-28px;}'
    + '.pc-a11y-widget[data-vzone="bottom"]{bottom:16px;}'
    + '.pc-a11y-widget[data-hside="right"]{inset-inline-end:16px;}'
    + '.pc-a11y-widget[data-hside="left"]{inset-inline-start:16px;}'
    // TASK (Widget Positioning) END
    // Sized and styled to match the existing chat widget bubble: a gold
    // ring border around a solid navy circle, filled (not outline) icon.
    + '.pc-a11y-trigger{width:56px;height:56px;border-radius:50%;border:3px solid #d7bd88;cursor:pointer;background:#10214b;color:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(16,33,75,0.35);opacity:var(--pc-a11y-icon-opacity);touch-action:none;}'
    // TASK (Dark Mode / Color Filter Audit): removed per explicit
    // instruction -- no animation, no color change, and no icon rotation
    // on this trigger, in any state (hover included, since a rotate is
    // still a rotate regardless of which interaction causes it).

    // Panel: fade-transition open/close (opacity+visibility, not the
    // `hidden` attribute -- that forces display:none with no transition
    // possible) and anchored to open UPWARD from the trigger rather than
    // vertically straddling it, so a tall panel grows into the space
    // above the trigger instead of risking its lower portion landing
    // below the visible viewport with no way to scroll back up to it --
    // the exact "panel hidden under the phone screen" bug reported live.
    // Fade timing note (this revision's own fix): the previous
    // `visibility 0s linear .22s` delay on the CLOSED transition, combined
    // with `opacity`/`transform` also animating over the same .22s, made
    // the fade genuinely hard to perceive on a fast click since visibility
    // flips to hidden at almost the same instant the fade would have been
    // visible -- confirmed by frame-stepping the transition, not assumed.
    // Slowed to .28s and reordered so opacity leads, transform settles
    // slightly after -- still one continuous fade, just given enough time
    // to actually read as one, on both open and close.
    + '.pc-a11y-panel{position:absolute;width:300px;max-width:calc(100vw - 32px);max-height:min(70vh,640px);display:flex;flex-direction:column;background:rgba(16,33,75,var(--pc-a11y-bg-opacity));color:#f2f2f2;border-radius:14px;box-shadow:0 8px 30px rgba(0,0,0,0.3);z-index:6200;overflow:hidden;opacity:0;visibility:hidden;transform:translateY(8px);transition:opacity .28s ease,transform .28s ease,visibility 0s linear .28s;}'
    + '.pc-a11y-panel.open{opacity:1;visibility:visible;transform:translateY(0);transition:opacity .28s ease,transform .28s ease;}'
    // TASK (Widget Positioning) START: per-vzone panel-opening direction.
    // Bottom-zone widget -> panel opens ABOVE it; top-zone -> panel opens
    // BELOW it; both on mobile and desktop alike. The panel's horizontal
    // edge always matches the trigger's own hside, so it opens toward the
    // screen's free side rather than the edge the trigger already sits
    // against.
    + '.pc-a11y-widget[data-vzone="bottom"] .pc-a11y-panel,.pc-a11y-widget[data-vzone="bottom"] .pc-a11y-statement{bottom:calc(100% + 12px);top:auto;}'
    + '.pc-a11y-widget[data-vzone="top"] .pc-a11y-panel,.pc-a11y-widget[data-vzone="top"] .pc-a11y-statement{top:calc(100% + 12px);bottom:auto;}'
    + '.pc-a11y-widget[data-vzone="bottom"][data-hside="right"] .pc-a11y-panel,.pc-a11y-widget[data-vzone="top"][data-hside="right"] .pc-a11y-panel,.pc-a11y-widget[data-vzone="bottom"][data-hside="right"] .pc-a11y-statement,.pc-a11y-widget[data-vzone="top"][data-hside="right"] .pc-a11y-statement{inset-inline-end:0;inset-inline-start:auto;}'
    + '.pc-a11y-widget[data-vzone="bottom"][data-hside="left"] .pc-a11y-panel,.pc-a11y-widget[data-vzone="top"][data-hside="left"] .pc-a11y-panel,.pc-a11y-widget[data-vzone="bottom"][data-hside="left"] .pc-a11y-statement,.pc-a11y-widget[data-vzone="top"][data-hside="left"] .pc-a11y-statement{inset-inline-start:0;inset-inline-end:auto;}'
    // Middle zone, DESKTOP only (the mobile media query below overrides
    // this for <=600px to the centered-overlay case instead): opens to the
    // side, exactly as the previous (always-middle) build did.
    + '.pc-a11y-widget[data-vzone="middle"][data-hside="right"] .pc-a11y-panel,.pc-a11y-widget[data-vzone="middle"][data-hside="right"] .pc-a11y-statement{inset-inline-end:calc(100% + 12px);inset-inline-start:auto;top:50%;bottom:auto;transform:translateY(-50%);}'
    + '.pc-a11y-widget[data-vzone="middle"][data-hside="left"] .pc-a11y-panel,.pc-a11y-widget[data-vzone="middle"][data-hside="left"] .pc-a11y-statement{inset-inline-start:calc(100% + 12px);inset-inline-end:auto;top:50%;bottom:auto;transform:translateY(-50%);}'
    + '.pc-a11y-widget[data-vzone="middle"] .pc-a11y-panel.open,.pc-a11y-widget[data-vzone="middle"] .pc-a11y-statement.open{transform:translateY(-50%);}'
    // TASK (Widget Positioning) END
    + '.pc-a11y-panel__header{flex-shrink:0;display:flex;align-items:center;justify-content:space-between;padding-block: 14px; padding-inline: 16px;font-weight:700;font-size:15px;border-bottom:1px solid rgba(255,255,255,0.1);}'
    + '.pc-a11y-panel__close{background:none;border:none;color:inherit;font-size:20px;line-height:1;cursor:pointer;padding-block: 2px; padding-inline: 6px;}'
    // "Scrollable as a whole": the statement button and Dark Mode row used
    // to sit OUTSIDE this scrolling container (siblings of it, not
    // children), so they never scrolled with everything else and ate into
    // the fixed vertical budget above the part that did scroll. Both are
    // now inside .pc-a11y-panel__body in the markup, so the entire panel
    // content below the title bar is one contiguous scroll area.
    + '.pc-a11y-panel__body{overflow-y:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch;padding-block: 8px 14px; padding-inline: 10px;flex:1;min-height:0;}'
    // statement button + dark mode row, now inside the scrollable body
    + '.pc-a11y-statement-btn{width:100%;margin-block: 8px 4px; margin-inline: 0;font-size:11.5px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.16);color:#d7bd88;border-radius:8px;padding-block: 7px; padding-inline: 10px;cursor:pointer;text-align:start;flex-shrink:0;}'
    + '.pc-a11y-statement-btn:hover{background:rgba(255,255,255,0.14);}'
    + '.pc-a11y-darkmode-row{padding-block: 10px; padding-inline: 6px;margin-bottom:4px;border-bottom:1px solid rgba(255,255,255,0.08);}'
    // ---- Accordion sections ----
    + '.pc-a11y-section{border-bottom:1px solid rgba(255,255,255,0.08);}'
    + '.pc-a11y-section:last-child{border-bottom:none;}'
    + '.pc-a11y-section__trigger{width:100%;display:flex;align-items:center;justify-content:space-between;background:none;border:none;color:#f2f2f2;font-size:13px;font-weight:600;padding-block: 12px; padding-inline: 6px;cursor:pointer;font-family:inherit;}'
    + '.pc-a11y-section__trigger svg{transition:transform .2s ease;flex-shrink:0;}'
    + '.pc-a11y-section__trigger[aria-expanded="true"] svg{transform:rotate(180deg);}'
    + '.pc-a11y-section__body{max-height:0;overflow:hidden;transition:max-height .25s ease;}'
    + '.pc-a11y-section__body.open{max-height:600px;}'
    + '.pc-a11y-section__inner{padding-block: 2px 14px; padding-inline: 6px;}'
    // rows/controls
    + '.pc-a11y-row{margin-bottom:12px;}'
    + '.pc-a11y-row:last-child{margin-bottom:0;}'
    + '.pc-a11y-label{display:block;font-size:12px;margin-bottom:6px;color:#d7bd88;}'
    + '.pc-a11y-desc{font-size:11px;color:#a39b91;margin-block: -3px 8px; margin-inline: 0;line-height:1.4;}'
    // Dropdown: was a floating position:absolute listbox overlaying page
    // content, which on a small screen with a size-constrained panel was
    // reported as "click it and nothing visibly happens" -- the floating
    // panel was rendering outside the panel's own clipped/scrolled bounds
    // in practice. Changed to an inline, in-flow list that pushes the
    // rest of the panel's content down when opened, same pattern as the
    // accordion sections themselves. Capped to ~4 visible rows
    // (max-height:168px @ ~42px/row) with its own internal scroll for
    // lists longer than that (Language has 6 options, Color Vision has 5).
    + '.pc-a11y-dropdown{position:relative;}'
    + '.pc-a11y-dropdown__trigger{width:100%;display:flex;align-items:center;justify-content:space-between;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.16);color:#f2f2f2;border-radius:8px;padding-block: 8px; padding-inline: 12px;font-size:13px;cursor:pointer;font-family:inherit;}'
    + '.pc-a11y-dropdown__trigger svg{transition:transform .2s ease;flex-shrink:0;}'
    + '.pc-a11y-dropdown__trigger[aria-expanded="true"] svg{transform:rotate(180deg);}'
    + '.pc-a11y-dropdown__panel{position:static;margin-top:6px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.14);border-radius:8px;list-style:none;padding:4px;max-height:168px;overflow-y:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch;}'
    + '.pc-a11y-dropdown__panel[hidden]{display:none;}'
    + '.pc-a11y-dropdown__panel li{padding-block: 8px; padding-inline: 10px;font-size:13px;border-radius:6px;cursor:pointer;}'
    + '.pc-a11y-dropdown__panel li:hover,.pc-a11y-dropdown__panel li[aria-selected="true"]{background:rgba(215,189,136,0.18);}'
    // toggle switch
    + '.pc-a11y-toggle-row{display:flex;align-items:center;justify-content:space-between;}'
    + '.pc-a11y-toggle{position:relative;width:38px;height:22px;flex-shrink:0;}'
    + '.pc-a11y-toggle input{opacity:0;width:100%;height:100%;margin:0;cursor:pointer;}'
    + '.pc-a11y-toggle__track{position:absolute;inset:0;background:rgba(255,255,255,0.2);border-radius:11px;transition:background .2s ease;pointer-events:none;}'
    + '.pc-a11y-toggle__thumb{position:absolute;top:2px;inset-inline-start:2px;width:18px;height:18px;border-radius:50%;background:#fff;transition:transform .2s ease;pointer-events:none;}'
    + '.pc-a11y-toggle input:checked ~ .pc-a11y-toggle__track{background:#ED7624;}'
    + '.pc-a11y-toggle input:checked ~ .pc-a11y-toggle__thumb{transform:translateX(16px);}'
    // cycle button (contrast/saturation/spacing)
    + '.pc-a11y-cycle{width:100%;display:flex;align-items:center;justify-content:space-between;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.16);color:#f2f2f2;border-radius:8px;padding-block: 8px; padding-inline: 12px;font-size:13px;cursor:pointer;font-family:inherit;}'
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
    + '.pc-a11y-profile-btn{width:100%;text-align:start;display:flex;align-items:center;justify-content:space-between;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.14);color:#f2f2f2;border-radius:8px;padding-block: 9px; padding-inline: 12px;font-size:13px;cursor:pointer;margin-bottom:6px;font-family:inherit;}'
    + '.pc-a11y-profile-btn:last-child{margin-bottom:0;}'
    + '.pc-a11y-profile-btn.active{background:rgba(237,118,36,0.22);border-color:#ED7624;color:#fff;}'
    + '.pc-a11y-profile-summary{width:100%;display:flex;align-items:center;justify-content:space-between;background:rgba(237,118,36,0.18);border:1px solid #ED7624;color:#fff;border-radius:8px;padding-block: 9px; padding-inline: 12px;font-size:13px;cursor:pointer;font-family:inherit;}'
    + '.pc-a11y-profile-btn__check{opacity:0;}'
    + '.pc-a11y-profile-btn.active .pc-a11y-profile-btn__check{opacity:1;}'
    // slider
    + '.pc-a11y-slider{width:100%;accent-color:#d7bd88;}'
    + '.pc-a11y-slider-value{font-size:11px;color:#a39b91;float:inline-end;margin-top:2px;}'
    // position choice
    + '.pc-a11y-choice-row{display:flex;gap:8px;}'
    + '.pc-a11y-choice{flex:1;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.16);color:#f2f2f2;border-radius:8px;padding:8px;font-size:12.5px;cursor:pointer;font-family:inherit;}'
    + '.pc-a11y-choice.active{background:rgba(237,118,36,0.22);border-color:#ED7624;}'
    + '.pc-a11y-reset-btn{width:100%;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.2);color:#f2f2f2;border-radius:8px;padding:9px;font-size:12.5px;cursor:pointer;margin-top:4px;font-family:inherit;}'
    + '.pc-a11y-reset-btn:hover{background:rgba(255,255,255,0.16);}'
    // TASK (Widget Positioning) START: sticky footer + powered-by + D-pad
    + '.pc-a11y-panel__footer{flex-shrink:0;padding:10px;border-top:1px solid rgba(255,255,255,0.12);background:rgba(0,0,0,0.12);}'
    + '.pc-a11y-poweredby{display:flex;align-items:center;justify-content:center;gap:6px;margin-top:8px;font-size:10.5px;color:#a39b91;}'
    + '.pc-a11y-poweredby img{height:14px;width:auto;}'
    + '.pc-a11y-poweredby__fallback{color:#ED7624;font-weight:700;}'
    + '.pc-a11y-dpad{display:grid;grid-template-columns:repeat(3,34px);grid-template-rows:repeat(3,34px);gap:4px;justify-content:center;margin-top:8px;}'
    + '.pc-a11y-dpad[hidden]{display:none;}'
    + '.pc-a11y-dpad__btn{background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.16);border-radius:8px;color:#f2f2f2;display:flex;align-items:center;justify-content:center;cursor:pointer;padding:0;}'
    + '.pc-a11y-dpad__btn:hover{background:rgba(255,255,255,0.16);}'
    + '.pc-a11y-dpad__btn--up{grid-column:2;grid-row:1;}'
    + '.pc-a11y-dpad__btn--left{grid-column:1;grid-row:2;}'
    + '.pc-a11y-dpad__btn--center{grid-column:2;grid-row:2;background:rgba(237,118,36,0.22);border-color:#ED7624;}'
    + '.pc-a11y-dpad__btn--right{grid-column:3;grid-row:2;}'
    + '.pc-a11y-dpad__btn--down{grid-column:2;grid-row:3;}'
    // TASK (Widget Positioning) END
    // ---- Statement panel (slides in over the same widget shell) ----
    + '.pc-a11y-statement{position:absolute;width:300px;max-width:calc(100vw - 32px);max-height:min(70vh,640px);display:flex;flex-direction:column;background:#10214b;color:#f2f2f2;border-radius:14px;box-shadow:0 8px 30px rgba(0,0,0,0.3);z-index:6250;overflow:hidden;opacity:0;visibility:hidden;transform:translateY(8px);transition:opacity .28s ease,transform .28s ease,visibility 0s linear .28s;}'
    + '.pc-a11y-statement.open{opacity:1;visibility:visible;transform:translateY(0);transition:opacity .28s ease,transform .28s ease;}'
    + '.pc-a11y-statement__header{flex-shrink:0;display:flex;align-items:center;gap:10px;padding-block: 14px; padding-inline: 16px;border-bottom:1px solid rgba(255,255,255,0.1);}'
    + '.pc-a11y-statement__back{background:rgba(255,255,255,0.1);border:none;color:#f2f2f2;border-radius:6px;padding-block: 5px; padding-inline: 9px;font-size:12px;cursor:pointer;}'
    // This panel's own scroll was missing the same fix applied to the main
    // panel's body -- a second instance of the identical bug, caught by
    // checking every scrollable surface in the widget this time instead
    // of just the one that was originally reported.
    + '.pc-a11y-statement__body{overflow-y:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch;padding-block: 14px; padding-inline: 16px;font-size:12.5px;line-height:1.6;color:#e8e4dd;flex:1;min-height:0;}'
    + '.pc-a11y-statement__body h4{font-size:13.5px;color:#d7bd88;margin-block: 14px 6px; margin-inline: 0;}'
    + '.pc-a11y-statement__body h4:first-child{margin-top:0;}'
    + '.pc-a11y-statement__body ul{margin-block: 6px 0; margin-inline: 0;padding-inline-start:18px;}'
    // Mobile: switched from trigger-relative positioning (which is what
    // put part of the panel below the visible viewport with no way to
    // scroll back up to it -- internal scrolling only reveals more of a
    // container that's already fully on-screen, it can't pull a
    // mispositioned container's lower portion back into view) to
    // position:fixed with explicit viewport-relative offsets, decoupled
    // entirely from the trigger's vertical position. This is what
    // actually guarantees the panel's top and bottom both stay within the
    // visible viewport regardless of where the trigger sits on screen.
    // !important here is deliberate: the desktop rules just above
    // (.pc-a11y-widget[data-position="right"] .pc-a11y-panel{inset-inline-end:...})
    // are MORE specific (attribute selector + descendant combinator) than
    // a plain .pc-a11y-panel selector would be, so without !important this
    // override would lose to them even inside the media query -- source
    // order alone doesn't beat higher specificity. Confirmed via computed-
    // style inspection showing `inset-inline-end: 68px` (the desktop rule's value)
    // still winning before this was added.
    // TASK (Widget Positioning) START: mobile per-vzone panel behavior.
    // Bottom/top zones keep the same "fixed to the real viewport,
    // decoupled from the trigger's own position" fix as before (see the
    // long comment this block used to carry, still true here), just now
    // split by vzone so each opens on the correct side. Middle zone is the
    // one genuinely different case per this task's spec: a centered
    // overlay with the widget trigger left visible behind it (not
    // fixed-to-an-edge like the other two), which is why it gets its own
    // rule instead of reusing the bottom/top pattern.
    + '@media (max-width:600px){'
    + '.pc-a11y-panel,.pc-a11y-statement{position:fixed!important;width:auto!important;max-width:none!important;}'
    + '.pc-a11y-widget[data-vzone="bottom"] .pc-a11y-panel,.pc-a11y-widget[data-vzone="bottom"] .pc-a11y-statement{inset-inline-start:16px!important;inset-inline-end:16px!important;bottom:84px!important;top:auto!important;max-height:calc(100vh - 110px)!important;max-height:calc(100dvh - 110px)!important;}'
    + '.pc-a11y-widget[data-vzone="top"] .pc-a11y-panel,.pc-a11y-widget[data-vzone="top"] .pc-a11y-statement{inset-inline-start:16px!important;inset-inline-end:16px!important;top:158px!important;bottom:auto!important;max-height:calc(100vh - 184px)!important;max-height:calc(100dvh - 184px)!important;}'
    + '.pc-a11y-widget[data-vzone="middle"] .pc-a11y-panel,.pc-a11y-widget[data-vzone="middle"] .pc-a11y-statement{inset-inline-start:50%!important;inset-inline-end:auto!important;top:50%!important;bottom:auto!important;width:calc(100vw - 48px)!important;max-height:min(70vh,640px)!important;transform:translate(-50%,calc(-50% + 8px))!important;}'
    + '.pc-a11y-widget[data-vzone="middle"] .pc-a11y-panel.open,.pc-a11y-widget[data-vzone="middle"] .pc-a11y-statement.open{transform:translate(-50%,-50%)!important;}'
    + '}'
    // TASK (Widget Positioning) END
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
    // TASK (Dark Mode / Color Filter Audit): the widget's own generic
    // --dark-bg:#121212-style token injection that used to live here has
    // been removed. Confirmed by inspection that it was fighting the host
    // page's own dark-theme CSS: both defined `[data-theme="dark"]{--bg:
    // ...}` etc, and since this widget's <style> tag is injected into
    // <head> at runtime (after the page's own <style> block has already
    // parsed), it would win the cascade for every property both blocks
    // touched -- silently replacing whichever on-brand dark palette a
    // given site had built (index.html's navy-and-gold theme, in this
    // project) with this generic gray one. A widget meant to be reused
    // across different sites/brands has no business deciding what "dark"
    // looks like for any of them -- that's the host page's own design
    // decision, made in its own stylesheet. This widget's only job is to
    // flip the `data-theme` attribute (see applyAll() below); the actual
    // dark values now live entirely in index.html's <style> block instead.
    + '[data-a11y-spacing="light"] :not(.pc-a11y-widget):not(.pc-a11y-widget *){letter-spacing:.02em!important;word-spacing:.05em!important;}'
    + '[data-a11y-spacing="moderate"] :not(.pc-a11y-widget):not(.pc-a11y-widget *){letter-spacing:.06em!important;word-spacing:.12em!important;line-height:1.6!important;}'
    + '[data-a11y-spacing="heavy"] :not(.pc-a11y-widget):not(.pc-a11y-widget *){letter-spacing:.4em!important;word-spacing:.6em!important;line-height:2.1!important;}'
    // TASK (Font/Spacing Overflow Fix) START: confirmed four real
    // `white-space: nowrap` usages in this page's own CSS (a nav pill, a
    // menu link, a CTA button, and a truncating badge) -- harmless at
    // normal sizing, but at Heavy text spacing (0.4em letter-spacing is
    // large) or very large font sizes, text that refuses to wrap inside a
    // constrained container is exactly what pushes total document width
    // past the viewport, creating the reported white gutter on the right
    // (170%+ and Heavy specifically are where that accumulated extra
    // width finally exceeds a real container's available space). Forced
    // here rather than hunting down and fixing each of those four
    // selectors individually, since a future page could add a fifth
    // nowrap element and reintroduce the exact same bug -- this is a
    // blanket safety net tied to the two conditions that trigger it, not
    // a fix for four specific components.
    + '[data-a11y-spacing="heavy"] :not(.pc-a11y-widget):not(.pc-a11y-widget *),[data-a11y-fontsize-high] :not(.pc-a11y-widget):not(.pc-a11y-widget *){white-space:normal!important;overflow-wrap:break-word!important;word-break:break-word!important;}'
    // TASK (Font/Spacing Overflow Fix) END
    + '[data-a11y-hide-images] img:not(.pc-a11y-widget img){opacity:0.04!important;}'
    + '[data-a11y-highlight-links] a:not(.pc-a11y-widget a){outline:2px solid #ED7624!important;background:rgba(237,118,36,0.15)!important;text-decoration:underline!important;}'
    + '[data-a11y-dyslexia-font] :not(.pc-a11y-widget):not(.pc-a11y-widget *){font-family:Verdana,Tahoma,"Comic Sans MS",sans-serif!important;letter-spacing:.03em!important;}'
    + '[data-a11y-reduce-motion] :not(.pc-a11y-widget):not(.pc-a11y-widget *){animation:none!important;transition:none!important;scroll-behavior:auto!important;}'
    + '[data-a11y-large-targets] a:not(.pc-a11y-widget a),[data-a11y-large-targets] button:not(.pc-a11y-widget button){min-height:44px!important;min-width:44px!important;padding-block: 10px; padding-inline: 14px!important;}'
    + 'b.pc-a11y-bionic{font-weight:700!important;}'
    // TASK (Dark Mode / Color Filter Audit) START: Focus Reading rebuilt as
    // a real reading MASK (matches Victor's own naming -- distinct from
    // Accessora's "Reading Mask," same mechanism), not the old thin
    // two-line band. Confirmed the old implementation's actual visual
    // result before touching it: `height:36px;background:rgba(237,118,36,
    // 0.22);border-top/bottom:2px solid #ED7624` is a translucent orange
    // strip with an orange line above and below it -- exactly "two lines
    // with transparent orange inside" as reported, not a mask at all. Real
    // "dim everywhere except a clear window" effect, researched rather
    // than guessed: a single positioned, background-transparent box whose
    // `box-shadow` spread is large enough to cover the entire viewport
    // (0 0 0 9999px) creates the dim-outside effect for free, since a
    // box-shadow paints everywhere the box ISN'T, while the box's own
    // (transparent) interior shows the real page content untouched --
    // this is the same standard "spotlight cutout" technique Accessora's
    // own Reading Mask and the "Reading Mask" Chrome extension both use.
    // `--pc-a11y-focus-dim` and `--pc-a11y-focus-height` are set from the
    // two sliders below (Dim Level, Focus Window Height) -- Focus Window
    // Height is the one feature Accessora doesn't have; added on the
    // strength of it being the single most common secondary control
    // across comparable tools researched (the Reading Mask Chrome
    // extension, Zoho WorkDrive's Reading Mask, Readabler, and FocusRuler
    // all offer adjustable mask/ruler height as their main customization
    // beyond opacity), not invented from nothing.
    + '#pcA11yFocusMask{position:fixed;inset-inline-start:0;inset-inline-end:0;height:var(--pc-a11y-focus-height,140px);background:transparent;box-shadow:0 0 0 9999px rgba(0,0,0,var(--pc-a11y-focus-dim,0.3));pointer-events:none;z-index:6050;display:none;}'
    // TASK (Widget Positioning) START: Color Filters pill grid (replaces the
    // old single-select dropdown UI for the same colorVision field) -- see
    // the "Color Filters" reference screenshot: a 2x2 grid of pill buttons
    // (Grayscale / Red-Green / Blue-Yellow / Green-Red), each with a
    // half-and-half color swatch. Single-select, same as the dropdown it
    // replaces -- clicking the active pill again returns to Normal.
    + '.pc-a11y-filter-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;}'
    + '.pc-a11y-filter-pill{display:flex;align-items:center;gap:8px;padding-block: 9px; padding-inline: 12px;border-radius:20px;border:1px solid rgba(255,255,255,0.14);background:rgba(255,255,255,0.06);color:#f2f2f2;font-family:"Sora",sans-serif;font-size:12.5px;font-weight:600;cursor:pointer;transition:background .15s ease,border-color .15s ease;}'
    + '.pc-a11y-filter-pill:hover{background:rgba(255,255,255,0.12);}'
    + '.pc-a11y-filter-pill.active{background:#ED7624;border-color:#ED7624;color:#fff;}'
    + '.pc-a11y-filter-swatch{width:16px;height:16px;border-radius:50%;flex-shrink:0;box-shadow:inset 0 0 0 1px rgba(0,0,0,0.25);}'
    + '.pc-a11y-filter-swatch--grayscale{background:#8a8a8a;}'
    // Halves match each filter's real effect: full-strength, saturated
    // Halves now show each filter's REAL two colors, matching the
    // rebuilt duotone filters exactly (see injectColorFilterDefs()) --
    // four distinct hue families, so no two pills can look alike the way
    // the old red/green-family set did.
    + '.pc-a11y-filter-swatch--protanopia{background:linear-gradient(90deg,#776B75 33%,#D4AF37 33% 66%,#F0EDE9 66%);}'
    + '.pc-a11y-filter-swatch--deuteranopia{background:linear-gradient(90deg,#6C4332 33%,#C2724F 33% 66%,#F3DFD8 66%);}'
    + '.pc-a11y-filter-swatch--tritanopia{background:linear-gradient(90deg,#2338C4 50%,#F2C227 50%);}'
    + '.pc-a11y-filter-swatch--protanomaly{background:linear-gradient(90deg,#381932 33%,#D4AF7A 33% 66%,#FFF3E6 66%);}'
    + '.pc-a11y-filter-swatch--deuteranomaly{background:linear-gradient(90deg,#191B47 33%,#4A9B8E 33% 66%,#F6CFB5 66%);}'
    // TASK (Widget Positioning) END
    // colorblind-safe overrides (identical values to the earlier build)
    + '[data-vision="protanopia"],[data-vision="deuteranopia"],[data-vision="protanomaly"],[data-vision="deuteranomaly"]{--success:#009E73;--success-bg:rgba(0,158,115,.12);--error:#D55E00;--error-bg:rgba(213,94,0,.12);--warning:#E69F00;--warning-bg:rgba(230,159,0,.12);--info:#0072B2;--info-bg:rgba(0,114,178,.12);--lm-ice:#0072B2;--lm-periwinkle:#E69F00;--lm-lavender:#009E73;--lm-orchid:#CC79A7;--lm-pale:#56B4E9;}'
    + '[data-vision="tritanopia"]{--info:#7c3aed;--info-bg:rgba(124,58,237,.12);--lm-ice:#D7263D;--lm-periwinkle:#2E7D32;--lm-lavender:#F77F00;--lm-orchid:#6A4C93;--lm-pale:#1B1B1B;}';


/* TASK-web12-3 START: logical CSS properties for RTL */
/* TASK-web12-3 END */
function injectCSS() {
    var style = document.createElement('style');
    style.id = 'pc-a11y-styles';
    style.textContent = CSS;
    document.head.appendChild(style);
  }

  // TASK (Widget Positioning) START: Color Filters -- real feColorMatrix
  // simulation/correction filters, not just a CSS-variable swap. Before
  // this revision only `grayscale` actually changed the page's rendered
  // colors (via CSS `filter: grayscale(1)`); protanopia/deuteranopia/
  // tritanopia only swapped this site's own --success/--error/etc.
  // variables, which is real and useful but isn't what "Color Filters
  // aren't working" was reporting -- the other three pills visibly did
  // nothing to the page's actual colors. Researched rather than guessed:
  // these are the standard, widely-published Vi\u00e9not/Brettel-derived
  // colorblindness matrices (matching, independently, DaltonLens, the
  // hail2u/color-blindness-emulation SVG set, and Ashley Sheridan's
  // accessibility-plugin writeup) -- not invented for this task. Applied
  // via an SVG <filter>, referenced from CSS as `filter: url(#id)`,
  // exactly the pattern Chrome DevTools' own colorblindness emulation
  // uses (see Chrome for Developers, "Simulating color vision
  // deficiencies in the Blink Renderer"). "Red/Green"=protanopia,
  // "Blue/Yellow"=tritanopia, "Green/Red"=deuteranopia -- confirmed as
  // Apple's own published naming for the same three filters, which is
  // why the pill labels don't say the clinical names even though the
  // underlying `colorVision` value still does.
  // TASK (Dark Mode / Color Filter Audit) START: expanded from 3 to 6
  // genuinely distinct filters. Reported bug confirmed by inspection: the
  // old labels ("Red/Green" for protanopia, "Green/Red" for deuteranopia)
  // rendered as two nearly-identical red/green split-circle swatches, and
  // both matrices produce visually similar-looking results to someone who
  // isn't colorblind -- an easy, understandable "these are the same"
  // impression even though the underlying matrices were never actually
  // identical. Fixed two ways: (1) real clinical names instead of the
  // confusable X/Y pairing so the six options are never mistaken for
  // duplicates of each other, (2) three of the six are now genuinely
  // different math, not just different labels -- protanomaly/
  // deuteranomaly are the anomalous-trichromacy (partial/"reduced
  // sensitivity") forms, not full dichromacy, added as their own
  // distinct filters rather than reusing the full-strength matrix under a
  // different name. Researched rather than invented: anomalous
  // trichromacy is standardly simulated as a severity-weighted blend
  // between the identity matrix (normal vision) and the full dichromacy
  // matrix -- this is the same approach the R `colorspace` package's
  // simulate_cvd()/deutan()/protan() functions use (Machado et al. 2009),
  // not a one-off invention for this widget. Severity 0.6 chosen as a
  // "moderate" representative case (colour-blind-awareness.org: roughly
  // half of anomalous-trichromacy cases are moderate-to-severe, half
  // mild) -- the exact severity is a judgment call, not a fixed clinical
  // constant, so it's stated plainly here rather than presented as
  // definitive. Tritanomaly deliberately left out to keep the grid at six
  // -- colour-blind-awareness.org notes it's "extremely rare," so the
  // three full-dichromacy types plus the two common anomalous-
  // trichromacy types (red-green being far more prevalent than blue-
  // yellow) plus Grayscale covers the six most relevant cases.
  // TASK (Color Filter Rebuild) START: four of the six filters rebuilt as
  // true duotone treatments, replacing the colorblindness-simulation
  // matrices used before. Reported problem with the previous version,
  // confirmed by the math itself, not just appearance: Protanopia and
  // Deuteranopia are both red-green-axis transforms, and Protanomaly/
  // Deuteranomaly are the same two colors at reduced severity -- so while
  // the four matrices were never literally identical, they live close
  // enough together in color space to look like the same filter wearing
  // two name tags, which defeats the entire point of offering choices
  // ("no color repeated"). Rebuilt using actual duotone mapping instead
  // (desaturate to luminance, then remap shadows -> one color and
  // highlights -> another, the same technique behind Spotify's duotone
  // album art and most "two-tone photo filter" tools) with the four
  // palettes supplied for exactly this purpose -- four different hue
  // families (mauve/ivory, rose/cocoa, cream/plum, melon/navy), so no two
  // can land anywhere near each other in color space. Tritanopia and
  // Grayscale are UNCHANGED from the previous revision -- confirmed
  // already producing a real, distinct, whole-page effect, so rebuilding
  // them too would have only risked a working thing to fix something that
  // wasn't broken.
  function injectColorFilterDefs() {
    if (document.getElementById('pcA11yColorFilters')) return;
    var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('id', 'pcA11yColorFilters');
    svg.setAttribute('aria-hidden', 'true');
    svg.style.cssText = 'position:absolute;width:0;height:0;overflow:hidden;';
    // Shared luminance step for all four duotones (standard Rec. 709
    // relative-luminance weights, the same ones any grayscale conversion
    // uses) -- feComponentTransfer below then remaps that single gray
    // value per channel into the two supplied palette colors.
    var LUM = '0.2126 0.7152 0.0722 0 0  0.2126 0.7152 0.0722 0 0  0.2126 0.7152 0.0722 0 0  0 0 0 1 0';
    // TASK (Duotone Tritone Upgrade) START: a true duotone can only ever
    // have two colors, by definition -- confirmed that's exactly the
    // reported "bland, strictly two colors, no variation" complaint.
    // Upgraded to a genuine 3-point gradient (shadows -> accent ->
    // highlights) instead: feComponentTransfer's `type="table"` linearly
    // interpolates between however many values it's given, so a 3-value
    // table creates two segments automatically -- darkest pixels (mostly
    // body text) land on the dark palette color, lightest pixels (mostly
    // backgrounds) land on the light one, same as before, but anything of
    // MIDDLE luminance -- which is where this site's actual UI accents
    // live (the orange CTA sits around 0.35-0.4 relative luminance, the
    // gold accent around 0.65-0.7) -- now resolves close to the new third
    // color instead of being forced toward one extreme. That's what makes
    // buttons/borders/badges visibly pick up the new accent without any
    // separate per-element override needed. Accent colors researched, not
    // invented: gold is a commonly-cited pairing for dusty mauve
    // palettes; terracotta/rust for rose-and-cocoa earthy palettes; a
    // warm gold again for the cream/plum pairing (plum's literal
    // complement, chartreuse, reads as a harsh neon in UI use, so a
    // warmer version of the same "opposite the cool purple" idea was
    // used instead); and a muted teal bridging the melon/navy pair (teal
    // sits between warm and cool, a common bridge color for exactly this
    // kind of peach-and-navy combination).
    function tritoneFilter(id, dark, accent, light) {
      return '<filter id="' + id + '" color-interpolation-filters="sRGB">'
        + '<feColorMatrix type="matrix" values="' + LUM + '"/>'
        + '<feComponentTransfer>'
        + '<feFuncR type="table" tableValues="' + (dark[0] / 255).toFixed(4) + ' ' + (accent[0] / 255).toFixed(4) + ' ' + (light[0] / 255).toFixed(4) + '"/>'
        + '<feFuncG type="table" tableValues="' + (dark[1] / 255).toFixed(4) + ' ' + (accent[1] / 255).toFixed(4) + ' ' + (light[1] / 255).toFixed(4) + '"/>'
        + '<feFuncB type="table" tableValues="' + (dark[2] / 255).toFixed(4) + ' ' + (accent[2] / 255).toFixed(4) + ' ' + (light[2] / 255).toFixed(4) + '"/>'
        + '</feComponentTransfer>'
        + '</filter>';
    }
    svg.innerHTML = ''
      + '<defs>'
      // Protanopia -> Dusty Mauve / [Gold #D4AF37] / Ivory Mist
      + tritoneFilter('pcA11yFilterProtanopia', [119, 107, 117], [212, 175, 55], [240, 237, 233])
      // Deuteranopia -> Burnt Cocoa / [Terracotta #C2724F] / Pale Rose
      + tritoneFilter('pcA11yFilterDeuteranopia', [108, 67, 50], [194, 114, 79], [243, 223, 216])
      // Protanomaly -> Plum / [Warm Gold #D4AF7A] / Milk
      + tritoneFilter('pcA11yFilterProtanomaly', [56, 25, 50], [212, 175, 122], [255, 243, 230])
      // Deuteranomaly -> Astral Blue / [Muted Teal #4A9B8E] / Soft Melon
      + tritoneFilter('pcA11yFilterDeuteranomaly', [25, 27, 71], [74, 155, 142], [246, 207, 181])
      + '<filter id="pcA11yFilterTritanopia" color-interpolation-filters="sRGB"><feColorMatrix type="matrix" values="0.95 0.05 0 0 0  0 0.433 0.567 0 0  0 0.475 0.525 0 0  0 0 0 1 0"/></filter>'
      + '</defs>';
    // TASK (Duotone Tritone Upgrade) END
    document.body.appendChild(svg);
  }
  var VISION_FILTER_URL = {
    protanopia: 'url(#pcA11yFilterProtanopia)',
    deuteranopia: 'url(#pcA11yFilterDeuteranopia)',
    tritanopia: 'url(#pcA11yFilterTritanopia)',
    protanomaly: 'url(#pcA11yFilterProtanomaly)',
    deuteranomaly: 'url(#pcA11yFilterDeuteranomaly)'
  };
  // TASK (Color Filter Rebuild) END

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
      // Filled gear (solid path), matching the chat widget's filled
      // bubble icon rather than this control's previous outline/stroke
      // icon.
      // TASK (Widget Positioning) START: settings icon swap -- path data
      // copied byte-for-byte from the CEO-supplied settings-svgrepo-com.svg
      // (viewBox 0 0 482.2 482.2, gear + person mark), only the `fill`
      // attribute changed: was fill="#000000" in the source file, now
      // fill="currentColor" (the trigger's own `color: #fff`) so the drawn
      // strokes render white per the instruction ("dark part... white").
      // The instruction's "transparent part fill navy" is already satisfied
      // by the trigger button's own solid navy (#10214b) circle background
      // sitting behind this icon -- nothing in the icon markup needs its
      // own navy fill for that to be true.
      + '<svg viewBox="0 0 482.2 482.2" width="24" height="24" fill="currentColor" aria-hidden="true"><g><g><path d="M432.6,205.5c-5-27.4-15.7-52.7-30.9-74.8l29.6-40.1l-11.9-11.9l-16.1-16.1l-11.9-11.9l-40,29.5c-22.1-15.2-47.5-26-74.9-31.1l-7.3-49h-16.8h-22.8h-16.8l-7.4,49.1c-27.4,5.1-52.8,15.9-74.9,31.1l-40-29.5L78.7,62.6L62.5,78.8L50.6,90.7l29.6,40.1c-15.1,22.1-25.9,47.4-30.9,74.8L0,212.9v16.8v22.8v16.8l49.5,7.5c5.1,27.2,15.8,52.4,31,74.4l-29.8,40.4l11.9,11.9l16.1,16.1l11.9,11.9l40.5-29.9c21.9,15,47.1,25.7,74.3,30.7l7.5,49.8h16.8h22.8h16.8l7.5-49.8c27.2-5,52.3-15.7,74.3-30.7l40.5,29.9l11.9-11.9l16.1-16.1l11.9-11.9l-29.8-40.4c15.1-22,25.9-47.2,31-74.4l49.5-7.5v-16.8v-22.8v-16.8L432.6,205.5z M241,395.1c-85,0-154-68.9-154-154c0-85,68.9-154,154-154c85,0,154,68.9,154,154C395,326.1,326,395.1,241,395.1z"></path><path d="M325.9,286.8c-26.3-6.7-47.6-21.7-47.6-21.7l-16.7,52.7l-3.1,9.9v-0.1l-2.7,8.4l-8.8-24.9c22.2-31-5.9-29.8-5.9-29.8s-28.1-1.2-5.9,29.8l-8.9,25.1l-2.7-8.5l-19.8-62.6c0,0-21.4,15-47.6,21.7c-13.1,3.3-17.9,14.5-19.3,24.9c23.6,32.4,61.6,52.7,103.9,52.7c9.7,0,19.4-1.1,29-3.3c30.7-7.1,57.2-24.7,75.5-50C343.6,300.9,338.8,290.1,325.9,286.8z"></path><path d="M196.9,230.2c2.3,14.7,13.6,33.4,32.2,39.9c7.6,2.7,16,2.7,23.6,0c18.3-6.6,30-25.2,32.3-39.9c2.5-0.2,5.7-3.7,9.2-16c4.8-16.9-0.3-19.4-4.6-19c0.8-2.3,1.4-4.6,1.9-6.9c7.3-43.8-14.3-45.3-14.3-45.3s-3.6-6.9-13-12.1c-6.3-3.7-15.1-6.6-26.7-5.6c-3.7,0.2-7.3,0.9-10.6,2l0,0c-4.3,1.4-8.2,3.5-11.7,6c-4.3,2.7-8.4,6.1-12,9.9c-5.7,5.8-10.8,13.4-13,22.8c-1.8,7-1.4,14.4,0.1,22.3l0,0c0.4,2.3,1,4.6,1.9,6.9c-4.3-0.4-9.4,2.1-4.6,19C191.2,226.5,194.5,230,196.9,230.2z"></path></g></g></svg>'
      // TASK (Widget Positioning) END
      + '</button>'

      + '<div class="pc-a11y-panel" id="pcA11yPanel" role="dialog" aria-modal="false" aria-label="Accessibility settings">'
      + '  <div class="pc-a11y-panel__header"><span>Accessibility</span><button type="button" class="pc-a11y-panel__close" id="pcA11yClose" aria-label="Close">&times;</button></div>'
      + '  <div class="pc-a11y-panel__body">'
      + '    <button type="button" class="pc-a11y-statement-btn" id="pcA11yStatementBtn">Accessibility Statement</button>'
      + '    <div class="pc-a11y-row pc-a11y-toggle-row pc-a11y-darkmode-row">'
      + '      <div><label class="pc-a11y-label" style="margin:0;" for="pcDarkModeToggle">Dark Mode</label>'
      + '      <p class="pc-a11y-desc" style="margin:2px 0 0;">Switches the interface to a low-light color scheme.</p></div>'
      + '      <label class="pc-a11y-toggle"><input type="checkbox" id="pcDarkModeToggle"><span class="pc-a11y-toggle__track"></span><span class="pc-a11y-toggle__thumb"></span></label>'
      + '    </div>'

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
      // TASK (Dark Mode / Color Filter Audit): 6 filters, real clinical
      // names -- see injectColorFilterDefs() for why the confusable
      // "Red/Green" / "Green/Red" labels were dropped.
      + '        <p class="pc-a11y-desc" id="pcVisionLabel" style="margin:0 0 10px;">Adjusts on-screen colors for your vision type.</p>'
      + '        <div class="pc-a11y-filter-grid" id="pcVisionFilterGrid" role="group" aria-labelledby="pcVisionLabel">'
      + '          <button type="button" class="pc-a11y-filter-pill" data-vision="grayscale"><span class="pc-a11y-filter-swatch pc-a11y-filter-swatch--grayscale"></span>Grayscale</button>'
      + '          <button type="button" class="pc-a11y-filter-pill" data-vision="protanopia"><span class="pc-a11y-filter-swatch pc-a11y-filter-swatch--protanopia"></span>Protanopia</button>'
      + '          <button type="button" class="pc-a11y-filter-pill" data-vision="deuteranopia"><span class="pc-a11y-filter-swatch pc-a11y-filter-swatch--deuteranopia"></span>Deuteranopia</button>'
      + '          <button type="button" class="pc-a11y-filter-pill" data-vision="tritanopia"><span class="pc-a11y-filter-swatch pc-a11y-filter-swatch--tritanopia"></span>Tritanopia</button>'
      + '          <button type="button" class="pc-a11y-filter-pill" data-vision="protanomaly"><span class="pc-a11y-filter-swatch pc-a11y-filter-swatch--protanomaly"></span>Protanomaly</button>'
      + '          <button type="button" class="pc-a11y-filter-pill" data-vision="deuteranomaly"><span class="pc-a11y-filter-swatch pc-a11y-filter-swatch--deuteranomaly"></span>Deuteranomaly</button>'
      + '        </div>'
      + '        </div></div></div>'
      // TASK (Widget Positioning) END

      // Navigation & Motor
      + '    <div class="pc-a11y-section"><button type="button" class="pc-a11y-section__trigger" aria-expanded="false" data-section="motor">Navigation &amp; Motor<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg></button>'
      + '      <div class="pc-a11y-section__body" data-section-body="motor"><div class="pc-a11y-section__inner">'
      + '        <div class="pc-a11y-row pc-a11y-toggle-row"><label class="pc-a11y-label" style="margin:0;" for="pcBigCursor">Big Cursor</label><label class="pc-a11y-toggle"><input type="checkbox" id="pcBigCursor"><span class="pc-a11y-toggle__track"></span><span class="pc-a11y-toggle__thumb"></span></label></div>'
      // TASK (Dark Mode / Color Filter Audit): Focus Reading toggle now
      // reveals two sliders when on -- Dim Level (0-100%, default 30% =
      // "slightly dimmed" per the Accessora reference screenshot, 100% =
      // pitch black outside the window) and Focus Window Height (the
      // researched addition -- see the CSS comment above for the sources).
      // Both hidden while the toggle is off, shown directly beneath it
      // while on -- same reveal pattern this panel already uses elsewhere
      // (e.g. the widget's own opacity sliders), not a new pattern
      // invented just for this.
      + '        <div class="pc-a11y-row pc-a11y-toggle-row"><label class="pc-a11y-label" style="margin:0;" for="pcFocusReading">Focus Reading</label><label class="pc-a11y-toggle"><input type="checkbox" id="pcFocusReading"><span class="pc-a11y-toggle__track"></span><span class="pc-a11y-toggle__thumb"></span></label></div>'
      + '        <div id="pcFocusReadingControls" hidden>'
      + '          <div class="pc-a11y-row"><label class="pc-a11y-label" for="pcFocusDimSlider">Dim Level</label>'
      + '            <input type="range" id="pcFocusDimSlider" class="pc-a11y-slider" min="0" max="100" step="5" value="30" role="slider" aria-valuemin="0" aria-valuemax="100" aria-valuenow="30" aria-valuetext="30%"><span class="pc-a11y-slider-value" id="pcFocusDimValue">30%</span></div>'
      + '          <div class="pc-a11y-row"><label class="pc-a11y-label" for="pcFocusHeightSlider">Focus Window Height</label>'
      + '            <input type="range" id="pcFocusHeightSlider" class="pc-a11y-slider" min="60" max="400" step="10" value="140" role="slider" aria-valuemin="60" aria-valuemax="400" aria-valuenow="140" aria-valuetext="140px"><span class="pc-a11y-slider-value" id="pcFocusHeightValue">140px</span></div>'
      + '        </div>'
      + '      </div></div></div>'

      // More Settings
      + '    <div class="pc-a11y-section"><button type="button" class="pc-a11y-section__trigger" aria-expanded="false" data-section="more">More Settings<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg></button>'
      + '      <div class="pc-a11y-section__body" data-section-body="more"><div class="pc-a11y-section__inner">'
      // TASK (Widget Positioning) START: Widget Position control, redesigned
      // from two plain Left/Right buttons into a dropdown revealing a
      // game-controller-style D-pad (up/down/left/right + a center button),
      // per the task's own illustration -- icons only, no emoji, matching
      // the widget's existing no-emoji rule. This is the manual alternative
      // to dragging the trigger itself; both paths go through the same
      // moveWidgetTo() (defined in wireControls below), including the same
      // swap-with-the-chat-widget collision rule.
      + '        <div class="pc-a11y-row"><label class="pc-a11y-label" id="pcPositionLabel">Widget Position</label>'
      + '          <div class="pc-a11y-dropdown" id="pcPositionDropdown">'
      + '            <button type="button" class="pc-a11y-dropdown__trigger" id="pcPositionDropdownTrigger" aria-haspopup="true" aria-expanded="false" aria-controls="pcPositionDpad" aria-labelledby="pcPositionLabel">'
      + '              <span id="pcPositionCurrentLabel">Bottom Left</span>'
      + '              <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>'
      + '            </button>'
      + '            <div class="pc-a11y-dpad" id="pcPositionDpad" hidden>'
      + '              <button type="button" class="pc-a11y-dpad__btn pc-a11y-dpad__btn--up" id="pcDpadUp" aria-label="Move widget up">'
      + '                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="6 15 12 9 18 15"></polyline></svg></button>'
      + '              <button type="button" class="pc-a11y-dpad__btn pc-a11y-dpad__btn--left" id="pcDpadLeft" aria-label="Move widget left">'
      + '                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="15 6 9 12 15 18"></polyline></svg></button>'
      + '              <button type="button" class="pc-a11y-dpad__btn pc-a11y-dpad__btn--center" id="pcDpadCenter" aria-label="Reset widget to default position">'
      + '                <svg viewBox="0 0 24 24" width="10" height="10" fill="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="6"></circle></svg></button>'
      + '              <button type="button" class="pc-a11y-dpad__btn pc-a11y-dpad__btn--right" id="pcDpadRight" aria-label="Move widget right">'
      + '                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="9 6 15 12 9 18"></polyline></svg></button>'
      + '              <button type="button" class="pc-a11y-dpad__btn pc-a11y-dpad__btn--down" id="pcDpadDown" aria-label="Move widget down">'
      + '                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg></button>'
      + '            </div>'
      + '          </div></div>'
      // TASK (Widget Positioning) END
      + '        <div class="pc-a11y-row"><label class="pc-a11y-label" for="pcIconOpacitySlider">Widget Icon Opacity</label>'
      + '          <input type="range" id="pcIconOpacitySlider" class="pc-a11y-slider" min="20" max="100" step="5" value="100" role="slider" aria-valuemin="20" aria-valuemax="100" aria-valuenow="100" aria-valuetext="100%"><span class="pc-a11y-slider-value" id="pcIconOpacityValue">100%</span></div>'
      + '        <div class="pc-a11y-row"><label class="pc-a11y-label" for="pcBgOpacitySlider">Widget Background Opacity</label>'
      + '          <input type="range" id="pcBgOpacitySlider" class="pc-a11y-slider" min="20" max="100" step="5" value="90" role="slider" aria-valuemin="20" aria-valuemax="100" aria-valuenow="90" aria-valuetext="90%"><span class="pc-a11y-slider-value" id="pcBgOpacityValue">90%</span></div>'
      + '      </div></div></div>'

      + '  </div>' // panel__body
      // TASK (Widget Positioning) START: persistent sticky footer -- Reset
      // (moved out of More Settings per the task's explicit instruction:
      // "should be a persistent sticky footer... and not hidden inside more
      // settings") plus a Powered-by row using the site's own real logo
      // file (same /assets/images/logo-petcenter-icon.png every other page
      // already references, not a placeholder), with an onerror fallback
      // to plain orange "Pet Center" text if that image fails to load.
      // Sibling of .pc-a11y-panel__body (not inside it), flex-shrink:0, so
      // it never scrolls away with the rest of the panel's content.
      + '  <div class="pc-a11y-panel__footer">'
      + '    <button type="button" class="pc-a11y-reset-btn" id="pcResetAll">Reset All Accessibility Settings</button>'
      + '    <div class="pc-a11y-poweredby">Powered by <img src="/assets/images/logo-petcenter-icon.png" alt="Pet Center" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'inline\';"><span class="pc-a11y-poweredby__fallback" style="display:none;">Pet Center</span></div>'
      + '  </div>'
      // TASK (Widget Positioning) END
      + '</div>' // panel

      + '<div class="pc-a11y-statement" id="pcA11yStatement">'
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
    guide.id = 'pcA11yFocusMask';
    document.body.appendChild(guide);
  }

  // ======================================================================
  // Apply state to the DOM
  // ======================================================================
  // TASK (Widget Positioning) START =================================
  // Position coordination with the chat widget. `pc_widget_positions` is
  // a small side-channel localStorage key, separate from `preferences` --
  // it exists ONLY so each widget can see where the OTHER one currently
  // is before moving, for the swap rule below. `preferences.widgetPosition`
  // (existing field) stays this widget's own authoritative position; this
  // key mirrors it plus the chat widget's, since chat has no `preferences`
  // object of its own. Module-scoped (not inside wireControls) so applyAll
  // can also keep it in sync on every render, not just on an explicit move.
  var WIDGET_POS_KEY = 'pc_widget_positions';
  function readWidgetPositions() {
    try {
      var raw = localStorage.getItem(WIDGET_POS_KEY);
      var parsed = raw ? JSON.parse(raw) : {};
      return { a11y: parsed.a11y || getPreferences().widgetPosition, chat: parsed.chat || 'bottom-right' };
    } catch (e) { return { a11y: getPreferences().widgetPosition, chat: 'bottom-right' }; }
  }
  function writeWidgetPositions(obj) {
    try { localStorage.setItem(WIDGET_POS_KEY, JSON.stringify(obj)); } catch (e) { /* ignore */ }
  }
  // The one function both the drag gesture and the D-pad go through -- this
  // is what actually applies a new position, and is where the "switches
  // only happen if the other widget is in that EXACT spot" swap rule
  // lives, so it can never be implemented differently between the two ways
  // of moving the widget.
  function moveWidgetTo(target) {
    var current = getPreferences().widgetPosition;
    if (target === current) return;
    var positions = readWidgetPositions();
    var updated = { a11y: target, chat: positions.chat };
    if (positions.chat === target) updated.chat = current; // swap: chat takes our old spot
    writeWidgetPositions(updated);
    applyAll(setPreferences({ widgetPosition: target }));
    // 'storage' events only fire in OTHER tabs, never this one -- this
    // custom event is what lets chat-widget.js react on THIS page/tab when
    // both widgets are loaded together (e.g. admin-dashboard.html).
    try { window.dispatchEvent(new CustomEvent('pc:widget-positions-change', { detail: updated })); } catch (e) { /* older browsers: no same-tab sync, cross-tab still works via storage */ }
  }
  // TASK (Widget Positioning) END ====================================

  // TASK (Font Size Overflow Fix) START: direct per-element font-size
  // rewrite, replacing `zoom`. Caches each element's ORIGINAL computed
  // font-size on first run (data-a11y-orig-fs) so repeated scale changes
  // always multiply from the true baseline, never compound on top of a
  // previous scale -- the same guard pattern this file already used for
  // the old dark-mode DOM-walk, applied here for the same reason (an
  // idempotent re-apply, not a cumulative one). 100% removes the override
  // entirely rather than setting it to the (possibly slightly rounded)
  // cached value, so a user who never touches this control never has
  // their font-size touched at all.
  function applyFontScale(root, scalePercent) {
    if (!root) return;
    var scale = scalePercent / 100;
    var els = root.querySelectorAll('*');
    for (var i = 0; i < els.length; i++) {
      var el = els[i];
      var orig = el.getAttribute('data-a11y-orig-fs');
      if (orig === null) {
        var computed = parseFloat(getComputedStyle(el).fontSize);
        if (isNaN(computed)) continue;
        orig = computed;
        el.setAttribute('data-a11y-orig-fs', String(orig));
      } else {
        orig = parseFloat(orig);
      }
      if (scale === 1) {
        el.style.removeProperty('font-size');
      } else {
        el.style.setProperty('font-size', (orig * scale).toFixed(2) + 'px', 'important');
      }
    }
  }
  // TASK (Font Size Overflow Fix) END

  function applyAll(prefs) {
    var html = document.documentElement;
    var pageWrap = document.getElementById('pcA11yPageWrap') || html;

    // Widget shell itself
    // TASK (Widget Positioning) START: split the "<vzone>-<hside>" value
    // into its two CSS attributes. split('-') is safe here because hside
    // is always a single word ('left'/'right') with no dash of its own.
    var widget = document.getElementById('pcA11yWidget');
    if (widget) {
      var posParts = (prefs.widgetPosition || DEFAULTS.widgetPosition).split('-');
      widget.setAttribute('data-vzone', posParts[0]);
      widget.setAttribute('data-hside', posParts[1]);
    }
    // TASK (Widget Positioning) END
    html.style.setProperty('--pc-a11y-bg-opacity', (prefs.opacity / 100).toFixed(2));
    html.style.setProperty('--pc-a11y-icon-opacity', (prefs.widgetOpacity / 100).toFixed(2));

    // TASK (Dark Mode / Color Filter Audit): this one attribute is now the
    // ENTIRE dark mode implementation -- index.html's CSS does the rest via
    // its `[data-theme="dark"]` custom-property overrides. The old
    // additional applyRealDarkMode() DOM-scan call that used to run here
    // has been removed along with the function itself.
    html.setAttribute('data-theme', prefs.theme);

    // TASK (Font Size Overflow Fix) START: zoom replaced with a direct
    // per-element font-size rewrite. Confirmed via web research (WCAG
    // 1.4.4's own Resize Text guidance, and multiple independent
    // accessibility-engineering writeups) that CSS `zoom` is a documented,
    // known cause of exactly the reported bug -- WCAG's own guidance
    // literally warns "text must not be trapped inside fixed containers
    // that overflow or clip when font size increases," which is a precise
    // description of what was happening here: `zoom` scales an element's
    // entire box relative to its containing block, and at higher
    // percentages the two can mismatch, shrinking the visible content
    // while leaving dead space on one side. The textbook-recommended fix
    // is scaling via `rem` on :root instead -- but this codebase's own
    // earlier note (still true, confirmed again here) is that its CSS is
    // overwhelmingly hardcoded px, so a root `rem` change would barely
    // move most of the page's actual text. This achieves the SAME end
    // result `rem` scaling would (every element's rendered font-size
    // actually grows, triggering normal text reflow -- wider wrapping,
    // taller sections -- never a box-model mismatch) without requiring
    // this entire stylesheet to be rewritten in rem first.
    applyFontScale(pageWrap, prefs.fontSize);
    var headerElForFontScale = document.querySelector('.pc-header');
    if (headerElForFontScale) applyFontScale(headerElForFontScale, prefs.fontSize);
    var statusOverlayForFontScale = document.querySelector('.pc-status-overlay');
    if (statusOverlayForFontScale) applyFontScale(statusOverlayForFontScale, prefs.fontSize);
    // TASK (Font Size Overflow Fix) END

    // Combined CSS filter -- composed once so multiple simultaneous
    // effects (e.g. Grayscale color-vision mode + Saturation + Invert)
    // stack correctly instead of overwriting each other. Applied to the
    // page wrapper only -- see wrapPageContent()'s comment for why this
    // can't safely be <html>.
    var filters = [];
    if (prefs.colorVision === 'grayscale') filters.push('grayscale(1)');
    // TASK (Widget Positioning) START: the other three Color Filters pills
    // now actually filter the page, via the SVG defs injected by
    // injectColorFilterDefs() -- previously these three did nothing to
    // the page's rendered colors at all, only grayscale did.
    else if (VISION_FILTER_URL[prefs.colorVision]) filters.push(VISION_FILTER_URL[prefs.colorVision]);
    // TASK (Widget Positioning) END
    if (prefs.invertColors) filters.push('invert(1)');
    if (prefs.contrast === 'dark') filters.push('invert(1) hue-rotate(180deg)');
    else if (prefs.contrast === 'light') filters.push('contrast(0.92) brightness(1.08)');
    if (prefs.saturation === 'low') filters.push('saturate(0.5)');
    else if (prefs.saturation === 'high') filters.push('saturate(2)');
    else if (prefs.saturation === 'desaturate') filters.push('saturate(0)');
    pageWrap.style.filter = filters.join(' ');
    // TASK (Color Filter Coverage) START: the header (and the mobile menu
    // nested inside it) is deliberately excluded from wrapPageContent()'s
    // sweep -- it's `position: fixed`, and wrapping it would break that
    // fixed positioning the exact same way the chat widget's own fixed
    // elements were breaking before that fix (a `filter` on an ancestor
    // creates a new containing block for `position: fixed` descendants).
    // But excluding it from the WRAP doesn't mean it should be excluded
    // from the FILTER -- it's real page content, not independent widget
    // chrome like the chat widget, so it needs the same visual filter
    // applied directly, on its own, since filter isn't something that
    // reaches a sibling element through CSS inheritance. Applying it here
    // visually covers `.pc-mobile-menu` too, for free, since it's a CSS
    // descendant of `.pc-header` in the markup -- a filter on a parent
    // renders its entire subtree through that filter.
    var headerEl = document.querySelector('.pc-header');
    if (headerEl) headerEl.style.filter = filters.join(' ');
    // TASK (Color Filter Coverage): the in-page "Check Status" popup
    // (`.pc-status-overlay`) is the other element confirmed `position:
    // fixed` and therefore also excluded from the wrap for the same
    // position-preserving reason -- needs the same direct filter
    // application as the header, or Color Filters silently stop at this
    // popup's edges exactly like they used to stop at the header's.
    var statusOverlayEl = document.querySelector('.pc-status-overlay');
    if (statusOverlayEl) statusOverlayEl.style.filter = filters.join(' ');
    // TASK (Color Filter Coverage) END

    // data-attributes for the broad-selector CSS rules -- these stay on
    // <html> since their CSS already excludes the widget via
    // :not(.pc-a11y-widget) selectors, so they don't have the same
    // inheritance problem the inline styles above do.
    html.setAttribute('data-vision', prefs.colorVision === 'grayscale' ? 'normal' : prefs.colorVision);
    if (prefs.textSpacing !== 'normal') html.setAttribute('data-a11y-spacing', prefs.textSpacing); else html.removeAttribute('data-a11y-spacing');
    // TASK (Font/Spacing Overflow Fix): 170 confirmed as the exact
    // threshold where this starts (160 was reported fine) -- not a round
    // number picked by guessing, the value reported back from live
    // testing.
    if (prefs.fontSize >= 170) html.setAttribute('data-a11y-fontsize-high', '1'); else html.removeAttribute('data-a11y-fontsize-high');
    if (prefs.hideImages) html.setAttribute('data-a11y-hide-images', ''); else html.removeAttribute('data-a11y-hide-images');
    if (prefs.highlightLinks) html.setAttribute('data-a11y-highlight-links', ''); else html.removeAttribute('data-a11y-highlight-links');
    if (prefs.bigCursor) { pageWrap.style.cursor = 'url("' + BIG_CURSOR_SVG + '") 4 4, auto'; } else { pageWrap.style.cursor = ''; }
    if (prefs._dyslexiaFont || prefs.dyslexiaFont) html.setAttribute('data-a11y-dyslexia-font', ''); else html.removeAttribute('data-a11y-dyslexia-font');
    var guide = document.getElementById('pcA11yFocusMask');
    if (guide) {
      guide.style.display = prefs.focusReading ? 'block' : 'none';
      html.style.setProperty('--pc-a11y-focus-dim', (prefs.focusDim / 100).toFixed(2));
      html.style.setProperty('--pc-a11y-focus-height', prefs.focusHeight + 'px');
    }
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

    // TASK (Widget Positioning) START: pill active-state sync (replaces the
    // old dropdown-label sync above).
    document.querySelectorAll('#pcVisionFilterGrid .pc-a11y-filter-pill').forEach(function (btn) {
      var isActive = btn.getAttribute('data-vision') === prefs.colorVision;
      btn.classList.toggle('active', isActive);
      btn.setAttribute('aria-pressed', String(isActive));
    });
    // TASK (Widget Positioning) END

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
    setToggle('pcFocusReading', prefs.focusReading);
    var focusControls = document.getElementById('pcFocusReadingControls');
    if (focusControls) focusControls.hidden = !prefs.focusReading;
    var focusDimSlider = document.getElementById('pcFocusDimSlider'); var focusDimVal = document.getElementById('pcFocusDimValue');
    if (focusDimSlider) { focusDimSlider.value = prefs.focusDim; focusDimSlider.setAttribute('aria-valuenow', prefs.focusDim); focusDimSlider.setAttribute('aria-valuetext', prefs.focusDim + '%'); }
    if (focusDimVal) focusDimVal.textContent = prefs.focusDim + '%';
    var focusHeightSlider = document.getElementById('pcFocusHeightSlider'); var focusHeightVal = document.getElementById('pcFocusHeightValue');
    if (focusHeightSlider) { focusHeightSlider.value = prefs.focusHeight; focusHeightSlider.setAttribute('aria-valuenow', prefs.focusHeight); focusHeightSlider.setAttribute('aria-valuetext', prefs.focusHeight + 'px'); }
    if (focusHeightVal) focusHeightVal.textContent = prefs.focusHeight + 'px';

    var iconSlider = document.getElementById('pcIconOpacitySlider'); var iconVal = document.getElementById('pcIconOpacityValue');
    if (iconSlider) { iconSlider.value = prefs.widgetOpacity; iconSlider.setAttribute('aria-valuenow', prefs.widgetOpacity); iconSlider.setAttribute('aria-valuetext', prefs.widgetOpacity + '%'); }
    if (iconVal) iconVal.textContent = prefs.widgetOpacity + '%';
    var bgSlider = document.getElementById('pcBgOpacitySlider'); var bgVal = document.getElementById('pcBgOpacityValue');
    if (bgSlider) { bgSlider.value = prefs.opacity; bgSlider.setAttribute('aria-valuenow', prefs.opacity); bgSlider.setAttribute('aria-valuetext', prefs.opacity + '%'); }
    if (bgVal) bgVal.textContent = prefs.opacity + '%';

    var posLabelEl = document.getElementById('pcPositionCurrentLabel');
    if (posLabelEl) {
      var pParts = (prefs.widgetPosition || DEFAULTS.widgetPosition).split('-');
      var vLabel = pParts[0].charAt(0).toUpperCase() + pParts[0].slice(1);
      var hLabel = pParts[1] === 'left' ? 'Left' : 'Right';
      posLabelEl.textContent = vLabel + ' ' + hLabel;
    }

    // TASK (Widget Positioning): keep the chat-widget coordination key
    // in sync with this widget's actual current position on every render
    // (not only inside moveWidgetTo), so it's always correct even the
    // very first time this runs, before any drag/D-pad move has happened.
    var syncedPositions = readWidgetPositions();
    if (syncedPositions.a11y !== prefs.widgetPosition) writeWidgetPositions({ a11y: prefs.widgetPosition, chat: syncedPositions.chat });

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

  // TASK (Dark Mode / Color Filter Audit): the old "Real Dark Mode
  // (Section 19)" DOM-luminance-scan approach that used to live here has
  // been removed entirely -- see index.html's <style> block for the
  // replacement (a proper `:root` custom-property theme with a single
  // `[data-theme="dark"]` override block). That old approach only ever
  // touched `background-color`, by its own admission never touched
  // `color` at all beyond two CSS variables, and is exactly what produced
  // the reported bug (light sections went black, but hardcoded-color text
  // sitting on them stayed the same color and became unreadable). Dark
  // mode is now nothing more than toggling one attribute on <html>; see
  // setPreferences()'s theme handling in applyAll() below.

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
    // TASK (Widget Positioning): the D-pad panel is a different element
    // (class `pc-a11y-dpad`, not `pc-a11y-dropdown__panel`) but shares the
    // same open/close contract, so it needs the same treatment here.
    var dpad = document.getElementById('pcPositionDpad');
    if (dpad) dpad.hidden = true;
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
      panel.classList.add('open'); trigger.setAttribute('aria-expanded', 'true');
      if (document.body.style.overflow !== 'hidden') { pcA11yLockedScroll = true; document.body.style.overflow = 'hidden'; }
    }
    function closePanel() {
      panel.classList.remove('open'); trigger.setAttribute('aria-expanded', 'false'); statement.classList.remove('open'); closeAllDropdowns();
      if (pcA11yLockedScroll) { document.body.style.overflow = ''; pcA11yLockedScroll = false; }
    }

    trigger.addEventListener('click', function () { panel.classList.contains('open') ? closePanel() : openPanel(); });
    closeBtn.addEventListener('click', closePanel);
    statementBtn.addEventListener('click', function () { panel.classList.remove('open'); statement.classList.add('open'); });
    statementBack.addEventListener('click', function () { statement.classList.remove('open'); panel.classList.add('open'); });
    document.addEventListener('click', function (e) { if (!e.target.closest('#pcA11yWidget')) closePanel(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closePanel(); });

    wireDropdown('pcLangDropdown', Object.keys(LANG_LABELS).map(function (k) { return { value: k, label: LANG_LABELS[k] }; }), function (val) {
      applyAll(setPreferences({ lang: val }));
      window.location.href = LANG_FOLDER_MAP[val] || '/';
    });
    // TASK (Widget Positioning) START: pill-grid wiring (replaces the old
    // wireDropdown('pcVisionDropdown', ...) call). Single-select, same as
    // the dropdown it replaces: clicking the already-active pill clears
    // back to 'normal' rather than staying stuck selected.
    var visionGrid = document.getElementById('pcVisionFilterGrid');
    if (visionGrid) {
      visionGrid.addEventListener('click', function (e) {
        var btn = e.target.closest('.pc-a11y-filter-pill');
        if (!btn) return;
        var val = btn.getAttribute('data-vision');
        var next = (getPreferences().colorVision === val) ? 'normal' : val;
        applyAll(setPreferences({ colorVision: next }));
      });
    }
    // TASK (Widget Positioning) END

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
    wireToggle('pcFocusReading', 'focusReading');

    document.getElementById('pcFocusDimSlider').addEventListener('input', function (e) {
      applyAll(setPreferences({ focusDim: Number(e.target.value) }));
    });
    document.getElementById('pcFocusHeightSlider').addEventListener('input', function (e) {
      applyAll(setPreferences({ focusHeight: Number(e.target.value) }));
    });

    document.getElementById('pcIconOpacitySlider').addEventListener('input', function (e) {
      applyAll(setPreferences({ widgetOpacity: Number(e.target.value) }));
    });
    document.getElementById('pcBgOpacitySlider').addEventListener('input', function (e) {
      applyAll(setPreferences({ opacity: Number(e.target.value) }));
    });
    // TASK (Widget Positioning) START =============================
    // moveWidgetTo (with its swap-with-chat-widget rule) now lives at
    // module scope, above applyAll -- see there. Both the drag gesture and
    // the D-pad below call that same shared function.

    // ---- Drag-to-reposition (mouse + touch, via Pointer Events) ----
    // Snap trigger: viewport thirds for vzone (top/middle/bottom),
    // viewport halves for hside (left/right) -- the standard, commonly
    // recommended approach for this kind of free-drag-then-snap widget
    // placement (matches how OS-level "snap to screen edge" pickers
    // typically divide the screen), per "do what's recommended."
    (function wireDragToReposition() {
      var widgetEl = document.getElementById('pcA11yWidget');
      var triggerEl = document.getElementById('pcA11yTrigger');
      var dragging = false, moved = false, startX = 0, startY = 0, justDragged = false;
      function resetDrag() {
        dragging = false;
        widgetEl.classList.remove('pc-a11y-dragging');
        widgetEl.style.left = ''; widgetEl.style.top = ''; widgetEl.style.right = ''; widgetEl.style.bottom = ''; widgetEl.style.marginTop = '';
      }
      function onPointerDown(e) {
        if (e.pointerType === 'mouse' && e.button !== 0) return;
        dragging = true; moved = false; startX = e.clientX; startY = e.clientY;
        widgetEl.classList.add('pc-a11y-dragging');
        try { triggerEl.setPointerCapture(e.pointerId); } catch (err) { /* ignore */ }
      }
      function onPointerMove(e) {
        if (!dragging) return;
        var dx = e.clientX - startX, dy = e.clientY - startY;
        if (!moved && (Math.abs(dx) > 6 || Math.abs(dy) > 6)) {
          moved = true;
          if (panel.classList.contains('open')) closePanel(); // dragging with the panel open would drag it off its anchor mid-fade; closed once, at the start of the drag, not every move tick
        }
        if (!moved) return;
        widgetEl.style.left = (e.clientX - 28) + 'px';
        widgetEl.style.top = (e.clientY - 28) + 'px';
        widgetEl.style.right = 'auto'; widgetEl.style.bottom = 'auto'; widgetEl.style.marginTop = '0';
      }
      function onPointerUp(e) {
        if (!dragging) return;
        var wasMoved = moved;
        resetDrag();
        if (!wasMoved) return; // no real drag -- let the ordinary click handler open/close the panel
        justDragged = true;
        var vh = window.innerHeight, vw = window.innerWidth;
        var vzone = e.clientY < vh / 3 ? 'top' : (e.clientY > (vh * 2) / 3 ? 'bottom' : 'middle');
        var hside = e.clientX < vw / 2 ? 'left' : 'right';
        moveWidgetTo(vzone + '-' + hside);
      }
      // TASK (Dark Mode / Color Filter Audit): pointercancel was the
      // missing piece behind "can't be dragged continuously" -- confirmed
      // via web research (javascript.info's pointer-events guide, and
      // multiple real-world bug reports of the identical symptom): without
      // `touch-action: none` (added to .pc-a11y-trigger's CSS) a touch
      // drag gets reinterpreted as a page scroll/pan after a few pixels of
      // movement, the browser fires pointercancel with NO pointerup at
      // all, and pointermove stops arriving entirely -- so the drag just
      // stops mid-gesture, and the snap logic in onPointerUp above never
      // even runs. Registered here alongside pointerup so a cancelled
      // gesture still cleans up `dragging` properly instead of leaving it
      // stuck true, which would have broken every subsequent drag attempt
      // too.
      function onPointerCancel() { if (dragging) resetDrag(); }
      triggerEl.addEventListener('pointerdown', onPointerDown);
      document.addEventListener('pointermove', onPointerMove);
      document.addEventListener('pointerup', onPointerUp);
      document.addEventListener('pointercancel', onPointerCancel);
      // A real drag ends with the same pointerup target as the click that
      // would normally follow it -- without this guard, releasing a drag
      // would also toggle the panel open/closed as an unwanted side effect.
      triggerEl.addEventListener('click', function (e) {
        if (justDragged) { justDragged = false; e.stopImmediatePropagation(); }
      }, true);
    })();

    // ---- More Settings > Widget Position: D-pad dropdown ----
    var posDropdownTrigger = document.getElementById('pcPositionDropdownTrigger');
    var posDpad = document.getElementById('pcPositionDpad');
    if (posDropdownTrigger && posDpad) {
      posDropdownTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = !posDpad.hidden;
        closeAllDropdowns();
        posDpad.hidden = isOpen;
        posDropdownTrigger.setAttribute('aria-expanded', String(!isOpen));
      });
    }
    var VZONE_ORDER = ['top', 'middle', 'bottom'];
    function currentParts() { return getPreferences().widgetPosition.split('-'); }
    var dpadUp = document.getElementById('pcDpadUp');
    var dpadDown = document.getElementById('pcDpadDown');
    var dpadLeft = document.getElementById('pcDpadLeft');
    var dpadRight = document.getElementById('pcDpadRight');
    var dpadCenter = document.getElementById('pcDpadCenter');
    if (dpadUp) dpadUp.addEventListener('click', function () {
      var p = currentParts(); var idx = Math.max(0, VZONE_ORDER.indexOf(p[0]) - 1);
      moveWidgetTo(VZONE_ORDER[idx] + '-' + p[1]);
    });
    if (dpadDown) dpadDown.addEventListener('click', function () {
      var p = currentParts(); var idx = Math.min(2, VZONE_ORDER.indexOf(p[0]) + 1);
      moveWidgetTo(VZONE_ORDER[idx] + '-' + p[1]);
    });
    if (dpadLeft) dpadLeft.addEventListener('click', function () { var p = currentParts(); moveWidgetTo(p[0] + '-left'); });
    if (dpadRight) dpadRight.addEventListener('click', function () { var p = currentParts(); moveWidgetTo(p[0] + '-right'); });
    // Center button: resets to this widget's documented default position
    // (bottom-left) -- the clearest, least ambiguous reading of the
    // illustration's center "on" button for a position picker that has no
    // other obvious use for a 5th button beyond the four directions.
    if (dpadCenter) dpadCenter.addEventListener('click', function () { moveWidgetTo(DEFAULTS.widgetPosition); });

    // Same-tab reaction when the CHAT widget's own drag/D-pad swaps us out
    // of our current spot (storage events don't fire in the tab that made
    // the change, only other tabs -- this custom event covers this tab).
    window.addEventListener('pc:widget-positions-change', function (e) {
      if (e.detail && e.detail.a11y && e.detail.a11y !== getPreferences().widgetPosition) {
        applyAll(setPreferences({ widgetPosition: e.detail.a11y }));
      }
    });
    // TASK (Widget Positioning) END ================================

    document.getElementById('pcResetAll').addEventListener('click', function () {
      try { localStorage.removeItem(PREFS_KEY); } catch (e) { /* ignore */ }
      applyAll(getPreferences());
    });

    document.addEventListener('mousemove', function (e) {
      var guide = document.getElementById('pcA11yFocusMask');
      // TASK (Dark Mode / Color Filter Audit): centers on the mask's
      // actual current height (now adjustable via the Focus Window Height
      // slider) instead of the old hardcoded half of 36px -- otherwise the
      // window would visibly stop being centered on the cursor the moment
      // someone moved that slider away from its default.
      if (guide && guide.style.display === 'block') guide.style.top = (e.clientY - (getPreferences().focusHeight / 2)) + 'px';
    });

    window.addEventListener('storage', function (e) {
      if (e.key !== PREFS_KEY) return;
      applyAll(getPreferences());
    });
  }

  function init() {
    injectCSS();
    injectColorFilterDefs();
    wrapPageContent();
    buildWidgetHTML();
    wireAccordion();
    wireProfiles();
    wireControls();
    // TASK-2 FOLLOWUP: seed the URL-selected language before first paint.
    seedLangFromAutoDetect();
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
    // ROOT-CAUSE FIX (reported: chat widget goes missing, or sticks to the
    // bottom and scrolls with the page, whenever any color-related setting
    // -- Color Filters, Invert, Grayscale, Dark Mode -- is turned on).
    // Confirmed by reading the actual code, not guessed: this loop used to
    // move EVERY current <body> child into this wrapper unconditionally,
    // and applyAll() applies this widget's CSS `filter` directly to this
    // same wrapper. A `filter` on an ancestor creates a NEW containing
    // block for any `position:fixed` descendant (CSS Filter Effects spec)
    // -- so any already-fixed element swept in here (the chat widget's
    // launcher button, panel, and backdrop are each appended to <body>
    // independently by chat-widget.js, and that script runs earlier in
    // the page than this one) stops being fixed to the real viewport and
    // instead anchors to THIS wrapper's box, which is as tall as the
    // entire page -- producing exactly the two reported symptoms
    // (scrolling away with the page, or being clipped out of view
    // entirely) depending on where in that tall box it lands. The fix:
    // never sweep an element that is already `position: fixed` into the
    // filtered subtree at all -- true for the chat widget today, and for
    // any other independently-floating widget a future project adds,
    // without this file needing to know that widget's class names.
    var toWrap = [];
    for (var i = 0; i < document.body.children.length; i++) {
      var child = document.body.children[i];
    // TASK (Color Filter Coverage): back to excluding by computed
    // position:fixed (protects ANY such element's positioning from the
    // containing-block problem, header included) rather than only the
    // chat widget's `pcw-` prefix -- the header needing the filter applied
    // separately (see applyAll() above) is the other half of this fix, not
    // a reason to narrow this exclusion back down to just one widget's
    // elements. A blanket exclusion here is correct; what was missing was
    // ever re-applying the filter to what got excluded.
    if (getComputedStyle(child).position !== 'fixed') toWrap.push(child);
    }
    toWrap.forEach(function (el) { wrapper.appendChild(el); });
    document.body.appendChild(wrapper);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
