<?php
$base_path = '../';
$page_title = "Admin Settings";
require_once '../includes/settings_helper.php';

$success_msg = '';
$error_msg = '';

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_settings') {
    $farm_map_mode = isset($_POST['farm_map_view_mode']) && $_POST['farm_map_view_mode'] === 'homestead' ? 'homestead' : 'crops';
    $enable_switcher = isset($_POST['enable_homestead_switcher']) && $_POST['enable_homestead_switcher'] === '1';
    $lease_price = isset($_POST['default_lease_price']) ? floatval($_POST['default_lease_price']) : 110000.00;

    $updated = [
        'farm_map_view_mode' => $farm_map_mode,
        'enable_homestead_switcher' => $enable_switcher,
        'default_lease_price' => $lease_price
    ];

    if (save_system_settings($updated)) {
        $success_msg = 'System settings have been updated successfully!';
    } else {
        $error_msg = 'Failed to save settings. Please ensure includes/system_settings.json is writable.';
    }
}

$settings = get_system_settings();

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<style>
.settings-container {
    font-family: 'Outfit', sans-serif;
}

.settings-card {
    background: #FFFFFF;
    border: 1.5px solid #E5E7EB;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
}

.settings-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.mode-radio-card {
    border: 2px solid #E5E7EB;
    border-radius: 14px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #FFFFFF;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.mode-radio-card:hover {
    border-color: #10B981;
    background: #F0FDF4;
}

.mode-radio-card.active {
    border-color: #059669;
    background: #ECFDF5;
    box-shadow: 0 0 0 1px #059669;
}

.mode-radio-card input[type="radio"] {
    accent-color: #059669;
}

.switch-toggle-lg {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 26px;
}

.switch-toggle-lg input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider-round {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #D1D5DB;
    transition: .3s;
    border-radius: 34px;
}

.slider-round:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

input:checked + .slider-round {
    background-color: #059669;
}

input:checked + .slider-round:before {
    transform: translateX(22px);
}

.preview-map-box {
    border-radius: 14px;
    overflow: hidden;
    border: 1.5px solid #E5E7EB;
    position: relative;
    background: #F9FAFB;
}
</style>

