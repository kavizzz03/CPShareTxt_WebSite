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
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Saved Data Viewer — CP Share TXT</title>
    <link rel="icon" href="icontxt.webp" type="image/webp" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        body {
            background: #030812;
            color: #f1f5f9;
            font-family: system-ui, -apple-system, sans-serif;
            padding: 2rem 1rem;
        }
        .card-style {
            background: rgba(10, 22, 40, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        }
        table { color: #f1f5f9 !important; }
        .table-hover tbody tr:hover { background: rgba(6, 182, 212, 0.06); }
        .text-preview {
            max-width: 450px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-clock-history text-info me-2"></i>User Saved Texts</h2>
            <div class="text-secondary small">CP Share TXT Ephemeral Storage</div>
        </div>
        <a href="display_data.html" class="btn btn-outline-info btn-sm"><i class="bi bi-arrow-left me-1"></i> Interactive View</a>
    </div>

    <div class="card-style table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Username</th>
                    <th>Text Preview</th>
                    <th>Saved At</th>
                    <th>Protected</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()):
                        $isChecked = $row['is_protected'] ? 'checked' : '';
                        $fullText = htmlspecialchars($row['text'] ?? '');
                        $username = htmlspecialchars($row['username'] ?? 'Anonymous');
                        $savedAt = htmlspecialchars($row['saved_at'] ?? '');
                        $preview = htmlspecialchars(mb_strimwidth($row['text'] ?? '', 0, 80, "..."));
                    ?>
                    <tr>
                        <td>
                            <span class="username-link text-info fw-bold"
                                  style="cursor:pointer;"
                                  data-fulltext="<?= $fullText ?>"
                                  data-username="<?= $username ?>"><?= $username ?></span>
                        </td>
                        <td class="text-preview" title="<?= $fullText ?>"><?= $preview ?></td>
                        <td><span class="badge bg-secondary"><?= $savedAt ?></span></td>
                        <td>
                            <input type="checkbox" class="protect-toggle" data-username="<?= $username ?>" <?= $isChecked ?> disabled />
                            <span class="small ms-1 <?= $row['is_protected'] ? 'text-success' : 'text-muted' ?>">
                                <?= $row['is_protected'] ? 'Protected' : 'Auto-purge' ?>
                            </span>
                        </td>
                        <td>
                            <a href="display_data.html" class="btn btn-outline-info btn-sm"><i class="bi bi-eye"></i> View</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
<?php
$conn->close();
?>