<?php
declare(strict_types=1);

return [
    'site_url' => rtrim((string) (getenv('APP_URL') ?: ''), '/'),
    'db_path' => __DIR__ . '/data/lurbrook.sqlite',
    'admin_email' => getenv('ADMIN_EMAIL') ?: 'admin@lurbrookhealthltd.com',
    'admin_password' => getenv('ADMIN_PASSWORD') ?: 'ChangeMe!2026',
    'paypal_client_id' => getenv('PAYPAL_CLIENT_ID') ?: '',
    'paypal_secret' => getenv('PAYPAL_SECRET') ?: '',
    'paypal_mode' => getenv('PAYPAL_MODE') ?: 'sandbox',
];

