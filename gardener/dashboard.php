<?php
$base_path = '../';
$page_title = "Gardener Overview";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

// Prepare session defaults for gardener
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['active_role'] = 'gardener';
$_SESSION['user_name'] = 'Mary Gardener';
?>

<main class="workspace-surface">
    <!-- Toolbar/Title Bar -->
    <div class="toolbar border-bottom">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">Overview</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="browse.php" class="btn btn-drive-primary btn-sm px-3 d-flex align-items-center gap-2">
                <i data-lucide="search" style="width: 16px; height: 16px;"></i>
                <span>Browse Lands</span>
            </a>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll">
        <!-- 1. Stats Row -->
        <div class="row g-3 mb-4">
            <!-- Stat 1: Plots leased -->
            <div class="col-md-4">
                <div class="drive-card d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                        style="width: 52px; height: 52px; min-width: 52px; background-color: #e8f0fe; color: #1a73e8;">
                        <i data-lucide="layout-grid" style="width: 28px; height: 28px;"></i>
                    </div>
                    <div>
                        <span class="text-secondary fw-semibold d-block"
                            style="font-size: 0.75rem; line-height: 1.2;">Leased Plots</span>
                        <h3 class="fw-bold m-0 mt-1 text-dark" style="font-size: 1.5rem;">2</h3>
                        <p class="text-muted mb-0 mt-0.5" style="font-size: 0.7rem;">Active plots</p>
                    </div>
                </div>
            </div>
            <!-- Stat 2: Yield Recorded -->
            <div class="col-md-4">
                <div class="drive-card d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                        style="width: 52px; height: 52px; min-width: 52px; background-color: #f3e5f5; color: #8e24aa;">
                        <i data-lucide="shopping-bag" style="width: 28px; height: 28px;"></i>
                    </div>
                    <div>
                        <span class="text-secondary fw-semibold d-block"
                            style="font-size: 0.75rem; line-height: 1.2;">Total Yield</span>
                        <h3 class="fw-bold m-0 mt-1" style="font-size: 1.5rem; color: #8e24aa;">57.5 <span
                                style="font-size: 0.9rem; font-weight: normal; color: var(--bs-secondary-color);">kg</span>
                        </h3>
                        <p class="text-muted mb-0 mt-0.5" style="font-size: 0.7rem;">Total harvested</p>
                    </div>
                </div>
            </div>
            <!-- Stat 3: Scheduled Tasks -->
            <div class="col-md-4">
                <div class="drive-card d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center bg-warning-subtle text-warning-emphasis"
                        style="width: 52px; height: 52px; min-width: 52px;">
                        <i data-lucide="calendar" style="width: 28px; height: 28px;"></i>
                    </div>
                    <div>
                        <span class="text-secondary fw-semibold d-block"
                            style="font-size: 0.75rem; line-height: 1.2;">Pending Tasks</span>
                        <h3 class="fw-bold m-0 mt-1 text-warning" style="font-size: 1.5rem;">2</h3>
                        <p class="text-muted mb-0 mt-0.5" style="font-size: 0.7rem;">Upcoming tasks</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Environmental Contribution Tracker -->
        <?php include '../includes/eco_tracker.php'; ?>

        <!-- 2. Active Plot Folders -->
        <h2 class="fs-6 fw-semibold text-secondary mb-3" style="letter-spacing: 0.5px; text-transform: uppercase;">My Plots</h2>
        <div class="row g-3 mb-4">
            <!-- Plot Card 1 -->
            <div class="col-md-6">
                <div class="drive-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <img src="../assets/crop-icons/tomato/tomato.svg" style="width: 22px; height: 22px;" alt="Tomato">
                            <span class="fw-semibold text-dark">Downtown Rooftop - Plot B-1</span>
                        </div>
                        <span class="badge rounded-pill"
                            style="font-size: 10px; background-color: #e8f0fe; color: #1a73e8;">Leased</span>
                    </div>
                    <p class="text-muted mb-2" style="font-size: 0.8rem;">Cultivating: <strong>Organic Tomatoes</strong></p>
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                        <span class="text-secondary d-flex align-items-center gap-1" style="font-size: 0.75rem;"><i data-lucide="maximize-2" style="width: 12px; height: 12px;"></i> 20 m²</span>
                        <a href="harvests.php" class="btn btn-sm btn-drive-primary">Record Yield</a>
                    </div>
                </div>
            </div>
            <!-- Plot Card 2 -->
            <div class="col-md-6">
                <div class="drive-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <img src="../assets/crop-icons/romaine/romaine.svg" style="width: 22px; height: 22px;" alt="Lettuce">
                            <span class="fw-semibold text-dark">Downtown Rooftop - Plot B-2</span>
                        </div>
                        <span class="badge rounded-pill"
                            style="font-size: 10px; background-color: #e8f0fe; color: #1a73e8;">Leased</span>
                    </div>
                    <p class="text-muted mb-2" style="font-size: 0.8rem;">Cultivating: <strong>Romaine Lettuce</strong></p>
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                        <span class="text-secondary d-flex align-items-center gap-1" style="font-size: 0.75rem;"><i data-lucide="maximize-2" style="width: 12px; height: 12px;"></i> 20 m²</span>
                        <a href="harvests.php" class="btn btn-sm btn-drive-primary">Record Yield</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Immediate Upcoming Schedule Assigned Tasks -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="fs-6 fw-semibold text-secondary m-0" style="letter-spacing: 0.5px; text-transform: uppercase;">
                Upcoming Tasks</h2>
            <a href="schedules.php" class="text-decoration-none text-primary d-inline-flex align-items-center gap-1"
                style="font-size: 0.8rem; font-weight: 500;">View Schedule <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i></a>
        </div>

        <div class="border rounded-4 bg-white overflow-hidden border-light-subtle">
            <!-- Header Row -->
            <div class="d-flex align-items-center justify-content-between px-4 py-2 bg-light border-bottom text-secondary"
                style="font-size: 0.75rem; font-weight: 600;">
                <div class="w-30">TASK</div>
                <div class="w-30">LOCATION</div>
                <div class="w-25">SCHEDULED</div>
                <div class="w-15 text-end">TYPE</div>
            </div>

            <!-- Row 1 -->
            <div class="d-flex align-items-center justify-content-between px-4 py-2 border-bottom hover:bg-[#f8faff] text-sm text-dark transition-colors"
                style="height: 48px;">
                <div class="w-30 fw-semibold d-flex align-items-center gap-2"><i data-lucide="droplet" style="color: #1a73e8; width: 16px; height: 16px;"></i> Water the tomato plant</div>
                <div class="w-30 text-secondary">Downtown Rooftop (B-1, B-2)</div>
                <div class="w-25 text-secondary">Tomorrow, 09:00 AM</div>
                <div class="w-15 text-end"><span
                        class="badge rounded-pill px-2" style="background-color: #e8f0fe; color: #1a73e8;">Watering</span></div>
            </div>

            <!-- Row 2 -->
            <div class="d-flex align-items-center justify-content-between px-4 py-2 border-bottom hover:bg-[#f8faff] text-sm text-dark transition-colors"
                style="height: 48px;">
                <div class="w-30 fw-semibold d-flex align-items-center gap-2"><i data-lucide="file-text" style="color: #8e24aa; width: 16px; height: 16px;"></i> Tomato Seedling Planting
                </div>
                <div class="w-30 text-secondary">Downtown Rooftop (A-1)</div>
                <div class="w-25 text-secondary">Aug 09, 07:00 AM</div>
                <div class="w-15 text-end"><span
                        class="badge rounded-pill px-2" style="background-color: #f3e5f5; color: #8e24aa;">Planting</span></div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>