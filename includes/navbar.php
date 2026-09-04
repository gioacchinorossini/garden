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
<!-- Top Navigation Bar -->
<header class="h-16 flex items-center justify-between px-6 bg-drive-canvas z-10">
    <!-- Left: Branding -->
    <a href="<?php echo $bp; ?>index.php" class="flex items-center gap-2 text-decoration-none" style="min-width: 240px;">
        <img src="<?php echo $bp; ?>logo.jpeg" alt="IdleLand Logo" class="h-9 w-9 object-contain rounded-full">
        <span class="text-xl font-semibold text-drive-text-main font-['Outfit'] tracking-tight">
            <span class="text-drive-primary">Idle</span>Land
        </span>
    </a>

    <!-- Center: Google Drive Search Bar -->
    <div class="flex-1 max-w-[720px] mx-4 hidden md:block">
        <?php
        $search_action = "";
        if ($role === 'gardener') {
            $search_action = $bp . "gardener/search.php";
        } elseif ($role === 'landowner') {
            $search_action = $bp . "landowner/lands.php";
        } else {
            $search_action = $bp . "admin/lands.php";
        }
        ?>
        <form action="<?php echo $search_action; ?>" method="GET" class="m-0">
            <div
                class="relative flex items-center bg-drive-surface-hover focus-within:bg-white focus-within:shadow-md border border-transparent focus-within:border-drive-border rounded-full transition-all duration-200 h-12 px-2">
                <button type="submit"
                    class="p-2 text-drive-text-sub hover:bg-black/5 rounded-full flex items-center justify-center w-10 h-10">
                    <i class="bi bi-search"></i>
                </button>
                <input type="text" name="search" placeholder="Search lands, requests, or crops..."
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                    class="w-full bg-transparent text-drive-text-main placeholder-drive-text-sub focus:outline-none text-sm py-2 px-1"
                    autocomplete="off" />
                <button type="button"
                    class="p-2 text-drive-text-sub hover:bg-black/5 rounded-full flex items-center justify-center w-10 h-10"
                    data-bs-toggle="tooltip" data-bs-placement="bottom" title="Filters"
                    onclick="window.location.href='<?php echo $bp; ?>gardener/search.php'">
                    <i class="bi bi-sliders"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Right: Account Details & Role Switcher -->
    <div class="flex items-center gap-3">
        <!-- Quick Role Switcher for Demo -->
        <div class="dropdown">
            <button
                class="btn btn-sm border border-drive-border text-drive-text-sub hover:bg-drive-surface-hover rounded-full dropdown-toggle flex items-center gap-2 px-3 py-1.5 bg-white text-xs font-semibold"
                type="button" id="roleSwitcher" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-badge text-drive-primary"></i>
                <span>Role: <strong class="text-uppercase"><?php echo $_SESSION['active_role']; ?></strong></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end  border border-drive-border rounded-2xl p-2"
                aria-labelledby="roleSwitcher">
                <li>
                    <h6 class="dropdown-header text-xs text-drive-text-muted px-3 py-2">Select Role (Frontend Mock)</h6>
                </li>
                <li><a class="dropdown-item text-sm py-2 px-3 rounded-xl <?php echo $_SESSION['active_role'] == 'admin' ? 'active bg-drive-surface-active text-drive-primary-text font-semibold' : ''; ?>"
                        href="?switch_role=admin"><i class="bi bi-shield-lock me-2"></i>Administrator</a></li>
                <li><a class="dropdown-item text-sm py-2 px-3 rounded-xl <?php echo $_SESSION['active_role'] == 'landowner' ? 'active bg-drive-surface-active text-drive-primary-text font-semibold' : ''; ?>"
                        href="?switch_role=landowner"><i class="bi bi-house me-2"></i>Landowner</a></li>
                <li><a class="dropdown-item text-sm py-2 px-3 rounded-xl <?php echo $_SESSION['active_role'] == 'gardener' ? 'active bg-drive-surface-active text-drive-primary-text font-semibold' : ''; ?>"
                        href="?switch_role=gardener"><i class="bi bi-flower1 me-2"></i>Gardener</a></li>
                <li>
                    <hr class="dropdown-divider border-drive-border my-1">
                </li>
                <li><a class="dropdown-item text-danger text-sm py-2 px-3 rounded-xl"
                        href="<?php echo isset($base_path) ? $base_path : ''; ?>index.php"><i
                            class="bi bi-box-arrow-right me-2"></i>Sign Out</a></li>
            </ul>
        </div>

        <!-- User Profile Avatar -->
        <div class="flex items-center gap-2">
            <?php 
            $profile_url = "#";
            if ($role === 'landowner') {
                $profile_url = $bp . "landowner/profile.php";
            }
            ?>
            <a href="<?php echo $profile_url; ?>" class="text-white text-decoration-none rounded-full flex items-center justify-center font-bold text-sm bg-drive-primary hover:opacity-90 transition-opacity"
                style="width: 38px; height: 38px;" title="View Profile">
                <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
            </a>
        </div>
    </div>
</header>
<div class="flex flex-1 overflow-hidden pb-4 pr-4 h-[calc(100vh-4rem)]">