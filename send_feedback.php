<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userEmail = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $userFeedback = htmlspecialchars($_POST['feedback']);

    // Send thank you email to user
    $subjectToUser = "Thank You for Your Feedback!";
    $messageToUser = "Dear User,\n\nThank you for your feedback! We appreciate your time and effort to share your thoughts with us.\n\nBest regards,\nKavizz";
    $headersToUser = "From: no-reply@cpsharetxt.com\r\n" .
                     "Reply-To: no-reply@cpsharetxt.com\r\n" .
                     "X-Mailer: PHP/" . phpversion();

    mail($userEmail, $subjectToUser, $messageToUser, $headersToUser);

    // Send feedback details to admin
    $adminEmail = "kavizzn@gmail.com";
    $subjectToAdmin = "New Feedback Received";
    $messageToAdmin = "You have received new feedback from a user.\n\n" .
                      "Email: $userEmail\n" .
                      "Feedback: $userFeedback\n";
    $headersToAdmin = "From: kmdproduction@cpsharetxt.com\r\n" .
                      "Reply-To: $userEmail\r\n" .
                      "X-Mailer: PHP/" . phpversion();

    mail($adminEmail, $subjectToAdmin, $messageToAdmin, $headersToAdmin);

    // Redirect to a thank you page or display a confirmation message
    echo "<script>alert('Thank you for your feedback!'); window.location.href='index.html';</script>";
} else {
    // Redirect to feedback form if accessed directly
    header("Location: feedback_form.html");
    exit;
}
?>
