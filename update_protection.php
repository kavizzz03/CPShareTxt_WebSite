<?php
header('Content-Type: application/json; charset=utf-8');

// Centralized Database connection
require_once __DIR__ . '/db.php';

if (isset($_POST['username'], $_POST['is_protected'])) {
    $username = trim($_POST['username']);
    $is_protected = intval($_POST['is_protected']);

    $stmt = $conn->prepare("UPDATE user_texts SET is_protected = ? WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("is", $is_protected, $username);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Protection status updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Update failed: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
}

$conn->close();
?>