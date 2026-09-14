<?php
/**
 * admin/expenses.php
 * ---------------------------------------------------------------
 * Lists all expenses with filters for Category, Scope, and Status.
 */

require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle = 'Expenses';
$activeAdminPage = 'reports'; // Keep reports menu active

$filterCategory = (int)($_GET['category'] ?? 0);
$filterScope    = clean($_GET['scope'] ?? '');
$filterStatus   = clean($_GET['status'] ?? '');
$perPage = 20;

$where = '1=1';
if ($filterCategory > 0) $where .= " AND e.category_id = $filterCategory";
if ($filterScope) $where .= " AND e.scope = '" . $conn->real_escape_string($filterScope) . "'";
if ($filterStatus) $where .= " AND e.status = '" . $conn->real_escape_string($filterStatus) . "'";

$totalRows = $conn->query("SELECT COUNT(*) AS c FROM expenses e WHERE $where")->fetch_assoc()['c'];
$totals = $conn->query("SELECT SUM(amount) AS total_amt FROM expenses e WHERE $where AND e.status != 'Cancelled'")->fetch_assoc();
$pagination = paginate($totalRows, $perPage);

$stmt = $conn->prepare("
    SELECT e.*, c.name AS category_name, p.title AS property_title, pr.name AS project_name, cp.name AS construction_project_name 
    FROM expenses e 
    JOIN expense_categories c ON c.id = e.category_id 
    LEFT JOIN properties p ON p.id = e.property_id 
    LEFT JOIN projects pr ON pr.id = e.project_id 
    LEFT JOIN construction_projects cp ON cp.id = e.construction_project_id 
    WHERE $where 
    ORDER BY e.expense_date DESC 
    LIMIT ? OFFSET ?
");
$stmt->bind_param('ii', $perPage, $pagination['offset']);
$stmt->execute();
$expenses = $stmt->get_result();

$categories = $conn->query("SELECT * FROM expense_categories ORDER BY name");

include 'includes/admin-header.php';
?>
<div class="dash-stats" style="margin-bottom:1.5rem;">
    <div class="dash-card"><div class="ic-wrap red">💸</div><div><h3><?php echo formatPrice($totals['total_amt'] ?? 0); ?></h3><span>Filtered Expenses</span></div></div>
    <div class="dash-card"><div class="ic-wrap navy">📋</div><div><h3><?php echo (int)$totalRows; ?></h3><span>Total Records</span></div></div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>Expenses Management</h2>
        <div style="display:flex;gap:8px;">
            <form method="GET" style="display:flex;gap:8px;">
                <select name="category" class="form-control" style="width:auto;" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php while($c = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo $filterCategory==$c['id']?'selected':''; ?>><?php echo clean($c['name']); ?></option>
                    <?php endwhile; ?>
                </select>
                <select name="scope" class="form-control" style="width:auto;" onchange="this.form.submit()">
                    <option value="">All Scopes</option>
                    <option value="Company" <?php echo $filterScope==='Company'?'selected':''; ?>>Company</option>
                    <option value="Property" <?php echo $filterScope==='Property'?'selected':''; ?>>Property</option>
                    <option value="Project" <?php echo $filterScope==='Project'?'selected':''; ?>>Investment Project</option>
                    <option value="Construction" <?php echo $filterScope==='Construction'?'selected':''; ?>>Construction Project</option>
                </select>
                <select name="status" class="form-control" style="width:auto;" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="Paid" <?php echo $filterStatus==='Paid'?'selected':''; ?>>Paid</option>
                    <option value="Pending" <?php echo $filterStatus==='Pending'?'selected':''; ?>>Pending</option>
                    <option value="Cancelled" <?php echo $filterStatus==='Cancelled'?'selected':''; ?>>Cancelled</option>
                </select>
            </form>
            <a href="add-expense.php" class="btn btn-primary btn-sm">+ Add Expense</a>
        </div>
    </div>
    <div class="panel-body table-wrap">
        <table class="data-table">
            <thead><tr><th>Date</th><th>Category</th><th>Scope</th><th>Description</th><th>Amount</th><th>Status</th></tr></thead>
            <tbody>
                <?php if ($expenses && $expenses->num_rows > 0): while ($e = $expenses->fetch_assoc()): ?>
                <tr>
                    <td><?php echo date('M j, Y', strtotime($e['expense_date'])); ?></td>
                    <td><?php echo clean($e['category_name']); ?></td>
                    <td>
                        <span class="status-badge status-pending"><?php echo clean($e['scope']); ?></span><br>
                        <small>
                            <?php 
                            if ($e['scope'] === 'Property') echo clean($e['property_title'] ?? 'N/A');
                            elseif ($e['scope'] === 'Project') echo clean($e['project_name'] ?? 'N/A');
                            elseif ($e['scope'] === 'Construction') echo clean($e['construction_project_name'] ?? 'N/A');
                            ?>
                        </small>
                    </td>
                    <td><?php echo clean($e['description'] ?? '—'); ?></td>
                    <td style="color:var(--danger); font-weight:bold;">-<?php echo formatPrice($e['amount']); ?></td>
                    <td>
                        <?php $sc = $e['status']==='Paid'?'status-sold':($e['status']==='Pending'?'status-pending':'status-for-rent'); ?>
                        <span class="status-badge <?php echo $sc; ?>"><?php echo clean($e['status']); ?></span>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="6" class="empty-state">No expenses found matching the criteria.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo renderPagination($pagination, 'expenses.php', ['category' => $filterCategory, 'scope' => $filterScope, 'status' => $filterStatus]); ?>
</div>
<?php include 'includes/admin-footer.php'; ?>
