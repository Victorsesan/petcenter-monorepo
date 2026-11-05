# Task Completion — Task 1a

## Task Number
Admin-Project #2 — Task 1a (Transcription Build, admin → customer direction), quoted from `ADMIN_PROJECTS_2_3_4_TASK_ASSIGNMENTS.md`: "TASK 1a — Transcription Build (Admin → Customer Direction)"

**Correction flagged on this hand-in pass:** every earlier version of this
file (and the code comments inside the delivered files) labeled this
**Admin-Project #4**. That was wrong and has now been corrected
everywhere, including inside the actual code comments of the delivered
file in the zip. The task-assignment document's own title —
`ADMIN PROJECTS #2, #3, #4` / "Voice Transcription · Dark/Light Mode ·
Chat Language Preference" — lists three features in that order against
those three numbers; this task (Voice Transcription) is the first one,
**#2**, not #4. This is also what the project's own memory/tracking
system had already independently recorded (`Admin-Project #2`, alias
`AP2`) the whole time — only this worker's own doc text had it wrong.
Caught while naming this hand-in's zip, since the zip name needed the
correct project number. Fixed by find-and-replace (13 occurrences in
this file, 1 in `Task-1b-Completion.md`, 1 inside the actual
`admin/chat-transcribe.php` comments in the zip) — no logic changed.

## What Was Required
Build transcription for the admin → customer voice-message direction only:
when an admin sends a voice message, the customer must be able to click to
see a text transcription of it, shown alongside the customer's
`preferred_chat_language` value (if Task 3 has set one) so whoever
manually translates it knows the target language. The admin must also be
able to see the transcript of their own sent voice message, since the
admin is the one who has to manually translate it. Directive 17 applies
throughout: this never translates anything itself — it only produces
readable text for a human to translate. A real, currently-free (not a
lapsing trial or one-time credit) transcription tool had to be selected
via web search and documented, with its renewal terms stated plainly.

**Note on this hand-in:** this task and Task 1b were both completed by
the same worker (assigned to confirm/extend after a separate worker's
independent attempt at Task 1a — see memory/prior correspondence). Both
tasks touch the exact same five existing files, with no file belonging
to only one of them, so — per `WORKER-DELIVERY-GUIDE.md`'s rule that a
changed file is handed in once, as one old→new pair per location — **all
mergeable code for both tasks is consolidated below, in this file.**
`Task-1b-Completion.md` documents that task's own reasoning, tool choice
and testing, but points back here for the actual code, so the integrator
never has to apply two conflicting edits to the same spot in the same
file.

**Hand-in packaging (corrected on this pass):** this file and the one new
file it describes are delivered together, joined in one zip named
`admin-project-2-task-1a.zip` — `[project-name]-task-[task-number].zip`,
using this project's own established name (`admin-project-2`, matching
this doc's Task Number above and the project's own tracking system) so
sorting and gap-checking across workers stays consistent. Task 1b has no
new files of its own, but per the same instruction its completion file
still ships in its own zip, `admin-project-2-task-1b.zip`, rather than
being sent loose.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `chat-config.php` | `admin/chat-config.php` | Changed | Yes — Task 1b adds its own function in this same file (see below) |
| `chat-setup.php` | `admin/chat-setup.php` | Changed | No |
| `chat-migrate.php` | `admin/chat-migrate.php` | Changed | No |
| `chat-transcribe.php` | `admin/chat-transcribe.php` | **New** | Yes — Task 1b extends this task's new file |
| `chat-widget.js` | `admin/chat-widget.js` | Changed | Yes — Task 1b adds read-aloud playback in this same file |
| `admin-dashboard.html` | `admin/index.html` | Changed | Yes — Task 1b adds a second transcript-control call site and read-aloud in this same file |

**New-folder check (done, none needed):** this task's one new file,
`admin/chat-transcribe.php`, sits inside `admin/` — an existing folder,
not a new one. Confirmed against both `adoption-website-project-summary.md`
and `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`: both already reference live
files at `admin/chat-setup.php`, `admin/chat-widget.js`, and others in
that same folder, and `WORKER-DELIVERY-GUIDE.md` section 3 independently
confirms it as "the verified mapping." No new folder is being introduced
by this task, so there is nothing to flag for the integrator to create.

**`index.html` naming check (done):** this task changes exactly one
`.html` file, `admin-dashboard.html`, and per the mapping table above it
is delivered as `admin/index.html` — already following the strict
`index.html`-per-folder convention, not a loose named file. No other
`.html` file is touched by this task.

Live paths taken from the mapping table in `WORKER-DELIVERY-GUIDE.md`
section 3, cross-checked against `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s
own file-structure section and server-URL references (e.g.
`adopt.petcenterco.com/admin/chat-setup.php`, `<script
src="/admin/chat-widget.js">`) — both sources agree, no conflict to flag.

## Transcription tool selected (documented per task instructions)

**Groq's hosted Whisper endpoint**
(`https://api.groq.com/openai/v1/audio/transcriptions`). The model used
is `whisper-large-v3-turbo` — this is the corrected, final choice; see
"Issue 1" under Second Verification Round below for why the originally-
chosen model had to be changed, and do not use `distil-whisper-large-v3-en`,
which that section explains is dead.

- **Renewal terms — recurring, not a one-time credit:** Groq's free
  developer tier is rate-limited and resets continuously (20
  requests/minute, 2,000/day, 7,200 audio-seconds/hour, 28,800/day,
  25MB/upload, re-confirmed live during the second verification round) —
  no credit card, no dollar-credit balance that depletes, no trial end
  date.
- I initially considered **Speechmatics**, which historically offered a
  recurring 240-min/month free tier, but a web search showed they switched
  to a **one-time $25 credit grant** starting Aug 1, 2026 (already in
  effect). That fails "not a lapsing gimmick," so I ruled it out.

## Code Changes

Every block below is the exact old text from the original file this worker was given, matched once, and the exact final text now in the delivered file — covering this task's own work and Task 1b's additions together, since both tasks touched these same shared files and are being handed in as one merged state per file (see the note in Task-1b-Completion.md). Anchor comments inside each block show which task each specific addition belongs to.


### `chat-config.php`  →  live path `admin/chat-config.php`

