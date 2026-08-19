# Lurbrook Health e-commerce website

A responsive PHP 8.2 storefront and administration dashboard for Lurbrook Health LTD. The application uses SQLite, so no separate database server or migration command is required.

## Features

- Branded medical e-commerce storefront based on the supplied Medist template structure
- Product catalogue, category filters, search, product details and responsive shopping bag
- Server-created and server-captured PayPal checkout in GBP
- Stock reduction and order recording after verified PayPal payment
- Admin dashboard for products, images, inventory, orders, enquiries, pages and site settings
- Editable delivery thresholds, homepage copy and PayPal sandbox/live configuration
- Local contact enquiry inbox, CSRF protection, secure sessions and validated uploads

## Local setup

1. Serve this folder with Apache/PHP 8.2. The project is already located under the XAMPP `htdocs` directory.
2. Open `http://localhost/lurbrook/`. The SQLite database is created and seeded automatically on first request.
3. Open `http://localhost/lurbrook/admin/` to configure the shop.

Default development administrator:

- Email: `admin@lurbrookhealthltd.com`
- Password: `ChangeMe!2026`

Set the `ADMIN_EMAIL` and `ADMIN_PASSWORD` environment variables in production. Do not deploy with the development password.

## PayPal

In **Admin → Settings**, add the client ID and secret from a PayPal Business REST application. Keep the environment set to **Sandbox** while testing, then use live credentials and switch to **Live payments** for production.

The PayPal secret is stored in the local SQLite database and is never rendered back into the settings form. For infrastructure-managed deployments, `PAYPAL_CLIENT_ID`, `PAYPAL_SECRET`, and `PAYPAL_MODE` environment variables may be used for initial configuration.

## Writable directories

The PHP/Apache user needs write access to:

- `data/` for the SQLite database and sessions
- `uploads/` for administrator product images

Both runtime locations are excluded from Git.
