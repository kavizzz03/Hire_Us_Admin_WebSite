<?php
header('Content-Type: application/json');

// DB Credentials
$servername = "localhost";
$username = "u569550465_math_rakusa";
$password = "Sithija2025#";
$dbname = "u569550465_hireme";

// Connect DB
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Validate job_id
$job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
if ($job_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid job ID']);
    exit;
}

// Get number of vacancies
$vacancyQuery = $conn->query("SELECT vacancies FROM jobs WHERE id = $job_id");
$vacancies = 1;
if ($vacancyQuery && $vacancyQuery->num_rows > 0) {
    $vacancies = $vacancyQuery->fetch_assoc()['vacancies'];
}

// Get job applicants by job_id
$applicants = [];
$sql = "SELECT * FROM job_applications WHERE job_id = $job_id";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $idNumber = $conn->real_escape_string($row['worker_id_number']);

        // Get worker from workers table using idNumber
        $workerQuery = $conn->query("SELECT * FROM workers WHERE idNumber = '$idNumber' LIMIT 1");
        if ($workerQuery && $workerQuery->num_rows > 0) {
            $worker = $workerQuery->fetch_assoc();
            $workerId = intval($worker['id']);

            // Get ratings for this worker_id
            $ratingsResult = $conn->query("SELECT rating, feedback FROM worker_ratings WHERE worker_id = $workerId");
            $ratings = [];
            $totalRating = 0;
            $count = 0;

            if ($ratingsResult && $ratingsResult->num_rows > 0) {
                while ($rate = $ratingsResult->fetch_assoc()) {
                    $ratings[] = $rate['feedback'];
                    $totalRating += intval($rate['rating']);
                    $count++;
                }
            }

            $averageRating = $count > 0 ? round($totalRating / $count, 1) : "No Ratings";

            $applicants[] = [
                'idNumber' => $worker['idNumber'],
                'fullName' => $worker['fullName'],
                'email' => $worker['email'],
                'status' => $worker['status'], 
                'contactNumber' => $worker['contactNumber'],
                'workExperience' => $worker['workExperience'],
                'rating' => $averageRating,
                 'feedback' => $ratings
            ];
    
        }
    }

    echo json_encode([
        'success' => true,
        'vacancies' => $vacancies,
        'applicants' => $applicants
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'No applicants found']);
}

$conn->close();
?>
