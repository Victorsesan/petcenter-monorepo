# Task 3 Completion — Chat Language Preference Selection & Notice

## Task Number

"Task 3 — Chat Language Preference Selection & Notice" (from
`ADMIN_PROJECTS_2_3_4_TASK_ASSIGNMENTS.md`, Admin-Project #4 of the
"Admin-Projects 2, 3, 4" tracker).

## What Was Required

Before the chat panel opens, let the customer pick their preferred chat
language from a dropdown listing each language by its own native name.
Pre-select it using a fixed priority order (this browser's remembered
choice, then the sitewide Settings Widget's saved preference, then
browser-detected language, then a configured default), while still letting
the customer change it manually. Persist the choice permanently against
the customer's chat ID and email (not just the current session), make it
visible to admins, and tell the customer — right after they pick a
language — that they can change it later from the options panel and that
doing so also updates what admins see. Directly beneath that, show one
exact, non-negotiable disclosure sentence about peak-hour English-only
voice support and auto-translate-assisted text support. Store the value
under the exact field name `preferred_chat_language` so Tasks 1a/1b (a
separate transcription feature) can read it — this task never translates
anything itself. This file also documents the later real merge of this
work against Admin-Project #1's PIN (its own, differently-numbered Task 3)
and Lock Toggle (Task 4) code, which landed in two of the same files after
this task's original build, plus a Second Verification Round that found
and fixed two real gaps against the original brief.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `chat-config.php` | `admin/chat-config.php` | Changed | No |
| `chat-setup.php` | `admin/chat-setup.php` | Changed | No |
| `chat-migrate.php` | `admin/chat-migrate.php` | Changed | No |
| `chat-start.php` | `admin/chat-start.php` | Changed | Yes — also written by Admin-Project #1's Task 1 (session tokens) and Task 3 (PIN) |
| `chat-widget.js` | `admin/chat-widget.js` | Changed | Yes — also written by Admin-Project #1's Task 1 (session tokens), Task 3 (PIN), and Task 4 (Lock Toggle) |
| `admin-dashboard.html` | `admin/admin-dashboard.html` | Changed | No |
| `chat-update-language.php` | `admin/chat-update-language.php` | New | No |

`chat-admin-list.php` was requested and reviewed (every query there uses
`SELECT t.*`/`t.*`, so `preferred_chat_language` reaches it automatically)
but was never edited, so it is not listed as Changed here.

## Code Changes

### `chat-config.php`  →  live path `admin/chat-config.php`

<!-- AP4-TASK-3 START -->
**Old code:**
```php
define('CHAT_NO_ANSWER_MESSAGE', "Our team typically replies within a few hours. We'll get back to you as soon as we can.");
```
**New code:**
```php
define('CHAT_NO_ANSWER_MESSAGE', "Our team typically replies within a few hours. We'll get back to you as soon as we can.");

// ============================================
// AP4-TASK-3 START — Chat Language Preference
// Supported language codes for the pre-chat / options-panel language picker.
// Keys are ISO 639-1 two-letter codes, EXCEPT Chinese, which uses 'zh-Hans'
// (Simplified) per the Shared Data Contract with the sitewide translation
// project's preferences.lang codes — plain 'zh' is intentionally not a key.
// Values are each language's own NATIVE name (never the English name), since
// the picker must be readable by a speaker of that language, not of English.
// Editable here without touching any endpoint or front-end logic.
// ============================================
define('CHAT_SUPPORTED_LANGUAGES', [
    'en'      => 'English',
    'es'      => 'Español',
    'fr'      => 'Français',
    'de'      => 'Deutsch',
    'pt'      => 'Português',
    'it'      => 'Italiano',
    'zh-Hans' => '中文（简体）',
    'ja'      => '日本語',
    'ko'      => '한국어',
    'ar'      => 'العربية',
    'hi'      => 'हिन्दी',
    'vi'      => 'Tiếng Việt',
    'tl'      => 'Filipino',
    'ru'      => 'Русский',
]);

define('CHAT_DEFAULT_LANGUAGE', 'en');
// AP4-TASK-3 END
```
<!-- AP4-TASK-3 END -->

<!-- AP4-TASK-3 START -->
**Old code:**
```php
function chatValidReason($reason) {
    return array_key_exists($reason, CHAT_REASONS);
}
```
**New code:**
```php
function chatValidReason($reason) {
    return array_key_exists($reason, CHAT_REASONS);
}

// AP4-TASK-3 START — validates a submitted language code against the
// supported list; mirrors chatValidReason() above exactly so callers never
// have to special-case one vs. the other.
function chatValidLanguage($lang) {
    return array_key_exists($lang, CHAT_SUPPORTED_LANGUAGES);
}
// AP4-TASK-3 END
```
<!-- AP4-TASK-3 END -->

---

### `chat-setup.php`  →  live path `admin/chat-setup.php`

<!-- AP4-TASK-3 START -->
**Old code:**
```php
            unread_for_admin    TINYINT(1) DEFAULT 1,
            unread_for_user     TINYINT(1) DEFAULT 0,
            archived            TINYINT(1) NOT NULL DEFAULT 0,
            INDEX (email), INDEX (status), INDEX (chat_id), INDEX (archived)
```
**New code:**
```php
            unread_for_admin    TINYINT(1) DEFAULT 1,
            unread_for_user     TINYINT(1) DEFAULT 0,
            archived            TINYINT(1) NOT NULL DEFAULT 0,
            /* AP4-TASK-3 START — customer's selected chat language (ISO 639-1,
               or 'zh-Hans' for Simplified Chinese per the shared preferences.lang
               contract). NULL until the pre-chat gate sets it; VARCHAR(10) is
               generous headroom over the longest real code used here. */
            preferred_chat_language VARCHAR(10) NULL,
            /* AP4-TASK-3 END */
            INDEX (email), INDEX (status), INDEX (chat_id), INDEX (archived)
```
<!-- AP4-TASK-3 END -->

---

### `chat-migrate.php`  →  live path `admin/chat-migrate.php`

