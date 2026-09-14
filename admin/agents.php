<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'All Agents';
$activeAdminPage = 'agents';

$agents = $conn->query("SELECT a.*, u.full_name, u.email FROM agents a JOIN users u ON u.id = a.user_id ORDER BY u.full_name ASC");

include 'includes/admin-header.php';
?>
<div class="panel">
    <div class="panel-head">
        <h2>All Agents</h2>
        <a href="add-agent.php" class="btn btn-primary btn-sm">+ Add Agent</a>
    </div>
    <div class="panel-body table-wrap">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Specialty</th><th>Commission %</th><th>Joined</th></tr></thead>
            <tbody>
                <?php if ($agents && $agents->num_rows > 0): while ($a = $agents->fetch_assoc()): ?>
                <tr>
                    <td><?php echo clean($a['full_name']); ?></td>
                    <td><?php echo clean($a['email']); ?></td>
                    <td><?php echo clean($a['phone'] ?: '—'); ?></td>
                    <td><?php echo clean($a['specialization'] ?: '—'); ?></td>
                    <td><?php echo number_format((float)$a['commission_rate'], 2); ?>%</td>
                    <td><?php echo date('M Y', strtotime($a['created_at'])); ?></td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="6" class="empty-state">No agents found. <a href="add-agent.php">Add one</a>.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'includes/admin-footer.php'; ?>
