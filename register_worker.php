<?php
// For debugging - remove on production
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Database credentials
$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

// Function to send email
function sendConfirmationEmail($toEmail, $fullName) {
    $subject = "Registration Successful - Hire Me";
    $message = "Dear $fullName,\n\nThank you for registering with Hire Me.\nYour account has been created successfully.\n\nRegards,\nHire Me Team";
    $headers = "From: noreply@hireme.com";

    return mail($toEmail, $subject, $message, $headers);
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect POST data
    $fullName = $conn->real_escape_string($_POST['fullName'] ?? '');
    $username = $conn->real_escape_string($_POST['username'] ?? '');
    $contactNumber = $conn->real_escape_string($_POST['contactNumber'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $idNumber = $conn->real_escape_string($_POST['idNumber'] ?? '');
    $permanentAddress = $conn->real_escape_string($_POST['permanentAddress'] ?? '');
    $currentAddress = $conn->real_escape_string($_POST['currentAddress'] ?? '');
    $workExperience = $conn->real_escape_string($_POST['workExperience'] ?? '');
    $password = $_POST['password'] ?? '';
    $bankAccountNumber = $conn->real_escape_string($_POST['bankAccountNumber'] ?? '');
    $bankName = $conn->real_escape_string($_POST['bankName'] ?? '');
    $bankBranch = $conn->real_escape_string($_POST['bankBranch'] ?? '');

    // Validate required fields
    if (!$fullName || !$username || !$contactNumber || !$email || !$idNumber || !$permanentAddress || !$workExperience || !$password || !$bankAccountNumber || !$bankName || !$bankBranch) {
        echo json_encode(["status" => "error", "message" => "Please fill in all required fields."]);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format."]);
        exit;
    }

    // Check if email already exists
    $emailCheck = $conn->query("SELECT id FROM workers WHERE email='$email'");
    if ($emailCheck->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Email already registered."]);
        exit;
    }

    // Hash the password securely
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Handle file uploads for idFront and idBack
    $uploadDir = __DIR__ . '/uploads_employee/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $idFrontImageName = null;
    $idBackImageName = null;

    if (!empty($_FILES['idFront']['name'])) {
        $idFrontTmpName = $_FILES['idFront']['tmp_name'];
        $idFrontImageName = uniqid('front_') . "_" . basename($_FILES['idFront']['name']);
        $uploadFrontPath = $uploadDir . $idFrontImageName;
        if (!move_uploaded_file($idFrontTmpName, $uploadFrontPath)) {
            echo json_encode(["status" => "error", "message" => "Failed to upload front ID image."]);
            exit;
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Front ID image is required."]);
        exit;
    }

    if (!empty($_FILES['idBack']['name'])) {
        $idBackTmpName = $_FILES['idBack']['tmp_name'];
        $idBackImageName = uniqid('back_') . "_" . basename($_FILES['idBack']['name']);
        $uploadBackPath = $uploadDir . $idBackImageName;
        if (!move_uploaded_file($idBackTmpName, $uploadBackPath)) {
            echo json_encode(["status" => "error", "message" => "Failed to upload back ID image."]);
            exit;
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Back ID image is required."]);
        exit;
    }

    // Prepare and execute insert
    $stmt = $conn->prepare("INSERT INTO workers 
        (fullName, username, contactNumber, email, idNumber, permanentAddress, currentAddress, workExperience, password, bankAccountNumber, bankName, bankBranch, idFrontImage, idBackImage) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "ssssssssssssss",
        $fullName,
        $username,
        $contactNumber,
        $email,
        $idNumber,
        $permanentAddress,
        $currentAddress,
        $workExperience,
        $passwordHash,
        $bankAccountNumber,
        $bankName,
        $bankBranch,
        $idFrontImageName,
        $idBackImageName
    );

    if ($stmt->execute()) {
        // Send confirmation email
        sendConfirmationEmail($email, $fullName);

        echo json_encode(["status" => "success", "message" => "Registration successful"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to save data: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
