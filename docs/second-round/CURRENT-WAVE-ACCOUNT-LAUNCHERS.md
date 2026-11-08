# Current-Wave Account Launchers

Use only this file for the current seven-account wave. Do not use the archived `UNIFIED-MANUS-WORKER-PROMPT.md` or the archived account sections in `MANUS-ACCOUNT-PROMPTS.md`.

## Account-family rule

- `WEB-01` through `WEB-05` → `WEB-ACCOUNTS-OPERATIONAL-BRIEF.md`
- `DESIGN-01` or `DESIGN-02` → `REDESIGN-ACCOUNTS-OPERATIONAL-BRIEF.md`

Never give a `WEB-*` account the redesign prompt. Never give a `DESIGN-*` account the web prompt. If the account ID and supplied prompt disagree, use the prompt matching the account family and continue without asking the coordinator to choose between unrelated scopes.

## Unified web prompt — use for WEB-01 through WEB-05

Replace `[ACCOUNT ID]` with `WEB-01`, `WEB-02`, `WEB-03`, `WEB-04`, or `WEB-05`.

```text
You are [ACCOUNT ID]. Recover from the synchronized repository:

git fetch origin --prune
git checkout main
git reset --hard origin/main

Read docs/second-round/CURRENT-WAVE-ACCOUNT-LAUNCHERS.md, then read docs/second-round/WEB-ACCOUNTS-OPERATIONAL-BRIEF.md completely. Follow only the section matching your account ID and use the exact branch assigned there.

Do not read or use the redesign brief. It is only for DESIGN-01 and DESIGN-02. Do not stop because anything is missing; use the repository and continue all independently verifiable work. Keep secrets out of the repository, never push main, push only your assigned branch, and report your completed work and evidence.

No Hostinger deployment, upload, or automatic deployment is permitted.
```

## Unified redesign prompt — use for DESIGN-01 and DESIGN-02

Replace `[ACCOUNT ID]` with `DESIGN-01` or `DESIGN-02`.

```text
You are [ACCOUNT ID]. Recover from the synchronized repository:

git fetch origin --prune
git checkout main
git reset --hard origin/main

Read docs/second-round/CURRENT-WAVE-ACCOUNT-LAUNCHERS.md, then read docs/second-round/REDESIGN-ACCOUNTS-OPERATIONAL-BRIEF.md completely. Follow only the section matching your account ID and use the exact branch assigned there.

Do not read or use the web-project brief. It is only for WEB-01 through WEB-05. Do not stop because anything is missing; use the repository and continue all independently verifiable work. Preserve existing routes, backend contracts, authentication, authorization, accessibility, RTL behavior, and data flows. Keep secrets out of the repository, never push main, push only your assigned branch, and report your completed work and evidence.

No Hostinger deployment, upload, or automatic deployment is permitted.
```

## Current coordinator integration branch

```text
integration/second-round-final-2026-11-06
```