**Block 1 — Old code:**
```php
    return array_key_exists($reason, CHAT_REASONS);
}

// ============================================
// RATE LIMITING — sliding window backed by chat_rate_limits
// ============================================
```
**Block 1 — New code:**
```php
    return array_key_exists($reason, CHAT_REASONS);
}

// TASK-1a START — Admin voice-message transcription (admin -> customer direction)
// ============================================
// TRANSCRIPTION (Task 1a) — Groq's hosted Whisper endpoint.
//
// Tool choice, confirmed via web search on 2026-09-11: Groq's free developer
// tier for audio transcription (OpenAI-compatible /openai/v1/audio/transcriptions
// endpoint). This is a RECURRING, no-credit-card, no-expiring-balance free
// tier (rate limits reset continuously — per-minute/per-hour/per-day caps,
// not a one-time dollar credit that runs out) — as of this writing: 20
// requests/minute, 2,000 requests/day, 7,200 audio-seconds/hour, 28,800
// audio-seconds/day, 25MB per upload (free tier; re-confirmed live during
// the second verification round, unchanged). This is NOT a "free trial
// that lapses" — there is no trial period and no credit balance to exhaust.
//
// TASK-1a REVISED (second verification round) — MODEL CORRECTED. This
// originally used 'distil-whisper-large-v3-en' on the reasoning that
// admins speak English only, so an English-only-optimized model made
// sense. On re-verification, a fresh live fetch of Groq's own docs
// (console.groq.com/docs/deprecations and /docs/speech-to-text) confirmed
// Groq deprecated and fully shut down distil-whisper-large-v3-en on
// 08/23/25 — over a year before this task was even assigned. Every API
// call using that model ID would have failed outright with an error the
// moment a real API key was added; this was caught only because an
// unrelated memory-system conflict happened to surface the deprecation
// claim, prompting this re-check, not because the original research
// caught it. Groq's OWN deprecation notice names whisper-large-v3-turbo
// as the official recommended replacement, so that's what this now uses,
// rather than a second independent judgment call. Groq's current
// (re-verified) lineup has exactly two speech-to-text models, and NEITHER
// is English-only anymore — whisper-large-v3-turbo (multilingual, faster,
// 12% word error rate) and whisper-large-v3 (multilingual, more accurate,
// 10.3% WER). There is no dedicated English-only model to pick between
// them on that basis any more; this task still passes language='en'
// below, which Groq's docs confirm remains a valid way to improve
// accuracy/latency on a multilingual model when the spoken language is
// already known. Both models share the identical free-tier rate limits
// above (re-confirmed live, same for both) — so there is no quota
// difference driving this choice either; whisper-large-v3-turbo was
// chosen specifically because it is Groq's own prescribed migration path
// for this exact situation. Task 1b's chatTranscribeCustomerAudioFile()
// (chat-config.php, below) already correctly uses whisper-large-v3 — it
// was never affected by this deprecation, since "whisper-large-v3" and
// "distil-whisper-large-v3-en" are different model IDs and only the
// latter was ever deprecated.
// ============================================
define('GROQ_API_KEY', 'REPLACE_WITH_YOUR_GROQ_API_KEY'); // get a free key at https://console.groq.com/keys
define('GROQ_STT_MODEL', 'whisper-large-v3-turbo');
define('GROQ_STT_ENDPOINT', 'https://api.groq.com/openai/v1/audio/transcriptions');

/**
 * Transcribes a single audio file already saved on disk (a chat voice
 * message) via Groq's hosted Whisper endpoint. Returns
 * ['success' => true, 'text' => '...', 'confidence' => float|null] on
 * success, or ['success' => false, 'error' => '...'] on any failure
 * (missing API key, network error, non-200 response, empty result) —
 * callers must not treat a failure as a stub to retry-forever; it should
 * be surfaced to whoever clicked "transcribe" so they know to fall back
 * to listening to the audio directly. Never throws — every failure path
 * is caught and returned as a normal array so the calling endpoint's
 * transaction/response logic doesn't need its own try/catch around this.
 *
 * TASK-1a REVISED — added 'confidence'. Whisper doesn't return one
 * official confidence score, so this derives a 0.000-1.000 heuristic:
 * request response_format=verbose_json instead of json, read each
 * returned segment's avg_logprob (a log-probability, always <= 0), take
 * exp() of each to turn it into a 0-1 probability-like value, and average
 * across segments. Callers show a "may be inaccurate" warning below 0.5.
 * This threshold and derivation are a judgment call (Groq's API doesn't
 * specify one), not a value from any provider or design spec — worth
 * revisiting once this has run against real customer audio. null when no
 * segments came back (e.g. a very short clip), which callers must treat
 * as "unknown," not as "confident."
 */
function chatTranscribeAudioFile($absolutePath) {
    if (GROQ_API_KEY === '' || GROQ_API_KEY === 'REPLACE_WITH_YOUR_GROQ_API_KEY') {
        return ['success' => false, 'error' => 'Transcription is not configured yet (missing GROQ_API_KEY in chat-config.php).'];
    }
    if (!file_exists($absolutePath)) {
        return ['success' => false, 'error' => 'Audio file not found on disk.'];
    }
    if (filesize($absolutePath) > CHAT_VOICE_MAX_BYTES) {
        return ['success' => false, 'error' => 'Audio file is too large to transcribe.'];
    }

    // Multipart form upload — mirrors curl's CURLFile approach so the whole
    // file is streamed rather than loaded into a request body string.
    $cfile = new CURLFile($absolutePath);
    $postFields = [
        'file'  => $cfile,
        'model' => GROQ_STT_MODEL,
        // TASK-1a REVISED — verbose_json (was plain json) so the response
        // includes per-segment avg_logprob, which the confidence heuristic
        // below needs. Still has a top-level 'text' field either way, so
        // nothing else about parsing the transcript itself changes.
        'response_format' => 'verbose_json',
        'language' => 'en',
    ];

    $ch = curl_init(GROQ_STT_ENDPOINT);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . GROQ_API_KEY],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 60,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        error_log('chatTranscribeAudioFile cURL error: ' . $curlErr);
        return ['success' => false, 'error' => 'Could not reach the transcription service.'];
    }

    $decoded = json_decode($response, true);

    if ($httpCode !== 200 || !is_array($decoded)) {
        error_log('chatTranscribeAudioFile non-200 response (' . $httpCode . '): ' . $response);
        return ['success' => false, 'error' => 'Transcription service returned an error.'];
    }

    $text = trim($decoded['text'] ?? '');
    if ($text === '') {
        return ['success' => false, 'error' => 'Transcription came back empty — the audio may be silent or too unclear to transcribe.'];
    }

    // TASK-1a REVISED — confidence heuristic, see docblock above.
    $confidence = null;
    if (!empty($decoded['segments']) && is_array($decoded['segments'])) {
        $probs = [];
        foreach ($decoded['segments'] as $seg) {
            if (isset($seg['avg_logprob'])) {
                $probs[] = exp((float)$seg['avg_logprob']);
            }
        }
        if (!empty($probs)) {
            $confidence = max(0.0, min(1.0, round(array_sum($probs) / count($probs), 3)));
        }
    }

    return ['success' => true, 'text' => $text, 'confidence' => $confidence];
}
// TASK-1a END — Admin voice-message transcription (admin -> customer direction)

// TASK-1b START — Customer voice-message transcription (customer -> admin
// direction, Admin-Project #2 Task 1b). Separate function from Task 1a's
// chatTranscribeAudioFile() above rather than modifying it, per the
// Sequential File Ownership Chain — 1a's block stays exactly as 1a
// submitted it.
// ============================================
// TRANSCRIPTION (Task 1b) — same Groq account/free tier as Task 1a, a
// different model: 'whisper-large-v3' (confirmed via web search on
// 2026-09-11, GroqDocs: 99+ languages, ~10.3% word error rate; this
// specific model ID was unaffected by the distil-whisper-large-v3-en
// deprecation Task 1a's own comment above describes — re-confirmed live
// during the second verification round, still correct and current).
// Customers aren't limited to English the way admins are, so this uses
// Groq's general multilingual Whisper model (the more-accurate of Groq's
// two current multilingual options, rather than Task 1a's
// speed-optimized whisper-large-v3-turbo — customer audio quality and
// accent variability is less predictable than an admin's, so the better
// word-error-rate seemed worth the (free-tier-irrelevant) cost/speed
// difference) and does NOT pass a `language` parameter, letting Whisper
// auto-detect whatever language the customer spoke. This still shares
// Task 1a's same free-tier
// rate limits (they're per-account, not per-model), so no new signup or
// billing consideration beyond what 1a already documented.
//
// Per Directive 17, this deliberately calls Groq's TRANSCRIPTION endpoint
// only — never Groq/Whisper's separate "translation" endpoint (which
// would translate the audio directly to English text). That would be
// calling a translation tool, which this project never does. The output
// here is a transcript in whatever language the customer actually spoke,
// for the admin to manually translate themselves to understand it.
//
// Honest limitation, not verified end-to-end: I was not able to test this
// against real recorded sample audio in Pet Center customers' likely
// languages (no audio samples were available in this environment) — only
// against GroqDocs' own published benchmarks. The Task 1b Recommended
// Enhancements explicitly flag this as worth doing before fully trusting
// it; flagging it here rather than silently skipping it.
// ============================================
define('GROQ_MULTILINGUAL_STT_MODEL', 'whisper-large-v3');

/**
 * Transcribes a customer's voice message in whatever language it was
 * spoken in (auto-detected by Whisper — no language forced), via the same
 * Groq account as chatTranscribeAudioFile() above. Returns
 * ['success'=>true, 'text'=>..., 'confidence'=>float|null] on success, or
 * ['success'=>false, 'error'=>...] on failure — same shape as that
 * function, on purpose, so chat-transcribe.php can treat both the same way.
 *
 * TASK-1b REVISED (second verification round) — added 'confidence',
 * mirroring Task 1a's heuristic, per this task's own Recommended
 * Enhancements ("same low-confidence flag as Task 1a, mirrored for this
 * direction"). Missed in the original submission; added on review. Exact
 * same derivation as chatTranscribeAudioFile() above: response_format
 * changed from json to verbose_json, average exp(avg_logprob) across
 * segments. See that function's docblock for the full reasoning — not
 * repeated here to avoid drift between two copies of the same explanation.
 */
function chatTranscribeCustomerAudioFile($absolutePath) {
    if (GROQ_API_KEY === '' || GROQ_API_KEY === 'REPLACE_WITH_YOUR_GROQ_API_KEY') {
        return ['success' => false, 'error' => 'Transcription is not configured yet (missing GROQ_API_KEY in chat-config.php).'];
    }
    if (!file_exists($absolutePath)) {
        return ['success' => false, 'error' => 'Audio file not found on disk.'];
    }
    if (filesize($absolutePath) > CHAT_VOICE_MAX_BYTES) {
        return ['success' => false, 'error' => 'Audio file is too large to transcribe.'];
    }

    $cfile = new CURLFile($absolutePath);
    $postFields = [
        'file'  => $cfile,
        'model' => GROQ_MULTILINGUAL_STT_MODEL,
        // TASK-1b REVISED — verbose_json (was plain json), same reason as
        // Task 1a's function: needed for the per-segment avg_logprob the
        // confidence heuristic below reads. Still has a top-level 'text'
        // field either way.
        'response_format' => 'verbose_json',
        // No 'language' key here on purpose — auto-detect, since the
        // customer could be speaking any of the 99+ languages this model
        // supports, unlike Task 1a's guaranteed-English admin speech.
    ];

    $ch = curl_init(GROQ_STT_ENDPOINT);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . GROQ_API_KEY],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 60,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        error_log('chatTranscribeCustomerAudioFile cURL error: ' . $curlErr);
        return ['success' => false, 'error' => 'Could not reach the transcription service.'];
    }

    $decoded = json_decode($response, true);

    if ($httpCode !== 200 || !is_array($decoded)) {
        error_log('chatTranscribeCustomerAudioFile non-200 response (' . $httpCode . '): ' . $response);
        return ['success' => false, 'error' => 'Transcription service returned an error.'];
    }

    $text = trim($decoded['text'] ?? '');
    if ($text === '') {
        return ['success' => false, 'error' => 'Transcription came back empty — the audio may be silent or too unclear to transcribe.'];
    }

    // TASK-1b REVISED — confidence heuristic, identical derivation to
    // chatTranscribeAudioFile() above.
    $confidence = null;
    if (!empty($decoded['segments']) && is_array($decoded['segments'])) {
        $probs = [];
        foreach ($decoded['segments'] as $seg) {
            if (isset($seg['avg_logprob'])) {
                $probs[] = exp((float)$seg['avg_logprob']);
            }
        }
        if (!empty($probs)) {
            $confidence = max(0.0, min(1.0, round(array_sum($probs) / count($probs), 3)));
        }
    }

    return ['success' => true, 'text' => $text, 'confidence' => $confidence];
}
// TASK-1b END

// ============================================
// RATE LIMITING — sliding window backed by chat_rate_limits
// ============================================
```


