/* <!-- TASK-D1 START --> */
/**
 * D1 — Placement Lifecycle State Model
 * ------------------------------------
 * Owns the canonical `status` field on a listing, per the SHARED CONTRACTS
 * section of Rehoming-Platform-Task-Assignments.md:
 *
 *   active | pending_placement | placement_complete | placement_fell_through
 *
 * Task E2 will append `no_longer_available` to this same enum at its own
 * integration pass (account-lifecycle driven, not a transition this module
 * fires). This file does NOT add that value itself — it only guards against
 * the four transitions D1 owns, and treats any unrecognized status string
 * (including E2's eventual addition) as "not mine to validate a transition
 * into/out of," so it won't throw on a value it doesn't own.
 *
 * Every other task referencing listing status (A3, E1a, F1) should read
 * `listing.status` directly — this module is the only thing that's allowed
 * to WRITE it.
 */

// The four values this task owns. E2 appends a fifth (`no_longer_available`)
// to the real enum at integration — this constant stays D1's four only.
const STATUS = Object.freeze({
  ACTIVE: 'active',
  PENDING_PLACEMENT: 'pending_placement',
  PLACEMENT_COMPLETE: 'placement_complete',
  PLACEMENT_FELL_THROUGH: 'placement_fell_through',
});

// Legal transitions, keyed by current status. `placement_fell_through` is a
// terminal state for this module (the fallback flow — waitlist notify —
// happens from there, but there's no further status transition D1 defines
// out of it; if a merchant wants to re-list, that's a new listing/back to
// `active`, which we allow explicitly below since it's a real merchant need
// even though the task doc doesn't spell it out — flagged in the completion
// file as a deliberate, visible addition, not a silent one).
const ALLOWED_TRANSITIONS = Object.freeze({
  [STATUS.ACTIVE]: [STATUS.PENDING_PLACEMENT],
  [STATUS.PENDING_PLACEMENT]: [STATUS.PLACEMENT_COMPLETE, STATUS.PLACEMENT_FELL_THROUGH],
  [STATUS.PLACEMENT_COMPLETE]: [], // terminal — verified rehome, feeds B3/B4 stats
  [STATUS.PLACEMENT_FELL_THROUGH]: [STATUS.ACTIVE], // merchant can re-list
});

/**
 * Side-effect hooks fired on each transition. Left as named stub functions
 * so the checklist/upload/check-in/waitlist pieces (the task's later
 * deliverables) each have one obvious place to plug in, rather than this
 * module growing a monolithic switch statement as those land.
 */
const onEnterPendingPlacement = (listing, context) => {
  // context.selectedVisitorId — the visitor the merchant picked from their
  // conversation thread. Stored so the vet-document upload and the waitlist
  // flow both know who the "likely adopter" is without re-deriving it.
  listing.likely_adopter_id = context.selectedVisitorId || null;
  // Vet document upload becomes available starting here — handled by the
  // upload feature, not this module; this hook is just the state trigger.
};

const onEnterPlacementComplete = (listing /*, context */) => {
  // Triggers: Ownership Transfer Checklist shown to both parties,
  // vet-document auto-delivery if one was uploaded, "verified rehome" stat
  // for B3/B4, and the 7/30-day check-in schedule. All handled by their own
  // modules — this hook just stamps the timestamp those modules key off of.
  listing.placement_complete_at = new Date().toISOString();
};

const onEnterPlacementFellThrough = (listing /*, context */) => {
  // Surfaces the "Notify next waitlisted visitor" one-click action on the
  // merchant dashboard. Does not auto-notify — merchant-initiated per spec.
  listing.placement_fell_through_at = new Date().toISOString();
};

const onEnterActive = (listing, context) => {
  // Re-list path (fell_through -> active). Clears the prior likely-adopter
  // pointer so an old thread doesn't leak into the new attempt.
  if (context && context.fromFellThrough) {
    listing.likely_adopter_id = null;
  }
};

const TRANSITION_HOOKS = Object.freeze({
  [STATUS.PENDING_PLACEMENT]: onEnterPendingPlacement,
  [STATUS.PLACEMENT_COMPLETE]: onEnterPlacementComplete,
  [STATUS.PLACEMENT_FELL_THROUGH]: onEnterPlacementFellThrough,
  [STATUS.ACTIVE]: onEnterActive,
});

// Listeners notified after every successful transition. Lets later D1 pieces
// (vet-document delivery, check-in scheduling, waitlist) react to a status
// change without this file importing them (avoids circular dependencies).
const _listeners = [];
function onTransition(fn) { _listeners.push(fn); }

