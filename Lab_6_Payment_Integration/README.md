# Shoppn — Lab 6: Payment Integration

PHP and MySQL shop with a cart and Paystack checkout. This lab keeps the earlier catalogue and cart, and adds payment in Ghana cedis.

Paystack stays in **test mode**. Do not put live keys in this repository.

## Technologies

- PHP 8.2
- MySQL
- HTML, CSS, JavaScript
- Paystack Inline (test keys)
- PHPMailer (optional order email)

## Features

- Product listing, search, and category and brand filters
- Product details and add to cart
- Cart quantity updates and item removal
- Checkout with Paystack
- Server-side check of the payment before an order is saved
- Invoice page after a successful payment
- Admin pages for brands, categories, and products

## Folder layout

```
actions/        form handlers and the payment check
classes/        database queries
controllers/    between the pages and the classes
core/           session, base URL, config loading
core/private/   your real db and Paystack files (not in git)
css/ js/        storefront assets
views/          pages
images/         product photos
database/       empty schema (shoppn.sql)
vendor/         PHPMailer
deploy/         FileZilla notes and blank config templates
```

## XAMPP setup

1. Clone this repo and make sure `Lab_6_Payment_Integration` sits under your web root, for example `C:\xampp\htdocs\e-commerce-labs\ecommerce-task-manager-repo\Lab_6_Payment_Integration`.
2. Start Apache and MySQL from the XAMPP control panel.
3. `APP_BASE` is worked out from the folder Apache is serving. You do not hard-code the lab path. These both work:
   - `http://localhost/e-commerce-labs/ecommerce-task-manager-repo/Lab_6_Payment_Integration/`
   - a copy of the lab folder directly under `htdocs`

## MySQL

`database/shoppn.sql` creates the tables and drops them first. It does not load products. Import it into an empty database.

1. Open phpMyAdmin.
2. Create a database (local labs use the name `shoppn`).
3. Select it, open Import, and choose `database/shoppn.sql`.
4. If you already have a full database from your own machine, export that from local phpMyAdmin and import the export instead. Do not commit that dump.

Copy `deploy/templates/db_cred.php` to `core/private/db_cred.php` and fill in your own host, user, password, and database name.

`core/db_cred.php` in the project root of `core/` is only a loader. It does not contain a password.

On localhost the private file can keep the XAMPP login. On any other hostname it should use the hosting login. Do not commit `core/private/db_cred.php`.

## Paystack test mode

1. In the Paystack dashboard, copy your **test** public and secret keys (`pk_test_...` and `sk_test_...`).
2. Copy `deploy/templates/paystack_config.php` to `core/private/paystack_config.php`.
3. Paste the test keys there. Leave `PAYSTACK_CURRENCY` as `GHS`.
4. Leave `MAIL_ENABLED` as `false` unless you have a mailbox app password you are willing to store only in that private file.

`core/paystack_config.php` is a loader. The secret key must stay in `core/private/paystack_config.php`, which git ignores.

Do not switch this lab to `pk_live_` / `sk_live_` keys.

## How payment is checked

The checkout page opens Paystack in the browser. Paystack sends the shopper back with a transaction reference. `actions/process_payment_action.php` does not trust that callback on its own. It asks Paystack's verify API, using the secret key on the server, and only then creates the order.

The check requires:

- Paystack status `success`
- the same reference that came back from checkout
- currency `GHS`
- the amount paid matches the total stored for that checkout

`classes/CartClass.php` then writes the order, line items, and payment row in one database transaction and clears the cart. The same Paystack reference cannot create two orders.

## Trying checkout

1. Open the lab in the browser and add a product to the cart.
2. Register or log in. Checkout sends guests to the login page.
3. Open the cart and choose Proceed to Checkout.
4. Pay with a Paystack **test** card (see Paystack's test payments docs). A common test card is `4084084084084081`, any future expiry, CVV `408`. If asked, PIN `0000` and OTP `123456`.
5. After Paystack returns, the shop should show the invoice. No charge is a real settlement while the keys are test keys.

## Deploying

See `deploy/FILEZILLA_CHECKLIST.md`.

Short version:

- Upload the site over SFTP. Include `.htaccess` (FileZilla hides it until you force hidden files).
- Put `db_cred.php` and `paystack_config.php` in `shoppn-private` next to `public_html` when the host allows it. Otherwise use `core/private/` and confirm a direct browser request gets 403.
- Turn on the host SSL certificate. The htaccess file redirects the live site to https and leaves localhost on http.
- Import the database with phpMyAdmin. Do not leave the `.sql` file in the public site if you can avoid it.
- Keep Paystack in test mode until you deliberately go live.

## What is not in git

- `core/private/db_cred.php`
- `core/private/paystack_config.php`
- `.env` files, logs, and any database dump that contains customer data
