# Pet Center Adoption System — Project Summary

## Table of Contents

- [Features](#features)
- [About This Project](#about-this-project)
- [⚠️ Warning & Instructions — Read Before Editing Anything](#-warning-instructions-read-before-editing-anything)
- [Instructions for AIs contributing to this document](#instructions-for-ais-contributing-to-this-document)
- [1. Server Folder Structure](#1-server-folder-structure)
- [2. Hamburger Menu — Mandatory Implementation for All Pages](#2-hamburger-menu-mandatory-implementation-for-all-pages)
- [3. Files Covered In This Document](#3-files-covered-in-this-document)
- [4. Adoption Code — the system's shared key](#4-adoption-code-the-systems-shared-key)
- [5. `adoption-application.html` — Application Form](#5-adoption-applicationhtml-application-form)
- [6. `adoption-application.php` — Backend](#6-adoption-applicationphp-backend)
- [7. `adoptions/status/index.html` — Status Dashboard](#7-adoptionsstatusindexhtml-status-dashboard)
- [8. Shared Design System (all 4 files)](#8-shared-design-system-all-4-files)
- [9. Quick Reference: "Where do I fix X?"](#9-quick-reference-where-do-i-fix-x)
- [10. `testimonials/index.html` — Reviews Page](#10-testimonialsindexhtml-reviews-page)
- [11. Reusable Feature: Bottom Sheet + Filter Button + Search Bar](#11-reusable-feature-bottom-sheet-filter-button-search-bar)
- [12. Quick Reference addendum: Testimonials-specific symptoms](#12-quick-reference-addendum-testimonials-specific-symptoms)
- [13. `pet-detail.html` — Individual Pet Detail Page](#13-pet-detailhtml-individual-pet-detail-page)
- [14. Quick Reference addendum: pet-detail.html-specific symptoms](#14-quick-reference-addendum-pet-detailhtml-specific-symptoms)
- [15. Pet Category Listing Pages — `cats.html`, `dogs.html`, `birds.html`, `reptiles.html`, `small-mammals.html`, `livestocks.html`, `aquariums.html`](#15-pet-category-listing-pages-catshtml-dogshtml-birdshtml-reptileshtml-small-mammalshtml-livestockshtml-aquariumshtml)
- [16. Quick Reference addendum: pet category listing pages](#16-quick-reference-addendum-pet-category-listing-pages-catshtml-dogshtml-birdshtml-reptileshtml-small-mammalshtml-livestockshtml-aquariumshtml)
- [17. `pets/index.html` — Browse All Pets Landing Page](#17-petsindexhtml-browse-all-pets-landing-page)
- [18. `signin/index.html` — Sign-In Entry Point for the Future Rehoming Portal](#18-signinindexhtml-sign-in-entry-point-for-the-future-rehoming-portal)
- [19. The Blog — `blog/index.html`, `blog/post/index.html`, `blog/blogs-data.js`](#19-the-blog-blogindexhtml-blogpostindexhtml-blogblogs-datajs)
- [20. Homepage — `index.html`](#20-homepage-indexhtml)
- [21. `services/index.html` — Services Page (new)](#21-servicesindexhtml-services-page-new)

> **Rule for future AIs:** keep this table current. When you add a new
> numbered section (or a new addendum/quick-reference section) anywhere in
> this document, add a matching entry here in the same position, in
> document order — this table exists so the next AI can jump straight to
> the section it needs instead of reading the whole file. A numbered
> section that isn't listed here defeats the purpose of the table. *(This
> applies to the document-maintenance process described under "Instructions
> for AIs contributing to this document" below — not to Task Job or Project
> Job workers, who never edit this document; see Directive 14a.)*

---

## ⚠️ Warning & Instructions — Read Before Editing Anything

**This section applies to every AI creating, editing, or fixing any page in
this website, now or in the future — not just the section you're currently
working on.** Treat it as standing orders, not a one-time note.

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
3. **Never create a `.md` or `.txt` file unless explicitly told to.** Don't
   add scratch notes, summaries, or documentation files as a side effect of
   a task unless the person asked for one by name.
4. **Carry these instructions through every command you run.** Don't let
   them apply only to the first task in a session and quietly drop off by
   the fifth. See the memory note below for how to make this durable
   across separate conversations, not just within one.
5. **Every page is deployed as a folder path, never a bare filename in the
   URL.** On the server, each page is uploaded as its own folder containing
   an `index.html` — e.g. the file worked on here as `pet-detail.html` is
   uploaded to the server as `pets/detail/index.html`, which is what
   produces the clean URL `https://adopt.petcenterco.com/pets/detail/`
   instead of `.../pet-detail.html`. This is the same pattern already used
   for `apply/adoption-application.html` → `/apply/`,
   `adoptions/status/index.html` → `/adoptions/status/`, and
   `testimonials/index.html` (source file `Testimonials.html`) →
   `/testimonials/` (see Section 1's folder tree and Section 3's file
   table for the existing examples). The file should be named normally while
   working on it in claude (e.g. `pet-detail.html`, `dogs-listing.html`) — what
   matters is that **every internal link and every URL-building line of
   code inside that file** uses the clean folder path, never the `.html`
   filename: `href="/pets/dogs/"`, not `href="/dogs-10.html"`;
   `window.location.href = '/pets/detail/?pet_id=...'`, not
   `'/pet-detail.html?pet_id=...'`. Any new page created must follow this
   exact convention.
⚠️The only exception this rule are admin based file's which aren't html file's! Be warned this rule applies to html pages only⚠️
7. **Never try to recreate features from scratch.** First features are recommended to be used accross all pages when needed and can be enhanced if required e.g for any image/file uploads, we use our pop-up modal feature which includes pdf download for filled documents. For each feature you want to use you must not read only its documentation,  but also go to the exact file(s) it is documented in and use the exact same code in your file for consistency.  You can always find the file(s) the documented features are said to be in, inside your project folder. You must also note some features have .php handlers which work together to help the feature work perfectly,  you must understand it's documentation and the feature code well.

### Dev Manager Directives — added by CEO/Dev Manager, apply project-wide, no exceptions

These directives were issued directly by the CEO and the Dev Manager overseeing this entire project. They carry exactly the same weight as rules 1–7 above — not a secondary or optional layer of guidance. **Every rule below must be read and saved to memory before a worker touches any file, on every single task, for the full duration of this project.**

8. **Everything in this Warning & Instructions section — rules 1 through 14, and every word of it — is top priority.** Save all of it to memory before beginning any task, and re-check it against your own actions before finishing, the same way rule 1 already asks you to self-question before and after every command. Nothing here is filler or background reading.

9. **Consult `PETCENTER_DESIGN_SYSTEM_v1.0.md` before making any UI, styling, or animation decision.** It is a large, standalone reference file — use its Table of Contents to jump directly to the section relevant to your task rather than reading it front to back. One fixed rule applies across all of it: on this website's adoption-related pages, the header, footer, hamburger menu, and core color palette documented in Section 2, Section 2.6, and Section 8 of *this* document remain locked exactly as written — do not let the design system override them. Everywhere else the design system offers a free choice (loading animation style, component variant, etc.), the worker is free to pick whichever option fits the page best; it does not need to match every other page's choice unless a specific task says otherwise.

10. **Image/reference-material gate — do not proceed without it.** Some tasks say a screenshot or reference image is required before starting, because the expected result must visually match something the CEO has in mind. If a task says this and no image is present in your project folder, stop and wait — do not guess at the intended design, do not proceed on assumption, and do not ask the CEO to describe it in text instead. **Reference material is not always an image file** — sometimes it will be a full HTML page containing the desired design, dropped into your project folder instead of a screenshot. Treat that exactly the same way: locate it, open it, and extract the real markup/CSS/structure directly from its source rather than only eyeballing how it renders. Either form (image or HTML reference) is a hard gate on tasks that call for one.

10a. **Design-fidelity lock — for any task involving a reference image or HTML design source.** Treat the reference material as the literal, exact target, not inspiration and not a rough guide. No shortcuts, no simplified or minimalist substitution for what's shown, no "close enough." Before calling any design-related task done, use the Playwright headless browser tool to actually load your finished page and visually compare it against the reference — this is mandatory for any task involving a reference image or design source, not optional. Save the reference image/file to your own outputs directory and keep checking your work against it throughout the task, not just once at the start. Any design-related task also requires cross-referencing `PETCENTER_DESIGN_SYSTEM_v1.0.md` for relevant guidance and existing patterns before building anything. This project has already had a real instance of a worker treating a precise reference image as a loose suggestion and shipping something far short of what was asked — this rule exists specifically because of that, and is treated with zero tolerance going forward.

11. **Use web search during implementation, don't rely only on training knowledge.** This applies to tool choices, free/no-cost API options, current library versions, and current best practices. Training data goes stale; a method that was correct when a worker was trained can be outdated or deprecated by now. Search before committing to an approach, especially for anything involving a third-party service, library, or API.

12. **Multi-worker, same-file protocol — Sequential File Ownership Chain with Anchor Comments.** More than one task in this project can land in the same file, assigned to different workers. Blind parallel editing followed by a manual merge is the single riskiest pattern available to us here, since there is no real version control — files move between the CEO and each worker's project folder by hand. Instead, every task that shares a file with another task will say so explicitly, and workers must follow this exact protocol:
    - **Named zones.** Each worker is told which other task number(s) also touch the same file, and which part of the file is theirs to touch. Stay inside your named zone; do not edit outside it even if you notice something else you'd fix.
    - **Anchor comments.** Wrap every addition in a unique, task-numbered comment block — `<!-- TASK-14 START -->` ... `<!-- TASK-14 END -->` (substitute the real task or project number) — so any later reviewer can see at a glance which lines belong to which task. This is an extension of the existing code-comment rule already in this document, not a separate system.
    - **Sequential handoff, never blind parallel merge.** When a file has more than one assigned worker, one is designated first in line. Every worker after the first pulls the most recently submitted version of that file as their starting point — never the original, untouched file — before making any edit.
    - **One final integration/lint pass.** Whoever closes out a shared file checks it for the recurring bug classes already documented throughout this project (duplicate CSS selectors declared twice, duplicate `padding`/property declarations silently overriding each other, `DOMContentLoaded`/script-timing conflicts, z-index collisions against the site's highest existing z-index) before marking the file done.

13. **Use `cats.html` as the canonical blueprint for header, footer, and hamburger menu on any pet-related page.** Before editing any pet page — the 5 (soon more) category listing pages, `pets/index.html`, `pets/detail/`, or any new pet page — check that its header/footer/hamburger match Section 2 and Section 2.6 of this document exactly. Some existing pages have drifted from the canonical version in the past (see Section 10.8, Section 13.9, Section 17's discrepancy note). If a worker is ever unsure whether a page's header/footer/hamburger is correct, `cats.html` is confirmed current and correct — copy from it directly rather than guessing or re-deriving from this document's prose alone.

14a. **Never edit this document directly, under any circumstance.** This file is read-only reference material — it is not a shared changelog. **Work is no longer delivered as a long completion file with pasted old/new code.** All work now lives on a shared git branch that everyone can read, so you deliver it the way the Worker protocol (`WORKER-GIT-SYNC-PROTOCOL.md`, repo root) says: your code and comments on your own branch, plus a **short** `reports/task-[number].md` (about 80 lines, **no pasted code**) with the sections defined there (Task Number, What Was Required, What I Built, Files Changed, Small Out-of-Scope Fixes Made, Bugs and Follow-ups Sent to TODO.md, Things the Next Person Must Know, Self-Check Confirmation). Every code block you add or change still carries its task-numbered anchor comment (`<!-- TASK-N START -->` … `<!-- TASK-N END -->`) and an explanatory comment, per Warning & Instructions rule 6, with no exceptions — including trivial one-line changes. Because there is no longer a completion document to carry explanations, anything a future reader needs to understand a block (why it is built that way, a trap, a dependency) must be written as a comment next to that code. Anything someone must act on goes into `TODO.md` at the repo root. Brand-new files are simply committed on your branch. A task is not considered done until your branch and report are pushed.

14b. **Scope Lock — no exceptions, ever, except the verified small-fix rule below.** You touch only the files explicitly involved in your task. If completing your task correctly seems to require a change somewhere else — a shared component, a related page, anything not part of your assignment — you do not make that change yourself, no matter how clearly correct it seems, **unless it passes the small-fix test in Step 4 of the Worker protocol**: first verify the problem is real (you can read every file and every branch); the fix is about 10 changed lines or fewer in one place; it adds no new file and changes no shared structure; and no other active branch edits the same lines. In that case fix it as its own separate commit wrapped in `TASK-N-EXTRA START/END` anchor comments and list it in your report. Anything bigger, or anything you are unsure about, you do **not** fix: add an entry to `TODO.md` (file, approximate line range, branch, how to reproduce, severity) and list its number in your report. If something outside your scope is genuinely blocking your own task, raise it in `TODO.md` addressed to your VDM rather than working around it. Entries addressed to you in `TODO.md` count as permission to touch the files they name. This rule holds during a second verification round too — good intentions never override it. An untracked change outside your assigned scope breaks the process used to join everyone's work back together, and has already caused a real problem on this project.

14c. **Second verification round.** If the CEO asks you to re-check your completed work, and you make any further changes, push them to the *same branch* and add a short, clearly labeled **`## SECOND VERIFICATION ROUND`** section to your *existing* `reports/task-[number].md` — never a new file. Keep it short, with no pasted code, and the same anchor-comment and Scope Lock rules apply. A request to re-verify your own work is not permission to touch anything outside your original assignment.

### Testing tools — required, not optional

Real, rendered, measured verification is mandatory before calling
anything "done" — reasoning about CSS/JS in the abstract is not
sufficient and has caused real regressions in this project (see the
"Lesson learned" callouts throughout this document). Use:

| Tool | What it's for |
|---|---|
| **Playwright** (Python or Node, Chromium headless) | Load the real page, drive real interaction (click, hover, touch, scroll), and read back real computed styles / bounding boxes / timing. This is the primary way to confirm a fix actually works, not just that the code looks right. |
| **CDP screenshots** (`Page.captureScreenshot` via a raw CDP session) | Standard `page.screenshot()` waits on `document.fonts.ready`, which can hang indefinitely if a font/CDN request is blocked or unavailable in the working environment. A raw CDP screenshot bypasses that wait when you need a render and the normal API won't cooperate. |
| **Pixel-level image analysis** (e.g. PIL/numpy) | Cropping a render and scanning pixel rows/colors to independently confirm layout (text line positions, colors actually applied) when visual inspection alone isn't reliable or available. |
| **Route stubbing / interception** (`page.route`) | Stubbing JSON fetches and blocking unreachable third-party requests (fonts, CDNs) so a page under test can reach a usable state instead of hanging on a network call that will never resolve in a sandboxed environment. |
| **ffmpeg** | Generating small synthetic test media (e.g. portrait vs. landscape test videos) when real sample assets aren't available, to verify logic that depends on real file dimensions/metadata. |
| **Temporary exposed test copies** | Closure-scoped functions aren't reachable from outside their scope. Make a throwaway copy of the file with one line added to expose the function on `window` for direct testing, verify against it, then delete the copy — never ship it. |
| **Structural lint** (balanced tags, balanced braces/parens, no duplicate `id`s) | A fast, cheap check to run after every edit, before ever opening the file in a browser — catches an entire class of self-inflicted breakage for free. |

**Boundary-case testing is not optional.** "It works in my one test case"
is insufficient for anything with more than one input shape — test the
shortest input, the longest input, an empty/zero input, and every discrete
state a toggle or score can be in, individually.

**If a sandbox limitation blocks real verification** (no network access,
a missing codec, a function that isn't reachable), say so plainly rather
than presenting an untested claim as verified. Prefer a narrow, clearly
temporary workaround over skipping verification.

### Carrying this across sessions (Claude memory)

Claude's persistent memory feature (available on Claude.ai, including the
free plan) can hold this instruction so it's active automatically in
future sessions, not just this one:

- In the Claude.ai app: **Settings → Capabilities → Memory**, turn on
  **"Generate memory from chats."** Claude can then be asked directly —
  in any conversation — to remember this Warning & Instructions section
  (e.g. "remember these standing instructions for every future task on
  this project"), and it will persist as part of Claude's memory of you
  going forward.
- Memories can be reviewed and edited any time at **Settings → Memory**.
- This is separate from, and in addition to, restating the instruction at
  the top of a task — memory reduces how often it needs to be repeated,
  it doesn't replace pasting this section into a new project/repo the
  first time Claude works in it.

---

---

## Features

Reusable, cross-page features documented in this file. If you're building or
fixing a new page and need one of these, jump straight to the linked section
instead of re-implementing it from scratch.

| Feature | Use case | Read more |
|---|---|---|
| **Hamburger Menu** | Site-standard mobile nav menu — required on every page, old or new | Section 2 |
| **Signature Pad** | Canvas-based draw-to-sign input, saved as a base64 PNG in a hidden field | Section 5.5 |
| **PDF Generation & the two "preview" features** | Client-side PDF generation from a form, plus two distinct preview/popup patterns sharing one modal (uploaded-file preview vs. full-application preview) | Section 5.6 |
| **Bottom Sheet + Filter Button + Search Bar** | A dark-themed, full-width, drag-to-close mobile bottom sheet paired with a pill-shaped Filter trigger and a circular Search button with debounced live filtering — built for the Testimonials page's Filters panel and Review-submission form, reusable on any page needing a pull-up panel pattern | Section 11 |
| **Reading-time-based rotating banner** | A fading/rotating text banner (tips, announcements, etc.) whose on-screen duration per item is computed from word count instead of one fixed timer, with pause-on-hover/touch and auto-scroll for items too long to fit their box — built for the pet-detail page's Tips card, reusable anywhere a rotating message banner is needed | Section 13.3 |
| **Compatibility Match Ring** | An animated SVG progress ring with a distinct color per possible score and a live "X / N" count, driven off any set of Yes/No/Unknown-style fields — built for pet-detail's Kids/Dogs/Cats/Other checklist, reusable for any small fixed-size compatibility/checklist score | Section 13.4 |

> **Rule for future AIs:** when you document a new reusable feature in this
> file, add a row to this table too — name the feature, give a one-line use
> case, and link to the section where it's fully documented. This is how the
> next AI finds it without reading the whole file. *(Same note as above —
> this is the document-maintenance process, not something Task Job or
> Project Job workers do; see Directive 14a.)*

## About This Project

**Pet Center** (Pet Center Foundation) is an animal rescue and adoption
organization. Physical location: **235-269 N Beverly Dr, Beverly Hills, CA —
Golden Triangle Shopping Center, Section #257.** Hours: Mon–Fri 7am–9pm,
Sat–Sun 10am–6pm.

Pet Center rescues and lists dogs, cats, birds, reptiles, small mammals,
livestock, and aquarium pets for adoption. Some listings are individual animals; others are "multiple
adoptable options" listings (e.g. a flock/litter with several animals of
varying age, sex, or color available under one listing — applicants specify
which one(s) they want in their application). Adoption fees, shipping/local
delivery, starter kits, 30-day health insurance, and flexible payment plans
(BNPL, Pay on Delivery, Pay-in-4 / in-house installments) are all handled
per-listing and are visible on each pet's detail page.

**Website:** `adopt.petcenterco.com` — the public-facing adoption platform
covering pet browsing, applications, and adoption status tracking.
Supporting policy/help content (privacy policy, returns, FAQ, contact form,
health guarantee, shipping terms, etc.) lives on a separate subdomain,
`docs.petcenterco.com`, and is linked to from every page's footer rather
than hosted on the main site.

**Hosting / platform:** Hostinger shared hosting, standard `public_html`
directory layout, PHP backend (no framework — plain PHP scripts), static
HTML/CSS/JS frontend (no build step, no bundler — files are hand-authored
and deployed as-is). Data storage is **flat-file JSON**, not a database —
every pet listing and every submitted application is a `pet.json` /
`application.json` file sitting in a predictable folder path (see Section 1). Pet
listings and application statuses are managed through a separate internal
admin dashboard (documented in `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`) —
publishing/editing a pet or approving/rejecting an application goes through
that dashboard's PHP endpoints (`publish-pet.php`, `update-adoption.php`,
etc.), which write these same JSON files; it isn't raw hand-editing of
JSON in a hosting file manager as a day-to-day workflow, even though the
underlying storage format is still flat-file JSON, not a database.

**Design system:** Navy (`#10214b`) / Gold (`#d7bd88`) / Orange (`#ED7624`)
color palette, `Cormorant Garamond` serif for headings, `Sora` sans-serif
for body/UI text. Every page shares one fixed header with a dual-logo
scheme, one 5-column footer, and one hamburger-menu implementation — see
Section 2 below, which is **mandatory** for every page, old or new.

---

## Instructions for AIs contributing to this document

> ⚠️ **If you are a worker completing a Task Job or Project Job sub-task, this entire section does not apply to you.** This section describes how this document *itself* gets permanently extended over time — a separate, deliberate process the Dev Manager/CEO runs directly, not something that happens as a side effect of routine task work. **You do not edit this document, under any circumstance** — see Warning & Instructions Directive 14a above for what you actually do instead: push your code on your own branch plus a short `reports/task-[number].md`, as the Worker protocol describes. If you're unsure which applies to you, it's Directive 14a — this section is not for you.

If you are an AI asked to document a file not yet covered here (e.g.
`pet-detail.html`, `dogs-10.html`, `cats-18.html`, `birds-10.html`,
`reptiles-10.html`, `small-mammals-9.html`, or any new page added later):

> **Note:** the filenames in the list above are illustrative placeholders
> for this instruction template, not a claim about what any real page is
> actually named. Always confirm actual filenames against what the person
> gives you or what's already deployed — don't copy an example name from
> this paragraph into a new section as if it were verified fact. (The pet
> category listing pages' real names are `cats.html`, `dogs.html`,
> `birds.html`, `reptiles.html`, `small-mammals.html`, `livestocks.html`,
> and `aquariums.html` — see Section 15.)

1. **Add your file(s) to the folder structure in Section 1** — don't create a
   second folder-structure section, extend the existing tree in place.
2. **Add a new numbered section** for your file(s) following the same
   pattern already used below: structure/purpose, then subsections for each
   distinct feature, with a **"Lesson learned"** callout anywhere you fix a
   bug that a future AI could easily reintroduce.
3. **Add rows to the "Where do I fix X?" table** at the very end for
   anything your file(s) handle that isn't already listed.
4. **Do not duplicate the hamburger menu section (Section 2).** Every page must
   use that exact implementation verbatim — reference it, don't re-explain
   it or invent a variant.
5. **If you document a reusable feature** (something another page could plug
   in as-is, not just a one-off detail of your own page), **add a row to the
   Features table at the very top of this document** — name the feature, a
   short use case, and a link to the section where it's fully documented. The
   Features table is the entry point other AIs scan first; a feature that
   only exists buried in your section won't be found.
6. Keep the same tone: this file is written so a fix can be located without
   re-reading entire source files first. Prefer "where" and "why it broke
   before" over restating what the code visibly does.

> **Scope note on the current version of this document:** it covers
> `index.html` (Section 20), `apply/adoption-application.html`,
> `apply/adoption-application.php`, `adoptions/status/index.html`,
> `testimonials/index.html` (+ its supporting `.json`/`.php` files),
> `pet-detail.html` (Section 13), the 7 pet category listing pages —
> `cats.html`, `dogs.html`, `birds.html`, `reptiles.html`,
> `small-mammals.html`, `livestocks.html`, `aquariums.html` (Section 15),
> `pets/index.html` (Section 17), `signin/index.html` (Section 18), the
> blog — `blog/index.html`, `blog/post/index.html`, and `blog/blogs-data.js`
> (Section 19), and `services/index.html` (Section 21, new).

---

## 1. Server Folder Structure

```
public_html/
└── adopt/                                  ← site root (adopt.petcenterco.com/)
    ├── index.html                          ← Homepage
    │
    ├── apply/                              ← Adoption application (sibling of /adoptions/)
    │   ├── adoption-application.html       ← The multi-step application form
    │   ├── adoption-application.php        ← Backend: processes submission, sends email, saves JSON
    │   ├── upload-temp.php                 ← Backend: pre-uploads ID/proof files, returns a token
    │   ├── config.php                      ← (optional) overrides UPLOAD_BASE / LOG_FILE constants
    │   ├── adoption_applications.log       ← Plain-text submission log (one line per submission)
    │   └── uploads/
    │       └── adoption-applications/
    │           └── {user_folder}/          ← Per-submission uploaded documents (ID, proof, signature, PDF)
    │
    ├── adoptions/                          ← Sibling of /apply/, NOT nested inside it
    │   ├── pets/                            ← DATA folder (JSON + images) — NOT the same folder as the
    │   │   │                                   page-serving /pets/ near the bottom of this tree. This one
    │   │   │                                   is fetched from by both /pets/index.html (Section 17) and
    │   │   │                                   the 7 category listing pages (Section 15) via
    │   │   │                                   /adoptions/pets/{category}/index.json.
    │   │   ├── dogs/
    │   │   │   ├── index.json
    │   │   │   └── DOG-00001/
    │   │   │       ├── pet.json
    │   │   │       ├── single_image.jpg
    │   │   │       └── bulk_images/
    │   │   ├── cats/{CAT-00001}/...
    │   │   ├── birds/{BIRD-00001}/...
    │   │   ├── reptiles/{REP-00001}/...      ← prefix is REP- (see Section 15's Lesson Learned note —
    │   │   │                                    an earlier REPTILE- generator mismatch was found/fixed)
    │   │   ├── small-mammals/{SM-00001}/...
    │   │   ├── livestocks/{LVS-00001}/...    ← added — see Section 15's "Two New Categories" note
    │   │   └── aquariums/{AQU-00001}/...     ← added — see Section 15's "Two New Categories" note
    │   │
    │   ├── applications/
    │   │   └── AD-{5digits}-{4digits}/     ← One folder per submitted application
    │   │       ├── application.json        ← Full submission record (source of truth for status)
    │   │       └── metadata.json           ← Lightweight lookup copy
    │   │
    │   └── status/
    │       └── index.html                  ← Status dashboard page (adopt.petcenterco.com/adoptions/status/)
    │
    ├── testimonials/                       ← Sibling of /apply/ and /adoptions/ (adopt.petcenterco.com/testimonials/)
    │   ├── index.html                      ← Page file is named Testimonials.html in the source repo;
    │   │                                     deployed/served as index.html at this path
    │   ├── testimonials.json               ← Admin-curated review data + business metadata (source of truth)
    │   ├── testimonials.log                ← Append-only audit trail of visitor-submitted reviews (never
    │   │                                     written to testimonials.json directly — see Section 10.2)
    │   ├── save-testimonial.php             ← Backend: receives new review submissions, logs them
    │   ├── vote-helpful.php                 ← Backend: persists thumbs up/down votes into testimonials.json
    │   ├── Google-reviews.png               ← "Powered by Google" badge (primary)
    │   ├── Google-reviews.svg               ← Same badge, inlined as fallback if the PNG fails to load
    │   └── profiles/
    │       └── admin1.png, admin2.png, ... ← Real staff photos used for admin-reply avatars (optional —
    │                                          falls back to colored initials if a file is missing)
    │
    ├── pets/                                ← PAGE folder — the actual HTML pages, not to be confused
    │   │                                       with /adoptions/pets/ above (that one holds JSON + images)
    │   ├── index.html                      ← pets.html — Browse All Pets landing page
    │   │                                      (adopt.petcenterco.com/pets/) (Section 17 — covered)
    │   ├── detail/
    │   │   └── index.html                  ← pet-detail.html, deployed as index.html at this path —
    │   │                                      adopt.petcenterco.com/pets/detail/?pet_id={id}&category={cat}
    │   │                                      (Section 13 — covered)
    │   └── cats.html, dogs.html, birds.html, reptiles.html,
    │       small-mammals.html, livestocks.html,
    │       aquariums.html                  ← Category listing pages, 7 total (Section 15 — covered)
    │
    ├── blog/
    │   ├── index.html                      ← blog-home.html — Blog landing page (grid + category filter)
    │   │                                      (adopt.petcenterco.com/blog/) (Section 19 — covered)
    │   ├── post/
    │   │   └── index.html                  ← blog-single.html — Individual article page, deployed as
    │   │                                      index.html at this path so the query string produces a
    │   │                                      clean URL: adopt.petcenterco.com/blog/post/?id={blogId}
    │   │                                      (Section 19 — covered)
    │   └── blogs-data.js                   ← Shared data file, loaded via a bare relative
    │                                          `<script src="blogs-data.js">` by both pages above — must
    │                                          stay in this same folder for that reference to resolve
    │                                          (Section 19 — covered)
    │
    ├── services/
    │   └── index.html                       ← services.html — new, full-article write-ups of the 6
    │                                           services shown in the homepage's "What's New" slide
    │                                           (adopt.petcenterco.com/services/) (Section 21 — covered)
    │
    └── signin/
        └── index.html                       ← signin.html (renamed from rehome.html; /rehome/ is
                                                retired sitewide, replaced by /signin/) — sign-in entry
                                                point for the future Rehoming Platform portal
                                                (adopt.petcenterco.com/signin/) (Section 18 — covered)
```

**Path resolution rule that matters most:** `adoption-application.php` lives in `/apply/`, but `/adoptions/` is a **sibling** of `/apply/`, not nested inside it. The PHP therefore uses `dirname(__DIR__)` to step up one level before appending `/adoptions/applications/`:

```php
$ADOPTIONS_BASE = dirname(__DIR__) . '/adoptions/applications/';
```

Any future edit to this PHP file that touches path logic must preserve this — using plain `__DIR__` here would silently write files to `/apply/adoptions/applications/` instead of `/adoptions/applications/`, breaking the status page and homepage checker with no visible error (the folder would just be created in the wrong place).

`$UPLOAD_BASE` and `$LOG_FILE`, by contrast, correctly use plain `__DIR__` since they belong inside `/apply/` itself.

---

## 2. Hamburger Menu — Mandatory Implementation for All Pages

This exact implementation is used on every page site-wide and **must** be
copied verbatim into any new page rather than reimplemented. It solves three
specific problems that recurred repeatedly before being fixed here:

1. On pages with a fixed bottom bar (pet detail, adoption form), the last
   menu item was hidden behind the bar with no way to scroll to it.
2. Scrolling the menu to its end would "leak" and start scrolling the page
   underneath instead of stopping.
3. The hamburger icon never animated into an X on open.

### 2.1 HTML structure

The mobile nav lives **inside** `<header>`, not as a sibling:

```html
<header class="pc-header pc-header--solid" id="pcHeader">
  <a href="/" class="pc-header__logo pc-header__logo--desktop">...</a>
  <a href="/" class="pc-header__logo pc-header__logo--mobile">...</a>
  <ul class="pc-header__nav">...</ul>

  <button class="pc-hamburger" id="pcHamburger" aria-label="Open menu" aria-expanded="false">
    <span></span><span></span><span></span>
  </button>

  <nav class="pc-mobile-menu" id="pcMobileMenu" aria-label="Mobile navigation">
    <a href="/">Home</a>
    ...
    <a href="https://donate.petcenterco.com" class="mobile-donate">Donate</a>
  </nav>
</header>
```

### 2.2 CSS — the hamburger icon (X animation)

```css
.pc-hamburger span {
  display: block; width: 24px; height: 2px;
  background: #fff; border-radius: 2px;
  transition: all 0.3s ease;
}
.pc-hamburger.active span:nth-child(1) { transform: rotate(45deg) translate(8px, 8px); }
.pc-hamburger.active span:nth-child(2) { opacity: 0; }
.pc-hamburger.active span:nth-child(3) { transform: rotate(-45deg) translate(7px, -7px); }
```

### 2.3 CSS — the menu scroll fix (the important part)

```css
.pc-mobile-menu.open {
  max-height: calc(100vh - 68px);   /* full screen minus header height */
  overflow-y: auto;
  padding: 16px 0 calc(100vh - 68px - 56px);  /* ← the fix */
  overscroll-behavior: contain;      /* stops scroll bleeding into the page */
  -webkit-overflow-scrolling: touch; /* smooth momentum scroll on iOS */
}
```

**Why `padding-bottom: calc(100vh - 68px - 56px)`:**

- `100vh - 68px` is the menu's own visible height (screen height minus the
  fixed header).
- Subtracting `56px` (the height of one menu link) means the scrollable
  content is padded so that when you scroll all the way down, exactly one
  item's worth of space remains at the bottom — so the **last link sits
  fully visible, right at the bottom edge of the menu**, with everything
  above it scrolled up and out of view inside the menu's own box.
- This works regardless of whether the page has a fixed bottom bar, because
  the padding is relative to the menu's own height, not the page's — the
  menu never needs to know what else is on the page.

**Critical gotcha:** never write two `padding` declarations on the same
rule. In earlier versions of these files, `.pc-mobile-menu.open` had
`padding: 16px 0 100px;` followed later by `padding: 16px 0 24px;` inside
the same block — the second silently won, so the fix appeared to do
nothing. Always check for duplicate property declarations when a CSS fix
doesn't seem to apply.

`overscroll-behavior: contain` is what stops the scroll from bleeding
through to the page once you hit the end of the menu's scroll range —
without it, continuing to scroll past the bottom of the menu scrolls the
page behind it instead.

### 2.4 JS — open/close with body scroll lock

```js
(function () {
  var hamburger = document.getElementById('pcHamburger');
  var mobileMenu = document.getElementById('pcMobileMenu');
  if (!hamburger || !mobileMenu) return;

  function openMenu() {
    hamburger.classList.add('active');
    mobileMenu.classList.add('open');
    hamburger.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';   // lock page scroll
  }
  function closeMenu() {
    hamburger.classList.remove('active');
    mobileMenu.classList.remove('open');
    hamburger.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';          // restore page scroll
  }

  hamburger.addEventListener('click', function (e) {
    e.stopPropagation();
    mobileMenu.classList.contains('open') ? closeMenu() : openMenu();
  });

  mobileMenu.querySelectorAll('a').forEach(function (link) {
    link.addEventListener('click', closeMenu);
  });

  document.addEventListener('click', function (e) {
    if (!e.target.closest('header') && !e.target.closest('.pc-mobile-menu')) {
      closeMenu();
    }
  });
})();
```

`document.body.style.overflow = 'hidden'` while the menu is open is what
prevents the page from scrolling underneath at all — combined with
`overscroll-behavior: contain` on the menu itself, this fully isolates the
menu's scroll from the page's scroll in both directions.

### 2.5 Where this is implemented

| Page | Fixed bottom bar? | Notes |
|---|---|---|
| `index.html` (homepage) | No | |
| `adoptions/status/index.html` | No | |
| `pet-detail.html` | Yes (Apply to Adopt bar) | |
| `apply/adoption-application.html` | Yes (sticky Next/Submit bar) | |
| `cats.html`, `dogs.html`, `birds.html`, `reptiles.html`, `small-mammals.html`, `livestocks.html`, `aquariums.html` | No | |
| `pets/index.html`, `signin/index.html`, `services/index.html` | No | |

The `padding-bottom` formula is identical everywhere since it's derived
purely from the menu's own height, not the page layout — that's what makes
it portable across every page regardless of what else is fixed to the
viewport. **Any new page must use this exact CSS/JS, unmodified.**

### 2.6 Full header and footer markup — copy exactly, do not reinvent

The hamburger menu above lives *inside* the `<header>` shown here — this
subsection gives the **complete** header (including the hamburger button
and mobile nav from Section 2.1) and the complete footer, so a new page
can be built by copying both blocks verbatim rather than re-deriving them
from the class-name notes in Section 8. Pulled from a real, currently
deployed page (`pets/detail/` — see Section 13) — link targets already use
the clean folder-path convention (rule 5 in the Warning section above);
copy them as-is, don't rewrite any `href` back to a `.html` filename.

**Header** (place as the first element inside `<body>`):

```html
<header class="pc-header pc-header--solid" id="pcHeader">
  <a href="/" class="pc-header__logo pc-header__logo--desktop">
    <img src="/assets/images/logo-petcenter.png" alt="Pet Center">
  </a>
  <a href="/" class="pc-header__logo pc-header__logo--mobile">
    <img src="/assets/images/logo-petcenter-icon.png" alt="Pet Center">
  </a>
  <ul class="pc-header__nav">
    <li><a href="/">Home</a></li>
    <li class="has-drop"><a href="#">Our Pets</a>
      <ul>
        <li><a href="/pets/dogs/">Dogs</a></li>
        <li><a href="/pets/cats/">Cats</a></li>
        <li><a href="/pets/birds/">Birds</a></li>
        <li><a href="/pets/reptiles/">Reptiles</a></li>
        <li><a href="/pets/small-mammals/">Small Mammals</a></li>
        <li><a href="/pets/livestocks/">Livestocks</a></li>
        <li><a href="/pets/aquariums/">Aquariums</a></li>
      </ul>
    </li>
    <li><a href="/blog/">Blog</a></li>
    <li><a href="/testimonials/">Testimonials</a></li>
    <li class="nav-donate"><a href="https://donate.petcenterco.com" target="_blank" rel="noopener">Donate</a></li>
    <li><a href="https://docs.petcenterco.com/contact-us" target="_blank" rel="noopener">Contact</a></li>
    <li class="nav-signup"><a href="/signin/">Sign Up</a></li>
  </ul>
  <button class="pc-hamburger" id="pcHamburger" aria-label="Open menu" aria-expanded="false">
    <span></span><span></span><span></span>
  </button>
  <nav class="pc-mobile-menu" id="pcMobileMenu" aria-label="Mobile navigation">
    <a href="/">Home</a>
    <a href="/pets/dogs/">Dogs</a>
    <a href="/pets/cats/">Cats</a>
    <a href="/pets/birds/">Birds</a>
    <a href="/pets/reptiles/">Reptiles</a>
    <a href="/pets/small-mammals/">Small Mammals</a>
    <a href="/pets/livestocks/">Livestocks</a>
    <a href="/pets/aquariums/">Aquariums</a>
    <a href="/blog/">Blog</a>
    <a href="/testimonials/">Testimonials</a>
    <a href="https://docs.petcenterco.com/contact-us" target="_blank" rel="noopener">Contact</a>
    <a href="/signin/">Sign Up</a>
    <a href="https://donate.petcenterco.com" class="mobile-donate" target="_blank" rel="noopener">Donate</a>
  </nav>
</header>
```

**Footer** (place as the last element before `</body>`, or immediately
before the closing tag of the page's main content wrapper):

```html
<footer class="pc-footer">
  <div class="pc-footer__inner">
    <div class="pc-footer__top">
      <div class="pc-footer__brand">
        <img src="/assets/images/logo-petcenter.png" alt="Pet Center">
        <p>Every pet deserves a loving home. Browse our available pets and start your adoption journey today.</p>
      </div>
      <div class="pc-footer__col">
        <h5>Our Pets</h5>
        <ul>
          <li><a href="/pets/dogs/">Dogs</a></li>
          <li><a href="/pets/cats/">Cats</a></li>
          <li><a href="/pets/birds/">Birds</a></li>
          <li><a href="/pets/reptiles/">Reptiles</a></li>
          <li><a href="/pets/small-mammals/">Small Mammals</a></li>
          <li><a href="/pets/livestocks/">Livestocks</a></li>
          <li><a href="/pets/aquariums/">Aquariums</a></li>
        </ul>
      </div>
      <div class="pc-footer__col">
        <h5>Information</h5>
        <ul>
          <li><a href="/">About Us</a></li>
          <li><a href="/blog/">Blog</a></li>
          <li><a href="/testimonials/">Testimonials</a></li>
          <li><a href="https://donate.petcenterco.com" target="_blank" rel="noopener">Donate</a></li>
          <li><a href="https://docs.petcenterco.com/health-guarantee/" target="_blank" rel="noopener">Health Guarantee</a></li>
          <li><a href="https://docs.petcenterco.com/shipping-cancellation/" target="_blank" rel="noopener">Shipping & Cancellation</a></li>
          <li><a href="https://docs.petcenterco.com/shipping-insurance-claims/" target="_blank" rel="noopener">Shipping Insurance</a></li>
          <li><a href="https://docs.petcenterco.com/data-retention-security/" target="_blank" rel="noopener">Data & Security</a></li>
        </ul>
      </div>
      <div class="pc-footer__col">
        <h5>Support</h5>
        <ul>
          <li><a href="https://docs.petcenterco.com/contact-us" target="_blank" rel="noopener">Contact Us</a></li>
          <li><a href="/signin/">Sign Up</a></li>
          <li><a href="https://docs.petcenterco.com/returns-refunds/" target="_blank" rel="noopener">Returns & Refunds</a></li>
          <li><a href="https://docs.petcenterco.com/faq/" target="_blank" rel="noopener">FAQ</a></li>
        </ul>
      </div>
      <div class="pc-footer__col">
        <h5>Visit Us</h5>
        <p style="color:rgba(255,255,255,.7);font-size:13px;line-height:1.8;margin-bottom:0;">235-269 N Beverly Dr<br>Beverly Hills, CA</p>
        <p style="color:rgba(255,255,255,.7);font-size:13px;line-height:1.8;margin-top:10px;"><strong style="color:#d7bd88;">Hours:</strong><br>Mon – Fri: 7am – 9pm<br>Sat – Sun: 10am – 6pm</p>
      </div>
    </div>
    <div class="pc-footer__bottom">
      <span class="pc-footer__copy">&copy; 2026 Pet Center. All rights reserved.</span>
      <div class="pc-footer__links">
        <a href="https://docs.petcenterco.com/privacy-policy/" target="_blank" rel="noopener">Privacy Policy</a>
        <a href="https://docs.petcenterco.com/terms-of-service/" target="_blank" rel="noopener">Terms of Service</a>
      </div>
    </div>
  </div>
</footer>
```

**Discrepancy worth flagging, not silently corrected here:** Section 8
below states the footer's "Visit Us" column includes the full address
"Golden Triangle Shopping Center, Section #257." The real markup pulled
above does not contain that text — only "235-269 N Beverly Dr, Beverly
Hills, CA." Whoever next touches Section 8 or the footer should confirm
which is actually correct on the live site and fix the mismatch; it was
deliberately left as-is here since correcting Section 8's prose wasn't
part of this specific edit.

**Where to find these for a new page:** copy both blocks from this
subsection directly — don't copy them from any single page's source file,
since an individual page could have drifted from the standard (see the
regression example in Section 10.8/Section 13.9). This subsection is the
canonical copy.

---



## 3. Files Covered In This Document

| File | Role |
|---|---|
| `index.html` | Homepage — hero, "What's New" services slide, pet browsing links, testimonials carousel, minimal adoption-status popup checker, footer/header — see Section 20 |
| `apply/adoption-application.html` | The 5-step adoption application form, including multi-pet carousel, file uploads, signature pad, PDF generation, preview modal |
| `apply/adoption-application.php` | Backend: validates submission, saves `application.json`/`metadata.json`, emails applicant + admin, generates a downloadable PDF attachment |
| `adoptions/status/index.html` | Full status dashboard — looks up an adoption code, shows PENDING/APPROVED/REJECTED state with full detail and multi-pet carousel |
| `testimonials/index.html` (source file `Testimonials.html`) | The Google-Business-Profile-styled reviews page: business info card, Filters bottom sheet, search, review cards with threaded admin replies and helpful votes, review-submission form |
| `testimonials/testimonials.json` | Admin-curated review data + business metadata (hours, timezone, location) — the one file an admin hand-edits directly to add/change reviews |
| `testimonials/save-testimonial.php` | Backend: receives visitor-submitted reviews, logs them to `testimonials.log` (never writes to `testimonials.json`) |
| `testimonials/vote-helpful.php` | Backend: persists thumbs up/down "helpful" votes directly into `testimonials.json` |
| `pets/detail/index.html` (source file `pet-detail.html`) | Individual pet listing page — full pet info panel, gallery/lightbox, portrait/landscape-aware video player, fading Tips card, compatibility match ring, personality vibe badge, sticky mini action bar, similarity-ranked "You Might Also Like" |
| `cats.html`, `dogs.html`, `birds.html`, `reptiles.html`, `small-mammals.html`, `livestocks.html`, `aquariums.html` | The 7 pet category listing pages — identical structure/CSS/JS per page, differing only in title/banner copy and `data-category`; card grid + Filters bottom sheet + live breed/Pet-ID search (Section 15) |
| `pets/index.html` (source file `pets.html`) | Browse All Pets landing page — 7 category cards linking into the pages above, each showing a live "N available"/"Check back soon" count (Section 17) |
| `signin/index.html` (source file `signin.html`, renamed from `rehome.html`) | Placeholder "Coming Soon" page for a future pet-listing portal — now has full header/footer, no backend on the notify form yet, foundation for the Rehoming Platform project (Section 18) |
| `blog/index.html` (source file `blog-home.html`), `blog/post/index.html` (source file `blog-single.html`), `blog/blogs-data.js` | The blog — a landing grid with category filtering and an individual article page, both driven by one shared data file (Section 19) |
| `services/index.html` (source file `services.html`, new) | Full-article write-ups of the 6 services introduced in the homepage's "What's New" slide (Section 21) |

---

## 4. Adoption Code — the system's shared key

Every application is identified by a code in the format `AD-XXXXX-XXXX` (5 digits, dash, 4 digits), e.g. `AD-47293-2026`.

- **Generated client-side** in `adoption-application.html` by `generateAdoptionCode()`.
- **Sent to the server** as `adoption_code` in the form POST.
- **Validated server-side** in `adoption-application.php` with `preg_match('/^AD-\d{5}-\d{4}$/')`; if missing or malformed, the PHP generates a fallback in the same format — it never invents a different format.
- This code is the **folder name** under `/adoptions/applications/`, the **filename key** both `index.html` and `adoptions/status/index.html` fetch by, and the value shown to the user on the success modal.

**Lesson learned:** an earlier version had the PHP generate its own random hex code independently of the one the frontend showed the user — the email and the on-screen code didn't match. If you ever touch code generation, there must be exactly one source of truth (the frontend generates it, the backend validates and reuses it, never regenerates unless genuinely missing).

---

## 5. `adoption-application.html` — Application Form

### 5.1 Structure
5-step form: **Applicant → Living → Pets & Care → Adoption → Review**, controlled by `updateStickyFooter(step)` which shows/hides Back/Next/Download/Submit buttons in a sticky footer bar (`.sticky-review-buttons`, `position: fixed; bottom: 0`).

### 5.2 Pet pre-fill (single pet, via URL)
On load, reads `?pet_id=` and `?category=` from the URL, fetches `/adoptions/pets/{category}/{pet_id}/pet.json`, and populates hidden form fields (`pet_id`, `pet_category`, `pet_name`, `pet_breed`, `pet_age`, `pet_size`, `adoption_fee`, `pet_image_url`) plus a visible "Your Selected Pet" card. This pet becomes **slot 0** of the carousel (see below).

**Lesson learned:** originally only `pet_id` and `category` were sent as hidden fields — `pet_name`, `breed`, `fee`, etc. were shown on screen but never actually submitted, so `application.json` had empty pet data. All pet display fields must have matching hidden `<input>`s or they silently don't reach the backend.

### 5.3 Multi-pet carousel ("adopt more than one pet at once")
- State lives in a JS array `carouselPets[]` (max 5, enforced by `MAX_PETS = 5`). Index 0 is always the primary pet from the URL; indices 1+ are user-added.
- **Add flow:** user types a Pet ID (e.g. `DOG-00002`) into `#addPetIdInput`, clicks `#addPetBtn`. The button cycles `Add → Adding… → Added → Add`. The category is inferred from the ID prefix (`DOG`→dogs, `CAT`→cats, `BIRD`→birds, `REP`→reptiles, `SM`→small-mammals, `LVS`→livestocks, `AQU`→aquariums) and `pet.json` is fetched from `/adoptions/pets/{category}/{id}/pet.json`.
- **Display:** all pets render as horizontal slides inside `#petCarouselTrack` (`display:flex`, each slide `flex:0 0 100%`), with `translateX(-N*100%)` sliding logic, dot indicators, and prev/next arrows that only appear once there are 2+ pets. Touch swipe supported.
- **Remove:** each non-primary slide has an ✕ button; removing a pet re-enables the Add button (max count is always `MAX_PETS - carouselPets.length`).
- **Submission:** `syncAdditionalPetInputs()` writes one hidden `<input name="additional_pet_ids[]">` per added pet (skipping the primary) right before submit, so the PHP receives them as a standard PHP array field.
- **Pet ID tooltip:** the label text "Pet ID*" is a clickable trigger (`#petIdTipTrigger`) that opens a small chat-bubble-style tooltip explaining where to find a pet's ID (shown just above the About section on that pet's detail page).

### 5.4 File uploads (ID, proof of funding, etc.)
- Each file field pre-uploads immediately on selection via `preUploadFile(key, item)` → POSTs to `/apply/upload-temp.php`, which returns a temp token. Tokens are later sent as `{fieldname}_token[]` on final submission (note: **singular** `_token`, not `_tokens` — this exact field name matters, see lesson below).
- `renderField(key)` draws each uploaded file's row: thumbnail, filename, size, and a live status indicator (✓ Uploaded / Uploading… / ⚠ error) using inline SVG icons — **never emoji**, per project style rule.
- **Retry button:** appears only when `item.uploadError` is set and not currently uploading. Clicking it clears the error and re-calls `preUploadFile()` for that exact item.

**Lesson learned:** a past bug sent tokens as `file_id_tokens[]` (plural) while the PHP expected `file_id_token[]` (singular) — uploads succeeded but the PHP rejected the whole submission with "Please upload both front and back of your Government ID" because it couldn't find the token array under the name it expected. Always verify the exact field name string matches between frontend `FormData.append()` calls and PHP `$_POST`/`$_FILES` keys character-for-character.

### 5.5 Signature pad
Draw-based canvas signature. On save, converts to a base64 PNG data URL and stores it in a **hidden text input** `<input type="hidden" name="signature">` — it is NOT a file upload, so the PHP must read it from `$_POST['signature']`, decode the base64, and write it to disk itself (see PHP section below).

**Lesson learned:** desktop mouse drawing previously didn't work at all — only touch input drew a signature. Canvas event handling now covers both mouse and touch input correctly; also fixed alongside it, a window-resize could silently wipe an in-progress signature (the canvas buffer was being reset to 0×0 on resize before being re-fit) — the canvas now snapshots and restores the drawing across a resize instead of losing it.

### 5.6 PDF generation & the two different "preview" features (do not confuse these)

There are **two separate, unrelated preview/popup features** sharing the same `#previewModal` element but with different content injected — this distinction caused repeated regressions and must be preserved:

**A. Uploaded file preview** (`openPreview(item)`)
Triggered by the "Preview" button next to each uploaded document. Shows the raw image or a PDF iframe. Sets the modal's top-bar title to the file's name and **hides** the Download PDF button (`modalDownloadPdfBtn.style.display = 'none'`).

**B. Application preview / Download** (`showPreviewModal()`)
Triggered by the sticky-footer "Download" button (a download-arrow icon on mobile, "Download" text on desktop — this used to say "Preview" with an eye icon; renamed per requirements). Shows the full filled-in form summary (reused from the Review step's `#reviewSummary` HTML) and **shows** the Download PDF button, which calls `downloadAdoptionPdf()` to generate a client-side PDF via the browser.

**Modal layout (applies to both A and B):**
```
.modal-box (flex column, max-height: 82vh mobile / 85vh desktop, overflow: hidden)
 ├─ .modal-top       (sticky header: title + action buttons + Close, never scrolls)
 └─ .modal-scroll     (flex:1, overflow-y:auto — only this part scrolls)
```
The success modal (`.success-modal`, shown after submission with the adoption code) is visually similar but has **no** `.modal-top`/`.modal-scroll` split — it needed its own `overflow-y: auto` added directly because `.modal-box`'s `overflow: hidden` would otherwise clip its "Check Status Now" button on short mobile screens.

**Lesson learned (repeated regression):** multiple past edits accidentally merged these two features — e.g. giving the file-preview popup the same "Application Preview" header and a visible Download PDF button, or restructuring `.modal-box` in a way that broke the success modal's ability to scroll. Any change to modal CSS/HTML must be tested against **all three** consumers: file preview, form preview, and the success modal — not just the one being worked on.

### 5.7 Success modal
Shown after successful submission. Displays the adoption code, a "Copy Code" button, an important notice box, and a "Check Status Now" button linking to `/adoptions/status/?code={code}`.

### 5.8 Required vs optional fields (business logic, not just UI)
- Rent/landlord fields (`landlord_allows`, `landlord_contact`) are optional — not everyone rents.
- `have_vet` is optional; if answered, `allow_contact_vet`'s label reads "**If you have a Veterinarian** would you allow us to speak with your Vet for health info?" and is itself optional — you can't require permission to contact a vet the applicant doesn't have.
- `sex_pref` (labelled "Confirm: I am adopting (Pet name, Breed/Type, Sex, Adoption ID, Adoption Fee)") is a **required, full-width `<textarea>`**, not a text input — it needs room for multiple pets' worth of detail when the multi-pet carousel is used. Placeholder: *"Fill details here, add multiple pets information if applicable..."*

### 5.9 Hamburger menu
Uses the site-standard implementation documented in Section 2 above (scroll-to-last-item CSS trick, body-scroll-lock JS, the duplicate-`padding`-declaration gotcha). Identical pattern used on all pages including this one — do not modify or reimplement.

### 5.10 Rich Text Editor component + expanded application questions

A reusable Quill.js rich-text-editor component (`initRichTextFields()`, Design System Section 25) was added and applied to the form's genuinely long-form, narrative fields — not every textarea converted to it. **7 fields use the RTE component:** `additional_info`, `past_animals`, `introduce_existing`, `handle_undesirable`, and 3 new questions added at the same time (`daily_routine`, `exercise_enrichment_plan`, `commitment_plan` — covering daily routine/alone-time, exercise/enrichment plans, and a long-term-commitment contingency plan). **Two fields were deliberately reverted to plain textareas after initial conversion** — `household_list` (a structured name/age roster, not narrative) and `sex_pref` (structured per-pet confirmation data, see Section 5.8) — since Design System Section 25's own guidance is that the RTE component is for narrative answers, not structured data entry.

The original `<textarea>` for each RTE field stays in the DOM as the real submitted value/source of truth (`class="rte-source"`, visually hidden once Quill mounts over it) — this is intentional, not dead code; it's what the form actually submits.

**HTML safety, since RTE fields store real formatted HTML rather than plain text:** the client review panel and PDF export render RTE values through an allowlist sanitizer rather than the plain-text `escapeHtml()` used elsewhere on the form. The backend (`adoption-application.php`) independently sanitizes the same 7 fields server-side before they reach `application.json` — this server-side pass is the one that actually matters for security, since a request can bypass client-side JS entirely. The admin notification email uses the same server-side-sanitized values rather than raw `htmlspecialchars()`, so admins see real formatting instead of literal escaped tags.

**Lesson learned:** the RTE component builds its own label inside its header (needed for the character-counter layout); the original `<label for="...">` above each converted field must be hidden once the editor mounts, or the same question text renders twice, stacked.

---

## 6. `adoption-application.php` — Backend

### 6.1 What it does, in order
1. Reads and validates `$_POST['adoption_code']` (see Section 4).
2. Saves uploaded documents to `$UPLOAD_BASE/{user_folder}/` (inside `/apply/uploads/adoption-applications/`).
3. Decodes the base64 signature from `$_POST['signature']` and writes it as a PNG file.
4. Receives the client-generated PDF (`$_FILES['screenshot_pdf']`) and saves it.
5. **Creates `/adoptions/applications/{code}/`** and writes:
   - `application.json` — every form field, plus `status: "PENDING"`, `applied_at`, `approved_at: null`, `checkout_url: null`, `rejection_notes: null`, and critically **`additional_pet_ids`** (array, captured from `$_POST['additional_pet_ids']`).
   - `metadata.json` — a lightweight subset (code, pet_id, pet_name, applicant name/email, status, created_at) for quick lookups.
6. Sends two emails: an HTML confirmation to the applicant (with the adoption code, "What Happens Next" steps, footer links) and a plain notification to admin addresses, both without exposing raw PHP errors.
7. Returns JSON `{ success: true, submission_id, adoption_code }`.

**Lesson learned:** `additional_pet_ids` was initially not captured at all — the frontend carousel worked and PHP saved everything else, but the status page's multi-pet carousel had nothing to read because this one field was missing from `application.json`. When adding any new frontend field that needs to reach the status page, always confirm it's captured in this file's `$applicationData` array, not just present in the form's HTML.

### 6.2 Email content rules
- Uses the label "**Adoption Code**", not "Submission ID" (an earlier version used inconsistent terminology between the popup and the email).
- Logo path: `/assets/images/logo-petcenter.png` (must match the same asset path used site-wide — an earlier version pointed to a nonexistent `/logo.png`).
- No Stripe payment icon/branding — was explicitly removed.
- Footer links point to `docs.petcenterco.com/...` pages (privacy policy, returns & refunds, contact-us), consistent with every other page's footer.

---

## 7. `adoptions/status/index.html` — Status Dashboard

Full-page dashboard at `/adoptions/status/?code=AD-XXXXX-XXXX`. Auto-looks-up the code from the URL on load; also has its own input+button for manual lookup.

### 7.1 Lookup mechanism
Pure client-side fetch of the **static JSON file** — no PHP endpoint involved for reading status:
```
fetch('/adoptions/applications/' + code + '/application.json')
```
This is intentional and important: status checking never touches the PHP backend, it just reads the file the PHP already wrote at submission time. If status can change (e.g. an admin manually edits `application.json` to APPROVED), the dashboard picks it up on next lookup with zero backend code needed.

### 7.2 Status states
- **PENDING** — badge + "under review, check back in 1–2 hours" message.
- **APPROVED** — green checkmark animation, full payment-options info box (BNPL, Pay on Delivery, Installment Plans — exact wording is business-approved copy, don't paraphrase it), and a "Proceed to Checkout" button using `checkout_url` from the JSON. This button **only ever appears here**, never on the application form. Also shows who approved the application and when, in this exact format:
  ```
  Approved by: John Doe (Adoption Manager)
  Approved on: dd/mm/yy, hour:minute:second
  ```
  Position (the staff member's role/title) renders directly under the name, in small font. Both values are entered by the approving admin in the admin dashboard's Approve flow and stored alongside the rest of the application data — see `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s `update-adoption.php` documentation. **Lesson learned:** the "Approved on" date used to render blank — this whole feature (the date actually displaying, plus the two new fields) was added together, not just a display fix.
- **REJECTED** — shows `rejection_notes` from the JSON verbatim, plus a prompt to use the floating contact button.

### 7.3 Multi-pet carousel (mirrors the one in the application form)
- Reads `application.json`'s `additional_pet_ids` array.
- Primary pet (from `pet_name`/`pet_image_url`/etc. already in the JSON) is always slide 0.
- Each additional pet ID is resolved to a category via the same prefix map used in the application form (`DOG→dogs`, `CAT→cats`, `BIRD→birds`, `REP→reptiles`, `SM→small-mammals`, `LVS→livestocks`, `AQU→aquariums`), then fetched live from its own `pet.json`, and injected as an additional slide with a loading placeholder shown while the fetch is in flight.
- Nav arrows/dots only render when there's more than one pet.
- **Adoption fee fades between pets:** each slide stores its own fee in a `data-fee` attribute; when the carousel's `goTo(idx)` runs, the `#dashFeeValue` element fades to `opacity:0`, swaps text after 350ms, fades back to `opacity:1`. This was added specifically because the fee display used to stay locked to the primary pet's fee even when browsing a different pet's slide.

### 7.4 Homepage integration (`index.html`)
The homepage has a **deliberately minimal** popup version of this same lookup (`#statusOverlay` / `checkAdoptionStatus()`), showing only status + basic pet/adopter info, with a "Visit Status Dashboard" button in a sticky popup footer linking to this full page. This is intentional UX layering — keep the homepage popup light, put full detail only here.

---

## 8. Shared Design System (all 4 files)

- **Colors:** Navy `#10214b`, Gold `#d7bd88`, Orange `#ED7624`.
- **Fonts:** `Cormorant Garamond` (headings/serif), `Sora` (body/UI).
- **Header:** fixed, dual logo (`.pc-header__logo--desktop` full wordmark / `.pc-header__logo--mobile` icon-only, swapped at 900px), nav list with a dropdown ("Our Pets"), Donate button styled distinctly (`.nav-donate`).
- **Hamburger menu:** identical implementation across every page — see Section 2 above (mandatory, identical on every page — do not reinvent this per-page).
- **Footer:** 5-column grid (`grid-template-columns: 2fr 1fr 1.2fr 1fr 1.3fr`) — brand, Our Pets, Information, Support, Visit Us (with the full address including "Golden Triangle Shopping Center, Section #257"). An earlier 4-column template (`2fr 1fr 1fr 1.5fr`) with 5 actual columns was a recurring bug — always verify the column count in the grid CSS matches the number of `.pc-footer__col` divs in the HTML.
- **No emoji anywhere in code** — every status/icon indicator uses inline SVG. This is a hard project rule, not a style preference.

---

## 9. Quick Reference: "Where do I fix X?"

| Symptom / Request | File to open |
|---|---|
| Adoption code format, generation, or mismatch | `adoption-application.html` (`generateAdoptionCode()`) + `adoption-application.php` (validation block, Section 4) |
| A field isn't saving / isn't in the emailed data | `adoption-application.php`, `$applicationData` array — confirm the key exists there |
| Multi-pet "add another pet" carousel behavior (form side) | `adoption-application.html`, Section 5.3 — `carouselPets[]`, `buildPetSlide()`, `addPetBtn` handler |
| Multi-pet carousel on the status page | `adoptions/status/index.html`, Section 7.3 — `initStatusCarousel()`, `buildPetSlideHtml()` |
| File upload fails / needs retry / wrong token field name | `adoption-application.html`, Section 5.4 — `preUploadFile()`, `renderField()` — and cross-check `upload-temp.php` token field naming against the PHP |
| Any popup/modal looks wrong or overflowing on mobile | `adoption-application.html`, Section 5.6 — check which of the 3 consumers (file preview / form preview / success modal) you're actually affecting before changing shared `.modal-box` CSS |
| Preview vs Download button confusion | Section 5.6 — these are two different features sharing one modal; don't merge their logic |
| Status not updating / shows stale info | `adoptions/status/index.html`, Section 7.1 — confirm `application.json` on disk actually reflects the new status; there is no caching layer, it's a raw fetch |
| Payment info wording on APPROVED status | `adoptions/status/index.html`, Section 7.2 — this copy is business-approved, treat as fixed text |
| Homepage status popup vs full dashboard | Section 7.4 — homepage is intentionally minimal by design, not a bug |
| Hamburger menu scroll/behavior issues (any page) | Section 2 above — apply the documented fix exactly, don't improvise a new one |
| Footer column count/layout looks broken | Section 8 — verify grid-template-columns count matches actual `.pc-footer__col` divs |
| Email content/branding wrong | `adoption-application.php`, Section 6.2 |
| Path/folder errors (files saved in wrong place) | Section 1 — re-check the `/apply/` vs `/adoptions/` sibling relationship and `dirname(__DIR__)` usage |
| "Approved by / Approved on" not showing on the status page | Section 7.2 — confirm `update-adoption.php` actually wrote `approved_by`/`approved_position`/`approved_at` on approval; see `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` Section 9 |
| Rich text editor question not saving formatting / shows raw HTML | Section 5.10 — check the field is one of the 7 RTE fields, that `rte-source` textarea is what's actually being submitted, and that server-side sanitization in `adoption-application.php` hasn't stripped something it shouldn't |
| A pet card or `pet-detail.html` doesn't show the "ADOPTED" badge when it should | Section 15.5 (cards) / Section 13.10 (detail page) — confirm `pet.json`'s `status` field is exactly `"Adopted"`, capital A |
| A new Livestocks or Aquariums pet's ID doesn't resolve correctly (carousel, status page, admin) | Section 15.0 — confirm the ID uses `LVS-`/`AQU-` and check for lingering old `REPTILE-`/`MAM-` prefixed pets elsewhere |
| Signin/rehome link still points to the old `/rehome/` path anywhere | Section 18 and Section 2.6 — `/rehome/` is retired sitewide, every reference should be `/signin/` |
| Blog category filters or tags not working | Section 19.1/19.4 — confirm `blogs-data.js`'s exported helpers are actually being called, not re-derived logic |
| Homepage "What's New" slide or Services page content needs updating | Section 20.2 / Section 21 |

---

## 10. `testimonials/index.html` — Reviews Page

Google-Business-Profile-styled reviews page at `adopt.petcenterco.com/testimonials/`. Source file is `Testimonials.html`; all CSS/JS is inline in that one file (no separate stylesheet/script files, aside from the two small PHP backends and the JSON data file).

### 10.1 How a review actually gets onto the page (two very different paths)

This is the single most important thing to understand about this file, because the two paths behave completely differently:

**Path A — Admin-added reviews (permanent, the real database).** An admin hand-edits `testimonials.json` directly (FTP/hosting file manager/text editor), adding a new object to the `testimonials` array following the schema in Section 10.3. Saves the file. Done — next page load, that review is live for everyone, permanently. There is no CMS or admin UI for this; it's a hand-edited JSON file by design.

**Path B — Visitor-submitted reviews (session-only, never permanent on its own).** A visitor opens the review-submission bottom sheet, fills it in, hits Post Review. JS `fetch()`-POSTs to `save-testimonial.php`, which validates the input and **appends one line to `testimonials.log`** — it does **not** write to `testimonials.json`. The PHP returns the new review object; the browser pushes it into the in-memory `ALL_TESTI` array so it appears on the page immediately, indistinguishable from a real review. If the visitor hard-refreshes, it vanishes from the page (it was only ever in browser memory) but still exists forever in `testimonials.log` for the admin to read and manually promote into `testimonials.json` later if they choose to.

**Lesson learned:** this two-path split is intentional, not a bug — it lets anyone "try" leaving a review and see it appear live with zero moderation bottleneck, while nothing goes permanently public without a human reading it first. If a future request asks "why did my review disappear after refresh," this is why — check `testimonials.log`, the submission is there.

### 10.2 `testimonials.json` — schema

**Top level:**
```json
{ "business": { ... }, "testimonials": [ { ... }, ... ] }
```

**`business` object:**
- `type` — free text shown next to the star rating (e.g. "Pet Shelter").
- `location.timezone` — IANA timezone string (e.g. `America/Los_Angeles`). Drives the live Open/Closed calculation via the browser's `Intl` API — **not hardcoded anywhere in the JS**. Move the business, change this one field, everything recalculates automatically.
- `location.state` / `location.country` — not displayed; exist only as human-readable context for whoever edits the file to pick the correct `timezone`.
- `hours` — one entry per day, 24-hour `HH:MM`. **If `hours` is empty/missing entirely, the page defaults to "Open 24/7" in green.**

**Each review object:**

| Field | Type | Notes |
|---|---|---|
| `id` | integer | Must be unique across the file |
| `name`, `email` | string | `email` not displayed publicly |
| `postedAt` | ISO 8601 datetime **with timezone offset** | Drives the live "time ago" system — see Section 10.4. This is the field that most often gets misunderstood; read that section before touching it |
| `rating` | integer 1–5 | Drives stars, overall average, sort by highest/lowest |
| `text` | string | Review body — the only field the search feature searches |
| `initials`, `color` | string | Avatar circle. `color` should follow the rating-based palette below |
| `topics` | array of strings | Manually chosen short phrases (e.g. `"helpful staff"`) — powers the Filters panel's Topics chips (see Section 10.5 for why this is manual, not auto-extracted) |
| `helpfulYes`, `helpfulNo` | integers | Thumbs up/down vote counts. New reviews start both at `0` |
| `reply` | object or `null` | Admin/staff response. Omit or `null` if none yet |
| `reply.verified` | boolean | Controls the blue checkmark next to the admin's name |
| `reply.avatar` | string (path) or omitted | e.g. `"profiles/admin1.png"`. Falls back to colored initials automatically if omitted or the image fails to load — never a broken image |

**Avatar color palette (convention, not enforced by code) — tied to rating:**

| Rating | Color |
|---|---|
| 5★ | `#10b981` (green) |
| 4★ | `#0ea5e9` (blue) |
| 3★ | `#9ca3af` (gray) |
| 2★ | `#ef4444` (red) |
| 1★ | `#dc2626` (dark red) |

**Batch distribution convention:** roughly **2× 5★, 2× 4★, 2× 3★, 1× 2★, 1× 1★ per 8 reviews** — genuine believable variation, not a wall of 5-stars. Negative reviews should still usually praise the *pet* while criticizing *process/communication/staff*, mirroring how real adoption complaints actually read.

### 10.3 Live "time ago" system

Every review stores exactly one moment in time — `postedAt`, a full ISO 8601 datetime. Every page load, JS computes `Date.now() - postedAt` fresh and converts it to "just now" / "5 minutes ago" / "3 hours ago" / "2 days ago" / "4 months ago" / "1 year ago". **This is a live calculation, recomputed on every view — the JSON never stores the words "2 days ago."** Under 1 month renders in a small green pill; 1 month+ renders as plain gray text.

**Lesson learned (recurring point of confusion, not an actual bug):** because `postedAt` is a fixed point in time, the displayed text keeps advancing as real time passes — a review that says "2 days ago" today will correctly say "3 days ago" tomorrow with zero code changes. This has been reported as "broken" more than once by someone expecting a hand-typed timestamp to somehow stay frozen at "just now" — it isn't a caching bug, it's the feature working exactly as designed. If you're asked to "fix" a timestamp that "won't update," check whether it actually needs updating (has real time actually passed) before assuming the calculation is wrong.

### 10.4 Topics system (Filters panel)

Topics are **manually curated per review**, not auto-detected from the review text. Each review's `topics` array is a short list of human-chosen phrases.

**Lesson learned:** an earlier version tried auto-extracting topics via word-frequency analysis on the review text. It was reverted after testing showed it produced meaningless junk phrases like `"the staff was"` or `"but the application"` — a frequency-count algorithm has no way to know which words form a meaningful theme versus which are just grammatically common. Do not reintroduce auto-extraction; the manual `topics` field is simpler and completely accurate at the cost of needing the admin to type a few phrases per review. The Filters panel aggregates every review's `topics`, counts mentions, and renders chips labeled `"phrase N"`. Selecting one or more + Apply filters the grid to any review matching **at least one** selected topic (OR logic).

### 10.5 Helpful votes (thumbs up/down)

Sits at the end of each review card, after the admin reply if there is one. Uses thumbs icons (deliberately simpler than Google's newer emoji-reaction system — a conscious choice to avoid over-engineering a static page). Clicking either button is permanent for that browser session (both buttons disable immediately) and `fetch()`-POSTs to `vote-helpful.php`, which genuinely writes the updated count back into `testimonials.json` (with file-locking against two simultaneous votes racing each other). Displayed text is vote-specific — *"X people agreed to this"* / *"Y people didn't agree to this"* — not one ambiguous combined count.

**Lesson learned:** an earlier version showed one generic "X people found this helpful" line regardless of which button was clicked, so clicking "No" still displayed a positive-sounding message. If you touch this feature, keep the yes/no counts and their wording visibly separate.

### 10.6 Pagination

Fixed at 10 reviews per page. Purely client-side — all reviews are fetched once into memory, Next/Prev just re-slices that array, no server round-trip, no reload. Recalculates page count automatically whenever the active (filtered/searched) list changes size, including immediately after a new review is submitted.

### 10.7 Business info row (Google-Maps-card look)

One row: overall star rating + review count, business type, live Open/Closed status with the correct closing/opening time — all computed live from `testimonials.json`'s `business` object using the browser's `Intl` timezone API, no server dependency.

### 10.8 Hamburger menu, header, footer

Uses the site-standard implementation documented in Section 2 and Section 8 above — **identical markup/CSS/JS to every other page, do not modify or reimplement.** This was not always true: an earlier version of this page had its own bespoke header (no dual-logo swap, no `pc-header--solid`, non-standard element IDs `pcHam`/`pcMobMenu`, `display:none`/`block` toggle instead of the documented `max-height`/`overflow-y` scroll fix, no body-scroll-lock) and a 4-column inline-styled footer instead of the standard 5-column `.pc-footer` system. Both were replaced with the exact site-standard versions — if you ever see the old `pcHam`/`pcMobMenu` IDs or inline-styled footer divs reappear on this page, that's a regression back to the pre-standardization version, not an intentional variant.

**Watch for (not a bug, just a real interaction to be aware of):** this page's own Filters/Search/Review-form bottom sheets use their own `pcLockBodyScroll()`/`pcUnlockBodyScroll()` system (Section 10.9 below), which is more robust than the hamburger menu's plain `document.body.style.overflow` toggle (see Section 2.4). Both systems independently touch `document.body`'s scroll state. In normal use they're never open at the same time, so this hasn't caused an actual problem — but if a future change ever lets the hamburger and one of the bottom sheets be open simultaneously, check both systems aren't fighting over `document.body.style` before assuming either one is broken.

### 10.9 Bottom Sheet + Filter Button + Search Bar

This page is the origin of a reusable, cross-page component — see **Section 11** for the full copy-paste-ready spec (markup, CSS, JS, and a list of real mistakes made building it the first time, so they aren't repeated). Summary of what it does here: a pill-shaped Filter button and a circular Search button, both opening either a dark full-width bottom sheet (Filters, or the Review-submission form) or an inline debounced search bar. Full-width edge-to-edge on both mobile and desktop — an earlier version made desktop use a centered 560px card instead, which was reverted; full-width-everywhere is the correct, current behavior.

---

## 11. Reusable Feature: Bottom Sheet + Filter Button + Search Bar

A complete, copy-paste-ready specification for the "pull-up panel" system built for the testimonials page — written so it can be dropped into a different page/project and produce identical behavior on both mobile and desktop, without re-deriving anything from scratch. This system has three parts that work together: a pill-shaped **Filter button** that triggers a bottom sheet, the **bottom sheet** itself (dark themed, full-width, drag-to-close, reusable for both a Filters UI and a Form UI), and a circular **Search button** that reveals an inline search bar with debounced live filtering.

### 11.1 Read this first: mistakes made during the original build (avoid repeating these)

These are real bugs that shipped on the testimonials page and had to be fixed later. Build from the corrected version below, not the naive first-instinct version.

1. **`document.body.style.overflow = 'hidden'` is NOT enough to prevent scroll/layout issues when a sheet is open on mobile.** It doesn't lock scroll *position*, and on iOS Safari / Chrome Android with dynamic address bars, the page can still visibly reflow or expand behind a `position: fixed` sheet. The correct fix is a real scroll-lock: pin `<body>` with `position: fixed` at its exact current scroll offset, then restore that exact offset on close. Working code is in Section 11.4 — use it as-is.

2. **Don't hardcode a different sheet width for desktop unless explicitly asked.** An early version made desktop use a centered 560px-wide card instead of full-width; this was reversed — full-width, edge-to-edge, on every screen size, is the corrected, intended behavior. Default to full-width-everywhere unless told otherwise.

3. **When inlining an SVG logo/icon with many `<path>` elements, copy the file's raw bytes mechanically — never hand-retype the path data.** Manually reconstructing SVG paths from memory is exactly how a real bug happened here: a duplicated/mismatched path caused a company logo to render with a visibly wrong extra shape. Copy static assets byte-for-byte, or reference them as external files — don't transcribe path coordinates by hand.

4. **Test "drag to close" with both touch and mouse, without breaking normal clicks inside the sheet's form fields.** The drag listener should generally only *start* a drag from the sheet's handle bar for mouse input (desktop), while still allowing a full-sheet touch-drag on mobile — otherwise clicking into a text input inside the sheet can accidentally trigger a drag.

5. **"Clear" inside a filter panel should reset in-progress selections but NOT close the panel.** Only "Apply" commits and closes. Users expect to clear and immediately pick something else without the sheet slamming shut.

6. **The "×" inside a search bar should both clear the text AND collapse/hide the search bar** — not just clear the text while leaving the bar open. A real shipped bug had the × clear the input while the bar stayed open, which reads as broken.

7. **Give buttons real contrast against the actual page background — don't reuse two similarly-light, similarly-muted colors for a button's background and its own icon/text.** An earlier version used a warm tan background with a gold icon on a cream page background; all three colors were close enough in lightness that the button was nearly invisible. Dark background + light icon/text (or vice versa) against a light page is the safe default.

8. **Don't hand-assemble nested-quoted HTML strings inside an inline `onerror` attribute.** A later variant of this page (the "v2" experimental redesign) hit this same class of bug again when building image-fallback handlers — string-concatenating an `onerror="...'<div class=\"...\">...'..."` attribute is fragile and easy to get wrong. Use a named JS function called via `onerror="myFallbackFn(this)"` instead, built with `document.createElement`/DOM APIs, never string-built nested-quote HTML.

### 11.2 Design tokens (swap for your own brand, structure stays the same)

```css
/* Buttons (filter pill, search circle) */
--btn-bg: #10214b;        /* dark navy - must contrast clearly against the page background */
--btn-fg: #d7bd88;        /* gold/accent - icon and text color */
--btn-hover-bg: #1a3070;  /* lighter navy on hover */
--btn-hover-border: #d7bd88;

/* Sheet (bottom pull-up panel) */
--sheet-bg: #121212;              /* near-black */
--sheet-text: #ffffff;
--sheet-muted-text: rgba(255,255,255,.6);
--chip-bg: #1e1e1e;
--chip-border: rgba(255,255,255,.15);
--chip-selected-bg: #d7bd88;       /* same gold accent */
--chip-selected-text: #10214b;     /* same navy */
--sheet-clear-border: #d0c3ba;     /* warm gray */
--sheet-apply-bg: #d7bd88;
--sheet-apply-hover-bg: #b89968;   /* darker gold on hover/press */

/* Search bar (light theme, sits on the page, not inside the dark sheet) */
--search-border: #d0c3ba;
--search-border-focus: #b89968;
```

The key relationship to preserve: the same gold accent color is used for (a) the filter/search button's icon/text on its dark background, (b) a selected chip's background inside the dark sheet, and (c) the sheet's primary Apply button — this is what makes the button, the panel it opens, and the panel's primary action feel like one connected system.

### 11.3 Part A — The Filter button (pill-shaped trigger)

```html
<button class="pc-filter-btn" id="pcFilterBtn" type="button" aria-haspopup="true" aria-expanded="false">
  <svg class="pc-filter-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
    <line x1="4" y1="6" x2="20" y2="6"/><circle cx="9" cy="6" r="2" fill="currentColor" stroke="none"/>
    <line x1="4" y1="12" x2="20" y2="12"/><circle cx="15" cy="12" r="2" fill="currentColor" stroke="none"/>
    <line x1="4" y1="18" x2="20" y2="18"/><circle cx="11" cy="18" r="2" fill="currentColor" stroke="none"/>
  </svg>
  Filters
</button>
```

```css
.pc-filter-btn {
  display: inline-flex; align-items: center; gap: 7px;
  background: #10214b; color: #d7bd88; border: 1.5px solid transparent;
  border-radius: 999px; padding: 9px 18px;
  font-family: 'Sora', sans-serif; font-size: 13.5px; font-weight: 600;
  cursor: pointer;
  transition: border-color .2s ease, transform .15s ease, background .2s ease;
}
.pc-filter-btn:hover, .pc-filter-btn:focus-visible, .pc-filter-btn.active {
  border-color: #d7bd88; background: #1a3070; transform: translateY(-1px);
}
.pc-filter-btn:active { transform: translateY(0); }
.pc-filter-icon { flex-shrink: 0; }
```

Apply `.active` via JS while the sheet is open, so the button visibly stays "pressed" for the whole time the panel is up, not just during the click.

### 11.4 Part B — The Bottom Sheet ("pull-up panel")

One sheet pattern, reused for multiple purposes (a Filters UI and a completely different Review-submission form UI on the testimonials page) by just changing what's inside `.pc-sheet-body`. The chrome (backdrop, handle, header, open/close animation, drag-to-close, scroll-lock) is identical and shared.

**Markup skeleton (reusable for ANY sheet content):**
```html
<div class="pc-sheet-backdrop" id="pcYourSheetBackdrop"></div>

<div class="pc-your-sheet" id="pcYourSheet" role="dialog" aria-modal="true" aria-label="Descriptive label">
  <div class="pc-sheet-handle"></div>
  <div class="pc-sheet-header">
    <h3>Sheet Title</h3>
    <button class="pc-sheet-close" id="pcYourSheetClose" aria-label="Close" type="button">&times;</button>
  </div>
  <div class="pc-sheet-body">
    <!-- ANY content: filter chips, a form, whatever -->
  </div>
  <!-- Footer is optional — only include for Clear/Apply-style actions -->
  <div class="pc-sheet-footer">
    <button class="pc-sheet-clear" type="button">Clear</button>
    <button class="pc-sheet-apply" type="button">Apply</button>
  </div>
</div>
```

Use a distinct class per sheet instance (e.g. `.pc-filter-sheet`, `.pc-review-sheet`) so multiple sheets can exist on the same page independently, but give them all the shared structural CSS below (comma-separated selectors).

**CSS — sheet chrome (core reusable piece):**
```css
.pc-sheet-backdrop {
  position: fixed; inset: 0; background: rgba(0,0,0,.55);
  opacity: 0; visibility: hidden; transition: opacity .3s ease;
  z-index: 1400;
}
.pc-sheet-backdrop.show { opacity: 1; visibility: visible; }

/* List every sheet class you create, comma-separated, here */
.pc-filter-sheet, .pc-review-sheet {
  position: fixed; left: 0; right: 0; bottom: 0;
  background: #121212; border-radius: 18px 18px 0 0;
  z-index: 1500;
  max-height: 82vh; max-height: 82dvh; /* vh fallback, dvh overrides where supported —
                                          dvh prevents size jumps when the mobile
                                          browser's address bar shows/hides */
  display: flex; flex-direction: column;
  transform: translateY(100%); /* fully offscreen by default */
  transition: transform .35s cubic-bezier(.32,.72,0,1);
  box-shadow: 0 -8px 40px rgba(0,0,0,.4);
}
.pc-filter-sheet.open, .pc-review-sheet.open { transform: translateY(0); }

/* Do NOT add a desktop media query that shrinks this to a centered card —
   see mistake #2 above. Full-width edge-to-edge on every screen size. */

.pc-sheet-handle {
  width: 40px; height: 4px; background: rgba(255,255,255,.25);
  border-radius: 4px; margin: 10px auto 0; flex-shrink: 0;
}
.pc-sheet-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 20px 8px; flex-shrink: 0;
}
.pc-sheet-header h3 { font-family: 'Sora', sans-serif; font-size: 18px; color: #fff; font-weight: 700; }
.pc-sheet-close {
  background: none; border: none; color: #fff; font-size: 22px; line-height: 1;
  cursor: pointer; padding: 4px 8px; opacity: .8;
}
.pc-sheet-close:hover { opacity: 1; }
.pc-sheet-body { overflow-y: auto; padding: 8px 20px 20px; flex: 1; }

.pc-sheet-footer {
  display: flex; gap: 12px; padding: 16px 20px 22px;
  border-top: 1px solid rgba(255,255,255,.08); flex-shrink: 0;
}
.pc-sheet-clear, .pc-sheet-apply {
  flex: 1; padding: 13px; border-radius: 999px;
  font-family: 'Sora', sans-serif; font-size: 14px; font-weight: 700;
  cursor: pointer; border: 1.5px solid transparent;
}
.pc-sheet-clear { background: transparent; border-color: #d0c3ba; color: #d7bd88; }
.pc-sheet-clear:hover { border-color: #b89968; }
.pc-sheet-apply { background: #d7bd88; color: #10214b; }
.pc-sheet-apply:hover { background: #b89968; color: #fff; }
```

**Optional content pattern — filter chips (multi-select with checkmark):**
```css
.pc-chip-row { display: flex; flex-wrap: wrap; gap: 10px; }
.pc-chip {
  background: #1e1e1e; border: 1px solid rgba(255,255,255,.15);
  color: #e5e5e5; border-radius: 999px; padding: 9px 16px;
  font-family: 'Sora', sans-serif; font-size: 13px; cursor: pointer;
  display: flex; align-items: center; gap: 6px; transition: all .15s ease;
}
.pc-chip:hover { border-color: rgba(215,189,136,.5); }
.pc-chip.selected { background: #d7bd88; color: #10214b; border-color: #d7bd88; font-weight: 600; }
.pc-chip.selected::before { content: "✓"; font-weight: 700; }
```
Toggling `.selected` on click is enough — the checkmark appears automatically via `::before`.

**Behavior rule if you use Clear/Apply:** chip selections should be held in a **draft/temporary state** while the sheet is open — not applied to whatever the sheet is filtering until the user taps Apply. Apply commits the draft and closes. Clear resets the draft but does **not** close the sheet (see mistake #5). The × close button, backdrop-click, and drag-to-close should all discard any unsaved draft changes and just close, without applying them.

**JS — scroll lock (use this exact code, don't substitute `overflow:hidden`):**
```js
var _pcScrollLockY = 0;
var _pcScrollLockCount = 0; // supports multiple sheets that could overlap

function pcLockBodyScroll() {
  _pcScrollLockCount++;
  if (_pcScrollLockCount > 1) return;
  _pcScrollLockY = window.scrollY || window.pageYOffset || 0;
  document.body.style.position = 'fixed';
  document.body.style.top = (-_pcScrollLockY) + 'px';
  document.body.style.left = '0';
  document.body.style.right = '0';
  document.body.style.width = '100%';
}

function pcUnlockBodyScroll() {
  _pcScrollLockCount = Math.max(0, _pcScrollLockCount - 1);
  if (_pcScrollLockCount > 0) return;
  document.body.style.position = '';
  document.body.style.top = '';
  document.body.style.left = '';
  document.body.style.right = '';
  document.body.style.width = '';
  window.scrollTo(0, _pcScrollLockY);
}
```
Also add globally, once, regardless of sheets:
```css
html { overflow-x: hidden; width: 100%; }
body { overflow-x: hidden; width: 100%; position: relative; }
```
This prevents residual horizontal-expansion edge cases across phone types/browsers.

**JS — drag-to-close:**
```js
function enableDragToClose(sheetEl, handleEl, onClose) {
  var startY = 0, currentY = 0, dragging = false, sheetHeight = 0;

  function pointerDown(e) {
    dragging = true;
    startY = (e.touches ? e.touches[0].clientY : e.clientY);
    currentY = startY;
    sheetHeight = sheetEl.offsetHeight || 1;
    sheetEl.style.transition = 'none';
  }
  function pointerMove(e) {
    if (!dragging) return;
    currentY = (e.touches ? e.touches[0].clientY : e.clientY);
    var delta = Math.max(0, currentY - startY); // only allow dragging downward
    sheetEl.style.transform = 'translateY(' + delta + 'px)';
  }
  function pointerUp() {
    if (!dragging) return;
    dragging = false;
    sheetEl.style.transition = '';
    var delta = Math.max(0, currentY - startY);
    if (delta > sheetHeight * 0.25) { // dragged more than 25% of sheet height -> close
      sheetEl.style.transform = '';
      onClose();
    } else {
      sheetEl.style.transform = ''; // snap back open
    }
  }

  [handleEl, sheetEl].forEach(function(el) {
    if (!el) return;
    el.addEventListener('touchstart', pointerDown, { passive: true });
    el.addEventListener('mousedown', function(e) {
      // Mouse drag only starts from the handle — prevents interfering with
      // normal clicks/typing inside form fields elsewhere in the sheet.
      if (el === handleEl) pointerDown(e);
    });
  });
  document.addEventListener('touchmove', pointerMove, { passive: true });
  document.addEventListener('mousemove', pointerMove);
  document.addEventListener('touchend', pointerUp);
  document.addEventListener('mouseup', pointerUp);
}
```

**JS — wiring a sheet instance together:**
```js
(function(){
  var triggerBtn = document.getElementById('pcFilterBtn');
  var sheet = document.getElementById('pcYourSheet');
  var backdrop = document.getElementById('pcYourSheetBackdrop');
  var closeBtn = document.getElementById('pcYourSheetClose');

  function openSheet() {
    sheet.classList.add('open');
    backdrop.classList.add('show');
    triggerBtn.classList.add('active');
    pcLockBodyScroll();
  }
  function closeSheet() {
    sheet.classList.remove('open');
    backdrop.classList.remove('show');
    triggerBtn.classList.remove('active');
    pcUnlockBodyScroll();
  }

  triggerBtn.addEventListener('click', openSheet);
  closeBtn.addEventListener('click', closeSheet);
  backdrop.addEventListener('click', function(e){ if (e.target === backdrop) closeSheet(); });
  enableDragToClose(sheet, sheet.querySelector('.pc-sheet-handle'), closeSheet);
})();
```
This exact pattern is what both the Filters sheet and the Review-form sheet use on the testimonials page — only the element IDs differ. Copy this IIFE once per sheet instance.

### 11.5 Part C — Search button + inline search bar

```html
<button class="pc-search-btn" id="pcSearchBtn" aria-label="Search" type="button">
  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
    <circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
  </svg>
</button>

<!-- Placed in normal page flow, directly below wherever the button sits -->
<div class="pc-search-bar-wrap" id="pcSearchBarWrap">
  <div class="pc-search-bar">
    <input type="text" id="pcSearchInput" placeholder="Search..." autocomplete="off">
    <button class="pc-search-clear" id="pcSearchClear" aria-label="Clear search" type="button">&times;</button>
  </div>
</div>
```

```css
.pc-search-btn {
  width: 38px; height: 38px; border-radius: 50%;
  background: #10214b; color: #d7bd88; border: 1.5px solid transparent;
  display: flex; align-items: center; justify-content: center; cursor: pointer;
  transition: border-color .2s ease, transform .15s ease, background .2s ease;
}
.pc-search-btn:hover, .pc-search-btn.active { border-color: #d7bd88; background: #1a3070; transform: translateY(-1px); }
.pc-search-btn:active { transform: translateY(0); }

.pc-search-bar-wrap {
  max-height: 0; opacity: 0; overflow: hidden;
  transition: max-height .35s ease, opacity .35s ease; /* slow, deliberate fade+expand */
  margin-top: 0;
}
.pc-search-bar-wrap.open { max-height: 70px; opacity: 1; margin-top: 14px; }
.pc-search-bar {
  display: flex; align-items: center; gap: 10px;
  border: 1.5px solid #d0c3ba; border-radius: 999px;
  padding: 10px 18px; background: #fff;
  transition: border-color .2s ease;
}
.pc-search-bar:focus-within { border-color: #b89968; }
.pc-search-bar input {
  flex: 1; border: none; outline: none;
  font-family: 'Sora', sans-serif; font-size: 14px; color: #10214b; background: transparent;
}
.pc-search-bar input::placeholder { color: #b0a69a; }
.pc-search-clear {
  background: none; border: none; font-size: 19px; cursor: pointer;
  color: #7a6d5f; line-height: 1; padding: 2px 4px;
}
.pc-search-clear:hover { color: #10214b; }

/* Optional: highlight the matched term inside results */
.pc-search-highlight {
  background: rgba(215,189,136,0.4); color: #10214b; font-weight: 700;
  border-radius: 3px; padding: 0 1px;
}
```
The `max-height` + `opacity` transition (rather than `display:none`/`block`) is what produces the smooth fade-and-expand-down reveal — animating `display` isn't possible in CSS.

```js
(function(){
  var searchBtn = document.getElementById('pcSearchBtn');
  var wrap = document.getElementById('pcSearchBarWrap');
  var input = document.getElementById('pcSearchInput');
  var clearBtn = document.getElementById('pcSearchClear');
  var debounceTimer = null;
  var DEBOUNCE_MS = 450; // wait this long after the last keystroke before searching

  searchBtn.addEventListener('click', function() {
    var isOpen = wrap.classList.toggle('open');
    searchBtn.classList.toggle('active', isOpen);
    if (isOpen) {
      setTimeout(function(){ input.focus(); }, 150);
    } else {
      input.value = '';
      onSearchChange(''); // your own function to re-render results with no filter
    }
  });

  // The × INSIDE the bar must both clear text AND close/collapse the bar (see mistake #6).
  clearBtn.addEventListener('click', function() {
    input.value = '';
    wrap.classList.remove('open');
    searchBtn.classList.remove('active');
    onSearchChange('');
  });

  input.addEventListener('input', function() {
    clearTimeout(debounceTimer);
    var value = input.value.trim();
    debounceTimer = setTimeout(function() {
      onSearchChange(value);
    }, DEBOUNCE_MS);
  });

  function onSearchChange(term) {
    // Replace with your actual filter/render logic. Keep it fast/local
    // (in-memory array filter) if possible — no need for a real network
    // request if all your data is already client-side.
  }
})();
```

**Optional "loading squares" animation while debounce is pending:**
```css
.pc-loading-squares { display: flex; gap: 8px; justify-content: center; padding: 60px 0; }
.pc-loading-squares span {
  width: 14px; height: 14px; background: #d7bd88; border-radius: 3px;
  animation: pcSquareMorph 1.2s ease-in-out infinite;
}
.pc-loading-squares span:nth-child(2) { animation-delay: .15s; }
.pc-loading-squares span:nth-child(3) { animation-delay: .3s; }
@keyframes pcSquareMorph {
  0%, 100% { transform: scale(1) rotate(0deg); border-radius: 3px; }
  50%      { transform: scale(1.3) rotate(45deg); border-radius: 6px; }
}
```
Swap into the results container the moment a keystroke happens, then swap in real results once the debounced search resolves.

### 11.6 Putting all three together (typical layout)

```html
<div class="controls-row">
  <div class="left-side-info"><!-- whatever contextual info belongs here --></div>
  <div class="controls-actions">
    <button class="pc-search-btn" id="pcSearchBtn">...</button>
    <button class="pc-filter-btn" id="pcFilterBtn">Filters</button>
  </div>
</div>

<div class="pc-search-bar-wrap" id="pcSearchBarWrap">...</div>

<!-- results grid/list here -->

<!-- sheets are usually placed at the end of <body>, since they're position:fixed anyway -->
<div class="pc-sheet-backdrop" id="pcFilterBackdrop"></div>
<div class="pc-filter-sheet" id="pcFilterSheet">...</div>
```
```css
.controls-row {
  display: flex; align-items: flex-start; justify-content: space-between;
  gap: 16px; flex-wrap: wrap;
}
.controls-actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
```

### 11.7 Checklist before calling this "done" on a new page

- [ ] Filter button and search button both use a **dark background with a clearly contrasting icon/text color** against the actual page background behind them (check on the real page, not in isolation).
- [ ] Sheet is **full-width edge-to-edge on both mobile and desktop** — no centered-card breakpoint.
- [ ] Sheet uses **`pcLockBodyScroll()`/`pcUnlockBodyScroll()`**, not plain `overflow:hidden`.
- [ ] Sheet closes via: × button, backdrop click, **and** dragging the handle down.
- [ ] If Clear/Apply exist: Clear resets but does not close; Apply commits and closes; × and backdrop-click discard without applying.
- [ ] Search bar's × both clears the input **and** collapses the bar.
- [ ] Search input is debounced.
- [ ] Any inlined SVG icon was copied byte-for-byte from its source file, never hand-retyped.
- [ ] Any image-fallback `onerror` handler calls a named JS function, not a hand-assembled nested-quoted HTML string.
- [ ] Tested on an actual narrow mobile viewport and a wide desktop viewport, not just one.

---

## 12. Quick Reference addendum: Testimonials-specific symptoms

| Symptom / Request | File to open |
|---|---|
| A review timestamp "won't update" / stuck on an old-looking value | `testimonials.json` — check the actual elapsed real time first (Section 10.3); this is very often not a bug |
| New review disappeared after page refresh | Expected for visitor-submitted reviews (Section 10.1, Path B) — check `testimonials.log`, not `testimonials.json` |
| Topic filter chips show junk/nonsense phrases | Do not reintroduce auto-extraction (Section 10.4) — topics must stay a manually-curated `topics` array per review |
| Thumbs up/down shows the wrong message for the vote given | `vote-helpful.php` + the vote-rendering JS in `Testimonials.html`, Section 10.5 — yes/no text must stay separate |
| Open/Closed status wrong for the business's actual hours | `testimonials.json`'s `business.hours` + `business.location.timezone` — Section 10.2; nothing is hardcoded, the data is the fix |
| Testimonials page header/footer/hamburger looks different from other pages | Section 10.8 — must match Section 2/8 exactly; check for the old `pcHam`/`pcMobMenu` IDs or inline-styled footer divs as a sign of regression |
| Building a new pull-up panel / filter+search UI on another page | Section 11 — copy the spec as-is, read the mistakes list (11.1) first |

---

## 13. `pet-detail.html` — Individual Pet Detail Page

Individual listing page for one pet (e.g. `adopt.petcenterco.com/pets/detail/?pet_id=DOG-00001&category=dogs`). Two versions exist on disk: `pet-detail.html` (production) and `pet-detail-v2.html` (an experimental branch — every feature below started life in v2; check which file you've been asked to work on before editing, and never assume a v2 feature has been carried into production unless told so).

Structure: fixed header/hamburger menu (Section 2), a navy hero with the pet's name, an image gallery with lightbox, a fading Tips card, the main pet-info panel (Breed/Physical Traits/Behavior/Health/Compatibility/Actions, each a `.pc-info-section` of `.pc-info-row`s), a fixed bottom action bar (Section 2.5), and a "You Might Also Like" grid. Data comes from `/adoptions/pets/{category}/{pet_id}/pet.json` (see Section 1's folder tree) — there is no other data source; every feature below reads fields from that one object.

### 13.1 Long-value row wrapping (`Personality`, `Specify starter kit(s) if included`)

Some `.pc-info-row` values are long free text, not a short Yes/No. These two rows are given block layout instead of the default flex row, so the value starts inline right after the label and wraps like a normal paragraph using the row's full width, rather than being forced into a separate narrow right-aligned column:

```css
#petKitSpecifyRow, #petPersonalityRow { display: block !important; }
#petKitSpecifyRow #petKitSpecify, #petPersonalityRow #petPersonality { text-align: left; }
```

**Lesson learned:** the default `.pc-info-row` is `display:flex` with the value as a flex item — when a flex item wraps onto a second line, it locks to whatever width it happened to get on its *first* line, so a long value stays trapped in a narrow column even though the row has plenty of empty width below the label. This is not fixable by adjusting `flex-wrap`/`flex-basis` alone; it needs the row taken out of flex layout entirely for these two fields. Do not "simplify" this back to flex without re-testing a genuinely long value in both fields.

### 13.2 Video player — auto-detects portrait vs. landscape

Player: [Plyr](https://plyr.io) (MIT-licensed, loaded from cdnjs: `plyr.min.css` / `plyr.polyfilled.min.js`, version pinned at `3.8.4`). Chosen because pet videos are downloaded from Instagram and include both landscape feed posts and portrait Reels (~9:16) — a single fixed-aspect-ratio player box letterboxes one or the other.

`applyVideoOrientation(videoEl, frameEl, opts)` runs on the video's `loadedmetadata` event, reads the real `videoWidth`/`videoHeight`, and toggles `.is-portrait` / `.is-landscape` on the frame element:

- **Inline player** (`#petVideoFrame`): class toggle drives CSS — `.is-landscape` keeps the standard wide 16:9-ish box (max-width 700px), `.is-portrait` switches to a tall capped-width "reel" box (max-width 380px, `aspect-ratio: 9/16`).
- **Fullscreen modal** (opened via `openVideoPlayer()`): no fixed CSS classes to hook into (the modal is built fresh each time), so `opts.mode === 'modal'` applies the sizing as inline styles directly instead (max-width 420px portrait / 900px landscape).

**Lesson learned:** the very first version of this measured `scrollHeight`/`clientHeight`-style properties before the video's metadata had actually loaded, always reading `0`. `loadedmetadata` is the correct event — don't try to read video dimensions any earlier (e.g. immediately after setting `src`).

### 13.3 Reading-time-based fading Tips card

Replaces a fixed-interval rotating banner. Each tip's on-screen duration is computed from its own word count instead of one universal timer, so a short tip doesn't linger and a long tip isn't cut off before it can be read:

```js
var WORDS_PER_MINUTE = 238;      // Brysbaert et al. 2019 meta-analysis average for adult
                                  // silent reading of non-fiction — not the older, commonly
                                  // cited but inflated 300 WPM figure
var MIN_DURATION_MS = 3500;      // floor, so even a one-word tip is on screen long enough to register
var READING_BUFFER_MS = 900;     // margin so a tip doesn't fade right as reading finishes
```

`estimateReadTime(text)` = `max(MIN_DURATION_MS, (wordCount / WORDS_PER_MINUTE) * 60000 + READING_BUFFER_MS)`.

**Design, not just timing:** the card itself was redesigned, not just recolored — gold gradient background, a left accent spine, a large faint quote-mark watermark, a live "`X / N`" counter, and progress dots (one per tip, active dot elongates into a pill). All four of these update together from the same `showTip(index)` call — if you add a tip, nothing else needs manual updating, the dot count and counter text are built/derived from the tip list at runtime.

**Manual controls:** prev/next arrow buttons, clickable dots, and left/right touch swipe on the text area (swipe detection requires the horizontal motion to clearly exceed vertical motion, so it doesn't fire during a vertical scroll inside a long tip). Hovering (desktop) or touching (mobile) the card pauses auto-advance and any in-progress auto-scroll; releasing resumes both — implemented as a single `paused` flag checked by both the advance timer and the scroll animation frame loop, not two independent pause systems.

**Long tips auto-scroll instead of growing the box.** A tip taller than the fixed-height text box scrolls its own content top-to-bottom during its display window (detected via `scrollHeight > clientHeight`), rather than the box growing to fit — this keeps short tips from looking lost in an oversized card. Scrollable tips get **extra total time** on top of their base reading-time estimate (`SCROLL_EXTRA_MS = 3000`), applied once per tip and memoized via a `data-extended-for-scroll` flag, so the fade-timer and the scroll pacing both work off the same longer window instead of the scroll racing to finish before the tip gets swapped away. The scroll itself waits `1600ms` before starting (long enough that the tip has clearly finished fading in) and paces itself to arrive at the bottom with time to spare before the swap.

**Lesson learned (two separate real bugs, both worth re-reading before touching this code):**
1. This whole script runs on initial page parse, but `#petDetailContent` starts as `display:none` and only becomes visible once the pet's data has actually loaded elsewhere, asynchronously. Measuring `scrollHeight`/`clientHeight` before that happens always reads `0` regardless of real content, which silently broke long-tip scroll detection. The fix waits for the container to actually be visible (`offsetParent !== null` and computed `display !== 'none'`) before the first real measurement — don't call `showTip(0)` unconditionally on script load.
2. The auto-scroll's pause handling originally didn't account for *how long* it had been paused — resuming would suddenly jump the scroll position forward by the entire paused duration. Fixed by recording the timestamp pausing began and shifting the animation's clock forward by exactly that duration on resume, so elapsed time excludes time spent paused. If you touch `autoScrollTip()`, re-verify pause → wait → resume doesn't jump.
3. An earlier version of the scroll-start delay (400ms) and scroll pace felt like the tip started scrolling "immediately," before a reader had even finished the first line — increased to a 1600ms hold plus the `SCROLL_EXTRA_MS` time budget above. If this is ever revisited, keep the pause clearly longer than a single fade transition (700ms), not just barely longer.

### 13.4 Compatibility Match Ring *(reusable feature — see Features table)*

Turns the existing Kids/Dogs/Cats/Other Yes/No/Unknown compatibility checklist into an animated SVG ring (`applyMatchRing(values, petName)`) showing "`X`/4" plus a plain-language caption ("*{name} gets along with everyone!*" / "*{name} does best as the only pet.*" / "*{name} is a confirmed match for X of 4 categories below.*"). If every field is `unknown`, the ring hides itself rather than showing a misleading empty/zero ring.

**Every possible score gets its own distinct color** (not 3 buckets shared across 5 scores):

```js
var MATCH_RING_COLORS = ['#c0392b','#e0622f','#ED7624','#c9a227','#0d7c4a'];
// 0/4 red → 1/4 red-orange → 2/4 orange (site's own --orange) → 3/4 amber-green → 4/4 green
```

**Markup structure matters for centering.** The ring number/label sits in `.pc-match-ring__center`, absolutely positioned with `inset:0` **inside a dedicated `.pc-match-ring__visual` wrapper** that is sized identically to the SVG (88×88). Do not position the center label as a plain sibling of the SVG using hand-guessed `top`/`left` pixel offsets — see lesson below.

**Lesson learned:** an earlier version positioned the center label using guessed `left`/`top` pixel values as a sibling of the SVG rather than a same-sized wrapped box with `inset:0`. It looked centered by coincidence in one test case and was visibly off-center in production, and a duplicate `.pc-match-ring` CSS rule (same selector declared twice in the stylesheet) silently undid part of an earlier fix on top of that. If this ring is ever restyled, grep the compiled CSS for repeated `.pc-match-ring` selectors before assuming a change took effect, and keep the wrapper-plus-`inset:0` structure — it is what makes the centering structural instead of guessed.

### 13.5 Personality Vibe Badge

Reads the pet's own `personality` field (comma-separated trait list) and matches it against a small set of keyword-scored archetypes — purely a fun presentational lens on data that already exists, no new fields required:

```js
var VIBE_ARCHETYPES = [
  { keywords: ['social','friendly','affectionate','loving','people-oriented'], title: 'The Life of the Party', icon: 'heart' },
  { keywords: ['playful','energetic','active','athletic','high-energy'],       title: 'The Adventure Buddy',    icon: 'compass' },
  { keywords: ['intelligent','smart','clever','quick learner','trainable'],    title: 'The Little Genius',      icon: 'award' },
  { keywords: ['loyal','devoted','protective','bonded'],                      title: 'Your Ride-or-Die',       icon: 'shield' },
  { keywords: ['independent','confident','self-assured'],                     title: 'The Free Spirit',        icon: 'sun' },
  { keywords: ['gentle','calm','quiet','shy','timid','reserved'],             title: 'The Gentle Soul',        icon: 'moon' },
  { keywords: ['curious','inquisitive','explorer'],                          title: 'The Little Detective',   icon: 'search' },
  { keywords: ['cuddly','lap','snuggly','affectionate'],                     title: 'The Professional Snuggler', icon: 'smile' },
  { keywords: ['smart','independent','aloof'],                               title: 'The Mysterious One',     icon: 'eyeOff' }
];
```

Highest keyword-overlap score wins; ties resolve to whichever archetype appears first in the array. No match at all → falls back to a generic "One of a Kind" (`star` icon) rather than showing nothing. No personality data at all → badge hides itself.

**Icons only, never emoji.** Every icon here is real inline SVG path data, copied from [Feather Icons](https://feathericons.com) (verified against the official source, not hand-drawn) — matching every other icon already on the page. The icon container also has a light continuous CSS pulse animation (`scale(1) → scale(1.08) → scale(1)`, ~2.4s loop).

**Lesson learned:** the first version of this used emoji for the archetype icons. This project's standing rule is icons only, never emoji, anywhere in the page or code — see the Warning & Instructions section at the top of this document. If you extend the archetype list, add a real SVG icon (Feather or Tabler, verified against the source), never an emoji character.

### 13.6 Apply-button paw burst

A short celebratory particle animation plays when "Apply to Adopt" is clicked, reusing the exact paw-print SVG path already used elsewhere on the page (the loading-screen paw trail) rather than introducing new artwork. `firePawBurst(originEl)` spawns several paw elements at the button's position, fans them outward and upward with randomized angle/distance/rotation, and fades them out via a CSS `@keyframes` animation (`pawBurstFly`), removing each element from the DOM after it finishes.

**Navigation is deliberately delayed** (~550ms via `setTimeout`) after the click so the burst is visible before the page actually navigates to the apply form — the click handler calls `e.preventDefault()` first, fires the burst, then navigates.

### 13.7 Sticky mini action bar

A compact bar (`#pcStickyMini`: photo, name, fee, Apply button) slides in from under the fixed header once the pet's name (`#petName`) scrolls above the viewport, so the call-to-action stays reachable while reading further down the page. Visibility is driven by a `scroll` listener comparing `petName`'s `getBoundingClientRect().bottom` against the header's height (68px) — not a fixed scroll-distance threshold, so it stays correct regardless of how tall the content above it is.

Populated from the same `pet` object already loaded for the rest of the page (`setupStickyMiniBar(pet, applyUrl)`) — no second fetch. The fee uses the same `toLocaleString('en-US')` number formatting as the main fee display elsewhere on the page; keep these in sync if the fee-formatting logic changes anywhere.

**Lesson learned:** the fee was accidentally hidden below a 640px viewport width by a leftover `display:none` mobile media-query rule from an earlier iteration of the bar's layout — it looked intentional (a reasonable "hide it to save space on small screens" call) but was never actually requested, and mobile is this site's primary viewport. If you touch `.pc-sticky-mini`'s mobile breakpoint, keep the fee visible unless explicitly told otherwise.

### 13.8 "You Might Also Like" — similarity ranking

Replaces a "first 4 pets in the category index" listing with real similarity scoring against the pet currently being viewed, modeled on e-commerce "similar products" — `similarityScore(current, candidate)` returns a weighted score:

| Signal | Weight | Match rule |
|---|---|---|
| Breed | 5 | Exact match, case-insensitive |
| Size | 3 | Exact match, case-insensitive |
| Age bracket | 2 | Both fall in the same bracket — `ageBracket()`: `<1` baby, `<3` young, `<8` adult, `8+` senior (bracket, not exact year match) |
| Gender | 1 | Exact match, case-insensitive |
| Personality trait overlap | 2 per shared trait | Case-insensitive comparison of the comma-split `personality` list |
| Each compatibility flag (`compatible_kids/dogs/cats/other_pets`) | 1 per shared value | Counts a shared **value** — two pets both marked `"no"` for the same field is a genuine match, not just both being `"yes"` |

All other pets in the category are scored, sorted descending, top 4 shown. If nothing scores above zero (matched on at least one field), falls back to any pet sharing at least one field — which the scoring already captures, so in practice this tier is reached automatically. If truly nothing has anything in common with the current pet (rare), falls back to a genuinely random `shuffle()` of the remaining pets. Re-fetches all other pets' full `pet.json` (not just the lightweight index) since scoring needs the full field set — for a category with many listings this is the tradeoff to be aware of if performance ever becomes a concern.

**Lesson learned:** don't assume two differing compatibility values fail to match and two identical ones always match at face value without checking `unknown` — the current implementation only awards the point when both sides have the **same non-empty value** (`current[field] && candidate[field] && current[field] === candidate[field]`), so two pets both marked `"unknown"` for the same field do not score a match. If you touch this scoring, keep that guard — otherwise "unknown" becomes a false similarity signal.

### 13.9 Hamburger menu, header, footer, bottom action bar

Uses the site-standard implementation documented in Section 2 above, including the fixed bottom "Apply to Adopt" bar (Section 2.5, already listed for this page). Identical pattern used on all pages — do not modify or reimplement.

### 13.10 Adopted-pet full-page treatment

Unlike a card grid, this page can't show a corner-badge overlay for an
adopted pet — a full-page treatment was built instead, reading the same
`pet.json` `status` field the card badge (Section 15.5) uses. When
`status` is `"Adopted"`: the hero image area shows the same navy "ADOPTED"
badge language as the cards, the info panel dims, and the sticky "Apply to
Adopt" bottom bar (Section 13.7 / Section 2.5) is hidden, since applying
to adopt an already-adopted pet isn't a real action. This flips correctly
in both directions, live, the same as the card badge — a reversed/rejected
adoption decision restores the normal available-pet view.

---

## 14. Quick Reference addendum: pet-detail.html-specific symptoms

| Symptom / Request | File to open |
|---|---|
| A long value (Personality, Specify starter kit(s)) is squeezed into a narrow column instead of using the row's full width | Section 13.1 — confirm the row still has `display:block !important`, not flex |
| Portrait video shows with black bars on the sides | Section 13.2 — confirm `applyVideoOrientation()` is actually firing on `loadedmetadata`, and check `videoWidth`/`videoHeight` aren't still `0` at the time it runs |
| Tips card starts scrolling a long tip almost immediately / too fast | Section 13.3 — check `scrollStart` and `SCROLL_EXTRA_MS`, not just `WORDS_PER_MINUTE` |
| Tips card's dots/counter/text look out of sync | Section 13.3 — all four (text, dots, counter, and scroll state) must be driven from the same `showTip(index)` call; don't update one independently |
| Compatibility ring number looks off-center | Section 13.4 — check for a duplicate `.pc-match-ring` selector in the stylesheet before touching layout; confirm the wrapper-plus-`inset:0` structure is intact |
| Compatibility ring shows the same color for two different scores | Section 13.4 — `MATCH_RING_COLORS` must have 5 distinct entries, one per possible score, not fewer buckets shared across scores |
| Vibe badge shows an emoji instead of an icon | Section 13.5 — violates the project's icons-only rule (Warning & Instructions, top of document); replace with a verified Feather/Tabler SVG |
| "You Might Also Like" pets don't look similar to the current pet | Section 13.8 — confirm `currentPet` is actually being passed into `loadRecommended()`; a missing/undefined current pet silently falls back to random |
| Sticky mini bar is missing the price on mobile | Section 13.7 — check for a `display:none` rule on `.pc-sticky-mini__fee` in a mobile media query |
| Apply button click navigates instantly with no animation | Section 13.6 — confirm `e.preventDefault()` and the `setTimeout` delay before `window.location.href` are both still present |
| Adopted pet's detail page still shows "Apply to Adopt" / doesn't look adopted | Section 13.10 — confirm `pet.json`'s `status` field is actually `"Adopted"` and the same-fetch badge logic (Section 15.5) is wired up on this page too |


---

## 15. Pet Category Listing Pages — `cats.html`, `dogs.html`, `birds.html`, `reptiles.html`, `small-mammals.html`, `livestocks.html`, `aquariums.html`

Seven pages, one per pet category, that share **identical structure, CSS, and
JS** — the only differences between any two of them are the `<title>`, the
banner `<h1>`/`<p>` copy, and the `data-category` attribute on the catalog
section (which drives which `/adoptions/pets/{category}/` folder each page
fetches from — see Section 1's folder tree). Documented here as one section
per the standing rule at the top of this document, not seven separate ones.

Each page: fixed header/hamburger menu (Section 2 — verbatim, not
reimplemented), a category-specific navy banner, a search bar + Filters
button controls row, a pet card grid fed from
`/adoptions/pets/{category}/index.json` (see Section 1), and the shared
footer (Section 2.6). The filter/search system is the reusable Bottom Sheet
+ Filter Button + Search Bar feature — see Section 11 for the full spec;
this section only covers what's specific to how these 7 pages use it.

### 15.0 Two New Categories — Livestocks & Aquariums

`livestocks.html` and `aquariums.html` were added as full new categories,
built by copying `cats.html` and adapting content/`data-category`/copy —
structurally identical to the original five in every way described in this
section. Their ID prefixes are `LVS-` and `AQU-` respectively. Adding them
touched every category-referencing surface sitewide, not just these two new
pages: the main nav and mobile hamburger menu (Section 2.6), `pets/index.html`'s
category grid (Section 17, now 7 cards), the multi-pet carousel's
category-prefix maps in both `adoption-application.html` (Section 5.3) and
`adoptions/status/index.html` (Section 7.3), and the admin dashboard's
publish/edit pet flows and category dropdowns (see
`ADMIN_DASHBOARD_PROJECT_SUMMARY.md`).

**Lesson learned — pre-existing ID-prefix mismatch found and fixed while
adding these two categories.** While updating the category-prefix maps
above to add `LVS`/`AQU`, a pre-existing mismatch (not introduced by this
work) was found: the admin dashboard's pet-ID generator produced
`REPTILE-00001` and `MAM-00001` for reptile and small-mammal pets, but every
frontend prefix map (this document's own folder tree, `adoption-application.html`'s
carousel, `adoptions/status/index.html`'s lookup) expected `REP-00001` and
`SM-00001` — a full first-dash-delimited-prefix match, not just the first 3
characters. This meant a real, valid reptile or small-mammal Pet ID typed
into the "add another pet" carousel field failed with "Unknown Pet ID
format." The frontend prefixes documented throughout this project (`REP-`,
`SM-`) are the correct, canonical ones — the admin-side generator has since
been corrected to match. If you ever see `REPTILE-` or `MAM-` prefixed IDs
on already-published pets, those were generated before this fix and may
need a one-time manual rename to stay consistent.

### 15.1 Filter Bottom Sheet — built from Section 11, with one page-specific adaptation

These pages predate Section 11's generic spec and were retrofitted to it, so
the sheet here differs from the spec in one deliberate way: instead of
generic filter options, each of the 6 sheet groups (Sort By, Breed, Adoption
Fee, Age, Size, Compatible With) wraps the page's own pre-existing dropdown
filter options as tappable chips, with `.pc-chip-scroll` capping each
group's chip area at ~4 rows tall (`max-height: 168px`) with its own
internal scroll — necessary because Breed alone can have 20+ options
(one per distinct breed across all currently-listed pets in that category).
Chips wrap up to 3-per-row, auto-dropping to 2-per-row when combined chip
width would overflow, via a JS pass (`layoutChipRow()`) that measures each
chip's real `offsetWidth` and inserts a `flex-basis:100%` break element
after the last chip that still fits — not CSS `flex-wrap` alone, which
would wrap correctly but couldn't be capped at a specific max-rows-before-
scroll without knowing where lines break.

The 6 native `<select>` elements from the pre-existing filter system are
kept, but hidden (`.hidden-select { display: none !important; }`) and
repurposed as pure state holders — the sheet's chips write into them on
Apply, and all existing filter/sort logic (`applyAndRender()`) reads them
exactly as before. This meant the actual filtering/sorting code needed zero
changes; only the UI presenting those 6 values changed.

**Lesson learned — script timing.** The sheet's wiring IIFE originally ran
as an immediately-invoked function at the top of an inline, non-deferred
`<script>` block, while the sheet's own HTML lives ~600 lines further down
the same document. `document.getElementById('pcFilterSheet')` returned
`null` at the time that script executed, tripped the function's own
`if (!sheet) return;` guard, and the Filters button's click listener was
never attached — clicking it did nothing, with no console error to point at
why. Any script that queries elements defined later in the same document
must be wrapped in `document.addEventListener('DOMContentLoaded', ...)`;
don't assume an inline `<script>` runs after everything above it in the
markup has been queried successfully just because it runs after that
markup has been *parsed*.

**Lesson learned — z-index stacking.** Independently of the timing bug
above, the sheet and its backdrop were originally at `z-index: 1400`/`1500`,
while the page's floating contact-message button sits at `z-index: 4000`
(fixed, bottom-right, on every page). Once the timing bug was fixed and the
sheet could open at all, it would have rendered underneath that floating
button. Both the sheet and its backdrop are now `z-index: 6000`/`6001` —
above every other fixed/floating element on the page. When adding any new
fixed-position overlay to these pages, check it against the *highest*
existing `z-index` on the page, not just the ones near it in the stylesheet.

**Lesson learned — drag-to-close hijacking scroll.** The drag-to-close
gesture was originally attached via `touchstart` to the entire sheet
element, not just its handle — a `mousedown` guard restricted the *mouse*
version to the handle only, but that same guard was never applied to the
`touchstart` listener. On a touchscreen, touching anywhere in the sheet —
including scrolling the chip list — started a close-drag, making the sheet
feel like it was "fighting" every scroll attempt. Fixed by moving the drag
listener onto a dedicated `.pc-sheet-drag-zone` wrapper around the handle
bar + header strip only (matching how sheets like this behave elsewhere —
drag the header, scroll the body, two independent gestures), with a guard
so a tap on the close button inside that same zone still registers as a tap
rather than a drag. The scrollable body itself has no drag listener at all.

### 15.2 Live search — breed or Pet ID, case- and dash-insensitive

Single search input (`Search Breed/Pet ID` placeholder), 450ms debounced,
searching only `breed` and `pet_id` fields client-side against the
already-loaded `allPets` array (no additional fetch per keystroke). Both the
query and the fields being matched are lowercased and stripped of dashes
before comparing (`pcNormalizeSearch()`), so `DOG-00001`, `dog00001`, and
`Dog-00001` all match the same pet regardless of how the pet ID's dash or
casing is typed. A loading-squares animation (`.pc-loading-squares`, three
morphing squares) shows in the grid area for the debounce window so a slow
network doesn't look like the search silently did nothing.

### 15.3 Empty/error states — no server paths or raw statuses shown to visitors

All pet-loading failure paths — a genuinely empty category (valid JSON,
zero pets), every individual pet's `pet.json` failing to load, and the
category's `index.json` itself failing to fetch (e.g. a 404) — now render
the same friendly message: a paw-print SVG icon, "All our pets here have
found their homes," and "Please check back soon — new arrivals are added
often." Real failure detail (HTTP status, which fetch failed) goes to
`console.error` only.

**Lesson learned:** this previously was not the case. A failed
`index.json` fetch rendered the raw internal folder path
(`/adoptions/pets/{category}/`) and the raw HTTP status directly into the
page body — visible to any visitor who happened to land in that state,
disclosing server folder structure that has no reason to be public. Never
put a fetch URL, file path, or raw HTTP status into user-facing markup;
put it in `console.error`/`console.warn` instead, and show a generic
friendly message in the DOM regardless of which specific failure occurred.

A second, distinct empty state ("No pets match your filters" — shown when
filters/search narrow a non-empty category down to zero results) is kept
separate from the above, since the two situations aren't the same thing to
a visitor. This message previously used a raw `✕` Unicode character as its
icon; replaced with an inline SVG per the project's icons-only rule
(Warning & Instructions, top of document). Both empty-state variants now
share one consolidated `.pc-empty-state` CSS block — a duplicate,
conflicting `.pc-empty-state` rule (one sized for an image-style icon via
`width`/`height`/`opacity`, the other using `color` for an inline SVG) was
found and merged while fixing this; check for exactly this kind of
duplicate-selector conflict (Warning & Instructions, rule 1) before adding
a third variant.

### 15.4 Header, footer, hamburger menu — must match Section 2/2.6 exactly

These pages use the header, footer, and hamburger menu from Section 2.6
verbatim — the header and footer markup itself was already correct
byte-for-byte against the canonical copy. What had drifted was the
**behavior**, not the markup:

- The mobile menu's `padding` was `16px 0 24px` — this is the literal
  broken value Section 2.3 names as the pre-fix state, not
  `calc(100vh - 68px - 56px)`. `overscroll-behavior: contain` and
  `-webkit-overflow-scrolling: touch` were both missing entirely.
- The hamburger's open/close JS never set `document.body.style.overflow`,
  so the page behind the open menu could still scroll — the exact "leak"
  problem Section 2.4 documents as solved elsewhere. It also never touched
  `aria-expanded`, leaving it permanently `"false"` regardless of actual
  state.

Both are now the exact code from Section 2.3/2.4, copied verbatim, not a
variant.

**Lesson learned:** the footer's "Visit Us" column was also missing the
"Golden Triangle Shopping Center, Section #257" line that the live
`adoptions/status/index.html` page shows (confirmed against a real
screenshot of that page). This resolves the discrepancy Section 2.6 flagged
but deliberately left unconfirmed — the fuller version (with the shopping
center line) is the correct one; Section 8's prose describing the footer
was already accurate, the markup pulled into 2.6 for copy-paste use was the
stale copy. If you're copying the footer for a new page, use the version in
this section or re-pull from `adoptions/status/index.html`, not from 2.6
as it stood before this correction.

**Matching Section 2's own caution:** don't re-derive the header/footer/
hamburger from scratch for these or any future page — copy Section 2.6
(now corrected) verbatim, the same way Section 10.8/13.9 required for
testimonials and pet-detail.

### 15.5 Card additions — Signin button, Posted date, Adopted badge

Three sitewide-consistency changes landed on these 7 pages together:

- **"List Out A Pet" → "Signin."** All 7 category pages had a
  `pc-btn--ghost`-styled button reading "List Out A Pet," linking to the
  old `/rehome/` path. Retexted to "Signin," restyled to match the site's
  existing gold-bordered `.nav-login`/Signin look (new `.pc-btn--signin`
  class), and repointed to `/signin/` (see Section 18 — the `/rehome/`
  path is retired sitewide).
- **"Posted on" date.** Every pet card now surfaces `pet.json`'s
  `published_date` field as a visible "Posted on" date, sourced from the
  same fetch each card already performs — see Section 1's folder tree for
  where `published_date` lives in the JSON.
- **"Adopted" status badge.** Cards now show a navy "ADOPTED" badge when
  a pet's `pet.json` `status` field reads `"Adopted"` (capital A, matching
  `"Available"`'s capitalization), sourced from the same already-fetched
  `pet.json` — no separate fetch. This reflects live state in both
  directions (an admin approving an adoption adds the badge; a reversed/
  rejected decision removes it again). `birds.html` was the original
  canonical implementation this badge logic was copied from; `pet-detail.html`
  gets a full-page equivalent treatment rather than a card overlay — dims
  the info panel and hides the sticky "Apply to Adopt" bar — described
  further in Section 13.

---

## 16. Quick Reference addendum: pet category listing pages (`cats.html`, `dogs.html`, `birds.html`, `reptiles.html`, `small-mammals.html`, `livestocks.html`, `aquariums.html`)

| Symptom / Request | File to open |
|---|---|
| Filters button does nothing when clicked | Section 15.1 — confirm the sheet-wiring script is wrapped in `DOMContentLoaded`, not running as an inline IIFE above where the sheet markup is defined |
| Filter sheet opens but appears behind another element (e.g. the floating contact button) | Section 15.1 — check the sheet/backdrop `z-index` against the *highest* fixed-position `z-index` on the page, not just nearby rules |
| Sheet tries to close / shakes while scrolling its contents | Section 15.1 — drag-to-close must only listen on `.pc-sheet-drag-zone` (handle + header), never on the scrollable body |
| A filter group's chips overflow past 3-per-row or don't scroll after ~4 rows | Section 15.1 — check `layoutChipRow()` is running (on load, resize, and sheet-open) and `.pc-chip-scroll`'s `max-height` |
| Pet ID search doesn't match when the dash is missing or casing differs | Section 15.2 — confirm both the query and the stored `pet_id` are run through `pcNormalizeSearch()` before comparing |
| A category with zero pets (or a failed fetch) shows a raw folder path or HTTP status | Section 15.3 — this must never happen; only `console.error`/`console.warn` may contain that detail, the DOM always gets the friendly paw-icon message |
| Empty state after filtering looks different from the empty state for a truly empty category | Expected — Section 15.3 treats these as two distinct messages by design, not a bug |
| Mobile menu lets the page scroll behind it, or the hamburger icon's `aria-expanded` never changes | Section 15.4 — confirm `document.body.style.overflow` and `aria-expanded` are both being set in the open/close handlers, verbatim per Section 2.4 |
| Footer's "Visit Us" column is missing the shopping center line | Section 15.4 — must include "Golden Triangle Shopping Center, Section #257" per the live status-page footer |


---

## 17. `pets/index.html` — Browse All Pets Landing Page

Source file `pets.html`. A single navigational page — 7 category cards
(Dogs, Cats, Birds, Reptiles, Small Mammals, Livestocks, Aquariums — the
last two added alongside the rest of the sitewide category rollout, see
Section 15.0), each linking to its corresponding page from Section 15.
Structure: fixed header/hamburger (Section 2), a navy hero, a breadcrumb,
the 7-card grid, and the shared footer. Each card shows a live "N
available" count (or "Check back soon" at zero) pulled the same way each
category page's own empty state is determined (Section 15.3) — the two new
cards use the identical pattern, not a special case. It has its own
preloader (a walking-paw-trail animation on `#petcenter-preloader`) that
isn't used anywhere else in the site — no further detail on it here since
it's page-specific and cosmetic, not part of the shared design system in
Section 8.

Each card's availability text is live, not hardcoded: `loadCounts()` fetches
`/adoptions/pets/{category}/index.json` for all 7 categories in parallel
(the same JSON files Section 15's pages read from), and `buildCards()`
renders each card with either `"{N} available"` when the category's count
is above zero, or `"Check back soon"` when it's zero — matching the
friendly, non-technical wording Section 15.3 established for the listing
pages' own empty state. If a category's fetch fails for any reason, it's
treated the same as a zero count (falls back silently to `"Check back
soon"`); no error, path, or HTTP status is ever shown here either.

**Header, footer, and hamburger menu now match Section 2/2.6 exactly** —
this page previously had its own drifted copies of all three (different
footer brand copy, missing several Information/Support links, different
Privacy Policy/Terms URLs, `Our Pets` linking to `#` instead of `/pets/`
in one spot, and the same missing `overscroll-behavior`/
`-webkit-overflow-scrolling`/`aria-expanded`/body-scroll-lock gaps found
and fixed in Section 15.4). All three have been replaced with the
canonical copy; nothing else on the page was touched.

---

## 18. `signin/index.html` — Sign-In Entry Point for the Future Rehoming Portal

Source file `signin.html` (renamed from `rehome.html`; the `/rehome/` path is retired sitewide — every link that used to point there now points to `/signin/`). This page is still, today, a placeholder for a large feature not yet built — a self-service portal where users will eventually be able to create an account and publish their own pets on the site — but it's no longer a bare standalone card. It now carries the site's standard header and footer (copied verbatim from `cats.html`, per Rule 7 — not reconstructed), a fixed preload paw icon, and the same centered card as before: logo, "Coming Soon" eyebrow, a headline, an email-capture "Notify Me" form, 4 feature-preview blurbs (Safe & Verified, Fast Matching, Loving Homes, No Listing Fee), and a link back to the homepage (now correctly `href="/"`, per Warning & Instructions rule 5 — the earlier `/index.html` discrepancy noted below is resolved).

The notify form is still client-side only — `submitNotify()` validates that the input contains an `@`, then just swaps the form's visibility for a static success message. It does not call any backend, so no email is actually captured or sent yet; wiring this to a real subscribe endpoint is part of whatever this page grows into.

**This page is the intended foundation for the Rehoming Platform project** (the large register-to-rehome-or-adopt marketplace covered elsewhere in this project's planning documents) — its signup/login entry point is meant to be built out from this exact file, not from scratch. Whoever picks up that work should treat this page's header/footer, paw icon, and existing card structure as the starting point.

**This page now does have a header, footer, and hamburger menu** — an earlier version of this document's note calling this page's lack of header/footer "intentional, not an oversight" no longer applies; that was true of the original bare placeholder card, not the current file.

---

**Note on Sections 17 and 18's documentation style:** Section 17 remains a simple, already-working navigation page with no history of bugs to record. Section 18, however, has now had real work done on it (header/footer addition, path rename, icon fix) — a future AI documenting whatever the real `/signin/` portal becomes as the Rehoming Platform project progresses should write that as a full section in the style of Sections 5, 7, 10, 13, and 15, building on what's described above rather than starting over.

---

## 19. The Blog — `blog/index.html`, `blog/post/index.html`, `blog/blogs-data.js`

Source files `blog-home.html`, `blog-single.html`, and `blogs-data.js`.
Three files, documented together because `blog-home.html` and
`blog-single.html` are two views over the exact same dataset — a landing
grid with category filtering, and an individual article page — and neither
one means much without the data file both of them load.

### 19.1 `blogs-data.js` — the data file

A single global object, `blogsDB`, keyed `blog1` through `blog12` (12 posts
today, in insertion order — that order matters, see 19.3). Each entry has
the same shape:

```js
blog1: {
  id: 'blog1',
  title: "...",
  excerpt: "...",
  category: 'Adoption Guides',        // one of 4 categories currently in use:
                                       // Adoption Guides, Pet Care, Training, Health & Wellness
  tags: ['first-time','preparation','all-pets','dogs','cats'],
  image: 'blog1-grid.jpg',            // filename only — see 19.2 for how it resolves to a real path
  author: 'Pet Center Team',
  date: 'May 20, 2026',               // pre-formatted display string, not a machine-parseable date
  readTime: '10 min read',
  content: '<p>...</p><h2>...</h2>...'  // full article body as an HTML string, injected via innerHTML
}
```

`content` is hand-authored HTML, not Markdown — it uses a small set of
recurring classes for styled blocks: `.pc-article-callout` (a highlighted
tip box), `.pc-step-block` / `.pc-step-number` (numbered how-to steps), and
`.pc-article-checklist` (a checklist card). Anyone adding a new post should
reuse these classes rather than inventing new inline styles, so new
articles look consistent with the 12 already there.

The file also exports 7 helper functions that both pages are meant to call
rather than querying `blogsDB` directly: `getAllBlogs()`,
`getBlogsByCategory(category)`, `getBlogsByTag(tag)`, `getAllCategories()`,
`getAllTags()`, `getBlogById(id)`, `getRelatedBlogs(blogId, limit)`, and
`getBlogNavigation(blogId)`.

### 19.2 `blog/index.html` — the landing/grid page

Hero banner with 4 category-filter pills (All Posts, Adoption Guides, Pet
Care, Training, Health & Wellness) wired to `onclick="filterByCategory(...)"`,
a card grid rendering every post's title/excerpt/category/date/readTime,
and the shared header/footer. Each card links to
`/blog/post/?id={blogId}`, which is what `blog-single.html` reads on load.

Card thumbnails resolve each post's bare `image` filename (e.g.
`blog1-grid.jpg`) against `/assets/images/` — this isn't stored as a full
path in the data file, so moving or renaming that images folder would
require a matching change to wherever this page builds the `<img src>`,
not to the data file itself.

### 19.3 `blog/post/index.html` — the individual article page

Reads `?id={blogId}` from the URL (its own small regex-based
`getURLParam()`, not `URLSearchParams`), looks up that post in `blogsDB`,
and injects title, meta (author/date/readTime), category tag, the full
`content` HTML, a related-posts sidebar list, and Previous/Next post
navigation — all via direct DOM writes keyed to specific element IDs
(`pcHeroTitle`, `pcMetaAuthor`, `.pc-article-body`, `pcRelatedPosts`, etc.).
If no `id` is present, or the ID doesn't match anything in `blogsDB`, it
falls back to showing `blog1`.

Two details worth knowing if this page is ever debugged or extended:

- **Previous/Next navigation is positional, not chronological.** It walks
  `Object.keys(blogsDB)` in the order those keys were written in the file
  (`blog1`, `blog2`, `blog3`, ...) — not by `date`. Reordering the `date`
  values without reordering the actual `blog1:`/`blog2:`/... blocks in the
  file will not change Prev/Next order; only physically moving an entry's
  block within the file changes its position in that sequence.
- **This page keeps its own local copies of `getBlogById`,
  `getRelatedBlogs`, and `getBlogNavigation`**, redefined inline near the
  top of its own script, rather than calling the versions already exported
  by `blogs-data.js`. They currently do the same thing (`getRelatedBlogs`
  here defaults to a limit of 3 instead of `blogs-data.js`'s default of 4,
  though both pages explicitly pass `4` when calling it, so that default
  difference doesn't currently show up in practice). Anyone editing the
  matching/navigation logic should be aware there are two copies of it and
  should check both files, not just `blogs-data.js`.

Because `blogs-data.js` loads as a separate `<script src="blogs-data.js">`
tag rather than being inlined, this page can't assume it's available the
instant its own script starts running. It handles that with a short
polling retry (`tryLoadBlogContent`, checking for `blogsDB` every 100ms for
a limited number of attempts) rather than assuming load order — and if it
genuinely never arrives, the article body shows a plain "having trouble
loading this article" message instead of a blank page or a raw error.

### 19.4 Header, footer, and hamburger menu

Both pages' header and mobile menu now match the canonical Section 2.6
markup — the two structural gaps that used to be flagged here (a single
generic logo instead of the dual `pc-header__logo--desktop`/`--mobile`
pattern, and `<nav id="pcMobileMenu">` sitting as a sibling after
`</header>` instead of nested inside it) have been corrected on both
`blog-home.html` and `blog-single.html`. Both pages also now set
`document.body.style.overflow` on hamburger open/close, matching the
scroll-lock fix documented in Section 15.4/Section 2.4 — this was missing
before and is now present on both files.

**The category-filter pills and tag system, previously non-functioning on
both pages, now work** — restored using `blogs-data.js`'s own exported
helper functions (`filterByCategory()`, and the tag-lookup helpers — see
Section 19.1) rather than re-derived logic, per Warning & Instructions
rule 7.

---

**Note on this section's documentation style — update:** the note that used
to sit here explained that 19.4's header/footer/hamburger drift and broken
filters were being flagged rather than fixed, since no work had been
requested on these two files yet. That work has since happened — see the
rewritten 19.4 above — so the caveat about this section being read-through-
only no longer applies to 19.4 specifically. Sections 17 and 18 (now itself
also updated, see Section 18) remain lighter on build history for the same
original reason where no further work has landed yet.


---

## 20. Homepage — `index.html`

Not previously documented as its own section (only listed in passing in
the Quick Reference table) — now getting a real one, since several
separate pieces of work have landed here.

### 20.1 Hero CTA
Beneath the "Check Adoption Status" section, the CTA now reads "Looking to
adopt? Find available pets →", with "Find available pets" as the embedded
hyperlink, styled identically to the button it replaced. Links to
`pets/index.html` (Section 17).

### 20.2 "What's New" services slide
A promo-carousel component sits directly beneath the hero, structurally
and mechanically matching a Samsung Galaxy-style promo carousel (arrow
nav, segmented progress-bar dots, play/pause autoplay control, touch
swipe) — populated with Pet Center's own 6 services, in this fixed order:
1. Flexible Payments (BNPL), 2. Shipping & Delivery, 3. Foster or List a
Pet, 4. Health Guarantee, 5. Adoption Starter Kit, 6. Post-Adoption
Support. Each slide links through to its full write-up on the new
`services/index.html` page (Section 21) via a "Read More" button; some
slides carry a second button per the reference design. No photo assets
exist yet for these slides — each currently shows an SVG icon over a
gradient placeholder built in the site's core Navy/Gold/Orange palette
(Section 8), structured so a real photo can be swapped in later without
rebuilding the slide. Expected filenames for those photos, once supplied:
`/assets/images/whatsnew-{bnpl,shipping,foster,guarantee,starterkit,support}.jpg`.

### 20.3 Testimonials carousel
Brought into structural/markup consistency with the actual testimonials
page (`Testimonials.html`, Section 10) — same card structure
(`pc-testi-card`, avatar/meta/stars/text/reply blocks), not a full port of
that page's filter/search/review-submission features, which this
condensed homepage version doesn't need.

### 20.4 Pet showcase adopted badge
Any pet shown in the homepage's own pet-showcase section uses the same
live "ADOPTED" badge logic documented in Section 15.5 — same source field,
same already-fetched `pet.json`, no separate homepage-specific
implementation.

### 20.5 Preloader paw icon
The preloader's paw SVG previously faced right; corrected to face up (its
natural resting orientation), matching every other page's paw iconography.

---

## 21. `services/index.html` — Services Page (new)

Source file `services.html`. A new page, full-article documentation of
the 6 services introduced in the homepage's "What's New" slide (Section
20.2) — one full write-up per service, same order. Built using the
canonical header/footer/hamburger (Section 2/2.6, copied verbatim from
`cats.html` — not reimplemented, per Warning & Instructions rule 7) and
the shared design system's typography/color rules (Section 8).

**Lesson learned:** an intermediate build pass briefly deleted the
canonical header/hamburger/footer block while splicing in this page's own
content-specific CSS. Caught during the mandatory Playwright visual
self-check (Design-fidelity lock, Warning & Instructions rule 10a) before
delivery — restored verbatim from `cats.html`. Worth remembering as a
concrete example of exactly the kind of mistake that check exists to
catch: a page can look fine in isolation and still have silently lost its
shared site chrome.
