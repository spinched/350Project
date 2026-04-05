<?php
require 'db.php';
$activePage = 'stocking';
$errors     = [];
 
if (!hasAccess(['Manager','IT'])) {
    header('Location: stocking.php'); exit;
}
 

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
 
if (!$id) {
    header('Location: stocking.php'); exit;
}
 

$stmt = $conn->prepare(
    "SELECT p.*, l.StoreAisle, l.P_Type
     FROM PRODUCT p
     JOIN LOCATION l ON p.L_ID = l.L_ID
     WHERE p.P_ID = ?"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$prod = $stmt->get_result()->fetch_assoc();
 
if (!$prod) {
    $_SESSION['toast'] = 'Product not found.';
    header('Location: stocking.php'); exit;
}
 
$locations = getAllLocations($conn);
 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $cost = $_POST['cost']      ?? '';
    $sale = trim($_POST['sale'] ?? '');
    $wt   = $_POST['weight']    ?? '';
    $qty  = $_POST['qty']       ?? '';
    $desc = trim($_POST['desc'] ?? '');
    $mid  = (int)($_POST['manager'] ?? 0);
 
    // Validate
    if (!$name)                                           $errors['name']   = 'Product name is required.';
    elseif (strlen($name) > 50)                           $errors['name']   = 'Max 50 characters.';
    if (!$type)                                           $errors['type']   = 'Please select a product type.';
    if (!$cost || (float)$cost <= 0)                      $errors['cost']   = 'Enter a valid price > 0.';
    if ($sale !== '' && (float)$sale <= 0)                $errors['sale']   = 'Sale price must be > 0 if set.';
    if (!$wt || (float)$wt <= 0)                          $errors['weight'] = 'Enter a valid weight > 0.';
    if ($qty === '' || (int)$qty < 0 || (int)$qty > 9999) $errors['qty']    = 'Quantity must be 0–9999.';
    if (!$desc)                                           $errors['desc']   = 'Description is required.';
    if (!$mid)                                            $errors['manager']= 'Please assign a manager.';
 
    if (empty($errors)) {
        // Resolve location — find existing or create new
        $loc = null;
        foreach ($locations as $l) {
            if (strtolower($l['P_Type']) === strtolower($type)) { $loc = $l; break; }
        }
        if (!$loc) {
            $aisle = 99;
            $stmt = $conn->prepare("INSERT INTO LOCATION (StoreAisle, P_Type) VALUES (?, ?)");
            $stmt->bind_param('is', $aisle, $type);
            $stmt->execute();
            $lid = $conn->insert_id;
        } else {
            $lid = $loc['L_ID'];
        }
 
        $costF = round((float)$cost, 2);
        $wtF   = round((float)$wt, 2);
        $saleF = $sale !== '' ? round((float)$sale, 2) : null;
        $qtyI  = (int)$qty;
 
        $stmt = $conn->prepare(
            "UPDATE PRODUCT
             SET P_Name = ?, P_Description = ?, P_Cost = ?, P_SaleCost = ?,
                 P_Weight = ?, QuantityInStock = ?, L_ID = ?, M_ID = ?
             WHERE P_ID = ?"
        );
        $stmt->bind_param('ssdddiiii', $name, $desc, $costF, $saleF, $wtF, $qtyI, $lid, $mid, $id);
 
        try {
            $stmt->execute();
            $_SESSION['toast'] = "\"$name\" updated.";
            header('Location: stocking.php'); exit;
        } catch (mysqli_sql_exception $e) {
            $errors['general'] = 'Failed to update product. Please try again.';
        }
    }
 
    
    $prod['P_Name']          = $name;
    $prod['P_Type']          = $type;
    $prod['P_Cost']          = $cost;
    $prod['P_SaleCost']      = $sale !== '' ? $sale : null;
    $prod['P_Weight']        = $wt;
    $prod['QuantityInStock'] = $qty;
    $prod['P_Description']   = $desc;
    $prod['M_ID']            = $mid;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Product · GreenLeaf Market</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
 
<?php require 'nav.php'; ?>
 
<main class="page-container">
  <div class="breadcrumb" style="margin-bottom:20px;">
    <a href="stocking.php">← Back to Inventory</a>
  </div>
  <h1 class="page-title" style="margin-bottom:24px;">
    Edit Product — <?= htmlspecialchars($prod['P_Name']) ?>
  </h1>
 
  <?php if (isset($errors['general'])): ?>
    <p style="color:var(--tomato);background:var(--tomato-light);padding:10px 16px;border-radius:var(--radius);margin-bottom:16px;">
      <?= htmlspecialchars($errors['general']) ?>
    </p>
  <?php endif; ?>
 
  <form method="post" style="max-width:600px;">
    <input type="hidden" name="id" value="<?= $id ?>">
 
    <div class="form-grid">
 
      <div class="field-group">
        <label for="name">Product Name *</label>
        <input id="name" name="name" type="text" maxlength="50" placeholder="e.g. Organic Apples"
               value="<?= htmlspecialchars($prod['P_Name']) ?>"
               class="<?= isset($errors['name']) ? 'input-error' : '' ?>" />
        <?php if (isset($errors['name'])): ?><span class="field-error"><?= $errors['name'] ?></span><?php endif; ?>
      </div>
 
      <div class="field-group">
        <label for="type">Product Type *</label>
        <select id="type" name="type" class="<?= isset($errors['type']) ? 'input-error' : '' ?>">
          <option value="">Select type…</option>
          <?php foreach (['fruit','vegetables','dairy','poultry','beef','fish','canned goods','frozen goods','pasta','spices','beverages','bakery','snacks','baking','household','beauty','health'] as $t): ?>
            <option value="<?= $t ?>"<?= strtolower($prod['P_Type'] ?? '') === $t ? ' selected' : '' ?>><?= ucfirst($t) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['type'])): ?><span class="field-error"><?= $errors['type'] ?></span><?php endif; ?>
      </div>
 
      <div class="field-group">
        <label for="cost">Price ($) *</label>
        <input id="cost" name="cost" type="number" min="0.01" step="0.01" placeholder="0.00"
               value="<?= htmlspecialchars($prod['P_Cost']) ?>"
               class="<?= isset($errors['cost']) ? 'input-error' : '' ?>" />
        <?php if (isset($errors['cost'])): ?><span class="field-error"><?= $errors['cost'] ?></span><?php endif; ?>
      </div>
 
      <div class="field-group">
        <label for="sale">Sale Price ($) <span class="optional">(optional — leave blank to remove sale)</span></label>
        <input id="sale" name="sale" type="number" min="0.01" step="0.01" placeholder="0.00"
               value="<?= htmlspecialchars($prod['P_SaleCost'] ?? '') ?>"
               class="<?= isset($errors['sale']) ? 'input-error' : '' ?>" />
        <?php if (isset($errors['sale'])): ?><span class="field-error"><?= $errors['sale'] ?></span><?php endif; ?>
      </div>
 
      <div class="field-group">
        <label for="weight">Weight (lbs) *</label>
        <input id="weight" name="weight" type="number" min="0.01" step="0.01" placeholder="0.00"
               value="<?= htmlspecialchars($prod['P_Weight']) ?>"
               class="<?= isset($errors['weight']) ? 'input-error' : '' ?>" />
        <?php if (isset($errors['weight'])): ?><span class="field-error"><?= $errors['weight'] ?></span><?php endif; ?>
      </div>
 
      <div class="field-group">
        <label for="qty">Quantity in Stock *</label>
        <input id="qty" name="qty" type="number" min="0" max="9999" step="1" placeholder="0"
               value="<?= htmlspecialchars($prod['QuantityInStock']) ?>"
               class="<?= isset($errors['qty']) ? 'input-error' : '' ?>" />
        <?php if (isset($errors['qty'])): ?><span class="field-error"><?= $errors['qty'] ?></span><?php endif; ?>
      </div>
 
      <div class="field-group field-group-full">
        <label for="desc">Description *</label>
        <textarea id="desc" name="desc" maxlength="300" rows="3" placeholder="Up to 300 characters…"
                  class="<?= isset($errors['desc']) ? 'input-error' : '' ?>"><?= htmlspecialchars($prod['P_Description']) ?></textarea>
        <?php if (isset($errors['desc'])): ?><span class="field-error"><?= $errors['desc'] ?></span><?php endif; ?>
      </div>
 
      <div class="field-group field-group-full">
        <label for="manager">Responsible Manager *</label>
        <select id="manager" name="manager" class="<?= isset($errors['manager']) ? 'input-error' : '' ?>">
          <option value="">Select manager…</option>
          <?php foreach (getAllManagers($conn) as $m): ?>
            <option value="<?= $m['M_ID'] ?>"<?= ($prod['M_ID'] ?? '') == $m['M_ID'] ? ' selected' : '' ?>>
              <?= htmlspecialchars($m['M_FirstName'] . ' ' . $m['M_LastName']) ?> (<?= $m['M_ID'] ?>)
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['manager'])): ?><span class="field-error"><?= $errors['manager'] ?></span><?php endif; ?>
      </div>
 
    </div>
 
    <div style="display:flex; gap:10px; margin-top:24px;">
      <button type="submit" name="submit" value="1" class="btn btn-primary">Save Changes</button>
      <a href="stocking.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</main>
 
<footer class="site-footer">
  <p>© 2026 GreenLeaf Market · Fresh · Local · Trusted</p>
</footer>
 
</body>
</html>
