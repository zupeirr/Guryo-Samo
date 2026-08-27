<?php
require_once 'config/db.php';

$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

if ($type === 'property') {
    $stmt = $conn->prepare("SELECT image_data, image_type FROM properties WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($data, $mime);
    if ($stmt->fetch() && !empty($data)) {
        header("Content-Type: " . ($mime ? $mime : 'image/jpeg'));
        echo $data;
        exit;
    }
} elseif ($type === 'media') {
    $stmt = $conn->prepare("SELECT media_data, media_mime FROM property_media WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($data, $mime);
    if ($stmt->fetch() && !empty($data)) {
        header("Content-Type: " . ($mime ? $mime : 'image/jpeg'));
        echo $data;
        exit;
    }
}

// Fallback to no-image
header("Location: assets/images/no-image.jpg");
exit;
