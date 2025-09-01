<?php
session_start();
require 'db.php';

if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: dashboard.php');
    exit();
}

// Get login counts per admin
$sql = "
SELECT au.full_name, COUNT(al.id) AS login_count 
FROM admin_users au 
LEFT JOIN admin_logins al ON au.id = al.admin_id 
GROUP BY au.id ORDER BY login_count DESC
";

$result = $conn->query($sql);

$names = [];
$counts = [];
while ($row = $result->fetch_assoc()) {
    $names[] = $row['full_name'];
    $counts[] = (int)$row['login_count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Login Stats</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div style="width: 600px; margin: 40px auto;">
    <h2>Admin Login Counts</h2>
    <canvas id="loginChart"></canvas>
    <a href="dashboard.php">Back to Dashboard</a>
</div>

<script>
const ctx = document.getElementById('loginChart').getContext('2d');
const chart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($names) ?>,
        datasets: [{
            label: 'Number of Logins',
            data: <?= json_encode($counts) ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: { beginAtZero: true, precision:0 }
        }
    }
});
</script>
</body>
</html>
