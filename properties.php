<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$pageTitle = 'Properties';
$activePage = 'properties';
$baseUrl = '';

// ---- Read & sanitize filters from the query string ----
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$type     = isset($_GET['type']) ? trim($_GET['type']) : '';
$status   = isset($_GET['status']) ? trim($_GET['status']) : '';
$maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;
$sort     = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';

$allowedTypes = ['House','Apartment','Land','Office','Commercial','Villa'];
$allowedStatus = ['For Sale','For Rent','Sold'];

// ---- Build query safely with prepared statement ----
$sql = "SELECT * FROM properties WHERE 1=1";
$params = [];
$types = '';

if ($location !== '') {
    $sql .= " AND location LIKE ?";
    $params[] = '%' . $location . '%';
    $types .= 's';
}
if ($type !== '' && in_array($type, $allowedTypes, true)) {
    $sql .= " AND type = ?";
    $params[] = $type;
    $types .= 's';
}
if ($status !== '' && in_array($status, $allowedStatus, true)) {
    $sql .= " AND status = ?";
    $params[] = $status;
    $types .= 's';
}
if ($maxPrice !== null && $maxPrice > 0) {
    $sql .= " AND price <= ?";
    $params[] = $maxPrice;
    $types .= 'd';
}

// ---- Pagination ----
$perPage = 9;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

// count total matches
$countSql = str_replace('SELECT *', 'SELECT COUNT(*) AS total', $sql);
$countStmt = $conn->prepare($countSql);
if (!empty($params)) { $countStmt->bind_param($types, ...$params); }
$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = max(1, ceil($totalRows / $perPage));

// Sorting logic
if ($sort === 'price_asc') {
    $sql .= " ORDER BY price ASC LIMIT ? OFFSET ?";
} elseif ($sort === 'price_desc') {
    $sql .= " ORDER BY price DESC LIMIT ? OFFSET ?";
} elseif ($sort === 'popular') {
    // Assuming views or just fallback to newest for now
    $sql .= " ORDER BY id ASC LIMIT ? OFFSET ?"; 
} else {
    $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
}

$params[] = $perPage;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

include 'includes/header.php';
?>

<section class="hero" style="padding: 70px 0;">
    <div class="container">
        <div class="section-head" style="margin-bottom:0;">
            <span class="eyebrow" style="color:var(--gold-500);">OUR PROPERTIES</span>
            <h1 style="color:#fff; font-size: 2.2rem; margin-bottom: 12px;">Find Your Next Property</h1>
            <p style="color:rgba(255,255,255,0.8); max-width: 560px; margin:0 auto; font-size: 1.05rem;">Browse homes, apartments, land and commercial spaces across our network.</p>
        </div>
    </div>
</section>

<div class="container">
    <form class="search-card" method="get" id="searchForm">
        <div class="search-grid" style="grid-template-columns:1fr 1fr 1fr 1fr auto;">
            <div class="field">
                <label for="f_location">Location</label>
                <input type="text" id="f_location" name="location" value="<?php echo clean($location); ?>" placeholder="e.g. Hargeisa">
            </div>
            <div class="field">
                <label for="f_type">Type</label>
                <select id="f_type" name="type">
                    <option value="">Any Type</option>
                    <?php foreach ($allowedTypes as $t): ?>
                        <option value="<?php echo $t; ?>" <?php echo $type === $t ? 'selected' : ''; ?>><?php echo $t; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="f_status">Status</label>
                <select id="f_status" name="status">
                    <option value="">Any Status</option>
                    <?php foreach ($allowedStatus as $s): ?>
                        <option value="<?php echo $s; ?>" <?php echo $status === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="f_max">Max Price</label>
                <input type="number" id="f_max" name="max_price" value="<?php echo $maxPrice ? clean($maxPrice) : ''; ?>" placeholder="e.g. 300000">
            </div>
            <div class="field" style="align-self:end;">
                <button type="submit" class="btn btn-primary btn-block">Search</button>
            </div>
        </div>
    </form>
</div>

