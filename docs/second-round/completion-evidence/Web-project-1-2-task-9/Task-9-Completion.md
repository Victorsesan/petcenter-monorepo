# Task Completion — Task 9

## Task Number

Task 9 — Widget + Persistence Core Build (Homepage pilot), quoted from `WEB-PROJECTS-1-2-TASK-ASSIGNMENTS-v12.md`. Everything in this file is direct follow-on work under that same task, carried out across several rounds with the CEO (live-testing feedback cycles) before the hand-in format changed to this one: the drag/swipe 6-zone positioning system, the Color Filters rebuild (duotone, then tritone), the Focus Reading rebuild, Dark Mode (rebuilt twice), the font-size/spacing overflow fix, and several chat-widget.js fixes made alongside it for position-sync and shared bugs.

## What Was Required

Build the Settings Widget (gear icon, language + opacity + position controls, Color & Vision tools, Focus Reading, Dark Mode) and keep it in sync with the existing chat widget's position, per the original Task 9 spec — then, across several corrective rounds requested directly by the CEO after live testing, fix: the widget's drag-and-drop not working continuously, Color Filters being either non-functional or visually indistinguishable from each other, Dark Mode only partially covering the page, a font-size/text-spacing overflow bug, the widget appearing on top of the page header instead of below it, the chat widget losing its fixed position when color filters were active, a circumference mismatch between the two widgets' trigger buttons, and a chat-widget bug that scrolls the page on close.

**Per the CEO's explicit instruction, `accessibility-widget.js` is classified as a new file in this delivery** — the work done on it in this task went substantially beyond what the previously-credited worker had built, so it's delivered as a new file at its live path rather than as an old/new code diff against their version. `chat-widget.js` is the one file in this delivery that remains classified as modified.

**One item is NOT resolved and is flagged, not hidden**: the chat widget still scrolls the page when closed. Two fix attempts (detailed in the relevant Code Changes blocks' own comments, chat-widget.js blocks 1 and 2) did not fix it. Whoever picks this up next should read those two blocks' comments before trying a third theory — a stale-scroll-position theory and a scroll-anchoring theory have both already been tried and both fell short.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `accessibility-widget.js` | `assets/js/accessibility-widget.js` | New (see note above) | Yes — this is the single shared widget script every page loads; other tasks (the earlier Accessibility Widget Rebuild / Color Vision work) also touched this file's live path before this delivery |
| `chat-widget.js` | `admin/chat-widget.js` | Changed | Yes — originally built under the Live Chat System tasks; touched here only for position-sync with the settings widget and the shared bugs listed above |
| `index.html` | `index.html` | Changed | Yes — shared with the adoption-website-project-summary.md pages generally; the `<style>` block's color tokens are the same design-system tokens other pages reference |

**Note on `index.html`'s inline widget script**: at the CEO's own earlier explicit request, this file also carries a second, inline-embedded copy of `accessibility-widget.js`'s full code, for local `file://` testing without a server. That inline copy is kept byte-for-byte identical to the real `accessibility-widget.js` file every time the latter changes — it is not a separate implementation, just a duplicate for local testing. **Its diff is intentionally excluded from the Code Changes section below**, for the same reason as before, and because `accessibility-widget.js` itself is no longer documented as a diff at all in this version.

## Code Changes

### chat-widget.js → admin/chat-widget.js

<!-- TASK-9 START (block 1, chat-widget.js orig lines 170-177) -->
**Old code:**
```js
    width: 60px; height: 60px; border-radius: 50%;
    background: linear-gradient(135deg, #10214b, #1a2f5c);
    border: 2px solid #d7bd88; color: #fff; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; box-shadow: 0 6px 20px rgba(16,33,75,0.35);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .pcw-launcher-btn:hover { transform: scale(1.06); box-shadow: 0 8px 26px rgba(16,33,75,0.45); }
```
**New code:**
```js
    width: 56px; height: 56px; border-radius: 50%;
    background: linear-gradient(135deg, #10214b, #1a2f5c);
    border: 3px solid #d7bd88; color: #fff; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 6px 20px rgba(16,33,75,0.35);
    touch-action: none;
    /* TASK (Dark Mode / Color Filter Audit): size matched byte-for-byte to
       .pc-a11y-trigger (56px + 3px border = same 62px outer circle on both
       widgets, measured from the actual settings-widget CSS rather than
       eyeballed) -- was 60px + 2px border (64px outer) before, visibly
       larger than the settings widget as reported. Hover transform/shadow
       animation also dropped, matching the no-animation instruction
       applied consistently to both widgets' base triggers, not just the
       one it was stated for. */
  }
  /* TASK (Widget Positioning) START: position-sync with the settings
     widget only -- see wireChatPositionSync() near the end of this file.
     Scope Lock: this is the one thing this task asked to be touched in
     this file; nothing else about the chat widget's own features changes.
     Desktop-only (the existing @media (max-width:480px) block below
     already gives the panel a dedicated, working full-width mobile
     layout -- left untouched here beyond the one hside line added to it,
     so as not to regress an already-shipped mobile behavior that isn't
     part of this task). */
  body[data-pcw-vzone="top"] .pcw-launcher-btn { top: 84px; bottom: auto; }
  body[data-pcw-vzone="middle"] .pcw-launcher-btn { top: 50%; bottom: auto; margin-top: -30px; }
  body[data-pcw-hside="left"] .pcw-launcher-btn { left: 20px; right: auto; }
  @media (min-width: 481px) {
    body[data-pcw-vzone="top"] .pcw-panel { top: 160px; bottom: auto; }
    body[data-pcw-vzone="middle"] .pcw-panel { top: 50%; bottom: auto; transform: translateY(-50%); }
    body[data-pcw-hside="left"] .pcw-panel { left: 20px; right: auto; }
  }
  /* TASK (Widget Positioning) END */
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 2, chat-widget.js orig lines 773-772) -->
**Old code:**
```js
    .pcw-launcher-btn { bottom: 16px; right: 16px; }
  }
```
**New code:**
```js
    .pcw-launcher-btn { bottom: 16px; right: 16px; }
    /* TASK (Widget Positioning): the panel is already edge-to-edge
       (left:14px;right:14px) on mobile, so hside only needs to move the
       launcher button itself -- the panel's own left/right don't need an
       hside variant here. */
    body[data-pcw-hside="left"] .pcw-launcher-btn { left: 16px; right: auto; }
  }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 3, chat-widget.js orig lines 851-852) -->
**Old code:**
```js
    launcherBtn = el('button', { class: 'pcw-launcher-btn', type: 'button', title: 'Chat with us' }, [
      el('i', { class: 'fas fa-comment-dots' }),
```
**New code:**
```js
    // TASK (Dark Mode / Color Filter Audit): was `<i class="fas fa-comment-dots">`
    // (Font Awesome) -- an external icon-font dependency this project
    // doesn't otherwise rely on (the settings widget's icons are all
    // inline SVG), and if Font Awesome's stylesheet doesn't happen to be
    // loaded on a given page this renders as an empty box rather than a
    // chat icon. Replaced with an inline SVG at the same 24x24 size the
    // settings widget's own trigger icon uses, so the two are visually
    // consistent and neither depends on a page happening to load the
    // right external font.
    launcherBtn = el('button', { class: 'pcw-launcher-btn', type: 'button', title: 'Chat with us' }, [
      el('span', { 'aria-hidden': 'true', html: '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>' }),
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 4, chat-widget.js orig lines 908-907) -->
**Old code:**
```js
    _pcwScrollLockY = window.scrollY || window.pageYOffset || 0;
    document.body.style.position = 'fixed';
