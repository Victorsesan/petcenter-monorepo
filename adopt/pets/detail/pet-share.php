<?php
/**
 * TASK-MANUS-06 START — F3 server-side share preview endpoint.
 * Social crawlers need metadata before client-side pet JSON is fetched. This
 * endpoint reads the public listing record, emits escaped OG/Twitter tags, and
 * redirects human visitors to the canonical detail page.
 * TASK-MANUS-06 END
 */

function pc_share_id($value) {
    $value = preg_replace('/[^A-Za-z0-9\-]/', '', (string)$value);
    return $value === '' ? null : $value;
}
function pc_share_html($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function pc_share_json($path) {
    if (!is_file($path)) return null;
    $raw = file_get_contents($path);
    if ($raw === false) return null;
    $data = json_decode($raw, true);
    return json_last_error() === JSON_ERROR_NONE && is_array($data) ? $data : null;
}

$category = pc_share_id($_GET['category'] ?? '');
$petId = pc_share_id($_GET['pet_id'] ?? '');
$canonical = '/pets/detail/?pet_id=' . rawurlencode((string)$petId) . '&category=' . rawurlencode((string)$category);
$pet = null;
if ($category && $petId) {
    $base = realpath(__DIR__ . '/../../adoptions/pets');
    $candidate = __DIR__ . '/../../adoptions/pets/' . $category . '/' . $petId . '/pet.json';
    $directory = realpath(dirname($candidate));
    if ($base && $directory && ($directory === $base || str_starts_with($directory, $base . DIRECTORY_SEPARATOR))) {
        $pet = pc_share_json($candidate);
    }
}

$name = is_array($pet) ? ($pet['name'] ?? 'Pet Details') : 'Pet Details';
$description = is_array($pet) ? ($pet['description_short'] ?? $pet['description'] ?? 'Browse this pet listing at Pet Center.') : 'Browse adoptable pets at Pet Center.';
$image = is_array($pet) ? ($pet['single_image_url'] ?? (is_array($pet['bulk_images_url'] ?? null) ? ($pet['bulk_images_url'][0] ?? '') : '')) : '';
if ($image && !preg_match('/^https:\/\//i', $image) && !str_starts_with($image, '/')) $image = '';

if (!empty($_SERVER['HTTP_USER_AGENT']) && !preg_match('/bot|crawler|spider|facebookexternalhit|slackbot|twitterbot|linkedinbot/i', $_SERVER['HTTP_USER_AGENT'])) {
    header('Location: ' . $canonical, true, 302);
    exit;
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta property="og:type" content="website">
<meta property="og:site_name" content="Pet Center">
<meta property="og:title" content="<?= pc_share_html($name) ?> — Pet Center">
<meta property="og:description" content="<?= pc_share_html($description) ?>">
<?php if ($image): ?><meta property="og:image" content="<?= pc_share_html($image) ?>">
<?php endif; ?><meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= pc_share_html($name) ?> — Pet Center">
<meta name="twitter:description" content="<?= pc_share_html($description) ?>">
<?php if ($image): ?><meta name="twitter:image" content="<?= pc_share_html($image) ?>">
<?php endif; ?><meta http-equiv="refresh" content="0;url=<?= pc_share_html($canonical) ?>">
<title><?= pc_share_html($name) ?> — Pet Center</title>
</head>
<body style="font-family:Sora,Arial,sans-serif;color:#10214b;border-top:4px solid #ED7624;padding:32px;">
<p>Opening <a href="<?= pc_share_html($canonical) ?>" style="color:#ED7624;">the Pet Center listing</a>…</p>
</body>
</html>
