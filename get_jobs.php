<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

$sql = "SELECT 
            j.id,
            j.job_title,
            j.vacancies,
            j.time_range,
            j.location,
            j.basic_salary,
            j.ot_salary,
            j.requirements,
            j.job_date,
            j.pickup_location,
            j.contact_info,
            j.email,
            e.company_name
        FROM jobs j
        JOIN employers e ON j.employee_id = e.id
        ORDER BY j.created_at DESC";

$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to execute query"]);
    $conn->close();
    exit();
}

if ($result->num_rows == 0) {
    echo json_encode([]);
    $conn->close();
    exit();
}

$jobs = [];
while ($row = $result->fetch_assoc()) {
    $jobs[] = [
        "id" => (int)$row['id'],
        "job_title" => $row['job_title'],
        "vacancies" => $row['vacancies'],
        "time_range" => $row['time_range'],
        "location" => $row['location'],
        "basic_salary" => $row['basic_salary'],
        "ot_salary" => $row['ot_salary'],
        "requirements" => $row['requirements'],
        "job_date" => $row['job_date'],
        "pickup_location" => $row['pickup_location'],
        "contact_info" => $row['contact_info'],
        "email" => $row['email'],
        "company_name" => $row['company_name']
    ];
}

echo json_encode($jobs);

$conn->close();
?>
