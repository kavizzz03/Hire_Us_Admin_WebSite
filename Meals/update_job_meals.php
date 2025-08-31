<?php
require 'db.php';

$job_id = intval($_POST['job_id']);

// Clear old meals
$conn->query("DELETE FROM job_meals WHERE job_id = $job_id");

// Insert new selected meals
if (isset($_POST['meals'])) {
    foreach ($_POST['meals'] as $meal_id) {
        $meal_id = intval($meal_id);
        $conn->query("INSERT INTO job_meals (job_id, meal_id) VALUES ($job_id, $meal_id)");
    }
    echo "Meals updated successfully!";
} else {
    echo "No meals selected. Cleared all meals.";
}
