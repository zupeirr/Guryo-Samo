<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'Notifications';
$activeAdminPage = 'notifications';

$unreadMessages  = $conn->query("SELECT id, name, subject, created_at FROM messages WHERE is_read = 0 ORDER BY created_at DESC LIMIT 20")->fetch_all(MYSQLI_ASSOC);
$pendingAppts    = $conn->query("SELECT a.id, a.scheduled_at, a.type, p.title FROM appointments a LEFT JOIN properties p ON p.id = a.property_id WHERE a.status = 'Pending' ORDER BY a.scheduled_at ASC LIMIT 20")->fetch_all(MYSQLI_ASSOC);

include 'includes/admin-header.php';
?>
<div class="dash-stats" style="margin-bottom:2rem;">
    <div class="dash-card">
        <div class="ic-wrap red">✉️</div>
        <div><h3><?php echo count($unreadMessages); ?></h3><span>Unread Messages</span></div>
    </div>
    <div class="dash-card">
        <div class="ic-wrap gold">📅</div>
        <div><h3><?php echo count($pendingAppts); ?></h3><span>Pending Appointments</span></div>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>📬 Unread Messages</h2></div>
    <div class="panel-body table-wrap">
        <table class="data-table">
            <thead><tr><th>From</th><th>Subject</th><th>Received</th></tr></thead>
            <tbody>
                <?php if ($unreadMessages): foreach ($unreadMessages as $m): ?>
                <tr>
                    <td><?php echo clean($m['name']); ?></td>
                    <td><?php echo clean($m['subject'] ?: '(no subject)'); ?></td>
                    <td><?php echo date('M j, Y g:i A', strtotime($m['created_at'])); ?></td>
                </tr>
                <?php endforeach; else: ?><tr><td colspan="3" class="empty-state">All caught up!</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>📅 Pending Appointments</h2></div>
    <div class="panel-body table-wrap">
        <table class="data-table">
            <thead><tr><th>Property</th><th>Type</th><th>Date</th></tr></thead>
            <tbody>
                <?php if ($pendingAppts): foreach ($pendingAppts as $ap): ?>
                <tr>
                    <td><?php echo clean($ap['title'] ?: '—'); ?></td>
                    <td><?php echo clean($ap['type']); ?></td>
                    <td><?php echo date('M j, Y g:i A', strtotime($ap['scheduled_at'])); ?></td>
                </tr>
                <?php endforeach; else: ?><tr><td colspan="3" class="empty-state">No pending appointments.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'includes/admin-footer.php'; ?>
