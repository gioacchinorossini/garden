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
require_once __DIR__ . '/../includes/db.php';

$gardener_id = $_SESSION['user_id'] ?? 3;
$userRow = null;
try {
    $uStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $uStmt->execute([$gardener_id]);
    $userRow = $uStmt->fetch();
} catch (\Exception $e) {}

if ($userRow && !isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = $userRow['name'];
} elseif (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = 'Gardener';
}

$user_email = $userRow['email'] ?? ($_SESSION['user_email'] ?? '');
$user_phone = $userRow['phone'] ?? ($_SESSION['user_phone'] ?? '');
$user_address = $_SESSION['user_address'] ?? '';
$user_bio = $_SESSION['user_bio'] ?? '';
$member_since = !empty($userRow['created_at']) ? 'Active Gardener since ' . date('M Y', strtotime($userRow['created_at'])) : 'Active Gardener';

// Process profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $newName = htmlspecialchars(trim($_POST['user_name']));
    $newEmail = htmlspecialchars(trim($_POST['user_email']));
    $newPhone = htmlspecialchars(trim($_POST['user_phone']));
    $_SESSION['user_name'] = $newName;
    $_SESSION['user_email'] = $newEmail;
    $_SESSION['user_phone'] = $newPhone;
    $_SESSION['user_address'] = htmlspecialchars(trim($_POST['user_address'] ?? ''));
    $_SESSION['user_bio'] = htmlspecialchars(trim($_POST['user_bio'] ?? ''));
    $user_email = $newEmail;
    $user_phone = $newPhone;
    $user_address = $_SESSION['user_address'];
    $user_bio = $_SESSION['user_bio'];

    try {
        $upStmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
        $upStmt->execute([$newName, $newEmail, $newPhone, $gardener_id]);
    } catch (\Exception $e) {}
    $success_msg = "Profile updated successfully!";
}

// Calculate completed tasks from schedule
$sched_list = $_SESSION['mock_schedules'] ?? [];
$completed_scheds = array_filter($sched_list, fn($s) => ($s['status'] ?? '') === 'completed');
$completed_tasks_count = count($completed_scheds);

