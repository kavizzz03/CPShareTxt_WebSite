<?php
// Admin Email Configuration
$adminEmail = 'kavizzn@gmail.com'; // Replace with the admin's email address
$subject = 'CPShareTXT - List Size Notification';

// Get the POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = htmlspecialchars($_POST['message']); // Sanitize message content

    // Get current date and time
    $dateTime = date('Y-m-d H:i:s'); // Format: YYYY-MM-DD HH:MM:SS

    // Prepare the email message with date, time, website name, and the custom message
    $emailMessage = "Date & Time: $dateTime\n";
    $emailMessage .= "Website: CPShareTXT.com\n";
     $emailMessage="Dear Kavindu Bogahwatte!!!\n";
    $emailMessage .= "Message: $message\n";
    $emailMessage .= "Your database have more than 10 records.please check and delete unused records.else ur database size will increse.\n";
    $emailMessage="Have a Nice Day\n";

    // Set headers
    $headers = 'From: kmdproduction@cpsharetxt.com' . "\r\n" . // Replace with your sender email
               'Reply-To: noreply@example.com' . "\r\n" .
               'X-Mailer: PHP/' . phpversion();

    // Send email
    if (mail($adminEmail, $subject, $emailMessage, $headers)) {
        echo 'Email sent successfully!';
    } else {
        http_response_code(500);
        echo 'Failed to send email.';
    }
} else {
    http_response_code(400);
    echo 'Invalid request.';
}
?>
