<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle = 'Customer Messages';
$activeAdminPage = 'messages';

// ── Action Handlers ──────────────────────────────────────────────────────────

// Update status (AJAX-style or redirect)
if (isset($_GET['set_status'])) {
    $sid    = (int)$_GET['id'];
    $sval   = $_GET['set_status'];
    $allowed = ['Unread', 'Read', 'Replied', 'Closed'];
    if (in_array($sval, $allowed, true)) {
        $is_read = ($sval !== 'Unread') ? 1 : 0;
        $s = $conn->prepare("UPDATE messages SET status = ?, is_read = ? WHERE id = ?");
        $s->bind_param('sii', $sval, $is_read, $sid);
        $s->execute();
    }
    redirect('messages.php?updated=1');
}

// Toggle priority
if (isset($_GET['toggle_priority'])) {
    $pid = (int)$_GET['id'];
    $conn->prepare("UPDATE messages SET priority = IF(priority='Normal','Important','Normal') WHERE id = ?")->bind_param('i', $pid) ?: null;
    $s = $conn->prepare("UPDATE messages SET priority = IF(priority='Normal','Important','Normal') WHERE id = ?");
    $s->bind_param('i', $pid);
    $s->execute();
    redirect('messages.php?updated=1');
}

// Toggle archive
if (isset($_GET['toggle_archive'])) {
    $aid = (int)$_GET['id'];
    $s = $conn->prepare("UPDATE messages SET is_archived = IF(is_archived=1,0,1) WHERE id = ?");
    $s->bind_param('i', $aid);
    $s->execute();
    redirect('messages.php?updated=1');
}

// Mark all as read
if (isset($_GET['mark_all_read'])) {
    $conn->query("UPDATE messages SET is_read = 1, status = IF(status='Unread','Read',status) WHERE is_read = 0");
    redirect('messages.php?all_read=1');
}

// Delete a message
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $s = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $s->bind_param('i', $delId);
    $s->execute();
    redirect('messages.php?deleted=1');
}

// ── Filters ──────────────────────────────────────────────────────────────────
$q       = trim($_GET['q']       ?? '');
$status  = trim($_GET['status']  ?? '');
$sort    = in_array($_GET['sort'] ?? '', ['asc', 'desc']) ? $_GET['sort'] : 'desc';
$archive = isset($_GET['archive']) ? 1 : 0;
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$validStatuses = ['', 'Unread', 'Read', 'Replied', 'Closed'];
if (!in_array($status, $validStatuses, true)) { $status = ''; }

// ── Build WHERE ───────────────────────────────────────────────────────────────
$where  = ["is_archived = $archive"];
$params = [];
$types  = '';

if ($q !== '') {
    $where[]  = '(m.name LIKE ? OR m.email LIKE ? OR m.subject LIKE ? OR m.message LIKE ?)';
    $like     = '%' . $q . '%';
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
    $types   .= 'ssss';
}
if ($status !== '') {
    $where[] = 'm.status = ?';
    $params[] = $status;
    $types   .= 's';
}

$whereSql = 'WHERE ' . implode(' AND ', $where);

// ── Count ─────────────────────────────────────────────────────────────────────
$countSql  = "SELECT COUNT(*) AS c FROM messages m $whereSql";
$countStmt = $conn->prepare($countSql);
if ($params) { $countStmt->bind_param($types, ...$params); }
$countStmt->execute();
$totalRows = (int)$countStmt->get_result()->fetch_assoc()['c'];

// ── Paginate ──────────────────────────────────────────────────────────────────
$pg = paginate($totalRows, $perPage);

// ── Fetch (with LEFT JOIN on properties) ─────────────────────────────────────
$orderDir = strtoupper($sort);
$sql  = "SELECT m.*, p.title AS property_title, p.image AS property_image, p.id AS prop_id
         FROM messages m
         LEFT JOIN properties p ON p.id = m.property_id
         $whereSql
         ORDER BY m.priority DESC, m.is_read ASC, m.created_at $orderDir
         LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$fetchParams = array_merge($params, [$pg['per_page'], $pg['offset']]);
$fetchTypes  = $types . 'ii';
$stmt->bind_param($fetchTypes, ...$fetchParams);
$stmt->execute();
$result = $stmt->get_result();

