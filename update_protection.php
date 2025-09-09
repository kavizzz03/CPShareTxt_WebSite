<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "u569550465_kavindu", "Malshan2003#", "u569550465_dew");
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
    exit();
}

if (isset($_POST['username'], $_POST['is_protected'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $is_protected = intval($_POST['is_protected']);
    $sql = "UPDATE user_texts SET is_protected = $is_protected WHERE username = '$username'";
    if ($conn->query($sql)) {
        echo json_encode(['status' => 'success', 'message' => 'Protection updated']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Update failed']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
}

$conn->close();
?>