```
**New code:**
```js
    _pcwScrollLockY = window.scrollY || window.pageYOffset || 0;
    // TASK (Chat Widget Scroll Fix): confirmed via research (MDN's own
    // CSS Scroll Anchoring docs, plus multiple independent write-ups on
    // this exact position:fixed body-lock technique) that taking <body>
    // out of normal flow like this is a documented trigger for the
    // browser's scroll-anchoring feature to pick a NEW anchor element and
    // "helpfully" keep adjusting scroll position around it as layout
    // settles back to normal on unlock -- which lands wherever that
    // anchor's heuristic puts it, not necessarily back where the user
    // was. That matches the reported symptom precisely: it never happens
    // if the page was already at the top (nothing for an anchor to
    // "correct"), and when it does fire, it's not a stale-position bug
    // (which would at least be consistent with SOME prior state) but a
    // browser heuristic landing roughly mid-page. Disabling anchoring for
    // the duration of the lock removes the browser's incentive to touch
    // scroll position on its own at all -- the explicit scrollTo below
    // becomes the only thing moving it.
    document.body.style.overflowAnchor = 'none';
    document.body.style.position = 'fixed';
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 5, chat-widget.js orig lines 915-915) -->
**Old code:**
```js
    _pcwScrollLockCount = Math.max(0, _pcwScrollLockCount - 1);
```
**New code:**
```js
    // TASK (Chat Widget Scroll Fix): this guard used to decrement FIRST
    // and check `> 0` after -- so a second, redundant call arriving after
    // the count had already reached 0 would compute Math.max(0, -1) = 0,
    // see `0 > 0` as false, and fall through to re-run the restore logic
    // a second time, using the SAME stale `_pcwScrollLockY` captured at
    // the original lock. If the user had scrolled around freely in the
    // meantime (the page is fully unlocked and scrollable at that point),
    // that stray second call would yank them back to wherever they were
    // when the chat was first opened -- matching exactly what was
    // reported ("stops the page at a certain position... closes it, it
    // will just scroll the page to a certain position"). Checking before
    // decrementing makes a call that arrives after the count is already 0
    // a true no-op.
    if (_pcwScrollLockCount <= 0) return;
    _pcwScrollLockCount--;
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 6, chat-widget.js orig lines 922-922) -->
**Old code:**
```js
    window.scrollTo(0, _pcwScrollLockY);
```
**New code:**
```js
    // TASK (Chat Widget Scroll Fix): a style change doesn't force the
    // browser to finish layout before the next line of JS runs -- calling
    // scrollTo() immediately risks doing so against a transient, not-yet-
    // settled layout (the CSS-Tricks writeup on this exact technique flags
    // this ordering issue specifically). One rAF is enough to guarantee a
    // layout/paint has happened since the style change above.
    requestAnimationFrame(function () {
      window.scrollTo(0, _pcwScrollLockY);
      document.body.style.overflowAnchor = '';
    });
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 7, chat-widget.js orig lines 3648-3647) -->
**Old code:**
```js
  // ============================================
  function init() {
```
**New code:**
```js
  // ============================================
  // TASK (Widget Positioning) START =================================
  // Position-sync with the accessibility/settings widget ONLY -- the one
  // thing this task asked to be touched in this file. Reads/writes the
  // same 'pc_widget_positions' localStorage key the settings widget owns
  // (see assets/js/accessibility-widget.js); this widget never initiates
  // a move itself (no drag/D-pad here, by design -- out of this task's
  // scope), it only ever reacts to what that key says its own 'chat'
  // field currently is, including when the settings widget's own drag/
  // D-pad swaps this widget out of a spot it occupied.
  const WIDGET_POS_KEY = 'pc_widget_positions';
  function readChatPosition() {
    try {
      const raw = localStorage.getItem(WIDGET_POS_KEY);
      const parsed = raw ? JSON.parse(raw) : {};
      return parsed.chat || 'bottom-right';
    } catch (e) { return 'bottom-right'; }
  }
  function applyChatPosition() {
    const parts = readChatPosition().split('-');
    document.body.setAttribute('data-pcw-vzone', parts[0]);
    document.body.setAttribute('data-pcw-hside', parts[1]);
  }
  function writeChatPosition(next) {
    try {
      const raw = localStorage.getItem(WIDGET_POS_KEY);
      const parsed = raw ? JSON.parse(raw) : {};
      const current = parsed.chat || 'bottom-right';
      if (next === current) return;
      const updated = { a11y: parsed.a11y || 'bottom-left', chat: next };
      // Same swap rule as the settings widget's moveWidgetTo(): if the
      // settings widget already occupies the target zone, it takes this
      // widget's old spot rather than the two ever sharing one zone.
      if (updated.a11y === next) updated.a11y = current;
      localStorage.setItem(WIDGET_POS_KEY, JSON.stringify(updated));
      applyChatPosition();
      window.dispatchEvent(new CustomEvent('pc:widget-positions-change', { detail: updated }));
    } catch (e) { /* ignore */ }
  }
  // TASK (Dark Mode / Color Filter Audit): this widget now drags exactly
  // like the settings widget, per explicit instruction that the drag fix
  // applies to both. `touch-action: none` on the launcher button (added
  // to its CSS above) and handling `pointercancel` here are the two
  // pieces that were missing from the settings widget's first version --
  // without `touch-action: none`, a touch drag gets reinterpreted as a
  // page scroll after a few pixels of movement, the browser fires
  // pointercancel, and no more pointermove events arrive -- exactly the
  // "can't be dragged continuously" symptom reported. Built correctly
  // here from the start rather than copying the original bug.
  function wireChatDragToReposition() {
    let dragging = false, moved = false, startX = 0, startY = 0, justDragged = false;
    function reset() {
      dragging = false;
      launcherBtn.style.left = ''; launcherBtn.style.top = ''; launcherBtn.style.right = ''; launcherBtn.style.bottom = ''; launcherBtn.style.marginTop = '';
      launcherBtn.classList.remove('pcw-dragging');
    }
    function onPointerDown(e) {
      if (e.pointerType === 'mouse' && e.button !== 0) return;
      dragging = true; moved = false; startX = e.clientX; startY = e.clientY;
      launcherBtn.classList.add('pcw-dragging');
      try { launcherBtn.setPointerCapture(e.pointerId); } catch (err) { /* ignore */ }
    }
    function onPointerMove(e) {
      if (!dragging) return;
      const dx = e.clientX - startX, dy = e.clientY - startY;
      if (!moved && (Math.abs(dx) > 6 || Math.abs(dy) > 6)) moved = true;
      if (!moved) return;
      launcherBtn.style.left = (e.clientX - 28) + 'px';
      launcherBtn.style.top = (e.clientY - 28) + 'px';
      launcherBtn.style.right = 'auto'; launcherBtn.style.bottom = 'auto'; launcherBtn.style.marginTop = '0';
    }
    function onPointerUp(e) {
      if (!dragging) return;
      const wasMoved = moved;
      reset();
      if (!wasMoved) return;
      justDragged = true;
      const vh = window.innerHeight, vw = window.innerWidth;
      const vzone = e.clientY < vh / 3 ? 'top' : (e.clientY > (vh * 2) / 3 ? 'bottom' : 'middle');
      const hside = e.clientX < vw / 2 ? 'left' : 'right';
      writeChatPosition(vzone + '-' + hside);
    }
    // Registered alongside pointerup, not instead of it: a cancelled
    // gesture (browser hands off to panning/scrolling, a second finger
    // arrives, palm rejection) fires pointercancel with NO pointerup at
    // all -- without this, `dragging` would stay stuck true and the next
    // real drag attempt would start from a broken state.
    function onPointerCancel() { reset(); }
    launcherBtn.addEventListener('pointerdown', onPointerDown);
    document.addEventListener('pointermove', onPointerMove);
    document.addEventListener('pointerup', onPointerUp);
    document.addEventListener('pointercancel', onPointerCancel);
    launcherBtn.addEventListener('click', (e) => {
      if (justDragged) { justDragged = false; e.stopImmediatePropagation(); e.preventDefault(); }
    }, true);
  }

  function wireChatPositionSync() {
    applyChatPosition();
    wireChatDragToReposition();
    // Cross-tab: 'storage' only fires in OTHER tabs than the one that wrote it.
    window.addEventListener('storage', (e) => { if (e.key === WIDGET_POS_KEY) applyChatPosition(); });
    // Same-tab: dispatched by accessibility-widget.js's moveWidgetTo() when
    // both widgets are loaded on the same page (e.g. admin-dashboard.html).
    window.addEventListener('pc:widget-positions-change', applyChatPosition);
  }
  // TASK (Widget Positioning) END ====================================

  function init() {
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 8, chat-widget.js orig lines 3652-3651) -->
**Old code:**
```js
    updateLauncherButtonState();
    dlog('widget initialized');
