# DESIGN-01 — Public Adoption Website Redesign

- **Status:** Implementation complete; the final local Chromium/axe audit passed within the documented preview limits.
- **Assignment:** Public Adoption Website redesign.
- **Branch:** `redesign/public-adoption`.
- **Synchronized base:** `0786a3fab7bb60012b01549614dbfee5bdbeda4b` (`origin/main`); branch sync commit `7d0ee0a`.
- **Implementation commit:** `b21300d` (`feat(adoption): add shared public redesign system`).
- **Hostinger:** No deployment, Hostinger upload, release, or change to `origin/adopt-deploy` was made.

## Summary

Added a shared public CSS design layer and keyboard-navigation enhancer across 40 active public adoption HTML pages. Routes, backend endpoints, form submission names/flow, authentication/authorization boundaries, and page-owned application logic remain intact. Most changes are shared CSS/JS hooks and page scope classes; documented exceptions include adding the common public shell to two contact pages that lacked it, narrowly correcting existing accessible names/semantics, and aligning one contact-department value with the existing handler whitelist. Two legacy redirect wrappers (`adopt/about.html` and `adopt/contact.html`) were left unchanged because they do not render a local public page.

The design adopts the user's Morgan Mallet reference [1] at the level of visual language: editorial serif headlines, generous spacing, image-led heroes with deep navy overlays, and restrained gold details. PetCenter's own logo and repository-owned imagery are retained; no proprietary reference assets, third-party photos, or paid dependencies were copied or introduced. Cormorant Garamond and Sora establish the editorial/type contrast; PetCenter orange remains a limited secondary/action anchor within the navy, gold, and warm-neutral system. Shared styles cover tokens, headers/navigation, footers, buttons, links, cards, forms, focus states, dialogs/sheets, feedback/loading/empty states, responsive behavior, and reduced motion.

The shared navigation script adds Escape-to-close for the existing mobile menu, returns keyboard focus to the existing trigger, resets its inline display override, and synchronizes its existing active/`aria-expanded` state. The current page handlers remain responsible for menu opening and existing scroll-lock behavior.

## Baseline, migration, and scope

Before source work, I read the complete DESIGN-01 operational brief and current-wave launcher, both mandatory structural summaries, the core reading gate, and the documents enumerated in the brief: the PetCenter design system, integration progress, TODO, roadblocks/fixes, worker/VDM/integrator Git protocols, coordination, worker inventory, issue register, and Hostinger checklist. I also read the relevant assignment/completion evidence and available historical public-web reports (`MANUS-03`, `MANUS-04`, `MANUS-08`, `MANUS-09`, `MANUS-10`, the MANUS-03 integration review, the historical report index/README, and `reports/task-web345-7b.md`). I followed only the DESIGN-01 section and did not read or use the web-project brief.

The supplied repository had no reference HTML or licensed replacement imagery for this redesign. The current report index says it is historical; the synchronized remote refs contained no current `WEB-01`–`WEB-05` branch, and the available report set did not include their current-wave baseline reports. I used the available historical public reports and source inventory, recorded this evidence gap, and continued independently verifiable redesign work as instructed.

The shared styles/scripts load from the site's existing `/assets/...` convention and do not replace page-owned logic. Existing route destinations, endpoint URLs, form names and data-flow remain unchanged. HTML changes beyond the shared hooks are limited to the explicit contact-shell, form-value/accessibility, quiz-syntax, and species-guide fixes described below; no backend handler, authentication, API, database, or deployment configuration was changed. Existing task-numbered comment anchors were retained, with new DESIGN-01 comment anchors around the isolated repairs.

## Files changed

Added:

- `adopt/assets/css/public-redesign.css` — documented shared design tokens, component presentation, keyboard focus, accessible states, responsive rules, reduced-motion rules, and public page refinements.
- `adopt/assets/js/public-chrome.js` — Escape close and focus restoration for the existing shared mobile menu.

Added the shared CSS and JS hooks plus additive body scope classes to these 40 public pages:

