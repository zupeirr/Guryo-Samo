<?php
/**
 * admin/construction-documents.php
 * Document and permit management for construction projects.
 */
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'Construction Documents';
$activeAdminPage = 'construction';
$action = clean($_GET['action'] ?? '');

// ── DELETE ────────────────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    
    // Fetch file path to delete actual file
    $s = $conn->prepare("SELECT file_path FROM construction_documents WHERE id = ?");
    $s->bind_param('i', $delId);
    $s->execute();
    $doc = $s->get_result()->fetch_assoc();
    if ($doc && $doc['file_path'] && file_exists('../' . $doc['file_path'])) {
        unlink('../' . $doc['file_path']);
    }

    $s = $conn->prepare("DELETE FROM construction_documents WHERE id = ?");
    $s->bind_param('i', $delId);
    $s->execute();
    redirect('construction-documents.php?deleted=1');
}

// ── SAVE ──────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = (int)($_POST['id'] ?? 0);
    $project_id = (int)($_POST['project_id'] ?? 0) ?: null;
    $title      = trim($_POST['title'] ?? '');
    $type       = $_POST['type'] ?? 'Other';
    $notes      = trim($_POST['notes'] ?? '');
    $issued_date= $_POST['issued_date'] ?: null;
    $expiry_date= $_POST['expiry_date'] ?: null;

    // Handle file upload
    $file_name = $_POST['existing_file_name'] ?? '';
    $file_path = $_POST['existing_file_path'] ?? '';

    if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['pdf','doc','docx','xls','xlsx','jpg','jpeg','png'];
        $ext = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $newFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', $_FILES['document']['name']);
            $targetDir = '../uploads/documents/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            
            if (move_uploaded_file($_FILES['document']['tmp_name'], $targetDir . $newFileName)) {
                // Delete old file if updating
                if ($id > 0 && $file_path && file_exists('../' . $file_path)) {
                    unlink('../' . $file_path);
                }
                $file_name = $_FILES['document']['name'];
                $file_path = 'uploads/documents/' . $newFileName;
            }
        }
    }

    if ($id > 0) {
        $s = $conn->prepare("UPDATE construction_documents SET project_id=?,title=?,type=?,notes=?,issued_date=?,expiry_date=?,file_name=?,file_path=? WHERE id=?");
        $s->bind_param('isssssssi', $project_id,$title,$type,$notes,$issued_date,$expiry_date,$file_name,$file_path,$id);
    } else {
        $s = $conn->prepare("INSERT INTO construction_documents (project_id,title,type,notes,issued_date,expiry_date,file_name,file_path) VALUES (?,?,?,?,?,?,?,?)");
        $s->bind_param('isssssss', $project_id,$title,$type,$notes,$issued_date,$expiry_date,$file_name,$file_path);
    }
    $s->execute();
    redirect('construction-documents.php?saved=1');
}

// ── EDIT FETCH ────────────────────────────────────────────────────────────────
$editItem = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $s = $conn->prepare("SELECT * FROM construction_documents WHERE id = ?");
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
if ($filterType) { $where[] = 'd.type = ?'; $params[] = $filterType; $types .= 's'; }
if ($filterProj) { $where[] = 'd.project_id = ?'; $params[] = $filterProj; $types .= 'i'; }
if ($q)          { $where[] = 'd.title LIKE ?'; $params[] = "%$q%"; $types .= 's'; }
$whereSql = implode(' AND ', $where);

$total = $conn->prepare("SELECT COUNT(*) AS count FROM construction_documents d WHERE $whereSql");
if ($params) $total->bind_param($types, ...$params);
$total->execute();
$totalRows = (int)$total->get_result()->fetch_assoc()['count'];
$pg = paginate($totalRows, $perPage);

$sql  = "SELECT d.*, p.name AS project_name FROM construction_documents d LEFT JOIN construction_projects p ON p.id = d.project_id WHERE $whereSql ORDER BY d.created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$fp   = array_merge($params, [$pg['per_page'], $pg['offset']]);
$stmt->bind_param($types.'ii', ...$fp);
$stmt->execute();
$items = $stmt->get_result();

