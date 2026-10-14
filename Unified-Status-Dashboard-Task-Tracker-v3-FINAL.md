# PET CENTER — UNIFIED STATUS DASHBOARD — TASK TRACKER (v3, FINAL)

**Prepared by: Claude**
**Sent to: assigned workers (Claude AI instances) on this project**
**All enhancements from v2 are approved and folded in here as real scope, not proposals. Task 12 was added after this version was first delivered, once the CEO confirmed adoption-flow email notifications should be built too. v1 and v2 are kept unchanged as historical record — this is a new, standalone version.**

---

## INTRODUCTION

I'm Claude, running this project for **Pet Center**, an animal rescue and adoption organization operating a production adoption platform at `adopt.petcenterco.com` with a companion admin dashboard — plain HTML/CSS/JS/PHP, no framework, flat-file JSON storage.

This project builds **`status.petcenterco.com`**, a new standalone subdomain where visitors check adoption and BNPL application status, and where the CEO manages uptime status for Pet Center's domains — plus, as of this version, the security hardening and notification infrastructure the enhancement research surfaced.

**Read every task in two parts, not one.** Each task below has its required scope (Complexity/What/Where/Files/Read first) *and*, right after it, a "Recommended Enhancements" section with its own warning. The required scope is exactly that — required. The Recommended Enhancements section is a headstart, not a second set of requirements — read the warning inside it before you decide what to do with it.

---

## SPLITTING RATIONALE — WHY THIS IS NOW 11 TASKS, NOT 7

Going through each approved enhancement against the 3-day test, one at a time, rather than assuming the original 7-task shape still holds:

**Tasks 1, 2, 5, 7 stay as single assignments.** Their approved enhancements (accessibility/contrast/CDN-lightness baked into Task 1; rate-limiting/timeline/error-states on Tasks 2 and 5; the seeded candidate list and evaluation framework on Task 7) are all small, natural extensions of work the task was already going to do — none of them add a genuinely separate deliverable, so splitting them further would be exactly the busywork-sized fragmentation the CEO's rule warns against.

**Task 2 is not otherwise growing, but one of its findings is spinning out into a brand new Task 3.** The proxy-side fix (require a second identifier before returning a match) is small and stays in what's now Task 2. But the deeper finding — `application.json` on the *live, already-deployed* `adopt.petcenterco.com` checker exposes full name/phone/address with no rate limiting on a ~1-billion-combination code — is a fix to existing production files this project was never scoped to touch. Different file scope, different domain boundary, real standalone work (add rate limiting, add a second-factor check, likely restructure how the JSON gets served) — this earns its own task (new Task 3), not a bullet point inside Task 2.

**Task 3 (backend hardening, now Task 4) stays one assignment.** Strict ID validation, an audit trail, and atomic writes are all part of building a status-persistence layer correctly — genuinely one deliverable, not three.

**Task 4 (admin panel, now Task 5) splits in two — and this split is about reuse, not just size.** The panel itself (list/filter/detail/approve-reject, reusing the existing document-preview modal, bulk actions, notes field) is one coherent deliverable. Email-on-status-change is a second, separable piece — and it needs to be, because the uptime dashboard's new subscriber-notification feature (below) needs the exact same email-sending capability. Building it once, as its own small standalone task (new Task 6), and having both the BNPL panel and the uptime dashboard call into it, avoids two different workers independently building two different email integrations — the same "don't recreate a feature from scratch" principle the CEO's standing rules already apply everywhere else, applied forward instead of after the fact.

