# WEB-03 — Public Adoption Website QA

**Date:** 2026-11-08
**Account / assignment:** WEB-03 — Public Adoption Website baseline QA
**Branch:** `qa/adoption-website`
**Starting and tested-source commit:** `42228b6` (`origin/main` at audit start); application source remained unchanged. The report commit is the ending branch tip and is recorded in Git metadata / the final handoff.

## Task Number

WEB-03.

## What Was Required

Crawl the public adoption routes and verify route/link behavior, assets, scripts, forms and AJAX boundaries, chat-widget loading, 404 handling, responsive layout, RTL, accessibility, console and network behavior, and claimed-file reachability. Do not redesign the website. Preserve backend/authentication contracts and do not access protected runtime data or deploy to Hostinger.

## What I Built

Completed a read-only QA baseline using a sanitized local static preview. The preview included public HTML/assets only; PHP, protected data, external business endpoints, and non-allowlisted remote requests were blocked or stubbed. No application code, backend contract, route, or `TODO.md` content was changed. I did not merge or modify the separate `origin/redesign/public-adoption` branch.

### Mandatory reading and historical evidence

Read the current-wave launcher and complete WEB operational brief; `adoption-website-project-summary.md`; `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`; the public-web-relevant sections of `PETCENTER_DESIGN_SYSTEM_v1.0.md`; `INTEGRATION-PROGRESS.md`; `TODO.md`; `ROADBLOCKS-AND-FIXES.md`; `WORKER-GIT-SYNC-PROTOCOL.md`; `VDM-GIT-SYNC-COORDINATION-PROTOCOL.md`; `INTEGRATOR-GIT-SYNC-MERGE-PROTOCOL.md`; `docs/second-round/CORE-DOCUMENT-READING-GATE.md`; `SECOND-ROUND-MANUS-COORDINATION.md`; `WORKER-PACKAGE-INVENTORY.md`; `SECOND-ROUND-ISSUE-REGISTER.md`; both Web Projects assignment trackers; and `HOSTINGER-PREDEPLOY-AND-ROLLBACK-CHECKLIST.md`.

Reviewed the completion-evidence manifest and README plus all 13 matching public-web handoffs: Project-5 Tasks 5, 5b, 6a, 6b, 7a and 9; Web Projects 1–2 Tasks 2 and 10 plus Web-project-1-2 Task 9; Web Projects 3–4–5 Tasks 2, 3 and 4/4b; and Pet Center adoption-system Task 8. Also reviewed the current report index/README, `task-web345-7b`, relevant MANUS-01/02/03/03-integration-review/04/08/09/10/11 reports, `task-3`, `task-3b`, and integration build checks dated 2026-11-04 and 2026-11-06. No required matching public-web evidence file was missing from the checkout.

### Verification matrix

| Area | Result |
|---|---|
| Public route crawl | 56 HTML routes, desktop 1440×900 and mobile 390×844; all preview navigations returned 200; no horizontal overflow. |
| Link/asset crawl | 2,334 same-origin references and 90 unique local paths examined. No missing local image/style/script asset was found; 8 distinct local route targets returned 404 in the preview. `adopt/admin/chat-widget.js` returned 200. |
| Unknown-route behavior | A deterministic unknown path returned 404 in the local static preview. Production/Hostinger rewrite behavior was not tested. |
| JavaScript syntax | `node --check`: 94 inline scripts, one failure (`adopt/pets/quiz/index.html`, `checkEnergy` SVG string around lines 723–736); 30 standalone public JS files passed. |
| PHP syntax | `php -l`: 21 non-protected public PHP handlers passed. No PHP endpoint was executed. Configuration, upload/runtime, admin and protected data paths were excluded. |
| Accessibility | axe-core 4.14.0: serious color-contrast 419 nodes/51 routes; critical missing-label 45/2; label-content-name-mismatch 4/1; link-in-text-block 3/2; list semantics 2/1; meta-viewport 2/2; target-size 18/6. These are static-preview findings, not a production/authenticated-user audit. |
| Forms / APIs | Four client-only forms were exercised with synthetic local values; all prevented submission, sent zero POSTs, showed success, and cleared their fields. Backend submissions were not attempted. Protected pet/runtime requests were blocked; one PHP configuration endpoint was stubbed with 503, so its runtime response remains unverified. |
| RTL | Through the accessibility panel, Arabic could be selected, but the page remained `lang="en"` with no `dir` on `html` or `body`. Only static fonts/CDN requests were allowed; no external translator request was made. |
| Network / widget | Browser requests to protected data and non-allowlisted hosts were blocked; no protected payload was read. The chat-widget script path was reachable, but chat/API submission was not tested. |

