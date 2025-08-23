<?php
header('Content-Type: application/json');

$servername = "localhost"; // Hostinger MySQL host
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['status'=>'error','message'=>$conn->connect_error]));
}

$employer_id = $_POST['employer_id'] ?? '';

if(empty($employer_id)){
    echo json_encode(['status'=>'error','message'=>'Employer ID required']);
    exit;
}

// Get deleted job IDs
$jobIds = [];
$stmt = $conn->prepare("SELECT job_id FROM deleted_jobs WHERE employee_id=?");
$stmt->bind_param("i", $employer_id);
$stmt->execute();
$result = $stmt->get_result();
while($row = $result->fetch_assoc()){
    $jobIds[] = $row['job_id'];
}

if(count($jobIds) == 0){
    echo json_encode(['status'=>'success','data'=>[]]);
    exit;
}

// Get withdraw requests for these job IDs
$placeholders = implode(',', array_fill(0, count($jobIds), '?'));
$types = str_repeat('i', count($jobIds));

$sql = "SELECT * FROM withdraw_requests WHERE job_id IN ($placeholders) ORDER BY requested_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$jobIds);
$stmt->execute();
$res = $stmt->get_result();
$requests = [];
while($row = $res->fetch_assoc()){
    $requests[] = $row;
}

echo json_encode(['status'=>'success','data'=>$requests]);
?>
