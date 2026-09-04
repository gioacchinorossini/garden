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
                                        <div class="d-flex align-items-center gap-3">
                                            <!-- Task type icon in colored square -->
                                            <?php
                                            $type = $sched['task_type'];
                                            $typeMap = [
                                                'watering' => ['icon' => 'bi-droplet-fill',  'bg' => 'bg-primary-subtle',  'color' => 'text-primary',        'label' => 'Watering'],
                                                'planting' => ['icon' => 'bi-seedling',        'bg' => 'bg-success-subtle',  'color' => 'text-success',        'label' => 'Planting'],
                                                'weeding'  => ['icon' => 'bi-scissors',        'bg' => 'bg-warning-subtle',  'color' => 'text-warning-emphasis','label' => 'Weeding'],
                                                'harvesting'=> ['icon'=> 'bi-basket3-fill',   'bg' => 'bg-danger-subtle',   'color' => 'text-danger',         'label' => 'Harvesting'],
                                                'fertilizing'=> ['icon'=>'bi-droplet-half',   'bg' => 'bg-info-subtle',     'color' => 'text-info-emphasis',  'label' => 'Fertilizing'],
                                            ];
                                            $t = $typeMap[$type] ?? ['icon' => 'bi-calendar-check-fill', 'bg' => 'bg-secondary-subtle', 'color' => 'text-secondary', 'label' => ucfirst($type ?: 'Task')];
                                            ?>
                                            <div class="rounded-3 d-flex align-items-center justify-content-center <?php echo $t['bg']; ?> <?php echo $t['color']; ?>"
                                                 style="width:44px; height:44px; min-width:44px; font-size:1.3rem;">
                                                <i class="bi <?php echo $t['icon']; ?>"></i>
                                            </div>
                                            <div>
                                                <span class="d-block text-muted" style="font-size:0.68rem; font-weight:600; letter-spacing:0.4px; text-transform:uppercase;"><?php echo $t['label']; ?></span>
                                                <h3 class="fs-6 fw-bold m-0 <?php echo $sched['status'] == 'completed' ? 'text-decoration-line-through text-muted' : 'text-dark'; ?>">
                                                    <?php echo htmlspecialchars($sched['title']); ?>
                                                </h3>
                                            </div>
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