<section class="section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
            <p style="color:var(--ink-soft); font-weight: 600; margin: 0; font-size: 1.05rem;">
                <?php echo (int)$totalRows; ?> Propert<?php echo $totalRows === 1 ? 'y' : 'ies'; ?> Available
            </p>
            <form method="get" id="sortForm" style="display: flex; align-items: center; gap: 12px;">
                <!-- Preserve existing filters -->
                <input type="hidden" name="location" value="<?php echo clean($location); ?>">
                <input type="hidden" name="type" value="<?php echo clean($type); ?>">
                <input type="hidden" name="status" value="<?php echo clean($status); ?>">
                <input type="hidden" name="max_price" value="<?php echo $maxPrice ? clean($maxPrice) : ''; ?>">
                
                <label for="sort" style="font-size: 0.9rem; font-weight: 600; color: var(--navy-800);">Sort by:</label>
                <select name="sort" id="sort" onchange="document.getElementById('sortForm').submit()" style="padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; background: #fff; font-size: 0.9rem; color: var(--navy-900); font-weight: 500; cursor: pointer; outline: none;">
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                    <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                </select>
            </form>
        </div>

        <div class="property-grid">
            <?php if ($result->num_rows > 0): while ($p = $result->fetch_assoc()):
                $statusClass = $p['status'] === 'For Sale' ? 'sale' : ($p['status'] === 'For Rent' ? 'rent' : 'sold');
                $imgSrc = ($p['image'] && $p['image'] !== 'no-image.jpg') ? 'image.php?type=property&id=' . $p['id'] : 'assets/images/no-image.jpg';
            ?>
            <div class="property-card">
                <div class="property-thumb">
                    <span class="badge <?php echo $statusClass; ?>"><?php echo clean($p['status']); ?></span>
                    <button class="favorite-btn" aria-label="Save to favorites">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    </button>
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
                <p class="empty-state">No properties match your search. Try adjusting your filters.</p>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php
            $queryWithoutPage = $_GET;
            for ($i = 1; $i <= $totalPages; $i++):
                $queryWithoutPage['page'] = $i;
                $qs = http_build_query($queryWithoutPage);
            ?>
                <?php if ($i === $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="properties.php?<?php echo $qs; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ===== PREMIUM SUBSCRIBE SECTION ===== -->
<section class="subscribe-section" id="subscribeSection">

    <!-- Background layers -->
    <div class="subscribe-bg-gradient"></div>
    <div class="subscribe-glow"></div>

    <!-- Faint building silhouette SVG -->
    <svg class="subscribe-skyline" viewBox="0 0 1200 200" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" preserveAspectRatio="xMidYMax meet">
        <g fill="none" stroke="rgba(200,155,60,0.07)" stroke-width="1.5">
            <!-- Far left tower -->
            <rect x="40" y="80" width="60" height="120"/>
            <rect x="55" y="60" width="30" height="22"/>
            <line x1="70" y1="30" x2="70" y2="60"/>
            <rect x="60" y="95" width="8" height="10"/>
            <rect x="75" y="95" width="8" height="10"/>
            <rect x="60" y="115" width="8" height="10"/>
            <rect x="75" y="115" width="8" height="10"/>
            <!-- Left mid building -->
            <rect x="130" y="100" width="90" height="100"/>
            <rect x="145" y="85" width="60" height="17"/>
            <rect x="140" y="110" width="12" height="14"/>
            <rect x="160" y="110" width="12" height="14"/>
            <rect x="180" y="110" width="12" height="14"/>
            <rect x="140" y="132" width="12" height="14"/>
            <rect x="160" y="132" width="12" height="14"/>
            <rect x="180" y="132" width="12" height="14"/>
            <rect x="140" y="154" width="12" height="14"/>
            <rect x="160" y="154" width="12" height="14"/>
            <rect x="180" y="154" width="12" height="14"/>
            <!-- Center tall tower -->
            <rect x="470" y="30" width="80" height="170"/>
            <rect x="485" y="10" width="50" height="22"/>
            <line x1="510" y1="0" x2="510" y2="10"/>
            <rect x="480" y="50" width="14" height="16"/>
            <rect x="502" y="50" width="14" height="16"/>
            <rect x="524" y="50" width="14" height="16"/>
            <rect x="480" y="76" width="14" height="16"/>
            <rect x="502" y="76" width="14" height="16"/>
            <rect x="524" y="76" width="14" height="16"/>
            <rect x="480" y="102" width="14" height="16"/>
            <rect x="502" y="102" width="14" height="16"/>
            <rect x="524" y="102" width="14" height="16"/>
            <rect x="480" y="128" width="14" height="16"/>
            <rect x="502" y="128" width="14" height="16"/>
            <rect x="524" y="128" width="14" height="16"/>
            <!-- Center-right block -->
            <rect x="570" y="70" width="100" height="130"/>
            <rect x="585" y="55" width="70" height="17"/>
            <rect x="580" y="85" width="14" height="16"/>
            <rect x="602" y="85" width="14" height="16"/>
            <rect x="624" y="85" width="14" height="16"/>
            <rect x="646" y="85" width="14" height="16"/>
            <rect x="580" y="111" width="14" height="16"/>
            <rect x="602" y="111" width="14" height="16"/>
            <rect x="624" y="111" width="14" height="16"/>
            <rect x="646" y="111" width="14" height="16"/>
            <rect x="580" y="137" width="14" height="16"/>
            <rect x="602" y="137" width="14" height="16"/>
            <rect x="624" y="137" width="14" height="16"/>
            <rect x="646" y="137" width="14" height="16"/>
            <!-- Right tower -->
            <rect x="950" y="60" width="70" height="140"/>
            <rect x="963" y="44" width="44" height="18"/>
            <line x1="985" y1="20" x2="985" y2="44"/>
            <rect x="958" y="75" width="12" height="14"/>
            <rect x="978" y="75" width="12" height="14"/>
            <rect x="998" y="75" width="12" height="14"/>
            <rect x="958" y="97" width="12" height="14"/>
            <rect x="978" y="97" width="12" height="14"/>
            <rect x="998" y="97" width="12" height="14"/>
            <rect x="958" y="119" width="12" height="14"/>
            <rect x="978" y="119" width="12" height="14"/>
            <rect x="998" y="119" width="12" height="14"/>
            <!-- Far right building -->
            <rect x="1070" y="90" width="90" height="110"/>
            <rect x="1085" y="75" width="60" height="17"/>
            <rect x="1080" y="105" width="12" height="14"/>
            <rect x="1100" y="105" width="12" height="14"/>
            <rect x="1120" y="105" width="12" height="14"/>
            <rect x="1080" y="127" width="12" height="14"/>
            <rect x="1100" y="127" width="12" height="14"/>
            <rect x="1120" y="127" width="12" height="14"/>
            <!-- Ground line -->
            <line x1="0" y1="200" x2="1200" y2="200"/>
        </g>
    </svg>

    <!-- Animated floating orbs -->
    <div class="subscribe-orb subscribe-orb-1"></div>
    <div class="subscribe-orb subscribe-orb-2"></div>

    <div class="container subscribe-content">
        <!-- Staggered animated elements -->
        <div class="sub-reveal sub-reveal-1">
            <span class="sub-eyebrow">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:5px;margin-top:-2px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,12 2,6"/></svg>
                Stay in the Loop
            </span>
        </div>
        <h2 class="sub-reveal sub-reveal-2">
            Stay Updated &amp; Subscribe<br>to Our Blog Content!
        </h2>
        <p class="sub-reveal sub-reveal-3">
            Get the latest real estate news, property updates, and market insights<br>delivered straight to your inbox — no spam, ever.
        </p>
        <form action="#" method="POST" class="subscribe-form sub-reveal sub-reveal-4" id="subscribeForm">
            <div class="subscribe-input-wrap">
                <svg class="subscribe-envelope" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,12 2,6"/></svg>
                <input type="email" name="email" id="subEmail" placeholder="Enter your email address..." required autocomplete="email">
            </div>
            <button type="submit" class="subscribe-btn" id="subscribeBtn">
                <span class="subscribe-btn-text">Subscribe</span>
                <span class="subscribe-btn-arrow">→</span>
                <span class="subscribe-btn-shine"></span>
            </button>
        </form>

        <!-- Feedback message -->
        <div id="subFeedback" class="sub-feedback" aria-live="polite"></div>

        <p class="sub-reveal sub-reveal-5 sub-trust">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:4px;margin-top:-2px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
            We respect your privacy. Unsubscribe anytime.
        </p>
    </div>
</section>

<style>
/* ===== SUBSCRIBE SECTION ===== */
.subscribe-section {
    position: relative;
    overflow: hidden;
    padding: 100px 20px;
    text-align: center;
    background: linear-gradient(160deg, #101d2c 0%, #16324f 50%, #101d2c 100%);
}

/* Gradient overlay */
.subscribe-bg-gradient {
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse 80% 60% at 50% 60%, rgba(200,155,60,0.08) 0%, transparent 70%);
    pointer-events: none;
}

/* Gold glow behind form */
.subscribe-glow {
    position: absolute;
    left: 50%;
    top: 55%;
    transform: translate(-50%, -50%);
    width: 600px;
    height: 300px;
    background: radial-gradient(ellipse, rgba(200,155,60,0.14) 0%, transparent 70%);
    pointer-events: none;
    animation: glowPulse 4s ease-in-out infinite;
}
@keyframes glowPulse {
    0%, 100% { opacity: 0.7; transform: translate(-50%, -50%) scale(1); }
    50%       { opacity: 1;   transform: translate(-50%, -50%) scale(1.12); }
}

/* Building silhouette */
.subscribe-skyline {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 200px;
    pointer-events: none;
}

/* Floating orbs */
.subscribe-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    pointer-events: none;
    animation: orbFloat 10s ease-in-out infinite;
}
.subscribe-orb-1 {
    width: 280px; height: 280px;
    background: rgba(200,155,60,0.07);
    top: -60px; left: -80px;
    animation-delay: 0s;
}
.subscribe-orb-2 {
    width: 220px; height: 220px;
    background: rgba(31,68,104,0.4);
    bottom: -40px; right: -60px;
    animation-delay: -5s;
}
@keyframes orbFloat {
    0%, 100% { transform: translateY(0) translateX(0); }
    33%       { transform: translateY(-20px) translateX(10px); }
    66%       { transform: translateY(10px) translateX(-10px); }
}

