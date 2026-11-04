# MANUS-10 — Browser and Integration QA Report

## Assignment and repository state

- **Account:** MANUS-10
- **Package:** Browser and integration QA: Playwright, desktop/mobile behavior, console errors, network failures, routes, forms, and visual checks
- **Starting commit:** `251d8c2` (`main`)
- **Branch:** `task/second-round-MANUS-10`
- **QA date:** 2026-11-04
- **Application state tested:** current `main` checkout, served locally from `adopt/` with PHP's built-in server at `127.0.0.1:8000`

The required core-document gate was completed before source inspection. The named `docs/second-round/completion-evidence/` directory and MANUS-10-specific completion handoff are not present on this branch; this is recorded as an evidence gap rather than inferred away. Available worker branches and reports were reviewed before testing. They are unmerged evidence, not acceptance of their changes.

Protected paths were not opened, copied, modified, or staged: `.env`, `config.php`, `pet.json`, `applications/`, `uploads/`, `chat-media/`, and `logs/`.

## Documents read

The ordered project packet and coordination documents read were: `INTEGRATION-PROGRESS.md`, `TODO.md`, `docs/second-round/README.md`, `docs/second-round/SECOND-ROUND-REQUEST.md`, `docs/second-round/SECOND-ROUND-SETUP-AND-SKILLS.md`, `docs/second-round/SECOND-ROUND-MANUS-COORDINATION.md`, `docs/second-round/WORKER-PACKAGE-INVENTORY.md`, `docs/second-round/SECOND-ROUND-ISSUE-REGISTER.md`, `docs/second-round/ACCOUNT-WIDE-CORRECTION.md`, `reports/second-round/README.md`, `reports/second-round/ACCOUNT-REPORT-INDEX.md`, and the MANUS-10 section of `docs/second-round/MANUS-ACCOUNT-PROMPTS.md`.

Relevant core and project references read included `docs/second-round/core-docs/TODO-5.md`, `docs/second-round/core-docs/WORKER-GIT-SYNC-PROTOCOL-7.md`, `docs/second-round/core-docs/VDM-GIT-SYNC-COORDINATION-PROTOCOL-7.md`, `docs/second-round/core-docs/Web-Code-Integrator-5.md`, `adoption-website-project-summary.md`, `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, and `PETCENTER_DESIGN_SYSTEM_v1.0.md`.

The available MANUS-01, MANUS-02, MANUS-03, MANUS-04, MANUS-05, MANUS-06, MANUS-07, MANUS-09, and MANUS-11 reports were reviewed from fetched worker branches where available. MANUS-08 and a MANUS-10 completion package/report were not available before this report.

## QA method and results

### Static checks

- **HTML:** 40 current non-legacy/non-vendor HTML files scanned with BeautifulSoup; zero files had an invalid document/body count.
- **JavaScript:** 26 non-legacy/non-vendor JavaScript files checked with `node --check`; **0 failures**.
- **PHP:** 63 non-protected PHP files checked with `php -l`; **0 failures**.
- **Duplicate IDs:** no duplicate-ID defect was observed in the inspected pages.

### Browser route sweep

A Playwright smoke script using the installed system Chromium (`/usr/bin/chromium`) visited 33 canonical public/admin routes at both **1440×900 desktop** and **390×844 mobile** sizes: **66 route/viewport checks** total.

- All requested routes returned HTTP 200 in the local preview.
- No uncaught `pageerror` exceptions occurred.
- The shared mobile hamburger on `/` worked: `aria-expanded` changed `false → true → false`, and the mobile menu became visible while open.
- The adoption form on `/apply/` rendered with one form and 26 required controls; native browser validity correctly blocked an empty submission.
- One horizontal-overflow failure was reproduced: `/apply/` at 390px had `scrollWidth=398` vs `clientWidth=390`. The overflowing element was `#step-ind-5` (`.step-indicator__item`), whose right edge was `397.625px`.

### Console and network findings

The sweep found no JavaScript runtime exceptions, but it did find reproducible missing-resource failures:

1. **Missing language scripts — Needs My Review / integration dependency.** Browser requests for `/assets/js/lang-redirect.js` and `/assets/js/i18n-core.js` returned 404 during the route sweep. This prevents the language/translation layer from loading on affected pages. The issue-register and worker reports already identify the translation rollout as incomplete; no replacement implementation was fabricated in this QA-only package.
2. **Missing category data — Blocked by data/runtime dependency.** `/pets/cats/`, `/pets/dogs/`, and `/pets/quiz/` attempted to fetch category `index.json` data and logged HTTP 404/error states. This is consistent with the documented missing category indexes/data-owner dependency; protected pet records were not opened or recreated.
3. **Malformed chat-widget URL — Needs My Review; already documented outside the owning page task.** The browser requested relative paths such as `/testimonials/adopt.petcenterco.com/admin/chat-widget.js` and `/testimonials3/adopt.petcenterco.com/admin/chat-widget.js`, receiving 404s. Source locations include `adopt/testimonials/index.html:494`, `adopt/testimonials3/index.html:763`, `adopt/testimonials3/index8.html:750`, and the legacy `adopt/testimonials/inde8x.html:489`. This reproduces the issue-register entry previously handed to the rehoming/page owner; no shared-page path was changed here.
4. **Other 404 console messages** were observed for page-local assets on some routes. Because the local preview has no production CDN/server rewrite context, these were retained in the raw smoke output and not all promoted as independent defects without a route-specific reproduction.

