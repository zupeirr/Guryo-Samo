<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'Commissions';
$activeAdminPage = 'commissions';

$commissions = $conn->query("
    SELECT u.full_name, u.email, a.commission_rate,
           COUNT(t.id) AS total_deals,
           COALESCE(SUM(CASE WHEN t.status='Completed' THEN t.commission ELSE 0 END),0) AS paid,
           COALESCE(SUM(CASE WHEN t.status='Pending'   THEN t.commission ELSE 0 END),0) AS pending_amt
    FROM agents a
    JOIN users u ON u.id = a.user_id
    LEFT JOIN transactions t ON t.agent_id = a.id
    GROUP BY a.id
    ORDER BY paid DESC
");

include 'includes/admin-header.php';
?>
<div class="panel">
    <div class="panel-head"><h2>Agent Commissions</h2></div>
    <div class="panel-body table-wrap">
        <table class="data-table">
            <thead><tr><th>Agent</th><th>Rate</th><th>Total Deals</th><th>Paid Commissions</th><th>Pending Commissions</th></tr></thead>
            <tbody>
                <?php if ($commissions && $commissions->num_rows > 0): while ($c = $commissions->fetch_assoc()): ?>
                <tr>
                    <td><?php echo clean($c['full_name']); ?><br><small><?php echo clean($c['email']); ?></small></td>
                    <td><?php echo number_format((float)$c['commission_rate'],2); ?>%</td>
                    <td><?php echo (int)$c['total_deals']; ?></td>
                    <td class="text-success"><?php echo formatPrice($c['paid']); ?></td>
                    <td class="text-warning"><?php echo formatPrice($c['pending_amt']); ?></td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="5" class="empty-state">No commission data yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'includes/admin-footer.php'; ?>
