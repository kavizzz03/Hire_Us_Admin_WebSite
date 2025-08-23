<?php
header('Content-Type: application/json');

$host = "localhost";  // your host
$db = "u569550465_hireme";  // your database name
$user = "u569550465_math_rakusa";  // your db user
$pass = "Sithija2025#";  // your db password

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $company_name = $_POST['company_name'] ?? '';
    $name = $_POST['name'] ?? '';
    $address = $_POST['address'] ?? '';
    $email = $_POST['email'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
        exit();
    }

    $icon_path = "";

    if (isset($_FILES['company_icon']) && $_FILES['company_icon']['error'] == 0) {
        $target_dir = "uploads_company/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $fileName = time() . "_" . basename($_FILES["company_icon"]["name"]);
        $target_file = $target_dir . $fileName;

        if (move_uploaded_file($_FILES["company_icon"]["tmp_name"], $target_file)) {
            $icon_path = $target_file;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload company icon.']);
            exit();
        }
    }

    // Check if email already exists
    $stmt_check = $conn->prepare("SELECT id FROM employers WHERE email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already registered.']);
        $stmt_check->close();
        exit();
    }
    $stmt_check->close();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO employers (company_name, name, address, email, contact, company_icon, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $company_name, $name, $address, $email, $contact, $icon_path, $hashed_password);

    if ($stmt->execute()) {

        // Send confirmation email
        $to = $email;
        $subject = "Job Maker - Registration Successful";
        $message = "Dear $name,\n\n"
                 . "Thank you for registering your company \"$company_name\" with Job Maker.\n"
                 . "Your account has been created successfully.\n\n"
                 . "If you did not register, please contact us immediately.\n\n"
                 . "Best regards,\n"
                 . "Hire Us Team";

        $headers = "From: no-reply@yourdomain.com\r\n" .
                   "Reply-To: support@yourdomain.com\r\n" .
                   "X-Mailer: PHP/" . phpversion();

        @mail($to, $subject, $message, $headers);

        echo json_encode(['success' => true, 'message' => 'Registration successful, confirmation email sent.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed, please try again later.']);
    }

    $stmt->close();
    $conn->close();

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
