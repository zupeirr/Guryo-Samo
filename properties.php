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
$minPrice = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
$maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;
$sort     = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';

$allowedTypes = ['House','Apartment','Land','Office','Commercial','Villa'];
$allowedStatus = ['For Sale','For Rent','Sold'];

// ---- Build query safely with prepared statement ----
$sql = "SELECT * FROM properties WHERE deleted_at IS NULL";
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
if ($minPrice !== null && $minPrice >= 0) {
    $sql .= " AND price >= ?";
    $params[] = $minPrice;
    $types .= 'd';
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
    $sql .= " ORDER BY view_count DESC, created_at DESC LIMIT ? OFFSET ?"; 
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
                <label>Price Range</label>
                <div style="display:flex; gap:8px;">
                    <input type="number" name="min_price" value="<?php echo $minPrice ? clean($minPrice) : ''; ?>" placeholder="Min" style="width:50%;">
                    <input type="number" name="max_price" value="<?php echo $maxPrice ? clean($maxPrice) : ''; ?>" placeholder="Max" style="width:50%;">
                </div>
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
                <input type="hidden" name="min_price" value="<?php echo $minPrice ? clean($minPrice) : ''; ?>">
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
                
                $isNew = (strtotime($p['created_at']) > strtotime('-7 days'));
                $isPriceReduced = (isset($p['previous_price']) && $p['previous_price'] > 0 && $p['previous_price'] > $p['price'] && strtotime($p['price_changed_at']) > strtotime('-30 days'));
            ?>
            <div class="property-card">
                <div class="property-thumb">
                    <div style="position:absolute; top:12px; left:12px; display:flex; flex-direction:column; gap:6px; z-index:10;">
                        <span class="badge <?php echo $statusClass; ?>"><?php echo clean($p['status']); ?></span>
                        <?php if ($isPriceReduced): ?>
                            <span class="badge" style="background:#ff3b30; color:white; animation: pulse 2s infinite;">🔥 Price Reduced</span>
                        <?php elseif ($isNew): ?>
                            <span class="badge" style="background:#34c759; color:white;">✨ New</span>
                        <?php endif; ?>
                    </div>
                    <button class="favorite-btn" aria-label="Save to favorites">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    </button>
                    <a href="property-details.php?id=<?php echo $p['id']; ?>">
                        <img src="<?php echo clean($imgSrc); ?>" alt="<?php echo clean($p['title']); ?>" loading="lazy" onerror="this.src='assets/images/no-image.jpg'">
                    </a>
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
                <div class="empty-state" style="text-align:center; padding: 40px 20px; grid-column: 1 / -1;">
                    <p style="margin-bottom:16px;">No properties match your search. Try adjusting your filters.</p>
                    <a href="properties.php" class="btn btn-navy" style="display:inline-block;">Clear Filters</a>
                </div>
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

<?php include 'includes/footer.php'; ?>
