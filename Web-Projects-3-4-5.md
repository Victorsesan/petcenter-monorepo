# WEB-PROJECTS #3, #4, #5 — TASK ASSIGNMENTS

**Prepared by: Project Lead**
**Sent to: Workers (Claude AI instances)**

## INTRODUCTION

This document is prepared by the lead coordinating three related
feature-addition projects for Pet Center, and sent to you as your task
assignment. Pet Center is an animal rescue and adoption organization
running a production adoption platform at `adopt.petcenterco.com`, plus an
admin dashboard for managing pets, applications, and live customer chat.
The site is plain HTML/CSS/JS/PHP, no framework, flat-file JSON storage
aside from the MySQL-backed live chat system.

This document covers three related feature-addition projects:

- **Project 3** — new content pages the site currently lacks
- **Project 4** — location-based pet listings + a worldwide shipping calculator
- **Project 5** — a pet-match/request application (visitor-facing + admin-facing)

Each task below is a complete, standalone assignment. Read the Warning &
Instructions section in full before touching anything — it applies to every
task in this document, no exceptions.

**Every task also has a "Recommended Enhancements" section beneath its main
instructions.** Read that too, not just the task itself — don't stop at the
main assignment and assume you've seen everything relevant to your task.
Each of those sections carries its own warning about how to treat them —
read that warning as well before acting on anything listed there.

**Work is delivered on `Victorsesan/petcenter-monorepo`, not as a pasted-code
completion document.** Every task below now names its own branch and, where
it shares a file with another task, a merge-order note. Get the repo the
same way `WORKER-GIT-SYNC-PROTOCOL.md` (repo root) describes before starting
any task, and follow that document's report format — this tracker still
tells you *what* to build and *where*, the Worker protocol tells you *how*
to deliver it. **Read `ROADBLOCKS-AND-FIXES.md` (repo root) before your
first command too** — every wall already hit setting this up, with the
exact fix, so you don't lose time rediscovering one.

