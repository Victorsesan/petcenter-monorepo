# Task-SHIPPING-TIER1-Completion

## Task Number
"SHIPPING-TIER1" — no formal numeric task ID was assigned by the tracker; built directly at the CEO's instruction as Tier 1 of the shipping-status feature discussed following Task 11's research. Anchored in code as `SHIPPING-TIER1` rather than `TASK-N`, consistently across both files below.

## What Was Required
Give shipping an actual trackable status on an APPROVED adoption application: an internal `SHIP-XXXXXXXX` code, a manually-set status of Preparing/Shipped/Delivered, and optional carrier/tracking-number fields — closing the "no order ID, no tracking number" gap identified in Task 11's research, with zero external dependencies, and laying groundwork for an optional future Tier 2 (automated carrier sync) without needing a schema change later.

## Files Changed
| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `update-adoption.php` | `admin/update-adoption.php` | Changed | Yes — also carries TASK-10 and TASK-12 code, confirmed no overlap with this task's additions |
| `admin-dashboard.html` | `admin/index.html` | Changed | Yes — also carries Task 4, TASK-9, and TASK-10 code, confirmed no overlap with this task's additions |

*(Live paths taken from the delivery guide's mapping table, row "Any admin PHP/JS... → `admin/` + the same file name" for `update-adoption.php`, since it isn't individually listed; and the explicit `admin-dashboard.html` → `admin/index.html` row for the second file.)*

## Code Changes

### `update-adoption.php` → live path `admin/update-adoption.php`

<!-- SHIPPING-TIER1 START -->
**Old code:**
```php
$approved_by       = $_POST['approved_by']       ?? null;
$approved_position = $_POST['approved_position'] ?? null;
// TASK-10 END

if (!$code || !$status) {
```
**New code:**
```php
$approved_by       = $_POST['approved_by']       ?? null;
$approved_position = $_POST['approved_position'] ?? null;
// TASK-10 END
// SHIPPING-TIER1 START: manual shipping-status update (Tier 1 of the shipping
// status-tracking feature — requested directly by the CEO ahead of a formal
// tracker task number, so anchored descriptively rather than TASK-N; happy to
// relabel these anchors if/when this gets an official task number).
// This is a fully separate code path from the APPROVED/REJECTED adoption-status
// flow below — it never touches $application['status'], so it can't be confused
// with or interfere with the approve/reject logic. Handled and exited before the
// "code and status required" check below, since a shipping-only update
// legitimately has no $status value to send.
$ship_status      = $_POST['ship_status']      ?? null;
$shipping_carrier = $_POST['shipping_carrier'] ?? null;
$tracking_number  = $_POST['tracking_number']  ?? null;

if ($code && $ship_status && !$status) {
    $valid_ship_statuses = ['PREPARING', 'SHIPPED', 'DELIVERED'];
    if (!in_array($ship_status, $valid_ship_statuses, true)) {
        http_response_code(400);
        die(json_encode(['success' => false, 'message' => 'ship_status must be PREPARING, SHIPPED, or DELIVERED']));
    }

    $app_file = __DIR__ . "/../adoptions/applications/{$code}/application.json";
    if (!file_exists($app_file)) {
        http_response_code(404);
        die(json_encode(['success' => false, 'message' => 'Application not found']));
    }

    $application = json_decode(file_get_contents($app_file), true);

    // Shipping only makes sense once the adoption itself has been approved
    // (i.e. checkout has already happened) — matches the same gating logic
    // the rest of this file already applies to checkout_url.
    if (($application['status'] ?? null) !== 'APPROVED') {
        http_response_code(400);
        die(json_encode(['success' => false, 'message' => 'Shipping status can only be set on an APPROVED application']));
    }

    // ship_code is generated once, on the first shipping update, and kept
    // stable after that — same one-time-generation shape as the existing
    // adoption_code, not regenerated on every status change.
    if (empty($application['ship_code'])) {
        $application['ship_code'] = 'SHIP-' . strtoupper(bin2hex(random_bytes(4)));
    }

    $application['ship_status']      = $ship_status;
    $application['shipping_carrier'] = $shipping_carrier ?: ($application['shipping_carrier'] ?? null);
    $application['tracking_number']  = $tracking_number  ?: ($application['tracking_number']  ?? null);
    $application['ship_updated_at']  = date('Y-m-d H:i:s');

    // Append-only history, same shape/intent as the uptime dashboard's history
    // array elsewhere in this project — also what Tier 2 (automated carrier
    // sync) will read/append to later without needing a schema change now.
    if (!isset($application['ship_history']) || !is_array($application['ship_history'])) {
        $application['ship_history'] = [];
    }
    $application['ship_history'][] = [
        'status'          => $ship_status,
        'at'              => $application['ship_updated_at'],
        'carrier'         => $application['shipping_carrier'],
        'tracking_number' => $application['tracking_number'],
    ];

    file_put_contents($app_file, json_encode($application, JSON_PRETTY_PRINT));
    echo json_encode(['success' => true, 'message' => 'Shipping status updated', 'ship_code' => $application['ship_code']]);
    exit;
}
// SHIPPING-TIER1 END

if (!$code || !$status) {
```
<!-- SHIPPING-TIER1 END -->

