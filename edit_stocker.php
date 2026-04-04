<?php
require 'db.php';
$activePage = 'stockers';
$errors     = [];

if (!hasAccess(['Manager, IT'])) {
    header('Location: employees.php'); exit;
}