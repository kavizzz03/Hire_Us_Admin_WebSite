<?php
session_start();
require 'db.php';

if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: /../../dashboard.php');
    exit();
}

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 300)) {
    session_unset();
    session_destroy();
    header('Location: /../../admin_login.php');
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

$message = '';
$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $username = trim($_POST['username']);
    $full_name_new = trim($_POST['full_name']);
    $password = trim($_POST['password']);
    $role_new = trim($_POST['role']);
    if ($username && $full_name_new && $password && ($role_new === 'admin' || $role_new === 'superadmin')) {
        $stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            $stmt = $conn->prepare("INSERT INTO admin_users (username, full_name, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $full_name_new, $password, $role_new);
            $stmt->execute();
            $message = "Admin added successfully.";
        } else {
            $message = "Username already exists.";
        }
    } else {
        $message = "Please fill all fields correctly.";
    }
} elseif ($action === 'update') {
    $id = intval($_POST['id']);
    $full_name_upd = trim($_POST['full_name']);
    $password_upd = trim($_POST['password']);
    $role_upd = trim($_POST['role']);
    if ($id > 0 && $full_name_upd && $role_upd && ($role_upd === 'admin' || $role_upd === 'superadmin')) {
        if ($password_upd !== '') {
            $stmt = $conn->prepare("UPDATE admin_users SET full_name = ?, password = ?, role = ? WHERE id = ?");
            $stmt->bind_param("sssi", $full_name_upd, $password_upd, $role_upd, $id);
        } else {
            $stmt = $conn->prepare("UPDATE admin_users SET full_name = ?, role = ? WHERE id = ?");
            $stmt->bind_param("ssi", $full_name_upd, $role_upd, $id);
        }
        $stmt->execute();
        $message = "Admin updated successfully.";
    } else {
        $message = "Invalid data for update.";
    }
} elseif ($action === 'delete') {
    $id = intval($_POST['id']);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM admin_users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $message = "Admin deleted successfully.";
    }
}

$result = $conn->query("SELECT id, username, full_name, role FROM admin_users ORDER BY id ASC");

$loginCounts = [];
$loginQuery = "SELECT au.username, COUNT(al.id) AS login_count 
               FROM admin_users au 
               LEFT JOIN admin_logins al ON au.id = al.admin_id 
               GROUP BY au.id
               ORDER BY au.username ASC";
