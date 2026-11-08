# PetCenter Redesign Accounts — Canonical Operational Brief

**Status:** Canonical brief for the public-adoption redesign and the Admin Dashboard/Rehoming redesign.

**Applies to:** `DESIGN-01` and `DESIGN-02`.

**Repository:** `Victorsesan/petcenter-monorepo`

**Important:** This is a redesign wave, not permission to discard working behavior. The existing routes, backend contracts, authentication, authorization, accessibility, RTL behavior, and project data flows must survive the redesign.

**Scope lock:** This file is for `DESIGN-01` and `DESIGN-02` only. A `WEB-01` through `WEB-05` account must not use this brief. A `DESIGN-*` account must not use `WEB-ACCOUNTS-OPERATIONAL-BRIEF.md`. If a launcher or user message names a different account family than the brief being read, stop using the mismatched brief, select the brief matching the account ID, and continue without asking the coordinator to choose between unrelated assignments.

## 1. Redesign assignments

| Account | Assignment | Branch | Primary ownership |
|---|---|---|---|
| `DESIGN-01` | Public Adoption Website redesign | `redesign/public-adoption` | Shared public CSS/JS, public design tokens, header, footer, navigation, adoption components, responsive public pages, and licensed imagery |
| `DESIGN-02` | Admin Dashboard, Rehoming Platform, and authentication-gateway redesign | `redesign/admin-rehoming` | Admin design system, rehoming visual system, login/signup gateway interface, responsive admin/rehoming components, and licensed imagery |

The coordinator merges accepted work into:

```text
integration/second-round-final-2026-11-06
```

Neither redesign account may push directly to `main`.

## 2. Mandatory background reading

Before inspecting or editing application source, both redesign accounts must read and record:

1. `adoption-website-project-summary.md`
2. `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`
3. `PETCENTER_DESIGN_SYSTEM_v1.0.md`
4. `INTEGRATION-PROGRESS.md`
5. `TODO.md`
6. `ROADBLOCKS-AND-FIXES.md`
7. `WORKER-GIT-SYNC-PROTOCOL.md`
8. `VDM-GIT-SYNC-COORDINATION-PROTOCOL.md`
9. `INTEGRATOR-GIT-SYNC-MERGE-PROTOCOL.md`
10. `docs/second-round/CORE-DOCUMENT-READING-GATE.md`
11. `docs/second-round/SECOND-ROUND-MANUS-COORDINATION.md`
12. `docs/second-round/WORKER-PACKAGE-INVENTORY.md`
13. `docs/second-round/SECOND-ROUND-ISSUE-REGISTER.md`
14. The assignment and completion evidence relevant to the redesign scope.
15. The reports produced by the five web-project accounts after their baseline work is merged or otherwise made available.
16. `docs/second-round/HOSTINGER-PREDEPLOY-AND-ROLLBACK-CHECKLIST.md`

Both core structural summaries are mandatory even when the account is redesigning only one area. They document the existing live files, paths, functions, architecture, and dependencies.

If the user supplies a scraped/reference HTML file, inspect it as a design reference. Do not assume that its proprietary code, branding, fonts, or images may be copied. Use the visual and structural direction while implementing an original, compatible PetCenter system.

## 3. Baseline-before-redesign rule

Do not start a destructive rewrite before the web-project baseline is understood. Before editing:

1. inspect the current routes and source structure;
2. read the baseline reports from `WEB-01` through `WEB-05` where available;
3. identify current shared and page-specific CSS/JS;
4. identify all backend and AJAX contracts;
5. identify authentication and authorization boundaries;
6. identify existing accessibility and RTL behavior;
7. record known 404s, console errors, and missing assets;
8. create a component and migration map;
9. determine which shared files require coordinator approval.

If a baseline bug is found inside the redesign scope, fix it as part of the redesign and test it. If it is outside scope, coordinate with the owning web account rather than silently changing its files.

## 4. Coordination and synchronization

Git cannot show unsaved changes from another account. Real-time uncommitted collaboration is not safe. Use near-real-time commit visibility instead:

1. Work only in the assigned redesign branch.
2. Commit small logical units.
3. Push completed units promptly.
4. Monitor branch and pull-request updates.
5. Fetch before starting work and immediately before committing.
6. Do not automatically pull over an active worktree.
7. Do not overwrite another account's changes.
8. Notify the coordinator before changing a shared file or contract.
9. Reconcile deliberately with the latest `integration/project-audit` before handoff.
10. The coordinator owns integration merges and staging refreshes.

Before starting:

```bash
git fetch --all --prune
git status --short --branch
git branch --show-current
git log -1 --oneline
```

Before every commit:

```bash
git fetch origin
git diff --check
git status --short --branch
```

Never force-push. Never use `git add .` or `git add -A`; stage explicit paths only. Never commit secrets or protected runtime data.

## 5. Shared design-system requirements

Create a documented, reusable design system containing:

- color tokens;
- typography scale and font fallbacks;
- spacing tokens;
- layout/container rules;
- border radius and elevation;
- buttons and button states;
- links;
- cards;
- inputs and forms;
- selects and checkboxes;
- alerts and notifications;
- loading states;
- empty states;
- error states;
- modal and drawer behavior;
- header and navigation behavior;
- footer behavior;
- focus-visible states;
- disabled states;
- reduced-motion behavior;
- responsive breakpoints;
- RTL logical properties and mirroring rules;
- accessible color contrast.

The design should remain recognizably PetCenter: navy, orange, gold, and supporting colors from the repository design system may be refined, but do not invent an unrelated brand identity.

Use shared CSS and JavaScript assets for repeated behavior. Keep page-specific styles only where a page genuinely needs unique behavior. Do not remove embedded code until the equivalent shared behavior is loaded and verified on every affected page.

## 6. DESIGN-01 — Public Adoption Website

### Scope

Redesign the public adoption pages while preserving their current URLs, endpoint contracts, forms, chat integration, content behavior, and asset conventions.

### Required implementation sequence

1. Inspect the supplied reference HTML and the PetCenter design system.
2. Inventory all repeated embedded CSS and JavaScript across adoption pages.
3. Identify shared header, navigation, footer, hero, section, card, form, alert, and CTA patterns.
4. Define public design tokens and component classes.
5. Create the shared public CSS layer.
6. Create shared public JavaScript only for genuinely shared interactions.
7. Implement a shared header and footer without breaking page-relative paths.
8. Migrate representative pages first: homepage, category page, detail page, blog page, contact/form page, and status/application page.
9. Validate shared assets from every nested route.
10. Migrate the remaining pages incrementally.
11. Remove duplicated embedded styles/scripts only after regression checks pass.
12. Preserve chat-widget behavior, accessibility widget behavior, forms, AJAX, and backend endpoints.
13. Verify desktop, tablet, mobile, keyboard, reduced motion, accessibility, and RTL behavior.

### Public-content and imagery requirements

The current pages are text-heavy. Improve the visual communication with carefully selected image areas where they support the content, such as hero sections, adoption-category sections, service explanations, blog cards, testimonials, and informational blocks.

Use only:

- PetCenter-owned or existing repository images;
- user-supplied images;
- clearly licensed free images from sources such as Openverse or Wikimedia Commons;
- other sources with clear permitted use.

Getty Images and other commercial stock sources must not be used without an appropriate license. For every new image, record source and license, use meaningful alt text, optimize the file, provide responsive sizing and fallback handling, and avoid implying that a generic image is a specific available animal.

## 7. DESIGN-02 — Admin Dashboard, Rehoming, and Gateway

### Scope

Redesign the Admin Dashboard, Rehoming Platform user-facing pages, and the missing login/signup gateway. Preserve authentication, roles, authorization, API contracts, file paths, protected routes, forms, uploads, and data behavior.

### Admin design priorities

The admin system should prioritize:

- clear information hierarchy;
- dense but readable tables and cards;
- safe destructive-action confirmation;
- visible status and validation feedback;
- responsive data presentation;
- accessible forms and controls;
- predictable navigation;
- loading, empty, error, and success states;
- secure authentication and session behavior.

Do not make administrative controls look like public marketing pages when that would reduce clarity or safety.

### Rehoming design priorities

The Rehoming Platform is the newest and highest-risk project. Use a coherent user-facing design for visitors, merchants, and account pages while preserving role boundaries and protected flows. The rehoming design account must coordinate with the rehoming web accounts before changing route shells, authentication markup, shared helpers, or API-connected forms.