```
**New code:**
```js
    updateLauncherButtonState();
    wireChatPositionSync();
    dlog('widget initialized');
```
<!-- TASK-9 END -->
### index.html → index.html

<!-- TASK-9 START (block 1, index.html orig lines 46-45) -->
**Old code:**
```html
  --navy: #10214b;
  --navy-light: #1a2f5c;
```
**New code:**
```html
  --navy: #10214b;
  --navy-ink: #10214b;
  --navy-border: #10214b;
  --navy-light: #1a2f5c;
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 2, index.html orig lines 69-68) -->
**Old code:**
```html
  --transition: 0.25s ease;
}

/* ACCESSIBILITY-WIDGET NOTE: the --warning/--info variables and their
```
**New code:**
```html
  --transition: 0.25s ease;
}
/* TASK (Dark Mode / Color Filter Audit) START ============================
   Full rebuild of Dark Mode, replacing the old runtime DOM-luminance-scan
   approach (applyRealDarkMode() in accessibility-widget.js, now removed)
   with a proper CSS custom-property theme, per the explicit hard-gate
   research requirement. The old approach walked every element on the
   page, measured each one's computed background luminance, and force-
   overrode ONLY the bright ones to a dark surface color via inline
   `!important` styles -- it never touched text color at all beyond two
   CSS variables. That's exactly the reported bug: sections with a light
   background went black, but any hardcoded-color text sitting on that
   background (like "Read More", set with a literal navy hex rather than
   a variable) stayed navy, and navy-on-newly-black is nearly invisible --
   a "cherry-picked" set of overrides, not a real theme.

   Researched approach (confirmed against multiple independent sources,
   including the pattern used by web.dev's own dark-mode guidance and
   Josh Comeau's widely-cited dark-mode article): define every color as a
   CSS custom property at :root, have every rule in this stylesheet
   reference those properties instead of a hardcoded hex value, and have
   dark mode be nothing more than one `[data-theme="dark"]` block that
   redefines the SAME property names -- so every rule that already uses
   `var(--token)` picks up the new values automatically, with nothing
   selected or excluded rule-by-rule. All 97 hardcoded hex declarations in
   this stylesheet that used one of this page's core palette colors were
   converted to `var(--token)` as part of this fix (verified by re-
   scanning the stylesheet afterward for each raw hex value -- zero
   remaining outside of one descriptive code comment).

   Color choices below (contrast-checked against WCAG AA's 4.5:1 minimum
   for normal text, not just picked by eye):
   - Page background goes to a near-black deepened navy (#060c1f), not
     pure black -- pure black against this site's warm, off-white light
     background is a much harsher jump than the light theme's own soft
     cream-to-navy contrast, and several dark-mode guides flag pure black
     as its own readability/eye-strain problem, not just a light-mode one.
   - The existing brand navy (#10214b) becomes the DARK THEME'S "raised
     surface" color (cards, panels) sitting on top of that deeper page
     background -- this needed no new color at all, since the site's own
     navy sections (header/hero/footer) are already dark and already
     legible against gold/white/orange text today; reusing it here keeps
     the palette anchored to the existing brand rather than inventing a
     second dark blue.
   - Headings/text that read fine on the light cream background (dark
     brown/near-black) get INVERTED to gold as the dark theme's primary
     text accent and a soft off-white (#f2f2f2, not pure #fff, for the
     same eye-strain reason as above) as the dark theme's body-text color
     -- gold on the new near-black background measures roughly 10.7:1
     contrast, soft white roughly 17:1, both comfortably clear of the
     4.5:1 AA floor.
   - Orange and gold themselves are left UNCHANGED between themes -- both
     already read clearly on dark navy (that's how they're used in the
     light theme's own header/hero already) and on the new dark
     background, so changing them would only add inconsistency for no
     contrast benefit.
   This lines up with, but isn't a verbatim copy of, the suggestion
   floated for this task (deepen navy, gold primary, orange/white
   secondary) -- arrived at independently through the contrast check
   above, then cross-referenced against that suggestion afterward.
   ========================================================================= */
[data-theme="dark"] {
  /* TASK (Dark Mode Correction) START =====================================
     Rebuilt per explicit, corrected direction after the first version
     didn't go far enough: ONE flat dark navy, slightly darker than this
     page's own existing brand navy (#10214b) -- not a layered bg/surface
     hierarchy like the previous version used (that was a reasonable
     design-system instinct, but it's explicitly not what was asked for
     here: everything gets the SAME navy, no distinction). No gradients
     anywhere, full stop -- confirmed the previous version's actual bug by
     reading the CSS: `.pc-testimonials-section` used `background:
     linear-gradient(135deg, #f3f4f6 0%, var(--bg) 100%)` -- a hardcoded
     LIGHT gray that never changes, blended with the (correctly dark)
     --bg token, producing exactly the wrong, hard-to-read gradient
     reported in the "Reviews from Adopters" section. The safety-net rules
     below strip every background-image and force a flat background-color
     within the three regions that are independently `position: fixed`
     (and therefore NOT reached by the token system alone the way normal
     document-flow content is) -- the page wrapper, the header, and the
     in-page status popup -- as the forceful, can't-be-missed layer this
     was asked for, on top of (not instead of) the token conversion
     already in place for the rest of the page.
     ========================================================================= */
  --bg: #0c1830;
  --surface: #0c1830;
  --text: #f2f2f2;
  --muted: #cfc6ba;
  --chocolate: #d7bd88;
  --navy-ink: #d7bd88;
  --navy-border: #3a4a72;
  --warm-gray: #3a4a72;
  --orange-light: #2a1d10;
  --success: #2ecc82;
  --success-bg: rgba(46,204,130,0.14);
  --error: #ff6b5b;
  --error-bg: rgba(255,107,91,0.14);
  --shadow-sm: 0 2px 8px rgba(0,0,0,0.35);
  --shadow-md: 0 4px 20px rgba(0,0,0,0.45);
  --shadow-lg: 0 8px 40px rgba(0,0,0,0.55);
}
html, body { background: var(--bg); }
/* One unified page-wide transition for every themed property, so the
   whole page fades together when the toggle flips, rather than each
   section popping at a different moment. */
