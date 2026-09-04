<?php
$base_path = '../';
$page_title = "Manage Lands";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="workspace-surface">
    <!-- Toolbar/Title Bar -->
    <div class="toolbar border-bottom">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">Manage Lands</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary rounded-pill btn-sm px-3" onclick="toggleMapSection()">
                <i class="bi bi-map-fill me-1 text-primary"></i> Map View
            </button>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll">
        <!-- 1. Leaflet Map Section (Toggleable) -->
        <div id="mapWrapper" class="mb-4" style="display: block;">
            <div class="card border rounded-4 overflow-hidden " style="border-color: var(--drive-border) !important;">
                <div class="card-header bg-light d-flex align-items-center justify-content-between py-2 border-bottom">
                    <span class="fw-semibold text-secondary"
                        style="font-size: 0.75rem; letter-spacing:0.5px; text-transform:uppercase;">Interactive Map</span>
                </div>
                <div id="landMap" style="height: 250px; background-color: #e9f2ff;"></div>
            </div>
        </div>

        <!-- 2. Status Filters -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 active filter-btn"
                    data-filter="all">All</button>
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 filter-btn"
                    data-filter="pending">Pending</button>
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 filter-btn"
                    data-filter="approved">Approved</button>
            </div>
            <div class="text-muted" style="font-size: 0.8rem;" id="landsCountText">
                Showing <strong>0</strong> submissions
            </div>
        </div>

        <!-- 3. Lands Table List -->
        <div class="border rounded-4 bg-white overflow-hidden  border-light-subtle mb-4">
            <!-- Header Row -->
            <div class="d-flex align-items-center justify-content-between px-4 py-2 bg-light border-bottom text-secondary"
                style="font-size: 0.75rem; font-weight: 600;">
                <div class="w-25">TITLE</div>
                <div class="w-20">OWNER</div>
                <div class="w-15">AREA</div>
                <div class="w-15">GPS</div>
                <div class="w-10">STATUS</div>
                <div class="w-15 text-end">ACTION</div>
            </div>

            <!-- Lands Row Placeholder -->
            <div id="landsTableBody"></div>
        </div>
    </div>
</main>

<!-- Details Modal (Single instance) -->
<div class="modal fade" id="viewLandModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content drive-modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold text-dark" id="modal_title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-secondary" style="font-size: 0.85rem;" id="modal_description"></p>
                <div class="p-3 bg-light rounded-3 mb-3"
                    style="font-size: 0.8rem; border: 1px solid var(--drive-border);">
                    <div class="row g-2">
                        <div class="col-6"><strong>Owner:</strong> <span id="modal_owner"></span></div>
                        <div class="col-6"><strong>Area:</strong> <span id="modal_area"></span></div>
                        <div class="col-12"><strong>Address:</strong> <span id="modal_address"></span></div>
                        <div class="col-12"><strong>Coordinates:</strong> <span id="modal_coords"></span></div>
                    </div>
                </div>
                <div class="alert alert-danger py-2 hidden" style="font-size: 0.8rem; border-radius: 8px;"
                    id="modal_reason_block">
                    <strong>Rejection Reason:</strong> <span id="modal_reason"></span>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-drive-primary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal (Single instance) -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content drive-modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold text-dark">Reject Land</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="rejectForm">
                    <input type="hidden" id="reject_land_id" name="id">
                    <div class="mb-4">
                        <label for="rejection_reason" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">REASON</label>
                        <textarea class="form-control drive-form-control w-100" id="rejection_reason"
                            name="rejection_reason" rows="3" required
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

<script src="<?php echo $base_path; ?>assets/js/admin_lands.js"></script>

<?php include '../includes/footer.php'; ?>