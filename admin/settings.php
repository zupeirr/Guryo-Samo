<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'Settings';
$activeAdminPage = 'settings';

$errors  = [];
$success = '';

// ── Helper: upsert one setting ────────────────────────────────────────────
function ss($conn, $key, $value) {
    $s = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?");
    $s->bind_param('sss', $key, $value, $value);
    $s->execute();
}

$activeTab = clean($_GET['tab'] ?? 'general');
$allowed_tabs = ['general','property','notifications','system'];
if (!in_array($activeTab, $allowed_tabs)) $activeTab = 'general';

// ── POST handler ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tab = clean($_POST['_tab'] ?? 'general');

    if ($tab === 'general') {
        ss($conn, 'site_name',     trim($_POST['site_name']     ?? 'Guryo Samo'));
        ss($conn, 'site_tagline',  trim($_POST['site_tagline']  ?? ''));
        ss($conn, 'contact_email', trim($_POST['contact_email'] ?? ''));
        ss($conn, 'contact_phone', trim($_POST['contact_phone'] ?? ''));
        ss($conn, 'currency',      trim($_POST['currency']      ?? 'USD'));
        ss($conn, 'address',       trim($_POST['address']       ?? ''));
        ss($conn, 'social_facebook', trim($_POST['social_facebook'] ?? ''));
        ss($conn, 'social_whatsapp', trim($_POST['social_whatsapp'] ?? ''));
        ss($conn, 'social_tiktok',   trim($_POST['social_tiktok']   ?? ''));
        $success = '✅ General settings saved.';
        $activeTab = 'general';

    } elseif ($tab === 'property') {
        $ppp = max(1, (int)($_POST['properties_per_page'] ?? 12));
        ss($conn, 'properties_per_page',    $ppp);
        ss($conn, 'default_property_status',trim($_POST['default_property_status'] ?? 'For Sale'));
        ss($conn, 'allow_guest_inquiries',  in_array($_POST['allow_guest_inquiries'] ?? '', ['1']) ? '1' : '0');
        ss($conn, 'show_price_on_card',     in_array($_POST['show_price_on_card']    ?? '', ['1']) ? '1' : '0');
        $success = '✅ Property settings saved.';
        $activeTab = 'property';

    } elseif ($tab === 'notifications') {
        ss($conn, 'email_notifications',   in_array($_POST['email_notifications']   ?? '', ['1']) ? '1' : '0');
        ss($conn, 'notification_email',    trim($_POST['notification_email']        ?? ''));
        ss($conn, 'notify_new_inquiry',    in_array($_POST['notify_new_inquiry']    ?? '', ['1']) ? '1' : '0');
        ss($conn, 'notify_new_user',       in_array($_POST['notify_new_user']       ?? '', ['1']) ? '1' : '0');
        ss($conn, 'notify_new_appt',       in_array($_POST['notify_new_appt']       ?? '', ['1']) ? '1' : '0');
        
        // SMTP Settings
        ss($conn, 'smtp_host',             trim($_POST['smtp_host']       ?? ''));
        ss($conn, 'smtp_port',             trim($_POST['smtp_port']       ?? '587'));
        ss($conn, 'smtp_username',         trim($_POST['smtp_username']   ?? ''));
        if (!empty($_POST['smtp_password'])) {
            ss($conn, 'smtp_password',     trim($_POST['smtp_password']));
        }
        ss($conn, 'smtp_encryption',       trim($_POST['smtp_encryption'] ?? 'tls'));

        $success = '✅ Notification and SMTP settings saved.';
        $activeTab = 'notifications';

    } elseif ($tab === 'system') {
        $app = max(1, (int)($_POST['admin_per_page'] ?? 20));
        ss($conn, 'admin_per_page',    $app);
        ss($conn, 'maintenance_mode',  in_array($_POST['maintenance_mode'] ?? '', ['1']) ? '1' : '0');
        ss($conn, 'debug_mode',        in_array($_POST['debug_mode']       ?? '', ['1']) ? '1' : '0');
        ss($conn, 'timezone',          trim($_POST['timezone']             ?? 'UTC'));
        ss($conn, 'date_format',       trim($_POST['date_format']          ?? 'M j, Y'));
        $success = '✅ System settings saved.';
        $activeTab = 'system';
    }
}

