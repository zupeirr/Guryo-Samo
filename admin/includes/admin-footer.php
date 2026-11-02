    </div><!-- /.admin-content — closes the main content area opened in admin-header.php -->
</div><!-- /.admin-main — closes the main wrapper column opened in admin-header.php -->

<!-- ── SCRIPTS ──────────────────────────────────────────────────────────── -->

<!-- Lucide Icons library — converts <i data-lucide="icon-name"> tags into inline SVG icons.
     Must load BEFORE lucide.createIcons() is called below. -->
<script src="../assets/js/lucide.min.js"></script>
<script>
  // Initialize all Lucide icons on the page.
  // This scans the DOM for data-lucide attributes and replaces them with SVG.
  lucide.createIcons();
</script>

<!-- Admin-specific JavaScript — loads AFTER icons so all DOM elements are ready.
     ?v=timestamp forces the browser to always fetch the latest version (cache-busting). -->
<script src="../assets/js/admin.js?v=2.0"></script>

<!-- Real-time notifications polling -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const badge = document.getElementById('sidebarMsgBadge');
    if (!badge) return;
    
    function checkNotifications() {
        fetch('api/notification-count.php')
            .then(res => res.json())
            .then(data => {
                if (data.unread > 0) {
                    badge.textContent = data.unread;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            })
            .catch(err => console.error('Error fetching notifications:', err));
    }
    
    // Check immediately, then every 30 seconds
    checkNotifications();
    setInterval(checkNotifications, 30000);
});
</script>
</body>
</html>
