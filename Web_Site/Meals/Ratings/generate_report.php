<?php
// DB Connection
$conn = new mysqli("localhost", "u569550465_math_rakusa", "Sithija2025#", "u569550465_hireme");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if (!isset($_POST['worker_ids']) || !is_array($_POST['worker_ids']) || count($_POST['worker_ids']) == 0) {
    die("No workers selected.");
}

$worker_ids = array_map('intval', $_POST['worker_ids']);
$ids_placeholder = implode(',', $worker_ids);

// Fetch workers info
$sql_workers = "SELECT id, fullName FROM workers WHERE id IN ($ids_placeholder)";
$res_workers = $conn->query($sql_workers);
if (!$res_workers || $res_workers->num_rows == 0) {
    die("Selected workers not found.");
}

$workers = [];
while ($w = $res_workers->fetch_assoc()) {
    $workers[$w['id']] = $w['fullName'];
}

// Fetch ratings
$sql_ratings = "SELECT * FROM worker_ratings WHERE worker_id IN ($ids_placeholder) ORDER BY worker_id, id";
$res_ratings = $conn->query($sql_ratings);

$ratingsByWorker = [];
while ($row = $res_ratings->fetch_assoc()) {
    $ratingsByWorker[$row['worker_id']][] = $row;
}

// Calculate averages per worker
$summary = [];
foreach ($workers as $id => $name) {
    $ratings = $ratingsByWorker[$id] ?? [];
    $count = count($ratings);
    $sum = 0;
    foreach ($ratings as $r) {
        $sum += (int)$r['rating'];
    }
    $avg = $count > 0 ? round($sum / $count, 2) : 0;
    $summary[$id] = ['average' => $avg, 'count' => $count];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>📊 Worker Ratings Report | Hire Us Platform</title>
  <link rel="icon" type="image/png" href="icon2.png">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600&family=Roboto&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f5f7fa;
      color: #333;
      padding: 20px;
    }
    .letterhead {
      max-width: 900px;
      margin: 0 auto 40px auto;
      background: #ffffff;
      border-radius: 15px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      padding: 25px 40px;
      display: flex;
      align-items: center;
      gap: 20px;
    }
    .logo {
      width: 100px;
      height: 100px;
      background: linear-gradient(135deg, #0d6efd, #6610f2);
      border-radius: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-weight: 700;
      font-size: 36px;
      box-shadow: 0 3px 8px rgba(13, 110, 253, 0.4);
    }
    .letterhead-text h1 {
      margin: 0;
      font-weight: 600;
      font-size: 2.2rem;
      color: #0d6efd;
    }
    .letterhead-text p {
      margin-top: 4px;
      font-size: 1rem;
      color: #555;
    }
    .print-btn {
      position: fixed;
      top: 25px;
      right: 25px;
      z-index: 9999;
      background: #0d6efd;
      border: none;
      border-radius: 50%;
      width: 52px;
      height: 52px;
      color: white;
      font-size: 1.4rem;
      box-shadow: 0 3px 8px rgba(13, 110, 253, 0.6);
      cursor: pointer;
    }
    .print-btn:hover {
      background: #084cd0;
    }
    .report-card {
      max-width: 900px;
      margin: 0 auto 50px auto;
      background: white;
      border-radius: 15px;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
      padding: 30px 40px;
    }
    .worker-name {
      font-weight: 700;
      color: #6610f2;
      font-size: 1.8rem;
      margin-bottom: 10px;
      border-bottom: 3px solid #0d6efd;
      padding-bottom: 6px;
    }
    .summary-info {
      font-weight: 600;
      color: #444;
      margin-bottom: 20px;
    }
    .rating-badge {
      background: #ffc107;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 1rem;
      font-weight: bold;
      color: #000;
      margin-left: 10px;
    }
    thead {
      background: linear-gradient(90deg, #6610f2, #0d6efd);
      color: white;
    }
    tbody tr:hover {
      background: #f0f4ff;
    }
    .chart-container {
      margin-bottom: 30px;
    }
    footer {
      max-width: 900px;
      margin: 50px auto 0 auto;
      text-align: center;
      font-weight: 300;
      color: #777;
      font-size: 0.9rem;
    }
    @media print {
      body {
        background: white;
        padding: 0;
      }
      .print-btn {
        display: none !important;
      }
      .letterhead, .report-card, footer {
        box-shadow: none !important;
        border-radius: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        max-width: 100% !important;
      }
    }
  </style>
</head>
<body>

<button class="print-btn" title="Print Report" onclick="window.print()">🖨️</button>

<!-- Letterhead -->
<div class="letterhead">
  <div class="logo" aria-label="HireMe Logo">HM</div>
  <div class="letterhead-text">
    <h1>HireMe Platform</h1>
    <p>Comprehensive Worker Ratings Report</p>
    <small>Generated on <?= date('F j, Y, g:i a') ?></small>
  </div>
</div>

<!-- Worker Reports -->
<?php foreach ($workers as $id => $name): ?>
  <?php
    $ratings = $ratingsByWorker[$id] ?? [];
    $avg = $summary[$id]['average'];
    $count = $summary[$id]['count'];
  ?>
  <div class="report-card">
    <div class="worker-name"><?= htmlspecialchars($name) ?></div>
    <div class="summary-info">
      Total Ratings: <strong><?= $count ?></strong> |
      Average: <span class="rating-badge"><?= $avg ?>/5</span>
    </div>

    <div class="chart-container">
      <canvas id="chart-<?= $id ?>" height="250"></canvas>
    </div>

    <h5>Detailed Ratings</h5>
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead>
          <tr>
            <th>Rated By</th>
            <th>Rating</th>
            <th>Feedback</th>
            <th>Job Title</th>
            <th>Company</th>
            <th>Duration</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($ratings)): ?>
          <tr><td colspan="6" class="text-center text-muted">No ratings available.</td></tr>
        <?php else: ?>
          <?php foreach ($ratings as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['rated_by']) ?></td>
              <td><?= htmlspecialchars($r['rating']) ?></td>
              <td><?= htmlspecialchars($r['feedback']) ?></td>
              <td><?= htmlspecialchars($r['job_title']) ?></td>
              <td><?= htmlspecialchars($r['company_name']) ?></td>
              <td><?= htmlspecialchars($r['duration']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endforeach; ?>

<footer>&copy; <?= date('Y') ?> HireMe Platform. All rights reserved.</footer>

<script>
<?php foreach ($workers as $id => $name):
  $ratings = $ratingsByWorker[$id] ?? [];
  $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
  foreach ($ratings as $r) {
      $star = (int)$r['rating'];
      if (isset($distribution[$star])) $distribution[$star]++;
  }
?>
new Chart(document.getElementById('chart-<?= $id ?>').getContext('2d'), {
  type: 'bar',
  data: {
    labels: ['1 ⭐', '2 ⭐', '3 ⭐', '4 ⭐', '5 ⭐'],
    datasets: [{
      label: 'Rating Distribution',
      data: [
        <?= $distribution[1] ?>,
        <?= $distribution[2] ?>,
        <?= $distribution[3] ?>,
        <?= $distribution[4] ?>,
        <?= $distribution[5] ?>
      ],
      backgroundColor: ['#dc3545', '#fd7e14', '#ffc107', '#198754', '#0d6efd'],
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: {
        beginAtZero: true,
        ticks: { precision: 0 }
      }
    },
    plugins: {
      legend: { display: false },
      title: {
        display: true,
        text: 'Ratings Breakdown',
        font: { size: 16 }
      }
    }
  }
});
<?php endforeach; ?>
</script>

</body>
</html>
