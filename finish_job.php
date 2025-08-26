<?php
// finish_job.php

$servername = "localhost";
$username   = "u569550465_math_rakusa";
$password   = "Sithija2025#";
$dbname     = "u569550465_hireme";

header('Content-Type: application/json');

// Validate inputs
if (!isset($_POST['job_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing job_id']);
    exit;
}

$job_id   = intval($_POST['job_id']);
$ot_hours = isset($_POST['ot_hours']) ? floatval($_POST['ot_hours']) : 0.0;

// Set timezone to Colombo, Sri Lanka
date_default_timezone_set("Asia/Colombo");
$current_time = date("Y-m-d H:i:s");

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'DB connection failed: ' . $conn->connect_error]);
    exit;
}

$conn->set_charset("utf8mb4");
$conn->begin_transaction();

try {
    // 1. Delete all job applications for this job
    $stmt = $conn->prepare("DELETE FROM job_applications WHERE job_id = ?");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $stmt->close();

    // 2. Get all hired workers for this job
    $stmt = $conn->prepare("SELECT id_number FROM job_hires WHERE job_id = ?");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $hiredWorkers = [];
    while ($row = $result->fetch_assoc()) {
        $hiredWorkers[] = $row['id_number'];
    }
    $stmt->close();

    foreach ($hiredWorkers as $workerIdNumber) {
        // 3. Update worker status to 'not hired'
        $stmt = $conn->prepare("UPDATE workers SET status = 'not hired' WHERE idNumber = ?");
        $stmt->bind_param("s", $workerIdNumber);
        $stmt->execute();
        $stmt->close();

        // 4. Send email notification to worker
        $stmt = $conn->prepare("SELECT email, fullName FROM workers WHERE idNumber = ?");
        $stmt->bind_param("s", $workerIdNumber);
        $stmt->execute();
        $res        = $stmt->get_result();
        $workerData = $res->fetch_assoc();
        $stmt->close();

        if ($workerData) {
            $to      = $workerData['email'];
            $name    = $workerData['fullName'];
            $subject = "Job Completed Notification";
            $message = "Dear $name,\n\nYour job with ID $job_id has been completed. Thank you for your work.\n\nRegards,\nHire Us Team";
            $headers = "From: no-reply@hireus.com";

            @mail($to, $subject, $message, $headers);
        }

        // 5. Insert salary, OT info, and credit status into vault
        $stmt = $conn->prepare("SELECT basic_salary, ot_salary FROM jobs WHERE id = ?");
        $stmt->bind_param("i", $job_id);
        $stmt->execute();
        $resJob  = $stmt->get_result();
        $jobData = $resJob->fetch_assoc();
        $stmt->close();

        if ($jobData) {
            $baseSalary    = floatval($jobData['basic_salary']);
            $otHourlyRate  = floatval($jobData['ot_salary']);
            $totalSalary   = $baseSalary + ($ot_hours * $otHourlyRate);
            $transactionType = "credit"; // Always credit when finishing job

            $stmt = $conn->prepare("
                INSERT INTO vault 
                (idNumber, job_id, salary, ot_hours, ot_salary, transaction_type, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("siiddss", $workerIdNumber, $job_id, $totalSalary, $ot_hours, $otHourlyRate, $transactionType, $current_time);
            $stmt->execute();
            $stmt->close();
        }
    }

    // 6. Delete job hires for this job
    $stmt = $conn->prepare("DELETE FROM job_hires WHERE job_id = ?");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $stmt->close();

    // 7. Delete meals related to this job
    $stmt = $conn->prepare("DELETE FROM meals WHERE job_id = ?");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $stmt->close();

    // 8. Delete the job itself
    $stmt = $conn->prepare("DELETE FROM jobs WHERE id = ?");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    echo json_encode(['status' => 'success']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Transaction failed: ' . $e->getMessage()]);
}

$conn->close();
?>
