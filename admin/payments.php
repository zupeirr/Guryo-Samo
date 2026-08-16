<?php
/**
 * admin/payments.php
 * ---------------------------------------------------------------
 * Lists all payments received.
 */

require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle = 'Payments';
$activeAdminPage = 'reports'; // Keep reports menu active

$filterMethod = clean($_GET['method'] ?? '');
$perPage = 20;

$where = '1=1';
if ($filterMethod) $where .= " AND p.payment_method = '" . $conn->real_escape_string($filterMethod) . "'";

$totalRows = $conn->query("SELECT COUNT(*) AS c FROM payments p WHERE $where")->fetch_assoc()['c'];
$totals = $conn->query("SELECT SUM(amount) AS total_amt FROM payments p WHERE $where")->fetch_assoc();
$pagination = paginate($totalRows, $perPage);

$stmt = $conn->prepare("
    SELECT p.*, i.invoice_ref, i.customer_name, r.receipt_ref 
    FROM payments p 
    LEFT JOIN invoices i ON i.id = p.invoice_id 
    LEFT JOIN receipts r ON r.payment_id = p.id 
    WHERE $where 
    ORDER BY p.payment_date DESC 
    LIMIT ? OFFSET ?
");
$stmt->bind_param('ii', $perPage, $pagination['offset']);
$stmt->execute();
$payments = $stmt->get_result();

include 'includes/admin-header.php';
?>
<div class="dash-stats" style="margin-bottom:1.5rem;">
    <div class="dash-card"><div class="ic-wrap green">💳</div><div><h3><?php echo formatPrice($totals['total_amt'] ?? 0); ?></h3><span>Filtered Payments</span></div></div>
    <div class="dash-card"><div class="ic-wrap navy">📋</div><div><h3><?php echo (int)$totalRows; ?></h3><span>Total Records</span></div></div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>Payments Received</h2>
        <div style="display:flex;gap:8px;">
            <form method="GET" style="display:flex;gap:8px;">
                <select name="method" class="form-control" style="width:auto;" onchange="this.form.submit()">
                    <option value="">All Methods</option>
                    <option value="Cash" <?php echo $filterMethod==='Cash'?'selected':''; ?>>Cash</option>
                    <option value="Bank Transfer" <?php echo $filterMethod==='Bank Transfer'?'selected':''; ?>>Bank Transfer</option>
                    <option value="Mobile Money" <?php echo $filterMethod==='Mobile Money'?'selected':''; ?>>Mobile Money</option>
                    <option value="Cheque" <?php echo $filterMethod==='Cheque'?'selected':''; ?>>Cheque</option>
                    <option value="Other" <?php echo $filterMethod==='Other'?'selected':''; ?>>Other</option>
                </select>
            </form>
            <a href="add-payment.php" class="btn btn-primary btn-sm">+ Record Payment</a>
        </div>
    </div>
    <div class="panel-body table-wrap">
        <table class="data-table">
            <thead><tr><th>Date</th><th>Invoice</th><th>Customer</th><th>Method</th><th>Reference</th><th>Receipt</th><th>Amount</th></tr></thead>
            <tbody>
                <?php if ($payments && $payments->num_rows > 0): while ($p = $payments->fetch_assoc()): ?>
                <tr>
                    <td><?php echo date('M j, Y', strtotime($p['payment_date'])); ?></td>
                    <td><a href="view-invoice.php?id=<?php echo $p['invoice_id']; ?>"><strong><?php echo clean($p['invoice_ref'] ?? 'N/A'); ?></strong></a></td>
                    <td><?php echo clean($p['customer_name'] ?? 'N/A'); ?></td>
                    <td><span class="status-badge status-pending"><?php echo clean($p['payment_method']); ?></span></td>
                    <td><?php echo clean($p['reference'] ?? '—'); ?></td>
                    <td><?php echo $p['receipt_ref'] ? '<strong>'.clean($p['receipt_ref']).'</strong>' : '—'; ?></td>
                    <td style="color:var(--success); font-weight:bold;">+<?php echo formatPrice($p['amount']); ?></td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="7" class="empty-state">No payments found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo renderPagination($pagination, 'payments.php', ['method' => $filterMethod]); ?>
</div>
<?php include 'includes/admin-footer.php'; ?>
