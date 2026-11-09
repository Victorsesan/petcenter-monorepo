<?php
// adoption-application.php
// Screenshot method: browser generates the PDF via html2canvas+jsPDF and sends it here.
// PHP just receives it, saves files, attaches PDF to admin email, returns PDF for download.
// NO Dompdf needed — PDF is already built by the browser.

ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        error_log('adoption-application2.php FATAL: ' . $err['message'] . ' in ' . $err['file'] . ':' . $err['line']);
        echo json_encode(['success' => false, 'message' => 'Server error. Please contact support.']);
    }
});

// ── Helpers ───────────────────────────────────────────────────────────────────

function json_out($ok, $msg, $extra = []) {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['success' => (bool)$ok, 'message' => $msg], $extra));
    exit;
}

function ini_size_to_bytes($val) {
    $val  = trim($val);
    $last = strtolower($val[strlen($val) - 1]);
    $num  = (int)$val;
    switch ($last) {
        case 'g': return $num * 1024 * 1024 * 1024;
        case 'm': return $num * 1024 * 1024;
        case 'k': return $num * 1024;
        default:  return $num;
    }
}

function sanitize_folder($email) {
    $e = strtolower(trim($email));
    $e = str_replace(['@', '.'], '__', $e);
    $e = preg_replace('/[^a-z0-9_]/', '_', $e);
    return $e;
}

function safe_name($name) {
    return preg_replace('/[^A-Za-z0-9_\-\.]/', '_', mb_substr($name, 0, 200));
}

function normalize_field($key) {
    $out = [];
    if (!isset($_FILES[$key])) return $out;
    $f = $_FILES[$key];
    if (is_array($f['name'])) {
        $count = count($f['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($f['error'][$i] === UPLOAD_ERR_OK) {
                $out[] = ['name' => $f['name'][$i], 'tmp' => $f['tmp_name'][$i], 'size' => $f['size'][$i], 'type' => $f['type'][$i]];
            }
        }
    } else {
        if ($f['error'] === UPLOAD_ERR_OK) {
            $out[] = ['name' => $f['name'], 'tmp' => $f['tmp_name'], 'size' => $f['size'], 'type' => $f['type']];
        }
    }
    return $out;
}

// ── Preflight ─────────────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(false, 'POST required.');

$postMax    = ini_get('post_max_size');
$contentLen = isset($_SERVER['CONTENT_LENGTH']) ? (int)$_SERVER['CONTENT_LENGTH'] : 0;
if ($postMax && $contentLen > 0 && $contentLen > ini_size_to_bytes($postMax)) {
    http_response_code(413);
    json_out(false, 'Request too large. Please reduce file sizes.');
}

// ── Config ────────────────────────────────────────────────────────────────────
require_once __DIR__ . '/../apply-env.php';

$admin_defaults = ['adopt@petcenterco.com', 'zeusvictor88@gmail.com'];
$ADMIN_EMAILS   = applyEnvEmails('ADMIN_EMAILS', $admin_defaults);
$UPLOAD_BASE    = applyEnvPath('UPLOAD_BASE', __DIR__ . '/uploads/adoption-applications/');
$LOG_FILE       = applyEnvPath('LOG_FILE', __DIR__ . '/adoption_applications.log');

if (!defined('MAX_PER_FIELD')) define('MAX_PER_FIELD', 5);
if (!defined('MAX_FILE_SIZE')) define('MAX_FILE_SIZE', 5 * 1024 * 1024);

$ALLOWED_EXT  = ['jpg', 'jpeg', 'png', 'pdf'];
$ALLOWED_MIME = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];

// ── Email validation ──────────────────────────────────────────────────────────

$user_email = '';
foreach (['user_email', 'email', 'userEmail', 'appEmail', 'app_email'] as $k) {
    if (!empty($_POST[$k])) { $user_email = filter_var(trim($_POST[$k]), FILTER_SANITIZE_EMAIL); break; }
}
if (!$user_email || !filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
    json_out(false, 'Please provide a valid email address.');
}

$applicant_name = !empty($_POST['name']) ? trim($_POST['name']) : '';

// ── Submission ID (use the adoption code generated client-side, validate format) ──

$sid = isset($_POST['adoption_code']) ? trim($_POST['adoption_code']) : '';
if (!$sid || !preg_match('/^AD-\d{5}-\d{4}$/', $sid)) {
    // Fallback: generate one in the same AD-#####-#### format if missing/invalid
    $sid = 'AD-' . str_pad((string)random_int(0, 99999), 5, '0', STR_PAD_LEFT) . '-' . str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
}

