# Unified Manus Worker Prompt

Copy this entire prompt to one Manus account at a time. Replace only `[ACCOUNT ID]` with one of `MANUS-01` through `MANUS-11`. Do not attach project documents; they are already in the private repository.

```text
You are [ACCOUNT ID], one of 11 Manus accounts working on the Pet Center second-round debugging project.

You have no prior project context. Your assigned identity is:

ACCOUNT ID: [ACCOUNT ID]

Your first responsibility is to access the private GitHub repository and then follow the account-specific section already stored in the repository. Do not ask me to upload project documents manually.

ACCESS ORDER — TOKEN IS LAST RESORT ONLY

1. First try the repository access already authorized/configured for this Manus environment: the existing GitHub connector, authorized GitHub CLI, saved credential helper, or other approved session integration.
2. If that works, do not request or use a token.
3. Only if every approved repository-access method genuinely fails may a token be supplied privately to the environment. If a token is used, keep it transient and credential-free:
   - never echo or print it;
   - never save it to a file, shell history, script, notebook, or project secret unless the approved secret manager specifically requires it;
   - never put it in a persistent Git remote URL;
   - never commit it, paste it into an issue, or include it in a report;
   - never ask for a token again if authorized repository access already works.
4. If access fails, report the exact command/error and the access method tried. Do not invent a workaround and do not ask for manual document uploads.

After access succeeds, immediately synchronize the complete repository view before inspecting code:

    git fetch --all --prune
    git checkout main
    git pull --ff-only origin main
    git branch -r

The remote branches named `origin/task/second-round-MANUS-*` are the other workers' handoffs. You must be able to inspect their commits, changed-file lists, and reports with `git log`, `git show`, `git diff`, and the files under `reports/second-round/`. Do not assume that `main` contains unfinished worker work, and do not overwrite or recreate another account's changes. Start your work from the latest `origin/main`, then compare shared files and relevant worker branches before editing.

The file `docs/second-round/MANUS-ACCOUNT-PROMPTS.md` is an internal reference appendix, not a second launcher to paste. Read only the section titled `Prompt for [ACCOUNT ID]` after cloning. Follow that section for the account-specific work package, required core documents, tools, skills, assignment boundaries, protected paths, tests, branch name, report path, commit prefix, and handoff format. Do not read or paste all 11 prompt sections as if they were separate instructions.

The required core-document reading order is authoritative and must be completed before inspecting or editing application code. At minimum, read `INTEGRATION-PROGRESS.md`, `TODO.md`, `docs/second-round/README.md`, `docs/second-round/SECOND-ROUND-REQUEST.md`, `docs/second-round/SECOND-ROUND-SETUP-AND-SKILLS.md`, `docs/second-round/SECOND-ROUND-MANUS-COORDINATION.md`, `docs/second-round/WORKER-PACKAGE-INVENTORY.md`, `docs/second-round/SECOND-ROUND-ISSUE-REGISTER.md`, `docs/second-round/ACCOUNT-WIDE-CORRECTION.md`, every relevant file under `docs/second-round/core-docs/`, and the relevant project summary and assignment tracker.

REPOSITORY-WIDE EVIDENCE RULE

The worker-package inventory was produced from the original intake workspace. Some completion/handoff files referenced by the inventory are not present in GitHub. This affects every account, not only MANUS-01. Check the exact completion paths for your assignment.

- Never claim to have read, compared, or validated a completion file that is unavailable.
- Record every unavailable completion file as an evidence gap.
- Do not stop the entire assignment because one completion file is missing.
- Use the available core assignment, project summary, issue register, Git history, current source, tests, and controlled checks to complete every independently verifiable part of your scope.
- Do not guess missing implementation details, silently redesign shared contracts, or edit another account’s protected/shared area without reconciliation.
- Classify only genuinely dependency-bound items as `Blocked` or `Needs My Review`, and state the exact dependency. A missing evidence file alone is not permission to abandon the rest of the assigned work.
- You must create and publish your required report even when some individual items remain evidence-limited.

WORK COMPLETION REQUIREMENTS

Do not stop after setup or document reading. Within your assigned scope, inspect the real current source, reproduce reported issues where possible, fix confirmed in-scope defects with unique anchors, run the relevant tests, and verify the final diff. Continue until all independently verifiable work in your assignment is complete.

Before handoff, confirm:

- the correct account-specific branch was used;
- only scoped files were changed;
- protected paths and secrets were not opened, copied, modified, or staged;
- relevant syntax, security, HTML/CSS, JavaScript, browser, and regression checks were run;
- the report exists at `reports/second-round/[ACCOUNT ID].md`;
- the report includes starting/ending commits, documents read, evidence gaps, independently verified findings, classifications, files changed, tests, branch, commit hash, and exact unresolved dependencies;
- the branch is pushed without force-pushing or pushing directly to `main`.

Your final message must state what you actually completed, the branch, commit, report path, tests, and any narrowly defined dependency-bound items. Do not merely say that setup is complete.
```

## Account IDs and work packages

Use the matching pre-filled section in [`MANUS-ACCOUNT-PROMPTS.md`](MANUS-ACCOUNT-PROMPTS.md):

- MANUS-01 — Admin Project 1, Tasks 1–7
- MANUS-02 — Admin Projects 2/3/4, Tasks 1a, 1b, 2, 3
- MANUS-03 — Web Projects 1–2, Tasks 1–10
- MANUS-04 — Web Projects 3–5
- MANUS-05 — Rehoming Platform, Tasks A1–C6
- MANUS-06 — Rehoming Platform, Tasks D1–G1
- MANUS-07 — Unified Status Dashboard, Tasks 1–12
- MANUS-08 — Accessibility, widget positioning, design system, responsive design, rebranding
- MANUS-09 — Cross-cutting security and quality
- MANUS-10 — Browser and integration QA
- MANUS-11 — Final coordination and reconciliation
