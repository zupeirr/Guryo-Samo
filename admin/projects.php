<?php
/**
 * admin/projects.php
 * ---------------------------------------------------------------
 * Lists development projects, total investment, revenue, and ROI.
 */

require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle = 'Projects';
$activeAdminPage = 'reports'; // Keep reports menu active

$filterStatus = clean($_GET['status'] ?? '');
$perPage = 20;

$where = '1=1';
if ($filterStatus) $where .= " AND status = '" . $conn->real_escape_string($filterStatus) . "'";

$totalRows = $conn->query("SELECT COUNT(*) AS c FROM projects WHERE $where")->fetch_assoc()['c'];
$pagination = paginate($totalRows, $perPage);

$stmt = $conn->prepare("
    SELECT *, (revenue_generated - total_investment) AS net_profit, 
           ROUND((revenue_generated - total_investment) / NULLIF(total_investment,0) * 100, 1) AS roi 
    FROM projects 
    WHERE $where 
    ORDER BY created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->bind_param('ii', $perPage, $pagination['offset']);
$stmt->execute();
$projects = $stmt->get_result();

include 'includes/admin-header.php';
?>
<div class="dash-stats" style="margin-bottom:1.5rem;">
    <div class="dash-card"><div class="ic-wrap green">🏗️</div><div><h3><?php echo (int)$totalRows; ?></h3><span>Total Projects</span></div></div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>Development Projects</h2>
        <div style="display:flex;gap:8px;">
            <form method="GET" style="display:flex;gap:8px;">
                <select name="status" class="form-control" style="width:auto;" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="Planned" <?php echo $filterStatus==='Planned'?'selected':''; ?>>Planned</option>
                    <option value="In Progress" <?php echo $filterStatus==='In Progress'?'selected':''; ?>>In Progress</option>
                    <option value="Completed" <?php echo $filterStatus==='Completed'?'selected':''; ?>>Completed</option>
                </select>
            </form>
            <a href="add-project.php" class="btn btn-primary btn-sm">+ Add Project</a>
        </div>
    </div>
    <div class="panel-body table-wrap">
        <table class="data-table">
            <thead><tr><th>Project</th><th>Status</th><th>Start Date</th><th>Total Investment</th><th>Revenue Generated</th><th>Net Profit</th><th>ROI</th></tr></thead>
            <tbody>
                <?php if ($projects && $projects->num_rows > 0): while ($p = $projects->fetch_assoc()): 
                    $isPositive = (float)$p['net_profit'] >= 0;
                ?>
                <tr>
                    <td>
                        <strong><?php echo clean($p['name']); ?></strong><br>
                        <small><?php echo substr(clean($p['description']), 0, 50) . (strlen($p['description'])>50?'...':''); ?></small>
                    </td>
                    <td>
                        <?php $sc = ['Completed'=>'status-sold','In Progress'=>'status-pending','Planned'=>'status-for-rent'];
                        echo '<span class="status-badge '.($sc[$p['status']]??'status-pending').'">'.clean($p['status']).'</span>'; ?>
                    </td>
                    <td><?php echo $p['start_date'] ? date('M j, Y', strtotime($p['start_date'])) : '—'; ?></td>
                    <td><?php echo formatPrice($p['total_investment']); ?></td>
                    <td><?php echo formatPrice($p['revenue_generated']); ?></td>
                    <td style="color:var(--<?php echo $isPositive?'success':'danger'; ?>); font-weight:bold;">
                        <?php echo ($isPositive ? '+' : '') . formatPrice($p['net_profit']); ?>
                    </td>
                    <td style="color:var(--<?php echo $isPositive?'success':'danger'; ?>); font-weight:bold;">
                        <?php echo $p['roi']; ?>%
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="7" class="empty-state">No projects found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo renderPagination($pagination, 'projects.php', ['status' => $filterStatus]); ?>
</div>
<?php include 'includes/admin-footer.php'; ?>
