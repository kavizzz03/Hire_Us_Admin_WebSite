<?php
// Database connection info
$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['email'])) {
    $email = $_POST['email'];

    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM employers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generate secure token & expiry (30 min)
        $token = bin2hex(random_bytes(16));
        $expiry = date("Y-m-d H:i:s", strtotime("+30 minutes"));

        // Save token & expiry in DB
        $update = $conn->prepare("UPDATE employers SET reset_token = ?, token_expiry = ? WHERE email = ?");
        $update->bind_param("sss", $token, $expiry, $email);
        $update->execute();

        // Prepare reset link (update domain if needed)
        $resetLink = "https://hireme.cpsharetxt.com/reset_password.php?token=$token";

        // Email content
        $subject = "HireMe Password Reset Request";
        $message = "Hello,\n\n";
        $message .= "We received a request to reset your password. Click the link below to set a new password:\n\n";
        $message .= "$resetLink\n\n";
        $message .= "This link will expire in 30 minutes.\n\n";
        $message .= "If you did not request this, please ignore this email.\n\n";
        $message .= "Thanks,\nHireMe Team";

        $headers = "From: noreply@hireme.cpsharetxt.com\r\n";
        $headers .= "Reply-To: noreply@hireme.cpsharetxt.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        if (mail($email, $subject, $message, $headers)) {
            echo "success";
        } else {
            echo "email_failed";
        }
    } else {
        echo "not_found";
    }
} else {
    echo "invalid_request";
}
?>
