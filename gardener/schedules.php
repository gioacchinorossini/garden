<?php
$base_path = '../';
$page_title = "My Schedule";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Always reset so code changes immediately reflect (no stale session data)
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

<main class="workspace-surface">
    <!-- Toolbar/Title Bar -->
    <div class="toolbar border-bottom">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">Schedules</h1>

        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll">
        <!-- Schedules Dynamic Container -->
        <div id="schedulesContainer"></div>
    </div>
</main>

<script>window.CURRENT_USER_ROLE = 'gardener';</script>
<script src="<?php echo $base_path; ?>assets/vendor/lucide/lucide.min.js"></script>
<script src="<?php echo $base_path; ?>assets/js/schedules.js?v=<?php echo time(); ?>"></script>

<?php include '../includes/footer.php'; ?>