### `chat-setup.php`  →  live path `admin/chat-setup.php`

**Block 1 — Old code:**
```php
            original_body TEXT NULL,
            deleted_at    TIMESTAMP NULL,
            created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (thread_id) REFERENCES chat_threads(id) ON DELETE CASCADE,
            INDEX (thread_id, created_at),
            INDEX (group_id)
```
**Block 1 — New code:**
```php
            original_body TEXT NULL,
            deleted_at    TIMESTAMP NULL,
            created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            /* TASK-1a START — cached voice-message transcript (admin -> customer
               direction, Admin-Project #2 Task 1a). transcript holds the cached
               text so the same audio is never re-sent to the transcription API
               twice; transcript_status tracks whether one has been requested yet
               ('none'), is in flight ('pending'), succeeded ('done'), or failed
               ('failed'), so the front-end can tell 'never asked' apart from
               'asked and it failed'. transcript_confidence is a 0.000-1.000
               heuristic derived from the STT provider's per-segment
               probabilities (see chatTranscribeAudioFile() in chat-config.php
               for the exact derivation) — NULL until a transcription succeeds
               and returns one, or for any direction/model that doesn't compute
               one. Never blocks a normal audio send; both columns start NULL /
               'none'. See chat-migrate.php for the matching ALTER on any
               database that predates this feature. */
            transcript             TEXT NULL,
            transcript_status      ENUM('none','pending','done','failed') NOT NULL DEFAULT 'none',
            transcript_confidence  DECIMAL(4,3) NULL,
            /* TASK-1a REVISED (second verification round) — when the current
               'pending' claim was made. Lets chat-transcribe.php treat a row
               stuck at 'pending' (a hard crash mid-request) as stale after a
               couple of minutes instead of blocking retries forever. */
            transcript_started_at  TIMESTAMP NULL,
            /* TASK-1a END */
            FOREIGN KEY (thread_id) REFERENCES chat_threads(id) ON DELETE CASCADE,
            INDEX (thread_id, created_at),
            INDEX (group_id)
```


### `chat-migrate.php`  →  live path `admin/chat-migrate.php`

**Block 1 — Old code:**
```php
    "Widen chat_typing.who ENUM to include recording-ping values (Task 8b)" =>
        "ALTER TABLE chat_typing MODIFY who ENUM('user','admin','user_recording','admin_recording') NOT NULL",
    // BUGFIX-1 END
];

foreach ($migrations as $desc => $sql) {
```
**Block 1 — New code:**
```php
    "Widen chat_typing.who ENUM to include recording-ping values (Task 8b)" =>
        "ALTER TABLE chat_typing MODIFY who ENUM('user','admin','user_recording','admin_recording') NOT NULL",
    // BUGFIX-1 END

    // TASK-1a START — voice-message transcription (admin -> customer direction,
    // Admin-Project #2 Task 1a). Adds the columns chat-setup.php's fresh-
    // install schema already has, so an existing database (every currently-
    // deployed database, same situation as every other migration in this file)
    // gets them too. transcript caches the transcribed text per message so the
    // same audio is never re-sent to the transcription API twice; transcript_status
    // tracks none/pending/done/failed so the front-end can tell "never asked"
    // apart from "asked and it failed"; transcript_confidence is the 0-1 quality
    // heuristic; transcript_started_at lets a crashed, stuck 'pending' row be
    // detected as stale and retried (see chat-transcribe.php).
    //
    // TASK-1a REVISED (second verification round) — these four are plain
    // `ADD COLUMN`, NOT `ADD COLUMN IF NOT EXISTS` like the pre-existing
    // `archived` entry above. `IF NOT EXISTS` on ADD COLUMN is MariaDB-only
    // syntax; on real MySQL it is a syntax error, and this script's catch
    // block would print [SKIP] and move on — the columns would silently never
    // be created and chat-transcribe.php would then fail on every request.
    // A plain ADD COLUMN works on both MySQL and MariaDB, and stays safe to
    // re-run: on a second run MySQL/MariaDB raise "duplicate column name",
    // which this script's existing catch block already reports as [SKIP].
    "Add transcript column to chat_messages (Task 1a)" =>
        "ALTER TABLE chat_messages ADD COLUMN transcript TEXT NULL",

    "Add transcript_status column to chat_messages (Task 1a)" =>
        "ALTER TABLE chat_messages ADD COLUMN transcript_status ENUM('none','pending','done','failed') NOT NULL DEFAULT 'none'",

    "Add transcript_confidence column to chat_messages (Task 1a)" =>
        "ALTER TABLE chat_messages ADD COLUMN transcript_confidence DECIMAL(4,3) NULL",

    "Add transcript_started_at column to chat_messages (Task 1a)" =>
        "ALTER TABLE chat_messages ADD COLUMN transcript_started_at TIMESTAMP NULL",
    // TASK-1a END
];

foreach ($migrations as $desc => $sql) {
```


### `chat-widget.js`  →  live path `admin/chat-widget.js`

**Block 1 — Old code:**
```js
    return res.json();
  }

  function initials(name) {
    if (!name) return '?';
    const parts = name.trim().split(/\s+/).filter(Boolean);
```
**Block 1 — New code:**
```js
    return res.json();
  }

  // TASK-1b START — read-aloud playback (Admin-Project #2 Task 1b).
  // Uses the browser's native SpeechSynthesis API rather than a cloud TTS
  // service, per this task's instructions: it's been standard across all
  // major browsers since 2018, runs fully offline once the page has
  // loaded, has no character cap, and needs no account or API key — no
  // cloud fallback is used here since a usable on-device voice is the
  // common case for the languages this widget deals with. Applies to
  // whatever text is currently on-screen (a plain message or a
  // transcript), same function either way.
  function pcwSpeak(text) {
    if (!('speechSynthesis' in window) || !text) return;
    window.speechSynthesis.cancel(); // stop any speech already in progress first
    const utterance = new SpeechSynthesisUtterance(text);
    window.speechSynthesis.speak(utterance);
  }

  // Small reusable speaker-icon button that reads the given text aloud on
  // click. Built once per call site rather than shared/cloned, since each
  // needs its own closure over its own message's text.
  function buildReadAloudButton(getText) {
    const btn = el('button', { type: 'button', class: 'pcw-readaloud-btn', title: 'Read aloud' }, [
      el('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': '2', html:
        '<path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="M15.5 8.5a5 5 0 0 1 0 7"/>' }),
    ]);
    btn.addEventListener('click', () => pcwSpeak(getText()));
    return btn;
  }
  // TASK-1b END

  function initials(name) {
    if (!name) return '?';
    const parts = name.trim().split(/\s+/).filter(Boolean);
```

