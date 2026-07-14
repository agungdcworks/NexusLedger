<?php
// NexusLedger - Transactions
// Contains: SQL Injection (search query), XSS Reflected (search term echo)

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/page.php';
require_login();

$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';
$order = $_GET['order'] ?? 'DESC';
$transactions = null;

// SQL Injection vulnerability in search
if ($search) {
    // LOW: direct SQL injection
    $query = "SELECT * FROM transactions WHERE user_id={$_SESSION['user_id']} AND (ref_number LIKE '%$search%' OR description LIKE '%$search%') ORDER BY created_at " . $order;
    $transactions = $mysqli->query($query);
} else {
    $query = "SELECT * FROM transactions WHERE user_id={$_SESSION['user_id']} ORDER BY created_at " . $order;
    $transactions = $mysqli->query($query);
}

page_header('Transactions', 'transactions');
?>

<div class="card">
    <div class="card-header">
        <span class="card-title">Transaction History</span>
        <a href="transfer.php" class="btn btn-gold btn-sm">New Transfer</a>
    </div>

    <!-- Search + Filter -->
    <form method="GET" class="search-bar mb-2">
        <div style="flex:1;position:relative">
            <input type="text" name="search" class="form-control" placeholder="Search by reference or description..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <select name="order" class="form-control" style="width:auto">
            <option value="DESC" <?php echo $order === 'DESC' ? 'selected' : ''; ?>>Newest First</option>
            <option value="ASC" <?php echo $order === 'ASC' ? 'selected' : ''; ?>>Oldest First</option>
        </select>
        <button type="submit" class="btn btn-blue">Search</button>
    </form>

    <?php if ($search): ?>
    <div class="alert alert-info">
        Search results for: <strong><?php echo $search; // XSS Reflected ?></strong>
        (<?php echo $transactions ? $transactions->num_rows : 0; ?> results)
    </div>
    <?php endif; ?>

    <table>
        <thead><tr>
            <th>Ref #</th><th>From</th><th>To</th><th>Type</th><th>Amount</th><th>Status</th><th>Date</th><th>Description</th>
        </tr></thead>
        <tbody>
        <?php if ($transactions && $transactions->num_rows > 0): ?>
            <?php while ($txn = $transactions->fetch_assoc()): ?>
            <tr>
                <td class="text-mono"><?php echo htmlspecialchars($txn['ref_number']); ?></td>
                <td class="text-mono"><?php echo htmlspecialchars($txn['from_account']); ?></td>
                <td class="text-mono"><?php echo htmlspecialchars($txn['to_account']); ?></td>
                <td><span class="badge badge-info"><?php echo ucfirst($txn['type']); ?></span></td>
                <td class="text-mono">$<?php echo number_format($txn['amount'], 2); ?></td>
                <td><span class="badge badge-<?php echo $txn['status']; ?>"><?php echo ucfirst($txn['status']); ?></span></td>
                <td><?php echo date('M d, Y', strtotime($txn['created_at'])); ?></td>
                <td><?php echo htmlspecialchars($txn['description'] ?? ''); ?></td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8" style="text-align:center;padding:32px;color:var(--text-muted)">No transactions found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php page_footer(); ?>
