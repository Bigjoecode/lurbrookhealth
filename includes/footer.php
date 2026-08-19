  </main>
  <section class="service-strip"><div class="container service-grid"><div><i>✓</i><span><strong>Quality-led sourcing</strong><small>Products selected with care</small></span></div><div><i>▣</i><span><strong>Secure PayPal checkout</strong><small>Trusted payment protection</small></span></div><div><i>♧</i><span><strong>UK-wide delivery</strong><small>To organisations and homes</small></span></div><div><i>☎</i><span><strong>Friendly support</strong><small>Real help when you need it</small></span></div></div></section>
  <footer class="site-footer">
    <div class="container footer-grid">
      <div class="footer-about"><img src="<?= url('assets/img/logo.png') ?>" alt="Lurbrook Health"><p>Quality PPE, healthcare consumables and medical equipment for businesses, healthcare organisations and consumers across the UK.</p><a href="https://www.linkedin.com/company/lurbrook-health-ltd-734372389/" target="_blank" rel="noopener">LinkedIn ↗</a></div>
      <div><h3>Shop</h3><?php foreach($categories as $cat): ?><a href="<?= url('shop.php?category='.urlencode($cat['category'])) ?>"><?= e($cat['category']) ?></a><?php endforeach; ?></div>
      <div><h3>Information</h3><a href="<?= url('page/about') ?>">About us</a><a href="<?= url('contact.php') ?>">Contact us</a><a href="<?= url('page/shipping') ?>">Shipping</a><a href="<?= url('page/returns') ?>">Returns</a><a href="<?= url('page/privacy') ?>">Privacy</a><a href="<?= url('page/terms') ?>">Terms</a></div>
      <div><h3>Get in touch</h3><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a><a href="tel:+447961076672"><?= e(setting('phone')) ?></a><p>Supplying customers nationwide across the United Kingdom.</p><div class="payment-badge">Pay securely with <strong>PayPal</strong></div></div>
    </div>
    <div class="container footer-bottom"><span>© <?= date('Y') ?> Lurbrook Health LTD. All rights reserved.</span><a href="<?= url('admin/') ?>">Admin</a></div>
  </footer>
  <div id="toast" class="toast" role="status" aria-live="polite"></div>
  <script>window.LURBROOK={base:<?= json_encode(rtrim(url(), '/')) ?>,csrf:<?= json_encode(csrf_token()) ?>};</script>
  <script src="<?= url('assets/js/site.js') ?>"></script>
  <?php if (!empty($pageScripts)) echo $pageScripts; ?>
</body></html>

