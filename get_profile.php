<?php
include 'db_connection.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_number'])) {
    $id_number = $_POST['id_number'];
    $query = "SELECT * FROM workers WHERE id_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $id_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $response['status'] = 'success';
        $response['user'] = $user;
    } else {
        $response['status'] = 'not_found';
    }

    $stmt->close();
    $conn->close();
} else {
    $response['status'] = 'invalid';
}

echo json_encode($response);
?>
