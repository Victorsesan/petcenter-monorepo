<?php
require_once __DIR__ . '/anti-fraud-detectors.php';
$result = cfRunReputationAnomalyCheck();
error_log('[anti-fraud-reputation-cron] ' . json_encode($result));