**Branch names are project-scoped: `task/web345-[number]`** (for example
`task/web345-1`, `task/web345-3b`), so this project's branches can never
collide with another VDM's project using the same task numbers. **Task 3's
branch is the one exception — it's `task/3`, not `task/web345-3`**, because
that branch already existed before this scoping rule was introduced and is
explicitly grandfathered (see `TODO.md` #3). Every other task in this
document uses the full scoped name. Wherever a rule below or the Worker
protocol says `task/N`, `reports/task-N.md`, or `TASK-N` anchors, use your
task's actual branch id in place of `N` — for Task 1 that's
`reports/task-web345-1.md` and `TASK-web345-1 START/END`; for Task 3,
because it predates the scoping rule, it stays `reports/task-3.md` and
`TASK-3 START/END`.

## ⚠️ WARNING & INSTRUCTIONS — READ THIS FIRST, EVERY WORD

### Core rules (apply to every task, every file, every page)

1. **Self-question before and after every task.** Before running any
   command, ask yourself whether it's actually correct for the task. After
   running it, check the result — don't assume it worked because it didn't
   error. Even after moving past a step, look back at it again before
   finishing.

2. **Never use emojis anywhere in the page or the code.** Use icons only
   (inline SVG, matching the existing icon style already in use on the page
   you're editing). This applies to visible UI text, code comments,
   commit messages, and branch content alike.

3. **Never create a `.md` or `.txt` file unless explicitly told to.**

4. **Carry these instructions through every command you run.** Don't let
   them apply only to the first task and quietly drop off later.

5. **Every page is deployed as a folder path, never a bare filename in the
   URL.** On the server, each page is uploaded as its own folder containing
   an `index.html` — e.g. the file worked on as `pet-detail.html` is
   uploaded to the server as `pets/detail/index.html`, which is what
   produces the clean URL `https://adopt.petcenterco.com/pets/detail/`
   instead of `.../pet-detail.html`. The file should be named normally
   while working on it (e.g. `pet-detail.html`, `dogs-listing.html`) —
   what matters is that **every internal link and every URL-building line
   of code inside that file** uses the clean folder path, never the
   `.html` filename: `href="/pets/dogs/"`, not `href="/dogs-10.html"`;
   `window.location.href = '/pets/detail/?pet_id=...'`, not
   `'/pet-detail.html?pet_id=...'`. Any new page created must follow this
   exact convention.
   **The only exception is admin-side files that aren't HTML files** —
   this rule applies to HTML pages only. **Note: this rule is about
   deployed behavior only — when referring to or requesting a file, always
   use its real source filename** (e.g. `pet-detail.html`, `pets.html`),
   never the deployed `index.html` name, since multiple source files all
   become `index.html` on the server and requesting by that name is
   ambiguous.

6. **Write code comments.** You must always write code comments on all
   your code to help other AI easily find specific code during debugging,
   and update the code comment after any change made to the code. Give a
   name for each piece of code and write its comment.

7. **Never try to recreate features from scratch.** First features are
   recommended to be used across all pages when needed, and can be
   enhanced if required — e.g. for any image/file uploads, use the
   existing pop-up modal feature, which includes PDF download for filled
   documents. For each feature you want to use, you must not only read its
   documentation, but also go to the exact file(s) it is documented in and
   use the exact same code in your file for consistency. You can always
   find the file(s) a documented feature is said to be in inside your
   project folder. Note that some features have `.php` handlers that work
   together to help the feature function correctly — understand both the
   documentation and the feature code well.

### Directives (apply project-wide, carry equal weight to the rules above)

These directives carry exactly the same weight as rules 1–7 above — not a
secondary or optional layer of guidance. Every rule below must be read and
understood before a worker touches any file, on every single task, for
the full duration of this project.

8. **Everything in this Warning & Instructions section — rules 1 through
14, and every word of it — is top priority.** Re-check it against your own
actions before finishing, the same way rule 1 already asks you to
self-question before and after every command. Nothing here is filler or
background reading.

9. **Consult `PETCENTER_DESIGN_SYSTEM_v1.0.md` before making any UI,
styling, or animation decision.** It is a large, standalone reference
file — use its Table of Contents to jump directly to the section relevant
to your task rather than reading it front to back. One fixed rule applies
across all of it: on this website's adoption-related pages, the header,
footer, hamburger menu, and core color palette documented in
`adoption-website-project-summary.md` remain locked exactly as written —
do not let the design system override them. Everywhere else the design
system offers a free choice, you're free to pick whichever option fits
the page best.

10. **Image/reference-material gate — do not proceed without it.** If a
task says a screenshot or reference image is required and none is
present, stop and wait — don't guess, don't proceed on assumption.
Reference material isn't always an image — sometimes it's a full HTML
page containing the desired design; extract the real markup/CSS/structure
directly from its source either way. Either form is a hard gate on tasks
that call for one.

10a. **Design-fidelity lock — for any task involving a reference image or
HTML design source.** Treat the reference material as the literal, exact
target, not inspiration and not a rough guide. No shortcuts, no
simplified or minimalist substitution for what's shown, no "close
enough." Before calling any design-related task done, use the Playwright
headless browser to actually load your finished page and visually compare
it against the reference — mandatory, not optional. Save the reference
file to your own outputs directory and keep checking against it
throughout the task. This project has already had a real instance of a
worker treating a precise reference image as a loose suggestion and
shipping something far short of what was asked — this rule exists
specifically because of that, and is treated with zero tolerance going
forward.

11. **Use web search during implementation, don't rely only on training
knowledge.** Tool choices, free/no-cost API options, current library
versions, current best practices — search before committing to an
approach, especially for anything involving a third-party service,
library, or API.

12. **Multi-worker, same-file protocol — Sequential File Ownership Chain
with Anchor Comments.** More than one task in this project can land in
the same file, assigned to different workers. Every task that shares a
file with another will say so explicitly:
    - **Named zones.** Stay inside your named zone; don't edit outside it
      even if you notice something else you'd fix.
    - **Anchor comments.** Wrap every addition in a unique, task-numbered
      comment block — `<!-- TASK-N START -->` ... `<!-- TASK-N END -->` —
      so any later reviewer can see at a glance which lines belong to
      which task.
    - **Sequential handoff.** Where a file has more than one task touching
      it, one goes first — on a shared file, that now means merge order:
      see your task's merge-order note.
    - **One final integration/lint pass** on a file once every task
      touching it has landed.

13. **Use `cats.html` as the canonical blueprint for header, footer, and
hamburger menu on any pet-related page your task touches.**

14a. **Never edit `adoption-website-project-summary.md` or
`ADMIN_DASHBOARD_PROJECT_SUMMARY.md` directly, under any circumstance.**
These are read-only reference material. **Work is not delivered as a long
completion file with pasted old/new code.** Everything lives on a shared
git branch everyone can read: your code and comments on your own branch,
plus a **short** `reports/task-[number].md` (about 80 lines, **no pasted
code**) — Task Number, What Was Required, What I Built, Files Changed,
Small Out-of-Scope Fixes Made, Bugs and Follow-ups Sent to `TODO.md`,
Things the Next Person Must Know, Self-Check Confirmation. Every code
block you add or change still carries its task-numbered anchor comment
(`<!-- TASK-N START -->` … `<!-- TASK-N END -->`) and an explanatory
comment, per rule 6, with no exceptions — including trivial one-line
changes. Anything a future reader needs to understand a block (why it's
built that way, a trap, a dependency) is written as a comment next to
that code. Anything someone must act on goes into `TODO.md` at the repo
root. Brand-new files are simply committed on your branch. A task isn't
done until your branch and report are pushed.

14b. **Scope Lock — no exceptions, ever, except the verified small-fix
rule below.** You touch only the files explicitly involved in your task.
If completing your task correctly seems to require a change somewhere
else, you don't make that change yourself, no matter how clearly correct
it seems — **unless it passes the small-fix test**: first verify the
problem is real; the fix is about 10 changed lines or fewer in one place;
it adds no new file and changes no shared structure; and no other active
branch edits the same lines. In that case, fix it as its own separate
commit wrapped in `TASK-N-EXTRA START/END` anchor comments and list it in
your report. Anything bigger, or anything you're unsure about, goes into
`TODO.md` instead (file, approximate line range, branch, how to
reproduce, severity) — list its entry number in your report. If something
outside your scope is genuinely blocking your own task, raise it in
`TODO.md` addressed to your VDM rather than working around it. This rule
holds during a second verification round too.

14c. **Second verification round.** If asked to re-check completed work,
and you make further changes, push them to the *same branch* and add a
short, clearly labeled **`## SECOND VERIFICATION ROUND`** section to your
*existing* `reports/task-[number].md` — never a new file. Keep it short,
no pasted code, same anchor-comment and Scope Lock rules apply.

### Testing — required, not optional

Real, rendered, measured verification before calling anything "done" —
see both project summaries' "Testing tools — required, not optional"
section for the full tool list (Playwright, CDP screenshots, pixel
analysis, route stubbing, structural lint, and more) and how to use each.
Test boundary cases, not just the one case you tried first. If a sandbox
limitation blocks real verification, say so plainly rather than
presenting an untested claim as verified.

> *Adapted for this worker-facing tracker, not a blind copy: "this
> document" in 14a is spelled out as the two named summary docs, since
> copied verbatim it would ambiguously point at this tracker instead.
> Rule 6 is patched in from `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, since
> `adoption-website-project-summary.md`'s own copy is missing it —
> flagged as TODO #2 at the repo root, not something I'm fixing on my
> own authority (Directive 14a). Rule 13's "on any pet-related page" is
> scoped to "your task touches," since the original's "your project"
> was written for the VDM, not a worker. The Testing table itself isn't
> reproduced here since both source documents already carry the full
> version — read theirs rather than a duplicated copy that could drift
> out of sync with it.*

---

## SHARED FILE COORDINATION — READ BEFORE STARTING IF YOUR TASK IS LISTED

Several tasks in this document touch the same underlying files. Rule 12
applies to all of them, but the zones, branches, and merge order are
defined here centrally so no worker has to guess who else is touching
their file. This section is now git-aware: each task also carries its own
**Branch** and, where relevant, **Merge-order note** right in its own
write-up — this section is the consolidated view across all of them, not
a separate source of truth.

**The seven category pages + `pets.html`**
(`cats.html`, `dogs.html`, `birds.html`, `reptiles.html`,
`small-mammals.html`, `livestocks.html`, `aquariums.html`, `pets.html`) are
touched by **five separate tasks**:

| Task | Branch | Named Zone | Dependency |
|---|---|---|---|
| 3 | `task/3` | `FILTER` | None — already assigned |
| 5 | `task/5` | `CTA` | None — runs in parallel with 3, separate zone |
| 3b | `task/3b` | `FILTER` (extends 3's zone) | Hard: `task/3` must merge to `main` first |
| 2 | `task/2` (or split) | `NAV-LINKS` | Soft: after Task 1's completion file |
| 8 | `task/8` | `NAV-LINKS` (extends 2's zone) | Hard: `task/2` must merge to `main` first |

Tasks 3 and 5 sit in genuinely separate zones of the same files and can be
developed and merged independently — git resolves non-overlapping changes
on merge without either worker needing to wait on the other. Whoever
merges second on a file like this should still merge latest `main` into
their own branch before finishing, so they're working from (and testing
against) the other task's already-merged changes, not a stale copy. Tasks
3b and 8 are different: they extend code that doesn't exist until their
predecessor is actually merged into `main`, so those are hard dependencies
with a real wait, not just a courtesy merge.

**The visitor pet-match form** (built in Task 5, branch `task/5`) is
touched by up to four tasks: 5 (`FORM-CORE`), 7a
(`NOTIFICATION-PREFS`, hard dependency on 5), 9 (`SUBMISSION-CHECK`, hard
dependency on 5 *and* 6a), and optionally 5b (`FORM-HARDENING`, hard
dependency on 5, not currently assigned). All three dependents wait for
`task/5` to merge into `main` before starting on this file.

**The admin pet-match listing** (built in Task 6a, branch `task/6a`) is
touched by two tasks: 6a (`LISTING-CORE`) and 6b (`DETAIL-MODAL`, hard
dependency on 6a); 9 also touches it separately in a third zone,
`DUPLICATE-FLAG` (hard dependency on 6a too).

**The shipping calculator page** (built in Task 4, branch `task/4`) is
touched by 4 (`CALCULATOR-CORE`) and 4b (`COMPLIANCE-FLAG`, hard
dependency on 4).

No other files in this document are touched by more than one task.

---

## TABLE OF CONTENTS

- [Task 1 — Project 3: Research, Brainstorm & Finalize New Page List](#task-1)
- [Task 2 — Project 3: Build the New Content Pages](#task-2)
- [Task 3 — Project 4: Location-Based Pet Listings](#task-3)
- [Task 3b — Project 4: Extend Location Filter to Canada, UK & Australia](#task-3b)
- [Task 4 — Project 4: Worldwide Shipping Calculator Page](#task-4)
- [Task 4b — Project 4: Destination Compliance Flag Lookup](#task-4b)
- [Task 5 — Project 5: Pet-Match Request — Naming, Visitor Form & Category CTAs](#task-5)
- [Task 5b (Optional) — Project 5: Visitor Form Hardening](#task-5b)
- [Task 6a — Project 5: Pet-Match Request — Admin Listing & Filtering Structure](#task-6a)
- [Task 6b — Project 5: Pet-Match Request — Admin Detail Modal & Actions](#task-6b)
- [Task 7a — Project 5: Pet-Match Request — Notification Preferences UI & Match Tagging](#task-7a)
- [Task 7b — Project 5: Pet-Match Request — Live-Chat Verification Design](#task-7b)
- [Task 8 — Project 3: All-Category Breed/Species Match Quiz](#task-8)
- [Task 9 — Project 5: Duplicate-Submission Detection](#task-9)

## MODEL & SETTINGS RECOMMENDATIONS

Claude Sonnet 5 (free tier is the practical ceiling for this whole project),
extended thinking on for complex tasks, web search on throughout, and
real completed work per message turn rather than routine check-ins — save
check-ins for genuine blockers (a missing reference image, a genuine scope
ambiguity).

---

<a name="task-1"></a>
## TASK 1 — Project 3: Research, Brainstorm & Finalize New Page List

**Complexity: Medium | Files touched: None (planning/research only, output is a completion file)**
**Branch:** `task/web345-1`
**Bug/follow-up and self-test:** Follow Step 4 of the Worker protocol for anything you find outside your scope: verify it first; small fix (about 10 lines or fewer, one place, no shared structure, no clash with another active branch) → fix it yourself as a separate `TASK-N-EXTRA` commit; anything bigger or uncertain → add an entry to `TODO.md`. Test your own work with the installed tools before you report.
**Read First:** `adoption-website-project-summary.md`, in full
**Files to Request Before Starting:** `PETCENTER_DESIGN_SYSTEM_v1.0.md`. Also check `TODO.md`'s table of contents at the start and before finishing for entries addressed to you.

The site's core adoption-flow pages are already covered. This task is about
the pages a mature, complete pet adoption site should also have but
currently doesn't. Starting examples: a services page, a guides page, a
vets page — but this list is explicitly not exhaustive. Research what
comparable, high-quality pet adoption sites include that this site
currently lacks, cross-checking against `adoption-website-project-summary.md`
so nothing already built gets duplicated.

This is not just a "replicate what competitors have" exercise. Come up with
**at least one genuinely original page idea** — something most other pet
adoption sites don't have — arrived at through your own research and
thinking, not competitor-matching alone.

Produce a final, decided page list (with a one-paragraph purpose statement
per page) and a plan for how each new page links into the site's existing
navigation (header nav, mobile menu, footer, and any relevant in-page CTAs).
Task 2 builds from this list, so be specific about page names and URLs
(using the folder-path convention, per Rule 5).

### Recommended Enhancements

1. **"Sponsor a Pet" program page.** A real, structured version goes
   further than a static donation form: a sponsor picks a specific listed
   pet, gets named credit on that pet's public profile for a set period,
   and gets one email update when that pet's status changes (adopted,
   fostered, transferred). Tying sponsorship to a live pet profile with a
   triggered status email is a genuine differentiator worth considering
   for the "original page idea" this task already requires. The triggered
   email would reuse the existing admin-to-user automation pattern
   documented for Task 7a, so note that dependency if this gets built.
2. **Lost & Found Pets page.** Common at comparable shelter sites,
   distinct purpose from adoption listings, low build complexity.
3. **"Can't Keep Your Pet" / surrender-alternatives page.** Offering
   rehoming resources before intake is a pattern that reduces surrenders
   at comparable organizations. Pairs naturally as a cross-link to Task
   5's pet-match page once both exist — not a build dependency for
   either.
4. **Site-wide structured data as part of the nav plan.** Once new page
   URLs are locked, basic Schema.org `AnimalShelter`/`Organization`
   markup costs nothing extra and helps every new page's search
   visibility at once.

> ⚠️ **These are a headstart, not a final answer.** Treat every item above
> as a starting point, not an instruction to follow as-is. You own the code
> and the files, and you have context these suggestions didn't — think it
> through against the actual task requirements and the real code in front
> of you, and use web search yourself (per Rule 11) to confirm what's
> actually right before building anything from this list.

---

<a name="task-2"></a>
## TASK 2 — Project 3: Build the New Content Pages

**Splitting rule — read before assigning this to anyone:** this task's
page count isn't known until Task 1's completion file delivers the
finalized list, so it cannot be handed to a single worker as written. Once
the list arrives, split it into multiple worker assignments at roughly
**2 pages per worker**, adjusted up or down for how substantial each
individual page turns out to be — a short, simple page and a long,
feature-heavy one shouldn't be weighted the same. If the finalized list
comes back at 2 pages or fewer, one worker assignment is genuinely fine;
don't split for its own sake. Number the resulting assignments 2a, 2b, 2c...
in submission order, each keeping the file-touch scope and read/request
list below, narrowed to that worker's specific pages.

**Complexity: High (per split — see rule above) | Files touched: One new file per page assigned, plus header/footer nav updates on existing pages (see Shared File Coordination)**
**Branch:** `task/web345-2` (or `task/web345-2a`, `task/web345-2b`... per sub-split, once Task 1's list is final)
**Merge-order note:** This task's NAV-LINKS zone on the shared category pages/`pets.html` merges to `main` *before* Task 8's — Task 8 extends the nav links this task adds, so it needs this task's branch merged first, then merges latest `main` into its own branch before finishing.
**Bug/follow-up and self-test:** Follow Step 4 of the Worker protocol for anything you find outside your scope: verify it first; small fix (about 10 lines or fewer, one place, no shared structure, no clash with another active branch) → fix it yourself as a separate `TASK-N-EXTRA` commit; anything bigger or uncertain → add an entry to `TODO.md`. Test your own work with the installed tools before you report.
**Read First:** `adoption-website-project-summary.md`, in full
**Files to Request Before Starting:** The finalized page list and nav plan
from Task 1's completion file; `PETCENTER_DESIGN_SYSTEM_v1.0.md`. Also check `TODO.md`'s table of contents at the start and before finishing for entries addressed to you.

Build the pages from the finalized list. Use `cats.html`'s header, footer,
and hamburger menu as the canonical blueprint (Rule 13) — copy verbatim,
don't reimplement. Follow `PETCENTER_DESIGN_SYSTEM_v1.0.md` for layout,
typography, and color decisions not already locked by Rule 9. Wire in the
navigation changes from Task 1's plan across every page that needs an
updated header/footer/nav link — if that touches a shared file another
task also modifies, follow the Sequential File Ownership Chain
(Rule 12).

### Recommended Enhancements

1. **Per-page structured data + breadcrumb markup**, companion to Task 1's
   structured-data suggestion — cheap to add while building since URLs
   are already folder-path-locked.
2. **Open Graph + Twitter Card meta tags per new page** (title,
   description, image) so shared links preview correctly on social.
3. **A free Lighthouse/axe accessibility + performance pass per finished
   page**, in addition to the Playwright visual-fidelity check Rule 10a
   already requires — visual comparison won't catch missing alt text,
   contrast failures, or heading-order problems.

> ⚠️ **These are a headstart, not a final answer.** Treat every item above
> as a starting point, not an instruction to follow as-is. You own the code
> and the files, and you have context these suggestions didn't — think it
> through against the actual task requirements and the real code in front
> of you, and use web search yourself (per Rule 11) to confirm what's
> actually right before building anything from this list.

---

<a name="task-3"></a>
## TASK 3 — Project 4: Location-Based Pet Listings

**Complexity: High | Files touched: `cats.html`, `dogs.html`, `birds.html`, `reptiles.html`, `small-mammals.html`, `livestocks.html`, `aquariums.html`, `pets.html` (see Shared File Coordination)**
**Branch:** `task/3`
**Merge-order note:** This task's FILTER zone and Task 5's CTA zone are separate zones of the same files, running in parallel — no ordering dependency between them, but whoever finishes second should merge latest `main` into their branch before finishing to pick up the other's changes. Task 3b *does* have a hard dependency: it extends this task's FILTER zone, so it must wait for this branch to merge into `main` first.
**Bug/follow-up and self-test:** Follow Step 4 of the Worker protocol for anything you find outside your scope: verify it first; small fix (about 10 lines or fewer, one place, no shared structure, no clash with another active branch) → fix it yourself as a separate `TASK-N-EXTRA` commit; anything bigger or uncertain → add an entry to `TODO.md`. Test your own work with the installed tools before you report.
**Read First:** `adoption-website-project-summary.md`, in full (Section 15
and Section 17 specifically cover the existing filter system this task
extends)
**Files to Request Before Starting:** None beyond the summary doc — this
task works from the site's existing filter pattern. Also check `TODO.md`'s table of contents at the start and before finishing for entries addressed to you.

The site is explicitly modeled on Petfinder, and one thing it currently
lacks that Petfinder has is location-aware listings. Research how Petfinder
(or a comparable platform, if Petfinder's own method isn't discoverable)
actually implements this. Design an equivalent using only free/no-cost
tools: a manual state-and-city location filter added to the existing
pet-page filter system, letting a visitor see pets located near them, or
within a chosen radius of a location they specify. Reuse the existing
filter UI pattern (Bottom Sheet + Filter Button + Search Bar, Section 11)
rather than building a separate filter mechanism — this is an addition to
that system, not a new one next to it.

### Recommended Enhancements

**This task is already assigned to a worker.** These three items are real
but modest — not enough on their own to justify a separate task, and
Task 3b already reopens this same file in the same filter zone right
after this task completes. If adopted, they belong inside Task 3b's scope
rather than a separate follow-on (see Task 3b's own Recommended
Enhancements below) — bundling avoids a third handoff on the same file
for work this size.

1. **Server-side Haversine distance calculation**, matching the existing
   PHP backend, rather than a client-side JS approximation — keeps
   results consistent regardless of client and avoids shipping the raw
   pet-location dataset to the browser for local filtering.
2. **A "Use my location" button** via the browser's native Geolocation
   API as a one-tap alternative to typing a city/state — keep the typed
   field as fallback for visitors who decline permission.
3. **Cache resolved location lookups** (e.g., in the existing flat-file
   JSON store) so a repeated search for the same location doesn't re-hit
   whatever geocoding service gets chosen every time — worth building in
   from the start given Task 3b will add three more countries on top of
   this same lookup path shortly after.

> ⚠️ **These are a headstart, not a final answer.** Treat every item above
> as a starting point, not an instruction to follow as-is. You own the code
> and the files, and you have context these suggestions didn't — think it
> through against the actual task requirements and the real code in front
> of you, and use web search yourself (per Rule 11) to confirm what's
> actually right before building anything from this list.

---

<a name="task-3b"></a>
## TASK 3b — Project 4: Extend Location Filter to Canada, UK & Australia

**Complexity: Medium | Files touched: The same files from Task 3 (named zone: country-selector and geocoding-lookup logic only; see Shared File Coordination)**
**Branch:** `task/web345-3b`
**Merge-order note:** Hard dependency on Task 3: do not start until `task/3` is merged into `main`. Merge latest `main` into your own branch before starting, since you're extending code that only exists after that merge.
**Bug/follow-up and self-test:** Follow Step 4 of the Worker protocol for anything you find outside your scope: verify it first; small fix (about 10 lines or fewer, one place, no shared structure, no clash with another active branch) → fix it yourself as a separate `TASK-N-EXTRA` commit; anything bigger or uncertain → add an entry to `TODO.md`. Test your own work with the installed tools before you report.
**Read First:** `adoption-website-project-summary.md`, in full
**Files to Request Before Starting:** Task 3's completion file — this
task builds directly on that filter, not a separate one. Also check `TODO.md`'s table of contents at the start and before finishing for entries addressed to you.

Confirmed target markets for this feature are the USA, Canada, UK, and
Australia. Task 3 was scoped and assigned before that was confirmed, so
rather than change that worker's in-progress assignment, this follow-on
task extends their finished filter to the other three countries.

Follow the Sequential File Ownership Chain (Rule 12): pull the most
recently submitted version of the file(s) from Task 3, stay inside the
country-selector/geocoding zone, and wrap your additions in anchor
comments.

Add a country selector to the location filter (defaulting to the visitor's
own country if reasonably inferable, USA otherwise). Per Rule 11, research
and choose a free, no-API-key geocoding solution that covers all four
countries with a single integration, rather than bolting on a different
lookup per country — check whichever service you choose for a usage-rate
policy, and build on top of whatever caching Task 3 already put in
place (or add it here if it didn't) so repeated searches for the same
location don't exceed it. Confirm the existing radius/distance calculation
from Task 3 works unchanged for non-US postal formats before calling
this done.

### Recommended Enhancements

*Also carrying three items from Task 3's own enhancements list, sized too
small to justify their own task and picked up here instead since this
task already reopens the same file/zone: server-side Haversine
calculation, a "Use my location" geolocation button, and caching resolved
lookups — see Task 3's Recommended Enhancements for the full reasoning on
each.*

1. **Postal-code format hints per selected country** (Canada's
   `A1A 1A1`, UK's alphanumeric postcodes, Australia's 4-digit codes) —
   a small input-format hint once a country is picked reduces failed
   lookups from visitors typing in the wrong shape for that country.
2. **Prefill the country selector from a lightweight, no-account signal**
   (e.g., the visitor's browser locale or an approximate IP-based guess)
   rather than always defaulting blind to USA — pairs naturally with
   Task 3's "Use my location" enhancement if that gets built first.

> ⚠️ **These are a headstart, not a final answer.** Treat every item above
> as a starting point, not an instruction to follow as-is. You own the code
> and the files, and you have context these suggestions didn't — think it
> through against the actual task requirements and the real code in front
> of you, and use web search yourself (per Rule 11) to confirm what's
> actually right before building anything from this list.

---

<a name="task-4"></a>
## TASK 4 — Project 4: Worldwide Shipping Calculator Page

**Complexity: High | Files touched: One new page (see Shared File Coordination — Task 4b builds on this file)**
**Branch:** `task/web345-4`
**Merge-order note:** Task 4b extends this task's file in a separate zone (compliance-flag). This task's branch (`task/web345-4`) must merge into `main` before Task 4b starts.
**Bug/follow-up and self-test:** Follow Step 4 of the Worker protocol for anything you find outside your scope: verify it first; small fix (about 10 lines or fewer, one place, no shared structure, no clash with another active branch) → fix it yourself as a separate `TASK-N-EXTRA` commit; anything bigger or uncertain → add an entry to `TODO.md`. Test your own work with the installed tools before you report.
**Read First:** `adoption-website-project-summary.md`, in full
**Files to Request Before Starting:** CEO reference screenshots for this
feature's design, and Pet Center's real shipping policy documents. Also check `TODO.md`'s table of contents at the start and before finishing for entries addressed to you.

**Hard gate — do not begin building until the reference screenshots are in
hand.** Per Rule 10, if this task's reference images aren't present, stop
and wait rather than guessing at the intended design. Once they arrive,
Rule 10a's Design-fidelity lock applies in full: treat them as the literal
target, and use Playwright to visually verify your finished page against
them before calling this done.

Build a new page providing real, credible worldwide pet-shipping cost
estimates, calculated from a Los Angeles, CA origin point (Pet Center's
actual location). Web search is required to research how a worldwide
pet-shipping cost calculator is realistically built and priced for an
LA-based business (carrier options, live-animal shipping regulations,
typical cost drivers — distance, weight/crate size, destination country
restrictions). The page also needs to document Pet Center's real shipping
policies, using the policy documents supplied for this task — incorporate
their actual content, don't paraphrase around gaps.

### Recommended Enhancements

**This task is already assigned to a worker.** Both items below are
copy-only, inside the same calculator-core zone that worker already owns
— not big enough to need any task structure, follow-on or otherwise.
Task 4b's zone is compliance-flag-only, so these don't belong there
either. If wanted, the simplest path is passing them directly to Task 4's
current worker before they submit, rather than routing through any
follow-on task.

1. **Present the cost output as a range with a "final quote requires
   contacting us" disclaimer**, not a single precise dollar figure — real
   pet shipping is quote-based industry-wide, so a tool implying false
   precision would misrepresent pricing to visitors.
2. **Credibility copy point:** LAX (Pet Center's home airport) is a major
   hub for professional live-animal shippers — worth naming in the page's
   copy, trivial to add.

> ⚠️ **These are a headstart, not a final answer.** Treat every item above
> as a starting point, not an instruction to follow as-is. You own the code
> and the files, and you have context these suggestions didn't — think it
> through against the actual task requirements and the real code in front
> of you, and use web search yourself (per Rule 11) to confirm what's
> actually right before building anything from this list.

---

<a name="task-4b"></a>
## TASK 4b — Project 4: Destination Compliance Flag Lookup

**Complexity: High | Files touched: The page from Task 4 (named zone: compliance-flag section only; see Shared File Coordination)**
**Branch:** `task/web345-4b`
**Merge-order note:** Hard dependency on Task 4: do not start until `task/web345-4` is merged into `main`. Merge latest `main` into your own branch before starting.
**Bug/follow-up and self-test:** Follow Step 4 of the Worker protocol for anything you find outside your scope: verify it first; small fix (about 10 lines or fewer, one place, no shared structure, no clash with another active branch) → fix it yourself as a separate `TASK-N-EXTRA` commit; anything bigger or uncertain → add an entry to `TODO.md`. Test your own work with the installed tools before you report.
**Read First:** `adoption-website-project-summary.md`, in full
**Files to Request Before Starting:** Task 4's completion file — this
task adds to that page, not a separate one. Also check `TODO.md`'s table of contents at the start and before finishing for entries addressed to you.

Follow the Sequential File Ownership Chain (Rule 12): pull the most
recently submitted version of the file from Task 4, stay inside the
compliance-flag zone, and wrap your additions in anchor comments.

Full CITES and import-restriction coverage for every country and species
combination is not realistically buildable in this timeframe — scope this
task in two bounded parts instead:

1. **A species-level flag, not tied to any one country.** Research which
   of Pet Center's non-dog/cat categories (birds, reptiles, small mammals)
   are commonly CITES-listed or otherwise subject to export-permit
   requirements, and show a general flag on the calculator when a visitor
   selects one of those categories — regardless of destination.
2. **A country-specific flag for the confirmed target markets only**
   (Canada, UK, Australia) — research each country's actual known
   restrictions (quarantine periods, breed bans, banned species) and
   surface a warning when a visitor selects one of those three as their
   destination. Any other destination country falls back to the general
   "contact us to confirm requirements" language Task 4 already
   established, rather than attempting broader coverage in this task.

This flag warns the visitor before they get a cost estimate for a shipment
that may not actually be legal as described — it augments Task 4's
estimate, it doesn't replace it.

### Recommended Enhancements

1. **Timestamp the compliance data with a "requirements confirmed as of
   [date] — always verify current rules with a licensed pet shipper
   before booking" disclaimer.** Import/export regulations change; a
   dated disclaimer protects Pet Center from stale information being
   read as a guarantee.
2. **Store the flags as a small structured data file** (species × country)
   rather than hardcoded into the page's HTML/copy, so future updates to
   a single country's rules don't require re-touching page code.

> ⚠️ **These are a headstart, not a final answer.** Treat every item above
> as a starting point, not an instruction to follow as-is. You own the code
> and the files, and you have context these suggestions didn't — think it
> through against the actual task requirements and the real code in front
> of you, and use web search yourself (per Rule 11) to confirm what's
> actually right before building anything from this list.

---

<a name="task-5"></a>
## TASK 5 — Project 5: Pet-Match Request — Naming, Visitor Form & Category CTAs

**Complexity: High | Files touched: One new page (see Shared File Coordination — Tasks 7a/9 build on this file), plus `cats.html`, `dogs.html`, `birds.html`, `reptiles.html`, `small-mammals.html`, `livestocks.html`, `aquariums.html` (CTA insert only, see Shared File Coordination)**
**Branch:** `task/web345-5`
**Merge-order note:** This task's CTA zone on the category pages runs in parallel with Task 3's FILTER zone on the same files — no ordering dependency, merge latest `main` before finishing. On the new visitor-form page itself, Tasks 5b, 7a, and 9 all extend this task's file in separate zones and have a hard dependency on this branch merging into `main` first.
**Bug/follow-up and self-test:** Follow Step 4 of the Worker protocol for anything you find outside your scope: verify it first; small fix (about 10 lines or fewer, one place, no shared structure, no clash with another active branch) → fix it yourself as a separate `TASK-N-EXTRA` commit; anything bigger or uncertain → add an entry to `TODO.md`. Test your own work with the installed tools before you report.
**Read First:** `adoption-website-project-summary.md`, in full (Section 5
for the form-field/upload pattern, `pet.json`'s schema). Also check `TODO.md`'s table of contents at the start and before finishing for entries addressed to you.

The internal working terms "pet request," "requested pet," and "requested
applications" are placeholders — deciding on better terminology across the
whole feature (button label, page name, admin-side section name) is part of
this task, not something to leave as-is.

Build a new page where a visitor who can't find what they want in current
listings — or finds current listings too expensive — can describe exactly
the pet they're looking for. The form uses the **same attribute fields as
`pet-detail.html`/`pet.json`**, since this is fundamentally a pet
description submitted in reverse. Add a free-text area for additional
detail, and an optional reference-image upload for a visual example. Reuse
the existing file-upload/preview modal feature (Rule 7) rather than
building a new one.

The copy explaining the optional image section needs original, polished,
professional writing — not a reproduction of any rough draft language —
covering: the image is optional and should only be used if genuinely
helpful; it must not be a pirated/not-owned image (a screenshot rather than
a saved/downloaded copy if the visitor doesn't own the rights); the image
only improves matching accuracy, it doesn't guarantee an exact match; and a
required checkbox confirming the visitor agrees to these terms.

Add a CTA for this feature to all seven pet category pages, using the
terminology decided at the top of this task.

*Note: notification-preference radio buttons belong to this same visitor
form but are scoped to Task 7 — coordinate on the form's markup so
Task 7 can slot its section in without restructuring the page.*

### Recommended Enhancements

**This task is already assigned to a worker.** Real bot-protection
integration plus file validation is genuine engineering, not a quick
addendum, so it isn't listed loosely here — see **Task 5b (Optional)**
below, a standalone follow-on candidate sized appropriately for its own
worker.

> ⚠️ **These are a headstart, not a final answer.** Anything referenced
> above (including Task 5b) is a starting point, not an instruction to
> follow as-is. You own the code and the files, and you have context these
> suggestions didn't — think it through against the actual task
> requirements and the real code in front of you, and use web search
> yourself (per Rule 11) to confirm what's actually right before building
> anything from this list.

---

<a name="task-5b"></a>
## TASK 5b (OPTIONAL) — Project 5: Visitor Form Hardening

**This task is not currently assigned — it exists only as a sized,
ready-to-hand-off candidate if the CEO decides to use it.** It is not an
"adopted" requirement; treat it exactly like the rest of this document's
Recommended Enhancements — a headstart, not a final decision.

**Complexity: Medium | Files touched: The visitor form from Task 5 (named zone: form-hardening only; see Shared File Coordination)**
**Branch:** `task/web345-5b`
**Merge-order note:** Hard dependency on Task 5: do not start until `task/web345-5` is merged into `main`. This task is optional — not currently assigned (see the task write-up below).
**Bug/follow-up and self-test:** Follow Step 4 of the Worker protocol for anything you find outside your scope: verify it first; small fix (about 10 lines or fewer, one place, no shared structure, no clash with another active branch) → fix it yourself as a separate `TASK-N-EXTRA` commit; anything bigger or uncertain → add an entry to `TODO.md`. Test your own work with the installed tools before you report.
**Read First:** `adoption-website-project-summary.md`, in full
**Files to Request Before Starting:** Task 5's completion file — this task
builds on that form, not from scratch. Also check `TODO.md`'s table of contents at the start and before finishing for entries addressed to you.

Follow the Sequential File Ownership Chain (Rule 12): pull the most
recently submitted version of the file from Task 5, stay inside the
form-hardening zone, and wrap additions in anchor comments.

1. **Bot/spam protection on this public form.** Unlike the site's
   existing gated adoption flow, this is a new public-facing form open to
   anyone. Research and choose a lightweight, invisible verification
   approach (per Rule 11) rather than a disruptive CAPTCHA, layered with a
   honeypot field and basic server-side rate limiting.
2. **Server-side max-file-size + MIME-type allowlist on the optional
   reference-image upload** — this form opens the existing upload feature
   to a wider, less-trusted audience than it may have originally been
   built for.

> ⚠️ **This is a headstart, not a final answer.** Research and choose the
> actual implementation yourself (per Rule 11) rather than treating either
> item above as a specification.

---

<a name="task-6a"></a>
## TASK 6a — Project 5: Pet-Match Request — Admin Listing & Filtering Structure

**Complexity: High | Files touched: New admin dashboard section/page(s) (see Shared File Coordination — Tasks 6b/9 build on this file)**
**Branch:** `task/web345-6a`
**Merge-order note:** Tasks 6b and 9 both extend this task's file in separate zones and have a hard dependency on this branch merging into `main` first.
**Bug/follow-up and self-test:** Follow Step 4 of the Worker protocol for anything you find outside your scope: verify it first; small fix (about 10 lines or fewer, one place, no shared structure, no clash with another active branch) → fix it yourself as a separate `TASK-N-EXTRA` commit; anything bigger or uncertain → add an entry to `TODO.md`. Test your own work with the installed tools before you report.
**Read First:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, in full
**Files to Request Before Starting:** The finalized terminology and form
field list from Task 5's completion file. Also check `TODO.md`'s table of contents at the start and before finishing for entries addressed to you.

Build a new admin-dashboard section for managing submitted pet-match
requests, organized into sub-sections by pet category — mirroring the
existing category-based organization already used for published pets.
Each category sub-section needs independent filtering by recency, price,
breed, and whatever else the existing pet-page filter pattern already
supports — reuse that pattern (Rule 7), don't rebuild it.

This task covers the listing structure and filtering only. It does not
cover the per-request detail view or any admin actions on a request — that
is Task 6b, built against the listing structure this task produces.
Stop at a working, filterable list of requests per category; don't build
ahead into the detail modal.

### Recommended Enhancements

1. **Check the existing pet-listing admin view for combined multi-column
   filtering (recency + price + breed at once) before adding anything
   new** — reuse it exactly per Rule 7 if it already supports that. Only
   if it doesn't, a small dependency-free JS add-on (no framework, no
   build step, matching the site's existing stack) is worth considering
   over a heavier library.
2. **Persist each admin's last-used filter settings per category** so
   staff don't re-set filters every session.

> ⚠️ **These are a headstart, not a final answer.** Treat every item above
> as a starting point, not an instruction to follow as-is. You own the code
> and the files, and you have context these suggestions didn't — think it
> through against the actual task requirements and the real code in front
> of you, and use web search yourself (per Rule 11) to confirm what's
> actually right before building anything from this list.

---

<a name="task-6b"></a>
## TASK 6b — Project 5: Pet-Match Request — Admin Detail Modal & Actions

**Complexity: High | Files touched: The admin section built in Task 6a (named zone: detail modal and its trigger only; see Shared File Coordination)**
**Branch:** `task/web345-6b`
**Merge-order note:** Hard dependency on Task 6a: do not start until `task/web345-6a` is merged into `main`. Merge latest `main` into your own branch before starting.
**Bug/follow-up and self-test:** Follow Step 4 of the Worker protocol for anything you find outside your scope: verify it first; small fix (about 10 lines or fewer, one place, no shared structure, no clash with another active branch) → fix it yourself as a separate `TASK-N-EXTRA` commit; anything bigger or uncertain → add an entry to `TODO.md`. Test your own work with the installed tools before you report.
**Read First:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, in full
**Files to Request Before Starting:** Task 6a's completion file (for
the listing structure this modal attaches to), plus the finalized
terminology and form field list from Task 5's completion file. Also check `TODO.md`'s table of contents at the start and before finishing for entries addressed to you.

Follow the Sequential File Ownership Chain (Rule 12): pull the most
recently submitted version of the file(s) from Task 6a, stay inside the
detail-modal zone, and wrap your additions in anchor comments.

Every submitted request needs to show its full detail, including the
visitor's contact info, in a pop-up modal an admin can also take action
from directly. Reuse the admin dashboard's existing account/messaging
modal patterns rather than designing a new one.

### Recommended Enhancements

1. **If building any new modal markup (vs. pure reuse), consider a
   native, browser-built dialog element** instead of a hand-rolled div
   modal — modern browsers handle focus containment, backdrop, and
   Escape-to-close natively. Only relevant if the existing admin modal
   pattern being reused per Rule 7 isn't already built this way; match
   the existing pattern for consistency if it is.
2. **A clear two-step confirm on any reject/remove action** rather than a
   single click — prevents an admin misclick from silently discarding a
   visitor's submission with no undo.
3. **Log which admin took which action, and when**, if
   `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` already documents an
   activity/audit pattern to reuse — check for an existing pattern first
   per Rule 7 rather than inventing new logging.

> ⚠️ **These are a headstart, not a final answer.** Treat every item above
> as a starting point, not an instruction to follow as-is. You own the code
> and the files, and you have context these suggestions didn't — think it
> through against the actual task requirements and the real code in front
> of you, and use web search yourself (per Rule 11) to confirm what's
> actually right before building anything from this list.

---

<a name="task-7a"></a>
## TASK 7a — Project 5: Pet-Match Request — Notification Preferences UI & Match Tagging

**Complexity: High | Files touched: The visitor form from Task 5 (named zone: notification-preference section only), plus registered-user account UI and matched-pet card/detail-page templates**
**Branch:** `task/web345-7a`
**Merge-order note:** Hard dependency on Task 5 for the visitor-form portion: do not touch that file until `task/web345-5` is merged into `main`. The account-UI and matched-pet-card files are not shared with another task in this document.
**Bug/follow-up and self-test:** Follow Step 4 of the Worker protocol for anything you find outside your scope: verify it first; small fix (about 10 lines or fewer, one place, no shared structure, no clash with another active branch) → fix it yourself as a separate `TASK-N-EXTRA` commit; anything bigger or uncertain → add an entry to `TODO.md`. Test your own work with the installed tools before you report.
**Read First:** Both `adoption-website-project-summary.md` and
`ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, in full (the admin-to-user email
automation pattern this task's email path reuses is documented in the
admin summary). Also check `TODO.md`'s table of contents at the start and before finishing for entries addressed to you.

This task shares the visitor form file with Task 5. Follow the
Sequential File Ownership Chain (Rule 12): pull the most recently
submitted version of that file, stay inside the notification-preference
section only, and wrap your additions in anchor comments.

Add radio-button notification preferences to the form: email, phone-text,
phone-call, and live-chat notification. Phone-text and phone-call have no
supporting infrastructure yet — show both grayed out with a small
"*currently unavailable" note. The email path is straightforward: reuse
the site's existing admin-to-user email automation pattern to send the
matching pet's link once an admin marks a match found.

**The live-chat option's radio button belongs here, but do not build its
actual verification/send logic** — that's Task 7b, developed
separately. Wire the button into the form as a selectable, currently
non-functional option; the real send path plugs in once 7b's design is
resolved. This task can proceed independent of how 7b resolves, since the
UI only needs the form structure from Task 5 to be stable, not 7b's
findings.

For registered/signed-in visitors: add a notification icon to their
account interface, and visibly tag their submitted request as matched
(using the terminology from Task 5) wherever the matching pet appears
— its card and its detail page. Reuse the existing live "ADOPTED" badge
logic pattern (same live-fetched data approach, not a separate
implementation) as your model for how this badge should be wired up.

**Account-interface dependency — read before starting this part.** The
site currently has no working visitor account system — `signin/index.html`
is a placeholder, documented as the intended foundation for the Rehoming
Platform's account system, not a working page. The CEO is building the
real signup/account system directly (standard email, username, password,
and name fields) — no worker or task here owns that piece. Build this
part against a local stub instead of an assumed existing page: key it on
**email**, since Task 5's form already collects the visitor's email at
submission — that's the natural match point against a real account once
one exists. Assume a documented, placeholder location for "the account
interface" for now. Treat the real wiring as a reconciliation pass once
the CEO's signup system exists, and flag this explicitly in the completion
file as an unreconciled stub, not a finished integration.

### Recommended Enhancements

1. **If the existing admin-to-user email pattern is near any
   volume/deliverability limit, check current free-tier transactional
   email options before assuming headroom** for this new notification
   type — worth a quick check rather than an assumption either way.
2. **Give the "matched" badge an `aria-live` announcement**, not just a
   visual tag, so screen-reader users in the account/notification area
   actually hear about the match — same live-fetched-data model as the
   "ADOPTED" badge it's built from.
3. **Since the account-interface stub is keyed on email**, the same key
   naturally supports backfilling a match for a visitor who submits a
   request anonymously and only creates an account afterward — worth
   keeping in mind while designing the stub, even though the real
   reconciliation itself waits on the CEO's signup system.

> ⚠️ **These are a headstart, not a final answer.** Treat every item above
> as a starting point, not an instruction to follow as-is. You own the code
> and the files, and you have context these suggestions didn't — think it
> through against the actual task requirements and the real code in front
> of you, and use web search yourself (per Rule 11) to confirm what's
> actually right before building anything from this list.

---

<a name="task-7b"></a>
## TASK 7b — Project 5: Pet-Match Request — Live-Chat Verification Design

**Complexity: High (direction is confirmed — this task works out the concrete design, not an open-ended search for one) | Files touched: None until the design is confirmed as buildable; this task's output is a specification in its completion file, not shipped code**
**Branch:** `task/web345-7b`
**Bug/follow-up and self-test:** Follow Step 4 of the Worker protocol for anything you find outside your scope: verify it first; small fix (about 10 lines or fewer, one place, no shared structure, no clash with another active branch) → fix it yourself as a separate `TASK-N-EXTRA` commit; anything bigger or uncertain → add an entry to `TODO.md`. Test your own work with the installed tools before you report.
**Read First:** Both `adoption-website-project-summary.md` and
`ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, in full (the live-chat system's
existing architecture is what this design has to work within). Also check `TODO.md`'s table of contents at the start and before finishing for entries addressed to you.

This is a standalone design task — do not bundle it with the lighter
notification-UI work in Task 7a, and do not let it block 7a's start or
delivery. It requires a visitor to already have an existing chat ID, and
sending an automated match notification into the wrong person's chat
thread would itself be a privacy failure — so rather than try to verify
ownership of an old chat ID, the confirmed direction is to route around
that problem entirely: regardless of which notification preference the
visitor selected, the actual send path for a match is always a single-use,
short-lived (roughly 15–30 minute) link emailed to the address the visitor
originally submitted with their request. Clicking it authenticates a
*fresh* chat session pre-filled with the match context, rather than
re-entering an old thread.

Work out the concrete, implementation-ready specification for this: token
generation and format, TTL, single-use invalidation immediately after
first click, rate-limiting on link issuance (so the mechanism can't itself
become a spam vector against the visitor's inbox), and the handoff flow
that opens the new chat session pre-filled with the pet-match context once
the link is clicked.

**Document, but do not attempt to build, a "return to the visitor's actual
existing chat thread" alternative.** That would require binding chat IDs
to a verified email at chat-*creation* time — a change to the live-chat
system itself, bigger than this task's scope — so note it in the
completion file as an open question for a possible future project, not
something this task resolves or implements.

Once this design is confirmed as buildable, the actual send-logic
implementation is a follow-on task, not part of this one.

**Scope boundary — read if another team references this task as a
dependency:** this task is a narrow identity-verification fix for one
notification path. It does not touch the chat system's image-upload
pipeline, message-timestamp reliability, or any broader chat
infrastructure. If another project is waiting on "a messaging/security
overhaul" before starting chat-image-moderation or chat-reliability work,
that dependency is not this task — flag the mismatch rather than trying to
stretch this design to cover it.

### Recommended Enhancements

No open items here — the original research candidates for this task (the
magic-link direction, issuance rate-limiting, and treating "reopen an
existing thread" as an open question rather than a build target) were
already folded into the task instructions above once the CEO confirmed the
direction, rather than left as separate suggestions. Nothing further to
flag as optional at this time.

---

<a name="task-8"></a>
## TASK 8 — Project 3: All-Category Breed/Species Match Quiz

**Complexity: High | Files touched: One new page**
**Branch:** `task/web345-8`
**Merge-order note:** Hard dependency on Task 2: the quiz's nav link extends Task 2's NAV-LINKS zone, so do not touch the shared category-page files until `task/web345-2` (or its relevant split) is merged into `main`. The new quiz page file itself has no dependency.
**Bug/follow-up and self-test:** Follow Step 4 of the Worker protocol for anything you find outside your scope: verify it first; small fix (about 10 lines or fewer, one place, no shared structure, no clash with another active branch) → fix it yourself as a separate `TASK-N-EXTRA` commit; anything bigger or uncertain → add an entry to `TODO.md`. Test your own work with the installed tools before you report.
**Read First:** `adoption-website-project-summary.md`, in full
(`pet.json`'s schema and Section 11's filter attributes are what this
quiz's questions and matching logic are built from)
**Files to Request Before Starting:** Task 1's completion file (the
finalized page list and nav plan this quiz page slots into). Also check `TODO.md`'s table of contents at the start and before finishing for entries addressed to you.

Petfinder's version of this feature only covers dogs. Build one that spans
all seven of Pet Center's categories: a short series of questions (living
situation, activity level, experience level, other pets/kids in the home,
and category-relevant attributes drawn from `pet.json`) that narrows down
to a shortlist of currently-listed pets matching the visitor's answers.

Use `cats.html`'s header, footer, and hamburger menu as the canonical
blueprint (Rule 13). Follow `PETCENTER_DESIGN_SYSTEM_v1.0.md` for layout
and styling decisions. The matching logic should query the same live pet
data the category pages already use — don't build a separate or stale
copy of it. Wire this page into the navigation per Task 1's plan; if
that touches a shared file another task also modifies, follow the
Sequential File Ownership Chain (Rule 12).

### Recommended Enhancements

1. **Let a visitor retake or adjust individual answers** rather than only
   offering a full restart — small UX addition, meaningfully reduces
   friction if their first answer set returns few or no matches.
2. **Show a graceful "no strong matches" state** (rather than an empty
   results grid) that suggests loosening a specific filter, when the
   quiz's answers narrow results to zero or very few pets — worth
   designing deliberately rather than leaving as a default empty state.

> ⚠️ **These are a headstart, not a final answer.** Treat every item above
> as a starting point, not an instruction to follow as-is. You own the code
> and the files, and you have context these suggestions didn't — think it
> through against the actual task requirements and the real code in front
> of you, and use web search yourself (per Rule 11) to confirm what's
> actually right before building anything from this list.

---

<a name="task-9"></a>
## TASK 9 — Project 5: Duplicate-Submission Detection

**Complexity: Medium | Files touched: The visitor form from Task 5 (named zone: submission-check logic only) and the admin listing from Task 6a (named zone: duplicate-flag indicator only; see Shared File Coordination)**
**Branch:** `task/web345-9`
**Merge-order note:** Hard dependency on both Task 5 and Task 6a: do not start until `task/web345-5` *and* `task/web345-6a` are both merged into `main`. Merge latest `main` into your own branch before starting.
**Bug/follow-up and self-test:** Follow Step 4 of the Worker protocol for anything you find outside your scope: verify it first; small fix (about 10 lines or fewer, one place, no shared structure, no clash with another active branch) → fix it yourself as a separate `TASK-N-EXTRA` commit; anything bigger or uncertain → add an entry to `TODO.md`. Test your own work with the installed tools before you report.
**Read First:** Both `adoption-website-project-summary.md` and
`ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, in full
**Files to Request Before Starting:** Task 5's completion file and
Task 6a's completion file — this task builds on both, not from
scratch. Also check `TODO.md`'s table of contents at the start and before finishing for entries addressed to you.

Follow the Sequential File Ownership Chain (Rule 12) on both files
independently: pull the most recently submitted version of each, stay
inside your named zone in each, and wrap your additions in anchor
comments.

When a new pet-match request is submitted, check for an existing
unresolved request from the same visitor (matching submitted email, and a
similar description submitted within a short recent window) rather than
silently accepting an unlimited number of near-duplicate submissions from
the same person. Don't block the submission — flag it. Surface that flag
as a small indicator on the matching request(s) in Task 6a's admin
listing, so staff can see at a glance that a visitor has submitted more
than once, without having to cross-reference manually.

### Recommended Enhancements

1. **Make the duplicate-detection window a configurable constant** (e.g.,
   a number of days set in one place) rather than a hardcoded value
   buried in logic, so staff can tune sensitivity later without a code
   change.
2. **Normalize email before matching** (lowercase, trim whitespace) — a
   small thing, but casing/whitespace differences would otherwise cause
   real duplicates to be missed.

> ⚠️ **These are a headstart, not a final answer.** Treat every item above
> as a starting point, not an instruction to follow as-is. You own the code
> and the files, and you have context these suggestions didn't — think it
> through against the actual task requirements and the real code in front
> of you, and use web search yourself (per Rule 11) to confirm what's
> actually right before building anything from this list.

---
