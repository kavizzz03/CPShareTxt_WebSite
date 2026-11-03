<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

define("COMMON_PASSWORD", "dunil2003");

// Centralized Database connection
require_once __DIR__ . '/db.php';

// AUTO DELETE unlocked files older than 2 weeks
$twoWeeksAgo = date('Y-m-d H:i:s', strtotime('-14 days'));
$selectOldUnlocked = $conn->prepare("SELECT username, file_path FROM user_files WHERE is_locked = 0 AND created_at < ?");
if ($selectOldUnlocked) {
    $selectOldUnlocked->bind_param("s", $twoWeeksAgo);
    $selectOldUnlocked->execute();
    $resultOldUnlocked = $selectOldUnlocked->get_result();

    while ($row = $resultOldUnlocked->fetch_assoc()) {
        $usernameToDelete = $row['username'];
        $fileToDelete = $row['file_path'];
        if ($fileToDelete && file_exists($fileToDelete)) {
            @unlink($fileToDelete);
        }
        $delStmt = $conn->prepare("DELETE FROM user_files WHERE username = ?");
        if ($delStmt) {
            $delStmt->bind_param("s", $usernameToDelete);
            $delStmt->execute();
            $delStmt->close();
        }
    }
    $selectOldUnlocked->close();
}

// Lock/Unlock (GET)
if (isset($_GET['lock_toggle']) && isset($_GET['username'])) {
    $username = $_GET['username'];
    $stmt = $conn->prepare("UPDATE user_files SET is_locked = NOT is_locked WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: backend.php");
    exit();
}

// Delete (GET)
if (isset($_GET['delete']) && isset($_GET['username'])) {
    $username = $_GET['username'];
    $stmt = $conn->prepare("SELECT file_path FROM user_files WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($filePath);
        $stmt->fetch();
        $stmt->close();

        if ($filePath && file_exists($filePath)) {
            @unlink($filePath);
        }
        $delStmt = $conn->prepare("DELETE FROM user_files WHERE username = ?");
        if ($delStmt) {
            $delStmt->bind_param("s", $username);
            $delStmt->execute();
            $delStmt->close();
        }
    }

    header("Location: backend.php");
    exit();
}
?>
<!doctype html>
<html lang="en" class="dark">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <title>Files Vault & Manager — CpShare TXT</title>

  <!-- Primary Meta Tags -->
  <meta name="description" content="CpShare TXT — Manage, download, protect, or purge your secure ephemeral file uploads. Designed and developed in Colombo, Sri Lanka." />
  <meta name="author" content="Kavindu Bogahawatte — Vexel IT, Colombo, Sri Lanka" />
  <meta name="robots" content="index, follow" />

  <link rel="canonical" href="https://cpsharetxt.com/backend.php" />

  <!-- Favicon -->
  <link rel="icon" href="logo.png" type="image/png" />
  <meta name="theme-color" content="#0056d2">

  <!-- Bootstrap 5, Icons, AOS, SweetAlert2 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Unified Stylesheet -->
  <link rel="stylesheet" href="styles.css">