$allProjects = $conn->query("SELECT id, name FROM construction_projects ORDER BY name");

// Check for expiring documents (within 30 days)
$expiringDocs = (int)$conn->query("SELECT COUNT(*) AS c FROM construction_documents WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetch_assoc()['c'];

include 'includes/admin-header.php';
?>

<?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Document saved successfully.</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Document deleted.</div><?php endif; ?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div style="margin-bottom:12px;"><a href="construction-documents.php" class="btn btn-ghost btn-sm">← Back to List</a></div>
<div class="panel">
    <div class="panel-head"><h2><?php echo $editItem ? 'Edit Document' : 'Upload Document'; ?></h2></div>
    <div class="panel-body">
        <form method="POST" enctype="multipart/form-data" class="admin-form" style="display:grid; grid-template-columns:1fr 1fr; gap:16px; max-width:720px;">
            <?php if($editItem): ?>
                <input type="hidden" name="id" value="<?php echo $editItem['id']; ?>">
                <input type="hidden" name="existing_file_name" value="<?php echo clean($editItem['file_name']); ?>">
                <input type="hidden" name="existing_file_path" value="<?php echo clean($editItem['file_path']); ?>">
            <?php endif; ?>
            <div style="grid-column:span 2;">
                <label class="form-label">Document Title *</label>
                <input type="text" name="title" class="form-control" required value="<?php echo clean($editItem['title'] ?? ''); ?>">
            </div>
            <div>
                <label class="form-label">Document Type</label>
                <select name="type" class="form-control">
                    <?php foreach(['Contract','Permit','Blueprint','Agreement','Invoice','Other'] as $t): ?>
                    <option <?php echo ($editItem['type'] ?? 'Other')===$t?'selected':''; ?>><?php echo $t; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Associated Project</label>
                <select name="project_id" class="form-control">
                    <option value="">— General / Unassigned —</option>
                    <?php while($p = $allProjects->fetch_assoc()): ?>
                    <option value="<?php echo $p['id']; ?>" <?php echo ($editItem['project_id'] ?? 0)==$p['id']?'selected':''; ?>><?php echo clean($p['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Issued Date</label>
                <input type="date" name="issued_date" class="form-control" value="<?php echo $editItem['issued_date'] ?? ''; ?>">
            </div>
            <div>
                <label class="form-label">Expiry Date (for permits, contracts)</label>
                <input type="date" name="expiry_date" class="form-control" value="<?php echo $editItem['expiry_date'] ?? ''; ?>">
            </div>
            <div style="grid-column:span 2;">
                <label class="form-label">Upload File <?php echo $editItem ? '(leave blank to keep current)' : '*'; ?></label>
                <input type="file" name="document" class="form-control" <?php echo $editItem ? '' : 'required'; ?> accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                <?php if($editItem && $editItem['file_path']): ?>
                    <div style="font-size:0.8rem; margin-top:4px;">Current file: <a href="../<?php echo $editItem['file_path']; ?>" target="_blank"><?php echo clean($editItem['file_name']); ?></a></div>
                <?php endif; ?>
            </div>
            <div style="grid-column:span 2;">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3"><?php echo clean($editItem['notes'] ?? ''); ?></textarea>
            </div>
            <div style="grid-column:span 2; display:flex; gap:10px;">
                <button type="submit" class="btn btn-primary">💾 Save Document</button>
                <a href="construction-documents.php" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php else: ?>

<?php if($expiringDocs > 0): ?>
<div class="alert alert-warning" style="margin-bottom:1.5rem; display:flex; align-items:center; gap:10px;">
    <span style="font-size:1.5rem;">⚠️</span>
    <div>
        <strong>Attention:</strong> You have <?php echo $expiringDocs; ?> document(s) expiring within the next 30 days. Please review them.
    </div>
</div>
<?php endif; ?>

<div class="panel">
    <div class="panel-head">
        <h2>Documents & Permits (<?php echo number_format($totalRows); ?>)</h2>
        <a href="construction-documents.php?action=add" class="btn btn-primary btn-sm">+ Upload Document</a>
    </div>
    <div class="search-filter-bar">
        <form method="GET" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            <input type="text" name="q" value="<?php echo clean($q); ?>" placeholder="Search titles…" class="form-control" style="width:200px;">
            <select name="type" class="form-control" style="width:auto;" onchange="this.form.submit()">
                <option value="">All Types</option>
                <?php foreach(['Contract','Permit','Blueprint','Agreement','Invoice','Other'] as $t): ?>
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
            <?php if($filterType||$filterProj||$q): ?><a href="construction-documents.php" class="btn btn-ghost btn-sm">✕ Clear</a><?php endif; ?>
        </form>
    </div>
    <div class="panel-body table-wrap" style="padding:0;">
        <table class="data-table">
            <thead><tr><th>Document</th><th>Type</th><th>Project</th><th>Dates</th><th>File</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if($items->num_rows > 0): while($i = $items->fetch_assoc()): 
                    $isExpiring = false;
                    $isExpired = false;
                    if ($i['expiry_date']) {
                        $diff = (strtotime($i['expiry_date']) - time()) / 86400;
                        if ($diff < 0) $isExpired = true;
                        elseif ($diff <= 30) $isExpiring = true;
                    }
                ?>
                <tr style="<?php echo $isExpired ? 'background:#fef2f2;' : ($isExpiring ? 'background:#fffbeb;' : ''); ?>">
                    <td>
                        <strong><?php echo clean($i['title']); ?></strong>
                        <?php if($i['notes']): ?><br><small style="color:var(--ink-soft);"><?php echo clean(mb_strimwidth($i['notes'],0,50,'…')); ?></small><?php endif; ?>
                    </td>
                    <td><span style="font-size:0.8rem;font-weight:600;padding:2px 8px;border-radius:12px;background:#f3f4f6;color:#374151;"><?php echo $i['type']; ?></span></td>
                    <td style="font-size:0.82rem;"><?php echo $i['project_name'] ? clean($i['project_name']) : '<span style="color:var(--ink-soft)">General</span>'; ?></td>
                    <td style="font-size:0.82rem;">
                        <?php if($i['issued_date']): ?><span style="color:var(--ink-soft);">Issued:</span> <?php echo date('M j, Y', strtotime($i['issued_date'])); ?><br><?php endif; ?>
                        <?php if($i['expiry_date']): ?>
                            <span style="color:var(--ink-soft);">Expires:</span> <span style="font-weight:700;color:<?php echo $isExpired?'#dc2626':($isExpiring?'#d97706':'#15803d'); ?>"><?php echo date('M j, Y', strtotime($i['expiry_date'])); ?></span>
                            <?php if($isExpired): ?> <span style="color:#dc2626;font-size:0.7rem;">(Expired)</span><?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($i['file_path']): ?>
                        <a href="../<?php echo $i['file_path']; ?>" target="_blank" class="btn btn-ghost btn-sm" title="<?php echo clean($i['file_name']); ?>">📎 View</a>
                        <?php else: ?>
                        <span style="color:var(--ink-soft);font-size:0.8rem;">No file</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="row-actions">
                            <a href="construction-documents.php?action=edit&id=<?php echo $i['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                            <a href="construction-documents.php?delete=<?php echo $i['id']; ?>" class="btn btn-danger btn-sm js-confirm-delete" data-label="this document">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="6" class="empty-state">No documents found. <a href="construction-documents.php?action=add">Upload one →</a></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo renderPagination($pg, 'construction-documents.php', ['q'=>$q,'type'=>$filterType,'project'=>$filterProj]); ?>
</div>
<?php endif; ?>

<?php include 'includes/admin-footer.php'; ?>
