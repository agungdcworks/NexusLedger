<?php
// NexusLedger - Audit Logs
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/page.php';
require_login(); // BAC: intentionally not require_admin()

page_header('Audit Logs', 'admin_audit');
?>

<div class="card">
    <div class="card-header">
        <span class="card-title">System Audit Trail</span>
        <span class="badge badge-info">Last 100 entries</span>
    </div>
    <table>
        <thead><tr><th>Time</th><th>User ID</th><th>Action</th><th>Details</th><th>IP</th></tr></thead>
        <tbody>
        <?php
        $logs = $mysqli->query("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 100");
        if ($logs && $logs->num_rows > 0):
            while ($log = $logs->fetch_assoc()):
        ?>
        <tr>
            <td class="text-mono" style="font-size:11px"><?php echo $log['created_at']; ?></td>
            <td class="text-mono">#<?php echo $log['user_id']; ?></td>
            <td><?php echo htmlspecialchars($log['action']); ?></td>
            <td><?php echo htmlspecialchars($log['details']); ?></td>
            <td class="text-mono" style="font-size:11px"><?php echo $log['ip_address']; ?></td>
        </tr>
        <?php
            endwhile;
        else:
            echo '<tr><td colspan="5" style="text-align:center;padding:24px;color:var(--text-muted)">No audit entries.</td></tr>';
        endif;
        ?>
        </tbody>
    </table>
</div>

<?php page_footer(); ?>
