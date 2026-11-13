# FileZilla deployment checklist — Lab 6 (Shoppn)

Nothing in this checklist switches Paystack to live keys or fills in hosting passwords. Use the values your host gives you when you have them.

The site stays in Paystack **test mode**.

## Where the site can live

`APP_BASE` is calculated from the folder Apache is serving. These all work without editing PHP:

- `http://localhost/e-commerce-labs/Lab_6_Payment_Integration/`
- `https://your-domain/` if you upload the project **contents** into `public_html`
- `https://your-domain/shop/` if you upload the project into `public_html/shop`

CSS, JavaScript, images, product links, the cart, checkout, and invoices use that same base path.

## 1. Database in phpMyAdmin (do this before relying on the live site)

`database/shoppn.sql` is a schema only. It drops those tables and does not contain your products. Importing that file alone gives you an empty shop.

On your PC, in local phpMyAdmin:

1. Open the `shoppn` database.
2. Click **Export**.
3. Method: **Quick**. Format: **SQL**.
4. Download the `.sql` file and keep it off the website (Desktop or a USB folder is fine).

On the hosting phpMyAdmin, after the host has created an empty database:

1. Select that empty database. Do not import into a database that already has tables you need, because a full Shoppn export may drop and recreate tables.
2. Click **Import**.
3. Choose the `.sql` file you exported.
4. Click **Go** and confirm the tables are present: `brands`, `categories`, `products`, `customer`, `cart`, `orders`, `orderdetails`, `payment`.

Then copy `deploy/templates/db_cred.php` to the private config location in section 3 and replace the four placeholders with the host, username, password, and database name from the hosting panel. Do not invent them.

`DB_HOST` is often `localhost` even on shared hosting, because PHP and MySQL are on the same machine. Use whatever the panel shows.

## 2. Paystack and mail

Keep the existing test keys (`pk_test_` and `sk_test_`). Do not create live keys for this upload.

Mail can stay disabled (`MAIL_ENABLED` false) until you have a real mailbox password stored in the private config file.

## 3. Private configuration

The site loads the first file it finds:

1. `{parent of the document root}/shoppn-private/db_cred.php` and `paystack_config.php`
   - Hosting example: `/home/youraccount/shoppn-private/` when the site is in `/home/youraccount/public_html/`
   - This XAMPP PC: `C:\xampp\shoppn-private\` (that folder is not on the web)
2. Otherwise `core/private/` inside the project. Apache is told to refuse direct downloads of that folder.

Local XAMPP already uses `core/private/`. Leave those files in place so the lab keeps running.

On the host, prefer option 1. Copy your local `core/private/db_cred.php` and `core/private/paystack_config.php` with FileZilla into `shoppn-private` **next to** `public_html`, not inside it. Then edit only `db_cred.php` with the hosting database values.

Also upload `deploy/templates/shoppn-private.htaccess` as `shoppn-private/.htaccess` so a mistaken upload into the website is still blocked.

If the host will not let you store files outside `public_html`, upload `core/private/` as well and check section 6. The `.htaccess` rules are the protection in that case.

## 4. What to upload into the website folder

Upload these into `public_html` or into the subfolder you chose:

- `.htaccess` (hidden file — in FileZilla use **Server > Force showing hidden files** before the transfer)
- `index.php`
- `logout.php`
- `actions/`
- `classes/`
- `controllers/`
- `core/` (loaders and `core/private/.htaccess`)
- `css/`
- `images/` including `images/products/`
- `js/`
- `vendor/`
- `views/`

If you used the outside `shoppn-private` folder, you may skip uploading:

- `core/private/db_cred.php`
- `core/private/paystack_config.php`

## 5. What must not go in the website folder

- `core/private/db_cred.php` and `core/private/paystack_config.php` when the outside `shoppn-private` folder is available
- `database/` (the SQL dump). Import it with phpMyAdmin instead
- `deploy/` (this checklist and the blank templates)
- `.git/`, `.gitignore`, editor folders, logs
- Any file named `.env`

FileZilla: use **SFTP** if the host offers it. Transfer type **Auto** is enough. Do not overwrite the private config on the server with the template placeholders.

## 6. Checks after upload

Open these on the real domain (https):

- `/` or `/your-folder/` — homepage, stylesheet, and product photos
- a product details URL
- search
- cart
- checkout (login required). Paystack must still open with the **test** public key
- an invoice URL while logged in as the customer who owns the order

These addresses must **not** show PHP source or a password. They should be forbidden (403) or a normal 404:

- `/core/private/db_cred.php`
- `/core/private/paystack_config.php`
- `/core/db_cred.php`
- `/core/paystack_config.php`
- `/database/shoppn.sql`

A public page must not print PHP warnings, file paths, or SQL errors. Those are written to the server error log instead.

## 7. HTTPS

`.htaccess` redirects visitors to `https://` except when the host is `localhost` or `127.0.0.1`, so XAMPP is unchanged.

Turn on the host’s free SSL certificate (Let’s Encrypt or the panel’s SSL tool) **before** you rely on that redirect. If the certificate is missing, the redirect will fail.

Some hosts set `X-Forwarded-Proto` instead of `HTTPS`. The redirect rule and the PHP session cookie both treat that header as HTTPS.

The session cookie is marked Secure only on HTTPS requests, and HttpOnly so JavaScript cannot read it.
