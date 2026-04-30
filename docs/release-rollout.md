# Unified Release Rollout

This rollout is for the unified Laravel release copy only.

## Scope

- primary live site: `aplusdigitizing.com`
- one Laravel application for:
  - customer website
  - admin portal
  - team portal
- hosted payments only:
  - Stripe Checkout
  - 2Checkout

## SQL install order

1. `database/sql/sites.sql`
2. `database/sql/site_domains.sql`
3. `database/sql/site_pricing_profiles.sql`
4. `database/sql/site_promotions.sql`
5. `database/sql/site_promotion_claims.sql`
6. `database/sql/customer_activation_tokens.sql`
7. `database/sql/customer_password_reset_tokens.sql`
8. `database/sql/customer_credit_ledger.sql`
9. `database/sql/payment_transactions.sql`
10. `database/sql/payment_transaction_items.sql`
11. `database/sql/payment_provider_events.sql`
12. `database/sql/quote_negotiations.sql`
13. `database/sql/order_workflow_meta.sql`
14. `database/sql/email_templates.sql`
15. `database/sql/security_audit_events.sql`
16. `database/sql/admin_login_attempts.sql`
17. `database/sql/supervisor_team_members.sql`
18. `database/sql/performance_indexes.sql`
19. `database/sql/phase_two_safe_columns.sql`
20. `database/sql/phase_two_primary_site_backfill.sql`
21. `database/sql/phase_two_primary_site_seed.sql`
22. `database/sql/phase_two_password_security.sql`

## Environment

Required core settings:

- database connection
- mail settings
- shared uploads path
- `PAYMENT_DEFAULT_PROVIDER`
- `TURNSTILE_ENABLED`, `TURNSTILE_SITE_KEY`, `TURNSTILE_SECRET_KEY` if bot protection should be active

Stripe:

- `STRIPE_SECRET_KEY`
- `STRIPE_PUBLISHABLE_KEY`
- `STRIPE_WEBHOOK_SECRET`

2Checkout:

- `TWOCHECKOUT_SELLER_ID`
- `TWOCHECKOUT_SECRET_WORD`
- `TWOCHECKOUT_PURCHASE_URL`

## Callback endpoints

- Stripe webhook: `/webhooks/stripe`
- 2Checkout notification: `/payment-notification.php`
- Hosted return: `/successpay.php`

## Post-deploy commands

```bash
php artisan optimize:clear
php artisan view:cache
php artisan release:check --strict
php artisan passwords:backfill-secure-hashes
```

Password note:

- `database/sql/phase_two_password_security.sql` is part of the rollout and prepares the secure password columns.
- `php artisan passwords:backfill-secure-hashes` is the required deploy step that upgrades existing legacy plain-text passwords into secure hashes without forcing customers to reset.
- Do not bake a shared test password such as `123` into production SQL or production deploy steps.
- If a production admin password must be reset, do it as a one-off controlled action after deployment, not as part of the general rollout script.

If you want a staged password migration first:

```bash
php artisan passwords:backfill-secure-hashes --keep-legacy
```

## First QA path

1. admin login
2. customer signup
3. email verification
4. welcome offer payment
5. customer login
6. create quote
7. create order
8. admin assignment
9. team completion
10. admin completion
11. billing payment with Stripe
12. billing payment with 2Checkout
13. download/preview rules

## Success criteria

- existing `1dollar` data remains visible and functional
- customer accounts remain site-scoped
- payments mark invoices correctly
- overpayments create customer credit
- admin/team workflow remains intact
- no password viewing remains in reports or UI
