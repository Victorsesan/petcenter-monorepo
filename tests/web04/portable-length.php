<?php
declare(strict_types=1);

$repoRoot = realpath(__DIR__ . '/../..');
if ($repoRoot === false) throw new RuntimeException('Repository root could not be resolved');
if (function_exists('mb_strlen') || function_exists('mb_substr')) {
    throw new RuntimeException('Run this test with mb_strlen and mb_substr disabled');
}
require_once $repoRoot . '/adopt/rehoming/merchant-platform-lib.php';
if (rh_text_length('short text') !== 10) throw new RuntimeException('ASCII length fallback failed');
if (rh_text_length("é猫🐾") !== 3) throw new RuntimeException('UTF-8 character-length fallback failed');
if (rh_text_slice('portable', 4) !== 'port') throw new RuntimeException('Text slicing fallback failed');
if (rh_text_slice("é猫🐾x", 3) !== "é猫🐾") throw new RuntimeException('UTF-8 character-slicing fallback failed');
echo "PASS: portable text helper preserves UTF-8 characters without mbstring\n";
