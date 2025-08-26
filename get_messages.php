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

if (!isset($data['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User ID missing']);
    exit();
}

$user_id = $conn->real_escape_string($data['user_id']);

// Check if chat session exists
$sqlCheck = "SELECT * FROM chat_sessions WHERE user_id = '$user_id'";
$result = $conn->query($sqlCheck);

if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'No chat session found']);
    exit();
}

// Get messages ordered by sent time
$sqlMessages = "SELECT sender, message, sent_at FROM chat_messages WHERE user_id = '$user_id' ORDER BY sent_at ASC";
$result = $conn->query($sqlMessages);

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'sender' => $row['sender'],
        'message' => $row['message'],
        'sent_at' => $row['sent_at']
    ];
}

echo json_encode(['success' => true, 'messages' => $messages]);

$conn->close();
?>
