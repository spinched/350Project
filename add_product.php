<?php
require 'db.php';
$activePage = 'stocking';
$errors     = [];

if (!hasAccess(['Manager','IT'])) {
    header('Location: stocking.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $cost = $_POST['cost'] ?? '';
    $sale = trim($_POST['sale'] ?? '');
    $wt   = $_POST['weight'] ?? '';
    $qty  = $_POST['qty'] ?? '';
    $desc = trim($_POST['desc'] ?? '');

    if (!$name)                             $errors['name']   = 'Product name is required.';
    elseif (strlen($name) > 50)             $errors['name']   = 'Max 50 characters.';
    if (!$type)                             $errors['type']   = 'Please select a product type.';
    if (!$cost || (float)$cost <= 0)        $errors['cost']   = 'Enter a valid price > 0.';
    if ($sale !== '' && (float)$sale <= 0)  $errors['sale']   = 'Sale price must be > 0 if set.';
    if (!$wt || (float)$wt <= 0)           $errors['weight'] = 'Enter a valid weight > 0.';
    if ($qty === '' || (int)$qty < 0 || (int)$qty > 9999) $errors['qty'] = 'Quantity must be 0–9999.';
    if (!$desc)                             $errors['desc']   = 'Description is required.';

    if (empty($errors)) {
        $loc = null;
        foreach ($_SESSION['db']['locations'] as $l) {
            if (strtolower($l['P_Type']) === strtolower($type)) { $loc = $l; break; }
        }
        if (!$loc) {
            $newLID = 400000 + count($_SESSION['db']['locations']);
            $loc = ['L_ID'=>$newLID,'StoreAisle'=>'99','P_Type'=>$type];
            $_SESSION['db']['locations'][] = $loc;
        }
        $costF = round((float)$cost, 2);
        $wtF   = round((float)$wt, 2);
        $_SESSION['db']['products'][] = [
            'P_ID'            => $_SESSION['db']['_nextProduct']++,
            'P_Name'          => $name,
            'P_Description'   => $desc,
            'P_Cost'          => $costF,
            'P_SaleCost'      => $sale !== '' ? round((float)$sale, 2) : null,
            'P_Weight'        => $wtF,
            'P_CostPerOunce'  => round($costF / ($wtF * 16), 4),
            'QuantityInStock' => (int)$qty,
            'M_ID'            => $_SESSION['db']['managers'][0]['M_ID'],
            'L_ID'            => $loc['L_ID'],
        ];
        $_SESSION['toast'] = "\"$name\" added to inventory.";
        header('Location: stocking.php'); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add Product · GreenLeaf Market</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>

<?php require 'nav.php'; ?>

<main class="page-container">
  <div class="breadcrumb" style="margin-bottom:20px;">
    <a href="stocking.php">← Back to Inventory</a>
  </div>
  <h1 class="page-title" style="margin-bottom:24px;">Add New Product</h1>

  <form method="post" style="max-width:600px;">
    <div class="form-grid">

      <div class="field-group">
        <label for="name">Product Name *</label>
        <input id="name" name="name" type="text" maxlength="50" placeholder="e.g. Organic Apples"
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
               class="<?= isset($errors['name']) ? 'input-error' : '' ?>" />
        <?php if (isset($errors['name'])): ?><span class="field-error"><?= $errors['name'] ?></span><?php endif; ?>
      </div>

      <div class="field-group">
        <label for="type">Product Type *</label>
        <select id="type" name="type" class="<?= isset($errors['type']) ? 'input-error' : '' ?>">
          <option value="">Select type…</option>
          <?php foreach (['vegetables','dairy','poultry','beef','canned goods','frozen goods'] as $t): ?>
            <option value="<?= $t ?>"<?= ($_POST['type'] ?? '') === $t ? ' selected' : '' ?>><?= ucfirst($t) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['type'])): ?><span class="field-error"><?= $errors['type'] ?></span><?php endif; ?>
      </div>

      <div class="field-group">
        <label for="cost">Price ($) *</label>
        <input id="cost" name="cost" type="number" min="0.01" step="0.01" placeholder="0.00"
               value="<?= htmlspecialchars($_POST['cost'] ?? '') ?>"
               class="<?= isset($errors['cost']) ? 'input-error' : '' ?>" />
        <?php if (isset($errors['cost'])): ?><span class="field-error"><?= $errors['cost'] ?></span><?php endif; ?>
      </div>

      <div class="field-group">
        <label for="sale">Sale Price ($) <span class="optional">(optional)</span></label>
        <input id="sale" name="sale" type="number" min="0.01" step="0.01" placeholder="0.00"
               value="<?= htmlspecialchars($_POST['sale'] ?? '') ?>"
               class="<?= isset($errors['sale']) ? 'input-error' : '' ?>" />
        <?php if (isset($errors['sale'])): ?><span class="field-error"><?= $errors['sale'] ?></span><?php endif; ?>
      </div>

      <div class="field-group">
        <label for="weight">Weight (lbs) *</label>
        <input id="weight" name="weight" type="number" min="0.01" step="0.01" placeholder="0.00"
               value="<?= htmlspecialchars($_POST['weight'] ?? '') ?>"
               class="<?= isset($errors['weight']) ? 'input-error' : '' ?>" />
        <?php if (isset($errors['weight'])): ?><span class="field-error"><?= $errors['weight'] ?></span><?php endif; ?>
      </div>

      <div class="field-group">
        <label for="qty">Quantity in Stock *</label>
        <input id="qty" name="qty" type="number" min="0" max="9999" step="1" placeholder="0"
               value="<?= htmlspecialchars($_POST['qty'] ?? '') ?>"
               class="<?= isset($errors['qty']) ? 'input-error' : '' ?>" />
        <?php if (isset($errors['qty'])): ?><span class="field-error"><?= $errors['qty'] ?></span><?php endif; ?>
      </div>

      <div class="field-group field-group-full">
        <label for="desc">Description *</label>
        <textarea id="desc" name="desc" maxlength="300" rows="3" placeholder="Up to 300 characters…"
                  class="<?= isset($errors['desc']) ? 'input-error' : '' ?>"><?= htmlspecialchars($_POST['desc'] ?? '') ?></textarea>
        <?php if (isset($errors['desc'])): ?><span class="field-error"><?= $errors['desc'] ?></span><?php endif; ?>
      </div>

    </div>

    <div style="display:flex; gap:10px; margin-top:24px;">
      <button type="submit" class="btn btn-primary">Add Product</button>
      <a href="stocking.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</main>

<footer class="site-footer">
  <p>© 2026 GreenLeaf Market · Fresh · Local · Trusted</p>
</footer>

</body>
</html>
