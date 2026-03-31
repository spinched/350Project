<?php
session_start();
$allowed = ['Customer', 'Stocker', 'Manager', 'IT'];
if (isset($_POST['role']) && in_array($_POST['role'], $allowed)) {
    $_SESSION['role'] = $_POST['role'];
}
$redirect = $_POST['redirect'] ?? 'index.php';
// Basic safety: only allow relative redirects
$redirect = preg_replace('/[^a-zA-Z0-9\/_\-\.?=&]/', '', $redirect);
header('Location: ' . $redirect);
exit;
