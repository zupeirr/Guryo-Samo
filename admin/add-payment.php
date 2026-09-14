<?php
/**
 * admin/add-payment.php
 * ---------------------------------------------------------------
 * Form to record a payment and auto-generate a receipt.
 */

require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle = 'Record Payment';
$activeAdminPage = 'reports';
$error = '';

$invoice_id = (int)($_GET['invoice_id'] ?? 0);
$prefill = null;

if ($invoice_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM invoices WHERE id = ?");
    $stmt->bind_param('i', $invoice_id);
    $stmt->execute();
    $prefill = $stmt->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = "Invalid form submission.";
    } else {
        $inv_id = (int)$_POST['invoice_id'];
        $amount = (float)$_POST['amount'];
        $payment_method = clean($_POST['payment_method'] ?? 'Cash');
        $payment_date = clean($_POST['payment_date']);
        $reference = clean($_POST['reference'] ?? '');
        $notes = clean($_POST['notes'] ?? '');

        if ($inv_id <= 0 || $amount <= 0 || empty($payment_date)) {
            $error = "Invoice, Amount, and Date are required.";
        } else {
            // Check invoice
            $chk = $conn->prepare("SELECT * FROM invoices WHERE id = ?");
            $chk->bind_param('i', $inv_id);
            $chk->execute();
            $inv = $chk->get_result()->fetch_assoc();

            if (!$inv) {
                $error = "Invoice not found.";
            } else {
                $conn->begin_transaction();
                try {
                    // 1. Insert Payment
                    $insPay = $conn->prepare("INSERT INTO payments (invoice_id, amount, payment_method, reference, payment_date, notes) VALUES (?, ?, ?, ?, ?, ?)");
                    $insPay->bind_param('idssss', $inv_id, $amount, $payment_method, $reference, $payment_date, $notes);
                    $insPay->execute();
                    $payment_id = $conn->insert_id;

                    // 2. Generate Receipt
                    $receipt_ref = 'REC-' . date('Ymd') . '-' . mt_rand(1000, 9999);
                    $customer_name = $inv['customer_name'] ?? 'Unknown';
                    $insRec = $conn->prepare("INSERT INTO receipts (receipt_ref, payment_id, issued_to, amount) VALUES (?, ?, ?, ?)");
                    $insRec->bind_param('sisd', $receipt_ref, $payment_id, $customer_name, $amount);
                    $insRec->execute();

                    // 3. Update Invoice
                    $new_paid = (float)$inv['paid_amount'] + $amount;
                    $total_amt = (float)$inv['amount'];
                    
                    $new_status = 'Unpaid';
                    if ($new_paid >= $total_amt) {
                        $new_status = 'Paid';
                    } elseif ($new_paid > 0) {
                        $new_status = 'Partially Paid';
                    }

                    $updInv = $conn->prepare("UPDATE invoices SET paid_amount = ?, status = ? WHERE id = ?");
                    $updInv->bind_param('dsi', $new_paid, $new_status, $inv_id);
                    $updInv->execute();

                    $conn->commit();
                    redirect('view-invoice.php?id=' . $inv_id . '&msg=payment_added');

                } catch (Exception $e) {
                    $conn->rollback();
                    $error = "Failed to record payment: " . $e->getMessage();
                }
            }
        }
    }
}

$invoices = $conn->query("SELECT id, invoice_ref, customer_name, amount, paid_amount FROM invoices WHERE status != 'Paid' AND status != 'Cancelled' ORDER BY created_at DESC");

include 'includes/admin-header.php';
?>
<div class="panel">
    <div class="panel-head">
        <h2>Record Payment</h2>
        <a href="<?php echo $prefill ? 'view-invoice.php?id='.$invoice_id : 'invoices.php'; ?>" class="btn btn-outline btn-sm">← Back</a>
    </div>
    <div class="panel-body">
        <?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
        
        <form method="POST" action="" class="auth-form" style="max-width: 600px; padding:0;">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            
            <div class="form-group">
                <label>Select Invoice *</label>
                <?php if ($prefill): ?>
                    <input type="hidden" name="invoice_id" value="<?php echo $prefill['id']; ?>">
                    <input type="text" class="form-control" value="<?php echo clean($prefill['invoice_ref'] . ' - ' . ($prefill['customer_name']??'') . ' (Balance: ' . formatPrice($prefill['amount'] - $prefill['paid_amount']) . ')'); ?>" disabled>
                <?php else: ?>
                    <select name="invoice_id" class="form-control" required>
                        <option value="">Choose an unpaid invoice...</option>
                        <?php while($inv = $invoices->fetch_assoc()): 
                            $balance = $inv['amount'] - $inv['paid_amount'];
                        ?>
                        <option value="<?php echo $inv['id']; ?>">
                            <?php echo clean($inv['invoice_ref']); ?> — <?php echo clean($inv['customer_name']); ?> (Balance: <?php echo formatPrice($balance); ?>)
                        </option>
                        <?php endwhile; ?>
                    </select>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Payment Amount ($) *</label>
                <input type="number" step="0.01" name="amount" class="form-control" value="<?php echo $prefill ? ($prefill['amount'] - $prefill['paid_amount']) : ''; ?>" required>
            </div>

            <div class="details-grid" style="grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:15px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label>Payment Method *</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="Cash">Cash</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Mobile Money">Mobile Money (Zaad, eDahab)</option>
                        <option value="Cheque">Cheque</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Date Received *</label>
                    <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Transaction Reference #</label>
                <input type="text" name="reference" class="form-control" placeholder="e.g. TXN-998822 (if applicable)">
            </div>

            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Record Payment & Generate Receipt</button>
        </form>
    </div>
</div>
<?php include 'includes/admin-footer.php'; ?>
