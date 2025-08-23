<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);

$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "DB Connection failed"]);
    exit;
}

$id_number = $_POST['id_number'] ?? '';
$job_id = $_POST['job_id'] ?? '';
$wants_meals = $_POST['wants_meals'] ?? '';

if (!$id_number || !$job_id || !$wants_meals) {
    echo json_encode(["success" => false, "error" => "Missing parameters"]);
    exit;
}

// Update job_hires table, add a new column "wants_meals" varchar(3) DEFAULT 'no' if not exists
// You may have to run this once in your DB:
// ALTER TABLE job_hires ADD COLUMN wants_meals VARCHAR(3) DEFAULT 'no';

$sql = "UPDATE job_hires SET wants_meals = ? WHERE id_number = ? AND job_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $wants_meals, $id_number, $job_id);
if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
