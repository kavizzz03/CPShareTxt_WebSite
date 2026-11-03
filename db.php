<?php
/**
 * CPShareTXT - Centralized Database Connection
 * Maintained by Vexel IT
 */

// Report errors cleanly without breaking JSON responses
mysqli_report(MYSQLI_REPORT_OFF);

// Define default database credentials
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'if0_43346459');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: 'Malshan2003');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'if0_43346459_dew');

// Remote fallback host for InfinityFree / external hosting environments
$fallbackHost = 'sql101.infinityfree.com';

// Attempt primary connection
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// If localhost fails and primary wasn't fallbackHost, try fallback host
if ($conn->connect_error && DB_HOST !== $fallbackHost) {
    $conn = @new mysqli($fallbackHost, DB_USER, DB_PASS, DB_NAME);
}

// Check final connection status
if ($conn->connect_error) {
    // If running in an API context (headers sent or requesting JSON), respond with JSON
    $isJsonExpected = (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
                   || (!empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
                   || (basename($_SERVER['PHP_SELF'] ?? '') === 'save_text.php')
                   || (basename($_SERVER['PHP_SELF'] ?? '') === 'save_file.php')
                   || (basename($_SERVER['PHP_SELF'] ?? '') === 'load_data_json.php')
                   || (basename($_SERVER['PHP_SELF'] ?? '') === 'delete_data.php')
                   || (basename($_SERVER['PHP_SELF'] ?? '') === 'update_protection.php')
                   || (basename($_SERVER['PHP_SELF'] ?? '') === 'verify_password.php');

    if ($isJsonExpected) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
        }
        echo json_encode([
            'status' => 'error',
            'success' => false,
            'message' => 'Database connection failed: ' . $conn->connect_error
        ]);
        exit();
    } else {
        // Human-readable error message
        die('<div style="font-family:sans-serif;padding:2rem;text-align:center;background:#0f172a;color:#f8fafc;min-height:100vh;">' .
            '<h2>Database Connection Error</h2>' .
            '<p style="color:#94a3b8;">Unable to establish connection to database. Please verify host and credentials in <code>db.php</code>.</p>' .
            '<small style="color:#ef4444;">Error: ' . htmlspecialchars($conn->connect_error) . '</small>' .
            '</div>');
    }
}

// Set UTF-8 encoding
$conn->set_charset('utf8mb4');
?>