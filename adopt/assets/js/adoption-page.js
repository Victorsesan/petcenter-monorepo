/* ════════════════════════════════════════════════════════════════
   Pet Center — adoption-page.js
   Page-specific JS for the adoption application form
   Shared utilities live in app.js | Form logic in adoption.js
   Both must be loaded before this file
   ════════════════════════════════════════════════════════════════ */

// ── Multi-step navigation ────────────────────────────────────────────────────
let currentStep = 1;
function goStep(step) {
  if (step > currentStep) {
    const active = document.getElementById(`step-${currentStep}`);
    const required = active.querySelectorAll('[required]');
    let ok = true;
    required.forEach(el => {
      if (!el.value.trim()) {
        el.style.boxShadow = '0 0 0 3px rgba(192,57,43,0.35)';
        ok = false;
      } else {
        el.style.boxShadow = '';
      }
    });
    if (!ok) {
      PC.toast('Please fill in all required fields before continuing.', 'error');
      return;
    }
  }

  document.getElementById(`step-${currentStep}`).classList.remove('active');
  document.getElementById(`step-ind-${currentStep}`).classList.remove('active');
  document.getElementById(`step-ind-${currentStep}`).classList.add('done');

  currentStep = step;
  document.getElementById(`step-${step}`).classList.add('active');
  document.getElementById(`step-ind-${step}`).classList.add('active');
  document.getElementById(`step-ind-${step}`).classList.remove('done');

  window.scrollTo({ top: 0, behavior: 'smooth' });

  if (step === 5) buildReview();
}

// ── Build review summary ─────────────────────────────────────────────────────
function buildReview() {
  const form = document.getElementById('adoptionForm');
  const data = Object.fromEntries(new FormData(form).entries());
  const el = document.getElementById('reviewSummary');
  const rows = [
    ['Name', data.name], ['Email', data.user_email], ['Phone', data.phone],
    ['ID Number', data.id_number], ['Employment', data.employment_status],
    ['Address', `${data.address} ${data.apt || ''}, ${data.city}, ${data.state} ${data.zip}`],
    ['Residence', data.residence_type], ['Home Type', data.home_type],
    ['Landlord Allows Pets', data.landlord_allows], ['Pet to Adopt', data.want_adopt],
    ['Primary Caregiver', data.primary_caregiver], ['Has Vet', data.have_vet],
    ['Can Afford Pet Care', data.can_afford], ['Pet Location', data.where_keep],
  ];
  el.innerHTML = rows.filter(r => r[1]).map(r =>
    `<div style="background:#f9f7f4;border-radius:8px;padding:12px 14px;">
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#7a6d5f;margin-bottom:3px;">${PC.esc(r[0])}</div>
      <div style="color:#2c2416;font-size:14px;">${PC.esc(r[1])}</div>
    </div>`
  ).join('');

  document.getElementById('submissionDate').value = new Date().toISOString().slice(0, 10);
}

// ── Signature pad ────────────────────────────────────────────────────────────
(function initSigPad() {
  const canvas = document.getElementById('sigCanvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  let drawing = false;
  let lastX = 0, lastY = 0;

  function getPos(e) {
    const r = canvas.getBoundingClientRect();
    const t = e.touches ? e.touches[0] : e;
    return [(t.clientX - r.left) * (canvas.width / r.width), (t.clientY - r.top) * (canvas.height / r.height)];
  }
  function resize() { canvas.width = canvas.offsetWidth; }
  resize();
  window.addEventListener('resize', resize);

  ctx.strokeStyle = '#10214b';
  ctx.lineWidth = 2.5;
  ctx.lineCap = 'round';
  ctx.lineJoin = 'round';

  canvas.addEventListener('mousedown', e => { drawing = true; [lastX, lastY] = getPos(e); });
  canvas.addEventListener('mousemove', e => {
    if (!drawing) return;
    const [x, y] = getPos(e);
    ctx.beginPath(); ctx.moveTo(lastX, lastY); ctx.lineTo(x, y); ctx.stroke();
    [lastX, lastY] = [x, y];
  });
  canvas.addEventListener('mouseup', () => drawing = false);
  canvas.addEventListener('mouseleave', () => drawing = false);
  canvas.addEventListener('touchstart', e => { e.preventDefault(); drawing = true; [lastX, lastY] = getPos(e); });
  canvas.addEventListener('touchmove', e => {
    e.preventDefault();
    if (!drawing) return;
    const [x, y] = getPos(e);
    ctx.beginPath(); ctx.moveTo(lastX, lastY); ctx.lineTo(x, y); ctx.stroke();
    [lastX, lastY] = [x, y];
  });
  canvas.addEventListener('touchend', () => drawing = false);

  document.getElementById('clearSig')?.addEventListener('click', () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
  });
})();

