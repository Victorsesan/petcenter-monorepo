# PetCenter Web Accounts — Canonical Operational Brief

**Status:** Canonical brief for the current web-project audit, debugging, authentication, testing, and integration wave.

**Applies to:** WEB-01 through WEB-05.

**Repository:** `Victorsesan/petcenter-monorepo`

**Site root:** `adopt/`

**Important:** This brief supersedes older round-one and second-round launcher wording where the older wording conflicts with the current seven-account plan. Historical completion documents remain evidence only; they are not final implementation authority.

## 1. Purpose and operating model

Five accounts will inspect, repair, secure, test, and integrate the four functional web areas:

1. Unified Status Dashboard.
2. Admin Dashboard.
3. Public Adoption Website.
4. Rehoming Platform, split between authentication/backend ownership and broader functional QA/integration ownership.

The two PetCenter structural summaries are mandatory background documents for **every** account, even when the account is not assigned to that area:

- `adoption-website-project-summary.md`
- `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`

They describe the existing live file paths, functions, architecture, historical decisions, and relationships between files. They must be read before source inspection or editing.

The repository currently contains the project summaries, assignment trackers, synchronization protocols, design system, reports, and historical evidence under `docs/second-round/completion-evidence/`. Completion evidence is historical input to reconcile against current code. It must never be accepted as proof that code is correct, present, reachable, secure, or deployed.

## 2. Accounts, branches, and ownership

| Account | Assignment | Branch | Primary ownership |
|---|---|---|---|
| `WEB-01` | Unified Status Dashboard | `qa/unified-status` | Unified status routes, forms, status lookup, related APIs, assets, and tests |
| `WEB-02` | Admin Dashboard baseline QA | `qa/admin-dashboard` | Existing admin UI, admin APIs, authentication, chat management, uploads, reviews, applications, statistics, and tests |
| `WEB-03` | Public Adoption Website QA | `qa/adoption-website` | Public adoption pages, public assets, routes, scripts, styles, forms, and browser verification |
| `WEB-04` | Rehoming authentication gateway and backend | `qa/rehoming-auth-backend` | Rehoming login/signup gateway, sessions, OAuth structure, route protection, authorization, legal-page routes, and security tests |
| `WEB-05` | Rehoming functional QA and integration | `qa/rehoming-functional-integration` | Rehoming route/file map, visitor and merchant flows, listings, posting, APIs, assets, paths, and cross-project integration tests |

No account may push directly to `main`. The coordinator owns merges into the integration branch:

```text
integration/project-audit
```

The current `main` branch must remain protected until the coordinator has reviewed and merged tested work.

## 3. Mandatory reading gate

Before opening application source or editing code, every account must read and record these documents:

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
14. The assignment tracker for the account's project.
15. Every available matching completion or handoff document under `docs/second-round/completion-evidence/`.
16. Current reports relevant to the assigned scope.

If a referenced completion file is absent, record the exact missing path as an evidence gap and continue all independently verifiable work. Do not invent the missing implementation and do not stop the entire assignment.

Every account must perform the work sequence itself: implementation and tests, coordination/review of the branch, then integration reconciliation and bug triage. Legacy instructions requiring manual uploads, avoiding GitHub, or treating completion files as the only handoff are superseded by this Git-aware brief and the current protocols.

## 4. Repository access and secret safety

Use the environment's existing authorized GitHub connector, authorized CLI, or approved private credential mechanism first. A personal access token is a last resort only and must be transient.

Never:

- put a token in this repository;
- put a token in a prompt, report, issue, URL, script, or shell history;
- echo a token to terminal output;
- commit `.env`, secrets, passwords, OAuth client secrets, applicant data, or production credentials;
- place credentials in a saved Git remote URL;
- request a token when authorized repository access already works.

Keep the Git remote credential-free. Never inspect, copy, modify, or commit protected runtime content such as `.env`, `config.php`, `pet.json`, `applications/`, `uploads/`, `chat-media/`, or `logs/` unless the current project protocol explicitly authorizes a safe, controlled test and the coordinator confirms the boundary.

## 5. Coordination and synchronization system

Git does not expose another account's unsaved edits. A remote branch becomes visible to other accounts only after save, commit, and push. Do not promise or attempt unsafe live merging of uncommitted work.

Use this safe near-real-time system:

1. Each account works only in its assigned branch and worktree.
2. Each account commits small logical units and pushes completed units promptly.
3. GitHub branch and pull-request notifications are enabled for all accounts and the coordinator.
4. A coordinator or approved lightweight monitor records each push: account, branch, commit, changed files, tests, and conflict status.
5. The coordinator merges accepted commits into `integration/project-audit`.
6. A stable staging service is refreshed only from accepted integration commits.
7. Every account fetches before beginning a new work unit and immediately before committing.
8. No tool may silently pull or overwrite files in an account's active worktree.
9. A shared file or shared contract may be changed only by its owner or after explicit coordination.
10. If another account changes a file currently being edited, stop, fetch, inspect the diff, and reconcile deliberately.

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

Do not force-push. Do not reset away another account's changes. Do not use `git add .` or `git add -A`; stage explicit paths only.

