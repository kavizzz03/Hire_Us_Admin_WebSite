<?php
// get_worker_ratings.php
header('Content-Type: application/json');
$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  echo json_encode(['success'=>false, 'message'=>'DB connection failed']);
  exit;
}

$worker_id = intval($_GET['worker_id'] ?? 0);
if($worker_id <= 0) {
  echo json_encode(['success'=>false, 'message'=>'Invalid worker ID']);
  exit;
}

$stmt = $conn->prepare("SELECT * FROM worker_ratings WHERE worker_id = ?");
$stmt->bind_param("i", $worker_id);
$stmt->execute();
$result = $stmt->get_result();

$ratings = [];
while($row = $result->fetch_assoc()){
  $ratings[] = $row;
}

echo json_encode(['success'=>true, 'ratings'=>$ratings]);
?>
