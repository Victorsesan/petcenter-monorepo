/* <!-- TASK-D1 START --> */
/**
 * D1 — Vet Document Upload & Delivery
 * -----------------------------------
 * DELIVERY DECISION (flagged for the completion file, per CEO instruction
 * to take the recommended option rather than leave it pending):
 *   Chosen: in-dashboard download link, PLUS a visitor notification and a
 *   short email that contains the link (not the file as an attachment).
 *   Why: no file payloads through the mailer, reuses the checklist's
 *   existing document link, and the adopter still gets pushed to it.
 *   Trade-off: adopter must be logged in to open the link.
 *
 * Upload UI follows Section 7's file-upload component (same drop zone and
 * list classes, same constants pattern), narrowed to this field's contract:
 * PDF/JPG/PNG, 5MB, max 5 files. The DOM wiring is exactly Section 7's;
 * this file holds the field contract and the logic around it.
 */

const { STATUS, onTransition } = require('./placement-lifecycle-v1.js');
// BUGFIX-MANUS-06-3-IMPORT-START — reuse D1's URL allowlist and HTML escaping for uploaded links.
const { createNotification, NOTIFICATION_TYPES, safeActionUrl, escapeHtml } = require('./visitor-notifications-v1.js');
// BUGFIX-MANUS-06-3-IMPORT-END

// Field contract (Section 7: "these three constants are the field's real contract")
const VET_DOC_MAX_FILES = 5;
const VET_DOC_MAX_BYTES = 5 * 1024 * 1024;
const VET_DOC_ALLOWED_TYPES = Object.freeze(['image/jpeg', 'image/jpg', 'image/png', 'application/pdf']);

/**
 * Upload is available once pending_placement is set, and stays available
 * through placement_complete (so a merchant who forgot can still add it,
 * and delivery then happens immediately). Not available otherwise.
 */
function isVetUploadAvailable(listing) {
  return listing.status === STATUS.PENDING_PLACEMENT || listing.status === STATUS.PLACEMENT_COMPLETE;
}

/**
 * Validates one file against the field contract.
 * Returns { ok: true } or { ok: false, message } (message suits Section 7's alert()).
 */
function validateVetFile(file, currentCount) {
  if (currentCount >= VET_DOC_MAX_FILES) return { ok: false, message: `Max ${VET_DOC_MAX_FILES} files allowed.` };
  if (file.size > VET_DOC_MAX_BYTES) return { ok: false, message: `File "${file.name}" too large. Max 5MB.` };
  if (!VET_DOC_ALLOWED_TYPES.includes(file.type)) return { ok: false, message: `File "${file.name}" type not allowed.` };
  return { ok: true };
}

/**
 * Records an uploaded document on the listing. `vet_document_url` always
 * points at the first document (what the checklist links to); the full set
 * lives in `vet_documents`. If the listing is already placement_complete,
 * delivery fires right away so a late upload is never silently stranded.
 */
function attachVetDocument(listing, doc, deps = {}) {
  if (!isVetUploadAvailable(listing)) {
    throw new Error('vet-document: upload is only available once a listing is pending_placement or placement_complete.');
  }
  listing.vet_documents = listing.vet_documents || [];
  listing.vet_documents.push({ name: doc.name, url: doc.url, uploaded_at: new Date().toISOString() });
  listing.vet_document_url = listing.vet_documents[0].url;

  if (listing.status === STATUS.PLACEMENT_COMPLETE) {
    deliverVetDocuments(listing, deps);
  }
  return listing;
}

/**
 * Delivers the documents to the adopter: notification + email-with-link.
 * Safe to call more than once: it only delivers once per listing
 * (tracked via vet_documents_delivered_at), and does nothing if no
 * documents exist or no adopter is set.
 *
 * deps.sendEmail({to, subject, html}) is injected so this module doesn't
 * hard-code the mailer. At integration, pass a wrapper around the
 * platform's existing mailer (contact-config.php / chat-mail.php pattern).
 * deps.getAdopterEmail(adopter_id) supplies the address.
 */
function deliverVetDocuments(listing, deps = {}) {
  if (listing.vet_documents_delivered_at) return false;
  if (!listing.vet_documents || !listing.vet_documents.length) return false;
  if (!listing.likely_adopter_id) return false;

  // BUGFIX-MANUS-06-3-DELIVERY-START — stored upload URLs must not become unsafe hrefs.
  const safeUrl = safeActionUrl(listing.vet_document_url);
  const emailUrl = safeUrl && /^https:\/\//i.test(safeUrl) ? safeUrl : null;

  createNotification({
    recipient_id: listing.likely_adopter_id,
    type: NOTIFICATION_TYPES.VET_DOCUMENT_DELIVERED,
    listing_id: listing.id,
    title: 'Vet records are ready',
    body: 'The previous owner uploaded vaccination and vet records for your new pet.',
    action_url: safeUrl,
  });

  // Email is best-effort: a mail failure must never block delivery or throw.
  try {
    if (deps.sendEmail && deps.getAdopterEmail) {
      const to = deps.getAdopterEmail(listing.likely_adopter_id);
      if (to) {
        deps.sendEmail({
          to,
          subject: 'Vet records for your new pet',
          html: emailUrl
            ? `<p>The previous owner has uploaded vet records. Log in to view them: <a href="${escapeHtml(emailUrl)}">open documents</a>.</p>`
            : '<p>The previous owner has uploaded vet records. Log in to view them.</p>',
        });
      }
    }
  } catch (err) {
    // Swallowed on purpose; the in-dashboard notification already exists.
  }

  listing.vet_documents_delivered_at = new Date().toISOString();
  return true;
  // BUGFIX-MANUS-06-3-DELIVERY-END
}

// Auto-delivery: when a listing reaches placement_complete, deliver any
// documents already uploaded. Callers pass { deps: { sendEmail, getAdopterEmail } }
// in the transition context to enable the email; notification works without it.
onTransition((listing, from, to, context) => {
  if (to === STATUS.PLACEMENT_COMPLETE) deliverVetDocuments(listing, (context && context.deps) || {});
});

module.exports = {
  VET_DOC_MAX_FILES,
  VET_DOC_MAX_BYTES,
  VET_DOC_ALLOWED_TYPES,
  isVetUploadAvailable,
  validateVetFile,
  attachVetDocument,
  deliverVetDocuments,
};
/* <!-- TASK-D1 END --> */
