<?php
// Centralized Database connection
require_once __DIR__ . '/db.php';

// Automatically delete records older than 5 minutes if unlocked
$currentTime = time();
$sql = "SELECT username, file_path, uploaded_at, delete_at, is_locked FROM user_files";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $filePath = $row['file_path'];
        $isLocked = (int)($row['is_locked'] ?? 0);
        $deleteAt = isset($row['delete_at']) ? (int)$row['delete_at'] : (strtotime($row['uploaded_at'] ?? 'now') + 300);

        // Auto delete if expired and unlocked
        if (!$isLocked && $currentTime > $deleteAt) {
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            $delStmt = $conn->prepare("DELETE FROM user_files WHERE file_path = ?");
            if ($delStmt) {
                $delStmt->bind_param("s", $filePath);
                $delStmt->execute();
                $delStmt->close();
            }
        }
    }
}

// Handle delete request
if (isset($_GET['delete'])) {
    $filePath = $_GET['delete'];
    if (file_exists($filePath)) {
        @unlink($filePath);
    }

    $stmt = $conn->prepare("DELETE FROM user_files WHERE file_path = ?");
    if ($stmt) {
        $stmt->bind_param("s", $filePath);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: backend.php");
    exit();
}

// Fetch records for front-end display
$sql = "SELECT username, file_path, uploaded_at, is_locked FROM user_files ORDER BY created_at DESC";
$result = $conn->query($sql);

$data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Output as JSON if requested
if (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    $conn->close();
    exit();
}
?>