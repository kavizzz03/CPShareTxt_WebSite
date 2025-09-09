<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Connect to database
$servername = "localhost";
$username = "u569550465_kavindu";
$password = "Malshan2003#";
$dbname = "u569550465_dew";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]));
}

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
        echo json_encode(["status" => "error", "message" => "Failed to delete record"]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}

$conn->close();
?>
