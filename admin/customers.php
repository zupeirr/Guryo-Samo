<?php
/**
 * admin/customers.php
 * ---------------------------------------------------------------
 * Unified customer directory — combines:
 *   - Registered users (role='customer')
 *   - Unique inquiry/message senders from the messages table
 * No duplicate emails are shown.
 */
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'Customers';
$activeAdminPage = 'customers';

$search = clean($_GET['q'] ?? '');
$filterSource = clean($_GET['source'] ?? ''); // 'registered' | 'inquiry' | ''
$perPage = 25;

// Build a UNION of registered customers + unique message senders
// We mark source so the UI can distinguish them
$searchSql = '';
$havingSql = '';
if ($search) {
    $like = $conn->real_escape_string($search);
    $searchSql = "AND (email LIKE '%$like%' OR full_name LIKE '%$like%')";
}

$sourceSql = '';
if ($filterSource === 'registered') {
    $sourceSql = "HAVING source = 'Registered'";
} elseif ($filterSource === 'inquiry') {
    $sourceSql = "HAVING source = 'Inquiry'";
}

// Combined query — registered users take priority over plain message senders
$unionSql = "
    SELECT 
        u.id AS user_id,
        u.full_name,
        u.email,
        u.created_at AS first_seen,
        'Registered' AS source,
        (SELECT COUNT(*) FROM messages m WHERE m.email = u.email) AS message_count,
        (SELECT COUNT(*) FROM appointments a WHERE a.customer_email = u.email) AS appointment_count
    FROM users u
    WHERE u.role = 'customer' $searchSql

    UNION

    SELECT 
        NULL AS user_id,
        m.name AS full_name,
        m.email,
        MIN(m.created_at) AS first_seen,
        'Inquiry' AS source,
        COUNT(*) AS message_count,
        (SELECT COUNT(*) FROM appointments a WHERE a.customer_email = m.email) AS appointment_count
    FROM messages m
    WHERE m.email NOT IN (SELECT email FROM users WHERE role='customer') $searchSql
    GROUP BY m.email
";

$fullSql  = "SELECT * FROM ($unionSql) AS combined $sourceSql ORDER BY first_seen DESC";
$countSql = "SELECT COUNT(*) AS c FROM ($fullSql) AS total";

$totalRows  = (int)$conn->query($countSql)->fetch_assoc()['c'];
$pg         = paginate($totalRows, $perPage);

$stmt = $conn->prepare("$fullSql LIMIT ? OFFSET ?");
$stmt->bind_param('ii', $pg['per_page'], $pg['offset']);
$stmt->execute();
$customers = $stmt->get_result();

// Stats
$regCount  = (int)$conn->query("SELECT COUNT(*) AS c FROM users WHERE role='customer'")->fetch_assoc()['c'];
$inqCount  = (int)$conn->query("SELECT COUNT(DISTINCT email) AS c FROM messages WHERE email NOT IN (SELECT email FROM users WHERE role='customer')")->fetch_assoc()['c'];
$totalMsgs = (int)$conn->query("SELECT COUNT(*) AS c FROM messages")->fetch_assoc()['c'];

include 'includes/admin-header.php';
?>

<!-- KPI strip -->
<div class="dash-stats" style="margin-bottom:1.5rem; grid-template-columns:repeat(3,1fr);">
    <div class="dash-card"><div class="ic-wrap navy"><i data-lucide="users"></i></div><div><h3><?php echo $regCount; ?></h3><span>Registered Customers</span></div></div>
    <div class="dash-card"><div class="ic-wrap gold"><i data-lucide="contact"></i></div><div><h3><?php echo $inqCount; ?></h3><span>Inquiry-Only Contacts</span></div></div>
    <div class="dash-card"><div class="ic-wrap green"><i data-lucide="messages-square"></i></div><div><h3><?php echo $totalMsgs; ?></h3><span>Total Messages</span></div></div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>Customer Directory (<?php echo number_format($totalRows); ?>)</h2>
    </div>
    <div class="search-filter-bar">
        <form method="GET" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            <input type="text" name="q" value="<?php echo clean($search); ?>" placeholder="Search name or email…" class="form-control" style="width:220px;">
            <select name="source" class="form-control" style="width:auto;" onchange="this.form.submit()">
                <option value="">All Customers</option>
                <option value="registered" <?php echo $filterSource==='registered'?'selected':''; ?>>Registered Only</option>
                <option value="inquiry" <?php echo $filterSource==='inquiry'?'selected':''; ?>>Inquiry Senders Only</option>
            </select>
            <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
            <?php if($search||$filterSource): ?><a href="customers.php" class="btn btn-ghost btn-sm">✕ Clear</a><?php endif; ?>
        </form>
    </div>
    <div class="panel-body table-wrap" style="padding:0;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th>Messages</th>
                    <th>Appointments</th>
                    <th>First Seen</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($customers->num_rows > 0): while ($c = $customers->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo clean($c['full_name']); ?></strong></td>
                    <td style="font-size:0.85rem;"><?php echo clean($c['email']); ?></td>
                    <td>
                        <?php if ($c['source'] === 'Registered'): ?>
                        <span style="font-size:0.75rem; font-weight:700; padding:2px 8px; border-radius:12px; background:#e0f2fe; color:#0369a1;">Registered</span>
                        <?php else: ?>
                        <span style="font-size:0.75rem; font-weight:700; padding:2px 8px; border-radius:12px; background:#fef9c3; color:#854d0e;">Inquiry</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ((int)$c['message_count'] > 0): ?>
                        <a href="messages.php?q=<?php echo urlencode($c['email']); ?>" style="font-weight:700; color:var(--gold-600);"><?php echo $c['message_count']; ?> msg<?php echo $c['message_count']>1?'s':''; ?></a>
                        <?php else: ?>
                        <span style="color:var(--ink-soft);">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php echo (int)$c['appointment_count'] > 0 ? '<strong>'.(int)$c['appointment_count'].'</strong>' : '<span style="color:var(--ink-soft);">—</span>'; ?>
                    </td>
                    <td style="font-size:0.82rem;"><?php echo date('M j, Y', strtotime($c['first_seen'])); ?></td>
                    <td>
                        <div class="row-actions">
                            <a href="messages.php?q=<?php echo urlencode($c['email']); ?>" class="btn btn-primary btn-sm">View Messages</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="7" class="empty-state">No customers found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo renderPagination($pg, 'customers.php', ['q'=>$search,'source'=>$filterSource]); ?>
</div>

<?php include 'includes/admin-footer.php'; ?>
