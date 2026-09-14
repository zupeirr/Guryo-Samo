<?php
/**
 * admin/add-project.php
 * ---------------------------------------------------------------
 * Form to create a new development project.
 */

require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle = 'Add Project';
$activeAdminPage = 'reports';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = "Invalid form submission.";
    } else {
        $name = clean($_POST['name'] ?? '');
        $description = clean($_POST['description'] ?? '');
        $total_investment = (float)$_POST['total_investment'];
        $status = clean($_POST['status'] ?? 'Planned');
        $start_date = !empty($_POST['start_date']) ? clean($_POST['start_date']) : null;
        $end_date = !empty($_POST['end_date']) ? clean($_POST['end_date']) : null;

        if (empty($name)) {
            $error = "Project Name is required.";
        } else {
            $stmt = $conn->prepare("INSERT INTO projects (name, description, total_investment, status, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ssdsss', $name, $description, $total_investment, $status, $start_date, $end_date);
            if ($stmt->execute()) {
                redirect('projects.php?msg=added');
            } else {
                $error = "Failed to create project.";
            }
        }
    }
}

include 'includes/admin-header.php';
?>
<div class="panel">
    <div class="panel-head">
        <h2>Create New Project</h2>
        <a href="projects.php" class="btn btn-outline btn-sm">← Back to Projects</a>
    </div>
    <div class="panel-body">
        <?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
        
        <form method="POST" action="" class="auth-form" style="max-width: 600px; padding:0;">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            
            <div class="form-group">
                <label>Project Name *</label>
                <input type="text" name="name" class="form-control" placeholder="e.g., Downtown Apartments Phase 2" required>
            </div>
            
            <div class="form-group">
                <label>Initial Investment ($)</label>
                <input type="number" step="0.01" name="total_investment" class="form-control" value="0.00">
            </div>

            <div class="details-grid" style="grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:15px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label>Start Date</label>
                    <input type="date" name="start_date" class="form-control">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Target End Date</label>
                    <input type="date" name="end_date" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="Planned">Planned</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Brief details about the development project..."></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Create Project</button>
        </form>
    </div>
</div>
<?php include 'includes/admin-footer.php'; ?>
