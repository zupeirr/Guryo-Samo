<?php
/**
 * admin/edit-invoice.php
 * ---------------------------------------------------------------
 * Form to edit an existing invoice.
 */

require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) redirect('invoices.php');

$stmt = $conn->prepare("SELECT * FROM invoices WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$inv = $stmt->get_result()->fetch_assoc();
if (!$inv) redirect('invoices.php');

$pageTitle = 'Edit Invoice';
$activeAdminPage = 'reports';
$error = '';

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

        if (empty($customer_name) || $amount <= 0 || empty($issue_date) || empty($due_date)) {
            $error = "Customer Name, Amount, Issue Date, and Due Date are required.";
        } else {
            $upd = $conn->prepare("UPDATE invoices SET customer_name = ?, property_id = ?, amount = ?, status = ?, issue_date = ?, due_date = ?, notes = ? WHERE id = ?");
            $upd->bind_param('sidssssi', $customer_name, $property_id, $amount, $status, $issue_date, $due_date, $notes, $id);
            if ($upd->execute()) {
                redirect('view-invoice.php?id=' . $id . '&msg=updated');
            } else {
                $error = "Failed to update invoice.";
            }
        }
    }
}

$properties = $conn->query("SELECT id, title FROM properties ORDER BY title");

include 'includes/admin-header.php';
?>
<div class="panel">
    <div class="panel-head">
        <h2>Edit Invoice #<?php echo clean($inv['invoice_ref']); ?></h2>
        <a href="invoices.php" class="btn btn-outline btn-sm">← Back</a>
    </div>
    <div class="panel-body">
        <?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
        
        <form method="POST" action="" class="auth-form" style="max-width: 600px; padding:0;">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            
            <div class="form-group">
                <label>Customer Name *</label>
                <input type="text" name="customer_name" class="form-control" value="<?php echo clean($inv['customer_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Linked Property</label>
                <select name="property_id" class="form-control">
                    <option value="">None / General</option>
                    <?php while($p = $properties->fetch_assoc()): ?>
                    <option value="<?php echo $p['id']; ?>" <?php echo $inv['property_id']==$p['id']?'selected':''; ?>><?php echo clean($p['title']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Amount *</label>
                <input type="number" step="0.01" name="amount" class="form-control" value="<?php echo $inv['amount']; ?>" required>
            </div>

            <div class="details-grid" style="grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:15px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label>Issue Date *</label>
                    <input type="date" name="issue_date" class="form-control" value="<?php echo clean($inv['issue_date']); ?>" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Due Date *</label>
                    <input type="date" name="due_date" class="form-control" value="<?php echo clean($inv['due_date']); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="Unpaid" <?php echo $inv['status']==='Unpaid'?'selected':''; ?>>Unpaid</option>
                    <option value="Paid" <?php echo $inv['status']==='Paid'?'selected':''; ?>>Paid</option>
                    <option value="Partially Paid" <?php echo $inv['status']==='Partially Paid'?'selected':''; ?>>Partially Paid</option>
                    <option value="Overdue" <?php echo $inv['status']==='Overdue'?'selected':''; ?>>Overdue</option>
                    <option value="Cancelled" <?php echo $inv['status']==='Cancelled'?'selected':''; ?>>Cancelled</option>
                </select>
            </div>

            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" class="form-control" rows="3"><?php echo clean($inv['notes']); ?></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Save Changes</button>
        </form>
    </div>
</div>
<?php include 'includes/admin-footer.php'; ?>
