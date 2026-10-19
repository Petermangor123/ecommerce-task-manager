# Shoppn — E-Commerce Lab 5: Cart Management

A PHP/MySQL e-commerce web application extending Labs 1–4 with a full IP-based shopping cart. Built as part of an E-Commerce Lab assignment.

## Technologies

- PHP (MVC structure — no framework)
- MySQL
- HTML, CSS, JavaScript

## MVC Structure

```
actions/        — handle POST input, set sessions, redirect
classes/        — model layer (database queries)
controllers/    — coordinate models and return results
core/           — shared helpers (session, redirect, auth guards, CSRF)
views/          — HTML templates
database/       — SQL schema (shoppn.sql)
images/         — uploaded product images (excluded from version control)
css/            — stylesheet
js/             — client-side validation
```

## Tasks Implemented

- **Task 1** — Database setup (`database/shoppn.sql`)
- **Task 2** — Folder structure and core files
- **Task 3** — Customer registration with server-side and client-side validation
- **Task 4** — Customer login, session management, and role-based navigation
- **Task 5** — Admin dashboard
- **Task 6** — Brand management (CRUD)
- **Task 7** — Category management (CRUD)
- **Task 8** — Product management (CRUD with image upload)
- **Task 9** — Product display for admin
- **Task 10** — Customer-facing product listing, category/brand filtering, search, and product details
- **Task 11** — Add to Cart (IP-based guest cart, duplicate detection, header cart summary)
- **Task 12** — Cart management (view cart, update quantity, remove items)

## Cart Design

Cart records are keyed by visitor IP address (`ip_add` column). No login is required to add or view cart items. When a customer logs in, their `customer_id` is optionally stored in the `c_id` column of the same cart rows, but the IP remains the primary lookup key throughout the session.

## Local Setup

1. Clone or copy the `Lab_5_Cart_Management` folder into your XAMPP `htdocs` directory.
2. Import `database/shoppn.sql` into MySQL via phpMyAdmin to create the `shoppn` database and all required tables.
3. Copy `core/db_cred.example.php` to `core/db_cred.php` and fill in your local database credentials.
4. Open `core/core.php` and confirm that `APP_BASE` matches your actual installation path:

   ```php
   define('APP_BASE', '/e-commerce-labs/Lab_5_Cart_Management/');
   ```

   If you installed the project at a different path (for example, directly under `htdocs/Lab_5_Cart_Management/`), update this constant to match, for example:

   ```php
   define('APP_BASE', '/Lab_5_Cart_Management/');
   ```

5. Open `http://localhost/e-commerce-labs/Lab_5_Cart_Management/index.php` in your browser.

## Database Credentials

`core/db_cred.php` is **not included** in this repository for security reasons. A template is provided at `core/db_cred.example.php`.

Create `core/db_cred.php` manually with your local credentials:

```php
<?php
define('DB_HOST', 'localhost');      // or '127.0.0.1'
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'shoppn');
```

## Administrative Access

Administrative features (dashboard, brand, category, and product management) require an account with `user_role = 1` in the `customer` table. Role assignment must be performed by the database administrator directly in MySQL. Do not use ad-hoc SQL from this README to promote users.

## Checkout Placeholder

The **Checkout** button on the cart page is intentionally disabled. `views/checkout.php` does not exist in this lab. Checkout will be implemented in Task 13 in a future lab. The disabled button and "Coming Soon" label are the expected state of Lab 5.

## Product Images

Sample product images are included in `images/products/`. Images uploaded through the admin product form are excluded from version control (see `.gitignore`). After cloning, the `images/products/` directory contains only the committed sample images and a `.gitkeep` file.
