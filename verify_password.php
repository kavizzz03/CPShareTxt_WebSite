<?php
$conn = new mysqli('localhost', 'u569550465_kavindu', 'Malshan2003#', 'u569550465_dew');

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT file_password, file_path FROM user_files WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->bind_result($dbPassword, $filePath);
$stmt->fetch();
$stmt->close();

if ($password === $dbPassword) {
    echo json_encode(['success' => true, 'download_url' => $filePath]);
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $datetime = date("Y-m-d H:i:s");

    $logMessage = "Unauthorized attempt!\nUsername: $username\nIP: $ip\nDevice: $userAgent\nTime: $datetime\n\n";

    $to = "kavizzn@gmail.com";
    $subject = "CP Share TXT - Unauthorized Access Attempt";
    $headers = "From: noreply@cpsharetxt.com";
    mail($to, $subject, $logMessage, $headers);

    echo json_encode(['success' => false]);
}
$conn->close();
?>
