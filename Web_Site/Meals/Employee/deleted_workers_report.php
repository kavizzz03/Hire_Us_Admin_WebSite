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

// Delete handler
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM deleted_users_log WHERE id = $delete_id");
    header("Location: deleted_workers_report.php");
    exit();
}

// Restore handler
if (isset($_GET['restore_id'])) {
    $restore_id = intval($_GET['restore_id']);
    $res = $conn->query("SELECT * FROM deleted_users_log WHERE id = $restore_id LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $worker = $res->fetch_assoc();
        $stmt = $conn->prepare("INSERT INTO workers (fullName, username, contactNumber, email, idNumber, permanentAddress, currentAddress, workExperience, jobTitle, password, bankAccountNumber, bankName, bankBranch, idFrontImage, idBackImage, created_at, reset_token, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if($stmt){
            $stmt->bind_param(
                "ssssssssssssssssss",
                $worker['fullName'],
                $worker['username'],
                $worker['contactNumber'],
                $worker['email'],
                $worker['idNumber'],
                $worker['permanentAddress'],
                $worker['currentAddress'],
                $worker['workExperience'],
                $worker['jobTitle'],
                $worker['password'],
                $worker['bankAccountNumber'],
                $worker['bankName'],
                $worker['bankBranch'],
                $worker['idFrontImage'],
                $worker['idBackImage'],
                $worker['created_at'],
                $worker['reset_token'],
                $worker['status']
            );
            if ($stmt->execute()) {
                $conn->query("DELETE FROM deleted_users_log WHERE id = $restore_id");
                header("Location: deleted_workers_report.php");
                exit();
            } else {
                $restore_error = "Error restoring worker: " . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        } else {
            $restore_error = "Prepare statement failed: " . htmlspecialchars($conn->error);
        }
    } else {
        $restore_error = "Worker not found in deleted log.";
    }
}

// Fetch deleted workers
$sql = "SELECT * FROM deleted_users_log ORDER BY deleted_at DESC";
$result = $conn->query($sql);

// Weekly deletion analysis
$weeklyQuery = "
    SELECT YEARWEEK(deleted_at, 1) AS yearweek, COUNT(*) AS deleted_count, MIN(deleted_at) AS week_start, MAX(deleted_at) AS week_end
    FROM deleted_users_log
    GROUP BY YEARWEEK(deleted_at, 1)
    ORDER BY yearweek ASC
";
$weeklyResult = $conn->query($weeklyQuery);

$weeks = [];
$counts = [];
if($weeklyResult){
    while ($row = $weeklyResult->fetch_assoc()) {
        $weekNum = substr($row['yearweek'], 4);
        $startDate = date('m/d', strtotime($row['week_start']));
        $endDate = date('m/d', strtotime($row['week_end']));
        $weeks[] = "Week $weekNum ($startDate - $endDate)";
        $counts[] = $row['deleted_count'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Deleted Workers</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<link rel="stylesheet" href="sidebar.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="icon" type="image/png" href="icon2.png">
<style>
body {
    margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#f0f4f8; display:flex;
}
.main-content {
    flex:1; padding:20px; overflow-x:hidden; transition: margin-left 0.3s ease, flex 0.3s ease;
}
.table-container { background:#fff; border-radius:15px; padding:25px; box-shadow:0 12px 28px rgba(0,123,255,0.15); margin-bottom:50px; }
.table thead { background: linear-gradient(45deg, #007bff, #6610f2); color:white; }
table tbody tr { opacity:0; transform:translateY(15px); animation:fadeSlideIn 0.5s forwards; }
@keyframes fadeSlideIn { to { opacity:1; transform:translateY(0); } }
.btn-sm { font-size:0.9rem; font-weight:600; transition: transform 0.3s ease, box-shadow 0.3s ease; }
.btn-sm:hover { transform: scale(1.08); box-shadow: 0 6px 12px rgba(0,123,255,0.3); }
.btn-success { background:#198754; border:none; }
.btn-success:hover { background:#157347; }
.btn-danger { background:#dc3545; border:none; }
.btn-danger:hover { background:#bb2d3b; }
.chart-container { background:#fff; border-radius:15px; padding:30px; box-shadow:0 12px 28px rgba(102,16,242,0.15); margin-bottom:50px; }
footer.footer { margin-top:50px; padding:15px 0; background:#fff; box-shadow:0 -2px 6px rgba(0,0,0,0.05); font-size:0.9rem; color:#6c757d; text-align:center; border-radius:0 0 12px 12px; }

/* Mobile Sidebar Toggle */
#sidebarToggle { display:none; margin-bottom:15px; }
@media(max-width:992px){ #sidebarToggle { display:inline-block; } }

/* Sidebar hide effect */
body.sidebar-hidden #sidebar {
    transform:translateX(-250px);
    position:fixed;
    z-index:1000;
}
body.sidebar-hidden .main-content {
    margin-left:0 !important;
    flex: 1 1 100%;
}
#sidebar {
    width:250px;
    transition: transform 0.3s ease;
}
.main-content { margin-left:250px; transition: margin-left 0.3s ease; }
</style>
</head>
<body>

<!-- Sidebar -->
<?php include 'sidebar.php'; ?>

<div class="main-content">

  <!-- Sidebar Toggle Button -->
  <button id="sidebarToggle" class="btn btn-outline-primary mb-3">
    <i class="bi bi-list"></i> Menu
  </button>

<h1 class="text-center mb-4">Deleted Workers Log</h1>

<?php if(isset($restore_error)) echo "<div class='alert alert-danger'>$restore_error</div>"; ?>

<div class="mb-4">
<input type="text" id="searchInput" class="form-control form-control-lg" placeholder="Search deleted workers...">
</div>

<div class="table-container">
<div class="table-responsive">
<table class="table table-bordered table-striped align-middle">
<thead>
<tr>
<th>ID</th>
<th>Full Name</th>
<th>Username</th>
<th>Contact</th>
<th>Email</th>
<th>ID Number</th>
<th>Job Title</th>
<th>Deleted At</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php if($result && $result->num_rows>0): while($row=$result->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($row['id']) ?></td>
<td><?= htmlspecialchars($row['fullName']) ?></td>
<td><?= htmlspecialchars($row['username']) ?></td>
<td><?= htmlspecialchars($row['contactNumber']) ?></td>
<td><?= htmlspecialchars($row['email']) ?></td>
<td><?= htmlspecialchars($row['idNumber']) ?></td>
<td><?= htmlspecialchars($row['jobTitle']) ?></td>
<td><?= htmlspecialchars($row['deleted_at']) ?></td>
<td class="text-center action-btns">
<a href="#" data-id="<?= $row['id'] ?>" data-action="restore" class="btn btn-success btn-sm"><i class="bi bi-arrow-clockwise"></i> Restore</a>
<a href="#" data-id="<?= $row['id'] ?>" data-action="delete" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Delete</a>
</td>
</tr>
<?php endwhile; else: ?>
<tr><td colspan="9" class="text-center text-muted fst-italic">No deleted workers found.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>

<h3 class="mb-4 mt-5 text-center">Weekly Deletion Analysis</h3>
<div class="chart-container mx-auto" style="max-width:900px;">
<canvas id="deletionChart" height="150"></canvas>
</div>

<div class="text-center mb-5">
<a href="export_deleted_workers.php" class="btn btn-primary btn-lg shadow"><i class="bi bi-download"></i> Export Deleted Workers CSV Report</a>
</div>

<footer class="footer">&copy; <?= date('Y') ?> Hire Us System. All rights reserved.</footer>
</div>

<script>
// Mobile sidebar toggle
document.getElementById('sidebarToggle').addEventListener('click', ()=>{
    document.body.classList.toggle('sidebar-hidden');
});

// Restore/Delete SweetAlert
document.querySelectorAll('.action-btns a').forEach(btn=>{
    btn.addEventListener('click', e=>{
        e.preventDefault();
        const id = btn.getAttribute('data-id');
        const action = btn.getAttribute('data-action');
        Swal.fire({
            title: action==='restore'?'Restore':'Delete Permanently',
            text: action==='restore'?'Are you sure you want to restore this worker?':'This action will permanently delete the worker. Are you sure?',
            icon: action==='restore'?'question':'warning',
            showCancelButton:true,
            confirmButtonText: action==='restore'?'Restore':'Delete',
            confirmButtonColor: action==='restore'?'#198754':'#dc3545',
            cancelButtonText:'Cancel'
        }).then(result=>{
            if(result.isConfirmed) window.location.href=`deleted_workers_report.php?${action}_id=${id}`;
        });
    });
});

// Search functionality
const searchInput=document.getElementById('searchInput');
const table=document.querySelector('table tbody');
searchInput.addEventListener('input', ()=>{
    const filter=searchInput.value.toLowerCase();
    Array.from(table.getElementsByTagName('tr')).forEach(row=>{
        let matched=false;
        const cells=row.getElementsByTagName('td');
        for(let i=0;i<cells.length-1;i++){
            if(cells[i].textContent.toLowerCase().includes(filter)){ matched=true; break; }
        }
        row.style.display=matched?'':'none';
    });
});

// Chart.js
const ctx=document.getElementById('deletionChart').getContext('2d');
const gradient=ctx.createLinearGradient(0,0,0,400);
gradient.addColorStop(0,'rgba(0,123,255,0.8)');
gradient.addColorStop(1,'rgba(102,16,242,0.8)');
new Chart(ctx,{
    type:'bar',
    data:{
        labels: <?= json_encode($weeks) ?>,
        datasets:[{
            label:'Workers Deleted',
            data: <?= json_encode($counts) ?>,
            backgroundColor:gradient,
            borderColor:'#4b2998',
            borderWidth:1,
            borderRadius:6
        }]
    },
    options:{
        responsive:true,
        plugins:{
            legend:{display:false},
            tooltip:{
                callbacks:{
                    label:function(ctx){ return ` ${ctx.parsed.y} workers deleted`; }
                }
            }
        },
        scales:{x:{ticks:{maxRotation:45,minRotation:45}}, y:{beginAtZero:true,ticks:{stepSize:1}}}
    }
});
</script>

</body>
</html>
