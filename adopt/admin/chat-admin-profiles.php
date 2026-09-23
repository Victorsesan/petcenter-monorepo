<?php
/**
 * chat-admin-profiles.php
 *
 * Admin profile CRUD (Tier 1 item 16). Zero profiles exist at launch — every
 * one is created manually here, no limit on count. Each profile has fully
 * independent edit and delete actions; touching one never affects another.
 *
 * Photo upload uses the exact same constraints as the existing pet
 * single-image field (CHAT_IMAGE_MAX_BYTES / CHAT_IMAGE_ALLOWED_MIME /
 * CHAT_IMAGE_ALLOWED_EXT, all defined once in chat-config.php and shared
 * with chat-upload-temp.php) rather than a separately invented rule.
 *
 * Actions: create, list, update, delete — dispatched via 'action' param.
 */

header('Content-Type: application/json');
error_reporting(0);
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';

chatRequireAdmin();

$db = chatDb();
$action = $_REQUEST['action'] ?? '';

// ── Shared photo-upload handling (create + update both need this) ───────
function chatHandlePhotoUpload() {
    if (empty($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // no new photo provided — not an error, just nothing to do
    }

    $file = $_FILES['photo'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        chatRespond(['success' => false, 'error' => 'Photo upload failed (error code ' . $file['error'] . ')'], 400);
    }

    if ($file['size'] > CHAT_IMAGE_MAX_BYTES) {
        chatRespond(['success' => false, 'error' => 'Photo too large (' . round($file['size'] / 1024 / 1024, 1) . 'MB). Max: ' . round(CHAT_IMAGE_MAX_BYTES / 1024 / 1024) . 'MB'], 413);
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, CHAT_IMAGE_ALLOWED_MIME, true)) {
        chatRespond(['success' => false, 'error' => "Invalid photo type: $mime. Allowed: JPG, PNG"], 415);
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, CHAT_IMAGE_ALLOWED_EXT, true)) {
        $ext = 'jpg';
    }

    $photoDir = __DIR__ . '/chat-admin-photos/';
    if (!is_dir($photoDir)) {
        @mkdir($photoDir, 0755, true);
    }

    $filename = 'admin_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = $photoDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        chatRespond(['success' => false, 'error' => 'Failed to save photo'], 500);
    }
    @chmod($dest, 0644);

    return 'chat-admin-photos/' . $filename;
}

// ── LIST ──────────────────────────────────────────────────────────────────
if ($action === 'list') {
    $stmt = $db->query("SELECT * FROM chat_admin_profiles ORDER BY created_at ASC");
    chatRespond(['success' => true, 'profiles' => $stmt->fetchAll()]);
}

// ── CREATE ────────────────────────────────────────────────────────────────
if ($action === 'create') {
    $name     = trim($_POST['name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $blueCheck = !empty($_POST['blueCheck']) ? 1 : 0;

    if ($name === '' || mb_strlen($name) > 255) {
        chatRespond(['success' => false, 'error' => 'A valid name is required'], 400);
    }
    if ($position === '' || mb_strlen($position) > 255) {
        chatRespond(['success' => false, 'error' => 'A valid position/title is required'], 400);
    }

    $photoPath = chatHandlePhotoUpload(); // null is fine — falls back to initials avatar client-side

    $stmt = $db->prepare(
        "INSERT INTO chat_admin_profiles (name, position, photo_url, blue_check, active) VALUES (?, ?, ?, ?, 1)"
    );
    $stmt->execute([$name, $position, $photoPath, $blueCheck]);

    chatRespond(['success' => true, 'id' => $db->lastInsertId()]);
}

// ── UPDATE — fully independent, only the targeted profile changes ───────
if ($action === 'update') {
    $id       = (int)($_POST['id'] ?? 0);
    $name     = trim($_POST['name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $blueCheck = !empty($_POST['blueCheck']) ? 1 : 0;
    $active    = isset($_POST['active']) ? (!empty($_POST['active']) ? 1 : 0) : null;

    if (!$id) {
        chatRespond(['success' => false, 'error' => 'id is required'], 400);
    }

    $stmt = $db->prepare("SELECT * FROM chat_admin_profiles WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) {
        chatRespond(['success' => false, 'error' => 'Profile not found'], 404);
    }

    if ($name === '') $name = $existing['name'];
    if ($position === '') $position = $existing['position'];
    if ($active === null) $active = $existing['active'];

    $newPhotoPath = chatHandlePhotoUpload();
    $photoPath = $newPhotoPath ?? $existing['photo_url']; // keep existing photo if none re-uploaded

    if ($newPhotoPath && $existing['photo_url']) {
        // Replacing an existing photo — clean up the old file so chat-admin-photos/
        // doesn't accumulate orphaned images every time someone updates their picture.
        $oldFile = __DIR__ . '/' . $existing['photo_url'];
        if (is_file($oldFile)) {
            @unlink($oldFile);
        }
    }

    $stmt = $db->prepare(
        "UPDATE chat_admin_profiles SET name = ?, position = ?, photo_url = ?, blue_check = ?, active = ? WHERE id = ?"
    );
    $stmt->execute([$name, $position, $photoPath, $blueCheck, $active, $id]);

    chatRespond(['success' => true, 'id' => $id]);
}

// ── DELETE — fully independent, never affects other profiles ────────────
if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        chatRespond(['success' => false, 'error' => 'id is required'], 400);
    }

    $stmt = $db->prepare("SELECT photo_url FROM chat_admin_profiles WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) {
        chatRespond(['success' => false, 'error' => 'Profile not found'], 404);
    }

    // Deleting a profile does NOT delete the threads/messages it was
    // attached to — chat_messages.admin_id and chat_threads.assigned_admin_id
    // are nullable, not foreign-keyed with CASCADE, specifically so removing
    // an admin profile never corrupts existing chat history.
    $db->prepare("DELETE FROM chat_admin_profiles WHERE id = ?")->execute([$id]);

    if ($existing['photo_url']) {
        $photoFile = __DIR__ . '/' . $existing['photo_url'];
        if (is_file($photoFile)) {
            @unlink($photoFile);
        }
    }

    chatRespond(['success' => true, 'id' => $id]);
}

chatRespond(['success' => false, 'error' => "action must be 'list', 'create', 'update', or 'delete'"], 400);
