

## Live navigation and header audit — 2026-11-10

**Account/worker ID:** `MANUS-05`

Added `tools/live-navigation-audit.py`, a read-only same-origin crawler that tests HTML link targets, repeated header links, HTTP status, redirects, page titles, and non-HTML navigation targets. The first live run crawled 42 pages and tested 42 same-origin targets. It found 8 unique HTTP 404 routes, appearing as 17 broken header-link occurrences: `/dogs.html`, `/cats.html`, `/birds.html`, `/reptiles.html`, `/small-mammals.html`, `/blog-home.html`, `/adoption-application.html`, and `/rehome/`.

These were stale legacy targets. The tracked site files now use the current clean routes: `/pets/dogs/`, `/pets/cats/`, `/pets/birds/`, `/pets/reptiles/`, `/pets/small-mammals/`, `/blog/`, `/apply/`, and `/signin/` respectively. The `/signin/` mapping follows the existing repository-wide decision to retire `/rehome/`; it does not claim that the signup backend is complete. The replacement covered 33 tracked HTML/JS/PHP files, including repeated headers, footer links, category detail variants, and the pet-detail JavaScript route map.

The signup gateway remains a separate major issue: `/signin/` is still a “Coming Soon” page and `/signup/` remains HTTP 404. That issue is owned by WEB-04 and DESIGN-02, not MANUS-05, and remains explicitly flagged for integrators.
