<?php
$base_path = '../';
$page_title = "Street Vendor Profile";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
require_once '../includes/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['active_role'] = 'gardener';
if (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = 'Kusina Street Bites';
}

$user_id = $_SESSION['user_id'] ?? 3;
$user_name = $_SESSION['user_name'] ?? 'Kusina Street Bites';
$user_email = $_SESSION['user_email'] ?? 'vendor@streetbites.com';
$user_phone = $_SESSION['user_phone'] ?? '+63 917 555 4321';
$user_address = $_SESSION['user_address'] ?? 'Stall 4, Roxas Boulevard Night Market, Manila';
$user_bio = $_SESSION['user_bio'] ?? 'Serving authentic charcoal-grilled street food, savory isaw skewers, pork BBQ, and fresh calamansi coolers since 2021.';

// Vendor profile details
$business_name = $_SESSION['business_name'] ?? 'Kusina Street Bites';
$cuisine_type = $_SESSION['cuisine_type'] ?? 'Grilled Street Food, Skewers & Coolers';
$permit_number = $_SESSION['permit_number'] ?? 'SAN-2025-08492';
$permit_verified = $_SESSION['permit_verified'] ?? 1;
$operating_hours = $_SESSION['operating_hours'] ?? '4:00 PM - 11:30 PM (Daily)';

// Check database for vendor_profile
if (isset($conn) && $conn) {
    $vstmt = $conn->prepare("SELECT * FROM vendor_profiles WHERE user_id = ? LIMIT 1");
    if ($vstmt) {
        $vstmt->bind_param("i", $user_id);
        $vstmt->execute();
        $vres = $vstmt->get_result();
        if ($vrow = $vres->fetch_assoc()) {
            $business_name = $vrow['business_name'] ?: $business_name;
            $cuisine_type = $vrow['cuisine_type'] ?: $cuisine_type;
            $permit_number = $vrow['permit_number'] ?: $permit_number;
            $permit_verified = $vrow['permit_verified'];
            $operating_hours = $vrow['operating_hours'] ?: $operating_hours;
            $user_bio = $vrow['bio'] ?: $user_bio;
        }
    }
}

// Process profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $business_name = htmlspecialchars(trim($_POST['business_name'] ?? $user_name));
    $user_name = htmlspecialchars(trim($_POST['user_name']));
    $user_email = htmlspecialchars(trim($_POST['user_email']));
    $user_phone = htmlspecialchars(trim($_POST['user_phone']));
    $user_address = htmlspecialchars(trim($_POST['user_address']));
    $cuisine_type = htmlspecialchars(trim($_POST['cuisine_type']));
    $permit_number = htmlspecialchars(trim($_POST['permit_number']));
    $operating_hours = htmlspecialchars(trim($_POST['operating_hours']));
    $user_bio = htmlspecialchars(trim($_POST['user_bio']));

    $_SESSION['user_name'] = $user_name;
    $_SESSION['user_email'] = $user_email;
    $_SESSION['user_phone'] = $user_phone;
    $_SESSION['user_address'] = $user_address;
    $_SESSION['business_name'] = $business_name;
    $_SESSION['cuisine_type'] = $cuisine_type;
    $_SESSION['permit_number'] = $permit_number;
    $_SESSION['operating_hours'] = $operating_hours;
    $_SESSION['user_bio'] = $user_bio;

    if (isset($conn) && $conn) {
        $upstmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
        if ($upstmt) {
            $upstmt->bind_param("ssssi", $user_name, $user_email, $user_phone, $user_address, $user_id);
            $upstmt->execute();
        }

        // Upsert into vendor_profiles
        $vup = $conn->prepare("INSERT INTO vendor_profiles (user_id, business_name, cuisine_type, permit_number, operating_hours, bio) 
                              VALUES (?, ?, ?, ?, ?, ?) 
                              ON DUPLICATE KEY UPDATE business_name = VALUES(business_name), cuisine_type = VALUES(cuisine_type), 
                              permit_number = VALUES(permit_number), operating_hours = VALUES(operating_hours), bio = VALUES(bio)");
        if ($vup) {
            $vup->bind_param("isssss", $user_id, $business_name, $cuisine_type, $permit_number, $operating_hours, $user_bio);
            $vup->execute();
        }
    }
    $success_msg = "Vendor profile updated successfully!";
}

