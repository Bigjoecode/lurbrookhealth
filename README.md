# Lurbrook Health e-commerce website

A responsive PHP 8.2 storefront and administration dashboard for Lurbrook Health LTD. The application uses SQLite, so no separate database server or migration command is required.

## Features

- Branded medical e-commerce storefront based on the supplied Medist template structure
- Product catalogue, category filters, search, product details and responsive shopping bag
- Server-created and server-captured PayPal checkout in GBP
- Stock reduction and order recording after verified PayPal payment
- Admin dashboard for products, categories, images, inventory, orders, customer reviews, enquiries, pages and site settings
- Editable delivery thresholds, homepage copy and PayPal sandbox/live configuration
- Local contact enquiry inbox, CSRF protection, secure sessions and validated uploads
- Clean public URLs, multi-image product galleries, FAQ and full policy pages

## Local setup

1. Serve this folder with Apache/PHP 8.2. The project is already located under the XAMPP `htdocs` directory.
2. Open `http://localhost/lurbrook/`. The SQLite database is created and seeded automatically on first request.
3. Open `http://localhost/lurbrook/admin/` to configure the shop.

Default development administrator:

- Email: `admin@lurbrookhealthltd.com`
- Password: `ChangeMe!2026`

Set the `ADMIN_EMAIL` and `ADMIN_PASSWORD` environment variables in production. Do not deploy with the development password.

## Lurbrook Health Assistant

The storefront includes a product-aware shopping assistant with product recommendations, add-to-bag actions, checkout guidance, policy answers and an enquiry form. It works with a built-in catalogue and policy fallback. For conversational AI responses, set `OPENAI_API_KEY` on the server or add the key under **Admin → Settings**. The default model is `gpt-5.4-nano` and can be changed with `OPENAI_MODEL` or in the same settings screen. API credentials are used only by the PHP backend and are never exposed to storefront JavaScript.

## PayPal

In **Admin → Settings**, add the client ID and secret from a PayPal Business REST application. Keep the environment set to **Sandbox** while testing, then use live credentials and switch to **Live payments** for production.

The PayPal secret is stored in the local SQLite database and is never rendered back into the settings form. For infrastructure-managed deployments, `PAYPAL_CLIENT_ID`, `PAYPAL_SECRET`, and `PAYPAL_MODE` environment variables may be used for initial configuration.

## Writable directories

The PHP/Apache user needs write access to:

- `data/` for the SQLite database and sessions
- `uploads/` for administrator product images

Both runtime locations are excluded from Git.

## Automatic production deployment

The `Deploy production via FTPS` GitHub Actions workflow uploads each push to `main` over explicit FTPS on port 21. It is gated by the `FTPS_DEPLOY_ENABLED` repository variable, so no deployment runs until production credentials are configured.

Create these GitHub Actions repository secrets:

- `FTPS_PASSWORD`
- `ADMIN_EMAIL`
- `ADMIN_PASSWORD` — at least 16 characters and different from the development password

Create these repository variables:

- `FTPS_REMOTE_DIR` — `/` for a dedicated FTP account rooted at the addon domain, or `/lurbrookhealthltd.com` for the main cPanel FTP account
- `FTPS_DEPLOY_ENABLED` — set to `true` only after the secrets and remote directory are confirmed

The workflow verifies the FTPS certificate, uploads without remote deletion, preserves the live SQLite database, sessions and product uploads, writes the production administrator credentials to the server-protected `data/production-config.php`, and checks the live HTTPS homepage after deployment.

The configured deployment account is `lurbrook@lurbrookhealthltd.com` on `ftp.globalaffairs.uk:21` and should remain restricted to the addon-domain document root.
