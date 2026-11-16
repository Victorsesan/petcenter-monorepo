# PetCenter AI Integrator Takeover Prompt

You are continuing work in the GitHub repository https://github.com/Victorsesan/petcenter-monorepo.

## Start here — mandatory

1. Read `INTEGRATION-PROGRESS.md` at the repository root. It is the central project handover and progress ledger.
2. Read `TODO.md` for the issue inbox and communication history. Do not treat it as the progress ledger.
3. Read `WEB-PROJECTS-1-2-TASK-ASSIGNMENTS-v13.md`. Version 13 is authoritative; older task versions and completion reports are historical only.
4. Read `INTEGRATOR-GIT-SYNC-MERGE-PROTOCOL.md`, `WORKER-GIT-SYNC-PROTOCOL.md`, and the relevant project summaries before modifying code.
5. Fetch the current `main` HEAD and inspect branch status. Never assume old commit hashes or branch comparisons remain current.

## Immediate priority

Continue with **Task 9** as the next integration gate, then Task 10. Do not start Task 2 while Task 1 remains on owner hold. Tasks 4, 5, 6, and 8 depend on Tasks 1 and 10. Task 7 additionally depends on Task 3.

Task 1 remains on hold pending explicit owner approval because English content is still being developed and the translation architecture/manual-translation decision is unresolved. Do not independently lift this hold.

## Task 9 focus

Audit the existing implementation against every v13 acceptance criterion before editing. In particular verify:

- shared Settings/Accessibility widget and its integration;
- language and opacity controls and persistence;
- localStorage key `preferences` with object shape `{ lang, opacity }`;
- supported language codes `es`, `pt`, `fr`, `ar`, `zh-Hans`;
- admin default-opacity setting and persistence;
- RGBA/background alpha rather than reducing opacity of the entire content container;
- keyboard accessibility of the opacity slider;
- View Transitions;
- cross-tab `storage` event behavior;
- required responsive/browser tests.

Prior inspection suggested the homepage already had much of the widget and persistence behavior, while the admin default-opacity controls were not found. Re-check current `main`; these are leads, not unquestionable facts. The older Task 9 completion note is v12 evidence and does not prove v13 acceptance.

## Safety and merge rules

- Do not merge candidate recovery/release branches wholesale. Prior comparisons found large divergences and unrelated files.
- Compare changes individually and selectively integrate only required, verified source files.
- Never inspect, copy, or commit customer/runtime data, adoption/application records, uploads, chat media, logs, or pet records. Treat `adopt/adoptions/pets/**/pet.json` as protected data.
- Never commit credentials, PATs, passwords, cookies, private keys, or secrets.
- Do not overwrite root `.htaccess` without checking the existing repository and live server configuration.
- Keep repository integration separate from production deployment.
- Make small, scoped, reversible changes. Run tests and report exact commands and results. Do not claim tests passed if they were not run.

## Required completion behavior

For each task, record status, branch/base HEAD, exact changed files, shared contracts, tests and results, unresolved issues/owner decisions, commit or PR, and next action in `INTEGRATION-PROGRESS.md`. Update `TODO.md` when appropriate.

Do not mark a task DONE until all v13 acceptance criteria are supported by evidence. Ask the owner only when a decision or access is genuinely required; otherwise proceed with the next safe, dependency-valid action.

**The repository is the shared memory.** A future AI should be able to continue by reading the repository documentation, without asking the owner to upload the worker ZIP or repeat the project history.