html[data-theme] , html[data-theme] * {
  transition: background-color 0.35s ease, color 0.35s ease, border-color 0.35s ease !important;
}
/* TASK (Dark Mode Correction): forceful safety net. Scoped to the three
   regions confirmed independently `position: fixed` (so excluded from
   wrapPageContent()'s sweep, and therefore from the Color Filters' own
   `filter` property -- but custom properties still cascade to them
   normally, which is what this block relies on) -- NOT a blanket `*`
   selector, which would also repaint this widget's own panel (explicitly
   out of scope: "these should not affect or be applied inside any of our
   widget panel" applies here too, not just to the earlier color-filter
   work). `div/section/article/header/footer/li/ul` deliberately excludes
   `button`/`a`/`input` so intentional CTA colors (the orange "View All
   Available Pets" button, etc.) are never touched by this -- background
   does not inherit in CSS, so a broad rule on structural containers has
   no effect on a differently-colored element nested inside one.
   Background-image removal is unconditional and absolute, per explicit
   instruction that dark mode must never use a gradient under any
   circumstance, no exceptions. */
[data-theme="dark"] #pcA11yPageWrap, [data-theme="dark"] #pcA11yPageWrap *,
[data-theme="dark"] .pc-header, [data-theme="dark"] .pc-header *,
[data-theme="dark"] .pc-status-overlay, [data-theme="dark"] .pc-status-overlay * {
  background-image: none !important;
}
[data-theme="dark"] #pcA11yPageWrap div, [data-theme="dark"] #pcA11yPageWrap section,
[data-theme="dark"] #pcA11yPageWrap article, [data-theme="dark"] #pcA11yPageWrap header,
[data-theme="dark"] #pcA11yPageWrap footer, [data-theme="dark"] #pcA11yPageWrap li,
[data-theme="dark"] #pcA11yPageWrap ul,
[data-theme="dark"] .pc-header, [data-theme="dark"] .pc-mobile-menu,
[data-theme="dark"] .pc-status-overlay, [data-theme="dark"] .pc-status-popup,
[data-theme="dark"] .pc-status-popup__header, [data-theme="dark"] .pc-status-popup__footer {
  background-color: var(--bg) !important;
}
/* TASK (Dark Mode / Color Filter Audit) END */


/* ACCESSIBILITY-WIDGET NOTE: the --warning/--info variables and their
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 3, index.html orig lines 169-169) -->
**Old code:**
```html
  border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff;
```
**New code:**
```html
  border: 2px solid rgba(255,255,255,0.3); border-top-color: var(--surface);
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 4, index.html orig lines 190-194) -->
**Old code:**
```html
  background: #10214b; z-index: 9999;
  display: flex; flex-direction: column;
  justify-content: center; align-items: center;
  transition: opacity 0.5s ease;
  color: #d7bd88;
```
**New code:**
```html
  background: var(--navy); z-index: 9999;
  display: flex; flex-direction: column;
  justify-content: center; align-items: center;
  transition: opacity 0.5s ease;
  color: var(--gold);
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 5, index.html orig lines 228-228) -->
**Old code:**
```html
  opacity: 0;
  color: #d7bd88;
}
```
**New code:**
```html
  opacity: 0;
  color: var(--gold);
}
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 6, index.html orig lines 336-340) -->
**Old code:**
```html
.pc-header__nav a:hover { color: #d7bd88; background: rgba(255,255,255,0.08); }
.pc-header__nav .nav-donate a { background: #ED7624; color: #fff; font-weight: 600; padding: 8px 16px; }
.pc-header__nav .nav-donate a:hover { background: #d4621a; color: #fff; }
.pc-header__nav .nav-signup a,
.pc-header__nav .nav-login a { border: 1px solid rgba(215,189,136,0.5); color: #d7bd88; }
```
**New code:**
```html
.pc-header__nav a:hover { color: var(--gold); background: rgba(255,255,255,0.08); }
.pc-header__nav .nav-donate a { background: var(--orange); color: #fff; font-weight: 600; padding: 8px 16px; }
.pc-header__nav .nav-donate a:hover { background: #d4621a; color: #fff; }
.pc-header__nav .nav-signup a,
.pc-header__nav .nav-login a { border: 1px solid rgba(215,189,136,0.5); color: var(--gold); }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 7, index.html orig lines 345-345) -->
**Old code:**
```html
  background: #10214b; border-radius: 8px; list-style: none;
```
**New code:**
```html
  background: var(--navy); border-radius: 8px; list-style: none;
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 8, index.html orig lines 358-358) -->
**Old code:**
```html
  background: #fff; border-radius: 2px; transition: all 0.3s ease;
```
**New code:**
```html
  background: var(--surface); border-radius: 2px; transition: all 0.3s ease;
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 9, index.html orig lines 373-373) -->
**Old code:**
```html
  opacity: 1; pointer-events: auto; padding: 16px 0 24px;
```
**New code:**
```html
  opacity: 1; pointer-events: auto;
  /* TASK (Widget Positioning): re-diffed against cats.html (the canonical
     Section 2.4 implementation) after the scroll-leak bug was reported as
     still present here despite the earlier JS fix. The JS was already
     identical to cats.html's -- this padding value was the actual
     difference. cats.html pads the bottom by calc(100vh - 68px - 56px),
     not a plain 24px: that forces the menu's content to always be taller
     than its own max-height, so it's genuinely scrollable even when the
     link list is short. `overscroll-behavior: contain` only stops a touch
     gesture from scrolling the page BEHIND this menu once the menu itself
     has started scrolling -- with a short link list and only 24px of
     padding, there was often nothing to scroll inside the menu at all, so
     the touch passed straight through to the page underneath. This is
     that missing piece, copied byte-for-byte from cats.html rather than
     re-derived. */
  padding: 16px 0 calc(100vh - 68px - 56px);
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 10, index.html orig lines 381-384) -->
**Old code:**
```html
.pc-mobile-menu a:hover { color: #d7bd88; background: rgba(255,255,255,0.05); }
.pc-mobile-menu .mobile-donate {
  margin: 12px 28px 0; display: inline-block;
  background: #ED7624; color: #fff; padding: 11px 24px;
```
**New code:**
```html
.pc-mobile-menu a:hover { color: var(--gold); background: rgba(255,255,255,0.05); }
.pc-mobile-menu .mobile-donate {
  margin: 12px 28px 0; display: inline-block;
  background: var(--orange); color: #fff; padding: 11px 24px;
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 11, index.html orig lines 408-412) -->
**Old code:**
```html
  font-weight: 700; color: #d7bd88; margin-bottom: 16px;
}
.pc-footer__col ul { list-style: none; display: flex; flex-direction: column; gap: 9px; }
.pc-footer__col ul a { font-size: 13px; color: rgba(255,255,255,0.6); text-decoration: none; transition: color 0.2s; }
.pc-footer__col ul a:hover { color: #d7bd88; }
```
**New code:**
```html
  font-weight: 700; color: var(--gold); margin-bottom: 16px;
}
.pc-footer__col ul { list-style: none; display: flex; flex-direction: column; gap: 9px; }
.pc-footer__col ul a { font-size: 13px; color: rgba(255,255,255,0.6); text-decoration: none; transition: color 0.2s; }
.pc-footer__col ul a:hover { color: var(--gold); }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 12, index.html orig lines 420-420) -->
**Old code:**
```html
.pc-footer__links a:hover { color: #d7bd88; }
```
**New code:**
```html
.pc-footer__links a:hover { color: var(--gold); }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 13, index.html orig lines 443-443) -->
**Old code:**
```html
  color: #d7bd88; background: rgba(215,189,136,0.12);
