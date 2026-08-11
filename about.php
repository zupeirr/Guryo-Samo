<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$pageTitle = 'About Us';
$activePage = 'about';
$baseUrl = '';

include 'includes/header.php';
?>

<section class="hero" style="padding:80px 0;">
    <div class="container">
        <div class="hero-content">
            <span class="eyebrow" style="color:#e9c874;">About Us</span>
            <h1 style="font-size:2.2rem;">Built on trust, driven by service.</h1>
            <p>Guryo Samo helps people buy, rent, and sell property with confidence &mdash; backed by a team that knows the local market inside and out.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="details-grid">
            <div>
                <span class="eyebrow">Our Story</span>
                <h2 class="section-title">Who We Are</h2>
                <p style="color:var(--ink-soft); margin-bottom:16px;">
                    Guryo Samo was founded with a simple goal: make finding a home a straightforward,
                    transparent experience. What started as a small local agency has grown into a trusted
                    name for property seekers and owners alike.
                </p>
                <p style="color:var(--ink-soft);">
                    Today, our platform lists apartments, villas, houses, studios and offices for sale and
                    for rent, giving customers an easy way to search, compare, and connect directly with our
                    team of agents.
                </p>
            </div>
            <div>
                <div class="side-card">
                    <h3 style="margin-bottom:14px;">Our Mission</h3>
                    <p style="color:var(--ink-soft); font-size:0.92rem; margin-bottom:18px;">
                        To connect people with properties that truly fit their lives, through honest
                        information and dependable service.
                    </p>
                    <h3 style="margin-bottom:14px;">Our Vision</h3>
                    <p style="color:var(--ink-soft); font-size:0.92rem;">
                        To be the most trusted real estate platform in the region, known for
                        simplicity and integrity.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section bg-alt">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Our Values</span>
            <h2 class="section-title">What Guides Our Work</h2>
        </div>
        <div class="value-grid">
            <div class="value-item">
                <h3>Transparency</h3>
                <p style="margin-top:8px; color:var(--ink-soft); font-size:0.92rem;">Clear listings, honest pricing, and no hidden surprises at any stage.</p>
            </div>
            <div class="value-item">
                <h3>Reliability</h3>
                <p style="margin-top:8px; color:var(--ink-soft); font-size:0.92rem;">We follow through on what we promise, from first contact to closing.</p>
            </div>
            <div class="value-item">
                <h3>Customer Focus</h3>
                <p style="margin-top:8px; color:var(--ink-soft); font-size:0.92rem;">Every recommendation starts with understanding what our clients actually need.</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Our Team</span>
            <h2 class="section-title">Meet the People Behind Guryo Samo</h2>
        </div>
        <div class="team-grid">
            <div class="team-card">
                <div class="avatar-circle">HZ</div>
                <h3 style="font-size:1.05rem;">Hamze Zakarie</h3>
                <p style="color:var(--ink-soft); font-size:0.86rem; margin-top:4px;">Founder &amp; Managing Director</p>
            </div>
            <div class="team-card">
                <div class="avatar-circle">RM</div>
                <h3 style="font-size:1.05rem;">Raaqiya Mohamed</h3>
                <p style="color:var(--ink-soft); font-size:0.86rem; margin-top:4px;">Head of Sales</p>
            </div>
            <div class="team-card">
                <div class="avatar-circle">AE</div>
                <h3 style="font-size:1.05rem;">Ahmed Ebrahim</h3>
                <p style="color:var(--ink-soft); font-size:0.86rem; margin-top:4px;">Customer Relations Manager</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
