<?php
header('Content-Type: application/json');
include 'db_config.php';

$idNumber = $_POST['id_number'];
$fullName = $_POST['fullName'];
$username = $_POST['username'];
$contactNumber = $_POST['contactNumber'];
$permanentAddress = $_POST['permanentAddress'];
$currentAddress = $_POST['currentAddress'];
$workExperience = $_POST['workExperience'];
$bankName = $_POST['bankName'];
$bankBranch = $_POST['bankBranch'];
$bankAccountNumber = $_POST['bankAccountNumber'];

// Get user's email to send confirmation
$emailQuery = "SELECT email FROM workers WHERE idNumber = ?";
$stmtEmail = $conn->prepare($emailQuery);
$stmtEmail->bind_param("s", $idNumber);
$stmtEmail->execute();
$resultEmail = $stmtEmail->get_result();

if ($resultEmail->num_rows == 0) {
    echo json_encode(["success" => false, "error" => "User not found"]);
    exit();
}

$userData = $resultEmail->fetch_assoc();
$userEmail = $userData['email'];

$sql = "UPDATE workers SET 
            fullName = ?, username = ?, contactNumber = ?, permanentAddress = ?, 
            currentAddress = ?, workExperience = ?, bankName = ?, 
            bankBranch = ?, bankAccountNumber = ?
        WHERE idNumber = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssssss", $fullName, $username, $contactNumber, $permanentAddress, $currentAddress, $workExperience, $bankName, $bankBranch, $bankAccountNumber, $idNumber);

if ($stmt->execute()) {
    // Prepare email
    $subject = "Profile Update Confirmation - HireUs";
    $message = "Hello " . htmlspecialchars($fullName) . ",\n\n" .
               "Your profile on HireUs has been successfully updated.\n\n" .
               "If you did not make this change, please contact support immediately.\n\n" .
               "Thank you,\nHireUs Team";

    $fromEmail = "no-reply@hireme.cpsharetxt.com";
    $headers = "From: HireUs Team <" . filter_var($fromEmail, FILTER_SANITIZE_EMAIL) . ">\r\n" .
               "Reply-To: support@hireme.cpsharetxt.com\r\n" .
               "X-Mailer: PHP/" . phpversion();

    // Send mail only if email is valid
    if (filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        @mail($userEmail, $subject, $message, $headers);
    }

    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$stmtEmail->close();
$conn->close();
?>