include 'includes/admin-header.php';
?>

<!-- Page Header -->
<div style="margin-bottom:28px;">
    <h1 style="font-size:1.5rem;font-weight:700;color:var(--navy-900);">⚙️ Settings</h1>
    <p style="font-size:0.88rem;color:var(--ink-soft);margin-top:4px;">Manage your platform configuration, branding, and system preferences.</p>
</div>

<?php if ($success): ?>
<div class="alert-banner success" style="margin-bottom:24px;">
    <span class="alert-icon">✅</span>
    <span><?php echo $success; ?></span>
</div>
<?php endif; ?>
<?php if ($errors): ?>
<div class="alert-banner danger" style="margin-bottom:24px;">
    <span class="alert-icon">⚠️</span>
    <div><?php echo implode('<br>', $errors); ?></div>
</div>
<?php endif; ?>

<!-- Tab Navigation -->
<div class="settings-tabs">
    <?php
    $tabs = [
        'general'       => ['icon'=>'🏷️',  'label'=>'General'],
        'property'      => ['icon'=>'🏢',  'label'=>'Property'],
        'notifications' => ['icon'=>'🔔',  'label'=>'Notifications'],
        'system'        => ['icon'=>'🖥️',  'label'=>'System'],
    ];
    foreach ($tabs as $slug => $t): ?>
    <a href="?tab=<?php echo $slug; ?>"
       class="settings-tab-btn <?php echo $activeTab === $slug ? 'active' : ''; ?>">
        <span><?php echo $t['icon']; ?></span>
        <?php echo $t['label']; ?>
    </a>
    <?php endforeach; ?>
</div>

