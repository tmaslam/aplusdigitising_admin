# Unified Platform Phase Two

## Goal

Phase two moves the customer-facing website into the same Laravel application that already powers the admin and team portals. The end state is one application with three layers:

- customer website at `/`
- admin portal at `/admin` with legacy `/v/*` compatibility during rollout
- team portal at `/team`

The system must support multiple customer-facing brands while keeping one shared internal workflow and one shared fulfillment team.

## What Exists Today

### Laravel application

- Admin and team already live in one Laravel project.
- Admin routes are still mostly legacy-compatible under `/v/*`.
- Team routes already live under `/team/*`.
- Root `/` is only an internal landing page.
- The Laravel app talks directly to legacy tables such as `users`, `orders`, `billing`, and `attach_files`.
- Newer workflow features use sidecar SQL tables instead of formal migrations:
  - `order_workflow_meta`
  - `customer_credit_ledger`
  - `email_templates`

### Legacy customer website

- Customer pages still live in the old PHP application.
- Customer auth, account state, and downloads depend on `$_SESSION`, raw SQL, and constants from `db.php`.
- Site identity is partly hardcoded through `ORDER_WEBSITE`, `WEBSITE_ADDRESS`, and related constants.
- The legacy schema already stores a `website` column on several tables, but the current website still behaves like a single-site app.
- Payment marking is fragile because success depends heavily on redirect-time logic in the legacy payment pages.
- Quote rejection today is effectively a disapproval/edit path, not a structured negotiation flow.

## Architecture Direction

## 1. Site resolution

- Introduce normalized `sites` and `site_domains` tables.
- Resolve the active site from the request host.
- Keep a config fallback so the app works before the new tables are installed.
- Use the normalized site record as the source of truth for branding, support email, and pricing policy.

## 2. Customer isolation

- Keep customers in the existing `users` table for backward compatibility.
- Add nullable `site_id` to customer-facing tables and backfill from the legacy `website` string.
- Treat customer auth as `site + email/password`, never global email lookup.
- Internal users remain globally managed and shared across all sites.

## 3. Pricing

- Preserve current customer-level pricing fields:
  - `normal_fee`
  - `middle_fee`
  - `urgent_fee`
  - `super_fee`
  - limits and prepaid balance fields
- Add site-level pricing profiles and promotions as configurable layers.
- Pricing precedence should be:
  - site default
  - site promotion/package rule
  - customer override
  - order-specific override where workflow metadata requires it

## 4. Reporting

- Add site-aware filtering to billing, due, received, ledger, team, and transaction reporting.
- Preserve the current site as the default report context.
- Internal reporting remains cross-site capable; customer screens remain site-scoped only.

## 5. File access and notifications

- Downloads, uploads, emails, and customer communications must stay site-scoped.
- Site context must flow into:
  - customer login
  - password reset
  - order lookup
  - billing lookup
  - file delivery
  - email sender selection
  - notification templates

## 6. Customer experience

- Keep the current customer website look and flow familiar.
- Improve usability, clarity, and responsiveness without a drastic redesign.
- Customer pages should be responsive across phone, tablet, and desktop.
- Security and performance take priority over decorative changes.

## 7. Payments

- Replace redirect-only payment marking with a transaction-based approach.
- Store an internal payment transaction record before redirect.
- Reconcile billing updates from provider responses, callbacks, or retryable admin reconciliation.
- Keep payment and credit-ledger effects idempotent so retries do not double-pay invoices.

## 8. Quote negotiation

- Add structured customer quote rejection with reason and target price.
- Let admin review and either accept, counter, or reject the requested lower price.
- Preserve the final admin-controlled pricing decision and the existing internal workflow.

## Safe Rollout Strategy

### Step 1

- Create `sites`, `site_domains`, `site_pricing_profiles`, and `site_promotions`.
- Add nullable `site_id` columns to legacy-facing tables.
- Backfill `site_id` from existing `website` values.

### Step 2

- Change root `/` to a site-aware customer layer in Laravel.
- Preserve internal workflow by keeping `/v/*` and `/team/*` working.
- Introduce `/admin` as the modern admin entry path.

### Step 3

- Rebuild customer login, dashboard, orders, billing, downloads, profile, and communication in Laravel.
- Preserve legacy business rules for release gating, billing status, and credit handling.
- Rebuild payment confirmation on a transaction/reconciliation model rather than legacy redirect-side updates.
- Rebuild quotes with structured negotiation instead of unstructured disapproval only.

### Step 4

- Cut traffic from the old PHP customer app to the new Laravel customer portal.
- Keep the old schema intact until all customer flows have been validated in production.

## Risk Controls

- Never query customer data without explicit site scope.
- Keep legacy `website` values during transition so reporting and rollback remain possible.
- Prefer sidecar tables and nullable columns over schema rewrites.
- Keep admin/team operational paths stable while migrating the customer layer.
- Preserve the current site as the baseline behavior for pricing, approvals, file access, and notifications.
