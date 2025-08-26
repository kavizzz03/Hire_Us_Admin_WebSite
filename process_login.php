<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DB connection info
$servername = "localhost";
$db_username = "u569550465_math_rakusa";
$db_password = "Sithija2025#";
$db_name = "u569550465_hireme";

$conn = new mysqli($servername, $db_username, $db_password, $db_name);
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_login.html");
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    echo "<script>alert('Enter username and password'); location.href='admin_login.html'</script>";
    exit();
}

// Prepare statement to get user by username
$stmt = $conn->prepare("SELECT id, username, password, full_name, role FROM admin_users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();

    // Since passwords are NOT encrypted, compare plain text passwords
    if ($password === $row['password']) {
        // Set session variables
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $row['id'];
        $_SESSION['admin_username'] = $row['username'];
        $_SESSION['admin_full_name'] = $row['full_name'];
        $_SESSION['admin_role'] = $row['role'];
        $_SESSION['last_activity'] = time(); // track last activity for auto logout

        // Increment login count
        $upd = $conn->prepare("UPDATE admin_users SET login_count = login_count + 1 WHERE id = ?");
        $upd->bind_param("i", $row['id']);
        $upd->execute();
        $upd->close();

        // Redirect to dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        echo "<script>alert('Incorrect username or password'); location.href='admin_login.html'</script>";
        exit();
    }
} else {
    echo "<script>alert('User not found'); location.href='admin_login.html'</script>";
    exit();
}

$stmt->close();
$conn->close();
