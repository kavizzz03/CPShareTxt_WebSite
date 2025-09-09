<?php
// Set timezone to Colombo, Sri Lanka
date_default_timezone_set('Asia/Colombo');

// Database connection
$conn = new mysqli('localhost', 'u569550465_kavindu', 'Malshan2003#', 'u569550465_dew');

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Fetch file limit and auto delete time from admin_settings
$settingsQuery = "SELECT file_limit, auto_delete_time FROM admin_settings WHERE id = 1";
$settingsResult = $conn->query($settingsQuery);

if ($settingsResult === false || $settingsResult->num_rows === 0) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error fetching admin settings.']);
    exit;
}

$settingsRow = $settingsResult->fetch_assoc();
$maxFileSizeMB = (int)$settingsRow['file_limit']; // File limit in MB
$maxFileSize = $maxFileSizeMB * 1024 * 1024; // Convert MB to Bytes
$autoDeleteMinutes = (int)$settingsRow['auto_delete_time'];
$autoDeleteSeconds = $autoDeleteMinutes * 60; // Convert minutes to seconds

// Validate username and uploaded file
if (empty($_POST['username']) || !isset($_FILES['userFile'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Username or file is missing.']);
    exit;
}

$username = trim($_POST['username']);
$uploadedFile = $_FILES['userFile'];

// Validate the file size
if ($uploadedFile['size'] > $maxFileSize) {
    http_response_code(413);
    echo json_encode(['status' => 'error', 'message' => "File size exceeds the limit of {$maxFileSizeMB} MB. Please increase the limit in admin settings."]);
    exit;
}

// Set the file upload directory (ensure trailing slash)
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Upload directory does not exist.']);
    exit;
}

// Check if the directory is writable
if (!is_writable($uploadDir)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Upload directory is not writable.']);
    exit;
}

// Generate a unique filename to prevent overwriting and collisions
$originalName = basename($uploadedFile['name']);
$ext = pathinfo($originalName, PATHINFO_EXTENSION);
$baseName = pathinfo($originalName, PATHINFO_FILENAME);
$uniqueName = $baseName . '_' . time() . '_' . bin2hex(random_bytes(4)) . ($ext ? '.' . $ext : '');
$uploadFilePath = $uploadDir . $uniqueName;

// Move the uploaded file
if (!move_uploaded_file($uploadedFile['tmp_name'], $uploadFilePath)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error uploading the file.']);
    exit;
}

// Prepare insert SQL with created_at and default is_locked = 0, file_password = NULL
$sql = "INSERT INTO user_files (username, file_path, delete_at, created_at, is_locked, file_password) VALUES (?, ?, ?, ?, 0, NULL)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    // Clean up uploaded file if DB insert fails
    if (file_exists($uploadFilePath)) {
        unlink($uploadFilePath);
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error preparing SQL query: ' . $conn->error]);
    exit;
}

// Calculate timestamps
$currentTimestamp = time();
$deleteTimestamp = $currentTimestamp + $autoDeleteSeconds;
$createdAtDatetime = date('Y-m-d H:i:s', $currentTimestamp);

$stmt->bind_param('ssis', $username, $uploadFilePath, $deleteTimestamp, $createdAtDatetime);

if (!$stmt->execute()) {
    // Clean up uploaded file if DB insert fails
    if (file_exists($uploadFilePath)) {
        unlink($uploadFilePath);
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error saving file to database: ' . $stmt->error]);
    exit;
}

// Success response
http_response_code(200);
echo json_encode([
    'status' => 'success',
    'message' => 'File uploaded and saved successfully!',
    'filePath' => $uploadFilePath,
    'username' => $username,
    'deleteAt' => $deleteTimestamp,
    'createdAt' => $createdAtDatetime
]);

$stmt->close();
$conn->close();
?>
