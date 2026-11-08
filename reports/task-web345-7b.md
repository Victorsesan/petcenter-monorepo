# Task 7b — Pet-Match Live-Chat Verification Design

## Status

This document is the implementation-ready design deliverable for Task 7b. The task is intentionally a specification task; it does not modify the live chat system or pretend that a notification has been delivered before the visitor verifies the email link.

## Confirmed direction

A Pet Match notification must not reopen or write into an old visitor chat ID. The system cannot safely prove that a browser presenting an old chat ID belongs to the visitor who submitted the request. Instead, every match notification is delivered through a fresh, single-use email link. The link authenticates a new short-lived chat session and carries only the approved match context.

The visitor’s selected preference may be retained with the request for product analytics and future channel support, but the verified delivery path for this task is the email magic link. Phone text and phone call remain unavailable until their separate provider and consent workflows exist.

## Data contract

A match request already has a generated `reference_code`, the visitor email, category, and optional `matched_pet_id`/`fulfilled_pet_note`. The follow-on implementation should add a server-side magic-link record with:

- `token_hash`: SHA-256 hash of the random token; never store the raw token.
- `request_code`: the Pet Match request reference code.
- `email_hash`: normalized-email hash for audit correlation without duplicating the address in the token table.
- `purpose`: fixed value `pet_match_live_chat`.
- `context_json`: the minimum context needed to prefill the fresh chat, such as request code, pet/category, and a short staff-approved message; do not copy the full request or private admin notes.
- `issued_at`, `expires_at`, `used_at`, and `issued_ip_hash`.
- `issue_count`/status fields sufficient to audit issuance and invalidate outstanding links for the same request.

The implementation may use the site’s existing JSON storage convention if the deployment has not yet provisioned a database table. The token must still be generated with a cryptographically secure random source, hashed before persistence, and compared with a constant-time hash comparison.

## Token and TTL rules

Generate at least 32 random bytes and encode the raw token with URL-safe base64 or lowercase hexadecimal. Issue links with a 20-minute lifetime; the implementation may use a 15-minute lifetime if the product chooses the shorter end of the approved 15–30 minute range. A token is valid only when all of the following are true:

1. Its hash exists and its `expires_at` is in the future.
2. Its record is not already marked used or revoked.
3. Its purpose and request association are valid.
4. The request is still a fulfilled/matched request eligible for the notification.

Invalidate the token immediately after the first successful click/claim, before creating the chat session. The invalidation must be atomic so two simultaneous clicks cannot both claim it. A second click returns a generic expired/used response and must not disclose whether the request exists.

## Issuance and abuse controls

The issuance endpoint must be server-side and must not accept a visitor-supplied request code as proof of ownership. It receives an internal request context after an admin marks the request fulfilled. Rate-limit by normalized destination email and by source IP: no more than three link issues per request in 30 minutes and no more than ten total Pet Match verification emails per IP in 10 minutes. If a new issue is allowed, revoke prior unused tokens for the same request before creating the replacement. Log only the hashed email/IP, request code, outcome, and timestamp.

The email must contain a generic subject and a single absolute HTTPS URL. Do not include the pet’s private notes, staff identity, or raw token anywhere except the URL. The response from public-facing issuance/claim routes must be deliberately generic so they cannot be used to enumerate request codes or email addresses.

## Claim and handoff flow

1. An authorized admin marks a Pet Match request as fulfilled and supplies the approved matched-pet reference/note.
2. The backend creates/revokes the single-use token record and sends the email through the site’s existing transactional-email helper.
3. The visitor opens `/chat/verify-pet-match.php?token=...` over HTTPS.
4. The claim route hashes the token, performs the validity checks, atomically marks it used, and creates a fresh chat session using the existing chat-session creation contract.
5. The fresh session receives a server-created context message such as “Your Pet Match request PM-… has an update about [pet name].” The browser is redirected to the existing chat UI with the new `chat_id`/session token returned by the server; it must not reuse a chat ID from the URL or local storage.
6. The page shows a generic failure state for expired, used, malformed, or unknown tokens and does not reveal which condition occurred.

The context message must be generated server-side from the fulfilled request and must be escaped by the existing chat rendering path. A token claim must never grant admin privileges, reveal the entire request JSON, or permit arbitrary chat-recipient selection.

## Transport and privacy requirements

Use HTTPS-only absolute links, `SameSite=Lax` cookies for the fresh session, short-lived session credentials, and `Cache-Control: no-store` on the claim response. Do not place the email address in the URL. Add a referrer-policy suitable for sensitive token URLs, and clear the token from browser history after the claim redirect. Keep raw tokens out of access logs where the hosting stack permits query-string redaction.

## Required tests before implementation is called complete

- Valid token claims once and creates exactly one fresh chat session.
- A second click, an expired token, a revoked token, malformed input, and a token for a closed/non-matched request all produce the same generic failure response.
- Two simultaneous claim requests cannot both succeed.
- Issuance limits work independently for email/request and IP windows.
- The email contains no raw request details beyond the approved context and no email address in the URL.
- The newly-created chat cannot be selected or redirected to an arbitrary existing `chat_id`.
- The complete path works with the existing chat UI and does not alter chat uploads, message timestamps, or unrelated chat infrastructure.

## Explicit non-goal

Returning a visitor to the visitor’s previous chat thread remains an open future product/security question. It would require binding a verified email to a chat at chat-creation time and is outside Task 7b.
