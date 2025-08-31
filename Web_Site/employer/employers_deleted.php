<?php
require 'db.php';

$message = '';
$message_type = '';

// Handle Restore
if (isset($_GET['restore_id'])) {
    $id = intval($_GET['restore_id']);
    $deleted_emp = $conn->query("SELECT * FROM employers_deleted WHERE id = $id")->fetch_assoc();
    if ($deleted_emp) {
        $stmt = $conn->prepare("INSERT INTO employers (company_name, name, address, email, contact, company_icon, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $deleted_emp['company_name'], $deleted_emp['name'], $deleted_emp['address'], $deleted_emp['email'], $deleted_emp['contact'], $deleted_emp['company_icon'], $deleted_emp['password']);
        $stmt->execute();
        $conn->query("DELETE FROM employers_deleted WHERE id = $id");
        $message = "✅ Employer restored successfully!";
        $message_type = 'success';
    }
}

// Handle Permanent Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM employers_deleted WHERE id = $id");
    $message = "🗑 Employer permanently deleted.";
    $message_type = 'danger';
}

// Search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_sql = '';
if ($search !== '') {
    $safe = $conn->real_escape_string($search);
    $search_sql = "WHERE company_name LIKE '%$safe%' 
                   OR name LIKE '%$safe%' 
                   OR email LIKE '%$safe%' 
                   OR contact LIKE '%$safe%'";
}

// Fetch Deleted Employers
$deleted_result = $conn->query("SELECT * FROM employers_deleted $search_sql ORDER BY deleted_at DESC");

// Fetch Weekly Delete Counts for Chart
$chart_result = $conn->query("
    SELECT YEARWEEK(deleted_at, 1) AS week, COUNT(*) AS count
    FROM employers_deleted
    GROUP BY YEARWEEK(deleted_at, 1)
    ORDER BY week ASC
");

$chart_labels = [];
$chart_counts = [];
while ($row = $chart_result->fetch_assoc()) {
    $chart_labels[] = 'Week ' . substr($row['week'], -2);
    $chart_counts[] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Deleted Employers</title>
<link rel="icon" type="image/png" href="icon2.png">
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Bootstrap + Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    body { background: #f1f5f9; font-family: 'Segoe UI', sans-serif; }
    .card { border-radius: 15px; box-shadow: 0 6px 20px rgba(0,0,0,0.08); }
    thead th { background: linear-gradient(90deg, #1f2937, #374151); color: #fff; }
    .table-hover tbody tr:hover { background-color: #f0f9ff; }
    .chart-container { background: white; border-radius: 15px; padding: 20px; }
    .toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; }
</style>
</head>
<body class="p-4">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold"><i class="bi bi-trash3-fill text-danger"></i> Deleted Employers</h2>
    <a href="employers_crud.php" class="btn btn-dark rounded-pill px-4"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
</div>

<!-- Toast Message -->
<?php if ($message): ?>
<div class="toast-container">
    <div class="toast align-items-center text-bg-<?= $message_type ?> border-0 show" role="alert">
        <div class="d-flex">
            <div class="toast-body"><?= $message ?></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Search Bar -->
<div class="card p-3 mb-4">
    <form method="get" class="d-flex">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control me-2" placeholder="Search by company, name, email or contact">
        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
        <?php if ($search !== ''): ?>
            <a href="employers_deleted.php" class="btn btn-secondary ms-2"><i class="bi bi-x-lg"></i> Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Table -->
<div class="card p-3 mb-4">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Company</th>
                <th>Name</th>
                <th>Email</th>
                <th>Contact</th>
                <th>Deleted At</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($deleted_result->num_rows > 0): ?>
                <?php while ($row = $deleted_result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['company_name']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['contact']) ?></td>
                    <td><?= $row['deleted_at'] ?></td>
                    <td class="text-center">
                        <a href="?restore_id=<?= $row['id'] ?>" class="btn btn-sm btn-success rounded-pill px-3">
                            <i class="bi bi-arrow-counterclockwise"></i> Restore
                        </a>
                        <a href="?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-danger rounded-pill px-3">
                            <i class="bi bi-trash"></i> Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center text-muted">No deleted employers found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Chart -->
<div class="chart-container">
    <h4 class="mb-3 fw-bold"><i class="bi bi-bar-chart-fill text-primary"></i> Weekly Employer Deletion Analysis</h4>
    <canvas id="deleteChart" height="150"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const ctx = document.getElementById('deleteChart').getContext('2d');
const grad = ctx.createLinearGradient(0,0,0,400);
grad.addColorStop(0,'rgba(59, 130, 246, 0.9)');
grad.addColorStop(1,'rgba(59, 130, 246, 0.4)');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [{
            label: 'Deleted Employers',
            data: <?= json_encode($chart_counts) ?>,
            backgroundColor: grad,
            borderRadius: 12
        }]
    },
    options: {
        responsive: true,
        animation: { duration: 1000, easing: 'easeOutQuart' },
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        return 'Deleted: ' + ctx.formattedValue;
                    }
                }
            }
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 12 } } },
            y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 12 } } }
        }
    }
});
</script>

</body>
</html>
