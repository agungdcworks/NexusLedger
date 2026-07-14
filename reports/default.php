<div class="card">
    <div class="card-header"><span class="card-title">Portfolio Summary Report</span></div>
    <p>Generated on: <strong><?php echo date('F j, Y'); ?></strong></p>
    <hr>
    <div class="grid grid-2">
        <div class="stat-card">
            <span class="stat-label">Total Assets</span>
            <div class="stat-value stat-accent">$<?php echo number_format($_SESSION['balance'] ?? 0, 2); ?></div>
        </div>
        <div class="stat-card">
            <span class="stat-label">Account Status</span>
            <div class="stat-value stat-accent-green">ACTIVE</div>
        </div>
    </div>
    <p>This is your portfolio summary for the current period. All values are in USD.</p>
    <p class="text-muted" style="font-size:11px">Report ID: RPT-<?php echo strtoupper(substr(md5(time()), 0, 8)); ?></p>
</div>
