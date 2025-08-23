<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);

// DB config
$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    exit;
}

$id_number = $_GET['id_number'] ?? '';

if (empty($id_number)) {
    echo json_encode(["success" => false, "message" => "No ID provided"]);
    exit;
}

// Check if worker exists
$checkWorker = $conn->prepare("SELECT * FROM workers WHERE idNumber = ?");
$checkWorker->bind_param("s", $id_number);
$checkWorker->execute();
$workerResult = $checkWorker->get_result();

if ($workerResult->num_rows === 0) {
    echo json_encode(["exists" => false, "hired" => false, "message" => "Worker not found"]);
    exit;
}

// Check if hired
$checkHired = $conn->prepare("SELECT job_id FROM job_hires WHERE id_number = ?");
$checkHired->bind_param("s", $id_number);
$checkHired->execute();
$hiredResult = $checkHired->get_result();

if ($hiredResult->num_rows > 0) {
    $row = $hiredResult->fetch_assoc();
    echo json_encode([
        "exists" => true,
        "hired" => true,
        "job_id" => $row['job_id'],
        "message" => "Worker is hired"
    ]);
} else {
    echo json_encode([
        "exists" => true,
        "hired" => false,
        "message" => "Worker is not hired yet"
    ]);
}

$conn->close();
?>
