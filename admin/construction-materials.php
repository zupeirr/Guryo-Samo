<?php
/**
 * admin/construction-materials.php
 * Inventory management for construction materials with stock alerts.
 */
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'Materials & Inventory';
$activeAdminPage = 'construction';
$action = clean($_GET['action'] ?? '');
$defaultProjId = (int)($_GET['project_id'] ?? 0);

if (isset($_GET['delete'])) {
    $s = $conn->prepare("DELETE FROM construction_materials WHERE id = ?");
    $s->bind_param('i', $_GET['delete']);
    $s->execute();
    redirect('construction-materials.php?deleted=1');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id           = (int)($_POST['id'] ?? 0);
    $project_id   = (int)($_POST['project_id'] ?? 0) ?: null;
    $name         = trim($_POST['name'] ?? '');
    $category     = trim($_POST['category'] ?? '');
    $unit         = trim($_POST['unit'] ?? '');
    $quantity     = (float)($_POST['quantity'] ?? 0);
    $unit_cost    = (float)($_POST['unit_cost'] ?? 0);
    $supplier     = trim($_POST['supplier'] ?? '');
    $stock_level  = (float)($_POST['stock_level'] ?? 0);
    $reorder_point= (float)($_POST['reorder_point'] ?? 0);

    if ($id > 0) {
        $s = $conn->prepare("UPDATE construction_materials SET project_id=?,name=?,category=?,unit=?,quantity=?,unit_cost=?,supplier=?,stock_level=?,reorder_point=? WHERE id=?");
        $s->bind_param('isssddsddi', $project_id,$name,$category,$unit,$quantity,$unit_cost,$supplier,$stock_level,$reorder_point,$id);
    } else {
        $s = $conn->prepare("INSERT INTO construction_materials (project_id,name,category,unit,quantity,unit_cost,supplier,stock_level,reorder_point) VALUES (?,?,?,?,?,?,?,?,?)");
        $s->bind_param('isssddsdd', $project_id,$name,$category,$unit,$quantity,$unit_cost,$supplier,$stock_level,$reorder_point);
    }
    $s->execute();
    redirect('construction-materials.php?saved=1');
}

$editItem = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $s = $conn->prepare("SELECT * FROM construction_materials WHERE id = ?");
    $s->bind_param('i', $_GET['id']);
    $s->execute();
    $editItem = $s->get_result()->fetch_assoc();
}

// List
$filterLow = isset($_GET['low_stock']);
$filterProj = (int)($_GET['project'] ?? 0);
$q = clean($_GET['q'] ?? '');
$perPage = 25;

$where  = ['1=1'];
$params = [];
$types  = '';
if ($filterLow)  { $where[] = 'm.stock_level <= m.reorder_point AND m.reorder_point > 0'; }
if ($filterProj) { $where[] = 'm.project_id = ?'; $params[] = $filterProj; $types .= 'i'; }
if ($q)          { $where[] = 'm.name LIKE ?';    $params[] = "%$q%";      $types .= 's'; }
$whereSql = implode(' AND ', $where);

$total = $conn->prepare("SELECT COUNT(*) AS c FROM construction_materials m WHERE $whereSql");
if ($params) $total->bind_param($types, ...$params);
$total->execute();
$totalRows = (int)$total->get_result()->fetch_assoc()['c'];
$pg = paginate($totalRows, $perPage);

$sql  = "SELECT m.*, p.name AS project_name FROM construction_materials m LEFT JOIN construction_projects p ON p.id = m.project_id WHERE $whereSql ORDER BY m.name LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$fp   = array_merge($params, [$pg['per_page'], $pg['offset']]);
$stmt->bind_param($types.'ii', ...$fp);
$stmt->execute();
$items = $stmt->get_result();

$lowStockCount = (int)$conn->query("SELECT COUNT(*) AS c FROM construction_materials WHERE stock_level <= reorder_point AND reorder_point > 0")->fetch_assoc()['c'];
$allProjects   = $conn->query("SELECT id, name FROM construction_projects ORDER BY name");
$totalValue    = (float)$conn->query("SELECT SUM(stock_level * unit_cost) AS tv FROM construction_materials")->fetch_assoc()['tv'];

