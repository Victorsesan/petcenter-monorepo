- **Task Number** — Task 4 — BNPL Status Backend
  (from `Unified-Status-Dashboard-Task-Tracker-v3-FINAL.md`)

- **What Was Required** — Build the backend persistence layer for BNPL
  (installment plan) status records, analogous to how adoption application
  status works. Concretely: (1) a per-`submission_id` flat JSON status record
  on disk, (2) a write endpoint admins call to approve or reject a submission,
  (3) a read-only internal endpoint for Task 7's cross-domain proxy on
  `status.petcenterco.com` to call over HTTPS (since PHP cannot `require_once`
  across servers), (4) strict validation of the `submission_id` format before
  it is ever used to build a file path, (5) an append-only `history[]` audit
  trail (who changed it, old→new, when), (6) `flock()` around every
  read-modify-write so two concurrent admin actions on the same record can't
  race, and (7) a hook in `submit-installment.php` to create the initial
  "pending" record at submission time so every accepted submission has a record
  from the start. On-disk field names follow the project's Shared Data
  Contracts section exactly: `submission_id`, `status` (lowercase
  `pending`/`approved`/`rejected`), `approved_by`, `approved_at`,
  `rejection_notes`, `history` (array of `{from, to, by, at}`).

- **⚠️ NEW FOLDER NOT IN EITHER SUMMARY DOC — FLAGGING HEAVILY** — This task
  needed a folder to put its new files in, and `installments/` does **not**
  appear anywhere in the "1. Server Folder Structure" section of
  `adoption-website-project-summary.md` (grepped in full — confirmed absent)
  or in `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` (grepped in full — no
  "installment" mentions at all). **This folder had to be invented by this
  worker** because no existing path was documented for the installment/BNPL
  feature's backend files, and the CEO subsequently confirmed directly (see
  conversation history) that this invented structure should stand as the
  canonical layout going forward — but it is still new, unverified-by-docs
  ground, so the integrator must create it.

  **Integrator action needed:** create a new top-level folder at
  `public_html/adopt/installments/` (sibling of `/apply/`, `/adoptions/`,
  `/pets/`, etc. — same level in the tree) and place the three new files
  below inside it, plus the already-existing `submit-installment.php` (which
  this worker found already deployed there, per the live file supplied for
  this task — i.e. `installments/` as a folder already existed in production
  holding `submit-installment.php`; only the folder's *documentation* in the
  two summary docs was missing, not the folder itself on the server). A new
  subfolder `installments/bnpl_status/` is also created automatically at
  runtime by `bnpl-status-lib.php` (`bnpl_ensure_status_dir()`, `mkdir` with
  `0755`) the first time a status record is written — no manual folder
  creation needed for that one, but flagging it here so the integrator isn't
  surprised to see it appear.

- **Files Changed**

  | Source file name | Live path | Changed or New | Shared with another task? |
  |---|---|---|---|
  | `bnpl-status-lib.php` | `installments/bnpl-status-lib.php` | New | Yes — Tasks 5, 6, 7 all `require_once` this file; it is the single source of truth for the on-disk BNPL status schema and locking helpers |
  | `update-bnpl-status.php` | `installments/update-bnpl-status.php` | New | No — this is Task 4's own admin write endpoint; later tasks call it or read the records it produces, they don't edit it |
  | `bnpl-status-read.php` | `installments/bnpl-status-read.php` | New | Yes — built specifically so Task 7's proxy on `status.petcenterco.com` can fetch a BNPL record over HTTPS |
  | `submit-installment.php` | `installments/submit-installment.php` | Changed | No — no other task touches this file |

- **Code Changes**

  ### `submit-installment.php`  →  live path `installments/submit-installment.php`

  **Change 1 — require the new status-backend library right after config is loaded**

  <!-- TASK-4 START -->
  **Old code:**
  ```php
  if (file_exists(__DIR__ . '/config.php')) {
      require_once __DIR__ . '/config.php';
  }

  $admin_defaults = ['sales@petcenterco.com', 'zeusvictor88@gmail.com'];
  ```
  **New code:**
  ```php
  if (file_exists(__DIR__ . '/config.php')) {
      require_once __DIR__ . '/config.php';
  }

  // TASK-4 START: BNPL status backend -- shared schema/locking helpers, used further
  // below (after the submission is fully accepted) to create this submission's initial
  // "pending" status record.
  require_once __DIR__ . '/bnpl-status-lib.php';
  // TASK-4 END

  $admin_defaults = ['sales@petcenterco.com', 'zeusvictor88@gmail.com'];
  ```
  <!-- TASK-4 END -->

  **Change 2 — create the initial status record once the submission is fully accepted**

  <!-- TASK-4 START -->
  **Old code:**
  ```php
  @file_put_contents($LOG_FILE, date('c') . " | {$sid} | {$user_email} | {$plan_label} | \${$amount} | " . implode(',', $filenames) . PHP_EOL, FILE_APPEND | LOCK_EX);

  // ── Success ───────────────────────────────────────────────────────────────────
  ```
  **New code:**
  ```php
  @file_put_contents($LOG_FILE, date('c') . " | {$sid} | {$user_email} | {$plan_label} | \${$amount} | " . implode(',', $filenames) . PHP_EOL, FILE_APPEND | LOCK_EX);

  // TASK-4 START: create the initial BNPL status backend record ("pending") now that this
  // submission has been fully accepted (every validation/upload step above already
  // succeeded). Any later admin approve/reject action reads and updates this same record
  // via update-bnpl-status.php.
  bnpl_create_initial_status($sid);
  // TASK-4 END

  // ── Success ───────────────────────────────────────────────────────────────────
  ```
  <!-- TASK-4 END -->

  Placement note: the call sits after the log write and immediately before the
  success response — i.e. only after every validation and upload step above it
  has already succeeded (missing required files, oversized files, bad
  MIME/extension, etc. all `json_out(false, ...)` and `exit` earlier in the
  file). A rejected or incomplete submission never gets a status record created.

