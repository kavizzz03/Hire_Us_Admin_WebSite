<?php
header("Content-Type: application/json");
$conn = new mysqli("localhost","u569550465_math_rakusa","Sithija2025#","u569550465_hireme");
if($conn->connect_error){ echo json_encode(['status'=>'error','message'=>$conn->connect_error]); exit; }

$job_id=intval($_GET['job_id']);
$res=$conn->query("SELECT * FROM meals WHERE job_id=$job_id ORDER BY created_at DESC");
$meals=[];
while($r=$res->fetch_assoc()) $meals[]=$r;

if(count($meals)==0) echo json_encode(['status'=>'error','message'=>'No meals found']);
else echo json_encode(['status'=>'success','meals'=>$meals]);
