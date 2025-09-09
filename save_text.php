<?php
header('Content-Type: application/json');
date_default_timezone_set('Asia/Colombo');

$conn = new mysqli('localhost', 'u569550465_kavindu', 'Malshan2003#', 'u569550465_dew');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed: ' . $conn->connect_error]);
    exit;
}

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$userText = isset($_POST['userText']) ? trim($_POST['userText']) : '';

if (empty($username) || empty($userText)) {
    echo json_encode(['success' => false, 'message' => 'Please provide both username and text.']);
    exit;
}

$currentDateTime = date('Y-m-d H:i:s');

$sql = "INSERT INTO user_texts (username, text, saved_at, created_at) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ssss", $username, $userText, $currentDateTime, $currentDateTime);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Text saved successfully at ' . $currentDateTime . ' Colombo Time.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Insert failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
