<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// DB connection details - update as needed
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

if ($result->num_rows > 0) {
    // Session exists
    echo json_encode(['success' => true, 'message' => 'Chat session already exists']);
} else {
    // Create new chat session
    $sqlInsert = "INSERT INTO chat_sessions (user_id, started_at) VALUES ('$user_id', NOW())";
    if ($conn->query($sqlInsert) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Chat session started']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create chat session']);
    }
}

$conn->close();
?>
