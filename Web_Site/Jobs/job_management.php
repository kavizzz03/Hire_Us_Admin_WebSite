<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

// DB Connection
$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$msg = '';
$msgType = 'success';

// Handle Add Job
if (isset($_POST['add_job'])) {
    $job_title = $_POST['job_title'];
    $vacancies = $_POST['vacancies'];
    $time_range = $_POST['time_range'];
    $location = $_POST['location'];
    $basic_salary = $_POST['basic_salary'];
    $ot_salary = $_POST['ot_salary'] ?: 0.00;
    $requirements = $_POST['requirements'];
    $job_date = $_POST['job_date'];
    $pickup_location = $_POST['pickup_location'];
    $contact_info = $_POST['contact_info'];
    $email = $_POST['email'];
    $employee_id = $_POST['employee_id'];

    $stmt = $conn->prepare("INSERT INTO jobs (job_title,vacancies,time_range,location,basic_salary,ot_salary,requirements,job_date,pickup_location,contact_info,email,employee_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("sissddsssssi", $job_title, $vacancies, $time_range, $location, $basic_salary, $ot_salary, $requirements, $job_date, $pickup_location, $contact_info, $email, $employee_id);
    if($stmt->execute()){
        $msg = "Job added successfully!";
    } else {
        $msg = "Error adding job: ".$stmt->error;
        $msgType = "error";
    }
    $stmt->close();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if($conn->query("DELETE FROM jobs WHERE id=$id")){
        $msg = "Job deleted successfully!";
    } else {
        $msg = "Error deleting job!";
        $msgType = "error";
    }
}

// Handle Update Job
if (isset($_POST['update_job'])) {
    $id = $_POST['job_id'];
    $job_title = $_POST['job_title'];
    $vacancies = $_POST['vacancies'];
    $time_range = $_POST['time_range'];
    $location = $_POST['location'];
    $basic_salary = $_POST['basic_salary'];
    $ot_salary = $_POST['ot_salary'] ?: 0.00;
    $requirements = $_POST['requirements'];
    $job_date = $_POST['job_date'];
    $pickup_location = $_POST['pickup_location'];
    $contact_info = $_POST['contact_info'];
    $email = $_POST['email'];
    $employee_name = $_POST['employee_name'];

    // Get employee ID by name
    $empRes = $conn->query("SELECT id FROM employers WHERE company_name='". $conn->real_escape_string($employee_name) ."'");
    $employee_id = $empRes && $empRes->num_rows > 0 ? $empRes->fetch_assoc()['id'] : null;

    $stmt = $conn->prepare("UPDATE jobs SET job_title=?, vacancies=?, time_range=?, location=?, basic_salary=?, ot_salary=?, requirements=?, job_date=?, pickup_location=?, contact_info=?, email=?, employee_id=? WHERE id=?");
    $stmt->bind_param("sissddssssiii", $job_title, $vacancies, $time_range, $location, $basic_salary, $ot_salary, $requirements, $job_date, $pickup_location, $contact_info, $email, $employee_id, $id);
    if($stmt->execute()){
        $msg = "Job updated successfully!";
    } else {
        $msg = "Error updating job: ".$stmt->error;
        $msgType = "error";
    }
    $stmt->close();
}

// Fetch Employers
$employers = $conn->query("SELECT id, company_name FROM employers ORDER BY company_name ASC");

// Fetch Jobs with all details
$jobs = $conn->query("SELECT jobs.*, employers.company_name FROM jobs INNER JOIN employers ON jobs.employee_id = employers.id ORDER BY jobs.id DESC");

// Chart Data
$chartDataQuery = $conn->query("SELECT job_title, COUNT(*) as total FROM jobs GROUP BY job_title");
$chartLabels = [];
$chartCounts = [];
while($row = $chartDataQuery->fetch_assoc()){
    $chartLabels[] = $row['job_title'];
    $chartCounts[] = $row['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Jobs - Hire Us System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="sidebar.css">
<link rel="icon" type="image/png" href="icon2.png">
<style>
body { margin:0; font-family:'Segoe UI', Tahoma, sans-serif; display:flex; background:#f0f4f8; }
.main-content { flex:1; padding:20px; overflow-x:hidden; transition: margin-left 0.3s ease; }
.card { border-radius:12px; box-shadow:0 6px 15px rgba(0,0,0,0.1); }
.table-container { background:#fff; border-radius:15px; padding:20px; box-shadow:0 12px 28px rgba(0,123,255,0.15); margin-bottom:20px; }
.table thead { background: linear-gradient(45deg,#007bff,#6610f2); color:white; }
.table tbody tr:hover { background: rgba(0,123,255,0.05); transform:scale(1.01); }
.btn-sm { font-size:0.85rem; font-weight:600; }
.footer { margin-top:50px; padding:15px 0; background:#fff; box-shadow:0 -2px 6px rgba(0,0,0,0.05); font-size:0.9rem; color:#6c757d; text-align:center; border-radius:0 0 12px 12px; }
#sidebarToggle { display:none; margin-bottom:15px; }
@media(max-width:992px){ #sidebarToggle { display:inline-block; } }
body.sidebar-hidden #sidebar { transform:translateX(-250px); position:fixed; z-index:1000; }
.chart-container { background:#fff; padding:20px; border-radius:12px; box-shadow:0 8px 18px rgba(0,0,0,0.08); margin-bottom:20px; }
.filter-section { background:#fff; padding:15px; border-radius:10px; box-shadow:0 5px 15px rgba(0,0,0,0.05); margin-bottom:20px; }
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <button id="sidebarToggle" class="btn btn-outline-primary mb-3"><i class="bi bi-list"></i> Menu</button>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Jobs</h1>
        <a href="job_history.php" class="btn btn-info"><i class="bi bi-clock-history"></i> Job History</a>
    </div>

    <!-- Filter & Print -->
    <div class="filter-section">
        <h5><i class="bi bi-funnel"></i> Filter Jobs & Print</h5>
        <div class="row g-2">
            <div class="col-md-4"><input type="date" id="startDate" class="form-control"></div>
            <div class="col-md-4"><input type="date" id="endDate" class="form-control"></div>
            <div class="col-md-4 d-flex">
                <button class="btn btn-success me-2" onclick="filterJobs()">Generate Report</button>
                <button class="btn btn-secondary" onclick="printReport()">Print</button>
            </div>
        </div>
    </div>

    <!-- Add Job Form -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white"><i class="bi bi-briefcase"></i> Add New Job</div>
        <div class="card-body">
            <form method="POST">
                <div class="row mb-2">
                    <div class="col-md-6"><input type="text" name="job_title" class="form-control" placeholder="Job Title" required></div>
                    <div class="col-md-6"><input type="number" name="vacancies" class="form-control" placeholder="Vacancies" required></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6"><input type="text" name="time_range" class="form-control" placeholder="Time Range" required></div>
                    <div class="col-md-6"><input type="text" name="location" class="form-control" placeholder="Location" required></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6"><input type="number" step="0.01" name="basic_salary" class="form-control" placeholder="Basic Salary" required></div>
                    <div class="col-md-6"><input type="number" step="0.01" name="ot_salary" class="form-control" placeholder="OT Salary (optional)"></div>
                </div>
                <textarea name="requirements" class="form-control mb-2" placeholder="Requirements"></textarea>
                <div class="row mb-2">
                    <div class="col-md-6"><input type="date" name="job_date" class="form-control" required></div>
                    <div class="col-md-6"><input type="text" name="pickup_location" class="form-control" placeholder="Pickup Location"></div>
                </div>
                <input type="text" name="contact_info" class="form-control mb-2" placeholder="Contact Info" required>
                <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
                <select name="employee_id" class="form-select mb-2" required>
                    <option value="">Select Employer</option>
                    <?php while($row = $employers->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['company_name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" name="add_job" class="btn btn-success w-100"><i class="bi bi-plus-circle"></i> Add Job</button>
            </form>
        </div>
    </div>

    <!-- Dynamic Search -->
    <div class="mb-3">
        <input type="text" id="tableSearch" class="form-control" placeholder="Search jobs...">
    </div>

    <!-- Job Listings -->
    <div class="table-container">
        <h4><i class="bi bi-list-task"></i> Job Listings</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle" id="jobsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Job Title</th>
                        <th>Employer</th>
                        <th>Vacancies</th>
                        <th>Time Range</th>
                        <th>Location</th>
                        <th>Salary</th>
                        <th>OT Salary</th>
                        <th>Date</th>
                        <th>Pickup Location</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($jobs && $jobs->num_rows>0): while($job = $jobs->fetch_assoc()): ?>
                    <tr>
                        <td><?= $job['id'] ?></td>
                        <td><?= htmlspecialchars($job['job_title']) ?></td>
                        <td><?= htmlspecialchars($job['company_name']) ?></td>
                        <td><?= $job['vacancies'] ?></td>
                        <td><?= htmlspecialchars($job['time_range']) ?></td>
                        <td><?= htmlspecialchars($job['location']) ?></td>
                        <td><?= $job['basic_salary'] ?></td>
                        <td><?= $job['ot_salary'] ?></td>
                        <td><?= $job['job_date'] ?></td>
                        <td><?= htmlspecialchars($job['pickup_location']) ?></td>
                        <td><?= htmlspecialchars($job['contact_info']) ?></td>
                        <td><?= htmlspecialchars($job['email']) ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick='editJob(<?= json_encode($job) ?>)'><i class="bi bi-pencil"></i> Edit</button>
                            <a href="?delete=<?= $job['id'] ?>" class="btn btn-danger btn-sm delete-btn"><i class="bi bi-trash"></i> Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="13" class="text-center">No jobs available.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Analysis Chart -->
    <div class="chart-container">
        <h5><i class="bi bi-bar-chart"></i> Job Analysis by Title</h5>
        <canvas id="jobChart"></canvas>
    </div>

    <!-- Update Job Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Edit Job</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="job_id" id="job_id">
                        <div class="mb-2"><input type="text" name="job_title" id="job_title" class="form-control" placeholder="Job Title" required></div>
                        <div class="mb-2"><input type="text" name="employee_name" id="employee_name" class="form-control" placeholder="Employer Name" required></div>
                        <div class="mb-2"><input type="number" name="vacancies" id="vacancies" class="form-control" required></div>
                        <div class="mb-2"><input type="text" name="time_range" id="time_range" class="form-control" required></div>
                        <div class="mb-2"><input type="text" name="location" id="location" class="form-control" required></div>
                        <div class="mb-2"><input type="number" step="0.01" name="basic_salary" id="basic_salary" class="form-control" required></div>
                        <div class="mb-2"><input type="number" step="0.01" name="ot_salary" id="ot_salary" class="form-control"></div>
                        <textarea name="requirements" id="requirements" class="form-control mb-2" placeholder="Requirements"></textarea>
                        <input type="date" name="job_date" id="job_date" class="form-control mb-2" required>
                        <input type="text" name="pickup_location" id="pickup_location" class="form-control mb-2" placeholder="Pickup Location">
                        <input type="text" name="contact_info" id="contact_info" class="form-control mb-2" placeholder="Contact Info" required>
                        <input type="email" name="email" id="email" class="form-control mb-2" placeholder="Email" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="update_job" class="btn btn-warning">Update Job</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer">&copy; <?= date('Y') ?> Hire Us System</footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// SweetAlert Messages
<?php if($msg): ?>
Swal.fire({ icon:'<?= $msgType ?>', title:'<?= addslashes($msg) ?>', timer:2500 });
<?php endif; ?>

// Edit Job Modal
function editJob(job){
    const modal = new bootstrap.Modal(document.getElementById('editModal'));
    document.getElementById('job_id').value = job.id;
    document.getElementById('job_title').value = job.job_title;
    document.getElementById('employee_name').value = job.company_name;
    document.getElementById('vacancies').value = job.vacancies;
    document.getElementById('time_range').value = job.time_range;
    document.getElementById('location').value = job.location;
    document.getElementById('basic_salary').value = job.basic_salary;
    document.getElementById('ot_salary').value = job.ot_salary;
    document.getElementById('requirements').value = job.requirements;
    document.getElementById('job_date').value = job.job_date;
    document.getElementById('pickup_location').value = job.pickup_location;
    document.getElementById('contact_info').value = job.contact_info;
    document.getElementById('email').value = job.email;
    modal.show();
}

// Delete Confirmation
document.querySelectorAll('.delete-btn').forEach(btn=>{
    btn.addEventListener('click', function(e){
        e.preventDefault();
        const href = this.getAttribute('href');
        Swal.fire({ title:'Are you sure?', text:'This job will be deleted!', icon:'warning', showCancelButton:true }).then(result=>{
            if(result.isConfirmed){ window.location.href = href; }
        });
    });
});

// Sidebar toggle
document.getElementById('sidebarToggle').addEventListener('click', ()=>{
    document.body.classList.toggle('sidebar-hidden');
});

// Chart.js
const ctx = document.getElementById('jobChart').getContext('2d');
new Chart(ctx,{ type:'bar', data:{ labels:<?= json_encode($chartLabels) ?>, datasets:[{ label:'Jobs Count', data:<?= json_encode($chartCounts) ?>, backgroundColor:'rgba(0,123,255,0.7)' }] }, options:{ responsive:true, plugins:{ legend:{ display:false } } } });

// Print Report
function printReport(){
    let rows = Array.from(document.querySelectorAll("#jobsTable tbody tr")).filter(r=>r.style.display!=='none');
    let html = `<html><head><link rel="icon" type="image/png" href="icon2.png"><title>Job Report</title><style>
        body{font-family:'Segoe UI', Tahoma, sans-serif; padding:20px; color:#333;}
        h1,h4{text-align:center; margin:0; padding:5px;} h1{color:#007bff;} h4{margin-bottom:20px;}
        table{width:100%; border-collapse:collapse; font-size:14px;} th,td{border:1px solid #ddd; padding:10px; text-align:left;}
        th{background:linear-gradient(45deg,#007bff,#6610f2); color:white; font-weight:600;}
        tbody tr:nth-child(even){background:#f7f7f7;} tbody tr:hover{background:#d9f0ff;}
        .report-footer{text-align:center; font-size:13px; color:#555; margin-top:20px;}
        .logo{text-align:center; margin-bottom:10px;} .logo img{height:60px;}
    </style></head><body>
    <div class="logo"><img src="https://via.placeholder.com/150x60?text=Hire+Us+Logo" alt="Logo"></div>
    <h1>Hire Us System</h1><h4>Job Report</h4>
    <p style="text-align:center;">Generated on: ${new Date().toLocaleString()}</p>
    <table><thead><tr>
    <th>ID</th><th>Job Title</th><th>Employer</th><th>Vacancies</th><th>Time Range</th><th>Location</th>
    <th>Salary</th><th>OT Salary</th><th>Date</th><th>Pickup Location</th><th>Contact</th><th>Email</th>
    </tr></thead><tbody>`;
    rows.forEach(row=>{
        let cells=row.querySelectorAll('td');
        html+='<tr>'; for(let i=0;i<12;i++){ html+=`<td>${cells[i].innerText}</td>`; } html+='</tr>';
    });
    html+=`</tbody></table><div class="report-footer">&copy; ${new Date().getFullYear()} Hire Us System</div></body></html>`;
    let w=window.open(); w.document.write(html); w.document.close(); w.focus(); w.print();
}

// Filter Jobs
function filterJobs(){
    let start = document.getElementById('startDate').value;
    let end = document.getElementById('endDate').value;
    const rows = document.querySelectorAll('#jobsTable tbody tr');
    rows.forEach(row=>{
        let date = row.children[8].innerText;
        if((!start || date>=start) && (!end || date<=end)){ row.style.display=''; } else { row.style.display='none'; }
    });
}

// Dynamic Search
const searchInput = document.getElementById('tableSearch');
searchInput.addEventListener('keyup', function(){
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#jobsTable tbody tr');
    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>
</body>
</html>
