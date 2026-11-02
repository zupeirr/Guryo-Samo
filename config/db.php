<?php
/**
 * =========================================================================
 * config/db.php  —  Database Connection File
 * =========================================================================
 *
 * PURPOSE:
 *   Opens ONE shared MySQL database connection ($conn) reused across every
 *   page. Credentials are loaded from the project-root .env file so they
 *   are never hardcoded or committed to version control.
 *
 * HOW TO USE IT IN OTHER FILES:
 *   require_once 'config/db.php';     (from the root folder)
 *   require_once '../config/db.php';  (from sub-folders like /admin)
 *
 * HOW TO CONFIGURE:
 *   Edit the .env file in the project root. Never edit this file directly.
 * =========================================================================
 */

// ── Load credentials from .env ────────────────────────────────────────────
// parse_ini_file() reads "KEY=value" lines into an associative array.
// We look for .env two levels up from /config/ so it always resolves to
// the project root, no matter where in the project this file is included.
$envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';

if (file_exists($envFile)) {
    $env = parse_ini_file($envFile);
} else {
    // Fallback defaults (only for first-time local setup without a .env file).
    // On a production server this file MUST exist.
    $env = [
        'DB_HOST' => 'localhost',
        'DB_PORT' => 3306,
        'DB_USER' => 'root',
        'DB_PASS' => '',
        'DB_NAME' => 'real_estate_db',
        'APP_ENV' => 'development',
    ];
}

define('DB_HOST', $env['DB_HOST'] ?? 'localhost');
define('DB_PORT', (int)($env['DB_PORT'] ?? 3306));
define('DB_USER', $env['DB_USER'] ?? 'root');
define('DB_PASS', $env['DB_PASS'] ?? '');
define('DB_NAME', $env['DB_NAME'] ?? 'real_estate_db');
define('APP_ENV', $env['APP_ENV'] ?? 'development');

// ── Configure error reporting based on environment ───────────────────────
// In development: show errors on screen so you can debug quickly.
// In production:  hide errors from visitors (log them to file instead).
if (APP_ENV === 'production') {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    // Ensure the logs directory exists before writing to it
    $logDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs';
    if (!is_dir($logDir)) { mkdir($logDir, 0755, true); }
    ini_set('error_log', $logDir . DIRECTORY_SEPARATOR . 'php_errors.log');
} else {
    // Development — show all errors
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
}

// ── Turn off automatic MySQLi exception throwing ──────────────────────────
mysqli_report(MYSQLI_REPORT_OFF);

// ── Open the database connection ──────────────────────────────────────────
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// ── Handle connection failure ─────────────────────────────────────────────
if ($conn->connect_error) {
    error_log('[Guryo Samo DB] Connection failed: ' . $conn->connect_error);
    http_response_code(503);
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

// ── Set the character encoding ────────────────────────────────────────────
$conn->set_charset('utf8mb4');
