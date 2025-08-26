<?php
header('Content-Type: application/json');

// Database connection
$conn = new mysqli("localhost", "u569550465_math_rakusa", "Sithija2025#", "u569550465_hireme");
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "DB connection failed"]);
    exit;
}

// Validate input
$job_id = $_POST['job_id'] ?? null;
$rating = $_POST['rating'] ?? null;
$feedback = $_POST['feedback'] ?? '';
$work_experience = $_POST['work_experience'] ?? '';
$rated_by = $_POST['rated_by'] ?? 'Employer';

if (!$job_id || !$rating) {
    echo json_encode(["status" => "error", "message" => "Job ID and rating are required"]);
    exit;
}

// Fetch all hired workers for this job
$sql_workers = "SELECT id_number FROM job_hires WHERE job_id = ?";
$stmt = $conn->prepare($sql_workers);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "No workers found for this job"]);
    exit;
}

$updated = 0;

while ($row = $result->fetch_assoc()) {
    $id_number = $row['id_number'];

    // Get worker ID
    $sql_worker = "SELECT id, jobTitle FROM workers WHERE idNumber = ?";
    $stmt_worker = $conn->prepare($sql_worker);
    $stmt_worker->bind_param("s", $id_number);
    $stmt_worker->execute();
    $worker_data = $stmt_worker->get_result()->fetch_assoc();

    if (!$worker_data) continue;

    $worker_id = $worker_data['id'];
    $worker_job_title = $worker_data['jobTitle'];

    // Get job details for reference
    $sql_job = "SELECT j.job_title, e.company_name FROM jobs j 
                JOIN employers e ON e.id = j.employee_id WHERE j.id = ?";
    $stmt_job = $conn->prepare($sql_job);
    $stmt_job->bind_param("i", $job_id);
    $stmt_job->execute();
    $job_data = $stmt_job->get_result()->fetch_assoc();

    $job_title = $job_data['job_title'] ?? '';
    $company_name = $job_data['company_name'] ?? '';

    // Check if rating already exists
    $sql_check = "SELECT id FROM worker_ratings WHERE worker_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $worker_id);
    $stmt_check->execute();
    $check_result = $stmt_check->get_result();

    if ($check_result->num_rows > 0) {
        // Update existing rating
        $sql_update = "UPDATE worker_ratings 
                       SET rated_by = ?, rating = ?, work_experience = ?, feedback = ?, job_title = ?, company_name = ?, created_at = NOW()
                       WHERE worker_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sissssi", $rated_by, $rating, $work_experience, $feedback, $job_title, $company_name, $worker_id);
        if ($stmt_update->execute()) $updated++;
    } else {
        // Insert new rating
        $sql_insert = "INSERT INTO worker_ratings 
                       (worker_id, rated_by, rating, work_experience, feedback, job_title, company_name, created_at) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("isissss", $worker_id, $rated_by, $rating, $work_experience, $feedback, $job_title, $company_name);
        if ($stmt_insert->execute()) $updated++;
    }
}

echo json_encode([
    "status" => "success",
    "message" => "$updated worker ratings updated successfully"
]);

$conn->close();
?>
