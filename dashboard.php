<?php
session_start();
require 'db.php';

// Check login session & auto logout after 5 mins inactivity
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 300)) {
    session_unset();
    session_destroy();
    header('Location: admin_login.php');
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

$full_name = $_SESSION['full_name'];
$role = $_SESSION['role']; // 'superadmin' or 'admin'
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard | Hire Us System</title>
  <link rel="icon" type="image/png" href="icon2.png">

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />

  <style>
/* === Base Styles === */
body {
  font-family: 'Poppins', sans-serif;
  background: #f7f9fc;
  margin: 0;
  padding: 0;
  color: #333;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

/* === Sidebar === */
.sidebar {
  position: fixed;
  top: 0;
  left: -260px;
  width: 260px;
  height: 100vh;
  background: #1f2636;
  color: #e2e8f0;
  padding-top: 1.5rem;
  transition: left 0.4s ease-in-out;
  z-index: 1040;
  display: flex;
  flex-direction: column;
  box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
}

.sidebar.show {
  left: 0;
}

.sidebar-header {
  font-size: 1.6rem;
  font-weight: 700;
  text-align: center;
  margin-bottom: 1rem;
  color: #fff;
  text-transform: uppercase;
  letter-spacing: 1.5px;
}

.sidebar .text-center {
  font-size: 1rem;
  color: #cbd5e1;
  margin-bottom: 1.5rem;
}

.sidebar .nav-link {
  color: #cbd5e1;
  font-weight: 500;
  padding: 0.8rem 1.5rem;
  font-size: 1rem;
  border-radius: 0.5rem;
  transition: background 0.3s ease, color 0.3s ease;
  display: flex;
  align-items: center;
  gap: 12px;
}

.sidebar .nav-link i {
  font-size: 1.3rem;
}

.sidebar .nav-link:hover {
  background: rgba(80, 97, 252, 0.15);
  color: #fff;
}

.sidebar .nav-link.active {
  background: #5061fc;
  color: #fff;
}

.sidebar .nav-link.disabled {
  opacity: 0.4;
  pointer-events: none;
}

/* === Navbar === */
.navbar-custom {
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  padding: 0.8rem 1.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}

.navbar-brand {
  font-weight: 700;
  font-size: 1.4rem;
  color: #1f2636;
}

#sidebarToggle {
  font-size: 1.8rem;
  color: #1f2636;
  background: none;
  border: none;
  cursor: pointer;
  transition: transform 0.3s ease;
}

#sidebarToggle.rotate {
  transform: rotate(90deg);
}

/* User profile in navbar */
.navbar-nav .nav-link {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #1f2636;
  font-weight: 600;
}

.navbar-nav img {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  border: 2px solid #5061fc;
  transition: box-shadow 0.3s ease;
}

.navbar-nav img:hover {
  box-shadow: 0 0 10px rgba(80, 97, 252, 0.6);
}

/* === Dashboard Content === */
.content {
  margin-left: 0;
  padding: 1.5rem;
  flex-grow: 1;
  transition: margin-left 0.4s ease-in-out;
}

@media (min-width: 992px) {
  .content {
    margin-left: 260px;
  }
}

/* === Cards === */
.card-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.5rem;
}

.card {
  background: rgba(255, 255, 255, 0.85);
  border-radius: 16px;
  padding: 1.8rem 1.4rem;
  text-align: center;
  box-shadow: 0 10px 25px rgba(80, 97, 252, 0.08);
  backdrop-filter: blur(8px);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 1rem;
  cursor: pointer;
}

.card:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 15px 35px rgba(80, 97, 252, 0.25);
}

.card i {
  font-size: 3rem;
  color: #5061fc;
}

.card h5 {
  font-weight: 600;
  font-size: 1.2rem;
}

.card a.btn {
  border-radius: 30px;
  font-size: 0.95rem;
  font-weight: 600;
  padding: 0.6rem 1.3rem;
  background: #5061fc;
  color: #fff;
  transition: background 0.3s ease, box-shadow 0.3s ease;
}

