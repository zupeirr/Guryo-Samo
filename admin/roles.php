<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'Roles & Permissions';
$activeAdminPage = 'roles';

$errors = []; $success = '';

// Handle role change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId  = (int)($_POST['user_id'] ?? 0);
    $newRole = clean($_POST['role'] ?? '');

    if (in_array($newRole, ['admin','customer','agent']) && $userId > 0) {
        $conn->begin_transaction();
        try {
            $conn->query("UPDATE users SET role='$newRole' WHERE id=$userId");

            // If promoting to agent, ensure agent profile exists
            if ($newRole === 'agent') {
                $exists = $conn->query("SELECT id FROM agents WHERE user_id=$userId")->num_rows;
                if (!$exists) {
                    $conn->query("INSERT INTO agents (user_id, status, commission_rate) VALUES ($userId, 'active', 5.00)");
                }
            }
            $conn->commit();
            $success = 'Role updated successfully.';
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = 'Error: ' . $e->getMessage();
        }
    }
}

$users = $conn->query("SELECT id, full_name, email, role FROM users ORDER BY full_name ASC");

include 'includes/admin-header.php';
?>
<?php if ($success): ?><div class="alert alert-success" style="margin-bottom:1rem;"><?php echo $success; ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-danger" style="margin-bottom:1rem;"><?php echo implode('<br>',$errors); ?></div><?php endif; ?>
<div class="panel">
    <div class="panel-head"><h2>Roles & Permissions</h2></div>
    <div class="panel-body table-wrap">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Email</th><th>Current Role</th><th>Change Role</th></tr></thead>
            <tbody>
                <?php if ($users && $users->num_rows > 0): while ($u = $users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo clean($u['full_name']); ?></td>
                    <td><?php echo clean($u['email']); ?></td>
                    <td><span class="status-pill <?php echo $u['role']==='admin'?'sold':($u['role']==='agent'?'sale':'rent'); ?>"><?php echo ucfirst($u['role']); ?></span></td>
                    <td>
                        <form method="POST" style="display:flex;gap:6px;">
                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                            <select name="role" class="form-control" style="width:auto;">
                                <option value="customer" <?php echo $u['role']==='customer'?'selected':''; ?>>Customer</option>
                                <option value="agent"    <?php echo $u['role']==='agent'?'selected':''; ?>>Agent</option>
                                <option value="admin"    <?php echo $u['role']==='admin'?'selected':''; ?>>Admin</option>
                            </select>
                            <button class="btn btn-primary btn-sm" type="submit">Save</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="4" class="empty-state">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'includes/admin-footer.php'; ?>
