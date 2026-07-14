<?php
// NexusLedger - Document Management
// Contains: Unrestricted File Upload, Path Traversal

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/page.php';
require_login();

$message = '';
$upload_dir = __DIR__ . '/uploads/';

// File Upload vulnerability
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $file = $_FILES['document'];
    $filename = $file['name'];

    if (SECURITY_LEVEL === 'low') {
        // No validation - upload any file
        move_uploaded_file($file['tmp_name'], $upload_dir . $filename);
        $message = '<div class="alert alert-success">Document uploaded: ' . htmlspecialchars($filename) . '</div>';
    } elseif (SECURITY_LEVEL === 'medium') {
        // Only check extension
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, ['php', 'phtml', 'php3', 'php4'])) {
            $message = '<div class="alert alert-error">Invalid file type: .' . $ext . '</div>';
        } else {
            move_uploaded_file($file['tmp_name'], $upload_dir . $filename);
            $message = '<div class="alert alert-success">Document uploaded.</div>';
        }
    } elseif (SECURITY_LEVEL === 'high') {
        // Check extension AND mime type, but bypassable
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','pdf','doc','docx','xls','xlsx','csv','txt'];
        if (in_array($ext, $allowed) && in_array($file['type'], ['image/jpeg','image/png','application/pdf'])) {
            move_uploaded_file($file['tmp_name'], $upload_dir . $filename);
            $message = '<div class="alert alert-success">Document uploaded.</div>';
        } else {
            $message = '<div class="alert alert-error">Invalid file.</div>';
        }
    } else {
        // Impossible: proper validation, rename file
        $safe_name = md5(uniqid()) . '.' . strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','pdf','csv','txt'];
        if (in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), $allowed)) {
            move_uploaded_file($file['tmp_name'], $upload_dir . $safe_name);
            $message = '<div class="alert alert-success">Document uploaded securely.</div>';
        }
    }
}

// Path Traversal - view uploaded file
if (isset($_GET['view'])) {
    // Vulnerable to path traversal in low/medium
    $view_file = $_GET['view'];
    if (SECURITY_LEVEL === 'high') {
        $view_file = str_replace(['../', '..\\'], '', $view_file);
    } elseif (SECURITY_LEVEL === 'impossible') {
        $view_file = basename($view_file);
    }
    $file_path = $upload_dir . $view_file;
    if (file_exists($file_path)) {
        header('Content-Type: application/octet-stream');
        readfile($file_path);
        exit;
    }
}

page_header('Documents', 'documents');
?>

<div class="card" style="max-width:700px">
    <div class="card-header"><span class="card-title">Upload Document</span></div>
    <?php echo $message; ?>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label class="form-label">Select file (Invoices, Receipts, Statements)</label>
            <input type="file" name="document" class="form-control" style="padding:8px" required>
        </div>
        <button type="submit" class="btn btn-gold">Upload</button>
    </form>
</div>

<div class="card" style="max-width:700px">
    <div class="card-header"><span class="card-title">Uploaded Files</span></div>
    <?php
    $files = scandir($upload_dir);
    $has_files = false;
    echo '<table><thead><tr><th>Filename</th><th>Size</th><th>Action</th></tr></thead><tbody>';
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        $has_files = true;
        $size = filesize($upload_dir . $f);
        echo '<tr>
            <td class="text-mono">' . htmlspecialchars($f) . '</td>
            <td>' . round($size/1024, 1) . ' KB</td>
            <td><a href="?view=' . urlencode($f) . '" class="btn btn-outline btn-sm">Download</a></td>
        </tr>';
    }
    if (!$has_files) {
        echo '<tr><td colspan="3" style="text-align:center;padding:24px;color:var(--text-muted)">No documents uploaded.</td></tr>';
    }
    echo '</tbody></table>';
    ?>
</div>

<?php page_footer(); ?>