<!-- AP4-TASK-3 START -->
**Old code:**
```php
    "Widen chat_typing.who ENUM to include recording-ping values (Task 8b)" =>
        "ALTER TABLE chat_typing MODIFY who ENUM('user','admin','user_recording','admin_recording') NOT NULL",
    // BUGFIX-1 END
```
**New code:**
```php
    "Widen chat_typing.who ENUM to include recording-ping values (Task 8b)" =>
        "ALTER TABLE chat_typing MODIFY who ENUM('user','admin','user_recording','admin_recording') NOT NULL",
    // BUGFIX-1 END

    // AP4-TASK-3 START — Chat Language Preference. Same additive,
    // non-destructive pattern as the "archived" column migration above:
    // ADD COLUMN IF NOT EXISTS is safe to re-run and never touches existing
    // rows' data (they simply get NULL until next set by the pre-chat gate
    // or the options-panel re-picker).
    "Add preferred_chat_language column to chat_threads" =>
        "ALTER TABLE chat_threads ADD COLUMN IF NOT EXISTS preferred_chat_language VARCHAR(10) NULL",
    // AP4-TASK-3 END
```
<!-- AP4-TASK-3 END -->

### `chat-start.php`  →  live path `admin/chat-start.php`

**Note — shared file:** this file already carried Admin-Project #1's
Task 1 (`/* TASK-1 START/END */`, session tokens) and Task 3
(`/* TASK-3 START/END */`, PIN gating) anchors before this task's work
began. Every "Old code" snippet below is copied character for character
from the real file as supplied, PIN/Task-1 code included, and none of
their code was altered — only this task's own `AP4-TASK-3`-anchored lines
were added around it.

<!-- AP4-TASK-3 START -->
**Old code:**
```php
   Never required — a thread with no PIN configured ignores this field
   entirely on every path. */
$pin = trim($input['pin'] ?? '');
/* TASK-3 END */
```
**New code:**
```php
   Never required — a thread with no PIN configured ignores this field
   entirely on every path. */
$pin = trim($input['pin'] ?? '');
/* TASK-3 END */
// AP4-TASK-3: customer's pre-chat language selection. Falls back to the
// configured default rather than rejecting the request outright — an
// invalid/missing code here should never block a customer from chatting.
$preferredLanguage = trim($input['preferredLanguage'] ?? '');
if (!chatValidLanguage($preferredLanguage)) {
    $preferredLanguage = CHAT_DEFAULT_LANGUAGE;
}
```
<!-- AP4-TASK-3 END -->

<!-- AP4-TASK-3 START -->
**Old code:**
```php
                'status'       => $thread['status'],
            ]);
        }
        $sessionToken = $gate['token'];
        /* TASK-3 END */

        chatRespond([
```
**New code:**
```php
                'status'       => $thread['status'],
            ]);
        }
        $sessionToken = $gate['token'];
        /* TASK-3 END */

        // AP4-TASK-3: keep the stored language in sync with whatever the
        // customer selected THIS time through the pre-chat gate — same
        // "latest submission wins" spirit as the reason-change check above,
        // but this always applies (not just on 'done' threads), since
        // language can legitimately change any time the gate is shown
        // again. Placed AFTER the PIN gate resolves successfully (never
        // before) — a request that hasn't passed the gate yet (wrong/no
        // PIN, authRequired=true above) must never be allowed to write to
        // this thread, language included.
        if ($preferredLanguage !== $thread['preferred_chat_language']) {
            $db->prepare("UPDATE chat_threads SET preferred_chat_language = ? WHERE id = ?")
               ->execute([$preferredLanguage, $thread['id']]);
        }

        chatRespond([
```
<!-- AP4-TASK-3 END -->

<!-- AP4-TASK-3 START -->
**Old code:**
```php
            'status'       => $thread['status'],
            'fullName'     => $thread['full_name'],
            'email'        => $thread['email'],
            /* TASK-1 START — sessionToken in the response payload */
            'sessionToken' => $sessionToken,
            /* TASK-1 END */
        ]);
```
**New code:**
```php
            'status'       => $thread['status'],
            'fullName'     => $thread['full_name'],
            'email'        => $thread['email'],
            /* TASK-1 START — sessionToken in the response payload */
            'sessionToken' => $sessionToken,
            /* TASK-1 END */
            'preferredLanguage' => $preferredLanguage, // AP4-TASK-3
        ]);
```
<!-- AP4-TASK-3 END -->

<!-- AP4-TASK-3 START -->
**Old code:**
```php
            'status'       => $existing['status'],
        ]);
    }
    $sessionToken = $gate['token'];
    /* TASK-3 END */

    chatRespond([
```
**New code:**
```php
            'status'       => $existing['status'],
        ]);
    }
    $sessionToken = $gate['token'];
    /* TASK-3 END */

    // AP4-TASK-3: same sync-on-resume behavior as Path 1 above, and same
    // ordering rule — only after the PIN gate resolves successfully.
    if ($preferredLanguage !== $existing['preferred_chat_language']) {
        $db->prepare("UPDATE chat_threads SET preferred_chat_language = ? WHERE id = ?")
           ->execute([$preferredLanguage, $existing['id']]);
    }

    chatRespond([
```
<!-- AP4-TASK-3 END -->

<!-- AP4-TASK-3 START -->
**Old code:**
```php
        'status'       => $existing['status'],
        'fullName'     => $existing['full_name'],
        'email'        => $existing['email'],
        /* TASK-1 START — sessionToken in the response payload */
        'sessionToken' => $sessionToken,
        /* TASK-1 END */
    ]);
```
**New code:**
```php
        'status'       => $existing['status'],
        'fullName'     => $existing['full_name'],
        'email'        => $existing['email'],
        /* TASK-1 START — sessionToken in the response payload */
        'sessionToken' => $sessionToken,
        /* TASK-1 END */
        'preferredLanguage' => $preferredLanguage, // AP4-TASK-3
    ]);
```
<!-- AP4-TASK-3 END -->

