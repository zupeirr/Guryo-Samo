<?php
/**
 * admin/add-budget.php
 * ---------------------------------------------------------------
 * Form to allocate a new budget.
 */

require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle = 'Add Budget';
$activeAdminPage = 'reports';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = "Invalid form submission.";
    } else {
        $department = clean($_POST['department'] ?? '');
        $allocated_amount = (float)$_POST['allocated_amount'];
        $period_type = clean($_POST['period_type'] ?? 'Quarterly');
        $period_label = clean($_POST['period_label'] ?? '');
        $project_id = !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null;

        if ($period_type !== 'Project') $project_id = null;

        if (empty($department) || $allocated_amount <= 0 || empty($period_label)) {
            $error = "Department, Allocated Amount, and Period Label are required.";
        } else {
            $stmt = $conn->prepare("INSERT INTO budgets (department, allocated_amount, spent_amount, period_type, period_label, project_id) VALUES (?, ?, 0, ?, ?, ?)");
            $stmt->bind_param('sdssi', $department, $allocated_amount, $period_type, $period_label, $project_id);
            if ($stmt->execute()) {
                redirect('budgets.php?msg=added');
            } else {
                $error = "Failed to allocate budget.";
            }
        }
    }
}

$projects = $conn->query("SELECT id, name FROM projects ORDER BY name");

include 'includes/admin-header.php';
?>
<div class="panel">
    <div class="panel-head">
        <h2>Allocate New Budget</h2>
        <a href="budgets.php" class="btn btn-outline btn-sm">← Back to Budgets</a>
    </div>
    <div class="panel-body">
        <?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
        
        <form method="POST" action="" class="auth-form" style="max-width: 600px; padding:0;">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            
            <div class="form-group">
                <label>Department / Category *</label>
                <input type="text" name="department" class="form-control" placeholder="e.g., Marketing, Property Maintenance" required>
            </div>
            
            <div class="form-group">
                <label>Allocated Amount *</label>
                <input type="number" step="0.01" name="allocated_amount" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Period Type *</label>
                <select name="period_type" id="periodType" class="form-control" required>
                    <option value="Annual">Annual</option>
                    <option value="Quarterly">Quarterly</option>
                    <option value="Project">Project Based</option>
                </select>
            </div>

            <div class="form-group">
                <label>Period Label *</label>
                <input type="text" name="period_label" class="form-control" placeholder="e.g., Q4 2026, FY 2026" required>
            </div>
            
            <div class="form-group" id="projectWrap" style="display:none;">
                <label>Linked Project</label>
                <select name="project_id" class="form-control">
                    <option value="">Select Project...</option>
                    <?php while($p = $projects->fetch_assoc()): ?>
                    <option value="<?php echo $p['id']; ?>"><?php echo clean($p['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100">Allocate Budget</button>
        </form>
    </div>
</div>

<script>
document.getElementById('periodType').addEventListener('change', function(){
    document.getElementById('projectWrap').style.display = this.value === 'Project' ? 'block' : 'none';
});
</script>

<?php include 'includes/admin-footer.php'; ?>
