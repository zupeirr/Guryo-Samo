<?php
/**
 * admin/add-expense.php
 * ---------------------------------------------------------------
 * Form to log a new expense.
 */

require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle = 'Add Expense';
$activeAdminPage = 'reports';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = "Invalid form submission.";
    } else {
        $category_id = (int)$_POST['category_id'];
        $amount = (float)$_POST['amount'];
        $description = clean($_POST['description'] ?? '');
        $scope = clean($_POST['scope'] ?? 'Company');
        $expense_date = clean($_POST['expense_date']);
        $status = clean($_POST['status'] ?? 'Paid');
        
        $property_id = !empty($_POST['property_id']) ? (int)$_POST['property_id'] : null;
        $project_id = !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null;
        $construction_project_id = !empty($_POST['construction_project_id']) ? (int)$_POST['construction_project_id'] : null;

        if ($scope !== 'Property') $property_id = null;
        if ($scope !== 'Project') $project_id = null;
        if ($scope !== 'Construction') $construction_project_id = null;

        if ($category_id <= 0 || $amount <= 0 || empty($expense_date)) {
            $error = "Category, Amount, and Date are required.";
        } else {
            $stmt = $conn->prepare("INSERT INTO expenses (category_id, description, amount, scope, property_id, project_id, construction_project_id, status, expense_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('isdsiiiss', $category_id, $description, $amount, $scope, $property_id, $project_id, $construction_project_id, $status, $expense_date);
            if ($stmt->execute()) {
                redirect('expenses.php?msg=added');
            } else {
                $error = "Failed to log expense.";
            }
        }
    }
}

$categories = $conn->query("SELECT * FROM expense_categories ORDER BY name");
$properties = $conn->query("SELECT id, title FROM properties ORDER BY title");
$projects = $conn->query("SELECT id, name FROM projects ORDER BY name");
$construction_projects = $conn->query("SELECT id, name FROM construction_projects ORDER BY name");

include 'includes/admin-header.php';
?>
<div class="panel">
    <div class="panel-head">
        <h2>Log New Expense</h2>
        <a href="expenses.php" class="btn btn-outline btn-sm">← Back to Expenses</a>
    </div>
    <div class="panel-body">
        <?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
        
        <form method="POST" action="" class="auth-form" style="max-width: 600px; padding:0;">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            
            <div class="form-group">
                <label>Category *</label>
                <select name="category_id" class="form-control" required>
                    <option value="">Select Category...</option>
                    <?php while($c = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $c['id']; ?>"><?php echo clean($c['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Amount *</label>
                <input type="number" step="0.01" name="amount" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Date *</label>
                <input type="date" name="expense_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label>Scope *</label>
                <select name="scope" id="scopeSelect" class="form-control" required>
                    <option value="Company">Company / General</option>
                    <option value="Property">Specific Property</option>
                    <option value="Project">Investment Project</option>
                    <option value="Construction">Construction Project</option>
                </select>
            </div>
            
            <div class="form-group" id="propertyWrap" style="display:none;">
                <label>Linked Property</label>
                <select name="property_id" class="form-control">
                    <option value="">Select Property...</option>
                    <?php while($p = $properties->fetch_assoc()): ?>
                    <option value="<?php echo $p['id']; ?>"><?php echo clean($p['title']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group" id="projectWrap" style="display:none;">
                <label>Linked Investment Project</label>
                <select name="project_id" class="form-control">
                    <option value="">Select Project...</option>
                    <?php while($p = $projects->fetch_assoc()): ?>
                    <option value="<?php echo $p['id']; ?>"><?php echo clean($p['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group" id="constProjectWrap" style="display:none;">
                <label>Linked Construction Project</label>
                <select name="construction_project_id" class="form-control">
                    <option value="">Select Construction Project...</option>
                    <?php while($p = $construction_projects->fetch_assoc()): ?>
                    <option value="<?php echo $p['id']; ?>"><?php echo clean($p['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="Paid">Paid</option>
                    <option value="Pending">Pending</option>
                </select>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Log Expense</button>
        </form>
    </div>
</div>

<script>
document.getElementById('scopeSelect').addEventListener('change', function(){
    const val = this.value;
    document.getElementById('propertyWrap').style.display = val === 'Property' ? 'block' : 'none';
    document.getElementById('projectWrap').style.display = val === 'Project' ? 'block' : 'none';
    document.getElementById('constProjectWrap').style.display = val === 'Construction' ? 'block' : 'none';
});
</script>

<?php include 'includes/admin-footer.php'; ?>