- `adopt/index.html`, `adopt/about/index.html`, `adopt/adoptions/status/index.html`, `adopt/apply/index.html`.
- `adopt/blog/index.html`, `adopt/blog/post/index.html`, `adopt/contact-us/index.html`, `adopt/contact-us/contact-us.html`, `adopt/events/index.html`, `adopt/foster/index.html`, `adopt/gift-my-adoption/index.html`, `adopt/lost-and-found/index.html`, `adopt/original-adoption-application.html`, `adopt/partners/index.html`, `adopt/pet-match/index.html`.
- `adopt/pets/index.html`, `adopt/pets/detail/index.html`, `adopt/pets/quiz/index.html`; aquariums (`adopt/pets/aquariums/index.html`); birds (`adopt/pets/birds/index.html`, `adopt/pets/birds/Birds.html`); cats (`adopt/pets/cats/index.html`, `adopt/pets/cats/Cats.html`); dogs (`adopt/pets/dogs/index.html`, `adopt/pets/dogs/Dogs.html`); livestock (`adopt/pets/livestocks/index.html`); reptiles (`adopt/pets/reptiles/index.html`, `adopt/pets/reptiles/Reptiles.html`); small mammals (`adopt/pets/small-mammals/index.html`, `adopt/pets/small-mammals/Small-mammals.html`).
- `adopt/rehoming-resources/index.html`, `adopt/services/index.html`, `adopt/shipping/index.html`, `adopt/species-guide/index.html`, `adopt/sponsor-a-pet/index.html`, `adopt/success-stories/index.html`, `adopt/testimonials/index.html`, `adopt/testimonials3/index.html`, `adopt/verify/index.html`, and `adopt/volunteer/index.html`.

No backend, API, database, auth, admin, merchant, upload, deployment, configuration, or credential file was changed. The two legacy redirect wrappers above and out-of-scope protected/legacy directories were not migrated.

## Verification evidence

Testing was performed against a localhost-only static preview. The Playwright request handler blocked third-party hosts, PHP endpoints, protected pet/application record paths, and upload paths; no form was submitted and no private/user record was retrieved.

- Chromium/axe route sweep: **40 pages × 3 viewports (1440×900, 768×1024, and 390×844) = 120 checks**. All 120 local page responses were 200; there were no failed routes, missing shared hooks, or horizontal overflows. Axe reported **zero WCAG A/AA violations** across the 120 page/viewport samples.
- RTL + reduced-motion: six representative routes (home, cats, application, application status, blog, testimonials) were switched to RTL at 390 px. All retained `scrollWidth == clientWidth`; the header transition resolves to `0.01ms` under `prefers-reduced-motion: reduce` [2].
- Keyboard: Tab produced `:focus-visible` with a solid 3 px outline [3]. On mobile, the hamburger opened; Escape removed the open state, reset `aria-expanded` to false, and returned focus to the hamburger.
- Touch targets: the shared layer sets 44 px minimum block-size on principal buttons/actions and desktop navigation, 44×44 px on the hamburger, and 48 px on mobile menu links. The final Chromium spot-check measured the mobile hamburger at **44×44 px**, the homepage primary CTA at **53 px tall**, and the secondary sign-in CTA at **53 px tall**.
- Contrast: browser-computed white-on-orange primary and donation CTAs measured **5.316:1** at rest and **7.48:1** on hover.
- Responsive footer: browser-computed grid resolved to five columns at 1440 px, two at 768 px, and one at 390 px.
- Modal/sheet styles: existing verification modal and cat filter sheet resolve the shared border, radius, surface, and elevation tokens without changing their page-owned display/interaction state.
- Quiz interaction: advanced five existing client-side steps to the energy-level question; the `No preference` option rendered its SVG, with no quiz page error and no final submission/API request.
- Syntax/quality: `node --check adopt/assets/js/public-chrome.js`, the quiz inline-JavaScript syntax check, the shared CSS parse check, and `git diff --check` passed.

## Findings and validation limits

**Fixed in-scope baseline defect — public quiz.** The existing `LIVING_ICON_SVG.checkEnergy` single-quoted JavaScript string contained two raw line breaks inside the embedded SVG `<style>` block, so the browser rejected the entire quiz inline script with `SyntaxError: Invalid or unexpected token`. The two line breaks are now escaped as `\n`, preserving the existing SVG output, with no change to quiz state, options, or result/backend flow. Both `node --check` and the browser interaction above now pass.

