<?php
// NexusLedger - User Profile
// Contains: XSS Stored (guestbook/comments), XSS DOM (client-side fragment)

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/page.php';
require_login();

$message = '';

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';

    if (SECURITY_LEVEL === 'impossible') {
        $stmt = $pdo->prepare("UPDATE users SET full_name=?, email=? WHERE id=?");
        $stmt->execute([$full_name, $email, $_SESSION['user_id']]);
    } else {
        $mysqli->query("UPDATE users SET full_name='$full_name', email='$email' WHERE id={$_SESSION['user_id']}");
    }
    $_SESSION['full_name'] = $full_name;
    $message = '<div class="alert alert-success">Profile updated successfully.</div>';
}

// Add comment (XSS Stored)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    $comment = $_POST['comment'] ?? '';

    if (SECURITY_LEVEL === 'impossible') {
        $stmt = $pdo->prepare("INSERT INTO comments (user_id, username, comment) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $_SESSION['username'], htmlspecialchars($comment)]);
    } else {
        // XSS Stored: no sanitization
        $mysqli->query("INSERT INTO comments (user_id, username, comment) VALUES ({$_SESSION['user_id']}, '{$_SESSION['username']}', '$comment')");
    }
    $message = '<div class="alert alert-success">Comment posted.</div>';
}

page_header('My Profile', 'profile');
?>

<?php echo $message; ?>

<div class="grid grid-2">
    <div class="card">
        <div class="card-header"><span class="card-title">Edit Profile</span></div>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($_SESSION['full_name']); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars(current_user()['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" value="<?php echo $_SESSION['username']; ?>" disabled>
                <small class="text-muted">Username cannot be changed.</small>
            </div>
            <button type="submit" name="update_profile" value="1" class="btn btn-gold">Save Changes</button>
        </form>
    </div>

    <div class="card">
        <div class="card-header"><span class="card-title">Account Details</span></div>
        <table>
            <tr><th>User ID</th><td class="text-mono">#<?php echo $_SESSION['user_id']; ?></td></tr>
            <tr><th>Role</th><td><span class="badge badge-info"><?php echo ucfirst($_SESSION['role']); ?></span></td></tr>
            <tr><th>Balance</th><td class="text-mono">$<?php echo number_format($_SESSION['balance'], 2); ?></td></tr>
            <tr><th>Currency</th><td><?php echo $_SESSION['currency'] ?? 'USD'; ?></td></tr>
            <tr><th>Session</th><td class="text-mono" style="font-size:11px"><?php echo $_SESSION['session_id'] ?? ''; ?></td></tr>
        </table>
    </div>
</div>

<!-- XSS Stored: Comments section -->
<div class="card">
    <div class="card-header"><span class="card-title">Portfolio Notes</span></div>
    <form method="POST" class="mb-2">
        <div class="form-group">
            <textarea name="comment" class="form-control" rows="3" placeholder="Share your thoughts..." required></textarea>
        </div>
        <button type="submit" name="add_comment" value="1" class="btn btn-blue">Post Note</button>
    </form>

    <div id="comments-container">
    <?php
    $comments = $mysqli->query("SELECT * FROM comments ORDER BY created_at DESC LIMIT 20");
    while ($c = $comments->fetch_assoc()):
    ?>
    <div class="stat-card" style="margin-bottom:8px">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
            <div class="user-avatar"><?php echo strtoupper(substr($c['username'], 0, 2)); ?></div>
            <div>
                <span style="font-weight:600;color:var(--text);font-size:13px"><?php echo $c['username']; // XSS Stored: unsanitized ?></span>
                <span style="font-size:11px;color:var(--text-muted);margin-left:8px"><?php echo date('M d H:i', strtotime($c['created_at'])); ?></span>
            </div>
        </div>
        <p style="margin:0;color:var(--text-secondary)"><?php echo $c['comment']; // XSS Stored: unsanitized output ?></p>
    </div>
    <?php endwhile; ?>
    </div>
</div>

<!-- XSS DOM: Client-side URL fragment processor -->
<script>
(function() {
    var fragment = window.location.hash.substring(1);
    if (fragment) {
        document.write('<div class="alert alert-info">Profile tab: ' + fragment + '</div>');
    }
})();
</script>

<?php page_footer(); ?>
