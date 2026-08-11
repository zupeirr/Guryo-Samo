<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle = 'Edit Property';
$activeAdminPage = 'properties';

$allowedTypes = ['Apartment','Villa','House','Studio','Office','Land'];
$allowedStatus = ['For Sale','For Rent','Sold'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM properties WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();

if (!$property) {
    redirect('properties.php');
}

$errorMsg = '';
$formData = $property;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errorMsg = 'Your session has expired. Please try submitting the form again.';
    } else {
        $formData['title']       = trim($_POST['title'] ?? '');
        $formData['type']        = $_POST['type'] ?? '';
        $formData['price']       = $_POST['price'] ?? '';
        $formData['location']    = trim($_POST['location'] ?? '');
        $formData['description'] = trim($_POST['description'] ?? '');
        $formData['bedrooms']    = $_POST['bedrooms'] ?? 0;
        $formData['bathrooms']   = $_POST['bathrooms'] ?? 0;
        $formData['size']        = $_POST['size'] ?? 0;
        $formData['status']      = $_POST['status'] ?? '';

        if ($formData['title'] === '' || $formData['location'] === '' || $formData['price'] === '') {
            $errorMsg = 'Please fill in the title, location, and price.';
        } elseif (!in_array($formData['type'], $allowedTypes, true)) {
            $errorMsg = 'Please select a valid property type.';
        } elseif (!in_array($formData['status'], $allowedStatus, true)) {
            $errorMsg = 'Please select a valid property status.';
        } elseif (!is_numeric($formData['price']) || $formData['price'] < 0) {
            $errorMsg = 'Please enter a valid price.';
        } else {
            $imageName = $property['image'];

            // Only replace the image if a new one was uploaded
            if (isset($_FILES['propertyImage']) && $_FILES['propertyImage']['error'] !== UPLOAD_ERR_NO_FILE) {
                $imageResult = uploadPropertyImage('propertyImage', '../uploads');
                if (is_array($imageResult) && isset($imageResult['error'])) {
                    $errorMsg = $imageResult['error'];
                } else {
                    // remove old uploaded image if it isn't the placeholder
                    if ($property['image'] && $property['image'] !== 'no-image.jpg' && file_exists('../uploads/' . $property['image'])) {
                        @unlink('../uploads/' . $property['image']);
                    }
                    $imageName = $imageResult;
                }
            }

            if ($errorMsg === '') {
                $stmt = $conn->prepare(
                    "UPDATE properties SET title=?, type=?, price=?, location=?, description=?, bedrooms=?, bathrooms=?, size=?, status=?, image=? WHERE id=?"
                );
                $stmt->bind_param(
                    'ssdssiidssi',
                    $formData['title'], $formData['type'], $formData['price'], $formData['location'],
                    $formData['description'], $formData['bedrooms'], $formData['bathrooms'],
                    $formData['size'], $formData['status'], $imageName, $id
                );

                if ($stmt->execute()) {
                    redirect('properties.php?saved=1');
                } else {
                    $errorMsg = 'Something went wrong while updating the property. Please try again.';
                }
            }
        }
    }
}

$currentImg = ($property['image'] && $property['image'] !== 'no-image.jpg') ? '../uploads/' . $property['image'] : '../assets/images/no-image.jpg';

include 'includes/admin-header.php';
?>

<?php if ($errorMsg): ?><div class="alert alert-error"><?php echo clean($errorMsg); ?></div><?php endif; ?>

<div class="panel">
    <div class="panel-head"><h2>Edit Property: <?php echo clean($property['title']); ?></h2></div>
    <div class="panel-body">
        <form method="post" enctype="multipart/form-data" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            <input type="hidden" name="id" value="<?php echo (int)$id; ?>">

            <div class="form-grid-2">
                <div class="field-block">
                    <label for="title">Property Title</label>
                    <input type="text" id="title" name="title" value="<?php echo clean($formData['title']); ?>" required>
                </div>
                <div class="field-block">
                    <label for="type">Property Type</label>
                    <select id="type" name="type" required>
                        <?php foreach ($allowedTypes as $t): ?>
                            <option value="<?php echo $t; ?>" <?php echo $formData['type'] === $t ? 'selected' : ''; ?>><?php echo $t; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="field-block">
                    <label for="price">Price ($)</label>
                    <input type="number" step="0.01" id="price" name="price" value="<?php echo clean($formData['price']); ?>" required>
                </div>
                <div class="field-block">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" value="<?php echo clean($formData['location']); ?>" required>
                </div>
            </div>

            <div class="field-block">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5"><?php echo clean($formData['description']); ?></textarea>
            </div>

            <div class="form-grid-2">
                <div class="field-block">
                    <label for="bedrooms">Bedrooms</label>
                    <input type="number" id="bedrooms" name="bedrooms" value="<?php echo clean($formData['bedrooms']); ?>" min="0">
                </div>
                <div class="field-block">
                    <label for="bathrooms">Bathrooms</label>
                    <input type="number" id="bathrooms" name="bathrooms" value="<?php echo clean($formData['bathrooms']); ?>" min="0">
                </div>
            </div>

            <div class="form-grid-2">
                <div class="field-block">
                    <label for="size">Property Size (m²)</label>
                    <input type="number" step="0.01" id="size" name="size" value="<?php echo clean($formData['size']); ?>" min="0">
                </div>
                <div class="field-block">
                    <label for="status">Property Status</label>
                    <select id="status" name="status" required>
                        <?php foreach ($allowedStatus as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo $formData['status'] === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="field-block">
                <label>Current Image</label>
                <img src="<?php echo clean($currentImg); ?>" class="current-image" alt="Current property image" onerror="this.src='../assets/images/no-image.jpg'">
                <label for="propertyImage" style="margin-top:12px;">Replace Image (optional)</label>
                <input type="file" id="propertyImage" name="propertyImage" accept="image/jpeg,image/png,image/webp,image/gif">
                <img id="imagePreview" class="current-image" style="display:none; margin-top:12px;" alt="Preview">
            </div>

            <button type="submit" class="btn btn-primary">Update Property</button>
            <a href="properties.php" class="btn btn-ghost">Cancel</a>
        </form>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
