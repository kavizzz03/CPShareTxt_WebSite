<?php
// Centralized Database connection
require_once __DIR__ . '/db.php';

// Get current Colombo time and calculate cutoff (5 minutes ago)
date_default_timezone_set('Asia/Colombo');
$cutoff_time = date('Y-m-d H:i:s', strtotime('-5 minutes'));

// Delete unprotected records older than 5 minutes
$sql = "DELETE FROM user_texts WHERE saved_at <= ? AND is_protected = 0";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $cutoff_time);
    if ($stmt->execute()) {
        echo "Unprotected records older than 5 minutes deleted successfully.";
    } else {
        echo "Error deleting records: " . $conn->error;
    }
    $stmt->close();
} else {
    echo "Error preparing statement: " . $conn->error;
}

$conn->close();
?>