include 'includes/admin-header.php';
?>

<?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Material saved.</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Material deleted.</div><?php endif; ?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div style="margin-bottom:12px;"><a href="construction-materials.php" class="btn btn-ghost btn-sm" style="display:inline-flex; align-items:center; gap:5px;"><i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Back to Inventory</a></div>
<div class="panel">
    <div class="panel-head"><h2><?php echo $editItem ? 'Edit Material' : 'Add Material / Inventory Item'; ?></h2></div>
    <div class="panel-body">
        <form method="POST" class="admin-form" style="display:grid; grid-template-columns:1fr 1fr; gap:16px; max-width:720px;">
            <?php if($editItem): ?><input type="hidden" name="id" value="<?php echo $editItem['id']; ?>"><?php endif; ?>
            <div style="grid-column:span 2;">
                <label class="form-label">Material Name *</label>
                <input type="text" name="name" class="form-control" required value="<?php echo clean($editItem['name'] ?? ''); ?>">
            </div>
            <div>
                <label class="form-label">Category</label>
                <input type="text" name="category" class="form-control" placeholder="e.g., Cement, Steel, Plumbing" value="<?php echo clean($editItem['category'] ?? ''); ?>">
            </div>
            <div>
                <label class="form-label">Project</label>
                <select name="project_id" class="form-control">
                    <option value="">— General Stock —</option>
                    <?php while($p = $allProjects->fetch_assoc()): ?>
                    <option value="<?php echo $p['id']; ?>" <?php echo ($editItem['project_id'] ?? $defaultProjId)==$p['id']?'selected':''; ?>><?php echo clean($p['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Unit</label>
                <input type="text" name="unit" class="form-control" placeholder="bags, kg, m², pcs" value="<?php echo clean($editItem['unit'] ?? ''); ?>">
            </div>
            <div>
                <label class="form-label">Quantity Ordered</label>
                <input type="number" name="quantity" step="0.01" min="0" class="form-control" value="<?php echo $editItem['quantity'] ?? ''; ?>">
            </div>
            <div>
                <label class="form-label">Unit Cost (USD)</label>
                <input type="number" name="unit_cost" step="0.01" min="0" class="form-control" value="<?php echo $editItem['unit_cost'] ?? ''; ?>">
            </div>
            <div>
                <label class="form-label">Current Stock Level</label>
                <input type="number" name="stock_level" step="0.01" min="0" class="form-control" value="<?php echo $editItem['stock_level'] ?? ''; ?>">
            </div>
            <div>
                <label class="form-label">Reorder Point (alert when stock ≤)</label>
                <input type="number" name="reorder_point" step="0.01" min="0" class="form-control" value="<?php echo $editItem['reorder_point'] ?? ''; ?>">
            </div>
            <div style="grid-column:span 2;">
                <label class="form-label">Supplier</label>
                <input type="text" name="supplier" class="form-control" value="<?php echo clean($editItem['supplier'] ?? ''); ?>">
            </div>
            <div style="grid-column:span 2; display:flex; gap:10px;">
                <button type="submit" class="btn btn-primary" style="display:inline-flex; align-items:center; gap:6px;"><i data-lucide="save" style="width:15px;height:15px;"></i> Save</button>
                <a href="construction-materials.php" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php else: ?>
<!-- KPI strip -->
<div class="dash-stats" style="margin-bottom:1.5rem; grid-template-columns:repeat(3,1fr);">
    <div class="dash-card"><div class="ic-wrap blue"><i data-lucide="package" style="width:20px;height:20px;"></i></div><div><h3><?php echo number_format($totalRows); ?></h3><span>Total Materials</span></div></div>
    <div class="dash-card"><div class="ic-wrap <?php echo $lowStockCount>0?'red':'green'; ?>"><i data-lucide="alert-triangle" style="width:20px;height:20px;"></i></div><div><h3><?php echo $lowStockCount; ?></h3><span>Low Stock Alerts</span></div></div>
    <div class="dash-card"><div class="ic-wrap green"><i data-lucide="circle-dollar-sign" style="width:20px;height:20px;"></i></div><div><h3><?php echo formatPrice($totalValue); ?></h3><span>Total Inventory Value</span></div></div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>Materials &amp; Inventory (<?php echo number_format($totalRows); ?>)</h2>
        <a href="construction-materials.php?action=add" class="btn btn-primary btn-sm">+ Add Material</a>
    </div>
    <div class="search-filter-bar">
        <form method="GET" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            <input type="text" name="q" value="<?php echo clean($q); ?>" placeholder="Search materials…" class="form-control" style="width:200px;">
            <select name="project" class="form-control" style="width:auto;" onchange="this.form.submit()">
                <option value="">All Projects</option>
                <?php $allProjects->data_seek(0); while($p = $allProjects->fetch_assoc()): ?>
                <option value="<?php echo $p['id']; ?>" <?php echo $filterProj==$p['id']?'selected':''; ?>><?php echo clean($p['name']); ?></option>
                <?php endwhile; ?>
            </select>
            <?php if($lowStockCount > 0): ?>
            <a href="construction-materials.php?low_stock=1" class="btn btn-sm" style="background:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; display:inline-flex; align-items:center; gap:5px;"><i data-lucide="alert-triangle" style="width:14px;height:14px;"></i> Low Stock (<?php echo $lowStockCount; ?>)</a>
            <?php endif; ?>
            <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
            <?php if($filterLow||$filterProj||$q): ?><a href="construction-materials.php" class="btn btn-ghost btn-sm">✕ Clear</a><?php endif; ?>
        </form>
    </div>
    <div class="panel-body table-wrap" style="padding:0;">
        <table class="data-table">
            <thead><tr><th>Material</th><th>Category</th><th>Project</th><th>Supplier</th><th>Qty Ordered</th><th>Stock</th><th>Unit Cost</th><th>Total Value</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if($items->num_rows > 0): while($m = $items->fetch_assoc()):
                    $lowS = $m['reorder_point'] > 0 && $m['stock_level'] <= $m['reorder_point'];
                    $totalVal = $m['stock_level'] * $m['unit_cost'];
                ?>
                <tr style="<?php echo $lowS ? 'background:#fff7f7;' : ''; ?>">
                    <td><strong><?php echo clean($m['name']); ?></strong></td>
                    <td style="font-size:0.82rem;"><?php echo clean($m['category'] ?: '—'); ?></td>
                    <td style="font-size:0.82rem;"><?php echo $m['project_name'] ? clean($m['project_name']) : '<span style="color:var(--ink-soft)">General</span>'; ?></td>
                    <td style="font-size:0.82rem;"><?php echo clean($m['supplier'] ?: '—'); ?></td>
                    <td><?php echo $m['quantity']; ?> <?php echo clean($m['unit']); ?></td>
                    <td>
                        <span style="font-weight:700; color:<?php echo $lowS?'#dc2626':'var(--navy-900)'; ?>"><?php echo $m['stock_level']; ?> <?php echo clean($m['unit']); ?></span>
                        <?php if($lowS): ?><br><span style="font-size:0.7rem; color:#dc2626; display:inline-flex; align-items:center; gap:3px;"><i data-lucide="alert-triangle" style="width:11px;height:11px;"></i> Low Stock</span><?php endif; ?>
                    </td>
                    <td><?php echo formatPrice($m['unit_cost']); ?></td>
                    <td><?php echo formatPrice($totalVal); ?></td>
                    <td>
                        <div class="row-actions">
                            <a href="construction-materials.php?action=edit&id=<?php echo $m['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                            <a href="construction-materials.php?delete=<?php echo $m['id']; ?>" class="btn btn-danger btn-sm js-confirm-delete" data-label="this material">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="9" class="empty-state">No materials found. <a href="construction-materials.php?action=add">Add one →</a></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo renderPagination($pg, 'construction-materials.php', ['q'=>$q,'project'=>$filterProj]); ?>
</div>
<?php endif; ?>

<?php include 'includes/admin-footer.php'; ?>
