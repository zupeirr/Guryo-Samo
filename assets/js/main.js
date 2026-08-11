/* ==========================================================================
   Real Estate Management System — Public site JS
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {

    // ---- Mobile nav toggle ----
    const navToggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');
    if (navToggle && navLinks) {
        navToggle.addEventListener('click', function () {
            navLinks.classList.toggle('open');
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
});
