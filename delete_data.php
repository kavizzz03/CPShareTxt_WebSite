<?php
header('Content-Type: application/json; charset=utf-8');

// Centralized Database connection
require_once __DIR__ . '/db.php';

// Handle deletion if POST request is received
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_username'])) {
    $delete_username = $_POST['delete_username'];

    // Prepare the delete query
    $stmt = $conn->prepare("DELETE FROM user_texts WHERE username = ?");
    $stmt->bind_param("s", $delete_username);

    // Execute and check success
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(["status" => "success", "message" => "Record deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete record or record not found"]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request: missing delete_username"]);
}

$conn->close();
?>