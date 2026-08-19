<article class="product-card">
  <a class="product-image" href="<?= url('product/'.e($product['slug'])) ?>">
    <?php if (!empty($product['compare_price']) && $product['compare_price'] > $product['price']): ?><span class="sale-label">SAVE <?= (int)round((1-$product['price']/$product['compare_price'])*100) ?>%</span><?php endif; ?>
    <img src="<?= url(e($product['image'])) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
  </a>
  <div class="product-info"><span class="product-category"><?= e($product['category']) ?></span><h3><a href="<?= url('product/'.e($product['slug'])) ?>"><?= e($product['name']) ?></a></h3><div class="price"><?= money((float)$product['price']) ?><?php if (!empty($product['compare_price']) && $product['compare_price'] > $product['price']): ?><del><?= money((float)$product['compare_price']) ?></del><?php endif; ?></div><div class="product-actions"><button class="btn" data-add-cart="<?= (int)$product['id'] ?>">Add to bag</button><a class="btn btn-outline" href="<?= url('product/'.e($product['slug'])) ?>" aria-label="View <?= e($product['name']) ?>">→</a></div></div>
</article>