<main class="workspace-surface settings-container">
    <!-- Toolbar/Title Bar -->
    <div class="toolbar border-bottom px-4 py-3 d-flex align-items-center justify-content-between">
        <div>
            <div class="d-flex align-items-center gap-2">
                <i data-lucide="settings" class="text-success" style="width: 22px; height: 22px;"></i>
                <h1 class="fs-5 fw-bold m-0 text-dark">System & Farm Settings</h1>
            </div>
            <p class="text-secondary small mb-0 mt-0.5">
                Configure farm map visualization modes, landowner display controls, and platform defaults.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="submit" form="systemSettingsForm" class="btn btn-success d-flex align-items-center gap-2 px-3 py-1.5 rounded-pill shadow-sm fw-medium">
                <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                <span>Save Changes</span>
            </button>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll p-4">
        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success d-flex align-items-center gap-2 rounded-3 border-0 shadow-sm mb-4" role="alert">
                <i data-lucide="check-circle" class="text-success" style="width: 20px; height: 20px;"></i>
                <div class="fw-medium text-dark"><?php echo htmlspecialchars($success_msg); ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 border-0 shadow-sm mb-4" role="alert">
                <i data-lucide="alert-circle" class="text-danger" style="width: 20px; height: 20px;"></i>
                <div class="fw-medium text-dark"><?php echo htmlspecialchars($error_msg); ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="settings.php" id="systemSettingsForm">
            <input type="hidden" name="action" value="save_settings">

            <div class="row g-4">
                <!-- Left Column: Core Farm Map View & Switcher Settings -->
                <div class="col-lg-7">
                    <!-- 1. Farm Map Display Mode Card -->
                    <div class="settings-card p-4 mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle p-2 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i data-lucide="map" style="width: 18px; height: 18px;"></i>
                                </div>
                                <div>
                                    <h2 class="h6 fw-bold text-dark mb-0">Farm Map View Mode</h2>
                                    <span class="text-muted small">Choose the primary visual layout shown when landowners open Farm & Plots</span>
                                </div>
                            </div>
                        </div>

                        <p class="text-secondary small mb-3">
                            Select which visualization style serves as the primary map layout for partitioned farm lands.
                        </p>

                        <div class="row g-3">
                            <!-- Option 1: Dynamic Crop Plots Bed Map (Recommended) -->
                            <div class="col-md-6">
                                <label class="mode-radio-card <?php echo ($settings['farm_map_view_mode'] === 'crops' ? 'active' : ''); ?>" id="labelModeCrops">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="radio" name="farm_map_view_mode" value="crops" id="radioModeCrops" <?php echo ($settings['farm_map_view_mode'] === 'crops' ? 'checked' : ''); ?> onchange="updateRadioSelection('crops')">
                                            <span class="fw-bold text-dark" style="font-size: 0.92rem;">Dynamic Crops Bed</span>
                                        </div>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill" style="font-size: 0.68rem;">Recommended</span>
                                    </div>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">
                                        Generates top-down soil partition beds showing exact crops planted and counts per plot. Focused directly on crops cultivation.
                                    </p>
                                </label>
                            </div>

                            <!-- Option 2: Homestead & Facilities Map -->
                            <div class="col-md-6">
                                <label class="mode-radio-card <?php echo ($settings['farm_map_view_mode'] === 'homestead' ? 'active' : ''); ?>" id="labelModeHomestead">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="radio" name="farm_map_view_mode" value="homestead" id="radioModeHomestead" <?php echo ($settings['farm_map_view_mode'] === 'homestead' ? 'checked' : ''); ?> onchange="updateRadioSelection('homestead')">
                                            <span class="fw-bold text-dark" style="font-size: 0.92rem;">Full Homestead Map</span>
                                        </div>
                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-0.5 rounded-pill" style="font-size: 0.68rem;">Artistic</span>
                                    </div>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">
                                        Illustrates full isometric farm ground including farmhouse buildings, pastures, reservoirs, and peripheral facilities.
                                    </p>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Landowner View Switcher Control Card -->
                    <div class="settings-card p-4 mb-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i data-lucide="toggle-right" style="width: 20px; height: 20px;"></i>
                                </div>
                                <div>
                                    <h2 class="h6 fw-bold text-dark mb-0">Landowner Homestead View Switcher</h2>
                                    <span class="text-muted small">Show or hide the "Homestead" mode switcher button on the Landowner Farm page</span>
                                </div>
                            </div>
                            <div>
                                <label class="switch-toggle-lg mb-0">
                                    <input type="checkbox" name="enable_homestead_switcher" value="1" id="switcherToggle" <?php echo (!empty($settings['enable_homestead_switcher']) ? 'checked' : ''); ?> onchange="updateSwitcherStatusText(this.checked)">
                                    <span class="slider-round"></span>
                                </label>
                            </div>
                        </div>

                        <hr class="my-3 opacity-25">

                        <div class="d-flex align-items-center justify-content-between">
                            <div class="small text-secondary" style="max-width: 480px;">
                                <strong>Default Status:</strong> When toggled <strong>OFF</strong>, landowners will only see their dynamic crop plots map. The View Homestead mode and View Switcher buttons remain completely hidden from their interface.
                            </div>
                            <span id="switcherStatusBadge" class="badge <?php echo (!empty($settings['enable_homestead_switcher']) ? 'bg-warning-subtle text-warning border border-warning' : 'bg-success-subtle text-success border border-success'); ?> px-3 py-1.5 rounded-pill" style="font-size: 0.78rem;">
                                <?php echo (!empty($settings['enable_homestead_switcher']) ? 'Switcher Visible to Landowners' : 'Hidden from Landowners (Default)'); ?>
                            </span>
                        </div>
                    </div>

                    <!-- 3. General Lease & Farm Pricing Defaults -->
                    <div class="settings-card p-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="rounded-circle p-2 bg-amber-subtle text-warning d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: #FEF3C7; color: #D97706;">
                                <i data-lucide="banknote" style="width: 18px; height: 18px;"></i>
                            </div>
                            <div>
                                <h2 class="h6 fw-bold text-dark mb-0">Default Lease & Rate Settings</h2>
                                <span class="text-muted small">Base values used when creating new partition plots</span>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Default Plot Lease Price / Year (₱)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-secondary border-end-0 fw-bold">₱</span>
                                    <input type="number" step="1000" name="default_lease_price" class="form-control" value="<?php echo htmlspecialchars($settings['default_lease_price']); ?>" required>
                                </div>
                                <span class="text-muted" style="font-size: 0.72rem;">Pre-fills the rental price per year when adding new plots.</span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Currency Code</label>
                                <input type="text" class="form-control bg-light" value="PHP (₱) - Philippine Peso" readonly>
                                <span class="text-muted" style="font-size: 0.72rem;">Fixed currency standard across Idle Land Gardening.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Visual Layout Preview & Info Box -->
                <div class="col-lg-5">
                    <div class="settings-card p-4 sticky-top" style="top: 80px;">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i data-lucide="eye" class="text-secondary" style="width: 18px; height: 18px;"></i>
                                <h3 class="h6 fw-bold text-dark mb-0">Live Layout Preview</h3>
                            </div>
                            <span class="badge bg-light text-secondary border px-2 py-0.5 rounded-pill" id="previewModeBadge">
                                <?php echo ($settings['farm_map_view_mode'] === 'homestead' ? 'Homestead Map' : 'Dynamic Crop Beds'); ?>
                            </span>
                        </div>

                        <!-- Preview Canvas Box -->
                        <div class="preview-map-box mb-3 p-2 text-center">
                            <!-- Dynamic Bed Preview Image -->
                            <div id="previewCropBeds" class="<?php echo ($settings['farm_map_view_mode'] === 'homestead' ? 'd-none' : ''); ?>">
                                <img src="../assets/images/farm_field_open.jpg" alt="Dynamic Crop Beds" class="img-fluid rounded-3 shadow-sm" style="max-height: 220px; object-fit: cover; width: 100%;">
                                <div class="p-2 mt-1">
                                    <div class="fw-bold text-dark small">Dynamic Soil Bed Grid</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Clean partitioned cultivation plots with crop icons directly overlaid.</div>
                                </div>
                            </div>

                            <!-- Homestead Map Preview Image -->
                            <div id="previewHomestead" class="<?php echo ($settings['farm_map_view_mode'] === 'homestead' ? '' : 'd-none'); ?>">
                                <img src="../assets/images/farm_plots_map.jpg" alt="Homestead View" class="img-fluid rounded-3 shadow-sm" style="max-height: 220px; object-fit: cover; width: 100%;">
                                <div class="p-2 mt-1">
                                    <div class="fw-bold text-dark small">Homestead Facilities Map</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Illustrates farmhouse, barn, pastures, and surrounding plots.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Info Explainer Box -->
                        <div class="p-3 rounded-3 border bg-light-subtle" style="font-size: 0.8rem;">
                            <div class="fw-semibold text-dark mb-1 d-flex align-items-center gap-1.5">
                                <i data-lucide="info" class="text-primary" style="width: 15px; height: 15px;"></i>
                                <span>Configuration Summary</span>
                            </div>
                            <ul class="mb-0 ps-3 text-secondary space-y-1">
                                <li>The <strong>Dynamic Crops Bed</strong> layout automatically scales to 1, 2, 4, or more plots depending on the farm selected.</li>
                                <li>The <strong>Homestead View & Switcher</strong> are hidden from landowners by default to ensure an uncluttered, focused experience.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
