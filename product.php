<?php
require 'db.php';
$activePage = 'store';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$p  = $id ? getProduct($conn, $id) : null;
if ($p) {
    $onSale   = $p['P_SaleCost'] !== null;
    $lowStock = $p['QuantityInStock'] < 20;
    $cpoz     = $p['P_Cost'] / ($p['P_Weight'] * 16);
    $saleCpoz = $onSale ? ($p['P_SaleCost'] / ($p['P_Weight'] * 16)) : null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $p ? htmlspecialchars($p['P_Name']) . ' · ' : '' ?>GreenLeaf Market</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>

<?php require 'nav.php'; ?>

<main class="page-container">
  <div class="breadcrumb">
    <a href="index.php">← Back to Store</a>
  </div>

  <?php if (!$p): ?>
    <p class="empty-msg">Product not found. <a href="index.php">Browse all products →</a></p>
  <?php else: ?>
    <div class="product-detail-wrap">
      <div class="detail-card">

        <div class="detail-image-col">
          <div class="detail-image <?= getCategoryClass($p['P_Type']) ?>">
            <span class="detail-emoji"><?= getCategoryEmoji($p['P_Type']) ?></span>
            <?php if ($onSale): ?>
              <span class="sale-badge sale-badge-lg">ON SALE</span>
            <?php endif; ?>
          </div>
        </div>

        <div class="detail-info-col">
          <p class="detail-category"><?= htmlspecialchars($p['P_Type']) ?></p>
          <h1 class="detail-name"><?= htmlspecialchars($p['P_Name']) ?></h1>

          <div class="detail-pricing">
            <?php if ($onSale): ?>
              <div class="detail-price-sale">
                <span class="price-sale-large"><?= fmtPrice($p['P_SaleCost']) ?></span>
                <span class="price-was">was <?= fmtPrice($p['P_Cost']) ?></span>
              </div>
              <p class="detail-cpo">Sale cost/oz: <strong><?= fmtPrice($saleCpoz) ?></strong></p>
            <?php else: ?>
              <span class="price-regular-large"><?= fmtPrice($p['P_Cost']) ?></span>
            <?php endif; ?>
            <p class="detail-cpo">Cost per oz: <strong><?= fmtPrice($cpoz) ?></strong></p>
          </div>

          <div class="detail-meta">
            <div class="meta-row">
              <span class="meta-label">Product Type</span>
              <span class="meta-value"><?= htmlspecialchars($p['P_Type']) ?></span>
            </div>
            <div class="meta-row">
              <span class="meta-label">Aisle</span>
              <span class="meta-value"><?= fmtAisle($p['StoreAisle']) ?></span>
            </div>
            <div class="meta-row">
              <span class="meta-label">Weight</span>
              <span class="meta-value"><?= number_format($p['P_Weight'], 2) ?> lbs</span>
            </div>
            <div class="meta-row">
              <span class="meta-label">In Stock</span>
              <span class="meta-value <?= $lowStock ? 'qty-low' : 'qty-ok' ?>">
                <?= $lowStock ? '⚠️ ' : '' ?><?= $p['QuantityInStock'] ?> units
              </span>
            </div>
          </div>

          <div class="detail-description">
            <h3>About this product</h3>
            <p><?= htmlspecialchars($p['P_Description']) ?></p>
          </div>
        </div>

      </div>
    </div>
  <?php endif; ?>
</main>

<footer class="site-footer">
  <p>© 2026 GreenLeaf Market · Fresh · Local · Trusted</p>
</footer>

</body>
</html>
