<?php
$base_path = '../';
$page_title = "My Lands & Plots";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="workspace-surface">
    <!-- Toolbar/Title Bar -->
    <div class="toolbar border-bottom">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">My Lands</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="register.php" class="btn btn-drive-primary btn-sm px-3 d-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i>
                <span>Register Land</span>
            </a>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll">
        <div class="row g-4">
            <!-- Left: Lands List (2 Columns / folder-grid style) -->
            <div class="col-md-6">
                <h2 class="fs-6 fw-semibold text-secondary mb-3"
                    style="letter-spacing: 0.5px; text-transform: uppercase;">Properties</h2>

                <!-- Lands list dynamic container -->
                <div id="landsContainer"></div>
            </div>

            <!-- Right: Partition Plots panel for Selected Land -->
            <div class="col-md-6">
                <div id="plotsWorkspace" style="display: none;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h2 class="fs-6 fw-semibold text-secondary m-0"
                            style="letter-spacing: 0.5px; text-transform: uppercase;">Plots for: <span
                                id="activeLandTitle" class="text-dark fw-bold"></span></h2>
                        <button class="btn btn-sm btn-drive-primary py-1 px-3 d-flex align-items-center gap-1"
                            data-bs-toggle="modal" data-bs-target="#addPlotModal">
                            <i class="bi bi-plus-lg"></i> Add Plot
                        </button>
                    </div>

                    <!-- Plots dynamic list -->
                    <div id="plotsContainer"></div>
                </div>

                <div id="noPlotsSelected"
                    class="card border rounded-4 p-5 text-center bg-light text-muted  h-100 d-flex flex-column align-items-center justify-content-center"
                    style="border-color: var(--drive-border) !important;">
                    <i class="bi bi-grid-3x3-gap fs-1 text-secondary mb-3"></i>
                    <p class="mb-0 fw-medium">No land selected</p>
                    <span class="text-secondary" style="font-size: 0.8rem;">Select "Manage Plots" on an approved land.</span>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Single Edit Land Details Modal -->
<div class="modal fade" id="editLandModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content drive-modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold text-dark">Edit Land</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editLandForm">
                    <input type="hidden" id="edit_land_id" name="land_id">
                    <div class="mb-3">
                        <label for="edit_title" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">TITLE</label>
                        <input type="text" class="form-control drive-form-control w-100" id="edit_title" name="title"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_address" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">ADDRESS</label>
                        <input type="text" class="form-control drive-form-control w-100" id="edit_address"
                            name="address" required>
                    </div>
                    <div class="mb-4">
                        <label for="edit_description" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">DESCRIPTION</label>
                        <textarea class="form-control drive-form-control w-100" id="edit_description" name="description"
                            rows="3"></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-drive-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-drive-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Plot Modal (Single instance) -->
<div class="modal fade" id="addPlotModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content drive-modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold text-dark">Add Plot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addPlotForm">
                    <input type="hidden" name="land_id" id="modalLandId" value="">

                    <div class="mb-3">
                        <label for="plot_number" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">PLOT NUMBER</label>
                        <input type="text" class="form-control drive-form-control w-100" id="plot_number"
                            name="plot_number" required placeholder="Plot C-1">
                    </div>
                    <div class="mb-4">
                        <label for="plot_area" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">AREA (m²)</label>
                        <input type="number" step="0.1" class="form-control drive-form-control w-100" id="plot_area"
                            name="plot_area" required placeholder="25.0">
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-drive-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-drive-primary">Create Plot</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo $base_path; ?>assets/js/landowner_lands.js"></script>

<?php include '../includes/footer.php'; ?>