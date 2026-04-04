<?php
require 'db.php';
$activePage = 'employees';
$errors     = [];

if (!hasAccess(['IT'])) {
    header('Location: employees.php'); exit;
}

