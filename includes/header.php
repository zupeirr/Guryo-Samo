<?php
/**
 * =========================================================================
 * includes/header.php  —  Shared Public Page Header
 * =========================================================================
 *
 * PURPOSE:
 *   This file is included at the TOP of every public-facing page
 *   (index.php, properties.php, about.php, contact.php, etc.).
 *   It outputs the <html>, <head>, and the <header> navigation bar.
 *
 * VARIABLES TO SET BEFORE INCLUDING:
 *   $pageTitle  (string) – Used in the browser tab title.
 *                          e.g. $pageTitle = 'Properties';
 *   $activePage (string) – Highlights the current page in the navbar.
 *                          e.g. $activePage = 'properties';
 *   $baseUrl    (string) – Prefix for asset/link paths.
 *                          Set to '' (empty) for root-level pages.
 *                          Set to '../' for pages in sub-folders.
 *
 * EXAMPLE USAGE (at the top of about.php):
 *   $pageTitle  = 'About Us';
 *   $activePage = 'about';
 *   $baseUrl    = '';
 *   include 'includes/header.php';
 * =========================================================================
 */

// If the calling page forgot to set $pageTitle, default to 'Home'
if (!isset($pageTitle))       { $pageTitle       = 'Home'; }
// If the calling page forgot to set $activePage, default to empty string (no highlight)
if (!isset($activePage))      { $activePage      = ''; }
// Optional: pages can set $metaDescription before including this file
if (!isset($metaDescription)) { $metaDescription = ''; }
// Optional: pages can set $ogImage (full URL) for social sharing
if (!isset($ogImage))         { $ogImage         = ''; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<!-- Character encoding — must be first so the browser knows how to read the page -->
<meta charset="UTF-8">
<!-- Responsive viewport — makes the page look correct on phones and tablets -->
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
// Fetch the site name from the database settings table.
// Falls back to 'Guryo Samo' if the setting is not found.
$siteName = getSetting($conn, 'site_name', 'Guryo Samo');
?>
<!-- Browser tab title: "Page Name | Site Name" -->
<title><?php echo clean($pageTitle); ?> | <?php echo clean($siteName); ?></title>

<!-- SEO Meta Tags -->
<?php if ($metaDescription): ?>
<meta name="description" content="<?php echo clean($metaDescription); ?>">
<?php else: ?>
<meta name="description" content="<?php echo clean($siteName); ?> — Find verified properties for sale and rent in Somaliland.">
<?php endif; ?>
<meta name="robots" content="index, follow">

<!-- Open Graph (Facebook, WhatsApp, LinkedIn previews) -->
<meta property="og:type"        content="website">
<meta property="og:site_name"   content="<?php echo clean($siteName); ?>">
<meta property="og:title"       content="<?php echo clean($pageTitle); ?> | <?php echo clean($siteName); ?>">
<meta property="og:description" content="<?php echo $metaDescription ? clean($metaDescription) : 'Find verified properties for sale and rent in Somaliland.'; ?>">
<?php if ($ogImage): ?>
<meta property="og:image" content="<?php echo clean($ogImage); ?>">
<?php endif; ?>

<!-- Favicon (place a favicon.ico in /assets/images/) -->
<link rel="icon" type="image/x-icon" href="<?php echo $baseUrl ?? ''; ?>assets/images/favicon.ico">

<!-- Google Fonts preconnect — speeds up font loading by opening the connection early -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<!-- Load Inter (body text) and Poppins (headings) fonts from Google -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">

<!-- Main stylesheet — version string prevents stale cache on updates -->
<link rel="stylesheet" href="<?php echo $baseUrl ?? ''; ?>assets/css/style.css?v=2.0">
</head>
<body>

<!-- ── NAVIGATION BAR ─────────────────────────────────────────────────── -->
<header class="navbar">
    <div class="container nav-inner">

        <!-- Brand logo / site name — clicking takes you to the home page -->
        <a href="<?php echo $baseUrl ?? ''; ?>index.php" class="brand">
            <span class="mark">GS</span> <?php echo clean($siteName); ?>
        </a>

        <!-- Nav menu — hidden on mobile until the hamburger button is clicked -->
        <div class="nav-menu" id="navMenu">
            <nav class="nav-links">
                <!-- Each link gets the CSS class "active" if it matches the current page.
                     This is set by the $activePage variable at the top of each page. -->
                <a href="<?php echo $baseUrl ?? ''; ?>index.php"      class="<?php echo $activePage === 'home'       ? 'active' : ''; ?>">Home</a>
                <a href="<?php echo $baseUrl ?? ''; ?>properties.php" class="<?php echo $activePage === 'properties' ? 'active' : ''; ?>">Properties</a>
                <a href="<?php echo $baseUrl ?? ''; ?>services.php"   class="<?php echo $activePage === 'services'   ? 'active' : ''; ?>">Services</a>
                <a href="<?php echo $baseUrl ?? ''; ?>about.php"      class="<?php echo $activePage === 'about'      ? 'active' : ''; ?>">About Us</a>
                <a href="<?php echo $baseUrl ?? ''; ?>contact.php"    class="<?php echo $activePage === 'contact'    ? 'active' : ''; ?>">Contact</a>
            </nav>

            <!-- Call-to-action buttons on the right side of the navbar -->
            <div class="nav-cta">
                <?php if (isLoggedIn()): ?>
                    <!-- If a user is logged in, show their name and relevant buttons -->
                    <span style="color:rgba(255,255,255,0.8); font-size:0.88rem;">
                        Hi, <?php echo clean($_SESSION['user_name']); ?>
                    </span>
                    <?php if (isAdminLoggedIn()): ?>
                        <!-- Only admins/staff get the Dashboard button -->
                        <a href="<?php echo $baseUrl ?? ''; ?>admin/dashboard.php" class="btn btn-primary btn-sm">Dashboard</a>
                    <?php endif; ?>
                    <!-- All logged-in users can log out -->
                    <a href="<?php echo $baseUrl ?? ''; ?>logout.php" class="btn btn-outline btn-sm">Log Out</a>
                <?php else: ?>
                    <!-- If nobody is logged in, show a "Get Started" button -->
                    <a href="<?php echo $baseUrl ?? ''; ?>properties.php" class="btn btn-primary btn-sm" style="font-weight: 700; font-size: 0.9rem;">Get Started <span class="btn-arrow">&rarr;</span></a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Hamburger button — only visible on mobile screens.
             Clicking it toggles the nav menu open/closed via main.js -->
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
            <span></span> <!-- These three spans form the three lines of the hamburger icon -->
            <span></span>
            <span></span>
        </button>
    </div>
</header>

