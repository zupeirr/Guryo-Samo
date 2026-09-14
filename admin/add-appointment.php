<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'Add Appointment';
$activeAdminPage = 'appointments';

$errors = []; $success = '';
$properties = $conn->query("SELECT id, title FROM properties ORDER BY title ASC");
$construction_projects = $conn->query("SELECT id, name FROM construction_projects ORDER BY name ASC");
$agents     = $conn->query("SELECT a.id, u.full_name AS name FROM agents a JOIN users u ON u.id = a.user_id ORDER BY u.full_name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $propertyId   = (int)($_POST['property_id'] ?? 0) ?: null;
    $constProjId  = (int)($_POST['construction_project_id'] ?? 0) ?: null;
    $agentId      = (int)($_POST['agent_id'] ?? 0) ?: null;
    $clientName   = trim($_POST['client_name'] ?? '');
    $clientEmail  = trim($_POST['client_email'] ?? '');
    $clientPhone  = trim($_POST['client_phone'] ?? '');
    $type         = trim($_POST['type'] ?? 'Viewing');
    $date         = trim($_POST['appointment_date'] ?? '');
    $notes        = trim($_POST['notes'] ?? '');

    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Session expired. Please try again.';
    }
    if (!$clientName)  $errors[] = 'Client name is required.';
    if (!$clientEmail) $errors[] = 'Client email is required.';
    if (!$date)        $errors[] = 'Appointment date is required.';

    if (empty($errors)) {
        $ins = $conn->prepare("INSERT INTO appointments (property_id, construction_project_id, agent_id, customer_name, customer_email, customer_phone, type, scheduled_at, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
        $ins->bind_param('iiissssss', $propertyId, $constProjId, $agentId, $clientName, $clientEmail, $clientPhone, $type, $date, $notes);
        if ($ins->execute()) {
            redirect('appointments.php?saved=1');
        } else {
            $errors[] = 'Could not save appointment. Please try again.';
        }
    }
}

include 'includes/admin-header.php';
?>
<div class="panel" style="max-width:640px;">
    <div class="panel-head"><h2>Schedule Appointment</h2></div>
    <div class="panel-body">
        <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
        <?php if ($errors): ?><div class="alert alert-danger"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?></div><?php endif; ?>
        <form method="POST" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            <div class="form-group"><label>Type</label>
                <select name="type" id="apptType" class="form-control">
                    <option value="Viewing">Viewing</option>
                    <option value="Consultation">Consultation</option>
                    <option value="Site Visit">Site Visit</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-group" id="propSelectWrap"><label>Property (for Viewing/Consultation)</label>
                <select name="property_id" class="form-control">
                    <option value="0">— Select Property —</option>
                    <?php while ($p = $properties->fetch_assoc()): ?><option value="<?php echo $p['id']; ?>"><?php echo clean($p['title']); ?></option><?php endwhile; ?>
                </select>
            </div>
            <div class="form-group" id="constSelectWrap" style="display:none;"><label>Construction Project (for Site Visit)</label>
                <select name="construction_project_id" class="form-control">
                    <option value="0">— Select Construction Project —</option>
                    <?php while ($p = $construction_projects->fetch_assoc()): ?><option value="<?php echo $p['id']; ?>"><?php echo clean($p['name']); ?></option><?php endwhile; ?>
                </select>
            </div>
            <div class="form-group"><label>Agent</label>
                <select name="agent_id" class="form-control">
                    <option value="0">— Select Agent —</option>
                    <?php while ($ag = $agents->fetch_assoc()): ?><option value="<?php echo $ag['id']; ?>"><?php echo clean($ag['name']); ?></option><?php endwhile; ?>
                </select>
            </div>
            <div class="form-group"><label>Client Name *</label><input type="text" name="client_name" class="form-control"></div>
            <div class="form-group"><label>Client Email *</label><input type="email" name="client_email" class="form-control"></div>
            <div class="form-group"><label>Client Phone</label><input type="text" name="client_phone" class="form-control"></div>
            <div class="form-group"><label>Date & Time *</label><input type="datetime-local" name="appointment_date" class="form-control"></div>
            <div class="form-group"><label>Notes</label><textarea name="notes" class="form-control" rows="3"></textarea></div>
            <button class="btn btn-primary" type="submit">Schedule</button>
            <a href="appointments.php" class="btn btn-ghost">Cancel</a>
        </form>
    </div>
</div>
<script>
document.getElementById('apptType').addEventListener('change', function(){
    const t = this.value;
    document.getElementById('propSelectWrap').style.display = (t==='Viewing' || t==='Consultation') ? 'block' : 'none';
    document.getElementById('constSelectWrap').style.display = (t==='Site Visit') ? 'block' : 'none';
});
// trigger on load
document.getElementById('apptType').dispatchEvent(new Event('change'));
</script>
<?php include 'includes/admin-footer.php'; ?>
