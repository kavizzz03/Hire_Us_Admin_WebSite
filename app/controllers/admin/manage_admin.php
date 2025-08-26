<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: admin_login.html"); exit(); }

$servername = "localhost";
$db_username = "u569550465_math_rakusa";
$db_password = "Sithija2025#";
$db_name = "u569550465_hireme";

$conn = new mysqli($servername, $db_username, $db_password, $db_name);
if ($conn->connect_error) die("DB error");

$action = $_GET['action'] ?? '';

if ($action === 'add') {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($username === '' || $password === '') {
        header("Location: admin_management.php");
        exit();
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO admin_users (username, password, full_name, email) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $hash, $full_name, $email);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_management.php");
    exit();
}

if ($action === 'update') {
    $id = (int)$_POST['id'];
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admin_users SET username=?, full_name=?, email=?, password=? WHERE id=?");
        $stmt->bind_param("ssssi", $username, $full_name, $email, $hash, $id);
    } else {
        $stmt = $conn->prepare("UPDATE admin_users SET username=?, full_name=?, email=? WHERE id=?");
        $stmt->bind_param("sssi", $username, $full_name, $email, $id);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: admin_management.php");
    exit();
}

if ($action === 'delete') {
    $id = (int)$_POST['id'];
    $stmt = $conn->prepare("DELETE FROM admin_users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_management.php");
    exit();
}

$conn->close();
