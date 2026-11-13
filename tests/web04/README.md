# WEB-04 regression checks

These tests use generated RSA keys, synthetic accounts, and owner-only temporary directories. They do not call Google, require production credentials, or read/write application account data.

Run the auth and behavior regression suite with PHP 8.1+ and OpenSSL:

```sh
php tests/web04/run-regressions.php
```

Run the shared text-length fallback with mbstring functions disabled:

```sh
php -d disable_functions=mb_strlen,mb_substr tests/web04/portable-length.php
```

The main suite removes its temporary files at shutdown. It covers explicit external storage validation, role/verification gates, visitor-profile persistence, external staging fixtures, aggregate per-IP throttling, synthetic OIDC issuer/audience/authorized-party/expiry/nonce/email/signature/key checks, OAuth state mismatch/expiry/replay and PKCE, exact redirect configuration, safe return paths, CSRF origin/token rejection, server-assigned visitor roles, logout when bearer-token revocation fails, and static gateway/profile/route checks. All identity data, credentials and keys are synthetic.

An optional browser test uses Python Playwright and a locally installed Chromium. It serves only the static page, mocks every auth API response, and does not require a signed-in account or external network:

```sh
python3 tests/web04/profile-loading-smoke.py
```

Run the direct sign-in and account-creation gateway smoke test (signup mode, keyboard tabs, mobile viewport, legal links, not-configured and backend-error messaging, and server-side OAuth navigation):

```sh
python3 tests/web04/gateway-smoke.py
```

Both browser scripts use mocked auth responses, the repository's static files, and local Chromium. They do not contact Google, use live accounts, or mutate remote data. These tests do not replace real screen-reader testing, Apache/Hostinger route verification, or a configured provider round-trip.
