<?php
header("Content-Type: application/json");
$conn = new mysqli("localhost","u569550465_math_rakusa","Sithija2025#","u569550465_hireme");
if($conn->connect_error){ echo json_encode(['status'=>'error','message'=>$conn->connect_error]); exit; }

$job_id=intval($_GET['job_id']);

// Count wants_meals
$c=$conn->query("SELECT COUNT(*) AS yes_count, COUNT(*) AS total_hires FROM job_hires WHERE job_id=$job_id")->fetch_assoc();
$yesCount=$c['yes_count']; $totalHires=$c['total_hires'];

// Get workers details
$res=$conn->query("SELECT jh.id_number, jh.wants_meals, w.name, w.contact_number 
                   FROM job_hires jh 
                   JOIN workers w ON jh.id_number=w.id_number
                   WHERE jh.job_id=$job_id");

$hires=[];
while($r=$res->fetch_assoc()) $hires[]=$r;

if(count($hires)==0) echo json_encode(['status'=>'error','message'=>'No hires']);
else echo json_encode(['status'=>'success','total_hires'=>$totalHires,'wants_meals_count'=>$yesCount,'hires'=>$hires]);
