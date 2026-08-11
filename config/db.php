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

// Create the connection using MySQLi (Object Oriented style)
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Stop the whole page with a clear error if the connection fails
// (e.g. MySQL isn't running, or the database hasn't been imported yet)
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// utf8mb4 lets the database store any language/character, including emoji
$conn->set_charset('utf8mb4');
