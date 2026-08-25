<?php
/**
 * admin/construction-projects.php
 * Full CRUD for construction projects with budget, progress, and status tracking.
 */
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'Construction Projects';
$activeAdminPage = 'construction';

$action = clean($_GET['action'] ?? '');
$viewId = isset($_GET['view']) ? (int)$_GET['view'] : 0;

// ── DELETE ────────────────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $s = $conn->prepare("DELETE FROM construction_projects WHERE id = ?");
    $s->bind_param('i', $_GET['delete']);
    $s->execute();
    redirect('construction-projects.php?deleted=1');
}

// ── SAVE (add / edit) ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = (int)($_POST['id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $property_id = (int)($_POST['property_id'] ?? 0) ?: null;
    $status      = $_POST['status'] ?? 'Planned';
    $progress    = min(100, max(0, (int)($_POST['progress'] ?? 0)));
    $budget      = (float)($_POST['budget'] ?? 0);
    $spent       = (float)($_POST['spent'] ?? 0);
    $start_date  = $_POST['start_date'] ?: null;
    $end_date    = $_POST['end_date'] ?: null;

    if ($id > 0) {
        $s = $conn->prepare("UPDATE construction_projects SET name=?,description=?,property_id=?,status=?,progress=?,budget=?,spent=?,start_date=?,end_date=? WHERE id=?");
        $s->bind_param('siisiddssi', $name,$description,$property_id,$status,$progress,$budget,$spent,$start_date,$end_date,$id);
    } else {
        $s = $conn->prepare("INSERT INTO construction_projects (name,description,property_id,status,progress,budget,spent,start_date,end_date) VALUES (?,?,?,?,?,?,?,?,?)");
        $s->bind_param('siisiddss', $name,$description,$property_id,$status,$progress,$budget,$spent,$start_date,$end_date);
    }
    $s->execute();
    redirect('construction-projects.php?saved=1');
}

// ── VIEW SINGLE ───────────────────────────────────────────────────────────────
$viewProject = null;
if ($viewId > 0) {
    $s = $conn->prepare("SELECT cp.*, p.title AS property_title FROM construction_projects cp LEFT JOIN properties p ON p.id = cp.property_id WHERE cp.id = ?");
    $s->bind_param('i', $viewId);
    $s->execute();
    $viewProject = $s->get_result()->fetch_assoc();
    if (!$viewProject) redirect('construction-projects.php');
    
    $projTasks = $conn->prepare("SELECT * FROM construction_tasks WHERE project_id = ? ORDER BY FIELD(priority,'Urgent','High','Normal','Low'), due_date ASC");
    $projTasks->bind_param('i', $viewId);
    $projTasks->execute();
    $projTasks = $projTasks->get_result();
    
    $projMaterials = $conn->prepare("SELECT * FROM construction_materials WHERE project_id = ?");
    $projMaterials->bind_param('i', $viewId);
    $projMaterials->execute();
    $projMaterials = $projMaterials->get_result();
}

// ── EDIT FETCH ────────────────────────────────────────────────────────────────
$editProject = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $s = $conn->prepare("SELECT * FROM construction_projects WHERE id = ?");
    $s->bind_param('i', $_GET['id']);
    $s->execute();
    $editProject = $s->get_result()->fetch_assoc();
}

// ── LIST ──────────────────────────────────────────────────────────────────────
$filterStatus = clean($_GET['status'] ?? '');
$q = clean($_GET['q'] ?? '');
$perPage = 20;

$where  = ['1=1'];
$params = [];
$types  = '';
if ($filterStatus) { $where[] = "cp.status = ?"; $params[] = $filterStatus; $types .= 's'; }
if ($q) { $where[] = "cp.name LIKE ?"; $params[] = "%$q%"; $types .= 's'; }
$whereSql = implode(' AND ', $where);

$total = $conn->prepare("SELECT COUNT(*) AS c FROM construction_projects cp WHERE $whereSql");
if ($params) $total->bind_param($types, ...$params);
$total->execute();
$totalRows = (int)$total->get_result()->fetch_assoc()['c'];
$pg = paginate($totalRows, $perPage);

$sql  = "SELECT cp.*, p.title AS property_title FROM construction_projects cp LEFT JOIN properties p ON p.id = cp.property_id WHERE $whereSql ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$fp = array_merge($params, [$pg['per_page'], $pg['offset']]);
$stmt->bind_param($types.'ii', ...$fp);
$stmt->execute();
$projects = $stmt->get_result();

// Properties for dropdown
$propOptions = $conn->query("SELECT id, title FROM properties ORDER BY title");

