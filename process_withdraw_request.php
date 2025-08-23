<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['status'=>'error','message'=>$conn->connect_error]));
}

$withdraw_id = $_POST['withdraw_id'] ?? '';
if(empty($withdraw_id)){
    echo json_encode(['status'=>'error','message'=>'Withdraw ID required']);
    exit;
}

// Get withdraw request
$stmt = $conn->prepare("SELECT * FROM withdraw_requests WHERE id=?");
$stmt->bind_param("i", $withdraw_id);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows == 0){
    echo json_encode(['status'=>'error','message'=>'Withdraw request not found']);
    exit;
}

$request = $res->fetch_assoc();
$idNumber = $request['idNumber'];
$job_id = $request['job_id'];
$amount = $request['amount'];

// Get worker email
$stmt = $conn->prepare("SELECT email, fullName FROM workers WHERE idNumber=?");
$stmt->bind_param("s", $idNumber);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows == 0){
    echo json_encode(['status'=>'error','message'=>'Worker not found']);
    exit;
}
$worker = $res->fetch_assoc();
$workerEmail = $worker['email'];
$workerName = $worker['fullName'];

// Begin transaction
$conn->begin_transaction();

try {
    // 1. Insert into vault
    $stmt = $conn->prepare("INSERT INTO vault (idNumber, job_id, salary, transaction_type) VALUES (?,?,?,?)");
    $type = 'debit';
    $stmt->bind_param("iids", $idNumber, $job_id, $amount, $type);
    $stmt->execute();

    // 2. Update withdraw_requests status
    $stmt = $conn->prepare("UPDATE withdraw_requests SET status='Completed' WHERE id=?");
    $stmt->bind_param("i", $withdraw_id);
    $stmt->execute();

    // 3. Delete withdraw_requests record
    $stmt = $conn->prepare("DELETE FROM withdraw_requests WHERE id=?");
    $stmt->bind_param("i", $withdraw_id);
    $stmt->execute();

    $conn->commit();

    // 4. Send Hire Me email to worker
    $subject = "Hire Us - Withdraw Request Completed";
    $message = "
    <html>
    <head>
        <title>Hire Us - Withdraw Request Completed</title>
    </head>
    <body>
        <p>Hi <strong>$workerName</strong>,</p>
        <p>We are pleased to inform you that your withdraw request of <strong>LKR $amount</strong> has been successfully processed.</p>
        <p>Please check your bank account to confirm the transaction.</p>
        <br>
        <p>Thank you for using <strong>Hire Me</strong>!</p>
        <p>Best regards,<br>Hire Us Team</p>
    </body>
    </html>
    ";

    // Set content-type for HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Hire Me <no-reply@hireme.com>" . "\r\n";

    mail($workerEmail, $subject, $message, $headers);

    echo json_encode(['status'=>'success','message'=>'Withdraw processed and worker notified']);

} catch(Exception $e){
    $conn->rollback();
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
?>
