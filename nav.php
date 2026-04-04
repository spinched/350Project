<?php
// $activePage must be set before including this file

$role = getRole();
$links = [
    ['href'=>'index.php',    'label'=>'Store',     'page'=>'store',     'roles'=>['Customer','Stocker','Manager','IT']],
    ['href'=>'stocking.php', 'label'=>'Inventory', 'page'=>'stocking',  'roles'=>['Stocker','Manager','IT']],
    ['href'=>'stockers.php', 'label'=>'Stockers',  'page'=>'stockers',  'roles'=>['Manager','IT']],
    ['href'=>'employees.php','label'=>'Employees', 'page'=>'employees', 'roles'=>['IT']],
];
$roles = ['Customer','Stocker','Manager','IT'];
?>
<nav id="main-nav">
  <div class="nav-inner">
    <a href="index.php" class="nav-logo">
      <span class="logo-icon">🌿</span>
      <span class="logo-text">GreenLeaf Market</span>
    </a>
    <div class="nav-links">
      <?php foreach ($links as $l): ?>
        <?php if (in_array($role, $l['roles'])): ?>
          <a href="<?= $l['href'] ?>" class="nav-link<?= ($activePage ?? '') === $l['page'] ? ' active' : '' ?>">
            <?= htmlspecialchars($l['label']) ?>
          </a>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <div class="nav-role">
      <span class="role-label"></span>
        <div class="nav-role">
          <?php if ($role === 'Customer'): ?>
            <?php if ($activePage !== 'login'): ?>
              <a href="login.php" class="btn btn-primary">Employee Login</a>
            <?php endif; ?>
          <?php else: ?>
            <span class="role-label"><?= htmlspecialchars($_SESSION['name'] ?? '') ?></span>
            <a href="logout.php" class="btn btn-primary">Logout</a>
          <?php endif; ?>
        </div>
    </div>
  </div>
</nav>
