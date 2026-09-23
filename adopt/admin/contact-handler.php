<?php
/**
 * Contact Form Handler
 * BUGFIX-3: corrected from the stale "docs.petcenterco.com" — per
 * ADMIN_DASHBOARD_PROJECT_SUMMARY.md's File Structure section, contact-us/
 * lives directly under /public_html/adopt/, i.e. adopt.petcenterco.com,
 * not docs.petcenterco.com. First flagged in Task 11's "Bugs Found
 * Outside Task Scope."
 * For: adopt.petcenterco.com/contact-us/contact-handler.php
 * 
 * Receives form submission + image tokens
 * Retrieves temp images, sends to admin emails with attachments
 * Sends confirmation to customer
 */

require_once __DIR__ . '/contact-config.php';

header('Content-Type: application/json');

// ============================================
// SECURITY: Check method & CORS
// ============================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

/* BUGFIX-3 START — was hard-coded to allow a domain (docs.petcenterco.com)
   that isn't where contact-us.html is actually served from. Dormant today
   only because contact-us.html's own fetch() call is same-origin (a
   relative path), so the browser never actually checks this header in the
   current flow — but it would silently and confusingly block any future
   legitimate cross-origin call. Corrected to the real production domain
   per ADMIN_DASHBOARD_PROJECT_SUMMARY.md's documented file structure.
   First flagged in Task 11's "Bugs Found Outside Task Scope." */
header('Access-Control-Allow-Origin: https://adopt.petcenterco.com');
/* BUGFIX-3 END */
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// ============================================
// VALIDATE REQUIRED FIELDS
// ============================================
$required = ['fullName', 'email', 'subject', 'department', 'message'];
$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$errors = [];
foreach ($required as $field) {
    if (empty($data[$field])) {
        $errors[] = "Missing: $field";
    }
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode('; ', $errors)]);
    exit;
}

// ============================================
// SANITIZE INPUT
// ============================================
$fullName = trim(htmlspecialchars($data['fullName'], ENT_QUOTES, 'UTF-8'));
$email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
$phone = trim($data['phone'] ?? '');
$subject = trim(htmlspecialchars($data['subject'], ENT_QUOTES, 'UTF-8'));
$department = trim($data['department']);
$message = trim(htmlspecialchars($data['message'], ENT_QUOTES, 'UTF-8'));

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Validate department
$validDepts = ['support', 'sales', 'adoption', 'shipping', 'health-claim', 'other'];
if (!in_array($department, $validDepts)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid department']);
    exit;
}

// ============================================
// PROCESS IMAGE TOKENS
// ============================================
$imageTokens = [];
for ($i = 0; $i < UPLOAD_MAX_FILES; $i++) {
    $tokenKey = "image_token_$i";
    if (!empty($data[$tokenKey])) {
        $imageTokens[] = trim($data[$tokenKey]);
    }
}

// Limit to max 10
$imageTokens = array_slice($imageTokens, 0, UPLOAD_MAX_FILES);

// Retrieve image files from temp dir
$attachments = [];
$tempDir = UPLOAD_TEMP_DIR;
$finalDir = UPLOAD_FINAL_DIR;

foreach ($imageTokens as $token) {
    // Validate token format (hex 32 chars)
    if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
        continue;
    }

    // Find temp file with any extension
    $tempFiles = glob($tempDir . $token . '.*');
    if (empty($tempFiles)) {
        continue;
    }

    $tempFile = $tempFiles[0];
    $ext = pathinfo($tempFile, PATHINFO_EXTENSION);

    // Generate submission ID (once for all images)
    static $submissionId;
    if (!isset($submissionId)) {
        $submissionId = generateSubmissionId();
    }

    // Copy to final location with submission prefix
    $finalFileName = $submissionId . '_' . basename($tempFile);
    $finalFilePath = $finalDir . $finalFileName;

    if (copy($tempFile, $finalFilePath)) {
        $attachments[$finalFilePath] = $finalFileName;
    }

    // Clean up temp file & metadata
    @unlink($tempFile);
    @unlink($tempDir . $token . '.json');
}

// ============================================
// BUILD EMAIL HTML
// ============================================
if (!isset($submissionId)) {
    $submissionId = generateSubmissionId();
}

