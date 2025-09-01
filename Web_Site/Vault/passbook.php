<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$db_username = "u569550465_math_rakusa";
$db_password = "Sithija2025#";
$db_name = "u569550465_hireme";

$conn = new mysqli($servername, $db_username, $db_password, $db_name);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// =======================
// Add/Edit/Delete Transaction
if(isset($_POST['add_record'])){
    $idNumber = $_POST['idNumber'];
    $job_id = $_POST['job_id'];
    $salary = $_POST['salary'];
    $ot_hours = $_POST['ot_hours'];
    $ot_salary = $_POST['ot_salary'];
    $transaction_type = $_POST['transaction_type'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("INSERT INTO vault (idNumber, job_id, salary, ot_hours, ot_salary, transaction_type, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sidddss", $idNumber, $job_id, $salary, $ot_hours, $ot_salary, $transaction_type, $status);
    $stmt->execute(); $stmt->close();
}
if(isset($_POST['edit_record'])){
    $id = $_POST['edit_id'];
    $salary = $_POST['edit_salary'];
    $ot_hours = $_POST['edit_ot_hours'];
    $ot_salary = $_POST['edit_ot_salary'];
    $transaction_type = $_POST['edit_transaction_type'];
    $status = $_POST['edit_status'];
    $stmt = $conn->prepare("UPDATE vault SET salary=?, ot_hours=?, ot_salary=?, transaction_type=?, status=? WHERE id=?");
    $stmt->bind_param("dddssi", $salary, $ot_hours, $ot_salary, $transaction_type, $status, $id);
    $stmt->execute(); $stmt->close();
}
if(isset($_POST['delete_record'])){
    $delete_id = $_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM vault WHERE id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute(); $stmt->close();
}

// =======================
// Get All Workers and Total Amount per Worker
$workers_res = $conn->query("SELECT * FROM workers ORDER BY fullName ASC");
$workers = $workers_res->fetch_all(MYSQLI_ASSOC);

$worker_totals = [];
foreach($workers as $w){
    $id = $w['idNumber'];
    $stmt = $conn->prepare("SELECT salary, ot_hours, ot_salary, transaction_type FROM vault WHERE idNumber=?");
    $stmt->bind_param("s",$id); $stmt->execute();
    $res = $stmt->get_result();
    $txns = $res->fetch_all(MYSQLI_ASSOC); $stmt->close();
    $total=0;
    foreach($txns as $txn){
        $amount = $txn['salary'] + ($txn['ot_hours']*$txn['ot_salary']);
        $total += ($txn['transaction_type']=='credit')?$amount:-$amount;
    }
    $worker_totals[$w['fullName']] = $total;
}

// =======================
// Selected Worker Transactions
$selected_worker = null;
$transactions = [];
$total_amount = 0;
if(isset($_GET['idNumber'])){
    $idNumber = $_GET['idNumber'];
    $stmt = $conn->prepare("SELECT * FROM workers WHERE idNumber=?");
    $stmt->bind_param("s", $idNumber);
    $stmt->execute();
    $res = $stmt->get_result(); $selected_worker = $res->fetch_assoc(); $stmt->close();

    $stmt = $conn->prepare("SELECT v.*, d.job_title FROM vault v LEFT JOIN deleted_jobs d ON v.job_id=d.id WHERE v.idNumber=? ORDER BY v.updated_at DESC");
    $stmt->bind_param("s", $idNumber); $stmt->execute();
    $res = $stmt->get_result(); $transactions = $res->fetch_all(MYSQLI_ASSOC); $stmt->close();

    foreach($transactions as $txn){
        $amount = floatval($txn['salary']) + floatval($txn['ot_hours'])*floatval($txn['ot_salary']);
        $total_amount += ($txn['transaction_type']=='credit')?$amount:-$amount;
    }
}

// =======================
// Filtered Report
$report_transactions=[]; $report_total=0;
if(isset($_POST['generate_report'])){
    $idNumber = $_POST['idNumber']; $start_date=$_POST['start_date']; $end_date=$_POST['end_date'];
    $stmt = $conn->prepare("SELECT v.*, d.job_title FROM vault v LEFT JOIN deleted_jobs d ON v.job_id=d.id WHERE v.idNumber=? AND v.updated_at BETWEEN ? AND ? ORDER BY v.updated_at ASC");
    $stmt->bind_param("sss",$idNumber,$start_date,$end_date); $stmt->execute();
    $res = $stmt->get_result(); $report_transactions = $res->fetch_all(MYSQLI_ASSOC); $stmt->close();
    foreach($report_transactions as $txn){
        $amount = floatval($txn['salary']) + floatval($txn['ot_hours'])*floatval($txn['ot_salary']);
        $report_total += ($txn['transaction_type']=='credit')?$amount:-$amount;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="icon" type="image/png" href="icon2.png">
<title>Hire Us System - Workers Management</title>

<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
<!-- SweetAlert2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Custom CSS -->
<link href="style.css" rel="stylesheet" />
<style>
/* =====================
   General Layout
===================== */
body {
  margin: 0;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  min-height: 100vh;
  background: #f0f4f8;
}

/* =====================
   Sidebar
===================== */
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  height: 100%;
  width: 260px;
  background: #1e293b;
  color: #fff;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  padding-top: 1rem;
  z-index: 1050;
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

/* =====================
   Main Content
===================== */
.main-content {
  margin-left: 260px;
  padding: 20px;
  transition: margin-left 0.3s ease;
}

/* =====================
   Responsive Sidebar
===================== */
@media (max-width: 768px) {
  .sidebar {
    width: 220px;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
  }
  .sidebar.active {
    transform: translateX(0);
  }
  .main-content {
    margin-left: 0;
  }
  .sidebar-toggle {
    position: fixed;
    top: 15px;
    left: 15px;
    z-index: 1100;
  }
}

/* =====================
   Animations
===================== */
.fade-in {
  opacity: 0;
  transform: translateY(20px);
  animation: fadeInUp 0.8s forwards;
}
@keyframes fadeInUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* =====================
   Table + Cards
===================== */
.credit {
  color: green;
  font-weight: bold;
}
.debit {
  color: red;
  font-weight: bold;
}
.card-header {
  background: #0d6efd;
  color: white;
  font-weight: bold;
}
#transactionsTable tbody tr:hover {
  background-color: rgba(13, 110, 253, 0.1);
  transition: background-color 0.3s ease;
}

/* =====================
   Print Styles
===================== */
.printable {
  display: none;
}
@media print {
  body * {
    visibility: hidden;
  }
  #printableReport,
  #printableReport * {
    visibility: visible;
  }
  #printableReport {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
  }
}
#printableReport h2 {
  text-align: center;
  color: #0d6efd;
  margin-bottom: 20px;
}

