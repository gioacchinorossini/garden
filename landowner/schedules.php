<?php
$base_path = '../';
$page_title = "Landowner Schedules";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="workspace-surface">
    <!-- Desktop Toolbar (Title and actions, borderless) -->
    <div class="toolbar d-none d-md-flex justify-content-between align-items-center">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">Schedules</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-drive-primary btn-sm px-3 d-flex align-items-center gap-2 rounded-pill"
                    data-bs-toggle="modal" data-bs-target="#addTaskModal">
                <i class="bi bi-plus-lg"></i>
                <span>Add Task</span>
            </button>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll">
        <!-- Mobile-only header row -->
        <div class="d-flex d-md-none align-items-center justify-content-between mb-3">
            <h2 class="fs-6 fw-semibold m-0" style="letter-spacing:0.5px;text-transform:uppercase;">Upcoming Tasks</h2>
            <button class="btn btn-drive-primary btn-sm px-3 d-flex align-items-center gap-2 rounded-pill"
                    data-bs-toggle="modal" data-bs-target="#addTaskModal">
                <i class="bi bi-plus-lg"></i> Add Task
            </button>
        </div>

        <!-- Schedules Dynamic Container -->
        <div id="schedulesContainer"></div>
    </div>
</main>

<!-- Add Task Modal (Single instance) -->
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content drive-modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold text-dark">Add Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addTaskForm">
                    <div class="mb-3">
                        <label for="title" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">TASK NAME</label>
                        <input type="text" class="form-control drive-form-control w-100" id="title" name="title"
                            required placeholder="Irrigation Check">
                    </div>

                    <div class="mb-3">
                        <label for="task_type" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">TYPE</label>
                        <select class="form-select drive-form-control w-100" id="task_type" name="task_type" required>
                            <option value="watering">Watering</option>
                            <option value="planting">Planting</option>
                            <option value="weeding">Weeding</option>
                            <option value="pruning">Pruning</option>
                            <option value="mulching">Mulching</option>
                            <option value="fertilizing">Fertilizing</option>
                            <option value="trellising">Trellising</option>
                            <option value="harvesting">Harvesting</option>
                            <option value="pests">Checking for Pests</option>
                            <option value="cleaning">Cleaning Tools</option>
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="start_time" class="form-label text-secondary"
                                style="font-size: 0.75rem; font-weight:600;">START TIME</label>
                            <input type="datetime-local" class="form-control drive-form-control w-100" id="start_time"
                                name="start_time" required>
                        </div>
                        <div class="col-md-6">
                            <label for="end_time" class="form-label text-secondary"
                                style="font-size: 0.75rem; font-weight:600;">END TIME</label>
                            <input type="datetime-local" class="form-control drive-form-control w-100" id="end_time"
                                name="end_time" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">INSTRUCTIONS</label>
                        <textarea class="form-control drive-form-control w-100" id="description" name="description"
                            rows="3" placeholder="Provide instructions..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-drive-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-drive-primary">Create Task</button>
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