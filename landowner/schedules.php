<?php
$base_path = '../';
$page_title = "Property Schedules";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['active_role'] = 'property_owner';
if (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = 'Juan Property Owner';
}

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="workspace-surface d-flex flex-column h-100 overflow-hidden">
    <!-- Desktop & Mobile Toolbar -->
    <div class="toolbar border-bottom d-flex align-items-center justify-content-between px-3 px-md-4 py-2.5 bg-white flex-shrink-0">
        <div class="d-flex align-items-center gap-2 min-w-0">
            <div class="d-inline-flex align-items-center justify-content-center rounded-3 bg-orange-100 text-orange-600 p-2 flex-shrink-0" style="width:36px;height:36px;">
                <i data-lucide="calendar" style="width:20px;height:20px;"></i>
            </div>
            <div class="min-w-0">
                <h1 class="fs-6 fw-bold m-0 text-dark font-['Outfit'] text-truncate" style="line-height:1.2;">
                    Property Schedules & Events
                </h1>
                <p class="text-secondary mb-0 small text-truncate" style="font-size:0.75rem;">
                    Coordinate night market days, maintenance, and stall operating hours
                </p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <button class="btn btn-drive-primary btn-sm rounded-pill px-3.5 d-inline-flex align-items-center gap-1.5"
                    style="font-size:0.76rem;" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                <i data-lucide="plus" style="width:15px;height:15px;"></i>
                <span>Add Task</span>
            </button>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll flex-grow-1 p-3 p-md-4">
        <!-- Schedules Dynamic Container -->
        <div id="schedulesContainer"></div>
    </div>
</main>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-hidden="true" style="z-index: 10050;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content drive-modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 pb-1">
                <div>
                    <span class="badge rounded-pill px-2.5 py-1 mb-1 fw-bold text-xs" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;">PROPERTY OWNER ACTION</span>
                    <h5 class="modal-title fw-bold text-dark m-0">Schedule Property Event / Shift</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2 pb-3">
                <form id="addTaskForm">
                    <div class="mb-3">
                        <label for="title" class="form-label text-secondary mb-1"
                            style="font-size: 0.72rem; font-weight:700; letter-spacing:0.5px;">EVENT / SHIFT NAME <span class="text-danger">*</span></label>
                        <input type="text" class="form-control drive-form-control w-100" id="title" name="title"
                            required placeholder="e.g. Weekend Street Food Bazaar Rush">
                    </div>

                    <div class="mb-3">
                        <label for="task_type" class="form-label text-secondary mb-1"
                            style="font-size: 0.72rem; font-weight:700; letter-spacing:0.5px;">CATEGORY <span class="text-danger">*</span></label>
                        <select class="form-select drive-form-control w-100" id="task_type" name="task_type" required>
                            <option value="night_market">🔥 Evening Street Food Shift</option>
                            <option value="prep">🔪 Food Prep & Meat Marination</option>
                            <option value="event">🎪 Weekend Food Bazaar Event</option>
                            <option value="cleaning">🧹 Facility & Grease Trap Cleaning</option>
                            <option value="maintenance">⚡ Power, Gas & Water Maintenance</option>
                            <option value="pests">🛡️ Sanitation & Pest Inspection</option>
                            <option value="restock">📦 Beverage & Ice Restock</option>
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