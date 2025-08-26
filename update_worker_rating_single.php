<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "u569550465_math_rakusa", "Sithija2025#", "u569550465_hireme");
if ($conn->connect_error) { echo "DB connection failed"; exit; }

// Colombo time
date_default_timezone_set("Asia/Colombo");
$created_at = date("Y-m-d H:i:s");

$id_number = $_POST['id_number'] ?? '';
$rated_by = $_POST['rated_by'] ?? '';
$rating = $_POST['rating'] ?? '';
$experience = $_POST['work_experience'] ?? '';
$feedback = $_POST['feedback'] ?? '';
$job_title = $_POST['job_title'] ?? '';
$company_name = $_POST['company_name'] ?? '';
$duration = $_POST['duration'] ?? '';

if (!$id_number || !$rating) { echo "Missing required fields"; exit; }

// Map id_number → worker_id
$res = $conn->query("SELECT id FROM workers WHERE id_number='$id_number'");
if ($res->num_rows == 0) { echo "Worker not found"; exit; }
$row = $res->fetch_assoc();
$worker_id = $row['id'];

// Insert record
$stmt = $conn->prepare("INSERT INTO worker_ratings 
(worker_id, rated_by, rating, work_experience, feedback, job_title, company_name, duration, created_at)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssssss", $worker_id, $rated_by, $rating, $experience, $feedback, $job_title, $company_name, $duration, $created_at);

if ($stmt->execute()) { echo "Rating added successfully"; }
else { echo "Failed to add rating"; }

$stmt->close();
$conn->close();
?>
