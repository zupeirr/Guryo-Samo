<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$pageTitle = 'Admin Login';
$activePage = 'login';
$baseUrl = '';

// If already logged in, go straight to the dashboard
if (isLoggedIn()) {
    redirect('admin/dashboard.php');
}

$errorMsg = '';
// Show a friendly message after a successful registration (see register.php)
$successMsg = isset($_GET['registered']) ? 'Account created successfully! You can now log in below.' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1) CSRF check - makes sure the form was submitted from our own site
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errorMsg = 'Your session has expired. Please try logging in again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $errorMsg = 'Please enter both username and password.';
        } else {
            // 2) Look up the account by username. This works for BOTH
            //    admin/staff accounts AND customer accounts, since they
            //    all live in the same "users" table.
            $stmt = $conn->prepare("SELECT id, full_name, username, password, role FROM users WHERE username = ? LIMIT 1");
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            // 3) password_verify() checks the typed password against the
            //    bcrypt hash saved in the database (see functions.php / register.php)
            if ($user && password_verify($password, $user['password'])) {
                // Prevent session fixation attacks by issuing a new session id
                session_regenerate_id(true);

                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
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

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            <div class="field" style="margin-bottom:16px;">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="admin" required autofocus>
            </div>
            <div class="field" style="margin-bottom:20px;">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Log In</button>
        </form>

        <p style="text-align:center; font-size:0.88rem; margin-top:18px; color:var(--ink-soft);">
            Don't have an account? <a href="register.php" style="color:var(--gold-600); font-weight:600;">Register here</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
