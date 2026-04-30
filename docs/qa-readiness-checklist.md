# Unified Platform QA Readiness

This checklist is for the unified Laravel release copy only.

## Manual SQL rollout

Apply these tables before testing flows that depend on them:

- `database/sql/sites.sql`
- `database/sql/site_domains.sql`
- `database/sql/site_pricing_profiles.sql`
- `database/sql/site_promotions.sql`
- `database/sql/site_promotion_claims.sql`
- `database/sql/customer_activation_tokens.sql`
- `database/sql/customer_remember_tokens.sql`
- `database/sql/customer_credit_ledger.sql`
- `database/sql/payment_transactions.sql`
- `database/sql/payment_transaction_items.sql`
- `database/sql/payment_provider_events.sql`
- `database/sql/quote_negotiations.sql`
- `database/sql/customer_password_reset_tokens.sql`
- `database/sql/security_audit_events.sql`
- `database/sql/admin_login_attempts.sql`
- `database/sql/order_workflow_meta.sql`
- `database/sql/email_templates.sql`
- `database/sql/supervisor_team_members.sql`
- `database/sql/performance_indexes.sql`

Apply the phase-two safe column updates as well:

- `database/sql/phase_two_safe_columns.sql`
- `database/sql/phase_two_primary_site_backfill.sql`
- `database/sql/phase_two_primary_site_seed.sql`
- `database/sql/phase_two_password_security.sql`

## App prep

1. Install dependencies.
2. Configure `.env` for:
   - database
   - mail
   - shared uploads path
   - `PAYMENT_DEFAULT_PROVIDER`
   - 2Checkout hosted payment settings
   - Stripe Checkout settings
   - Turnstile keys if bot protection should be active
3. Clear caches:
   - `php artisan optimize:clear`
   - `php artisan view:cache`
4. Run the release gate:
   - `php artisan release:check --strict`
5. Backfill secure password hashes:
   - `php artisan passwords:backfill-secure-hashes`
   - this is required for production rollout after `phase_two_password_security.sql`
   - do not use a shared test password in production

## Core customer tests

1. Public pages:
   - home
   - pricing
   - work process
   - contact
2. Signup:
   - sign up with a new email
   - confirm success message tells user to check spam/junk
3. Verification:
   - open activation email
   - confirm activation works
   - if a welcome offer is active, confirm redirect to `/member-offer.php`
4. Resend verification:
   - request a new verification email
   - confirm generic success response
5. Welcome offer payment:
   - confirm the payment page shows welcome payment amount
   - confirm hosted checkout transaction is created
   - confirm both Stripe and 2Checkout can be selected when configured
   - confirm successful return updates payment transaction
   - confirm successful provider callback/webhook marks the transaction verified
6. Customer portal:
   - login
   - remember me on login
   - dashboard
   - orders
   - quotes
   - billing
   - downloads/previews

## Offer tests

1. In admin, create a site offer.
2. Confirm it appears on:
   - signup page
   - login page
   - activation email
3. Confirm pending-payment customers cannot browse directly to dashboard and are redirected to the offer gate.
4. Confirm the first eligible order gets the flat first-order amount.
5. Confirm welcome credit is applied when billing is created for that first order.

## Security checks

1. Inactive customer cannot log in before verification.
2. Customer on one site cannot access another site's customer data.
3. Preview-safe files still work under legacy-compatible gate rules.
4. Production digitizing files remain blocked until payment/credit rules allow them.
5. Resend verification and forgot password stay generic and do not reveal account existence.

## Internal portal checks

1. Admin login under `/admin` and legacy `/v`.
2. Team login under `/team`.
3. Existing admin order workflow still works.
4. Existing team workflow still works.
5. New site offers screen loads under `/v/site-offers.php`.
6. New site pricing screen loads under `/v/site-pricing.php`.
7. Confirm the seeded `1dollar` pricing profiles exist before testing price previews or completion pricing.
8. Confirm security events load under `/v/security-events.php`.
9. Confirm simulate login works for admin and can be exited safely.

## Payment setup notes

Stripe:

- `STRIPE_SECRET_KEY`
- `STRIPE_PUBLISHABLE_KEY`
- `STRIPE_WEBHOOK_SECRET`
- webhook target: `/webhooks/stripe`

2Checkout:

- `TWOCHECKOUT_SELLER_ID`
- `TWOCHECKOUT_SECRET_WORD`
- `TWOCHECKOUT_PURCHASE_URL`
- notification target: `/payment-notification.php`

General:

- `PAYMENT_DEFAULT_PROVIDER=stripe_checkout` or `2checkout_hosted`
- `/successpay.php` remains the hosted return URL used by the customer payment flow
