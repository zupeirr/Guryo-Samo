<?php
/**
 * admin/view-invoice.php
 * ---------------------------------------------------------------
 * Details view for a specific invoice, including its payments.
 */

require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) redirect('invoices.php');

$stmt = $conn->prepare("
    SELECT i.*, p.title AS property_title 
    FROM invoices i 
    LEFT JOIN properties p ON p.id = i.property_id 
    WHERE i.id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$inv = $stmt->get_result()->fetch_assoc();

if (!$inv) redirect('invoices.php');

// Fetch payments for this invoice
$stmt2 = $conn->prepare("SELECT p.*, r.receipt_ref FROM payments p LEFT JOIN receipts r ON r.payment_id = p.id WHERE p.invoice_id = ? ORDER BY p.payment_date DESC");
$stmt2->bind_param('i', $id);
$stmt2->execute();
$payments = $stmt2->get_result();

$pageTitle = 'Invoice Details';
$activeAdminPage = 'reports';
$sc = ['Paid'=>'status-sold','Partially Paid'=>'status-pending','Unpaid'=>'status-pending','Overdue'=>'status-for-rent','Cancelled'=>'status-pending'];

include 'includes/admin-header.php';
?>
<div class="panel">
    <div class="panel-head">
        <h2>Invoice #<?php echo clean($inv['invoice_ref']); ?></h2>
        <div style="display:flex;gap:8px;">
            <a href="invoices.php" class="btn btn-outline btn-sm">← Back</a>
            <button class="btn btn-outline btn-sm no-print" onclick="window.print()">🖨️ Print</button>
            <?php if ($inv['status'] !== 'Paid' && $inv['status'] !== 'Cancelled'): ?>
            <a href="add-payment.php?invoice_id=<?php echo $id; ?>" class="btn btn-primary btn-sm">💳 Add Payment</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="panel-body">
        
        <div class="details-grid" style="grid-template-columns: 1fr 1fr; gap:20px; margin-bottom: 20px;">
            <div>
                <p><strong>Customer:</strong> <?php echo clean($inv['customer_name'] ?? 'N/A'); ?></p>
                <p><strong>Property:</strong> <?php echo clean($inv['property_title'] ?? '—'); ?></p>
                <p><strong>Status:</strong> <span class="status-badge <?php echo $sc[$inv['status']]??'status-pending'; ?>"><?php echo clean($inv['status']); ?></span></p>
            </div>
            <div style="text-align:right;">
                <p><strong>Issue Date:</strong> <?php echo date('M j, Y', strtotime($inv['issue_date'])); ?></p>
                <p><strong>Due Date:</strong> <span style="<?php echo strtotime($inv['due_date']) < time() && $inv['status']!=='Paid' ? 'color:var(--danger);font-weight:bold;' : ''; ?>"><?php echo date('M j, Y', strtotime($inv['due_date'])); ?></span></p>
            </div>
        </div>

        <div style="background:var(--bg-card); border:1px solid var(--border); padding:20px; border-radius:8px; margin-bottom:20px;">
            <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid var(--border);">
                <span style="font-size:1.1rem;">Invoice Amount</span>
                <strong style="font-size:1.2rem;"><?php echo formatPrice($inv['amount']); ?></strong>
            </div>
            <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid var(--border);">
                <span>Amount Paid</span>
                <strong style="color:var(--success);"><?php echo formatPrice($inv['paid_amount']); ?></strong>
            </div>
            <div style="display:flex; justify-content:space-between; padding:10px 0;">
                <span style="font-size:1.1rem; font-weight:bold;">Remaining Balance</span>
                <strong style="font-size:1.2rem; color:var(--danger);"><?php echo formatPrice($inv['amount'] - $inv['paid_amount']); ?></strong>
            </div>
        </div>
        
        <?php if (!empty($inv['notes'])): ?>
        <div style="margin-bottom:20px;">
            <h4>Notes</h4>
            <p><?php echo nl2br(clean($inv['notes'])); ?></p>
        </div>
        <?php endif; ?>
        
        <h3 style="margin-top:30px; margin-bottom:15px; font-size:1.2rem; border-bottom:1px solid var(--border); padding-bottom:10px;">Payment History</h3>
        <table class="data-table">
            <thead><tr><th>Date</th><th>Method</th><th>Reference</th><th>Receipt</th><th>Amount</th></tr></thead>
            <tbody>
                <?php if ($payments && $payments->num_rows > 0): while ($p = $payments->fetch_assoc()): ?>
                <tr>
                    <td><?php echo date('M j, Y', strtotime($p['payment_date'])); ?></td>
                    <td><?php echo clean($p['payment_method']); ?></td>
                    <td><?php echo clean($p['reference'] ?? '—'); ?></td>
                    <td><?php echo $p['receipt_ref'] ? '<strong>'.clean($p['receipt_ref']).'</strong>' : '—'; ?></td>
                    <td style="color:var(--success); font-weight:bold;"><?php echo formatPrice($p['amount']); ?></td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="5" class="empty-state">No payments recorded for this invoice yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'includes/admin-footer.php'; ?>
