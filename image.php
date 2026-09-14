<?php
/**
 * =========================================================================
 * image.php  —  Database Image / Media Server
 * =========================================================================
 *
 * PURPOSE:
 *   This file serves images and media files that are stored directly in
 *   the MySQL database as binary BLOB data (not as files on disk).
 *
 * HOW IMAGES ARE STORED:
 *   When an admin uploads a property image, the binary data is stored in
 *   the `properties.image_data` or `property_media.media_data` column.
 *   This avoids needing to manage an uploads folder.
 *
 * HOW TO USE IT IN HTML:
 *   To display a property's main image:
 *       <img src="image.php?type=property&id=5">
 *   To display a media gallery image:
 *       <img src="image.php?type=media&id=12">
 *
 * SUPPORTED TYPES:
 *   - 'property'  → reads from the properties table (main cover image)
 *   - 'media'     → reads from the property_media table (gallery images/videos)
 *
 * FALLBACK:
 *   If the image is not found or the data is empty, the browser is
 *   redirected to the static placeholder image: assets/images/no-image.jpg
 * =========================================================================
 */

// Load database connection so we can query for image data
require_once 'config/db.php';

// Read query parameters from the URL: ?type=property&id=5
$type = isset($_GET['type']) ? $_GET['type'] : '';  // 'property' or 'media'
$id   = isset($_GET['id'])   ? (int)$_GET['id'] : 0; // Cast to int for SQL safety (prevents SQL injection)

// If no valid ID was provided, return a 404 Not Found response
if ($id <= 0) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

// ── Serve a property cover image ─────────────────────────────────────────
// Looks up the image binary data stored in the properties table
if ($type === 'property') {
    // Use a prepared statement to safely pass the $id into the SQL query
    $stmt = $conn->prepare("SELECT image_data, image_type FROM properties WHERE id = ?");
    $stmt->bind_param("i", $id); // "i" = integer type
    $stmt->execute();
    $stmt->bind_result($data, $mime); // $data = raw binary image, $mime = e.g. 'image/jpeg'

    // If we found a row AND it has actual image data, serve it as an image
    if ($stmt->fetch() && !empty($data)) {
        // Set the Content-Type header so the browser knows this is an image
        header("Content-Type: " . ($mime ? $mime : 'image/jpeg'));
        echo $data; // Output the raw binary image data directly
        exit;
    }

// ── Serve a gallery media item ────────────────────────────────────────────
// Looks up image/video data from the property_media table (gallery uploads)
} elseif ($type === 'media') {
    $stmt = $conn->prepare("SELECT media_data, media_mime FROM property_media WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($data, $mime);

    if ($stmt->fetch() && !empty($data)) {
        header("Content-Type: " . ($mime ? $mime : 'image/jpeg'));
        echo $data; // Output the raw binary media data (image or video)
        exit;
    }
}

// ── Fallback ──────────────────────────────────────────────────────────────
// If we reach here, either the type was invalid or no data was found.
// Redirect the browser to show the placeholder "no image" image instead.
header("Location: assets/images/no-image.jpg");
exit;
