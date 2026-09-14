<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle = 'Edit Property';
$activeAdminPage = 'properties';

$allowedTypes = ['House','Apartment','Land','Office','Commercial','Villa'];
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
$formData['amenities'] = isset($property['amenities']) && $property['amenities'] ? json_decode($property['amenities'], true) : [];
if (!is_array($formData['amenities'])) $formData['amenities'] = [];

// Fetch existing extra media
$mediaStmt = $conn->prepare("SELECT * FROM property_media WHERE property_id = ? ORDER BY sort_order ASC");
$mediaStmt->bind_param('i', $id);
$mediaStmt->execute();
$mediaRes = $mediaStmt->get_result();
$existingMedia = [];
while ($m = $mediaRes->fetch_assoc()) {
    $existingMedia[] = $m;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errorMsg = 'Your session has expired. Please try submitting the form again.';
    } else {
        $formData['property_ref']= trim($_POST['property_ref'] ?? '');
        $formData['title']       = trim($_POST['title'] ?? '');
        $formData['type']        = $_POST['type'] ?? '';
        $formData['price']       = $_POST['price'] ?? '';
        $formData['location']    = trim($_POST['location'] ?? '');
        $formData['description'] = trim($_POST['description'] ?? '');
        $formData['bedrooms']    = $_POST['bedrooms'] ?? 0;
        $formData['bathrooms']   = $_POST['bathrooms'] ?? 0;
        $formData['size']        = $_POST['size'] ?? 0;
        $formData['status']      = $_POST['status'] ?? '';
        $formData['amenities']   = isset($_POST['amenities']) && is_array($_POST['amenities']) ? $_POST['amenities'] : [];

        // Media deletion handling
        if (!empty($_POST['delete_media'])) {
            $delIds = array_map('intval', $_POST['delete_media']);
            foreach ($delIds as $delId) {
                // Get filename first to unlink
                $dstmt = $conn->prepare("SELECT file_name FROM property_media WHERE id = ? AND property_id = ?");
                $dstmt->bind_param('ii', $delId, $id);
                $dstmt->execute();
                $dres = $dstmt->get_result();
                if ($drow = $dres->fetch_assoc()) {
                    @unlink('../uploads/' . $drow['file_name']);
                    $conn->query("DELETE FROM property_media WHERE id = $delId");
                }
            }
        }

        if ($formData['title'] === '' || $formData['location'] === '' || $formData['price'] === '' || $formData['property_ref'] === '') {
            $errorMsg = 'Please fill in the title, property reference, location, and price.';
        } elseif (!in_array($formData['type'], $allowedTypes, true)) {
            $errorMsg = 'Please select a valid property type.';
        } elseif (!in_array($formData['status'], $allowedStatus, true)) {
            $errorMsg = 'Please select a valid property status.';
        } elseif (!is_numeric($formData['price']) || $formData['price'] < 0) {
            $errorMsg = 'Please enter a valid price.';
        } else {
            $imageResult = $property['image'];
            
            // --- Cover image ---
            if (isset($_FILES['coverImage']) && $_FILES['coverImage']['error'] !== UPLOAD_ERR_NO_FILE) {
                $upRes = uploadPropertyImage('coverImage', '../uploads');
                if (is_array($upRes) && isset($upRes['error'])) {
                    $errorMsg = $upRes['error'];
                } else {
                    if ($property['image'] && $property['image'] !== 'no-image.jpg' && file_exists('../uploads/' . $property['image'])) {
                        @unlink('../uploads/' . $property['image']);
                    }
                    $imageResult = $upRes;
                }
            }

            // --- Additional images ---
            $extraImages = [];
            if (empty($errorMsg)) {
                $extraImages = uploadPropertyImages('galleryImages', '../uploads');
                if (is_array($extraImages) && isset($extraImages['error'])) {
                    $errorMsg = $extraImages['error'];
                    $extraImages = [];
                }
            }

            // --- Video ---
            $videoName = '';
            if (empty($errorMsg)) {
                $videoResult = uploadPropertyVideo('propertyVideo', '../uploads');
                if (is_array($videoResult) && isset($videoResult['error'])) {
                    $errorMsg = $videoResult['error'];
                } else if ($videoResult !== '') {
                    $videoName = $videoResult;
                }
            }

            if (empty($errorMsg)) {
                $amenitiesJson = json_encode($formData['amenities']);
                
                $imgName = is_array($imageResult) ? $imageResult['name'] : $imageResult;
                $imgData = is_array($imageResult) ? $imageResult['data'] : null;
                $imgMime = is_array($imageResult) ? $imageResult['mime'] : null;

                if ($imgData !== null) {
                    $stmt = $conn->prepare(
                        "UPDATE properties SET property_ref=?, title=?, type=?, price=?, location=?, description=?, bedrooms=?, bathrooms=?, size=?, status=?, amenities=?, image=?, image_data=?, image_type=? WHERE id=?"
                    );
                    $stmt->bind_param(
                        'sssdssiidsssssi',
                        $formData['property_ref'], $formData['title'], $formData['type'], $formData['price'], $formData['location'],
                        $formData['description'], $formData['bedrooms'], $formData['bathrooms'],
                        $formData['size'], $formData['status'], $amenitiesJson, $imgName, $imgData, $imgMime, $id
                    );
                } else {
                    $stmt = $conn->prepare(
                        "UPDATE properties SET property_ref=?, title=?, type=?, price=?, location=?, description=?, bedrooms=?, bathrooms=?, size=?, status=?, amenities=?, image=? WHERE id=?"
                    );
                    $stmt->bind_param(
                        'sssdssiidsssi',
                        $formData['property_ref'], $formData['title'], $formData['type'], $formData['price'], $formData['location'],
                        $formData['description'], $formData['bedrooms'], $formData['bathrooms'],
                        $formData['size'], $formData['status'], $amenitiesJson, $imgName, $id
                    );
                }

                if ($stmt->execute()) {
                    if (!empty($extraImages)) savePropertyMedia($conn, $id, $extraImages, 'image');
                    if ($videoName) savePropertyMedia($conn, $id, [$videoName], 'video');
                    redirect('properties.php?saved=1');
                } else {
                    $errorMsg = 'Something went wrong while saving the property. Please try again.';
                }
            }
        }
    }
}

