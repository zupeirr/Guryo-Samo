<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) redirect('properties.php');

$stmt = $conn->prepare("SELECT title, property_ref FROM properties WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$prop = $stmt->get_result()->fetch_assoc();
if (!$prop) redirect('properties.php');

$pageTitle       = 'Property Status History';
$activeAdminPage = 'properties';

// Safely check if table exists before querying
$historyRes  = null;
$tableExists = false;
$chk = $conn->query("SHOW TABLES LIKE 'property_status_history'");
if ($chk && $chk->num_rows > 0) {
    $tableExists = true;
    $historyStmt = $conn->prepare("
        SELECT h.*, u.name AS changed_by_name 
        FROM property_status_history h 
        LEFT JOIN users u ON h.changed_by = u.id 
        WHERE h.property_id = ? 
        ORDER BY h.changed_at DESC
    ");
    $historyStmt->bind_param('i', $id);
    $historyStmt->execute();
    $historyRes = $historyStmt->get_result();
}

include 'includes/admin-header.php';
?>

<div class="page-header">
    <div>
        <h2>🕒 Status History: <?php echo clean($prop['title']); ?></h2>
        <p style="color:var(--ink-soft); margin-top:4px;">Ref: <?php echo clean($prop['property_ref']); ?></p>
    </div>
    <a href="properties.php" class="btn btn-outline">← Back to Properties</a>
</div>

<div class="card" style="margin-top:20px;">
    <?php if (!$tableExists): ?>
    <div class="empty-state" style="padding: 40px; text-align: center;">
        <p style="font-size: 1rem; color: var(--ink-soft);">⚠️ Status history is not enabled yet.</p>
        <p style="color: var(--ink-soft); margin-top: 6px; font-size: 0.9rem;">Run <code>migration_business_features.sql</code> in phpMyAdmin to enable this feature.</p>
    </div>
    <?php elseif ($historyRes && $historyRes->num_rows > 0): ?>
    <div style="border-left: 2px solid var(--border); padding-left: 20px; margin-left: 10px;">
        <?php while ($h = $historyRes->fetch_assoc()): ?>
        <div style="margin-bottom: 24px; position: relative;">
            <div style="position: absolute; left: -27px; top: 0; width: 12px; height: 12px; border-radius: 50%; background: var(--gold-500); border: 2px solid #fff; box-shadow: 0 0 0 1px var(--border);"></div>
            <div style="font-size: 0.85rem; color: var(--ink-soft); margin-bottom: 4px;">
                <?php echo date('d M Y, H:i', strtotime($h['changed_at'])); ?> by <strong><?php echo clean($h['changed_by_name'] ?? 'System'); ?></strong>
            </div>
            <div style="font-size: 1.05rem; font-weight: 500; margin-bottom: 4px;">
                Changed to <span class="badge" style="background: var(--navy-800); color: white;"><?php echo clean($h['new_status']); ?></span>
                <?php if ($h['old_status']): ?>
                <span style="color: var(--ink-soft); font-size: 0.9rem;">(from <?php echo clean($h['old_status']); ?>)</span>
                <?php endif; ?>
            </div>
            <?php if ($h['note']): ?>
            <div style="background: rgba(0,0,0,0.02); padding: 10px; border-radius: 6px; font-size: 0.9rem; color: var(--ink-soft); margin-top: 8px;">
                <em>"<?php echo clean($h['note']); ?>"</em>
            </div>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">No status changes recorded yet for this property.</div>
    <?php endif; ?>
</div>

<?php include 'includes/admin-footer.php'; ?>
