<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
    exit();
}

$idNumber = $_POST['id_number'] ?? '';
$email = $_POST['email'] ?? '';

if (empty($idNumber) || empty($email)) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter both ID number and email']);
    exit();
}

// Check if account exists
$sql = "SELECT * FROM workers WHERE idNumber=? AND email=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $idNumber, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $token = bin2hex(random_bytes(32));
    $update = $conn->prepare("UPDATE workers SET reset_token=? WHERE idNumber=?");
    $update->bind_param("ss", $token, $idNumber);
    $update->execute();

    $resetLink = "https://hireme.cpsharetxt.com/reset_password_page_emp.php?token=$token";

    // Styled HTML email content
    $subject = "🔐 Password Reset Request - Hire Me Platform";

    $message = "
    <html>
    <head>
        <title>Password Reset - Hire Me</title>
    </head>
    <body style='font-family: Arial, sans-serif; color: #333; background-color: #f9f9f9; padding: 20px;'>
        <div style='max-width: 600px; margin: auto; background-color: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);'>
            <h2 style='color: #fbc02d;'>Hire Me - Password Reset Request</h2>
            <p>Dear User,</p>
            <p>We received a request to reset your password for the <strong>Hire Me</strong> account linked to <strong>$email</strong>.</p>
            <p>Click the button below to reset your password. This link will expire in 30 minutes.</p>
            <p style='text-align: center; margin: 30px 0;'>
                <a href='$resetLink' style='background-color: #fbc02d; color: #000; padding: 12px 20px; border-radius: 6px; text-decoration: none; font-weight: bold;'>Reset Password</a>
            </p>
            <p>If you didn’t request this, please ignore this email. Your password will remain unchanged.</p>
            <br>
            <p>Thanks,<br><strong>Hire Me Support Team</strong></p>
            <hr style='border: none; border-top: 1px solid #eee; margin-top: 30px;'>
            <p style='font-size: 12px; color: #777;'>Need help? Contact us at <a href='mailto:support@hireme.cpsharetxt.com'>support@hireme.cpsharetxt.com</a></p>
        </div>
    </body>
    </html>";

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Hire Me <no-reply@hireme.cpsharetxt.com>" . "\r\n";

    if (mail($email, $subject, $message, $headers)) {
        echo json_encode(['status' => 'success', 'message' => 'Reset link has been sent to your email. Please check your inbox.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Email sending failed. Please try again later.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No user found with provided ID number and email.']);
}

$stmt->close();
$conn->close();
?>
