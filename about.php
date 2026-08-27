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
                <a href="properties.php" class="btn btn-primary">Explore Properties <span class="btn-arrow">&rarr;</span></a>
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
                <div class="editorial-img-wrap anim-img-reveal" id="editorial-img-trigger">
                    <img src="assets/images/about-page.png" alt="Our Story" class="anim-img-zoom">
                </div>
            </div>

            <!-- Text col: staggered entry -->
            <div class="editorial-text-col">
                <span class="eyebrow anim-stagger" style="transition-delay:0ms;">OUR STORY</span>
                <h2 class="section-title anim-stagger" style="font-size: 2.8rem; margin-bottom: 24px; line-height: 1.2; transition-delay:80ms;">Real estate made simpler.</h2>

                <!-- Gold vertical line + quote -->
                <div class="editorial-quote-wrapper anim-stagger" style="transition-delay:160ms;" id="quote-block">
                    <span class="editorial-quote-inner">&ldquo;Finding the right property should feel exciting, not complicated.&rdquo;</span>
                </div>

                <p class="anim-stagger" style="color:var(--ink-soft); margin-bottom: 24px; font-size: 1.05rem; line-height: 1.7; transition-delay:240ms;">
                    Guryo Samo was founded with a simple goal: make finding a home a straightforward, transparent experience. What started as a small local agency has grown into a trusted name for property seekers and owners alike.
                </p>
                <p class="anim-stagger" style="color:var(--ink-soft); margin-bottom: 32px; font-size: 1.05rem; line-height: 1.7; transition-delay:320ms;">
                    Today, our platform lists apartments, villas, houses, studios and offices for sale and for rent, giving customers an easy way to search, compare, and connect directly with our expert team.
                </p>
                <a href="services.php" class="text-link-gold anim-stagger" style="transition-delay:420ms;">Learn more about our approach &rarr;</a>
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
                    <p>To connect people with properties that fit their needs through honest information, trusted guidance, and dependable service.</p>
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
<section class="section values-section-wrapper" id="values-trigger" style="padding: 100px 0; position: relative; overflow: hidden;">
    <!-- Subtle gold glow background -->
    <div style="position: absolute; top: 20%; left: 50%; transform: translateX(-50%); width: 600px; height: 400px; background: radial-gradient(circle, rgba(200, 155, 60, 0.05) 0%, rgba(255,255,255,0) 70%); z-index: 0; pointer-events: none;"></div>
    
    <div class="container" style="position: relative; z-index: 1;">
        <div class="section-head services-section-head">
            <div class="eyebrow-wrapper">
                <span class="eyebrow-line anim-gold-line"></span>
                <span class="eyebrow" style="margin-bottom:0;">OUR VALUES</span>
                <span class="eyebrow-line anim-gold-line"></span>
            </div>
            <h2 class="section-title" style="font-size: 2.4rem; margin: 16px auto 20px;">What Guides Our Work</h2>
        </div>
        
        <div class="values-grid">
            <div class="value-card anim-value-stagger" style="transition-delay: 0ms;">
                <div class="value-icon">
                    <svg class="anim-icon-eye" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </div>
                <h3>Transparency</h3>
                <p>Clear listings, honest pricing, and no hidden surprises at any stage of the process.</p>
            </div>
            
            <div class="value-card anim-value-stagger" style="transition-delay: 120ms;">
                <div class="value-icon">
                    <svg class="anim-icon-check" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <h3>Reliability</h3>
                <p>We follow through on what we promise, from your very first contact straight to closing.</p>
            </div>
            
            <div class="value-card anim-value-stagger" style="transition-delay: 240ms;">
                <div class="value-icon">
                    <svg class="anim-icon-heart" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                </div>
                <h3>Customer First</h3>
                <p>Every single recommendation starts with truly understanding what our clients actually need.</p>
            </div>
        </div>
    </div>
</section>