The coordinator may require a rebase or merge from the latest `integration/project-audit` before accepting a branch. If reconciliation affects a protected or shared file, report the conflict rather than silently choosing a side.

## 6. General execution rules

Each account must:

1. Build a task/completion/status matrix.
2. Inventory current files, routes, assets, endpoints, forms, redirects, and dependencies.
3. Compare historical completion claims with current code, Git history, project summaries, and browser behavior.
4. Reproduce reported defects with a test or controlled check.
5. Fix confirmed in-scope defects immediately.
6. Apply clearly necessary security, accessibility, path, compatibility, or integration improvements without waiting for permission.
7. Preserve working API contracts and URLs unless the current requirements require a documented correction.
8. Search before creating a new file, route, helper, endpoint, stylesheet, or component.
9. Test after every meaningful change.
10. Document only completed and verified work in the report and `TODO.md`.

There must be no artificial pending list for work that is within the account's authority and resources. A genuine external dependency, such as an OAuth secret or final legal approval, must be implemented as far as possible and described precisely as external configuration—not disguised as completed functionality and not used as an excuse to abandon the rest of the assignment.

## 7. Required tools and research

Use free or open-source tools only. Check what the environment and repository already provide before installing anything. At minimum, use the appropriate combination of:

- Git and GitHub integration;
- PHP CLI and `php -l`;
- Node.js and `node --check`;
- Chromium/Playwright or the approved browser tooling;
- `curl`, `grep`, `find`, `jq`, `rsync`, and repository search;
- HTML/CSS validation where available;
- accessibility checks, including keyboard/focus and an axe-compatible check where available;
- browser console and network inspection;
- responsive viewport checks;
- PHP security checks for authorization, CSRF, validation, escaping, path traversal, uploads, sessions, and rate limiting.

Research is mandatory when a problem is unfamiliar. Use official documentation, OWASP, MDN, official Google OAuth documentation, GitHub projects with established implementations, accessibility standards, and reputable web communities. Research must improve the implementation, not merely appear as a list in the report. Record the key sources and the decisions they informed.

Do not add a paid dependency, service, stock-image subscription, or proprietary runtime. Do not add another backend language merely for perceived security. The current HTML/CSS/JavaScript/PHP stack is sufficient when implemented with secure architecture and verified controls.

## 8. Account-specific instructions

### WEB-01 — Unified Status Dashboard

Read the Unified Status Dashboard tracker and every matching completion evidence file. Verify and repair every documented task, route, status lookup, form, numerical/status behavior, asset path, authentication boundary, error state, responsive layout, RTL behavior, and accessibility behavior. Check that every endpoint referenced by the dashboard exists and that every file claimed by the documentation is actually reachable. Keep the work within the status-dashboard scope and report cross-project dependencies.

### WEB-02 — Admin Dashboard baseline QA

