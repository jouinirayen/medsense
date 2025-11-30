            </div>
        </main>
    </div>

    <script>
        // Mobile sidebar toggle
        const mobileToggle = document.getElementById('mobileToggle');
        const adminSidebar = document.getElementById('adminSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        mobileToggle.addEventListener('click', function() {
            adminSidebar.classList.toggle('mobile-open');
            sidebarOverlay.classList.toggle('active');
        });

        sidebarOverlay.addEventListener('click', function() {
            adminSidebar.classList.remove('mobile-open');
            sidebarOverlay.classList.remove('active');
        });

        // Close sidebar when clicking on links in mobile view
        const navLinks = document.querySelectorAll('.nav-links a');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    adminSidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('active');
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                adminSidebar.classList.remove('mobile-open');
                sidebarOverlay.classList.remove('active');
            }
        });
    </script>
</body>
</html>