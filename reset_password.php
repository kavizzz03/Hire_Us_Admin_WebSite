<?php
$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$tokenValid = false;
$showForm = false;
$message = "";

// Check token validity
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT * FROM employers WHERE reset_token = ? AND token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $tokenValid = true;
        $showForm = true;
    } else {
        $message = "Invalid or expired password reset link.";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'], $_POST['password'])) {
    $token = $_POST['token'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $update = $conn->prepare("UPDATE employers SET password = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ?");
    $update->bind_param("ss", $password, $token);

    if ($update->execute()) {
        $message = "✅ Password updated successfully. You can now use New password.";
        $showForm = false;
    } else {
        $message = "❌ Failed to update password. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Reset Password | Hire Us</title>
<!-- Bootstrap CSS CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="card-title mb-4 text-center">Reset Your Password</h2>

                    <?php if ($message): ?>
                        <div class="alert <?php echo $showForm ? 'alert-danger' : 'alert-success'; ?>" role="alert">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($showForm): ?>
                    <form method="POST" action="reset_password.php" novalidate>
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>" />

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   minlength="6" 
                                   placeholder="Enter new password">
                            <div class="form-text">Password must be at least 6 characters.</div>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 fw-semibold">Update Password</button>
                    </form>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle CDN (Popper.js included) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
