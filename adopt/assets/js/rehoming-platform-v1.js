/* <!-- TASK-MANUS-06 START --> */
/**
 * MANUS-06 rehoming feature engine.
 * The module keeps the new surfaces functional in the repository's static
 * deployment by using versioned localStorage records; server adapters can
 * replace the storage functions without changing the UI contracts.
 */
(function () {
  'use strict';

  // The canonical placement enum is shared by D1, E1, and E2.
  const STATUS = Object.freeze({
    ACTIVE: 'active',
    PENDING: 'pending_placement',
    COMPLETE: 'placement_complete',
    FELL_THROUGH: 'placement_fell_through',
    UNAVAILABLE: 'no_longer_available'
  });

  // The demo record includes the structured fields required by A1/F2.
  const DEFAULT_LISTING = {
    id: 'demo-pet-001', name: 'Milo', status: STATUS.ACTIVE, currency_code: 'USD',
    adoption_fee: 150, energy: 'medium', size: 'medium', temperament: 'gentle',
    compatible_kids: 'yes', compatible_dogs: 'yes', compatible_cats: 'unknown',
    country: 'US', merchant_id: 'MERCH-00001', donation_link: '', wishlist_link: ''
  };

  // All persisted records live under one namespaced key to keep the demo easy
  // to migrate into the platform's server-backed repositories later.
  const STORAGE_KEY = 'petcenter.rehoming.v1';
  const readStore = () => {
    try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}'); } catch (error) { return {}; }
  };
  const writeStore = (store) => localStorage.setItem(STORAGE_KEY, JSON.stringify(store));
  const store = () => {
    const value = readStore();
    return Object.assign({ listings: [DEFAULT_LISTING], notifications: [], checklist: {}, waitlist: [], alerts: [], favorites: [], tickets: [], testAccounts: [], reviews: [], preferences: { country: 'US', currency: 'USD', locale: 'en-us', consent: null, digestOptOut: false } }, value);
  };
  const uid = (prefix) => `${prefix}-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
  const escapeHtml = (value) => String(value == null ? '' : value).replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char]));
  const safeUrl = (value) => {
    if (typeof value !== 'string' || !value.trim() || /[\u0000-\u001f\u007f\\]/.test(value)) return '';
    const url = value.trim();
    if (url.startsWith('/') && !url.startsWith('//')) return url;
    try { const parsed = new URL(url); return parsed.protocol === 'https:' && !parsed.username && !parsed.password ? url : ''; } catch (error) { return ''; }
  };

  // The notification record shape is the published D1 cross-project contract.
  function makeNotification(data, recipientId, type, title, body, actionUrl, listingId) {
    const record = { id: uid('notif'), recipient_id: recipientId, type, title, body, action_url: safeUrl(actionUrl) || null, listing_id: listingId || null, created_at: new Date().toISOString(), dismissed_at: null };
    data.notifications.unshift(record); return record;
  }
  function addNotification(recipientId, type, title, body, actionUrl, listingId) {
    const data = store();
    const record = makeNotification(data, recipientId, type, title, body, actionUrl, listingId);
    writeStore(data); return record;
  }

  // D1 lifecycle transitions are explicit and reject illegal jumps.
  function transitionPlacement(listingId, nextStatus) {
    const data = store();
    const listing = data.listings.find((item) => item.id === listingId);
    if (!listing) throw new Error('Listing not found.');
    const allowed = { active: ['pending_placement'], pending_placement: ['placement_complete', 'placement_fell_through'], placement_fell_through: ['pending_placement'], placement_complete: [] };
    if (!allowed[listing.status] || !allowed[listing.status].includes(nextStatus)) throw new Error(`Cannot move ${listing.status} to ${nextStatus}.`);
    listing.status = nextStatus; listing.updated_at = new Date().toISOString();
    if (nextStatus === STATUS.COMPLETE) makeNotification(data, 'visitor-demo', 'placement_complete', 'Placement complete', `${listing.name} has been marked as safely placed.`, `/account/settings/`, listing.id);
    if (nextStatus === STATUS.FELL_THROUGH) makeNotification(data, 'visitor-demo', 'placement_fell_through', 'Placement needs a new home', `The placement for ${listing.name} fell through. Review the listing and waitlist options.`, `/rehoming/`, listing.id);
    writeStore(data); return listing;
  }

  // The checklist is informational and self-checked by each party.
  function toggleChecklist(listingId, itemId, checked) {
    const data = store(); data.checklist[`${listingId}:${itemId}`] = !!checked; writeStore(data); return data.checklist;
  }

  // Waitlist records remain private to the visitor and merchant.
  function joinWaitlist(listingId, visitorId) {
    const data = store();
    if (!data.waitlist.some((item) => item.listing_id === listingId && item.visitor_id === visitorId)) data.waitlist.push({ id: uid('wait'), listing_id: listingId, visitor_id: visitorId, created_at: new Date().toISOString(), notified_at: null });
    writeStore(data); return data.waitlist;
  }
  function notifyNextWaitlisted(listingId) {
    const data = store(); const next = data.waitlist.find((item) => item.listing_id === listingId && !item.notified_at);
    if (!next) return null; next.notified_at = new Date().toISOString(); makeNotification(data, next.visitor_id, 'waitlist_spot_open', 'A waitlist spot opened', 'The merchant has reopened this placement opportunity.', `/pets/detail/?pet_id=${encodeURIComponent(listingId)}`, listingId); writeStore(data); return next;
  }

  // F1 saves the exact filter object and produces a deterministic daily digest.
  function saveAlert(name, filters, visitorId) { const data = store(); const alert = { id: uid('alert'), name, filters, visitor_id: visitorId, created_at: new Date().toISOString(), last_run_at: null }; data.alerts.push(alert); writeStore(data); return alert; }
  function toggleFavorite(listingId, visitorId) { const data = store(); const index = data.favorites.findIndex((item) => item.listing_id === listingId && item.visitor_id === visitorId); if (index >= 0) data.favorites.splice(index, 1); else data.favorites.push({ listing_id: listingId, visitor_id: visitorId, created_at: new Date().toISOString() }); writeStore(data); return data.favorites; }
  function runDailyAlerts() { const data = store(); const matches = data.alerts.map((alert) => ({ alert, matches: data.listings.filter((listing) => Object.entries(alert.filters || {}).every(([key, value]) => !value || String(listing[key]).toLowerCase() === String(value).toLowerCase())) })); matches.forEach(({ alert, matches: found }) => { if (found.length) makeNotification(data, alert.visitor_id, 'pet_alert_digest', `${alert.name}: ${found.length} match${found.length === 1 ? '' : 'es'}`, `Your saved search found ${found.map((item) => item.name).join(', ')}.`, '/pets/', found[0].id); alert.last_run_at = new Date().toISOString(); }); writeStore(data); return matches; }

  // F2 uses a transparent point model instead of a black-box score.
  function scoreCompatibility(answers, listing) {
    let score = 0; let hardMismatch = false;
    if (answers.energy && listing.energy === answers.energy) score += 2;
    if (answers.home === 'apartment' && listing.size === 'large') { score -= 3; hardMismatch = true; } else if (answers.home) score += 1;
    if (answers.kids === 'yes' && listing.compatible_kids === 'no') { score -= 5; hardMismatch = true; } else if (answers.kids === 'yes' && listing.compatible_kids === 'yes') score += 2;
    if (answers.otherPets === 'yes' && listing.compatible_dogs === 'yes') score += 1;
    if (answers.experience === 'first-time' && listing.temperament === 'gentle') score += 2;
    return { score, label: hardMismatch || score <= 0 ? 'Less likely' : score >= 5 ? 'Good match' : 'Might work' };
  }

  // F3 builds a share URL and the weekly digest payload with an opt-out link.
  function buildShareUrl(listingId) { return `${location.origin}/pets/detail/?pet_id=${encodeURIComponent(listingId)}`; }
  function buildDigest(visitorId) { const data = store(); if (data.preferences.digestOptOut) return null; const favoriteIds = data.favorites.filter((item) => item.visitor_id === visitorId).map((item) => item.listing_id); return { subject: 'Your Pet Center weekly rehoming digest', listings: data.listings.filter((item) => favoriteIds.includes(item.id) || item.status === 'active'), optOutUrl: `${location.origin}/rehoming/?action=opt-out-digest&visitor=${encodeURIComponent(visitorId)}` }; }

  // E3 support tickets include account identity metadata in every record.
  function createSupportTicket(category, description, username, accountType) { const data = store(); const ticket = { id: uid('ticket'), category, description, username, account_type: accountType, status: 'open', created_at: new Date().toISOString() }; data.tickets.unshift(ticket); writeStore(data); return ticket; }
  // E5 test accounts expose a generated password once and retain only a hash.
  async function createTestAccount(type) { const data = store(); const username = `test-${type}-${Math.random().toString(36).slice(2, 8)}`; const password = `PC-${Math.random().toString(36).slice(2, 10)}!`; const bytes = new TextEncoder().encode(password); const digest = await crypto.subtle.digest('SHA-256', bytes); const hash = Array.from(new Uint8Array(digest)).map((byte) => byte.toString(16).padStart(2, '0')).join(''); const account = { id: uid('acct'), username, type, password_hash: hash, is_test_account: true, verification_status: type === 'merchant' ? 'verified' : null, created_at: new Date().toISOString() }; data.testAccounts.push(account); writeStore(data); return { account, one_time_password: password }; }
  function deleteTestAccount(accountId) { const data = store(); data.testAccounts = data.testAccounts.filter((item) => item.id !== accountId); data.reviews = data.reviews.filter((item) => item.account_id !== accountId); writeStore(data); }
  function importSeededReview(accountId, review) { const data = store(); const record = Object.assign({ id: uid('review'), account_id: accountId, is_seeded: true, created_at: new Date().toISOString() }, review); data.reviews.push(record); writeStore(data); return record; }

  // F5 links are grouped by country so the page can be reviewed without guessing.
  const RESOURCES = Object.freeze({
    US: { spay: 'https://www.animalleague.org/get-involved/spay-usa/', transport: 'https://flightangels.org/', crisis: 'https://www.safehavensforpets.org/' },
    CA: { spay: 'https://www.nationaldesexingnetwork.com.au/', transport: 'https://www.furryhobos.com/', crisis: 'https://redrover.org/additional-dv-resources/' },
    GB: { spay: 'https://www.dogstrust.org.uk/what-we-do', transport: 'https://www.dogstrust.org.uk/rehoming', crisis: 'https://www.rspca.org.uk/adviceandwelfare/pets/general' },
    AU: { spay: 'https://www.nationaldesexingnetwork.com.au/', transport: 'https://fly4paws.com/', crisis: 'https://www.rspca.org.au/' }
  });
  // F7 keeps locale prefixes and currency rendering together.
  const CURRENCY = Object.freeze({ USD: '$', CAD: 'CA$', GBP: '£', AUD: 'A$' });
  function setLocale(country) { const data = store(); const map = { US: ['en-us', 'USD'], CA: ['en-ca', 'CAD'], GB: ['en-gb', 'GBP'], AU: ['en-au', 'AUD'] }; const value = map[country] || map.US; data.preferences.country = country; data.preferences.locale = value[0]; data.preferences.currency = value[1]; writeStore(data); return value; }
  function formatFee(amount, currency) { return `${CURRENCY[currency] || '$'}${Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 0 })}`; }

  // G1 creates a checkout-link record; a live Stripe adapter can replace this hook.
  function createCheckoutLink(listingId, amount, currency) { const data = store(); const link = { id: uid('pay'), listing_id: listingId, amount: Number(amount), currency, url: `${location.origin}/rehoming/?checkout=${encodeURIComponent(listingId)}&amount=${encodeURIComponent(amount)}&currency=${encodeURIComponent(currency)}`, created_at: new Date().toISOString() }; data.checkoutLinks = data.checkoutLinks || []; data.checkoutLinks.push(link); writeStore(data); return link; }

  // F6 stores only the visitor's non-essential choice; essential session data is unaffected.
  function setConsent(choice) { const data = store(); data.preferences.consent = choice; writeStore(data); return choice; }

  // Expose the stable contract for page adapters and tests.
  window.PetCenterRehoming = { STATUS, store, writeStore, escapeHtml, safeUrl, addNotification, transitionPlacement, toggleChecklist, joinWaitlist, notifyNextWaitlisted, saveAlert, toggleFavorite, runDailyAlerts, scoreCompatibility, buildShareUrl, buildDigest, createSupportTicket, createTestAccount, deleteTestAccount, importSeededReview, RESOURCES, setLocale, formatFee, createCheckoutLink, setConsent };
})();
/* <!-- TASK-MANUS-06 END --> */