// Menu items count
$menu_count = 0;
if (isset($conn) && $conn) {
    $mstmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM menu_items WHERE vendor_id = ?");
    if ($mstmt) {
        $mstmt->bind_param("i", $user_id);
        $mstmt->execute();
        $mres = $mstmt->get_result();
        if ($mrow = $mres->fetch_assoc()) {
            $menu_count = $mrow['cnt'];
        }
    }
}
if ($menu_count === 0 && isset($_SESSION['vendor_menu_items'])) {
    $menu_count = count($_SESSION['vendor_menu_items']);
}
if ($menu_count === 0) {
    $menu_count = 6;
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

        <!-- Vendor Header Banner -->
        <h2 class="fs-6 fw-semibold text-secondary mb-3 mt-3" style="letter-spacing: 0.5px; text-transform: uppercase;">
            Vendor Profile & Business Information
        </h2>

        <div class="card border rounded-4 overflow-hidden mb-4 bg-white shadow-xs"
            style="border-color: var(--drive-border) !important;">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start gap-4">
                    <!-- Avatar with Food/Vendor Badge -->
                    <div class="position-relative">
                        <div
                            class="w-24 h-24 rounded-full bg-amber-500 text-white d-flex align-items-center justify-content-center fw-bold fs-2 shadow-sm border-4 border-white">
                            <i data-lucide="utensils" style="width: 38px; height: 38px;"></i>
                        </div>
                        <span
                            class="position-absolute bottom-0 end-0 bg-emerald-500 text-white rounded-full p-1 border-2 border-white"
                            title="Verified Food Vendor">
                            <i data-lucide="check" style="width: 14px; height: 14px;"></i>
                        </span>
                    </div>

                    <!-- Vendor Main Details -->
                    <div class="flex-grow-1 text-center text-md-start">
                        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-2 mb-2">
                            <div>
                                <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2 flex-wrap">
                                    <h2 class="fs-4 fw-bold text-dark mb-0">
                                        <?php echo htmlspecialchars($business_name); ?>
                                    </h2>
                                    <span class="badge rounded-pill bg-amber-100 text-amber-800 text-xs font-semibold px-2.5 py-1">
                                        Street Food Vendor
                                    </span>
                                    <?php if ($permit_verified): ?>
                                        <span class="badge rounded-pill bg-emerald-100 text-emerald-800 text-xs font-semibold px-2.5 py-1 d-inline-flex align-items-center gap-1">
                                            <i data-lucide="shield-check" style="width: 12px; height: 12px;"></i> Sanitary Permit Verified
                                        </span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis text-xs font-semibold px-2.5 py-1">
                                            Permit Pending Review
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-secondary text-xs mb-0 mt-1 d-flex align-items-center justify-content-center justify-content-md-start gap-1">
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
                                <a href="harvests.php" class="btn btn-drive-primary btn-sm px-3 d-flex align-items-center gap-2 rounded-pill">
                                    <i data-lucide="book-open" style="width: 16px; height: 16px;"></i>
                                    <span>Manage Menu</span>
                                </a>
                            </div>
                        </div>

                        <p class="text-dark text-xs mb-3 max-w-2xl"><?php echo htmlspecialchars($user_bio); ?></p>

                        <!-- Quick Details Grid -->
                        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-3 pt-3 border-top text-xs text-secondary">
                            <div class="d-flex align-items-center gap-1.5">
                                <i data-lucide="chef-hat" style="width: 14px; height: 14px;" class="text-amber-600"></i>
                                <span><strong>Cuisine:</strong> <?php echo htmlspecialchars($cuisine_type); ?></span>
                            </div>
                            <div class="d-flex align-items-center gap-1.5">
                                <i data-lucide="clock" style="width: 14px; height: 14px;" class="text-primary"></i>
                                <span><strong>Hours:</strong> <?php echo htmlspecialchars($operating_hours); ?></span>
                            </div>
                            <div class="d-flex align-items-center gap-1.5">
                                <i data-lucide="file-text" style="width: 14px; height: 14px;" class="text-success"></i>
                                <span><strong>Permit #:</strong> <?php echo htmlspecialchars($permit_number); ?></span>
                            </div>
                            <div class="d-flex align-items-center gap-1.5">
                                <i data-lucide="mail" style="width: 14px; height: 14px;" class="text-secondary"></i>
                                <span><?php echo htmlspecialchars($user_email); ?></span>
                            </div>
                            <div class="d-flex align-items-center gap-1.5">
                                <i data-lucide="phone" style="width: 14px; height: 14px;" class="text-secondary"></i>
                                <span><?php echo htmlspecialchars($user_phone); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="bg-light border-top p-3 px-4">
                <div class="row text-center g-3">
                    <div class="col-3 border-end">
                        <span class="text-secondary text-[11px] font-semibold uppercase tracking-wider d-block">Active Stalls</span>
                        <span class="fs-5 fw-bold text-dark">1</span>
                    </div>
                    <div class="col-3 border-end">
                        <span class="text-secondary text-[11px] font-semibold uppercase tracking-wider d-block">Menu Items</span>
                        <span class="fs-5 fw-bold text-amber-600"><?php echo $menu_count; ?></span>
                    </div>
                    <div class="col-3 border-end">
                        <span class="text-secondary text-[11px] font-semibold uppercase tracking-wider d-block">Customer Rating</span>
                        <span class="fs-5 fw-bold text-success">4.9 ★ <span class="fs-6 font-normal text-muted">(38 reviews)</span></span>
                    </div>
                    <div class="col-3">
                        <span class="text-secondary text-[11px] font-semibold uppercase tracking-wider d-block">Monthly Sales</span>
                        <span class="fs-5 fw-bold text-primary">₱32,450</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Stall Leases & Property Locations -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="fs-6 fw-semibold text-secondary m-0" style="letter-spacing: 0.5px; text-transform: uppercase;">
                Current Rented Stalls & Locations
            </h2>
            <a href="browse.php"
                class="text-decoration-none text-primary d-inline-flex align-items-center gap-1 text-xs fw-semibold">
                Find More Stalls <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
            </a>
        </div>

        <div class="row g-3 mb-4">
            <!-- Stall 1 -->
            <div class="col-md-6">
                <div class="drive-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge rounded-pill bg-emerald-100 text-emerald-800 text-[10px] font-semibold">
                                Stall A-1 • Active Rental
                            </span>
                            <span class="text-xs text-secondary font-semibold">
                                <i data-lucide="store" class="text-amber-500 inline me-1" style="width: 12px; height: 12px;"></i>
                                Night Food Market
                            </span>
                        </div>
                        <h3 class="fs-6 fw-bold text-dark mb-1">Roxas Boulevard Food Park</h3>
                        <p class="text-secondary text-xs mb-3">
                            <i data-lucide="map-pin" class="inline me-1" style="width: 12px; height: 12px;"></i>
                            Roxas Blvd cor. Padre Faura, Ermita, Manila
                        </p>
                        <div class="bg-light p-2.5 rounded-3 border mb-3 text-xs">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Stall Dimensions:</span>
                                <span class="fw-semibold text-dark">3m x 3m (9 m²)</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Monthly Rent:</span>
                                <span class="fw-semibold text-emerald-700">₱4,500 / month</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Amenities:</span>
                                <span class="fw-semibold text-primary">Water line, 220V Outlets, Grease Trap</span>
                            </div>
                        </div>
                    </div>
                    <div class="pt-2 border-top d-flex align-items-center justify-content-between">
                        <span class="text-xs text-muted">Property Owner: Juan Santos</span>
                        <a href="schedules.php" class="btn btn-sm btn-drive-primary text-xs">Operating Hours</a>
                    </div>
                </div>
            </div>

            <!-- Stall 2 (Upcoming application / reserved) -->
            <div class="col-md-6">
                <div class="drive-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge rounded-pill bg-blue-100 text-blue-800 text-[10px] font-semibold">
                                Stall C-3 • Application Approved
                            </span>
                            <span class="text-xs text-secondary font-semibold">
                                <i data-lucide="clock" class="text-primary inline me-1" style="width: 12px; height: 12px;"></i>
                                Starts Nov 1
                            </span>
                        </div>
                        <h3 class="fs-6 fw-bold text-dark mb-1">Quezon City Weekend Bites Bazaar</h3>
                        <p class="text-secondary text-xs mb-3">
                            <i data-lucide="map-pin" class="inline me-1" style="width: 12px; height: 12px;"></i>
                            Elliptical Road, Diliman, Quezon City
                        </p>
                        <div class="bg-light p-2.5 rounded-3 border mb-3 text-xs">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Stall Dimensions:</span>
                                <span class="fw-semibold text-dark">4m x 3m (12 m²)</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Monthly Rent:</span>
                                <span class="fw-semibold text-emerald-700">₱5,200 / month</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Amenities:</span>
                                <span class="fw-semibold text-primary">Outdoor Seating, Night Lights, Restroom</span>
                            </div>
                        </div>
                    </div>
                    <div class="pt-2 border-top d-flex align-items-center justify-content-between">
                        <span class="text-xs text-muted">Property Owner: Maria Dela Cruz</span>
                        <a href="requests.php" class="btn btn-sm btn-drive-secondary text-xs">View Agreement</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Edit Vendor Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content drive-modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold text-dark" id="editProfileModalLabel">Edit Street Vendor Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="profile.php" method="POST">
                <input type="hidden" name="action" value="update_profile">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="businessName" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">STALL / BUSINESS BRAND NAME</label>
                        <input type="text" class="form-control drive-form-control w-100" id="businessName" name="business_name"
                            value="<?php echo htmlspecialchars($business_name); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="userName" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">VENDOR OWNER / REPRESENTATIVE NAME</label>
                        <input type="text" class="form-control drive-form-control w-100" id="userName" name="user_name"
                            value="<?php echo htmlspecialchars($user_name); ?>" required>
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

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="cuisineType" class="form-label text-secondary"
                                style="font-size: 0.75rem; font-weight:600;">CUISINE SPECIALTY</label>
                            <input type="text" class="form-control drive-form-control w-100" id="cuisineType"
                                name="cuisine_type" value="<?php echo htmlspecialchars($cuisine_type); ?>" placeholder="e.g. Pork BBQ, Isaw, Takoyaki" required>
                        </div>
                        <div class="col-md-6">
                            <label for="permitNumber" class="form-label text-secondary"
                                style="font-size: 0.75rem; font-weight:600;">SANITARY PERMIT NO.</label>
                            <input type="text" class="form-control drive-form-control w-100" id="permitNumber"
                                name="permit_number" value="<?php echo htmlspecialchars($permit_number); ?>" placeholder="e.g. SAN-2025-XXXXX">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="operatingHours" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">OPERATING HOURS</label>
                        <input type="text" class="form-control drive-form-control w-100" id="operatingHours"
                            name="operating_hours" value="<?php echo htmlspecialchars($operating_hours); ?>" placeholder="e.g. 4:00 PM - 11:30 PM (Daily)">
                    </div>

                    <div class="mb-3">
                        <label for="userAddress" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">PRIMARY BUSINESS / STALL ADDRESS</label>
                        <input type="text" class="form-control drive-form-control w-100" id="userAddress"
                            name="user_address" value="<?php echo htmlspecialchars($user_address); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="userBio" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">STREET FOOD BIO & STORY</label>
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