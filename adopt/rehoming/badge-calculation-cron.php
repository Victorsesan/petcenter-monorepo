<?php
/**
 * badge-calculation-cron.php
 * TASK-B3 — Badge & reputation system: core tiers
 *
 * Scheduled script (daily cron on Hostinger — see deployment note at bottom of this file).
 * Recalculates all four badges for every merchant profile on disk and writes the result
 * back into each merchant's own profile.json.
 *
 * Confirmed before writing this file: neither adoption-website-project-summary.md nor
 * ADMIN_DASHBOARD_PROJECT_SUMMARY.md documents an existing scheduled/cron job anywhere on
 * the platform (checked both in full, including a targeted search for "cron"/"scheduled" —
 * the only hit was chat-widget.js's client-side polling self-scheduler, which is a
 * browser-side setTimeout loop, not a server cron job). Per this task's instruction to
 * "confirm before creating a new one," this is a genuinely new file, not a duplicate of
 * something that already exists.
 *
 * Task A2 (Needs Extra Love flag) is spec'd against "the same cron pattern" as this task,
 * per the task tracker's Integration Notes. Since A2's file does not exist yet either
 * (both are new, parallel tasks), this script is written standalone for now — whoever
 * reconciles A2 and B3 at the integration checkpoint can decide whether to merge both
 * checks into one shared cron entry or keep two separate ones; the calculation logic
 * itself lives in badge-logic.php specifically so it can be required from either setup
 * without duplicating code.
 */

// TASK-B3 START: cron script body
require_once __DIR__ . '/badge-logic.php';

/**
 * runBadgeCalculationForAllMerchants()
 * Scans every merchant folder under B3_MERCHANTS_BASE_DIR, recalculates that merchant's
 * badges, and writes the result back. Returns a small report array so this can be run
 * either from real cron (silent) or manually from a browser/CLI for testing (printed).
 *
 * @return array{processed:int, succeeded:int, failed:array<string>}
 */
function runBadgeCalculationForAllMerchants() {
    $report = array(
        'processed' => 0,
        'succeeded' => 0,
        'failed' => array(),
    );

    if (!is_dir(B3_MERCHANTS_BASE_DIR)) {
        // Nothing to do yet — the merchants directory doesn't exist until at least one
        // merchant profile has been created. Not an error condition.
        return $report;
    }

    // Every subfolder of B3_MERCHANTS_BASE_DIR is one merchant, named by merchant ID —
    // mirrors the existing platform convention of one folder per record
    // (e.g. /adoptions/pets/{category}/{pet_id}/, /adoptions/applications/{code}/).
    $merchantIds = array_filter(scandir(B3_MERCHANTS_BASE_DIR), function ($entry) {
        return $entry !== '.' && $entry !== '..' && is_dir(B3_MERCHANTS_BASE_DIR . '/' . $entry);
    });

    foreach ($merchantIds as $merchantId) {
        $report['processed']++;

        $profile = readMerchantProfile($merchantId);
        if ($profile === null) {
            // profile.json missing or unreadable for this merchant folder — record and
            // move on, one bad record must not abort the whole run.
            $report['failed'][] = $merchantId;
            continue;
        }

        $updatedProfile = recalculateAllBadges($profile);
        $writeSucceeded = writeMerchantProfile($merchantId, $updatedProfile);

        if ($writeSucceeded) {
            $report['succeeded']++;
        } else {
            $report['failed'][] = $merchantId;
        }
    }

    return $report;
}

// Only actually run when this file is the one being executed directly (real cron hit, or
// a manual browser/CLI run for testing) — not when some future shared dispatcher requires
// this file just to reuse runBadgeCalculationForAllMerchants() itself.
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__ || php_sapi_name() === 'cli') {

    // Found during self re-verification: this script, if invoked via a public URL
    // (Hostinger's URL-based Cron Jobs option), had no authentication at all — anyone who
    // discovered the URL could trigger repeated recalculation runs and see the plain-text
    // report below, including internal merchant IDs. CLI invocation is implicitly trusted
    // (see b3IsAdminAuthenticated()'s doc comment); an HTTP invocation must supply the
    // shared ADMIN_TOKEN, matching this platform's existing admin-endpoint security
    // pattern (Security Features — Token-Based Authentication; reconciled against the
    // real admin-auth.php / chat-config.php source once those were provided).
    $isCliInvocation = (php_sapi_name() === 'cli');
    if (!$isCliInvocation && !b3IsAdminAuthenticated()) {
        http_response_code(401);
        echo "Unauthorized.\n";
        exit;
    }

    $report = runBadgeCalculationForAllMerchants();

    // Plain-text output so a Hostinger cron job's emailed output (if configured) is
    // readable without a browser, matching how admin-activity.log-style output is kept
    // human-readable elsewhere on this platform.
    echo 'Badge calculation run: ' . gmdate('Y-m-d H:i:s') . " UTC\n";
    echo 'Merchants processed: ' . $report['processed'] . "\n";
    echo 'Succeeded: ' . $report['succeeded'] . "\n";
    if (!empty($report['failed'])) {
        echo 'Failed (profile.json missing/unreadable/unwritable): ' . implode(', ', $report['failed']) . "\n";
    }
}
// TASK-B3 END: cron script body

/**
 * Deployment note (Hostinger, per Directive 11 — search before committing to an approach):
 * Hostinger's hPanel supports Cron Jobs directly (Advanced -> Cron Jobs), which can call
 * either a URL (e.g. https://adopt.petcenterco.com/rehoming/badge-calculation-cron.php)
 * on a schedule, or run `php /home/{user}/public_html/adopt/rehoming/badge-calculation-cron.php`
 * via CLI. A daily interval was used above with SCRIPT_FILENAME/CLI detection so this file
 * behaves correctly under either invocation method — the actual hPanel schedule entry
 * itself is a hosting-control-panel configuration step, not a code change, and needs the
 * CEO's Hostinger access to actually create; noted here rather than assumed done.
 */