if ($loginResult = $conn->query($loginQuery)) {
    while ($row = $loginResult->fetch_assoc()) {
        $loginCounts[] = [
            'username' => $row['username'] ?? 'Unknown',
            'count' => isset($row['login_count']) ? (int)$row['login_count'] : 0
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Management & Login Analysis</title>
  <link rel="icon" type="image/png" href="icon2.png">

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
  /* Global */
  body {
    background: linear-gradient(135deg, #6b73ff 0%, #000dff 100%);
    font-family: 'Poppins', sans-serif;
    color: #222;
    min-height: 100vh;
    padding-bottom: 40px;
  }
  .container {
    max-width: 1100px;
    background: #fff;
    border-radius: 12px;
    padding: 2rem 3rem 3rem 3rem;
    margin-top: 2rem;
    box-shadow: 0 20px 50px rgba(0, 13, 255, 0.2);
    animation: fadeInUp 0.8s ease forwards;
  }
  h2 {
    color: #000dff;
    font-weight: 700;
    margin-bottom: 1rem;
    letter-spacing: 1.1px;
  }
  h4 {
    color: #222;
    margin-top: 2rem;
    margin-bottom: 1rem;
    font-weight: 600;
    border-bottom: 3px solid #000dff;
    display: inline-block;
    padding-bottom: 0.2rem;
  }
  /* Alert */
  .alert {
    font-weight: 600;
    border-radius: 8px;
    background-color: #d1e7dd;
    color: #0f5132;
    border-color: #badbcc;
    box-shadow: 0 0 8px #badbcc;
  }

  /* Forms */
  form input, form select {
    border-radius: 6px;
    box-shadow: inset 0 0 7px rgb(0 0 0 / 0.05);
    transition: box-shadow 0.3s ease;
  }
  form input:focus, form select:focus {
    box-shadow: 0 0 12px #000dff;
    outline: none;
  }

  /* Buttons */
  button.btn {
    font-weight: 600;
    transition: background-color 0.3s ease, transform 0.2s ease;
    border-radius: 8px;
  }
  button.btn:hover {
    transform: scale(1.05);
  }

  /* Table */
  table {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 13, 255, 0.1);
  }
  thead {
    background-color: #000dff;
    color: #fff;
  }
  tbody tr:hover {
    background-color: #e6f0ff;
    transition: background-color 0.3s ease;
  }
  tbody input[type="text"], tbody select {
    border: 1px solid #ccc;
    border-radius: 5px;
  }
  tbody input[type="text"]:focus, tbody select:focus {
    border-color: #000dff;
    box-shadow: 0 0 8px #000dff;
  }

  /* Chart container */
  #chartContainer {
    margin-top: 3rem;
    padding: 1.5rem;
    border-radius: 1rem;
    background: #f0f4ff;
    box-shadow: 0 8px 30px rgba(0, 13, 255, 0.1);
    height: 420px;
    position: relative;
    animation: fadeIn 1s ease forwards;
  }
  #chartContainer h4 {
    color: #000dff;
    font-weight: 600;
    margin-bottom: 1rem;
  }

  /* Report Section */
  #reportSection {
    margin-top: 3rem;
    background: #f9faff;
    border-radius: 12px;
    padding: 2rem 2.5rem;
    box-shadow: 0 8px 30px rgba(0, 13, 255, 0.1);
    animation: fadeIn 1.2s ease forwards;
  }
  #reportSection h4 {
    color: #000dff;
    font-weight: 600;
    margin-bottom: 1.5rem;
  }
  #reportContent h5 {
    font-weight: 600;
    color: #0a0a54;
  }
  #reportContent table {
    box-shadow: none;
    border: none;
  }
  #reportContent thead {
    background-color: #000dff;
    color: white;
  }
  #reportContent tbody tr:nth-child(even) {
    background-color: #e3eaff;
  }
  #reportContent tbody tr:hover {
    background-color: #c3d0ff;
  }

  /* Buttons layout */
  #generateReportBtn, #printReportBtn {
    border-radius: 8px;
    font-weight: 600;
  }
  #printReportBtn {
    border: 2px solid #000dff;
    color: #000dff;
    background: transparent;
    transition: background-color 0.3s ease, color 0.3s ease;
  }
  #printReportBtn:hover:not(:disabled) {
    background-color: #000dff;
    color: white;
  }
  #printReportBtn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  /* Responsive tweaks */
  @media (max-width: 768px) {
    .container {
      padding: 1.5rem 1.5rem 2rem 1.5rem;
    }
    #chartContainer, #reportSection {
      height: auto;
    }
  }

  /* Animations */
  @keyframes fadeInUp {
    from {opacity: 0; transform: translateY(30px);}
    to {opacity: 1; transform: translateY(0);}
  }
  @keyframes fadeIn {
    from {opacity: 0;}
    to {opacity: 1;}
  }
  /* Footer */
footer.footer {
  margin-top: 4rem;
  padding: 18px 0;
  background: #f3f4f6;
  color: #64748b;
  font-size: 0.9rem;
  border-radius: 0 0 1rem 1rem;
  text-align: center;
  user-select: none;
  box-shadow: inset 0 1px 2px rgba(255,255,255,0.7);
}

</style>
</head>
<body>

