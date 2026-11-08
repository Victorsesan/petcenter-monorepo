/* ════════════════════════════════════════════════════════════════════════════
   TASK B4 — Merchant response-rate computation (reference implementation)
   ────────────────────────────────────────────────────────────────────────────
   WHAT THIS IS: a pure function (no DOM, no network, no storage) that turns a
   merchant's message history into the stats the platform needs:
     { sample_size, avg_reply_hours, response_rate, window_hours, tier }
   - B3 (Trusted badge)   -> `response_rate`, a fraction 0.0-1.0 (NOT a percentage),
                             same unit as B3_TRUSTED_TIER_MIN_RESPONSE_RATE = 0.80 in
                             badge-logic.php. null when there is too little history to
                             judge, which B3's calculateActivityTier() already treats as
                             "not enough data" rather than 0%.
   - B4 (public profile)  -> `tier`, shown as a plain-language label, never numbers
   - E1a (merchant's own stats panel) -> may show the precise numbers (response_rate x 100)
   `window_hours` echoes ON_TIME_HOURS; it must equal B3_RESPONSE_RATE_WINDOW_HOURS (48).

   WHAT THIS IS NOT: the persistence layer, and not the production runtime.
   Messages are private server-side data and must never be shipped to the browser.
   B3's daily job (badge-calculation-cron.php, PHP on Hostinger) runs the
   production version: merchant-response-stats.php is a line-for-line PHP port of
   this file. This JavaScript file is the tested reference; both are checked against
   the same fixtures (merchant-response-stats.fixtures.json).
   See "HANDOFF TO TASK B3's WORKER" in Task-B4-Completion.md.

   MESSAGE SHAPE — CORRECTED (see "Fifth check" in Task-B4-Completion.md): an
   earlier version of this file cited ADMIN_DASHBOARD_PROJECT_SUMMARY.md's
   `chat_messages` table here, as if it were this shape's source. That table is
   the general site's visitor<->admin SUPPORT chat (account/technical issues) —
   a real system, but the WRONG one. The merchant<->visitor ENQUIRY messaging
   this stat actually needs to measure is owned by Task D1 ("Messaging a
   merchant (Task D1's enquiry flow)" — VISIBILITY & ACCESS MODEL; D1's own
   section: "any visitor who messages about a listing..."). D1 has not shipped
   a file yet, so there is no real schema to build against — the shape below
   is this task's OWN GUESS, not sourced from anything that exists:
     { thread_id, from_type, created_at, deleted_at }
   from_type: 'user' (the visitor / inbound), 'admin' (the merchant replying /
   outbound) — named to match this platform's existing chat convention only
   because no better name exists yet, NOT because it reuses that system.
   'system' and 'bot' are listed as values this function already knows to
   exclude in case D1's design includes anything automated, but D1 may not use
   these exact names at all. Reconcile at D1's integration checkpoint — not
   C5's; C5 only adds token-based access control to whatever endpoints D1
   builds, it does not define the message/thread data shape. (B4's own task
   text names "Task C5's endpoint hardening" as the reconciliation trigger;
   this is flagged as a likely documentation mix-up in the completion file,
   not silently resolved — reconcile at whichever of D1/C5 the CEO confirms.)
   ════════════════════════════════════════════════════════════════════════════ */