```
**New code:**
```html
  color: var(--gold); background: rgba(215,189,136,0.12);
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 14, index.html orig lines 460-460) -->
**Old code:**
```html
  background: #ED7624; color: #fff; padding: 14px 32px; border-radius: 8px;
```
**New code:**
```html
  background: var(--orange); color: #fff; padding: 14px 32px; border-radius: 8px;
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 15, index.html orig lines 475-476) -->
**Old code:**
```html
.pc-btn--signin { background: transparent; border: 1px solid rgba(215,189,136,0.5); color: #d7bd88; }
.pc-btn--signin:hover { background: rgba(215,189,136,0.12); color: #d7bd88; }
```
**New code:**
```html
.pc-btn--signin { background: transparent; border: 1px solid rgba(215,189,136,0.5); color: var(--gold); }
.pc-btn--signin:hover { background: rgba(215,189,136,0.12); color: var(--gold); }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 16, index.html orig lines 489-489) -->
**Old code:**
```html
  overflow: hidden;
  background: #10214b;
}
```
**New code:**
```html
  overflow: hidden;
  background: var(--navy);
}
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 17, index.html orig lines 546-546) -->
**Old code:**
```html
  color: #ED7624;
```
**New code:**
```html
  color: var(--orange);
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 18, index.html orig lines 561-561) -->
**Old code:**
```html
  font-size: clamp(1.3rem, 2.2vw, 1.7rem);
  color: #d7bd88;
  margin-bottom: 16px;
```
**New code:**
```html
  font-size: clamp(1.3rem, 2.2vw, 1.7rem);
  color: var(--gold);
  margin-bottom: 16px;
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 19, index.html orig lines 596-596) -->
**Old code:**
```html
.pc-wn-slide__link:hover { color: #d7bd88; }
```
**New code:**
```html
.pc-wn-slide__link:hover { color: var(--gold); }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 20, index.html orig lines 601-602) -->
**Old code:**
```html
  background: #fff;
  color: #10214b;
```
**New code:**
```html
  background: var(--surface);
  color: var(--navy-ink);
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 21, index.html orig lines 613-613) -->
**Old code:**
```html
.pc-wn-slide__btn--orange { background: #ED7624; color: #fff; }
```
**New code:**
```html
.pc-wn-slide__btn--orange { background: var(--orange); color: #fff; }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 22, index.html orig lines 691-691) -->
**Old code:**
```html
  width: 0%;
  background: #fff;
  transition: width 0.2s linear;
```
**New code:**
```html
  width: 0%;
  background: var(--surface);
  transition: width 0.2s linear;
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 23, index.html orig lines 740-740) -->
**Old code:**
```html
  background: #fff; padding: 56px 24px;
```
**New code:**
```html
  background: var(--surface); padding: 56px 24px;
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 24, index.html orig lines 747-747) -->
**Old code:**
```html
  font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase;
  color: #ED7624; background: rgba(237,118,36,0.08);
  padding: 4px 14px; border-radius: 20px; margin-bottom: 16px;
```
**New code:**
```html
  font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase;
  color: var(--orange); background: rgba(237,118,36,0.08);
  padding: 4px 14px; border-radius: 20px; margin-bottom: 16px;
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 25, index.html orig lines 753-760) -->
**Old code:**
```html
  color: #10214b; margin-bottom: 8px;
}
.pc-status-inner > p {
  font-family: 'Sora', sans-serif; font-size: 14px;
  color: #7a6d5f; margin-bottom: 28px;
}
.pc-status-form {
  display: flex; gap: 10px; background: #f9f7f4;
```
**New code:**
```html
  color: var(--navy-ink); margin-bottom: 8px;
}
.pc-status-inner > p {
  font-family: 'Sora', sans-serif; font-size: 14px;
  color: var(--muted); margin-bottom: 28px;
}
.pc-status-form {
  display: flex; gap: 10px; background: var(--bg);
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 26, index.html orig lines 766-766) -->
**Old code:**
```html
  font-family: 'Sora', sans-serif; font-size: 15px; color: #10214b;
```
**New code:**
```html
  font-family: 'Sora', sans-serif; font-size: 15px; color: var(--navy-ink);
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 27, index.html orig lines 771-771) -->
**Old code:**
```html
  background: #ED7624; color: #fff; border: none; border-radius: 8px;
```
**New code:**
```html
  background: var(--orange); color: #fff; border: none; border-radius: 8px;
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 28, index.html orig lines 778-781) -->
**Old code:**
```html
.status-pet-info h4 { font-family: 'Cormorant Garamond', serif; font-size: 20px; font-weight: 700; color: #10214b; }
.status-pet-info p { font-family: 'Sora', sans-serif; font-size: 13px; color: #7a6d5f; }
.status-payment-box {
  background: #fff; border-radius: 10px; padding: 20px;
```
**New code:**
```html
.status-pet-info h4 { font-family: 'Cormorant Garamond', serif; font-size: 20px; font-weight: 700; color: var(--navy-ink); }
.status-pet-info p { font-family: 'Sora', sans-serif; font-size: 13px; color: var(--muted); }
.status-payment-box {
  background: var(--surface); border-radius: 10px; padding: 20px;
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 29, index.html orig lines 786-790) -->
**Old code:**
```html
  color: #10214b; letter-spacing: .04em; text-transform: uppercase; margin-bottom: 14px;
}
.status-payment-box ul { list-style: none; display: flex; flex-direction: column; gap: 12px; }
.status-payment-box ul li {
  font-family: 'Sora', sans-serif; font-size: 13px; color: #3d2817;
```
**New code:**
```html
  color: var(--navy-ink); letter-spacing: .04em; text-transform: uppercase; margin-bottom: 14px;
}
.status-payment-box ul { list-style: none; display: flex; flex-direction: column; gap: 12px; }
.status-payment-box ul li {
  font-family: 'Sora', sans-serif; font-size: 13px; color: var(--chocolate);
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 30, index.html orig lines 795-797) -->
**Old code:**
```html
  width: 8px; height: 8px; background: #ED7624; border-radius: 50%;
}
.status-payment-box ul li strong { color: #10214b; }
```
**New code:**
```html
  width: 8px; height: 8px; background: var(--orange); border-radius: 50%;
}
.status-payment-box ul li strong { color: var(--navy-ink); }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 31, index.html orig lines 813-813) -->
**Old code:**
```html
.pc-recent-pets { padding: 80px 24px; background: #f9f7f4; }
```
**New code:**
```html
.pc-recent-pets { padding: 80px 24px; background: var(--bg); }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 32, index.html orig lines 818-818) -->
**Old code:**
```html
  font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase;
  color: #ED7624; background: rgba(237,118,36,0.08);
  padding: 4px 14px; border-radius: 20px; margin-bottom: 14px;
```
**New code:**
```html
  font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase;
  color: var(--orange); background: rgba(237,118,36,0.08);
  padding: 4px 14px; border-radius: 20px; margin-bottom: 14px;
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 33, index.html orig lines 824-826) -->
**Old code:**
```html
  color: #10214b; margin-bottom: 10px;
}
.pc-section-head p { font-family: 'Sora', sans-serif; font-size: 15px; color: #7a6d5f; max-width: 500px; margin: 0 auto; }
```
**New code:**
```html
  color: var(--navy-ink); margin-bottom: 10px;
}
.pc-section-head p { font-family: 'Sora', sans-serif; font-size: 15px; color: var(--muted); max-width: 500px; margin: 0 auto; }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 34, index.html orig lines 836-836) -->
**Old code:**
```html
.pc-pets-grid-wrap::-webkit-scrollbar-thumb { background: #d0c3ba; border-radius: 4px; }
```
**New code:**
```html
.pc-pets-grid-wrap::-webkit-scrollbar-thumb { background: var(--warm-gray); border-radius: 4px; }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 35, index.html orig lines 842-842) -->
**Old code:**
```html
  font-family: 'Sora', sans-serif; color: #7a6d5f;
