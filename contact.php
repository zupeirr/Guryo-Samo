<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$pageTitle = 'Contact Us';
$activePage = 'contact';
$baseUrl = '';

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errorMsg = 'Your session has expired. Please try submitting the form again.';
    } else {
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($name === '' || $email === '' || $message === '') {
            $errorMsg = 'Please fill in your name, email, and message.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMsg = 'Please enter a valid email address.';
        } else {
            $stmt = $conn->prepare("INSERT INTO messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('sssss', $name, $email, $phone, $subject, $message);
            if ($stmt->execute()) {
                $successMsg = 'Thank you! Your message has been sent. Our team will get back to you soon.';
                
                // Send email notification if enabled
                $emailEnabled = getSetting($conn, 'email_notifications', '0');
                $notifyInquiry = getSetting($conn, 'notify_new_inquiry', '0');
                $adminEmail = getSetting($conn, 'notification_email', '');
                
                if ($emailEnabled === '1' && $notifyInquiry === '1' && !empty($adminEmail)) {
                    require_once 'includes/PHPMailer/Exception.php';
                    require_once 'includes/PHPMailer/PHPMailer.php';
                    require_once 'includes/PHPMailer/SMTP.php';
                    
                    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host       = getSetting($conn, 'smtp_host', '');
                        $mail->SMTPAuth   = true;
                        $mail->Username   = getSetting($conn, 'smtp_username', '');
                        $mail->Password   = getSetting($conn, 'smtp_password', '');
                        
                        $encryption = getSetting($conn, 'smtp_encryption', 'tls');
                        if ($encryption === 'tls') {
                            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                        } elseif ($encryption === 'ssl') {
                            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                        }
                        
                        $mail->Port = (int)getSetting($conn, 'smtp_port', '587');
                        
                        $siteName = getSetting($conn, 'site_name', 'Guryo Samo');
                        $mail->setFrom($mail->Username, $siteName . ' System');
                        $mail->addAddress($adminEmail);
                        
                        $mail->isHTML(true);
                        $mail->Subject = 'New Inquiry: ' . ($subject ? $subject : 'No Subject');
                        
                        $body = "<h2>New Property Inquiry Received</h2>";
                        $body .= "<p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>";
                        $body .= "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
                        $body .= "<p><strong>Phone:</strong> " . htmlspecialchars($phone) . "</p>";
                        $body .= "<p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>";
                        
                        $mail->Body = $body;
                        $mail->send();
                    } catch (Exception $e) {
                        // Silently fail if email doesn't send, we don't want to show users a white screen
                        error_log('PHPMailer Error: ' . $mail->ErrorInfo);
                    }
                }

            } else {
                $errorMsg = 'Something went wrong while sending your message. Please try again.';
            }
        }
    }
}

include 'includes/header.php';
?>

<section class="hero" style="padding:80px 0;">
    <div class="container">
        <div class="hero-content">
            <span class="eyebrow" style="color:#e9c874;">Contact</span>
            <h1 style="font-size:2.2rem;">We'd love to hear from you.</h1>
            <p>Have a question about a property, or want to list your own? Send us a message below.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="contact-grid">
            <div>
                <span class="eyebrow">Get In Touch</span>
                <h2 class="section-title">Contact Information</h2>

                <div class="contact-info-item">
                    <div class="icon-box" style="color:var(--gold-600);"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg></div>
                    <div>
                        <h4 style="font-size:0.98rem;">Office</h4>
                        <p style="color:var(--ink-soft); font-size:0.9rem;"><?php echo clean(getSetting($conn, 'address', 'Hargeisa, Somaliland')); ?></p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="icon-box" style="color:var(--gold-600);"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg></div>
                    <div>
                        <h4 style="font-size:0.98rem;">Call us</h4>
                        <p style="color:var(--ink-soft); font-size:0.9rem;"><?php echo clean(getSetting($conn, 'contact_phone', '+252 63 6097266')); ?></p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="icon-box" style="color:var(--gold-600);"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg></div>
                    <div>
                        <h4 style="font-size:0.98rem;">Email us</h4>
                        <p style="color:var(--ink-soft); font-size:0.9rem;"><?php echo clean(getSetting($conn, 'contact_email', 'info@guryosamo.com')); ?></p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="icon-box" style="color:var(--gold-600);"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg></div>
                    <div>
                        <h4 style="font-size:0.98rem;">Opening hours</h4>
                        <p style="color:var(--ink-soft); font-size:0.9rem;">Mon &ndash; Sat &middot; 8:00 AM &ndash; 6:00 PM</p>
                    </div>
                </div>
            </div>

            <div class="contact-form">
                <h3 style="margin-bottom:18px;">Send Us a Message</h3>

                <?php if ($successMsg): ?><div class="alert alert-success"><?php echo clean($successMsg); ?></div><?php endif; ?>
                <?php if ($errorMsg): ?><div class="alert alert-error"><?php echo clean($errorMsg); ?></div><?php endif; ?>

                <form method="post" id="contactForm">
                    <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                    <div class="form-row">
                        <div class="field-block">
                            <label for="name" style="font-size:0.84rem; font-weight:600; display:block; margin-bottom:6px;">Full Name</label>
                            <div class="field"><input type="text" id="name" name="name" placeholder="Your full name" required></div>
                        </div>
                        <div class="field-block">
                            <label for="email" style="font-size:0.84rem; font-weight:600; display:block; margin-bottom:6px;">Email Address</label>
                            <div class="field"><input type="email" id="email" name="email" placeholder="you@example.com" required></div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="field-block">
                            <label for="phone" style="font-size:0.84rem; font-weight:600; display:block; margin-bottom:6px;">Phone (optional)</label>
                            <div class="field"><input type="text" id="phone" name="phone" placeholder="+252 63 1234567"></div>
                        </div>
                        <div class="field-block">
                            <label for="subject" style="font-size:0.84rem; font-weight:600; display:block; margin-bottom:6px;">How can we help?</label>
                            <div class="field">
                                <select id="subject" name="subject" required style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 6px; font-family: 'Inter', sans-serif;">
                                    <option value="" disabled selected>Select an option...</option>
                                    <option value="I'm looking to buy">I'm looking to buy</option>
                                    <option value="I'm looking to rent">I'm looking to rent</option>
                                    <option value="I want to sell a property">I want to sell a property</option>
                                    <option value="I want to list a property">I want to list a property</option>
                                    <option value="General inquiry">General inquiry</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="field-block">
                        <label for="message" style="font-size:0.84rem; font-weight:600; display:block; margin-bottom:6px;">Message</label>
                        <div class="field"><textarea id="message" name="message" rows="5" placeholder="Write your message here..." required></textarea></div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Send Message</button>
                    <p style="text-align: center; color: var(--ink-soft); font-size: 0.85rem; margin-top: 12px;">We usually respond within one business day.</p>
                </form>

                <div style="margin-top: 40px; padding-top: 30px; border-top: 1px solid var(--border);">
                    <h4 style="font-size: 1.05rem; margin-bottom: 16px;">Prefer a quick conversation?</h4>
                    <div style="display:flex; gap:12px; flex-wrap:wrap;">
                        <a href="https://wa.me/252636097266" target="_blank" class="btn" style="background:#25D366; color:#fff;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                            WhatsApp Us
                        </a>
                        <a href="tel:+252636097266" class="btn btn-ghost">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                            Call Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
