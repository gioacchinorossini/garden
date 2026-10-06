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
<div id="mobileBottomNavDock"
    class="mobile-bottom-nav-dock fixed bottom-4 md:bottom-8 left-1/2 -translate-x-1/2 bg-white/90 backdrop-blur-lg border border-drive-border rounded-full shadow-2xl px-4 py-2 z-50 flex items-center gap-1 md:gap-3 transition-all duration-300 max-w-[95vw] md:max-w-none block md:hidden">
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
            <span class="hidden sm:inline">Gardens</span>
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
        <a href="<?php echo $base; ?>landowner/profile.php"
            class="<?php echo get_dock_link_class('profile.php', $bottom_page); ?>">
            <i data-lucide="user" style="width: 20px; height: 20px;"></i>
            <span class="hidden sm:inline">Profile</span>
        </a>
    <?php elseif ($bottom_role == 'gardener'): ?>
        <a href="<?php echo $base; ?>gardener/dashboard.php"
            class="<?php echo get_dock_link_class('dashboard.php', $bottom_page); ?>">
            <i data-lucide="trees" style="width: 20px; height: 20px;"></i>
            <span class="hidden sm:inline">Gardens</span>
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
        <a href="<?php echo $base; ?>gardener/profile.php"
            class="<?php echo get_dock_link_class('profile.php', $bottom_page); ?>">
            <i data-lucide="user" style="width: 20px; height: 20px;"></i>
            <span class="hidden sm:inline">Profile</span>
        </a>
    <?php endif; ?>

    <!-- Notifications Dropup in Footer (Mobile) -->
    <div class="dropup flex flex-col items-center">
        <button type="button"
            class="flex flex-col items-center text-decoration-none text-[10px] font-medium transition-all px-2.5 py-1 rounded-full gap-0.5 text-drive-text-sub hover:bg-drive-surface-hover hover:text-drive-text-main border-0 bg-transparent relative cursor-pointer"
            id="mobileFooterNotificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false"
            title="Notifications">
            <div class="relative">
                <i data-lucide="bell" style="width: 20px; height: 20px;"></i>
                <span class="absolute -top-0.5 -right-0.5 flex h-2 w-2">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-600"></span>
                </span>
            </div>
            <span class="hidden sm:inline">Alerts</span>
        </button>
        <div class="dropdown-menu dropdown-menu-end border border-drive-border rounded-2xl p-0 shadow-2xl overflow-hidden mb-3"
            style="width: 310px; max-width: 90vw; z-index: 1060;" aria-labelledby="mobileFooterNotificationsDropdown">
            <!-- Dropdown Header -->
            <div
                class="flex items-center justify-between px-3.5 py-2.5 border-b border-drive-border bg-drive-canvas/40">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-drive-text-main font-['Outfit']">Notifications</span>
                    <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-100 text-emerald-800">3
                        new</span>
                </div>
                <button type="button"
                    class="text-[11px] text-drive-text-muted hover:text-drive-primary border-0 bg-transparent p-0 cursor-pointer font-medium"
                    onclick="event.stopPropagation(); this.closest('.dropdown-menu').querySelectorAll('.notification-unread-dot').forEach(d => d.remove()); this.textContent = 'All read';">
                    Mark read
                </button>
            </div>

            <!-- Notifications List -->
            <div class="divide-y divide-drive-border/50 max-h-[260px] overflow-y-auto">
                <!-- Notification 1 -->
                <a href="<?php echo $base; ?><?php echo $bottom_role === 'gardener' ? 'gardener/requests.php' : 'landowner/requests.php'; ?>"
                    class="flex items-start gap-2.5 px-3.5 py-2.5 hover:bg-drive-surface-hover text-decoration-none transition-colors group">
                    <div
                        class="w-7 h-7 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 mt-0.5 text-xs">
                        <i class="bi bi-envelope-paper"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p
                            class="text-xs text-drive-text-main font-semibold mb-0.5 truncate group-hover:text-drive-primary">
                            <?php echo $bottom_role === 'gardener' ? 'Plot Request Approved' : 'New Plot Request'; ?>
                        </p>
                        <p class="text-[11px] text-drive-text-muted mb-1 line-clamp-2 leading-tight">
                            <?php echo $bottom_role === 'gardener' ? 'Your request for Plot #2 at Sunnyvale was accepted.' : 'Mary Gardener requested Plot #2 at Sunnyvale Garden.'; ?>
                        </p>
                        <span class="text-[10px] text-drive-text-muted font-medium">10 mins ago</span>
                    </div>
                    <span
                        class="notification-unread-dot w-1.5 h-1.5 rounded-full bg-emerald-600 flex-shrink-0 mt-1.5"></span>
                </a>

                <!-- Notification 2 -->
                <a href="<?php echo $base; ?><?php echo $bottom_role === 'gardener' ? 'gardener/schedules.php' : ($bottom_role === 'landowner' ? 'landowner/schedules.php' : 'admin/dashboard.php'); ?>"
                    class="flex items-start gap-2.5 px-3.5 py-2.5 hover:bg-drive-surface-hover text-decoration-none transition-colors group">
                    <div
                        class="w-7 h-7 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0 mt-0.5 text-xs">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p
                            class="text-xs text-drive-text-main font-semibold mb-0.5 truncate group-hover:text-drive-primary">
                            Gardening Schedule Reminder
                        </p>
                        <p class="text-[11px] text-drive-text-muted mb-1 line-clamp-2 leading-tight">
                            Soil preparation and weeding routine set for Saturday 8:00 AM.
                        </p>
                        <span class="text-[10px] text-drive-text-muted font-medium">2 hours ago</span>
                    </div>
                    <span
                        class="notification-unread-dot w-1.5 h-1.5 rounded-full bg-emerald-600 flex-shrink-0 mt-1.5"></span>
                </a>

                <!-- Notification 3 -->
                <a href="<?php echo $base; ?><?php echo $bottom_role === 'landowner' ? 'landowner/lands.php' : ($bottom_role === 'gardener' ? 'gardener/dashboard.php' : 'admin/lands.php'); ?>"
                    class="flex items-start gap-2.5 px-3.5 py-2.5 hover:bg-drive-surface-hover text-decoration-none transition-colors group">
                    <div
                        class="w-7 h-7 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0 mt-0.5 text-xs">
                        <i class="bi bi-patch-check"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p
                            class="text-xs text-drive-text-main font-semibold mb-0.5 truncate group-hover:text-drive-primary">
                            Land Registration Approved
                        </p>
                        <p class="text-[11px] text-drive-text-muted mb-1 line-clamp-2 leading-tight">
                            Riverdale Acres registration approved and listed on public map.
                        </p>
                        <span class="text-[10px] text-drive-text-muted font-medium">1 day ago</span>
                    </div>
                    <span
                        class="notification-unread-dot w-1.5 h-1.5 rounded-full bg-emerald-600 flex-shrink-0 mt-1.5"></span>
                </a>
            </div>

            <!-- Dropdown Footer -->
            <div class="p-1.5 bg-drive-canvas/20 border-t border-drive-border text-center">
                <a href="<?php echo $base; ?><?php echo $bottom_role === 'gardener' ? 'gardener/requests.php' : ($bottom_role === 'landowner' ? 'landowner/requests.php' : 'admin/dashboard.php'); ?>"
                    class="text-[11px] font-semibold text-drive-primary hover:underline text-decoration-none block py-1">
                    View All Activity
                </a>
            </div>
        </div>
    </div>
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
                if (window.innerWidth >= 768) {
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