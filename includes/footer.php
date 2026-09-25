</div> <!-- /main-layout -->
</div> <!-- /app-container -->

<?php
$bottom_role = isset($_SESSION['active_role']) ? $_SESSION['active_role'] : 'admin';
$bottom_page = basename($_SERVER['PHP_SELF']);

function get_dock_link_class($page_name, $bottom_page)
{
    $active = ($bottom_page == $page_name);
    $base_classes = "flex flex-col items-center text-decoration-none text-[10px] font-medium transition-all px-2.5 py-1 rounded-full gap-0.5 hover:text-drive-text-main hover:translate-y-[-2px] duration-150";
    return $active
        ? "$base_classes bg-drive-surface-active text-drive-primary-text font-semibold"
        : "$base_classes text-drive-text-sub hover:bg-drive-surface-hover";
}
?>
<!-- Floating Bottom Navigation Dock (Visible on all devices) -->
<div
    class="fixed bottom-4 md:bottom-8 left-1/2 -translate-x-1/2 bg-white/90 backdrop-blur-lg border border-drive-border rounded-full shadow-2xl px-4 py-2 z-50 flex items-center gap-1 md:gap-3 transition-all duration-300 max-w-[95vw] md:max-w-none block md:hidden">
    <?php
    $base = isset($base_path) ? $base_path : '';
    if ($bottom_role == 'admin'):
        ?>
        <a href="<?php echo $base; ?>admin/dashboard.php"
            class="<?php echo get_dock_link_class('dashboard.php', $bottom_page); ?>">
            <i data-lucide="layout-dashboard" style="width: 20px; height: 20px;"></i>
            <span class="hidden sm:inline">Dashboard</span>
        </a>
        <a href="<?php echo $base; ?>admin/users.php" class="<?php echo get_dock_link_class('users.php', $bottom_page); ?>">
            <i data-lucide="users" style="width: 20px; height: 20px;"></i>
            <span class="hidden sm:inline">Users</span>
        </a>
        <!-- Action Trigger -->
        <button
            class="w-11 h-11 bg-drive-primary text-white rounded-full flex items-center justify-center shadow-md hover:scale-105 hover:bg-drive-primary-hover active:scale-95 transition-all flex-shrink-0"
            data-bs-toggle="modal" data-bs-target="#addUserModal" title="New User">
            <i data-lucide="plus" style="width: 20px; height: 20px;"></i>
        </button>
        <a href="<?php echo $base; ?>admin/lands.php" class="<?php echo get_dock_link_class('lands.php', $bottom_page); ?>">
            <i data-lucide="map" style="width: 20px; height: 20px;"></i>
            <span class="hidden sm:inline">Lands</span>
        </a>
        <a href="<?php echo $base; ?>admin/reports.php"
            class="<?php echo get_dock_link_class('reports.php', $bottom_page); ?>">
            <i data-lucide="bar-chart-3" style="width: 20px; height: 20px;"></i>
            <span class="hidden sm:inline">Reports</span>
        </a>
    <?php elseif ($bottom_role == 'landowner'): ?>
        <a href="<?php echo $base; ?>landowner/dashboard.php"
            class="<?php echo get_dock_link_class('dashboard.php', $bottom_page); ?>">
            <i data-lucide="map-pin" style="width: 20px; height: 20px;"></i>
            <span class="hidden sm:inline">Gardens Map</span>
        </a>
        <a href="<?php echo $base; ?>landowner/lands.php"
            class="<?php echo get_dock_link_class('lands.php', $bottom_page); ?>">
            <i data-lucide="trees" style="width: 20px; height: 20px;"></i>
            <span class="hidden sm:inline">My Lands</span>
        </a>
        <!-- Action Trigger -->
        <a href="<?php echo $base; ?>landowner/register.php"
            class="w-11 h-11 bg-drive-primary text-white rounded-full flex items-center justify-center shadow-md hover:scale-105 hover:bg-drive-primary-hover active:scale-95 transition-all flex-shrink-0 text-decoration-none"
            title="Register Land">
            <i data-lucide="plus" style="width: 20px; height: 20px;"></i>
        </a>
        <a href="<?php echo $base; ?>landowner/requests.php"
            class="<?php echo get_dock_link_class('requests.php', $bottom_page); ?>">
            <i data-lucide="file-text" style="width: 20px; height: 20px;"></i>
            <span class="hidden sm:inline">Requests</span>
        </a>
        <a href="<?php echo $base; ?>landowner/schedules.php"
            class="<?php echo get_dock_link_class('schedules.php', $bottom_page); ?>">
            <i data-lucide="calendar" style="width: 20px; height: 20px;"></i>
            <span class="hidden sm:inline">Schedules</span>
        </a>
    <?php elseif ($bottom_role == 'gardener'): ?>
        <a href="<?php echo $base; ?>gardener/dashboard.php"
            class="<?php echo get_dock_link_class('dashboard.php', $bottom_page); ?>">
            <i data-lucide="home" style="width: 20px; height: 20px;"></i>
            <span class="hidden sm:inline">Overview</span>
        </a>
        <a href="<?php echo $base; ?>gardener/map.php" class="<?php echo get_dock_link_class('map.php', $bottom_page); ?>">
            <i data-lucide="map-pin" style="width: 20px; height: 20px;"></i>
            <span class="hidden sm:inline">Map</span>
        </a>
        <a href="<?php echo $base; ?>gardener/search.php"
            class="<?php echo get_dock_link_class('search.php', $bottom_page); ?>">
            <i data-lucide="search" style="width: 20px; height: 20px;"></i>
            <span class="hidden sm:inline">Search</span>
        </a>
        <a href="<?php echo $base; ?>gardener/requests.php"
            class="<?php echo get_dock_link_class('requests.php', $bottom_page); ?>">
            <i data-lucide="send" style="width: 20px; height: 20px;"></i>
            <span class="hidden sm:inline">Requests</span>
        </a>
        <a href="<?php echo $base; ?>gardener/schedules.php"
            class="<?php echo get_dock_link_class('schedules.php', $bottom_page); ?>">
            <i data-lucide="calendar-check" style="width: 20px; height: 20px;"></i>
            <span class="hidden sm:inline">Schedules</span>
        </a>
        <a href="<?php echo $base; ?>gardener/harvests.php"
            class="<?php echo get_dock_link_class('harvests.php', $bottom_page); ?>">
            <i data-lucide="shopping-bag" style="width: 20px; height: 20px;"></i>
            <span class="hidden sm:inline">Harvests</span>
        </a>
    <?php endif; ?>
