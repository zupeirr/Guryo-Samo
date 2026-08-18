<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$pageTitle = 'About Us';
$activePage = 'about';
$baseUrl = '';

include 'includes/header.php';
?>

<!-- 1. Premium Property Hero -->
<section class="about-hero" style="background: linear-gradient(180deg, rgba(16, 29, 44, 0.75) 0%, rgba(16, 29, 44, 0.85) 100%), url('https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?q=80&w=1600&auto=format&fit=crop') center/cover;">
    <div class="container text-center">
        <div class="hero-content" style="max-width: 720px; margin: 0 auto; padding: 100px 0;">
            <span class="eyebrow" style="color:var(--gold-500); margin-bottom: 16px;">About Guryo Samo</span>
            <h1 style="font-size: 3.2rem; color: #fff; line-height: 1.2; margin-bottom: 24px;">Built on trust, driven by service.</h1>
            <p style="color: rgba(255,255,255,0.85); font-size: 1.15rem; margin-bottom: 40px; line-height: 1.7;">Guryo Samo helps people buy, rent, and sell property with confidence &mdash; backed by a team that knows the local market inside and out.</p>
            
            <div style="display:flex; gap:16px; justify-content:center; flex-wrap:wrap;">
                <a href="properties.php" class="btn btn-primary">Explore Properties</a>
                <a href="#team" class="btn btn-outline-gold">Meet Our Team</a>
            </div>
        </div>
    </div>
</section>

<!-- 2. Trust / Statistics Section -->
<section class="stats-section bg-alt" style="padding: 40px 0;">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-number" data-target="500" data-suffix="+">0+</span>
                <span class="stat-label">Properties Listed</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" data-target="1200" data-suffix="+">0+</span>
                <span class="stat-label">Happy Clients</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" data-target="10" data-suffix="+">0+</span>
                <span class="stat-label">Years Experience</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" data-target="98" data-suffix="%">0%</span>
                <span class="stat-label">Client Satisfaction</span>
            </div>
        </div>
    </div>
</section>

<!-- 3. Our Story (50/50 Editorial Layout) -->
<section class="section">
    <div class="container">
        <div class="editorial-grid">
            <div class="editorial-img-col">
                <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?q=80&w=800&auto=format&fit=crop" alt="Real Estate Consultation" class="editorial-img">
            </div>
            <div class="editorial-text-col">
                <span class="eyebrow">OUR STORY</span>
                <h2 class="section-title" style="font-size: 2.8rem; margin-bottom: 24px; line-height: 1.2;">Real estate made simpler.</h2>
                <div class="editorial-quote">
                    “Finding the right property should feel exciting, not complicated.”
                </div>
                <p style="color:var(--ink-soft); margin-bottom: 24px; font-size: 1.05rem; line-height: 1.7;">
                    Guryo Samo was founded with a simple goal: make finding a home a straightforward, transparent experience. What started as a small local agency has grown into a trusted name for property seekers and owners alike.
                </p>
                <p style="color:var(--ink-soft); margin-bottom: 32px; font-size: 1.05rem; line-height: 1.7;">
                    Today, our platform lists apartments, villas, houses, studios and offices for sale and for rent, giving customers an easy way to search, compare, and connect directly with our expert team.
                </p>
                <a href="services.php" class="text-link-gold">Learn more about our approach &rarr;</a>
            </div>
        </div>
    </div>
</section>

<!-- 4. Mission / Vision -->
<section class="section bg-alt" style="padding: 90px 0;">
    <div class="container">
        <div class="mission-vision-grid">
            <div class="mv-card">
                <div class="mv-number">01</div>
                <div class="mv-content">
                    <span class="eyebrow">OUR MISSION</span>
                    <p>Connect people with properties that genuinely fit their lives through honest information and dependable service.</p>
                </div>
            </div>
            <div class="mv-card">
                <div class="mv-number">02</div>
                <div class="mv-content">
                    <span class="eyebrow">OUR VISION</span>
                    <p>Become the region's most trusted real estate platform, known for simplicity and integrity.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 5. What Guides Our Work -->
<section class="section" style="padding: 100px 0;">
    <div class="container">
        <div class="section-head services-section-head">
            <div class="eyebrow-wrapper">
                <span class="eyebrow-line"></span>
                <span class="eyebrow" style="margin-bottom:0;">OUR VALUES</span>
                <span class="eyebrow-line"></span>
            </div>
            <h2 class="section-title" style="font-size: 2.4rem; margin: 16px auto 20px;">What Guides Our Work</h2>
        </div>
        
        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </div>
                <h3>Transparency</h3>
                <p>Clear listings, honest pricing, and no hidden surprises at any stage of the process.</p>
            </div>
            
            <div class="value-card">
                <div class="value-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <h3>Reliability</h3>
                <p>We follow through on what we promise, from your very first contact straight to closing.</p>
            </div>
            
            <div class="value-card">
                <div class="value-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                </div>
                <h3>Customer First</h3>
                <p>Every single recommendation starts with truly understanding what our clients actually need.</p>
            </div>
        </div>
    </div>
