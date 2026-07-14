<div class="card">
    <div class="card-header"><span class="card-title">Quarterly Review - Q<?php echo ceil(date('n') / 3); ?> <?php echo date('Y'); ?></span></div>
    <div class="alert alert-success">Quarterly performance: Above market average (+8.2%)</div>
    <table>
        <thead><tr><th>Month</th><th>Volume</th><th>Profit/Loss</th><th>Status</th></tr></thead>
        <tbody>
        <?php
        $months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        $q = ceil(date('n') / 3);
        for ($i = ($q-1)*3; $i < min($q*3, 12); $i++):
            $vol = rand(10000, 100000);
            $pl = rand(-5000, 25000);
        ?>
        <tr>
            <td><?php echo $months[$i]; ?></td>
            <td class="text-mono">$<?php echo number_format($vol, 2); ?></td>
            <td class="text-mono <?php echo $pl >= 0 ? 'text-green' : 'text-red'; ?>"><?php echo $pl >= 0 ? '+' : ''; ?>$<?php echo number_format($pl, 2); ?></td>
            <td><span class="badge badge-success">CLOSED</span></td>
        </tr>
        <?php endfor; ?>
        </tbody>
    </table>
</div>
