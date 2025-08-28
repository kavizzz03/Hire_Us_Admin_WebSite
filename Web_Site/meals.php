<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Colombo');

$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: ".$conn->connect_error);

// Fetch jobs for dropdown
$jobsResult = $conn->query("SELECT id, job_title FROM jobs");

// Add new meal
if(isset($_POST['add_meal'])){
    $job_id = intval($_POST['job_id']);
    $meal_name = $conn->real_escape_string($_POST['meal_name']);
    $description = $conn->real_escape_string($_POST['description']);
    $meal_price = floatval($_POST['meal_price']);

    $conn->query("INSERT INTO meals (job_id, meal_name, description, meal_price, created_at)
                  VALUES ($job_id,'$meal_name','$description',$meal_price,NOW())");
    header("Location: meals.php");
    exit();
}

// Update meal
if(isset($_POST['update_meal'])){
    $id = intval($_POST['update_id']);
    $job_id = intval($_POST['job_id']);
    $meal_name = $conn->real_escape_string($_POST['meal_name']);
    $description = $conn->real_escape_string($_POST['description']);
    $meal_price = floatval($_POST['meal_price']);

    $conn->query("UPDATE meals SET job_id=$job_id, meal_name='$meal_name', description='$description', meal_price=$meal_price WHERE id=$id");
    header("Location: meals.php");
    exit();
}

// Delete meal
if(isset($_GET['delete_id'])){
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM meals WHERE id=$id");
    header("Location: meals.php");
    exit();
}

// Fetch all meals with job title
$mealsResult = $conn->query("SELECT m.*, j.job_title FROM meals m LEFT JOIN jobs j ON m.job_id=j.id ORDER BY m.created_at DESC");

// Fetch meals analysis data for chart
$chartResult = $conn->query("
    SELECT j.job_title,
           COUNT(h.id) AS meals_count
    FROM jobs j
    LEFT JOIN job_hires h 
           ON j.id = h.job_id AND h.wants_meals='yes'
    GROUP BY j.id
    ORDER BY j.id
");

$chartJobs = [];
$chartCounts = [];
while($row = $chartResult->fetch_assoc()){
    $chartJobs[] = $row['job_title'];
    $chartCounts[] = intval($row['meals_count']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Meals Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="icon" type="image/png" href="icon2.png">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* Include your previous styles here (same as before) */
body, html { margin:0; padding:0; font-family:'Segoe UI',sans-serif; background:#f4f6f9; color:#333;}
.sidebar { width:260px; background:#1e293b; color:#fff; flex-shrink:0; display:flex; flex-direction:column; padding-top:1rem; position:fixed; top:0; left:0; bottom:0; overflow-y:auto; transition:transform .3s ease; box-shadow:2px 0 12px rgba(0,0,0,.1);}
.sidebar-hidden .sidebar {transform:translateX(-260px);}
.sidebar-header {font-size:1.8rem;font-weight:700;text-align:center;margin-bottom:1.5rem;color:#4b6cb7;letter-spacing:1px;}
.sidebar .nav-link {color:#cbd5e1;padding:.75rem 1rem;display:flex;align-items:center;gap:.75rem;font-size:1rem;border-radius:8px;margin:.25rem .5rem;transition:background .3s,color .3s;}
.sidebar .nav-link:hover,.sidebar .nav-link.active {background:#334155;color:#fff;}
.main-content {margin-left:260px;padding:25px;transition:margin-left .3s ease;}
body.sidebar-hidden .main-content {margin-left:0;}
.main-content h1 {font-size:2rem;margin-bottom:20px;color:#182848;text-align:center;}
.card-table {background:#fff;border-radius:15px;box-shadow:0 8px 20px rgba(0,0,0,.05);padding:20px;margin-bottom:30px;}
.table {border-collapse:separate;border-spacing:0;}
.table th {background:linear-gradient(90deg,#4b6cb7,#182848);color:#fff;text-align:center;}
.table td,.table th {vertical-align:middle;text-align:center;}
.table-striped tbody tr:nth-of-type(odd) {background-color:#f8f9fa;}
.table-hover tbody tr:hover {background-color:#e2e8f0;}
.btn {border-radius:8px;font-weight:600;transition:transform .2s,box-shadow .2s;}
.btn:hover {transform:scale(1.05);box-shadow:0 4px 15px rgba(0,0,0,.1);}
.btn-success {background:linear-gradient(90deg,#4b6cb7,#182848);border:none;}
.btn-success:hover {background:linear-gradient(90deg,#182848,#4b6cb7);}
.modal-header.bg-success {background:linear-gradient(90deg,#4b6cb7,#182848);color:#fff;}
.modal-header.bg-warning {background:linear-gradient(90deg,#ffc107,#ffca2c);color:#212529;}
@media(max-width:992px){.sidebar {transform:translateX(-260px);z-index:1000;}#sidebarToggle {display:inline-block;}.main-content {margin-left:0;}}
#sidebarToggle {display:none;margin-bottom:15px;}
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <button id="sidebarToggle" class="btn btn-outline-primary mb-3"><i class="bi bi-list"></i> Menu</button>
    <h1 class="text-center mb-4">Meals Management</h1>

    <div class="d-flex justify-content-between mb-3">
        <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#addMealModal"><i class="bi bi-plus-circle"></i> Add Meal</button>
        <a href="meals_report.php" class="btn btn-info shadow-sm"><i class="bi bi-file-earmark-text"></i> Generate Report</a>
    </div>

    <div class="card-table table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Job</th>
                    <th>Meal Name</th>
                    <th>Description</th>
                    <th>Price (LKR)</th>
                    <th>Created At</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if($mealsResult->num_rows>0): while($meal = $mealsResult->fetch_assoc()): ?>
                <tr>
                    <td><?= $meal['id'] ?></td>
                    <td><?= htmlspecialchars($meal['job_title']) ?></td>
                    <td><?= htmlspecialchars($meal['meal_name']) ?></td>
                    <td><?= htmlspecialchars($meal['description']) ?></td>
                    <td><?= number_format($meal['meal_price'],2) ?></td>
                    <td>
                        <?php
                        $dt = new DateTime($meal['created_at'], new DateTimeZone('UTC'));
                        $dt->setTimezone(new DateTimeZone('Asia/Colombo'));
                        echo $dt->format('Y-m-d H:i:s');
                        ?>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-warning btn-sm edit-meal" 
                            data-id="<?= $meal['id'] ?>" 
                            data-job="<?= $meal['job_id'] ?>" 
                            data-name="<?= htmlspecialchars($meal['meal_name']) ?>" 
                            data-desc="<?= htmlspecialchars($meal['description']) ?>" 
                            data-price="<?= $meal['meal_price'] ?>"><i class="bi bi-pencil-square"></i> Edit</button>
                        <a href="?delete_id=<?= $meal['id'] ?>" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Delete</a>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="7" class="text-center text-muted fst-italic">No meals found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Meals Analysis Chart -->
    <div class="card-table mt-5">
        <h3 class="text-center mb-4">Meals Requests Analysis (wants_meals='yes')</h3>
        <canvas id="mealsChart" height="100"></canvas>
    </div>
</div>

<!-- Add/Edit Modals (unchanged from your original code) -->


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Sidebar toggle
document.getElementById('sidebarToggle').addEventListener('click', ()=>{ document.body.classList.toggle('sidebar-hidden'); });

// Edit meal modal
document.querySelectorAll('.edit-meal').forEach(btn=>{
    btn.addEventListener('click', ()=>{
        document.getElementById('edit_id').value = btn.dataset.id;
        document.getElementById('edit_job_id').value = btn.dataset.job;
        document.getElementById('edit_name').value = btn.dataset.name;
        document.getElementById('edit_desc').value = btn.dataset.desc;
        document.getElementById('edit_price').value = btn.dataset.price;
        new bootstrap.Modal(document.getElementById('editMealModal')).show();
    });
});

// Chart.js
const ctx = document.getElementById('mealsChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($chartJobs) ?>,
        datasets: [{
            label: 'Meals Wanted',
            data: <?= json_encode($chartCounts) ?>,
            backgroundColor: 'rgba(75,123,255,0.7)',
            borderColor: 'rgba(75,123,255,1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive:true,
        scales: {
            y: { beginAtZero:true, title:{display:true,text:'Number of Meals'} },
            x: { title:{display:true,text:'Job'} }
        }
    }
});
</script>
</body>
</html>
