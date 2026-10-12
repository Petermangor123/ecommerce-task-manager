# Shoppn — Lab 3 Brand and Categories

PHP/MySQL e-commerce lab covering customer auth plus admin brand and category management.

## Local Setup

1. Copy this folder into your XAMPP `htdocs` tree (or keep it in this repository clone).
2. Import `database/shoppn.sql` into MySQL via phpMyAdmin.
3. Copy `core/db_cred.example.php` to `core/db_cred.php` and fill in your local credentials.
4. Open `http://localhost/e-commerce-labs/Lab_3_Brand_and_Categories/` in your browser.

`core/db_cred.php` is **not included** in this repository.

## Creating an Admin Account

1. Register via `/views/register.php`.
2. Promote the account in phpMyAdmin:

```sql
UPDATE customer SET user_role = 1 WHERE customer_email = 'you@example.com';
```

3. Log out and back in to use Brand and Category admin pages.
