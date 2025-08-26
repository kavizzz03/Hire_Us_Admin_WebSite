<?php
// get_admin_logins.php
header('Content-Type: application/json');
require 'db.php';

if (!isset($_GET['username']) || empty($_GET['username'])) {
    echo json_encode(['success' => false, 'message' => 'Username missing']);
    exit;
}

$username = $_GET['username'];

// Get admin id
$stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows !== 1) {
    echo json_encode(['success' => false, 'message' => 'Admin not found']);
    exit;
}

$row = $res->fetch_assoc();
$admin_id = $row['id'];

// Get login times ordered descending
$stmt2 = $conn->prepare("SELECT login_time FROM admin_logins WHERE admin_id = ? ORDER BY login_time DESC");
$stmt2->bind_param("i", $admin_id);
$stmt2->execute();
$res2 = $stmt2->get_result();

$logins = [];
while ($loginRow = $res2->fetch_assoc()) {
    // send in UTC ISO format, JS will convert to local
    $logins[] = $loginRow['login_time'];
}

echo json_encode(['success' => true, 'logins' => $logins]);
exit;
