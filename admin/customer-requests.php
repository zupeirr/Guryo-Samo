<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'Customer Requests';
$activeAdminPage = 'customer-requests';

$perPage = 20;
$totalRows = $conn->query("SELECT COUNT(*) AS c FROM messages m INNER JOIN users u ON u.email = m.email WHERE u.role = 'customer'")->fetch_assoc()['c'];
$pagination = paginate($totalRows, $perPage);

$stmt = $conn->prepare("SELECT m.*, u.id AS user_id FROM messages m INNER JOIN users u ON u.email = m.email WHERE u.role = 'customer' ORDER BY m.created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param('ii', $perPage, $pagination['offset']);
$stmt->execute();
$requests = $stmt->get_result();

include 'includes/admin-header.php';
?>
<div class="panel">
    <div class="panel-head"><h2>Customer Requests</h2></div>
    <div class="panel-body table-wrap">
        <table class="data-table">
            <thead><tr><th>Customer</th><th>Email</th><th>Subject</th><th>Status</th><th>Received</th></tr></thead>
            <tbody>
                <?php if ($requests->num_rows > 0): while ($r = $requests->fetch_assoc()): ?>
                <tr>
                    <td><?php echo clean($r['name']); ?></td>
                    <td><?php echo clean($r['email']); ?></td>
                    <td><?php echo clean($r['subject'] ?: '—'); ?></td>
                    <td><span class="status-pill <?php echo $r['is_read'] ? 'sale' : 'rent'; ?>"><?php echo $r['is_read'] ? 'Read' : 'Unread'; ?></span></td>
                    <td><?php echo date('M j, Y', strtotime($r['created_at'])); ?></td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="5" class="empty-state">No customer requests found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo renderPagination($pagination, 'customer-requests.php'); ?>
</div>
<?php include 'includes/admin-footer.php'; ?>
