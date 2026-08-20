<?php
/**
 * admin/budgets.php
 * ---------------------------------------------------------------
 * Lists all budgets and tracks spent vs. allocated amounts.
 */

require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle = 'Budgets';
$activeAdminPage = 'reports'; // Keep reports menu active

$filterPeriod = clean($_GET['period'] ?? '');
$perPage = 20;

$where = '1=1';
if ($filterPeriod) $where .= " AND b.period_type = '" . $conn->real_escape_string($filterPeriod) . "'";

$totalRows = $conn->query("SELECT COUNT(*) AS c FROM budgets b WHERE $where")->fetch_assoc()['c'];
$pagination = paginate($totalRows, $perPage);

$stmt = $conn->prepare("
    SELECT b.*, p.name AS project_name 
    FROM budgets b 
    LEFT JOIN projects p ON p.id = b.project_id 
    WHERE $where 
    ORDER BY b.created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->bind_param('ii', $perPage, $pagination['offset']);
$stmt->execute();
$budgets = $stmt->get_result();

include 'includes/admin-header.php';
?>
<div class="dash-stats" style="margin-bottom:1.5rem;">
    <div class="dash-card"><div class="ic-wrap teal">💼</div><div><h3><?php echo (int)$totalRows; ?></h3><span>Total Budgets</span></div></div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>Budgets Management</h2>
        <div style="display:flex;gap:8px;">
            <form method="GET" style="display:flex;gap:8px;">
                <select name="period" class="form-control" style="width:auto;" onchange="this.form.submit()">
                    <option value="">All Period Types</option>
                    <option value="Annual" <?php echo $filterPeriod==='Annual'?'selected':''; ?>>Annual</option>
                    <option value="Quarterly" <?php echo $filterPeriod==='Quarterly'?'selected':''; ?>>Quarterly</option>
                    <option value="Project" <?php echo $filterPeriod==='Project'?'selected':''; ?>>Project</option>
                </select>
            </form>
            <a href="add-budget.php" class="btn btn-primary btn-sm">+ Add Budget</a>
        </div>
    </div>
    <div class="panel-body table-wrap">
        <table class="data-table">
            <thead><tr><th>Department</th><th>Type</th><th>Period</th><th>Allocated</th><th>Spent</th><th>Remaining</th><th>Usage</th></tr></thead>
            <tbody>
                <?php if ($budgets && $budgets->num_rows > 0): while ($b = $budgets->fetch_assoc()):
                    $remaining = $b['allocated_amount'] - $b['spent_amount'];
                    $pct = $b['allocated_amount'] > 0 ? min(100, round(($b['spent_amount']/$b['allocated_amount'])*100)) : 0;
                    $barColor = $pct >= 90 ? 'var(--danger)' : ($pct >= 70 ? 'var(--gold-500)' : 'var(--success)');
                ?>
                <tr>
                    <td>
                        <strong><?php echo clean($b['department']); ?></strong>
                        <?php if($b['period_type']==='Project') echo '<br><small>Proj: '.clean($b['project_name']).'</small>'; ?>
                    </td>
                    <td><?php echo clean($b['period_type']); ?></td>
                    <td><?php echo clean($b['period_label']); ?></td>
                    <td><?php echo formatPrice($b['allocated_amount']); ?></td>
                    <td><?php echo formatPrice($b['spent_amount']); ?></td>
                    <td style="color:<?php echo $remaining < 0 ? 'var(--danger)' : 'var(--success)'; ?>; font-weight:bold;">
                        <?php echo formatPrice(abs($remaining)); ?>
                    </td>
                    <td style="min-width:100px;">
                        <div class="chart-bar-track">
                            <div class="chart-bar-fill" style="background:<?php echo $barColor; ?>; width:<?php echo $pct; ?>%;"></div>
                        </div>
                        <small><?php echo $pct; ?>%</small>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="7" class="empty-state">No budgets found matching the criteria.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo renderPagination($pagination, 'budgets.php', ['period' => $filterPeriod]); ?>
</div>
<?php include 'includes/admin-footer.php'; ?>
