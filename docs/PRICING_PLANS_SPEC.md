# Pricing & plans specification (LibraPOS)

This document is the **single source of truth** for marketing copy alignment and for **future implementation** of subscriptions, payments, plan limits, and multi-tenant enforcement. Update it when prices or entitlements change.

---

## Currency & billing

| Field | Value |
|--------|--------|
| Currency | **TZS** (Tanzanian Shillings) |
| Trial | **7 calendar days**, **no credit card** required |
| Billing intervals | `monthly`, `annual` |

### Annual discount rule

- **Annual price = 10 × monthly price** (equivalent to **2 months free** per year).
- Reference amounts (marketing / UI):

| Plan | Monthly (TZS) | Annual (TZS) | Strikethrough “list” annual (12 × monthly) |
|------|---------------|--------------|---------------------------------------------|
| starter | 12,000 | 120,000 | 144,000 |
| business | 22,000 | 220,000 | 264,000 |
| enterprise | 30,000 | 300,000 | 360,000 |

---

## Plan identifiers (use in code)

Use stable **slugs** everywhere (DB, API, contact form, middleware):

| Slug | Display name | Popular |
|------|--------------|---------|
| `starter` | Starter | no |
| `business` | Business | **yes** |
| `enterprise` | Enterprise | no |

---

## Tenant & subscription model (target architecture)

These are **implementation targets**; adapt to your existing `tenants` / `teams` / `stores` schema.

1. **Tenant** = one paying business workspace (isolated data boundary).
2. Each tenant has exactly one **active subscription** (or trial) record:
   - `plan_slug` (`starter` | `business` | `enterprise`)
   - `billing_interval` (`monthly` | `annual`)
   - `status` (`trialing` | `active` | `past_due` | `canceled` | `paused`)
   - `trial_ends_at`, `current_period_ends_at`
   - Optional: `external_customer_id` / `external_subscription_id` for a payment provider.
3. **Enforcement** should run:
   - On **HTTP requests** to the panel (middleware / Filament auth pipeline).
   - On **mutations** that create locations, items, or enable gated features (defense in depth).

---

## Quantitative limits (enforce in backend)

Limits apply **per tenant** unless noted.

| Limit key | starter | business | enterprise | Notes |
|-----------|---------|----------|------------|--------|
| `max_locations` (stores / warehouses) | **1** | **3** | **null** (unlimited) | Block creating additional `Store` rows beyond cap. |
| `max_products` (inventory items) | **500** | **null** (unlimited) | **null** | Count active inventory SKUs; soft-deleted may be excluded by policy. |
| `max_suppliers` | reasonable default or unlimited | unlimited | unlimited | Optional cap for starter if abuse becomes an issue. |
| `max_customers` | reasonable default or unlimited | unlimited | unlimited | Same as above. |
| `max_users` | **TBD** | **TBD** | **TBD** | Set explicitly before launch; marketing does not promise user counts today. |

When a limit is exceeded:

- Return a **clear validation error** (Filament notification / form error).
- Optionally show an **upgrade CTA** with target plan.

---

## Feature flags (module gating)

Map Filament **resources / navigation groups** to flags. Use a single `PlanFeature` enum or config array.

### Core (all paid plans after trial)

- `core_pos` — Standard sales / POS flows.
- `core_invoicing_print` — Sale / order print routes.
- `core_items` — Inventory items with SKU, category, units, per-store quantity.
- `core_purchases` — Purchase orders / supplier purchasing (if you keep this on Starter).
- `core_customers_suppliers` — CRM-style master data.

**Suggested policy:** keep **purchases + basic stock** on Starter so the product is usable; if you prefer a leaner Starter, move purchases to Business only and update marketing + this table together.

### Business tier and above

- `manufacturing` — Single / standard manufacturing batches.
- `bulk_manufacturing` — Bulk manufacturing workflows.
- `assembly_bom` — Assembly items / BOM-style management.
- `stock_movements` — Detailed movement ledger.
- `stock_adjustments` — Adjustments with audit trail.
- `sale_orders` — Sale order pipeline (pending counts, etc.).
- `multi_location_inventory` — More than one store; sync rules as per app.
- `priority_support` — Operational / SLA label; enforce via support queue or contract, not code.

### Enterprise emphasis

- `advanced_reporting` — Extended dashboards, P&L breakdown pages, exports, scheduled reports (define precisely when built).
- `financial_modules_full` — Expenses, inter-account **transfer funds**, deeper account views (if Business should be restricted, narrow this flag).
- `dedicated_support` — Named channel / faster response; usually manual.
- `custom_onboarding` — Checklist + manual provisioning steps.

### Recommended default matrix

| Feature flag | starter | business | enterprise |
|--------------|---------|----------|------------|
| `core_pos` | ✓ | ✓ | ✓ |
| `core_items` | ✓ (cap 500) | ✓ | ✓ |
| `max_locations` | 1 | 3 | ∞ |
| `manufacturing` | ✗ | ✓ | ✓ |
| `bulk_manufacturing` | ✗ | ✓ | ✓ |
| `assembly_bom` | ✗ | ✓ | ✓ |
| `stock_movements` | ✗ | ✓ | ✓ |
| `stock_adjustments` | ✗ | ✓ | ✓ |
| `sale_orders` | ✗ | ✓ | ✓ |
| `advanced_reporting` | ✗ | partial / basic | ✓ |
| `financial_modules_full` | ✗ | ✗ or partial | ✓ |
| `priority_support` | ✗ | ✓ | ✓ |
| `dedicated_support` | ✗ | ✗ | ✓ |
| `custom_onboarding` | ✗ | ✗ | ✓ |

Adjust **purchases on Starter** and **financial_modules on Business** to match what you actually ship; the matrix above matches current **marketing** on the pricing page.

---

## Trial behaviour

- New tenant (or new subscription): `status = trialing`, `trial_ends_at = now() + 7 days`.
- During trial: grant **Business-equivalent** or **full** feature access (product decision):
  - **Option A (recommended for conversion):** trial = Business feature set, one location until upgraded.
  - **Option B:** trial = selected plan only.
- After trial without payment: `read_only` mode or `blocked` panel (define explicitly).

---

## Payments (placeholder)

- Provider: **TBD** (e.g. mobile money aggregator, card gateway, invoicing-only for B2B).
- Webhooks should update `subscriptions` and emit domain events (`SubscriptionActivated`, etc.).
- All amounts stored in **integer TZS** minor units if you add decimals later; currently whole TZS is fine.

---

## Marketing copy reference (public bullets)

Use these for **parity checks** against `resources/views/landing/pricing.blade.php`:

### Starter — TZS 12,000/mo

- 1 business location  
- Up to 500 products (SKU + category control)  
- Core POS + invoice print workflows  
- Basic purchase and stock tracking  
- 7-day free trial  

### Business — TZS 22,000/mo (most popular)

- 3 locations, synchronized inventory visibility  
- Unlimited products, suppliers, customers  
- Full manufacturing + batch production  
- Stock movements + adjustments  
- Sale orders + bulk sales flow  
- Priority support  
- 7-day free trial  

### Enterprise — TZS 30,000/mo

- Unlimited locations + teams (set `max_users` in policy)  
- Everything in Business + advanced reporting depth  
- Financial visibility (expenses, transfers — per final product split)  
- Dedicated support + custom onboarding  
- 7-day free trial  

---

## Change log

| Date | Change |
|------|--------|
| 2026-05-04 | Initial spec from marketing pricing page and Filament resource inventory. |
