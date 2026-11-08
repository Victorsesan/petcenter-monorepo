<?php
/**
 * merchant-response-stats.php
 * TASK-B4 — Merchant response-rate computation (PHP port, for Task B3's daily job)
 *
 * WHY THIS FILE EXISTS: B3's scheduled job (badge-calculation-cron.php) runs as PHP on
 * Hostinger, so it cannot execute B4's JavaScript reference (merchant-response-stats-v1.js).
 * This is a line-for-line PHP port of that file. Both are verified against the SAME shared
 * fixtures (merchant-response-stats.fixtures.json) so they cannot drift apart — run
 * `php merchant-response-stats.test.php` after any change to either one.
 *
 * PURE FUNCTION: no file/DB/network access, no globals. The caller (B3's job) fetches a
 * merchant's messages and passes them in. Messages must never be sent to the browser.
 *
 * OUTPUT UNITS MATCH badge-logic.php (B3):
 *   response_rate is a FRACTION 0.0-1.0 — the same unit as B3_TRUSTED_TIER_MIN_RESPONSE_RATE
 *   (0.80) — and is null when there is too little history to judge, which B3's
 *   calculateActivityTier() already treats as "not enough data" (not 0%). It is NOT a
 *   percentage: storing 85 instead of 0.85 would make every merchant pass the 0.80 check.
 *   window_hours echoes the on-time window; it must equal B3_RESPONSE_RATE_WINDOW_HOURS (48).
 *
 * MESSAGE SHAPE — CORRECTED (see "Fifth check" in Task-B4-Completion.md): an earlier version
 * cited ADMIN_DASHBOARD_PROJECT_SUMMARY.md's `chat_messages` table here. That table is the
 * general site's visitor<->admin SUPPORT chat — a real system, but the WRONG one. The
 * merchant<->visitor ENQUIRY messaging this stat must measure is owned by Task D1
 * ("Messaging a merchant (Task D1's enquiry flow)"), which has not shipped a file yet.
 * The shape below is this task's OWN GUESS, not sourced from anything that exists:
 *   thread_id, from_type ('user' = visitor/inbound, 'admin' = merchant reply/outbound;
 *   'bot'/'system' listed only in case D1's design includes something automated),
 *   created_at, deleted_at. Reconcile at D1's integration checkpoint — not C5's; C5 only
 * adds token-based access control to whatever endpoints D1 builds, it does not define the
 * message data shape. (B4's own task text names C5 as the reconciliation trigger here; this
 * is flagged as a likely documentation mix-up in the completion file, not resolved silently.)
 *
 * TIMESTAMPS: a created_at with no timezone is treated as UTC. The messages table must store
 * UTC (or the caller must convert) — otherwise reply times shift by the server's offset.
 */

// TASK-B4 START: response-stats constants
/**
 * All thresholds are adjustable starting points, not magic numbers scattered through logic.
 * ON_TIME_HOURS must stay equal to B3_RESPONSE_RATE_WINDOW_HOURS in badge-logic.php.
 */
function b4ResponseStatsConstants() {
    return array(
        'INBOUND_TYPES'  => array('user'),    // visitor -> merchant
        'OUTBOUND_TYPES' => array('admin'),   // merchant (human) -> visitor; 'bot'/'system' deliberately excluded
        'WINDOW_DAYS'    => 90,               // rolling window: only enquiries started in the last N days
        'MAX_SAMPLES'    => 50,               // ...and at most the most recent N enquiries
        'MIN_SAMPLE'     => 3,                // fewer than this -> response_rate and tier are null
        'ON_TIME_HOURS'  => 48,               // "replied in time" cutoff; also the penalty for an overdue unanswered enquiry
        'FAST_HOURS'     => 6,                // tier boundary: "within a few hours"
        'DAY_HOURS'      => 24,               // tier boundary: "within a day"
    );
}

/** Plain-language labels for the tier keys (B4 shows these publicly, never numbers). */
function b4ResponseTierLabels() {
    return array(
        'few_hours'  => 'Usually replies within a few hours',
        'within_day' => 'Usually replies within a day',
        'slower'     => 'Typically slower to reply',
    );
}
// TASK-B4 END: response-stats constants


// TASK-B4 START: timestamp + message normalisation
/**
 * b4ToMs()
 * Converts a created_at value to milliseconds since the epoch, or null if unusable.
 * Accepts MySQL "YYYY-MM-DD HH:MM:SS" or ISO 8601; zone-less strings are read as UTC.
 * Numbers are taken as milliseconds already (matches the JavaScript reference).
 *
 * @param mixed $v
 * @return float|null
 */
function b4ToMs($v) {
    if ($v === null || $v === '') {
        return null;
    }
    if (is_int($v) || is_float($v)) {
        return (float) $v;
    }
    $str = str_replace(' ', 'T', (string) $v);
    if (!preg_match('/(Z|[+-]\d{2}:?\d{2})$/', $str)) {
        $str .= 'Z';
    }
    try {
        $d = new DateTimeImmutable($str);
    } catch (Exception $e) {
        return null;
    }
    return ((float) $d->format('U')) * 1000 + (float) $d->format('v');
}

/**
 * b4NormaliseMessages()
 * Keeps only usable rows: valid timestamp, not soft-deleted, recognised direction.
 * `idx` preserves input order so same-timestamp messages sort deterministically.
 *
 * @param array $messages
 * @param array $C constants
 * @return array list of array('thread'=>, 'dir'=>'in'|'out', 't'=>float, 'idx'=>int)
 */
