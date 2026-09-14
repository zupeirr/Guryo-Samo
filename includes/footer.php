<footer class="site-footer">
    <!-- Background layers -->
    <div class="footer-bg-gradient"></div>
    <div class="footer-glow"></div>

    <!-- Faint building silhouette SVG -->
    <svg class="footer-skyline" viewBox="0 0 1200 200" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" preserveAspectRatio="xMidYMax meet">
        <g fill="none" stroke="rgba(200,155,60,0.07)" stroke-width="1.5">
            <!-- Far left tower -->
            <rect x="40" y="80" width="60" height="120"/>
            <rect x="55" y="60" width="30" height="22"/>
            <line x1="70" y1="30" x2="70" y2="60"/>
            <rect x="60" y="95" width="8" height="10"/>
            <rect x="75" y="95" width="8" height="10"/>
            <rect x="60" y="115" width="8" height="10"/>
            <rect x="75" y="115" width="8" height="10"/>
            <!-- Left mid building -->
            <rect x="130" y="100" width="90" height="100"/>
            <rect x="145" y="85" width="60" height="17"/>
            <rect x="140" y="110" width="12" height="14"/>
            <rect x="160" y="110" width="12" height="14"/>
            <rect x="180" y="110" width="12" height="14"/>
            <rect x="140" y="132" width="12" height="14"/>
            <rect x="160" y="132" width="12" height="14"/>
            <rect x="180" y="132" width="12" height="14"/>
            <rect x="140" y="154" width="12" height="14"/>
            <rect x="160" y="154" width="12" height="14"/>
            <rect x="180" y="154" width="12" height="14"/>
            <!-- Center tall tower -->
            <rect x="470" y="30" width="80" height="170"/>
            <rect x="485" y="10" width="50" height="22"/>
            <line x1="510" y1="0" x2="510" y2="10"/>
            <rect x="480" y="50" width="14" height="16"/>
            <rect x="502" y="50" width="14" height="16"/>
            <rect x="524" y="50" width="14" height="16"/>
            <rect x="480" y="76" width="14" height="16"/>
            <rect x="502" y="76" width="14" height="16"/>
            <rect x="524" y="76" width="14" height="16"/>
            <rect x="480" y="102" width="14" height="16"/>
            <rect x="502" y="102" width="14" height="16"/>
            <rect x="524" y="102" width="14" height="16"/>
            <rect x="480" y="128" width="14" height="16"/>
            <rect x="502" y="128" width="14" height="16"/>
            <rect x="524" y="128" width="14" height="16"/>
            <!-- Center-right block -->
            <rect x="570" y="70" width="100" height="130"/>
            <rect x="585" y="55" width="70" height="17"/>
            <rect x="580" y="85" width="14" height="16"/>
            <rect x="602" y="85" width="14" height="16"/>
            <rect x="624" y="85" width="14" height="16"/>
            <rect x="646" y="85" width="14" height="16"/>
            <rect x="580" y="111" width="14" height="16"/>
            <rect x="602" y="111" width="14" height="16"/>
            <rect x="624" y="111" width="14" height="16"/>
            <rect x="646" y="111" width="14" height="16"/>
            <rect x="580" y="137" width="14" height="16"/>
            <rect x="602" y="137" width="14" height="16"/>
            <rect x="624" y="137" width="14" height="16"/>
            <rect x="646" y="137" width="14" height="16"/>
            <!-- Right tower -->
            <rect x="950" y="60" width="70" height="140"/>
            <rect x="963" y="44" width="44" height="18"/>
            <line x1="985" y1="20" x2="985" y2="44"/>
            <rect x="958" y="75" width="12" height="14"/>
            <rect x="978" y="75" width="12" height="14"/>
            <rect x="998" y="75" width="12" height="14"/>
            <rect x="958" y="97" width="12" height="14"/>
            <rect x="978" y="97" width="12" height="14"/>
            <rect x="998" y="97" width="12" height="14"/>
            <rect x="958" y="119" width="12" height="14"/>
            <rect x="978" y="119" width="12" height="14"/>
            <rect x="998" y="119" width="12" height="14"/>
            <!-- Far right building -->
            <rect x="1070" y="90" width="90" height="110"/>
            <rect x="1085" y="75" width="60" height="17"/>
            <rect x="1080" y="105" width="12" height="14"/>
            <rect x="1100" y="105" width="12" height="14"/>
            <rect x="1120" y="105" width="12" height="14"/>
            <rect x="1080" y="127" width="12" height="14"/>
            <rect x="1100" y="127" width="12" height="14"/>
            <rect x="1120" y="127" width="12" height="14"/>
            <!-- Ground line -->
            <line x1="0" y1="200" x2="1200" y2="200"/>
        </g>
    </svg>

    <!-- Animated floating orbs -->
    <div class="footer-orb footer-orb-1"></div>
    <div class="footer-orb footer-orb-2"></div>

    <div class="container" style="position: relative; z-index: 2;">

        <!-- ── Main Grid ── -->
        <div class="footer-grid">

            <!-- Brand Column -->
            <div>
                <a href="<?php echo $baseUrl ?? ''; ?>index.php" class="brand" style="display:inline-flex; margin-bottom:4px;">
                    <span class="mark">GS</span> <?php echo clean(getSetting($conn, 'site_name', 'Guryo Samo')); ?>
                </a>
                <p class="footer-brand-desc">
                    Find your next home with confidence. Discover quality properties across Somaliland with a simple, transparent and trusted experience.
                </p>
                <a href="<?php echo $baseUrl ?? ''; ?>properties.php" class="footer-brand-cta">
                    Explore Properties
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
                <div class="footer-social">
                    <a href="<?php echo htmlspecialchars(getSetting($conn, 'social_facebook', '#')); ?>" target="_blank" class="social-icon-link" aria-label="Facebook">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-facebook"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                    </a>
                    <a href="<?php echo htmlspecialchars(getSetting($conn, 'social_whatsapp', '#')); ?>" target="_blank" class="social-icon-link" aria-label="WhatsApp">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </a>
                    <a href="<?php echo htmlspecialchars(getSetting($conn, 'social_tiktok', '#')); ?>" target="_blank" class="social-icon-link" aria-label="TikTok">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-instagram"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
                    </a>
                </div>
            </div>

            <!-- Explore Links -->
            <div>
                <h4>Explore</h4>
                <ul>
                    <li><a href="<?php echo $baseUrl ?? ''; ?>index.php" class="footer-link">Home</a></li>
                    <li><a href="<?php echo $baseUrl ?? ''; ?>properties.php" class="footer-link">Properties</a></li>
                    <li><a href="<?php echo $baseUrl ?? ''; ?>services.php" class="footer-link">Services</a></li>
                    <li><a href="<?php echo $baseUrl ?? ''; ?>about.php" class="footer-link">About Us</a></li>
                    <li><a href="<?php echo $baseUrl ?? ''; ?>contact.php" class="footer-link">Contact</a></li>
                </ul>
            </div>

            <!-- Property Types -->
            <div>
                <h4>Properties</h4>
                <ul>
                    <li><a href="<?php echo $baseUrl ?? ''; ?>properties.php?type=Villa" class="footer-link">Villas</a></li>
                    <li><a href="<?php echo $baseUrl ?? ''; ?>properties.php?type=Apartment" class="footer-link">Apartments</a></li>
                    <li><a href="<?php echo $baseUrl ?? ''; ?>properties.php?type=House" class="footer-link">Houses</a></li>
                    <li><a href="<?php echo $baseUrl ?? ''; ?>properties.php?type=Commercial" class="footer-link">Commercial</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div>
                <h4>Contact Us</h4>
                <ul class="footer-contact-list">
                    <li>
                        <span class="footer-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pin"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg></span>
                        <?php echo str_replace('Hargeisa,Somaliland', 'Hargeisa, Somaliland', clean(getSetting($conn, 'address', 'Hargeisa, Somaliland'))); ?>
                    </li>
                    <?php $phone = clean(getSetting($conn, 'contact_phone', '+252 63 6097266')); ?>
                    <li>
                        <span class="footer-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span>
                        <a href="tel:<?php echo str_replace(' ', '', $phone); ?>"><?php echo $phone; ?></a>
                    </li>
                    <?php $email = clean(getSetting($conn, 'contact_email', 'info@guryosamo.com')); ?>
                    <li>
                        <span class="footer-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg></span>
                        <a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a>
                    </li>
                </ul>
            </div>
        </div><!-- /.footer-grid -->


        <!-- ── Newsletter ── -->
        <div class="footer-newsletter">
            <div class="footer-newsletter-copy">
                <h3>Stay Updated With Guryo Samo</h3>
                <p>Get notified about new properties, featured listings and the latest opportunities across Somaliland.</p>
            </div>
            <form class="footer-newsletter-form" onsubmit="handleNewsletterSubmit(event)">
                <input type="email" id="footer-email" name="email" placeholder="Enter your email address" required autocomplete="email">
                <button type="submit">
                    Subscribe
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </button>
            </form>
        </div>


    </div><!-- /.container -->

    <!-- ── Legal Bottom Bar (full-width dark strip) ── -->
    <div class="footer-bottom" style="position: relative; z-index: 2;">
        <div class="footer-bottom-inner">
            <span>&copy; <?php echo date('Y'); ?> <?php echo clean(getSetting($conn, 'site_name', 'Guryo Samo')); ?>. All rights reserved.</span>
            <div class="footer-bottom-links">
                <a href="<?php echo $baseUrl ?? ''; ?>privacy.php">Privacy Policy</a>
                <span>&middot;</span>
                <a href="<?php echo $baseUrl ?? ''; ?>terms.php">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

<script>
function handleNewsletterSubmit(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button');
    const input = e.target.querySelector('input');
    const original = btn.innerHTML;
    btn.innerHTML = '✓ Subscribed!';
    btn.style.background = '#3f7a5c';
    input.value = '';
    setTimeout(() => { btn.innerHTML = original; btn.style.background = ''; }, 3000);
}
</script>

<script src="<?php echo $baseUrl ?? ''; ?>assets/js/main.js"></script>
</body>
</html>
