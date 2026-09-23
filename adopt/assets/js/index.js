/* ════════════════════════════════════════════════════════════════
   Pet Center — index.js
   Page-specific JS for the homepage (index.html)
   Shared utilities live in app.js (loaded before this file)
   ════════════════════════════════════════════════════════════════ */

/* ════════════════════════════════════════════════════════════════
   STICKY HEADER — scroll behaviour (transparent → solid)
   ════════════════════════════════════════════════════════════════ */
(function () {
  var header = document.getElementById('pcHeader');
  if (!header) return;
  function onScroll() {
    if (window.scrollY > 60) header.classList.add('scrolled');
    else header.classList.remove('scrolled');
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
})();

/* ════════════════════════════════════════════════════════════════
   STATUS CHECKER
   ════════════════════════════════════════════════════════════════ */
function esc(s) {
  return String(s || '').replace(/[&<>"']/g, function(c){
    return({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]);
  });
}

function checkAdoptionStatus() {
  var input = document.getElementById('statusInput');
  var result = document.getElementById('statusResult');
  var code = (input.value || '').trim().toUpperCase();
  if (!code) {
    result.style.display = 'block';
    result.innerHTML = '<p style="color:#c0392b;font-family:Sora,sans-serif;font-size:14px;">Please enter your adoption code.</p>';
    return;
  }
  result.style.display = 'block';
  result.innerHTML = '<p style="font-family:Sora,sans-serif;font-size:14px;color:#7a6d5f;">Checking status...</p>';

  fetch('/api/adoption/' + encodeURIComponent(code))
    .then(function(r){ return r.json(); })
    .then(function(data) {
      if (!data.success) {
        result.innerHTML = '<div class="status-card rejected"><p style="font-family:Sora,sans-serif;font-size:14px;color:#c0392b;">' + esc(data.error || 'Code not found. Please check and try again.') + '</p></div>';
        return;
      }
      var html = '';
      if (data.status === 'PENDING') {
        html = '<div class="status-card pending">' +
          '<span class="status-badge badge-pending">Under Review</span>' +
          '<div class="status-pet-row"><div class="status-pet-info">' +
          '<h4>' + esc(data.pet_name || 'Your Pet') + '</h4>' +
          '<p>Applicant: ' + esc(data.applicant_name) + '</p>' +
          '<p>Code: ' + esc(data.adoption_code) + '</p>' +
          '</div></div>' +
          '<p style="font-family:Sora,sans-serif;font-size:14px;color:#7a6d5f;">Your application is under review. Please check back in 1–2 hours.</p>' +
          '</div>';
      } else if (data.status === 'APPROVED') {
        var imgHtml = data.pet_image_url
          ? '<img class="status-pet-img" src="' + esc(data.pet_image_url) + '" alt="' + esc(data.pet_name) + '" onerror="this.style.display=\'none\'">'
          : '';
        html = '<div class="status-card approved">' +
          '<span class="status-badge badge-approved">Approved</span>' +
          '<div class="status-pet-row">' + imgHtml +
          '<div class="status-pet-info">' +
          '<h4>' + esc(data.pet_name || 'Your Pet') + '</h4>' +
          '<p>Congratulations, ' + esc(data.applicant_name) + '!</p>' +
          '</div></div>' +
          '<div class="status-payment-box">' +
          '<h5>Your Flexible Payment Options</h5>' +
          '<ul>' +
          '<li><strong>BNPL (Buy Now Pay Later):</strong> Select "Apply for BNPL" during checkout in the card checkout option. It takes about 2–5 minutes to apply and is reviewed within 24 hours. Once approved, your pet is usually shipped or delivered locally the same day.</li>' +
          '<li><strong>Pay on Delivery:</strong> Adopt your pet and pay only when you receive them at home.</li>' +
          '<li><strong>Installment Plans (Pay in 4):</strong> Pay for your pet in 4 installment payments over the months using your PayPal account, or choose our in-house installment option for more flexibility. To set up either plan, contact customer support — they will help you configure it.</li>' +
          '</ul></div>' +
          (data.checkout_url ? '<a href="' + esc(data.checkout_url) + '" class="status-checkout-btn" target="_blank" rel="noopener"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>Proceed to Checkout</a>' : '') +
          '</div>';
      } else if (data.status === 'REJECTED') {
        html = '<div class="status-card rejected">' +
          '<span class="status-badge badge-rejected">Not Approved</span>' +
          '<p style="font-family:Sora,sans-serif;font-size:14px;color:#3d2817;margin-bottom:10px;">Your application for <strong>' + esc(data.pet_name) + '</strong> was not approved.</p>' +
          (data.rejection_notes ? '<p style="font-family:Sora,sans-serif;font-size:13px;color:#7a6d5f;margin-bottom:14px;"><strong>Reason:</strong> ' + esc(data.rejection_notes) + '</p>' : '') +
          '<a href="https://docs.petcenterco.com/contact-us" target="_blank" rel="noopener" style="font-family:Sora,sans-serif;font-size:13px;color:#ED7624;">Contact us for more information</a>' +
          '</div>';
      }
      result.innerHTML = html;
    })
    .catch(function() {
      result.innerHTML = '<div class="status-card rejected"><p style="font-family:Sora,sans-serif;font-size:14px;color:#c0392b;">Network error. Please try again.</p></div>';
    });
}

(function () {
  var statusInput = document.getElementById('statusInput');
  if (statusInput) {
    statusInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') checkAdoptionStatus();
    });
  }
})();

