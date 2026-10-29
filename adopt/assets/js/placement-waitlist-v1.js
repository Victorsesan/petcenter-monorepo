/* <!-- TASK-D1 START --> */
/**
 * D1 — Interest Waitlist
 * ----------------------
 * Visitors who message about a listing already in pending_placement see
 * "Join waitlist" instead of the normal enquiry flow. The list is PRIVATE:
 * visible only to the visitor themselves and the listing's merchant, and
 * never exposed as a public count (no pressure-tactic gamification).
 * On placement_fell_through the merchant gets a one-click
 * "Notify next waitlisted visitor".
 *
 * ACCESS GATE: joining requires a logged-in account. A logged-out visitor
 * clicking "Join waitlist" gets Task A4's showAuthPrompt(contextMessage)
 * instead of proceeding. This file does NOT build its own prompt; it
 * calls the shared function (injected as deps.showAuthPrompt so it is
 * testable now and bound to A4's real function at integration).
 */

const { STATUS } = require('./placement-lifecycle-v1.js');
const { createNotification, NOTIFICATION_TYPES } = require('./visitor-notifications-v1.js');

/** Which control a visitor sees on a listing, given its status. */
function getEnquiryControl(listing) {
  return listing.status === STATUS.PENDING_PLACEMENT ? 'join_waitlist' : 'message_merchant';
}

/**
 * Click handler for "Message this merchant" (the enquiry entry point).
 * Logged-out visitors get Task A4's showAuthPrompt instead of the compose UI,
 * so the compose UI (Task C5) can safely assume a logged-in visitor.
 * Returns { proceed: true } when the compose UI may open.
 */
function requestMessageMerchant(listing, viewer, deps = {}) {
  if (!viewer || !viewer.id) {
    if (deps.showAuthPrompt) deps.showAuthPrompt('Sign up to message this merchant');
    return { proceed: false, gated: true };
  }
  // A pending listing routes to the waitlist instead of the normal enquiry flow.
  if (getEnquiryControl(listing) === 'join_waitlist') return { proceed: false, gated: false, useWaitlist: true };
  return { proceed: true, gated: false };
}

/**
 * Click handler for "Join waitlist". If logged out, calls showAuthPrompt
 * and returns { joined: false, gated: true }. Otherwise joins.
 */
function requestJoinWaitlist(listing, viewer, deps = {}) {
  if (!viewer || !viewer.id) {
    if (deps.showAuthPrompt) deps.showAuthPrompt('Sign up to join the waitlist');
    return { joined: false, gated: true };
  }
  return joinWaitlist(listing, viewer.id);
}

/** Adds a visitor to the waitlist. Idempotent; only allowed while pending_placement. */
function joinWaitlist(listing, visitor_id) {
  if (listing.status !== STATUS.PENDING_PLACEMENT) {
    return { joined: false, gated: false, reason: 'not_pending' };
  }
  if (visitor_id === listing.likely_adopter_id) {
    return { joined: false, gated: false, reason: 'already_likely_adopter' };
  }
  listing.waitlist = listing.waitlist || [];
  if (listing.waitlist.some((w) => w.visitor_id === visitor_id)) {
    return { joined: true, gated: false, already: true };
  }
  listing.waitlist.push({ visitor_id, joined_at: new Date().toISOString() });
  return { joined: true, gated: false };
}

/** A visitor may leave their own waitlist spot. */
function leaveWaitlist(listing, visitor_id) {
  const before = (listing.waitlist || []).length;
  listing.waitlist = (listing.waitlist || []).filter((w) => w.visitor_id !== visitor_id);
  return listing.waitlist.length < before;
}

/**
 * PRIVACY: what a given viewer is allowed to see of the waitlist.
 *  - the merchant: the full ordered list
 *  - a waitlisted visitor: only their own entry (no position, no count)
 *  - anyone else: nothing
 * Deliberately no count or position is ever returned to a visitor.
 */
function getWaitlistView(listing, viewer) {
  const list = listing.waitlist || [];
  if (!viewer) return [];
  if (viewer.id === listing.merchant_id) return list.slice();
  return list.filter((w) => w.visitor_id === viewer.id);
}

/**
 * Merchant one-click action, available only after placement_fell_through.
 * Notifies the next waitlisted visitor, removes them from the list so the
 * following click moves on to the next person, and returns who was
 * notified (or null if the list is empty).
 */
function notifyNextWaitlisted(listing, merchant_id, deps = {}) {
  if (merchant_id !== listing.merchant_id) throw new Error('placement-waitlist: only the listing merchant can notify the waitlist.');
  if (listing.status !== STATUS.PLACEMENT_FELL_THROUGH) {
    throw new Error('placement-waitlist: notify is only available after a placement falls through.');
  }
  const next = (listing.waitlist || []).shift();
  if (!next) return null;

  // Rule 5 (both summary docs, no exceptions): every page URL uses the clean
  // folder path with the trailing slash BEFORE the query string, matching
  // the site's own documented examples (`/pets/detail/?pet_id=...`). The
  // exact public listing-detail page isn't owned by this task — A1 or
  // whichever task builds the Rehoming Platform's public listing page
  // decides its real route — so `/listings/detail/` is a placeholder here,
  // deliberately styled to match that existing `/pets/detail/` pattern
  // rather than inventing an unrelated shape, and flagged in the completion
  // file as needing confirmation once that page exists.
  const listingUrl = `/listings/detail/?id=${encodeURIComponent(listing.id)}`;

  createNotification({
    recipient_id: next.visitor_id,
    type: NOTIFICATION_TYPES.WAITLIST_SPOT_OPEN,
    listing_id: listing.id,
    title: 'A spot opened up',
    body: 'The earlier placement did not go ahead. You can now message the merchant about this pet.',
    action_url: listingUrl,
  });
  try {
    const to = deps.getEmail && deps.getEmail(next.visitor_id);
    if (to && deps.sendEmail) {
      deps.sendEmail({ to, subject: 'A spot opened up', html: `<p>The earlier placement did not go ahead. <a href="${listingUrl}">View the listing</a>.</p>` });
    }
  } catch (err) { /* best-effort */ }
  return next.visitor_id;
}

module.exports = { getEnquiryControl, requestMessageMerchant, requestJoinWaitlist, joinWaitlist, leaveWaitlist, getWaitlistView, notifyNextWaitlisted };
/* <!-- TASK-D1 END --> */
