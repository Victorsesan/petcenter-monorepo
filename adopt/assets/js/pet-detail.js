/* ════════════════════════════════════════════════════════════════
   Pet Center — pet-detail.js
   Page-specific JS for the pet detail page (pet-detail.html)
   Shared utilities live in app.js (loaded before this file)
   ════════════════════════════════════════════════════════════════ */

function sharePet() {
  var url = window.location.href;
  if (navigator.share) {
    var petName = document.getElementById('petName') ? document.getElementById('petName').textContent : 'this pet';
    navigator.share({ title: petName + ' — Pet Center', url: url }).catch(function(){});
  } else if (navigator.clipboard) {
    navigator.clipboard.writeText(url).then(function() {
      var toast = document.getElementById('shareToast');
      if (toast) { toast.style.display = 'block'; setTimeout(function() { toast.style.display = 'none'; }, 2500); }
    });
  } else {
    var ta = document.createElement('textarea');
    ta.value = url;
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
    var toast = document.getElementById('shareToast');
    if (toast) { toast.style.display = 'block'; setTimeout(function() { toast.style.display = 'none'; }, 2500); }
  }
}

(async function () {
  const petId = PC.param('pet_id');
  const category = PC.param('category');

  const catLabels = { dogs:'Dogs', cats:'Cats', birds:'Birds', reptiles:'Reptiles', 'small-mammals':'Small Mammals' };
  const catPages = { dogs:'/pets/dogs/', cats:'/pets/cats/', birds:'/pets/birds/', reptiles:'/pets/reptiles/', 'small-mammals':'/pets/small-mammals/' };

  if (!petId || !category) {
    showError();
    return;
  }

  try {
    const data = await fetch(`/api/pets/${encodeURIComponent(category)}/${encodeURIComponent(petId)}`).then(r => r.json());
    if (!data.success || !data.pet) { showError(); return; }
    renderPet(data.pet);
  } catch (e) {
    showError();
  }

  function renderPet(p) {
    document.getElementById('petDetailLoading').style.display = 'none';
    document.getElementById('petDetailContent').style.display = 'block';
    document.title = `${p.name} — Pet Center`;
    document.getElementById('pageTitle').textContent = `${p.name} — Pet Center`;
    document.getElementById('bannerTitle').textContent = p.name;
    document.getElementById('crumbName').textContent = p.name;

    const catLabel = catLabels[p.category] || p.category;
    const catPage = catPages[p.category] || '/index.html';
    const crumbCatLink = document.getElementById('crumbCategoryLink');
    crumbCatLink.textContent = catLabel;
    crumbCatLink.href = catPage;

    document.getElementById('petCategory').textContent = catLabel;
    document.getElementById('petName').textContent = p.name;
    document.getElementById('petFee').textContent = PC.fmt(p.adoption_fee);
    document.getElementById('petBreed').textContent = p.breed || '—';
    document.getElementById('petAge').textContent = PC.age(p.age_years);
    document.getElementById('petSize').textContent = p.size || '—';
    document.getElementById('petHealth').textContent = p.health_info || 'Contact us for health details';
    document.getElementById('petId').textContent = p.pet_id;
    document.getElementById('petDescription').textContent = p.description_short || '';

    if (p.description_full) {
      document.getElementById('fullDescSection').style.display = 'block';
      document.getElementById('fullDescName').textContent = p.name;
      document.getElementById('fullDescription').textContent = p.description_full;
    }

    const statusEl = document.getElementById('petStatus');
    if (p.status === 'Adopted') {
      statusEl.innerHTML = '<span class="pc-badge pc-badge--adopted">Adopted</span>';
      document.getElementById('adoptBtn').style.display = 'none';
      document.getElementById('adoptedMsg').style.display = 'block';
      const morePetsLink = document.getElementById('morePetsLink');
      if (morePetsLink) morePetsLink.href = catPage;
    } else {
      statusEl.innerHTML = '<span class="pc-badge pc-badge--available">Available</span>';
      const applyBtn = document.getElementById('applyBtn');
      applyBtn.href = `/apply/?pet_id=${encodeURIComponent(p.pet_id)}&category=${encodeURIComponent(p.category)}`;
    }

    const mainImg = p.single_image_url || '/assets/images/pet-placeholder.svg';
    const galleryMain = document.getElementById('galleryMain');
    galleryMain.src = mainImg;
    galleryMain.alt = p.name;

    const thumbsEl = document.getElementById('galleryThumbs');
    const allImages = [mainImg, ...(p.bulk_images || [])].filter(Boolean);

    if (allImages.length > 1) {
      thumbsEl.innerHTML = allImages.map((src, i) =>
        `<div class="pc-gallery-thumb ${i === 0 ? 'active' : ''}" data-src="${PC.esc(src)}">
          <img src="${PC.esc(src)}" alt="" loading="lazy" onerror="this.parentElement.style.display='none'">
        </div>`
      ).join('');

      thumbsEl.querySelectorAll('.pc-gallery-thumb').forEach(thumb => {
        thumb.addEventListener('click', () => {
          galleryMain.src = thumb.dataset.src;
          thumbsEl.querySelectorAll('.pc-gallery-thumb').forEach(t => t.classList.remove('active'));
          thumb.classList.add('active');
        });
      });
    }
  }

  function showError() {
    document.getElementById('petDetailLoading').style.display = 'none';
    document.getElementById('petDetailError').style.display = 'block';
  }
})();
