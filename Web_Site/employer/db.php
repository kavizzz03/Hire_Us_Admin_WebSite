<?php
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);

date_default_timezone_set('Asia/Colombo');

$servername = "localhost";
$db_username = "u569550465_math_rakusa";
$db_password = "Sithija2025#";
$db_name = "u569550465_hireme";

$conn = new mysqli($servername, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
