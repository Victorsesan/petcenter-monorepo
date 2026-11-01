# Task Completion — Task 1b

## Task Number
Admin-Project #2 — Task 1b (Transcription Build, customer → admin direction, plus two-way read-aloud), quoted from `ADMIN_PROJECTS_2_3_4_TASK_ASSIGNMENTS.md`: "TASK 1b — Transcription Build (Customer → Admin Direction) & Read-Aloud Playback"

**Correction flagged on this hand-in pass:** earlier versions of this
file labeled this Admin-Project #4; that was wrong. Full reasoning for
the correction (now Admin-Project #2) is in `Task-1a-Completion.md`'s
matching note — not repeated here.

**Hand-in packaging (corrected on this pass):** this file ships alone in
its own zip, `admin-project-2-task-1b.zip` — this task created no new
files, but per the current delivery instructions the completion file
still goes in a zip of its own rather than being sent loose.

## What Was Required
Build transcription for the reverse direction from Task 1a: when a
customer sends a voice message, the admin must be able to click to see a
text transcription of it, so the admin can understand what the customer
said even if it's in another language — `preferred_chat_language` is not
needed for this direction since the target is always English. Per
Directive 17, present the transcript for manual translation rather than
building or calling anything resembling a translation API — this task
uses the transcription endpoint only, never a speech-to-English
translation endpoint. Separately, add read-aloud playback via
text-to-speech, in both directions, on top of whatever text is currently
on-screen (a plain message or a transcript).

This task's starting point was Task 1a's already-submitted version of the
shared files (`chat-config.php`, `chat-transcribe.php`, `chat-widget.js`,
`admin-dashboard.html`), per the Sequential File Ownership Chain.

**Note on this hand-in:** this task and Task 1a were both completed by
the same worker, and every file this task touches is also touched by
Task 1a — there is no file that belongs only to this task. Per
`WORKER-DELIVERY-GUIDE.md`'s one-old-to-one-new rule, a changed file's
mergeable code is handed in exactly once, so **all of this task's code
is consolidated into `Task-1a-Completion.md`'s Code Changes section**
(clearly marked there with `TASK-1b` anchor comments wherever it
appears), rather than being repeated here with old-snippets that would
no longer match the live file once Task 1a's own edits have already been
applied to it. This file documents this task's own reasoning, tool
choice, what was found on re-verification, and testing — and points back
to Task 1a's file for every actual code block.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `chat-config.php` | `admin/chat-config.php` | Changed | Yes — code consolidated in `Task-1a-Completion.md` |
| `chat-transcribe.php` | `admin/chat-transcribe.php` | Changed (Task 1a's new file, extended) | Yes — created by Task 1a; new-file zip entry and code consolidated in `Task-1a-Completion.md` |
| `chat-widget.js` | `admin/chat-widget.js` | Changed | Yes — code consolidated in `Task-1a-Completion.md` |
| `admin-dashboard.html` | `admin/index.html` | Changed | Yes — code consolidated in `Task-1a-Completion.md` |

`chat-setup.php` / `chat-migrate.php` were not touched by this task — the
`transcript`/`transcript_status`/`transcript_confidence`/`transcript_started_at`
columns Task 1a added to `chat_messages` are generic enough to serve both
directions with no schema change.

## Transcription tool selected (documented per task instructions)

**Groq's hosted Whisper endpoint** — same account, same free tier, same
endpoint as Task 1a — but a different model: **`whisper-large-v3`**
(Groq's general multilingual model), confirmed via web search on
2026-09-11 (GroqDocs: 99+ languages supported, ~10.3% word error rate).

- **Why not Task 1a's model:** admins are English-only; a customer may
  speak any language, so this uses the general multilingual model and
  does **not** pass a `language` parameter — Whisper auto-detects
  whatever language was spoken.
- **Renewal terms:** identical to Task 1a's — Groq's free tier limits are
  per-account, not per-model, so this shares the exact same recurring,
  no-credit-card, non-expiring rate limits. No new signup or billing
  consideration.
- **Per Directive 17:** this calls Groq's *transcription* endpoint only,
  never Groq/Whisper's separate *translation* endpoint. The output is a
  transcript in whatever language the customer spoke, for the admin to
  manually translate themselves.
- **Honest limitation:** not tested against real recorded sample audio in
  Pet Center customers' likely languages — no audio samples were
  available in this environment. Verified only against Groq's own
  published benchmarks.

For read-aloud, no new tool was selected — this uses the browser's
built-in `SpeechSynthesis` API, which needs no account, no API key, and
runs fully offline once the page has loaded.

## Code Changes

All of this task's mergeable code lives in `Task-1a-Completion.md`'s
Code Changes section, inside `TASK-1b START`/`TASK-1b END` anchor
comments (or, for the second-round confidence fix below, `TASK-1b
REVISED` comments) — see that file. Nothing is repeated here on purpose:
every location this task touches was also touched by Task 1a, so
documenting it twice, each time as its own old→new pair, would give the
integrator two instructions for the same spot in the same file — the
second one's "old" text would no longer exist once the first had been
applied. Summary of what to look for there: the new
`chatTranscribeCustomerAudioFile()` function and
`GROQ_MULTILINGUAL_STT_MODEL` constant in `chat-config.php`; the
multi-language branch and widened validation in `chat-transcribe.php`;
`pcwSpeak()`/`buildReadAloudButton()` and their wiring into text bubbles
and the transcript box in `chat-widget.js`; and `cadSpeak()`/
`buildAdminReadAloudButton()`, the second `buildAdminTranscriptControl()`
call site for customer-sent audio, and the same read-aloud wiring in
`admin-dashboard.html`.

## New files
None — this task extends Task 1a's new file (`admin/chat-transcribe.php`)
rather than creating one of its own. That file's single, final, zip-ready
version (including this task's extension) is listed under Task 1a's "New
files" section.

## Bugs Found Outside Task Scope
Same two items as `Task-1a-Completion.md` (the pre-existing `archived`
migration's MariaDB-only syntax, and the never-provided design-system
document) — found while re-verifying these same shared files, not
repeated in full here. Nothing additional found specific to this task's
own scope.

## Self-Check Confirmation
- Confirmed this task's own contribution — everywhere marked with a
  `TASK-1b` anchor comment in the delivered files — is accurately
  described above and fully present in `Task-1a-Completion.md`'s
  consolidated Code Changes section; spot-checked by searching the
  delivered files for every `TASK-1b` anchor and confirming each has a
  matching block there.
- Confirmed no file was created or listed here that duplicates what is
  already in Task 1a's "New files" section.
- Confirmed `chat-setup.php`/`chat-migrate.php` were correctly left out
  of this task's Files Changed table, matching that this task made no
  schema changes.

## SECOND VERIFICATION ROUND

**Context:** the CEO asked for a full, unhurried re-check of this task
against the project's authoritative docs and this task's own section of
`ADMIN_PROJECTS_2_3_4_TASK_ASSIGNMENTS.md`, re-read verbatim. One real gap
was found and fixed in this task's own scope (the confidence-mirroring
enhancement below); this task's own model choice was also independently
cross-checked against a critical model-deprecation issue Task 1a's own
re-check surfaced, and confirmed unaffected (see below). Nothing above
this section was rewritten.

### Issue — this task's own Recommended Enhancement ("same low-confidence
flag as Task 1a, mirrored for this direction") was never implemented

**What was wrong:** Task 1a's completion file (first revision, written
before this re-check) explicitly noted: *"I don't need to switch to
synchronous-at-send-time architecture... [confidence] could inform Task
1b too"* — but that forward-looking note was never actually acted on.
Task 1b's own Recommended Enhancements section literally says: *"Same
low-confidence flag as Task 1a, mirrored for this direction."* This is
not an optional suggestion I'm choosing to skip (unlike the "visual link"
item in Task 1a's own enhancements, which I evaluated and declined with
reasoning in that task's completion file) — it's a direct instruction to
mirror a specific, already-built piece of behavior, and it was simply
missed on the original pass.

**Fix:** `chatTranscribeCustomerAudioFile()` in `chat-config.php` now
computes the exact same confidence heuristic as Task 1a's
`chatTranscribeAudioFile()` — `response_format` changed from `json` to
`verbose_json`, confidence derived as the average of `exp(avg_logprob)`
across returned segments. No changes were needed anywhere else: because
Task 1a's original design already read `$result['confidence'] ?? null`
generically in `chat-transcribe.php` (regardless of which function
produced `$result`), and because both the widget's and the dashboard's
low-confidence tag logic already check `typeof json.confidence ===
'number' && json.confidence < 0.5` with no direction-specific branching,
this function returning a real number instead of always being absent is
the only change required for the warning tag to now also appear for a
customer's low-confidence voice message.

[CODE BLOCK REMOVED — see consolidated Code Changes section in Task-1a-Completion.md]

A now-stale comment in `chat-transcribe.php` (which previously said
confidence "only exists on chatTranscribeAudioFile()'s (admin-direction)
return value today") was also corrected, since that statement is no
longer true after this fix — a comment contradicting the code it sits
next to is its own small bug.

### Cross-check: Task 1a's second-round discovery that `distil-whisper-large-v3-en` is dead

Task 1a's own second verification round found that the model it had
originally chosen, `distil-whisper-large-v3-en`, was deprecated and fully
shut down by Groq on 08/23/25 — confirmed via a fresh live fetch of
Groq's own documentation. Since this task's `chatTranscribeCustomerAudioFile()`
uses a different model ID (`whisper-large-v3`), it was independently
re-verified during this same round rather than assumed safe by
association: Groq's current deprecation history (fetched live, the full
list, not a snippet) does **not** list `whisper-large-v3` anywhere — only
the distilled English-only variant was ever deprecated. This task's model
choice required no change. Full detail on the deprecation and the fix
Task 1a applied is in that task's own completion file, not repeated here.

### Shared-file fixes from Task 1a's second verification round that also apply here

Task 1a's own second-round re-check found and fixed two issues in files
this task shares: a race condition in `chat-transcribe.php`'s
pending-status claim (two near-simultaneous "View transcript" clicks on
the same not-yet-transcribed message could both trigger a Groq call), and
a missing `.catch()` on the transcript copy buttons in both
`chat-widget.js` and `admin-dashboard.html`. Both fixes are
direction-agnostic — they protect a customer-authored message exactly the
same way they protect an admin-authored one — so this task's own
transcript-viewing flow benefits from them with no separate changes
needed here. Full detail and the exact before/after code for both is in
Task 1a's completion file, not duplicated here to avoid two copies of the
same explanation drifting apart over time.

### Re-verification testing performed

- PHP brace/paren balance check on `chat-config.php` after the fix.
- A fresh Playwright assertion specifically covering this task's
  direction: a customer-authored message with low confidence now shows
  the warning tag; one with high confidence does not; neither shows a
  `preferredChatLanguage` line (that key is still correctly absent for
  this direction, confirmed unaffected by the confidence fix).
- Re-ran `node -c` on `chat-widget.js` and both inline `<script>` blocks
  in `admin-dashboard.html`, and a byte-level diff against the originally
  uploaded files, after all of this round's combined changes (this task's
  fix plus Task 1a's two shared-file fixes) landed together.


### Part 2 fixes from Task 1a's file that also protect this direction

Task 1a's Part 2 (documented there in full, not duplicated) added a
stale-`pending` recovery window, an IP rate limit on
`chat-transcribe.php`, MySQL-portable migrations and a widget error-box
fix. All four apply equally to customer-authored messages. Specifically
relevant here: a customer voice message whose transcription request died
mid-flight would otherwise have stayed stuck on "pending"; it now becomes
retryable after 120 seconds.

### Bugs Found Outside Task Scope (Second Verification Round)
Same two items as listed in Task 1a's file (the pre-existing `archived`
migration's MariaDB-only syntax, and the never-provided design-system
document). Nothing additional found in this task's own scope.

### Open items specific to this task
- Test `whisper-large-v3` against real recorded customer audio in the
  languages Pet Center customers actually use — **action for Victor/QA**;
  no samples exist in the sandbox, and the confidence tag is a heuristic
  that should be re-tuned against real results.
- No other items pending.

### Self-Check Confirmation (Second Verification Round)
- Confirmed the only file this task's own fix touched is
  `chat-config.php`, and only inside `chatTranscribeCustomerAudioFile()`
  — the function this task originally created. No other function in that
  file was altered by this fix.
- Confirmed the fix is marked `TASK-1b REVISED (second verification
  round)` throughout, distinguishable from the original `TASK-1b START`/
  `END` submission.
- Confirmed this task's Recommended Enhancements are now fully
  addressed: the low-confidence flag (fixed this round), the copy control
  (already shared with Task 1a's function from the original submission),
  and the real-sample-audio testing gap (still honestly flagged as not
  possible in this environment — unchanged from the original submission,
  not something a code fix can close).
- Confirmed `GROQ_MULTILINGUAL_STT_MODEL` (`whisper-large-v3`) was
  checked against Groq's complete, freshly-fetched deprecation history
  during this round and does not appear anywhere in it — this task's
  model choice needed no change.