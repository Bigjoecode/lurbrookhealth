<?php
declare(strict_types=1);

function policy_content(string $filename, string $class, array $replacements = []): string
{
    $source = @file_get_contents(dirname(__DIR__) . '/' . $filename);
    if (!$source || !preg_match('/<div class="' . preg_quote($class, '/') . '">(.*?)<\/div>\s*<style>/s', $source, $match)) {
        return '<p>Please contact Lurbrook Health LTD if you require a copy of this policy.</p>';
    }
    $content = '<div class="policy-document">' . trim($match[1]) . '</div>';
    return str_replace(array_keys($replacements), array_values($replacements), $content);
}

function terms_policy_content(): string
{
    $content = policy_content('terms.html','lb-terms');
    return (string) preg_replace('/<p class="lb-p">We accept:<\/p>\s*<ul class="lb-list">.*?<\/ul>/s', '<p class="lb-p">We accept secure payment through:</p><ul class="lb-list"><li>PayPal and payment methods made available through PayPal</li></ul>', $content);
}

function run_migrations(PDO $db): void
{
    $db->exec('CREATE TABLE IF NOT EXISTS schema_migrations (version INTEGER PRIMARY KEY, applied_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)');
    $applied = array_map('intval', $db->query('SELECT version FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN));
    if (!in_array(2, $applied, true)) migrate_catalogue_and_content($db);
    if (!in_array(3, $applied, true)) migrate_payment_copy_and_gallery($db);
    if (!in_array(4, $applied, true)) migrate_homepage_headline($db);
}

function migrate_homepage_headline(PDO $db): void
{
    $stmt = $db->prepare('UPDATE settings SET setting_value=? WHERE setting_key=? AND setting_value=?');
    $stmt->execute(['Trusted Medical Supplies, Delivered with care','hero_title','Healthcare essentials. Delivered with care.']);
    $db->exec('INSERT INTO schema_migrations(version) VALUES(4)');
}

function migrate_catalogue_and_content(PDO $db): void
{
    $db->beginTransaction();
    try {
        $db->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS categories (
 id INTEGER PRIMARY KEY AUTOINCREMENT,
 name TEXT NOT NULL UNIQUE,
 slug TEXT NOT NULL UNIQUE,
 description TEXT NOT NULL DEFAULT '',
 active INTEGER NOT NULL DEFAULT 1,
 sort_order INTEGER NOT NULL DEFAULT 0,
 created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS reviews (
 id INTEGER PRIMARY KEY AUTOINCREMENT,
 customer_name TEXT NOT NULL,
 customer_type TEXT NOT NULL DEFAULT 'Verified customer',
 rating INTEGER NOT NULL DEFAULT 5,
 review TEXT NOT NULL,
 active INTEGER NOT NULL DEFAULT 1,
 sort_order INTEGER NOT NULL DEFAULT 0
);
SQL);
        $categoryInsert = $db->prepare('INSERT OR IGNORE INTO categories(name,slug,description,sort_order) VALUES(?,?,?,?)');
        $categories = [
            ['Thermometers','thermometers','Fast, convenient temperature monitoring for homes and care settings.',10],
            ['Blood Pressure Monitors','blood-pressure-monitors','Clear, dependable blood-pressure monitoring for everyday health checks.',20],
            ['PPE & Masks','ppe-and-masks','Protective medical essentials for professional and everyday use.',30],
            ['Healthcare Consumables','healthcare-consumables','Everyday consumable supplies for healthcare and workplace settings.',40],
            ['Medical Equipment','medical-equipment','Practical equipment selected for dependable healthcare support.',50]
        ];
        foreach ($categories as $category) $categoryInsert->execute($category);

        $additionalProducts = [
            ['Family Forehead Infrared Thermometer','family-forehead-infrared-thermometer','Thermometers','LHB-TH-004','A quick, easy-read forehead thermometer for family temperature checks.','Fast forehead reading|Colour fever indicator|Silent mode|Suitable for adults and children',27.99,32.99,44,'assets/products/family-forehead-thermometer.jpg','["assets/products/gallery/family-2.jpg","assets/products/gallery/family-3.jpg","assets/products/gallery/family-4.jpg"]',1,1],
            ['Compact Automatic Blood Pressure Monitor','compact-automatic-blood-pressure-monitor','Blood Pressure Monitors','LHB-BP-003','A compact automatic monitor with an integrated cuff and clear display.','One-touch operation|Integrated cuff|Large digital display|Compact home design',46.99,54.99,24,'assets/products/compact-blood-pressure-monitor.jpg','["assets/products/gallery/compact-bp-2.jpg","assets/products/gallery/compact-bp-3.jpg","assets/products/gallery/compact-bp-4.jpg"]',1,1],
            ['Digital Ear Thermometer','digital-ear-thermometer','Thermometers','LHB-TH-005','A comfortable in-ear thermometer with fast digital results and memory recall.','Quick ear measurement|Clear LCD display|Memory recall|Automatic power-off',22.99,27.99,37,'assets/products/digital-ear-thermometer.jpg','["assets/products/gallery/ear-2.jpg","assets/products/gallery/ear-3.jpg","assets/products/gallery/ear-4.jpg"]',0,1],
            ['Clinical Non-contact Thermometer','clinical-non-contact-thermometer','Thermometers','LHB-TH-006','A blue-and-white contact-free infrared thermometer for hygienic routine screening.','1–5 cm measuring distance|Contact-free operation|Body and object modes|Celsius and Fahrenheit',34.99,39.99,31,'assets/products/clinical-non-contact-thermometer.jpg','["assets/products/gallery/clinical-2.jpg","assets/products/gallery/clinical-3.jpg","assets/products/gallery/clinical-4.jpg"]',1,1],
            ['Individually Wrapped Medical Masks – 50 Pack','individually-wrapped-medical-masks-50','PPE & Masks','LHB-PPE-002','A convenient box of individually wrapped, three-layer medical face masks.','50 masks per box|Individually wrapped|Comfortable ear loops|Three-layer construction',14.99,18.99,86,'assets/products/individually-wrapped-medical-masks.jpg','["assets/products/gallery/masks-2.jpg","assets/products/gallery/masks-3.png"]',0,1]
        ];
        $productInsert = $db->prepare('INSERT OR IGNORE INTO products(name,slug,category,sku,description,details,price,compare_price,stock,image,gallery,featured,active) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)');
        foreach ($additionalProducts as $product) $productInsert->execute($product);

        $galleryUpdates = [
            'LHB-TH-001'=>'["assets/products/gallery/family-2.jpg","assets/products/gallery/family-3.jpg"]',
            'LHB-BP-001'=>'["assets/products/gallery/compact-bp-2.jpg","assets/products/gallery/compact-bp-3.jpg"]',
            'LHB-TH-002'=>'["assets/products/gallery/ear-2.jpg","assets/products/gallery/ear-3.jpg"]',
            'LHB-TH-003'=>'["assets/products/gallery/clinical-2.jpg","assets/products/gallery/clinical-3.jpg"]',
            'LHB-PPE-001'=>'["assets/products/gallery/masks-2.jpg","assets/products/gallery/masks-3.png"]'
        ];
        $galleryUpdate = $db->prepare("UPDATE products SET gallery=? WHERE sku=? AND (gallery='' OR gallery='[]')");
        foreach ($galleryUpdates as $sku=>$gallery) $galleryUpdate->execute([$gallery,$sku]);

        $reviews = [
            ['Sarah M.','Verified customer',5,'Straightforward ordering, clear product information and everything arrived carefully packed.',10],
            ['David R.','Business customer',5,'Helpful communication from the team and a simple process from enquiry through to delivery.',20],
            ['Amina K.','Verified customer',5,'The monitor was easy to order and simple to use. I appreciated the clear updates.',30],
            ['Care team buyer','Healthcare organisation',5,'A professional, responsive service for the healthcare essentials our team needed.',40]
        ];
        $reviewInsert = $db->prepare('INSERT INTO reviews(customer_name,customer_type,rating,review,sort_order) VALUES(?,?,?,?,?)');
        if ((int)$db->query('SELECT COUNT(*) FROM reviews')->fetchColumn() === 0) foreach ($reviews as $review) $reviewInsert->execute($review);

        $renamePages = ['privacy'=>'privacy-policy','terms'=>'terms-and-conditions','returns'=>'returns-refund-policy','shipping'=>'shipping-policy'];
        $rename = $db->prepare('UPDATE pages SET slug=? WHERE slug=?');
        foreach ($renamePages as $old=>$new) $rename->execute([$new,$old]);
        $pages = [
            ['privacy-policy','Privacy Policy','How Lurbrook Health collects, uses and protects your personal information.',policy_content('privacy.html','lb-privacy',['Stripe, PayPal or your card provider'=>'PayPal or its card processing partners'])],
            ['terms-and-conditions','Terms & Conditions','The terms that apply when you use our website or place an order.',terms_policy_content()],
            ['returns-refund-policy','Returns & Refund Policy','Your cancellation rights and our process for returns, damaged goods and refunds.',policy_content('returns.html','lb-returns')],
            ['shipping-policy','Shipping Policy','Processing, UK delivery and tracking information for your order.',policy_content('shipping.html','lb-shipping')],
            ['cookies-policy','Cookie Policy','How cookies support essential website functions and your browsing experience.',policy_content('cookie.html','lb-cookie')],
            ['media-disclaimer','Media Disclaimer','Important guidance about product use and healthcare information on this website.',policy_content('disclaimer.html','lb-disclaimer')],
            ['faq','Frequently Asked Questions','Helpful answers about products, ordering, payments, delivery and returns','<div class="faq-list"><details open><summary>What does Lurbrook Health supply?</summary><p>We supply quality PPE, healthcare consumables and medical equipment to businesses, healthcare organisations and consumers across the UK.</p></details><details><summary>Do you supply businesses and healthcare organisations?</summary><p>Yes. We support both one-off and repeat requirements. Contact our team for trade, wholesale or larger-quantity enquiries.</p></details><details><summary>How can I pay for my order?</summary><p>Online orders are paid securely through PayPal. Depending on eligibility and location, PayPal may also offer debit or credit card payment options.</p></details><details><summary>Where do you deliver?</summary><p>We currently deliver to addresses throughout the United Kingdom.</p></details><details><summary>How quickly will my order be processed?</summary><p>In-stock orders are usually processed within 1–2 working days. Courier delivery time is additional and may vary.</p></details><details><summary>Can I track my order?</summary><p>When tracking is available, the details will be provided after your order has been dispatched.</p></details><details><summary>Can medical products be returned?</summary><p>Eligible unused products may be returned in line with our Returns & Refund Policy. Sealed goods cannot be returned for hygiene reasons once their seal has been broken, unless faulty.</p></details><details><summary>How do I get help choosing a product?</summary><p>Contact our team and we will help with product information. Website information is general and is not a substitute for advice from a qualified healthcare professional.</p></details></div>']
        ];
        $pageUpsert = $db->prepare('INSERT INTO pages(slug,title,excerpt,content,active) VALUES(?,?,?,?,1) ON CONFLICT(slug) DO UPDATE SET title=excluded.title,excerpt=excluded.excerpt,content=excluded.content,updated_at=CURRENT_TIMESTAMP');
        foreach ($pages as $page) $pageUpsert->execute($page);
        $db->exec('INSERT INTO schema_migrations(version) VALUES(2)');
        $db->commit();
    } catch (Throwable $error) {
        $db->rollBack();
        throw $error;
    }
}

function migrate_payment_copy_and_gallery(PDO $db): void
{
    $db->beginTransaction();
    try {
        $db->prepare('UPDATE pages SET content=?,updated_at=CURRENT_TIMESTAMP WHERE slug=?')->execute([terms_policy_content(),'terms-and-conditions']);
        $db->prepare("UPDATE products SET gallery=? WHERE sku='LHB-BP-002'")->execute(['["assets/products/gallery/pro-bp-2.jpg","assets/products/gallery/pro-bp-3.jpg"]']);
        $db->exec('INSERT INTO schema_migrations(version) VALUES(3)');
        $db->commit();
    } catch (Throwable $error) {
        $db->rollBack();
        throw $error;
    }
}
