<?php
$DB_HOST = "localhost";
$DB_USER = "u569550465_math_rakusa";
$DB_PASS = "Sithija2025#";
$DB_NAME = "u569550465_hireme";
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

$job_id = intval($_POST['job_id']);
$res = $conn->query("SELECT * FROM meals WHERE job_id = $job_id ORDER BY id ASC");
?>

<table class="table table-bordered">
  <thead>
    <tr>
      <th>ID</th>
      <th>Meal Name</th>
      <th>Meal Time</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php if($res->num_rows > 0){ 
      while($row = $res->fetch_assoc()){ ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td contenteditable="true" class="editable" data-id="<?= $row['id'] ?>" data-field="meal_name"><?= htmlspecialchars($row['meal_name']) ?></td>
          <td contenteditable="true" class="editable" data-id="<?= $row['id'] ?>" data-field="meal_time"><?= htmlspecialchars($row['meal_time']) ?></td>
          <td><button class="btn btn-sm btn-danger deleteMeal" data-id="<?= $row['id'] ?>">Delete</button></td>
        </tr>
    <?php }} else { ?>
        <tr><td colspan="4" class="text-center">No meals added yet</td></tr>
    <?php } ?>
  </tbody>
</table>

<button id="addMeal" class="btn btn-primary">+ Add Meal</button>
