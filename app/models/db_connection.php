<?php
$host = "localhost"; // or your Hostinger DB host (e.g., "mysql.hostinger.com")
$db_name = "u569550465_hireme";
$username1 = "u569550465_math_rakusa";
$password1 = "Sithija2025#";

$conn = new mysqli($host, $username1, $password1, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
