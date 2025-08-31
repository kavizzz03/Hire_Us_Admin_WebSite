<?php
$DB_HOST = "localhost";
$DB_USER = "u569550465_math_rakusa";
$DB_PASS = "Sithija2025#";
$DB_NAME = "u569550465_hireme";
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

$action = $_POST['action'] ?? '';

if($action === "add"){
    $job_id = intval($_POST['job_id']);
    $conn->query("INSERT INTO meals (job_id, meal_name, meal_time) VALUES ($job_id, 'New Meal', '00:00')");
    echo "Meal Added";
}

if($action === "edit"){
    $id = intval($_POST['id']);
    $field = $conn->real_escape_string($_POST['field']);
    $value = $conn->real_escape_string($_POST['value']);
    $conn->query("UPDATE meals SET $field = '$value' WHERE id = $id");
    echo "Updated";
}

if($action === "delete"){
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM meals WHERE id = $id");
    echo "Deleted";
}
