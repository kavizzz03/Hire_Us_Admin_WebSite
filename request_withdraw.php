<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials
$servername = "localhost";
$username   = "u569550465_math_rakusa";
$password   = "Sithija2025#";
$dbname     = "u569550465_hireme";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Get and validate POST input
$idNumber = $_POST['id_number'] ?? null;
$jobId    = $_POST['job_id'] ?? null;
$amount   = $_POST['amount'] ?? null;

if (!$idNumber || !$jobId || !$amount) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit();
}

if (!is_numeric($jobId) || !is_numeric($amount)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid data type for job_id or amount"]);
    exit();
}

// Check for existing pending withdraw request
$checkStmt = $conn->prepare("SELECT id FROM withdraw_requests WHERE idNumber = ? AND job_id = ? AND status = 'pending' LIMIT 1");
$checkStmt->bind_param("si", $idNumber, $jobId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    http_response_code(409);
    echo json_encode(["success" => "You already have a pending withdrawal request for this job."]);
    $checkStmt->close();
    $conn->close();
    exit();
}
$checkStmt->close();

// Get current datetime in Asia/Colombo
try {
    $dateTimeColombo = new DateTime("now", new DateTimeZone('Asia/Colombo'));
    $requestedAt = $dateTimeColombo->format('Y-m-d H:i:s');
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to get Colombo time"]);
    exit();
}

// Insert new withdraw request
$stmt = $conn->prepare("INSERT INTO withdraw_requests (idNumber, job_id, amount, requested_at, status) VALUES (?, ?, ?, ?, 'pending')");
$stmt->bind_param("sids", $idNumber, $jobId, $amount, $requestedAt);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to save withdraw request: " . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();

// ✅ Update vault table: mark as finished
$updateVault = $conn->prepare("UPDATE vault SET status = 'finished' WHERE idNumber = ? AND job_id = ? AND status = 'pending'");
$updateVault->bind_param("si", $idNumber, $jobId);
$updateVault->execute();
$updateVault->close();

// Get employer email from deleted_jobs
$query = $conn->prepare("
    SELECT e.email, e.company_name
    FROM employers e
    JOIN deleted_jobs j ON e.id = j.employee_id
    WHERE j.job_id = ?
");
$query->bind_param("i", $jobId);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["warning" => "Request saved and vault updated but Job Maker email not found"]);
    $query->close();
    $conn->close();
    exit();
}

$row = $result->fetch_assoc();
$query->close();

$to = $row['email'];
$companyName = $row['company_name'] ?? "Employer";

// Prepare email
$subject = "New Withdrawal Request for Job ID: $jobId";
$message = "Dear $companyName,

You have a new withdrawal request from worker ID: $idNumber.

Requested Amount: Rs. $amount
Job ID: $jobId
Requested At: $requestedAt

The vault entry has been marked as finished.

Please log in to your dashboard to review and approve the request.

Regards,
Job Maker Team";

$headers = "From: noreply@yourdomain.com\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Send email
if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
    if (mail($to, $subject, $message, $headers)) {
        echo json_encode(["success" => "Request saved, vault updated, and email sent"]);
    } else {
        echo json_encode(["warning" => "Request saved, vault updated but failed to send email"]);
    }
} else {
    echo json_encode(["warning" => "Request saved, vault updated but invalid employer email"]);
}

$conn->close();
?>
