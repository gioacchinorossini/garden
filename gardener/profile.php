<?php
$base_path = '../';
$page_title = "Gardener Profile & Eco Impact";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['active_role'] = 'gardener';
if (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = 'Mary Gardener';
}

$user_email = $_SESSION['user_email'] ?? 'mary.gardener@example.com';
$user_phone = $_SESSION['user_phone'] ?? '+63 918 765 4321';
$user_address = $_SESSION['user_address'] ?? 'Plot B-1, Downtown Rooftop Garden, Manila';
$user_bio = $_SESSION['user_bio'] ?? 'Passionate urban gardener specialized in organic vegetable cultivation, companion planting, drip irrigation, and composting.';

// Process mock profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $_SESSION['user_name'] = htmlspecialchars(trim($_POST['user_name']));
    $_SESSION['user_email'] = htmlspecialchars(trim($_POST['user_email']));
    $_SESSION['user_phone'] = htmlspecialchars(trim($_POST['user_phone']));
    $_SESSION['user_address'] = htmlspecialchars(trim($_POST['user_address']));
    $_SESSION['user_bio'] = htmlspecialchars(trim($_POST['user_bio']));
    $success_msg = "Profile updated successfully!";
}

// Calculate completed tasks from schedule
$sched_list = $_SESSION['mock_schedules'] ?? [];
$completed_scheds = array_filter($sched_list, fn($s) => ($s['status'] ?? '') === 'completed');
$completed_tasks_count = count($completed_scheds);
?>

