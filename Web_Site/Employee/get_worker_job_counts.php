<?php
header('Content-Type: application/json');

// DB Connection
$conn = new mysqli("localhost", "u569550465_math_rakusa", "Sithija2025#", "u569550465_hireme");
if ($conn->connect_error) {
    die(json_encode(['error' => 'DB connection failed']));
}

// Query: count jobs per worker from job_hires table
$sql = "SELECT w.fullName, COUNT(jh.job_id) AS jobCount
        FROM job_hires_log jh
        JOIN workers w ON jh.id_number = w.id_number
        GROUP BY jh.id_number";

$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'fullName' => $row['fullName'],
        'jobCount' => (int)$row['jobCount']
    ];
}

echo json_encode($data);
$conn->close();
