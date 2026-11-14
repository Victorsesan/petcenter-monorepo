# ADMIN PROJECTS #2, #3, #4 — TASK ASSIGNMENTS
## Voice Transcription · Dark/Light Mode · Chat Language Preference

**Prepared by: Project Lead**
**Sent to: Workers (Claude AI instances)**
**Project slug (branch prefix):** `admin234`
**How work is delivered:** push a branch `task/admin234-[N]` plus a short report at `reports/task-admin234-[N].md` — see the Worker protocol (`WORKER-GIT-SYNC-PROTOCOL.md` at the repo root) for the full delivery steps. Do not write a long completion document with pasted code.

---

## INTRODUCTION

Pet Center is an animal rescue and adoption organization running a
production adoption platform at `adopt.petcenterco.com` alongside an
admin dashboard used to manage pets, applications, and live customer
chat. The admin dashboard's live chat system already supports voice
messages and is built on plain HTML/CSS/JS/PHP with a MySQL-backed chat
layer. Pet Center's own admins speak and write English only — nothing
below should assume otherwise.

This document assigns three separate features that all touch the chat
widget and/or admin dashboard. They are grouped in one place because
they're similar in size and don't collide on files — but they are three
independent deliverables. Read the whole document before starting your
assigned task, since some notes (especially file-request lists and
the shared-file protocol) apply across more than one task.

**Structure note — read this too, not just your task section:** every
task below is followed by its own **Recommended Enhancements**
subsection. That section is not part of your required scope and does
not change what "done" means for your task — it's a headstart of
ideas worth considering, written with more research and cross-project
context than any one worker has time to gather mid-task. Read it after
your task's core instructions, weigh it against what you actually find
once you're in the real files, and use, modify, or discard anything in
it. The warning at the top of each Recommended Enhancements section
applies in full every time — it isn't boilerplate to skim past.

---

## ⚠️ WARNING & INSTRUCTIONS — READ THIS FIRST, EVERY WORD

**This section applies to every task below, for its full duration.
Copied from `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` — that file is the
canonical source; if you see a discrepancy, the repo copy wins.**

1. **Self-question before and after every task.** Before running any
   command, ask yourself whether it's actually correct for the task. After
   running it, check the result — don't assume it worked because it didn't
   error. This applies to every single task, not just complex ones: full
   file linting (balanced tags/braces/parens, no duplicate IDs), checking
   for wrong code placement, and catching bad structure all come from doing
   this consistently. Even after moving past a step, look back at it again
   before finishing — don't treat "I already checked that" as permanent.
2. **Never use emojis anywhere in the page or the code.** Use icons only
   (inline SVG, matching the existing icon style already in use on the
   page you're editing). This applies to visible UI text, code comments,
   console messages — everywhere.
3. **Never create a `.md` or `.txt` file unless explicitly told to.** The
   one `.md` file you do create is your short report at
   `reports/task-admin234-[N].md` — that is required, not optional.
4. **Carry these instructions through every command you run.** Don't let
   them apply only to the first task and quietly drop off later.
5. **Every page is deployed as a folder path, never a bare filename in the
   URL.** On the server, each page is uploaded as its own folder containing
   an `index.html` — e.g. the file worked on as `pet-detail.html` is
   uploaded to the server as `pets/detail/index.html`, producing the clean
   URL `.../pets/detail/`. Every internal link and every URL-building line
   of code must use the clean folder path, never the `.html` filename.
   The only exception is admin-side files that aren't HTML files.
6. **Write code comments.** You must always write code comments on all your
   code to help other AI easily find specific code during debugging, and
   update the code comment after any changes made. Give a name for each
   code block and write its comment.
7. **Never try to recreate features from scratch.** Go to the exact file(s)
   where a feature is documented and copy the exact same code for
   consistency. You can find the relevant files in the project summary.
   Some features have `.php` handlers that work together — understand both
   the documentation and the code.

### Dev Manager Directives

8. **Everything in this Warning & Instructions section is top priority.**
   Save all of it to memory before beginning any task and re-check it
   before finishing.
9. **Consult `PETCENTER_DESIGN_SYSTEM_v1.0.md` before any UI, styling, or
   animation decision.** Use its Table of Contents to jump to the relevant
   section. For colors on admin-dashboard work, use Section 15's
   Navy/Gold/Orange palette — not v2.2's Lavender Mist.
10. **Image/reference-material gate.** If a task says a screenshot or
    reference design is required and it isn't present, stop and wait —
    don't guess. Reference material can be a full HTML page; extract the
    real markup/CSS from its source rather than eyeballing the render.
10a. **Design-fidelity lock.** For any task with a reference image or HTML
    design source, treat it as the literal exact target — no shortcuts, no
    "close enough." Before calling any design task done, use the Playwright
    headless browser tool to load your finished page and visually compare it
    against the reference. This is mandatory, not optional.
11. **Use web search during implementation.** Training data goes stale.
    Search before committing to any third-party tool, library, or API.
12. **Multi-worker, same-file protocol — Sequential File Ownership Chain
    with Anchor Comments.** For any file shared with another task: stay
    inside your named zone, wrap every addition in `<!-- TASK-admin234-N
    START/END -->` anchor comments, pull the most recently merged version
    from `main` (never the original) if you're not first in line, and run a
    final integration/lint pass before marking the file done. Check your
    received files against every other task's file list — if overlap isn't
    flagged here, treat it as shared anyway and note it in your report. This
    also applies across separate trackers: if a file is shared with a
    different project's task, use a distinguishing prefix (see Task 3 below
    for the specific case).
