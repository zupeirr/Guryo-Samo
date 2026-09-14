<?php
/**
 * admin/view-receipt.php
 * ---------------------------------------------------------------
 * Detailed view of a receipt.
 */

require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) redirect('reports.php#receipts');

$stmt = $conn->prepare("
    SELECT r.*, p.amount AS payment_amount, p.payment_method, p.reference, p.payment_date, i.invoice_ref 
    FROM receipts r 
    LEFT JOIN payments p ON p.id = r.payment_id 
    LEFT JOIN invoices i ON i.id = p.invoice_id
    WHERE r.id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$rec = $stmt->get_result()->fetch_assoc();

if (!$rec) redirect('reports.php#receipts');

$pageTitle = 'Receipt Details';
$activeAdminPage = 'reports';

include 'includes/admin-header.php';
?>
<div class="panel">
    <div class="panel-head">
        <h2>Receipt #<?php echo clean($rec['receipt_ref']); ?></h2>
        <div style="display:flex;gap:8px;">
            <a href="reports.php#receipts" class="btn btn-outline btn-sm">← Back</a>
            <button class="btn btn-outline btn-sm no-print" onclick="window.print()">🖨️ Print</button>
            <a href="edit-receipt.php?id=<?php echo $id; ?>" class="btn btn-primary btn-sm no-print">✏️ Edit</a>
        </div>
    </div>
    <div class="panel-body">
        <div style="max-width: 600px; margin: 0 auto; border: 1px solid var(--border); padding: 30px; border-radius: 8px;">
            <h1 style="text-align:center; font-size:2rem; margin-bottom: 5px;">RECEIPT</h1>
            <p style="text-align:center; color:var(--text-light); margin-bottom: 30px;">#<?php echo clean($rec['receipt_ref']); ?></p>

            <div style="display:flex; justify-content:space-between; margin-bottom: 20px;">
                <div>
                    <p><strong>Issued To:</strong><br><?php echo clean($rec['issued_to'] ?? 'N/A'); ?></p>
                </div>
                <div style="text-align:right;">
                    <p><strong>Date:</strong><br><?php echo date('F j, Y', strtotime($rec['issued_at'])); ?></p>
                </div>
            </div>

            <table style="width:100%; border-collapse: collapse; margin-bottom: 20px;">
                <tr style="border-bottom: 2px solid var(--border);">
                    <th style="text-align:left; padding: 10px 0;">Description</th>
                    <th style="text-align:right; padding: 10px 0;">Amount</th>
                </tr>
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 15px 0;">
                        Payment for Invoice <strong><?php echo clean($rec['invoice_ref'] ?? 'N/A'); ?></strong><br>
                        <small style="color:var(--text-light);">Method: <?php echo clean($rec['payment_method'] ?? 'N/A'); ?> | Ref: <?php echo clean($rec['reference'] ?? 'N/A'); ?></small>
                    </td>
                    <td style="text-align:right; font-weight:bold; padding: 15px 0;">
                        <?php echo formatPrice($rec['amount']); ?>
                    </td>
                </tr>
            </table>

            <div style="text-align:right; font-size: 1.2rem; margin-bottom:30px;">
                <strong>Total Paid: <span style="color:var(--success);"><?php echo formatPrice($rec['amount']); ?></span></strong>
            </div>

            <div style="text-align:center; color:var(--text-light); font-size: 0.9rem;">
                <p>Thank you for your business!</p>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/admin-footer.php'; ?>
