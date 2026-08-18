<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle = 'Properties';
$activeAdminPage = 'properties';

// ---- Filters from GET ----
$q      = trim($_GET['q']      ?? '');
$status = trim($_GET['status'] ?? '');
$type   = trim($_GET['type']   ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

// Allowed values (whitelist against SQL injection on ENUM columns)
$allowedStatuses = ['For Sale', 'For Rent', 'Sold'];
$allowedTypes    = ['House', 'Apartment', 'Land', 'Office', 'Commercial', 'Villa'];

if ($status && !in_array($status, $allowedStatuses, true)) { $status = ''; }
if ($type   && !in_array($type,   $allowedTypes,    true)) { $type = ''; }

// ---- Build WHERE clause ----
$where  = [];
$params = [];
$types  = '';

if ($q !== '') {
    $where[]  = '(title LIKE ? OR location LIKE ? OR property_ref LIKE ?)';
    $like     = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types   .= 'sss';
}
if ($status !== '') {
    $where[]  = 'status = ?';
    $params[] = $status;
    $types   .= 's';
}
if ($type !== '') {
    $where[]  = 'type = ?';
    $params[] = $type;
    $types   .= 's';
}

$whereSql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// ---- Count total matching rows ----
$countSql  = "SELECT COUNT(*) AS c FROM properties $whereSql";
$countStmt = $conn->prepare($countSql);
if ($params) { $countStmt->bind_param($types, ...$params); }
$countStmt->execute();
$totalRows = (int)$countStmt->get_result()->fetch_assoc()['c'];

// ---- Paginate ----
$pg     = paginate($totalRows, $perPage);
$offset = $pg['offset'];

// ---- Fetch paginated rows ----
$sql  = "SELECT * FROM properties $whereSql ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$fetchParams = $params;
$fetchTypes  = $types . 'ii';
$fetchParams[] = $pg['per_page'];
$fetchParams[] = $pg['offset'];
$stmt->bind_param($fetchTypes, ...$fetchParams);
$stmt->execute();
$result = $stmt->get_result();

// ---- Base URL for pagination (strip page param) ----
// $baseUrl not needed anymore as renderPagination handles it

include 'includes/admin-header.php';
?>

<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">Property deleted successfully.</div>
<?php endif; ?>
<?php if (isset($_GET['saved'])): ?>
    <div class="alert alert-success">Property saved successfully.</div>
<?php endif; ?>

<div class="panel">
    <div class="panel-head">
        <h2>All Properties (<?php echo number_format($totalRows); ?>)</h2>
        <a href="add-property.php" class="btn btn-primary btn-sm">+ Add Property</a>
    </div>

    <!-- Search & Filter Bar -->
    <div class="search-filter-bar">
        <div class="search-wrap">
            <span class="search-ic">🔍</span>
            <form method="GET" id="searchForm" style="display:contents;">
                <?php if ($status): ?><input type="hidden" name="status" value="<?php echo clean($status); ?>"><?php endif; ?>
                <?php if ($type):   ?><input type="hidden" name="type"   value="<?php echo clean($type); ?>"><?php endif; ?>
                <input type="text" name="q" value="<?php echo clean($q); ?>"
                       placeholder="Search by title, location, or ref…"
                       oninput="this.form.submit()"
                       autocomplete="off" id="propSearch">
            </form>
        </div>

        <div class="filter-group">
            <span>Status:</span>
            <?php
            $statusFilters = ['' => 'All', 'For Sale' => 'For Sale', 'For Rent' => 'For Rent', 'Sold' => 'Sold'];
            foreach ($statusFilters as $val => $label):
                $qp = array_filter(['q' => $q, 'status' => $val, 'type' => $type], fn($v) => $v !== '');
                $href = 'properties.php' . ($qp ? '?' . http_build_query($qp) : '');
            ?>
            <a href="<?php echo htmlspecialchars($href); ?>"
               class="filter-pill <?php echo $status === $val ? 'active' : ''; ?>">
                <?php echo $label; ?>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="filter-group">
            <span>Type:</span>
            <select onchange="location.href=this.value" id="typeFilter">
                <?php
                $typeOptions = ['' => 'All Types', 'House' => 'House', 'Apartment' => 'Apartment',
                                'Land' => 'Land', 'Office' => 'Office', 'Commercial' => 'Commercial', 'Villa' => 'Villa'];
                foreach ($typeOptions as $val => $label):
                    $qp = array_filter(['q' => $q, 'status' => $status, 'type' => $val], fn($v) => $v !== '');
                    $href = 'properties.php' . ($qp ? '?' . http_build_query($qp) : '');
                ?>
                <option value="<?php echo htmlspecialchars($href); ?>" <?php echo $type === $val ? 'selected' : ''; ?>>
                    <?php echo $label; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <span class="results-count">
            Showing <?php echo number_format(min($pg['offset'] + 1, $totalRows)); ?>–<?php echo number_format(min($pg['offset'] + $pg['per_page'], $totalRows)); ?>
            of <?php echo number_format($totalRows); ?>
        </span>

        <?php if ($q || $status || $type): ?>
            <a href="properties.php" class="btn btn-ghost btn-sm">✕ Clear</a>
        <?php endif; ?>
    </div>

    <div class="panel-body table-wrap" style="padding:0;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Image</th><th>Ref #</th><th>Title</th><th>Type</th><th>Price</th>
                    <th>Location</th><th>Beds/Baths</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): while ($p = $result->fetch_assoc()):
                    $imgSrc = ($p['image'] && $p['image'] !== 'no-image.jpg')
                        ? '../uploads/' . $p['image']
                        : '../assets/images/no-image.jpg';
                    $statusClass = $p['status'] === 'For Sale' ? 'sale' : ($p['status'] === 'For Rent' ? 'rent' : 'sold');
                ?>
                <tr>
                    <td><img src="<?php echo clean($imgSrc); ?>" class="thumb" alt=""></td>
                    <td><span style="font-family:monospace; font-size:0.78rem; color:var(--ink-soft); white-space:nowrap;"><?php echo clean($p['property_ref']); ?></span></td>
                    <td><?php echo clean($p['title']); ?></td>
                    <td><?php echo clean($p['type']); ?></td>
                    <td><?php echo formatPrice($p['price']); ?></td>
                    <td><?php echo clean($p['location']); ?></td>
                    <td><?php echo (int)$p['bedrooms']; ?> / <?php echo (int)$p['bathrooms']; ?></td>
                    <td><span class="status-pill <?php echo $statusClass; ?>"><?php echo clean($p['status']); ?></span></td>
                    <td>
                        <div class="row-actions">
                            <a href="edit-property.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-ghost btn-sm">Edit</a>
                            <a href="delete-property.php?id=<?php echo (int)$p['id']; ?>"
                               class="btn btn-danger btn-sm js-confirm-delete"
                               data-label="<?php echo clean($p['title']); ?>">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="9" class="empty-state">
                        <?php echo ($q || $status || $type)
                            ? 'No properties match your search. <a href="properties.php">Clear filters</a>'
                            : 'No properties added yet. <a href="add-property.php">Add your first property</a>.'; ?>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php echo renderPagination($pg, 'properties.php', ['q' => $q, 'status' => $status, 'type' => $type]); ?>
    <?php if ($pg['total_pages'] > 1): ?>
    <p class="pagination-info">
        Page <?php echo $pg['current']; ?> of <?php echo $pg['total_pages']; ?>
        &nbsp;·&nbsp; <?php echo number_format($totalRows); ?> total properties
    </p>
    <?php endif; ?>
</div>

<?php include 'includes/admin-footer.php'; ?>
