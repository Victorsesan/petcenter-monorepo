

## Current-main integration pass — 2026-11-05

After `origin/main` advanced through the MANUS-04 integration merge (`5814eab`), the current branch was merged with that latest application state rather than leaving the task on an older base. The latest category-page Pet Match notification integration was preserved, while the MANUS-10 accessibility rollout, language loaders, category indexes, chat scroll restoration, responsive fixes, and public fallback assets were restored where the integration merge had removed them.

The expanded route inventory now covers **52 active HTML routes** from the merged tree. Playwright regression at 1440×900 and 390×844 completed **104 checks** with **0 HTTP failures, 0 uncaught page errors, 0 horizontal-overflow failures, and 0 non-protected failed requests**. The only 404s are the 52 protected per-pet `adoptions/pets/*/pet.json` records. The intentional legacy `/about.html` external adoption-policy redirect was tested with commit-level navigation handling and is not a local route failure.

The pass also repaired current-main-only defects found during the expanded sweep: missing livestock/aquarium banner paths, the legacy application root logo path, an unavailable Cloudflare-only email decoder reference, the legacy application mobile column overflow, and the legacy apply variant step-indicator overflow. All active non-vendor public pages now carry both shared `/admin/chat-widget.js` and `/assets/js/accessibility-widget.js` loaders; admin/vendor/archived package content remains excluded where appropriate. JavaScript, PHP, HTML structure, diff hygiene, and protected-path staging checks all pass.
