<?php
$conn = new mysqli("localhost","u569550465_math_rakusa","Sithija2025#","u569550465_hireme");
if($conn->connect_error) die("Connection failed: ".$conn->connect_error);

$action=$_REQUEST['action'] ?? '';

if($action=='add'){
    $job_id=intval($_POST['job_id']);
    $name=$_POST['meal_name']; $desc=$_POST['description']; $price=$_POST['meal_price'];
    $stmt=$conn->prepare("INSERT INTO meals (job_id, meal_name, description, meal_price) VALUES (?,?,?,?)");
    $stmt->bind_param("issd",$job_id,$name,$desc,$price); $stmt->execute();
    echo "Meal added!";
}
if($action=='update'){
    $id=intval($_POST['id']);
    $name=$_POST['meal_name']; $desc=$_POST['description']; $price=$_POST['meal_price'];
    $stmt=$conn->prepare("UPDATE meals SET meal_name=?, description=?, meal_price=? WHERE id=?");
    $stmt->bind_param("ssdi",$name,$desc,$price,$id); $stmt->execute();
    echo "Meal updated!";
}
if($action=='delete'){
    $id=intval($_GET['id']);
    $conn->query("DELETE FROM meals WHERE id=$id");
    echo "Meal deleted!";
}
