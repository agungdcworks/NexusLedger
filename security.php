<?php
// NexusLedger - Security Settings
// Contains: Weak Session IDs, CSP Bypass, JavaScript Attacks, Open Redirect

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/page.php';
require_login();

$message = '';

// Change password - CSRF vulnerability in low/medium
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $new_pass = $_POST['new_password'] ?? '';

    if (SECURITY_LEVEL === 'impossible') {
        if (!check_csrf_token($_POST['csrf_token'] ?? '')) {
            $message = '<div class="alert alert-error">Invalid security token.</div>';
        } else {
            $hash = md5($new_pass);
            $stmt = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->execute([$hash, $_SESSION['user_id']]);
            $message = '<div class="alert alert-success">Password changed.</div>';
        }
    } else {
        // No CSRF protection
        $hash = md5($new_pass);
        $mysqli->query("UPDATE users SET password='$hash' WHERE id={$_SESSION['user_id']}");
        $message = '<div class="alert alert-success">Password changed.</div>';
    }
}

// Open Redirect
if (isset($_GET['return'])) {
    if (SECURITY_LEVEL === 'high') {
        // Filter external URLs
        $return = $_GET['return'];
        if (strpos($return, 'http') === 0 && strpos($return, $_SERVER['HTTP_HOST']) === false) {
            $return = 'dashboard.php';
        }
        header('Location: ' . $return);
    } else {
        header('Location: ' . $_GET['return']); // Open redirect
    }
    exit;
}

page_header('Security Settings', 'security');
?>

<?php echo $message; ?>

<div class="grid grid-2">
    <div class="card">
        <div class="card-header"><span class="card-title">Change Password</span></div>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <?php if (SECURITY_LEVEL === 'impossible'): echo csrf_field(); endif; ?>
            <button type="submit" name="change_password" value="1" class="btn btn-gold">Update Password</button>
        </form>
    </div>

    <div class="card">
        <div class="card-header"><span class="card-title">Session Information</span></div>
        <table>
            <tr><th>Session ID</th><td class="text-mono" style="font-size:11px;word-break:break-all"><?php echo session_id(); ?></td></tr>
            <tr><th>App Session</th><td class="text-mono" style="font-size:11px"><?php echo $_SESSION['session_id'] ?? 'N/A'; ?></td></tr>
            <tr><th>IP Address</th><td><?php echo $_SERVER['REMOTE_ADDR']; ?></td></tr>
            <tr><th>User Agent</th><td style="font-size:11px"><?php echo $_SERVER['HTTP_USER_AGENT']; ?></td></tr>
        </table>
        <br>
        <a href="?return=dashboard.php" class="btn btn-outline btn-sm">Test Redirect (Safe)</a>
    </div>
</div>

<!-- JavaScript Attacks: Client-side validation bypass demo -->
<div class="card">
    <div class="card-header"><span class="card-title">API Key Management</span></div>
    <form onsubmit="return validateApiKey(this)" method="GET" action="api/index.php">
        <div class="form-group">
            <label class="form-label">Generate New API Key</label>
            <div class="form-row">
                <input type="text" id="api_key" name="key" class="form-control" placeholder="Auto-generated key" readonly
                    value="nl_<?php echo substr(md5($_SESSION['user_id'] . time()), 0, 24); ?>">
                <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('api_key').value='nl_'+Math.random().toString(36).substring(2,26)">
                    Regenerate</button>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Expiry (Days)</label>
                <input type="number" id="expiry" class="form-control" value="90" min="1" max="365">
            </div>
            <div class="form-group">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-blue" onclick="alert('API key generated! (Client-side only - bypassable)')">
                    Create API Key
                </button>
            </div>
        </div>
    </form>
    <small class="text-muted">Key validation is client-side only and can be bypassed.</small>
</div>

<script>
// JavaScript Attacks: Client-side validation that can be bypassed
function validateApiKey(form) {
    var key = document.getElementById('api_key').value;
    if (key.length < 16) {
        alert('API key must be at least 16 characters.');
        return false;
    }
    return true;
}

// CSP Bypass: Inline script demonstrates weak CSP
// Security level determines CSP header
</script>

<?php
// CSP Bypass - different CSP per security level
if (SECURITY_LEVEL === 'low') {
    // No CSP header
} elseif (SECURITY_LEVEL === 'medium') {
    // Weak CSP that can be bypassed
    header("Content-Security-Policy: script-src 'self' 'unsafe-inline'");
} elseif (SECURITY_LEVEL === 'high') {
    header("Content-Security-Policy: script-src 'self'");
} else {
    header("Content-Security-Policy: script-src 'self'; object-src 'none'; base-uri 'self'");
}

page_footer();
?>
