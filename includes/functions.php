<?php
/**
 * Shared helper functions used across the site.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Clean user input to help prevent XSS when echoing values back to HTML */
function clean($value) {
    return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/** Format a number as a price string, e.g. 250000 -> $250,000 */
function formatPrice($price) {
    return '$' . number_format((float)$price, 0);
}

/**
 * ACCOUNT / SESSION HELPERS
 * ---------------------------------------------------------------
 * The "users" table stores THREE kinds of accounts (see role column):
 *   - admin    : full access to the Admin Dashboard
 *   - staff    : same as admin (kept as a separate role for future use)
 *   - customer : a normal visitor who registered on the public site
 *
 * When someone logs in successfully (login.php) we store 3 things
 * in the PHP session so the rest of the site knows who is logged in:
 *   $_SESSION['user_id']   -> the users.id value
 *   $_SESSION['user_name'] -> the users.full_name value (shown in the UI)
 *   $_SESSION['user_role'] -> 'admin' | 'staff' | 'customer'
 */

/** Returns true if ANY account (admin, staff or customer) is logged in */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/** Returns true only if the logged-in account is an admin/staff (can use the dashboard) */
function isAdminLoggedIn() {
    return isset($_SESSION['user_id'])
        && in_array($_SESSION['user_role'] ?? '', ['admin', 'staff'], true);
}

/** Redirect helper - sends the browser to a new URL and stops the script */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/** Used at the top of every page inside admin/ to block non-admin visitors */
function requireLogin() {
    if (!isAdminLoggedIn()) {
        redirect('../login.php');
    }
}

/** Generate / verify a simple CSRF token for forms */
function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

/**
 * Handle a property image upload.
 * Returns the new filename on success, or 'no-image.jpg' if no file was submitted.
 * Returns an array with an 'error' key on failure.
 */
function uploadPropertyImage($fileInputName, $uploadDir) {
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] === UPLOAD_ERR_NO_FILE) {
        return 'no-image.jpg';
    }

    $file = $_FILES[$fileInputName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Upload failed. Please try again.'];
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedTypes, true)) {
        return ['error' => 'Only JPG, PNG, GIF or WEBP images are allowed.'];
    }

    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        return ['error' => 'Image size must be smaller than 5MB.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $safeExt = preg_replace('/[^a-z0-9]/', '', $ext);
    $newName = 'property_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $safeExt;

    if (!move_uploaded_file($file['tmp_name'], rtrim($uploadDir, '/') . '/' . $newName)) {
        return ['error' => 'Could not save the uploaded image.'];
    }

    return $newName;
}
