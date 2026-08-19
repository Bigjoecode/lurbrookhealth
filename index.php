<?php
require __DIR__ . '/bootstrap.php';
$pageTitle = 'Quality healthcare supplies across the UK';
$activePage = 'home';
$featured = $db->query('SELECT * FROM products WHERE active=1 AND featured=1 ORDER BY id LIMIT 8')->fetchAll();
$categories = $db->query('SELECT category, COUNT(*) total FROM products WHERE active=1 GROUP BY category ORDER BY category')->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="hero"><div class="container hero-grid"><div class="hero-copy"><span class="eyebrow">Trusted UK healthcare supply</span><h1><?= e(setting('hero_title')) ?></h1><p><?= e(setting('hero_text')) ?></p><div class="button-row"><a class="btn btn-dark" href="<?= url('shop.php') ?>">Shop healthcare essentials <span>→</span></a><a class="btn btn-outline" href="<?= url('contact.php?subject=Business enquiry') ?>">Talk to our team</a></div></div><div class="hero-media"><img src="<?= url('assets/img/hero.png') ?>" alt="Lurbrook healthcare specialist"><div class="hero-note"><b>✓</b><span><strong>Quality-led selection</strong>For care settings and everyday use</span></div></div></div></section>
<section class="section"><div class="container"><div class="section-head"><div><span class="eyebrow">Shop by department</span><h2>Healthcare, made easier</h2><p>Focused collections of dependable essentials for healthcare organisations, businesses and homes.</p></div><a class="text-link" href="<?= url('shop.php') ?>">View all products →</a></div><div class="category-cards">
<?php $icons=['Thermometers'=>'♨','Blood Pressure Monitors'=>'♥','PPE & Masks'=>'✚']; foreach($categories as $category): ?><a class="category-card" href="<?= url('shop.php?category='.urlencode($category['category'])) ?>"><span class="category-icon"><?= $icons[$category['category']] ?? '✚' ?></span><div><h3><?= e($category['category']) ?></h3><small><?= (int)$category['total'] ?> carefully selected products →</small></div></a><?php endforeach; ?>
</div></div></section>
<section class="section section-soft"><div class="container"><div class="section-head"><div><span class="eyebrow">Featured products</span><h2>Everyday healthcare essentials</h2></div><a class="text-link" href="<?= url('shop.php') ?>">Shop the full range →</a></div><div class="product-grid"><?php foreach($featured as $product) require __DIR__ . '/includes/product-card.php'; ?></div></div></section>
<section class="section"><div class="container"><div class="trade-banner"><div class="trade-copy"><span class="eyebrow">Business & healthcare supply</span><h2>A dependable supply partner for your organisation</h2><p>From one-off requirements to repeat orders, we provide flexible sourcing support for practices, care teams, workplaces and distributors.</p><div class="button-row"><a class="btn" href="<?= url('contact.php?subject=Trade and wholesale enquiry') ?>">Open a conversation</a><a class="btn btn-outline" style="color:#fff;border-color:#6c8ba2" href="<?= url('page/about') ?>">Why Lurbrook</a></div></div><div class="trade-media"><img src="<?= url('assets/img/business.png') ?>" alt="Healthcare supply team working together" loading="lazy"></div></div></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>

