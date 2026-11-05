# Task-5b-Completion.md

## Task Number
**Task 5b (Optional) — Visitor Form Hardening** (from `WEB-PROJECTS-3-4-5-TASK-ASSIGNMENTS.md`, Project 5: Pet-Match Request feature)

## What Was Required
Add hardening to the visitor Pet Match Request form built in Task 5, since it's a new, fully public form open to anyone rather than the site's existing gated adoption flow. Two items: (1) lightweight, invisible bot/spam protection — not a disruptive CAPTCHA — layered with a honeypot field and basic server-side rate limiting; (2) a server-side max-file-size and MIME-type allowlist on the optional reference-image upload, since this form opens that upload feature to a wider, less-trusted audience. Both items were explicitly a headstart rather than a fixed spec, requiring my own research into an actual implementation (Rule 11) rather than treating the wording as a rigid design.

## Research (Rule 11)
Before implementing, I searched for current (2026) best practice on free, invisible form-hardening. Findings:
- A **honeypot field** (hidden from real visitors via off-screen CSS rather than `display:none`, since some bots specifically check for and skip that) plus a **minimum-fill-time trap** (rejecting submissions that arrive faster than a human could plausibly type) are the standard first layer — free, invisible, and catch different bot behaviors (generic form-fillers vs. fast scripted posts).
- **Cloudflare Turnstile**, run in **Invisible mode**, is free with unlimited sitekeys/challenges on its free tier and runs with zero visible UI or user friction, verifying a token server-side — this is the "invisible verification approach" layer Task 5b asked for, on top of the honeypot.
- Server-side IP-based rate limiting was modeled on the existing `chatCheckRateLimit()` / `chat_rate_limits` sliding-window pattern already documented in `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, reused for consistency rather than inventing a new limiter.

This gave a 3-layer, entirely free stack: honeypot + timing trap → Turnstile Invisible → server-side rate limiting, with file-size/MIME validation as a separate, unrelated check on the upload endpoint itself.

## Files Changed

| Source name | Live path | Changed or new | Shared with another task? |
|---|---|---|---|
| `pet-match.html` | `pet-match/index.html` | Changed | Yes — FORM-HARDENING zone, depends on Task 5's FORM-CORE zone. This file was Task 5's new file, not this task's — already delivered in `Project-5-Task-5.zip`, so it is documented here as a change against that baseline rather than re-included |

## Code Changes

### `pet-match.html`

**1. Honeypot field, timestamp trap, and Turnstile widget markup** — this block is correctly anchored in the live file. Added immediately after the image-terms error message, inside Step 7:

```html
<!-- OLD (the real line this sits directly after, unique in the file) -->
      <div class="pm-error-msg" id="err-pmImageTerms">Please confirm the terms above before submitting — this checkbox is required whether or not you attach an image.</div>

<!-- NEW (added directly after it) -->
      <!-- TASK-5b START — Form Hardening: honeypot field + minimum-fill-time
           trap + Cloudflare Turnstile Invisible mode. See Task-5b-Completion.md
           for the full design rationale (honeypot + timing trap + Turnstile
           Invisible + server-side rate limiting, confirmed via web search
           per Rule 11). All silent, zero-friction checks for a real visitor. -->
      <div class="pm-hp-field" aria-hidden="true">
        <label for="pmWebsiteHP">Leave this field blank</label>
        <input type="text" id="pmWebsiteHP" name="website" tabindex="-1" autocomplete="off">
      </div>
      <input type="hidden" id="pmFormRenderedAt" name="form_rendered_at" value="">
      <div class="cf-turnstile" data-sitekey="YOUR_TURNSTILE_SITE_KEY" data-callback="pmOnTurnstileSuccess" data-size="invisible"></div>
      <input type="hidden" id="pmTurnstileToken" name="turnstile_token" value="">
      <!-- TASK-5b END -->
```

`YOUR_TURNSTILE_SITE_KEY` is still a placeholder — the real sitekey needs to come from the Cloudflare dashboard before this goes live.

**2. Cloudflare Turnstile script tag** — also correctly anchored, placed after the Quill.js CDN tags:

```html
<!-- OLD (the real line this sits directly after, unique in the file) -->
<!-- TASK-5 END -->
(immediately following the Quill.js <link>/<script> tags block)

<!-- NEW (added directly after it) -->
<!-- TASK-5b START — Cloudflare Turnstile widget script, Invisible mode -->
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<!-- TASK-5b END -->
```

**3. Timestamp-trap init + Turnstile success callback (JS) — found with a broken anchor, not fixed this pass.** Checking the live file for this hand-in, this block's `// TASK-5b END` comment is present, but its matching `// TASK-5b START` is missing — it appears to have been lost during one of `pet-match.html`'s several full-file rebuilds since this was first written (most likely when the Rich Text Editor's own `TASK-5 START/END` block was inserted immediately before it). Per this round's explicit instruction not to redo or change the actual work, I have **not** edited `pet-match.html` to re-add the missing comment — documenting it here instead:

