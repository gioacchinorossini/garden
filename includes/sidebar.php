<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$role = isset($_SESSION['active_role']) ? $_SESSION['active_role'] : 'admin';
$current_page = basename($_SERVER['PHP_SELF']);
$bp = isset($base_path) ? $base_path : '';
?>
<!-- Mobile Backdrop for offcanvas drawer -->
<div id="sidebarBackdrop" class="fixed inset-0 bg-black/40 z-[1040] hidden transition-opacity"></div>

<aside id="mainSidebar" class="w-52 flex-shrink-0 px-2.5 py-4 flex flex-col justify-between hidden md:flex h-full bg-white border-r border-drive-border transition-all duration-300 ease-in-out select-none font-['Outfit']">
    <div class="overflow-y-auto overflow-x-hidden">
        <!-- Nav Links Section -->
        <nav class="flex flex-col gap-1">
            <?php if ($role == 'admin'): ?>
                <!-- ADMIN MENU -->
                <a href="<?php echo $bp; ?>admin/dashboard.php" title="Dashboard"
                    class="sidebar-nav-link flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'dashboard.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="layout-dashboard" class="flex-shrink-0" style="width: 18px; height: 18px;"></i>
                    <span class="sidebar-text truncate">Dashboard</span>
                </a>
                <a href="<?php echo $bp; ?>admin/users.php" title="Manage Users"
                    class="sidebar-nav-link flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'users.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="users" class="flex-shrink-0" style="width: 18px; height: 18px;"></i>
                    <span class="sidebar-text truncate">Manage Users</span>
                </a>
                <a href="<?php echo $bp; ?>admin/lands.php" title="Manage Lands"
                    class="sidebar-nav-link flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'lands.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="map" class="flex-shrink-0" style="width: 18px; height: 18px;"></i>
                    <span class="sidebar-text truncate">Manage Lands</span>
                </a>
                <a href="<?php echo $bp; ?>admin/reports.php" title="Reports"
                    class="sidebar-nav-link flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'reports.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="bar-chart-3" class="flex-shrink-0" style="width: 18px; height: 18px;"></i>
                    <span class="sidebar-text truncate">Reports</span>
                </a>

            <?php elseif ($role == 'landowner'): ?>
                <!-- LANDOWNER MENU -->
                <a href="<?php echo $bp; ?>landowner/dashboard.php" title="Gardens Map"
                    class="sidebar-nav-link flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'dashboard.php' || $current_page == 'map.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="map-pin" class="flex-shrink-0" style="width: 18px; height: 18px;"></i>
                    <span class="sidebar-text truncate">Gardens Map</span>
                </a>
                <a href="<?php echo $bp; ?>landowner/register.php" title="Register Land"
                    class="sidebar-nav-link flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'register.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="plus-circle" class="flex-shrink-0" style="width: 18px; height: 18px;"></i>
                    <span class="sidebar-text truncate">Register Land</span>
                </a>
                <a href="<?php echo $bp; ?>landowner/lands.php" title="My Lands"
                    class="sidebar-nav-link flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'lands.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="trees" class="flex-shrink-0" style="width: 18px; height: 18px;"></i>
                    <span class="sidebar-text truncate">My Lands</span>
                </a>
                <a href="<?php echo $bp; ?>landowner/requests.php" title="Requests"
                    class="sidebar-nav-link flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'requests.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="file-text" class="flex-shrink-0" style="width: 18px; height: 18px;"></i>
                    <span class="sidebar-text truncate">Requests</span>
                </a>
                <a href="<?php echo $bp; ?>landowner/schedules.php" title="Schedules"
                    class="sidebar-nav-link flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'schedules.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="calendar" class="flex-shrink-0" style="width: 18px; height: 18px;"></i>
                    <span class="sidebar-text truncate">Schedules</span>
                </a>
                <a href="<?php echo $bp; ?>landowner/profile.php" title="My Profile"
                    class="sidebar-nav-link flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'profile.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="user" class="flex-shrink-0" style="width: 18px; height: 18px;"></i>
                    <span class="sidebar-text truncate">My Profile</span>
                </a>

            <?php elseif ($role == 'gardener'): ?>
                <!-- GARDENER MENU -->
                <a href="<?php echo $bp; ?>gardener/dashboard.php" title="Overview"
                    class="sidebar-nav-link flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'dashboard.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="home" class="flex-shrink-0" style="width: 18px; height: 18px;"></i>
                    <span class="sidebar-text truncate">Overview</span>
                </a>
                <a href="<?php echo $bp; ?>gardener/search.php" title="Search Lands"
                    class="sidebar-nav-link flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'search.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="search" class="flex-shrink-0" style="width: 18px; height: 18px;"></i>
                    <span class="sidebar-text truncate">Search Lands</span>
                </a>
                <a href="<?php echo $bp; ?>gardener/browse.php" title="Browse Lands"
                    class="sidebar-nav-link flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'browse.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="compass" class="flex-shrink-0" style="width: 18px; height: 18px;"></i>
                    <span class="sidebar-text truncate">Browse Lands</span>
                </a>
                <a href="<?php echo $bp; ?>gardener/map.php" title="Map View"
                    class="sidebar-nav-link flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'map.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="map-pin" class="flex-shrink-0" style="width: 18px; height: 18px;"></i>
                    <span class="sidebar-text truncate">Map View</span>
                </a>
                <a href="<?php echo $bp; ?>gardener/requests.php" title="My Requests"
                    class="sidebar-nav-link flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'requests.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="send" class="flex-shrink-0" style="width: 18px; height: 18px;"></i>
                    <span class="sidebar-text truncate">My Requests</span>
                </a>
                <a href="<?php echo $bp; ?>gardener/schedules.php" title="Schedules"
                    class="sidebar-nav-link flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'schedules.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="calendar-check" class="flex-shrink-0" style="width: 18px; height: 18px;"></i>
                    <span class="sidebar-text truncate">Schedules</span>
                </a>
                <a href="<?php echo $bp; ?>gardener/harvests.php" title="Record Harvest"
                    class="sidebar-nav-link flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'harvests.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="shopping-bag" class="flex-shrink-0" style="width: 18px; height: 18px;"></i>
                    <span class="sidebar-text truncate">Record Harvest</span>
                </a>
            <?php endif; ?>
        </nav>
    </div>

    <!-- Bottom Collapse Toggle (Desktop) -->
    <div class="pt-3 border-t border-drive-border mt-auto">
        <button type="button" id="sidebarCollapseBtn"
            class="flex items-center gap-4 w-full px-4 h-10 rounded-full text-xs font-semibold text-drive-text-muted hover:text-drive-text-main hover:bg-drive-surface-hover transition-all text-decoration-none cursor-pointer border-0 bg-transparent"
            title="Collapse Sidebar">
            <i class="bi bi-chevron-left sidebar-collapse-icon text-sm transition-transform duration-300 flex-shrink-0"></i>
            <span class="sidebar-text truncate">Collapse</span>
        </button>
    </div>
</aside>

<!-- Instant State Hydration to prevent layout flash -->
<script>
    (function () {
        if (localStorage.getItem('sidebar_collapsed') === 'true' && window.innerWidth >= 768) {
            var s = document.getElementById('mainSidebar');
            if (s) s.classList.add('sidebar-collapsed');
        }
    })();
</script>