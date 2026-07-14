<div class="card">
    <div class="card-header"><span class="card-title">Monthly Statement - <?php echo date('F Y'); ?></span></div>
    <p>Prepared for: <strong><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></strong></p>
    <hr>
    <div class="grid grid-3">
        <div class="stat-card">
            <span class="stat-label">Deposits</span>
            <div class="stat-value stat-accent-green">$<?php echo number_format(rand(5000, 50000), 2); ?></div>
        </div>
        <div class="stat-card">
            <span class="stat-label">Withdrawals</span>
            <div class="stat-value text-red">$<?php echo number_format(rand(1000, 20000), 2); ?></div>
        </div>
        <div class="stat-card">
            <span class="stat-label">Net Change</span>
            <div class="stat-value stat-accent-blue">+$<?php echo number_format(rand(2000, 30000), 2); ?></div>
        </div>
    </div>
    <p class="text-muted" style="font-size:11px">Statement ID: STMT-<?php echo date('Ym'); ?>-<?php echo strtoupper(substr(md5(time()), 0, 6)); ?></p>
</div>
