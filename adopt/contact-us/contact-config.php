<?php
/**
 * Contact Form Configuration
 * For: docs.petcenterco.com/contact-us/
 * 
 * Uses Hostinger SMTP via PHPMailer for email delivery
 * Requires: composer require phpmailer/phpmailer
 */

error_reporting(E_ALL);

// Load PHPMailer
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ============================================
// HOSTINGER SMTP CONFIGURATION
// ============================================
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 465);
define('SMTP_USERNAME', 'noreply@petcenterco.com');
define('SMTP_PASSWORD', 'Godisgood8z$');
define('SMTP_FROM_EMAIL', 'noreply@petcenterco.com');
define('SMTP_FROM_NAME', 'PetCenter Support');
define('SMTP_ENCRYPTION', 'ssl');
define('EMAIL_ENABLED', true);

// ============================================
// CONTACT FORM ADMIN RECIPIENTS
// ============================================
define('CONTACT_ADMIN_EMAILS', [
    'support@petcenterco.com',
    'adopt@petcenterco.com',
    'zeusvictor88@gmail.com'
]);

// ============================================
// UPLOAD CONFIGURATION (docs.petcenterco.com/contact-us/)
// ============================================
define('UPLOAD_TEMP_DIR', __DIR__ . '/uploads/contact-forms/temp/');
define('UPLOAD_FINAL_DIR', __DIR__ . '/uploads/contact-forms/');
define('UPLOAD_MAX_FILES', 10);
define('UPLOAD_MAX_SIZE_MB', 5);
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png']);
define('UPLOAD_ALLOWED_EXT', ['jpg', 'jpeg', 'png']);

// ============================================
// LOGGING
// ============================================
define('LOG_DIR', __DIR__ . '/logs/');
define('LOG_FILE', LOG_DIR . 'contact-submissions.log');

// Ensure directories exist
if (!is_dir(UPLOAD_TEMP_DIR)) {
    @mkdir(UPLOAD_TEMP_DIR, 0755, true);
}
if (!is_dir(UPLOAD_FINAL_DIR)) {
    @mkdir(UPLOAD_FINAL_DIR, 0755, true);
}
if (!is_dir(LOG_DIR)) {
    @mkdir(LOG_DIR, 0755, true);
}

// ============================================
// HELPER: Send Email via PHPMailer
// ============================================
function sendContactEmail($recipients, $subject, $htmlBody, $attachments = []) {
    $result = [
        'success' => false,
        'message' => '',
        'errors' => []
    ];

    try {
        if (!class_exists('\\PHPMailer\\PHPMailer\\PHPMailer')) {
            $result['message'] = 'PHPMailer not installed';
            $result['errors'][] = 'Missing PHPMailer dependency';
            return $result;
        }

        $mail = new PHPMailer(true);
        
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = (SMTP_ENCRYPTION === 'ssl') ? 
            PHPMailer::ENCRYPTION_SMTPS : 
            PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addReplyTo('support@petcenterco.com', 'PetCenter Support');
        
        // Message
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);

        // Recipients
        if (is_string($recipients)) {
            $recipients = [$recipients];
        }
        
        foreach ($recipients as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $mail->addAddress($email);
            } else {
                $result['errors'][] = "Invalid email: $email";
            }
        }

        // Attachments
        if (!empty($attachments) && is_array($attachments)) {
            foreach ($attachments as $filePath => $fileName) {
                if (file_exists($filePath)) {
                    $mail->addAttachment($filePath, $fileName ?: basename($filePath));
                } else {
                    $result['errors'][] = "File not found: $filePath";
                }
            }
        }

        // Send
        $mail->send();
        $result['success'] = true;
        $result['message'] = 'Email sent successfully';
        
    } catch (Exception $e) {
        $result['success'] = false;
        $result['message'] = 'Email error: ' . $e->getMessage();
        $result['errors'][] = $e->getMessage();
        error_log('Contact form email error: ' . $e->getMessage());
    } catch (Throwable $t) {
        $result['success'] = false;
        $result['message'] = 'System error: ' . $t->getMessage();
        $result['errors'][] = $t->getMessage();
        error_log('Contact form system error: ' . $t->getMessage());
    }

    return $result;
}

// ============================================
// HELPER: Log Contact Submission
// ============================================
function logContactSubmission($data) {
    if (!is_writable(LOG_DIR)) {
        error_log('Contact form log dir not writable: ' . LOG_DIR);
        return false;
    }

    $timestamp = date('Y-m-d H:i:s');
    $logEntry = [
        'timestamp' => $timestamp,
        'name' => $data['fullName'] ?? 'N/A',
        'email' => $data['email'] ?? 'N/A',
        'department' => $data['department'] ?? 'N/A',
        'images_count' => count($data['images'] ?? []),
        'submission_id' => $data['submission_id'] ?? 'N/A',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'N/A'
    ];

    $line = json_encode($logEntry) . "\n";
    return (bool)@file_put_contents(LOG_FILE, $line, FILE_APPEND);
}

// ============================================
// HELPER: Generate Submission ID
// ============================================
function generateSubmissionId() {
    return 'CF-' . strtoupper(bin2hex(random_bytes(4))) . '-' . date('Ymd');
}

// ============================================
// HELPER: Generate Hex Token
// ============================================
function generateHexToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

?>
