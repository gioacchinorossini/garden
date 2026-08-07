<?php
$base_path = '../';
$page_title = "My Plot Requests";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="workspace-surface">
    <!-- Toolbar/Title Bar -->
    <div class="toolbar border-bottom">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">Request Log</h1>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll">
        <!-- Requests List -->
        <div class="border rounded-4 bg-white overflow-hidden  border-light-subtle">
            <!-- Header Row -->
            <div class="d-flex align-items-center justify-content-between px-4 py-2 bg-light border-bottom text-secondary"
                style="font-size: 0.75rem; font-weight: 600;">
                <div class="w-50">PROPERTY / PLOT</div>
                <div class="w-20">LEASE TERM</div>
                <div class="w-15">STATUS</div>
                <div class="w-15 text-end">DETAILS</div>
            </div>

            <!-- Request Rows Placeholder -->
            <div id="requestsTableBody"></div>
        </div>
    </div>
</main>

<!-- Details/Response Modal (Single dynamic modal instance) -->
<div class="modal fade" id="viewResponseModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content drive-modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold text-dark">Application Status Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start">
                <div class="mb-3">
                    <label class="text-secondary fw-semibold d-block mb-1" style="font-size: 11px;">REQUESTED
                        PLOT</label>
                    <strong id="modal_land_title"></strong>
                </div>
                <div class="mb-3">
                    <label class="text-secondary fw-semibold d-block mb-1" style="font-size: 11px;">SUBMITTED
                        PURPOSE</label>
                    <div class="p-3 bg-light rounded-3 text-secondary" style="font-size:0.85rem;" id="modal_purpose">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-secondary fw-semibold d-block mb-1" style="font-size: 11px;">TERM</label>
                    <div id="modal_duration"></div>
                </div>
                <div class="mb-3 hidden" id="modal_feedback_section">
                    <label class="text-secondary fw-semibold d-block mb-1" style="font-size: 11px;">LANDOWNER
                        FEEDBACK</label>
                    <div class="alert alert-info py-2" style="font-size: 0.85rem; border-radius: 8px;"
                        id="modal_feedback"></div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-drive-primary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo $base_path; ?>assets/js/gardener_requests.js"></script>

<?php include '../includes/footer.php'; ?>