// ── Upload directory ──────────────────────────────────────────────────────────

if (!is_dir($UPLOAD_BASE)) {
    if (!mkdir($UPLOAD_BASE, 0755, true)) json_out(false, 'Server error: cannot create uploads folder.');
}
$user_folder = sanitize_folder($user_email);
$target_dir  = rtrim($UPLOAD_BASE, '/\\') . DIRECTORY_SEPARATOR . $user_folder;
if (!is_dir($target_dir)) {
    if (!mkdir($target_dir, 0755, true)) json_out(false, 'Server error: cannot create user folder.');
}

// ── Save uploaded ID / address / vet files ────────────────────────────────────

$TEMP_BASE = rtrim($UPLOAD_BASE, '/\\') . DIRECTORY_SEPARATOR . 'temp';

$saved_files = [];
foreach (['file_id', 'file_address', 'file_vet'] as $field) {

    $token_key = $field . '_token';
    $tokens    = isset($_POST[$token_key]) ? (array)$_POST[$token_key] : [];

    $token_files = [];
    foreach ($tokens as $token) {
        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $token)) continue;
        $temp_meta = $TEMP_BASE . DIRECTORY_SEPARATOR . $token . '.json';
        if (!file_exists($temp_meta)) continue;
        $meta = json_decode(file_get_contents($temp_meta), true);
        if (!$meta || empty($meta['path']) || !file_exists($meta['path'])) continue;

        $src      = $meta['path'];
        $ext      = strtolower(pathinfo($src, PATHINFO_EXTENSION));
        $orig     = $meta['original'] ?? basename($src);
        $safe     = safe_name(pathinfo($orig, PATHINFO_FILENAME));
        try { $uniq = $sid . '_' . substr(bin2hex(random_bytes(4)), 0, 8) . '_' . $safe . '.' . $ext; }
        catch (Exception $e) { $uniq = $sid . '_' . substr(md5(uniqid()), 0, 8) . '_' . $safe . '.' . $ext; }

        $dest = $target_dir . DIRECTORY_SEPARATOR . $uniq;
        if (@rename($src, $dest)) {
            @unlink($temp_meta);
            @chmod($dest, 0644);
            $mime = $meta['mime'] ?? 'application/octet-stream';
            $token_files[] = ['path' => $dest, 'mime' => $mime, 'original' => $orig];
        }
    }

    $raw_entries = normalize_field($field);
    $entries_to_save = array_merge($token_files, $raw_entries);

    if ($field === 'file_id' && count($entries_to_save) < 2) {
        json_out(false, 'Please upload both front and back of your Government ID.');
    }
    if ($field === 'file_address' && count($entries_to_save) === 0) {
        json_out(false, 'Please upload proof of address.');
    }

    foreach ($raw_entries as $entry) {
        if ($entry['size'] > MAX_FILE_SIZE) json_out(false, "File too large: {$entry['name']}. Max 5MB.");
        $ext = strtolower(pathinfo($entry['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $ALLOWED_EXT)) json_out(false, "Invalid file type: {$entry['name']}.");
        $mime = function_exists('mime_content_type') ? @mime_content_type($entry['tmp']) : $entry['type'];
        if (!in_array($mime, $ALLOWED_MIME)) json_out(false, "Invalid file content: {$entry['name']}.");
        $safe = safe_name(pathinfo($entry['name'], PATHINFO_FILENAME));
        try { $uniq = $sid . '_' . substr(bin2hex(random_bytes(4)), 0, 8) . '_' . $safe . '.' . $ext; }
        catch (Exception $e) { $uniq = $sid . '_' . substr(md5(uniqid()), 0, 8) . '_' . $safe . '.' . $ext; }
        $dest = $target_dir . DIRECTORY_SEPARATOR . $uniq;
        if (!@move_uploaded_file($entry['tmp'], $dest)) json_out(false, "Could not save: {$entry['name']}.");
        @chmod($dest, 0644);
        $token_files[] = ['path' => $dest, 'mime' => $mime, 'original' => $entry['name']];
    }

    foreach ($token_files as $tf) {
        $saved_files[] = $tf;
    }
}

// ── Save signature (sent as base64 PNG data URL in $_POST['signature']) ───────

$signature_saved_name = null;
if (!empty($_POST['signature']) && strpos($_POST['signature'], 'data:image/png;base64,') === 0) {
    $sigImageData = base64_decode(str_replace('data:image/png;base64,', '', $_POST['signature']));
    if ($sigImageData !== false) {
        $sig_dest = $target_dir . DIRECTORY_SEPARATOR . $sid . '_signature.png';
        if (@file_put_contents($sig_dest, $sigImageData)) {
            @chmod($sig_dest, 0644);
            $signature_saved_name = $sid . '_signature.png';
            $saved_files[] = ['path' => $sig_dest, 'mime' => 'image/png', 'original' => 'signature-' . $sid . '.png'];
        }
    }
}

// ── Receive the browser-generated PDF ────────────────────────────────────────

$pdf_path = '';

if (isset($_FILES['screenshot_pdf']) && $_FILES['screenshot_pdf']['error'] === UPLOAD_ERR_OK) {
    $pdf_file = $_FILES['screenshot_pdf'];
    $pdf_dest = $target_dir . DIRECTORY_SEPARATOR . $sid . '_application.pdf';
    if (@move_uploaded_file($pdf_file['tmp_name'], $pdf_dest)) {
        @chmod($pdf_dest, 0644);
        $pdf_path = $pdf_dest;
        error_log("adoption-application.php: PDF saved -> {$pdf_dest}");
    } else {
        error_log("adoption-application.php: Could not save PDF to {$pdf_dest}");
    }
} else {
    $err_code = isset($_FILES['screenshot_pdf']['error']) ? $_FILES['screenshot_pdf']['error'] : 'not set';
    error_log("adoption-application.php: PDF missing or error={$err_code}");
}

// ── Create /adoptions/applications/{adoption_code}/ and save application.json + metadata.json ──
// This mirrors the folder structure & data contract used by submit-adoption.php so the
// homepage status checker and /adoptions/status/ dashboard can read this application by code.
// IMPORTANT: this PHP file lives at /apply/adoption-application.php, but the web-readable
// /adoptions/ folder lives at the SITE ROOT (e.g. adopt.petcenterco.com/adoptions/...), one
// directory above /apply/. So we step up one level from __DIR__ before appending the path.

$ADOPTIONS_BASE = dirname(__DIR__) . '/adoptions/applications/';
$appDir = $ADOPTIONS_BASE . $sid . '/';
$appFolderCreated = (@mkdir($appDir, 0755, true) || is_dir($appDir));

if ($appFolderCreated) {
    // Pull every posted field generically (skip arrays-of-files / tokens, those are handled above)
    $formData = [];
    foreach ($_POST as $key => $value) {
        if (!is_array($value)) {
            $formData[$key] = $value;
        }
    }
    $reasonArr        = isset($_POST['reason'])             ? (array)$_POST['reason']             : [];
    $hearArr          = isset($_POST['hear'])               ? (array)$_POST['hear']               : [];
    $additionalPetIds = isset($_POST['additional_pet_ids']) ? array_values(array_filter((array)$_POST['additional_pet_ids'])) : [];

    // Build relative (web-safe) file list for the JSON record — store filenames only,
    // the actual files already live in the same user folder ($target_dir) saved above.
    $uploadedFileNames = [];
    foreach ($saved_files as $sf) {
        if (!empty($sf['path'])) $uploadedFileNames[] = basename($sf['path']);
    }

    $applicationData = [
        'adoption_code'    => $sid,
        'pet_id'           => $formData['pet_id']        ?? '',
        'pet_name'         => $formData['pet_name']       ?? '',
        'pet_image_url'    => $formData['pet_image_url']  ?? '',
        'pet_category'     => $formData['pet_category']   ?? '',
        'pet_breed'        => $formData['pet_breed']      ?? '',
        'pet_age'          => $formData['pet_age']        ?? '',
        'pet_size'         => $formData['pet_size']       ?? '',
        'adoption_fee'     => $formData['adoption_fee']   ?? '',
        'applicant_name'   => $formData['name']           ?? '',
        'applicant_email'  => $user_email,
        'applicant_phone'  => $formData['phone']          ?? '',
        'address'          => $formData['address']        ?? '',
        'apartment'        => $formData['apt']            ?? '',
        'city'             => $formData['city']           ?? '',
        'state'            => $formData['state']          ?? '',
        'zip'              => $formData['zip']             ?? '',
        'id_number'        => $formData['id_number']       ?? '',
        'spouse'           => $formData['spouse']          ?? '',
        'employment_status'=> $formData['employment_status'] ?? '',
        'residence_type'   => $formData['residence_type']  ?? '',
        'landlord_allows'  => $formData['landlord_allows']  ?? '',
        'landlord_contact' => $formData['landlord_contact'] ?? '',
        'home_type'        => $formData['home_type']        ?? '',
        'time_at_address'  => $formData['time_at_address']  ?? '',
        'household_list'   => $formData['household_list']   ?? '',
        'past_animals'     => $formData['past_animals']     ?? '',
        'can_afford'       => $formData['can_afford']       ?? '',
        'have_vet'         => $formData['have_vet']         ?? '',
        'vet_name'         => $formData['vet_name']         ?? '',
        'vet_address'      => $formData['vet_address']      ?? '',
        'vet_phone'        => $formData['vet_phone']        ?? '',
        'allow_contact_vet'=> $formData['allow_contact_vet'] ?? '',
        'where_keep'       => $formData['where_keep']        ?? '',
        'inside_plan'      => $formData['inside_plan']       ?? '',
        'outside_plan'     => $formData['outside_plan']      ?? '',
        'introduce_existing'=> $formData['introduce_existing'] ?? '',
        'handle_undesirable'=> $formData['handle_undesirable'] ?? '',
        'want_adopt'       => $formData['want_adopt']        ?? '',
        'sex_pref'         => $formData['sex_pref']          ?? '',
        'reason'           => $reasonArr,
        'hear'             => $hearArr,
        'primary_caregiver'=> $formData['primary_caregiver'] ?? '',
        'additional_info'  => $formData['additional_info']   ?? '',
        'additional_pet_ids' => $additionalPetIds,
        'submission_date'  => $formData['submission_date']   ?? date('Y-m-d'),
        'signature_file'   => $signature_saved_name,
        'uploaded_files'   => $uploadedFileNames,
        'status'           => 'PENDING',
        'applied_at'       => date('Y-m-d H:i:s'),
        'approved_at'      => null,
        'checkout_url'     => null,
        'rejection_notes'  => null
    ];

    @file_put_contents(
        $appDir . 'application.json',
        json_encode($applicationData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );

    $metadata = [
        'adoption_code'   => $sid,
        'pet_id'          => $applicationData['pet_id'],
        'pet_name'        => $applicationData['pet_name'],
        'applicant_name'  => $applicationData['applicant_name'],
        'applicant_email' => $applicationData['applicant_email'],
        'status'          => 'PENDING',
        'created_at'      => $applicationData['applied_at']
    ];

    @file_put_contents(
        $appDir . 'metadata.json',
        json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );
} else {
    error_log("adoption-application.php: Could not create application folder -> {$appDir}");
}

// ── Build attachments ─────────────────────────────────────────────────────────

$attachments = [];
if ($pdf_path && file_exists($pdf_path)) {
    $attachments[$pdf_path] = "adoption-{$sid}.pdf";
}
foreach ($saved_files as $sf) {
    if (!empty($sf['path']) && file_exists($sf['path'])) {
        $attachments[$sf['path']] = $sf['original'];
    }
}

// ── Build RAW form data table for admin email body ────────────────────────────

$FIELD_LABELS = [
    'name'=>'Full Name','top_date'=>'Reference Date','address'=>'Address','apt'=>'Apt / Unit #',
    'city'=>'City','state'=>'State','zip'=>'Zip','phone'=>'Phone','user_email'=>'Email',
    'additional_info'=>'Additional Info','id_number'=>'ID / License #','spouse'=>'Spouse / Partner',
    'employment_status'=>'Employment Status','residence_type'=>'Residence Type',
    'landlord_allows'=>'Landlord Allows Pets','landlord_contact'=>'Landlord Contact',
    'home_type'=>'Home Type','time_at_address'=>'Time at Address',
    'household_list'=>'Household Members','past_animals'=>'Past Animals',
    'can_afford'=>'Can Afford Pet Care','have_vet'=>'Has Vet','vet_name'=>'Vet Name',
    'vet_address'=>'Vet Address','vet_phone'=>'Vet Phone','allow_contact_vet'=>'Allow Vet Contact',
    'pet1_name'=>'Pet 1 Name','pet1_breed'=>'Pet 1 Breed','pet1_age'=>'Pet 1 Age','pet1_spay'=>'Pet 1 Spayed',
    'pet2_name'=>'Pet 2 Name','pet2_breed'=>'Pet 2 Breed','pet2_age'=>'Pet 2 Age','pet2_spay'=>'Pet 2 Spayed',
    'pet3_name'=>'Pet 3 Name','pet3_breed'=>'Pet 3 Breed','pet3_age'=>'Pet 3 Age','pet3_spay'=>'Pet 3 Spayed',
    'where_keep'=>'Pet Storage When Away','inside_plan'=>'Inside Arrangement','outside_plan'=>'Outside Arrangement',
    'introduce_existing'=>'Introduce to Existing Pets','handle_undesirable'=>'Handle Bad Behaviour',
    'want_adopt'=>'Want to Adopt','sex_pref'=>'Sex Preference','primary_caregiver'=>'Primary Caregiver',
    'submission_date'=>'Submission Date',
];
$raw_rows = '';
foreach ($FIELD_LABELS as $key => $label) {
    $val = isset($_POST[$key]) ? trim($_POST[$key]) : '';
    if ($val === '') continue;
    $raw_rows .= "<tr><td style='padding:6px 10px;font-weight:600;color:#555;white-space:nowrap;border-bottom:1px solid #f0f0f0;vertical-align:top'>" . htmlspecialchars($label) . "</td>"
               . "<td style='padding:6px 10px;border-bottom:1px solid #f0f0f0'>" . nl2br(htmlspecialchars($val)) . "</td></tr>";
}
foreach (['reason[]'=>'Reason for Adopting','hear[]'=>'How Heard About Us'] as $key=>$label) {
    $vals = isset($_POST[$key]) ? (array)$_POST[$key] : [];
    if (empty($vals)) continue;
    $raw_rows .= "<tr><td style='padding:6px 10px;font-weight:600;color:#555;white-space:nowrap;border-bottom:1px solid #f0f0f0;vertical-align:top'>" . htmlspecialchars($label) . "</td>"
               . "<td style='padding:6px 10px;border-bottom:1px solid #f0f0f0'>" . htmlspecialchars(implode(', ', $vals)) . "</td></tr>";
}
$raw_table = $raw_rows
    ? "<h3 style='color:#ED7624;margin:18px 0 8px'>Raw Form Data</h3><table style='width:100%;border-collapse:collapse;font-size:13px;background:#fafafa;border:1px solid #eee'>{$raw_rows}</table>"
    : '';

// ── SEND TO ADMINS ────────────────────────────────────────────────────────────

$host     = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'petcenterco.com';
$scheme   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$logo_url = $scheme . '://' . $host . '/assets/images/logo-petcenter.png';

$admin_subject = "New Adoption Application — {$sid}";

$admin_body  = "<div style='font-family:Inter,Arial,sans-serif;color:#111'>";
$admin_body .= "<img src='{$logo_url}' alt='Pet Center' style='height:48px;display:block;margin-bottom:8px'>";
$admin_body .= "<h2 style='color:#ED7624;margin:0 0 12px'>New Adoption Application</h2>";
$admin_body .= "<p><strong>Adoption Code:</strong> {$sid}</p>";
$admin_body .= "<p><strong>Applicant email:</strong> " . htmlspecialchars($user_email) . "</p>";
if ($applicant_name) $admin_body .= "<p><strong>Name:</strong> " . htmlspecialchars($applicant_name) . "</p>";
$admin_body .= "<p>The completed application PDF is attached. Uploaded documents and signature are also attached.</p>";
$admin_body .= "<h3 style='color:#ED7624;margin:18px 0 8px'>Attached Files</h3><ul style='font-size:13px'>";
foreach ($saved_files as $sf) {
    $admin_body .= "<li>" . htmlspecialchars($sf['original']) . "</li>";
}
$admin_body .= "</ul>";
$admin_body .= $raw_table;
$admin_body .= "</div>";

$admin_sent = false;

if (function_exists('sendPdfEmail')) {
    try {
        $result = sendPdfEmail($ADMIN_EMAILS, $admin_subject, $admin_body, "adoption-{$sid}.pdf", $attachments);
        if (is_array($result) && !empty($result['success'])) {
            $admin_sent = true;
        }
    } catch (Throwable $e) {
        error_log('sendPdfEmail: ' . $e->getMessage());
    }
}

if (!$admin_sent) {
    $boundary = md5(time());
    $headers  = "From: Pet Center <noreply@petcenterco.com>\r\n";
    $headers .= "Reply-To: adopt@petcenterco.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";

    $body  = "--{$boundary}\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
    $body .= $admin_body . "\r\n";

    foreach ($attachments as $path => $name) {
        if (!file_exists($path)) continue;
        $file_content = chunk_split(base64_encode(file_get_contents($path)));
        $mime = function_exists('mime_content_type') ? mime_content_type($path) : 'application/octet-stream';
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: {$mime}; name=\"{$name}\"\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n";
        $body .= "Content-Disposition: attachment; filename=\"{$name}\"\r\n\r\n";
        $body .= $file_content . "\r\n";
    }

    $body .= "--{$boundary}--\r\n";

    foreach ($ADMIN_EMAILS as $to) {
        @mail($to, $admin_subject, $body, $headers);
    }
}

// ── USER EMAIL — HTML only, NO PDF ────────────────────────────────────────────

$display_name = $applicant_name ?: explode('@', $user_email)[0];
$year         = date('Y');
$date_str     = date('M j, Y \a\t g:i A');

$user_subject = "Adoption Application Received — Pet Center";
$user_html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<body style="margin:0;padding:0;background:#ffffff;font-family:Arial,sans-serif;color:#1a1a1a;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff;padding:40px 20px;">
  <tr><td align="center">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;">

      <tr><td align="center" style="padding-bottom:30px;">
        <img src="{$logo_url}" alt="PetCenter" style="width:140px;height:auto;">
      </td></tr>

      <tr><td align="center">
        <h1 style="margin:0;font-size:26px;font-weight:700;">Application Received</h1>
        <p style="margin:10px 0 0;font-size:16px;color:#4b5563;">Thank you, {$display_name} &mdash; we have your adoption application.</p>
      </td></tr>

      <tr><td align="center" style="padding:30px 0 40px;">
        <p style="margin:0;font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:1px;font-weight:600;">Application Status</p>
        <p style="margin:5px 0 0;font-size:42px;font-weight:800;">Under Review</p>
      </td></tr>

      <tr><td>
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f9fafb;border-radius:12px;padding:24px;border:1px solid #f1f5f9;">
          <tr><td style="padding-bottom:12px;border-bottom:1px solid #e5e7eb;">
            <span style="font-size:14px;color:#6b7280;">Adoption Code</span>
            <span style="font-size:14px;color:#1a1a1a;font-weight:700;float:right;font-family:monospace;">{$sid}</span>
          </td></tr>
          <tr><td style="padding:12px 0;border-bottom:1px solid #e5e7eb;">
            <span style="font-size:14px;color:#6b7280;">Date Submitted</span>
            <span style="font-size:14px;color:#1a1a1a;font-weight:600;float:right;">{$date_str}</span>
          </td></tr>
          <tr><td style="padding:12px 0;border-bottom:1px solid #e5e7eb;">
            <span style="font-size:14px;color:#6b7280;">Applicant</span>
            <span style="font-size:14px;color:#1a1a1a;font-weight:600;float:right;">{$display_name}</span>
          </td></tr>
          <tr><td style="padding-top:12px;">
            <span style="font-size:14px;color:#6b7280;">Status</span>
            <span style="font-size:12px;color:#475569;font-weight:700;float:right;background:#f1f5f9;padding:3px 12px;border-radius:20px;border:1px solid #cbd5e1;text-transform:uppercase;">Under Review</span>
          </td></tr>
        </table>
      </td></tr>

      <tr><td style="padding:40px 0 30px;">
        <h3 style="margin:0 0 24px;font-size:18px;font-weight:700;">What Happens Next</h3>

        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:20px;"><tr>
          <td style="width:32px;vertical-align:top;"><div style="width:24px;height:24px;background:#1a1a1a;border-radius:50%;color:#ffffff;text-align:center;line-height:24px;font-size:12px;font-weight:700;">1</div></td>
          <td style="padding-left:15px;vertical-align:top;">
            <p style="margin:0;font-size:15px;font-weight:700;">Application Review — 24 to 72 Hours</p>
            <p style="margin:4px 0 0;font-size:14px;color:#4b5563;line-height:1.5;">Our adoption team carefully reviews every application. You will hear from us within 24–72 hours with an update on your application status.</p>
          </td>
        </tr></table>

        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:20px;"><tr>
          <td style="width:32px;vertical-align:top;"><div style="width:24px;height:24px;background:#1a1a1a;border-radius:50%;color:#ffffff;text-align:center;line-height:24px;font-size:12px;font-weight:700;">2</div></td>
          <td style="padding-left:15px;vertical-align:top;">
            <p style="margin:0;font-size:15px;font-weight:700;">Meet &amp; Greet</p>
            <p style="margin:4px 0 0;font-size:14px;color:#4b5563;line-height:1.5;">If your application is approved, we will coordinate a meet-and-greet so you can bond with your potential new companion before finalising the adoption.</p>
          </td>
        </tr></table>

        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:20px;"><tr>
          <td style="width:32px;vertical-align:top;"><div style="width:24px;height:24px;background:#1a1a1a;border-radius:50%;color:#ffffff;text-align:center;line-height:24px;font-size:12px;font-weight:700;">3</div></td>
          <td style="padding-left:15px;vertical-align:top;">
            <p style="margin:0;font-size:15px;font-weight:700;">Adoption Agreement &amp; Payment</p>
            <p style="margin:4px 0 0;font-size:14px;color:#4b5563;line-height:1.5;">Once approved, we email your adoption agreement and payment instructions. After the adoption fee is received, the process is complete.</p>
          </td>
        </tr></table>

        <table width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
          <td style="width:32px;vertical-align:top;"><div style="width:24px;height:24px;background:#1a1a1a;border-radius:50%;color:#ffffff;text-align:center;line-height:24px;font-size:12px;font-weight:700;">4</div></td>
          <td style="padding-left:15px;vertical-align:top;">
            <p style="margin:0;font-size:15px;font-weight:700;">Lifetime Support</p>
            <p style="margin:4px 0 0;font-size:14px;color:#4b5563;line-height:1.5;">Our team remains available after adoption to offer guidance, answer questions, and support you and your new family member every step of the way.</p>
          </td>
        </tr></table>
      </td></tr>

      <tr><td align="center" style="padding:10px 0 40px;">
        <a href="https://g.page/r/CdINyeKnI7eVEBI/review" style="display:inline-block;background:#ED7624;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;padding:12px 28px;border-radius:8px;">Leave a 5-Star Review</a>
      </td></tr>

      <tr><td style="background:#f8fafc;border-radius:12px;padding:40px 20px;text-align:center;">
        <p style="margin:0 0 15px;font-size:14px;color:#1a1a1a;font-weight:700;">Thank you for giving a rescue pet a second chance.</p>
        <p style="margin:0 0 15px;font-size:12px;color:#64748b;line-height:1.6;"><strong>Pet Center</strong><br>
          <a href="https://docs.petcenterco.com/privacy-policy/" style="color:#1a1a1a;text-decoration:none;">Privacy Policy</a> |
          <a href="https://docs.petcenterco.com/returns-refunds/" style="color:#1a1a1a;text-decoration:none;">Returns &amp; Refunds</a> |
          <a href="https://docs.petcenterco.com/contact-us" style="color:#1a1a1a;text-decoration:none;">Contact</a>
        </p>
        <p style="margin:0;font-size:11px;color:#94a3b8;">Sent to {$user_email}. Keep your adoption code handy: {$sid}. You can check your application status anytime at <a href="https://adopt.petcenterco.com/adoptions/status/?code={$sid}" style="color:#94a3b8;">adopt.petcenterco.com/adoptions/status/</a>. If you did not submit this application, please contact us immediately.</p>
      </td></tr>

    </table>
  </td></tr>
</table>
</body>
</html>
HTML;

$user_headers  = "MIME-Version: 1.0\r\n";
$user_headers .= "Content-type: text/html; charset=UTF-8\r\n";
$user_headers .= "From: Pet Center <noreply@petcenterco.com>\r\n";
$user_headers .= "Reply-To: adopt@petcenterco.com\r\n";

@mail($user_email, $user_subject, $user_html, $user_headers);

// ── Log ───────────────────────────────────────────────────────────────────────

$filenames = array_map(function ($f) { return basename($f['path']); }, $saved_files);
if ($pdf_path) $filenames[] = basename($pdf_path);
@file_put_contents($LOG_FILE, date('c') . " | {$sid} | {$user_email} | " . implode(',', $filenames) . PHP_EOL, FILE_APPEND | LOCK_EX);

// ── Success ───────────────────────────────────────────────────────────────────

json_out(true, 'Application submitted successfully!', [
    'submission_id'  => $sid,
    'adoption_code'  => $sid,
]);
