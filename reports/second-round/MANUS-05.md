

## Listing-page and signup gateway audit — 2026-11-10

**Account/worker ID:** `MANUS-05`

The exact comparison of `origin/main:adopt/merchant/post-listing/index.html` and `origin/adopt-deploy:merchant/post-listing/index.html` found identical listing markup and behavior. The only difference was that the deployment copy already contained the sitewide `admin/chat-widget.js` and `assets/js/accessibility-widget.js` scripts, while `main` did not. Those scripts have now been carried into `main` so the source and deployment versions have one authoritative content set; the page is ready to be republished to `adopt-deploy`.

Live route checks confirmed `/rehoming/`, `/rehoming/list-a-pet/`, `/merchant/post-listing/`, and `/merchant/` return HTTP 200. The reason the platform is not discoverable through the visible “Sign Up” navigation is separate: `/signin/` is currently a “Coming Soon — List Your Pet” notification page, while `/signup/`, `/register/`, `/account/signup/`, and `/account/register/` return HTTP 404. The rehoming hub exists, but the current global navigation does not expose it as the signup/authentication entry point.

### MAJOR INTEGRATOR ISSUE — signup/authentication gateway is outside MANUS-05 ownership

MANUS-05 was assigned the historical Rehoming Platform A1–C6 package, not the account-registration gateway. The repository explicitly states that registration/login pages are outside the A1–C6 task scope. The current operational assignment names `WEB-04` (`qa/rehoming-auth-backend`) as owner of the rehoming authentication gateway, login/signup routes, sessions, OAuth structure, route protection, authorization, legal routes, and security tests. The redesign brief separately assigns `DESIGN-02` (`redesign/admin-rehoming`) the authentication-gateway interface and rehoming visual redesign.

This is therefore a major integrator dependency, not a MANUS-05 implementation omission. Integrators must merge or complete the WEB-04 authentication backend and coordinate the DESIGN-02 gateway UI, then replace the current `/signin/` “Coming Soon” destination and 404 signup routes with the authoritative signup/login route set. Until that happens, users cannot create an account through the public navigation and cannot reach authenticated rehoming chat/review flows through a normal signup journey.

The widget-preserving listing-page change and this handoff update must be committed to `main`, after which `adopt-deploy` should be regenerated from the resulting `adopt/` tree. No signup implementation is fabricated in MANUS-05 because doing so would create a parallel authentication system outside the assigned contract.
