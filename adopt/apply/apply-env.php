<?php
/**
 * Shared configuration for the adoption upload handlers.
 *
 * Runtime secrets and optional overrides belong in adopt/admin/.env. The
 * legacy apply/config.php file is intentionally not required: it is not part
 * of the repository deployment tree and must not be a hidden dependency.
 */
function applyEnvValues(): array
{
    static $values;
    if ($values !== null) {
        return $values;
    }

    $values = [];
    $path = dirname(__DIR__) . '/admin/.env';
    if (!is_readable($path)) {
        return $values;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        if (!preg_match('/^[A-Z][A-Z0-9_]*$/', $key)) {
            continue;
        }
        $value = trim($value);
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last = $value[strlen($value) - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }
        $values[$key] = $value;
    }

    return $values;
}

function applyEnvValue(string $key, string $default = ''): string
{
    $values = applyEnvValues();
    return isset($values[$key]) && $values[$key] !== '' ? $values[$key] : $default;
}

function applyEnvEmails(string $key, array $default): array
{
    $raw = applyEnvValue($key);
    if ($raw === '') {
        return $default;
    }

    $emails = [];
    foreach (preg_split('/[,;\s]+/', $raw) ?: [] as $email) {
        $email = trim($email);
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emails[] = $email;
        }
    }
    return $emails ?: $default;
}

function applyEnvPath(string $key, string $default): string
{
    $value = applyEnvValue($key);
    if ($value === '') {
        return $default;
    }
    return $value[0] === '/' ? $value : __DIR__ . '/' . ltrim($value, '/\\');
}
