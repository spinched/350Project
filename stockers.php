<?php
require 'db.php';
$activePage = 'stockers';
$toast      = $_SESSION['toast'] ?? null;
unset($_SESSION['toast']);

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && hasAccess(['Manager','IT'])) {
    if (($_POST['action'] ?? '') === 'delete') {
        $sid = (int)$_POST['sid'];
        $stmt = $conn->prepare("DELETE FROM STOCKER WHERE S_ID = ?");
        $stmt->bind_param('i', $sid);
        try {
          $stmt->execute();
          $_SESSION['toast'] = 'Stocker removed.';
        } catch (mysqli_sql_exception $e) {
          if ($e->getCode() === 1451) {
              $_SESSION['toast'] = 'Cannot delete this employee — they still have staff or products assigned to them. Contact IT for assistance with this issue.';
          } else {
              $_SESSION['toast'] = 'An unexpected error occurred.';
          }
        }
        header('Location: stockers.php'); exit;
    }
}

$stockers = getAllStockers($conn);

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
        <?php if (empty($stockers)): ?>
          <tr><td colspan="5" class="empty-msg">No stockers found.</td></tr>
        <?php else: ?>
          <?php foreach ($stockers as $s): ?>
          <tr>
            <td><strong><?= htmlspecialchars($s['S_FirstName'] . ' ' . $s['S_LastName']) ?></strong></td>
            <td class="mono"><?= $s['S_ID'] ?></td>
            <td><?= fmtDate($s['S_BirthDate']) ?></td>
            <td class="mono"><?= $s['M_ID'] ?></td>
            <td>
              <div style="display:flex; gap:6px;">
                <a href="edit_stocker.php?id=<?= $s['S_ID'] ?>&role=<?= $s['Role'] ?>" 
                    class="btn-icon btn-edit" title="Edit">✏️</a>
                <form method="post" style="margin:0" onsubmit="return confirm('Remove this stocker? This cannot be undone.')">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="sid" value="<?= $s['S_ID'] ?>">
                  <button type="submit" class="btn-icon btn-danger" title="Remove">🗑</button>
                </form>
              </div>
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
