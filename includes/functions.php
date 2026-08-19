<?php
declare(strict_types=1);

function e(?string $value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function money(float $value): string { return '£' . number_format($value, 2); }
function url(string $path = ''): string {
    global $config;
    $base = $config['site_url'] ?: rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/admin');
    if (str_ends_with($base, '/admin')) $base = substr($base, 0, -6);
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}
function setting(string $key, string $default = ''): string {
    global $db;
    $stmt = $db->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();
    return $value === false ? $default : (string) $value;
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24));
    return $_SESSION['csrf'];
}
function verify_csrf(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(419); exit('Your session expired. Please go back and try again.');
    }
}
function redirect(string $path): never { header('Location: ' . url($path)); exit; }
function flash(string $type, string $message): void { $_SESSION['flash'] = [$type, $message]; }
function pull_flash(): ?array { $v = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $v; }
function is_admin(): bool { return !empty($_SESSION['admin']); }
function require_admin(): void { if (!is_admin()) redirect('admin/login'); }
function slugify(string $value): string {
    $value = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $value), '-'));
    return $value ?: 'item-' . time();
}
function cart_count(): int { return array_sum(array_map('intval', $_SESSION['cart'] ?? [])); }
function products_by_ids(array $ids): array {
    global $db;
    $ids = array_values(array_filter(array_map('intval', $ids)));
    if (!$ids) return [];
    $stmt = $db->prepare('SELECT * FROM products WHERE active = 1 AND id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')');
    $stmt->execute($ids);
    $rows = [];
    foreach ($stmt->fetchAll() as $row) $rows[(int) $row['id']] = $row;
    return $rows;
}
function upload_image(string $field, string $existing = ''): string {
    if (empty($_FILES[$field]['tmp_name']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return $existing;
    if ($_FILES[$field]['size'] > 6 * 1024 * 1024) throw new RuntimeException('Image must be under 6 MB.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES[$field]['tmp_name']);
    $ext = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/avif'=>'avif'][$mime] ?? null;
    if (!$ext) throw new RuntimeException('Please upload a JPG, PNG, WebP, or AVIF image.');
    $name = bin2hex(random_bytes(10)) . '.' . $ext;
    $target = __DIR__ . '/../uploads/' . $name;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $target)) throw new RuntimeException('Could not save uploaded image.');
    return 'uploads/' . $name;
}
function seed_database(PDO $db, array $config): void {
    $settings = [
        'site_name'=>'Lurbrook Health LTD', 'email'=>'contact@lurbrookhealthltd.com',
        'phone'=>'+44 7961 076672', 'announcement'=>'Free UK delivery on orders over £75',
        'paypal_client_id'=>$config['paypal_client_id'], 'paypal_secret'=>$config['paypal_secret'], 'paypal_mode'=>$config['paypal_mode'],
        'assistant_enabled'=>'1', 'openai_api_key'=>$config['openai_api_key'], 'openai_model'=>$config['openai_model'],
        'shipping_flat'=>'4.95', 'shipping_free_over'=>'75',
        'hero_title'=>'Trusted Medical Supplies, Delivered with care',
        'hero_text'=>'Quality PPE, healthcare consumables and medical equipment for organisations, businesses and homes across the UK.'
    ];
    $stmt = $db->prepare('INSERT OR IGNORE INTO settings(setting_key, setting_value) VALUES(?, ?)');
    foreach ($settings as $key=>$value) $stmt->execute([$key, $value]);

    if ((int) $db->query('SELECT COUNT(*) FROM products')->fetchColumn() === 0) {
        $products = [
            ['Infrared Ear Thermometer','infrared-ear-thermometer','Thermometers','LHB-TH-001','Fast, gentle temperature readings for adults and children.','One-second reading|Memory recall|Fever alert|Easy-read display',24.99,29.99,42,'assets/products/infrared-ear-thermometer.jpg',1],
            ['Automatic Blood Pressure Monitor','automatic-blood-pressure-monitor','Blood Pressure Monitors','LHB-BP-001','Reliable upper-arm monitoring with a large, clear digital display.','Automatic operation|Large LCD display|Pulse detection|Memory function',39.99,49.99,28,'assets/products/blood-pressure-monitor.jpg',1],
            ['Professional Blood Pressure Monitor','professional-blood-pressure-monitor','Blood Pressure Monitors','LHB-BP-002','Advanced home blood-pressure tracking with a comfortable adjustable cuff.','Dual-user memory|Irregular heartbeat indicator|WHO classification|Storage case',54.99,64.99,16,'assets/products/blood-pressure-monitor-pro.jpg',1],
            ['Medical Infrared Thermometer','medical-infrared-thermometer','Thermometers','LHB-TH-002','Clinical-style infrared measurement designed for accurate everyday checks.','Non-invasive|Quick measurement|Celsius and Fahrenheit|Auto shut-off',32.99,39.99,35,'assets/products/medical-infrared-thermometer.jpg',1],
            ['Non-contact Infrared Thermometer','non-contact-infrared-thermometer','Thermometers','LHB-TH-003','Hygienic, contact-free temperature measurement in seconds.','Contact-free|Backlit display|Fever alarm|Memory mode',29.99,34.99,51,'assets/products/non-contact-thermometer.jpg',1],
            ['Type IIR Medical Face Masks','type-iir-medical-face-masks','PPE & Masks','LHB-PPE-001','Fluid-resistant Type IIR medical masks for professional and everyday use.','Type IIR protection|Fluid resistant|Comfortable ear loops|Box of 50',12.99,16.99,120,'assets/products/type-iir-masks.png',1]
        ];
        $insert = $db->prepare('INSERT INTO products(name,slug,category,sku,description,details,price,compare_price,stock,image,featured) VALUES(?,?,?,?,?,?,?,?,?,?,?)');
        foreach ($products as $product) $insert->execute($product);
    }
    if ((int) $db->query('SELECT COUNT(*) FROM pages')->fetchColumn() === 0) {
        $pages = [
            ['about','About Lurbrook Health','Healthcare supplies you can rely on','<h2>Healthcare supplies you can rely on</h2><p>Lurbrook Health LTD is a healthcare supply company providing quality PPE, healthcare consumables and medical equipment to businesses, healthcare organisations and consumers across the UK.</p><p>We work with carefully selected manufacturers and suppliers to source reliable products that meet applicable quality and regulatory standards, while making essential healthcare supplies easier to access.</p><h2>Our mission</h2><p>To make quality healthcare products simpler, more reliable and accessible to those who need them.</p><h2>Our vision</h2><p>To become a trusted global healthcare brand, delivering quality products and solutions that improve access to essential healthcare supplies.</p>'],
            ['shipping','Shipping Policy','How we deliver your order','<h2>UK delivery</h2><p>We deliver throughout the United Kingdom. Delivery times and charges are shown at checkout. Orders may be sent in more than one parcel.</p><h2>Dispatch</h2><p>We aim to dispatch in-stock items promptly. You will receive confirmation when your order has been processed.</p>'],
            ['returns','Returns & Refunds','Our straightforward returns process','<h2>Returns</h2><p>If you need to return an eligible item, contact us within 14 days of delivery. Products must be unused, unopened and in their original packaging.</p><p>For hygiene and safety reasons, opened PPE and medical consumables cannot normally be returned unless faulty.</p>'],
            ['privacy','Privacy Policy','How we use and protect your data','<h2>Your privacy</h2><p>We collect only the information required to process orders, respond to enquiries and improve our service. We do not sell personal information.</p><p>Payment details are processed securely by PayPal and are not stored by Lurbrook Health.</p>'],
            ['terms','Terms & Conditions','Terms for using this website','<h2>Website terms</h2><p>These terms govern purchases from Lurbrook Health LTD. Product availability, descriptions and pricing may change. An order is accepted once payment is confirmed and we issue an order confirmation.</p>']
        ];
        $insert = $db->prepare('INSERT INTO pages(slug,title,excerpt,content) VALUES(?,?,?,?)');
        foreach ($pages as $page) $insert->execute($page);
    }
}
