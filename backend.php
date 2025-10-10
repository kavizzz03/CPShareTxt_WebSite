<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define("COMMON_PASSWORD", "dunil2003");

// Database connection
$conn = new mysqli("localhost", "u569550465_kavindu", "Malshan2003#", "u569550465_dew");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// AUTO DELETE unlocked files older than 2 weeks
$twoWeeksAgo = date('Y-m-d H:i:s', strtotime('-14 days'));
$selectOldUnlocked = $conn->prepare("SELECT username, file_path FROM user_files WHERE is_locked = 0 AND created_at < ?");
$selectOldUnlocked->bind_param("s", $twoWeeksAgo);
$selectOldUnlocked->execute();
$resultOldUnlocked = $selectOldUnlocked->get_result();

while ($row = $resultOldUnlocked->fetch_assoc()) {
    $usernameToDelete = $row['username'];
    $fileToDelete = $row['file_path'];
    if ($fileToDelete && file_exists($fileToDelete)) unlink($fileToDelete);
    $delStmt = $conn->prepare("DELETE FROM user_files WHERE username = ?");
    $delStmt->bind_param("s", $usernameToDelete);
    $delStmt->execute();
}
$selectOldUnlocked->close();

// Lock/Unlock (GET)
if (isset($_GET['lock_toggle']) && isset($_GET['username'])) {
    $username = $_GET['username'];
    $stmt = $conn->prepare("UPDATE user_files SET is_locked = NOT is_locked WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    header("Location: backend.php");
    exit();
}

// Delete (GET)
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

    header("Location: backend.php");
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>CPShareTXT - File Manager</title>
  <link rel="icon" href="icontxt.webp" />

  <!-- Bootstrap 5 + icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- AOS + Lottie + SweetAlert2 -->
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <script src="https://unpkg.com/@lottiefiles/lottie-player@1.5.7/dist/lottie-player.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    :root{
      --accent: #06b6d4;
      --accent-2: #0891b2;
      --muted: #9ca3af;
      --bg-dark: #071a2b;
      --bg-light: #f8fafc;
      --card-radius: 1rem;
      --focus: rgba(6,182,212,0.14);
      --glass-dark: rgba(255,255,255,0.03);
      --glass-light: rgba(2,6,23,0.04);
      --max-width: 1200px;
      --table-bg-light: #ffffff;
      --table-bg-dark: rgba(255,255,255,0.05); /* transparent glass in dark mode */
    }

    html,body{height:100%;}
    body{
      margin:0;
      font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      color:#0f172a;
      background: linear-gradient(180deg, var(--bg-light) 0%, #eef2ff 100%);
      -webkit-font-smoothing:antialiased;
      -webkit-text-size-adjust:100%;
      transition: background 0.28s ease, color 0.28s ease;
    }

    /* dark mode */
    body.dark-mode {
      color: #e6eef6;
      background:
        radial-gradient(1000px 600px at 10% 10%, rgba(6,182,212,0.06), transparent),
        radial-gradient(800px 500px at 90% 90%, rgba(8,145,178,0.04), transparent),
        linear-gradient(180deg, var(--bg-dark) 0%, #041023 0%);
    }

    .app-container { max-width: var(--max-width); margin:0 auto; padding:1rem; }

    /* Card */
    .card-glass{
      border-radius: var(--card-radius);
      border:1px solid rgba(2,6,23,0.06);
      background: linear-gradient(180deg,#fff,#fbfdff);
      padding: 1rem;
      transition: background 0.28s, border-color 0.28s;
    }
    body.dark-mode .card-glass{
      background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
      border:1px solid rgba(255,255,255,0.04);
      backdrop-filter: blur(8px);
    }

    header { padding: .75rem 0; display:flex; align-items:center; justify-content:space-between; }
    .brand-title { font-weight:800; letter-spacing:-0.3px; color: inherit; }
    .brand-sub { font-size:.78rem; opacity:.9; color: inherit; }

    .muted { color: rgba(15,23,42,0.6); }
    body.dark-mode .muted { color: rgba(230,238,246,0.72); }

    /* NAV header items */
    nav a, nav .nav-cta { color: inherit; text-decoration:none; transition: color .18s; }
    nav a:hover { color: var(--accent-2); }

    /* TABLE styling (glass in dark mode, white in light) */
    .table-wrap { overflow-x:auto; margin-top:1rem; border-radius:12px; }
    table.app-table {
      width:100%;
      border-collapse: separate;
      border-spacing: 0;
      background: var(--table-bg-light);
      border-radius: 12px;
      overflow: hidden;
      transition: background .28s, box-shadow .28s;
    }
    body.dark-mode table.app-table {
      background: var(--table-bg-dark);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      box-shadow: 0 6px 30px rgba(2,6,23,0.12);
    }

    .app-table thead tr { background: linear-gradient(90deg, var(--accent), var(--accent-2)); color:#fff; }
    .app-table th, .app-table td { padding: .75rem .9rem; text-align:center; vertical-align:middle; }
    .app-table tbody tr { border-bottom: 1px solid rgba(0,0,0,0.02); }
    body.dark-mode .app-table tbody tr { border-bottom: 1px solid rgba(255,255,255,0.02); }
    .app-table tbody tr:hover { transform: translateY(-2px); transition: transform .12s, background .12s; background: rgba(6,182,212,0.06); }

    /* Buttons */
    .btn-custom { min-width:90px; margin: 6px 6px 6px 0; font-weight:600; border-radius:8px; padding:.45rem .6rem; }
    .btn-download { background:#10b981; color:#fff; border:none; box-shadow: 0 6px 18px rgba(16,185,129,0.06); }
    .btn-download:hover { filter:brightness(.95); transform:translateY(-2px); }
    .btn-delete { background:#ef4444; color:#fff; border:none; box-shadow: 0 6px 18px rgba(239,68,68,0.06); }
    .btn-delete:hover { filter:brightness(.95); transform:translateY(-2px); }
    .btn-lock { background:#fbbf24; color:#000; border:none; }
    .btn-lock:hover { filter:brightness(.98); transform:translateY(-2px); }

    /* responsive */
    @media (max-width: 860px) {
      .brand-title { font-size: 1rem; }
      .btn-custom { min-width:70px; padding:.35rem .5rem; font-size:.85rem; }
    }

    /* stacked rows on small widths */
    @media (max-width: 680px) {
      .app-table thead { display:none; }
      .app-table, .app-table tbody, .app-table tr, .app-table td { display:block; width:100%; }
      .app-table tr { margin-bottom:.9rem; border-radius:10px; padding:.6rem; background: var(--table-bg-light); }
      body.dark-mode .app-table tr { background: var(--table-bg-dark); }
      .app-table td { text-align:left; padding:.6rem; position:relative; }
      .app-table td::before { content: attr(data-label); font-weight:700; display:inline-block; width:110px; color:var(--muted); }
      .app-table td.actions { display:flex; gap:.5rem; justify-content:flex-start; }
    }

    footer { margin-top: 1.5rem; text-align:center; }
  </style>
</head>
<body>
  <div class="app-container">

    <!-- NAV / HEADER (user-provided header) -->
    <header class="d-flex align-items-center justify-content-between">
      <a href="/" class="d-flex align-items-center gap-3 text-decoration-none">
        <div style="width:56px;height:56px;border-radius:12px;background:linear-gradient(135deg,#06b6d4,#0891b2);display:flex;align-items:center;justify-content:center;">
          <lottie-player src="https://assets10.lottiefiles.com/packages/lf20_jtbfg2nb.json" background="transparent" speed="1" style="width:40px;height:40px" loop autoplay></lottie-player>
        </div>
        <div>
          <div class="brand-title">CP Share TXT</div>
          <div class="brand-sub muted">Fast • Private • Free</div>
        </div>
      </a>

      <nav class="d-flex align-items-center gap-2">
        <div class="d-none d-sm-inline nav-cta muted">Free • </div>
        <a href="display_data.html" class="text-decoration-none small px-2 py-1 rounded-2 muted">Saved</a>
        <a href="feedback_form.html" class="text-decoration-none small px-2 py-1 rounded-2 muted">Feedback</a>
        <a href="admin_panel.php" class="text-decoration-none small px-2 py-1 rounded-2 muted" style="background: rgba(0,0,0,0.04);">Admin</a>

        <button id="themeToggle" class="btn btn-sm btn-outline-custom ms-2" aria-label="Toggle theme" title="Toggle light/dark">
          <i id="themeIcon" class="bi bi-moon-fill"></i>
        </button>
      </nav>
    </header>

    <!-- Main Table -->
    <main>
      <h2 class="mt-3 mb-3">📂 Uploaded Files Manager</h2>

      <div class="table-wrap card-glass p-3 shadow" data-aos="fade-up">
        <?php
        $result = $conn->query("SELECT * FROM user_files ORDER BY created_at DESC");
        if ($result && $result->num_rows > 0) {
          echo '<table class="app-table" role="table" aria-label="Uploaded files">';
          echo '<thead><tr><th>Username</th><th>File</th><th>Uploaded At</th><th>Locked?</th><th>Actions</th></tr></thead><tbody>';
          while ($row = $result->fetch_assoc()) {
            $username = $row['username'];
            $filePath = $row['file_path'];
            $fileBasename = htmlspecialchars(basename($filePath));
            $fileEsc = json_encode($filePath); // safe for JS
            $usernameEsc = json_encode($username);
            $createdAt = htmlspecialchars($row['created_at']);
            $isLocked = (bool)$row['is_locked'];
            $lockBtnText = $isLocked ? "Unlock 🔓" : "Lock 🔒";

            echo "<tr>";
            echo "<td data-label='Username'>" . htmlspecialchars($username) . "</td>";
            echo "<td data-label='File' title='" . htmlspecialchars($filePath) . "'>" . $fileBasename . "</td>";
            echo "<td data-label='Uploaded At'>" . $createdAt . "</td>";
            echo "<td data-label='Locked?'>" . ($isLocked ? "<span class='badge bg-warning text-dark'>Yes</span>" : "<span class='badge bg-secondary'>No</span>") . "</td>";

            // Actions: use buttons (so SweetAlert runs)
            echo "<td class='actions' data-label='Actions' style='white-space:nowrap;'>";
            echo "<button class='btn btn-download btn-custom' onclick='promptPasswordAndDownload($fileEsc, " . ($isLocked ? 'true' : 'false') . ")' aria-label='Download'>Download</button>";
            echo "<button class='btn btn-delete btn-custom' onclick='promptPasswordAndDelete($usernameEsc, " . ($isLocked ? 'true' : 'false') . ")' aria-label='Delete'>Delete</button>";
            echo "<button class='btn btn-lock btn-custom' onclick='promptPasswordAndToggleLock($usernameEsc, " . ($isLocked ? 'true' : 'false') . ")' aria-label='Lock/Unlock'>$lockBtnText</button>";
            echo "</td>";

            echo "</tr>";
          }
          echo '</tbody></table>';
        } else {
          echo "<p class='text-center py-3 muted'>No files uploaded yet.</p>";
        }
        $conn->close();
        ?>
      </div>
    </main>

    <!-- Footer -->
    <footer class="mt-4 py-4 text-center">
      <div class="card-glass d-inline-block p-3 rounded-3">
        <div class="small muted mb-1">Made with Kavizz - CP Share TXT</div>
        <div class="small muted">© <span id="footerYear"></span> • Team Alpha Software Solutions - Kavizz</div>
      </div>
    </footer>
  </div>

  <script>
    // inject PHP password safely
    const COMMON_PASSWORD = <?php echo json_encode(COMMON_PASSWORD); ?>;

    // footer year
    document.getElementById('footerYear').textContent = new Date().getFullYear();

    // theme toggle with persistence (uses dark-mode class)
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    const saved = localStorage.getItem('cp_theme');
    if (saved === 'dark') {
      document.body.classList.add('dark-mode');
      themeIcon.className = 'bi bi-sun-fill';
    } else {
      document.body.classList.remove('dark-mode');
      themeIcon.className = 'bi bi-moon-fill';
    }
    themeToggle.addEventListener('click', () => {
      const nowDark = !document.body.classList.contains('dark-mode');
      document.body.classList.toggle('dark-mode', nowDark);
      localStorage.setItem('cp_theme', nowDark ? 'dark' : 'light');
      themeIcon.className = nowDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
    });

    // SweetAlert flows

    function promptPasswordAndDownload(filePath, isLocked) {
      if (!isLocked) {
        // open directly
        window.open(filePath, '_blank');
        return;
      }

      Swal.fire({
        title: 'Enter password to download',
        input: 'password',
        inputPlaceholder: 'Password',
        showCancelButton: true,
        confirmButtonText: 'Download',
        allowOutsideClick: false,
        preConfirm: (value) => {
          if (!value || value !== COMMON_PASSWORD) {
            Swal.showValidationMessage('❌ Incorrect password');
            return false;
          }
        }
      }).then((res) => {
        if (res.isConfirmed) {
          window.open(filePath, '_blank');
          Swal.fire({ icon: 'success', title: 'Download started', timer: 1000, showConfirmButton: false });
        }
      });
    }

    function promptPasswordAndDelete(username, isLocked) {
      if (isLocked) {
        Swal.fire({
          title: 'Enter password to delete',
          input: 'password',
          inputPlaceholder: 'Password',
          showCancelButton: true,
          confirmButtonText: 'Delete',
          allowOutsideClick: false,
          preConfirm: (value) => {
            if (!value || value !== COMMON_PASSWORD) {
              Swal.showValidationMessage('❌ Incorrect password');
              return false;
            }
          }
        }).then((res) => {
          if (res.isConfirmed) {
            // trigger PHP deletion
            window.location.href = '?delete=true&username=' + encodeURIComponent(username);
          }
        });
      } else {
        Swal.fire({
          title: 'Are you sure?',
          text: 'This will permanently delete the file.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, delete',
          cancelButtonText: 'Cancel',
          allowOutsideClick: false
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
          title: 'Enter password to unlock',
          input: 'password',
          inputPlaceholder: 'Password',
          showCancelButton: true,
          confirmButtonText: 'Unlock',
          allowOutsideClick: false,
          preConfirm: (value) => {
            if (!value || value !== COMMON_PASSWORD) {
              Swal.showValidationMessage('❌ Incorrect password');
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
          title: 'Lock file?',
          text: 'Locking will require a password to download or delete this file.',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Lock',
          cancelButtonText: 'Cancel',
          allowOutsideClick: false
        }).then((r) => {
          if (r.isConfirmed) {
            window.location.href = '?lock_toggle=true&username=' + encodeURIComponent(username);
          }
        });
      }
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
  <script>
    AOS.init({ duration: 600, once: true });
  </script>
</body>
</html>
