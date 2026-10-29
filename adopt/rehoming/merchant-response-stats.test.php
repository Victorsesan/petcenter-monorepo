<?php
/**
 * merchant-response-stats.test.php
 * TASK-B4 — verifies the PHP port against the same fixtures the JavaScript reference is
 * tested against, so the two implementations cannot drift apart.
 *
 * Run from the command line:   php merchant-response-stats.test.php
 * Exit code 0 = every case matches, 1 = at least one mismatch. CLI only (refuses to run over HTTP).
 *
 * NOTE: the author's sandbox had no PHP available, so this runner and the port were NOT
 * executed there. Run this once on any machine with PHP (hPanel's terminal, or locally)
 * before deploying. The fixtures themselves are verified by the JS tests (17/17 passing).
 */
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit("CLI only.\n");
}

require_once __DIR__ . '/merchant-response-stats.php';

// TASK-B4 START: fixture runner
$fixtures = json_decode(file_get_contents(__DIR__ . '/merchant-response-stats.fixtures.json'), true);
if (!is_array($fixtures) || empty($fixtures['cases'])) {
    fwrite(STDERR, "Could not read merchant-response-stats.fixtures.json\n");
    exit(1);
}

/** Equal if both null, or both numbers within a tiny tolerance, or identical otherwise. */
function b4Same($a, $b) {
    if ($a === null || $b === null) {
        return $a === $b;
    }
    if (is_numeric($a) && is_numeric($b)) {
        return abs($a - $b) < 1e-9;
    }
    return $a === $b;
}

$pass = 0;
$fail = 0;
foreach ($fixtures['cases'] as $case) {
    $got = computeMerchantResponseStats($case['messages'], $fixtures['now']);
    $ok = true;
    foreach ($case['expected'] as $key => $want) {
        if (!array_key_exists($key, $got) || !b4Same($got[$key], $want)) {
            $ok = false;
        }
    }
    if ($ok) {
        $pass++;
        echo 'PASS ' . $case['name'] . "\n";
    } else {
        $fail++;
        echo 'FAIL ' . $case['name'] . "\n   got  " . json_encode($got) . "\n   want " . json_encode($case['expected']) . "\n";
    }
}
echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
// TASK-B4 END: fixture runner
