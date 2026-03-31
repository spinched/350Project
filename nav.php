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
      <span class="role-label">Role:</span>
      <form method="post" action="set_role.php" style="margin:0">
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
        <select name="role" class="role-select" onchange="this.form.submit()">
          <?php foreach ($roles as $r): ?>
            <option value="<?= $r ?>"<?= $r === $role ? ' selected' : '' ?>><?= $r ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
  </div>
</nav>
