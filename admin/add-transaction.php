<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'Add Transaction';
$activeAdminPage = 'transactions';

$properties = $conn->query("SELECT id, title, price FROM properties ORDER BY title ASC");
$agents     = $conn->query("SELECT a.id, a.commission_rate, u.full_name FROM agents a JOIN users u ON u.id = a.user_id ORDER BY u.full_name ASC");

$errors = []; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $propertyId   = (int)($_POST['property_id']   ?? 0);
    $agentId      = (int)($_POST['agent_id']      ?? 0);
    $customerName = clean($_POST['customer_name'] ?? '');
    $type         = clean($_POST['type']          ?? 'Sale');
    $amount       = (float)($_POST['amount']      ?? 0);
    $commission   = (float)($_POST['commission']  ?? 0);
    $date         = clean($_POST['transaction_date'] ?? date('Y-m-d'));
    $status       = clean($_POST['status']        ?? 'Pending');
    $notes        = clean($_POST['notes']         ?? '');

    if (!$customerName)  $errors[] = 'Customer name is required.';
    if ($amount <= 0)    $errors[] = 'Amount must be greater than 0.';
    if (!in_array($type, ['Sale','Rental'])) $errors[] = 'Invalid type.';
    if (!in_array($status, ['Pending','Completed','Cancelled'])) $errors[] = 'Invalid status.';

    if (empty($errors)) {
        $ins = $conn->prepare(
            "INSERT INTO transactions (property_id, agent_id, customer_name, type, amount, commission, transaction_date, status, notes)
             VALUES (?,?,?,?,?,?,?,?,?)"
        );
        $ins->bind_param('iissddsss',
            $propertyId, $agentId, $customerName, $type,
            $amount, $commission, $date, $status, $notes
        );
        $ins->execute();
        $success = 'Transaction recorded successfully!';
        $_POST = [];
    }
}

include 'includes/admin-header.php';
?>

<!-- Page header -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;">
    <div>
        <div style="font-size:0.8rem;color:var(--ink-soft);margin-bottom:4px;">
            <a href="transactions.php" style="color:var(--ink-soft);">Transactions</a> &rsaquo; Add New
        </div>
        <h1 style="font-size:1.5rem;font-weight:700;color:var(--navy-900);">Add Transaction</h1>
    </div>
    <a href="transactions.php" class="btn btn-ghost">← Back</a>
</div>

