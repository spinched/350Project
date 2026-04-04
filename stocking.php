<?php
require 'db.php';
$activePage = 'stocking';
$toast      = $_SESSION['toast'] ?? null;
unset($_SESSION['toast']);

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && hasAccess(['Manager','IT'])) {
    if (($_POST['action'] ?? '') === 'delete') {
        $pid = (int)$_POST['pid'];
        $stmt = $conn->prepare("DELETE FROM PRODUCT WHERE P_ID = ?");
        $stmt->bind_param('i', $pid);
        try {
          $stmt->execute();
          $_SESSION['toast'] = 'Product removed.';
        } catch (mysqli_sql_exception $e) {
          $_SESSION['toast'] = 'An unexpected error occurred.';
        }
        header('Location: stocking.php'); exit;
    }

    // Handle inline quantity update
    if (($_POST['action'] ?? '') === 'update_qty') {
        $pid = (int)$_POST['pid'];
        $qty = (int)$_POST['qty'];
        if ($qty >= 0 && $qty <= 9999) {
            $stmt = $conn->prepare("UPDATE PRODUCT SET QuantityInStock = ? WHERE P_ID = ?");
            $stmt->bind_param('ii', $qty, $pid);
            try {
                $stmt->execute();
                $_SESSION['toast'] = 'Quantity updated.';
            } catch (mysqli_sql_exception $e) {
                $_SESSION['toast'] = 'Failed to update quantity.';
            }
        } else {
            $_SESSION['toast'] = 'Invalid quantity — must be 0–9999.';
        }
        header('Location: stocking.php'); exit;
    }
}

$products  = getAllProducts($conn);
$canEdit   = hasAccess(['Manager','IT']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Inventory · GreenLeaf Market</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>

<?php require 'nav.php'; ?>

<?php if (!hasAccess(['Stocker','Manager','IT'])): ?>
<main class="page-container">
  <p class="empty-msg">Access denied. This page is for Stockers, Managers, and IT only.</p>
</main>
<?php else: ?>

<main class="page-container">

  <?php if ($toast): ?>
    <p style="color:var(--forest);background:var(--forest-light);padding:10px 16px;border-radius:var(--radius);margin-bottom:16px;">
      <?= htmlspecialchars($toast) ?>
    </p>
  <?php endif; ?>

  <div class="page-header">
    <div>
      <h1 class="page-title">Product Stocking</h1>
      <p class="page-sub">Manage product inventory and pricing.</p>
    </div>
    <?php if ($canEdit): ?>
      <a href="add_product.php" class="btn btn-primary">+ Add Product</a>
    <?php endif; ?>
  </div>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Product Name</th>
          <th>Stock</th>
          <th>Price</th>
          <th>Sale Price</th>
          <th>Cost/oz</th>
          <th>Type</th>
          <th>Aisle</th>
          <th>Description</th>
          <?php if ($canEdit): ?><th></th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p):
          $low  = $p['QuantityInStock'] < 20;
          $cpoz = number_format($p['P_Cost'] / ($p['P_Weight'] * 16), 4);
        ?>
        <tr>
          <td>
            <a href="product.php?id=<?= $p['P_ID'] ?>" class="table-product-link">
              <span class="table-emoji"><?= getCategoryEmoji($p['P_Type']) ?></span>
              <?= htmlspecialchars($p['P_Name']) ?>
            </a>
          </td>
          <td>
            <form method="post" style="display:flex;gap:6px;align-items:center;">
              <input type="hidden" name="action" value="update_qty">
              <input type="hidden" name="pid" value="<?= $p['P_ID'] ?>">
              <input type="number" name="qty" value="<?= $p['QuantityInStock'] ?>"
                     min="0" max="9999"
                     style="width:70px;padding:4px 6px;border:1px solid var(--border);border-radius:4px;font-family:var(--font-body);<?= $low ? 'color:var(--tomato);' : '' ?>"
                     title="<?= $low ? 'Low stock' : '' ?>" />
              <button type="submit" class="btn-icon" title="Save">💾</button>
            </form>
          </td>
          <td><?= fmtPrice($p['P_Cost']) ?></td>
          <td><?= fmtPrice($p['P_SaleCost']) ?></td>
          <td>$<?= $cpoz ?></td>
          <td><?= htmlspecialchars($p['P_Type']) ?></td>
          <td><?= fmtAisle($p['StoreAisle']) ?></td>
          <td class="desc-cell"><?= htmlspecialchars($p['P_Description']) ?></td>
          <?php if ($canEdit): ?>
          <td>
            <div style="display:flex; gap:6px;">
              <a href="edit_product.php?id=<?= $p['P_ID'] ?>" class="btn-icon btn-edit" title="Edit">✏️</a>
              <form method="post" style="margin:0" onsubmit="return confirm('Delete this product? This cannot be undone.')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="pid" value="<?= $p['P_ID'] ?>">
                <button type="submit" class="btn-icon btn-danger" title="Delete">🗑</button>
              </form>
            </div>
          </td>
          <?php endif; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>

<?php endif; ?>

<footer class="site-footer">
  <p>© 2026 GreenLeaf Market · Fresh · Local · Trusted</p>
</footer>

</body>
</html>
