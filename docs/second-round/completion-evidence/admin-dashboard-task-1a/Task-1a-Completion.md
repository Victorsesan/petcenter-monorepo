# Task 1a Completion

## ⚠ PENDING / OUTSTANDING — READ BEFORE INTEGRATING
This task's code changes are complete and verified (see Testing Performed and both verification rounds below), but three things are not yet done and are called out here together so they aren't missed:

1. **`GROQ_API_KEY_TASK1A` is still a placeholder.** It's set to the literal string `'REPLACE_WITH_YOUR_GROQ_API_KEY'` at the top of `chat-admin-reply.php`. Until Victor replaces it with a real key from a free console.groq.com account, the feature silently no-ops — voice messages send normally, they just never get a transcript. Not a bug, but nothing will visibly work until this is done.
2. **No live PHP/MySQL/Groq test was possible in this sandbox.** No PHP CLI, no database, no real API key were available here, so `php -l` and an actual end-to-end send-and-transcribe could not be run. Structural checks (brace/paren balance, `node --check` on the JS, Playwright DOM tests with stubbed message data) all passed — see Testing Performed — but a real test with a live key is still needed before calling this fully verified in production.
3. **New files / new folders: none.** This task only changed existing files (see Files Changed and New Files below) — there is nothing new to create a folder for, and no zip-worthy new file exists for this task.

Also see Bugs Found Outside Task Scope (a pre-existing dark-theme contrast note, not something this task caused) and the SECOND VERIFICATION ROUND section at the bottom, which documents a real bug this task found and fixed in its own first pass (a deprecated Groq model ID) plus two gaps closed (copy-transcript control, readable language name).

---

## Task Number
**Task 1a** (of the voice-message-transcription epic split into Task 1a and Task 1b in `ADMIN_PROJECTS_2_3_4_TASK_ASSIGNMENTS.md`)

## What Was Required
Build the transcription layer for the **admin → customer** direction of voice messages only: when an admin sends a voice message, both the customer and the admin should be able to click to reveal an English text transcript of it. This is a fallback for reading, not a translator — no auto-translation and no third-party translation API were in scope; the admin still has to manually translate and send the translation as a normal follow-up text message. The customer's saved `preferred_chat_language` (Task 3's field, owned by a different worker) needed to be shown alongside the transcript, specifically on the admin's side, so whoever does that manual translation knows the target language. Tool selection had to be a genuinely free (not trial-credit) speech-to-text service, confirmed current via web search, with its choice and terms documented here — including whether it's English-only-optimized, since that matters for Task 1b's own tool choice.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `chat-setup.php` | `admin/chat-setup.php` | Changed | Likely shared with Task 1b (its own transcript columns for the opposite direction would also land here) |
| `chat-migrate.php` | `admin/chat-migrate.php` | Changed | Likely shared with Task 1b, same reason |
| `chat-admin-reply.php` | `admin/chat-admin-reply.php` | Changed | Shared with Task 1b per the assignment doc's Sequential File Ownership Chain |
| `admin-dashboard.html` | `admin/index.html` | Changed | Shared with Task 1b per the assignment doc's Sequential File Ownership Chain |
| `chat-widget.js` | `admin/chat-widget.js` | Changed | Shared with Task 1b per the assignment doc's Sequential File Ownership Chain |

Live paths taken from WORKER-DELIVERY-GUIDE.md's source-name → live-path mapping table (Section 3): `admin-dashboard.html` → `admin/index.html` explicitly; the other four fall under that table's "Any admin PHP/JS (`chat-widget.js`, `stats.php`, `publish-pet.php`, `chat-*.php`, and so on) → `admin/` + the same file name" rule — `chat-widget.js` is named there explicitly, and `chat-setup.php` / `chat-migrate.php` / `chat-admin-reply.php` match the `chat-*.php` pattern in that same rule.

I did not find any anchor comments from a Task 1b pass already present in these files, so I'm treating this submission as first in the chain for this file set. Whoever picks up Task 1b should pull this version, not the original untouched files.

---

## Code Changes

### 1. `chat-setup.php`
Added two nullable columns to the `chat_messages` fresh-install schema so a brand-new database has somewhere to store a transcript and a confidence heuristic. Nullable so no other feature or query that already assumes `chat_messages`'s shape breaks.

**Old code:**
```php
            original_body TEXT NULL,
            deleted_at    TIMESTAMP NULL,
            created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (thread_id) REFERENCES chat_threads(id) ON DELETE CASCADE,
```

**New code:**
```php
            original_body TEXT NULL,
            deleted_at    TIMESTAMP NULL,
            created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            /* TASK-1a START: speech-to-text transcript for admin-sent voice
               messages (English only, this direction). transcript_confidence
               is a 0.000-1.000 heuristic derived from the STT provider's
               per-segment probabilities, NULL when transcription hasn't run
               or failed. Both are NULL until this direction's transcription
               step succeeds, so they never block a normal audio send. */
            transcript              TEXT NULL,
            transcript_confidence   DECIMAL(4,3) NULL,
            /* TASK-1a END */
            FOREIGN KEY (thread_id) REFERENCES chat_threads(id) ON DELETE CASCADE,
```

---

### 2. `chat-migrate.php`
Added the matching migration entries for existing (non-fresh) databases — the exact same two columns, added the same way the file's existing `archived` and other columns already were (`ADD COLUMN IF NOT EXISTS`, so re-running it is safe).

**Old code:**
```php
    "Widen chat_typing.who ENUM to include recording-ping values (Task 8b)" =>
        "ALTER TABLE chat_typing MODIFY who ENUM('user','admin','user_recording','admin_recording') NOT NULL",
    // BUGFIX-1 END
];
```

**New code:**
```php
    "Widen chat_typing.who ENUM to include recording-ping values (Task 8b)" =>
        "ALTER TABLE chat_typing MODIFY who ENUM('user','admin','user_recording','admin_recording') NOT NULL",
    // BUGFIX-1 END

    // TASK-1a START — admin-to-customer voice message transcription needs
    // somewhere to store the resulting text and a confidence heuristic on
    // existing (non-fresh) databases. See chat-setup.php for the matching
    // fresh-install columns. Both are nullable so existing rows are
    // unaffected and normal audio sends never break if transcription
    // hasn't run yet.
    "Add transcript column to chat_messages (Task 1a)" =>
        "ALTER TABLE chat_messages ADD COLUMN IF NOT EXISTS transcript TEXT NULL",

    "Add transcript_confidence column to chat_messages (Task 1a)" =>
        "ALTER TABLE chat_messages ADD COLUMN IF NOT EXISTS transcript_confidence DECIMAL(4,3) NULL",
    // TASK-1a END
];
```