/* ════════════════════════════════════════════════════════════════
   MOST RECENT PETS — Load from all categories
   ════════════════════════════════════════════════════════════════ */
(function () {
  var CATEGORIES = ['dogs','cats','birds','reptiles','small-mammals'];
  var CAT_COLORS = {dogs:'',cats:'cat-btn',birds:'bird-btn',reptiles:'rep-btn','small-mammals':'sm-btn'};

  function fmt(n) { return '$' + Number(n||0).toLocaleString('en-US',{minimumFractionDigits:0}); }
  function fmtAge(n) {
    n = parseFloat(n);
    if (!n) return 'Age unknown';
    return n < 1 ? Math.round(n*12)+' mo' : n===1?'1 yr':n+' yrs';
  }

  function buildPetCard(pet) {
    var cat = pet.category || 'dogs';
    var btnClass = CAT_COLORS[cat] || '';
    var adopted = pet.status === 'Adopted';
    var imgUrl = pet.single_image_url || 'assets/images/pet-placeholder.svg';
    var tags = (pet.temperament || []).slice(0,3).map(function(t){
      return '<span class="pc-pet-card__tag">' + esc(t) + '</span>';
    }).join('');

    return '<a href="pet-detail.html?pet_id=' + esc(pet.pet_id) + '&category=' + esc(cat) + '" class="pc-pet-card">' +
      '<div class="pc-pet-card__img-wrap">' +
      '<img src="' + esc(imgUrl) + '" alt="' + esc(pet.name) + '" loading="lazy" onerror="this.src=\'assets/images/pet-placeholder.svg\'">' +
      (adopted ? '<span class="pc-pet-card__adopted-badge">Adopted</span>' : '') +
      '</div>' +
      '<div class="pc-pet-card__body">' +
      '<div class="pc-pet-card__category">' + esc(cat) + '</div>' +
      '<div class="pc-pet-card__name">' + esc(pet.name || 'Unknown') + '</div>' +
      '<div class="pc-pet-card__meta">' + esc(pet.breed||'') + (pet.breed&&pet.age_years?' &bull; ':'') + fmtAge(pet.age_years) + (pet.size?' &bull; '+esc(pet.size):'') + '</div>' +
      (tags ? '<div class="pc-pet-card__tags">' + tags + '</div>' : '') +
      '<div class="pc-pet-card__footer">' +
      '<span class="pc-pet-card__fee">' + fmt(pet.adoption_fee) + '</span>' +
      (!adopted ? '<a href="adoption-application.html?pet_id=' + esc(pet.pet_id) + '&category=' + esc(cat) + '" class="pc-pet-card__btn ' + btnClass + '" onclick="event.stopPropagation()">Adopt ' + esc((pet.name||'').split(' ')[0]) + '</a>' : '') +
      '</div></div></a>';
  }

  Promise.all(CATEGORIES.map(function(cat){
    return fetch('/api/pets/' + cat).then(function(r){ return r.json(); }).then(function(d){ return d.pets||[]; }).catch(function(){ return []; });
  })).then(function(results) {
    var all = [];
    results.forEach(function(pets){ all = all.concat(pets); });
    all.sort(function(a,b){ return new Date(b.created_at||0) - new Date(a.created_at||0); });
    var recent = all.slice(0,6);
    var grid = document.getElementById('recentPetsGrid');
    if (!grid) return;
    if (!recent.length) {
      grid.innerHTML = '<div class="pc-pets-empty"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#d0c3ba" stroke-width="1.5" style="margin:0 auto 12px;display:block;"><path d="M20.42 4.58a5.4 5.4 0 0 0-7.65 0l-.77.78-.77-.78a5.4 5.4 0 0 0-7.65 7.65l1.06 1.06L12 21.23l7.36-7.94 1.06-1.06a5.4 5.4 0 0 0 0-7.65z"/></svg><p style="font-family:Sora,sans-serif;color:#b0a69a;">No pets available right now. Check back soon!</p></div>';
      return;
    }
    grid.innerHTML = recent.map(buildPetCard).join('');
  });
})();

/* ════════════════════════════════════════════════════════════════
   BLOG CAROUSEL — prev/next
   ════════════════════════════════════════════════════════════════ */
