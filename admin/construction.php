<?php
/**
 * admin/construction.php
 * Construction Module Hub — summary dashboard linking all sub-sections
 */
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle = 'Construction';
$activeAdminPage = 'construction';

// ── Summary stats ────────────────────────────────────────────────────────────
$totalProjects  = (int)$conn->query("SELECT COUNT(*) AS c FROM construction_projects")->fetch_assoc()['c'];
$activeProjects = (int)$conn->query("SELECT COUNT(*) AS c FROM construction_projects WHERE status='In Progress'")->fetch_assoc()['c'];
$totalTasks     = (int)$conn->query("SELECT COUNT(*) AS c FROM construction_tasks")->fetch_assoc()['c'];
$openTasks      = (int)$conn->query("SELECT COUNT(*) AS c FROM construction_tasks WHERE status != 'Completed'")->fetch_assoc()['c'];
$urgentTasks    = (int)$conn->query("SELECT COUNT(*) AS c FROM construction_tasks WHERE priority='Urgent' AND status != 'Completed'")->fetch_assoc()['c'];
$totalContractors = (int)$conn->query("SELECT COUNT(*) AS c FROM contractors WHERE status='Active'")->fetch_assoc()['c'];
$totalMaterials = (int)$conn->query("SELECT COUNT(*) AS c FROM construction_materials")->fetch_assoc()['c'];
$lowStock       = (int)$conn->query("SELECT COUNT(*) AS c FROM construction_materials WHERE stock_level <= reorder_point AND reorder_point > 0")->fetch_assoc()['c'];
$totalDocuments = (int)$conn->query("SELECT COUNT(*) AS c FROM construction_documents")->fetch_assoc()['c'];
$expiringDocs   = (int)$conn->query("SELECT COUNT(*) AS c FROM construction_documents WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetch_assoc()['c'];

// Budget summary
$budgetRow = $conn->query("SELECT SUM(budget) AS tb, SUM(spent) AS ts FROM construction_projects")->fetch_assoc();
$totalBudget = (float)($budgetRow['tb'] ?? 0);
$totalSpent  = (float)($budgetRow['ts'] ?? 0);
$budgetPct   = $totalBudget > 0 ? min(100, round($totalSpent / $totalBudget * 100)) : 0;

// Recent projects
$recentProjects = $conn->query("SELECT * FROM construction_projects ORDER BY created_at DESC LIMIT 5");

// Upcoming/urgent tasks
$urgentTaskList = $conn->query("
    SELECT t.*, p.name AS project_name 
    FROM construction_tasks t 
    LEFT JOIN construction_projects p ON p.id = t.project_id
    WHERE t.status != 'Completed' 
    ORDER BY FIELD(t.priority,'Urgent','High','Normal','Low'), t.due_date ASC 
    LIMIT 6
");

include 'includes/admin-header.php';

$statusColors = [
    'Planned'    => ['bg'=>'#eff6ff','color'=>'#1d4ed8'],
    'In Progress'=> ['bg'=>'#fefce8','color'=>'#854d0e'],
    'On Hold'    => ['bg'=>'#f3f4f6','color'=>'#374151'],
    'Completed'  => ['bg'=>'#f0fdf4','color'=>'#15803d'],
    'Cancelled'  => ['bg'=>'#fef2f2','color'=>'#b91c1c'],
];
$priorityColors = [
    'Urgent' => '#dc2626', 'High' => '#d97706', 'Normal' => '#4b5563', 'Low' => '#6b7280',
];
?>

<!-- ── KPI Strip ─────────────────────────────────────────────────────────── -->
<div class="dash-stats" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); margin-bottom:1.5rem;">
    <div class="dash-card">
        <div class="ic-wrap blue">🏗️</div>
        <div><h3><?php echo $totalProjects; ?></h3><span>Total Projects</span></div>
    </div>
    <div class="dash-card">
        <div class="ic-wrap green">▶️</div>
        <div><h3><?php echo $activeProjects; ?></h3><span>Active</span></div>
    </div>
    <div class="dash-card">
        <div class="ic-wrap <?php echo $urgentTasks > 0 ? 'red' : 'blue'; ?>">📋</div>
        <div><h3><?php echo $openTasks; ?></h3><span>Open Tasks <?php if($urgentTasks): ?><span style="color:#dc2626; font-size:0.72rem;">(<?php echo $urgentTasks; ?> urgent)</span><?php endif; ?></span></div>
    </div>
    <div class="dash-card">
        <div class="ic-wrap blue">👷</div>
        <div><h3><?php echo $totalContractors; ?></h3><span>Active Contractors</span></div>
    </div>
    <div class="dash-card">
        <div class="ic-wrap <?php echo $lowStock > 0 ? 'red' : 'green'; ?>">🧱</div>
        <div><h3><?php echo $totalMaterials; ?></h3><span>Materials <?php if($lowStock): ?><span style="color:#dc2626; font-size:0.72rem;">(<?php echo $lowStock; ?> low)</span><?php endif; ?></span></div>
    </div>
    <div class="dash-card">
        <div class="ic-wrap <?php echo $expiringDocs > 0 ? 'red' : 'blue'; ?>">📄</div>
        <div><h3><?php echo $totalDocuments; ?></h3><span>Documents <?php if($expiringDocs): ?><span style="color:#dc2626; font-size:0.72rem;">(<?php echo $expiringDocs; ?> expiring)</span><?php endif; ?></span></div>
    </div>
</div>

<!-- ── Budget Overview ───────────────────────────────────────────────────── -->
<div class="panel" style="margin-bottom:1.5rem;">
    <div class="panel-head">
        <h2>💰 Overall Budget</h2>
        <a href="construction-projects.php" class="btn btn-ghost btn-sm">Manage Projects →</a>
    </div>
    <div class="panel-body" style="padding:20px 24px;">
        <div style="display:flex; justify-content:space-between; font-size:0.88rem; margin-bottom:8px;">
            <span style="color:var(--ink-soft);">Total Spent: <strong style="color:var(--navy-900);"><?php echo formatPrice($totalSpent); ?></strong></span>
            <span style="color:var(--ink-soft);">Total Budget: <strong><?php echo formatPrice($totalBudget); ?></strong></span>
            <span style="font-weight:700; color:<?php echo $budgetPct > 90 ? '#dc2626' : ($budgetPct > 70 ? '#d97706' : '#15803d'); ?>;"><?php echo $budgetPct; ?>% used</span>
        </div>
        <div style="background:#e5e7eb; border-radius:20px; height:10px; overflow:hidden;">
            <div style="height:100%; width:<?php echo $budgetPct; ?>%; background:<?php echo $budgetPct > 90 ? '#dc2626' : ($budgetPct > 70 ? '#d97706' : 'var(--gold-500)'); ?>; border-radius:20px; transition:width 0.8s ease;"></div>
        </div>
        <?php if ($totalBudget > 0): ?>
        <p style="margin-top:8px; font-size:0.82rem; color:var(--ink-soft);">Remaining: <?php echo formatPrice($totalBudget - $totalSpent); ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- ── Two column: Recent Projects + Urgent Tasks ────────────────────────── -->
<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">

    <!-- Recent Projects -->
    <div class="panel">
        <div class="panel-head">
            <h2>🏗️ Recent Projects</h2>
            <a href="construction-projects.php?action=add" class="btn btn-primary btn-sm">+ New</a>
        </div>
        <div class="panel-body" style="padding:0;">
            <?php if ($recentProjects->num_rows > 0): while($proj = $recentProjects->fetch_assoc()):
                $sc = $statusColors[$proj['status']] ?? ['bg'=>'#f3f4f6','color'=>'#374151'];
                $pct = (int)$proj['progress'];
                $budgPct = $proj['budget'] > 0 ? min(100, round($proj['spent']/$proj['budget']*100)) : 0;
            ?>
            <div style="padding:14px 18px; border-bottom:1px solid var(--border);">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px; margin-bottom:8px;">
                    <div>
                        <a href="construction-projects.php?view=<?php echo $proj['id']; ?>" style="font-weight:600; font-size:0.92rem; color:var(--navy-900); text-decoration:none;"><?php echo clean($proj['name']); ?></a>
                        <div style="font-size:0.75rem; color:var(--ink-soft); margin-top:2px;">
                            <?php echo $proj['start_date'] ? 'Started: '.date('M j, Y', strtotime($proj['start_date'])) : 'No start date'; ?>
                            <?php echo $proj['end_date'] ? ' · Due: '.date('M j, Y', strtotime($proj['end_date'])) : ''; ?>
                        </div>
                    </div>
                    <span style="padding:3px 10px; border-radius:20px; font-size:0.7rem; font-weight:700; white-space:nowrap; background:<?php echo $sc['bg']; ?>; color:<?php echo $sc['color']; ?>;"><?php echo $proj['status']; ?></span>
                </div>
                <!-- Progress bar -->
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="flex:1; background:#e5e7eb; border-radius:10px; height:6px; overflow:hidden;">
                        <div style="height:100%; width:<?php echo $pct; ?>%; background:var(--gold-500); border-radius:10px;"></div>
                    </div>
                    <span style="font-size:0.72rem; font-weight:700; color:var(--ink-soft); min-width:32px; text-align:right;"><?php echo $pct; ?>%</span>
                </div>
                <div style="font-size:0.75rem; color:var(--ink-soft); margin-top:4px;">
                    Budget: <?php echo formatPrice($proj['budget']); ?> · Spent: <?php echo formatPrice($proj['spent']); ?>
                </div>
            </div>
            <?php endwhile; else: ?>
            <div class="empty-state">No projects yet. <a href="construction-projects.php?action=add">Add one →</a></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Urgent / Open Tasks -->
    <div class="panel">
        <div class="panel-head">
            <h2>📋 Open Tasks</h2>
            <a href="construction-tasks.php?action=add" class="btn btn-primary btn-sm">+ New</a>
        </div>
        <div class="panel-body" style="padding:0;">
            <?php if ($urgentTaskList->num_rows > 0): while($task = $urgentTaskList->fetch_assoc()):
                $pc = $priorityColors[$task['priority']] ?? '#4b5563';
            ?>
            <div style="padding:12px 18px; border-bottom:1px solid var(--border); display:flex; gap:10px; align-items:flex-start;">
                <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:<?php echo $pc; ?>; flex-shrink:0; margin-top:5px;"></span>
                <div style="flex:1; min-width:0;">
                    <div style="font-weight:600; font-size:0.88rem; color:var(--navy-900);"><?php echo clean($task['title']); ?></div>
                    <div style="font-size:0.75rem; color:var(--ink-soft);">
                        <?php echo clean($task['project_name'] ?? '—'); ?>
                        <?php if($task['assigned_to']): ?> · <?php echo clean($task['assigned_to']); ?><?php endif; ?>
                        <?php if($task['due_date']): ?> · Due: <?php echo date('M j', strtotime($task['due_date'])); ?><?php endif; ?>
                    </div>
                </div>
                <span style="font-size:0.7rem; font-weight:700; color:<?php echo $pc; ?>; flex-shrink:0;"><?php echo $task['priority']; ?></span>
            </div>
            <?php endwhile; else: ?>
            <div class="empty-state">No open tasks.</div>
            <?php endif; ?>
        </div>
        <?php if ($openTasks > 6): ?>
        <div style="padding:10px 18px; text-align:center; border-top:1px solid var(--border);">
            <a href="construction-tasks.php" style="font-size:0.82rem; color:var(--gold-600);">View all <?php echo $openTasks; ?> tasks →</a>
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- ── Quick-access module cards ─────────────────────────────────────────── -->
<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:1rem;">
    <?php
    $modules = [
        ['construction-projects.php',   '🏗️', 'Projects',   'Manage construction projects, budgets & progress.', '#eff6ff','#1d4ed8'],
        ['construction-tasks.php',      '📋', 'Tasks',      'Work orders, assignments & deadlines.',              '#fefce8','#854d0e'],
        ['construction-materials.php',  '🧱', 'Materials',  'Inventory, suppliers & stock levels.',               '#f0fdf4','#15803d'],
        ['contractors.php',             '👷', 'Contractors','Workers, contractors & contact info.',                '#fdf4ff','#7e22ce'],
        ['construction-documents.php',  '📄', 'Documents',  'Contracts, permits, blueprints & agreements.',       '#fff7ed','#9a3412'],
    ];
    foreach($modules as [$url,$icon,$title,$desc,$bg,$col]): ?>
    <a href="<?php echo $url; ?>" style="display:block; background:<?php echo $bg; ?>; border:1px solid <?php echo $col; ?>22; border-radius:12px; padding:20px; text-decoration:none; transition:transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 20px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
        <div style="font-size:2rem; margin-bottom:10px;"><?php echo $icon; ?></div>
        <div style="font-weight:700; font-size:0.98rem; color:<?php echo $col; ?>; margin-bottom:4px;"><?php echo $title; ?></div>
        <div style="font-size:0.78rem; color:var(--ink-soft); line-height:1.5;"><?php echo $desc; ?></div>
    </a>
    <?php endforeach; ?>
</div>

<?php include 'includes/admin-footer.php'; ?>
