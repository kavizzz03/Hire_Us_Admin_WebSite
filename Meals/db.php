<?php
$DB_HOST = 'localhost';
$DB_USER = 'u569550465_math_rakusa';
$DB_PASS = 'Sithija2025#';
$DB_NAME = 'u569550465_hireme';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