include 'includes/admin-header.php';
?>

<?php if ($errorMsg): ?>
<div class="alert-banner danger" style="margin-bottom:20px;">
    <span class="alert-icon">⚠️</span>
    <span><?php echo clean($errorMsg); ?></span>
</div>
<?php endif; ?>

<div class="panel">
    <div class="panel-head">
        <div>
            <h2>Edit Property: <?php echo clean($property['title']); ?></h2>
            <p style="font-size:0.83rem;color:var(--ink-soft);margin-top:3px;">Update the property details and media below.</p>
        </div>
        <span style="font-size:0.78rem;color:var(--ink-soft);">Fields marked <span style="color:var(--danger);">*</span> are required</span>
    </div>
    <div class="panel-body">
        <form method="post" enctype="multipart/form-data" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            <input type="hidden" name="id" value="<?php echo (int)$id; ?>">

            <!-- Section: Basic Details -->
            <div class="form-section-label">🏠 Property Details</div>
            <div class="form-grid-2">
                <div class="field-block">
                    <label for="property_ref">Property Ref <span class="req">*</span></label>
                    <input type="text" id="property_ref" name="property_ref" value="<?php echo clean($formData['property_ref']); ?>" required>
                </div>
                <div class="field-block">
                    <label for="title">Property Title <span class="req">*</span></label>
                    <input type="text" id="title" name="title" value="<?php echo clean($formData['title']); ?>" required>
                </div>
                <div class="field-block">
                    <label for="type">Property Type <span class="req">*</span></label>
                    <select id="type" name="type" required>
                        <?php foreach ($allowedTypes as $t): ?>
                            <option value="<?php echo $t; ?>" <?php echo $formData['type'] === $t ? 'selected' : ''; ?>><?php echo $t; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field-block">
                    <label for="price">Price ($) <span class="req">*</span></label>
                    <input type="number" step="0.01" id="price" name="price" value="<?php echo clean($formData['price']); ?>" required>
                </div>
                <div class="field-block">
                    <label for="location">Location <span class="req">*</span></label>
                    <input type="text" id="location" name="location" value="<?php echo clean($formData['location']); ?>" required>
                </div>
            </div>

            <div class="field-block" style="margin-top:18px;">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"><?php echo clean($formData['description']); ?></textarea>
            </div>

            <!-- Section: Features & Amenities -->
            <div class="form-section-label" style="margin-top:24px;">✨ Features & Amenities</div>
            <div class="field-block">
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(160px, 1fr)); gap:12px;">
                    <?php
                    $availableAmenities = ['Parking', 'Garden', 'Security', 'Water supply', 'Electricity', 'Furnished', 'Unfurnished', 'Air Conditioning', 'Balcony', 'Pool'];
                    foreach ($availableAmenities as $amn):
                    ?>
                    <label style="display:flex; align-items:center; gap:8px; font-weight:normal; cursor:pointer;">
                        <input type="checkbox" name="amenities[]" value="<?php echo clean($amn); ?>" <?php echo in_array($amn, $formData['amenities']) ? 'checked' : ''; ?>>
                        <?php echo clean($amn); ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-divider"></div>

            <!-- Section: Specs -->
            <div class="form-section-label">📐 Specifications</div>
            <div class="form-grid-2">
                <div class="field-block">
                    <label for="bedrooms">Bedrooms</label>
                    <input type="number" id="bedrooms" name="bedrooms" value="<?php echo clean($formData['bedrooms']); ?>" min="0">
                </div>
                <div class="field-block">
                    <label for="bathrooms">Bathrooms</label>
                    <input type="number" id="bathrooms" name="bathrooms" value="<?php echo clean($formData['bathrooms']); ?>" min="0">
                </div>
                <div class="field-block">
                    <label for="size">Property Size (m²)</label>
                    <input type="number" step="0.01" id="size" name="size" value="<?php echo clean($formData['size']); ?>" min="0">
                </div>
                <div class="field-block">
                    <label for="status">Property Status <span class="req">*</span></label>
                    <select id="status" name="status" required>
                        <?php foreach ($allowedStatus as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo $formData['status'] === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-divider"></div>

            <!-- Section: Existing Extra Media (if any) -->
            <?php if (!empty($existingMedia)): ?>
            <div class="form-section-label">📂 Existing Media</div>
            <p style="font-size:0.8rem;color:var(--ink-soft);margin-bottom:10px;">Select any images or videos you wish to delete.</p>
            <div class="gallery-preview-grid" style="margin-bottom:24px;">
                <?php foreach ($existingMedia as $media): ?>
                    <div class="gallery-thumb" style="position:relative;">
                        <?php if ($media['media_type'] === 'video'): ?>
                            <video src="../image.php?type=media&id=<?php echo $media['id']; ?>" style="width:80px;height:60px;object-fit:cover;"></video>
                        <?php else: ?>
                            <img src="../image.php?type=media&id=<?php echo $media['id']; ?>" alt="Media">
                        <?php endif; ?>
                        <label style="font-size:0.7rem; display:flex; align-items:center; gap:4px; margin-top:4px;">
                            <input type="checkbox" name="delete_media[]" value="<?php echo $media['id']; ?>"> Delete
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Section: Media -->
            <div class="form-section-label">📸 Upload New Media</div>

            <!-- Cover Image -->
            <div class="field-block">
                <label for="coverImage">Cover Image <span style="color:var(--ink-soft);font-weight:400;">(Replaces current main image if uploaded)</span></label>
                <div style="display:flex;gap:12px;margin-bottom:12px;align-items:center;">
                    <img src="<?php echo ($property['image'] && $property['image'] !== 'no-image.jpg') ? '../image.php?type=property&id='.$property['id'] : '../assets/images/no-image.jpg'; ?>" 
                         style="width:60px;height:60px;object-fit:cover;border-radius:6px;border:1px solid var(--border);">
                    <span style="font-size:0.8rem;color:var(--ink-soft);">Current Cover</span>
                </div>
                
                <div class="media-drop-zone" id="coverDropZone" onclick="document.getElementById('coverImage').click()" style="padding:16px;">
                    <span class="media-drop-icon" style="font-size:1.5rem;">🖼️</span>
                    <span class="media-drop-text">Click or drag to replace cover image</span>
                </div>
                <input type="file" id="coverImage" name="coverImage" accept="image/jpeg,image/png,image/webp,image/gif" style="display:none" onchange="previewCover(this)">
                <div id="coverPreviewWrap" style="display:none;margin-top:12px;">
                    <img id="coverPreview" src="" alt="Cover preview" class="media-preview-img" style="width:120px;height:80px;">
                    <button type="button" class="media-remove-btn" onclick="removeCover()">✕ Remove</button>
                </div>
            </div>

            <!-- Gallery Images -->
            <div class="field-block" style="margin-top:18px;">
                <label for="galleryImages">Add Gallery Images <span style="color:var(--ink-soft);font-weight:400;">(Appended to existing)</span></label>
                <div class="media-drop-zone" id="galleryDropZone" onclick="document.getElementById('galleryImages').click()" style="padding:16px;">
                    <span class="media-drop-icon" style="font-size:1.5rem;">🗂️</span>
                    <span class="media-drop-text">Click or drag to add more gallery images</span>
                </div>
                <input type="file" id="galleryImages" name="galleryImages[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple style="display:none" onchange="previewGallery(this)">
                <div id="galleryPreviewWrap" class="gallery-preview-grid" style="margin-top:12px;"></div>
            </div>

            <!-- Video -->
            <div class="field-block" style="margin-top:18px;">
                <label for="propertyVideo">Add Property Video <span style="color:var(--ink-soft);font-weight:400;">(Appended to existing)</span></label>
                <div class="media-drop-zone" id="videoDropZone" onclick="document.getElementById('propertyVideo').click()" style="padding:16px;">
                    <span class="media-drop-icon" style="font-size:1.5rem;">🎬</span>
                    <span class="media-drop-text">Click or drag to add a video</span>
                </div>
                <input type="file" id="propertyVideo" name="propertyVideo" accept="video/mp4,video/webm,video/ogg,video/quicktime" style="display:none" onchange="previewVideo(this)">
                <div id="videoPreviewWrap" style="display:none;margin-top:12px;">
                    <video id="videoPreview" controls class="media-preview-video" style="max-width:200px;"></video>
                    <button type="button" class="media-remove-btn" onclick="removeVideo()">✕ Remove</button>
                </div>
            </div>

            <div class="form-divider"></div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">✚ Update Property</button>
                <a href="properties.php" class="btn btn-ghost btn-lg">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function previewCover(input) {
    if (!input.files[0]) return;
    const wrap = document.getElementById('coverPreviewWrap');
    const img  = document.getElementById('coverPreview');
    img.src = URL.createObjectURL(input.files[0]);
    wrap.style.display = 'flex';
    document.getElementById('coverDropZone').style.display = 'none';
}
function removeCover() {
    document.getElementById('coverImage').value = '';
    document.getElementById('coverPreviewWrap').style.display = 'none';
    document.getElementById('coverDropZone').style.display = '';
}

let galleryDt = new DataTransfer();

function previewGallery(input) {
    const wrap = document.getElementById('galleryPreviewWrap');
    
    // Add new files to DataTransfer
    Array.from(input.files).forEach(file => {
        // Prevent duplicate file names
        let exists = false;
        for (let i = 0; i < galleryDt.files.length; i++) {
            if (galleryDt.files[i].name === file.name) exists = true;
        }
        if (!exists) galleryDt.items.add(file);
    });
    
    // Update input files
    input.files = galleryDt.files;
    
    wrap.innerHTML = '';
    Array.from(input.files).forEach(function(file, index) {
        const div = document.createElement('div');
        div.className = 'gallery-thumb';
        div.style.position = 'relative';
        
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.alt = file.name;
        div.appendChild(img);
        
        const name = document.createElement('span');
        name.textContent = file.name.length > 18 ? file.name.slice(0,16)+'…' : file.name;
        div.appendChild(name);
        
        // Remove button
        const removeBtn = document.createElement('span');
        removeBtn.innerHTML = '&times;';
        removeBtn.style.cssText = 'position:absolute; top:-6px; right:-6px; background:var(--danger); color:#fff; border-radius:50%; width:20px; height:20px; line-height:18px; text-align:center; font-size:14px; cursor:pointer; font-weight:bold; box-shadow:0 2px 4px rgba(0,0,0,0.2);';
        removeBtn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            const newDt = new DataTransfer();
            for (let i = 0; i < galleryDt.files.length; i++) {
                if (i !== index) newDt.items.add(galleryDt.files[i]);
            }
            galleryDt = newDt;
            input.files = galleryDt.files;
            previewGallery(input); // Re-render
        };
        div.appendChild(removeBtn);
        
        wrap.appendChild(div);
    });
}

