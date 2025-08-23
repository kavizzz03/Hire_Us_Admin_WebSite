<?php
// Database connection (inside the same page)
$servername = "localhost";
$username   = "u569550465_math_rakusa";
$password   = "Sithija2025#";
$dbname     = "u569550465_hireme";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}
$conn->set_charset("utf8mb4");

// Get employer_id
if (!isset($_GET['employer_id'])) {
    echo json_encode([]);
    exit;
}
$employer_id = $_GET['employer_id'];

// Fetch deleted jobs for the employer
$sql = "SELECT job_id, job_title, job_date, location FROM deleted_jobs WHERE employee_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employer_id);
$stmt->execute();
$result = $stmt->get_result();

$jobs = [];
while ($row = $result->fetch_assoc()) {
    $jobs[] = [
        "job_id" => $row['job_id'],
        "job_title" => $row['job_title'],
        "date" => $row['job_date'],
        "location" => $row['location']
    ];
}

echo json_encode($jobs);
?>
