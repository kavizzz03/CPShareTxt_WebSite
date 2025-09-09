<?php
define("COMMON_PASSWORD", "dunil2003");

$conn = new mysqli("localhost", "u569550465_kavindu", "Malshan2003#", "u569550465_dew");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// AUTO DELETE unlocked files older than 2 weeks (14 days)
$twoWeeksAgo = date('Y-m-d H:i:s', strtotime('-14 days'));

// Select all unlocked files older than 2 weeks
$selectOldUnlocked = $conn->prepare("SELECT username, file_path FROM user_files WHERE is_locked = 0 AND created_at < ?");
$selectOldUnlocked->bind_param("s", $twoWeeksAgo);
$selectOldUnlocked->execute();
$resultOldUnlocked = $selectOldUnlocked->get_result();

while ($row = $resultOldUnlocked->fetch_assoc()) {
    $usernameToDelete = $row['username'];
    $fileToDelete = $row['file_path'];
    
    // Delete the file if exists
    if ($fileToDelete && file_exists($fileToDelete)) {
        unlink($fileToDelete);
    }
    
    // Delete the record from DB
    $delStmt = $conn->prepare("DELETE FROM user_files WHERE username = ?");
    $delStmt->bind_param("s", $usernameToDelete);
    $delStmt->execute();
}

$selectOldUnlocked->close();

