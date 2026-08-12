<?php
/**
 * admin/reports.php
 * ---------------------------------------------------------------
 * Tabbed analytics/reports page for the admin.
 */

require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle = 'Reports & Analytics';
$activeAdminPage = 'reports';

// --- Properties Data ---
$byType = [];
$typeResult = $conn->query("SELECT type, COUNT(*) AS total FROM properties GROUP BY type ORDER BY total DESC");
while ($row = $typeResult->fetch_assoc()) { $byType[] = $row; }
$maxTypeCount = count($byType) > 0 ? max(array_column($byType, 'total')) : 0;

$byStatus = [];
$statusResult = $conn->query("SELECT status, COUNT(*) AS total FROM properties GROUP BY status");
while ($row = $statusResult->fetch_assoc()) { $byStatus[$row['status']] = (int)$row['total']; }
$totalProperties = array_sum($byStatus);

$priceStats = $conn->query("SELECT MIN(price) AS min_price, MAX(price) AS max_price, AVG(price) AS avg_price FROM properties")->fetch_assoc();
$topLocations = $conn->query("SELECT location, COUNT(*) AS total FROM properties GROUP BY location ORDER BY total DESC LIMIT 5");

// --- Sales/Transactions Data ---
$totalSalesValue = $conn->query("SELECT SUM(amount) AS total FROM transactions WHERE type = 'Sale' AND status = 'Completed'")->fetch_assoc()['total'] ?? 0;
$totalRentalsValue = $conn->query("SELECT SUM(amount) AS total FROM transactions WHERE type = 'Rental' AND status = 'Completed'")->fetch_assoc()['total'] ?? 0;

// Monthly sales for the current year (simple SQL group by month)
$currentYear = date('Y');
$monthlySales = array_fill(1, 12, 0);
$salesRes = $conn->query("SELECT MONTH(transaction_date) as m, SUM(amount) as total FROM transactions WHERE YEAR(transaction_date) = $currentYear AND type = 'Sale' AND status = 'Completed' GROUP BY MONTH(transaction_date)");
while ($row = $salesRes->fetch_assoc()) { $monthlySales[(int)$row['m']] = (float)$row['total']; }
$maxMonthlySale = max($monthlySales) ?: 1; // avoid div by 0

// --- Revenue/Commissions Data ---
$totalCommissions = $conn->query("SELECT SUM(commission) AS total FROM transactions WHERE status = 'Completed'")->fetch_assoc()['total'] ?? 0;

