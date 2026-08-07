<?php
$base_path = '../';
$page_title = "My Schedule";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Make sure session schedules exist
if (!isset($_SESSION['mock_schedules'])) {
    $_SESSION['mock_schedules'] = [
        [
            'id' => 1,
            'land_title' => 'Downtown Rooftop Garden',
            'gardener' => 'Mary Gardener',
            'title' => 'Water the tomato plant',
            'description' => 'Review the pressure valve on the rooftop irrigation box and clear blockages in B-1 and B-2 drip lines.',
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

<main class="workspace-surface">
    <!-- Toolbar/Title Bar -->
    <div class="toolbar border-bottom">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">Schedules</h1>

        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll">
        <div class="row g-4">
            <!-- Left: Timeline list -->
            <div class="col-md-8">
                <h2 class="fs-6 fw-semibold text-secondary mb-3"
                    style="letter-spacing: 0.5px; text-transform: uppercase;">Tasks</h2>

                <div class="d-grid gap-3">
                    <?php
                    $my_schedules = array_filter($_SESSION['mock_schedules'], function ($s) {
                        return $s['gardener'] === 'Mary Gardener';
                    });
                    ?>

                    <?php if (empty($my_schedules)): ?>
                        <div class="text-center py-5 text-muted border rounded-4 bg-white">
                            <i class="bi bi-calendar-check fs-1 d-block mb-2"></i>
                            <span>You have no upcoming tasks scheduled at this time.</span>
                        </div>
                    <?php else: ?>
                        <?php foreach ($my_schedules as $sched): ?>
                            <div class="card border rounded-4 " style="border-color: var(--drive-border) !important;">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <!-- Icons -->
                                            <?php if ($sched['task_type'] === 'planting'): ?>
                                                <i class="bi bi-seedling text-success fs-4"></i>
                                            <?php elseif ($sched['task_type'] === 'watering'): ?>
                                                <i class="bi bi-droplet-fill text-primary fs-4"></i>
                                            <?php elseif ($sched['task_type'] === 'weeding'): ?>
                                                <i class="bi bi-scissors text-warning fs-4"></i>
                                            <?php else: ?>
                                                <i class="bi bi-calendar-check-fill text-secondary fs-4"></i>
                                            <?php endif; ?>

                                            <h3
                                                class="fs-6 fw-bold m-0 <?php echo $sched['status'] == 'completed' ? 'text-decoration-line-through text-muted' : 'text-dark'; ?>">
                                                <?php echo htmlspecialchars($sched['title']); ?>
                                            </h3>
                                        </div>
                                        <span
                                            class="badge <?php echo $sched['status'] == 'completed' ? 'bg-success' : 'bg-success-subtle text-success'; ?> rounded-pill px-3 py-1"
                                            style="font-size: 10px;">
                                            <?php echo htmlspecialchars(ucfirst($sched['status'])); ?>
                                        </span>
                                    </div>

                                    <p class="text-secondary mb-3" style="font-size: 0.825rem;">
                                        <?php echo htmlspecialchars($sched['description']); ?>
                                    </p>

                                    <div class="row g-2 p-3 bg-light rounded-3 text-secondary mb-3" style="font-size: 0.75rem;">
                                        <div class="col-sm-6"><i class="bi bi-geo-alt-fill me-1"></i> <strong>Location:</strong>
                                            <?php echo htmlspecialchars($sched['land_title']); ?></div>
                                        <div class="col-sm-6"><i class="bi bi-clock-fill me-1"></i> <strong>Scheduled
                                                Time:</strong>
                                            <?php echo date('M d, Y h:i A', strtotime($sched['start_time'])); ?></div>
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <a href="?complete_id=<?php echo $sched['id']; ?>"
                                            class="btn btn-sm <?php echo $sched['status'] == 'completed' ? 'btn-outline-secondary' : 'btn-drive-primary'; ?> rounded-pill">
                                            <i class="bi bi-check2-circle me-1"></i>
                                            <?php echo $sched['status'] == 'completed' ? 'Mark Incomplete' : 'Mark Completed'; ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>