</div>



<?php $base = isset($base_path) ? $base_path : ''; ?>
<!-- Bootstrap Bundle with Popper JS -->
<script src="<?php echo $base; ?>assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>

<!-- Leaflet.js Map script (Loads standard OpenStreetMap map) -->
<script src="<?php echo $base; ?>assets/vendor/leaflet/leaflet.js"></script>

<!-- Initialize Tooltips and Mobile Navigation Toggle -->
<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

    // Collapsible Sidebar & Mobile Navigation Controller
    document.addEventListener("DOMContentLoaded", function () {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        const sidebar = document.getElementById('mainSidebar');
        const toggleBtn = document.getElementById('sidebarToggleBtn');
        const collapseBtn = document.getElementById('sidebarCollapseBtn');
        const backdrop = document.getElementById('sidebarBackdrop');

        function updateCollapseBtnUI(isCollapsed) {
            if (!collapseBtn) return;
            const textSpan = collapseBtn.querySelector('.sidebar-text');
            if (textSpan) {
                textSpan.textContent = isCollapsed ? 'Expand' : 'Collapse';
            }
            collapseBtn.title = isCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar';
        }

        function toggleSidebarCollapse() {
            if (!sidebar) return;
            const isCollapsed = sidebar.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebar_collapsed', isCollapsed ? 'true' : 'false');
            updateCollapseBtnUI(isCollapsed);

            // Re-trigger window resize so Leaflet/3D maps adapt instantly
            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 260);
        }

        function toggleMobileSidebar() {
            if (!sidebar || !backdrop) return;
            const isOpen = sidebar.classList.toggle('mobile-open');
            backdrop.classList.toggle('show', isOpen);
            backdrop.classList.toggle('hidden', !isOpen);
        }

        function closeMobileSidebar() {
            if (!sidebar || !backdrop) return;
            sidebar.classList.remove('mobile-open');
            backdrop.classList.remove('show');
            backdrop.classList.add('hidden');
        }

        // Initialize state on load
        if (sidebar && window.innerWidth >= 768) {
            const savedState = localStorage.getItem('sidebar_collapsed') === 'true';
            sidebar.classList.toggle('sidebar-collapsed', savedState);
            updateCollapseBtnUI(savedState);
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                if (window.innerWidth < 768) {
                    toggleMobileSidebar();
                } else {
                    toggleSidebarCollapse();
                }
            });
        }

        if (collapseBtn) {
            collapseBtn.addEventListener('click', toggleSidebarCollapse);
        }

        if (backdrop) {
            backdrop.addEventListener('click', closeMobileSidebar);
        }

        // Auto close mobile drawer when clicking a nav link
        if (sidebar) {
            sidebar.querySelectorAll('.sidebar-nav-link').forEach(link => {
                link.addEventListener('click', function () {
                    if (window.innerWidth < 768) {
                        closeMobileSidebar();
                    }
                });
            });
        }
    });

    // Dynamic Stacked/Nested Bootstrap Modals Handler
    document.addEventListener('show.bs.modal', function (event) {
        const modal = event.target;
        if (!modal.classList.contains('modal')) return;
        const openModals = Array.from(document.querySelectorAll('.modal.show')).filter(m => m !== modal);
        if (openModals.length > 0) {
            const baseZIndex = 1055;
            const zIndex = baseZIndex + (openModals.length * 20);
            modal.style.zIndex = zIndex;
            setTimeout(() => {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                if (backdrops.length > 1) {
                    const currentBackdrop = backdrops[backdrops.length - 1];
                    currentBackdrop.style.zIndex = zIndex - 5;
                }
            }, 10);
        }
    });

    document.addEventListener('hidden.bs.modal', function (event) {
        const modal = event.target;
        if (modal && modal.classList.contains('modal')) {
            modal.style.zIndex = '';
        }
        const openModals = document.querySelectorAll('.modal.show');
        if (openModals.length > 0) {
            document.body.classList.add('modal-open');
        }
    });

    // Keep header fully opaque when a header dropdown is active
    document.addEventListener('show.bs.dropdown', function (e) {
        const header = document.getElementById('mainHeader');
        if (header && header.contains(e.target)) {
            header.classList.add('focus-active');
        }
    });
    document.addEventListener('hidden.bs.dropdown', function (e) {
        const header = document.getElementById('mainHeader');
        if (header) {
            header.classList.remove('focus-active');
        }
    });
</script>

</body>

</html>