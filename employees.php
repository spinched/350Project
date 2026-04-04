<?php
require 'db.php';
$activePage = 'employees';
$toast      = $_SESSION['toast'] ?? null;
unset($_SESSION['toast']);

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && hasAccess(['IT'])) {
    if (($_POST['action'] ?? '') === 'delete') {
        $id   = (int)$_POST['eid'];
        $role = strtoupper($_POST['erole'] ?? '');
        $col = match($role){
          'IT'      => 'IT_ID',
          'MANAGER' => 'M_ID',
          'STOCKER' => 'S_ID',
           default   => null
        };
        if ($col) {
          $stmt = $conn->prepare("DELETE FROM $role WHERE $col= ?");
          $stmt->bind_param('i', $id);
          try {
            $stmt->execute();
            $_SESSION['toast'] = 'Employee removed.';
          } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1451) {
                $_SESSION['toast'] = 'Cannot delete this employee — they still have staff or products assigned to them. Please reassign them first.';
            } else {
                $_SESSION['toast'] = 'An unexpected error occurred.';
            }
          }
        }
        header('Location: employees.php'); exit;
    }
}

$employees = getAllEmployees($conn);

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
            <td><strong><?= htmlspecialchars($e['FirstName'] . ' ' . $e['LastName']) ?></strong></td>
            <td class="mono"><?= $e['EmployeeID'] ?></td>
            <td><?= fmtDate($e['BirthDate']) ?></td>
            <td><span class="badge <?= $roleBadge[$e['Role']] ?? '' ?>"><?= $e['Role'] ?></span></td>
            <td class="mono"><?= $e['ManagerID'] ?? '—' ?></td>
            <td>
              <div style="display:flex; gap:6px;">
                <a href="edit_employee.php?id=<?= $e['EmployeeID'] ?>&role=<?= $e['Role'] ?>" 
                    class="btn-icon btn-edit" title="Edit">✏️</a>
                <form method="post" style="margin:0" onsubmit="return confirm('Remove this employee? This cannot be undone.')">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="eid"   value="<?= $e['EmployeeID'] ?>">
                  <input type="hidden" name="erole" value="<?= $e['Role'] ?>">
                  <button type="submit" class="btn-icon btn-danger" title="Delete">🗑</button>
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
