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
});
