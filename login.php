<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// Database credentials
$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}

// Get POST data
$idNumber = $_POST['id_number'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($idNumber) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'ID number or password missing']);
    exit();
}

// Prepare and execute query
$sql = "SELECT * FROM workers WHERE idNumber = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $idNumber);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();

    // ✅ Verify hashed password
    if (password_verify($password, $row['password'])) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful',
            'id_number' => $row['idNumber'],
            'full_name' => $row['fullName'],
            'job_title' => $row['jobTitle']
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Incorrect password']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID number not found']);
}

$stmt->close();
$conn->close();
?>