13a. **Never edit `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` or this task
    document directly.** They are read-only reference material.
13b. **Scope Lock — no exceptions.** Touch only the files explicitly in
    your task. Anything outside your scope that needs fixing: verify it
    first — if it's about 10 lines or fewer in one place, no shared
    structure, no clash with another active branch, fix it as a separate
    `TASK-admin234-N-EXTRA` commit and list it in your report; if it's
    bigger, add a `TODO.md` entry instead.
13c. **Second verification round.** If asked to re-check your work and you
    make further changes, add a clearly labeled `## SECOND VERIFICATION
    ROUND` section to your existing report — never a new file. Scope Lock
    still applies in full.
14. **Bug and self-test line (applies to every task):** Follow Step 4 of
    the Worker protocol (`WORKER-GIT-SYNC-PROTOCOL.md`) for anything you
    find outside your scope: verify it first; small fix (about 10 lines or
    fewer, one place, no shared structure, no clash with another active
    branch) → fix it as a separate `TASK-admin234-N-EXTRA` commit; anything
    bigger or uncertain → add a `TODO.md` entry. Test your own work with
    the installed tools (Playwright, structural lint) before you push your
    report.
15. **All work lives in the shared repo.** `git fetch origin` and merge the
    latest `main` before starting. Never force-push, never commit secrets
    (`config.php`, `pet.json`, `applications/`, tokens, passwords). Files
    you need are in the repo — read them from `main` rather than asking the
    CEO to send them, except for anything explicitly marked "not yet in the
    repo" in your task below.
16. **Translation is never your job, and it is never automated.** Every
    translation of chat text is a manual, human-performed step owned
    entirely by a separate, existing project; there is no translation API
    in this pipeline. Never select, build, evaluate, or call a translation
    tool. Where a task produces text that needs translating, present it
    clearly (readable, selectable/copyable) for that manual step.

---

## SHARED DATA CONTRACT — READ THIS IF YOUR TASK IS 1a, 1b, OR 3

Task 3 is the sole owner and creator of the customer's selected
chat language. Task 1a and 1b's transcription features need that
exact value. These tasks don't share a file, so the Sequential File
Ownership Chain (Directive 12) never triggers between them — this
section is what keeps them in sync instead.

- **Canonical field name:** `preferred_chat_language`
- **Format:** an ISO 639-1 two-letter code for languages with no
  variant split (`en`, `es`, `fr`), lowercase. **Confirmed exception —
  Chinese:** use `zh-Hans`, not a bare `zh` — this matches the sitewide
  translation project's confirmed code; do not shorten or normalize it.
- **Default source, in priority order:** check the sitewide Settings
  Widget's `preferences.lang` first — **confirmed storage mechanism:**
  `localStorage`, key `preferences`, JSON field `lang` — read via
  `JSON.parse(localStorage.getItem('preferences')).lang`, guarding for
  the key not existing. Fall back to browser-detected language, then a
  configured default.
