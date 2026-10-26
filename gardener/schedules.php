<?php
$base_path = '../';
$page_title = "Operating Hours & Schedules";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['active_role'] = 'vendor';
if (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = 'Maria Street Vendor';
}

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

// Initialize operating schedules if not already set in session
if (!isset($_SESSION['mock_schedules']) || empty($_SESSION['mock_schedules'])) {
    $_SESSION['mock_schedules'] = [
        [
            'id' => 1,
            'land_title' => 'Mercato Night Food Park - Stall A-01',
            'gardener' => 'Maria Street Vendor',
            'title' => 'Evening Food Park Operations',
            'description' => 'Charcoal grill station open for dinner rush, skewers, isaw, and cold drinks.',
            'start_time' => date('Y-m-d') . ' 17:00:00',
            'end_time' => date('Y-m-d') . ' 23:30:00',
            'task_type' => 'night_market',
            'status' => 'scheduled'
        ],
        [
            'id' => 2,
            'land_title' => 'Mercato Night Food Park - Stall A-01',
            'gardener' => 'Maria Street Vendor',
            'title' => 'Morning Prep & Stock Replenishment',
            'description' => 'Marinate fresh pork isaw, prep kwek-kwek batter, and restock ice & gulaman syrup.',
            'start_time' => date('Y-m-d') . ' 10:00:00',
            'end_time' => date('Y-m-d') . ' 12:30:00',
            'task_type' => 'prep',
            'status' => 'scheduled'
        ]
    ];
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

<main class="workspace-surface d-flex flex-column h-100 overflow-hidden">

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll flex-grow-1 p-3 p-md-4">
        <!-- Schedules Dynamic Container -->
        <div id="schedulesContainer"></div>
    </div>
</main>

<script>window.CURRENT_USER_ROLE = 'gardener';</script>
<script src="<?php echo $base_path; ?>assets/vendor/lucide/lucide.min.js"></script>
<script src="<?php echo $base_path; ?>assets/js/schedules.js?v=<?php echo time(); ?>"></script>

<?php include '../includes/footer.php'; ?>