<?php
require 'db.php';
$activePage = 'employees';
$errors     = [];
 
if (!hasAccess(['IT'])) {
    header('Location: employees.php'); exit;
}


$id   = (int)($_GET['id']   ?? $_POST['id']   ?? 0);
$role = trim($_GET['role']  ?? $_POST['role']  ?? '');
 
if (!$id || !in_array($role, ['IT','Manager','Stocker'])) {
    header('Location: employees.php'); exit;
}
 

if ($role === 'IT') {
    $stmt = $conn->prepare("SELECT IT_ID AS EID, IT_FirstName AS FirstName, IT_LastName AS LastName, IT_BirthDate AS BirthDate FROM IT WHERE IT_ID = ?");
} elseif ($role === 'Manager') {
    $stmt = $conn->prepare("SELECT M_ID AS EID, M_FirstName AS FirstName, M_LastName AS LastName, M_BirthDate AS BirthDate, IT_ID FROM MANAGER WHERE M_ID = ?");
} else {
    $stmt = $conn->prepare("SELECT S_ID AS EID, S_FirstName AS FirstName, S_LastName AS LastName, S_BirthDate AS BirthDate, M_ID, IT_ID FROM STOCKER WHERE S_ID = ?");
}
$stmt->bind_param('i', $id);
$stmt->execute();
$emp = $stmt->get_result()->fetch_assoc();
 
