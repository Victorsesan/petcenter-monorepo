# Task-2-Completion.md

## Task Number
Task 2 — Accept-Language First-Visit Detection

## What Was Required
Redirect a genuinely first-time visitor (no saved language preference yet)
away from the English homepage to their browser's preferred supported
language, using the real `Accept-Language` HTTP header. This is separate
from `lang-redirect.js` (Task 1), which only redirects on a mismatch
against an already-saved `preferences.lang`. The task also required
confirming PHP availability per page first, and escalating if that meant
per-page duplication.

**PHP-availability finding:** the site's pages are static, hand-authored
`.html` files with no PHP execution wired into them. Every PHP file in
either project doc is a separate `.php` backend script, and neither doc
shows an `AddHandler`/`AddType` making `.html` run PHP. Using PHP would
mean converting every page to `.php` (per-page duplication, and a break
from the folder/clean-URL convention in Warning & Instructions rule 5) or
adding an unverified server handler. Instead, Apache `mod_rewrite` reads
the real `Accept-Language` header and redirects at the web-server layer,
in one shared root file, with zero changes to any page file.

CEO-directed extension (outside original scope, recorded per Scope Lock):
the Settings widget (`accessibility-widget.js`) now recognizes the
`pc_lang_checked` cookie and seeds `preferences.lang` from the URL once,
so its language picker matches an auto-redirected visitor.

## Files Changed

| Source file name | Live path | Changed or new | Shared with another task? |
|---|---|---|---|
| `.htaccess` | `.htaccess` | New | No. Not documented as touched by any other task. See the merge caveat under New files. |
| `accessibility-widget.js` | `assets/js/accessibility-widget.js` | Changed | Yes. Built by the Accessibility Widget Rebuild and revised by that work; this is a small isolated addition (CEO-directed). |

## Code Changes

### accessibility-widget.js → assets/js/accessibility-widget.js

Both edits are wrapped in uniquely named `TASK-2 FOLLOWUP` anchor
comments. Each old-code snippet below was checked to occur exactly once in
the original file, and applying both edits to the original reproduces the
delivered file byte for byte.

**Edit 1 — new functions, added immediately after `setPreferences()`**

Old code:
```js
    try { localStorage.setItem(PREFS_KEY, JSON.stringify(next)); } catch (e) { /* storage unavailable -- in-memory state still applies for this tab */ }
    return next;
  }
```

New code:
```js
    try { localStorage.setItem(PREFS_KEY, JSON.stringify(next)); } catch (e) { /* storage unavailable -- in-memory state still applies for this tab */ }
    return next;
  }

  // TASK-2 FOLLOWUP (Accept-Language Auto-Detect Sync) START -- CEO-directed,
  // outside this script's original task scope; documented as such in
  // Task-2-Completion.md rather than folded in silently.
  //
  // Task 2's root .htaccess redirects a genuine first-time visitor straight
  // to their browser-preferred language folder via a real HTTP 302, entirely
  // server-side, before any JS on this page has run. It intentionally never
  // touches localStorage (Apache can't read or write it) -- it only sets a
  // one-year "pc_lang_checked" cookie so the same visitor isn't re-evaluated
  // on every future homepage load. Left alone, that means a visitor who was
  // auto-redirected this way has a `preferences.lang` that was simply never
  // written -- the page still displays correctly (i18n-core.js falls back to
  // the URL's own language segment when no preference is saved), but this
  // widget's own language picker would show "English" regardless, since it
  // reads only `preferences.lang`, not the URL. This closes that gap by
  // seeding `preferences.lang` from the URL once, the first time this script
  // runs for such a visitor.
  //
  // SEGMENT_TO_LANG below is copied verbatim from lang-redirect.js's own
  // map of the same name, not re-derived -- reusing the project's one
  // existing source of truth for "URL segment -> preferences.lang value"
  // rather than inventing a second one that could drift out of sync with it
  // (in particular "zh" -> "zh-Hans", per Task 8's CEO-confirmed decision).
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

  // Gated on `lang` being truly ABSENT from a freshly-read `preferences`
  // object (never saved before, not even as an explicit "en") -- so a
  // visitor who has ever actually made a language choice through this
  // widget, English included, is never silently overridden. This is a
  // one-time seed, not an ongoing sync: once it writes, `parsed.lang` is no
  // longer absent, so this is a no-op on every later run for that visitor,
  // same as how Task 2's own cookie only lets the .htaccess redirect itself
  // fire once.
  function seedLangFromAutoDetect() {
    if (!readCookie('pc_lang_checked')) return; // Task 2 never evaluated this visitor at all (e.g. local file:// testing, or they landed on a non-homepage page first) -- nothing to sync
    var seg = getUrlLangSegment();
    if (!seg) return; // on the English root -- nothing to correct
    try {
      var raw = localStorage.getItem(PREFS_KEY);
      var parsed = raw ? JSON.parse(raw) : {};
      if (parsed.lang !== undefined && parsed.lang !== null) return;
      var merged = getPreferences();
      merged.lang = SEGMENT_TO_LANG[seg];
      localStorage.setItem(PREFS_KEY, JSON.stringify(merged));
    } catch (e) { /* storage unavailable -- nothing to seed; the page still renders correctly via i18n-core.js's own URL-segment fallback, this widget's picker just won't reflect it */ }
  }
  // TASK-2 FOLLOWUP (Accept-Language Auto-Detect Sync) END
```

