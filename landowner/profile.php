<?php
$base_path = '../';
$page_title = "Landowner Profile & Eco Impact";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['active_role'] = 'landowner';
if (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = 'John Landowner';
}

$user_email = $_SESSION['user_email'] ?? 'john.landowner@example.com';
$user_phone = $_SESSION['user_phone'] ?? '+63 917 123 4567';
$user_address = $_SESSION['user_address'] ?? '124 Green Ave, Sunnyvale, Metro Manila';
$user_bio = $_SESSION['user_bio'] ?? 'Dedicated eco-conscious landowner offering idle urban spaces for sustainable community farming and organic crop cultivation.';

// Process mock profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $_SESSION['user_name'] = htmlspecialchars(trim($_POST['user_name']));
    $_SESSION['user_email'] = htmlspecialchars(trim($_POST['user_email']));
    $_SESSION['user_phone'] = htmlspecialchars(trim($_POST['user_phone']));
    $_SESSION['user_address'] = htmlspecialchars(trim($_POST['user_address']));
    $_SESSION['user_bio'] = htmlspecialchars(trim($_POST['user_bio']));
    $success_msg = "Profile updated successfully!";
}
?>

<main class="workspace-surface">
    <!-- Toolbar -->
    <div class="toolbar border-bottom">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">Landowner Profile & Eco Impact</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-drive-secondary btn-sm px-3 d-flex align-items-center gap-2"
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

        <!-- Landowner Profile Card -->
        <div class="card border rounded-4 overflow-hidden mb-4 bg-white shadow-xs" style="border-color: var(--drive-border) !important;">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start gap-4">
                    <!-- Avatar -->
                    <div class="position-relative">
                        <div class="w-24 h-24 rounded-full bg-drive-primary text-white d-flex align-items-center justify-content-center fw-bold fs-2 shadow-sm border-4 border-white">
                            <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                        </div>
                        <span class="position-absolute bottom-0 end-0 bg-emerald-500 text-white rounded-full p-1 border-2 border-white" title="Verified Landowner">
                            <i data-lucide="check" style="width: 14px; height: 14px;"></i>
                        </span>
                    </div>

                    <!-- Profile Info -->
                    <div class="flex-grow-1 text-center text-md-start">
                        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-2 mb-2">
                            <div>
                                <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2">
                                    <h2 class="fs-4 fw-bold text-dark mb-0"><?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
                                    <span class="badge rounded-pill bg-emerald-100 text-emerald-800 text-xs font-semibold px-2.5 py-1">Verified Landowner</span>
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
                                <span>Landowner since Jan 2025</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="bg-light border-top p-3 px-4">
                <div class="row text-center g-3">
                    <div class="col-4 border-end">
                        <span class="text-secondary text-[11px] font-semibold uppercase tracking-wider d-block">Registered Properties</span>
                        <span class="fs-5 fw-bold text-dark">3</span>
                    </div>
                    <div class="col-4 border-end">
                        <span class="text-secondary text-[11px] font-semibold uppercase tracking-wider d-block">Total Land Area</span>
                        <span class="fs-5 fw-bold text-primary">835.5 <span class="fs-6 font-normal text-muted">m²</span></span>
                    </div>
                    <div class="col-4">
                        <span class="text-secondary text-[11px] font-semibold uppercase tracking-wider d-block">Active Plots Leased</span>
                        <span class="fs-5 fw-bold text-emerald-600">8 <span class="fs-6 font-normal text-muted">/ 12</span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gamified Environmental Contribution Tracker Section -->
        <h2 class="fs-6 fw-semibold text-secondary mb-3" style="letter-spacing: 0.5px; text-transform: uppercase;">
            Environmental Contribution & Eco Badges
        </h2>

        <?php
        // Landowner custom eco metrics override
        $eco_level_override = 4;
        $eco_xp_override = 880;
        $co2_offset_override = 412.5;
        $water_saved_override = 3850;
        $organic_yield_override = 185.0;
        $streak_days_override = 14;
        include '../includes/eco_tracker.php';
        ?>

        <!-- Registered Properties & Eco Scores -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="fs-6 fw-semibold text-secondary m-0" style="letter-spacing: 0.5px; text-transform: uppercase;">
                Registered Land Portfolio
            </h2>
            <a href="lands.php" class="text-decoration-none text-primary d-inline-flex align-items-center gap-1 text-xs fw-semibold">
                Manage All Lands <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
            </a>
        </div>

        <div class="row g-3 mb-4">
            <!-- Property Card 1 -->
            <div class="col-md-4">
                <div class="drive-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge rounded-pill bg-emerald-100 text-emerald-800 text-[10px] font-semibold">Approved</span>
                            <span class="text-xs text-secondary font-semibold"><i data-lucide="leaf" class="text-emerald-500 inline me-1" style="width: 12px; height: 12px;"></i> +180kg CO₂</span>
                        </div>
                        <h3 class="fs-6 fw-bold text-dark mb-1">Sunnyvale Gardening Lot</h3>
                        <p class="text-secondary text-xs mb-3"><i data-lucide="map-pin" class="inline me-1" style="width: 12px; height: 12px;"></i>124 Green Ave, Sunnyvale</p>
                        <div class="bg-light p-2.5 rounded-3 border mb-3 text-xs">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Land Area:</span>
                                <span class="fw-semibold text-dark">250 m²</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Permitted Crops:</span>
                                <span class="fw-semibold text-dark">Root Veg, Tubers</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Active Gardeners:</span>
                                <span class="fw-semibold text-emerald-600">3 Active</span>
                            </div>
                        </div>
                    </div>
                    <div class="pt-2 border-top d-flex align-items-center justify-content-between">
                        <span class="text-xs text-muted">Plot Occupancy: 100%</span>
                        <a href="lands.php" class="btn btn-sm btn-drive-secondary text-xs">View Details</a>
                    </div>
                </div>
            </div>

            <!-- Property Card 2 -->
            <div class="col-md-4">
                <div class="drive-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge rounded-pill bg-emerald-100 text-emerald-800 text-[10px] font-semibold">Approved</span>
                            <span class="text-xs text-secondary font-semibold"><i data-lucide="leaf" class="text-emerald-500 inline me-1" style="width: 12px; height: 12px;"></i> +155kg CO₂</span>
                        </div>
                        <h3 class="fs-6 fw-bold text-dark mb-1">Downtown Rooftop Garden</h3>
                        <p class="text-secondary text-xs mb-3"><i data-lucide="map-pin" class="inline me-1" style="width: 12px; height: 12px;"></i>45 Main St, Business District</p>
                        <div class="bg-light p-2.5 rounded-3 border mb-3 text-xs">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Land Area:</span>
                                <span class="fw-semibold text-dark">85.5 m²</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Permitted Crops:</span>
                                <span class="fw-semibold text-dark">Leafy Greens, Herbs</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Active Gardeners:</span>
                                <span class="fw-semibold text-emerald-600">2 Active</span>
                            </div>
                        </div>
                    </div>
                    <div class="pt-2 border-top d-flex align-items-center justify-content-between">
                        <span class="text-xs text-muted">Plot Occupancy: 50%</span>
                        <a href="lands.php" class="btn btn-sm btn-drive-secondary text-xs">View Details</a>
                    </div>
                </div>
            </div>

            <!-- Property Card 3 -->
            <div class="col-md-4">
                <div class="drive-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge rounded-pill bg-amber-100 text-amber-800 text-[10px] font-semibold">Pending Review</span>
                            <span class="text-xs text-secondary font-semibold"><i data-lucide="clock" class="text-amber-500 inline me-1" style="width: 12px; height: 12px;"></i> Verification</span>
                        </div>
                        <h3 class="fs-6 fw-bold text-dark mb-1">Riverdale Acres</h3>
                        <p class="text-secondary text-xs mb-3"><i data-lucide="map-pin" class="inline me-1" style="width: 12px; height: 12px;"></i>Riverside Dr, Block B</p>
                        <div class="bg-light p-2.5 rounded-3 border mb-3 text-xs">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Land Area:</span>
                                <span class="fw-semibold text-dark">500 m²</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Permitted Crops:</span>
                                <span class="fw-semibold text-dark">Fruits, Legumes</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Active Gardeners:</span>
                                <span class="fw-semibold text-muted">Pending</span>
                            </div>
                        </div>
                    </div>
                    <div class="pt-2 border-top d-flex align-items-center justify-content-between">
                        <span class="text-xs text-muted">Status: Pending Verification</span>
                        <a href="lands.php" class="btn btn-sm btn-drive-secondary text-xs">View Status</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content drive-modal-content rounded-4 overflow-hidden border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3 bg-white">
                <h5 class="modal-title fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                    <i data-lucide="user-check" class="text-primary" style="width: 18px; height: 18px;"></i>
                    Edit Landowner Profile
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <input type="hidden" name="action" value="update_profile">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-secondary text-xs font-semibold">FULL NAME</label>
                        <input type="text" name="user_name" class="form-control drive-form-control text-xs"
                            value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary text-xs font-semibold">EMAIL ADDRESS</label>
                        <input type="email" name="user_email" class="form-control drive-form-control text-xs"
                            value="<?php echo htmlspecialchars($user_email); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary text-xs font-semibold">PHONE NUMBER</label>
                        <input type="text" name="user_phone" class="form-control drive-form-control text-xs"
                            value="<?php echo htmlspecialchars($user_phone); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary text-xs font-semibold">PRIMARY ADDRESS</label>
                        <input type="text" name="user_address" class="form-control drive-form-control text-xs"
                            value="<?php echo htmlspecialchars($user_address); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary text-xs font-semibold">BIOGRAPHY / ECO MISSION</label>
                        <textarea name="user_bio" class="form-control drive-form-control text-xs" rows="3"><?php echo htmlspecialchars($user_bio); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light px-4 py-3">
                    <button type="button" class="btn btn-sm btn-drive-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-drive-primary rounded-pill px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
