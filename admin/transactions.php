<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'Transactions';
$activeAdminPage = 'transactions';

$filterType = clean($_GET['type'] ?? '');
$perPage = 20;

$where = '1=1';
if ($filterType) $where .= " AND t.type = '" . $conn->real_escape_string($filterType) . "'";

$totalRows = $conn->query("SELECT COUNT(*) AS c FROM transactions t WHERE $where")->fetch_assoc()['c'];
$totals    = $conn->query("SELECT SUM(amount) AS vol, SUM(commission) AS comm FROM transactions t WHERE $where")->fetch_assoc();
$pagination = paginate($totalRows, $perPage);

$stmt = $conn->prepare("SELECT t.*, p.title AS property_title, u.full_name AS agent_name FROM transactions t LEFT JOIN properties p ON p.id = t.property_id LEFT JOIN agents ag ON ag.id = t.agent_id LEFT JOIN users u ON u.id = ag.user_id WHERE $where ORDER BY t.transaction_date DESC LIMIT ? OFFSET ?");
$stmt->bind_param('ii', $perPage, $pagination['offset']);
$stmt->execute();
$transactions = $stmt->get_result();

include 'includes/admin-header.php';
?>
<div class="dash-stats" style="margin-bottom:1.5rem;">
    <div class="dash-card"><div class="ic-wrap gold"><i data-lucide="trending-up" style="width:20px;height:20px;"></i></div><div><h3><?php echo formatPrice($totals['vol'] ?? 0); ?></h3><span>Total Volume</span></div></div>
    <div class="dash-card"><div class="ic-wrap green"><i data-lucide="bar-chart-2" style="width:20px;height:20px;"></i></div><div><h3><?php echo formatPrice($totals['comm'] ?? 0); ?></h3><span>Total Commissions</span></div></div>
    <div class="dash-card"><div class="ic-wrap navy"><i data-lucide="clipboard-list" style="width:20px;height:20px;"></i></div><div><h3><?php echo (int)$totalRows; ?></h3><span>Total Transactions</span></div></div>
</div>
<div class="panel">
    <div class="panel-head">
        <h2>Transactions</h2>
        <div style="display:flex;gap:8px;">
            <form method="GET">
                <select name="type" class="form-control" style="width:auto;" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="Sale" <?php echo $filterType==='Sale'?'selected':''; ?>>Sales</option>
                    <option value="Rental" <?php echo $filterType==='Rental'?'selected':''; ?>>Rentals</option>
                </select>
            </form>
            <a href="add-transaction.php" class="btn btn-primary btn-sm">+ Add</a>
        </div>
    </div>
    <div class="panel-body table-wrap">
        <table class="data-table">
            <thead><tr><th>Property</th><th>Agent</th><th>Client</th><th>Type</th><th>Amount</th><th>Commission</th><th>Date</th><th>Status</th></tr></thead>
            <tbody>
                <?php if ($transactions && $transactions->num_rows > 0): while ($t = $transactions->fetch_assoc()): ?>
                <tr>
                    <td><?php echo clean($t['property_title'] ?: '—'); ?></td>
                    <td><?php echo clean($t['agent_name'] ?: '—'); ?></td>
                    <td><?php echo clean($t['customer_name']); ?></td>
                    <td><?php echo clean($t['type']); ?></td>
                    <td><?php echo formatPrice($t['amount']); ?></td>
                    <td><?php echo formatPrice($t['commission']); ?></td>
                    <td><?php echo date('M j, Y', strtotime($t['transaction_date'])); ?></td>
                    <td><span class="status-pill <?php echo $t['status']==='completed'?'sale':'rent'; ?>"><?php echo ucfirst($t['status']); ?></span></td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="8" class="empty-state">No transactions yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo renderPagination($pagination, 'transactions.php', ['type' => $filterType]); ?>
</div>
<?php include 'includes/admin-footer.php'; ?>
