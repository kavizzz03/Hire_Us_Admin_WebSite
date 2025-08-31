<?php
require 'db.php';

// Get selected employer IDs
$employer_ids = $_GET['employer_ids'] ?? [];

if(empty($employer_ids)){
    die("No employers selected for report.");
}

// Sanitize IDs
$ids = implode(',', array_map('intval', $employer_ids));

// Fetch employer data and job stats
$sql = "SELECT 
            e.id,
            e.company_name,
            e.name,
            e.email,
            e.contact,
            COUNT(j.id) AS total_jobs,
            COUNT(dj.id) AS deleted_jobs
        FROM employers e
        LEFT JOIN jobs j ON j.employee_id = e.id
        LEFT JOIN deleted_jobs dj ON dj.employee_id = e.id
        WHERE e.id IN ($ids)
        GROUP BY e.id";

$result = $conn->query($sql);

$report_data = [];
while($row = $result->fetch_assoc()){
    $report_data[] = $row;
}

$chart_labels = array_column($report_data, 'company_name');
$total_jobs = array_column($report_data, 'total_jobs');
$deleted_jobs = array_column($report_data, 'deleted_jobs');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employer Job Report</title>
  <link rel="icon" type="image/png" href="icon2.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
body { background: #f5f7fa; font-family: 'Segoe UI', sans-serif; margin:0; }
.header { background: #343a40; color: #fff; padding: 20px 0; text-align:center; }
.header h1 { margin:0; font-size:2rem; font-weight:700; }
.header p { margin:0; font-size:1rem; }
.report-container { margin: 30px auto; max-width: 1200px; }
h2, h4 { font-weight: 600; margin-bottom: 20px; text-align:center; }
.table-wrapper { background: #fff; padding: 20px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); margin-bottom:30px; }
#reportChart { background: linear-gradient(to right, #f0f4f8, #e6eefc); border-radius: 15px; padding: 20px; margin-bottom:30px; }
.btn-print { border-radius: 50px; padding: 8px 20px; display: block; margin: 20px auto; }
.hire-us { background: #ffd700; padding: 20px; text-align:center; border-radius: 15px; margin-bottom:30px; }
.footer { background: #343a40; color: #fff; padding: 15px; text-align:center; border-radius: 10px; margin-top:30px; }
@media print { .btn-print, .hire-us { display: none; } }
  footer.footer {
    margin-top: 50px;
    padding: 15px 0;
    background: #fff;
    box-shadow: 0 -2px 6px rgba(0,0,0,0.05);
    font-size: 0.9rem;
    color: #6c757d;
    text-align: center;
    border-radius: 0 0 12px 12px;
  }
</style>
</head>
<body>

<!-- Header -->
<div class="header animate__animated animate__fadeInDown">
    <h1>Hire Me - Employer Job Report</h1>
    <p>Comprehensive analysis of employer activities and job postings</p>
</div>

<div class="container report-container animate__animated animate__fadeInUp">
    
    <!-- Hire Us Info -->
    <div class="hire-us animate__animated animate__fadeInUp">
        <h4>Need assistance with recruitment or job management?</h4>
        <p>Contact <strong>Hire US</strong> team at <a href="mailto:support@hireme.com">support@hireme.com</a> or call <strong>+94 123 456 789</strong></p>
        <p>We provide full-service recruitment and dashboard management for your company.</p>
    </div>

    <!-- Employer Table -->
    <div class="table-wrapper">
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Company Name</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Total Jobs</th>
                    <th>Deleted Jobs</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($report_data as $r): ?>
                    <tr>
                        <td><?= $r['company_name'] ?></td>
                        <td><?= $r['name'] ?></td>
                        <td><?= $r['email'] ?></td>
                        <td><?= $r['contact'] ?></td>
                        <td><?= $r['total_jobs'] ?></td>
                        <td><?= $r['deleted_jobs'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Chart -->
    <canvas id="reportChart" height="200"></canvas>

    <!-- Print Button -->
    <button class="btn btn-success btn-print" onclick="window.print()">Print / Save PDF</button>
</div>

<!-- Footer -->
<footer class="footer">
  &copy; <?= date('Y') ?> Hire Us System. All rights reserved.
</footer>

<script>
const ctx = document.getElementById('reportChart').getContext('2d');
const gradient1 = ctx.createLinearGradient(0,0,0,400);
gradient1.addColorStop(0,'rgba(54, 162, 235, 0.8)');
gradient1.addColorStop(1,'rgba(54, 162, 235, 0.3)');

const gradient2 = ctx.createLinearGradient(0,0,0,400);
gradient2.addColorStop(0,'rgba(255, 99, 132, 0.8)');
gradient2.addColorStop(1,'rgba(255, 99, 132, 0.3)');

const reportChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [
            {
                label: 'Total Jobs',
                data: <?= json_encode($total_jobs) ?>,
                backgroundColor: gradient1,
                borderRadius: 10
            },
            {
                label: 'Deleted Jobs',
                data: <?= json_encode($deleted_jobs) ?>,
                backgroundColor: gradient2,
                borderRadius: 10
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true, stepSize: 1 }, x: { grid: { display: false } } },
        animation: { duration: 1500, easing: 'easeOutQuart' }
    }
});
</script>

</body>
</html>