function previewVideo(input) {
    if (!input.files[0]) return;
    const wrap  = document.getElementById('videoPreviewWrap');
    const video = document.getElementById('videoPreview');
    video.src = URL.createObjectURL(input.files[0]);
    wrap.style.display = 'flex';
    document.getElementById('videoDropZone').style.display = 'none';
}
function removeVideo() {
    document.getElementById('propertyVideo').value = '';
    document.getElementById('videoPreviewWrap').style.display = 'none';
    document.getElementById('videoDropZone').style.display = '';
}

['coverDropZone','galleryDropZone','videoDropZone'].forEach(function(zoneId) {
    const zone = document.getElementById(zoneId);
    zone.addEventListener('dragover', function(e) { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', function() { zone.classList.remove('drag-over'); });
    zone.addEventListener('drop', function(e) {
        e.preventDefault(); zone.classList.remove('drag-over');
        const inputId = zoneId === 'coverDropZone' ? 'coverImage' : zoneId === 'galleryDropZone' ? 'galleryImages' : 'propertyVideo';
        const input = document.getElementById(inputId);
        const dt = new DataTransfer();
        Array.from(e.dataTransfer.files).forEach(f => dt.items.add(f));
        input.files = dt.files;
        input.dispatchEvent(new Event('change'));
    });
});
</script>

<?php include 'includes/admin-footer.php'; ?>
