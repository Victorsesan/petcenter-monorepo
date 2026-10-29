/* <!-- TASK-D1 START --> */
/**
 * D1 — Ownership Transfer Checklist
 * ----------------------------------
 * Shown to BOTH parties (merchant + adopter) once a listing enters
 * `placement_complete` (see placement-lifecycle.js). Per the CEO's
 * correction in the task doc: informational only, self-checked by the
 * user, NOT verified or compliance-enforced by the platform.
 *
 * Built on PETCENTER_DESIGN_SYSTEM_v1_0.md Section 5's shared modal shell
 * (uses B/C structure: sticky .modal-top + scrolling .modal-scroll body) —
 * no new modal system invented, per Rule 7 / Section 5's "do not build a
 * separate modal system per use case" instruction.
 *
 * Status badges use the mandatory, unmodified 5-state system from Section
 * 18 (Rule: "Status badges ... use the exact 5-state / 4-type systems as
 * written — don't invent new status wording per feature"). D1's own status
 * values (active/pending_placement/placement_complete/placement_fell_through)
 * are a domain-specific enum owned by the SHARED CONTRACTS section — that
 * enum is NOT renamed to the 5 badge words. Only the *visual badge* each
 * value renders as is mapped onto the existing 5-state palette, chosen by
 * closest meaning:
 *
 *   active                  -> OPEN          (muted gray #7a6d5f)
 *   pending_placement       -> PENDING       (amber #f59e0b)
 *   placement_complete      -> COMPLETED     (green #10b981)
 *   placement_fell_through  -> CANCELLED     (red #ef4444)
 *
 * Section 20's Lavender Mist palette (the mandatory admin/merchant-dashboard
 * accent palette) is used elsewhere on the dashboard (cards, sidebar) but
 * deliberately NOT substituted into these badge colors — Section 1's
 * Mandatory list states the 5-state badge system has zero exceptions, so
 * Section 20's own loose badge-color illustration is treated as a stylistic
 * aside, not an override.
 */

// ---- Status badge mapping (Section 18's exact 5-state system) ----------

const STATUS_BADGE_MAP = Object.freeze({
  active: { badgeClass: 'status-open', label: 'Active' },
  pending_placement: { badgeClass: 'status-pending', label: 'Pending Placement' },
  placement_complete: { badgeClass: 'status-completed', label: 'Placement Complete' },
  placement_fell_through: { badgeClass: 'status-cancelled', label: 'Fell Through' },
});

/**
 * Returns { badgeClass, label } for a given D1 listing status, for use with
 * the Section 18 `.status-badge` CSS class already defined platform-wide.
 * Unknown values (e.g. E2's eventual `no_longer_available`) fall back to
 * the neutral OPEN styling rather than throwing — this is a read-only
 * display helper, not the status-writer (that's placement-lifecycle.js).
 */
function getStatusBadge(status) {
  return STATUS_BADGE_MAP[status] || { badgeClass: 'status-open', label: status };
}

// ---- Checklist definition ------------------------------------------------

// Fixed, informational-only items per the task doc. Order matters — vet
// records is last because it's the one with a linked action (the upload
// feature), so it reads naturally as "and also, here's the document."
const CHECKLIST_ITEMS = Object.freeze([
  {
    id: 'microchip_updated',
    label: 'Update the microchip registry with new owner details',
  },
  {
    id: 'licensing_updated',
    label: 'Update local pet licensing/registration if required in your area',
  },
  {
    id: 'vet_records_handed_off',
    label: 'Hand off vaccination/vet records',
    linksToUpload: true, // rendered with a link into the vet-document feature
  },
]);

/**
 * Pure helper: given a listing's `ownership_transfer_checklist` object
 * (see placement-lifecycle.js's createStubListing shape), returns how many
 * of the fixed items are checked. Used for an optional "2 of 3 done" label
 * — never used to gate or block anything, since this checklist is
 * explicitly not compliance-enforced.
 */
function getChecklistProgress(checklistState) {
  const state = checklistState || {};
  const total = CHECKLIST_ITEMS.length;
  const done = CHECKLIST_ITEMS.filter((item) => !!state[item.id]).length;
  return { done, total };
}

// ---- Modal markup, following Section 5's USE B/C shared shell exactly ----

