<?php
/**
 * admin/contractors.php
 * Manage contractors, workers, suppliers, and their assignments.
 */
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'Contractors & Workers';
$activeAdminPage = 'construction';

$action = clean($_GET['action'] ?? '');

// ── DELETE ────────────────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $s = $conn->prepare("DELETE FROM contractors WHERE id = ?");
    $s->bind_param('i', $_GET['delete']);
    $s->execute();
    redirect('contractors.php?deleted=1');
}

// ── SAVE ──────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = (int)($_POST['id'] ?? 0);
    $name       = trim($_POST['name'] ?? '');
    $type       = $_POST['type'] ?? 'Contractor';
    $company    = trim($_POST['company'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $speciality = trim($_POST['speciality'] ?? '');
    $project_id = (int)($_POST['project_id'] ?? 0) ?: null;
    $daily_rate = (float)($_POST['daily_rate'] ?? 0);
    $rating     = (int)($_POST['rating'] ?? 0);
    $status     = $_POST['status'] ?? 'Active';
    $notes      = trim($_POST['notes'] ?? '');

    if ($id > 0) {
        $s = $conn->prepare("UPDATE contractors SET name=?,type=?,company=?,phone=?,email=?,speciality=?,project_id=?,daily_rate=?,rating=?,status=?,notes=? WHERE id=?");
        $s->bind_param('ssssssidissi', $name,$type,$company,$phone,$email,$speciality,$project_id,$daily_rate,$rating,$status,$notes,$id);
    } else {
        $s = $conn->prepare("INSERT INTO contractors (name,type,company,phone,email,speciality,project_id,daily_rate,rating,status,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $s->bind_param('ssssssidiss', $name,$type,$company,$phone,$email,$speciality,$project_id,$daily_rate,$rating,$status,$notes);
    }
    $s->execute();
    redirect('contractors.php?saved=1');
}

// ── EDIT FETCH ────────────────────────────────────────────────────────────────
$editItem = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $s = $conn->prepare("SELECT * FROM contractors WHERE id = ?");
    $s->bind_param('i', $_GET['id']);
    $s->execute();
    $editItem = $s->get_result()->fetch_assoc();
}

// ── LIST ──────────────────────────────────────────────────────────────────────
$filterType = clean($_GET['type'] ?? '');
$filterProj = (int)($_GET['project'] ?? 0);
$q = clean($_GET['q'] ?? '');
$perPage = 25;

$where  = ['1=1'];
$params = [];
$types  = '';
if ($filterType) { $where[] = 'c.type = ?'; $params[] = $filterType; $types .= 's'; }
if ($filterProj) { $where[] = 'c.project_id = ?'; $params[] = $filterProj; $types .= 'i'; }
if ($q)          { $where[] = '(c.name LIKE ? OR c.company LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; $types .= 'ss'; }
$whereSql = implode(' AND ', $where);

$total = $conn->prepare("SELECT COUNT(*) AS count FROM contractors c WHERE $whereSql");
if ($params) $total->bind_param($types, ...$params);
$total->execute();
$totalRows = (int)$total->get_result()->fetch_assoc()['count'];
$pg = paginate($totalRows, $perPage);

$sql  = "SELECT c.*, p.name AS project_name FROM contractors c LEFT JOIN construction_projects p ON p.id = c.project_id WHERE $whereSql ORDER BY c.name LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$fp   = array_merge($params, [$pg['per_page'], $pg['offset']]);
$stmt->bind_param($types.'ii', ...$fp);
$stmt->execute();
$items = $stmt->get_result();

$allProjects = $conn->query("SELECT id, name FROM construction_projects ORDER BY name");

include 'includes/admin-header.php';
?>

<?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Saved successfully.</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Deleted successfully.</div><?php endif; ?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div style="margin-bottom:12px;"><a href="contractors.php" class="btn btn-ghost btn-sm">← Back to List</a></div>
<div class="panel">
    <div class="panel-head"><h2><?php echo $editItem ? 'Edit Profile' : 'Add Contractor / Worker'; ?></h2></div>
    <div class="panel-body">
        <form method="POST" style="display:grid; grid-template-columns:1fr 1fr; gap:16px; max-width:720px;">
            <?php if($editItem): ?><input type="hidden" name="id" value="<?php echo $editItem['id']; ?>"><?php endif; ?>
            <div style="grid-column:span 2;">
                <label class="form-label">Full Name *</label>
                <input type="text" name="name" class="form-control" required value="<?php echo clean($editItem['name'] ?? ''); ?>">
            </div>
            <div>
                <label class="form-label">Type</label>
                <select name="type" class="form-control">
                    <?php foreach(['Contractor','Subcontractor','Worker','Supplier'] as $t): ?>
                    <option <?php echo ($editItem['type'] ?? 'Contractor')===$t?'selected':''; ?>><?php echo $t; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Company (if applicable)</label>
                <input type="text" name="company" class="form-control" value="<?php echo clean($editItem['company'] ?? ''); ?>">
            </div>
            <div>
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="<?php echo clean($editItem['phone'] ?? ''); ?>">
            </div>
            <div>
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo clean($editItem['email'] ?? ''); ?>">
            </div>
            <div>
                <label class="form-label">Speciality (e.g. Electrician)</label>
                <input type="text" name="speciality" class="form-control" value="<?php echo clean($editItem['speciality'] ?? ''); ?>">
            </div>
            <div>
                <label class="form-label">Current Assignment (Project)</label>
                <select name="project_id" class="form-control">
                    <option value="">— Unassigned —</option>
                    <?php while($p = $allProjects->fetch_assoc()): ?>
                    <option value="<?php echo $p['id']; ?>" <?php echo ($editItem['project_id'] ?? 0)==$p['id']?'selected':''; ?>><?php echo clean($p['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Daily Rate (USD)</label>
                <input type="number" name="daily_rate" step="0.01" min="0" class="form-control" value="<?php echo $editItem['daily_rate'] ?? ''; ?>">
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <?php foreach(['Active','Inactive','Blacklisted'] as $s): ?>
                    <option <?php echo ($editItem['status'] ?? 'Active')===$s?'selected':''; ?>><?php echo $s; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="grid-column:span 2;">
                <label class="form-label">Rating: <span id="ratVal"><?php echo $editItem['rating'] ?? 0; ?></span> / 5</label>
                <input type="range" name="rating" min="0" max="5" value="<?php echo $editItem['rating'] ?? 0; ?>" oninput="document.getElementById('ratVal').textContent=this.value" style="width:100%; max-width:200px;">
            </div>
            <div style="grid-column:span 2;">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3"><?php echo clean($editItem['notes'] ?? ''); ?></textarea>
            </div>
            <div style="grid-column:span 2; display:flex; gap:10px;">
                <button type="submit" class="btn btn-primary">💾 Save</button>
                <a href="contractors.php" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php else: ?>
<div class="panel">
    <div class="panel-head">
        <h2>Contractors & Workers (<?php echo number_format($totalRows); ?>)</h2>
        <a href="contractors.php?action=add" class="btn btn-primary btn-sm">+ Add New</a>
    </div>
    <div class="search-filter-bar">
        <form method="GET" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            <input type="text" name="q" value="<?php echo clean($q); ?>" placeholder="Search name or company…" class="form-control" style="width:200px;">
            <select name="type" class="form-control" style="width:auto;" onchange="this.form.submit()">
                <option value="">All Types</option>
                <?php foreach(['Contractor','Subcontractor','Worker','Supplier'] as $t): ?>
                <option <?php echo $filterType===$t?'selected':''; ?>><?php echo $t; ?></option>
                <?php endforeach; ?>
            </select>
            <select name="project" class="form-control" style="width:auto;" onchange="this.form.submit()">
                <option value="">All Projects</option>
                <?php $allProjects->data_seek(0); while($p = $allProjects->fetch_assoc()): ?>
                <option value="<?php echo $p['id']; ?>" <?php echo $filterProj==$p['id']?'selected':''; ?>><?php echo clean($p['name']); ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
            <?php if($filterType||$filterProj||$q): ?><a href="contractors.php" class="btn btn-ghost btn-sm">✕ Clear</a><?php endif; ?>
        </form>
    </div>
    <div class="panel-body table-wrap" style="padding:0;">
        <table class="data-table">
            <thead><tr><th>Name / Company</th><th>Type & Spec</th><th>Contact</th><th>Assignment</th><th>Rate</th><th>Status / Rating</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if($items->num_rows > 0): while($i = $items->fetch_assoc()): ?>
                <tr style="<?php echo $i['status']==='Blacklisted' ? 'background:#fef2f2;opacity:0.8;' : ($i['status']==='Inactive'?'opacity:0.6;':''); ?>">
                    <td>
                        <strong><?php echo clean($i['name']); ?></strong>
                        <?php if($i['company']): ?><br><small style="color:var(--ink-soft);"><?php echo clean($i['company']); ?></small><?php endif; ?>
                    </td>
                    <td style="font-size:0.82rem;">
                        <span style="font-weight:600;"><?php echo $i['type']; ?></span>
                        <?php if($i['speciality']): ?><br><span style="color:var(--ink-soft);"><?php echo clean($i['speciality']); ?></span><?php endif; ?>
                    </td>
                    <td style="font-size:0.82rem;">
                        <?php echo $i['phone'] ? '📞 '.clean($i['phone']) : ''; ?>
                        <?php echo $i['email'] ? '<br>✉ '.clean($i['email']) : ''; ?>
                    </td>
                    <td style="font-size:0.82rem;"><?php echo $i['project_name'] ? clean($i['project_name']) : '<span style="color:var(--ink-soft)">Unassigned</span>'; ?></td>
                    <td><?php echo formatPrice($i['daily_rate']); ?><span style="font-size:0.7rem;color:var(--ink-soft);">/day</span></td>
                    <td>
                        <span style="font-size:0.75rem;font-weight:700;color:<?php echo $i['status']==='Active'?'#15803d':($i['status']==='Blacklisted'?'#b91c1c':'#6b7280'); ?>;"><?php echo $i['status']; ?></span>
                        <div style="color:var(--gold-500);font-size:0.8rem;letter-spacing:1px;margin-top:2px;">
                            <?php echo str_repeat('★', (int)$i['rating']) . str_repeat('☆', 5 - (int)$i['rating']); ?>
                        </div>
                    </td>
                    <td>
                        <div class="row-actions">
                            <a href="contractors.php?action=edit&id=<?php echo $i['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                            <a href="contractors.php?delete=<?php echo $i['id']; ?>" class="btn btn-danger btn-sm js-confirm-delete" data-label="this contractor">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="7" class="empty-state">No records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo renderPagination($pg, 'contractors.php', ['q'=>$q,'type'=>$filterType,'project'=>$filterProj]); ?>
</div>
<?php endif; ?>

<?php include 'includes/admin-footer.php'; ?>
