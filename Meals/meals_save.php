<?php
$DB_HOST = "localhost";
$DB_USER = "u569550465_math_rakusa";
$DB_PASS = "Sithija2025#";
$DB_NAME = "u569550465_hireme";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if($conn->connect_error){ die("DB error"); }

$action = $_POST['action'] ?? '';

if($action === 'add'){
  $job_id = intval($_POST['job_id'] ?? 0);
  $meal_name = $conn->real_escape_string($_POST['meal_name'] ?? '');
  $description = $conn->real_escape_string($_POST['description'] ?? '');
  $meal_price = floatval($_POST['meal_price'] ?? 0);

  if($job_id && $meal_name !== ''){
    $stmt = $conn->prepare("INSERT INTO meals (job_id, meal_name, description, meal_price) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issd", $job_id, $meal_name, $description, $meal_price);
    $stmt->execute();
    echo "Meal added.";
  } else {
    echo "Missing job_id or meal_name.";
  }
  exit;
}

if($action === 'update'){
  $id = intval($_POST['id'] ?? 0);
  $field = $_POST['field'] ?? '';
  $value = $_POST['value'] ?? '';

  // Allow only known fields
  $allowed = ['meal_name','description','meal_price'];
  if(!$id || !in_array($field, $allowed, true)){
    echo "Invalid update."; exit;
  }

  if($field === 'meal_price'){
    $value = floatval($value);
    $stmt = $conn->prepare("UPDATE meals SET meal_price=? WHERE id=?");
    $stmt->bind_param("di", $value, $id);
  } else {
    $value = $conn->real_escape_string($value);
    $sql = "UPDATE meals SET $field='$value' WHERE id=$id";
    $stmt = $conn->query($sql);
  }
  echo "Updated.";
  exit;
}

if($action === 'delete'){
  $id = intval($_POST['id'] ?? 0);
  if($id){
    $conn->query("DELETE FROM meals WHERE id=$id");
    echo "Deleted.";
  } else {
    echo "Invalid id.";
  }
  exit;
}

echo "No action.";
