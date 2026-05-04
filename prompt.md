You are building the public-facing marketing website (landing pages) for a SaaS POS
system called [YOUR BRAND NAME]. The actual application is already built in Laravel
with Filament v4 admin panel. The admin login is at /admin/login. Your job is ONLY
the public marketing pages — do NOT touch any backend logic.

---

## WHAT THE PRODUCT IS

A multitenant SaaS Point of Sale (POS) system tailored for:

- Manufacturing businesses (raw material tracking, production batches)
- Bulk manufacturing (bulk orders, warehouse management)
- Selling manufactured goods (inventory, invoicing, customer management)

Each business gets their own isolated tenant workspace. It's built for Tanzanian
businesses, priced in Tanzanian Shillings (TZS).

---

## PAGES TO CREATE

Create the following Blade views inside `resources/views/landing/`:

1. `home.blade.php` — Main landing/hero page
2. `features.blade.php` — Full features breakdown
3. `pricing.blade.php` — Pricing plans
4. `contact.blade.php` — Contact form + info
5. `about.blade.php` — About the company
6. `layouts/landing.blade.php` — Shared layout (navbar + footer)

Register routes in `routes/web.php` for:

- GET / → home
- GET /features → features
- GET /pricing → pricing
- GET /contact → contact
- GET /about → about

---

## DESIGN DIRECTION

Choose a bold, modern industrial-meets-digital aesthetic:

- Dark navy/charcoal base (#0D1117 or similar) with electric amber/gold accents (#F59E0B
  or similar) to convey manufacturing + precision
- Sharp geometric shapes, subtle grid/blueprint background textures
- Clean sans-serif display font (e.g. DM Sans, Syne, or Outfit from Google Fonts)
  paired with a readable body font
- Smooth scroll animations using AOS.js or pure CSS scroll-driven animations
- Mobile-first, fully responsive
- Use Tailwind CSS (already available in Laravel) for all styling
- Add Alpine.js for interactive elements (already available with Filament)

---

## HOME PAGE (`home.blade.php`) — MUST INCLUDE:

### Hero Section

- Bold headline: e.g. "Manufacture. Manage. Sell. — All in One Place"
- Subheadline explaining the SaaS POS for manufacturing businesses in Tanzania
- Two CTA buttons: "Start Free Trial" (links to /register or /admin/login) and
  "See Features" (links to /features)
- A mockup/screenshot placeholder (use a stylized div with a dashboard-like UI
  skeleton or an SVG illustration of a dashboard)
- Floating badge: "7-Day Free Trial • No Credit Card Required"

### Social Proof Bar

- "Trusted by manufacturers across Tanzania" with placeholder company logos
  (use styled text placeholders)

### Problem/Solution Section

- 3 pain points manufacturing businesses face (manual stock tracking, bulk order chaos,
  disconnected sales) each with an icon and solution statement

### Key Features Preview (3–4 cards)

- Manufacturing batch tracking
- Multi-warehouse/store inventory
- Invoicing & bulk sales
- Multi-tenant isolation (each business has their own workspace)
- Each card has icon, title, 1-line description, "Learn more" link

### Pricing Teaser

- Show 3 plan cards (see Pricing section below for details)
- CTA: "Compare all plans →" linking to /pricing

### CTA Banner

- Full-width dark section: "Ready to modernize your manufacturing business?"
- Button: "Start Your Free Week" → /admin/login

### Footer

- Links: Home, Features, Pricing, Contact, About
- "Login to Dashboard" → /admin/login
- Copyright, tagline

---

## FEATURES PAGE (`features.blade.php`) — MUST INCLUDE:

Organized into 4 feature categories with icons, titles, descriptions:

**Manufacturing Management**

- Production batch creation and tracking
- Raw material consumption tracking
- Bill of Materials (BOM) management

**Inventory & Warehousing**

- Multi-location stock management
- Stock movement logs
- Low-stock alerts
- Bulk stock adjustments

**Sales & Invoicing**

- POS sales terminal
- Bulk/wholesale order creation
- Invoice generation and printing
- Customer credit management

**Business Intelligence**

- Sales and production reports
- Cost vs revenue analysis
- Per-tenant isolated dashboards
- Daily/weekly/monthly summaries

