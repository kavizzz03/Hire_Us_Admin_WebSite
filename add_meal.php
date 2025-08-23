<?php
// Show all errors for debugging - remove on production
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials - UPDATE your DB HOST here!
$servername = "localhost";   // <-- Replace with your Hostinger MySQL host (not localhost)
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch deleted workers log
$sql = "SELECT * FROM workers_delete_log ORDER BY deleted_at DESC";
$result = $conn->query($sql);

// Fetch weekly deletion counts
$weeklyQuery = "
    SELECT 
        YEARWEEK(deleted_at, 1) AS week,
        COUNT(*) AS deleted_count
    FROM workers_delete_log
    GROUP BY YEARWEEK(deleted_at, 1)
    ORDER BY week ASC
";
$weeklyResult = $conn->query($weeklyQuery);

$weeks = [];
$counts = [];
while ($row = $weeklyResult->fetch_assoc()) {
    $weeks[] = 'Week ' . substr($row['week'], 4);
    $counts[] = $row['deleted_count'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Deleted Workers Report</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="container py-4">

<h2 class="mb-4">Deleted Workers Log</h2>

<a href="export_deleted_workers.php" class="btn btn-success mb-3">Export CSV</a>

<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>Full Name</th>
            <th>Username</th>
            <th>Contact</th>
            <th>Email</th>
            <th>ID Number</th>
            <th>Job Title</th>
            <th>Deleted By</th>
            <th>Deleted At</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['fullName']) ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['contactNumber']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['idNumber']) ?></td>
                <td><?= htmlspecialchars($row['jobTitle']) ?></td>
                <td><?= htmlspecialchars($row['deleted_by']) ?></td>
                <td><?= htmlspecialchars($row['deleted_at']) ?></td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="8" class="text-center">No deleted workers found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<h3 class="mt-5">Weekly Deletion Analysis</h3>
<canvas id="deletionChart" height="100"></canvas>

<script>
    const ctx = document.getElementById('deletionChart').getContext('2d');
    const deletionChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($weeks) ?>,
            datasets: [{
                label: 'Workers Deleted Per Week',
                data: <?= json_encode($counts) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Deletions' }
                },
                x: {
                    title: { display: true, text: 'Week' }
                }
            }
        }
    });
</script>

</body>
</html>
