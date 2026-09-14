<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'View Appointment';
$activeAdminPage = 'appointments';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle quick status update before fetching
if (isset($_GET['setstatus'])) {
    $allowed = ['Pending','Confirmed','Cancelled','Completed'];
    $newStatus = $_GET['setstatus'];
    if (in_array($newStatus, $allowed, true)) {
        $upd = $conn->prepare("UPDATE appointments SET status=? WHERE id=?");
        $upd->bind_param('si', $newStatus, $id);
        $upd->execute();
    }
    redirect("view-appointment.php?id=$id");
}

$stmt = $conn->prepare("SELECT a.*, p.title AS property_title, u.full_name AS agent_name FROM appointments a LEFT JOIN properties p ON p.id = a.property_id LEFT JOIN agents ag ON ag.id = a.agent_id LEFT JOIN users u ON u.id = ag.user_id WHERE a.id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$ap = $stmt->get_result()->fetch_assoc();

if (!$ap) { redirect('appointments.php'); }

$statusColors = ['Pending'=>'rent','Confirmed'=>'sale','Cancelled'=>'sold','Completed'=>'sold'];
$statusColor  = $statusColors[$ap['status']] ?? 'rent';

include 'includes/admin-header.php';
?>
<div class="panel" style="max-width:720px;">
    <div class="panel-head">
        <div>
            <h2>Appointment Details</h2>
            <p style="font-size:0.83rem;color:var(--ink-soft);margin-top:3px;">Ref #<?php echo (int)$ap['id']; ?></p>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="edit-appointment.php?id=<?php echo $ap['id']; ?>" class="btn btn-primary btn-sm">✏️ Edit</a>
            <a href="appointments.php" class="btn btn-ghost btn-sm">&#8592; Back</a>
        </div>
    </div>
    <div class="panel-body">

        <div style="display:flex;align-items:center;gap:12px;padding:14px 18px;background:var(--bg-alt);border-radius:8px;margin-bottom:24px;">
            <span class="status-pill <?php echo $statusColor; ?>" style="font-size:0.95rem;padding:6px 16px;"><?php echo clean($ap['status']); ?></span>
            <span style="color:var(--ink-soft);font-size:0.9rem;">Scheduled for <strong style="color:var(--navy-900);"><?php echo date('l, F j, Y \a\t g:i A', strtotime($ap['scheduled_at'])); ?></strong></span>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">
            <div>
                <h4 style="font-size:0.78rem;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-soft);margin-bottom:12px;">Client Information</h4>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <div><span style="font-size:0.8rem;color:var(--ink-soft);">Name</span><p style="font-weight:600;color:var(--navy-900);margin:2px 0 0;"><?php echo clean($ap['customer_name']); ?></p></div>
                    <div><span style="font-size:0.8rem;color:var(--ink-soft);">Email</span><p style="margin:2px 0 0;"><a href="mailto:<?php echo clean($ap['customer_email']); ?>" style="color:var(--gold-600);"><?php echo clean($ap['customer_email']); ?></a></p></div>
                    <div><span style="font-size:0.8rem;color:var(--ink-soft);">Phone</span><p style="margin:2px 0 0;"><?php echo $ap['customer_phone'] ? clean($ap['customer_phone']) : '&mdash;'; ?></p></div>
                </div>
            </div>
            <div>
                <h4 style="font-size:0.78rem;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-soft);margin-bottom:12px;">Appointment Info</h4>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <div><span style="font-size:0.8rem;color:var(--ink-soft);">Type</span><p style="font-weight:600;margin:2px 0 0;"><?php echo clean($ap['type']); ?></p></div>
                    <div><span style="font-size:0.8rem;color:var(--ink-soft);">Property</span><p style="margin:2px 0 0;"><?php echo $ap['property_title'] ? clean($ap['property_title']) : '&mdash;'; ?></p></div>
                    <div><span style="font-size:0.8rem;color:var(--ink-soft);">Agent</span><p style="margin:2px 0 0;"><?php echo $ap['agent_name'] ? clean($ap['agent_name']) : '&mdash;'; ?></p></div>
                </div>
            </div>
        </div>

        <?php if ($ap['notes']): ?>
        <div style="border-top:1px solid var(--border);padding-top:20px;">
            <h4 style="font-size:0.78rem;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-soft);margin-bottom:8px;">Notes</h4>
            <p style="color:var(--ink-soft);line-height:1.6;"><?php echo nl2br(clean($ap['notes'])); ?></p>
        </div>
        <?php endif; ?>

        <div style="border-top:1px solid var(--border);padding-top:20px;margin-top:20px;display:flex;gap:10px;flex-wrap:wrap;">
            <?php if ($ap['status'] !== 'Confirmed'): ?>
            <a href="?id=<?php echo $ap['id']; ?>&setstatus=Confirmed" class="btn btn-primary btn-sm" onclick="return confirm('Mark as Confirmed?')">&#10004; Confirm</a>
            <?php endif; ?>
            <?php if ($ap['status'] !== 'Completed'): ?>
            <a href="?id=<?php echo $ap['id']; ?>&setstatus=Completed" class="btn btn-sm" style="background:#6c757d;color:#fff;" onclick="return confirm('Mark as Completed?')">&#10003; Mark Completed</a>
            <?php endif; ?>
            <?php if ($ap['status'] !== 'Cancelled'): ?>
            <a href="?id=<?php echo $ap['id']; ?>&setstatus=Cancelled" class="btn btn-sm" style="background:var(--danger);color:#fff;" onclick="return confirm('Cancel this appointment?')">&#10005; Cancel</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'includes/admin-footer.php'; ?>
