# Fixing HTTP 500 on Hostinger (asosignature.com)

Use this checklist to fix "This page isn't working / HTTP ERROR 500" on Hostinger.

---

## 1. See the actual error (do this first)

The 500 page hides the real error. To find it:

**Option A – Enable debug temporarily (only for debugging)**  
On the server, edit `.env`:

```env
APP_DEBUG=true
APP_ENV=local
```

Reload asosignature.com. You should see the real error (e.g. missing key, database, permission).  
**Important:** Set `APP_DEBUG=false` and `APP_ENV=production` again after fixing.

**Option B – Use Laravel logs**  
On Hostinger:

- Open **File Manager** → go to your project folder.
- Check `storage/logs/laravel.log` (tail the file after reproducing the 500).

**Option C – Hostinger error log**  
In hPanel: **Advanced** → **Error Logs**, or look for `error_log` in your domain’s root or `public_html`.

---

## 2. Document root must be `public`

Laravel must run from the **`public`** folder, not the project root.

- In **Hostinger hPanel**: **Domains** → your domain → **Document Root** (or **Public HTML**).
- Set it to the **`public`** folder of your Laravel app, e.g.:
  - `public_html/asosignature/public`  
  or  
  - `domains/asosignature.com/public`
- If the document root points to the project root (where `artisan` and `vendor` are), you will get 500 or "No input file specified".

---

## 3. Production `.env` on the server

On the **server**, ensure `.env` exists and has at least:

```env
APP_NAME="AsoSignature"
APP_ENV=production
APP_KEY=base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
APP_DEBUG=false
APP_URL=https://asosignature.com
APP_ADMIN_URL=admin
APP_TIMEZONE=Africa/Lagos
APP_LOCALE=en
APP_CURRENCY=USD

# Database – use Hostinger MySQL credentials from hPanel
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_hostinger_db_name
DB_USERNAME=your_hostinger_db_user
DB_PASSWORD=your_hostinger_db_password

# Session & cache (file is fine on shared hosting)
CACHE_DRIVER=file
SESSION_DRIVER=file

# URLs – important for links and assets
SANCTUM_STATEFUL_DOMAINS=https://asosignature.com
STORE_FRONT_URL=https://asosignature.com
```

- **APP_KEY:** If missing or wrong, run on the server:  
  `php artisan key:generate`  
  (SSH or Hostinger’s “Run PHP script” / terminal if available.)
- **DB_***: Must match the MySQL database you created in hPanel for this site.
- **APP_URL / STORE_FRONT_URL:** Use `https://asosignature.com` (no trailing slash).

Use the rest of your current `.env` (mail, Flutterwave, etc.) and keep production secrets only on the server, not in git.

---

## 4. File permissions

Laravel needs to write to `storage` and `bootstrap/cache`. On the server (SSH or File Manager):

```bash
chmod -R 775 storage bootstrap/cache
```

If your FTP user is different from the web server user, you may need:

```bash
chown -R www-data:www-data storage bootstrap/cache
```

(Replace `www-data` with the user Hostinger uses for PHP; support can confirm.)

---

## 5. Composer and caches

Run these from the **project root** (where `composer.json` and `artisan` are), not inside `public`:

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

- If `composer` isn’t available on shared hosting, run `composer install` locally with `--no-dev`, upload the `vendor` folder, then run the `php artisan` commands on the server (e.g. via Hostinger’s PHP runner or SSH).

---

## 6. PHP version

This project requires **PHP 8.2+**. In Hostinger:

- **hPanel** → **Advanced** → **PHP Configuration** (or **Select PHP Version**).
- Set PHP to **8.2** or **8.3**.

---

## 7. Trust proxies (if links still wrong or HTTPS issues)

If the site is behind Hostinger’s proxy and you get wrong URLs or redirect loops, trust the proxy. In `app/Http/Middleware/TrustProxies.php` set:

```php
protected $proxies = '*';
```

(Or use specific IPs if Hostinger provides them.)

---

## 8. Quick checklist

| Check | Action |
|-------|--------|
| See real error | `APP_DEBUG=true` temporarily or read `storage/logs/laravel.log` / Hostinger error log |
| Document root | Points to `public` folder |
| `.env` on server | Exists with production values, correct DB_*, APP_URL, APP_KEY |
| APP_KEY | Run `php artisan key:generate` if missing |
| Permissions | `storage` and `bootstrap/cache` writable (775) |
| Caches | `php artisan config:cache` (and route/view cache) after any .env change |
| PHP version | 8.2 or 8.3 |
| Vendor/autoload | `composer install --no-dev` run (or uploaded) |

After each change, clear config cache if you use it:

```bash
php artisan config:clear
# then
php artisan config:cache
```

If Hostinger still “doesn’t resolve” the issue, send them the **exact error message** from `laravel.log` or the debug page (with `APP_DEBUG=true` temporarily) so they can check server-level restrictions (PHP modules, open_basedir, etc.).
