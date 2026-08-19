<?php $section=$section??'dashboard';$adminFlash=pull_flash(); ?>
<!doctype html>
<html lang="en-GB">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($adminTitle??'Dashboard') ?> | Lurbrook Admin</title>
  <link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>">
  <link rel="stylesheet" href="<?= url('assets/css/admin-overrides.css') ?>">
</head>
<body>
<div class="admin-shell">
  <aside class="admin-sidebar">
    <div class="admin-logo"><a class="admin-logo-link" href="<?= url('admin/') ?>"><img src="<?= url('assets/img/logo.png') ?>" alt="Lurbrook Health"></a><button class="sidebar-close" type="button" aria-label="Close admin navigation">X</button></div>
    <nav>
      <small>Workspace</small>
      <a class="<?= $section==='dashboard'?'active':'' ?>" href="<?= url('admin/') ?>"><i>◫</i>Overview</a>
      <a class="<?= $section==='products'?'active':'' ?>" href="<?= url('admin/?view=products') ?>"><i>▦</i>Products</a>
      <a class="<?= $section==='categories'?'active':'' ?>" href="<?= url('admin/?view=categories') ?>"><i>◈</i>Categories</a>
      <a class="<?= $section==='orders'?'active':'' ?>" href="<?= url('admin/?view=orders') ?>"><i>▤</i>Orders</a>
      <a class="<?= $section==='messages'?'active':'' ?>" href="<?= url('admin/?view=messages') ?>"><i>✉</i>Enquiries</a>
      <small>Website</small>
      <a class="<?= $section==='reviews'?'active':'' ?>" href="<?= url('admin/?view=reviews') ?>"><i>★</i>Reviews</a>
      <a class="<?= $section==='pages'?'active':'' ?>" href="<?= url('admin/?view=pages') ?>"><i>▧</i>Pages</a>
      <a class="<?= $section==='settings'?'active':'' ?>" href="<?= url('admin/?view=settings') ?>"><i>⚙</i>Settings</a>
    </nav>
    <div class="sidebar-footer"><a href="<?= url() ?>" target="_blank">View storefront ↗</a><a href="<?= url('admin/logout') ?>">Sign out</a></div>
  </aside>
  <div class="admin-main">
    <header class="admin-top"><button class="sidebar-toggle" type="button" aria-label="Open admin navigation">☰</button><div><span><?= date('l, j F Y') ?></span></div><div class="admin-user"><span>LH</span><div><strong>Administrator</strong><small><?= e($_SESSION['admin']['email']) ?></small></div></div></header>
    <main class="admin-content">
      <?php if($adminFlash): ?><div class="admin-alert <?= e($adminFlash[0]) ?>"><?= e($adminFlash[1]) ?></div><?php endif; ?>
