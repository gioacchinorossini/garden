<?php
$base_path = '../';
$page_title = "My Schedule";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['active_role'] = 'gardener';
if (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = 'Mary Gardener';
}

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

// Initialize mock schedules if not already set in session
if (!isset($_SESSION['mock_schedules']) || empty($_SESSION['mock_schedules'])) {
    $_SESSION['mock_schedules'] = [
        [
            'id' => 1,
            'land_title' => 'Downtown Rooftop Garden',
            'gardener' => 'Mary Gardener',
            'title' => 'Water the tomato plant',
            'description' => 'Inspect and water all tomato plants in B-1 and B-2. Check soil moisture levels.',
            'start_time' => '2026-08-08 09:00:00',
            'end_time' => '2026-08-08 11:30:00',
            'task_type' => 'watering',
            'status' => 'scheduled'
        ],
        [
            'id' => 2,
            'land_title' => 'Downtown Rooftop Garden',
            'gardener' => 'Mary Gardener',
            'title' => 'Tomato Seedling Planting',
            'description' => 'Transplant tomato seedlings into Plot A-1 and A-2 organic garden compost beds.',
            'start_time' => '2026-08-09 07:00:00',
            'end_time' => '2026-08-09 10:00:00',
            'task_type' => 'planting',
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