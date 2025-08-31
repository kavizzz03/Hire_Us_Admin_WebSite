<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

// Database connection
$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// --- Add Application ---
if(isset($_POST['add_application'])){
    $new_job_id = intval($_POST['new_job_id']);
    $new_worker_id = $conn->real_escape_string($_POST['new_worker_id']);

    $checkJob = $conn->query("SELECT id FROM jobs WHERE id=$new_job_id");
    $checkWorker = $conn->query("SELECT idNumber FROM workers WHERE idNumber='$new_worker_id'");

    if($checkJob->num_rows>0 && $checkWorker->num_rows>0){
        $checkDuplicate = $conn->query("SELECT id FROM job_applications WHERE job_id=$new_job_id AND worker_id_number='$new_worker_id'");
        if($checkDuplicate->num_rows == 0){
            $conn->query("INSERT INTO job_applications (job_id, worker_id_number, applied_at) 
                          VALUES ($new_job_id, '$new_worker_id', NOW())");
            header("Location: job_applicants.php");
            exit();
        } else {
            echo "<script>alert('This worker has already applied to this job.');</script>";
        }
    } else {
        echo "<script>alert('Invalid job or worker selected!');</script>";
    }
}

// --- Delete Application ---
if(isset($_GET['delete_id'])){
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM job_applications WHERE id = $delete_id");
    header("Location: job_applicants.php");
    exit();
}

// --- Update Application ---
if(isset($_POST['update_application'])){
    $update_id = intval($_POST['update_id']);
    $job_id = intval($_POST['job_id']);
    $worker_id = $conn->real_escape_string($_POST['worker_id']);
    $conn->query("UPDATE job_applications SET job_id=$job_id, worker_id_number='$worker_id' WHERE id=$update_id");
    header("Location: job_applicants.php");
    exit();
}

// --- Confirm Appointment ---
if(isset($_GET['confirm_id'])){
    $appId = intval($_GET['confirm_id']);
    $appResult = $conn->query("SELECT job_id, worker_id_number FROM job_applications WHERE id=$appId");
    if($appResult && $appRow = $appResult->fetch_assoc()){
        $job_id = $appRow['job_id'];
        $worker_id = $appRow['worker_id_number'];

        $checkHired = $conn->query("SELECT id FROM job_hires WHERE job_id=$job_id AND id_number='$worker_id'");
        if($checkHired->num_rows == 0){
            $conn->query("INSERT INTO job_hires (job_id, id_number, hired_at) VALUES ($job_id, '$worker_id', NOW())");
            echo "<script>
            Swal.fire({icon:'success',title:'Appointment Confirmed!',text:'Saved to Job Hires table.',timer:2000,showConfirmButton:false});
            </script>";
        } else {
            echo "<script>
            Swal.fire({icon:'warning',title:'Already Hired',text:'This worker is already hired for this job.',timer:2000,showConfirmButton:false});
            </script>";
        }
    }
}

// --- Fetch applications with details ---
$sql = "SELECT ja.id, ja.job_id, ja.worker_id_number, ja.applied_at,
        w.fullName AS worker_name, w.jobTitle AS worker_job,
        j.job_title AS job_title, j.vacancies, j.location
        FROM job_applications ja
        LEFT JOIN workers w 
          ON ja.worker_id_number = w.idNumber COLLATE utf8mb4_unicode_ci
        LEFT JOIN jobs j 
          ON ja.job_id = j.id";
$result = $conn->query($sql);

// --- Fetch jobs & workers for dropdowns ---
$jobsResult = $conn->query("SELECT id, job_title FROM jobs");
$workersResult = $conn->query("SELECT idNumber, fullName FROM workers");

// --- Applicants analysis ---
$analysisQuery = "SELECT j.job_title, COUNT(ja.id) AS total_applicants
                  FROM job_applications ja
                  LEFT JOIN jobs j ON ja.job_id = j.id
                  GROUP BY ja.job_id";
$analysisResult = $conn->query($analysisQuery);
$jobTitles = [];
$applicantsCounts = [];
if($analysisResult){
    while($row = $analysisResult->fetch_assoc()){
        $jobTitles[] = $row['job_title'];
        $applicantsCounts[] = $row['total_applicants'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Job Applicants Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="icon" type="image/png" href="icon2.png">
<link rel="stylesheet" href="sidebar.css">
<style>
body { margin:0; font-family:'Segoe UI', sans-serif; display:flex; background:#f0f4f8; }
.main-content { flex:1; padding:20px; overflow-x:hidden; transition: margin-left 0.3s ease; }
.table-container { background:#fff; border-radius:15px; padding:20px; box-shadow:0 12px 28px rgba(0,123,255,0.15); margin-bottom:50px; }
.table thead { background: linear-gradient(45deg,#007bff,#6610f2); color:white; }
table tbody tr { opacity:0; transform:translateY(15px); animation:fadeSlideIn 0.5s forwards; }
@keyframes fadeSlideIn { to { opacity:1; transform:translateY(0); } }
.btn-sm { font-size:0.9rem; font-weight:600; transition: transform 0.3s ease, box-shadow 0.3s ease; }
.btn-sm:hover { transform: scale(1.08); box-shadow: 0 6px 12px rgba(0,123,255,0.3); }
.chart-container { background:#fff; border-radius:15px; padding:30px; box-shadow:0 12px 28px rgba(102,16,242,0.15); margin-bottom:50px; }
footer.footer { margin-top:50px; padding:15px 0; background:#fff; box-shadow:0 -2px 6px rgba(0,0,0,0.05); font-size:0.9rem; color:#6c757d; text-align:center; border-radius:0 0 12px 12px; }
#sidebarToggle { display:none; margin-bottom:15px; }
@media(max-width:992px){ #sidebarToggle { display:inline-block; } }
body.sidebar-hidden #sidebar { transform:translateX(-250px); position:fixed; z-index:1000; }
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
  <button id="sidebarToggle" class="btn btn-outline-primary mb-3"><i class="bi bi-list"></i> Menu</button>

  <h1 class="text-center mb-4">Job Applicants Management</h1>
  
<div class="d-flex justify-content-between mb-3">
    <input type="text" id="searchInput" class="form-control form-control-lg w-75" placeholder="Search applicants...">
    <div>
      <button class="btn btn-success me-2" id="addApplicationBtn"><i class="bi bi-plus-circle"></i> Add Application</button>
      <a href="job_applicants_history.php" target="_blank" class="btn btn-info"><i class="bi bi-clock-history"></i> Job Applicants History</a>
    </div>
</div>

<div class="table-container">
    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>Job Title</th>
            <th>Worker Name</th>
            <th>Worker Job</th>
            <th>Job Location</th>
            <th>Applied At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if($result && $result->num_rows>0): while($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['job_title']) ?></td>
            <td><?= htmlspecialchars($row['worker_name']) ?></td>
            <td><?= htmlspecialchars($row['worker_job']) ?></td>
            <td><?= htmlspecialchars($row['location']) ?></td>
            <td><?= $row['applied_at'] ?></td>
            <td class="text-center">
              <button class="btn btn-success btn-sm show-worker" data-id="<?= $row['worker_id_number'] ?>"><i class="bi bi-person"></i> Worker</button>
              <button class="btn btn-primary btn-sm show-job" data-id="<?= $row['job_id'] ?>"><i class="bi bi-briefcase"></i> Job</button>
              <button class="btn btn-warning btn-sm edit-app" data-id="<?= $row['id'] ?>" data-job="<?= $row['job_id'] ?>" data-worker="<?= $row['worker_id_number'] ?>"><i class="bi bi-pencil-square"></i> Update</button>
              <a href="?delete_id=<?= $row['id'] ?>" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Delete</a>
              <a href="?confirm_id=<?= $row['id'] ?>" class="btn btn-info btn-sm"><i class="bi bi-check-circle"></i> Confirm</a>
            </td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="7" class="text-center text-muted fst-italic">No applicants found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
</div>

<h3 class="mb-4 mt-5 text-center">Applicants Analysis per Job</h3>
<div class="chart-container mx-auto" style="max-width:900px;">
  <canvas id="applicantsChart" height="150"></canvas>
</div>

<footer class="footer">&copy; <?= date('Y') ?> Hire Us System. All rights reserved.</footer>
</div>

<!-- Add Application Modal -->
<div class="modal fade" id="addApplicationModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST">
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Application</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <label>Job:</label>
          <select name="new_job_id" class="form-select" required>
            <option value="">Select Job</option>
            <?php while($job = $jobsResult->fetch_assoc()): ?>
              <option value="<?= $job['id'] ?>"><?= htmlspecialchars($job['job_title']) ?></option>
            <?php endwhile; ?>
          </select>
          <label class="mt-2">Worker:</label>
          <select name="new_worker_id" class="form-select" required>
            <option value="">Select Worker</option>
            <?php while($worker = $workersResult->fetch_assoc()): ?>
              <option value="<?= $worker['idNumber'] ?>"><?= htmlspecialchars($worker['fullName']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="add_application" class="btn btn-success">Add</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Update Application Modal -->
<div class="modal fade" id="updateModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST">
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Update Application</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <input type="hidden" name="update_id" id="update_id">
          <label>Job:</label>
          <select name="job_id" id="job_id" class="form-select" required>
            <option value="">Select Job</option>
            <?php $jobsResult->data_seek(0); while($job = $jobsResult->fetch_assoc()): ?>
              <option value="<?= $job['id'] ?>"><?= htmlspecialchars($job['job_title']) ?></option>
            <?php endwhile; ?>
          </select>
          <label class="mt-2">Worker:</label>
          <select name="worker_id" id="worker_id" class="form-select" required>
            <option value="">Select Worker</option>
            <?php $workersResult->data_seek(0); while($worker = $workersResult->fetch_assoc()): ?>
              <option value="<?= $worker['idNumber'] ?>"><?= htmlspecialchars($worker['fullName']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="update_application" class="btn btn-warning">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Sidebar toggle
document.getElementById('sidebarToggle').addEventListener('click', ()=>{ document.body.classList.toggle('sidebar-hidden'); });

// Search
const searchInput = document.getElementById('searchInput');
const table = document.querySelector('table tbody');
searchInput.addEventListener('input', ()=>{ 
  const filter = searchInput.value.toLowerCase();
  Array.from(table.getElementsByTagName('tr')).forEach(row=>{
    let matched = false;
    const cells = row.getElementsByTagName('td');
    for(let i=0;i<cells.length-1;i++){ 
      if(cells[i].textContent.toLowerCase().includes(filter)){ matched=true; break; } 
    }
    row.style.display = matched?'':'none';
  });
});

// Worker & Job buttons
document.querySelectorAll('.show-worker').forEach(btn=>{
    btn.addEventListener('click', e=>{
        e.preventDefault();
        const id = btn.getAttribute('data-id');
        window.open(`get_worker_details.php?id=${id}`,'_blank','width=800,height=600');
    });
});
document.querySelectorAll('.show-job').forEach(btn=>{
    btn.addEventListener('click', e=>{
        e.preventDefault();
        const id = btn.getAttribute('data-id');
        window.open(`get_job_details.php?id=${id}`,'_blank','width=800,height=600');
    });
});

// Update modal
document.querySelectorAll('.edit-app').forEach(btn=>{
    btn.addEventListener('click', ()=>{
        const modal = new bootstrap.Modal(document.getElementById('updateModal'));
        document.getElementById('update_id').value = btn.dataset.id;
        document.getElementById('job_id').value = btn.dataset.job;
        document.getElementById('worker_id').value = btn.dataset.worker;
        modal.show();
    });
});

// Add Application modal
document.getElementById('addApplicationBtn').addEventListener('click', ()=>{
    const modal = new bootstrap.Modal(document.getElementById('addApplicationModal'));
    modal.show();
});

// Chart.js
const ctx = document.getElementById('applicantsChart').getContext('2d');
const gradient = ctx.createLinearGradient(0,0,0,400);
gradient.addColorStop(0,'rgba(0,123,255,0.8)');
gradient.addColorStop(1,'rgba(102,16,242,0.8)');
new Chart(ctx,{
    type:'bar',
    data:{
        labels: <?= json_encode($jobTitles) ?>,
        datasets:[{
            label:'Number of Applicants',
            data: <?= json_encode($applicantsCounts) ?>,
            backgroundColor: gradient,
            borderColor:'#4b2998',
            borderWidth:1,
            borderRadius:6
        }]
    },
    options:{ responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true,ticks:{stepSize:1}}} }
});
</script>

</body>
</html>
<?php $conn->close(); ?>
