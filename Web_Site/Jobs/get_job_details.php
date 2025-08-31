<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername,$username,$password,$dbname);
if($conn->connect_error) die("Connection failed: ".$conn->connect_error);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT * FROM jobs WHERE id = $id";
$result = $conn->query($sql);

if($result && $result->num_rows>0){
    $job = $result->fetch_assoc();
} else {
    die("Job not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Job Details</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="icon" type="image/png" href="icon2.png">
<style>
body{font-family:'Segoe UI',sans-serif; padding:20px;}
h2{text-align:center; margin-bottom:30px;}
table{width:100%;}
</style>
</head>
<body>
<h2>Job Details: <?= htmlspecialchars($job['job_title']) ?></h2>
<table class="table table-bordered table-striped">
<tr><th>Job ID</th><td><?= $job['id'] ?></td></tr>
<tr><th>Job Title</th><td><?= $job['job_title'] ?></td></tr>
<tr><th>Vacancies</th><td><?= $job['vacancies'] ?></td></tr>
<tr><th>Time Range</th><td><?= $job['time_range'] ?></td></tr>
<tr><th>Location</th><td><?= $job['location'] ?></td></tr>
<tr><th>Basic Salary</th><td><?= $job['basic_salary'] ?></td></tr>
<tr><th>OT Salary</th><td><?= $job['ot_salary'] ?></td></tr>
<tr><th>Requirements</th><td><?= $job['requirements'] ?></td></tr>
<tr><th>Job Date</th><td><?= $job['job_date'] ?></td></tr>
<tr><th>Pickup Location</th><td><?= $job['pickup_location'] ?></td></tr>
<tr><th>Contact Info</th><td><?= $job['contact_info'] ?></td></tr>
<tr><th>Email</th><td><?= $job['email'] ?></td></tr>
<tr><th>Employee ID</th><td><?= $job['employee_id'] ?></td></tr>
<tr><th>Created At</th><td><?= $job['created_at'] ?></td></tr>
</table>
<div class="text-center mt-4">
<button onclick="window.print()" class="btn btn-primary">Print Report</button>
</div>
</body>
</html>
