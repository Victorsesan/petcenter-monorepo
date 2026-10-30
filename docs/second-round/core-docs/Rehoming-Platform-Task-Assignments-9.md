# REHOMING PLATFORM — TASK ASSIGNMENTS

**Prepared by: Vice Dev Manager (VDM-5 / Web-Project #6)**
**Sent to: Workers (Claude AI instances)**

---

> **HOW WORK IS DELIVERED HAS CHANGED.**
> Workers no longer write completion documents with pasted code. Everyone now shares one GitHub repository: `Victorsesan/petcenter-monorepo`. Workers push a branch and a short report (about 80 lines, no pasted code); VDMs review the real code; the Integrator merges and publishes. The `WORKER-GIT-SYNC-PROTOCOL.md` at the repo root governs every step of this. Where the Warning & Instructions section below or any task description still uses the word "completion file," read it as "branch + `reports/task-reh-[N].md`." **The files attached by the CEO and the copies at the repo root win over anything older.**
>
> **Branch naming for this project:** every branch is `task/reh-[letter+number]` (for example `task/reh-A1`, `task/reh-B3`). Report files are `reports/task-reh-[letter+number].md`. Code anchor comments are `TASK-reh-[letter+number] START/END`.

---

## INTRODUCTION

Welcome to Pet Center. Pet Center runs adopt.petcenterco.com, a pet adoption platform. This document assigns your work on the Rehoming Platform — a new register-to-rehome-or-adopt marketplace built on top of the existing site, with two account types: visitor-users who can browse and message about pets, and merchant-users (individuals or organizations) who list pets for rehoming, closely mirroring the existing super admin dashboard pattern.

Each sub-task below is a self-contained assignment. Read the Warning & Instructions section fully before starting any work — it applies to every task in this document without exception.

**Before your first command, read these files at the repo root on `main`:**
- `ROADBLOCKS-AND-FIXES.md` — every wall already hit during setup, with the exact fix. Skim it before your first command.
- `WORKER-GIT-SYNC-PROTOCOL.md` — how to clone, branch, commit, test and report.
- `TODO.md` — the shared inbox. Read its Table of Contents at the start and before finishing; handle every entry addressed to you or `ALL`.
- `adoption-website-project-summary.md` — how the public adoption site works (required for most tasks).
- `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` — how the admin dashboard works (required for tasks that clone admin patterns).
- `PETCENTER_DESIGN_SYSTEM_v1.0.md` — the visual source of truth.
- This file (`Rehoming-Platform-Task-Assignments.md`) — your task assignment.

---

## ⚠️ WARNING & INSTRUCTIONS — READ THIS FIRST, EVERY WORD

*(Copied from the live repo's `adoption-website-project-summary.md` — that repo copy wins over any older version you may hold.)*

### Core rules (apply to every task, every file, every page)

1. **Self-question before and after every task.** Before running any command, ask yourself whether it's actually correct. After running it, check the result — don't assume it worked because it didn't error. Even after moving past a step, look back at it again before finishing.

2. **Never use emojis anywhere in the page or the code.** Use icons only (inline SVG, matching the existing icon style already in use on the page you're editing).

3. **Never create a `.md` or `.txt` file unless explicitly told to** — with one exception: your short `reports/task-reh-[N].md` is required, not optional.

4. **Carry these instructions through every command you run.** Don't let them apply only to the first task and quietly drop off later.

5. **Every page is deployed as a folder path, never a bare filename in the URL.** On the server, each page is uploaded as its own folder containing an `index.html` — e.g. the source file `pet-detail.html` is uploaded as `pets/detail/index.html`, producing the clean URL `.../pets/detail/`. Every internal link and every URL-building line of code must use the clean folder path, never the `.html` filename. **The only exception is admin-side files that aren't HTML files.** **Note: this rule is about deployed behavior only — when referring to or requesting a file, always use its real source filename** (e.g. `pet-detail.html`, `pets.html`), never the deployed `index.html` name, since multiple source files all become `index.html` on the server and requesting by that name is ambiguous.

6. **Write code comments.** You must always write code comments on all your code to help other AI easily find specific code during debugging, and update the code comment after any changes. Give a name for each code block and write its comment. *(Restored 2026-10-12 — see TODO #2; was missing in the original upload.)*

7. **Never recreate a feature from scratch.** If a reusable feature already exists (file upload/preview modal, hamburger menu, bottom sheet + filter + search, signature pad, PDF generation, admin dashboard account/messaging patterns, email-sending, etc.), go to the exact file(s) where it's documented and copy the exact same code for consistency — don't reimplement your own version.

### Directives (apply project-wide, carry equal weight to the rules above)

8a. **Never edit this document directly, under any circumstance.** This file is read-only reference material — it is not a shared changelog. **Work is no longer delivered as a long completion file with pasted old/new code.** All work now lives on a shared git branch that everyone can read, so you deliver it the way the Worker protocol (`WORKER-GIT-SYNC-PROTOCOL.md`, repo root) says: your code and comments on your own branch `task/reh-[N]`, plus a **short** `reports/task-reh-[N].md` (about 80 lines, **no pasted code**) with the sections defined there (Task Number, What Was Required, What I Built, Files Changed, Small Out-of-Scope Fixes Made, Bugs and Follow-ups Sent to TODO.md, Things the Next Person Must Know, Self-Check Confirmation). Every code block you add or change still carries its task-numbered anchor comment (`<!-- TASK-reh-N START -->` … `<!-- TASK-reh-N END -->`) and an explanatory comment, per Warning & Instructions rule 6, with no exceptions — including trivial one-line changes. Because there is no longer a completion document to carry explanations, anything a future reader needs to understand a block (why it is built that way, a trap, a dependency) must be written as a comment next to that code. Anything someone must act on goes into `TODO.md` at the repo root. Brand-new files are simply committed on your branch. A task is not considered done until your branch and report are pushed.

8b. **Scope Lock — no exceptions, ever, except the verified small-fix rule below.** You touch only the files explicitly involved in your task. If completing your task correctly seems to require a change somewhere else — a shared component, a related page, anything not part of your assignment — you do not make that change yourself, no matter how clearly correct it seems, **unless it passes the small-fix test in Step 4 of the Worker protocol**: first verify the problem is real (you can read every file and every branch); the fix is about 10 changed lines or fewer in one place; it adds no new file and changes no shared structure; and no other active branch edits the same lines. In that case fix it as its own separate commit wrapped in `TASK-reh-N-EXTRA START/END` anchor comments and list it in your report. Anything bigger, or anything you are unsure about, you do **not** fix: add an entry to `TODO.md` (file, approximate line range, branch, how to reproduce, severity) and list its number in your report. If something outside your scope is genuinely blocking your own task, raise it in `TODO.md` addressed to your VDM rather than working around it. Entries addressed to you in `TODO.md` count as permission to touch the files they name.

8c. **Second verification round.** If the CEO asks you to re-check your completed work, and you make any further changes, push them to the *same branch* and add a short, clearly labeled **`## SECOND VERIFICATION ROUND`** section to your *existing* `reports/task-reh-[N].md` — never a new file. Keep it short, with no pasted code, and the same anchor-comment and Scope Lock rules apply. A request to re-verify your own work is not permission to touch anything outside your original assignment.

9. **Consult `PETCENTER_DESIGN_SYSTEM_v1.0.md` before making any UI, styling, or animation decision.** Use its Table of Contents to jump to the relevant section. On adoption-related public pages, the header, footer, hamburger menu, and core color palette already documented in `adoption-website-project-summary.md` stay exactly as they are.

9a. **Dropdown menus — read this even if you skimmed everything else.** Any dropdown/select menu you build, anywhere in this project, must use the modern dropdown component documented in `PETCENTER_DESIGN_SYSTEM_v1.0.md` — not a native HTML `<select>`, not an old or default-styled dropdown, not something you remember from training data. This has been restated repeatedly across this project specifically because most workers still default to an old-style dropdown despite the warning already being here — if you're about to write a dropdown and haven't just opened `PETCENTER_DESIGN_SYSTEM_v1.0.md`'s dropdown section in this session, stop and do that first, then build from what it actually shows.

10. **Image/reference-material gate — hard stop if it's missing.** If a task says a screenshot or reference image is required and it isn't present, stop and wait — don't guess.

10a. **Design-fidelity lock — for any task involving a reference image or HTML design source.** Treat the reference material as the literal, exact target. Before calling any design-related task done, use the Playwright headless browser tool to load your finished page and visually compare it against the reference — mandatory, not optional. Save the reference material to your own outputs directory and keep checking against it throughout the task.

11. **Use web search during implementation — don't rely only on training knowledge.** Tool choices, free/no-cost libraries, current library versions, current best practices — search before committing to an approach. This project requires free and open-source tools only, evaluated for genuine quality (GitHub star count, issue/maintenance health, and community reviews from places like Reddit) — not just "it's free."

12. **Multi-worker, same-file protocol — anchor comments required.** Where more than one worker touches the same file: name each worker's zone explicitly, wrap every addition in a unique task-numbered comment block, and merge the latest `main` into your branch before finishing. See Step 3 of `WORKER-GIT-SYNC-PROTOCOL.md`.

13. **Use `cats.html` as the canonical blueprint for header, footer, and hamburger menu** on any pet-related page. It is at `adopt/pets/cats/index.html` in the repo (confirm with `git diff origin/main` if unsure).

14. **Read the relevant project summary document in full before starting.** Website work → `adoption-website-project-summary.md` in full. Admin dashboard work → `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` in full. Work spanning both → both, in full. Both are at the repo root on `main`.

14a. *(Covered by 8a above — the git workflow.)*

14b. *(Covered by 8b above — Scope Lock.)*

14c. *(Covered by 8c above — second verification round.)*

**Bug / follow-up line (include this behavior for every task):** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything you find outside your scope: verify it first; small fix (about 10 lines or fewer, one place, no shared structure, no clash with another active branch) → fix it yourself as a separate `TASK-reh-N-EXTRA` commit; anything bigger or uncertain → add an entry to `TODO.md`. Test your own work with the installed tools before you report.

---

## TABLE OF CONTENTS

- Phase A — Core Data Model: A1, A2, A3, A4
- Phase B — Merchant Verification & Trust Infrastructure: B1, B2, B3, B4, B5, B6
- Phase C — Trust, Safety & Anti-Fraud: C1 (C1a/C1b), C2, C3, C4, C5, C6
- Phase D — Post-Placement & Ownership Transfer: D1
- Phase E — Merchant Dashboard & Support: E1 (E1a/E1b), E2, E3, E4, E5
- Phase F — Discovery, Engagement & Growth: F1, F2, F3, F4, F5, F6, F7
- Phase G — Business-Adjacent: G1

---

## MODEL & SETTINGS RECOMMENDATIONS

Claude Sonnet 5 (free tier is the practical ceiling for this project). Extended thinking on for complex sub-tasks. Web search on throughout. Deliver real completed work per message turn rather than routine check-ins — save check-ins for genuine blockers (a missing reference image, a genuine scope ambiguity).

---

## BUILD APPROACH — READ THIS BEFORE STARTING ANY TASK BELOW

This project is fundamentally a modification of the existing super admin dashboard, not a new system built alongside it. The pattern is: an account creates itself, does actions in that account, and those actions reflect on the public site — which is exactly what `admin-dashboard.html` and its supporting files already do for the super admin today. **Almost every merchant-facing capability in this document already exists in some form in the super admin dashboard and just needs modification for a merchant account context — not a fresh build.**

Concretely, before starting any task below that says "build a page/panel/form" for merchant or visitor use: check whether `admin-dashboard.html` or its supporting files already implement that capability for the super admin. If they do — posting a pet, editing a pet, messaging, replying to reviews, bot-message-scenario settings, dashboard navigation and layout — **clone that exact panel's markup, CSS classes, and JS**, then modify labels, permissions, and context for the merchant or visitor account. Do not design a new standalone page from scratch when an admin equivalent exists. This is Rule 7 applied specifically to `admin-dashboard.html` as this project's primary reusable-feature source. Each task below that this applies to says so explicitly and names the specific admin panel to clone from — if a task doesn't name one and you believe it should, that's worth flagging rather than guessing either way.

**What each task actually owns:** a full merchant pet listing has many fields, and no single task in this document builds the whole thing — that's intentional, since a complete listing flow is too large for one worker's 3 days. Task A1 clones the admin posting panel and owns the seven newly-added listing fields (health status, temperament, reason for rehoming, spay/neuter status, listing type, currency code, urgent flag). Everything else — bonded-pair/needs-extra-love flags (A2), listing status/placement lifecycle (D1, E2), verification badges (B2/B3), guided first-timer hints (F4), locale/currency display (F7), and donation/checkout links (G1) — is a different task, deliberately, so each stays a 3-day-sized unit. If you're working a task and a field you'd expect isn't there, it's very likely owned by one of those other tasks, not missing — check the SHARED CONTRACTS section below first, which lists every cross-task field by name and which task owns it.

**Standing rule, no exceptions:** the platform's account registration and login pages are being built separately by the CEO directly and are never in scope for any task in this document, in any form. No task should reference them, name them, describe waiting on them, or request their files from the CEO — not even as background context. Where a task's flow starts from "however the visitor/merchant arrived at this page," describe it as a generic entry point (a URL parameter, a session flag, an account-status check) and never by naming the account/signup/login pages. The one narrow exception: a task that needs to *link to* those pages (a "Sign Up" button pointing somewhere) may ask the CEO for the destination URL only — a URL is a fact, not a file, and doesn't require seeing or understanding those pages' structure or content.

---

## VISIBILITY & ACCESS MODEL — applies across every task below that touches a listing or an interactive feature

All pet listings and their full details (photos, health/temperament/reason-for-rehoming, merchant name and badges, everything from Task A1) are visible to every visitor, logged in or not, and to search engines. There is no content that only becomes visible after signing in — showing the full value of what's on the platform before asking someone to create an account is the deliberate design, not an oversight.

What *does* require an account — logged-out visitors can see these controls and what they do, but clicking one shows a "Sign up or log in to continue" prompt rather than performing the action or silently failing:
- Messaging a merchant (Task D1's enquiry flow)
- Thumbs-up/down on a listing, leaving a review, or reporting (Task C1)
- Submitting an adoption/rehoming application
- Saving to favorites or setting up a Pet Alert (Task F1)
- Joining a waitlist (Task D1)
- Taking the compatibility quiz's results further than a generic preview (Task F2) — the quiz itself can run for anyone, but saving results or applying them to a specific enquiry requires an account

Every task below that builds one of these actions must check login state and show this prompt for a logged-out visitor, rather than assuming the visitor is always logged in. This is called out explicitly in each affected task below.

---

## SHARED CONTRACTS

These are locked field/enum contracts every task below can build against **immediately, in parallel, without waiting on each other.** Each contract has one owning task, responsible for making the real implementation match it exactly. Every other task that references a contract builds against a local stub matching it — you do not need the owning task's branch to be merged to write and test your own code against these values. Reconciliation (merging your work into the real shared file) happens as a short pass once the owning task's branch is merged into `main` — not a rebuild, a merge.

**If your own work reveals a contract needs to change, that's a flagged, visible deviation in your report — never a silent private adjustment.** Everyone downstream reconciles against the change at integration time.

- **Listing core schema** (owned by Task A1): `health_status` (string), `temperament` (string), `reason_for_rehoming` (string), `spay_neuter_status` (enum: `spayed_neutered`/`not_yet`/`unknown`), `listing_type` (enum: `permanent`/`foster`), `foster_duration_estimate` (string, present only if `listing_type` is `foster`), `foster_covers` (string, present only if `listing_type` is `foster`), `currency_code` (enum: `USD`/`CAD`/`GBP`/`AUD`), `is_urgent` (bool). Tasks A3, F4, F5, F7, G1 build against this field list in parallel now, using a local stub object matching this shape — reconcile into A1's real listing file once A1's branch is merged into `main`.
- **Listing relationships/flags** (owned by Task A2): `grouped_with` (array of listing IDs), `last_inquiry_date` (timestamp), `needs_extra_love` (bool). A2 builds this in parallel with A1, not after it — A2 doesn't need A1's fields to exist to build its own; both add different fields to the same file and reconcile together at integration (see "Integration checkpoint" below). Task A3 builds its Needs-Extra-Love filter against this stub in parallel too.
- **Listing `status` field** (owned by Task D1): `active` | `pending_placement` | `placement_complete` | `placement_fell_through`. Task E2 adds `no_longer_available` to this same enum — E2 does not wait for D1 to finish; E2 builds its own account-lifecycle logic now, assuming these five values (four from D1's contract plus its own addition), and reconciles the actual enum definition with D1's real implementation at integration. Tasks A3, E1a, F1 all build their display/filter logic against this five-value contract in parallel, starting now. **Any task referencing listing status must use these exact values — not "pending," not "placed," not any paraphrase.** (Listings flagged by C2's anti-fraud detectors go into an admin review queue as a separate flag, not a change to this status field — don't conflate "flagged for review" with a listing status value.)
- **Listing ID field:** whatever the existing pet-listing ID field is called in the current data model (confirm exact name against the two summary docs — this one's a read of existing platform state, not a new contract to negotiate, so it doesn't block anyone).
- **Merchant `verification_status` field** (owned by Task B2): `pending` | `verified` | `rejected`. Task B3 builds its "Verified" badge trigger logic against this contract in parallel with B1/B2 — B3 doesn't need B1/B2 to have actually run a real verification to write and test "if verification_status equals verified, award the badge." Reconcile once B2's branch is merged into `main`.
- **Merchant activity tier field** (owned by Task B3): `new_merchant` | `active` | `trusted`. Note this is a *different* field from listing status even though "active" appears in both — no task should assume these are the same value on the same object.
- **B1's verification-flow entry contract** (owned by Task B1, published now so this task never needs to see or request the platform's separate signup/registration pages, which are outside this project's scope entirely): the verification flow is entered via a URL parameter or session flag (`?verify=true` or equivalent — B1's call on the exact mechanism, documented in B1's report). Build and test this flow as fully standalone — it never depends on seeing any other page. If it's ever wired into a different entry point later, that's a one-line routing change made by whoever owns that other system, not something this task needs to plan around or request files for.
- **Report record** (owned by Task C1b; C1a and C2 write to it): `report_id`, `source` (`user_report` | `system_flag`), `report_type` (`platform` | `welfare`), `target_type` (`listing` | `merchant`), `target_id`, `category` (string — for user reports the category chosen in the report form; for system flags the detector name: `duplicate_photo`, `posting_velocity`, `off_platform_contact`, `reputation_anomaly`), `description`, `reporter_id` (null for system flags), `created_at`, `status` (`open` | `reviewed` | `actioned` | `dismissed`), `holds_listing` (bool — true only for system flags that hold a listing back, see `held_for_review` below), `context_token` (random string used by the merchant response link), `merchant_context` (string, null until the merchant replies). Storage follows whatever pattern the admin dashboard already uses for comparable data (confirm against `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`) — the contract is the field names and enum values, not the storage layer; document the choice in your report. Welfare reports (`report_type: welfare`) are record-only and are never triaged.
- **Listing `held_for_review` field** (owned by Task C2): bool, default false. Set true by C2's posting-velocity detector; a listing where it is true never appears in browse/search results or to visitors (Task A3 excludes it). Task C1b's queue clears it when an admin sets the matching Report record to `dismissed`. This is a separate field from the listing `status` enum on purpose — do not add a status value for it.
- **Review record** (owned by Task C6; E1b writes `merchant_reply`; E5 writes seeded reviews): `review_id`, `merchant_id`, `reviewer_id`, `listing_id` (optional), `thumb` (`up` | `down`), `review_text` (string, may be empty), `created_at`, `merchant_reply` (string, null until the merchant replies), `merchant_reply_at`, `visibility` (`visible` | `hidden` — `hidden` is set only by an admin, never by the merchant), `is_seeded` (bool, true only for reviews created through the test-account panel in Task E5).
- **Account `is_test_account` field** (owned by Task E5): bool on the account record, default false. C1a, C2, and C6 each check the target merchant's flag before writing anything: if true, the visitor sees the normal success message but nothing is recorded, and nothing ever changes that account's badges, stats, response rate, or admin queues.

**Integration checkpoint:** once a contract's owning task's branch is merged into `main`, whoever built against that contract in parallel does a short reconciliation pass — merge `origin/main` into your branch (`git fetch origin && git merge origin/main`), confirm your assumptions matched the contract (they should, since it was locked day one), and note the reconciliation in your own report's Self-Check Confirmation. This is the only point where "another task's file" enters your work — not a blocking wait beforehand.

Every task's report must explicitly confirm which of these shared contracts it built against, and confirm the reconciliation pass happened where applicable.

**Cross-project coordination note (messaging security):** a separate security overhaul elsewhere on the platform publishes its own token-based auth contract (`issue_token`, `verify_token`, `invalidate_all_tokens`, `log_audit`) for its own chat system's endpoints. This project's messaging endpoints (referenced by Task C2 and Task C5 below) are **separate, new endpoints — not the same files** as that other project's ten hardened endpoints. Reuse means applying the same verification pattern to our own endpoints, never assuming those files exist in this codebase or requesting them by name. One semantic difference to build against deliberately: that other system's `principal_id` means "one anonymous chat conversation" — this project's equivalent principal is "one merchant or visitor account" (persistent, not anonymous), so any inherited protection (session tokens, future lock/PIN features) should cover that account's whole inbox as a unit, not scope per conversation thread. If the underlying session/auth tables ever become genuinely shared with that other system rather than each system standing up its own copy, a `principal_type` discriminator column is required first to prevent an account ID and an anonymous chat ID colliding in the same ID space — flag this to the CEO before assuming shared tables, don't default into it.

---

## Universal Requirements (apply to every task below, not restated per-task)

- **Anchor comments required in any shared file:** use `<!-- TASK-reh-[ID] START -->` / `<!-- TASK-reh-[ID] END -->` around any code a task adds to a file another task also touches. Not optional for shared-file tasks (flagged explicitly per task below).
- **No emoji anywhere in any UI output** — badges, labels, and status indicators use SVG/Font Awesome icons only, per platform standard.
- **Every task's report must confirm:** which shared contracts it built against, which shared files it touched (with anchor comments confirmed), that Playwright screenshots were taken demonstrating the feature working, and that `origin/main` was merged before the final push.
- **Version control:** working files use `-v1` suffixes, reference copies get `-original` suffixes before edits, per platform standard.

---

## PHASE A — Core Data Model

## TASK A1 — Merchant pet-posting panel: clone from admin-dashboard.html + add new fields
**Complexity:** Medium | **Branch:** `task/reh-A1`
**Read First:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` in full (primary reference). `adoption-website-project-summary.md` in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** `admin-dashboard.html` is at `adopt/admin-dashboard.html` on `main`. The listing data file and `pet-detail.html` are also in `adopt/` — confirm exact paths against `adoption-website-project-summary.md`. Read them directly from the repo; do not request them from the CEO unless the repo path is unclear.

**This task is a clone-and-modify task, not a new build.** Pet Center's super admin dashboard already has a working "post a pet" panel — that panel's markup, CSS classes, and JS are the starting point for this task, per Rule 7 (never recreate a feature from scratch). Do not design a new form. Copy the admin panel's exact structure for the merchant-facing version, then: (1) adjust labels/permissions/context where the panel currently assumes a super-admin actor instead of a merchant actor, and (2) add the new fields listed below that don't exist in the admin panel yet. If something in the admin panel doesn't make sense in a merchant context (e.g. an admin-only bulk action), leave it out rather than porting it blindly — but the base structure, styling, and interaction pattern come from the existing panel, not from scratch.

**New fields to add** (to the existing pet listing JSON structure — confirm current structure against `adoption-website-project-summary.md` before adding, don't guess the schema):
- `health_status` (string, required, min 20 characters — free text but enforced non-trivial length so merchants can't leave it blank/one-word; label on form: "Health & medical history — vaccinations, known conditions, medications")
- `temperament` (string, required, min 20 characters; label: "Temperament — energy level, behavior around people/other animals/kids")
- `reason_for_rehoming` (string, required, min 15 characters; label: "Why is this pet being rehomed?")
- `spay_neuter_status` (enum: `spayed_neutered` / `not_yet` / `unknown`, required, radio buttons on form)
- `listing_type` (enum: `permanent` / `foster`, required, radio buttons; if `foster` selected, reveal two additional fields: `foster_duration_estimate` (string, e.g. "2-4 weeks") and `foster_covers` (string, what the merchant/org covers vs. what the fosterer covers))
- `currency_code` (enum: `USD`/`CAD`/`GBP`/`AUD`, auto-filled from the merchant's stored country field — not manually chosen by the merchant, to prevent mismatches)
- `is_urgent` (bool, optional, defaults false — a self-declared checkbox: "This is an urgent rehoming situation (eviction, health crisis, deployment, etc.)")

**Validation:** the three required text fields (`health_status`, `temperament`, `reason_for_rehoming`) must reject submission with an inline error if under their minimum length — don't just mark them "required," actually enforce the character minimum, since the entire point is stopping vague one-line listings.

**Display:** these three text fields render as their own labeled sections on the pet-detail page (not buried in a single "description" block) — visually distinct from any existing free-text description field that may already exist, so a visitor can scan health/temperament/reason separately.

**Sizing:** one worker — cloning an existing panel plus adding seven fields is comfortably a 3-day task, likely faster than building fresh since the base structure already exists and is proven.
**File zone:** `admin-dashboard.html`'s pet-posting panel (source, read-only reference — clone from it, don't edit the admin file itself), the new merchant-facing posting page/panel this task creates from that clone, the listing data file, and `pet-detail.html` (display side). A1 owns the real listing schema contract (see SHARED CONTRACTS above) — A2 builds in parallel against the same listing data file, not after it; both reconcile together at the integration checkpoint.
**Shared file note:** the listing data file is shared with A2. Use anchor comments. Check `git diff origin/main...origin/task/reh-A2` before touching the listing data file to see what A2 has changed so far.
**Report must confirm:** which admin panel this was cloned from and what was changed/removed/added versus the original, the exact JSON schema additions, Playwright screenshots of the new form fields with validation triggering on a too-short submission, and confirmation that both A1 and A2's anchor comments are present in the listing data file without conflict.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work with the installed tools before reporting.

---

## TASK A2 — Listing schema expansion: relationships & flags
**Complexity:** Medium-High | **Branch:** `task/reh-A2`
**Read First:** `adoption-website-project-summary.md` in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** the listing data file and browse/search page are in `adopt/` on `main`. Any existing scheduled/cron script — confirm path against `adoption-website-project-summary.md`. Read them directly from the repo.

**Scope:** add `grouped_with` (bonded-pair/sibling grouping) and the automated "Needs Extra Love" flag.

**`grouped_with` implementation:** an array field on each pet record holding the IDs of other pet records it must be placed with (e.g. pet #1042's `grouped_with: [1043]` and #1043's `grouped_with: [1042]` for a bonded pair — bidirectional, both records reference each other). On the posting form, add a "This pet must be adopted with another pet?" toggle; if yes, the merchant selects from their own other active listings (not a free-text ID field — must be selectable from a dropdown of the merchant's own listings to prevent typo'd/invalid IDs). Display: grouped listings show together on the browse page as a single card with a "Bonded pair — must adopt together" label, linking to both individual detail pages.

**`needs_extra_love` flag:** a scheduled check (confirm the platform's cron pattern from `adoption-website-project-summary.md` — reuse it, don't invent a new pattern) runs daily. It sets `needs_extra_love: true` on any active listing whose `last_inquiry_date` is more than 30 days ago (or has never had an inquiry). On the browse page, add a "Needs extra love" badge to those listings' cards (SVG icon only, no emoji) and a "Needs extra love" filter option that, when active, shows only those listings. Badge boost: in the default browse sort order, `needs_extra_love` listings rank above other listings with equal relevance.

**Sizing:** fits in 3 days.
**File zone:** same listing data file as A1 — build in parallel against the SHARED CONTRACTS section above, not after A1 finishes; reconcile both sets of new fields into the one real file at the integration checkpoint. Plus a new scheduled-check script (confirm existing cron pattern, don't invent a new one) and the browse/search page (for the boost logic and badge).
**Shared file note:** listing data file is shared with A1 (and later D1/E2). Use anchor comments. Check `git diff origin/main...origin/task/reh-A1` before touching the listing data file to see what A1 has changed so far.
**Report must confirm:** the scheduled-check script filename and cron setup, Playwright screenshots of a bonded-pair card on browse and the Needs Extra Love badge.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## TASK A3 — Browse, search, and filter
**Complexity:** Medium | **Branch:** `task/reh-A3`
**Read First:** `adoption-website-project-summary.md` in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** the browse/search page is in `adopt/` on `main`. Read it directly from the repo.

**Scope:** browse/search/filter against the listing schema.

**Filter set:**
- Species / breed / age / size
- Listing type (permanent / foster)
- Needs Extra Love (from A2's `needs_extra_love` flag)
- Verified-only toggle (reads the merchant `verification_status` from B2, not listing status — see SHARED CONTRACTS section)
- Urgent-only toggle (reads `is_urgent` from A1's contract)
- Location proximity (city/postcode input with radius slider — free geocoding API, no paid service)

**Default browse view excludes** any listing whose `held_for_review` is true (Task C2's contract) and any listing whose `status` is not `active` (per the canonical status enum in the SHARED CONTRACTS section — `pending_placement`, `placement_complete`, `placement_fell_through`, and `no_longer_available` listings never appear in default browse results).

**Sizing:** fits in 3 days.
**File zone:** browse/search page. Builds against the SHARED CONTRACTS field list (A1 and A2's published contracts) in parallel, using a local stub dataset matching that shape — reconciles with the real listing data once A1 and A2's branches are merged into `main`.
**Report must confirm:** screenshots of each filter working, the held/non-active exclusions confirmed with a test case, and the reconciliation pass against A1 and A2 contracts.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## TASK A4 — Auth prompt: showAuthPrompt()
**Complexity:** Low | **Branch:** `task/reh-A4`
**Read First:** `adoption-website-project-summary.md` in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** any existing modal or overlay pattern in the adopt site — check `adopt/` on `main` and confirm against `adoption-website-project-summary.md`.

**Scope:** the single `showAuthPrompt(contextMessage)` function, locked as the entry point for every access-gated action in this project.

**Contract published now so C1, D1, F1, and F2 can build against it in parallel:** the function signature `showAuthPrompt(contextMessage)` is locked as of this document — those four tasks call it assuming it exists, this task is responsible for making the real implementation match that signature exactly. Reconcile at the integration checkpoint once this task's branch is merged into `main`.

**Exact behavior:** a modal (cloned from the existing modal pattern in `admin-dashboard.html`, per Rule 7) that shows "Sign up or log in to [contextMessage]" (e.g. "message this merchant", "save to favorites") with Sign Up and Log In buttons pointing to the CEO-provided URLs (ask the CEO for those two URLs — not the files, just the URLs). Dismissible with Escape or a close button. No logged-in check is performed by `showAuthPrompt()` itself — callers must check login state first and only call this when the visitor is confirmed logged out.

**Sizing:** small, standalone.
**File zone:** new shared JS file for `showAuthPrompt()`, plus any shared CSS for the modal. No existing page is edited — this task only creates the reusable component.
**Report must confirm:** the exact function signature and what file it lives in (this becomes the contract the other tasks wire into), and a Playwright screenshot of it firing.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## PHASE B — Merchant Verification & Trust Infrastructure

## TASK B1 — Merchant identity verification flow
**Complexity:** Medium | **Branch:** `task/reh-B1`
**Read First:** `adoption-website-project-summary.md` in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** no existing verification page on the site — confirmed. This task creates new standalone page(s). Use `cats.html` (at `adopt/pets/cats/index.html`) as the header/footer/hamburger blueprint per Rule 13.

**Scope:** the merchant identity-verification submission flow.

**Exact behavior:** a merchant reaches this standalone "Verify your account" page via B1's own entry contract (`?verify=true` or equivalent — B1's call on the exact mechanism, documented in the report). Not from any account area, since none exists yet at this point. The merchant submits a government-issued ID (photo upload — one photo per side, or a single document, their call) with a matching live selfie. The uploaded files are emailed directly to the admin (the CEO) using the platform's existing email-sending implementation (per Directive 7 — reuse it, don't build a new one); they are **not** stored in the repo or in any public-facing server path. After upload, show the merchant a "Verification submitted" confirmation and set their `verification_status` to `pending` (SHARED CONTRACTS above).

**Sizing:** fits in 3 days.
**File zone:** new, standalone verification-flow page(s) — not part of any account area, per the note above. Builds fully standalone against B1's own entry contract (see SHARED CONTRACTS above) — never requests or needs the platform's separate account registration/signup pages, which are outside this project's scope and built by someone else entirely.
**Report must confirm:** the exact entry mechanism (the `?verify=true` parameter or equivalent — this is the contract B2 wires its admin approval to), the email delivery confirmed, and the `verification_status: pending` write confirmed.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## TASK B2 — Admin: verification queue + approval/rejection
**Complexity:** Low-Medium | **Branch:** `task/reh-B2`
**Read First:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** `admin-dashboard.html` is at `adopt/admin-dashboard.html` on `main`. Read it directly.

**Scope:** the admin-side verification queue and the charity/rescue badge request type.

**Exact behavior:** a new "Verification Requests" section in the super admin dashboard, listing each pending request as a row: merchant username, submission timestamp, country, and two buttons — Approve / Reject (no raw media shown here — the admin already received it by email per B1). Clicking either button:
- **Approve:** sets `verification_status: verified` on the merchant record (SHARED CONTRACTS above) and emails the merchant a confirmation using the platform's existing email-sending implementation.
- **Reject:** sets `verification_status: rejected` and emails the merchant explaining the rejection (no reason required, just the decision).

**Charity/rescue badge request type (second queue type, same section):** a merchant selects "I'm a registered charity/rescue" at account setup (or later in settings) and submits their registration number + country. This lands in the same B2 admin review queue as identity verification (add as a second request type, not a separate dashboard section) — admin manually looks it up on the correct free registry (IRS Tax Exempt Org Search / CRA Charities List / Charity Commission / ACNC, chosen by the merchant's stated country) and approves/rejects the same way as B2.

**Sizing:** fits in 3 days.
**File zone:** `admin-dashboard.html` (new Verification Requests section, anchor comments required). Shared file — confirm no other active branch edits the same section before starting (check `git branch -r` and diff).
**Report must confirm:** Playwright screenshots of the queue with a pending request, the approve and reject buttons working, the status update to `verified`/`rejected` confirmed, and the email sent.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## TASK B3 — Badge & reputation system: core tiers
**Complexity:** Medium-High | **Branch:** `task/reh-B3`
**Read First:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** the merchant profile data file and any existing scheduled-job file are in `adopt/` on `main`. Confirm exact paths against `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` before requesting anything from the CEO.

**Scope:** the earn/award logic for four distinct badge types (kept separate from display, B4).

**Badge 1 — Activity tier** (`New Merchant` / `Active` / `Trusted`): server-derived, recalculated on a schedule (daily), not manually assigned.
- `New Merchant`: default state, 0 completed placements
- `Active`: 1+ verified-rehome completions (builds against D1's placement-completion contract in parallel — assumes a `placement_complete` event/status exists per the SHARED CONTRACTS section, doesn't wait for D1's real implementation to test this logic)
- `Trusted`: 5+ verified-rehome completions AND response rate ≥ 80% within 48 hours (response-rate data from B4's worker script — see integration note in task B4)

**Badge 2 — Verified** (identity): set directly by B2's admin-approval action. Binary, no tiers.

**Badge 3 — Registered Charity/Rescue**: a merchant selects "I'm a registered charity/rescue" at account setup (or later in settings) and submits their registration number + country. This lands in the same B2 admin review queue as identity verification (add as a second request type, not a separate dashboard section) — admin manually looks it up on the correct free registry (IRS Tax Exempt Org Search / CRA Charities List / Charity Commission / ACNC, chosen by the merchant's stated country) and approves/rejects the same way as B2.

**Badge 4 — Takeback Commitment**: a simple opt-in toggle in merchant settings — no verification needed, self-declared, since it's a commitment the merchant is making publicly (if they claim it falsely, that's a reportable ToS violation, not something requiring proactive verification).

**Sizing:** four distinct badge types with different trigger logic — split from display (B4) to keep this focused on earn/award logic only.
**File zone:** merchant profile data model + badge-calculation logic (scheduled script, same pattern as A2's Needs Extra Love check — confirm if these can share one scheduled-job file rather than two separate cron entries). B4 is expected to hand off its response-rate script to this task's scheduled job — see B4's note.
**Report must confirm:** the exact threshold constants used and where they live in code (so they're easy to find and adjust later without re-reading the whole task), and whether B4's response-rate script was integrated or a stub used (with reconciliation note).
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## TASK B4 — Badge & reputation display
**Complexity:** Medium | **Branch:** `task/reh-B4`
**Read First:** `adoption-website-project-summary.md` in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** `pet-detail.html` is in `adopt/` on `main`. Read it directly. The merchant profile page is Task B6's contract — build against the `<!-- MERCHANT-PROFILE: badges -->` and `<!-- MERCHANT-PROFILE: impact-stats -->` named sections it publishes (see Task B6 below), don't read that page as a separate file until B6's branch is merged into `main`; reconcile into the real page at the integration checkpoint once B6's branch exists.

**Scope:** rendering badges and stats.

**Exact display spec:**
- Merchant profile — badges render into B6's `<!-- MERCHANT-PROFILE: badges -->` section as a row of small icon+label chips (e.g. "[shield icon] Verified", "[trophy icon] Trusted", "[heart-hands icon] Takeback Commitment" — SVG/Font Awesome icons, never emoji, per platform standard) — order of precedence if space is limited: Verified/Charity first, then activity tier, then Takeback Commitment last
- Pet-detail.html: only the merchant's activity tier + Verified badge shown inline near the merchant's name (not the full badge row — keeps listing pages from being cluttered)
- Impact stat block renders into B6's `<!-- MERCHANT-PROFILE: impact-stats -->` section: "X pets rehomed · Y active listings · avg Z days to home" — computed from completed-placement records (D1) and active listing count; shown on profile only, not inline on listings
- Response-rate stat: shown as a plain-language tier label ("Usually replies within a few hours" / "within a day" / "typically slower"), NOT a raw percentage or number. Computed against message timestamps from this project's own messaging system (inbound message → first outbound reply from that merchant, rolling average) — build the computation and storage now against whatever the current message data structure is; if Task C5's endpoint hardening changes that structure, reconcile at that task's integration checkpoint per the SHARED CONTRACTS mechanism, don't block this task waiting on it.

**B4 → B3 integration note:** B4 builds the response-rate computation as a standalone tested script. B3's scheduled job calls it and persists the result onto the merchant record. Note this handoff explicitly in your report so B3's worker picks it up. The response-rate output is a percentage-replied-within-48h figure (for B3's Trusted threshold) and a speed-tier label (for B4's display).

**Listing-page badge data:** the listing stores `merchant_id` and `pet-detail.html` looks up that merchant's current badges from B3's merchant record at render time — never copies tier data into `pet.json` (would go stale on daily recalculation).

**Response-rate slot on profile:** B6's named sections don't include a dedicated slot for the response-rate label. Place it inside `<!-- MERCHANT-PROFILE: impact-stats -->` — document this placement in your report so it doesn't surface as a discrepancy at the integration checkpoint.

**Sizing:** pure display/UI work against data B3 already computes — fits in 3 days.
**File zone:** `pet-detail.html`, plus B6's merchant profile page at the two named sections above (shared file, anchor comments required, reconcile at integration checkpoint — don't wait for B6 to finish before writing this task's own logic).
**Shared file note:** `pet-detail.html` is shared with A1, C1a, C6, and G1. Check all those branches before touching it.
**Report must confirm:** screenshots of the badge row on profile AND the reduced inline version on pet-detail.html, so the difference is visibly confirmed, not just described.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## TASK B5 — Visitor-side trust signal + Adopter Profile
**Complexity:** Medium | **Branch:** `task/reh-B5`
**Read First:** `adoption-website-project-summary.md` in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** visitor account area file(s) and the enquiry/message form — confirm paths in `adopt/` against `adoption-website-project-summary.md` before starting.

**Scope:** reusable Adopter Profile + visitor track record.

**Adopter Profile fields:** household type (apartment/house-with-yard/house-no-yard), other pets in home (yes/no + species if yes), kids in home (yes/no), experience level (first-time/some/experienced), a short free-text "why this pet" field. Filled once in visitor account settings, auto-attached whenever the visitor sends a first enquiry message on any listing — visible only to the specific merchant they've messaged, never public, never visible to other merchants unless that visitor separately messages them too.

**Visitor track record shown alongside it:** count of past completed adoptions (from D1's placement records where this visitor was the adopter) and thumbs received from prior enquiries/reviews (if the reputation system extends that far — confirm scope with B3's reviewer if reviews-of-visitors is in scope; if not yet built, this section shows "New to Pet Center" for first-time visitors rather than a blank/broken field).

**Cross-project reconciliation flag:** a separate feature-addition project's pet-match/request feature built its own "registered visitor" notification icon and matched-listing tagging against a local stub, keyed on visitor email, specifically because no real visitor account system existed at the time — and explicitly flagged that stub as needing reconciliation once a real account system exists. This project's visitor account area (this task) is part of that real system. When both are live, someone needs to do a short reconciliation pass wiring that other project's stub notification/tagging logic to the real account interface here, keyed on the same email field — this doesn't happen automatically just because both projects finish. Flag this to the CEO in your report; this is a CEO-level coordination item to track at that point, not work for this task to perform.

**Sizing:** fits in 3 days — one profile object, one display surface.
**File zone:** visitor account area, merchant-facing enquiry view.
**Report must confirm:** Playwright screenshots of the profile form and the auto-attached profile visible in a merchant's enquiry view.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## TASK B6 — Merchant public profile page: base structure
**Complexity:** Medium | **Branch:** `task/reh-B6`
**Read First:** `adoption-website-project-summary.md` in full. `PETCENTER_DESIGN_SYSTEM_v1.0.md` for the page's styling. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** `cats.html` is at `adopt/pets/cats/index.html` on `main` (canonical blueprint for header/footer/hamburger, per Directive 13). Confirm which existing page shows pet cards in a grid (check `adoption-website-project-summary.md`) — clone its card markup. There is no existing organization, shelter, or profile-style page on this site — confirmed by the CEO — so this page's body is a fresh build; do not ask whether one exists.

**Why this task exists:** Tasks B4 (badge/stat display) and G1 (donation/checkout links) both write content onto "the merchant profile page," and neither builds it — nothing in this document did, until now. This task builds the base page so those two (and B3's review display, if that lands here too) have somewhere real to write into.

**Scope:** the merchant's public profile page — the page a visitor lands on from a merchant's name or listing. No profile-style page exists on the site to clone, so the page body is built fresh; the header, footer, and hamburger come from `cats.html` per Directive 13, the listings grid reuses an existing pet-card component per Rule 7, and all styling follows the design system. Keep it structure-only — this task builds the shell and its own two sections, not the content other tasks write into the rest.

**Section contract, published now so B4 and G1 can build against named sections in parallel without waiting on this task to finish:** the page has these named, anchor-commented sections, each ready for another task to write into:
- `<!-- MERCHANT-PROFILE: header -->` — merchant name, photo, this task's own content
- `<!-- MERCHANT-PROFILE: badges -->` — reserved for Task B4
- `<!-- MERCHANT-PROFILE: impact-stats -->` — reserved for Task B4
- `<!-- MERCHANT-PROFILE: donation-links -->` — reserved for Task G1
- `<!-- MERCHANT-PROFILE: listings-grid -->` — this task's own content (the merchant's active listings, pulled from the listing data model)
- `<!-- MERCHANT-PROFILE: reviews -->` — reserved for Task C6
- `<!-- MERCHANT-PROFILE: actions -->` — reserved for Task C1a (Report button) and Task C6 (thumbs-up/down)

This task builds the page shell and its own two sections (header, listings-grid) with the other sections present as empty, clearly anchor-commented placeholders — it does not need B4 or G1 to exist first, and they don't need to wait for this task to finish before writing their own logic against the published section names above; they reconcile into the real page at the integration checkpoint once this task's branch is merged into `main`.

**Sizing:** fits in 3 days — deliberately scoped as structure-only.
**File zone:** new merchant profile page. Shared with B4 and G1 at the named sections above — anchor comments required.
**Merge-order note:** B4 and G1 should merge `origin/main` into their branches once B6's branch is merged, then reconcile their stub-based code against the real file at that point.
**Report must confirm:** the page with all seven sections present (even the placeholder ones), a note on which existing components (header/footer/hamburger, pet card) were cloned versus built new, and Playwright screenshots confirming all seven anchor comments are in the rendered HTML.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## PHASE C — Trust, Safety & Anti-Fraud

## TASK C1 — Reporting: report modal, welfare routing, admin reports queue (split into C1a and C1b)
**Complexity:** High (split into C1a and C1b) | **Branches:** `task/reh-C1a` and `task/reh-C1b`
**Read First:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` in full. `adoption-website-project-summary.md` in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** `admin-dashboard.html` is at `adopt/admin-dashboard.html` on `main`. `pet-detail.html` is in `adopt/`. There is no existing report modal and no existing reports queue anywhere on the platform — do not look for them. Both workers read these files directly from the repo.

**This is a clone-and-build task.** Neither a report form nor a reports queue exists yet, so there is nothing to modify — but every piece of UI here has an existing pattern in `admin-dashboard.html` (modal, list/table rows, status buttons, notifications) that must be cloned per Rule 7 rather than restyled. Any dropdown uses the design-system dropdown per Directive 9a.

**Access gate:** the Report control requires a logged-in account, per the VISIBILITY & ACCESS MODEL above. A logged-out visitor sees the control but clicking it calls `showAuthPrompt()` (Task A4). (Thumbs-up/down and reviews are a separate task, C6.)

**Both halves build in parallel against the Report record contract in SHARED CONTRACTS — neither waits for the other.**

**C1a — Report modal, welfare routing, and Report button placement**
- A "Report" button on `pet-detail.html` and in the `<!-- MERCHANT-PROFILE: actions -->` section of Task B6's merchant profile page (build against that published section name; shared files, anchor comments required).
- Clicking it opens a modal that first asks "What's this about?" with two choices: "Platform issue (fake listing, scam, inappropriate behavior)" or "I'm concerned about this animal's welfare."
- **Platform issue:** opens a form with a category dropdown (Fake or misleading listing / Suspected scam / Inappropriate behavior / Other) and a description field (minimum 20 characters), and on submit writes a Report record (`source: user_report`, `report_type: platform`). The merchant notification is C1b's job — C1a only writes the record and shows the reporter a confirmation message.
- **Animal welfare concern:** no form. It immediately shows the correct official hotline for the reporter's stated country (RSPCA for the UK and Australia by state, local SPCA/Humane Society for Canada, local Animal Care Services or 911 for the US) with the line "For urgent concerns, contact them directly — we've also logged this report for our records," and writes a Report record with `report_type: welfare` for internal record only.
- If the target merchant has `is_test_account` true, show the normal confirmation but write nothing (per the contract).
- **Sizing:** one modal, one form, one country-lookup, one record write — fits in 3 days.
- **File zone:** `pet-detail.html` and Task B6's merchant profile page (`actions` section) — shared, anchor comments required; a new modal component/file cloned from `admin-dashboard.html`'s modal pattern.
- **Shared file note:** `pet-detail.html` is shared with A1, B4, C6, and G1. Check those branches before touching it.
- **Report (C1a) must confirm:** the welfare hotline actually differs by country (screenshots of a UK case and a US case), and the modal working from both `pet-detail.html` and the profile page.

**C1b — Admin reports queue and merchant response**
- A new "Reports" section in `admin-dashboard.html`, cloning the list-row and status-button pattern already used elsewhere in that dashboard. Rows show source (user report vs system flag), type, target, category, status, and date, with actions to mark a report reviewed, actioned, or dismissed, and a view of `merchant_context` when the merchant has replied. Welfare records appear in a separate "Welfare log (record only)" tab with no triage actions. System flags written by Task C2 appear in the same queue, identified by their `source`. When an admin dismisses a report whose `holds_listing` is true, this task clears `held_for_review` on the target listing (Task C2's contract).
- **Merchant response:** when a platform report is written, the merchant is emailed using the platform's existing email-sending implementation (per Directive 7 — reuse it, don't build a new one). The email says a report was filed regarding their listing/account (never who filed it) and links to a small standalone response page identified by the record's `context_token`; the merchant submits a text response there, which is saved to `merchant_context` and shown to the admin in the queue. The response does not pause or block any admin action. The token's expiry is the worker's call, documented in the report.
- **Sizing:** one admin panel, one email, one small standalone page — fits in 3 days. Builds in parallel with C1a against the Report record contract.
- **File zone:** `admin-dashboard.html` (new Reports section, anchor comments required), a new standalone merchant-response page.
- **Shared file note:** `admin-dashboard.html` is shared with B2, C1b, E1, E3, E5. Check all active branches before touching it.
- **Report (C1b) must confirm:** the queue with a user report, a welfare log entry, and a system flag each present, the dismiss-clears-hold behavior, and the email actually sent.

**Bug/follow-up (both C1a and C1b):** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## TASK C2 — Anti-fraud detection layer
**Complexity:** High | **Branch:** `task/reh-C2`
**Read First:** `adoption-website-project-summary.md` and `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, both in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** the listing-upload handler and this project's own chat message handler (a separate file from any other project's chat-hardening work) are in `adopt/` on `main`. Confirm exact paths against both summaries before starting. `admin-dashboard.html` is for reference only — read it directly.

**Scope:** four independent detectors.

1. **Duplicate-photo detection:** on listing photo upload, detect whether the same photo has already been used on a different merchant account's listing — worker's call on the specific free/open-source technique/library per Directive 11, evaluated for genuine quality (stars, maintenance, community reviews) before use. If a match above a similarity threshold (start at 90%, flag as adjustable) is found on a *different* merchant account, write a Report record (`source: system_flag`, category `duplicate_photo`) for the new listing rather than blocking it outright (same account reusing their own photo across their own listings is normal and fine — only cross-account matches are suspicious).
2. **Posting-velocity flag:** if a merchant account created within the last 7 days posts more than 3 listings within a 24-hour window, write a Report record (`source: system_flag`, category `posting_velocity`, `holds_listing: true`) for each and set `held_for_review` to true on those listings so they don't go live until an admin reviews them.
3. **Off-platform-contact detection:** regex pattern matching on chat messages for phone-number patterns, email address patterns, and common phrases ("text me at," "whatsapp," "call me") — applied only to the first 5 messages of a new conversation thread (after that, contact info sharing for meetup logistics is normal and expected, per the original research). Matches don't block the message, they write a Report record (`source: system_flag`, category `off_platform_contact`) that appears in the reports queue, since blocking legitimate contact-info sharing later in a conversation would be the wrong tradeoff.
4. **Reputation-anomaly detection:** scheduled check (can share the same cron pattern as A2/B3) — if a merchant receives 3+ reviews or reports within a 24-hour window, especially from visitor accounts created within the last 24 hours, write a Report record (`source: system_flag`, category `reputation_anomaly`), reading Report and Review records per the SHARED CONTRACTS section. Does not auto-hide anything, per the original research decision — false positives are costly.

**Sizing:** kept as one task because each detector is small and independent (no shared state between them) — if a worker's own research shows meaningful overlap/complexity, split by detector.
**File zone:** listing-upload handler, chat message handler. Flags are written as Report records (Task C1b's contract) — this task builds no queue UI of its own.
**Report must confirm:** each detector confirmed with a test case, the library choice for duplicate-photo detection with quality evaluation rationale, and the `held_for_review` behavior confirmed.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## TASK C3 — Onboarding safety UX
**Complexity:** Low-Medium | **Branch:** `task/reh-C3`
**Read First:** `adoption-website-project-summary.md` in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** the merchant onboarding flow file is in `adopt/` on `main`. Confirm path against `adoption-website-project-summary.md`. The chat UI and first-contact compose UI are not separate existing files — they're Task C5's new build; see item 3 below.

**Scope:** three small, independent UI prompts.

1. **Merchant Code of Conduct:** a single onboarding screen (or modal) within this project's own merchant onboarding flow — this task builds its own placement for this screen and does not need to see or request any other page's structure to do so — with plain-language bullet points: listings must be accurate and current; no requesting payment before an in-person meeting (unless using the platform's checkout-link feature, G1); be aware of your country's breed/pet laws (note: platform doesn't restrict listings, but merchants remain individually responsible for their own local law compliance); your verification data is used only for account approval and is not stored after review. Single "I agree" checkbox, required before first listing, timestamped and logged to the merchant's account record.
2. **Safe-exchange-zone chat prompt:** a lightweight, dismissible system message that appears in a chat thread once both parties have exchanged 3+ messages AND a message contains meetup-related keywords ("meet," "pick up," "when can I," "address") — text: "Planning to meet in person? Consider a police-monitored Safe Exchange Zone near you, and always meet in daylight." Not repeated more than once per conversation thread.
3. **Gift-adoption prompt:** there is no separate application form in this project — "starting an enquiry" is the first-contact compose UI that opens after a visitor clicks "Message this merchant," which Task C5 is building (its first-enquiry auto-draft feature). This item does not gate anything itself and never calls `showAuthPrompt()` — by the time this compose UI can render at all, the visitor is already logged in, because D1's access gate intercepts the click before the compose UI is ever shown to a logged-out visitor. Build your checkbox into `<!-- MERCHANT-CONTACT: gift-check -->`, a named insertion point Task C5 publishes for this purpose (see C5's scope) — build against that name now, in parallel, and reconcile into the real compose UI once C5's branch is merged into `main`. Checkbox text: "Is this pet intended as a gift for someone else?" — if checked, reveals a required confirmation checkbox: "I confirm [name/relationship] has expressed genuine interest in adopting this pet and will be part of this decision."

**Sizing:** three small, independent UI prompts — bundled deliberately per the "don't over-fragment" side of the rule.
**File zone:** merchant onboarding flow, plus Task C5's compose UI at the `gift-check` named section above (shared file, anchor comments required, build in parallel — don't wait for C5 to finish).
**Report must confirm:** Playwright screenshots of all three prompts (Code of Conduct at onboarding, safe-exchange-zone in chat, gift-adoption checkbox in compose UI), and the timestamp log for the CoC acceptance confirmed.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## TASK C4 — Chat image moderation [GATED]
**Complexity:** Medium | **Branch:** `task/reh-C4`
**Read First:** `adoption-website-project-summary.md`, specifically this project's own messaging/chat documentation. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** this project's own chat upload-handling files — request once Task C5's branch is merged into `main`, since image moderation needs the hardened upload endpoint in place first. Read those files directly from the repo once available.

**Scope:** NSFWJS-based flagging.
**Exact behavior (spec now, build once Task C5's branch is merged):** on chat image upload, screen for explicit content before delivery — worker's call on the specific free/open-source classifier per Directive 11 (NSFWJS is a confirmed-free option worth evaluating). If flagged, the image is held in a "pending review" state (recipient sees "Image pending review" instead of the photo) until an admin manually approves or rejects it from a new moderation queue.
**Sizing:** fits in 3 days on its own, but genuinely gated — **not started until Task C5's branch is confirmed merged into `main`**, since this attaches to that hardened upload path.
**File zone:** this project's own chat message/upload handling (see Task C5) — coordinate sequencing with whoever owns C5.
**Report must confirm:** the specific classifier used and quality evaluation rationale, the "pending review" state shown in a Playwright screenshot, and confirmation that this was built against C5's merged, hardened endpoint (not a prior stub).
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## TASK C5 — Merchant-visitor messaging: build the endpoints, hardened from day one
**Complexity:** High | **Branch:** `task/reh-C5`
**Read First:** `adoption-website-project-summary.md` in full. `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` in full — specifically its live-chat-widget documentation, which is the clone source for this task, not something this task hardens after the fact. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** the core send/poll/thread source files of the existing admin support-chat system are in `adopt/` on `main`. Confirm the real filenames against `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` (e.g. whatever `chat-start.php`/`chat-poll.php`/`chat-send.php` actually are, confirmed against the platform, not guessed). Request the core messaging files only; the video/voice/multi-media/ticket-rating/bot-profile/archive features documented alongside them are explicitly out of scope for this task.

**What this task actually is, and why it changed:** there is no existing merchant↔visitor messaging system on this platform. The only chat system that exists is the admin support-chat (customer ↔ admin), which this task does not touch or extend directly. This task clones that system's core send/poll/thread pattern per Rule 7 to create a new, separate merchant↔visitor messaging system — and builds the token-based access-control pattern (`issue_token` / `verify_token` / `invalidate_all_tokens` / `log_audit`, from the cross-project contract in SHARED CONTRACTS) into these new endpoints from the start, rather than building them first and hardening them in a second pass.

**Scope, deliberately narrower than the admin chat's full feature set:** core message send, poll/receive, conversation-thread listing, and basic single-image attachment per message (Task C4 attaches to this) between one merchant and one visitor — nothing else. Do not clone chunked video upload, MediaRecorder voice messages, multi-media grouped send, the ticket-rating system, or application delete/archive.

**Two features from the original project scope that had no owner until now, included here since they're this same messaging surface:**
- **First-enquiry auto-draft:** when a visitor starts a conversation with a merchant from a listing, the first message is pre-filled with the pet's ID, name, and species (editable before sending, not sent automatically without the visitor seeing it).
- **Named insertion point for Task C3:** the compose UI this task builds includes an anchor-commented placeholder, `<!-- MERCHANT-CONTACT: gift-check -->`, immediately below the message field and above the send button, reserved for Task C3's gift-adoption checkbox. This task leaves it empty; C3 writes into it.
- **Bot-scenario message firing:** Task E1b builds the settings UI where a merchant sets their preferred message per scenario. This task builds the underlying mechanism that actually sends the merchant's configured message when a scenario triggers — publish the exact function/data interface this task exposes (e.g. `getMerchantBotMessage(merchant_id, scenario)`) in your report so E1b's settings UI has something concrete to write into; E1b does not need to wait for this task to finish to build its settings form.

**Principal semantics — build this deliberately, not by default:** this project's "principal" is one merchant or visitor **account** (persistent), not one anonymous conversation. Any protection this task adds covers that account's entire message inbox as a unit — a visitor's tokens aren't scoped to a single conversation thread with one merchant, they cover all of that visitor's active conversations.

**Naming convention:** confirm a `merchant-chat-*` prefix (or equivalent) with the CEO before creating new endpoint files, to keep them clearly distinct from the admin support-chat's files at a glance.

**Sizing:** genuinely substantial — real endpoints built from scratch with auth wired in, plus two smaller features. Flag for a split (e.g. core messaging vs. the two additional features) if the actual scope proves too large once the admin chat's real files are in hand and the clone effort is clearer.
**File zone:** new files for this project's own messaging endpoints — confirm naming convention before creating them.
**Report must confirm:** the real endpoint filenames created (this becomes the contract Task C4 depends on), the token pattern wired into each endpoint, the auto-draft working end to end, and the `getMerchantBotMessage` interface with a test call.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## TASK C6 — Reviews and thumbs: visitor submission, display, admin moderation
**Complexity:** Medium | **Branch:** `task/reh-C6`
**Read First:** `adoption-website-project-summary.md` and `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, both in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** `pet-detail.html` and `admin-dashboard.html` are in `adopt/` on `main`. Ask the CEO directly whether any existing testimonial/review UI exists on the site to clone from. Task B6's merchant profile page is a contract, not a file to request: build against its `reviews` and `actions` sections.

**Why this task exists:** thumbs-up/down and visitor reviews were referenced by several tasks (the access gate, merchant replies in E1b, the profile page's reviews section, anomaly detection in C2) but no task built them.

**Access gate:** thumbs and reviews require a logged-in account — logged-out visitors see the controls and clicking calls `showAuthPrompt()` (Task A4).

**Scope:**
- **Thumbs and review submission:** a visitor can give a merchant one thumbs-up or thumbs-down (changeable later) with an optional written review (10 to 1000 characters if provided). Controls render in the `<!-- MERCHANT-PROFILE: actions -->` section of Task B6's page and on `pet-detail.html`. Writes a Review record per SHARED CONTRACTS.
- **Eligibility:** a visitor can only review a merchant they have exchanged messages with (an enquiry thread exists between them) — this stops drive-by fake reviews. Build against the current message data structure; if Task C5 changes it, reconcile at the integration checkpoint, don't wait.
- **Display:** renders into `<!-- MERCHANT-PROFILE: reviews -->` — thumbs counts plus the list of visible reviews, each showing the merchant's reply beneath it when one exists (the reply itself is written by Task E1b). Reviews with `visibility: hidden` never display and never count.
- **Admin moderation:** a new "Reviews" section in `admin-dashboard.html`, cloning the existing list-row pattern, where an admin can hide or unhide a review (hide only — never delete, and merchants cannot hide or delete their own reviews).
- If the target merchant has `is_test_account` true, show the visitor a normal success message and write nothing (per the contract). Seeded reviews on test accounts are created by Task E5, not here, but must render exactly like any other review.

**Sizing:** one submission control, one display block, one admin list — fits in 3 days.
**File zone:** `pet-detail.html` and Task B6's profile page (`reviews` and `actions` sections — shared, anchor comments required, build in parallel against the published section names), `admin-dashboard.html` (new Reviews section, anchor comments required), the review data store.
**Shared file note:** `pet-detail.html` is shared with A1, B4, C1a, and G1. `admin-dashboard.html` is shared with B2, C1b, E1, E3, E5. Check all active branches before touching either.
**Report must confirm:** a thumbs-up, a thumbs-down, and a written review each submitted and displayed; a hidden review disappearing from display; and the eligibility rule blocking a visitor who never messaged the merchant.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## PHASE D — Post-Placement & Ownership Transfer

## TASK D1 — Placement lifecycle: completion, checklist, waitlist
**Complexity:** Very High | **Branch:** `task/reh-D1`
**Read First:** `adoption-website-project-summary.md` and `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, both in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** the listing/placement data file (e.g. `pet.json`, as a reference shape to extend with the status enum) and `admin-dashboard.html` (clone source for the merchant-side placement/checklist UI) are in `adopt/` on `main`. Read them directly.

**Access gate:** starting an enquiry/application and joining the waitlist both require a logged-in account, per the VISIBILITY & ACCESS MODEL above — a logged-out visitor viewing a listing sees the "Message this merchant" and "Join waitlist" controls, but clicking either calls `showAuthPrompt()` (Task A4) instead of proceeding.

**Visitor notification surface — new, built by this task:** the 7/30-day check-in prompts and the "no longer with adopter" flag need somewhere to render on the visitor side, and nothing on the platform provides that today. Build a small, lightweight in-account notifications list (a simple list of dated items the visitor can see and dismiss) rather than anything elaborate — this is not a chat system and not `showAuthPrompt()`. Publish its structure (the notification record shape, and where in the visitor account area it renders) in your report: a separate feature-addition project's stub is waiting on a real visitor account interface to reconcile a similar notification icon against, and this may end up being the same surface — worth flagging to the CEO once built.

**Scope:** the full listing → placement → check-in → fallback state chain.

**States, explicitly:** `active` → `pending_placement` (merchant marks a specific visitor as the likely adopter, from that visitor's conversation thread) → `placement_complete` (merchant confirms the handoff happened — this is what triggers "verified rehome" and feeds B3/B4's impact stats) → either resolves permanently, or → `placement_fell_through` (merchant marks it explicitly, doesn't just go silent) which triggers the fallback flow below.

**Ownership Transfer Checklist** (shown to both parties on `placement_complete`, informational only, not compliance-enforced): a simple checklist UI with unchecked items — "Update the microchip registry with new owner details," "Update local pet licensing/registration if required in your area," "Hand off vaccination/vet records" (this last one links to the document-upload feature below) — items are self-checked by the user, not verified by the platform, purely a helpful reminder.

**Vet document upload:** a file upload field on the listing (available once `pending_placement` is set), merchant uploads vaccination/vet records, auto-delivered to the adopter once `placement_complete` triggers — worker's call on email attachment (using the platform's existing email-sending implementation, consistent with B1) vs. an in-dashboard download link; confirm preference with the CEO if genuinely ambiguous.

**Post-placement check-ins:** scheduled messages (via the platform's existing email-sending implementation) sent to both parties at 7 days and 30 days after `placement_complete`, asking a simple "How's it going?" with a yes/no "Is the pet still with the adopter?" — a "no" answer flags the placement for admin visibility (doesn't undo the "verified rehome" stat automatically, just flags it for awareness).

**Interest waitlist:** any visitor who messages about a listing that's already `pending_placement` sees an option "Join waitlist" instead of the normal enquiry flow — private list, visible only to that visitor and the merchant, not public and not shown as a count ("3 people waiting") to avoid pressure-tactic gamification. On `placement_fell_through`, the merchant sees a one-click "Notify next waitlisted visitor" button.

**Sizing:** recommend the worker's first deliverable be the state model itself (the `active`/`pending_placement`/`placement_complete`/`placement_fell_through` states and their transitions), with the checklist/upload/check-in/waitlist pieces as natural continuations. Flag for a possible 2-worker split if the state model alone proves substantial once scoped — this is the single most likely task in this entire tracker to blow past 3 days, so treat the "fits in 3 days" call as provisional pending the worker's own early assessment, not a guarantee.
**File zone:** listing/placement data model (shared with A1, A2, E2 — anchor comments required), merchant dashboard (cloned from `admin-dashboard.html`), and a new visitor notification-list component.
**Report must confirm:** the state model with all four states and transitions documented, Playwright screenshots of a placement moving through each state, and the visitor notification record shape published for cross-project reconciliation.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## PHASE E — Merchant Dashboard & Support

## TASK E1 — Merchant dashboard shell: clone from admin-dashboard.html
**Complexity:** High (split into E1a and E1b) | **Branches:** `task/reh-E1a` and `task/reh-E1b`
**Read First:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** `admin-dashboard.html` is at `adopt/admin-dashboard.html` on `main` (the actual file, not just its documentation — this is the clone source, read it directly). Confirm exact paths for its supporting navigation/layout files against `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`.

**This is a clone-and-modify task, not a new dashboard design.** `admin-dashboard.html`'s existing navigation, layout, and panel structure are the direct starting point — per Rule 7 and the BUILD APPROACH section above, copy its exact markup/CSS/JS structure for the merchant dashboard shell, then adjust for a merchant account's permissions and content instead of a super admin's. Do not invent a new dashboard layout or navigation pattern.

**Shell contract, published now so E1a and E1b can build in parallel without waiting on each other:** both workers build against the same known directory structure and route/section names (confirm exact names against `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s file layout before starting) rather than one waiting for the other to finish the shell first — whoever's ready first submits the real shell file with both sections' anchor comments already reserved; the second worker's branch reconciles into it.

**E1a — Listing management + stats panel:** list view of the merchant's own listings with status (active/pending_placement/placement_complete), edit/close actions, a "post new listing" entry point (routes into A1's cloned posting panel), and the merchant's own stats panel (their activity tier, impact numbers, response-rate tier — read-only display of B3/B4's computed data, precision level can be higher here than the public-facing version since this is the merchant viewing their own numbers). Clone the admin dashboard's existing listing-management panel and stats-display components where they already exist, per the same clone-first rule as A1.

**E1b — Inbox + reviews + verification-status panel + bot-message settings + first-message email notification:**
- Message inbox: built against Task C5's new merchant-visitor messaging endpoints (not the admin support-chat, which is a separate, unrelated system) — check `git diff origin/main...origin/task/reh-C5` to see C5's published interface in parallel before C5's branch is merged; reconcile once it is.
- Bot-message-scenario settings: the super admin dashboard already has a settings panel where preferred bot messages are configured per scenario (documented in `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`) — clone that exact settings panel for merchant accounts. Writes into Task C5's `getMerchantBotMessage` interface (build against that published interface in parallel, don't wait for C5 to finish).
- First-time-message email notification: the super admin already receives an email notification when a first-time message comes in (documented in `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`) — reuse that same notification pattern (using this project's existing email-sending implementation, per Directive 7) so merchants get the equivalent notification when a visitor messages them for the first time.
- Reviews section: view all reviews received (Review record per SHARED CONTRACTS, owned by Task C6) and reply once per review by writing `merchant_reply`; no delete button. Does not build review submission or display on the public profile — that is Task C6.
- Verification-status panel: pending/approved/rejected state from B2/B3's charity-registry check.

**Sizing:** split explicitly — E1b picked up two previously-orphaned features (bot-message settings, first-message email notification). Flag for a further split if E1b's combined scope proves too large once actually underway.
**File zone:** `admin-dashboard.html` (clone source, read-only reference), new merchant dashboard directory. E1a and E1b build in parallel against the published shell contract above — reconcile into one real shell file at the integration checkpoint, not by one waiting for the other.
**Merge-order note:** E1a and E1b should each push to their own branch and sync via `origin/main` once the CEO merges whichever lands first. Neither merges the other's branch directly.
**Report must confirm (both):** which admin panels were cloned versus built new, the anchor comments confirming both halves' zones in the shell file, and Playwright screenshots of the sections working.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## TASK E2 — Account lifecycle handling
**Complexity:** Low-Medium | **Branch:** `task/reh-E2`
**Read First:** `adoption-website-project-summary.md` in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** the account management logic file and the listing data file are in `adopt/` on `main`. Confirm paths against `adoption-website-project-summary.md`. Read them directly.

**Scope:** merchant deletion/suspension mid-listing.
**Exact behavior:** on merchant self-deletion or admin suspension, all of that merchant's listings get `status: no_longer_available` — this is an addition to the canonical `status` contract owned by Task D1 (see SHARED CONTRACTS above). E2 does not wait for D1 to finish — E2 builds this logic now, assuming D1's four published status values plus its own `no_longer_available` addition, and reconciles the actual enum with D1's real implementation at the integration checkpoint, using anchor comments in the shared listing data file since both tasks write to the same field. Displays "This listing is no longer available" rather than disappearing, so any bookmarked/shared link doesn't 404. Any visitor with an open chat thread on that merchant's listings receives a one-time system message in that thread: "This merchant's account is no longer active — this conversation is now closed." Suspension (not voluntary deletion) keeps listings in `no_longer_available` but reversible — if C1's dispute path succeeds, admin restores to `active` directly.

**Sizing:** fits in 3 days, one clear behavior change triggered from account status.
**File zone:** account management logic, listing status field (shared with D1 — build in parallel against the published contract, reconcile at integration checkpoint, use anchor comments).
**Report must confirm:** Playwright screenshots of a suspended merchant's listings showing "no longer available," the system message in an open chat thread, and the reconciliation note against D1's status enum.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## TASK E3 — Support ticket system + FAQ
**Complexity:** Low-Medium | **Branch:** `task/reh-E3`
**Read First:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** `admin-dashboard.html` is at `adopt/admin-dashboard.html` on `main`. Confirm where the new support-queue and FAQ page will live by asking the CEO.

**Scope:** "Contact Support" form + FAQ page.
**Exact fields:** category dropdown (Account issue / Verification issue / Technical problem / Other), free-text description, auto-attaches the merchant/visitor's username and account type. Lands in its own admin dashboard queue (separate list from reports and verification, per the original research decision — different triage, help not judgment) — reuse the same list/row UI pattern as B2's verification queue and Task C1b's reports queue (clone from `admin-dashboard.html`'s existing list rows if C1b isn't merged yet — don't wait for it), per Rule 7. FAQ page covers, at minimum: how verification works (from B1/B2), how badges are earned (from B3), how reporting works (from C1) — write actual FAQ copy, don't leave placeholder text.

**Sizing:** fits in 3 days together.
**File zone:** new support page + admin queue (in `admin-dashboard.html` — anchor comments required), new FAQ page.
**Shared file note:** `admin-dashboard.html` is shared with B2, C1b, E1, E5. Check active branches before touching it.
**Report must confirm:** Playwright screenshots of a support ticket submitted and appearing in the admin queue, and the FAQ page with all three required topics covered.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## TASK E4 — Merchant dashboard dark/light mode (reuse pass)
**Complexity:** Low | **Branch:** `task/reh-E4`
**Files needed:** the admin dashboard's dark/light mode toggle task (a separate project's task) must be merged into `main` first — confirm with the CEO whether it is. Once it is: read its `reports/task-[that-project]-[N].md` for the documented localStorage key, root class names, and CSS variable names, and read its actual toggle JS/CSS files from `main` to copy from, per Directive 7. **This is a real, unavoidable dependency — do not start until the CEO confirms that other task's branch is merged.**

**Scope:** copy the admin dashboard's dark/light mode toggle exactly — same localStorage key name, same root-class approach, same CSS custom property names, same three-state Light/Dark/System cycle, same blocking anti-flash script pattern in `<head>`, same `prefers-reduced-motion` handling. Apply it to the merchant dashboard's own shared layout file (from Task E1) using the merchant dashboard's own color values against those same CSS variable names — do not invent parallel variable names or a second mechanism.

**Sizing:** fits comfortably in 3 days — it's a copy-and-reapply task once the source is available.
**File zone:** merchant dashboard shared layout/CSS/JS files (established by Task E1).
**Merge-order note:** wait for the admin dashboard dark-mode task's branch to be merged into `main` before starting. Then also merge E1's branch before starting.
**Report must confirm:** which source task's implementation was copied (branch name, report file), the exact localStorage key and CSS variable names used, and Playwright screenshots of light/dark/system all three states.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## TASK E5 — Super admin test-account panel and whitelist
**Complexity:** Medium | **Branch:** `task/reh-E5`
**Read First:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` in full. `adoption-website-project-summary.md` in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** `admin-dashboard.html` is at `adopt/admin-dashboard.html` on `main` (clone source for the management panel, form, and list patterns). The account record schema is in the repo — confirm path against `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`; read schema only, per the standing rule — never request the signup or login pages.

**Why this task exists:** the CEO needs to create fully working test visitor and merchant accounts for pre-launch testing, with whitelisting and hand-entered reviews. No task in this document built any of it.

**Scope:** a new "Test accounts" section in `admin-dashboard.html`, cloned from an existing management panel's form-and-list pattern.
- **Create:** the admin chooses test visitor or test merchant; the panel generates a unique username and password (shown to the admin once), and writes an account record in the platform's normal account record format with `is_test_account: true`. A test merchant is created with `verification_status: verified` so it can operate immediately. The account must behave exactly like a real one — log in, post listings, message, receive messages. Password storage follows the existing platform standard in the account schema; never keep it in plaintext after it is shown once.
- **Whitelist:** enforced by the other tasks checking the `is_test_account` contract (C1a, C2, C6). This task's job is to set the flag correctly and to confirm in its report that it tested each of those paths once those tasks' branches are merged into `main` — not to wait on them to build.
- **Manual reviews:** a form in this panel (merchant, thumb, review text, reviewer display name, date) and a JSON import in a documented format (the Review record fields per SHARED CONTRACTS, with `is_seeded: true`), writing Review records that the public profile renders like any other.
- **List and delete:** a list of test accounts with a delete action that removes the account and its seeded reviews. These accounts are for pre-launch testing only and are meant to be deleted before real users arrive.

**Sizing:** one admin panel with a create form, a seeding form, a JSON import, and a list — fits in 3 days; flag a split (create/list vs seeding/import) if the JSON import proves larger than expected.
**File zone:** `admin-dashboard.html` (new Test accounts section, anchor comments required), the account record store (schema only, per the request line above).
**Shared file note:** `admin-dashboard.html` is shared with B2, C1b, E1, E3. Check active branches before touching it.
**Report must confirm:** a test visitor and a test merchant created and logged into, a seeded review imported by JSON and displayed, and deletion removing both. Confirm the `is_test_account` whitelist paths for C1a, C2, and C6 (once those branches are merged).
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## PHASE F — Discovery, Engagement & Growth

## TASK F1 — Pet Alerts + favorites
**Complexity:** Medium | **Branch:** `task/reh-F1`
**Read First:** `adoption-website-project-summary.md` in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** the visitor account area file(s) and the listing data file are in `adopt/` on `main`. Confirm paths against `adoption-website-project-summary.md`. Read them directly.

**Access gate:** both Pet Alerts and favorites require a logged-in account, per the VISIBILITY & ACCESS MODEL above — a logged-out visitor sees the "Save search" and bookmark controls, but using either calls `showAuthPrompt()` (Task A4) instead of proceeding.

**Scope:** saved-search notifications + favorites.
**Exact behavior:** visitor saves a search (the same filter set from A3) with a name; a scheduled check (same cron pattern as A2/B3/C2) compares new listings against all saved searches daily and emails a digest of matches using the platform's existing email-sending implementation (not instant per-listing emails — batch daily to avoid spamming). Favorites: a simple bookmark icon on listing cards, saved to the visitor's account, with the listing's current `status` (per the canonical enum in the SHARED CONTRACTS section — `active`, `pending_placement`, `placement_complete`, etc., not a paraphrased shorthand) shown next to each favorited item so a visitor knows if a pet they liked is still available.

**Sizing:** fits in 3 days.
**File zone:** visitor account area, listing data model (read-only here).
**Report must confirm:** a saved search triggering an email with a matching new listing, and a favorited listing showing a status change in the favorites view.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## TASK F2 — Compatibility quiz
**Complexity:** Medium | **Branch:** `task/reh-F2`
**Read First:** `adoption-website-project-summary.md` in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** the listing display page is in `adopt/` on `main`. Confirm where the new quiz page will live by asking the CEO.

**Access gate:** the quiz itself is open to everyone, logged in or not. Saving quiz results to an account, or applying them directly to a specific enquiry, requires login; a logged-out visitor gets the match labels on listings for that session, but attempting to save results calls `showAuthPrompt()` (Task A4) instead.

**Scope:** lifestyle/compatibility scoring against structured listing fields.
**Design (finalized):** a simple weighted-match score, not a black-box algorithm — visitor answers a short quiz (energy level: low/medium/high; home type: apartment/house-with-yard/house-no-yard; experience: first-time/some/experienced; other pets: yes/no; kids in home: yes/no). Each listing's structured fields (temperament, size, energy descriptors from A1) get scored against the visitor's answers on a simple point system — full matches score higher, mismatches on hard constraints (e.g. "not good with kids" vs. visitor has kids) drop a listing's score sharply rather than excluding it outright. Displayed as a "Good match" / "Might work" / "Less likely" label on listings, not a raw numeric score (avoids false precision on an inherently rough signal).

**Sizing:** fits in 3 days now that the scoring approach is decided — one quiz UI + one scoring function against existing fields.
**File zone:** new quiz page/component, listing display (badge/label addition).
**Report must confirm:** Playwright screenshots of the quiz form, a listing showing "Good match," and the auth gate triggering for save-to-account.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## TASK F3 — Social sharing + digest email
**Complexity:** Low-Medium | **Branch:** `task/reh-F3`
**Read First:** `adoption-website-project-summary.md` in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** the listing page and any existing scheduled-job file are in `adopt/` on `main`. Confirm paths before starting.

**Scope:** share links + digest email.
**Exact behavior:** each listing gets a "Share" button generating a direct URL with Open Graph meta tags (pet name, photo, one-line description) so it previews properly on Facebook/etc. when shared — this is standard HTML meta tags, no paid service. Digest email (weekly, via the platform's existing email-sending implementation): compiles new Pet-Alert matches (F1), Needs-Extra-Love listings (A2) in the visitor's saved search area, and one recent verified-rehome story (D1) if available — opt-out link required in every email (basic email compliance, not optional).

**Sizing:** fits in 3 days.
**File zone:** listing page (share links), new scheduled digest-compile script.
**Report must confirm:** Open Graph tags confirmed in page source (screenshot), the digest email sent with an opt-out link, and the opt-out working.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## TASK F4 — First-time-merchant guided listing flow
**Complexity:** Low | **Branch:** `task/reh-F4`
**Read First:** `adoption-website-project-summary.md` in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** the pet-posting form (same file A1 clones and builds) is in `adopt/` on `main`. Read it directly — build in parallel against A1's published field contract, reconcile at the integration checkpoint once A1's branch is merged into `main`.

**Scope:** inline guidance on A1's form.
**Exact behavior:** next to each of A1's required text fields, a small "Why this matters" expandable hint (e.g. next to `reason_for_rehoming`: "Being upfront here builds trust with adopters and helps the right person find your pet"). Photo guidance: minimum 3 photos required to publish (not just recommended), with a hint line "Natural light and eye-level shots get more interest than dark or blurry photos."

**Sizing:** fits in 3 days.
**File zone:** pet-posting form (shared with A1 — builds in parallel against A1's published field contract, reconciles into the real form at integration).
**Merge-order note:** reconcile with A1's branch once it is merged into `main`.
**Report must confirm:** Playwright screenshots of each hint expanding and the 3-photo minimum enforcing on submission.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## TASK F5 — Free resource integrations
**Complexity:** Low | **Branch:** `task/reh-F5`
**Read First:** `adoption-website-project-summary.md` in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** the listing page is in `adopt/` on `main`. Confirm where the new resources page will live by asking the CEO.

**Scope:** three link-outs.
**Exact links, routed by the merchant/visitor's stated country:** spay/neuter — SpayUSA (US), National Desexing Network (Australia), Welfare for Animals directory (Canada), RSPCA/Dogs Trust (UK). Transport — the confirmed networks per country (rescue relay/flight angels for US, Furry Hobos 'N Hiway Heroes for Canada, Nickoftime.org.uk-coordinated networks for UK, Fly4Paws for Australia). Crisis resources page for the Urgent Rehoming flag — links to established domestic-violence, housing/eviction, and military-deployment pet-care resources per country (worker to compile current working links, verifying every link is live at build time — don't hardcode from this document without verification).

**Sizing:** fits comfortably in 3 days.
**File zone:** listing page, new resources page.
**Report must confirm:** all links verified live at build time, Playwright screenshots of the correct country-specific links showing for a US case and a non-US case.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## TASK F6 — Cookie consent banner
**Complexity:** Low | **Branch:** `task/reh-F6`
**Read First:** `adoption-website-project-summary.md` in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** the site-wide include/partial file used across pages is in `adopt/` on `main`. Confirm path against `adoption-website-project-summary.md`.

**Scope:** site-wide cookie consent.
**Exact behavior:** a dismissible banner on first visit — "We use essential cookies to run this site, and optional cookies for analytics. Accept / Reject non-essential" — stores the choice (cookie or localStorage) so it doesn't reappear every visit; essential session/login cookies function regardless of the choice.

**Sizing:** small, standalone.
**File zone:** site-wide include/partial.
**Report must confirm:** Playwright screenshots of the banner appearing, the choice persisting on reload, and essential cookies unaffected by rejection.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## TASK F7 — Currency & locale routing
**Complexity:** Low | **Branch:** `task/reh-F7`
**Read First:** `adoption-website-project-summary.md` in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** whichever rehoming-platform pages exist on `main` at the time this task starts — check `git branch -r` and `git diff origin/main...origin/task/reh-*` to see what's been merged.

**Scope:** currency-code display (A1 dependency).
**Locale/URL routing decision (finalized):** `/en-us/`, `/en-ca/`, `/fr-ca/`, `/en-gb/`, `/en-au/` prefix pattern, matching the platform's existing folder-path deployment convention. This is a routing decision made for this project's own new pages only; a separate project elsewhere on the platform owns the actual translation content/mechanism.

**Sizing:** small — currency display logic + applying the routing pattern to new pages as they're built.
**File zone:** new rehoming-platform pages (routing applied as each is built), currency display component.
**Report must confirm:** the currency display showing correctly for each of the four currency codes on a listing page, and the locale prefix pattern applied to at least one new page.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## PHASE G — Business-Adjacent

## TASK G1 — Donation/wishlist links + checkout-link integration
**Complexity:** Medium | **Branch:** `task/reh-G1`
**Read First:** `adoption-website-project-summary.md` and `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, both in full. `ROADBLOCKS-AND-FIXES.md`. `TODO.md` Table of Contents.
**Files in repo:** `pet-detail.html` is in `adopt/` on `main`. Read it directly. The merchant profile page is Task B6's contract — build against the `<!-- MERCHANT-PROFILE: donation-links -->` named section it publishes (see Task B6 above), don't read that page as a separate file until B6's branch is merged into `main`; reconcile into the real page at the integration checkpoint once B6's branch exists.

**Scope:** optional links + adoption-fee checkout.
**Exact behavior:** the merchant profile settings gets two optional URL fields — "Donation link" and "Amazon wishlist link" — rendered into B6's `<!-- MERCHANT-PROFILE: donation-links -->` section, and, if the merchant opts in per-listing, on that listing too. Separately, adoption-fee checkout: merchant enters a fee amount (in their listing's currency, from A1) and the platform generates a **Stripe Payment Link** (recommended over PayPal for lower standard rates; confirm nonprofit-rate eligibility with CEO before assuming the discounted rate applies) for that specific amount, displayed as a "Pay adoption fee" button on the listing.

**Sizing:** fits in 3 days.
**File zone:** `pet-detail.html`, plus B6's merchant profile page at the named section above (shared file, anchor comments required, reconcile at integration checkpoint — don't wait for B6 to finish before writing this task's own logic).
**Shared file note:** `pet-detail.html` is shared with A1, B4, C1a, and C6. Check active branches before touching it.
**Merge-order note:** reconcile with B6's branch once it is merged into `main`.
**Report must confirm:** Playwright screenshots of the donation/wishlist links rendering on both the listing page and the profile page, and the Stripe Payment Link generating and displaying on a listing.
**Bug/follow-up:** Follow Step 4 of `WORKER-GIT-SYNC-PROTOCOL.md` for anything found outside scope. Test your own work before reporting.

---

## Integration Notes

Every task in this document can start immediately, in parallel — nothing here requires waiting for another task's branch to be fully merged before beginning work, per the SHARED CONTRACTS section above. The only genuine blockers are:

- **Task C4 is gated on Task C5's branch being merged into `main`** (this project's own messaging endpoint hardening), not on any external project — see the Phase C tasks above for why. C4's worker should poll `git branch -r` and check once C5's branch is confirmed merged before starting.
- **Task E4 is gated on the admin dashboard's dark-mode task being merged into `main`** — that is a different project's task; confirm with the CEO. E4 builds from that merged implementation, not from a stub.
- **A separate feature-addition project's pet-match notification/tagging work is stubbed against email, waiting on a real visitor account system to exist.** This project's account area (Task B5) is that system. Flag to the CEO once both are live — see Task B5 for detail.

Every other apparent dependency in this document (A1/A2 same file, A3 needing A1/A2's fields, B3 needing B1/B2's verification outcome, D1/E2 sharing the status field, F4 touching A1's form) is resolved by the SHARED CONTRACTS mechanism: build against the published contract now, reconcile at the integration checkpoint once the owning task's branch is merged into `main`. That reconciliation is a short merge pass, not a rebuild — the contract was locked day one specifically so nobody's 3 days gets consumed waiting.

---

*Task numbering and phase structure are final. Branches are `task/reh-[letter+number]`. Reports are `reports/task-reh-[letter+number].md`. Anchor comments are `TASK-reh-[letter+number] START/END`. Ready for distribution.*
