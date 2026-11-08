/* <!-- TASK-D1 START --> */
/**
 * D1 — Post-Placement Check-ins
 * -----------------------------
 * Sends a "How's it going?" check-in to BOTH parties 7 and 30 days after
 * placement_complete, asking "Is the pet still with the adopter?" (yes/no).
 * A "no" flags the placement for admin visibility; it does NOT undo the
 * verified-rehome stat (per task doc).
 *
 * SCHEDULING: this module is pure logic with no timer of its own.
 * runDueCheckins(listings, now, deps) must be called periodically by the
 * platform's scheduled job (a daily cron hitting a small PHP/Node endpoint
 * is enough: check-ins are day-granular). It is idempotent, so running it
 * more often, or twice in a day, never double-sends.
 *
 * Delivery per party: an in-account notification (visitor-notifications.js)
 * plus an email through the injected mailer (best-effort, never throws).
 */

const { STATUS } = require('./placement-lifecycle-v1.js');
const { createNotification, NOTIFICATION_TYPES } = require('./visitor-notifications-v1.js');

const DAY_MS = 24 * 60 * 60 * 1000;

// The two check-in milestones, in days after placement_complete.
const CHECKIN_SCHEDULE = Object.freeze([
  { key: '7day', days: 7, notificationType: NOTIFICATION_TYPES.CHECKIN_7_DAY },
  { key: '30day', days: 30, notificationType: NOTIFICATION_TYPES.CHECKIN_30_DAY },
]);

/**
 * Returns the milestone keys that are due and not yet sent for a listing.
 * Only listings that reached placement_complete are eligible.
 */
function getDueMilestones(listing, now = new Date()) {
  if (listing.status !== STATUS.PLACEMENT_COMPLETE || !listing.placement_complete_at) return [];
  const sent = listing.checkins_sent || {};
  const elapsed = now.getTime() - new Date(listing.placement_complete_at).getTime();
  return CHECKIN_SCHEDULE.filter((m) => !sent[m.key] && elapsed >= m.days * DAY_MS);
}

/**
 * Sends all due check-ins for one listing to merchant and adopter, marks
 * them sent. Returns the list of milestone keys sent.
 *
 * listing.merchant_id and listing.likely_adopter_id identify the parties.
 * deps.sendEmail / deps.getEmail(account_id) are injected (see
 * vet-document.js for the mailer-wiring note).
 */
function sendDueCheckins(listing, now = new Date(), deps = {}) {
  const due = getDueMilestones(listing, now);
  listing.checkins_sent = listing.checkins_sent || {};

  due.forEach((m) => {
    [listing.merchant_id, listing.likely_adopter_id].filter(Boolean).forEach((account_id) => {
      createNotification({
        recipient_id: account_id,
        type: m.notificationType,
        listing_id: listing.id,
        title: "How's it going?",
        body: `It has been ${m.days} days since the handoff. Is the pet still with the adopter?`,
        // Rule 5 (both summary docs, no exceptions): every page URL this
        // project builds uses the clean folder path, never a bare filename
        // or an extensionless path missing its trailing slash — the exact
        // examples given are `/pets/dogs/` and `/pets/detail/?pet_id=...`,
        // i.e. the slash comes BEFORE the query string. `/checkin/` is a
        // placeholder route name (no page owns it yet — flagged in the
        // completion file as still needing a real page built against
        // recordCheckinResponse()), but its URL SHAPE follows Rule 5 now
        // so nothing has to be fixed later just to match convention.
        action_url: `/checkin/?listing=${encodeURIComponent(listing.id)}&milestone=${m.key}`,
      });
      // Email is best-effort and must never block the loop.
      try {
        const to = deps.getEmail && deps.getEmail(account_id);
        if (to && deps.sendEmail) {
          deps.sendEmail({
            to,
            subject: "How's it going? A quick check-in",
            html: `<p>It has been ${m.days} days since the handoff. Is the pet still with the adopter? <a href="/checkin/?listing=${encodeURIComponent(listing.id)}&milestone=${m.key}">Answer in one click</a>.</p>`,
          });
        }
      } catch (err) { /* swallowed on purpose; notification already exists */ }
    });
    listing.checkins_sent[m.key] = new Date(now).toISOString();
  });

  return due.map((m) => m.key);
}

/** Scheduler entry point: run over all listings; returns { listing_id: [keys] } for those sent. */
function runDueCheckins(listings, now = new Date(), deps = {}) {
  const result = {};
  listings.forEach((l) => {
    const sent = sendDueCheckins(l, now, deps);
    if (sent.length) result[l.id] = sent;
  });
  return result;
}

/**
 * Records one party's yes/no answer. A "no" sets a flag for admin
 * visibility and notifies the other party's list nothing further; the
 * verified-rehome stat (placement_complete_at / status) is left untouched.
 *
 * Idempotent per (account_id, milestone): a second submission from the same
 * party for a milestone they already answered returns the stored response
 * unchanged rather than appending a duplicate row — the one-click email/
 * notification link can realistically be opened twice (a re-click, a mail
 * client prefetching the link), so this must not double-record.
 *
 * Returns the stored response. Throws on an invalid answer, an unknown
 * party, an unknown milestone, or a milestone that was never actually sent
 * for this listing (this handler is only reachable via that milestone's own
 * check-in link, so a request for one that was never sent is either a
 * forged/guessed URL or a caller bug — reject it rather than recording a
 * response to something the parties were never actually asked).
 */
function recordCheckinResponse(listing, { account_id, milestone, still_with_adopter }) {
  if (typeof still_with_adopter !== 'boolean') throw new Error('placement-checkins: answer must be true or false.');
  if (![listing.merchant_id, listing.likely_adopter_id].includes(account_id)) {
    throw new Error('placement-checkins: account is not a party to this placement.');
  }
  if (!CHECKIN_SCHEDULE.some((m) => m.key === milestone)) throw new Error(`placement-checkins: unknown milestone "${milestone}".`);
  if (!listing.checkins_sent || !listing.checkins_sent[milestone]) {
    throw new Error(`placement-checkins: the "${milestone}" check-in was never sent for this listing.`);
  }

  listing.checkin_responses = listing.checkin_responses || [];
  const existing = listing.checkin_responses.find((r) => r.account_id === account_id && r.milestone === milestone);
  if (existing) return existing; // idempotent: don't record a duplicate for a re-opened link

  const response = { account_id, milestone, still_with_adopter, answered_at: new Date().toISOString() };
  listing.checkin_responses.push(response);

  if (still_with_adopter === false) {
    // Admin-visibility flag only; deliberately does not change status or stats.
    listing.admin_flag_no_longer_with_adopter = {
      flagged_at: response.answered_at,
      reported_by: account_id,
      milestone,
    };
  }
  return response;
}

module.exports = { CHECKIN_SCHEDULE, getDueMilestones, sendDueCheckins, runDueCheckins, recordCheckinResponse };
/* <!-- TASK-D1 END --> */
