<?php
$base_path = '../';
$page_title = "Manage Requests";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="workspace-surface">
    <!-- Desktop Toolbar (Title and actions, borderless) -->
    <div class="toolbar d-none d-md-flex justify-content-between align-items-center">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">Plot Requests</h1>
            <p class="text-muted mb-0" style="font-size: 0.78rem;">Review and manage gardener applications for your plots</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <!-- Search bar -->
            <div class="position-relative" style="width: 220px;">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 0.8rem;"></i>
                <input type="text" id="requestsSearchInput" class="form-control form-control-sm ps-5 rounded-pill border-light-subtle bg-light"
                    placeholder="Search gardener, land...">
            </div>

            <!-- Filter tabs -->
            <!-- Filter tabs -->
            <!-- Filter tabs -->
            <div class="filter-pill-track" role="group">
                <button type="button" class="btn btn-sm rounded-pill request-filter-btn active" data-filter="all" id="filterAllBtn">
                    All <span class="badge rounded-pill bg-secondary ms-1" id="countAll">0</span>
                </button>
                <button type="button" class="btn btn-sm rounded-pill request-filter-btn text-secondary" data-filter="pending" id="filterPendingBtn">
                    Pending <span class="badge rounded-pill bg-warning text-dark ms-1" id="countPending">0</span>
                </button>
                <button type="button" class="btn btn-sm rounded-pill request-filter-btn text-secondary" data-filter="offer_sent" id="filterOfferBtn">
                    Offers Sent <span class="badge rounded-pill text-dark ms-1" style="background:#FEE7AA;" id="countOffer">0</span>
                </button>
                <button type="button" class="btn btn-sm rounded-pill request-filter-btn text-secondary" data-filter="counter_offered" id="filterCounterBtn">
                    Counter-Offers <span class="badge rounded-pill text-white ms-1" style="background:#0284C7;" id="countCounter">0</span>
                </button>
                <button type="button" class="btn btn-sm rounded-pill request-filter-btn text-secondary" data-filter="approved" id="filterApprovedBtn">
                    Approved <span class="badge rounded-pill bg-success ms-1" id="countApproved">0</span>
                </button>
                <button type="button" class="btn btn-sm rounded-pill request-filter-btn text-secondary" data-filter="rejected" id="filterRejectedBtn">
                    Rejected <span class="badge rounded-pill bg-danger ms-1" id="countRejected">0</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll p-3 px-md-4 pt-md-2 pb-md-4">
        <!-- Mobile Header Controls (Visible only on mobile < md) -->
        <div class="d-block d-md-none mb-3">
            <!-- Mobile Search -->
            <div class="position-relative mb-2.5">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 0.85rem;"></i>
                <input type="text" id="mobileSearchInput" class="form-control form-control-sm ps-5 py-2 rounded-pill border-light-subtle bg-white shadow-xs"
                    placeholder="Search requests...">
            </div>

            <!-- Mobile Filter Pills (Horizontal Scroll) -->
            <div class="d-flex align-items-center gap-2 overflow-x-auto py-1.5 px-0.5 no-scrollbar">
                <button type="button" class="btn btn-sm rounded-pill request-filter-btn active flex-shrink-0" data-filter="all">
                    All <span class="badge rounded-pill bg-secondary ms-1" id="mCountAll">0</span>
                </button>
                <button type="button" class="btn btn-sm rounded-pill request-filter-btn bg-white border flex-shrink-0 text-secondary" data-filter="pending">
                    Pending <span class="badge rounded-pill bg-warning text-dark ms-1" id="mCountPending">0</span>
                </button>
                <button type="button" class="btn btn-sm rounded-pill request-filter-btn bg-white border flex-shrink-0 text-secondary" data-filter="offer_sent">
                    Offers <span class="badge rounded-pill text-dark ms-1" style="background:#FEE7AA;" id="mCountOffer">0</span>
                </button>
                <button type="button" class="btn btn-sm rounded-pill request-filter-btn bg-white border flex-shrink-0 text-secondary" data-filter="counter_offered">
                    Counter <span class="badge rounded-pill text-white ms-1" style="background:#0284C7;" id="mCountCounter">0</span>
                </button>
                <button type="button" class="btn btn-sm rounded-pill request-filter-btn bg-white border flex-shrink-0 text-secondary" data-filter="approved">
                    Approved <span class="badge rounded-pill bg-success ms-1" id="mCountApproved">0</span>
                </button>
                <button type="button" class="btn btn-sm rounded-pill request-filter-btn bg-white border flex-shrink-0 text-secondary" data-filter="rejected">
                    Rejected <span class="badge rounded-pill bg-danger ms-1" id="mCountRejected">0</span>
                </button>
            </div>
        </div>

        <!-- Desktop Table View (Hidden on mobile) -->
        <div class="d-none d-md-block request-table-container mb-4">
            <!-- Header Row -->
            <div class="d-flex align-items-center justify-content-between px-4 py-3 request-table-header"
                style="font-size: 0.75rem; font-weight: 800; letter-spacing: 0.5px;">
                <div style="width: 28%;">GARDENER</div>
                <div style="width: 28%;">PLOT & PROPERTY</div>
                <div style="width: 16%;">PURPOSE & DURATION</div>
                <div style="width: 14%;">STATUS</div>
                <div style="width: 14%;" class="text-end">ACTION</div>
            </div>

            <!-- Request Rows Placeholder -->
            <div id="requestsTableBody"></div>
        </div>

        <!-- Mobile Card List View (Visible only on mobile < md) -->
        <div class="d-md-none" id="requestsMobileCards">
            <!-- Dynamic cards injected here -->
        </div>
    </div>