<div class="container shadow-lg">
    <h2 class="text-center">Admin Management & Login Analysis</h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-info mt-4"><?=htmlspecialchars($message)?></div>
    <?php endif; ?>

    <h4>Add New Admin</h4>
    <form method="POST" class="mb-4" autocomplete="off" novalidate>
        <input type="hidden" name="action" value="add" />
        <div class="row g-3 align-items-center">
            <div class="col-md-3">
                <input type="text" name="username" class="form-control shadow-sm" placeholder="Username" required minlength="3" maxlength="30" pattern="^[a-zA-Z0-9_]+$" title="Alphanumeric and underscore only" />
            </div>
            <div class="col-md-3">
                <input type="text" name="full_name" class="form-control shadow-sm" placeholder="Full Name" required minlength="3" maxlength="50" />
            </div>
            <div class="col-md-3">
                <input type="password" name="password" class="form-control shadow-sm" placeholder="Password" required minlength="6" />
            </div>
            <div class="col-md-2">
                <select name="role" class="form-select shadow-sm" required>
                    <option value="" disabled selected>Select Role</option>
                    <option value="admin">Admin</option>
                    <option value="superadmin">Super Admin</option>
                </select>
            </div>
            <div class="col-md-1 d-grid">
                <button type="submit" class="btn btn-primary btn-lg shadow">Add</button>
            </div>
        </div>
    </form>

    <h4>Existing Admins</h4>
    <table class="table table-hover bg-white align-middle shadow-sm">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Full Name</th>
                <th>Role</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <form method="POST" onsubmit="return confirm('Are you sure?');" autocomplete="off" novalidate>
                        <input type="hidden" name="id" value="<?= $row['id'] ?>" />
                        <td><?= $row['id'] ?></td>
                        <td class="text-secondary"><?= htmlspecialchars($row['username']) ?></td>
                        <td>
                            <input type="text" name="full_name" value="<?= htmlspecialchars($row['full_name']) ?>" class="form-control form-control-sm shadow-sm" required minlength="3" maxlength="50" />
                        </td>
                        <td>
                            <select name="role" class="form-select form-select-sm shadow-sm" required>
                                <option value="admin" <?= $row['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="superadmin" <?= $row['role'] === 'superadmin' ? 'selected' : '' ?>>Super Admin</option>
                            </select>
                        </td>
                        <td class="text-center">
                            <input type="password" name="password" placeholder="New Password (optional)" class="form-control form-control-sm mb-1 shadow-sm" minlength="6" />
                            <button type="submit" name="action" value="update" class="btn btn-success btn-sm me-1 shadow-sm" title="Update admin info">
                              <i class="bi bi-pencil-square"></i> Update
                            </button>
                            <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm shadow-sm" title="Delete admin" onclick="return confirm('Delete this admin?');">
                              <i class="bi bi-trash"></i> Delete
                            </button>
                        </td>
                    </form>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="/../../dashboard.php" class="btn btn-outline-secondary mt-4 shadow-sm d-block w-100">
      &larr; Back to Dashboard
    </a>

    <!-- Chart Section -->
    <div id="chartContainer">
      <h4>Login Counts of All Admins</h4>
      <canvas id="loginChart"></canvas>
    </div>

    <!-- Report Section -->
    <div id="reportSection">
      <h4>Generate Admin Login Report</h4>
      <div class="row g-3 align-items-center">
        <div class="col-md-6">
          <select id="adminSelect" class="form-select shadow-sm" aria-label="Select admin to generate report">
            <option value="">-- Select Admin --</option>
            <?php foreach ($loginCounts as $admin): ?>
              <option value="<?= htmlspecialchars($admin['username']) ?>"><?= htmlspecialchars($admin['username']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3 d-grid">
          <button id="generateReportBtn" class="btn btn-primary btn-lg shadow" disabled>Generate Report</button>
        </div>
        <div class="col-md-3 d-grid">
          <button id="printReportBtn" class="btn btn-outline-primary btn-lg shadow" disabled>Print Report</button>
        </div>
      </div>
      <div id="reportContent" class="mt-4" style="display:none;">
        <h5>Report for <span id="reportAdminName"></span></h5>
        <table class="table table-striped shadow-sm">
          <thead>
            <tr>
              <th>Login Time (Local)</th>
            </tr>
          </thead>
          <tbody id="reportTableBody">
            <!-- Filled dynamically -->
          </tbody>
        </table>
      </div>
    </div>
</div>
  <!-- Footer -->
  <footer class="footer">
    &copy; <?= date('Y') ?> Hire Us System. All rights reserved.
  </footer>


<!-- Bootstrap Icons CDN for icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

<script>
const loginCounts = <?= json_encode($loginCounts) ?>;

// Chart colors for bars
const colors = [
  '#000dff', '#7c4dff', '#4a60e9', '#e94f37', '#f7b733',
  '#3fc1c9', '#8e44ad', '#27ae60', '#d35400', '#2980b9'
];

// Prepare chart data
const ctx = document.getElementById('loginChart').getContext('2d');
const labels = loginCounts.map(a => a.username);
const data = loginCounts.map(a => a.count);

const loginChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels,
    datasets: [{
      label: 'Number of Logins',
      data,
      backgroundColor: data.map((_,i) => colors[i % colors.length]),
      borderWidth: 1,
      borderColor: '#000dff',
      borderRadius: 8,
      maxBarThickness: 70,
    }]
  },
  options: {
    scales: {
      y: {
        beginAtZero: true,
        ticks: { stepSize: 1, precision: 0 },
        title: { display: true, text: 'Number of Logins' }
      },
      x: {
        title: { display: true, text: 'Admin Users' }
      }
    },
    plugins: {
      legend: { display: false },
      tooltip: {
        enabled: true,
        backgroundColor: '#000dff',
        titleColor: '#fff',
        bodyColor: '#fff',
        padding: 8,
        cornerRadius: 8,
      }
    },
    responsive: true,
    maintainAspectRatio: false,
  }
});

