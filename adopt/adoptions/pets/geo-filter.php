<?php
/**
 * geo-filter.php — TASK-3b addition (moves Haversine off the client per the
 * Recommended Enhancement, and moves pet-location geocoding onto the server
 * with a disk-persisted cache instead of Task 3's in-memory, per-page-load
 * cache — same OpenStreetMap Nominatim integration Task 3 already used, just
 * run once here instead of once per visitor's browser).
 *
 * Deployment path assumed to be /adoptions/pets/geo-filter.php, a sibling of
 * the per-category folders it reads from (/adoptions/pets/{category}/...),
 * matching the exact same relative structure the front-end already fetches
 * (index.json -> per-pet pet.json). If the real deployment path differs,
 * only the two require paths below and the front-end's fetch URL need to
 * change — nothing else here is path-dependent.
 *
 * The visitor's own typed "near me" text is still geocoded client-side
 * (unchanged from Task 3b) since that's a single one-off call per search;
 * this endpoint is what makes the *pet* side and the distance math
 * server-side and persistently cached, which is the part that actually
 * repeats across every visitor and every search.
 *
 * GET params:
 *   category (required) — one of the 7 category slugs
 *   lat, lng (required) — the visitor's already-resolved coordinates
 *   radius   (required) — miles
 *
 * Response: { "ids": ["PET-ID-1", "PET-ID-2", ...], "pending": 0 }  — nearest-first.
 *   "pending" (TASK-3d) = how many pets' locations could not be looked up on THIS request
 *   (lookup budget used up, or the geocoder was unreachable). They are simply retried on
 *   the next request; the front-end tells the visitor the list may be incomplete.
 */

header('Content-Type: application/json');

$validCategories = ['cats', 'dogs', 'birds', 'reptiles', 'aquariums', 'livestocks', 'small-mammals'];

$category = isset($_GET['category']) ? $_GET['category'] : '';
$lat      = isset($_GET['lat']) && $_GET['lat'] !== '' ? floatval($_GET['lat']) : null;
$lng      = isset($_GET['lng']) && $_GET['lng'] !== '' ? floatval($_GET['lng']) : null;
$radius   = isset($_GET['radius']) && $_GET['radius'] !== '' ? floatval($_GET['radius']) : null;

// TASK-3d — range checks: reject impossible coordinates and absurd radii instead of computing with them
if (!in_array($category, $validCategories, true) || $lat === null || $lng === null || !$radius
    || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180 || $radius <= 0 || $radius > 20000) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid category, lat, lng, or radius']);
    exit;
}

$categoryDir = __DIR__ . '/pets/' . $category . '/';
$indexFile   = $categoryDir . 'index.json';

if (!file_exists($indexFile)) {
    echo json_encode(['ids' => []]);
    exit;
}

$index   = json_decode(file_get_contents($indexFile), true);
$petIds  = (is_array($index) && !empty($index['pets']) && is_array($index['pets'])) ? $index['pets'] : [];

// ─── Persistent, cross-visitor, cross-category geocode cache ───────────────
// Task 3's client-side cache reset on every page load; this file doesn't.
$cacheFile = __DIR__ . '/geo-cache.json';
$cache = file_exists($cacheFile) ? json_decode(file_get_contents($cacheFile), true) : [];
if (!is_array($cache)) {
    $cache = [];
}
$cacheDirty = false;

/**
 * Great-circle (Haversine) distance in miles between two lat/lng points.
 * Same formula as Task 3's client-side pcHaversineMiles — moved here per
 * this task's "server-side Haversine" requirement.
 */
function pc_haversine_miles($lat1, $lng1, $lat2, $lng2) {
    $earthRadiusMiles = 3958.8;
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
    return $earthRadiusMiles * 2 * atan2(sqrt($a), sqrt(1 - $a));
}

/**
 * TASK-3d — small HTTP GET helper. Uses cURL when the server has it, otherwise
 * file_get_contents (needs allow_url_fopen). Returns the response body, or false
 * on ANY failure (network error, timeout, non-200 status) so callers can tell
 * "the geocoder was unreachable" apart from "the geocoder found nothing".
 * The User-Agent identifies this site, as Nominatim's usage policy requires.
 */
function pc_http_get($url) {
    $userAgent = 'PetCenter-GeoFilter/1.0 (https://adopt.petcenterco.com)';
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 6,
            CURLOPT_USERAGENT      => $userAgent,
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($body !== false && $code === 200) ? $body : false;
    }
    $context = stream_context_create(['http' => ['header' => "User-Agent: $userAgent\r\n", 'timeout' => 6]]);
    $body = @file_get_contents($url, false, $context);
    return $body === false ? false : $body;
}

