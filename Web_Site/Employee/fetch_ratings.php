<?php
// fetch_ratings.php
$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  echo json_encode(['error' => 'Database connection failed']);
  exit;
}

$worker_id = intval($_GET['worker_id'] ?? 0);
if (!$worker_id) {
  echo json_encode(['error' => 'Invalid worker ID']);
  exit;
}

$stmt = $conn->prepare("SELECT rated_by, rating, work_experience, feedback, job_title, company_name, duration, created_at FROM worker_ratings WHERE worker_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $worker_id);
$stmt->execute();
$result = $stmt->get_result();

$ratings = [];
while ($row = $result->fetch_assoc()) {
  $ratings[] = $row;
}

echo json_encode($ratings);
