<?php
$DB_HOST = "localhost";
$DB_USER = "u569550465_math_rakusa";
$DB_PASS = "Sithija2025#";
$DB_NAME = "u569550465_hireme";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$job_id = intval($_POST['job_id'] ?? 0);

$sql = "SELECT w.id_number, w.full_name, w.contact_number, w.email
        FROM job_hires jh 
        JOIN workers w ON jh.id_number = w.id_number
        WHERE jh.job_id = $job_id";

$result = $conn->query($sql);
if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        echo "<tr>
                <td>{$row['id_number']}</td>
                <td>{$row['full_name']}</td>
                <td>{$row['contact_number']}</td>
                <td>{$row['email']}</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='4'>No participants found</td></tr>";
}
