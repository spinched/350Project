<?php
require 'config.php'; //change this to config_example LATER

if (session_status() === PHP_SESSION_NONE) session_start();

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection to the database failed: " . $conn->connect_error);
}

function getRole(): string {
    return $_SESSION['role'] ?? 'Customer';
}

function hasAccess(array $roles): bool {
    return in_array(getRole(), $roles);
}

function getProduct(mysqli $conn, int $pid): array {
    $stmt = $conn->prepare("SELECT * FROM all_products WHERE P_ID = ?");
    $stmt->bind_param('i', $pid);
    $stmt->execute();
    $value = $stmt->get_result()->fetch_assoc();
    return is_array($value) ? $value : [];
}

function getAllProducts(mysqli $conn): array {
    $sql = "SELECT * FROM all_products";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        //handle error
        return [];
    } 
    $value = mysqli_fetch_all($result, MYSQLI_ASSOC);
    return is_array($value) ? $value : [];
}

function getAllProductsOnSale(mysqli $conn): array {
    $sql = "SELECT * FROM products_on_sale";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        //handle error
        return [];
    } 
    $value = mysqli_fetch_all($result, MYSQLI_ASSOC);
    return is_array($value) ? $value : [];
}

function getEmployee(mysqli $conn, int $eid): array {
    $stmt = $conn->prepare("SELECT * FROM all_employees WHERE EmployeeID = ?");
    $stmt->bind_param('i', $eid);
    $stmt->execute();
    $value = $stmt->get_result()->fetch_assoc();
    return is_array($value) ? $value : [];
}

function getAllEmployees(mysqli $conn): array {
    $sql = "SELECT * FROM all_employees";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        //handle error
        return [];
    } 
    $value = mysqli_fetch_all($result, MYSQLI_ASSOC);
    return is_array($value) ? $value : [];
}

function getAllManagers(mysqli $conn): array {
    $sql = "SELECT M_ID, M_FirstName, M_LastName FROM MANAGER";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        //handle error
        return [];
    } 
    $value = mysqli_fetch_all($result, MYSQLI_ASSOC);
    return is_array($value) ? $value : [];
}

function getAllIT(mysqli $conn): array {
    $sql = "SELECT IT_ID, IT_FirstName, IT_LastName FROM IT";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        //handle error
        return [];
    } 
    $value = mysqli_fetch_all($result, MYSQLI_ASSOC);
    return is_array($value) ? $value : [];
}

function getAllStockers(mysqli $conn): array {
    $sql = "SELECT * FROM all_stockers";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        //handle error
        return [];
    } 
    $value = mysqli_fetch_all($result, MYSQLI_ASSOC);
    return is_array($value) ? $value : [];
}

function getAllLocations(mysqli $conn): array {
    $result = $conn->query("SELECT L_ID, StoreAisle, P_Type FROM LOCATION ORDER BY StoreAisle");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getCategoryEmoji(?string $type): string {
    return ['fruit'=>'🍎','vegetables'=>'🥬','dairy'=>'🥛','poultry'=>'🐓','beef'=>'🥩','fish'=>'🐟','canned goods'=>'🥫','frozen goods'=>'❄️','pasta'=>'🍝','spices'=>'🫚','beverages'=>'🧃','bakery'=>'🥖','snacks'=>'🥨','baking'=>'🎂','household'=>'🏠','beauty'=>'💄','health'=>'💊'][strtolower($type)] ?? '🛒';
}
function getCategoryClass(?string $type): string {
    return ['fruit'=>'cat-fruit','vegetables'=>'cat-vegetables','dairy'=>'cat-dairy','poultry'=>'cat-poultry','beef'=>'cat-beef','fish'=>'cat-fish','canned goods'=>'cat-canned','frozen goods'=>'cat-frozen','pasta'=>'cat-pasta','spices'=>'cat-spices','beverages'=>'cat-beverages','bakery'=>'cat-bakery','snacks'=>'cat-snacks','baking'=>'cat-baking','household'=>'cat-household','beauty'=>'cat-beauty','health'=>'cat-health'][strtolower($type)] ?? '';
}

function fmtDate(string $d): ?string {
    if (!$d) return '—';
    [$y, $m, $day] = explode('-', $d);
    return "$m/$day/$y";
}

function fmtPrice(?float $val): string {
    return $val === null ? '—' : '$' . number_format($val, 2);
}

function fmtAisle(string $aisle): string {
    return strlen($aisle) < 2 ? '0' . $aisle : $aisle;
}

// ── Validation ────────────────────────────────────────────────
function validateName(string $val, string $label): ?string {
    if (!$val) return "$label is required.";
    if (!preg_match('/^[A-Za-z]+$/', $val)) return "$label must contain only letters (no spaces).";
    return null;
}

function validatePassword(string $val): ?string {
    if (!$val) return 'Password is required.';
    if (strlen($val) < 12) return 'Password must be at least 12 characters.';
    return null;
}
