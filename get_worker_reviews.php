<?php
header('Content-Type: application/json');
error_reporting(0);

// DB config
$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    exit();
}

$idNumber = $_POST['id_number'] ?? '';

if (empty($idNumber)) {
    echo json_encode(["success" => false, "message" => "Missing ID number"]);
    exit();
}

// Get worker ID
$workerSql = "SELECT id FROM workers WHERE idNumber = ?";
$stmt = $conn->prepare($workerSql);
$stmt->bind_param("s", $idNumber);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Worker not found"]);
    exit();
}

$row = $result->fetch_assoc();
$workerId = $row['id'];

// Get reviews
$reviewSql = "SELECT rated_by, rating, work_experience, feedback, job_title, company_name, duration, created_at 
              FROM worker_ratings 
              WHERE worker_id = ? ORDER BY created_at DESC";

$stmt = $conn->prepare($reviewSql);
$stmt->bind_param("i", $workerId);
$stmt->execute();
$result = $stmt->get_result();

$reviews = [];
while ($review = $result->fetch_assoc()) {
    $reviews[] = $review;
}

echo json_encode(["success" => true, "reviews" => $reviews]);
$conn->close();
?>
