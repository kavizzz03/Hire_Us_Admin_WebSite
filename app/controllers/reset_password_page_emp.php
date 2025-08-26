<?php
$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);

$token = $_GET['token'] ?? '';
$message = '';
$messageClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $token = $_POST['token'] ?? '';

    if ($newPassword !== $confirmPassword) {
        $message = "Passwords do not match.";
        $messageClass = "alert-danger";
    } elseif (strlen($newPassword) < 6) {
        $message = "Password must be at least 6 characters.";
        $messageClass = "alert-danger";
    } else {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE workers SET password=?, reset_token=NULL WHERE reset_token=?");
        $stmt->bind_param("ss", $hashed, $token);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $message = "Password reset successful. You can now your new password.";
            $messageClass = "alert-success";
        } else {
            $message = "Invalid or expired token. Please request a new password reset.";
            $messageClass = "alert-danger";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reset Password | Hire Me</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f9f9f9;
        }
        .reset-container {
            max-width: 420px;
            margin: 60px auto;
            padding: 25px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgb(0 0 0 / 0.1);
        }
        .toggle-password {
            cursor: pointer;
            user-select: none;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h3 class="mb-4 text-center" style="color: #fbc02d;">Hire Me - Reset Password</h3>

        <?php if ($message): ?>
            <div class="alert <?= $messageClass ?>" role="alert">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if (empty($messageClass) || $messageClass === "alert-danger"): ?>
        <form method="POST" novalidate>
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>" />
            <div class="mb-3 position-relative">
                <label for="new_password" class="form-label">New Password</label>
                <input
                    type="password"
                    id="new_password"
                    name="new_password"
                    class="form-control"
                    required
                    minlength="6"
                />
                <span
                    class="position-absolute top-50 end-0 translate-middle-y me-3 toggle-password"
                    onclick="togglePassword('new_password', this)"
                    title="Show/Hide Password"
                >👁️</span>
            </div>

            <div class="mb-3 position-relative">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    class="form-control"
                    required
                    minlength="6"
                />
                <span
                    class="position-absolute top-50 end-0 translate-middle-y me-3 toggle-password"
                    onclick="togglePassword('confirm_password', this)"
                    title="Show/Hide Password"
                >👁️</span>
            </div>

            <button type="submit" class="btn btn-warning w-100 fw-bold">Reset Password</button>
        </form>
        <?php endif; ?>
    </div>

    <script>
        function togglePassword(fieldId, elem) {
            const input = document.getElementById(fieldId);
            if (input.type === "password") {
                input.type = "text";
                elem.textContent = "🙈";
            } else {
                input.type = "password";
                elem.textContent = "👁️";
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