// Top earning agents
$agentEarnings = $conn->query("
    SELECT u.full_name, SUM(t.commission) as earned
    FROM transactions t
    JOIN agents a ON a.id = t.agent_id
    JOIN users u ON u.id = a.user_id
    WHERE t.status = 'Completed'
    GROUP BY a.id
    ORDER BY earned DESC LIMIT 5
");

// --- User Activity Data ---
$totalUsers     = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$totalCustomers = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role = 'customer'")->fetch_assoc()['c'];

include 'includes/admin-header.php';
?>

<div class="panel-head" style="padding:0; border:none; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center;">
    <div class="tab-nav">
        <a href="#properties" data-tab="properties" class="active">Property Reports</a>
        <a href="#sales" data-tab="sales">Sales Reports</a>
        <a href="#revenue" data-tab="revenue">Revenue Reports</a>
        <a href="#activity" data-tab="activity">User Activity</a>
    </div>
    <button onclick="window.print()" class="btn btn-ghost btn-sm no-print">🖨️ Print Report</button>
</div>

<!-- ================= PROPERTY REPORTS ================= -->
<div id="properties" class="settings-section active">
    <div class="dash-stats">
        <div class="dash-card">
            <div class="ic-wrap navy">🏠</div>
            <div><h3><?php echo (int)$totalProperties; ?></h3><span>Total Properties</span></div>
        </div>
        <div class="dash-card">
            <div class="ic-wrap gold">🔑</div>
            <div><h3><?php echo (int)($byStatus['For Rent'] ?? 0); ?></h3><span>For Rent</span></div>
        </div>
        <div class="dash-card">
            <div class="ic-wrap green">💰</div>
            <div><h3><?php echo (int)($byStatus['For Sale'] ?? 0); ?></h3><span>For Sale</span></div>
        </div>
        <div class="dash-card">
            <div class="ic-wrap purple">📍</div>
            <div><h3><?php echo $topLocations->num_rows; ?></h3><span>Top Locations</span></div>
        </div>
    </div>

    <div class="details-grid" style="grid-template-columns: 1.3fr 1fr; gap:24px;">
        <div class="panel">
            <div class="panel-head"><h2>Properties by Type</h2></div>
            <div class="panel-body">
                <?php if (count($byType) > 0): foreach ($byType as $row):
                    $pct = $maxTypeCount > 0 ? round(((int)$row['total'] / $maxTypeCount) * 100) : 0;
                ?>
                <div class="chart-bar-row">
                    <div class="chart-bar-label">
                        <span><?php echo clean($row['type']); ?></span>
                        <strong><?php echo (int)$row['total']; ?></strong>
                    </div>
                    <div class="chart-bar-track">
                        <div class="chart-bar-fill" style="background:var(--gold-500); width:<?php echo $pct; ?>%;"></div>
                    </div>
                </div>
                <?php endforeach; else: ?>
                    <p class="empty-state">No property data yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>Top Locations</h2></div>
            <div class="panel-body">
                <table class="data-table">
                    <thead><tr><th>Location</th><th>Listings</th></tr></thead>
                    <tbody>
                        <?php if ($topLocations->num_rows > 0): while ($loc = $topLocations->fetch_assoc()): ?>
                            <tr><td><?php echo clean($loc['location']); ?></td><td><?php echo (int)$loc['total']; ?></td></tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="2" class="empty-state">No data yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ================= SALES REPORTS ================= -->
<div id="sales" class="settings-section">
    <div class="dash-stats">
        <div class="dash-card">
            <div class="ic-wrap navy">💼</div>
            <div><h3><?php echo formatPrice($totalSalesValue); ?></h3><span>Total Sales Volume</span></div>
        </div>
        <div class="dash-card">
            <div class="ic-wrap green">🔑</div>
            <div><h3><?php echo formatPrice($totalRentalsValue); ?></h3><span>Total Rentals Volume</span></div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>Sales Volume (<?php echo $currentYear; ?>)</h2></div>
        <div class="panel-body">
            <div style="display:flex; align-items:flex-end; gap:10px; height:200px; padding-top:20px; border-bottom:1px solid var(--border);">
                <?php 
                $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                foreach ($months as $i => $m): 
                    $val = $monthlySales[$i+1];
                    $hPct = ($val / $maxMonthlySale) * 100;
                    if ($hPct < 1 && $val > 0) $hPct = 1; 
                ?>
                <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:flex-end; height:100%;">
                    <div style="font-size:0.7rem; color:var(--ink-soft); margin-bottom:4px;" title="<?php echo formatPrice($val); ?>">
                        <?php echo $val > 0 ? '$' . number_format($val/1000, 0) . 'k' : ''; ?>
                    </div>
                    <div style="width:100%; max-width:40px; background:var(--navy-800); height:<?php echo $hPct; ?>%; border-radius:4px 4px 0 0; transition:height 0.5s ease;"></div>
                    <div style="margin-top:8px; font-size:0.75rem; color:var(--ink-soft);"><?php echo $m; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- ================= REVENUE REPORTS ================= -->
<div id="revenue" class="settings-section">
    <div class="dash-stats">
        <div class="dash-card">
            <div class="ic-wrap gold">💵</div>
            <div><h3><?php echo formatPrice($totalCommissions); ?></h3><span>Total Commissions Paid</span></div>
        </div>
    </div>
    
    <div class="details-grid" style="grid-template-columns: 1fr 1fr; gap:24px;">
        <div class="panel">
            <div class="panel-head">
                <h2>Top Agents by Commission</h2>
                <a href="agent-performance.php" class="btn btn-ghost btn-sm">Full Performance →</a>
            </div>
            <div class="panel-body">
                <table class="data-table">
                    <thead><tr><th>Agent</th><th>Commission Earned</th></tr></thead>
                    <tbody>
                        <?php if ($agentEarnings->num_rows > 0): while ($a = $agentEarnings->fetch_assoc()): ?>
                            <tr>
                                <td style="font-weight:600;"><?php echo clean($a['full_name']); ?></td>
                                <td style="color:var(--success); font-weight:700;"><?php echo formatPrice($a['earned']); ?></td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="2" class="empty-state">No commissions yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ================= USER ACTIVITY ================= -->
<div id="activity" class="settings-section">
    <div class="dash-stats">
        <div class="dash-card">
            <div class="ic-wrap teal">👥</div>
            <div><h3><?php echo (int)$totalUsers; ?></h3><span>Total Users</span></div>
        </div>
        <div class="dash-card">
            <div class="ic-wrap green">🙋</div>
            <div><h3><?php echo (int)$totalCustomers; ?></h3><span>Customers</span></div>
        </div>
    </div>
    
    <div class="panel">
        <div class="panel-head">
            <h2>Recent Activity Logs</h2>
            <a href="activity-logs.php" class="btn btn-ghost btn-sm">View All Logs</a>
        </div>
        <div class="panel-body table-wrap" style="padding:0;">
            <?php
            $recentLogs = $conn->query("SELECT l.*, u.username FROM activity_logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT 10");
            ?>
            <table class="data-table">
                <thead><tr><th>Time</th><th>User</th><th>Action</th></tr></thead>
                <tbody>
                    <?php if ($recentLogs->num_rows > 0): while ($l = $recentLogs->fetch_assoc()): ?>
                    <tr>
                        <td style="font-size:0.8rem; color:var(--ink-soft);"><?php echo date('M j, g:i A', strtotime($l['created_at'])); ?></td>
                        <td><?php echo clean($l['username'] ?: 'System'); ?></td>
                        <td><?php echo clean($l['action']); ?></td>
                    </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="3" class="empty-state">No recent activity.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    @media print {
        .admin-sidebar, .admin-topbar, .no-print, .tab-nav { display: none !important; }
        .admin-content { padding: 0 !important; }
        .settings-section { display: block !important; margin-bottom: 40px; page-break-inside: avoid; }
    }
</style>

<?php include 'includes/admin-footer.php'; ?>