// Summary counts (active inbox only)
$totalMessages  = (int)$conn->query("SELECT COUNT(*) AS c FROM messages WHERE is_archived = 0")->fetch_assoc()['c'];
$unreadMessages = (int)$conn->query("SELECT COUNT(*) AS c FROM messages WHERE is_read = 0 AND is_archived = 0")->fetch_assoc()['c'];
$archivedCount  = (int)$conn->query("SELECT COUNT(*) AS c FROM messages WHERE is_archived = 1")->fetch_assoc()['c'];

include 'includes/admin-header.php';
?>

<?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Message deleted successfully.</div><?php endif; ?>
<?php if (isset($_GET['all_read'])): ?><div class="alert alert-success">All messages marked as read.</div><?php endif; ?>
<?php if (isset($_GET['updated'])): ?><div class="alert alert-success">Message updated.</div><?php endif; ?>

<div class="panel">
    <div class="panel-head">
        <h2>
            <?php echo $archive ? 'Archived Messages' : 'Customer Messages'; ?>
            (<?php echo number_format($totalMessages); ?>
            <?php if ($unreadMessages > 0 && !$archive): ?>
            — <span style="color:var(--danger);"><?php echo $unreadMessages; ?> unread</span>
            <?php endif; ?>)
        </h2>
        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
            <?php if ($archive): ?>
                <a href="messages.php" class="btn btn-ghost btn-sm">← Back to Inbox</a>
            <?php else: ?>
                <?php if ($archivedCount > 0): ?>
                    <a href="messages.php?archive" class="btn btn-ghost btn-sm">📦 Archive (<?php echo $archivedCount; ?>)</a>
                <?php endif; ?>
                <?php if ($unreadMessages > 0): ?>
                    <a href="messages.php?mark_all_read=1" class="btn btn-ghost btn-sm"
                       onclick="return confirm('Mark all messages as read?')">✅ Mark All Read</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="search-filter-bar">
        <div class="search-wrap">
            <span class="search-ic">🔍</span>
            <form method="GET" style="display:contents;">
                <?php if ($status): ?><input type="hidden" name="status" value="<?php echo clean($status); ?>"><?php endif; ?>
                <?php if ($archive): ?><input type="hidden" name="archive" value="1"><?php endif; ?>
                <input type="text" name="q" value="<?php echo clean($q); ?>"
                       placeholder="Search by name, email, or subject…"
                       oninput="this.form.submit()" autocomplete="off">
            </form>
        </div>

        <div class="filter-group">
            <span>Status:</span>
            <?php
            $statusFilters = ['' => 'All', 'Unread' => 'Unread', 'Read' => 'Read', 'Replied' => 'Replied', 'Closed' => 'Closed'];
            foreach ($statusFilters as $val => $label):
                $qp = array_filter(['q' => $q, 'status' => $val, 'archive' => $archive ?: null], fn($v) => $v !== '' && $v !== null);
                $href = 'messages.php' . ($qp ? '?' . http_build_query($qp) : '');
            ?>
            <a href="<?php echo htmlspecialchars($href); ?>"
               class="filter-pill <?php echo $status === $val ? 'active' : ''; ?>">
                <?php echo $label; ?>
                <?php if ($val === 'Unread' && $unreadMessages > 0): ?>
                <span style="background:var(--danger); color:#fff; font-size:0.65rem; padding:1px 5px; border-radius:10px; margin-left:3px;"><?php echo $unreadMessages; ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Sort toggle -->
        <?php $toggleSort = $sort === 'desc' ? 'asc' : 'desc'; ?>
        <a href="?<?php echo http_build_query(array_filter(['q'=>$q,'status'=>$status,'sort'=>$toggleSort,'archive'=>$archive?:null])); ?>"
           class="btn btn-ghost btn-sm" title="Sort by date">
            <?php echo $sort === 'desc' ? '↓ Newest' : '↑ Oldest'; ?>
        </a>

        <span class="results-count">
            Showing <?php echo number_format(min($pg['offset'] + 1, $totalRows)); ?>–<?php echo number_format(min($pg['offset'] + $pg['per_page'], $totalRows)); ?>
            of <?php echo number_format($totalRows); ?>
        </span>

        <?php if ($q || $status): ?>
            <a href="messages.php" class="btn btn-ghost btn-sm">✕ Clear</a>
        <?php endif; ?>
    </div>

    <div class="panel-body table-wrap" style="padding:0;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:24px;"></th><!-- priority star -->
                    <th>From</th>
                    <th>Property</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Received</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): while ($m = $result->fetch_assoc()): ?>
                <?php
                    $isUnread   = !$m['is_read'];
                    $isImportant = $m['priority'] === 'Important';
                    $propImg = ($m['property_image'] && $m['property_image'] !== 'no-image.jpg')
                               ? '../uploads/' . $m['property_image']
                               : '../assets/images/no-image.jpg';
                    $statusColors = [
                        'Unread'  => '#dc2626',
                        'Read'    => '#6b7280',
                        'Replied' => '#16a34a',
                        'Closed'  => '#374151',
                    ];
                    $statusBg = [
                        'Unread'  => '#fee2e2',
                        'Read'    => '#f3f4f6',
                        'Replied' => '#dcfce7',
                        'Closed'  => '#e5e7eb',
                    ];
                    $sc = $m['status'] ?? 'Unread';
                ?>
                <tr style="<?php echo $isUnread ? 'font-weight:600; background:#fafbff;' : ''; ?>">
                    <!-- Priority star -->
                    <td style="text-align:center; padding:8px 4px;">
                        <a href="messages.php?toggle_priority=1&id=<?php echo (int)$m['id']; ?>"
                           title="<?php echo $isImportant ? 'Important – click to mark normal' : 'Mark as important'; ?>"
                           style="font-size:1.1rem; text-decoration:none; line-height:1;">
                            <?php echo $isImportant ? '⭐' : '☆'; ?>
                        </a>
                    </td>
                    <!-- From -->
                    <td>
                        <?php if ($isUnread): ?><span class="unread-dot"></span><?php endif; ?>
                        <span style="font-size:0.95rem;"><?php echo clean($m['name']); ?></span><br>
                        <span style="font-size:0.78rem; color:var(--ink-soft);"><?php echo clean($m['email']); ?></span>
                        <?php if ($m['phone'] && $m['phone'] !== '—'): ?>
                        <br><span style="font-size:0.78rem; color:var(--ink-soft);">📞 <?php echo clean($m['phone']); ?></span>
                        <?php endif; ?>
                    </td>
                    <!-- Property reference -->
                    <td>
                        <?php if ($m['property_title']): ?>
                        <a href="../property-details.php?id=<?php echo (int)$m['prop_id']; ?>" target="_blank"
                           style="display:flex; align-items:center; gap:8px; text-decoration:none; color:var(--ink);">
                            <img src="<?php echo clean($propImg); ?>" alt="" onerror="this.src='../assets/images/no-image.jpg'"
                                 style="width:36px; height:36px; object-fit:cover; border-radius:6px; flex-shrink:0;">
                            <span style="font-size:0.82rem; font-weight:600; line-height:1.3;"><?php echo clean(mb_strimwidth($m['property_title'], 0, 40, '…')); ?></span>
                        </a>
                        <?php else: ?>
                        <span style="color:var(--ink-soft); font-size:0.82rem;">General Inquiry</span>
                        <?php endif; ?>
                    </td>
                    <!-- Subject -->
                    <td style="max-width:200px; font-size:0.88rem;">
                        <?php echo clean(mb_strimwidth($m['subject'] ?: 'No Subject', 0, 50, '…')); ?>
                        <br><span style="font-size:0.75rem; color:var(--ink-soft); font-weight:400;"><?php echo clean(mb_strimwidth($m['message'], 0, 60, '…')); ?></span>
                    </td>
                    <!-- Status badge -->
                    <td>
                        <span style="display:inline-block; padding:3px 10px; border-radius:20px; font-size:0.72rem; font-weight:700; letter-spacing:0.04em;
                                     background:<?php echo $statusBg[$sc]; ?>; color:<?php echo $statusColors[$sc]; ?>;">
                            <?php echo $sc; ?>
                        </span>
                    </td>
                    <!-- Date -->
                    <td style="white-space:nowrap; font-size:0.82rem; color:var(--ink-soft);">
                        <?php echo date('M j, Y', strtotime($m['created_at'])); ?><br>
                        <span style="font-size:0.75rem;"><?php echo date('g:i A', strtotime($m['created_at'])); ?></span>
                    </td>
                    <!-- Actions -->
                    <td>
                        <div class="row-actions" style="gap:6px; flex-wrap:wrap;">
                            <button class="btn btn-primary btn-sm" onclick="openMsgModal(this)"
                                    data-id="<?php echo (int)$m['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($m['name']); ?>"
                                    data-email="<?php echo htmlspecialchars($m['email']); ?>"
                                    data-phone="<?php echo htmlspecialchars($m['phone'] ?: ''); ?>"
                                    data-date="<?php echo date('M j, Y g:i A', strtotime($m['created_at'])); ?>"
                                    data-subject="<?php echo htmlspecialchars($m['subject'] ?: 'No Subject'); ?>"
                                    data-msg="<?php echo htmlspecialchars($m['message']); ?>"
                                    data-status="<?php echo htmlspecialchars($sc); ?>"
                                    data-property="<?php echo htmlspecialchars($m['property_title'] ?: ''); ?>"
                                    data-propid="<?php echo (int)($m['prop_id'] ?? 0); ?>"
                                    data-propimg="<?php echo htmlspecialchars($propImg); ?>">
                                View
                            </button>
                            <!-- Quick archive toggle -->
                            <a href="messages.php?toggle_archive=1&id=<?php echo (int)$m['id']; ?>"
                               class="btn btn-ghost btn-sm" title="<?php echo $m['is_archived'] ? 'Restore' : 'Archive'; ?>">
                                <?php echo $m['is_archived'] ? '📤' : '📥'; ?>
                            </a>
                            <a href="messages.php?delete=<?php echo (int)$m['id']; ?>"
                               class="btn btn-danger btn-sm js-confirm-delete" data-label="this message">🗑</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="7" class="empty-state">
                        <?php echo ($q || $status) ? 'No messages match your search.' : ($archive ? 'No archived messages.' : 'No customer messages yet.'); ?>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php echo renderPagination($pg, 'messages.php', ['q' => $q, 'status' => $status, 'sort' => $sort]); ?>
    <?php if ($pg['total_pages'] > 1): ?>
    <p class="pagination-info">
        Page <?php echo $pg['current']; ?> of <?php echo $pg['total_pages']; ?>
        &nbsp;·&nbsp; <?php echo number_format($totalRows); ?> total messages
    </p>
    <?php endif; ?>
