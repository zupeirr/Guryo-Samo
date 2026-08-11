<?php
mysqli_report(MYSQLI_REPORT_OFF);
$mysqli = new mysqli("localhost", "root", "");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error . "\n");
}
echo "Connected successfully to MySQL.\n";

// Create DB if not exists
$mysqli->query("CREATE DATABASE IF NOT EXISTS `real_estate_db`");
if (!$mysqli->select_db("real_estate_db")) {
    die("Could not select database: " . $mysqli->error . "\n");
}

// Check if tables already exist
$result = $mysqli->query("SHOW TABLES LIKE 'users'");
if ($result && $result->num_rows > 0) {
    echo "Database already exists and is set up.\n";
    $mysqli->close();
    exit(0);
}

echo "Importing database schema...\n";
$sql = file_get_contents(__DIR__ . "/database/real_estate.sql");
if ($sql === false) {
    die("Could not read SQL file.\n");
}

if ($mysqli->multi_query($sql)) {
    do {
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());
    echo "Database imported successfully.\n";
} else {
    echo "Error importing database: " . $mysqli->error . "\n";
}
$mysqli->close();
