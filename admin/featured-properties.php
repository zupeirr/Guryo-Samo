<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'Featured Properties';
$activeAdminPage = 'featured';

// Handle toggle featured
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    $tid   = (int)$_POST['toggle_id'];
    $state = (int)$_POST['state'];
    $conn->prepare("UPDATE properties SET is_featured = ?, featured_order = IF(? = 1, (SELECT COALESCE(MAX(featured_order),0)+1 FROM properties p2), 0) WHERE id = ?")->execute();
    $stmt = $conn->prepare("UPDATE properties SET is_featured = ? WHERE id = ?");
    $stmt->bind_param('ii', $state, $tid);
    $stmt->execute();
    redirect('featured-properties.php?saved=1');
}

// Check if columns exist
$hasFeaturedCols = false;
$chk = $conn->query("SHOW COLUMNS FROM properties LIKE 'is_featured'");
if ($chk && $chk->num_rows > 0) {
    $hasFeaturedCols = true;
    $featured   = $conn->query("SELECT * FROM properties WHERE is_featured = 1 AND deleted_at IS NULL ORDER BY featured_order ASC");
    $available  = $conn->query("SELECT * FROM properties WHERE is_featured = 0 AND deleted_at IS NULL AND status != 'Sold' ORDER BY title ASC");
} else {
    $featured = null;
    $available = null;
}

include 'includes/admin-header.php';
?>

<div class="page-header">
    <div>
        <h2>⭐ Featured Properties</h2>
        <p style="color:var(--ink-soft); margin-top:4px;">Control which properties appear in the homepage "Featured Listings" section.</p>
    </div>
</div>

<?php if (!empty($_GET['saved'])): ?>
<div class="alert alert-success" style="margin-bottom:16px;">✅ Featured list updated.</div>
<?php endif; ?>

<?php if (!$hasFeaturedCols): ?>
<div class="empty-state" style="padding: 48px; text-align: center; margin-top:20px;">
    <p style="font-size: 1rem; color: var(--ink-soft);">⚠️ Featured Properties are not enabled yet.</p>
    <p style="color: var(--ink-soft); margin-top: 6px; font-size: 0.9rem;">Run <code>migration_business_features.sql</code> in phpMyAdmin to enable this feature.</p>
</div>
<?php else: ?>
<div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-top:20px;">

    <!-- Currently Featured -->
    <div class="card">
        <h3 style="margin-bottom:16px; color:var(--gold-500);">⭐ Currently Featured (<?php echo $featured ? $featured->num_rows : 0; ?>/6)</h3>
        <?php if ($featured && $featured->num_rows > 0): while ($p = $featured->fetch_assoc()): ?>
        <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid var(--border);">
            <div>
                <strong><?php echo clean($p['title']); ?></strong><br>
                <small style="color:var(--ink-soft);"><?php echo clean($p['location']); ?> · <?php echo formatPrice($p['price']); ?></small>
            </div>
            <form method="POST">
                <input type="hidden" name="toggle_id" value="<?php echo $p['id']; ?>">
                <input type="hidden" name="state" value="0">
                <button type="submit" class="btn btn-sm btn-danger">Remove</button>
            </form>
        </div>
        <?php endwhile; else: ?>
        <p style="color:var(--ink-soft);">No featured properties yet.</p>
        <?php endif; ?>
    </div>

    <!-- Available to Feature -->
    <div class="card">
        <h3 style="margin-bottom:16px;">🏠 Available to Feature</h3>
        <?php if ($available && $available->num_rows > 0): while ($p = $available->fetch_assoc()): ?>
        <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid var(--border);">
            <div>
                <strong><?php echo clean($p['title']); ?></strong><br>
                <small style="color:var(--ink-soft);"><?php echo clean($p['location']); ?> · <?php echo formatPrice($p['price']); ?></small>
            </div>
            <form method="POST">
                <input type="hidden" name="toggle_id" value="<?php echo $p['id']; ?>">
                <input type="hidden" name="state" value="1">
                <button type="submit" class="btn btn-sm btn-primary">⭐ Feature</button>
            </form>
        </div>
        <?php endwhile; else: ?>
        <p style="color:var(--ink-soft);">All properties are already featured.</p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/admin-footer.php'; ?>