**Block 2 — Old code:**
```js
  .pcw-audio-bar .pcw-audio-track { flex: 1; height: 4px; background: rgba(0,0,0,0.12); border-radius: 2px; position: relative; }
  .pcw-audio-bar .pcw-audio-fill { position: absolute; left: 0; top: 0; height: 100%; background: #ED7624; border-radius: 2px; width: 0%; }

  .pcw-typing-dots { display: flex; gap: 3px; padding: 9px 13px; align-self: flex-start; }
  .pcw-typing-dots span { width: 6px; height: 6px; border-radius: 50%; background: #a89c8a; animation: pcwTypingBounce 1.2s infinite ease-in-out; }
  .pcw-typing-dots span:nth-child(2) { animation-delay: 0.15s; }
```
**Block 2 — New code:**
```js
  .pcw-audio-bar .pcw-audio-track { flex: 1; height: 4px; background: rgba(0,0,0,0.12); border-radius: 2px; position: relative; }
  .pcw-audio-bar .pcw-audio-fill { position: absolute; left: 0; top: 0; height: 100%; background: #ED7624; border-radius: 2px; width: 0%; }

  /* TASK-1a START — transcript control under an admin voice message
     (Admin-Project #2 Task 1a: admin -> customer transcription). */
  .pcw-transcript-wrap { margin-top: 6px; }
  .pcw-transcript-btn {
    display: inline-flex; align-items: center; gap: 5px;
    background: transparent; border: 1px solid rgba(16,33,75,0.2); color: #10214b;
    border-radius: 999px; padding: 4px 10px; font-size: 11px; font-weight: 600;
    cursor: pointer; font-family: inherit;
  }
  .pcw-transcript-btn:hover { background: rgba(16,33,75,0.06); }
  .pcw-transcript-btn svg { width: 12px; height: 12px; flex-shrink: 0; }
  .pcw-transcript-box {
    margin-top: 6px; background: #f4f1ea; border-radius: 8px; padding: 8px 10px;
    font-size: 12.5px; line-height: 1.5; color: #2c2416; user-select: text;
  }
  .pcw-transcript-lang {
    font-size: 10.5px; color: #7a6d5f; margin-top: 4px; font-style: italic;
  }
  /* TASK-1a REVISED (second verification round) — low-confidence warning
     tag, shown when Whisper's derived confidence heuristic (see
     chatTranscribeAudioFile() in chat-config.php) comes back under 0.5.
     Originally used an amber I invented myself; on review,
     admin-dashboard.html already has this project's real established
     warning convention (.pc-alert--warning / .pc-warning-box: #fff8e6
     background, #92400e text, #f59e0b left-border accent) — this widget
     has no such rule of its own to begin with, so matched to those exact
     values for consistency across the product rather than a second,
     slightly-different amber. Kept visually distinct from the red used
     for an outright transcription failure below. */
  .pcw-transcript-lowconf-tag {
    display: flex; align-items: center; gap: 5px; font-size: 10.5px; font-weight: 600;
    color: #92400e; background: #fff8e6; border-left: 3px solid #f59e0b; border-radius: 0 6px 6px 0;
    padding: 3px 7px; margin-top: 6px;
  }
  .pcw-transcript-copy {
    display: inline-flex; align-items: center; gap: 4px; margin-top: 5px;
    background: transparent; border: none; color: #ED7624; font-size: 11px;
    font-weight: 600; cursor: pointer; padding: 0; font-family: inherit;
  }
  .pcw-transcript-copy svg { width: 11px; height: 11px; }
  .pcw-transcript-error { color: #c0392b; font-style: italic; }
  /* TASK-1a END */

  /* TASK-1b START — read-aloud button, shared look for both a plain text
     bubble and a transcript box (Admin-Project #2 Task 1b). */
  .pcw-readaloud-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 20px; height: 20px; border-radius: 50%; border: none;
    background: transparent; color: #7a6d5f; cursor: pointer; padding: 0;
    flex-shrink: 0; vertical-align: middle;
  }
  .pcw-readaloud-btn:hover { background: rgba(0,0,0,0.06); color: #10214b; }
  .pcw-readaloud-btn svg { width: 13px; height: 13px; }
  /* TASK-1b END */

  .pcw-typing-dots { display: flex; gap: 3px; padding: 9px 13px; align-self: flex-start; }
  .pcw-typing-dots span { width: 6px; height: 6px; border-radius: 50%; background: #a89c8a; animation: pcwTypingBounce 1.2s infinite ease-in-out; }
  .pcw-typing-dots span:nth-child(2) { animation-delay: 0.15s; }
```

**Block 3 — Old code:**
```js
    if (msg.msg_type === 'text') {
      bodyTextEl.textContent = msg.body;
      bubble.appendChild(bodyTextEl);
    } else if (msg.msg_type === 'image') {
      const p = msg.media_path ? '/admin/' + msg.media_path : '';
      const img = el('img', { src: p, style: 'max-width:220px;border-radius:8px;display:block;cursor:pointer;' });
```
**Block 3 — New code:**
```js
    if (msg.msg_type === 'text') {
      bodyTextEl.textContent = msg.body;
      bubble.appendChild(bodyTextEl);
      // TASK-1b START — read-aloud only makes sense for messages the
      // customer is reading, i.e. from the admin/bot side (!isUser) —
      // there'd be nothing useful in reading the customer's own words
      // back to them. Also skips empty/deleted bodies implicitly since
      // pcwSpeak() no-ops on falsy text.
      if (!isUser && msg.body) {
        bubble.appendChild(buildReadAloudButton(() => msg.body));
      }
      // TASK-1b END
    } else if (msg.msg_type === 'image') {
      const p = msg.media_path ? '/admin/' + msg.media_path : '';
      const img = el('img', { src: p, style: 'max-width:220px;border-radius:8px;display:block;cursor:pointer;' });
```

**Block 4 — Old code:**
```js
      bubble.appendChild(buildAudioBar('/admin/' + msg.media_path));
    }
    col.appendChild(bubble);

    // ── Meta row: timestamp + persistent "Edited" tag (clickable to reveal
    //    the original pre-edit text). This row fades out and is replaced by
```
**Block 4 — New code:**
```js
      bubble.appendChild(buildAudioBar('/admin/' + msg.media_path));
    }
    col.appendChild(bubble);
    // TASK-1a START — transcript control only applies to this task's
    // direction: an admin's own voice message, viewed by the customer.
    // 'bot' never sends audio (text-only, per v2.1), so admin-only here
    // is the complete set for this task's scope. Placed after the bubble
    // so it renders directly beneath the audio player, not above it.
    if (msg.msg_type === 'audio' && msg.from_type === 'admin') {
      col.appendChild(buildTranscriptControl(msg));
    }
    // TASK-1a END

    // ── Meta row: timestamp + persistent "Edited" tag (clickable to reveal
    //    the original pre-edit text). This row fades out and is replaced by
```

**Block 5 — Old code:**
```js
    return wrap;
  }

  function buildDownloadChip(mediaPath, type, iconClass) {
    // Renders sent images/videos as a downloadable link rather than
    // inline media — reduces storage load, speeds up previous-chat history
```
**Block 5 — New code:**
```js
    return wrap;
  }

  // TASK-1a START — "View transcript" control for an admin's voice message,
  // shown to the customer. Fetches (and server-side caches) a text
  // transcription via chat-transcribe.php. Read-only and self-contained:
  // per Directive 17, this never translates anything — it just presents
  // the admin's own English speech as readable, selectable/copyable text,
  // since a human still has to manually translate it for the customer.
  function buildTranscriptControl(msg) {
    const wrap = el('div', { class: 'pcw-transcript-wrap' });
    const btn = el('button', { type: 'button', class: 'pcw-transcript-btn' }, [
      el('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': '2', html:
        '<path d="M4 6h16M4 12h10M4 18h7"/>' }),
      document.createTextNode('View transcript'),
    ]);
    wrap.appendChild(btn);

    let box = null; // built lazily on first click, then just toggled
    // TASK-1a REVISED (second verification round, part 2) — handle to the
    // most recent failure message, so a retry can remove it first. Before
    // this, every failed attempt appended a fresh error box that nothing
    // ever removed, so three failed clicks left three stacked error boxes.
    let errBox = null;

    btn.addEventListener('click', async () => {
      if (box) {
        // Already fetched once — just toggle visibility, no re-fetch.
        const showing = box.style.display !== 'none';
        box.style.display = showing ? 'none' : '';
        btn.textContent = '';
        btn.appendChild(el('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': '2', html:
          '<path d="M4 6h16M4 12h10M4 18h7"/>' }));
        btn.appendChild(document.createTextNode(showing ? 'View transcript' : 'Hide transcript'));
        return;
      }

      if (errBox) { errBox.remove(); errBox = null; } // TASK-1a REVISED — clear the previous failure first

      btn.disabled = true;
      btn.textContent = 'Transcribing...';

      try {
        const json = await apiPost('chat-transcribe.php', { chatId: state.chatId, messageId: msg.id });
        btn.disabled = false;

        if (!json.success) {
          errBox = el('div', { class: 'pcw-transcript-box pcw-transcript-error' },
            'Could not transcribe this voice message: ' + (json.error || 'unknown error'));
          wrap.appendChild(errBox);
          btn.textContent = '';
          btn.appendChild(document.createTextNode('Try again'));
          // box stays null on purpose: a failure is never cached client-side, so the next click is a real retry
          return;
        }

        box = el('div', { class: 'pcw-transcript-box' });
        const textEl = el('div', {}, json.transcript);
        box.appendChild(textEl);

        // TASK-1a REVISED — low-confidence warning, shown only when the
        // backend actually computed a heuristic (see chat-config.php) and
        // it came back under 0.5. Absent/null confidence (e.g. no usable
        // segments) shows nothing here rather than a false "low confidence"
        // claim — this is "uncertain," not "known to be inaccurate."
        if (typeof json.confidence === 'number' && json.confidence < 0.5) {
          box.appendChild(el('div', { class: 'pcw-transcript-lowconf-tag' },
            'This transcript may be inaccurate — the audio was hard to make out clearly.'));
        }

        // TASK-1b START — read-aloud on top of the transcribed text, same
        // as this task adds to plain text bubbles above. Placed here too
        // since the task explicitly asks for read-aloud "on top of
        // whatever text is on-screen (transcribed or eventually
        // translated)" — a transcript is exactly that.
        box.appendChild(buildReadAloudButton(() => json.transcript));
        // TASK-1b END

        if (json.preferredChatLanguage) {
          box.appendChild(el('div', { class: 'pcw-transcript-lang' },
            'Your selected chat language: ' + json.preferredChatLanguage));
        }

        const copyBtn = el('button', { type: 'button', class: 'pcw-transcript-copy' }, [
          el('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': '2', html:
            '<rect x="9" y="9" width="11" height="11" rx="1"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/>' }),
          document.createTextNode('Copy'),
        ]);
        // TASK-1a REVISED (second verification round) — added .catch(),
        // matching this project's own existing clipboard-copy pattern
        // (the debug-log copy button in admin-dashboard.html already pairs
        // every navigator.clipboard.writeText() with a .catch()). Without
        // it, a denied clipboard permission or an insecure (non-HTTPS)
        // context throws an unhandled promise rejection and the button
        // silently does nothing — the person clicking "Copy" never finds
        // out it didn't work.
        copyBtn.addEventListener('click', () => {
          navigator.clipboard.writeText(json.transcript).then(() => {
            copyBtn.lastChild.textContent = 'Copied';
            setTimeout(() => { copyBtn.lastChild.textContent = 'Copy'; }, 1500);
          }).catch(() => {
            dlog('transcript copy to clipboard failed', 'warn');
            copyBtn.lastChild.textContent = 'Copy failed';
            setTimeout(() => { copyBtn.lastChild.textContent = 'Copy'; }, 1500);
          });
        });
        box.appendChild(copyBtn);

        wrap.appendChild(box);
        btn.textContent = '';
        btn.appendChild(document.createTextNode('Hide transcript'));
      } catch (err) {
        btn.disabled = false;
        btn.textContent = 'Try again';
        dlog('transcript fetch failed: ' + err, 'error');
      }
    });

    return wrap;
  }
  // TASK-1a END

  function buildDownloadChip(mediaPath, type, iconClass) {
    // Renders sent images/videos as a downloadable link rather than
    // inline media — reduces storage load, speeds up previous-chat history
```


