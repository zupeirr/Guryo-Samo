<?php
/**
 * =========================================================================
 * admin/dashboard.php  —  Admin Dashboard (Overview Page)
 * =========================================================================
 *
 * PURPOSE:
 *   The first page an admin or staff member sees after logging in.
 *   Shows a quick summary of the site's key numbers (KPIs) and
 *   lists the most recently added properties and messages.
 *
 * ACCESS:
 *   Restricted — requireLogin() at the top immediately redirects
 *   anyone who is not an admin or staff member back to login.php.
 *
 * DATA LOADED:
 *   - Property statistics (total, for sale, for rent, this month)
 *   - Message statistics (total, unread, this week)
 *   - Last 5 properties added
 *   - Last 5 messages received
 * =========================================================================
 */

// Load database connection and shared functions
require_once '../config/db.php';
require_once '../includes/functions.php';

// SECURITY: Redirect to login page if visitor is not admin/staff
requireLogin();

$pageTitle      = 'Dashboard';   // Shown in the browser tab and the top bar
$activeAdminPage = 'dashboard';  // Tells the sidebar to highlight the Dashboard link

// ── Property Statistics ───────────────────────────────────────────────────
// We combine multiple counts into ONE query using conditional aggregation.
// SUM(condition) counts the rows where the condition is TRUE (MySQL treats TRUE as 1).
// This is more efficient than running 4 separate COUNT queries.
$propStats = $conn->query("
    SELECT
        COUNT(*) AS total,                         -- Total properties in the system
        SUM(status='For Sale') AS for_sale,        -- How many are listed for sale
        SUM(status='For Rent') AS for_rent,        -- How many are listed for rent
        SUM(MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())) AS this_month,      -- Added this calendar month
        SUM(MONTH(created_at)=MONTH(CURDATE()-INTERVAL 1 MONTH) AND YEAR(created_at)=YEAR(CURDATE()-INTERVAL 1 MONTH)) AS last_month  -- Added last month
    FROM properties
")->fetch_assoc();

// Extract individual values from the result row and cast them to integers
$totalProperties = (int)$propStats['total'];
$forSaleCount    = (int)$propStats['for_sale'];
$forRentCount    = (int)$propStats['for_rent'];
$propsThisMonth  = (int)$propStats['this_month'];
$propsLastMonth  = (int)$propStats['last_month'];

// Calculate what percentage of all properties are for sale / for rent
// The ternary ?: avoids division by zero when there are no properties yet
$forSalePct = $totalProperties > 0 ? round(($forSaleCount / $totalProperties) * 100) : 0;
$forRentPct = $totalProperties > 0 ? round(($forRentCount  / $totalProperties) * 100) : 0;

