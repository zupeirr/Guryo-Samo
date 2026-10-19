<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$pageTitle = 'Login';
$activePage = 'login';
$baseUrl = '';

// If already logged in:
// Admins/staff go to dashboard; customers go to home page (prevents redirect loop)
if (isLoggedIn()) {
    if (isAdminLoggedIn()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('index.php');
    }
}

$errorMsg = '';
// Show a friendly message after a successful registration (see register.php)
$successMsg = isset($_GET['registered']) ? 'Account created successfully! You can now log in below.' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Rate-limit: block brute-force attacks — max 5 login attempts per 5 minutes per IP.
    // rateLimit() uses the PHP session to track attempt counts.
    if (!rateLimit('login_' . md5($_SERVER['REMOTE_ADDR'] ?? ''), 5, 300)) {
        $errorMsg = 'Too many failed login attempts. Please wait 5 minutes and try again.';
    } else {

    // 1) CSRF check - makes sure the form was submitted from our own site
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errorMsg = 'Your session has expired. Please try logging in again.';
    } else {
        $loginInput = trim($_POST['username'] ?? '');
        $password   = $_POST['password'] ?? '';

        if ($loginInput === '' || $password === '') {
            $errorMsg = 'Please enter both username/email and password.';
        } else {
            // 2) Look up the account by username OR email (case-insensitive)
            $stmt = $conn->prepare(
                "SELECT id, full_name, username, email, password, role 
                 FROM users 
                 WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?) 
                 LIMIT 1"
            );
            $stmt->bind_param('ss', $loginInput, $loginInput);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            $authenticated = false;

            if ($user) {
                // 3) password_verify() checks the typed password against the bcrypt hash
                if (password_verify($password, $user['password'])) {
                    $authenticated = true;
                    // Rehash if algorithm/cost parameters have updated
                    if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $rehashStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $rehashStmt->bind_param('si', $newHash, $user['id']);
                        $rehashStmt->execute();
                    }
                }
            }

            if ($authenticated) {
                // Ensure admin role if user is the designated admin account
                if (strtolower($user['username']) === 'admin' && $user['role'] !== 'admin') {
                    $user['role'] = 'admin';
                    $upRole = $conn->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
                    $upRole->bind_param('i', $user['id']);
                    $upRole->execute();
                }

                // Prevent session fixation attacks by issuing a new session id
                session_regenerate_id(true);

                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = !empty($user['full_name']) ? $user['full_name'] : $user['username'];
                $_SESSION['user_role'] = $user['role'];

                // 4) Send the person to the right place depending on their role:
                //    - admin/staff -> the management dashboard
                //    - customer    -> the normal home page
                if (in_array($user['role'], ['admin', 'staff'], true)) {
                    redirect('admin/dashboard.php');
                } else {
                    redirect('index.php');
                }
            } else {
                $errorMsg = 'Invalid username or password.';
            }
        }
    }
    } // end rate-limit block
}

include 'includes/header.php';
?>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="mark">GS</div>
        <h2 style="margin-bottom:6px;">Login</h2>
        <p style="color:var(--ink-soft); font-size:0.9rem; margin-bottom:24px;">Sign in to your account. Admins are taken to the dashboard automatically.</p>

        <?php if ($successMsg): ?><div class="alert alert-success"><?php echo clean($successMsg); ?></div><?php endif; ?>
        <?php if ($errorMsg): ?><div class="alert alert-error"><?php echo clean($errorMsg); ?></div><?php endif; ?>

        <form method="post" autocomplete="on">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            
            <div class="field" style="margin-bottom:16px;">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" placeholder="admin or name@example.com" value="<?php echo clean($_POST['username'] ?? ''); ?>" required autofocus>
            </div>
            
            <div class="field" style="margin-bottom:20px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <label for="password" style="margin-bottom:0;">Password</label>
                    <span id="capsNotice" style="display:none; color:var(--gold-600); font-size:0.75rem; font-weight:600;">Caps Lock is ON</span>
                </div>
                <div style="position:relative; display:flex; align-items:center;">
                    <input type="password" id="password" name="password" placeholder="••••••••" required style="padding-right:42px; width:100%;">
                    <button type="button" id="togglePasswordBtn" title="Show / Hide Password" aria-label="Show or hide password" style="position:absolute; right:10px; background:transparent; border:none; color:var(--ink-soft); cursor:pointer; padding:6px; display:flex; align-items:center; justify-content:center;">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Log In</button>
        </form>

        <p style="text-align:center; font-size:0.88rem; margin-top:18px; color:var(--ink-soft);">
            Don't have an account? <a href="register.php" style="color:var(--gold-600); font-weight:600;">Register here</a>
        </p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const capsNotice = document.getElementById('capsNotice');

    if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener('click', function() {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            toggleBtn.innerHTML = isPassword 
                ? '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9.88 9.88 4.24 4.24m.02-4.24-4.24 4.24M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61M2 2l20 20"/></svg>'
                : '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
        });
    }

    if (passwordInput && capsNotice) {
        passwordInput.addEventListener('keyup', function(e) {
            if (e.getModifierState && e.getModifierState('CapsLock')) {
                capsNotice.style.display = 'inline';
            } else {
                capsNotice.style.display = 'none';
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
