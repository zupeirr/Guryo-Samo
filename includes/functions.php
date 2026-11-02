<?php
/**
 * =========================================================================
 * includes/functions.php  —  Shared Helper Functions
 * =========================================================================
 *
 * PURPOSE:
 *   This file holds small, reusable "helper" functions that are used all
 *   over the project (public pages, admin pages, forms, etc.).
 *
 * HOW TO LOAD IT:
 *   require_once 'includes/functions.php';     (from root)
 *   require_once '../includes/functions.php';  (from /admin)
 *
 * SECTIONS:
 *   1. Session startup
 *   2. Utility helpers  (clean, formatPrice, redirect)
 *   3. Auth helpers     (isLoggedIn, isAdminLoggedIn, requireLogin)
 *   4. CSRF protection  (csrfToken, verifyCsrf)
 *   5. File upload      (uploadPropertyImage, uploadPropertyImages, uploadPropertyVideo)
 *   6. Media save       (savePropertyMedia)
 *   7. Pagination       (paginate, renderPagination)
 *   8. Settings         (getSetting)
 *   9. Rate limiting    (rateLimit)
 * =========================================================================
 */


/* ── 1. SESSION ──────────────────────────────────────────────────────────
   Start the PHP session if it has not been started yet.
   Sessions let us store data across page loads (e.g. who is logged in).
   We check first because calling session_start() twice causes an error. (if)- prevents a crash if session_start() is called twice*/
if (session_status() === PHP_SESSION_NONE) { session_start(); }


/* ── 2. UTILITY HELPERS ──────────────────────────────────────────────── */

/**
 * clean() — Sanitise a value before printing it in HTML.
 *
 * WHY: If we print raw user input on the page, a hacker could inject
 *      malicious HTML/JavaScript (called XSS). htmlspecialchars() converts
 *      dangerous characters like < > " ' into safe &lt; &gt; &quot; etc.
 *
 * @param  mixed  $v  The value to clean (string or null).
 * @return string     Safe, HTML-encoded string.
 *
 * Example:
 *   echo clean($_GET['name']);  // Safe to print on page
 */
