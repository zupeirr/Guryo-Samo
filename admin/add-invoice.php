<?php
/**
 * admin/add-invoice.php
 * ---------------------------------------------------------------
 * Form to issue a new invoice.
 */

require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle = 'Add Invoice';
$activeAdminPage = 'reports';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = "Invalid form submission.";
    } else {
        $customer_name = clean($_POST['customer_name'] ?? '');
        $property_id = !empty($_POST['property_id']) ? (int)$_POST['property_id'] : null;
        $amount = (float)$_POST['amount'];
        $status = clean($_POST['status'] ?? 'Unpaid');
        $issue_date = clean($_POST['issue_date']);
        $due_date = clean($_POST['due_date']);
        $notes = clean($_POST['notes'] ?? '');
        
        $invoice_ref = 'INV-' . date('Ymd') . '-' . mt_rand(1000, 9999);

        if (empty($customer_name) || $amount <= 0 || empty($issue_date) || empty($due_date)) {
            $error = "Customer Name, Amount, Issue Date, and Due Date are required.";
        } else {
            // For simplicity, we are just storing the customer name instead of linking to users table.
            $stmt = $conn->prepare("INSERT INTO invoices (invoice_ref, customer_name, property_id, amount, paid_amount, status, issue_date, due_date, notes) VALUES (?, ?, ?, ?, 0, ?, ?, ?, ?)");
            $stmt->bind_param('ssidssss', $invoice_ref, $customer_name, $property_id, $amount, $status, $issue_date, $due_date, $notes);
            if ($stmt->execute()) {
                redirect('invoices.php?msg=added');
            } else {
                $error = "Failed to create invoice.";
            }
        }
    }
}

$properties = $conn->query("SELECT id, title FROM properties ORDER BY title");

include 'includes/admin-header.php';
?>
<div class="panel">
    <div class="panel-head">
        <h2>Issue New Invoice</h2>
        <a href="invoices.php" class="btn btn-outline btn-sm">← Back to Invoices</a>
    </div>
    <div class="panel-body">
        <?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
        
        <form method="POST" action="" class="auth-form" style="max-width: 600px; padding:0;">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            
            <div class="form-group">
                <label>Customer Name *</label>
                <input type="text" name="customer_name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Linked Property</label>
                <select name="property_id" class="form-control">
                    <option value="">None / General</option>
                    <?php while($p = $properties->fetch_assoc()): ?>
                    <option value="<?php echo $p['id']; ?>"><?php echo clean($p['title']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Amount *</label>
                <input type="number" step="0.01" name="amount" class="form-control" required>
            </div>

            <div class="details-grid" style="grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:15px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label>Issue Date *</label>
                    <input type="date" name="issue_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Due Date *</label>
                    <input type="date" name="due_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="Unpaid">Unpaid</option>
                    <option value="Paid">Paid</option>
                    <option value="Overdue">Overdue</option>
                </select>
            </div>

            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes for the invoice..."></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Issue Invoice</button>
        </form>
    </div>
</div>
<?php include 'includes/admin-footer.php'; ?>
