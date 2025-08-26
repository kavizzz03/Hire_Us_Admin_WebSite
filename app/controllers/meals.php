<?php
header('Content-Type: application/json');
error_reporting(0);

// DB config
$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    exit();
}

$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;
if ($job_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid job_id"]);
    exit();
}

$sql = "SELECT meal_name, description, meal_price FROM meals WHERE job_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();

$meals = [];
while ($row = $result->fetch_assoc()) {
    $meals[] = $row;
}

if (count($meals) > 0) {
    echo json_encode(["success" => true, "meals" => $meals]);
} else {
    echo json_encode(["success" => false, "meals" => [], "message" => "No meals available"]);
}

$stmt->close();
$conn->close();
?>
