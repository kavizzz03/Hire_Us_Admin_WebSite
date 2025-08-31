<?php
$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername,$username,$password,$dbname);
date_default_timezone_set('Asia/Colombo'); // Sri Lanka time

$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;

$sql = "SELECT m.*, j.job_title FROM meals m LEFT JOIN jobs j ON m.job_id=j.id";
if($job_id>0) $sql .= " WHERE m.job_id=$job_id";

$result = $conn->query($sql);

// Export CSV
if(isset($_GET['export']) && $_GET['export']=='csv'){
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="meals_report.csv"');
    $output = fopen('php://output','w');
    fputcsv($output,['ID','Job','Meal Name','Description','Price','Created At']);
    while($row = $result->fetch_assoc()){
        $dt = new DateTime($row['created_at'], new DateTimeZone('UTC'));
        $dt->setTimezone(new DateTimeZone('Asia/Colombo'));
        fputcsv($output,[$row['id'],$row['job_title'],$row['meal_name'],$row['description'],$row['meal_price'],$dt->format('Y-m-d H:i:s')]);
    }
    fclose($output);
    exit();
}

// Print report
if(isset($_GET['print']) && $_GET['print']=='true'){
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Print Meals Report</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            @media print { button { display: none; } }
            .letterhead { text-align:center; margin-bottom:30px; }
            .letterhead h1 { margin:0; font-size:2rem; color:#182848; font-weight:bold; }
            .letterhead p { margin:0; font-size:1rem; color:#4b6cb7; }
        </style>
    </head>
    <body class="p-4">
    <div class="letterhead">
        <h1>HireMe System</h1>
        <p>Meals Management Report</p>
    </div>
    <button onclick="window.print()" class="btn btn-primary mb-3">Print</button>
    <table class="table table-bordered table-striped">
        <thead>
        <tr><th>ID</th><th>Job</th><th>Meal Name</th><th>Description</th><th>Price</th><th>Created At</th></tr>
        </thead>
        <tbody>
        <?php while($row = $result->fetch_assoc()): 
            $dt = new DateTime($row['created_at'], new DateTimeZone('UTC'));
            $dt->setTimezone(new DateTimeZone('Asia/Colombo'));
        ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['job_title']) ?></td>
                <td><?= htmlspecialchars($row['meal_name']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= number_format($row['meal_price'],2) ?></td>
                <td><?= $dt->format('Y-m-d H:i:s') ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </body>
    </html>
    <?php
    exit();
}

// Fetch jobs for dropdown
$jobs = $conn->query("SELECT id, job_title FROM jobs");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Meals Report</title>
<link rel="icon" type="image/png" href="icon2.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.letterhead { text-align:center; margin-bottom:30px; }
.letterhead h1 { margin:0; font-size:2rem; color:#182848; font-weight:bold; }
.letterhead p { margin:0; font-size:1rem; color:#4b6cb7; }
</style>
</head>
<body class="p-4">
<div class="letterhead">
    <h1>HireMe System</h1>
    <p>Meals Management Report</p>
</div>

<form method="get" class="mb-3">
    <label>Filter by Job</label>
    <select name="job_id" class="form-select w-25 d-inline-block me-2">
        <option value="0">All Jobs</option>
        <?php while($job = $jobs->fetch_assoc()): ?>
            <option value="<?= $job['id'] ?>" <?= $job_id==$job['id']?'selected':'' ?>><?= htmlspecialchars($job['job_title']) ?></option>
        <?php endwhile; ?>
    </select>
    <button class="btn btn-primary">Filter</button>
    <a href="?<?= $job_id>0?'job_id='.$job_id.'&':'' ?>export=csv" class="btn btn-success">Export CSV</a>
    <a href="?<?= $job_id>0?'job_id='.$job_id.'&':'' ?>print=true" class="btn btn-secondary">Print Report</a>
</form>

<table class="table table-bordered table-striped">
<thead>
<tr><th>ID</th><th>Job</th><th>Meal Name</th><th>Description</th><th>Price</th><th>Created At</th></tr>
</thead>
<tbody>
<?php while($row = $result->fetch_assoc()): 
    $dt = new DateTime($row['created_at'], new DateTimeZone('UTC'));
    $dt->setTimezone(new DateTimeZone('Asia/Colombo'));
?>
<tr>
<td><?= $row['id'] ?></td>
<td><?= htmlspecialchars($row['job_title']) ?></td>
<td><?= htmlspecialchars($row['meal_name']) ?></td>
<td><?= htmlspecialchars($row['description']) ?></td>
<td><?= number_format($row['meal_price'],2) ?></td>
<td><?= $dt->format('Y-m-d H:i:s') ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</body>
</html>
