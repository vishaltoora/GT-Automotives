<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
requireLogin();
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'No brand ID provided';
    header('Location: brands.php');
    exit;
}
$brand_id = intval($_GET['id']);
$stmt = $conn->prepare('SELECT * FROM brands WHERE id = ?');
$stmt->bindValue(1, $brand_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$brand = $result->fetchArray(SQLITE3_ASSOC);
if (!$brand) {
    $_SESSION['error_message'] = 'Brand not found';
    header('Location: brands.php');
    exit;
}
$stmt = $conn->prepare('DELETE FROM brands WHERE id = ?');
$stmt->bindValue(1, $brand_id, SQLITE3_INTEGER);
if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Brand deleted successfully';
} else {
    $_SESSION['error_message'] = 'Error deleting brand: ' . $conn->lastErrorMsg();
}
header('Location: brands.php');
exit; 