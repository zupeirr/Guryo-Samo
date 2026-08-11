<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <a href="<?php echo $baseUrl ?? ''; ?>index.php" class="brand" style="margin-bottom:14px; display:inline-flex;">
                    <span class="mark">GS</span> Guryo Samo
                </a>
                <p style="max-width:320px; font-size:0.9rem; margin-top:12px;">
                    Helping people find the right place to call home. Browse our listings or reach
                    out and our team will help you find a property that fits your needs.
                </p>
            </div>
            <div>
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?php echo $baseUrl ?? ''; ?>index.php">Home</a></li>
                    <li><a href="<?php echo $baseUrl ?? ''; ?>properties.php">Properties</a></li>
                    <li><a href="<?php echo $baseUrl ?? ''; ?>about.php">About Us</a></li>
                    <li><a href="<?php echo $baseUrl ?? ''; ?>contact.php">Contact</a></li>
                </ul>
            </div>
            <div>
                <h4>Property Types</h4>
                <ul>
                    <li><a href="<?php echo $baseUrl ?? ''; ?>properties.php?type=Villa">Villas</a></li>
                    <li><a href="<?php echo $baseUrl ?? ''; ?>properties.php?type=Apartment">Apartments</a></li>
                    <li><a href="<?php echo $baseUrl ?? ''; ?>properties.php?type=House">Houses</a></li>
                    <li><a href="<?php echo $baseUrl ?? ''; ?>properties.php?type=Studio">Studios</a></li>
                </ul>
            </div>
            <div>
                <h4>Contact</h4>
                <ul>
                    <li>Airport Road, Hargeisa, Somaliland</li>
                    <li>+252 63 4567890</li>
                    <li>info@guryosamo.com</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; <?php echo date('Y'); ?> Guryo Samo. All rights reserved. &mdash; University Graduation Project.
        </div>
    </div>
</footer>

<script src="<?php echo $baseUrl ?? ''; ?>assets/js/main.js"></script>
</body>
</html>
