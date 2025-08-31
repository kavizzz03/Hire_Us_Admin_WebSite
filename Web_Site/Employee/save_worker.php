<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success'=>false,'message'=>'DB connection failed']);
    exit;
}

$action = $_POST['action'] ?? 'add';
$id = intval($_POST['id'] ?? 0);
$fullName = trim($_POST['fullName'] ?? '');
$usernameInput = trim($_POST['username'] ?? '');
$contactNumber = trim($_POST['contactNumber'] ?? '');
$email = trim($_POST['email'] ?? '');
$idNumber = strtoupper(trim($_POST['idNumber'] ?? ''));
$jobTitle = trim($_POST['jobTitle'] ?? '');
$bankName = trim($_POST['bankName'] ?? '');
$bankBranch = trim($_POST['bankBranch'] ?? '');
$bankAccountNumber = trim($_POST['bankAccountNumber'] ?? '');
$permanentAddress = trim($_POST['permanentAddress'] ?? '');
$currentAddress = trim($_POST['currentAddress'] ?? '');
$workExperience = trim($_POST['workExperience'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';
$status = $_POST['status'] ?? 'not_hired';

// Validate ID number
$idPattern = "/^[0-9]{9}[VvXx]$|^[0-9]{12}$/";
if(!preg_match($idPattern, $idNumber)){
    echo json_encode(['success'=>false,'message'=>'Invalid Sri Lankan ID Number format.']);
    exit;
}

// Validate required fields
$required = [$fullName,$usernameInput,$contactNumber,$email,$idNumber,$jobTitle,$bankName,$bankBranch,$bankAccountNumber,$permanentAddress,$workExperience];
foreach($required as $f){
    if(empty($f)){
        echo json_encode(['success'=>false,'message'=>'Please fill all required fields.']);
        exit;
    }
}

// Validate email
if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    echo json_encode(['success'=>false,'message'=>'Invalid email address.']);
    exit;
}

// Check unique constraints
if($action === 'add'){
    $stmt = $conn->prepare("SELECT id FROM workers WHERE idNumber=? OR username=?");
    $stmt->bind_param("ss",$idNumber,$usernameInput);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows>0){
        echo json_encode(['success'=>false,'message'=>'ID Number or Username already exists.']);
        exit;
    }
    $stmt->close();
}

// Password handling
$passwordHash = null;
if($action==='add' || !empty($password)){
    if(strlen($password)<6 || $password!==$confirmPassword){
        echo json_encode(['success'=>false,'message'=>'Password must be at least 6 chars and match confirmation.']);
        exit;
    }
    $passwordHash = password_hash($password,PASSWORD_DEFAULT);
}

// File upload
$uploadDir = '../../uploads_employee/';
$allowedTypes = ['image/jpeg','image/png','image/gif'];

function uploadFile($fileInput,$oldFile=''){
    global $uploadDir,$allowedTypes;
    if(!isset($_FILES[$fileInput]) || $_FILES[$fileInput]['error']===UPLOAD_ERR_NO_FILE){
        return $oldFile;
    }
    $file = $_FILES[$fileInput];
    if($file['error']!==UPLOAD_ERR_OK) throw new Exception('Upload error: '.$fileInput);
    if(!in_array($file['type'],$allowedTypes)) throw new Exception('Invalid file type: '.$fileInput);
    $ext = pathinfo($file['name'],PATHINFO_EXTENSION);
    $newName = uniqid($fileInput.'_').'.'.$ext;
    $target = $uploadDir.$newName;
    if(!move_uploaded_file($file['tmp_name'],$target)) throw new Exception('Failed to move uploaded file');
    if($oldFile && file_exists($uploadDir.$oldFile) && $oldFile!==$newName) unlink($uploadDir.$oldFile);
    return $newName;
}

try{
    if($action==='add'){
        $idFrontImage = uploadFile('idFrontImage');
        $idBackImage = uploadFile('idBackImage');
    } else {
        // fetch existing images
        $stmt = $conn->prepare("SELECT idFrontImage,idBackImage FROM workers WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $res = $stmt->get_result();
        if($res->num_rows===0) throw new Exception('Worker not found');
        $old = $res->fetch_assoc();
        $idFrontImage = uploadFile('idFrontImage',$old['idFrontImage']);
        $idBackImage = uploadFile('idBackImage',$old['idBackImage']);
        $stmt->close();
    }
}catch(Exception $e){
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
    exit;
}

// Insert or update
if($action==='add'){
    $stmt = $conn->prepare("INSERT INTO workers (fullName,username,contactNumber,email,idNumber,jobTitle,bankName,bankBranch,bankAccountNumber,permanentAddress,currentAddress,workExperience,password,idFrontImage,idBackImage,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssssssssssssssss",$fullName,$usernameInput,$contactNumber,$email,$idNumber,$jobTitle,$bankName,$bankBranch,$bankAccountNumber,$permanentAddress,$currentAddress,$workExperience,$passwordHash,$idFrontImage,$idBackImage,$status);
    $stmt->execute();
    if($stmt->affected_rows>0) echo json_encode(['success'=>true,'message'=>'Worker added successfully.']);
    else echo json_encode(['success'=>false,'message'=>'Failed to add worker.']);
    $stmt->close();
} else {
    if($passwordHash){
        $stmt = $conn->prepare("UPDATE workers SET fullName=?,username=?,contactNumber=?,email=?,idNumber=?,jobTitle=?,bankName=?,bankBranch=?,bankAccountNumber=?,permanentAddress=?,currentAddress=?,workExperience=?,password=?,idFrontImage=?,idBackImage=?,status=? WHERE id=?");
        $stmt->bind_param("ssssssssssssssssi",$fullName,$usernameInput,$contactNumber,$email,$idNumber,$jobTitle,$bankName,$bankBranch,$bankAccountNumber,$permanentAddress,$currentAddress,$workExperience,$passwordHash,$idFrontImage,$idBackImage,$status,$id);
    } else {
        $stmt = $conn->prepare("UPDATE workers SET fullName=?,username=?,contactNumber=?,email=?,idNumber=?,jobTitle=?,bankName=?,bankBranch=?,bankAccountNumber=?,permanentAddress=?,currentAddress=?,workExperience=?,idFrontImage=?,idBackImage=?,status=? WHERE id=?");
        $stmt->bind_param("sssssssssssssssi",$fullName,$usernameInput,$contactNumber,$email,$idNumber,$jobTitle,$bankName,$bankBranch,$bankAccountNumber,$permanentAddress,$currentAddress,$workExperience,$idFrontImage,$idBackImage,$status,$id);
    }
    $stmt->execute();
    if($stmt->affected_rows>0) echo json_encode(['success'=>true,'message'=>'Worker updated successfully.']);
    else echo json_encode(['success'=>false,'message'=>'No changes made or failed to update.']);
    $stmt->close();
}

$conn->close();
?>