/* Content wrapper */
.subscribe-content {
    position: relative;
    z-index: 2;
}

/* Eyebrow */
.sub-eyebrow {
    display: inline-block;
    font-size: 0.78rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--gold-500);
    font-weight: 700;
    margin-bottom: 16px;
    background: rgba(200,155,60,0.1);
    padding: 6px 14px;
    border-radius: 20px;
    border: 1px solid rgba(200,155,60,0.2);
}

/* Headline */
.subscribe-section h2 {
    font-size: 2.4rem;
    color: #fff;
    margin-bottom: 16px;
    line-height: 1.25;
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
}

/* Subtitle */
.subscribe-section p {
    color: rgba(255,255,255,0.72);
    font-size: 1.05rem;
    margin-bottom: 36px;
    line-height: 1.7;
}

/* Staggered fade-up animations */
.sub-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.7s ease, transform 0.7s ease;
}
.sub-reveal.visible { opacity: 1; transform: translateY(0); }
.sub-reveal-1.visible { transition-delay: 0s; }
.sub-reveal-2.visible { transition-delay: 0.15s; }
.sub-reveal-3.visible { transition-delay: 0.28s; }
.sub-reveal-4.visible { transition-delay: 0.42s; }
.sub-reveal-5.visible { transition-delay: 0.56s; }

