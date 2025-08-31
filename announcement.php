<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$servername = "localhost";
$username   = "u569550465_math_rakusa";
$password   = "Sithija2025#";
$dbname     = "u569550465_hireme";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$message_sent = "";
$email_results = [];

$emails = [];
$workerQuery   = $conn->query("SELECT email FROM workers");
$employerQuery = $conn->query("SELECT email FROM employers");
while ($row = $workerQuery->fetch_assoc())   $emails[] = $row['email'];
while ($row = $employerQuery->fetch_assoc()) $emails[] = $row['email'];
$emails = array_unique($emails);

$smtpHost = 'smtp.gmail.com';
$smtpUser = 'yakacrew2025@gmail.com';
$smtpPass = 'wfgj qrwj kuss jgvz';
$smtpPort = 587;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['subject'], $_POST['message'])) {
    $subject = trim($_POST['subject']);
    $announcement_message = trim($_POST['message']);

    foreach ($emails as $email) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpUser;
            $mail->Password   = $smtpPass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $smtpPort;

            $mail->setFrom($smtpUser, 'Hire Us Admin');
            $mail->addAddress($email);
            $mail->addReplyTo($smtpUser, 'Hire Us Admin');

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial,sans-serif; background:#f4f7f9; margin:0; padding:0; }
                    .container { max-width:600px; margin:30px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 10px 20px rgba(0,0,0,0.08); }
                    .header { background:#667eea; color:#fff; padding:25px; text-align:center; }
                    .header h1 { margin:0; font-size:26px; }
                    .content { padding:30px; color:#333; line-height:1.6; }
                    .content h2 { color:#667eea; font-size:22px; margin-top:0; }
                    .cta { text-align:center; margin:30px 0; }
                    .cta a { background:#667eea; color:#fff; padding:15px 30px; border-radius:8px; text-decoration:none; font-weight:bold; font-size:16px; transition:all 0.3s; }
                    .cta a:hover { background:#5648c6; }
                    .footer { background:#f1f1f1; padding:20px; text-align:center; font-size:12px; color:#777; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Hire Us System</h1>
                    </div>
                    <div class='content'>
                        <h2>Hello!</h2>
                        <p>{$announcement_message}</p>
                        <div class='cta'>
                            <a href='https://hireme.cpsharetxt.com/'>Visit Our Website</a>
                        </div>
                        <p style='font-size:12px;color:#555;'>This is an automated message. Please do not reply.</p>
                    </div>
                    <div class='footer'>
                        &copy; ".date('Y')." Hire Us System. All rights reserved.
                    </div>
                </div>
            </body>
            </html>
            ";

            $mail->send();
            $email_results[] = "$email : Sent";
        } catch (Exception $e) {
            $email_results[] = "$email : Failed ({$mail->ErrorInfo})";
        }
    }

    $message_sent = "Announcement processed for all emails.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Announcement - Hire Us</title>
      <link rel="icon" type="image/png" href="icon2.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 2rem;
            animation: fadeInPage 1s ease forwards;
            opacity: 0;
        }
        @keyframes fadeInPage { to { opacity: 1; } }
        .card {
            background: rgba(255,255,255,0.95);
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            padding: 2rem;
            width: 100%;
            max-width: 700px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        .btn-gradient {
            background: linear-gradient(90deg, #667eea, #764ba2);
            color: #fff;
            border-radius: 50px;
            padding: 0.8rem 2rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-gradient:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(102,126,234,0.5);
        }
        .btn-back {
            display: inline-block;
            margin-bottom: 1.5rem;
            background: #fff;
            color: #764ba2;
            font-weight: 600;
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-back:hover {
            background: #764ba2;
            color: #fff;
            box-shadow: 0 5px 15px rgba(118,75,162,0.4);
        }
    </style>
</head>
<body>

<div class="card">
    <a href="Web_Site/Messages/admin_chat.php" class="btn-back">⬅ Back</a>
    <h1 class="text-3xl font-extrabold text-purple-600 mb-6">📢 Send Announcement</h1>

    <form method="POST">
        <label class="block text-gray-700 font-semibold mb-2">Subject</label>
        <input type="text" name="subject" placeholder="Enter announcement subject" required
               class="w-full p-3 mb-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400">

        <label class="block text-gray-700 font-semibold mb-2">Message</label>
        <textarea name="message" rows="6" placeholder="Enter announcement message" required
                  class="w-full p-3 mb-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400"></textarea>

        <button type="submit" class="btn-gradient w-full">Send Announcement</button>
    </form>

    <?php if ($message_sent): ?>
        <div class="bg-white shadow-lg rounded-lg p-6 mt-6 border-l-4 border-purple-500 animate-fadeIn">
            <h3 class="text-lg font-semibold mb-2 text-purple-600"><?php echo $message_sent; ?></h3>
            <ul class="list-disc list-inside text-gray-700">
                <?php foreach ($email_results as $result) echo "<li>$result</li>"; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
