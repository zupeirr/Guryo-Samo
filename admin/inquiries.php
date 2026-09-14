<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'Inquiries';
$activeAdminPage = 'inquiries';

$perPage = 20;
$totalRows = $conn->query("SELECT COUNT(*) AS c FROM inquiries")->fetch_assoc()['c'] ?? 0;
$pagination = paginate($totalRows, $perPage);

$stmt = $conn->prepare("SELECT i.*, p.title AS property_title FROM inquiries i LEFT JOIN properties p ON p.id = i.property_id ORDER BY i.created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param('ii', $perPage, $pagination['offset']);
$stmt->execute();
$inquiries = $stmt->get_result();

include 'includes/admin-header.php';
?>
<div class="panel">
    <div class="panel-head"><h2>Property Inquiries</h2></div>
    <div class="panel-body table-wrap">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Email</th><th>Property</th><th>Message</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
                <?php if ($inquiries && $inquiries->num_rows > 0): while ($inq = $inquiries->fetch_assoc()): ?>
                <tr>
                    <td><?php echo clean($inq['name']); ?></td>
                    <td><?php echo clean($inq['email']); ?></td>
                    <td><?php echo clean($inq['property_title'] ?: '—'); ?></td>
                    <td><?php echo nl2br(clean(substr($inq['message'], 0, 80))); ?>…</td>
                    <td><span class="status-pill <?php echo $inq['status'] === 'new' ? 'rent' : 'sale'; ?>"><?php echo ucfirst(clean($inq['status'])); ?></span></td>
                    <td><?php echo date('M j, Y', strtotime($inq['created_at'])); ?></td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="6" class="empty-state">No inquiries yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo renderPagination($pagination, 'inquiries.php'); ?>
</div>
<?php include 'includes/admin-footer.php'; ?>
