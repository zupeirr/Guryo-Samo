<?php
/**
 * admin/invoices.php
 * ---------------------------------------------------------------
 * Lists all invoices with filters for Status.
 */

require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle = 'Invoices';
$activeAdminPage = 'reports'; // Keep reports menu active

$filterStatus = clean($_GET['status'] ?? '');
$perPage = 20;

$where = '1=1';
if ($filterStatus) $where .= " AND status = '" . $conn->real_escape_string($filterStatus) . "'";

$totalRows = $conn->query("SELECT COUNT(*) AS c FROM invoices WHERE $where")->fetch_assoc()['c'];
$totals = $conn->query("SELECT SUM(amount) AS total_amt, SUM(paid_amount) AS total_paid FROM invoices WHERE $where")->fetch_assoc();
$pagination = paginate($totalRows, $perPage);

$stmt = $conn->prepare("
    SELECT i.*, p.title AS property_title 
    FROM invoices i 
    LEFT JOIN properties p ON p.id = i.property_id 
    WHERE $where 
    ORDER BY created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->bind_param('ii', $perPage, $pagination['offset']);
$stmt->execute();
$invoices = $stmt->get_result();

include 'includes/admin-header.php';
?>
<div class="dash-stats" style="margin-bottom:1.5rem;">
    <div class="dash-card"><div class="ic-wrap navy"><i data-lucide="file-text" style="width:20px;height:20px;"></i></div><div><h3><?php echo (int)$totalRows; ?></h3><span>Total Invoices</span></div></div>
    <div class="dash-card"><div class="ic-wrap gold"><i data-lucide="banknote" style="width:20px;height:20px;"></i></div><div><h3><?php echo formatPrice($totals['total_amt'] ?? 0); ?></h3><span>Total Invoiced</span></div></div>
    <div class="dash-card"><div class="ic-wrap green"><i data-lucide="credit-card" style="width:20px;height:20px;"></i></div><div><h3><?php echo formatPrice($totals['total_paid'] ?? 0); ?></h3><span>Total Paid</span></div></div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>Invoices Management</h2>
        <div style="display:flex;gap:8px;">
            <form method="GET" style="display:flex;gap:8px;">
                <select name="status" class="form-control" style="width:auto;" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="Paid" <?php echo $filterStatus==='Paid'?'selected':''; ?>>Paid</option>
                    <option value="Partially Paid" <?php echo $filterStatus==='Partially Paid'?'selected':''; ?>>Partially Paid</option>
                    <option value="Unpaid" <?php echo $filterStatus==='Unpaid'?'selected':''; ?>>Unpaid</option>
                    <option value="Overdue" <?php echo $filterStatus==='Overdue'?'selected':''; ?>>Overdue</option>
                    <option value="Cancelled" <?php echo $filterStatus==='Cancelled'?'selected':''; ?>>Cancelled</option>
                </select>
            </form>
            <a href="add-invoice.php" class="btn btn-primary btn-sm">+ Issue Invoice</a>
        </div>
    </div>
    <div class="panel-body table-wrap">
        <table class="data-table">
            <thead><tr><th>Ref</th><th>Customer</th><th>Property</th><th>Amount</th><th>Paid</th><th>Status</th><th>Due Date</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if ($invoices && $invoices->num_rows > 0): while ($i = $invoices->fetch_assoc()): 
                    $sc = ['Paid'=>'status-sold','Partially Paid'=>'status-pending','Unpaid'=>'status-pending','Overdue'=>'status-for-rent','Cancelled'=>'status-pending'];
                ?>
                <tr>
                    <td><strong><?php echo clean($i['invoice_ref']); ?></strong></td>
                    <td><?php echo clean($i['customer_name'] ?? 'N/A'); ?></td>
                    <td><?php echo clean($i['property_title'] ?? '—'); ?></td>
                    <td><?php echo formatPrice($i['amount']); ?></td>
                    <td style="color:var(--success); font-weight:bold;"><?php echo formatPrice($i['paid_amount']); ?></td>
                    <td><span class="status-badge <?php echo $sc[$i['status']]??'status-pending'; ?>"><?php echo clean($i['status']); ?></span></td>
                    <td><?php echo date('M j, Y', strtotime($i['due_date'])); ?></td>
                    <td>
                        <div class="action-btns">
                            <a href="view-invoice.php?id=<?php echo $i['id']; ?>" class="btn-icon" title="View"><i data-lucide="eye" style="width:15px;height:15px;"></i></a>
                            <a href="edit-invoice.php?id=<?php echo $i['id']; ?>" class="btn-icon" title="Edit"><i data-lucide="pencil" style="width:15px;height:15px;"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="8" class="empty-state">No invoices found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo renderPagination($pagination, 'invoices.php', ['status' => $filterStatus]); ?>
</div>
<?php include 'includes/admin-footer.php'; ?>
