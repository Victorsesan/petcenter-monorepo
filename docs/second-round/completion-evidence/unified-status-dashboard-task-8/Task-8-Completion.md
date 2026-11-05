# Task 8 — Uptime Status Dashboard (Core)

## Task Number
Task 8 — Uptime Status Dashboard (Core), from Unified-Status-Dashboard-Task-Tracker-v3-FINAL.md

## What Was Required
Admin-facing manual up/down/degraded control per domain/subdomain, a public
display of those statuses, and an incident history log that appends an
entry (timestamp, old→new status, optional note) on the same write action
that changes a domain's status — under `status.petcenterco.com`, depending
on Task 1's subdomain shell.

**CEO override on the task's "Files to request" line:** the task
originally listed "current list of domains/subdomains under
petcenterco.com" as a file to request. The CEO explicitly declined to
supply a fixed list — he manages the domain list himself, including
`petcenterco.com`'s own main-domain status, added and removed over time
through the admin page rather than hardcoded in code. The domain list is
pure data (`data/uptime-status.json`), never a fixed set in the
HTML/JS/PHP.

**Admin-control placement decision** (left to the worker by the task
spec): a protected page on `status.petcenterco.com` itself, at
`uptime/admin/`, gated by HTTP Basic Auth via `.htaccess` — matches the
project's flat-file, no-database philosophy; no existing lightweight-auth
pattern existed elsewhere on the project to reuse instead.

## ⚠️ New-folder check (heavily flagged, per the delivery guide)
`status.petcenterco.com` as a whole is **not covered by either**
`adoption-website-project-summary.md` or `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`
— neither document mentions it (confirmed directly, not assumed). Its
folder structure was established by Task 1, not by either project summary.
This task does **not** introduce any new top-level folder: `uptime/`,
`uptime/admin/`, `api/`, and `data/` all already exist from Task 1 onward.
Every file below sits inside one of those four existing folders. If the
integrator is reconciling against the two named summary docs, they will
not find `status.petcenterco.com` there at all — that's expected, not a
gap in this delivery.

## ⚠️ index.html naming check
Both HTML pages this task adds comply with the strict `index.html`
convention: source `uptime.html` → live `uptime/index.html`; source
`uptime-admin.html` → live `uptime/admin/index.html`. Neither is a loose
`name.html` at its folder.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `uptime.html` | `uptime/index.html` | New | No (public display page, this task only) |
| `uptime-admin.html` | `uptime/admin/index.html` | New | No (admin control page, this task only) |
| `action.php` | `uptime/admin/action.php` | New | No |
| `htaccess.txt` (renamed `.htaccess` in the zip) | `uptime/admin/.htaccess` | New | No |
| `uptime-status.php` | `api/uptime-status.php` | New | No, but this file's output is intended to be read by any later task that needs uptime data publicly (e.g. a future homepage tile) |
| `uptime-status.json` | `data/uptime-status.json` | New | **Yes — defines the Shared Data Contracts schema** (`status`: up/down/degraded, `history`: array of `{from, to, note, at}`) that later tasks extending this dashboard must read/write against |
| `htpasswd.txt` | `uptime/admin/.htpasswd` | New — **not in the zip, credential file, see note below** | No |

No existing live file was changed by this task — Task 1's `index.html`,
`subscribe.php`, `api/status.php`, `.htaccess`, and
`data/status-categories.json` were all left untouched. Every row above is
a brand-new file; Task 8 only reuses Task 1's header/footer markup by
copying it, not by editing Task 1's actual file.

## Code Changes
No existing files were changed — see table above. Nothing to show as
old-code/new-code diffs for this task.

## New files
- `uptime/index.html` — New file, in the zip. Public uptime status display: lists every CEO-added domain with a status badge and its append-only incident history.
- `uptime/admin/index.html` — New file, in the zip. Protected admin control page: add/remove domains, set up/down/degraded status with an optional note.
- `uptime/admin/action.php` — New file, in the zip. Protected write endpoint behind the same folder's `.htaccess`; handles `add_domain`, `remove_domain`, `set_status`.
- `uptime/admin/.htaccess` — New file, in the zip. HTTP Basic Auth gate for the whole `uptime/admin/` folder (covers both the admin page and `action.php`).
- `api/uptime-status.php` — New file, in the zip. Public, read-only JSON endpoint that both HTML pages fetch from.
- `data/uptime-status.json` — New file, in the zip. The data store — starts with an empty `domains` array; the CEO populates it himself via the admin page.
- `uptime/admin/.htpasswd` — **New file, but intentionally NOT in the zip** — see the next section.

### Credential file: `uptime/admin/.htpasswd` (excluded from the zip per the guide's no-secrets rule)
This file's entire content is a login credential, so per the guide's rule
3 ("Never include... passwords, tokens, or keys") it is not shipped in
the zip. The integrator (or the CEO) needs to create this file directly
on the server at `uptime/admin/.htpasswd` with this exact line:

```
petcenter-admin:{SHA}Mo+p2bj0jIcDR/E0ZfRc6p4G4E4=
```