.card a.btn:hover {
  background: #364fc7;
  box-shadow: 0 8px 20px rgba(54, 79, 199, 0.5);
}

/* === Footer === */
footer {
  background: #1f2636;
  color: #cbd5e1;
  text-align: center;
  padding: 1rem;
  font-size: 0.9rem;
}

footer a {
  color: #5061fc;
  font-weight: 600;
}

footer a:hover {
  color: #364fc7;
}

/* === Responsive Sidebar === */
@media (max-width: 992px) {
  .sidebar {
    left: -260px;
  }
  .sidebar.show {
    left: 0;
  }
}

  </style>
</head>
<body>

<!-- Sidebar -->
<nav id="sidebar" class="sidebar" aria-label="Sidebar Navigation">
  <div class="sidebar-header" tabindex="0">DASHBOARD</div>
  <div class="text-center mb-4 fw-semibold" id="sidebarUserName" tabindex="0"><?=htmlspecialchars($full_name)?></div>
  <ul class="nav flex-column px-1" role="menu">
    <li class="nav-item" role="none">
      <a href="Web_Site/Employee/workers.php" class="nav-link employee-management" role="menuitem" tabindex="0"><i class="bi bi-people"></i> Employee Management</a>
    </li>
    <li class="nav-item" role="none">
      <a href="Web_Site/employer/employers_crud.phpS" class="nav-link" role="menuitem" tabindex="0"><i class="bi bi-building"></i> Employer Management</a>
    </li>
    <li class="nav-item" role="none">
      <a href="Web_Site/Ratings/ratings.php"  class="nav-link" role="menuitem" tabindex="0"><i class="bi bi-star"></i> Review & Ratings</a>
    </li>
    <li class="nav-item" role="none">
      <a href="#" class="nav-link" role="menuitem" tabindex="0"><i class="bi bi-briefcase"></i> Job Management</a>
    </li>
    <li class="nav-item" role="none">
      <a href="Web_Site/Meals/index.php" class="nav-link" role="menuitem" tabindex="0"><i class="bi bi-basket"></i> Food Management</a>
    </li>
    <li class="nav-item" role="none">
      <a href="Web_Site/Vault/passbook.php" class="nav-link" role="menuitem" tabindex="0"><i class="bi bi-wallet2"></i> Withdraw Management</a>
    </li>
    <li class="nav-item" role="none">
      <a href="Web_Site/Messages/admin_chat.php" class="nav-link" role="menuitem" tabindex="0"><i class="bi bi-chat-dots"></i> Message Management</a>
    </li>

    <?php if ($role === 'superadmin'): ?>
    <li class="nav-item" role="none">
      <a href="Web_Site/Admins/admins.php" class="nav-link" role="menuitem" tabindex="0"><i class="bi bi-person-gear"></i> Admin Management</a>
    </li>
    <?php else: ?>
    <li class="nav-item" role="none">
      <a href="#" class="nav-link disabled" tabindex="-1" aria-disabled="true"><i class="bi bi-person-gear"></i> Admin Management</a>
    </li>
    <?php endif; ?>
  </ul>
</nav>

