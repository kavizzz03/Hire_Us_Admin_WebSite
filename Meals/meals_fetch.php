<?php
$DB_HOST = "localhost";
$DB_USER = "u569550465_math_rakusa";
$DB_PASS = "Sithija2025#";
$DB_NAME = "u569550465_hireme";
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if($conn->connect_error){ die("DB error"); }

$job_id = intval($_POST['job_id'] ?? 0);
$q = $conn->query("SELECT id, meal_name, description, meal_price FROM meals WHERE job_id=$job_id ORDER BY id DESC");

if($q && $q->num_rows){
  while($m = $q->fetch_assoc()){
    ?>
    <tr data-id="<?= $m['id'] ?>">
      <td><?= $m['id'] ?></td>
      <td contenteditable="true" class="meal_name"><?= htmlspecialchars($m['meal_name']) ?></td>
      <td contenteditable="true" class="description"><?= htmlspecialchars($m['description']) ?></td>
      <td contenteditable="true" class="meal_price"><?= htmlspecialchars($m['meal_price']) ?></td>
      <td>
        <button class="btn btn-sm btn-danger deleteMeal" data-id="<?= $m['id'] ?>">Delete</button>
      </td>
    </tr>
    <?php
  }
} else {
  echo '<tr><td colspan="5" class="text-center">No meals found for this job.</td></tr>';
}
