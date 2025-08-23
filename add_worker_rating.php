<?php
header('Content-Type: application/json');
date_default_timezone_set('Asia/Colombo');

$conn = new mysqli("localhost", "u569550465_math_rakusa", "Sithija2025#", "u569550465_hireme");
if ($conn->connect_error) {
    echo json_encode(["status"=>"error","message"=>"DB connection failed"]);
    exit;
}

// Receive ID number from Android
$idNumber = $_GET['id_number'] ?? '';
$rated_by = $_GET['rated_by'] ?? '';
$rating = $_GET['rating'] ?? '';
$experience = $_GET['work_experience'] ?? '';
$feedback = $_GET['feedback'] ?? '';
$job_title = $_GET['job_title'] ?? '';
$company_name = $_GET['company_name'] ?? '';
$duration = $_GET['duration'] ?? '';

if($idNumber == ''){
    echo json_encode(["status"=>"error","message"=>"ID number missing"]);
    exit;
}

// Get numeric worker_id and email from workers table
$stmt = $conn->prepare("SELECT id, email, fullName FROM workers WHERE idNumber = ?");
$stmt->bind_param("s", $idNumber);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    echo json_encode(["status"=>"error","message"=>"Worker not found"]);
    exit;
}

$row = $result->fetch_assoc();
$worker_id = $row['id'];
$email = $row['email'];  
$worker_name = $row['fullName'];

$created_at = date('Y-m-d H:i:s');

$insert = $conn->prepare("INSERT INTO worker_ratings 
(worker_id, rated_by, rating, work_experience, feedback, job_title, company_name, duration, created_at)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

$insert->bind_param("sisssssis", $worker_id, $rated_by, $rating, $experience, $feedback, $job_title, $company_name, $duration, $created_at);

if($insert->execute()){

    // Send email to worker
    $subject = "New Feedback in Hire Us System";
    $message = "Hello $worker_name,\n\nYou have received a new feedback in the Hire Us system.\n\n" .
               "From: $rated_by\n" .
               "Rating: $rating\n" .
               "Experience: $experience\n" .
               "Feedback: $feedback\n" .
               "Job: $job_title\n" .
               "Company: $company_name\n" .
               "Duration: $duration\n\n" .
               "Please log in to your account to view full details.\n\nBest regards,\nHire Us System";

    $headers = "From: no-reply@hireus.com";

    mail($email, $subject, $message, $headers);

    echo json_encode([
        "status"=>"success",
        "message"=>"Rating added and email sent",
        "worker_email"=>$email
    ]);
} else {
    echo json_encode(["status"=>"error","message"=>$insert->error]);
}

$insert->close();
$stmt->close();
$conn->close();
?>
