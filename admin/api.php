<?php
// NexusLedger - API Dashboard
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/page.php';
require_login(); // BAC: intentionally not require_admin()

page_header('API Dashboard', 'admin_api');
?>

<div class="grid grid-2">
    <div class="card">
        <div class="card-header"><span class="card-title">API Endpoints</span></div>
        <table>
            <thead><tr><th>Method</th><th>Endpoint</th><th>Auth</th><th>Status</th></tr></thead>
            <tbody>
                <tr>
                    <td><span class="badge badge-info">GET</span></td>
                    <td class="text-mono">/api/?endpoint=health</td>
                    <td>None</td>
                    <td><span class="badge badge-success">ACTIVE</span></td>
                </tr>
                <tr>
                    <td><span class="badge badge-info">GET</span></td>
                    <td class="text-mono">/api/?endpoint=users&id={id}</td>
                    <td>API Key</td>
                    <td><span class="badge badge-success">ACTIVE</span></td>
                </tr>
                <tr>
                    <td><span class="badge badge-info">GET/POST</span></td>
                    <td class="text-mono">/api/?endpoint=transactions</td>
                    <td>API Key</td>
                    <td><span class="badge badge-success">ACTIVE</span></td>
                </tr>
                <tr>
                    <td><span class="badge badge-info">GET</span></td>
                    <td class="text-mono">/api/?endpoint=orders&id={id}</td>
                    <td>None</td>
                    <td><span class="badge badge-success">ACTIVE</span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="card">
        <div class="card-header"><span class="card-title">API Keys</span></div>
        <table>
            <thead><tr><th>User</th><th>Token</th><th>Created</th></tr></thead>
            <tbody>
            <?php
            $tokens = $mysqli->query("SELECT t.*, u.username FROM api_tokens t JOIN users u ON t.user_id=u.id ORDER BY t.created_at DESC");
            if ($tokens && $tokens->num_rows > 0):
                while ($tok = $tokens->fetch_assoc()):
            ?>
            <tr>
                <td><?php echo htmlspecialchars($tok['username']); ?></td>
                <td class="text-mono" style="font-size:11px"><?php echo substr($tok['token'], 0, 16); ?>...</td>
                <td><?php echo date('M d', strtotime($tok['created_at'])); ?></td>
            </tr>
            <?php
                endwhile;
            else:
                echo '<tr><td colspan="3" style="text-align:center;padding:24px;color:var(--text-muted)">No API keys generated.</td></tr>';
            endif;
            ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header"><span class="card-title">Quick Test</span></div>
    <form method="GET" action="../api/index.php" target="_blank" class="form-row">
        <select name="endpoint" class="form-control">
            <option value="health">Health Check</option>
            <option value="users">Users</option>
            <option value="transactions">Transactions</option>
            <option value="orders">Orders</option>
        </select>
        <input type="text" name="api_key" class="form-control" placeholder="API Key (or 'demo')" value="demo">
        <input type="text" name="id" class="form-control" placeholder="ID parameter" value="1">
        <button type="submit" class="btn btn-blue">Test API</button>
    </form>
</div>

<?php page_footer(); ?>
