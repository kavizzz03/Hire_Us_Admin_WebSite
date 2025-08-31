<?php
// delete_worker.php
header('Content-Type: application/json');
$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  echo json_encode(['success'=>false,'message'=>'DB connection failed']);
  exit;
}

$id = intval($_GET['id'] ?? 0);
if($id <= 0) {
  echo json_encode(['success'=>false,'message'=>'Invalid ID']);
  exit;
}

// Get worker images to delete files
$stmt = $conn->prepare("SELECT idFrontImage, idBackImage FROM workers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows === 0){
  echo json_encode(['success'=>false,'message'=>'Worker not found']);
  exit;
}
$worker = $res->fetch_assoc();
$uploadDir = '../../uploads_employee/';

// Delete images if exists
if(!empty($worker['idFrontImage']) && file_exists($uploadDir . $worker['idFrontImage'])){
  unlink($uploadDir . $worker['idFrontImage']);
}
if(!empty($worker['idBackImage']) && file_exists($uploadDir . $worker['idBackImage'])){
  unlink($uploadDir . $worker['idBackImage']);
}

// Delete ratings for worker
$stmt = $conn->prepare("DELETE FROM worker_ratings WHERE worker_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// Delete worker
$stmt = $conn->prepare("DELETE FROM workers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

if($stmt->affected_rows > 0){
  echo json_encode(['success'=>true,'message'=>'Worker deleted successfully including ratings.']);
} else {
  echo json_encode(['success'=>false,'message'=>'Failed to delete worker.']);
}
$stmt->close();
$conn->close();
?>