/* =====================
   Buttons + Footer
===================== */
.btn:hover {
  transform: scale(1.05);
  transition: transform 0.3s ease;
}
footer.footer {
  margin-top: 50px;
  padding: 15px 0;
  background: #fff;
  box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.05);
  font-size: 0.9rem;
  color: #6c757d;
  text-align: center;
  border-radius: 0 0 12px 12px;
}

/* =====================
   Report Table
===================== */
.report-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
}
.report-table th,
.report-table td {
  border: 1px solid #dee2e6;
  padding: 8px;
  font-size: 14px;
  text-align: center;
}
.report-table th {
  background: #0d6efd;
  color: white;
}
.report-table tfoot td {
  font-weight: bold;
}
.text-right {
  text-align: right;
}
.final-row {
  background: #f8f9fa;
}
.footer-note {
  margin-top: 30px;
  text-align: center;
  font-size: 12px;
  color: #6c757d;
}


</style>
</head>
<body>
    <button class="btn btn-primary sidebar-toggle d-md-none">
        <i class="bi bi-list"></i>
    </button>

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

<div class="main-content container my-4">

<h2 class="mb-3 text-center">Workers Passbook</h2>

<table id="workersTable" class="table table-striped table-bordered fade-in">
<thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Job</th><th>Action</th></tr></thead>
<tbody>
<?php foreach($workers as $w){ ?>
<tr>
<td><?= $w['idNumber'] ?></td>
<td><?= $w['fullName'] ?></td>
<td><?= $w['email'] ?></td>
<td><?= $w['jobTitle'] ?></td>
<td><a href="?idNumber=<?= $w['idNumber'] ?>" class="btn btn-primary btn-sm">Select</a></td>
</tr>
<?php } ?>
</tbody>
</table>

<div class="card my-4 fade-in">
<div class="card-header">Workers Total Amount Analysis</div>
<div class="card-body">
<canvas id="workerChart"></canvas>
</div>
</div>