function updateRadioSelection(mode) {
    const cardCrops = document.getElementById('labelModeCrops');
    const cardHomestead = document.getElementById('labelModeHomestead');
    const previewCropBeds = document.getElementById('previewCropBeds');
    const previewHomestead = document.getElementById('previewHomestead');
    const previewBadge = document.getElementById('previewModeBadge');

    if (mode === 'crops') {
        cardCrops.classList.add('active');
        cardHomestead.classList.remove('active');
        previewCropBeds.classList.remove('d-none');
        previewHomestead.classList.add('d-none');
        previewBadge.textContent = 'Dynamic Crop Beds';
    } else {
        cardHomestead.classList.add('active');
        cardCrops.classList.remove('active');
        previewHomestead.classList.remove('d-none');
        previewCropBeds.classList.add('d-none');
        previewBadge.textContent = 'Homestead Map';
    }
}

function updateSwitcherStatusText(isChecked) {
    const badge = document.getElementById('switcherStatusBadge');
    if (isChecked) {
        badge.className = 'badge bg-warning-subtle text-warning border border-warning px-3 py-1.5 rounded-pill';
        badge.textContent = 'Switcher Visible to Landowners';
    } else {
        badge.className = 'badge bg-success-subtle text-success border border-success px-3 py-1.5 rounded-pill';
        badge.textContent = 'Hidden from Landowners (Default)';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (window.lucide) {
        lucide.createIcons();
    }
});
</script>

<?php include '../includes/footer.php'; ?>
