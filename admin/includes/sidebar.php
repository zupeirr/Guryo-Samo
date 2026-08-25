<?php if (!isset($activeAdminPage)) { $activeAdminPage = ''; } ?>

<?php
// ── Reusable SVG icon helper ─────────────────────────────────────────────────
function navIcon(string $name): string {
    $icons = [

        'dashboard' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',

        'construction' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2 20h20"/><path d="M6 20V10l6-7 6 7v10"/><path d="M9 20v-5h6v5"/><path d="M12 3v4"/></svg>',

        'properties' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',

        'customers' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',

        'agents' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="M12 11v2"/><path d="M10 13h4"/></svg>',

        'communications' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',

        'appointments' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/></svg>',

        'finance' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',

        'reports' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',

        'users' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><path d="M21 20c0-3-1.8-5.4-4-6.3"/></svg>',

        'settings' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',

        'globe' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',

        'logout' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',

        'chevron' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>',
    ];
    return $icons[$name] ?? '';
}
?>

<aside class="admin-sidebar" id="adminSidebar">
    <a href="dashboard.php" class="brand">
        <span class="mark">GS</span> Guryo Samo
    </a>
    <nav class="admin-nav">

        <!-- Dashboard -->
        <a href="dashboard.php" class="<?php echo $activeAdminPage === 'dashboard' ? 'active' : ''; ?>">
            <span class="ic"><?php echo navIcon('dashboard'); ?></span> Dashboard
        </a>

        <!-- Construction -->
        <div class="nav-group <?php echo in_array($activeAdminPage, ['construction']) || in_array(basename($_SERVER['PHP_SELF']), ['construction.php','construction-projects.php','construction-tasks.php','construction-materials.php','contractors.php','construction-documents.php']) ? 'open active-group' : ''; ?>">
            <button class="nav-group-toggle">
                <span class="ic"><?php echo navIcon('construction'); ?></span>
                <span>Construction</span>
                <span class="chevron"><?php echo navIcon('chevron'); ?></span>
            </button>
            <div class="nav-group-children">
                <a href="construction.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'construction.php' ? 'active' : ''; ?>">Hub / Overview</a>
                <a href="construction-projects.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'construction-projects.php' ? 'active' : ''; ?>">Projects</a>
                <a href="construction-tasks.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'construction-tasks.php' ? 'active' : ''; ?>">Tasks / Work Orders</a>
                <a href="construction-materials.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'construction-materials.php' ? 'active' : ''; ?>">Materials &amp; Inventory</a>
                <a href="contractors.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'contractors.php' ? 'active' : ''; ?>">Contractors</a>
                <a href="construction-documents.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'construction-documents.php' ? 'active' : ''; ?>">Documents &amp; Permits</a>
            </div>
        </div>

        <!-- Properties -->
        <div class="nav-group <?php echo in_array($activeAdminPage, ['properties','add','pending','available','sold','rented']) ? 'open active-group' : ''; ?>">
            <button class="nav-group-toggle">
                <span class="ic"><?php echo navIcon('properties'); ?></span>
                <span>Properties</span>
                <span class="chevron"><?php echo navIcon('chevron'); ?></span>
            </button>
            <div class="nav-group-children">
                <a href="properties.php" class="<?php echo $activeAdminPage === 'properties' ? 'active' : ''; ?>">All Properties</a>
                <a href="add-property.php" class="<?php echo $activeAdminPage === 'add' ? 'active' : ''; ?>">Add Property</a>
                <a href="properties.php?status=Pending" class="<?php echo $activeAdminPage === 'pending' ? 'active' : ''; ?>">Pending Approval</a>
                <a href="properties.php?status=For+Sale" class="<?php echo $activeAdminPage === 'available' ? 'active' : ''; ?>">Available</a>
                <a href="properties.php?status=Sold" class="<?php echo $activeAdminPage === 'sold' ? 'active' : ''; ?>">Sold</a>
                <a href="properties.php?status=For+Rent" class="<?php echo $activeAdminPage === 'rented' ? 'active' : ''; ?>">Rented</a>
            </div>
        </div>

        <!-- Customers -->
        <div class="nav-group <?php echo in_array($activeAdminPage, ['customers','messages']) ? 'open active-group' : ''; ?>">
            <button class="nav-group-toggle">
                <span class="ic"><?php echo navIcon('customers'); ?></span>
                <span>Customers</span>
                <span class="chevron"><?php echo navIcon('chevron'); ?></span>
            </button>
            <div class="nav-group-children">
                <a href="customers.php" class="<?php echo $activeAdminPage === 'customers' ? 'active' : ''; ?>">Customer Directory</a>
                <a href="messages.php" class="<?php echo $activeAdminPage === 'messages' ? 'active' : ''; ?>">All Messages</a>
            </div>
        </div>

        <!-- Agents -->
        <div class="nav-group <?php echo in_array($activeAdminPage, ['agents','add-agent','agent-performance']) ? 'open active-group' : ''; ?>">
            <button class="nav-group-toggle">
                <span class="ic"><?php echo navIcon('agents'); ?></span>
                <span>Agents</span>
                <span class="chevron"><?php echo navIcon('chevron'); ?></span>
            </button>
            <div class="nav-group-children">
                <a href="agents.php" class="<?php echo $activeAdminPage === 'agents' ? 'active' : ''; ?>">All Agents</a>
                <a href="add-agent.php" class="<?php echo $activeAdminPage === 'add-agent' ? 'active' : ''; ?>">Add Agent</a>
                <a href="agent-performance.php" class="<?php echo $activeAdminPage === 'agent-performance' ? 'active' : ''; ?>">Agent Performance</a>
            </div>
        </div>

        <!-- Appointments -->
        <div class="nav-group <?php echo in_array($activeAdminPage, ['appointments','viewings','site-visits','calendar']) ? 'open active-group' : ''; ?>">
            <button class="nav-group-toggle">
                <span class="ic"><?php echo navIcon('appointments'); ?></span>
                <span>Appointments</span>
                <span class="chevron"><?php echo navIcon('chevron'); ?></span>
            </button>
            <div class="nav-group-children">
                <a href="appointments.php" class="<?php echo $activeAdminPage === 'appointments' ? 'active' : ''; ?>">All Appointments</a>
                <a href="appointments.php?type=Viewing" class="<?php echo $activeAdminPage === 'viewings' ? 'active' : ''; ?>">Property Viewings</a>
                <a href="appointments.php?type=Site+Visit" class="<?php echo $activeAdminPage === 'site-visits' ? 'active' : ''; ?>">Site Visits</a>
                <a href="calendar.php" class="<?php echo $activeAdminPage === 'calendar' ? 'active' : ''; ?>">Calendar</a>
            </div>
        </div>

        <!-- Finance -->
        <div class="nav-group <?php echo in_array($activeAdminPage, ['transactions','add-transaction','commissions','finance','invoices','payments','expenses','budgets']) ? 'open active-group' : ''; ?>">
            <button class="nav-group-toggle">
                <span class="ic"><?php echo navIcon('finance'); ?></span>
                <span>Finance</span>
                <span class="chevron"><?php echo navIcon('chevron'); ?></span>
            </button>
            <div class="nav-group-children">
                <a href="invoices.php" class="<?php echo $activeAdminPage === 'invoices' ? 'active' : ''; ?>">Invoices</a>
                <a href="payments.php" class="<?php echo $activeAdminPage === 'payments' ? 'active' : ''; ?>">Payments</a>
                <a href="expenses.php" class="<?php echo $activeAdminPage === 'expenses' ? 'active' : ''; ?>">Expenses</a>
                <a href="budgets.php" class="<?php echo $activeAdminPage === 'budgets' ? 'active' : ''; ?>">Budgets</a>
                <a href="commissions.php" class="<?php echo $activeAdminPage === 'commissions' ? 'active' : ''; ?>">Commissions</a>
                <a href="transactions.php" class="<?php echo $activeAdminPage === 'transactions' ? 'active' : ''; ?>">Sales / Rents</a>
            </div>
        </div>

        <!-- Reports -->
        <div class="nav-group <?php echo in_array($activeAdminPage, ['reports']) ? 'open active-group' : ''; ?>">
            <button class="nav-group-toggle">
                <span class="ic"><?php echo navIcon('reports'); ?></span>
                <span>Reports &amp; Analytics</span>
                <span class="chevron"><?php echo navIcon('chevron'); ?></span>
            </button>
            <div class="nav-group-children">
                <a href="reports.php" class="<?php echo $activeAdminPage === 'reports' ? 'active' : ''; ?>">Property Reports</a>
                <a href="reports.php#sales">Sales Reports</a>
                <a href="reports.php#revenue">Revenue Reports</a>
                <a href="reports.php#agent-performance">Agent Performance</a>
                <a href="reports.php#financial">Financial Reports</a>
                <a href="reports.php#construction-operations">Construction Ops</a>
                <a href="reports.php#exports">CSV/PDF Exports</a>
            </div>
        </div>

        <div class="nav-divider"></div>

        <!-- User Management -->
        <div class="nav-group <?php echo in_array($activeAdminPage, ['users','roles','activity-logs']) ? 'open active-group' : ''; ?>">
            <button class="nav-group-toggle">
                <span class="ic"><?php echo navIcon('users'); ?></span>
                <span>User Management</span>
                <span class="chevron"><?php echo navIcon('chevron'); ?></span>
            </button>
            <div class="nav-group-children">
                <a href="users.php" class="<?php echo $activeAdminPage === 'users' ? 'active' : ''; ?>">All Users</a>
                <a href="roles.php" class="<?php echo $activeAdminPage === 'roles' ? 'active' : ''; ?>">Roles &amp; Permissions</a>
                <a href="activity-logs.php" class="<?php echo $activeAdminPage === 'activity-logs' ? 'active' : ''; ?>">Activity Logs</a>
            </div>
        </div>

        <!-- Settings -->
        <div class="nav-group <?php echo in_array($activeAdminPage, ['settings']) ? 'open active-group' : ''; ?>">
            <button class="nav-group-toggle">
                <span class="ic"><?php echo navIcon('settings'); ?></span>
                <span>Settings</span>
                <span class="chevron"><?php echo navIcon('chevron'); ?></span>
            </button>
            <div class="nav-group-children">
                <a href="settings.php#general" class="<?php echo $activeAdminPage === 'settings' ? 'active' : ''; ?>">General Settings</a>
                <a href="settings.php#property">Property Settings</a>
                <a href="settings.php#notifications">Notifications</a>
                <a href="settings.php#system">System Settings</a>
            </div>
        </div>

        <div class="nav-divider"></div>

        <!-- View Site -->
        <a href="../index.php" target="_blank">
            <span class="ic"><?php echo navIcon('globe'); ?></span> View Site
        </a>

    </nav>
    <div class="admin-sidebar-footer">
        <a href="../logout.php" class="btn btn-outline btn-block" style="border-color:rgba(255,255,255,0.3); color:#fff; display:flex; align-items:center; gap:8px; justify-content:center;">
            <?php echo navIcon('logout'); ?> Log Out
        </a>
    </div>
</aside>