---

### `admin-dashboard.html` → live path `admin/index.html`

Four separate blocks in this file. Each is its own old/new pair below, in file order.

<!-- SHIPPING-TIER1 START (block 1 of 4 — new modal markup) -->
**Old code:**
```html
    <div style="display:flex;gap:10px;">
      <button onclick="submitReject()" class="pc-btn pc-btn--primary" style="flex:1;background:linear-gradient(135deg,#c0392b,#a93226);">Send Rejection</button>
      <button onclick="closeRejectModal()" class="pc-btn pc-btn--ghost">Cancel</button>
    </div>
  </div>
</div>

<!-- TASK-4 (CEO task-list item 6): upload/file preview modal
```
**New code:**
```html
    <div style="display:flex;gap:10px;">
      <button onclick="submitReject()" class="pc-btn pc-btn--primary" style="flex:1;background:linear-gradient(135deg,#c0392b,#a93226);">Send Rejection</button>
      <button onclick="closeRejectModal()" class="pc-btn pc-btn--ghost">Cancel</button>
    </div>
  </div>
</div>

<!-- SHIPPING MODAL -->
<!-- SHIPPING-TIER1 START: manual shipping-status update modal, requested directly
     by the CEO ahead of a formal tracker task number. Same Use A confirmation-modal
     shell (overflow-y:auto + max-height safety net) as approveModal/rejectModal
     above — reused rather than inventing a new modal pattern. Only ever opened
     from an APPROVED application (see openShippingModal / the Quick action button
     in viewApplication below), so no separate status-gating needed here in the UI —
     the backend still enforces it independently either way. -->
<div id="shippingModal" class="pc-modal-shell" style="position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(16,33,75,0.65);display:none;align-items:center;justify-content:center;z-index:10000;padding:20px;">
  <div style="width:100%;max-width:520px;max-height:85vh;overflow-y:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch;background:#fff;border-radius:14px;padding:32px;box-shadow:0 20px 60px rgba(16,33,75,0.25);">
    <h3 style="font-family:'Cormorant Garamond',serif;font-size:22px;color:#10214b;margin:0 0 8px;">Update Shipping Status</h3>
    <p style="color:#7a6d5f;font-size:14px;margin-bottom:20px;">Set the current shipping status for this adoption. A shipping code is generated automatically the first time you save.</p>
    <label class="pc-form-label" style="margin-bottom:6px;">Status</label>
    <select id="shipStatusSelect" class="pc-form-input" style="margin-bottom:14px;">
      <option value="PREPARING">Preparing</option>
      <option value="SHIPPED">Shipped</option>
      <option value="DELIVERED">Delivered</option>
    </select>
    <label class="pc-form-label" style="margin-bottom:6px;">Carrier <span style="font-weight:400;text-transform:none;">(optional)</span></label>
    <input type="text" id="shipCarrierInput" class="pc-form-input" placeholder="e.g. UPS, FedEx, USPS" style="margin-bottom:14px;">
    <label class="pc-form-label" style="margin-bottom:6px;">Tracking Number <span style="font-weight:400;text-transform:none;">(optional)</span></label>
    <input type="text" id="shipTrackingInput" class="pc-form-input" placeholder="e.g. 1Z999AA10123456784" style="margin-bottom:18px;">
    <div style="display:flex;gap:10px;">
      <button onclick="submitShippingStatus()" class="pc-btn pc-btn--primary" style="flex:1;">Save Shipping Status</button>
      <button onclick="closeShippingModal()" class="pc-btn pc-btn--ghost">Cancel</button>
    </div>
  </div>
</div>
<!-- SHIPPING-TIER1 END -->

<!-- TASK-4 (CEO task-list item 6): upload/file preview modal
```
<!-- SHIPPING-TIER1 END (block 1 of 4) -->

<!-- SHIPPING-TIER1 START (block 2 of 4 — shipping-status display card in the view-application modal) -->
**Old code:**
```html
      <!-- Rejection notes if rejected -->
      ${status === 'REJECTED' && app.rejection_notes ? `