## Issue/status matrix

| Area | Result | Classification | Evidence / handoff |
|---|---|---|---|
| Desktop/mobile canonical route availability | All 66 local route checks returned 200 | **Fixed / verified locally** | PHP preview only; production rewrite/deployment behavior remains unverified |
| Shared hamburger open/close | Passed at 390×844 | **Fixed / verified** | `aria-expanded` and visibility transitions behaved correctly |
| Adoption form required validation | Passed basic empty-form validity check | **Fixed / verified locally** | No real submission was attempted; backend/config remains out of scope |
| `/apply/` mobile horizontal overflow | Reproduced at 390px | **Needs My Review** | `#step-ind-5` extends 7.625px beyond viewport; hand to application-page/shared responsive owner |
| Language script loading | `/assets/js/lang-redirect.js` and `/assets/js/i18n-core.js` 404 | **Needs My Review / blocked by translation integration** | Requires approved language asset rollout and shared-page ownership decision |
| Category data loading | Cats/dogs/quiz index requests 404 | **Blocked** | Requires data-owner/runtime index restoration; protected `pet.json` content was not inspected |
| Testimonials chat-widget path | Relative malformed path 404s | **Needs My Review** | Reproduced and previously registered; page owner/integrator should change to the approved root-relative script path |
| Settings/accessibility widget on current `main` | `#pcSettingsWidget` absent on representative homepage, category, admin, and apply pages | **Needs My Review / integration gap** | Worker report states widget rollout is on an unmerged branch; no unowned shared implementation was applied here |
| Production deployment, external CDN, PHP backend, auth, email, uploads, and protected data | Not safely testable in this local checkout | **Blocked** | Requires authorized deployed environment and protected runtime configuration |

## Files inspected

Representative and route-sweep sources included `adopt/index.html`, `adopt/about.html`, `adopt/account/settings/index.html`, `adopt/adoptions/status/index.html`, `adopt/apply/index.html`, `adopt/blog/index.html`, `adopt/blog/post/index.html`, `adopt/contact-us/index.html`, `adopt/contact.html`, `adopt/merchant/index.html`, `adopt/merchants/index.html`, `adopt/pet-match/index.html`, `adopt/pets/index.html`, `adopt/pets/cats/index.html`, `adopt/pets/dogs/index.html`, `adopt/pets/detail/index.html`, `adopt/pets/quiz/index.html`, `adopt/services/index.html`, `adopt/signin/index.html`, `adopt/testimonials/index.html`, `adopt/testimonials3/index.html`, `adopt/verify/index.html`, `adopt/admin/index.html`, and the integrated public pages under `shipping/`, `events/`, `foster/`, `partners/`, `lost-and-found/`, `rehoming-resources/`, `species-guide/`, `sponsor-a-pet/`, `success-stories/`, and `volunteer/` where present in the tested checkout. Shared CSS/JS and PHP source were included in the static syntax checks.

## Exact changes and anchors

No application source was changed. There are therefore no `BUGFIX-MANUS-10-*` anchors. The only intended change on this branch is this QA report.

Temporary Playwright results and screenshots were kept outside the repository under `/home/ubuntu/qa-tools/` and `/tmp/`; no applicant or protected data was included in the report.

## Unresolved blockers and handoffs

1. Route the 390px `/apply/` step-indicator overflow to the application-page/shared responsive owner and re-run the mobile sweep after correction.
2. Restore or deliberately integrate the missing language scripts and translation rollout; do not silently add placeholder translations.
3. Restore category index data through the authorized data owner; do not bypass the protected pet-record boundary.
4. Correct the four malformed testimonials chat-widget script references through the owning shared-page/integrator path.
5. Review the unmerged MANUS-03 accessibility/RTL/widget work before treating the current `main` checkout as widget-complete.
6. Repeat network/backend/authenticated checks against the authorized deployment; the local PHP preview cannot establish production routing, session, mail, upload, or external-service behavior.

## Git delivery

- **Branch:** `task/second-round-MANUS-10`
- **Report path:** `reports/second-round/MANUS-10.md`
- **Application code changes:** none
- **Commit:** final branch tip is supplied in the coordinator handoff; the report was amended after metadata validation
