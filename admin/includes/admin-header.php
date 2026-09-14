<?php
/**
 * =========================================================================
 * admin/includes/admin-header.php  —  Admin Panel Page Header
 * =========================================================================
 *
 * PURPOSE:
 *   Included at the TOP of every admin page. Outputs the full HTML
 *   document head (<html>, <head>), the sidebar, and the top bar.
 *
 * VARIABLES TO SET BEFORE INCLUDING:
 *   $pageTitle       (string) — Used in <title> and the top bar heading.
 *   $activeAdminPage (string) — Tells the sidebar which section to highlight.
 *
 * EXAMPLE (top of admin/properties.php):
 *   $pageTitle       = 'Properties';
 *   $activeAdminPage = 'properties';
 *   include 'includes/admin-header.php';
 * =========================================================================
 */

// Default page title if the calling page didn't set one
if (!isset($pageTitle)) { $pageTitle = 'Dashboard'; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo clean($pageTitle); ?> | Admin Panel</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo filemtime('../assets/css/style.css'); ?>">
<link rel="stylesheet" href="../assets/css/admin.css?v=<?php echo filemtime('../assets/css/admin.css'); ?>">
</head>
<body class="admin-body">

<?php include 'includes/sidebar.php'; ?>

<div class="admin-main">
    <div class="admin-topbar">
        <div style="display:flex; align-items:center; gap:14px;">
            <button class="mobile-topbar-toggle" id="sidebarToggle" aria-label="Toggle menu" style="background:transparent; border:none; cursor:pointer; color:var(--gold-500); padding:0; display:flex; align-items:center; justify-content:center; transition: opacity 0.2s;" onmouseover="this.style.opacity=0.8" onmouseout="this.style.opacity=1"><i data-lucide="menu" style="width:24px;height:24px;"></i></button>
            <h1><?php echo clean($pageTitle); ?></h1>
        </div>
        <div class="who">
            <span>Welcome, <strong><?php echo clean($_SESSION['user_name'] ?? 'Admin'); ?></strong></span>
        </div>
    </div>
    <div class="admin-content">