### `admin-dashboard.html`  →  live path `admin/index.html`

**Block 1 — Old code:**
```html
}
.cad-retry-btn:hover { background: #dc2626; }

.cad-msg-actions { display: none; gap: 4px; margin-top: 4px; }
.cad-msg-row:hover .cad-msg-actions.available { display: flex; }
.cad-msg-actions.tapped { display: flex; }
```
**Block 1 — New code:**
```html
}
.cad-retry-btn:hover { background: #dc2626; }

/* TASK-1a START — transcript control CSS, admin dashboard's own
   cad- namespace (Admin-Project #2 Task 1a). This page doesn't load
   chat-widget.js's stylesheet, so these mirror its .pcw-transcript-*
   rules under this page's own naming convention rather than depending
   on classes that don't exist here. */
.cad-transcript-wrap { margin-top: 6px; }
.cad-transcript-btn {
  display: inline-flex; align-items: center; gap: 5px;
  background: transparent; border: 1px solid rgba(16,33,75,0.2); color: #10214b;
  border-radius: 999px; padding: 4px 10px; font-size: 11px; font-weight: 600;
  cursor: pointer; font-family: inherit;
}
.cad-transcript-btn:hover { background: rgba(16,33,75,0.06); }
.cad-transcript-box {
  margin-top: 6px; background: #f4f1ea; border-radius: 8px; padding: 8px 10px;
  font-size: 12.5px; line-height: 1.5; color: #2c2416; user-select: text;
}
.cad-transcript-lang { font-size: 10.5px; color: #7a6d5f; margin-top: 4px; font-style: italic; }
/* TASK-1a REVISED (second verification round) — low-confidence warning
   tag, this page's own cad- namespace. Originally used an amber I
   invented myself; on review, this project already has an established
   warning convention right on this page — .pc-alert--warning and
   .pc-warning-box both use #fff8e6 background / #92400e text, and
   .pc-warning-box adds a #f59e0b left border as an accent — so this now
   matches those exact values instead of a close-but-different amber of
   my own choosing. */
.cad-transcript-lowconf-tag {
  display: flex; align-items: center; gap: 5px; font-size: 10.5px; font-weight: 600;
  color: #92400e; background: #fff8e6; border-left: 3px solid #f59e0b; border-radius: 0 6px 6px 0;
  padding: 3px 7px; margin-top: 6px;
}
.cad-transcript-copy {
  display: inline-flex; align-items: center; gap: 4px; margin-top: 5px;
  background: transparent; border: none; color: #ED7624; font-size: 11px;
  font-weight: 600; cursor: pointer; padding: 0; font-family: inherit;
}
/* TASK-1a END */

/* TASK-1b START — read-aloud button, this page's own cad- namespace
   (Admin-Project #2 Task 1b). Same look-and-feel intent as
   chat-widget.js's .pcw-readaloud-btn, kept separate since this page
   doesn't load that stylesheet. */
.cad-readaloud-btn {
  display: inline-flex; align-items: center; justify-content: center;
  width: 20px; height: 20px; border-radius: 50%; border: none;
  background: transparent; color: #7a6d5f; cursor: pointer; padding: 0;
  flex-shrink: 0; vertical-align: middle;
}
.cad-readaloud-btn:hover { background: rgba(0,0,0,0.06); color: #10214b; }
.cad-readaloud-btn i { font-size: 11px; }
/* TASK-1b END */
.cad-msg-actions { display: none; gap: 4px; margin-top: 4px; }
.cad-msg-row:hover .cad-msg-actions.available { display: flex; }
.cad-msg-actions.tapped { display: flex; }
```

**Block 2 — Old code:**
```html
  } else if (msg.msg_type === 'text') {
    bodyTextEl.textContent = msg.body || '';
    bubble.appendChild(bodyTextEl);
  } else if (msg.msg_type === 'image' || msg.msg_type === 'video' || msg.msg_type === 'audio') {
    const p = msg.media_path ? '/admin/' + msg.media_path : '';
    if (msg.msg_type === 'image') {
```
**Block 2 — New code:**
```html
  } else if (msg.msg_type === 'text') {
    bodyTextEl.textContent = msg.body || '';
    bubble.appendChild(bodyTextEl);
    /* TASK-1b START — read-aloud only makes sense for the admin reading
       the CUSTOMER's own words (!isAdmin) — reading the admin's own
       message back to them serves no purpose. Skips empty bodies
       implicitly since cadSpeak() no-ops on falsy text. */
    if (!isAdmin && msg.body) {
      bubble.appendChild(buildAdminReadAloudButton(() => msg.body));
    }
    /* TASK-1b END */
  } else if (msg.msg_type === 'image' || msg.msg_type === 'video' || msg.msg_type === 'audio') {
    const p = msg.media_path ? '/admin/' + msg.media_path : '';
    if (msg.msg_type === 'image') {
```

**Block 3 — Old code:**
```html
      const aud = document.createElement('audio');
      aud.src = p; aud.controls = true; aud.style.cssText = 'max-width:220px;height:36px;';
      bubble.appendChild(aud);
    }

    // Admin-only: delete button under their own sent media
```
**Block 3 — New code:**
```html
      const aud = document.createElement('audio');
      aud.src = p; aud.controls = true; aud.style.cssText = 'max-width:220px;height:36px;';
      bubble.appendChild(aud);
      /* TASK-1a START — transcript control for the admin's OWN sent voice
         message (Admin-Project #2 Task 1a: admin -> customer transcription).
         The admin needs their own transcript too, since they're the one who
         has to manually translate it (Directive 17 — this never translates
         anything itself, it only presents readable/selectable text). */
      if (isAdmin) {
        bubble.appendChild(buildAdminTranscriptControl(msg));
      }
      /* TASK-1a END */
      /* TASK-1b START — transcript control for a CUSTOMER's sent voice
         message (Admin-Project #2 Task 1b: customer -> admin
         transcription). Reuses the exact same buildAdminTranscriptControl()
         function as Task 1a above — chat-transcribe.php itself branches on
         the message's from_type to pick the right transcription
         model/path, so this call site doesn't need its own duplicate
         function or need to know which direction it's in. */
      if (!isAdmin) {
        bubble.appendChild(buildAdminTranscriptControl(msg));
      }
      /* TASK-1b END */
    }

    // Admin-only: delete button under their own sent media
```

