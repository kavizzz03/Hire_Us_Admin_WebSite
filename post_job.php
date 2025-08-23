<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check DB connection
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "DB connection failed"]);
    exit;
}

// Get POST data
$job_title = $_POST['job_title'] ?? '';
$vacancies = $_POST['vacancies'] ?? '';
$time = $_POST['time'] ?? '';
$location = $_POST['location'] ?? '';
$basic_salary = $_POST['basic_salary'] ?? '';
$ot_salary = $_POST['ot_salary'] ?? '';
$requirements = $_POST['requirements'] ?? '';
$job_date = $_POST['job_date'] ?? '';
$pickup_location = $_POST['pickup_location'] ?? '';
$contact_info = $_POST['contact_info'] ?? '';
$email = $_POST['email'] ?? '';
$employee_id = $_POST['employee_id'] ?? '';

// Validation
if (
    empty($job_title) || empty($vacancies) || empty($time) || empty($location) ||
    empty($basic_salary) || empty($job_date) || empty($contact_info) || empty($employee_id)
) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

// Reformat job_date
$dateObject = date_create($job_date);
if ($dateObject) {
    $job_date = date_format($dateObject, 'Y-m-d');
} else {
    echo json_encode(["status" => "error", "message" => "Invalid job date format"]);
    exit;
}

// Prepare insert
$stmt = $conn->prepare("INSERT INTO jobs (
    job_title, vacancies, time_range, location, basic_salary, ot_salary, requirements,
    job_date, pickup_location, contact_info, email, employee_id
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Statement prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("sissddsssssi",
    $job_title,
    $vacancies,
    $time,
    $location,
    $basic_salary,
    $ot_salary,
    $requirements,
    $job_date,
    $pickup_location,
    $contact_info,
    $email,
    $employee_id
);

if ($stmt->execute()) {
    // ✅ Success - send confirmation email if contact_info is a valid email
    if (filter_var($contact_info, FILTER_VALIDATE_EMAIL)) {
        $to = $contact_info;
        $subject = "Job Confirmation - Hire Us";
        $message = "
        Hello,

        Your job post has been successfully submitted.

        Job Details:
        ------------------------
        Title: $job_title
        Vacancies: $vacancies
        Time: $time
        Location: $location
        Basic Salary: Rs. $basic_salary
        OT Salary: Rs. $ot_salary
        Requirements: $requirements
        Job Date: $job_date
        Pickup Location: $pickup_location

        Thank you for using Hire Me.

        - Hire Us Team
        ";
        $headers = "From: no-reply@hireme.cpsharetxt.com\r\n";

        // Send the email
        mail($to, $subject, $message, $headers);
    }

    echo json_encode(["status" => "success", "message" => "Job posted successfully and email sent (if valid)."]);
} else {
    echo json_encode(["status" => "error", "message" => "Insert failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
