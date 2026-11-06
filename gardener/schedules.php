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

require_once __DIR__ . '/../includes/db.php';

// Fetch assigned plots for convenient dropdown selection
$gardener_plots = [];
if (isset($pdo)) {
    try {
        $pStmt = $pdo->prepare("
            SELECT p.id, p.plot_number, l.title as land_title 
            FROM plots p 
            JOIN lands l ON p.land_id = l.id 
            WHERE p.gardener_id = ? OR p.farmer_name = ?
            ORDER BY l.title ASC, p.plot_number ASC
        ");
        $pStmt->execute([$_SESSION['user_id'] ?? 0, $_SESSION['user_name'] ?? '']);
        $gardener_plots = $pStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (\Exception $e) {
        $gardener_plots = [];
    }
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

/* Floating Plus Button (Add Task FAB) */
.floating-task-fab {
    position: fixed;
    bottom: 2rem;
    right: 2.25rem;
    width: 54px;
    height: 54px;
    background: #7FA668 !important;
    color: #FFFDF9 !important;
    border: 2px solid #523E31 !important;
    border-radius: 50% !important;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 0 #523E31, 0 8px 24px rgba(82, 62, 49, 0.28);
    text-decoration: none !important;
    z-index: 1040;
    cursor: pointer;
    outline: none;
    transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s ease, background-color 0.15s ease;
}

.floating-task-fab i {
    font-size: 1.5rem;
    line-height: 1;
    color: #FFFDF9;
    transition: transform 0.25s ease;
}

.floating-task-fab:hover {
    background: #6E9557 !important;
    transform: translateY(-3px) scale(1.06);
    box-shadow: 0 7px 0 #523E31, 0 14px 28px rgba(82, 62, 49, 0.35);
    color: #FFFDF9 !important;
}

.floating-task-fab:hover i {
    transform: rotate(90deg);
}

.floating-task-fab:active {
    transform: translateY(2px) scale(0.96);
    box-shadow: 0 1px 0 #523E31, 0 4px 10px rgba(82, 62, 49, 0.2);
}

/* Tooltip label appearing on hover */
.floating-task-fab .fab-tooltip {
    position: absolute;
    right: calc(100% + 12px);
    top: 50%;
    transform: translateY(-50%) scale(0.9);
    background: #523E31;
    color: #FFFDF9;
    padding: 6px 14px;
    border-radius: 99px;
    font-size: 0.8rem;
    font-weight: 700;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease, transform 0.2s ease;
    box-shadow: 0 4px 12px rgba(82, 62, 49, 0.2);
}

.floating-task-fab .fab-tooltip::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 100%;
    margin-top: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: transparent transparent transparent #523E31;
}

.floating-task-fab:hover .fab-tooltip {
    opacity: 1;
    transform: translateY(-50%) scale(1);
}

@media (max-width: 767.98px) {
    .floating-task-fab {
        bottom: 5.5rem; /* Float above mobile bottom navigation dock */
        right: 1.25rem;
        width: 50px;
        height: 50px;
    }
    .floating-task-fab i {
        font-size: 1.35rem;
    }
    .floating-task-fab .fab-tooltip {
        display: none;
    }
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

            <!-- Add Task Button -->
            <button type="button" class="btn btn-sm btn-drive-primary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                <i class="bi bi-plus-lg"></i>
                <span>Add Task</span>
            </button>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll p-3 px-md-4 pt-md-2 pb-5 pb-md-5">
        <!-- Mobile Header Controls (Visible only on mobile < md) -->
        <div class="d-block d-md-none mb-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div>
                    <h1 class="fs-6 fw-bold m-0 text-dark font-['Outfit']">My Schedule &amp; Tasks</h1>
                    <p class="text-secondary mb-0" style="font-size: 0.72rem;">Track daily garden chores &amp; care for plots</p>
                </div>
                <button type="button" class="btn btn-sm btn-drive-primary rounded-pill px-2.5 py-1 fw-bold text-xs d-inline-flex align-items-center gap-1 shadow-xs" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                    <i class="bi bi-plus-lg"></i>
                    <span>Task</span>
                </button>
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

    <!-- Floating Action Button: Add Task FAB -->
    <button type="button" class="floating-task-fab" id="addTaskFab" data-bs-toggle="modal" data-bs-target="#addTaskModal" title="Schedule Task" aria-label="Schedule Task">
        <i class="bi bi-plus-lg"></i>
        <span class="fab-tooltip">Add Task</span>
    </button>
</main>

<!-- Schedule Garden Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true" style="z-index: 10050;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content drive-modal-content border-0 shadow-lg" style="border-radius: 24px; background: #FFFDF9; border: 2px solid #523E31 !important;">
            <div class="modal-header border-0 pb-1 pt-3 px-4">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: #EBF3E8; color: #2D5A27; border: 1.5px solid #BDDAB8;">
                        <i class="bi bi-calendar-plus fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark m-0 font-['Outfit']" id="addTaskModalLabel" style="font-size: 1.1rem; color: #3E2B1E;">Schedule Garden Task</h5>
                        <p class="text-secondary mb-0" style="font-size: 0.74rem;">Add a new chore, maintenance work, or daily garden duty</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="addTaskForm">
                <div class="modal-body pt-3 pb-3 px-4">
                    <!-- Task Title -->
                    <div class="mb-3">
                        <label for="title" class="form-label text-secondary fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 0.3px;">TASK TITLE <span class="text-danger">*</span></label>
                        <input type="text" class="form-control drive-form-control rounded-3" id="title" name="title" required placeholder="e.g., Morning Tomato Watering" style="border: 1.5px solid #DFCFC2; background: #FFFDF9;">
                    </div>

                    <!-- Task Category / Type -->
                    <div class="mb-3">
                        <label for="task_type" class="form-label text-secondary fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 0.3px;">TASK CATEGORY <span class="text-danger">*</span></label>
                        <select class="form-select drive-form-control rounded-3" id="task_type" name="task_type" required style="border: 1.5px solid #DFCFC2; background: #FFFDF9;">
                            <option value="watering">💧 Watering</option>
                            <option value="planting">🌱 Planting</option>
                            <option value="weeding">🌿 Weeding</option>
                            <option value="pruning">✂️ Pruning</option>
                            <option value="mulching">🍂 Mulching</option>
                            <option value="fertilizing">🧪 Fertilizing</option>
                            <option value="trellising">🎋 Trellising</option>
                            <option value="harvesting">🧺 Harvesting</option>
                            <option value="pests">🐛 Checking for Pests</option>
                            <option value="cleaning">🧹 Cleaning Tools</option>
                        </select>
                    </div>

                    <!-- Target Land / Plot -->
                    <div class="mb-3">
                        <label for="land_title" class="form-label text-secondary fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 0.3px;">TARGET LAND / PLOT</label>
                        <?php if (!empty($gardener_plots)): ?>
                            <select class="form-select drive-form-control rounded-3" id="land_title" name="land_title" style="border: 1.5px solid #DFCFC2; background: #FFFDF9;">
                                <?php foreach ($gardener_plots as $gp): ?>
                                    <option value="<?php echo htmlspecialchars($gp['land_title'] . ' - Plot ' . $gp['plot_number']); ?>">
                                        <?php echo htmlspecialchars($gp['land_title'] . ' — Plot ' . $gp['plot_number']); ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="General Garden Plot">Other / General Garden Plot</option>
                            </select>
                        <?php else: ?>
                            <input type="text" class="form-control drive-form-control rounded-3" id="land_title" name="land_title" placeholder="e.g., Downtown Rooftop Garden - Plot A-1" value="Plot A-1" style="border: 1.5px solid #DFCFC2; background: #FFFDF9;">
                        <?php endif; ?>
                    </div>

                    <!-- Date & Time Range -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="start_time" class="form-label text-secondary fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 0.3px;">START TIME <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control drive-form-control rounded-3" id="start_time" name="start_time" required style="border: 1.5px solid #DFCFC2; font-size: 0.8rem; background: #FFFDF9;">
                        </div>
                        <div class="col-6">
                            <label for="end_time" class="form-label text-secondary fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 0.3px;">END TIME <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control drive-form-control rounded-3" id="end_time" name="end_time" required style="border: 1.5px solid #DFCFC2; font-size: 0.8rem; background: #FFFDF9;">
                        </div>
                    </div>

                    <!-- Instructions / Description -->
                    <div class="mb-2">
                        <label for="description" class="form-label text-secondary fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 0.3px;">INSTRUCTIONS / DESCRIPTION</label>
                        <textarea class="form-control drive-form-control rounded-3" id="description" name="description" rows="2" placeholder="e.g., Check soil moisture before watering. Use drip hose for rows 1 to 4." style="border: 1.5px solid #DFCFC2; background: #FFFDF9;"></textarea>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 pb-3 px-4 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-3.5" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-xs" style="background: #7FA668; border-color: #523E31; color: #FFFDF9;">
                        <i class="bi bi-calendar-plus me-1"></i> Schedule Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>window.CURRENT_USER_ROLE = 'gardener';</script>
<script src="<?php echo $base_path; ?>assets/vendor/lucide/lucide.min.js"></script>
<script src="<?php echo $base_path; ?>assets/js/schedules.js?v=<?php echo time(); ?>"></script>

<?php include '../includes/footer.php'; ?>