<?php
session_start();

// Centralized Database connection
require_once __DIR__ . '/db.php';

// Admin credentials
$adminUsername = 'kavizz';
$adminPassword = 'Malshan2003#';

// Handle login logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === $adminUsername && $password === $adminPassword) {
        $_SESSION['admin_logged_in'] = true;

        $loginTime = date('Y-m-d H:i:s');
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

        $adminEmail = "kavindumalshan2003@gmail.com";
        $subject = "🔔 Admin Panel Login Alert!";
        $message = "🛡️ Admin Panel Login Alert\n---------------------------------\n🕒 Login Time: $loginTime\n🌍 IP Address: $ipAddress\n💻 Device Info: $userAgent\n---------------------------------";

        $headers = "From: no-reply@cpsharetxt.com\r\n" .
                   "Reply-To: no-reply@cpsharetxt.com\r\n" .
                   "X-Mailer: PHP/" . phpversion();

        @mail($adminEmail, $subject, $message, $headers);

        header("Location: admin_panel.php");
        exit();
    } else {
        $loginError = 'Invalid admin credentials!';
    }
}

// Handle settings update
if (isset($_SESSION['admin_logged_in']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fileLimit'])) {
    $fileLimit = (int)$_POST['fileLimit'];
    $autoDeleteTime = (int)$_POST['autoDeleteTime'];

    $updateSql = "UPDATE admin_settings SET file_limit = ?, auto_delete_time = ? WHERE id = 1";
    $stmt = $conn->prepare($updateSql);
    if ($stmt) {
        $stmt->bind_param('ii', $fileLimit, $autoDeleteTime);
        if ($stmt->execute()) {
            $updateSuccess = 'Settings saved successfully!';
        } else {
            $updateError = 'Error updating settings in database!';
        }
        $stmt->close();
    } else {
        $updateError = 'Prepare failed: ' . $conn->error;
    }
}

// Handle logout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout'])) {
    unset($_SESSION['admin_logged_in']);
    session_destroy();
    header('Location: admin_panel.php');
    exit();
}

// Fetch current settings
$sql = "SELECT file_limit, auto_delete_time FROM admin_settings WHERE id = 1";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $settings = $result->fetch_assoc();
    $fileLimit = (int)$settings['file_limit'];
    $autoDeleteTime = (int)$settings['auto_delete_time'];
} else {
    $fileLimit = 10;
    $autoDeleteTime = 5;
}
?>
<!doctype html>
<html lang="en" class="dark">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <title>Admin Console — CpShare TXT</title>

  <!-- Favicon -->
  <link rel="icon" href="logo.png" type="image/png" />
  <meta name="theme-color" content="#0056d2">

  <!-- Bootstrap 5, Icons, AOS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

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
          <a href="backend.php" class="nav-pill"><i class="bi bi-folder-fill"></i> Files Vault</a>
          <a href="about.html" class="nav-pill"><i class="bi bi-info-circle-fill"></i> About Us</a>
          <a href="feedback_form.html" class="nav-pill d-none d-sm-inline-flex"><i class="bi bi-chat-heart-fill"></i> Feedback</a>
          <a href="admin_panel.php" class="nav-pill active"><i class="bi bi-gear-wide-connected"></i> Admin</a>

          <button id="themeToggle" class="theme-btn" aria-label="Toggle dark/light theme" title="Toggle Theme">
            <i id="themeIcon" class="bi bi-sun-fill"></i>
          </button>
        </nav>
      </div>
    </header>

    <!-- MAIN ADMIN PANEL AREA -->
    <main class="d-flex align-items-center justify-content-center py-4 mb-5" data-aos="fade-up">
      <?php if (!isset($_SESSION['admin_logged_in'])): ?>
        <!-- Login Card -->
        <div class="glass-panel w-100" style="max-width: 440px;">
          <div class="text-center mb-4">
            <div class="drag-icon-circle mx-auto mb-3" style="width: 56px; height: 56px; font-size: 1.4rem;">
              <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h3 class="fw-bold mb-1">Admin Console</h3>
            <p class="text-muted small mb-0">Authorized administration authentication</p>
          </div>

          <?php if (isset($loginError)): ?>
            <div class="alert alert-danger py-2 px-3 small border-0 mb-3"><i class="bi bi-exclamation-circle me-1"></i> <?= htmlspecialchars($loginError) ?></div>
          <?php endif; ?>

          <form method="POST" novalidate>
            <div class="mb-3">
              <label for="username" class="form-label-custom">Admin Username</label>
              <input type="text" class="form-control-custom" id="username" name="username" placeholder="Username" required autofocus>
            </div>
            <div class="mb-4">
              <label for="password" class="form-label-custom">Password</label>
              <input type="password" class="form-control-custom" id="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" name="login" class="btn-brand-primary w-100 py-2"><i class="bi bi-box-arrow-in-right me-1"></i> Access Panel</button>
          </form>
        </div>
      <?php else: ?>
        <!-- Settings Cockpit -->
        <div class="glass-panel w-100" style="max-width: 600px;">
          <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom border-secondary border-opacity-20">
            <div>
              <h3 class="fw-bold mb-1"><i class="bi bi-sliders2 text-info me-2"></i>Vault Controls</h3>
              <div class="text-muted small">Configure limits & automatic retention timers</div>
            </div>
            <form method="POST" class="m-0">
              <button type="submit" name="logout" class="btn btn-sm btn-outline-danger"><i class="bi bi-box-arrow-right me-1"></i> Logout</button>
            </form>
          </div>

          <?php if (isset($updateSuccess)): ?>
            <div class="alert alert-success py-2 px-3 small border-0 mb-3"><i class="bi bi-check-circle me-1"></i> <?= htmlspecialchars($updateSuccess) ?></div>
          <?php endif; ?>
          <?php if (isset($updateError)): ?>
            <div class="alert alert-danger py-2 px-3 small border-0 mb-3"><i class="bi bi-exclamation-circle me-1"></i> <?= htmlspecialchars($updateError) ?></div>
          <?php endif; ?>

          <form method="POST">
            <div class="mb-3">
              <label for="fileLimit" class="form-label-custom">Max File Size Limit (MB)</label>
              <div class="input-group">
                <input type="number" class="form-control-custom" id="fileLimit" name="fileLimit" min="1" max="100" value="<?= htmlspecialchars($fileLimit) ?>" required>
                <span class="input-group-text bg-transparent border-secondary text-info">MB</span>
              </div>
              <small class="text-muted">Maximum allowed file upload size per session.</small>
            </div>

            <div class="mb-4">
              <label for="autoDeleteTime" class="form-label-custom">Auto Delete Time (Minutes)</label>
              <div class="input-group">
                <input type="number" class="form-control-custom" id="autoDeleteTime" name="autoDeleteTime" min="1" max="1440" value="<?= htmlspecialchars($autoDeleteTime) ?>" required>
                <span class="input-group-text bg-transparent border-secondary text-info">Minutes</span>
              </div>
              <small class="text-muted">Retention timer before unprotected files are permanently purged.</small>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn-brand-primary flex-grow-1"><i class="bi bi-check2-circle me-1"></i> Save Configuration</button>
              <a href="backend.php" class="btn-brand-outline"><i class="bi bi-folder me-1"></i> Manage Files</a>
            </div>
          </form>
        </div>
      <?php endif; ?>
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
  (function(){
    AOS.init({ duration: 700, once: true });

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
  })();
  </script>
</body>
</html>
<?php
$conn->close();
?>