<?php
header('Content-Type: application/json');
error_reporting(0);

// Database connection
$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

// Get ID from URL query
$idNumber = isset($_GET['id_number']) ? $_GET['id_number'] : '';

if (empty($idNumber)) {
    echo json_encode(["success" => false, "message" => "Missing ID number"]);
    exit;
}

$sql = "SELECT * FROM workers WHERE idNumber = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $idNumber);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode(["success" => true, "user" => $user]);
} else {
    echo json_encode(["success" => false, "message" => "User not found"]);
}

$stmt->close();
$conn->close();
?>