</main>

<!-- Details Modal -->
<div class="modal fade" id="viewRequestModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content drive-modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="request-avatar bg-success-subtle text-success" id="view_avatar">MG</div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark m-0" id="view_gardener_title">Request Details</h5>
                        <small class="text-muted" id="view_date"></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <!-- Contact info card -->
                <div class="p-3 bg-light rounded-3 mb-3 border border-light-subtle">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <strong class="text-dark" id="view_gardener"></strong>
                        <span id="view_status_pill"></span>
                    </div>
                    <div class="text-muted small d-flex flex-wrap gap-3 mt-1" id="view_contact">
                        <!-- email and phone links -->
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="p-2.5 bg-light rounded-3 border border-light-subtle">
                            <label class="text-secondary fw-semibold d-block mb-1" style="font-size: 11px;">TARGET PLOT</label>
                            <div class="text-dark fw-bold small" id="view_plot"></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2.5 bg-light rounded-3 border border-light-subtle">
                            <label class="text-secondary fw-semibold d-block mb-1" style="font-size: 11px;">REQUESTED TERM</label>
                            <div class="text-dark fw-bold small" id="view_duration"></div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-secondary fw-semibold d-block mb-1" style="font-size: 11px;">GARDENING PURPOSE / PROPOSAL</label>
                    <p class="text-dark bg-light p-3 rounded-3 mb-0 border border-light-subtle" style="font-size: 0.85rem; line-height: 1.5;" id="view_purpose"></p>
                </div>

                <!-- Gardener Counter-Offer Section -->
                <div class="mb-3 d-none" id="view_counter_offer_section">
                    <div class="p-3 rounded-4 border" style="background:#F0F9FF;border-color:#BAE6FD !important;">
                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                            <span class="badge rounded-pill px-2.5 py-1 text-xs fw-bold" style="background:#E0F2FE;color:#0369A1;border:1px solid #BAE6FD;">
                                <i class="bi bi-arrow-left-right me-1 text-primary"></i> Gardener Counter-Offer Proposed
                            </span>
                            <span class="text-muted small" style="font-size:11px;" id="view_counter_time"></span>
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mb-1">
                            <span class="fs-4 fw-bolder text-dark" id="view_counter_rate">₱95,000.00</span>
                            <span class="text-secondary small fw-medium">/ Year</span>
                            <span class="text-muted small" id="view_counter_monthly">(₱7,916.67/mo)</span>
                        </div>
                        <div class="small text-secondary p-2.5 rounded-3 bg-white border border-light-subtle" id="view_counter_notes">
                            No explanation provided.
                        </div>
                    </div>
                </div>

                <div class="mb-3 d-none" id="view_feedback_section">
                    <label class="text-secondary fw-semibold d-block mb-1" style="font-size: 11px;">RESPONSE / FEEDBACK</label>
                    <div class="alert py-2 px-3 mb-0" style="font-size: 0.85rem; border-radius: 8px;" id="view_feedback"></div>
                </div>

                <!-- Land Location Map inside Application Details -->
                <div class="mb-3 d-none" id="view_map_section">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label class="text-secondary fw-semibold mb-0" style="font-size: 11px;">LAND LOCATION</label>
                        <span class="text-muted small" id="view_map_coords" style="font-size: 0.72rem;"></span>
                    </div>
                    <div class="rounded-4 overflow-hidden border mb-2" style="border-color:#DFCFC2 !important;">
                        <div id="viewLeafletMap" style="height: 180px; width: 100%; z-index: 1;"></div>
                    </div>
                    <div class="text-end">
                        <a id="view_gmaps_link" href="#" target="_blank" rel="noopener"
                           class="btn btn-sm rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1.5 text-decoration-none"
                           style="background:#E8F3F8;color:#204E6E;border:1.5px solid #C4DCEB;font-size:0.75rem;">
                            <i class="bi bi-box-arrow-up-right"></i> Open in Google Maps
                        </a>
                    </div>
                </div>

                <!-- Modal footer actions -->
                <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                    <div id="modalPendingActions" class="d-flex gap-2">
                        <!-- Dynamic Approve & Reject buttons injected here if pending -->
                    </div>
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectRequestModal" tabindex="-1" aria-hidden="true" style="z-index: 10000;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content drive-modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold text-dark m-0">Reject Application</h5>
                    <p class="text-muted small mb-0 mt-1">Provide feedback for <strong id="rejectGardenerName">the gardener</strong></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <form id="rejectRequestForm">
                    <input type="hidden" id="reject_request_id" name="id">

                    <!-- Quick Reason Chips -->
                    <div class="mb-3">
                        <label class="form-label text-secondary d-block mb-2" style="font-size: 0.75rem; font-weight: 600;">QUICK REASONS</label>
                        <div class="d-flex flex-wrap gap-1.5" id="quickReasonChips">
                            <span class="quick-reason-chip" data-reason="This plot is already reserved or occupied.">Plot occupied</span>
                            <span class="quick-reason-chip" data-reason="The requested duration exceeds the allowed seasonal term.">Term too long</span>
                            <span class="quick-reason-chip" data-reason="The proposed crop/setup is not suitable for this soil or location.">Crop not suitable</span>
                            <span class="quick-reason-chip" data-reason="Plot capacity has been reached for this area.">Capacity reached</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="reject_notes" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight: 600;">REJECTION REASON / FEEDBACK</label>
                        <textarea class="form-control drive-form-control w-100" id="reject_notes" name="notes" rows="3"
                            required placeholder="Provide clear feedback on why this request cannot be accepted..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4">Confirm Rejection</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Send Rental Offer Modal (Landowner -> Gardener Offer Workflow) -->
