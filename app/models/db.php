<?php

date_default_timezone_set('Asia/Colombo');
$host = "localhost";  // your host
$db = "u569550465_hireme";  // your database name
$user = "u569550465_math_rakusa";  // your db user
$pass = "Sithija2025#";  // your db password

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
} // your DB connection with $host, $user, $pass, $db

session_start();
?>
