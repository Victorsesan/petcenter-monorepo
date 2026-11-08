/* <!-- TASK-D1 START --> */
/**
 * D1 — Visitor Notification List (new, built from scratch per the task doc)
 * -------------------------------------------------------------------------
 * A small in-account list of dated items a visitor can see and dismiss.
 * Not a chat system, not showAuthPrompt() (Task A4). Used by D1 for:
 *   - vet document delivered
 *   - 7-day and 30-day post-placement check-ins
 *   - "no longer with adopter" flag acknowledgement
 *   - waitlist spot opened
 *
 * NOTIFICATION RECORD SHAPE (published for the completion file / CEO
 * reconciliation with the separate feature-addition project's stub):
 *   {
 *     id:           string   unique id
 *     recipient_id: string   the visitor account this belongs to
 *     type:         string   one of NOTIFICATION_TYPES
 *     listing_id:   string   listing this relates to (or null)
 *     title:        string   short heading
 *     body:         string   one or two sentences
 *     action_url:   string   optional link target (or null)
 *     created_at:   string   ISO timestamp
 *     dismissed_at: string   ISO timestamp, or null while visible
 *   }
 *
 * RENDER LOCATION: a "Notifications" section in the visitor account area,
 * with an unread-count badge on its nav entry. The account area itself is
 * outside D1's scope, so this file exposes renderNotificationList() as a
 * drop-in that returns markup for whichever container the account area
 * provides.
 *
 * STORAGE: in-memory store behind createNotification/listNotifications so
 * the module is testable now; swap the store functions for the platform's
 * real persistence (JSON file or DB, per the project's data conventions)
 * at integration without changing any caller.
 */

const NOTIFICATION_TYPES = Object.freeze({
  VET_DOCUMENT_DELIVERED: 'vet_document_delivered',
  CHECKIN_7_DAY: 'checkin_7day',
  CHECKIN_30_DAY: 'checkin_30day',
  NO_LONGER_WITH_ADOPTER: 'no_longer_with_adopter',
  WAITLIST_SPOT_OPEN: 'waitlist_spot_open',
});

// In-memory store. Replace with real persistence at integration.
const _store = [];
let _idCounter = 0;

/** Creates and stores a notification; returns the new record. */
function createNotification({ recipient_id, type, listing_id = null, title, body, action_url = null }) {
  if (!Object.values(NOTIFICATION_TYPES).includes(type)) {
    throw new Error(`visitor-notifications: unknown type "${type}"`);
  }
  const record = {
    id: `notif-${++_idCounter}`,
    recipient_id,
    type,
    listing_id,
    title,
    body,
    action_url,
    created_at: new Date().toISOString(),
    dismissed_at: null,
  };
  _store.push(record);
  return record;
}

/** Returns a visitor's undismissed notifications, newest first. */
function listNotifications(recipient_id) {
  return _store
    .filter((n) => n.recipient_id === recipient_id && !n.dismissed_at)
    .sort((a, b) => b.created_at.localeCompare(a.created_at));
}

/** Marks one notification dismissed. Returns true if it existed and was visible. */
function dismissNotification(id, recipient_id) {
  const n = _store.find((x) => x.id === id && x.recipient_id === recipient_id && !x.dismissed_at);
  if (!n) return false;
  n.dismissed_at = new Date().toISOString();
  return true;
}

/** Count for the nav badge. */
function getUnreadCount(recipient_id) {
  return listNotifications(recipient_id).length;
}

/**
 * Minimal HTML-escaping helper. `title`/`body`/`action_url` are supplied by
 * whichever D1 module creates the notification (vet-document.js,
 * placement-checkins.js, placement-waitlist.js) — today those are all
 * hardcoded, author-written strings, but `action_url` is built from
 * `listing.id` and a stored upload URL, neither of which this module
 * controls, so every one of these fields is escaped before being placed in
 * markup rather than assuming today's callers are the only callers this
 * will ever have.
 */
function escapeHtml(value) {
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

/**
 * Returns list markup. Each row has a dismiss button carrying
 * data-notification-id; the caller wires click -> dismissNotification.
 * Dismiss uses an SVG X icon (no emoji, per Section 11).
 */
function renderNotificationList(recipient_id) {
  const items = listNotifications(recipient_id);
  if (!items.length) {
    return `<p style="color:var(--muted);font-size:14px;">You have no notifications.</p>`;
  }
  return items.map((n) => {
    const date = new Date(n.created_at).toLocaleDateString();
    const link = n.action_url
      ? ` <a href="${escapeHtml(n.action_url)}" style="color:var(--primary);text-decoration:underline;">Open</a>`
      : '';
    return `
      <div class="notification-item" style="display:flex;gap:12px;align-items:flex-start;padding:12px 0;border-bottom:1px solid var(--border);">
        <div style="flex:1;">
          <div style="font-family:'Sora',sans-serif;font-weight:600;font-size:14px;color:var(--text);">${escapeHtml(n.title)}</div>
          <div style="font-size:13.5px;color:var(--muted);margin-top:2px;">${escapeHtml(n.body)}${link}</div>
          <div style="font-family:'Bitter',serif;font-size:12px;color:var(--muted);margin-top:4px;">${date}</div>
        </div>
        <button type="button" class="small-btn" data-notification-id="${escapeHtml(n.id)}" aria-label="Dismiss notification">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>`;
  }).join('');
}

module.exports = {
  NOTIFICATION_TYPES,
  createNotification,
  listNotifications,
  dismissNotification,
  getUnreadCount,
  renderNotificationList,
};
/* <!-- TASK-D1 END --> */
