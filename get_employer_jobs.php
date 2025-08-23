<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

if (!isset($_GET['employer_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing employer_id"]);
    exit();
}

$employer_id = intval($_GET['employer_id']);

$sql = "SELECT 
            j.id,
            j.job_title,
            e.company_name,
            j.vacancies,
            j.time_range,
            j.location,
            j.basic_salary,
            j.ot_salary,
            j.requirements,
            j.job_date,
            j.pickup_location,
            j.contact_info,
            j.email
        FROM jobs j
        INNER JOIN employers e ON j.employee_id = e.id
        WHERE j.employee_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employer_id);
$stmt->execute();
$result = $stmt->get_result();

$jobs = [];
while ($row = $result->fetch_assoc()) {
    $jobs[] = [
        "id" => (int)$row['id'],
        "jobTitle" => $row['job_title'],
        "companyName" => $row['company_name'],    // added companyName here
        "vacancies" => (string)$row['vacancies'],
        "timeRange" => $row['time_range'],
        "location" => $row['location'],
        "basicSalary" => (string)$row['basic_salary'],
        "otSalary" => (string)$row['ot_salary'],
        "requirements" => $row['requirements'],
        "jobDate" => $row['job_date'],
        "pickupLocation" => $row['pickup_location'],
        "contactInfo" => $row['contact_info'],
        "email" => $row['email']
    ];
}

echo json_encode($jobs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
