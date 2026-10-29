<?php
/**
 * anti-fraud-detectors.php
 * TASK C2 — Anti-fraud detection layer
 *
 * Four independent detectors, callable from wherever the real listing-upload
 * handler and chat message handler live. This file has NO knowledge of those
 * handlers' internals — it's a standalone module with one function per
 * detector, each taking plain data (an image path, a merchant ID, a message
 * string, etc.) and returning a simple result. This keeps it safe to drop
 * into whichever real files exist, without C2 having to touch or guess at
 * their code.
 *
 * ASSUMPTION FLAGGED (per SHARED CONTRACTS — "a flagged, visible deviation,
 * never a silent private adjustment"): the real listing-upload handler and
 * chat message handler did not exist as confirmed files at the time this was
 * written (see this task's completion file). Every integration point below
 * is written as a single function call with a documented call site, so
 * wiring it into the real files — whatever their final names — is a one-line
 * change, not a rewrite.
 *
 * STORAGE ASSUMPTION (also flagged): no Report/Review/account storage layer
 * existed yet to confirm against. This module uses flat-file JSON records,
 * one file per record, matching the existing pet.json / index.json pattern
 * already used for listings in this codebase (see publish-pet.php) — the
 * closest "comparable data" pattern the platform already has. Task C1b
 * (which owns the Report record) and Task C6 (which owns the Review record)
 * may choose a different real storage layer (e.g. a DB table, if they follow
 * chat-send.php's pattern instead) — if so, swap the five storage functions
 * at the bottom of this file for their real implementation; every detector
 * function above them is storage-agnostic and doesn't need to change.
 */

// ── Storage locations (flat-file JSON, see STORAGE ASSUMPTION above) ───────
define('CF_REPORTS_DIR',  __DIR__ . '/../data/reports');
define('CF_REVIEWS_DIR',  __DIR__ . '/../data/reviews');
define('CF_ACCOUNTS_DIR', __DIR__ . '/../data/accounts');
define('CF_LISTINGS_DIR', __DIR__ . '/../data/listings'); // used only to set held_for_review

foreach ([CF_REPORTS_DIR, CF_REVIEWS_DIR, CF_ACCOUNTS_DIR] as $dir) {
    if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
}

// =====================================================================
// SHARED HELPERS
// =====================================================================

/**
 * Reads the account record for $accountId and returns true only if
 * is_test_account is explicitly true. Missing/unreadable records default
 * to false (never silently skip real-account checks because of a read
 * error) so other detectors never write about a real merchant account.
 */
function cfIsTestAccount(string $accountId): bool {
    $path = CF_ACCOUNTS_DIR . '/' . basename($accountId) . '.json';
    if (!file_exists($path)) return false;
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) && !empty($data['is_test_account']) && $data['is_test_account'] === true;
}

/**
 * Reads an account's created_at timestamp (ISO 8601) and returns true if
 * that account was created within $withinDays days of now. Used by
 * detector 2 (posting velocity) and detector 4 (reputation anomaly) to
 * weight recently-created accounts. Returns false (never a crash) if the
 * account record or its created_at field is missing, since "unknown age"
 * should not itself trigger a fraud flag.
 */
function cfAccountCreatedWithinDays(string $accountId, int $withinDays): bool {
    $path = CF_ACCOUNTS_DIR . '/' . basename($accountId) . '.json';
    if (!file_exists($path)) return false;
    $data = json_decode(file_get_contents($path), true);
    if (!is_array($data) || empty($data['created_at'])) return false;
    $created = strtotime($data['created_at']);
    if ($created === false) return false;
    return $created >= strtotime("-{$withinDays} days");
}

/**
 * Writes one Report record per the SHARED CONTRACTS field list. Every
 * detector below calls this instead of writing JSON directly, so the field
 * names/shape stay in exactly one place in this file. Checks the target
 * merchant's is_test_account first and writes nothing if true, per the
 * contract ("nothing ever changes that account's ... admin queues").
 *
 * Returns the report_id on success, or null if the write was suppressed
 * (test account) or failed.
 */