<div class="panel">
    <div class="panel-body">
        <form method="POST" class="admin-form">
            <input type="hidden" name="_tab" value="<?php echo $activeTab; ?>">

            <?php /* ══════════════════ GENERAL TAB ══════════════════ */ ?>
            <?php if ($activeTab === 'general'): ?>

            <div class="form-section-label">🏷️ Branding & Identity</div>
            <div class="form-grid-2">
                <div class="field-block">
                    <label for="site_name">Site Name</label>
                    <input type="text" id="site_name" name="site_name"
                           value="<?php echo htmlspecialchars(getSetting($conn,'site_name','Guryo Samo')); ?>"
                           placeholder="Guryo Samo">
                </div>
                <div class="field-block">
                    <label for="site_tagline">Site Tagline</label>
                    <input type="text" id="site_tagline" name="site_tagline"
                           value="<?php echo htmlspecialchars(getSetting($conn,'site_tagline','Find Your Dream Home')); ?>"
                           placeholder="Find Your Dream Home">
                </div>
            </div>

            <div class="form-divider"></div>
            <div class="form-section-label">📞 Contact Information</div>
            <div class="form-grid-2">
                <div class="field-block">
                    <label for="contact_email">Contact Email</label>
                    <input type="email" id="contact_email" name="contact_email"
                           value="<?php echo htmlspecialchars(getSetting($conn,'contact_email','')); ?>"
                           placeholder="info@example.com">
                </div>
                <div class="field-block">
                    <label for="contact_phone">Contact Phone</label>
                    <input type="tel" id="contact_phone" name="contact_phone"
                           value="<?php echo htmlspecialchars(getSetting($conn,'contact_phone','')); ?>"
                           placeholder="+252 61 XXX XXXX">
                </div>
                <div class="field-block">
                    <label for="currency">Currency Symbol</label>
                    <input type="text" id="currency" name="currency"
                           value="<?php echo htmlspecialchars(getSetting($conn,'currency','USD')); ?>"
                           placeholder="USD" style="max-width:120px;">
                    <span class="field-hint">Used across pricing displays</span>
                </div>
                <div class="field-block">
                    <label for="address">Business Address</label>
                    <input type="text" id="address" name="address"
                           value="<?php echo htmlspecialchars(getSetting($conn,'address','')); ?>"
                           placeholder="Hargeisa, Somalia">
                </div>
            </div>

            <div class="form-divider"></div>
            <div class="form-section-label">🌐 Social Media Links</div>
            <div class="form-grid-2">
                <div class="field-block">
                    <label for="social_facebook">Facebook URL</label>
                    <input type="url" id="social_facebook" name="social_facebook"
                           value="<?php echo htmlspecialchars(getSetting($conn,'social_facebook','')); ?>"
                           placeholder="https://facebook.com/yourpage">
                </div>
                <div class="field-block">
                    <label for="social_whatsapp">WhatsApp URL</label>
                    <input type="url" id="social_whatsapp" name="social_whatsapp"
                           value="<?php echo htmlspecialchars(getSetting($conn,'social_whatsapp','')); ?>"
                           placeholder="https://wa.me/252634567890">
                </div>
                <div class="field-block">
                    <label for="social_tiktok">TikTok URL</label>
                    <input type="url" id="social_tiktok" name="social_tiktok"
                           value="<?php echo htmlspecialchars(getSetting($conn,'social_tiktok','')); ?>"
                           placeholder="https://tiktok.com/@yourprofile">
                </div>
            </div>

            <?php /* ══════════════════ PROPERTY TAB ══════════════════ */ ?>
            <?php elseif ($activeTab === 'property'): ?>

            <div class="form-section-label">📋 Listing Defaults</div>
            <div class="form-grid-2">
                <div class="field-block">
                    <label for="properties_per_page">Properties Per Page (Public)</label>
                    <input type="number" id="properties_per_page" name="properties_per_page"
                           min="3" max="60" style="max-width:120px;"
                           value="<?php echo (int)getSetting($conn,'properties_per_page','12'); ?>">
                    <span class="field-hint">Number of listings on the public search page</span>
                </div>
                <div class="field-block">
                    <label for="default_property_status">Default New Property Status</label>
                    <select id="default_property_status" name="default_property_status" style="max-width:220px;">
                        <?php foreach (['For Sale','For Rent','Pending'] as $s): ?>
                        <option value="<?php echo $s; ?>" <?php echo getSetting($conn,'default_property_status','For Sale') === $s ? 'selected' : ''; ?>>
                            <?php echo $s; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="field-hint">Applied when adding a new property</span>
                </div>
            </div>

            <div class="form-divider"></div>
            <div class="form-section-label">⚙️ Display Options</div>
            <div style="display:flex;flex-direction:column;gap:16px;">
                <label class="toggle-row">
                    <div>
                        <strong>Allow Guest Inquiries</strong>
                        <div style="font-size:0.82rem;color:var(--ink-soft);">Let visitors submit property inquiries without logging in.</div>
                    </div>
                    <input type="hidden" name="allow_guest_inquiries" value="0">
                    <label class="toggle-switch">
                        <input type="checkbox" name="allow_guest_inquiries" value="1" <?php echo getSetting($conn,'allow_guest_inquiries','1') === '1' ? 'checked' : ''; ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </label>
                <label class="toggle-row">
                    <div>
                        <strong>Show Price on Property Cards</strong>
                        <div style="font-size:0.82rem;color:var(--ink-soft);">Display price on listing cards in search results.</div>
                    </div>
                    <input type="hidden" name="show_price_on_card" value="0">
                    <label class="toggle-switch">
                        <input type="checkbox" name="show_price_on_card" value="1" <?php echo getSetting($conn,'show_price_on_card','1') === '1' ? 'checked' : ''; ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </label>
            </div>

            <?php /* ══════════════════ NOTIFICATIONS TAB ══════════════════ */ ?>
            <?php elseif ($activeTab === 'notifications'): ?>

            <div class="form-section-label">✉️ Email & SMTP Configuration</div>
            <div class="form-grid-2">
                <div class="field-block">
                    <label for="notification_email">Notification Recipient Email</label>
                    <input type="email" id="notification_email" name="notification_email"
                           value="<?php echo htmlspecialchars(getSetting($conn,'notification_email','')); ?>"
                           placeholder="admin@example.com">
                    <span class="field-hint">Where admin alerts are sent</span>
                </div>
            </div>

            <div class="form-grid-2" style="margin-top:16px;">
                <div class="field-block">
                    <label for="smtp_host">SMTP Host</label>
                    <input type="text" id="smtp_host" name="smtp_host"
                           value="<?php echo htmlspecialchars(getSetting($conn,'smtp_host','')); ?>"
                           placeholder="smtp.mailtrap.io">
                </div>
                <div class="field-block">
                    <label for="smtp_port">SMTP Port</label>
                    <input type="text" id="smtp_port" name="smtp_port"
                           value="<?php echo htmlspecialchars(getSetting($conn,'smtp_port','587')); ?>"
                           placeholder="587">
                </div>
            </div>

            <div class="form-grid-2" style="margin-top:16px;">
                <div class="field-block">
                    <label for="smtp_username">SMTP Username</label>
                    <input type="text" id="smtp_username" name="smtp_username"
                           value="<?php echo htmlspecialchars(getSetting($conn,'smtp_username','')); ?>">
                </div>
                <div class="field-block">
                    <label for="smtp_password">SMTP Password</label>
                    <input type="password" id="smtp_password" name="smtp_password"
                           placeholder="Leave blank to keep current password">
                    <span class="field-hint">Your SMTP password or App Password</span>
                </div>
            </div>

            <div class="form-grid-2" style="margin-top:16px;">
                <div class="field-block">
                    <label for="smtp_encryption">Encryption</label>
                    <select id="smtp_encryption" name="smtp_encryption">
                        <option value="tls" <?php echo getSetting($conn,'smtp_encryption','tls') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                        <option value="ssl" <?php echo getSetting($conn,'smtp_encryption','') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                        <option value="" <?php echo getSetting($conn,'smtp_encryption','') === '' ? 'selected' : ''; ?>>None</option>
                    </select>
                </div>
            </div>

            <div class="form-divider"></div>
            <div class="form-section-label">🔔 Notification Triggers</div>
            <div style="display:flex;flex-direction:column;gap:16px;">
                <label class="toggle-row">
                    <div>
                        <strong>Email Notifications (Master Switch)</strong>
                        <div style="font-size:0.82rem;color:var(--ink-soft);">Enable or disable all email notifications globally.</div>
                    </div>
                    <input type="hidden" name="email_notifications" value="0">
                    <label class="toggle-switch">
                        <input type="checkbox" name="email_notifications" value="1" <?php echo getSetting($conn,'email_notifications','1') === '1' ? 'checked' : ''; ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </label>
                <label class="toggle-row">
                    <div>
                        <strong>Notify on New Inquiry</strong>
                        <div style="font-size:0.82rem;color:var(--ink-soft);">Send an alert when a visitor submits a contact form or property inquiry.</div>
                    </div>
                    <input type="hidden" name="notify_new_inquiry" value="0">
                    <label class="toggle-switch">
                        <input type="checkbox" name="notify_new_inquiry" value="1" <?php echo getSetting($conn,'notify_new_inquiry','1') === '1' ? 'checked' : ''; ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </label>
                <label class="toggle-row">
                    <div>
                        <strong>Notify on New User Registration</strong>
                        <div style="font-size:0.82rem;color:var(--ink-soft);">Alert when a new customer account is created.</div>
                    </div>
                    <input type="hidden" name="notify_new_user" value="0">
                    <label class="toggle-switch">
                        <input type="checkbox" name="notify_new_user" value="1" <?php echo getSetting($conn,'notify_new_user','1') === '1' ? 'checked' : ''; ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </label>
                <label class="toggle-row">
                    <div>
                        <strong>Notify on New Appointment</strong>
                        <div style="font-size:0.82rem;color:var(--ink-soft);">Alert when a viewing or consultation is booked.</div>
                    </div>
                    <input type="hidden" name="notify_new_appt" value="0">
                    <label class="toggle-switch">
                        <input type="checkbox" name="notify_new_appt" value="1" <?php echo getSetting($conn,'notify_new_appt','1') === '1' ? 'checked' : ''; ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </label>
            </div>

            <?php /* ══════════════════ SYSTEM TAB ══════════════════ */ ?>
            <?php elseif ($activeTab === 'system'): ?>

            <div class="form-section-label">🖥️ Admin Panel</div>
            <div class="form-grid-2">
                <div class="field-block">
                    <label for="admin_per_page">Admin Items Per Page</label>
                    <input type="number" id="admin_per_page" name="admin_per_page"
                           min="5" max="200" style="max-width:120px;"
                           value="<?php echo (int)getSetting($conn,'admin_per_page','20'); ?>">
                    <span class="field-hint">Rows shown in admin data tables</span>
                </div>
                <div class="field-block">
                    <label for="timezone">Timezone</label>
                    <select id="timezone" name="timezone">
                        <?php
                        $tzList = ['UTC','Africa/Nairobi','Africa/Djibouti','Asia/Riyadh','Europe/London','America/New_York'];
                        $curTz  = getSetting($conn,'timezone','UTC');
                        foreach ($tzList as $tz): ?>
                        <option value="<?php echo $tz; ?>" <?php echo $curTz === $tz ? 'selected' : ''; ?>><?php echo $tz; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field-block">
                    <label for="date_format">Date Display Format</label>
                    <select id="date_format" name="date_format">
                        <?php
                        $fmts = ['M j, Y' => 'Aug 12, 2026', 'd/m/Y' => '12/08/2026', 'Y-m-d' => '2026-08-12', 'F j, Y' => 'August 12, 2026'];
                        $curFmt = getSetting($conn,'date_format','M j, Y');
                        foreach ($fmts as $fmt => $preview): ?>
                        <option value="<?php echo $fmt; ?>" <?php echo $curFmt === $fmt ? 'selected' : ''; ?>><?php echo $preview; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-divider"></div>
            <div class="form-section-label">🔧 Advanced</div>
            <div style="display:flex;flex-direction:column;gap:16px;">
                <label class="toggle-row">
                    <div>
                        <strong>Maintenance Mode</strong>
                        <div style="font-size:0.82rem;color:var(--ink-soft);">Take the public site offline for maintenance. The admin dashboard stays accessible.</div>
                    </div>
                    <input type="hidden" name="maintenance_mode" value="0">
                    <label class="toggle-switch">
                        <input type="checkbox" name="maintenance_mode" value="1" <?php echo getSetting($conn,'maintenance_mode','0') === '1' ? 'checked' : ''; ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </label>
                <label class="toggle-row">
                    <div>
                        <strong>Debug Mode</strong>
                        <div style="font-size:0.82rem;color:var(--ink-soft);">Show detailed PHP errors. <strong style="color:var(--danger);">Disable on production!</strong></div>
                    </div>
                    <input type="hidden" name="debug_mode" value="0">
                    <label class="toggle-switch">
                        <input type="checkbox" name="debug_mode" value="1" <?php echo getSetting($conn,'debug_mode','0') === '1' ? 'checked' : ''; ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </label>
            </div>

            <?php endif; ?>

            <div class="form-divider"></div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">💾 Save Settings</button>
                <a href="dashboard.php" class="btn btn-ghost btn-lg">← Dashboard</a>
            </div>
        </form>
    </div>
