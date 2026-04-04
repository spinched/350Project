<?php
require 'db.php';
$activePage = 'employees';
$errors     = [];

if (!hasAccess(['IT'])) {
    header('Location: employees.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role  = trim($_POST['role']     ?? '');
    $first = trim($_POST['first']    ?? '');
    $last  = trim($_POST['last']     ?? '');
    $dob   = trim($_POST['dob']      ?? '');
    $pw    = $_POST['pw']            ?? '';
    $mID   = (int)($_POST['manager'] ?? 0);
    $itID  = (int)($_POST['it']      ?? 0);

    if (!in_array($role, ['IT','Manager','Stocker']))         $errors['role']     = 'Please select a role.';
    if ($e = validateName($first, 'First name'))              $errors['first']    = $e;
    if ($e = validateName($last,  'Last name'))               $errors['last']     = $e;
    if (!$dob || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob))  $errors['dob']      = 'Date of birth is required.';
    if ($e = validatePassword($pw))                           $errors['pw']       = $e;
    if ($role === 'Stocker' && !$mID)                         $errors['manager']  = 'Please assign a manager.';
    if (in_array($role, ['Manager','Stocker']) && !$itID)     $errors['it']       = 'Please assign an IT staff member.';
    if (empty($errors)) {
        $hashedPw = password_hash($pw, PASSWORD_DEFAULT);
        if ($role === 'IT') {
            $stmt = $conn->prepare("INSERT INTO IT (IT_FirstName, IT_LastName, IT_BirthDate, IT_Password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssss', $first, $last, $dob, $hashedPw);
            $stmt->execute();
        } elseif ($role === 'Manager') {
            $stmt = $conn->prepare("INSERT INTO MANAGER (M_FirstName, M_LastName, M_BirthDate, M_Password, IT_ID) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('ssssi', $first, $last, $dob, $hashedPw, $itID);
            $stmt->execute();
        } else if ($role === 'Stocker') {
            $stmt = $conn->prepare("INSERT INTO STOCKER (S_FirstName, S_LastName, S_BirthDate, S_Password, M_ID, IT_ID) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ssssii', $first, $last, $dob, $hashedPw, $mID, $itID);
            $stmt->execute();
        }
        $_SESSION['toast'] = "$first $last added as $role.";
        header('Location: employees.php'); exit;
    }
}

$selectedRole = $_POST['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add Employee · GreenLeaf Market</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>

<?php require 'nav.php'; ?>

<main class="page-container">
  <div class="breadcrumb" style="margin-bottom:20px;">
    <a href="employees.php">← Back to Employees</a>
  </div>
  <h1 class="page-title" style="margin-bottom:24px;">Add New Employee</h1>

  <form method="post" style="max-width:600px;">
    <div class="form-grid">

      <div class="field-group field-group-full">
        <label for="role">Role *</label>
        <select id="role" name="role" onchange="this.form.submit()"
                class="<?= isset($errors['role']) ? 'input-error' : '' ?>">
          <option value="">Select role…</option>
          <?php foreach (['IT','Manager','Stocker'] as $r): ?>
            <option value="<?= $r ?>"<?= $selectedRole === $r ? ' selected' : '' ?>><?= $r ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['role'])): ?><span class="field-error"><?= $errors['role'] ?></span><?php endif; ?>
      </div>

      <?php if ($selectedRole === 'Stocker'): ?>
      <div class="field-group field-group-full">
        <label for="manager">Assigned Manager *</label>
        <select id="manager" name="manager" class="<?= isset($errors['manager']) ? 'input-error' : '' ?>">
          <option value="">Select manager…</option>
          <?php 
            $managers = getAllManagers($conn);
            foreach ($managers as $m):
          ?>
            <option value="<?= $m['M_ID'] ?>"<?= ($_POST['manager'] ?? '') == $m['M_ID'] ? ' selected' : '' ?>>
              <?= htmlspecialchars($m['M_FirstName'] . ' ' . $m['M_LastName']) ?> (<?= $m['M_ID'] ?>)
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['manager'])): ?><span class="field-error"><?= $errors['manager'] ?></span><?php endif; ?>
      </div>
      <?php endif; ?>

      <?php if ($selectedRole === 'Manager' || $selectedRole === 'Stocker'): ?>
      <div class="field-group field-group-full">
        <label for="it">Assigned IT Staff *</label>
        <select id="it" name="it" class="<?= isset($errors['it']) ? 'input-error' : '' ?>">
          <option value="">Select IT staff…</option>
          <?php foreach (getAllIT($conn) as $i): ?>
            <option value="<?= $i['IT_ID'] ?>"<?= ($_POST['it'] ?? '') == $i['IT_ID'] ? ' selected' : '' ?>>
              <?= htmlspecialchars($i['IT_FirstName'] . ' ' . $i['IT_LastName']) ?> (<?= $i['IT_ID'] ?>)
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['it'])): ?><span class="field-error"><?= $errors['it'] ?></span><?php endif; ?>
      </div>
      <?php endif; ?>

      <div class="field-group">
        <label for="first">First Name *</label>
        <input id="first" name="first" type="text" placeholder="Letters only"
               value="<?= htmlspecialchars($_POST['first'] ?? '') ?>"
               class="<?= isset($errors['first']) ? 'input-error' : '' ?>" />
        <?php if (isset($errors['first'])): ?><span class="field-error"><?= $errors['first'] ?></span><?php endif; ?>
      </div>

      <div class="field-group">
        <label for="last">Last Name *</label>
        <input id="last" name="last" type="text" placeholder="Letters only"
               value="<?= htmlspecialchars($_POST['last'] ?? '') ?>"
               class="<?= isset($errors['last']) ? 'input-error' : '' ?>" />
        <?php if (isset($errors['last'])): ?><span class="field-error"><?= $errors['last'] ?></span><?php endif; ?>
      </div>

      <div class="field-group">
        <label for="dob">Date of Birth *</label>
        <input id="dob" name="dob" type="date"
               value="<?= htmlspecialchars($_POST['dob'] ?? '') ?>"
               class="<?= isset($errors['dob']) ? 'input-error' : '' ?>" />
        <?php if (isset($errors['dob'])): ?><span class="field-error"><?= $errors['dob'] ?></span><?php endif; ?>
      </div>

      <div class="field-group">
        <label for="pw">Password *</label>
        <input id="pw" name="pw" type="password" placeholder="Min 12 characters"
               class="<?= isset($errors['pw']) ? 'input-error' : '' ?>" />
        <?php if (isset($errors['pw'])): ?><span class="field-error"><?= $errors['pw'] ?></span><?php endif; ?>
      </div>

    </div>

    <?php if ($selectedRole): ?>

    <div style="display:flex; gap:10px; margin-top:24px;">
      <button type="submit" name="submit" value="1" class="btn btn-primary">Add Employee</button>
      <a href="employees.php" class="btn btn-ghost">Cancel</a>
    </div>
    <?php endif; ?>
  </form>
</main>

<footer class="site-footer">
  <p>© 2026 GreenLeaf Market · Fresh · Local · Trusted</p>
</footer>

</body>
</html>
