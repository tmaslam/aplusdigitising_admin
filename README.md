# A Plus Digitizing Platform

This is a GitHub-ready development copy of the Laravel application for the A Plus Digitizing website, customer portal, admin portal, and team workflow.

What is included:

- application source code
- local development env samples
- one clean database bootstrap SQL for a fresh local install
- payment configuration notes for production handoff
- production/staging SQL seed for default system email templates

What is intentionally not included:

- live `.env` files
- deployment scripts
- vendor dependencies
- generated build artifacts

## Tech Stack

- PHP 8.4+
- Laravel 13
- MySQL 8+
- Node.js 20+
- Vite

## Local Setup

### 1. Install dependencies

```bash
composer install
npm install
```

### 2. Create your local env file

```bash
cp .env.example .env
```

Update the database, mail, and hostname values for your machine.

### 3. Create a local database

Create an empty MySQL database, for example:

```sql
CREATE DATABASE onedollar_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Import the clean local database

Use the bundled clean bootstrap SQL:

```bash
mysql -u root -p onedollar_dev < database/sql/local_development_setup.sql
```

This file includes:

- the blank legacy schema
- phase-two portal additions
- localhost site-domain entries
- a default admin login for development

### 5. Default admin login

After import, you can sign in to the admin portal with:

- username: `admin`
- password: `K6v!Q2m#9LpR`

Admin login URL:

- `http://localhost/v`

### 6. Generate the app key

```bash
php artisan key:generate
```

### 7. Prepare runtime directories and caches

```bash
php artisan optimize:clear
php artisan view:cache
```

### 8. Start the app

```bash
php artisan serve
npm run dev
```

## Local Environment Notes

- `MAIL_MAILER=log` is the safest default for local development.
- `TURNSTILE_ENABLED=false` is expected locally unless you configure Cloudflare Turnstile keys.
- `TWOCHECKOUT_SIMULATION_ENABLED=false` should remain disabled unless you intentionally set up a safe local payment test path.
- `SHARED_UPLOADS_PATH` should point to a writable local folder if you want to test uploads outside `storage`.

## Clean Database Asset

The repository includes:

- `database/sql/local_development_setup.sql`
- `database/sql/production_email_templates_seed.sql`

Use this for a fresh local bootstrap. This GitHub package intentionally keeps only this one SQL setup file.

Use the production/staging seed on an existing live database when you want to create the default admin-manageable email templates without rebuilding the schema.

## Production Payment Configuration

This application supports hosted checkout through:

- `2Checkout` for the primary `1dollar` site by default
- `Stripe Checkout` as an alternate hosted provider

The payment flow is security-sensitive. Production must use real provider secrets, valid callback URLs, and simulation disabled.

### Required Production Base Settings

Set these first in `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_FORCE_URL=https://your-domain.com
SESSION_DOMAIN=your-domain.com

PAYMENT_DEFAULT_PROVIDER=2checkout_hosted
TWOCHECKOUT_SIMULATION_ENABLED=false
```

### Stripe Production Configuration

```env
STRIPE_SECRET_KEY=sk_live_xxxxxxxxxxxxxxxxxxxxx
STRIPE_PUBLISHABLE_KEY=pk_live_xxxxxxxxxxxxxxxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxxxxxxxxxx
STRIPE_API_BASE=https://api.stripe.com/v1
STRIPE_WEBHOOK_TOLERANCE=300
```

If Stripe should be the active hosted provider for a site, set:

```env
PAYMENT_DEFAULT_PROVIDER=stripe_checkout
```

### Stripe Callback URLs

- success return:
  - `https://your-domain.com/successpay.php`
- webhook:
  - `https://your-domain.com/webhooks/stripe`

### 2Checkout Production Configuration

```env
PAYMENT_DEFAULT_PROVIDER=2checkout_hosted
TWOCHECKOUT_SELLER_ID=1359240
TWOCHECKOUT_SECRET_WORD=your_real_2checkout_secret_word
TWOCHECKOUT_PURCHASE_URL=https://www.2checkout.com/2co/buyer/purchase
TWOCHECKOUT_SIMULATION_ENABLED=false
TWOCHECKOUT_SIMULATION_CUSTOMER_ID=
TWOCHECKOUT_SIMULATION_CUSTOMER_EMAIL=
```

### 2Checkout Callback URLs

- return URL:
  - `https://your-domain.com/successpay.php`
- notification URL:
  - `https://your-domain.com/payment-notification.php`

### Production Cache Refresh

```bash
php artisan optimize:clear
php artisan view:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:cache
```

## Production / Staging Email Template Seed

The application supports admin-managed system email templates for account activation, password reset, customer submission confirmations, and completion emails.

On an existing staging or production database, run:

```bash
mysql -u YOUR_DB_USER -p YOUR_DB_NAME < database/sql/production_email_templates_seed.sql
```

This seed is idempotent:

- it inserts only missing templates
- it targets the `1dollar` site
- it does not overwrite templates that admin has already customized

The seeded template names are:

- `Customer Account Activation`
- `Customer Password Reset`
- `Customer Digitizing Order Confirmation`
- `Customer Vector Order Confirmation`
- `Customer Digitizing Quote Confirmation`
- `Customer Vector Quote Confirmation`
- `Customer Order Completed`
- `Customer Quote Completed`
- `Customer Quick Quote Completed`
