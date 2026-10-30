# Second-Round Setup, Skills, and Quality Gates

## Environment baseline

Required command-line tools: Python 3, PHP CLI, Node.js, Git, UnZip, jq, rsync, curl, grep, sed, awk, file, and find. The current sandbox has the required baseline verified in `INTEGRATION-PROGRESS.md`.

## Skill routing

- Use `code-reviewer` for structured correctness, maintainability, security, and test-coverage reviews.
- Use `github-integration` for repository operations and credential-safe GitHub access.
- Use `webdev`/`webdev-seo` for website behavior, route, metadata, and crawler-visible page checks.
- Use `image-processing` when validating supplied screenshots or visual references.
- Use `persistent-computing` only if a persistent server, Docker service, or long-running integration environment is genuinely required.
- Use `skill-creator` only when a missing project-specific skill must be authored or updated; do not invent a security-audit skill during debugging.

The project requests security-audit and PHP/WordPress skills, but no such managed packages or source archives were supplied. Until a source is provided, use the documented OWASP/PHP/HTML checklists and report the limitation explicitly.

## Quality gates

1. Repository gate: clean working tree, current `main`, no protected files, no credentials in remotes or files.
2. Scope gate: assignment, completion file, current path, shared-file owner, and dependencies agree.
3. Static gate: PHP lint, JavaScript syntax, HTML/CSS structural checks, duplicate-ID check, and anchor balance.
4. Security gate: authorization, session/token lifecycle, CSRF, XSS, path traversal, upload validation, file permissions/storage, rate limits, and secret scan.
5. Browser gate: Playwright/Manus browser at desktop and phone sizes, console/network errors, keyboard/focus behavior, forms, and route navigation.
6. Regression gate: existing tests plus a focused test for each confirmed bug; no unrelated redesign.
7. Reporting gate: every issue is Fixed, False Alarm/Already Resolved, or Needs My Review with evidence and commit hash.

## Install policy

Do not install arbitrary skills or libraries solely because a task mentions them. First check the available managed skills, the repository's package manifests, and the assignment's allowed tooling. If a new skill is needed, provide its source, initialize it through the skill-creator workflow, validate it, and record its provenance in the account report.
