<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle = 'Add Property';
$activeAdminPage = 'add';

$errorMsg = '';
$formData = [
    'title' => '', 'property_ref' => '', 'type' => 'Apartment', 'price' => '', 'location' => '',
    'description' => '', 'bedrooms' => '', 'bathrooms' => '', 'size' => '', 'status' => 'For Sale', 'amenities' => []
];

$allowedTypes  = ['House','Apartment','Land','Office','Commercial','Villa'];
$allowedStatus = ['For Sale','For Rent','Sold'];

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

        if ($formData['title'] === '' || $formData['location'] === '' || $formData['price'] === '' || $formData['property_ref'] === '') {
            $errorMsg = 'Please fill in the title, property reference, location, and price.';
        } elseif (!in_array($formData['type'], $allowedTypes, true)) {
            $errorMsg = 'Please select a valid property type.';
        } elseif (!in_array($formData['status'], $allowedStatus, true)) {
            $errorMsg = 'Please select a valid property status.';
        } elseif (!is_numeric($formData['price']) || $formData['price'] < 0) {
            $errorMsg = 'Please enter a valid price.';
        } else {
            // --- Cover image (first uploaded image or legacy single) ---
            $imageResult = uploadPropertyImage('coverImage', '../uploads');
            if (is_array($imageResult) && isset($imageResult['error'])) {
                $errorMsg = $imageResult['error'];
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
                } else {
                    $videoName = $videoResult;
                }
            }

            if (empty($errorMsg)) {
                $amenitiesJson = json_encode($formData['amenities']);
                $stmt = $conn->prepare(
                    "INSERT INTO properties (property_ref, title, type, price, location, description, bedrooms, bathrooms, size, status, amenities, image, image_data, image_type)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                
                $imgName = is_array($imageResult) ? $imageResult['name'] : $imageResult;
                $imgData = is_array($imageResult) ? $imageResult['data'] : null;
                $imgMime = is_array($imageResult) ? $imageResult['mime'] : null;

                $stmt->bind_param(
                    'sssdssiidsssss',
                    $formData['property_ref'], $formData['title'], $formData['type'], $formData['price'], $formData['location'],
                    $formData['description'], $formData['bedrooms'], $formData['bathrooms'],
                    $formData['size'], $formData['status'], $amenitiesJson, $imgName, $imgData, $imgMime
                );

                if ($stmt->execute()) {
                    $newId = $conn->insert_id;
                    // Save gallery images
                    if (!empty($extraImages)) {
                        savePropertyMedia($conn, $newId, $extraImages, 'image');
                    }
                    // Save video
                    if ($videoName) {
                        savePropertyMedia($conn, $newId, [$videoName], 'video');
                    }
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
            <h2>Add New Property</h2>
            <p style="font-size:0.83rem;color:var(--ink-soft);margin-top:3px;">Fill in the details below to list a new property.</p>
        </div>
        <span style="font-size:0.78rem;color:var(--ink-soft);">Fields marked <span style="color:var(--danger);">*</span> are required</span>
    </div>
    <div class="panel-body">
        <form method="post" enctype="multipart/form-data" class="admin-form" id="addPropertyForm">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">

            <!-- Section: Basic Details -->
            <div class="form-section-label">🏠 Property Details</div>
            <div class="form-grid-2">
                <div class="field-block">
                    <label for="property_ref">Property Ref <span class="req">*</span></label>
                    <?php
                        $defaultRef = $formData['property_ref'];
                        if (empty($defaultRef)) {
                            $nextId = $conn->query("SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'properties'")->fetch_assoc()['AUTO_INCREMENT'] ?? '1';
                            $defaultRef = 'GS-' . date('Y') . '-' . str_pad((int)$nextId, 5, '0', STR_PAD_LEFT);
                        }
                    ?>
                    <input type="text" id="property_ref" name="property_ref" value="<?php echo clean($defaultRef); ?>" required>
                </div>
                <div class="field-block">
                    <label for="title">Property Title <span class="req">*</span></label>
                    <input type="text" id="title" name="title" value="<?php echo clean($formData['title']); ?>" placeholder="e.g. Modern Family Villa" required>
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
                    <input type="number" step="0.01" id="price" name="price" value="<?php echo clean($formData['price']); ?>" placeholder="e.g. 250000" required>
                </div>
                <div class="field-block">
                    <label for="location">Location <span class="req">*</span></label>
                    <input type="text" id="location" name="location" value="<?php echo clean($formData['location']); ?>" placeholder="e.g. Hargeisa, Jigjiga Yar" required>
                </div>
            </div>

            <div class="field-block" style="margin-top:18px;">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" placeholder="Describe the property features, highlights, nearby landmarks..."><?php echo clean($formData['description']); ?></textarea>
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
                    <input type="number" id="bedrooms" name="bedrooms" value="<?php echo clean($formData['bedrooms']); ?>" min="0" placeholder="e.g. 3">
                </div>
                <div class="field-block">
                    <label for="bathrooms">Bathrooms</label>
                    <input type="number" id="bathrooms" name="bathrooms" value="<?php echo clean($formData['bathrooms']); ?>" min="0" placeholder="e.g. 2">
                </div>
                <div class="field-block">
                    <label for="size">Property Size (m²)</label>
                    <input type="number" step="0.01" id="size" name="size" value="<?php echo clean($formData['size']); ?>" min="0" placeholder="e.g. 120">
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

            <!-- Section: Media -->
            <div class="form-section-label">📸 Property Media</div>

            <!-- Cover Image -->
            <div class="field-block">
                <label for="coverImage">Cover Image <span style="color:var(--ink-soft);font-weight:400;">(main listing image, max 5MB)</span></label>
                <div class="media-drop-zone" id="coverDropZone" onclick="document.getElementById('coverImage').click()">
                    <span class="media-drop-icon">🖼️</span>
                    <span class="media-drop-text">Click or drag to upload cover image</span>
                    <span class="media-drop-hint">JPG, PNG, WEBP, GIF — max 5MB</span>
                </div>
                <input type="file" id="coverImage" name="coverImage" accept="image/jpeg,image/png,image/webp,image/gif" style="display:none" onchange="previewCover(this)">
                <div id="coverPreviewWrap" style="display:none;margin-top:12px;">
                    <img id="coverPreview" src="" alt="Cover preview" class="media-preview-img">
                    <button type="button" class="media-remove-btn" onclick="removeCover()">✕ Remove</button>
                </div>
            </div>

            <!-- Gallery Images -->
            <div class="field-block" style="margin-top:18px;">
                <label for="galleryImages">Gallery Images <span style="color:var(--ink-soft);font-weight:400;">(select multiple — up to 10, max 5MB each)</span></label>
                <div class="media-drop-zone" id="galleryDropZone" onclick="document.getElementById('galleryImages').click()">
                    <span class="media-drop-icon">🗂️</span>
                    <span class="media-drop-text">Click or drag to upload gallery images</span>
                    <span class="media-drop-hint">Select multiple files at once</span>
                </div>
                <input type="file" id="galleryImages" name="galleryImages[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple style="display:none" onchange="previewGallery(this)">
                <div id="galleryPreviewWrap" class="gallery-preview-grid" style="margin-top:12px;"></div>
            </div>

            <!-- Video -->
            <div class="field-block" style="margin-top:18px;">
                <label for="propertyVideo">Property Video <span style="color:var(--ink-soft);font-weight:400;">(optional — MP4/WEBM/MOV, max 100MB)</span></label>
                <div class="media-drop-zone" id="videoDropZone" onclick="document.getElementById('propertyVideo').click()">
                    <span class="media-drop-icon">🎬</span>
                    <span class="media-drop-text">Click or drag to upload a property video</span>
                    <span class="media-drop-hint">MP4, WEBM, OGG, MOV — max 100MB</span>
                </div>
                <input type="file" id="propertyVideo" name="propertyVideo" accept="video/mp4,video/webm,video/ogg,video/quicktime" style="display:none" onchange="previewVideo(this)">
                <div id="videoPreviewWrap" style="display:none;margin-top:12px;">
                    <video id="videoPreview" controls class="media-preview-video"></video>
                    <button type="button" class="media-remove-btn" onclick="removeVideo()">✕ Remove</button>
                </div>
            </div>

            <div class="form-divider"></div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">✚ Save Property</button>
                <a href="properties.php" class="btn btn-ghost btn-lg">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// ---- Cover Image ----
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

// ---- Gallery Images ----
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

// ---- Video ----
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

// ---- Drag & Drop support ----
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

