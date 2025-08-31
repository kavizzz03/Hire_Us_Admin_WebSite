<?php
// fetch_worker.php
$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die(json_encode(['success'=>false, 'message'=>'DB connection failed']));

$id = intval($_GET['id'] ?? 0);
if($id <= 0) {
    echo json_encode(['success'=>false, 'message'=>'Invalid ID']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM workers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows === 0){
    echo json_encode(['success'=>false, 'message'=>'Worker not found']);
    exit;
}
$worker = $result->fetch_assoc();
echo json_encode(['success'=>true, 'worker'=>$worker]);
?>