if (!$emp) {
    $_SESSION['toast'] = 'Employee not found.';
    header('Location: employees.php'); exit;
}
 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $first = trim($_POST['first'] ?? '');
    $last  = trim($_POST['last']  ?? '');
    $dob   = trim($_POST['dob']   ?? '');
    $pw    = $_POST['pw']         ?? '';
    $mID   = (int)($_POST['manager'] ?? 0);
    $itID  = (int)($_POST['it']      ?? 0);
 
    // Validate
    if ($e = validateName($first, 'First name'))              $errors['first']   = $e;
    if ($e = validateName($last,  'Last name'))               $errors['last']    = $e;
    if (!$dob || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) $errors['dob']     = 'Date of birth is required.';
    if ($pw !== '' && ($e = validatePassword($pw)))           $errors['pw']      = $e;
    if ($role === 'Stocker' && !$mID)                         $errors['manager'] = 'Please assign a manager.';
    if (in_array($role, ['Manager','Stocker']) && !$itID)     $errors['it']      = 'Please assign an IT staff member.';
 
    if (empty($errors)) {
        if ($role === 'IT') {
            if ($pw !== '') {
                $hash = password_hash($pw, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE IT SET IT_FirstName = ?, IT_LastName = ?, IT_BirthDate = ?, IT_Password = ? WHERE IT_ID = ?");
                $stmt->bind_param('ssssi', $first, $last, $dob, $hash, $id);
            } else {
                $stmt = $conn->prepare("UPDATE IT SET IT_FirstName = ?, IT_LastName = ?, IT_BirthDate = ? WHERE IT_ID = ?");
                $stmt->bind_param('sssi', $first, $last, $dob, $id);
            }
        } elseif ($role === 'Manager') {
            if ($pw !== '') {
                $hash = password_hash($pw, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE MANAGER SET M_FirstName = ?, M_LastName = ?, M_BirthDate = ?, M_Password = ?, IT_ID = ? WHERE M_ID = ?");
                $stmt->bind_param('ssssii', $first, $last, $dob, $hash, $itID, $id);
            } else {
                $stmt = $conn->prepare("UPDATE MANAGER SET M_FirstName = ?, M_LastName = ?, M_BirthDate = ?, IT_ID = ? WHERE M_ID = ?");
                $stmt->bind_param('sssii', $first, $last, $dob, $itID, $id);
            }
        } else { // Stocker
            if ($pw !== '') {
                $hash = password_hash($pw, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE STOCKER SET S_FirstName = ?, S_LastName = ?, S_BirthDate = ?, S_Password = ?, M_ID = ?, IT_ID = ? WHERE S_ID = ?");
                $stmt->bind_param('ssssiii', $first, $last, $dob, $hash, $mID, $itID, $id);
            } else {
                $stmt = $conn->prepare("UPDATE STOCKER SET S_FirstName = ?, S_LastName = ?, S_BirthDate = ?, M_ID = ?, IT_ID = ? WHERE S_ID = ?");
                $stmt->bind_param('sssiii', $first, $last, $dob, $mID, $itID, $id);
            }
        }
 
        try {
            $stmt->execute();
            $_SESSION['toast'] = "$first $last updated.";
            header('Location: employees.php'); exit;
        } catch (mysqli_sql_exception $ex) {
            $errors['general'] = 'Failed to update employee. Please try again.';
        }
    }
 
    // Overwrite $emp values so the form re-displays posted data on validation failure
    $emp['FirstName'] = $first;
    $emp['LastName']  = $last;
    $emp['BirthDate'] = $dob;
    if ($role === 'Stocker')                      $emp['M_ID']  = $mID;
    if (in_array($role, ['Manager','Stocker']))   $emp['IT_ID'] = $itID;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Employee · GreenLeaf Market</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
 
<?php require 'nav.php'; ?>
 
<main class="page-container">
  <div class="breadcrumb" style="margin-bottom:20px;">
    <a href="employees.php">← Back to Employees</a>
  </div>
  <h1 class="page-title" style="margin-bottom:24px;">
    Edit <?= htmlspecialchars($role) ?> — <?= htmlspecialchars($emp['FirstName'] . ' ' . $emp['LastName']) ?>
  </h1>
 
  <?php if (isset($errors['general'])): ?>
    <p style="color:var(--tomato);background:var(--tomato-light);padding:10px 16px;border-radius:var(--radius);margin-bottom:16px;">
      <?= htmlspecialchars($errors['general']) ?>
    </p>
  <?php endif; ?>
 
  <form method="post" style="max-width:600px;">
    <input type="hidden" name="id"   value="<?= $id ?>">
    <input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>">
 
    <div class="form-grid">
 
      <!-- Role (read-only) -->
      <div class="field-group field-group-full">
        <label>Role</label>
        <input type="text" value="<?= htmlspecialchars($role) ?>" disabled
               style="background:var(--cream-mid);color:var(--text-mid);cursor:not-allowed;" />
      </div>
 
      <!-- Stocker: Assigned Manager -->
      <?php if ($role === 'Stocker'): ?>
      <div class="field-group field-group-full">
        <label for="manager">Assigned Manager *</label>
        <select id="manager" name="manager" class="<?= isset($errors['manager']) ? 'input-error' : '' ?>">
          <option value="">Select manager…</option>
          <?php foreach (getAllManagers($conn) as $m): ?>
            <option value="<?= $m['M_ID'] ?>"<?= ($emp['M_ID'] ?? '') == $m['M_ID'] ? ' selected' : '' ?>>
              <?= htmlspecialchars($m['M_FirstName'] . ' ' . $m['M_LastName']) ?> (<?= $m['M_ID'] ?>)
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['manager'])): ?><span class="field-error"><?= $errors['manager'] ?></span><?php endif; ?>
      </div>
      <?php endif; ?>
 
      <!-- Manager / Stocker: Assigned IT -->
      <?php if ($role === 'Manager' || $role === 'Stocker'): ?>
      <div class="field-group field-group-full">
        <label for="it">Assigned IT Staff *</label>
        <select id="it" name="it" class="<?= isset($errors['it']) ? 'input-error' : '' ?>">
          <option value="">Select IT staff…</option>
          <?php foreach (getAllIT($conn) as $i): ?>
            <option value="<?= $i['IT_ID'] ?>"<?= ($emp['IT_ID'] ?? '') == $i['IT_ID'] ? ' selected' : '' ?>>
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
               value="<?= htmlspecialchars($emp['FirstName']) ?>"
               class="<?= isset($errors['first']) ? 'input-error' : '' ?>" />
        <?php if (isset($errors['first'])): ?><span class="field-error"><?= $errors['first'] ?></span><?php endif; ?>
      </div>
 
      <div class="field-group">
        <label for="last">Last Name *</label>
        <input id="last" name="last" type="text" placeholder="Letters only"
               value="<?= htmlspecialchars($emp['LastName']) ?>"
               class="<?= isset($errors['last']) ? 'input-error' : '' ?>" />
        <?php if (isset($errors['last'])): ?><span class="field-error"><?= $errors['last'] ?></span><?php endif; ?>
      </div>
 
      <div class="field-group">
        <label for="dob">Date of Birth *</label>
        <input id="dob" name="dob" type="date"
               value="<?= htmlspecialchars($emp['BirthDate']) ?>"
               class="<?= isset($errors['dob']) ? 'input-error' : '' ?>" />
        <?php if (isset($errors['dob'])): ?><span class="field-error"><?= $errors['dob'] ?></span><?php endif; ?>
      </div>
 
      <div class="field-group">
        <label for="pw">New Password <span class="optional">(leave blank to keep current)</span></label>
        <input id="pw" name="pw" type="password" placeholder="Min 12 characters"
               class="<?= isset($errors['pw']) ? 'input-error' : '' ?>" />
        <?php if (isset($errors['pw'])): ?><span class="field-error"><?= $errors['pw'] ?></span><?php endif; ?>
      </div>
 
    </div>
 
    <div style="display:flex; gap:10px; margin-top:24px;">
      <button type="submit" name="submit" value="1" class="btn btn-primary">Save Changes</button>
      <a href="employees.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</main>
 
<footer class="site-footer">
  <p>© 2026 GreenLeaf Market · Fresh · Local · Trusted</p>
</footer>
 
</body>
</html>