function clean($v) {
    // trim()             – remove leading/trailing whitespace
    // ?? ''              – if $v is null, use empty string instead
    // htmlspecialchars() – convert dangerous characters to HTML entities
    // ENT_QUOTES         – also encode single quotes (important in attributes)
    // 'UTF-8'            – tell PHP to treat the string as UTF-8
    return htmlspecialchars(trim($v ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * formatPrice() — Format a number as a price string with a $ sign.
 *
 * Example: formatPrice(250000)  →  "$250,000"
 *
 * @param  mixed  $p  The price (number or string).
 * @return string     Formatted price like "$250,000".
 */
function formatPrice($p) {
    // (float) converts the value to a decimal number first
    // number_format(..., 0) adds commas every 3 digits, no decimal places
    return '$' . number_format((float)$p, 0);
}

/**
 * redirect() — Send the user to a different page.
 *
 * Works by sending an HTTP Location header to the browser, then
 * stopping PHP execution with exit so nothing else runs.
 *
 * Example: redirect('login.php');
 *          redirect('../index.php');
 *
 * @param string $url  The URL or path to redirect to.
 */
function redirect($url) {
    header('Location: ' . $url); // Tell the browser to go to $url
    exit;                         // Stop running any more PHP code
}


/* ── 3. AUTH HELPERS ─────────────────────────────────────────────────── */

/**
 * isLoggedIn() — Is ANY user currently logged in?
 *
 * After a successful login, login.php stores the user's ID in the session.
 * If $_SESSION['user_id'] exists, someone is logged in.
 *
 * @return bool  true = logged in, false = not logged in.
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * isAdminLoggedIn() — Is an admin or staff member currently logged in?
 *
 * Used to protect admin pages. Only accounts with role 'admin' or 'staff'
 * have access to the dashboard. Regular customers are blocked.
 *
 * @return bool  true = admin/staff logged in, false = otherwise.
 */
function isAdminLoggedIn() {
    // Check both: a user ID exists AND that user's role is admin or staff
    return isset($_SESSION['user_id'])
        && in_array($_SESSION['user_role'] ?? '', ['admin', 'staff'], true);
    //                                      ^^^   true = strict comparison (no type juggling)
}

/**
 * requireLogin() — Protect an admin page.
 *
 * Call this at the TOP of every admin page. If the visitor is not an
 * admin or staff member, they are immediately redirected to the login page.
 *
 * Example (top of admin/dashboard.php):
 *   requireLogin();
 */
function requireLogin() {
    if (!isAdminLoggedIn()) {
        redirect('../login.php'); // Send non-admins back to the login page
    }
}


/* ── 4. CSRF PROTECTION ──────────────────────────────────────────────── */

/**
 * csrfToken() — Generate (or retrieve) a secure CSRF token for forms.
 *
 * WHAT IS CSRF?
 *   Cross-Site Request Forgery — a type of attack where a malicious website
 *   tricks a logged-in user into submitting a form on YOUR site without
 *   knowing it. The CSRF token is a secret code that only OUR forms know,
 *   so fake submissions from other sites will fail the check.
 *
 * HOW TO USE:
 *   Inside your <form>:
 *       <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
 *
 * @return string  The CSRF token (a random hex string).
 */
function csrfToken() {
    // If no token exists yet for this session, create one using random bytes
    // random_bytes(32) = 32 random bytes → bin2hex converts to 64-char hex string
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * verifyCsrf() — Check that a submitted CSRF token matches the session token.
 *
 * Call this at the top of any POST form handler BEFORE doing anything else.
 *
 * HOW TO USE:
 *   if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
 *       die('Invalid request.');
 *   }
 *
 * @param  string|null $t  The token submitted by the form ($_POST['csrf_token']).
 * @return bool            true = token is valid, false = invalid/missing.
 */
function verifyCsrf($t) {
    // hash_equals() does a timing-safe comparison — it prevents "timing attacks"
    // where a hacker measures how fast the comparison runs to guess the token
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $t ?? '');
}


/* ── 5. FILE UPLOADS ─────────────────────────────────────────────────── */

/**
 * uploadPropertyImage() — Handle a single image upload from a form.
 *
 * Steps it performs:
 *   1. Checks if a file was actually submitted.
 *   2. Validates the file type (only JPG, PNG, WebP, GIF allowed).
 *   3. Validates the file size (max 5 MB).
 *   4. Generates a unique, safe filename.
 *   5. Reads the file's binary data so it can be saved to the database.
 *
 * @param string $fn   The HTML form field name (e.g. 'main_image').
 * @param string $dir  Upload directory (not used — images saved to DB).
 * @return array       On success: ['name'=>..., 'data'=>..., 'mime'=>...]
 *                     On no file: ['name'=>'no-image.jpg', 'data'=>null, 'mime'=>null]
 *                     On error:   ['error'=>'...message...']
 */
function uploadPropertyImage($fn, $dir) {
    // If no file was uploaded for this field, return a placeholder
    if (!isset($_FILES[$fn]) || $_FILES[$fn]['error'] === UPLOAD_ERR_NO_FILE) {
        return ['name' => 'no-image.jpg', 'data' => null, 'mime' => null];
    }

    $f = $_FILES[$fn]; // The uploaded file's info array

    // If PHP reported an upload error, fail immediately
    if ($f['error'] !== UPLOAD_ERR_OK) return ['error' => 'Upload failed.'];

    // Detect the real file type by reading the file's content (not just the extension)
    // This is safer than trusting $_FILES['type'], which users can fake
    $fi = finfo_open(FILEINFO_MIME_TYPE);
    $m  = finfo_file($fi, $f['tmp_name']); // $m = MIME type like 'image/jpeg'
    finfo_close($fi);

    // Only allow safe image formats
    if (!in_array($m, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
        return ['error' => 'Invalid image type.'];
    }

    // Reject files larger than 5 MB (5 * 1024 * 1024 bytes)
    if ($f['size'] > 5 * 1024 * 1024) return ['error' => 'Image too large (max 5MB).'];

    // Build a unique filename: property_<timestamp>_<random>.jpg
    // preg_replace removes any character that is not a letter or number from the extension
    $ext = preg_replace('/[^a-z0-9]/', '', strtolower(pathinfo($f['name'], PATHINFO_EXTENSION)));
    $n   = 'property_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

    // Read the file's raw binary data so we can store it directly in the database
    $data = file_get_contents($f['tmp_name']);

    return ['name' => $n, 'data' => $data, 'mime' => $m];
}

/**
 * uploadPropertyImages() — Handle MULTIPLE image uploads at once.
 *
 * Used for gallery images on a property. Works the same as
 * uploadPropertyImage() but loops through each file in the batch.
 *
 * @param string $fn   The HTML form field name (e.g. 'gallery_images[]').
 * @param string $dir  Upload directory (not used — images saved to DB).
 * @return array       Array of file info arrays, or ['error'=>'...'] on failure.
 */
function uploadPropertyImages($fn, $dir) {
    // If no files were selected, return an empty array
    if (!isset($_FILES[$fn]) || empty($_FILES[$fn]['name'][0])) return [];

    $fs = $_FILES[$fn];           // All uploaded files' info
    $c  = count($fs['name']);     // Total number of files uploaded
    $s  = [];                     // Results array we'll build up
    $al = ['image/jpeg', 'image/png', 'image/webp', 'image/gif']; // Allowed MIME types

    // Loop through each uploaded file one by one
    for ($i = 0; $i < $c; $i++) {
        // Skip files that had no file selected
        if ($fs['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
        // Stop and return error if any file had an upload error
        if ($fs['error'][$i] !== UPLOAD_ERR_OK) return ['error' => 'Upload failed.'];

        // Check real MIME type (same safety check as single upload above)
        $fi = finfo_open(FILEINFO_MIME_TYPE);
        $m  = finfo_file($fi, $fs['tmp_name'][$i]);
        finfo_close($fi);

        if (!in_array($m, $al, true))       return ['error' => 'Invalid image type.'];
        if ($fs['size'][$i] > 5 * 1024 * 1024) return ['error' => 'Image too large.'];

        // Generate a unique filename for each image (adds random number to avoid collisions)
        $ext = preg_replace('/[^a-z0-9]/', '', strtolower(pathinfo($fs['name'][$i], PATHINFO_EXTENSION)));
        $n   = 'property_' . time() . mt_rand(100, 999) . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        // Read the binary data and add this file's info to the results
        $data = file_get_contents($fs['tmp_name'][$i]);
        $s[]  = ['name' => $n, 'data' => $data, 'mime' => $m];
    }
    return $s; // Return the array of all uploaded images' data
}

/**
 * uploadPropertyVideo() — Handle a single video file upload.
 *
 * Allows MP4, WebM, OGG, and MOV formats. Max size is 100 MB.
 *
 * @param string $fn   The HTML form field name (e.g. 'property_video').
 * @param string $dir  Upload directory (not used — video saved to DB).
 * @return array|null  File info array on success, null if no file, ['error'=>...] on fail.
 */
function uploadPropertyVideo($fn, $dir) {
    // If no video was uploaded, return null (videos are optional)
    if (!isset($_FILES[$fn]) || $_FILES[$fn]['error'] === UPLOAD_ERR_NO_FILE) return null;

    $f  = $_FILES[$fn];
    $al = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime']; // Allowed video types

    if ($f['error'] !== UPLOAD_ERR_OK) return ['error' => 'Video upload failed.'];

    // Detect real MIME type
    $fi = finfo_open(FILEINFO_MIME_TYPE);
    $m  = finfo_file($fi, $f['tmp_name']);
    finfo_close($fi);

    if (!in_array($m, $al, true))          return ['error' => 'Only MP4/WEBM/OGG/MOV allowed.'];
    if ($f['size'] > 100 * 1024 * 1024)   return ['error' => 'Video must be under 100MB.'];

    // Unique filename: video_<timestamp>_<random>.mp4
    $ext  = preg_replace('/[^a-z0-9]/', '', strtolower(pathinfo($f['name'], PATHINFO_EXTENSION)));
    $n    = 'video_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $data = file_get_contents($f['tmp_name']); // Read binary data

    return ['name' => $n, 'data' => $data, 'mime' => $m];
}


/* ── 6. SAVE MEDIA TO DATABASE ────────────────────────────────────────── */

/**
 * savePropertyMedia() — Save uploaded image/video files into the database.
 *
 * After uploading, this function stores each file's binary data in the
 * property_media table so images/videos are served directly from the DB
 * via image.php (no need to manage file folders).
 *
 * @param mysqli $conn   Active database connection.
 * @param int    $pid    The property ID to attach the media to.
 * @param array  $files  Array of file info arrays from upload functions above.
 * @param string $type   'image' or 'video' (stored in media_type column).
 */
function savePropertyMedia($conn, $pid, $files, $type = 'image') {
    // Prepare the INSERT statement once and reuse it for each file (more efficient)
    $s = $conn->prepare(
        'INSERT INTO property_media (property_id, file_name, media_data, media_mime, media_type, sort_order)
         VALUES (?,?,?,?,?,?)'
    );
    // Loop through each file and insert a row
    foreach ($files as $i => $f) {
        $n = is_array($f) ? $f['name'] : $f;    // Filename string
        $d = is_array($f) ? $f['data'] : null;   // Binary data (blob)
        $m = is_array($f) ? $f['mime'] : null;   // MIME type string

        // bind_param('issssi', ...) — 'i'=integer, 's'=string
        // sort_order = $i so images stay in upload order
        $s->bind_param('issssi', $pid, $n, $d, $m, $type, $i);
        $s->execute();
    }
}


/* ── 7. PAGINATION ───────────────────────────────────────────────────── */

/**
 * paginate() — Calculate pagination values from a total record count.
 *
 * Reads the current page from $_GET['page'] (URL query string).
 * Returns an array with all values needed to slice a SQL query
 * and to render page navigation links.
 *
 * Example usage:
 *   $total = 150; // total rows from database COUNT(*)
 *   $p = paginate($total, 20);
 *   // Then in your SQL: ... LIMIT {$p['per_page']} OFFSET {$p['offset']}
 *
 * @param int $total    Total number of records in the database.
 * @param int $perPage  How many records to show per page (default 20).
 * @return array        Keys: total, per_page, current, total_pages, offset.
 */
function paginate(int $total, int $perPage = 20): array {
    // Read current page from URL, default to 1, make sure it's at least 1
    $cur = max(1, (int)($_GET['page'] ?? 1));
    // Calculate total pages needed (ceil rounds up — e.g. 21 items / 20 per page = 2 pages)
    $tp  = max(1, (int)ceil($total / $perPage));
    // Make sure current page doesn't exceed total pages
    $cur = min($cur, $tp);

    return [
        'total'       => $total,
        'per_page'    => $perPage,
        'current'     => $cur,
        'total_pages' => $tp,
        'offset'      => ($cur - 1) * $perPage, // How many rows to skip in SQL OFFSET
    ];
}

/**
 * renderPagination() — Build the HTML for prev/next page buttons.
 *
 * @param array  $p   Pagination data from paginate() above.
 * @param string $bu  Base URL for links (e.g. 'properties.php').
 * @param array  $ex  Extra query params to preserve in links (e.g. search filters).
 * @return string     HTML string for the pagination bar, or '' if only 1 page.
 */
function renderPagination(array $p, string $bu, array $ex = []): string {
    // If there is only 1 page, no navigation is needed
    if ($p['total_pages'] <= 1) return '';

    $pa = $ex;                 // Start with any extra params (e.g. search filters)
    $l  = '<div class="pagination">'; // Opening wrapper

    // "‹ Prev" button — only show if we're not on page 1
    if ($p['current'] > 1) {
        $pa['page'] = $p['current'] - 1;
        $l .= '<a href="' . $bu . '?' . http_build_query(array_filter($pa)) . '" class="page-btn">‹ Prev</a>';
    }

    // Numbered page buttons — show 2 pages either side of the current page
    $st = max(1, $p['current'] - 2);
    $en = min($p['total_pages'], $p['current'] + 2);
    for ($i = $st; $i <= $en; $i++) {
        $pa['page'] = $i;
        $ac = $i === $p['current'] ? ' active' : ''; // Highlight current page
        $l .= '<a href="' . $bu . '?' . http_build_query(array_filter($pa)) . '" class="page-btn' . $ac . '">' . $i . '</a>';
    }

    // "Next ›" button — only show if we're not on the last page
    if ($p['current'] < $p['total_pages']) {
        $pa['page'] = $p['current'] + 1;
        $l .= '<a href="' . $bu . '?' . http_build_query(array_filter($pa)) . '" class="page-btn">Next ›</a>';
    }

    $l .= '</div>';
    return $l;
}


/* ── 8. SITE SETTINGS ────────────────────────────────────────────────── */

/**
 * getSetting() — Retrieve a site-wide setting from the database.
 *
 * Settings (site name, contact phone, social links, etc.) are stored in
 * the `settings` table as key–value pairs so they can be changed from
 * the Admin → Settings page without editing any PHP file.
 *
 * @param mysqli $conn     Active database connection.
 * @param string $key      The setting name (e.g. 'site_name', 'contact_email').
 * @param string $default  Value to return if the setting doesn't exist.
 * @return string          The setting value, or $default.
 *
 * Example:
 *   $siteName = getSetting($conn, 'site_name', 'Guryo Samo');
 */
function getSetting($conn, $key, $default = '') {
    $s = $conn->prepare('SELECT setting_value FROM settings WHERE setting_key=?');
    if ($s) {
        $s->bind_param('s', $key);
        $s->execute();
        $r = $s->get_result();
        // If the row exists AND setting_value is not NULL, return it
        if ($r && $r->num_rows > 0) {
            $row = $r->fetch_assoc();
            return $row['setting_value'] !== null ? $row['setting_value'] : $default;
        }
    }
    return $default; // Setting not found — return the default value
}


/* ── 9. RATE LIMITING ────────────────────────────────────────────────── */

/**
 * rateLimit() — Prevent users from submitting forms too many times too quickly.
 *
 * WHAT IS RATE LIMITING?
 *   It stops form spam and brute-force attacks by limiting how many times
 *   someone can submit a form within a short time window.
 *
 * HOW IT WORKS:
 *   It stores a counter + timestamp in the user's PHP session.
 *   Each call increments the counter. If the counter exceeds $maxHits
 *   within $window seconds, the function returns false (blocked).
 *   When the time window expires, the counter resets automatically.
 *
 * HOW TO USE:
 *   if (!rateLimit('contact_form', 3, 60)) {
 *       die('Too many requests. Please wait a moment.');
 *   }
 *   // ... process form normally ...
 *
 * @param string $key      Unique name for the action (e.g. 'contact_form', 'login').
 * @param int    $maxHits  Maximum allowed submissions in the time window.
 * @param int    $window   Time window in seconds (default: 60 seconds = 1 minute).
 * @return bool            true = request is allowed, false = limit exceeded.
 */
function rateLimit(string $key, int $maxHits = 3, int $window = 60): bool {
    // Make sure the session is running (it should already be, but just in case)
    if (session_status() === PHP_SESSION_NONE) { session_start(); }

    $now        = time();                 // Current Unix timestamp (seconds since 1970)
    $sessionKey = '__rl_' . $key;         // Unique session key, e.g. '__rl_contact_form'

    // If this is the first time this action is being tracked, initialise the counter
    if (!isset($_SESSION[$sessionKey])) {
        $_SESSION[$sessionKey] = ['count' => 0, 'start' => $now];
    }

    // Get a reference to the stored data (& means we can modify it directly)
    $data = &$_SESSION[$sessionKey];

    // If the time window has expired, reset the counter for a fresh window
    if ($now - $data['start'] > $window) {
        $data = ['count' => 0, 'start' => $now];
    }

    // Increment the counter for this submission
    $data['count']++;

    // Return true (allowed) if under the limit, false (blocked) if over
    return $data['count'] <= $maxHits;
}
