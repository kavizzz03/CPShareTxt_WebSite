<?php
if (isset($_GET['file'])) {
    $file = $_GET['file'];
    $filePath = "uploads/" . basename($file); // Change this path to the directory where your files are stored

    if (file_exists($filePath)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        echo "File not found.";
    }
} else {
    echo "No file specified.";
}
?>