<?php if($selected_worker){ ?>
<hr>
<div class="card mb-3 fade-in">
<div class="card-header"><?= $selected_worker['fullName'] ?> - Transactions (Total: <span class="<?=($total_amount>=0?'credit':'debit')?>"><?=number_format($total_amount,2)?></span>)</div>
<div class="card-body">

<form method="post" class="row g-3 mb-3">
<input type="hidden" name="idNumber" value="<?= $selected_worker['idNumber'] ?>">
<div class="col-md-3"><input type="date" class="form-control" name="start_date" required></div>
<div class="col-md-3"><input type="date" class="form-control" name="end_date" required></div>
<div class="col-md-3"><button type="submit" name="generate_report" class="btn btn-info">Generate Report</button></div>
</form>
<!-- Print Button -->
<div style="text-align:right; margin-bottom:15px;">
    <button onclick="printReport()" 
            style="background:#0d6efd; color:white; border:none; padding:10px 20px; 
                   border-radius:5px; font-size:14px; cursor:pointer;">
        🖨️ Print Report
    </button>
</div>

<!-- Your Report -->
<div id="printableReport">
    <div style="text-align:center; margin-bottom:20px;">
        <img src="icon2.png" alt="Logo" style="height:60px; margin-bottom:10px;">
        <h2 style="color:#0d6efd; margin:0;">HireMe System - Worker Withdraw Report</h2>
    </div>

    <div style="margin-bottom:20px;">
        <h5>Worker Details</h5>
        <p>
            <strong>Name:</strong> <?= $selected_worker['fullName']?> <br>
            <strong>ID:</strong> <?= $selected_worker['idNumber']?> <br>
            <strong>Email:</strong> <?= $selected_worker['email']?> <br>
            <strong>Job Title:</strong> <?= $selected_worker['jobTitle']?> 
        </p>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Job Title</th>
                <th>Salary</th>
                <th>OT Hours</th>
                <th>OT Salary</th>
                <th>Type</th>
                <th>Status</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        $total_credit=0; $total_debit=0;
        foreach($report_transactions as $txn): 
            $amount = $txn['salary'] + ($txn['ot_hours']*$txn['ot_salary']);
            if($txn['transaction_type']=='credit'){ $total_credit+=$amount; }
            else { $total_debit+=$amount; }
        ?>
            <tr>
                <td><?= $txn['updated_at']?></td>
                <td><?= $txn['job_title']?></td>
                <td><?= number_format($txn['salary'],2)?></td>
                <td><?= $txn['ot_hours']?></td>
                <td><?= number_format($txn['ot_salary'],2)?></td>
                <td><span class="<?= $txn['transaction_type']?>"><?= ucfirst($txn['transaction_type'])?></span></td>
                <td><?= ucfirst($txn['status'])?></td>
                <td><span class="<?= $txn['transaction_type']?>"><?=($txn['transaction_type']=='credit'?'+':'-').number_format($amount,2)?></span></td>
            </tr>
        <?php endforeach;?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-right">Total Credit</td>
                <td class="credit">+<?= number_format($total_credit,2)?></td>
            </tr>
            <tr>
                <td colspan="7" class="text-right">Total Debit</td>
                <td class="debit">-<?= number_format($total_debit,2)?></td>
            </tr>
            <tr class="final-row">
                <td colspan="7" class="text-right">Final Balance</td>
                <td class="<?= ($report_total>=0?'credit':'debit')?>"><?= number_format($report_total,2)?></td>
            </tr>
        </tfoot>
    </table>

    <p class="footer-note">
        Generated on <?= date("Y-m-d H:i") ?> by HireMe System
    </p>
</div>

<!-- JS for Print -->
<script>
function printReport() {
    var content = document.getElementById("printableReport").innerHTML;
    var printWindow = window.open('', '', 'height=800,width=1000');
    printWindow.document.write('<html><head><title>Worker Report</title>');
    printWindow.document.write('<style>');
    printWindow.document.write(`

        body { font-family: Arial, sans-serif; margin: 20px; color:#333; }
        h2 { color:#0d6efd; margin-bottom:5px; }
        p { margin:4px 0; }
        
        .report-table {
            width:100%; border-collapse: collapse; margin-top:15px;
        }
        .report-table th, .report-table td {
            border:1px solid #dee2e6;
            padding:8px;
            font-size:14px;
            text-align:center;
        }
        .report-table th {
            background:#0d6efd;
            color:white;
        }
        .report-table tfoot td {
            font-weight:bold;
        }
        .text-right { text-align:right; }
        .credit { color:green; font-weight:bold; }
        .debit { color:red; font-weight:bold; }
        .final-row { background:#f8f9fa; }
        
        .footer-note {
            margin-top:30px;
            text-align:center;
            font-size:12px;
            color:#6c757d;
        }

    `);
    printWindow.document.write('</style></head><body>');
    printWindow.document.write(content);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}
</script>


<table id="transactionsTable" class="table table-striped table-bordered fade-in">
<thead>
<tr><th>Date</th><th>Job Title</th><th>Salary</th><th>OT Hours</th><th>OT Salary</th><th>Type</th><th>Status</th><th>Total</th><th>Actions</th></tr>
</thead>
<tbody>
<?php foreach($transactions as $txn):
$total = $txn['salary'] + ($txn['ot_hours']*$txn['ot_salary']); ?>
<tr>
<td><?= $txn['updated_at']?></td>
<td><?= $txn['job_title']?></td>
<td><?= number_format($txn['salary'],2)?></td>
<td><?= $txn['ot_hours']?></td>
<td><?= number_format($txn['ot_salary'],2)?></td>
<td><span class="<?= $txn['transaction_type']?>"><?= ucfirst($txn['transaction_type'])?></span></td>
<td><?= ucfirst($txn['status'])?></td>
<td><span class="<?= $txn['transaction_type']?>"><?=($txn['transaction_type']=='credit'?'+':'-').number_format($total,2)?></span></td>
<td>
<button class="btn btn-warning btn-sm btn-edit" 
data-id="<?= $txn['id']?>" 
data-salary="<?= $txn['salary']?>" 
data-ot_hours="<?= $txn['ot_hours']?>"
data-ot_salary="<?= $txn['ot_salary']?>"
data-type="<?= $txn['transaction_type']?>"
data-status="<?= $txn['status']?>">Edit</button>
<button class="btn btn-danger btn-sm btn-delete" data-id="<?= $txn['id']?>">Delete</button>
</td>
</tr>
<?php endforeach;?>
</tbody>
</table>
</div>
</div>
<?php } ?>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">
<form method="post">
<div class="modal-header">
<h5 class="modal-title">Edit Transaction</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<input type="hidden" name="edit_id" id="edit_id">
<div class="mb-3"><label>Salary</label><input type="number" step="0.01" name="edit_salary" id="edit_salary" class="form-control" required></div>
<div class="mb-3"><label>OT Hours</label><input type="number" step="0.01" name="edit_ot_hours" id="edit_ot_hours" class="form-control" required></div>
<div class="mb-3"><label>OT Salary</label><input type="number" step="0.01" name="edit_ot_salary" id="edit_ot_salary" class="form-control" required></div>
<div class="mb-3"><label>Transaction Type</label>
<select name="edit_transaction_type" id="edit_transaction_type" class="form-control" required>
<option value="credit">Credit</option>
<option value="debit">Debit</option>
</select></div>
<div class="mb-3"><label>Status</label>
<select name="edit_status" id="edit_status" class="form-control" required>
<option value="paid">Paid</option>
<option value="pending">Pending</option>
</select></div>
</div>
<div class="modal-footer">
<button type="submit" name="edit_record" class="btn btn-primary">Save Changes</button>
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>
</form>
</div>
</div>
</div>

