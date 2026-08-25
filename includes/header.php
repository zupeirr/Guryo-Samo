<?php
/**
 * Shared header for all public-facing pages.
 * Expects $pageTitle and optionally $activePage to be set before including.
 */
if (!isset($pageTitle)) { $pageTitle = 'Home'; }
if (!isset($activePage)) { $activePage = ''; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php $siteName = getSetting($conn, 'site_name', 'Guryo Samo'); ?>
<title><?php echo clean($pageTitle); ?> | <?php echo clean($siteName); ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo $baseUrl ?? ''; ?>assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>

<header class="navbar">
    <div class="container nav-inner">
        <a href="<?php echo $baseUrl ?? ''; ?>index.php" class="brand">
            <span class="mark">GS</span> <?php echo clean($siteName); ?>
        </a>
        <div class="nav-menu" id="navMenu">
            <nav class="nav-links">
                <a href="<?php echo $baseUrl ?? ''; ?>index.php" class="<?php echo $activePage === 'home' ? 'active' : ''; ?>">Home</a>
                <a href="<?php echo $baseUrl ?? ''; ?>properties.php" class="<?php echo $activePage === 'properties' ? 'active' : ''; ?>">Properties</a>
                <a href="<?php echo $baseUrl ?? ''; ?>services.php" class="<?php echo $activePage === 'services' ? 'active' : ''; ?>">Services</a>
                <a href="<?php echo $baseUrl ?? ''; ?>about.php" class="<?php echo $activePage === 'about' ? 'active' : ''; ?>">About Us</a>
                <a href="<?php echo $baseUrl ?? ''; ?>contact.php" class="<?php echo $activePage === 'contact' ? 'active' : ''; ?>">Contact</a>
            </nav>
            <div class="nav-cta">
                <?php if (isLoggedIn()): ?>
                    <span style="color:rgba(255,255,255,0.8); font-size:0.88rem;">
                        Hi, <?php echo clean($_SESSION['user_name']); ?>
                    </span>
                    <?php if (isAdminLoggedIn()): ?>
                        <a href="<?php echo $baseUrl ?? ''; ?>admin/dashboard.php" class="btn btn-primary btn-sm">Dashboard</a>
                    <?php endif; ?>
                    <a href="<?php echo $baseUrl ?? ''; ?>logout.php" class="btn btn-outline btn-sm">Log Out</a>
                <?php else: ?>
                    <a href="<?php echo $baseUrl ?? ''; ?>login.php" class="btn btn-outline btn-sm">Login</a>
                    <a href="<?php echo $baseUrl ?? ''; ?>register.php" class="btn btn-primary btn-sm">Register</a>
                <?php endif; ?>
            </div>
        </div>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>