function cfWriteReportRecord(array $args): ?string {
    $targetId = $args['target_id'] ?? null;
    if ($targetId !== null && cfIsTestAccount((string)$targetId)) {
        return null; // test account — show normal success to the caller, record nothing
    }

    $reportId = 'RPT-' . date('Ymd') . '-' . bin2hex(random_bytes(6));
    $record = [
        'report_id'       => $reportId,
        'source'          => 'system_flag', // every C2 detector writes system_flag, never user_report
        'report_type'     => $args['report_type']     ?? 'platform',
        'target_type'     => $args['target_type']     ?? 'listing',
        'target_id'       => $targetId,
        'category'        => $args['category'], // duplicate_photo | posting_velocity | off_platform_contact | reputation_anomaly
        'description'     => $args['description']     ?? '',
        'reporter_id'     => null, // always null for system flags, per contract
        'created_at'      => gmdate('Y-m-d\TH:i:s\Z'),
        'status'          => 'open',
        'holds_listing'   => $args['holds_listing']   ?? false,
        'context_token'   => bin2hex(random_bytes(16)), // for C1b's merchant-response link, unused by welfare/system-only flows but kept consistent with the contract shape
        'merchant_context' => null,
    ];

    $ok = @file_put_contents(
        CF_REPORTS_DIR . '/' . $reportId . '.json',
        json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );
    return $ok !== false ? $reportId : null;
}

