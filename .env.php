APP_NAME="APLUS Digitizing (Local)"
APP_ENV=local
APP_KEY=base64:dDF9RxNCFK8UM2ohlV2fVbdNCTskz+XhJ7UNazaByf0=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

APP_TIMEZONE=UTC

# ========================

# DATABASE (LOCAL)

# ========================

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aplus_db
DB_USERNAME=root
DB_PASSWORD=

# ========================

# SESSION

# ========================

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_COOKIE=laravel_session
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax

# ========================

# MAIL (DISABLED / LOG)

# ========================

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="[test@example.com](mailto:test@example.com)"
MAIL_FROM_NAME="Laravel Local"

# ========================

# FILESYSTEM / CACHE

# ========================

FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
CACHE_STORE=file

# ========================

# OPTIONAL / KEEP IF NEEDED

# ========================

TURNSTILE_ENABLED=false
TURNSTILE_SITE_KEY=
TURNSTILE_SECRET_KEY=

PAYMENT_DEFAULT_PROVIDER=2checkout_hosted

TWOCHECKOUT_SELLER_ID=
TWOCHECKOUT_SECRET_WORD=
TWOCHECKOUT_PURCHASE_URL=https://www.2checkout.com/2co/buyer/purchase

TWOCHECKOUT_SIMULATION_ENABLED=true
TWOCHECKOUT_SIMULATION_CUSTOMER_ID=
TWOCHECKOUT_SIMULATION_CUSTOMER_EMAIL=

PRIMARY_SITE_KEY="1dollar"
PRIMARY_SITE_LEGACY_KEY="1dollar"
PRIMARY_SITE_HOST="localhost"

SITE_SUPPORT_EMAIL="[test@example.com](mailto:test@example.com)"
SITE_FROM_EMAIL="[test@example.com](mailto:test@example.com)"
ADMIN_ALERT_EMAIL="[test@example.com](mailto:test@example.com)"

SITE_COMPANY_ADDRESS="Local Development Environment"
