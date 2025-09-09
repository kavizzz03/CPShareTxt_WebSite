<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "u569550465_kavindu";
$password = "Malshan2003#";
$dbname = "u569550465_dew";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error;
    exit();
}

// Auto delete unprotected entries older than 1 month
$autoDeleteSQL = "DELETE FROM user_texts WHERE saved_at < NOW() - INTERVAL 1 MONTH AND is_protected = 0";
$conn->query($autoDeleteSQL);

// Fetch data
$sql = "SELECT username, text, saved_at, is_protected FROM user_texts ORDER BY saved_at DESC";
$result = $conn->query($sql);
if ($result === false) {
    echo "Error executing query: " . $conn->error;
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Saved Data Viewer</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
<style>
    body {
        background: #f8f9fa;
    }
    .card-style {
        background: white;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border-radius: 12px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        padding: 1rem;
    }
    .card-style:hover {
        transform: scale(1.02);
        box-shadow: 0 6px 18px rgba(0,0,0,0.12);
    }
    .text-preview {
        max-width: 500px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        cursor: default;
    }
    .switch-toggle {
        width: 45px;
        height: 22px;
        position: relative;
        display: inline-block;
    }
    .switch-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #ccc;
        transition: 0.4s;
        border-radius: 34px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 2px;
        bottom: 2px;
        background-color: white;
        transition: 0.4s;
        border-radius: 50%;
    }
    input:checked + .slider {
        background-color: #28a745;
    }
    input:checked + .slider:before {
        transform: translateX(22px);
    }
    tr:hover {
        background-color: #e9f5ff;
        transition: background-color 0.3s ease;
    }
    button.btn {
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    button.btn:hover {
        transform: scale(1.05);
        box-shadow: 0 0 8px rgba(0,0,0,0.15);
    }
</style>
</head>
<body>

<div class="container py-5">
    <h2 class="mb-4 text-center">🗂️ User Saved Texts</h2>

    <div class="card-style table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Username</th>
                    <th>Text Preview</th>
                    <th>Saved At</th>
                    <th>Protected</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()):
                        $isChecked = $row['is_protected'] ? 'checked' : '';
                        $fullText = htmlspecialchars($row['text']);
                        $username = htmlspecialchars($row['username']);
                        $savedAt = htmlspecialchars($row['saved_at']);
                        $preview = mb_strimwidth($row['text'], 0, 80, "...");
                        $preview = htmlspecialchars($preview);
                    ?>
                    <tr>
                        <td>
                            <span
                                class="username-link text-primary fw-bold"
                                style="cursor:pointer;"
                                data-fulltext="<?= $fullText ?>"
                                data-username="<?= $username ?>"
                                tabindex="0"
                                role="button"
                                aria-label="View full text of <?= $username ?>"
                            ><?= $username ?></span>
                        </td>
                        <td class="text-preview" title="<?= $fullText ?>"><?= $preview ?></td>
                        <td><span class="badge bg-secondary"><?= $savedAt ?></span></td>
                        <td>
                            <label class="switch-toggle" title="Toggle protection for <?= $username ?>">
                                <input type="checkbox" class="protect-toggle" data-username="<?= $username ?>" <?= $isChecked ?> />
                                <span class="slider"></span>
                            </label>
                        </td>
                        <td>
                            <button
                                class="copy-btn btn btn-outline-primary btn-sm me-1"
                                data-text="<?= $fullText ?>"
                                aria-label="Copy full text of <?= $username ?>"
                            ><i class="bi bi-clipboard"></i> Copy</button>

                            <button
                                class="view-details-btn btn btn-outline-info btn-sm me-1"
                                data-fulltext="<?= $fullText ?>"
                                data-username="<?= $username ?>"
                                aria-label="View full text of <?= $username ?>"
                            ><i class="bi bi-eye"></i> View</button>

                            <button
                                class="delete-btn btn btn-outline-danger btn-sm"
                                data-username="<?= $username ?>"
                                aria-label="Delete record of <?= $username ?>"
                            ><i class="bi bi-trash"></i> Delete</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center text-muted">No data available.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for View Full Text -->
<div class="modal fade" id="viewTextModal" tabindex="-1" aria-labelledby="viewTextModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewTextModalLabel">Full Text</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="white-space: pre-wrap; word-break: break-word;">
        <!-- Full text content injected by JS -->
      </div>
    </div>
  </div>
</div>

<!-- Modal for Delete Confirmation -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteConfirmModalLabel"><i class="bi bi-exclamation-triangle-fill"></i> Confirm Delete</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete <strong id="deleteUsername"></strong>?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger btn-sm" id="confirmDeleteBtn">Delete</button>
      </div>
    </div>
  </div>
</div>

<!-- Toast container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
  <div id="liveToast" class="toast align-items-center text-white bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="toastMessage"></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
    const viewTextModal = new bootstrap.Modal(document.getElementById('viewTextModal'));
    const viewTextModalBody = document.querySelector('#viewTextModal .modal-body');
    const viewTextModalLabel = document.getElementById('viewTextModalLabel');

    const deleteConfirmModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    const deleteUsernameEl = document.getElementById('deleteUsername');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    let deleteRow = null;
    let deleteUsername = '';

    const liveToast = new bootstrap.Toast(document.getElementById('liveToast'));
    const toastMessage = document.getElementById('toastMessage');

    // Show toast
    function showToast(message, bgClass = 'bg-primary') {
        const toastEl = document.getElementById('liveToast');
        toastEl.className = `toast align-items-center text-white ${bgClass} border-0`;
        toastMessage.textContent = message;
        liveToast.show();
    }
    



    // Toggle protection checkbox
    document.querySelectorAll('.protect-toggle').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const username = this.getAttribute('data-username');
            const isProtected = this.checked ? 1 : 0;

            fetch('update_protection.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `username=${encodeURIComponent(username)}&is_protected=${isProtected}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast('🔒 Protection status updated.', 'bg-success');
                } else {
                    showToast('⚠️ Error: ' + data.message, 'bg-warning text-dark');
                }
            })
            .catch(err => showToast('❌ Error updating protection: ' + err, 'bg-danger'));
        });
    });
})();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
    const viewTextModal = new bootstrap.Modal(document.getElementById('viewTextModal'));
    const viewTextModalBody = document.querySelector('#viewTextModal .modal-body');
    const viewTextModalLabel = document.getElementById('viewTextModalLabel');
        const liveToast = new bootstrap.Toast(document.getElementById('liveToast'));
    const toastMessage = document.getElementById('toastMessage');

    // Show toast
    function showToast(message, bgClass = 'bg-primary') {
        const toastEl = document.getElementById('liveToast');
        toastEl.className = `toast align-items-center text-white ${bgClass} border-0`;
        toastMessage.textContent = message;
        liveToast.show();
        
            // 👁️ View full text
    document.querySelectorAll('.view-details-btn, .username-link').forEach(btn => {
        btn.addEventListener('click', () => {
            const fullText = btn.getAttribute('data-fulltext');
            const username = btn.getAttribute('data-username') || 'User';
            viewTextModalBody.textContent = fullText;
            viewTextModalLabel.textContent = `Full Text of ${username}`;
            viewTextModal.show();
        });
    });
    </script>

</body>
</html>

<?php
$conn->close();
?>