<!-- Page Content -->
<div class="content" role="main" tabindex="-1">
  <!-- Top Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light navbar-custom mb-4" aria-label="Top Navigation">
    <button class="btn" id="sidebarToggle" aria-label="Toggle sidebar" aria-expanded="false" aria-controls="sidebar">
      <i class="bi bi-list"></i>
    </button>
    <a class="navbar-brand" href="dashboard.php" tabindex="0">Admin Panel</a>

    <ul class="navbar-nav ms-auto align-items-center">
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" tabindex="0">
          <img src="https://i.pravatar.cc/40" alt="User avatar" />
          <span class="ms-2"><?=htmlspecialchars($full_name)?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUserDropdown">
          <li><a class="dropdown-item" href="profile.php">Profile</a></li>
          <li><hr class="dropdown-divider" /></li>
          <li><a class="dropdown-item" href="Web_Site/logout.php">Logout</a></li>
        </ul>
      </li>
    </ul>
  </nav>

  <!-- Dashboard Cards -->
  <div class="card-container" aria-label="Dashboard cards">
    <div class="card" tabindex="0" role="button" aria-pressed="false" onclick="location.href='Web_Site/Employee/workers.php'">
      <i class="bi bi-people"></i>
      <h5>Employee Management</h5>
      <a href="Web_Site/Employee/workers.php" class="btn btn-primary mt-auto" role="button" tabindex="-1">Go</a>
    </div>
    <div class="card" tabindex="0" role="button" aria-pressed="false" onclick="location.href='#'">
      <i class="bi bi-building"></i>
      <h5>Employer Management</h5>
      <a href="Web_Site/employer/employers_crud.php" class="btn btn-primary mt-auto" role="button" tabindex="-1">Go</a>
    </div>
    <div class="card" tabindex="0" role="button" aria-pressed="false" onclick="location.href='Web_Site/Ratings/ratings.php'">
      <i class="bi bi-star"></i>
      <h5>Review & Ratings</h5>
      <a href="Web_Site/Ratings/ratings.php" class="btn btn-primary mt-auto" role="button" tabindex="-1">Go</a>
    </div>
    <div class="card" tabindex="0" role="button" aria-pressed="false" onclick="location.href='#'">
      <i class="bi bi-briefcase"></i>
      <h5>Job Management</h5>
      <a href="#" class="btn btn-primary mt-auto" role="button" tabindex="-1">Go</a>
    </div>
    <div class="card" tabindex="0" role="button" aria-pressed="false" onclick="location.href='#'">
      <i class="bi bi-basket"></i>
      <h5>Food Management</h5>
      <a href="Web_Site/Meals/admin_jobs_meals.php" class="btn btn-primary mt-auto" role="button" tabindex="-1">Go</a>
    </div>
    <div class="card" tabindex="0" role="button" aria-pressed="false" onclick="location.href='#'">
      <i class="bi bi-wallet2"></i>
      <h5>Withdraw Management</h5>
      <a href="Web_Site/Vault/passbook.php" class="btn btn-primary mt-auto" role="button" tabindex="-1">Go</a>
    </div>

    <div class="card" tabindex="0" role="button" aria-pressed="false" onclick="location.href='Web_Site/Messages/admin_chat.php'">
      <i class="bi bi-chat-dots" style="font-size: 3rem; color: #198754;"></i>
      <h5 class="mt-3">Message Management</h5>
      <a href="Web_Site/Messages/admin_chat.php" class="btn btn-success mt-auto" role="button" tabindex="-1">Go</a>
    </div>

    <?php if ($role === 'superadmin'): ?>
    <div class="card" tabindex="0" role="button" aria-pressed="false" onclick="location.href='Web_Site/Admins/admins.php'">
      <i class="bi bi-person-gear"></i>
      <h5>Admin Management</h5>
      <a href="Web_Site/Admins/admins.php" class="btn btn-primary mt-auto" role="button" tabindex="-1">Go</a>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Footer -->
<footer>
  <p>© 2025 Hire Us System. All rights reserved. Developed by Alfa Software Solutions.</p>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
  const sidebar = document.getElementById('sidebar');
  const toggleBtn = document.getElementById('sidebarToggle');

  toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('show');
    toggleBtn.classList.toggle('rotate');
    const expanded = toggleBtn.getAttribute('aria-expanded') === 'true';
    toggleBtn.setAttribute('aria-expanded', !expanded);
  });

  // Set initial aria-expanded based on sidebar visibility on page load
  document.addEventListener('DOMContentLoaded', () => {
    const isShown = sidebar.classList.contains('show');
    toggleBtn.setAttribute('aria-expanded', isShown);
  });
</script>

</body>
</html>