/**
 * Minimal HTML-escaping helper for any value interpolated into markup that
 * isn't a hardcoded, author-controlled string. CHECKLIST_ITEMS' own labels
 * are static and never passed through this; `vet_document_url` (below) is
 * not — it originates from an uploaded file's stored URL, so it is escaped
 * before being placed inside an href attribute and link text.
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
 * Builds the inner HTML rendered into the shared `#modalContent` element
 * (Section 5's canonical markup) when opening the checklist. Does not open
 * the modal itself — call the existing shared `openFullPreview()` style
 * opener (Section 5's JS) with this as the content and no secondary action
 * button, since this checklist has no "download/generate" action the way
 * use C's PDF preview does.
 *
 * @param {object} listing - a listing with `ownership_transfer_checklist`
 *   and `vet_document_url` fields (see placement-lifecycle.js stub shape).
 * @param {function} onToggle - called as onToggle(itemId, checked) when the
 *   user (de)selects a checkbox; caller is responsible for persisting the
 *   change back onto the listing record (this module holds no state of
 *   its own, matching placement-lifecycle.js being the only status-writer).
 */
function renderChecklistModalContent(listing, onToggle) {
  const state = listing.ownership_transfer_checklist || {};
  const { done, total } = getChecklistProgress(state);

  const itemsHtml = CHECKLIST_ITEMS.map((item) => {
    const checked = !!state[item.id];
    const linkHtml = item.linksToUpload
      ? renderVetDocumentLink(listing)
      : '';
    return `
      <label class="checklist-item" style="display:flex;align-items:flex-start;gap:10px;padding:10px 0;border-bottom:1px solid var(--border);">
        <input
          type="checkbox"
          class="checklist-item__checkbox"
          data-item-id="${item.id}"
          ${checked ? 'checked' : ''}
          style="margin-top:3px;width:18px;height:18px;flex-shrink:0;"
        />
        <span style="font-family:'Sora',sans-serif;font-size:14.5px;line-height:1.5;color:var(--text);">
          ${item.label}
          ${linkHtml}
        </span>
      </label>`;
  }).join('');

  return `
    <h2 style="font-family:'Cormorant Garamond',serif;font-size:22px;color:var(--primary);margin:0 0 6px;">
      Ownership Transfer Checklist
    </h2>
    <p style="color:var(--muted);font-size:13px;margin:0 0 4px;">
      A helpful reminder, not a requirement — check items off as you complete them.
    </p>
    <p style="color:var(--muted);font-size:12.5px;margin:0 0 16px;"><span style="font-family:'Bitter',serif;">${done}</span> of <span style="font-family:'Bitter',serif;">${total}</span> checked</p>
    <div class="checklist-items">${itemsHtml}</div>
  `;

  // Wiring note (done by the caller after inserting this HTML, same pattern
  // as Section 5's own openFullPreview — this function only builds markup):
  //   modalContent.querySelectorAll('.checklist-item__checkbox')
  //     .forEach(cb => cb.addEventListener('change', (e) =>
  //       onToggle(e.target.dataset.itemId, e.target.checked)));
}

/**
 * Vet-document link shown inline with the "Hand off vaccination/vet
 * records" item. If the merchant has already uploaded a document (via the
 * separate vet-document-upload feature, not built in this file), link to
 * it; otherwise a short note that it isn't uploaded yet. Kept purely
 * informational — this checklist item is still self-checked regardless of
 * whether a document was attached, since a vet handoff can happen outside
 * the platform too.
 */
function renderVetDocumentLink(listing) {
  if (listing.vet_document_url) {
    // Escaped: this URL comes from an uploaded file's stored location, not
    // a hardcoded string, so it must not be trusted as safe markup as-is.
    const safeUrl = escapeHtml(listing.vet_document_url);
    return ` &mdash; <a href="${safeUrl}" target="_blank" rel="noopener" style="color:var(--primary);text-decoration:underline;">view uploaded document</a>`;
  }
  return ` <span style="color:var(--muted);font-style:italic;">(no document uploaded yet)</span>`;
}

module.exports = {
  getStatusBadge,
  CHECKLIST_ITEMS,
  getChecklistProgress,
  renderChecklistModalContent,
};
/* <!-- TASK-D1 END --> */
