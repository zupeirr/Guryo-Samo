<?php
/**
 * register.php
 * ---------------------------------------------------------------
 * Public sign-up page. Anyone can create a "customer" account here.
 * Customer accounts can log in (login.php) but cannot open the
 * Admin Dashboard - only accounts with role 'admin' or 'staff' can
 * (that check happens in includes/functions.php -> requireLogin()).
 */

require_once 'config/db.php';
require_once 'includes/functions.php';

$pageTitle = 'Register';
$activePage = 'register';
$baseUrl = '';

// If the person is already logged in, there's no need to register again
if (isLoggedIn()) {
    redirect('index.php');
}

$errorMsg = '';
$formData = ['full_name' => '', 'username' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1) CSRF check - confirms the form came from our own site
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errorMsg = 'Your session has expired. Please try submitting the form again.';
    } else {
        $formData['full_name'] = trim($_POST['full_name'] ?? '');
        $formData['username']  = trim($_POST['username'] ?? '');
        $formData['email']     = trim($_POST['email'] ?? '');
        $password              = $_POST['password'] ?? '';
        $confirmPassword       = $_POST['confirm_password'] ?? '';

        // 2) Basic validation
        if ($formData['full_name'] === '' || $formData['username'] === '' || $formData['email'] === '' || $password === '') {
            $errorMsg = 'Please fill in all the required fields.';
        } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $errorMsg = 'Please enter a valid email address.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $formData['username'])) {
            $errorMsg = 'Username must be 3-50 characters and contain only letters, numbers, or underscores.';
        } elseif ($password !== $confirmPassword) {
            $errorMsg = 'Passwords do not match.';
        } else {
            // 3) Make sure the username / email are not already taken
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
            $checkStmt->bind_param('ss', $formData['username'], $formData['email']);
            $checkStmt->execute();

            if ($checkStmt->get_result()->num_rows > 0) {
                $errorMsg = 'That username or email is already registered. Please log in instead.';
            } else {
                // 4) Hash the password (NEVER store plain-text passwords)
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                // 5) Insert the new account with role = 'customer'
                $insertStmt = $conn->prepare(
                    "INSERT INTO users (full_name, username, email, password, role) VALUES (?, ?, ?, ?, 'customer')"
                );
                $insertStmt->bind_param(
                    'ssss',
                    $formData['full_name'], $formData['username'], $formData['email'], $hashedPassword
                );

                if ($insertStmt->execute()) {
                    // Send the new user to the login page with a success message
                    redirect('login.php?registered=1');
                } else {
                    $errorMsg = 'Something went wrong while creating your account. Please try again.';
                }
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="mark">GS</div>
        <h2 style="margin-bottom:6px;">Create an Account</h2>
        <p style="color:var(--ink-soft); font-size:0.9rem; margin-bottom:24px;">Register to save your details and get in touch with our team faster.</p>

        <?php if ($errorMsg): ?><div class="alert alert-error"><?php echo clean($errorMsg); ?></div><?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">

            <div class="field" style="margin-bottom:16px;">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo clean($formData['full_name']); ?>" placeholder="Your full name" required>
            </div>
            <div class="field" style="margin-bottom:16px;">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo clean($formData['username']); ?>" placeholder="Choose a username" required>
            </div>
            <div class="field" style="margin-bottom:16px;">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo clean($formData['email']); ?>" placeholder="you@example.com" required>
            </div>
            <div class="field" style="margin-bottom:16px;">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter a password" required>
            </div>
            <div class="field" style="margin-bottom:20px;">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Create Account</button>
        </form>

        <p style="text-align:center; font-size:0.88rem; margin-top:18px; color:var(--ink-soft);">
            Already have an account? <a href="login.php" style="color:var(--gold-600); font-weight:600;">Log in here</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
