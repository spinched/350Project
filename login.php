<?php
require 'db.php';
$activePage = 'login';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $e_id = trim($_POST['e_id'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$e_id) $errors['e_id'] = 'Employee ID is required.';
    if (!$password) $errors['password'] = 'Password is required.';

    if (empty($errors)) {
        $user = getEmployee($conn, $e_id);

        if (!$user || !password_verify($password, $user['Password'])) {
            $errors['general'] = 'Invalid ID or password.';
        } else {
            $_SESSION['role']     = $user['Role'];
            $_SESSION['user_id']  = $user['EmployeeID'];
            $_SESSION['name']     = $user['FirstName'];
            header('Location: index.php'); exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login · GreenLeaf Market</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>

<?php require 'nav.php'; ?>

<main class="page-container">
  <h1 class="page-title" style="margin-bottom:24px;">Login</h1>

  <form method="post" action="login.php" style="max-width:400px;">

    <?php if (isset($errors['general'])): ?>
      <p style="color:red;margin-bottom:16px;"><?= htmlspecialchars($errors['general']) ?></p>
    <?php endif; ?>

    <div class="form-grid">
      <div class="field-group">
        <label for="first_name">ID Number *</label>
        <input id="e_id" name="e_id" type="text"
               value="<?= htmlspecialchars($_POST['e_id'] ?? '') ?>"
               class="<?= isset($errors['e_id']) ? 'input-error' : '' ?>" />
        <?php if (isset($errors['e_id'])): ?>
          <span class="field-error"><?= $errors['e_id'] ?></span>
        <?php endif; ?>
      </div>

      <div class="field-group">
        <label for="password">Password *</label>
        <input id="password" name="password" type="password"
               class="<?= isset($errors['password']) ? 'input-error' : '' ?>" />
        <?php if (isset($errors['password'])): ?>
          <span class="field-error"><?= $errors['password'] ?></span>
        <?php endif; ?>
      </div>
    </div>

    <div style="margin-top:24px;">
      <button type="submit" class="btn btn-primary">Login</button>
    </div>
  </form>
</main>

<footer class="site-footer">
  <p>© 2026 GreenLeaf Market · Fresh · Local · Trusted</p>
</footer>

</body>
</html>