### Confirmed findings and evidence

- The quiz inline script fails parsing with `Invalid or unexpected token`; the browser route reports `Invalid or unexpected token` on `/pets/quiz/`. The fetched `origin/redesign/public-adoption` version of this file passes `node --check`, but is not integrated into this branch.
- Both `adopt/contact-us/index.html:318` and `adopt/contact-us/contact-us.html:315` submit the department value `health`; `adopt/contact-us/contact-handler.php:65` accepts `health-claim` instead. No real form POST was made. The fetched redesign branch still contains the mismatch in both forms.
- A full route matrix reports `Quill is not defined` on `/apply/`; three focused page loads then loaded Quill from jsDelivr with HTTP 200 and no page errors. Classify as intermittent/unresolved; retest after integration rather than treating it as a stable pass.
- Eight local link targets returned 404 in the sanitized preview: `/rehome/` (53 anchor references across 18 routes), `/adoption-application.html` (4), `/birds.html` (8), `/blog-home.html` (8), `/cats.html` (8), `/dogs.html` (12), `/reptiles.html` (8), and `/small-mammals.html` (8). The project summary describes `/rehome/` as retired, while the current WEB brief names `/signin/` and `/rehoming/` as current entries; coordinator/WEB-04/05 should confirm the intended destination before mass link edits. Two `/cdn-cgi/l/email-protection` links were observed separately; this is a Cloudflare-generated path, not a repository page. No public-root `.htaccess` rewrite was found; production-specific routing remains unverified.
- `/about.html` and `/contact.html` both redirected to `/` in the preview; this may be legacy behavior and was not tested against production routing.
- The local-only `/foster/`, `/partners/`, `/volunteer/`, and `/rehoming-resources/` forms display success and clear entered values without sending data. Do not represent these flows as backend-confirmed submissions.
- Arabic selection did not produce an RTL document state. The axe findings above also need owner review; fixing them entails site-wide/shared styling or component changes and is not a safe baseline-only edit.

### Cross-branch coordination

`origin/redesign/public-adoption` (`6757292`) changes the same quiz and both contact pages, as well as broad public-site files. To avoid conflicting edits, this QA branch leaves application files untouched. The redesign snapshot fixes the quiz syntax, but its two contact forms still use the rejected `health` value. The integrator should reconcile and rerun the quiz/contact tests after merging.

## Files Changed

| File | Shared with another task? |
|---|---|
| `reports/second-round/WEB-03.md` | New WEB-03 QA handoff; no application source changed. |

## Small Out-of-Scope Fixes Made

None.

## Bugs and Follow-ups Sent to TODO.md

None. Findings and cross-branch handoff are recorded here; the operational brief restricts task-branch edits to the shared TODO inbox unless its prescribed entry workflow applies.

## Things the Next Person Must Know

Before any release, **remind the coordinator to create and verify both a repository backup and a Hostinger backup, prepare a rollback path, and obtain the user's explicit approval for the exact release commit and target**. This report does not authorize deployment. No Hostinger deployment, upload, deploy-branch push, or automatic deployment was triggered. No `.github/workflows` files are tracked in this checkout; this worker used only the assigned QA branch. Runtime PHP/configuration, protected records, and production Hostinger routing remain unverified by design.

## Self-Check Confirmation

Only this report is being added; the assigned branch is `qa/adoption-website`, never `main`. The branch was fetched and fast-forward-checked against `origin/main` (`42228b6`) before report creation. Local static/browser checks, JS syntax checks, PHP linting, mobile viewports, accessibility scans, links, forms, and RTL were completed within the protected-data boundary described above. No secrets or protected runtime data were read into or committed in the report; no conflict markers were introduced. The report records the active redesign-branch overlap rather than silently merging it.