// Query assigned plots for this gardener
$assigned_plots = [];
try {
    $pStmt = $pdo->prepare("
        SELECT p.*, l.title as land_title, l.address as land_address
        FROM plots p
        JOIN lands l ON p.land_id = l.id
        WHERE p.gardener_id = ? OR (p.farmer_name = ? AND p.status = 'occupied')
        ORDER BY p.id ASC
    ");
    $pStmt->execute([$gardener_id, $_SESSION['user_name']]);
    $assigned_plots = $pStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\Exception $e) {
    $assigned_plots = [];
}

// Query total harvests for this gardener
$total_harvest_kg = 0;
try {
    $hStmt = $pdo->prepare("SELECT COALESCE(SUM(quantity_kg), 0) FROM harvests WHERE gardener_id = ?");
    $hStmt->execute([$gardener_id]);
    $total_harvest_kg = (float)$hStmt->fetchColumn();
} catch (\Exception $e) {
    $total_harvest_kg = 0;
}
?>

<main class="workspace-surface">
    <!-- Toolbar (Title and actions) -->


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
        <!-- Landowner Profile Card -->
        <h2 class="fs-6 fw-semibold text-secondary mb-3 mt-3" style="letter-spacing: 0.5px; text-transform: uppercase;">
            Profile Information
        </h2>
        <div class="card border rounded-4 overflow-hidden mb-4 bg-white shadow-xs"
            style="border-color: var(--drive-border) !important;">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start gap-4">
                    <!-- Avatar -->
                    <div class="position-relative">
                        <div
                            class="w-24 h-24 rounded-full bg-emerald-600 text-white d-flex align-items-center justify-content-center fw-bold fs-2 shadow-sm border-4 border-white">
                            <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                        </div>
                        <span
                            class="position-absolute bottom-0 end-0 bg-emerald-500 text-white rounded-full p-1 border-2 border-white"
                            title="Verified Gardener">
                            <i data-lucide="sprout" style="width: 14px; height: 14px;"></i>
                        </span>
                    </div>

                    <!-- Profile Info -->
                    <div class="flex-grow-1 text-center text-md-start">
                        <div
                            class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-2 mb-2">
                            <div>
                                <div
                                    class="d-flex align-items-center justify-content-center justify-content-md-start gap-2">
                                    <h2 class="fs-4 fw-bold text-dark mb-0">
                                        <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                                    </h2>
                                    <span
                                        class="badge rounded-pill bg-emerald-100 text-emerald-800 text-xs font-semibold px-2.5 py-1">Gardener</span>
                                </div>
                                <p
                                    class="text-secondary text-xs mb-0 mt-1 d-flex align-items-center justify-content-center justify-content-md-start gap-1">
                                    <i data-lucide="map-pin" style="width: 14px; height: 14px;"></i>
                                    <?php echo htmlspecialchars($user_address); ?>
                                </p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button
                                    class="btn btn-drive-secondary btn-sm px-3 d-flex align-items-center gap-2 rounded-pill"
                                    data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                    <i data-lucide="edit-3" style="width: 16px; height: 16px;"></i>
                                    <span>Edit Profile</span>
                                </button>
                            </div>
                        </div>

                        <p class="text-dark text-xs mb-3 max-w-2xl"><?php echo htmlspecialchars($user_bio); ?></p>

                        <!-- Quick Details -->
                        <div
                            class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-2 pt-3 border-top text-xs text-secondary">
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
                                <span><?php echo htmlspecialchars($member_since); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="bg-light border-top p-3 px-4">
                <div class="row text-center g-3">
                    <div class="col-4 border-end">
                        <span class="text-secondary text-[11px] font-semibold uppercase tracking-wider d-block">Active
                            Plots</span>
                        <span class="fs-5 fw-bold text-dark"><?php echo count($assigned_plots); ?></span>
                    </div>
                    <div class="col-4 border-end">
                        <span class="text-secondary text-[11px] font-semibold uppercase tracking-wider d-block">Tasks
                            Completed</span>
                        <span class="fs-5 fw-bold text-emerald-600"><?php echo $completed_tasks_count; ?></span>
                    </div>
                    <div class="col-4">
                        <span class="text-secondary text-[11px] font-semibold uppercase tracking-wider d-block">Total
                            Harvests</span>
                        <span class="fs-5 fw-bold text-primary"><?php echo number_format($total_harvest_kg, 1); ?> <span
                                class="fs-6 font-normal text-muted">kg</span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Garden Stewardship & Environmental Contribution Section -->
        <h2 class="fs-6 fw-semibold text-secondary mb-3" style="letter-spacing: 0.5px; text-transform: uppercase;">
            Environmental Impact
        </h2>

        <?php
        include '../includes/eco_tracker.php';
        ?>

        <!-- Assigned Plots & Cultivation -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="fs-6 fw-semibold text-secondary m-0" style="letter-spacing: 0.5px; text-transform: uppercase;">
                Assigned Plots & Crops
            </h2>
            <a href="schedules.php"
                class="text-decoration-none text-primary d-inline-flex align-items-center gap-1 text-xs fw-semibold">
                View My Tasks & Schedules <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
            </a>
        </div>

        <div class="row g-3 mb-4">
            <?php if (empty($assigned_plots)): ?>
                <div class="col-12">
                    <div class="p-4 border rounded-4 text-center bg-white text-muted">
                        <i data-lucide="sprout" class="mx-auto mb-2 text-secondary" style="width: 28px; height: 28px;"></i>
                        <p class="mb-1 text-dark fw-semibold text-xs">No assigned plots yet</p>
                        <span class="text-xs text-secondary d-block mb-3">Browse available community garden plots and submit an application to start cultivating.</span>
                        <a href="dashboard.php" class="btn btn-sm btn-drive-primary text-xs">Browse Available Lands</a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($assigned_plots as $plot): ?>
                    <div class="col-md-6">
                        <div class="drive-card h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge rounded-pill bg-emerald-100 text-emerald-800 text-[10px] font-semibold">
                                        Plot <?php echo htmlspecialchars($plot['plot_number']); ?> • Leased
                                    </span>
                                    <span class="text-xs text-secondary font-semibold">
                                        <i data-lucide="sprout" class="text-emerald-500 inline me-1" style="width: 12px; height: 12px;"></i>
                                        <?php echo htmlspecialchars($plot['crop'] ?? 'Crops'); ?>
                                    </span>
                                </div>
                                <h3 class="fs-6 fw-bold text-dark mb-1"><?php echo htmlspecialchars($plot['land_title']); ?></h3>
                                <p class="text-secondary text-xs mb-3">
                                    <i data-lucide="map-pin" class="inline me-1" style="width: 12px; height: 12px;"></i>
                                    <?php echo htmlspecialchars($plot['land_address'] ?? 'Urban Community Garden'); ?>
                                </p>
                                <div class="bg-light p-2.5 rounded-3 border mb-3 text-xs">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted">Plot Size:</span>
                                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($plot['area_sqm'] ?? 25); ?> m²</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted">Status:</span>
                                        <span class="fw-semibold text-emerald-700"><?php echo ucfirst(htmlspecialchars($plot['status'])); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Current Crop:</span>
                                        <span class="fw-semibold text-primary"><?php echo htmlspecialchars($plot['crop'] ?? 'Vegetables'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-2 border-top d-flex align-items-center justify-content-between">
                                <a href="schedules.php" class="btn btn-sm btn-drive-primary text-xs">Manage Tasks</a>
                                <a href="harvests.php" class="btn btn-sm btn-drive-secondary text-xs">Record Harvest</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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
                        <label for="userName" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">FULL NAME</label>
                        <input type="text" class="form-control drive-form-control w-100" id="userName" name="user_name"
                            value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="userEmail" class="form-label text-secondary"
                                style="font-size: 0.75rem; font-weight:600;">EMAIL ADDRESS</label>
                            <input type="email" class="form-control drive-form-control w-100" id="userEmail"
                                name="user_email" value="<?php echo htmlspecialchars($user_email); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="userPhone" class="form-label text-secondary"
                                style="font-size: 0.75rem; font-weight:600;">PHONE NUMBER</label>
                            <input type="tel" class="form-control drive-form-control w-100" id="userPhone"
                                name="user_phone" value="<?php echo htmlspecialchars($user_phone); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="userAddress" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">GARDEN LOCATION / ADDRESS</label>
                        <input type="text" class="form-control drive-form-control w-100" id="userAddress"
                            name="user_address" value="<?php echo htmlspecialchars($user_address); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="userBio" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">BIO & EXPERIENCE</label>
                        <textarea class="form-control drive-form-control w-100" id="userBio" name="user_bio"
                            rows="3"><?php echo htmlspecialchars($user_bio); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-drive-secondary btn-sm rounded-pill px-3"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-drive-primary btn-sm rounded-pill px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>