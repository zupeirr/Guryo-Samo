<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'Recycle Bin';
$activeAdminPage = 'archived';

// Check if deleted_at column exists
$hasDeletedAt = false;
$chk = $conn->query("SHOW COLUMNS FROM properties LIKE 'deleted_at'");
if ($chk && $chk->num_rows > 0) {
    $hasDeletedAt = true;
    $result = $conn->query("SELECT p.*, u.name AS deleted_by_name FROM properties p LEFT JOIN users u ON p.deleted_by = u.id WHERE p.deleted_at IS NOT NULL ORDER BY p.deleted_at DESC");
} else {
    $result = null;
}

include 'includes/admin-header.php';
?>

<div class="page-header">
    <div>
        <h2>🗑️ Recycle Bin</h2>
        <p style="color:var(--ink-soft); margin-top:4px;">Properties deleted in the last 30 days. Restore or permanently remove them.</p>
    </div>
    <a href="properties.php" class="btn btn-outline">← Back to Properties</a>
</div>

<?php if (!empty($_GET['restored'])): ?>
<div class="alert alert-success">✅ Property restored successfully.</div>
<?php elseif (!empty($_GET['purged'])): ?>
<div class="alert alert-success">🗑️ Property permanently deleted.</div>
<?php endif; ?>

<div class="card" style="margin-top:20px;">
    <?php if (!$hasDeletedAt): ?>
    <div class="empty-state" style="padding: 48px; text-align: center;">
        <p style="font-size: 1rem; color: var(--ink-soft);">⚠️ Recycle Bin is not enabled yet.</p>
        <p style="color: var(--ink-soft); margin-top: 6px; font-size: 0.9rem;">Run <code>migration_business_features.sql</code> in phpMyAdmin to enable this feature.</p>
    </div>
    <?php else: ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Property</th>
                <th>Location</th>
                <th>Price</th>
                <th>Deleted By</th>
                <th>Deleted At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): while ($p = $result->fetch_assoc()): ?>
            <tr>
                <td><strong><?php echo clean($p['title']); ?></strong><br><small style="color:var(--ink-soft);"><?php echo clean($p['property_ref'] ?? ''); ?></small></td>
                <td><?php echo clean($p['location']); ?></td>
                <td><?php echo formatPrice($p['price']); ?></td>
                <td><?php echo clean($p['deleted_by_name'] ?? 'Unknown'); ?></td>
                <td><?php echo date('d M Y, H:i', strtotime($p['deleted_at'])); ?></td>
                <td>
                    <a href="delete-property.php?id=<?php echo $p['id']; ?>&action=restore" class="btn btn-sm btn-primary" onclick="return confirm('Restore this property?')">↩ Restore</a>
                    <a href="delete-property.php?id=<?php echo $p['id']; ?>&action=purge" class="btn btn-sm btn-danger" onclick="return confirm('Permanently delete? This CANNOT be undone.')">🗑 Delete Forever</a>
                </td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="6" style="text-align:center; color:var(--ink-soft); padding:40px;">The recycle bin is empty.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php include 'includes/admin-footer.php'; ?>