```javascript
/* CURRENT STATE IN THE LIVE FILE — missing its opening anchor */
})();
// TASK-5 END


document.addEventListener('DOMContentLoaded', function () {
  var renderedAtField = document.getElementById('pmFormRenderedAt');
  if (renderedAtField) renderedAtField.value = Date.now().toString();
});
function pmOnTurnstileSuccess(token) {
  var tokenField = document.getElementById('pmTurnstileToken');
  if (tokenField) tokenField.value = token;
}
// TASK-5b END          <-- this line IS present; the matching START above it is not
```

**Suggested fix for whoever next touches this file:** insert `// TASK-5b START — Form Hardening: timestamp trap init + Turnstile callback.` immediately before the `document.addEventListener('DOMContentLoaded', ...)` line shown above. Comment-only change, no functional effect.

**4. Honeypot bypass check + hardening fields in the submit payload — found with no anchors at all, not fixed this pass.** The `website`/`form_rendered_at`/`turnstile_token` lines inside `collectPayload()`, and the honeypot short-circuit check at the top of `submitPetMatchForm()`, carry **no `TASK-5b` anchor comments whatsoever** in the live file — also most likely lost across the same rebuilds. Documenting the code exactly as it currently stands, unanchored:

```javascript
/* CURRENT STATE IN THE LIVE FILE — no anchor comments at all around these lines */
      website: document.getElementById('pmWebsiteHP').value,
      form_rendered_at: document.getElementById('pmFormRenderedAt').value,
      turnstile_token: document.getElementById('pmTurnstileToken').value
    };
  }

  function submitPetMatchForm() {
    var honeypotField = document.getElementById('pmWebsiteHP');
    if (honeypotField && honeypotField.value) {
      showSuccessModal('PM-00000-0000');
      return;
    }
```

**Suggested fix for whoever next touches this file:** wrap the three payload lines in `// TASK-5b START` / `// TASK-5b END`, and the same for the `honeypotField`/`if` block in `submitPetMatchForm()`. Both are comment-only changes with no functional effect — not made here per this round's explicit instruction to fix only the hand-in, not the underlying work.

**5. Client-side file-size/MIME-type check — present and functioning, but living inside a `TASK-5` block rather than `TASK-5b`.** The `maxFiles`/`maxBytes`/`allowedTypes` constants and their enforcement (5MB max, JPG/PNG/WEBP only) are exactly Task 5b's own requirement #2, but they're physically inside the File Upload Component's `TASK-5 START`/`TASK-5 END` block (Task 5's own reused design-system component), not a `TASK-5b` block of their own. Flagging this as an attribution quirk rather than a functional bug — the validation genuinely works, it's just not anchored under the task that actually specified it. Not restructured this pass, for the same reason as items 3 and 4 above.

### Backend expectations (still not built — see Task 5's own completion file, Bugs Found Outside Task Scope item 4, for the full detail)
`/pet-match-submit.php` and `/pet-match-upload-temp.php` are the two endpoints this task's hardening logic depends on to actually enforce anything server-side (rate limiting, Turnstile token verification, the honeypot/timing checks, and the real file-size/MIME allowlist). Neither exists yet. Until they do, every check this task implemented is enforced client-side only, which is not a real defense on its own — client-side JS can always be bypassed. This is shared with Task 5 and Task 6a/9's own pending items, not unique to this task, but worth restating plainly here since it directly concerns what this task was actually asked to secure.

## New files
No new files were created by this task. `pet-match.html` was created by Task 5, not this task; this task's contribution is documented above as changes against that file.

## Bugs Found Outside Task Scope
None beyond what's already logged in Task 5's own completion file (this task only touched the hardening zone of the same file Task 5 created; no other files were opened for this task). The three anchor-comment gaps found in Code Changes items 3 and 4 above are this task's own zone, not outside it — logged there rather than here, and intentionally left unfixed this pass per the explicit instruction to fix only the hand-in format.

## Self-Check Confirmation
I only modified `pet-match.html` — the one file in scope for this task, and the same file Task 5 created and already delivered in its own zip. No new file was created by this task, so `Project-5-Task-5b.zip` contains only this completion file. Scanned before delivery to confirm it holds no secrets, `.env`, `config.php`, `pet.json`, or any `applications/`/`uploads/` content (trivially true for a zip containing a single markdown file, but checked anyway for consistency). Every code block I added or changed in `pet-match.html` is addressed above exactly as it currently exists in the live file — including the three places where its own anchor comments have gone missing or were never applied to the right task, which I found during this hand-in review and disclosed rather than silently fixed, per this round's explicit instruction not to redo or change the underlying work.
