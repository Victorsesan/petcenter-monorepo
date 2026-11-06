<?php
/**
 * Task A2 — Needs Extra Love maintenance job.
 *
 * Run daily from cron. The job deliberately accepts a root directory so it can
 * be tested without touching live listing data. It supports the current
 * flat-file pet.json layout and the rehoming listing layout used by C2.
 *
 * Example cron (from the repository root):
 *   17 3 * * * /usr/bin/php /path/to/adopt/admin/needs-extra-love-check.php
 */

declare(strict_types=1);

const A2_NEEDS_EXTRA_LOVE_DAYS = 21;

/**
 * Recursively find pet.json records below a listing root without following
 * symlinks. Runtime data stays outside the source tree in normal deployments;
 * this helper is also safe to point at a temporary fixture directory.
 */
function a2FindPetFiles(string $root): array
{
    if (!is_dir($root)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }
        if ($file->getFilename() === 'pet.json') {
            $files[] = $file->getPathname();
        }
    }

    sort($files, SORT_STRING);
    return $files;
}

function a2ReadJson(string $path): ?array
{
    $raw = @file_get_contents($path);
    if ($raw === false || $raw === '') {
        return null;
    }
    $value = json_decode($raw, true);
    return is_array($value) ? $value : null;
}

function a2WriteJson(string $path, array $record): bool
{
    $json = json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    if ($json === false) {
        return false;
    }

    $tmp = $path . '.tmp.' . bin2hex(random_bytes(5));
    if (@file_put_contents($tmp, $json, LOCK_EX) === false) {
        return false;
    }
    @chmod($tmp, 0640);
    if (!@rename($tmp, $path)) {
        @unlink($tmp);
        return false;
    }
    return true;
}

function a2IsActiveListing(array $pet): bool
{
    $status = strtolower(trim((string)($pet['status'] ?? 'active')));
    return !in_array($status, [
        'pending_placement',
        'placement_complete',
        'placement_fell_through',
        'no_longer_available',
        'adopted',
        'closed',
    ], true);
}

function a2HasNoRecentInquiry(array $pet, DateTimeImmutable $cutoff): bool
{
    $lastInquiry = trim((string)($pet['last_inquiry_date'] ?? ''));
    if ($lastInquiry === '') {
        return true;
    }

    try {
        return new DateTimeImmutable($lastInquiry) < $cutoff;
    } catch (Throwable $error) {
        // Invalid legacy dates are treated as stale, but never crash the job.
        return true;
    }
}

/**
 * Mark eligible records and return a deterministic summary.
 *
 * A record is eligible only when it is active, has no inquiry in the threshold
 * window, and has existed at least the threshold number of days. The created
 * date falls back to posted_at/listed_at for compatibility with older records.
 */
function a2MarkNeedsExtraLove(string $root, ?DateTimeImmutable $now = null, int $thresholdDays = A2_NEEDS_EXTRA_LOVE_DAYS): array
{
    $now = $now ?? new DateTimeImmutable('now', new DateTimeZone('UTC'));
    $cutoff = $now->modify('-' . $thresholdDays . ' days');
    $summary = ['scanned' => 0, 'marked' => 0, 'unchanged' => 0, 'skipped' => 0, 'errors' => []];

    foreach (a2FindPetFiles($root) as $path) {
        $summary['scanned']++;
        $pet = a2ReadJson($path);
        if ($pet === null || !a2IsActiveListing($pet)) {
            $summary['skipped']++;
            continue;
        }

        $createdRaw = trim((string)($pet['created_at'] ?? $pet['posted_at'] ?? $pet['listed_at'] ?? ''));
        if ($createdRaw === '') {
            $summary['skipped']++;
            continue;
        }

        try {
            $createdAt = new DateTimeImmutable($createdRaw);
        } catch (Throwable $error) {
            $summary['skipped']++;
            continue;
        }

        if ($createdAt >= $cutoff || !a2HasNoRecentInquiry($pet, $cutoff)) {
            $summary['unchanged']++;
            continue;
        }

        if (($pet['needs_extra_love'] ?? false) === true) {
            $summary['unchanged']++;
            continue;
        }

        $pet['needs_extra_love'] = true;
        $pet['needs_extra_love_marked_at'] = $now->format(DateTimeInterface::ATOM);
        if (!a2WriteJson($path, $pet)) {
            $summary['errors'][] = $path;
            continue;
        }
        $summary['marked']++;
    }

    return $summary;
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $roots = array_values(array_filter([
        __DIR__ . '/../adoptions/pets',
        __DIR__ . '/../rehoming/data/listings',
    ], static fn(string $root): bool => is_dir($root)));

    $total = ['scanned' => 0, 'marked' => 0, 'unchanged' => 0, 'skipped' => 0, 'errors' => []];
    foreach ($roots as $root) {
        $result = a2MarkNeedsExtraLove($root);
        foreach (['scanned', 'marked', 'unchanged', 'skipped'] as $key) {
            $total[$key] += $result[$key];
        }
        $total['errors'] = array_merge($total['errors'], $result['errors']);
    }

    fwrite(STDOUT, json_encode($total, JSON_UNESCAPED_SLASHES) . PHP_EOL);
    exit($total['errors'] === [] ? 0 : 1);
}