**Block 4 — Old code:**
```html
  }
}

function startAdminPolling(chatId) {
  stopAdminPolling();
  chatAdmin.pollTimer = setInterval(() => {
```
**Block 4 — New code:**
```html
  }
}

/* TASK-1a START — "View transcript" control for the admin's own voice
   message (Admin-Project #2 Task 1a: admin -> customer transcription).
   Mirrors chat-widget.js's buildTranscriptControl on the customer side —
   same endpoint, same cached server-side result, so both sides always see
   identical text for the same message. The admin needs this because they
   are the one who has to manually translate the transcript (Directive 17
   — this control never translates anything itself). */
function buildAdminTranscriptControl(msg) {
  const wrap = document.createElement('div');
  wrap.className = 'cad-transcript-wrap';

  const btn = document.createElement('button');
  btn.type = 'button';
  btn.className = 'cad-transcript-btn';
  btn.textContent = 'View transcript';
  wrap.appendChild(btn);

  let box = null;

  btn.addEventListener('click', async () => {
    if (box) {
      const showing = box.style.display !== 'none';
      box.style.display = showing ? 'none' : '';
      btn.textContent = showing ? 'View transcript' : 'Hide transcript';
      return;
    }

    btn.disabled = true;
    btn.textContent = 'Transcribing...';

    try {
      const token = getAdminToken();
      const r = await fetch('chat-transcribe.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Admin-Token': token },
        body: JSON.stringify({ chatId: chatAdmin.activeThreadId, messageId: msg.id })
      });
      const json = await r.json();
      btn.disabled = false;

      if (!json.success) {
        btn.textContent = 'Try again';
        alert('Could not transcribe this voice message: ' + (json.error || 'unknown error'));
        return;
      }

      box = document.createElement('div');
      box.className = 'cad-transcript-box';

      const textEl = document.createElement('div');
      textEl.textContent = json.transcript;
      box.appendChild(textEl);

      /* TASK-1a REVISED — low-confidence warning, shown only when the
         backend actually computed a heuristic (see chatTranscribeAudioFile()
         in chat-config.php) and it came back under 0.5. Absent/null
         confidence (a customer-authored message, Task 1b's direction, or an
         admin one with no usable segments) shows nothing here — this is
         "uncertain," not "known to be inaccurate." Admin sees this because
         they're the one deciding whether to trust it enough to translate. */
      if (typeof json.confidence === 'number' && json.confidence < 0.5) {
        const lowConfTag = document.createElement('div');
        lowConfTag.className = 'cad-transcript-lowconf-tag';
        lowConfTag.textContent = 'Low confidence — please verify before translating';
        box.appendChild(lowConfTag);
      }

      /* TASK-1b START — read-aloud on top of the transcribed text, same
         helper this task adds to plain text bubbles elsewhere in this
         file. The task asks for read-aloud "on top of whatever text is
         on-screen (transcribed or eventually translated)" — a transcript
         is exactly that. */
      box.appendChild(buildAdminReadAloudButton(() => json.transcript));
      /* TASK-1b END */

      /* TASK-1b START — chat-transcribe.php now omits the
         preferredChatLanguage key entirely for a customer-authored
         message (Task 1b's direction has no language concept — the
         target is always English). Checking for the key's presence
         rather than truthiness of its value tells "not applicable"
         (Task 1b, key absent) apart from "applicable but not set yet"
         (Task 1a, key present with a null value). */
      if ('preferredChatLanguage' in json) {
        const langEl = document.createElement('div');
        langEl.className = 'cad-transcript-lang';
        langEl.textContent = json.preferredChatLanguage
          ? 'Customer\u2019s selected chat language: ' + json.preferredChatLanguage
          : 'Customer\u2019s chat language preference: not set yet';
        box.appendChild(langEl);
      }
      /* TASK-1b END */

      const copyBtn = document.createElement('button');
      copyBtn.type = 'button';
      copyBtn.className = 'cad-transcript-copy';
      copyBtn.textContent = 'Copy';
      /* TASK-1a REVISED (second verification round) — added .catch(),
         matching this exact page's own existing clipboard-copy pattern
         (the debug-log copy button a few thousand lines up already pairs
         navigator.clipboard.writeText() with a .catch() + window.dlog()).
         Without it, a denied clipboard permission throws an unhandled
         promise rejection and the admin never finds out the copy failed. */
      copyBtn.addEventListener('click', () => {
        navigator.clipboard.writeText(json.transcript).then(() => {
          copyBtn.textContent = 'Copied';
          setTimeout(() => { copyBtn.textContent = 'Copy'; }, 1500);
        }).catch(() => {
          window.dlog('Chat admin: transcript copy to clipboard failed', 'warn');
          copyBtn.textContent = 'Copy failed';
          setTimeout(() => { copyBtn.textContent = 'Copy'; }, 1500);
        });
      });
      box.appendChild(copyBtn);

      wrap.appendChild(box);
      btn.textContent = 'Hide transcript';
    } catch (e) {
      btn.disabled = false;
      btn.textContent = 'Try again';
      window.dlog('Chat admin: transcript fetch failed — ' + e, 'error');
    }
  });

  return wrap;
}
/* TASK-1a END */

/* TASK-1b START — read-aloud playback (Admin-Project #2 Task 1b). Uses
   the browser's native SpeechSynthesis API rather than a cloud TTS
   service, per this task's instructions: standard across all major
   browsers since 2018, runs fully offline once the page has loaded, no
   character cap, no account or API key needed. Mirrors chat-widget.js's
   pcwSpeak()/buildReadAloudButton() on the customer side — kept as its
   own copy here rather than a shared import, since this page doesn't
   load chat-widget.js. */
function cadSpeak(text) {
  if (!('speechSynthesis' in window) || !text) return;
  window.speechSynthesis.cancel(); // stop any speech already in progress first
  const utterance = new SpeechSynthesisUtterance(text);
  window.speechSynthesis.speak(utterance);
}

// Small reusable speaker-icon button that reads the given text aloud on
// click. Uses the dashboard's existing Font Awesome icon set (this page's
// established icon convention) rather than inline SVG, matching the
// pattern already used by delBtn/copyBtn above.
function buildAdminReadAloudButton(getText) {
  const btn = document.createElement('button');
  btn.type = 'button';
  btn.className = 'cad-readaloud-btn';
  btn.title = 'Read aloud';
  btn.innerHTML = '<i class="fas fa-volume-up"></i>';
  btn.addEventListener('click', () => cadSpeak(getText()));
  return btn;
}
/* TASK-1b END */

function startAdminPolling(chatId) {
  stopAdminPolling();
  chatAdmin.pollTimer = setInterval(() => {
```
## New files

- **`admin/chat-transcribe.php`** — New file, in `admin-project-2-task-1a.zip`
  (joined with this completion file in that same zip). On-demand
  transcription endpoint shared by both directions: looks up a voice
  message by `chatId`+`messageId`, returns a cached transcript
  immediately if one exists, otherwise calls Groq (via the functions
  added to `chat-config.php` below) and caches the result. Created by
  this task (Task 1a); Task 1b later extended its logic in place (see
  `Task-1b-Completion.md`) — the version in the zip already includes
  that extension, since only one final version of a new file is ever
  handed in.

## Bugs Found Outside Task Scope

Found during the second verification round, deliberately **not**
changed, per Scope Lock:

1. **Pre-existing `archived` migration in `chat-migrate.php` (~line 26)**
   uses `ALTER TABLE chat_threads ADD COLUMN IF NOT EXISTS archived ...`
   — `IF NOT EXISTS` on `ADD COLUMN` is MariaDB-only syntax and is a
   syntax error on real MySQL. This predates this task. If production is
   MySQL, that column was never created and anything depending on it has
   been failing silently. Suggested one-line fix for whoever owns that
   file: remove `IF NOT EXISTS` (the existing `[SKIP]` handling in the
   same script already covers safe re-runs without it).
2. **`PETCENTER_DESIGN_SYSTEM_v1.0.md`** was never provided in any
   session, although a project-wide directive says to consult it before
   UI decisions. Colors for this task's new UI were taken from the
   project's own real, existing CSS instead of guessing. If supplied
   later, a quick comparison pass against it would be cheap.

## Self-Check Confirmation

- Confirmed only the six files listed above were touched —
  `chat-admin-reply.php`, `chat-poll.php`, and `chat-admin-poll.php` were
  deliberately left untouched (transcription is on-demand, not hooked
  into send/poll, and `SELECT m.*` in the poll endpoints already
  surfaces the new columns with no edits needed there).
- Confirmed every block in the Code Changes section is wrapped in
  `TASK-1a` and/or `TASK-1b` anchor comments matching exactly what is in
  the delivered files, and that every "Old code" block was copied
  character-for-character from the original file this worker was given
  and verified (by script, not by eye) to occur exactly once in it.
- Confirmed `admin/chat-transcribe.php` in the zip is listed above under
  New files with a matching live path, and contains the one, final,
  fully-merged version of that file (Task 1a's original content plus
  Task 1b's extension plus every second-round fix) — not an intermediate
  draft.
- Re-ran a full PHP brace/paren balance check on all four touched PHP
  files, `node -c` on `chat-widget.js` and on both inline `<script>`
  blocks extracted from `admin-dashboard.html`, and a byte-level diff
  against the originally-provided files confirming zero unintended
  deletions, after assembling this consolidated version of the hand-in.
- Confirmed `admin-project-2-task-1a.zip` contains exactly this file
  (`Task-1a-Completion.md`) plus `admin/chat-transcribe.php`, and nothing
  else — checked with `unzip -l` before sending. Confirmed the project-
  number correction above was applied inside that real file's comments,
  not just in this document.
## SECOND VERIFICATION ROUND

**Context:** the CEO asked for a full, unhurried re-check of this task
against the project's authoritative docs (`adoption-website-project-summary.md`,
`ADMIN_DASHBOARD_PROJECT_SUMMARY.MD`, and this task's own section of
`ADMIN_PROJECTS_2_3_4_TASK_ASSIGNMENTS.md`), including whether the
originally-requested file list was actually complete. This re-read the
full Warning & Instructions section, the Shared Data Contract, Task 1a's
and Task 1b's text verbatim, and the `chat_messages` schema, then checked
the delivered code line by line against all of it. Five real issues were
found — one of them critical (Issue 1: the chosen transcription model had
actually been dead for over a year) — and fixed in Part 1 below. A second
pass the same day, asked to leave nothing pending, found and fixed four
more (Issues 6-9, "Part 2" below), and ends with an explicit ledger of
every open item so none can be overlooked. Nothing above this section was
rewritten; this section documents corrections on top of it.