**Edit 2 — one call added inside `init()`, before the first `applyAll(getPreferences())`**

Old code:
```js
    wireControls();
    applyAll(getPreferences());
  }
```

New code:
```js
    wireControls();
    // TASK-2 FOLLOWUP: see seedLangFromAutoDetect() above -- must run before
    // the first getPreferences() read below so the picker/applied styles
    // reflect it on first paint, not one load later.
    seedLangFromAutoDetect();
    applyAll(getPreferences());
  }
```

## New files
- `.htaccess` — New file — in the zip (`WEB-PROJECTS-1-2-Task-2.zip`, at the zip root, which is the site root)

No new folder is needed for this file. Its live path is `.htaccess` at the
site root itself — a location already documented to exist in
`adoption-website-project-summary.md` (Section 1 mentions an existing
`.htaccess` use for `Deny from all` on upload-staging directories, which
confirms the site root is a real, already-present location, not a path
this task is introducing). This task introduces no `.html` pages, so the
project's strict `index.html`-per-folder deployment convention does not
apply to anything in this delivery.

Notes on this file:
- No existing root `.htaccess` was among Task 2's provided files, so no
  existing content for it was known. The live Hostinger server may
  already have one. If it does, this is really a change: do not overwrite
  it. Merge the block between `# TASK-2 START` and `# TASK-2 END` into the
  existing file (`RewriteEngine On` is safe to repeat). This is a pending
  item for the integrator to confirm against the live server, since it
  could not be checked from this sandbox.
- Behavior: only evaluates `/` and `/index.html`; skips visitors with the
  `pc_lang_checked` cookie (anchored match, 365-day lifetime, `CO` flag
  lifetime is in minutes); skips bots by User-Agent (including
  `bingpreview`); redirects es/pt/fr/ar and Simplified Chinese only
  (`zh-TW`/`zh-HK`/`zh-Hant` excluded, since only Simplified content
  exists); any other language, English included, just gets the cookie so
  the check runs once.
- Not verified against a real Apache (none in the sandbox) — still a
  pending verification item. Recommended live check: `curl -sI -H
  "Accept-Language: es" https://adopt.petcenterco.com/` should return a
  `302` to `/es/` with the cookie set, and a second request carrying the
  cookie should not redirect.

## Bugs Found Outside Task Scope
None found.

Files-to-Request note (not a bug, flagged for whoever maintains the task
doc): Task 2's entry lists only `adoption-website-project-summary.md`, but
the language folder names and the `zh` → `zh-Hans` rule live only in
`WEB-PROJECTS-1-2-TASK-ASSIGNMENTS-v12.md` (Task 1 architecture notes).
It should be listed too. `admin/chat-widget.js` was checked and has no
language code, so it was not touched.

## Self-Check Confirmation
- Files touched: `.htaccess` (new, in the zip) and `assets/js/accessibility-widget.js`
  (changed, documented above as old/new code, not physically in the zip).
  Nothing else.
- Every old-code snippet is an exact copy from the original file and
  occurs once in it; applying the edits reproduces the delivered file.
- Zip name: `WEB-PROJECTS-1-2-Task-2.zip`, matching the canonical project
  file name (`WEB-PROJECTS-1-2-TASK-ASSIGNMENTS-v12.md`) plus this task's
  number, per the 2026-10-25 hand-in update. Contents: `.htaccess` (new
  file, at its real live path, site root as zip root) and this completion
  file, joined together in the one zip. No `config.php`, `pet.json`,
  `.env`, nothing under `applications/`, `uploads/`, `chat-media/` or
  `logs/`, and no passwords, tokens or keys.
- Widget change verified with Playwright against the real edited file
  served over HTTP: seeded `es` on `/es/` with cookie and no prior prefs;
  not overridden when `en` already saved; nothing seeded without the
  cookie; nothing seeded on the English root; the picker showed
  "Español" selected. `node --check` clean. Change is purely additive.
- Second verification round (re-audit) fixed two `.htaccess` gaps (cookie
  match anchored to cookie boundaries, `bingpreview` added to the bot
  list), retested with 8 regex cases, all passing. No new files were
  created by that round.
- Pending/open items carried into this hand-in (not blockers, but real):
  whether a root `.htaccess` already exists live and needs merging instead
  of a straight overwrite (unverifiable from this sandbox), and the
  Files-to-Request doc gap noted above under Bugs Found Outside Task
  Scope. Both are documented here per the 2026-10-25 instruction to record
  anything pending or in doubt before zipping.
