# Task B5 — Completion Doc

## Task Number
TASK B5 — Visitor-side trust signal + Adopter Profile

## What Was Required
A reusable Adopter Profile (household type, other pets in home + species if yes, kids in home,
experience level, short free-text "why this pet") filled once in visitor account settings, later
auto-attached to a visitor's first enquiry on a listing — plus a visitor track record display
(completed adoptions from D1's placement records, thumbs received if reviews-of-visitors is in scope
for B3, else a "New to Pet Center" fallback).

## Status
**Partial — by the CEO's own instruction.** The Adopter Profile settings page and track record display
are complete, tested, and audited twice. The enquiry-attachment and the merchant-facing enquiry view are
intentionally not started — on hold until Task D1's enquiry/message form is delivered and the CEO
confirms the attachment point.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `account-settings.html` | `account/settings/index.html` **NEW FOLDER PATH — see the NEW FOLDERS TO CREATE box below.** | New | No — not shared with another task. Does not call `showAuthPrompt()` (Task A4); the profile is filled from an already-logged-in context. |

## Code Changes
N/A — no existing file was changed. `account-settings.html` is a brand-new file; see **New Files** below.
(Its header, footer, hamburger, base styles and button system were copied from `cats.html`, but that is
a read — nothing in `cats.html` itself was edited, so there is no old/new snippet pair to show.)

## New Files
- `account/settings/index.html` — New file — in the zip. The visitor account settings page: Adopter
  Profile form (locked Section 24 dropdown shell for all single-select fields, conditional species
  field, free-text "why this pet") and the visitor track record display, with the "New to Pet Center"
  fallback for first-timers. Persistence is a `localStorage` stub keyed on a placeholder visitor email,
  clearly marked with `TODO(integration)` comments for where the real account backend plugs in.

### !!! NEW FOLDERS TO CREATE (NOT FOUND IN EITHER PROJECT SUMMARY) !!!
Neither `adoption-website-project-summary.md` (Section 1 folder structure) nor
`ADMIN_DASHBOARD_PROJECT_SUMMARY.md` contains an `account/` area, so these folders do not exist on the
live site yet. **The integrator must create them** (unzipping on top of the site root does this
automatically):

| New folder to create | Where | Contains |
|---|---|---|
| `account/` | Directly inside the site root (`public_html/adopt/`), as a sibling of `signin/`, `apply/`, `blog/` | Only the `settings/` sub-folder for now |
| `account/settings/` | Inside `account/` | `index.html` (the page above) |

Live URL once deployed: `adopt.petcenterco.com/account/settings/`. The path is my choice (most consistent
sibling of `signin/`), not a documented convention, so the CEO may relocate it; if so, nothing inside the
file depends on its own location (all links are absolute from the site root).

**Naming rule followed:** the page is a folder containing `index.html`, never a loose `account-settings.html`
in a URL. The source name `account-settings.html` exists only as my working name.

**Zip contents:** this completion file sits at the zip root for the integrator to read; it is not a site
file and must **not** be copied into the live site. Only `account/` goes live.

## Bugs Found Outside Task Scope
- `cats.html`'s footer uses a 4-column grid (`2fr 1fr 1fr 1.5fr`) for 5 children (brand + 4 columns),
  so "Visit Us" wraps to a second row at all widths. Present in `cats.html` itself, not introduced here
  — not fixed, per Scope Lock.
- The header copied from `cats.html` always shows "Sign Up," including for a logged-in visitor. No
  logged-in header variant exists anywhere in the current docs. Needs a decision from whoever owns the
  account system — not fixed here, per Scope Lock.

