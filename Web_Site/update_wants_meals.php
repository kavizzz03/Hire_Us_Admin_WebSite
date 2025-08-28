<?php
$conn = new mysqli("localhost","u569550465_math_rakusa","Sithija2025#","u569550465_hireme");
if($conn->connect_error) die("Connection failed: ".$conn->connect_error);

$id_number=$_GET['id_number'];
$job_id=intval($_GET['job_id']);

$res=$conn->query("SELECT wants_meals FROM job_hires WHERE id_number='$id_number' AND job_id=$job_id");
$row=$res->fetch_assoc();
$new=($row['wants_meals']=='yes')?'no':'yes';
$conn->query("UPDATE job_hires SET wants_meals='$new' WHERE id_number='$id_number' AND job_id=$job_id");
echo "Updated wants_meals to $new";
