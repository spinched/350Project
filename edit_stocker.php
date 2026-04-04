<?php
require 'db.php';
$activePage = 'stockers';
$errors     = [];

if (!hasAccess(['Manager, IT'])) {
    header('Location: stockers.php'); exit;
}