$statusColors = [
    'Planned'    => ['#eff6ff','#1d4ed8'],
    'In Progress'=> ['#fefce8','#854d0e'],
    'On Hold'    => ['#f3f4f6','#374151'],
    'Completed'  => ['#f0fdf4','#15803d'],
    'Cancelled'  => ['#fef2f2','#b91c1c'],
];

include 'includes/admin-header.php';
?>

<?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Project saved successfully.</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Project deleted.</div><?php endif; ?>

<?php if ($viewProject): ?>
<!-- ═══ SINGLE PROJECT VIEW ════════════════════════════════════════════════ -->
<div style="margin-bottom:12px;">
    <a href="construction-projects.php" class="btn btn-ghost btn-sm">← Back to Projects</a>
    <a href="construction-projects.php?action=edit&id=<?php echo $viewProject['id']; ?>" class="btn btn-primary btn-sm" style="margin-left:8px;">✏️ Edit</a>
    <a href="construction-tasks.php?action=add&project_id=<?php echo $viewProject['id']; ?>" class="btn btn-ghost btn-sm" style="margin-left:8px;">+ Add Task</a>
</div>

<?php
    $sc = $statusColors[$viewProject['status']] ?? ['#f3f4f6','#374151'];
    $pct = (int)$viewProject['progress'];
    $budgPct = $viewProject['budget'] > 0 ? min(100, round($viewProject['spent']/$viewProject['budget']*100)) : 0;
?>
<div class="panel" style="margin-bottom:1.5rem;">
    <div class="panel-body" style="padding:24px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px; margin-bottom:20px;">
            <div>
                <h2 style="font-size:1.5rem; margin:0 0 6px;"><?php echo clean($viewProject['name']); ?></h2>
                <?php if($viewProject['property_title']): ?><p style="font-size:0.85rem; color:var(--ink-soft);">🏢 <?php echo clean($viewProject['property_title']); ?></p><?php endif; ?>
                <?php if($viewProject['description']): ?><p style="margin-top:8px; color:var(--ink-soft);"><?php echo nl2br(clean($viewProject['description'])); ?></p><?php endif; ?>
            </div>
            <span style="padding:6px 16px; border-radius:20px; font-size:0.82rem; font-weight:700; background:<?php echo $sc[0]; ?>; color:<?php echo $sc[1]; ?>;"><?php echo $viewProject['status']; ?></span>
        </div>
        <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:16px; margin-bottom:20px;">
            <div><span style="font-size:0.7rem; text-transform:uppercase; color:var(--ink-soft); letter-spacing:0.08em;">Budget</span><div style="font-weight:700; font-size:1.1rem;"><?php echo formatPrice($viewProject['budget']); ?></div></div>
            <div><span style="font-size:0.7rem; text-transform:uppercase; color:var(--ink-soft); letter-spacing:0.08em;">Spent</span><div style="font-weight:700; font-size:1.1rem; color:<?php echo $budgPct>90?'#dc2626':($budgPct>70?'#d97706':'var(--navy-900)'); ?>"><?php echo formatPrice($viewProject['spent']); ?></div></div>
            <div><span style="font-size:0.7rem; text-transform:uppercase; color:var(--ink-soft); letter-spacing:0.08em;">Remaining</span><div style="font-weight:700; font-size:1.1rem;"><?php echo formatPrice($viewProject['budget']-$viewProject['spent']); ?></div></div>
            <div><span style="font-size:0.7rem; text-transform:uppercase; color:var(--ink-soft); letter-spacing:0.08em;">Start Date</span><div style="font-weight:600;"><?php echo $viewProject['start_date'] ? date('M j, Y', strtotime($viewProject['start_date'])) : '—'; ?></div></div>
            <div><span style="font-size:0.7rem; text-transform:uppercase; color:var(--ink-soft); letter-spacing:0.08em;">End Date</span><div style="font-weight:600;"><?php echo $viewProject['end_date'] ? date('M j, Y', strtotime($viewProject['end_date'])) : '—'; ?></div></div>
        </div>
        <div>
            <div style="display:flex; justify-content:space-between; font-size:0.82rem; margin-bottom:6px;">
                <span>Progress</span><strong><?php echo $pct; ?>%</strong>
            </div>
            <div style="background:#e5e7eb; border-radius:20px; height:12px; overflow:hidden;">
                <div style="height:100%; width:<?php echo $pct; ?>%; background:var(--gold-500); border-radius:20px;"></div>
            </div>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
    <!-- Tasks -->
    <div class="panel">
        <div class="panel-head"><h2>📋 Tasks (<?php echo $projTasks->num_rows; ?>)</h2><a href="construction-tasks.php?action=add&project_id=<?php echo $viewProject['id']; ?>" class="btn btn-primary btn-sm">+</a></div>
        <div class="panel-body" style="padding:0;">
            <?php if($projTasks->num_rows > 0): while($t = $projTasks->fetch_assoc()):
                $pc = ['Urgent'=>'#dc2626','High'=>'#d97706','Normal'=>'#4b5563','Low'=>'#9ca3af'][$t['priority']] ?? '#4b5563';
                $done = $t['status'] === 'Completed';
            ?>
            <div style="padding:10px 16px; border-bottom:1px solid var(--border); display:flex; gap:10px; align-items:center; <?php echo $done ? 'opacity:0.55;' : ''; ?>">
                <span style="width:8px;height:8px;border-radius:50%;background:<?php echo $pc;?>;flex-shrink:0;"></span>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:0.88rem;font-weight:600;<?php echo $done?'text-decoration:line-through':'';?>"><?php echo clean($t['title']); ?></div>
                    <div style="font-size:0.72rem;color:var(--ink-soft);"><?php echo $t['assigned_to'] ? clean($t['assigned_to']).' · ' : ''; ?><?php echo $t['due_date'] ? 'Due '.date('M j',strtotime($t['due_date'])) : ''; ?></div>
                </div>
                <span style="font-size:0.7rem;font-weight:700;color:<?php echo $pc;?>;flex-shrink:0;"><?php echo $t['status']; ?></span>
            </div>
            <?php endwhile; else: ?><div class="empty-state">No tasks.</div><?php endif; ?>
        </div>
    </div>
    <!-- Materials -->
    <div class="panel">
        <div class="panel-head"><h2>🧱 Materials (<?php echo $projMaterials->num_rows; ?>)</h2><a href="construction-materials.php?action=add&project_id=<?php echo $viewProject['id']; ?>" class="btn btn-primary btn-sm">+</a></div>
        <div class="panel-body" style="padding:0;">
            <?php if($projMaterials->num_rows > 0): while($m = $projMaterials->fetch_assoc()):
                $lowS = $m['reorder_point'] > 0 && $m['stock_level'] <= $m['reorder_point'];
            ?>
            <div style="padding:10px 16px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; <?php echo $lowS?'background:#fef2f2':''; ?>">
                <div>
                    <div style="font-size:0.88rem;font-weight:600;"><?php echo clean($m['name']); ?></div>
                    <div style="font-size:0.72rem;color:var(--ink-soft);"><?php echo clean($m['supplier'] ?: '—'); ?></div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:0.82rem;font-weight:700;"><?php echo $m['stock_level']; ?> <?php echo clean($m['unit']); ?></div>
                    <?php if($lowS): ?><div style="font-size:0.7rem;color:#dc2626;font-weight:700;">⚠ Low Stock</div><?php endif; ?>
                </div>
            </div>
            <?php endwhile; else: ?><div class="empty-state">No materials.</div><?php endif; ?>
        </div>
    </div>
