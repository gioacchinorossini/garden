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
require_once __DIR__ . '/../includes/db.php';

$owner_id = $_SESSION['user_id'] ?? 2;
$userRow = null;
try {
    $uStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $uStmt->execute([$owner_id]);
    $userRow = $uStmt->fetch();
} catch (\Exception $e) {}

if ($userRow && !isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = $userRow['name'];
} elseif (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = 'Landowner';
}

$user_email = $userRow['email'] ?? ($_SESSION['user_email'] ?? '');
$user_phone = $userRow['phone'] ?? ($_SESSION['user_phone'] ?? '');
$user_address = $_SESSION['user_address'] ?? '';
$user_bio = $_SESSION['user_bio'] ?? '';
$member_since = !empty($userRow['created_at']) ? 'Landowner since ' . date('M Y', strtotime($userRow['created_at'])) : 'Active Landowner';

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
        $upStmt->execute([$newName, $newEmail, $newPhone, $owner_id]);
    } catch (\Exception $e) {}
    $success_msg = "Profile updated successfully!";
}

// Fetch landowner's registered properties and plot metrics
$owner_lands = [];
$total_land_area = 0;
$total_plots = 0;
$leased_plots = 0;
try {
    $stmt = $pdo->prepare("SELECT * FROM lands WHERE owner_id = ? ORDER BY id DESC");
    $stmt->execute([$owner_id]);
    $owner_lands = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_land_area = array_sum(array_column($owner_lands, 'area_sqm'));

    if (!empty($owner_lands)) {
        $land_ids = array_column($owner_lands, 'id');
        $placeholders = implode(',', array_fill(0, count($land_ids), '?'));
        $pStmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as leased FROM plots WHERE land_id IN ($placeholders)");
        $pStmt->execute($land_ids);
        $plotRow = $pStmt->fetch();
        $total_plots = (int)($plotRow['total'] ?? 0);
        $leased_plots = (int)($plotRow['leased'] ?? 0);
    }
} catch (\Exception $e) {
    $owner_lands = [];
}
?>

<main class="workspace-surface">
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
                            class="w-24 h-24 rounded-full bg-drive-primary text-white d-flex align-items-center justify-content-center fw-bold fs-2 shadow-sm border-4 border-white">
                            <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                        </div>
                        <span
                            class="position-absolute bottom-0 end-0 bg-emerald-500 text-white rounded-full p-1 border-2 border-white"
                            title="Landowner">
                            <i data-lucide="check" style="width: 14px; height: 14px;"></i>
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
                                        class="badge rounded-pill bg-emerald-100 text-emerald-800 text-xs font-semibold px-2.5 py-1">Landowner</span>
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
                        <span
                            class="text-secondary text-[11px] font-semibold uppercase tracking-wider d-block">Registered
                            Properties</span>
                        <span class="fs-5 fw-bold text-dark"><?php echo count($owner_lands); ?></span>
                    </div>
                    <div class="col-4 border-end">
                        <span class="text-secondary text-[11px] font-semibold uppercase tracking-wider d-block">Total
                            Land Area</span>
                        <span class="fs-5 fw-bold text-primary"><?php echo number_format($total_land_area, 1); ?> <span
                                class="fs-6 font-normal text-muted">m²</span></span>
                    </div>
                    <div class="col-4">
                        <span class="text-secondary text-[11px] font-semibold uppercase tracking-wider d-block">Active
                            Plots Leased</span>
                        <span class="fs-5 fw-bold text-emerald-600"><?php echo $leased_plots; ?> <span class="fs-6 font-normal text-muted">/
                                <?php echo $total_plots; ?></span></span>
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

        <!-- Registered Properties & Eco Scores -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="fs-6 fw-semibold text-secondary m-0" style="letter-spacing: 0.5px; text-transform: uppercase;">
                Registered Land Portfolio
            </h2>
            <a href="lands.php"
                class="text-decoration-none text-primary d-inline-flex align-items-center gap-1 text-xs fw-semibold">
                Manage All Lands <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
            </a>
        </div>

        <div class="row g-3 mb-4">
            <?php if (empty($owner_lands)): ?>
                <div class="col-12">
                    <div class="p-4 border rounded-4 text-center bg-white text-muted">
                        <i data-lucide="map-pin" class="mx-auto mb-2 text-secondary" style="width: 28px; height: 28px;"></i>
                        <p class="mb-1 text-dark fw-semibold text-xs">No registered properties yet</p>
                        <span class="text-xs text-secondary d-block mb-3">Add and manage your urban land lots to start hosting community gardens.</span>
                        <a href="register.php" class="btn btn-sm btn-drive-primary text-xs">Register Land</a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach (array_slice($owner_lands, 0, 6) as $land): ?>
                    <?php
                    $crops = json_decode($land['allowed_seeds'] ?? '[]', true);
                    $cropStr = is_array($crops) && !empty($crops) ? implode(', ', array_slice($crops, 0, 3)) : 'Vegetables';
                    $isApproved = ($land['status'] === 'approved');
                    ?>
                    <div class="col-md-4">
                        <div class="drive-card h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge rounded-pill <?php echo $isApproved ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'; ?> text-[10px] font-semibold">
                                        <?php echo ucfirst($land['status']); ?>
                                    </span>
                                    <span class="text-xs text-secondary font-semibold">
                                        <i data-lucide="grid" class="text-emerald-500 inline me-1" style="width: 12px; height: 12px;"></i>
                                        <?php echo intval($land['plot_count'] ?? 4); ?> plots
                                    </span>
                                </div>
                                <h3 class="fs-6 fw-bold text-dark mb-1 text-truncate"><?php echo htmlspecialchars($land['title']); ?></h3>
                                <p class="text-secondary text-xs mb-3 text-truncate">
                                    <i data-lucide="map-pin" class="inline me-1" style="width: 12px; height: 12px;"></i>
                                    <?php echo htmlspecialchars($land['address']); ?>
                                </p>
                                <div class="bg-light p-2.5 rounded-3 border mb-3 text-xs">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted">Land Area:</span>
                                        <span class="fw-semibold text-dark"><?php echo number_format($land['area_sqm'] ?? 0); ?> m²</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Permitted Crops:</span>
                                        <span class="fw-semibold text-dark text-truncate ms-2"><?php echo htmlspecialchars($cropStr); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-2 border-top d-flex align-items-center justify-content-between">
                                <span class="text-xs text-muted">Status: <?php echo ucfirst($land['status']); ?></span>
                                <a href="lands.php" class="btn btn-sm btn-drive-secondary text-xs">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>  </div>
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
                        <textarea name="user_bio" class="form-control drive-form-control text-xs"
                            rows="3"><?php echo htmlspecialchars($user_bio); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light px-4 py-3">
                    <button type="button" class="btn btn-sm btn-drive-secondary rounded-pill px-3"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-drive-primary rounded-pill px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>