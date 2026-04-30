# Admin Portal Deployment Guide

## Overview

This repository is configured for automatic deployment to `https://user.aplusdigitising.com/` via GitHub Actions.

**Local development environment is unaffected.** The deployment only pushes code from this GitHub repository to the live server. Your local XAMPP files at `C:\xampp\htdocs\aplus\` remain untouched.

## GitHub Secrets Required

Add these secrets to the GitHub repository (`tmaslam/aplusdigitising_admin`):

| Secret | Value | Description |
|--------|-------|-------------|
| `FTP_SERVER` | `192.64.118.116` or `premium355.web-hosting.com` | Your cPanel hosting server |
| `FTP_USERNAME` | `apluihej` | FTP username |
| `FTP_PASSWORD` | `P@cistan1!@` | FTP password |
| `ADMIN_FTP_SERVER_DIR` | `public_html/user/` | **Remote directory for the subdomain** (default if not set) |

To add secrets:
1. Go to https://github.com/tmaslam/aplusdigitising_admin/settings/secrets/actions
2. Click **New repository secret**
3. Add each secret above

## First-Time Server Setup

If `user.aplusdigitising.com` is a fresh subdomain, you need to complete these steps **once** before the automated deployment will work:

### 1. Ensure the subdomain document root is correct

In cPanel:
- Go to **Subdomains**
- Verify `user.aplusdigitising.com` points to `public_html/user/`
- **If the subdomain doesn't exist yet**, create it with document root `public_html/user/`

### 2. Upload the `.env` file

The `.env` file is **never deployed** by GitHub Actions (to protect secrets).

Manually upload your production `.env` file to `public_html/user/.env` via cPanel File Manager or FTP.

**Minimum required variables in production `.env`:**
```env
APP_NAME="Aplus Admin"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://user.aplusdigitising.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_production_db
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

SESSION_DOMAIN=user.aplusdigitising.com
SESSION_COOKIE=aplus_admin_portal
SESSION_SECURE_COOKIE=true

MAIL_MAILER=smtp
MAIL_HOST=your-mail-server
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-email-password
MAIL_FROM_ADDRESS="support@aplusdigitising.com"
MAIL_FROM_NAME="Aplus Digitizing"
```

### 3. Run the initialization script

After the first automated deployment completes:

1. Add `DEPLOY_INIT_TOKEN=your-random-secret-string` to the `.env` file on the server
2. Visit: `https://user.aplusdigitising.com/deploy-init.php?token=your-random-secret-string`
3. The script will create storage directories and clear caches
4. **Delete `deploy-init.php` from the server after successful initialization**

### 4. Set directory permissions

In cPanel File Manager or via FTP:
- Ensure `storage/` and all subdirectories are writable (chmod 755 or 775)
- Ensure `bootstrap/cache/` is writable

## How Deployment Works

1. **You push code to `main` branch** on GitHub
2. GitHub Actions automatically:
   - Checks out the code
   - Installs PHP 8.3 and Composer
   - Runs `composer install --no-dev --optimize-autoloader`
   - Deploys all files (including `vendor/`) to the server via FTPS
3. Files preserved on the server (never overwritten):
   - `.env`
   - `storage/` contents (logs, sessions, cache, views)
   - `bootstrap/cache/` compiled files

## Manual Deployment (if needed)

If you need to deploy manually without waiting for GitHub Actions:

```bash
# From your local machine
cd C:\xampp\htdocs\aplus
git add -A
git commit -m "Your changes"
git push origin main
```

The GitHub Actions workflow will trigger automatically.

## Troubleshooting

### Deployment fails with FTP error
- Verify the FTP credentials in GitHub secrets
- Check if the cPanel FTP account is active
- Try changing `protocol` in the workflow from `ftps` to `ftps-legacy` or `ftp`

### 500 Error after deployment
- Check that `.env` exists on the server
- Check `storage/logs/laravel.log` on the server via cPanel File Manager
- Run the `deploy-init.php` script to rebuild caches

### Missing vendor files
- The workflow installs Composer dependencies during deployment
- Ensure `composer.lock` is committed to git (`git add composer.lock`)

### Subdomain shows blank page
- Verify the subdomain document root in cPanel
- If document root is `public_html/user/`, the app should work with the root `index.php`
- If document root is `public_html/user/public/`, update the workflow `server-dir` to `public_html/user/public/`


---
Deployment triggered: 2026-04-30 21:38:00 UTC


Updated FTP credentials for subdomain deployment.