// ── Set today's date ─────────────────────────────────────────────────────────
(function () {
  var appDate = document.getElementById('appDate');
  if (appDate) appDate.value = new Date().toISOString().slice(0, 10);
})();

// ── Change pet link ──────────────────────────────────────────────────────────
document.getElementById('changePetLink')?.addEventListener('click', e => {
  e.preventDefault();
  window.location.href = 'dogs.html';
});

// ── Drag-drop file upload zones ───────────────────────────────────────────────
function initDropZone(zoneId, inputId, previewId, countId, errorId, maxFiles) {
  var zone = document.getElementById(zoneId);
  var input = document.getElementById(inputId);
  var preview = document.getElementById(previewId);
  var count = document.getElementById(countId);
  var err = errorId ? document.getElementById(errorId) : null;
  var files = [];

  function updatePreview() {
    preview.innerHTML = '';
    files.forEach(function(f, i) {
      var thumb = document.createElement('div');
      thumb.className = 'dz-thumb';
      if (f.type && f.type.startsWith('image/')) {
        var reader = new FileReader();
        reader.onload = function(e) {
          thumb.innerHTML = '<img src="' + e.target.result + '" alt=""><button type="button" class="dz-thumb-del" data-i="' + i + '">&times;</button>';
          thumb.querySelector('.dz-thumb-del').addEventListener('click', function(ev) { ev.stopPropagation(); files.splice(parseInt(this.dataset.i),1); updatePreview(); });
        };
        reader.readAsDataURL(f);
      } else {
        thumb.innerHTML = '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:11px;color:#7a6d5f;padding:4px;text-align:center;background:#f9f7f4;">' + f.name.split('.').pop().toUpperCase() + '</div><button type="button" class="dz-thumb-del" data-i="' + i + '">&times;</button>';
        thumb.querySelector('.dz-thumb-del').addEventListener('click', function(ev) { ev.stopPropagation(); files.splice(parseInt(this.dataset.i),1); updatePreview(); });
      }
      preview.appendChild(thumb);
    });
    count.textContent = files.length ? files.length + ' file' + (files.length > 1 ? 's' : '') + ' selected' : '';
    if (err) err.style.display = 'none';
  }

  zone.addEventListener('dragover', function(e) { e.preventDefault(); zone.classList.add('dragover'); });
  zone.addEventListener('dragleave', function() { zone.classList.remove('dragover'); });
  zone.addEventListener('drop', function(e) {
    e.preventDefault(); zone.classList.remove('dragover');
    var dropped = Array.from(e.dataTransfer.files);
    dropped.forEach(function(f) { if (files.length < maxFiles) files.push(f); });
    updatePreview();
  });
  input.addEventListener('change', function() {
    Array.from(input.files).forEach(function(f) { if (files.length < maxFiles) files.push(f); });
    input.value = '';
    updatePreview();
  });
  zone._getFiles = function() { return files; };
}

initDropZone('dzId', 'fileIdInput', 'dzIdPreview', 'dzIdCount', 'dzIdError', 5);
initDropZone('dzAddr', 'fileAddrInput', 'dzAddrPreview', 'dzAddrCount', 'dzAddrError', 5);
initDropZone('dzFund', 'fileFundInput', 'dzFundPreview', 'dzFundCount', null, 5);

// ── Validate documents step and proceed ───────────────────────────────────────
function validateDocsAndNext() {
  var idFiles = document.getElementById('dzId')._getFiles();
  var addrFiles = document.getElementById('dzAddr')._getFiles();
  var ok = true;
  if (idFiles.length < 2) {
    document.getElementById('dzIdError').style.display = 'block';
    ok = false;
  }
  if (addrFiles.length < 1) {
    document.getElementById('dzAddrError').style.display = 'block';
    ok = false;
  }
  if (!ok) { PC.toast('Please complete required document uploads before continuing.', 'error'); return; }
  goStep(5);
}