---

### 3. `chat-admin-reply.php`
Three changes in this file:

**(a) Added the transcription helper and its API key constant**, scoped locally to this file (see "Bugs Found / Notes" at the bottom for why it isn't in `chat-config.php`).

**Old code:**
```php
require_once __DIR__ . '/chat-config.php';

chatRequireAdmin();
```

**New code:**
```php
require_once __DIR__ . '/chat-config.php';

/* TASK-1a START: Groq Whisper (distil-whisper-large-v3-en) speech-to-text
   helper for admin-sent voice messages, English only, since Pet Center's
   own admins speak/write English only in this direction (per
   ADMIN_PROJECTS_2_3_4_TASK_ASSIGNMENTS.md, Task 1a). Tool choice, the
   free-tier terms, and the confidence-heuristic reasoning below are all
   documented in full in Task-1a-Completion.md. This constant + helper are
   scoped locally to chat-admin-reply.php rather than added to
   chat-config.php: chat-config.php is loaded by every chat PHP file and
   was not part of this task's assigned file list, so under Scope Lock a
   shared constant there is a call for the CEO to make, not this task —
   see the completion file for a suggested follow-up if Task 1b also
   needs Groq access from chat-send.php. Never throws: any failure just
   returns [null, null] so a normal audio send is never blocked by a
   transcription problem. */
define('GROQ_API_KEY_TASK1A', 'REPLACE_WITH_YOUR_GROQ_API_KEY');

function chatTranscribeAdminAudioTask1a($absoluteAudioPath) {
    if (GROQ_API_KEY_TASK1A === 'REPLACE_WITH_YOUR_GROQ_API_KEY') {
        return [null, null]; // Feature not configured yet — silent no-op.
    }
    if (!is_file($absoluteAudioPath)) {
        return [null, null];
    }
    try {
        $ch = curl_init('https://api.groq.com/openai/v1/audio/transcriptions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . GROQ_API_KEY_TASK1A],
            CURLOPT_POSTFIELDS     => [
                'file'            => new CURLFile($absoluteAudioPath),
                'model'           => 'distil-whisper-large-v3-en',
                'language'        => 'en',
                'response_format' => 'verbose_json',
                'temperature'     => '0',
            ],
        ]);
        $raw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $httpCode !== 200) {
            error_log('Task 1a Groq transcription failed: HTTP ' . $httpCode . ' — ' . $raw);
            return [null, null];
        }

        $data = json_decode($raw, true);
        $text = trim($data['text'] ?? '');
        if ($text === '') {
            return [null, null];
        }

        // Whisper does not return one official confidence score. Derive a
        // 0.000-1.000 heuristic from each segment's avg_logprob (a
        // log-probability, always <= 0) via exp(), then average across
        // segments. Full reasoning in Task-1a-Completion.md.
        $confidence = null;
        if (!empty($data['segments']) && is_array($data['segments'])) {
            $probs = [];
            foreach ($data['segments'] as $seg) {
                if (isset($seg['avg_logprob'])) {
                    $probs[] = exp((float)$seg['avg_logprob']);
                }
            }
            if (!empty($probs)) {
                $confidence = round(array_sum($probs) / count($probs), 3);
                $confidence = max(0.0, min(1.0, $confidence));
            }
        }

        return [$text, $confidence];
    } catch (Throwable $e) {
        error_log('Task 1a Groq transcription exception: ' . $e->getMessage());
        return [null, null];
    }
}
/* TASK-1a END */

chatRequireAdmin();
```

**(b) Triggered transcription right after an audio file lands in its final location, inside the existing send transaction:**

**Old code:**
```php
    $insertedIds = [];

    if ($text !== '') {
        $insertStmt->execute([$thread['id'], $adminId, 'text', $text, null, $replyToId, $groupId]);
        $insertedIds[] = $db->lastInsertId();
    }

    foreach ($resolved as $item) {
        $kind     = $item['kind'];
        $tempPath = $item['path'];
        $ext = pathinfo($tempPath, PATHINFO_EXTENSION);
        $finalName = $kind . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $finalPath = $mediaDir . $finalName;

        if (!chatMoveStagedFile($tempPath, $finalPath)) {
            throw new Exception("Failed to move staged $kind file into thread folder");
        }
        @unlink(preg_replace('/\.[a-zA-Z0-9]+$/', '.json', $tempPath));

        $relativePath = 'chat-media/' . $chatId . '/' . $finalName;
        $insertStmt->execute([$thread['id'], $adminId, $kind, null, $relativePath, $replyToId, $groupId]);
        $insertedIds[] = $db->lastInsertId();
    }
```

**New code:**
```php
    $insertedIds = [];
    $transcriptsForResponse = []; // TASK-1a: messageId => ['transcript' => ..., 'confidence' => ...]

    if ($text !== '') {
        $insertStmt->execute([$thread['id'], $adminId, 'text', $text, null, $replyToId, $groupId]);
        $insertedIds[] = $db->lastInsertId();
    }

    foreach ($resolved as $item) {
        $kind     = $item['kind'];
        $tempPath = $item['path'];
        $ext = pathinfo($tempPath, PATHINFO_EXTENSION);
        $finalName = $kind . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $finalPath = $mediaDir . $finalName;

        if (!chatMoveStagedFile($tempPath, $finalPath)) {
            throw new Exception("Failed to move staged $kind file into thread folder");
        }
        @unlink(preg_replace('/\.[a-zA-Z0-9]+$/', '.json', $tempPath));

        $relativePath = 'chat-media/' . $chatId . '/' . $finalName;
        $insertStmt->execute([$thread['id'], $adminId, $kind, null, $relativePath, $replyToId, $groupId]);
        $newId = $db->lastInsertId();
        $insertedIds[] = $newId;

        /* TASK-1a START: transcribe the admin's own voice message right
           after the file lands in its final location, so both the admin
           and the customer can see a text fallback (chat-widget.js and
           admin-dashboard.html read the transcript/transcript_confidence
           columns this writes, via the existing m.* wildcard select in
           chat-poll.php / chat-admin-poll.php — neither of those files
           needed to change). Runs synchronously and inside this same
           transaction: Groq's free tier responds in well under a second
           in normal operation, but a slow/stalled network call here would
           hold the transaction open for up to the 30s cURL timeout above.
           That tradeoff (vs. adding background-job infrastructure, which
           is out of this task's scope) is documented in
           Task-1a-Completion.md. Any failure or non-audio kind just skips
           this silently and the message still sends normally. */
        if ($kind === 'audio') {
            [$transcriptText, $transcriptConfidence] = chatTranscribeAdminAudioTask1a($finalPath);
            if ($transcriptText !== null) {
                $db->prepare("UPDATE chat_messages SET transcript = ?, transcript_confidence = ? WHERE id = ?")
                   ->execute([$transcriptText, $transcriptConfidence, $newId]);
                $transcriptsForResponse[$newId] = [
                    'transcript' => $transcriptText,
                    'confidence' => $transcriptConfidence,
                ];
            }
        }
        /* TASK-1a END */
    }
```

**(c) Returned the transcript(s) and the customer's `preferred_chat_language` in the send response:**

**Old code:**
```php
chatRespond([
    'success'    => true,
    'groupId'    => $groupId,
    'messageIds' => $insertedIds,
    'justClaimed' => $justClaimed,
]);
```

**New code:**
```php
chatRespond([
    'success'    => true,
    'groupId'    => $groupId,
    'messageIds' => $insertedIds,
    'justClaimed' => $justClaimed,
    /* TASK-1a: transcript text + confidence for any voice message just
       sent, keyed by message id, so admin-dashboard.html can show them
       immediately without waiting on the next poll cycle. Also passes
       through the customer's preferred_chat_language (Task 3's field —
       $thread was already fetched with SELECT * above, so this needs no
       extra query and will simply start appearing once Task 3's own
       migration adds the column; it is null until then or until the
       customer sets one) so the admin knows which language to manually
       translate this transcript into. */
    'transcripts' => $transcriptsForResponse,
    'threadPreferredLanguage' => $thread['preferred_chat_language'] ?? null,
]);
```

---

### 4. `admin-dashboard.html`
Four changes in this file:

**(a) New state field to cache the customer's language per chat, in `const chatAdmin = {...}`:**

**Old code:**
```js
  // TASK-8 START (8b — admin state additions for outgoing typing/recording pings)
  lastTypingPingAt: 0,      // throttle for the outgoing "admin is typing" ping
  recordingPingTimer: null, // repeating "admin is recording" ping while active
  // TASK-8 END (8b — admin state additions for outgoing typing/recording pings)
};
```

**New code:**
```js
  // TASK-8 START (8b — admin state additions for outgoing typing/recording pings)
  lastTypingPingAt: 0,      // throttle for the outgoing "admin is typing" ping
  recordingPingTimer: null, // repeating "admin is recording" ping while active
  // TASK-8 END (8b — admin state additions for outgoing typing/recording pings)
  // TASK-1a START: chat-admin-reply.php returns the customer's saved
  // preferred_chat_language (Task 3's field) in its send response so this
  // panel can show it next to a voice message's transcript without an
  // extra request. chat_threads has no such column yet client-side (Task
  // 3 not part of this task's scope), so this in-memory cache — cleared
  // on a full page reload, not persisted — is the only place this task
  // can source that value from. See sendAdminMessage() and
  // adminPollOnce() for where this is written to and read from.
  task1aThreadLangByChatId: {},
  // TASK-1a END
};
```

**(b) Stamp the cached language onto messages before rendering, inside `adminPollOnce()` — mirrors the existing `BUGFIX-2` bot-info injection immediately above it in the same function:**

**Old code:**
```js
  /* BUGFIX-2 END */

  // Merge events and messages into one true chronological sequence by
```

**New code:**
```js
  /* BUGFIX-2 END */

  /* TASK-1a START: stamp the cached customer preferred_chat_language (see
     chatAdmin.task1aThreadLangByChatId, populated in sendAdminMessage())
     onto every message of this thread before rendering, exactly mirroring
     the BUGFIX-2 botInfo injection immediately above — renderAdminMessage()
     stays a pure function of the message object either way. Undefined
     when nothing has been sent in this browser session yet or Task 3
     hasn't landed; renderAdminMessage() only shows the tag when present. */
  const task1aLang = chatAdmin.task1aThreadLangByChatId[chatId] || null;
  if (task1aLang && json.messages) {
    json.messages.forEach(m => { m._task1aThreadLang = task1aLang; });
  }
  /* TASK-1a END */

  // Merge events and messages into one true chronological sequence by
```

**(c) Cache the language from the send response, inside `sendAdminMessage()`:**

**Old code:**
```js
    if (json.success) {
      cadPlaySendSound();
      if (!isRealRetry) {
```

**New code:**
```js
    if (json.success) {
      cadPlaySendSound();
      // TASK-1a: cache the customer's preferred_chat_language (Task 3's
      // field, passed through by chat-admin-reply.php) so the transcript
      // shown for a voice message just sent — and any earlier ones in
      // this thread, once re-rendered — can display it. See the
      // chatAdmin.task1aThreadLangByChatId declaration and its read site
      // in adminPollOnce() above.
      if (json.threadPreferredLanguage) {
        chatAdmin.task1aThreadLangByChatId[chatId] = json.threadPreferredLanguage;
      }
      if (!isRealRetry) {
```

**(d) The transcript toggle UI itself, inside `renderAdminMessage()`'s audio branch:**

**Old code:**
```js
    } else {
      const aud = document.createElement('audio');
      aud.src = p; aud.controls = true; aud.style.cssText = 'max-width:220px;height:36px;';
      bubble.appendChild(aud);
    }

    // Admin-only: delete button under their own sent media
```

**New code:**
```js
    } else {
      const aud = document.createElement('audio');
      aud.src = p; aud.controls = true; aud.style.cssText = 'max-width:220px;height:36px;';
      bubble.appendChild(aud);

      /* TASK-1a START: transcript toggle for the admin's own sent voice
         message (admin -> customer direction only; the customer -> admin
         direction is Task 1b's separate scope, even though it renders in
         this same function). msg.transcript / msg.transcript_confidence
         arrive automatically via chat-admin-poll.php's existing `m.*`
         select once chat-admin-reply.php has written them — see that
         file's TASK-1a block — so no poll-file change was needed here.
         Nothing renders when transcript is absent (Groq key still a
         placeholder, or this particular clip failed to transcribe), so an
         audio bubble looks exactly as it did before this task in that
         case. */
      if (isAdmin && msg.transcript) {
        const transcriptToggle = document.createElement('button');
        transcriptToggle.type = 'button';
        transcriptToggle.className = 'cad-transcript-toggle';
        transcriptToggle.textContent = 'View transcript';

        const transcriptBox = document.createElement('div');
        transcriptBox.className = 'cad-transcript-box';

        const transcriptTextEl = document.createElement('div');
        transcriptTextEl.className = 'cad-transcript-text';
        transcriptTextEl.textContent = msg.transcript;
        transcriptBox.appendChild(transcriptTextEl);

        // Low-confidence flag, Warning Amber styling per
        // PETCENTER_DESIGN_SYSTEM_v1_0.md Section 18 (Status & Feedback
        // Systems). The 0.5 cutoff is this task's own judgment call, not
        // a value handed down by the design system or the STT provider —
        // reasoning documented in Task-1a-Completion.md.
        if (typeof msg.transcript_confidence === 'number' && msg.transcript_confidence < 0.5) {
          const lowConfTag = document.createElement('div');
          lowConfTag.className = 'cad-transcript-lowconf-tag';
          lowConfTag.innerHTML = '<i class="fas fa-triangle-exclamation"></i> Low confidence — please verify before translating';
          transcriptBox.appendChild(lowConfTag);
        }

        // Customer's saved language preference (Task 3's field, cached
        // from the send response — see chatAdmin.task1aThreadLangByChatId
        // and the stamping step in adminPollOnce()), shown so whoever
        // translates this transcript knows the target language.
        if (msg._task1aThreadLang) {
          const langTag = document.createElement('div');
          langTag.className = 'cad-transcript-lang-tag';
          langTag.textContent = "Customer's preferred language: " + msg._task1aThreadLang;
          transcriptBox.appendChild(langTag);
        }

        transcriptToggle.addEventListener('click', () => {
          const isShown = transcriptBox.classList.toggle('show');
          transcriptToggle.classList.toggle('active', isShown);
        });

        bubble.appendChild(transcriptToggle);
        bubble.appendChild(transcriptBox);
      }
      /* TASK-1a END */
    }

    // Admin-only: delete button under their own sent media
```

**(e) CSS**, added directly after the existing `.cad-original-reveal-text` rule so the new toggle/box sits next to the pattern it mirrors:

**Old code:**
```css
.cad-original-reveal-text { font-size: 12px; color: #4a4030; font-style: italic; line-height: 1.4; }
```

**New code:**
```css
.cad-original-reveal-text { font-size: 12px; color: #4a4030; font-style: italic; line-height: 1.4; }

/* TASK-1a START: transcript toggle/reveal for admin-sent voice messages.
   Structurally mirrors .cad-edited-tag-btn + .cad-original-reveal directly
   above (same collapse/expand pattern, same transition timing) so this
   reads as one consistent interaction language rather than a one-off.
   Low-confidence tag uses Warning Amber (#f59e0b) per
   PETCENTER_DESIGN_SYSTEM_v1_0.md Section 18 (Status & Feedback Systems —
   Warning Toast), background at ~10% opacity per that section's "soft
   tint" rule for toast/tag backgrounds. */
.cad-transcript-toggle {
  border: none; background: none; cursor: pointer; font-size: 10px; font-style: italic;
  color: #a89c8a; padding: 0; text-decoration: underline; text-underline-offset: 2px;
  transition: color 0.15s ease; display: block; margin-top: 4px;
}
.cad-transcript-toggle:hover, .cad-transcript-toggle.active { color: #ED7624; }

.cad-transcript-box {
  max-height: 0; overflow: hidden; opacity: 0;
  transition: max-height 0.25s ease, opacity 0.2s ease, margin 0.25s ease;
  background: rgba(16,33,75,0.05); border-radius: 8px; margin-top: 0;
}
.cad-transcript-box.show { max-height: 260px; opacity: 1; margin-top: 6px; padding: 8px 10px; }
.cad-transcript-text { font-size: 12px; color: #4a4030; line-height: 1.4; }
.cad-transcript-lowconf-tag {
  display: flex; align-items: center; gap: 5px; font-size: 10.5px; font-weight: 600;
  color: #f59e0b; background: rgba(245,158,11,0.1); border-radius: 6px; padding: 3px 7px; margin-top: 6px;
}
.cad-transcript-lang-tag { font-size: 10.5px; color: #a89c8a; margin-top: 6px; }
/* TASK-1a END */
```

Colors used (`#a89c8a`, `#ED7624`, `#10214b`-family, `#4a4030`) are the same hardcoded values the file's own pre-existing `.cad-edited-tag-btn` / `.cad-original-reveal` / `.cad-media-delete-btn` rules already use — per Warning & Instructions rule 9, this is Section 15's Navy/Gold/Orange palette, not v2.2's unapproved "Lavender Mist." The low-confidence amber (`#f59e0b`) is Section 18's Warning Amber token.

---

### 5. `chat-widget.js`
Two changes:

**(a) Transcript toggle for admin-sent voice messages, inside `renderMessage()`'s audio branch:**

**Old code:**
```js
    } else if (msg.msg_type === 'audio') {
      bubble.appendChild(buildAudioBar('/admin/' + msg.media_path));
    }
    col.appendChild(bubble);
```

**New code:**
```js
    } else if (msg.msg_type === 'audio') {
      bubble.appendChild(buildAudioBar('/admin/' + msg.media_path));

      /* TASK-1a START: transcript toggle for a voice message the admin
         sent to this customer (admin -> customer direction only; a
         customer's own outgoing voice message being transcribed back for
         the admin is Task 1b's separate scope, not this file's customer-
         facing render). msg.transcript arrives automatically via
         chat-poll.php's existing `m.*` select once chat-admin-reply.php
         has written it — see that file's TASK-1a block — so no poll-file
         change was needed here. Mirrors the .pcw-edited-tag-btn /
         .pcw-original-reveal toggle pattern directly below for a
         consistent interaction language. Nothing renders when transcript
         is absent, so a normal audio bubble is unchanged in that case. */
      if (!isUser && msg.transcript) {
        const transcriptToggle = el('button', { type: 'button', class: 'pcw-transcript-toggle' }, 'View transcript');
        const transcriptTextEl = el('div', { class: 'pcw-transcript-text' }, msg.transcript);
        const transcriptChildren = [transcriptTextEl];

        // Low-confidence flag, Warning Amber per
        // PETCENTER_DESIGN_SYSTEM_v1_0.md Section 18. The 0.5 cutoff is
        // this task's own judgment call — reasoning in
        // Task-1a-Completion.md — not a value from the design system or
        // the STT provider.
        if (typeof msg.transcript_confidence === 'number' && msg.transcript_confidence < 0.5) {
          transcriptChildren.push(el('div', { class: 'pcw-transcript-lowconf-tag' },
            [el('i', { class: 'fas fa-triangle-exclamation' }), document.createTextNode(' This transcript may be inaccurate')]));
        }

        const transcriptBox = el('div', { class: 'pcw-transcript-box' }, transcriptChildren);
        transcriptToggle.addEventListener('click', () => {
          const isShown = transcriptBox.classList.toggle('show');
          transcriptToggle.classList.toggle('active', isShown);
        });
        bubble.appendChild(transcriptToggle);
        bubble.appendChild(transcriptBox);
      }
      /* TASK-1a END */
    }
    col.appendChild(bubble);
```

**(b) CSS**, added directly after the existing `.pcw-original-reveal-text` rule:

**Old code:**
```css
  .pcw-original-reveal-text { font-size: 12px; color: #4a4030; font-style: italic; line-height: 1.4; }
```

**New code:**
```css
  .pcw-original-reveal-text { font-size: 12px; color: #4a4030; font-style: italic; line-height: 1.4; }

  /* TASK-1a START: transcript toggle/reveal for an admin's voice message
     to this customer. Structurally identical to .pcw-edited-tag-btn +
     .pcw-original-reveal directly above (same collapse/expand pattern,
     same transition timing) for a consistent interaction language.
     Low-confidence tag uses Warning Amber (#f59e0b) per
     PETCENTER_DESIGN_SYSTEM_v1_0.md Section 18 (Status & Feedback
     Systems — Warning Toast), background at ~10% opacity per that
     section's "soft tint" rule. */
  .pcw-transcript-toggle {
    border: none; background: none; cursor: pointer; font-size: 10px; font-style: italic;
    color: #a89c8a; padding: 0; text-decoration: underline; text-underline-offset: 2px;
    transition: color 0.15s ease; display: block; margin-top: 4px;
  }
  .pcw-transcript-toggle:hover, .pcw-transcript-toggle.active { color: #ED7624; }
  .pcw-transcript-box {
    max-height: 0; overflow: hidden; opacity: 0; transition: max-height 0.25s ease, opacity 0.2s ease, margin 0.25s ease;
    background: rgba(16,33,75,0.05); border-radius: 8px; margin-top: 0;
  }
  .pcw-transcript-box.show { max-height: 260px; opacity: 1; margin-top: 6px; padding: 8px 10px; }
  .pcw-transcript-text { font-size: 12px; color: #4a4030; line-height: 1.4; }
  .pcw-transcript-lowconf-tag {
    display: flex; align-items: center; gap: 5px; font-size: 10.5px; font-weight: 600;
    color: #f59e0b; background: rgba(245,158,11,0.1); border-radius: 6px; padding: 3px 7px; margin-top: 6px;
  }
  /* TASK-1a END */
```

---

## New Files
No new files. Every file in this task was an existing file that was changed — nothing is in a zip; all code changes are shown above under Code Changes as old/new snippets, per WORKER-DELIVERY-GUIDE.md's rule that a changed existing file never goes in the zip.

## STT Tool Choice (Rule 11 — web search required, documented here)

**Chosen: Groq's hosted Whisper endpoint** (`https://api.groq.com/openai/v1/audio/transcriptions`), model `distil-whisper-large-v3-en`, an OpenAI-Whisper-API-compatible REST endpoint.

- **Why this one:** confirmed via web search (current as of this task) that Groq's free tier is a genuinely *recurring* daily allowance — 2,000 requests/day and 28,800 audio-seconds/day (8 hours/day) — not a one-time trial credit that runs out. No credit card is required to sign up. The two other strong candidates found (AssemblyAI, Deepgram) both use one-time trial credits (roughly $50 and $200 respectively) that are consumed and gone, which fails this task's explicit "genuinely free, not trial-limited" requirement.
- **Model choice:** `distil-whisper-large-v3-en` is Groq's English-only-optimized, fastest/cheapest Whisper variant — the right fit here since Pet Center's own admins speak/write English only in this direction. **Flag for Task 1b:** that worker needs the customer's language auto-detected (not fixed to English), so they should use `whisper-large-v3` or `whisper-large-v3-turbo` instead of this English-only model — same account, same free-tier limits, different `model` parameter.
- **Confidence heuristic:** Whisper does not return one official confidence score. This task derives one by requesting `response_format=verbose_json`, reading each segment's `avg_logprob` (a log-probability, always ≤ 0), taking `exp()` of each to get a 0–1 probability-like value, and averaging across segments. A result under 0.5 shows the "low confidence" tag. This threshold and this derivation are this task's own judgment call, not a number Groq or the design system specifies — worth revisiting with real-world samples once the feature is live with a real API key.
- **Setup needed before this works:** `GROQ_API_KEY_TASK1A` in `chat-admin-reply.php` is currently the placeholder string `'REPLACE_WITH_YOUR_GROQ_API_KEY'`. Until a real key is set there, the feature no-ops silently — voice messages send normally, they just never get a transcript. A free Groq account (console.groq.com) is all that's needed to generate one.

## Testing Performed
Live database/API testing wasn't possible in this environment (no MySQL instance, no live Groq key). What was verified:
- `chat-setup.php` and `chat-migrate.php`: SQL syntax follows the file's own existing, already-working patterns exactly (same `ADD COLUMN IF NOT EXISTS` style already used for `archived`).
- `chat-admin-reply.php`: brace/paren/bracket balance confirmed after edit (no PHP CLI available in this sandbox to run `php -l`, network also disabled so one couldn't be installed — this is a gap the CEO or a later verification pass with PHP available should close).
- `chat-widget.js`: `node --check` passed on the full file after edits.
- `admin-dashboard.html`: both inline `<script>` blocks extracted and passed `node --check` together.
- Used Playwright to load both `chat-widget.js` (via a throwaway copy with `renderMessage` temporarily exposed on `window`, deleted immediately after) and the real `admin-dashboard.html` (its `renderAdminMessage` is already a global function, no copy needed) and rendered stubbed messages covering all four states: high-confidence transcript, low-confidence transcript, admin audio with no transcript yet, and a customer-sent audio message. Confirmed via DOM assertions and a screenshot that: the toggle only appears on admin audio messages that have a transcript; the low-confidence tag only appears under 0.5; the language tag appears only when cached; nothing renders differently on customer-sent audio or on admin audio with no transcript (both look exactly as they did before this task).

## Bugs Found Outside Task Scope
None found.

One design-system note rather than a bug: `admin-dashboard.html` already has a `.cad-bubble--admin` themed background (a purple gradient, from a different, separate dashboard-redesign task) that renders under a default theme with lower contrast for muted-grey text like the pre-existing `.cad-edited-tag-btn`. This task's new transcript elements reuse those exact same pre-existing hardcoded colors for consistency (see Code Changes above), so they have identical contrast behavior to those pre-existing elements under that theme — not a regression introduced here, just worth knowing about if that theme's contrast is ever revisited.

## Self-Check Confirmation
Only the five files listed under Files Changed were modified: `chat-setup.php`, `chat-migrate.php`, `chat-admin-reply.php`, `admin-dashboard.html`, and `chat-widget.js`. Every block added to those files is wrapped in a `TASK-1a START` / `TASK-1a END` anchor comment with an explanation of what it does, per Warning & Instructions rule 6. No other file was opened for editing, and no code outside my named zone in the three shared files was touched.

---

## SECOND VERIFICATION ROUND

Re-read `adoption-website-project-summary.md`, `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` (including the voice-message size/MIME-type documentation I hadn't fully cross-checked the first time), and the full `ADMIN_PROJECTS_2_3_4_TASK_ASSIGNMENTS.md` Task 1a section plus the Core Rules / Project Directives / Shared Data Contract at the top of that document again in full. Found one real bug and three worthwhile gaps. No files outside the original five were touched; Scope Lock held throughout this round too.

### Bug found and fixed: deprecated Groq model ID

Re-confirming the tool choice via a fresh web search (per Directive 13) turned up Groq's own deprecation page: `distil-whisper-large-v3-en` — the model this task originally shipped with — was deprecated and **shut down on August 23, 2025**, with `whisper-large-v3-turbo` as Groq's own recommended replacement. That shutdown date is well before this task was originally built, meaning the original code would have failed on every single transcription attempt once a real API key was set (the helper's own error handling would have swallowed this as a silent `[null, null]`, so the symptom would have been "the feature just never works," not a visible error). This is now fixed.

**File:** `chat-admin-reply.php`

**Old code:**
```php
/* TASK-1a START: Groq Whisper (distil-whisper-large-v3-en) speech-to-text
   helper for admin-sent voice messages, English only, since Pet Center's
   own admins speak/write English only in this direction (per
   ADMIN_PROJECTS_2_3_4_TASK_ASSIGNMENTS.md, Task 1a). Tool choice, the
   free-tier terms, and the confidence-heuristic reasoning below are all
   documented in full in Task-1a-Completion.md. This constant + helper are
   scoped locally to chat-admin-reply.php rather than added to
   chat-config.php: chat-config.php is loaded by every chat PHP file and
   was not part of this task's assigned file list, so under Scope Lock a
   shared constant there is a call for the CEO to make, not this task —
   see the completion file for a suggested follow-up if Task 1b also
   needs Groq access from chat-send.php. Never throws: any failure just
   returns [null, null] so a normal audio send is never blocked by a
   transcription problem. */
define('GROQ_API_KEY_TASK1A', 'REPLACE_WITH_YOUR_GROQ_API_KEY');

function chatTranscribeAdminAudioTask1a($absoluteAudioPath) {
    if (GROQ_API_KEY_TASK1A === 'REPLACE_WITH_YOUR_GROQ_API_KEY') {
        return [null, null]; // Feature not configured yet — silent no-op.
    }
    if (!is_file($absoluteAudioPath)) {
        return [null, null];
    }
    try {
        $ch = curl_init('https://api.groq.com/openai/v1/audio/transcriptions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . GROQ_API_KEY_TASK1A],
            CURLOPT_POSTFIELDS     => [
                'file'            => new CURLFile($absoluteAudioPath),
                'model'           => 'distil-whisper-large-v3-en',
                'language'        => 'en',
                'response_format' => 'verbose_json',
                'temperature'     => '0',
            ],
        ]);
```

**New code:**
```php
/* TASK-1a START: Groq Whisper (whisper-large-v3-turbo) speech-to-text
   helper for admin-sent voice messages, hinted to English since Pet
   Center's own admins speak/write English only in this direction (per
   ADMIN_PROJECTS_2_3_4_TASK_ASSIGNMENTS.md, Task 1a). Tool choice, the
   free-tier terms, and the confidence-heuristic reasoning below are all
   documented in full in Task-1a-Completion.md. This constant + helper are
   scoped locally to chat-admin-reply.php rather than added to
   chat-config.php: chat-config.php is loaded by every chat PHP file and
   was not part of this task's assigned file list, so under Scope Lock a
   shared constant there is a call for the CEO to make, not this task —
   see the completion file for a suggested follow-up if Task 1b also
   needs Groq access from chat-send.php. Never throws: any failure just
   returns [null, null] so a normal audio send is never blocked by a
   transcription problem.
   NOTE (added on re-verification): Groq's English-only variant,
   distil-whisper-large-v3-en, was deprecated and shut down on Aug 23
   2025 — confirmed current via web search during this task — so
   whisper-large-v3-turbo (multilingual, 'language' param below just
   hints English rather than restricting to it) is used instead. Same
   free-tier ceiling applies (2,000 requests/day, 28,800 audio-sec/day,
   20 RPM, 25MB max file on the free plan — CHAT_VOICE_MAX_BYTES is 20MB
   per chat-config.php, so a normal admin recording is always under
   that). This also means Task 1b no longer has a dedicated English-only
   model to avoid — whisper-large-v3 / whisper-large-v3-turbo are the
   only two options for either direction now. */
define('GROQ_API_KEY_TASK1A', 'REPLACE_WITH_YOUR_GROQ_API_KEY');

function chatTranscribeAdminAudioTask1a($absoluteAudioPath) {
    if (GROQ_API_KEY_TASK1A === 'REPLACE_WITH_YOUR_GROQ_API_KEY') {
        return [null, null]; // Feature not configured yet — silent no-op.
    }
    if (!is_file($absoluteAudioPath)) {
        return [null, null];
    }
    try {
        // Explicit mimetype rather than leaving CURLFile to guess: admin
        // recordings can legitimately arrive as audio/webm, audio/mp4,
        // audio/ogg, or (on some Android/Brave browsers, per this
        // project's own documented quirk) a container labeled video/webm
        // for an audio-only clip — ffmpeg on Groq's side reads the real
        // stream either way, but sending an accurate hint based on the
        // file's actual extension (set at upload time, not from that
        // label) is more robust than the "application/octet-stream"
        // default curl falls back to when no mimetype is given.
        $ext = strtolower(pathinfo($absoluteAudioPath, PATHINFO_EXTENSION));
        $mimeByExt = [
            'webm' => 'audio/webm', 'mp4' => 'audio/mp4', 'm4a' => 'audio/mp4',
            'ogg'  => 'audio/ogg',  'wav' => 'audio/wav',  'mp3' => 'audio/mpeg',
        ];
        $mime = $mimeByExt[$ext] ?? 'application/octet-stream';

        $ch = curl_init('https://api.groq.com/openai/v1/audio/transcriptions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . GROQ_API_KEY_TASK1A],
            CURLOPT_POSTFIELDS     => [
                'file'            => new CURLFile($absoluteAudioPath, $mime),
                'model'           => 'whisper-large-v3-turbo',
                'language'        => 'en',
                'response_format' => 'verbose_json',
                'temperature'     => '0',
            ],
        ]);
```

Re-confirmed via a fresh, targeted web search of Groq's own `console.groq.com/docs/deprecations` page that this is accurate and current: shutdown date 08/23/25, recommended replacement `whisper-large-v3-turbo`, and that `response_format=verbose_json` on `whisper-large-v3-turbo` still returns per-segment `avg_logprob` exactly as the confidence-heuristic code expects — no other change needed there. One stale third-party aggregator page turned up in search still listing the old model as "active"; Groq's own deprecations page is the authoritative source and was treated as such.

### Gap found: "copy transcript" control was missing

Re-reading Task 1a's "Recommended Enhancements" list (and noticing Task 1b's own section explicitly says *"Same 'copy transcript' control as Task 1a"*, i.e. it assumes this task already built one) surfaced that I had not actually added a copy-to-clipboard control the first time through. Added now, in both files, right under the transcript text, using the plain Clipboard API with a silent no-op if it's unavailable.

**File:** `admin-dashboard.html`

**Old code:**
```js
        const transcriptTextEl = document.createElement('div');
        transcriptTextEl.className = 'cad-transcript-text';
        transcriptTextEl.textContent = msg.transcript;
        transcriptBox.appendChild(transcriptTextEl);

        // Low-confidence flag, Warning Amber styling per
```

**New code:**
```js
        const transcriptTextEl = document.createElement('div');
        transcriptTextEl.className = 'cad-transcript-text';
        transcriptTextEl.textContent = msg.transcript;
        transcriptBox.appendChild(transcriptTextEl);

        // Copy-transcript control — a recommended enhancement in
        // ADMIN_PROJECTS_2_3_4_TASK_ASSIGNMENTS.md's Task 1a section,
        // and one Task 1b's own section explicitly expects this task to
        // already have ("Same 'copy transcript' control as Task 1a").
        // Plain Clipboard API with a graceful no-op if it's unavailable
        // (e.g. a non-HTTPS deployment) rather than throwing.
        const copyBtn = document.createElement('button');
        copyBtn.type = 'button';
        copyBtn.className = 'cad-transcript-copy-btn';
        copyBtn.innerHTML = '<i class="fas fa-copy"></i> Copy';
        copyBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          if (!navigator.clipboard) return;
          navigator.clipboard.writeText(msg.transcript).then(() => {
            const original = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied';
            setTimeout(() => { copyBtn.innerHTML = original; }, 1500);
          }).catch(() => {});
        });
        transcriptBox.appendChild(copyBtn);

        // Low-confidence flag, Warning Amber styling per
```

CSS added (after `.cad-transcript-text`):
```css
.cad-transcript-copy-btn {
  border: none; background: rgba(16,33,75,0.08); cursor: pointer; font-size: 10.5px; font-weight: 600;
  color: #10214b; padding: 3px 8px; border-radius: 5px; margin-top: 6px; display: inline-flex;
  align-items: center; gap: 4px; transition: background 0.15s ease;
}
.cad-transcript-copy-btn:hover { background: rgba(16,33,75,0.15); }
```

**File:** `chat-widget.js`

**Old code:**
```js
        const transcriptToggle = el('button', { type: 'button', class: 'pcw-transcript-toggle' }, 'View transcript');
        const transcriptTextEl = el('div', { class: 'pcw-transcript-text' }, msg.transcript);
        const transcriptChildren = [transcriptTextEl];
```

**New code:**
```js
        const transcriptToggle = el('button', { type: 'button', class: 'pcw-transcript-toggle' }, 'View transcript');
        const transcriptTextEl = el('div', { class: 'pcw-transcript-text' }, msg.transcript);
        // Copy-transcript control — recommended enhancement in
        // ADMIN_PROJECTS_2_3_4_TASK_ASSIGNMENTS.md's Task 1a section;
        // mirrors the same control on the admin-dashboard.html side.
        const copyBtn = el('button', { type: 'button', class: 'pcw-transcript-copy-btn' }, 'Copy');
        copyBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          if (!navigator.clipboard) return;
          const original = copyBtn.textContent;
          navigator.clipboard.writeText(msg.transcript).then(() => {
            copyBtn.textContent = 'Copied';
            setTimeout(() => { copyBtn.textContent = original; }, 1500);
          }).catch(() => {});
        });
        const transcriptChildren = [transcriptTextEl, copyBtn];
```

CSS added (after `.pcw-transcript-text`):
```css
  .pcw-transcript-copy-btn {
    border: none; background: rgba(16,33,75,0.08); cursor: pointer; font-size: 10.5px; font-weight: 600;
    color: #10214b; padding: 3px 8px; border-radius: 5px; margin-top: 6px; display: inline-block;
    transition: background 0.15s ease;
  }
  .pcw-transcript-copy-btn:hover { background: rgba(16,33,75,0.15); }
```

Verified with Playwright (clipboard permissions granted in the test browser context, since a plain `file://` page without granted permissions isn't representative of the real HTTPS deployment): clicking Copy calls `navigator.clipboard.writeText` with the exact transcript text and shows "Copied" feedback for 1.5s on both the admin and customer sides.

### Gap found: `preferred_chat_language` shown as a raw code, not a readable name

The Shared Data Contract is explicit that `preferred_chat_language` must never be renamed or normalized (e.g. Chinese is `zh-Hans`, not `zh`) — my original pass displayed that raw value verbatim to the admin, which is contract-compliant but not very readable (e.g. "Customer's preferred language: zh-Hans"). Added a **display-only** lookup table in `admin-dashboard.html` that never touches the stored or passed-through value itself — only what's rendered. Unmapped codes still fall back to the raw value, so nothing can ever render as blank or wrong.

**File:** `admin-dashboard.html`

**Old code:**
```js
  // TASK-8 END (8b — admin state additions for outgoing typing/recording pings)
  // TASK-1a START: chat-admin-reply.php returns the customer's saved
  // preferred_chat_language (Task 3's field) in its send response so this
  // panel can show it next to a voice message's transcript without an
  // extra request. chat_threads has no such column yet client-side (Task
  // 3 not part of this task's scope), so this in-memory cache — cleared
  // on a full page reload, not persisted — is the only place this task
  // can source that value from. See sendAdminMessage() and
  // adminPollOnce() for where this is written to and read from.
  task1aThreadLangByChatId: {},
  // TASK-1a END
};
```

**New code:**
```js
  // TASK-8 END (8b — admin state additions for outgoing typing/recording pings)
  // TASK-1a START: chat-admin-reply.php returns the customer's saved
  // preferred_chat_language (Task 3's field) in its send response so this
  // panel can show it next to a voice message's transcript without an
  // extra request. chat_threads has no such column yet client-side (Task
  // 3 not part of this task's scope), so this in-memory cache — cleared
  // on a full page reload, not persisted — is the only place this task
  // can source that value from. See sendAdminMessage() and
  // adminPollOnce() for where this is written to and read from.
  task1aThreadLangByChatId: {},
  // TASK-1a END
};

/* TASK-1a START: common ISO 639-1 preferred_chat_language codes mapped to
   a human-readable name, for display only — see the usage site in
   renderAdminMessage() for why this never changes the stored/passed
   value itself (the Shared Data Contract in
   ADMIN_PROJECTS_2_3_4_TASK_ASSIGNMENTS.md requires the raw code never be
   renamed or normalized). Not exhaustive; anything missing just falls
   back to showing the raw code, which is always a safe, correct
   display. */
const TASK1A_LANG_DISPLAY_NAMES = {
  en: 'English', es: 'Spanish', fr: 'French', de: 'German', it: 'Italian',
  pt: 'Portuguese', 'zh-Hans': 'Chinese (Simplified)', ja: 'Japanese',
  ko: 'Korean', ar: 'Arabic', hi: 'Hindi', ru: 'Russian', vi: 'Vietnamese',
  tl: 'Tagalog', pl: 'Polish', nl: 'Dutch', tr: 'Turkish',
};
/* TASK-1a END */
```

And at the display site itself:

**Old code:**
```js
        if (msg._task1aThreadLang) {
          const langTag = document.createElement('div');
          langTag.className = 'cad-transcript-lang-tag';
          langTag.textContent = "Customer's preferred language: " + msg._task1aThreadLang;
          transcriptBox.appendChild(langTag);
        }
```

**New code:**
```js
        if (msg._task1aThreadLang) {
          const langTag = document.createElement('div');
          langTag.className = 'cad-transcript-lang-tag';
          const readableLang = TASK1A_LANG_DISPLAY_NAMES[msg._task1aThreadLang] || msg._task1aThreadLang;
          langTag.textContent = "Customer's preferred language: " + readableLang;
          transcriptBox.appendChild(langTag);
        }
```

Verified with Playwright: `zh-Hans` renders as "Chinese (Simplified)"; an unmapped test code (`xx-unknown`) correctly falls back to showing itself verbatim rather than breaking or showing nothing.

### Hardening (not a functional bug, done proactively): explicit CURLFile mimetype

Re-reading `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s voice-message documentation surfaced that admin recordings can arrive as `audio/webm`, `audio/mp4`, `audio/ogg`, or — per that document's own noted Android/Brave quirk — a container labeled `video/webm` for what is actually an audio-only clip. The original code let `CURLFile` guess the MIME type (defaulting to `application/octet-stream` when it can't), which would likely still work since Groq decodes the real audio stream regardless of the label, but passing an accurate hint based on the file's actual extension is strictly more correct and was a one-line change to make while already in this function — included in the same code block shown under the model-ID fix above, since both touch the same lines.

### Re-checked and confirmed not broken

- `$kind === 'audio'` is set from which dedicated token field the client sent the file under (`audioToken`), not from the file's MIME type — so the Android/Brave "video/webm"-labeled-audio quirk can never cause an admin voice message to be misclassified and skipped by this task's `if ($kind === 'audio')` check.
- Grouped multi-media sends (text + image + audio together) are inserted as separate rows and each still renders through the same single `renderAdminMessage()` / `renderMessage()` path this task edits — there is no separate "grouped card" renderer that would bypass the transcript UI.
- `isAdmin` in `renderAdminMessage()` is per-message (`msg.from_type === 'admin'`), not "viewer is admin," so the transcript toggle correctly never appears on customer-sent or bot-sent messages (confirmed bots only ever send templated text, never audio, so this is moot for bots anyway).
- `CHAT_VOICE_MAX_BYTES` (20MB, per the project summary) is comfortably under Groq's free-tier 25MB file-size ceiling, so no admin recording this system permits could ever be rejected by Groq for size.
- `fa-triangle-exclamation`, used for the low-confidence tag, is already the exact icon class this codebase uses elsewhere for warnings (`chat-widget.js` line ~2760, `admin-dashboard.html` line ~6367) — confirmed consistent, not a mismatched Font Awesome version/icon name.
- Scanned all of this task's own added code for emoji (Core Rule 2) — none found. (Three plain Unicode symbol characters — ✓, ☰, ✗ — exist elsewhere in the untouched parts of `admin-dashboard.html`; confirmed these predate this task and are not inside any `TASK-1a` block.)
- Re-ran `node --check` on `chat-widget.js` and on both of `admin-dashboard.html`'s extracted `<script>` blocks together after every change in this round — all pass with no syntax errors.
- Re-ran the full Playwright test suite (all four message-state cases, both files) after every fix in this round — all pass, zero page errors.

### Bugs Found Outside Task Scope
None found, still. (The pre-existing `.cad-bubble--admin` dark-theme contrast observation from the original round still stands as a note, not a bug — see above.)

### Self-Check Confirmation (second round)
Still only the same five files: `chat-setup.php`, `chat-migrate.php`, `chat-admin-reply.php`, `admin-dashboard.html`, `chat-widget.js`. `chat-setup.php` and `chat-migrate.php` needed no changes in this round. Every new line added in this round is inside an existing or new `TASK-1a` anchor comment. No file outside the original five was opened or edited.

### Hand-in format fix (per WORKER-DELIVERY-GUIDE.md)
No code was redone or changed for this pass — only this completion file's format. Confirmed: Files Changed now has all four required columns with live paths from the guide's mapping table; no new files were created in this task, so there is no zip and the New Files section says so; every Code Changes block was already wrapped in `TASK-1a` anchors with an old/new snippet pulled verbatim from the real file; only the five files listed above were ever touched, none of them added or removed in this pass.
