<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'Add Agent';
$activeAdminPage = 'add-agent';

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name    = clean($_POST['full_name']    ?? '');
    $email        = clean($_POST['email']        ?? '');
    $phone        = clean($_POST['phone']        ?? '');
    $password     = $_POST['password']           ?? '';
    $specialization = clean($_POST['specialization'] ?? '');
    $commission   = (float)($_POST['commission'] ?? 5.00);

    if (!$full_name) $errors[] = 'Full Name is required.';
    if (!$email)     $errors[] = 'Email is required.';
    if (!$password)  $errors[] = 'Password is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

    if (empty($errors)) {
        $ck = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $ck->bind_param('s', $email);
        $ck->execute();
        if ($ck->get_result()->num_rows > 0) {
            $errors[] = 'An account with this email already exists.';
        } else {
            $conn->begin_transaction();
            try {
                $hash  = password_hash($password, PASSWORD_DEFAULT);
                $uname = strtolower(str_replace(' ', '.', $full_name)) . rand(10,99);
                $ins   = $conn->prepare("INSERT INTO users (full_name, username, email, password, role) VALUES (?, ?, ?, ?, 'customer')");
                $ins->bind_param('ssss', $full_name, $uname, $email, $hash);
                $ins->execute();
                $userId = $conn->insert_id;

                // Update role to match what agents section expects
                $conn->query("UPDATE users SET role='customer' WHERE id=$userId");

                $insAgent = $conn->prepare("INSERT INTO agents (user_id, phone, specialization, commission_rate) VALUES (?, ?, ?, ?)");
                $insAgent->bind_param('issd', $userId, $phone, $specialization, $commission);
                $insAgent->execute();

                $conn->commit();
                $success = 'Agent <strong>' . htmlspecialchars($full_name) . '</strong> has been created successfully!';
                // Clear form
                $_POST = [];
            } catch (Exception $e) {
                $conn->rollback();
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

include 'includes/admin-header.php';
?>

<!-- Page header -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;">
    <div>
        <div style="font-size:0.8rem;color:var(--ink-soft);margin-bottom:4px;">
            <a href="agents.php" style="color:var(--ink-soft);">Agents</a> &rsaquo; Add New Agent
        </div>
        <h1 style="font-size:1.5rem;font-weight:700;color:var(--navy-900);">Add New Agent</h1>
    </div>
    <a href="agents.php" class="btn btn-ghost">← Back to Agents</a>
</div>

<?php if ($success): ?>
<div class="alert-banner success" style="margin-bottom:24px;">
    <span class="alert-icon">✅</span>
    <span><?php echo $success; ?></span>
    <a href="agents.php" style="margin-left:auto;font-weight:600;">View All Agents →</a>
</div>
<?php endif; ?>

<?php if ($errors): ?>
<div class="alert-banner danger" style="margin-bottom:24px;">
    <span class="alert-icon">⚠️</span>
    <div><?php echo implode('<br>', $errors); ?></div>
</div>
<?php endif; ?>

<div class="panel" style="max-width:820px;">
    <div class="panel-head">
        <div>
            <h2>Agent Information</h2>
            <p style="font-size:0.83rem;color:var(--ink-soft);margin-top:3px;">Fill in the details below to create a new agent account.</p>
        </div>
        <span style="font-size:0.78rem;color:var(--ink-soft);">Fields marked <span style="color:var(--danger);">*</span> are required</span>
    </div>
    <div class="panel-body">
        <form method="POST" class="admin-form" id="addAgentForm">

            <!-- Section: Account Details -->
            <div class="form-section-label">👤 Account Details</div>
            <div class="form-grid-2">
                <div class="field-block">
                    <label for="full_name">Full Name <span class="req">*</span></label>
                    <input type="text" id="full_name" name="full_name"
                           placeholder="e.g. Ahmed Hassan"
                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                           autocomplete="name" required>
                </div>
                <div class="field-block">
                    <label for="email">Email Address <span class="req">*</span></label>
                    <input type="email" id="email" name="email"
                           placeholder="agent@example.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           autocomplete="email" required>
                </div>
                <div class="field-block">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone"
                           placeholder="+252 61 XXX XXXX"
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
                <div class="field-block">
                    <label for="password">Password <span class="req">*</span></label>
                    <div class="input-icon-wrap">
                        <input type="password" id="password" name="password"
                               placeholder="Min. 6 characters"
                               autocomplete="new-password" required>
                        <button type="button" class="pw-toggle" onclick="togglePw()" title="Show/hide password">👁</button>
                    </div>
                </div>
            </div>

            <div class="form-divider"></div>

            <!-- Section: Agent Details -->
            <div class="form-section-label">🏢 Agent Details</div>
            <div class="form-grid-2">
                <div class="field-block">
                    <label for="specialization">Specialization</label>
                    <select id="specialization" name="specialization">
                        <option value="" <?php echo empty($_POST['specialization']) ? 'selected' : ''; ?>>— Select specialty —</option>
                        <option value="Residential"  <?php echo ($_POST['specialization'] ?? '') === 'Residential'  ? 'selected' : ''; ?>>Residential</option>
                        <option value="Commercial"   <?php echo ($_POST['specialization'] ?? '') === 'Commercial'   ? 'selected' : ''; ?>>Commercial</option>
                        <option value="Luxury"       <?php echo ($_POST['specialization'] ?? '') === 'Luxury'       ? 'selected' : ''; ?>>Luxury</option>
                        <option value="Land"         <?php echo ($_POST['specialization'] ?? '') === 'Land'         ? 'selected' : ''; ?>>Land</option>
                        <option value="Rental"       <?php echo ($_POST['specialization'] ?? '') === 'Rental'       ? 'selected' : ''; ?>>Rental</option>
                        <option value="Investment"   <?php echo ($_POST['specialization'] ?? '') === 'Investment'   ? 'selected' : ''; ?>>Investment</option>
                    </select>
                </div>
                <div class="field-block">
                    <label for="commission">Commission Rate <span class="req">*</span></label>
                    <div class="input-suffix-wrap">
                        <input type="number" id="commission" name="commission"
                               step="0.01" min="0" max="100"
                               value="<?php echo htmlspecialchars($_POST['commission'] ?? '5.00'); ?>"
                               placeholder="5.00">
                        <span class="input-suffix">%</span>
                    </div>
                    <span class="field-hint">Typical range: 2% – 10%</span>
                </div>
            </div>

            <div class="form-divider"></div>

            <!-- Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                    <span>✚</span> Create Agent
                </button>
                <a href="agents.php" class="btn btn-ghost btn-lg">Cancel</a>
            </div>

        </form>
    </div>
</div>

<script>
function togglePw() {
    const inp = document.getElementById('password');
    inp.type = inp.type === 'password' ? 'text' : 'password';
}
</script>

<?php include 'includes/admin-footer.php'; ?>
