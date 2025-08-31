<?php
require 'db.php';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM employers WHERE id='$id'");
    header("Location: employers_crud.php?msg=deleted");
    exit;
}

// Search filter
$searchQuery = "";
if (isset($_GET['search']) && $_GET['search'] !== "") {
    $searchQuery = $conn->real_escape_string($_GET['search']);
    $employer_result = $conn->query("
        SELECT * FROM employers
        WHERE company_name LIKE '%$searchQuery%'
           OR name LIKE '%$searchQuery%'
           OR email LIKE '%$searchQuery%'
        ORDER BY company_name ASC
    ");
} else {
    $employer_result = $conn->query("SELECT * FROM employers ORDER BY company_name ASC");
}

// Fetch job counts for chart
$chart_result = $conn->query("
    SELECT 
        e.company_name, 
        COUNT(j.id) AS total_jobs,
        COUNT(dj.id) AS deleted_jobs
    FROM employers e
    LEFT JOIN jobs j ON j.employee_id = e.id
    LEFT JOIN deleted_jobs dj ON dj.employee_id = e.id
    GROUP BY e.id
");

$chart_labels = [];
$total_jobs = [];
$deleted_jobs = [];

while ($row = $chart_result->fetch_assoc()) {
    $chart_labels[] = $row['company_name'];
    $total_jobs[] = $row['total_jobs'];
    $deleted_jobs[] = $row['deleted_jobs'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employers Dashboard</title>
<link rel="icon" type="image/png" href="icon2.png">
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f0f4f8;
}

/* sidebar.css - static sidebar */
.sidebar {
    width: 260px;
    background: #1e293b;
    color: #fff;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    padding-top: 1rem;
    position: fixed;      /* make sidebar fixed/static */
    top: 0;
    left: 0;
    bottom: 0;
    overflow-y: auto;     /* allow scrolling if sidebar content exceeds height */
}

.sidebar-header {
    font-size: 1.5rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 1.5rem;
}

.sidebar .nav-link {
    color: #cbd5e1;
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1rem;
    border-radius: 8px;
    margin: 0.25rem 0.5rem;
    transition: background 0.3s;
}

.sidebar .nav-link:hover {
    background: #334155;
    color: #fff;
}

/* Adjust main content to not overlap sidebar */
.main-content {
    margin-left: 260px;  /* same width as sidebar */
    padding: 20px;
}


/* Main content fix */
.main-content {
    margin-left: 260px; /* Same as sidebar width */
    padding: 20px;
}

@media (max-width: 992px) {
    .sidebar {
        transform: translateX(-100%);
    }
    .sidebar.active {
        transform: translateX(0);
        z-index: 9999;
    }
    .sidebar-toggle {
        display: block;
    }
    .main-content {
        margin-left: 0 !important;
    }
}



.card {
    border-radius: 15px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    transition: transform 0.3s ease;
    background: #fff;
}
.card:hover {
    transform: translateY(-4px);
}

.table-wrapper {
    overflow-x: auto;
}
thead th {
    background: #1f2937;
    color: #fff;
}
#jobChart {
    background: #fff;
    border-radius: 15px;
    padding: 15px;
}
</style>
</head>
<body>

<!-- Mobile Toggle Button -->
<button class="sidebar-toggle" onclick="document.querySelector('.sidebar').classList.toggle('active')">
    <i class="bi bi-list"></i> Menu
</button>

<?php include 'sidebar.php'; ?>

<div class="main-content container-fluid animate__animated animate__fadeIn">

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-2">
        <h2 class="mb-3 mb-md-0 fw-bold">📊 Employers Dashboard</h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="employers_deleted.php" class="btn btn-info rounded-pill px-4 shadow-sm">📜 Employer History</a>
            <a href="add_employer.php" class="btn btn-success rounded-pill px-4 shadow-sm">+ Add Employer</a>
        </div>
    </div>

    <!-- Search Bar -->
    <form method="GET" class="mb-3 d-flex flex-wrap gap-2">
        <input type="text" name="search" id="searchInput" class="form-control flex-grow-1"
               placeholder="Search employer by name, email, or company..."
               value="<?= htmlspecialchars($searchQuery) ?>" onkeyup="filterTable()">
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <!-- Employers Table -->
    <div class="card mb-4 p-3">
        <div class="table-wrapper">
            <table class="table table-hover align-middle text-nowrap" id="employerTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Company</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = $employer_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['company_name']) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['contact']) ?></td>
                        <td class="text-center">
                            <a href="edit_employer.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary rounded-pill px-3">✏ Edit</a>
                            <button class="btn btn-sm btn-danger rounded-pill px-3" onclick="confirmDelete(<?= $row['id'] ?>)">🗑 Delete</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Chart -->
    <div class="card p-4 mb-4">
        <h4 class="mb-3 fw-bold">📈 Job Analysis Overview</h4>
        <canvas id="jobChart" height="150"></canvas>
    </div>

    <!-- Report Section -->
    <div class="card p-4">
        <h4 class="mb-3 fw-bold">📝 Generate Employer Report</h4>
        <form method="GET" action="generate_report.php" target="_blank">
            <div class="mb-3">
                <label for="employerSelect" class="form-label">Select Employers</label>
                <select name="employer_ids[]" id="employerSelect" class="form-select" multiple required>
                    <?php
                    $employers_for_report = $conn->query("SELECT id, company_name FROM employers ORDER BY company_name ASC");
                    while($emp = $employers_for_report->fetch_assoc()):
                    ?>
                        <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['company_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Generate Report</button>
        </form>
    </div>
</div>

<script>
// Delete Confirmation
function confirmDelete(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This employer will be permanently deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'employers_crud.php?delete_id=' + id;
        }
    });
}

// Success Message
<?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
Swal.fire({
    icon: 'success',
    title: 'Deleted!',
    text: 'The employer has been removed successfully.',
    timer: 2000,
    showConfirmButton: false
});
<?php endif; ?>

// Live Table Filter
function filterTable() {
    let input = document.getElementById("searchInput").value.toLowerCase();
    let rows = document.querySelectorAll("#employerTable tbody tr");
    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}

// Chart.js
const ctx = document.getElementById('jobChart').getContext('2d');
const gradTotal = ctx.createLinearGradient(0,0,0,400);
gradTotal.addColorStop(0,'rgba(54, 162, 235, 0.9)');
gradTotal.addColorStop(1,'rgba(54, 162, 235, 0.4)');
const gradDeleted = ctx.createLinearGradient(0,0,0,400);
gradDeleted.addColorStop(0,'rgba(255, 99, 132, 0.9)');
gradDeleted.addColorStop(1,'rgba(255, 99, 132, 0.4)');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [
            { label: 'Total Jobs', data: <?= json_encode($total_jobs) ?>, backgroundColor: gradTotal, borderRadius: 12 },
            { label: 'Deleted Jobs', data: <?= json_encode($deleted_jobs) ?>, backgroundColor: gradDeleted, borderRadius: 12 }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 14, weight: 'bold' }, usePointStyle: true } },
            datalabels: { anchor: 'end', align: 'top', color: '#333', font: { weight: 'bold', size: 12 } }
        },
        scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { stepSize: 1 } } },
        animation: { duration: 1500, easing: 'easeOutBounce' }
    },
    plugins: [ChartDataLabels]
});
</script>
<script>
$(document).ready(function(){
  // Sidebar toggle for mobile
  $('.sidebar-toggle').click(function(){
    $('.sidebar').toggleClass('active');
  });
});
</script>

</body>
</html>