</section>

<!-- 6. How We Work (Timeline) -->
<section class="section bg-alt" style="padding: 100px 0;">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">HOW WE WORK</span>
            <h2 class="section-title" style="font-size: 2.2rem; margin-bottom: 50px;">From search to keys, we're with you.</h2>
        </div>
        
        <div class="timeline-grid">
            <div class="timeline-step">
                <div class="step-marker">01</div>
                <h4>Tell us what you need</h4>
                <p>Share your budget, preferred location and property type.</p>
            </div>
            <div class="timeline-step">
                <div class="step-marker">02</div>
                <h4>Discover the right properties</h4>
                <p>We narrow down options that perfectly fit your requirements.</p>
            </div>
            <div class="timeline-step">
                <div class="step-marker">03</div>
                <h4>Visit & compare</h4>
                <p>Schedule viewings, ask questions, and make an informed decision.</p>
            </div>
            <div class="timeline-step">
                <div class="step-marker">04</div>
                <h4>Close with confidence</h4>
                <p>Our team fully supports you through the final paperwork steps.</p>
            </div>
        </div>
    </div>
</section>

<!-- 7. Our Team -->
<section id="team" class="section" style="padding: 100px 0;">
    <div class="container">
        <div class="section-head services-section-head">
            <div class="eyebrow-wrapper">
                <span class="eyebrow-line"></span>
                <span class="eyebrow" style="margin-bottom:0;">OUR TEAM</span>
                <span class="eyebrow-line"></span>
            </div>
            <h2 class="section-title" style="font-size: 2.4rem; margin: 16px auto 20px;">Meet the people behind Guryo Samo</h2>
        </div>
        
        <div class="team-photo-grid">
            <!-- Team Member 1 -->
            <div class="team-card-premium">
                <div class="team-photo-wrapper">
                    <img src="assets/images/my-photo.jpg" alt="Suber Sulub">
                    <div class="team-social-overlay">
                        <a href="#"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg></a>
                        <a href="#"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg></a>
                    </div>
                </div>
                <div class="team-info">
                    <h3>Suber Sulub</h3>
                    <p>Founder & CTO</p>
                </div>
            </div>
            
            <!-- Team Member 2 -->
            <div class="team-card-premium">
                <div class="team-photo-wrapper">
                    <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=400&auto=format&fit=crop" alt="Raaqiya Mohamed">
                    <div class="team-social-overlay">
                        <a href="#"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg></a>
                        <a href="#"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg></a>
                    </div>
                </div>
                <div class="team-info">
                    <h3>Hermela</h3>
                    <p>Head of Sales</p>
                </div>
            </div>
            
            <!-- Team Member 3 -->
            <div class="team-card-premium">
                <div class="team-photo-wrapper">
                    <img src="https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?q=80&w=400&auto=format&fit=crop" alt="Ahmed Ebrahim">
                    <div class="team-social-overlay">
                        <a href="#"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg></a>
                        <a href="#"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg></a>
                    </div>
                </div>
                <div class="team-info">
                    <h3>SM</h3>
                    <p>Customer Relations Manager</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 8. Strong CTA -->
<section class="bottom-cta" style="background: linear-gradient(135deg, var(--navy-900) 0%, var(--navy-800) 100%); padding: 100px 0; color: #fff; text-align: center;">
    <div class="container">
        <h2 style="font-size: 2.8rem; color: #fff; margin-bottom: 20px;">Ready to find your next property?</h2>
        <p style="font-size: 1.1rem; color: rgba(255,255,255,0.8); max-width: 600px; margin: 0 auto 40px;">Whether you're buying, renting, or selling, our team is here to help guide you smoothly through the market.</p>
        <div style="display:flex; gap:16px; justify-content:center; flex-wrap:wrap;">
            <a href="properties.php" class="btn btn-primary">Explore Properties</a>
            <a href="contact.php" class="btn btn-outline-gold">Contact Us</a>
        </div>
    </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const counters = document.querySelectorAll('.stat-number');
    let hasCounted = false;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !hasCounted) {
                hasCounted = true;
                counters.forEach(counter => {
                    const target = +counter.getAttribute('data-target');
                    const suffix = counter.getAttribute('data-suffix') || '';
                    counter.innerText = '0' + suffix;
                    
                    const updateCount = () => {
                        const count = +counter.innerText.replace(/\D/g, '');
                        const inc = target / 60; // 1 second at 60fps
                        
                        if(count < target) {
                            counter.innerText = Math.ceil(count + inc).toLocaleString() + suffix;
                            requestAnimationFrame(updateCount);
                        } else {
                            counter.innerText = target.toLocaleString() + suffix;
                        }
                    };
                    updateCount();
                });
            }
        });
    }, { threshold: 0.5 });

    const statsSection = document.querySelector('.stats-section');
    if (statsSection) {
        observer.observe(statsSection);
    }
});
</script>

<?php include 'includes/footer.php'; ?>
