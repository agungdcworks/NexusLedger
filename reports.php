<?php
// NexusLedger - Reports
// Contains: Command Injection (export functionality), File Inclusion (template loading)

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/page.php';
require_login();

$output = '';
$template = $_GET['template'] ?? 'default';

// File Inclusion vulnerability
if (isset($_GET['template'])) {
    $template_file = __DIR__ . '/reports/' . $template . '.php';

    if (SECURITY_LEVEL === 'low') {
        // Direct file inclusion
        if (file_exists($template_file)) {
            ob_start();
            include $template_file;
            $output = ob_get_clean();
        } else {
            $output = '<div class="alert alert-error">Template not found: ' . htmlspecialchars($template) . '</div>';
        }
    } elseif (SECURITY_LEVEL === 'medium') {
        // Remove ../ but bypassable with ....//
        $template = str_replace(['../', '..\\'], '', $template);
        $template_file = __DIR__ . '/reports/' . $template . '.php';
        if (file_exists($template_file)) {
            ob_start(); include $template_file; $output = ob_get_clean();
        }
    } elseif (SECURITY_LEVEL === 'high') {
        // Whitelist
        $allowed = ['default', 'monthly', 'quarterly', 'annual'];
        if (in_array($template, $allowed)) {
            ob_start(); include $template_file; $output = ob_get_clean();
        }
    } else {
        // Impossible: strict whitelist + no user-controlled path
        $allowed = ['default', 'monthly', 'quarterly', 'annual'];
        $template = in_array($template, $allowed) ? $template : 'default';
        ob_start(); include $template_file; $output = ob_get_clean();
    }
}

// Command Injection - export CSV
if (isset($_POST['export'])) {
    $format = $_POST['format'] ?? 'csv';

    if (SECURITY_LEVEL === 'low') {
        // Direct command injection
        $cmd = "echo 'Generating report in $format format for user " . $_SESSION['username'] . "'";
        $output = '<div class="alert alert-info"><pre>' . shell_exec($cmd) . '</pre></div>';
    } elseif (SECURITY_LEVEL === 'medium') {
        // Blacklist certain characters
        $format = str_replace([';', '&&', '||', '|'], '', $format);
        $cmd = "echo 'Report format: $format'";
        $output = '<div class="alert alert-info"><pre>' . shell_exec($cmd) . '</pre></div>';
    } elseif (SECURITY_LEVEL === 'high') {
        // Whitelist approach
        $allowed_formats = ['csv', 'pdf', 'xlsx'];
        if (in_array($format, $allowed_formats)) {
            $output = '<div class="alert alert-success">Report exported as ' . $format . ' format.</div>';
        }
    } else {
        $output = '<div class="alert alert-success">Report generated using secure export pipeline.</div>';
    }
}

page_header('Reports', 'reports');
?>

<div class="grid grid-2">
    <div class="card">
        <div class="card-header"><span class="card-title">Generate Report</span></div>
        <div style="display:flex;flex-direction:column;gap:10px">
            <a href="?template=default" class="btn btn-outline">Portfolio Summary</a>
            <a href="?template=monthly" class="btn btn-outline">Monthly Statement</a>
            <a href="?template=quarterly" class="btn btn-outline">Quarterly Review</a>
            <a href="?template=annual" class="btn btn-outline">Annual Report</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><span class="card-title">Export Data</span></div>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Export Format</label>
                <select name="format" class="form-control">
                    <option value="csv">CSV</option>
                    <option value="pdf">PDF</option>
                    <option value="xlsx">Excel</option>
                </select>
            </div>
            <button type="submit" name="export" value="1" class="btn btn-gold">Export</button>
        </form>
    </div>
</div>

<?php if ($output): ?>
<div class="card"><?php echo $output; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><span class="card-title">Scheduled Reports</span></div>
    <table><thead><tr><th>Title</th><th>Template</th><th>Generated</th><th>Action</th></tr></thead>
    <tbody>
    <?php
    $reports_query = $mysqli->query("SELECT * FROM reports WHERE user_id={$_SESSION['user_id']} ORDER BY generated_at DESC LIMIT 10");
    if ($reports_query && $reports_query->num_rows > 0):
        while ($r = $reports_query->fetch_assoc()):
    ?>
        <tr>
            <td><?php echo htmlspecialchars($r['title']); ?></td>
            <td><span class="badge badge-info"><?php echo htmlspecialchars($r['template']); ?></span></td>
            <td><?php echo date('M d, Y H:i', strtotime($r['generated_at'])); ?></td>
            <td><a href="?template=<?php echo urlencode($r['template']); ?>" class="btn btn-outline btn-sm">View</a></td>
        </tr>
    <?php
        endwhile;
    else:
        echo '<tr><td colspan="4" style="text-align:center;padding:24px;color:var(--text-muted)">No reports generated yet.</td></tr>';
    endif;
    ?>
    </tbody></table>
</div>

<?php page_footer(); ?>