</div>

<!-- ── Message Detail Modal ────────────────────────────────────────────────── -->
<div id="msgModal" class="modal-overlay">
    <div class="modal-content" style="max-width:640px; padding:0; overflow:hidden;">
        <div class="modal-header" style="padding:20px 24px; border-bottom:1px solid var(--border); background:#fff;">
            <h3 id="modalSubject" style="margin:0; font-size:1.15rem; font-weight:700; color:var(--navy-900);">Message Subject</h3>
            <button class="modal-close" onclick="closeMsgModal()" style="font-size:1.2rem; color:var(--ink-soft);">&times;</button>
        </div>
        <div class="modal-body" style="padding:0; background:#fff;">

            <!-- Property banner (shown only when linked) -->
            <div id="modalPropertyBanner" style="display:none; align-items:center; gap:16px; padding:16px 24px; background:#fbfcfe; border-bottom:1px solid var(--border);">
                <img id="modalPropertyImg" src="" alt="" style="width:64px; height:52px; object-fit:cover; border-radius:6px; box-shadow:0 2px 4px rgba(0,0,0,0.06); flex-shrink:0;">
                <div>
                    <div style="font-size:0.7rem; letter-spacing:0.06em; text-transform:uppercase; color:var(--gold-600); font-weight:700; margin-bottom:4px;">Interested Property</div>
                    <a id="modalPropertyLink" href="#" target="_blank" style="font-weight:700; font-size:1rem; color:var(--navy-900); text-decoration:none;"></a>
                </div>
            </div>

            <!-- Sender info -->
            <div class="msg-meta" style="padding:20px 24px; background:#fff;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                    <div>
                        <div style="font-size:0.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:var(--ink-soft); margin-bottom:4px;">From</div>
                        <strong id="modalName" style="font-size:0.95rem; color:var(--navy-900);"></strong>
                        <div style="margin-top:4px;"><a id="modalEmailLink" href="#" style="font-size:0.9rem; color:var(--primary); text-decoration:none;"></a></div>
                    </div>
                    <div>
                        <div style="font-size:0.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:var(--ink-soft); margin-bottom:4px;">Received</div>
                        <div id="modalDate" style="font-size:0.95rem; color:var(--ink);"></div>
                        
                        <div id="modalPhoneRow" style="margin-top:10px;">
                            <div style="display:flex; gap:12px; align-items:center;">
                                <a id="modalCallLink" href="#" style="display:inline-flex; align-items:center; gap:4px; font-size:0.85rem; font-weight:600; color:var(--navy-700); text-decoration:none; padding:4px 8px; background:#f1f5f9; border-radius:4px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    Call
                                </a>
                                <a id="modalWaLink" href="#" target="_blank" style="display:inline-flex; align-items:center; gap:4px; font-size:0.85rem; font-weight:600; color:#16a34a; text-decoration:none; padding:4px 8px; background:#dcfce7; border-radius:4px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                                    WhatsApp
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message body -->
            <div style="padding:24px; background:#f8f9fb; border-top:1px solid var(--border); border-bottom:1px solid var(--border); min-height:120px;">
                <p id="modalMsg" style="white-space:pre-wrap; font-size:0.95rem; line-height:1.7; color:var(--ink); margin:0;"></p>
            </div>

            <!-- Status changer -->
            <div style="padding:16px 24px; display:flex; align-items:center; gap:12px; flex-wrap:wrap; background:#fff;">
                <span style="font-size:0.85rem; color:var(--ink-soft); font-weight:600;">Set Status:</span>
                <?php foreach (['Unread','Read','Replied','Closed'] as $sv): ?>
                <a id="statusBtn_<?php echo $sv; ?>" href="#" class="status-btn btn btn-ghost btn-sm" style="border-radius:20px; font-size:0.8rem;"
                   data-status="<?php echo $sv; ?>"><?php echo $sv; ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="modal-footer" style="padding:16px 24px; background:#fbfcfe; border-top:1px solid var(--border); display:flex; gap:10px; justify-content:flex-end;">
            <a id="modalArchiveBtn" href="#" class="btn btn-ghost" style="display:flex; align-items:center; gap:6px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                Archive
            </a>
            <a id="modalReplyBtn" href="#" class="btn btn-primary" style="display:flex; align-items:center; gap:6px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                Reply via Email
            </a>
        </div>
    </div>
