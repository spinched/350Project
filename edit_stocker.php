<?php
require 'db.php';
$activePage = 'stockers';
$errors     = [];

if (!hasAccess(['Manager','IT'])) {
    header('Location: stockers.php'); exit;
}

$id = (int)($_GET['id'] ?? 0);
$stocker = getStocker($conn, $id);
if (!$stocker){
    header('Location: stockers.php'); exit; 
}

$selectedManager = isset($_POST['manager']) ? (int)$_POST['manager'] : $stocker['M_ID'];
$selectedIT      = isset($_POST['it'])      ? (int)$_POST['it']      : $stocker['IT_ID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['first'] ?? '');
    $last  = trim($_POST['last']  ?? '');
    $dob   = trim($_POST['dob']   ?? '');
    $pw    =      $_POST['pw']    ?? '';
    $mid   = (int)($_POST['manager'] ?? 0);
    $itID  = (int)($_POST['it']      ?? 0);

    if ($first && $e = validateName($first, 'First name')) $errors['first'] = $e;
    if ($last  && $e = validateName($last,  'Last name'))  $errors['last']  = $e;
    if ($dob   && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) $errors['dob'] = 'Invalid date format.';
    if ($pw    && $e = validatePassword($pw))               $errors['pw']   = $e;

    if (empty($errors)) {
        $sets = [];
        $types = '';
        $params = [];

        if ($first)                    { $sets[] = 'S_FirstName = ?'; $types .= 's'; $params[] = $first; }
        if ($last)                     { $sets[] = 'S_LastName = ?';  $types .= 's'; $params[] = $last; }
        if ($dob)                      { $sets[] = 'S_BirthDate = ?'; $types .= 's'; $params[] = $dob; }
        if ($pw)                       { $sets[] = 'S_Password = ?';  $types .= 's'; $params[] = password_hash($pw, PASSWORD_DEFAULT); }
        if ($mid  && $mid  != $stocker['M_ID'])  { $sets[] = 'M_ID = ?';  $types .= 'i'; $params[] = $mid; }
        if ($itID && $itID != $stocker['IT_ID']) { $sets[] = 'IT_ID = ?'; $types .= 'i'; $params[] = $itID; }

        if ($sets) {
            $types .= 'i';
            $params[] = $id;
            $sql = 'UPDATE STOCKER SET ' . implode(', ', $sets) . ' WHERE S_ID = ?';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            try {
                $name = trim(($first ?: $stocker['S_FirstName']) . ' ' . ($last ?: $stocker['S_LastName']));
                $stmt->execute();
                $_SESSION['toast'] = "$name updated.";
                header('Location: stockers.php'); exit;
            } catch (mysqli_sql_exception $e) {
                $_SESSION['toast'] = 'Update failed. Please try again.';
            }
        } else {
            $_SESSION['toast'] = 'No changes submitted.';
            header('Location: stockers.php'); exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Stocker · GreenLeaf Market</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>

<?php require 'nav.php'; ?>

<main class="page-container">
  <div class="breadcrumb" style="margin-bottom:20px;">
    <a href="stockers.php">← Back to Stockers</a>
  </div>
  <h1 class="page-title" style="margin-bottom:24px;">Update Stocker</h1>

  <form method="post" style="max-width:600px;">
    <div class="form-grid">

      <div class="field-group">
        <label for="first">First Name *</label>
        <input id="first" name="first" type="text" placeholder="Letters only"
                value="<?= htmlspecialchars($_POST['first'] ?? $stocker['S_FirstName']) ?>"
                class="<?= isset($errors['first']) ? 'input-error' : '' ?>" />
        <?php if (isset($errors['first'])): ?><span class="field-error"><?= $errors['first'] ?></span><?php endif; ?>
      </div>

      <div class="field-group">
        <label for="last">Last Name *</label>
        <input id="last" name="last" type="text" placeholder="Letters only"
                value="<?= htmlspecialchars($_POST['last'] ?? $stocker['S_LastName']) ?>"
                class="<?= isset($errors['last']) ? 'input-error' : '' ?>" />
        <?php if (isset($errors['last'])): ?><span class="field-error"><?= $errors['last'] ?></span><?php endif; ?>
      </div>

      <div class="field-group">
        <label for="dob">Date of Birth *</label>
        <input id="dob" name="dob" type="date"
                value="<?= htmlspecialchars($_POST['dob'] ?? $stocker['S_BirthDate']) ?>"
                class="<?= isset($errors['dob']) ? 'input-error' : '' ?>" />
        <?php if (isset($errors['dob'])): ?><span class="field-error"><?= $errors['dob'] ?></span><?php endif; ?>
      </div>

      <div class="field-group">
        <label for="pw">Password *</label>
        <input id="pw" name="pw" type="password" placeholder="Min 12 characters"
                class="<?= isset($errors['pw']) ? 'input-error' : '' ?>" />
        <?php if (isset($errors['pw'])): ?><span class="field-error"><?= $errors['pw'] ?></span><?php endif; ?>
      </div>

      <div class="field-group field-group-full">
        <label for="manager">Assigned Manager *</label>
        <select id="manager" name="manager" class="<?= isset($errors['manager']) ? 'input-error' : '' ?>">
          <option value="">Select manager…</option>
          <?php 
            foreach (getAllManagers($conn) as $m):
          ?>
            <option value="<?= $m['M_ID'] ?>"<?= $selectedManager == $m['M_ID'] ? ' selected' : '' ?>>
              <?= htmlspecialchars($m['M_FirstName'] . ' ' . $m['M_LastName']) ?> (<?= $m['M_ID'] ?>)
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['manager'])): ?><span class="field-error"><?= $errors['manager'] ?></span><?php endif; ?>
      </div>

      <div class="field-group field-group-full">
        <label for="it">Assigned IT Staff *</label>
        <select id="it" name="it" class="<?= isset($errors['it']) ? 'input-error' : '' ?>">
          <option value="">Select IT staff…</option>
          <?php foreach (getAllIT($conn) as $i): ?>
            <option value="<?= $i['IT_ID'] ?>"<?= $selectedIT == $i['IT_ID'] ? ' selected' : '' ?>>
              <?= htmlspecialchars($i['IT_FirstName'] . ' ' . $i['IT_LastName']) ?> (<?= $i['IT_ID'] ?>)
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['it'])): ?><span class="field-error"><?= $errors['it'] ?></span><?php endif; ?>
      </div>


    </div>

    <div style="display:flex; gap:10px; margin-top:24px;">
      <button type="submit" class="btn btn-primary">Edit Stocker</button>
      <a href="stockers.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</main>

<footer class="site-footer">
  <p>© 2026 GreenLeaf Market · Fresh · Local · Trusted</p>
</footer>

</body>
</html>
