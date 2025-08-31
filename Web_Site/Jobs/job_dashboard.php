<?php
// Job Dashboard page
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="icon2.png">
<title>Job Dashboard</title>

<!-- Bootstrap & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

<!-- Sidebar CSS -->


<style>
body { font-family: 'Roboto', sans-serif; background: #f1f5f9; transition: all 0.3s ease; }
.main-content { margin-left:260px; padding:40px; min-height:100vh; display:flex; flex-direction:column; align-items:center; transition: all 0.3s ease; }
h1 { font-weight:700; font-size:2.5rem; margin-bottom:40px; opacity:0; animation: fadeSlideDown 0.8s forwards; text-align:center; }
.dashboard-buttons { display:flex; flex-wrap:wrap; gap:30px; justify-content:center; }
.sidebar { width: 260px; background: #1e293b; color: #fff; flex-shrink: 0; display: flex; flex-direction: column; padding-top: 1rem; position: fixed; top: 0; left: 0; bottom: 0; overflow-y: auto; }
.sidebar-header { font-size: 1.5rem; font-weight: 700; text-align: center; margin-bottom: 1.5rem; }
.sidebar .nav-link { color: #cbd5e1; padding: 0.75rem 1rem; display: flex; align-items: center; gap: 0.75rem; font-size: 1rem; border-radius: 8px; margin: 0.25rem 0.5rem; transition: background 0.3s; }
.sidebar .nav-link:hover { background: #334155; color: #fff; }

.dashboard-buttons a { 
    width:250px; height:180px; display:flex; flex-direction:column; align-items:center; justify-content:center; 
    text-decoration:none; font-size:1.2rem; font-weight:600; border-radius:20px; 
    color:#fff; box-shadow:0 8px 20px rgba(0,0,0,0.15); transition: all 0.4s ease; position:relative; overflow:hidden;
}
.dashboard-buttons a i { font-size:2.5rem; margin-bottom:10px; transition: transform 0.4s; }

.dashboard-buttons a.job { background: linear-gradient(135deg,#6610f2,#4b2998); }
.dashboard-buttons a.hires { background: linear-gradient(135deg,#0d6efd,#6610f2); }
.dashboard-buttons a.applicants { background: linear-gradient(135deg,#198754,#0dcaf0); }

.dashboard-buttons a:hover { transform: translateY(-5px) scale(1.05); box-shadow:0 16px 36px rgba(0,0,0,0.25); }
.dashboard-buttons a:hover i { transform: rotate(15deg) scale(1.2); }

.dashboard-buttons a span { z-index:1; text-align:center; }

footer.footer { margin-top:auto; padding:20px 0; background:#fff; box-shadow:0 -2px 10px rgba(0,0,0,0.05); font-size:0.9rem; color:#6c757d; text-align:center; border-radius:12px 12px 0 0; width:100%; }

#sidebarToggle { display:none; margin-bottom:20px; }
@media(max-width:992px){ 
    #sidebarToggle { display:inline-block; } 
    body.sidebar-hidden .main-content { margin-left:0 !important; }
}

@keyframes fadeSlideDown { to { opacity:1; } }
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
  <button id="sidebarToggle" class="btn btn-outline-primary mb-3 shadow-sm">
    <i class="bi bi-list"></i> Menu
  </button>

  <h1>Job Management Dashboard</h1>

  <div class="dashboard-buttons">
    <a href="job_management.php" class="job">
      <i class="bi bi-briefcase"></i>
      <span>Job Management</span>
    </a>
    <a href="job_hires.php" class="hires">
      <i class="bi bi-people"></i>
      <span>Job Hires Management</span>
    </a>
    <a href="job_applicants.php" class="applicants">
      <i class="bi bi-person-lines-fill"></i>
      <span>Job Applicants Management</span>
    </a>
  </div>

  <footer class="footer">&copy; <?= date('Y') ?> Hire Us System. All rights reserved.</footer>
</div>

<!-- Scripts -->
<script>
document.getElementById('sidebarToggle').addEventListener('click', ()=>{
    document.body.classList.toggle('sidebar-hidden');
});
</script>
</body>
</html>
