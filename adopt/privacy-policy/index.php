<?php
/** TASK-WEB-04 START — Public account-data notice supplements the canonical Privacy Policy. */
declare(strict_types=1);
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Privacy Notice — Rehoming Accounts | Pet Center</title>
  <meta name="description" content="How Pet Center Rehoming sign-in and adopter profile data are handled.">
  <style>
    :root{color-scheme:light;--navy:#10214b;--orange:#ed7624;--text:#2c2416;--muted:#62584d;--bg:#f9f7f4;--surface:#fff}
    *{box-sizing:border-box}body{margin:0;padding:24px;background:var(--bg);color:var(--text);font:16px/1.7 system-ui,-apple-system,sans-serif}
    main{max-width:820px;margin:32px auto;padding:clamp(24px,5vw,48px);background:var(--surface);border-radius:14px;box-shadow:0 8px 34px #10214b14}
    h1,h2{color:var(--navy);line-height:1.25}h1{font-size:clamp(2rem,5vw,2.8rem)}h2{margin-top:30px;font-size:1.4rem}
    p,li{color:var(--muted)}a{color:var(--navy);text-underline-offset:3px}a:focus-visible{outline:3px solid var(--orange);outline-offset:3px}
    .notice{padding:14px 16px;border-inline-start:4px solid var(--orange);background:#fff8f1}
  </style>
</head>
<body>
<main>
  <h1>Privacy notice for Rehoming accounts</h1>
  <p class="notice">This page supplements the <a href="https://docs.petcenterco.com/privacy-policy/">canonical Pet Center Privacy Policy</a> with factual details about the Rehoming account gateway. It is not a replacement for that policy or a legal-compliance statement. Final wording and deployment configuration require Pet Center review before production publication.</p>

  <h2>Google sign-in</h2>
  <p>When Google sign-in is configured, the gateway requests OpenID Connect identity, email, and basic profile scopes. It verifies Google's signed ID token, issuer, audience, expiry, one-time nonce, and verified-email claim on the server. A first-time Google identity creates a visitor account. Merchant or admin roles are assigned only from the server-side account registry; they cannot be selected in a browser form or URL.</p>
  <p>The gateway stores the Google subject identifier, verified email address, and optional display name needed for account access. It does not save the Google access token or refresh token. Google sign-in is unavailable when the required server configuration is absent; the site must not report a successful login in that case.</p>

  <h2>Adopter profile information</h2>
  <p>If a signed-in visitor saves an adopter profile, the server stores the selected household type, pet-experience level, whether other pets or children live in the home, optional other-pet details, and the visitor's short explanation of interest. The profile is account-owned and is not public. In the current Rehoming flow it is not automatically attached to messages or shared with merchants.</p>

  <h2>Merchant verification submissions</h2>
  <p>The separate merchant verification form sends the submitted liveness video and ID images to the review team by email. The server stages those media files temporarily and the handler removes its temporary copies after processing. A server-side review-queue record contains the submitted username, email address, country, submission time, status, and email-delivery result. This code does not set an expiry period for queue records or copies retained by recipient mail systems; Pet Center must confirm those retention and access procedures before production use.</p>

  <h2>Sessions and storage</h2>
  <p>Authentication state and adopter profile values are stored server-side, not in browser local storage. The site uses a session cookie marked HttpOnly, Secure when served over HTTPS, and SameSite=Lax. The implementation expires sessions after 30 minutes of inactivity or 12 hours from sign-in. Rehoming account records are kept in private server-side storage; the deployment must verify that the account store is inaccessible as a static download and, where supported, placed outside the public web root with restrictive permissions.</p>

  <h2>Retention and requests</h2>
  <p>This code change does not define a separate retention period for Rehoming account/profile records and does not add self-service deletion for visitor profiles. For access, correction, or deletion requests, use the contact process in the <a href="https://docs.petcenterco.com/privacy-policy/">canonical Privacy Policy</a> or contact <a href="mailto:support@petcenterco.com">support@petcenterco.com</a>. Pet Center must review this notice against its actual hosting, retention, and support procedures before production use.</p>

  <p><a href="/signin/">Return to secure sign-in</a> · <a href="/terms-of-service/">Terms of Service</a></p>
</main>
</body>
</html>
<?php /* TASK-WEB-04 END — Account-data privacy notice. */ ?>
