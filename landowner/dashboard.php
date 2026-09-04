<?php
$base_path = '../';
$page_title = "Landowner Dashboard";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

// Prepare session defaults for landowner
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['active_role'] = 'landowner';
$_SESSION['user_name'] = 'John Landowner';

$ui_mode = isset($_SESSION['ui_mode']) ? $_SESSION['ui_mode'] : 'drive';

if ($ui_mode === 'modern') {
    include '../includes/landowner_modern_ui.php';
    include '../includes/footer.php';
    exit;
}
?>

<main class="workspace-surface">
    <!-- Toolbar/Title Bar -->
    <div class="toolbar border-bottom">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">Overview</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="register.php" class="btn btn-drive-primary btn-sm px-3 d-flex align-items-center gap-2">
                <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                <span>Register Land</span>
            </a>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll">
        <!-- 1. Key Metrics Cards Grid -->
        <div class="row g-3 mb-4">
            <!-- Metric 1: My Properties -->
            <div class="col-md-4">
                <div class="drive-card d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                        style="width: 52px; height: 52px; min-width: 52px; background-color: #e8f0fe; color: #1a73e8;">
                        <i data-lucide="map-pin" style="width: 28px; height: 28px;"></i>
                    </div>
                    <div>
                        <span class="text-secondary fw-semibold d-block"
                            style="font-size: 0.75rem; line-height: 1.2;">My Properties</span>
                        <h3 class="fw-bold m-0 mt-1 text-dark" style="font-size: 1.5rem;">3</h3>
                        <p class="text-muted mb-0 mt-0.5" style="font-size: 0.7rem;">2 approved, 1 pending</p>
                    </div>
                </div>
            </div>
            <!-- Metric 2: Pending Requests -->
            <div class="col-md-4">
                <div class="drive-card d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center bg-warning-subtle text-warning-emphasis"
                        style="width: 52px; height: 52px; min-width: 52px;">
                        <i data-lucide="file-text" style="width: 28px; height: 28px;"></i>
                    </div>
                    <div>
                        <span class="text-secondary fw-semibold d-block"
                            style="font-size: 0.75rem; line-height: 1.2;">Plot Requests</span>
                        <h3 class="fw-bold m-0 mt-1 text-warning" style="font-size: 1.5rem;">1</h3>
                        <p class="text-muted mb-0 mt-0.5" style="font-size: 0.7rem;">Requires action</p>
                    </div>
                </div>
            </div>
            <!-- Metric 3: Active Gardeners -->
            <div class="col-md-4">
                <div class="drive-card d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                        style="width: 52px; height: 52px; min-width: 52px; background-color: #f3e5f5; color: #8e24aa;">
                        <i data-lucide="layout-grid" style="width: 28px; height: 28px;"></i>
                    </div>
                    <div>
                        <span class="text-secondary fw-semibold d-block"
                            style="font-size: 0.75rem; line-height: 1.2;">Occupied Plots</span>
                        <h3 class="fw-bold m-0 mt-1" style="font-size: 1.5rem; color: #8e24aa;">8 <span
                                style="font-size: 1rem; font-weight: normal; color: var(--bs-secondary-color);">/
                                12</span></h3>
                        <p class="text-muted mb-0 mt-0.5" style="font-size: 0.7rem;">Active units</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Folder-grid Layout for My Registered Lands -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="fs-6 fw-semibold text-secondary m-0" style="letter-spacing: 0.5px; text-transform: uppercase;">My Lands</h2>
            <a href="lands.php" class="text-decoration-none text-primary d-inline-flex align-items-center gap-1"
                style="font-size: 0.8rem; font-weight: 500;">Manage Lands <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i></a>
        </div>

        <div class="row g-3 mb-4">
            <!-- Land Card 1 -->
            <div class="col-md-6">
                <div class="drive-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <i data-lucide="folder-open" class="text-warning" style="width: 20px; height: 20px;"></i>
                            <span class="fw-semibold text-dark">Sunnyvale Gardening Lot</span>
                        </div>
                        <span class="badge bg-warning text-dark rounded-pill" style="font-size: 10px;">Pending</span>
                    </div>
                    <div class="p-3 bg-light rounded-3 mb-2" style="font-size: 0.8rem;">
                        <p class="mb-1 d-flex align-items-center gap-1"><i data-lucide="map-pin" class="text-muted" style="width: 14px; height: 14px;"></i> 124 Green Ave, Sunnyvale</p>
                        <p class="mb-0 text-muted d-flex align-items-center gap-1"><i data-lucide="maximize-2" style="width: 14px; height: 14px;"></i> 250.0 m² • 0 Active
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
                            <i data-lucide="folder-open" class="text-warning" style="width: 20px; height: 20px;"></i>
                            <span class="fw-semibold text-dark">Downtown Rooftop Garden</span>
                        </div>
                        <span class="badge rounded-pill" style="font-size: 10px; background-color: #e8f0fe; color: #1a73e8;">Approved</span>
                    </div>
                    <div class="p-3 bg-light rounded-3 mb-2" style="font-size: 0.8rem;">
                        <p class="mb-1 d-flex align-items-center gap-1"><i data-lucide="map-pin" class="text-muted" style="width: 14px; height: 14px;"></i> 45 Main St, Business District
                        </p>
                        <p class="mb-0 text-muted d-flex align-items-center gap-1"><i data-lucide="maximize-2" style="width: 14px; height: 14px;"></i> 85.5 m² • 4 Active
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
                Plot Requests</h2>
            <a href="requests.php" class="text-decoration-none text-primary d-inline-flex align-items-center gap-1"
                style="font-size: 0.8rem; font-weight: 500;">Review Requests <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i></a>
        </div>

        <div class="border rounded-4 bg-white overflow-hidden  border-light-subtle">
            <!-- Header Row -->
            <div class="d-flex align-items-center justify-content-between px-4 py-2 bg-light border-bottom text-secondary"
                style="font-size: 0.75rem; font-weight: 600;">
                <div class="w-40">GARDENER</div>
                <div class="w-30">LAND</div>
                <div class="w-15">DURATION</div>
                <div class="w-15 text-end">ACTION</div>
            </div>

            <!-- Row 1 -->
            <div class="d-flex align-items-center justify-content-between px-4 py-2 border-bottom hover:bg-[#f8faff] text-sm text-dark transition-colors"
                style="height: 54px;">
                <div class="w-40 d-flex align-items-center gap-2">
                    <div class="text-white rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 28px; height: 28px; font-weight: 600; font-size:11px; background-color: #1a73e8;">M</div>
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