// ── Message Statistics ────────────────────────────────────────────────────
// Again using one query with conditional aggregation instead of three separate queries.
// SUM(is_read=0) counts messages where is_read is 0 (meaning not yet read).
$msgStats = $conn->query("
    SELECT
        COUNT(*) AS total,                                           -- All messages ever received
        SUM(is_read=0) AS unread,                                    -- Messages not yet read by admin
        SUM(created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) AS this_week -- Received in the last 7 days
    FROM messages
")->fetch_assoc();

$totalMessages  = (int)$msgStats['total'];
$unreadMessages = (int)$msgStats['unread'];
$msgsThisWeek   = (int)$msgStats['this_week'];

// ── Recent Data for Tables ────────────────────────────────────────────────
// IMPORTANT: We select only the columns we need.
// We do NOT select image_data or media_data because those are large BLOB columns
// that would waste memory and slow the page down.
$recentProperties = $conn->query(
    "SELECT id, title, type, price, location, status, image FROM properties ORDER BY created_at DESC LIMIT 5"
);
$recentMessages = $conn->query(
    "SELECT id, name, email, subject, is_read, created_at FROM messages ORDER BY created_at DESC LIMIT 5"
);

include 'includes/admin-header.php';
?>

<div class="dash-stats">
    <div class="dash-card">
        <div class="ic-wrap navy"><i data-lucide="building-2"></i></div>
        <div class="kpi-body">
            <h3><?php echo $totalProperties; ?></h3>
            <span class="kpi-label">Total Properties</span>
            <span class="kpi-sub"><?php
                if ($propsThisMonth > 0)      echo '<i data-lucide="trending-up" class="kpi-arrow up"></i> ' . $propsThisMonth . ' added this month';
                elseif ($propsLastMonth > 0)  echo '<i data-lucide="minus" class="kpi-arrow"></i> None added this month';
                else                          echo '<i data-lucide="building-2" class="kpi-arrow"></i> No activity yet';
            ?></span>
        </div>
    </div>
    <div class="dash-card">
        <div class="ic-wrap gold"><i data-lucide="tag"></i></div>
        <div class="kpi-body">
            <h3><?php echo $forSaleCount; ?></h3>
            <span class="kpi-label">For Sale</span>
            <span class="kpi-sub"><i data-lucide="trending-up" class="kpi-arrow"></i> <?php echo $forSalePct; ?>% of all properties</span>
        </div>
    </div>
    <div class="dash-card">
        <div class="ic-wrap green"><i data-lucide="key-round"></i></div>
        <div class="kpi-body">
            <h3><?php echo $forRentCount; ?></h3>
            <span class="kpi-label">For Rent</span>
            <span class="kpi-sub"><i data-lucide="trending-up" class="kpi-arrow"></i> <?php echo $forRentPct; ?>% of all properties</span>
        </div>
    </div>
    <div class="dash-card">
        <div class="ic-wrap red"><i data-lucide="message-square"></i></div>
        <div class="kpi-body">
            <h3><?php echo $totalMessages; ?></h3>
            <span class="kpi-label">Customer Messages</span>
            <span class="kpi-sub"><?php
                if ($unreadMessages > 0)    echo '<i data-lucide="message-square" class="kpi-arrow up"></i> ' . $unreadMessages . ' unread message' . ($unreadMessages > 1 ? 's' : '');
                elseif ($msgsThisWeek > 0) echo '<i data-lucide="message-square" class="kpi-arrow"></i> ' . $msgsThisWeek . ' received this week';
                else                       echo '<i data-lucide="message-square" class="kpi-arrow"></i> No new messages';
            ?></span>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>Recently Added Properties</h2>
        <a href="properties.php" class="btn btn-ghost btn-sm">View All</a>
    </div>
    <div class="panel-body table-wrap">
        <table class="data-table">
            <thead>
                <tr><th>Image</th><th>Title</th><th>Type</th><th>Price</th><th>Location</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php
                // Loop through recent properties — check if rows exist first
                if ($recentProperties->num_rows > 0): while ($p = $recentProperties->fetch_assoc()):
                    // Build the image URL: use image.php for DB-stored images, fallback for none
                    $imgSrc = ($p['image'] && $p['image'] !== 'no-image.jpg')
                        ? '../image.php?type=property&id=' . $p['id']
                        : '../assets/images/no-image.jpg';
                    // Pick a CSS class based on status for color-coded badges
                    $statusClass = $p['status'] === 'For Sale' ? 'sale' : ($p['status'] === 'For Rent' ? 'rent' : 'sold');
                ?>
                <tr onclick="window.location.href='edit-property.php?id=<?php echo $p['id']; ?>'">
                    <td><img src="<?php echo clean($imgSrc); ?>" class="thumb" alt=""></td>
                    <td><a href="edit-property.php?id=<?php echo $p['id']; ?>" class="property-title-link"><?php echo clean($p['title']); ?></a></td>
                    <td><?php echo clean($p['type']); ?></td>
                    <td><?php echo formatPrice($p['price']); ?></td>
                    <td><?php echo clean($p['location']); ?></td>
                    <td><span class="status-pill <?php echo $statusClass; ?>"><?php echo clean($p['status']); ?></span></td>
                </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="6" class="empty-state">No properties added yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>Recent Messages</h2>
        <a href="messages.php" class="btn btn-ghost btn-sm">View All</a>
    </div>
    <div class="panel-body table-wrap">
        <table class="data-table">
            <thead>
                <tr><th>From</th><th>Email</th><th>Subject</th><th>Received</th></tr>
            </thead>
            <tbody>
                <?php if ($recentMessages->num_rows > 0): while ($m = $recentMessages->fetch_assoc()): ?>
                <tr>
                    <td><?php if (!$m['is_read']): ?><span class="unread-dot"></span><?php endif; ?><?php echo clean($m['name']); ?></td>
                    <td><?php echo clean($m['email']); ?></td>
                    <td><?php echo clean($m['subject'] ?: '—'); ?></td>
                    <td><?php echo date('M j, Y g:i A', strtotime($m['created_at'])); ?></td>
                </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="4" class="empty-state">No messages received yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
