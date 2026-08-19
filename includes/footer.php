  </main>
  <section class="service-strip"><div class="container service-grid"><div><i>✓</i><span><strong>Quality-led sourcing</strong><small>Products selected with care</small></span></div><div><i>▣</i><span><strong>Secure PayPal checkout</strong><small>Trusted payment protection</small></span></div><div><i>♧</i><span><strong>UK-wide delivery</strong><small>To organisations and homes</small></span></div><div><i>☎</i><span><strong>Friendly support</strong><small>Real help when you need it</small></span></div></div></section>
  <footer class="site-footer">
    <div class="container footer-grid">
      <div class="footer-about"><img src="<?= url('assets/img/logo.png') ?>" alt="Lurbrook Health"><p>Quality PPE, healthcare consumables and medical equipment for businesses, healthcare organisations and consumers across the UK.</p><a href="https://www.linkedin.com/company/lurbrook-health-ltd-734372389/" target="_blank" rel="noopener">LinkedIn ↗</a></div>
      <div><h3>Shop</h3><?php foreach($categories as $cat): ?><a href="<?= url('shop?category='.urlencode($cat['category'])) ?>"><?= e($cat['category']) ?></a><?php endforeach; ?></div>
      <div><h3>Information</h3><a href="<?= url('about') ?>">About us</a><a href="<?= url('faq') ?>">Frequently asked questions</a><a href="<?= url('contact') ?>">Contact us</a><a href="<?= url('privacy-policy') ?>">Privacy</a><a href="<?= url('terms-and-conditions') ?>">Terms & Conditions</a><a href="<?= url('returns-refund-policy') ?>">Returns Policy</a><a href="<?= url('shipping-policy') ?>">Shipping Policy</a><a href="<?= url('cookies-policy') ?>">Cookie Policy</a><a href="<?= url('media-disclaimer') ?>">Media Disclaimer</a></div>
      <div><h3>Get in touch</h3><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a><a href="tel:+447961076672"><?= e(setting('phone')) ?></a><p>Supplying customers nationwide across the United Kingdom.</p><div class="payment-badge">Pay securely with <strong>PayPal</strong></div></div>
    </div>
    <div class="container footer-bottom"><span>© <?= date('Y') ?> Lurbrook Health LTD. All rights reserved.</span></div>
  </footer>
  <div id="toast" class="toast" role="status" aria-live="polite"></div>
  <div class="cart-prompt" id="cart-prompt" hidden><button class="cart-prompt-backdrop" type="button" data-close-cart-prompt aria-label="Continue shopping"></button><section class="cart-prompt-card" role="dialog" aria-modal="true" aria-labelledby="cart-prompt-title"><button class="cart-prompt-close" type="button" data-close-cart-prompt aria-label="Close">×</button><span class="cart-prompt-check">✓</span><small>Added to your bag</small><h2 id="cart-prompt-title">Item added successfully</h2><p id="cart-prompt-product"></p><div class="cart-prompt-actions"><a class="btn btn-dark" href="<?= url('cart') ?>">View cart</a><a class="btn" href="<?= url('checkout') ?>">Checkout now</a></div><button class="continue-link" type="button" data-close-cart-prompt>Continue shopping</button></section></div>
  <script>window.LURBROOK={base:<?= json_encode(rtrim(url(), '/')) ?>,csrf:<?= json_encode(csrf_token()) ?>};</script>
  <script src="<?= url('assets/js/site.js') ?>"></script>
  <?php if (!empty($pageScripts)) echo $pageScripts; ?>
</body></html>
