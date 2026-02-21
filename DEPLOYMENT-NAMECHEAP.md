# Fixing HTTP 500 on Namecheap Stellar (asosignature.com)

Use this checklist to fix "This page isn't working / HTTP ERROR 500" on Namecheap Stellar shared hosting (cPanel).

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

**Option B – Laravel logs**  
In cPanel **File Manager**, go to your project folder and open:

- `storage/logs/laravel.log`  
  (reproduce the 500, then refresh or tail the file to see the latest error.)

**Option C – cPanel error log**  
In cPanel: **Metrics** → **Errors**, or look for `error_log` in your account root or in the domain’s document root folder.

---

## 2. Document root must be `public` (very common cause of 500)

Laravel must run from the **`public`** folder, not the project root.

- In **cPanel**: **Domains** → **Domains** (or **Addon Domains** if asosignature.com is an addon).
- Click **Manage** next to **asosignature.com**.
- Set **Document Root** to the **`public`** folder of your Laravel app, e.g.:
  - `public_html/asosignature/public`  
  or  
  - `domains/asosignature.com/public`  
  (exact path depends how you uploaded the app; it must end in `/public`.)
- If the document root points to the project root (where `artisan` and `vendor` are), you will get 500 or "No input file specified".

---

## 3. Production `.env` on the server

On the **server**, ensure `.env` exists in the project root (same level as `artisan`) and has at least:

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

# Database – use Namecheap/cPanel MySQL credentials
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_cpanel_db_name
DB_USERNAME=your_cpanel_db_user
DB_PASSWORD=your_cpanel_db_password

# Session & cache (file is fine on shared hosting)
CACHE_DRIVER=file
SESSION_DRIVER=file

# URLs – important for links and assets
SANCTUM_STATEFUL_DOMAINS=https://asosignature.com
STORE_FRONT_URL=https://asosignature.com
```

- **APP_KEY:** If missing or wrong, run on the server:  
  `php artisan key:generate`  
  (SSH or cPanel **Terminal** if available.)
- **DB_***: Create the database and user in cPanel **MySQL® Databases**, then put those credentials here.
- **APP_URL / STORE_FRONT_URL:** Use `https://asosignature.com` (no trailing slash).

Use the rest of your current `.env` (mail, Flutterwave, etc.) and keep production secrets only on the server.

---

## 4. File permissions

Laravel needs to write to `storage` and `bootstrap/cache`. On the server (SSH or cPanel File Manager → right‑click → Change Permissions):

```bash
chmod -R 775 storage bootstrap/cache
```

If the web server user is different from your FTP user, you may need:

```bash
chown -R nobody:nobody storage bootstrap/cache
```

(Namecheap shared hosting often uses `nobody`; support can confirm.)

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

- If **Composer** isn’t available on the server, run `composer install --no-dev` locally, upload the `vendor` folder, then run the `php artisan` commands on the server (e.g. cPanel **Terminal** or **PHP Script** runner).
- After any `.env` change, run:  
  `php artisan config:clear` then `php artisan config:cache`.

---

## 6. PHP version

This project requires **PHP 8.2+**. In Namecheap cPanel:

- **Software** → **Select PHP Version** (or **Exclusive for Namecheap Customers** → **Select PHP Version**).
- Set PHP to **8.2** or **8.3** and save.

---

## 7. Trust proxies (if links are wrong or you use HTTPS)

If the site is behind Namecheap’s proxy and you get wrong URLs or redirect issues, the app trusts the proxy. In `app/Http/Middleware/TrustProxies.php`, set:

```php
protected $proxies = '*';
```

(Already set in this project for shared hosting.)

---

## 8. Quick checklist

| Check | Action |
|-------|--------|
| See real error | `APP_DEBUG=true` temporarily or read `storage/logs/laravel.log` / cPanel Errors |
| Document root | Points to **`public`** folder (e.g. `.../public`) |
| `.env` on server | Exists with production values, correct DB_*, APP_URL, APP_KEY |
| APP_KEY | Run `php artisan key:generate` if missing |
| Permissions | `storage` and `bootstrap/cache` writable (775) |
| Caches | `php artisan config:cache` (and route/view cache) after any .env change |
| PHP version | 8.2 or 8.3 |
| Vendor/autoload | `composer install --no-dev` run (or `vendor` uploaded) |

After each change that affects config:

```bash
php artisan config:clear
php artisan config:cache
```

If the 500 persists, use the **exact error message** from `laravel.log` or the debug page (with `APP_DEBUG=true` temporarily) and share it with Namecheap support or for further debugging.
