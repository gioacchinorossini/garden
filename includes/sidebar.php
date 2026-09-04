<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$role = isset($_SESSION['active_role']) ? $_SESSION['active_role'] : 'admin';
$current_page = basename($_SERVER['PHP_SELF']);
$bp = isset($base_path) ? $base_path : '';
?>
<!-- Left Sidebar -->
<aside class="w-64 flex-shrink-0 px-3 py-4 flex flex-col justify-between hidden md:flex h-full bg-white border-r border-drive-border">
    <div>
        <!-- Floating + New Action Button (Dynamic based on role)
        <div class="mb-4">
            <?php if ($role == 'admin'): ?>
                <button
                    class="flex items-center gap-3 bg-white hover:bg-drive-surface-hover text-drive-text-main font-semibold px-5 py-3 rounded-2xl  hover:shadow-md transition-all border border-drive-border/50 ms-2"
                    data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-plus-lg fs-4 text-drive-primary"></i>
                    <span class="text-sm font-semibold">New User</span>
                </button>
            <?php elseif ($role == 'landowner'): ?>
                <a href="<?php echo $bp; ?>landowner/register.php"
                    class="flex items-center gap-3 bg-white hover:bg-drive-surface-hover text-drive-text-main font-semibold px-5 py-3 rounded-2xl  hover:shadow-md transition-all border border-drive-border/50 ms-2 text-decoration-none">
                    <i class="bi bi-plus-lg fs-4 text-drive-primary"></i>
                    <span class="text-sm font-semibold">Register Land</span>
                </a>
            <?php else: ?>
                <a href="<?php echo $bp; ?>gardener/browse.php"
                    class="flex items-center gap-3 bg-white hover:bg-drive-surface-hover text-drive-text-main font-semibold px-5 py-3 rounded-2xl  hover:shadow-md transition-all border border-drive-border/50 ms-2 text-decoration-none">
                    <i class="bi bi-plus-lg fs-4 text-drive-primary"></i>
                    <span class="text-sm font-semibold">Request Plot</span>
                </a>
            <?php endif; ?>
        </div> -->

        <!-- Nav Links Section -->
        <nav class="flex flex-col gap-1">
            <?php if ($role == 'admin'): ?>
                <!-- ADMIN MENU -->
                <a href="<?php echo $bp; ?>admin/dashboard.php"
                    class="flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'dashboard.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="layout-dashboard" style="width: 18px; height: 18px;"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?php echo $bp; ?>admin/users.php"
                    class="flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'users.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="users" style="width: 18px; height: 18px;"></i>
                    <span>Manage Users</span>
                </a>
                <a href="<?php echo $bp; ?>admin/lands.php"
                    class="flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'lands.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="map" style="width: 18px; height: 18px;"></i>
                    <span>Manage Lands</span>
                </a>
                <a href="<?php echo $bp; ?>admin/reports.php"
                    class="flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'reports.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="bar-chart-3" style="width: 18px; height: 18px;"></i>
                    <span>Reports</span>
                </a>

            <?php elseif ($role == 'landowner'): ?>
                <!-- LANDOWNER MENU -->
                <a href="<?php echo $bp; ?>landowner/dashboard.php"
                    class="flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'dashboard.php' || $current_page == 'map.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="map-pin" style="width: 18px; height: 18px;"></i>
                    <span>Gardens Map</span>
                </a>
                <a href="<?php echo $bp; ?>landowner/register.php"
                    class="flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'register.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i>
                    <span>Register Land</span>
                </a>
                <a href="<?php echo $bp; ?>landowner/lands.php"
                    class="flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'lands.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="trees" style="width: 18px; height: 18px;"></i>
                    <span>My Lands</span>
                </a>
                <a href="<?php echo $bp; ?>landowner/requests.php"
                    class="flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'requests.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="file-text" style="width: 18px; height: 18px;"></i>
                    <span>Requests</span>
                </a>
                <a href="<?php echo $bp; ?>landowner/schedules.php"
                    class="flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'schedules.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="calendar" style="width: 18px; height: 18px;"></i>
                    <span>Schedules</span>
                </a>
                <a href="<?php echo $bp; ?>landowner/profile.php"
                    class="flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'profile.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="user" style="width: 18px; height: 18px;"></i>
                    <span>My Profile</span>
                </a>

            <?php elseif ($role == 'gardener'): ?>
                <!-- GARDENER MENU -->
                <a href="<?php echo $bp; ?>gardener/dashboard.php"
                    class="flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'dashboard.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="home" style="width: 18px; height: 18px;"></i>
                    <span>Overview</span>
                </a>
                <a href="<?php echo $bp; ?>gardener/search.php"
                    class="flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'search.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="search" style="width: 18px; height: 18px;"></i>
                    <span>Search Lands</span>
                </a>
                <a href="<?php echo $bp; ?>gardener/browse.php"
                    class="flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'browse.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="compass" style="width: 18px; height: 18px;"></i>
                    <span>Browse Lands</span>
                </a>
                <a href="<?php echo $bp; ?>gardener/map.php"
                    class="flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'map.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="map-pin" style="width: 18px; height: 18px;"></i>
                    <span>Map View</span>
                </a>
                <a href="<?php echo $bp; ?>gardener/requests.php"
                    class="flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'requests.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="send" style="width: 18px; height: 18px;"></i>
                    <span>My Requests</span>
                </a>
                <a href="<?php echo $bp; ?>gardener/schedules.php"
                    class="flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'schedules.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="calendar-check" style="width: 18px; height: 18px;"></i>
                    <span>Schedules</span>
                </a>
                <a href="<?php echo $bp; ?>gardener/harvests.php"
                    class="flex items-center gap-4 px-4 h-10 rounded-full text-sm transition-colors text-decoration-none <?php echo ($current_page == 'harvests.php') ? 'bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover font-medium'; ?>">
                    <i data-lucide="shopping-bag" style="width: 18px; height: 18px;"></i>
                    <span>Record Harvest</span>
                </a>
            <?php endif; ?>
        </nav>
    </div>
</aside>