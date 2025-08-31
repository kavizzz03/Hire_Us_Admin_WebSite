<?php
$conn = new mysqli("localhost", "u569550465_math_rakusa", "Sithija2025#", "u569550465_hireme");
if ($conn->connect_error) die("DB connection failed.");

$worker_id = $_POST['worker_id'];
$rated_by = $_POST['rated_by'];
$rating = $_POST['rating'];
$feedback = $_POST['feedback'];
$job_title = $_POST['job_title'];
$company_name = $_POST['company_name'];
$duration = $_POST['duration'];

$stmt = $conn->prepare("INSERT INTO worker_ratings (worker_id, rated_by, rating, feedback, job_title, company_name, duration) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isissss", $worker_id, $rated_by, $rating, $feedback, $job_title, $company_name, $duration);
$stmt->execute();

header("Location: ratings.php");
?>
