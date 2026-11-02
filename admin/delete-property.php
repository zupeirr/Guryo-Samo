<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? trim($_GET['action']) : 'delete';

if ($id > 0) {
    if ($action === 'restore') {
        $stmt = $conn->prepare("UPDATE properties SET deleted_at = NULL, deleted_by = NULL WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        redirect('archived-properties.php?restored=1');
    } elseif ($action === 'purge') {
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
        redirect('archived-properties.php?purged=1');
    } else {
        $deletedBy = $_SESSION['user_id'] ?? null;
        $stmt = $conn->prepare("UPDATE properties SET deleted_at = NOW(), deleted_by = ? WHERE id = ?");
        $stmt->bind_param('ii', $deletedBy, $id);
        $stmt->execute();
        redirect('properties.php?deleted=1');
    }
}
redirect('properties.php');
