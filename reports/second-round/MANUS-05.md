

## Follow-up implementation — 2026-11-04

The previously blocked A4 contract is now implemented as a standalone shared component at `adopt/assets/js/show-auth-prompt.js`.

- Exposes the exact `window.showAuthPrompt(contextMessage)` API required by A4 and downstream callers.
- Exposes `window.PetCenterAuthPrompt.configure({ signUpUrl, logInUrl })` so the account owner can set final destinations without editing the component.
- Uses `/signin/` as the current safe default destination for both actions, matching the current public sign-in entry point.
- Uses `textContent` for the context message, preventing caller-supplied HTML injection.
- Includes keyboard Escape dismissal, focus restoration, a focus trap, accessible dialog semantics, close-on-backdrop, responsive styling, and SVG iconography.
- Does not inspect authentication state; callers remain responsible for invoking it only for logged-out visitors, as required by the contract.

Validation completed:

- `node --check adopt/assets/js/show-auth-prompt.js`: passed.
- Static API/config contract check: passed.
- Chromium DOM smoke test with configured `/signup/` and `/login/` destinations: passed; the expected message and both links rendered.

The report’s A4 classification should be read as **Implemented / Needs Integration** rather than Blocked. The component is intentionally not injected into existing pages because A4’s file zone creates the reusable component only; each owning caller must load it at its integration checkpoint.

The other pending items remain dependency-bound, not ignored: A1’s referenced `pet-posting-form.html` and merchant publish route are absent; A2’s scheduler/browse integration is absent; C1, C5, and C6 have no current implementations; C4 is explicitly gated on C5; B5 requires D1/account persistence; and B3/B4/C2/C3 require real placement, enquiry, account, listing-upload, or review/report stores. No substitute endpoint or protected runtime data was fabricated.

Files changed in this follow-up:

- `adopt/assets/js/show-auth-prompt.js`
- `reports/second-round/MANUS-05.md`
