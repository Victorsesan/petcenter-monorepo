<?php
/**
 * Task 3: hardened adoption-status lookup.
 *
 * This endpoint deliberately never returns the raw application.json file.
 * A valid adoption code and the applicant's last name are both required.
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

const RATE_LIMIT_MAX = 10;
const RATE_LIMIT_WINDOW = 600;

function jsonResponse(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function requestData(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '', true);
    if (!is_array($data)) {
        $data = $_POST;
    }
    return is_array($data) ? $data : [];
}

function clientIp(): string
{
    // Do not trust forwarded headers for rate limiting; the web server's
    // remote address is the only value this endpoint treats as authoritative.
    return isset($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)
        ? $_SERVER['REMOTE_ADDR']
        : 'unknown';
}

function rateLimitAllows(string $ip): bool
{
    $dir = __DIR__ . '/rate-limit-data';
    if (!is_dir($dir) && !@mkdir($dir, 0750, true) && !is_dir($dir)) {
        error_log('Adoption status rate-limit directory could not be created.');
        return true;
    }

    $key = hash('sha256', $ip);
    $path = $dir . '/ip-' . $key . '.json';
    $handle = @fopen($path, 'c+');
    if (!$handle) {
        error_log('Adoption status rate-limit file could not be opened.');
        return true;
    }

    if (!flock($handle, LOCK_EX)) {
        fclose($handle);
        return true;
    }

    $contents = stream_get_contents($handle);
    $timestamps = json_decode($contents ?: '[]', true);
    if (!is_array($timestamps)) {
        $timestamps = [];
    }
    $cutoff = time() - RATE_LIMIT_WINDOW;
    $timestamps = array_values(array_filter($timestamps, static fn($value): bool => is_int($value) && $value >= $cutoff));
    $allowed = count($timestamps) < RATE_LIMIT_MAX;
    if ($allowed) {
        $timestamps[] = time();
        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode($timestamps));
        fflush($handle);
    }
    flock($handle, LOCK_UN);
    fclose($handle);
    return $allowed;
}

function normalizedLastName(string $name): string
{
    $name = trim((string)preg_replace('/\s+/u', ' ', $name));
    if ($name === '') {
        return '';
    }
    $parts = preg_split('/\s+/u', $name);
    return mb_strtolower((string)end($parts), 'UTF-8');
}

function applicationLastName(string $applicantName): string
{
    return normalizedLastName($applicantName);
}

function publicApplicationFields(array $application): array
{
    $fields = [
        'adoption_code', 'applicant_name', 'applicant_email', 'applicant_phone',
        'adoption_fee', 'pet_name', 'pet_breed', 'pet_age', 'pet_image_url',
        'status', 'applied_at', 'approved_by', 'approved_position', 'approved_at',
        'checkout_url', 'rejection_notes', 'additional_pet_ids'
    ];
    $result = [];
    foreach ($fields as $field) {
        if (array_key_exists($field, $application)) {
            $result[$field] = $application[$field];
        }
    }
    if (!isset($result['additional_pet_ids']) || !is_array($result['additional_pet_ids'])) {
        $result['additional_pet_ids'] = [];
    }
    return $result;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
}

if (!rateLimitAllows(clientIp())) {
    jsonResponse(['success' => false, 'message' => 'Too many lookups. Please wait a moment and try again.'], 429);
}

$data = requestData();
$code = strtoupper(trim((string)($data['code'] ?? '')));
$lastName = trim((string)($data['last_name'] ?? ''));

// The generic failure intentionally covers malformed, missing, nonexistent,
// and wrong-last-name cases so the endpoint cannot be used as an oracle.
$notFound = static function (): never {
    jsonResponse([
        'success' => false,
        'message' => 'No application found matching that code and last name.'
    ], 404);
};

if (!preg_match('/^AD-\d{5}-\d{4}$/', $code) || $lastName === '' || mb_strlen($lastName, 'UTF-8') > 80) {
    $notFound();
}

$applicationsBase = realpath(__DIR__ . '/../applications');
if ($applicationsBase === false) {
    jsonResponse(['success' => false, 'message' => 'Status service temporarily unavailable.'], 503);
}

$appFolder = realpath($applicationsBase . DIRECTORY_SEPARATOR . $code);
$basePrefix = rtrim($applicationsBase, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
if ($appFolder === false || strpos($appFolder, $basePrefix) !== 0) {
    $notFound();
}

$appFile = $appFolder . DIRECTORY_SEPARATOR . 'application.json';
if (!is_file($appFile) || !is_readable($appFile)) {
    $notFound();
}

$application = json_decode((string)file_get_contents($appFile), true);
if (!is_array($application) || applicationLastName((string)($application['applicant_name'] ?? '')) !== normalizedLastName($lastName)) {
    $notFound();
}

jsonResponse(['success' => true] + publicApplicationFields($application));
