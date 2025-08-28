<?php
// Database connection
$DB_HOST = "localhost";
$DB_USER = "u569550465_math_rakusa";
$DB_PASS = "Sithija2025#";
$DB_NAME = "u569550465_hireme";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Load jobs
$jobs = $conn->query("SELECT id, job_title FROM jobs ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Job Meals</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="p-4">
    <h2 class="mb-4">Manage Job Meals</h2>

    <div class="mb-3">
        <label class="form-label">Select Job</label>
        <select id="jobSelect" class="form-select">
            <option value="">-- Select Job --</option>
            <?php while($row = $jobs->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"><?= $row['job_title'] ?> (Job ID: <?= $row['id'] ?>)</option>
            <?php endwhile; ?>
        </select>
    </div>

    <div id="mealsSection" style="display:none;">
        <h4>Meals for this Job</h4>
        <table class="table table-bordered" id="mealsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Meal Name</th>
                    <th>Meal Type</th>
                    <th>Yes Count</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="mt-3">
        <button id="participantsBtn" class="btn btn-primary" style="display:none;">View Participants</button>
    </div>

    <div id="participantsSection" style="display:none;" class="mt-4">
        <h4>Participants</h4>
        <table class="table table-striped" id="participantsTable">
            <thead>
                <tr>
                    <th>ID Number</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

<script>
$(document).ready(function(){
    $("#jobSelect").change(function(){
        let jobId = $(this).val();
        if(jobId){
            $.post("get_meals.php", {job_id: jobId}, function(data){
                $("#mealsTable tbody").html(data);
                $("#mealsSection").show();
                $("#participantsBtn").show();
                $("#participantsSection").hide();
            });
        }
    });

    $("#participantsBtn").click(function(){
        let jobId = $("#jobSelect").val();
        if(jobId){
            $.post("get_participants.php", {job_id: jobId}, function(data){
                $("#participantsTable tbody").html(data);
                $("#participantsSection").show();
            });
        }
    });
});
</script>
</body>
</html>
