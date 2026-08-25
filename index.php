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
                <a href="properties.php" class="btn btn-primary">Browse Properties <span class="btn-arrow">&rarr;</span></a>
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
                <select id="q_type" name="type"> <!--select waa dropdown menu ,Waxay user-ka siinaysaa inuu hal option ka doorto-->
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

<section style="padding: 20px 0 32px;">
    <div class="container">
        <div class="stats-strip">
            <div class="stat">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                </div>
                <h3 class="counter" data-target="<?php echo (int)$totalProperties; ?>">0</h3> <!--data-target="42" = Stores this in a data attribute -->
                <span>Properties</span>
            </div>
            <div class="stat">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                </div>
                <h3 class="counter" data-target="<?php echo (int)$forSaleCount; ?>">0</h3>
                <span>For Sale</span>
            </div>
            <div class="stat">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg>
                </div>
                <h3 class="counter" data-target="<?php echo (int)$forRentCount; ?>">0</h3>
                <span>For Rent</span>
            </div>
            <div class="stat">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <h3 class="counter" data-target="<?php echo (int)$soldCount; ?>">0</h3>
                <span>Sold</span>
            </div>
        </div>
    </div>
</section>

<section class="section bg-alt" style="padding-top: 48px;">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Featured Listings</span>
            <h2 class="section-title">Recently Added Properties</h2>
            <p class="section-sub" style="margin-left:auto;margin-right:auto;">A selection of our newest properties for sale and rent.</p>
        </div>

        <div class="property-grid">
            <?php if ($featured->num_rows > 0): while ($p = $featured->fetch_assoc()):                   //--loop oo hubinaya haddii database-ka laga helay properties,kadibna mid-mid ayuu u soo qaadanayaa -->
                $statusClass = $p['status'] === 'For Sale' ? 'sale' : ($p['status'] === 'For Rent' ? 'rent' : 'sold');            //--- This is a ternary operator (if/else)
                $imgSrc = ($p['image'] && $p['image'] !== 'no-image.jpg') ? 'image.php?type=property&id=' . $p['id'] : 'assets/images/no-image.jpg';
            ?>
            <div class="property-card">
                <div class="property-thumb">
                    <span class="badge <?php echo $statusClass; ?>"><?php echo clean($p['status']); ?></span>
                    <button class="favorite-btn" aria-label="Save to favorites">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    </button>
                    <img src="<?php echo clean($imgSrc); ?>" alt="<?php echo clean($p['title']); ?>" onerror="this.src='assets/images/no-image.jpg'">  <!--//---onerror = If image fails to load, show placeholder -->
                </div>
                <div class="property-body">
                    <div class="property-price"><?php echo formatPrice($p['price']); ?><?php echo $p['status'] === 'For Rent' ? ' / mo' : ''; ?></div>
                    <h3 class="property-title"><?php echo clean($p['title']); ?></h3>
                    <div class="property-location">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <?php echo clean($p['location']); ?>
                    </div>
                    <div class="property-meta">
                        <span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                            <?php echo (int)$p['bedrooms']; ?> Beds
                        </span>
                        <span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h16"></path><path d="M4 12a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6z"></path><path d="M6 12V7a2 2 0 0 1 2-2h4"></path></svg>
                            <?php echo (int)$p['bathrooms']; ?> Baths
                        </span>
                        <span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect></svg>
                            <?php echo (float)$p['size']; ?> m²
                        </span>
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

<section class="section why-section">
    <div class="container">
        <div class="section-head services-section-head">
            <div class="eyebrow-wrapper">
                <span class="eyebrow-line"></span>
                <span class="eyebrow" style="margin-bottom:0;">WHY CHOOSE GURYO SAMO</span>
                <span class="eyebrow-line"></span>
            </div>
            <h2 class="section-title" style="font-size: 2.4rem; max-width: 640px; margin: 16px auto 16px; line-height: 1.3;">A Simple, Trusted Way to Find Your Next Property</h2>
            <p class="section-sub" style="max-width: 560px; margin: 0 auto 40px; font-size: 1.05rem;">Discover properties with confidence, clarity and support at every step of the way.</p>
        </div>

        <div class="why-grid">
            <!-- Card 1 -->
            <div class="why-card">
                <div class="why-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <h3>Verified Listings</h3>
                <p>Every property is carefully reviewed by our team before it appears on Guryo Samo. No guesswork, no surprises.</p>
                <a href="services.php" class="why-cta">Learn More <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a>
            </div>

            <!-- Card 2 -->
            <div class="why-card">
                <div class="why-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
                <h3>Transparent Pricing</h3>
                <p>Know exactly what you're paying. Clear, honest pricing with no hidden fees or unexpected costs at any stage.</p>
                <a href="services.php" class="why-cta">Learn More <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a>
            </div>

            <!-- Card 3 -->
            <div class="why-card">
                <div class="why-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <h3>Dedicated Support</h3>
                <p>Our team is here to guide you through every step of your property journey &mdash; from search to final handover.</p>
                <a href="contact.php" class="why-cta">Learn More <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a>
            </div>
        </div>

        <!-- Trust Bar -->
        <div class="trust-bar">
            <div class="trust-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Verified Properties
            </div>
            <div class="trust-divider"></div>
            <div class="trust-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Clear Pricing
            </div>
            <div class="trust-divider"></div>
            <div class="trust-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Local Support
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