(function() {
  var track = document.getElementById('blogTrack');
  var slides = track ? track.querySelectorAll('.pc-blog-slide') : [];
  var cur = 0;
  var total = slides.length;
  if (!total) return;
  function goTo(i) {
    cur = (i + total) % total;
    track.style.transform = 'translateX(-' + (cur * 100) + '%)';
  }
  document.getElementById('blogPrev') && document.getElementById('blogPrev').addEventListener('click', function(){ goTo(cur-1); });
  document.getElementById('blogNext') && document.getElementById('blogNext').addEventListener('click', function(){ goTo(cur+1); });
})();

/* ════════════════════════════════════════════════════════════════
   TESTIMONIALS CAROUSEL
   ════════════════════════════════════════════════════════════════ */
(function() {
  var TESTI = [];
  var cur = 0;
  var timer = null;
  var paused = false;
  var DELAY = 4500;

  function esc2(s) {
    return String(s||'').replace(/[&<>"']/g,function(c){return({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]);});
  }
  function stars(n) {
    var h = '';
    for (var i=1;i<=5;i++) h += '<span style="color:'+(i<=n?'#f59e0b':'#d1d5db')+'">&#9733;</span>';
    return h;
  }

  function render() {
    var track = document.getElementById('testiTrack');
    var dots = document.getElementById('testiDots');
    if (!track || !dots) return;
    track.innerHTML = '';
    dots.innerHTML = '';

    TESTI.forEach(function(t, i) {
      var replyHtml = '';
      if (t.reply && t.reply.name && t.reply.text) {
        var ri = (t.reply.name.split(' ').map(function(n){ return n[0]; }).join(''));
        replyHtml = '<div class="pc-testi-reply">' +
          '<div class="pc-testi-reply-header">' +
          '<div class="pc-testi-reply-avatar">' + esc2(ri) + '</div>' +
          '<div><div class="pc-testi-reply-name">' + esc2(t.reply.name) + '</div>' +
          '<div class="pc-testi-reply-role">' + esc2(t.reply.role||'') + '</div>' +
          '<div class="pc-testi-reply-date">' + esc2(t.reply.date||'') + '</div></div></div>' +
          '<div class="pc-testi-reply-text">' + esc2(t.reply.text) + '</div></div>';
      }

      var card = document.createElement('div');
      card.className = 'pc-testi-card';
      card.innerHTML = '<div class="pc-testi-card-inner">' +
        '<div class="pc-testi-top">' +
        '<div class="pc-testi-avatar" style="background:' + esc2(t.color) + '">' + esc2(t.initials) + '</div>' +
        '<div class="pc-testi-meta"><div class="pc-testi-name">' + esc2(t.name) + '</div>' +
        '<div class="pc-testi-date">' + esc2(t.date) + '</div></div>' +
        '<div class="pc-testi-stars">' + stars(t.rating) + '</div></div>' +
        '<div class="pc-testi-text">' + esc2(t.text) + '</div>' +
        replyHtml + '</div>';
      track.appendChild(card);

      var dot = document.createElement('button');
      dot.className = 'pc-testi-dot' + (i===0?' active':'');
      dot.setAttribute('aria-label', 'Go to testimonial '+(i+1));
      dot.addEventListener('click', function(){ goTo(i); pauseFor(8000); });
      dots.appendChild(dot);
    });
    goTo(0);
  }

  function goTo(i) {
    var track = document.getElementById('testiTrack');
    var dotEls = document.querySelectorAll('#testiDots .pc-testi-dot');
    if (!track || !TESTI.length) return;
    cur = ((i % TESTI.length) + TESTI.length) % TESTI.length;
    track.style.transform = 'translateX(-' + (cur * 100) + '%)';
    dotEls.forEach(function(d, idx){ d.classList.toggle('active', idx===cur); });
  }

  function pauseFor(ms) {
    paused = true;
    clearTimeout(window._testiResume);
    window._testiResume = setTimeout(function(){ paused = false; }, ms);
  }

  function startTimer() {
    if (timer) clearInterval(timer);
    timer = setInterval(function(){ if (!paused) goTo(cur+1); }, DELAY);
  }

  var wrap = document.getElementById('testiCarouselWrap');
  if (wrap) {
    wrap.addEventListener('mouseenter', function(){ paused = true; });
    wrap.addEventListener('mouseleave', function(){ paused = false; });
  }

  var prevBtn = document.getElementById('testiPrev');
  var nextBtn = document.getElementById('testiNext');
  if (prevBtn) prevBtn.addEventListener('click', function(){ goTo(cur-1); pauseFor(8000); });
  if (nextBtn) nextBtn.addEventListener('click', function(){ goTo(cur+1); pauseFor(8000); });

  fetch('/testimonials/testimonials.json')
    .then(function(r){ return r.json(); })
    .then(function(data) {
      TESTI = (data.testimonials || []).slice(0, 8);
      if (TESTI.length) { render(); startTimer(); }
    })
    .catch(function(e) { console.warn('Testimonials load error:', e); });
})();
