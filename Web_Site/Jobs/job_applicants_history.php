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

if(isset($_GET['restore_id'])){
    $id = intval($_GET['restore_id']);
    $row = $conn->query("SELECT * FROM deleted_job_applications WHERE id=$id")->fetch_assoc();
    if($row){
        $jobExists = $conn->query("SELECT id FROM jobs WHERE id='{$row['job_id']}'")->num_rows > 0;
        $workerExists = $conn->query("SELECT idNumber FROM workers WHERE idNumber='{$row['worker_id_number']}'")->num_rows > 0;

        if($jobExists && $workerExists){
            $conn->query("INSERT INTO job_applications (job_id, worker_id_number, applied_at) 
                          VALUES ('{$row['job_id']}','{$row['worker_id_number']}','{$row['applied_at']}')");
            $conn->query("DELETE FROM deleted_job_applications WHERE id=$id");
            $msg = "Application restored successfully!";
            $msgType = "success";
        } else {
            $msg = "Cannot restore: Job or Worker does not exist!";
            $msgType = "error";
        }
    } else {
        $msg = "Record not found!";
        $msgType = "error";
    }
}

$filter = "";
$reportTitle = '';
if(isset($_GET['start_date']) && isset($_GET['end_date'])){
    $start = $_GET['start_date'];
    $end = $_GET['end_date'];
    $filter = " WHERE deleted_at BETWEEN '$start 00:00:00' AND '$end 23:59:59'";
    $reportTitle = "Report from $start to $end";
}

$sql = "SELECT d.id, d.job_id, d.worker_id_number, d.applied_at, d.deleted_at,
        COALESCE(w.fullName,'-') AS worker_name, COALESCE(w.jobTitle,'-') AS worker_job,
        COALESCE(j.job_title,'-') AS job_title, COALESCE(j.location,'-') AS location
        FROM deleted_job_applications d
        LEFT JOIN workers w ON d.worker_id_number = w.idNumber COLLATE utf8mb4_unicode_ci
        LEFT JOIN jobs j ON d.job_id = j.id";
if($filter) $sql .= $filter;
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Job Applicants History</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="sidebar.css">
<link rel="icon" type="image/png" href="icon2.png">
<style>
body { margin:0; font-family:'Segoe UI', sans-serif; display:flex; background:#f0f4f8; }
.main-content { flex:1; padding:20px; overflow-x:hidden; transition: margin-left 0.3s ease; }
.table-container { background:#fff; border-radius:15px; padding:20px; box-shadow:0 12px 28px rgba(0,123,255,0.15); margin-bottom:20px; }
.table thead { background: linear-gradient(45deg,#007bff,#6610f2); color:white; }
.table tbody tr:hover { background: rgba(0,123,255,0.05); transform:scale(1.01); transition: transform 0.3s ease, background 0.3s ease; }
.btn-sm { font-size:0.85rem; font-weight:600; transition: transform 0.3s ease, box-shadow 0.3s ease; }
.btn-sm:hover { transform: scale(1.05); box-shadow: 0 6px 12px rgba(0,123,255,0.3); }
.footer { margin-top:50px; padding:15px 0; background:#fff; box-shadow:0 -2px 6px rgba(0,0,0,0.05); font-size:0.9rem; color:#6c757d; text-align:center; border-radius:0 0 12px 12px; }
#sidebarToggle { display:none; margin-bottom:15px; }
@media(max-width:992px){ #sidebarToggle { display:inline-block; } }
body.sidebar-hidden #sidebar { transform:translateX(-250px); position:fixed; z-index:1000; }

.fade-out { animation: fadeOut 0.6s forwards; }
@keyframes fadeOut { from { opacity:1; } to { opacity:0; height:0; margin:0; padding:0; } }

/* Print styling */
@media print {
  body * { visibility:hidden; }
  #printable, #printable * { visibility:visible; }
  #printable { position:absolute; left:0; top:0; width:100%; padding:0; margin:0; }
  table { width:100%; border-collapse: collapse; font-size:12pt; }
  th, td { border: 1px solid #000; padding: 8px; text-align:left; }
  thead { background-color:#007bff; color:#fff; }
  tbody tr:nth-child(even) { background-color:#f2f2f2; }
}
#printable h2 { text-align:center; margin-bottom:20px; font-size:20pt; font-weight:bold; text-transform:uppercase; }
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">

  <button id="sidebarToggle" class="btn btn-outline-primary mb-3"><i class="bi bi-list"></i> Menu</button>

  <!-- Back Button -->
  <div class="mb-3">
    <a href="job_applicants.php" class="btn btn-outline-primary">
      <i class="bi bi-arrow-left"></i> Back to Job Applicants
    </a>
  </div>

  <h1 class="text-center mb-4">Hire Us System - Job Applicants History</h1>

  <!-- Filter Section -->
  <form class="row mb-4" method="get">
    <div class="col-md-4">
      <input type="date" name="start_date" class="form-control" value="<?= isset($_GET['start_date'])?$_GET['start_date']:'' ?>" required>
    </div>
    <div class="col-md-4">
      <input type="date" name="end_date" class="form-control" value="<?= isset($_GET['end_date'])?$_GET['end_date']:'' ?>" required>
    </div>
    <div class="col-md-4">
      <button type="submit" class="btn btn-success w-100"><i class="bi bi-search"></i> Generate Report</button>
    </div>
  </form>

  <!-- Table Section -->
  <div class="table-container">
    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle" id="historyTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Job Title</th>
            <th>Worker Name</th>
            <th>Worker Job</th>
            <th>Job Location</th>
            <th>Applied At</th>
            <th>Deleted At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if($result && $result->num_rows>0): while($row = $result->fetch_assoc()): ?>
          <tr data-id="<?= $row['id'] ?>">
            <td><?= $row['id'] ?></td>
            <td><?= $row['job_title'] ?: '-' ?></td>
            <td><?= $row['worker_name'] ?: '-' ?></td>
            <td><?= $row['worker_job'] ?: '-' ?></td>
            <td><?= $row['location'] ?: '-' ?></td>
            <td><?= $row['applied_at'] ?: '-' ?></td>
            <td><?= $row['deleted_at'] ?: '-' ?></td>
            <td class="text-center">
              <a href="?restore_id=<?= $row['id'] ?>" class="btn btn-success btn-sm restore-btn"><i class="bi bi-arrow-counterclockwise"></i> Restore</a>
              <button class="btn btn-danger btn-sm delete-btn" data-id="<?= $row['id'] ?>"><i class="bi bi-x-circle"></i> Delete</button>
            </td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="8" class="text-center text-muted fst-italic">No history found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Printable Report Section -->
  <div class="text-end mb-5">
    <button onclick="printReport()" class="btn btn-secondary"><i class="bi bi-printer"></i> Print Report</button>
  </div>

  <div id="printable" style="display:none;">
    <h2>Hire Us System - Job Applicants Report</h2>
    <?php if($reportTitle) echo "<p style='text-align:center; font-weight:bold;'>$reportTitle</p>"; ?>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>ID</th>
            <th>Job Title</th>
            <th>Worker Name</th>
            <th>Worker Job</th>
            <th>Job Location</th>
            <th>Applied At</th>
            <th>Deleted At</th>
          </tr>
        </thead>
        <tbody>
        <?php
        if($result && $result->num_rows>0){
            $result->data_seek(0); // reset pointer
            while($row = $result->fetch_assoc()){
                echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['job_title']}</td>
                    <td>{$row['worker_name']}</td>
                    <td>{$row['worker_job']}</td>
                    <td>{$row['location']}</td>
                    <td>{$row['applied_at']}</td>
                    <td>{$row['deleted_at']}</td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='7' class='text-center'>No data available</td></tr>";
        }
        ?>
        </tbody>
      </table>
    </div>
  </div>

  <footer class="footer">&copy; <?= date('Y') ?> Hire Us System. All rights reserved.</footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
<?php if($msg): ?>
Swal.fire({
    icon: '<?= $msgType ?>',
    title: '<?= addslashes($msg) ?>',
    showConfirmButton: true,
    timer: 2500
});
<?php endif; ?>

function printReport(){
    const printable = document.getElementById('printable');
    printable.style.display = 'block';
    window.print();
    printable.style.display = 'none';
}

// Sidebar toggle
document.getElementById('sidebarToggle').addEventListener('click', ()=>{ document.body.classList.toggle('sidebar-hidden'); });

// Permanent Delete with SweetAlert & Animation
document.querySelectorAll('.delete-btn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
        const id = btn.dataset.id;
        const row = document.querySelector(`tr[data-id='${id}']`);
        Swal.fire({
            title: 'Are you sure?',
            text: "This will permanently delete the record!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if(result.isConfirmed){
                fetch(`?permanent_delete_id=${id}`)
                    .then(()=> {
                        row.classList.add('fade-out');
                        setTimeout(()=> row.remove(), 600);
                        Swal.fire('Deleted!','The record has been permanently deleted.','success');
                    })
                    .catch(()=> Swal.fire('Error!','Could not delete record.','error'));
            }
        });
    });
});
</script>

</body>
</html>
<?php $conn->close(); ?>
