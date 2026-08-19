<?php
declare(strict_types=1);

$productionFile = __DIR__ . '/data/production-config.php';
$production = is_file($productionFile) ? require $productionFile : [];
if (!is_array($production)) $production = [];

return [
    'site_url' => rtrim((string) (getenv('APP_URL') ?: ($production['site_url'] ?? '')), '/'),
    'db_path' => __DIR__ . '/data/lurbrook.sqlite',
    'admin_email' => getenv('ADMIN_EMAIL') ?: ($production['admin_email'] ?? 'admin@lurbrookhealthltd.com'),
    'admin_password' => getenv('ADMIN_PASSWORD') ?: ($production['admin_password'] ?? 'ChangeMe!2026'),
    'paypal_client_id' => getenv('PAYPAL_CLIENT_ID') ?: ($production['paypal_client_id'] ?? ''),
    'paypal_secret' => getenv('PAYPAL_SECRET') ?: ($production['paypal_secret'] ?? ''),
    'paypal_mode' => getenv('PAYPAL_MODE') ?: ($production['paypal_mode'] ?? 'sandbox'),
    'openai_api_key' => getenv('OPENAI_API_KEY') ?: ($production['openai_api_key'] ?? ''),
    'openai_model' => getenv('OPENAI_MODEL') ?: ($production['openai_model'] ?? 'gpt-5.4-nano'),
];
