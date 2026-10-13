# ADMIN PROJECT #1: LIVE CHAT SECURITY & SESSION OVERHAUL — TASK ASSIGNMENTS

**Prepared by: VDM, Admin Project #1**
**Project slug for all branches, reports, and anchors: `admin1`**
**Branch format: `task/admin1-[N]` · Report format: `reports/task-admin1-[N].md` · Anchor format: `TASK-admin1-[N] START/END`**

---

## INTRODUCTION

You are a worker on Pet Center Co's live chat security project. Pet Center is a pet adoption platform; this project closes a real, currently-live access-control vulnerability in its live chat system and adds three new protection layers on top of the fix. This document is your task assignment.

**How to read this document:** every task section has two parts — the task itself, and a separate "Recommended Enhancements" subsection underneath it. Read both parts for your task. The enhancements are researched starting points, not final answers — the warning inside each one makes this explicit.

Read the Warning & Instructions section below in full before touching anything. It applies to every task in this document with no exceptions.

---

## TABLE OF CONTENTS
- [Warning & Instructions](#warning--instructions--read-before-editing-anything)
- [Shared Contracts — Published Day One](#shared-contracts--published-day-one)
- [Model & Settings](#model--settings)
- [Task 3 — PIN System](#task-3--pin-system)
- [Task 4 — Lock Toggle](#task-4--lock-toggle)
- [Task 5a — Password System Core](#task-5a--password-system-core)
- [Task 5b — Device Management UI (enhancement-driven, optional)](#task-5b--device-management-ui-enhancement-driven-optional)
- [Task 6 — Password Reset Flow](#task-6--password-reset-flow)
- [Task 7 — Admin Auth-State Badge](#task-7--admin-auth-state-badge)

> **Tasks 1 and 2 are complete.** Their branches (`task/admin1-1`, `task/admin1-2`) are merged into `main`. Their reports are at `reports/task-admin1-1.md` and `reports/task-admin1-2.md`. Read them — especially Task 1's, which documents the real function signatures, table names, audit-log vocabulary, and the `chat-widget.js` session-token plumbing all remaining tasks build on.

---

## ⚠️ WARNING & INSTRUCTIONS — READ BEFORE EDITING ANYTHING

**This section applies to every AI creating, editing, or fixing any file in this project — not just the section you're currently working on.** Treat it as standing orders, not a one-time note.

1. **Self-question before and after every task.** Before running any command, ask yourself whether it's actually correct for the task. After running it, check the result — don't assume it worked because it didn't error. This applies to every single task, not just complex ones: full file linting (balanced tags/braces/parens, no duplicate IDs), checking for wrong code placement, and catching bad structure all come from doing this consistently. Even after moving past a step, look back at it again before finishing — don't treat "I already checked that" as permanent.
2. **Never use emojis anywhere in the page or the code.** Use icons only (inline SVG, matching the existing icon style already in use on the page you're editing). This applies to visible UI text, code comments, console messages — everywhere.
3. **Never create a `.md` or `.txt` file unless explicitly told to.** Don't add scratch notes, summaries, or documentation files as a side effect of a task unless the person asked for one by name.
4. **Carry these instructions through every command you run.** Don't let them apply only to the first task in a session and quietly drop off by the fifth.
5. **Every page is deployed as a folder path, never a bare filename in the URL.** On the server, each page is uploaded as its own folder containing an `index.html` — e.g. the file worked on here as `pet-detail.html` is uploaded to the server as `pets/detail/index.html`, which is what produces the clean URL `https://adopt.petcenterco.com/pets/detail/`. Every internal link and every URL-building line of code inside that file uses the clean folder path, never the `.html` filename. ⚠️ The only exception is admin-based files which aren't HTML files.
6. **Write code comments.** You must always write code comments on all your code to help other AI easily find specific code during debugging and also update the code comment after any changes made in the code. Give a name for each code block and write its comment.
7. **Never try to recreate features from scratch.** First go to the exact file(s) where a feature is documented and use the exact same code for consistency. Some features have `.php` handlers which work together — understand the documentation and the feature code well before proceeding.

### Dev Manager Directives — apply project-wide, no exceptions

8. **Everything in this Warning & Instructions section — rules 1 through 13, and every word of it — is top priority.** Save all of it to memory before beginning any task, and re-check it against your own actions before finishing.

9. **Consult `PETCENTER_DESIGN_SYSTEM_v1.0.md` before making any UI, styling, or animation decision.** Use its Table of Contents to jump directly to the relevant section. For colors: use Section 15's Navy/Gold/Orange palette.

10. **Image/reference-material gate — do not proceed without it.** If a task says a screenshot or reference image is required and it isn't present, stop and wait — do not guess. Reference material may be a full HTML page, not just an image; treat it the same way.

10a. **Design-fidelity lock.** Treat reference material as the literal, exact target. Before calling any design-related task done, use the Playwright headless browser tool to load your finished page and visually compare it against the reference — mandatory, not optional. Keep checking throughout the task, not just once.

11. **Use web search during implementation.** This applies to tool choices, library versions, and current best practices. Search before committing to an approach, especially for anything involving a third-party service, library, or API.

12. **Multi-worker, same-file protocol — Sequential File Ownership Chain with Anchor Comments.** Where more than one task touches the same file:
    - **Named zones.** Each worker is told which other task number(s) also touch the same file. Stay inside your named zone.
    - **Anchor comments.** Wrap every addition in `<!-- TASK-admin1-N START -->` … `<!-- TASK-admin1-N END -->` blocks.
    - **Sequential handoff.** Pull the most recently merged version of a shared file from `main` as your starting point — never the original untouched file.
    - **One final integration/lint pass** by the last owner of each shared file before marking it done.

13a. **Never edit this document directly, under any circumstance.** Work is delivered as code on your branch plus a **short** `reports/task-admin1-[N].md` (about 80 lines, **no pasted code**) with these sections: Task Number, What Was Required, What I Built, Files Changed, Small Out-of-Scope Fixes Made, Bugs and Follow-ups Sent to TODO.md, Things the Next Person Must Know, Self-Check Confirmation. Every code block still carries its anchor comment and an explanatory comment (rule 6). Anything someone must act on goes into `TODO.md`. A task is not done until your branch and report are pushed.

13b. **Scope Lock — no exceptions, ever, except the verified small-fix rule.** Touch only the files explicitly in your task. Out-of-scope fixes must pass the small-fix test: problem verified real; about 10 lines or fewer; one place; no new file; no clash with another active branch. If it passes: fix it as a separate `TASK-admin1-N-EXTRA` commit and list it in your report. If not: add a `TODO.md` entry and list its number in your report.

13c. **Second verification round.** If asked to re-check your work, push further changes to the *same branch* and add a `## SECOND VERIFICATION ROUND` section to your *existing* `reports/task-admin1-[N].md` — never a new file.

### Testing tools — required, not optional

| Tool | What it's for |
|---|---|
| **Playwright** (Python or Node, Chromium headless) | Load the real page, drive real interaction, read back computed styles/bounding boxes/timing |
| **CDP screenshots** | Bypass `document.fonts.ready` hangs for a raw render |
| **Pixel-level image analysis** (PIL/numpy) | Independently confirm layout when visual inspection isn't reliable |
| **Route stubbing** (`page.route`) | Stub JSON fetches, block unreachable CDN requests |
| **ffmpeg** | Generate synthetic test media when real assets aren't available |
| **Structural lint** | Check balanced tags/braces/parens, no duplicate IDs — run after every edit |

Boundary-case testing is not optional. Test the shortest input, longest input, empty/zero input, and every discrete state a toggle or score can be in.

---

## SHARED CONTRACTS — PUBLISHED DAY ONE

These locked interfaces are what every worker builds against. Unlike the Recommended Enhancements below, these are **not optional** — the parallel-build model only works if every worker uses the same shape. A genuine need to change one is a flagged deviation in your report, not a silent decision.

**`includes/chat-auth.php` — real function signatures (from Task 1's branch on `main`):**
- `issue_token(principal_id): token_string` — opaque SHA-256 token, 4h idle + 24h absolute expiry
- `verify_token(token_string, principal_id): bool`
- `invalidate_all_tokens(principal_id): void` — DELETE-based, immediate
- `log_audit(principal_id, endpoint, result): void`
- `check_pin_throttle(principal_id): {allowed: bool, delay_seconds: int}` — Task 3 builds this; Task 6 exempts its recovery flow from it

**Real table names:**
- `chat_session_token` — columns: `id`, `token_hash`, `principal_id`, `issued_at`, `expires_at`
- `chat_auth_audit_log` — columns: `id`, `principal_id`, `endpoint`, `result`, `created_at`
- `chat_auth` — `principal_id`, `auth_tier` (`none`/`pin`/`password`), `secret_hash`, `salt`, `failed_attempts`, `locked_until`, `created_at`, `updated_at`. Task 3 creates it (PIN fields); Task 5a adds password fields; Task 6 writes `secret_hash` only — no further schema changes.
- `chat_remember_device` — `id`, `principal_id`, `device_fingerprint`, `label`, `last_used_at`, `created_at`

**Audit-log result vocabulary (settled — not a proposal):** Tasks 3, 4, and 5a must log against exactly these result strings so Task 7's badge query works without substring-matching:
- `pin_set` — when a PIN is created or changed
- `password_set` — when a password is created or changed
- `tier_verified` — when a customer correctly passes a PIN or password gate (not every token verify — those are too noisy)
- `locked` — when the lock toggle fires
- `unlocked` — when a customer successfully reopens a locked chat

**`AuthGate` component interface:** props `tier` (`"pin"` | `"password"`), `principalId`, `onVerifySuccess`, `onVerifyFail`. Task 3 builds it; Task 4 and Task 5a import it.

**Options-panel section contract — cross-project shared file:** the accessibility/options panel is also claimed by Admin-Project #2/#3/#4's Task 3 (chat language). Each feature appends its own self-contained, anchor-commented section to the panel — never interleaves with or restructures another feature's section. Our Task 3 merges first; the language task merges second.

**Reuse note for future adopters (e.g. Rehoming Platform):** `principal_id` here means one anonymous chat conversation. An adopting system with persistent accounts should treat it as one account (protecting the full inbox, not per-thread). If the tables are ever shared across systems, add a `principal_type` discriminator column first.

---

## MODEL & SETTINGS

Claude Sonnet 5. Extended thinking on. Web search on throughout. Deliver real completed work each turn; save check-ins for genuine blockers only.

---

## TASK 3 — PIN System

**Branch:** `task/admin1-3`
**Report:** `reports/task-admin1-3.md`
**Complexity:** High | **Files touched:** 2 shared + new

**Read first (from repo `main` before starting):**
`ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, `ROADBLOCKS-AND-FIXES.md`, `TODO.md` (Table of Contents — handle any entries addressed to you or ALL), `PETCENTER_DESIGN_SYSTEM_v1.0.md` (UI/animation sections), `reports/task-admin1-1.md` (Task 1's report — real function signatures, table names, `chat-widget.js` session-token plumbing), `WORKER-GIT-SYNC-PROTOCOL.md`.

**Shared files — merge order:**
- `chat-start.php`: Task 1 owns it first (already merged on `main`). Pull from `main`; add your block after Task 1's `<!-- TASK-admin1-1 END -->` anchor.
- `includes/chat-auth.php`: Task 1 owns it first (already on `main`). Pull from `main`; add your throttle-logic block wrapped in `<!-- TASK-admin1-3 START/END -->`. You are NOT the last owner — Task 6 adds an exemption after you; run your own lint pass but leave the anchor clearly in place for Task 6.
- The existing **accessibility/options panel** file: shared with Task 4 (after you) and Admin-Project #2/#3/#4 Task 3 (after Task 4). You are first. Follow the options-panel section contract above — one self-contained, anchor-commented section appended.

**What to build:**
An optional per-chat PIN gating the reopen of a thread. Recommended at chat creation, not mandatory — if a user declines, **show a warning about the risk before allowing them to continue without one.** PIN setup and all management live inside the existing accessibility/options panel — find that panel and extend it; do not build a new location.

The prechat form is already visually packed. Design your own approach to surfacing the PIN offer without overwhelming the existing layout, and confirm the approach makes sense in the available space before building it.

Build the `AuthGate` component matching the Shared Contract — Task 4 and Task 5a both import it. Build the `chat_auth` table with PIN-relevant fields active per the Shared Contract schema; Task 5a activates password fields later. On repeated failed PIN attempts, use a growing delay — **not a hard lockout** — and ensure this throttle never blocks the underlying email+chatID lookup, only reading content after it. Check the real `check_pin_throttle()` contract above before designing your throttle implementation.

**Build against the Shared Contract now.** Pull the real `chat-start.php` and `includes/chat-auth.php` from `main` only when ready for final integration; wrap additions in `<!-- TASK-admin1-3 START/END -->`; run a final integration/lint pass on `chat-start.php` (you're its last owner among these tasks).

**Bug/follow-up and self-test line:** Follow Step 4 of the Worker protocol (`WORKER-GIT-SYNC-PROTOCOL.md`) for anything you find outside your scope: verify it first; small fix (about 10 lines or fewer, one place, no shared structure, no clash with another active branch) → fix it yourself as a separate `TASK-admin1-3-EXTRA` commit; anything bigger or uncertain → add an entry to `TODO.md`. Test your own work with the installed tools before you push your report.

### Recommended Enhancements for Task 3 (headstart — not final)
- Layer a coarser per-IP rate limit alongside the per-`principal_id` growing delay, since per-account-only throttles miss distributed guessing from one source IP across many chat IDs.

⚠️ **This is a headstart, not a final decision.** You own this code and these files — you have the real source files and know what's actually there; we don't. Think it through against what you find, use web search to confirm best practice, and decide what's right for this codebase. Adopt, modify, or skip the suggestion above and say which in your report.

---

## TASK 4 — Lock Toggle

**Branch:** `task/admin1-4`
**Report:** `reports/task-admin1-4.md`
**Complexity:** Medium | **Files touched:** new only + one shared component import + options panel

**Read first:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, `ROADBLOCKS-AND-FIXES.md`, `TODO.md` (Table of Contents), `PETCENTER_DESIGN_SYSTEM_v1.0.md` (animation/transition sections specifically), `reports/task-admin1-1.md`, `WORKER-GIT-SYNC-PROTOCOL.md`.

**Shared files — merge order:**
- The **accessibility/options panel** file: Task 3 merges first. Pull from `main` once Task 3's branch is merged; append your section after Task 3's anchor. You are NOT the last owner — Admin-Project #2/#3/#4 Task 3 (language feature) adds after you.
- `AuthGate` component: import from Task 3's merged code — do not rebuild it.

**What to build:**
A lock toggle in the open chat widget that locks the conversation on close, requiring the PIN to reopen. Two configurable behaviors in the same options panel section as the PIN settings (follow the options-panel section contract): a delay mode (lock after N minutes idle) and an immediate mode. **The visual transition between locked and unlocked states must use a fade animation — not a slide, not an instant toggle.**

**On lock trigger, call `invalidate_all_tokens(principal_id)` from `includes/chat-auth.php`** — required, not optional; without it an already-open tab stays valid past the lock. Log `locked` using `log_audit()` against the settled vocabulary above. The idle countdown can run client-side for responsiveness, but the server must independently re-check elapsed idle time before honoring any "still open" request. Reuse Task 3's `AuthGate` component for the unlock step; do not build a second gate.

**Do not start until Task 3's branch is merged into `main`** — there is nothing to import before then. Once merged, build against what's on `main`.

**Bug/follow-up and self-test line:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything outside your scope. Test your own work with the installed tools before pushing your report.

### Recommended Enhancements for Task 4 (headstart — not final)
- Consider a brief "locking in N seconds" indicator once the countdown starts, so a customer mid-conversation isn't silently locked out. This rides on the timer you're already building.

⚠️ **Headstart, not a final decision.** Think it through against the existing chat widget's UX conventions. Adopt, modify, or skip the above and say which in your report.

---

## TASK 5a — Password System Core

**Branch:** `task/admin1-5a`
**Report:** `reports/task-admin1-5a.md`
**Complexity:** Very High | **Files touched:** `chat_auth` (alter) + new

**Read first:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` (specifically: existing admin-to-user email automation pattern; `accounts.petcenterco.com/2fa` device-fingerprinting implementation), `ROADBLOCKS-AND-FIXES.md`, `TODO.md` (Table of Contents), `reports/task-admin1-1.md`, `reports/task-admin1-3.md` (once Task 3 is merged — for current `chat_auth` schema before you alter it), `WORKER-GIT-SYNC-PROTOCOL.md`.

**Shared files — merge order:**
- `chat_auth` table: Task 3 creates it (already on `main` once merged). Pull the current schema from Task 3's merged code before altering — do not redesign; add your password fields only. Task 6 writes `secret_hash` after you.
- `chat_remember_device` table: you create it — no predecessor.
- `AuthGate` component: import Task 3's — do not rebuild.

**What to build:**
Password protection for a chat. Password upgrade is the path beyond PIN. **Password policy (settled — not open for reinterpretation):** no mandatory composition rules; 12-character minimum, no upper limit, all characters; check against a common/breached-password blocklist and reject matches; show a live strength meter driven by actual length/entropy plus the blocklist check — not a composition checklist.

**Reuse the existing 2FA device-fingerprinting implementation** for "remember this device" — find it in `accounts.petcenterco.com/2fa` per rule 7; copy it; do not reimplement. **On password set/change, call `invalidate_all_tokens(principal_id)`** — required. Log `password_set` via `log_audit()` against the settled vocabulary.

Core scope for this task: the password setup UI, hashing/storage, login verification using the `AuthGate` shell, and a single remember-device write using the copied fingerprinting code. The device list/management UI is Task 5b's scope — do not build it here.

**Can start now** against the Shared Contract. Pull the real `chat_auth` schema from Task 3's merged code only at integration time.

**Bug/follow-up and self-test line:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md`. Test your own work with the installed tools before pushing your report.

### Recommended Enhancements for Task 5a (headstart — not final)
No additional optional suggestions beyond what's in the core scope — the password policy and fingerprinting-reuse requirement are settled, and the implementation specifics within those are yours to decide against the real code.

⚠️ The core requirements are fixed; how you implement them against the real source is your judgment call. Use web search to confirm current best practice for the blocklist check and strength meter specifically.

---

## TASK 5b — Device Management UI *(enhancement-driven, optional)*

**Branch:** `task/admin1-5b`
**Report:** `reports/task-admin1-5b.md`
**Complexity:** Low | **Files touched:** `chat_remember_device` (read/write) + new UI

**Read first:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, `ROADBLOCKS-AND-FIXES.md`, `TODO.md` (Table of Contents), `reports/task-admin1-5a.md` (once Task 5a is merged — for real table schema), `WORKER-GIT-SYNC-PROTOCOL.md`.

**Shared files — merge order:**
- `chat_remember_device`: Task 5a creates it. Pull from `main` once Task 5a is merged; do not start before then.

**This task exists only if the device-list enhancement is adopted.** Task 5a's core scope already closes the real gap with a single revoke-all toggle. This task builds a per-device list instead — genuinely optional, confirm with the CEO it's still wanted before starting.

### Recommended Enhancement for Task 5b (the entire task is this enhancement)
A settings view listing every remembered device (label, last-used date) with a per-device "forget" action. Forgetting one deletes its row and calls `invalidate_all_tokens()` scoped to that device's session. CRUD UI only — no auth logic belongs here.

⚠️ **Headstart, not a final decision — and whether to build this at all is itself not final.** Confirm with the CEO first. Think through the implementation against the real table structure Task 5a leaves you.

**Bug/follow-up and self-test line:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md`. Test your own work before pushing your report.

---

## TASK 6 — Password Reset Flow

**Branch:** `task/admin1-6`
**Report:** `reports/task-admin1-6.md`
**Complexity:** High | **Files touched:** `includes/chat-auth.php` (3rd owner) + new

**Read first:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` (specifically: the admin-to-user email automation pattern — reuse it, do not build a new email path), `ROADBLOCKS-AND-FIXES.md`, `TODO.md` (Table of Contents), `reports/task-admin1-1.md`, `reports/task-admin1-3.md` (for Task 3's throttle implementation you'll be exempting), `reports/task-admin1-5a.md` (for confirmed `chat_auth` schema), `WORKER-GIT-SYNC-PROTOCOL.md`.

**Shared files — merge order:**
- `includes/chat-auth.php`: Task 1 → Task 3 → **you** (3rd and final owner). Pull from `main` once both Task 1 and Task 3 are merged. Wrap your addition in `<!-- TASK-admin1-6 START/END -->`. You run the final integration/lint pass on this file.
- `chat_auth` table: data writes only (no schema changes). Pull the confirmed schema from Task 5a's merged code.

**What to build:**
Self-service password recovery. User confirms recovery contact on file (never the chat ID). Server sends a temp password via the existing email automation — **the email must never include chat content or account detail beyond the temp password itself.** Temp password: 30–60 min expiry, single-use regardless, forced change on next login.

**Return an identical response in identical time** whether or not the recovery contact matches an account — required, not optional; timing differences alone can confirm account existence, which is the same class of vulnerability this project exists to close.

Build the exemption against `check_pin_throttle()` in `includes/chat-auth.php` — **an active throttle-lockout must never block recovery.** Call `invalidate_all_tokens()` on successful reset.

**Note (not a build task):** generic reset-flow guidance about Referrer-Policy/URL-token leakage applies to link-based resets. This design uses a temp password by email — that category of concern doesn't apply here.

**Can start now** against the contracts. Pull the real `includes/chat-auth.php` from `main` (after Tasks 1 and 3 are merged) and the confirmed `chat_auth` schema (after Task 5a is merged) only at integration time.

**Bug/follow-up and self-test line:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md`. Test your own work with the installed tools before pushing your report.

### Recommended Enhancements for Task 6 (headstart — not final)
- Consider rate-limiting reset requests per recovery contact (e.g. a few per hour), using your own table separate from the PIN/password throttle tables, to reduce email-spam abuse of this endpoint.

⚠️ **Headstart, not a final decision.** Think it through against the real codebase and use web search to confirm current best practice. Adopt, modify, or skip the above and say which in your report.

---

## TASK 7 — Admin Auth-State Badge

**Branch:** `task/admin1-7`
**Report:** `reports/task-admin1-7.md`
**Complexity:** Low | **Files touched:** `chat-admin-list.php` (read from `main` after Task 2 merged)

**Read first:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, `ROADBLOCKS-AND-FIXES.md`, `TODO.md` (Table of Contents — **this task has a prior history of a contract gap; see the VDM sync notice if present**), `reports/task-admin1-1.md`, `reports/task-admin1-2.md`, `WORKER-GIT-SYNC-PROTOCOL.md`.

**Shared files:** `chat-admin-list.php` — Task 2 is the prior owner (merged on `main`). Pull from `main`. No further owners after you.

**What to build:**
A badge on the admin claim/list view showing each chat's current protection state: none / PIN set / password set / currently locked / verified-this-session. Source everything from `chat_auth_audit_log` — read only, no new logging.

**Critical — match against the settled audit vocabulary above, not substring-matching on `endpoint`.** The badge query should match on `result` values (`pin_set`, `password_set`, `locked`, `tier_verified`) against the fixed vocabulary settled in the Shared Contracts section. Do not build logic that relies on `endpoint` name strings to infer tier — Task 1's real endpoint names (`issue_token`, `verify_token`, `invalidate_all_tokens`) don't carry that signal in `endpoint`, they carry it in `result`.

**Do not start until Task 2, Task 3, Task 4, and Task 5a are all merged into `main`** — the badge displays state that doesn't exist until all four are complete.

**Bug/follow-up and self-test line:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md`. Test your own work with the installed tools before pushing your report.

### Recommended Enhancements for Task 7 (headstart — not final)
No additional optional suggestions — this task is scoped precisely enough that the research pass didn't surface further refinements worth separating.

⚠️ The core scope is yours to implement using your own judgment against the real admin list UI's existing conventions. Adopt whatever badge presentation fits the existing admin panel design system best.