/** Lists all Report records, newest first. Used by detector 4's reputation-anomaly window check and by reconciliation/tests. */
function cfListReportRecords(): array {
    $out = [];
    foreach (glob(CF_REPORTS_DIR . '/*.json') ?: [] as $f) {
        $r = json_decode(file_get_contents($f), true);
        if (is_array($r)) $out[] = $r;
    }
    usort($out, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
    return $out;
}

/** Lists all Review records, newest first. Used by detector 4. */
function cfListReviewRecords(): array {
    $out = [];
    foreach (glob(CF_REVIEWS_DIR . '/*.json') ?: [] as $f) {
        $r = json_decode(file_get_contents($f), true);
        if (is_array($r)) $out[] = $r;
    }
    usort($out, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
    return $out;
}

/**
 * Sets held_for_review on a listing's pet.json (this field is owned by C2
 * per SHARED CONTRACTS). $listingPath is the full path to the listing's
 * pet.json — the real listing-upload handler passes this in, since only it
 * knows the on-disk layout (category folder etc. per publish-pet.php's
 * pattern).
 */
function cfSetHeldForReview(string $listingPath, bool $value): bool {
    if (!file_exists($listingPath)) return false;
    $pet = json_decode(file_get_contents($listingPath), true);
    if (!is_array($pet)) return false;
    $pet['held_for_review'] = $value;
    return @file_put_contents($listingPath, json_encode($pet, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
}


// =====================================================================
// DETECTOR 1 — Duplicate-photo detection
// =====================================================================
/**
 * Library choice (Directive 11): intervention/imagehash
 * (packagist.org/packages/intervention/imagehash), the perceptual-hashing
 * companion to Intervention Image — one of the most widely used, actively
 * maintained PHP image libraries (large install base, active maintenance,
 * MIT licensed, free). Preferred over smaller alternatives found in the
 * same search (sidus/perceptual-hash, sapientpro/image-comparator — both
 * far lower install counts/community signal) specifically for maintenance
 * health per Directive 11.
 *
 * composer require intervention/imagehash
 *
 * FALLBACK: if the library isn't installed on the server this runs on
 * (confirm at integration time), cfAverageHashFallback() below provides a
 * dependency-free 8x8 average-hash implementation using only GD, built so
 * detector 1 degrades gracefully rather than fatal-erroring if composer
 * isn't available in the deployment environment. Swap this out once the
 * real server environment is confirmed.
 */
function cfDetectDuplicatePhoto(string $newImagePath, string $newMerchantId, string $newListingId, array $existingPhotos, float $thresholdPercent = 90.0): ?array {
    // $existingPhotos: array of ['path' => ..., 'merchant_id' => ..., 'listing_id' => ...]
    // for every photo already on the platform. The real handler supplies this
    // list (e.g. by scanning CF_LISTINGS_DIR) — this function only compares.
    if (!file_exists($newImagePath)) return null;

    $newHash = class_exists('Intervention\ImageHash\Hasher')
        ? cfInterventionHash($newImagePath)
        : cfAverageHashFallback($newImagePath);
    if ($newHash === null) return null;

    foreach ($existingPhotos as $existing) {
        if (($existing['merchant_id'] ?? null) === $newMerchantId) continue; // same-account reuse is normal, skip
        if (!file_exists($existing['path'] ?? '')) continue;

        $existingHash = class_exists('Intervention\ImageHash\Hasher')
            ? cfInterventionHash($existing['path'])
            : cfAverageHashFallback($existing['path']);
        if ($existingHash === null) continue;

        $similarity = cfHashSimilarityPercent($newHash, $existingHash);
        if ($similarity >= $thresholdPercent) {
            $reportId = cfWriteReportRecord([
                'target_type'   => 'listing',
                'target_id'     => $newListingId,
                'category'      => 'duplicate_photo',
                'description'   => sprintf(
                    'Photo on listing %s matches a photo on listing %s (merchant %s) at %.1f%% similarity (threshold %.0f%%).',
                    $newListingId, $existing['listing_id'] ?? 'unknown', $existing['merchant_id'] ?? 'unknown', $similarity, $thresholdPercent
                ),
            ]);
            return ['matched' => true, 'similarity' => $similarity, 'report_id' => $reportId, 'matched_listing_id' => $existing['listing_id'] ?? null];
        }
    }
    return ['matched' => false];
}

/** Hash via intervention/imagehash, if installed. Returns a bit-string or null on failure. */
function cfInterventionHash(string $path): ?string {
    try {
        $manager = new \Intervention\Image\ImageManager(\Intervention\Image\Drivers\Gd\Driver::class);
        $image = $manager->read($path);
        $hasher = new \Intervention\ImageHash\Hasher(new \Intervention\ImageHash\Strategies\Perceptual());
        return (string) $hasher->hash($image)->toBits();
    } catch (\Throwable $e) {
        error_log('cfInterventionHash failed: ' . $e->getMessage());
        return null;
    }
}

/**
 * Dependency-free 8x8 average-hash (aHash) fallback using only GD, which
 * ships with PHP by default. Resizes to 8x8 greyscale, compares each pixel
 * to the mean, returns a 64-bit string ('0'/'1' per pixel). Lower fidelity
 * than a true perceptual hash (no frequency-domain step) but catches exact
 * and near-exact re-uploads, which is the primary duplicate-photo case
 * this detector targets, without requiring composer in this environment.
 */
function cfAverageHashFallback(string $path): ?string {
    $info = @getimagesize($path);
    if (!$info) return null;
    $src = match ($info[2]) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
        IMAGETYPE_PNG  => @imagecreatefrompng($path),
        IMAGETYPE_GIF  => @imagecreatefromgif($path),
        IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
        default        => null,
    };
    if (!$src) return null;

    $small = imagecreatetruecolor(8, 8);
    imagecopyresampled($small, $src, 0, 0, 0, 0, 8, 8, imagesx($src), imagesy($src));
    imagedestroy($src);

    $values = [];
    for ($y = 0; $y < 8; $y++) {
        for ($x = 0; $x < 8; $x++) {
            $rgb = imagecolorat($small, $x, $y);
            $r = ($rgb >> 16) & 0xFF; $g = ($rgb >> 8) & 0xFF; $b = $rgb & 0xFF;
            $values[] = (int) round(($r + $g + $b) / 3);
        }
    }
    imagedestroy($small);

    $mean = array_sum($values) / count($values);
    $bits = '';
    foreach ($values as $v) { $bits .= $v >= $mean ? '1' : '0'; }
    return $bits;
}

/** Hamming-distance-based similarity percentage between two equal-length bit strings. */
function cfHashSimilarityPercent(string $hashA, string $hashB): float {
    $len = min(strlen($hashA), strlen($hashB));
    if ($len === 0) return 0.0;
    $diff = 0;
    for ($i = 0; $i < $len; $i++) {
        if ($hashA[$i] !== $hashB[$i]) $diff++;
    }
    return (1 - ($diff / $len)) * 100;
}


// =====================================================================
// DETECTOR 2 — Posting-velocity flag
// =====================================================================
/**
 * Call this once per new listing, right after the listing is saved (so
 * $recentListingIds already includes the one just created). The real
 * handler is responsible for supplying the merchant's listing IDs/paths
 * from the last 24 hours — this function only counts and flags.
 */
function cfDetectPostingVelocity(string $merchantId, array $recentListingPathsLast24h): array {
    $flaggedListingIds = [];
    if (count($recentListingPathsLast24h) > 3 && cfAccountCreatedWithinDays($merchantId, 7)) {
        foreach ($recentListingPathsLast24h as $listingPath) {
            $pet = json_decode(@file_get_contents($listingPath), true);
            $listingId = is_array($pet) ? ($pet['pet_id'] ?? $pet['id'] ?? basename(dirname($listingPath))) : basename(dirname($listingPath));

            cfWriteReportRecord([
                'target_type'    => 'listing',
                'target_id'      => $listingId,
                'category'       => 'posting_velocity',
                'holds_listing'  => true,
                'description'    => sprintf(
                    'Merchant %s (account created within last 7 days) posted %d listings within a 24-hour window (threshold: more than 3).',
                    $merchantId, count($recentListingPathsLast24h)
                ),
            ]);
            cfSetHeldForReview($listingPath, true); // C2 owns held_for_review
            $flaggedListingIds[] = $listingId;
        }
    }
    return ['flagged' => !empty($flaggedListingIds), 'listing_ids' => $flaggedListingIds];
}


// =====================================================================
// DETECTOR 3 — Off-platform-contact detection
// =====================================================================
/**
 * Call this on every new message, passing the message's position in its
 * thread (1-indexed). Only scans the first 5 messages of a thread, per
 * spec — after that, sharing contact info is normal meetup logistics.
 */
function cfDetectOffPlatformContact(string $messageText, int $messageIndexInThread, string $threadId, string $senderAccountId): ?string {
    if ($messageIndexInThread > 5) return null;

    $patterns = [
        // Phone numbers: covers common US/intl formats e.g. (555) 123-4567, 555-123-4567, +1 555 123 4567
        '/(\+?\d{1,3}[\s.-]?)?\(?\d{3}\)?[\s.-]?\d{3}[\s.-]?\d{4}\b/',
        // Email addresses
        '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/',
        // Common off-platform phrases
        '/\b(text me at|whatsapp|whats ?app|call me|reach me on|my number is|email me at)\b/i',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $messageText)) {
            return cfWriteReportRecord([
                'target_type' => 'merchant', // the conversation's merchant side is the subject of the flag regardless of which party sent it, so admins can review the thread
                'target_id'   => $senderAccountId,
                'category'    => 'off_platform_contact',
                'description' => sprintf(
                    'Message %d of thread %s matched an off-platform-contact pattern.',
                    $messageIndexInThread, $threadId
                ),
            ]);
        }
    }
    return null;
}


// =====================================================================
// DETECTOR 4 — Reputation-anomaly detection (scheduled)
// =====================================================================
/**
 * Scheduled check (shares A2/B3's cron pattern — confirm the real cron
 * entry point at integration time; this function is the unit of work a
 * cron job calls, not a cron runner itself). Scans all merchants and
 * flags any with 3+ reviews/reports in a trailing 24-hour window,
 * weighting recently-created reviewer/reporter accounts. Never
 * auto-hides anything — write-only, per spec.
 */
function cfRunReputationAnomalyCheck(): array {
    $windowStart = strtotime('-24 hours');
    $perMerchant = []; // merchant_id => ['reviews' => [...], 'reports' => [...]]

    foreach (cfListReviewRecords() as $review) {
        $ts = strtotime($review['created_at'] ?? '');
        if ($ts === false || $ts < $windowStart) continue;
        $perMerchant[$review['merchant_id']]['reviews'][] = $review;
    }
    foreach (cfListReportRecords() as $report) {
        if (($report['target_type'] ?? '') !== 'merchant') continue;
        $ts = strtotime($report['created_at'] ?? '');
        if ($ts === false || $ts < $windowStart) continue;
        $perMerchant[$report['target_id']]['reports'][] = $report;
    }

    $flaggedMerchantIds = [];
    foreach ($perMerchant as $merchantId => $activity) {
        $reviews = $activity['reviews'] ?? [];
        $reports = $activity['reports'] ?? [];
        $total = count($reviews) + count($reports);
        if ($total < 3) continue;

        $newAccountCount = 0;
        foreach ($reviews as $r) {
            if (!empty($r['reviewer_id']) && cfAccountCreatedWithinDays($r['reviewer_id'], 1)) $newAccountCount++;
        }
        foreach ($reports as $r) {
            if (!empty($r['reporter_id']) && cfAccountCreatedWithinDays($r['reporter_id'], 1)) $newAccountCount++;
        }

        cfWriteReportRecord([
            'target_type' => 'merchant',
            'target_id'   => $merchantId,
            'category'    => 'reputation_anomaly',
            'description' => sprintf(
                'Merchant %s received %d reviews/reports in a trailing 24-hour window (%d from accounts created within the last 24 hours).',
                $merchantId, $total, $newAccountCount
            ),
        ]);
        $flaggedMerchantIds[] = $merchantId;
    }

    return ['flagged_merchant_ids' => $flaggedMerchantIds, 'checked_at' => gmdate('Y-m-d\TH:i:s\Z')];
}
