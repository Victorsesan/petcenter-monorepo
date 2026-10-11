## PETCENTER DESIGN SYSTEM — OFFICIAL DOCUMENTATION

VERSION: 1.25
LAST UPDATED: August 7, 2026

👋 INTRODUCTION

I'm Claude, acting as UI Design Manager for PetCenter. I wrote and maintain this document, and I'm the one
you should treat as the source of authority on everything in it — not any single image, external draft, or
past conversation that contributed material here. Where outside content was reviewed and folded in, it was
checked against everything already established in this system first, corrected where it conflicted, and
flagged where something couldn't be resolved outright. Treat this document itself as the final word.

ABOUT PETCENTER

PetCenter is a pet store company operating a platform that connects pet owners, adopters, donors, sellers,
and administrators — covering everything from e-commerce and merchant storefronts to pet adoption
applications, donation campaigns, and internal admin tooling. The platform spans multiple distinct
audiences and moods (a checkout flow, a donation appeal, and an admin dashboard all need to feel different
from each other) while still reading, unmistakably, as one coherent brand.

WHAT THIS DOCUMENT IS

This is the single official design system for every PetCenter page, component, and UI element — present
and future, across every team and every worker building for this platform. It exists to make sure that no
matter who builds a page or when, the result is consistent, professional, and unmistakably on-brand. It
covers color, typography, spacing, components, animation, accessibility, page-specific theming, and the
reasoning behind all of it — not just the "what," but the "why," so decisions on new work stay consistent
with everything already built rather than reinventing the system piecemeal.

This document is alive — it grows and gets corrected as new material comes in. Version history for every
change is logged at the very end.

VISUAL REFERENCE COMPANION: A separate file, PetCenter_Design_System_Visual_Reference.html, renders a
live, interactive preview of this system's core visual elements — color palette, typography, button
hierarchy, loading animations, status badges/toasts, and a stepper, including a working dark mode toggle.
It is a companion, not a replacement — this written document remains the authoritative spec; the visual
file exists so a worker can SEE a section rather than only reading its CSS. Open it directly in a browser.

## ⚠️  READ BEFORE YOU START — THIS IS A LARGE DOCUMENT

This file is long and covers a lot of ground. Don't try to read it start to finish before beginning work —
use the Table of Contents directly below to jump straight to the section relevant to your task.

That said: do not skim past a section just because it isn't a component spec. Sections like The Creative
Process, Minimal Design Principles, and the UX Laws inside Status & Feedback Systems are not optional
color/font reading — they shape HOW you should approach any judgment call, free-choice decision, or
undocumented situation you run into. If your task touches on something covered by one of these sections,
treat it with the same weight as a locked visual spec, not as background reading you can skip.

### TABLE OF CONTENTS

