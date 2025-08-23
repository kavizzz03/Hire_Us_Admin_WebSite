<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username   = "u569550465_math_rakusa";
$password   = "Sithija2025#";
$dbname     = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}
$conn->set_charset("utf8mb4");

// Check for job_id
if (!isset($_GET['job_id'])) {
    echo json_encode(["error" => "job_id parameter missing"]);
    exit;
}

$job_id = $_GET['job_id'];

// Step 1: Get all worker id_numbers from job_hires_log
$sql_ids = "SELECT id_number FROM job_hires_log WHERE job_id = ?";
$stmt_ids = $conn->prepare($sql_ids);
$stmt_ids->bind_param("i", $job_id);
$stmt_ids->execute();
$result_ids = $stmt_ids->get_result();

$id_numbers = [];
while ($row = $result_ids->fetch_assoc()) {
    $id_numbers[] = $row['id_number'];
}

// If no workers found
if (count($id_numbers) == 0) {
    echo json_encode([]);
    exit;
}

// Step 2: Get worker details from workers table
// Prepare placeholders for IN clause
$placeholders = implode(',', array_fill(0, count($id_numbers), '?'));
$types = str_repeat('s', count($id_numbers)); // all id_numbers are strings

$sql_workers = "SELECT idNumber, fullName, contactNumber, email, jobTitle FROM workers WHERE idNumber IN ($placeholders)";
$stmt_workers = $conn->prepare($sql_workers);

// Bind parameters dynamically
$stmt_workers->bind_param($types, ...$id_numbers);
$stmt_workers->execute();
$result_workers = $stmt_workers->get_result();

$workers = [];
while ($row = $result_workers->fetch_assoc()) {
    $workers[] = [
        "id_number" => $row['idNumber'],
        "name" => $row['fullName'],
        "contact_number" => $row['contactNumber'],
        "email" => $row['email'],
        "jobTitle" => $row['jobTitle']
    ];
}

echo json_encode($workers);
?>