Use an alternating layout (text left, illustration right, then flip) for the categories.
Add a final CTA section.

---

## PRICING PAGE (`pricing.blade.php`) — MUST INCLUDE:

3 pricing tiers in TZS. Recommended/popular tier should be visually highlighted:

**Starter Plan — TZS 12,000/month**

- 1 location
- Up to 500 products
- Basic POS & invoicing
- 7-day free trial

**Business Plan — TZS 22,000/month** ← MOST POPULAR (highlight this)

- 3 locations
- Unlimited products
- Full manufacturing & batch tracking
- Inventory management
- Priority support
- 7-day free trial

**Enterprise Plan — TZS 30,000/month**

- Unlimited locations
- Everything in Business
- Advanced reporting
- Dedicated support
- Custom onboarding
- 7-day free trial

Add:

- Toggle for Monthly/Annual (annual = 2 months free, show crossed-out price)
- Bold banner above the cards: "All plans include a 7-day FREE trial — no credit card needed"
- FAQ section below (5–6 common questions about the trial, billing, tenants, etc.)
- CTA: "Get Started Free" → /admin/login

---

## CONTACT PAGE (`contact.blade.php`) — MUST INCLUDE:

- Contact form with fields: Name, Business Name, Email, Phone, Message,
  Plan of Interest (dropdown), Submit button
- Form submission should POST to a /contact route that just sends an email
  (use Laravel's Mail::to() with a simple Mailable, or just dd() the request
  for now with a TODO comment)
- Contact info sidebar: Email, WhatsApp number (+255748224536), Location (Dar es Salaam, Tanzania)
- Embedded Google Maps placeholder (just a styled div with iframe placeholder or
  a map pin SVG)
- "Response within 24 hours" assurance badge

---

## ABOUT PAGE (`about.blade.php`) — MUST INCLUDE:

- Mission statement: empowering Tanzanian manufacturers with modern digital tools
- Story section: why this was built (local manufacturing challenges)
- Values: Reliability, Simplicity, Local-First
- Team placeholder section (3 cards with avatar circles + role titles)
- CTA to try the product

---

## SHARED LAYOUT (`layouts/landing.blade.php`) — MUST INCLUDE:

**Navbar:**

- Logo (text-based styled logo is fine) on the left
- Nav links: Home, Features, Pricing, About, Contact
- Right side: "Login" button (secondary, → /admin/login) + "Start Free Trial"
  button (primary, → /admin/login)
- Mobile hamburger menu (Alpine.js toggle)
- Sticky on scroll with slight background blur (backdrop-filter)

**Footer:**

- 4-column layout: Brand+tagline, Quick Links, Product, Contact
- Bottom bar: copyright + "Made in Tanzania 🇹🇿"

---

## TECHNICAL REQUIREMENTS

- All pages extend `layouts.landing`
- Use Tailwind CSS utility classes throughout (no custom CSS files needed unless
  necessary for animations)
- Alpine.js for: mobile menu toggle, pricing monthly/annual toggle, FAQ accordion
- Google Fonts loaded in layout head
- Smooth scroll behavior on the html element
- Meta tags: title, description, og:title, og:description per page
- All "Get Started" / "Free Trial" / "Login" buttons consistently point to `/admin/login`
- No authentication logic on these pages — they are purely public marketing pages
- Add a sticky "Start Free Trial" floating button on mobile (bottom of screen)
- Use heroicons or a simple SVG icon set for feature icons

---

## CONTENT TONE

- Confident, direct, professional but approachable
- Speak to small-to-medium Tanzanian manufacturing business owners
- Emphasize: local pricing (TZS), ease of use, reliability, free trial with no risk
- Avoid overly technical jargon on marketing pages

---

## DELIVERABLES EXPECTED

1. `resources/views/layouts/landing.blade.php`
2. `resources/views/landing/home.blade.php`
3. `resources/views/landing/features.blade.php`
4. `resources/views/landing/pricing.blade.php`
5. `resources/views/landing/contact.blade.php`
6. `resources/views/landing/about.blade.php`
7. Route additions in `routes/web.php`
8. Optional: A simple `ContactController.php` with a `store()` method for the form

Generate all files completely. Do not skip sections or truncate code.