/* Form layout */
.subscribe-form {
    display: flex;
    max-width: 520px;
    margin: 0 auto 20px;
    gap: 0;
    border-radius: 10px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3), 0 0 0 1px rgba(200,155,60,0.1);
    overflow: hidden;
}

/* Input wrapper */
.subscribe-input-wrap {
    flex: 1;
    position: relative;
    display: flex;
    align-items: center;
}
.subscribe-envelope {
    position: absolute;
    left: 16px;
    color: rgba(255,255,255,0.35);
    transition: color 0.3s;
    pointer-events: none;
    flex-shrink: 0;
}
.subscribe-input-wrap:focus-within .subscribe-envelope {
    color: var(--gold-500);
}
.subscribe-input-wrap input {
    width: 100%;
    padding: 16px 16px 16px 46px;
    border: 2px solid transparent;
    border-right: none;
    border-radius: 10px 0 0 10px;
    background: rgba(255,255,255,0.08);
    color: #fff;
    font-size: 0.97rem;
    outline: none;
    backdrop-filter: blur(4px);
    transition: background 0.3s, border-color 0.3s, box-shadow 0.3s;
}
.subscribe-input-wrap input::placeholder { color: rgba(255,255,255,0.4); }
.subscribe-input-wrap input:focus {
    background: rgba(255,255,255,0.13);
    border-color: var(--gold-500);
    box-shadow: inset 0 0 0 1px rgba(200,155,60,0.2), 0 0 16px rgba(200,155,60,0.15);
}

/* Subscribe button */
.subscribe-btn {
    position: relative;
    overflow: hidden;
    padding: 16px 28px;
    background: var(--gold-500);
    color: var(--navy-900);
    font-weight: 700;
    font-size: 0.97rem;
    border: none;
    border-radius: 0 10px 10px 0;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
    transition: background 0.25s, transform 0.2s, box-shadow 0.25s;
    font-family: 'Inter', sans-serif;
}
.subscribe-btn:hover {
    background: #d4a72c;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(200,155,60,0.45);
}
.subscribe-btn:active {
    transform: scale(0.97) translateY(0);
    box-shadow: none;
}
.subscribe-btn-arrow {
    display: inline-block;
    transition: transform 0.3s ease;
}
.subscribe-btn:hover .subscribe-btn-arrow {
    transform: translateX(4px);
}

