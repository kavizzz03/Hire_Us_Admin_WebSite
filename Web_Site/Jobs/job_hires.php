<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Colombo');

$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

// ----------------- AJAX ADD/UPDATE -----------------
if(isset($_POST['ajax']) && $_POST['ajax']==='update_hire'){
    $id = $_POST['id'] ?? '';
    $job_id = $_POST['job_id'] ?? '';
    $id_number = $_POST['id_number'] ?? '';
    $wants_meals = isset($_POST['wants_meals']) && $_POST['wants_meals']==='yes' ? 1 : 0;

    if($job_id && $id_number){
        if($id){ // Update
            $old_worker_stmt = $conn->prepare("SELECT id_number FROM job_hires WHERE id=?");
            $old_worker_stmt->bind_param("i", $id);
            $old_worker_stmt->execute();
            $old_worker_stmt->bind_result($old_worker);
            $old_worker_stmt->fetch();
            $old_worker_stmt->close();

            if($old_worker && $old_worker !== $id_number){
                $conn->query("UPDATE workers SET status='not_hired' WHERE idNumber='" . $conn->real_escape_string($old_worker) . "'");
            }

            $stmt = $conn->prepare("UPDATE job_hires SET job_id=?, id_number=?, wants_meals=? WHERE id=?");
            $stmt->bind_param("isii", $job_id, $id_number, $wants_meals, $id);
            $stmt->execute();
            $stmt->close();
        } else { // Add
            $stmt = $conn->prepare("INSERT INTO job_hires (job_id, id_number, wants_meals, hired_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("isi", $job_id, $id_number, $wants_meals);
            $stmt->execute();
            $stmt->close();
        }

        $conn->query("UPDATE workers SET status='hired' WHERE idNumber='" . $conn->real_escape_string($id_number) . "'");
        echo json_encode(['status'=>'success','message'=>'Hire saved successfully!']);
        exit;
    } else {
        echo json_encode(['status'=>'error','message'=>'Please fill all fields.']);
        exit;
    }
}

// ----------------- AJAX DELETE -----------------
if(isset($_POST['ajax']) && $_POST['ajax']==='delete_hire'){
    $del_id = intval($_POST['id']);
    $old_worker_res = $conn->query("SELECT id_number FROM job_hires WHERE id=$del_id");
    $old_worker_row = $old_worker_res->fetch_assoc();
    if($old_worker_row){
        $conn->query("UPDATE workers SET status='not_hired' WHERE idNumber='" . $conn->real_escape_string($old_worker_row['id_number']) . "'");
    }
    $conn->query("DELETE FROM job_hires WHERE id=$del_id");
    echo json_encode(['status'=>'success','message'=>'Hire deleted successfully!']);
    exit;
}

// ----------------- FETCH DATA -----------------
$jobs = $conn->query("SELECT id, job_title FROM jobs ORDER BY job_title ASC");
$workers = $conn->query("SELECT idNumber, fullName, status FROM workers ORDER BY fullName ASC");
$hires = $conn->query("
    SELECT jh.id, jh.job_id, jh.id_number, jh.hired_at, jh.wants_meals,
           jobs.job_title, workers.fullName
    FROM job_hires jh
    LEFT JOIN jobs ON jh.job_id = jobs.id
    LEFT JOIN workers ON jh.id_number COLLATE utf8mb4_general_ci = workers.idNumber
    ORDER BY jh.hired_at DESC
");

// Chart Data
$chart_data = $conn->query("
    SELECT jobs.job_title, COUNT(jh.id) as total_hires
    FROM job_hires jh
    LEFT JOIN jobs ON jh.job_id = jobs.id
    GROUP BY jh.job_id
    ORDER BY total_hires DESC
");
$chart_labels = [];
$chart_values = [];
while($row=$chart_data->fetch_assoc()){
    $chart_labels[] = $row['job_title'] ?: 'Unknown';
    $chart_values[] = (int)$row['total_hires'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Job Hires Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="icon" type="image/png" href="icon2.png">
<style>
body { background:#f8fafc; font-family:'Segoe UI', sans-serif; margin:0; }
.fade-in { animation: fadeIn 0.8s ease-in-out; }
@keyframes fadeIn { from {opacity:0;} to {opacity:1;} }
/* Sidebar and Theme */
.sidebar { width: 260px; background: #1e293b; color: #fff; flex-shrink: 0; display: flex; flex-direction: column; padding-top: 1rem; position: fixed; top: 0; left: 0; bottom: 0; overflow-y: auto; }
.sidebar-header { font-size: 1.5rem; font-weight: 700; text-align: center; margin-bottom: 1.5rem; }
.sidebar .nav-link { color: #cbd5e1; padding: 0.75rem 1rem; display: flex; align-items: center; gap: 0.75rem; font-size: 1rem; border-radius: 8px; margin: 0.25rem 0.5rem; transition: background 0.3s; }
.sidebar .nav-link:hover { background: #334155; color: #fff; }
.main-content { margin-left:260px; padding:20px; transition:0.3s; }
.main-content.collapsed { margin-left:70px; }
#sidebarToggle { position:fixed; top:15px; left:270px; z-index:1100; background:#3b82f6; color:#fff; border:none; padding:6px 10px; border-radius:4px; cursor:pointer; transition:0.3s; }
#sidebarToggle.collapsed { left:80px; }
.card { border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.08); margin-bottom:20px; }
.card-header { font-weight:600; background:#0f172a; color:#fff; }
table th, table td { padding:12px 15px; }
table th { background:#0f172a; color:#fff; }
table tr:hover { background:#e2e8f0; }
#searchInput { margin-bottom:15px; padding:8px 12px; border-radius:5px; border:1px solid #cbd5e1; }
</style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<button id="sidebarToggle">☰</button>

<div class="main-content container my-4 fade-in">

<div class="d-flex justify-content-between align-items-center mb-4">
<h2 class="fw-bold">Job Hires Management</h2>
<a href="job_hires_history.php" class="btn btn-primary">Job Hires History</a>
</div>

<input type="text" id="searchInput" class="form-control mb-4" placeholder="Search Job Hires...">

<!-- Add/Edit Form -->
<div class="card mb-4 fade-in">
<div class="card-header">Add / Edit Hire</div>
<div class="card-body">
<form id="hireForm">
<input type="hidden" name="id" id="edit_id">
<div class="row g-3">
<div class="col-md-4">
<label class="form-label">Job</label>
<select name="job_id" id="job_id" class="form-select" required>
<option value="">-- Select Job --</option>
<?php $jobs->data_seek(0); while($job=$jobs->fetch_assoc()): ?>
<option value="<?= $job['id'] ?>"><?= htmlspecialchars($job['job_title']) ?></option>
<?php endwhile; ?>
</select>
</div>
<div class="col-md-4">
<label class="form-label">Worker</label>
<select name="id_number" id="id_number" class="form-select" required>
<option value="">-- Select Worker --</option>
<?php $workers->data_seek(0); while($worker=$workers->fetch_assoc()): ?>
<option value="<?= htmlspecialchars($worker['idNumber']) ?>" <?= $worker['status']==='hired'?'disabled':'' ?>>
<?= htmlspecialchars($worker['fullName']) ?> <?= $worker['status']==='hired'?'(Hired)':'' ?>
</option>
<?php endwhile; ?>
</select>
</div>
<div class="col-md-4">
<label class="form-label">Wants Meals</label>
<select name="wants_meals" id="wants_meals" class="form-select">
<option value="no">No</option>
<option value="yes">Yes</option>
</select>
</div>
</div>
<div class="mt-3">
<button type="submit" class="btn btn-success">Save</button>
<button type="reset" class="btn btn-secondary" onclick="clearForm()">Clear</button>
</div>
</form>
</div>
</div>

<!-- Hires Table -->
<div class="card mb-4 fade-in">
<div class="card-header">All Job Hires</div>
<div class="card-body table-responsive">
<table class="table table-striped table-bordered" id="hiresTable">
<thead>
<tr><th>ID</th><th>Job</th><th>Worker</th><th>Hired At</th><th>Meals</th><th>Actions</th></tr>
</thead>
<tbody>
<?php if($hires && $hires->num_rows>0): ?>
<?php while($h=$hires->fetch_assoc()): ?>
<tr data-id="<?= $h['id'] ?>">
<td><?= $h['id'] ?></td>
<td><?= htmlspecialchars($h['job_title'] ?? 'N/A') ?></td>
<td><?= htmlspecialchars($h['fullName'] ?? 'N/A') ?></td>
<td><?= $h['hired_at'] ?? 'N/A' ?></td>
<td><?= $h['wants_meals'] ? 'Yes':'No' ?></td>
<td>
<button class="btn btn-sm btn-primary" onclick="editHire('<?= $h['id'] ?>','<?= $h['job_id'] ?>','<?= $h['id_number'] ?>','<?= $h['wants_meals']?'yes':'no' ?>')">Edit</button>
<button class="btn btn-sm btn-danger delete-btn" data-id="<?= $h['id'] ?>">Delete</button>
</td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="6" class="text-center">No hires found</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>

<!-- Chart -->
<div class="card mb-4 fade-in">
<div class="card-header">Job Hires Analysis</div>
<div class="card-body">
<canvas id="hiresChart"></canvas>
</div>
</div>

</div>

<script>
// Sidebar toggle
const sidebar = document.querySelector('.sidebar');
const mainContent = document.querySelector('.main-content');
const toggleBtn = document.getElementById('sidebarToggle');
toggleBtn.addEventListener('click', ()=>{
sidebar.classList.toggle('collapsed');
mainContent.classList.toggle('collapsed');
toggleBtn.classList.toggle('collapsed');
});

// Form helpers
function clearForm(){
document.getElementById('edit_id').value='';
document.getElementById('job_id').value='';
document.getElementById('id_number').value='';
document.getElementById('wants_meals').value='no';
}
function editHire(id, job_id, id_number, wants_meals){
document.getElementById('edit_id').value=id;
document.getElementById('job_id').value=job_id;
document.getElementById('id_number').value=id_number;
document.getElementById('wants_meals').value=wants_meals;
window.scrollTo({ top: 0, behavior: 'smooth' });
}

// AJAX Add/Edit
document.getElementById('hireForm').addEventListener('submit', function(e){
e.preventDefault();
const formData=new FormData(this);
formData.append('ajax','update_hire');
fetch('', {method:'POST', body:formData})
.then(res=>res.json())
.then(data=>{
if(data.status==='success'){
Swal.fire({icon:'success',title:data.message,timer:1500,showConfirmButton:false}).then(()=> location.reload());
}else{
Swal.fire({icon:'error',title:data.message});
}
}).catch(()=> Swal.fire({icon:'error',title:'Server error'}));
});

// AJAX Delete
document.querySelectorAll('.delete-btn').forEach(btn=>{
btn.addEventListener('click', function(){
let id=this.dataset.id;
Swal.fire({
title:'Are you sure?',
text:'This hire will be deleted permanently!',
icon:'warning',
showCancelButton:true,
confirmButtonColor:'#dc2626',
cancelButtonColor:'#6c757d',
confirmButtonText:'Yes, delete!'
}).then((result)=>{
if(result.isConfirmed){
const fd = new FormData();
fd.append('ajax','delete_hire');
fd.append('id', id);
fetch('',{method:'POST',body:fd})
.then(res=>res.json())
.then(d=>{
if(d.status==='success'){
Swal.fire({icon:'success',title:d.message,timer:1500,showConfirmButton:false});
document.querySelector('tr[data-id="'+id+'"]').remove();
}
}).catch(()=> Swal.fire({icon:'error',title:'Delete failed'}));
}
});
});
});

// Chart.js
const ctx=document.getElementById('hiresChart').getContext('2d');
new Chart(ctx,{
type:'bar',
data:{
labels: <?= json_encode($chart_labels) ?>,
datasets:[{label:'Number of Hires', data: <?= json_encode($chart_values) ?>, backgroundColor:'rgba(37, 99, 235, 0.7)', borderColor:'rgba(37, 99, 235, 1)', borderWidth:1}]
},
options:{responsive:true, plugins:{legend:{display:false}, title:{display:true, text:'Job Hires Analysis'}}, scales:{y:{beginAtZero:true, precision:0}}}
});

// Dynamic search
document.getElementById('searchInput').addEventListener('keyup', function(){
let filter=this.value.toLowerCase();
document.querySelectorAll('#hiresTable tbody tr').forEach(row=>{
row.style.display=Array.from(row.cells).some(td=>td.textContent.toLowerCase().includes(filter))?'':'none';
});
});
</script>
</body>
</html>
<?php $conn->close(); ?>
