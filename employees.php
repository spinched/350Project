<?php
require 'db.php';
$activePage = 'employees';
$toast      = $_SESSION['toast'] ?? null;
unset($_SESSION['toast']);

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && hasAccess(['IT'])) {
    if (($_POST['action'] ?? '') === 'delete') {
        $id   = (int)$_POST['eid'];
        $role = $_POST['erole'] ?? '';
        if ($role === 'IT') {
            $_SESSION['db']['it'] = array_values(array_filter($_SESSION['db']['it'], fn($e) => $e['IT_ID'] !== $id));
        } elseif ($role === 'Manager') {
            $_SESSION['db']['managers'] = array_values(array_filter($_SESSION['db']['managers'], fn($e) => $e['M_ID'] !== $id));
        } else {
            $_SESSION['db']['stockers'] = array_values(array_filter($_SESSION['db']['stockers'], fn($e) => $e['S_ID'] !== $id));
        }
        $_SESSION['toast'] = 'Employee removed.';
        header('Location: employees.php'); exit;
    }
}

$employees = getAllEmployees();
$roleBadge = ['IT'=>'badge-it','Manager'=>'badge-manager','Stocker'=>'badge-stocker'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Employees · GreenLeaf Market</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>

<?php require 'nav.php'; ?>

<?php if (!hasAccess(['IT'])): ?>
<main class="page-container">
  <p class="empty-msg">Access denied. This page is for IT only.</p>
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
      <h1 class="page-title">All Employees</h1>
      <p class="page-sub">Viewing all IT, Manager, and Stocker accounts.</p>
    </div>
    <a href="add_employee.php" class="btn btn-primary">+ Add Employee</a>
  </div>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Full Name</th>
          <th>Employee ID</th>
          <th>Birthdate</th>
          <th>Role</th>
          <th>Manager ID</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($employees)): ?>
          <tr><td colspan="6" class="empty-msg">No employees found.</td></tr>
        <?php else: ?>
          <?php foreach ($employees as $e): ?>
          <tr>
            <td><strong><?= htmlspecialchars($e['firstName'] . ' ' . $e['lastName']) ?></strong></td>
            <td class="mono"><?= $e['id'] ?></td>
            <td><?= fmtDate($e['birthDate']) ?></td>
            <td><span class="badge <?= $roleBadge[$e['role']] ?? '' ?>"><?= $e['role'] ?></span></td>
            <td class="mono"><?= $e['managerID'] ?? '—' ?></td>
            <td>
              <form method="post" style="margin:0" onsubmit="return confirm('Remove this employee? This cannot be undone.')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="eid"   value="<?= $e['id'] ?>">
                <input type="hidden" name="erole" value="<?= $e['role'] ?>">
                <button type="submit" class="btn-icon btn-danger" title="Delete">🗑</button>
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