// Lock/Unlock file
if (isset($_GET['lock_toggle']) && isset($_GET['username'])) {
    $username = $_GET['username'];
    $stmt = $conn->prepare("UPDATE user_files SET is_locked = NOT is_locked WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    header("Location: backend.php");
    exit();
}

// Delete file if password is correct
if (isset($_GET['delete']) && isset($_GET['username'])) {
    $username = $_GET['username'];
    $stmt = $conn->prepare("SELECT file_path FROM user_files WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($filePath);
    $stmt->fetch();
    $stmt->close();

    if ($filePath && file_exists($filePath)) unlink($filePath);

    $delStmt = $conn->prepare("DELETE FROM user_files WHERE username = ?");
    $delStmt->bind_param("s", $username);
    $delStmt->execute();

    echo "<p class='text-success text-center mt-3'>Record deleted successfully.</p>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>CP Share TXT - File Manager</title>
  <link rel="icon" href="icontxt.webp" />
  
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Font Awesome for icons -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />

  <style>
    body {
      background: linear-gradient(to right, #000000cc, #1c1c1cdd), url('imageback.png') no-repeat center center fixed;
      background-size: cover;
      color: white;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    main.container {
      flex-grow: 1;
    }
    table {
      background-color: white;
      color: black;
    }
    .btn-custom {
      margin-right: 8px;
      min-width: 90px;
    }
    .header-title {
      color: #0ea5e9;
      font-weight: 700;
      text-align: center;
      margin: 30px 0 20px 0;
      text-shadow: 0 0 5px #0ea5e9;
    }
    /* Navbar styles */
    .navbar {
      background-color: #1e293b !important;
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.4);
      padding: 10px 0;
      border-bottom: 3px solid #0ea5e9;
      user-select: none;
    }
    .navbar-brand,
    .navbar-nav .nav-link {
      color: #f8f9fa !important;
      font-weight: 600;
      letter-spacing: 0.03em;
      transition: color 0.3s ease;
    }
    .navbar-nav .nav-link:hover,
    .navbar-nav .nav-link.active {
      color: #0ea5e9 !important;
      font-weight: 700;
      text-shadow: 0 0 10px #0ea5e9;
    }
    .btn-admin {
      transition: all 0.3s ease;
      font-weight: 600;
      border-radius: 8px;
      padding: 6px 15px;
      margin-left: 15px;
      box-shadow: 0 0 5px transparent;
      border: 2px solid transparent;
      color: #f8f9fa !important;
      background-color: transparent;
      user-select: none;
    }
    .btn-admin:hover {
      background-color: #0ea5e9 !important;
      border-color: #0ea5e9 !important;
      box-shadow: 0 0 15px #0ea5e9;
      color: white !important;
      text-decoration: none;
    }
    /* Footer styles */
    footer.footer-custom {
      margin-top: auto;
      background: linear-gradient(135deg, #1e293b, #0ea5e9);
      border-top: 3px solid #0284c7;
      box-shadow: inset 0 4px 10px rgba(255, 255, 255, 0.1);
      font-weight: 500;
      font-size: 0.9rem;
      color: #f8f9fa;
      padding: 20px 0;
      text-align: center;
      user-select: none;
    }
    footer.footer-custom p,
    footer.footer-custom a {
      color: #f8f9fa !important;
      margin: 0;
      transition: color 0.3s ease;
      text-decoration: none;
    }
    footer.footer-custom a:hover,
    footer.footer-custom a:focus {
      color: #ffffff !important;
      text-shadow: 0 0 8px #ffffff;
      text-decoration: underline;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
  <div class="container px-4 px-md-5">
    <a class="navbar-brand fw-bold fs-4" href="index.html">CP Share TXT</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
            aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
      <ul class="navbar-nav fs-6">
        <li class="nav-item">
          <a class="nav-link" href="index.html">Submit Text</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="display_data.html">Saved Files</a>
        </li>
      </ul>
      <a class="btn btn-admin btn-outline-light" href="admin_panel.php">Admin Login</a>
    </div>
  </div>
</nav>

<!-- Main container -->
<main class="container my-4">

  <h2 class="header-title">📁 File Manager - Uploaded Files</h2>

  <div class="table-responsive shadow rounded">
    <?php
    $result = $conn->query("SELECT * FROM user_files ORDER BY created_at DESC");
    if ($result && $result->num_rows > 0) {
      echo '<table class="table table-bordered table-hover align-middle mb-0">';
      echo '<thead class="table-primary"><tr><th>Username</th><th>File</th><th>Uploaded At</th><th>Locked?</th><th>Actions</th></tr></thead><tbody>';
      while ($row = $result->fetch_assoc()) {
        $username = htmlspecialchars($row['username']);
        $file = htmlspecialchars($row['file_path']);
        $createdAt = htmlspecialchars($row['created_at']);
        $isLocked = (bool)$row['is_locked'];

        $lockBtnText = $isLocked ? "Unlock 🔓" : "Lock 🔒";
        $lockBtnClass = $isLocked ? "btn-warning" : "btn-outline-info";

        echo "<tr>";
        echo "<td>$username</td>";
        echo "<td><a href='$file' target='_blank' download>" . basename($file) . "</a></td>";
        echo "<td>$createdAt</td>";
        echo "<td>" . ($isLocked ? "<span class='badge bg-warning text-dark'>Yes</span>" : "<span class='badge bg-secondary'>No</span>") . "</td>";
        echo "<td>";
        if ($isLocked) {
          echo "<button class='btn btn-warning btn-sm btn-custom' onclick=\"promptPasswordAndDownload('$file')\">Download</button>";
          echo "<button class='btn btn-danger btn-sm btn-custom' onclick=\"promptPasswordAndDelete('$username')\">Delete</button>";
        } else {
          echo "<a class='btn btn-success btn-sm btn-custom' href='$file' download>Download</a>";
          echo "<a class='btn btn-danger btn-sm btn-custom' href='?delete=true&username=" . urlencode($username) . "' onclick='return confirm(\"Are you sure you want to delete this file?\")'>Delete</a>";
        }
        echo "<button class='btn $lockBtnClass btn-sm' onclick=\"promptPasswordAndToggleLock('$username', $isLocked)\">$lockBtnText</button>";
        echo "</td>";
        echo "</tr>";
      }
      echo '</tbody></table>';
    } else {
      echo "<p class='text-center text-light py-3'>No files uploaded yet.</p>";
    }
    $conn->close();
    ?>
  </div>

</main>

<!-- Footer -->
<footer class="footer-custom">
  <p>Developer: <a href="https://your-website.com" target="_blank" rel="noopener noreferrer">Kavizz</a> | All Rights Reserved © 2025</p>
</footer>

<script>
function promptPasswordAndDownload(filePath) {
  const pwd = prompt("Enter password to download:");
  if (pwd === "CariTokka") {
    window.open(filePath, '_blank');
  } else {
    alert("❌ Incorrect password!");
  }
}

function promptPasswordAndDelete(username) {
  const pwd = prompt("Enter password to delete:");
  if (pwd === "CariTokka") {
    window.location.href = "?delete=true&username=" + encodeURIComponent(username);
  } else {
    alert("❌ Incorrect password!");
  }
}

function promptPasswordAndToggleLock(username, isLocked) {
  if (isLocked) {
    const pwd = prompt("Enter password to unlock:");
    if (pwd === "dunil2003") {
      window.location.href = "?lock_toggle=true&username=" + encodeURIComponent(username);
    } else {
      alert("❌ Incorrect password!");
    }
  } else {
    // Locking doesn't require password
    window.location.href = "?lock_toggle=true&username=" + encodeURIComponent(username);
  }
}
</script>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
