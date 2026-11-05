# TASK C3 COMPLETION - Onboarding safety UX

## Task Number
TASK C3 — Onboarding safety UX (Phase C, Complexity Low-Medium)

## What Was Required
Three small, independent UI prompts:
1. A Merchant Code of Conduct screen in the merchant onboarding flow, with a required "I agree" checkbox, timestamped and logged to the merchant account record.
2. A dismissible safe-exchange-zone chat prompt that appears once a thread has 3+ messages from both parties and a meetup keyword, max once per thread.
3. A gift-adoption checkbox on Task C5's new first-contact compose UI, revealing a required confirmation checkbox when ticked.

## NEW FOLDER REQUIRED - INTEGRATOR READ THIS FIRST

**CREATE THE FOLDER `assets/js/` AT THE SITE ROOT (inside `public_html/adopt/`) IF IT DOES NOT ALREADY EXIST.**

Neither `adoption-website-project-summary.md` nor `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` documents an `assets/js/` folder (they only document `/assets/images/`). The path comes from WORKER-DELIVERY-GUIDE.md's "Shared CSS/JS under `assets/`" row, not from the summaries. Both new files below sit in it. If your real site keeps shared scripts elsewhere, move both files there and tell me so the task is updated.

Other items in the zip that are NOT site files (do not deploy): this completion file at the zip root, and the folder `Task-C3-Evidence/` (Playwright screenshots).