(function (root, factory) {
  if (typeof module === 'object' && module.exports) module.exports = factory();
  else root.MerchantResponseStats = factory();
}(typeof self !== 'undefined' ? self : this, function () {

  // All thresholds are adjustable starting points, NOT magic numbers (per B3's
  // note that thresholds must be easy to find and change).
  var CONSTANTS = {
    INBOUND_TYPES:  ['user'],    // visitor -> merchant
    OUTBOUND_TYPES: ['admin'],   // merchant (human) -> visitor. 'bot'/'system' deliberately excluded
    WINDOW_DAYS: 90,             // rolling window: only enquiries started in the last N days
    MAX_SAMPLES: 50,             // ...and at most the most recent N enquiries
    MIN_SAMPLE: 3,               // fewer enquiries than this -> no label (avoid judging a brand-new merchant on 1 message)
    ON_TIME_HOURS: 48,           // "replied in time" cutoff; also the penalty value for an overdue unanswered enquiry
    FAST_HOURS: 6,               // tier boundary: "within a few hours"
    DAY_HOURS: 24                // tier boundary: "within a day"
  };

  var TIER_LABELS = {
    few_hours: 'Usually replies within a few hours',
    within_day: 'Usually replies within a day',
    slower: 'Typically slower to reply'
  };

  function toMs(v) {
    if (v === null || v === undefined || v === '') return NaN;
    if (typeof v === 'number') return v;
    // MySQL "YYYY-MM-DD HH:MM:SS" or ISO. A timestamp with no zone is treated as UTC (never as
    // the machine's local time, which would make results depend on where this runs).
    // The messages table must therefore store UTC, or the cron must convert before calling.
    var str = String(v).replace(' ', 'T');
    if (!/(Z|[+-]\d{2}:?\d{2})$/.test(str)) str += 'Z';
    return new Date(str).getTime();
  }

  // Keep only usable rows: valid timestamp, not soft-deleted, recognised direction.
  function normalise(messages, C) {
    var out = [];
    (messages || []).forEach(function (m) {
      if (!m || m.deleted_at) return;
      var t = toMs(m.created_at);
      if (isNaN(t)) return;
      var dir = C.INBOUND_TYPES.indexOf(m.from_type) !== -1 ? 'in'
              : C.OUTBOUND_TYPES.indexOf(m.from_type) !== -1 ? 'out' : null;
      if (dir) out.push({ thread: m.thread_id, dir: dir, t: t, idx: out.length });
    });
    return out;
  }

  // One "enquiry" = the first inbound message of a burst that is still waiting
  // for a reply. Several visitor messages in a row before the merchant answers
  // are ONE enquiry (otherwise a chatty visitor would inflate the sample).
  function extractEnquiries(rows) {
    var byThread = {};
    rows.forEach(function (r) { (byThread[r.thread] = byThread[r.thread] || []).push(r); });
    var enquiries = [];
    Object.keys(byThread).forEach(function (id) {
      var list = byThread[id].sort(function (a, b) { return a.t - b.t || a.idx - b.idx; });   // idx = deterministic tie-break
      var waitingSince = null;
      list.forEach(function (r) {
        if (r.dir === 'in') { if (waitingSince === null) waitingSince = r.t; }
        else if (waitingSince !== null) {
          enquiries.push({ start: waitingSince, replyHours: (r.t - waitingSince) / 3600000 });
          waitingSince = null;
        }
      });
      if (waitingSince !== null) enquiries.push({ start: waitingSince, replyHours: null });  // still unanswered
    });
    return enquiries;
  }

  // `now` is injectable so the function stays deterministic and testable.
  function computeMerchantResponseStats(messages, now, overrides) {
    var C = {}; Object.keys(CONSTANTS).forEach(function (k) { C[k] = CONSTANTS[k]; });
    if (overrides) Object.keys(overrides).forEach(function (k) { C[k] = overrides[k]; });
    var nowMs = (now === undefined || now === null) ? Date.now() : toMs(now);
    var windowStart = nowMs - C.WINDOW_DAYS * 86400000;

    var enquiries = extractEnquiries(normalise(messages, C))
      .filter(function (e) { return e.start >= windowStart && e.start <= nowMs; })
      .filter(function (e) {
        // An unanswered enquiry only counts once it is overdue; a fresh one
        // (e.g. sent 2 hours ago) hasn't had a fair chance yet, so it is not held against the merchant.
        return e.replyHours !== null || (nowMs - e.start) / 3600000 >= C.ON_TIME_HOURS;
      })
      .sort(function (a, b) { return b.start - a.start; })
      .slice(0, C.MAX_SAMPLES);

    var answered = enquiries.filter(function (e) { return e.replyHours !== null; });
    var onTime = answered.filter(function (e) { return e.replyHours <= C.ON_TIME_HOURS; }).length;
    var sum = function (arr) { return arr.reduce(function (a, b) { return a + b; }, 0); };

    var enoughData = enquiries.length >= C.MIN_SAMPLE;
    var result = {
      sample_size: enquiries.length,
      avg_reply_hours: answered.length ? round1(sum(answered.map(function (e) { return e.replyHours; })) / answered.length) : null,
      // Fraction 0.0-1.0 (B3's unit). null below MIN_SAMPLE so one lucky reply can't
      // read as "100%" and push a merchant over B3's Trusted threshold.
      response_rate: enoughData ? Math.round(onTime / enquiries.length * 100) / 100 : null,
      window_hours: C.ON_TIME_HOURS,
      tier: null
    };

    if (enoughData) {
      // Tier basis counts an overdue-unanswered enquiry as ON_TIME_HOURS so that
      // ignoring people can't hide behind a few fast replies.
      var basis = sum(enquiries.map(function (e) {
        return e.replyHours === null ? C.ON_TIME_HOURS : Math.min(e.replyHours, C.ON_TIME_HOURS);
      })) / enquiries.length;
      result.tier = basis <= C.FAST_HOURS ? 'few_hours' : basis <= C.DAY_HOURS ? 'within_day' : 'slower';
    }
    return result;
  }

  function round1(n) { return Math.round(n * 10) / 10; }

  // Label for a stored tier key; null when there isn't enough data to say anything.
  function tierLabel(tier) { return TIER_LABELS[tier] || null; }

  return { CONSTANTS: CONSTANTS, TIER_LABELS: TIER_LABELS, computeMerchantResponseStats: computeMerchantResponseStats, tierLabel: tierLabel };
}));
