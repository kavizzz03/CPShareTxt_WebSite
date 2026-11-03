<?php
header('Content-Type: application/json; charset=utf-8');

// Centralized Database connection
require_once __DIR__ . '/db.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username)) {
    echo json_encode(['success' => false, 'message' => 'Username required']);
    exit();
}

$stmt = $conn->prepare("SELECT file_password, file_path, is_locked FROM user_files WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->bind_result($dbPassword, $filePath, $isLocked);
$stmt->fetch();
$stmt->close();

// If locked and password matches, or common password matches
define("COMMON_PASSWORD", "dunil2003");

if ($password === $dbPassword || $password === COMMON_PASSWORD) {
    echo json_encode(['success' => true, 'download_url' => $filePath]);
} else {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $datetime = date("Y-m-d H:i:s");

    $logMessage = "Unauthorized attempt!\nUsername: $username\nIP: $ip\nDevice: $userAgent\nTime: $datetime\n\n";

    $to = "kavizzn@gmail.com";
    $subject = "CP Share TXT - Unauthorized Access Attempt";
    $headers = "From: noreply@cpsharetxt.com";
    @mail($to, $subject, $logMessage, $headers);

    echo json_encode(['success' => false, 'message' => 'Invalid password']);
}

$conn->close();
?>