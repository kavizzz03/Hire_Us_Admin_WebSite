<?php
session_start();
require 'db.php';

// Auto logout after 5 mins inactivity
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 300)) {
    session_unset();
    session_destroy();
}
$_SESSION['LAST_ACTIVITY'] = time();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if ($username === '' || $password === '') {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, full_name, password, role FROM admin_users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
            $row = $res->fetch_assoc();
            // Passwords stored in plain text (not secure, but per your request)
            if ($row['password'] === $password) {
                // Set session variables
                $_SESSION['admin_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['full_name'] = $row['full_name'];
                $_SESSION['role'] = $row['role']; // 'superadmin' or 'admin'
                $_SESSION['LAST_ACTIVITY'] = time();

                // Track login count
                $stmt2 = $conn->prepare("INSERT INTO admin_logins (admin_id, login_time) VALUES (?, NOW())");
                $stmt2->bind_param("i", $row['id']);
                $stmt2->execute();

                header('Location: dashboard.php');
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "Username not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Hire Us System - Admin Login</title>
  <link rel="icon" type="image/png" href="icon2.png">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />

  <style>
    /* Fade-in page load */
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 1rem;
      margin: 0;
      color: #333;
      animation: fadeInPage 1s ease forwards;
      opacity: 0;
    }

    @keyframes fadeInPage {
      to {
        opacity: 1;
      }
    }

    header {
      width: 100%;
      max-width: 400px;
      text-align: center;
      margin-bottom: 2rem;
      color: #fff;
      text-shadow: 0 1px 3px rgba(0,0,0,0.3);
      opacity: 0;
      transform: translateY(-20px);
      animation: slideFadeIn 0.8s ease forwards;
    }
    header h1 {
      font-weight: 700;
      font-size: 2rem;
      letter-spacing: 0.1em;
    }

    .login-card {
      background: #fff;
      border-radius: 1rem;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
      padding: 2.5rem 3rem;
      max-width: 400px;
      width: 100%;
      position: relative;
      overflow: hidden;
      transition: box-shadow 0.3s ease, transform 0.4s ease;
      opacity: 0;
      transform: translateY(30px);
      animation: slideFadeIn 1s ease forwards 0.3s;
    }
    .login-card:hover {
      box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
      transform: translateY(0) scale(1.03);
    }

    h3 {
      font-weight: 700;
      margin-bottom: 1.8rem;
      color: #4a3fbd;
      text-align: center;
      letter-spacing: 0.05em;
      opacity: 0;
      transform: translateY(20px);
      animation: slideFadeIn 0.6s ease forwards 0.6s;
    }

    label {
      font-weight: 600;
      color: #4a3fbd;
      display: inline-block;
      margin-bottom: 0.5rem;
      opacity: 0;
      transform: translateX(-20px);
      animation: slideFadeInLeft 0.5s ease forwards;
    }

    .form-control {
      border-radius: 0.6rem;
      padding: 0.75rem 1rem;
      font-size: 1rem;
      transition: box-shadow 0.3s ease, border-color 0.3s ease;
      opacity: 0;
      transform: translateX(20px);
      animation: slideFadeInRight 0.5s ease forwards;
    }
    .form-control:focus {
      box-shadow: 0 0 8px 2px #4a3fbd;
      border-color: #4a3fbd;
      outline: none;
    }

    .input-group-text {
      background: transparent;
      border: none;
      cursor: pointer;
      color: #4a3fbd;
      font-size: 1.2rem;
      user-select: none;
      opacity: 0;
      transform: translateX(20px);
      animation: slideFadeInRight 0.5s ease forwards 0.3s;
    }

    .form-check-label {
      user-select: none;
      color: #5a4fbf;
      opacity: 0;
      transform: translateX(-20px);
      animation: slideFadeInLeft 0.5s ease forwards 0.2s;
    }

    .form-check-input {
      opacity: 0;
      transform: translateX(20px);
      animation: slideFadeInRight 0.5s ease forwards 0.2s;
    }

    .btn-primary {
      background: #4a3fbd;
      border: none;
      border-radius: 50px;
      padding: 0.75rem;
      font-weight: 600;
      box-shadow: 0 8px 15px rgba(74, 63, 189, 0.4);
      transition: all 0.3s ease;
      width: 100%;
      font-size: 1.1rem;
      cursor: pointer;
      opacity: 0;
      transform: translateY(20px);
      animation: slideFadeIn 0.6s ease forwards 0.8s;
    }
    .btn-primary:hover {
      background: #382f8f;
      box-shadow: 0 12px 20px rgba(56, 47, 143, 0.6);
      transform: translateY(0) scale(1.05);
    }

    .alert {
      border-radius: 0.75rem;
      font-weight: 600;
      text-align: center;
      margin-bottom: 1.5rem;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      color: #b71c1c;
      background-color: #f8d7da;
      border-color: #f5c2c7;
      opacity: 0;
      animation: fadeIn 0.8s ease forwards 1s;
    }

    footer {
      margin-top: 3rem;
      color: #eee;
      font-weight: 500;
      text-align: center;
      font-size: 0.9rem;
      user-select: none;
      text-shadow: 0 1px 2px rgba(0,0,0,0.5);
      opacity: 0;
      transform: translateY(20px);
      animation: slideFadeIn 0.8s ease forwards 1.2s;
    }

    /* Animations */
    @keyframes slideFadeIn {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    @keyframes slideFadeInLeft {
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }
    @keyframes slideFadeInRight {
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }
    @keyframes fadeIn {
      to {
        opacity: 1;
      }
    }
  </style>
</head>
<body>

  <header>
    <h1>Hire Us System - Admin Login</h1>
  </header>

  <div class="login-card shadow-sm">
    <h3>Admin Login</h3>

    <?php if ($error): ?>
      <div class="alert"><?=htmlspecialchars($error)?></div>
    <?php endif; ?>

    <form method="POST" action="" id="loginForm" autocomplete="off">
      <div class="mb-3">
        <label for="username">Username</label>
        <input 
          type="text" 
          id="username" 
          name="username" 
          class="form-control" 
          required 
          autofocus
          value=""
        />
      </div>

      <div class="mb-3">
        <label for="password">Password</label>
        <div class="input-group">
          <input 
            type="password" 
            id="password" 
            name="password" 
            class="form-control" 
            required 
          />
          <span class="input-group-text" id="togglePassword" title="Show/Hide Password">
            <i class="bi bi-eye"></i>
          </span>
        </div>
      </div>

      <div class="mb-3 form-check d-flex align-items-center">
        <input type="checkbox" class="form-check-input" id="rememberMe" />
        <label class="form-check-label ms-2" for="rememberMe">Remember Me</label>
      </div>

      <button type="submit" class="btn btn-primary">Log In</button>
    </form>
  </div>

  <footer>
    &copy; <?=date('Y')?> Hire Us System. All rights reserved.
  </footer>

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

  <script>
    // Password toggle
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    togglePassword.addEventListener('click', () => {
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      togglePassword.innerHTML = type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
    });

    // Remember Me - store username in localStorage
    const rememberMeCheckbox = document.getElementById('rememberMe');
    const usernameInput = document.getElementById('username');

    // Load stored username
    window.addEventListener('DOMContentLoaded', () => {
      const storedUsername = localStorage.getItem('rememberedUsername');
      if (storedUsername) {
        usernameInput.value = storedUsername;
        rememberMeCheckbox.checked = true;
      }
    });

    // Save username on form submit if "Remember Me" is checked
    document.getElementById('loginForm').addEventListener('submit', () => {
      if (rememberMeCheckbox.checked) {
        localStorage.setItem('rememberedUsername', usernameInput.value);
      } else {
        localStorage.removeItem('rememberedUsername');
      }
    });
  </script>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
