<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "u569550465_math_rakusa", "Sithija2025#", "u569550465_hireme");

$sql = "SELECT w.fullName, AVG(r.rating) AS avg_rating
        FROM worker_ratings r
        JOIN workers w ON r.worker_id = w.id
        GROUP BY r.worker_id
        ORDER BY avg_rating DESC
        LIMIT 5";

$result = $conn->query($sql);

$data = ["labels" => [], "ratings" => []];

while ($row = $result->fetch_assoc()) {
    $data["labels"][] = $row["fullName"];
    $data["ratings"][] = round($row["avg_rating"], 2);
}

echo json_encode($data);
?>
