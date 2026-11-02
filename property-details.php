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
$imgSrc = ($property['image'] && $property['image'] !== 'no-image.jpg') ? 'image.php?type=property&id=' . $property['id'] : 'assets/images/no-image.jpg';

// Similar properties (same type, excluding current)
$similarStmt = $conn->prepare("SELECT * FROM properties WHERE type = ? AND id != ? ORDER BY created_at DESC LIMIT 3");
$similarStmt->bind_param('si', $property['type'], $id);
$similarStmt->execute();
$similar = $similarStmt->get_result();

// SEO Data
$metaDescription = substr(strip_tags($property['description'] ?? ''), 0, 155) . '...';
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$ogImage = $protocol . "://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/" . $imgSrc;

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
                <p style="margin-top:8px; font-size:0.82rem; color:var(--ink-soft); letter-spacing:0.04em;">
                    🔖 Property Ref: <strong style="color:var(--navy-800); font-family:monospace;"><?php echo clean($property['property_ref'] ?? ''); ?></strong>
                </p>
            </div>
        </div>

        <div class="details-hero-img" style="cursor:pointer;" onclick="openLightbox(0)">
            <img src="<?php echo clean($imgSrc); ?>" alt="<?php echo clean($property['title']); ?>" loading="lazy" onerror="this.src='assets/images/no-image.jpg'" id="hero-img-src">
        </div>

        <div class="details-grid">
            <div>
                <h2 style="font-size:1.4rem; color:var(--navy-900); margin-bottom:12px;">About this property</h2>
                <p style="color:var(--ink-soft); line-height: 1.6; margin-bottom: 24px;"><?php echo nl2br(clean($property['description'])); ?></p>

                <h3 style="font-size:1.25rem; color:var(--navy-900); margin-bottom:16px;">Features & Amenities</h3>
                <?php
                $amenities = isset($property['amenities']) && $property['amenities'] ? json_decode($property['amenities'], true) : [];
                if (!is_array($amenities)) $amenities = [];
                ?>
                <?php if (!empty($amenities)): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; margin-bottom: 32px;">
                    <?php foreach ($amenities as $amn): ?>
                    <div style="display: flex; align-items: center; gap: 8px; color: var(--ink-soft); font-size: 0.95rem;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--gold-500)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> 
                        <?php echo clean($amn); ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p style="color:var(--ink-soft); margin-bottom: 32px;">No specific amenities listed.</p>
                <?php endif; ?>

                <div class="spec-grid">
                    <div class="spec-item"><strong><?php echo (int)$property['bedrooms']; ?></strong><span>Bedrooms</span></div>
                    <div class="spec-item"><strong><?php echo (int)$property['bathrooms']; ?></strong><span>Bathrooms</span></div>
                    <div class="spec-item"><strong><?php echo (float)$property['size']; ?> m²</strong><span>Size</span></div>
                    <div class="spec-item"><strong><?php echo clean($property['type']); ?></strong><span>Type</span></div>
                </div>
                
                <?php
                $mediaStmt = $conn->prepare("SELECT * FROM property_media WHERE property_id = ? ORDER BY sort_order ASC");
                $mediaStmt->bind_param('i', $id);
                $mediaStmt->execute();
                $mediaRes = $mediaStmt->get_result();
                $images = [];
                $videos = [];
                while ($m = $mediaRes->fetch_assoc()) {
                    if ($m['media_type'] === 'video') $videos[] = $m;
                    else $images[] = $m;
                }
                ?>
                
                <?php if (!empty($images)): ?>
                <h3 style="font-size:1.2rem; margin:30px 0 15px;">Property Gallery</h3>
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(120px, 1fr)); gap:10px;" id="property-gallery-container">
                    <?php foreach ($images as $index => $img): ?>
                        <img src="image.php?type=media&id=<?php echo $img['id']; ?>" class="gallery-img-item" data-index="<?php echo $index + 1; ?>" style="width:100%; height:90px; object-fit:cover; border-radius:8px; cursor:pointer;" onclick="openLightbox(<?php echo $index + 1; ?>)">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($videos)): foreach($videos as $vid): ?>
                <div style="margin-top:20px;">
                    <h3 class="anim-stagger" style="font-size:1.2rem; margin-bottom:10px;">Property Video</h3>
                    <video src="image.php?type=media&id=<?php echo $vid['id']; ?>" controls style="width:100%; max-height:400px; background:#000;"></video>
                </div>
                <?php endforeach; endif; ?>
            </div>

            <div>
                <div class="side-card">
                    <span class="eyebrow">Price</span>
                    <div class="price-tag"><?php echo formatPrice($property['price']); ?><?php echo $property['status'] === 'For Rent' ? ' / mo' : ''; ?></div>
                    <h3 style="font-size:1rem; margin-bottom:6px;">Interested in this property?</h3>
                    <p style="color:var(--ink-soft); font-size:0.88rem; margin-bottom:18px;">Send us a message and one of our agents will get back to you shortly.</p>
                    
                    <?php if (!empty($_GET['inquiry_sent'])): ?>
                    <div class="alert alert-success" style="margin-bottom:16px; font-size:0.88rem;">✅ Your inquiry has been sent! We'll be in touch soon.</div>
                    <?php endif; ?>
                    
                    <form action="contact.php" method="POST" style="display:flex; flex-direction:column; gap:10px;">
                        <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                        <input type="hidden" name="property_id" value="<?php echo (int)$property['id']; ?>">
                        <input type="hidden" name="subject" value="Inquiry: <?php echo htmlspecialchars($property['title']); ?>">
                        <input type="hidden" name="redirect_to" value="property-details.php?id=<?php echo (int)$property['id']; ?>&inquiry_sent=1">
                        <input type="text" name="name" placeholder="Your name" required style="padding:10px 12px; border:1px solid var(--border); border-radius:6px; font-size:0.9rem; font-family:inherit;">
                        <input type="email" name="email" placeholder="Email address" required style="padding:10px 12px; border:1px solid var(--border); border-radius:6px; font-size:0.9rem; font-family:inherit;">
                        <input type="tel" name="phone" placeholder="Phone (optional)" style="padding:10px 12px; border:1px solid var(--border); border-radius:6px; font-size:0.9rem; font-family:inherit;">
                        <textarea name="message" rows="4" placeholder="Is this property still available?" required style="padding:10px 12px; border:1px solid var(--border); border-radius:6px; font-size:0.9rem; font-family:inherit; resize:vertical;"></textarea>
                        <button type="submit" class="btn btn-primary btn-block">Send Inquiry</button>
                    </form>
                    
                    <?php 
                    // Fetch agency phone, default to a placeholder Somaliland number if not set
                    $waPhone = preg_replace('/[^0-9]/', '', getSetting($conn, 'contact_phone', '252630000000'));
                    $waMessage = urlencode("Hi, I'm interested in the property: " . $property['title'] . ($property['property_ref'] ? " (Ref: " . $property['property_ref'] . ")" : ""));
                    $waLink = "https://wa.me/{$waPhone}?text={$waMessage}";
                    ?>
                    
                    <div style="text-align:center; margin: 16px 0;">
                        <span style="color:var(--ink-soft); font-size:0.85rem; font-weight:600; text-transform:uppercase; letter-spacing:1px;">OR</span>
                    </div>
                    
                    <a href="<?php echo $waLink; ?>" target="_blank" rel="noopener noreferrer" class="btn btn-block" style="background-color:#25D366; color:white; display:flex; align-items:center; justify-content:center; gap:8px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                        Chat on WhatsApp
                    </a>
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
                $sImg = ($p['image'] && $p['image'] !== 'no-image.jpg') ? 'image.php?type=property&id=' . $p['id'] : 'assets/images/no-image.jpg';
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

