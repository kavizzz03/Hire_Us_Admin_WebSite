<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = strip_tags(trim($_POST["name"]));
  $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
  $message = strip_tags(trim($_POST["message"]));

  // Validate inputs
  if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($message)) {
    die("Invalid form data. Please go back and try again.");
  }

  // Admin email (change to your actual admin email)
  $adminEmail = "kavizzn@gmail.com"; // 🔁 change this to your email

  // --------- Send Email to Admin ---------
  $subjectAdmin = "New Contact Form Message - Hire Me";
  $messageAdmin = "You have received a new message from Hire Me contact form.\n\n".
                  "Name: $name\nEmail: $email\n\nMessage:\n$message";
  $headersAdmin = "From: Hire Me <noreply@yourdomain.com>\r\n";
  $headersAdmin .= "Reply-To: $email\r\n";

  $adminMail = mail($adminEmail, $subjectAdmin, $messageAdmin, $headersAdmin);

  // --------- Send Acknowledgment to User ---------
  $subjectUser = "Thanks for contacting Hire Me";
  $messageUser = "Hi $name,\n\nThank you for reaching out to Hire Me. We have received your message and will respond shortly.\n\nBest regards,\nHire Me Team";
  $headersUser = "From: Hire Me <noreply@yourdomain.com>\r\n";
  $headersUser .= "Reply-To: $adminEmail\r\n";

  $userMail = mail($email, $subjectUser, $messageUser, $headersUser);

  // --------- Redirect to Thank You Page if both mails sent ---------
  if ($adminMail && $userMail) {
    header("Location: thankyou.html");
    exit();
  } else {
    echo "Failed to send email. Please check your server mail settings.";
  }
}
?>