<main class="workspace-surface">
    <!-- Toolbar (Title and actions) -->
    <div class="toolbar justify-content-between align-items-center">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">Gardener Profile & Eco Impact</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-drive-secondary btn-sm px-3 d-flex align-items-center gap-2 rounded-pill"
                data-bs-toggle="modal" data-bs-target="#editProfileModal">
                <i data-lucide="edit-3" style="width: 16px; height: 16px;"></i>
                <span>Edit Profile</span>
            </button>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll">
        <?php if (isset($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 text-sm mb-4" role="alert">
                <i data-lucide="check-circle" class="me-2" style="width: 16px; height: 16px; display: inline;"></i>
                <?php echo $success_msg; ?>
                <button type="button" class="btn-close text-xs" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Gardener Profile Card -->
        <div class="card border rounded-4 overflow-hidden mb-4 bg-white shadow-xs" style="border-color: var(--drive-border) !important;">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start gap-4">
                    <!-- Avatar -->
                    <div class="position-relative">
                        <div class="w-24 h-24 rounded-full bg-emerald-600 text-white d-flex align-items-center justify-content-center fw-bold fs-2 shadow-sm border-4 border-white">
                            <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                        </div>
                        <span class="position-absolute bottom-0 end-0 bg-emerald-500 text-white rounded-full p-1 border-2 border-white" title="Verified Gardener">
                            <i data-lucide="sprout" style="width: 14px; height: 14px;"></i>
                        </span>
                    </div>

                    <!-- Profile Info -->
                    <div class="flex-grow-1 text-center text-md-start">
                        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-2 mb-2">
                            <div>
                                <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2">
                                    <h2 class="fs-4 fw-bold text-dark mb-0"><?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
                                    <span class="badge rounded-pill bg-emerald-100 text-emerald-800 text-xs font-semibold px-2.5 py-1">Gardener</span>
                                </div>
                                <p class="text-secondary text-xs mb-0 mt-1 d-flex align-items-center justify-content-center justify-content-md-start gap-1">
                                    <i data-lucide="map-pin" style="width: 14px; height: 14px;"></i>
                                    <?php echo htmlspecialchars($user_address); ?>
                                </p>
                            </div>

                            <button class="btn btn-sm btn-drive-primary rounded-pill px-3 py-1.5 text-xs font-semibold d-flex align-items-center gap-1.5"
                                data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                <i data-lucide="settings" style="width: 14px; height: 14px;"></i>
                                Profile Settings
                            </button>
                        </div>

                        <p class="text-dark text-xs mb-3 max-w-2xl"><?php echo htmlspecialchars($user_bio); ?></p>

                        <!-- Quick Details -->
                        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-4 pt-3 border-top text-xs text-secondary">
                            <div class="d-flex align-items-center gap-1.5">
                                <i data-lucide="mail" style="width: 14px; height: 14px;" class="text-primary"></i>
                                <span><?php echo htmlspecialchars($user_email); ?></span>
                            </div>
                            <div class="d-flex align-items-center gap-1.5">
                                <i data-lucide="phone" style="width: 14px; height: 14px;" class="text-primary"></i>
                                <span><?php echo htmlspecialchars($user_phone); ?></span>
                            </div>
                            <div class="d-flex align-items-center gap-1.5">
                                <i data-lucide="calendar" style="width: 14px; height: 14px;" class="text-primary"></i>
                                <span>Active Gardener since Feb 2025</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="bg-light border-top p-3 px-4">
                <div class="row text-center g-3">
                    <div class="col-4 border-end">
                        <span class="text-secondary text-[11px] font-semibold uppercase tracking-wider d-block">Active Plots</span>
                        <span class="fs-5 fw-bold text-dark">2</span>
                    </div>
                    <div class="col-4 border-end">
                        <span class="text-secondary text-[11px] font-semibold uppercase tracking-wider d-block">Tasks Completed</span>
                        <span class="fs-5 fw-bold text-emerald-600"><?php echo $completed_tasks_count; ?></span>
                    </div>
                    <div class="col-4">
                        <span class="text-secondary text-[11px] font-semibold uppercase tracking-wider d-block">Total Harvests</span>
                        <span class="fs-5 fw-bold text-primary">42.5 <span class="fs-6 font-normal text-muted">kg</span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gamified Environmental Contribution Tracker Section -->
        <h2 class="fs-6 fw-semibold text-secondary mb-3" style="letter-spacing: 0.5px; text-transform: uppercase;">
            Environmental Contribution & Eco Badges
        </h2>

        <?php
        include '../includes/eco_tracker.php';
        ?>

        <!-- Assigned Plots & Cultivation -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="fs-6 fw-semibold text-secondary m-0" style="letter-spacing: 0.5px; text-transform: uppercase;">
                Assigned Plots & Crops
            </h2>
            <a href="schedules.php" class="text-decoration-none text-primary d-inline-flex align-items-center gap-1 text-xs fw-semibold">
                View My Tasks & Schedules <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
            </a>
        </div>

        <div class="row g-3 mb-4">
            <!-- Plot Card 1 -->
            <div class="col-md-6">
                <div class="drive-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge rounded-pill bg-emerald-100 text-emerald-800 text-[10px] font-semibold">Plot A-1 • Leased</span>
                            <span class="text-xs text-secondary font-semibold"><i data-lucide="sprout" class="text-emerald-500 inline me-1" style="width: 12px; height: 12px;"></i> Tomatoes</span>
                        </div>
                        <h3 class="fs-6 fw-bold text-dark mb-1">Downtown Rooftop Garden</h3>
                        <p class="text-secondary text-xs mb-3"><i data-lucide="map-pin" class="inline me-1" style="width: 12px; height: 12px;"></i>45 Main St, Business District</p>
                        <div class="bg-light p-2.5 rounded-3 border mb-3 text-xs">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Plot Size:</span>
                                <span class="fw-semibold text-dark">25 m²</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Crop Status:</span>
                                <span class="fw-semibold text-emerald-700">Flowering & Fruiting</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Next Activity:</span>
                                <span class="fw-semibold text-primary">Watering & Soil Inspection</span>
                            </div>
                        </div>
                    </div>
                    <div class="pt-2 border-top d-flex align-items-center justify-content-between">
                        <span class="text-xs text-muted">Health Score: 96%</span>
                        <a href="schedules.php" class="btn btn-sm btn-drive-primary text-xs">Manage Tasks</a>
                    </div>
                </div>
            </div>

            <!-- Plot Card 2 -->
            <div class="col-md-6">
                <div class="drive-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge rounded-pill bg-emerald-100 text-emerald-800 text-[10px] font-semibold">Plot B-2 • Leased</span>
                            <span class="text-xs text-secondary font-semibold"><i data-lucide="sprout" class="text-emerald-500 inline me-1" style="width: 12px; height: 12px;"></i> Organic Herbs</span>
                        </div>
                        <h3 class="fs-6 fw-bold text-dark mb-1">Sunnyvale Community Yard</h3>
                        <p class="text-secondary text-xs mb-3"><i data-lucide="map-pin" class="inline me-1" style="width: 12px; height: 12px;"></i>124 Green Ave, Sunnyvale</p>
                        <div class="bg-light p-2.5 rounded-3 border mb-3 text-xs">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Plot Size:</span>
                                <span class="fw-semibold text-dark">18 m²</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Crop Status:</span>
                                <span class="fw-semibold text-emerald-700">Ready for Harvest</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Harvest Estimate:</span>
                                <span class="fw-semibold text-success">~12 kg Basil & Mint</span>
                            </div>
                        </div>
                    </div>
                    <div class="pt-2 border-top d-flex align-items-center justify-content-between">
                        <span class="text-xs text-muted">Health Score: 98%</span>
                        <a href="harvests.php" class="btn btn-sm btn-drive-secondary text-xs">Record Harvest</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content drive-modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold text-dark" id="editProfileModalLabel">Edit Gardener Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="profile.php" method="POST">
                <input type="hidden" name="action" value="update_profile">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="userName" class="form-label text-secondary" style="font-size: 0.75rem; font-weight:600;">FULL NAME</label>
                        <input type="text" class="form-control drive-form-control w-100" id="userName" name="user_name"
                            value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="userEmail" class="form-label text-secondary" style="font-size: 0.75rem; font-weight:600;">EMAIL ADDRESS</label>
                            <input type="email" class="form-control drive-form-control w-100" id="userEmail" name="user_email"
                                value="<?php echo htmlspecialchars($user_email); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="userPhone" class="form-label text-secondary" style="font-size: 0.75rem; font-weight:600;">PHONE NUMBER</label>
                            <input type="tel" class="form-control drive-form-control w-100" id="userPhone" name="user_phone"
                                value="<?php echo htmlspecialchars($user_phone); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="userAddress" class="form-label text-secondary" style="font-size: 0.75rem; font-weight:600;">GARDEN LOCATION / ADDRESS</label>
                        <input type="text" class="form-control drive-form-control w-100" id="userAddress" name="user_address"
                            value="<?php echo htmlspecialchars($user_address); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="userBio" class="form-label text-secondary" style="font-size: 0.75rem; font-weight:600;">BIO & EXPERIENCE</label>
                        <textarea class="form-control drive-form-control w-100" id="userBio" name="user_bio" rows="3"><?php echo htmlspecialchars($user_bio); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-drive-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-drive-primary btn-sm rounded-pill px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