<!-- 6. How We Work (Timeline) -->
<section id="how-we-work" class="section bg-alt" style="padding: 90px 0 100px;">
    <div class="container">
        <div class="section-head" style="margin-bottom: 60px;">
            <span class="eyebrow">HOW WE WORK</span>
            <h2 class="section-title" style="font-size: 2.2rem; margin-bottom: 0;">From search to keys, we're with you.</h2>
        </div>
        
        <div class="timeline-wrapper">
            <div class="timeline-line-bg"></div>
            <div class="timeline-line-active"></div>
            
            <div class="timeline-grid">
                <div class="timeline-step" style="transition-delay: 0ms;">
                    <div class="step-marker">01</div>
                    <h4>Tell us what you need</h4>
                    <p>Share your budget, location, and preferred property type.</p>
                </div>
                <div class="timeline-step" style="transition-delay: 150ms;">
                    <div class="step-marker">02</div>
                    <h4>Find the right property</h4>
                    <p>We narrow down options that match your requirements.</p>
                </div>
                <div class="timeline-step" style="transition-delay: 300ms;">
                    <div class="step-marker">03</div>
                    <h4>View & compare</h4>
                    <p>Schedule viewings and make an informed decision.</p>
                </div>
                <div class="timeline-step" style="transition-delay: 450ms;">
                    <div class="step-marker">04</div>
                    <h4>Close with confidence</h4>
                    <p>We support you through the final paperwork.</p>
                </div>
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
            <a href="properties.php" class="btn btn-primary">Explore Properties <span class="btn-arrow">&rarr;</span></a>
            <a href="contact.php" class="btn btn-outline-gold">Contact Us</a>
        </div>
    </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", function () {

    /* ── Stat counter ──────────────────────────────────────────────────── */
    const counters = document.querySelectorAll('.stat-number');
    let hasCounted = false;
    const statObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !hasCounted) {
                hasCounted = true;
                counters.forEach(counter => {
                    const target = +counter.getAttribute('data-target');
                    const suffix = counter.getAttribute('data-suffix') || '';
                    counter.innerText = '0' + suffix;
                    const updateCount = () => {
                        const count = +counter.innerText.replace(/\D/g, '');
                        const inc = target / 60;
                        if (count < target) {
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
    if (statsSection) statObserver.observe(statsSection);

    /* ── How We Work timeline stagger & line draw ─────────────────────── */
    const howWeWork = document.getElementById('how-we-work');
    if (howWeWork) {
        const howObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Start line draw
                    const line = howWeWork.querySelector('.timeline-line-active');
                    if (line) line.style.width = '75%';
                    
                    // Fade in steps via class (delays are inline in HTML)
                    howWeWork.querySelectorAll('.timeline-step').forEach((step) => {
                        step.classList.add('animate-in');
                    });
                    
                    howObserver.unobserve(howWeWork);
                }
            });
        }, { threshold: 0.3 });
        howObserver.observe(howWeWork);
    }

    /* ── 1 + 3. Image reveal & one-shot zoom ──────────────────────────── */
    const imgWrap = document.getElementById('editorial-img-trigger');
    if (imgWrap) {
        const imgObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    imgWrap.classList.add('is-visible');          // fade + slide up
                    const img = imgWrap.querySelector('.anim-img-zoom');
                    if (img) img.classList.add('is-visible');     // scale 1.05 → 1
                    imgObserver.unobserve(imgWrap);               // fire only once
                }
            });
        }, { threshold: 0.2 });
        imgObserver.observe(imgWrap);
    }

    /* ── 2. Text stagger + gold line animation ────────────────────────── */
    const editorialSection = document.querySelector('.editorial-text-col');
    if (editorialSection) {
        const staggerItems = editorialSection.querySelectorAll('.anim-stagger');
        const quoteBlock   = document.getElementById('quote-block');

        const textObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Stagger each element using its own transition-delay
                    staggerItems.forEach(el => el.classList.add('is-visible'));

                    // Gold line grows after the quote wrapper fades in (160ms delay)
                    if (quoteBlock) {
                        setTimeout(() => quoteBlock.classList.add('line-grown'), 380);
                    }

                    textObserver.unobserve(editorialSection);
                }
            });
        }, { threshold: 0.25 });
        textObserver.observe(editorialSection);
    }

    /* ── Values section stagger & icon animation ──────────────────────── */
    const valuesTrigger = document.getElementById('values-trigger');
    if (valuesTrigger) {
        const valuesObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Trigger gold lines
                    valuesTrigger.querySelectorAll('.anim-gold-line').forEach(el => el.classList.add('is-visible'));
                    
                    // Trigger value cards stagger
                    valuesTrigger.querySelectorAll('.anim-value-stagger').forEach(el => el.classList.add('is-visible'));
                    
                    // Trigger icon animations
                    valuesTrigger.querySelectorAll('.anim-icon-eye, .anim-icon-check, .anim-icon-heart').forEach(el => el.classList.add('is-visible'));

                    valuesObserver.unobserve(valuesTrigger);
                }
            });
        }, { threshold: 0.25 });
        valuesObserver.observe(valuesTrigger);
    }

});
</script>

<?php include 'includes/footer.php'; ?>
