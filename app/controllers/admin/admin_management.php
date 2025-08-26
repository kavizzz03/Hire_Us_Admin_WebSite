<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: admin_login.html"); exit(); }

$servername = "localhost";
$db_username = "u569550465_math_rakusa";
$db_password = "Sithija2025#";
$db_name = "u569550465_hireme";

$conn = new mysqli($servername, $db_username, $db_password, $db_name);
if ($conn->connect_error) die("DB error");

$res = $conn->query("SELECT id, username, full_name, email, role, login_count FROM admin_users ORDER BY id ASC");
$admins = [];
$labels = []; $counts = [];
while ($r = $res->fetch_assoc()) {
    $admins[] = $r;
    $labels[] = $r['username'];
    $counts[] = (int)$r['login_count'];
}
$conn->close();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="p-4">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3>Admin Management</h3>
      <a class="btn btn-secondary" href="dashboard.php">Back to Dashboard</a>
    </div>

    <!-- Add Admin -->
    <div class="card mb-4 p-3">
      <h5>Add Admin</h5>
      <form method="POST" action="manage_admin.php?action=add">
        <div class="row g-2">
          <div class="col-md-3"><input name="username" class="form-control" placeholder="Username" required/></div>
          <div class="col-md-3"><input name="full_name" class="form-control" placeholder="Full name" required/></div>
          <div class="col-md-3"><input name="email" type="email" class="form-control" placeholder="Email" required/></div>
          <div class="col-md-2"><input name="password" type="password" class="form-control" placeholder="Password" required/></div>
          <div class="col-md-1"><button class="btn btn-success w-100">Add</button></div>
        </div>
      </form>
    </div>

    <!-- Table -->
    <div class="card p-3">
      <h5>Admins</h5>
      <table class="table table-sm">
        <thead class="table-light">
          <tr><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Role</th><th>Login Count</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($admins as $a): ?>
          <tr>
            <td><?php echo $a['id']; ?></td>
            <td><?php echo htmlspecialchars($a['username']); ?></td>
            <td><?php echo htmlspecialchars($a['full_name']); ?></td>
            <td><?php echo htmlspecialchars($a['email']); ?></td>
            <td><?php echo htmlspecialchars($a['role']); ?></td>
            <td><?php echo (int)$a['login_count']; ?></td>
            <td>
              <button class="btn btn-sm btn-primary" onclick="openEdit(<?php echo $a['id'];?>,'<?php echo addslashes($a['username']);?>','<?php echo addslashes($a['full_name']);?>','<?php echo addslashes($a['email']);?>')">Edit</button>
              <form method="POST" action="manage_admin.php?action=delete" style="display:inline-block" onsubmit="return confirm('Delete admin?')">
                <input type="hidden" name="id" value="<?php echo $a['id'];?>">
                <button class="btn btn-sm btn-danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Chart -->
    <div class="card p-3 mt-3">
      <h5>Login Analysis</h5>
      <canvas id="chart" style="height:200px;"></canvas>
    </div>
  </div>

  <!-- Edit Modal -->
  <div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
      <form class="modal-content" method="POST" action="manage_admin.php?action=update">
        <div class="modal-header"><h5>Edit Admin</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <input type="hidden" name="id" id="editId">
          <div class="mb-2"><label>Username</label><input name="username" id="editUsername" class="form-control" required></div>
          <div class="mb-2"><label>Full name</label><input name="full_name" id="editFullName" class="form-control" required></div>
          <div class="mb-2"><label>Email</label><input name="email" id="editEmail" type="email" class="form-control" required></div>
          <div class="mb-2"><label>New password (leave blank = unchanged)</label><input name="password" type="password" class="form-control"></div>
        </div>
        <div class="modal-footer"><button class="btn btn-success">Save</button></div>
      </form>
    </div>
  </div>

  <script>
    function openEdit(id, username, fullName, email){
      document.getElementById('editId').value = id;
      document.getElementById('editUsername').value = username;
      document.getElementById('editFullName').value = fullName;
      document.getElementById('editEmail').value = email;
      var m = new bootstrap.Modal(document.getElementById('editModal'));
      m.show();
    }

    const ctx = document.getElementById('chart');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{ label: 'Login Count', data: <?php echo json_encode($counts); ?>, backgroundColor: 'rgba(75,192,192,0.6)' }]
      },
      options: { responsive:true, plugins:{ legend:{display:false}} }
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
