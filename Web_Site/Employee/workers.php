<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Hire Us System - Workers Management</title>
<link rel="icon" type="image/png" href="icon2.png">

<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<link rel="stylesheet" href="sidebar.css">

<style>
body { font-family: 'Segoe UI', sans-serif; background:#f0f4f8; margin-left:290px; }
.container-main { padding:20px; }
h1 { text-align:center; margin-bottom:30px; font-weight:600; }
.table-responsive { max-height:500px; overflow-y:auto; }
.image-preview { max-height:120px; border-radius:8px; border:1px solid #ced4da; margin-top:8px; object-fit:contain; }
.modal-content { border-radius:1rem; }
.modal-header { background:#0d6efd; color:#fff; border-top-left-radius:1rem; border-top-right-radius:1rem; }
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="container-main">
<h1>Workers Management</h1>

<div class="d-flex flex-wrap justify-content-between mb-3 gap-2">
  <div class="d-flex gap-2">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#workerModal" onclick="resetForm()">
      <i class="bi bi-person-plus-fill"></i> Add Worker
    </button>
    <a href="deleted_workers_report.php"><button class="btn btn-secondary" id="viewHistoryTopBtn">
      <i class="bi bi-clock-history"></i> Worker History
    </button></a>
  </div>
  <input type="search" id="searchInput" class="form-control w-25" placeholder="Search by Name, Username or Job">
  <button class="btn btn-success" id="generateReportBtn" disabled>
    <i class="bi bi-file-earmark-spreadsheet-fill"></i> Generate Report
  </button>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle" id="workerTable">
      <thead class="table-primary">
        <tr>
          <th><input type="checkbox" id="selectAll"></th>
          <th>Full Name</th>
          <th>Username</th>
          <th>Contact</th>
          <th>Email</th>
          <th>ID Number</th>
          <th>Job</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="workerTableBody">
        <?php
        $conn = new mysqli('localhost','u569550465_math_rakusa','Sithija2025#','u569550465_hireme');
        if($conn->connect_error){ echo "<tr><td colspan='9'>DB connection failed</td></tr>"; }
        else{
          $res = $conn->query("SELECT * FROM workers ORDER BY fullName ASC");
          if($res->num_rows>0){
            while($w=$res->fetch_assoc()){
              $status = ucwords(str_replace('_',' ',$w['status']??'not_hired'));
              echo "<tr data-name='".strtolower($w['fullName'])."' data-username='".strtolower($w['username'])."' data-job='".strtolower($w['jobTitle'])."'>";
              echo "<td><input type='checkbox' class='selectWorker' value='{$w['id']}'></td>";
              echo "<td>{$w['fullName']}</td>";
              echo "<td>{$w['username']}</td>";
              echo "<td>{$w['contactNumber']}</td>";
              echo "<td>{$w['email']}</td>";
              echo "<td>{$w['idNumber']}</td>";
              echo "<td>{$w['jobTitle']}</td>";
              echo "<td>{$status}</td>";
              echo "<td>
                      <button class='btn btn-sm btn-info' onclick='editWorker({$w['id']})'><i class='bi bi-pencil-square'></i></button>
                      <button class='btn btn-sm btn-danger' onclick='deleteWorker({$w['id']})'><i class='bi bi-trash-fill'></i></button>
                    </td>";
              echo "</tr>";
            }
          } else { echo "<tr><td colspan='9'>No workers found</td></tr>"; }
          $conn->close();
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Worker Modal -->
<div class="modal fade" id="workerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <form class="modal-content" id="workerForm">
      <div class="modal-header">
        <h5 class="modal-title" id="workerModalLabel">Add Worker</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="id" value="">

        <div class="row g-3">
          <div class="col-md-6"><label>Full Name</label><input type="text" class="form-control" name="fullName" required></div>
          <div class="col-md-6"><label>Username</label><input type="text" class="form-control" name="username" required></div>
          <div class="col-md-6"><label>Contact Number</label><input type="text" class="form-control" name="contactNumber" required></div>
          <div class="col-md-6"><label>Email</label><input type="email" class="form-control" name="email" required></div>
          <div class="col-md-6"><label>ID Number</label><input type="text" class="form-control" name="idNumber" required></div>
          <div class="col-md-6"><label>Job Title</label><input type="text" class="form-control" name="jobTitle" required></div>
          <div class="col-md-6"><label>Permanent Address</label><textarea class="form-control" name="permanentAddress" required></textarea></div>
          <div class="col-md-6"><label>Current Address</label><textarea class="form-control" name="currentAddress"></textarea></div>
          <div class="col-md-6"><label>Work Experience</label><textarea class="form-control" name="workExperience" required></textarea></div>
          <div class="col-md-6"><label>Bank Account Number</label><input type="text" class="form-control" name="bankAccountNumber" required></div>
          <div class="col-md-6"><label>Bank Name</label><input type="text" class="form-control" name="bankName" required></div>
          <div class="col-md-6"><label>Bank Branch</label><input type="text" class="form-control" name="bankBranch" required></div>
          <div class="col-md-6"><label>Password</label><input type="password" class="form-control" name="password" required></div>
          <div class="col-md-6"><label>Confirm Password</label><input type="password" class="form-control" name="confirmPassword" required></div>
          <div class="col-md-6"><label>ID Front</label><input type="file" class="form-control" id="idFrontImage" name="idFrontImage" accept="image/*"><img id="previewFront" class="image-preview" style="display:none;"></div>
          <div class="col-md-6"><label>ID Back</label><input type="file" class="form-control" id="idBackImage" name="idBackImage" accept="image/*"><img id="previewBack" class="image-preview" style="display:none;"></div>
          <div class="col-md-6"><label>Status</label><select class="form-select" name="status" required><option value="not_hired">Not Hired</option><option value="hired">Hired</option></select></div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save Worker</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Image preview
['idFrontImage','idBackImage'].forEach(id=>{
  document.getElementById(id).addEventListener('change', e=>{
    const preview=document.getElementById(id==='idFrontImage'?'previewFront':'previewBack');
    if(e.target.files && e.target.files[0]){
      const reader=new FileReader();
      reader.onload=ev=>{ preview.src=ev.target.result; preview.style.display='block'; }
      reader.readAsDataURL(e.target.files[0]);
    } else { preview.src=''; preview.style.display='none'; }
  });
});

// Reset form
function resetForm(){
  const form=document.getElementById('workerForm');
  form.reset();
  form.action.value='add';
  form.id.value='';
  document.getElementById('workerModalLabel').innerText='Add Worker';
  ['previewFront','previewBack'].forEach(id=>document.getElementById(id).style.display='none');
  form.password.required=true;
  form.confirmPassword.required=true;
}

// Submit Add/Update
document.getElementById('workerForm').addEventListener('submit', e=>{
  e.preventDefault();
  const form=document.getElementById('workerForm');
  const idNum=form.idNumber.value.trim();
  const idRegex=/^[0-9]{9}[VvXx]$|^[0-9]{12}$/;
  if(!idRegex.test(idNum)){ Swal.fire('Invalid ID Number'); return; }
  if(form.action.value==='add' && form.password.value!==form.confirmPassword.value){ Swal.fire('Passwords do not match'); return; }

  Swal.fire({title:'Save Worker?', icon:'question', showCancelButton:true, confirmButtonText:'Yes'}).then(result=>{
    if(result.isConfirmed){
      fetch('save_worker.php',{method:'POST', body:new FormData(form)})
      .then(res=>res.json())
      .then(data=>{
        if(data.success){ Swal.fire('Saved!', data.message,'success').then(()=>location.reload()); }
        else{ Swal.fire('Error', data.message,'error'); }
      }).catch(()=>Swal.fire('Error','Failed to save worker','error'));
    }
  });
});

// Edit Worker
function editWorker(id){
  fetch('fetch_worker.php?id='+id)
  .then(res=>res.json())
  .then(data=>{
    if(data.success){
      const w=data.worker;
      const form=document.getElementById('workerForm');
      form.action.value='update';
      form.id.value=w.id;
      form.fullName.value=w.fullName;
      form.username.value=w.username;
      form.contactNumber.value=w.contactNumber;
      form.email.value=w.email;
      form.idNumber.value=w.idNumber;
      form.jobTitle.value=w.jobTitle;
      form.permanentAddress.value=w.permanentAddress;
      form.currentAddress.value=w.currentAddress;
      form.workExperience.value=w.workExperience;
      form.bankAccountNumber.value=w.bankAccountNumber;
      form.bankName.value=w.bankName;
      form.bankBranch.value=w.bankBranch;
      form.status.value=w.status;
      form.password.required=false;
      form.confirmPassword.required=false;
      if(w.idFrontImage){ document.getElementById('previewFront').src='../../uploads_employee/'+w.idFrontImage; document.getElementById('previewFront').style.display='block'; }
      if(w.idBackImage){ document.getElementById('previewBack').src='../../uploads_employee/'+w.idBackImage; document.getElementById('previewBack').style.display='block'; }
      document.getElementById('workerModalLabel').innerText='Edit Worker';
      new bootstrap.Modal(document.getElementById('workerModal')).show();
    } else Swal.fire('Error','Worker not found','error');
  });
}

// Delete Worker
function deleteWorker(id){
  Swal.fire({title:'Delete Worker?', icon:'warning', showCancelButton:true, confirmButtonText:'Yes'})
  .then(result=>{ if(result.isConfirmed){
    fetch('delete_worker.php?id='+id,{method:'DELETE'})
    .then(res=>res.json())
    .then(data=>{ if(data.success){ Swal.fire('Deleted',data.message,'success').then(()=>location.reload()); } else Swal.fire('Error',data.message,'error'); })
    .catch(()=>Swal.fire('Error','Failed to delete','error'));
  }});
}

// Search
document.getElementById('searchInput').addEventListener('input', function(){
  const val=this.value.toLowerCase();
  document.querySelectorAll('#workerTableBody tr').forEach(row=>{
    row.style.display=[row.dataset.name,row.dataset.username,row.dataset.job].some(d=>d.includes(val))?'':'none';
  });
});

// Checkboxes & Report
const selectAll=document.getElementById('selectAll');
const generateBtn=document.getElementById('generateReportBtn');
function updateGenerateBtn(){ generateBtn.disabled = ![...document.querySelectorAll('.selectWorker')].some(cb=>cb.checked); }
selectAll.addEventListener('change', function(){ document.querySelectorAll('.selectWorker').forEach(cb=>cb.checked=this.checked); updateGenerateBtn(); });
document.querySelectorAll('.selectWorker').forEach(cb=>cb.addEventListener('change',updateGenerateBtn));

generateBtn.addEventListener('click', function(){
  const ids=[...document.querySelectorAll('.selectWorker:checked')].map(cb=>cb.value);
  if(ids.length===0){ Swal.fire('No workers selected'); return; }
  const form=document.createElement('form'); form.method='POST'; form.action='generate_report.php'; form.target='_blank';
  ids.forEach(id=>{ const input=document.createElement('input'); input.type='hidden'; input.name='worker_ids[]'; input.value=id; form.appendChild(input); });
  document.body.appendChild(form); form.submit(); document.body.removeChild(form);
});

// Worker History Top Button
document.getElementById('viewHistoryTopBtn').addEventListener('click', function(){
  window.location.href = 'worker_history.php';
});
</script>

</body>
</html>
