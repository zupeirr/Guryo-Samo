<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$pageTitle = 'Home';
$activePage = 'home';
$baseUrl = '';

// ---- Stats for the strip ----
$totalProperties = $conn->query("SELECT COUNT(*) AS c FROM properties")->fetch_assoc()['c'];
$forSaleCount   = $conn->query("SELECT COUNT(*) AS c FROM properties WHERE status='For Sale'")->fetch_assoc()['c'];
$forRentCount   = $conn->query("SELECT COUNT(*) AS c FROM properties WHERE status='For Rent'")->fetch_assoc()['c'];
$soldCount      = $conn->query("SELECT COUNT(*) AS c FROM properties WHERE status='Sold'")->fetch_assoc()['c'];

// ---- Featured properties (latest 6) ----
$featured = $conn->query("SELECT * FROM properties ORDER BY created_at DESC LIMIT 6");

include 'includes/header.php';
?>

<section class="hero">
    <div class="container">
        <div class="hero-content">
            <span class="eyebrow" style="color:#e9c874;">Find Your Place</span>
            <h1>Discover a home that fits the life you're building.</h1>
            <p>Browse verified listings for sale and for rent across the city &mdash; from cozy studios to spacious family villas.</p>
            <div class="hero-actions">
                <a href="properties.php" class="btn btn-primary">Browse Properties</a>
                <a href="contact.php" class="btn btn-outline">Contact an Agent</a>
            </div>
        </div>
    </div>
</section>

<div class="container">
    <form class="search-card" action="properties.php" method="get" id="searchForm">
        <div class="search-grid">
            <div class="field">
                <label for="q_location">Location</label>
                <input type="text" id="q_location" name="location" placeholder="e.g. Hargeisa, Berbera...">
            </div>
            <div class="field">
                <label for="q_type">Property Type</label>
                <select id="q_type" name="type">
                    <option value="">Any Type</option>
                    <option value="House">House</option>
                    <option value="Apartment">Apartment</option>
                    <option value="Land">Land</option>
                    <option value="Office">Office</option>
                    <option value="Commercial">Commercial</option>
                    <option value="Villa">Villa</option>
                </select>
            </div>
            <div class="field">
                <label for="q_max">Max Price</label>
                <input type="number" id="q_max" name="max_price" placeholder="e.g. 300000">
            </div>
            <div class="field" style="align-self:end;">
                <button type="submit" class="btn btn-primary btn-block">Search</button>
            </div>
        </div>
    </form>
</div>

<section class="section">
    <div class="container">
        <div class="stats-strip">
            <div class="stat"><h3><?php echo (int)$totalProperties; ?></h3><span>Total Listings</span></div>
            <div class="stat"><h3><?php echo (int)$forSaleCount; ?></h3><span>For Sale</span></div>
            <div class="stat"><h3><?php echo (int)$forRentCount; ?></h3><span>For Rent</span></div>
            <div class="stat"><h3><?php echo (int)$soldCount; ?></h3><span>Sold</span></div>
        </div>
    </div>
</section>

<section class="section bg-alt">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Featured Listings</span>
            <h2 class="section-title">Recently Added Properties</h2>
            <p class="section-sub" style="margin-left:auto;margin-right:auto;">A selection of our newest properties for sale and rent.</p>
        </div>

        <div class="property-grid">
            <?php if ($featured->num_rows > 0): while ($p = $featured->fetch_assoc()):
                $statusClass = $p['status'] === 'For Sale' ? 'sale' : ($p['status'] === 'For Rent' ? 'rent' : 'sold');
                $imgSrc = ($p['image'] && $p['image'] !== 'no-image.jpg') ? 'uploads/' . $p['image'] : 'assets/images/no-image.jpg';
            ?>
            <div class="property-card">
                <div class="property-thumb">
                    <span class="badge <?php echo $statusClass; ?>"><?php echo clean($p['status']); ?></span>
                    <img src="<?php echo clean($imgSrc); ?>" alt="<?php echo clean($p['title']); ?>" onerror="this.src='assets/images/no-image.jpg'">
                </div>
                <div class="property-body">
                    <div class="property-price"><?php echo formatPrice($p['price']); ?><?php echo $p['status'] === 'For Rent' ? ' / mo' : ''; ?></div>
                    <h3 class="property-title"><?php echo clean($p['title']); ?></h3>
                    <div class="property-location">📍 <?php echo clean($p['location']); ?></div>
                    <div class="property-meta">
                        <span>🛏 <?php echo (int)$p['bedrooms']; ?> Beds</span>
                        <span>🛁 <?php echo (int)$p['bathrooms']; ?> Baths</span>
                        <span>📐 <?php echo (float)$p['size']; ?> m²</span>
                    </div>
                    <a href="property-details.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-ghost btn-block">View Details</a>
                </div>
            </div>
            <?php endwhile; else: ?>
                <p class="empty-state">No properties available yet. Please check back soon.</p>
            <?php endif; ?>
        </div>

        <div class="text-center mt-3">
            <a href="properties.php" class="btn btn-navy">View All Properties</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Why Choose Us</span>
            <h2 class="section-title">A Simple, Trustworthy Way To Find Property</h2>
        </div>
        <div class="value-grid">
            <div class="value-item">
                <h3>Verified Listings</h3>
                <p style="margin-top:8px; color:var(--ink-soft); font-size:0.92rem;">Every property is reviewed by our team before it goes live on the site.</p>
            </div>
            <div class="value-item">
                <h3>Transparent Pricing</h3>
                <p style="margin-top:8px; color:var(--ink-soft); font-size:0.92rem;">Clear pricing information with no hidden fees or surprises.</p>
            </div>
            <div class="value-item">
                <h3>Dedicated Support</h3>
                <p style="margin-top:8px; color:var(--ink-soft); font-size:0.92rem;">Our team is on hand to help you through every step of the process.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
