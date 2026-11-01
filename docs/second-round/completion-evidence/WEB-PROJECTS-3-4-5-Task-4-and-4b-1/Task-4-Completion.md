# Task 4 Completion (covers Task 4 and Task 4b — same new file, same worker)

## Task Number
- **TASK 4 — Project 4: Worldwide Shipping Calculator Page** (zone `CALCULATOR-CORE`)
- **TASK 4b — Project 4: Destination Compliance Flag Lookup** (zone `COMPLIANCE-FLAG`, depends on Task 4)

Both come from `WEB-PROJECTS-3-4-5-TASK-ASSIGNMENTS.md`. I did both, in the one new file, so there is one completion file and one zip.

## What Was Required
Task 4: a new page giving credible worldwide pet-shipping cost estimates from Pet Center's Los Angeles origin, matching the CEO's track.global reference screenshots (grey/disabled form with a loading bar while calculating, then a soft fade-in of results), pet-only categories, same-day and round-trip options, results shown as ranges with a "final quote requires contacting us" disclaimer, LAX credibility copy, and Pet Center's real shipping policy content.
Task 4b: a compliance-flag layer on top of that page — species-level flags (birds, reptiles, fish) plus country-specific flags for Canada, UK and Australia, with a general fallback for every other destination.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `shipping-calculator.html` | `shipping/index.html` | **New** | Yes — Task 4 (calculator core) and Task 4b (compliance zone) both live in it; both done by me in this hand-in. No other task touches it. |

No existing file was changed. No existing file's code is pasted below.

## Code Changes
None — no existing file was changed. (Task 4b's compliance code sits inside the new file, between `<!-- TASK-4B ZONE START -->` and `<!-- TASK-4B ZONE END -->`, and is delivered whole in the zip.)

## New files

- **`shipping/index.html`** — New file — in the zip. The worldwide pet-shipping calculator page (Task 4 + Task 4b).

### ⚠️ NOTE FOR THE INTEGRATOR (read this)
- **NEW FOLDER TO CREATE: `shipping/`, directly under the site root** (next to `pets/`, `blog/`, `services/`, `signin/`). Live URL becomes `adopt.petcenterco.com/shipping/`.
- **This folder and path are not in `adoption-website-project-summary.md` (Section 1) or `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`.** The task assignment does not give a path either, so I followed the delivery guide's own example ("a page called Shipping goes at `shipping/index.html`"). If the CEO prefers another path (e.g. `shipping/calculator/index.html`), only the folder moves — every link in the page is absolute (`/pets/...`, `/assets/...`), so nothing inside the file needs editing.
- **Live page naming is `index.html`.** The source file was `shipping-calculator.html`; it must be deployed as `shipping/index.html`, never as a loose `.html` filename.
- **`Task-4-Completion.md` is inside the zip at the zip root at the CEO's request, so everything travels together. It is a hand-in document, not a site file — do not copy it into the live site root.**
- The Section 1 folder structure in `adoption-website-project-summary.md` should get a `shipping/` entry once this is merged (I did not edit that document).
- Zero secrets needed. No `config.php`, `.env`, keys or tokens are used or included.
- The page expects `/assets/images/logo-petcenter.png` and `/assets/images/logo-petcenter-icon.png` (the canonical header/footer logo paths from Section 2.6). They exist on the live site; they only show as broken images when the file is opened standalone.

## Bugs Found Outside Task Scope
None found in other files. Pending items and doubts that still sit around this work:

1. **No page links to the calculator yet.** Task 4 is "one new page" with no nav-wiring step, so I did not touch any header or footer. Until some page links to `/shipping/` (global header/footer nav, or a CTA on the pet pages), visitors cannot discover it. Needs a decision and an owner.
2. **`cats.html` was never supplied.** Rule 13 names it as the ground truth for header/footer/hamburger. I used the Section 2.6 markup instead and diffed every link against it (all match). If `cats.html` has drifted from Section 2.6, the integrator should diff the header/footer block of `shipping/index.html` against it.
3. **Playwright visual diff against the CEO's reference screenshots was NOT run** (Rule 10a). The environment had no network to install it. I checked the UI by tracing the code against the screenshots and by static checks only. A real visual pass in a browser is still owed before this is called design-verified.
4. **Pricing is an estimate model, not live carrier rates.** Starting prices ($120 / $220 / $450) come from Pet Center's own shipping policy; everything above them (distance zones, weight, international tiers, regional surcharges) is a formula built from web research, then lowered per the CEO's request. The CEO should confirm the numbers feel right commercially.
5. **Currency conversion uses fixed approximate rates** (CAD 1.38, GBP 0.75, AUD 1.53), labelled approximate. They will go stale.
6. **Compliance flags are dated "September 2026"** and come from third-party summaries of government rules. They should be re-checked periodically and a licensed shipper should confirm before anyone relies on them. Countries other than Canada, UK and Australia get the general "contact us" fallback only.
7. **Postal-code check is coarse.** US uses the first ZIP digit as a zone; CA/GB/AU use format validation plus a remote-area surcharge by postal prefix. Countries without a known format are not validated. This is not address-level pricing.
8. **50% deposit** is shown on live-animal results as taken from the cancellation policy. Suggest the payment flow shows the cancellation table and receipts at checkout (advice only, no code involved).
9. Task 4b's zone anchors are written `TASK-4B ZONE START/END`; the rest of the file (all Task 4) is not wrapped in per-block anchors because it is a whole new file.

## Self-Check Confirmation
- I created only `shipping/index.html` (source name `shipping-calculator.html`) and this completion file. No existing file was touched, so there are no old/new snippets to copy.
- Every file in the zip is listed above with the same path: `shipping/index.html` (New file), plus this completion file at the zip root as the CEO requested.
- No secrets, `config.php`, `pet.json`, `.env`, `applications/`, `uploads/`, `chat-media/` or `logs/` content is in the zip (scanned the page for keys, tokens and passwords: none).
- How I tested: JavaScript syntax check with `node --check`; HTML div balance; duplicate-CSS-selector scan (two stale duplicates found and fixed earlier); every `getElementById` cross-checked against ids in the HTML (none missing); header/footer links diffed against Section 2.6; pricing, postal validation and compliance logic hand-traced with real inputs. Not done: Playwright/browser rendering (see item 3 above).
- Scope: nothing outside Tasks 4 and 4b was touched.