/* Shine sweep effect */
.subscribe-btn-shine {
    position: absolute;
    top: 0; left: -100%;
    width: 60%;
    height: 100%;
    background: linear-gradient(120deg, transparent, rgba(255,255,255,0.35), transparent);
    transition: left 0.5s ease;
    pointer-events: none;
}
.subscribe-btn:hover .subscribe-btn-shine { left: 150%; }

/* Trust line */
.sub-trust {
    font-size: 0.82rem;
    color: rgba(255,255,255,0.45);
    margin-bottom: 0;
}

@media (max-width: 600px) {
    .subscribe-section { padding: 72px 16px; }
    .subscribe-section h2 { font-size: 1.75rem; }
    .subscribe-form { flex-direction: column; border-radius: 10px; overflow: visible; box-shadow: none; }
    .subscribe-input-wrap input { border-radius: 10px; border: 2px solid rgba(200,155,60,0.3); border-right: 2px solid rgba(200,155,60,0.3); }
    .subscribe-btn { border-radius: 10px; justify-content: center; }
}

/* Feedback message */
.sub-feedback {
    min-height: 40px;
    margin: 14px auto 0;
    max-width: 520px;
    display: none;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 600;
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 0.4s ease, transform 0.4s ease;
}
.sub-feedback.show {
    display: flex;
    opacity: 1;
    transform: translateY(0);
}
.sub-feedback.success {
    background: rgba(63,122,92,0.18);
    border: 1px solid rgba(63,122,92,0.4);
    color: #7ed9a8;
}
.sub-feedback.error {
    background: rgba(176,65,62,0.18);
    border: 1px solid rgba(176,65,62,0.4);
    color: #f7a5a3;
}
</style>

<script>
// Staggered reveal on scroll
(function () {
    const reveals = document.querySelectorAll('.sub-reveal');
    if (!reveals.length) return;

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.15 });

    reveals.forEach(function(el) { observer.observe(el); });
})();

// Subscribe form validation & feedback
(function () {
    const form    = document.getElementById('subscribeForm');
    const input   = document.getElementById('subEmail');
    const btn     = document.getElementById('subscribeBtn');
    const feedback = document.getElementById('subFeedback');
    if (!form) return;

    function showFeedback(type, icon, message) {
        feedback.className = 'sub-feedback ' + type;
        feedback.innerHTML = icon + ' ' + message;
        // Force reflow so transition fires
        feedback.style.display = 'flex';
        feedback.offsetHeight;
        feedback.classList.add('show');
    }

    function hideFeedback() {
        feedback.classList.remove('show');
        setTimeout(function() { feedback.style.display = 'none'; }, 400);
    }

    // Real-time email format validation
    input.addEventListener('input', function () {
        if (input.validity.typeMismatch || (input.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value))) {
            input.style.borderColor = 'rgba(176,65,62,0.7)';
        } else {
            input.style.borderColor = '';
        }
        hideFeedback();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const emailVal = input.value.trim();

        // Validate
        if (!emailVal || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
            // Error state
            input.style.borderColor = 'rgba(176,65,62,0.8)';
            input.focus();
            showFeedback(
                'error',
                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
                'Please enter a valid email address.'
            );
            return;
        }

        // Loading state
        btn.disabled = true;
        btn.querySelector('.subscribe-btn-text').textContent = 'Subscribing...';

        // Simulate async submission (replace with real fetch if needed)
        setTimeout(function () {
            // Success state
            form.style.opacity = '0.4';
            form.style.pointerEvents = 'none';
            input.style.borderColor = '';
            showFeedback(
                'success',
                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
                'You&rsquo;re subscribed! Thanks for joining &mdash; we&rsquo;ll be in touch.'
            );

            // Reset after 5s
            setTimeout(function () {
                form.reset();
                form.style.opacity = '';
                form.style.pointerEvents = '';
                btn.disabled = false;
                btn.querySelector('.subscribe-btn-text').textContent = 'Subscribe';
                hideFeedback();
            }, 5000);
        }, 900);
    });
})();
</script>

<?php include 'includes/footer.php'; ?>

