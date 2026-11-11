# Rehoming live operations

This document covers the live runtime secret and the automated smoke test for the separate merchant/visitor rehoming chat and review endpoints.

## 1. Set `PETCENTER_REHOMING_TOKEN_SECRET` in Hostinger

Generate a value locally and keep it in a password manager. Do not commit it, paste it into GitHub, put it in a public HTML file, or send it in chat. A suitable Linux/macOS command is:

```sh
openssl rand -hex 32
```

The application requires at least 32 characters. A 64-character hexadecimal value from the command above is appropriate.

In Hostinger, open the website’s **Website Dashboard**, open **Environment variables**, add the key `PETCENTER_REHOMING_TOKEN_SECRET`, paste the generated value without surrounding quotes, and select **Apply changes**. Restart the website/runtime if Hostinger presents a restart option. Hostinger’s documented environment-variable workflow says runtime variables take effect when the application starts; the PHP code reads the value with `getenv()` and also checks `$_ENV`.

If the PHP website does not expose an Environment variables section in its hPanel plan, do not put the secret into this Git repository. Use Hostinger support or the plan’s server configuration mechanism to make the variable available to the PHP runtime. An Apache `SetEnv` entry can be used only if the hosting plan explicitly supports it and only in a protected server configuration; test it carefully because an unsupported directive can cause an HTTP 500. The application must see the value through `getenv('PETCENTER_REHOMING_TOKEN_SECRET')`.

After applying the value, verify it without revealing it. A request with a syntactically valid but dummy 64-character bearer token should return HTTP 401 with an invalid-token response. HTTP 503 with `Rehoming token secret is not configured` means the PHP runtime still cannot see the variable. Never use the real secret as a bearer token in a URL or test command.

## 2. Run the automated smoke test

The script is [`tools/rehoming-live-smoke.sh`](../tools/rehoming-live-smoke.sh). It always checks public route availability, HTTP method guards, unauthenticated access guards, and the secret-loaded signal. It never prints bearer tokens or cookie contents.

Make it executable and run the safe, non-mutating checks:

```sh
chmod +x tools/rehoming-live-smoke.sh
./tools/rehoming-live-smoke.sh
```

A full authenticated visitor flow requires a dedicated logged-in test account and a cookie jar for that account. The account must not be a real visitor. The flow creates a smoke thread and message, so it is deliberately opt-in:

```sh
export BASE_URL='https://adopt.petcenterco.com'
export RH_COOKIE_JAR="$HOME/petcenter-smoke-visitor.cookies"
export RH_MERCHANT_ID='SMOKE-MERCHANT'
export RH_RUN_WRITES=1
./tools/rehoming-live-smoke.sh
```

The cookie jar must be produced by logging the dedicated test visitor into the real site through the normal login flow. Do not put credentials in the script or repository. The script then tests thread creation and token issuance, message send, message poll, thread listing, and review reads. It also checks that the review endpoint remains authenticated for an unreviewed merchant.

If a browser export does not work as a Netscape cookie jar, pass the session cookie as a shell variable instead. Do not save this value in Git or paste it into a shared document:

```sh
export RH_COOKIE_HEADER='PHPSESSID=<dedicated-test-session-id>'
export RH_RUN_WRITES=1
./tools/rehoming-live-smoke.sh
```

To test review submission, use only a dedicated test account. This can persist a review unless the account is marked as a test account by the application’s account contract:

```sh
export RH_RUN_REVIEW_WRITE=1
./tools/rehoming-live-smoke.sh
```

Admin moderation is opt-in and requires a dedicated admin cookie jar and an existing test review ID:

```sh
export RH_ADMIN_COOKIE_JAR="$HOME/petcenter-smoke-admin.cookies"
export RH_REVIEW_ID='REV-...'
./tools/rehoming-live-smoke.sh
```

The current script intentionally does not publish a listing because the listing endpoint requires a merchant session, while the chat flow requires a visitor session. Use a separate merchant-session fixture before enabling listing writes; do not reuse a visitor cookie for that test.

## 3. Expected results

A healthy deployment returns HTTP 200 for `/rehoming/list-a-pet/`, HTTP 405 for GET requests to the POST-only start/send/publish endpoints, HTTP 401 for unauthenticated polling, thread listing, review, and moderation requests, and HTTP 401—not HTTP 503—for the dummy bearer-token secret probe.

With a valid dedicated visitor session and configured secret, the authenticated flow should return HTTP 200 for thread creation, send, poll, thread listing, and review reads. With the optional write flags and eligible test account, review submission and admin hide/unhide should also return HTTP 200.

## 4. Rollback

If the live smoke test finds a regression, revert the deployment branch to the previous deployment commit in Git and republish the previous `adopt/` tree. Rotate the runtime secret in Hostinger if it was exposed or entered into an unsafe location. Do not solve a secret problem by adding a secret to source control.

## References

[1]: https://www.hostinger.com/support/how-to-edit-or-add-environment-variables-after-deployment/ "Hostinger environment variables after deployment"
[2]: https://www.php.net/manual/en/reserved.variables.environment.php "PHP environment variables"
