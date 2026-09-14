<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Fetch image name first so we can remove the file from disk
    $stmt = $conn->prepare("SELECT image FROM properties WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $property = $stmt->get_result()->fetch_assoc();

    if ($property) {
        $delStmt = $conn->prepare("DELETE FROM properties WHERE id = ?");
        $delStmt->bind_param('i', $id);
        $delStmt->execute();

        if ($property['image'] && $property['image'] !== 'no-image.jpg' && file_exists('../uploads/' . $property['image'])) {
            @unlink('../uploads/' . $property['image']);
        }
    }
}

redirect('properties.php?deleted=1');