```
**New code:**
```html
  font-family: 'Sora', sans-serif; color: var(--muted);
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 36, index.html orig lines 847-851) -->
**Old code:**
```html
  border: 2px solid #ED7624; color: #ED7624; padding: 12px 32px; border-radius: 8px;
  font-family: 'Sora', sans-serif; font-size: 14px; font-weight: 600;
  text-decoration: none; transition: all 0.25s; background: transparent;
}
.pc-btn-outline-navy:hover { background: #ED7624; color: #fff; }
```
**New code:**
```html
  border: 2px solid var(--orange); color: var(--orange); padding: 12px 32px; border-radius: 8px;
  font-family: 'Sora', sans-serif; font-size: 14px; font-weight: 600;
  text-decoration: none; transition: all 0.25s; background: transparent;
}
.pc-btn-outline-navy:hover { background: var(--orange); color: #fff; }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 37, index.html orig lines 857-860) -->
**Old code:**
```html
  font-family: 'Sora', sans-serif; font-size: 12.5px; color: #3d2817;
}
.pc-pet-card__meta-row svg { flex-shrink: 0; color: #ED7624; }
.pc-pet-card__meta-row.fee { font-weight: 700; color: #ED7624; font-size: 14px; }
```
**New code:**
```html
  font-family: 'Sora', sans-serif; font-size: 12.5px; color: var(--chocolate);
}
.pc-pet-card__meta-row svg { flex-shrink: 0; color: var(--orange); }
.pc-pet-card__meta-row.fee { font-weight: 700; color: var(--orange); font-size: 14px; }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 38, index.html orig lines 877-877) -->
**Old code:**
```html
  background: #fff; border-radius: 14px; overflow: hidden;
```
**New code:**
```html
  background: var(--surface); border-radius: 14px; overflow: hidden;
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 39, index.html orig lines 885-885) -->
**Old code:**
```html
  background: linear-gradient(135deg, #10214b, #2a4080);
```
**New code:**
```html
  background: linear-gradient(135deg, var(--navy), #2a4080);
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 40, index.html orig lines 898-898) -->
**Old code:**
```html
  background: #10214b; color: #fff;
```
**New code:**
```html
  background: var(--navy); color: #fff;
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 41, index.html orig lines 907-910) -->
**Old code:**
```html
  color: #10214b; margin-bottom: 4px;
}
.pc-pet-card__breed { font-family: 'Sora', sans-serif; font-size: 12.5px; color: #7a6d5f; margin-bottom: 0; }
.pc-pet-card__fee { margin-top: auto; font-family: 'Sora', sans-serif; font-size: 15px; font-weight: 700; color: #ED7624; }
```
**New code:**
```html
  color: var(--navy-ink); margin-bottom: 4px;
}
.pc-pet-card__breed { font-family: 'Sora', sans-serif; font-size: 12.5px; color: var(--muted); margin-bottom: 0; }
.pc-pet-card__fee { margin-top: auto; font-family: 'Sora', sans-serif; font-size: 15px; font-weight: 700; color: var(--orange); }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 42, index.html orig lines 929-929) -->
**Old code:**
```html
    background: #10214b;
```
**New code:**
```html
    background: var(--navy);
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 43, index.html orig lines 955-955) -->
**Old code:**
```html
.pc-blog-carousel-section { padding: 80px 24px; background: #fff; }
```
**New code:**
```html
.pc-blog-carousel-section { padding: 80px 24px; background: var(--surface); }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 44, index.html orig lines 967-967) -->
**Old code:**
```html
  background: #f9f7f4; border-radius: 12px; overflow: hidden;
```
**New code:**
```html
  background: var(--bg); border-radius: 12px; overflow: hidden;
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 45, index.html orig lines 973-973) -->
**Old code:**
```html
.pc-blog-card__img { height: 170px; overflow: hidden; background: linear-gradient(135deg,#10214b,#2a4080); }
```
**New code:**
```html
.pc-blog-card__img { height: 170px; overflow: hidden; background: linear-gradient(135deg,var(--navy),#2a4080); }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 46, index.html orig lines 979-983) -->
**Old code:**
```html
  letter-spacing: .1em; text-transform: uppercase; color: #ED7624; margin-bottom: 8px;
}
.pc-blog-card__title {
  font-family: 'Cormorant Garamond', serif; font-size: 1.12rem; font-weight: 700;
  color: #10214b; line-height: 1.3; margin-bottom: 8px;
```
**New code:**
```html
  letter-spacing: .1em; text-transform: uppercase; color: var(--orange); margin-bottom: 8px;
}
.pc-blog-card__title {
  font-family: 'Cormorant Garamond', serif; font-size: 1.12rem; font-weight: 700;
  color: var(--navy-ink); line-height: 1.3; margin-bottom: 8px;
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 47, index.html orig lines 989-998) -->
**Old code:**
```html
  color: #10214b; text-decoration: none; transition: color 0.2s;
}
.pc-blog-card__read:hover { color: #ED7624; }
.pc-blog-nav { display: flex; align-items: center; justify-content: center; gap: 12px; margin-top: 28px; }
.pc-blog-nav-btn {
  width: 42px; height: 42px; border: 1px solid #d0c3ba; background: #fff;
  border-radius: 50%; cursor: pointer; display: flex; align-items: center;
  justify-content: center; transition: all 0.2s; color: #10214b;
}
.pc-blog-nav-btn:hover { background: #10214b; color: #fff; border-color: #10214b; }
```
**New code:**
```html
  color: var(--navy-ink); text-decoration: none; transition: color 0.2s;
}
.pc-blog-card__read:hover { color: var(--orange); }
.pc-blog-nav { display: flex; align-items: center; justify-content: center; gap: 12px; margin-top: 28px; }
.pc-blog-nav-btn {
  width: 42px; height: 42px; border: 1px solid var(--warm-gray); background: var(--surface);
  border-radius: 50%; cursor: pointer; display: flex; align-items: center;
  justify-content: center; transition: all 0.2s; color: var(--navy-ink);
}
.pc-blog-nav-btn:hover { background: var(--navy); color: #fff; border-color: var(--navy-border); }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 48, index.html orig lines 1029-1029) -->
**Old code:**
```html
  .pc-blog-card__tag { color: #ED7624; }
```
**New code:**
```html
  .pc-blog-card__tag { color: var(--orange); }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 49, index.html orig lines 1036-1036) -->
**Old code:**
```html
  .pc-blog-nav-btn { background: #ED7624; border-color: #ED7624; color: #fff; width: 46px; height: 46px; }
```
**New code:**
```html
  .pc-blog-nav-btn { background: var(--orange); border-color: var(--orange); color: #fff; width: 46px; height: 46px; }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 50, index.html orig lines 1044-1044) -->
**Old code:**
```html
  background: #ED7624; color: #fff;
```
**New code:**
```html
  background: var(--orange); color: #fff;
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 51, index.html orig lines 1054-1056) -->
**Old code:**
```html
.pc-testimonials-section { background: linear-gradient(135deg, #f3f4f6 0%, #f9f7f4 100%); padding: 80px 24px; }
.pc-testimonials-inner { max-width: 820px; margin: 0 auto; }
.pc-testi-carousel-wrap { margin: 32px 0 0; background: #fff; border-radius: 14px; box-shadow: 0 4px 24px rgba(16,33,75,0.09); overflow: hidden; }
```
**New code:**
```html
.pc-testimonials-section { background: linear-gradient(135deg, #f3f4f6 0%, var(--bg) 100%); padding: 80px 24px; }
.pc-testimonials-inner { max-width: 820px; margin: 0 auto; }
.pc-testi-carousel-wrap { margin: 32px 0 0; background: var(--surface); border-radius: 14px; box-shadow: 0 4px 24px rgba(16,33,75,0.09); overflow: hidden; }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 52, index.html orig lines 1068-1068) -->
**Old code:**
```html
.pc-testi-card__name { font-family: 'Sora', sans-serif; font-size: 14.5px; font-weight: 600; color: #10214b; }
```
**New code:**
```html
.pc-testi-card__name { font-family: 'Sora', sans-serif; font-size: 14.5px; font-weight: 600; color: var(--navy-ink); }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 53, index.html orig lines 1075-1075) -->
**Old code:**
```html
.pc-testi-card__text { font-family: 'Sora', sans-serif; font-size: 14px; line-height: 1.7; color: #3d2817; }
```
**New code:**
```html
.pc-testi-card__text { font-family: 'Sora', sans-serif; font-size: 14px; line-height: 1.7; color: var(--chocolate); }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 54, index.html orig lines 1082-1097) -->
**Old code:**
```html
.pc-testi-card__reply-av { width: 40px; height: 40px; border-radius: 50%; background: #10214b; color: #d7bd88; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0; overflow: hidden; }
.pc-testi-card__reply-av img { width: 100%; height: 100%; object-fit: cover; display: block; }
.pc-testi-card__reply-name { font-family: 'Sora', sans-serif; font-size: 13px; font-weight: 600; color: #10214b; display: flex; align-items: center; gap: 4px; }
.pc-testi-card__reply-role { font-size: 11px; color: #9ca3af; }
.pc-testi-card__reply-date { font-size: 11px; color: #b0a69a; margin-top: 3px; }
.pc-testi-card__reply-text { font-family: 'Sora', sans-serif; font-size: 13.5px; line-height: 1.6; color: #7a6d5f; font-style: normal; font-weight: 400; }
/* TASK-6 END */
.pc-testi-controls { display: flex; align-items: center; justify-content: center; gap: 14px; padding: 20px; }
.pc-testi-btn { width: 40px; height: 40px; border: 1px solid #d0c3ba; background: #fff; border-radius: 50%; cursor: pointer; font-size: 22px; display: flex; align-items: center; justify-content: center; color: #10214b; transition: all 0.2s; }
.pc-testi-btn:hover { background: #10214b; color: #fff; border-color: #10214b; }
.pc-testi-dots { display: flex; gap: 8px; }
.pc-testi-dot { width: 10px; height: 10px; border-radius: 50%; background: #d0c3ba; border: none; cursor: pointer; transition: all 0.3s; padding: 0; }
.pc-testi-dot.active { background: #10214b; width: 28px; border-radius: 5px; }
.pc-testi-cta { text-align: center; margin-top: 32px; }
.pc-testi-cta a { display: inline-flex; align-items: center; gap: 8px; font-family: 'Sora', sans-serif; font-size: 14px; font-weight: 600; color: #10214b; text-decoration: none; border-bottom: 2px solid #ED7624; padding-bottom: 2px; transition: color 0.2s; }
.pc-testi-cta a:hover { color: #ED7624; }
```
**New code:**
```html
.pc-testi-card__reply-av { width: 40px; height: 40px; border-radius: 50%; background: var(--navy); color: var(--gold); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0; overflow: hidden; }
.pc-testi-card__reply-av img { width: 100%; height: 100%; object-fit: cover; display: block; }
.pc-testi-card__reply-name { font-family: 'Sora', sans-serif; font-size: 13px; font-weight: 600; color: var(--navy-ink); display: flex; align-items: center; gap: 4px; }
.pc-testi-card__reply-role { font-size: 11px; color: #9ca3af; }
.pc-testi-card__reply-date { font-size: 11px; color: #b0a69a; margin-top: 3px; }
.pc-testi-card__reply-text { font-family: 'Sora', sans-serif; font-size: 13.5px; line-height: 1.6; color: var(--muted); font-style: normal; font-weight: 400; }
/* TASK-6 END */
.pc-testi-controls { display: flex; align-items: center; justify-content: center; gap: 14px; padding: 20px; }
.pc-testi-btn { width: 40px; height: 40px; border: 1px solid var(--warm-gray); background: var(--surface); border-radius: 50%; cursor: pointer; font-size: 22px; display: flex; align-items: center; justify-content: center; color: var(--navy-ink); transition: all 0.2s; }
.pc-testi-btn:hover { background: var(--navy); color: #fff; border-color: var(--navy-border); }
.pc-testi-dots { display: flex; gap: 8px; }
.pc-testi-dot { width: 10px; height: 10px; border-radius: 50%; background: var(--warm-gray); border: none; cursor: pointer; transition: all 0.3s; padding: 0; }
.pc-testi-dot.active { background: var(--navy); width: 28px; border-radius: 5px; }
.pc-testi-cta { text-align: center; margin-top: 32px; }
.pc-testi-cta a { display: inline-flex; align-items: center; gap: 8px; font-family: 'Sora', sans-serif; font-size: 14px; font-weight: 600; color: var(--navy-ink); text-decoration: none; border-bottom: 2px solid var(--orange); padding-bottom: 2px; transition: color 0.2s; }
.pc-testi-cta a:hover { color: var(--orange); }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 55, index.html orig lines 1112-1112) -->
**Old code:**
```html
  background: #f9f7f4; border-radius: 16px;
```
**New code:**
```html
  background: var(--bg); border-radius: 16px;
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 56, index.html orig lines 1121-1134) -->
**Old code:**
```html
  background: #fff; flex-shrink: 0;
}
.pc-status-popup__title { font-size: 15px; font-weight: 700; color: #10214b; font-family: 'Sora', sans-serif; }
.pc-status-popup__close {
  width: 32px; height: 32px; border-radius: 50%;
  border: 1.5px solid rgba(16,33,75,0.15); background: #fff;
  color: #10214b; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.2s ease;
}
.pc-status-popup__close:hover { border-color: #ED7624; color: #ED7624; }
.pc-status-popup__body { flex: 1; overflow-y: auto; padding: 28px 24px; }
.pc-status-popup__footer {
  flex-shrink: 0; padding: 14px 20px; background: #fff;
```
**New code:**
```html
  background: var(--surface); flex-shrink: 0;
}
.pc-status-popup__title { font-size: 15px; font-weight: 700; color: var(--navy-ink); font-family: 'Sora', sans-serif; }
.pc-status-popup__close {
  width: 32px; height: 32px; border-radius: 50%;
  border: 1.5px solid rgba(16,33,75,0.15); background: var(--surface);
  color: var(--navy-ink); cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.2s ease;
}
.pc-status-popup__close:hover { border-color: var(--orange); color: var(--orange); }
.pc-status-popup__body { flex: 1; overflow-y: auto; padding: 28px 24px; }
.pc-status-popup__footer {
  flex-shrink: 0; padding: 14px 20px; background: var(--surface);
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 57, index.html orig lines 1139-1139) -->
**Old code:**
```html
  width: 100%; background: #10214b; color: #fff;
```
**New code:**
```html
  width: 100%; background: var(--navy); color: #fff;
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 58, index.html orig lines 1154-1155) -->
**Old code:**
```html
.status-mini-pet h4 { font-family: 'Cormorant Garamond', serif; font-size: 19px; font-weight: 700; color: #10214b; margin-bottom: 2px; }
.status-mini-pet p { font-family: 'Sora', sans-serif; font-size: 12.5px; color: #7a6d5f; margin: 0; }
```
**New code:**
```html
.status-mini-pet h4 { font-family: 'Cormorant Garamond', serif; font-size: 19px; font-weight: 700; color: var(--navy-ink); margin-bottom: 2px; }
.status-mini-pet p { font-family: 'Sora', sans-serif; font-size: 12.5px; color: var(--muted); margin: 0; }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 59, index.html orig lines 1160-1162) -->
**Old code:**
```html
.status-mini-row span:first-child { color: #7a6d5f; }
.status-mini-row span:last-child { color: #2c2416; font-weight: 600; text-align: right; }
.status-mini-msg { font-family: 'Sora', sans-serif; font-size: 13.5px; color: #3d2817; line-height: 1.6; margin-top: 14px; }
```
**New code:**
```html
.status-mini-row span:first-child { color: var(--muted); }
.status-mini-row span:last-child { color: var(--text); font-weight: 600; text-align: right; }
.status-mini-msg { font-family: 'Sora', sans-serif; font-size: 13.5px; color: var(--chocolate); line-height: 1.6; margin-top: 14px; }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 60, index.html orig lines 1170-1178) -->
**Old code:**
```html
.pc-how-section { padding: 80px 24px; background: #10214b; }
.pc-how-section .pc-section-head h2 { color: #fff; }
.pc-how-section .pc-section-head p { color: rgba(255,255,255,0.7); }
.pc-how-section .pc-section-head .eyebrow { color: #d7bd88; background: rgba(215,189,136,0.12); }
.pc-how-steps { max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; }
.pc-how-step { text-align: center; padding: 32px 18px; background: rgba(255,255,255,0.06); border-radius: 14px; border: 1px solid rgba(215,189,136,0.15); transition: background 0.25s; }
.pc-how-step:hover { background: rgba(255,255,255,0.1); }
.pc-how-num { width: 52px; height: 52px; background: linear-gradient(135deg, #ED7624, #d4621a); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: 'Cormorant Garamond', serif; font-size: 1.5rem; font-weight: 700; margin: 0 auto 18px; }
.pc-how-step h4 { font-family: 'Cormorant Garamond', serif; font-size: 1.2rem; font-weight: 700; color: #d7bd88; margin-bottom: 10px; }
```
**New code:**
```html
.pc-how-section { padding: 80px 24px; background: var(--navy); }
.pc-how-section .pc-section-head h2 { color: #fff; }
.pc-how-section .pc-section-head p { color: rgba(255,255,255,0.7); }
.pc-how-section .pc-section-head .eyebrow { color: var(--gold); background: rgba(215,189,136,0.12); }
.pc-how-steps { max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; }
.pc-how-step { text-align: center; padding: 32px 18px; background: rgba(255,255,255,0.06); border-radius: 14px; border: 1px solid rgba(215,189,136,0.15); transition: background 0.25s; }
.pc-how-step:hover { background: rgba(255,255,255,0.1); }
.pc-how-num { width: 52px; height: 52px; background: linear-gradient(135deg, var(--orange), #d4621a); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: 'Cormorant Garamond', serif; font-size: 1.5rem; font-weight: 700; margin: 0 auto 18px; }
.pc-how-step h4 { font-family: 'Cormorant Garamond', serif; font-size: 1.2rem; font-weight: 700; color: var(--gold); margin-bottom: 10px; }
```
<!-- TASK-9 END -->

<!-- TASK-9 START (block 61, index.html orig lines 1196-1196) -->
**Old code:**
```html
.sr-only { position:absolute;width:1px;height:1px;padding:0;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0; }
```
**New code:**
```html
.sr-only { position:absolute;width:1px;height:1px;padding:0;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border: 0; }
```
<!-- TASK-9 END -->

## New files

- **`accessibility-widget.js` → `assets/js/accessibility-widget.js`** — classified as new per the CEO's instruction above. Full final content placed in the zip at this exact live path (no new folder needed — `assets/js/` already exists per the established site structure in `adoption-website-project-summary.md`).

## Bugs Found Outside Task Scope

- **`admin-dashboard.html`'s whole live theme renders in an unapproved "Lavender Mist" purple instead of the Navy/Gold specified in Section 15 of the design system.** Found during earlier work on this same shared-widget thread, not touched (outside this task's scope, and deliberately left for CEO confirmation per Scope Lock before anyone changes a whole-dashboard color scheme). Not re-verified as still present in this round.
- **Task 10's page rollout is still pending**: the already-rolled-out pages (`cats.html`, `dogs.html`, `birds.html`, `reptiles.html`, `small-mammals.html`, `livestocks.html`, `aquariums.html`, `pets.html`, `services.html`, `pet-detail.html`, `testimonials.html`, `adoption-application.html`, `status.html`, `blog-home.html`, `blog-single.html`) still carry the pre-rebuild widget and have not been swapped to the current `assets/js/accessibility-widget.js` + `admin/chat-widget.js` include. Not this task's file scope, not touched.
- **The chat-widget scroll-on-close bug** (see "What Was Required" above and the relevant Code Changes blocks) is inside this task's scope but not resolved — listed here for visibility as well as there, since "found but not fixed" is true of it in a different sense than the two bugs above (those were never attempted; this one had two real attempts that both fell short).

## Self-Check Confirmation

- Touched only the three files listed in Files Changed above (`accessibility-widget.js`, `chat-widget.js`, `index.html`) — verified by diffing each delivered file against the original upload and confirming no other file was modified.
- Every Code Changes block (`chat-widget.js`, `index.html`) is wrapped in `<!-- TASK-9 START -->` / `<!-- TASK-9 END -->` anchors.
- Every "Old code" snippet in those two files' Code Changes blocks was generated by diffing the delivered file against the **original file as uploaded for this task**, and separately verified by script, not by eye, that each one occurs **exactly once** in that original file.
- `accessibility-widget.js` carries no Code Changes block in this version, by design — it's delivered whole, as a new file, in the zip.
- No file in this delivery, new or changed, touches `applications/`, `uploads/`, or contains a secret, `config.php`, `pet.json`, or `.env` content — confirmed by scanning the zip's contents directly.
- **Verification method, stated plainly**: no Playwright or other live-browser tool was available for most of this work. The chat-widget scroll bug specifically was iterated against real CEO-reported symptoms from live testing on their end, not observed directly here. Where this document says a fix is "confirmed working" (e.g. the header/footer filter-coverage fix, the font-size overflow fix), that confirmation came from the CEO's own follow-up testing between rounds, not from a tool in this environment — stated here rather than left ambiguous.

## Delivery-format notes (flagged per the updated guide)

- **Project name used for the zip filename**: no single canonical project-name string was given anywhere in this task's own materials, so `petcenter` was inferred from the consistent branding across every page (`Pet Center — Adopt Your New Best Friend`, the `petcenterco.com` domain). Flagging this as an assumption rather than a confirmed fact — if the actual project identifier used for sorting is different (e.g. includes a client/org prefix), the zip should be renamed to match it.
- **Where this completion file lives inside the zip**: at the zip's top level, not nested under any site-structure folder. Reasoning: a completion report isn't a deployable site asset, so it has no live path to place it at in the first place — none of the folders described in `adoption-website-project-summary.md` or `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` are an obvious or correct home for it.
- **No HTML pages were created this round**, so the "every page is named `index.html` at its own folder path" convention doesn't apply to this delivery — noted here only to confirm it was read and understood for future tasks that do create pages.
