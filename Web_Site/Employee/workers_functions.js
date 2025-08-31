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