// ── Signature Save & Preview ──────────────────────────────────────────────────
document.getElementById('saveSig')?.addEventListener('click', function() {
  var canvas = document.getElementById('sigCanvas');
  var dataUrl = canvas.toDataURL('image/png');
  document.getElementById('signatureData').value = dataUrl;
  var box = document.getElementById('sigPreviewBox');
  box.innerHTML = '<img src="' + dataUrl + '" style="max-width:100%;max-height:100%;object-fit:contain;">';
  PC.toast('Signature saved!', 'success');
});

// ── Form Preview & Download ───────────────────────────────────────────────────
function previewForm() {
  var form = document.getElementById('adoptionForm');
  var data = Object.fromEntries(new FormData(form).entries());
  var sigData = document.getElementById('signatureData').value;
  var win = window.open('', '_blank');
  if (!win) { alert('Please allow popups to preview the form.'); return; }
  var rows = [
    ['Full Name', data.name], ['Email', data.user_email], ['Phone', data.phone],
    ['ID Number', data.id_number], ['Employment', data.employment_status],
    ['Address', (data.address||'') + ' ' + (data.apt||'') + ', ' + (data.city||'') + ', ' + (data.state||'') + ' ' + (data.zip||'')],
    ['Residence Type', data.residence_type], ['Home Type', data.home_type],
    ['Landlord Allows Pets', data.landlord_allows], ['Time at Address', data.time_at_address],
    ['Pet to Adopt', data.want_adopt], ['Primary Caregiver', data.primary_caregiver],
    ['Has Vet', data.have_vet], ['Can Afford Pet Care', data.can_afford],
    ['Pet Location', data.where_keep], ['Submission Date', data.submission_date || new Date().toISOString().slice(0,10)],
  ];
  var tableRows = rows.filter(function(r){return r[1];}).map(function(r){
    return '<tr><td style="font-weight:600;color:#10214b;padding:8px 12px;border-bottom:1px solid #f0ebe4;width:35%;">' + r[0] + '</td><td style="padding:8px 12px;border-bottom:1px solid #f0ebe4;color:#2c2416;">' + (r[1]||'') + '</td></tr>';
  }).join('');
  win.document.write('<!DOCTYPE html><html><head><title>Adoption Application Preview</title><style>body{font-family:Sora,sans-serif;max-width:860px;margin:32px auto;padding:0 24px;}h1{font-family:"Cormorant Garamond",serif;color:#10214b;}table{width:100%;border-collapse:collapse;margin-top:24px;}@media print{.no-print{display:none;}.print-btn{display:none;}}</style></head><body>');
  win.document.write('<div style="display:flex;align-items:center;gap:20px;margin-bottom:24px;padding-bottom:20px;border-bottom:2px solid #10214b;"><div><h1 style="margin:0;font-size:28px;">Pet Center — Adoption Application</h1><p style="margin:4px 0 0;color:#7a6d5f;font-size:14px;">Preview generated on ' + new Date().toLocaleDateString('en-US', {year:'numeric',month:'long',day:'numeric'}) + '</p></div></div>');
  win.document.write('<table>' + tableRows + '</table>');
  if (sigData) {
    win.document.write('<div style="margin-top:28px;"><h3 style="font-family:Cormorant Garamond,serif;color:#10214b;margin-bottom:12px;">Applicant Signature</h3><img src="' + sigData + '" style="max-width:300px;border:1px solid #e8e3de;border-radius:8px;padding:8px;"></div>');
  }
  win.document.write('<div class="no-print" style="margin-top:32px;display:flex;gap:12px;"><button onclick="window.print()" style="background:#10214b;color:#fff;border:none;padding:12px 28px;border-radius:8px;font-family:Sora,sans-serif;font-size:14px;font-weight:600;cursor:pointer;">Print / Save as PDF</button><button onclick="window.close()" style="background:#f3f4f6;color:#2c2416;border:1px solid #e5e7eb;padding:12px 28px;border-radius:8px;font-family:Sora,sans-serif;font-size:14px;cursor:pointer;">Close</button></div>');
  win.document.write('</body></html>');
  win.document.close();
}
