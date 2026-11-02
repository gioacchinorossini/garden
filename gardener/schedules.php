<?php
$base_path = '../';
$page_title = "My Schedule";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['active_role'] = 'gardener';
if (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = 'Gardener';
}

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

// Initialize schedules session array if not already set in session
if (!isset($_SESSION['mock_schedules'])) {
    $_SESSION['mock_schedules'] = [];
}

// Toggle schedule completion
if (isset($_GET['complete_id'])) {
    $c_id = intval($_GET['complete_id']);
    foreach ($_SESSION['mock_schedules'] as &$sched) {
        if ($sched['id'] === $c_id) {
            $sched['status'] = ($sched['status'] == 'completed') ? 'scheduled' : 'completed';
            break;
        }
    }
}
?>

<style>
/* Search Input Fix: Ensure placeholder never overlaps search icon */
#taskSearchInput,
#mobileTaskSearchInput {
    padding-left: 2.4rem !important;
}
</style>

<main class="workspace-surface">
    <!-- Desktop Toolbar (Title and actions, borderless matching requests.php & lands.php) -->
    <div class="toolbar d-none d-md-flex justify-content-between align-items-center">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">My Schedule &amp; Tasks</h1>
            <p class="text-muted mb-0" style="font-size: 0.78rem;">Track daily garden chores, earn points, and care for assigned plots</p>
        </div>

        <div class="d-flex align-items-center gap-3">
            <!-- Search bar -->
            <div class="position-relative" style="width: 240px;">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 0.8rem; pointer-events: none;"></i>
                <input type="text" id="taskSearchInput" class="form-control form-control-sm ps-5 rounded-pill border-light-subtle bg-light"
                    placeholder="Search tasks, chores...">
            </div>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll p-3 px-md-4 pt-md-2 pb-5 pb-md-5">
        <!-- Mobile Header Controls (Visible only on mobile < md) -->
        <div class="d-block d-md-none mb-3">
            <div class="mb-2.5">
                <h1 class="fs-6 fw-bold m-0 text-dark font-['Outfit']">My Schedule &amp; Tasks</h1>
                <p class="text-secondary mb-0" style="font-size: 0.72rem;">Track daily garden chores &amp; care for plots</p>
            </div>

            <!-- Mobile Search -->
            <div class="position-relative mb-2.5">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 0.85rem; pointer-events: none;"></i>
                <input type="text" id="mobileTaskSearchInput" class="form-control form-control-sm ps-5 py-2 rounded-pill border-light-subtle bg-white shadow-xs"
                    placeholder="Search tasks...">
            </div>
        </div>

        <!-- Schedules Dynamic Container -->
        <div id="schedulesContainer"></div>
    </div>
</main>

<script>window.CURRENT_USER_ROLE = 'gardener';</script>
<script src="<?php echo $base_path; ?>assets/vendor/lucide/lucide.min.js"></script>
<script src="<?php echo $base_path; ?>assets/js/schedules.js?v=<?php echo time(); ?>"></script>

<?php include '../includes/footer.php'; ?>