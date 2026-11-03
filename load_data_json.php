<?php
/**
 * CPShareTXT - Load Saved Texts JSON API
 * Powered by db.php
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
date_default_timezone_set('Asia/Colombo');

require_once __DIR__ . '/db.php';

// Auto delete unprotected entries older than 1 month
$autoDeleteSQL = "DELETE FROM user_texts WHERE saved_at < NOW() - INTERVAL 1 MONTH AND is_protected = 0";
$conn->query($autoDeleteSQL);

// Fetch saved texts
$sql = "SELECT username, text, saved_at, is_protected FROM user_texts ORDER BY saved_at DESC";
$result = $conn->query($sql);

$records = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $fullText = $row['text'] ?? '';
        $preview = mb_strimwidth($fullText, 0, 800, '...');
        $records[] = [
            'username'     => $row['username'] ?? 'Anonymous',
            'fulltext'     => $fullText,
            'preview'      => $preview,
            'saved_at'     => $row['saved_at'] ?? '',
            'is_protected' => (int)($row['is_protected'] ?? 0)
        ];
    }
}

echo json_encode($records);
$conn->close();
?>