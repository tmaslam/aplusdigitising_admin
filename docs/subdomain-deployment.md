# Admin Portal Subdomain Setup

This project supports both of these setups without code changes:

- Local path-based access: `http://localhost/admin-portal/public`
- Production subdomain: `https://admin.example.com`

## Folder Layout

Keep the project and the shared upload folder side by side:

```text
/home/account/public_html/
├── admin-portal/
└── upload/
```

For production, the subdomain document root must point to:

```text
/home/account/public_html/admin-portal/public
```

Do not point the subdomain to the `admin-portal` project root.

## Local Configuration

Use the values in:

[`/.env.local.example`](/Users/abid/Desktop/Projects/Tariq/1dollar/admin-portal/.env.local.example)

Key settings:

- `APP_URL=http://localhost/admin-portal/public`
- `APP_FORCE_URL=` blank
- `APP_FORCE_HTTPS=false`
- `SESSION_DOMAIN=null`
- `SESSION_COOKIE=onedollar_admin_portal_local`
- `SHARED_UPLOADS_PATH` should point to your shared `upload` folder if it is not a sibling of `admin-portal`

## Production Subdomain Configuration

Use the values in:

[`/.env.production-subdomain.example`](/Users/abid/Desktop/Projects/Tariq/1dollar/admin-portal/.env.production-subdomain.example)

Key settings:

- `APP_URL=https://admin.example.com`
- `APP_FORCE_URL=https://admin.example.com`
- `APP_FORCE_HTTPS=true`
- `SESSION_DOMAIN=admin.example.com`
- `SESSION_COOKIE=onedollar_admin_portal`
- `SESSION_SECURE_COOKIE=true`
- `SHARED_UPLOADS_PATH=/home/account/public_html/upload`

Using the subdomain itself as `SESSION_DOMAIN` keeps the admin login cookie isolated from the main website.

## Apache Example

Example subdomain virtual host:

```apache
<VirtualHost *:80>
    ServerName admin.example.com
    DocumentRoot /home/account/public_html/admin-portal/public

    <Directory /home/account/public_html/admin-portal/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

If SSL is enabled, use the same `DocumentRoot` inside the HTTPS virtual host and keep:

- `APP_URL=https://admin.example.com`
- `APP_FORCE_HTTPS=true`
- `SESSION_SECURE_COOKIE=true`

## Deploy Steps

1. Upload the full `admin-portal` project folder.
2. Make sure the subdomain document root points to `admin-portal/public`.
3. Create or update `.env` using the production example.
4. Make sure the web server can write to:
   - `storage`
   - `bootstrap/cache`
   - the shared `upload` folder
5. Run:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Notes

- The app generates links with Laravel helpers, so moving from the local path setup to the production subdomain only requires the `.env` update.
- `APP_FORCE_URL` is useful when the site is behind hosting panels, proxies, or SSL termination that can otherwise produce the wrong host in generated links.
- If the shared upload folder sits next to `admin-portal`, `SHARED_UPLOADS_PATH` can be left blank.