Read `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, all Admin Project assignment documents, and matching admin evidence. Verify the existing dashboard before redesign: authentication, authorization, pets, applications, reviews, statistics, uploads, profiles, chat, sessions, tokens, and every endpoint referenced by the UI. Check sensitive-data boundaries and protected files. Produce a stable baseline for the later admin/rehoming redesign.

### WEB-03 — Public Adoption Website QA

Read `adoption-website-project-summary.md`, the Web Projects trackers, and matching evidence. Crawl every public adoption route. Verify links, redirects, relative paths, scripts, stylesheets, images, JSON, forms, AJAX calls, chat-widget loading, 404 behavior, mobile layouts, RTL, accessibility, console errors, and network errors. Confirm that new files claimed in evidence are present under one authoritative path and are actually reachable. Do not redesign during this baseline audit.

### WEB-04 — Rehoming authentication gateway and backend

This is a mandatory first-priority task within the rehoming project. Before full rehoming acceptance testing, users must have a real gateway rather than the current `Coming soon` destination.

First inventory all rehoming pages, endpoints, data dependencies, uploads, redirects, public routes, authenticated routes, merchant routes, and admin routes. Then implement and test the gateway described in Section 9 of this brief. Coordinate with WEB-05 on route names and with WEB-02 where admin authentication or shared helpers are involved. Do not use a hidden field, query parameter, or client-side value as proof of role.

### WEB-05 — Rehoming functional QA and integration

Treat Rehoming as the largest and highest-risk project. Read all Rehoming Platform assignments and matching evidence. Build a complete route/dependency/file map. Verify visitor, merchant, listing, posting, profile, upload, moderation, and cross-project flows. Check that every new file is reachable and that paths agree across HTML, JavaScript, PHP, JSON, redirects, and assets. Start by coordinating with WEB-04 on the gateway contract. If the project is too large for one account, subdivide it into concrete substreams immediately and notify the coordinator; do not stop at a vague “too large” report.

## 9. WEB-04 rehoming authentication gateway requirements

### 9.1 Gateway scope

Replace the current rehoming entry behavior where the existing Sign Up link leads to a “Coming soon” notice. Use the repository's established naming conventions, but document one authoritative route set. A recommended structure is:

```text
/rehoming/auth/
/rehoming/auth/login.php
/rehoming/auth/signup.php
/rehoming/auth/logout.php
/rehoming/auth/callback.php
/rehoming/privacy-policy/
/rehoming/terms-of-service/
```

The final route may differ if the source structure requires it, but all links, redirects, tests, and reports must use the same final route.

### 9.2 Identity and roles

Support the roles required by the platform, at minimum:

```text
visitor
merchant
admin
```

Roles must be assigned server-side. A user must not gain a role by editing a hidden input, cookie, URL, or request body. Every protected action must verify authentication, role, resource ownership, and authorization on the server.

### 9.3 Login and signup interface

Implement an accessible gateway with:

- login and account-creation entry points;
- Google sign-in button;
- future Facebook option only as a clearly disabled or future placeholder if needed by the supplied design;
- labels, keyboard support, focus states, loading state, and accessible error messages;
- links to Privacy Policy and Terms of Service;
- safe post-login return handling;
- explicit not-configured messaging when Google credentials are absent;
- no fake successful OAuth result.

If local test authentication is implemented for staging, it must be unmistakably test-only and must not be confused with production authentication.

### 9.4 Google OAuth structure

Implement server-side OAuth with:

- cryptographically random `state`;
- nonce where applicable;
- session-bound state storage;
- strict callback validation;
- authorization-code exchange on the server;
- ID-token signature and claim verification;
- issuer, audience, expiry, and email-verification checks;
- exact redirect-URI validation;
- safe error handling;
- no client secret in HTML or JavaScript.

Use environment or hosting configuration for:

```text
GOOGLE_CLIENT_ID
GOOGLE_CLIENT_SECRET
GOOGLE_REDIRECT_URI
APP_BASE_URL
SESSION_SECRET
```

Until credentials and Google Console configuration are supplied, the staging behavior must say that Google sign-in is not configured for the environment. Test the not-configured path and the callback validation with a safe local/mock method where possible.

### 9.5 Session security

Implement secure sessions with:

- server-side session state where practical;
- session-ID regeneration after authentication;
- logout invalidation;
- idle and absolute timeouts;
- `HttpOnly` cookies;
- `Secure` cookies on HTTPS;
- appropriate `SameSite` handling;
- no sensitive identity data in client-controlled local storage.

Test both the local staging behavior and the eventual HTTPS Hostinger behavior. For embedded cross-site HTTPS preview, verify cookie behavior rather than assuming local HTTP behavior is equivalent.

### 9.6 Protected routes and CSRF

Use shared server-side helpers such as the equivalent of:

```php
requireAuthenticatedUser();
requireRole('merchant');
requireRole('admin');
requireResourceOwner($ownerId);
```

Protect every state-changing action with CSRF protection, including listings, applications, messages, account changes, uploads, merchant actions, and any state-changing logout implementation. Validate tokens server-side using timing-safe comparison.

Unauthenticated requests should be redirected to the gateway with a validated return path. Prevent open redirects.

### 9.7 Legal-page routes

Create public Privacy Policy and Terms of Service routes, link them from the gateway and footer, and describe Google sign-in and account-data handling accurately. Do not invent legal claims or declare compliance that has not been reviewed. The user must review final legal wording before production publication, but the technical routes and links must be implemented and tested now.

### 9.8 Gateway tests

Test direct unauthenticated access, valid and invalid login, logout, expired sessions, session fixation, CSRF rejection, invalid OAuth state, invalid callback, expired code, wrong audience, unverified email, missing configuration, unsafe return URLs, role escalation, visitor/merchant/admin boundaries, mobile layout, keyboard navigation, and screen-reader labeling.

## 10. Final web-account deliverables

Each account must create or update its assigned report after implementation, not before. The report must include:

- account ID and assignment;
- branch and starting/ending commits;
- every mandatory document read;
- evidence files available and missing;
- task/completion/status matrix;
- files inspected, added, modified, deleted, or moved;
- route and path map;
- bugs fixed and enhancements implemented;
- commands and browser tests with viewport details;
- research sources and decisions;
- security/accessibility/RTL results;
- commit hashes;
- cross-project dependencies;
- exact external configuration requirements;
- confirmation that no secrets or protected runtime data were committed;
- confirmation that no conflict markers remain;
- confirmation that all independently verifiable in-scope work was completed.

`TODO.md` is the shared inbox. Read it at the start and end of the session. Follow its repository rules: do not edit it on a task branch unless the current protocol explicitly authorizes the entry workflow; never put secrets there; add only completed, actionable, clearly addressed information using the prescribed format. Do not use `TODO.md` as a plan dump.

## 11. Short launcher to send with this file

Send only this short message to an account after giving it repository access:

> You are `[ACCOUNT ID]`. Clone the repository, read `docs/second-round/WEB-ACCOUNTS-OPERATIONAL-BRIEF.md` completely, then follow only the section for your assigned account and branch. Do not edit code before completing the mandatory reading gate. Treat completion documents as historical evidence, keep secrets out of the repository, synchronize before commits, implement and test all in-scope work, push only your assigned branch, and report completed work with evidence.
