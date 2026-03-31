<?php
require 'db.php';
$activePage = 'stockers';
$toast      = $_SESSION['toast'] ?? null;
unset($_SESSION['toast']);

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && hasAccess(['Manager','IT'])) {
    if (($_POST['action'] ?? '') === 'delete') {
        $sid = (int)$_POST['sid'];
        $_SESSION['db']['stockers'] = array_values(
            array_filter($_SESSION['db']['stockers'], fn($s) => $s['S_ID'] !== $sid)
        );
        $_SESSION['toast'] = 'Stocker removed.';
        header('Location: stockers.php'); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Stockers · GreenLeaf Market</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>

<?php require 'nav.php'; ?>

<?php if (!hasAccess(['Manager','IT'])): ?>
<main class="page-container">
  <p class="empty-msg">Access denied. This page is for Managers and IT only.</p>
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
      <h1 class="page-title">Manage Stockers</h1>
      <p class="page-sub">View and manage stocker accounts.</p>
    </div>
    <a href="add_stocker.php" class="btn btn-primary">+ Add Stocker</a>
  </div>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Full Name</th>
          <th>Stocker ID</th>
          <th>Birthdate</th>
          <th>Manager ID</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($_SESSION['db']['stockers'])): ?>
          <tr><td colspan="5" class="empty-msg">No stockers found.</td></tr>
        <?php else: ?>
          <?php foreach ($_SESSION['db']['stockers'] as $s): ?>
          <tr>
            <td><strong><?= htmlspecialchars($s['S_FirstName'] . ' ' . $s['S_LastName']) ?></strong></td>
            <td class="mono"><?= $s['S_ID'] ?></td>
            <td><?= fmtDate($s['S_BirthDate']) ?></td>
            <td class="mono"><?= $s['M_ID'] ?></td>
            <td>
              <form method="post" style="margin:0" onsubmit="return confirm('Remove this stocker? This cannot be undone.')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="sid" value="<?= $s['S_ID'] ?>">
                <button type="submit" class="btn-icon btn-danger" title="Remove">🗑</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
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
