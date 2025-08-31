<?php
// process_worker.php
$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Error: " . $conn->connect_error);

function uploadImage($fileInputName, $oldFile = null){
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] == UPLOAD_ERR_NO_FILE) {
        return $oldFile;
    }

    $targetDir = "../../uploads_employee/";

    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

    $fileName = time() . "_" . basename($_FILES[$fileInputName]["name"]);
    $targetFilePath = $targetDir . $fileName;

    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg','jpeg','png','gif'];

    if (!in_array($fileType, $allowedTypes)) {
        die("Error: Only JPG, JPEG, PNG & GIF files allowed for $fileInputName.");
    }
    if (!move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $targetFilePath)) {
        die("Error uploading file $fileInputName.");
    }
    if ($oldFile && file_exists($oldFile) && $oldFile !== $targetFilePath) {
        unlink($oldFile);
    }
    return $targetFilePath;
}

$action = $_POST['action'] ?? '';
$id = intval($_POST['id'] ?? 0);

$fullName = $conn->real_escape_string($_POST['fullName'] ?? '');
$username = $conn->real_escape_string($_POST['username'] ?? '');
$contactNumber = $conn->real_escape_string($_POST['contactNumber'] ?? '');
$email = $conn->real_escape_string($_POST['email'] ?? '');
$idNumber = $conn->real_escape_string($_POST['idNumber'] ?? '');
$permanentAddress = $conn->real_escape_string($_POST['permanentAddress'] ?? '');
$currentAddress = $conn->real_escape_string($_POST['currentAddress'] ?? '');
$workExperience = $conn->real_escape_string($_POST['workExperience'] ?? '');
$jobTitle = $conn->real_escape_string($_POST['jobTitle'] ?? '');
$bankAccountNumber = $conn->real_escape_string($_POST['bankAccountNumber'] ?? '');
$bankName = $conn->real_escape_string($_POST['bankName'] ?? '');
$bankBranch = $conn->real_escape_string($_POST['bankBranch'] ?? '');
$status = $conn->real_escape_string($_POST['status'] ?? 'not_hired');

if ($action === 'add') {
    // Check duplicate username or idNumber
    $exists = $conn->query("SELECT id FROM workers WHERE username='$username' OR idNumber='$idNumber'")->fetch_assoc();
    if ($exists) die("Error: Username or ID number already exists.");

    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $idFrontImage = uploadImage('idFrontImage');
    $idBackImage = uploadImage('idBackImage');

    $stmt = $conn->prepare("INSERT INTO workers 
      (fullName, username, contactNumber, email, idNumber, permanentAddress, currentAddress, workExperience, jobTitle, password, bankAccountNumber, bankName, bankBranch, idFrontImage, idBackImage, status)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssssssssssssssss", $fullName, $username, $contactNumber, $email, $idNumber, $permanentAddress, $currentAddress, $workExperience, $jobTitle, $password, $bankAccountNumber, $bankName, $bankBranch, $idFrontImage, $idBackImage, $status);

    if($stmt->execute()){
        echo "Success";
    } else {
        echo "Error: " . $stmt->error;
    }
} elseif ($action === 'edit' && $id) {
    $worker = $conn->query("SELECT * FROM workers WHERE id=$id")->fetch_assoc();
    if (!$worker) die("Error: Worker not found");

    // Handle images
    $idFrontImage = uploadImage('idFrontImage', $worker['idFrontImage']);
    $idBackImage = uploadImage('idBackImage', $worker['idBackImage']);

    // Update password only if provided
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE workers SET fullName=?, username=?, contactNumber=?, email=?, idNumber=?, permanentAddress=?, currentAddress=?, workExperience=?, jobTitle=?, password=?, bankAccountNumber=?, bankName=?, bankBranch=?, idFrontImage=?, idBackImage=?, status=? WHERE id=?");
        $stmt->bind_param("ssssssssssssssssi", $fullName, $username, $contactNumber, $email, $idNumber, $permanentAddress, $currentAddress, $workExperience, $jobTitle, $password, $bankAccountNumber, $bankName, $bankBranch, $idFrontImage, $idBackImage, $status, $id);
    } else {
        $stmt = $conn->prepare("UPDATE workers SET fullName=?, username=?, contactNumber=?, email=?, idNumber=?, permanentAddress=?, currentAddress=?, workExperience=?, jobTitle=?, bankAccountNumber=?, bankName=?, bankBranch=?, idFrontImage=?, idBackImage=?, status=? WHERE id=?");
        $stmt->bind_param("sssssssssssssssi", $fullName, $username, $contactNumber, $email, $idNumber, $permanentAddress, $currentAddress, $workExperience, $jobTitle, $bankAccountNumber, $bankName, $bankBranch, $idFrontImage, $idBackImage, $status, $id);
    }

    if($stmt->execute()){
        echo "Success";
    } else {
        echo "Error: " . $stmt->error;
    }
} else {
    echo "Error: Invalid action";
}