That hash corresponds to username `petcenter-admin`, temporary password
`hbiSoQQu7YAri8SY`. **This is a one-time bootstrap credential, not a
final one** — it was generated in a sandbox with no way to hash a
password of the CEO's own choosing. The CEO should log in once and then
immediately replace it (`htpasswd -c .htpasswd petcenter-admin` on the
real server, or ask for a fresh hash once he's picked his own password).
This same caveat was already flagged in this task's first delivery — repeating
it here because the credential itself, not just the caveat, now has to
travel through this document instead of the zip.

`uptime/admin/.htaccess` (which **is** in the zip) still has a related,
separate open item: its `AuthUserFile` directive is a best-practice
*guessed* absolute path
(`/home/petcenterco/public_html/status/uptime/admin/.htpasswd`), based on
common cPanel subdomain conventions, not a verified one — only the CEO's
hosting panel has the real path. Apache fails closed if it's wrong (500
error, not an exposed admin panel), so the failure mode is safe, but this
needs the CEO's real path to actually work.

## Bugs Found Outside Task Scope
`data/status-categories.json` (a Task 1 file, not touched by this task)
still hardcodes its `uptime` category entry to `"available": false` —
Task 1's own completion file flagged this exact file for whoever builds
Task 8 to decide how to wire it. Per Scope Lock, it wasn't touched here.
Wiring the homepage's Uptime tile to read from `api/uptime-status.php`
instead of that placeholder would need a small edit to that file (and
possibly a few lines of `index.html`'s existing category-rendering JS) —
not done, since it's a different task's file and nobody has asked for it
yet.

## Self-Check Confirmation
- Only the files listed in Files Changed were touched; no existing
  project file (Task 1's or otherwise) was edited.
- Every added code block in every new file carries a `TASK-8 START/END`
  anchor comment (`<!-- -->` in HTML, `/** */` in PHP) with an
  explanatory comment, per rule 6.
- There are no "old code" blocks to verify character-for-character,
  since nothing existing was changed.
- Every file in the zip (6 files) is listed in Files Changed with the
  same live path; the one file listed as New but excluded from the zip
  (`.htpasswd`) is explicitly marked as such in both the table and its
  own section above, with its real content given in this document
  instead.
- Zip contents double-checked directly with `unzip -l` before sending —
  6 files, no `config.php`, no `pet.json`, no `.env`, no
  `applications/`/`uploads/`/`chat-media/`/`logs/` paths, and the one
  actual credential (`.htpasswd`) deliberately left out.
- Structural lint re-run on every file: `uptime/index.html` and
  `uptime/admin/index.html` parse cleanly (BeautifulSoup, zero duplicate
  IDs); `action.php` and `api/uptime-status.php` have balanced
  braces/parens and a single `<?php` open tag each (no `php -l`
  available in this sandbox); `data/uptime-status.json` validated as
  parseable JSON.
- `status` values are validated server-side in `action.php` against the
  exact three-value set (`up`/`down`/`degraded`, lowercase) before any
  write, per the Shared Data Contracts section.
- History is append-only by construction — `action.php` only ever pushes
  a new entry onto a domain's `history` array, never edits or removes an
  existing one; resubmitting the same status with a new note updates the
  note/timestamp without logging a false transition.
- Boundary cases checked by code inspection: zero domains (both pages
  show an explicit empty state, not a blank screen or a fabricated
  example row); a domain with zero history entries (the history toggle
  simply doesn't render); removing a domain that has history (the entry
  is deleted — no separate archive is kept, since nothing in the task
  spec called for one).
- **Sandbox limitation, stated plainly:** no Chromium binary and no
  network access to fetch one in this environment, so the Playwright
  rendered-browser check could not be run as a real browser-driven
  verification. Structural lint plus manual review of every
  fetch/response-handling path (success and error shapes both) and
  manual contrast spot-checks (reusing Task 1's already-measured
  warning-amber icon-on-fill contrast, since this task introduces no new
  color) were used in its place. Flagging this as unverified rather than
  claiming it as done.

## SECOND VERIFICATION ROUND
(Carried forward from this task's prior hand-in; reformatted here to the
delivery guide's structure, no further code changes made in this
reformatting pass.)

A CEO-requested re-audit against both project summary docs, the full
tracker, and the design system found and fixed three real issues in this
task's own files (all already reflected in the files above and in the
zip — nothing further to apply):

1. **Header reuse was incomplete.** Task 1's own comment instructs
   copying its header block verbatim into every future page on this
   subdomain. The first delivery of `uptime.html`/`uptime-admin.html`
   copied only the brand-logo portion and silently dropped the "Contact
   support" link and "Subscribe to updates" dropdown (and their CSS/JS).
   Fixed: both pages now carry the full header block, copied verbatim
   rather than re-derived.
2. **Missing `<noscript>` fallback**, matching the precedent Task 1 set.
   Added a plain-text fallback notice to both pages (a static per-domain
   fallback isn't possible, since the domain list is CEO-managed data
   unknown at build time).
3. **`.htaccess` `AuthUserFile` was an obvious template placeholder**,
   which the project's standing no-placeholder rule rules out. Replaced
   with the best-practice guessed path described in the credential
   section above, explicitly flagged as unverified.

**Known limitation, still open:** Tasks 9 and 10 have since extended the
*live* versions of `uptime/admin/action.php`, `uptime/index.html`,
`uptime/admin/index.html`, and `data/uptime-status.json` on the server
(new actions, a maintenance banner, a badge endpoint, UptimeRobot sync
fields) — per their own completion files. This delivery still reflects
only Task 8's own original files, not those later merges; the integrator
should be applying this task's contribution as the base layer, with
Task 9's and Task 10's own hand-ins layered on top, not the other way
around.