**Task 6 (uptime dashboard) splits into three.** The core (manual up/down/degraded control + public display + an incident log, since a log is nearly free once you're already writing status-change records) is one deliverable. Three more things came out of research — a maintenance-announcement banner, an embeddable badge, and hooking the new email infrastructure up to notify subscribers when status changes — are each individually small. Bundled together, they're the same shape as the CEO's own example of correct bundling (several small fixes, one worker), so they become a second task rather than three trivial ones. Automated monitoring (wiring in a real uptime-checking API, reconciling its results against manual overrides) is genuinely comparable in size to the dashboard's core build — that's the clearest case in the whole enhancement list for its own, third task.

**One item is deliberately not assigned yet.** Whether BNPL-style status-change emails should also be retrofitted onto the *existing* adoption approval flow (a different file, `update-adoption.php`, outside every task's current scope) was an open question in the research, not something "Yes" resolved on its own. It's listed at the end as a possible future task, not handed to a worker.

---

## ⚠️ WARNING & INSTRUCTIONS — READ THIS FIRST, EVERY WORD

This is copied word-for-word from the Warning & Instructions section given to me. It applies to you on every sub-task below, no exceptions.

### Core rules (apply to every task, every file, every page)

1. **Self-question before and after every task.** Before running any command, ask yourself whether it's actually correct. After running it, check the result — don't assume it worked because it didn't error. Even after moving past a step, look back at it again before finishing.

2. **Never use emojis anywhere in the page or the code.** Use icons only (inline SVG, matching the existing icon style already in use on the page you're editing).

3. **Never create a `.md` or `.txt` file unless explicitly told to** — with one exception: the short report every task requires (see Directive 8 below; `reports/task-status-N.md` is the only `.md` file a worker adds).

4. **Carry these instructions through every command you run.** Don't let them apply only to the first task and quietly drop off later.

5. **Every page is deployed as a folder path, never a bare filename in the URL.** On the server, each page is uploaded as its own folder containing an `index.html` — e.g. the source file `pet-detail.html` is uploaded as `pets/detail/index.html`, producing the clean URL `.../pets/detail/`. Every internal link and every URL-building line of code must use the clean folder path, never the `.html` filename. **The only exception is admin-side files that aren't HTML files.** **Note: this rule is about deployed behavior only — when referring to or requesting a file, always use its real source filename**, never the deployed `index.html` name.

6. **Write code comments on everything you touch.** Every function, block, or non-obvious line gets a comment.

7. **Never recreate a feature from scratch.** If a reusable feature already exists, go to the exact file(s) where it's documented and copy the exact same code for consistency — don't reimplement your own version. **This now explicitly includes the email-sending helper built in Task 6** — if your task needs to send an email after Task 6 exists, call that helper; do not write a second one.

### Dev Manager Directives (apply project-wide, carry equal weight to the rules above)

8a. **Never edit the project summary documents directly, under any circumstance.** They're read-only reference material. **Work is no longer delivered as a completion file with pasted code.** Everyone shares one real repository now — your code and comments live on your own git branch, and you deliver a **short** report file instead (see "Branch, Report, and Git Workflow" below for the exact mechanics; full detail is in `WORKER-GIT-SYNC-PROTOCOL.md` at the repo root, which this tracker does not re-explain). Every code block still carries its project-scoped anchor comment (e.g. `<!-- TASK-status-4 START -->` ... `<!-- TASK-status-4 END -->` — matching your branch id, not a bare number) and an explanatory comment — no exceptions, including trivial one-line changes — since there's no longer a completion document to carry that explanation separately.

8b. **Scope Lock — no exceptions, ever, except the verified small-fix rule.** Touch only the files your task names. If something outside your scope seems to need fixing, you may only fix it yourself if it passes the small-fix test in Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` (roughly: ~10 changed lines or fewer, one place, no new file, no shared structure, no other active branch touching the same lines) — and then only as its own separate `TASK-status-N-EXTRA` commit (your task's scoped id, not a bare number), listed in your report. Anything bigger or uncertain goes into `TODO.md` instead, not fixed directly. If something outside your scope is genuinely blocking your own task, raise it in `TODO.md` addressed to this project rather than working around it.

8c. **Second verification round.** Push further changes to the *same branch* and add a short `## SECOND VERIFICATION ROUND` section to your *existing* report file — never a new file, never pasted code. Scope Lock still applies in full.

9. **Consult `PETCENTER_DESIGN_SYSTEM_v1.0.md` before any UI, styling, or animation decision.** On adoption-related public pages, header/footer/hamburger/core palette stay exactly as documented in `adoption-website-project-summary.md`.

10. **Image/reference-material gate.** If a screenshot or reference is required and missing, stop and wait — don't guess.

10a. **Design-fidelity lock.** Treat reference material as the literal exact target. Use Playwright headless browser to visually compare your finished page against the reference before calling any design task done — mandatory. Save the reference to outputs and keep re-checking throughout.

11. **Use web search during implementation — don't rely only on training knowledge.**

12. **Multi-worker, same-file protocol — Sequential File Ownership Chain with Anchor Comments.** Name each worker's zone explicitly, wrap every addition in unique task-numbered anchor comments, pull the most recently submitted version of a shared file (never the original) as your starting point, and run one final integration/lint pass before marking a shared file done.

13. **Use `cats.html` as the canonical blueprint for header, footer, and hamburger menu** on pet-related pages. (`status.petcenterco.com`'s own header/footer is its own design — see Task 1.)

14. **Read the relevant project summary document in full before starting.** Website work → `adoption-website-project-summary.md` in full. Admin dashboard work → `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` in full. Both, where work spans both.

15. **Files for a task come from the CEO in a chat message.** Copy and save every one to the outputs directory the moment you receive them, before doing anything else.

### Testing tools — required, not optional

Real, rendered, measured verification is mandatory before calling anything "done" — reasoning about CSS/JS in the abstract is not sufficient and has caused real regressions on this project. Use:

| Tool | What it's for |
|---|---|
| **Playwright** (Python or Node, Chromium headless) | Load the real page, drive real interaction (click, hover, touch, scroll), and read back real computed styles / bounding boxes / timing. This is the primary way to confirm a fix actually works, not just that the code looks right. |
| **CDP screenshots** (`Page.captureScreenshot` via a raw CDP session) | Standard `page.screenshot()` waits on `document.fonts.ready`, which can hang indefinitely if a font/CDN request is blocked or unavailable in the working environment. A raw CDP screenshot bypasses that wait when you need a render and the normal API won't cooperate. |
| **Pixel-level image analysis** (e.g. PIL/numpy) | Cropping a render and scanning pixel rows/colors to independently confirm layout (text line positions, colors actually applied) when visual inspection alone isn't reliable or available. |
| **Route stubbing / interception** (`page.route`) | Stubbing JSON fetches and blocking unreachable third-party requests (fonts, CDNs) so a page under test can reach a usable state instead of hanging on a network call that will never resolve in a sandboxed environment. |
| **ffmpeg** | Generating small synthetic test media when real sample assets aren't available, to verify logic that depends on real file dimensions/metadata. |
| **Temporary exposed test copies** | Closure-scoped functions aren't reachable from outside their scope. Make a throwaway copy of the file with one line added to expose the function on `window` for direct testing, verify against it, then delete the copy — never ship it. |
| **Structural lint** (balanced tags, balanced braces/parens, no duplicate `id`s) | A fast, cheap check to run after every edit, before ever opening the file in a browser. |

**Boundary-case testing is not optional.** "It works in my one test case" is insufficient for anything with more than one input shape — test the shortest input, the longest input, an empty/zero input, and every discrete state a toggle or score can be in, individually.

**If a sandbox limitation blocks real verification** (no network access, a missing codec, a function that isn't reachable), say so plainly rather than presenting an untested claim as verified. Prefer a narrow, clearly temporary workaround over skipping verification.

---

## BRANCH, REPORT, AND GIT WORKFLOW — READ `WORKER-GIT-SYNC-PROTOCOL.md` FIRST

Full mechanics (cloning, syncing, testing, the report format, Design-Fidelity Lock, Scope Lock enforcement) are in `WORKER-GIT-SYNC-PROTOCOL.md` at the repo root — read it in full before writing anything, this tracker does not repeat it. What's specific to this project:

- **Branch naming is project-scoped, not the bare `task/N` the protocol document's own examples use.** The shared repo already has an unrelated `task/3` branch from a different project — bare task numbers collide across projects (flagged in `TODO.md` #3). Every task below gives you an exact branch name in the form `task/status-N` — use it exactly, don't shorten it to `task/N`.
- **Your report file is `reports/task-status-[N].md`** (same reasoning — `reports/task-3.md` already exists and belongs to someone else's work), following the exact section structure `WORKER-GIT-SYNC-PROTOCOL.md` Step 6 defines, about 80 lines, no pasted code.
- **Read `TODO.md`'s Table of Contents at the start of your session and again before you finish.** Handle anything addressed to your task number or to `ALL`.
- **Read `ROADBLOCKS-AND-FIXES.md` at the repo root before your first command.** Every wall it lists has already been hit and solved — the light clone, branch/report naming, deploy mechanics, blocked-push handling. Don't spend time rediscovering something that's already answered there.
- **Every task below includes this standing instruction, not repeated per-task below the line:** *Follow Step 4 of the Worker protocol for anything you find outside your scope: verify it first; a small fix (about 10 lines or fewer, one place, no shared structure, no clash with another active branch) → fix it yourself as a separate `TASK-status-N-EXTRA` commit (your task's scoped id); anything bigger or uncertain → add an entry to `TODO.md`. Test your own work with the installed tools (Playwright, code-review/security-audit skills) before you report — you have the same tools the Integrator does, don't rely on them to catch what you could have caught.*

---

## SHARED DATA CONTRACTS — READ BEFORE TOUCHING ANY SHARED SCHEMA

Several tasks below read or write the *same* status record from different files. If each worker independently guesses field names or status values, the pieces won't agree once combined — so these are fixed here, once, as the single source of truth every task must follow exactly. If a task's own report or merged code ever shows these fields differently, this section wins; flag the mismatch rather than silently going with whichever you saw last.

**BNPL status record** (Tasks 4, 5, 6, 7 all read and/or write this): JSON field names are `submission_id` (string, exact `IP-XXXXXXXX` format), `status` (string, one of exactly `"pending"`, `"approved"`, `"rejected"` — lowercase, no other spellings or values), `approved_by`, `approved_at` (ISO 8601), `rejection_notes` (string, empty string if not rejected), `history` (array of `{ from, to, by, at }` objects — this is Task 4's audit trail; Task 5's UI, Task 6's trigger, and Task 7's checker all read this same array, not a separate log). Task 4 defines this schema when it builds the backend — every later task using it must match these exact names, not invent its own.

**Uptime status record** (Tasks 8, 9, 10 all read and/or write this): `status` is one of exactly `"up"`, `"down"`, `"degraded"` — lowercase, no other spellings (not "operational"/"outage"/etc., even though the public-facing *label* shown to visitors can say "Operational" — the stored value itself stays one of these three). `history` (array of `{ from, to, note, at }` objects) is the incident log Task 8 builds — Task 9's banner/badge/notify feature and Task 10's automated reconciliation both read and append to this same array, not separate logs each. Task 8 defines this schema; Tasks 9 and 10 must match it exactly.

**Any worker who needs to change one of these field names or values** must treat that as work outside their task's scope (Scope Lock, Rule 8b) — log it as a blocking issue and ask, rather than quietly adopting a different name that only their own piece uses.

---

## TABLE OF CONTENTS

- [Shared Data Contracts](#shared-data-contracts-read-before-touching-any-shared-schema)
- [Model & Settings Guidance](#model--settings-guidance)
- [Task 1 — status.petcenterco.com Subdomain Shell](#task-1)
- [Task 2 — Adoption Application Status Checker](#task-2)
- [Task 3 — Harden the Live Adoption Status Exposure](#task-3)
- [Task 4 — BNPL Status Backend](#task-4)
- [Task 5 — BNPL Admin Review Panel](#task-5)
- [Task 6 — Email Notification Infrastructure + BNPL Trigger](#task-6)
- [Task 7 — BNPL Public Status Checker](#task-7)
- [Task 8 — Uptime Status Dashboard (Core)](#task-8)
- [Task 9 — Uptime Dashboard Extras](#task-9)
- [Task 10 — Automated Uptime Monitoring Integration](#task-10)
- [Task 11 — Additional Status Categories (Research & Proposal)](#task-11)
- [Task 12 — Extend Email Notifications to the Adoption Approval Flow](#task-12)

---

## MODEL & SETTINGS GUIDANCE

Use **Claude Sonnet 5** (free tier is the practical ceiling). Turn on **extended thinking** for anything non-trivial. Keep **web search on** throughout. Deliver real completed work per turn; save check-ins for genuine blockers — don't guess at those, ask.

---

<a id="task-1"></a>
## Task 1 — status.petcenterco.com Subdomain Shell

**Complexity:** Medium. Standalone, no dependencies.

**Branch:** `task/status-1` **Report:** `reports/task-status-1.md`

**What:** Build the base site shell: own header/footer (not `cats.html`'s), a homepage listing status-check categories, shared page chrome/CSS. Build in from the start, not retrofitted later: status meaning is never color-alone (pair every status color with an icon shape and a text label — Operational/Degraded/Down); check badge colors against the Navy/Gold/Orange palette for 4.5:1 text / 3:1 icon contrast; keep the shell itself light on third-party CDN/font dependencies so it still renders if something else is down; add a plain JSON endpoint (e.g. `/api/status.json`) mirroring the homepage's status summary. Also build an email "subscribe to updates" capture form on the homepage — just the form and storage of submitted addresses (a simple list file is fine); this task does not send anything, it only collects the list Task 9 will read from later.

**Where:** New files only, under a new `status.petcenterco.com` root. Does not touch `adopt.petcenterco.com` or the admin dashboard.

**Files to request from the CEO:** none required to start.

**Read first:** `adoption-website-project-summary.md` in full, `PETCENTER_DESIGN_SYSTEM_v1.0.md`.

### Recommended Enhancements (Not Mandatory)

- Consider a small "last checked/updated" timestamp on the homepage, so visitors trust the data is current rather than a stale cache.
- Another team is building a dark/light mode toggle for the admin dashboard elsewhere on this project. Worth a quick thought on whether `status.petcenterco.com` should visually support it too for consistency across Pet Center's properties — not required, your call based on time and whether it fits the shell you build.
- Another team is building sitewide auto-translation for the main site. If that ships, this new subdomain could end up the one page that isn't translated. Not something to build into this task now — just worth a mental note to flag to the CEO later if it becomes relevant, rather than something to solve here.

**⚠️ Not a final decision:** This is a headstart, not a directive. You own the code and the files, and by the time you're building this you'll have context I don't have right now. Weigh it yourself, search the web if it helps, and go with what's actually right for what you're building — not just what's written here.

---

<a id="task-2"></a>
## Task 2 — Adoption Application Status Checker

**Complexity:** Medium. **Depends on Task 1.**

**Branch:** `task/status-2` **Report:** `reports/task-status-2.md`

**What:** Server-side proxy on `status.petcenterco.com` fetching `application.json` from `adopt.petcenterco.com` (avoids CORS), adapted checker UI. Rate-limit lookups per IP and return a generic "not found" that doesn't distinguish wrong-code from nonexistent-code. Require a second identifier (e.g. last name) matched server-side in the proxy before returning applicant data — adoption codes are a ~1-billion-combination numeric space with no protection today, so this proxy is the first line of defense even though the deeper fix is Task 3. Show status as a timeline (Submitted → Under Review → Approved/Rejected), not a bare word. Distinguish "temporarily unavailable" (the cross-domain call itself failed) from "not found" (no such code) — don't conflate them.

**Where:** New page(s) + new proxy endpoint under `status.petcenterco.com`. Does not modify `adopt.petcenterco.com`.

**Files:** no CEO hand-off needed — find them directly in the repo. The existing checker (this tracker's "status-2.html") is `adopt/adoptions/status/index.html`; the submission handler (this tracker's "adoption-application-6.php") is `adopt/apply/adoption-application.php` — **not** the near-identical copy in `adopt/apply/Old/`, which is a leftover, not the live file. `adopt/assets/js/status.js` also exists and references a `/api/adoption/{code}` pattern this task's proxy doesn't use — it isn't wired to any page, confirmed by checking what actually calls it; don't build against it, and mention it in your report's Things the Next Person Must Know rather than acting on it, since whether it's intentional future scaffolding or dead code isn't this task's call.

**Read first:** `adoption-website-project-summary.md` Section 7, `PETCENTER_DESIGN_SYSTEM_v1.0.md` (this task makes UI decisions on the checker page).

### Recommended Enhancements (Not Mandatory)

- A brief server-side cache (30–60 seconds) on successful proxy lookups could reduce repeat load on `adopt.petcenterco.com` if someone refreshes rapidly — worth considering, not required.
- Another team is doing a security overhaul of the live-chat system, which has a very similar problem to solve: preventing a guessable identifier (a chat ID there, an application code here) from being abused. If they land on a specific anti-enumeration pattern, aligning this task's second-identifier approach with it would keep the whole site consistent — worth a quick check if their work is visible to you, but not worth waiting on if it isn't.

**⚠️ Not a final decision:** This is a headstart, not a directive. You own the code and the files, and by the time you're building this you'll have context I don't have right now. Weigh it yourself, search the web if it helps, and go with what's actually right for what you're building — not just what's written here.

---

<a id="task-3"></a>
## Task 3 — Harden the Live Adoption Status Exposure

**Complexity:** Medium. Standalone — different domain/file scope from every other task here, can run in parallel with anything. **Recommend prioritizing this one** — it's a fix to a live, currently-exposed production page, not new-feature work.

**Branch:** `task/status-3` **Report:** `reports/task-status-3.md`

**What:** `adopt.petcenterco.com`'s existing checker page (`adopt/adoptions/status/index.html` in the repo) fetches `application.json` directly, client-side, with no authentication and no rate limiting. That JSON contains the applicant's full name, phone number, and home address. The code space (`AD-#####-####`) is small enough to be practically guessable at scale. Add rate limiting on this lookup, and require a second identifier (matching Task 2's approach, for consistency) before the real data is returned — which likely means restructuring this from "fetch a static JSON file directly" to "call a small PHP endpoint that checks the second identifier server-side before returning the data," since a static file can't gate on anything by itself. Preserve the exact current UX for legitimate users (same inputs, same look) — this is a security fix, not a redesign.

**Where:** `adopt/adoptions/status/index.html`, plus a new small PHP endpoint alongside it. Does not touch `status.petcenterco.com` or any other task's files.

**Files:** no CEO hand-off needed — in the repo on `main`: `adopt/adoptions/status/index.html` and `adopt/apply/adoption-application.php` (for the exact `application.json` shape) — not the decoy copy in `adopt/apply/Old/`.

**Read first:** `adoption-website-project-summary.md` Section 7, `PETCENTER_DESIGN_SYSTEM_v1.0.md` (even though this preserves existing UX, any code touching the rendered page falls under Rule 9).

### Recommended Enhancements (Not Mandatory)

- Same note as Task 2: this and the live-chat security work are solving the same underlying problem (an enumerable identifier) in two different parts of the site. Keeping the two fixes conceptually consistent is a nice-to-have, not a requirement, given they're different workers on different timelines.
- A simple failed-lookup counter (not a full audit trail — just a number) could give the CEO visibility into whether this endpoint is actively being probed right now. Optional, and only worth it if it's cheap to add alongside the rate limiting you're already building.

**⚠️ Not a final decision:** This is a headstart, not a directive. You own the code and the files, and by the time you're building this you'll have context I don't have right now. Weigh it yourself, search the web if it helps, and go with what's actually right for what you're building — not just what's written here.

---

<a id="task-4"></a>
## Task 4 — BNPL Status Backend

**Complexity:** Medium-large. **Tasks 5, 6, and 7 depend on this.**

**Branch:** `task/status-4` **Report:** `reports/task-status-4.md`

**What:** Per-`submission_id` status record mirroring `application.json`'s shape, and a write endpoint analogous to `update-adoption.php`. Modifying `submit-installment.php` to create the initial record at submission time is in-scope. Strictly validate the `submission_id` format (`IP-` + exact hex pattern `submit-installment.php` generates) before it's ever used to build a file path — this is a required part of building the backend correctly, not optional hardening. Keep an append-only audit trail of status transitions (who, old→new, when) alongside the current-status file, not just the current value. Use `flock()` around each status file's read-modify-write to avoid a race if two admin actions land close together.

**Where:** New backend file(s) alongside `submit-installment.php`; `submit-installment.php` itself may need modification (in-scope).

**Files:** `submit-installment.php` and `installment_submissions.log` aren't in the repo yet — request these from the CEO. `adoption-application.php` (`adopt/apply/`) and `update-adoption.php` (`adopt/admin/`) are both on `main` — read them there for the exact shape to mirror. Note: `adopt/admin/` also has an `update-adoption8.php` that differs meaningfully from `update-adoption.php` — confirm with the CEO which one is actually live before treating either as the reference, rather than assuming.

**Read first:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` in full.

### Recommended Enhancements (Not Mandatory)

- Task 11's research may propose other payment-adjacent categories (Pay on Delivery, Pay-in-4). Worth a moment's thought on whether the schema you're building here is generic enough to extend later without a rebuild — not a requirement to design for hypothetical categories now, just worth not painting yourself into an unnecessarily BNPL-specific corner if it costs nothing to avoid.

**⚠️ Not a final decision:** This is a headstart, not a directive. You own the code and the files, and by the time you're building this you'll have context I don't have right now. Weigh it yourself, search the web if it helps, and go with what's actually right for what you're building — not just what's written here.

---
<a id="task-5"></a>
## Task 5 — BNPL Admin Review Panel

**Complexity:** Medium-large. **Depends on Task 4.**

**Branch:** `task/status-5` **Report:** `reports/task-status-5.md`

**What:** New "BNPL Applications" panel in the admin dashboard (`adopt/admin/index.html` in the repo — this tracker previously called it "admin-dashboard.html"; that's not the real file name, use the path) — list, status filter, detail view, approve/reject — matching the existing adoption Applications panel's shape. Reuse the existing upload/preview modal (documented in both project summaries) for viewing submitted documents — do not build a new viewer. Add bulk approve/reject and a staff notes field on each submission.

**Where:** `adopt/admin/index.html` — **not** the decoy copy in `adopt/admin/Old/`. Shared with other in-flight work on this codebase, each under its own project-scoped id. Wrap your additions in `TASK-status-5 START/END`, not a bare number, so your zone is distinguishable from theirs in the same file. Pull the latest version of this file from the repo (`git fetch origin` / check other branches), not an original copy.

**Files:** `adopt/admin/index.html`, from the repo on `main`, plus `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` Section 9. For Task 4: its merged code on `main` once approved, or if not yet merged, read it with `git diff origin/main...origin/task/status-4` plus `reports/task-status-4.md`.

**Read first:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` in full, `PETCENTER_DESIGN_SYSTEM_v1.0.md` (this task makes UI decisions for the new panel).

### Recommended Enhancements (Not Mandatory)

- `adopt/admin/index.html` is being touched by this task and by other in-flight work on the admin dashboard (dark/light mode, chat security) around a similar timeframe. Double-checking with the CEO for the truly latest version immediately before starting — not just relying on whatever you were handed — is worth the extra thirty seconds given how many hands are in this one file right now.

**⚠️ Not a final decision:** This is a headstart, not a directive. You own the code and the files, and by the time you're building this you'll have context I don't have right now. Weigh it yourself, search the web if it helps, and go with what's actually right for what you're building — not just what's written here.

---

<a id="task-6"></a>
## Task 6 — Email Notification Infrastructure + BNPL Trigger

**Complexity:** Small-medium — intentionally small and quick, since Task 9 also needs this to exist before it can build. **Depends on Task 4** (hooks into its write endpoint). Sequence this early relative to Task 9.

**Branch:** `task/status-6` **Report:** `reports/task-status-6.md`

**What:** Set up a Brevo account and API integration (free tier: 300 emails/day, PHP SDK support — comfortably covers this project's volume) and write one reusable send-email helper function/file. Wire it into Task 4's status-write endpoint so approving or rejecting a BNPL submission emails the applicant. This is BNPL-only for now — it does not touch `update-adoption.php` or the adoption approval flow (see "Not Yet Assigned" below). Build this helper to be genuinely reusable — Task 9 will call it too, and should not need to duplicate any of this setup.

**Where:** New helper file (e.g. alongside Task 4's backend files); adds a hook into Task 4's write endpoint — shared file, wrap your addition in `TASK-status-6 START/END`, merge `task/status-4` into `main` (or at minimum pull its latest commits) before you branch, so you're hooking into Task 4's real endpoint, not a stale copy.

**Files:** Task 4's merged code on `main` once approved, or if not yet merged, `git diff origin/main...origin/task/status-4` plus `reports/task-status-4.md`.

**Read first:** the same — this is a focused, self-contained piece, nothing else needed.

### Recommended Enhancements (Not Mandatory)

- No additional recommendations beyond what's already required above — this task is small and focused enough that there isn't more to layer on. (Brevo's own dashboard happens to log what it sends, which is a nice side benefit for the CEO's own visibility, but that's just worth knowing exists, not something to build.)

**⚠️ Not a final decision:** This is a headstart, not a directive. You own the code and the files, and by the time you're building this you'll have context I don't have right now. Weigh it yourself, search the web if it helps, and go with what's actually right for what you're building — not just what's written here.

---

<a id="task-7"></a>
## Task 7 — BNPL Public Status Checker

**Complexity:** Small-medium. **Depends on Task 4**, reuses Task 2's proxy rather than building a second one.

**Branch:** `task/status-7` **Report:** `reports/task-status-7.md`

**What:** Public BNPL checker on `status.petcenterco.com`, extending Task 2's proxy to also serve BNPL records. Same rate-limiting/generic-error treatment as Task 2, for consistency (BNPL's own submission ID is already strong — 6 random bytes, effectively unguessable — so this is about consistency and defense-in-depth, not closing an equivalent hole). Same status-timeline UI as Task 2, reading off Task 4's audit trail.

**Where:** New page under `status.petcenterco.com`; extends Task 2's proxy endpoint — shared file, wrap your addition in `TASK-status-7 START/END` alongside Task 2's existing `TASK-status-2` block, merge `task/status-2`'s latest commits before you start per Rule 12.

**Files:** Task 2's and Task 4's merged code on `main` once approved, or if either isn't merged yet, `git diff origin/main...origin/task/status-2` / `...task/status-4` plus their `reports/task-status-N.md` files.

**Read first:** `adoption-website-project-summary.md` Section 7, `PETCENTER_DESIGN_SYSTEM_v1.0.md` (this task makes UI decisions on the checker page).

### Recommended Enhancements (Not Mandatory)

- Same caching idea as Task 2 (a brief server-side cache on the proxy) applies equally here, for the same reason. Optional.

**⚠️ Not a final decision:** This is a headstart, not a directive. You own the code and the files, and by the time you're building this you'll have context I don't have right now. Weigh it yourself, search the web if it helps, and go with what's actually right for what you're building — not just what's written here.

---

<a id="task-8"></a>
## Task 8 — Uptime Status Dashboard (Core)

**Complexity:** Medium. **Depends on Task 1.**

**Branch:** `task/status-8` **Report:** `reports/task-status-8.md`

**What:** Admin-facing manual up/down/degraded control per domain/subdomain, a public display, and an incident history log (append an entry — timestamp, old→new status, optional note — every time status changes; display it publicly per domain). The log rides on the same write action as the manual control, so it's part of this task's core build, not a separate one.

**Where:** New page(s) under `status.petcenterco.com`, plus a lightweight admin-side control (your call whether that's a protected page on `status.petcenterco.com` itself or elsewhere — state your choice clearly in your report).

**Files to request from the CEO:** current list of domains/subdomains under `petcenterco.com`.

**Read first:** `PETCENTER_DESIGN_SYSTEM_v1.0.md`.

### Recommended Enhancements (Not Mandatory)

- Another team is building a separate Rehoming Platform that may eventually get its own subdomain. If/when it does, it could be worth adding to this dashboard's domain list later — nothing to build now, just worth keeping the domain list easy to extend rather than hard-coded to a fixed set.

**⚠️ Not a final decision:** This is a headstart, not a directive. You own the code and the files, and by the time you're building this you'll have context I don't have right now. Weigh it yourself, search the web if it helps, and go with what's actually right for what you're building — not just what's written here.

---
<a id="task-9"></a>
## Task 9 — Uptime Dashboard Extras

**Complexity:** Small-medium — three small features bundled into one assignment, matching how genuinely small work has been bundled elsewhere on this project. **Depends on Task 8, Task 1 (subscriber list), and Task 6 (email helper). Sequencing note: Task 10 also extends Task 8's files independently — do this task (9) first and have Task 10 pull this task's completed version of Task 8's files, not Task 8's original. If Task 9 hasn't shipped yet when Task 10 starts, Task 10 must check with the CEO before proceeding rather than both workers independently modifying Task 8's original in parallel.**

**Branch:** `task/status-9` **Report:** `reports/task-status-9.md`

**What:** Three additions to Task 8's dashboard: (1) a scheduled-maintenance banner the CEO can set to pre-announce planned downtime; (2) a small embeddable status badge (image or script snippet) that could later be dropped into `adopt.petcenterco.com`'s footer — generating it is this task's job, actually placing it on `adopt.petcenterco.com` is a different, unassigned task, not this one; (3) wiring Task 8's status-change action to notify everyone on Task 1's subscriber list, using Task 6's email helper — do not write a second email integration, call the existing one.

**Where:** Extends Task 8's files — wrap your addition in `TASK-status-9 START/END` alongside Task 8's `TASK-status-8` block, merge `task/status-8`'s latest commits before you start — reads Task 1's subscriber-list storage, calls Task 6's helper.

**Files:** Task 8's, Task 1's, and Task 6's merged code on `main` once approved, or for any not yet merged, `git diff origin/main...origin/task/status-N` plus that task's `reports/task-status-N.md`.

**Read first:** the same three, plus `PETCENTER_DESIGN_SYSTEM_v1.0.md` (the banner and badge are new UI elements).

### Recommended Enhancements (Not Mandatory)

- No additional recommendations beyond what's already required above.

**⚠️ Not a final decision:** This is a headstart, not a directive. You own the code and the files, and by the time you're building this you'll have context I don't have right now. Weigh it yourself, search the web if it helps, and go with what's actually right for what you're building — not just what's written here.

---
<a id="task-10"></a>
## Task 10 — Automated Uptime Monitoring Integration

**Complexity:** Medium — comparable in scope to Task 8 itself. **Depends on Task 8, and sequenced after Task 9** (see Task 9's sequencing note) — pull Task 9's completed version of Task 8's files as your starting point, not Task 8's original, since Task 9 will have already modified them.

**Branch:** `task/status-10` **Report:** `reports/task-status-10.md`

**What:** First step: verify UptimeRobot's current free-tier terms allow commercial/business use before building against it (its free tier — 50 monitors, 5-minute checks, has an API — is the strongest free option researched, but one source flagged a personal/non-commercial restriction worth confirming directly against UptimeRobot's own current terms). If clear, wire it in: register Pet Center's domains/subdomains as monitors, poll/sync results into Task 8's status data, and reconcile automated results with manual overrides so the CEO can still force a status regardless of what automated checks report. If UptimeRobot's terms are actually a blocker, the fallback is Uptime Kuma (free, self-hosted, unlimited monitors) — a materially bigger undertaking since it means standing up and maintaining a monitoring server, not just calling an API; flag this fork to the CEO before building it rather than assuming which path to take.

**Where:** Extends Task 8's files — wrap your addition in `TASK-status-10 START/END` alongside the `TASK-status-8`/`TASK-status-9` blocks already there, merge `task/status-9`'s latest commits before you start — plus new integration/sync code.

**Files:** Task 8's merged code on `main` once approved, or if not yet merged, `git diff origin/main...origin/task/status-8` plus `reports/task-status-8.md`.

**Read first:** the same — nothing else needed.

### Recommended Enhancements (Not Mandatory)

- No additional recommendations beyond what's already required above.

**⚠️ Not a final decision:** This is a headstart, not a directive. You own the code and the files, and by the time you're building this you'll have context I don't have right now. Weigh it yourself, search the web if it helps, and go with what's actually right for what you're building — not just what's written here.

---
<a id="task-11"></a>
## Task 11 — Additional Status Categories (Research & Proposal)

**Complexity:** Small, open-ended. Deliverable is a proposal, not code.

**Branch:** `task/status-11` **Report:** `reports/task-status-11.md`

**What:** Research and propose additional status-check categories. Start from four candidates already implied by Pet Center's own documented offerings, before reaching further: shipping/local delivery (the CEO's own original example), Pay on Delivery orders, Pay-in-4/in-house installments, and 30-day health insurance coverage/claims — as part of this research, confirm with the CEO whether "Pay-in-4/in-house installments" is the same flow this project has been calling "BNPL" throughout, or a genuinely separate one, since the original site documentation lists them as three distinct payment options. Evaluate every candidate (these four and anything else proposed) against the same three questions: does backing data already exist, what's the smallest backend addition that would make it real, and can a fully functional "not found" UI ship now regardless of the answer to the first two.

**Where:** No file changes.

**Files to request from the CEO:** none required to start.

**Read first:** `adoption-website-project-summary.md` in full, `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` in full.

### Recommended Enhancements (Not Mandatory)

- Another team is working on location/shipping content pages elsewhere on this project. Shipping-status is your leading candidate category — check what's actually been built there before assuming that data source needs to be created from scratch; there may already be a foundation to check status against.
- A separate Rehoming Platform (a marketplace-style project) is in progress elsewhere — a different application/listing flow that doesn't exist yet, but may be worth noting as a future category once it ships — not something to research now, just worth flagging in your proposal as a "watch for later" rather than leaving it undiscovered.

**⚠️ Not a final decision:** This is a headstart, not a directive. You own the code and the files, and by the time you're building this you'll have context I don't have right now. Weigh it yourself, search the web if it helps, and go with what's actually right for what you're building — not just what's written here.

---
<a id="task-12"></a>
## Task 12 — Extend Email Notifications to the Adoption Approval Flow

**Complexity:** Small. **Depends on Task 6** (calls its existing email helper — do not write a second one). Standalone otherwise: no other task touches `update-adoption.php`, so there's no file-sharing conflict to manage here.

**Branch:** `task/status-12` **Report:** `reports/task-status-12.md`

**What:** CEO-approved: adoption applicants should get the same "your status changed" email treatment BNPL applicants get from Task 6. Hook `update-adoption.php` so that when an admin approves or rejects an adoption application, it calls Task 6's existing email helper to notify the applicant — mirroring exactly how Task 6 wired the same trigger into the BNPL write endpoint. This is a trigger addition to an existing, working file, not a redesign of the approval flow itself — Scope Lock still applies: touch only what's needed to add the email call, nothing else in `update-adoption.php`.

**Where:** `adopt/admin/update-adoption.php` (existing). Not shared with any other task above — but see the note below before you touch it.

**Files:** `adopt/admin/update-adoption.php`, from the repo on `main`. **Confirm with the CEO this is actually the live file before starting** — `adopt/admin/` also has an `update-adoption8.php` that differs from it meaningfully (found while scoping Task 4), and this tracker doesn't know which one the admin dashboard actually calls. Also: Task 6's merged code on `main` once approved, or if not yet merged, `git diff origin/main...origin/task/status-6` plus `reports/task-status-6.md`.

**Read first:** the same, for the exact helper interface, plus `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` Section 9 (`update-adoption.php` documentation).

### Recommended Enhancements (Not Mandatory)

- No additional recommendations beyond what's already required above.

**⚠️ Not a final decision:** This is a headstart, not a directive. You own the code and the files, and by the time you're building this you'll have context I don't have right now. Weigh it yourself, search the web if it helps, and go with what's actually right for what you're building — not just what's written here.