## Pending, Open Items and Doubts (everything that keeps this task incomplete or uncertain)
**Incomplete by instruction (waiting on Task D1):**
1. Auto-attach of the Adopter Profile to a visitor's first enquiry on a listing. Needs D1's enquiry/message form files and completion doc, then CEO confirmation.
2. The merchant-facing enquiry view (in this task's file zone): shows the attached profile and track record to the merchant messaged. Same dependency. Until it exists, the privacy rule "visible only to the merchant they messaged" is not enforced anywhere.

**Stubs that must be reconciled:**
3. Completed adoptions is stubbed at 0; real source is D1's placement records (`placement_complete`, visitor = adopter). Not reconciled.
4. Thumbs received is stubbed `null` (hidden) because reviews-of-visitors scope is unconfirmed with B3's worker. Not reconciled.
5. Persistence is `localStorage` under a placeholder email. Needs the CEO's real account system and a backend; the profile is not private or durable until then.
6. Cross-project reconciliation (flag only, per task text): the separate pet-match/request project's stub "registered visitor" tagging, keyed on visitor email, needs wiring to this real account area once both are live.

**Doubts / decisions needed:**
7. Live path `account/settings/index.html` is my own choice (see New Folders box). Confirm or relocate.
8. Nothing links to this page yet. No header/account menu entry exists in the current docs, and the account system is out of scope, so there is no way for a visitor to reach it.
9. The copied `cats.html` header shows "Sign Up" even for a logged-in visitor; no logged-in header variant is specified.
10. Page is for logged-in visitors only and holds personal data; recommend `noindex` and an auth check by the account system. Not added, to avoid changing finished work.
11. Whether "why this pet" and the other fields should be required before a first enquiry is not specified; all are optional as built.
12. Zip name `Rehoming-Platform-Task-B5.zip` is derived from the project file `Rehoming-Platform-Task-Assignments.md`; correct me if the project name should differ.
13. Unverified carry-over: an earlier note (from another task's session) claimed a second revision of the task document added Report/Review/`held_for_review`/`is_test_account` contracts. Not found in the uploaded `Rehoming-Platform-Task-Assignments.md`; not acted on. If a newer document exists, send it.

## Self-Check Confirmation
- [x] Touched only one file, `account-settings.html` / `account/settings/index.html` — no shared file
      was edited, confirming Scope Lock.
- [x] It is a new file, so no anchor-commented old/new code pair applies; `<!-- TASK-B5 START/END -->`
      anchors still wrap the page-specific content inside the file itself, distinct from the verbatim
      `cats.html` chrome.
- [x] No "old code" claims to verify against an original file — N/A for a new file.
- [x] Zip `Rehoming-Platform-Task-B5.zip` contains exactly two things: this completion file (root) and `account/settings/index.html`; the one site file matches the path listed above and the new folders are flagged.
- [x] Confirmed no secrets: the zip contains no `config.php`, `pet.json`, `.env`, nothing under
      `applications/`, `uploads/`, `chat-media/`, or `logs/`, and a manual grep for
      password/secret/token/api_key strings in the file returned only an unrelated CSS comment
      ("design tokens").
- [x] Tested with Playwright (desktop, dropdown open + flip-up, conditional field reveal/clear,
      save→reload persistence, all three track-record states, mobile hamburger + stacked layout) —
      see SECOND VERIFICATION ROUND below for the full list and what it caught.
- [x] Enquiry-attachment and merchant-facing enquiry view correctly left un-started, per the CEO's
      explicit hold instruction pending Task D1.

---

## SECOND VERIFICATION ROUND

Triggered by a full re-audit against the current task document, `cats.html`, the design system, and
both project summaries. Only `account-settings.html` was touched (Scope Lock respected).

### Defects found in the first version and fixed
1. **Header/footer CSS was not actually verbatim.** It had been reconstructed from memory; 14 rules
   differed from `cats.html` (nav padding/fonts, hamburger animation, mobile menu behaviour, footer
   bottom/links). Re-extracted programmatically from `cats.html` and diffed: header markup, footer
   markup, all chrome/button/base CSS rules, and the chrome JS are now identical. Anchor comments from
   other tasks inside the header/footer markup (TASK-1, TASK-2) are preserved unedited.
2. **Invented a custom save button** (`.pc-btn-save`). Replaced with the site's canonical
   `.pc-btn .pc-btn--orange`.
3. **Class collision:** the page's card class `.pc-card` collided with `cats.html`'s own `.pc-card`
   (`overflow:hidden`, hover lift), which would have clipped the dropdown panels. Renamed to `.b5-card`.
4. **Dropdown flip-up bug:** the panel anchored to the whole field rather than the trigger, so flipping
   upward would have covered the trigger itself. `.dropdown` is now the positioning context. Verified in
   a short viewport: panel sits fully above the trigger with no overlap.
5. **Track record:** a visitor with 0 adoptions and 0 thumbs saw a row of zeros instead of the required
   "New to Pet Center" fallback. Fixed. Values are rendered with `textContent` only (no `innerHTML` with
   data), so real data can never inject markup later.
6. **Stale data:** switching "Other pets" from Yes to No kept the old species text, which would have
   saved and shared stale data. Now cleared on save (verified).
7. Added `--border` / `--accent` page-scoped CSS variables, since `cats.html`'s own `:root` doesn't
   define them and the page needs both.

### Re-verified (Playwright)
No JS console errors; no horizontal scroll at a 390px mobile width; dropdown open, flip-up, and
save/clear behaviour all correct; all three track-record states (0/0 → fallback, adoptions only, both
populated) render correctly; mobile hamburger menu opens/closes as in `cats.html`.

### Dropdown confirmation (per the project's dropdown directive)
All four dropdowns (Household Type, Pet Experience Level, Other Pets In Home, Kids In Home) use the
modern "Message Bubble" dropdown component from `PETCENTER_DESIGN_SYSTEM_v1.0.md` Section 24 (sharp
top-right notch, layered shadow, fade+scale+slide, 3px Gold selected bar, outside-click/Escape close,
smart flip). The search bar variant was deliberately omitted — each list has only 2–3 fixed options.
No native `<select>` was used anywhere.

### Shared contracts built against
- Listing `status` (owned by D1): "completed adoptions" is stubbed to count `placement_complete` where
  the visitor was the adopter. **Not yet reconciled** — D1's completion file/files not yet received.
- Reviews-of-visitors / thumbs (B3 scope): unconfirmed, so `thumbsReceived` is stubbed `null` and
  hidden from display until confirmed. **Not yet reconciled.**

### Not done yet (explicitly open, on hold per CEO instruction)
- Auto-attaching the profile to a visitor's first enquiry on a listing (depends on D1's enquiry form).
- The **merchant-facing enquiry view** named in this task's file zone, which shows the attached profile
  and track record to the specific merchant messaged, and is where the "never visible to any other
  merchant" privacy rule actually gets enforced. Depends on the same D1 files.
- Real persistence: the profile currently saves to a `localStorage` stub keyed on a placeholder email.
  Privacy of the profile can't be properly enforced until a real visitor account backend exists (the
  CEO's own build, out of scope here).

### Unverified note carried over from the last round
A prior memory note (apparently from a different task's session) referenced a "second revision" of the
task-assignments document adding Report/Review/`held_for_review`/`is_test_account` shared contracts.
That content could not be found anywhere in the currently uploaded `Rehoming-Platform-Task-Assignments.md`
— the Shared Contracts section there only lists the A1/A2/D1+E2/B2/B3/B1 entries. Flagged as unverified
and not acted on. If a newer document exists, please send it.
