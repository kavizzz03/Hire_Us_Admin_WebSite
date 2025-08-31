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

$id = isset($_GET['id']) ? $conn->real_escape_string($_GET['id']) : '';

$sql = "SELECT * FROM workers WHERE idNumber COLLATE utf8mb4_unicode_ci = '$id'";
$result = $conn->query($sql);

if($result && $result->num_rows>0){
    $worker = $result->fetch_assoc();
} else {
    die("Worker not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Worker Details</title>
<link rel="icon" type="image/png" href="icon2.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{font-family:'Segoe UI',sans-serif; padding:20px;}
h2{text-align:center; margin-bottom:30px;}
table{width:100%;}
</style>
</head>
<body>
<h2>Worker Details: <?= htmlspecialchars($worker['fullName']) ?></h2>
<table class="table table-bordered table-striped">
<tr><th>ID Number</th><td><?= $worker['idNumber'] ?></td></tr>
<tr><th>Full Name</th><td><?= $worker['fullName'] ?></td></tr>
<tr><th>Username</th><td><?= $worker['username'] ?></td></tr>
<tr><th>Contact Number</th><td><?= $worker['contactNumber'] ?></td></tr>
<tr><th>Email</th><td><?= $worker['email'] ?></td></tr>
<tr><th>Permanent Address</th><td><?= $worker['permanentAddress'] ?></td></tr>
<tr><th>Current Address</th><td><?= $worker['currentAddress'] ?></td></tr>
<tr><th>Job Title</th><td><?= $worker['jobTitle'] ?></td></tr>
<tr><th>Work Experience</th><td><?= $worker['workExperience'] ?></td></tr>
<tr><th>Bank Name / Account</th><td><?= $worker['bankName'].' / '.$worker['bankAccountNumber'] ?></td></tr>
<tr><th>ID Front</th><td><img src="<?= $worker['idFrontImage'] ?>" width="150"></td></tr>
<tr><th>ID Back</th><td><img src="<?= $worker['idBackImage'] ?>" width="150"></td></tr>
<tr><th>Status</th><td><?= $worker['status'] ?></td></tr>
<tr><th>Created At</th><td><?= $worker['created_at'] ?></td></tr>
</table>
<div class="text-center mt-4">
<button onclick="window.print()" class="btn btn-primary">Print Report</button>
</div>
</body>
</html>