/**
 * Geocodes a "City, State" string via OpenStreetMap Nominatim (free, no API
 * key — same service Task 3 already used client-side), using and updating
 * the shared on-disk cache passed by reference. Pet Center's pets are all
 * US-located (confirmed via pet.json), so this is always restricted to "us"
 * regardless of which country a visitor is searching from — matching Task
 * 3b's own client-side logic for pet-side geocoding.
 *
 * TASK-3d — return value is now three-way:
 *   array(lat,lng) = found            null  = Nominatim answered and found nothing (cached, not asked again)
 *   false          = could not look it up right now (budget spent / geocoder unreachable) — NOT cached,
 *                    so a transient outage can no longer permanently hide a pet from distance searches
 *                    (the first version cached every failure as "no coordinates", forever).
 */
function pc_geocode_pet_location($location, &$cache, &$cacheDirty, &$freshLookups, $maxFreshLookups, &$lockHandle, $cacheFile) {
    $key = strtolower(trim($location));
    if ($key === '') {
        return null;
    }
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    // TASK-3d — a cold cache must not make one request run for minutes (PHP's default
    // time limit is 30s and each live lookup costs >= 1.1s), so cap live lookups per request.
    // Whatever is left over is reported as "pending" and finished by later requests.
    if ($freshLookups >= $maxFreshLookups) {
        return false;
    }

    // TASK-3d — one lock file shared by every request, so simultaneous visitors cannot
    // each fire their own Nominatim calls (the 1 req/sec policy applies to the whole site,
    // not to each request). The cache is re-read once the lock is ours, because another
    // request may have resolved this very location while we waited.
    if ($lockHandle === null) {
        $lockHandle = @fopen(__DIR__ . '/geo-cache.lock', 'c');
        if ($lockHandle) {
            flock($lockHandle, LOCK_EX);
            $latest = file_exists($cacheFile) ? json_decode(file_get_contents($cacheFile), true) : [];
            if (is_array($latest)) {
                $cache = $latest + $cache; // "+" keeps string keys intact (array_merge would renumber numeric-looking ones)
            }
            if (array_key_exists($key, $cache)) {
                return $cache[$key];
            }
        } else {
            $lockHandle = false; // cannot create the lock file: carry on unlocked rather than fail the request
        }
    }

    // Nominatim's usage policy caps requests at ~1/sec.
    usleep(1100000);
    $freshLookups++;

    $url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=us&q=' . urlencode($location);
    $response = pc_http_get($url);
    if ($response === false) {
        return false; // transient failure: do NOT cache
    }
    $results = json_decode($response, true);
    if (!is_array($results)) {
        return false; // unusable response: do NOT cache
    }

    $coords = (count($results) > 0 && isset($results[0]['lat'], $results[0]['lon']))
        ? ['lat' => floatval($results[0]['lat']), 'lng' => floatval($results[0]['lon'])]
        : null;

    $cache[$key] = $coords;
    $cacheDirty = true;
    return $coords;
}

$matches        = [];
$pending        = 0;   // TASK-3d — pets whose location could not be resolved on this request
$freshLookups   = 0;
$maxFreshLookups = 8;  // TASK-3d — ~9-10s worst case per request, safely inside PHP's default 30s limit
$lockHandle     = null;

foreach ($petIds as $petId) {
    // TASK-3d — the ID is used to build a file path, so only plain ID characters are allowed
    // (index.json is admin-written, but a path built from data should never trust it blindly)
    if (!is_string($petId) || !preg_match('/^[A-Za-z0-9_-]+$/', $petId)) {
        continue;
    }
    $petFile = $categoryDir . $petId . '/pet.json';
    if (!file_exists($petFile)) {
        continue;
    }
    $pet = json_decode(file_get_contents($petFile), true);
    if (!is_array($pet) || empty($pet['location']) || !is_string($pet['location'])) {
        continue;
    }

    $coords = pc_geocode_pet_location($pet['location'], $cache, $cacheDirty, $freshLookups, $maxFreshLookups, $lockHandle, $cacheFile);
    if ($coords === false) {
        $pending++;
        continue; // not resolvable right now — retried on a later request, and reported via "pending"
    }
    if (!$coords) {
        continue; // Nominatim has no match for this location text — leave it out rather than guess
    }

    $distance = pc_haversine_miles($lat, $lng, $coords['lat'], $coords['lng']);
    if ($distance <= $radius) {
        $matches[] = ['id' => isset($pet['id']) ? $pet['id'] : $petId, 'distance' => $distance];
    }
}

if ($cacheDirty) {
    // The lock (held since the first live lookup) makes this the only writer; LOCK_EX is belt and braces.
    if (@file_put_contents($cacheFile, json_encode($cache), LOCK_EX) === false) {
        error_log('geo-filter.php: could not write ' . $cacheFile . ' - the web server user needs write access to this folder');
    }
}
if ($lockHandle) {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
}

usort($matches, function ($a, $b) {
    return $a['distance'] <=> $b['distance'];
});

echo json_encode([
    'ids'     => array_map(function ($m) { return $m['id']; }, $matches),
    'pending' => $pending,
]);