<footer class="footer">&copy; <?= date('Y') ?> Hire Us System. All rights reserved.</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function(){
    $('#workersTable').DataTable();
    $('#transactionsTable').DataTable();

    $('.btn-delete').click(function(){
        let id=$(this).data('id');
        Swal.fire({
            title:'Are you sure?',
            text:"You won't be able to revert this!",
            icon:'warning',
            showCancelButton:true,
            confirmButtonColor:'#3085d6',
            cancelButtonColor:'#d33',
            confirmButtonText:'Yes, delete it!'
        }).then((result)=>{
            if(result.isConfirmed){
                $('<form method="post"><input type="hidden" name="delete_id" value="'+id+'"><input type="hidden" name="delete_record" value="1"></form>').appendTo('body').submit();
            }
        });
    });

    $('.btn-edit').click(function(){
        $('#edit_id').val($(this).data('id'));
        $('#edit_salary').val($(this).data('salary'));
        $('#edit_ot_hours').val($(this).data('ot_hours'));
        $('#edit_ot_salary').val($(this).data('ot_salary'));
        $('#edit_transaction_type').val($(this).data('type'));
        $('#edit_status').val($(this).data('status'));
        $('#editModal').modal('show');
    });

    // Chart.js Bar Chart
    const ctx=document.getElementById('workerChart').getContext('2d');
    const chart=new Chart(ctx,{
        type:'bar',
        data:{
            labels:<?= json_encode(array_keys($worker_totals)) ?>,
            datasets:[{
                label:'Total Amount',
                data:<?= json_encode(array_values($worker_totals)) ?>,
                backgroundColor:'rgba(13,110,253,0.7)',
                borderColor:'rgba(13,110,253,1)',
                borderWidth:1
            }]
        },
        options:{
            responsive:true,
            animation: { duration:1500, easing:'easeOutBounce' },
            plugins:{ legend:{ display:true, position:'top' }, title:{ display:true, text:'Worker Total Amount Analysis' } },
            scales:{ y:{ beginAtZero:true } }
        }
    });
});
</script>
</body>
</html>