<!-- AP4-TASK-3 START -->
**Old code:**
```php
$stmt = $db->prepare(
    "INSERT INTO chat_threads (chat_id, email, full_name, reason, pet_context, status, unread_for_admin)
     VALUES (?, ?, ?, ?, ?, 'waiting', 0)"
);
$stmt->execute([
    $newChatId,
    $email,
    $fullName,
    $reason,
    $petContext ? json_encode($petContext) : null,
]);
```
**New code:**
```php
// AP4-TASK-3: preferred_chat_language is captured at creation, same moment
// as every other pre-chat field — a thread never exists without it set to
// at least the configured default.
$stmt = $db->prepare(
    "INSERT INTO chat_threads (chat_id, email, full_name, reason, pet_context, status, unread_for_admin, preferred_chat_language)
     VALUES (?, ?, ?, ?, ?, 'waiting', 0, ?)"
);
$stmt->execute([
    $newChatId,
    $email,
    $fullName,
    $reason,
    $petContext ? json_encode($petContext) : null,
    $preferredLanguage,
]);
```
<!-- AP4-TASK-3 END -->

<!-- AP4-TASK-3 START -->
**Old code:**
```php
    'status'       => 'waiting',
    'fullName'     => $fullName,
    'email'        => $email,
    /* TASK-1 START — sessionToken in the response payload */
    'sessionToken' => $sessionToken,
    /* TASK-1 END */
]);
```
**New code:**
```php
    'status'       => 'waiting',
    'fullName'     => $fullName,
    'email'        => $email,
    /* TASK-1 START — sessionToken in the response payload */
    'sessionToken' => $sessionToken,
    /* TASK-1 END */
    'preferredLanguage' => $preferredLanguage, // AP4-TASK-3
]);
```
<!-- AP4-TASK-3 END -->

### `chat-widget.js`  →  live path `admin/chat-widget.js`

**Note — shared file:** this file already carried Admin-Project #1's
Task 1 (session tokens), Task 3 (PIN), and Task 4 (Lock Toggle) anchors
before this task's work began. Every "Old code" snippet below is copied
character for character from the real file as supplied, their code
included, and none of it was altered, reordered, or removed — only this
task's own `AP4-TASK-3`-anchored lines were added around it. One line not
wrapped in an anchor was also corrected (a comment, singular → plural)
because it became inaccurate the moment a second line was added next to
it — shown in its own block below with an explanation.

<!-- AP4-TASK-3 START -->
**Old code:**
```javascript
    storageKeyDraftUuid: 'pc_chat_draft_uuid', // pre-thread temporary draft key, Section 4
    contactFormUrl: '/contact-us/contact-us.html', // absolute path — contact-us.html lives at /contact-us/ not site root
  };
```
**New code:**
```javascript
    storageKeyDraftUuid: 'pc_chat_draft_uuid', // pre-thread temporary draft key, Section 4
    contactFormUrl: '/contact-us/contact-us.html', // absolute path — contact-us.html lives at /contact-us/ not site root
  };

  // ============================================
  // AP4-TASK-3 START — Chat Language Preference
  // Mirrors chat-config.php's CHAT_SUPPORTED_LANGUAGES exactly (same keys,
  // same native-name values). Kept as a plain object here rather than
  // fetched from the server, matching how CHAT_REASONS' labels are already
  // hardcoded client-side in this file (see the pre-chat reason dropdown) —
  // one shared editable source of truth per side, not a network round trip
  // just to render a picker.
  // ============================================
  const SUPPORTED_LANGUAGES = {
    en: 'English',
    es: 'Español',
    fr: 'Français',
    de: 'Deutsch',
    pt: 'Português',
    it: 'Italiano',
    'zh-Hans': '中文（简体）',
    ja: '日本語',
    ko: '한국어',
    ar: 'العربية',
    hi: 'हिन्दी',
    vi: 'Tiếng Việt',
    tl: 'Filipino',
    ru: 'Русский',
  };
  const DEFAULT_LANGUAGE = 'en';
  const LANGUAGE_STORAGE_KEY = 'pc_chat_preferred_language'; // last explicit customer choice, this browser only

  // Resolves the language to pre-select in the picker, in priority order:
  //  1. This browser's own last explicit choice (LANGUAGE_STORAGE_KEY) — a
  //     returning customer's own prior pick always wins over re-detecting.
  //  2. The sitewide Settings Widget's saved preference, per the Shared Data
  //     Contract: JSON.parse(localStorage.getItem('preferences')).lang —
  //     guarded against a missing/malformed key so this never throws.
  //  3. The browser's own reported language (navigator.language), reduced to
  //     a supported code — 'zh-Hant'/'zh-TW'/bare 'zh' all resolve to
  //     'zh-Hans' since Traditional Chinese isn't separately offered.
  //  4. CHAT_DEFAULT_LANGUAGE (English) if nothing above matched.
  function resolveDefaultLanguage() {
    const stored = localStorage.getItem(LANGUAGE_STORAGE_KEY);
    if (stored && SUPPORTED_LANGUAGES[stored]) return stored;

    try {
      const sitePrefs = JSON.parse(localStorage.getItem('preferences') || '{}');
      if (sitePrefs && sitePrefs.lang && SUPPORTED_LANGUAGES[sitePrefs.lang]) {
        return sitePrefs.lang;
      }
    } catch (e) {
      // malformed 'preferences' value — fall through to browser detection
    }

    const browserLang = (navigator.language || '').toLowerCase();
    if (browserLang.startsWith('zh')) return 'zh-Hans';
    const shortCode = browserLang.split('-')[0];
    if (SUPPORTED_LANGUAGES[shortCode]) return shortCode;

    return DEFAULT_LANGUAGE;
  }
  // AP4-TASK-3 END
```
<!-- AP4-TASK-3 END -->

<!-- AP4-TASK-3 START -->
**Old code:**
```javascript
    draftTimer: null,
    draftKey: null,        // real chatId once a thread exists, else the temp UUID
  };
```
**New code:**
```javascript
    draftTimer: null,
    draftKey: null,        // real chatId once a thread exists, else the temp UUID
    preferredLanguage: null, // AP4-TASK-3 — set once the pre-chat gate resolves/submits it
  };
```
<!-- AP4-TASK-3 END -->

