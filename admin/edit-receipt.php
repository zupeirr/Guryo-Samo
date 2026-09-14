<?php
/**
 * admin/edit-receipt.php
 * ---------------------------------------------------------------
 * Form to edit an existing receipt.
 */

require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) redirect('reports.php#receipts');

$stmt = $conn->prepare("SELECT * FROM receipts WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$rec = $stmt->get_result()->fetch_assoc();
if (!$rec) redirect('reports.php#receipts');

$pageTitle = 'Edit Receipt';
$activeAdminPage = 'reports';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = "Invalid form submission.";
    } else {
        $issued_to = clean($_POST['issued_to'] ?? '');
        $amount = (float)$_POST['amount'];
        $issued_at = clean($_POST['issued_at']);

        if (empty($issued_to) || $amount <= 0 || empty($issued_at)) {
            $error = "Issued To, Amount, and Date are required.";
        } else {
            $upd = $conn->prepare("UPDATE receipts SET issued_to = ?, amount = ?, issued_at = ? WHERE id = ?");
            $upd->bind_param('sdsi', $issued_to, $amount, $issued_at, $id);
            if ($upd->execute()) {
                redirect('view-receipt.php?id=' . $id . '&msg=updated');
            } else {
                $error = "Failed to update receipt.";
            }
        }
    }
}

include 'includes/admin-header.php';
?>
<div class="panel">
    <div class="panel-head">
        <h2>Edit Receipt #<?php echo clean($rec['receipt_ref']); ?></h2>
        <a href="view-receipt.php?id=<?php echo $id; ?>" class="btn btn-outline btn-sm">← Back</a>
    </div>
    <div class="panel-body">
        <?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
        
        <form method="POST" action="" class="auth-form" style="max-width: 600px; padding:0;">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            
            <div class="form-group">
                <label>Issued To *</label>
                <input type="text" name="issued_to" class="form-control" value="<?php echo clean($rec['issued_to']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Amount *</label>
                <input type="number" step="0.01" name="amount" class="form-control" value="<?php echo $rec['amount']; ?>" required>
            </div>

            <div class="form-group">
                <label>Issue Date *</label>
                <input type="date" name="issued_at" class="form-control" value="<?php echo date('Y-m-d', strtotime($rec['issued_at'])); ?>" required>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Save Changes</button>
        </form>
    </div>
</div>
<?php include 'includes/admin-footer.php'; ?>