No `.html` page was created by this task, so the `index.html` folder rule does not apply to any zipped file. (The gift-check block is an HTML fragment for Task C5's compose UI, not a page.)

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `merchant-code-of-conduct-v1.js` | `assets/js/merchant-code-of-conduct.js` **(NEW FOLDER `assets/js/` - not in either summary)** | New | No |
| `safe-exchange-prompt-v1.js` | `assets/js/safe-exchange-prompt.js` **(NEW FOLDER `assets/js/` - not in either summary)** | New | No (called by Task C5's thread view; does not edit C5's file) |
| `merchant-contact-gift-check-v1.html` (gift-check block) | No live path yet — fragment for Task C5's compose UI file, which does not exist on the live site yet | New (not zipped — see note below) | **Yes — Task C5** |

**Live-path note:** neither of the two tables in WORKER-DELIVERY-GUIDE.md section 3, nor the folder-structure sections of `adoption-website-project-summary.md` / `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, document a location for new merchant-onboarding or messaging components (this part of the Rehoming Platform isn't live yet). I placed the two standalone JS files under `assets/js/` by analogy with the guide's existing rule for shared CSS/JS. Please confirm or correct these two paths — not guessing silently, flagging per the guide's instruction.

**Gift-check note:** this is not a standalone file — it's a fragment meant to be merged into Task C5's compose UI at the named insertion point `<!-- MERCHANT-CONTACT: gift-check -->`. Since that target file doesn't exist on the live site yet, it has no live path of its own and isn't a "brand-new file" in the zip sense (per the guide, the zip is for files with their own live path). It's provided in full below, under New Files, for the integrator to merge once C5's file exists.

## Code Changes
No existing file was changed. All three outputs are new.

## New Files

- `assets/js/merchant-code-of-conduct.js` — New file — in the zip (`Rehoming-Platform-Task-C3.zip`). Standalone Code of Conduct modal component (item 1). Public API: `PetCenterCodeOfConduct.show({onAgree})`, `.requireBeforeListing(account, proceedFn)`, `.configure({endpoint, version})`. Logs `{code_of_conduct_agreed_at, code_of_conduct_version}` via POST to `/merchant-conduct-agree` (placeholder endpoint — not yet built).
- `assets/js/safe-exchange-prompt.js` — New file — in the zip. Standalone safe-exchange-zone chat prompt component (item 2). Public API: `PCSafeExchange.shouldShow(messages, threadId)`, `.render(messages, containerEl, threadId)`, `.configure({getRole, getText})`.
- Gift-adoption block (item 3) — New file — **not in the zip** (see Gift-check note above). Full content below, wrapped in `<!-- TASK-C3 START -->` / `<!-- TASK-C3 END -->`, to replace Task C5's `<!-- MERCHANT-CONTACT: gift-check -->` placeholder once C5's compose UI file exists:

```html
<!-- =====================================================================
     TASK C3 - ITEM 3: GIFT-ADOPTION PROMPT
     Paste everything between the TASK-C3 START and END anchors below into
     Task C5's compose UI, in place of the empty placeholder comment whose
     text is: MERCHANT-CONTACT: gift-check
     (immediately below the message field, above the send button).
     This block gates nothing itself and never calls showAuthPrompt().

     HOOK FOR C5's SEND BUTTON:
       if (!PCGiftCheck.validate()) return;     // blocks send when invalid
       var gift = PCGiftCheck.getValue();       // { is_gift, recipient, confirmed }
     ===================================================================== -->
<!-- TASK-C3 START -->
<style>
  /* Scoped with the pc-gift prefix so it cannot clash with C5's styles */
  .pc-gift { margin: 12px 0; font-family: "Sora", system-ui, sans-serif; color: #2c2416; }
  .pc-gift__row { display: flex; align-items: flex-start; gap: 12px; min-height: 44px; padding: 4px 0; cursor: pointer; font-size: 14px; line-height: 1.5; }
  .pc-gift__row input[type="checkbox"] { width: 22px; height: 22px; margin: 0; flex-shrink: 0; accent-color: #10214b; cursor: pointer; }
  .pc-gift__row input[type="checkbox"]:focus-visible { outline: 3px solid rgba(215,189,136,0.6); outline-offset: 2px; }
  /* The revealed area is hidden until the first box is ticked */
  .pc-gift__more { display: none; margin: 4px 0 0 34px; flex-direction: column; gap: 10px; }
  .pc-gift__more.show { display: flex; }
  .pc-gift__field { display: flex; flex-direction: column; gap: 6px; }
  .pc-gift__field label { font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
  .pc-gift__field input[type="text"] { background: #fff; border: 1.5px solid rgba(16,33,75,0.12); border-radius: 9px; padding: 11px 14px; font-size: 14px; font-family: inherit; color: #2c2416; outline: none; transition: all 0.3s cubic-bezier(0.4,0,0.2,1); }
  .pc-gift__field input[type="text"]:focus { border-color: #d7bd88; background: #fafaf8; box-shadow: 0 0 0 3px rgba(215,189,136,0.15); }
  .pc-gift__field input[type="text"].invalid { border-color: #ef4444; }
  .pc-gift__confirm-text { font-weight: 400; }
  .pc-gift__error { display: none; font-size: 13px; color: #ef4444; line-height: 1.5; margin: 0; }
  .pc-gift__error.show { display: block; }
</style>

<div class="pc-gift" id="pcGift">
  <label class="pc-gift__row">
    <input type="checkbox" id="pcGiftIsGift" aria-controls="pcGiftMore" aria-expanded="false">
    <span>Is this pet intended as a gift for someone else?</span>
  </label>

  <div class="pc-gift__more" id="pcGiftMore">
    <!-- Fills in the [name/relationship] part of the confirmation sentence -->
    <div class="pc-gift__field">
      <label for="pcGiftWho">Who is it for?</label>
      <input type="text" id="pcGiftWho" maxlength="60" placeholder="Name or relationship, e.g. my sister" autocomplete="off">
    </div>
    <label class="pc-gift__row">
      <input type="checkbox" id="pcGiftConfirm">
      <span class="pc-gift__confirm-text">I confirm <strong id="pcGiftWhoEcho">[name/relationship]</strong> has expressed genuine interest in adopting this pet and will be part of this decision.</span>
    </label>
    <p class="pc-gift__error" id="pcGiftError" role="alert"></p>
  </div>
</div>

<script>
/* ---- TASK-C3: gift-adoption prompt logic ---- */
(function () {
  var isGift = document.getElementById('pcGiftIsGift');
  var more = document.getElementById('pcGiftMore');
  var who = document.getElementById('pcGiftWho');
  var echo = document.getElementById('pcGiftWhoEcho');
  var confirmBox = document.getElementById('pcGiftConfirm');
  var err = document.getElementById('pcGiftError');

  // Show/hide the confirmation area; reset it when the first box is unticked
  isGift.addEventListener('change', function () {
    more.classList.toggle('show', isGift.checked);
    isGift.setAttribute('aria-expanded', String(isGift.checked));
    if (!isGift.checked) { who.value = ''; confirmBox.checked = false; echo.textContent = '[name/relationship]'; clearError(); }
  });

  // Live-update the confirmation sentence with whatever the visitor types
  who.addEventListener('input', function () {
    echo.textContent = who.value.trim() || '[name/relationship]';
    who.classList.remove('invalid');
  });

  function clearError() { err.classList.remove('show'); err.textContent = ''; who.classList.remove('invalid'); }

  // Returns true when the form may be sent. Unticked first box = nothing required.
  function validate() {
    clearError();
    if (!isGift.checked) return true;
    if (!who.value.trim()) {
      who.classList.add('invalid'); who.focus();
      err.textContent = 'Please tell us who this pet is for.'; err.classList.add('show');
      return false;
    }
    if (!confirmBox.checked) {
      confirmBox.focus();
      err.textContent = 'Please confirm before sending your message.'; err.classList.add('show');
      return false;
    }
    return true;
  }

  // Values for C5 to send along with the first message
  function getValue() {
    return { is_gift: isGift.checked, recipient: isGift.checked ? who.value.trim() : '', confirmed: isGift.checked && confirmBox.checked };
  }

  window.PCGiftCheck = { validate: validate, getValue: getValue };
})();
</script>
<!-- TASK-C3 END -->
```

Hook for C5's send button: `if (!PCGiftCheck.validate()) return;` then `PCGiftCheck.getValue()` returns `{ is_gift, recipient, confirmed }` to store/send with the first message.

## Bugs Found Outside Task Scope
None found.

## Self-Check Confirmation
- Touched only the files listed above; nothing outside the C3 file zone was edited or included (`adoption-application.html`, admin files, and the CEO's signup/login pages untouched and never referenced).
- No existing file was changed, so no old-code snippets apply; both new standalone files are in the zip in full, unmodified from what was tested.
- Anchor comments `<!-- TASK-C3 START -->` / `<!-- TASK-C3 END -->` present around the gift-check block for Task C5's future merge.
- Zip is named `Rehoming-Platform-Task-C3.zip` (project name taken from the project file `Rehoming-Platform-Task-Assignments.md`, plus the task number). Its contents match this file exactly: the two new JS files above at their live paths, plus this completion file and the `Task-C3-Evidence/` screenshots at the zip root (not site files, do not deploy). No existing file is in the zip.
- Checked the zip contains no `config.php`, `pet.json`, `.env`, secrets, tokens, or anything under `applications/`, `uploads/`, `chat-media/`, or `logs/` — confirmed, it only contains the two JS files above.
- Tested with Playwright (58/58 automated checks across desktop 1200px and mobile 375px viewports): modal cannot be dismissed without agreeing, Continue stays disabled until ticked, timestamp POST body correct, failed-save path keeps modal open with an error, `requireBeforeListing` skips the modal when already agreed, every chat-prompt trigger/non-trigger condition, once-per-thread held after dismissal, gift-check reveal/reset/validation/getValue, no horizontal overflow, no JS console errors. Screenshots (8 images: code-of-conduct, safe-exchange-prompt, gift-check blocked and confirmed, each at desktop and mobile) are in `Task-C3-Evidence/` inside the zip.
- `showAuthPrompt()` is never called by C3 (confirmed by VDM in a prior round). No dropdown menus in this task. No emojis anywhere — inline SVG only. No third-party libraries used.

## Still pending (carried over from the prior hand-in, unresolved by this format fix)
1. The merchant onboarding flow does not exist yet — item 1 is a standalone component until whoever builds that flow (or Task A1's posting panel) calls `PetCenterCodeOfConduct.requireBeforeListing(account, proceedFn)`.
2. The `/merchant-conduct-agree` save endpoint is a placeholder and still needs to be built; the merchant account record needs `code_of_conduct_agreed_at` and `code_of_conduct_version` fields.
3. Item 2's message shape (`{sender_role, text}`) is an assumption pending Task C5's published interface; `configure()` adapts to other field names. C5's thread view must call `PCSafeExchange.render(messages, threadEl, threadId)`.
4. Item 3 (gift-check) still needs to be merged into Task C5's real compose UI once it exists.
5. **Doubt - live path of the two JS files:** `assets/js/` is an assumption (see the NEW FOLDER banner). Please confirm or correct.
6. **Doubt - code-of-conduct wording of the "G1 checkout-link" bullet:** it refers to the platform's checkout-link feature by description only, since Task G1 is not built yet. Re-check the bullet once G1's feature name is final.
7. **Doubt - "3+ messages" and "[name/relationship]" interpretations** (3+ total messages with both parties having written; a required "Who is it for?" field fills the confirmation sentence). Tell me if either should work differently.
8. **Limitation:** "shown once per thread" is remembered per browser (localStorage with in-memory fallback), not per account or device. Cross-device persistence would need a per-thread flag stored by Task C5's backend.
9. **No bugs outside scope were found**, and no other findings beyond the items in this section.