</div>

<script>
let currentMsgId = null;

function openMsgModal(btn) {
    currentMsgId = btn.dataset.id;
    document.getElementById('modalSubject').textContent   = btn.dataset.subject;
    document.getElementById('modalName').textContent      = btn.dataset.name;
    document.getElementById('modalDate').textContent      = btn.dataset.date;
    document.getElementById('modalMsg').textContent       = btn.dataset.msg;

    // Email
    const emailEl = document.getElementById('modalEmailLink');
    emailEl.textContent = btn.dataset.email;
    emailEl.href = 'mailto:' + btn.dataset.email;

    // Phone
    const phone = btn.dataset.phone;
    const phoneRow = document.getElementById('modalPhoneRow');
    if (phone && phone !== '—' && phone !== '') {
        const clean = phone.replace(/\s+/g, '');
        document.getElementById('modalCallLink').href = 'tel:' + clean;
        document.getElementById('modalWaLink').href   = 'https://wa.me/' + clean.replace('+', '');
        phoneRow.style.display = 'block';
    } else {
        phoneRow.style.display = 'none';
    }

    // Property banner
    const banner = document.getElementById('modalPropertyBanner');
    if (btn.dataset.property) {
        document.getElementById('modalPropertyImg').src  = btn.dataset.propimg;
        const titleEl = document.getElementById('modalPropertyLink');
        titleEl.textContent = btn.dataset.property;
        titleEl.href = '../property-details.php?id=' + btn.dataset.propid;
        banner.style.display = 'flex';
    } else {
        banner.style.display = 'none';
    }

    // Reply pre-fill
    document.getElementById('modalReplyBtn').href =
        'mailto:' + btn.dataset.email + '?subject=Re: ' + encodeURIComponent(btn.dataset.subject);

    // Archive link
    document.getElementById('modalArchiveBtn').href =
        'messages.php?toggle_archive=1&id=' + currentMsgId;

    // Status buttons — highlight current
    document.querySelectorAll('.status-btn').forEach(b => {
        const active = b.dataset.status === btn.dataset.status;
        b.style.background    = active ? 'var(--navy-800)' : '';
        b.style.color         = active ? '#fff' : '';
        b.style.borderColor   = active ? 'var(--navy-800)' : '';
        b.onclick = (e) => {
            e.preventDefault();
            window.location.href = 'messages.php?set_status=' + b.dataset.status + '&id=' + currentMsgId;
        };
    });

    document.getElementById('msgModal').classList.add('show');
}

function closeMsgModal() {
    document.getElementById('msgModal').classList.remove('show');
}

window.addEventListener('click', function(e) {
    if (e.target === document.getElementById('msgModal')) closeMsgModal();
});
</script>

<?php include 'includes/admin-footer.php'; ?>
