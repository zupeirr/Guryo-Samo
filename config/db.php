<?php
/**
 * config/db.php
 * ---------------------------------------------------------------
 * Opens ONE shared MySQL connection ($conn) that every page in the
 * project reuses. It is loaded with `require_once` so PHP only
 * connects once per request, no matter how many files include it.
 *
 * Update the 4 constants below to match your own XAMPP / MySQL setup.
 * A default XAMPP install normally uses: root / (empty password).
 */

define('DB_HOST', 'localhost');       // MySQL server address
define('DB_USER', 'root');            // MySQL username
define('DB_PASS', '');                // MySQL password
define('DB_NAME', 'real_estate_db');  // Database created from database/real_estate.sql

// Suppress default mysqli errors so we can handle them gracefully
mysqli_report(MYSQLI_REPORT_OFF);

// Create the connection using MySQLi (Object Oriented style)
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Handle connection failure safely — log privately, never expose to visitor
if ($conn->connect_error) {
    error_log('[Guryo Samo DB] Connection failed: ' . $conn->connect_error);
    http_response_code(503);
    // Show a user-friendly page instead of a raw MySQL error
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">
        <title>Service Unavailable</title>
        <style>body{font-family:Inter,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#101d2c;color:#fff;}
        .box{text-align:center;padding:40px;}.box h1{font-size:2rem;margin-bottom:12px;color:#c89b3c;}
        .box p{color:rgba(255,255,255,0.7);max-width:400px;line-height:1.6;}</style></head>
        <body><div class="box">
            <h1>We\'ll be right back</h1>
            <p>We\'re experiencing a temporary issue. Please try again in a few moments.</p>
        </div></body></html>';
    exit;
}

// utf8mb4 lets the database store any language/character, including emoji
$conn->set_charset('utf8mb4');
