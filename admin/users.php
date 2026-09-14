<?php
/**
 * admin/users.php
 * ---------------------------------------------------------------
 * Lets the admin see every registered account (admins, staff, agents,
 * and customers). Admin accounts cannot be deleted from here.
 */

require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle = 'User Management';
$activeAdminPage = 'users';

// ---- Handle deleting a customer account ----
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $checkStmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $checkStmt->bind_param('i', $deleteId);
    $checkStmt->execute();
    $target = $checkStmt->get_result()->fetch_assoc();

    if ($target && $target['role'] === 'customer') {
        $delStmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $delStmt->bind_param('i', $deleteId);
        $delStmt->execute();
        redirect('users.php?deleted=1');
    } else {
        redirect('users.php?blocked=1');
    }
}

// ---- Filters ----
$q    = trim($_GET['q']    ?? '');
$role = trim($_GET['role'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$allowedRoles = ['admin', 'staff', 'agent', 'customer'];
if ($role && !in_array($role, $allowedRoles, true)) { $role = ''; }

// ---- Build WHERE ----
$where  = [];
$params = [];
$types  = '';

if ($q !== '') {
    $where[]  = '(full_name LIKE ? OR username LIKE ? OR email LIKE ?)';
    $like     = '%' . $q . '%';
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types   .= 'sss';
}
if ($role !== '') {
    $where[]  = 'role = ?';
    $params[] = $role;
    $types   .= 's';
}
$whereSql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// ---- Count ----
$countSql  = "SELECT COUNT(*) AS c FROM users $whereSql";
$countStmt = $conn->prepare($countSql);
if ($params) { $countStmt->bind_param($types, ...$params); }
$countStmt->execute();
$totalRows = (int)$countStmt->get_result()->fetch_assoc()['c'];

// ---- Paginate ----
$pg = paginate($totalRows, $perPage);

// ---- Fetch ----
$sql  = "SELECT id, full_name, username, email, role, created_at FROM users $whereSql ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$fetchParams = $params;
$fetchTypes  = $types . 'ii';
$fetchParams[] = $pg['per_page'];
$fetchParams[] = $pg['offset'];
$stmt->bind_param($fetchTypes, ...$fetchParams);
$stmt->execute();
$result = $stmt->get_result();

// ---- Summary stats (always total, not filtered) ----
$totalUsers     = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$totalCustomers = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='customer'")->fetch_assoc()['c'];
$totalAdmins    = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role IN ('admin','staff')")->fetch_assoc()['c'];
$totalAgents    = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='agent'")->fetch_assoc()['c'];

// $baseUrl not needed anymore as renderPagination handles it

include 'includes/admin-header.php';
?>

<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">User account deleted successfully.</div>
<?php endif; ?>
<?php if (isset($_GET['blocked'])): ?>
    <div class="alert alert-error">Admin/staff/agent accounts cannot be deleted from here.</div>
<?php endif; ?>
<?php if (isset($_GET['updated'])): ?>
    <div class="alert alert-success">User role updated successfully.</div>
<?php endif; ?>

<div class="dash-stats">
    <div class="dash-card">
        <div class="ic-wrap navy"><i data-lucide="users" style="width:20px;height:20px;"></i></div>
        <div><h3><?php echo (int)$totalUsers; ?></h3><span>Total Accounts</span></div>
    </div>
    <div class="dash-card">
        <div class="ic-wrap gold"><i data-lucide="user-check" style="width:20px;height:20px;"></i></div>
        <div><h3><?php echo (int)$totalCustomers; ?></h3><span>Registered Customers</span></div>
    </div>
    <div class="dash-card">
        <div class="ic-wrap purple"><i data-lucide="briefcase" style="width:20px;height:20px;"></i></div>
        <div><h3><?php echo (int)$totalAgents; ?></h3><span>Agents</span></div>
    </div>
    <div class="dash-card">
        <div class="ic-wrap green"><i data-lucide="shield-check" style="width:20px;height:20px;"></i></div>
        <div><h3><?php echo (int)$totalAdmins; ?></h3><span>Admin / Staff</span></div>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>All Registered Users (<?php echo number_format($totalRows); ?>)</h2>
        <a href="roles.php" class="btn btn-ghost btn-sm">Manage Roles</a>
    </div>

    <!-- Search & Filter Bar -->
    <div class="search-filter-bar">
        <div class="search-wrap">
            <span class="search-ic"><i data-lucide="search" style="width:16px;height:16px;"></i></span>
            <form method="GET" style="display:contents;">
                <?php if ($role): ?><input type="hidden" name="role" value="<?php echo clean($role); ?>"><?php endif; ?>
                <input type="text" name="q" value="<?php echo clean($q); ?>"
                       placeholder="Search by name, username, or email…"
                       oninput="this.form.submit()" autocomplete="off">
            </form>
        </div>

        <div class="filter-group">
            <span>Role:</span>
            <?php
            $roleFilters = ['' => 'All', 'customer' => 'Customer', 'agent' => 'Agent', 'staff' => 'Staff', 'admin' => 'Admin'];
            foreach ($roleFilters as $val => $label):
                $qp = array_filter(['q' => $q, 'role' => $val], fn($v) => $v !== '');
                $href = 'users.php' . ($qp ? '?' . http_build_query($qp) : '');
            ?>
            <a href="<?php echo htmlspecialchars($href); ?>"
               class="filter-pill <?php echo $role === $val ? 'active' : ''; ?>">
                <?php echo $label; ?>
            </a>
            <?php endforeach; ?>
        </div>

        <span class="results-count">
            Showing <?php echo number_format(min($pg['offset'] + 1, $totalRows)); ?>–<?php echo number_format(min($pg['offset'] + $pg['per_page'], $totalRows)); ?>
            of <?php echo number_format($totalRows); ?>
        </span>

        <?php if ($q || $role): ?>
            <a href="users.php" class="btn btn-ghost btn-sm" style="display:inline-flex; align-items:center; gap:4px;"><i data-lucide="x" style="width:13px;height:13px;"></i> Clear</a>
        <?php endif; ?>
    </div>

    <div class="panel-body table-wrap" style="padding:0;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Full Name</th><th>Username</th><th>Email</th>
                    <th>Role</th><th>Joined</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): while ($u = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo clean($u['full_name']); ?></td>
                    <td><?php echo clean($u['username']); ?></td>
                    <td><?php echo clean($u['email']); ?></td>
                    <td>
                        <?php
                        $roleClass = match($u['role']) {
                            'admin'    => 'admin',
                            'staff'    => 'staff',
                            'agent'    => 'agent',
                            default    => 'customer'
                        };
                        ?>
                        <span class="status-pill <?php echo $roleClass; ?>">
                            <?php echo ucfirst(clean($u['role'])); ?>
                        </span>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                    <td>
                        <?php if ($u['role'] === 'customer'): ?>
                            <div class="row-actions">
                                <a href="roles.php?promote=<?php echo (int)$u['id']; ?>" class="btn btn-ghost btn-sm">Change Role</a>
                                <a href="users.php?delete=<?php echo (int)$u['id']; ?>"
                                   class="btn btn-danger btn-sm js-confirm-delete"
                                   data-label="this user account">Delete</a>
                            </div>
                        <?php else: ?>
                            <div class="row-actions">
                                <a href="roles.php?user=<?php echo (int)$u['id']; ?>" class="btn btn-ghost btn-sm">Manage</a>
                                <span style="color:var(--ink-soft); font-size:0.82rem;">Protected</span>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="6" class="empty-state">
                        <?php echo ($q || $role) ? 'No users match your search.' : 'No users found.'; ?>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php echo renderPagination($pg, 'users.php', ['q' => $q, 'role' => $role]); ?>
    <?php if ($pg['total_pages'] > 1): ?>
    <p class="pagination-info">
        Page <?php echo $pg['current']; ?> of <?php echo $pg['total_pages']; ?>
        &nbsp;·&nbsp; <?php echo number_format($totalRows); ?> total users
    </p>
    <?php endif; ?>
</div>

<?php include 'includes/admin-footer.php'; ?>
