<?php
require 'db.php';

if(isset($_POST['submit'])){
    $company_name = $conn->real_escape_string($_POST['company_name']);
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $contact = $conn->real_escape_string($_POST['contact']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $conn->query("INSERT INTO employers (company_name, name, email, contact, password) VALUES ('$company_name','$name','$email','$contact','$password')");

    header("Location: employers_crud.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add New Employer</title>
<meta name="viewport" content="width=device-width, initial-scale=1"> <!-- Mobile scaling -->
<link rel="icon" type="image/png" href="icon2.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
body {
    background: linear-gradient(135deg, #f0f4f8, #d9e4f5);
    font-family: 'Segoe UI', sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 15px;
}
.card {
    border-radius: 20px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    overflow: hidden;
    width: 100%;
    max-width: 500px;
    background: #fff;
}
.card-header {
    background: linear-gradient(90deg, #28a745, #20c997);
    color: #fff;
    text-align: center;
    padding: 20px;
}
h2 {
    margin: 0;
    font-weight: 600;
    font-size: 1.6rem;
}
.form-control {
    border-radius: 10px;
    padding: 10px 15px;
}
.form-control:focus {
    box-shadow: 0 0 0 0.2rem rgba(40,167,69,.25);
}
.btn-custom {
    border-radius: 50px;
    padding: 12px 25px;
    font-weight: 500;
    transition: all 0.3s ease;
    width: 48%;
}
.btn-custom:hover {
    transform: translateY(-2px);
}
.btn-row {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
}
/* Mobile adjustments */
@media (max-width: 576px) {
    .card-header h2 {
        font-size: 1.4rem;
    }
    .btn-row {
        flex-direction: column;
    }
    .btn-row .btn-custom {
        width: 100%;
        margin-bottom: 10px;
    }
}
</style>
</head>
<body>

<div class="card animate__animated animate__fadeInUp">
    <div class="card-header">
        <h2>Add New Employer</h2>
    </div>
    <div class="card-body p-4">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Company Name</label>
                <input type="text" name="company_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Contact</label>
                <input type="text" name="contact" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="btn-row">
                <button type="submit" name="submit" class="btn btn-success btn-custom">Add Employer</button>
                <a href="employers_crud.php" class="btn btn-secondary btn-custom">Cancel</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
