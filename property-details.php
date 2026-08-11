<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$baseUrl = '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT * FROM properties WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();

if (!$property) {
    $pageTitle = 'Property Not Found';
    $activePage = 'properties';
    include 'includes/header.php';
    echo '<section class="section"><div class="container empty-state">
            <h2>Property Not Found</h2>
            <p style="margin-top:10px;">The property you are looking for does not exist or may have been removed.</p>
            <a href="properties.php" class="btn btn-navy mt-3">Back to Properties</a>
          </div></section>';
    include 'includes/footer.php';
    exit;
}

$pageTitle = $property['title'];
$activePage = 'properties';
$statusClass = $property['status'] === 'For Sale' ? 'sale' : ($property['status'] === 'For Rent' ? 'rent' : 'sold');
$imgSrc = ($property['image'] && $property['image'] !== 'no-image.jpg') ? 'uploads/' . $property['image'] : 'assets/images/no-image.jpg';

// Similar properties (same type, excluding current)
$similarStmt = $conn->prepare("SELECT * FROM properties WHERE type = ? AND id != ? ORDER BY created_at DESC LIMIT 3");
$similarStmt->bind_param('si', $property['type'], $id);
$similarStmt->execute();
$similar = $similarStmt->get_result();

include 'includes/header.php';
?>

<section class="section">
    <div class="container">
        <a href="properties.php" style="color:var(--ink-soft); font-size:0.88rem;">&larr; Back to Properties</a>

        <div class="details-header" style="margin-top:20px;">
            <div>
                <span class="badge <?php echo $statusClass; ?>" style="position:static; display:inline-block; margin-bottom:10px;"><?php echo clean($property['status']); ?></span>
                <h1 style="font-size:1.8rem;"><?php echo clean($property['title']); ?></h1>
                <p class="property-location" style="margin-top:6px;">📍 <?php echo clean($property['location']); ?></p>
            </div>
        </div>

        <div class="details-hero-img">
            <img src="<?php echo clean($imgSrc); ?>" alt="<?php echo clean($property['title']); ?>" onerror="this.src='assets/images/no-image.jpg'">
        </div>

        <div class="details-grid">
            <div>
                <h2 class="section-title" style="font-size:1.4rem;">Property Overview</h2>
                <p style="color:var(--ink-soft);"><?php echo nl2br(clean($property['description'])); ?></p>

                <div class="spec-grid">
                    <div class="spec-item"><strong><?php echo (int)$property['bedrooms']; ?></strong><span>Bedrooms</span></div>
                    <div class="spec-item"><strong><?php echo (int)$property['bathrooms']; ?></strong><span>Bathrooms</span></div>
                    <div class="spec-item"><strong><?php echo (float)$property['size']; ?> m²</strong><span>Size</span></div>
                    <div class="spec-item"><strong><?php echo clean($property['type']); ?></strong><span>Type</span></div>
                </div>
            </div>

            <div>
                <div class="side-card">
                    <span class="eyebrow">Price</span>
                    <div class="price-tag"><?php echo formatPrice($property['price']); ?><?php echo $property['status'] === 'For Rent' ? ' / mo' : ''; ?></div>
                    <h3 style="font-size:1rem; margin-bottom:6px;">Interested in this property?</h3>
                    <p style="color:var(--ink-soft); font-size:0.88rem; margin-bottom:18px;">Send us a message and one of our agents will get back to you shortly.</p>
                    <a href="contact.php" class="btn btn-primary btn-block">Contact an Agent</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($similar->num_rows > 0): ?>
<section class="section bg-alt">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">You May Also Like</span>
            <h2 class="section-title">Similar Properties</h2>
        </div>
        <div class="property-grid">
            <?php while ($p = $similar->fetch_assoc()):
                $sClass = $p['status'] === 'For Sale' ? 'sale' : ($p['status'] === 'For Rent' ? 'rent' : 'sold');
                $sImg = ($p['image'] && $p['image'] !== 'no-image.jpg') ? 'uploads/' . $p['image'] : 'assets/images/no-image.jpg';
            ?>
            <div class="property-card">
                <div class="property-thumb">
                    <span class="badge <?php echo $sClass; ?>"><?php echo clean($p['status']); ?></span>
                    <img src="<?php echo clean($sImg); ?>" alt="<?php echo clean($p['title']); ?>" onerror="this.src='assets/images/no-image.jpg'">
                </div>
                <div class="property-body">
                    <div class="property-price"><?php echo formatPrice($p['price']); ?></div>
                    <h3 class="property-title"><?php echo clean($p['title']); ?></h3>
                    <div class="property-location">📍 <?php echo clean($p['location']); ?></div>
                    <a href="property-details.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-ghost btn-block">View Details</a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
