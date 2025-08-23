<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB Connection failed']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id'], $data['sender'], $data['message'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

$user_id = $conn->real_escape_string($data['user_id']);
$sender = $conn->real_escape_string($data['sender']); // "user" or "admin"
$message = $conn->real_escape_string($data['message']);

// Optional: Check if chat session exists for user
$sqlCheck = "SELECT * FROM chat_sessions WHERE user_id = '$user_id'";
$result = $conn->query($sqlCheck);

if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'No chat session found']);
    exit();
}

// Insert message
$sqlInsert = "INSERT INTO chat_messages (user_id, sender, message, sent_at) VALUES ('$user_id', '$sender', '$message', NOW())";
if ($conn->query($sqlInsert) === TRUE) {
    echo json_encode(['success' => true, 'message' => 'Message sent']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
}

$conn->close();
?>
