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

$message = '';
$alert_type = 'info';

// ------------------- HANDLE PERMANENT DELETE -------------------
if (isset($_GET['delete_perm_id'])) {
    $del_id = intval($_GET['delete_perm_id']);
    try {
        $conn->query("DELETE FROM job_hires_log WHERE id=$del_id");
        $message = "Job hire permanently deleted!";
        $alert_type = "success";
    } catch (Exception $e) {
        $message = "Error deleting record: " . $e->getMessage();
        $alert_type = "danger";
    }
}

// ------------------- HANDLE RESTORE -------------------
if (isset($_GET['restore_id'])) {
    $restore_id = intval($_GET['restore_id']);
    // Fetch record from log
    $res = $conn->query("SELECT * FROM job_hires_log WHERE id=$restore_id");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        // Insert back into main table
        $stmt = $conn->prepare("INSERT INTO job_hires (job_id, id_number, hired_at, wants_meals) VALUES (?,?,?,?)");
        $stmt->bind_param("issi", $row['job_id'], $row['id_number'], $row['hired_at'], $row['wants_meals']);
        if($stmt->execute()) {
            $conn->query("DELETE FROM job_hires_log WHERE id=$restore_id");
            $message = "Job hire restored successfully!";
            $alert_type = "success";
        } else {
            $message = "Error restoring record: " . $stmt->error;
            $alert_type = "danger";
        }
        $stmt->close();
    } else {
        $message = "Record not found!";
        $alert_type = "warning";
    }
}

// ------------------- DATE FILTER -------------------
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$where = [];
if ($start_date) $where[] = "deleted_at >= '$start_date 00:00:00'";
if ($end_date) $where[] = "deleted_at <= '$end_date 23:59:59'";
$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

// ------------------- FETCH HIRES LOG -------------------
$hires_log = $conn->query("
    SELECT jhl.*, j.job_title, w.fullName
    FROM job_hires_log jhl
    LEFT JOIN jobs j ON jhl.job_id = j.id
    LEFT JOIN workers w ON jhl.id_number COLLATE utf8mb4_general_ci = w.idNumber
    $where_sql
    ORDER BY jhl.deleted_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Job Hires History</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="icon" type="image/png" href="icon2.png">
<style>
body { font-family: 'Segoe UI', sans-serif; background: #f1f5f9; margin: 0; }
.main-content { margin-left: 260px; padding: 20px; transition: margin-left 0.3s; }
.sidebar { width: 260px; background: #1e293b; color: #fff; flex-shrink: 0; display: flex; flex-direction: column; padding-top: 1rem; position: fixed; top: 0; left: 0; bottom: 0; overflow-y: auto; }
.sidebar-header { font-size: 1.5rem; font-weight: 700; text-align: center; margin-bottom: 1.5rem; }
.sidebar .nav-link { color: #cbd5e1; padding: 0.75rem 1rem; display: flex; align-items: center; gap: 0.75rem; font-size: 1rem; border-radius: 8px; margin: 0.25rem 0.5rem; transition: background 0.3s; }
.sidebar .nav-link:hover { background: #334155; color: #fff; }
.card { border-radius: 8px; box-shadow:0 2px 8px rgba(0,0,0,0.1); margin-bottom:20px; }
@media (max-width:768px) { .main-content { margin-left:0; padding:15px; } .sidebar { left:-260px; } }
</style>
</head>
<body>
<div class="d-flex">
    <?php include 'sidebar.php'; ?>

    <div class="main-content container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Job Hires History</h2>
            <a href="job_hires.php" class="btn btn-secondary">Back to Hires</a>
        </div>

        <!-- Alert -->
        <?php if(!empty($message)): ?>
        <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Date Filter + Search -->
        <form method="GET" class="row g-3 mb-3">
            <div class="col-md-3">
                <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" class="form-control">
            </div>
            <div class="col-md-3">
                <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" class="form-control">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-success w-100" onclick="printReport()">Print Report</button>
            </div>
            <div class="col-md-2">
                <input type="text" id="dynamicSearch" class="form-control" placeholder="Search Job Hires...">
            </div>
        </form>

        <!-- Table -->
        <div class="card">
            <div class="card-body">
                <table id="historyTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Job</th>
                            <th>Worker</th>
                            <th>Hired At</th>
                            <th>Wants Meals</th>
                            <th>Deleted At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($hires_log && $hires_log->num_rows>0): ?>
                        <?php while($row = $hires_log->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['job_title'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($row['fullName'] ?? 'N/A') ?></td>
                            <td><?= $row['hired_at'] ?? 'N/A' ?></td>
                            <td><?= $row['wants_meals'] ?? 'No' ?></td>
                            <td><?= $row['deleted_at'] ?? 'N/A' ?></td>
                            <td>
                                <button class="btn btn-sm btn-success restore-btn" data-id="<?= $row['id'] ?>">Restore</button>
                                <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $row['id'] ?>">Delete</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr><td colspan="7" class="text-center">No records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    var table = $('#historyTable').DataTable({ order: [[5,'desc']], pageLength:25 });
    $('#dynamicSearch').on('keyup', function(){ table.search(this.value).draw(); });

    // SweetAlert2 delete
    $('.delete-btn').on('click', function(){
        var id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This record will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '?delete_perm_id=' + id;
            }
        });
    });

    // SweetAlert2 restore
    $('.restore-btn').on('click', function(){
        var id = $(this).data('id');
        Swal.fire({
            title: 'Restore this record?',
            text: "This will move it back to the main Job Hires table.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, restore it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '?restore_id=' + id;
            }
        });
    });
});

// Print report
function printReport(){
    let printContent = document.querySelector('.main-content').innerHTML;
    let win = window.open('', '', 'width=1200,height=800');
    win.document.write('<html><head><title>Hire Us System - Job Hires Report</title>');
    win.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">');
    win.document.write('</head><body>');
    win.document.write('<h2 class="text-center my-4">Hire Us System - Job Hires Report</h2>');
    win.document.write(printContent);
    win.document.write('</body></html>');
    win.document.close();
    win.print();
}
</script>
</body>
</html>
<?php $conn->close(); ?>
