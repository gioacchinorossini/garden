<?php
$base_path = '../';
$page_title = "Landowner Dashboard";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

// Prepare mock requests and schedule counts for landowner
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Set mock role to landowner if switched
$_SESSION['active_role'] = 'landowner';
$_SESSION['user_name'] = 'John Landowner';
?>

<main class="workspace-surface">
    <!-- Toolbar/Title Bar -->
    <div class="toolbar border-bottom">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">Landowner Overview</h1>
            <p class="text-muted mb-0" style="font-size: 0.75rem;">Manage your community gardening spaces, view gardener
                requests, and monitor schedules</p>
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
        <!-- 1. Stats row -->
        <div class="row g-3 mb-4">
            <!-- Metric 1: Lands Owned -->
            <div class="col-md-4">
                <div class="drive-card d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center bg-primary-subtle text-primary"
                        style="width: 52px; height: 52px; min-width: 52px; font-size: 1.8rem;">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <div>
                        <span class="text-secondary fw-semibold d-block"
                            style="font-size: 0.75rem; line-height: 1.2;">My Properties</span>
                        <h3 class="fw-bold m-0 mt-1 text-dark" style="font-size: 1.5rem;">3</h3>
                        <p class="text-muted mb-0 mt-0.5" style="font-size: 0.7rem;">2 approved, 1 pending</p>
                    </div>
                </div>
            </div>
            <!-- Metric 2: Gardener Requests -->
            <div class="col-md-4">
                <div class="drive-card d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center bg-warning-subtle text-warning-emphasis"
                        style="width: 52px; height: 52px; min-width: 52px; font-size: 1.8rem;">
                        <i class="bi bi-file-earmark-person"></i>
                    </div>
                    <div>
                        <span class="text-secondary fw-semibold d-block"
                            style="font-size: 0.75rem; line-height: 1.2;">Plot Requests</span>
                        <h3 class="fw-bold m-0 mt-1 text-warning" style="font-size: 1.5rem;">2</h3>
                        <p class="text-muted mb-0 mt-0.5" style="font-size: 0.7rem;">Needs responses</p>
                    </div>
                </div>
            </div>
            <!-- Metric 3: Active Gardeners -->
            <div class="col-md-4">
                <div class="drive-card d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center bg-success-subtle text-success"
                        style="width: 52px; height: 52px; min-width: 52px; font-size: 1.8rem;">
                        <i class="bi bi-flower3"></i>
                    </div>
                    <div>
                        <span class="text-secondary fw-semibold d-block"
                            style="font-size: 0.75rem; line-height: 1.2;">Occupied Plots</span>
                        <h3 class="fw-bold m-0 mt-1 text-success" style="font-size: 1.5rem;">8 <span
                                style="font-size: 1rem; font-weight: normal; color: var(--bs-secondary-color);">/
                                12</span></h3>
                        <p class="text-muted mb-0 mt-0.5" style="font-size: 0.7rem;">Active units</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Folder-grid Layout for My Registered Lands -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="fs-6 fw-semibold text-secondary m-0" style="letter-spacing: 0.5px; text-transform: uppercase;">My
                Gardening Lands</h2>
            <a href="lands.php" class="text-decoration-none text-primary"
                style="font-size: 0.8rem; font-weight: 500;">Manage Lands & Plots <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="row g-3 mb-4">
            <!-- Land Card 1 -->
            <div class="col-md-6">
                <div class="drive-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-folder-fill text-warning fs-5"></i>
                            <span class="fw-semibold text-dark">Sunnyvale Gardening Lot</span>
                        </div>
                        <span class="badge bg-warning text-dark rounded-pill" style="font-size: 10px;">Pending
                            Review</span>
                    </div>
                    <div class="p-3 bg-light rounded-3 mb-2" style="font-size: 0.8rem;">
                        <p class="mb-1"><i class="bi bi-geo-alt-fill text-muted me-1"></i> 124 Green Ave, Sunnyvale</p>
                        <p class="mb-0 text-muted"><i class="bi bi-arrows-angle-expand me-1"></i> 250.0 m² • 0 Active
                            Plots</p>
                    </div>
                    <div class="d-flex justify-content-end gap-1 mt-2">
                        <a href="lands.php" class="btn btn-sm btn-outline-secondary rounded-pill">Manage Plots</a>
                    </div>
                </div>
            </div>
            <!-- Land Card 2 -->
            <div class="col-md-6">
                <div class="drive-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-folder-fill text-warning fs-5"></i>
                            <span class="fw-semibold text-dark">Downtown Rooftop Garden</span>
                        </div>
                        <span class="badge bg-success rounded-pill" style="font-size: 10px;">Approved</span>
                    </div>
                    <div class="p-3 bg-light rounded-3 mb-2" style="font-size: 0.8rem;">
                        <p class="mb-1"><i class="bi bi-geo-alt-fill text-muted me-1"></i> 45 Main St, Business District
                        </p>
                        <p class="mb-0 text-muted"><i class="bi bi-arrows-angle-expand me-1"></i> 85.5 m² • 4 Active
                            Plots</p>
                    </div>
                    <div class="d-flex justify-content-end gap-1 mt-2">
                        <a href="lands.php" class="btn btn-sm btn-outline-secondary rounded-pill">Manage Plots</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Immediate Alerts / Pending plot requests -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="fs-6 fw-semibold text-secondary m-0" style="letter-spacing: 0.5px; text-transform: uppercase;">
                Incoming Plot Requests</h2>
            <a href="requests.php" class="text-decoration-none text-primary"
                style="font-size: 0.8rem; font-weight: 500;">Review Requests <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="border rounded-4 bg-white overflow-hidden  border-light-subtle">
            <!-- Header Row -->
            <div class="d-flex align-items-center justify-content-between px-4 py-2 bg-light border-bottom text-secondary"
                style="font-size: 0.75rem; font-weight: 600;">
                <div class="w-40">GARDENER</div>
                <div class="w-30">REQUESTED LAND</div>
                <div class="w-15">DURATION</div>
                <div class="w-15 text-end">ACTION</div>
            </div>

            <!-- Row 1 -->
            <div class="d-flex align-items-center justify-content-between px-4 py-2 border-bottom hover:bg-[#f8faff] text-sm text-dark transition-colors"
                style="height: 54px;">
                <div class="w-40 d-flex align-items-center gap-2">
                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 28px; height: 28px; font-weight: 600; font-size:11px;">M</div>
                    <span class="fw-semibold text-dark">Mary Gardener</span>
                </div>
                <div class="w-30 text-secondary">Downtown Rooftop</div>
                <div class="w-15 text-secondary">6 Months</div>
                <div class="w-15 text-end">
                    <a href="requests.php" class="btn btn-sm btn-drive-primary">Review</a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>