<!-- AP4-TASK-3 START -->
**Old code:**
```javascript
  .pcw-accessibility-panel.open { max-height: 70px; opacity: 1; margin-top: 10px; padding-bottom: 4px; }
```
**New code:**
```javascript
  .pcw-accessibility-panel.open { max-height: 70px; opacity: 1; margin-top: 10px; padding-bottom: 4px; }
  /* AP4-TASK-3 START — the language re-picker reuses .pcw-accessibility-panel's
     collapse/expand mechanics (same base class) but needs far more vertical
     room than the accessibility icon row (a dropdown plus the required
     notice paragraph). ap4-lang-panel is an ADDITIONAL class alongside
     .pcw-accessibility-panel, not a replacement, so its own open-state
     height lives here rather than widening the shared .open rule and
     affecting the unrelated accessibility panel. */
  .pcw-accessibility-panel.ap4-lang-panel.open { max-height: 260px; }
  /* AP4-TASK-3 END */
```
<!-- AP4-TASK-3 END -->

<!-- AP4-TASK-3 START -->
**Old code:**
```javascript
    const lockRow = buildLockRow();
    const lockDelayPanel = buildLockDelayPanel();
    // TASK-4 END (Lock Toggle — row build)

    const body = el('div', { class: 'pcw-sheet-body' }, [
      soundRow, transcriptRow, accessibilityRow, accessibilityPanel,
      pinRow, pinPanel, // TASK-3 — PIN Protection section
      lockRow, lockDelayPanel, // TASK-4 — Lock Toggle section
      endSessionRow,
    ]);
```
**New code:**
```javascript
    const lockRow = buildLockRow();
    const lockDelayPanel = buildLockDelayPanel();
    // TASK-4 END (Lock Toggle — row build)

    // AP4-TASK-3 START — Chat Language Preference re-picker.
    // Own self-contained section (own row + own expandable sub-panel),
    // appended after Task 4's Lock Toggle section, per the shared
    // options-panel structural contract: each feature gets its own
    // heading/anchor block and settings row, appended, never interleaved
    // with PIN's or Lock's. Nothing above this line in buildOptionsSheet()
    // was touched or reordered.
    // This changes an ALREADY-OPEN thread's stored language — it calls
    // chat-update-language.php, a new small endpoint, since chat-start.php
    // only ever runs from the pre-chat gate before a thread/session exists.
    const languageRow = el('div', { class: 'pcw-option-row pcw-option-clickable' }, [
      el('div', { class: 'pcw-option-icon' }, el('i', { class: 'fas fa-language' })),
      el('div', { class: 'pcw-option-label' }, 'Chat language'),
      el('i', { class: 'fas fa-chevron-down pcw-option-chevron' }),
    ]);
    const languagePanel = el('div', { class: 'pcw-accessibility-panel ap4-lang-panel', style: 'flex-direction:column;align-items:stretch;gap:10px;' });

    let optionsSelectedLanguage = state.preferredLanguage || resolveDefaultLanguage();
    const optLangDropdownWrap = el('div', { class: 'pcw-dropdown' });
    const optLangDropdownTrigger = el('button', { type: 'button', class: 'pcw-dropdown-trigger has-value' }, [
      el('span', { class: 'pcw-dropdown-label' }, SUPPORTED_LANGUAGES[optionsSelectedLanguage] || SUPPORTED_LANGUAGES[DEFAULT_LANGUAGE]),
      el('i', { class: 'fas fa-chevron-down pcw-dropdown-chevron' }),
    ]);
    const optLangDropdownList = el('div', { class: 'pcw-dropdown-list' });
    const langSavedMsg = el('div', { class: 'pcw-field-hint', style: 'display:none;color:#10b981;' }, 'Language updated.');
    const langErrorMsg = el('div', { class: 'pcw-field-hint', style: 'display:none;color:#ef4444;' }, 'Could not update language. Please try again.');

    Object.keys(SUPPORTED_LANGUAGES).forEach(code => {
      const nativeName = SUPPORTED_LANGUAGES[code];
      const item = el('div', {
        class: 'pcw-dropdown-item' + (code === optionsSelectedLanguage ? ' selected' : ''),
        'data-value': code,
      }, [
        el('span', { class: 'pcw-dropdown-item-label' }, nativeName),
        el('span', { class: 'pcw-dropdown-item-radio' }),
      ]);
      item.addEventListener('click', async () => {
        optionsSelectedLanguage = code;
        localStorage.setItem(LANGUAGE_STORAGE_KEY, code);
        optLangDropdownTrigger.querySelector('.pcw-dropdown-label').textContent = nativeName;
        optLangDropdownList.querySelectorAll('.pcw-dropdown-item').forEach(i => {
          i.classList.toggle('selected', i.dataset.value === code);
        });
        closeOptLangDropdown();
        langSavedMsg.style.display = 'none';
        langErrorMsg.style.display = 'none';

        // Persist immediately to the DB against this thread — language must
        // survive as a permanent record, not just live in this browser.
        // apiPost() (see its own TASK-1 comment above) auto-attaches
        // state.sessionToken to this call — no manual token handling needed
        // here, same as pinManageCall() above relies on the same mechanism.
        if (state.chatId) {
          try {
            const json = await apiPost('chat-update-language.php', { chatId: state.chatId, preferredLanguage: code });
            if (json.success) {
              state.preferredLanguage = code;
              langSavedMsg.style.display = 'block';
              dlog('chat language updated to ' + code);
            } else {
              langErrorMsg.style.display = 'block';
              dlog('chat language update failed: ' + (json.error || 'unknown'), 'error');
            }
          } catch (e) {
            langErrorMsg.style.display = 'block';
            dlog('chat language update network error: ' + e.message, 'error');
          }
        } else {
          // No thread open yet (options panel reachable before pre-chat gate
          // in some entry points) — nothing to persist to yet; the pre-chat
          // gate will pick up this same LANGUAGE_STORAGE_KEY value next.
          langSavedMsg.style.display = 'block';
        }
      });
      optLangDropdownList.appendChild(item);
    });

    let optLangDropdownOpen = false;
    function openOptLangDropdown() {
      optLangDropdownOpen = true;
      optLangDropdownList.classList.add('open');
      optLangDropdownTrigger.classList.add('open');
    }
    function closeOptLangDropdown() {
      optLangDropdownOpen = false;
      optLangDropdownList.classList.remove('open');
      optLangDropdownTrigger.classList.remove('open');
    }
    optLangDropdownTrigger.addEventListener('click', (e) => {
      e.stopPropagation();
      optLangDropdownOpen ? closeOptLangDropdown() : openOptLangDropdown();
    });
    document.addEventListener('click', closeOptLangDropdown);

    optLangDropdownWrap.appendChild(optLangDropdownTrigger);
    optLangDropdownWrap.appendChild(optLangDropdownList);

    // Notice text — directly under the picker, exact verbatim wording
    // required by the task, never rewritten or paraphrased.
    const langNotice = el('div', { class: 'pcw-field-hint' },
      'We always try our absolute best to provide ease of communications to our user\'s of all nationality, ' +
      'however on some occasions during peak hours we might have mostly English speaking customer care on standby ' +
      'whom might be able to chat with your selected language only through text(via our auto-translate) and not verbally.'
    );

    languagePanel.appendChild(optLangDropdownWrap);
    languagePanel.appendChild(langSavedMsg);
    languagePanel.appendChild(langErrorMsg);
    languagePanel.appendChild(langNotice);

    languageRow.addEventListener('click', () => {
      const isOpen = languagePanel.classList.toggle('open');
      languageRow.classList.toggle('open', isOpen);
    });
    // AP4-TASK-3 END

    const body = el('div', { class: 'pcw-sheet-body' }, [
      soundRow, transcriptRow, accessibilityRow, accessibilityPanel,
      pinRow, pinPanel, // TASK-3 — PIN Protection section
      lockRow, lockDelayPanel, // TASK-4 — Lock Toggle section
      languageRow, languagePanel, // AP4-TASK-3 — Chat Language Preference section
      endSessionRow,
    ]);
```
<!-- AP4-TASK-3 END -->