- **New files** — in the zip

  - `installments/bnpl-status-lib.php` — Shared library defining the on-disk BNPL status record schema (`submission_id`, `status`, `approved_by`, `approved_at`, `rejection_notes`, `history[]`) and providing race-safe read/write helpers (`bnpl_create_initial_status`, `bnpl_write_status`, `bnpl_read_status`) using `flock()`; every other task that reads or writes a BNPL status record must `require_once` this file. **New folder note:** lives in the new `installments/` folder — see the flagged section above.
  - `installments/update-bnpl-status.php` — Admin POST endpoint for BNPL status transitions (approve/reject); authenticates using `ADMIN_TOKEN` from `/adopt/admin/.env`; calls `bnpl_write_status()` with a mutator that sets status fields and appends a `{from, to, by, at}` entry to `history[]`. **New folder note:** same `installments/` folder.
  - `installments/bnpl-status-read.php` — Internal GET-only read endpoint for Task 7's cross-domain proxy; authenticates with a separate `BNPL_INTERNAL_READ_KEY` secret (also from `/adopt/admin/.env`) using `hash_equals()` for timing-safe comparison; calls `bnpl_read_status()` only, never writes. **New folder note:** same `installments/` folder.

  No `.html` pages were created by this task, so the strict `index.html`
  naming rule for live pages does not apply to any of the above — all three
  new files are PHP backend endpoints, not browser-served pages.

- **Bugs Found Outside Task Scope** — None found.

- **Pending / Open Items (not yet resolved — documented so the hand-in is not silently incomplete)**

  1. **`BNPL_INTERNAL_READ_KEY` value must be generated and set by CEO/ops.**
     `bnpl-status-read.php` reads this secret from `/adopt/admin/.env`. The key
     does not exist yet — it needs to be generated (any strong random string,
     e.g. `openssl rand -hex 32`) and added as a new line
     `BNPL_INTERNAL_READ_KEY=<value>` in that file, alongside the existing
     `ADMIN_TOKEN` entry, before Task 7's proxy can authenticate against this
     endpoint for real. This worker has no access to the live `.env` and
     cannot set this itself.
  2. **No PHP interpreter was available in this sandbox at any point**, so
     `php -l` / a live-request smoke test was never run against any of the
     four files. Structural lint only (balanced `{}`/`()`/`[]`, valid opening
     `<?php` tag) was performed as a substitute. Recommend a real `php -l`
     pass or one live test submission + one live approve/reject call before
     this goes fully live.
  3. **`installments/` folder documentation gap** — flagged in full above.
     Not a bug in this task's own work, but something the integrator/CEO
     should fold back into `adoption-website-project-summary.md`'s Server
     Folder Structure section so the next worker who touches BNPL files
     doesn't hit the same undocumented-path problem this task did.

- **Self-Check Confirmation** — Only the four files listed above were touched:
  `bnpl-status-lib.php` (new), `update-bnpl-status.php` (new),
  `bnpl-status-read.php` (new), and `submit-installment.php` (two-change
  modification). No other project file was modified. Every code block added to
  the existing file carries `<!-- TASK-4 START -->` / `<!-- TASK-4 END -->`
  (Markdown) wrapping `// TASK-4 START` / `// TASK-4 END` (PHP) anchor
  comments; every new file's added blocks carry the same PHP anchors
  internally. Both "Old code" snippets were copied character-for-character
  from the original `submit-installment.php` as uploaded (verified
  line-by-line against the source) and each appears only once in that file.
  The `submission_id` regex (`^IP-[0-9A-F]{12}$`) was checked against every
  real ID in the supplied `installment_submissions.log` and matched all seven.
  This completion file plus all three new files are packaged together in
  `Unified-Status-Dashboard-Task-4.zip`, per the current delivery format (new
  files and completion doc combined in one zip, named for the project and
  task number). `submit-installment.php` (an existing, changed file) is
  **not** in the zip — its changes are documented above as old/new code
  blocks only, per the rule that changed existing files never go in the zip.
  No `.env`, `config.php`, `pet.json`, or files under `applications/` or
  `uploads/` are included anywhere in the zip.