**Additional in-scope form and accessibility repairs.** The Health Claim department option on both public contact forms now uses the existing `health-claim` handler whitelist value; no handler/backend code or other form value was changed. The two contact pages received the common public header/footer because they had no shared shell. Existing application fields, shipping toggles, and floating contact controls received explicit accessible names without changing their submission names, values, or actions. The species guide comparison table is now a labelled keyboard-scrollable region, its list headings sit outside valid list markup, and the category pages' contact buttons have explicit accessible names.

**Visual review follow-up.** Screenshot and source review exposed dark-on-dark cascade conflicts that automated checks did not report: the homepage sign-in action, pet-directory title, shared animal-category, quiz, blog/article and testimonial banners, and homepage service-carousel title. Component-scoped overrides restore gold/white type on navy surfaces while preserving the carousel's gold subtitle; Chromium spot checks verified the homepage, directory, cat banner, and carousel title colors. The refreshed contact sheet and separate carousel screenshot capture the editorial hero and section treatments at desktop and mobile sizes.

**Existing external dependency — application preview.** The isolated browser run intentionally blocks external HTTPS hosts. Consequently, the existing `apply/` page's Quill CDN script did not load and produced `Quill is not defined` (one page error at each tested viewport). This is an environment limitation caused by the privacy-safe request block, not a shared-redesign regression; the source/CDN dependency was not changed. Verify the normal deployed environment can reach its existing Quill CDN before approving an application-form release.

**Current-wave baseline evidence gap.** No current reports from `WEB-01`–`WEB-05` were available in the synchronized repository/ref set. The historical public-site findings reviewed above are not substitutes for those current-wave reports. Coordinator should reconcile those reports when they become available; no WEB account branch or file was changed.

**Deployment/branch safety.** No GitHub Actions workflow directory was present in the repository at baseline. The remote includes a separate `origin/adopt-deploy` ref, which was not checked out, edited, or pushed. I did not access Hostinger settings, so the coordinator must verify any Git/Hostinger branch watcher configuration before any future release; this report does not assert that Hostinger auto-deploy is disabled.

## Research and design decisions

The PetCenter palette/type and page conventions come from `PETCENTER_DESIGN_SYSTEM_v1.0.md`. The user's reference URL [1] informed high-level choices only—editorial display typography, spacious composition, image-led heroes, navy overlays, and understated gold; no reference code, font files, logo, or photography was copied. Existing repository-owned assets were reused. Layout and spacing use CSS logical properties where relevant to preserve RTL behavior; page-specific CSS continues to own unique behaviors.

Accessibility references consulted include MDN's `prefers-reduced-motion` and `@media` guidance [2] [4] and W3C's WCAG focus-visible guidance [3].

## Release and coordination gate

**Coordinator reminder: before any production publication, create and verify both (1) a repository backup and (2) a Hostinger site/files/database backup, and prepare and verify a rollback path. Follow `docs/second-round/HOSTINGER-PREDEPLOY-AND-ROLLBACK-CHECKLIST.md` and obtain the user's explicit approval for the exact release commit and target.** No production deployment, Hostinger upload, or Hostinger-triggering action was performed by DESIGN-01. The branch commit is for integration review only; confirm it is not configured as a production deploy source before any future release.

- **Secrets/data:** No token, credential, private URL, applicant data, protected runtime data, or generated user record was placed in the repository or report.
- **Cross-project dependency:** Coordinator-owned merge/reconciliation into `integration/second-round-final-2026-11-06`; no shared backend contract or external configuration was added by this branch.
- **Completion:** All independently verifiable DESIGN-01 implementation work was completed and tested; this is a local static/browser verification, not a live backend, authentication, or production deployment certification.

## References

[1]: https://morganmallet.agency/ "Morgan Mallet Agency"
[2]: https://developer.mozilla.org/en-US/docs/Web/CSS/Reference/At-rules/@media/prefers-reduced-motion "MDN prefers-reduced-motion"
[3]: https://www.w3.org/WAI/WCAG22/Understanding/focus-visible.html "WCAG 2.2 Focus Visible"
[4]: https://developer.mozilla.org/en-US/docs/Web/CSS/Reference/At-rules/@media "MDN media queries"
