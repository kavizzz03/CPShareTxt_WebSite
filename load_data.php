<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Centralized Database connection
require_once __DIR__ . '/db.php';

// Auto delete unprotected entries older than 1 month
$autoDeleteSQL = "DELETE FROM user_texts WHERE saved_at < NOW() - INTERVAL 1 MONTH AND is_protected = 0";
$conn->query($autoDeleteSQL);

// Fetch data
$sql = "SELECT username, text, saved_at, is_protected FROM user_texts ORDER BY saved_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Saved Data Table — CPShare TXT</title>
    <link rel="icon" href="logo.png" type="image/png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css" />
</head>
<body class="p-3 p-md-4">
<div class="app-container" style="max-width: 1100px; margin: 0 auto;">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h4 fw-bold mb-1"><i class="bi bi-clock-history text-info me-2"></i>Saved Texts Vault</h2>
            <div class="small text-muted">CPShare TXT Active Ephemeral Records</div>
        </div>
        <div class="d-flex gap-2">
            <a href="display_data.html" class="btn-brand-primary btn-sm"><i class="bi bi-grid-fill me-1"></i> Interactive Cards View</a>
            <a href="index.html" class="btn-brand-outline btn-sm"><i class="bi bi-house-door-fill me-1"></i> Home</a>
        </div>
    </div>

    <div class="glass-panel p-3 p-md-4">
        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th>Student / Alias</th>
                        <th>Text & Code Preview</th>
                        <th>Saved At</th>
                        <th>Protection Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()):
                            $fullText = htmlspecialchars($row['text'] ?? '');
                            $username = htmlspecialchars($row['username'] ?? 'Anonymous');
                            $savedAt = htmlspecialchars($row['saved_at'] ?? '');
                            $isProtected = (bool)$row['is_protected'];
                            $preview = htmlspecialchars(mb_strimwidth($row['text'] ?? '', 0, 120, "..."));
                        ?>
                        <tr>
                            <td data-label="Student">
                                <span class="username-link text-info fw-bold"
                                      style="cursor:pointer;"
                                      data-fulltext="<?= $fullText ?>"
                                      data-username="<?= $username ?>"><i class="bi bi-person-fill me-1"></i><?= $username ?></span>
                            </td>
                            <td data-label="Preview" style="font-family: 'JetBrains Mono', monospace; font-size: 0.88rem; color: #f1f5f9 !important;" title="<?= $fullText ?>">
                                <?= $preview ?>
                            </td>
                            <td data-label="Saved At">
                                <span class="record-meta-pill"><i class="bi bi-calendar-event text-info me-1"></i><?= $savedAt ?></span>
                            </td>
                            <td data-label="Status">
                                <?php if ($isProtected): ?>
                                    <span class="badge-glow-success"><i class="bi bi-shield-check me-1"></i> Protected</span>
                                <?php else: ?>
                                    <span class="badge-glow-warning"><i class="bi bi-hourglass-split me-1"></i> 5m Purge</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Action" class="text-end">
                                <a href="display_data.html" class="btn btn-sm btn-outline-info"><i class="bi bi-eye me-1"></i> View</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>No saved text records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
