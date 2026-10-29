<?php
/**
 * get-verification-requests.php
 *
 * TASK-B2 — new file, no existing code to merge (per Directive 8a's "New
 * files created from scratch" exception).
 *
 * Lists pending merchant verification requests for the admin dashboard's
 * new "Verification Requests" panel. Mirrors get-applications.php's shape
 * (token-based POST, returns a JSON array under a named key) since that's
 * the existing list-endpoint pattern documented in
 * ADMIN_DASHBOARD_PROJECT_SUMMARY.md — reused here per Rule 7 rather than
 * inventing a different response shape.
 *
 * DATA SOURCE NOTE (flagged per the Integration Notes / SHARED CONTRACTS
 * mechanism, not a silent private decision): Task B1 owns the actual
 * verification-submission intake (the liveness-check + ID-upload flow that
 * emails the admin). B1's own written spec says nothing is stored
 * server-side after the email send completes — but the CEO separately
 * confirmed in chat that verification media should be stored the same way
 * other project media (adoption, chat) already is, and pointed at
 * adoption-application.php as the real reference. That file's convention —
 * one folder per submission containing a JSON record
 * (`/adoptions/applications/{code}/application.json`) — is what this store
 * now mirrors (see verification-store.php's VERIF_REQUESTS_BASE), instead
 * of the single flat JSON file this endpoint originally read from. The
 * sensitive-media-deletion question (does B1 still delete the video/ID
 * files themselves, or keep those too) is still open and separate from
 * this lightweight record — see the completion file for the exact
 * question raised to the CEO. Reconcile this store against B1's real
 * intake mechanism once B1's completion file exists — a short merge pass,
 * not a rebuild, per the Integration Notes.
 */

require_once __DIR__ . '/verification-store.php'; // TASK-B2: shared read/write helpers, see that file

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Same admin-token check as every other admin-dashboard endpoint (see
// verification-store.php's verifRequireAdmin() for the shared implementation).
if (!verifRequireAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid or missing admin token']);
    exit;
}

$requests = verifLoadRequests();

echo json_encode([
    'success'  => true,
    'requests' => array_values($requests),
]);
