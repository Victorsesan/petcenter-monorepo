# LIVE CHAT SECURITY & SESSION OVERHAUL — TASK ASSIGNMENTS

**Prepared by: Vice Dev Manager**
**Sent to: Workers (Claude AI instances)**

## INTRODUCTION

Hi — you're one of the workers on Pet Center Co's live chat security project. Pet Center is a pet adoption platform; this project closes a real, currently-live access-control gap in its live chat system and adds three new protection layers on top of the fix. This document is your task assignment.

**Structural note before you read your task:** every task section below has two parts — the task itself, and a separate "Recommended Enhancements" subsection underneath it. Read both, every time, for your task. The enhancements are real, researched, and worth taking seriously — but they are a headstart, not a final answer. Read the warning inside that subsection before you decide what to build.

Read the Warning & Instructions section below in full before you touch anything — it applies to every task in this document, including yours, with no exceptions and no drop-off partway through.

---

## TABLE OF CONTENTS
- [Warning & Instructions](#warning--instructions--read-this-first-every-word)
- [Shared Contracts — Published Day One](#shared-contracts--published-day-one)
- [Model & Settings Recommendations](#model--settings-recommendations)
- [Task 1 — Session-Token Base Layer + Endpoint Hardening](#task-1--session-token-base-layer--endpoint-hardening)
- [Task 2 — Admin-Side Auth Enforcement + Bulk-Action Validation](#task-2--admin-side-auth-enforcement--bulk-action-validation)
- [Task 3 — PIN System](#task-3--pin-system)
- [Task 4 — Lock Toggle](#task-4--lock-toggle)
- [Task 5a — Password System Core](#task-5a--password-system-core)
- [Task 5b — Device Management UI (enhancement-driven, optional)](#task-5b--device-management-ui-enhancement-driven-optional)
- [Task 6 — Password Reset Flow](#task-6--password-reset-flow)
- [Task 7 — Admin Auth-State Badge](#task-7--admin-auth-state-badge)

---

## WARNING & INSTRUCTIONS — READ THIS FIRST, EVERY WORD

### Core rules (apply to every task, every file, every page)

1. **Self-question before and after every task.** Before running any command, ask yourself whether it's actually correct. After running it, check the result — don't assume it worked because it didn't error. Even after moving past a step, look back at it again before finishing.

2. **Never use emojis anywhere in the page or the code.** Use icons only (inline SVG, matching the existing icon style already in use on the page you're editing).

3. **Never create a `.md` or `.txt` file unless explicitly told to** — with one exception: task/project completion files (see Directive 8 below) are required, not optional.

4. **Carry these instructions through every command you run.** Don't let them apply only to the first task and quietly drop off later.

5. **Every page is deployed as a folder path, never a bare filename in the URL.** On the server, each page is uploaded as its own folder containing an `index.html` — e.g. the source file `pet-detail.html` is uploaded as `pets/detail/index.html`, producing the clean URL `.../pets/detail/`. Every internal link and every URL-building line of code must use the clean folder path, never the `.html` filename. **The only exception is admin-side files that aren't HTML files.** **Note: this rule is about deployed behavior only — when referring to or requesting a file, always use its real source filename**, never the deployed `index.html` name.

6. **Write code comments on everything you touch.** Every function, block, or non-obvious line gets a comment — so another worker can understand it during debugging without re-reading the whole file.

7. **Never recreate a feature from scratch.** If a reusable feature already exists, go to the exact file(s) where it's documented and copy the exact same code for consistency — don't reimplement your own version.

### Dev Manager Directives (apply project-wide, carry equal weight to the rules above)

8a. **Never edit this document directly, under any circumstance.** Instead, when a task is complete, create a **new, separate completion file** named after your task number (e.g. `Task-3-Completion.md`) using this exact format:

    - **Task Number** — the exact task number you were assigned, quoted in full
    - **What Was Required** — a short restatement of the task's scope, in your own words
    - **Files Changed** — a table listing every file you touched and whether it's shared with another task number
    - **Code Changes** — for every file you touched, in full: if you replaced existing code, show the exact old snippet being replaced *and* the exact new final code; if you added new code, show the final code. Every code block must be wrapped in a task-numbered anchor comment (`<!-- TASK-X START -->` ... `<!-- TASK-X END -->`) and must carry a comment explaining what it does — this applies even to trivial one-line changes. This section must be complete enough that someone who has never seen your work could locate and apply every change from your documentation alone. Copy the code exactly as it exists in the real file, including every comment you wrote — do not strip, shorten, or paraphrase.
        - **New files are the one exception to full code documentation** — list under Files Changed and write "New file — no existing code to merge, submit the file directly."
        - **If a completion file you're reviewing claims a "new file" that was never supplied to you, do not create your own version of it.** Stop and flag exactly which file is missing.
    - **Contract Deviations** — if your actual implementation differs at all from the Shared Contract published for your task, list exactly what changed and why. If none, write "None — matches published contract exactly."
    - **Enhancement Decisions** — for each Recommended Enhancement listed under your task, state whether you adopted it, modified it, or skipped it, and why. This is required even if your answer is "adopted all as written."
    - **Bugs Found Outside Task Scope** — see Scope Lock below. If none, write "None found."
    - **Self-Check Confirmation** — a short statement confirming you only modified the files listed above, and that every code block you added carries a task-numbered anchor comment.

8b. **Scope Lock — no exceptions, ever.** You touch only the files explicitly involved in your task. If something outside your scope is blocking you, raise it as a question before proceeding rather than fixing it yourself.

8c. **Second verification round.** If asked to re-check completed work, add a new **`## SECOND VERIFICATION ROUND`** section to your *existing* completion file — never a new file. Scope Lock still applies in full.

9. **Consult `PETCENTER_DESIGN_SYSTEM_v1.0.md` before making any UI, styling, or animation decision.** Use its own Table of Contents to jump to the relevant section.

10. **Image/reference-material gate — hard stop if it's missing.** If a task says a screenshot or reference is required and it isn't present, stop and wait — don't guess.

10a. **Design-fidelity lock.** Treat reference material as the literal, exact target. Before calling any design-related task done, use the Playwright headless browser tool to load your finished page and visually compare it against the reference — mandatory, not optional. Keep checking throughout the task, not just once.

11. **Use web search during implementation.** Tool choices, current library versions, current best practices — search before committing to an approach. This applies doubly to the Recommended Enhancements sections below — they're a starting point for your own research, not a substitute for it.

12. **Multi-worker, same-file protocol — Sequential File Ownership Chain with Anchor Comments.** Where more than one worker touches the same file: your zone is named explicitly below. Wrap every addition in a unique task-numbered comment block (`<!-- TASK-X START -->` ... `<!-- TASK-X END -->`). **If you are not the first worker assigned to a shared file, you must pull the most recently submitted version of that file — never the original, untouched file — as your starting point.** Before marking a shared file done, run one final integration/lint pass over the whole file, not just your own added block.

12a. **Build-against-contract, integrate-against-reality.** Every shared function, table, and component below has a locked interface published in Shared Contracts. You may start building immediately against that published contract — you do not need to wait for the upstream task to finish to begin work. What you do need the upstream task's completion file for is the *final* step only: swapping your contract-stub calls for the real implementation and confirming nothing drifted. If your actual build needs the contract to be different from what's published, that's a flagged deviation (see the completion-file format above) — not a silent change.

13. **Use `cats.html` as the canonical blueprint** for header, footer, and hamburger menu on any pet-related page your task touches.

14. **Read the relevant project summary document in full before starting** — not just the section that looks related.

15. **Files for your task will be provided by the CEO in a chat message.** The moment you receive them, copy and save every one of them to the outputs directory before doing anything else.

---

## SHARED CONTRACTS — PUBLISHED DAY ONE

These are locked interfaces every worker can build against immediately, in parallel, without waiting on each other. Unlike the Recommended Enhancements in each task below, these are **not optional or up for reinterpretation** — the parallel-build model only works if every worker is building against the same fixed shape. A genuine need to change one is a flagged Contract Deviation in your completion file, not a private judgment call.

**`includes/chat-auth.php` — function signatures:**
- `issue_token(principal_id): token_string` — issues a new opaque session token, returns the raw token for client storage
- `verify_token(token_string, principal_id): bool` — validates a token exists, is unexpired, and matches the principal
- `invalidate_all_tokens(principal_id): void` — revokes every outstanding token for a principal
- `log_audit(principal_id, endpoint, result): void` — writes one audit row per verification attempt
- `check_pin_throttle(principal_id): {allowed: bool, delay_seconds: int}` — added by Task 3, contract published now so Task 6 can build its exemption against it in parallel rather than waiting

**`chat_session_token` table:** `token_hash`, `principal_id`, `issued_at`, `expires_at`

**`chat_auth` table (full final shape, agreed by all three owners now):** `principal_id`, `auth_tier` (`none` / `pin` / `password`), `secret_hash`, `salt`, `failed_attempts`, `locked_until`, `created_at`, `updated_at`. Task 3 creates it with the PIN-relevant fields active; Task 5a activates the password fields; Task 6 only ever writes to `secret_hash` — no further schema changes past this point, by anyone.

**`chat_remember_device` table:** `id`, `principal_id`, `device_fingerprint`, `label`, `last_used_at`, `created_at`

**`AuthGate` component interface:** props `tier` (`"pin"` | `"password"`), `principalId`, `onVerifySuccess`, `onVerifyFail`. Task 3 builds the real component; Task 4 and Task 5a build against this prop contract in parallel and swap in the real component once Task 3's completion file exists.

**Options-panel section contract — cross-project shared file, flagged for coordination:** the existing accessibility/options panel is also claimed by Admin-Project #2/#3/#4's Task 3 (chat language preference), per that tracker's own text — it was written assuming PIN/lock already exist there, which isn't true yet; this file now has three real claimants (our Task 3, our Task 4, their Task 3) across two trackers, and needs a single order, not two trackers each guessing. Structural contract every owner builds against: each feature adds its own self-contained section (own heading, own anchor-commented block, own settings row) appended to the panel, never interleaved with or restructuring another feature's section. Under this contract, the language-preference task does not need our real file to exist first — it can build its section in parallel against this same convention and reconcile at integration, exactly like every other shared file in this project. Our Task 3 goes first in the actual merge order for this file, since Task 4 depends on it existing; flag the cross-tracker chain explicitly in both projects' completion files so it isn't lost between VDMs.

**Reuse notes for future adopters of this system (e.g. a merchant/visitor messaging platform elsewhere on Pet Center):**
- `principal_id` in this project always means "one anonymous chat conversation." An adopting system with persistent accounts should treat `principal_id` as "one account," so PIN/password/lock protect that account's entire message inbox as a unit — not one lock per conversation thread. Nothing in this schema forces per-thread scoping; that's a design choice for the adopting system to make deliberately, not default into.
- The ten hardened endpoints in Task 1 are specific to this project's own chat files. Reuse means applying the same verification pattern (issue token → verify on every read/write endpoint → invalidate on lock/password-change) to whatever endpoints the adopting system actually has — not literally importing these files.
- If `chat_auth` / `chat_session_token` become genuinely shared tables across two systems rather than each system standing up its own copy, add a `principal_type` discriminator column before that happens — otherwise an anonymous-chat ID and an unrelated account ID could collide in the same ID space.

---

## MODEL & SETTINGS RECOMMENDATIONS
Claude Sonnet 5, free tier. Extended thinking on for this task. Web search on throughout. Deliver real completed work each turn rather than routine check-ins — save check-ins for genuine blockers (a missing reference image, a real scope ambiguity).

---

## TASK 1 — Session-Token Base Layer + Endpoint Hardening
**Complexity: High | Files touched: 12**
**Read First:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, in full.
**Files to Request From CEO before starting:** `chat-start.php`, `chat-poll.php`, `chat-typing.php`, `chat-draft.php`, `chat-history.php`, `chat-message-edit.php`, `chat-message-delete.php`, `chat-send.php`, `chat-rate.php`, `chat-upload-temp.php`, `chat-upload-video-chunk.php` (real source filenames — request these exact names, not any deployed path).

Reopening a chat currently works on email + chat ID alone — neither is treated as a secret, and both are guessable/enumerable, so either one leaking is enough to expose a full conversation. Your job is to make email + chat ID a *lookup* mechanism only, never an *authorization* mechanism, by implementing the session-token contract above for real and enforcing it in every one of the files listed.

Build the real `chat_session_token` table and the real `includes/chat-auth.php`, matching the Shared Contract exactly (or documenting a flagged deviation if reality forces a change). Issue a token in `chat-start.php` once the existing lookup succeeds. **Both `chat-start.php` and `includes/chat-auth.php` are shared files — wrap every addition in either one in `<!-- TASK-1 START -->` / `<!-- TASK-1 END -->`.** `chat-start.php` is shared with Task 3. `includes/chat-auth.php` is shared with Task 3 (adds PIN throttle logic) and Task 6 (adds a lockout exemption) after you.

**Build `invalidate_all_tokens()` and a per-verification audit log as required, non-optional parts of this task** — every downstream lock/password/reset action depends on the revocation function existing, and the audit log is what makes Task 7's admin visibility possible at all. These are structural, not enhancements — the specific *implementation* of them (token format, hashing, expiry policy) is what's covered below.

You're on the critical path for final integration, not for everyone else's start — Tasks 2 through 7 are building against the published contract in parallel while you work. Get your completion file out as soon as the real implementation is solid; that's what everyone else needs to reconcile against.

### Recommended Enhancements for Task 1 (headstart — not final)
- Use an opaque, cryptographically random token stored server-side (not a JWT) — a DB-backed token gets revocation for free instead of needing a second subsystem.
- Store the token as a hash, using the same hash/verify helper Task 5a will use for passwords, rather than two separate implementations.
- Add an absolute expiry (e.g. 24h) on top of any idle expiry, so a token that's never explicitly revoked still has a hard ceiling.

⚠️ **These are a headstart, not a final decision.** You own this code and this file — think it through against what you actually find in the real source, and use web search to confirm current best practice before committing to an approach. Adopt, modify, or skip each of the above, and say which in your completion file.

---

## TASK 2 — Admin-Side Auth Enforcement + Bulk-Action Validation
**Complexity: Medium | Files touched: 6**
**Read First:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, in full.
**Files to Request From CEO before starting:** `chat-admin-claim.php`, `chat-admin-reply.php`, `chat-admin-done.php`, `chat-admin-poll.php`, `chat-admin-list.php`, `chat-manage.php`.

Confirm each of the files above actually verifies an authenticated admin session before acting, not just the presence of admin-shaped fields — fix any gap you find. Build your own rate-limit table for admin actions if you decide one is warranted (see below) — do not touch the `chat_auth` table, it belongs to other tasks.

None of your files are shared with another task — no anchor comments needed here. No contract dependency either — start immediately.

### Recommended Enhancements for Task 2 (headstart — not final)
- For `chat-manage.php`'s bulk archive/delete: consider validating the entire ID batch against the admin's authority in one query and rejecting the whole batch on any mismatch, rather than applying to whichever IDs pass. This is a design choice, not a hard requirement — a per-ID partial-success response with clear per-ID status is also defensible; pick whichever fits the existing admin UI's error-handling conventions better.
- Consider rate-limiting `chat-admin-claim.php` claim attempts per admin account, to leave a trail if a compromised admin credential is used to rapidly claim many chats.

⚠️ **These are a headstart, not a final decision.** You own this code and this file — think it through against what you actually find in the real source, and use web search to confirm current best practice before committing to an approach. Adopt, modify, or skip each of the above, and say which in your completion file.

---

## TASK 3 — PIN System
**Complexity: High | Files touched: 2 shared + new**
**Read First:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, in full. Also consult `PETCENTER_DESIGN_SYSTEM_v1.0.md` before any UI decision below.
**Files to Request From CEO before starting:** none required to *start* — build against the published `chat-auth.php` contract and the `chat_auth` table shape above. Request the real `chat-start.php` and `includes/chat-auth.php` (Task 1's submitted versions of both) only when you're ready for final integration.

Build an optional per-chat PIN, entered from the prechat form immediately after email and chat ID, gating reopening of that thread. This is recommended, not mandatory: **if a user declines to set one, they must be shown a warning about the risk of leaving it off before being allowed to proceed without it.** PIN setup and management live inside the existing accessibility/options panel already documented for the chat widget — this is a reuse case, not a new UI location; find that panel and add to it, following the options-panel section contract above.

The prechat form is already visually packed. Design your own UX for how the "you can set up a PIN" notice is surfaced without overwhelming the existing layout, and how the setup section is presented and dismissed back to the prechat form once saved. Propose your approach and check it against the space constraint before building — don't take the first idea you have.

Build the real `AuthGate` component matching the Shared Contract — Tasks 4 and 5a are building against that same contract in parallel and will import your real component once your completion file exists. Create the real `chat_auth` table per the Shared Contract's full shape (PIN fields active now, password fields Task 5a will activate later — don't build those yourself). The PIN itself is numeric with a fixed length, since that's what a PIN is by definition — no composition rules to design here beyond correctness.

**On repeated failed attempts, do not implement a hard lockout.** A hard lockout on a PIN gate lets anyone who knows or guesses a chat ID lock out the legitimate customer — this is a real vulnerability in the architecture, not a stylistic choice, and a growing delay (or equivalent throttle that never fully blocks access) is required instead. This throttle must never block the underlying email+chatID lookup itself, only reading content after lookup.

**You can start now**, building `issue_token`/`verify_token`/`invalidate_all_tokens` calls against the published contract as stubs. Before your completion file: pull Task 1's real `chat-start.php` and `chat-auth.php`, wrap your additions in `<!-- TASK-3 START -->` / `<!-- TASK-3 END -->` in each, and run a final integration pass on `chat-start.php` (you're its last owner). You are *not* the last owner of `chat-auth.php` — Task 6 adds to it after you and runs that file's final pass, so leave your throttle-logic block clearly anchored for them to build on, not merge yourself.

### Recommended Enhancements for Task 3 (headstart — not final)
- Consider layering a coarser per-IP rate limit alongside the per-`principal_id` throttle, since per-account-only limits can miss distributed guessing across many chat IDs from one source.

⚠️ **This is a headstart, not a final decision.** You own this code and this file — think it through against what you actually find in the real source, and use web search to confirm current best practice before committing to an approach. Adopt, modify, or skip the above, and say which in your completion file.

---

## TASK 4 — Lock Toggle
**Complexity: Medium | Files touched: new only, plus one shared component import**
**Read First:** consult `PETCENTER_DESIGN_SYSTEM_v1.0.md` for the required transition treatment before building anything.
**Files to Request From CEO before starting:** none — build against the `AuthGate` and `invalidate_all_tokens` contracts now; swap in Task 3's real component once their completion file exists.

While a chat is actively open, a user can toggle a setting that locks the conversation once the widget is closed; reopening after that requires the PIN. This needs two configurable behaviors living in the same options panel as the PIN setup, following the options-panel section contract above: a delay setting (how long after close before lock engages) and a separate immediate/aggressive option with no delay. **The visual transition between locked and unlocked states must use a fade animation specifically — not a slide, not an instant toggle.**

**On lock trigger, you must call `invalidate_all_tokens()`.** This isn't optional — without it, an already-open second tab stays valid past the lock, which defeats the point of locking. The idle countdown itself can run client-side for responsiveness, but the server must independently re-check elapsed idle time before honoring any unlock/still-open request — never trust a client-reported idle state alone; that part is also required, not a suggestion, since a client-only check is trivially bypassed.

**Start now** against the contract. Swap in Task 3's real `AuthGate` for the unlock step once their completion file exists — this is a component swap, not a rebuild, so it should be quick.

### Recommended Enhancements for Task 4 (headstart — not final)
- Consider showing a brief "locking in N seconds" indicator once the countdown starts, so a customer mid-conversation isn't silently locked out — a UX nicety riding on the timer you're already building, not a security requirement.

⚠️ **This is a headstart, not a final decision.** You own this code and this file — think it through against what fits the existing chat widget's UX conventions, and use web search or the design system as needed. Adopt, modify, or skip the above, and say which in your completion file.

---

## TASK 5a — Password System Core
**Complexity: High | Files touched: 1 shared (alter) + new**
**Read First:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, in full — specifically the existing admin-to-user email automation pattern and the `accounts.petcenterco.com/2fa` device-fingerprinting implementation.
**Files to Request From CEO before starting:** the existing 2FA device-fingerprinting source file (needed immediately). The real `chat_auth` table (Task 3's submitted version) only at integration time — build against the published schema until then.

**Password policy (resolved by CEO decision, not open for reinterpretation):** no mandatory composition rules (no forced uppercase/number/symbol) — these are known to push users toward predictable patterns without adding real entropy. Instead: 12-character minimum, no upper limit, all characters allowed; check the chosen password against a common/breached-password blocklist at signup and reject a match; show a live strength meter as the user types, driven by actual length/entropy plus the blocklist check — not a composition checklist. This satisfies the original requirement for an enforced, live-measured strength standard using the mechanism that's actually effective, and it's a settled decision, not a recommendation you're free to override.

Every chat needs the option of a password, saved against the user's chat ID if they've chatted before, or against their registered email/phone if they're new. **Reuse the existing 2FA device-fingerprinting implementation for "remember this device" rather than writing new fingerprinting logic — this is required by the standing project rule against rebuilding an existing feature, not optional.** Build against the `AuthGate` contract for the login-verification UI, with the password tier's secret check. **On password set/change, you must call `invalidate_all_tokens()`** — same requirement as Task 4's lock trigger, for the same reason.

**Core remember-device scope for this task:** a single "remember this device" toggle with one revoke-all-remembered-devices control is sufficient to close the real gap (a customer needs *some* way to undo remembering a device, e.g. after using a shared computer). Whether that becomes a fuller per-device list is Task 5b's call — see that task for why it's separate and optional.

**Start now** against the `chat_auth` and `AuthGate` contracts, and the real `chat_remember_device` table (which you create — no dependency there). At integration time, pull Task 3's real `chat_auth` table state and activate your password fields on it per the published shape — don't redesign the schema at that point, just populate your columns.

### Recommended Enhancements for Task 5a (headstart — not final)
- None beyond what's already folded into the core task above — the password-policy and fingerprinting-reuse points are settled/required, not optional suggestions, so there's nothing further to list here as "take it or leave it."

⚠️ Even with nothing further listed here, the core requirements above are still yours to implement using your own judgment on the real code — the policy and reuse requirement are fixed, the exact implementation is not.

---

## TASK 5b — Device Management UI (enhancement-driven, optional)
**Complexity: Low | Files touched: 1 shared (read/write) + new UI**
**Read First:** none beyond this brief.
**Files to Request From CEO before starting:** none to start — build against the published `chat_remember_device` schema. Request Task 5a's real submitted table only at integration time.

**This task exists only if the device-list enhancement below is adopted.** Task 5a's core scope already closes the real security gap with a single revoke-all toggle. This task is what you'd build instead if a fuller per-device list is wanted — it's genuinely optional, not a required part of the project, and skipping it entirely is a valid outcome. If assigned, it does not block anything else and nothing else blocks it except Task 5a's schema.

### Recommended Enhancement for Task 5b (headstart — not final, and this entire task is that enhancement)
- A settings view listing every device remembered for a given user (label, last-used date) with a per-device "forget" action, instead of one global toggle — useful if a customer is expected to routinely use more than one device with a password set. Forgetting a device would delete its row and call `invalidate_all_tokens()` scoped to that device's active session.

⚠️ **This is a headstart, not a final decision — and unlike other tasks, whether to build this at all is itself part of what's not final.** If you're assigned this task, confirm with the CEO it's still wanted before investing time, since Task 5a alone may already be considered sufficient.

---

## TASK 6 — Password Reset Flow
**Complexity: High | Files touched: 1 shared (data + logic) + new**
**Read First:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, in full — specifically the admin-to-user email automation pattern. Reuse it; do not build a new email-sending path.
**Files to Request From CEO before starting:** none to start — build against the `chat-auth.php` and `chat_auth` contracts. Request the real `includes/chat-auth.php` (Task 3's submitted version) and the confirmed `chat_auth` schema (from Task 5a's completion file) only at integration time.

Build a reset path: user confirms the recovery contact on file (never the chat ID), and receives a temporary password by email, using the existing admin email automation pattern. **The reset email must never include chat content or account detail beyond the temp password itself.** The temp password expires in 30–60 minutes and is single-use regardless of whether the window elapsed; force a password change on next successful login with it.

**You must return an identical response, in identical time, whether or not the recovery contact matches an account.** This isn't optional hardening — it's the same class of vulnerability (identity confirmable via response behavior) this whole project exists to close, just on a different endpoint. Pad the response time to a fixed minimum if the lookup is faster than that minimum.

Build your exemption against the published `check_pin_throttle()` signature now — **an active PIN/password lockout must never block this recovery flow; this is required, not a suggestion**, since a self-service recovery path that can itself be locked out defeats its own purpose. Apply it as a real edit to `includes/chat-auth.php` only at integration time: pull Task 3's real submitted file, wrap your addition in `<!-- TASK-6 START -->` / `<!-- TASK-6 END -->`, and run the final integration pass on that file (you're its last owner). Call `invalidate_all_tokens()` on successful reset.

**Note, not a build task:** most generic reset-flow security guidance (Referrer-Policy headers, token-in-URL leakage) is written for link-based resets. This design uses a temp-password-by-email, not a reset link, so that category of concern doesn't apply here — don't spend research time on it.

**Start now** against the contracts. The only things you genuinely wait on are the two real files/schema at integration time, listed above.

### Recommended Enhancements for Task 6 (headstart — not final)
- Consider rate-limiting reset requests per recovery contact (e.g. a few per hour), separate from the PIN/password throttle, to reduce email-spam abuse of the endpoint rather than credential-guessing abuse.

⚠️ **This is a headstart, not a final decision.** You own this code and this file — think it through against what you actually find in the real source, and use web search to confirm current best practice before committing to an approach. Adopt, modify, or skip the above, and say which in your completion file.

---

## TASK 7 — Admin Auth-State Badge
**Complexity: Low | Files touched: 1**
**Read First:** none beyond this brief.
**Files to Request From CEO before starting:** none to start. Request `chat-admin-list.php` (current state, after Task 2's changes) only when you're ready to wire the badge in.

Add a badge to the admin claim/list view showing a chat's current protection state — none / PIN set / password set / currently locked / verified-this-session — sourced entirely from the `log_audit()` contract's output format. Read-only display work; no new logging. This task exists because support agents currently have no way to know a chat is protected before engaging with it — that's a real gap, not a nice-to-have, which is why this task is required rather than folded into the enhancement list.

**Start now** building the badge component and its state logic against the published audit-log shape. The only real wait is wiring it into the actual `chat-admin-list.php`, which needs Task 2's edits already merged in — request that file once you're ready for that step, not before.

### Recommended Enhancements for Task 7 (headstart — not final)
- None beyond the core scope above — this task is small enough that the research pass didn't surface additional refinements worth listing separately.

⚠️ The core scope above is still yours to implement using your own judgment on the real admin UI's existing conventions.
