<?php
// one product card. $p comes from the query in the page that includes this.
$card_id    = (int)($p['product_id'] ?? 0);
$card_title = (string)($p['product_title'] ?? '');
$card_meta  = [];
if (!empty($p['brand_name'])) {
    $card_meta[] = (string)$p['brand_name'];
}
if (!empty($p['cat_name'])) {
    $card_meta[] = (string)$p['cat_name'];
}
?>
<article class="product-card">
    <a class="product-card__media" href="<?php echo APP_BASE; ?>index.php?pro_id=<?php echo $card_id; ?>">
        <?php if (!empty($p['product_image'])): ?>
            <img src="<?php echo APP_BASE . 'images/products/' . htmlspecialchars($p['product_image'], ENT_QUOTES, 'UTF-8'); ?>"
                 alt="<?php echo htmlspecialchars($card_title, ENT_QUOTES, 'UTF-8'); ?>"
                 class="product-card__img">
        <?php else: ?>
            <div class="product-card__no-img">No Image</div>
        <?php endif; ?>
    </a>
    <div class="product-card__body">
        <?php if ($card_meta): ?>
            <p class="product-card__meta"><?php echo htmlspecialchars(implode(' · ', $card_meta), ENT_QUOTES, 'UTF-8'); ?></p>
        <?php else: ?>
            <p class="product-card__meta">&nbsp;</p>
        <?php endif; ?>
        <h3 class="product-card__title">
            <a href="<?php echo APP_BASE; ?>index.php?pro_id=<?php echo $card_id; ?>">
                <?php echo htmlspecialchars($card_title, ENT_QUOTES, 'UTF-8'); ?>
            </a>
        </h3>
        <p class="product-card__price">GH₵ <?php echo number_format((float)($p['product_price'] ?? 0), 2); ?></p>
        <div class="product-card__actions">
            <a href="<?php echo APP_BASE; ?>index.php?pro_id=<?php echo $card_id; ?>"
               class="btn btn-secondary">Product Details</a>
            <a href="<?php echo APP_BASE; ?>index.php?add_cart=<?php echo $card_id; ?>"
               class="btn btn-primary">Add to Cart</a>
        </div>
    </div>
</article>
