# Current-Wave Account Launchers

Use only this file for the current seven-account wave. Do not use the archived `UNIFIED-MANUS-WORKER-PROMPT.md` or the archived account sections in `MANUS-ACCOUNT-PROMPTS.md`.

## Account-family rule

The account ID determines the only valid brief:

- `WEB-01` through `WEB-05` → `WEB-ACCOUNTS-OPERATIONAL-BRIEF.md`
- `DESIGN-01` or `DESIGN-02` → `REDESIGN-ACCOUNTS-OPERATIONAL-BRIEF.md`

Never give a `WEB-*` account the redesign launcher. Never give a `DESIGN-*` account the web launcher. If an account receives the wrong launcher, it must ignore that launcher, select the brief matching its account ID, and continue without asking the coordinator to choose between unrelated assignments.

## Web launcher — use only for WEB-01 through WEB-05

Replace `[ACCOUNT ID]` with exactly one of `WEB-01`, `WEB-02`, `WEB-03`, `WEB-04`, or `WEB-05`.

```text
You are [ACCOUNT ID]. This is the current PetCenter web-project QA wave. Clone Victorsesan/petcenter-monorepo, fetch origin, and read docs/second-round/WEB-ACCOUNTS-OPERATIONAL-BRIEF.md completely before inspecting or editing source. Your only valid assignment is the section whose heading matches [ACCOUNT ID]. Do not read or use REDESIGN-ACCOUNTS-OPERATIONAL-BRIEF.md for your assignment; that file is only for DESIGN-01 and DESIGN-02. Read both structural summaries and every document in the web brief’s mandatory reading gate. Work only on your assigned branch and scope, synchronize before commits, never push main, never store secrets, never stop because historical evidence is missing, and report all tests, findings, dependencies, and unresolved issues. Do not deploy, upload, push, or trigger Hostinger deployment.
```

## Redesign launcher — use only for DESIGN-01 and DESIGN-02

Replace `[ACCOUNT ID]` with exactly one of `DESIGN-01` or `DESIGN-02`.

```text
You are [ACCOUNT ID]. This is the current PetCenter redesign wave. Clone Victorsesan/petcenter-monorepo, fetch origin, and read docs/second-round/REDESIGN-ACCOUNTS-OPERATIONAL-BRIEF.md completely before inspecting or editing source. Your only valid assignment is the section whose heading matches [ACCOUNT ID]. Do not read or use WEB-ACCOUNTS-OPERATIONAL-BRIEF.md for your assignment; that file is only for WEB-01 through WEB-05. Read both structural summaries and every document in the redesign brief’s mandatory reading gate. Preserve existing routes, backend contracts, authentication, authorization, accessibility, RTL behavior, and data flows. Work only on your assigned redesign branch and scope, synchronize before commits, never push main, never store secrets, and report all tests, findings, dependencies, and unresolved issues. Do not deploy, upload, push, or trigger Hostinger deployment.
```

## Mandatory recovery rule

If the account ID and the supplied brief disagree, the account must not ask the coordinator which unrelated scope to choose. It must use this mapping, switch to the matching brief, verify the matching branch in that brief, and continue:

```text
WEB-*    → WEB-ACCOUNTS-OPERATIONAL-BRIEF.md
DESIGN-* → REDESIGN-ACCOUNTS-OPERATIONAL-BRIEF.md
```
