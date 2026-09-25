<?php
// Retrieve active role for frontend switching (default to admin for now)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['active_role'])) {
    $_SESSION['active_role'] = 'admin';
}
$role = $_SESSION['active_role'];
$bp = isset($base_path) ? $base_path : '';

if (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = 'System Administrator';
}

if (!isset($_SESSION['user_id'])) {
    if ($_SESSION['active_role'] === 'admin') {
        $_SESSION['user_id'] = 1;
    } elseif ($_SESSION['active_role'] === 'landowner') {
        $_SESSION['user_id'] = 2;
    } else {
        $_SESSION['user_id'] = 3;
    }
}

// Handle role switcher action
if (isset($_GET['switch_role'])) {
    $role = $_GET['switch_role'];
    if (in_array($role, ['admin', 'landowner', 'gardener'])) {
        $_SESSION['active_role'] = $role;
        $base = isset($base_path) ? $base_path : '';
        if ($role == 'admin') {
            $_SESSION['user_id'] = 1;
            $_SESSION['user_name'] = 'System Administrator';
            header("Location: " . $base . "admin/dashboard.php");
            exit;
        } elseif ($role == 'landowner') {
            $_SESSION['user_id'] = 2;
            $_SESSION['user_name'] = 'John Landowner';
            header("Location: " . $base . "landowner/dashboard.php");
            exit;
        } elseif ($role == 'gardener') {
            $_SESSION['user_id'] = 3;
            $_SESSION['user_name'] = 'Mary Gardener';
            header("Location: " . $base . "gardener/dashboard.php");
            exit;
        }
    }
}
?>
<header id="mainHeader" class="flex items-center justify-between px-3 md:px-4 bg-white border-b border-drive-border relative z-[1050] transition-opacity duration-300 ease-in-out" style="height: 3.25rem;">
    <!-- Left: Branding & Sidebar Toggle -->
    <div class="flex items-center gap-2">
        <button type="button" id="sidebarToggleBtn"
            class="p-1.5 rounded-full text-drive-text-sub hover:bg-drive-surface-hover hover:text-drive-primary transition-all flex items-center justify-center cursor-pointer border-0 bg-transparent"
            title="Toggle Sidebar" aria-label="Toggle Sidebar">
            <i class="bi bi-list text-xl leading-none"></i>
        </button>
        <a href="<?php echo $bp; ?>index.php" class="flex items-center gap-2 text-decoration-none">
            <img src="<?php echo $bp; ?>logo.jpeg" alt="IdleLand Logo" class="h-7 w-7 object-contain rounded-full shadow-xs">
            <span class="text-lg font-semibold text-drive-text-main font-['Outfit'] tracking-tight">
                <span class="text-drive-primary">Idle</span>Land
            </span>
        </a>
    </div>

    <!-- Right: Notifications & Profile -->
    <div class="flex items-center gap-1.5 md:gap-2">
        <!-- Notifications Button & Dropdown -->
        <div class="dropdown">
            <button
                class="relative p-0 border-0 bg-transparent rounded-full flex items-center justify-center text-drive-text-sub hover:bg-drive-surface-hover hover:text-drive-primary transition-all focus:outline-none"
                style="width: 32px; height: 32px;"
                type="button" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                <i class="bi bi-bell text-base leading-none"></i>
                <!-- Notification Ping Indicator -->
                <span class="absolute top-1 right-1 flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-600"></span>
                </span>
            </button>
            <div class="dropdown-menu dropdown-menu-end border border-drive-border rounded-2xl p-0 shadow-lg overflow-hidden"
                style="width: 310px; max-width: 90vw;" aria-labelledby="notificationsDropdown">
                <!-- Dropdown Header -->
                <div class="flex items-center justify-between px-3.5 py-2.5 border-b border-drive-border bg-drive-canvas/40">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-drive-text-main font-['Outfit']">Notifications</span>
                        <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-100 text-emerald-800">3 new</span>
                    </div>
                    <button type="button" class="text-[11px] text-drive-text-muted hover:text-drive-primary border-0 bg-transparent p-0 cursor-pointer font-medium"
                        onclick="event.stopPropagation(); this.closest('.dropdown-menu').querySelectorAll('.notification-unread-dot').forEach(d => d.remove()); this.textContent = 'All read';">
                        Mark read
                    </button>
                </div>

                <!-- Notifications List -->
                <div class="divide-y divide-drive-border/50 max-h-[300px] overflow-y-auto">
                    <!-- Notification 1 -->
                    <a href="<?php echo $bp; ?><?php echo $role === 'gardener' ? 'gardener/requests.php' : 'landowner/requests.php'; ?>"
                        class="flex items-start gap-2.5 px-3.5 py-2.5 hover:bg-drive-surface-hover text-decoration-none transition-colors group">
                        <div class="w-7 h-7 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 mt-0.5 text-xs">
                            <i class="bi bi-envelope-paper"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-drive-text-main font-semibold mb-0.5 truncate group-hover:text-drive-primary">
                                <?php echo $role === 'gardener' ? 'Plot Request Approved' : 'New Plot Request'; ?>
                            </p>
                            <p class="text-[11px] text-drive-text-muted mb-1 line-clamp-2 leading-tight">
                                <?php echo $role === 'gardener' ? 'Your request for Plot #2 at Sunnyvale was accepted.' : 'Mary Gardener requested Plot #2 at Sunnyvale Garden.'; ?>
                            </p>
                            <span class="text-[10px] text-drive-text-muted font-medium">10 mins ago</span>
                        </div>
                        <span class="notification-unread-dot w-1.5 h-1.5 rounded-full bg-emerald-600 flex-shrink-0 mt-1.5"></span>
                    </a>

                    <!-- Notification 2 -->
                    <a href="<?php echo $bp; ?><?php echo $role === 'gardener' ? 'gardener/schedules.php' : ($role === 'landowner' ? 'landowner/schedules.php' : 'admin/dashboard.php'); ?>"
                        class="flex items-start gap-2.5 px-3.5 py-2.5 hover:bg-drive-surface-hover text-decoration-none transition-colors group">
                        <div class="w-7 h-7 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0 mt-0.5 text-xs">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-drive-text-main font-semibold mb-0.5 truncate group-hover:text-drive-primary">
                                Gardening Schedule Reminder
                            </p>
                            <p class="text-[11px] text-drive-text-muted mb-1 line-clamp-2 leading-tight">
                                Soil preparation and weeding routine set for Saturday 8:00 AM.
                            </p>
                            <span class="text-[10px] text-drive-text-muted font-medium">2 hours ago</span>
                        </div>
                        <span class="notification-unread-dot w-1.5 h-1.5 rounded-full bg-emerald-600 flex-shrink-0 mt-1.5"></span>
                    </a>

                    <!-- Notification 3 -->
                    <a href="<?php echo $bp; ?><?php echo $role === 'landowner' ? 'landowner/my_lands.php' : ($role === 'gardener' ? 'gardener/dashboard.php' : 'admin/lands.php'); ?>"
                        class="flex items-start gap-2.5 px-3.5 py-2.5 hover:bg-drive-surface-hover text-decoration-none transition-colors group">
                        <div class="w-7 h-7 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0 mt-0.5 text-xs">
                            <i class="bi bi-patch-check"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-drive-text-main font-semibold mb-0.5 truncate group-hover:text-drive-primary">
                                Land Title Verified
                            </p>
                            <p class="text-[11px] text-drive-text-muted mb-1 line-clamp-2 leading-tight">
                                Riverdale Acres registration approved and listed on public map.
                            </p>
                            <span class="text-[10px] text-drive-text-muted font-medium">1 day ago</span>
                        </div>
                        <span class="notification-unread-dot w-1.5 h-1.5 rounded-full bg-emerald-600 flex-shrink-0 mt-1.5"></span>
                    </a>
                </div>

                <!-- Dropdown Footer -->
                <div class="p-1.5 bg-drive-canvas/20 border-t border-drive-border text-center">
                    <a href="<?php echo $bp; ?><?php echo $role === 'gardener' ? 'gardener/requests.php' : ($role === 'landowner' ? 'landowner/requests.php' : 'admin/dashboard.php'); ?>"
                        class="text-[11px] font-semibold text-drive-primary hover:underline text-decoration-none block py-1">
                        View All Activity
                    </a>
                </div>
            </div>
        </div>

        <!-- User Profile Dropdown -->
        <div class="dropdown">
            <button
                class="btn p-0 border-0 bg-transparent rounded-full flex items-center justify-center focus:outline-none transition-transform active:scale-95"
                type="button" id="userProfileDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Account & Switch Role">
                <div class="text-white rounded-full flex items-center justify-center font-bold text-xs bg-drive-primary hover:opacity-90 transition-all shadow-xs ring-2 ring-transparent hover:ring-drive-primary/30"
                    style="width: 32px; height: 32px;">
                    <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end border border-drive-border rounded-2xl p-2 shadow-lg"
                style="min-width: 240px;" aria-labelledby="userProfileDropdown">
                <!-- User Summary -->
                <li class="px-3 py-2 border-b border-drive-border mb-1">
                    <div class="flex items-center gap-2.5">
                        <div class="text-white rounded-full flex items-center justify-center font-bold text-sm bg-drive-primary flex-shrink-0"
                            style="width: 36px; height: 36px;">
                            <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="text-xs font-semibold text-drive-text-main mb-0.5 truncate"><?php echo htmlspecialchars($_SESSION['user_name']); ?></h6>
                            <span class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-medium tracking-wide uppercase rounded-full bg-emerald-100 text-emerald-800">
                                <?php echo htmlspecialchars($_SESSION['active_role']); ?>
                            </span>
                        </div>
                    </div>
                </li>

                <!-- Profile Link (if landowner) -->
                <?php if ($role === 'landowner'): ?>
                <li>
                    <a class="dropdown-item text-xs py-2 px-3 rounded-xl flex items-center gap-2 text-drive-text-sub hover:bg-drive-surface-hover"
                        href="<?php echo $bp; ?>landowner/profile.php">
                        <i class="bi bi-person text-base leading-none"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                <?php endif; ?>

                <!-- Switch Account Header -->
                <li>
                    <h6 class="dropdown-header text-[11px] font-semibold text-drive-text-muted px-3 pt-2 pb-1 uppercase tracking-wider">
                        Switch Account
                    </h6>
                </li>

                <!-- Admin Option -->
                <li>
                    <a class="dropdown-item text-xs py-2 px-3 rounded-xl flex items-center justify-between <?php echo $_SESSION['active_role'] == 'admin' ? 'active bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover'; ?>"
                        href="?switch_role=admin">
                        <span class="flex items-center gap-2">
                            <i class="bi bi-shield-lock text-sm"></i>
                            <span>Administrator</span>
                        </span>
                        <?php if ($_SESSION['active_role'] == 'admin'): ?>
                            <i class="bi bi-check2 text-drive-primary font-bold"></i>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- Landowner Option -->
                <li>
                    <a class="dropdown-item text-xs py-2 px-3 rounded-xl flex items-center justify-between <?php echo $_SESSION['active_role'] == 'landowner' ? 'active bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover'; ?>"
                        href="?switch_role=landowner">
                        <span class="flex items-center gap-2">
                            <i class="bi bi-house text-sm"></i>
                            <span>Landowner</span>
                        </span>
                        <?php if ($_SESSION['active_role'] == 'landowner'): ?>
                            <i class="bi bi-check2 text-drive-primary font-bold"></i>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- Gardener Option -->
                <li>
                    <a class="dropdown-item text-xs py-2 px-3 rounded-xl flex items-center justify-between <?php echo $_SESSION['active_role'] == 'gardener' ? 'active bg-drive-surface-active text-drive-primary-text font-semibold' : 'text-drive-text-sub hover:bg-drive-surface-hover'; ?>"
                        href="?switch_role=gardener">
                        <span class="flex items-center gap-2">
                            <i class="bi bi-flower1 text-sm"></i>
                            <span>Gardener</span>
                        </span>
                        <?php if ($_SESSION['active_role'] == 'gardener'): ?>
                            <i class="bi bi-check2 text-drive-primary font-bold"></i>
                        <?php endif; ?>
                    </a>
                </li>

                <li>
                    <hr class="dropdown-divider border-drive-border my-1.5">
                </li>

                <!-- Sign Out -->
                <li>
                    <a class="dropdown-item text-danger text-xs py-2 px-3 rounded-xl flex items-center gap-2 hover:bg-red-50"
                        href="<?php echo isset($base_path) ? $base_path : ''; ?>index.php">
                        <i class="bi bi-box-arrow-right text-sm"></i>
                        <span>Sign Out</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>
<div class="flex flex-1 overflow-hidden h-[calc(100vh-3.25rem)]">