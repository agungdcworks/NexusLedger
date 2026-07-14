<?php
// NexusLedger - Fund Transfer
// Contains: CSRF (no token in low level), Insecure CAPTCHA (step-based bypass)

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/page.php';
require_login();

$message = '';
$step = $_GET['step'] ?? ($_POST['step'] ?? 1);

// CSRF protection only in impossible level
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['step'] ?? '') == '3') {
    if (SECURITY_LEVEL === 'impossible') {
        if (!check_csrf_token($_POST['csrf_token'] ?? '')) {
            $message = '<div class="alert alert-error">Invalid security token. Please try again.</div>';
            $step = 1;
        }
    }

    if ($step == 3) {
        $to = $_POST['to_account'] ?? '';
        $amount = $_POST['amount'] ?? 0;
        $desc = $_POST['description'] ?? '';
        $captcha = $_POST['captcha'] ?? '';
        $step_num = $_POST['step_num'] ?? '1';

        // Captcha bypass: step-based validation
        if (SECURITY_LEVEL === 'low') {
            // No captcha validation at all
            $captcha_ok = true;
        } elseif (SECURITY_LEVEL === 'medium') {
            // Captcha checked but step can be manipulated
            $captcha_ok = ($step_num == '2' && $captcha === 'PASS') || $step_num != '2';
        } elseif (SECURITY_LEVEL === 'high') {
            // Server-side step tracking, but captcha is weak
            $captcha_ok = ($captcha === 'VERIFIED');
        } else {
            // Impossible: proper Google reCAPTCHA
            $captcha_ok = true; // placeholder
        }

        if ($captcha_ok && $amount > 0) {
            $ref = 'TXN-' . strtoupper(substr(md5(time()), 0, 8));
            $mysqli->query("INSERT INTO transactions (user_id, from_account, to_account, amount, type, status, description, ref_number) VALUES ({$_SESSION['user_id']}, 'ACC-" . $_SESSION['user_id'] . "001', '$to', $amount, 'transfer', 'completed', '$desc', '$ref')");
            $message = '<div class="alert alert-success">Transfer of $' . number_format($amount, 2) . ' completed successfully! Ref: ' . $ref . '</div>';
            $step = 1;
        } else {
            $message = '<div class="alert alert-error">Transfer failed. Invalid captcha or amount.</div>';
        }
    }
}

page_header('Transfer Funds', 'transfer');
?>

<div class="card" style="max-width:600px">
    <div class="card-header"><span class="card-title">New Transfer</span></div>
    <?php echo $message; ?>

    <?php if ($step == 1): ?>
    <form method="POST" action="?step=2">
        <input type="hidden" name="step" value="2">
        <div class="form-group">
            <label class="form-label">Recipient Account</label>
            <input type="text" name="to_account" class="form-control" placeholder="ACC-XXXX" required>
        </div>
        <div class="form-group">
            <label class="form-label">Amount (USD)</label>
            <input type="number" name="amount" class="form-control" placeholder="0.00" step="0.01" min="0.01" required>
        </div>
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="2" placeholder="Transfer note..."></textarea>
        </div>
        <button type="submit" class="btn btn-gold">Review Transfer</button>
    </form>

    <?php elseif ($step == 2): ?>
    <div class="alert alert-info">Please verify the details and complete the captcha.</div>
    <table class="mb-2">
        <tr><th>From</th><td>ACC-<?php echo $_SESSION['user_id']; ?>001</td></tr>
        <tr><th>To</th><td><?php echo htmlspecialchars($_POST['to_account'] ?? ''); ?></td></tr>
        <tr><th>Amount</th><td>$<?php echo number_format($_POST['amount'] ?? 0, 2); ?></td></tr>
        <tr><th>Description</th><td><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></td></tr>
    </table>
    <form method="POST" action="?step=3">
        <input type="hidden" name="step" value="3">
        <input type="hidden" name="step_num" value="2">
        <input type="hidden" name="to_account" value="<?php echo htmlspecialchars($_POST['to_account'] ?? ''); ?>">
        <input type="hidden" name="amount" value="<?php echo htmlspecialchars($_POST['amount'] ?? '0'); ?>">
        <input type="hidden" name="description" value="<?php echo htmlspecialchars($_POST['description'] ?? ''); ?>">
        <div class="form-group">
            <label class="form-label">Security Verification</label>
            <div style="background:var(--bg-input);border:1px solid var(--border);border-radius:var(--radius);padding:16px;text-align:center;margin-bottom:8px">
                <span style="font-family:'JetBrains Mono',monospace;font-size:18px;letter-spacing:4px;color:var(--gold)">XK4T-9M2P</span>
            </div>
            <input type="text" name="captcha" class="form-control" placeholder="Enter code above" required>
        </div>
        <?php if (SECURITY_LEVEL === 'impossible'): echo csrf_field(); endif; ?>
        <button type="submit" class="btn btn-gold">Confirm Transfer</button>
        <a href="transfer.php" class="btn btn-outline">Cancel</a>
    </form>
    <?php endif; ?>
</div>

<?php page_footer(); ?>
