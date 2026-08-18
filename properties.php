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

$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

include 'includes/header.php';
?>

<section class="section" style="padding-bottom:0;">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Listings</span>
            <h2 class="section-title">All Properties</h2>
            <p class="section-sub" style="margin-left:auto;margin-right:auto;">Use the filters below to search by location, type, status or budget.</p>
        </div>

        <form class="search-card" style="margin-top:0;" method="get" id="searchForm">
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
</section>

<section class="section">
    <div class="container">
        <p style="color:var(--ink-soft); margin-bottom:24px;"><?php echo (int)$totalRows; ?> propert<?php echo $totalRows === 1 ? 'y' : 'ies'; ?> found</p>

        <div class="property-grid">
            <?php if ($result->num_rows > 0): while ($p = $result->fetch_assoc()):
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

<?php include 'includes/footer.php'; ?>
