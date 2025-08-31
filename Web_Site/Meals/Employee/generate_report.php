<?php
$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("DB Connection failed: " . $conn->connect_error);
}

$worker_ids = $_POST['worker_ids'] ?? [];
if (empty($worker_ids) || !is_array($worker_ids)) {
  die("No workers selected.");
}

$ids = implode(',', array_map('intval', $worker_ids));
$sql = "SELECT * FROM workers WHERE id IN ($ids) ORDER BY fullName ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Workers Report - Hire Us System</title>
    <link rel="icon" type="image/png" href="icon2.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f8f9fa;
      padding: 20px;
    }
    .letterhead {
      text-align: center;
      border-bottom: 4px solid #0d6efd;
      padding-bottom: 15px;
      margin-bottom: 25px;
    }
    .letterhead h1 {
      color: #0d6efd;
      font-weight: 700;
    }
    .letterhead p {
      font-style: italic;
      font-size: 1.1rem;
    }
    .table thead {
      background-color: #0d6efd;
      color: white;
    }
    .rating-entry {
      background: #ffffff;
      border: 1px solid #dee2e6;
      border-radius: 6px;
      padding: 10px;
      margin-bottom: 10px;
    }
    .footer {
      margin-top: 40px;
      border-top: 3px solid #0d6efd;
      padding-top: 15px;
      text-align: center;
      color: #6c757d;
      font-size: 0.9rem;
    }
    @media print {
      .no-print {
        display: none;
      }
    }
  </style>
</head>
<body onload="window.print()">

  <div class="letterhead">
    <h1>Hire Us System</h1>
    <p>📄 Registered Workers Report</p>
  </div>

  <h3 class="text-primary mb-3">🧾 Selected Workers Report</h3>
  <table class="table table-bordered align-middle table-hover">
    <thead>
      <tr>
        <th>Full Name</th>
        <th>Username</th>
        <th>Contact</th>
        <th>Email</th>
        <th>ID Number</th>
        <th>Job Title</th>
        <th>Status</th>
        <th>Ratings & Feedback</th>
      </tr>
    </thead>
    <tbody>
    <?php while ($worker = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($worker['fullName']) ?></td>
        <td><?= htmlspecialchars($worker['username']) ?></td>
        <td><?= htmlspecialchars($worker['contactNumber']) ?></td>
        <td><?= htmlspecialchars($worker['email']) ?></td>
        <td><?= htmlspecialchars($worker['idNumber']) ?></td>
        <td><?= htmlspecialchars($worker['jobTitle']) ?></td>
        <td><?= htmlspecialchars(ucwords(str_replace('_',' ', $worker['status']))) ?></td>
        <td>
          <?php
          $stmt = $conn->prepare("SELECT rating, work_experience, feedback, job_title, company_name, duration, rated_by FROM worker_ratings WHERE worker_id = ?");
          $stmt->bind_param("i", $worker['id']);
          $stmt->execute();
          $res = $stmt->get_result();
          if ($res->num_rows > 0) {
            while ($r = $res->fetch_assoc()) {
              echo '<div class="rating-entry">';
              echo '<strong>⭐ Rating:</strong> ' . htmlspecialchars($r['rating']) . '/5<br>';
              echo '<strong>💼 Work Experience:</strong> ' . htmlspecialchars($r['work_experience']) . '<br>';
              echo '<strong>💬 Feedback:</strong> ' . htmlspecialchars($r['feedback']) . '<br>';
              echo '<strong>🏢 Job:</strong> ' . htmlspecialchars($r['job_title']) . ' at ' . htmlspecialchars($r['company_name']) . '<br>';
              echo '<strong>📅 Duration:</strong> ' . htmlspecialchars($r['duration']) . '<br>';
              echo '<strong>👤 Rated By:</strong> ' . htmlspecialchars($r['rated_by']);
              echo '</div>';
            }
          } else {
            echo '<span class="text-muted">No ratings</span>';
          }
          ?>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>

  <div class="footer">
    &copy; <?= date('Y') ?> Hire Us System. All rights reserved.
  </div>

</body>
</html>
