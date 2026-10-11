# Pet Center Admin Dashboard - Complete Project Summary

## Table of Contents

- [⚠️ Warning & Instructions — Read Before Editing Anything](#-warning-instructions-read-before-editing-anything)
- [Features](#features)
- [Project Overview](#project-overview)
- [System Architecture](#system-architecture)
- [File-by-File Documentation](#file-by-file-documentation) (pet management, applications, and core dashboard files)
  1. [admin-dashboard.html](#1-admin-dashboardhtml-main-dashboard-interface) — Main Dashboard Interface, includes Live Chat, Admin Profiles, Reviews panels
  2. [admin-auth.php](#2-admin-authphp-authentication-handler)
  3. [security.php](#3-securityphp-token-validation)
  4. [publish-pet.php](#4-publish-petphp-publish-new-pets)
  5. [update-pet.php](#5-update-petphp-edit-published-pets)
  6. [delete-pet.php](#6-delete-petphp-delete-published-pet)
  7. [get-pets.php](#7-get-petsphp-fetch-pet-list)
  8. [get-applications.php](#8-get-applicationsphp-fetch-applications-list)
  9. [update-adoption.php](#9-update-adoptionphp-update-application-status)
  10. [delete-application.php](#10-delete-applicationphp-delete-adoption-applications) — permanent delete, applications only (no archive option)
  11. [stats.php](#11-statsphp-dashboard-statistics)
  12. [admin-upload-temp.php](#12-admin-upload-tempphp-temporary-image-upload)
  13. [upload-video-chunk.php](#13-upload-video-chunkphp-video-upload-handler)
  14. [video-upload-manager.js](#14-video-upload-managerjs-client-side-video-upload)
  15. [admin-dashboard.html - CSS & Styling](#15-admin-dashboardhtml---css--styling)
- [Data Flow Diagrams](#data-flow-diagrams) — Login, Publish Pet, Edit Pet, Application Review
- [Security Features](#security-features)
- [File Structure on Server](#file-structure-on-server) — full folder tree, all v1.0 + v2.0 + v2.1 + v2.2 files
- [Technologies Used](#technologies-used)
- [Known Limitations & Future Enhancements](#known-limitations--future-enhancements)
- [Summary](#summary)
- [v2.0 — Live Chat Feature](#v20--live-chat-feature)
  - [About](#about)
  - File-by-File Documentation (v2.0):
    - [admin-dashboard.html — v2.0 Changes](#admin-dashboardhtml--v20-changes)
    - [chat-widget.js](#chat-widgetjs)
    - [chat-config.php](#chat-configphp)
    - [chat-setup.php](#chat-setupphp)
    - [chat-migrate.php](#chat-migratephp)
    - [chat-start.php](#chat-startphp)
    - [chat-poll.php](#chat-pollphp)
    - [chat-admin-poll.php](#chat-admin-pollphp)
    - [chat-send.php](#chat-sendphp)
    - [chat-admin-reply.php](#chat-admin-replyphp)
    - [chat-admin-claim.php](#chat-admin-claimphp)
    - [chat-admin-done.php](#chat-admin-donephp)
    - [chat-admin-list.php](#chat-admin-listphp)
    - [chat-admin-profiles.php](#chat-admin-profilesphp)
    - [chat-manage.php](#chat-managephp-bulk-archive--unarchive--permanent-delete-for-chat-threads) — bulk archive / unarchive / permanent delete for chat threads
    - [chat-upload-temp.php](#chat-upload-tempphp)
    - [chat-upload-video-chunk.php](#chat-upload-video-chunkphp)
    - [chat-typing.php](#chat-typingphp)
    - [chat-draft.php](#chat-draftphp)
    - [chat-history.php](#chat-historyphp)
    - [chat-message-edit.php](#chat-message-editphp)
    - [chat-message-delete.php](#chat-message-deletephp)
    - [chat-rate.php](#chat-ratephp)
  - [Database Schema (v2.0 Tables)](#database-schema-v20-tables)
  - [Concurrency & Safety (v2.0)](#concurrency--safety-v20)
  - [Key Design Decisions (v2.0)](#key-design-decisions-v20)
- [v2.1 — Chat Bot Feature](#v21--chat-bot-feature)
  - [About](#about-1)
  - [Storage model](#storage-model)
  - [The {name} placeholder](#the-name-placeholder)
  - [The four trigger scenarios](#the-four-trigger-scenarios-exact-firing-rules)
  - [Files](#files)
  - [Deployment note](#deployment-note-real-issue-hit-during-this-build-worth-preserving)
- [v2.2 — Dashboard Redesign, Chat Bot Refinements & Email Notifications](#v22--dashboard-redesign-chat-bot-refinements--email-notifications)
  - [About](#about-2)
  - [admin-dashboard.html — Visual Redesign](#admin-dashboardhtml--visual-redesign)
  - [Chat bot pacing & live indicators](#chat-bot-pacing--live-indicators-chat-widgetjs-chat-typingphp-chat-pollphp-chat-admin-pollphp-admin-dashboardhtml)
  - [Prechat widget additions](#prechat-widget-additions-chat-widgetjs)
  - [chat-mail.php (new) — Live chat email notifications](#chat-mailphp-new--live-chat-email-notifications)
  - [Open items carried forward, not yet resolved](#open-items-carried-forward-not-yet-resolved)

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
6. **Write code  comments.** You must always write code comments on all your codes to help other AI easily find specific codes during debugging and also update the code comment after any changes made in the code. Give a name for each code and write its comment.
7. **Never try to recreate features from scratch.** First features are recommended to be used accross all pages when needed and can be enhanced if required e.g for any image/file uploads, we use our pop-up modal feature which includes pdf download for filled documents. For each feature you want to use you must not read only its documentation,  but also go to the exact file(s) it is documented in and use the exact same code in your file for consistency.  You can always find the file(s) the documented features are said to be in, inside your project folder. You must also note some features have .php handlers which work together to help the feature work perfectly,  you must understand it's documentation and the feature code well.

### Dev Manager Directives — added by CEO/Dev Manager, apply project-wide, no exceptions

These directives were issued directly by the CEO and the Dev Manager overseeing this entire project. They carry exactly the same weight as rules 1–7 above — not a secondary or optional layer of guidance. **Every rule below must be read and saved to memory before a worker touches any file, on every single task, for the full duration of this project.**

8. **Everything in this Warning & Instructions section — rules 1 through 13, and every word of it — is top priority.** Save all of it to memory before beginning any task, and re-check it against your own actions before finishing, the same way rule 1 already asks you to self-question before and after every command. Nothing here is filler or background reading.

9. **Consult `PETCENTER_DESIGN_SYSTEM_v1.0.md` before making any UI, styling, or animation decision.** It is a large, standalone reference file — use its Table of Contents to jump directly to the section relevant to your task rather than reading it front to back. This applies with extra weight to admin-dashboard work specifically — the dashboard underwent a full visual redesign (see `v2.2 — Dashboard Redesign, Chat Bot Refinements & Email Notifications` at the bottom of this document) for its structural/layout patterns (sticky header, modal pattern, notification system, dropdown component). **For colors specifically, use Section 15's Navy/Gold/Orange palette, not v2.2's "Lavender Mist"** — that color choice was the assigned worker's own, not something the CEO approved or wants; see Section 15's and v2.2's own notes for the full explanation. Everywhere the design system offers a free choice (loading animation style, component variant, etc.), the worker is free to pick whichever option fits the panel best; it does not need to match every other panel's choice unless a specific task says otherwise.

10. **Image/reference-material gate — do not proceed without it.** Some tasks say a screenshot or reference image is required before starting, because the expected result must visually match something the CEO has in mind. If a task says this and no image is present in your project folder, stop and wait — do not guess at the intended design, do not proceed on assumption, and do not ask the CEO to describe it in text instead. **Reference material is not always an image file** — sometimes it will be a full HTML page containing the desired design, dropped into your project folder instead of a screenshot. Treat that exactly the same way: locate it, open it, and extract the real markup/CSS/structure directly from its source rather than only eyeballing how it renders. Either form (image or HTML reference) is a hard gate on tasks that call for one.

10a. **Design-fidelity lock — for any task involving a reference image or HTML design source.** Treat the reference material as the literal, exact target, not inspiration and not a rough guide. No shortcuts, no simplified or minimalist substitution for what's shown, no "close enough." Before calling any design-related task done, use the Playwright headless browser tool to actually load your finished page and visually compare it against the reference — this is mandatory for any task involving a reference image or design source, not optional. Save the reference image/file to your own outputs directory and keep checking your work against it throughout the task, not just once at the start. Any design-related task also requires cross-referencing `PETCENTER_DESIGN_SYSTEM_v1.0.md` for relevant guidance and existing patterns before building anything. This project has already had a real instance of a worker treating a precise reference image as a loose suggestion and shipping something far short of what was asked — this rule exists specifically because of that, and is treated with zero tolerance going forward.

11. **Use web search during implementation, don't rely only on training knowledge.** This applies to tool choices, free/no-cost API options, current library versions, and current best practices. Training data goes stale; a method that was correct when a worker was trained can be outdated or deprecated by now. Search before committing to an approach, especially for anything involving a third-party service, library, or API.

12. **Multi-worker, same-file protocol — Sequential File Ownership Chain with Anchor Comments.** More than one task in this project can land in the same file, assigned to different workers — this is especially likely in `admin-dashboard.html` and the `chat-*.php` files given how many features share them. Blind parallel editing followed by a manual merge is the single riskiest pattern available to us here, since there is no real version control — files move between the CEO and each worker's project folder by hand. Instead, every task that shares a file with another task will say so explicitly, and workers must follow this exact protocol:
    - **Named zones.** Each worker is told which other task number(s) also touch the same file, and which part of the file is theirs to touch. Stay inside your named zone; do not edit outside it even if you notice something else you'd fix.
    - **Anchor comments.** Wrap every addition in a unique, task-numbered comment block — `<!-- TASK-14 START -->` ... `<!-- TASK-14 END -->` (substitute the real task or project number) — so any later reviewer can see at a glance which lines belong to which task. This is an extension of the existing code-comment rule (rule 6 above) already in this document, not a separate system.
    - **Sequential handoff, never blind parallel merge.** When a file has more than one assigned worker, one is designated first in line. Every worker after the first pulls the most recently submitted version of that file as their starting point — never the original, untouched file — before making any edit.
    - **One final integration/lint pass.** Whoever closes out a shared file checks it for the recurring bug classes already documented throughout this project (duplicate DB-query logic, event/message ordering bugs, `from_type` handling gaps, z-index collisions, script-timing conflicts) before marking the file done.

13a. **Never edit this document directly, under any circumstance.** This file is read-only reference material — it is not a shared changelog. Instead, when a task is complete, create a **new, separate completion file** named after your task number (e.g. `Task-9-Completion.md`) using this exact format:

    - **Task Number** — the exact task number you were assigned, quoted in full
    - **What Was Required** — a short restatement of the task's scope, in your own words
    - **Files Changed** — a table listing every file you touched and whether it's shared with another task number
    - **Code Changes** — for every file you touched, in full: if you replaced existing code, show the exact old snippet being replaced *and* the exact new final code; if you added new code, show the final code. Every code block, no matter how small, must be wrapped in a task-numbered anchor comment (`<!-- TASK-9 START -->` ... `<!-- TASK-9 END -->`) and must carry a comment explaining what it does, per Warning & Instructions rule 6 — this applies even to trivial one-line changes. This section must be complete enough that someone who has never seen your work could locate and apply every change from your documentation alone, without needing to ask you anything. Copy the code exactly as it exists in the real file, including every comment you wrote in it — do not strip, shorten, or paraphrase comments when pasting code into this document. The person merging your work depends on those comments being present, word for word, to be able to trace and verify each change.
        - **New files are the one exception to full code documentation** — if you created a file from scratch rather than modifying an existing one, list it under Files Changed and write "New file — no existing code to merge, submit the file directly" instead of pasting its code; the CEO downloads the actual file directly. This exception applies only to genuinely new files — if a new file you created gets modified again in a later second verification round, that later change is a real modification against a real baseline and does need full old-code/new-code documentation.
    - **Bugs Found Outside Task Scope** — see the Scope Lock rule below. If you found none, write "None found."
    - **Self-Check Confirmation** — a short statement confirming you only modified the files listed above, and that every code block you added carries a task-numbered anchor comment.

    This is not optional and not a nice-to-have — it is how your work gets verified and folded back into the project without anyone having to re-derive what you did from a written description. A task is not considered done until this file exists in this format.

13b. **Scope Lock — no exceptions, ever.** You touch only the files explicitly involved in your task. If completing your task correctly seems to require a change somewhere else — a shared component, a related page, anything not part of your assignment — you do not make that change yourself, no matter how small or how clearly correct it seems. Stop, and log it under **Bugs Found Outside Task Scope** in your completion file instead: state what the bug is and exactly where you found it (file and approximate line range) — do not attempt to fix it. If something outside your scope is genuinely blocking your own task, raise it as a question before proceeding rather than fixing it yourself. This rule holds with zero exceptions, including during a second verification round (see below) — good intentions never override it. An untracked change outside your assigned scope breaks the process used to join everyone's work back together, and has already caused a real problem on this project.

13c. **Second verification round.** If the CEO asks you to re-check your completed work, and you make any further changes, add a new, clearly labeled **`## SECOND VERIFICATION ROUND`** section to your *existing* completion file — never a new file. Document any additional changes in the exact same format as above (old code/new code, anchor comments, Files Changed, Bugs Found Outside Task Scope). Scope Lock still applies in full during a second round — a request to re-verify your own work is not permission to touch anything outside your original assignment.

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

## Features 
**Quick lookup — where to find a specific feature:**

| Looking for... | Go to |
|---|---|
| Applications: bulk permanent delete | Section 10 — delete-application.php |
| Chat threads: archive / unarchive / permanent delete | chat-manage.php section, and Section 1H above |
| Chat bot / automated responses | v2.1 — Chat Bot Feature |
| Chat bot pacing/typewriter delay, live typing or recording indicators | v2.2 — Dashboard Redesign, Chat Bot Refinements & Email Notifications |
| Dashboard visual design, layout, modals, notifications, dropdowns | v2.2 — Dashboard Redesign, Chat Bot Refinements & Email Notifications |
| Live chat email notifications (`chat-mail.php`) | v2.2 — Dashboard Redesign, Chat Bot Refinements & Email Notifications |
| Approved by / approved position / approved on fields | Section 9 (`update-adoption.php`) and v2.2 |
| Real-time dashboard stats not updating | Section 11 (`stats.php`) |
| Pet ID category prefixes (including Livestocks/Aquariums) | Section 4 (`publish-pet.php`) |
| Database tables and columns | Database Schema (v2.0 Tables) |
| Full folder/file layout on the server | File Structure on Server |

---

## Project Overview

The Pet Center Admin Dashboard is a comprehensive admin management system for managing pet adoptions. It allows admins to:
- Publish new pets with complete details (images, videos, personality traits, compatibility info)
- Edit existing published pets (modify any field, upload new media)
- Manage adoption applications (view, approve, reject, send checkout links, add rejection notes)
- View real-time statistics (available pets, pending applications, approved applications, adopted pets)
- Secure login with token-based authentication
- Robust video upload system optimized for slow/unstable internet connections
- **(v2.0)** Run a full live chat system with customers: claim conversations, reply with text/image/video/voice, manage admin identities, and review customer feedback ratings
- **(v2.1)** Automated bot identity sending admin-configured, personalized responses at specific trigger points in a conversation
- **(v2.2)** Redesigned dashboard interface, refined chat bot pacing and live typing/recording indicators, and email notifications for new/reopened chat threads

---

## System Architecture

```
Admin Dashboard Frontend (admin-dashboard.html)
          ↓
    Authenticates via (admin-auth.php)
          ↓
    Validates token (security.php)
          ↓
    Manages Pets:
        - Publish: publish-pet.php
        - Edit: update-pet.php
        - Delete: delete-pet.php
        - Fetch list: get-pets.php
        - Video upload: upload-video-chunk.php
    
    Manages Applications:
        - Fetch applications: get-applications.php
        - Update status/approval: update-adoption.php
        - Delete (permanent, bulk): delete-application.php
        - Fetch stats: stats.php
    
    Video Uploads:
        - Manager: video-upload-manager.js
        - Temp uploads: admin-upload-temp.php
        - Chunk handler: upload-video-chunk.php

    Manages Live Chat (v2.0):
        - Shared config/auth: chat-config.php
        - Thread list/lookup: chat-admin-list.php
        - Claim/Done: chat-admin-claim.php, chat-admin-done.php
        - Reply (grouped media): chat-admin-reply.php
        - Poll for updates: chat-admin-poll.php
        - Admin identities: chat-admin-profiles.php
        - Archive/Unarchive/Delete (bulk): chat-manage.php
        - Reviews: chat-rate.php
        - Video chunk upload: chat-upload-video-chunk.php
        - Single-file staging: chat-upload-temp.php

    Manages Chat Bot (v2.1):
        - Bot identity + message template CRUD: chat-bot-profile.php
        - Bot settings storage: chat-bot-profile.json (single file, not a DB table)
        - Bot's sent messages: real rows in chat_messages (from_type='bot')
```

---

## File-by-File Documentation

### 1. **admin-dashboard.html** (Main Dashboard Interface)

**Purpose:** Frontend UI for the entire admin dashboard system

**What It Does:**

#### A. Authentication & Security
- Login screen with token input field
- Validates admin token against backend
- Maintains session token in JavaScript memory (NOT localStorage for security)
- Heartbeat validation every 5 seconds to verify token still valid
- Auto-logout on session expiration

#### B. Dashboard Navigation
- Sidebar with menu items: Dashboard, Pet Management, Applications, Live Chat (Conversations/Admin Profiles/Reviews — v2.0, see H/I/J below), Chat Bot (Bot Profile panel — v2.1, see below)
- Panel switching (dashboard stats, publish pets, edit pets, applications, live chat, chat bot)
- Responsive design with dark theme — see v2.2 below for the full visual redesign this section's original description predates

#### C. Dashboard Stats Panel
- Displays 4 metrics: Available Pets, Pending Applications, Approved Applications, Adopted Pets
- Fetches data from `stats.php`
- Auto-refreshes when pets/applications change

#### D. Pet Management - Publish Section
- Form to add new pets with all required fields:
  - Basic info: Name, Species, Breed, Age, Size, Gender, Adoption Fee
  - Descriptions: Short description, full description, health info
  - Personality: Tags (comma-separated)
  - Compatibility: Good with kids/dogs/cats/other pets (yes/no/unknown)
  - Training: House trained, spayed/neutered, vaccinated
  - Media: Single image (grid card), bulk images (carousel), video (optional)
- Add multiple pets at once with "+ Add Another Pet" button
- All videos >= 1MB are chunked (512KB chunks) for reliable upload on slow connections
- Upload progress bar shows real-time percentage
- Mobile debugger widget (blue ▶ button) shows upload logs in real-time
- Publish button sends all pets to `publish-pet.php`
- Form resets on successful publish

#### E. Pet Management - Edit Section
- Select category dropdown to view published pets
- Lists all pets in category with ID and adoption fee
- Edit button opens modal with:
  - All pet fields pre-filled with current data
  - Current single image shown with thumbnail
  - Current bulk images shown as strip of thumbnails
  - Current video shown (if exists) with clear button
  - New video upload field (replaces old video if new one selected)
  - All fields fully editable
- Video upload same intelligent chunking as publish (512KB chunks, progress bar, debugger)
- Save Changes button sends to `update-pet.php`
- New images/videos replace old ones completely (not additive)
- If video removed, old video deleted and pet.json updated

#### F. Applications Section
- Status filter dropdown: "All Statuses", "Pending", "Approved", "Rejected"
- Category filter dropdown: Lists all pet categories
- Each row has a selection checkbox; checking any reveals a bulk-action bar (`appBulkActionBar`) showing a live "N selected" count and a single "Delete Permanently" button (`deleteApplicationsBulk()` → `delete-application.php`) — this is the only bulk action applications support; there is no archive option for applications, permanent delete only
- 5-column table: Applicant | Pet Requested | Applied Date | Status | Actions
- Status badges: Yellow (pending), Green (approved), Red (rejected)
- View Application button opens modal showing:
  - Pet info: Name, image, ID, category
  - Applicant details: Name, email, phone, address
  - Employment info: Employment status, residence type
  - Veterinary reference: Vet name, phone
  - Preferences: Indoor plan, can afford
  - "Why they want to adopt" section
  - Checkout link (if approved)
  - Approved by / Position (if approved — see Section 9 and v2.2's redesign item 10)
  - Rejection note (if rejected)
- Action buttons: Approve, Reject, Edit Status (for approved apps)
- Approve modal: Checkbox to confirm, input for Stripe checkout link, plus **Approved By** (staff name) and **Position** (role/title) fields, added alongside the checkout link input — see Section 9 (`update-adoption.php`) for the full field/storage detail and Section 7.2 of `adoption-website-project-summary.md` for how these render on the public status page
- Reject modal: Textarea for rejection reason
- Edit Status: Allows changing approved to rejected with reason

#### G. Support Features
- Mobile-friendly design (tested on phone size)
- Toast notifications for success/error messages
- No emojis in code (clean professional appearance)
- Custom select dropdowns styled to match design
- Responsive sidebar (hamburger menu on mobile)
- Blue play button indicator for video upload progress

#### H. Live Chat Panel (v2.0)
- New sidebar section "LIVE CHAT" with three nav items: Conversations, Admin Profiles, Reviews
- `switchPanel()` extended with hooks: `initChatPanel()`, `loadAdminProfiles()`, `loadChatReviews()`
- **Conversations sub-panel:**
  - Acting-admin dropdown at top — who the logged-in browser tab is currently "acting as" when replying. Persisted per-tab via `sessionStorage` (not shared across tabs, so two admins on the same computer in different tabs can each pick their own identity)
  - "Jump to a chat" search field — exact match by chat ID or customer email via `chat-admin-list.php?action=lookup`
  - Five tabs: Waiting (badge shows count), My Active, Others' Active, Done, and **Archived** — each of the first four calls `chat-admin-list.php?action=list`, which now also always returns the `archived` bucket in the same response regardless of which tab is active, so switching to the Archived tab never needs a second network round-trip
  - Stale-thread indicator: an "active" thread with no activity for `CHAT_STALE_CLAIM_HOURS` (24h) shows a visual flag so it doesn't get forgotten
  - Two-column layout: thread list (left, fixed height 640px, internal scroll) and thread detail (right, same height, internal scroll) — this fixed-height + `overflow-y:auto` pairing is what prevents the whole page from growing indefinitely as messages accumulate (a bug that existed early in the build and was fixed by giving both columns an explicit height instead of `min-height`)
  - Thread detail header: customer name, email, status badge (waiting=gold/active=green/done=grey), Claim or Mark Done button (whichever applies), an Archive/Unarchive icon button (box icon, toggles depending on the thread's current `archived` flag), a Delete Permanently icon button (trash icon, red), Chat ID, reason for contacting
  - **Bulk archive/delete:** every thread card in every tab (including Archived) has its own selection checkbox, positioned absolutely over the card so clicking it doesn't also open the thread. Checking any card reveals a bulk-action bar above the list with a live "N selected" count and up to three buttons depending on which tab is active: Archive (hidden on the Archived tab, since everything there is already archived), Unarchive (shown only on the Archived tab), and Delete Permanently (always shown, always requires a confirm dialog that explicitly states the action cannot be undone). All three post to `chat-manage.php`
  - Messages area scrolls internally, admin composer pinned at the bottom
- **Admin composer** (`buildAdminComposer()`) is a deliberate visual and functional clone of the customer widget's composer, using a separate `cad-` (chat-admin-dashboard) prefixed CSS class set that mirrors the widget's `pcw-` classes property-for-property:
  - Image attach: multi-select, up to 10 files, thumbnail preview chip per image
  - Video attach: multi-select, up to 5 files, uploaded via chunked upload (`adminUploadVideoChunked()`, 512KB chunks through `chat-upload-video-chunk.php`) rather than a single POST — a single POST was the original bug that made video uploads hang forever on Hostinger's upload size limits
  - Voice message: tap mic button to start recording, tap again to stop, 10-minute hard cap, live "0:15 / 10:00 Recording..." timer, then a DOM `<audio controls>` preview (not a detached `new Audio()` object — that approach silently fails to play on some mobile browsers) with re-record and discard buttons
  - Send button (orange circle, paper-plane icon) — disabled while any attachment is still uploading
  - All attached image/video tokens are collected into `chatAdmin.composer.imageTokens[]` / `videoTokens[]` arrays before sending (previously these were single-value slots that silently overwrote each other, so attaching 3 images and sending only actually sent 1 — fixed by converting to arrays matching the customer widget's approach)
  - After a successful send, the composer text, attachment chips, and voice preview are all cleared — this only works correctly because of an `isRealRetry` type-guard on the send function (see chat-widget.js notes below for why this guard exists)
- **Message rendering** (`renderAdminMessage()`) builds each message bubble via direct DOM construction (not `innerHTML` string building, which made event listeners awkward to attach) :
  - Own admin messages align right (navy bubble), customer messages align left (cream bubble), system/bot messages are centered dashed-border italic
  - Received images/videos display in full (`<img>`/`<video>` elements, click image to open full-size in new tab) — NOT as downloadable links; that was tried and explicitly reverted per instruction, since the admin needs to actually see what the customer sent
  - Admin's OWN sent image/video/audio messages get an admin-only "Delete" button beneath them
  - Admin's own text messages: tapping the bubble fades the timestamp row out and fades in Edit/Delete buttons (no time limit on this — `CHAT_EDIT_DELETE_WINDOW_MINUTES` is set to 0/unlimited); auto-hides after 4 seconds if untouched
  - If a message has been edited, a persistent clickable "Edited" tag sits beside the timestamp permanently; clicking it fades in a small panel showing the original pre-edit text (pulled from the `original_body` column, which is preserved only on the very first edit so repeated edits don't lose the true original)
  - Events (`admin_joined`, `ticket_closed`) and messages are merged into one array sorted by `created_at` before rendering, so a Ticket Closed marker or joined-banner always appears in its true chronological position relative to the surrounding messages rather than all events rendering before all messages regardless of timing
- Photos referenced anywhere in chat (admin avatar in messages, admin profile cards) are stored in the database as relative paths like `chat-admin-photos/file.jpg` and prefixed with `/admin/` at render time, since the widget can be loaded from any page depth on the public site but the photo files physically live under `/admin/`

#### I. Admin Profiles Panel (v2.0)
- Grid of cards, one per chat-admin identity: photo (or initials fallback), name, position/title, blue checkmark toggle, active/inactive toggle
- "+ New Profile" opens a form: name, position, photo upload (JPEG/PNG, 5MB max — same constraint as the rest of the site's image uploads), blue checkmark checkbox, active checkbox
- Edit and Delete buttons per card
- Deleting a profile does NOT cascade-delete their sent messages — `admin_id` on `chat_messages` intentionally has no foreign key constraint, so old messages remain fully readable (with the admin's name/photo as they were at send time, since messages don't store a live reference) even after the profile itself is gone
- The acting-admin dropdown in the Conversations panel re-fetches this list on load; if there is exactly one profile it auto-selects it for convenience

#### J. Reviews Panel (v2.0)
- AJAX-loads every review from `/admin/reviews/*.json` via `chat-rate.php?action=list`
- Each review card shows: face-icon rating (bad/poor/okay/good/perfect), which admin it was for, customer email, chat ID, timestamp, and the free-text comment if one was left
- Delete button per review removes that chat's review JSON file entirely
- Reviews are triggered customer-side by the "Ticket Closed" card that appears after an admin marks a chat Done — see chat-widget.js documentation

#### K. Font Awesome Fix (v2.0)
- The dashboard's `<head>` never included a Font Awesome stylesheet link at any point before v2.0 — every icon across the entire dashboard (not just the new chat panels) was silently rendering as an invisible/empty glyph, while still being fully clickable, which made a real bug look like a design complaint at first
- Fixed with a static `<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">` in `<head>`, matching the same version the customer widget injects dynamically at runtime

#### L. Visual Redesign (v2.2) — full detail in that section below
- The entire dashboard's visual design (header, sidebar, Applications panel actions/layout, modals, notification system, dropdowns, iconography) was redesigned — see `## v2.2` at the bottom of this document for the full write-up. This subsection exists as a pointer only, so anyone reading File-by-File Documentation top to bottom knows this file has a major visual layer documented elsewhere, rather than assuming Sections A–K above are still visually current. **One exception: colors.** The redesign's actual color choice ("Lavender Mist") was not CEO-approved — Section 15's Navy/Gold/Orange palette remains the correct reference for admin dashboard colors specifically; see Section 15's and v2.2's own notes.

---

### 2. **admin-auth.php** (Authentication Handler)

**Purpose:** Validates admin token and creates authenticated session

**What It Does:**

#### Input
- Receives POST request with `token` parameter from admin-dashboard.html login form

#### Processing
- Reads `.env` file to get stored `ADMIN_TOKEN`
- Compares provided token with stored token (exact match required)
- If match: 
  - Sets `$_SESSION['admin_token']` for server-side tracking
  - Calls geolocation API (ip-api.com) to get location info (country, city, ISP, coordinates)
  - Logs login to `admin-activity.log` with:
    - Timestamp
    - Token hash (first 8 chars for anonymity)
    - IP address
    - Location (country, continent, city, region, coordinates)
    - Device type (mobile/tablet/desktop)
    - Browser type (Chrome/Firefox/Safari/Edge)
    - User agent string
  - Deduplicates logs (doesn't log same IP twice within 60 seconds)
  - Keeps only last 10 login records

#### Output
- Returns JSON: `{success: true, message: "Authenticated"}` on success
- Returns HTTP 401 with JSON: `{success: false, message: "Invalid token"}` on failure
- Returns HTTP 500 if `.env` file missing

#### Security Features
- No localStorage persistence (session memory only for security)
- Token read from `.env` file (never hardcoded)
- IP geolocation for security audit
- Login activity log for monitoring
- Session timeout tracking

---

### 3. **security.php** (Token Validation)

**Purpose:** Fast heartbeat validation of admin token without heavy operations

**What It Does:**

#### Input
- Receives POST request with:
  - `token`: Admin token to validate
  - `action`: Either "login" or "validate" (heartbeat)

#### Processing
- Reads `.env` to get `ADMIN_TOKEN`
- Compares provided token with stored token
- Two modes:

**Mode 1: action=login (Full login logging)**
- Calls `logLoginActivity()` function
- Includes geolocation lookup (ip-api.com)
- Writes to admin-activity.log
- Takes ~2-3 seconds total

**Mode 2: action=validate (Fast heartbeat)**
- SKIPS geolocation and file I/O
- Only validates token in memory
- Takes ~10ms (instant)
- Runs every 5 seconds from dashboard

#### Output
- Returns JSON: `{valid: true, code: "OK"}` on valid token
- Returns HTTP 401: `{valid: false, code: "SESSION_EXPIRED"}` on invalid token

#### Why Two Modes?
- Publish flow calls login action → full logging
- Dashboard runs validate action every 5 seconds → fast check only
- Prevents timeout on slow connections from repeated geolocation calls

---

### 4. **publish-pet.php** (Publish New Pets)

**Purpose:** Receives new pet data from publish form and saves to file system

**What It Does:**

#### Input
- POST request from `publishAllPets()` function containing:
  - `token`: Admin token for authentication
  - `pets`: JSON array of pet objects with all fields
  - Video files: `pet_video_pet_1`, `pet_video_pet_2`, etc. (optional)

#### Processing
1. **Authentication**
   - Validates token against `.env`
   - Returns 401 if invalid

2. **For Each Pet:**
   - Generates unique `pet_id` by scanning existing folders, prefixed by
     category: `DOG-`, `CAT-`, `BIRD-`, `REP-`, `SM-`, `LVS-` (Livestocks),
     `AQU-` (Aquariums) — 7 categories total, `LVS`/`AQU` added alongside
     the sitewide category rollout (see `adoption-website-project-summary.md`
     Section 15.0). **Lesson learned:** the reptile and small-mammal
     prefixes here previously generated as `REPTILE-` and `MAM-`, which
     didn't match what every frontend prefix map on the public site
     actually expected (`REP-`/`SM-` — a full first-dash-delimited-prefix
     match, not just a 3-character check). This silently broke the "add
     another pet" multi-pet carousel for any real reptile/small-mammal
     Pet ID. Corrected here to generate `REP-`/`SM-`, matching the
     frontend's documented, canonical prefixes. If any pets were already
     published under the old `REPTILE-`/`MAM-` prefix before this fix,
     they need a one-time manual rename to stay consistent.
   - Ensures no ID collisions (checks `/adoptions/pets/{category}/` folders)
   - Creates folder structure: `/adoptions/pets/{category}/{pet_id}/`

3. **Image Handling:**
   - Single image: Uploaded via temp token during publish, moved to pet folder
   - Bulk images: Uploaded via temp tokens, moved to `bulk_images/` subfolder
   - Filenames: `image1.jpg`, `image2.jpg`, etc. (numbered sequentially)

4. **Video Handling:**
   - If new video uploaded in FormData: saves to pet folder as `pet_video.mp4`
   - Gets file extension (mp4, webm, mov, avi, mkv)
   - Validates MIME type

5. **Pet Data Assembly:**
   - Creates JSON object with ALL fields from form:
   ```json
   {
     "pet_id": "DOG-00001",
     "category": "dogs",
     "name": "Fluffy",
     "species": "Dog",
     "breed": "Golden Retriever",
     "age_years": 2,
     "gender": "Male",
     "size": "Large",
     "adoption_fee": 150,
     "description_short": "...",
     "description_full": "...",
     "health_info": "...",
     "personality": ["Friendly", "Playful"],
     "compatible_kids": "yes",
     "compatible_dogs": "yes",
     "compatible_cats": "unknown",
     "compatible_other_pets": "no",
     "house_trained": "yes",
     "spayed_neutered": "yes",
     "vaccinated": "yes",
     "status": "Available",
     "single_image": "single_image.jpg",
     "single_image_url": "/adoptions/pets/dogs/DOG-00001/single_image.jpg",
     "bulk_images": ["image1.jpg", "image2.jpg"],
     "bulk_images_url": "/adoptions/pets/dogs/DOG-00001/bulk_images/",
     "video": "pet_video.mp4",
     "video_url": "/adoptions/pets/dogs/DOG-00001/pet_video.mp4",
     "published_date": "2026-06-18 12:45:30"
   }
   ```
   `published_date` was already written here, but wasn't previously shown
   anywhere on the public site — it's now surfaced as a visible "Posted on"
   date on every pet card and on `pet-detail.html` (see
   `adoption-website-project-summary.md` Section 15.5), sourced from this
   same field via the fetch each card already performs.

6. **File Writing:**
   - Writes pet.json to `/adoptions/pets/{category}/{pet_id}/pet.json`
   - Creates/updates `index.json` in category folder with pet IDs list

#### Output
- Returns JSON: `{success: true, published_pets: [...]}`
- Returns JSON: `{success: false, error: "..."}` on failure

---

### 5. **update-pet.php** (Edit Published Pets)

**Purpose:** Updates existing published pet data and media files

**What It Does:**

#### Input
- POST request from `savePetChanges()` containing:
  - `token`: Admin token
  - `pet_id`: Which pet to edit
  - `category`: Pet's category
  - All editable fields (name, breed, etc.)
  - Files: `single_image`, `bulk_images[]`, `pet_video` (all optional)

#### Processing
1. **Authentication & Validation**
   - Validates token
   - Checks for oversized POST (detects if file too large, returns error)
   - Validates category and pet_id format (alphanumeric only)
   - Checks pet folder exists

2. **Load Current Data**
   - Reads existing `pet.json`
   - Maintains all fields for update

3. **Update Text Fields**
   - All scalar fields overwrite old values (even if blank, allowing clearing)
   - Includes: name, breed, species, size, gender, all descriptions
   - Updates: personality (parses comma-separated to array), age, fee

4. **Single Image Update**
   - If new single_image uploaded:
     - Validates MIME type (JPG/PNG only)
     - Removes ALL old `single_image.*` files
     - Saves new file with extension (jpg/png)
     - Updates `pet.json` with new filename and URL

5. **Bulk Images Update**
   - If new bulk images uploaded:
     - Validates MIME types
     - **DELETES ALL existing bulk images** (not additive!)
     - Saves new images as `image1.jpg`, `image2.jpg`, etc.
     - Updates count and URL in pet.json
     - Can reduce from 5 images to 2 images, or vice versa

6. **Video Update**
   - If new pet_video uploaded:
     - Validates video MIME type
     - Checks file size (max 50MB)
     - Removes old `pet_video.*` file
     - Saves new video with original extension
     - Updates `pet.json` with video filename and URL
   - If no video uploaded, pet.json keeps existing video info

7. **File Write**
   - Writes updated pet.json back to disk
   - Checks return value (detects permission errors)
   - Rebuilds category `index.json` with all pet IDs

#### Error Handling
- HTTP 413: File too large
- HTTP 401: Token invalid
- HTTP 404: Pet not found
- HTTP 400: Missing required fields
- JSON response: Specific error message + technical details

#### Output
- JSON: `{success: true, message: "Pet updated successfully", bytes_written: 12345}`
- JSON: `{success: false, error: "Detailed error message"}`

---

### 6. **delete-pet.php** (Delete Published Pet)

**Purpose:** Removes a published pet from catalog

**What It Does:**

#### Input
- POST request with: `token`, `pet_id`, `category`

#### Processing
1. Validates token and pet existence
2. Deletes entire pet folder: `/adoptions/pets/{category}/{pet_id}/`
   - Removes pet.json
   - Removes single_image.*
   - Removes entire bulk_images/ subfolder
   - Removes pet_video.* if exists
3. Updates category `index.json` to remove pet_id from list

#### Output
- JSON: `{success: true}` or `{success: false, error: "..."}`

---

### 7. **get-pets.php** (Fetch Pet List)

**Purpose:** Returns list of all pets in a category for display in edit panel

**What It Does:**

#### Input
- POST request with: `token`, `category`

#### Processing
1. Validates token
2. Checks category folder exists
3. Scans all pet folders in category
4. For each pet, reads `pet.json` file
5. Returns array of complete pet objects with all fields

#### Output
- JSON: Array of pet objects
```json
{
  "success": true,
  "pets": [
    {
      "pet_id": "DOG-00001",
      "name": "Fluffy",
      "breed": "Golden Retriever",
      ...all fields...
    },
    {...more pets...}
  ]
}
```

---

### 8. **get-applications.php** (Fetch Applications List)

**Purpose:** Returns all adoption applications for the applications panel

**What It Does:**

#### Input
- POST request with: `token`, optional filters

#### Processing
1. Validates token
2. Scans `/adoptions/applications/` directory
3. For each application folder, reads `application.json` and `metadata.json`
4. Returns array with all application data

#### Output
- JSON: Array of application objects with:
  - Applicant info (name, email, phone, address)
  - Employment info
  - Vet reference
  - Preferences
  - Pet ID and category requested
  - Current status (Pending/Approved/Rejected)
  - Application date
  - Rejection notes (if rejected)
  - Checkout URL (if approved)

---

### 9. **update-adoption.php** (Update Application Status)

**Purpose:** Updates application status (Approve/Reject) and stores checkout link or rejection reason

**What It Does:**

#### Input
- POST request with:
  - `token`: Admin token
  - `code`: Which application (this document previously called this field
    `application_code` — the real, live code on both the frontend and this
    file has always used `code`; corrected here to match)
  - `status`: "APPROVED" or "REJECTED"
  - `checkout_url`: (if approving) Stripe link
  - `approved_by`: (if approving) Name of the staff member approving
  - `approved_position`: (if approving) That staff member's role/title
  - `rejection_notes`: (if rejecting) Reason text

#### Processing
1. Validates token
2. Reads existing `application.json` and `metadata.json`
3. Updates status field
4. If APPROVED: stores `checkout_url`, `approved_by`, `approved_position`,
   and `approved_at` (server-generated timestamp — **lesson learned:** this
   file never wrote `approved_at` until this fix; the public status page's
   "Approved on" date was rendering blank because there was nothing to
   read) in metadata. Surfaced on `adoptions/status/index.html` as:
   ```
   Approved by: John Doe (Adoption Manager)
   Approved on: dd/mm/yy, hour:minute:second
   ```
   — see `adoption-website-project-summary.md` Section 7.2. The admin
   dashboard's own application-detail view shows the same `approved_by`/
   `approved_position` once set, styled with position in small font
   directly under the name (Section 1 above).
5. If REJECTED: Stores `rejection_notes` in metadata
6. Writes files back to disk

#### Output
- JSON: `{success: true}` or `{success: false, error: "..."}`

---

### 10. **delete-application.php** (Delete Adoption Applications)

**Purpose:** Permanently deletes one or more adoption application folders. Permanent delete only — there is no archive option for applications (archive exists only for chat threads; applications were explicitly kept as delete-only per instruction). Reuses `chatRequireAdmin()` from `chat-config.php` for its auth check rather than the pet-management token pattern, so it shares the exact same auth behavior as every chat endpoint.

**What It Does:**

#### Input
- POST request (JSON body or form-encoded) with:
  - `token`: Admin token (sent as `X-Admin-Token` header or `token` field — same dual pattern `chatRequireAdmin()` accepts everywhere else)
  - `applicationIds`: array of application codes for bulk delete, **or**
  - `applicationId`: a single application code (accepted as a convenience — internally normalized into a one-element array so the rest of the logic never needs to branch on single-vs-bulk)

#### Processing
1. `chatRequireAdmin()` validates the token
2. Resolves the real, canonical applications base directory once via `realpath(__DIR__ . '/../adoptions/applications')` — if this fails (directory missing/misconfigured), the whole request fails fast with a 500 rather than silently operating on a wrong path
3. For each requested ID:
   - Sanitizes it to alphanumeric + hyphen only (`preg_replace('/[^A-Za-z0-9\-]/', '', ...)`) — this is what makes path traversal via a crafted ID (e.g. `../../something`) impossible, since anything outside that character set is stripped before the path is ever built
   - Resolves the target folder's real path and checks it with `strpos($realTarget, $appsBaseDir) !== 0` — confirms the resolved path is still genuinely inside the applications directory even after symlink resolution, a second independent safety layer on top of the sanitization
   - Recursively deletes the folder (`rrmdir()` — walks every file and subdirectory, unlinking as it goes, then removes the now-empty directory itself)
   - Tracks success/failure independently per ID, so one bad ID in a batch of 10 doesn't abort the other 9
4. This is a genuinely permanent, irreversible action — there is no soft-delete, no trash bin, no way to recover a deleted application through this endpoint

#### Output
- JSON: `{success: bool, deleted: [...ids that were removed], failed: [...ids that could not be found/removed]}` — `success` is `true` only when `failed` is empty, so a partially-successful bulk delete is still reported as `success: false` even though some items did get removed, which is what the admin dashboard's failure alert relies on to tell the admin exactly which ones didn't go through

#### Admin dashboard integration
- Applications panel: each row gets a checkbox; checking any of them reveals a bulk-action bar (`appBulkActionBar`) with a live "N selected" count and a single "Delete Permanently" button
- `deleteApplicationsBulk()` (JS) confirms with the admin (irreversibility is stated explicitly in the confirm dialog), posts the selected codes as `applicationIds`, then clears selection and reloads the applications list on completion
- Any codes reported back in `failed` trigger a separate alert naming exactly which ones didn't delete, so the admin isn't left guessing

---

### 11. **stats.php** (Dashboard Statistics)

**Purpose:** Returns real-time counts for dashboard stat boxes

**What It Does:**

#### Input
- POST request with: `token`

#### Processing
1. Validates token
2. Scans all pet categories and counts:
   - `total_available`: All pets with status="Available"
   - `total_adopted`: All pets with status="Adopted"
3. Scans applications folder and counts:
   - `pending_applications`: status="Pending"
   - `approved_applications`: status="Approved"
   - `rejected_applications`: status="Rejected"

#### Output
- JSON: `{success: true, total_available: 5, total_adopted: 12, pending_applications: 3, approved_applications: 2, rejected_applications: 1}`

**Lesson learned — real-time accuracy fix.** This endpoint used to return
stale-looking counts. Traced to three separate, independent bugs, not one:
1. A structural mismatch between what each category's `index.json`
   actually contains and how this file was reading it, undercounting pets.
2. A response-shape mismatch between what this file returned and what
   `admin-dashboard.html`'s stats panel expected to parse, affecting both
   pet and application counts.
3. A frontend staleness bug — several admin actions (approving/rejecting
   an application, publishing/deleting a pet) never triggered a re-fetch of
   this endpoint at all, so the dashboard kept showing whatever numbers
   loaded on initial page load until a manual refresh.

All three needed fixing together for counts to genuinely reflect current
state on every load — fixing only the backend counting logic wouldn't have
helped if the frontend never re-fetched, and vice versa. Application
status-value casing (`PENDING`/`APPROVED`/`REJECTED`, uppercase, as used
elsewhere in `admin-dashboard.html`) was cross-checked and found already
consistent — not part of the bug.

---

### 12. **admin-upload-temp.php** (Temporary Image Upload)

**Purpose:** Handles image uploads for publish form during editing, stores in temp directory with token

**What It Does:**

#### Input
- POST request with file(s) and returns unique token

#### Processing
1. Validates token (user must be authenticated)
2. Creates temp directory if needed
3. Receives uploaded image file
4. Validates MIME type (JPG/PNG only)
5. Generates unique filename with token
6. Saves to `/admin/temp_admin/`
7. Returns token to client

#### Output
- JSON: `{success: true, token: "abc123def456"}`
- Client stores token and uses it when submitting pet data to `publish-pet.php`

---

### 13. **upload-video-chunk.php** (Video Upload Handler)

**Purpose:** Handles chunked video uploads for robust upload on slow connections

**What It Does:**

#### Input
Two types of POST requests:

**Type 1: Upload Chunk**
- `action`: "upload" (default)
- `sessionId`: Unique session ID
- `uploadId`: Upload instance ID
- `chunkIndex`: Which chunk (0, 1, 2, ...)
- `totalChunks`: How many chunks total
- `chunk`: The actual file blob (512KB)

**Type 2: Finalize Assembly**
- `action`: "finalize"
- `uploadId`: Same upload ID
- `sessionId`: Same session
- `totalChunks`: Total chunks to assemble

#### Processing

**On Upload Chunk:**
1. Validates token and chunk parameters
2. Creates session directory: `/admin/video_uploads_temp/{sessionId}/`
3. Saves chunk with index: `/admin/video_uploads_temp/{sessionId}/chunk_0000` (padded)
4. Returns JSON confirming receipt

**On Finalize:**
1. Checks all chunks exist (validates all 0000, 0001, 0002, ...)
2. Opens output file for writing
3. Reads each chunk in order and concatenates them
4. Writes to `/admin/video_uploads_final/pet_video_{uniqid}.mp4`
5. Deletes all temp chunk files
6. Returns final video URL and path

#### Why Chunking?
- **512KB chunks**: Perfect size for slow connections (even 1MB video = 2 chunks)
- **Resumable**: If network drops at chunk 47, resumes from chunk 47 (not chunk 1)
- **Progress visible**: JavaScript updates progress bar per chunk
- **Retry logic**: Each chunk can retry independently
- **Network detection**: Automatically pauses/resumes on connection changes

#### Output
- Upload response: `{success: true, chunkIndex: 5, chunksReceived: 6}`
- Finalize response: `{success: true, videoUrl: "/admin/video_uploads_final/pet_video.mp4", videoPath: "/full/path/..."}`

---

### 14. **video-upload-manager.js** (Client-Side Video Upload)

**Purpose:** Manages intelligent video chunking, progress tracking, auto-pause/resume on slow connections

**What It Does:**

#### Features

**Intelligent Chunking:**
```javascript
chunkThreshold = 1MB
chunkSize = 512KB

// All videos >= 1MB are chunked
1MB file → 2 chunks × 512KB
5MB file → 10 chunks × 512KB
50MB file → 100 chunks × 512KB
```

**Upload Flow:**
1. User selects video in admin dashboard
2. `VideoUploadManager.startUpload(file, callbacks)` called
3. Chunks file into 512KB pieces
4. Uploads each chunk to `upload-video-chunk.php` sequentially
5. Tracks which chunks succeeded (stores in Set)
6. Updates progress bar: `(chunksUploaded / totalChunks) * 100`
7. On completion: calls `finalizeUpload()` to assemble on server
8. Returns final video URL to form

**Progress Tracking:**
- Real-time percentage in progress bar
- Mobile debugger widget shows chunk-by-chunk progress
- `[14:23:45] Chunk 47/100 OK` log entries

**Network Monitoring:**
```javascript
window.addEventListener('online', () => {
  // Network restored
  VideoUploadManager.resumeAllUploads();
});

window.addEventListener('offline', () => {
  // Network lost
  VideoUploadManager.pauseAllUploads();
});
```

**Auto-Pause/Resume:**
- Network drops during upload → Manager pauses
- Uploaded chunks: stored safely
- Pending chunks: waiting in queue
- Network returns → Manager automatically resumes from last uploaded chunk
- No manual action needed

**Retry Logic:**
```javascript
maxRetries = 3
retryDelay = exponential backoff
  // 1st fail: wait 1s, retry
  // 2nd fail: wait 2s, retry
  // 3rd fail: wait 4s, retry
  // Still fail: mark as error
```

**Mobile Debugger Widget:**
- Blue ▶ button appears on screen
- Shows real-time logs:
  ```
  [14:23:45] Upload: video.mp4 (125.5 MB) - 245 chunks
  [14:23:46] Chunk 1/245 OK
  [14:23:47] Chunk 2/245 OK
  [14:23:48] Network lost - pausing uploads
  [14:24:10] Network restored - resuming uploads
  [14:24:11] Chunk 3/245 OK
  ...
  [14:29:15] SUCCESS: video.mp4 ready!
  ```
- Color coded: Blue=info, Green=success, Yellow=warning, Red=error
- Copy button to share logs
- Clear button to reset

**Session Persistence:**
- Stores upload state in localStorage
- If browser crashes, can resume from checkpoint
- Survives page refresh

---

### 15. **admin-dashboard.html - CSS & Styling**

> **Colors documented below (Navy/Gold/Orange) are the current, correct
> reference** — matching the same core palette used site-wide (see
> `adoption-website-project-summary.md` Section 8). **Note for future
> AIs:** these exact hex values may change over time if the CEO decides to
> update the admin dashboard's look — always confirm against the CEO
> directly or the latest reference image before assuming this section is
> still current, rather than assuming it's permanently fixed. One
> concrete example of why that check matters: the v2.2 redesign (see
> `## v2.2` at the bottom of this document) used a "Lavender Mist"
> palette instead of the colors below — **that was the assigned worker's
> own unapproved choice, not something the CEO asked for or wants.** The
> palette below remains the correct one unless/until the CEO says
> otherwise; v2.2's structural/layout changes (sticky header, modal
> pattern, notification system, dropdown component, icon replacements)
> are a separate matter from its color choice and aren't affected by this
> note — only the color palette itself is in question.

**Design System:**
- Dark theme with navy sidebar (#10214b)
- Gold accent color (#d7bd88) for highlights
- Orange action color (#ED7624) for buttons
- Professional typography: Cormorant Garamond + Sora
- Responsive: Mobile, tablet, desktop
- No borders on cards, shadows only
- **NO EMOJIS** - clean professional appearance

**Key Styles:**
- Sidebar: Fixed left, dark navy
- Menu items: Gold text (#d7bd88)
- Active menu: Orange right border
- Cards: White background, subtle shadow
- Buttons: Orange background, white text
- Success toast: Green background
- Error toast: Red background
- Progress bars: Green gradient
- Disabled state: Gray text

---

## Data Flow Diagrams

### Login Flow
```
User enters token in admin-dashboard.html
    ↓
handleLogin() function triggered
    ↓
POST to admin-auth.php with token
    ↓
admin-auth.php validates against .env
    ↓
If valid: Sets $_SESSION, logs to admin-activity.log
    ↓
Returns {success: true}
    ↓
JavaScript stores token in variable (memory only)
    ↓
Dashboard becomes visible
    ↓
Heartbeat starts: validateToken() every 5 seconds → security.php
```

### Publish Pet Flow
```
Admin fills out pet form (all fields)
    ↓
Selects single image → uploaded via admin-upload-temp.php → temp token
    ↓
Selects bulk images → uploaded via admin-upload-temp.php → temp tokens
    ↓
Selects video (optional) → chunked via video-upload-manager.js
    ↓
Video chunks uploaded to upload-video-chunk.php (512KB each)
    ↓
Admin clicks "Publish 1 Pet" button
    ↓
publishAllPets() function:
  - Validates all required fields
  - Calls checkTokenBeforeAction() → security.php
  - Builds FormData with pet JSON + image/video tokens/files
  ↓
POST to publish-pet.php
    ↓
publish-pet.php:
  - Validates token
  - Generates unique pet_id
  - Creates folder structure
  - Moves images from temp → pet folder
  - Assembles pet.json with all data
  - Creates/updates category index.json
  ↓
Returns {success: true, published_pets: [...]}
    ↓
JavaScript: Shows success toast, resets form, reloads pet list
```

### Edit Pet Flow
```
Admin selects category → loadPetsForCategory()
    ↓
Fetches all pets in category via get-pets.php
    ↓
Lists pets in table with Edit buttons
    ↓
Admin clicks Edit → openEditPetModal()
    ↓
Modal opens with all fields pre-filled from pet.json:
  - Current single image shown
  - Current bulk images shown
  - Current video shown (if exists)
    ↓
Admin modifies fields (optional)
    ↓
Admin uploads new image/video (optional)
  - New single image replaces old
  - New bulk images DELETE all old, use only new
  - New video replaces old, or clears if removed
    ↓
Admin clicks "Save Changes"
    ↓
savePetChanges() function:
  - Validates required fields
  - Builds FormData with ALL fields + files
  - Includes token
  ↓
POST to update-pet.php
    ↓
update-pet.php:
  - Validates token
  - Loads current pet.json
  - Updates text fields (allows blanking)
  - Deletes old images if new ones uploaded
  - Deletes old video if new one uploaded or cleared
  - Writes updated pet.json
  - Rebuilds index.json
  ↓
Returns {success: true}
    ↓
JavaScript: Shows success toast, closes modal, reloads pet list with cache-bust query param
```

### Application Review Flow
```
Admin navigates to Applications panel
    ↓
loadApplications() function → GET from get-applications.php
    ↓
Applications displayed in table with filters:
  - Status filter (All/Pending/Approved/Rejected)
  - Category filter (Dogs/Cats/etc)
    ↓
Admin clicks "View" on application
    ↓
viewApplication() opens modal showing:
  - Pet info with image
  - Applicant details
  - Employment info
  - Vet reference
  - Preferences
  - Why they want pet
    ↓
Admin clicks "Approve" button
    ↓
approveModal() opens with:
  - Checkbox to confirm
  - Input field for Stripe checkout link
  - Input fields for Approved By (staff name) and Position (role/title)
    ↓
Admin pastes Stripe link, fills in name/position, clicks "Add Link"
    ↓
submitApprove() function:
  - Validates link provided
  - Builds FormData with:
    - status: "APPROVED"
    - checkout_url: the Stripe link
    - approved_by: staff member's name
    - approved_position: staff member's role/title
    ↓
POST to update-adoption.php
    ↓
update-adoption.php:
  - Validates token
  - Updates metadata.json with checkout_url, approved_by, approved_position, approved_at
  - Changes status to "APPROVED"
  ↓
Returns {success: true}
    ↓
JavaScript: Shows success toast, closes modals, reloads application list
    ↓
Admin (or applicant) can now see checkout link and approver name/position in the application details
```

---

## Security Features

1. **Token-Based Authentication**
   - Admin token stored in `.env` file (never in code)
   - Token compared server-side every request
   - Token NOT persisted to localStorage (memory only)
   - Session timeout: token must remain valid

2. **Activity Logging**
   - Every login logged with IP, location, device, browser
   - Geolocation lookup for security audit
   - Last 10 logins kept for review
   - Token hash (first 8 chars) for anonymity

3. **Input Validation**
   - Pet IDs sanitized: alphanumeric only
   - Categories sanitized: lowercase, no special chars
   - File types validated: JPG/PNG for images, MP4/WebM/MOV for videos
   - MIME type validation on all uploads
   - File size limits enforced (50MB for video)

4. **Error Handling**
   - Specific error messages for debugging
   - HTTP status codes correct (401 for auth, 404 for not found, 413 for size, etc)
   - PHP errors suppressed (only JSON output)
   - Network errors handled gracefully

---

## File Structure on Server

```
/public_html/adopt/
├── admin/
│   ├── admin-dashboard.html (main UI — v2.0: Live Chat, Admin Profiles, Reviews panels; v2.1: Chat Bot panel; v2.2: full visual redesign — see `## v2.2` at the bottom of `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`)
│   ├── admin-auth.php
│   ├── security.php
│   ├── publish-pet.php
│   ├── update-pet.php
│   ├── delete-pet.php
│   ├── get-pets.php
│   ├── get-applications.php
│   ├── update-adoption.php
│   ├── delete-application.php (permanent delete of adoption applications, bulk-capable — reuses chatRequireAdmin() for auth)
│   ├── stats.php
│   ├── admin-upload-temp.php
│   ├── upload-video-chunk.php
│   ├── video-upload-manager.js
│   ├── .env (ADMIN_TOKEN=..., also read by chat-config.php)
│   ├── admin-activity.log
│   ├── temp_admin/ (temporary image storage)
│   ├── video_uploads_temp/ (temporary video chunks)
│   ├── video_uploads_final/ (assembled videos)
│   │
│   ├── chat-widget.js (v2.0 — customer-facing floating chat widget, served statically)
│   ├── chat-config.php (v2.0 — shared DB connection, constants, auth, helper functions)
│   ├── chat-setup.php (v2.0 — one-time schema creation, DELETE after running once)
│   ├── chat-migrate.php (v2.0 — one-time ALTER for pre-existing DBs, DELETE after running once)
│   ├── chat-start.php (v2.0 — pre-chat gate: resume or create thread)
│   ├── chat-poll.php (v2.0 — customer-side polling)
│   ├── chat-admin-poll.php (v2.0 — admin-side polling, inverted seen/unread logic)
│   ├── chat-send.php (v2.0 — customer grouped multi-media send)
│   ├── chat-admin-reply.php (v2.0 — admin grouped multi-media send)
│   ├── chat-admin-claim.php (v2.0 — atomic thread claim)
│   ├── chat-admin-done.php (v2.0 — mark thread Done, fires ticket_closed event)
│   ├── chat-admin-list.php (v2.0 — thread list by tab + exact-match lookup, includes archived bucket)
│   ├── chat-admin-profiles.php (v2.0 — admin identity CRUD)
│   ├── chat-manage.php (v2.0 — bulk archive/unarchive/permanent-delete for chat threads)
│   ├── chat-upload-temp.php (v2.0 — single-file staging: image/audio/video)
│   ├── chat-upload-video-chunk.php (v2.0 — chunked video upload, 512KB chunks)
│   ├── chat-typing.php (v2.0 — typing indicator upsert; v2.2 — `who` column's ENUM widened to accept `user_recording`/`admin_recording` alongside `user`/`admin`, see `## v2.2`)
│   ├── chat-draft.php (v2.0 — draft save/get/clear + server-side unread count)
│   ├── chat-history.php (v2.0 — powers "Download transcript"; mode=media lazy-load path unused)
│   ├── chat-message-edit.php (v2.0 — edit own message, unlimited window)
│   ├── chat-message-delete.php (v2.0 — soft-delete own message)
│   ├── chat-rate.php (v2.0 — rating submission + admin review management)
│   ├── chat-bot-profile.php (v2.1 — bot identity + message template CRUD)
│   ├── chat-mail.php (v2.2 — new; email notifications for a genuinely new chat thread and for a customer message reopening a Done thread, reusing `contact-config.php`'s mail setup — see `## v2.2`)
│   │
│   ├── chat-media/ (v2.0 — final sent media files, one subfolder per thread)
│   │   └── {chatId}/
│   │       ├── image_{random}.jpg
│   │       ├── audio_{random}.webm
│   │       └── video_{random}.mp4
│   │
│   ├── chat-uploads-temp/ (v2.0 — staged files before a message attaches them)
│   │   ├── {token}.jpg / .webm / .mp4 (staged file)
│   │   ├── {token}.json (sidecar: kind, mime, original filename, expires_at)
│   │   ├── .htaccess (Deny from all — protects staging area from direct access)
│   │   └── chunks/ (v2.0 — in-progress chunked video upload sessions)
│   │       └── {sessionId}/
│   │           ├── chunk_0000
│   │           ├── chunk_0001
│   │           └── filename.txt
│   │
│   ├── chat-admin-photos/ (v2.0 — admin profile photos, also stores the bot's own photo)
│   │   └── {filename}.jpg
│   │
│   ├── reviews/ (v2.0 — one JSON file per chat thread that received a rating)
│   │   └── {chatId}.json
│   │
│   └── chat-bot-profile.json (v2.1 — single file holding the bot's name, photo path, and all message templates for every trigger scenario; full-replace on every save, LOCK_EX protected)
│
├── contact-us/ (untouched by v2.0 — separate system, chat widget's "Chat by Email" loads this via iframe; v2.2 — a stale CORS domain in contact-config.php/contact-handler.php, hardcoded to docs.petcenterco.com instead of adopt.petcenterco.com, was found and fixed, see `## v2.2`)
│   ├── index.html (or contact-us.html)
│   ├── contact-config.php
│   ├── contact-handler.php
│   └── upload-temp.php
│
├── adoptions/
│   └── pets/
│       ├── dogs/
│       │   ├── index.json (list of dog pet IDs)
│       │   ├── DOG-00001/
│       │   │   ├── pet.json
│       │   │   ├── single_image.jpg
│       │   │   ├── pet_video.mp4
│       │   │   └── bulk_images/
│       │   │       ├── image1.jpg
│       │   │       ├── image2.jpg
│       │   │       └── image3.jpg
│       │   └── DOG-00002/
│       │       ├── pet.json
│       │       └── ...
│       ├── cats/
│       │   └── ...
│       └── ...
│   └── applications/
│       ├── APP-00001/
│       │   ├── application.json
│       │   └── metadata.json
│       └── ...
│
└── pets/
    └── detail/
        ├── pet-detail-14-3.html (single pet page with video player)
        └── ...
```

**Note on public pages (index.html and others):** `chat-widget.js` is loaded via a single `<script src="/admin/chat-widget.js"></script>` tag placed before `</body>` on every public page, using an absolute path so it resolves correctly regardless of how deep the page sits in the site's folder structure (e.g. a page at `/pets/labrador/` still finds it at `/admin/chat-widget.js`, not a nonexistent relative path). The old floating contact-popup widget code (CSS block, `#contactOverlay` div, `#messageBtn` button, and its IIFE script) was fully removed from each page it appeared on and replaced by that one script tag — the widget's own "Chat by Email" option now handles what that old popup used to do, via an iframe to `/contact-us/`.

---

## Technologies Used

**Frontend:**
- HTML5 / CSS3
- Vanilla JavaScript (no frameworks)
- FormData API for file uploads
- XMLHttpRequest for chunked uploads
- LocalStorage for upload session persistence
- Browser online/offline events

**Backend:**
- PHP 7.4+
- cURL for geolocation API calls
- File system operations (scandir, glob, mkdir, unlink)
- JSON for data storage
- Stream timeouts for large files

**Server Requirements:**
- PHP: `upload_max_filesize = 60M`, `post_max_size = 65M`
- `max_execution_time = 300s`, `max_input_time = 300s`
- Hostinger hPanel or equivalent
- Writable directories: `/admin/`, `/adoptions/`

---

## Known Limitations & Future Enhancements

**Current Limitations:**
- Pet management side: single admin login (token-based, not per-person accounts) — this is unrelated to and unchanged by the v2.0 chat system's separate admin PROFILE concept (a chat admin profile is just a name/photo/title used for attribution in chat, not a separate login credential; anyone with the one shared dashboard token can act as any chat admin profile)
- Video assembly happens on every chunk (not optimized) — applies to both the original pet-video chunked uploader and the newer chat chunked video uploader, which reuse the same 512KB-chunk pattern
- No compression of videos, anywhere in the system
- Limited to 50MB video files (PHP limit) — same ceiling applies to chat video uploads
- No download/export of application data
- Chat: `chat-setup.php` and `chat-migrate.php` must be manually deleted after running — they are not auto-removed, and leaving them accessible is a security exposure since they can re-run schema operations
- Chat: edit/delete window is currently unlimited (`CHAT_EDIT_DELETE_WINDOW_MINUTES = 0`) by explicit choice — there is no server-side cutoff after which a customer or admin can no longer edit/delete their own message
- Chat: reviews are stored as both JSON files (`/admin/reviews/{chatId}.json`) and rows in the `chat_ratings` table — these two are not kept in sync if one write succeeds and the other fails (the JSON write happens first and is treated as authoritative; the DB write is best-effort and wrapped in its own try/catch so a DB failure doesn't block the review being saved)

**Potential Enhancements:**
- Multi-user support with role-based access (pet management side)
- Application export to CSV/PDF
- Email notifications for new applications
- Batch operations (publish multiple pets, bulk approve applications)
- Video compression before upload
- Advanced analytics dashboard
- Application notes/comments history
- Pet adoption timeline tracking
- Automatic rejection of duplicate applications
- SMS notifications for approvals
---

## Summary

The Pet Center Admin Dashboard is a complete pet adoption management system built with security, reliability, and user experience in mind. It handles:

✓ Pet publishing with complete information
✓ Pet editing with flexible media management
✓ Application management with approval workflow
✓ Secure token-based authentication
✓ Robust video uploads optimized for slow connections
✓ Real-time progress tracking with mobile debugger
✓ Complete data persistence to file system
✓ Comprehensive activity logging
✓ Responsive mobile-friendly design
✓ **(v2.0)** Full live chat system between customers and admins, with grouped multi-media messaging
✓ **(v2.0)** Chunked video upload reused for chat, same reliability approach as pet video uploads
✓ **(v2.0)** Message edit/delete with permanent original-text preservation
✓ **(v2.0)** Customer satisfaction ratings tied to individual admin identities
✓ **(v2.0)** Multiple named admin chat identities with photo, title, and verified-checkmark badge
✓ **(v2.1)** Automated bot identity with admin-configurable, personalized ({name}) message templates for four distinct trigger scenarios
✓ **(v2.2)** Full visual redesign of the dashboard interface — sticky header/sidebar, consolidated mobile nav, standardized modal and dropdown components, a new dashboard-wide notification system, and Applications panel actions/mobile-layout overhaul (**note:** the redesign's color palette itself was not CEO-approved — see Section 15 and `## v2.2` for the correct color reference)
✓ **(v2.2)** Chat bot response pacing (processing delay + typewriter reveal) and fully working live typing/voice-recording indicators, both directions
✓ **(v2.2)** Rating-prompt persistence bug fixed — the post-Done rating prompt now reliably re-fires after every future Done chat
✓ **(v2.2)** New email notification system for live chat threads (new thread created, or a customer reopening a Done thread)
✓ **(v2.2)** "Approved by" / "Position" / "Approved on" now recorded and displayed for every approved application, both in the dashboard and on the public status page

All files work together seamlessly to provide admin staff with tools to manage pet adoptions efficiently and reliably, even on unstable internet connections, and — as of v2.0 — to communicate with customers in real time from the same dashboard, with v2.1 adding automated, contextual responses on top of that, and v2.2 refining that chat experience further while also giving the dashboard itself its first full visual redesign.


---

## v2.0 — Live Chat Feature

### About
A full P2P live chat system added to the admin dashboard as v2.0. Customers interact via a floating widget (`chat-widget.js`) embedded on every public page via a single absolute-path script tag. Admins manage conversations from a new "Live Chat" section in the admin dashboard (see Section 1, subsections H–K above for how this integrates into `admin-dashboard.html` itself). Features: real-time polling (2.5s active / 15s backgrounded), grouped multi-media messaging (text + image + video + audio sent together as one atomic message group), chunked video upload for Hostinger's upload size limits, cross-browser voice recording with MIME fallback, message edit/delete with unlimited time window and original-text reveal, a "Ticket Closed" notification with an admin star-rating system, a custom-styled reason-for-contact dropdown, a persistent unread badge that survives full browser restarts, and an accurate online/offline presence dot. The complete v2.0 folder structure is documented in the "File Structure on Server" section above, alongside the rest of the site's files.

For complete per-file documentation of every v2.0 PHP endpoint and the `chat-widget.js` file itself, see below.

### File-by-File Documentation (v2.0)

---

### `admin-dashboard.html` — v2.0 Changes
Full detail is documented in **Section 1, subsections H through K** above (Live Chat Panel, Admin Profiles Panel, Reviews Panel, Font Awesome Fix), directly alongside the rest of that file's documentation rather than repeated here. In short: three new sidebar nav items (Conversations, Admin Profiles, Reviews), a `cad-`-prefixed CSS class set that visually clones the customer widget's composer, DOM-constructed message rendering with chronological event/message merging, and a previously-missing Font Awesome stylesheet link that had left every icon in the entire dashboard invisible since before this project began.

---

### `chat-widget.js`
Customer-facing floating chat widget, self-contained, single-file. Injected via `<script src="/admin/chat-widget.js"></script>` on any public page using an absolute path (this matters — a relative path breaks on any page not sitting at site root). Self-injects Font Awesome 6.4.2 if not already present on the host page. Self-injects all its own CSS (design tokens: navy #10214b, gold #d7bd88, orange #ED7624, Sora + Cormorant Garamond fonts — matching the rest of the site). `apiBase: '/admin/'` — all PHP endpoint calls are resolved from `/admin/` regardless of what page depth the widget is loaded from.

**Launcher button:** bottom-right fixed circle, orange, chat-bubble icon. Unread count badge (orange number) persists server-side via `chat_drafts` table under key `unread:{chatId}` — this is what makes the badge survive a full browser close/reopen, not just a page reload. Online/offline presence dot: green = an admin is actively assigned to this thread, grey = waiting or done.

**Two entry paths from the launcher:** "Chat by Email" opens `/contact-us/` in an iframe (the pre-existing separate contact form system — deliberately untouched and kept fully independent from the chat system). "Chat Live" opens the pre-chat gate.

**Pre-chat gate:** name, email, a custom-styled dropdown for reason-of-contact (dark overlay list with radio circles, built to the `CUSTOM_DROPDOWN_DESIGN_DOCUMENTATION` pattern instead of a native `<select>`, because native selects render as an ugly full-screen OS picker on mobile), and an optional existing-chat-ID field. The custom dropdown requires its own explicit JS validation on submit since a hidden `<input>` doesn't get native browser `required` validation the way a real `<select>` would.

**Session persistence (localStorage keys):** `pc_chat_active_id`, `pc_chat_last_msg_id`, `pc_chat_name`, `pc_chat_email`, `pc_chat_draft_uuid`, `pc_ticket_closed_{chatId}`. `resumeStoredChat()` always fetches from `lastMessageId=0` on resume — an earlier version fetched from the stored last-seen ID instead, which meant a returning customer's full chat history silently never loaded, only new messages after whatever they'd last seen. `rebuildLiveView()` is the equivalent function used when the panel is closed and reopened mid-session without a full page reload.

**Polling:** `pollOnce()` self-schedules via `scheduleNextPoll()` — 2500ms when the tab is focused and panel open, 15000ms when blurred or the panel is closed. Polling deliberately continues in the background even when the panel is closed, which is what makes the unread badge and presence dot stay accurate without the customer needing to reopen the widget.

**A note on event/message ordering (important for anyone debugging why something appears in the wrong position):** `handlePollResult()` used to process `json.events` in one full pass and `json.messages` in a second full pass — meaning ALL events always rendered before ALL messages regardless of when they actually happened, so a "Ticket Closed" card would appear at the very top of the whole conversation instead of where it actually occurred. The fix was to merge both arrays into one list sorted by `created_at` before rendering anything. Any future change to polling or rendering needs to preserve this merge step or the same class of bug will reappear.

**Composer:** image attach is multi-select, up to 10 files, each gets a thumbnail preview chip, tokens collected into an `imageTokens[]` array. Video attach is multi-select, up to 5 files, uploaded via the inline `uploadVideoChunked()` function (512KB chunks through `chat-upload-video-chunk.php`) rather than a single POST — a single POST for video was the original design and it hung indefinitely on Hostinger because the file size exceeded PHP's `upload_max_filesize`. Voice message: tap to start recording, tap again to stop, 10-minute hard cap with a live countdown, then previewed in a real DOM `<audio controls>` element — an earlier version used a detached `new Audio()` JS object which silently failed to play on some mobile browsers, so this was deliberately changed to a DOM element. Voice recording also does cross-browser MIME type detection (prefers `audio/webm;codecs=opus`, falls back through `audio/mp4`, `audio/ogg`, and finally accepts `video/webm` — some Android/Brave browsers report the container as `video/webm` even for an audio-only recording with no video track, and the server was updated to accept that specific case rather than reject it as an "invalid audio type").

**Send/retry mechanics:** `sendComposedMessage(retryPayload)` has a type-guard, `isRealRetry`, that checks the argument is a real payload object and specifically NOT an `Event` instance — this exists because the Send button's own click handler was originally wired directly as `sendBtnEl.addEventListener('click', sendComposedMessage)`, which means the browser's click `MouseEvent` gets passed as the first argument and was being misread as a retry payload, silently breaking every ordinary send. An idempotency key is generated fresh per genuine send attempt (not per retry) to protect against accidental double-sends from a double-tap or a browser-level request retry. `beforeunload` only warns the user if there's an upload actively in flight, not merely because an attachment is staged but unsent.

**Message rendering (`renderMessage`):** admin avatar photos are stored in the DB as relative paths and get a `/admin/` prefix added at render time. Admin name and blue checkmark badge show on every admin message bubble, not only in the "joined" banner. Images and videos render in full (`<img>`/`<video>` elements) — a downloadable-link-only approach was tried at one point to reduce load, then explicitly reverted back to full inline display per instruction. Tapping a message bubble (the sender's own messages only) fades the timestamp row out and fades Edit/Delete buttons in, with no time limit on how old a message can be to still edit or delete it. If a message has ever been edited, a persistent clickable "Edited" tag stays beside its timestamp permanently; tapping that tag fades in a small reveal panel showing the true original pre-edit text, sourced from the `original_body` column which is only ever set on a message's first edit (a second or third edit does not overwrite it, so the original is never lost). `startInlineEdit()` swaps the bubble's rendered content in place at its existing DOM position — it does not remove and re-append the message, so an edited message never appears to "jump" to the bottom of the conversation or lose its original timestamp.

**Ticket Closed and ratings:** when an admin marks a thread Done, a `ticket_closed` event lands in the merged chronological stream and renders `buildTicketClosedCard()` — a "Ticket Closed" notice plus a dismissable prompt asking the customer to rate their experience. Tapping Rate opens `buildRatingForm()`: five Font Awesome 6 Regular (outline-style, not solid/filled) face icons from bad to perfect (`far fa-face-tired` through `far fa-face-grin-beam` — plain emoji characters were explicitly avoided per instruction), a 500-character comment textarea, and a submit button that posts to `chat-rate.php`. The closed state is also written to `localStorage` under `pc_ticket_closed_{chatId}` so the UI can reflect it correctly even before the first poll response comes back after a fresh page load.

---

### `chat-config.php`

**Purpose:** Shared foundation required by every single v2.0 chat PHP file — database connection, constants, authentication, and helper functions all live here so nothing else has to redefine them. Every chat PHP file's first real line of code is `require_once __DIR__ . '/chat-config.php'`.

**What It Does:**

#### Database connection
- `chatDb()` returns a lazy PDO singleton (only connects on first call, reused for the rest of the request) configured with `PDO::ERRMODE_EXCEPTION`, so any SQL failure anywhere in the chat system throws instead of silently returning `false` — this is why an uncaught database error surfaces as a real PHP error rather than a confusing "nothing happened"
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` are defined as constants at the top of the file — these ship as placeholders and must be replaced with real Hostinger MySQL credentials before first deploy; `chat-setup.php` and `chat-migrate.php` both refuse to run while placeholders are still present, specifically to prevent someone forgetting this step and accidentally running schema operations against nothing
- Optionally reads additional config from a `.env` file, using the same format the v1.0 dashboard's `admin-auth.php` already uses — this is also where the shared `ADMIN_TOKEN` used for chat auth lives

#### File paths (auto-created on first use, not at deploy time)
- `CHAT_MEDIA_DIR` = `__DIR__ . '/chat-media/'` — where final sent images/videos/audio live, one subfolder per chat ID
- `CHAT_UPLOAD_TEMP_DIR` = `__DIR__ . '/chat-uploads-temp/'` — where staged files sit before a message actually attaches them; protected by an auto-written `.htaccess` containing `Deny from all`, so even though this folder is inside the public `/admin/` webroot, direct browser access to a staged file by guessing its filename is blocked at the web server level

#### Limits and constants
- `CHAT_IMAGE_MAX_BYTES` = 5MB (JPEG/PNG only — matches the size/type limits already used elsewhere on the site, for consistency)
- `CHAT_VOICE_MAX_BYTES` = 20MB, `CHAT_VIDEO_MAX_BYTES` = 50MB
- `CHAT_NO_ANSWER_MINUTES` = 15 — how long a thread can sit in `waiting` before the customer widget shows a "no answer yet" notice
- `CHAT_STALE_CLAIM_HOURS` = 24 — how long an `active` thread can go without activity before the admin dashboard flags it as stale
- `CHAT_EDIT_DELETE_WINDOW_MINUTES` = 0 — this is deliberately unlimited (0 means "no time restriction at all"); `chat-message-edit.php` and `chat-message-delete.php` both skip their time-check entirely when this constant is `0`, only enforcing an actual cutoff if a positive number is ever set here in the future
- `CHAT_REASONS` — the canonical list of contact reasons: `['adoption'=>'Adoption inquiry', 'shipping'=>'Shipping question', 'health'=>'Health / medical', 'general'=>'General / other']`. This exact set of keys is what the customer widget's dropdown, `CHAT_AUTO_RESPONSES`, and the bot profile's per-reason message fields all key off of — they must always stay in sync with this list
- `CHAT_AUTO_RESPONSES` — the original hardcoded per-reason system message sent when a new thread is created, before the bot profile feature existed; still used today as the fallback whenever the bot is inactive or has no message configured for a given reason
- `CHAT_NO_ANSWER_MESSAGE` — the notice text shown after `CHAT_NO_ANSWER_MINUTES` of silence on a waiting thread

#### Authentication functions
- `chatRequireAdmin()` — the gate every admin-only endpoint calls first; internally calls `chatIsAdminAuthenticated()` and responds with HTTP 401 immediately if it returns false, halting the rest of the script
- `chatIsAdminAuthenticated()` — reads the real `ADMIN_TOKEN` from `.env`, then checks the incoming request for a matching value in either `$_POST['token']` or the `X-Admin-Token` HTTP header (checking both is what lets both plain form POSTs and fetch()-with-header calls authenticate the same way)
- `chatValidateAdminId($adminId)` — confirms a given admin profile ID actually exists in `chat_admin_profiles` and is currently marked active, used anywhere an endpoint needs to attribute an action to a specific admin identity (not just "some authenticated admin")

#### Generator helpers
- `chatGenerateChatId()` — produces a `PC-XXXXXXXX` style ID, checked against the database in a loop until a genuinely unused one is found (collision-safe)
- `chatGenerateUploadToken()` — a 32-character hex token used to name staged files in `chat-uploads-temp/`
- `chatGenerateGroupId()` — a 40-character hex value used to tag every message that was part of the same grouped multi-media send

#### I/O helpers
- `chatJsonInput()` — reads the raw `php://input` stream and JSON-decodes it; used by every endpoint that accepts a JSON body instead of classic form-encoded POST data
- `chatRespond($payload, $httpCode = 200)` — the single exit point used by every chat endpoint: sets the HTTP status code, `json_encode`s the payload, echoes it, and calls `exit` — nothing written after a call to this function ever executes, which is a common source of confusion if a future edit assumes code continues past it

#### File helpers
- `chatMoveStagedFile($from, $to)` — attempts `rename()` first (fast, atomic on the same filesystem), and if that fails, falls back to `copy()` followed by `unlink()` of the original — this fallback exists because some hosting environments (Hostinger included, depending on configuration) can have the temp upload area and the final media area on different underlying filesystems, where a plain `rename()` silently fails
- `chatResolveStagedToken($token)` — given a token, globs for the matching staged file, reads its sidecar JSON, and validates it hasn't expired; returns `false` if the token is unknown or expired so the calling endpoint can fail cleanly before any database writes happen

#### Rate limiting
- `chatCheckRateLimit($key, $max, $windowSeconds)` — a sliding-window rate limiter backed by the `chat_rate_limits` table, used by `chat-start.php` to cap new-thread creation at 10 per 10 minutes per IP address

#### Bot profile helpers (v2.1)
- `chatGetBotProfile()` — reads and JSON-decodes `chat-bot-profile.json`; if the file doesn't exist yet, returns a sensible all-defaults structure (bot inactive, empty message fields) rather than erroring, so every other file that calls this never has to separately check "does the bot profile even exist yet"
- `chatSaveBotProfile($profile)` — writes the full profile back out with `LOCK_EX`, which is what prevents two admins saving the bot settings at the exact same moment from corrupting the file; one write fully completes before the other's lock is granted
- `chatBotSubstitute($template, $fullName)` — the `{name}` placeholder engine: extracts just the first name from the full name on file, replaces every literal `{name}` in the template with it, and falls back to the word "there" if no usable name is available, so a message can never render with a broken literal `{name}` shown to a real customer. Called at the moment a bot message is actually inserted for a specific customer — never when the admin saves the template — which is what allows one saved message to correctly personalize itself differently for every different customer who triggers it

#### Output
This file produces no output of its own — it defines constants and functions consumed by every other chat PHP file.

---

### `chat-setup.php`

**Purpose:** One-time database schema creation for the entire chat system. Run exactly once against a fresh database, then deleted.

**What It Does:**

#### Input
- No parameters — visited directly as a URL: `adopt.petcenterco.com/admin/chat-setup.php`

#### Processing
1. Guards against placeholder DB credentials still being present in `chat-config.php` — refuses to run and prints an explanation instead of attempting to connect with fake credentials
2. Runs `CREATE TABLE IF NOT EXISTS` for all 8 tables: `chat_threads` (including the `archived` column — see Database Schema table below), `chat_messages` (including `edited_at`, `original_body`, `deleted_at`, `group_id`), `chat_events` (`event_type` ENUM covering `admin_joined`, `admin_left_done`, `ticket_closed`), `chat_admin_profiles`, `chat_typing`, `chat_rate_limits`, `chat_drafts`, `chat_ratings`
3. Because every statement uses `IF NOT EXISTS`, running this file a second time against a database that already has these tables is harmless — it reports each table as already existing rather than erroring or overwriting data

#### Output
- Plain-text report of each table, one line per table, confirming success or reporting the specific error if one occurred

#### Post-run
- **Must be deleted immediately after running.** Leaving it accessible means anyone who finds the URL can re-trigger schema operations against the live database.

---

### `chat-migrate.php`

**Purpose:** Safe, additive migration for a chat database that was already set up by `chat-setup.php` at an earlier point in this project, before certain schema pieces existed yet. Unlike `chat-setup.php` (which only ever creates tables that don't exist), this file's job is specifically to `ALTER` tables that already exist with real data in them, without touching or losing that data.

**What It Does:**

#### Input
- No parameters — visited directly as a URL: `adopt.petcenterco.com/admin/chat-migrate.php`

#### Processing
Runs three migrations, each wrapped so that a migration which has already been applied (or doesn't need to be) is reported rather than causing the whole script to fail:
1. **Add `ticket_closed` to the `chat_events` ENUM** — `ALTER TABLE chat_events MODIFY event_type ENUM('admin_joined','admin_left_done','ticket_closed') NOT NULL`. Needed if the database was set up before the Ticket Closed / rating feature existed.
2. **Add `'bot'` to the `chat_messages` `from_type` ENUM** — `ALTER TABLE chat_messages MODIFY from_type ENUM('user','admin','system','bot') NOT NULL`. Needed if the database was set up before the bot profile feature existed. Skipping this migration on a database that predates it is the single most common cause of a bot message rendering with no name/photo/styling — the INSERT either fails outright or the value gets silently coerced depending on SQL mode, and either way the message doesn't come through as a proper `bot`-typed row.
3. **Add the `archived` column to `chat_threads`** — `ALTER TABLE chat_threads ADD COLUMN IF NOT EXISTS archived TINYINT(1) NOT NULL DEFAULT 0`. Needed if the database was set up before the archive/bulk-delete feature existed.
4. Also creates the `chat_ratings` table via `CREATE TABLE IF NOT EXISTS`, for a database old enough to predate that table entirely.

#### Output
- Plain-text report, one line per migration, showing `[OK]` or `[SKIP]` (with the underlying error message) for each

#### Post-run
- **Must be deleted immediately after running**, for the same reason as `chat-setup.php`.

---

### `chat-start.php`
Customer pre-chat gate handler (POST). Rate limited 10/10min by IP. **Three paths:** (1) Explicit `chatId` provided → resume that thread regardless of status (done threads resumable by chat ID). (2) No chatId, email matches any existing thread (including done) → resume most recent thread for that email. (3) Genuinely new email with no prior thread → create new thread: INSERT into chat_threads (status=waiting), INSERT auto-response as system message using `CHAT_AUTO_RESPONSES[reason]`, link `previous_chat_id` if a prior done thread exists for this email. **Response:** `{success, chatId, status, fullName, email, resumed}`.

---

### `chat-poll.php`

**Purpose:** Customer-side polling endpoint — hit every 2.5s while the widget panel is open and focused, backing off to 15s when hidden (that backoff timing lives client-side in `chat-widget.js`; this endpoint just answers cheaply, since it's the single most frequently-called endpoint in the whole feature).

**What It Does:**

#### Input
- GET request (no auth — `chatId` itself is the credential, same trust model as the rest of the customer-facing endpoints): `chatId`, `lastMessageId`, `lastEventId`

#### Processing
1. Looks up the thread by `chat_id`; 404s if not found
2. **Marks admin messages as seen** — `UPDATE chat_messages SET status='seen' WHERE thread_id=? AND from_type='admin' AND status != 'seen' AND deleted_at IS NULL`. This is the read-receipt mechanism: simply polling as the customer is what flips admin messages to "seen"
3. Clears `unread_for_user` on the thread if it was set
4. Fetches new `messages[]` since `lastMessageId` (`m.*` wildcard, so `edited_at`/`original_body`/`deleted_at`/`group_id` all come through automatically without needing to be named individually), filtered to `deleted_at IS NULL`, LEFT JOINed to `chat_admin_profiles` for admin name/photo/position/blue_check
5. Fetches new `events[]` since `lastEventId`, same admin-profile LEFT JOIN
6. Checks `adminTyping` — `SELECT 1 FROM chat_typing WHERE thread_id=? AND who='admin' AND expires_at > NOW()`
7. Always includes `botProfile` (`name`, `photoUrl` only — never the message templates) regardless of the bot's current `active` state — a message already sent while the bot was active must keep showing correctly even after an admin later toggles the bot off, since this is re-evaluated fresh on every single poll rather than fixed at send time

#### Output
- JSON: `{success, status, messages[], events[], adminTyping, botProfile: {name, photoUrl}}`

**Note:** an earlier version of this endpoint also returned a `noAnswerNotice` string (populated when the thread was `waiting` and idle past `CHAT_NO_ANSWER_MINUTES`). That field, and the hardcoded `CHAT_NO_ANSWER_MESSAGE` behind it, were removed as part of the v2.1 bot rollout — see the v2.1 section's `chat-poll.php (modified)` entry below for why.

---

### `chat-admin-poll.php`

**Purpose:** Admin-side polling — deliberately a separate file from `chat-poll.php` rather than a shared one with a role flag, because the two sides have inverted seen/unread semantics and merging them risked flipping the wrong side's state.

**What It Does:**

#### Input
- GET request, requires admin auth: `chatId`, `lastMessageId`, `lastEventId`

#### Processing
1. Looks up the thread by `chat_id`; 404s if not found
2. **Marks USER messages as seen** (inverted from `chat-poll.php`, which marks admin messages) — `UPDATE chat_messages SET status='seen' WHERE thread_id=? AND from_type='user' AND status != 'seen' AND deleted_at IS NULL`
3. Clears `unread_for_admin` on the thread if it was set
4. Fetches new `messages[]` and `events[]` since the given IDs, same shape and admin-profile LEFT JOIN as `chat-poll.php`
5. Checks `userTyping` — `SELECT 1 FROM chat_typing WHERE thread_id=? AND who='user' AND expires_at > NOW()`
6. Computes `isStale` — `true` only when the thread's `status='active'` AND hours since `last_activity_at` >= `CHAT_STALE_CLAIM_HOURS`, used by the admin dashboard's staleness flag on active threads

#### Output
- JSON: `{success, status, messages[], events[], userTyping, isStale, botProfile}`

**Fixed in v2.2 — previously a known gap.** This endpoint's response used to omit the `botProfile` field that `chat-poll.php` always included, so bot messages rendered on the admin side with generic admin-message handling instead of the bot's own name/photo. `botProfile` (`name`, `photoUrl` only — never the message templates) is now included here too, and `admin-dashboard.html`'s `renderAdminPollResult()`/`renderAdminMessage()` attach and render it — see `## v2.2` at the bottom of this document for the full fix.

---

### `chat-send.php`

**Purpose:** Customer's grouped multi-media send — text, an image, an audio clip, and a video can all be submitted together from one Send action, saved as one or more `chat_messages` rows sharing a single `group_id`, inside one atomic DB transaction (the whole turn saves, or none of it does).

**What It Does:**

#### Input
- POST, JSON body (falls back to `$_POST` if body isn't valid JSON): `chatId`, `text`, `imageTokens[]` (up to 10; also accepts a legacy single `imageToken`), `audioToken`, `videoTokens[]` (up to 5; also accepts legacy `videoToken`), `replyToId`, `idempotencyKey`

#### Processing
1. **Idempotency guard:** if `idempotencyKey` is present, checks `chat_messages WHERE group_id = idempotencyKey` for this thread — if a match exists already, immediately returns `{success:true, duplicate:true, groupId}` without inserting anything again. This is what protects against a double-tap or a browser-level request retry actually sending the same turn twice
2. Rejects an entirely empty turn (no text, no tokens of any kind)
3. Looks up the thread; 404s if not found
4. **Done-thread reopen:** if the thread's `status='done'`, sending into it flips it back to `'waiting'` and clears `assigned_admin_id` — sets `$wasReopenedFromDone = true` for the bot-trigger logic below
5. Ensures the thread's `chat-media/{chatId}/` folder exists
6. **Detects first-ever customer message:** counts prior `chat_messages` rows with `from_type='user'` for this thread, *before* this send's own insert — `$isFirstUserMessage` is true only if that count is zero
7. **Resolves every token to a staged file BEFORE the transaction starts** via `chatResolveStagedToken()` — a bad or expired token fails the whole request cleanly (HTTP 410) with zero partial writes, rather than committing a half-finished message group
8. **Inside one transaction:**
   - If `text` is non-empty, inserts one `text` message row
   - For each resolved image/audio/video: moves the staged file into `chat-media/{chatId}/` via `chatMoveStagedFile()`, deletes the staged file's JSON sidecar, inserts one media message row — all sharing the same `group_id`
   - Updates `last_activity_at` and sets `unread_for_admin = 1` on the thread
   - **v2.1 bot trigger (Scenario A):** if `$isFirstUserMessage && !$wasReopenedFromDone`, and the bot is active with a `firstMessageAck` configured, inserts a `from_type='bot'` message with that (personalized) text
   - **v2.1 bot trigger (Scenario B):** if `$wasReopenedFromDone`, and the bot is active with a `reopenFirstMessageAck` configured, inserts a `from_type='bot'` message with that text instead — these two triggers are mutually exclusive by construction
   - Deletes any saved draft for this `chatId` from `chat_drafts`
   - Commits
9. On any exception during the transaction, rolls back and returns a generic 500 — no partial message group is ever left behind

#### Output
- JSON: `{success, groupId, messageIds[]}` or `{success:true, duplicate:true, groupId}` on a detected retry

---

### `chat-admin-reply.php`

**Purpose:** Admin's grouped multi-media send — structurally identical to `chat-send.php` (same token resolution, same atomic transaction, same `group_id` sharing), with `from_type='admin'` and an `admin_id` attached, plus an auto-claim behavior `chat-send.php` doesn't need.

**What It Does:**

#### Input
- POST, JSON body, requires admin auth: `chatId`, `adminId`, `text`, `imageTokens[]` (up to 10, legacy `imageToken` accepted), `audioToken`, `videoTokens[]` (up to 5, legacy `videoToken` accepted), `replyToId`, `idempotencyKey`

#### Processing
1. Requires both `chatId` and `adminId`; validates `adminId` via `chatValidateAdminId()` (must exist and be `active` in `chat_admin_profiles`)
2. Rejects an entirely empty turn
3. Same idempotency guard as `chat-send.php`, scoped to this thread's `group_id`s
4. Looks up the thread; 404s if not found
5. **Auto-claim on reply:** if the thread is still `'waiting'`, replying to it claims it in the same atomic `UPDATE ... WHERE status='waiting'` pattern as `chat-admin-claim.php` — `rowCount()` determines whether this admin actually won the claim. If someone else claimed it in the gap between the admin loading the thread and hitting Send, this returns HTTP 409 ("This chat was just claimed by another admin.") instead of silently sending into a thread now assigned elsewhere. On a successful auto-claim, fires the same `admin_joined` event `chat-admin-claim.php` would fire, so an admin who jumps straight to typing a reply — rather than clicking "Claim" first — still triggers the joined-chat banner correctly on the customer side
6. If the thread is already `active` but assigned to a *different* admin ID than the one sending, returns HTTP 403
7. Resolves all tokens before the transaction, same fail-clean pattern as `chat-send.php`
8. Inside one transaction: inserts the text row (if any) and one row per resolved media item, all sharing `group_id`, all with `admin_id` set; updates `last_activity_at` and sets `unread_for_user = 1`

#### Output
- JSON: `{success, groupId, messageIds[], justClaimed}` — `justClaimed` tells the dashboard whether this send also auto-claimed the thread, so the UI can update the thread's status locally without waiting for the next poll

---

### `chat-admin-claim.php`

**Purpose:** Explicit "Claim" action for a thread sitting in the open `waiting` queue — any admin can claim any waiting thread, first-come-first-served.

**What It Does:**

#### Input
- POST, requires admin auth: `chatId`, `adminId`

#### Processing
1. Validates `adminId` via `chatValidateAdminId()`
2. Looks up the thread; 404s if not found
3. If the thread is already claimed by this same admin (`status='active'` and `assigned_admin_id` already matches), treats it as a harmless no-op and returns `{success:true, alreadyClaimed:true}` — covers a double-click or a retried request rather than erroring
4. If the thread isn't `'waiting'` at all (already claimed by someone else), returns HTTP 409
5. **Atomic claim, inside a transaction:** `UPDATE chat_threads SET status='active', assigned_admin_id=?, unread_for_admin=0 WHERE chat_id=? AND status='waiting'` — the `WHERE status='waiting'` clause is what closes the race window between the earlier SELECT and this UPDATE; if `rowCount()` comes back 0, someone else won the race in that gap, so the transaction rolls back and HTTP 409 is returned
6. On a successful claim, inserts an `admin_joined` event — this is what makes the "an admin has joined this chat" banner appear on the customer side via `chat-poll.php`'s events array

#### Output
- JSON: `{success, chatId}`, or `{success, alreadyClaimed:true, chatId}`, or a 409 error on lost race / already-claimed-by-someone-else

---

### `chat-admin-done.php`

**Purpose:** Marks an active thread as Done — closing the conversation on the admin side and triggering the customer-facing "Ticket Closed" + rating prompt.

**What It Does:**

#### Input
- POST, requires admin auth: `chatId`, `adminId`

#### Processing
1. Validates `adminId`
2. Looks up the thread; 404s if not found
3. If already `'done'`, returns `{success:true, alreadyDone:true}` as a harmless no-op
4. **Ownership check:** only the admin currently assigned to the thread (`assigned_admin_id` matches the requesting `adminId`) can mark it done — HTTP 403 otherwise, preventing one admin from closing a conversation someone else is actively handling
5. Inside a transaction: sets `status='done'` and clears `assigned_admin_id`, then fires **two** separate events — `admin_left_done` (makes the "admin joined" banner disappear on the customer side) and `ticket_closed` (triggers the Ticket Closed card + star-rating prompt in the customer widget). Both are required; `ticket_closed` must exist in the `chat_events` ENUM, which `chat-migrate.php` adds retroactively on any database set up before this feature existed

#### Output
- JSON: `{success, chatId}`, or `{success, alreadyDone:true, chatId}`

---

### `chat-admin-list.php`

**Purpose:** The single data source behind every tab in the admin dashboard's Conversations sub-panel, plus the "Jump to a chat" lookup box.

**What It Does:**

#### Input
- GET, requires admin auth. `action` = `list` (default) or `lookup`
- **list:** optional `adminId` (used only to split active threads into "mine" vs "others" — if missing or invalid, lists still work, "mine" is simply empty; this is an attribution detail, not an auth gate)
- **lookup:** `query` (a chat ID or an email address)

#### Processing — action=list
1. **Waiting:** all threads with `status='waiting' AND archived=0`, ordered oldest-first (a real FIFO queue), each with `message_count`, `last_msg_type`, `last_msg_body` computed via correlated subqueries
2. **Active:** all threads with `status='active' AND archived=0`, LEFT JOINed to `chat_admin_profiles` for the assigned admin's name/photo/position/blue-check, plus an `is_stale` flag (`TIMESTAMPDIFF(HOUR, last_activity_at, NOW()) >= CHAT_STALE_CLAIM_HOURS`) computed directly in SQL. Split in PHP into `activeMine[]` (where `assigned_admin_id` matches the passed `adminId`) and `activeOthers[]` (everything else)
3. **Done:** all threads with `status='done' AND archived=0`, most recent first, capped at 200
4. **Archived:** all threads with `archived=1` **regardless of their `status`** — this is the "hidden but recoverable" bucket, and it is deliberately returned in the *same* `action=list` response as the other four buckets every single time, rather than requiring a second request when the admin switches to the Archived tab. This is why switching to the Archived tab in the dashboard never triggers an extra network round-trip
5. Returns all five buckets together: `{waiting[], activeMine[], activeOthers[], done[], archived[]}`

#### Processing — action=lookup
1. If the query string is a valid email, matches `chat_threads WHERE email = ?`, most recent first, returns every matching thread (all statuses, including archived — this is an exact lookup, not a filtered list)
2. Otherwise treats the query as a chat ID and does an exact `chat_id` match
3. This is intentionally an exact-match lookup, not a fuzzy/LIKE search — the admin dashboard's own "Jump to a chat" field is built around typing or pasting a known ID or email, not free-text search

#### Output
- **list:** `{success, waiting[], activeMine[], activeOthers[], done[], archived[]}`
- **lookup:** `{success, results[]}`

---

### `chat-admin-profiles.php`

**Purpose:** Full CRUD for chat admin identities (name, position/title, photo, blue-checkmark, active toggle) — the profiles selectable from the acting-admin dropdown and attributed on every admin-sent message.

**What It Does:**

#### Input
- Requires admin auth for every action, dispatched via `action` param (`list`, `create`, `update`, `delete`)
- **create:** `name`, `position`, optional `blueCheck`, optional `photo` file (multipart)
- **update:** `id`, optional `name`/`position`/`blueCheck`/`active`/`photo` — any field omitted keeps its existing value
- **delete:** `id`

#### Processing
1. **Shared photo-upload helper** (`chatHandlePhotoUpload()`, used by both create and update): validates size against `CHAT_IMAGE_MAX_BYTES`, validates MIME via `finfo` against `CHAT_IMAGE_ALLOWED_MIME` (same limits used everywhere else images are accepted on the site), saves to `chat-admin-photos/` with a randomized filename, returns the relative path — or `null` if no file was provided, which is not treated as an error (falls back to an initials avatar client-side)
2. **list:** returns every row from `chat_admin_profiles`, oldest first
3. **create:** requires non-empty `name` and `position` (both ≤255 chars); inserts with `active=1` by default; photo is optional
4. **update:** loads the existing row first; any field left blank in the request falls back to the existing value rather than being blanked out; if a new photo is uploaded and an old one existed, the old photo file is deleted from disk so `chat-admin-photos/` doesn't accumulate orphaned images every time someone changes their picture
5. **delete:** deletes the profile row, then deletes its photo file from disk if one existed. **Does not cascade-delete anything else** — `chat_messages.admin_id` and `chat_threads.assigned_admin_id` are both nullable with no foreign-key constraint to this table, specifically so that deleting a profile never corrupts or orphans existing chat history. Every message sent by that admin remains fully readable, showing the name/photo/title as they were stored on the message's own LEFT JOIN at read time, not a live reference back to a (possibly now-deleted) profile

#### Output
- JSON varies by action: `{success, profiles[]}` (list), `{success, id}` (create/update/delete)

---

### `chat-manage.php` — bulk archive / unarchive / permanent delete for chat threads

**Purpose:** The endpoint behind every archive, unarchive, and permanent-delete action on chat threads in the admin dashboard — both the per-thread icon buttons in the thread detail header and the multi-select bulk-action bar above the thread list (Section 1H above). This is the chat-side counterpart to `delete-application.php`, with one key structural difference: chat threads support a genuine **archive** state (reversible, hides the thread from the normal tab views without touching its data) in addition to permanent delete, whereas applications only ever support permanent delete.

**What It Does:**

#### Input
- POST (JSON body or form-encoded), requires admin auth: `action` (must be `archive`, `unarchive`, or `delete`), and either `chatIds` (array, for bulk) or `chatId` (single string, normalized internally into a one-element array — same convenience pattern `delete-application.php` uses for `applicationIds`/`applicationId`)

#### Processing
1. Validates `action` is one of the three allowed values
2. Normalizes `chatIds`/`chatId` into a single array; rejects the request if it ends up empty
3. For each chat ID in the batch:
   - Looks up the thread's internal `id` by `chat_id`; if not found, that ID is recorded as failed and the loop continues to the next one (one bad ID never aborts the rest of the batch)
   - **archive:** `UPDATE chat_threads SET archived = 1 WHERE id = ?` — the thread's `status` (waiting/active/done) is left completely untouched; only the `archived` flag changes. An archived thread is simply excluded from the Waiting/My Active/Others Active/Done queries in `chat-admin-list.php` and only appears in the dedicated Archived bucket
   - **unarchive:** `UPDATE chat_threads SET archived = 0 WHERE id = ?` — the thread reappears in whichever of the four normal tabs matches its existing `status` again, since that column was never changed by archiving in the first place
   - **delete (permanent, irreversible):** inside its own transaction, deletes the `chat_threads` row outright. Because `chat_messages.thread_id` and `chat_events.thread_id` are both defined with `FOREIGN KEY ... ON DELETE CASCADE` (set up in `chat-setup.php`), deleting the thread row automatically deletes every one of its messages and events at the database level — no separate DELETE statements needed for those tables. After the transaction commits, it also cleans up what the cascade can't reach: the thread's `chat-media/{chatId}/` folder is recursively removed from disk (`rrmdirChat()`, a local recursive-delete helper), and the thread's saved review file at `reviews/{chatId}.json` is deleted if one exists. The chat ID is sanitized to alphanumeric-plus-hyphen before being used to build either of those file paths
4. Tracks succeeded/failed IDs independently per item, exactly like `delete-application.php`'s pattern, so one bad ID in a batch of 10 doesn't block the other 9

#### Output
- JSON: `{success: bool, succeeded: [...ids], failed: [...ids]}` — `success` is `true` only when `failed` is empty, matching the same "partial success still reports overall failure" convention `delete-application.php` uses, so the admin dashboard can tell the admin exactly which IDs in a batch didn't go through

#### Admin dashboard integration
- Thread detail header (Section 1H): an Archive/Unarchive icon button (a box icon, its behavior toggling based on the open thread's current `archived` flag) and a separate Delete Permanently icon button (trash icon, styled red), each acting on the single open thread
- Bulk-action bar: every thread card in every tab — including the Archived tab itself — has its own selection checkbox, positioned absolutely over the card so tapping it doesn't also open the thread. Selecting any card(s) reveals a bulk-action bar showing a live "N selected" count with up to three buttons depending on which tab is currently active: **Archive** (hidden while viewing the Archived tab, since everything there is already archived), **Unarchive** (shown only on the Archived tab), and **Delete Permanently** (always shown, and always requires a confirm dialog that explicitly states the action cannot be undone before it actually posts to this endpoint)
- After any of the three actions completes, the dashboard clears the current selection and reloads the thread list; if the thread currently open in the detail pane was the one just archived/deleted, that detail pane is cleared too

---

### `chat-upload-temp.php`

**Purpose:** Stages a single image or audio file before it's attached to a message — mirrors the site's existing `upload-temp.php`/`admin-upload-temp.php` token pattern exactly: validate, store under a random token in a locked-down temp directory, write a JSON sidecar with metadata, return the token to the client. Video is deliberately **not** handled here — video goes through the chunked uploader (`chat-upload-video-chunk.php`) instead, described next.

**What It Does:**

#### Input
- POST, multipart form: `file` (the uploaded file), `kind` (`image`, `audio`, or `video`)

#### Processing
1. Rejects if no file was provided, or if `kind` isn't one of the three allowed values
2. Maps `kind` to its own allow-list and size ceiling:
   - **image:** `CHAT_IMAGE_ALLOWED_MIME`/`CHAT_IMAGE_ALLOWED_EXT` (JPEG/PNG), `CHAT_IMAGE_MAX_BYTES` (5MB)
   - **video:** `video/mp4`, `video/webm`, `video/ogg`, `video/quicktime`, `video/x-msvideo`, `video/mpeg` — extensions `mp4/webm/ogg/mov/avi/mpeg/mpg`, `CHAT_VIDEO_MAX_BYTES` (50MB)
   - **audio:** `audio/webm`, `audio/ogg`, `audio/mpeg`, `audio/mp4`, `audio/x-m4a`, `audio/wav`, `audio/x-wav`, and **`video/webm`** — extensions `webm/ogg/mp3/m4a/mp4/wav`, `CHAT_VOICE_MAX_BYTES` (20MB). `video/webm` is deliberately accepted for audio because some Android/Brave browsers report the container type as `video/webm` even for an audio-only `MediaRecorder` capture with no video track — rejecting it outright would break voice messages on those browsers
3. Validates the actual file size against the kind's max, and the actual MIME type (via `finfo`, not the client-supplied filename) against the kind's allow-list
4. Determines the file extension from the original filename; if it isn't one of the kind's allowed extensions, falls back to a safe default (`jpg`/`mp4`/`webm`) rather than rejecting outright, since the MIME check already passed and browsers sometimes report odd extensions
5. Generates a random token (`chatGenerateUploadToken()`), saves the file to `CHAT_UPLOAD_TEMP_DIR/{token}.{ext}`
6. Writes a sidecar `{token}.json` containing: `token`, `kind`, `original` filename, `mime`, `size`, `extension`, `created_at`, `expires_at` (2 hours out — matches the site's existing temp-upload convention)
7. **Probabilistic cleanup:** on roughly 1% of requests, scans every `.json` sidecar in the temp directory and deletes any staged file (plus its sidecar) whose `expires_at` has already passed. This runs *before* the response is sent, not after — anything placed after `chatRespond()` would never execute, since that function calls `exit` internally

#### Output
- JSON: `{success, token, kind, name, size}`

---

### `chat-upload-video-chunk.php`

**Purpose:** Chunked video upload for the chat system, used by both the customer widget and the admin dashboard composer — mirrors the site's existing pet-video chunked uploader (`upload-video-chunk.php`) mechanism, but stages its finished output into `chat-uploads-temp/` using the same token+sidecar pattern `chat-upload-temp.php` uses, so `chat-send.php`/`chat-admin-reply.php` can find and consume it identically to an image or audio token.

**What It Does:**

#### Input
Two POST actions, dispatched via `action`:
- **upload:** `uploadId`, `sessionId`, `chunkIndex`, `totalChunks`, `fileName`, `fileSize`, and the chunk itself as `chunk` (multipart file, capped at 6MB per chunk on this endpoint)
- **finalize:** `uploadId`, `sessionId`, `totalChunks`

Note: unlike most chat endpoints, this file does **not** call `chatRequireAdmin()` — it has no auth gate of its own on either action. It's reachable by both the public widget and the admin dashboard; the actual security boundary is downstream, since a bare staged-video token is useless on its own until it's consumed by `chat-send.php` (which needs a valid `chatId`) or `chat-admin-reply.php` (which requires admin auth).

#### Processing

**action=upload:**
1. Builds a session directory under `chat-uploads-temp/chunks/{sessionId}` (falls back to `uploadId` if no `sessionId` given), sanitized to alphanumeric/underscore/hyphen only
2. Writes the original filename to `filename.txt` inside that session directory (read back during finalize)
3. Validates the chunk was actually uploaded without error and isn't over 6MB
4. Saves the chunk as `chunk_{index}` (zero-padded to 4 digits) inside the session directory
5. Returns a running count of how many chunks have been received so far for this session

**action=finalize:**
1. Reads back the stored filename from `filename.txt` (falls back to the request's `fileName` if that file is missing)
2. Verifies every chunk from `0` to `totalChunks - 1` actually exists on disk — fails with a specific "Missing chunk N" error if any are absent
3. Determines the final extension from the filename (falls back to `mp4` if it's not a recognized video extension)
4. Generates a token, opens the destination file, reads and writes each chunk in order, deleting each chunk file as it's consumed, then closes the file and removes the now-empty session directory
5. Writes a sidecar JSON in the **exact same shape** `chat-upload-temp.php` writes (`token`, `kind:'video'`, `original`, `mime`, `extension`, `created_at`, `expires_at`), so this finalized video is indistinguishable from an image/audio token to any code downstream that just does `chatResolveStagedToken()`

#### Output
- **upload:** `{success, chunkIndex, chunksReceived, totalChunks}`
- **finalize:** `{success, token, kind:'video', name, extension}`

The customer widget drives this via its own inline `uploadVideoChunked()` function; the admin dashboard drives it via `adminUploadVideoChunked()` (sending an `X-Admin-Token` header alongside, even though this endpoint itself doesn't check it). Both produce a token consumed identically by `chat-send.php` and `chat-admin-reply.php`.

---

### `chat-typing.php`

**Purpose:** A lightweight typing-indicator ping, used by both sides of a conversation.

**What It Does:**

#### Input
- POST: `chatId`, `who` (`user` or `admin`)

#### Processing
1. Rejects if `chatId` is empty or `who` isn't exactly `user` or `admin`
2. If `who='admin'`, requires admin auth (`chatRequireAdmin()`) — a customer spoofing an "admin is typing" ping would be a minor but pointless nuisance, cheap to block here; customer-side pings (`who='user'`) require no auth
3. Looks up the thread's internal ID; 404s if not found
4. **Upserts** into `chat_typing`: `INSERT ... ON DUPLICATE KEY UPDATE expires_at = DATE_ADD(NOW(), INTERVAL 6 SECOND)` — a fresh ping just refreshes the existing row's expiry rather than creating duplicates, since `(thread_id, who)` is the table's primary key
5. There is deliberately no explicit "stopped typing" call — the row simply expires a few seconds after the last ping. This is simpler and more reliable than trying to catch every way someone can stop typing without telling the server (closing the tab, switching threads, losing connection mid-keystroke)

#### Output
- JSON: `{success: true}`

Polled by `chat-poll.php` (as `adminTyping`) and `chat-admin-poll.php` (as `userTyping`).

---

### `chat-draft.php`

**Purpose:** Saves and restores an in-progress, unsent message draft, and doubles as the storage mechanism for the widget's server-side-persisted unread badge count.

**What It Does:**

#### Input
- `action` (`save`, `get`, or `clear`) and `draftKey` (required, ≤64 chars) on every call. `draftKey` is either a real `chat_id` once a thread exists, or a temporary client-generated UUID used before the very first message has actually created a thread — this endpoint treats both identically; the handoff between the two keys is purely a client-side concern
- **save (POST):** additionally takes `body`
- **get (GET):** no additional params
- **clear (POST):** no additional params

#### Processing
1. **save:** if `body` is empty, treats that as "nothing to save" and deletes any existing draft row for this key rather than storing a blank one, responding `{success:true, cleared:true}`. Otherwise upserts into `chat_drafts`: `INSERT ... ON DUPLICATE KEY UPDATE body = VALUES(body), updated_at = CURRENT_TIMESTAMP`
2. **get:** looks up the row by `draft_key`, returns the body (or `null` if none exists) plus its `updated_at`
3. **clear:** unconditionally deletes the row for this key. Used by the client right after a successful send as a belt-and-suspenders alongside `chat-send.php`'s own draft deletion — this matters specifically for the pre-thread-UUID case, since `chat-send.php` only ever deletes a draft by the real `chat_id` and has no way to know about a UUID key that predates the thread's existence
4. **Reused for unread-count persistence:** the widget also calls this same save/get pair with `draftKey = 'unread:' + chatId` and the count (as a string) stored in `body` — this is how the launcher's unread badge survives a full browser close/reopen rather than resetting on every page load. There is no separate table or endpoint for this; it deliberately piggybacks on the generic draft mechanism

#### Output
- JSON: `{success:true}` (save/clear, or `{success:true, cleared:true}`), or `{success:true, draft, updatedAt}` (get)

---

### `chat-history.php`

**Purpose:** Powers the widget's **"Download transcript"** feature, reachable from the chat panel's 3-dot Options menu (bottom sheet, `openOptionsSheet()` in `chat-widget.js`) — not, as an earlier pass at this documentation stated, a "see previous messages" inline reveal. The endpoint supports a `mode=media` lazy-load path for resolving one attachment's real file path on demand, but nothing in the shipped widget or admin dashboard code currently calls it — `mode=full` is the only mode actually exercised end-to-end.

**What It Does:**

#### Input
- GET, no auth (`chatId` is the credential, same trust model as `chat-poll.php`): `chatId`, `mode` (`full` or `media`), and for `mode=media` a `messageId`

#### Processing
- **mode=full:** selects every non-deleted message for the thread (`SELECT m.*` LEFT JOINed to `chat_admin_profiles`, ordered oldest-first). For any message whose `msg_type` isn't `text` and which has a `media_path`, the actual path is stripped out and replaced with a lightweight `media_placeholder` object (`{type, filename}`) — the real `media_path` is set to `null` in the response. This is what the transcript download builds from: `downloadTranscript()` in `chat-widget.js` turns each returned message into one line of a `.txt` file, rendering image/video/audio messages as bracketed placeholders like `[Image attachment]` rather than needing the actual file
- **mode=media:** given a specific `messageId`, looks up just that one message's `media_path` and `msg_type` (scoped to the given thread, and only if not soft-deleted) — this is the on-demand path that *would* reveal the real file for one attachment at a time, but is not currently wired to any UI control

#### Output
- **mode=full:** `{success, messages[]}` — media messages carry `media_placeholder` instead of a real `media_path`
- **mode=media:** `{success, mediaPath, msgType}`

---

### `chat-message-edit.php`

**Purpose:** Lets a customer or admin correct their own already-sent text message.

**What It Does:**

#### Input
- POST, JSON body: `chatId`, `messageId`, `body` (the new text), optional `adminId` (present only for an admin-side edit)

#### Processing
1. Rejects if `chatId`/`messageId` are missing, or if the new `body` is empty (editing to blank isn't supported — deleting is the correct action for that, and the error message says so explicitly)
2. Looks up the thread, then the specific message scoped to that thread; 404s if either isn't found
3. If the message is already soft-deleted (`deleted_at` set), returns HTTP 410
4. **Only `msg_type='text'` messages can be edited** — image/audio/video attachments are rejected with HTTP 403, since "editing" an attachment isn't a meaningful action the way fixing a typo is
5. **Ownership check:** if `adminId` is present, requires admin auth, validates the admin ID is real/active, and requires the message's `from_type='admin'` with a matching `admin_id`. Otherwise requires the message's `from_type='user'` — in both branches, a `system` (or `bot`) message fails ownership on either path and can never be edited by anyone, without needing a dedicated "is this a system message" check
6. **Time window:** compares minutes since `created_at` against `CHAT_EDIT_DELETE_WINDOW_MINUTES`; the check is skipped entirely when that constant is `0` (its current, deliberately unlimited, setting)
7. **Original-text preservation:** `original_body` is set to whatever it already held, or to the message's current `body` if this is the first edit (`$message['original_body'] ?? $message['body']`) — meaning a second or third edit never overwrites the true original with an intermediate edited version
8. Updates `body`, `original_body`, and sets `edited_at = NOW()`

#### Output
- JSON: `{success, messageId, body}`

---

### `chat-message-delete.php`

**Purpose:** Soft-deletes a customer's or admin's own already-sent message (of any type — text, image, audio, or video).

**What It Does:**

#### Input
- POST, JSON body: `chatId`, `messageId`, optional `adminId` (admin-side delete only)

#### Processing
1. Rejects if `chatId`/`messageId` are missing
2. Looks up the thread, then the message scoped to it; 404s if either isn't found
3. If already soft-deleted, returns `{success:true, alreadyDeleted:true}` as a harmless no-op rather than an error
4. **`system` messages can never be deleted** (checked explicitly by `from_type`, HTTP 403) — the automated first response is treated as permanent thread history by design. **`bot`-authored messages are not given this same explicit exemption** in the source; a bot message's ownership check falls through to the same `from_type !== 'user'`/`from_type !== 'admin'` branches every non-owned message hits, so in practice a bot message can't be deleted by a customer or an unrelated admin either — but there's no admin-owned path that could delete it, since `admin_id` on a bot message is `NULL`, not a real admin's ID
5. **Ownership check:** identical structure to `chat-message-edit.php` — admin branch requires admin auth + a matching `admin_id`; customer branch requires `from_type='user'`
6. **Time window:** same `CHAT_EDIT_DELETE_WINDOW_MINUTES` check as edit, skipped when the constant is `0`
7. Sets `deleted_at = NOW()` — a true soft delete; the row and its content remain in the database, just filtered out of normal reads

#### Output
- JSON: `{success, messageId}`, or `{success, alreadyDeleted:true}`

**Read-side effect:** `chat-poll.php` and `chat-history.php` both filter `deleted_at IS NULL` when selecting messages. The widget and admin dashboard render a "This message was deleted" tombstone in place of the original content whenever `deleted_at` is present on a message that does still come through (e.g. in contexts that don't filter it out).

---

### `chat-rate.php`

**Purpose:** Customer-submitted satisfaction ratings tied to a specific admin, plus the admin-side review management (list/get/delete) behind the dashboard's Reviews panel.

**What It Does:**

#### Input
- `action` (`submit`, `list`, `get`, or `delete`)
- **submit (POST, no auth):** `chatId`, `email`, `adminId`, `adminName`, `rating` (1–5), `comment` (truncated to 500 chars server-side)
- **list (GET, admin auth):** no params
- **get (GET, admin auth):** `chatId`
- **delete (POST, admin auth):** `chatId`

#### Processing
1. **submit:** validates `chatId` is present and `email` is a valid address, and `rating` is between 1 and 5. Reads the existing `reviews/{chatId}.json` array (if any), appends a new entry (`chatId`, `email`, `adminId`, `adminName`, `rating`, `comment`, `createdAt`), and writes the whole array back with `JSON_PRETTY_PRINT`. Also attempts an INSERT into the `chat_ratings` DB table — wrapped in its own try/catch so a database failure doesn't prevent the review from being saved, since the JSON file write already succeeded and is treated as the authoritative copy
2. **list:** globs every file in `reviews/`, decodes and flattens them all into one array, sorted by `createdAt` descending
3. **get:** returns the single JSON file for one `chatId` (404 if it doesn't exist)
4. **delete:** deletes that one JSON file from disk (does not touch the corresponding `chat_ratings` DB rows, if any exist for that chat ID)

#### Output
- JSON varies by action: `{success:true}` (submit/delete), `{success, reviews[]}` (list/get)

**Trigger path:** a rating prompt is shown to the customer via the `ticket_closed` event (fired by `chat-admin-done.php`) landing in the widget's chronological event/message stream, which renders `buildTicketClosedCard()` → `buildRatingForm()` — five outline-style Font Awesome face icons from bad to perfect, a 500-character comment field, and a submit button posting to this endpoint's `action=submit`. The admin dashboard's Reviews panel drives `list`/`delete` via `loadChatReviews()`/`deleteChatReview()`.

---

### Database Schema (v2.0 Tables)


| Table | Key Columns | Purpose |
|---|---|---|
| `chat_threads` | chat_id UNIQUE, email, full_name, reason, status ENUM(waiting/active/done), assigned_admin_id, previous_chat_id, unread_for_admin, unread_for_user, last_activity_at, **archived TINYINT(1) DEFAULT 0** | One row per conversation thread; `archived` (added by `chat-migrate.php` for pre-existing DBs) hides a thread from the four normal tabs without touching `status` — see `chat-manage.php` |
| `chat_messages` | thread_id FK (ON DELETE CASCADE), from_type ENUM(user/admin/system/**bot**), admin_id, msg_type ENUM(text/image/audio/video), body, media_path, status ENUM(sent/delivered/seen), group_id, edited_at, original_body, deleted_at | All messages; group_id links grouped multi-media sends and doubles as idempotency key. `'bot'` added to `from_type` in v2.1 (see Deployment note under the Chat Bot Feature section) |
| `chat_events` | thread_id FK, event_type ENUM(admin_joined/admin_left_done/ticket_closed), admin_id | Timeline events rendered in merged chronological order with messages |
| `chat_admin_profiles` | name, position, photo_url, blue_check, active | Admin identities used in chat; no FK constraint on admin_id in messages (intentional) |
| `chat_typing` | (thread_id, who) PK, expires_at | 6-second typing indicator, polled every poll cycle |
| `chat_rate_limits` | rate_key PK, attempts, window_start | Sliding window rate limiting for chat-start.php (10/10min per IP) |
| `chat_drafts` | draft_key PK VARCHAR(64), body, updated_at | Composer drafts + server-side unread count (key: unread:{chatId}) |
| `chat_ratings` | chat_id, email, admin_id, admin_name, rating TINYINT(1-5), comment TEXT | Submitted ratings; also mirrored in /admin/reviews/{chatId}.json |

---

### Concurrency & Safety (v2.0)

- **Two admins claiming same thread:** `UPDATE WHERE status='waiting'` + `rowCount()` check. Atomic, race-safe.
- **Double-send prevention:** client generates `idempotencyKey` per send attempt; server checks `group_id` in chat_messages before inserting.
- **Admin reply auto-claim:** same `WHERE status='waiting'` guard in chat-admin-reply.php.
- **Token expiry:** staged upload tokens expire after 2 hours (sidecar JSON). Probabilistic cleanup on each upload request.
- **Cross-filesystem file moves:** `chatMoveStagedFile()` uses rename() with copy()+unlink() fallback.
- **Variable definition order:** `$db = chatDb()` always called before any query — confirmed across all files (critical on chat-send.php and chat-admin-reply.php where idempotency check uses $db early).

---

### Key Design Decisions (v2.0)

- **Media as inline display:** images and videos render as full `<img>`/`<video>` elements in both widget and admin dashboard. The downloadable-link approach was trialled and reverted.
- **Chronological merge:** events and messages are sorted by `created_at` into one array before rendering in both `handlePollResult()` (widget) and `renderAdminPollResult()` (dashboard). This ensures `ticket_closed` card and `admin_joined` banner land in their true positions in the message stream.
- **Admin photo paths:** stored as relative paths (e.g. `chat-admin-photos/file.jpg`) in DB. Prefixed with `/admin/` in the widget and dashboard at render time.
- **`from_type='system'`** for auto-responses — cannot be edited or deleted, shown with dashed italic bubble.
- **Voice recording MIME:** client detects best supported type (audio/webm;codecs=opus → audio/webm → audio/mp4 → audio/ogg → video/webm fallback). Server accepts video/webm for audio kind (Android/Brave Beta labels audio-only recordings as video/webm).
- **Edit/delete window:** `CHAT_EDIT_DELETE_WINDOW_MINUTES = 0` = unlimited. Server skips the check when value is 0.
- **Ticket Closed persistence:** `pc_ticket_closed_{chatId}` in localStorage ensures the closed state is known even before the first poll returns.


---

## v2.1 — Chat Bot Feature

### About
A single automated identity ("bot") that sends pre-written, admin-configured responses at specific trigger points in a conversation — never live replies, only structured text set up in advance. Gets its own top-level nav section in the dashboard ("Chat Bot" → "Bot Profile"), separate from Live Chat's own sub-sections, per explicit design decision. No new database table for its settings — everything the admin configures lives in one JSON file, since it's lightweight repetitive structured text with no relational data to model. The messages the bot actually *sends*, however, are real rows in the existing `chat_messages` table, using a new `from_type` value (`'bot'`) added to that column's existing ENUM.

### Storage model
- **Bot settings** (name, photo, all message templates): `/admin/chat-bot-profile.json`, one file, full-replace on every save. Read via `chatGetBotProfile()`, written via `chatSaveBotProfile()` (both in `chat-config.php`). Concurrency: writes use `LOCK_EX`, safe against two admins saving at the same instant; reads never modify the file so any number of simultaneous customers triggering the bot is never a risk.
- **Bot's sent messages**: normal rows in `chat_messages`, `from_type = 'bot'`, `admin_id = NULL`. Rendered using the same avatar+name layout as `admin` messages (not the plain dashed-italic `system` style) — the bot's name/photo are attached client-side at poll time (see below), not stored per-message, since the bot's identity can change and old messages should reflect whatever the bot's current name/photo is.

### The `{name}` placeholder
Every message template an admin writes can include the literal text `{name}` anywhere. `chatBotSubstitute($template, $fullName)` (in `chat-config.php`) replaces it with the customer's actual first name **at the moment the message is actually inserted for that specific customer** — never at the point the admin saves the template. This is what lets one saved template correctly personalize for every different customer who triggers it. Falls back to the word "there" if a name is somehow unavailable, so a customer is never shown a literal unrendered `{name}`.

### The four trigger scenarios (exact firing rules)
This is the part most likely to need re-explaining to a future AI or admin, so it's spelled out precisely:

**Scenario A — chat starts (brand new thread):**
1. Customer selects a reason and starts a chat → bot's per-reason welcome message fires immediately (`chat-start.php`, Path 3 — genuinely new email, no prior thread). Falls back to `welcomeFallback` if that reason has no message set; falls back further to the old hardcoded `CHAT_AUTO_RESPONSES` constant if the bot is inactive entirely.
2. Customer sends their actual first message → bot's `firstMessageAck` fires once (`chat-send.php`), gated by `$isFirstUserMessage && !$wasReopenedFromDone` (a thread's very first-ever user message, and not a reopen).

**Scenario B — a closed (Done) chat is reopened:**
1. If the customer just types directly into an already-open chat that happens to be Done — no gate revisit, no reason re-selection — sending that message flips the thread back to `waiting` (`chat-send.php`, `$wasReopenedFromDone = true`) and fires `reopenFirstMessageAck` once. **No per-reason welcome message fires in this path.**
2. If the customer instead goes back to the pre-chat gate (via the widget's back button) and explicitly re-submits with a **different** reason than the thread's original stored one, `chat-start.php`'s resume paths (both the explicit-chatId path and the email-match path) detect this mismatch and fire the per-reason Scenario B welcome message (`reopenMessages[$reason]`, or `reopenFallback`) immediately — **only if the thread's current status is `done`**. The thread's own stored `reason` column is never modified by this — it's a one-time comparison, not a reassignment. When their next message then arrives, `reopenFirstMessageAck` still fires as usual on top of that.
3. If they go back to the gate and pick the **same** reason as before, it's treated identically to case 1 — no per-reason welcome, just `reopenFirstMessageAck` when they message.

**Important implementation note:** `firstMessageAck` (Scenario A) and `reopenFirstMessageAck` (Scenario B) are two deliberately separate fields — they cannot share the same text, and the two triggering conditions (`$isFirstUserMessage && !$wasReopenedFromDone` vs. `$wasReopenedFromDone`) are mutually exclusive by construction, since a reopened thread by definition already has prior user messages.

### Files
- **`chat-bot-profile.php`** (new) — admin CRUD for the bot. `action=get` (admin auth, returns current JSON profile), `action=save` (admin auth, full-replace write — reads `name`, `active`, `welcomeMessages{}`, `welcomeFallback`, `firstMessageAck`, `reopenMessages{}`, `reopenFallback`, `reopenFirstMessageAck`; `welcomeMessages`/`reopenMessages` arrive as JSON-encoded objects keyed by reason and are filtered to only the valid reason keys from `CHAT_REASONS` before saving), `action=uploadPhoto` (admin auth, multipart, JPEG/PNG 5MB max, reuses the `chat-admin-photos/` directory, deletes the old photo file on replace).
- **`chat-config.php`** (modified) — added `CHAT_BOT_PROFILE_FILE` constant, `chatGetBotProfile()`, `chatSaveBotProfile()`, `chatBotSubstitute()`.
- **`chat-start.php`** (modified) — both resume paths (explicit chat ID, email match) now compare the freshly-submitted `reason` against the thread's stored `reason` and fire the Scenario B per-reason welcome message when they differ and the thread is `done`. The brand-new-thread creation path fires the Scenario A per-reason welcome, falling back to bot's general fallback, falling back further to the original hardcoded `CHAT_AUTO_RESPONSES` constant only when the bot is inactive.
- **`chat-send.php`** (modified) — `$wasReopenedFromDone` flag set when a customer's message flips a thread from `done` back to `waiting`. `$isFirstUserMessage` computed by counting prior `from_type='user'` rows before this send's own insert. Fires `firstMessageAck` (Scenario A) or `reopenFirstMessageAck` (Scenario B) based on those two flags, inside the same transaction as the customer's own message insert — this ordering is what makes the bot's response land chronologically *after* the customer's message, not before it (an earlier version fired the reopen bot message before the customer's own message was even inserted, which was a real bug that got fixed).
- **`chat-poll.php`** (modified) — `botProfile` (name + photoUrl only, never the message templates) is now **always** included in the response regardless of the bot's current `active` state. This was a real bug fix: gating it behind `active` meant that toggling the bot off would retroactively strip the name/photo from every previously-sent bot message on every future poll, since this is re-evaluated fresh every single poll rather than fixed at the moment each message was created. Also: the old hardcoded "Our team typically replies within a few hours..." (`noAnswerNotice` / `CHAT_NO_ANSWER_MESSAGE`) was removed entirely from this endpoint's response and from the widget's rendering — retired now that the bot's own fallback messages cover this.
- **`chat-admin-poll.php`** — at the time v2.1 shipped, this had **not** received the same `botProfile` treatment as `chat-poll.php` despite being documented elsewhere in this file as if it had — a real doc/code mismatch, now resolved. **Fixed in v2.2:** `chat-admin-poll.php`'s response now includes `botProfile`, and `admin-dashboard.html`'s admin-side rendering attaches and displays it, matching the widget's treatment — see `## v2.2` at the bottom of this document.
- **`chat-widget.js`** (modified) — `handlePollResult()` attaches `json.botProfile`'s `name`/`photoUrl` onto any message with `from_type === 'bot'` before rendering, so the existing admin-message rendering path (avatar + name + left-aligned bubble) picks it up with zero separate render branch needed. CSS: `.pcw-msg-row.bot` added alongside `.admin` in every relevant selector (row alignment, bubble background, download-chip styling) — a message with an unrecognized `from_type` (e.g. from a database that hasn't had the ENUM migration applied) is now defensively normalized to render as `admin` rather than falling through with no matching CSS class at all.
- **`admin-dashboard.html`** (modified) — new "Chat Bot" nav section → "Bot Profile" panel (`chatBotPanel`, wired via `switchPanel('chatBot')` → `loadChatBotProfile()`). Form fields: bot name, photo upload with live preview, active toggle, one textarea per reason for Scenario A welcome + its fallback, one shared `firstMessageAck` field, one textarea per reason for Scenario B welcome + its fallback, one shared `reopenFirstMessageAck` field. `buildBotReasonFields()` dynamically renders the four per-reason textareas from a `CHAT_BOT_REASONS` array that mirrors the widget's own reason list exactly. A persistent `{name}` usage hint is shown once at the top of the panel, applying to every field below it. `renderAdminMessage()` and `renderAdminPollResult()` both updated with the same bot-info-attachment and defensive `from_type` handling as the widget.

### Deployment note (real issue hit during this build, worth preserving)
`chat_messages.from_type` is a MySQL ENUM column. Adding `'bot'` as a valid value requires running `chat-migrate.php`'s `ALTER TABLE chat_messages MODIFY from_type ENUM('user','admin','system','bot') NOT NULL` migration on any database that was set up before this feature existed. This migration was done already by the user, but If that migration hasn't been run, here will be the potential cause; bot messages still get inserted (PHP doesn't reject the write outright under default settings) but render with no name, no photo, and a bare `?` avatar in both the widget and dashboard — this exact symptom was hit and confirmed live during development, and was resolved by running `chat-migrate.php` once and then deleting it as usual.


---

## v2.2 — Dashboard Redesign, Chat Bot Refinements & Email Notifications

### About
A batch of work covering four separate tasks, documented together here
following the same versioned-section convention as v2.0/v2.1: a full
visual redesign of `admin-dashboard.html` (originally scoped as one task,
restructured mid-build across two workers given its size), refinements to
the chat bot's pacing and the live typing/recording indicators, two small
prechat-widget additions, and a brand-new email notification system for
the live chat.

### `admin-dashboard.html` — Visual Redesign

**Build history worth knowing:** the first approach explored a
CSS-variable-driven 3-theme switcher (informally "Layered Suite" / "Data
Console" / "Calm Panel"). That approach was superseded mid-build — the
final, shipped direction is **Layered Suite only**; "Data Console" and
"Calm Panel" were deleted outright, not kept as unused alternate themes.
Most line-range references from that earlier phase no longer correspond to
anything in the current file — treat what follows as the actual, current
state, not the history.

**Palette — ⚠️ unapproved, do not treat as correct:** the redesign as
built uses a "Lavender Mist" palette (Design System Section 20 has the
exact values, if referencing what was actually shipped). **This was the
assigned worker's own color choice, not something the CEO asked for or
has approved — the CEO's own words: "the Lavender Mist color was a wrong
choice made by the previous worker and not something I want."** The
correct, current admin dashboard color reference is Navy `#10214b` /
Gold `#d7bd88` / Orange `#ED7624`, documented in full in Section 15 above
— see that section's own note for more detail, including that these
values may themselves change over time at the CEO's direction. Whoever
next touches this redesign's colors should bring them back in line with
Section 15's palette (or whatever the CEO specifies at that time), not
preserve Lavender Mist. The structural/layout work described below this
line (sticky header, modal pattern, notification system, dropdown
component, icon replacements, mobile nav consolidation) is unaffected by
this note — only the color choice is in question.

**What changed, as 10 distinct pieces of work:**
1. **Header redesign** — new profile dropdown (acting-admin identity,
   matching the existing acting-admin concept from the Conversations panel
   — Section 1G).
2. **Sticky header + sidebar** — both now stay fixed on scroll, rather
   than scrolling away with page content.
3. **Mobile nav consolidation** — the dashboard's navigation collapses
   into a single icon-driven menu on mobile rather than the desktop
   sidebar's full layout.
4. **Applications panel — icon actions redesign** — row actions
   consolidated into a 3-dot popover menu rather than a row of always-
   visible icon buttons.
5. **Applications panel — mobile layout** — rows become expandable/
   collapsible on mobile rather than a horizontally-scrolling table.
6. **Modal redesign** — every modal across the dashboard (pet
   publish/edit, application detail, chat bot profile, admin profiles,
   etc.) now follows one standardized visual pattern instead of each
   having its own bespoke styling.
7. **Icon replacements** — the Publish Pets and Reviews panels' icons were
   replaced with Font Awesome equivalents (see Section 1K's Font Awesome
   fix — this reuses that same stylesheet, already present in `<head>`,
   rather than adding a second icon system).
8. **Notification system** — new; a dashboard-wide notification
   surface, distinct from the chat system's own in-widget notifications.
9. **Dropdown redesign** — every dropdown across the dashboard now uses
   one standardized custom-dropdown component instead of native `<select>`
   elements or bespoke per-panel dropdown markup.
10. **Applications modal** — now displays `approved_by`/`approved_position`
    once an application is approved, matching the format documented in
    Section 9 above (name, with position in small font underneath).

**Known open item, not yet fixed:** a `showToast()` call exists in the
dashboard's JS but the function itself is not defined anywhere in the
current file — any code path that calls it will throw rather than showing
a toast notification. Pre-existing, not introduced by this redesign, but
more likely to actually surface now given how much UI code changed around
it — worth a dedicated small fix.

### Chat bot pacing & live indicators (`chat-widget.js`, `chat-typing.php`, `chat-poll.php`, `chat-admin-poll.php`, `admin-dashboard.html`)

- **Bot response pacing.** Automated bot replies (v2.1) previously
  appeared instantly, reading as obviously automated. A believable
  processing delay now precedes the message, followed by a typewriter-
  style reveal — purely a frontend presentation change; bot messages are
  still inserted into `chat_messages` exactly as v2.1 documents, only how
  the already-arrived message is *revealed* changed. Deliberately **not**
  applied to the admin dashboard's own message rendering — artificially
  delaying a message for staff would work against the tool's purpose.
- **Live typing indicators, made to actually work end to end.**
  `chat-typing.php`'s upsert-based ping system existed but wasn't fully
  wired up; both `chat-poll.php` (as `adminTyping`) and `chat-admin-poll.php`
  (as `userTyping`) now correctly surface it, and both the widget and the
  admin dashboard render it live. The same treatment was applied to
  voice-recording-in-progress indication.
- **Lesson learned — `chat_typing.who` ENUM conflict.** This column was a
  strict `ENUM('user','admin')`, which silently rejected the new
  `user_recording`/`admin_recording` ping values the recording-indicator
  work needed. Widened in both the fresh-install schema (`chat-setup.php`)
  and as a migration for existing databases (`chat-migrate.php`).
- **Rating-notification persistence bug fixed.** The post-Done "Ticket
  Closed" rating prompt (v2.0) used to stay visible indefinitely if a
  customer never rated or dismissed it, which then blocked the prompt from
  firing again after a *later* Done chat, since the widget still
  considered the earlier prompt "open." Now: if a customer sends a new
  message reopening a Done chat without ever having resolved the previous
  prompt, that stale prompt is automatically dismissed the moment the new
  message is sent — guaranteeing the rating prompt reliably fires again
  after every future Done chat.
- **Lesson learned — admin-side bot attribution gap, now fixed.** As
  flagged in the v2.1 Files list above, `chat-admin-poll.php` was missing
  the `botProfile` block that `chat-poll.php` already had, and
  `admin-dashboard.html`'s `renderAdminPollResult()`/`renderAdminMessage()`
  didn't attach or render it — bot messages had no name/photo on the admin
  side, unlike the customer widget. Fixed by mirroring the exact pattern
  already proven in `chat-poll.php`/`chat-widget.js`.

### Prechat widget additions (`chat-widget.js`)
- **Phone number field.** The prechat form (name, email, reason dropdown,
  optional existing-chat-ID field) now also collects an optional phone
  number, styled to match the form's existing custom input pattern.
- **Panel-open entrance animation.** A one-time "amoeba"-style organic
  blob animation plays the moment the chat panel opens after prechat
  submission — purely decorative, does not delay the panel becoming
  usable.

### `chat-mail.php` (new) — Live chat email notifications
Reuses the site's existing "Chat by Email" mail infrastructure
(`contact-config.php`'s config/mailer setup) rather than building a
separate mail system — call sites wrap every send in try/catch with
`error_log()` on failure, so a mail failure can never break the chat
request itself. Exactly two trigger conditions, both hooked into
conditions `chat-start.php`/`chat-send.php` already computed for other
purposes, not re-derived:
1. **A genuinely new chat thread is created** (`chat-start.php`) — sends
   to Pet Center's admin address *and* a separate notification to the
   customer's own prechat email.
2. **A customer sends a message into a thread whose status is `done`**
   (`chat-send.php`'s `$wasReopenedFromDone` flag) — sends to the admin
   address only; the customer isn't emailed here since they're still
   inside the same chat/ID they already started with.

No other message send triggers an email.

**Lesson learned — stale CORS domain, found and fixed.** While working in
this area, `contact-handler.php`/`contact-config.php` were found hardcoded
to `docs.petcenterco.com` instead of the site's real domain,
`adopt.petcenterco.com`. Corrected. This had been dormant (same-origin
requests mask a CORS mismatch) but was a real latent problem waiting for
the day it wasn't same-origin anymore.

### Open items carried forward, not yet resolved
- **`chat-start.php`'s `previous_chat_id` column** appears unused by any
  current code path. Likely stale from an earlier design, but genuinely
  ambiguous — needs a decision from whoever owns this file's intent before
  either removing it or wiring it up to something.
