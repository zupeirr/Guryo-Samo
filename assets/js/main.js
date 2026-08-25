/* ==========================================================================
   Real Estate Management System — Public site JS
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {

    // ---- Mobile nav toggle ----
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function () {
            navMenu.classList.toggle('open');
            navToggle.classList.toggle('open');
            if (navMenu.classList.contains('open')) {
                document.body.style.overflow = 'hidden'; // Prevent background scrolling
            } else {
                document.body.style.overflow = '';
            }
        });
    }

    // ---- Contact form basic validation ----
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function (e) {
            const name = contactForm.querySelector('[name="name"]');
            const email = contactForm.querySelector('[name="email"]');
            const message = contactForm.querySelector('[name="message"]');
            let valid = true;
            let errorMsg = '';

            if (!name.value.trim()) { valid = false; errorMsg = 'Please enter your name.'; }
            else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) { valid = false; errorMsg = 'Please enter a valid email address.'; }
            else if (!message.value.trim()) { valid = false; errorMsg = 'Please write your message.'; }

            if (!valid) {
                e.preventDefault();
                showFormError(contactForm, errorMsg);
            }
        });
    }

    function showFormError(form, msg) {
        let box = form.querySelector('.js-error-box');
        if (!box) {
            box = document.createElement('div');
            box.className = 'alert alert-error js-error-box';
            form.prepend(box);
        }
        box.textContent = msg;
    }

    // ---- Property search filter (used on properties.php, submits GET form) ----
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function (e) {
            // Remove empty fields from the query string for cleaner URLs
            searchForm.querySelectorAll('input, select').forEach(function (el) {
                if (!el.value) el.removeAttribute('name');
            });
        });
    }

    // ---- Animated Counters ----
    const counters = document.querySelectorAll('.counter');
    if (counters.length > 0) {
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = +counter.getAttribute('data-target');
                    const duration = 1000; // 1 second smooth animation
                    const suffix = counter.getAttribute('data-suffix') || '';
                    let startTime = null;

                    const step = (timestamp) => {
                        if (!startTime) startTime = timestamp;
                        const progress = Math.min((timestamp - startTime) / duration, 1);
                        const currentVal = Math.floor(progress * target);
                        counter.innerText = currentVal + suffix;
                        if (progress < 1) {
                            window.requestAnimationFrame(step);
                        } else {
                            counter.innerText = target + suffix;
                        }
                    };

                    window.requestAnimationFrame(step);
                    observer.unobserve(counter); // run only once
                }
            });
        }, observerOptions);

        counters.forEach(counter => {
            observer.observe(counter);
        });
    }
});