/**
 * Attempts to move a listing from its current status to `nextStatus`.
 * Throws on an illegal transition rather than silently coercing — callers
 * (merchant dashboard actions) should catch and surface a friendly message.
 *
 * @param {object} listing - the listing/placement record. Must have a
 *   `status` field already set to one of STATUS's values (or left unset,
 *   which is treated as ACTIVE — a brand-new listing's default).
 * @param {string} nextStatus - one of STATUS's values.
 * @param {object} [context] - optional data a given transition's hook needs
 *   (e.g. { selectedVisitorId } when entering PENDING_PLACEMENT).
 * @returns {object} the same listing object, mutated in place, for chaining.
 */
function transitionListingStatus(listing, nextStatus, context = {}) {
  const current = listing.status || STATUS.ACTIVE;

  if (!Object.values(STATUS).includes(nextStatus)) {
    // Not a value this module owns (e.g. E2's `no_longer_available`) —
    // refuse to write it here. E2's own module should write that value.
    throw new Error(
      `placement-lifecycle: "${nextStatus}" is not a D1-owned status value. ` +
      `If this is E2's no_longer_available, write it from E2's module instead.`
    );
  }

  const allowedNext = ALLOWED_TRANSITIONS[current] || [];
  if (!allowedNext.includes(nextStatus)) {
    throw new Error(
      `placement-lifecycle: illegal transition "${current}" -> "${nextStatus}". ` +
      `Allowed from "${current}": [${allowedNext.join(', ') || 'none — terminal state'}]`
    );
  }

  listing.status = nextStatus;

  const hook = TRANSITION_HOOKS[nextStatus];
  if (hook) {
    hook(listing, { ...context, fromFellThrough: current === STATUS.PLACEMENT_FELL_THROUGH });
  }

  // Notify listeners (they receive the same context, including any deps).
  _listeners.forEach((fn) => fn(listing, current, nextStatus, context));

  return listing;
}

/**
 * Local stub matching A1's published listing core-schema contract, extended
 * with D1's own fields. Used for building/testing D1's pieces against the
 * correct shape before A1's real listing file exists (SHARED CONTRACTS
 * "build against a local stub" pattern) — reconcile field-for-field into
 * A1's real file once A1's completion file exists.
 */
function createStubListing(overrides = {}) {
  // Listing ID field: the real reference file (pet.json, confirmed against
  // the two summary docs per BUILD APPROACH) stores BOTH `pet_id` and `id`
  // as mirrored copies of the same value — not just one or the other.
  // Mirroring both here (rather than guessing a single name) is what "whatever
  // the existing pet-listing ID field is called... confirm exact name against
  // the two summary docs" actually resolves to once pet.json is checked.
  const idValue = overrides.id || overrides.pet_id || 'stub-listing-001';
  return {
    // Fields owned by other tasks — present here only so D1's own code can
    // be exercised against a realistic shape, not re-implemented by D1.
    id: idValue,
    pet_id: idValue, // mirrors pet.json's real shape — see note above
    merchant_id: overrides.merchant_id || null, // not in A1's published contract; D1 assumes it exists (flagged in completion file)
    health_status: 'Healthy, up to date on vaccines', // A1
    temperament: 'Friendly, good with kids', // A1
    reason_for_rehoming: 'Moving, cannot bring pets', // A1
    spay_neuter_status: 'spayed_neutered', // A1
    listing_type: 'permanent', // A1
    currency_code: 'USD', // A1
    is_urgent: false, // A1
    grouped_with: [], // A2
    needs_extra_love: false, // A2

    // Fields this task (D1) owns. Centralized here, all in one place, so the
    // full shape of a D1-touched listing is documented in one spot rather
    // than discoverable only by reading vet-document.js/placement-checkins.js's
    // defensive `|| []`/`|| {}` fallbacks piecemeal.
    status: STATUS.ACTIVE,
    likely_adopter_id: null,
    placement_complete_at: null,
    placement_fell_through_at: null,
    ownership_transfer_checklist: {
      microchip_updated: false,
      licensing_updated: false,
      vet_records_handed_off: false,
    },
    vet_document_url: null, // set by the upload feature, delivered on placement_complete
    vet_documents: [], // full set of { name, url, uploaded_at } — see vet-document.js
    vet_documents_delivered_at: null, // see vet-document.js
    waitlist: [], // array of { visitor_id, joined_at } — private, not a public count
    checkins_sent: {}, // { '7day': isoTimestamp, '30day': isoTimestamp } — see placement-checkins.js
    checkin_responses: [], // see placement-checkins.js
    admin_flag_no_longer_with_adopter: null, // see placement-checkins.js

    ...overrides,
  };
}

module.exports = {
  STATUS,
  ALLOWED_TRANSITIONS,
  transitionListingStatus,
  onTransition,
  createStubListing,
};
/* <!-- TASK-D1 END --> */