const adminSelect = document.getElementById('adminSelect');
const generateBtn = document.getElementById('generateReportBtn');
const printBtn = document.getElementById('printReportBtn');
const reportContent = document.getElementById('reportContent');
const reportAdminName = document.getElementById('reportAdminName');
const reportTableBody = document.getElementById('reportTableBody');

adminSelect.addEventListener('change', () => {
  generateBtn.disabled = adminSelect.value === '';
  reportContent.style.display = 'none';
  printBtn.disabled = true;
});

generateBtn.addEventListener('click', () => {
  const selectedAdmin = adminSelect.value;
  if (!selectedAdmin) return;

  fetch(`get_admin_logins.php?username=${encodeURIComponent(selectedAdmin)}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        reportAdminName.textContent = selectedAdmin;
        reportTableBody.innerHTML = '';
        if (data.logins.length === 0) {
          reportTableBody.innerHTML = '<tr><td class="text-center">No login records found.</td></tr>';
        } else {
          data.logins.forEach(loginTime => {
            const localDate = new Date(loginTime);
            const formattedDate = localDate.toLocaleString();
            const row = `<tr><td>${formattedDate}</td></tr>`;
            reportTableBody.insertAdjacentHTML('beforeend', row);
          });
        }
        reportContent.style.display = 'block';
        printBtn.disabled = false;
        reportContent.scrollIntoView({ behavior: 'smooth' });
      } else {
        alert('Failed to load login records.');
      }
    })
    .catch(() => alert('Error fetching login data.'));
});

printBtn.addEventListener('click', () => {
  const printContent = reportContent.innerHTML;
  const originalContent = document.body.innerHTML;

  document.body.innerHTML = `<div style="margin:20px; font-family: 'Poppins', sans-serif;">${printContent}</div>`;
  window.print();
  document.body.innerHTML = originalContent;
  window.location.reload();
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