</head>
<body>

  <div class="app-container">

    <!-- FLOATING SITE HEADER (RESPONSIVE) -->
    <header class="site-header" data-aos="fade-down" data-aos-duration="600">
      <div class="site-navbar">
        <a href="index.html" class="brand-wrapper">
          <img src="logo.png" alt="CpShare TXT Student Sharing Platform" class="brand-img" />
        </a>

        <!-- Mobile Hamburger Toggle -->
        <button class="mobile-nav-toggle" id="mobileNavToggle" aria-label="Toggle navigation menu">
          <i class="bi bi-list"></i>
        </button>

        <!-- Nav Links (Desktop & Mobile Drawer) -->
        <nav class="nav-links-wrap" id="navLinksWrap">
          <a href="index.html" class="nav-pill"><i class="bi bi-house-door-fill"></i> Home</a>
          <a href="display_data.html" class="nav-pill"><i class="bi bi-clock-history"></i> Saved Texts</a>
          <a href="backend.php" class="nav-pill active"><i class="bi bi-folder-fill"></i> Files Vault</a>
          <a href="about.html" class="nav-pill"><i class="bi bi-info-circle-fill"></i> About Us</a>
          <a href="feedback_form.html" class="nav-pill d-none d-sm-inline-flex"><i class="bi bi-chat-heart-fill"></i> Feedback</a>
          <a href="admin_panel.php" class="nav-pill"><i class="bi bi-gear-wide-connected"></i> Admin</a>

          <button id="themeToggle" class="theme-btn" aria-label="Toggle dark/light theme" title="Toggle Theme">
            <i id="themeIcon" class="bi bi-sun-fill"></i>
          </button>
        </nav>
      </div>
    </header>

    <!-- MAIN FILES VAULT -->
    <main class="glass-panel mb-5" data-aos="fade-up">
      
      <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
          <h2 class="h4 fw-bold mb-1"><i class="bi bi-folder2-open text-info me-2"></i>Uploaded Files Vault</h2>
          <div class="small text-muted">Storage Management & Password Protected Ephemeral Files</div>
        </div>
        <span class="badge-glow-info">
          <i class="bi bi-database-fill-check me-1"></i> Vexel IT Storage Node
        </span>
      </div>

      <div class="table-responsive">
        <?php
        $result = $conn->query("SELECT * FROM user_files ORDER BY created_at DESC");
        if ($result && $result->num_rows > 0) {
          echo '<table class="app-table" role="table" aria-label="Uploaded files">';
          echo '<thead><tr><th>Student / Alias</th><th>File Name</th><th>Uploaded At</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody>';
          while ($row = $result->fetch_assoc()) {
            $username = $row['username'];
            $filePath = $row['file_path'];
            $fileBasename = htmlspecialchars(basename($filePath));
            $fileEsc = json_encode($filePath);
            $usernameEsc = json_encode($username);
            $createdAt = htmlspecialchars($row['created_at']);
            $isLocked = (bool)$row['is_locked'];
            $lockBtnText = $isLocked ? "<i class='bi bi-unlock-fill me-1'></i> Unlock" : "<i class='bi bi-lock-fill me-1'></i> Lock";

            echo "<tr>";
            echo "<td data-label='Student'><strong class=\"text-info\">" . htmlspecialchars($username) . "</strong></td>";
            echo "<td data-label='File Name' title=\"" . htmlspecialchars($filePath) . "\"><i class=\"bi bi-file-earmark-arrow-down text-info me-1\"></i>" . $fileBasename . "</td>";
            echo "<td data-label='Uploaded At' class=\"small text-muted\">" . $createdAt . "</td>";
            echo "<td data-label='Status'>" . ($isLocked ? "<span class=\"badge-glow-warning\"><i class=\"bi bi-shield-lock-fill me-1\"></i> Locked</span>" : "<span class=\"badge-glow-info\"><i class=\"bi bi-globe2 me-1\"></i> Public</span>") . "</td>";

            echo "<td class=\"actions text-end\" data-label=\"Actions\">";
            echo "<button class=\"btn btn-sm btn-outline-info me-1\" onclick='promptPasswordAndDownload($fileEsc, " . ($isLocked ? 'true' : 'false') . ")' aria-label=\"Download\"><i class=\"bi bi-download me-1\"></i> Download</button>";
            echo "<button class=\"btn btn-sm btn-outline-danger me-1\" onclick='promptPasswordAndDelete($usernameEsc, " . ($isLocked ? 'true' : 'false') . ")' aria-label=\"Delete\"><i class=\"bi bi-trash me-1\"></i> Delete</button>";
            echo "<button class=\"btn btn-sm " . ($isLocked ? 'btn-outline-warning' : 'btn-outline-primary') . "\" onclick='promptPasswordAndToggleLock($usernameEsc, " . ($isLocked ? 'true' : 'false') . ")' aria-label=\"Lock/Unlock\">$lockBtnText</button>";
            echo "</td>";

            echo "</tr>";
          }
          echo '</tbody></table>';
        } else {
          echo "<div class=\"text-center py-5 text-muted\"><i class=\"bi bi-folder-x fs-1 d-block mb-2 opacity-50\"></i>No files stored in the active vault.</div>";
        }
        $conn->close();
        ?>
      </div>

    </main>

  </div>

  <!-- ULTRA-PROFESSIONAL FOOTER -->
  <footer class="site-footer">
    <div class="container">
      <div class="row g-4 pb-4">
        <!-- Col 1: Brand & Mission -->
        <div class="col-12 col-lg-5">
          <a href="index.html" class="d-inline-block text-decoration-none">
            <img src="logo.png" alt="CpShare TXT" class="footer-logo-img" />
          </a>
          <p class="text-muted small mb-3 pe-lg-4" style="line-height: 1.7;">
            <strong>CpShare TXT</strong> is a free, fast, and secure student sharing platform engineered for university students, school children, and educators to exchange study notes, research docs, and code snippets effortlessly without registration.
          </p>
          <div class="d-flex flex-wrap align-items-center gap-2">
            <div class="maintainer-pill">
              <span class="status-dot"></span>
              <span>Maintained by <strong>Vexel IT</strong></span>
            </div>
            <span class="badge-glow-success">
              <i class="bi bi-mortarboard-fill me-1"></i> 100% Free For Education
            </span>
          </div>
        </div>

        <!-- Col 2: Navigation -->
        <div class="col-6 col-sm-3 col-lg-2">
          <div class="footer-heading"><i class="bi bi-grid-fill text-info"></i> Navigation</div>
          <ul class="footer-links-list">
            <li><a href="index.html" class="footer-link-item"><i class="bi bi-chevron-right small text-info"></i> Home</a></li>
            <li><a href="display_data.html" class="footer-link-item"><i class="bi bi-chevron-right small text-info"></i> Saved Texts</a></li>
            <li><a href="backend.php" class="footer-link-item"><i class="bi bi-chevron-right small text-info"></i> Files Vault</a></li>
            <li><a href="about.html" class="footer-link-item"><i class="bi bi-chevron-right small text-info"></i> About Us</a></li>
            <li><a href="feedback_form.html" class="footer-link-item"><i class="bi bi-chevron-right small text-info"></i> Feedback</a></li>
          </ul>
        </div>

        <!-- Col 3: Features -->
        <div class="col-6 col-sm-3 col-lg-2">
          <div class="footer-heading"><i class="bi bi-lightning-charge-fill text-info"></i> Capabilities</div>
          <ul class="footer-links-list">
            <li><span class="footer-link-item text-muted"><i class="bi bi-check2 text-info"></i> 5m Auto-Purge</span></li>
            <li><span class="footer-link-item text-muted"><i class="bi bi-check2 text-info"></i> 25MB Payload</span></li>
            <li><span class="footer-link-item text-muted"><i class="bi bi-check2 text-info"></i> 30-Day Protect</span></li>
            <li><span class="footer-link-item text-muted"><i class="bi bi-check2 text-info"></i> Vault Lock</span></li>
            <li><span class="footer-link-item text-muted"><i class="bi bi-check2 text-info"></i> Zero Tracking</span></li>
          </ul>
        </div>

        <!-- Col 4: Creator & Maintainer -->
        <div class="col-12 col-sm-6 col-lg-3">
          <div class="footer-heading"><i class="bi bi-award-fill text-info"></i> Engineering & Origin</div>
          <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-glass);">
            <div class="small fw-bold text-info mb-1">Kavindu Bogahawatte</div>
            <div class="small text-muted mb-2">Designed in 1st year Software Engineering undergrad. 1st live deployment in December 2024.</div>
            <div class="border-top border-secondary border-opacity-10 pt-2 d-flex justify-content-between align-items-center">
              <span class="small text-muted"><i class="bi bi-geo-alt-fill text-info me-1"></i> Colombo, Sri Lanka</span>
              <a href="mailto:info.vexelit@gmail.com" class="text-info small text-decoration-none" title="Email Vexel IT"><i class="bi bi-envelope-at-fill"></i></a>
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div class="footer-bottom-bar d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
          © <span id="footerYear"></span> <strong>CpShare TXT</strong>. All rights reserved. Built for learners worldwide.
        </div>
        <div>
          Designed & Developed by <strong class="text-info">Kavindu Bogahawatte</strong> • Maintained by <strong class="text-info">Vexel IT</strong>
        </div>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    AOS.init({ duration: 700, once: true });

    const COMMON_PASSWORD = <?php echo json_encode(COMMON_PASSWORD); ?>;

    /* Theme Management */
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    const htmlEl = document.documentElement;

    function applyTheme(isLight){
      if (isLight){
        htmlEl.classList.remove('dark');
        htmlEl.classList.add('light');
        document.body.classList.add('light');
        if (themeIcon) themeIcon.className = 'bi bi-moon-stars-fill';
      } else {
        htmlEl.classList.remove('light');
        htmlEl.classList.add('dark');
        document.body.classList.remove('light');
        if (themeIcon) themeIcon.className = 'bi bi-sun-fill';
      }
    }

    const savedTheme = localStorage.getItem('cp_theme');
    applyTheme(savedTheme === 'light');

    if (themeToggle){
      themeToggle.addEventListener('click', () => {
        const isLightNow = document.body.classList.contains('light');
        applyTheme(!isLightNow);
        localStorage.setItem('cp_theme', !isLightNow ? 'light' : 'dark');
      });
    }

    /* Mobile Nav Toggle */
    const mobileToggle = document.getElementById('mobileNavToggle');
    const navLinks = document.getElementById('navLinksWrap');
    if (mobileToggle && navLinks){
      mobileToggle.addEventListener('click', () => {
        navLinks.classList.toggle('show');
        const icon = mobileToggle.querySelector('i');
        if (icon){
          if (navLinks.classList.contains('show')){
            icon.className = 'bi bi-x-lg';
          } else {
            icon.className = 'bi bi-list';
          }
        }
      });
    }

    const yearEl = document.getElementById('footerYear');
    if (yearEl) yearEl.textContent = new Date().getFullYear();

    // SweetAlert Handlers
    function promptPasswordAndDownload(filePath, isLocked) {
      if (!isLocked) {
        window.open(filePath, '_blank');
        return;
      }
      Swal.fire({
        title: 'Vault Security Verification',
        text: 'This file is password protected.',
        input: 'password',
        inputPlaceholder: 'Enter security password',
        showCancelButton: true,
        confirmButtonText: 'Download',
        confirmButtonColor: '#0056d2',
        preConfirm: (value) => {
          if (!value || value !== COMMON_PASSWORD) {
            Swal.showValidationMessage('❌ Incorrect security password');
            return false;
          }
        }
      }).then((res) => {
        if (res.isConfirmed) {
          window.open(filePath, '_blank');
        }
      });
    }

    function promptPasswordAndDelete(username, isLocked) {
      if (isLocked) {
        Swal.fire({
          title: 'Protected File Deletion',
          text: 'Enter security password to permanently delete this file.',
          input: 'password',
          inputPlaceholder: 'Enter security password',
          showCancelButton: true,
          confirmButtonText: 'Delete',
          confirmButtonColor: '#ef4444',
          preConfirm: (value) => {
            if (!value || value !== COMMON_PASSWORD) {
              Swal.showValidationMessage('❌ Incorrect security password');
              return false;
            }
          }
        }).then((res) => {
          if (res.isConfirmed) {
            window.location.href = '?delete=true&username=' + encodeURIComponent(username);
          }
        });
      } else {
        Swal.fire({
          title: 'Confirm Deletion',
          text: 'This will permanently remove the file from storage.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete',
          confirmButtonColor: '#ef4444'
        }).then((r) => {
          if (r.isConfirmed) {
            window.location.href = '?delete=true&username=' + encodeURIComponent(username);
          }
        });
      }
    }

    function promptPasswordAndToggleLock(username, isLocked) {
      if (isLocked) {
        Swal.fire({
          title: 'Unlock Vault File',
          input: 'password',
          inputPlaceholder: 'Enter security password',
          showCancelButton: true,
          confirmButtonText: 'Unlock',
          confirmButtonColor: '#f59e0b',
          preConfirm: (value) => {
            if (!value || value !== COMMON_PASSWORD) {
              Swal.showValidationMessage('❌ Incorrect security password');
              return false;
            }
          }
        }).then((res) => {
          if (res.isConfirmed) {
            window.location.href = '?lock_toggle=true&username=' + encodeURIComponent(username);
          }
        });
      } else {
        Swal.fire({
          title: 'Lock File with Password?',
          text: 'Locking requires a security key to download or delete.',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Lock',
          confirmButtonColor: '#f59e0b'
        }).then((r) => {
          if (r.isConfirmed) {
            window.location.href = '?lock_toggle=true&username=' + encodeURIComponent(username);
          }
        });
      }
    }
  </script>
</body>
</html>