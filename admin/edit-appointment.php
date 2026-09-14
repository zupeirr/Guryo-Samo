<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'Edit Appointment';
$activeAdminPage = 'appointments';

$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$ap = $stmt->get_result()->fetch_assoc();
if (!$ap) { redirect('appointments.php'); }

$properties = $conn->query("SELECT id, title FROM properties ORDER BY title ASC");
$agents     = $conn->query("SELECT a.id, u.full_name AS name FROM agents a JOIN users u ON u.id = a.user_id ORDER BY u.full_name ASC");

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Session expired. Please try again.';
    } else {
        $propertyId  = (int)($_POST['property_id'] ?? 0);
        $agentId     = (int)($_POST['agent_id'] ?? 0);
        $clientName  = trim($_POST['client_name'] ?? '');
        $clientEmail = trim($_POST['client_email'] ?? '');
        $clientPhone = trim($_POST['client_phone'] ?? '');
        $type        = trim($_POST['type'] ?? 'Viewing');
        $date        = trim($_POST['appointment_date'] ?? '');
        $notes       = trim($_POST['notes'] ?? '');
        $status      = trim($_POST['status'] ?? 'Pending');

        $allowed_statuses = ['Pending','Confirmed','Cancelled','Completed'];
        if (!$clientName)  $errors[] = 'Client name is required.';
        if (!$clientEmail) $errors[] = 'Client email is required.';
        if (!$date)        $errors[] = 'Appointment date is required.';
        if (!in_array($status, $allowed_statuses, true)) $errors[] = 'Invalid status.';

        if (empty($errors)) {
            $upd = $conn->prepare("UPDATE appointments SET property_id=?, agent_id=?, customer_name=?, customer_email=?, customer_phone=?, type=?, scheduled_at=?, notes=?, status=? WHERE id=?");
            $upd->bind_param('iissssssi', $propertyId, $agentId, $clientName, $clientEmail, $clientPhone, $type, $date, $notes, $status, $id);
            // Fix: need 10 params - status is s not i
            $upd = $conn->prepare("UPDATE appointments SET property_id=?, agent_id=?, customer_name=?, customer_email=?, customer_phone=?, type=?, scheduled_at=?, notes=?, status=? WHERE id=?");
            $upd->bind_param('iisssssssi', $propertyId, $agentId, $clientName, $clientEmail, $clientPhone, $type, $date, $notes, $status, $id);
            if ($upd->execute()) {
                redirect('view-appointment.php?id=' . $id . '&updated=1');
            } else {
                $errors[] = 'Could not update appointment. Please try again.';
            }
        }
    }
}

include 'includes/admin-header.php';
?>
<div class="panel" style="max-width:640px;">
    <div class="panel-head">
        <div>
            <h2>Edit Appointment</h2>
            <p style="font-size:0.83rem;color:var(--ink-soft);margin-top:3px;">Ref #<?php echo (int)$ap['id']; ?></p>
        </div>
        <a href="view-appointment.php?id=<?php echo $ap['id']; ?>" class="btn btn-ghost btn-sm">&#8592; Back</a>
    </div>
    <div class="panel-body">
        <?php if ($errors): ?>
        <div class="alert-banner danger" style="margin-bottom:16px;">
            <span class="alert-icon">&#9888;</span>
            <span><?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            <input type="hidden" name="id" value="<?php echo (int)$ap['id']; ?>">

            <div class="form-section-label">&#128100; Client Details</div>
            <div class="form-grid-2">
                <div class="field-block">
                    <label>Client Name <span class="req">*</span></label>
                    <input type="text" name="client_name" class="form-control" value="<?php echo clean($ap['customer_name']); ?>" required>
                </div>
                <div class="field-block">
                    <label>Client Email <span class="req">*</span></label>
                    <input type="email" name="client_email" class="form-control" value="<?php echo clean($ap['customer_email']); ?>" required>
                </div>
                <div class="field-block">
                    <label>Client Phone</label>
                    <input type="text" name="client_phone" class="form-control" value="<?php echo clean($ap['customer_phone']); ?>">
                </div>
            </div>

            <div class="form-divider"></div>
            <div class="form-section-label">&#128197; Appointment Details</div>
            <div class="form-grid-2">
                <div class="field-block">
                    <label>Property</label>
                    <select name="property_id" class="form-control">
                        <option value="0">&#8212; Select Property &#8212;</option>
                        <?php while ($p = $properties->fetch_assoc()): ?>
                        <option value="<?php echo $p['id']; ?>" <?php echo $ap['property_id'] == $p['id'] ? 'selected' : ''; ?>><?php echo clean($p['title']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="field-block">
                    <label>Agent</label>
                    <select name="agent_id" class="form-control">
                        <option value="0">&#8212; Select Agent &#8212;</option>
                        <?php while ($ag = $agents->fetch_assoc()): ?>
                        <option value="<?php echo $ag['id']; ?>" <?php echo $ap['agent_id'] == $ag['id'] ? 'selected' : ''; ?>><?php echo clean($ag['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="field-block">
                    <label>Type</label>
                    <select name="type" class="form-control">
                        <?php foreach (['Viewing','Consultation','Other'] as $t): ?>
                        <option value="<?php echo $t; ?>" <?php echo $ap['type'] === $t ? 'selected' : ''; ?>><?php echo $t; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field-block">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <?php foreach (['Pending','Confirmed','Cancelled','Completed'] as $s): ?>
                        <option value="<?php echo $s; ?>" <?php echo $ap['status'] === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field-block">
                    <label>Date &amp; Time <span class="req">*</span></label>
                    <input type="datetime-local" name="appointment_date" class="form-control"
                           value="<?php echo date('Y-m-d\TH:i', strtotime($ap['scheduled_at'])); ?>" required>
                </div>
            </div>

            <div class="field-block" style="margin-top:16px;">
                <label>Notes</label>
                <textarea name="notes" class="form-control" rows="4"><?php echo clean($ap['notes']); ?></textarea>
            </div>

            <div class="form-actions" style="margin-top:24px;">
                <button type="submit" class="btn btn-primary btn-lg">&#10003; Save Changes</button>
                <a href="view-appointment.php?id=<?php echo $ap['id']; ?>" class="btn btn-ghost btn-lg">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php include 'includes/admin-footer.php'; ?>
