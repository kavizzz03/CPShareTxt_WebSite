<?php
// Database connection details
$servername = "localhost";
$username = "u569550465_kavindu";  // Your database username
$password = "Malshan2003#";  // Your database password
$dbname = "u569550465_dew";  // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the current time and calculate the cutoff time (5 minutes ago)
$cutoff_time = date('Y-m-d H:i:s', strtotime('-5 minutes'));

// SQL query to delete records older than 5 minutes based on the 'saved_at' timestamp
$sql = "DELETE FROM user_texts WHERE saved_at <= ?";

// Prepare the statement
if ($stmt = $conn->prepare($sql)) {
    // Bind the parameter
    $stmt->bind_param("s", $cutoff_time);

    // Execute the query
    if ($stmt->execute()) {
        echo "Records older than 5 minutes deleted successfully.";
    } else {
        echo "Error deleting records: " . $conn->error;
    }

    // Close the statement
    $stmt->close();
} else {
    echo "Error preparing the statement: " . $conn->error;
}

// Close the connection
$conn->close();
?>