### Issue 1 — CRITICAL: the chosen model, `distil-whisper-large-v3-en`, has been dead since August 2025

**What was wrong:** this is the most serious finding of this round. The
original submission's entire transcription feature was built on
`GROQ_STT_MODEL = 'distil-whisper-large-v3-en'`. A fresh, live fetch of
Groq's own documentation (`console.groq.com/docs/deprecations` and
`console.groq.com/docs/speech-to-text`, fetched directly during this
round, not recalled from earlier research) confirms Groq announced the
deprecation of this exact model and **shut it down entirely on
08/23/25** — over a year before this task was even assigned. Per Groq's
own stated policy, "requests to deprecated model IDs will return
errors." This means **every single transcription request this feature
makes would have failed outright** the moment a real `GROQ_API_KEY` was
added — not a degraded experience, a complete non-functional feature.
This was the single most severe thing wrong with the original
submission, and it was not caught by the original web search, which
confirmed the free-tier rate limits and the general existence of the
model but never specifically checked whether that exact model ID was
still live.

This was only caught because an unrelated memory-system version conflict
happened to surface a claim (from independent, parallel work on this same
task) that the model had been deprecated — prompting an immediate,
targeted verification rather than this being found through the planned
re-read of the project docs. Flagging that honestly: the process that
should have caught this (a web search before committing to a tool) did
not, on the original pass.

**Fix:** switched to `whisper-large-v3-turbo` — Groq's own named
replacement for the deprecated model, per their deprecation notice,
rather than an independent second guess. Re-verification also surfaced
that Groq's current lineup has exactly two speech-to-text models, and
**neither is English-only anymore** (the English-only tier Task 1a's
original reasoning relied on no longer exists at all) — both
`whisper-large-v3-turbo` and `whisper-large-v3` are multilingual, and the
choice between them now comes down to speed/accuracy trade-offs rather
than language support. Re-confirmed live that both share identical
free-tier rate limits (20 RPM, 2,000 RPD, 7,200 ASH, 28,800 ASD) — so the
original free-tier-quota research and the "genuinely recurring, not a
one-time credit" conclusion both remain correct and did not need
revising, only the specific model ID did. `language: 'en'` is still
passed on every request, which Groq's current docs confirm remains valid
and useful for improving accuracy/latency on a multilingual model when
the spoken language is already known.

[CODE BLOCK REMOVED — see consolidated Code Changes section above]

The surrounding comment block documenting this tool choice was rewritten
in place to explain the correction — not stripped, since future workers
reading this file need to know both what was decided and why the first
decision was wrong, not just the final answer. Task 1b's own model choice
(`whisper-large-v3`) was independently re-verified and confirmed
**unaffected** — it was never deprecated; only the distilled English-only
variant was.

### Issue 2 — race condition in `chat-transcribe.php`'s "claim" step

**What was wrong:** the original code set `transcript_status = 'pending'`
with a plain, unconditional `UPDATE ... WHERE id = ?`, then called Groq.
If the customer and the admin (or the same person, double-clicking)
both requested a transcript for the same not-yet-transcribed message
within the same few seconds, **both** requests would pass the "not done
yet" cache check, both would set the row to `pending` and both would
call Groq independently — wasting free-tier quota on a duplicate call,
with whichever response landed second silently overwriting the first in
the database.

**Fix:** the plain `UPDATE` was replaced with a conditional one —
`UPDATE ... WHERE id = ? AND transcript_status != 'pending'` — and the
code now checks `rowCount()`. Only one of two simultaneous requests can
ever win that conditional update (MySQL serializes concurrent writes to
the same row); the request that loses the race gets a plain "already
transcribing, try again shortly" response instead of placing its own
Groq call. (Part 1 originally accepted the resulting "stuck on pending
after a hard crash" case as a rare edge case. That was reversed in Part 2,
Issue 7 below, which adds a 120-second stale-claim recovery — nothing is
left accepted here any more.)

[CODE BLOCK REMOVED — see consolidated Code Changes section above]

### Issue 3 — invented warning colors instead of this project's actual ones

**What was wrong:** the low-confidence tag's amber (`#b45309` text on
`rgba(245,158,11,0.12)` background) was a color I chose myself. On
re-reading `admin-dashboard.html` closely, the project already has an
established warning convention used in two places on that same page —
`.pc-alert--warning` and `.pc-warning-box` — both `#fff8e6` background /
`#92400e` text, with `.pc-warning-box` adding a `#f59e0b` left-border
accent. My tag was close in hue but not actually consistent with the
real convention.

**Fix:** `.cad-transcript-lowconf-tag` (admin dashboard) and
`.pcw-transcript-lowconf-tag` (customer widget, which has no such rule of
its own to match, so this now sets the same brand-wide convention there
too) were both re-pointed to the exact existing values, with the same
left-border accent treatment as `.pc-warning-box`:

[CODE BLOCK REMOVED — see consolidated Code Changes section above]

Verified with a Playwright screenshot placing the new tag directly next
to the project's actual `.pc-warning-box` for visual comparison.

### Issue 4 — copy button didn't match this project's own existing clipboard-copy pattern

