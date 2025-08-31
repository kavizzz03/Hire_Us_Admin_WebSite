<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$fullName = $_POST['fullName'] ?? '';
$contactNumber = $_POST['contactNumber'] ?? '';
$jobTitle = $_POST['jobTitle'] ?? '';
$status = $_POST['status'] ?? '';

$stmt = $conn->prepare("UPDATE workers SET fullName=?, contactNumber=?, jobTitle=?, status=? WHERE id=?");
$stmt->bind_param("ssssi", $fullName, $contactNumber, $jobTitle, $status, $id);

if ($stmt->execute()) {
    header("Location: workers.php");
    exit;
} else {
    echo "Error updating record: " . $stmt->error;
}
?>
