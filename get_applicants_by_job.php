<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "DB connection failed"]));
}

$jobId = $_GET['job_id'];

$sql = "SELECT w.fullName, w.idNumber, w.contactNumber, w.email, a.applied_at
        FROM job_applications a
        JOIN workers w ON a.worker_id_number = w.idNumber
        WHERE a.job_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $jobId);
$stmt->execute();
$result = $stmt->get_result();

$applicants = [];
while ($row = $result->fetch_assoc()) {
    $applicants[] = $row;
}

echo json_encode(["success" => true, "applicants" => $applicants]);
$conn->close();
?>