### Authentication-gateway interface

Design the real gateway that replaces the current `Coming soon` destination. It must include:

- login and signup entry points;
- Google sign-in presentation;
- clear not-configured state when OAuth credentials are absent;
- links to Privacy Policy and Terms of Service;
- accessible labels, keyboard flow, focus states, validation, loading, and errors;
- safe post-login return behavior;
- no fake successful login.

The redesign must not weaken the server-side requirements implemented by `WEB-04`: state/nonce checks, callback validation, session security, CSRF, role checks, and protected routes remain authoritative.

## 8. Accessibility, RTL, performance, and quality

Every redesigned page must be checked for:

- semantic HTML;
- unique and meaningful accessible names;
- keyboard navigation;
- visible focus;
- sufficient color contrast;
- screen-reader-friendly labels and errors;
- reduced-motion support;
- responsive behavior at desktop, tablet, and mobile widths;
- RTL logical properties and mirrored layout behavior;
- no horizontal overflow at supported widths;
- optimized images and lazy loading where appropriate;
- no broken console or network requests;
- no broken CSS/JS/image/JSON paths;
- no new 404s;
- no regression of forms, AJAX, authentication, or redirects.

Do not rely on screenshots alone. Use browser interaction, console/network inspection, syntax checks, route checks, and accessibility checks.

## 9. Free and open-source requirements

Use free and open-source tools and dependencies only. Do not introduce paid UI kits, paid design systems, paid image subscriptions, paid monitoring, or paid runtime services. Do not add a new programming language solely for perceived security or animation. Modern CSS, JavaScript, PHP, and the existing hosting model are sufficient unless a documented technical requirement proves otherwise.

## 10. Research requirement

Research unfamiliar design and implementation problems using MDN, WAI guidance, browser compatibility documentation, established open-source projects, reputable design-system documentation, and GitHub examples. Record the important sources and decisions in the final report. Do not copy proprietary code or assets merely because they appear in a reference design.

## 11. No-pending-work rule

Do not create a report that only says a redesign issue should be fixed later. Implement and test every in-scope correction. If a genuine external dependency remains—such as final OAuth credentials, final legal wording, or user approval of licensed imagery—implement the technical structure, test the safe not-configured state, and identify that exact external requirement. Do not call it completed until the implementation and verification are complete.

## 12. Final redesign deliverables

Each redesign account must provide:

- account ID, assignment, and branch;
- mandatory documents read;
- baseline and component/migration map;
- design tokens and shared components implemented;
- files added, modified, deleted, or moved;
- routes and backend contracts preserved;
- responsive viewport results;
- accessibility and RTL results;
- browser console/network results;
- image sources and licenses;
- research sources and design decisions;
- tests executed and outcomes;
- commit hashes;
- cross-project dependencies;
- exact external configuration requirements;
- confirmation that no secrets or protected runtime data were committed;
- confirmation that no conflict markers remain;
- confirmation that all independently verifiable redesign work was completed;
- additional security, accessibility, performance, UX, compatibility, route, data, and deployment findings discovered during the work;
- exact reference to any backup, rollback, Hostinger, or release-approval risk found.

Read and follow `TODO.md` at the start and end of each session. Add only completed, actionable, addressed information under its prescribed rules; do not use it as a plan dump.

No account may deploy, upload, push, or trigger an automatic deployment to Hostinger. Before production publication, the coordinator must create and verify both a repository backup and a Hostinger backup, prepare a rollback path, and obtain the user's explicit permission for the exact release commit and target. Workers must remind the coordinator of this gate in their reports and add any additional release risks they discover. Follow `docs/second-round/HOSTINGER-PREDEPLOY-AND-ROLLBACK-CHECKLIST.md`.

## 13. Short launcher to send with this file

Send only this short message to a redesign account after giving it repository access:

> You are `[ACCOUNT ID]`. Clone the repository, read `docs/second-round/REDESIGN-ACCOUNTS-OPERATIONAL-BRIEF.md` completely, then follow only the section for your assigned redesign branch. Read both structural summaries before source inspection, preserve existing routes and contracts, use free/open-source tools and properly licensed images, synchronize before commits, implement and test the redesign, push only your branch, and report completed work with evidence.
