<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_number = $_POST['id_number'];
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];
    $permanent_address = $_POST['permanent_address'];
    $current_address = $_POST['current_address'];
    $work_experience = $_POST['work_experience'];
    $bank_account_number = $_POST['bank_account_number'];
    $bank_name = $_POST['bank_name'];
    $bank_branch = $_POST['bank_branch'];

    $query = "UPDATE workers SET full_name=?, username=?, email=?, contact_number=?, permanent_address=?, current_address=?, work_experience=?, bank_account_number=?, bank_name=?, bank_branch=? WHERE id_number=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssssss", $full_name, $username, $email, $contact_number, $permanent_address, $current_address, $work_experience, $bank_account_number, $bank_name, $bank_branch, $id_number);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "invalid";
}
?>