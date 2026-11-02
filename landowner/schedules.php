<?php
$base_path = '../';
$page_title = "Landowner Schedules";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['active_role'] = 'landowner';
if (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = 'Landowner';
}

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
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
            <h1 class="fs-5 fw-semibold m-0 text-dark">Garden Task Schedules</h1>
            <p class="text-muted mb-0" style="font-size: 0.78rem;">Coordinate plot maintenance, irrigation, and tasks across your lands</p>
        </div>

        <div class="d-flex align-items-center gap-3">
            <!-- Search bar -->
            <div class="position-relative" style="width: 240px;">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 0.8rem; pointer-events: none;"></i>
                <input type="text" id="taskSearchInput" class="form-control form-control-sm ps-5 rounded-pill border-light-subtle bg-light"
                    placeholder="Search tasks, types...">
            </div>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll p-3 px-md-4 pt-md-2 pb-5 pb-md-5">
        <!-- Mobile Header Controls (Visible only on mobile < md) -->
        <div class="d-block d-md-none mb-3">
            <div class="mb-2.5">
                <h1 class="fs-6 fw-bold m-0 text-dark font-['Outfit']">Garden Task Schedules</h1>
                <p class="text-secondary mb-0" style="font-size: 0.72rem;">Coordinate maintenance, irrigation &amp; plot tasks</p>
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

    <!-- Floating Plus Button (Add Task FAB) -->
    <button type="button" class="floating-task-fab" id="addTaskFab" data-bs-toggle="modal" data-bs-target="#addTaskModal" title="Schedule Garden Task" aria-label="Schedule Garden Task">
        <i class="bi bi-plus-lg"></i>
        <span class="fab-tooltip">Add Task</span>
    </button>
</main>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-hidden="true" style="z-index: 10050;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content drive-modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 pb-1">
                <div>
                    <span class="badge rounded-pill px-2.5 py-1 mb-1 fw-bold text-xs" style="background:#EBF3E8;color:#2D5A27;border:1px solid #BDDAB8;">LANDOWNER ACTION</span>
                    <h5 class="modal-title fw-bold text-dark m-0">Schedule Garden Task</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2 pb-3">
                <form id="addTaskForm">
                    <div class="mb-3">
                        <label for="title" class="form-label text-secondary mb-1"
                            style="font-size: 0.72rem; font-weight:700; letter-spacing:0.5px;">TASK NAME <span class="text-danger">*</span></label>
                        <input type="text" class="form-control drive-form-control w-100" id="title" name="title"
                            required placeholder="e.g., Morning Irrigation & Soil Moisture Check">
                    </div>

                    <div class="mb-3">
                        <label for="task_type" class="form-label text-secondary mb-1"
                            style="font-size: 0.72rem; font-weight:700; letter-spacing:0.5px;">TASK CATEGORY <span class="text-danger">*</span></label>
                        <select class="form-select drive-form-control w-100" id="task_type" name="task_type" required>
                            <option value="watering">💧 Watering / Irrigation</option>
                            <option value="planting">🌱 Planting / Seedlings</option>
                            <option value="weeding">🌿 Weeding & Bed Cleanup</option>
                            <option value="pruning">✂️ Pruning & Tree Care</option>
                            <option value="mulching">🪵 Mulching & Moisture Retention</option>
                            <option value="fertilizing">🧪 Organic Compost & Feeding</option>
                            <option value="trellising">🎋 Trellising & Staking</option>
                            <option value="harvesting">🌾 Harvesting Crops</option>
                            <option value="pests">🐛 Pest Scouting & Control</option>
                            <option value="cleaning">🧹 Tool & Facility Sanitizing</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="start_time" class="form-label text-secondary mb-1"
                                style="font-size: 0.72rem; font-weight:700; letter-spacing:0.5px;">START TIME <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control drive-form-control w-100" id="start_time"
                                name="start_time" required>
                        </div>
                        <div class="col-6">
                            <label for="end_time" class="form-label text-secondary mb-1"
                                style="font-size: 0.72rem; font-weight:700; letter-spacing:0.5px;">END TIME <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control drive-form-control w-100" id="end_time"
                                name="end_time" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label text-secondary mb-1"
                            style="font-size: 0.72rem; font-weight:700; letter-spacing:0.5px;">INSTRUCTIONS &amp; GUIDELINES</label>
                        <textarea class="form-control drive-form-control w-100" id="description" name="description"
                            rows="3" placeholder="Provide specific instructions, target plots, equipment needed, or safety notes..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-3.5" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-drive-primary rounded-pill px-4 fw-bold">
                            <i data-lucide="plus" style="width:14px;height:14px;" class="me-1"></i> Create Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>window.CURRENT_USER_ROLE = 'landowner';</script>
<script src="<?php echo $base_path; ?>assets/vendor/lucide/lucide.min.js"></script>
<script src="<?php echo $base_path; ?>assets/js/schedules.js?v=<?php echo time(); ?>"></script>

<?php include '../includes/footer.php'; ?>