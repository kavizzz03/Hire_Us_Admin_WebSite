<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=deleted_workers_report.csv');

$output = fopen('php://output', 'w');

// Define CSV headers matching all the fields you want to export
$headers = [
    'ID',
    'Full Name',
    'Username',
    'Contact Number',
    'Email',
    'ID Number',
    'Permanent Address',
    'Current Address',
    'Work Experience',
    'Job Title',
    'Password (hashed)',
    'Bank Account Number',
    'Bank Name',
    'Bank Branch',
    'ID Front Image Filename',
    'ID Back Image Filename',
    'Created At',
    'Reset Token',
    'Status',
    'Deleted At'
];
fputcsv($output, $headers);

// Fetch all deleted workers data ordered by deletion date
$sql = "SELECT * FROM deleted_users_log ORDER BY deleted_at DESC";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['fullName'],
            $row['username'],
            $row['contactNumber'],
            $row['email'],
            $row['idNumber'],
            $row['permanentAddress'],
            $row['currentAddress'],
            $row['workExperience'],
            $row['jobTitle'],
            $row['password'],
            $row['bankAccountNumber'],
            $row['bankName'],
            $row['bankBranch'],
            $row['idFrontImage'],
            $row['idBackImage'],
            $row['created_at'],
            $row['reset_token'],
            $row['status'],
            $row['deleted_at']
        ]);
    }
} else {
    // If query fails, output an error row for clarity
    fputcsv($output, ['Error fetching data']);
}

fclose($output);
exit();
