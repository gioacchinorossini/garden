<?php
$base_path = '../';
$page_title = "Manage Requests";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="workspace-surface">
    <!-- Toolbar/Title Bar -->
    <div class="toolbar border-bottom">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">Plot Requests</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary rounded-pill btn-sm px-3 active" id="filterAllBtn">All</button>
            <button class="btn btn-outline-secondary rounded-pill btn-sm px-3" id="filterPendingBtn">Pending</button>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll">
        <!-- Requests List -->
        <div class="border rounded-4 bg-white overflow-hidden  border-light-subtle mb-4">
            <!-- Header Row -->
            <div class="d-flex align-items-center justify-content-between px-4 py-2 bg-light border-bottom text-secondary"
                style="font-size: 0.75rem; font-weight: 600;">
                <div class="w-35">GARDENER</div>
                <div class="w-35">LAND</div>
                <div class="w-10">TERM</div>
                <div class="w-10">STATUS</div>
                <div class="w-10 text-end">ACTION</div>
            </div>

            <!-- Request Rows Placeholder -->
            <div id="requestsTableBody"></div>
        </div>
    </div>
</main>

<!-- Details Modal (Single instance) -->
<div class="modal fade" id="viewRequestModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content drive-modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold text-dark">Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="text-secondary fw-semibold d-block mb-1" style="font-size: 11px;">GARDENER</label>
                    <div class="p-3 bg-light rounded-3">
                        <strong id="view_gardener"></strong><br>
                        <small class="text-muted" id="view_contact"></small>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-secondary fw-semibold d-block mb-1" style="font-size: 11px;">LOCATION</label>
                    <div id="view_plot"></div>
                </div>
                <div class="mb-3">
                    <label class="text-secondary fw-semibold d-block mb-1" style="font-size: 11px;">PURPOSE</label>
                    <p class="text-dark bg-light p-3 rounded-3 mb-0" style="font-size: 0.85rem;" id="view_purpose"></p>
                </div>
                <div class="mb-3">
                    <label class="text-secondary fw-semibold d-block mb-1" style="font-size: 11px;">TERM</label>
                    <div id="view_duration"></div>
                </div>
                <div class="mb-3 hidden" id="view_feedback_section">
                    <label class="text-secondary fw-semibold d-block mb-1" style="font-size: 11px;">FEEDBACK</label>
                    <div class="alert alert-info py-2" style="font-size: 0.85rem; border-radius: 8px;"
                        id="view_feedback"></div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-drive-primary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal (Single instance) -->
<div class="modal fade" id="rejectRequestModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content drive-modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold text-dark">Reject Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="rejectRequestForm">
                    <input type="hidden" id="reject_request_id" name="id">
                    <div class="mb-4">
                        <label for="reject_notes" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">REASON</label>
                        <textarea class="form-control drive-form-control w-100" id="reject_notes" name="notes" rows="3"
                            required
                            placeholder="Enter reason for rejection..."></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-drive-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo $base_path; ?>assets/js/landowner_requests.js"></script>

<?php include '../includes/footer.php'; ?>