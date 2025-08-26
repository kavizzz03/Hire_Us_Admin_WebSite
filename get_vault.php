<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

$idNumber = $_POST['idNumber'] ?? null;
if (!$idNumber) {
    http_response_code(400);
    echo json_encode(["error" => "Missing idNumber"]);
    exit();
}

$stmt = $conn->prepare("SELECT job_id, salary, ot_hours, ot_salary, transaction_type, updated_at, status FROM vault WHERE idNumber = ? ORDER BY updated_at DESC");
$stmt->bind_param("s", $idNumber);
$stmt->execute();
$result = $stmt->get_result();

$vaultData = [];
while ($row = $result->fetch_assoc()) {
    $vaultData[] = [
        "job_id" => $row['job_id'],
        "salary" => (float)$row['salary'],
        "ot_hours" => (float)$row['ot_hours'],
        "ot_salary" => (float)$row['ot_salary'],
        "transaction_type" => $row['transaction_type'],
        "updated_at" => $row['updated_at'],
        "status" => $row['status']
    ];
}

echo json_encode($vaultData);

$stmt->close();
$conn->close();
?>
