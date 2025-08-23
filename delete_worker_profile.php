<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// Database config
$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

// Connect to database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Get id_number from GET parameters
$idNumber = $_GET['id_number'] ?? '';
if (empty($idNumber)) {
    echo json_encode(["success" => false, "message" => "ID number is required"]);
    exit();
}

// Fetch worker info
$stmt = $conn->prepare("SELECT id, fullName, email FROM workers WHERE idNumber = ?");
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
    exit();
}
$stmt->bind_param("s", $idNumber);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Worker not found"]);
    $stmt->close();
    $conn->close();
    exit();
}

$worker = $result->fetch_assoc();
$workerId = $worker['id'];
$userFullName = $worker['fullName'];
$userEmail = $worker['email'];
$stmt->close();

// Delete ratings explicitly
$deleteRatings = $conn->prepare("DELETE FROM worker_ratings WHERE worker_id = ?");
if (!$deleteRatings) {
    echo json_encode(["success" => false, "message" => "Prepare failed (delete ratings): " . $conn->error]);
    $conn->close();
    exit();
}
$deleteRatings->bind_param("i", $workerId);
$deleteRatings->execute();
$ratingsDeleted = $deleteRatings->affected_rows;
$deleteRatings->close();

// Delete worker record
$deleteWorker = $conn->prepare("DELETE FROM workers WHERE id = ?");
if (!$deleteWorker) {
    echo json_encode(["success" => false, "message" => "Prepare failed (delete worker): " . $conn->error]);
    $conn->close();
    exit();
}
$deleteWorker->bind_param("i", $workerId);

if ($deleteWorker->execute()) {
    // Send email notification
    $subject = "Your HireMe Profile Has Been Deleted";
    $message = "Dear $userFullName,\n\nYour HireMe profile has been deleted.\n\nThank you,\nHireUs Team";
    $headers = "From: HireMe <no-reply@hireme.cpsharetxt.com>";

    if (filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        @mail($userEmail, $subject, $message, $headers);
    }

    echo json_encode([
        "success" => true,
        "message" => "Worker deleted and email sent",
        "ratings_deleted" => $ratingsDeleted
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to delete worker: " . $deleteWorker->error
    ]);
}

$deleteWorker->close();
$conn->close();
exit();
