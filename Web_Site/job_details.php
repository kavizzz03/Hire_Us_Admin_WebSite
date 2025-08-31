<?php
$servername = "localhost";
$db_username = "u569550465_math_rakusa";
$db_password = "Sithija2025#";
$db_name = "u569550465_hireme";

$conn = new mysqli($servername, $db_username, $db_password, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$worker_id = $_GET['worker_id'] ?? '';

$sql = "SELECT w.*, d.*
        FROM workers w
        LEFT JOIN deleted_jobs d ON w.jobTitle = d.job_title
        WHERE w.idNumber = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $worker_id);
$stmt->execute();
$result = $stmt->get_result();
$details = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Details</title>
    <style>
        body { font-family: Arial, sans-serif; background: #111; color: gold; padding: 20px; }
        h2 { text-align: center; }
        .card { border: 1px solid gold; padding: 20px; border-radius: 10px; width: 70%; margin: auto; background: #222; }
        .section { margin-bottom: 15px; }
        strong { color: #ffcc00; }
    </style>
</head>
<body>
    <h2>Worker & Job Details</h2>
    <?php if ($details): ?>
        <div class="card">
            <div class="section"><strong>Worker ID:</strong> <?php echo htmlspecialchars($details['idNumber']); ?></div>
            <div class="section"><strong>Name:</strong> <?php echo htmlspecialchars($details['fullName']); ?></div>
            <div class="section"><strong>Contact:</strong> <?php echo htmlspecialchars($details['contactNumber']); ?></div>
            <div class="section"><strong>Email:</strong> <?php echo htmlspecialchars($details['email']); ?></div>
            <div class="section"><strong>Bank:</strong> <?php echo htmlspecialchars($details['bankName'] . ' - ' . $details['bankBranch']); ?></div>

            <hr>

            <div class="section"><strong>Job Title:</strong> <?php echo htmlspecialchars($details['job_title']); ?></div>
            <div class="section"><strong>Location:</strong> <?php echo htmlspecialchars($details['location']); ?></div>
            <div class="section"><strong>Salary:</strong> <?php echo htmlspecialchars($details['basic_salary']); ?></div>
            <div class="section"><strong>OT Salary:</strong> <?php echo htmlspecialchars($details['ot_salary']); ?></div>
            <div class="section"><strong>Job Date:</strong> <?php echo htmlspecialchars($details['job_date']); ?></div>
            <div class="section"><strong>Requirements:</strong> <?php echo htmlspecialchars($details['requirements']); ?></div>
            <div class="section"><strong>Pickup Location:</strong> <?php echo htmlspecialchars($details['pickup_location']); ?></div>
            <div class="section"><strong>Contact Info:</strong> <?php echo htmlspecialchars($details['contact_info']); ?></div>
        </div>
    <?php else: ?>
        <p style="text-align:center;">No details found for this worker.</p>
    <?php endif; ?>
</body>
</html>

<?php
$conn->close();
?>