**What was wrong:** Directive 7 (never recreate a feature from scratch —
copy the project's own existing pattern) was followed loosely, not
exactly. `admin-dashboard.html` already has a clipboard-copy button
elsewhere (the debug-log copy button) that pairs
`navigator.clipboard.writeText()` with both a `.catch()` and a
`dlog()`/`window.dlog()` call. My transcript copy buttons in both
`chat-widget.js` and `admin-dashboard.html` had neither — a denied
clipboard permission or a non-HTTPS context would throw an unhandled
promise rejection, and the button would silently do nothing with no
feedback to whoever clicked it.

**Fix:** both copy buttons now match the established pattern exactly —
`.catch()` added, with a logged warning and a visible "Copy failed"
state that reverts after 1.5s, same timing as the existing "Copied"
success state.

[CODE BLOCK REMOVED — see consolidated Code Changes section above]

The `admin-dashboard.html` copy button got the identical fix, using
`window.dlog(...)` (that page's convention) instead of the widget's local
`dlog(...)`.

### Issue 5 — considered and deliberately NOT implemented: the "visual link" enhancement

Task 1a's own Recommended Enhancements suggests a visual link between a
voice message and an admin's later manual-translation follow-up (e.g. a
"translated version below" note), so the customer doesn't have to guess
that a later text message is the translation. On review, I confirmed this
was **not** implemented, and I'm deliberately leaving it that way:

- The `chat_messages` schema has a `reply_to_id` column, but it is
  completely unwired — no rendering, no compose-time UI, nowhere in
  either `chat-widget.js` or `admin-dashboard.html` reads or writes it
  (confirmed by grepping both files). There is no existing "reply to a
  specific message" feature to attach this to.
- Building this properly would mean designing a new reference mechanism
  from scratch (how the admin marks a follow-up as "this is the
  translation of that voice note") and new rendering logic in both
  files — a meaningfully bigger addition than the task's framing of a
  "small visual link," and arguably a second feature in its own right.
- The alternative — guessing via a heuristic like "the next text message
  from the same admin shortly after a voice message" — risks mislabeling
  an unrelated message as a translation, which is worse than having no
  link at all.

Per this doc's own instructions ("ignore, modify, or replace anything
here that doesn't hold up once you're looking at the real thing"), I'm
treating this specific suggestion as not holding up at the "small
enhancement" size it was framed at, and leaving it out rather than
building a mismatched version of it. Flagging this explicitly rather than
silently skipping it.

### Other things re-verified, no changes needed

- Re-confirmed `msg_type ENUM('text','image','audio','video')` and
  `from_type ENUM('user','admin','system','bot')` against
  `ADMIN_DASHBOARD_PROJECT_SUMMARY.MD`'s schema table — every value this
  task's code checks against (`'audio'`, `'admin'`, `'user'`) is exact.
- Re-confirmed zero emoji/pictograph characters were introduced by this
  task's own code in either `chat-widget.js` or `admin-dashboard.html` —
  scanned both files for the full emoji/symbol Unicode ranges; every
  match found (arrows, a checkmark/cross, a hamburger icon) existed in
  the original files before this task touched them (byte-identical
  counts before/after), none inside a `TASK-1a`/`TASK-1a REVISED` block.
- Re-confirmed via diff that no file outside this task's assigned zone
  (`chat-config.php`, `chat-setup.php`, `chat-migrate.php`,
  `chat-transcribe.php`, `chat-widget.js`, `admin-dashboard.html`) was
  touched, and that zero lines were unintentionally deleted from either
  `chat-widget.js` or `admin-dashboard.html` relative to the originally
  uploaded versions.
- `PETCENTER_DESIGN_SYSTEM_v1.0.md`, which a project-wide directive says
  to consult before any UI decision, was never provided to me in any
  session. Per the directive covering missing reference material, I did
  not guess at its contents; instead I derived colors from this
  project's own real, verifiable CSS (Issue 2 above), which should be
  consistent with that document even without having read it directly.
  Flagging again here in case Victor wants to hand it over for a future
  pass.

### Re-verification testing performed

- Full PHP brace/paren balance check on all four touched PHP files after
  every fix.
- `node -c` on `chat-widget.js` and on both inline `<script>` blocks
  extracted from `admin-dashboard.html`.
- Byte-level diff against the originally uploaded files confirming zero
  unintended deletions in either file, both before and after this
  round's fixes.
- A fresh Playwright render test asserting all five confidence/language
  display combinations (Task 1a high/low confidence with a language
  value present; Task 1b high/low confidence — now computed, post-fix —
  with no language key; and the exact `0.5` boundary, confirmed to NOT
  trigger the tag since the check is strictly `< 0.5`) — all five
  produced the correct show/hide result.
- A side-by-side Playwright screenshot comparing the corrected
  low-confidence tag against this project's actual `.pc-warning-box`,
  confirming the colors now genuinely match rather than merely looking
  similar.


## Part 2 — closing out every open item

### Issue 6 — migrations used MariaDB-only syntax and could silently never create the columns

**What was wrong:** my three original migration entries used
`ALTER TABLE ... ADD COLUMN IF NOT EXISTS`, copying the style of the
pre-existing `archived` entry. `IF NOT EXISTS` on `ADD COLUMN` exists
only in MariaDB; on real MySQL it is a syntax error. `chat-migrate.php`'s
loop catches every `PDOException` and prints `[SKIP]`, so on a MySQL
server the run would have looked successful while creating none of my
columns, and `chat-transcribe.php` would then fail on every request with
"unknown column". The platform is described as MySQL-backed, so this was
a live risk, not a theoretical one.

**Fix:** my entries are now plain `ADD COLUMN`. That works on both MySQL
and MariaDB and stays safe to re-run, because a repeat run raises
"duplicate column name", which the existing catch block already reports
as `[SKIP]`.

[CODE BLOCK REMOVED — see consolidated Code Changes section above]

### Issue 7 — a request that died mid-flight left a message stuck on "pending" forever

**What was wrong:** Part 1's atomic claim (Issue 2) fixed the double-call
race but introduced a downside I had written off as an "accepted rare
edge case": if a request died hard after claiming (PHP fatal, killed
worker, server restart), the row stayed `pending` and every later click
got "already transcribing" with no way out except editing the database.
That is exactly the kind of leftover that gets forgotten, so it is fixed
rather than accepted.

**Fix:** new `transcript_started_at` column, stamped at claim time. The
claim now also succeeds when a `pending` row is older than 120 seconds.
Groq's call has a 60-second cURL timeout, so a legitimately in-flight
request can never be that old; only a dead one can.

[CODE BLOCK REMOVED — see consolidated Code Changes section above]

`chat-setup.php` and `chat-migrate.php` both gained
`transcript_started_at TIMESTAMP NULL`.

### Issue 8 — the unauthenticated endpoint had no rate limit

**What was wrong:** `chat-transcribe.php` is deliberately callable with
just a `chatId` (customers have no login), and every uncached call spends
real free-tier quota — 2,000 requests/day for the whole account. Failed
transcriptions are never cached, so anyone holding a chatId could loop a
failing request and exhaust the day's quota for every customer and admin.
This project already has `chatCheckRateLimit()` and `chat-start.php`
already uses it for the same reason (flooding), so leaving this endpoint
without one was an inconsistency, not a design choice.

**Fix:** same helper, same IP-keyed pattern as `chat-start.php`: 40
requests per 10 minutes per IP, returning HTTP 429 with a readable
message. 40 is well above normal use, including several admins sharing
one office IP. Note it counts cache hits too, which is intentional (it is
a flood guard, not a quota meter).

[CODE BLOCK REMOVED — see consolidated Code Changes section above]

### Issue 9 — failed transcript attempts stacked error boxes in the customer widget

**What was wrong:** found while tracing how the new 409/429 responses
would display. On each failed click, `buildTranscriptControl()` appended a
new error box and nothing ever removed it, so three failed clicks left
three stacked boxes.

**Fix:** the widget now holds the latest error box in `errBox` and removes
it before every retry.

[CODE BLOCK REMOVED — see consolidated Code Changes section above]

(The admin dashboard version reports failures with `alert()`, matching
`deleteAdminMessage()`, so it never stacked and needed no change.)

### Testing performed for Part 2

- Extracted the real shipped `buildTranscriptControl()` from
  `chat-widget.js` and the real `buildAdminTranscriptControl()` from
  `admin-dashboard.html` (not retyped copies) into Playwright pages with
  stubbed API responses. Widget: fail, fail, then success with low
  confidence — exactly one error box after each failure, none after
  success, then one transcript box with the warning tag, language line and
  read-aloud button, and a hide/show toggle that made no extra API call.
  Dashboard: failure (alert shown, no box), a customer-direction response
  (warning tag, no language line, read-aloud present) and an
  admin-direction response with no language set (no tag, "not set yet"
  line) — all correct.
- PHP brace/paren balance on all four PHP files; `node -c` on
  `chat-widget.js` and the dashboard's inline scripts; zero lines removed
  from any original file; every `TASK-1a`/`TASK-1b` START/END anchor
  balanced in every file.
- **Not possible in this environment, stated plainly:** the sandbox has no
  PHP interpreter and no MySQL, so none of the PHP (including the new
  claim SQL, the rate-limit call and the migrations) could be executed.
  Those were verified by careful reading and structural checks only.
  A live end-to-end run with a real key and database is still required.

(Already listed in the top-level "Bugs Found Outside Task Scope" section above — both were found during this round.)

### Open-items ledger — nothing left pending in code

| Item | Status |
|---|---|
| Dead transcription model | Fixed (Issue 1) |
| Duplicate-call race | Fixed (Issue 2) |
| Stuck "pending" after a crash | Fixed (Issue 7) |
| Unauthenticated endpoint flood risk | Fixed (Issue 8) |
| MySQL-incompatible migrations | Fixed (Issue 6) |
| Warning colors, copy `.catch()`, stacked error boxes | Fixed (Issues 3, 4, 9) |
| Visual link between voice note and translation | **Declined, with reasoning (Issue 5)** — needs new reply infrastructure, a separate feature |
| `archived` migration syntax | Outside scope, flagged above |
| Design-system document | Not provided, flagged above |
| Fill in `GROQ_API_KEY` in `admin/chat-config.php` on the live site | **Action for Victor** — free key at console.groq.com/keys |
| Run `admin/chat-migrate.php` once on the live database, then delete `admin/chat-setup.php`/`admin/chat-migrate.php` from the live site per the delivery guide's note | **Action for Victor** |
| Live end-to-end test with a real key and database | **Action for Victor/QA** — not possible in the sandbox |
| Test the multilingual model against real sample customer audio | **Action for Victor/QA** — no samples available here |

### Self-Check Confirmation (Second Verification Round)
- Confirmed every fix in this round stayed within this task's original
  file zone — no new files outside it, nothing touched in
  `chat-admin-reply.php`, `chat-poll.php`, or `chat-admin-poll.php`.
- Confirmed every changed line in this round is marked `TASK-1a REVISED
  (second verification round)` or simply folded into an existing `TASK-1a
  REVISED` block with an updated explanation — distinguishable from both
  the original submission and the first revision.
- Confirmed the Issue 2 fix (race condition) and Issue 4 fix (copy
  button) in `chat-transcribe.php`/the shared files also correctly
  protect Task 1b's direction, since both touched code paths are shared
  by both directions — cross-referenced in Task 1b's own completion file.
- Confirmed the Issue 1 model fix (`whisper-large-v3-turbo`) was applied
  only to `GROQ_STT_MODEL`/Task 1a's own `chatTranscribeAudioFile()` —
  Task 1b's separate `GROQ_MULTILINGUAL_STT_MODEL` constant and
  `chatTranscribeCustomerAudioFile()` function were independently
  re-verified as already correct (`whisper-large-v3`, never deprecated)
  and were not touched by this fix.
- Re-ran the full PHP brace/paren balance check and `node -c` syntax
  checks on every touched file after this fix landed alongside the other
  four, confirming nothing broke when all five were combined.
- Confirmed Part 2 (Issues 6-9) touched only files already in this task's
  zone, and that every Part 2 change is marked `TASK-1a REVISED (second
  verification round, part 2)` or sits inside an existing `TASK-1a` block.