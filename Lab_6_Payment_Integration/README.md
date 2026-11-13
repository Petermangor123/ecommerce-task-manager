# Shoppn — Lab 6: Payment Integration

A PHP and MySQL online shop that extends the earlier catalogue and cart with Paystack checkout and an invoice after payment.

## Objectives

- Let a customer review the cart and pay through Paystack.
- Confirm the payment on the server before saving an order.
- Store the order, line items, and payment together.
- Show an invoice for a completed order.

## Technologies

- PHP 8.2
- MySQL
- HTML, CSS, and JavaScript
- Paystack Inline
- PHPMailer

## Main features

- Product listing, search, and category and brand filters
- Product details and add to cart
- Cart quantity updates and item removal
- Paystack checkout
- Order invoice
- Admin pages for brands, categories, and products

## Project structure

```
actions/        request handlers
classes/        database queries
controllers/    between pages and classes
core/           shared setup
css/            styles
js/             client-side checks
views/          pages
images/         product photos
database/       schema
vendor/         PHPMailer
```

## Author

Peter Mangor Buol
