<?php
/**
 * =========================================================================
 * config/db.php  —  Database Connection File
 * =========================================================================
 *
 * PURPOSE:
 *   This file opens ONE shared MySQL database connection ($conn) that every
 *   other page in the project re-uses. It is loaded with `require_once` so
 *   PHP only connects to the database ONCE per request, no matter how many
 *   files include it.
 *
 * HOW TO USE IT IN OTHER FILES:
 *   At the top of any PHP file that needs the database, add:
 *       require_once 'config/db.php';     (from the root folder)
 *       require_once '../config/db.php';  (from sub-folders like /admin)
 *   After that, the variable $conn is available everywhere on the page.
 *
 * HOW TO CONFIGURE:
 *   Update the 4 constants below to match your own XAMPP / MySQL setup.
 *   A default XAMPP install normally uses: root / (empty password).
 * =========================================================================
 */

// ── Database credentials ──────────────────────────────────────────────────
// Change these values if your MySQL server uses different settings.

define('DB_HOST', 'localhost');       // The server where MySQL is running (usually 'localhost')
define('DB_USER', 'root');            // MySQL username (default XAMPP user is 'root')
define('DB_PASS', '');                // MySQL password (default XAMPP has no password — empty string)
define('DB_NAME', 'real_estate_db');  // The name of the database (created from database/real_estate.sql)
define('DB_PORT', 3307);              // MySQL port — XAMPP uses 3307 to avoid conflict with Windows MySQL80 service

// ── Turn off automatic error throwing ────────────────────────────────────
// By default, newer PHP versions throw exceptions on MySQL errors.
// We switch that off so we can handle errors ourselves more gracefully.
mysqli_report(MYSQLI_REPORT_OFF);

// ── Open the database connection ──────────────────────────────────────────
// We use MySQLi (MySQL Improved) in Object-Oriented style.
// $conn is the connection object — all SQL queries in the project use it.
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// ── Handle connection failure ─────────────────────────────────────────────
// If the connection fails (e.g. wrong password, MySQL not running),
// we log the real error privately and show a friendly message to the visitor.
// We NEVER show raw MySQL errors to users — that would be a security risk.
if ($conn->connect_error) {
    // Write the real error to the server's error log (only admins can see this)
    error_log('[Guryo Samo DB] Connection failed: ' . $conn->connect_error);

    // Send HTTP 503 "Service Unavailable" status code to the browser
    http_response_code(503);

    // Show a user-friendly "We'll be right back" page instead of a scary error
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">
        <title>Service Unavailable</title>
        <style>body{font-family:Inter,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#101d2c;color:#fff;}
        .box{text-align:center;padding:40px;}.box h1{font-size:2rem;margin-bottom:12px;color:#c89b3c;}
        .box p{color:rgba(255,255,255,0.7);max-width:400px;line-height:1.6;}</style></head>
        <body><div class="box">
            <h1>We\'ll be right back</h1>
            <p>We\'re experiencing a temporary issue. Please try again in a few moments.</p>
        </div></body></html>';
    exit; // Stop all further PHP execution
}

// ── Set the character encoding ────────────────────────────────────────────
// utf8mb4 allows the database to store any language, special characters,
// and even emoji. This is the safest encoding for international content.
$conn->set_charset('utf8mb4');
