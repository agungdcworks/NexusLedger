<div class="card">
    <div class="card-header"><span class="card-title">Annual Report - <?php echo date('Y'); ?></span></div>
    <div class="grid grid-4 mb-2">
        <div class="stat-card"><span class="stat-label">Total Volume</span><div class="stat-value stat-accent">$<?php echo number_format(rand(100000, 999999), 2); ?></div></div>
        <div class="stat-card"><span class="stat-label">Transactions</span><div class="stat-value stat-accent-blue"><?php echo rand(50, 500); ?></div></div>
        <div class="stat-card"><span class="stat-label">Growth</span><div class="stat-value stat-accent-green">+<?php echo rand(5, 35); ?>%</div></div>
        <div class="stat-card"><span class="stat-label">Fees Paid</span><div class="stat-value text-red">$<?php echo number_format(rand(500, 5000), 2); ?></div></div>
    </div>
    <p>Full annual report with detailed breakdown will be available for download.</p>
    <p class="text-muted" style="font-size:11px">Annual Report ID: AR-<?php echo date('Y'); ?></p>
</div>
