<?php
header("Content-Type: application/json");
error_reporting(0);

$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

// Validate input
$idNumber = isset($_POST['id_number']) ? trim($_POST['id_number']) : '';
if (empty($idNumber)) {
    echo json_encode(["success" => false, "message" => "Worker ID required"]);
    exit();
}

// Get worker details
$sqlWorker = "SELECT * FROM workers WHERE idNumber = ?";
$stmtWorker = $conn->prepare($sqlWorker);
$stmtWorker->bind_param("s", $idNumber);
$stmtWorker->execute();
$resultWorker = $stmtWorker->get_result();

if ($resultWorker->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Worker not found"]);
    $stmtWorker->close();
    $conn->close();
    exit();
}

$worker = $resultWorker->fetch_assoc();
$workerId = $worker['id'];
$stmtWorker->close();

// Get reviews
$sqlReviews = "SELECT * 
               FROM worker_ratings 
               WHERE worker_id = ?";
$stmtReviews = $conn->prepare($sqlReviews);
$stmtReviews->bind_param("i", $workerId);
$stmtReviews->execute();
$resultReviews = $stmtReviews->get_result();

$reviews = [];
while ($row = $resultReviews->fetch_assoc()) {
    $reviews[] = $row;
}
$stmtReviews->close();

// Get rating summary
$sqlSummary = "SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews FROM worker_ratings WHERE worker_id = ?";
$stmtSummary = $conn->prepare($sqlSummary);
$stmtSummary->bind_param("i", $workerId);
$stmtSummary->execute();
$resultSummary = $stmtSummary->get_result();
$summary = $resultSummary->fetch_assoc();
$stmtSummary->close();

$conn->close();

// Output response
echo json_encode([
    "success" => true,
    "worker" => $worker,
    "reviews" => $reviews,
    "rating_summary" => $summary
], JSON_UNESCAPED_UNICODE);
?>
