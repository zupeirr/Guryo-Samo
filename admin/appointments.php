<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'Appointments';
$activeAdminPage = 'appointments';

$filterType   = clean($_GET['type'] ?? '');
$filterStatus = clean($_GET['status'] ?? '');
$perPage = 20;

if ($filterType === 'Viewing') {
    $activeAdminPage = 'viewings';
} elseif ($filterType === 'Site Visit') {
    $activeAdminPage = 'site-visits';
}

$where = '1=1';
if ($filterType)   $where .= " AND a.type = '" . $conn->real_escape_string($filterType) . "'";
if ($filterStatus) $where .= " AND a.status = '" . $conn->real_escape_string($filterStatus) . "'";

$totalRows = $conn->query("SELECT COUNT(*) AS c FROM appointments a WHERE $where")->fetch_assoc()['c'];
$pagination = paginate($totalRows, $perPage);

$stmt = $conn->prepare("SELECT a.*, p.title AS property_title, cp.name AS construction_project_name, u.full_name AS agent_name FROM appointments a LEFT JOIN properties p ON p.id = a.property_id LEFT JOIN construction_projects cp ON cp.id = a.construction_project_id LEFT JOIN agents ag ON ag.id = a.agent_id LEFT JOIN users u ON u.id = ag.user_id WHERE $where ORDER BY a.scheduled_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param('ii', $perPage, $pagination['offset']);
$stmt->execute();
$appointments = $stmt->get_result();

// Quick confirm action
if (isset($_GET['confirm'])) {
    $conn->query("UPDATE appointments SET status='Confirmed' WHERE id=" . (int)$_GET['confirm']);
    redirect('appointments.php');
}

include 'includes/admin-header.php';
?>
<div class="panel">
    <div class="panel-head">
        <h2>All Appointments</h2>
        <div style="display:flex;gap:8px;align-items:center;">
            <form method="GET" style="display:flex;gap:8px;">
                <select name="type" class="form-control" style="width:auto;" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="Viewing" <?php echo $filterType === 'Viewing' ? 'selected' : ''; ?>>Viewing</option>
                    <option value="Consultation" <?php echo $filterType === 'Consultation' ? 'selected' : ''; ?>>Consultation</option>
                    <option value="Site Visit" <?php echo $filterType === 'Site Visit' ? 'selected' : ''; ?>>Site Visit</option>
                    <option value="Other" <?php echo $filterType === 'Other' ? 'selected' : ''; ?>>Other</option>
                </select>
                <select name="status" class="form-control" style="width:auto;" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php echo $filterStatus === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo $filterStatus === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="cancelled" <?php echo $filterStatus === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </form>
            <a href="add-appointment.php" class="btn btn-primary btn-sm">+ New</a>
        </div>
    </div>
    <div class="panel-body table-wrap">
        <table class="data-table">
            <thead><tr><th>Property</th><th>Agent</th><th>Client</th><th>Type</th><th>Date</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
                <?php if ($appointments && $appointments->num_rows > 0): while ($ap = $appointments->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php 
                        if ($ap['type'] === 'Site Visit') {
                            echo clean($ap['construction_project_name'] ?: '—');
                        } else {
                            echo clean($ap['property_title'] ?: '—');
                        }
                        ?>
                    </td>
                    <td><?php echo clean($ap['agent_name'] ?: '—'); ?></td>
                    <td><?php echo clean($ap['customer_name']); ?><br><small><?php echo clean($ap['customer_email']); ?></small></td>
                    <td><?php echo clean($ap['type']); ?></td>
                    <td><?php echo date('M j, Y g:i A', strtotime($ap['scheduled_at'])); ?></td>
                    <td><span class="status-pill <?php echo $ap['status'] === 'confirmed' ? 'sale' : ($ap['status'] === 'pending' ? 'rent' : 'sold'); ?>"><?php echo ucfirst($ap['status']); ?></span></td>
                    <td>
                        <a href="view-appointment.php?id=<?php echo $ap['id']; ?>" class="btn btn-ghost btn-sm">View</a>
                        <a href="edit-appointment.php?id=<?php echo $ap['id']; ?>" class="btn btn-sm" style="background:var(--navy-800);color:#fff;">Edit</a>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="7" class="empty-state">No appointments found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo renderPagination($pagination, 'appointments.php', ['type' => $filterType, 'status' => $filterStatus]); ?>
</div>
<?php include 'includes/admin-footer.php'; ?>