<!-- AP4-TASK-3 START -->
**Old code:**
```javascript
    ]);
    let pinWarningAcknowledged = false;
    const pinField = el('div', { class: 'pcw-field' }, [pinToggle, pinPanel, pinWarning]);
    // TASK-3 END

    const errorMsg = el('div', { class: 'pcw-error-msg' });
```
**New code:**
```javascript
    ]);
    let pinWarningAcknowledged = false;
    const pinField = el('div', { class: 'pcw-field' }, [pinToggle, pinPanel, pinWarning]);
    // TASK-3 END

    // AP4-TASK-3 START — Chat Language Preference dropdown. Exact same
    // CUSTOM_DROPDOWN_DESIGN_DOCUMENTATION pattern as the reason dropdown
    // above (hidden input + custom div UI), reused rather than rebuilt, per
    // the "never recreate a feature from scratch" rule. Pre-selected via
    // resolveDefaultLanguage() (this browser's last choice -> sitewide
    // Settings Widget preference -> browser language -> configured default),
    // but the customer can still open the list and pick a different one.
    // Placed after PIN's section, before the error message — appended
    // alongside PIN's block per the shared-file merge instruction, without
    // touching or reordering anything PIN added above it.
    let selectedLanguage = resolveDefaultLanguage();
    const languageInput = el('input', { type: 'hidden', name: 'preferredLanguage', value: selectedLanguage });
    const langDropdownWrap = el('div', { class: 'pcw-dropdown' });
    const langDropdownTrigger = el('button', { type: 'button', class: 'pcw-dropdown-trigger has-value' }, [
      el('span', { class: 'pcw-dropdown-label' }, SUPPORTED_LANGUAGES[selectedLanguage] || SUPPORTED_LANGUAGES[DEFAULT_LANGUAGE]),
      el('i', { class: 'fas fa-chevron-down pcw-dropdown-chevron' }),
    ]);
    const langDropdownList = el('div', { class: 'pcw-dropdown-list' });

    Object.keys(SUPPORTED_LANGUAGES).forEach(code => {
      const nativeName = SUPPORTED_LANGUAGES[code];
      const item = el('div', {
        class: 'pcw-dropdown-item' + (code === selectedLanguage ? ' selected' : ''),
        'data-value': code,
      }, [
        el('span', { class: 'pcw-dropdown-item-label' }, nativeName), // native name only — never the English name
        el('span', { class: 'pcw-dropdown-item-radio' }),
      ]);
      item.addEventListener('click', () => {
        selectedLanguage = code;
        languageInput.value = code;
        localStorage.setItem(LANGUAGE_STORAGE_KEY, code); // remember this browser's explicit choice for next time
        langDropdownTrigger.querySelector('.pcw-dropdown-label').textContent = nativeName;
        langDropdownList.querySelectorAll('.pcw-dropdown-item').forEach(i => {
          i.classList.toggle('selected', i.dataset.value === code);
        });
        closeLangDropdown();
      });
      langDropdownList.appendChild(item);
    });

    let langDropdownOpen = false;
    function openLangDropdown() {
      langDropdownOpen = true;
      langDropdownList.classList.add('open');
      langDropdownTrigger.classList.add('open');
    }
    function closeLangDropdown() {
      langDropdownOpen = false;
      langDropdownList.classList.remove('open');
      langDropdownTrigger.classList.remove('open');
    }
    langDropdownTrigger.addEventListener('click', (e) => {
      e.stopPropagation();
      langDropdownOpen ? closeLangDropdown() : openLangDropdown();
    });
    document.addEventListener('click', closeLangDropdown);

    langDropdownWrap.appendChild(languageInput);
    langDropdownWrap.appendChild(langDropdownTrigger);
    langDropdownWrap.appendChild(langDropdownList);

    const languageField = el('div', { class: 'pcw-field' }, [
      el('label', {}, 'Preferred language'),
      langDropdownWrap,
      // Directly-under-the-picker, verbatim required text — not to be
      // reworded, per the task's exact-text directive. The sentence before
      // it (also not boilerplate — required by the task brief) tells the
      // customer two specific things: (1) this is changeable later from the
      // options panel, which is also where PIN Protection (Admin-Project
      // #1's Task 3) and Lock Toggle (Admin-Project #1's Task 4) live as
      // their own sections, and (2) a change made there also updates what
      // the admin side sees — both were explicitly required, not just the
      // "changeable later" half.
      el('div', { class: 'pcw-field-hint' },
        'You can change your language anytime from the chat options panel, and doing so will also update what our team sees on their end. ' +
        'We always try our absolute best to provide ease of communications to our user\'s of all nationality, ' +
        'however on some occasions during peak hours we might have mostly English speaking customer care on standby ' +
        'whom might be able to chat with your selected language only through text(via our auto-translate) and not verbally.'
      ),
    ]);
    // AP4-TASK-3 END

    const errorMsg = el('div', { class: 'pcw-error-msg' });
```
<!-- AP4-TASK-3 END -->

