<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection (do NOT call session_start() here)
$servername = "localhost";
$db_username = "u569550465_math_rakusa";
$db_password = "Sithija2025#";
$db_name = "u569550465_hireme";

$conn = new mysqli($servername, $db_username, $db_password, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];
$full_name = $_SESSION['full_name'];
$role = $_SESSION['role'];

$error = $success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_full_name = trim($_POST['full_name'] ?? '');

    if ($new_full_name === '') {
        $error = "Full name cannot be empty.";
    } else {
        $stmt = $conn->prepare("UPDATE admin_users SET full_name = ? WHERE id = ?");
        $stmt->bind_param("si", $new_full_name, $admin_id);
        if ($stmt->execute()) {
            $success = "Profile updated successfully.";
            $_SESSION['full_name'] = $new_full_name;
            $full_name = $new_full_name;
        } else {
            $error = "Failed to update profile.";
        }
        $stmt->close();
    }
}

$stmt = $conn->prepare("SELECT username, full_name, role FROM admin_users WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    die("Admin not found.");
}
$admin = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Hire us Admin - Profile</title>
    <link rel="icon" type="image/png" href="icon2.png">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Google Fonts: Poppins for modern look -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />

  <style>
    /* Reset and base */
    body {
      margin: 0; padding: 0;
      min-height: 100vh;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 2rem;
      color: #333;
    }

    /* Glassmorphism card container */
    .glass-card {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border-radius: 20px;
      border: 1px solid rgba(255, 255, 255, 0.3);
      box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
      width: 100%;
      max-width: 480px;
      padding: 3rem 2.5rem;
      box-sizing: border-box;
      color: #fff;
      transition: box-shadow 0.3s ease;
    }
    .glass-card:hover {
      box-shadow: 0 16px 48px 0 rgba(31, 38, 135, 0.6);
    }

    h2 {
      font-weight: 700;
      font-size: 2rem;
      text-align: center;
      margin-bottom: 2rem;
      letter-spacing: 0.06em;
      color: #f3f4f6;
      text-shadow: 0 1px 5px rgba(0,0,0,0.3);
    }

    label {
      font-weight: 600;
      color: #e0e0e0;
      margin-bottom: 0.5rem;
      display: block;
    }

    input[type=text], input[disabled] {
      width: 100%;
      padding: 0.65rem 1rem;
      border-radius: 10px;
      border: none;
      background: rgba(255,255,255,0.25);
      color: #fff;
      font-size: 1rem;
      outline: none;
      box-shadow: inset 0 0 10px rgba(255,255,255,0.3);
      transition: background-color 0.3s ease;
      user-select: none;
    }
    input[type=text]:focus {
      background: rgba(255,255,255,0.4);
      box-shadow: 0 0 8px 2px #667eea;
      color: #222;
      user-select: text;
    }
    input[disabled] {
      opacity: 0.6;
      cursor: not-allowed;
      user-select: none;
    }

    .btn-primary {
      background: #5a42a6;
      border: none;
      font-weight: 600;
      padding: 0.75rem 2rem;
      border-radius: 50px;
      box-shadow: 0 8px 15px rgba(90, 66, 166, 0.4);
      transition: all 0.3s ease;
    }
    .btn-primary:hover {
      background: #482f91;
      box-shadow: 0 12px 20px rgba(72, 47, 145, 0.6);
      transform: translateY(-3px);
    }

    .btn-secondary {
      background: transparent;
      border: 2px solid #fff;
      color: #fff;
      font-weight: 600;
      padding: 0.75rem 2rem;
      border-radius: 50px;
      transition: all 0.3s ease;
      margin-left: 1rem;
    }
    .btn-secondary:hover {
      background: #fff;
      color: #5a42a6;
    }

    .alert {
      border-radius: 12px;
      font-weight: 600;
      text-align: center;
      margin-bottom: 1.5rem;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }

    @media (max-width: 576px) {
      .glass-card {
        padding: 2rem 1.5rem;
      }
      .btn-secondary {
        margin-left: 0;
        margin-top: 1rem;
        width: 100%;
      }
      .btn-primary {
        width: 100%;
      }
      .btn-group {
        flex-direction: column;
        gap: 0.75rem;
      }
    }
  </style>
</head>
<body>

  <div class="glass-card shadow">
    <h2>Admin Profile</h2>

    <?php if ($success): ?>
      <div class="alert alert-success" role="alert"><?=htmlspecialchars($success)?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger" role="alert"><?=htmlspecialchars($error)?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="mb-4">
        <label>Username (cannot edit)</label>
        <input type="text" value="<?=htmlspecialchars($admin['username'])?>" disabled />
      </div>

      <div class="mb-4">
        <label for="full_name">Full Name</label>
        <input id="full_name" name="full_name" type="text" required value="<?=htmlspecialchars($admin['full_name'])?>" />
      </div>

      <div class="mb-4">
        <label>Role (cannot edit)</label>
        <input type="text" value="<?=htmlspecialchars($admin['role'])?>" disabled />
      </div>

      <div class="d-flex btn-group justify-content-center">
        <button type="submit" class="btn btn-primary">Update Profile</button>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
      </div>
    </form>
  </div>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
