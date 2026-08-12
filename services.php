<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$pageTitle  = 'Our Services';
$activePage = 'services';
$baseUrl    = '';

include 'includes/header.php';
?>

<!-- Hero Banner -->
<section class="hero" style="background: linear-gradient(180deg, rgba(8, 25, 43, 0.72) 0%, rgba(8, 25, 43, 0.72) 100%), url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1600&auto=format&fit=crop') center/cover;">
    <div class="container">
        <div class="hero-content" style="max-width:680px;">
            <span class="eyebrow" style="color:#d4a638; letter-spacing: 0.14em;">OUR SERVICES</span>
            <h1 style="font-size:2.6rem; color:#ffffff; margin: 10px 0 16px; font-weight: 700;">Comprehensive Real Estate Solutions</h1>
            <p style="color: rgba(255,255,255,0.9); font-size:1.1rem; line-height: 1.65; margin: 0;">From finding your ideal home to professional property management and legal verification, Guryo Samo delivers trusted, full-suite real estate services.</p>
        </div>
    </div>
</section>

<!-- Services Grid Section -->
<section class="section services-section" style="background-color: #f7f9fc; padding: 70px 0 90px;">
    <div class="container">
        <div class="section-head" style="margin-bottom: 50px;">
            <span class="eyebrow">WHAT WE OFFER</span>
            <h2 class="section-title" style="font-size: 2.2rem;">Tailored Services for Every Property Need</h2>
            <p class="section-sub">Whether you are buying, renting, selling, or managing property, our platform and expert agents are here to help.</p>
        </div>

        <div class="services-grid">
            <!-- 1. Property Buying -->
            <div class="service-card">
                <div>
                    <div class="service-card-header">
                        <div class="service-icon-wrapper">🏠</div>
                        <span class="service-badge">Buying</span>
                    </div>
                    <h3>Property Buying</h3>
                    <p>Help customers find and purchase suitable properties matching their budget, preferred location, and family requirements.</p>
                    <ul class="service-features">
                        <li><span class="check-icon">✓</span> Curated listings in prime locations</li>
                        <li><span class="check-icon">✓</span> Expert price negotiation support</li>
                        <li><span class="check-icon">✓</span> Guided physical property tours</li>
                    </ul>
                </div>
                <a href="properties.php?status=For+Sale" class="btn btn-service">Explore Properties for Sale</a>
            </div>

            <!-- 2. Property Renting -->
            <div class="service-card">
                <div>
                    <div class="service-card-header">
                        <div class="service-icon-wrapper">🔑</div>
                        <span class="service-badge">Renting</span>
                    </div>
                    <h3>Property Renting</h3>
                    <p>Find houses, apartments, offices, and studios with flexible rental terms and transparent lease agreements.</p>
                    <ul class="service-features">
                        <li><span class="check-icon">✓</span> Verified rental property listings</li>
                        <li><span class="check-icon">✓</span> Flexible lease term options</li>
                        <li><span class="check-icon">✓</span> Seamless tenant onboarding</li>
                    </ul>
                </div>
                <a href="properties.php?status=For+Rent" class="btn btn-service">Browse Rental Listings</a>
            </div>

            <!-- 3. Property Selling -->
            <div class="service-card">
                <div>
                    <div class="service-card-header">
                        <div class="service-icon-wrapper">🏷️</div>
                        <span class="service-badge">Selling</span>
                    </div>
                    <h3>Property Selling</h3>
                    <p>Property owners can list their properties for sale and connect directly with serious buyers to close deals faster.</p>
                    <ul class="service-features">
                        <li><span class="check-icon">✓</span> High-visibility listing exposure</li>
                        <li><span class="check-icon">✓</span> Free professional property valuation</li>
                        <li><span class="check-icon">✓</span> Full sales closing support</li>
                    </ul>
                </div>
                <a href="contact.php?subject=List+My+Property" class="btn btn-service">List Your Property</a>
            </div>

            <!-- 4. Property Management -->
            <div class="service-card">
                <div>
                    <div class="service-card-header">
                        <div class="service-icon-wrapper">🏢</div>
                        <span class="service-badge">Management</span>
                    </div>
                    <h3>Property Management</h3>
                    <p>Help owners manage their properties and listings smoothly with tenant handling, maintenance, and rental collections.</p>
                    <ul class="service-features">
                        <li><span class="check-icon">✓</span> Comprehensive tenant screening</li>
                        <li><span class="check-icon">✓</span> Routine maintenance & inspections</li>
                        <li><span class="check-icon">✓</span> On-time rent collection & reports</li>
                    </ul>
                </div>
                <a href="contact.php?subject=Property+Management+Inquiry" class="btn btn-service">Inquire Management</a>
            </div>

            <!-- 5. Real Estate Consultation -->
            <div class="service-card">
                <div>
                    <div class="service-card-header">
                        <div class="service-icon-wrapper">🧑‍💼</div>
                        <span class="service-badge">Consultation</span>
                    </div>
                    <h3>Real Estate Consultation</h3>
                    <p>Connect customers with expert agents for personalized advice, market insights, legal guidance, and investment strategy.</p>
                    <ul class="service-features">
                        <li><span class="check-icon">✓</span> 1-on-1 expert agent advice</li>
                        <li><span class="check-icon">✓</span> Market trends & pricing analytics</li>
                        <li><span class="check-icon">✓</span> Investment return guidance</li>
                    </ul>
                </div>
                <a href="contact.php?subject=Real+Estate+Consultation" class="btn btn-service">Book a Consultation</a>
            </div>

            <!-- 6. Property Verification -->
            <div class="service-card">
                <div>
                    <div class="service-card-header">
                        <div class="service-icon-wrapper">🛡️</div>
                        <span class="service-badge">Verification</span>
                    </div>
                    <h3>Property Verification</h3>
                    <p>Highlight verified listings with thorough legal and physical checks to increase customer confidence and trust.</p>
                    <ul class="service-features">
                        <li><span class="check-icon">✓</span> Ownership & title deed audits</li>
                        <li><span class="check-icon">✓</span> Physical condition inspection</li>
                        <li><span class="check-icon">✓</span> Official Guryo Samo verified badge</li>
                    </ul>
                </div>
                <a href="properties.php" class="btn btn-service">View Verified Listings</a>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="section bg-alt" style="padding: 70px 0;">
    <div class="container">
        <div class="service-cta-box">
            <h2>Ready to Find or List Your Next Property?</h2>
            <p>Get in touch with our team of property experts today. We are here to answer your questions and guide you every step of the way.</p>
            <div style="display:flex; gap:16px; justify-content:center; flex-wrap:wrap;">
                <a href="contact.php" class="btn btn-primary">Get in Touch</a>
                <a href="properties.php" class="btn btn-outline">Explore Properties</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
