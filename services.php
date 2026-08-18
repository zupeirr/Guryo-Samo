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
        <div class="section-head services-section-head">
            <div class="eyebrow-wrapper">
                <span class="eyebrow-line"></span>
                <span class="eyebrow" style="margin-bottom:0;">WHAT WE OFFER</span>
                <span class="eyebrow-line"></span>
            </div>
            <h2 class="section-title" style="font-size: 2.4rem; max-width: 600px; margin: 16px auto 20px; line-height: 1.3;">Tailored Services for Every Property Need</h2>
            <p class="section-sub" style="max-width: 580px; margin: 0 auto 28px; font-size: 1.05rem;">Whether you are buying, renting, selling, or managing property, our platform and expert agents are here to help.</p>
            <div class="services-count-badge">6 SERVICES</div>
        </div>

        <div class="services-grid">
            <!-- 1. Property Buying -->
            <div class="service-card">
                <div>
                    <div class="service-card-header">
                        <div class="service-icon-wrapper">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--gold-600)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        </div>
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
                        <div class="service-icon-wrapper">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--gold-600)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg>
                        </div>
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
                        <div class="service-icon-wrapper">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--gold-600)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                        </div>
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
                        <div class="service-icon-wrapper">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--gold-600)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><path d="M9 22v-4h6v4"></path><path d="M8 6h.01"></path><path d="M16 6h.01"></path><path d="M12 6h.01"></path><path d="M12 10h.01"></path><path d="M12 14h.01"></path><path d="M16 10h.01"></path><path d="M16 14h.01"></path><path d="M8 10h.01"></path><path d="M8 14h.01"></path></svg>
                        </div>
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
                        <div class="service-icon-wrapper">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--gold-600)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
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
                        <div class="service-icon-wrapper">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--gold-600)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="M9 12l2 2 4-4"></path></svg>
                        </div>
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
                <a href="properties.php" class="btn btn-outline-gold">Explore Properties</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