<!-- AP4-TASK-3 START -->
**Old code:**
```javascript
    // TASK-3 START
    form.appendChild(pinField);
    // TASK-3 END
    form.appendChild(errorMsg);
```
**New code:**
```javascript
    // TASK-3 START
    form.appendChild(pinField);
    // TASK-3 END
    form.appendChild(languageField); // AP4-TASK-3
    form.appendChild(errorMsg);
```
<!-- AP4-TASK-3 END -->

<!-- AP4-TASK-3 START -->
**Old code:**
```javascript
        chatId: fd.get('chatId').trim(),
        /* TASK-3 START */
        pin: pinVal,
        /* TASK-3 END */
      };

      // Clean up the outside-click listener once form is submitted
      document.removeEventListener('click', closeDropdown);
```
**New code:**
```javascript
        chatId: fd.get('chatId').trim(),
        /* TASK-3 START */
        pin: pinVal,
        /* TASK-3 END */
        preferredLanguage: selectedLanguage, // AP4-TASK-3 — tracked value, same reasoning as selectedReason above
      };

      // Clean up the outside-click listeners once form is submitted
      document.removeEventListener('click', closeDropdown);
      document.removeEventListener('click', closeLangDropdown); // AP4-TASK-3
```
<!-- AP4-TASK-3 END -->

<!-- AP4-TASK-3 START -->
**Old code:**
```javascript
        if (json.success) {
          // TASK-9 START
          // TASK-9c: true here — this is the fresh pre-chat submission
          // path, the one moment the entrance animation should play.
          /* TASK-1 START — pass through the sessionToken chat-start.php's
             response now includes on every success path. */
          openLiveThread(json.chatId, json.fullName, json.email, json.status, undefined, true, json.sessionToken);
          /* TASK-1 END */
          // TASK-9 END
```
**New code:**
```javascript
        if (json.success) {
          // TASK-9 START
          // TASK-9c: true here — this is the fresh pre-chat submission
          // path, the one moment the entrance animation should play.
          /* TASK-1 START — pass through the sessionToken chat-start.php's
             response now includes on every success path. */
          // AP4-TASK-3: keep widget state in sync with whatever the server
          // actually stored (falls back to what we sent if the server, for
          // some reason, didn't echo it — never leave this undefined).
          state.preferredLanguage = json.preferredLanguage || selectedLanguage;
          openLiveThread(json.chatId, json.fullName, json.email, json.status, undefined, true, json.sessionToken);
          /* TASK-1 END */
          // TASK-9 END
```
<!-- AP4-TASK-3 END -->

<!-- AP4-TASK-3 START -->
**Old code:**
```javascript
      pcwRenderAuthGate(gateContainer, {
        tier: 'pin',
        principalId: payload.chatId || gateJson.chatId,
        verify: (pin) => apiPost('chat-start.php', Object.assign({}, payload, { pin })),
        onVerifySuccess: (json) => {
          openLiveThread(json.chatId, payload.fullName, payload.email, json.status, undefined, true, json.sessionToken);
        },
```
**New code:**
```javascript
      pcwRenderAuthGate(gateContainer, {
        tier: 'pin',
        principalId: payload.chatId || gateJson.chatId,
        verify: (pin) => apiPost('chat-start.php', Object.assign({}, payload, { pin })),
        onVerifySuccess: (json) => {
          state.preferredLanguage = json.preferredLanguage || payload.preferredLanguage; // AP4-TASK-3
          openLiveThread(json.chatId, payload.fullName, payload.email, json.status, undefined, true, json.sessionToken);
        },
```
<!-- AP4-TASK-3 END -->

### `admin-dashboard.html`  →  live path `admin/admin-dashboard.html`

**Note on naming:** confirmed with the CEO directly — this file's live
path stays `admin/admin-dashboard.html`, matching
`ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s own documented file tree, as an
explicit exception to the general "HTML pages use `index.html` on live
deployment" rule. This is the admin-side dashboard, not a public page.

<!-- AP4-TASK-3 START -->
**Old code:**
```javascript
  selectedChatIds: new Set(), // for bulk archive/delete/unarchive
  activeThreadId: null,
```
**New code:**
```javascript
  selectedChatIds: new Set(), // for bulk archive/delete/unarchive
  activeThreadId: null,
  // AP4-TASK-3: full thread row (incl. preferred_chat_language) for the
  // currently open thread, kept in sync by loadThreadMeta() below. Without
  // this, preferred_chat_language only ever existed as text rendered inside
  // buildAdminBanner()'s HTML — unreadable by any other code, including
  // Task 1a/1b's transcription features, which the Shared Data Contract
  // requires be able to display/log this value alongside a transcript.
  activeThreadMeta: null,
