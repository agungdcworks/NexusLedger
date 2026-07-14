<?php
// NexusLedger - Admin: User Management
// Contains: Broken Access Control, SQL Injection (Blind), Weak ID

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/page.php';
require_login(); // Intentionally not require_admin() - BAC vulnerability

$message = '';

// Broken Access Control - any logged-in user can access admin functions
// Admin check only cosmetic
if (!is_admin()) {
    $message = '<div class="alert alert-info">Viewing in read-only mode. Some actions restricted.</div>';
}

// User lookup - SQLi Blind (Boolean-based)
if (isset($_GET['lookup'])) {
    $username = $_GET['lookup'];

    if (SECURITY_LEVEL === 'low') {
        $query = "SELECT id FROM users WHERE username='$username' LIMIT 1";
        $result = $mysqli->query($query);
        if ($result && $result->num_rows > 0) {
            $message = '<div class="alert alert-success">User exists: ' . htmlspecialchars($username) . '</div>';
        } else {
            $message = '<div class="alert alert-error">User not found.</div>';
        }
    } elseif (SECURITY_LEVEL === 'high') {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username=? LIMIT 1");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $message = '<div class="alert alert-success">User exists.</div>';
        } else {
            $message = '<div class="alert alert-error">User not found.</div>';
        }
    } else {
        $message = '<div class="alert alert-error">User lookup disabled at this security level.</div>';
    }
}

// Delete user - BAC: any logged-in user can delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $uid = $_GET['delete'];
    $mysqli->query("DELETE FROM users WHERE id=$uid");
    $message = '<div class="alert alert-success">User #' . $uid . ' removed.</div>';
}

page_header('User Management', 'admin_users');
?>

<?php echo $message; ?>

<!-- User Lookup (SQLi Blind) -->
<div class="card">
    <div class="card-header"><span class="card-title">Lookup User</span></div>
    <form method="GET" class="search-bar">
        <input type="hidden" name="page" value="users">
        <input type="text" name="lookup" class="form-control" placeholder="Search by username...">
        <button type="submit" class="btn btn-blue">Lookup</button>
    </form>
    <small class="text-muted">Enter exact username to check if it exists in the system.</small>
</div>

<!-- User List -->
<div class="card">
    <div class="card-header">
        <span class="card-title">All Users</span>
        <span class="badge badge-info mt-1">
            <?php echo $mysqli->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c']; ?> users
        </span>
    </div>
    <table>
        <thead><tr>
            <th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Role</th><th>Balance</th><th>Actions</th>
        </tr></thead>
        <tbody>
        <?php
        // Weak ID enumeration
        $users = $mysqli->query("SELECT * FROM users ORDER BY id");
        while ($u = $users->fetch_assoc()):
        ?>
        <tr>
            <td class="text-mono">#<?php echo $u['id']; ?></td>
            <td>
                <a href="../dashboard.php?user_id=<?php echo $u['id']; ?>">
                    <?php echo htmlspecialchars($u['username']); ?>
                </a>
            </td>
            <td><?php echo htmlspecialchars($u['full_name']); ?></td>
            <td><?php echo htmlspecialchars($u['email']); ?></td>
            <td><span class="badge <?php echo $u['role'] === 'admin' ? 'badge-failed' : 'badge-info'; ?>"><?php echo $u['role']; ?></span></td>
            <td class="text-mono">$<?php echo number_format($u['balance'], 2); ?></td>
            <td>
                <a href="?delete=<?php echo $u['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmAction('Delete user #<?php echo $u['id']; ?>?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php page_footer(); ?>
