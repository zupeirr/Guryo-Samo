<?php
/**
 * admin/reports.php
 * ---------------------------------------------------------------
 * Master Analytics and Reporting Dashboard.
 * Tabs: Property Reports | Sales Reports | Revenue Reports |
 *       Project Profitability | Property Profitability |
 *       Agent Performance | Financial Reports | Operational Reports | Exports
 */

require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'Reports & Analytics';
$activeAdminPage = 'reports';

// ================================================================
// PROPERTY DATA
// ================================================================
$byType = [];
$typeResult = $conn->query("SELECT type, COUNT(*) AS total FROM properties GROUP BY type ORDER BY total DESC");
while ($row = $typeResult->fetch_assoc()) { $byType[] = $row; }
$maxTypeCount = count($byType) > 0 ? max(array_column($byType, 'total')) : 0;

$byStatus = [];
$statusResult = $conn->query("SELECT status, COUNT(*) AS total FROM properties GROUP BY status");
while ($row = $statusResult->fetch_assoc()) { $byStatus[$row['status']] = (int)$row['total']; }
$totalProperties = array_sum($byStatus);

$priceStats    = $conn->query("SELECT MIN(price) AS min_price, MAX(price) AS max_price, AVG(price) AS avg_price FROM properties")->fetch_assoc();
$topLocations  = $conn->query("SELECT location, COUNT(*) AS total FROM properties GROUP BY location ORDER BY total DESC LIMIT 5");
$recentProps   = $conn->query("SELECT id, title, type, status, price, created_at FROM properties ORDER BY created_at DESC LIMIT 5");
$propByLocation = $conn->query("SELECT location, COUNT(*) AS total FROM properties GROUP BY location ORDER BY total DESC LIMIT 8");

// ================================================================
// SALES REPORTS
// ================================================================
$salesCompletion = $conn->query("SELECT status, COUNT(*) as cnt, SUM(amount) as vol FROM transactions WHERE type='Sale' GROUP BY status");