</div>

<style>
/* Settings-specific tab nav */
.settings-tabs {
    display: flex; gap: 6px; margin-bottom: 24px; flex-wrap: wrap;
}
.settings-tab-btn {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 10px 20px; border-radius: 8px; font-weight: 600;
    font-size: 0.88rem; text-decoration: none;
    border: 1.5px solid var(--border); color: var(--navy-800);
    background: #fff; transition: all 0.18s;
}
.settings-tab-btn:hover { border-color: var(--navy-800); background: var(--navy-800); color: #fff; }
.settings-tab-btn.active { background: var(--navy-800); border-color: var(--navy-800); color: #fff; }

/* Toggle switch */
.toggle-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 18px; background: #fafaf8; border: 1px solid var(--border);
    border-radius: 10px; cursor: pointer; gap: 20px;
}
.toggle-switch { position: relative; display: inline-block; width: 48px; height: 27px; flex-shrink: 0; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider {
    position: absolute; inset: 0; background: #ccc; border-radius: 27px;
    transition: background 0.2s; cursor: pointer;
}
.toggle-slider::before {
    content: ''; position: absolute; left: 3px; top: 3px;
    width: 21px; height: 21px; background: #fff; border-radius: 50%;
    transition: transform 0.2s; box-shadow: 0 1px 4px rgba(0,0,0,0.2);
}
.toggle-switch input:checked + .toggle-slider { background: var(--success, #22c55e); }
.toggle-switch input:checked + .toggle-slider::before { transform: translateX(21px); }
</style>

<?php include 'includes/admin-footer.php'; ?>
