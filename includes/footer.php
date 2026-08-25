<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <a href="<?php echo $baseUrl ?? ''; ?>index.php" class="brand" style="margin-bottom:14px; display:inline-flex;">
                    <span class="mark">GS</span> <?php echo clean(getSetting($conn, 'site_name', 'Guryo Samo')); ?>
                </a>
                <p style="max-width:320px; font-size:0.95rem; margin-top:12px; color:rgba(255,255,255,0.7); line-height:1.6;">
                    Making property search simple, transparent, and trusted.
                </p>
                <div class="social-links" style="margin-top: 24px; display: flex; gap: 12px;">
                    <a href="<?php echo htmlspecialchars(getSetting($conn, 'social_facebook', '#')); ?>" target="_blank" class="social-icon-link" aria-label="Facebook">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M22.675 0h-21.35C.597 0 0 .597 0 1.325v21.351C0 23.403.597 24 1.325 24H12.82v-9.294H9.692v-3.622h3.128V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12V24h6.116c.73 0 1.323-.597 1.323-1.325V1.325C24 .597 23.403 0 22.675 0z"/></svg>
                    </a>
                    <a href="<?php echo htmlspecialchars(getSetting($conn, 'social_whatsapp', '#')); ?>" target="_blank" class="social-icon-link" aria-label="WhatsApp">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M11.9 22.6c-1.8 0-3.6-.5-5.2-1.4l-.4-.2-3.8 1 1-3.7-.2-.3c-.9-1.6-1.4-3.4-1.4-5.3 0-5.8 4.7-10.4 10.4-10.4 2.8 0 5.4 1.1 7.4 3.1s3.1 4.6 3.1 7.4c-.1 5.7-4.8 10.3-10.5 10.3zm-5.4-3.5c1.5.9 3.3 1.4 5.2 1.4 4.7 0 8.5-3.8 8.5-8.4 0-2.3-.9-4.4-2.5-6-1.6-1.6-3.7-2.5-6-2.5-4.7 0-8.5 3.8-8.5 8.4 0 1.7.5 3.4 1.5 4.8l-1 3.5 3.3-1.1zm9-5.1c-.2-.1-1.3-.7-1.5-.7-.2-.1-.3-.1-.4.1-.1.2-.5.7-.6.9-.1.1-.3.2-.5.1-.2-.1-.9-.3-1.8-.1-1.1-.7-1.5-.9-1.6-1.1-.1-.2 0-.3.1-.4.1-.1.2-.2.3-.3.1-.1.1-.2.2-.4s0-.3-.1-.4c-.1-.1-.4-1-.6-1.4-.2-.4-.3-.3-.4-.3h-.4c-.1 0-.3 0-.5.2-.2.2-.6.6-.6 1.4s.6 1.6 1.3 2.5c.9 1.1 2.2 2.8 4.3 3.6 2.2.9 2.2.6 2.6.5.4-.1 1.3-.5 1.5-1 .2-.5.2-.9.1-1z"/></svg>
                    </a>
                    <a href="<?php echo htmlspecialchars(getSetting($conn, 'social_tiktok', '#')); ?>" target="_blank" class="social-icon-link" aria-label="TikTok">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 15.71a6.34 6.34 0 0 0 10.86 4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/></svg>
                    </a>
                </div>
            </div>
            <div>
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?php echo $baseUrl ?? ''; ?>index.php" class="footer-link">Home</a></li>
                    <li><a href="<?php echo $baseUrl ?? ''; ?>properties.php" class="footer-link">Properties</a></li>
                    <li><a href="<?php echo $baseUrl ?? ''; ?>about.php" class="footer-link">About Us</a></li>
                    <li><a href="<?php echo $baseUrl ?? ''; ?>contact.php" class="footer-link">Contact</a></li>
                </ul>
            </div>
            <div>
                <h4>Property Types</h4>
                <ul>
                    <li><a href="<?php echo $baseUrl ?? ''; ?>properties.php?type=Villa" class="footer-link">Villas</a></li>
                    <li><a href="<?php echo $baseUrl ?? ''; ?>properties.php?type=Apartment" class="footer-link">Apartments</a></li>
                    <li><a href="<?php echo $baseUrl ?? ''; ?>properties.php?type=House" class="footer-link">Houses</a></li>
                    <li><a href="<?php echo $baseUrl ?? ''; ?>properties.php?type=Commercial" class="footer-link">Commercial</a></li>
                </ul>
            </div>
            <div>
                <h4>Contact</h4>
                <ul class="footer-contact-list">
                    <li><span class="footer-icon">📍</span> <?php echo str_replace('Hargeisa,Somaliland', 'Hargeisa, Somaliland', clean(getSetting($conn, 'address', 'Hargeisa, Somaliland'))); ?></li>
                    <?php $phone = clean(getSetting($conn, 'contact_phone', '+252 63 6097266')); ?>
                    <li><span class="footer-icon">☎</span> <a href="tel:<?php echo str_replace(' ', '', $phone); ?>"><?php echo $phone; ?></a></li>
                    <?php $email = clean(getSetting($conn, 'contact_email', 'info@guryosamo.com')); ?>
                    <li><span class="footer-icon">✉</span> <a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; <?php echo date('Y'); ?> <?php echo clean(getSetting($conn, 'site_name', 'Guryo Samo')); ?>. All rights reserved.
        </div>
    </div>
</footer>

<script src="<?php echo $baseUrl ?? ''; ?>assets/js/main.js"></script>
</body>
</html>
