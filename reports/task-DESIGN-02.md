# DESIGN-02 completion report

## Task Number
DESIGN-02 — account gateway, admin shell, and rehoming surfaces.

## What Was Required
Deliver a modern redesign on `redesign/admin-rehoming` while retaining routes, existing PHP/JS contracts, authentication and authorization boundaries, forms, accessibility, RTL-safe layout behavior, and data flows. Provide a usable login/signup gateway, a coherent admin operations shell, and a consistent rehoming experience. Do not deploy to Hostinger.

## What I Built
The account route now has a two-mode sign-in/create-account gateway with accessible labels, required-field error identification, focus return to the first invalid field, a Google-branded presentation that does not invent an OAuth endpoint, and an explicit not-configured state that never claims a session or sends a redirect.

The shared DESIGN-02 stylesheet now includes a navy/gold/orange component layer for authentication, admin operations, tables, cards, status feedback, dense controls, mobile overflow behavior, and reduced-risk destructive actions. The admin shell uses the existing IDs, navigation triggers, panels, and token login, but presents them with clearer hierarchy, status color, table framing, responsive spacing, and the approved design-system palette rather than the rejected lavender accent.

The rehoming hub and posting form continue to use the existing lifecycle engine, local-store behavior, merchant publish endpoint, and existing IDs. The hub receives stronger card hierarchy, action emphasis, sticky/scrollable tab behavior, and mobile-safe spacing without changing its data model or event bindings. Shared auth behavior is isolated in `adopt/assets/js/design02-accounts.js`.

## Files Changed
- `adopt/assets/css/design02-accounts.css`
- `adopt/assets/js/design02-accounts.js`
- `adopt/signin/index.html`
- `reports/task-DESIGN-02.md`

## Small Out-of-Scope Fixes Made
Added a null guard to the legacy signin notification keydown binding because the replacement gateway intentionally removes the old notification form. Without the guard, the route emitted a console error on every load.

## Bugs and Follow-ups Sent to TODO.md
No new backend bug was invented or hidden. Google OAuth remains intentionally unconfigured until an authorized server-side provider contract exists; the UI states this plainly and sends no request. A future auth implementation must use an allow-listed redirect, state/nonce, PKCE where applicable, and server-side session establishment.

## Things the Next Person Must Know
The existing admin token endpoint remains the authority at `./admin-auth.php`; this redesign does not store or fabricate admin credentials. The existing rehoming posting form remains bound to `/rehoming/merchant-publish-listing.php`. Any future OAuth work must not place tokens or secrets in HTML, URLs, prompts, or reports.

No account deployed, uploaded, pushed to Hostinger, or triggered an automatic deployment. Coordinator reminder: take and verify both repository and Hostinger backups before any future deployment or rollback exercise.

## Self-Check Confirmation
Static checks passed for JavaScript syntax, duplicate IDs, existing admin auth hooks, existing admin panel switch handlers, and existing rehoming posting form IDs/endpoints. Rendered Playwright checks passed for the account gateway desktop/mobile shell, sign-in/create-account tab switching, empty-form error status and focus, Google not-configured status, admin login shell at mobile width, and rehoming hub at mobile width. The rendered rehoming hub reported zero console errors; the initial signin console error was found and fixed, then the route reloaded cleanly.

## Research Evidence
The component choices were checked against W3C error identification guidance, Google’s official Sign in with Google branding guidance, OWASP OAuth2 security guidance, and Carbon Design System data-table/dialog guidance. These support text-identifiable form errors, official Google button treatment, state/nonce/PKCE protections for a future OAuth flow, toolbar/table hierarchy, and user-initiated confirmation for destructive actions.

[1]: https://www.w3.org/WAI/WCAG22/Understanding/error-identification.html "W3C WCAG 2.2 Error Identification"
[2]: https://developers.google.com/identity/branding-guidelines "Google Sign in with Google branding guidelines"
[3]: https://cheatsheetseries.owasp.org/cheatsheets/OAuth2_Cheat_Sheet.html "OWASP OAuth2 Cheat Sheet"
[4]: https://www.carbondesignsystem.com/building-blocks/core/components/data-table "Carbon Design System data table"
[5]: https://www.carbondesignsystem.com/building-blocks/core/patterns/dialogs "Carbon Design System dialogs"
