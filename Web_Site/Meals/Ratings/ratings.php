<?php
// DB Connection
$conn = new mysqli("localhost", "u569550465_math_rakusa", "Sithija2025#", "u569550465_hireme");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch all ratings with worker names
$sql = "SELECT r.id, w.fullName, r.rated_by, r.rating, r.feedback, r.job_title, r.company_name, r.duration
        FROM worker_ratings r
        JOIN workers w ON r.worker_id = w.id
        ORDER BY r.id DESC";
$result = $conn->query($sql);

// Fetch workers for dropdown
$workers = $conn->query("SELECT id, fullName FROM workers ORDER BY fullName");

// Bar Chart Data: Average ratings and counts per worker
$chartQuery = "SELECT w.fullName, 
                      ROUND(AVG(r.rating),2) AS avg_rating, 
                      COUNT(r.id) AS ratings_count
               FROM worker_ratings r
               JOIN workers w ON r.worker_id = w.id
               GROUP BY r.worker_id
               ORDER BY avg_rating DESC";
$chartRes = $conn->query($chartQuery);

$labels = [];
$avgRatings = [];
$countRatings = [];
while ($row = $chartRes->fetch_assoc()) {
    $labels[] = $row['fullName'];
    $avgRatings[] = $row['avg_rating'];
    $countRatings[] = $row['ratings_count'];
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

<style>
 /* ==== Global Reset & Body ==== */
body {
  margin: 0;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  min-height: 100vh;
  background: linear-gradient(135deg, #e0e7ff 0%, #dbeafe 100%);
  color: #2c3e50;
}

/* ==== Sidebar (Fixed) ==== */
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  height: 100%;
  width: 260px;
  background: #1e293b;
  color: #fff;
  display: flex;
  flex-direction: column;
  padding-top: 1rem;
  z-index: 1050;
  transition: transform 0.3s ease;
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

/* ==== Main Content ==== */
main.container {
  margin-left: 260px; /* shift away from sidebar */
  padding: 30px;
  transition: margin-left 0.3s ease;
}

/* ==== Sidebar Toggle Button (Mobile Only) ==== */
.sidebar-toggle {
  position: fixed;
  top: 15px;
  left: 15px;
  z-index: 1100;
  display: none; /* hidden on desktop */
}

/* ==== Cards, Tables, Buttons (Your Original Styling) ==== */
.container { max-width: 1200px; }
.card {
  border-radius: 1rem;
  box-shadow: 0 12px 30px rgba(0,0,0,0.12);
  background: #fff;
  transition: box-shadow 0.3s ease;
}
.card:hover { box-shadow: 0 18px 40px rgba(0,0,0,0.15); }

h2, h5 {
  font-weight: 700;
  color: #1e40af;
}
h5 i { color: #3b82f6; margin-right: 10px; }

.form-control, .form-select {
  border-radius: 15px;
  border: 1.5px solid #a5b4fc;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
  font-size: 1rem;
  padding: 0.55rem 1.2rem;
}
.form-control:focus, .form-select:focus {
  border-color: #2563eb;
  box-shadow: 0 0 10px rgba(59,130,246,0.5);
  outline: none;
}

/* Buttons */
.btn-primary {
  border-radius: 50px;
  padding: 0.65rem 1.8rem;
  font-weight: 700;
  background: linear-gradient(90deg, #2563eb 0%, #3b82f6 100%);
  border: none;
  box-shadow: 0 6px 12px rgba(59,130,246,0.5);
  transition: all 0.3s ease;
  font-size: 1rem;
}
.btn-primary:hover {
  background: linear-gradient(90deg, #1e40af 0%, #2563eb 100%);
  box-shadow: 0 8px 18px rgba(37,99,235,0.7);
  transform: translateY(-2px);
}
.btn-success {
  border-radius: 50px;
  padding: 0.65rem 1.8rem;
  font-weight: 700;
  background: linear-gradient(90deg, #059669 0%, #10b981 100%);
  border: none;
  box-shadow: 0 6px 12px rgba(16,185,129,0.5);
  transition: all 0.3s ease;
  font-size: 1rem;
}
.btn-success:hover {
  background: linear-gradient(90deg, #047857 0%, #059669 100%);
  box-shadow: 0 8px 18px rgba(5,150,105,0.7);
  transform: translateY(-2px);
}

/* Table Styling */
table { border-collapse: separate; border-spacing: 0 12px; }
thead tr th {
  background-color: #2563eb;
  color: #fff;
  border-radius: 12px;
  padding: 16px 20px;
  position: sticky;
  top: 0;
  z-index: 10;
  font-weight: 600;
  font-size: 1rem;
}
tbody tr {
  background: #f9fafb;
  border-radius: 12px;
  box-shadow: 0 8px 16px rgba(37,99,235,0.1);
  transition: background-color 0.25s ease;
}
tbody tr:hover { background-color: #dbeafe; }
tbody tr td {
  padding: 14px 18px;
  vertical-align: middle;
  border: none !important;
  color: #334155;
}

/* Stars */
.stars {
  font-size: 1.2rem;
  color: #facc15;
  letter-spacing: 0.02em;
}

/* Search Input */
#searchTableInput {
  max-width: 400px;
  border-radius: 50px;
  border: 1.5px solid #c7d2fe;
  padding-left: 1.8rem;
  padding-right: 3rem;
}
#searchTableInput:focus {
  border-color: #2563eb;
  box-shadow: 0 0 12px rgba(37,99,235,0.45);
}
.input-clear-btn {
  position: relative;
  left: -2.6rem;
  top: 3px;
  color: #94a3b8;
  cursor: pointer;
  font-size: 1.15rem;
}
.input-clear-btn:hover { color: #2563eb; }

/* Footer */
footer.footer {
  margin-top: 4rem;
  padding: 18px 0;
  background: #f3f4f6;
  color: #64748b;
  font-size: 0.9rem;
  border-radius: 0 0 1rem 1rem;
  text-align: center;
  box-shadow: inset 0 1px 2px rgba(255,255,255,0.7);
}

/* ==== Responsive ==== */
@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%); /* hidden by default */
  }
  .sidebar.active {
    transform: translateX(0); /* slide in */
  }
  main.container {
    margin-left: 0;
    padding: 20px;
  }
  .sidebar-toggle { display: block; }
  #searchTableInput { max-width: 100%; }
  thead tr th { font-size: 0.85rem; padding: 12px 10px; }
  tbody tr td { font-size: 0.85rem; padding: 12px 8px; }
}

  </style>
</head>
<body>
<!-- Sidebar -->
<?php include 'sidebar.php'; ?>

  <main class="container">

    <header class="text-center mb-5 animate__animated animate__fadeInDown">
      <h2><i class="fa-solid fa-star-half-stroke"></i> Worker Ratings Dashboard</h2>
      <p class="text-muted fs-5">Rate workers, analyze performance, and generate detailed reports with ease.</p>
    </header>

    <!-- Add Rating Form -->
    <section class="card p-4 mb-5 animate__animated animate__fadeInUp" style="will-change: transform, opacity;">
      <h5><i class="fa-solid fa-plus-circle"></i> Add New Rating</h5>
      <form action="add_rating.php" method="post" class="row g-3 mt-2" novalidate>
        <div class="col-md-4">
          <label for="worker_id" class="form-label fw-semibold">Select Worker</label>
          <select id="worker_id" name="worker_id" class="form-select" required>
            <option value="">-- Choose Worker --</option>
            <?php while($w = $workers->fetch_assoc()): ?>
              <option value="<?= htmlspecialchars($w['id']) ?>"><?= htmlspecialchars($w['fullName']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label for="rated_by" class="form-label fw-semibold">Rated By</label>
          <input type="text" id="rated_by" name="rated_by" placeholder="Your name" class="form-control" required autocomplete="off">
        </div>
        <div class="col-md-4">
          <label for="rating" class="form-label fw-semibold">Rating (1-5)</label>
          <input type="number" id="rating" name="rating" min="1" max="5" placeholder="⭐ Rating" class="form-control" required autocomplete="off">
        </div>
        <div class="col-md-6">
          <label for="job_title" class="form-label fw-semibold">Job Title</label>
          <input type="text" id="job_title" name="job_title" placeholder="Job title" class="form-control" autocomplete="off">
        </div>
        <div class="col-md-6">
          <label for="company_name" class="form-label fw-semibold">Company Name</label>
          <input type="text" id="company_name" name="company_name" placeholder="Company name" class="form-control" autocomplete="off">
        </div>
        <div class="col-md-6">
          <label for="duration" class="form-label fw-semibold">Duration</label>
          <input type="text" id="duration" name="duration" placeholder="e.g. 3 months" class="form-control" autocomplete="off">
        </div>
        <div class="col-md-6">
          <label for="feedback" class="form-label fw-semibold">Feedback</label>
          <textarea id="feedback" name="feedback" placeholder="Write your feedback here..." rows="3" class="form-control" autocomplete="off"></textarea>
        </div>
        <div class="col-12 text-end mt-3">
          <button type="submit" class="btn btn-success btn-lg shadow-sm">
            <i class="fa-solid fa-plus"></i> Add Rating
          </button>
        </div>
      </form>
    </section>

    <!-- Bar Chart -->
    <section class="card p-4 mb-5 animate__animated animate__fadeIn" style="min-height: 380px; will-change: transform, opacity;">
      <h5><i class="fa-solid fa-chart-column"></i> Average Rating & Number of Ratings per Worker</h5>
      <canvas id="barChart" height="240" style="margin-top: 1rem;"></canvas>
    </section>

    <!-- Ratings Table -->
    <section class="card p-4 animate__animated animate__fadeIn" style="will-change: transform, opacity;">
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5><i class="fa-solid fa-list-check"></i> All Worker Ratings</h5>
        <div class="position-relative">
          <input type="search" id="searchTableInput" class="form-control" placeholder="🔍 Search workers, feedback, companies..." aria-label="Search workers" autocomplete="off" />
          <span class="input-clear-btn" title="Clear search" id="clearSearch" style="display:none; cursor:pointer;"><i class="fa-solid fa-xmark"></i></span>
        </div>
      </div>
      <div class="table-responsive rounded-4 shadow-sm">
        <table class="table align-middle" id="ratingsTable" style="border-collapse: separate; border-spacing: 0 12px;">
          <thead>
            <tr>
              <th>Worker</th>
              <th>Rated By</th>
              <th>Rating</th>
              <th>Feedback</th>
              <th>Job</th>
              <th>Company</th>
              <th>Duration</th>
              <th style="min-width:130px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php $result->data_seek(0); while($r = $result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($r['fullName']) ?></td>
              <td><?= htmlspecialchars($r['rated_by']) ?></td>
              <td>
                <span class="stars" aria-label="Rating: <?= intval($r['rating']) ?> out of 5 stars">
                  <?= str_repeat('★', intval($r['rating'])) ?>
                  <?= str_repeat('☆', 5 - intval($r['rating'])) ?>
                </span> (<?= htmlspecialchars($r['rating']) ?>)
              </td>
              <td><?= nl2br(htmlspecialchars($r['feedback'])) ?></td>
              <td><?= htmlspecialchars($r['job_title']) ?></td>
              <td><?= htmlspecialchars($r['company_name']) ?></td>
              <td><?= htmlspecialchars($r['duration']) ?></td>
              <td>
                <a href="edit_rating.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-warning me-2" data-bs-toggle="tooltip" title="Edit this rating">
                  <i class="fa-solid fa-pen-to-square"></i>
                </a>
                <button class="btn btn-sm btn-outline-danger btn-delete-rating" data-id="<?= $r['id'] ?>" data-worker="<?= htmlspecialchars($r['fullName']) ?>" data-bs-toggle="tooltip" title="Delete this rating">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Report Generation Form -->
    <section class="card p-4 mt-5 animate__animated animate__fadeInUp" style="will-change: transform, opacity;">
      <h5><i class="fa-solid fa-file-lines"></i> Generate Report</h5>
      <label for="searchWorker" class="form-label mt-3 fw-semibold">Search Worker:</label>
      <input type="search" id="searchWorker" class="form-control mb-3" placeholder="Type to filter workers..." autocomplete="off" />
      <form action="generate_report.php" method="post" target="_blank">
        <label for="worker_ids" class="form-label fw-semibold">Select Workers (multiple allowed):</label>
        <select name="worker_ids[]" id="worker_ids" class="form-select select-multiple" multiple required>
          <?php
          $workers2 = $conn->query("SELECT id, fullName FROM workers ORDER BY fullName");
          while ($w = $workers2->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($w['id']) ?>"><?= htmlspecialchars($w['fullName']) ?></option>
          <?php endwhile; ?>
        </select>
        <button type="submit" class="btn btn-primary mt-3 shadow">
          <i class="fa-solid fa-file-export"></i> Generate Report
        </button>
      </form>
    </section>

  </main>



  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 shadow-lg">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="deleteConfirmLabel"><i class="fa-solid fa-triangle-exclamation"></i> Confirm Delete</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body fs-5">
          Are you sure you want to delete the rating for <strong id="modalWorkerName"></strong>?
          <p class="text-muted small mt-2">This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
          <a href="#" class="btn btn-danger rounded-pill px-4" id="confirmDeleteBtn">Delete</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap Bundle JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(t => new bootstrap.Tooltip(t));

    // Search table input with clear button
    const searchInput = document.getElementById('searchTableInput');
    const clearBtn = document.getElementById('clearSearch');

    function filterTable() {
      const filter = searchInput.value.toLowerCase();
      const rows = document.querySelectorAll("#ratingsTable tbody tr");
      let anyVisible = false;
      rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        if (text.includes(filter)) {
          row.style.display = "";
          anyVisible = true;
        } else {
          row.style.display = "none";
        }
      });
      clearBtn.style.display = filter.length ? 'inline' : 'none';
    }

    searchInput.addEventListener('input', filterTable);
    clearBtn.addEventListener('click', () => {
      searchInput.value = '';
      filterTable();
      searchInput.focus();
    });

    // Search Worker dropdown filter in Report section
    document.getElementById("searchWorker").addEventListener("input", function () {
      const filter = this.value.toLowerCase();
      const options = document.getElementById("worker_ids").options;
      Array.from(options).forEach(opt => {
        opt.style.display = opt.text.toLowerCase().includes(filter) ? "block" : "none";
      });
    });

    // Delete rating confirmation modal logic
    const deleteButtons = document.querySelectorAll('.btn-delete-rating');
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    const modalWorkerName = document.getElementById('modalWorkerName');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

    deleteButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const ratingId = btn.getAttribute('data-id');
        const workerName = btn.getAttribute('data-worker');
        modalWorkerName.textContent = workerName;
        confirmDeleteBtn.href = `delete_rating.php?id=${ratingId}`;
        deleteModal.show();
      });
    });

    // Chart.js dual axis bar chart
    const ctx = document.getElementById('barChart').getContext('2d');

    // Create vertical gradient
    const gradient = ctx.createLinearGradient(0, 0, 0, 250);
    gradient.addColorStop(0, 'rgba(37, 99, 235, 0.85)');
    gradient.addColorStop(1, 'rgba(147, 197, 253, 0.4)');

    const data = {
      labels: <?= json_encode($labels) ?>,
      datasets: [
        {
          type: 'bar',
          label: '⭐ Average Rating',
          data: <?= json_encode($avgRatings) ?>,
          backgroundColor: gradient,
          borderRadius: 14,
          borderSkipped: false,
          yAxisID: 'y',
          hoverBackgroundColor: 'rgba(37, 99, 235, 0.95)',
          maxBarThickness: 50,
          borderColor: 'rgba(37, 99, 235, 0.75)',
          borderWidth: 1.5,
        },
        {
          type: 'line',
          label: '📝 Number of Ratings',
          data: <?= json_encode($countRatings) ?>,
          borderColor: '#16a34a',
          backgroundColor: 'rgba(22, 163, 74, 0.25)',
          yAxisID: 'y1',
          tension: 0.28,
          fill: true,
          pointRadius: 7,
          pointHoverRadius: 9,
          borderWidth: 3,
          pointBackgroundColor: '#16a34a',
          pointHoverBackgroundColor: '#15803d',
        }
      ]
    };

    const options = {
      responsive: true,
      interaction: {
        mode: 'index',
        intersect: false,
      },
      plugins: {
        tooltip: {
          enabled: true,
          backgroundColor: '#1e293b',
          titleFont: { weight: '700', size: 15 },
          padding: 10,
          cornerRadius: 8,
          callbacks: {
            label: function(context) {
              if (context.dataset.type === 'bar') {
                return `${context.dataset.label}: ${context.parsed.y} ⭐`;
              } else {
                return `${context.dataset.label}: ${context.parsed.y} reviews`;
              }
            }
          }
        },
        legend: {
          position: 'top',
          labels: {
            boxWidth: 20,
            padding: 18,
            font: { size: 14, weight: '700' },
            usePointStyle: true,
            pointStyle: 'rectRounded',
          }
        },
      },
      scales: {
        y: {
          type: 'linear',
          position: 'left',
          min: 0,
          max: 5,
          ticks: {
            stepSize: 1,
            color: '#2563eb',
            font: { weight: '700', size: 13 },
          },
          grid: { color: '#e0e7ff', borderDash: [6, 6] },
          title: {
            display: true,
            text: 'Average Rating',
            color: '#2563eb',
            font: { weight: '700', size: 14 }
          }
        },
        y1: {
          type: 'linear',
          position: 'right',
          min: 0,
          ticks: {
            color: '#16a34a',
            font: { weight: '700', size: 13 },
          },
          grid: {
            drawOnChartArea: false
          },
          title: {
            display: true,
            text: 'Number of Ratings',
            color: '#16a34a',
            font: { weight: '700', size: 14 }
          }
        },
        x: {
          ticks: {
            maxRotation: 45,
            minRotation: 35,
            font: { weight: '700', size: 13 },
            color: '#334155',
          },
          grid: { display: false }
        }
      },
      animation: {
        duration: 1400,
        easing: 'easeOutQuart'
      }
    };

    new Chart(ctx, {
      data: data,
      options: options,
    });
  </script>
</body>
</html> 