$salesByMonth = [];
$monthLabels  = ['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
$salesByMonthRes = $conn->query("
    SELECT MONTH(transaction_date) as m, COUNT(*) as cnt, SUM(amount) as vol 
    FROM transactions 
    WHERE type='Sale' AND status='Completed' AND YEAR(transaction_date)=YEAR(CURDATE()) 
    GROUP BY m ORDER BY m
");
while ($r = $salesByMonthRes->fetch_assoc()) { $salesByMonth[(int)$r['m']] = $r; }
$maxMonthVol = count($salesByMonth) > 0 ? max(array_column($salesByMonth, 'vol')) : 1;

$salesByLocation = $conn->query("
    SELECT p.location, COUNT(t.id) as cnt, SUM(t.amount) as vol 
    FROM transactions t JOIN properties p ON p.id = t.property_id 
    WHERE t.type='Sale' AND t.status='Completed' 
    GROUP BY p.location ORDER BY vol DESC LIMIT 8
");

$salesByAgent = $conn->query("
    SELECT u.full_name, COUNT(t.id) as cnt, SUM(t.amount) as vol
    FROM transactions t
    JOIN agents a ON a.id = t.agent_id
    JOIN users u ON u.id = a.user_id
    WHERE t.type='Sale' AND t.status='Completed'
    GROUP BY a.id ORDER BY vol DESC LIMIT 8
");

$avgSalePrice = $conn->query("SELECT AVG(amount) AS avg FROM transactions WHERE type='Sale' AND status='Completed'")->fetch_assoc()['avg'] ?? 0;
$totalSalesCount = $conn->query("SELECT COUNT(*) AS c FROM transactions WHERE type='Sale' AND status='Completed'")->fetch_assoc()['c'] ?? 0;
$totalRentalCount = $conn->query("SELECT COUNT(*) AS c FROM transactions WHERE type='Rental' AND status='Completed'")->fetch_assoc()['c'] ?? 0;

// ================================================================
// REVENUE REPORTS
// ================================================================
$revSales       = $conn->query("SELECT SUM(amount) AS t FROM transactions WHERE type='Sale' AND status='Completed'")->fetch_assoc()['t'] ?? 0;
$revRental      = $conn->query("SELECT SUM(amount) AS t FROM transactions WHERE type='Rental' AND status='Completed'")->fetch_assoc()['t'] ?? 0;
$revConstruction= $conn->query("SELECT SUM(revenue_generated) AS t FROM projects WHERE status='Completed'")->fetch_assoc()['t'] ?? 0;

$revByMonth = $conn->query("
    SELECT MONTH(transaction_date) as m, SUM(amount) as vol 
    FROM transactions WHERE status='Completed' AND YEAR(transaction_date)=YEAR(CURDATE())
    GROUP BY m ORDER BY m
");

$revByProject = $conn->query("SELECT name, revenue_generated, total_investment FROM projects ORDER BY revenue_generated DESC LIMIT 8");

$revByProperty = $conn->query("
    SELECT p.title, SUM(t.amount) as revenue 
    FROM transactions t JOIN properties p ON p.id=t.property_id 
    WHERE t.status='Completed' GROUP BY p.id ORDER BY revenue DESC LIMIT 8
");

// ================================================================
// PROJECT PROFITABILITY
// ================================================================
$avgProjectROI = $conn->query("SELECT AVG((revenue_generated - total_investment) / NULLIF(total_investment,0) * 100) AS avg_roi FROM projects WHERE status='Completed'")->fetch_assoc()['avg_roi'] ?? 0;
$netProjProfit = $conn->query("SELECT SUM(revenue_generated - total_investment) AS np FROM projects WHERE status='Completed'")->fetch_assoc()['np'] ?? 0;
$projectsList  = $conn->query("
    SELECT *, (revenue_generated - total_investment) AS net_profit,
    ROUND((revenue_generated - total_investment) / NULLIF(total_investment,0) * 100, 1) AS roi 
    FROM projects ORDER BY created_at DESC
");

// ================================================================
// PROPERTY PROFITABILITY
// ================================================================
$propertyProfit = $conn->query("
    SELECT p.id, p.title, p.price AS cost,
           COALESCE(SUM(CASE WHEN ex.scope='Property' THEN ex.amount ELSE 0 END), 0) AS total_expenses,
           COALESCE(SUM(CASE WHEN t.status='Completed' THEN t.amount ELSE 0 END), 0) AS revenue
    FROM properties p
    LEFT JOIN expenses ex ON ex.property_id = p.id AND ex.status != 'Cancelled'
    LEFT JOIN transactions t ON t.property_id = p.id
    GROUP BY p.id ORDER BY revenue DESC LIMIT 10
");

// ================================================================
// AGENT PERFORMANCE
// ================================================================
$agentPerformance = $conn->query("
    SELECT u.full_name AS Agent, u.id AS user_id, a.id AS agent_id,
           (SELECT COUNT(*) FROM appointments ap WHERE ap.agent_id = a.id) AS total_appointments,
           (SELECT COUNT(*) FROM appointments ap WHERE ap.agent_id = a.id AND ap.type='Viewing') AS viewings,
           (SELECT COUNT(*) FROM appointments ap WHERE ap.agent_id = a.id AND ap.status='Completed') AS completed_viewings,
           (SELECT COUNT(*) FROM inquiries i WHERE i.property_id IN (SELECT id FROM properties WHERE id IS NOT NULL)) AS leads_in_system,
           COUNT(t.id) AS deals_closed,
           COALESCE(SUM(t.amount), 0) AS revenue,
           COALESCE(SUM(t.commission), 0) AS commissions
    FROM agents a
    JOIN users u ON u.id = a.user_id
    LEFT JOIN transactions t ON t.agent_id = a.id AND t.status = 'Completed'
    GROUP BY a.id
    ORDER BY revenue DESC
");

// ================================================================
// FINANCIAL REPORTS (consolidated from Finance module)
// ================================================================
$totalInvoiced    = $conn->query("SELECT SUM(amount) AS t FROM invoices")->fetch_assoc()['t'] ?? 0;
$totalCollected   = $conn->query("SELECT SUM(paid_amount) AS t FROM invoices")->fetch_assoc()['t'] ?? 0;
$totalOutstanding = $totalInvoiced - $totalCollected;
$totalExpensesAll = $conn->query("SELECT SUM(amount) AS t FROM expenses WHERE status != 'Cancelled'")->fetch_assoc()['t'] ?? 0;
$totalExpensesPaid= $conn->query("SELECT SUM(amount) AS t FROM expenses WHERE status='Paid'")->fetch_assoc()['t'] ?? 0;
$netCashFlow      = $totalCollected - $totalExpensesPaid;

// Outstanding invoices breakdown
$outstandingInvs = $conn->query("SELECT status, COUNT(*) as cnt, SUM(amount-paid_amount) as outstanding FROM invoices WHERE status NOT IN ('Paid','Cancelled') GROUP BY status");

// Budget vs Actual
$budgetComparison = $conn->query("SELECT b.department, b.allocated_amount, b.spent_amount, b.period_type, b.period_label, (b.allocated_amount - b.spent_amount) AS remaining FROM budgets b ORDER BY b.period_type, b.period_label");

// Expense breakdown by category
$expenseByCategory = $conn->query("
    SELECT c.name, SUM(e.amount) as total 
    FROM expenses e JOIN expense_categories c ON c.id = e.category_id 
    WHERE e.status != 'Cancelled' GROUP BY c.id ORDER BY total DESC LIMIT 8
");

// Recent payments
$recentPaymentsF = $conn->query("
    SELECT p.amount, p.payment_method, p.payment_date, i.invoice_ref, i.customer_name 
    FROM payments p LEFT JOIN invoices i ON i.id = p.invoice_id 
    ORDER BY p.payment_date DESC LIMIT 10
");

// ================================================================
// OPERATIONAL REPORTS
// ================================================================
// Property Operations
$totalAvailable = ($byStatus['For Sale'] ?? 0) + ($byStatus['For Rent'] ?? 0);
$totalSold      = $byStatus['Sold'] ?? 0;
$totalRented    = $byStatus['Rented'] ?? 0;
$occupancyRate  = $totalProperties > 0 ? round((($totalSold + $totalRented) / $totalProperties) * 100, 1) : 0;

// CRM / Lead Operations (using inquiries as leads)
$totalInquiries  = $conn->query("SELECT COUNT(*) AS c FROM inquiries")->fetch_assoc()['c'] ?? 0;
$unreadInquiries = $conn->query("SELECT COUNT(*) AS c FROM inquiries WHERE is_read=0")->fetch_assoc()['c'] ?? 0;
$totalViewings   = $conn->query("SELECT COUNT(*) AS c FROM appointments WHERE type='Viewing'")->fetch_assoc()['c'] ?? 0;
$completedViewings = $conn->query("SELECT COUNT(*) AS c FROM appointments WHERE type='Viewing' AND status='Completed'")->fetch_assoc()['c'] ?? 0;
$pendingAppts    = $conn->query("SELECT COUNT(*) AS c FROM appointments WHERE status='Pending'")->fetch_assoc()['c'] ?? 0;
$upcomingAppts   = $conn->query("SELECT a.*, p.title as property_title, u.full_name as agent_name FROM appointments a LEFT JOIN properties p ON p.id=a.property_id LEFT JOIN agents ag ON ag.id=a.agent_id LEFT JOIN users u ON u.id=ag.user_id WHERE a.status='Confirmed' AND a.scheduled_at >= NOW() ORDER BY a.scheduled_at LIMIT 5");

// Construction Operations
$activeProjects    = $conn->query("SELECT COUNT(*) AS c FROM projects WHERE status='In Progress'")->fetch_assoc()['c'] ?? 0;
$completedProjects = $conn->query("SELECT COUNT(*) AS c FROM projects WHERE status='Completed'")->fetch_assoc()['c'] ?? 0;
$plannedProjects   = $conn->query("SELECT COUNT(*) AS c FROM projects WHERE status='Planned'")->fetch_assoc()['c'] ?? 0;

// ================================================================
// NEW CONSTRUCTION & OPERATIONS
// ================================================================
$constBudgetTotal = $conn->query("SELECT SUM(budget) AS b, SUM(spent) AS s FROM construction_projects")->fetch_assoc();
$constTotalBudget = $constBudgetTotal['b'] ?? 0;
$constTotalSpent  = $constBudgetTotal['s'] ?? 0;
$constProjects    = $conn->query("SELECT status, COUNT(*) AS c FROM construction_projects GROUP BY status");
$constProjData = []; while($r = $constProjects->fetch_assoc()) $constProjData[$r['status']] = $r['c'];
$constTasks       = $conn->query("SELECT status, COUNT(*) AS c FROM construction_tasks GROUP BY status");
$constTaskData = []; while($r = $constTasks->fetch_assoc()) $constTaskData[$r['status']] = $r['c'];
$constMatVal      = $conn->query("SELECT COALESCE(SUM(quantity * unit_cost),0) AS ordered, COALESCE(SUM(stock_level * unit_cost),0) AS instock FROM construction_materials")->fetch_assoc();

// User Roles Summary
$userRoleSummary = $conn->query("SELECT role, COUNT(*) as cnt FROM users GROUP BY role ORDER BY cnt DESC");

include 'includes/admin-header.php';
?>

<div class="panel-head" style="padding:0; border:none; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; gap:10px;">
    <div class="tab-nav" style="overflow-x:auto; white-space:nowrap; flex:1;">
        <a href="#properties" data-tab="properties" class="active">Property Reports</a>
        <a href="#sales" data-tab="sales">Sales Reports</a>
        <a href="#revenue" data-tab="revenue">Revenue Reports</a>
        <a href="#project-profitability" data-tab="project-profitability">Project Profit</a>
        <a href="#property-profitability" data-tab="property-profitability">Property Profit</a>
        <a href="#agent-performance" data-tab="agent-performance">Agent Performance</a>
        <a href="#financial" data-tab="financial">Financial Reports</a>
        <a href="#operational" data-tab="operational">Operational Reports</a>
        <a href="#construction-operations" data-tab="construction-operations">Construction Ops</a>
        <a href="#exports" data-tab="exports">Exports</a>
    </div>
    <button onclick="window.print()" class="btn btn-ghost btn-sm no-print" style="flex-shrink:0; display:inline-flex; align-items:center; gap:5px;"><i data-lucide="printer" style="width:14px;height:14px;"></i> Print</button>
</div>

<!-- ===================== PROPERTY REPORTS ===================== -->
<div id="properties" class="settings-section active">
    <div class="dash-stats">
        <div class="dash-card"><div class="ic-wrap navy"><i data-lucide="building-2" style="width:20px;height:20px;"></i></div><div><h3><?php echo (int)$totalProperties; ?></h3><span>Total Properties</span></div></div>
        <div class="dash-card"><div class="ic-wrap gold"><i data-lucide="key-round" style="width:20px;height:20px;"></i></div><div><h3><?php echo (int)($byStatus['For Rent'] ?? 0); ?></h3><span>For Rent</span></div></div>
        <div class="dash-card"><div class="ic-wrap green"><i data-lucide="tag" style="width:20px;height:20px;"></i></div><div><h3><?php echo (int)($byStatus['For Sale'] ?? 0); ?></h3><span>For Sale</span></div></div>
        <div class="dash-card"><div class="ic-wrap blue"><i data-lucide="badge-check" style="width:20px;height:20px;"></i></div><div><h3><?php echo (int)($byStatus['Sold'] ?? 0); ?></h3><span>Sold</span></div></div>
        <div class="dash-card"><div class="ic-wrap purple"><i data-lucide="circle-dollar-sign" style="width:20px;height:20px;"></i></div><div><h3><?php echo formatPrice($priceStats['avg_price'] ?? 0); ?></h3><span>Avg Price</span></div></div>
    </div>
    <div class="details-grid" style="grid-template-columns: 1.3fr 1fr; gap:24px; margin-top:20px;">
        <div class="panel">
            <div class="panel-head"><h2>Properties by Type</h2></div>
            <div class="panel-body">
                <?php if ($byType): foreach ($byType as $row):
                    $pct = $maxTypeCount > 0 ? round(($row['total'] / $maxTypeCount) * 100) : 0; ?>
                <div style="margin-bottom:12px;">
                    <div style="display:flex;justify-content:space-between;font-size:.9rem;margin-bottom:4px;">
                        <strong><?php echo clean($row['type']); ?></strong><span><?php echo $row['total']; ?></span>
                    </div>
                    <div class="chart-bar-track"><div class="chart-bar-fill" style="width:<?php echo $pct; ?>%;background:var(--primary);"></div></div>
                </div>
                <?php endforeach; else: echo '<p class="empty-state">No data.</p>'; endif; ?>
            </div>
        </div>
        <div class="panel">
            <div class="panel-head"><h2>Top Locations</h2></div>
            <div class="panel-body table-wrap">
                <table class="data-table">
                    <thead><tr><th>Location</th><th>Count</th></tr></thead>
                    <tbody>
                        <?php while ($loc = $propByLocation->fetch_assoc()): ?>
                        <tr><td><?php echo clean($loc['location']); ?></td><td><?php echo $loc['total']; ?></td></tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="panel" style="margin-top:20px;">
        <div class="panel-head"><h2>Recently Added Properties</h2></div>
        <div class="panel-body table-wrap">
            <table class="data-table">
                <thead><tr><th>Title</th><th>Type</th><th>Status</th><th>Price</th><th>Added</th></tr></thead>
                <tbody>
                    <?php while ($rp = $recentProps->fetch_assoc()): ?>
                    <tr onclick="window.location.href='edit-property.php?id=<?php echo $rp['id']; ?>'">
                        <td><a href="edit-property.php?id=<?php echo $rp['id']; ?>" class="property-title-link"><?php echo clean($rp['title']); ?></a></td>
                        <td><?php echo clean($rp['type']); ?></td>
                        <td><span class="status-badge status-pending"><?php echo clean($rp['status']); ?></span></td>
                        <td><?php echo formatPrice($rp['price']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($rp['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===================== SALES REPORTS ===================== -->
<div id="sales" class="settings-section" style="display:none;">
    <div class="dash-stats">
        <div class="dash-card"><div class="ic-wrap green"><i data-lucide="check-circle-2" style="width:20px;height:20px;"></i></div><div><h3><?php echo (int)$totalSalesCount; ?></h3><span>Completed Sales</span></div></div>
        <div class="dash-card"><div class="ic-wrap purple"><i data-lucide="key-round" style="width:20px;height:20px;"></i></div><div><h3><?php echo (int)$totalRentalCount; ?></h3><span>Completed Rentals</span></div></div>
        <div class="dash-card"><div class="ic-wrap gold"><i data-lucide="bar-chart-2" style="width:20px;height:20px;"></i></div><div><h3><?php echo formatPrice($avgSalePrice); ?></h3><span>Avg Sale Price</span></div></div>
    </div>

    <div class="details-grid" style="grid-template-columns: 1fr 1fr; gap:24px; margin-top:20px;">
        <!-- Sales by Month Chart -->
        <div class="panel">
            <div class="panel-head"><h2>Sales by Month (<?php echo date('Y'); ?>)</h2></div>
            <div class="panel-body">
                <div style="display:flex; align-items:flex-end; gap:6px; height:140px; padding-bottom:10px; border-bottom:1px solid var(--border);">
                    <?php for ($m = 1; $m <= 12; $m++):
                        $vol = $salesByMonth[$m]['vol'] ?? 0;
                        $cnt = $salesByMonth[$m]['cnt'] ?? 0;
                        $h   = $maxMonthVol > 0 ? round(($vol / $maxMonthVol) * 120) : 0;
                    ?>
                    <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:flex-end;" title="<?php echo $monthLabels[$m].': '.$cnt.' deals, '.formatPrice($vol); ?>">
                        <div style="width:100%; background:var(--primary); border-radius:3px 3px 0 0; height:<?php echo $h; ?>px; opacity:0.85; transition:opacity .2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.85"></div>
                        <small style="font-size:.65rem; margin-top:4px; color:var(--text-light);"><?php echo $monthLabels[$m]; ?></small>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <!-- Completed vs Pending -->
        <div class="panel">
            <div class="panel-head"><h2>Completed vs Pending Sales</h2></div>
            <div class="panel-body table-wrap">
                <table class="data-table">
                    <thead><tr><th>Status</th><th>Count</th><th>Total Volume</th></tr></thead>
                    <tbody>
                        <?php $salesCompletion->data_seek(0); while ($s = $salesCompletion->fetch_assoc()): ?>
                        <tr>
                            <td><span class="status-badge <?php echo $s['status']==='Completed'?'status-sold':'status-pending'; ?>"><?php echo clean($s['status']); ?></span></td>
                            <td><?php echo $s['cnt']; ?></td>
                            <td><?php echo formatPrice($s['vol']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="details-grid" style="grid-template-columns: 1fr 1fr; gap:24px; margin-top:20px;">
        <!-- Sales by Agent -->
        <div class="panel">
            <div class="panel-head"><h2>Sales by Agent</h2></div>
            <div class="panel-body table-wrap">
                <table class="data-table">
                    <thead><tr><th>Agent</th><th>Deals</th><th>Revenue</th></tr></thead>
                    <tbody>
                        <?php while ($sa = $salesByAgent->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo clean($sa['full_name']); ?></strong></td>
                            <td><?php echo $sa['cnt']; ?></td>
                            <td style="color:var(--success);font-weight:bold;"><?php echo formatPrice($sa['vol']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sales by Location -->
        <div class="panel">
            <div class="panel-head"><h2>Sales by Location</h2></div>
            <div class="panel-body table-wrap">
                <table class="data-table">
                    <thead><tr><th>Location</th><th>Sales</th><th>Volume</th></tr></thead>
                    <tbody>
                        <?php while ($sl = $salesByLocation->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo clean($sl['location']); ?></strong></td>
                            <td><?php echo $sl['cnt']; ?></td>
                            <td><?php echo formatPrice($sl['vol']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ===================== REVENUE REPORTS ===================== -->
<div id="revenue" class="settings-section" style="display:none;">
    <div class="dash-stats">
        <div class="dash-card"><div class="ic-wrap green"><i data-lucide="home" style="width:20px;height:20px;"></i></div><div><h3><?php echo formatPrice($revSales); ?></h3><span>Property Sales Revenue</span></div></div>
        <div class="dash-card"><div class="ic-wrap purple"><i data-lucide="key-round" style="width:20px;height:20px;"></i></div><div><h3><?php echo formatPrice($revRental); ?></h3><span>Rental Revenue</span></div></div>
        <div class="dash-card"><div class="ic-wrap gold"><i data-lucide="hard-hat" style="width:20px;height:20px;"></i></div><div><h3><?php echo formatPrice($revConstruction); ?></h3><span>Construction Revenue</span></div></div>
        <div class="dash-card"><div class="ic-wrap blue"><i data-lucide="trending-up" style="width:20px;height:20px;"></i></div><div><h3><?php echo formatPrice($revSales + $revRental + $revConstruction); ?></h3><span>Total Revenue</span></div></div>
    </div>
    <div class="details-grid" style="grid-template-columns: 1fr 1fr; gap:24px; margin-top:20px;">
        <div class="panel">
            <div class="panel-head"><h2>Revenue by Project</h2></div>
            <div class="panel-body table-wrap">
                <table class="data-table">
                    <thead><tr><th>Project</th><th>Investment</th><th>Revenue</th></tr></thead>
                    <tbody>
                        <?php while ($rp = $revByProject->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo clean($rp['name']); ?></strong></td>
                            <td><?php echo formatPrice($rp['total_investment']); ?></td>
                            <td style="color:var(--success);font-weight:bold;"><?php echo formatPrice($rp['revenue_generated']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="panel">
            <div class="panel-head"><h2>Revenue by Property</h2></div>
            <div class="panel-body table-wrap">
                <table class="data-table">
                    <thead><tr><th>Property</th><th>Revenue</th></tr></thead>
                    <tbody>
                        <?php while ($rpp = $revByProperty->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo clean($rpp['title']); ?></td>
                            <td style="color:var(--success);font-weight:bold;"><?php echo formatPrice($rpp['revenue']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ===================== PROJECT PROFITABILITY ===================== -->
<div id="project-profitability" class="settings-section" style="display:none;">
    <div class="dash-stats">
        <div class="dash-card"><div class="ic-wrap navy"><i data-lucide="hard-hat" style="width:20px;height:20px;"></i></div><div><h3><?php echo (int)$activeProjects; ?></h3><span>Active Projects</span></div></div>
        <div class="dash-card"><div class="ic-wrap green"><i data-lucide="check-circle-2" style="width:20px;height:20px;"></i></div><div><h3><?php echo (int)$completedProjects; ?></h3><span>Completed</span></div></div>
        <div class="dash-card"><div class="ic-wrap gold"><i data-lucide="trending-up" style="width:20px;height:20px;"></i></div><div><h3><?php echo round($avgProjectROI, 1); ?>%</h3><span>Avg ROI</span></div></div>
        <div class="dash-card"><div class="ic-wrap blue"><i data-lucide="banknote" style="width:20px;height:20px;"></i></div><div><h3><?php echo formatPrice($netProjProfit); ?></h3><span>Total Net Profit</span></div></div>
    </div>
    <div class="panel" style="margin-top:20px;">
        <div class="panel-head"><h2>All Projects — ROI & Profitability</h2></div>
        <div class="panel-body table-wrap">
            <table class="data-table">
                <thead><tr><th>Project</th><th>Status</th><th>Investment</th><th>Revenue</th><th>Net Profit</th><th>ROI</th></tr></thead>
                <tbody>
                    <?php if ($projectsList && $projectsList->num_rows): while ($p = $projectsList->fetch_assoc()):
                        $isPos = (float)$p['net_profit'] >= 0;
                        $sc = ['Completed'=>'status-sold','In Progress'=>'status-pending','Planned'=>'status-for-rent'];
                    ?>
                    <tr>
                        <td><strong><?php echo clean($p['name']); ?></strong></td>
                        <td><span class="status-badge <?php echo $sc[$p['status']]??'status-pending'; ?>"><?php echo clean($p['status']); ?></span></td>
                        <td><?php echo formatPrice($p['total_investment']); ?></td>
                        <td><?php echo formatPrice($p['revenue_generated']); ?></td>
                        <td style="color:var(--<?php echo $isPos?'success':'danger'; ?>);font-weight:bold;"><?php echo ($isPos?'+':'').formatPrice($p['net_profit']); ?></td>
                        <td style="color:var(--<?php echo $isPos?'success':'danger'; ?>);font-weight:bold;"><?php echo $p['roi']; ?>%</td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="6" class="empty-state">No project data available.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===================== PROPERTY PROFITABILITY ===================== -->
<div id="property-profitability" class="settings-section" style="display:none;">
    <div class="panel">
        <div class="panel-head"><h2>Top Properties by Profitability</h2></div>
        <div class="panel-body table-wrap">
            <table class="data-table">
                <thead><tr><th>Property</th><th>Purchase Cost</th><th>Expenses</th><th>Revenue</th><th>Net Profit</th></tr></thead>
                <tbody>
                    <?php if ($propertyProfit && $propertyProfit->num_rows): while ($pp = $propertyProfit->fetch_assoc()):
                        $net = $pp['revenue'] - ($pp['cost'] + $pp['total_expenses']);
                        $isPos = $net >= 0;
                    ?>
                    <tr>
                        <td><strong><?php echo clean($pp['title']); ?></strong></td>
                        <td><?php echo formatPrice($pp['cost']); ?></td>
                        <td style="color:var(--danger);">-<?php echo formatPrice($pp['total_expenses']); ?></td>
                        <td style="color:var(--success);">+<?php echo formatPrice($pp['revenue']); ?></td>
                        <td style="color:var(--<?php echo $isPos?'success':'danger'; ?>);font-weight:bold;"><?php echo ($isPos?'+':'').formatPrice($net); ?></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="5" class="empty-state">No profitability data yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===================== AGENT PERFORMANCE ===================== -->
<div id="agent-performance" class="settings-section" style="display:none;">
    <div class="panel">
        <div class="panel-head">
            <h2>Agent Performance Metrics</h2>
        </div>
        <div class="panel-body table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Agent</th>
                        <th>Appointments</th>
                        <th>Viewings</th>
                        <th>Viewings Done</th>
                        <th>Deals Closed</th>
                        <th>Revenue</th>
                        <th>Commissions</th>
                        <th>Conversion</th>
                        <th>Avg Deal Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($agentPerformance && $agentPerformance->num_rows > 0): while ($ag = $agentPerformance->fetch_assoc()):
                        $conv = $ag['viewings'] > 0 ? round(($ag['deals_closed'] / $ag['viewings']) * 100, 1) : 0;
                        $avgDeal = $ag['deals_closed'] > 0 ? $ag['revenue'] / $ag['deals_closed'] : 0;
                    ?>
                    <tr>
                        <td><strong><?php echo clean($ag['Agent']); ?></strong></td>
                        <td><?php echo $ag['total_appointments']; ?></td>
                        <td><?php echo $ag['viewings']; ?></td>
                        <td><?php echo $ag['completed_viewings']; ?></td>
                        <td><span class="status-badge status-sold"><?php echo $ag['deals_closed']; ?></span></td>
                        <td style="color:var(--success);font-weight:bold;"><?php echo formatPrice($ag['revenue']); ?></td>
                        <td><?php echo formatPrice($ag['commissions']); ?></td>
                        <td>
                            <div class="chart-bar-track" style="margin-bottom:3px;">
                                <div class="chart-bar-fill" style="width:<?php echo min(100,$conv); ?>%;background:var(--primary);"></div>
                            </div>
                            <small><?php echo $conv; ?>%</small>
                        </td>
                        <td><?php echo formatPrice($avgDeal); ?></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="9" class="empty-state">No agent performance data available.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===================== FINANCIAL REPORTS ===================== -->
<div id="financial" class="settings-section" style="display:none;">
    <!-- Income / P&L Summary -->
    <div class="dash-stats">
        <div class="dash-card"><div class="ic-wrap green"><i data-lucide="banknote" style="width:20px;height:20px;"></i></div><div><h3><?php echo formatPrice($totalCollected); ?></h3><span>Total Income (Collected)</span></div></div>
        <div class="dash-card"><div class="ic-wrap red"><i data-lucide="receipt" style="width:20px;height:20px;"></i></div><div><h3><?php echo formatPrice($totalExpensesPaid); ?></h3><span>Total Expenses (Paid)</span></div></div>
        <div class="dash-card"><div class="ic-wrap <?php echo $netCashFlow >= 0 ? 'gold' : 'red'; ?>"><i data-lucide="bar-chart-2" style="width:20px;height:20px;"></i></div><div><h3 style="color:var(--<?php echo $netCashFlow >= 0 ? 'success' : 'danger'; ?>)"><?php echo formatPrice($netCashFlow); ?></h3><span>Net Profit / Loss</span></div></div>
        <div class="dash-card"><div class="ic-wrap navy"><i data-lucide="file-text" style="width:20px;height:20px;"></i></div><div><h3 style="color:var(--danger)"><?php echo formatPrice($totalOutstanding); ?></h3><span>Outstanding Invoices</span></div></div>
    </div>

    <div class="details-grid" style="grid-template-columns: 1fr 1fr; gap:24px; margin-top:20px;">
        <!-- Outstanding Invoices Breakdown -->
        <div class="panel">
            <div class="panel-head"><h2>Outstanding Invoice Breakdown</h2><a href="invoices.php" class="btn btn-outline btn-sm">Manage →</a></div>
            <div class="panel-body table-wrap">
                <table class="data-table">
                    <thead><tr><th>Status</th><th>Count</th><th>Outstanding</th></tr></thead>
                    <tbody>
                        <?php while ($oi = $outstandingInvs->fetch_assoc()): ?>
                        <tr>
                            <td><span class="status-badge status-pending"><?php echo clean($oi['status']); ?></span></td>
                            <td><?php echo $oi['cnt']; ?></td>
                            <td style="color:var(--danger);font-weight:bold;"><?php echo formatPrice($oi['outstanding']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Expense Analysis by Category -->
        <div class="panel">
            <div class="panel-head"><h2>Expense Analysis by Category</h2><a href="expenses.php" class="btn btn-outline btn-sm">Manage →</a></div>
            <div class="panel-body table-wrap">
                <table class="data-table">
                    <thead><tr><th>Category</th><th>Total Spent</th></tr></thead>
                    <tbody>
                        <?php while ($ec = $expenseByCategory->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo clean($ec['name']); ?></td>
                            <td style="color:var(--danger);">-<?php echo formatPrice($ec['total']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Budget vs Actual -->
    <div class="panel" style="margin-top:20px;">
        <div class="panel-head"><h2>Budget vs Actual Spending</h2><a href="budgets.php" class="btn btn-outline btn-sm">Manage →</a></div>
        <div class="panel-body table-wrap">
            <table class="data-table">
                <thead><tr><th>Department</th><th>Period</th><th>Allocated</th><th>Spent</th><th>Remaining</th><th>Usage %</th></tr></thead>
                <tbody>
                    <?php while ($bc = $budgetComparison->fetch_assoc()):
                        $pct = $bc['allocated_amount'] > 0 ? min(100, round(($bc['spent_amount'] / $bc['allocated_amount']) * 100)) : 0;
                        $barColor = $pct >= 90 ? 'var(--danger)' : ($pct >= 70 ? 'var(--gold-500, #f59e0b)' : 'var(--success)');
                        $remColor = $bc['remaining'] < 0 ? 'danger' : 'success';
                    ?>
                    <tr>
                        <td><strong><?php echo clean($bc['department']); ?></strong></td>
                        <td><small><?php echo clean($bc['period_type']); ?> — <?php echo clean($bc['period_label']); ?></small></td>
                        <td><?php echo formatPrice($bc['allocated_amount']); ?></td>
                        <td><?php echo formatPrice($bc['spent_amount']); ?></td>
                        <td style="color:var(--<?php echo $remColor; ?>);font-weight:bold;"><?php echo formatPrice(abs($bc['remaining'])); ?></td>
                        <td style="min-width:100px;">
                            <div class="chart-bar-track"><div class="chart-bar-fill" style="background:<?php echo $barColor; ?>;width:<?php echo $pct; ?>%;"></div></div>
                            <small><?php echo $pct; ?>%</small>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Payments Collection -->
    <div class="panel" style="margin-top:20px;">
        <div class="panel-head"><h2>Recent Payment Collection</h2><a href="payments.php" class="btn btn-outline btn-sm">View All →</a></div>
        <div class="panel-body table-wrap">
            <table class="data-table">
                <thead><tr><th>Date</th><th>Invoice</th><th>Customer</th><th>Method</th><th>Amount</th></tr></thead>
                <tbody>
                    <?php while ($rp = $recentPaymentsF->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('M j, Y', strtotime($rp['payment_date'])); ?></td>
                        <td><strong><?php echo clean($rp['invoice_ref'] ?? 'N/A'); ?></strong></td>
                        <td><?php echo clean($rp['customer_name'] ?? 'N/A'); ?></td>
                        <td><span class="status-badge status-pending"><?php echo clean($rp['payment_method']); ?></span></td>
                        <td style="color:var(--success);font-weight:bold;">+<?php echo formatPrice($rp['amount']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===================== OPERATIONAL REPORTS ===================== -->
<div id="operational" class="settings-section" style="display:none;">
    <!-- Property Operations -->
    <h3 style="margin-bottom:15px; padding-bottom:10px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px;"><i data-lucide="home" style="width:18px;height:18px;"></i> Property Operations</h3>
    <div class="dash-stats" style="margin-bottom:25px;">
        <div class="dash-card"><div class="ic-wrap navy"><i data-lucide="building-2" style="width:20px;height:20px;"></i></div><div><h3><?php echo (int)$totalProperties; ?></h3><span>Total Properties</span></div></div>
        <div class="dash-card"><div class="ic-wrap green"><i data-lucide="list-checks" style="width:20px;height:20px;"></i></div><div><h3><?php echo (int)$totalAvailable; ?></h3><span>Available</span></div></div>
        <div class="dash-card"><div class="ic-wrap gold"><i data-lucide="badge-check" style="width:20px;height:20px;"></i></div><div><h3><?php echo (int)$totalSold; ?></h3><span>Sold</span></div></div>
        <div class="dash-card"><div class="ic-wrap blue"><i data-lucide="key-round" style="width:20px;height:20px;"></i></div><div><h3><?php echo (int)$totalRented; ?></h3><span>Rented</span></div></div>
        <div class="dash-card"><div class="ic-wrap purple"><i data-lucide="percent" style="width:20px;height:20px;"></i></div><div><h3><?php echo $occupancyRate; ?>%</h3><span>Occupancy Rate</span></div></div>
    </div>

    <!-- CRM / Lead Operations -->
    <h3 style="margin-bottom:15px; padding-bottom:10px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px;"><i data-lucide="users" style="width:18px;height:18px;"></i> CRM &amp; Appointment Operations</h3>
    <div class="dash-stats" style="margin-bottom:25px;">
        <div class="dash-card"><div class="ic-wrap navy"><i data-lucide="mail" style="width:20px;height:20px;"></i></div><div><h3><?php echo (int)$totalInquiries; ?></h3><span>Total Inquiries</span></div></div>
        <div class="dash-card"><div class="ic-wrap red"><i data-lucide="bell" style="width:20px;height:20px;"></i></div><div><h3><?php echo (int)$unreadInquiries; ?></h3><span>Unread Inquiries</span></div></div>
        <div class="dash-card"><div class="ic-wrap gold"><i data-lucide="calendar" style="width:20px;height:20px;"></i></div><div><h3><?php echo (int)$totalViewings; ?></h3><span>Total Viewings</span></div></div>
        <div class="dash-card"><div class="ic-wrap green"><i data-lucide="check-circle-2" style="width:20px;height:20px;"></i></div><div><h3><?php echo (int)$completedViewings; ?></h3><span>Completed Viewings</span></div></div>
        <div class="dash-card"><div class="ic-wrap purple"><i data-lucide="clock" style="width:20px;height:20px;"></i></div><div><h3><?php echo (int)$pendingAppts; ?></h3><span>Pending Appointments</span></div></div>
    </div>

    <!-- Upcoming Appointments -->
    <div class="panel" style="margin-bottom:25px;">
        <div class="panel-head"><h2>Upcoming Confirmed Appointments</h2><a href="appointments.php" class="btn btn-outline btn-sm">Manage →</a></div>
        <div class="panel-body table-wrap">
            <table class="data-table">
                <thead><tr><th>Customer</th><th>Property</th><th>Agent</th><th>Type</th><th>Scheduled</th></tr></thead>
                <tbody>
                    <?php while ($ua = $upcomingAppts->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo clean($ua['customer_name']); ?></strong></td>
                        <td><?php echo clean($ua['property_title'] ?? '—'); ?></td>
                        <td><?php echo clean($ua['agent_name'] ?? '—'); ?></td>
                        <td><span class="status-badge status-pending"><?php echo clean($ua['type']); ?></span></td>
                        <td><?php echo date('M j, Y g:i A', strtotime($ua['scheduled_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Construction Operations -->
    <h3 style="margin-bottom:15px; padding-bottom:10px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px;"><i data-lucide="hard-hat" style="width:18px;height:18px;"></i> Construction Operations</h3>
    <div class="dash-stats" style="margin-bottom:25px;">
        <div class="dash-card"><div class="ic-wrap navy"><i data-lucide="settings-2" style="width:20px;height:20px;"></i></div><div><h3><?php echo (int)$activeProjects; ?></h3><span>Active Projects</span></div></div>
        <div class="dash-card"><div class="ic-wrap green"><i data-lucide="check-circle-2" style="width:20px;height:20px;"></i></div><div><h3><?php echo (int)$completedProjects; ?></h3><span>Completed Projects</span></div></div>
        <div class="dash-card"><div class="ic-wrap gold"><i data-lucide="clipboard-list" style="width:20px;height:20px;"></i></div><div><h3><?php echo (int)$plannedProjects; ?></h3><span>Planned Projects</span></div></div>
        <div class="dash-card"><div class="ic-wrap blue"><i data-lucide="trending-up" style="width:20px;height:20px;"></i></div><div><h3><?php echo round($avgProjectROI, 1); ?>%</h3><span>Avg Project ROI</span></div></div>
    </div>

    <!-- System User Summary -->
    <h3 style="margin-bottom:15px; padding-bottom:10px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px;"><i data-lucide="user" style="width:18px;height:18px;"></i> User Accounts Summary</h3>
    <div class="panel">
        <div class="panel-head"><h2>User Roles Distribution</h2><a href="users.php" class="btn btn-outline btn-sm">Manage →</a></div>
        <div class="panel-body table-wrap">
            <table class="data-table">
                <thead><tr><th>Role</th><th>Total Accounts</th></tr></thead>
                <tbody>
                    <?php while ($usr = $userRoleSummary->fetch_assoc()): ?>
                    <tr>
                        <td><span class="status-badge status-pending"><?php echo ucfirst(clean($usr['role'])); ?></span></td>
                        <td><?php echo $usr['cnt']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===================== CONSTRUCTION OPERATIONS ===================== -->
<div id="construction-operations" class="settings-section" style="display:none;">
    <h3 style="margin-bottom:15px; padding-bottom:10px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px;"><i data-lucide="hard-hat" style="width:18px;height:18px;"></i> Physical Construction Operations</h3>

    <div class="dash-stats" style="margin-bottom:25px;">
        <div class="dash-card"><div class="ic-wrap navy"><i data-lucide="construction" style="width:20px;height:20px;"></i></div><div><h3><?php echo (int)array_sum($constProjData); ?></h3><span>Total Construction Projects</span></div></div>
        <div class="dash-card"><div class="ic-wrap gold"><i data-lucide="wallet" style="width:20px;height:20px;"></i></div><div><h3><?php echo formatPrice($constTotalBudget); ?></h3><span>Total Budget Allocated</span></div></div>
        <div class="dash-card"><div class="ic-wrap red"><i data-lucide="receipt" style="width:20px;height:20px;"></i></div><div><h3><?php echo formatPrice($constTotalSpent); ?></h3><span>Total Spent to Date</span></div></div>
        <div class="dash-card"><div class="ic-wrap green"><i data-lucide="package" style="width:20px;height:20px;"></i></div><div><h3><?php echo formatPrice($constMatVal['instock'] ?? 0); ?></h3><span>Current Inventory Value</span></div></div>
    </div>

    <div class="details-grid" style="grid-template-columns: 1fr 1fr; gap:24px; margin-bottom:25px;">
        <div class="panel">
            <div class="panel-head"><h2>Construction Project Status</h2></div>
            <div class="panel-body">
                <table class="data-table">
                    <thead><tr><th>Status</th><th>Count</th></tr></thead>
                    <tbody>
                        <?php foreach(['Planned','In Progress','Completed','On Hold'] as $st): ?>
                        <tr>
                            <td><?php echo $st; ?></td>
                            <td><strong><?php echo (int)($constProjData[$st] ?? 0); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="panel">
            <div class="panel-head"><h2>Construction Tasks</h2></div>
            <div class="panel-body">
                <table class="data-table">
                    <thead><tr><th>Task Status</th><th>Count</th></tr></thead>
                    <tbody>
                        <?php foreach(['Pending','In Progress','Completed','Overdue'] as $st): ?>
                        <tr>
                            <td><?php echo $st; ?></td>
                            <td><strong><?php echo (int)($constTaskData[$st] ?? 0); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ===================== EXPORTS ===================== -->
<div id="exports" class="settings-section" style="display:none;">
    <div class="panel">
        <div class="panel-head"><h2>Export Reports</h2></div>
        <div class="panel-body">
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:15px; margin-bottom:20px;">
                <div style="border:1px solid var(--border); border-radius:8px; padding:20px; text-align:center;">
                    <div style="display:flex; justify-content:center; margin-bottom:12px;">
                        <div class="ic-wrap green" style="width:56px; height:56px; border-radius:14px;"><i data-lucide="bar-chart-2" style="width:24px;height:24px;"></i></div>
                    </div>
                    <h4>Sales Report (CSV)</h4>
                    <p style="font-size:.85rem; color:var(--text-light); margin-bottom:15px;">All completed sales by agent, location, and month.</p>
                    <button class="btn btn-outline btn-sm" style="display:inline-flex; align-items:center; gap:5px;" onclick="alert('CSV export coming soon.')"><i data-lucide="download" style="width:13px;height:13px;"></i> Export CSV</button>
                </div>
                <div style="border:1px solid var(--border); border-radius:8px; padding:20px; text-align:center;">
                    <div style="display:flex; justify-content:center; margin-bottom:12px;">
                        <div class="ic-wrap gold" style="width:56px; height:56px; border-radius:14px;"><i data-lucide="file-text" style="width:24px;height:24px;"></i></div>
                    </div>
                    <h4>Financial Report (PDF)</h4>
                    <p style="font-size:.85rem; color:var(--text-light); margin-bottom:15px;">Income statement, expenses and P&L summary.</p>
                    <button class="btn btn-outline btn-sm" style="display:inline-flex; align-items:center; gap:5px;" onclick="window.print()"><i data-lucide="printer" style="width:13px;height:13px;"></i> Print as PDF</button>
                </div>
                <div style="border:1px solid var(--border); border-radius:8px; padding:20px; text-align:center;">
                    <div style="display:flex; justify-content:center; margin-bottom:12px;">
                        <div class="ic-wrap navy" style="width:56px; height:56px; border-radius:14px;"><i data-lucide="user-check" style="width:24px;height:24px;"></i></div>
                    </div>
                    <h4>Agent Performance (CSV)</h4>
                    <p style="font-size:.85rem; color:var(--text-light); margin-bottom:15px;">Agent rankings, deals closed and conversion rates.</p>
                    <button class="btn btn-outline btn-sm" style="display:inline-flex; align-items:center; gap:5px;" onclick="alert('CSV export coming soon.')"><i data-lucide="download" style="width:13px;height:13px;"></i> Export CSV</button>
                </div>
                <div style="border:1px solid var(--border); border-radius:8px; padding:20px; text-align:center;">
                    <div style="display:flex; justify-content:center; margin-bottom:12px;">
                        <div class="ic-wrap blue" style="width:56px; height:56px; border-radius:14px;"><i data-lucide="building-2" style="width:24px;height:24px;"></i></div>
                    </div>
                    <h4>Property Report (CSV)</h4>
                    <p style="font-size:.85rem; color:var(--text-light); margin-bottom:15px;">All properties with status, type, and pricing.</p>
                    <button class="btn btn-outline btn-sm" style="display:inline-flex; align-items:center; gap:5px;" onclick="alert('CSV export coming soon.')"><i data-lucide="download" style="width:13px;height:13px;"></i> Export CSV</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
