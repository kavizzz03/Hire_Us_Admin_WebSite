<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$msg = '';
$msgType = '';

// RESTORE JOB
if(isset($_GET['restore_id'])){
    $id = intval($_GET['restore_id']);
    $row = $conn->query("SELECT * FROM deleted_jobs WHERE id=$id")->fetch_assoc();
    if($row){
        $conn->query("INSERT INTO jobs (job_title, vacancies, time_range, location, basic_salary, ot_salary, requirements, job_date, pickup_location, contact_info, email, employee_id)
                      VALUES ('{$row['job_title']}', '{$row['vacancies']}', '{$row['time_range']}', '{$row['location']}', '{$row['basic_salary']}', '{$row['ot_salary']}', '{$row['requirements']}', '{$row['job_date']}', '{$row['pickup_location']}', '{$row['contact_info']}', '{$row['email']}', '{$row['employee_id']}')");
        $conn->query("DELETE FROM deleted_jobs WHERE id=$id");
        $msg = "Job restored successfully!";
        $msgType = "success";
    } else {
        $msg = "Record not found!";
        $msgType = "error";
    }
}

// PERMANENT DELETE
if(isset($_GET['permanent_delete_id'])){
    $id = intval($_GET['permanent_delete_id']);
    $conn->query("DELETE FROM deleted_jobs WHERE id=$id");
    $msg = "Job permanently deleted!";
    $msgType = "success";
}

// DATE FILTER
$filter = "";
$reportTitle = '';
if(isset($_GET['start_date']) && isset($_GET['end_date'])){
    $start = $_GET['start_date'];
    $end = $_GET['end_date'];
    $filter = " WHERE deleted_at BETWEEN '$start 00:00:00' AND '$end 23:59:59'";
    $reportTitle = "Deleted Jobs Report from $start to $end";
}

// FETCH DELETED JOBS
$sql = "SELECT * FROM deleted_jobs";
if($filter) $sql .= $filter;
$result = $conn->query($sql);

// Prepare chart data
$chartData = [];
$chartQuery = "SELECT DATE(deleted_at) as del_date, COUNT(*) as count FROM deleted_jobs";
if($filter) $chartQuery .= $filter;
$chartQuery .= " GROUP BY DATE(deleted_at) ORDER BY DATE(deleted_at)";
$chartResult = $conn->query($chartQuery);
if($chartResult && $chartResult->num_rows>0){
    while($r = $chartResult->fetch_assoc()){
        $chartData[] = ['date'=>$r['del_date'], 'count'=>$r['count']];
    }
}

// JSON for JS
$chartDataJson = json_encode($chartData);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Deleted Jobs History</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="sidebar.css">
<link rel="icon" type="image/png" href="icon2.png">

<style>
body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display:flex; background:#f4f6f8; }
.main-content { flex:1; padding:20px; transition: margin-left 0.3s ease; }
.table-container { background:#fff; border-radius:12px; padding:15px; margin-bottom:20px; overflow-x:auto; }
.table thead { background: linear-gradient(135deg,#007bff,#6610f2); color:white; font-size:0.85rem; }
.table tbody tr:hover { background: rgba(0,123,255,0.05); }
.table td, .table th { vertical-align: middle; font-size:0.82rem; padding:6px; }
.btn-sm { font-size:0.75rem; }
h1 { font-weight:700; color:#007bff; margin-bottom:20px; font-size:1.5rem; }
.footer { margin-top:50px; padding:15px 0; background:#fff; font-size:0.9rem; color:#6c757d; text-align:center; border-radius:0 0 12px 12px; }

/* Responsive adjustments */
@media(max-width: 768px){
    .table td, .table th { font-size:0.7rem; padding:4px; }
    h1 { font-size:1.2rem; }
}
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
  <button id="sidebarToggle" class="btn btn-outline-primary mb-3 d-md-none"><i class="bi bi-list"></i> Menu</button>
  <div class="mb-3"><a href="job_management.php" class="btn btn-outline-primary"><i class="bi bi-arrow-left"></i> Back to Jobs</a></div>
  <h1 class="text-center">Deleted Jobs History</h1>

  <!-- Filter -->
  <form class="row mb-4 g-3" method="get">
    <div class="col-md-4 col-6"><input type="date" name="start_date" class="form-control" value="<?= $_GET['start_date']??'' ?>" required></div>
    <div class="col-md-4 col-6"><input type="date" name="end_date" class="form-control" value="<?= $_GET['end_date']??'' ?>" required></div>
    <div class="col-md-4 col-12"><button type="submit" class="btn btn-success w-100"><i class="bi bi-search"></i> Generate Report</button></div>
  </form>

  <!-- Dynamic Search -->
  <div class="mb-3">
    <input type="text" id="tableSearch" class="form-control" placeholder="Search deleted jobs...">
  </div>

  <!-- Table -->
  <div class="table-container">
    <table class="table table-bordered table-striped align-middle" id="deletedJobsTable" style="width:100%">
      <thead>
        <tr>
          <th>ID</th><th>Job Title</th><th>Vacancies</th><th>Time Range</th><th>Location</th>
          <th>Basic Salary</th><th>OT Salary</th><th>Requirements</th><th>Job Date</th>
          <th>Pickup Location</th><th>Contact Info</th><th>Email</th><th>Employee ID</th>
          <th>Deleted At</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if($result && $result->num_rows>0): while($row=$result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td><td><?= $row['job_title']?:'-' ?></td><td><?= $row['vacancies']?:'-' ?></td><td><?= $row['time_range']?:'-' ?></td><td><?= $row['location']?:'-' ?></td>
          <td><?= $row['basic_salary']?:'-' ?></td><td><?= $row['ot_salary']?:'-' ?></td><td><?= $row['requirements']?:'-' ?></td><td><?= $row['job_date']?:'-' ?></td>
          <td><?= $row['pickup_location']?:'-' ?></td><td><?= $row['contact_info']?:'-' ?></td><td><?= $row['email']?:'-' ?></td><td><?= $row['employee_id']?:'-' ?></td>
          <td><?= $row['deleted_at']?:'-' ?></td>
          <td class="text-center">
            <a href="?restore_id=<?= $row['id'] ?>" class="btn btn-success btn-sm mb-1"><i class="bi bi-arrow-counterclockwise"></i> Restore</a>
            <a href="?permanent_delete_id=<?= $row['id'] ?>" class="btn btn-danger btn-sm"><i class="bi bi-x-circle"></i> Delete</a>
          </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="15" class="text-center text-muted fst-italic">No deleted jobs found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="text-end mb-4">
    <button onclick="printReport()" class="btn btn-secondary"><i class="bi bi-printer"></i> Print Report</button>
  </div>

  <footer class="footer">&copy; <?= date('Y') ?> Hire Us System. All rights reserved.</footer>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
<?php if($msg): ?>
Swal.fire({ icon: '<?= $msgType ?>', title: '<?= addslashes($msg) ?>', showConfirmButton: true, timer: 2500 });
<?php endif; ?>

$(document).ready(function() {
    var table = $('#deletedJobsTable').DataTable({
        responsive:true,
        dom:'Bfrtip',
        buttons:['copy','excel','csv','pdf','print'],
        order:[[0,'desc']]
    });

    // Dynamic search
    $('#tableSearch').on('keyup', function() {
        table.search(this.value).draw();
    });
});

document.getElementById('sidebarToggle').addEventListener('click', ()=>{
    document.body.classList.toggle('sidebar-hidden');
});

function printReport(){
    const chartData = <?= $chartDataJson ?>;
    const printWindow = window.open('', '', 'width=1200,height=800');
    let html = `
    <html><head><title>Hire Us System - Deleted Jobs Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{font-family:Arial, sans-serif;}
        header{text-align:center; margin-bottom:15px;}
        header h1{color:#007bff; margin:0; font-size:20pt;}
        header p{font-size:12pt; margin:0;}
        table{width:100%; border-collapse:collapse; font-size:10pt;}
        th, td{border:1px solid #000; padding:4px; text-align:left;}
        thead{background:#007bff; color:#fff;}
        tbody tr:nth-child(even){background:#f2f2f2;}
        canvas{max-width:100%; margin:10px 0;}
    </style>
    </head><body>
    <header><h1>Hire Us System</h1><p><?= $reportTitle ?: "Hire Us System-Deleted Jobs Report" ?></p></header>
    <canvas id="printChart" height="100"></canvas>
    ${document.querySelector('.table-container').innerHTML}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"><\/script>
    <script>
        const ctx = document.getElementById('printChart').getContext('2d');
        new Chart(ctx,{
            type:'bar',
            data:{
                labels:${JSON.stringify(array_column($chartData,'date'))},
                datasets:[{
                    label:'Deleted Jobs per Day',
                    data:${JSON.stringify(array_column($chartData,'count'))},
                    backgroundColor:'rgba(54, 162, 235, 0.6)',
                    borderColor:'rgba(54, 162, 235, 1)',
                    borderWidth:1
                }]
            },
            options:{responsive:true, plugins:{legend:{display:true}}, scales:{y:{beginAtZero:true, stepSize:1}}}
        });
    <\/script>
    </body></html>`;
    printWindow.document.write(html);
    printWindow.document.close();
    printWindow.onload = function(){ printWindow.print(); printWindow.close(); };
}
</script>
</body>
</html>
