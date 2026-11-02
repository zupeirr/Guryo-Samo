<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

// Only allow logged in admins
if (!isAdminLoggedIn()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

// Count unread messages
$stmt = $conn->prepare("SELECT COUNT(*) AS unread FROM messages WHERE is_read = 0");
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo json_encode(['unread' => (int)$result['unread']]);
