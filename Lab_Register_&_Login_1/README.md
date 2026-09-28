# Shoppn — E-Commerce Lab

A PHP/MySQL e-commerce web application built as part of an E-Commerce Lab assignment.

## Technologies

- PHP (MVC structure — no framework)
- MySQL
- HTML, CSS, JavaScript

## MVC Structure

```
actions/        — handle POST input, set sessions, redirect
classes/        — model layer (database queries)
controllers/    — coordinate models and return results
core/           — shared helpers (session, redirect, auth guards)
views/          — HTML templates
```

## Tasks Implemented

- **Task 1** — Database setup (`database/shoppn.sql`)
- **Task 2** — Folder structure and core files
- **Task 3** — Customer registration with server-side and client-side validation
- **Task 4** — Customer login, session management, and role-based navigation

## Local Setup

1. Clone the repository into your XAMPP `htdocs` folder.
2. Import `database/shoppn.sql` into MySQL via phpMyAdmin.
3. Create `core/db_cred.php` with your local credentials (see below).
4. Open `http://localhost/shoppn/` in your browser.

## Database Credentials

`core/db_cred.php` is **not included** in this repository for security reasons.

Create it manually with the following content:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'shoppn');
?>
```

## Creating an Admin Account

1. Register a new account via `/views/register.php`.
2. Run the following SQL in phpMyAdmin to promote the account to admin:

```sql
UPDATE customer SET user_role = 1 WHERE customer_email = 'you@example.com';
```

3. Log out and back in — the Admin link will appear in the header.
