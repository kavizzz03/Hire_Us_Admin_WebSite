<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "DB connection failed"]);
    exit();
}

// Get POST data
$job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
$id_number = isset($_POST['id_number']) ? $conn->real_escape_string($_POST['id_number']) : '';

if ($job_id <= 0 || empty($id_number)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid input"]);
    exit();
}

// Optional: Check if worker has already applied (implement logic as needed)

// Insert application record into job_applications table (create this table as needed)
$sql = "INSERT INTO job_applications (job_id, worker_id_number, applied_at) VALUES (?, ?, NOW())";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Prepare failed"]);
    exit();
}
$stmt->bind_param("is", $job_id, $id_number);

if ($stmt->execute()) {
    echo json_encode(["success" => "Application submitted successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to apply for job"]);
}

$stmt->close();
$conn->close();
?>
