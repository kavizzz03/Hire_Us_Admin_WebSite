<?php
$conn = new mysqli("localhost", "u569550465_math_rakusa", "Sithija2025#", "u569550465_hireme");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $rated_by = $conn->real_escape_string($_POST['rated_by']);
    $rating = intval($_POST['rating']);
    $feedback = $conn->real_escape_string($_POST['feedback']);

    $query = "UPDATE worker_ratings SET rated_by='$rated_by', rating='$rating', feedback='$feedback' WHERE id=$id";

    if ($conn->query($query)) {
        header("Location: edit_rating.php?id=$id&status=success&msg=Rating updated successfully!");
    } else {
        header("Location: edit_rating.php?id=$id&status=error&msg=Failed to update rating.");
    }
}
?>
