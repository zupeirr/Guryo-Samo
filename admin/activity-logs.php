<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'Activity Logs';
$activeAdminPage = 'activity-logs';

$perPage = 50;
$totalRows = $conn->query("SELECT COUNT(*) AS c FROM activity_logs")->fetch_assoc()['c'] ?? 0;
$pagination = paginate($totalRows, $perPage);

$stmt = $conn->prepare("SELECT al.*, u.full_name AS user_name FROM activity_logs al LEFT JOIN users u ON u.id = al.user_id ORDER BY al.created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param('ii', $perPage, $pagination['offset']);
$stmt->execute();
$logs = $stmt->get_result();

include 'includes/admin-header.php';
?>
<div class="panel">
    <div class="panel-head"><h2>Activity Logs</h2></div>
    <div class="panel-body table-wrap">
        <table class="data-table">
            <thead><tr><th>User</th><th>Action</th><th>Target</th><th>IP Address</th><th>Time</th></tr></thead>
            <tbody>
                <?php if ($logs && $logs->num_rows > 0): while ($l = $logs->fetch_assoc()): ?>
                <tr>
                    <td><?php echo clean($l['user_name'] ?: 'Guest'); ?></td>
                    <td><span class="status-pill sale"><?php echo clean($l['action']); ?></span></td>
                    <td><?php echo clean(($l['target_type'] ?: '') . ($l['target_id'] ? ' #'.$l['target_id'] : '')); ?></td>
                    <td><?php echo clean($l['ip_address'] ?: '—'); ?></td>
                    <td><?php echo date('M j, Y g:i A', strtotime($l['created_at'])); ?></td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="5" class="empty-state">No activity logs yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo renderPagination($pagination, 'activity-logs.php'); ?>
</div>
<?php include 'includes/admin-footer.php'; ?>
