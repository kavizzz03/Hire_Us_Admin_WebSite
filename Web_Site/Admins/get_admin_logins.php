<?php
header('Content-Type: application/json');
session_start();
require 'db.php';

// Only allow superadmins
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'superadmin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$username = $_GET['username'] ?? '';

if (!$username) {
    echo json_encode(['success' => false, 'message' => 'No username provided']);
    exit();
}

// Get admin ID from username
$stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Admin not found']);
    exit();
}

$admin = $res->fetch_assoc();
$admin_id = $admin['id'];

// Fetch all login timestamps
$stmt = $conn->prepare("SELECT login_time FROM admin_logins WHERE admin_id = ? ORDER BY login_time DESC");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

$logins = [];
while ($row = $result->fetch_assoc()) {
    $logins[] = ['login_time' => $row['login_time']]; // return as object for consistency
}

echo json_encode(['success' => true, 'logins' => $logins]);