<?php if ($success): ?>
<div class="alert-banner success" style="margin-bottom:24px;">
    <span class="alert-icon">✅</span>
    <span><?php echo $success; ?></span>
    <a href="transactions.php" style="margin-left:auto;font-weight:600;">View All Transactions →</a>
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
            <h2>Transaction Details</h2>
            <p style="font-size:0.83rem;color:var(--ink-soft);margin-top:3px;">Record a sale, rental or payment transaction.</p>
        </div>
        <span style="font-size:0.78rem;color:var(--ink-soft);">Fields marked <span style="color:var(--danger);">*</span> are required</span>
    </div>
    <div class="panel-body">
        <form method="POST" class="admin-form" id="txForm">

            <div class="form-section-label">🏠 Property & Agent</div>
            <div class="form-grid-2">
                <div class="field-block">
                    <label for="property_id">Property</label>
                    <select name="property_id" id="propertySelect">
                        <option value="0">— Select Property (optional) —</option>
                        <?php while ($p = $properties->fetch_assoc()): ?>
                        <option value="<?php echo $p['id']; ?>"
                                data-price="<?php echo $p['price']; ?>"
                                <?php echo ((int)($_POST['property_id'] ?? 0) === $p['id']) ? 'selected' : ''; ?>>
                            <?php echo clean($p['title']); ?> (<?php echo formatPrice($p['price']); ?>)
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="field-block">
                    <label for="agent_id">Assigned Agent</label>
                    <select name="agent_id" id="agentSelect">
                        <option value="0">— Select Agent (optional) —</option>
                        <?php while ($ag = $agents->fetch_assoc()): ?>
                        <option value="<?php echo $ag['id']; ?>"
                                data-rate="<?php echo $ag['commission_rate']; ?>"
                                <?php echo ((int)($_POST['agent_id'] ?? 0) === $ag['id']) ? 'selected' : ''; ?>>
                            <?php echo clean($ag['full_name']); ?> (<?php echo $ag['commission_rate']; ?>%)
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="form-divider"></div>

            <div class="form-section-label">👤 Customer & Type</div>
            <div class="form-grid-2">
                <div class="field-block">
                    <label for="customer_name">Customer Name <span class="req">*</span></label>
                    <input type="text" id="customer_name" name="customer_name"
                           placeholder="e.g. Mohamed Ali"
                           value="<?php echo htmlspecialchars($_POST['customer_name'] ?? ''); ?>" required>
                </div>
                <div class="field-block">
                    <label for="type">Transaction Type <span class="req">*</span></label>
                    <select name="type" id="typeSelect">
                        <option value="Sale"   <?php echo ($_POST['type'] ?? '') === 'Sale'   ? 'selected' : ''; ?>>Sale</option>
                        <option value="Rental" <?php echo ($_POST['type'] ?? '') === 'Rental' ? 'selected' : ''; ?>>Rental</option>
                    </select>
                </div>
            </div>

            <div class="form-divider"></div>

            <div class="form-section-label">💰 Financials</div>
            <div class="form-grid-2">
                <div class="field-block">
                    <label for="amountInput">Amount (USD) <span class="req">*</span></label>
                    <div class="input-suffix-wrap">
                        <input type="number" id="amountInput" name="amount"
                               step="0.01" min="0" placeholder="0.00"
                               value="<?php echo htmlspecialchars($_POST['amount'] ?? ''); ?>" required>
                        <span class="input-suffix">$</span>
                    </div>
                </div>
                <div class="field-block">
                    <label for="commInput">Commission Amount (USD)</label>
                    <div class="input-suffix-wrap">
                        <input type="number" id="commInput" name="commission"
                               step="0.01" min="0" placeholder="Auto-calculated"
                               value="<?php echo htmlspecialchars($_POST['commission'] ?? ''); ?>">
                        <span class="input-suffix">$</span>
                    </div>
                    <span class="field-hint">Auto-fills based on agent's commission rate.</span>
                </div>
                <div class="field-block">
                    <label for="transaction_date">Transaction Date <span class="req">*</span></label>
                    <input type="date" id="transaction_date" name="transaction_date"
                           value="<?php echo htmlspecialchars($_POST['transaction_date'] ?? date('Y-m-d')); ?>" required>
                </div>
                <div class="field-block">
                    <label for="status">Status <span class="req">*</span></label>
                    <select name="status" id="status">
                        <?php foreach (['Pending','Completed','Cancelled'] as $s): ?>
                        <option value="<?php echo $s; ?>" <?php echo ($_POST['status'] ?? 'Pending') === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-divider"></div>

            <div class="field-block" style="margin-bottom:0;">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="3"
                          placeholder="Optional notes about this transaction…"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
            </div>

            <div class="form-divider"></div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">💾 Save Transaction</button>
                <a href="transactions.php" class="btn btn-ghost btn-lg">Cancel</a>
            </div>

        </form>
    </div>
</div>

<script>
(function(){
    var propSel   = document.getElementById('propertySelect');
    var agentSel  = document.getElementById('agentSelect');
    var amtInput  = document.getElementById('amountInput');
    var commInput = document.getElementById('commInput');

    function updateComm() {
        var price = parseFloat(propSel.options[propSel.selectedIndex]?.dataset?.price || 0);
        var rate  = parseFloat(agentSel.options[agentSel.selectedIndex]?.dataset?.rate || 0);
        // Auto-fill amount from property price if blank
        if (propSel.value !== '0' && amtInput.value === '') {
            amtInput.value = price.toFixed(2);
        }
        // Calculate commission
        var base = parseFloat(amtInput.value) || 0;
        if (rate > 0) commInput.value = (base * rate / 100).toFixed(2);
    }

    propSel.addEventListener('change', updateComm);
    agentSel.addEventListener('change', updateComm);
    amtInput.addEventListener('input', updateComm);
})();
</script>

<?php include 'includes/admin-footer.php'; ?>
