<?php
/**
 * admin/construction-tasks.php
 * Work orders / tasks CRUD with priority, status, and assignment tracking.
 */
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'Construction Tasks';
$activeAdminPage = 'construction';

$action    = clean($_GET['action'] ?? '');
$defaultProjId = (int)($_GET['project_id'] ?? 0);

// ── DELETE ────────────────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $s = $conn->prepare("DELETE FROM construction_tasks WHERE id = ?");
    $s->bind_param('i', $_GET['delete']);
    $s->execute();
    redirect('construction-tasks.php?deleted=1');
}

// ── SAVE ──────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = (int)($_POST['id'] ?? 0);
    $project_id  = (int)($_POST['project_id'] ?? 0);
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $assigned_to = trim($_POST['assigned_to'] ?? '');
    $priority    = $_POST['priority'] ?? 'Normal';
    $status      = $_POST['status']   ?? 'Pending';
    $due_date    = $_POST['due_date'] ?: null;
    $completed_at = ($status === 'Completed') ? date('Y-m-d H:i:s') : null;

    if ($id > 0) {
        $s = $conn->prepare("UPDATE construction_tasks SET project_id=?,title=?,description=?,assigned_to=?,priority=?,status=?,due_date=?,completed_at=? WHERE id=?");
        $s->bind_param('isssssssi', $project_id,$title,$description,$assigned_to,$priority,$status,$due_date,$completed_at,$id);
    } else {
        $s = $conn->prepare("INSERT INTO construction_tasks (project_id,title,description,assigned_to,priority,status,due_date,completed_at) VALUES (?,?,?,?,?,?,?,?)");
        $s->bind_param('isssssss', $project_id,$title,$description,$assigned_to,$priority,$status,$due_date,$completed_at);
    }
    $s->execute();
    redirect('construction-tasks.php?saved=1');
}

// ── EDIT FETCH ────────────────────────────────────────────────────────────────
$editTask = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $s = $conn->prepare("SELECT * FROM construction_tasks WHERE id = ?");
    $s->bind_param('i', $_GET['id']);
    $s->execute();
    $editTask = $s->get_result()->fetch_assoc();
}

// ── LIST ──────────────────────────────────────────────────────────────────────
$filterStatus   = clean($_GET['status']   ?? '');
$filterPriority = clean($_GET['priority'] ?? '');
$filterProject  = (int)($_GET['project']  ?? 0);
$q = clean($_GET['q'] ?? '');
$perPage = 25;

$where  = ['1=1'];
$params = [];
$types  = '';
if ($filterStatus)   { $where[] = 't.status = ?';          $params[] = $filterStatus;   $types .= 's'; }
if ($filterPriority) { $where[] = 't.priority = ?';         $params[] = $filterPriority; $types .= 's'; }
if ($filterProject)  { $where[] = 't.project_id = ?';       $params[] = $filterProject;  $types .= 'i'; }
if ($q)              { $where[] = 't.title LIKE ?';          $params[] = "%$q%";          $types .= 's'; }
$whereSql = implode(' AND ', $where);

$total = $conn->prepare("SELECT COUNT(*) AS c FROM construction_tasks t WHERE $whereSql");
if ($params) $total->bind_param($types, ...$params);
$total->execute();
$totalRows = (int)$total->get_result()->fetch_assoc()['c'];
$pg = paginate($totalRows, $perPage);

$sql  = "SELECT t.*, p.name AS project_name FROM construction_tasks t LEFT JOIN construction_projects p ON p.id = t.project_id WHERE $whereSql ORDER BY FIELD(t.priority,'Urgent','High','Normal','Low'), t.due_date ASC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$fp   = array_merge($params, [$pg['per_page'], $pg['offset']]);
$stmt->bind_param($types.'ii', ...$fp);
$stmt->execute();
$tasks = $stmt->get_result();

$allProjects = $conn->query("SELECT id, name FROM construction_projects ORDER BY name");