</div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
<!-- ═══ ADD / EDIT FORM ═══════════════════════════════════════════════════ -->
<div style="margin-bottom:12px;"><a href="construction-projects.php" class="btn btn-ghost btn-sm">← Back to Projects</a></div>
<div class="panel">
    <div class="panel-head"><h2><?php echo $editProject ? 'Edit Project' : 'New Project'; ?></h2></div>
    <div class="panel-body">
        <form method="POST" class="admin-form" style="display:grid; grid-template-columns:1fr 1fr; gap:16px; max-width:800px;">
            <?php if($editProject): ?><input type="hidden" name="id" value="<?php echo $editProject['id']; ?>"><?php endif; ?>
            <div style="grid-column:span 2;">
                <label class="form-label">Project Name *</label>
                <input type="text" name="name" class="form-control" required value="<?php echo clean($editProject['name'] ?? ''); ?>">
            </div>
            <div style="grid-column:span 2;">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?php echo clean($editProject['description'] ?? ''); ?></textarea>
            </div>
            <div>
                <label class="form-label">Linked Property</label>
                <select name="property_id" class="form-control">
                    <option value="">— None —</option>
                    <?php while($p = $propOptions->fetch_assoc()): ?>
                    <option value="<?php echo $p['id']; ?>" <?php echo ($editProject['property_id'] ?? '') == $p['id'] ? 'selected' : ''; ?>><?php echo clean($p['title']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <?php foreach(['Planned','In Progress','On Hold','Completed','Cancelled'] as $s): ?>
                    <option <?php echo ($editProject['status'] ?? 'Planned') === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Budget (USD)</label>
                <input type="number" name="budget" step="0.01" min="0" class="form-control" value="<?php echo $editProject['budget'] ?? ''; ?>">
            </div>
            <div>
                <label class="form-label">Amount Spent (USD)</label>
                <input type="number" name="spent" step="0.01" min="0" class="form-control" value="<?php echo $editProject['spent'] ?? ''; ?>">
            </div>
            <div>
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo $editProject['start_date'] ?? ''; ?>">
            </div>
            <div>
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo $editProject['end_date'] ?? ''; ?>">
            </div>
            <div style="grid-column:span 2;">
                <label class="form-label">Progress: <span id="progVal"><?php echo $editProject['progress'] ?? 0; ?></span>%</label>
                <input type="range" name="progress" min="0" max="100" value="<?php echo $editProject['progress'] ?? 0; ?>"
                       oninput="document.getElementById('progVal').textContent=this.value"
                       style="width:100%; accent-color:var(--gold-500);">
            </div>
            <div style="grid-column:span 2; display:flex; gap:10px;">
                <button type="submit" class="btn btn-primary">💾 Save Project</button>
                <a href="construction-projects.php" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php else: ?>
<!-- ═══ PROJECTS LIST ════════════════════════════════════════════════════ -->
<div class="panel">
    <div class="panel-head">
        <h2>All Projects (<?php echo number_format($totalRows); ?>)</h2>
        <a href="construction-projects.php?action=add" class="btn btn-primary btn-sm">+ New Project</a>
    </div>
    <div class="search-filter-bar">
        <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <input type="text" name="q" value="<?php echo clean($q); ?>" placeholder="Search projects…" class="form-control" style="width:220px;">
            <select name="status" class="form-control" style="width:auto;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <?php foreach(['Planned','In Progress','On Hold','Completed','Cancelled'] as $s): ?>
                <option <?php echo $filterStatus===$s?'selected':''; ?>><?php echo $s; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
            <?php if($filterStatus||$q): ?><a href="construction-projects.php" class="btn btn-ghost btn-sm">✕ Clear</a><?php endif; ?>
        </form>
    </div>
    <div class="panel-body table-wrap" style="padding:0;">
        <table class="data-table">
            <thead><tr>
                <th>Project</th><th>Property</th><th>Status</th><th>Progress</th><th>Budget</th><th>Spent</th><th>Dates</th><th>Actions</th>
            </tr></thead>
            <tbody>
                <?php if($projects->num_rows > 0): while($p = $projects->fetch_assoc()):
                    $sc = $statusColors[$p['status']] ?? ['#f3f4f6','#374151'];
                    $pct = (int)$p['progress'];
                ?>
                <tr>
                    <td><strong><?php echo clean($p['name']); ?></strong><br><small style="color:var(--ink-soft);"><?php echo clean(mb_strimwidth($p['description'], 0, 50, '…')); ?></small></td>
                    <td style="font-size:0.82rem;"><?php echo $p['property_title'] ? clean($p['property_title']) : '<span style="color:var(--ink-soft);">—</span>'; ?></td>
                    <td><span style="padding:3px 10px;border-radius:20px;font-size:0.72rem;font-weight:700;background:<?php echo $sc[0];?>;color:<?php echo $sc[1];?>;"><?php echo $p['status']; ?></span></td>
                    <td style="min-width:100px;">
                        <div style="display:flex;align-items:center;gap:6px;">
                            <div style="flex:1;background:#e5e7eb;border-radius:10px;height:6px;overflow:hidden;">
                                <div style="height:100%;width:<?php echo $pct;?>%;background:var(--gold-500);border-radius:10px;"></div>
                            </div>
                            <span style="font-size:0.72rem;font-weight:700;"><?php echo $pct;?>%</span>
                        </div>
                    </td>
                    <td><?php echo formatPrice($p['budget']); ?></td>
                    <td><?php echo formatPrice($p['spent']); ?></td>
                    <td style="font-size:0.78rem;white-space:nowrap;">
                        <?php echo $p['start_date'] ? date('M j, Y', strtotime($p['start_date'])) : '—'; ?><br>
                        <span style="color:var(--ink-soft);"><?php echo $p['end_date'] ? '→ '.date('M j, Y', strtotime($p['end_date'])) : ''; ?></span>
                    </td>
                    <td>
                        <div class="row-actions">
                            <a href="construction-projects.php?view=<?php echo $p['id']; ?>" class="btn btn-ghost btn-sm">View</a>
                            <a href="construction-projects.php?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                            <a href="construction-projects.php?delete=<?php echo $p['id']; ?>" class="btn btn-danger btn-sm js-confirm-delete" data-label="this project">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="8" class="empty-state">No projects found. <a href="construction-projects.php?action=add">Add one →</a></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo renderPagination($pg, 'construction-projects.php', ['status'=>$filterStatus,'q'=>$q]); ?>
</div>
<?php endif; ?>

<?php include 'includes/admin-footer.php'; ?>