function b4NormaliseMessages($messages, $C) {
    $out = array();
    foreach ((array) $messages as $m) {
        if (!is_array($m) || !empty($m['deleted_at'])) {
            continue;
        }
        $t = b4ToMs(isset($m['created_at']) ? $m['created_at'] : null);
        if ($t === null) {
            continue;
        }
        $type = isset($m['from_type']) ? $m['from_type'] : null;
        $dir = in_array($type, $C['INBOUND_TYPES'], true) ? 'in'
             : (in_array($type, $C['OUTBOUND_TYPES'], true) ? 'out' : null);
        if ($dir === null) {
            continue;
        }
        $out[] = array(
            'thread' => isset($m['thread_id']) ? (string) $m['thread_id'] : '',
            'dir'    => $dir,
            't'      => $t,
            'idx'    => count($out),
        );
    }
    return $out;
}
// TASK-B4 END: timestamp + message normalisation


// TASK-B4 START: enquiry extraction + stats
/**
 * b4ExtractEnquiries()
 * One "enquiry" = the first inbound message of a burst that is still waiting for a reply.
 * Several visitor messages in a row before the merchant answers are ONE enquiry, so a chatty
 * visitor cannot inflate the sample.
 *
 * @param array $rows output of b4NormaliseMessages()
 * @return array list of array('start'=>float, 'replyHours'=>float|null) — null = still unanswered
 */
function b4ExtractEnquiries($rows) {
    $byThread = array();
    foreach ($rows as $r) {
        $byThread[$r['thread']][] = $r;
    }
    $enquiries = array();
    foreach ($byThread as $list) {
        usort($list, function ($a, $b) {
            if ($a['t'] != $b['t']) {
                return $a['t'] < $b['t'] ? -1 : 1;
            }
            return $a['idx'] - $b['idx'];   // deterministic tie-break (same as the JS reference)
        });
        $waitingSince = null;
        foreach ($list as $r) {
            if ($r['dir'] === 'in') {
                if ($waitingSince === null) {
                    $waitingSince = $r['t'];
                }
            } elseif ($waitingSince !== null) {
                $enquiries[] = array('start' => $waitingSince, 'replyHours' => ($r['t'] - $waitingSince) / 3600000);
                $waitingSince = null;
            }
        }
        if ($waitingSince !== null) {
            $enquiries[] = array('start' => $waitingSince, 'replyHours' => null);   // still unanswered
        }
    }
    return $enquiries;
}

/**
 * computeMerchantResponseStats()
 * The one function B3's job calls. `$nowMs` is injectable so the result is deterministic
 * and testable; pass null to use the current time.
 *
 * @param array           $messages  one merchant's messages (see MESSAGE SHAPE above)
 * @param float|int|string|null $now ms since epoch, an ISO/MySQL string, or null for "now"
 * @param array|null      $overrides optional constant overrides (tests only)
 * @return array{sample_size:int, avg_reply_hours:float|null, response_rate:float|null, window_hours:int, tier:string|null}
 */
function computeMerchantResponseStats($messages, $now = null, $overrides = null) {
    $C = b4ResponseStatsConstants();
    if (is_array($overrides)) {
        foreach ($overrides as $k => $v) {
            $C[$k] = $v;
        }
    }
    $nowMs = ($now === null) ? microtime(true) * 1000 : b4ToMs($now);
    $windowStart = $nowMs - $C['WINDOW_DAYS'] * 86400000;

    $enquiries = array();
    foreach (b4ExtractEnquiries(b4NormaliseMessages($messages, $C)) as $e) {
        if ($e['start'] < $windowStart || $e['start'] > $nowMs) {
            continue;
        }
        // An unanswered enquiry only counts once it is overdue; a fresh one hasn't had a
        // fair chance yet, so it is not held against the merchant.
        if ($e['replyHours'] === null && ($nowMs - $e['start']) / 3600000 < $C['ON_TIME_HOURS']) {
            continue;
        }
        $enquiries[] = $e;
    }
    usort($enquiries, function ($a, $b) {
        if ($a['start'] == $b['start']) {
            return 0;
        }
        return $a['start'] < $b['start'] ? 1 : -1;   // most recent first
    });
    $enquiries = array_slice($enquiries, 0, $C['MAX_SAMPLES']);

    $count = count($enquiries);
    $answeredHours = array();
    $onTime = 0;
    foreach ($enquiries as $e) {
        if ($e['replyHours'] !== null) {
            $answeredHours[] = $e['replyHours'];
            if ($e['replyHours'] <= $C['ON_TIME_HOURS']) {
                $onTime++;
            }
        }
    }

    $enoughData = $count >= $C['MIN_SAMPLE'];
    $result = array(
        'sample_size'     => $count,
        'avg_reply_hours' => count($answeredHours) ? round(array_sum($answeredHours) / count($answeredHours) * 10) / 10 : null,
        // Fraction 0.0-1.0 (B3's unit). null below MIN_SAMPLE so one lucky reply can't read as
        // "100%" and push a merchant over B3's Trusted threshold.
        'response_rate'   => $enoughData ? round($onTime / $count * 100) / 100 : null,
        'window_hours'    => $C['ON_TIME_HOURS'],
        'tier'            => null,
    );

    if ($enoughData) {
        // Tier basis counts an overdue-unanswered enquiry as ON_TIME_HOURS so that ignoring
        // people can't hide behind a few fast replies.
        $sum = 0;
        foreach ($enquiries as $e) {
            $sum += ($e['replyHours'] === null) ? $C['ON_TIME_HOURS'] : min($e['replyHours'], $C['ON_TIME_HOURS']);
        }
        $basis = $sum / $count;
        $result['tier'] = $basis <= $C['FAST_HOURS'] ? 'few_hours' : ($basis <= $C['DAY_HOURS'] ? 'within_day' : 'slower');
    }
    return $result;
}
// TASK-B4 END: enquiry extraction + stats
