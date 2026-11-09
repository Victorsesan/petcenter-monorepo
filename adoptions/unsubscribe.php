<?php
/** TASK-MANUS-06 START — F3 one-click digest opt-out handler. */
const UNSUBSCRIBE_TOKEN_SALT = 'CHANGE_ME_BEFORE_PRODUCTION';
$file = __DIR__ . '/scripts/visitor-saved-searches-stub.json';
$email = filter_var($_GET['email'] ?? '', FILTER_VALIDATE_EMAIL);
$token = (string)($_GET['token'] ?? '');
$valid = $email && hash_equals(hash_hmac('sha256', strtolower(trim($email)), UNSUBSCRIBE_TOKEN_SALT), $token);
if ($valid) {
    $rows = json_decode((string)@file_get_contents($file), true); $rows = is_array($rows) ? $rows : [];
    foreach ($rows as &$row) if (strcasecmp((string)($row['email'] ?? ''), $email) === 0) $row['digest_opt_out'] = true;
    @file_put_contents($file, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><title>Digest preference — Pet Center</title></head><body style="margin:0;border-top:4px solid #ED7624;padding:32px;font-family:Sora,Arial,sans-serif;color:#2c2416"><h1 style="font-family:Cormorant Garamond,Bitter,Georgia,serif;color:#10214b"><?= $valid ? 'You are unsubscribed' : 'This link is not valid' ?></h1><p><?= $valid ? 'You will no longer receive the weekly Pet Center digest.' : 'Please use the complete unsubscribe link from your email.' ?></p></body></html>