<div class="modal fade" id="sendRentalOfferModal" tabindex="-1" aria-hidden="true" style="z-index: 10000;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content drive-modal-content border-0 shadow-xl" style="border-radius: 24px;">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div>
                    <h5 class="modal-title fw-bold text-dark m-0 d-flex align-items-center gap-2">
                        <i class="bi bi-tag-fill text-success"></i>
                        <span>Send Rental Offer to Gardener</span>
                    </h5>
                    <p class="text-muted small mb-0 mt-0.5">Gardener can review and decide whether to approve or decline</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pt-3 pb-4">
                <form id="sendRentalOfferForm">
                    <input type="hidden" id="offer_request_id" name="id">

                    <!-- Gardener & Plot Target Banner -->
                    <div class="p-3 rounded-3 mb-3 border border-light-subtle bg-light">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-secondary small fw-bold text-uppercase" style="font-size:10px;">APPLICANT</span>
                            <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis">Awaiting Offer</span>
                        </div>
                        <div class="fw-bold text-dark" id="offer_gardener_name">Gardener Name</div>
                        <div class="small text-muted" id="offer_plot_desc">Plot &amp; Property</div>
                    </div>

                    <!-- Rental Rate Field -->
                    <div class="mb-3">
                        <label for="offer_rental_rate" class="form-label text-secondary d-flex justify-content-between mb-1" style="font-size: 0.75rem; font-weight: 700;">
                            <span>PROPOSED ANNUAL RENTAL FEE (₱)</span>
                            <span class="text-muted font-normal">Benchmark: 50 m² @ ₱110k/yr</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-light-subtle fw-bold text-muted">₱</span>
                            <input type="number" step="100" min="0" class="form-control rounded-end-pill border-light-subtle ps-2 fw-bold"
                                id="offer_rental_rate" name="rental_rate" required placeholder="110000" value="110000">
                        </div>
                    </div>

                    <!-- Rental Duration -->
                    <div class="mb-3">
                        <label for="offer_rental_duration" class="form-label text-secondary mb-1" style="font-size: 0.75rem; font-weight: 700;">
                            LEASE TERM DURATION
                        </label>
                        <select class="form-select rounded-pill border-light-subtle text-sm" id="offer_rental_duration" name="rental_duration">
                            <option value="1 Year" selected>1 Year Lease Agreement</option>
                            <option value="6 Months">6 Months Seasonal Lease</option>
                            <option value="2 Years">2 Years Multi-Year Lease</option>
                        </select>
                    </div>

                    <!-- Terms & Conditions / Allowed Organic Crops -->
                    <div class="mb-4">
                        <label for="offer_terms" class="form-label text-secondary mb-1" style="font-size: 0.75rem; font-weight: 700;">
                            TERMS, CROPS &amp; GUIDELINES
                        </label>
                        <textarea class="form-control drive-form-control w-100" id="offer_terms" name="terms" rows="3"
                            placeholder="Permitted crops, plot guidelines, and water access included.">Standard 50 sq. m. community plot lease. Organic vegetables and herbs permitted. Water spigot access provided.</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-3.5" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold shadow-xs">
                            <i class="bi bi-send-fill me-1"></i> Send Offer to Renter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Toast Feedback Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 10500;">
    <div id="requestToast" class="toast align-items-center text-white bg-dark border-0 rounded-4 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body py-2.5 px-3 d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill text-success" id="toastIcon"></i>
                <span id="toastMsg" class="small fw-medium">Action completed successfully.</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script src="<?php echo $base_path; ?>assets/js/landowner_requests.js?v=<?php echo time(); ?>"></script>

<?php include '../includes/footer.php'; ?>