**REQUIRED READING**
- [1. Rules & Warnings — Read This First](#1-rules-warnings-read-this-first) — every mandatory rule, warning, and free-choice option in one place

**CORE VISUAL FOUNDATION**
- [2. Color Palette System](#2-color-palette-system) — brand colors, functional colors, supporting colors
- [3. Typography System](#3-typography-system) — the 4-font locked system, hierarchy, mistakes to avoid

**UI COMPONENTS**
- [4. UI Loading Animations & Designs](#4-ui-loading-animations-designs) — 8 free-choice loading styles + decision guide
- [5. Modal & Popup System](#5-modal-popup-system) — shared modal base for confirmation, single-item preview, and full-content preview, all three uses
- [6. Form Styling & Input Components](#6-form-styling-input-components) — inputs, states, labels
- [7. File Upload Component](#7-file-upload-component) — drop zone, preview, accepted types

**SYSTEM STANDARDS**
- [8. Animation System & Transition Standards](#8-animation-system-transition-standards) — universal timing, durations, key animations
- [9. Spacing & Layout System](#9-spacing-layout-system) — spacing scale, 7 spacing principles, border radius scale
- [10. Responsive Design & Mobile Considerations](#10-responsive-design-mobile-considerations) — breakpoints, responsive rules
- [11. SVG Icons (No Emojis Rule)](#11-svg-icons-no-emojis-rule) — required icons, sizing, sources

**PROCESS, WORKFLOW & NAVIGATION PATTERNS**
- [12. Implementation Checklist & Build Workflow](#12-implementation-checklist-build-workflow) — how to build any new page start to finish
- [13. Mobile Menu & Navigation Patterns](#13-mobile-menu-navigation-patterns) — 8 menu styles + navigation icon glossary
- [14. The Creative Process — How to Approach Design Decisions](#14-the-creative-process-how-to-approach-design-decisions) — required reading for judgment calls
- [15. Layout Grid System (Desktop / Tablet / Mobile)](#15-layout-grid-system-desktop-tablet-mobile) — grid terminology, column/gutter specs
- [16. UX Steppers (Multi-Step Progress Indicators)](#16-ux-steppers-multi-step-progress-indicators) — 5 stepper styles + onboarding card pattern

**DESIGN PHILOSOPHY & STANDARDS**
- [17. Minimal Design Principles](#17-minimal-design-principles) — required reading, includes full Button Type Reference
- [18. Status & Feedback Systems](#18-status-feedback-systems) — status badges, toast notifications, UX Laws (required reading)

**ACCESSIBILITY & THEMING**
- [19. Dark Mode System](#19-dark-mode-system) — five dark mode rules, full dark palette, Settings Widget controls
- [20. Page-Specific Color Palettes (Thematic Accent Systems)](#20-page-specific-color-palettes-thematic-accent-systems) — donation, admin, and form palettes

**REFERENCE & SOURCING**
- [21. Support & Reference](#21-support-reference) — quick reference, common CSS/colors to copy
- [22. Sourcing Libraries by Use Case](#22-sourcing-libraries-by-use-case) — fonts, mockups, icons, stock images, and more
- [23. "Go Wild" Mode — Unrestricted Color Experimentation](#23-go-wild-mode-unrestricted-color-experimentation) — ⚠️ OPT-IN ONLY, CEO-authorized use only

**ADVANCED / LOCKED COMPONENTS**
- [24. Custom Dropdown System](#24-custom-dropdown-system) — locked shell, governing rules, 4 variants
- [25. Rich Text Editor (WYSIWYG) for Long-Form Fields](#25-rich-text-editor-wysiwyg-for-long-form-fields) — Quill.js component, approved toolbar

**SITE CHROME & REUSABLE FEATURES**
- [26. Site Header & Footer (Global Chrome)](#26-site-header-footer-global-chrome) — canonical header/footer markup, copy verbatim on every page
- [27. Hamburger Menu — Site-Wide Mandatory Implementation](#27-hamburger-menu-site-wide-mandatory-implementation) — the one real implementation used site-wide, not a free-choice pattern
- [28. Signature Pad Component](#28-signature-pad-component) — canvas draw-to-sign input
- [29. PDF Generation & Preview/Download Modal System](#29-pdf-generation-previewdownload-modal-system) — the two distinct preview features and the shared modal
- [30. Bottom Sheet, Filter Button & Search Bar](#30-bottom-sheet-filter-button-search-bar) — resolves the former "Sheet Panel" gap referenced in Section 24
- [31. Reading-Time Rotating Banner](#31-reading-time-rotating-banner) — word-count-based display timing for rotating tip/message cards
- [32. Compatibility Match Ring](#32-compatibility-match-ring) — animated SVG score ring for Yes/No/Unknown-style checklists

**DOCUMENT LOG**
- [33. Version History & Updates](#33-version-history-updates) — full changelog of every revision to this document

> **A note on these links:** they follow standard GitHub-Flavored-Markdown anchor conventions, which is the
> most widely supported format for clickable in-document navigation. Most standard Markdown viewers (GitHub,
> VS Code, and similar) will jump straight to the section when clicked. If a specific viewer doesn't support
> it, use Ctrl+F / Cmd+F with the section title as a reliable fallback.

## 1. RULES & WARNINGS — READ THIS FIRST

Every mandatory rule, critical warning, free-choice option, and open gap documented across this entire
system, condensed to one line each with a pointer to the full detail. Read this section before starting
any task — it takes a few minutes and prevents the most common mistakes made against this system.

### MANDATORY — NO EXCEPTIONS

├─ **Orange (#ED7624) must appear on every page/surface, always** — the one brand-anchor color with zero
   flexibility, even on themed pages that otherwise use a completely different palette. See Section 20.
├─ **All numerals/digits use Bitter, never Cormorant Garamond** — Section 3.
├─ **No emoji anywhere in product UI — SVG icons only** — Section 11.
├─ **Exactly four fonts, each with a locked role** — Cormorant Garamond, Sora, Bitter, Bodoni Moda. Never
   introduce a fifth. See Section 3.
├─ **Every number a user needs to read quickly uses the Bitter numeral treatment** — tied to the rule above.
├─ **Status badges and toasts use the exact 5-state / 4-type systems as written** — don't invent new status
   wording per feature. See Section 18.
├─ **Dropdown and Rich Text Editor shells are locked** — trigger/panel structure, animation, and governing
   caps must not be altered; only the content inside is flexible. See Sections 24 and 25.
└─ **The site header, footer, and hamburger menu use one exact implementation, copied verbatim on every
   page** — never reimplemented or varied per-page. See Sections 26 and 27.

### CRITICAL WARNINGS

├─ **Never fill a secondary/Cancel button with Orange next to a Navy or Orange primary button** — both are
   strong saturated brand colors and will compete for attention. See Section 17.
├─ **Never ship an onboarding card with a progress indicator and no explicit Next/Skip control** — a
   progress dot row alone is ambiguous about how to advance. See Section 16.
└─ **A full z-index hierarchy does not yet exist** — only Modal (9999), Dropdown (999), and the Bottom
   Sheet system (backdrop 1400, sheet 1500 — Section 30) are documented. Don't assume ordering for
   anything else without checking with Design first.

### COLORS ARE NOT ALWAYS RIGID — READ BEFORE ASSUMING OTHERWISE

The core brand palette (Section 2) and the page-specific palettes (Section 20) are the default and should
be used as documented for anything they already cover. But they are not the only colors that will ever
exist on this platform:

├─ Where a project genuinely needs something outside what's documented (a new page type with no existing
   themed palette, for example), workers may adapt colors — but any new choice must be grounded in the
   color theory and distribution reasoning already taught in this system (Section 23's color theory
   lessons, and the reasoning behind each palette in Section 20), not picked arbitrarily
├─ Different page types legitimately call for different palettes — a donation page, an admin dashboard, and
   an adoption form already use three different palette families in this document precisely because they
   serve different moods. That precedent extends to future page types not yet covered
└─ The ONE constant across every single variation: **Orange (#ED7624) still appears no matter what.** That
   rule from the Mandatory list above does not bend for any of this.

### FREE-CHOICE GALLERIES (pick one, use good judgment — not a fixed assignment)

├─ Loading animations — 8 styles, Section 4
├─ Mobile menu styles — 8 styles, Section 13
├─ UX Steppers — 5 styles, Section 16
├─ Donation/emotional page palette — Romantic Blend OR Magenta Bloom, Section 20
├─ Adoption/application form palette — 3 options, Section 20
└─ Dropdown variants — 4 patterns inside the locked shell, Section 24

### OPT-IN-ONLY — DO NOT USE WITHOUT EXPLICIT AUTHORIZATION

└─ **"Go Wild" Mode (Section 23)** overrides the entire locked color system. It is off by default and only
   applies when the CEO explicitly authorizes it for a specific task, in those words. A creative-sounding
   request is never, on its own, authorization.

### OPEN GAPS — FLAG TO DESIGN, DON'T GUESS

├─ Card and Pet Selector components are referenced (Section 12) but not yet documented anywhere
├─ The Settings Widget itself (Section 19) is not yet fully specified beyond its dark mode and color vision
   controls
└─ Dropdown and Rich Text Editor accessibility (keyboard/ARIA behavior) are flagged as underspecified in
   both Sections 24 and 25 — verify before shipping either on a real form

## 2. COLOR PALETTE SYSTEM

> **Visual reference available:** open the companion HTML file's "Colors" tab to see every core, functional,
> and page-specific palette rendered live. These are samples, not final assets — adjust to your specific
> project, and verify exact hex values against this written spec.

🎨 PRIMARY PETCENTER COLORS (MUST ALWAYS USE):

1. NAVY BLUE: #10214b
   ├─ Usage: Primary buttons, borders, text, section numbers, primary UI elements
   ├─ Variants: Light Navy (#1a2f5c) for gradients
   ├─ Represents: Trust, professionalism, stability
   ├─ Background: Works on light/white backgrounds
   └─ When to Use: Headers, form labels, primary CTAs, focus states

2. GOLD / ACCENT: #d7bd88
   ├─ Usage: Hover states, focus glows, input borders on focus, premium accents
   ├─ Variants: Dark Gold (#b89968) for hover effects
   ├─ Represents: Elegance, premium quality, luxury
   ├─ Background: Works on light backgrounds as accents
   └─ When to Use: Hover borders, focus box-shadows, accent highlights

3. WARM GRAY: #d0c3ba
   ├─ Text Color On This: Chocolate (#3d2817)
   ├─ Represents: Warmth, sophistication
   └─ Purpose: Makes text headers stand out with premium

4. CHOCOLATE: #3d2817
   ├─ Usage: Text color on warm gray (#d0c3ba) backgrounds
   ├─ Represents: Warm, earthy, natural
   ├─ Contrast: High contrast on #d0c3ba background
   └─ When to Use: Text on warm gray, some special buttons.

5. CREAM: #ebe7e1
   ├─ Usage: Very light backgrounds, subtle highlights
   ├─ Represents: Lightness, approachability
   └─ When to Use: Rare - mainly for special background variations

6. ORANGE: #ED7624
   ├─ Represents: Brand identity, use only in the most highly recommend minimal places to keep brand identity alive like animations, some buttons etc.

FUNCTIONAL COLORS:

7. Success Green: #10b981
   ├─ Usage: Success messages, confirmation states
   └─ Example: " Saved successfully"

8. Error Red: #ef4444
   ├─ Usage: Error messages, delete buttons hover
   └─ Example: " Error: Please fill all fields"

9. Warning Amber: #f59e0b
   ├─ Usage: Warning states, pending/on-hold indicators, non-destructive caution messages
   ├─ Note: Deliberately distinct in hue from brand Orange (#ED7624) — Orange is reserved for primary CTAs
      (Section 17); Warning Amber must never be substituted with brand Orange or vice versa
   └─ Example: "This action will affect 3 listings" (paired with an SVG warning-triangle icon per Section 11
      — never a text symbol character in place of the icon)

10. Info Blue: #3b82f6
    ├─ Usage: Informational messages, neutral status indicators, "in progress" states
    └─ Example: "Your changes are syncing" (paired with an SVG info-circle icon per Section 11)

SUPPORTING COLORS:

11. Muted Text: #7a6d5f
    ├─ Usage: Secondary text, placeholders, helper text
    └─ Contrast: Lower contrast than primary text

12. Primary Text: #2c2416
    ├─ Usage: Body text, form labels, main content
    └─ Contrast: High contrast on light backgrounds

13. Background: #f9f7f4
    ├─ Usage: Page background (light cream/beige)
    └─ Warmth: Slightly warm to complement the color palette

14. Surface: #ffffff
    ├─ Usage: Form sections, cards, modal boxes
    └─ Effect: Clean, professional appearance

15. Border Color: rgba(16,33,75,0.12)
    ├─ Usage: Input borders, form section borders, dividers
    ├─ Transparency: 12% opacity Navy on light background
    └─ Subtlety: Professional, not harsh

## 3. TYPOGRAPHY SYSTEM

> **Visual reference available:** the "Typography" tab in the companion HTML file renders every font role below live. These are samples, not final assets — adjust to your specific project.

> **See also:** this section already includes its own "Seven Typography Mistakes to Avoid" further down. For how type restraint fits the bigger picture, see Section 17's Typography Restraint. For numeral-specific rules, see Section 1's Mandatory list.

🔤 FONT FAMILIES (Must Import):

@import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@600;700&family=Bitter:wght@400;500;600;700&family=Bodoni+Moda:wght@600;700&display=swap');

FONT 1: CORMORANT GARAMOND (Serif - Display)
├─ Used For: Page titles (H1), section headers (H2), hero text
├─ Style: Elegant, sophisticated, premium
├─ Weights: 600 (semi-bold), 700 (bold)
├─ Line Height: 1.1
├─ Letter Spacing: -0.5px to -1px
├─ Color: Navy (#10214b)
├─ Size Examples:
│  ├─ H1 (Page Title): 54px (or clamp(32px, 5vw, 54px))
│  ├─ H2 (Section): 20px
│  └─ H3 (Subsection): 18px

Usage Examples:
<!-- Page Title -->
<h1 style="font-family: 'Cormorant Garamond'; font-size: 54px; font-weight: 700;">
  Create Your Storefront
</h1>

<!-- Section Header -->
<h2 style="font-family: 'Cormorant Garamond'; font-size: 20px; font-weight: 700;">
  Merchant Information
</h2>

FONT 2: SORA (Sans-Serif - Body)
├─ Used For: Body text, labels, form text, buttons, UI text
├─ Style: Modern, clean, friendly, readable
├─ Weights: 300 (light), 400 (regular), 500 (medium), 600 (semi-bold), 700 (bold)
├─ Line Height: 1.6 for body, 1.4 for labels
├─ Letter Spacing: 0.04em for uppercase labels
├─ Color: Primary text (#2c2416) or Navy (#10214b)
├─ Size Examples:
│  ├─ Body Text: 14-16px
│  ├─ Form Labels: 13px uppercase
│  ├─ Placeholder: 14px
│  ├─ Helper Text: 12px
│  └─ Button Text: 15-16px

Usage Examples:
<!-- Form Label -->
<label style="font-family: 'Sora'; font-size: 13px; font-weight: 600; letter-spacing: 0.04em;">
  Full Name
</label>

<!-- Body Text -->
<p style="font-family: 'Sora'; font-size: 16px; line-height: 1.7;">
  Enter your full name as it appears on your business license.
</p>

<!-- Button Text -->
<button style="font-family: 'Sora'; font-size: 16px; font-weight: 700;">
  Publish My Storefront
</button>

FONT 3: BITTER (Slab Serif — Numerals & Functional Data)
├─ Used For: ALL numeral/digit display sitewide, plus specific functional UI listed below
├─ Style: Sturdy, highly legible slab serif — built for numbers in a way Cormorant Garamond is not
├─ Weights: 400 (regular), 500 (medium), 600 (semi-bold), 700 (bold)
├─ Color: Navy (#10214b) or Deep Plum/Ice Blue depending on themed page (Section 20)
├─ Why it exists: Cormorant Garamond's numerals are decorative and hard to scan at a glance — this was
│  flagged as an open problem earlier in this document. Bitter solves it. From this version forward, any
│  number the user needs to read quickly and accurately uses Bitter, full stop.
├─ MANDATORY use for numerals:
│  ├─ Prices and monetary amounts (donation totals, checkout totals, plan pricing)
│  ├─ Stats and KPI figures (dashboard cards, donation progress "$3,240 raised", order counts)
│  ├─ Timestamps, dates, and phone numbers
│  ├─ Badge counters (notification counts, cart item counts)
│  └─ Table/data-grid numeric columns (admin dashboards, order tables, inventory counts)
├─ ADDITIONAL discretionary uses (Design's call — apply these too):
│  ├─ Section/step numbers in numbered UI (e.g. "01 / 02 / 03" markers, onboarding step indicators)
│  ├─ Invoice and receipt-style layouts, where its sturdy, ledger-like character fits the content
│  └─ Data table row content generally in admin/dashboard contexts (pairs naturally with Section 20's
│     Lavender Mist palette), even for non-numeric cells, for visual consistency within a data table
├─ FALLBACK ROLE: If Cormorant Garamond fails to load, Bitter is the fallback for headings (not Sora) —
│  as a serif, it preserves more of the intended elegant feel than jumping straight to a sans-serif
└─ Do NOT use Bitter for body paragraphs, buttons, or form labels — that territory stays with Sora

Usage Examples:
<!-- Donation Total -->
<span style="font-family: 'Bitter'; font-weight: 600; font-size: 28px; color: var(--primary);">
  $3,240
</span>

<!-- Dashboard KPI -->
<div style="font-family: 'Bitter'; font-weight: 700; font-size: 32px;">1,204</div>

<!-- Section Number Marker -->
<span style="font-family: 'Bitter'; font-weight: 600; font-size: 13px;">01</span>

FONT 4: BODONI MODA (Serif — Editorial Accent, OPTIONAL)
├─ Used For: Occasional heading swap-in on long-form, writing-heavy pages ONLY — blog post titles,
│  magazine-style feature articles, "long read" adoption stories
├─ Style: High-contrast, dramatic, fashion-editorial — reads as more luxurious and more intense than
│  Cormorant Garamond
├─ Weights: 600 (semi-bold), 700 (bold)
├─ Color: Navy (#10214b), or Deep Plum on Romantic Blend pages (Section 20)
├─ Status: OPTIONAL. This is not a default — Cormorant Garamond remains the default heading font
│  everywhere, including most blog/editorial pages. Bodoni Moda is a deliberate, occasional swap-in when
│  a specific piece of content benefits from extra luxury weight
├─ Where to use it (Design's call):
│  ├─ Blog post H1 titles, when the post is a feature/flagship piece rather than routine content
│  ├─ Long-form adoption success stories or rescue narratives with substantial body text
│  └─ Any "magazine layout" page that is mostly text and benefits from an editorial feel
├─ Where NOT to use it: forms, dashboards, checkout flows, modals, buttons, navigation, or any
│  functional/transactional UI — reserve it for writing-heavy content or it loses its impact
└─ Rule of thumb: if the page's job is to be read like an article, Bodoni Moda is fair game; if the
   page's job is to get a task done, stick with Cormorant Garamond

Usage Example:
<!-- Blog Feature Headline (optional treatment) -->
<h1 style="font-family: 'Bodoni Moda'; font-size: 48px; font-weight: 700; color: var(--primary);">
  How One Shelter Saved 200 Dogs This Year
</h1>

FONT 5: DM SANS (Fallback)
├─ If Sora fails to load, use DM Sans
├─ Similar characteristics to Sora
├─ Ensure this font is in Google Fonts import as backup

TYPOGRAPHY HIERARCHY:

Level 1 (H1 - Page Title):
├─ Font: Cormorant Garamond 700
├─ Size: 54px (responsive: clamp(32px, 5vw, 54px))
├─ Color: Navy
├─ Line Height: 1.1
└─ Example: "PetCenter Merchant Portal"

Level 2 (H2 - Section Header):
├─ Font: Cormorant Garamond 700
├─ Size: 20px
├─ Color: Navy
├─ Line Height: 1.2
└─ Example: "Merchant Information"

Level 3 (H3 - Subsection):
├─ Font: Cormorant Garamond 700
├─ Size: 18px
├─ Color: Navy
└─ Example: "Edit Your Storefront"

Body Text:
├─ Font: Sora 400
├─ Size: 16px
├─ Color: Primary text (#2c2416)
├─ Line Height: 1.7
└─ Max Width: 520px for readability

Form Labels:
├─ Font: Sora 600
├─ Size: 13px
├─ Text Transform: uppercase
├─ Letter Spacing: 0.04em
├─ Color: Navy
└─ Example: "FULL NAME"

Form Input Text:
├─ Font: Sora 400
├─ Size: 14px
├─ Color: Navy
└─ Placeholder: Muted (#7a6d5f)

Button Text:
├─ Font: Sora 700
├─ Size: 15-16px
├─ Color: White
├─ Transform: None (mixed case okay)
└─ Example: "Publish My Storefront"

Helper/Secondary Text:
├─ Font: Sora 400
├─ Size: 12-13px
├─ Color: Muted (#7a6d5f)
└─ Example: "(used to match relevant customer reviews)"

Numerals & Data Display (MANDATORY for all numbers):
├─ Font: Bitter 600 (700 for large stat callouts)
├─ Size: Matches the context it sits in (14px in a table cell, 32-48px for a hero stat)
├─ Color: Navy, or the themed page's dark accent per Section 20
└─ Example: "$3,240 raised" · "1,204 orders" · "01"

Editorial Heading (OPTIONAL — long-form content pages only):
├─ Font: Bodoni Moda 700
├─ Size: Matches standard H1/H2 sizing for the page
├─ Color: Navy, or Deep Plum on Romantic Blend pages
└─ Example: A flagship blog post title — never a form, dashboard, or checkout heading

✅ RESOLVED: Numerals now use Bitter sitewide (see Font 3 above) — Cormorant Garamond must never be used
for digits/numbers under any circumstance.

### SEVEN TYPOGRAPHY MISTAKES TO AVOID

These are the specific failure patterns that quietly make typography look amateur even when the right
fonts are in use. Check new work against this list before it ships.

MISTAKE 1 — Weak Hierarchy
├─ The Problem: If every text element is close to the same size and weight, the eye doesn't know what to
   read first — a person ends up scanning randomly instead of being guided
└─ The Fix: Use clear, deliberate size AND weight contrast between hierarchy levels — this is exactly why
   Section 3's hierarchy above defines an explicit size for every text role. Never let two adjacent
   elements (e.g. an H2 and its body text) sit close enough in size/weight to be ambiguous

MISTAKE 2 — Poor Line Length
├─ The Problem: Lines that are too narrow force the eye to jump lines constantly; lines that are too wide
   make it easy to lose your place returning to the next line
└─ The Fix: Aim for 45-75 characters per line for body copy. This is why Body Text above caps at a 520px
   max-width, and why the Grid System (Section 15) intentionally keeps the Form max-width at 820px rather
   than letting body text stretch to a full page-width column

MISTAKE 3 — Bad Line Spacing
├─ The Problem: Line height set too tight (under ~120%) makes paragraphs feel cramped and hard to track
   line-to-line; too loose and lines feel disconnected from each other
└─ The Fix: Start around 120%-150% line height for body copy. Our system already sits inside this range
   (Body Text: 1.7 / 170% — generous for our serif+sans mix; Form Labels/Input: 1.4-1.6). Do not override
   these values with a tighter custom line-height when reusing these text styles

MISTAKE 4 — Random Font Pairing
├─ The Problem: Combining fonts with no deliberate contrast or shared logic between them makes a page feel
   visually inconsistent, like it was assembled from different sources
└─ The Fix: This is structurally prevented in our system — PetCenter uses exactly four fonts, each with a
   locked role (Cormorant Garamond for display headings, Sora for body/UI, Bitter for numerals, Bodoni
   Moda as an optional editorial accent — see Fonts 1-4 above). Never introduce a fifth font or use one of
   these four outside its defined role just because it "looks fine" in one spot

MISTAKE 5 — Inconsistent Spacing
├─ The Problem: Using different, arbitrary spacing values around similar text elements from section to
   section makes a page feel unplanned even if no single value looks wrong in isolation
└─ The Fix: Always pull spacing values from the Spacing Scale (Section 9), never eyeball a new number —
   this is the same principle as Spacing Rule 1. Two headings of the same hierarchy level should always
   have identical spacing above/below them, page after page

MISTAKE 6 — Overusing Bold
├─ The Problem: When a bold headline sits above a bold subheading and a bold-bordered CTA button, nothing
   stands out — bold loses its power the moment everything is bold
└─ The Fix: Pair contrast with structure. Let ONE element per section carry the bold weight and heaviest
   visual pull (usually the heading); keep supporting text, borders, and secondary elements at a lighter
   weight so the bold element still reads as the most important thing on screen

MISTAKE 7 — Ignoring Readability Context
├─ The Problem: A type scale tuned for a large desktop screen often overwhelms a small mobile screen, and
   a scale tuned for mobile can look undersized and thin on desktop — testing on only one device size
   misses this every time
└─ The Fix: Always test typography at real screen sizes across all three breakpoints (Section 10) before
   shipping — never judge type scale from a zoomed-out canvas. This is the same discipline as Spacing
   Rule 7 (test at actual screen size), applied specifically to type

## 4. UI LOADING ANIMATIONS & DESIGNS

> **Visual reference available:** the "Loading" tab in the companion HTML file runs all 8 styles live, plus the Skeleton Screen pattern. These are samples, not final assets — adjust to your specific project.

> **See also:** for content-heavy pages loading for the first time, Section 18's Doherty Threshold Law covers when to use a Skeleton Screen instead of one of the 8 animations below.

⚙️ AVAILABLE LOADING ANIMATIONS — 8 STYLES

🟢 FREE CHOICE: Any of the 8 styles below may be used per component/context. Match the style to the job
using the "Best For" line and the Decision Guide at the end of this section — don't pick at random just to
add variety on a single page; stay consistent within one flow (e.g. don't mix Spinner and Pulse on the
same form).

DESIGN 1: SPINNER (Plain)
└─ What: A circular ring with one solid-color arc segment rotating around a faint full track
├─ Duration: 0.8s
├─ Easing: linear (constant speed reads as "working," not "settling")
├─ Color: Navy (#10214b) arc on a 15% Navy track
├─ Smoothness: Clean, neutral, universally recognizable
├─ Use Case: Small inline loaders — next to an icon, inside a small button, table row loading state
├─ CSS Animation:
   .spinner {
     width: 20px; height: 20px;
     border: 2.5px solid rgba(16,33,75,0.15);
     border-top-color: #10214b;
     border-radius: 50%;
     animation: spin 0.8s linear infinite;
   }
   @keyframes spin { to { transform: rotate(360deg); } }
└─ Best For: Compact inline loaders, small icon-adjacent states, table/list row loading

DESIGN 2: SPINNER GRADIENT
└─ What: A circular spinner with a gradient stroke sweeping around the ring rather than a flat color
├─ Duration: 1.5s
├─ Easing: linear
├─ Color: Conic gradient, Navy (#10214b) to Gold (#d7bd88)
├─ Smoothness: Premium, more visually rich than the Plain Spinner
├─ Use Case: Full-page loading states, larger loading overlays where the extra visual richness earns its
   place
├─ CSS Animation:
   .spinner-gradient {
     width: 40px; height: 40px;
     border-radius: 50%;
     background: conic-gradient(from 0deg, #10214b, #d7bd88, transparent);
     -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 4px), #000 calc(100% - 4px));
     mask: radial-gradient(farthest-side, transparent calc(100% - 4px), #000 calc(100% - 4px));
     animation: spin 1.5s linear infinite;
   }
└─ Best For: Full-page/full-section loading overlays, initial dashboard load, premium moments (checkout
   confirmation processing)

DESIGN 3: SQUARES
└─ What: Two small squares that alternate scale and opacity, creating a soft morphing/breathing effect
   between them
├─ Duration: 1.2s
├─ Easing: ease-in-out
├─ Color: Muted gray square paired with a Navy or Gold square
├─ Movement: Squares alternate between full and reduced scale, offset in timing so one is always
   prominent while the other recedes
├─ CSS Animation:
   @keyframes squareMorph {
     0%, 100% { transform: scale(1); opacity: 1; }
     50% { transform: scale(0.6); opacity: 0.4; }
   }
   .square-a { animation: squareMorph 1.2s ease-in-out infinite; }
   .square-b { animation: squareMorph 1.2s ease-in-out infinite 0.6s; }
└─ Best For: Alternative/playful UI moments, empty-state loaders, secondary or decorative loading contexts

DESIGN 4: PULSE
└─ What: A single dot that scales up while fading out, like a radar ping, repeating continuously
├─ Duration: 1s
├─ Easing: ease-out
├─ Color: Gold (#d7bd88) or Orange (#ED7624) depending on urgency of the context
├─ Effect: Soft, ambient — reads as "something is happening in the background" rather than "actively
   working"
├─ CSS Animation:
   @keyframes pulse {
     0% { transform: scale(0.8); opacity: 1; }
     100% { transform: scale(2); opacity: 0; }
   }
   .pulse-dot { animation: pulse 1s ease-out infinite; }
└─ Best For: Subtle background indicators, live/active status dots, "syncing" indicators that shouldn't
   demand full attention

DESIGN 5: SPINNER DOTS
└─ What: 8-12 small dots arranged in a circle, each fading in and out sequentially around the ring —
   creates a rotating illusion without anything actually rotating
├─ Duration: 1s for one full cycle around the ring
├─ Easing: ease-in-out per dot, staggered
├─ Color: Navy dots fading to 20% opacity at their dimmest point
├─ Smoothness: Very refined, feels native/platform-standard (similar to iOS activity indicators)
├─ CSS Animation:
   @keyframes dotFade {
     0%, 100% { opacity: 0.15; }
     50% { opacity: 1; }
   }
   .dot { animation: dotFade 1s ease-in-out infinite; }
   /* stagger each of the 8-12 dots with animation-delay in ~0.08s increments */
└─ Best For: Native-feeling mobile loaders, splash/launch screens, anywhere a soft platform-standard feel
   is preferred over a bold branded one

DESIGN 6: HALF CIRCLES
└─ What: Two semi-circle arcs — one Navy, one Gold — rotating in opposite directions around the same
   center point, crossing and overlapping as they spin
├─ Duration: 1.4s
├─ Easing: linear
├─ Color: Navy (#10214b) arc + Gold (#d7bd88) arc
├─ Smoothness: Dynamic and distinctly "PetCenter" since it's the only style built from both brand colors
   moving independently
├─ CSS Animation:
   @keyframes spinCW { to { transform: rotate(360deg); } }
   @keyframes spinCCW { to { transform: rotate(-360deg); } }
   .arc-navy { border-top-color: #10214b; animation: spinCW 1.4s linear infinite; }
   .arc-gold { border-bottom-color: #d7bd88; animation: spinCCW 1.4s linear infinite; }
└─ Best For: A signature/branded full-page loader — use where the loading moment itself is worth making
   memorable (e.g. initial app load, storefront publish confirmation)

DESIGN 7: LOAD BAR
└─ What: Horizontal sliding rectangle animation
├─ Duration: 1.2s
├─ Easing: ease-in-out
├─ Direction: Left to right (0 → 40px translateX)
├─ Opacity: Pulses slightly (1 → 0.5 → 1)
├─ Color: White bar on dark/colored button background
├─ Smoothness: Very professional, elegant
├─ Use Case: Button loaders, submission progress
├─ CSS Animation:
   @keyframes slideLoad {
     0% { transform: translateX(0); opacity: 1; }
     50% { opacity: 0.5; }
     100% { transform: translateX(40px); opacity: 1; }
   }
├─ HTML Implementation:
   <svg viewBox="0 0 60 4" xmlns="http://www.w3.org/2000/svg">
     <rect x="0" y="1" width="20" height="2" fill="#ffffff"
       style="animation:slideLoad 1.2s ease-in-out infinite;"/>
   </svg>
└─ Best For: Publish buttons, save buttons, form submissions

DESIGN 8: THREE DOTS
└─ What: Three dots in a horizontal row, bouncing or fading up-and-down in a staggered wave sequence
├─ Duration: 1.2s
├─ Easing: ease-in-out per dot, staggered ~0.15s apart
├─ Color: Navy or Muted (#7a6d5f), matching the surrounding text color
├─ Effect: Casual, friendly — the classic "typing indicator" feel
├─ CSS Animation:
   @keyframes dotBounce {
     0%, 80%, 100% { transform: translateY(0); opacity: 0.4; }
     40% { transform: translateY(-4px); opacity: 1; }
   }
   .dot:nth-child(1) { animation: dotBounce 1.2s ease-in-out infinite; }
   .dot:nth-child(2) { animation: dotBounce 1.2s ease-in-out infinite 0.15s; }
   .dot:nth-child(3) { animation: dotBounce 1.2s ease-in-out infinite 0.3s; }
└─ Best For: Casual/conversational contexts — chat or messaging loaders, comment submission, anywhere a
   lighter, less formal tone fits

DECISION GUIDE — WHICH LOADING STYLE TO USE:
├─ Inside a button, submitting a form? → Load Bar (Design 7)
├─ Small inline loader next to an icon or in a table row? → Spinner (Design 1)
├─ Full-page or full-section loading overlay? → Spinner Gradient (Design 2) or Half Circles (Design 6)
│  for a more branded/memorable moment
├─ Background/ambient "something is syncing" indicator? → Pulse (Design 4)
├─ Native/platform-standard mobile feel? → Spinner Dots (Design 5)
├─ Casual, conversational context (chat, comments)? → Three Dots (Design 8)
└─ Playful or secondary/decorative loading moment? → Squares (Design 3)

WHEN TO SHOW LOADING ANIMATION:
├─ Form submission (publish, save changes)
├─ File upload (not after)
├─ Authentication attempts
├─ Data synchronization
└─ Any async operation (3+ seconds expected)

LOADING ANIMATION TIMING:
├─ Show: Immediately on button click
├─ Duration: Until server responds (3-30 seconds)
├─ Hide: When response received
├─ Button State: Disabled during loading
└─ Button Text: "Publishing..." or "Saving..." or "Loading..."

## 5. MODAL & POPUP SYSTEM

> **See also:** Section 8 for this system's universal transition timing, Section 18's Toast Notifications
> for a lighter-weight feedback pattern than a full modal, Section 28 for the Signature Pad component that
> commonly sits inside a form using this modal system, and Section 29 for the two specific PDF/file-preview
> features built on top of this base.
>
> **This section replaces an earlier glassmorphism-styled version of this documentation.** The glassmorphic,
> blurred-backdrop treatment previously written here does not match any real, currently deployed modal on
> the site. What follows is transcribed directly from a real, currently deployed page and is the accurate,
> current, mandatory base for any new modal — solid backgrounds, no `backdrop-filter`, instant open/close
> with no scale/bounce animation. Do not reintroduce the blurred-glass treatment without checking with
> Design first; it was likely an early concept that was superseded before ever shipping.

**Where this is implemented:** the adoption application form (three separate modal instances share this one
base — see below). Reusable anywhere a page needs a centered popup: confirmations, previews, success states,
alerts, or any other overlay content.

### One base structure, three real uses

This is **one shared modal shell**, reused for three different purposes just by changing what's rendered
inside it and which optional pieces are shown. Do not build a separate modal system per use case — extend
this one.

├─ **A. Success / confirmation modal** — shown after a successful action (e.g. a form submission). Centered
   icon, heading, a prominent piece of data (like a confirmation code), an optional warning callout, and one
   primary action button.
├─ **B. Single-item preview modal** — shown when previewing one specific thing already on the page (e.g. an
   uploaded file). Title bar shows that item's name; no secondary "download/generate" action.
└─ **C. Full-content preview modal** — shown when previewing an entire larger body of content (e.g. a whole
   filled-in form before submission), with an additional primary action in the header (e.g. "Download PDF").

B and C literally share the same DOM element and get their title/content/button visibility swapped by JS
depending on which one triggered the open — see the JS pattern below. **Any change to this shared modal's
CSS must be verified against all three uses**, not just the one currently being edited — this exact
component has regressed before by giving use B the full header treatment meant only for use C, or by
restructuring the box in a way that silently broke use A's ability to scroll on a short mobile screen.

### CSS — shared shell

```css
.modal {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.65);   /* solid dark overlay — no blur */
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 16px;
}
.modal.open { display: flex; }    /* instant show/hide — no fade or scale animation */

.modal-box {
  background: #ffffff;
  border-radius: 12px;
  padding: 0;
  max-width: 600px;
  width: 100%;
  max-height: 85vh;
  max-height: 85dvh;              /* dvh override where supported */
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
  display: flex;
  flex-direction: column;
}

@media (max-width: 600px) {
  .modal { padding: 12px; }
  .modal-box { max-height: 88vh; max-height: 88dvh; max-width: 100%; }
}
@media (max-width: 400px) {
  .modal { padding: 8px; }
}
```

**Deliberately no open/close animation.** The overlay and box appear and disappear instantly via the
`.open` class toggle. This is a real, intentional simplification versus an earlier fade/scale concept — it
keeps the modal feeling immediate rather than precious, appropriate for a form flow where a user is opening
and closing preview modals repeatedly. Don't add a `transition`/`@keyframes` entrance animation to `.modal`
or `.modal-box` without confirming this is actually wanted — it's not the current standard.

### CSS — the two-zone layout for uses B and C (title bar + separately-scrolling body)

This is the important structural pattern: the header (title, optional action button, Close) is **sticky and
never scrolls**; only the content below it scrolls. This is what lets a long preview scroll internally
without ever losing access to the Close button.

```css
.modal-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 14px 18px;
  border-bottom: 1px solid var(--border);
  flex-shrink: 0;
  background: #ffffff;
}
.modal-top__actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }

.modal-scroll {
  flex: 1;
  overflow-y: auto;
  padding: 20px 20px 24px;
  overscroll-behavior: contain;
  -webkit-overflow-scrolling: touch;
}

.modal-close {
  padding: 6px 12px;
  background: #f0ede8;
  border: 1px solid var(--border);
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
  font-family: 'Sora', sans-serif;
  font-weight: 500;
  color: var(--text);
  transition: all 0.2s;
  flex-shrink: 0;
}
.modal-close:hover { background: var(--border); color: var(--primary); }

.modal-content { margin-top: 0; }
.modal-content img { max-width: 100%; height: auto; border-radius: 8px; margin-bottom: 12px; }
.modal-content iframe { width: 100%; height: 600px; border: 1px solid var(--border); border-radius: 8px; }
```

### CSS — use A (success/confirmation modal variant)

Use A does **not** use the `.modal-top`/`.modal-scroll` split — a success modal is short enough that it
doesn't usually need internal scrolling, but on a very short mobile viewport it still might, so it applies
`overflow-y: auto` directly to `.modal-box` instead:

```css
.success-modal {
  text-align: center;
  padding: 24px;
  overflow-y: auto;
  overscroll-behavior: contain;
  -webkit-overflow-scrolling: touch;
}
.success-icon {
  width: 64px; height: 64px;
  background: rgba(13,124,74,0.12);   /* success-green tint */
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 20px;
}
.success-code {
  font-family: 'Cormorant Garamond', serif;
  font-size: 28px; font-weight: 700;
  color: var(--primary);
  margin: 16px 0; padding: 14px;
  background: #f9f7f4;
  border-radius: 8px;
  word-break: break-all;
  letter-spacing: 1px;
}
.success-warning {
  background: #fff8e7;
  border: 1.5px solid rgba(237,118,36,0.2);
  border-radius: 8px;
  padding: 16px; margin: 20px 0;
  font-size: 13.5px; line-height: 1.7;
  color: #6b4d2a;
  text-align: left;
}
.success-warning strong { display: block; color: #c67c1a; margin-bottom: 8px; font-size: 14px; font-weight: 700; }
.success-modal .btn { margin-top: 20px; }
```

⚠️ **Why `.success-modal` can't just reuse `.modal-top`/`.modal-scroll`:** `.modal-box` sets
`overflow: hidden`, which is what makes the sticky-header/scrolling-body split work for uses B/C. Applying
that same `overflow: hidden` to a *centered, non-split* content block like the success modal would clip its
own primary button on a short screen — that's a real regression that happened once already. If a modal
doesn't need the sticky-header pattern, give its own root element `overflow-y: auto` directly rather than
inheriting `.modal-box`'s `overflow: hidden` unexamined.

### Canonical markup — all three uses

```html
<!-- USE A: Success / confirmation -->
<div id="successModal" class="modal">
  <div class="modal-box success-modal">
    <div class="success-icon">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#0d7c4a" stroke-width="2.5"
           stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
    </div>
    <h2 style="font-family:'Cormorant Garamond',serif;font-size:24px;color:var(--primary);margin-bottom:8px;">
      Success heading here
    </h2>
    <p style="color:var(--muted);margin-bottom:16px;">One-line confirmation of what just happened.</p>

    <div class="success-code" id="successCode">CODE-00000-0000</div>
    <button type="button" class="small-btn" id="copyCodeBtn">Copy Code</button>

    <div class="success-warning">
      <strong>Important callout heading</strong>
      Any important follow-up information the user needs to know goes here.
    </div>

    <button type="button" class="btn btn--primary" id="primaryActionBtn">Primary Action</button>
  </div>
</div>

<!-- USE B & C: shared preview modal -->
<div id="previewModal" class="modal">
  <div class="modal-box">
    <div class="modal-top" id="modalTop">
      <div id="modalTopTitle" style="font-family:'Cormorant Garamond',serif;font-size:1.2rem;color:var(--orange);margin:0;"></div>
      <div class="modal-top__actions">
        <!-- Only shown for use C — a secondary action tied to the full content, e.g. Download PDF -->
        <button type="button" class="btn btn--primary" id="modalActionBtn" style="padding:7px 14px;font-size:13px;display:none;">
          Action
        </button>
        <button class="modal-close" id="modalClose">Close</button>
      </div>
    </div>
    <div class="modal-scroll">
      <div id="modalContent" class="modal-content"></div>
    </div>
  </div>
</div>
```

### JS — the shared open/close + the B/C title-and-button swap

```js
const modal = document.getElementById('previewModal');
const modalContent = document.getElementById('modalContent');
const modalClose = document.getElementById('modalClose');
const modalTopTitle = document.getElementById('modalTopTitle');
const modalActionBtn = document.getElementById('modalActionBtn');

// USE B — previewing one specific existing item (e.g. an uploaded file)
function openItemPreview(item) {
  modalContent.innerHTML = '';
  modalTopTitle.textContent = item.name || 'Preview';
  modalActionBtn.style.display = 'none';   // no secondary action for a single-item preview

  if (item.type.startsWith('image/')) {
    const img = document.createElement('img');
    img.src = item.url; img.alt = item.name;
    modalContent.appendChild(img);
  } else {
    const fr = document.createElement('iframe');
    fr.src = item.url;
    modalContent.appendChild(fr);
  }
  modal.classList.add('open');
}

// USE C — previewing the full larger content, with a secondary action available
function openFullPreview(renderedContentHtml, actionLabel, onAction) {
  modalContent.innerHTML = '';
  modalTopTitle.textContent = 'Preview';
  modalActionBtn.style.display = '';
  modalActionBtn.textContent = actionLabel;
  if (!modalActionBtn.dataset.wired) {          // wire the click handler only once
    modalActionBtn.addEventListener('click', () => onAction());
    modalActionBtn.dataset.wired = '1';
  }

  const wrap = document.createElement('div');
  wrap.innerHTML = renderedContentHtml;
  modalContent.appendChild(wrap);
  modal.classList.add('open');
}

modalClose.addEventListener('click', () => {
  modal.classList.remove('open');
  modalContent.innerHTML = '';
  // Reset title and button so the next open (of either use B or C) starts clean
  modalTopTitle.textContent = '';
  modalActionBtn.style.display = 'none';
});
```

**Why reset on close, not on open:** clearing `modalContent`, the title, and the action button's visibility
in the close handler (rather than only setting them fresh at the top of each open function) guarantees the
modal never briefly flashes stale content from whichever use last opened it, even if a future addition opens
it through some path that forgets to reset one of these fields.

### Accessibility

├─ `z-index: 9999` keeps the modal above all other page content — see Section 1's z-index note for how this
   relates to the Bottom Sheet system's z-index range (1400/1500 — modals stay clearly above sheets).
├─ Overlay click-to-close and Escape-to-close are both common, low-cost additions **not present in the
   base implementation above** — add them per-modal if the use case calls for it, they're not baked into
   the shared shell.
└─ Close button is always present and always reachable, since it lives in the sticky, non-scrolling header
   (uses B/C) or at the natural end of the scrollable content (use A).

## 6. FORM STYLING & INPUT COMPONENTS

> **See also:** Section 24's Custom Dropdown System and Section 25's Rich Text Editor are specialized form inputs built on top of this section's base label/field styling — read this section first.

📝 TEXT INPUT FIELDS:

Standard Input Styling:

.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.field label {
  font-size: 13px;
  font-weight: 600;
  color: var(--text);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.field label .opt {
  color: var(--muted);
  font-weight: 400;
  font-size: 12px;
  text-transform: none;
  letter-spacing: 0;
}

.field input,
.field textarea {
  background: #ffffff;
  border: 1.5px solid var(--border);              /* Navy semi-transparent */
  border-radius: 9px;
  padding: 11px 14px;
  font-size: 14px;
  color: var(--text);
  font-family: inherit;
  outline: none;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.field input:focus,
.field textarea:focus {
  border-color: var(--accent);                    /* Gold border on focus */
  background: #fafaf8;
  box-shadow: 0 0 0 3px rgba(215, 189, 136, 0.15); /* Gold glow */
}

.field input::placeholder,
.field textarea::placeholder {
  color: var(--muted);
}

.field textarea {
  resize: vertical;
  min-height: 78px;
}

INPUT STATES:

Default:
├─ Background: White
├─ Border: Navy 1.5px (subtle)
├─ Text: Navy
└─ Placeholder: Muted gray

Hover (optional, not required):
├─ Background: Same white
├─ Border: Same navy
└─ Cursor: text

Focus (CRITICAL - visible on interaction):
├─ Background: Light cream (#fafaf8)
├─ Border: Gold 1.5px (bright)
├─ Box-shadow: Gold glow (3px radius)
└─ Visual feedback: Clear and obvious

Disabled:
├─ Opacity: 0.5
├─ Cursor: not-allowed
└─ Border: Same navy (faded)

Error (for validation):
├─ Border: Red 1.5px
├─ Background: Light red tint optional
└─ Message below field in red

## 7. FILE UPLOAD COMPONENT

> **See also:** Section 4's Loading Animations for other in-progress states, Section 5 for the Preview modal
> a file's "Preview" button opens (use B specifically), Section 6 for general form field styling this
> component stays visually consistent with, and Section 28 for the separate Signature Pad component — a
> signature is never handled through this file-upload component, it's a distinct base64-in-a-hidden-field
> pattern.
>
> **This section replaces an earlier, incomplete version of this documentation.** The version previously
> here covered only the drop-zone's idle/hover styling and a generic feature bullet list; it didn't document
> the upload-on-select behavior, the four real per-file status states, retry, or the actual colors in use.
> What follows is transcribed directly from a real, currently deployed multi-file upload implementation and
> is the accurate, current, mandatory base for any new file-upload field — not scoped to any one form. Drop
> it into any page that needs to collect one or more files (documents, photos, attachments — anything).

### What this component does, in one paragraph

A click-or-drag drop zone accepts one or more files per field, immediately previews each one in a list below
the zone, and **starts uploading each file to the server the moment it's added** — not on form submit. By
the time a user finishes filling out the rest of a longer form and hits Submit, their files are typically
already sitting on the server, so submission itself feels closer to instant. Each file in the list shows a
live status (uploading / uploaded / failed, with retry) and buttons to preview or remove it.

**See it live in:** `adoption-application.html` (Government ID, Proof of Address, and Proof of Funding
fields — three separate instances of this same component on one page).

### CSS — drop zone

```css
.file-drop {
  padding: 12px;
  border-radius: 8px;
  border: 2px dashed #eee;
  background: linear-gradient(180deg, #fff, #fffaf6);
  cursor: pointer;
  text-align: center;
  margin-top: 10px;
  font-size: 14px;
  color: var(--muted);
  transition: border-color 0.2s, background 0.2s;
}
.file-drop.dragover {
  border-color: var(--orange);
  background: linear-gradient(180deg, #fff8f0, #fffefb);
}
```

The idle state is a **near-neutral light gray dash** (`#eee`), not a colored/tinted one — the orange brand
accent is reserved for the active `dragover` state, so it reads clearly as "something is happening" rather
than being the zone's constant resting appearance.

### CSS — uploaded file list

```css
.uploaded-list { margin-top: 8px; display: flex; flex-direction: column; gap: 8px; }

.uploaded-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px;
  background: #fff;
  border-radius: 6px;
  border: 1px solid #f1f5f9;
}
.uploaded-item img {
  width: 64px; height: 64px;
  object-fit: cover;
  border-radius: 6px;
  border: 1px solid #ddd;
}

.small-btn {
  background: #f3f4f6;
  border-radius: 6px;
  padding: 6px 8px;
  border: 1px solid #e5e7eb;
  color: #111;
  cursor: pointer;
  font-size: 13px;
  font-family: 'Sora', sans-serif;
  transition: background 0.2s;
}
.small-btn:hover { background: #e5e7eb; }
```

### Markup — one drop zone + list per field

```html
<div class="form-group">
  <label>Field label — required *</label>
  <div id="drop_FIELDKEY" class="file-drop" tabindex="0">Click or drag files here (max 5)</div>
  <div id="list_FIELDKEY" class="uploaded-list"></div>
</div>
```

`tabindex="0"` on the drop zone plus a keydown handler for Enter/Space (below) is what makes the zone
keyboard-operable — it's a plain `<div>`, not a native `<input>` or `<button>`, so this isn't automatic.

### Constants — copy these, adjust per field's actual needs

```js
const maxFiles = 5;
const maxBytes = 5 * 1024 * 1024;   // 5MB per file
const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
```

Adjust `allowedTypes` and `maxBytes` to whatever the specific field actually needs — these three constants
are the field's real contract; don't accept broader types than the field is meant for.

### JS — wiring click, drag/drop, and keyboard for one field

```js
const fld = { arr: [], drop: document.getElementById('drop_FIELDKEY'), list: document.getElementById('list_FIELDKEY') };

const inp = document.createElement('input');
inp.type = 'file';
inp.accept = '.jpg,.jpeg,.png,.pdf';
inp.multiple = true;
inp.style.display = 'none';
document.body.appendChild(inp);

fld.drop.addEventListener('click', () => inp.click());
fld.drop.addEventListener('keydown', e => {
  if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); inp.click(); }
});
['dragenter', 'dragover'].forEach(ev =>
  fld.drop.addEventListener(ev, e => { e.preventDefault(); fld.drop.classList.add('dragover'); })
);
['dragleave', 'drop', 'dragend'].forEach(ev =>
  fld.drop.addEventListener(ev, e => { e.preventDefault(); fld.drop.classList.remove('dragover'); })
);
fld.drop.addEventListener('drop', e => {
  const fl = e.dataTransfer && e.dataTransfer.files ? Array.from(e.dataTransfer.files) : [];
  handleFiles(fld, fl);
});
inp.addEventListener('change', e => {
  const fl = e.target.files ? Array.from(e.target.files) : [];
  handleFiles(fld, fl);
  inp.value = '';   // lets the same file be re-selected later (e.g. after Remove) and still fire 'change'
});
```

### JS — validating and queuing a batch of files

```js
async function handleFiles(fld, fileList) {
  for (const f of fileList) {
    if (fld.arr.length >= maxFiles) { alert('Max ' + maxFiles + ' files allowed.'); break; }
    if (f.size > maxBytes) { alert('File "' + f.name + '" too large. Max 5MB.'); continue; }
    if (!allowedTypes.includes(f.type)) { alert('File "' + f.name + '" type not allowed.'); continue; }
    const url = URL.createObjectURL(f);
    const item = { file: f, url, name: f.name, size: f.size, type: f.type, uploading: false, uploaded: false, token: null };
    fld.arr.push(item);
    renderField(fld);
    preUploadFile(fld, item);   // fire-and-forget — don't await; the form stays usable while this runs
  }
}
```

`alert()` for the three validation failures (too many files, too large, wrong type) is what the real
implementation uses — a plain browser alert, not a styled toast. This is a deliberate, acceptable choice
for validation the user needs to notice and dismiss immediately; it's not meant to be silently swapped for
a toast without a reason.

### JS — the pre-upload-on-select behavior

Each file starts uploading to the server the instant it's added, independent of the rest of the form. This
is the component's core distinguishing behavior:

```js
async function preUploadFile(fld, item) {
  item.uploading = true;
  item.uploaded = false;
  item.token = null;
  renderField(fld);

  try {
    const fd = new FormData();
    fd.append('file', item.file, item.name);
    const res = await fetch('/your-temp-upload-endpoint.php', { method: 'POST', body: fd });

    if (!res.ok) {
      item.uploadError = 'Server error (' + res.status + ')';
      item.uploading = false;
      renderField(fld);
      return;
    }

    const json = await res.json();   // wrap in try/catch in production — a non-JSON response
                                       // (e.g. a PHP warning printed before the JSON) must not
                                       // throw uncaught; surface it as item.uploadError instead
    if (json && json.token) {
      item.token = json.token;
      item.uploaded = true;
    } else {
      item.uploadError = json.message || 'Upload failed';
    }
  } catch (e) {
    item.uploadError = 'Network error during upload';
  }
  item.uploading = false;
  renderField(fld);
}
```

**Why a token, not the final file record:** the endpoint this posts to should be a *temporary* upload
handler that stores the file and hands back an opaque reference (`token`) — not the field it ultimately
belongs to in whatever record the form is building. The form's real submit step later sends the collected
tokens along with the rest of the form data, and only then does the backend associate each token's file
with its permanent destination. This is what allows files to upload in the background *before* the rest of
the form (which the token doesn't yet belong to) has even been submitted.

### Per-file status states — the four real states and their exact styling

Each uploaded-item row shows exactly one of these, right after the file size:

```js
let statusHtml = '';
if (it.uploaded) {
  statusHtml = ' <span style="color:#0d7c4a;font-weight:600;display:inline-flex;align-items:center;gap:3px;">' +
    '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" ' +
    'stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Uploaded</span>';
} else if (it.uploading) {
  statusHtml = ' <span style="color:#ED7624;display:inline-flex;align-items:center;gap:3px;">' +
    '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" ' +
    'stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12" y2="5"/>' +
    '<polyline points="5 12 12 5 19 12"/></svg> Uploading…</span>';
} else if (it.uploadError) {
  statusHtml = ' <span style="color:#c0392b;display:inline-flex;align-items:center;gap:3px;">' +
    '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" ' +
    'stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3' +
    'L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>' +
    '</svg> ' + escapeHtml(it.uploadError) + '</span>';
}
```

| State | Color | Icon | Meaning |
|---|---|---|---|
| Uploaded | `#0d7c4a` (success green) | checkmark | Token received — this file is safely on the server |
| Uploading | `#ED7624` (brand orange) | upward arrow | In-flight fetch to the temp-upload endpoint |
| Failed | `#c0392b` (error red) | warning triangle | Upload failed — error message shown, Retry button appears |
| *(idle)* | — | — | Just added, upload not started yet — brief, since upload begins immediately |

⚠️ **Always run `it.uploadError` through an `escapeHtml()` (or equivalent) before interpolating it into
`innerHTML`** — this string can originate from the server's own response message, so treat it the same as
any other untrusted string, not as safe markup.

### JS — rendering the list (thumbnail, meta, actions)

```js
function renderField(fld) {
  fld.list.innerHTML = '';
  fld.arr.forEach((it, idx) => {
    const div = document.createElement('div');
    div.className = 'uploaded-item';

    const thumb = document.createElement('img');
    if (it.type.startsWith('image/')) {
      thumb.src = it.url;
    } else {
      // Non-image files (e.g. PDFs) get a generated placeholder thumbnail, not a broken image icon
      thumb.src = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64">' +
        '<rect width="100%" height="100%" fill="%23f3f4f6"/>' +
        '<text x="50%" y="55%" dominant-baseline="middle" text-anchor="middle" fill="%239ca3af" font-size="12">PDF</text></svg>';
    }

    const meta = document.createElement('div');
    meta.style.flex = '1';
    meta.innerHTML = '<strong>' + escapeHtml(it.name) + '</strong>' +
      '<div style="font-size:12px;color:var(--muted);margin-top:2px;">' + (it.size / 1024).toFixed(0) + ' KB' + statusHtmlFor(it) + '</div>';

    const btns = document.createElement('div');
    btns.style.cssText = 'display:flex;flex-direction:column;gap:8px;';

    const preview = document.createElement('button');
    preview.type = 'button'; preview.className = 'small-btn'; preview.textContent = 'Preview';
    preview.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); openItemPreview(it); });
    btns.appendChild(preview);

    // Retry — only shown when the upload has failed and isn't currently retrying
    if (it.uploadError && !it.uploading) {
      const retry = document.createElement('button');
      retry.type = 'button'; retry.className = 'small-btn';
      retry.style.cssText = 'background:#fff3cd;border-color:#f0c300;color:#5a4200;';
      retry.textContent = 'Retry';
      retry.addEventListener('click', e => {
        e.preventDefault(); e.stopPropagation();
        it.uploadError = null;
        preUploadFile(fld, it);
      });
      btns.appendChild(retry);
    }

    const rem = document.createElement('button');
    rem.type = 'button'; rem.className = 'small-btn'; rem.textContent = 'Remove';
    rem.addEventListener('click', e => {
      e.preventDefault(); e.stopPropagation();
      URL.revokeObjectURL(it.url);   // release the object URL — don't leak memory on removal
      fld.arr.splice(idx, 1);
      renderField(fld);
    });
    btns.appendChild(rem);

    div.append(thumb, meta, btns);
    fld.list.appendChild(div);
  });
}
```

├─ `e.stopPropagation()` on every button inside the row matters — the row itself sits inside interactive
   form layout, and without it a click meant for "Remove" could bubble up into something else on the page
   listening for clicks (like Section 30's sheet backdrop-click-to-close, if this field is ever placed
   inside a bottom sheet).
└─ `URL.revokeObjectURL(it.url)` on removal is required cleanup — skipping it leaks the blob URL for as
   long as the page stays open.

### Full checklist for a new file-upload field

├─ Copy the drop-zone and list markup, giving both elements field-specific IDs
├─ Set `allowedTypes`/`maxBytes`/`maxFiles` to what this specific field actually needs
├─ Point `preUploadFile`'s `fetch()` at a real temp-upload endpoint that returns `{ token }` on success
├─ Wire the "Preview" button to Section 5's `openItemPreview()` (modal use B)
├─ Confirm the drop zone has `tabindex="0"` and the Enter/Space keydown handler — it's not a native
   `<input>`, keyboard support isn't automatic
├─ Confirm `it.uploadError` (and any other server-sourced string) is escaped before use in `innerHTML`
└─ Confirm the real form-submit step actually sends each item's `token`, not the raw `File` object — the
   file is already on the server by submit time

## 8. ANIMATION SYSTEM & TRANSITION STANDARDS

> **See also:** Section 4 for specific loading-animation implementations, and Sections 24/25 for real components already built using this section's two-tier timing system.

⚡ UNIVERSAL TIMING FUNCTION (Used everywhere):

cubic-bezier(0.4, 0, 0.2, 1)

Why this curve?
├─ Professional and smooth (Apple/Google standard)
├─ Feels natural and responsive
├─ Not bouncy (cubic), not abrupt
└─ 0.3s duration is optimal for UI

STANDARD DURATIONS:

Quick Interactions (0.2s):
├─ Border color changes
├─ Small opacity shifts
└─ Example: Focus state change

Standard Transitions (0.3s):
├─ Button hover effects
├─ Input focus effects
├─ Modal/overlay fades
├─ Dropdown opens
└─ Most UI interactions

Animations (0.4-0.8s):
├─ Modal entrance: 0.4s
├─ Icon bounce: 0.6s
├─ Form slides: 0.3s
└─ Item appears: 0.3s

Loading Animations (1.2-1.5s):
├─ Load bar: 1.2s
├─ Spinner: 1s-1.5s
└─ Pulse: 1s

Page Transitions (0.5s):
├─ Fade in/out: 0.3-0.5s
└─ Slide in: 0.4s

KEY ANIMATIONS:

fadeIn:
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
Duration: 0.3s

slideIn:
@keyframes slideIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
Duration: 0.3s

slideUp:
@keyframes slideUp {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
Duration: 0.3s

modalScale:
@keyframes modalScale {
  from { opacity: 0; transform: scale(0.95) translateY(20px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}
Duration: 0.4s

modalIconBounce:
@keyframes modalIconBounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-8px); }
}
Duration: 0.6s

slideLoad (Loading Bar):
@keyframes slideLoad {
  0% { transform: translateX(0); opacity: 1; }
  50% { opacity: 0.5; }
  100% { transform: translateX(40px); opacity: 1; }
}
Duration: 1.2s

BUTTON HOVER ANIMATION:

.submit-btn:hover:not(:disabled) {
  transform: translateY(-3px);              /* Lift effect */
  box-shadow: 0 10px 30px rgba(16,33,75,0.35); /* Shadow grows */
  /* Plus shine effect from ::before */
}

Timing: 0.3s transition
Effect: Feels responsive and premium

INPUT FOCUS ANIMATION:

.field input:focus {
  border-color: var(--accent);
  background: #fafaf8;
  box-shadow: 0 0 0 3px rgba(215,189,136,0.15); /* Gold glow */
}

Timing: 0.3s transition
Effect: Clear visual feedback

## 9. SPACING & LAYOUT SYSTEM

> **See also:** Section 15's Layout Grid System builds directly on this spacing scale for page-level column/gutter structure — read this section first, then Section 15 for full-page layout.

📐 SPACING SCALE:

Base Unit: 4px (though we use 8px increments mostly)

Common Spacing Values:
├─ 6px: Gap between label and input
├─ 8px: Gap between form sections, pet options
├─ 12px: Gap between form fields, button gaps
├─ 14px: Gap between major sections
├─ 16px: Padding inside containers
├─ 18px: Padding on form sections
├─ 20px: Top margin on sections
├─ 24px: Horizontal padding on main wrapper
├─ 32px: Large padding, hero section
└─ 40px: Page max-width padding

FORM LAYOUT:

Max Width: 820px (for readability)
Horizontal Padding: 24px
Vertical Padding: 40px top, 80px bottom

Field Spacing:
├─ Label to Input: 6px gap
├─ Between Fields: 14px gap
├─ Between Sections: 20px margin
└─ Section Padding: 22px internal

### SEVEN SPACING PRINCIPLES — THE REASONING BEHIND THE SCALE

The scale and values above tell you WHAT numbers to use. This tells you WHY, so spacing decisions on
pages or components not explicitly covered above can still be made correctly. Spacing is not decoration —
it is a communication tool that tells the eye where to go next. Treat every gap on a page as a deliberate
signal, not a leftover default.

RULE 1 — Use a Spacing Scale, Not Random Numbers
├─ Every gap on a page must come from the Spacing Scale above — never eyeball a random pixel value
└─ Why: consistency builds trust. Random, inconsistent spacing breaks visual rhythm and reads as sloppy,
   even when a person can't articulate exactly what feels off

RULE 2 — Space Groups, Not Just Elements
├─ Related items sit close together; unrelated items sit farther apart. The gap itself communicates
   relationship
└─ Why: equal gaps everywhere flatten hierarchy and make everything look equally (un)important. Deliberately
   different gap sizes are what create meaning — e.g. tight gaps within a card's internal fields, a larger
   gap before the next unrelated card

RULE 3 — Give Breathing Room Before Headings, Not After
├─ Put more space above a heading than below it
└─ Why: space above a heading sets context and signals "a new section starts here." Space below a heading
   should stay tight, because it visually connects the heading to the content it introduces. This applies
   to every H2/H3 section header used throughout this system

RULE 4 — Padding Over Margin for Components
├─ Prefer padding (internal spacing) to shape how a component feels on the inside; use margin (external
   spacing) only to define how components relate to their neighbors
└─ Why: padding controls internal breathing room and is part of the component's own identity. Margin
   controls external distance and is about relationships between components. Confusing the two creates
   inconsistent component behavior when components get reused across pages

RULE 5 — Vertical Rhythm Is Invisible Glue
├─ Sections down a page should "breathe" at consistent, repeating intervals rather than random ones
└─ Why: if the rhythm breaks — one section cramped, the next oddly loose — the layout feels wrong even
   when a person can't explain why. Consistent vertical rhythm is what makes a long page feel calm and
   intentional rather than assembled piecemeal

RULE 6 — Tight Spacing Signals Importance, Generous Spacing Signals Premium
├─ Less space between elements pulls focus and creates density/urgency; more space signals separation and
   a more editorial, premium feel
└─ Why: use tighter spacing for dense, functional UI (dashboards, data tables, forms) where efficiency
   matters. Use generous spacing for pages meant to feel elevated and editorial — hero sections, donation
   pages, long-form content, and anywhere Bodoni Moda (Section 3) is in play

RULE 7 — Test Spacing at Actual Screen Size
├─ Always verify spacing at 100% zoom on real target dimensions, not a zoomed-out canvas view
└─ Why: spacing that looks right zoomed out in a design tool can look completely wrong at true pixel size
   on a real device. Check at real desktop, tablet, and mobile widths per the breakpoints in Section 10 —
   designing without this check means designing an illusion, not a real layout

BOTTOM LINE: Spacing isn't decoration, it's communication. Use the system above, be consistent, and let it
do the work — don't reach for a custom value when an existing one already exists for the job.

BORDER RADIUS SCALE:

6px: Small buttons, tags
7px: Small components
8px: Pet selector, smaller cards
9px: Form inputs, moderate components
10px: Cards, moderate containers
12px: Large buttons
14px: Form sections, major containers
18px: Modals
999px: Fully rounded (pills/badges)

## 10. RESPONSIVE DESIGN & MOBILE CONSIDERATIONS

> **See also:** Section 15's Layout Grid System for the exact column/gutter specs at each breakpoint below, and Section 13 for mobile-specific navigation patterns.

📱 BREAKPOINTS:

Desktop: 1200px+
Tablet: 768px - 1199px
Mobile: < 768px

RESPONSIVE RULES:

Form Sections:
@media(max-width: 640px) {
  .form-section-body { padding: 16px; }
}

Grid Layouts:
@media(max-width: 540px) {
  .field-row.col2 { grid-template-columns: 1fr; }
}

Pet Selector:
├─ Always: flex with wrapping
├─ Buttons: Inline-block, wrap naturally
└─ No media query needed (already responsive)

Modal:
@media(max-width: 480px) {
  .modal-box { padding: 24px 20px; }
}

Header:
@media(max-width: 640px) {
  .header { padding: 14px 16px; }
}

MOBILE OPTIMIZATIONS:
├─ Larger touch targets (min 44px)
├─ Simpler layouts (single column)
├─ Readable font sizes (14px minimum)
├─ Adequate spacing (8px minimum)
└─ Clear visual feedback on touch

## 11. SVG ICONS (No Emojis Rule)

> **See also:** the SVG Icon Library doc (CEO-maintained, pre-built, hand-picked icons — check this FIRST, before anything below), Section 22's Free Icon Libraries for fallback sourcing beyond the primary libraries below, and Section 13's Navigation Icon Glossary for what specific icon shapes communicate.

🎯 ICON STRATEGY: Always use SVG, NEVER use emojis

Why SVG over Emojis?
├─ Consistent across browsers and devices
├─ Customizable colors (using currentColor)
├─ Scalable without quality loss
├─ Professional appearance
├─ Full control over styling
└─ Better accessibility

REQUIRED ICONS (with SVG examples):

1. SUCCESS/CELEBRATION ICON (Modal)
<svg width="48" height="48" viewBox="0 0 24 24" fill="none"
     stroke="currentColor" stroke-width="1.5"
     style="color: var(--accent-dark);">
  <polyline points="20 21 9 13 20 5 20 21"></polyline>
  <polyline points="4 21 15 13 4 5 4 21"></polyline>
</svg>
Purpose: Success modal icon
Color: Gold (--accent-dark)

2. INFO/ALERT ICON (Warning Box)
<svg width="16" height="16" viewBox="0 0 24 24" fill="none"
     stroke="currentColor" stroke-width="2">
  <circle cx="12" cy="12" r="10"></circle>
  <line x1="12" y1="8" x2="12" y2="12"></line>
  <line x1="12" y1="16" x2="12.01" y2="16"></line>
</svg>
Purpose: Warning/info indicator
Color: Red (for warning)

3. LOCK/SECURITY ICON (Auth Gate)
<svg width="44" height="44" viewBox="0 0 24 24" fill="none"
     stroke="currentColor" stroke-width="1.5"
     style="color: var(--primary);">
  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
</svg>
Purpose: Security/authentication
Color: Navy (--primary)

4. BACK ARROW (Navigation)
<svg width="20" height="20" viewBox="0 0 24 24" fill="none"
     stroke="currentColor" stroke-width="2" stroke-linecap="round">
  <polyline points="15 18 9 12 15 6"></polyline>
</svg>
Purpose: Back navigation
Color: Muted text

5. PLUS ICON (Add More)
<svg width="20" height="20" viewBox="0 0 24 24" fill="none"
     stroke="currentColor" stroke-width="2" stroke-linecap="round">
  <line x1="12" y1="5" x2="12" y2="19"></line>
  <line x1="5" y1="12" x2="19" y2="12"></line>
</svg>
Purpose: Add more items
Color: Gold or Navy

6. TRASH/DELETE ICON (Remove)
<svg width="16" height="16" viewBox="0 0 24 24" fill="none"
     stroke="currentColor" stroke-width="2" stroke-linecap="round">
  <polyline points="3 6 5 6 21 6"></polyline>
  <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
</svg>
Purpose: Delete/remove action
Color: Red on hover

SVG SIZING STANDARDS:
├─ Small icons: 16px (info, close)
├─ Medium icons: 20px (navigation)
├─ Modal icons: 44-48px (large focal point)
└─ Button icons: 14-16px

SVG COLOR USAGE:
<!-- Use currentColor to inherit text color -->
<svg fill="currentColor" ... ><!-- Inherits the current text color --></svg>

<!-- Or explicit variable -->
<svg style="color: var(--accent-dark);"><!-- Gold --></svg>

<!-- Or hardcoded if necessary -->
<svg stroke="#10214b"><!-- Navy --></svg>

⚠️ CEO-MANDATED SOURCING NOTICE (read before using the library list below): Pet Center now maintains its
own SVG Icon Library doc — a hand-picked, CEO-approved, pre-built set of icons, each already tuned (line
weight, sizing, card treatment, on/off-click states) to fit this system. Check that doc FIRST for any icon
a task needs. Do not source from the internet or hand-draw an icon — either of those has repeatedly produced
icons that don't actually resemble the animal/object they're meant to represent, or that don't visually
match the rest of the site once dropped into a real card. If the exact icon a task needs isn't in the SVG
Icon Library doc yet, ask the CEO to add it rather than substituting something sourced independently. The
library list below (Feather, Heroicons, Tabler, Font Awesome) remains a legitimate fallback only once the
SVG Icon Library doc has been checked and doesn't yet cover what's needed — not a first stop.

ICON LIBRARY SOURCES (fallback only — see notice above):
├─ Feather Icons (feathericons.com)
├─ Heroicons (heroicons.com)
├─ Tabler Icons (tabler-icons.io)
└─ Font Awesome (solid/pro)

## 12. IMPLEMENTATION CHECKLIST & BUILD WORKFLOW

> **Required reading first:** Section 1's Rules & Warnings before starting any build — it condenses every mandatory rule referenced across the steps below into one place.

🛠️ HOW TO BUILD ANY NEW PETCENTER PAGE, START TO FINISH

⚠️ OPEN GAP: Step 4 below references a "Card" component and a "Pet Selector" component that do not yet
have their own dedicated specification sections anywhere in this document, unlike Buttons (Sections 6 and
8) and Modals (Section 5). Workers: do not invent Card or Pet Selector styling from memory — flag it to
Design so reference material can be sourced and documented properly, rather than guessing and creating
inconsistency across pages.

Step 1: Set Up the Color System
├─ Import all CSS variables from Section 2 (Navy, Gold, Warm Gray, Chocolate, Cream, Orange, functional
│  and supporting colors)
├─ Double-check every hex code against Section 2 exactly — do not eyeball or approximate
└─ Define root variables: --primary, --accent, --text, --muted, --border, --background, --surface

Step 2: Import Typography
├─ Add the Google Fonts import line from Section 3 (Sora + Cormorant Garamond + Bitter + Bodoni Moda)
├─ Include DM Sans as the documented fallback in case Sora fails to load
└─ Confirm fonts are loaded/rendered before any critical above-the-fold text paints

Step 3: Apply Typography Hierarchy
├─ Cormorant Garamond for headings
├─ Sora for body and UI
├─ Follow sizing hierarchy (54px H1 → 16px body)
└─ Use letter-spacing for uppercase

Step 4: Build Components
├─ Inputs: White bg, Navy border, Gold focus
├─ Buttons: Navy gradient, lift hover, load bar
├─ Modals: Glassmorphic, SVG icons, bouncing animation
├─ Cards: White bg, Navy border, hover effects
└─ Forms: Navy labels, proper spacing

Step 5: Implement Animations
├─ All transitions: 0.3s cubic-bezier
├─ Button hover: -3px lift
├─ Modal entrance: Scale + fade
├─ Loading: slideLoad 1.2s
└─ Ensure smooth, professional feel

Step 6: Test Responsive
├─ Desktop: 1200px+
├─ Tablet: 768px-1199px
├─ Mobile: <768px
├─ Verify all layouts adapt
└─ Touch targets ≥44px

Step 7: Verify Accessibility
├─ Keyboard navigation works
├─ Focus states visible
├─ Color contrast sufficient
├─ SVG icons have alt text
├─ ARIA labels where needed
└─ Semantic HTML

EXAMPLE: Creating a Dashboard Page

1. Start with template structure
2. Set header/nav styling (Navy, Sora)
3. Create card components (white bg, Navy borders)
4. Add forms with our input styling
5. Use pet selector component (copy-paste)
6. Implement buttons (Navy gradient)
7. Add modals (Section 5's shared modal base)
8. Test on mobile
9. Verify animations smooth
10. Check color consistency

## 13. MOBILE MENU & NAVIGATION PATTERNS

> **Visual reference available:** the "Menus" tab in the companion HTML file shows structural mockups of all 8 styles. These are samples, not final assets — adjust layout to your actual content.

📱 RESTYLING NOTE

The 8 layouts below use a black-and-yellow color scheme in their original demonstration — that is not our
system. When building any of these on a PetCenter page, restyle using our own colors: swap black for Navy
(#10214b) or Surface White depending on context, and swap yellow for our Gold (#d7bd88) or Orange (#ED7624)
as appropriate to the accent's job (Gold for premium/hover accents, Orange for primary actions — see
Section 2). Never ship a raw black/yellow combination.

🟢 FREE CHOICE: This is a design gallery, not a fixed assignment. Workers may pick any ONE of the 8 patterns
below per project/page based on fit, unless a future update to this document restricts a specific pattern
to a specific page type (the way Section 20's palettes are restricted). When in doubt, match the pattern to
the page's content density and platform using the guidance under each style.

THE 8 MENU STYLES:

1. GRID
├─ Layout: Full-screen 2×3 (or 3×3) grid of large rounded-square icon tiles, evenly spaced
├─ Feel: App-launcher style, icon-forward, playful and easy to scan at a glance
├─ Best for: A main navigation hub with 6-9 top-level destinations — e.g. a "PetCenter Services" home
│  screen linking to Shop, Vet Booking, Grooming, Adoption, Community, and Account
├─ Avoid when: You have more than ~9 destinations (grid gets cramped) or need text-heavy labels
└─ Build notes: Tiles use border-radius from Section 9's card scale; each tile = icon + short label,
   minimum 44px touch target per tile, equal gutter spacing (Section 9 spacing scale)

2. SIDE MENU
├─ Layout: A wide vertical panel (roughly 30-40% of screen width) slides in from one edge, holding a
   stacked list of nav items; a close ("X") icon sits at the top of the panel; the rest of the screen
   dims/darkens behind it
├─ Feel: Familiar, comprehensive — the classic "hamburger drawer"
├─ Best for: Primary site-wide navigation with many items (Shop, Adoption, Donate, Account, Settings,
   Help) — anywhere you need a full nav list without permanently occupying screen space
├─ Avoid when: You only have 3-4 nav items — a drawer is overkill; use Tab Bar or Rectangular instead
└─ Build notes: Slide-in animation should use the 0.3-0.4s cubic-bezier timing from Section 8; overlay
   the remaining screen with a dimmed backdrop (Navy at ~40% opacity, matching the modal overlay pattern
   in Section 5) so focus stays on the drawer

3. TAB BAR
├─ Layout: A fixed bar spanning the full width at the bottom of the screen, holding 4-5 icons in a row,
   with a raised circular button floating slightly above the bar in the center (a FAB embedded into the
   tab bar itself)
├─ Feel: Standard, reliable, always-visible — the most common mobile nav pattern
├─ Best for: Primary app-wide navigation the user needs constant access to — e.g. Home, Browse/Search, a
   raised central "Add"/"Book Now" action, Notifications, Profile
├─ Avoid when: The page has minimal navigation needs, or vertical space is already tight (this pattern
   permanently consumes bottom screen real estate)
└─ Build notes: The raised center button gets its own elevated shadow and sits ~8-12px above the bar line;
   active tab state should use Gold or Orange underline/fill per Section 2, inactive icons in Muted (#7a6d5f)

4. FAB (Floating Action Button — Speed Dial)
├─ Layout: A vertical stack of 3-4 smaller circular buttons cascading down the right edge of the screen,
   each with its own icon (e.g. edit, notify, lock, add) — an expanded/"speed dial" FAB state
├─ Feel: Contextual, compact, action-oriented rather than navigational
├─ Best for: Page-specific quick actions rather than site navigation — e.g. on a pet's profile page:
   Edit Profile, Set Reminder, Privacy/Lock, Add Photo
├─ Avoid when: You need primary navigation (this pattern is for actions, not wayfinding)
└─ Build notes: Buttons animate in sequentially with a short stagger (~0.05s delay between each) when the
   main FAB is tapped open; each circle button follows the 44px minimum touch target rule; use Gold for
   the resting state, Orange reserved for the single most important action if one stands out

5. SHEET (Bottom Sheet)
├─ Layout: A rounded drag-handle "pill" sits near the bottom of the screen above a wider, full-width
   rounded bar/panel — the classic swipe-up bottom sheet, shown here in its collapsed/preview state
├─ Feel: Lightweight, non-committal — lets users peek at content or actions without leaving the page
├─ Best for: Filters, quick actions, share sheets, "select a pet" pickers, or short supplementary forms
   that don't need a full modal takeover
├─ Avoid when: The content is long or requires significant reading/interaction — use a full modal
   (Section 5) instead
└─ Build notes: Drag handle is a small rounded bar (~36-40px wide, 4-5px tall) centered at the top of the
   sheet; sheet slides up from off-screen using the same easing curve as modals; background page content
   should dim slightly (~20% Navy overlay) but remain partially visible, distinguishing it from a full modal

6. THREE DOTS (Overflow Menu)
├─ Layout: A top header bar with a back-arrow on the left and a vertical three-dot "more" icon on the
   right; tapping the dots reveals a floating dropdown/overlay card with a short list of secondary actions
├─ Feel: Compact, secondary, "everything else" — keeps the main header uncluttered
├─ Best for: Secondary or situational actions that don't deserve permanent screen space — e.g. Share,
   Report a Problem, Block, Page Settings on a listing or profile page
├─ Avoid when: The actions inside are actually primary/frequent — burying important actions in an
   overflow menu hurts discoverability
└─ Build notes: Dropdown card uses the same white surface + subtle shadow treatment as other floating UI
   in this system, appears directly below the three-dot icon, dismisses on outside tap or item selection

7. RECTANGULAR (Icon Rail)
├─ Layout: A slim vertical strip (narrower than the Side Menu) fixed to one edge of the screen, holding
   3-4 stacked icon-only buttons (e.g. home, messages, profile) with no text labels
├─ Feel: Minimal, persistent, unobtrusive — always present but takes up very little space
├─ Best for: A small set of core destinations that should always be reachable without opening a drawer —
   works especially well on tablet-width or landscape mobile layouts
├─ Avoid when: You have more than ~4 destinations, or icons alone won't be clear without labels (test
   this — icon-only navigation needs very well-recognized icons or a tooltip/label-on-tap fallback)
└─ Build notes: Each icon button meets the 44px touch target minimum even though the rail itself is
   visually slim; active item indicated by background fill or a small side indicator bar, not by size change

8. RUDDER (Floating Pill Nav)
├─ Layout: A compact, capsule-shaped bar floating above the bottom edge of the screen (not edge-to-edge
   like Tab Bar), holding 2-3 icons with a raised circular action button in the center
├─ Feel: Light, modern, unobtrusive — a smaller footprint alternative to the full-width Tab Bar
├─ Best for: Content-heavy pages (articles, pet stories, image feeds) where a full-width bottom bar would
   feel heavy — gives quick access to core actions (Messages, central Add/Book action, Profile) without
   dominating the screen
├─ Avoid when: You need more than 3 nav destinations — the compact pill doesn't have room
└─ Build notes: Floats with margin from all screen edges (roughly 16-20px), uses a stronger drop shadow
   than the Tab Bar since it's "floating" rather than docked, rounded fully (999px radius per Section 9)

QUICK DECISION GUIDE:
├─ Need full site navigation, many items? → Side Menu
├─ Need always-visible core navigation, few items, full-width? → Tab Bar
├─ Need always-visible core navigation, few items, lighter footprint? → Rudder or Rectangular
├─ Need a visual hub/launcher for many top-level destinations? → Grid
├─ Need page-specific quick actions, not navigation? → FAB
├─ Need a lightweight peek/action panel without leaving the page? → Sheet
└─ Need to tuck away secondary/rare actions? → Three Dots

### NAVIGATION ICON GLOSSARY — WHAT EACH ICON SHAPE COMMUNICATES

The icon that triggers a menu carries meaning on its own, before a user even taps it. Use this glossary to
pick the icon that matches what the menu actually does — this is a reference for intent, not a free-choice
gallery like the menu styles above. Mismatching an icon to its function (e.g. using a grid icon to open a
single settings toggle) creates false expectations about what will happen after the tap.

├─ Three horizontal lines ("hamburger"): Hidden navigation / main menu — the universal trigger for the
   Side Menu pattern (Type 2 above)
├─ Two or three thick stacked bars: Layered content or stacked settings — implies expandable/collapsible
   grouped content, not a flat list
├─ Vertical row of three dots ("kebab"): A vertical list of actions — this is the icon behind the Three
   Dots overflow pattern (Type 6 above)
├─ Grid of dots (3×3): A collection of apps or features — an app-launcher icon, pairs naturally with the
   Grid menu pattern (Type 1 above)
├─ Grid of filled squares (3×3): Also reads as a feature grid, but bolder/denser than the dot-grid version
   — use when the destination screen itself will be visually dense
├─ Multiple bars at varying depth/thickness: Depth or stacked layers — implies drilling into nested content
├─ A single long horizontal bar: Horizontal actions with generous space — implies a minimal, spacious
   destination rather than a dense list
├─ Three even, symmetrical lines: Balanced simplicity — the most neutral/generic menu trigger when no more
   specific icon fits
├─ Three lines of decreasing width (short/medium/short): A minimal action drawer or quick menu — implies
   a small, lightweight set of options rather than a full navigation list
├─ Several short vertical lines: Playful interaction — reserve for casual/lighter contexts, not core
   navigation or transactional flows
├─ Ascending horizontal lines (like steps): Progression or sequential steps — this is the visual language
   already used by the UX Steppers in Section 16, not a menu trigger at all; don't reuse this shape for an
   actual menu icon or it will misread as a progress indicator
└─ A dot sitting above stacked lines: Notification or "something new" — use for a badge/alert indicator,
   not a navigation trigger

## 14. THE CREATIVE PROCESS — HOW TO APPROACH DESIGN DECISIONS

> **See also:** Section 17's Minimal Design Principles — both are philosophy sections that shape how you approach undocumented situations, not just visual specs to copy.

🧠 CORE PRINCIPLE: CREATIVITY IS A HABIT, NOT A TALENT

Good design work on this platform doesn't come from waiting for a great idea to strike. It comes from a
repeatable process. This section is the working method every worker should apply whenever a task calls for
a judgment call — picking between free-choice options in this document, styling a component that isn't
documented yet, or adapting a pattern to a new page. Treat it with the same weight as any visual spec above.

PRACTICE 1 — START BEFORE YOU FEEL READY
├─ Do not wait for a fully-formed idea before opening the editor. Waiting produces nothing; starting
│  produces material to react to.
├─ Begin by placing components, testing spacing, and trying color combinations within our existing system
│  — momentum reveals the right direction faster than staring at a blank page does
└─ Applied here: if a task requests something without an obvious existing pattern, start building a rough
   version using the closest documented section as scaffolding, then refine — don't stall waiting for the
   "right" concept before touching anything

PRACTICE 2 — STUDY WHAT ALREADY WORKS
├─ Before building something new, look closely at strong existing examples — not just to copy their
   layout, but to understand WHY they work
├─ Ask: why does this spacing feel right, why does this color pairing feel premium, why does this
   hierarchy guide the eye where it should
└─ Applied here: before adding a new component or page to PetCenter, review the patterns already
   documented in this system (Sections 2-13) and understand why Navy pairs with Gold, why the modal system
   keeps a solid background and an instant open/close rather than a blurred, animated one, why the spacing
   scale steps the way it does — new work should extend that logic, not ignore it

PRACTICE 3 — FEWER OPTIONS, SHARPER RESULTS
├─ Unlimited choice slows decisions down and produces weaker, more generic results. Constraints sharpen
   thinking and force better decisions faster
├─ This is exactly why this document locks down an exact color palette and a two-font type system instead
   of leaving those choices open on every project — the constraint is the point, not a limitation
└─ Applied here: when facing a free-choice gallery in this document (loading animations, mobile menu
   styles, etc.), commit to ONE option cleanly rather than blending several together. Mixing multiple
   options from the same gallery on one page dilutes the design rather than strengthening it

PRACTICE 4 — CONSISTENCY BEATS OCCASIONAL BRILLIANCE
├─ Skill and speed compound through repetition, not through isolated bursts of inspiration
├─ A platform built from disciplined, repeated use of the same proven patterns will always look and feel
   more professional than one built from scattered one-off "inspired" pages
└─ Applied here: reuse the documented spacing scale, animation timing, and component states on every new
   page rather than reinventing them each time. This document exists specifically so that consistency
   doesn't depend on any one worker's individual creative mood on a given day

## 15. LAYOUT GRID SYSTEM (DESKTOP / TABLET / MOBILE)

> **See also:** Section 9's Spacing & Layout System, which this section's gutter/margin values are deliberately built from — read that section first if you haven't already.

📐 GRID TERMINOLOGY (know these four terms before touching any layout)

├─ Frame: The full width of the screen/device being designed for
├─ Side Margin: The fixed empty space between the edge of the Frame and where content is allowed to start
├─ Column: One vertical slice of content-safe width — text, images, and components snap to column edges
└─ Gutter: The fixed gap between two columns — nothing sits inside a gutter, it exists purely to separate

The Box Width (also called the content box) = Frame width minus both Side Margins. Everything a user
actually sees and interacts with lives inside the Box Width, built from Columns and Gutters.

### DESKTOP GRID (viewport 1200px+, per Section 10 breakpoints)

├─ Design Frame: 1440px
├─ Box Width: 1100px, centered in the frame (side margins ~170px each on a 1440px frame — this is
│  intentional; wide desktop screens should NOT stretch content edge-to-edge, it hurts readability)
├─ Grid: 12 columns
├─ Column Width: 70px
├─ Gutter: 24px — deliberately matches the existing 24px value already in our Spacing Scale (Section 9),
│  per Spacing Rule 1: use a value from the system, never a random number
└─ Formula check: (70px × 12 columns) + (24px × 11 gutters) = 840 + 264 = 1104px ≈ 1100px Box Width ✓

### TABLET GRID (viewport 768-1199px, per Section 10 breakpoints)

No tablet reference was provided for this system, so Design has extrapolated the following using the
standard desktop-to-mobile column progression (12 → 8 → 4) so the grid steps down smoothly:

├─ Design Frame: 768px
├─ Box Width: 720px (24px side margins, matching the Spacing Scale)
├─ Grid: 8 columns
├─ Column Width: 72px
└─ Gutter: 20px

### MOBILE GRID (viewport <768px, per Section 10 breakpoints)

├─ Design Frame: 390px (design against iPhone 14 / a standard "Android Large" frame for easiest scaling
│  to other device sizes)
├─ Box Width: 342px
├─ Grid: 4 columns
├─ Column Width: 71px
└─ Gutter: 20px, Side Margins: 24px (again matching the Spacing Scale)

### ALIGNING MAIN CONTAINERS

├─ Every major page container (hero sections, card grids, section wrappers) must align its outer edges to
│  the grid's column and gutter widths above — never pick an arbitrary container width by eye
├─ This is what makes unrelated elements on the same page feel like they belong to one coherent system,
   rather than looking like independently-sized blocks stacked on top of each other
├─ Padding goes INSIDE an aligned container, not instead of alignment — align the container to the grid
   first, then add internal padding per Section 9's spacing scale for breathing room within it
└─ Relationship to Section 9's Form Layout: forms intentionally use a narrower 820px max-width for reading
   comfort, distinct from this section's full 1100px page grid. That's correct and deliberate — long-form
   reading content (forms, article body text) should stay narrower than full structural page width even
   though both derive from the same underlying spacing system

CSS IMPLEMENTATION:

.page-grid {
  max-width: 1100px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(12, 1fr);
  gap: 24px;
  padding: 0 24px; /* safety padding if frame is narrower than 1440px */
}

@media (max-width: 1199px) and (min-width: 768px) {
  .page-grid {
    max-width: 720px;
    grid-template-columns: repeat(8, 1fr);
    gap: 20px;
  }
}

@media (max-width: 767px) {
  .page-grid {
    max-width: 342px;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    padding: 0 24px;
  }
}

## 16. UX STEPPERS (MULTI-STEP PROGRESS INDICATORS)

> **Visual reference available:** the "Steppers" tab in the companion HTML file renders 4 of the 5 styles live (Type 2 omitted for space). These are samples, not final assets — adjust to your specific project.

📶 WHAT A STEPPER IS FOR

A stepper shows a user where they are inside a multi-step flow — adoption applications, seller/business
onboarding, checkout, verification flows. It always needs to communicate three states clearly: what's
already done, what's happening now, and what's still ahead.

🟢 FREE CHOICE: Any of the 5 styles below may be used per flow. Pick based on the Decision Guide at the
end of this section — stay consistent within one flow (don't switch stepper styles mid-flow).

SHARED COLOR RULES (apply to all 5 styles):
├─ Completed: Navy (#10214b) fill with a white checkmark, or a Success Green checkmark where status text
   is also shown (Type 4 only)
├─ Current/Active: Navy — either solid fill (Types 1, 5) or a bold outlined ring (Types 2, 3, 5 variant)
├─ Upcoming/Pending: Muted gray (#7a6d5f) or a light outlined circle — never use Orange for pending states,
   Orange is reserved for primary CTA actions elsewhere per Section 2
└─ Connecting lines: solid Navy for the completed portion, light gray/muted for the remaining portion —
   the line itself should visually communicate progress even before reading any label

### TYPE 1 — SEGMENTED TAB STEPPER
├─ Layout: A full-width bar divided into equal chip/tab segments, one per step, each with a short arrow-cut
   edge pointing toward the next segment
├─ States: Active segment = solid Navy fill, white bold label + light subtitle; inactive segments =
   outlined border, Navy label text, muted subtitle
├─ Best For: A prominent, always-visible stepper at the top of a flow where the current stage should
   dominate visually — e.g. the seller/shop setup wizard
└─ Avoid when: You have more than ~4-5 steps — segments get cramped, and on mobile this style needs the
   most horizontal space of all 5

### TYPE 2 — ICON-LED STEPPER WITH PROGRESS LINE
├─ Layout: An icon representing each step sits above a horizontal connecting line; the line fills solid
   up to the current step; a label and support line sit below each icon
├─ States: Line fills Navy up to current position, remains muted gray beyond it; current step's label is
   bold Navy, others muted
├─ Best For: Flows where each step has a clear, distinct visual identity — payment, account setup,
   integrations — good for a settings/integration wizard or a checkout with visually distinct stages
└─ Avoid when: Steps don't have a natural, unambiguous icon (forcing a vague icon onto a step hurts more
   than it helps)

### TYPE 3 — NUMBERED CHECKLIST STEPPER
├─ Layout: A compact horizontal row of number/checkmark + label pairs, connected by a thin underline
├─ States: Completed = checkmark in a filled circle; current = filled Navy numbered circle; upcoming =
   outlined muted numbered circle
├─ Best For: The most space-efficient option — good for a slim header stepper on adoption application
   forms where vertical real estate is tight
└─ Avoid when: The flow needs to feel prominent/celebratory rather than efficient — this style is
   intentionally understated

### TYPE 4 — STATUS TIMELINE STEPPER
├─ Layout: Circles connected by a dotted line, each with a 3-line stack: step label ("Step 1"), step name,
   and an explicit status word
├─ States: Completed = filled circle + checkmark, status text "Completed" in Success Green; current =
   outlined ring circle, status "In Progress" in Navy; pending = light gray circle, status "Pending" in Muted
├─ Best For: Flows where the STATUS matters as much as the step name — e.g. tracking an adoption
   application through review, or order fulfillment status a customer can check back on later
└─ Avoid when: The flow is a single uninterrupted session (checkout, onboarding) — the explicit status
   labels are most valuable when a user might leave and return later to check progress

### TYPE 5 — NUMBERED CIRCLE STEPPER WITH SOLID CONNECTOR
├─ Layout: Numbered/checkmarked circles connected by a solid horizontal line that fills as steps complete;
   label and support text sit below each circle
├─ States: Completed = filled Navy circle + white checkmark; current = outlined Navy ring circle showing
   the step number; upcoming = outlined gray circle showing the step number
├─ Best For: The most classic, universally recognized stepper pattern — the safe default choice for most
   multi-step forms (adoption application, checkout, business onboarding) when no other style is
   specifically justified by the flow's needs
└─ Avoid when: Nothing in particular — this is the safest general-purpose option of the five

DECISION GUIDE — WHICH STEPPER TO USE:
├─ No strong reason to pick something else? → Type 5 (safe default)
├─ Tight vertical space, need compact? → Type 3
├─ Need visible status the user can check back on later (application review, order tracking)? → Type 4
├─ Each step has a clear, distinct icon identity? → Type 2
├─ Want the current step to dominate a prominent header area? → Type 1
└─ Never mix two stepper types within the same flow — pick one and use it for every step of that flow

RESPONSIVE BEHAVIOR (per Section 10 breakpoints):
├─ Desktop/Tablet: show the full stepper with all step labels visible
├─ Mobile: collapse long label text if needed, or reduce to showing only the current step plus immediate
   neighbors with a "Step 2 of 4" text label — never let a stepper overflow horizontally on mobile
└─ All step indicators (circles, tabs) must meet the 44px minimum touch target from Section 10, even when
   the visual circle itself is drawn smaller — pad the tappable area

### ONBOARDING CARD PATTERN

A specific, common application of the stepper concept above: a single-card, swipeable onboarding/tutorial
flow shown to first-time users. This gets its own guidance because it's easy to build a version that looks
fine but leaves the user unsure how to proceed.

⚠️ THE MISTAKE TO AVOID: A card with only a row of small progress dots and no other navigation cue is
ambiguous — a user can see there are more steps, but nothing tells them HOW to advance (tap the dots? tap
the card? swipe?). Never ship an onboarding card with a progress indicator and no explicit action control.

THE CORRECT PATTERN:
├─ Progress indicator: use a simplified progress bar (a single filling line, not full Type-5 circles from
   above — onboarding cards are compact, so a plain bar communicates "how far along" without the visual
   weight of a full stepper)
├─ Explicit navigation controls: a "Next" button (Primary, per Section 17's Button Hierarchy) to advance,
   always paired with a "Skip" control (Tertiary, per Section 17) so users are never forced through the
   full sequence
├─ Skip must always be present from the very first card — don't make users sit through even one screen
   with no way out
└─ Card content: one image + one short headline + one short supporting line per card, per Section 17's
   "one focal point per screen" principle — never stack multiple messages into a single onboarding card

MOBILE-SPECIFIC LAYOUT:
├─ On mobile, stack content smartly: left-align the image next to the text rather than centering a large
   image above stacked text — this reduces vertical space consumption significantly
├─ Reduce padding compared to the desktop/tablet version to keep the compact card readable without excess
   whitespace pushing content off-screen
└─ Keep the Skip/Next controls anchored at a consistent position (typically bottom-right for Next,
   bottom-left for Skip) across every card in the sequence — don't let button position shift card to card

CSS Structure Reference:
<div class="onboarding-card">
  <img class="onboarding-image" src="..." alt="..."/>
  <h3 class="onboarding-headline">Select Your Craving Delight</h3>
  <p class="onboarding-subtext">One tap closer to tasty!</p>
  <div class="onboarding-progress-bar"><div class="onboarding-progress-fill"></div></div>
  <div class="onboarding-actions">
    <button class="btn-tertiary">Skip</button>
    <button class="btn-primary">Next</button>
  </div>
</div>

## 17. MINIMAL DESIGN PRINCIPLES

> **Visual reference available:** the "Buttons" tab in the companion HTML file renders all 8 button types from this section's Button Hierarchy live, including a working hover state. These are samples, not final assets — adjust to your specific project.

> **See also:** this section's Button Hierarchy is the general spec. For buttons already built out in context, see Section 24's Custom Dropdown System and Section 19's Dark Mode toggle for real, working examples of the Toggle Button type.

🎯 WHAT MINIMAL DESIGN MEANS FOR PETCENTER

Minimal design means building simple, clean, efficient layouts using only the essential elements a page
needs — nothing added just because it's available. This isn't a separate aesthetic to switch into for
certain pages; it's the discipline underneath everything already documented in this system. Six pillars
define it:

├─ Simplicity: Clean, uncluttered designs — no decoration that isn't doing a job
├─ Clean Layouts: Organized structure with clear hierarchy (Sections 9 and 15)
├─ Limited Colors: A restricted, deliberate palette (Sections 2 and 20) — never introduce ad-hoc colors
├─ Essential Elements: Only the components a page actually needs for its function
├─ Intuitive UX: Easy navigation and interaction, not novelty for its own sake
└─ Prioritizes Functionality: Practical design over decoration, every time they conflict

### TYPOGRAPHY RESTRAINT

├─ Limit any single page to 1-2 fonts actively visible at once — even though our locked system (Section 3)
   has four fonts available (Cormorant Garamond, Sora, Bitter, Bodoni Moda), a given page should typically
   show a heading font plus Sora for body, with Bitter appearing only where numerals require it. Do not
   stack all four fonts onto one screen just because each is individually approved
├─ Make sure text is legible: size matters more in minimal design, not less, since there's less visual
   noise to hide a sizing mistake behind
└─ Maintain strong contrast between text and background — this is already enforced by the "Pairs with"
   guidance throughout Sections 2, 3, and 20; don't weaken it for a "softer" look

### SIMPLICITY IS THE KEY

├─ Keep copy short and direct — resist the urge to explain more than the user needs at that moment
├─ Use ample whitespace to create balance — this is the same discipline as Spacing Rule 6 (Section 9):
   generous spacing signals a premium, editorial feel, while cramped spacing signals density and urgency
└─ Focus on ONE main message or focal point per screen/section — a hero section, a card, a modal should
   each have a single clear thing the user is meant to notice first, not three competing for attention

### FOCUS ON FUNCTIONALITY (CTA DISCIPLINE)

├─ Keep call-to-action copy simple: "Learn More," "Explore Now," "Sign Up" — subtle but noticeable, never
   overwrought
└─ Avoid cluttering a screen with elements that aren't necessary — every additional button, badge, or icon
   should justify its presence, not just fill space

⚠️ CRITICAL WARNING — DO NOT LET TWO BUTTONS COMPETE: A common failure is giving a secondary action (like
"Cancel") its own bold, saturated fill color sitting right next to the primary action. On PetCenter, this
risk is real: our Orange (#ED7624) and Navy (#10214b) are both strong, saturated brand colors. Never fill
a Cancel/secondary button with Orange next to a Navy or Orange primary button — both compete for attention
and the user loses the visual cue for which action actually matters.

BUTTON HIERARCHY (fills a gap not previously specified in this document):
├─ Primary: Solid fill (Navy gradient per Section 8, or Orange when it's the single most important action
   on a themed page per Section 20) — white text — exactly ONE primary button visible per screen/section
├─ Secondary: Outlined only — Navy or Plum border depending on page theme, matching text color, transparent
   or white fill — used for "Cancel," "Back," or an alternate but non-critical action
└─ Tertiary: Text-only, no border or fill — Navy or Muted text — used for the lowest-priority action on a
   screen (e.g. "Skip for now")

CSS Example:
.btn-primary { background: var(--primary-gradient); color: #ffffff; border: none; }
.btn-secondary { background: transparent; color: var(--primary); border: 1.5px solid var(--primary); }
.btn-tertiary { background: transparent; color: var(--primary); border: none; text-decoration: underline; }

FULL BUTTON TYPE REFERENCE (8 types — the three above cover general priority; these five cover specific
jobs a button needs to do). 🟢 Workers are free to choose which type fits a given scenario, using the
notes below — this isn't a rigid rulebook, use good judgment for the specific project at hand.

4. ICON BUTTON
├─ What: A button represented by an icon alone, no visible label — used where space is limited and the
   icon clearly communicates its function on its own
├─ Best For: Toolbars, navigation bars, and menus — e.g. a notifications bell or a settings gear in a
   header, per Section 11's SVG icon standard
└─ Rule: Only use an icon alone when the icon is unambiguous — if a worker has to think about which icon
   fits, the user will too; add a text label instead when in doubt

5. FAB (FLOATING ACTION BUTTON)
├─ What: A circular button that floats above page content, reserved for the single primary action central
   to that screen — e.g. "Create," "Compose," "Add"
├─ Full pattern spec: see Section 13's FAB menu pattern for layout, positioning, and the expandable
   "speed dial" variant (below) — this entry is the general button-type summary, Section 13 has the
   complete build spec
└─ Rule: A FAB can be a stand-alone action button on its own — it doesn't require a menu attached to it

6. SOCIAL SIGN-IN BUTTON
├─ What: A button (or icon button) representing a third-party sign-in method — Google, Facebook, Apple —
   placed on login/signup pages to reduce friction
├─ Best For: Login and signup flows only — never repurpose this pattern for general in-app actions
├─ Format: Can be a full labeled button ("Continue with Google") or a compact icon-only row — pick based
   on how many providers are offered; 3+ providers favors the compact icon row to avoid a tall stacked list
└─ Rule: Always show the provider's recognizable icon so users can identify their preferred method at a
   glance — never replace provider icons with generic ones

7. EXPANDABLE BUTTON
├─ What: A button that reveals additional options on tap, most commonly a FAB that expands into a
   speed-dial stack of smaller action buttons
├─ Full pattern spec: this is the same expandable behavior already documented under Section 13's FAB
   pattern (Type 4, Speed Dial) — use that spec for stagger timing and layout
└─ Rule: The expand/collapse transition must be smooth (use Section 8's standard easing) so users
   intuitively understand there's more to explore, not that something broke

8. TOGGLE BUTTON
├─ What: A control that switches between two states (on/off, active/inactive) with a clear visual
   indicator of current state via color, icon, or position
├─ Full pattern spec: Section 19's "Settings Widget Control Pattern — Toggle + Description" is the general
   spec for every toggle in the system — when to use a toggle vs. a plain checkbox, and the rule that any
   consequential toggle needs a one-line description underneath it. Section 19's Dark Mode and Color Vision
   Accessibility controls are the concrete, built-out examples of this same pattern in use
└─ Rule: Never use a toggle for an action that isn't genuinely binary — if there are 3+ states, use a
   segmented control or dropdown instead

### BUTTON SIZING SPEC

Use this exact spec across every button on the platform — never eyeball button padding per Spacing Rule 1.

| Size   | Height | Font              | Horizontal Padding | Icon-to-Text Gap | Vertical Padding |
|--------|--------|-------------------|---------------------|-------------------|-------------------|
| Small  | 32px   | Sora 12px Semibold| 20px sides          | 4px               | 8px top/bottom    |
| Medium | 40px   | Sora 14px Semibold| 24px sides          | 8px               | 12px top/bottom   |
| Large  | 52px   | Sora 16px Semibold| 24px sides          | 8px               | 16px top/bottom   |

└─ All padding values pull directly from the existing Spacing Scale (Section 9) — heights and font sizes
   are new but every padding/gap value here was already in the system, not invented fresh

⚠️ **Small (32px) falls under Section 10's 44px minimum touch target rule — pad the tappable area, don't
enlarge the visual button.** Give a Small button's clickable hit area an invisible padding/margin extension
(e.g. `padding: 6px` on the wrapping element, or a pseudo-element hit-slop) so the actual tappable region
reaches 44×44px, while the button itself keeps rendering at its documented 32px visual height. Never solve
this by silently bumping a Small button's visible size to 44px — that defeats the point of having a Small
size at all.

### BUTTON REVIEW CHECKLIST

Run every new or modified button component through this before calling it done:

├─ ☐ Clear visual hierarchy between primary/secondary/tertiary actions on this screen (exactly one primary)
├─ ☐ Every touch target ≥44×44px, including Small buttons via padding (see note above)
├─ ☐ Button size matches the Small/Medium/Large spec table exactly — no eyeballed padding
├─ ☐ All 5 states designed: Default, Hover, Pressed, Disabled, Loading
├─ ☐ Placement is consistent with this component's established pattern elsewhere on the site
├─ ☐ Label leads with a clear, actionable verb, 1-3 words
├─ ☐ Secondary/utility actions converted to icon-only buttons where appropriate (not everything is a
   full bordered button)
└─ ☐ Any settings toggle with a real consequence pairs a toggle switch with a one-line description,
   per Section 19's Toggle + Description pattern — not a bare checkbox

BUTTON STATES (all 5 required on every button component — none are optional):
├─ Default: resting state per the styling already defined above
├─ Hover (desktop only): the -3px lift already defined in Section 8's Button Hover Animation
├─ Pressed: a darkened shade of the button's own fill (~10-15% darker), applied instantly with no
   transition delay — this state was not previously specified anywhere in this document
├─ Disabled: Muted (#7a6d5f) fill or border at reduced opacity (~50%), never fully hidden — the button
   should visibly communicate "not clickable right now," not be indistinguishable from an active one
└─ Loading: replace the label/icon with one of Section 4's loading animations (Load Bar is the standard
   choice for in-button loading) — button remains disabled for the loading duration

PLACEMENT CONSISTENCY:
├─ Once a button's position is established within a component (e.g. primary action bottom-right of a
   card, secondary action to its left), that exact position must repeat identically across every instance
   of that component
└─ This is the Law of Uniform Connectedness (Section 18) applied to buttons specifically — inconsistent
   placement breaks the pattern a user just learned from the previous instance

BUTTON COPY:
├─ Lead with an actionable verb — "Log In," "Continue," never a noun-only label like "Login page"
├─ Keep it to 1-3 words wherever possible
├─ Add a directional arrow only when it reinforces forward motion in a multi-step flow (e.g. "Continue →")
└─ If an action can't be summarized in 1-3 words, the flow likely needs rethinking — that's a signal to
   flag to Design, not a cue to write a longer button label

ICON BUTTON vs. TEXT BUTTON FOR SECONDARY ACTIONS (extends Button Type 4 above):
├─ In a toolbar or dense UI with one primary action and several secondary/utility actions, only the
   primary action should be a full bordered/filled button — convert secondary actions to icon-only buttons
   (optionally with a small caption label underneath)
├─ Why: when every action in a row is styled as an equal bordered button, none of them reads as more
   important than another — icon-only secondary actions restore the hierarchy without adding visual weight
├─ Good candidates for icon-only treatment: delete, undo/revert, history, filter, settings — anything
   utility-driven, frequent, or destructive where the icon meaning is unambiguous
└─ Never convert the screen's actual primary/main CTA to icon-only — it must stay a labeled button for
   clarity, per the Button Hierarchy rule above

### HIGH-QUALITY IMAGERY

├─ Use clean, high-quality images that align with the minimal aesthetic — a blurry, low-quality photo
   undermines every other correct decision on a page
└─ Where a photo isn't available or would add clutter, prefer a clean illustration or SVG icon (Section
   11) instead of forcing in a mediocre photo

### LIMIT THE COLOR PALETTE

├─ Stick to 2-3 primary colors visible on any one screen for a clean, cohesive look — this holds even
   within our locked 5-color thematic palettes (Section 20): a Romantic Blend page should draw from 2-3
   of its five tones at real prominence, not display all five at full saturation simultaneously
├─ Neutral tones (white, black, gray) paired with one accent color read as calm and professional — this is
   PetCenter's default mode, matching our core Navy/Cream/Gold system
└─ A bolder, higher-contrast palette (e.g. dark background with a single vivid accent) reads as energetic
   and bold instead — this is a valid alternative mood, not a mistake, but it should be a deliberate choice
   for a specific context (e.g. Magenta Bloom, Section 20) rather than the default

## 18. STATUS & FEEDBACK SYSTEMS

> **Visual reference available:** the "Status & Toasts" tab in the companion HTML file renders the full 5-state badge system and all 4 toast types live. These are samples, not final assets — adjust to your specific project.

📍 WHY THIS SECTION EXISTS

Status badges and toast notifications both communicate system state to a user, but they answer different
questions: a status badge answers "where does this THING currently stand?" (an order, an application, a
task); a toast answers "what just HAPPENED?" (an action's immediate result). Both use the same five
functional colors from Section 2 so the meaning stays consistent everywhere on the platform — a user
should never have to relearn what a color means from one context to the next.

### TASK/RECORD STATUS BADGES

Use this exact five-state system for anything with a lifecycle: adoption applications, orders, support
tickets, admin approval queues. Do not invent alternate status wording per feature — reuse these five terms
so the platform stays predictable.

1. OPEN
   ├─ Meaning: Created but not yet started
   ├─ Color: Muted gray (#7a6d5f) — outlined circle icon
   └─ Example: A new adoption application just submitted, awaiting first review

2. PENDING
   ├─ Meaning: On hold or waiting on input (from the user or from staff)
   ├─ Color: Warning Amber (#f59e0b) — clock icon
   └─ Example: An application waiting on the applicant to upload a missing document

3. IN PROGRESS
   ├─ Meaning: Currently being worked on
   ├─ Color: Info Blue (#3b82f6) — half-filled circle/spinner icon
   └─ Example: Staff actively reviewing an application (this is the same "In Progress" state used by the
      Status Timeline stepper, Section 16 Type 4)

4. COMPLETED
   ├─ Meaning: Finished
   ├─ Color: Success Green (#10b981) — checkmark icon
   └─ Example: Application approved and adoption finalized

5. CANCELLED
   ├─ Meaning: Stopped before completion
   ├─ Color: Error Red (#ef4444) — X icon
   └─ Example: Applicant withdrew, or application was declined

CSS Implementation (pill badge):
.status-badge {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 6px 12px; border-radius: 999px;
  font-family: 'Sora'; font-weight: 600; font-size: 13px;
}
.status-open { background: rgba(122,109,95,0.1); color: #7a6d5f; }
.status-pending { background: rgba(245,158,11,0.1); color: #f59e0b; }
.status-in-progress { background: rgba(59,130,246,0.1); color: #3b82f6; }
.status-completed { background: rgba(16,185,129,0.1); color: #10b981; }
.status-cancelled { background: rgba(239,68,68,0.1); color: #ef4444; }

### TOAST NOTIFICATIONS

Toasts confirm the result of an action the user just took. Use the same four-color functional system,
mapped slightly differently than status badges since toasts only need four categories, not five:

1. SUCCESS TOAST
   ├─ Color: Success Green (#10b981), checkmark icon in a filled circle
   └─ Example: "Your adoption application has been submitted."

2. INFO TOAST
   ├─ Color: Info Blue (#3b82f6), "i" icon in a filled circle
   └─ Example: "Your changes are syncing across devices."

3. WARNING TOAST
   ├─ Color: Warning Amber (#f59e0b), triangle-exclamation icon in a filled circle
   └─ Example: "This listing will expire in 24 hours."

4. DESTRUCTIVE/ERROR TOAST
   ├─ Color: Error Red (#ef4444), triangle-exclamation icon in a filled circle
   └─ Example: "This listing has been permanently deleted."

Shared toast rules:
├─ Structure: icon + message + dismiss (×) button, per the pattern above
├─ Background: a soft tint of the toast's color (~8-10% opacity) rather than a fully saturated fill — keeps
   it legible and calm rather than alarming, even for the destructive variant
├─ Position: fixed to a consistent screen corner/edge across the whole platform — never vary placement
   page to page
├─ Auto-dismiss: Success and Info toasts after ~4s; Warning and Destructive toasts stay until manually
   dismissed, since those carry information worth confirming was read
└─ Never stack more than 3 toasts at once — queue additional ones rather than flooding the screen

### UX LAWS APPLIED AT PETCENTER

Two general UX principles that inform how components across this entire document should behave. Treat
these as the reasoning behind existing specs, not new components to build.

DOHERTY THRESHOLD LAW — Speed Matters
├─ The Principle: Systems should respond within ~400ms to keep a user engaged and in flow. Delays beyond
   ~3 seconds cause frustration, distraction, and disengagement. Instant feedback is always better than no
   feedback, even if the feedback is just an acknowledgment that something is happening
├─ Applied here: This is why Section 4's loading animations exist at all — never leave a button or action
   with zero visual response while waiting on a server
├─ Skeleton screens vs. loaders — pick based on content type:
│  ├─ Use a SKELETON SCREEN (gray placeholder blocks matching the eventual layout) for content-heavy pages
│     loading for the first time — dashboards, order history lists, admin tables. This previews the
│     structure the user is about to see, which feels faster even at the same actual load time
│  └─ Use one of Section 4's LOADING ANIMATIONS (spinner, load bar, etc.) for short, discrete waits — a
     button submission, a small data refresh — where there's no layout to preview
└─ Skeleton Screen build notes: use the Border Color / Background tones from Section 2 as the placeholder
   fill (never pure gray unrelated to our palette), with a subtle shimmer animation (background-position
   sweep, ~1.5s ease-in-out loop) to signal "loading" rather than "broken"

LAW OF UNIFORM CONNECTEDNESS — Grouping
├─ The Principle: Elements that are visually connected — sharing a background, a border, or a container
   frame — are perceived as belonging to the same group far more strongly than elements that are merely
   placed near each other with no visual connection
├─ Applied here: This is a stronger version of Spacing Rule 2 (Section 9: "space groups, not just
   elements"). Proximity alone is a weak signal; a shared frame or connecting line is a strong one
└─ Practical rule: when a set of related items must read as clearly related (a nav item cluster, a card's
   internal fields, a stepper's steps), don't rely on spacing alone — wrap them in a shared container,
   background tint, or connecting line per Section 16's stepper connectors and Section 6's card patterns

## 19. DARK MODE SYSTEM

> **See also:** Section 2 for the light-mode base palette being adapted here, and Section 17's Button Hierarchy for the Toggle Button type this section's controls are a real, built-out example of.

🌙 WHY THIS MATTERS

Dark mode isn't "light mode with colors inverted." Naively flipping our light-mode palette (pure white
backgrounds → pure black, Navy text → pure white) causes real eye strain and visual problems. Dark mode
needs its own deliberately tuned palette, built from five rules below, plus a user-facing toggle. Because
most platforms now offer this, treat dark mode as a first-class part of the system, not an afterthought.

⚙️ ACCESS POINT — READ BEFORE BUILDING: Dark mode is NOT a standalone floating toggle button anywhere on
PetCenter. It lives as one control inside a broader Settings Widget — a floating panel that will also house
other accessibility and helper controls. The Settings Widget itself is not yet fully specified (its full
layout, other controls, and open/close behavior are pending future documentation — flag it rather than
inventing the rest of the widget). What IS specified here is exactly how the dark mode toggle control and
the resulting dark theme should look and behave once triggered from inside that widget.

### FIVE DARK MODE RULES

RULE 1 — Never Reuse Light Mode's Shadows
├─ The Problem: Light mode shadows (Navy-tinted, low opacity) become invisible or muddy against a dark
   background — shadows must be redesigned for dark mode, not carried over
└─ The Fix: Use pure black (#000) for dark mode shadows, since dark mode needs a stronger, more deliberate
   shadow to read as elevation at all against an already-dark surface

RULE 2 — Soften the Whites
├─ The Problem: Pure white (#ffffff) text or large white surfaces against a dark background cause the same
   eye strain pure black causes against light backgrounds, especially at large sizes
└─ The Fix: Never use pure white for text or large elements in dark mode — always use a softened off-white

RULE 3 — Lower Text/Background Contrast From Maximum
├─ The Problem: Pure white text on a pure black background is technically high-contrast, but that extreme
   contrast itself is what causes fuzziness and discomfort during extended reading
└─ The Fix: Use #F2F2F2 for dark mode text and #121212 for dark mode backgrounds — not #ffffff on #000

RULE 4 — Reduce Color Saturation
├─ The Problem: Fully saturated brand colors against a dark background create a visual "vibrating" or
   "shaking" effect that's uncomfortable to look at, especially on buttons and large fills
└─ The Fix: Use a lighter, less saturated shade of any brand color for dark mode rather than the full
   light-mode saturation — this applies directly to our own Orange and Navy, below

RULE 5 — Brand Colors as Accent, Not Fill
├─ The Problem: Dark mode exists specifically to reduce screen brightness and eye strain — covering large
   areas in a bright saturated brand color defeats that purpose even if the color itself is "correct"
└─ The Fix: Use brand colors for small accents, icons, borders, and CTAs — never as a large fill/background
   in dark mode, even where a light-mode equivalent might use a bold color block

### PETCENTER DARK MODE PALETTE

BASE COLORS:
├─ Dark Background: #121212 (page background — never pure #000)
├─ Dark Surface: #1c1c1c (cards, modals, elevated panels — one step lighter than the page background so
   elevation reads clearly even before a shadow is applied)
├─ Dark Text Primary: #F2F2F2 (never pure #ffffff)
├─ Dark Text Muted: #a39b91 (a warm-toned dim gray, keeping continuity with our light-mode Muted Text's
   warm character rather than switching to a neutral cold gray)
├─ Dark Border: rgba(255,255,255,0.08) — a faint light-on-dark border, the dark-mode equivalent of
   Section 2's Border Color
└─ Dark Shadow: rgba(0,0,0,0.6) — pure black per Rule 1, at a higher opacity than light mode's shadow since
   it needs to read clearly against an already-dark surface

BRAND ACCENT COLORS (desaturated/lightened per Rules 4-5 — accents only, never large fills):
├─ Dark Mode Navy Accent: #6d85c4 (a lightened, less saturated stand-in for Navy #10214b — the original
   Navy is too dark to read as an accent against a near-black background, so it must be lightened here,
   not just reused directly)
├─ Dark Mode Gold Accent: #d7bd88 (unchanged from light mode — Gold was already a muted, non-fully-
   saturated tone, so it needs no adjustment)
└─ Dark Mode Orange CTA: #f0925a (a lightened, desaturated stand-in for brand Orange #ED7624 — used only
   for primary CTA buttons, never as a large background fill, per Rule 5)

FUNCTIONAL COLORS IN DARK MODE (desaturated per Rule 4):
├─ Success Green (dark mode): #4ade80 (lightened from #10b981)
├─ Error Red (dark mode): #f87171 (lightened from #ef4444)
├─ Warning Amber (dark mode): #fbbf24 (lightened from #f59e0b)
└─ Info Blue (dark mode): #60a5fa (lightened from #3b82f6)

CSS VARIABLES:
:root[data-theme="dark"] {
  --dark-bg: #121212;
  --dark-surface: #1c1c1c;
  --dark-text: #F2F2F2;
  --dark-text-muted: #a39b91;
  --dark-border: rgba(255,255,255,0.08);
  --dark-shadow: rgba(0,0,0,0.6);
  --dark-navy-accent: #6d85c4;
  --dark-gold-accent: #d7bd88;
  --dark-orange-cta: #f0925a;
  --dark-success: #4ade80;
  --dark-error: #f87171;
  --dark-warning: #fbbf24;
  --dark-info: #60a5fa;
}

### THE MODE TOGGLE CONTROL

├─ Users should always be able to switch between dark and light mode manually, whenever they want — never
   force one mode with no way to change it
├─ Control style: a horizontal switch/slider with "Dark mode" labeled on one side and "Light mode" labeled
   on the other, the toggle thumb sliding between them — this is the control to build inside the Settings
   Widget once that component is specified
├─ Persist the user's choice (don't reset to system default every session) — respect their last explicit
   choice over the OS-level preference once they've manually chosen
└─ Transition between modes should use the same 0.3s cubic-bezier easing as the rest of this system
   (Section 8) — an instant, jarring color swap feels broken even when the end state is correct

### COLOR VISION ACCESSIBILITY (SETTINGS WIDGET)

A second control that lives in the same not-yet-built Settings Widget as the dark mode toggle above. Not
everyone sees color the same way — a meaningful share of users experience some form of color vision
deficiency, and our semantic color system (Section 18's Success/Error/Warning/Info states, chart colors in
the Lavender Mist palette, Section 20) needs to remain usable for them specifically, not just theoretically
accessible.

THE FOUR VISION TYPES TO SUPPORT:
├─ Normal Vision (default)
├─ Deuteranopia — reduced sensitivity to green (the most common form of color vision deficiency)
├─ Protanopia — reduced sensitivity to red
└─ Tritanopia — reduced sensitivity to blue

├─ Control style: same pattern as the dark mode toggle — a simple selectable control in the Settings
   Widget, not a separate standalone button
├─ Persist the user's selection the same way dark mode preference is persisted
└─ A full task brief for building this feature is provided separately for a worker to implement — see the
   response accompanying this document update, not embedded here as a build spec

### SETTINGS WIDGET CONTROL PATTERN — TOGGLE + DESCRIPTION

This is the general control pattern the entire Settings Widget should follow, not just dark mode and color
vision — applies to every future setting added to that widget.

├─ For any setting with a real consequence (dark mode, color vision, notifications, data sharing, account
   restrictions, anything with a financial or account-level effect), use a toggle switch, not a checkbox —
   toggles read as on/off at a glance far more clearly than a checkbox does
├─ Pair every consequential toggle with a short, one-line description underneath explaining what it
   actually does — a user should never have to guess what flipping a switch will do, especially for
   anything touching account state or billing
├─ Low-stakes, self-explanatory, purely cosmetic opt-ins can remain plain checkboxes with no description —
   not everything needs to become a toggle just because toggles exist
└─ Toggle color states: ON = Navy (#10214b) fill (or Gold on a themed page per Section 20), OFF = Muted
   gray (#7a6d5f) — never a generic unrelated color; every control in this system pulls from the locked
   palette, including toggle switches

Example structure:
[●—] Dark Mode                       (ON, Navy fill)
     Switches the interface to a low-light color scheme.

[●—] Color Vision Accessibility      (ON, Navy fill)
     Adjusts status colors for your selected vision type.

[—○] Auto-Renew Subscription         (OFF, Muted gray)
     Automatically charges your card on file each billing cycle.

☐ Show Tips on Startup               (plain checkbox — cosmetic, self-explanatory, no description needed)

## 20. PAGE-SPECIFIC COLOR PALETTES (THEMATIC ACCENT SYSTEMS)

> **See also:** Section 1's color flexibility rule for when it's appropriate to adapt beyond what's documented here, and Section 23 for the color theory lessons behind that flexibility.

🎨 WHY THIS SECTION EXISTS

The core brand palette in Section 2 (Navy, Gold, Warm Gray, Chocolate, Cream, Orange) governs the general
PetCenter interface — dashboards, forms, portals, checkout flows. But some pages exist to make the visitor
*feel* something specific before they act: a donation page needs to feel gentle and emotionally warm, an
admin dashboard needs to feel calm, orderly, and easy to scan. For pages like this, we layer a secondary
"thematic accent palette" on top of the core system.

This does NOT replace the core brand system. Typography rules (Cormorant Garamond / Sora), spacing, border
radius, animation timing, and SVG icon strategy from the rest of this document still apply everywhere,
without exception. Only the *color* layer shifts to suit the page's emotional purpose.

⚠️ MANDATORY RULE — THE ORANGE BRAND ANCHOR
No matter which thematic palette a page uses, our brand Orange (#ED7624) must always appear somewhere on
that page. This is non-negotiable. Orange is what keeps a donation page or an admin dashboard visibly and
unmistakably "PetCenter" even when the rest of the palette shifts away from Navy and Gold. Workers must
never build a themed page using only the theme's own colors — Orange is always the anchor.

How to place Orange without breaking the theme's mood:
├─ Use it in ONE dominant place, not scattered everywhere — typically the primary call-to-action button
│  ("Donate Now", "Export Report", "Confirm"), a key icon, or a progress/status indicator
├─ Keep it small and deliberate against pastel themes — a single bright orange CTA against a soft pink or
│  lavender background reads as "the one thing to click," which is good UX, not visual clutter
├─ Never use Orange as a large background fill on a themed page — it will fight the palette's mood instead
│  of anchoring it
└─ Pair Orange CTA buttons with white or navy text (#ffffff or #10214b) — never place Orange text directly
   on these pastel backgrounds, as contrast will be poor

### PALETTE A: "ROMANTIC BLEND" — Emotional & Donation Pages

🐾 USE CASE (Option 1 of 2 — see Magenta Bloom below for the alternative): Pet donation pages, adoption
success stories, memorial/tribute pages, rescue appeals, "sponsor a pet" flows, and any page whose primary
goal is to move someone emotionally before they take action. Workers may choose either Romantic Blend or
Magenta Bloom for these page types — do not substitute a different, undocumented palette.

Why this palette works here: muted plum, rose, and blush tones psychologically read as tender, compassionate,
and sincere — the opposite of a cold, transactional "checkout" feeling. Warm pinks lower visual aggression
and invite empathy, which is exactly the emotional register a donation appeal needs before asking someone
to give money.

THE FIVE COLORS & THEIR ROLES (do not reassign these roles — order matters for hierarchy):

1. DEEP PLUM: #826E8B
   ├─ Role: Anchor/dark color — headline text, nav bar background, footer background
   ├─ Use for: H1/H2 headings on light sections, primary navigation, dark section backgrounds
   └─ Pairs with: White or Pale Blush text on top of it

2. ROSE: #EB6E9B
   ├─ Role: Emotional highlight — the "heartbeat" color of the page
   ├─ Use for: Secondary buttons, quote/testimonial highlight borders, heart/favorite icons, tag badges
   │  ("Urgent", "Almost There"), progress bar fill (donation goal trackers)
   └─ Pairs with: White text on top of it

3. SOFT PINK: #E69CBA
   ├─ Role: Mid-tone accent — supporting UI, not a focal point
   ├─ Use for: Card borders, hover states on rose elements, secondary icon fills, chip/pill backgrounds
   └─ Pairs with: Deep Plum text on top of it

4. BLUSH: #F4B3C7
   ├─ Role: Light section background — soft separation between page sections
   ├─ Use for: Alternating section backgrounds, form field backgrounds, image overlay tints on pet photos
   └─ Pairs with: Deep Plum or Primary Text (#2c2416) on top of it

5. PALE BLUSH: #F5CDD0
   ├─ Role: Base/page background — the lightest, most neutral tone in the set
   ├─ Use for: Overall page background, hero section base, modal backgrounds on this page type
   └─ Pairs with: Deep Plum or Primary Text on top of it

WHERE THE ORANGE ANCHOR GOES ON THIS PALETTE:
├─ The primary "Donate Now" / "Sponsor This Pet" button = Orange (#ED7624) background, white text
├─ Donation amount selector: selected/active amount chip can use Orange border to show selection state
└─ Everything else on the page stays within the Romantic Blend — Orange appears once, boldly, as the action

EXAMPLE APPLICATION — Donation Page Anatomy:
├─ Page background: Pale Blush (#F5CDD0)
├─ Hero heading ("Help Bella Find a Home"): Deep Plum (#826E8B), Cormorant Garamond, per H1 spec
├─ Hero pet photo: rounded corners per Section 9 radius scale, thin Soft Pink border
├─ Donation progress bar: track in Blush, fill in Rose, percentage label in Deep Plum
├─ Suggested amount buttons ($10/$25/$50): Soft Pink background, Deep Plum text, Rose border on hover
├─ Primary CTA ("Donate Now"): Orange background, white bold Sora text, standard button hover-lift (Section 8)
└─ Testimonial/story card: white surface, Soft Pink left border accent, Deep Plum quote text

CSS VARIABLES:
:root[data-theme="donation"] {
  --donation-deep: #826E8B;
  --donation-rose: #EB6E9B;
  --donation-soft: #E69CBA;
  --donation-blush: #F4B3C7;
  --donation-base: #F5CDD0;
  --donation-cta: #ED7624;        /* mandatory brand anchor */
  --donation-cta-text: #ffffff;
}

### PALETTE A-2: "MAGENTA BLOOM" — Emotional & Donation Pages (Option 2 of 2)

🐾 USE CASE: The same page types as Romantic Blend above — donation, adoption stories, rescue appeals,
sponsorship flows. Choose this instead of Romantic Blend when a page calls for a bolder, more saturated
emotional tone rather than a soft muted one — both are equally valid; pick based on the specific story or
campaign's energy, not at random within the same page.

Why this palette works here: where Romantic Blend is soft and muted, Magenta Bloom is vivid and saturated
— it reads as passionate and urgent rather than gentle. This suits high-energy campaigns (an urgent rescue
appeal, a matching-donation deadline) better than a quiet, muted palette would.

THE FOUR COLORS & THEIR ROLES (lightest to darkest):

1. LIGHT PINK: #E185B1
   ├─ Role: Base/page background — the lightest tone in the set
   ├─ Use for: Overall page background, hero section base
   └─ Pairs with: Deep Purple or Primary Text on top of it

2. MAGENTA: #DB0F81
   ├─ Role: Emotional highlight — the most saturated, energetic color in the set
   ├─ Use for: Secondary buttons, progress bar fill, urgent tag badges ("Matching Gift Ends Soon"), icons
   └─ Pairs with: White text on top of it

3. PURPLE-MAGENTA: #91288D
   ├─ Role: Mid-dark accent — supporting UI and secondary headings
   ├─ Use for: Card borders, section backgrounds, H2/H3 headings
   └─ Pairs with: White text on top of it

4. DEEP PURPLE: #622D8F
   ├─ Role: Anchor/dark color — primary headline text, nav bar, footer
   ├─ Use for: H1 headings, primary navigation, dark section backgrounds
   └─ Pairs with: White or Light Pink text on top of it

WHERE THE ORANGE ANCHOR GOES ON THIS PALETTE:
├─ The primary "Donate Now" / "Sponsor This Pet" button = Orange (#ED7624) background, white text
└─ As with Romantic Blend, Orange appears once, boldly, as the single primary action — everything else
   stays within the four Magenta Bloom tones

CSS VARIABLES:
:root[data-theme="donation-bloom"] {
  --bloom-light: #E185B1;
  --bloom-magenta: #DB0F81;
  --bloom-purple: #91288D;
  --bloom-deep: #622D8F;
  --bloom-cta: #ED7624;           /* mandatory brand anchor */
  --bloom-cta-text: #ffffff;
}

### PALETTE B: "LAVENDER MIST" — Admin Dashboard & Internal Tools

Note on naming: "Lavender Mist" is the name assigned by Design for internal reference — use this name in
all future documentation and handoffs so everyone refers to it consistently.

🖥️ MANDATORY USE CASE: Internal admin dashboards, merchant analytics panels, order/inventory management
screens, and any internal tool used by staff rather than customers. This assignment is fixed — do not
substitute another palette for these page types.

Why this palette works here: a cool blue-to-lavender-to-pink gradient of closely related pastel tones is
calm and low-fatigue for screens staff will stare at for long shifts. Critically, five *distinct but
harmonious* hues make it an excellent chart/legend palette — data series stay visually separable without
looking chaotic, which is the opposite goal of the Romantic Blend (which prioritizes emotional warmth
over data legibility).

THE FIVE COLORS & THEIR ROLES:

1. ICE BLUE: #BDCFFF
   ├─ Role: Primary chart/data color, coolest tone in the set
   ├─ Use for: Primary line/bar in charts, active nav item highlight, primary data emphasis
   └─ Pairs with: Navy (#10214b) text on top of it

2. PERIWINKLE: #B8C0FF
   ├─ Role: Secondary chart/data color
   ├─ Use for: Secondary chart series, secondary status badges, hover state on Ice Blue elements
   └─ Pairs with: Navy text on top of it

3. LAVENDER: #C8B6FE
   ├─ Role: Tertiary chart/data color, general accent
   ├─ Use for: Third chart series, sidebar section dividers, tag/label backgrounds
   └─ Pairs with: Navy text on top of it

4. ORCHID: #E7C5FF
   ├─ Role: Quaternary chart/data color, softer accent
   ├─ Use for: Fourth chart series, table row hover highlight, low-priority status indicators
   └─ Pairs with: Navy or Primary Text on top of it

5. PALE PINK: #FED5FF
   ├─ Role: Fifth chart/data color, lightest panel background
   ├─ Use for: Fifth chart series, card/panel backgrounds, empty-state illustration backgrounds
   └─ Pairs with: Navy or Primary Text on top of it

WHERE THE ORANGE ANCHOR GOES ON THIS PALETTE:
├─ Primary action buttons that require urgency or attention: "Export Report," "Approve," "Publish," or any
   button representing an irreversible/important admin action = Orange background, white text
├─ Critical alert badges (e.g., "Low Stock," "Payment Failed") can use Orange to cut through the cool
   pastel field precisely because Orange contrasts sharply against blues/purples — this makes it the most
   effective color in the whole system for drawing the eye to something that needs action
└─ Do not use Orange for routine/neutral UI (pagination, filters, non-critical labels) — reserve it so it
   keeps its "pay attention" meaning

EXAMPLE APPLICATION — Admin Dashboard Anatomy:
├─ Page background: White (#ffffff) or Background (#f9f7f4) per core system — Lavender Mist is an ACCENT
   layer here, not the base, since admins need long-session neutrality more than donation pages do
├─ Sidebar active item: Ice Blue background, Navy text/icon
├─ KPI cards (Total Orders, Revenue, Active Pets): white surface, top border in rotating Lavender Mist
   colors (one per card) so each metric is visually distinct at a glance
├─ Revenue/analytics chart: 5-series line or bar chart using all five Lavender Mist tones in the fixed
   order above (Ice Blue → Periwinkle → Lavender → Orchid → Pale Pink) for consistent legend meaning
   across every chart in the platform
├─ Status badges: "Fulfilled" = Ice Blue, "Pending" = Lavender, "Cancelled" = Orchid, "Action Needed" =
   Orange
└─ Primary action button ("Export Report," "Save Changes"): Orange background, white text, standard hover-lift

CSS VARIABLES:
:root[data-theme="admin"] {
  --admin-ice: #BDCFFF;
  --admin-periwinkle: #B8C0FF;
  --admin-lavender: #C8B6FE;
  --admin-orchid: #E7C5FF;
  --admin-pale: #FED5FF;
  --admin-cta: #ED7624;           /* mandatory brand anchor */
  --admin-cta-text: #ffffff;
}

### PALETTE C: "MILK & PLUM" — Adoption & Application Forms (Option 1 of 3)

🐾 USE CASE (Option 1 of 3 — see Palettes C-2 and C-3 below for the alternatives): Adoption applications,
foster applications, volunteer sign-up forms, and any other long-form application/intake form. Workers may
choose any one of the three form palettes documented here — do not substitute an undocumented palette for
these form types.

Why this palette works here: this is a 2-color palette, not a 4-5 color page theme, on purpose. A form's
job is to get filled out accurately and without distraction — it is not the place for a multi-tone emotional
gradient. Milk and Plum give just enough warmth to feel human and on-brand without competing with the
form fields themselves.

⚠️ MINIMAL BY DESIGN — READ BEFORE APPLYING (applies to all three form palettes below): Unlike the
full-page palettes above, these must be used with restraint. A form styled like a marketing page fights
the user's ability to focus on filling it out. Do not add extra decorative accents, gradients, or multiple
background tints just because the colors are available — the constraint IS the design here (Section 14,
Practice 3, and Section 17's Minimal Design Principles).

THE TWO COLORS & THEIR ROLES:

1. MILK: #FFF3E6
   ├─ Role: Base — form background and/or input field background
   ├─ Use for: Page/card background behind the form, or input field fill if fields sit on a darker surface
   └─ Pairs with: Plum text on top of it

2. PLUM: #381932
   ├─ Role: Anchor — text, labels, borders, headings
   ├─ Use for: Form labels, headings, input borders, helper text, primary body copy
   └─ Pairs with: Milk or white text on top of it (when used as a background, e.g. a dark submit button)

WHERE THE ORANGE ANCHOR GOES ON THIS PALETTE:
├─ The single primary submit button ("Submit Application," "Apply to Adopt") = Orange (#ED7624)
   background, white text — and nowhere else on the form
└─ Do not add Orange to secondary elements (helper text, field borders, progress indicators) — on a
   minimal form, Orange should point at exactly one thing: the action that completes the form

EXAMPLE APPLICATION — Adoption Application Form:
├─ Page/card background: Milk (#FFF3E6)
├─ Form heading ("Apply to Adopt Bella"): Plum (#381932), Cormorant Garamond, per H1/H2 spec
├─ Field labels: Plum, Sora, per Section 6 label styling
├─ Input borders: Plum at reduced opacity (~30%), Gold focus ring per Section 6's existing focus state
├─ Helper/secondary text: Plum at reduced opacity (~60%), per Section 3's helper text spec
└─ Submit button: Orange background, white text — the only saturated color on the entire form

CSS VARIABLES:
:root[data-theme="application-form"] {
  --form-milk: #FFF3E6;
  --form-plum: #381932;
  --form-cta: #ED7624;            /* mandatory brand anchor */
  --form-cta-text: #ffffff;
}

### PALETTE C-2: "DUSTY MAUVE & IVORY MIST" — Adoption & Application Forms (Option 2 of 3)

🐾 USE CASE: Same form types as Palette C. Choose this over Milk & Plum when a form calls for a cooler,
more muted/sophisticated tone rather than Milk & Plum's warm cream-and-plum feel — e.g. a volunteer or
foster application where a slightly more neutral, calm tone fits better than a warm one.

THE TWO COLORS & THEIR ROLES:

1. IVORY MIST: #F0EDE9
   ├─ Role: Base — form/page background
   ├─ Use for: Page or card background behind the form
   └─ Pairs with: Dusty Mauve text on top of it

2. DUSTY MAUVE: #776B75
   ├─ Role: Anchor — text, labels, borders, headings
   ├─ Use for: Form labels, headings, input borders, helper text, primary body copy
   └─ Pairs with: Ivory Mist or white text on top of it (when used as a background)

WHERE THE ORANGE ANCHOR GOES ON THIS PALETTE:
└─ The single primary submit button = Orange (#ED7624) background, white text — same restraint rule as
   Palette C applies here

CSS VARIABLES:
:root[data-theme="application-form-mauve"] {
  --form2-base: #F0EDE9;
  --form2-anchor: #776B75;
  --form2-cta: #ED7624;           /* mandatory brand anchor */
  --form2-cta-text: #ffffff;
}

### PALETTE C-3: "PALE ROSE & BURNT COCOA" — Adoption & Application Forms (Option 3 of 3)

🐾 USE CASE: Same form types as Palette C. Choose this when a form calls for a warmer, more earthy/homely
tone — this pairing tends to read as the most inviting of the three, well suited to an adoption
application specifically, where a warm first impression matters.

THE TWO COLORS & THEIR ROLES:

1. PALE ROSE: #F3DFD8
   ├─ Role: Base — form/page background
   ├─ Use for: Page or card background behind the form
   └─ Pairs with: Burnt Cocoa text on top of it

2. BURNT COCOA: #6C4332
   ├─ Role: Anchor — text, labels, borders, headings
   ├─ Use for: Form labels, headings, input borders, helper text, primary body copy
   └─ Pairs with: Pale Rose or white text on top of it (when used as a background)

WHERE THE ORANGE ANCHOR GOES ON THIS PALETTE:
└─ The single primary submit button = Orange (#ED7624) background, white text — same restraint rule as
   Palette C applies here. Note that Burnt Cocoa and Orange are close in hue family (both warm brown-orange
   tones) — keep the submit button's Orange fully saturated so it still reads as distinctly brighter and
   more urgent than the muted Burnt Cocoa text color around it

CSS VARIABLES:
:root[data-theme="application-form-cocoa"] {
  --form3-base: #F3DFD8;
  --form3-anchor: #6C4332;
  --form3-cta: #ED7624;           /* mandatory brand anchor */
  --form3-cta-text: #ffffff;
}

### ASSIGNMENT RULES FOR THESE PALETTES (READ CAREFULLY)

Unlike other multi-option design galleries in this document — where workers are free to pick any variant
shown — these assignments are FIXED, not a free choice, with two exceptions noted below:
├─ Donation / emotional / adoption-story PAGES → Romantic Blend OR Magenta Bloom (worker's choice between
   these two only — see the note under Magenta Bloom for how to decide)
├─ Admin dashboard / internal tool pages → Lavender Mist ONLY
├─ Adoption/application FORMS → Milk & Plum, OR Dusty Mauve & Ivory Mist, OR Pale Rose & Burnt Cocoa
   (worker's choice among these three only), applied minimally per the warning above
└─ Do not blend palettes together on the same page/form, and do not substitute the core Navy/Gold system
   on these specific contexts unless a future update to this document says otherwise.

Any other page type not explicitly named above (product pages, general marketing pages, storefronts, etc.)
continues to use the core brand palette from Section 2 unless a future thematic palette is documented here
for that page type.

ACCESSIBILITY CHECK FOR ALL PALETTE GROUPS:
├─ Always verify text-on-background contrast before shipping — the "Pairs with" line under each color
   above is the tested safe combination; do not freelance new text/background pairings from these palettes
├─ Orange CTA buttons must always use white text, never Orange text on a pastel or Milk background (fails
   contrast)
└─ For chart palettes (Lavender Mist), never rely on color alone to distinguish data series — pair with
   labels, patterns, or a visible legend for colorblind accessibility

## 21. SUPPORT & REFERENCE

📚 QUICK REFERENCE:

When implementing something, ask:
├─ "What color should this be?" → Check Color Palette section
├─ "What font size?" → Check Typography Hierarchy
├─ "How should this animate?" → Check Animation section
├─ "Should this use emoji?" → NO! Use SVG instead
├─ "What's the transition timing?" → 0.3s cubic-bezier(.4,.0,.2,1)
├─ "Is this mobile responsive?" → Test at 640px breakpoint
└─ "Does this follow the design?" → Use Implementation Checklist

COMMON COLORS TO COPY:

Navy: #10214b
Gold: #d7bd88
Warm Gray: #d0c3ba
Chocolate: #3d2817
Orange: #ED7624
Background: #f9f7f4
White: #ffffff
Border: rgba(16,33,75,0.12)
Muted: #7a6d5f

COMMON CSS TO COPY:

Standard Transition:
transition: all 0.3s cubic-bezier(.4,.0,.2,1);

Input Styling:
border: 1.5px solid var(--border);
border-radius: 9px;
padding: 11px 14px;

Button Gradient:
background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);

Modal Box (Section 5):
background: #ffffff;
border-radius: 12px;
box-shadow: 0 20px 60px rgba(0,0,0,0.3);

## 22. SOURCING LIBRARIES BY USE CASE

> **See also:** Section 11 for the three primary locked icon libraries these sources are only a fallback for, and Section 3 for the locked font system these font sources must never override.

📚 WHAT THIS SECTION IS FOR

A consolidated reference of every sourcing option available to workers — Figma plugins, free icon
libraries, and free/freemium websites for raw design assets (fonts, mockups, inspiration, 3D packs, colors,
PNGs, icons, illustrations, stock photography). This is a bibliography, not a set of endorsements Design
has personally vetted. Anything pulled from these sources must still conform to this document's locked
specs — a font found on dafont.com doesn't override Section 3's four-font system, a color found on
coolors.co doesn't override Section 2 or Section 20.

### FIGMA PLUGIN TOOLKIT

Figma plugin categories and tool names workers can pull from to speed up production work. Evaluate any
plugin's actual output against this document's locked specs before using it, not the other way around.

TYPOGRAPHY TOOLS:
├─ Nisa Text, Fontpair, Typestyles, Typescales, Bullets, Font Explorer
└─ Use these to explore layout/pairing ideas or generate a type scale draft — the final font choice on any
   PetCenter page must still come from our locked four-font system (Cormorant Garamond, Sora, Bitter,
   Bodoni Moda — Section 3). These tools are for exploration, not for sourcing a fifth font

COLOR PALETTE TOOLS:
├─ Huge Icon, Dopely Colors, UI Color Palette, Coolors, Supa Palette, Realtime Colors
└─ Use these for early-stage exploration or client mood boards outside the PetCenter system. Any palette
   shipped on an actual PetCenter page must come from Section 2 (core brand palette) or Section 20
   (page-specific palettes) — these tools do not override that

ICON TOOLS:
├─ Huge Icon, Iconpark, Anti Icons, Phosphor, Iconduck, ion icon
└─ ⚠️ Check the SVG Icon Library doc first (see Section 11's sourcing notice) — ask the CEO to add a
   needed icon there before reaching for these. Supplementary to the icon libraries specified in Section
   11 (Heroicons, Tabler Icons, Font Awesome) only once both the SVG Icon Library doc and Section 11 have
   been checked — keep stroke weight and style consistent with whatever's already in use on the same page

COMPONENT/WORKFLOW TOOLS:
├─ Tablefy, Figr, UI Component, Handy Component, Token Studio, Master
└─ General Figma productivity plugins for building/organizing component libraries, tokens, and tables
   faster. No conflict with our system — these just speed up assembling what's already specified elsewhere
   in this document

### FREE ICON LIBRARIES

⚠️ Before using anything below: check the SVG Icon Library doc first (see Section 11's CEO-mandated
sourcing notice). It holds CEO-approved, pre-built, already-tuned icons for this system — ask the CEO to
add a needed icon there rather than sourcing independently. The libraries below remain a legitimate
fallback only once that doc and Section 11's three primary libraries (Heroicons, Tabler Icons, Font
Awesome) have both been checked and don't cover what's needed — match stroke weight and visual style to
whatever icon set is already in use on that page.

├─ Material Symbols (fonts.google.com/icons): Free, highly customizable icons from Google in multiple
   styles and weights — a strong first fallback since its customization range is unusually wide
├─ Noun Project (thenounproject.com): A very large general icon and photo collection — useful when a
   specific, less-common concept isn't covered by our primary libraries
├─ Lordicon (lordicon.com): Animated icon library — only relevant if a specific interaction calls for an
   animated icon rather than a static SVG; do not introduce animated icons into standard static UI just
   because they're available
├─ Atlas Icons (atlasicons.vectoplus.com): Customizable icon set including 3D-style icons — 3D icon
   treatments should be reserved for marketing/promotional contexts, not core product UI, to stay
   consistent with Section 11's flat SVG standard
├─ Heroicons (heroicons.com): Already one of our three primary libraries (Section 11) — listed here again
   simply because it's confirmed as a strong, actively maintained source
├─ Iconsax (iconsax.io): A large modern icon collection available in Figma, XD, Sketch, CSS, and Flutter
   formats — useful for cross-tool consistency when multiple workers are pulling from the same set
└─ Feather (feathericons.com): Simple, minimal, open-source icons — a good stylistic match for our existing
   flat SVG approach if Heroicons/Tabler don't have a specific icon needed

### GENERAL DESIGN ASSET SOURCES

⚠️ CAPABILITY NOTE FOR WORKERS: Whether a given worker can actually fetch/download from these sites
depends on that worker's own environment and tool access — some have live web/browser access, some don't.
Regardless of that, always route anything sourced from these sites back through this document's locked
specs (fonts, colors, icon style) before it ships on a PetCenter page.

FOR FONTS:
├─ fonts.google.com, dafont.com, fontsquirrel.com, fontshare.com, cufonfonts.com, fontspace.com,
   fontbundles.net/free-fonts, urbanfonts.com
└─ Exploration only — any font actually shipped on a PetCenter page must be one of the four locked fonts
   in Section 3 (Cormorant Garamond, Sora, Bitter, Bodoni Moda)

FOR MOCKUPS:
└─ mockupworld.co, unblast.com, freepik.com, pixeden.com, psdrepo.com, mockups-design.com, ls.graphics,
   pixelsurplus.com

FOR DESIGN IDEAS / INSPIRATION:
└─ mobbin.com, behance.net, awwwards.com, pinterest.com, uplabs.com, godly.website, dribbble.com, muz.li

FOR 3D PACKS:
├─ ui8.net/categories/freebies, icons8.com/illustrations/3d, iconscout.com/l/3d-abstract,
   icons8.com/l/3d-hands, gumroad.com/l/bamillu, freepik.com, 3dicons.co, shapefest.com
└─ Per Section 21's Atlas Icons note: 3D-style treatments are for marketing/promotional contexts, not core
   product UI — keep core UI on the flat SVG standard from Section 11

FOR COLORS:
├─ color.adobe.com, coolors.co, colorhunt.co, khroma.co, colors.muz.li, colors.dopely.top,
   flatuicolors.com, paletton.com
└─ Exploration/mood-boarding only — any color shipped on a PetCenter page must come from Section 2 (core
   palette) or Section 20 (page-specific palettes)

FOR PNGS:
└─ vecteezy.com, freepik.com, pngwing.com, pngtree.com, toppng.com, icons8.com, pikpng.com, pixabay.com,
   pngegg.com, freepng.com, kindpng.com

FOR ICONS:
├─ icons8.com, iconsax.io, Figma Community, iconscout.com/free-icons, fonts.google.com/icons,
   feathericons.com, flaticon.com, thenounproject.com
└─ Same rule as Section 21's Free Icon Libraries: fallback behind Section 11's three primary locked
   libraries (Heroicons, Tabler Icons, Font Awesome) — match stroke weight/style to what's already in use

FOR ILLUSTRATIONS:
└─ humaaans.com, undraw.co, icons8.com/illustrations, blush.design, opendoodles.com,
   uistore.design/items/ouch-free-illustrations, craftwork.design/downloads/category/freebies,
   drawkit.com

FOR STOCK IMAGES:
├─ unsplash.com, pexels.com, pixabay.com, freepik.com, kaboompics.com, freeimages.com,
   burst.shopify.com, stockvault.net
└─ Any photo used on a live page must still pass Section 17's High-Quality Imagery rule — clean and
   high-resolution, matching the minimal aesthetic; don't ship a low-quality pull just because it's free

## 23. 🚨 "GO WILD" MODE — UNRESTRICTED COLOR EXPERIMENTATION 🚨

> **See also:** Section 1's Rules & Warnings for the opt-in-only authorization requirement, and Sections 2/20 for exactly what this mode overrides when it is authorized.

⚠️⚠️⚠️ STOP AND READ BEFORE USING ANYTHING IN THIS SECTION ⚠️⚠️⚠️

EVERYTHING IN THIS SECTION IS OFF BY DEFAULT. This section directly contradicts and overrides the locked
color system built throughout the rest of this document (Sections 2, 18, 19, and 20). It exists for
exactly one purpose: specific creative exercises where the CEO explicitly tells a worker, in those words
or equivalent unmistakable language, to "go wild" on color for a specific task.

├─ DO NOT use anything in this section unless the CEO has explicitly authorized "go wild" mode for that
   specific task, in that specific conversation
├─ Absent that explicit authorization, every rule elsewhere in this document still applies in full — the
   locked brand palette (Section 2), the page-specific palettes (Section 20), and the functional color
   system (Section 18) remain mandatory as always
├─ This section is never the default interpretation of an ambiguous or creative-sounding request — "make
   this feel more vibrant" or "surprise me" is NOT go-wild authorization; only an explicit go-wild
   instruction is
└─ The purpose of this mode is deliberately unrestricted: to see what a worker produces on color when
   freed from our fixed palette, using only the general color theory below rather than PetCenter-specific
   rules. Nothing produced under this mode should be assumed reusable on a real PetCenter page unless the
   CEO separately approves that specific output for production use

### GENERAL COLOR THEORY TO APPLY WHEN "GO WILD" IS ACTIVE

THE 60-30-10 BALANCE RULE:
├─ Main Color: ~60% of the design — pick a neutral, easy-on-the-eyes color as the dominant tone
├─ Secondary Color: ~30% of the design — should contrast with the main color to create visual interest
└─ Accent Color: ~10% of the design — should contrast well against both other colors, used sparingly for
   emphasis and key actions

UNDERSTAND YOUR AUDIENCE:
├─ Color preference varies meaningfully by target audience — for example, blue tests as the most broadly
   liked color across genders, while preferences for colors like purple, pink, and orange show larger gaps
   between male and female audiences in general population studies
└─ Always consider who the specific design is for rather than assuming a universal "best" color choice

BLUE IS THE SAFEST DEFAULT:
├─ Across broad studies, blue tests as the single most liked color for both men and women — when in doubt
   and no other signal points elsewhere, a well-chosen shade of blue is a very safe anchor color
└─ Experiment with different shades/saturations of blue rather than defaulting to only one exact tone

COLOR PSYCHOLOGY — EMOTIONAL ASSOCIATIONS:
├─ Red: energy, attention, danger, warning, aggression
├─ Orange: energy, youth, creativity, fascination
├─ Yellow: enthusiasm, self-confidence, happiness, danger/warning, optimism
├─ Green: energy, health, nature, tranquility, relaxation
├─ Blue: trust, professionalism, experience, wisdom
├─ Purple: luxury, power, wealth, secrecy
├─ Pink: femininity, motherhood, innocence, youth, romance, gentleness
└─ Black: power, authority, discipline, elegance

General guidance: warm tones (red, yellow, orange) read as energetic; cool tones (blue, green) read as
calm. Consider cultural and personal associations, harmonize colors for a cohesive message, and use
contrast deliberately to highlight the most important elements.

## 24. CUSTOM DROPDOWN SYSTEM

> **Visual reference available:** the "Dropdown & Editor" tab in the companion HTML file renders the locked
> shell live. This is a sample, not a final asset — the shell's exact structure is locked exactly as
> described below; only the CSS presentation shown there is illustrative, never alter the structure itself
> when adapting for a real component.

📋 STATUS: This fills a real, previously-flagged gap. Dropdown/select styling was never formally specified
anywhere in this document before now — Section 6 covered general input styling, but not a dedicated
custom-dropdown component. Reviewed and integrated from an external draft, with corrections applied where
it drifted from what's already locked elsewhere in this system (details below).

### GOVERNING RULE — 5-ITEM VISIBLE CAP (applies to every dropdown panel, main or nested)

├─ No dropdown panel — main list or submenu — may show more than 5 items at once. Beyond 5, the panel
   becomes scrollable starting at item 6
├─ Why: keeps panels from growing tall enough to overrun the viewport, push page content unpredictably, or
   require awkward container-expansion math
└─ Math: each row is ~44-45px tall (padding + line-height + divider) → max-height = row_height × 5 ≈ 225px.
   Recompute per exact row height if a variant uses icons/checkboxes/two-line labels — never eyeball this

### THE LOCKED DROPDOWN SHELL — "MESSAGE BUBBLE" PANEL

🔒 LOCKED: The shell below (trigger, panel shape/shadow/radius, row structure, open/close animation) is
mandatory and identical across every dropdown in every PetCenter product. The only permitted adjustment is
resizing (width/height) when a specific feature genuinely needs more room. Everything INSIDE the shell —
list item content, icons, checkboxes, search bars — is open for whoever builds a specific variant.

**Why it's called "Message Bubble":** this isn't just a name — three specific traits combine to give the
panel that feel, and all three are locked:
1. **Fully rounded corners** (14px) rather than sharp or minimal corners — soft, not boxy
2. **A layered, soft drop shadow** (two shadow values stacked — a broad soft outer shadow plus a tighter
   inner one) rather than a single hard shadow — gives it depth without a harsh edge
3. **A simultaneous fade + scale + slide animation on open/close** — opacity 0→1, scale 0.98→1, and a
   translateY(-8px)→0 slide, all happening together over the same 0.2s — rather than any single motion
   (just a slide, or just a fade) on its own

Together, these three read as a soft bubble "popping" into view — closer to how a chat message bubble
appears than a hard dropdown snapping open. All three traits are part of the locked shell; a worker who
keeps the rounded corners and shadow but swaps in a plain slide-down (no fade, no scale) has NOT correctly
built this shell, even though it might look similar at a glance.

Required visual anatomy, top to bottom: trigger bar (label left, chevron right, chevron rotates 180° on
open) → 6px gap → panel (white, rounded corners **except a sharp, unrounded top-right corner** on the
chevron side, soft shadow, thin border) → rows (optional icon, label, hairline divider between rows, no
divider after the last row; hovered row gets a subtle tint; the selected/active row gets a 3px solid Gold
left accent bar).

**The sharp top-right corner is not optional or cosmetic — it's what makes this shell read as a "message
bubble" rather than a plain floating card.** A dropdown panel with four uniformly rounded corners is a
different, more generic shape; the asymmetry is the entire point of the name and must be preserved on every
instance of this component, regardless of which corner ends up "sharp" for a given trigger's icon/chevron
layout (mirror it to top-left if a specific trigger's interactive element sits on the left instead).

CSS (corrected — see notes below for what changed from the original draft):
.dropdown-trigger {
  display: flex; align-items: center; justify-content: space-between; gap: 10px;
  width: 100%; padding: 11px 14px;
  background: #ffffff;
  border: 1.5px solid rgba(16,33,75,0.12);
  border-radius: 9px;              /* corrected — see note 2 below */
  cursor: pointer; user-select: none;
  transition: all 0.2s cubic-bezier(.4,0,.2,1);   /* corrected — see note 3 below */
}
.dropdown-trigger:hover, .dropdown-trigger.active {
  border-color: var(--gold);
  box-shadow: 0 0 0 3px rgba(215,189,136,0.15);
}
.dropdown-trigger .chevron { transition: transform 0.2s cubic-bezier(.4,0,.2,1); }
.dropdown-trigger.active .chevron { transform: rotate(180deg); }

.dropdown-panel {
  /* position: fixed, NOT position: absolute — corrected 2026-09-22, see note 7 below. The
     original draft's `position: absolute; top: 100%` looks correct in isolation, but silently
     breaks the instant this component sits inside ANY ancestor with overflow: hidden (a
     rounded-corner card, a modal body, a section wrapper — extremely common), which clips the
     panel's lower rows at the ancestor's own edge with no visual warning. Because the panel
     no longer flows relative to the trigger under fixed positioning, top/left/width must be
     set in JS from the trigger's live getBoundingClientRect() every time the panel opens —
     see the required JS block below the CSS. This is not optional boilerplate; a `position:
     fixed` panel with no JS positioning renders in the top-left corner of the viewport. */
  position: fixed; margin-top: 6px; min-width: 100%;
  background: #ffffff;
  border: 1.5px solid rgba(16,33,75,0.10);
  border-radius: 14px 0 14px 14px;  /* sharp top-right corner is the message-bubble notch — not a rounding
                                        mistake; mirror to "0 14px 14px 14px" if the chevron is on the left */
  box-shadow: 0 12px 32px rgba(16,33,75,0.18), 0 2px 8px rgba(16,33,75,0.08);
  overflow: hidden;
  z-index: 999;                    /* sits below Modal's z-index: 9999 (Section 5) — correct stacking order */
  opacity: 0; visibility: hidden;
  transform: translateY(-8px) scale(0.98);
  transform-origin: top left;
  transition: opacity 0.2s ease, transform 0.2s cubic-bezier(.4,0,.2,1), visibility 0.2s; /* corrected */
  pointer-events: none;
}
.dropdown-panel.open {
  opacity: 1; visibility: visible; transform: translateY(0) scale(1); pointer-events: auto;
}
.dropdown-panel__scroll { max-height: 225px; overflow-y: auto; padding: 6px 0; }

.dropdown-panel__row {
  display: flex; align-items: center; gap: 10px; padding: 11px 16px;
  border-left: 3px solid transparent;
  border-bottom: 1px solid rgba(16,33,75,0.06);
  cursor: pointer;
  transition: background 0.15s ease, border-color 0.15s ease;
}
.dropdown-panel__row:last-child { border-bottom: none; }
.dropdown-panel__row:hover { background: rgba(16,33,75,0.05); }
.dropdown-panel__row.is-active, .dropdown-panel__row.selected {
  background: rgba(215,189,136,0.12);
  border-left-color: var(--gold);
  font-weight: 600;
}

REQUIRED JS — POSITIONING (this is not optional; without it the fixed-position panel above
renders in the wrong place or not visibly at all):
function openDropdownPanel(trigger, panel) {
  // Close any other open panel first — see "Multiple dropdowns open at once" below.
  document.querySelectorAll('.dropdown-panel.open').forEach(function (p) {
    if (p !== panel) p.classList.remove('open');
  });
  panel.classList.add('open');
  trigger.classList.add('active');
  trigger.setAttribute('aria-expanded', 'true');
  positionDropdownPanel(trigger, panel);
}
function positionDropdownPanel(trigger, panel) {
  var r = trigger.getBoundingClientRect();
  panel.style.left = r.left + 'px';
  panel.style.top = r.bottom + 6 + 'px';      // 6px gap, matching the shell's required anatomy
  panel.style.width = r.width + 'px';
  // Flip upward if there isn't enough room below (see SMART POSITIONING below) —
  // check available space and, if short, position above the trigger instead:
  var panelHeight = panel.offsetHeight;
  var spaceBelow = window.innerHeight - r.bottom;
  if (spaceBelow < panelHeight + 16 && r.top > panelHeight + 16) {
    panel.style.top = (r.top - panelHeight - 6) + 'px';
  }
}
function closeDropdownPanel(trigger, panel) {
  panel.classList.remove('open');
  trigger.classList.remove('active');
  trigger.setAttribute('aria-expanded', 'false');
}
// Recalculate on scroll/resize while open, per SMART POSITIONING below — a fixed-position
// panel does not move with the page on its own the way an absolute one would have.
window.addEventListener('scroll', function () {
  document.querySelectorAll('.dropdown-panel.open').forEach(function (panel) {
    var trigger = document.querySelector('[aria-controls="' + panel.id + '"]') || panel._trigger;
    if (trigger) positionDropdownPanel(trigger, panel);
  });
}, { passive: true, capture: true });
window.addEventListener('resize', function () {
  document.querySelectorAll('.dropdown-panel.open').forEach(function (panel) {
    var trigger = document.querySelector('[aria-controls="' + panel.id + '"]') || panel._trigger;
    if (trigger) positionDropdownPanel(trigger, panel);
  });
});

SMART POSITIONING (required on every instance): the panel must flip upward when there isn't enough
viewport room below the trigger, and clamp horizontally so it never runs off the right edge on mobile —
recalculate on scroll/resize while open (see the JS block above), and close the panel if the trigger
scrolls out of view.

COMMON BUGS THIS SHELL MUST SOLVE (do not reintroduce these):
├─ Clipped by a parent's `overflow: hidden` — this is why the panel above is `position: fixed`
   with JS-computed coordinates rather than `position: absolute; top: 100%`. An absolutely-
   positioned panel is still clipped the instant it sits inside any ancestor with overflow:
   hidden (a rounded-corner card is enough), silently cutting off the panel's lower rows —
   most visibly, the last row in a list long enough to need scrolling — with no visual error
   or console warning. This exact failure shipped once already on a real page (a quiz's breed
   picker sitting inside a rounded card) before being caught and fixed; use the corrected CSS
   and JS above rather than the plain-absolute version to avoid reintroducing it
├─ Multiple dropdowns open at once — always close all other open panels before opening a new
   one (handled in `openDropdownPanel()` above)
├─ Doesn't close on outside click or Escape key — every instance must bind both
└─ Janky repositioning on scroll/resize — recalculate position while open (handled above),
   close if trigger scrolls out of view entirely

Mobile: keep the same visual shell on mobile — don't swap to a full-screen sheet unless a specific
variant explicitly requires it. Row touch targets stay at minimum 44×44px per Section 10.

### FOUR VARIANTS (content inside the locked shell — flexible, build to fit the specific use case)

Each of these is a general-purpose interaction pattern, not tied to any specific data type (locations,
products, categories, staff — any dataset). Don't scope a variant's name or documentation to whatever
subject matter it happened to be demonstrated with.

VARIANT A — Searchable Single-Select: a search input pinned above the row list, filtering in real time
(case-insensitive substring match). Still obeys the 5-item cap on filtered results. Show a lightweight
"No results" row when nothing matches. Selecting a row sets the trigger value and closes the panel.

VARIANT B — Multi-Select Checklist (no search): a checkbox per row, whole-row click toggles it (not just
the checkbox hitbox), multiple rows selectable at once. Trigger label summarizes the selection (e.g.,
"3 selected") once the panel closes.

VARIANT C — Multi-Select With Search: Variant A's search combined with Variant B's multi-select. Trigger
label pattern: show the first selected item's name, then "& N MORE" once more than one is selected
(zero selected → placeholder text; exactly one → just that item's name).

VARIANT D — Nested Submenu (🔒 structural exception — this variant's layout, not just the outer shell, is
locked): a row that owns child options opens a second scrollable pane beside it, inside the same panel,
expanding the panel's total width. The sub-pane must be visually differentiated from the main pane (a
distinct tint or divider — exact color is flexible) and has its own independent 5-item scroll cap. Because
expanding can run the panel off a screen edge, mandatory flip logic applies: if there isn't room to expand
right (and there IS more room on the left), the submenu opens left instead — test this specifically on a
real mobile viewport, since a panel placed near a screen edge and then expanded by a submenu is the most
common dropdown bug on the whole site. If neither side has enough room on a small screen, cap the
submenu's max-width to the remaining viewport space rather than letting it overflow.

✅ REDIRECT NOTICE ON ALL FOUR VARIANTS — RESOLVED: the original draft instructed workers not to use any of
these four variants for filter/sort features, redirecting instead to "our documented Sheet Panel feature."
That component is now fully specified in **Section 30, Bottom Sheet, Filter Button & Search Bar** — a
dark-themed, full-width, drag-to-close bottom sheet paired with a pill-shaped Filter trigger, built
specifically for filter/sort UIs. This is a different thing from Section 13's free-choice Sheet mobile-menu
*style* (Type 5 in that section's gallery) — Section 13's Sheet is one option in a navigation-pattern
gallery; Section 30's Bottom Sheet is the locked, mandatory component for filter/sort panels specifically.
Do not substitute one for the other. Any filter or sort UI must use Section 30 as written.

### CORRECTIONS MADE DURING INTEGRATION

1. FONT: The original draft set the dropdown wrapper to `font-family: 'DM Sans', sans-serif` as the
   primary font. DM Sans is our documented FALLBACK for Sora (Font 5, Section 3), never a primary UI font
   on its own. Corrected to Sora, with DM Sans remaining the fallback per Section 3's existing rule.

2. TRIGGER BORDER-RADIUS: The draft used 9px in its original v1 content, then silently changed to 10px in
   the v2 "locked" shell with no stated reason. Per Section 9's radius scale, 9px is explicitly "Form
   inputs, moderate components" — the correct category for a dropdown trigger. 10px is "Cards, moderate
   containers" — the wrong category. Reverted to 9px. The panel's 14px radius was already correct
   ("Form sections, major containers") and needed no change.

3. TRANSITION TIMING: The draft introduced 0.22s-0.25s timings for the panel/trigger with no stated
   relationship to this system's established 0.3s cubic-bezier standard (Section 8). Rather than leave an
   unexplained third timing value floating in the system, this was standardized to 0.2s — a value that
   already had precedent in this same component's row-hover states and elsewhere in this document. This
   formally establishes a two-tier timing system: 0.3s for major/page-level transitions (modals, hover
   lifts, mode switching), 0.2s for compact/dense UI feedback (dropdown open/close, row hover, list items).

4. BUG FIX — STALE CONTAINER-EXPANSION MATH: The draft's newer "5-item, 225px" governing rule was never
   propagated to its own older Container Expansion feature or Performance Tips note, both of which still
   referenced the pre-v2.0 figure of 6 items / 180px. If built as originally written, a container
   auto-expanding to fit the panel would undercalculate by 45px and clip the 5th visible row. Any worker
   implementing container expansion for this component must use 225px, not 180px, and "5 items," not "6."

5. Z-INDEX: 999 is accepted as reasonable for a dropdown-level element, and it correctly sits below the
   Modal's existing z-index: 9999 (Section 5) in the stacking order. Section 30's Bottom Sheet system later
   added two more real values — backdrop 1400, sheet 1500 — which also sit correctly below the Modal. That
   said, a full documented z-index hierarchy (modals > sheets > toasts > dropdowns > tooltips, etc.) still
   does not exist beyond these four isolated values — established as a genuine future gap, not a blocker for
   any of these individual components. See Section 1's z-index warning for the current, up-to-date summary
   of exactly which values exist.

6. ACCESSIBILITY — UNDERSPECIFIED: The draft's accessibility notes mention only Tab-key navigation and a
   hidden native `<select>` for screen readers. A dropdown of this complexity (searchable, multi-select,
   nested) needs more: arrow-key navigation within an open panel, Enter/Space to select, Escape to close
   (already covered above), and `aria-expanded` on the trigger reflecting open/closed state. Treat the
   original accessibility notes as a starting point, not a complete spec — full keyboard/ARIA behavior
   should be verified before this ships on any real form.

7. BUG FIX — PANEL SILENTLY CLIPPED BY `overflow: hidden` ANCESTORS (2026-09-22): The CSS originally
   documented here used `position: absolute; top: 100%` for `.dropdown-panel`, which this section's own
   "COMMON BUGS THIS SHELL MUST SOLVE" list already named as something the shell "must solve" — but the
   CSS actually given never implemented that fix, so any worker who copied it as written reintroduced the
   exact bug the prose warned against. This shipped on a real page (a quiz page's breed-picker dropdown,
   sitting inside a rounded-corner card with `overflow: hidden`) before being caught: the panel opened in
   the right place but its lower rows — including, most visibly, the true last row of a long list — were
   silently sliced off at the card's own edge, with no console error or visual glitch to signal why.
   Corrected to `position: fixed`, with panel `top`/`left`/`width` now set from the trigger's live
   `getBoundingClientRect()` in JS every time the panel opens (see the CSS and the new required JS block
   above). This is a structural fix to the locked shell itself, not a per-instance workaround — every
   dropdown built from this section going forward should use the corrected CSS and JS as written, not the
   plain-absolute version. The same fix, plus a working test file, is also documented independently in the
   SVG Icon Library doc's own Dropdown Fix section, in case this file is skipped for being too large.

## 25. RICH TEXT EDITOR (WYSIWYG) FOR LONG-FORM FIELDS

> **Visual reference available:** the "Dropdown & Editor" tab in the companion HTML file renders the
> approved 13-control toolbar live. This is a sample, not a final asset — icon choices shown there are
> illustrative, not locked.

📋 STATUS: Fills a real gap — a plain `<textarea>` gives a user nothing to work with for a genuine
long-form written response. Reviewed and integrated from an external draft (again produced by another AI
due to the image upload limit); overall quality was strong and its exact brand colors, font, and transition
timing all matched this system correctly on the first pass. Two small corrections applied, noted below.

⚠️ SCOPE — READ BEFORE USING: This is for long-form narrative fields ONLY — a field explicitly meant to
capture a full written response of a paragraph or more (e.g. "Tell us about your previous experience with
pets"). Never apply this to short-answer fields — names, phone numbers, single-line inputs, dropdown-driven
fields, date pickers. If a field can be answered in one sentence or less, it does not need this component;
use the standard input styling from Section 6 instead.

### TECHNOLOGY: QUILL.JS

PetCenter pages are hand-coded static HTML/PHP uploaded to hosting directly — no CMS, no build pipeline.
That rules out CMS-bundled editors (like TinyMCE, which assumes a WordPress-style environment). Quill.js
is the chosen library because it loads from a plain CDN tag with no build tools required, is free and
actively maintained, has a fully per-button configurable toolbar (so only the approved controls below ship,
not a generic default set), and syncs cleanly into a plain HTML `<form>` via a hidden field. Do not
substitute a different editor library without sign-off — implementation is written and tested against
Quill specifically.

### APPROVED TOOLBAR — PHASE 1 (13 CONTROLS)

A deliberately restrained set — this is a narrative textarea, not a full document editor.

| # | Control | Why it's included |
|---|---|---|
| 1 | Bold | Basic emphasis |
| 2 | Italic | Basic emphasis |
| 3 | Link | Reference an external source or URL within a response |
| 4 | Bullet list | Natural for listing pets, experiences, etc. |
| 5 | Numbered list | Natural for sequential/step answers |
| 6 | Blockquote | Useful when quoting something (e.g. a previous vet's note) |
| 7 | Align left | Layout flexibility |
| 8 | Align center | Layout flexibility |
| 9 | Align right | Layout flexibility |
| 10 | Text color | Layout flexibility |
| 11 | Undo | Standard, expected editing control |
| 12 | Redo | Standard, expected editing control |
| 13 | Clear formatting | Lets the user reset a paragraph if formatting gets messy |

Note on Undo/Redo: Quill does not ship default toolbar buttons for these — its History module runs
internally but isn't exposed as a button out of the box, so custom buttons must be wired in explicitly
(see implementation code below) — do not assume this comes free.

📌 ROADMAP: A Phase 2 addendum will later expand this toolbar toward a much fuller feature set. Do not
build Phase 2 features now — this spec governs Phase 1 only. Wait for that documentation before adding
anything beyond these 13 controls to any field using this component.

### REQUIRED VISUAL STRUCTURE

├─ Header row: field label on the left, live character counter on the right
├─ Toolbar row: the 13 approved controls, left-to-right, sitting directly on top of the writing area with
   no gap — one continuous bordered box, toolbar and content area visually joined
├─ Content area: the writing surface, directly below the toolbar, inside the same bordered box
└─ Focus state: clicking into the editor puts a colored focus ring/border on the ENTIRE box (toolbar +
   content area together), not just the content area alone

CSS (corrected — see notes below):
.rte-field { margin-bottom: 20px; }
.rte-field__header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
.rte-field__label {
  font-family: 'Sora'; font-weight: 600; font-size: 13px; letter-spacing: 0.04em;  /* corrected — note 2 */
  color: var(--primary);
}
.rte-field__count { font-size: 12px; color: var(--muted, #7a6d5f); }

.rte-box {
  border: 1.5px solid var(--border);
  border-radius: 10px;             /* correct as-is — see note 3 */
  overflow: hidden;
  background: var(--white);
  transition: border-color 0.2s ease, box-shadow 0.2s ease;   /* matches this system's 0.2s tier */
}
.rte-box:focus-within {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(215,189,136,0.18);
}

.rte-toolbar.ql-toolbar.ql-snow {
  border: none; border-bottom: 1px solid var(--border);
  background: #faf9f7; padding: 8px 10px;
}
.rte-toolbar .ql-stroke { stroke: var(--primary); }
.rte-toolbar .ql-fill { fill: var(--primary); }
.rte-toolbar button:hover .ql-stroke, .rte-toolbar button.ql-active .ql-stroke { stroke: var(--orange); }
.rte-toolbar button:hover .ql-fill, .rte-toolbar button.ql-active .ql-fill { fill: var(--orange); }

.rte-editor.ql-container.ql-snow {
  border: none; font-family: 'Sora', sans-serif; font-size: 15px; min-height: 140px;
}
.rte-editor .ql-editor { padding: 14px 16px; }

### INTEGRATION NOTES FOR WORKERS

├─ This field's output is HTML, not plain text. The hidden field contains formatted HTML like
   `<p>Some <strong>bold</strong> text...</p>`, never treat it the same as a plain textarea value. If
   wiring this into a PDF summary, email template, server-side display, data export, or review panel —
   stop and flag it to Design first; how formatted HTML should render in that specific context needs a
   per-use-case decision, and a helper that HTML-escapes everything will silently break the formatting
├─ Multiple instances on one page are fully supported — each needs a unique field name; a single init call
   finds and initializes every instance on the page automatically, skipping any already initialized
├─ The character counter counts plain text length, not underlying HTML markup length, so it always
   reflects what the user actually typed, never inflated by tag overhead
├─ File attachments are NOT part of this component and must not be added to the toolbar without explicit
   sign-off — any page needing file uploads has its own dedicated upload flow; a second upload path inside
   this toolbar creates ambiguity about where a file ended up and is a data-integrity/support risk
└─ Server-side: sanitize this field's HTML before storing or rendering it — use a library like
   HTMLPurifier, or at minimum `strip_tags()` with a strict allowed-tags whitelist. Never output raw
   user-submitted HTML back to the browser unsanitized

### CORRECTIONS MADE DURING INTEGRATION

1. INTERNAL COUNT MISMATCH: The draft's intro described "12 formatting controls" while its own table listed
   13, and a later section of the same draft correctly referenced "13 approved controls." Standardized to
   13 throughout — the table was the accurate source, the intro line was the stale one.

2. LABEL STYLING DRIFT: The field label was set to 14px with no letter-spacing. Section 6's existing form
   label spec is 13px, Sora, weight 600, letter-spacing 0.04em. Corrected to match exactly rather than
   introduce a second, slightly different label style for this one component.

3. BORDER-RADIUS: 10px was checked against Section 9's radius scale and left unchanged — a rich text
   editor's full toolbar+content container reads as "moderate container" territory (10px), unlike a
   single-line input, so this one didn't need the correction the dropdown trigger needed.

4. ACCESSIBILITY — UNDERSPECIFIED: Same flag raised on the dropdown component (Section 24) applies here,
   more so — the draft doesn't address keyboard-only toolbar operation, focus movement between toolbar and
   editing surface, or screen-reader behavior for rich content. Treat this as a starting point requiring a
   full accessibility pass before shipping on a real form, not a finished spec.

Document status: Phase 1 of 2 — Phase 2 (an expanded full toolbar) is referenced but not yet approved for
build. Do not add controls beyond the 13 above until that future documentation exists.

## 26. SITE HEADER & FOOTER (GLOBAL CHROME)

> **Origin note:** this section, and Sections 27–32 that follow it, were migrated in full from an external
> project document (the adoption-system project summary) that had been maintaining its own duplicate
> "Shared Design System" notes. That source document now carries only a short pointer back to each section
> here — this is the single authoritative copy going forward. See Section 33's changelog for the migration
> entry.

The header and footer below are **one exact, shared implementation** used on every page site-wide — not a
theme or a starting point. Copy both blocks verbatim into any new page. Do not rebuild them from class names
alone and do not copy them from an individual page's source file, since any single page could have drifted
from the standard — this section is the canonical copy.

**See it live in:** every page on the site uses this header and footer — `index.html`, `cats.html`,
`dogs.html`, `birds.html`, `reptiles.html`, `small-mammals.html`, `pet-detail.html`,
`adoption-application.html`, `testimonials.html`, `status.html`, `blog-home.html`, `blog-single.html`,
`contact-us.html`, `rehome.html`. If this documentation and a live page ever visibly disagree, treat the
documentation as correct and flag the page for a fix — not the other way around, since individual pages can
drift out of sync with the standard over time.

### Header structure

├─ **Fixed, dual-logo scheme:** a full wordmark logo (`.pc-header__logo--desktop`) and an icon-only logo
   (`.pc-header__logo--mobile`) both exist in the markup at all times; CSS swaps which is visible at the
   900px breakpoint. Don't conditionally render one or the other in JS — both stay in the DOM.
├─ **Nav list with one dropdown:** "Our Pets" expands to the 5 category links (Dogs, Cats, Birds, Reptiles,
   Small Mammals).
├─ **Donate is visually distinct:** styled via `.nav-donate`, not a plain nav link — it should read as a
   call-to-action, not just another destination.
└─ **The hamburger button and mobile nav live inside `<header>`**, not as a sibling — see Section 27 for
   the full hamburger behavior this header contains.

### Footer structure

├─ **5-column grid:** `grid-template-columns: 2fr 1fr 1.2fr 1fr 1.3fr` — brand column, Our Pets, Information,
   Support, Visit Us. Before shipping any footer edit, verify the grid's column count still matches the
   actual number of `.pc-footer__col` divs in the HTML — a mismatched count (an earlier 4-column template
   with 5 real columns) was a recurring, easy-to-reintroduce bug.
├─ **Visit Us column** carries the full address and hours as plain text (see markup below).
└─ **No emoji anywhere in either block** — every icon is inline SVG, per Section 11.

### Canonical markup — copy exactly

**Header** (first element inside `<body>`):

```html
<header class="pc-header pc-header--solid" id="pcHeader">
  <a href="/" class="pc-header__logo pc-header__logo--desktop">
    <img src="/assets/images/logo-petcenter.png" alt="Pet Center">
  </a>
  <a href="/" class="pc-header__logo pc-header__logo--mobile">
    <img src="/assets/images/logo-petcenter-icon.png" alt="Pet Center">
  </a>
  <ul class="pc-header__nav">
    <li><a href="/">Home</a></li>
    <li class="has-drop"><a href="#">Our Pets</a>
      <ul>
        <li><a href="/pets/dogs/">Dogs</a></li>
        <li><a href="/pets/cats/">Cats</a></li>
        <li><a href="/pets/birds/">Birds</a></li>
        <li><a href="/pets/reptiles/">Reptiles</a></li>
        <li><a href="/pets/small-mammals/">Small Mammals</a></li>
      </ul>
    </li>
    <li><a href="/blog/">Blog</a></li>
    <li><a href="/testimonials/">Testimonials</a></li>
    <li class="nav-donate"><a href="https://donate.petcenterco.com" target="_blank" rel="noopener">Donate</a></li>
    <li><a href="https://docs.petcenterco.com/contact-us" target="_blank" rel="noopener">Contact</a></li>
    <li class="nav-signup"><a href="/rehome/">Sign Up</a></li>
  </ul>
  <button class="pc-hamburger" id="pcHamburger" aria-label="Open menu" aria-expanded="false">
    <span></span><span></span><span></span>
  </button>
  <nav class="pc-mobile-menu" id="pcMobileMenu" aria-label="Mobile navigation">
    <a href="/">Home</a>
    <a href="/pets/dogs/">Dogs</a>
    <a href="/pets/cats/">Cats</a>
    <a href="/pets/birds/">Birds</a>
    <a href="/pets/reptiles/">Reptiles</a>
    <a href="/pets/small-mammals/">Small Mammals</a>
    <a href="/blog/">Blog</a>
    <a href="/testimonials/">Testimonials</a>
    <a href="https://docs.petcenterco.com/contact-us" target="_blank" rel="noopener">Contact</a>
    <a href="/rehome/">Sign Up</a>
    <a href="https://donate.petcenterco.com" class="mobile-donate" target="_blank" rel="noopener">Donate</a>
  </nav>
</header>
```

**Footer** (last element before `</body>`, or immediately before the closing tag of the page's main content
wrapper):

```html
<footer class="pc-footer">
  <div class="pc-footer__inner">
    <div class="pc-footer__top">
      <div class="pc-footer__brand">
        <img src="/assets/images/logo-petcenter.png" alt="Pet Center">
        <p>Every pet deserves a loving home. Browse our available pets and start your adoption journey today.</p>
      </div>
      <div class="pc-footer__col">
        <h5>Our Pets</h5>
        <ul>
          <li><a href="/pets/dogs/">Dogs</a></li>
          <li><a href="/pets/cats/">Cats</a></li>
          <li><a href="/pets/birds/">Birds</a></li>
          <li><a href="/pets/reptiles/">Reptiles</a></li>
          <li><a href="/pets/small-mammals/">Small Mammals</a></li>
        </ul>
      </div>
      <div class="pc-footer__col">
        <h5>Information</h5>
        <ul>
          <li><a href="/">About Us</a></li>
          <li><a href="/blog/">Blog</a></li>
          <li><a href="/testimonials/">Testimonials</a></li>
          <li><a href="https://donate.petcenterco.com" target="_blank" rel="noopener">Donate</a></li>
          <li><a href="https://docs.petcenterco.com/health-guarantee/" target="_blank" rel="noopener">Health Guarantee</a></li>
          <li><a href="https://docs.petcenterco.com/shipping-cancellation/" target="_blank" rel="noopener">Shipping & Cancellation</a></li>
          <li><a href="https://docs.petcenterco.com/shipping-insurance-claims/" target="_blank" rel="noopener">Shipping Insurance</a></li>
          <li><a href="https://docs.petcenterco.com/data-retention-security/" target="_blank" rel="noopener">Data & Security</a></li>
        </ul>
      </div>
      <div class="pc-footer__col">
        <h5>Support</h5>
        <ul>
          <li><a href="https://docs.petcenterco.com/contact-us" target="_blank" rel="noopener">Contact Us</a></li>
          <li><a href="/rehome/">Sign Up</a></li>
          <li><a href="https://docs.petcenterco.com/returns-refunds/" target="_blank" rel="noopener">Returns & Refunds</a></li>
          <li><a href="https://docs.petcenterco.com/faq/" target="_blank" rel="noopener">FAQ</a></li>
        </ul>
      </div>
      <div class="pc-footer__col">
        <h5>Visit Us</h5>
        <p style="color:rgba(255,255,255,.7);font-size:13px;line-height:1.8;margin-bottom:0;">235-269 N Beverly Dr<br>Beverly Hills, CA</p>
        <p style="color:rgba(255,255,255,.7);font-size:13px;line-height:1.8;margin-top:10px;"><strong style="color:#d7bd88;">Hours:</strong><br>Mon – Fri: 7am – 9pm<br>Sat – Sun: 10am – 6pm</p>
      </div>
    </div>
    <div class="pc-footer__bottom">
      <span class="pc-footer__copy">&copy; 2026 Pet Center. All rights reserved.</span>
      <div class="pc-footer__links">
        <a href="https://docs.petcenterco.com/privacy-policy/" target="_blank" rel="noopener">Privacy Policy</a>
        <a href="https://docs.petcenterco.com/terms-of-service/" target="_blank" rel="noopener">Terms of Service</a>
      </div>
    </div>
  </div>
</footer>
```

> ⚠️ **Unresolved discrepancy — flag before editing the Visit Us copy:** the source material for this
> section disagreed with itself about the Visit Us address. One version states it should read "Golden
> Triangle Shopping Center, Section #257" in addition to the street address; the canonical markup pulled
> directly from a live deployed page above does **not** contain that text — only "235-269 N Beverly Dr,
> Beverly Hills, CA." This was not resolved during migration into this document. Confirm against the live
> site before changing the footer's address copy either way.

## 27. HAMBURGER MENU — SITE-WIDE MANDATORY IMPLEMENTATION

This is a **different thing** from Section 13's "Side Menu" (Type 2 of that section's free-choice gallery).
Section 13 documents a conceptual pattern a worker may choose among several options. This section documents
the one, real, already-built implementation that is **mandatory on every page, old or new** — it must be
copied verbatim, never reimplemented or varied. It lives inside the header markup from Section 26.

**See it live in:** the same page list as Section 26 — this hamburger implementation lives inside that same
shared header on every page.

This exact implementation solves three problems that recurred repeatedly before being fixed here: the last
menu item hiding behind a page's fixed bottom bar with no way to scroll to it, the menu's scroll "leaking"
into the page underneath once it hit its own end, and the hamburger icon never animating into an X on open.

### HTML structure

The mobile nav lives inside `<header>` (see the full markup in Section 26):

```html
<button class="pc-hamburger" id="pcHamburger" aria-label="Open menu" aria-expanded="false">
  <span></span><span></span><span></span>
</button>
<nav class="pc-mobile-menu" id="pcMobileMenu" aria-label="Mobile navigation">
  <a href="/">Home</a>
  ...
  <a href="https://donate.petcenterco.com" class="mobile-donate">Donate</a>
</nav>
```

### CSS — the hamburger icon (X animation)

```css
.pc-hamburger span {
  display: block; width: 24px; height: 2px;
  background: #fff; border-radius: 2px;
  transition: all 0.3s ease;
}
.pc-hamburger.active span:nth-child(1) { transform: rotate(45deg) translate(8px, 8px); }
.pc-hamburger.active span:nth-child(2) { opacity: 0; }
.pc-hamburger.active span:nth-child(3) { transform: rotate(-45deg) translate(7px, -7px); }
```

### CSS — the menu scroll fix (the important part)

```css
.pc-mobile-menu.open {
  max-height: calc(100vh - 68px);   /* full screen minus header height */
  overflow-y: auto;
  padding: 16px 0 calc(100vh - 68px - 56px);  /* the fix */
  overscroll-behavior: contain;      /* stops scroll bleeding into the page */
  -webkit-overflow-scrolling: touch; /* smooth momentum scroll on iOS */
}
```

**Why `padding-bottom: calc(100vh - 68px - 56px)`:** `100vh - 68px` is the menu's own visible height
(screen height minus the fixed header). Subtracting `56px` (the height of one menu link) pads the
scrollable content so that scrolling all the way down leaves exactly one item's worth of space at the
bottom — the **last link sits fully visible at the bottom edge of the menu**. This works regardless of
whether the page has a fixed bottom bar, because the padding is derived purely from the menu's own height,
not the page layout — that's what makes it portable to every page.

├─ **Critical gotcha:** never write two `padding` declarations on the same rule — a duplicate declaration
   silently wins and makes the fix appear to do nothing. Always check for duplicate property declarations
   first when a CSS fix doesn't seem to apply.
└─ `overscroll-behavior: contain` is what stops scroll from bleeding through to the page once the menu's
   own scroll range ends — without it, scrolling past the bottom of the menu scrolls the page behind it.

### JS — open/close with body scroll lock

```js
(function () {
  var hamburger = document.getElementById('pcHamburger');
  var mobileMenu = document.getElementById('pcMobileMenu');
  if (!hamburger || !mobileMenu) return;

  function openMenu() {
    hamburger.classList.add('active');
    mobileMenu.classList.add('open');
    hamburger.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';   // lock page scroll
  }
  function closeMenu() {
    hamburger.classList.remove('active');
    mobileMenu.classList.remove('open');
    hamburger.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';          // restore page scroll
  }

  hamburger.addEventListener('click', function (e) {
    e.stopPropagation();
    mobileMenu.classList.contains('open') ? closeMenu() : openMenu();
  });

  mobileMenu.querySelectorAll('a').forEach(function (link) {
    link.addEventListener('click', closeMenu);
  });

  document.addEventListener('click', function (e) {
    if (!e.target.closest('header') && !e.target.closest('.pc-mobile-menu')) {
      closeMenu();
    }
  });
})();
```

`document.body.style.overflow = 'hidden'` while open is what prevents the page from scrolling underneath at
all — combined with `overscroll-behavior: contain` on the menu itself, this fully isolates the menu's
scroll from the page's scroll in both directions.

> **Note on scroll-lock method:** this hamburger menu uses the simpler `document.body.style.overflow`
> toggle above, which is sufficient for its own use case. Section 30's Bottom Sheet system requires the
> more robust `pcLockBodyScroll()`/`pcUnlockBodyScroll()` position-fixed technique instead, because sheets
> are more prone to the iOS/Android dynamic-address-bar reflow issue that the simpler method doesn't catch.
> These are two independent systems that both touch `document.body` — in normal use they are never open at
> the same time, but don't assume one can substitute for the other.

**Any new page must use this exact CSS/JS, unmodified** — the padding-bottom formula is identical
everywhere since it's derived purely from the menu's own height, which is what makes it portable regardless
of what else is fixed to that page's viewport.

## 28. SIGNATURE PAD COMPONENT

A canvas-based, draw-to-sign input for any form needing a handwritten signature (adoption applications,
waivers, agreements).

**See it live in:** `adoption-application.html` (the "Applicant Signature" field).

├─ Renders as an HTML `<canvas>` the user draws on directly (touch or mouse).
├─ On save, the drawing is converted to a base64 PNG data URL and stored in a **hidden text input**
   (`<input type="hidden" name="signature">`) — it is deliberately **not** a file upload. The backend must
   read it from the form's POST body, decode the base64 string itself, and write it to disk server-side,
   rather than expecting it to arrive as an uploaded file (Section 7's File Upload Component does not apply
   here).
└─ Pair with a visible "Clear" control so a user can redraw before submitting — a signature pad with no way
   to clear a mis-drawn attempt is a usability dead end.

> **See Also:** Section 7 for the separate File Upload Component (actual uploaded files, e.g. ID documents),
> and Section 29 for how a signature typically gets folded into a generated PDF/preview.

## 29. PDF GENERATION & PREVIEW/DOWNLOAD MODAL SYSTEM

**See it live in:** `adoption-application.html` — the file "Preview" buttons in the upload fields, and the
sticky-footer "Download" action on the Review step.

This section documents two things specific to a form-with-PDF-output scenario, both built on top of
**Section 5's shared modal base** rather than duplicating it: (1) which of Section 5's three uses applies to
a file preview vs. a full-form preview, and (2) the client-side PDF generation itself, which Section 5 does
not cover.

### Mapping this scenario onto Section 5's uses

├─ **A single uploaded file's "Preview" button → Section 5's use B** (single-item preview). Title bar shows
   the file's name; no secondary action button, since there's nothing to generate — it's just viewing a file
   that already exists.
└─ **A form's own "Download"/"Preview" action (the whole filled-in form) → Section 5's use C** (full-content
   preview). Title reads something like "Application Preview"; the secondary action button is shown and
   reads "Download PDF" — clicking it runs the PDF generation below.

Do not reintroduce a separate lettering scheme for these two (an earlier draft of this section called them
"A" and "B", which collides with Section 5's own A/B/C — always refer to them as Section 5's use B and use C
to keep the two sections unambiguous together.

⚠️ **Any change to the shared modal must be tested against every consumer that uses it** — see Section 5's
own warning about this; it applies here without modification, since this section adds no CSS/HTML of its
own beyond what Section 5 already defines.

### Client-side PDF generation

Uses the `html2pdf.js` library to render a plain, off-DOM HTML node straight to a downloadable PDF in the
browser — no server round-trip required.

```js
function buildPdfNode(labelText) {
  const node = document.createElement('div');
  node.style.cssText = 'padding:24px;font-family:Sora,sans-serif;color:#10214b;';
  // Build the node's innerHTML from whatever content the PDF should contain —
  // typically the same summary markup already used for the on-page review/preview step,
  // so the PDF and the preview modal never drift out of sync with each other.
  node.innerHTML =
    '<h2 style="font-family:\'Cormorant Garamond\',serif;">' + labelText + '</h2>' +
    /* ...rest of the content... */
    '<p style="font-size:12px;color:#9ca3af;margin:0">Generated: ' + new Date().toLocaleString() + '</p>';
  return node;
}

function downloadPdf() {
  html2pdf()
    .set({
      margin: 0.4,
      filename: 'document-preview.pdf',
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: { scale: 1.8, useCORS: true, logging: false },
      jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    })
    .from(buildPdfNode('Preview Copy'))
    .save();
}
```

├─ **Reuse the review/summary markup, don't hand-duplicate it.** Building the PDF's content by cloning or
   re-reading whatever `innerHTML` already powers the on-page review step (rather than writing a second,
   separate content-generation function) is what keeps the downloaded PDF and the in-app preview from
   silently drifting apart as fields are added or changed later.
├─ `html2canvas: { scale: 1.8 }` is a deliberate quality/file-size balance — higher scales produce sharper
   text but meaningfully larger files and slower generation; don't raise it without a reason.
└─ Wire this function to Section 5's use-C action button via the `onAction` callback shown in Section 5's
   `openFullPreview()` — don't attach a second, separate click listener directly to the modal's button.

> **See Also:** Section 5 (Modal & Popup System) for the shared modal shell this section builds on, and
> Section 28 for how a Signature Pad's output typically feeds into the generated PDF.



## 30. BOTTOM SHEET, FILTER BUTTON & SEARCH BAR

> **This section resolves the "Sheet Panel" reference in Section 24.** The Custom Dropdown System's four
> variants redirect filter/sort use cases to a "Sheet Panel feature" — this is that feature, now fully
> specified. It is a different component from Section 13's free-choice Sheet menu style; do not substitute
> one for the other (see Section 24 and Section 27's note for the distinction).

A complete, copy-paste-ready system for the "pull-up panel" pattern: a pill-shaped **Filter button** that
triggers a bottom sheet, the **bottom sheet** itself (dark-themed, full-width, drag-to-close, reusable for
both a Filters UI and a Form UI), and a circular **Search button** that reveals an inline search bar with
debounced live filtering. All three are designed to be dropped into any page needing a pull-up panel without
re-deriving anything from scratch.

**See it live in:** `testimonials.html` (Filters panel and Review-submission form).

### Mistakes already made building this once — build from the corrected version below

1. **`document.body.style.overflow = 'hidden'` is not enough** to prevent scroll/layout issues when a sheet
   is open on mobile — it doesn't lock scroll *position*, and iOS Safari / Chrome Android with dynamic
   address bars can still visibly reflow behind a `position: fixed` sheet. Use the real scroll-lock in this
   section instead (`pcLockBodyScroll()`/`pcUnlockBodyScroll()`).
2. **Never shrink the sheet to a centered card on desktop** — full-width, edge-to-edge, on every screen
   size is the correct, intended behavior. An earlier centered-560px-card version was reverted.
3. **Copy inlined SVGs byte-for-byte** — never hand-retype path data from memory; a hand-reconstructed path
   is exactly how a logo once rendered with a visibly wrong extra shape.
4. **Mouse drag should only start from the sheet's handle bar**, while touch can drag from anywhere on the
   sheet — otherwise clicking into a text input inside the sheet can accidentally trigger a drag.
5. **"Clear" resets in-progress selections but must not close the panel** — only "Apply" commits and closes.
6. **The "×" inside a search bar must both clear the text and collapse/hide the bar** — clearing text while
   leaving the bar open reads as broken.
7. **Give the trigger buttons real contrast against the actual page background** — don't pair two similarly
   light/muted colors for a button's background and its own icon/text.
8. **Never hand-assemble nested-quoted HTML inside an inline `onerror` attribute** — use a named JS function
   called via `onerror="myFallbackFn(this)"`, built with DOM APIs, not string-concatenated HTML.

### Design tokens

```css
/* Buttons (filter pill, search circle) */
--btn-bg: #10214b;        /* dark navy — must contrast clearly against the page background */
--btn-fg: #d7bd88;        /* gold/accent — icon and text color */
--btn-hover-bg: #1a3070;
--btn-hover-border: #d7bd88;

/* Sheet (bottom pull-up panel) */
--sheet-bg: #121212;
--sheet-text: #ffffff;
--sheet-muted-text: rgba(255,255,255,.6);
--chip-bg: #1e1e1e;
--chip-border: rgba(255,255,255,.15);
--chip-selected-bg: #d7bd88;
--chip-selected-text: #10214b;
--sheet-clear-border: #d0c3ba;
--sheet-apply-bg: #d7bd88;
--sheet-apply-hover-bg: #b89968;

/* Search bar (light theme — sits on the page, not inside the dark sheet) */
--search-border: #d0c3ba;
--search-border-focus: #b89968;
```

The relationship to preserve: the same gold accent is used for (a) the filter/search button's icon/text on
its dark background, (b) a selected chip's background inside the dark sheet, and (c) the sheet's primary
Apply button — this is what makes the trigger, the panel, and the panel's primary action read as one
connected system.

### Part A — Filter button (pill-shaped trigger)

```html
<button class="pc-filter-btn" id="pcFilterBtn" type="button" aria-haspopup="true" aria-expanded="false">
  <svg class="pc-filter-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
    <line x1="4" y1="6" x2="20" y2="6"/><circle cx="9" cy="6" r="2" fill="currentColor" stroke="none"/>
    <line x1="4" y1="12" x2="20" y2="12"/><circle cx="15" cy="12" r="2" fill="currentColor" stroke="none"/>
    <line x1="4" y1="18" x2="20" y2="18"/><circle cx="11" cy="18" r="2" fill="currentColor" stroke="none"/>
  </svg>
  Filters
</button>
```

```css
.pc-filter-btn {
  display: inline-flex; align-items: center; gap: 7px;
  background: #10214b; color: #d7bd88; border: 1.5px solid transparent;
  border-radius: 999px; padding: 9px 18px;
  font-family: 'Sora', sans-serif; font-size: 13.5px; font-weight: 600;
  cursor: pointer;
  transition: border-color .2s ease, transform .15s ease, background .2s ease;
}
.pc-filter-btn:hover, .pc-filter-btn:focus-visible, .pc-filter-btn.active {
  border-color: #d7bd88; background: #1a3070; transform: translateY(-1px);
}
.pc-filter-btn:active { transform: translateY(0); }
.pc-filter-icon { flex-shrink: 0; }
```

Apply `.active` via JS while the sheet is open, so the button visibly stays "pressed" for the whole time
the panel is up, not just during the click.

### Part B — The bottom sheet ("pull-up panel")

One sheet pattern, reused for multiple purposes (a Filters UI and an entirely different Review-submission
Form UI) just by swapping what's inside `.pc-sheet-body`. The chrome (backdrop, handle, header, open/close
animation, drag-to-close, scroll-lock) stays identical and shared.

**Markup skeleton (reusable for any sheet content):**

```html
<div class="pc-sheet-backdrop" id="pcYourSheetBackdrop"></div>

<div class="pc-your-sheet" id="pcYourSheet" role="dialog" aria-modal="true" aria-label="Descriptive label">
  <div class="pc-sheet-handle"></div>
  <div class="pc-sheet-header">
    <h3>Sheet Title</h3>
    <button class="pc-sheet-close" id="pcYourSheetClose" aria-label="Close" type="button">&times;</button>
  </div>
  <div class="pc-sheet-body">
    <!-- ANY content: filter chips, a form, whatever -->
  </div>
  <!-- Footer is optional — only include for Clear/Apply-style actions -->
  <div class="pc-sheet-footer">
    <button class="pc-sheet-clear" type="button">Clear</button>
    <button class="pc-sheet-apply" type="button">Apply</button>
  </div>
</div>
```

Use a distinct class per sheet instance (e.g. `.pc-filter-sheet`, `.pc-review-sheet`) so multiple sheets can
exist on the same page independently, but give them all the shared structural CSS below (comma-separated
selectors).

**CSS — sheet chrome:**

```css
.pc-sheet-backdrop {
  position: fixed; inset: 0; background: rgba(0,0,0,.55);
  opacity: 0; visibility: hidden; transition: opacity .3s ease;
  z-index: 1400;
}
.pc-sheet-backdrop.show { opacity: 1; visibility: visible; }

/* List every sheet class you create, comma-separated, here */
.pc-filter-sheet, .pc-review-sheet {
  position: fixed; left: 0; right: 0; bottom: 0;
  background: #121212; border-radius: 18px 18px 0 0;
  z-index: 1500;
  max-height: 82vh; max-height: 82dvh; /* dvh overrides where supported — prevents size
                                          jumps when the mobile browser's address bar
                                          shows/hides */
  display: flex; flex-direction: column;
  transform: translateY(100%);
  transition: transform .35s cubic-bezier(.32,.72,0,1);
  box-shadow: 0 -8px 40px rgba(0,0,0,.4);
}
.pc-filter-sheet.open, .pc-review-sheet.open { transform: translateY(0); }

/* Do NOT add a desktop media query that shrinks this to a centered card —
   see mistake #2 above. Full-width edge-to-edge on every screen size. */

.pc-sheet-handle {
  width: 40px; height: 4px; background: rgba(255,255,255,.25);
  border-radius: 4px; margin: 10px auto 0; flex-shrink: 0;
}
.pc-sheet-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 20px 8px; flex-shrink: 0;
}
.pc-sheet-header h3 { font-family: 'Sora', sans-serif; font-size: 18px; color: #fff; font-weight: 700; }
.pc-sheet-close {
  background: none; border: none; color: #fff; font-size: 22px; line-height: 1;
  cursor: pointer; padding: 4px 8px; opacity: .8;
}
.pc-sheet-close:hover { opacity: 1; }
.pc-sheet-body { overflow-y: auto; padding: 8px 20px 20px; flex: 1; }

.pc-sheet-footer {
  display: flex; gap: 12px; padding: 16px 20px 22px;
  border-top: 1px solid rgba(255,255,255,.08); flex-shrink: 0;
}
.pc-sheet-clear, .pc-sheet-apply {
  flex: 1; padding: 13px; border-radius: 999px;
  font-family: 'Sora', sans-serif; font-size: 14px; font-weight: 700;
  cursor: pointer; border: 1.5px solid transparent;
}
.pc-sheet-clear { background: transparent; border-color: #d0c3ba; color: #d7bd88; }
.pc-sheet-clear:hover { border-color: #b89968; }
.pc-sheet-apply { background: #d7bd88; color: #10214b; }
.pc-sheet-apply:hover { background: #b89968; color: #fff; }
```

**Optional content pattern — filter chips (multi-select with checkmark):**

```css
.pc-chip-row { display: flex; flex-wrap: wrap; gap: 10px; }
.pc-chip {
  background: #1e1e1e; border: 1px solid rgba(255,255,255,.15);
  color: #e5e5e5; border-radius: 999px; padding: 9px 16px;
  font-family: 'Sora', sans-serif; font-size: 13px; cursor: pointer;
  display: flex; align-items: center; gap: 6px; transition: all .15s ease;
}
.pc-chip:hover { border-color: rgba(215,189,136,.5); }
.pc-chip.selected { background: #d7bd88; color: #10214b; border-color: #d7bd88; font-weight: 600; }
.pc-chip.selected::before { content: "✓"; font-weight: 700; }
```

Toggling `.selected` on click is enough — the checkmark appears automatically via `::before`.

**Behavior rule when using Clear/Apply:** chip selections live in a **draft/temporary state** while the
sheet is open, not applied to whatever the sheet is filtering until the user taps Apply. Apply commits the
draft and closes. Clear resets the draft but does not close the sheet. The × close button, backdrop click,
and drag-to-close all discard any unsaved draft and simply close, without applying it.

**JS — scroll lock (use this exact code, don't substitute plain `overflow:hidden`):**

```js
var _pcScrollLockY = 0;
var _pcScrollLockCount = 0; // supports multiple sheets that could overlap

function pcLockBodyScroll() {
  _pcScrollLockCount++;
  if (_pcScrollLockCount > 1) return;
  _pcScrollLockY = window.scrollY || window.pageYOffset || 0;
  document.body.style.position = 'fixed';
  document.body.style.top = (-_pcScrollLockY) + 'px';
  document.body.style.left = '0';
  document.body.style.right = '0';
  document.body.style.width = '100%';
}

function pcUnlockBodyScroll() {
  _pcScrollLockCount = Math.max(0, _pcScrollLockCount - 1);
  if (_pcScrollLockCount > 0) return;
  document.body.style.position = '';
  document.body.style.top = '';
  document.body.style.left = '';
  document.body.style.right = '';
  document.body.style.width = '';
  window.scrollTo(0, _pcScrollLockY);
}
```

Add globally, once, regardless of sheet count:

```css
html { overflow-x: hidden; width: 100%; }
body { overflow-x: hidden; width: 100%; position: relative; }
```

**JS — drag-to-close:**

```js
function enableDragToClose(sheetEl, handleEl, onClose) {
  var startY = 0, currentY = 0, dragging = false, sheetHeight = 0;

  function pointerDown(e) {
    dragging = true;
    startY = (e.touches ? e.touches[0].clientY : e.clientY);
    currentY = startY;
    sheetHeight = sheetEl.offsetHeight || 1;
    sheetEl.style.transition = 'none';
  }
  function pointerMove(e) {
    if (!dragging) return;
    currentY = (e.touches ? e.touches[0].clientY : e.clientY);
    var delta = Math.max(0, currentY - startY); // only allow dragging downward
    sheetEl.style.transform = 'translateY(' + delta + 'px)';
  }
  function pointerUp() {
    if (!dragging) return;
    dragging = false;
    sheetEl.style.transition = '';
    var delta = Math.max(0, currentY - startY);
    if (delta > sheetHeight * 0.25) { // dragged more than 25% of sheet height -> close
      sheetEl.style.transform = '';
      onClose();
    } else {
      sheetEl.style.transform = ''; // snap back open
    }
  }

  [handleEl, sheetEl].forEach(function(el) {
    if (!el) return;
    el.addEventListener('touchstart', pointerDown, { passive: true });
    el.addEventListener('mousedown', function(e) {
      // Mouse drag only starts from the handle — avoids interfering with
      // normal clicks/typing inside form fields elsewhere in the sheet.
      if (el === handleEl) pointerDown(e);
    });
  });
  document.addEventListener('touchmove', pointerMove, { passive: true });
  document.addEventListener('mousemove', pointerMove);
  document.addEventListener('touchend', pointerUp);
  document.addEventListener('mouseup', pointerUp);
}
```

**JS — wiring a sheet instance together:**

```js
(function(){
  var triggerBtn = document.getElementById('pcFilterBtn');
  var sheet = document.getElementById('pcYourSheet');
  var backdrop = document.getElementById('pcYourSheetBackdrop');
  var closeBtn = document.getElementById('pcYourSheetClose');

  function openSheet() {
    sheet.classList.add('open');
    backdrop.classList.add('show');
    triggerBtn.classList.add('active');
    pcLockBodyScroll();
  }
  function closeSheet() {
    sheet.classList.remove('open');
    backdrop.classList.remove('show');
    triggerBtn.classList.remove('active');
    pcUnlockBodyScroll();
  }

  triggerBtn.addEventListener('click', openSheet);
  closeBtn.addEventListener('click', closeSheet);
  backdrop.addEventListener('click', function(e){ if (e.target === backdrop) closeSheet(); });
  enableDragToClose(sheet, sheet.querySelector('.pc-sheet-handle'), closeSheet);
})();
```

Copy this IIFE once per sheet instance — only the element IDs differ between a Filters sheet and a
Review-form sheet built from the same pattern.

### Part C — Search button + inline search bar

```html
<button class="pc-search-btn" id="pcSearchBtn" aria-label="Search" type="button">
  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
    <circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
  </svg>
</button>

<!-- Placed in normal page flow, directly below wherever the button sits -->
<div class="pc-search-bar-wrap" id="pcSearchBarWrap">
  <div class="pc-search-bar">
    <input type="text" id="pcSearchInput" placeholder="Search..." autocomplete="off">
    <button class="pc-search-clear" id="pcSearchClear" aria-label="Clear search" type="button">&times;</button>
  </div>
</div>
```

```css
.pc-search-btn {
  width: 38px; height: 38px; border-radius: 50%;
  background: #10214b; color: #d7bd88; border: 1.5px solid transparent;
  display: flex; align-items: center; justify-content: center; cursor: pointer;
  transition: border-color .2s ease, transform .15s ease, background .2s ease;
}
.pc-search-btn:hover, .pc-search-btn.active { border-color: #d7bd88; background: #1a3070; transform: translateY(-1px); }
.pc-search-btn:active { transform: translateY(0); }

.pc-search-bar-wrap {
  max-height: 0; opacity: 0; overflow: hidden;
  transition: max-height .35s ease, opacity .35s ease; /* slow, deliberate fade+expand */
  margin-top: 0;
}
.pc-search-bar-wrap.open { max-height: 70px; opacity: 1; margin-top: 14px; }
.pc-search-bar {
  display: flex; align-items: center; gap: 10px;
  border: 1.5px solid #d0c3ba; border-radius: 999px;
  padding: 10px 18px; background: #fff;
  transition: border-color .2s ease;
}
.pc-search-bar:focus-within { border-color: #b89968; }
.pc-search-bar input {
  flex: 1; border: none; outline: none;
  font-family: 'Sora', sans-serif; font-size: 14px; color: #10214b; background: transparent;
}
.pc-search-bar input::placeholder { color: #b0a69a; }
.pc-search-clear {
  background: none; border: none; font-size: 19px; cursor: pointer;
  color: #7a6d5f; line-height: 1; padding: 2px 4px;
}
.pc-search-clear:hover { color: #10214b; }

/* Optional: highlight the matched term inside results */
.pc-search-highlight {
  background: rgba(215,189,136,0.4); color: #10214b; font-weight: 700;
  border-radius: 3px; padding: 0 1px;
}
```

The `max-height` + `opacity` transition (rather than toggling `display`) is what produces the smooth
fade-and-expand-down reveal — `display` itself can't be animated in CSS.

```js
(function(){
  var searchBtn = document.getElementById('pcSearchBtn');
  var wrap = document.getElementById('pcSearchBarWrap');
  var input = document.getElementById('pcSearchInput');
  var clearBtn = document.getElementById('pcSearchClear');
  var debounceTimer = null;
  var DEBOUNCE_MS = 450; // wait this long after the last keystroke before searching

  searchBtn.addEventListener('click', function() {
    var isOpen = wrap.classList.toggle('open');
    searchBtn.classList.toggle('active', isOpen);
    if (isOpen) {
      setTimeout(function(){ input.focus(); }, 150);
    } else {
      input.value = '';
      onSearchChange(''); // your own function to re-render results with no filter
    }
  });

  // The × INSIDE the bar must both clear text AND close/collapse the bar (mistake #6 above).
  clearBtn.addEventListener('click', function() {
    input.value = '';
    wrap.classList.remove('open');
    searchBtn.classList.remove('active');
    onSearchChange('');
  });

  input.addEventListener('input', function() {
    clearTimeout(debounceTimer);
    var value = input.value.trim();
    debounceTimer = setTimeout(function() {
      onSearchChange(value);
    }, DEBOUNCE_MS);
  });

  function onSearchChange(term) {
    // Replace with your actual filter/render logic. Keep it fast/local
    // (in-memory array filter) if possible — no need for a real network
    // request if all your data is already client-side.
  }
})();
```

**Optional "loading squares" animation while debounce is pending:**

```css
.pc-loading-squares { display: flex; gap: 8px; justify-content: center; padding: 60px 0; }
.pc-loading-squares span {
  width: 14px; height: 14px; background: #d7bd88; border-radius: 3px;
  animation: pcSquareMorph 1.2s ease-in-out infinite;
}
.pc-loading-squares span:nth-child(2) { animation-delay: .15s; }
.pc-loading-squares span:nth-child(3) { animation-delay: .3s; }
@keyframes pcSquareMorph {
  0%, 100% { transform: scale(1) rotate(0deg); border-radius: 3px; }
  50%      { transform: scale(1.3) rotate(45deg); border-radius: 6px; }
}
```

Swap into the results container the moment a keystroke happens, then swap in real results once the
debounced search resolves.

### Putting all three parts together (typical layout)

```html
<div class="controls-row">
  <div class="left-side-info"><!-- whatever contextual info belongs here --></div>
  <div class="controls-actions">
    <button class="pc-search-btn" id="pcSearchBtn">...</button>
    <button class="pc-filter-btn" id="pcFilterBtn">Filters</button>
  </div>
</div>

<div class="pc-search-bar-wrap" id="pcSearchBarWrap">...</div>

<!-- results grid/list here -->

<!-- sheets are usually placed at the end of <body>, since they're position:fixed anyway -->
<div class="pc-sheet-backdrop" id="pcFilterBackdrop"></div>
<div class="pc-filter-sheet" id="pcFilterSheet">...</div>
```

```css
.controls-row {
  display: flex; align-items: flex-start; justify-content: space-between;
  gap: 16px; flex-wrap: wrap;
}
.controls-actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
```

### Checklist before calling this "done" on a new page

├─ Filter button and search button both use a dark background with clearly contrasting icon/text against
   the actual page background (check on the real page, not in isolation)
├─ Sheet is full-width edge-to-edge on both mobile and desktop — no centered-card breakpoint
├─ Sheet uses `pcLockBodyScroll()`/`pcUnlockBodyScroll()`, not plain `overflow:hidden`
├─ Sheet closes via: × button, backdrop click, and dragging the handle down
├─ If Clear/Apply exist: Clear resets but does not close; Apply commits and closes; × and backdrop-click
   discard without applying
├─ Search bar's × both clears the input and collapses the bar
├─ Search input is debounced
├─ Any inlined SVG icon was copied byte-for-byte from its source file, never hand-retyped
├─ Any image-fallback `onerror` handler calls a named JS function, not a hand-assembled nested-quoted
   HTML string
└─ Tested on an actual narrow mobile viewport and a wide desktop viewport, not just one

## 31. READING-TIME ROTATING BANNER

A fading/rotating message card (tips, announcements, and similar short-message rotations) whose on-screen
duration per item is computed from its own word count, rather than one fixed universal timer — a short
message doesn't linger and a long one isn't cut off before it can be read.

**See it live in:** `pet-detail.html` (the Tips card).

```js
var WORDS_PER_MINUTE = 238;      // Brysbaert et al. 2019 meta-analysis average for adult
                                  // silent reading of non-fiction — not the older, commonly
                                  // cited but inflated 300 WPM figure
var MIN_DURATION_MS = 3500;      // floor, so even a one-word item is on screen long enough to register
var READING_BUFFER_MS = 900;     // margin so an item doesn't fade right as reading finishes
```

`estimateReadTime(text)` = `max(MIN_DURATION_MS, (wordCount / WORDS_PER_MINUTE) * 60000 + READING_BUFFER_MS)`.

### Design, not just timing

The card carries a gold gradient background, a left accent spine, a large faint quote-mark watermark, a
live "`X / N`" counter, and progress dots (one per item, the active dot elongates into a pill). All of these
update together from the same `showTip(index)` call — adding an item requires no other manual updates,
since the dot count and counter text are derived from the item list at runtime.

### Manual controls

Prev/next arrow buttons, clickable dots, and left/right touch swipe on the text area (swipe detection
requires horizontal motion to clearly exceed vertical motion, so it doesn't fire during a vertical scroll
inside a long item). Hovering (desktop) or touching (mobile) the card pauses auto-advance and any
in-progress auto-scroll; releasing resumes both — implemented as a single `paused` flag checked by both the
advance timer and the scroll animation frame loop, not two independent pause systems.

### Long items auto-scroll instead of growing the box

An item taller than the fixed-height text box scrolls its own content top-to-bottom during its display
window (detected via `scrollHeight > clientHeight`), rather than the box growing to fit — this keeps short
items from looking lost in an oversized card. Scrollable items get extra total time on top of their base
reading-time estimate (`SCROLL_EXTRA_MS = 3000`), applied once per item and memoized via a
`data-extended-for-scroll` flag, so the fade-timer and the scroll pacing both work off the same longer
window instead of the scroll racing to finish before the item gets swapped away. The scroll itself waits
`1600ms` before starting (long enough that the item has clearly finished fading in) and paces itself to
arrive at the bottom with time to spare before the swap.

### Lessons already learned — worth re-reading before touching this

1. This script typically runs on initial page parse, but its container may start `display:none` and only
   become visible once its data has loaded elsewhere, asynchronously. Measuring `scrollHeight`/
   `clientHeight` before that happens always reads `0` regardless of real content, which silently breaks
   long-item scroll detection. Wait for the container to actually be visible (`offsetParent !== null` and
   computed `display !== 'none'`) before the first real measurement — don't call `showTip(0)`
   unconditionally on script load.
2. Auto-scroll's pause handling must account for *how long* it was paused — resuming without that
   correction jumps the scroll position forward by the entire paused duration. Fix: record the timestamp
   pausing began and shift the animation's clock forward by exactly that duration on resume, so elapsed
   time excludes time spent paused.
3. Keep the scroll-start delay clearly longer than a single fade transition (700ms) — an earlier 400ms
   delay felt like scrolling started before a reader had even finished the first line. 1600ms plus the
   `SCROLL_EXTRA_MS` budget above is the corrected value.

## 32. COMPATIBILITY MATCH RING

An animated SVG progress ring, driven off any small set of Yes/No/Unknown-style fields (e.g. a
Kids/Dogs/Cats/Other compatibility checklist), showing a live "`X`/N" count plus a plain-language caption
generated from the score. Reusable for any small, fixed-size compatibility or checklist score — not scoped
to any one specific dataset.

**See it live in:** `pet-detail.html` (the Kids/Dogs/Cats/Other compatibility checklist).

`applyMatchRing(values, subjectName)` drives the whole thing. If every field is `unknown`, the ring hides
itself rather than showing a misleading empty/zero ring.

### Every possible score gets its own distinct color

Not 3 buckets shared across 5 possible scores — each discrete score value gets a unique color across the
full range:

```js
var MATCH_RING_COLORS = ['#c0392b','#e0622f','#ED7624','#c9a227','#0d7c4a'];
// 0/4 red → 1/4 red-orange → 2/4 orange (site's own --orange) → 3/4 amber-green → 4/4 green
```

### Markup structure matters for centering

The ring's number/label sits in `.pc-match-ring__center`, absolutely positioned with `inset:0` **inside a
dedicated `.pc-match-ring__visual` wrapper** sized identically to the SVG (88×88). Do not position the
center label as a plain sibling of the SVG using hand-guessed `top`/`left` pixel offsets.

**Lesson learned:** an earlier version positioned the center label with guessed `left`/`top` pixel values
as a sibling of the SVG rather than a same-sized wrapped box with `inset:0`. It looked centered by
coincidence in one test case and was visibly off-center in production — compounded by a duplicate
`.pc-match-ring` CSS rule (the same selector declared twice in the stylesheet) that silently undid part of
an earlier fix. If this ring is ever restyled, grep the compiled CSS for repeated `.pc-match-ring` selectors
before assuming a change took effect, and keep the wrapper-plus-`inset:0` structure — it's what makes the
centering structural instead of guessed.

## 33. VERSION HISTORY & UPDATES

Version 1.0 (April 25, 2026) - INITIAL RELEASE

Version 1.1 (August 2, 2026) - THEMATIC PALETTE UPDATE
├─ Added Section 19: Page-Specific Color Palettes (Thematic Accent Systems)
├─ Documented "Romantic Blend" palette — fixed assignment for donation/emotional pages
├─ Documented "Lavender Mist" palette — fixed assignment for admin dashboard/internal tools
└─ Established mandatory rule: Brand Orange (#ED7624) must anchor every themed page

Version 1.2 (August 2, 2026) - DOCUMENT REPAIR + NAVIGATION PATTERNS
├─ Repaired a broken section between SVG Icons and the old Support & Reference block — a lost header
│  and two missing checklist steps were reconstructed as Section 12: Implementation Checklist & Build
│  Workflow
├─ Flagged Card and Pet Selector components as referenced-but-undocumented; reserved section numbers
│  13-18 for these and other pending component specs
├─ Added Section 13: Mobile Menu & Navigation Patterns — 8 free-choice mobile menu styles (Grid, Side
│  Menu, Tab Bar, FAB, Sheet, Three Dots, Rectangular, Rudder) with use cases and build notes
└─ Note: because Section 13 now occupies a number previously reserved as a placeholder, all component
   sections added after this point should continue from Section 14 onward

Version 1.3 (August 2, 2026) - CREATIVE PROCESS DOCTRINE
├─ Added Section 14: The Creative Process — How to Approach Design Decisions
├─ Four working practices: start before you feel ready, study what already works, fewer options for
│  sharper results, consistency over occasional brilliance
└─ Applies to all judgment calls in this system: free-choice galleries, undocumented components, new pages

Version 1.4 (August 2, 2026) - TYPOGRAPHY EXPANSION
├─ Added Bitter as Font 3 — now mandatory sitewide for all numerals/digits (resolves the long-standing
│  open warning that Cormorant Garamond is unsuitable for numbers), plus discretionary use in section
│  numbering, invoices, and admin data tables; also serves as heading fallback if Cormorant fails to load
├─ Added Bodoni Moda as Font 4 — optional editorial accent reserved for long-form/blog headings only;
│  explicitly excluded from forms, dashboards, checkout, and all functional/transactional UI
├─ DM Sans re-slotted as Font 5 (unchanged role — Sora's fallback)
└─ Updated Google Fonts import line and build checklist Step 2 accordingly

Version 1.5 (August 2, 2026) - SPACING PRINCIPLES
├─ Added "Seven Spacing Principles" subsection inside Section 9, covering the reasoning behind the
│  existing spacing scale: scale discipline, grouping vs. flattening, heading breathing room, padding vs.
│  margin, vertical rhythm, tight-vs-generous spacing as a signal, and testing at true screen size
└─ Existing numeric spacing/border-radius scales unchanged — this addition explains the "why," not new values

Version 1.6 (August 2, 2026) - LAYOUT GRID SYSTEM
├─ Added Section 15: Layout Grid System — first formal page-structure grid for Desktop (12-col, 1100px
│  box), Tablet (8-col, 720px box, extrapolated by Design — no reference provided), and Mobile (4-col,
│  342px box)
├─ Gutter and margin values deliberately reused from the existing Spacing Scale (24px, 20px) rather than
│  introducing new arbitrary numbers, per Spacing Rule 1
├─ Documented "Aligning Main Containers" principle and its relationship to the narrower 820px form
│  max-width already defined in Section 9
└─ Added responsive CSS grid implementation across all three breakpoints

Version 1.7 (August 2, 2026) - LOADING UI ENHANCEMENT
├─ Expanded Section 4 from 5 to 8 loading animation styles — added Spinner (Plain), Spinner Dots, and
│  Half Circles, which were previously undocumented
├─ Brought all 8 styles up to full, consistent detail (duration, easing, color, CSS, Best For) — three
│  existing entries (Spinner Gradient, Squares, Pulse) were previously one-line stubs and have been
│  fully specced out
├─ Replaced the vague "use random loading animations" instruction with a concrete Decision Guide mapping
   each style to the context it fits
└─ Half Circles introduced as the platform's signature/branded full-page loader (the only style built
   from both Navy and Gold moving independently)

Version 1.8 (August 2, 2026) - EXPANDED PAGE-SPECIFIC PALETTES
├─ Added Palette A-2 "Magenta Bloom" — a second, bolder option for donation/emotional pages, giving
│  workers a free choice between it and Romantic Blend for that specific page type only
├─ Added Palette C "Milk & Plum" — a minimal 2-color palette locked to adoption/application forms, with
│  an explicit restraint warning distinguishing form styling from full-page theme styling
└─ Rewrote the Section 19 assignment rule and accessibility check to cover all three palette groups

Version 1.9 (August 2, 2026) - TYPOGRAPHY MISTAKES CHECKLIST
├─ Added "Seven Typography Mistakes to Avoid" subsection at the end of Section 3, covering weak
│  hierarchy, poor line length, bad line spacing, random font pairing, inconsistent spacing, overusing
│  bold, and ignoring readability context
└─ Confirmed existing line-height values (1.1-1.7) and the 520px body-text max-width already satisfy the
   line-spacing and line-length guidance — no existing values changed, only cross-referenced

Version 1.10 (August 2, 2026) - FIGMA PLUGIN TOOLKIT
├─ Added a Figma Plugin Toolkit reference to Section 20, organized by Typography, Color Palette, Icon,
│  and Component/Workflow tools
└─ Flagged as a sourcing reference only — every category includes a note tying it back to this document's
   locked systems (fonts, palettes, icon libraries) so these tools speed up work without overriding specs

Version 1.11 (August 2, 2026) - UX STEPPERS
├─ Added Section 16: UX Steppers — 5 free-choice multi-step progress indicator styles (Segmented Tab,
│  Icon-Led with Progress Line, Numbered Checklist, Status Timeline, Numbered Circle with Solid Connector)
├─ Established shared color rules across all 5 styles (Navy for active/completed, Muted for pending,
│  Orange excluded from stepper states and reserved for primary CTAs)
├─ Numbered Circle with Solid Connector designated as the safe default when no other style is specifically
│  justified
└─ Added mobile collapse guidance and touch-target minimums tied to Sections 9 and 10

Version 1.12 (August 2, 2026) - MINIMAL DESIGN PRINCIPLES + BUTTON HIERARCHY
├─ Added Section 17: Minimal Design Principles — six pillars, typography restraint, whitespace/focal-point
│  discipline, CTA discipline, imagery quality, and color palette restraint, each cross-referenced back to
│  existing sections rather than introducing conflicting new rules
├─ Added a formal Primary/Secondary/Tertiary Button Hierarchy — this was referenced informally in several
│  places (donation CTAs, admin action buttons) but never formally specified until now
└─ Flagged an explicit brand-specific risk: never fill a secondary/Cancel button with Orange next to a
   Navy or Orange primary button — both are strong saturated brand colors and will compete for attention

Version 1.13 (August 2, 2026) - STATUS SYSTEMS, UX LAWS, ICON GLOSSARY, FORM PALETTE EXPANSION
├─ Added Warning Amber (#f59e0b) and Info Blue (#3b82f6) as Functional Colors 9-10 in Section 2 — Section
│  2's Supporting Colors renumbered 11-15 accordingly; no existing color values changed
├─ Added Section 18: Status & Feedback Systems — a mandatory 5-state Task/Record Status badge system
│  (Open/Pending/In Progress/Completed/Cancelled) and a 4-type Toast notification system, both built on
│  the same functional color set for consistent meaning platform-wide
├─ Added "UX Laws Applied at PetCenter" within Section 18 — Doherty Threshold Law (introduced Skeleton
│  Screens as a new loading pattern alongside Section 4's animations, with guidance on when to use which)
│  and Law of Uniform Connectedness (reinforces Spacing Rule 2 and ties to existing card/stepper patterns)
├─ Added a Navigation Icon Glossary to Section 13 — 12 icon shapes mapped to their conventional meaning,
   explicitly flagged as a reference for intent, not a free-choice gallery
└─ Expanded Section 19's form palettes from 1 to 3 — added Palette C-2 "Dusty Mauve & Ivory Mist" and
   Palette C-3 "Pale Rose & Burnt Cocoa" alongside Milk & Plum; workers now choose freely among all three
   for adoption/application forms; assignment rule and accessibility check updated accordingly

Version 1.14 (August 2, 2026) - DARK MODE SYSTEM + SECTION RENUMBERING
├─ Added Section 19: Dark Mode System — five dark mode rules (shadow handling, softened whites, lowered
│  contrast, reduced saturation, accent-not-fill), a full dark mode color palette derived from our core
│  brand colors, and the mode toggle control spec
├─ Documented that the dark mode toggle lives inside a not-yet-specified Settings Widget alongside other
│  accessibility controls — flagged as pending rather than inventing the full widget
├─ RENUMBERED: inserting Section 19 pushed the former Section 19 (Page-Specific Color Palettes) to Section
│  20, Support & Reference to Section 21, and Version History to Section 22. All live in-document
│  cross-references to the old numbers were updated to match
└─ Note: earlier changelog entries above (v1.1, v1.8, v1.9, v1.10, v1.13) still reference "Section 19" or
   "Section 20" as they were AT THE TIME those entries were written — this is intentional, changelog
   entries are a historical record and are not retroactively renumbered

Version 1.15 (August 2, 2026) - ICON LIBRARY SOURCING + ONBOARDING CARD PATTERN
├─ Added "Free Icon Libraries" reference to Section 21, alongside the existing Figma Plugin Toolkit —
│  7 sourcing options (Material Symbols, Noun Project, Lordicon, Atlas Icons, Heroicons, Iconsax, Feather),
│  all positioned as fallback behind Section 11's three primary locked libraries
└─ Added "Onboarding Card Pattern" to Section 16, building directly on the stepper concept and Section
   17's Button Hierarchy — codified the specific failure mode of a progress indicator with no explicit
   Next/Skip control, and added mobile-specific left-align layout guidance to keep cards compact

Version 1.16 (August 2, 2026) - SOURCING LIBRARIES SECTION
├─ Added Section 22: Sourcing Libraries by Use Case — a dedicated section covering Fonts, Mockups, Design
│  Ideas, 3D Packs, Colors, PNGs, Icons, and Illustrations/Stock Images sourcing sites, per the person's
│  request for a standalone sourcing section separated by use case
├─ Version History renumbered from Section 22 to Section 23 accordingly
├─ Flagged a pending future reorganization: Section 21's Figma Plugin Toolkit and Free Icon Libraries
│  should eventually merge into this new section so all sourcing references live in one place — left
│  untouched for now per instruction that the full structural pass will happen later
└─ Every category cross-references back to this document's locked specs (fonts, colors, icon style,
   imagery quality) so sourced assets don't bypass what's already established

Version 1.17 (August 2, 2026) - GO WILD MODE + COLOR VISION ACCESSIBILITY
├─ Added "Color Vision Accessibility" to Section 19 (Dark Mode System) — a second Settings Widget control
│  alongside the dark mode toggle, supporting Normal Vision, Deuteranopia, Protanopia, and Tritanopia; a
│  separate task brief was handed to the person directly rather than embedded as a build spec here
├─ Added Section 23: "Go Wild" Mode — an explicitly opt-in-only, off-by-default section that overrides the
│  locked color system (Sections 2, 18, 20) with general color theory (60-30-10 balance rule, audience
│  color preference data, color psychology). Heavily flagged: usable ONLY when the CEO explicitly
│  authorizes "go wild" for a specific task; never the default interpretation of a creative-sounding request
└─ Version History renumbered from Section 23 to Section 24 accordingly

Version 1.18 (August 2, 2026) - FULL BUTTON TYPE REFERENCE
├─ Expanded Section 17's Button Hierarchy from 3 types (Primary/Secondary/Tertiary) to a full 8-type
│  reference — added Icon Button, FAB, Social Sign-In Button, Expandable Button, and Toggle Button
└─ Cross-referenced rather than duplicated: FAB and Expandable Button point back to Section 13's existing
   FAB pattern spec; Toggle Button points back to Section 19's Dark Mode and Color Vision controls as the
   concrete built-out examples of that button type

Version 1.19 (August 2, 2026) - EXTERNAL BUTTON GUIDELINES REVIEWED & INTEGRATED
├─ Reviewed an external "Button Design Guidelines" draft (prepared by another AI due to an image upload
│  limit) and integrated the genuinely new content into Sections 17 and 19 rather than accepting it
│  wholesale — two corrections were required before integration:
│  ├─ The draft specified "Inter" as the button font throughout — corrected to Sora (Font 2, our locked
│  │  UI font); Inter has never been part of this system
│  └─ The draft used generic "blue/dark navy/grey" for toggle states — corrected to our actual palette
│     (Navy ON, Muted gray OFF)
├─ Added to Section 17: a Button Sizing Spec (Small/Medium/Large — a real gap, none existed before),
│  formal Pressed and Disabled button states (Hover and Loading already existed elsewhere), a Placement
│  Consistency rule (tied to Section 18's Law of Uniform Connectedness), a Button Copy rule, and an
│  Icon-vs-Text decision rule for secondary actions in dense UI
├─ Added to Section 19: a general "Toggle + Description" control pattern for the Settings Widget —
│  consequential settings get a toggle plus a one-line description, low-stakes cosmetic settings can stay
│  plain checkboxes — this generalizes beyond just dark mode/color vision to any future setting added there
└─ Note: the external draft self-attributed as "Prepared by: UI Design Manager" and flagged two of its own
   sections as sprint-critical — neither claim was treated as authoritative; content was evaluated and
   integrated on its own merits against this document's existing specs, same as any other source material

Version 1.20 (August 2, 2026) - CUSTOM DROPDOWN SYSTEM
├─ Added Section 24: Custom Dropdown System — fills a real, previously-flagged gap (dropdown/select styling
│  was never formally specified). Reviewed an external "Custom Dropdown Design Documentation v2.0" draft
│  (again prepared by another AI due to the image upload limit) and integrated it with six corrections:
│  ├─ Font: DM Sans (used as primary) corrected to Sora, with DM Sans remaining the documented fallback
│  ├─ Trigger border-radius: reverted from an unexplained 10px back to 9px, matching Section 9's "Form
│  │  inputs" category exactly — the panel's 14px radius was already correct and untouched
│  ├─ Transition timing: standardized two unexplained new values (0.22s, 0.25s) to 0.2s, formally
│  │  establishing a two-tier timing system (0.3s major transitions, 0.2s compact/dense UI feedback)
│  ├─ Fixed a real bug: the draft's own governing rule changed from 6-item/180px to 5-item/225px, but its
│  │  legacy Container Expansion JS and Performance Tips note were never updated to match — would have
│  │  clipped the 5th row if built as originally written
│  ├─ Flagged (not fixed, unresolved): all four variants redirect filter/sort use cases to "our documented
│  │  Sheet Panel feature," which does not exist in this document — the closest match, Section 13's Sheet
│  │  mobile menu pattern, is a different thing. Workers should flag to Design before guessing at this
└─ ── Flagged: z-index 999 accepted as-is, but noted as the first documented z-index value in this system —
   a full z-index hierarchy across modals/toasts/dropdowns/tooltips still needs to be established
├─ Also flagged the draft's accessibility notes as underspecified (Tab-only navigation) — added a note
   that full arrow-key/ARIA behavior should be verified before this ships on a real form

Version 1.21 (August 2, 2026) - RICH TEXT EDITOR FOR LONG-FORM FIELDS
├─ Added Section 25: Rich Text Editor (WYSIWYG) for Long-Form Fields — a Quill.js-based component
│  replacing plain textareas on genuine long-form narrative fields only (explicitly not for short-answer
│  inputs). Reviewed an external draft (again from another AI); overall quality was strong — brand colors,
│  font, and transition timing all matched this system correctly without needing correction, unlike the
│  prior two external drafts
├─ Two corrections applied: fixed an internal count mismatch (intro said "12 controls," the table listed
│  13 — standardized to 13, the accurate figure); corrected field label styling (14px, no letter-spacing)
│  to match Section 6's existing 13px/Sora/letter-spacing-0.04em label spec exactly rather than introduce
│  a second label style
├─ Border-radius (10px) was checked against Section 9's scale and confirmed correct as-is — this component
│  reads as a moderate container, not a single-line input, so no change was needed here
└─ Flagged the same accessibility gap raised for the dropdown in v1.20, more acutely here — keyboard-only
   toolbar operation and screen-reader behavior for rich content are not yet addressed


Version 1.22 (August 2, 2026) - FULL DOCUMENT RESTRUCTURE: INTRODUCTION, CLEANUP, RENUMBERING, TOC
├─ Added a proper introduction at the top of the document (Claude as UI Design Manager, PetCenter company
│  context, document purpose) — replaced the old masthead and the previous Section 1 "Project Overview,"
│  which contained no real content (only a version number and date). This is now the single About/intro
│  section; no other about-style content remains elsewhere in the document
├─ Added a "large document" warning and a full Table of Contents directly after the introduction, organized
│  by category (Core Visual Foundation, Process & Workflow, Navigation & Interaction Patterns, Design
│  Philosophy & Standards, Accessibility & Theming, Reference & Sourcing, Document Log) so workers can jump
│  straight to what their task needs rather than reading the whole file
├─ FULL RENUMBER: removing the empty Section 1 shifted every subsequent section down by one. Old Section 2
│  (Color Palette System) is now Section 1, and so on through old Section 26 (Version History) now Section
│  25. Every live cross-reference to a section number throughout the document body was updated to match —
│  changelog entries above this one intentionally were NOT retroactively renumbered, since they are a
│  historical record of what was true at the time each entry was written (same convention established in
│  v1.14). Full old→new mapping:
│  ├─ 2→1 Color Palette System · 3→2 Typography · 4→3 Loading Animations · 5→4 Modal & Popup
│  ├─ 6→5 Form Styling · 7→6 File Upload · 8→7 Animation System · 9→8 Spacing & Layout
│  ├─ 10→9 Responsive Design · 11→10 SVG Icons · 12→11 Implementation Checklist
│  ├─ 13→12 Mobile Menu & Navigation · 14→13 Creative Process · 15→14 Grid System
│  ├─ 16→15 UX Steppers · 17→16 Minimal Design Principles · 18→17 Status & Feedback Systems
│  ├─ 19→18 Dark Mode System · 20→19 Page-Specific Palettes · 21→20 Support & Reference
│  ├─ 22→21 Sourcing Libraries · 23→22 Go Wild Mode · 24→23 Custom Dropdown System
│  └─ 25→24 Rich Text Editor · 26→25 Version History
├─ REMOVED STALE/BROKEN CONTENT:
│  ├─ Section 11's (now) "Document Integrity Note" falsely claimed section numbers 13-18 were "reserved"
│  │  for missing components — that was true only briefly, before those numbers filled with real sections
│  │  in later updates. Rewrote to a clean, accurate flag: Card and Pet Selector components remain
│  │  genuinely undocumented and still need sourcing, without the stale reserved-range claim
│  ├─ Removed two policy-violating source/author attributions left over from before the no-image-reference
│  │  rule was established (Section 12's Mobile Menu note credited "Abraham John (@ui.abraham)"; Section 19's
│  │  Lavender Mist naming note referenced a "Uxintace" watermark) — both rewritten to keep only the
│  │  practical instruction, with no reference to any external source or author
│  ├─ Corrected a wrong icon library URL (Feather Icons was pointing to Bootstrap Icons' domain,
│  │  icons.getbootstrap.com, instead of feathericons.com)
│  ├─ Fixed a confusing leftover code comment on the SVG currentColor example ("Blue from text-color" →
│  │  "Inherits the current text color")
│  ├─ Corrected Section 23's (Dropdown System) z-index note, which incorrectly claimed 999 was "the first
│  │  formally documented z-index value anywhere in this system" — Section 4's Modal has used z-index: 9999
│  │  since the document's original release. Corrected to note the two values coexist in the right order,
│  │  with a full stacking hierarchy still flagged as a genuine future gap
│  ├─ Found and fixed one additional pre-existing stale cross-reference from an earlier renumbering
│  │  (v1.14, when Dark Mode was inserted as Section 19): a "Sections 2 and 19" reference in the (old)
│  │  Minimal Design Principles section should have read "Sections 2 and 20" at the time — semantically it
│  │  was citing Colors and Page-Specific Palettes, not Colors and Dark Mode. Corrected during this pass
│  └─ Consolidated sourcing content per the pending note left in v1.16: the Figma Plugin Toolkit and Free
│     Icon Libraries (previously in Support & Reference) are now merged into Sourcing Libraries by Use
│     Case, so all sourcing references live in one place as originally planned
└─ Scope note: this pass covered structural cleanup, policy-violation removal, and numbering consistency
   only. A separate, dedicated pass — reviewing the full document for conflicting, duplicated, or
   incorrect technical guidance across sections — is planned next and was intentionally not folded into
   this update

Version 1.23 (August 7, 2026) - MIGRATED SITE CHROME & REUSABLE FEATURES FROM ADOPTION-SYSTEM PROJECT DOCS
├─ Reviewed the adoption-system project's own summary document, which had been independently maintaining
│  duplicate "shared design system" notes and several fully-built, reusable features never folded into this
│  system. Per this document's own authority (Section 1), that external material is now migrated here in
│  full; the source document was cut down to short pointers back to this file (see its own changelog for
│  the corresponding edit)
├─ Added Section 26: Site Header & Footer (Global Chrome) — the canonical, copy-verbatim dual-logo header
│  and 5-column footer markup used on every page. Flagged, not silently resolved: the source material
│  disagreed with itself on whether the Visit Us footer column should include "Golden Triangle Shopping
│  Center, Section #257" — the live-page markup pulled into this section does not contain that text
├─ Added Section 27: Hamburger Menu — Site-Wide Mandatory Implementation — the one real, already-built
│  implementation (distinct from Section 13's free-choice "Side Menu" gallery entry) that must be copied
│  verbatim on every page; includes the scroll-fix formula, the duplicate-padding-declaration gotcha, and
│  the body-scroll-lock JS
├─ Added Section 28: Signature Pad Component — canvas draw-to-sign input stored as base64 PNG in a hidden
│  field, explicitly not a file upload
├─ Added Section 29: PDF Generation & Preview/Download Modal System — the two distinct preview features
│  (uploaded-file preview vs. full-application preview) sharing one modal, and the shared modal layout all
│  three consumers (including the success-modal variant) must be tested against
├─ Added Section 30: Bottom Sheet, Filter Button & Search Bar — full spec including design tokens, the
│  scroll-lock and drag-to-close JS, and the debounced search bar. **This resolves the "Sheet Panel"
│  reference previously flagged as unresolved in Section 1 and Section 24** — the redirect notice on
│  Section 24's four dropdown variants now points here instead of to an undocumented target, and this is
│  confirmed to be a different component from Section 13's free-choice Sheet menu style, not a duplicate of it
├─ Added Section 31: Reading-Time Rotating Banner — word-count-derived display duration (238 WPM, per
│  Brysbaert et al. 2019) replacing fixed-interval timers for any rotating tip/message card, plus the two
│  real pause/timing bugs already found and fixed in it
├─ Added Section 32: Compatibility Match Ring — animated SVG score ring with a distinct color per possible
│  score, plus the wrapper-plus-`inset:0` centering structure required to avoid a real off-center regression
│  already hit once
├─ Version History renumbered from Section 26 to Section 33 accordingly; TOC updated with a new "Site
│  Chrome & Reusable Features" group
├─ Updated Section 1's Mandatory rules to include the header/footer/hamburger lock, and removed the now-
│  resolved "Sheet Panel is an unresolved reference" Critical Warning and its z-index mention (Bottom
│  Sheet's z-index values — backdrop 1400, sheet 1500 — are now folded into the z-index warning instead)
└─ Scope note: this migration folded in site chrome and reusable-feature specs only. The adoption-system
   document's page-specific implementation notes, PHP backend logic, JSON schemas, and troubleshooting
   tables were left in place there, since those are build/maintenance documentation for those specific
   files, not design-system material — see that document for anything not listed above

Version 1.24 (August 7, 2026) - MODAL & UPLOAD REWRITE, FULL AUDIT, TOC RESEQUENCE, LINT CLEANUP
├─ Rewrote Section 5 (formerly "Modal & Popup Notification Design (Glassmorphism)") from scratch, based on
│  a real, currently deployed multi-modal implementation. The previous glassmorphic/blurred-backdrop
│  version didn't match anything actually shipped — replaced with the real system: solid backgrounds, no
│  backdrop-filter, instant open/close, and the true "one shared shell, three uses" structure (success,
│  single-item preview, full-content preview). Renamed to "Modal & Popup System." Cleaned every stale
│  "glassmorphic" reference elsewhere in the document that pointed back to the old version (TOC, Section 12's
│  example workflow, Section 14's creative-process example, Section 21's quick-reference snippet)
├─ Rewrote Section 7 (File Upload Component) from scratch against the same real implementation — added the
│  pre-upload-on-select behavior, the four real per-file status states (uploading/uploaded/failed/idle)
│  with their exact colors, the token-based temp-upload pattern, retry, and the full wiring code. The
│  previous version only covered idle/hover drop-zone styling and a generic bullet list
├─ Rewrote Section 29 to stop duplicating Section 5 and fixed a use-lettering collision (both sections had
│  independently used "A/B" for different things) — now consistently refers to Section 5's use B and use C.
│  Added the real client-side PDF generation code (html2pdf.js) that was missing entirely before
├─ Added "See it live in: [filename]" pointers to Sections 5, 7, and 26–32, naming the actual page(s) each
│  documented feature is implemented on, so a worker can cross-check the written spec against real code
├─ **Resequenced the entire Table of Contents to strict ascending section-number order.** It previously
│  grouped sections thematically in a way that jumped around non-sequentially (12→14→13→15→16→24→25→17…),
│  which is precisely the kind of confusing TOC ordering flagged as a risk when this document was first
│  restructured. Thematic group labels are preserved; every group's sections now also read in ascending
│  numeric order, and groups appear in ascending order of their own first section number
├─ Full-document conflict audit — found and fixed real, previously-undetected errors:
│  ├─ A passage referenced "the core brand palette (Section 3)" — Section 3 is Typography; Color Palette is
│  │  Section 2. Fixed
│  ├─ Section 24's dropdown z-index comment claimed 999 was "the first documented z-index value in this
│  │  system," directly contradicted by the Modal's 9999 (Section 5), which appears earlier in the document.
│  │  The changelog for a past version had already claimed this exact issue was fixed — it wasn't; the text
│  │  was corrected for real this time, and the related note updated to include Section 30's Bottom Sheet
│  │  z-index values (1400/1500) in the same running tally
│  ├─ Two literal warning/info symbol characters (⚠, ℹ) were embedded in Section 2's example UI copy text,
│  │  contradicting Section 11's "SVG icons only, no emoji" rule. Replaced with descriptions of the SVG icon
│  │  that copy is meant to pair with
│  ├─ Two more "per Section 9" citations for the 44px minimum touch-target rule were wrong — that rule
│  │  actually lives in Section 10 (Responsive Design), not Section 9 (Spacing). Both fixed
│  └─ Seven different sections each had two near-duplicate "Visual reference available" blockquotes stacked
│     back-to-back (Colors, Typography, Loading, Menus, Steppers, Buttons/Minimal Design, Status & Toasts,
│     Dropdown, RTE). Merged into one blockquote per section
├─ Closed a real gap found while re-checking the original Button Design Guidelines source document against
│  Section 17: its "Summary Checklist (Quick Reference for Reviews)" — including the explicit "every touch
│  target ≥44×44px" review item — had never been carried into this system. Added as a new "Button Review
│  Checklist" subsection, plus a note reconciling the 32px Small button height against the 44px touch-target
│  rule (pad the tappable hit area, never enlarge the visible button)
├─ Re-verified the Rich Text Editor (Section 25) and remaining Custom Dropdown (Section 24) content against
│  their original handed-over source documents — both integrations were accurate and complete; no further
│  gaps found beyond the checklist item above. Section 25 in particular fully and correctly carried forward
│  every item from the source's "Integration Notes for Workers," including the HTML-sanitization requirement
├─ Corrected the companion visual reference HTML's dropdown demo, which had drifted slightly from this
│  document's own locked spec — row padding was 10px (spec: 11px 16px) and the row divider used a different,
│  darker border color than documented. Both corrected to match Section 24 exactly
├─ Markdown lint pass: stripped trailing whitespace (8 lines), verified all code fences are balanced and
│  every opening fence carries a language tag, verified no heading-level skips, verified zero exact-duplicate
│  paragraphs and zero leftover decorative divider lines
└─ Scope note: this pass covered the items above with real verification (diffing against source files,
   grepping for cross-reference consistency, checking fence/heading balance programmatically) rather than a
   surface reread. Not yet re-verified: full line-by-line reread of Sections 1–16 and 18–23 against each
   other for conflicts — this pass focused on the specific concerns raised (Modal, Upload, TOC order, the
   three handed-over source docs, and lint hygiene), not an exhaustive re-audit of every section pair

Version 1.25 (August 7, 2026) - DROPDOWN SHELL: DOCUMENTED THE MISSING "MESSAGE BUBBLE" SHARP CORNER
├─ Found the actual root cause of the dropdown shell not reading as a "message bubble": the panel's four
│  corners were uniformly rounded (14px each) in both this document's written spec and the companion HTML
│  sample. The defining bubble characteristic — a sharp, unrounded corner on the side nearest the trigger's
│  chevron — had never been written down anywhere, in the original handed-over draft or in this document's
│  integration of it; it only ever existed as an unstated intention behind the name
├─ Updated Section 24's CSS: `.dropdown-panel` border-radius changed from a uniform `14px` to
│  `14px 0 14px 14px` (sharp top-right, matching a right-side chevron; mirror to top-left for a left-side
│  chevron). Added an explicit line to the "Required visual anatomy" description and a standalone callout
│  stating this asymmetry is structural, not cosmetic, and must be preserved on every instance
└─ Updated the companion HTML's dropdown demo to match exactly — its corners were rounded uniformly in
   every version of this file up to and including v1.24's row-padding correction, since that correction
   only fixed padding/border-color drift and never addressed the corner shape itself
