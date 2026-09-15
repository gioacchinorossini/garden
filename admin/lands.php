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
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content drive-modal-content">
            <div class="modal-header border-bottom py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-geo-alt-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-semibold text-dark m-0" id="modal_title"></h5>
                        <span class="text-secondary text-xs" id="modal_address_sub"></span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-secondary text-sm mb-3" id="modal_description"></p>

                <!-- Basic Property Information -->
                <div class="p-3 bg-light rounded-3 mb-3" style="font-size: 0.8rem; border: 1px solid var(--drive-border);">
                    <div class="row g-2">
                        <div class="col-6"><strong>Owner:</strong> <span id="modal_owner"></span></div>
                        <div class="col-6"><strong>Total Area:</strong> <span id="modal_area"></span></div>
                        <div class="col-12"><strong>Address:</strong> <span id="modal_address"></span></div>
                        <div class="col-12"><strong>GPS Coordinates:</strong> <span id="modal_coords"></span></div>
                    </div>
                </div>

                <!-- LRA Authenticity & LOTS Due Diligence Section -->
                <div class="border rounded-3 p-3.5 mb-3 bg-white" style="border-color: rgba(37, 99, 235, 0.3) !important;">
                    <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary-subtle text-primary p-1.5 rounded-circle">
                                <i class="bi bi-shield-check fs-6"></i>
                            </span>
                            <div>
                                <h6 class="fw-bold text-dark m-0" style="font-size: 0.88rem;">Land Title & LRA LOTS Verification</h6>
                                <small class="text-muted" style="font-size: 0.72rem;">Land Registration Authority Transaction Due Diligence</small>
                            </div>
                        </div>
                        <div id="modal_lra_badge_container"></div>
                    </div>

                    <!-- LRA Data Grid -->
                    <div class="row g-2.5 mb-3" style="font-size: 0.8rem;">
                        <div class="col-md-6">
                            <div class="p-2.5 rounded-2 bg-light border">
                                <span class="text-muted text-xs d-block">Title Type & Number:</span>
                                <div class="fw-bold text-dark mt-0.5" id="modal_title_full">None declared</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-2.5 rounded-2 bg-light border">
                                <span class="text-muted text-xs d-block">Registry of Deeds (RD):</span>
                                <div class="fw-bold text-dark mt-0.5" id="modal_rod_name">Not specified</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-2.5 rounded-2 bg-light border">
                                <span class="text-muted text-xs d-block">EPEB Transaction Type:</span>
                                <div class="fw-bold text-dark mt-0.5" id="modal_epeb_type">CCV (Certified True Copy)</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-2.5 rounded-2 bg-light border d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-xs d-block">LRA EPEB Number:</span>
                                    <div class="fw-bold font-monospace text-primary fs-6" id="modal_epeb_no">None</div>
                                </div>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1 text-xs" onclick="copyEpebNumber()" id="copyEpebBtn" title="Copy EPEB number">
                                    <i class="bi bi-clipboard me-1"></i>Copy
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Direct LRA Verification Assistant -->
                    <div class="p-2.5 rounded-3 bg-primary-subtle border border-primary-subtle d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle-fill text-primary"></i>
                            <span style="font-size: 0.76rem;" class="text-primary-emphasis">
                                Cross-check this transaction on the official government portal using the RD and EPEB number above:
                            </span>
                        </div>
                        <a href="https://lots.lra.gov.ph/TransactionStatus/Search.aspx" target="_blank"
                            class="btn btn-sm btn-primary rounded-pill px-3 text-xs fw-semibold d-flex align-items-center gap-1.5 shadow-xs">
                            <i class="bi bi-box-arrow-up-right"></i>
                            <span>Open LRA LOTS Portal</span>
                        </a>
                    </div>

                    <!-- Proof Documents Thumbnails -->
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <div class="border rounded-3 p-2.5 bg-light d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2 overflow-hidden">
                                    <i class="bi bi-file-earmark-pdf-fill text-danger fs-4 flex-shrink-0"></i>
                                    <div class="overflow-hidden">
                                        <div class="fw-semibold text-dark text-xs text-truncate">Certified True Copy (Title)</div>
                                        <small class="text-muted text-xs" id="modal_title_doc_status">Document attached</small>
                                    </div>
                                </div>
                                <a id="modal_title_doc_link" href="#" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 text-xs fw-semibold flex-shrink-0">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="border rounded-3 p-2.5 bg-light d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2 overflow-hidden">
                                    <i class="bi bi-receipt-cutoff text-success fs-4 flex-shrink-0"></i>
                                    <div class="overflow-hidden">
                                        <div class="fw-semibold text-dark text-xs text-truncate">LRA Official Receipt</div>
                                        <small class="text-muted text-xs" id="modal_receipt_doc_status">Receipt attached</small>
                                    </div>
                                </div>
                                <a id="modal_receipt_doc_link" href="#" target="_blank" class="btn btn-xs btn-outline-success rounded-pill px-2.5 py-1 text-xs fw-semibold flex-shrink-0">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rejection Reason (if any) -->
                <div class="alert alert-danger py-2 hidden" style="font-size: 0.8rem; border-radius: 8px;" id="modal_reason_block">
                    <strong>Rejection Reason:</strong> <span id="modal_reason"></span>
                </div>

                <!-- Modal Actions -->
                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <div id="modal_quick_actions" class="d-flex gap-2"></div>
                    <button type="button" class="btn btn-drive-secondary px-4" data-bs-dismiss="modal">Close</button>
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