```
**New code:**
```html
      <!-- SHIPPING-TIER1 START: shipping-status display block, mirroring the
           Approved By block's exact card styling immediately above (per
           Warning & Instructions rule 7 — reuse existing patterns rather than
           inventing new ones). Only rendered for APPROVED applications, and
           only once a shipping update has actually been saved at least once
           (i.e. app.ship_code exists) — before that, no card shows here and
           the Quick action button below (Update Shipping) is what a staff
           member uses to set it the first time. -->
      ${status === 'APPROVED' && app.ship_code ? (() => {
          const shipStyles = {
            PREPARING: { bg: '#fffbe6', border: '#f5a623', text: '#b87a00', label: 'Preparing' },
            SHIPPED:   { bg: '#eaf1ff', border: '#3468c0', text: '#2b56a0', label: 'Shipped' },
            DELIVERED: { bg: '#e6f7ef', border: '#0d7c4a', text: '#0d7c4a', label: 'Delivered' },
          };
          const sst = shipStyles[app.ship_status] || shipStyles.PREPARING;
          return `
      <div style="background:#f9f7f2;border:1px solid #ece7e0;border-radius:10px;padding:14px 16px;margin-bottom:16px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
          <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#8b7d6b;">Shipping Status</div>
          <span style="padding:3px 12px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.04em;background:${sst.bg};color:${sst.text};border:1px solid ${sst.border};">${sst.label}</span>
        </div>
        <div style="font-size:12px;color:#7a6d5f;">Shipping Code: <strong>${PC.esc(app.ship_code)}</strong></div>
        ${app.shipping_carrier ? `<div style="font-size:12px;color:#7a6d5f;margin-top:2px;">Carrier: <strong>${PC.esc(app.shipping_carrier)}</strong></div>` : ''}
        ${app.tracking_number ? `<div style="font-size:12px;color:#7a6d5f;margin-top:2px;">Tracking #: <strong>${PC.esc(app.tracking_number)}</strong></div>` : ''}
      </div>`;
        })() : ''}
      <!-- SHIPPING-TIER1 END -->

      <!-- Rejection notes if rejected -->
      ${status === 'REJECTED' && app.rejection_notes ? `
```
<!-- SHIPPING-TIER1 END (block 2 of 4) -->

<!-- SHIPPING-TIER1 START (block 3 of 4 — Update Shipping quick-action button) -->
**Old code:**
```html
      ${status === 'APPROVED' ? `
      <div style="padding-top:4px;">
        <button onclick="closeViewApplicationModal();openEditStatusModal('${PC.esc(app.adoption_code || code)}')" class="pc-btn pc-btn--ghost" style="width:100%;">Edit Status</button>
      </div>` : ''}
    `;
```
**New code:**
```html
      ${status === 'APPROVED' ? `
      <div style="display:flex;gap:10px;padding-top:4px;">
        <button onclick="closeViewApplicationModal();openEditStatusModal('${PC.esc(app.adoption_code || code)}')" class="pc-btn pc-btn--ghost" style="flex:1;">Edit Status</button>
        <!-- SHIPPING-TIER1: Update Shipping quick action, same row as Edit Status -->
        <button onclick="closeViewApplicationModal();openShippingModal('${PC.esc(app.adoption_code || code)}', '${PC.esc(app.ship_status || 'PREPARING')}', '${PC.esc(app.shipping_carrier || '')}', '${PC.esc(app.tracking_number || '')}')" class="pc-btn pc-btn--primary" style="flex:1;background:linear-gradient(135deg,#3468c0,#2b56a0);">Update Shipping</button>
      </div>` : ''}
    `;
```
<!-- SHIPPING-TIER1 END (block 3 of 4) -->
*(Note: block 3's first draft briefly used `JSON.stringify()` for the button's arguments, which would have broken this double-quoted `onclick` attribute. Caught and fixed before delivery — the "New code" above already reflects the corrected, final version, not the intermediate draft.)*

<!-- SHIPPING-TIER1 START (block 4 of 4 — new JS functions) -->
**Old code:**
```javascript
  modal.querySelector('p').textContent  = 'Change this approved application to rejected and add a note.';
  modal.style.display = 'flex';
}

// INIT
```
**New code:**
```javascript
  modal.querySelector('p').textContent  = 'Change this approved application to rejected and add a note.';
  modal.style.display = 'flex';
}

// ── UPDATE SHIPPING (Tier 1 — manual, requested directly by the CEO ahead ───
// of a formal tracker task number) ──────────────────────────────────────────
// SHIPPING-TIER1 START
let currentShippingCode = null;

function openShippingModal(code, currentStatus, currentCarrier, currentTracking) {
  currentShippingCode = code;
  document.getElementById('shipStatusSelect').value  = currentStatus || 'PREPARING';
  document.getElementById('shipCarrierInput').value   = currentCarrier || '';
  document.getElementById('shipTrackingInput').value  = currentTracking || '';
  document.getElementById('shippingModal').style.display = 'flex';
}

function closeShippingModal() {
  document.getElementById('shippingModal').style.display = 'none';
  currentShippingCode = null;
}

async function submitShippingStatus() {
  if (!await checkTokenBeforeAction('submitShippingStatus')) return;
  const code = currentShippingCode;
  if (!code) return;
  const shipStatus = document.getElementById('shipStatusSelect').value;
  const carrier    = document.getElementById('shipCarrierInput').value.trim();
  const tracking   = document.getElementById('shipTrackingInput').value.trim();
  try {
    const fd = new FormData();
    fd.append('token', adminToken);
    fd.append('code', code);
    fd.append('ship_status', shipStatus);
    fd.append('shipping_carrier', carrier);
    fd.append('tracking_number', tracking);
    window.dlog(`Update Shipping Status: code=${code}, ship_status=${shipStatus}`, 'info');
    const res  = await fetch('./update-adoption.php', { method: 'POST', body: fd });
    const json = await res.json();
    if (json.success) {
      PC.toast('Shipping status updated.');
      window.dlog(`Update Shipping Status: success — code=${code}, ship_code=${json.ship_code || '?'}`, 'ok');
      closeShippingModal();
      loadApplications();
    } else {
      PC.toast(json.message || 'Error', 'error');
      window.dlog('Update Shipping Status: failed — ' + (json.message || 'unknown error'), 'error');
    }
  } catch (e) {
    PC.toast('Network error: ' + e.message, 'error');
    window.dlog('Update Shipping Status: network error — ' + e.message, 'error');
  }
}
// SHIPPING-TIER1 END

// INIT
```
<!-- SHIPPING-TIER1 END (block 4 of 4) -->

## New files
No new files. (Everything above is an addition to two existing files; nothing new was created, so no `Task-SHIPPING-TIER1-NewFiles.zip` is included.)

## Bugs Found Outside Task Scope
- `update-adoption.php` writes `application.json` with a plain `file_put_contents()` — no `flock()` — unlike Task 8's `action.php`, which uses `flock()` for its append-only history writes. Not touched here, since fixing it would change existing, unrelated behavior outside this task's scope. Two admins editing the same application at once could race.
- `update-adoption.php`'s `$app_file` path is built by interpolating `$code` directly from `$_POST` with no sanitization (`__DIR__ . "/../adoptions/applications/{$code}/application.json"`) — present in the original file before any of my changes, and my new branch reuses the exact same pattern rather than introducing a new one. A `$code` value containing path-traversal characters could point outside the intended folder. Not introduced or fixed here; flagging because it's real.
- Not a code bug, but an open dependency: `admin-dashboard.html`'s `viewApplication()` reads application data from `get-applications.php`, which I was never given and have not seen. Based on the existing view modal already rendering dozens of fields beyond what `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s description of that endpoint lists, it's very likely that endpoint returns the full decoded `application.json` (in which case the new shipping fields display automatically) — but this is inference, not confirmation. The write path is unaffected either way.

## SECOND VERIFICATION ROUND
Performed a full re-check against both project summary docs and the original files, specifically looking for sync issues, missed files, and quiet bugs:
- Confirmed brace/parenthesis balance in `update-adoption.php` (38/38, 147/147) and re-ran a JS syntax check (`node --check`) on the full merged `admin-dashboard.html` script block — both pass.
- Confirmed every CSS class used (`pc-modal-shell`, `pc-form-label`, `pc-form-input`) is a real, pre-existing class, not invented.
- Confirmed no modal in this file (including `approveModal`/`rejectModal`) has backdrop-click-to-close or a global Escape-key handler, so `shippingModal` not having one either is consistent, not a gap.
- Confirmed `admin-dashboard.html` as given already carried Task 4/TASK-9/TASK-10's merged code, so this task's additions are layered on the current chain, not an outdated snapshot.
- The `get-applications.php` item above was the one genuine open question surfaced by this pass — everything else checked out clean.

## Self-Check Confirmation
- Only `update-adoption.php` and `admin-dashboard.html` were touched — nothing outside this task's scope.
- Every code block above is wrapped in matched `SHIPPING-TIER1 START`/`END` anchors.
- Every "Old code" snippet above was copied character-for-character from the original files as given (confirmed by the fact that each was located via an exact-match text replacement at build time, which only succeeds on a unique, verbatim match) and each appears only once in its original file.
- No new files were created, so there is nothing to list in a zip and no zip is included.
- Nothing in this delivery touches `config.php`, `pet.json`, `.env`, or anything under `applications/` or `uploads/`.
