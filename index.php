<?php
require 'db.php';
$activePage = 'store';

$q    = strtolower(trim($_GET['q'] ?? ''));
$type = strtolower(trim($_GET['type'] ?? ''));

$products = array_filter(getAllProducts($conn), function($p) use ($q, $type) {
    $matchQ    = !$q    || str_contains(strtolower($p['P_Name']), $q)
                        || str_contains(strtolower($p['P_Type']), $q)
                        || str_contains(strtolower($p['P_Description']), $q);
    $matchType = !$type || strtolower($p['P_Type']) === $type;
    return $matchQ && $matchType;
  });

$sales = getAllProductsOnSale($conn);



?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>GreenLeaf Market</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>

<?php require 'nav.php'; ?>

<div style="max-width:1200px; margin:0 auto; padding:24px;">
  <h1 class="page-title" style="margin-bottom:16px;">🌿 GreenLeaf Market</h1>

  <form method="get" style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:24px;">
    <input name="q" type="text" placeholder="Search products…"
           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
           style="padding:9px 12px; border:1px solid var(--border); border-radius:var(--radius); font-family:var(--font-body); font-size:0.9rem; flex:1; min-width:200px;" />
    <select name="type" style="padding:9px 12px; border:1px solid var(--border); border-radius:var(--radius); font-family:var(--font-body); font-size:0.9rem;">
      <option value="">All Categories</option>
      <?php foreach (['vegetables','dairy','poultry','beef','canned goods','frozen goods','fruit','fish','pasta','spices','beverages','bakery','snacks','baking','household','beauty','health'] as $t):?>
        <option value="<?= $t ?>"<?= $type === $t ? ' selected' : '' ?>><?= ucfirst($t) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary">Search</button>
    <?php if ($q || $type): ?>
      <a href="index.php" class="btn btn-ghost">Clear</a>
    <?php endif; ?>
  </form>
</div>

<main style="max-width:1200px; margin:0 auto; padding:0 24px 60px;">
  <?php if (empty($q) && empty($type) && !empty($sales)): ?>
  <section class="section">
    <h2 class="section-title"><span class="section-title-icon">🏷️</span> Sales This Week</h2>
    <div class="product-grid">
      <?php foreach ($sales as $p):?>
        <a href="product.php?id=<?= $p['P_ID'] ?>" class="product-card">
          <div class="card-image <?= getCategoryClass($p['P_Type']) ?>">
            <span class="card-emoji"><?= getCategoryEmoji($p['P_Type']) ?></span>
            <span class="sale-badge">SALE</span>
          </div>
          <div class="card-body">
            <h3 class="card-name"><?= htmlspecialchars($p['P_Name']) ?></h3>
            <p class="card-type"><?= htmlspecialchars($p['P_Type']) ?> · Aisle <?= fmtAisle($p['StoreAisle']) ?></p>
            <div class="card-pricing">
              <span class="price-sale"><?= fmtPrice($p['P_SaleCost']) ?></span>
              <span class="price-original"><?= fmtPrice($p['P_Cost']) ?></span>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <section class="section">
    <h2 class="section-title"><span class="section-title-icon">🛒</span> All Products</h2>
    <?php if (empty($products)): ?>
      <p class="empty-msg">No products match your search.</p>
    <?php else: ?>
      <div class="product-grid">
        <?php foreach ($products as $p):?>
          <a href="product.php?id=<?= $p['P_ID'] ?>" class="product-card">
            <div class="card-image <?= getCategoryClass($p['P_Type']) ?>">
              <span class="card-emoji"><?= getCategoryEmoji($p['P_Type']) ?></span>
              <?php if ($p['P_SaleCost'] !== null): ?>
                <span class="sale-badge">SALE</span>
              <?php endif; ?>
            </div>
            <div class="card-body">
              <h3 class="card-name"><?= htmlspecialchars($p['P_Name']) ?></h3>
              <p class="card-type"><?= htmlspecialchars($p['P_Type']) ?> · Aisle <?= fmtAisle($p['StoreAisle']) ?></p>
              <div class="card-pricing">
                <?php if ($p['P_SaleCost'] !== null): ?>
                  <span class="price-sale"><?= fmtPrice($p['P_SaleCost']) ?></span>
                  <span class="price-original"><?= fmtPrice($p['P_Cost']) ?></span>
                <?php else: ?>
                  <span class="price-regular"><?= fmtPrice($p['P_Cost']) ?></span>
                <?php endif; ?>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

</main>

<footer class="site-footer">
  <p>© 2026 GreenLeaf Market · Fresh · Local · Trusted</p>
</footer>

</body>
</html>