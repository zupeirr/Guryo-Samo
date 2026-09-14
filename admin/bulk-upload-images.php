<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle = 'Bulk Upload Images';
$activeAdminPage = 'bulk-upload-images';

$successMsg = '';
$errorMsg = '';

// Handle Image Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['property_id'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errorMsg = 'Session expired. Please try again.';
    } else {
        $propertyId = (int)$_POST['property_id'];
        
        // Use the existing uploadPropertyImage function
        $imageResult = uploadPropertyImage('coverImage', '../uploads');
        
        if (is_array($imageResult) && isset($imageResult['error'])) {
            $errorMsg = $imageResult['error'];
        } else {
            $imgName = is_array($imageResult) ? $imageResult['name'] : $imageResult;
            $imgData = is_array($imageResult) ? $imageResult['data'] : null;
            $imgMime = is_array($imageResult) ? $imageResult['mime'] : null;

            if ($imgData) {
                $stmt = $conn->prepare("UPDATE properties SET image=?, image_data=?, image_type=? WHERE id=?");
                $stmt->bind_param('sssi', $imgName, $imgData, $imgMime, $propertyId);
                
                if ($stmt->execute()) {
                    $successMsg = 'Image uploaded successfully for Property ID: ' . $propertyId;
                } else {
                    $errorMsg = 'Database error: failed to update image.';
                }
            } else {
                $errorMsg = 'No valid image data was uploaded.';
            }
        }
    }
}

// Fetch properties that have 'no-image.jpg' or no image_data
$query = "SELECT id, property_ref, title, type, price, location FROM properties WHERE image = 'no-image.jpg' OR image_data IS NULL ORDER BY created_at DESC";
$result = $conn->query($query);
$properties = [];
if ($result) {
    while($row = $result->fetch_assoc()) {
        $properties[] = $row;
    }
}

include 'includes/admin-header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Properties Missing Images</h2>
    </div>
    <div class="card-body">
        <?php if($successMsg): ?>
            <div class="alert success"><?php echo clean($successMsg); ?></div>
        <?php endif; ?>
        <?php if($errorMsg): ?>
            <div class="alert error"><?php echo clean($errorMsg); ?></div>
        <?php endif; ?>

        <p style="margin-bottom:20px; color:#666;">
            The following properties are currently using the default placeholder image. Use the forms below to quickly upload cover images for them. Images are stored directly in the database (BLOB). Max size: 5MB per image.
        </p>

        <?php if (empty($properties)): ?>
            <p>All properties have images assigned!</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Ref</th>
                        <th>Title</th>
                        <th>Location</th>
                        <th>Upload Cover Image</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($properties as $p): ?>
                        <tr>
                            <td><?php echo clean($p['property_ref']); ?></td>
                            <td><?php echo clean($p['title']); ?></td>
                            <td><?php echo clean($p['location']); ?></td>
                            <td>
                                <form method="POST" enctype="multipart/form-data" style="display:flex; gap:10px; align-items:center; margin:0;">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrf(); ?>">
                                    <input type="hidden" name="property_id" value="<?php echo $p['id']; ?>">
                                    <input type="file" name="coverImage" accept="image/jpeg,image/png,image/webp,image/gif" required style="max-width:200px;">
                                    <button type="submit" class="btn btn-sm btn-primary">Upload</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
