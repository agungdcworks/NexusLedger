<?php
// NexusLedger - Dashboard
// Contains: Authorization Bypass (view other user data via ?user_id=)
// Contains: Weak Session ID (predictable session tokens)

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/page.php';
require_login();

// Authorization Bypass vulnerability: accepts user_id parameter
$view_user_id = is_admin() && isset($_GET['user_id']) ? $_GET['user_id'] : $_SESSION['user_id'];

// Intentionally no check if the user_id belongs to current user (BAC vulnerability)
$query = "SELECT * FROM users WHERE id = " . $view_user_id;
$user_data = $mysqli->query($query)->fetch_assoc();

if (!$user_data) {
    $view_user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM users WHERE id = " . $view_user_id;
    $user_data = $mysqli->query($query)->fetch_assoc();
}

// Dashboard stats
$total_txns = $mysqli->query("SELECT COUNT(*) as cnt FROM transactions WHERE user_id=" . $view_user_id)->fetch_assoc()['cnt'];
$total_volume = $mysqli->query("SELECT COALESCE(SUM(amount),0) as total FROM transactions WHERE user_id=" . $view_user_id)->fetch_assoc()['total'];
$pending = $mysqli->query("SELECT COUNT(*) as cnt FROM transactions WHERE user_id=" . $view_user_id . " AND status='pending'")->fetch_assoc()['cnt'];
$recent = $mysqli->query("SELECT * FROM transactions WHERE user_id=" . $view_user_id . " ORDER BY created_at DESC LIMIT 5");

page_header('Portfolio Dashboard', 'dashboard');
?>

<div class="grid grid-4 mb-3">
    <div class="stat-card">
        <span class="stat-label">Total Balance</span>
        <div class="stat-value stat-accent">$<?php echo number_format($user_data['balance'], 2); ?></div>
        <span class="stat-label"><?php echo $user_data['currency']; ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Total Transactions</span>
        <div class="stat-value stat-accent-blue"><?php echo $total_txns; ?></div>
        <span class="stat-change stat-up">+12% from last month</span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Transaction Volume</span>
        <div class="stat-value stat-accent-green">$<?php echo number_format($total_volume, 2); ?></div>
        <span class="stat-change stat-up">+5.3% from last month</span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Pending Actions</span>
        <div class="stat-value text-red"><?php echo $pending; ?></div>
        <span class="stat-change stat-down">Needs attention</span>
    </div>
</div>

<div class="grid grid-2">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Recent Transactions</span>
            <a href="transactions.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <table>
            <thead><tr><th>Reference</th><th>Type</th><th>Amount</th><th>Status</th></tr></thead>
            <tbody>
            <?php while ($txn = $recent->fetch_assoc()): ?>
            <tr>
                <td class="text-mono"><?php echo htmlspecialchars($txn['ref_number']); ?></td>
                <td><span class="badge badge-info"><?php echo ucfirst($txn['type']); ?></span></td>
                <td class="text-mono">$<?php echo number_format($txn['amount'], 2); ?></td>
                <td><span class="badge badge-<?php echo $txn['status']; ?>"><?php echo ucfirst($txn['status']); ?></span></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <div class="card">
        <div class="card-header">
            <span class="card-title">Quick Actions</span>
        </div>
        <div style="display:flex;flex-direction:column;gap:12px">
            <a href="transfer.php" class="btn btn-gold">New Transfer</a>
            <a href="transactions.php" class="btn btn-outline">View All Transactions</a>
            <a href="documents.php" class="btn btn-outline">Upload Document</a>
            <a href="reports.php" class="btn btn-outline">Generate Report</a>
        </div>
        <br>
        <div class="card-header">
            <span class="card-title">Account Info</span>
        </div>
        <table>
            <tr><th>Name</th><td><?php echo htmlspecialchars($user_data['full_name']); ?></td></tr>
            <tr><th>Email</th><td><?php echo htmlspecialchars($user_data['email']); ?></td></tr>
            <tr><th>Role</th><td><?php echo ucfirst($user_data['role']); ?></td></tr>
            <tr><th>Member Since</th><td><?php echo date('F Y', strtotime($user_data['created_at'])); ?></td></tr>
        </table>
    </div>
</div>

<?php page_footer(); ?>
