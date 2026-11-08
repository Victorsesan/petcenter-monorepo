# Task E4 Completion — Merchant dashboard dark/light mode (reuse pass)

## Task Number
TASK E4 — Merchant dashboard dark/light mode (reuse pass)
(Complexity: Low | Files touched: merchant dashboard shared layout/CSS/JS (new files, per E1) | File zone: merchant dashboard shared layout/CSS/JS files (established by Task E1))

## What Was Required
Give the merchant dashboard (E1's shell, cloned from `admin-dashboard.html`) a Light/Dark/System theme by copying the admin dashboard toggle mechanism: same localStorage key, root-class approach, CSS variable names, three-state cycle, blocking anti-flash `<head>` script, and `prefers-reduced-motion` handling, using the merchant dashboard's own color values.

**Deviations flagged (CEO authorized "desperate mode" decisions; read first):**
1. No true source file existed. The completion file for "the admin dashboard's dark/light toggle task" was never supplied. The closest code (`accessibility-widget.js`) is a binary checkbox using `data-theme` on `<html>` inside the shared `preferences` key, with no System state, no root classes and no anti-flash script. Copying it would break E4's own spec, so E4's written spec was implemented directly: key `theme` (`light|dark|system`), root classes `.light`/`.dark`, `--color-*` tokens, three-state cycle, blocking head script, reduced-motion handling. Dark values come from `PETCENTER_DESIGN_SYSTEM_v1.0.md` Section 19; light values from the `admin-dashboard.html` `:root` block.
2. Design System Section 19 says dark mode lives inside the Settings Widget, never as a standalone toggle, and describes a two-sided switch. E4 says a three-state cycle. Built as one topbar button (sun / moon / monitor icon) that cycles, per E4. Not a floating button. **Decision needed from Design/CEO:** keep the topbar button, or move into the widget.
3. Not verified against the real E1 shell (it does not exist yet). Verified on a stand-in test page that uses the clone's variable names.

## Files Changed
| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `merchant-theme-v1.css` | `assets/css/merchant-theme-v1.css` | New file | No |
| `merchant-theme-v1.js` | `assets/js/merchant-theme-v1.js` | New file | No |
| E1's merchant dashboard shell file (name and path set by E1; does not exist yet) | merchant dashboard directory chosen by E1 (unknown at time of writing) | Changed (integration only, see Code Changes) | Yes, Task E1 (E1a/E1b) |

Test files (`test-harness-e4.html`, `run_e4_test.py`, three screenshots) were used for verification only and are not part of the delivery.

## Code Changes
**Important: no existing file was edited, and there is no "old code" to copy.** The only existing-file touchpoint is E1's merchant shell, which has not been written yet, so exact old snippets cannot exist. The integrator applies the three insertions below to that shell once it exists, each placed at the location described. All blocks carry the `TASK-E4` anchors.

### E1 merchant shell file  →  live path: merchant dashboard directory chosen by E1 (`.../index.html`)

**Insertion 1 — in `<head>`, as the FIRST script, before any stylesheet link. Do NOT add `defer` or `async`.** Place it after the opening `<head>` line (and after `<meta charset>`/viewport meta if present):
```html
<!-- TASK-E4 START: blocking anti-flash theme script + theme stylesheet.
     Adds .light or .dark to <html> before first paint so there is no flash of the wrong theme.
     Reads localStorage key "theme" (light | dark | system); anything else follows the OS. -->
<script>
(function () {
  try {
    var pref = localStorage.getItem('theme');                 // 'light' | 'dark' | 'system' | null
    if (pref !== 'light' && pref !== 'dark') pref = 'system'; // anything else = follow the OS
    var dark = pref === 'dark' ||
      (pref === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.add(dark ? 'dark' : 'light');
  } catch (e) { document.documentElement.classList.add('light'); } // storage blocked: safe default
})();
</script>
<link rel="stylesheet" href="/assets/css/merchant-theme-v1.css">
<!-- TASK-E4 END -->
```
**Insertion 2 — before `</body>`, with the other deferred scripts:**
```html
<!-- TASK-E4 START: theme controller (builds the Light/Dark/System toggle button, handles storage and OS changes) -->
<script src="/assets/js/merchant-theme-v1.js" defer></script>
<!-- TASK-E4 END -->
```
**Insertion 3 (only if E1 renames or removes the `.dash-icon-utils` topbar icon row):** the JS auto-mounts the button into `.dash-icon-utils`. If that class is absent, add this inside the topbar and the button mounts there instead:
```html
<!-- TASK-E4 START: explicit mount point for the theme toggle button -->
<span data-theme-toggle-mount></span>
<!-- TASK-E4 END -->
```
**Public API for later tasks:** `window.PCTheme.get() / set('light'|'dark'|'system') / cycle() / resolved()`.

**Alias note:** `merchant-theme-v1.css` also maps the clone's variable names (`--bg`, `--surface`, `--text`, `--muted`, `--navy`, `--navy-light`, `--gold`, `--orange`, `--success`, `--error`, shadows) onto the generic `--color-*` tokens, so the E1 clone follows the theme without markup edits. Its alias selectors (`:root.light`, `:root.dark`) out-rank the clone's plain `:root` values, so load order does not matter.

## New files
- `assets/css/merchant-theme-v1.css` — New file — in the zip. Light, Dark and clone-alias tokens, smooth 0.3s switch (skipped under reduced motion), and the toggle button styles.
- `assets/js/merchant-theme-v1.js` — New file — in the zip. Theme controller: storage, Light/Dark/System cycle, live OS-change follow, cross-tab sync, toggle button builder and mount.

**NEW FOLDER WARNING — read before unzipping:**
- `assets/js/` is already used on the live site (the widget completion files reference `/assets/js/...`). No action needed.
- `assets/css/` is **not mentioned in either `adoption-website-project-summary.md` or `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`** (only `/assets/images/` is). **Create `assets/css/` at the site root if it does not already exist.** It is the location the Delivery Guide's mapping table names for shared CSS.
- No `.html` page was created, so the `index.html` naming rule does not apply to this delivery.
- The zip root is the site root. `Task-E4-Completion.md` sits at the zip root for the integrator's convenience and **must not be deployed to the site**.

## Bugs Found Outside Task Scope
- `admin-dashboard.html` (clone source), CSS block: about 39 hard-coded white backgrounds (for example `background: #fff` style declarations, and likely hard-coded text colors) cannot follow CSS variables and will stay light in dark mode. Belongs to E1's clone pass: change them to `var(--surface)`.
- `admin-dashboard.html`, CSS near lines 138 (`.pc-btn--primary:hover`): hover gradient uses a raw `#243970`, which cannot be themed. Same E1 pass.
- `admin-dashboard.html`, `.admin-sidebar` (about line 461), `.admin-login` (about line 910), `.pc-status-section` (about line 256): large-area navy gradient fills. Design System Section 19 Rule 5 says brand colors must never be a large fill in dark mode. This module keeps them working with the dark accent tokens, but they likely need redesign (flat `var(--color-surface)` / `var(--color-bg)` plus a thin accent border) during E1's dark-mode integration. Not fixed here (outside scope).
- The copy of `admin-dashboard.html` on hand contains no `<script src>` for `accessibility-widget.js`, although `Task-WidgetPositioning-Completion.md` and `Task-AccessibilityWidget-Completion.md` say it is loaded near the end of `<body>`. Either the copy is stale or the merge is pending. E1 should request the current file. If the widget is present on the merchant dashboard, its own Dark Mode checkbox (it writes `data-theme` in the `preferences` key) will not affect this module (CSS here is class-based), so two controls would exist. Recommended: hide that checkbox on the merchant dashboard, or have the widget call `PCTheme.set()`. Not done.
- `Rehoming-Platform-Task-Assignments.md`, Task E4: it depends on a "toggle task completion file" that does not exist as a standalone deliverable. The spec should say E4 defines the mechanism.

**Pending / open decisions:** (1) topbar button versus Settings Widget placement (deviation 2); (2) integration of the three insertions into E1's real shell; (3) creating `assets/css/` if missing; (4) the hard-coded colors and large navy fills listed above.

## Self-Check Confirmation
- Only the two new files above were created; no existing file was edited, so no old snippet exists to copy. The E1 shell insertions are instructions for a file that does not exist yet.
- Every code block carries `TASK-E4` anchors (balanced, 1 START / 1 END per file) and is commented.
- No dropdowns built; no emoji anywhere (scanned programmatically); icons are inline SVG.
- `-v1` naming kept. No secrets, `config.php`, `pet.json`, `.env`, or `applications/` content in the zip.
- Tested with `node -c` (JS clean) and headless Chromium via Playwright on a stand-in page: first visit follows OS; toggle mounts; cycle goes light, dark, system; dark `--color-bg` computes `#121212`; anti-flash class is present at first parse with stored `dark`; `system` follows live OS change; cross-tab change syncs; with reduced motion no transition class is applied; blocked localStorage falls back to light; `--navy` and `--navy-light` stay distinct in both themes. Screenshots were taken (light, dark, system with OS dark) and reviewed against Section 19 rules.
- The zip contents match the New files list exactly (checked with `unzip -l`, below).

## SECOND VERIFICATION ROUND
Full re-read of both project summaries and the Task E4 section. Scope Lock held; only E4's own files were edited.
- **Bug found and fixed (in `merchant-theme-v1.css`):** the alias block mapped `--navy-light` to the same value as `--navy`, which would have flattened five navy gradients (`.pc-btn--primary` and hover, `.admin-sidebar`, `.admin-login`, `.pc-status-section`) in both themes. Added a distinct `--color-accent-navy-light` token per theme (`#1a2f5c` light, `#92a4db` dark) and aliased `--navy-light` to it. Re-verified in Chromium.
- **Hand-in round:** file paths changed from relative (`./merchant-theme-v1.*`) to root-absolute (`/assets/css/...`, `/assets/js/...`) in the integration snippets and the JS header comment (comment-only change). The standalone head-snippet file was removed; its code now lives in Insertion 1 above because it is an edit to E1's shell, not a deployable file.
- Confirmed: `admin-dashboard.html`'s `data-dash-theme` system is an unrelated visual-redesign feature (the admin summary says "Data Console" and "Calm Panel" were deleted), and the rejected Lavender Mist palette was never used in this module.
