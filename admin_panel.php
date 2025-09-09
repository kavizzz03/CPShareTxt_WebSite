<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'u569550465_kavindu', 'Malshan2003#', 'u569550465_dew');

// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Admin login credentials
$adminUsername = 'kavizz';
$adminPassword = 'Malshan2003#'; // Change this to a strong password!

// Handle login logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === $adminUsername && $password === $adminPassword) {
        $_SESSION['admin_logged_in'] = true;

        // Get login details
        $loginTime = date('Y-m-d H:i:s');
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $ipAddress = $_SERVER['REMOTE_ADDR'];

        // Send email notification to admin
        $adminEmail = "kavindumalshan2003@gmail.com";
        $subject = "🔔 Admin Panel Login Alert!";
        $message = "
        🛡️ Admin Panel Login Alert 🛡️\n
        ---------------------------------\n
        🕒 Login Time: $loginTime\n
        🌍 IP Address: $ipAddress\n
        💻 Device Info: $userAgent\n
        ---------------------------------\n
        If this wasn't you, please take necessary actions.";

        // Send email
        $headers = "From: no-reply@yourdomain.com\r\n" .
                   "Reply-To: no-reply@yourdomain.com\r\n" .
                   "X-Mailer: PHP/" . phpversion();

        mail($adminEmail, $subject, $message, $headers);

        // Redirect to admin panel
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $loginError = '❌ Invalid credentials!';
    }
}

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Panel Login</title>
        <link rel="icon" href="icontxt.webp">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body backgaround="imageback.png">
        <div class="container" style="max-width: 400px; margin-top: 50px;">
            <h2>Admin Login</h2>';
            if (isset($loginError)) {
                echo '<div class="alert alert-danger">' . $loginError . '</div>';
            }
            echo '
            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" name="login" class="btn btn-primary">Login</button>
                <a href="index.html" class="btn btn-secondary">Back to Home</a>
            </form>
        </div>
    </body>
    </html>';
    exit();
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fileLimit'])) {
    // Get values directly in MB and minutes
    $fileLimit = $_POST['fileLimit'];  // Keep as MB
    $autoDeleteTime = $_POST['autoDeleteTime'];  // Keep as minutes

    // Update database directly with MB and minutes
    $updateSql = "UPDATE admin_settings SET file_limit = ?, auto_delete_time = ? WHERE id = 1";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param('ii', $fileLimit, $autoDeleteTime);

    if ($stmt->execute()) {
        $updateSuccess = '✅ Settings updated successfully!';
    } else {
        $updateError = '❌ Error updating settings!';
    }
    $stmt->close();
}

// Fetch settings from the database
$sql = "SELECT file_limit, auto_delete_time FROM admin_settings WHERE id = 1";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $settings = $result->fetch_assoc();
    $fileLimit = $settings['file_limit'];  // MB directly
    $autoDeleteTime = $settings['auto_delete_time'];  // Minutes directly
} else {
    // Default values
    $fileLimit = 10;  // 10 MB
    $autoDeleteTime = 5;  // 5 minutes
}

// Handle logout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Admin panel UI
echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icontxt.webp">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
 <body backgaround="imageback.png">
    <div class="container" style="max-width: 600px; margin-top: 50px;">
        <h2>Admin Panel</h2>';
        if (isset($updateSuccess)) {
            echo '<div class="alert alert-success">' . $updateSuccess . '</div>';
        }
        if (isset($updateError)) {
            echo '<div class="alert alert-danger">' . $updateError . '</div>';
        }
        echo '
        <form method="POST">
            <div class="mb-3">
                <label for="fileLimit" class="form-label">File Size Limit (MB)</label>
                <input type="number" class="form-control" id="fileLimit" name="fileLimit" value="' . $fileLimit . '" required>
            </div>
            <div class="mb-3">
                <label for="autoDeleteTime" class="form-label">Auto Delete Time (Minutes)</label>
                <input type="number" class="form-control" id="autoDeleteTime" name="autoDeleteTime" value="' . $autoDeleteTime . '" required>
            </div>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>

        <form method="POST" class="mt-3">
            <button type="submit" name="logout" class="btn btn-danger">Logout</button>
        </form>
    </div>
</body>
</html>';

$conn->close();
?>
