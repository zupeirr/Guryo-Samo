/* ==========================================================================
   Real Estate Management System — Admin Dashboard JS
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {

    // ---- Sidebar toggle (mobile) ----
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('adminSidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });
    }

    // ---- Sidebar dropdown groups ----
    document.querySelectorAll('.nav-group-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            this.closest('.nav-group').classList.toggle('open');
        });
    });

    // ---- Confirm before deleting a property or message ----
    document.querySelectorAll('.js-confirm-delete').forEach(function (link) {
        link.addEventListener('click', function (e) {
            const label = link.getAttribute('data-label') || 'this item';
            if (!confirm('Are you sure you want to delete ' + label + '? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // ---- Live image preview when adding / editing a property ----
    const imageInput = document.getElementById('propertyImage');
    const imagePreview = document.getElementById('imagePreview');
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function () {
            const file = imageInput.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // ---- Auto-hide success/error alerts after a few seconds ----
    document.querySelectorAll('.alert').forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.4s ease';
            alert.style.opacity = '0';
            setTimeout(function () { alert.remove(); }, 400);
        }, 4500);
    });

    // ---- Tab navigation (Reports & Analytics and Settings pages) ----
    // Reads URL hash on page load so direct links like reports.php#expenses work.
    function activateTab(tabId) {
        var sections = document.querySelectorAll('.settings-section');
        var tabLinks = document.querySelectorAll('[data-tab]');
        if (!sections.length) return;

        sections.forEach(function (s) {
            s.style.display = 'none';
            s.classList.remove('active');
        });
        tabLinks.forEach(function (a) { a.classList.remove('active'); });

        var target = document.getElementById(tabId);
        if (target) {
            target.style.display = '';
            target.classList.add('active');
        } else if (sections[0]) {
            sections[0].style.display = '';
            sections[0].classList.add('active');
        }

        var activeLink = document.querySelector('[data-tab="' + tabId + '"]');
        if (activeLink) activeLink.classList.add('active');
    }

    document.querySelectorAll('[data-tab]').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            var tabId = this.getAttribute('data-tab');
            activateTab(tabId);
            history.replaceState(null, '', '#' + tabId);
        });
    });

    // Activate tab based on URL hash on page load
    (function () {
        var tabLinks = document.querySelectorAll('[data-tab]');
        if (!tabLinks.length) return;
        var hash = window.location.hash.replace('#', '');
        if (hash) {
            activateTab(hash);
        } else {
            var first = tabLinks[0];
            if (first) activateTab(first.getAttribute('data-tab'));
        }
    })();

});
