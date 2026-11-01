# Task 1 — Completion File

## Task Number
Task 1 — Project 3: Research, Brainstorm & Finalize New Page List

## What Was Required
Research what pages a mature, complete pet adoption site should have that
`adopt.petcenterco.com` currently lacks, cross-checked against
`adoption-website-project-summary.md` so nothing already built gets
duplicated; come up with at least one genuinely original page idea (not
common on comparable adoption sites); and produce a final, decided page
list — one purpose paragraph per page — plus a plan for how each new page
links into the site's existing navigation (header nav, mobile menu,
footer, and relevant in-page CTAs). No files were touched — this document
is the deliverable Task 2 builds from.

## Research Notes

Cross-checked against `adoption-website-project-summary.md` first. The
site already has: a homepage, the 5-step application flow + status
dashboard, 7 category listing pages + a browse-all page, individual pet
detail pages, testimonials/reviews, a blog (guides content), and a
**services page was already added** (Section 21) — so "a services page"
from this task's own starting examples is already built and is **not**
included below. `docs.petcenterco.com` already covers FAQ, returns,
privacy, shipping, and contact — so a standalone FAQ/contact page was
also not duplicated here.

**International check (Pet Center's target audience is the US, Canada,
UK, and Australia, not the US alone).** The first pass of this research
leaned on US sources. A second round specifically pulled UK (RSPCA,
Blue Cross), Australian (RSPCA Victoria, RSPCA Australia), and Canadian
(Nova Scotia SPCA, SPCA de l'Outaouais, Humane Canada) shelter sites to
confirm the page list holds up across all four markets rather than just
reflecting US norms. It does: every one of these organizations,
regardless of country, converges on the same core structure — **Foster,
Volunteer, Events, Lost & Found, and a Surrender/rehoming-alternatives
page** appear on all of them (RSPCA Victoria's own nav groups these
almost identically to what's proposed below). A few country-specific
patterns showed up too, worth noting even though they're not becoming
separate pages here: Canadian and Australian shelters commonly surface
**corporate-partnership and low-cost community/financial-assistance
programs** (Nova Scotia SPCA's "Pet Pantry," RSPCA Victoria's "low-cost
community programs") — these are folded into the Partners page and the
Can't Keep Your Pet? page below as sections, not spun into standalone
pages, since none of the four markets treat them as needing their own
URL. Nothing found in the UK/Canada/Australia research suggested a page
type this list was missing that the US-only pass hadn't already
surfaced.

Two pages came out of thinking through gaps in Pet Center's own funnel
rather than pure competitor-matching: a **Lost & Found** page (distinct
purpose from adoption listings, low build complexity, common at
comparable shelters in every market checked) and a **"Can't Keep Your
Pet?"** surrender-alternatives page (reduces intake pressure by offering
rehoming resources before someone surrenders, and pairs naturally as a
future cross-link target once Project 5's pet-match page exists — not a
build dependency either way).

**Correction — Sponsor a Pet is not the original idea.** The first pass
of this research proposed "Sponsor a Pet" (pick a specific listed pet,
get named on-profile credit, get a status-change email) as the required
original idea. A deeper, targeted search on that exact mechanic found
it's actually a well-established pattern already — Oregon Humane
Society, Heartland Humane, Rancho Coastal Humane Society, and Blue Cross
UK all run essentially this same feature today, several down to the
same "email when adopted/fostered/transferred" detail. Presenting it as
original would have been wrong, so it's kept in the page list below
(it's still a real, proven addition Pet Center doesn't have yet) but is
no longer labeled as the differentiator, and a genuinely different idea
replaces it — see page 9 below.

**The required original idea — "Gift My Adoption" registry.** What
research did surface as uncommon: sites that let a *specific applicant,
mid-application, for the specific pet they're already adopting* generate
a shareable link so friends/family can chip in toward *that* adoption's
actual costs (fee, starter kit, shipping) as a group gift — the kind of
thing usually done informally through a generic crowdfunding site (or
a birthday-fundraiser page that raises money for the org broadly, not
for one person's one pending adoption). Pet Center's stack already has
every piece this needs and nothing comparable was found combining them
this way: a real `adoption_code`/`application.json` per submission,
existing BNPL/installment payment infrastructure, and the multi-pet
carousel's per-pet fee display. See page 9 for the concrete shape.

**Considered and deliberately left off this list:** a standalone Careers
page. Confirmed with you directly — Pet Center doesn't run a hiring
pipeline, and every page on this list is scoped to pet-related purposes
only, so a jobs page (out of scope on both counts) is dropped, not just
deferred.

**Structured data:** Recommended Enhancement #4 (site-wide Schema.org
`AnimalShelter`/`Organization` markup) is not a page, so it isn't listed
below as one — it's a cross-cutting addition that costs nothing once
every new URL below is locked. Noted here as a recommendation for Task 2
to fold into its per-page build pass, not a separate task deliverable.

---

## Final Page List

### 1. Volunteer With Us — `/volunteer/`
Describes the different ways someone can help in person or remotely
(shelter shifts, adoption-event staffing, transport, administrative
support), states any requirements (background check, minimum commitment,
training), and ends in a sign-up form. Purpose: convert visitors who
aren't ready to adopt into ongoing hands-on support for Pet Center.

### 2. Foster Program — `/foster/`
Explains what fostering with Pet Center involves (who it's for, what's
provided vs. what the foster covers, typical duration, the foster-to-adopt
option), addresses common hesitations, and ends in an application/interest
form. Purpose: grow the foster network that keeps pets out of
overcrowded shelter conditions and gives harder-to-place animals a better
shot at adoption.

### 3. Events & Adoption Days — `/events/`
A calendar/list of upcoming in-person adoption events, community
meet-and-greets, and fundraisers, each with date, time, location, and a
short description; past-event photos build social proof over time.
Purpose: drive foot traffic to real-world events and keep the site from
being purely a browse-and-apply funnel.

### 4. About Us / Our Story — `/about/`
Pet Center's founding story, mission, the Beverly Hills location and
hours (already used elsewhere in the footer), and a brief team/leadership
section. Purpose: build the trust and legitimacy every comparable
high-quality shelter site leads with, currently missing entirely from
this site.

### 5. Vet & Rescue Partners — `/partners/`
Lists the veterinary clinics, spay/neuter partners, transport
organizations, and business sponsors Pet Center works with, plus a
simple "become a partner" contact path. Purpose: gives partner
organizations a public presence and signals the health/medical rigor
behind every listing (already referenced piecemeal in the 30-day health
guarantee messaging, never centralized).

### 6. Sponsor a Pet — `/sponsor/`
A sponsor browses currently-listed pets, picks one, sponsors it for a set
period at a chosen tier, gets named credit on that pet's detail page for
the duration, and receives one automatic email when that pet's status
changes. Purpose: a proven giving mechanism (confirmed common across all
four target markets in research, not this task's original idea — see
Research Notes) that Pet Center doesn't currently have.

### 7. Lost & Found Pets — `/lost-found/`
A simple two-column board — pets reported lost near Beverly Hills, and
pets found/turned in without an owner yet identified — each with a
photo, brief description, and a contact path; intentionally separate
from the adoption listings since these animals aren't up for adoption.
Purpose: gives the community a resource that gets displaced pets back to
their real owners instead of entering the adoption pipeline unnecessarily.

### 8. Can't Keep Your Pet? — `/rehoming-help/`
A resource page for someone considering surrendering a pet: alternatives
to consider first (temporary fostering through a friend/family, low-cost
training or behavior help, financial-assistance resources), what
Pet Center's own intake process involves if surrender is still the right
choice, and a contact form. Purpose: reduces avoidable surrenders by
meeting people at the actual decision point, a pattern that measurably
helps intake volume at comparable organizations.

### 9. Gift My Adoption — `/gift-my-adoption/` *(original idea)*
Once a visitor has an active `adoption_code` (from Section 4 of the
adoption-website summary — a pending application, not yet approved),
this page lets them generate a private, shareable link for that specific
pending adoption. Friends and family who open the link see the actual
pet (name, photo, fee) and can each contribute toward that one
adoption's real costs — fee, starter kit, shipping — through the same
payment infrastructure the application flow already supports (BNPL/
installment). Purpose: this is the genuinely original idea research
didn't find elsewhere — existing "sponsor a pet" and "fund someone's
adoption" patterns are always either shelter-directed (helping a
future, not-yet-known adopter) or a generic org-wide fundraising page;
nothing found lets one specific applicant crowdsource the actual cost of
their own already-chosen pet as a group gift, tied to a real adoption
code already in the system rather than a separate donation platform.
Depends on Task 5/6's pet-match and payment groundwork existing first at
the data-model level (the `adoption_code` this page hooks into) — not a
hard build dependency, but worth sequencing after those if a build order
is being set.

---

## Navigation Integration Plan

**Header nav (desktop, `.pc-header__nav`):** add a new "Get Involved"
dropdown (matching the existing "Our Pets" dropdown pattern already in
Section 2/2.6) containing Volunteer, Foster, Events, and Sponsor a Pet.
About Us and Partners join the existing top-level nav items directly (not
nested), since they're informational rather than action pages. Lost &
Found, Can't Keep Your Pet?, and Gift My Adoption are intentionally
**not** placed in the primary header nav — they're narrower-audience
utility/transactional pages reached at a specific moment (mid-application,
lost a pet, considering surrender) rather than general browsing — and are
reached via footer + in-page CTAs instead, keeping the primary nav from
getting crowded.

**Mobile hamburger menu (`#pcMobileMenu`):** same nine links, flattened
(no nested dropdown on mobile, consistent with how "Our Pets" already
flattens there), inserted after the existing "Our Pets" category links and
before "Testimonials."

**Footer (5-column `.pc-footer` grid):** Volunteer, Foster, Events, and
Sponsor a Pet join the existing "Our Pets" column (which currently links
category pages) or a new column position — deferred to whoever finalizes
Task 2's exact footer column contents, since squeezing four more links in
without breaking the 5-column grid (a documented past regression, Section
8) needs to be checked against actual rendered column height. About Us and
Partners join the existing "Information" column. Lost & Found and Can't
Keep Your Pet? join the existing "Support" column, alongside Contact Us
and FAQ. Gift My Adoption isn't a footer link at all — it's only ever
reached from inside an active application (see CTA below), not browsed to
cold.

**In-page CTAs (cross-links, not new nav structure):**
- `pet-detail.html` — add a small "Sponsor this pet" CTA near the existing
  "Apply to Adopt" action, linking to `/sponsor/?pet_id={id}` (mirrors the
  existing `?pet_id=` pattern the application form already uses, Section
  5.2 of the adoption-website summary — Task 2 should follow that same
  convention rather than inventing a new query-param scheme).
- The 7 category listing pages' existing "Signin" button area (Section
  15.5) — no change needed there, it already points at `/signin/`.
- Homepage "What's New" services carousel (Section 20.2) — a future slide
  could promote Volunteer/Foster/Events once built, but that's Task 2's
  call at build time, not locked here.
- `/rehoming-help/` and Project 5's pet-match page (once built) should
  cross-link each other once both exist, per the Recommended Enhancements
  note — not a dependency in either direction.
- `adoptions/status/index.html`'s PENDING state — add a "Gift This
  Adoption" CTA that links to `/gift-my-adoption/?code={adoption_code}`,
  since that's the exact moment (a real pending application already
  exists) where the page becomes relevant; it has no reason to appear
  anywhere before an adoption code exists.

## Files Changed
None — this task is planning/research only. No source files were touched.

## Bugs Found Outside Task Scope
None found.

## Self-Check Confirmation
No files were modified during this task; only this completion file was
created, matching Task 1's "Files touched: None" scope. The final page
list above is specific about page names, URLs (folder-path convention,
per Rule 5), and one-paragraph purpose statements per page, as required
for Task 2 to build from.
