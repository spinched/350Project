<?php
require 'db.php';
$activePage = 'stocking';
$errors     = [];

if (!hasAccess(['Manager','IT'])) {
    header('Location: stocking.php'); exit;
}