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

$idNumber = $_GET['id_number'];

$response = [];

// Get worker info
$worker_sql = "SELECT * FROM workers WHERE idNumber = ?";
$worker_stmt = $conn->prepare($worker_sql);
$worker_stmt->bind_param("s", $idNumber);
$worker_stmt->execute();
$worker_result = $worker_stmt->get_result();

if ($worker_result->num_rows > 0) {
    $response['worker'] = $worker_result->fetch_assoc();
} else {
    echo json_encode(["success" => false, "message" => "Worker not found"]);
    exit();
}

// Get average rating
$rating_sql = "SELECT AVG(rating) as avg_rating FROM worker_reviews WHERE worker_id = (SELECT id FROM workers WHERE idNumber = ?)";
$rating_stmt = $conn->prepare($rating_sql);
$rating_stmt->bind_param("s", $idNumber);
$rating_stmt->execute();
$rating_result = $rating_stmt->get_result();
$avg_rating = $rating_result->fetch_assoc()['avg_rating'];
$response['avg_rating'] = $avg_rating ? round($avg_rating, 2) : 0;

// Get all reviews
$review_sql = "SELECT * FROM worker_reviews WHERE worker_id = (SELECT id FROM workers WHERE idNumber = ?)";
$review_stmt = $conn->prepare($review_sql);
$review_stmt->bind_param("s", $idNumber);
$review_stmt->execute();
$review_result = $review_stmt->get_result();

$reviews = [];
while ($row = $review_result->fetch_assoc()) {
    $reviews[] = $row;
}
$response['reviews'] = $reviews;

echo json_encode(["success" => true, "data" => $response]);
$conn->close();
?>
