<?php
header('Content-Type: application/json');
error_reporting(0);

// Database configuration
$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

// Function to send confirmation email to worker
function sendConfirmationEmail($email, $fullName, $jobTitle, $jobLocation, $jobDate, $jobContact) {
    $subject = "HireMe Job Confirmation - $jobTitle";
    $headers = "From: no-reply@hireme.cpsharetxt.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    $message = "
    <html>
    <body>
        <p>Dear <strong>$fullName</strong>,</p>
        <p>Congratulations! You have been selected for the following job:</p>
        <table>
            <tr><td><strong>Job Title:</strong></td><td>$jobTitle</td></tr>
            <tr><td><strong>Location:</strong></td><td>$jobLocation</td></tr>
            <tr><td><strong>Date:</strong></td><td>$jobDate</td></tr>
            <tr><td><strong>Contact:</strong></td><td>$jobContact</td></tr>
        </table>
        <p>Please be prepared and arrive on time.</p>
        <p>Thank you for using <strong>Hire Us</strong>!</p>
    </body>
    </html>
    ";

    return mail($email, $subject, $message, $headers);
}

// Get POST data
$jobId = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
$idNumbers = isset($_POST['id_numbers']) ? $_POST['id_numbers'] : [];

if ($jobId <= 0 || empty($idNumbers) || !is_array($idNumbers)) {
    echo json_encode(["success" => false, "message" => "Invalid job ID or worker list"]);
    exit();
}

// Get job vacancy count
$vacancyResult = $conn->query("SELECT vacancies FROM jobs WHERE id = $jobId LIMIT 1");
if (!$vacancyResult || $vacancyResult->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Job not found"]);
    exit();
}
$vacancies = (int)$vacancyResult->fetch_assoc()['vacancies'];

// Get current number of hired workers
$countResult = $conn->query("SELECT COUNT(*) AS count FROM job_hires WHERE job_id = $jobId");
$currentHires = $countResult && $countResult->num_rows > 0 ? (int)$countResult->fetch_assoc()['count'] : 0;

// Prepare statements
$stmtCheckWorker = $conn->prepare("SELECT status FROM workers WHERE idNumber = ?");
$stmtInsertHire = $conn->prepare("INSERT IGNORE INTO job_hires (job_id, id_number) VALUES (?, ?)");
$stmtUpdateStatus = $conn->prepare("UPDATE workers SET status = 'hired' WHERE idNumber = ?");

$skipped = [];
$hiredCount = 0;

$conn->begin_transaction();

try {
    foreach ($idNumbers as $idNumber) {
        // Check if worker exists and their current status
        $stmtCheckWorker->bind_param("s", $idNumber);
        $stmtCheckWorker->execute();
        $result = $stmtCheckWorker->get_result();

        if ($result->num_rows == 0) {
            $skipped[] = "$idNumber (worker not found)";
            continue;
        }

        $status = $result->fetch_assoc()['status'];
        if ($status === 'hired') {
            $skipped[] = "$idNumber (already hired)";
            continue;
        }

        if (($currentHires + $hiredCount + 1) > $vacancies) {
            $skipped[] = "$idNumber (no remaining vacancies)";
            continue;
        }

        // Insert into job_hires
        $stmtInsertHire->bind_param("is", $jobId, $idNumber);
        $stmtInsertHire->execute();

        // Update status to 'hired'
        $stmtUpdateStatus->bind_param("s", $idNumber);
        $stmtUpdateStatus->execute();

        // Get worker email & full name
        $workerQuery = $conn->query("SELECT fullName, email FROM workers WHERE idNumber = '$idNumber' LIMIT 1");
        $workerData = $workerQuery->fetch_assoc();
        $fullName = $workerData['fullName'];
        $email = $workerData['email'];

        // Get job details
        $jobQuery = $conn->query("SELECT * FROM jobs WHERE id = $jobId LIMIT 1");
        $jobData = $jobQuery->fetch_assoc();
        $jobTitle = $jobData['job_title'];
        $jobLocation = $jobData['location'];
        $jobDate = $jobData['job_date'];
        $jobContact = $jobData['contact_info'];

        // Send confirmation email
        sendConfirmationEmail($email, $fullName, $jobTitle, $jobLocation, $jobDate, $jobContact);

        $hiredCount++;
    }

    $conn->commit();

    echo json_encode([
        "success" => true,
        "message" => "$hiredCount worker(s) hired successfully",
        "skipped" => $skipped,
        "remaining_vacancies" => max(0, $vacancies - $currentHires - $hiredCount)
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        "success" => false,
        "message" => "Transaction failed: " . $e->getMessage()
    ]);
}

// Close resources
$stmtCheckWorker->close();
$stmtInsertHire->close();
$stmtUpdateStatus->close();
$conn->close();
?>