- **Task 3** stores and exposes this under the exact name
  `preferred_chat_language` — do not rename it even if a similar-
  sounding column already exists; flag any conflict in your report.
- **Task 1a and 1b** read `preferred_chat_language` to know which
  language the customer expects — per Directive 16, do not translate
  anything yourselves.
- If this contract doesn't fit the actual codebase once files are in
  hand, flag it and ask before proceeding on your own naming.

---

## TABLE OF CONTENTS

- [Shared Data Contract (read if you're on 1a, 1b, or 3)](#shared-data-contract--read-this-if-your-task-is-1a-1b-or-3)
- [Model & Settings Recommendations](#model--settings-recommendations)
- [Task 1a — Transcription Build (Admin → Customer Direction)](#task-1a) *(+ Recommended Enhancements)*
- [Task 1b — Transcription Build (Customer → Admin Direction) & Read-Aloud Playback](#task-1b) *(+ Recommended Enhancements)*
- [Task 2 — Admin Dashboard Dark/Light Mode Toggle](#task-2) *(+ Recommended Enhancements)*
- [Task 3 — Chat Language Preference Selection & Notice](#task-3) *(+ Recommended Enhancements)*

---

## MODEL & SETTINGS RECOMMENDATIONS

Claude Sonnet 5 is the practical ceiling for this project. Extended
thinking on for complex tasks. Web search on throughout. Deliver real
completed work each turn rather than routine check-ins — save check-ins
for genuine blockers.

---

<a name="task-1a"></a>
## TASK 1a — Transcription Build (Admin → Customer Direction)

**Branch:** `task/admin234-1a`
**Report:** `reports/task-admin234-1a.md`
**Complexity: Medium | Files touched: chat widget front-end + its backend handler(s)**
**Shared file:** none with other tasks in this tracker. Check Task 1b's file list once you receive your files — it shares the chat widget front-end and backend with you; if 1b has merged first, pull the latest `main` before starting.

**Read first:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, `ROADBLOCKS-AND-FIXES.md`, `WORKER-GIT-SYNC-PROTOCOL.md`, `TODO.md` (table of contents — handle any entry addressed to you or `ALL`). All at the repo root on `main`.

**Files:** all relevant files are in the repo. Read the chat widget's voice-message front-end and its PHP/backend handler(s) from `main` — the admin summary documents which files hold this logic; read those by name.

**What's required:** Right now, a voice message an admin sends has no
text fallback. Research and select a speech-to-text (transcription)
tool that is actually free — not a paid tier, not a lapsing trial —
and confirm via web search it's still accurate and current today.
Admins speak English only, so English-speech reliability is the
baseline; see the forward-compatibility note below for 1b's needs.

Build the transcription layer for this direction only: an admin sends
a voice message, the customer can click to see a text transcription of
it, along with the customer's `preferred_chat_language` value (Shared
Data Contract above) displayed alongside it so whoever performs the
manual translation step knows which language to target. Per Directive
16, do not translate that text yourself — present it in a clearly
readable, easily selectable/copyable form.

**Show this transcript to the admin too, not just the customer** — the
admin needs the text of what they said to translate accurately. **No
new delivery mechanism is needed:** the admin manually translates and
sends that as a normal follow-up text message through the existing
text-send capability. The click-to-view transcript is the English
fallback before that follow-up arrives, not a feature needing its own
send step.

Reuse the existing voice-message UI and playback pattern documented in
`ADMIN_DASHBOARD_PROJECT_SUMMARY.md` — this adds a transcription layer
on top of what exists, it doesn't replace it.

**Forward-compatibility note for Task 1b:** if the tool you pick is
English-only-optimized, say so clearly in your report so 1b's worker
evaluates multi-language support separately.

**In your report:** document your exact transcription tool/API choice,
its renewal terms (one-time credit vs. recurring monthly free tier —
state which, plainly), and the file(s) and line ranges you changed.

### RECOMMENDED ENHANCEMENTS FOR TASK 1a

⚠️ **Starting-point suggestions only — not instructions, not final
decisions.** You own the actual code and files. Think for yourself,
use web search, and decide what's right based on what you find in the
real codebase. Ignore, modify, or replace anything here that doesn't
hold up once you're looking at the real thing.

- **Low-confidence transcription flag.** If your STT tool returns a
  confidence score, show a small notice when it's low — a garbled
  source transcript produces a garbled manual translation.
- **Cache the transcription per voice message.** Avoid re-running STT
  every time the same message is reopened — saves free-tier usage and
  avoids slightly different transcripts on reruns.
- **Add a "copy transcript" control** next to the displayed text — a
  one-click copy genuinely speeds up whoever is manually translating.
- **Make the transcript-plus-follow-up relationship visually obvious.**
  Consider a small visual link between the voice message and its
  translated follow-up (e.g. a "translated version below" note) so the
  customer doesn't have to guess what a later-arriving text message is.

**Sizing note:** none of the above requires a separate task or delays
day-one work.

---

<a name="task-1b"></a>
## TASK 1b — Transcription Build (Customer → Admin Direction) & Read-Aloud Playback

**Branch:** `task/admin234-1b`
**Report:** `reports/task-admin234-1b.md`
**Complexity: Medium | Files touched: chat widget front-end + backend handler(s) (shared with Task 1a), admin dashboard chat-side counterpart**
**Shared file:** chat widget front-end and backend are shared with Task 1a. Task 1a merges first — wait for `task/admin234-1a` to be merged into `main`, then merge the latest `main` into your branch before starting work on those shared files. The admin dashboard counterpart file is yours alone.
**Merge order:** Task 1a first, then Task 1b.

**Read first:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, `ROADBLOCKS-AND-FIXES.md`, `WORKER-GIT-SYNC-PROTOCOL.md`, `TODO.md` (table of contents — handle any entry addressed to you or `ALL`). Also read `reports/task-admin234-1a.md` from the repo for Task 1a's tool choice and transcript presentation style — match that style rather than inventing a different one. All at the repo root on `main`.

**Files:** read from `main` — the post-1a versions of the chat widget front-end and backend files, plus the admin-dashboard-side chat interface file(s) that render incoming voice messages (which Task 1a does not touch).

**What's required:** Build the reverse direction: a customer sends a
voice message (potentially in any language) and the admin can click to
see a text transcription. If Task 1a's tool doesn't reliably handle
non-English speech, evaluate and select a multi-language-capable
transcription tool for this direction — confirm it's genuinely free and
currently accurate. Note this direction's target language is always
English (admins are English-only) — you do not need
`preferred_chat_language` here. Per Directive 16, present the
transcript clearly for manual translation into English, matching 1a's
style.

**This direction needs no publish/send step.** The admin reads the
transcript, comprehends it in English, then replies through normal
channels — all outside this task's scope. This task stops at showing
a readable transcript; do not build a "mark as translated" state or
any send button tied to the transcript.

Separately, add read-aloud playback via text-to-speech in both
directions on top of whatever text is on-screen. Default to the
browser's native `SpeechSynthesis` API — standard across all major
browsers since 2018, fully offline, no key, no cap. Only use a cloud
TTS free tier as a fallback for a specific language with no usable
on-device voice; say so in your report if you do. Do **not** use the
Web Speech API's *recognition* half for transcription — it is
Chrome/Edge-only, routes audio to Google's servers, and degrades with
accents. Keep the visible transcript alongside the audio controls, not
audio-only.

This task shares the chat widget files with Task 1a. Merge the latest
`main` into your branch first. Wrap all additions in
`<!-- TASK-admin234-1b START -->` / `<!-- TASK-admin234-1b END -->`
anchor comments so they're clearly distinguishable from 1a's. Run a
full lint pass on the shared files before marking done.

### RECOMMENDED ENHANCEMENTS FOR TASK 1b

⚠️ **Starting-point suggestions only — not instructions, not final
decisions.** You own the actual code and files. Think for yourself,
use web search, and decide what's right based on what you find in the
real codebase. Ignore, modify, or replace anything here that doesn't
hold up once you're looking at the real thing.

- **Same low-confidence flag as Task 1a, mirrored** — keep the
  presentation consistent between directions.
- **Same "copy transcript" control as Task 1a** — same reason.
- **Test your multi-language STT tool against real sample audio** in
  languages Pet Center's customers are likely to use, not just English.
  Free-tier multi-language accuracy varies far more than English-only.
- **Since this direction has no publish step, don't build anything that
  implies one.** No "mark as translated" state, no send button tied to
  the transcript — keep it read-only and self-contained.

**Sizing note:** none of the above requires a separate task or delays
day-one work after Task 1a has merged.

---

<a name="task-2"></a>
## TASK 2 — Admin Dashboard Dark/Light Mode Toggle

**Branch:** `task/admin234-2`
**Report:** `reports/task-admin234-2.md`
**Complexity: Medium | Files touched: admin dashboard shared layout/CSS/JS**
**Shared file:** none flagged. Check `git branch -r` for any active branch touching the admin dashboard shared layout before starting.

**Read first:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, `PETCENTER_DESIGN_SYSTEM_v1.0.md` (Section 19 — Dark Mode System specifically), `ROADBLOCKS-AND-FIXES.md`, `WORKER-GIT-SYNC-PROTOCOL.md`, `TODO.md` (table of contents — handle any entry addressed to you or `ALL`). All at the repo root on `main`.

**Files:** the admin dashboard's shared layout/header/shell file(s) and main stylesheet(s) — read from `main`.

**What's required:** Build a dark/light mode toggle for the admin
dashboard interface as a three-state cycle — Light / Dark / System —
not a plain on/off switch. Admin-side only — do not touch or duplicate
any public-facing toggle mechanism.

Implement Light/Dark as a class on the root element (e.g. `dark` on
`<html>`) that JS sets and CSS keys off — not a bare
`prefers-color-scheme` media query, which cannot respond to a manual
choice. Add a small blocking script in `<head>`, before any stylesheet
loads, that reads the saved preference (or falls back to system) and
applies the theme class immediately — prevents a flash of the wrong
theme on every page load. Confirm both themes hit WCAG AA contrast
(4.5:1 text, 3:1 icons/borders) — check the dark palette specifically.
Respect `prefers-reduced-motion` on the toggle's transition animation.

Build the mechanism generic rather than admin-specific, because the
rehoming platform's merchant dashboard is confirmed to copy this later
from a separate file tree. **Name things accordingly:** choose a
localStorage key, root-element class names, and CSS custom property
names that describe the *concept* (e.g. `theme`, `.dark`/`.light`,
`--color-bg`) not this *dashboard* (avoid `admin-theme`,
`--admin-color-bg`). In your report, add a clearly labeled section
listing the exact localStorage key, class names, CSS variable names,
and which file(s) contain the core JS/CSS — the merchant-dashboard
worker copies from this verbatim.

Check `PETCENTER_DESIGN_SYSTEM_v1.0.md` Section 19 before designing
anything from scratch.

### RECOMMENDED ENHANCEMENTS FOR TASK 2

⚠️ **Starting-point suggestions only — not instructions, not final
decisions.** You own the actual code and files. Think for yourself,
use web search, and decide what's right based on what you find in the
real codebase. Ignore, modify, or replace anything here that doesn't
hold up once you're looking at the real thing.

- **Consider building the toggle's core JS/CSS as one self-contained
  module** with no admin-specific dependencies — a single portable file
  is easier for the merchant-dashboard worker to copy than logic spread
  across several interconnected files.
- **A subtle icon transition (e.g. sun/moon swap) on toggle** — small
  polish, entirely optional.
- **Consider whether "System" should re-check the OS preference live**
  via a `matchMedia` change listener if an admin changes their OS theme
  while the dashboard is open — a nice-to-have, not a requirement.

**Sizing note:** none of the above requires a separate task or delays
day-one work.

---

<a name="task-3"></a>
## TASK 3 — Chat Language Preference Selection & Notice

**Branch:** `task/admin234-3`
**Report:** `reports/task-admin234-3.md`
**Complexity: Medium | Files touched: chat widget entry/pre-chat UI, its backend, admin-side chat notification display**
**Shared file:** the options panel file is shared across three tasks from two separate trackers — see Cross-project coordination note below. Merge order: Admin-Project #1's Task 3 (PIN) first, then Admin-Project #1's Task 4 (Lock Toggle) and this task in parallel; whichever of those two finishes second does the final integration/lint pass on the whole file.

**Read first:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, `ROADBLOCKS-AND-FIXES.md`, `WORKER-GIT-SYNC-PROTOCOL.md`, `TODO.md` (table of contents — handle any entry addressed to you or `ALL`). Also check the branch `task/admin1-3` (Admin-Project #1 PIN) and `task/admin1-4` (Admin-Project #1 Lock Toggle) in the repo to understand what's already in the shared options panel file before you touch it. All at the repo root on `main`.

**Files:** read from `main` — the chat widget's pre-chat entry file(s), its backend/session handler that persists chat ID and email, the options panel file in its current real state (with whatever PIN/lock sections Admin-Project #1 has already merged), and the admin-side file(s) that display incoming chat/session metadata to agents.

**What's required:** Before a chat panel opens, the customer selects
their preferred language. Pre-select the picker following the priority
order in the Shared Data Contract above: `preferences.lang` from
`localStorage` first, then browser-detected language, then a
configured default. The customer can still change it manually. List
each language by its own native name ("Español," not "Spanish").

Persist this selection permanently against the customer's chat ID and
email — not just for the current session. Before building the
persistence layer, check the actual code: can a customer start
chatting before an email is captured? If so, add a lightweight interim
signal (cookie or local value, reconciled into the DB record once email
exists) so that early window doesn't silently default to English. If
guests can't chat pre-email, a DB-only save is sufficient — verify
which is actually true and note it in your report.

The admin side must be able to see the customer's selected language.
Store and expose it as `preferred_chat_language` per the Shared Data
Contract — do not rename it.

Immediately after selecting a language, tell the customer they can
change it later from the same options panel that holds PIN and lock
settings, and that a change there also updates what the admin side
sees.

Directly under that notice, display this exact message — use it
verbatim, it is not a draft to rewrite:

> "We always try our absolute best to provide ease of communications to
> our user's of all nationality, however on some occasions during peak
> hours we might have mostly English speaking customer care on standby
> whom might be able to chat with your selected language only through
> text(via our auto-translate) and not verbally."

Per Directive 16, this task only stores and displays the selected
language — it does not build or call a translation tool.

**Cross-project coordination — read this before touching the options
panel file:** the panel has three real claimants across two trackers:
Admin-Project #1's Task 3 (PIN), Task 4 (Lock Toggle), and this task.
Build your section as a self-contained block — own heading, own
anchor-commented block, own settings row — appended to the panel,
never interleaved with or restructuring PIN's or Lock's sections. You
do not need to wait for the PIN/lock branches to merge first; build
against this convention now and reconcile at integration.

**Anchor-comment naming for this specific file only:** Admin-Project
#1 also has a "Task 3" writing into this same file. A bare
`TASK-admin234-3` would be ambiguous. For the options panel file only,
wrap your additions in `<!-- AP4-TASK-3 START -->` /
`<!-- AP4-TASK-3 END -->`. State this in your report and name both of
Admin-Project #1's task numbers explicitly.

**DOM namespace:** prefix any new element IDs/classes you add to this
shared panel with `ap4-` (e.g. `ap4-language-select`) so they can't
silently collide with PIN/lock's own additions.

**Merge order:** if you find yourself finishing after both
Admin-Project #1 tasks have already merged, you are last in line —
pull the latest `main`, which will already contain their sections, and
run the full-file integration/lint pass before pushing. Note which
tasks were already merged in your report.

**Note (not part of this task):** the English-only admin constraint is
Pet Center-specific and must not be assumed for the rehoming platform,
where agents may speak multiple languages.

### RECOMMENDED ENHANCEMENTS FOR TASK 3

⚠️ **Starting-point suggestions only — not instructions, not final
decisions.** You own the actual code and files. Think for yourself,
use web search, and decide what's right based on what you find in the
real codebase. Ignore, modify, or replace anything here that doesn't
hold up once you're looking at the real thing.

- **Show a small "(detected)" indicator** next to a pre-filled language
  if it was auto-set from `preferences.lang` or browser detection —
  helps the customer notice it's a guess they're free to correct.
- **Decide explicitly how to handle a browser reporting `zh-Hant` or a
  bare `zh`** — only `zh-Hans` is supported per the Shared Data
  Contract. Defaulting to `zh-Hans` seems reasonable; state your
  choice in your report rather than deciding it silently.

**Sizing note:** none of the above requires a separate task or delays
day-one work.
