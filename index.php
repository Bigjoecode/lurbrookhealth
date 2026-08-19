<?php
require __DIR__ . '/bootstrap.php';
$pageTitle = 'Quality healthcare supplies across the UK';
$activePage = 'home';
$featured = $db->query('SELECT * FROM products WHERE active=1 AND featured=1 ORDER BY id LIMIT 8')->fetchAll();
$reviews = $db->query('SELECT * FROM reviews WHERE active=1 ORDER BY sort_order,id LIMIT 4')->fetchAll();
$reviewAverage = $reviews ? array_sum(array_column($reviews,'rating')) / count($reviews) : 0;
require __DIR__ . '/includes/header.php';
?>
<section class="hero hero-slider" data-hero-slider aria-roledescription="carousel" aria-label="Lurbrook Health highlights">
  <div class="hero-track" aria-live="off">
    <article class="hero-slide active" data-hero-slide aria-hidden="false">
      <div class="container hero-grid"><div class="hero-copy"><span class="eyebrow">Trusted UK healthcare supply</span><h1><?= e(setting('hero_title')) ?></h1><p><?= e(setting('hero_text')) ?></p><div class="button-row"><a class="btn btn-dark" href="<?= url('shop') ?>">Shop healthcare essentials <span>→</span></a><a class="btn btn-outline" href="<?= url('contact?subject=Business enquiry') ?>">Talk to our team</a></div></div><div class="hero-media"><img src="<?= url('assets/img/hero.png') ?>" alt="Lurbrook healthcare specialist"><div class="hero-note"><b>✓</b><span><strong>Quality-led selection</strong>For care settings and everyday use</span></div></div></div>
    </article>
    <article class="hero-slide hero-slide-business" data-hero-slide aria-hidden="true">
      <div class="container hero-grid"><div class="hero-copy"><span class="eyebrow">Business &amp; healthcare supply</span><h1>Dependable supply for your organisation</h1><p>Flexible healthcare sourcing for practices, care providers, workplaces and distributors across the UK.</p><div class="button-row"><a class="btn btn-dark" href="<?= url('contact?subject=Trade and wholesale enquiry') ?>">Start a trade enquiry <span>→</span></a><a class="btn btn-outline" href="<?= url('about') ?>">Why Lurbrook</a></div></div><div class="hero-media"><img src="<?= url('assets/img/business.png') ?>" alt="Healthcare supply professionals working together"><div class="hero-note"><b>✓</b><span><strong>Responsive support</strong>From one-off needs to repeat orders</span></div></div></div>
    </article>
    <article class="hero-slide hero-slide-products" data-hero-slide aria-hidden="true">
      <div class="container hero-grid"><div class="hero-copy"><span class="eyebrow">Carefully selected essentials</span><h1>Quality-led products for everyday care</h1><p>Explore PPE, thermometers, blood pressure monitors and dependable medical equipment for professional and home use.</p><div class="button-row"><a class="btn btn-dark" href="<?= url('shop') ?>">Explore all products <span>→</span></a><a class="btn btn-outline" href="<?= url('faq') ?>">Read our FAQs</a></div></div><div class="hero-media"><img src="<?= url('assets/img/about.png') ?>" alt="Healthcare professional selecting quality medical supplies"><div class="hero-note"><b>✓</b><span><strong>Practical healthcare</strong>Clear choices, delivered with care</span></div></div></div>
    </article>
  </div>
  <div class="container hero-controls">
    <div class="hero-dots" role="tablist" aria-label="Choose a hero slide">
      <button class="active" type="button" data-hero-dot role="tab" aria-selected="true" aria-label="Show slide 1"></button>
      <button type="button" data-hero-dot role="tab" aria-selected="false" aria-label="Show slide 2"></button>
      <button type="button" data-hero-dot role="tab" aria-selected="false" aria-label="Show slide 3"></button>
    </div>
    <div class="hero-arrows"><button type="button" data-hero-prev aria-label="Previous slide">←</button><button type="button" data-hero-next aria-label="Next slide">→</button></div>
  </div>
  <div class="hero-progress" aria-hidden="true"><span></span></div>
</section>
<section class="section"><div class="container"><div class="section-head"><div><span class="eyebrow">Shop by department</span><h2>Healthcare, made easier</h2><p>Focused collections of dependable essentials for healthcare organisations, businesses and homes.</p></div><a class="text-link" href="<?= url('shop') ?>">View all products →</a></div><div class="category-cards">
<?php $icons=['Thermometers'=>'♨','Blood Pressure Monitors'=>'♥','PPE & Masks'=>'✚','Healthcare Consumables'=>'◎','Medical Equipment'=>'◇']; foreach($categories as $category): ?><a class="category-card" href="<?= url('shop?category='.urlencode($category['category'])) ?>"><span class="category-icon"><?= $icons[$category['category']] ?? '✚' ?></span><div><h3><?= e($category['category']) ?></h3><small><?= (int)$category['total'] ?> carefully selected product<?= (int)$category['total']===1?'':'s' ?> →</small></div></a><?php endforeach; ?>
</div></div></section>
<section class="section section-soft"><div class="container"><div class="section-head"><div><span class="eyebrow">Featured products</span><h2>Everyday healthcare essentials</h2></div><a class="text-link" href="<?= url('shop') ?>">Shop the full range →</a></div><div class="product-grid"><?php foreach($featured as $product) require __DIR__ . '/includes/product-card.php'; ?></div></div></section>
<?php if($reviews): ?><section class="section review-section"><div class="container"><div class="review-heading"><div><span class="eyebrow">Customer reviews</span><h2>Trusted for care that matters</h2><p>Thoughtful service, clear communication and quality-led healthcare supply.</p></div><div class="review-score"><strong><?= number_format($reviewAverage,1) ?></strong><span>★★★★★</span><small>Customer feedback</small></div></div><div class="review-grid"><?php foreach($reviews as $review): ?><article class="review-card"><div class="stars" aria-label="<?= (int)$review['rating'] ?> out of 5 stars"><?= str_repeat('★',(int)$review['rating']) ?></div><blockquote>“<?= e($review['review']) ?>”</blockquote><footer><span><?= strtoupper(substr($review['customer_name'],0,1)) ?></span><div><strong><?= e($review['customer_name']) ?></strong><small><?= e($review['customer_type']) ?></small></div><b>✓</b></footer></article><?php endforeach; ?></div></div></section><?php endif; ?>
<section class="section"><div class="container"><div class="trade-banner"><div class="trade-copy"><span class="eyebrow">Business & healthcare supply</span><h2>A dependable supply partner for your organisation</h2><p>From one-off requirements to repeat orders, we provide flexible sourcing support for practices, care teams, workplaces and distributors.</p><div class="button-row"><a class="btn" href="<?= url('contact?subject=Trade and wholesale enquiry') ?>">Open a conversation</a><a class="btn btn-outline" style="color:#fff;border-color:#6c8ba2" href="<?= url('about') ?>">Why Lurbrook</a></div></div><div class="trade-media"><img src="<?= url('assets/img/business.png') ?>" alt="Healthcare supply team working together" loading="lazy"></div></div></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