$priorityColors = ['Urgent'=>['#fef2f2','#b91c1c'],'High'=>['#fff7ed','#9a3412'],'Normal'=>['#f0f9ff','#0369a1'],'Low'=>['#f9fafb','#6b7280']];
$statusColors   = ['Pending'=>['#f9fafb','#374151'],'In Progress'=>['#fefce8','#854d0e'],'Completed'=>['#f0fdf4','#15803d'],'Blocked'=>['#fef2f2','#b91c1c']];

include 'includes/admin-header.php';
?>

<?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Task saved successfully.</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Task deleted.</div><?php endif; ?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<!-- ═══ FORM ══════════════════════════════════════════════════════════════ -->
<div style="margin-bottom:12px;"><a href="construction-tasks.php" class="btn btn-ghost btn-sm">← Back to Tasks</a></div>
<div class="panel">
    <div class="panel-head"><h2><?php echo $editTask ? 'Edit Task' : 'New Work Order / Task'; ?></h2></div>
    <div class="panel-body">
        <form method="POST" style="display:grid; grid-template-columns:1fr 1fr; gap:16px; max-width:700px;">
            <?php if($editTask): ?><input type="hidden" name="id" value="<?php echo $editTask['id']; ?>"><?php endif; ?>
            <div style="grid-column:span 2;">
                <label class="form-label">Task Title *</label>
                <input type="text" name="title" class="form-control" required value="<?php echo clean($editTask['title'] ?? ''); ?>">
            </div>
            <div>
                <label class="form-label">Project *</label>
                <select name="project_id" class="form-control" required>
                    <option value="">— Select Project —</option>
                    <?php
                    $allProjects->data_seek(0);
                    while($p = $allProjects->fetch_assoc()):
                        $sel = ($editTask['project_id'] ?? $defaultProjId) == $p['id'] ? 'selected' : '';
                    ?>
                    <option value="<?php echo $p['id']; ?>" <?php echo $sel; ?>><?php echo clean($p['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Assigned To</label>
                <input type="text" name="assigned_to" class="form-control" placeholder="Worker or contractor name" value="<?php echo clean($editTask['assigned_to'] ?? ''); ?>">
            </div>
            <div>
                <label class="form-label">Priority</label>
                <select name="priority" class="form-control">
                    <?php foreach(['Low','Normal','High','Urgent'] as $v): ?>
                    <option <?php echo ($editTask['priority'] ?? 'Normal')===$v?'selected':'';?>><?php echo $v;?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <?php foreach(['Pending','In Progress','Completed','Blocked'] as $v): ?>
                    <option <?php echo ($editTask['status'] ?? 'Pending')===$v?'selected':'';?>><?php echo $v;?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Due Date</label>
                <input type="date" name="due_date" class="form-control" value="<?php echo $editTask['due_date'] ?? ''; ?>">
            </div>
            <div style="grid-column:span 2;">
                <label class="form-label">Description / Notes</label>
                <textarea name="description" class="form-control" rows="3"><?php echo clean($editTask['description'] ?? ''); ?></textarea>
            </div>
            <div style="grid-column:span 2; display:flex; gap:10px;">
                <button type="submit" class="btn btn-primary">💾 Save Task</button>
                <a href="construction-tasks.php" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php else: ?>
<!-- ═══ LIST ═════════════════════════════════════════════════════════════ -->
<div class="panel">
    <div class="panel-head">
        <h2>Work Orders / Tasks (<?php echo number_format($totalRows); ?>)</h2>
        <a href="construction-tasks.php?action=add" class="btn btn-primary btn-sm">+ New Task</a>
    </div>
    <div class="search-filter-bar">
        <form method="GET" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            <input type="text" name="q" value="<?php echo clean($q); ?>" placeholder="Search tasks…" class="form-control" style="width:200px;">
            <select name="project" class="form-control" style="width:auto;" onchange="this.form.submit()">
                <option value="">All Projects</option>
                <?php $allProjects->data_seek(0); while($p = $allProjects->fetch_assoc()): ?>
                <option value="<?php echo $p['id']; ?>" <?php echo $filterProject==$p['id']?'selected':''; ?>><?php echo clean($p['name']); ?></option>
                <?php endwhile; ?>
            </select>
            <select name="priority" class="form-control" style="width:auto;" onchange="this.form.submit()">
                <option value="">All Priorities</option>
                <?php foreach(['Urgent','High','Normal','Low'] as $v): ?><option <?php echo $filterPriority===$v?'selected':''; ?>><?php echo $v; ?></option><?php endforeach; ?>
            </select>
            <select name="status" class="form-control" style="width:auto;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <?php foreach(['Pending','In Progress','Completed','Blocked'] as $v): ?><option <?php echo $filterStatus===$v?'selected':''; ?>><?php echo $v; ?></option><?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
            <?php if($filterStatus||$filterPriority||$filterProject||$q): ?><a href="construction-tasks.php" class="btn btn-ghost btn-sm">✕ Clear</a><?php endif; ?>
        </form>
    </div>
    <div class="panel-body table-wrap" style="padding:0;">
        <table class="data-table">
            <thead><tr><th>Task</th><th>Project</th><th>Assigned To</th><th>Priority</th><th>Status</th><th>Due Date</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if($tasks->num_rows > 0): while($t = $tasks->fetch_assoc()):
                    $pc = $priorityColors[$t['priority']] ?? ['#f9fafb','#6b7280'];
                    $sc = $statusColors[$t['status']]     ?? ['#f9fafb','#374151'];
                    $overdue = $t['due_date'] && $t['status'] !== 'Completed' && strtotime($t['due_date']) < time();
                ?>
                <tr style="<?php echo $overdue ? 'background:#fff7f7;' : ''; ?>">
                    <td>
                        <strong><?php echo clean($t['title']); ?></strong>
                        <?php if($t['description']): ?><br><small style="color:var(--ink-soft);"><?php echo clean(mb_strimwidth($t['description'],0,60,'…')); ?></small><?php endif; ?>
                    </td>
                    <td style="font-size:0.82rem;"><?php echo clean($t['project_name'] ?? '—'); ?></td>
                    <td style="font-size:0.85rem;"><?php echo $t['assigned_to'] ? clean($t['assigned_to']) : '<span style="color:var(--ink-soft)">Unassigned</span>'; ?></td>
                    <td><span style="padding:3px 10px;border-radius:20px;font-size:0.7rem;font-weight:700;background:<?php echo $pc[0];?>;color:<?php echo $pc[1];?>;"><?php echo $t['priority']; ?></span></td>
                    <td><span style="padding:3px 10px;border-radius:20px;font-size:0.7rem;font-weight:700;background:<?php echo $sc[0];?>;color:<?php echo $sc[1];?>;"><?php echo $t['status']; ?></span></td>
                    <td style="font-size:0.82rem; white-space:nowrap; <?php echo $overdue?'color:#dc2626;font-weight:700;':''; ?>">
                        <?php echo $t['due_date'] ? date('M j, Y', strtotime($t['due_date'])) : '—'; ?>
                        <?php if($overdue): ?><br><span style="font-size:0.7rem;">⚠ Overdue</span><?php endif; ?>
                    </td>
                    <td>
                        <div class="row-actions">
                            <a href="construction-tasks.php?action=edit&id=<?php echo $t['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                            <a href="construction-tasks.php?delete=<?php echo $t['id']; ?>" class="btn btn-danger btn-sm js-confirm-delete" data-label="this task">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="7" class="empty-state">No tasks found. <a href="construction-tasks.php?action=add">Add one →</a></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo renderPagination($pg, 'construction-tasks.php', ['q'=>$q,'status'=>$filterStatus,'priority'=>$filterPriority,'project'=>$filterProject]); ?>
</div>
<?php endif; ?>

<?php include 'includes/admin-footer.php'; ?>