```
<!-- AP4-TASK-3 END -->

<!-- AP4-TASK-3 START -->
**Old code:**
```javascript
async function openThreadDetail(chatId) {
  chatAdmin.activeThreadId = chatId;
  chatAdmin.lastMsgId = 0;
```
**New code:**
```javascript
async function openThreadDetail(chatId) {
  chatAdmin.activeThreadId = chatId;
  chatAdmin.activeThreadMeta = null; // AP4-TASK-3: clear stale meta from whatever thread was open before; loadThreadMeta() repopulates it for the new chatId
  chatAdmin.lastMsgId = 0;
```
<!-- AP4-TASK-3 END -->

<!-- AP4-TASK-3 START -->
**Old code:**
```javascript
function buildAdminBanner(chatId, thread) {
```
**New code:**
```javascript
// AP4-TASK-3 START — Chat Language Preference. Mirrors chat-config.php's
// CHAT_SUPPORTED_LANGUAGES exactly (same keys/native-name values) so the
// admin dashboard shows agents the same native-name label the customer saw
// when picking it, rather than an English translation of their choice.
const AP4_CHAT_LANGUAGES = {
  en: 'English',
  es: 'Español',
  fr: 'Français',
  de: 'Deutsch',
  pt: 'Português',
  it: 'Italiano',
  'zh-Hans': '中文（简体）',
  ja: '日本語',
  ko: '한국어',
  ar: 'العربية',
  hi: 'हिन्दी',
  vi: 'Tiếng Việt',
  tl: 'Filipino',
  ru: 'Русский',
};
// AP4-TASK-3 END

function buildAdminBanner(chatId, thread) {
```
<!-- AP4-TASK-3 END -->

<!-- AP4-TASK-3 START -->
**Old code:**
```javascript
    <div style="font-size:11px;color:#a89c8a;margin-top:4px;">
      Chat ID: ${escapeHtml(chatId)} &middot; Reason: ${escapeHtml(thread.reason || '—')}
    </div>`;
```
**New code:**
```javascript
    <div style="font-size:11px;color:#a89c8a;margin-top:4px;">
      Chat ID: ${escapeHtml(chatId)} &middot; Reason: ${escapeHtml(thread.reason || '—')}
      <!-- AP4-TASK-3 START — displays the customer's selected chat language
           to agents, native name (never the English name), same &middot;
           -separated meta-line style as Chat ID / Reason above. Falls back
           to the language code itself if a customer's stored value somehow
           isn't in the current supported-languages map (e.g. a language
           later removed from CHAT_SUPPORTED_LANGUAGES), rather than
           showing nothing for an agent trying to identify the customer's
           preference. -->
      &middot; Language: ${escapeHtml(AP4_CHAT_LANGUAGES[thread.preferred_chat_language] || thread.preferred_chat_language || '—')}
      <!-- AP4-TASK-3 END -->
    </div>`;
```
<!-- AP4-TASK-3 END -->

<!-- AP4-TASK-3 START -->
**Old code:**
```javascript
    const json = await r.json();
    const thread = (json.results || [])[0];
    if (thread) {
```
**New code:**
```javascript
    const json = await r.json();
    const thread = (json.results || [])[0];
    if (thread) {
      // AP4-TASK-3: keep the raw thread row (preferred_chat_language
      // included) available on shared state, not just rendered into the
      // banner's HTML — see the activeThreadMeta comment above for why.
      chatAdmin.activeThreadMeta = thread;
```
<!-- AP4-TASK-3 END -->

## New files

- `admin/chat-update-language.php` — New file — in the zip. Small,
  single-purpose endpoint that lets a customer change their
  `preferred_chat_language` from within an already-open thread (the
  options panel's re-picker), since `chat-start.php` only ever runs from
  the pre-chat gate before a thread's session is live. Hardened to call
  `chatRequireSessionToken()` per Admin-Project #1 Task 1's contract, since
  it writes to an existing thread.

### ⚠️ UNDOCUMENTED PATH — NEW FOLDER REQUIRED ON LIVE SERVER: `admin/includes/`

**This is not a file this task created, but this task's new file depends
on it, so it is flagged heavily here per the delivery guide's instruction
to call out any path not found in either project summary doc.**

`admin/chat-update-language.php` (above) requires
`admin/includes/chat-auth.php` at runtime:

```php
require_once __DIR__ . '/includes/chat-auth.php';
```

**`admin/includes/` does not appear anywhere in `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s
documented "File Structure on Server" tree.** That tree lists every v2.0
chat PHP file as living flat under `admin/` (`chat-config.php`,
`chat-start.php`, `chat-admin-list.php`, and so on) — no `includes/`
subfolder is shown or mentioned anywhere in that document.

This folder and file were introduced by Admin-Project #1's Task 1
(session tokens) and Task 3 (PIN), which this task merged against (see
"The Real Merge" section above) — **not by this task**. This task's own
new file was written to match the real, current `chat-start.php` and
`chat-widget.js` it was handed, which already required and used this path
before this task began. But since neither project summary doc has been
updated to reflect it, and the delivery guide asks that any such gap be
flagged rather than silently assumed correct:

- **Folder to create on the live server, if it does not already exist:** `admin/includes/`
- **File expected inside it:** `admin/includes/chat-auth.php`
- This task did not create, edit, or include that file in its zip — it
  was never part of this task's file list, and Scope Lock applied. It is
  flagged here purely so the integrator knows `admin/chat-update-language.php`
  (and likely `admin/chat-start.php` and `admin/chat-widget.js`, which
  reference the same path for Task 1/PIN's own code) will fail at runtime
  if `admin/includes/chat-auth.php` is not actually present at that path
  on the live server.
- Whoever owns Admin-Project #1's Task 1/Task 3 delivery should confirm
  `includes/chat-auth.php` is included in their own zip at that exact
  path, since it was never delivered as part of this task's work.

## Bugs Found Outside Task Scope

- `chat-poll.php` (not part of this task's file list, not modified):
  on a page reload, `resumeStoredChat()` in `chat-widget.js` rebuilds
  widget state entirely from `chat-poll.php`'s response, which — based on
  everything available to this task — does not appear to return
  `preferred_chat_language`. This task's options panel already falls back
  safely (`state.preferredLanguage || resolveDefaultLanguage()`), so there
  is no broken behavior, but the options panel may show a re-detected
  value rather than the authoritative DB value immediately after a reload,
  until the customer reopens the options panel or `chat-poll.php` is
  updated to include the field. Not fixed directly — outside this task's
  file list.
- No other out-of-scope issues found. `chat-admin-list.php` was reviewed
  (every query uses `SELECT t.*`/`t.*`) and needs no changes — this is a
  confirmation, not a bug.

## Self-Check Confirmation

- [x] Touched only the files listed in Files Changed above, plus the one
      new file — no scope expansion
- [x] Every code block carries `AP4-TASK-3` anchor comments (HTML blocks
      use the `<!-- -->` form; JS/PHP blocks use `//` or `/* */` to match
      the surrounding file's own style)
- [x] Every "Old code" snippet was copied character for character from the
      real original file (the version as supplied, including
      Admin-Project #1's Task 1/Task 3/Task 4 work already present in
      `chat-start.php` and `chat-widget.js`) — verified programmatically,
      not by eye, that each snippet appears exactly once in its file
- [x] The one non-anchored line changed anywhere (a comment in
      `chat-widget.js`, "listener" → "listeners") is shown explicitly in
      its own block with the reason it changed, rather than hidden inside
      a larger diff
- [x] `admin/chat-update-language.php` is listed in Files Changed as "New"
      and appears in `Task-3-NewFiles.zip` at that exact path; the zip
      contains nothing else
- [x] Ran `unzip -l Task-3-NewFiles.zip` and confirmed it contains exactly
      one file, `admin/chat-update-language.php`, matching the New Files
      entry above
- [x] Scanned the zip's contents for secrets/config/credentials — found
      none; the file only references `chat-config.php` and
      `includes/chat-auth.php` via `require_once`, holding no credentials
      of its own
- [x] Confirmed nothing in the zip touches `config.php`, `pet.json`,
      `.env`, or anything under `applications/` or `uploads/` — the zip
      contains only the one new admin PHP file
- [x] JS syntax validated (`new Function(source)`) on the final
      `chat-widget.js` — passes. PHP files checked for balanced
      braces/parens (no PHP interpreter available in this environment for
      a full lint)
- [x] Flagged `admin/includes/chat-auth.php` as an undocumented path this
      task's new file depends on but did not create — see "⚠️ UNDOCUMENTED
      PATH" under New Files above
- [x] Confirmed `admin-dashboard.html`'s live path with the CEO directly —
      stays `admin/admin-dashboard.html`, an explicit exception to the
      `index.html` rule for this admin-side file
- [x] Zip is named `admin-project-4-task-3.zip` and contains this
      completion file together with the new file, per the delivery guide's
      single-combined-zip requirement
- [x] No PIN, Lock, or Task 1 code was altered, reordered, or removed —
      verified by diff against the real original files for both shared
      files (`chat-start.php`, `chat-widget.js`)

## SECOND VERIFICATION ROUND

Re-read both project summary docs and this task's full tracker section
word for word, then checked the delivered work against the actual written
requirements. Found four real issues — detailed below, with the two
functional fixes already folded into the Code Changes blocks above (this
section documents what was found and why; it does not repeat the full
snippets a second time, since they are already shown above in the exact
format this hand-in requires).

### Issue 1 (functional gap, fixed): required "also updates what the admin side sees" sentence was missing

The task brief requires the pre-chat notice to tell the customer both
that they can change their language later from the options panel, **and**
that doing so also updates what the admin side sees. The delivered notice
only said the first half. Fixed in `chat-widget.js` — see the Code
Changes block above for `languageField`'s hint text (the sentence now
reads "...and doing so will also update what our team sees on their
end.").

### Issue 2 (functional gap, fixed): `preferred_chat_language` was unreachable by other code once rendered

The Shared Data Contract requires `preferred_chat_language` be available
for Task 1a/1b's transcription features to display/log "alongside a
transcript." As originally delivered, the value only ever existed as
rendered text inside `buildAdminBanner()`'s HTML — unreadable by any other
code. Fixed in `admin-dashboard.html` by adding `chatAdmin.activeThreadMeta`
(the full thread row, kept in sync on every thread open) — see the three
`admin-dashboard.html` Code Changes blocks above for the exact additions.
Task 1a/1b's code can now read
`chatAdmin.activeThreadMeta.preferred_chat_language` directly.

### Issue 3 (documentation gap, fixed): Recommended Enhancement decisions were never stated plainly

The task brief asks that the `zh-Hant`/bare-`zh` handling decision be
stated plainly, not buried in code. Stating it here: all three
(`zh-Hant`, `zh-TW`, bare `zh`) resolve to `zh-Hans` in
`resolveDefaultLanguage()`, since Traditional Chinese is not a separately
supported value per the Shared Data Contract — this only affects
automatic browser-language detection; the dropdown itself only ever shows
and stores real `CHAT_SUPPORTED_LANGUAGES` keys.

The "(detected)" indicator enhancement was considered and not implemented:
the picker is always open/editable before submission, so the customer
already sees and can change whatever was pre-selected before ever
submitting, which already serves the enhancement's stated purpose.

### Issue 4 (process gap, flagged, unresolved): translation-tooling access pattern was never confirmed with the CEO

The task brief asks this task to confirm with the CEO how the separate
translation project's tooling should read `preferred_chat_language`, if
that access pattern isn't already documented. It is not documented in
`ADMIN_DASHBOARD_PROJECT_SUMMARY.md` (that doc predates this task
entirely). This still needs a CEO answer: does that tooling read the value
from `chat_threads` directly, from `chat-admin-list.php`'s existing JSON
(already includes it via `SELECT t.*`), from the new
`chatAdmin.activeThreadMeta` (Issue 2 above), or some other access point
this task hasn't been told about. Flagging again here since it was never
actually answered.

### Re-verified, no issues found

- Confirmed the Database Schema table in `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`
  predates this task (and also predates PIN/Lock), so it not listing
  `preferred_chat_language` is expected staleness in a read-only reference
  document, not a bug in this task's work.
- Re-confirmed the "can a customer chat before email" finding — still
  holds.
- Re-confirmed `chat-admin-list.php` still needs no edits.

