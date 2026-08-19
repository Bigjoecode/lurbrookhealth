<?php
$pageTitle = $pageTitle ?? 'Quality healthcare supplies';
$metaDescription = $metaDescription ?? 'Quality PPE, healthcare consumables and medical equipment delivered across the UK.';
$activePage = $activePage ?? '';
$categories = $db->query("SELECT category, COUNT(*) AS total FROM products WHERE active=1 GROUP BY category ORDER BY category")->fetchAll();
$flash = pull_flash();
?>
<!doctype html>
<html lang="en-GB">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> | Lurbrook Health</title>
  <meta name="description" content="<?= e($metaDescription) ?>">
  <link rel="icon" href="<?= url('assets/img/logo.png') ?>">
  <link rel="stylesheet" href="<?= url('assets/css/site.css') ?>">
  <link rel="stylesheet" href="<?= url('assets/css/mobile-fixes.css') ?>">
</head>
<body>
  <a class="skip-link" href="#main">Skip to content</a>
  <div class="announcement"><div class="container"><span>UK healthcare supply partner</span><strong><?= e(setting('announcement')) ?></strong><a href="<?= url('contact.php') ?>">Business enquiries →</a></div></div>
  <header class="site-header">
    <div class="container header-main">
      <a class="brand" href="<?= url() ?>" aria-label="Lurbrook Health home"><img src="<?= url('assets/img/logo.png') ?>" alt="Lurbrook Health LTD"></a>
      <form class="search" action="<?= url('shop.php') ?>" method="get"><label class="sr-only" for="q">Search products</label><input id="q" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Search thermometers, masks, monitors…"><button type="submit" aria-label="Search">⌕</button></form>
      <div class="header-actions"><a href="tel:+447961076672"><small>Need help?</small><strong><?= e(setting('phone')) ?></strong></a><a class="cart-link" href="<?= url('cart.php') ?>"><span>Bag</span><b data-cart-count><?= cart_count() ?></b></a></div>
      <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="main-nav">Menu</button>
    </div>
    <nav id="main-nav" class="main-nav" aria-label="Primary navigation"><div class="container nav-inner">
      <div class="category-menu"><button type="button">Shop categories <span>⌄</span></button><div class="category-dropdown"><?php foreach($categories as $cat): ?><a href="<?= url('shop.php?category=' . urlencode($cat['category'])) ?>"><span><?= e($cat['category']) ?></span><small><?= (int)$cat['total'] ?> products</small></a><?php endforeach; ?></div></div>
      <a class="<?= $activePage==='home'?'active':'' ?>" href="<?= url() ?>">Home</a>
      <a class="<?= $activePage==='shop'?'active':'' ?>" href="<?= url('shop.php') ?>">Shop</a>
      <a class="<?= $activePage==='about'?'active':'' ?>" href="<?= url('page/about') ?>">About us</a>
      <a class="<?= $activePage==='contact'?'active':'' ?>" href="<?= url('contact.php') ?>">Contact</a>
      <a class="trade-link" href="<?= url('contact.php?subject=Trade account') ?>">Trade & wholesale</a>
    </div></nav>
  </header>
  <?php if ($flash): ?><div class="flash <?= e($flash[0]) ?>" role="status"><?= e($flash[1]) ?></div><?php endif; ?>
  <main id="main">
