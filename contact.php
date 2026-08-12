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
                    <div class="icon-box">📍</div>
                    <div>
                        <h4 style="font-size:0.98rem;">Office Address</h4>
                        <p style="color:var(--ink-soft); font-size:0.9rem;"><?php echo clean(getSetting($conn, 'address', 'Airport Road, Hargeisa, Somaliland')); ?></p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="icon-box">📞</div>
                    <div>
                        <h4 style="font-size:0.98rem;">Phone</h4>
                        <p style="color:var(--ink-soft); font-size:0.9rem;"><?php echo clean(getSetting($conn, 'contact_phone', '+252 63 4567890')); ?></p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="icon-box">✉️</div>
                    <div>
                        <h4 style="font-size:0.98rem;">Email</h4>
                        <p style="color:var(--ink-soft); font-size:0.9rem;"><?php echo clean(getSetting($conn, 'contact_email', 'info@guryosamo.com')); ?></p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="icon-box">🕒</div>
                    <div>
                        <h4 style="font-size:0.98rem;">Office Hours</h4>
                        <p style="color:var(--ink-soft); font-size:0.9rem;">Monday &ndash; Friday: 9:00 AM &ndash; 6:00 PM</p>
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
                            <label for="subject" style="font-size:0.84rem; font-weight:600; display:block; margin-bottom:6px;">Subject</label>
                            <div class="field"><input type="text" id="subject" name="subject" placeholder="How can we help?"></div>
                        </div>
                    </div>
                    <div class="field-block">
                        <label for="message" style="font-size:0.84rem; font-weight:600; display:block; margin-bottom:6px;">Message</label>
                        <div class="field"><textarea id="message" name="message" rows="5" placeholder="Write your message here..." required></textarea></div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
