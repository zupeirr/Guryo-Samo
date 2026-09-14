<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'Leads';
$activeAdminPage = 'leads';

$perPage = 20;
$totalRows = $conn->query("SELECT COUNT(*) AS c FROM messages WHERE is_read = 0")->fetch_assoc()['c'];
$pagination = paginate($totalRows, $perPage);

$stmt = $conn->prepare("SELECT * FROM messages WHERE is_read = 0 ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param('ii', $perPage, $pagination['offset']);
$stmt->execute();
$leads = $stmt->get_result();

include 'includes/admin-header.php';
?>
<div class="panel">
    <div class="panel-head"><h2>Leads <span class="badge"><?php echo $totalRows; ?> unread</span></h2></div>
    <div class="panel-body table-wrap">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Subject</th><th>Message</th><th>Received</th></tr></thead>
            <tbody>
                <?php if ($leads->num_rows > 0): while ($l = $leads->fetch_assoc()): ?>
                <tr>
                    <td><?php echo clean($l['name']); ?></td>
                    <td><?php echo clean($l['email']); ?></td>
                    <td><?php echo clean($l['phone'] ?: '—'); ?></td>
                    <td><?php echo clean($l['subject'] ?: '—'); ?></td>
                    <td><?php echo nl2br(clean(substr($l['message'], 0, 80))); ?>…</td>
                    <td><?php echo date('M j, Y g:i A', strtotime($l['created_at'])); ?></td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="6" class="empty-state">No unread leads at the moment.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo renderPagination($pagination, 'leads.php'); ?>
</div>
<?php include 'includes/admin-footer.php'; ?>
