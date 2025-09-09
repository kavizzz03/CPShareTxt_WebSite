<?php
// Database connection
$servername = "localhost";
$username = "u569550465_kavindu";
$password = "Malshan2003#";
$dbname = "u569550465_dew";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Automatically delete records older than 5 minutes
$current_time = time();
$sql = "SELECT file_path, uploaded_at FROM user_files";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $uploaded_time = strtotime($row['uploaded_at']);
        $filePath = $row['file_path'];

        if (($current_time - $uploaded_time) > 300) { // 5 minutes = 300 seconds
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $delete_sql = "DELETE FROM user_files WHERE file_path = ?";
            $stmt = $conn->prepare($delete_sql);
            $stmt->bind_param("s", $filePath);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Handle delete request
if (isset($_GET['delete'])) {
    $filePath = $_GET['delete'];

    if (file_exists($filePath)) {
        unlink($filePath);
    }

    $sql = "DELETE FROM user_files WHERE file_path = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $filePath);
    $stmt->execute();
    $stmt->close();

    header("Location: backend.php");
    exit;
}

// Fetch records for front-end display
$sql = "SELECT username, file_path, uploaded_at FROM user_files";
$result = $conn->query($sql);

$data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

$conn->close();
?>