$emailHTML = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Sora, Arial, sans-serif; color: #2c2416; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; background: #f9f7f4; padding: 20px; }
        .header { background: linear-gradient(135deg, #10214b 0%, #1a2f5c 100%); color: white; padding: 20px; border-radius: 8px; }
        .header h2 { margin: 0; font-size: 22px; }
        .content { background: white; padding: 24px; margin-top: 16px; border-radius: 8px; border: 1px solid rgba(16,33,75,0.12); }
        .field { margin-bottom: 16px; }
        .label { font-weight: 600; color: #10214b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em; display: block; margin-bottom: 4px; }
        .value { color: #2c2416; font-size: 14px; }
        .message-box { background: #f9f7f4; padding: 12px; border-left: 3px solid #d7bd88; border-radius: 4px; margin-top: 12px; }
        .images-section { margin-top: 20px; }
        .image-count { color: #d7bd88; font-weight: 600; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #7a6d5f; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>New Contact Form Submission</h2>
            <div style="font-size: 12px; margin-top: 8px;">Submission ID: {$submissionId}</div>
        </div>
        
        <div class="content">
            <div class="field">
                <span class="label">Full Name</span>
                <span class="value">{$fullName}</span>
            </div>
            
            <div class="field">
                <span class="label">Email Address</span>
                <span class="value"><a href="mailto:{$email}">{$email}</a></span>
            </div>
HTML;

if (!empty($phone)) {
    $emailHTML .= <<<HTML
            <div class="field">
                <span class="label">Phone Number</span>
                <span class="value">{$phone}</span>
            </div>
HTML;
}

$emailHTML .= <<<HTML
            <div class="field">
                <span class="label">Department</span>
                <span class="value" style="text-transform: capitalize;">{$department}</span>
            </div>
            
            <div class="field">
                <span class="label">Subject</span>
                <span class="value">{$subject}</span>
            </div>
            
            <div class="field">
                <span class="label">Message</span>
                <div class="message-box">{$message}</div>
            </div>
HTML;

if (!empty($attachments)) {
    $emailHTML .= <<<HTML
            <div class="images-section">
                <span class="label">Attached Images</span>
                <span class="image-count">[{count($attachments)} file(s) attached]</span>
            </div>
HTML;
}

$emailHTML .= <<<HTML
        </div>
        
        <div class="footer">
            <p>Received: {date('Y-m-d H:i:s')} | IP: {$_SERVER['REMOTE_ADDR']}</p>
        </div>
    </div>
</body>
</html>
HTML;

// ============================================
// SEND ADMIN EMAIL
// ============================================
$adminResult = sendContactEmail(
    CONTACT_ADMIN_EMAILS,
    "[Contact Form] $subject - $submissionId",
    $emailHTML,
    $attachments
);

if (!$adminResult['success']) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to process submission: ' . $adminResult['message'],
        'errors' => $adminResult['errors']
    ]);
    exit;
}

// ============================================
// SEND CUSTOMER CONFIRMATION EMAIL
// ============================================
$confirmHTML = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Sora, Arial, sans-serif; color: #2c2416; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; background: #f9f7f4; padding: 20px; }
        .header { background: linear-gradient(135deg, #10214b 0%, #1a2f5c 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; }
        .header h2 { margin: 0; font-size: 20px; }
        .content { background: white; padding: 24px; margin-top: 16px; border-radius: 8px; border: 1px solid rgba(16,33,75,0.12); }
        .reference { background: rgba(215,189,136,0.1); padding: 12px; border-left: 3px solid #d7bd88; border-radius: 4px; margin: 16px 0; font-family: monospace; font-weight: 600; color: #10214b; }
        .thanks { margin-top: 20px; color: #7a6d5f; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Message Received</h2>
            <div style="font-size: 13px; margin-top: 8px;">Thank you for contacting PetCenter</div>
        </div>
        
        <div class="content">
            <p>Hi {$fullName},</p>
            
            <p>We have received your message and will respond within 24 business hours during our operating hours (Monday-Friday, 9am-6pm ET).</p>
            
            <p>Your submission reference ID is:</p>
            
            <div class="reference">{$submissionId}</div>
            
            <p>Please save this ID for your records. You can reference it when following up on your inquiry.</p>
            
            <div class="thanks">
                <p><strong>PetCenter Support Team</strong><br>
                support@petcenterco.com<br>
                Monday-Friday, 9am-6pm ET</p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;

sendContactEmail(
    $email,
    "Re: Your PetCenter Contact Form - {$submissionId}",
    $confirmHTML
);

// ============================================
// LOG SUBMISSION
// ============================================
logContactSubmission([
    'fullName' => $fullName,
    'email' => $email,
    'department' => $department,
    'submission_id' => $submissionId,
    'images' => $attachments
]);

// ============================================
// SUCCESS RESPONSE
// ============================================
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Message sent successfully',
    'submission_id' => $submissionId,
    'emails_sent' => count(CONTACT_ADMIN_EMAILS) + 1,
    'images_attached' => count($attachments)
]);
?>