<!-- Sticky Mobile Contact Bar -->
<div class="mobile-sticky-contact">
    <a href="tel:<?php echo clean(isset($waPhone) ? '+' . $waPhone : ''); ?>" class="btn btn-outline" style="flex:1; display:flex; justify-content:center; align-items:center; gap:6px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
        Call
    </a>
    <a href="<?php echo clean(isset($waLink) ? $waLink : ''); ?>" target="_blank" rel="noopener noreferrer" class="btn" style="flex:1; background:#25D366; color:white; border-color:#25D366; display:flex; justify-content:center; align-items:center; gap:6px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
        WhatsApp
    </a>
</div>

<!-- Lightbox Modal -->
<div id="lightbox" class="lightbox" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.9); align-items:center; justify-content:center; flex-direction:column;">
    <button onclick="closeLightbox()" style="position:absolute; top:20px; right:20px; background:none; border:none; color:white; font-size:2rem; cursor:pointer;">&times;</button>
    <button onclick="changeLightboxImg(-1)" id="lightbox-prev" style="position:absolute; left:20px; top:50%; transform:translateY(-50%); background:rgba(255,255,255,0.2); border:none; color:white; font-size:2rem; padding:10px 15px; cursor:pointer; border-radius:50%;">&lsaquo;</button>
    <img id="lightbox-img" src="" style="max-width:90%; max-height:80vh; object-fit:contain; border-radius:8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
    <button onclick="changeLightboxImg(1)" id="lightbox-next" style="position:absolute; right:20px; top:50%; transform:translateY(-50%); background:rgba(255,255,255,0.2); border:none; color:white; font-size:2rem; padding:10px 15px; cursor:pointer; border-radius:50%;">&rsaquo;</button>
    <div id="lightbox-counter" style="color:white; margin-top:20px; font-size:1rem; font-family:'Inter', sans-serif;"></div>
</div>

<script>
let lightboxImages = [];
let currentLightboxIndex = 0;

document.addEventListener('DOMContentLoaded', () => {
    // Gather all images (hero + gallery)
    const heroImg = document.getElementById('hero-img-src');
    if(heroImg) lightboxImages.push(heroImg.src);
    
    document.querySelectorAll('.gallery-img-item').forEach(img => {
        lightboxImages.push(img.src);
    });
    
    // Hide arrows if only 1 image
    if(lightboxImages.length <= 1) {
        document.getElementById('lightbox-prev').style.display = 'none';
        document.getElementById('lightbox-next').style.display = 'none';
    }
});

function openLightbox(index) {
    if(lightboxImages.length === 0) return;
    currentLightboxIndex = index;
    updateLightbox();
    document.getElementById('lightbox').style.display = 'flex';
    document.body.style.overflow = 'hidden'; // prevent scrolling
}

function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function changeLightboxImg(dir) {
    currentLightboxIndex += dir;
    if (currentLightboxIndex < 0) currentLightboxIndex = lightboxImages.length - 1;
    if (currentLightboxIndex >= lightboxImages.length) currentLightboxIndex = 0;
    updateLightbox();
}

function updateLightbox() {
    document.getElementById('lightbox-img').src = lightboxImages[currentLightboxIndex];
    document.getElementById('lightbox-counter').textContent = (currentLightboxIndex + 1) + " / " + lightboxImages.length;
}

// Close on escape key or outside click
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') changeLightboxImg(-1);
    if (e.key === 'ArrowRight') changeLightboxImg(1);
});
document.getElementById('lightbox').addEventListener('click', (e) => {
    if(e.target.id === 'lightbox') closeLightbox();
});
</script>

<?php include 'includes/footer.php'; ?>
