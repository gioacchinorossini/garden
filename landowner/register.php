<?php
$base_path = '../';
$page_title = "Register Land";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

// Prepare mock register handler
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$success_message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $title = htmlspecialchars(trim($_POST['title']));
    $address = htmlspecialchars(trim($_POST['address']));
    $lat = floatval($_POST['latitude']);
    $lng = floatval($_POST['longitude']);
    $area = floatval($_POST['area']);
    $desc = htmlspecialchars(trim($_POST['description']));

    $seeds = isset($_POST['allowed_seeds']) ? array_map('htmlspecialchars', $_POST['allowed_seeds']) : [];

    // Store in mock session lands
    if (!isset($_SESSION['mock_lands'])) {
        $_SESSION['mock_lands'] = [];
    }
    $new_id = count($_SESSION['mock_lands']) + 1;
    $_SESSION['mock_lands'][] = [
        'id' => $new_id,
        'title' => $title,
        'landowner' => $_SESSION['user_name'] ?? 'John Landowner',
        'address' => $address,
        'latitude' => $lat,
        'longitude' => $lng,
        'area' => $area,
        'status' => 'pending',
        'reason' => '',
        'description' => $desc,
        'allowed_seeds' => $seeds
    ];
    $success_message = "Land registered successfully! It is now pending Administrator review.";
}
?>

<main class="workspace-surface">
    <!-- Toolbar/Title Bar -->
    <div class="toolbar border-bottom">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">Register Land</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="lands.php" class="btn btn-outline-secondary rounded-pill btn-sm px-3">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll">
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success d-flex align-items-center gap-2 border-0  mb-4" role="alert"
                style="border-radius: 12px; background-color: #d1e7dd; color: #0f5132;">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div><?php echo $success_message; ?></div>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Left: Form -->
            <div class="col-md-7">
                <form action="register.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="register">

                    <!-- Land Title -->
                    <div class="mb-3">
                        <label for="title" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">PROPERTY NAME</label>
                        <input type="text" class="form-control drive-form-control w-100" id="title" name="title"
                            required placeholder="Sunnyvale Empty Lot">
                    </div>

                    <!-- Address -->
                    <div class="mb-3">
                        <label for="address" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">ADDRESS</label>
                        <input type="text" class="form-control drive-form-control w-100" id="address" name="address"
                            required placeholder="124 Green Ave, Sunnyvale">
                    </div>

                    <!-- Size & Coordinates -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="area" class="form-label text-secondary"
                                style="font-size: 0.75rem; font-weight:600;">AREA (m²)</label>
                            <input type="number" step="0.1" class="form-control drive-form-control w-100" id="area"
                                name="area" required placeholder="250">
                        </div>
                        <div class="col-md-4">
                            <label for="latitude" class="form-label text-secondary"
                                style="font-size: 0.75rem; font-weight:600;">LATITUDE</label>
                            <input type="text" class="form-control drive-form-control w-100" id="latitude"
                                name="latitude" required readonly placeholder="Click map">
                        </div>
                        <div class="col-md-4">
                            <label for="longitude" class="form-label text-secondary"
                                style="font-size: 0.75rem; font-weight:600;">LONGITUDE</label>
                            <input type="text" class="form-control drive-form-control w-100" id="longitude"
                                name="longitude" required readonly placeholder="Click map">
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">DESCRIPTION</label>
                        <textarea class="form-control drive-form-control w-100" id="description" name="description"
                            rows="3"
                            placeholder="Soil quality, water sources, sun exposure, or guidelines..."></textarea>
                    </div>

                    <!-- Allowed Seed Types / Permitted Crops -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label text-secondary m-0"
                                style="font-size: 0.75rem; font-weight:600;">PERMITTED CROPS</label>
                            <button type="button" class="btn btn-sm btn-drive-secondary rounded-pill px-3 py-1 d-flex align-items-center gap-1.5"
                                data-bs-toggle="modal" data-bs-target="#permittedCropsModal" style="font-size: 0.75rem;">
                                <i data-lucide="search" style="width: 14px; height: 14px;"></i>
                                <span>Select Crops</span>
                            </button>
                        </div>
                        
                        <!-- Selected Crops Summary Box -->
                        <div id="selectedCropsSummary" class="p-3 border rounded-4 bg-light d-flex flex-wrap gap-2 align-items-center"
                            style="min-height: 52px; border-color: var(--drive-border) !important;">
                            <span class="text-muted text-xs italic" id="emptyCropsNotice">No specific crops selected (All crops permitted by default). Click "Select Crops" to restrict allowed crop types.</span>
                        </div>

                        <!-- Hidden container for checked inputs submitted with form -->
                        <div id="hiddenSeedInputs"></div>
                    </div>

                    <!-- Image Upload (Google style drag panel mockup) -->
                    <div class="mb-4">
                        <label class="form-label text-secondary" style="font-size: 0.75rem; font-weight:600;">PHOTOS</label>
                        <div class="border rounded-4 p-4 text-center bg-light"
                            style="border-style: dashed !important; border-color: var(--drive-border) !important;">
                            <i class="bi bi-cloud-arrow-up fs-2 text-primary mb-2"></i>
                            <p class="mb-1 text-dark fw-medium" style="font-size: 0.85rem;">Upload files</p>
                            <span class="text-secondary d-block mb-3" style="font-size: 0.75rem;">JPEG or PNG</span>
                            <input type="file" id="images" name="images[]" multiple class="d-none"
                                onchange="updateUploadLabel(this)">
                            <button type="button" class="btn btn-sm btn-drive-secondary px-3"
                                onclick="document.getElementById('images').click()">Select Files</button>
                            <span id="file-count" class="d-block mt-2 text-success fw-medium"
                                style="font-size: 12px;"></span>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="lands.php" class="btn btn-drive-secondary">Cancel</a>
                        <button type="submit" class="btn btn-drive-primary">Submit</button>
                    </div>
                </form>
            </div>

            <!-- Right: Map Picker Helper -->
            <div class="col-md-5">
                <div class="card border rounded-4 overflow-hidden h-100 "
                    style="border-color: var(--drive-border) !important;">
                    <div
                        class="card-header bg-light border-bottom py-2 d-flex align-items-center justify-content-between">
                        <span class="fw-semibold text-secondary"
                            style="font-size: 0.75rem; letter-spacing:0.5px; text-transform:uppercase;">Location Picker</span>
                        <i class="bi bi-geo-fill text-primary"></i>
                    </div>
                    <div id="pickerMap" style="height: 350px; background-color: #e9f2ff;"></div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
$seedTypes = [
    ['value' => 'tomato', 'label' => 'Tomato', 'icon' => '../assets/crop-icons/tomato/tomato.svg'],
    ['value' => 'lettuce', 'label' => 'Lettuce', 'icon' => '../assets/crop-icons/romaine/romaine.svg'],
    ['value' => 'herbs', 'label' => 'Herbs', 'icon' => '../assets/crop-icons/basil/basil.svg'],
    ['value' => 'pepper', 'label' => 'Pepper', 'icon' => '../assets/crop-icons/red-bell-pepper/red-bell-pepper.svg'],
    ['value' => 'carrot', 'label' => 'Carrot', 'icon' => '../assets/crop-icons/carrot/carrot.svg'],
    ['value' => 'eggplant', 'label' => 'Eggplant', 'icon' => '../assets/crop-icons/eggplant/eggplant.svg'],
    ['value' => 'cucumber', 'label' => 'Cucumber', 'icon' => '../assets/crop-icons/cucumber/cucumber.svg'],
    ['value' => 'spinach', 'label' => 'Spinach', 'icon' => '../assets/crop-icons/spinach/spinach.svg'],
    ['value' => 'beans', 'label' => 'Beans', 'icon' => '../assets/crop-icons/broad-bean/broad-bean.svg'],
    ['value' => 'squash', 'label' => 'Squash', 'icon' => '../assets/crop-icons/yellow-squash/yellow-squash.svg'],
    ['value' => 'onion', 'label' => 'Onion', 'icon' => '../assets/crop-icons/red-onion/red-onion.svg'],
    ['value' => 'corn', 'label' => 'Corn', 'icon' => '../assets/crop-icons/corn/corn.svg'],
    ['value' => 'garlic', 'label' => 'Garlic', 'icon' => '../assets/crop-icons/garlic/garlic.svg'],
    ['value' => 'potato', 'label' => 'Potato', 'icon' => '../assets/crop-icons/russet-potato/russet-potato.svg'],
    ['value' => 'mushroom', 'label' => 'Mushroom', 'icon' => '../assets/crop-icons/generic-mushroom/generic-mushroom.svg'],
    ['value' => 'peas', 'label' => 'Peas', 'icon' => '../assets/crop-icons/snap-pea/snap-pea.svg'],
];
?>

<!-- Permitted Crops Selection Modal -->
<div class="modal fade" id="permittedCropsModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content drive-modal-content rounded-4 overflow-hidden border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3 bg-white">
                <div>
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 1rem;">
                        <img src="../assets/crop-icons/generic-plant/generic-plant.svg" class="w-6 h-6 object-contain" alt="Crops">
                        Select Permitted Crops
                    </h5>
                    <p class="text-secondary mb-0" style="font-size: 0.75rem;">Choose the crop types gardeners are allowed to cultivate on this land.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4 bg-light">
                <!-- Search & Quick Selection Actions -->
                <div class="d-flex flex-column flex-sm-row gap-3 align-items-center justify-content-between mb-4">
                    <div class="position-relative w-100 me-sm-2">
                        <i data-lucide="search" class="position-absolute text-secondary" style="left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px;"></i>
                        <input type="text" id="cropSearchInput" class="form-control drive-form-control ps-5 py-2 text-xs rounded-pill"
                            placeholder="Search crop types (e.g., Tomato, Lettuce, Carrot)..." onkeyup="filterCropChips()">
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 text-xs" onclick="selectAllCrops(true)">Select All</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 text-xs" onclick="selectAllCrops(false)">Clear All</button>
                    </div>
                </div>

                <!-- Crop Grid Container -->
                <div class="row g-2 overflow-y-auto" id="modalCropGrid" style="max-height: 340px;">
                    <?php foreach ($seedTypes as $seed): ?>
                        <div class="col-6 col-sm-4 col-md-3 crop-grid-item" data-name="<?php echo strtolower($seed['label']); ?>">
                            <div class="modal-crop-chip p-2.5 border rounded-3 bg-white d-flex align-items-center justify-content-between gap-2"
                                data-value="<?php echo $seed['value']; ?>"
                                data-label="<?php echo $seed['label']; ?>"
                                data-icon="<?php echo $seed['icon']; ?>"
                                style="cursor: pointer; transition: all 0.15s; border-color: var(--drive-border) !important; user-select: none;"
                                onclick="toggleCropSelection('<?php echo $seed['value']; ?>', '<?php echo $seed['label']; ?>', '<?php echo $seed['icon']; ?>', this)">
                                <div class="d-flex align-items-center gap-2 text-truncate">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center bg-drive-canvas border border-drive-border flex-shrink-0">
                                        <img src="<?php echo $seed['icon']; ?>" alt="<?php echo $seed['label']; ?>" class="w-5 h-5 object-contain">
                                    </div>
                                    <span class="fw-semibold text-dark text-xs truncate"><?php echo $seed['label']; ?></span>
                                </div>
                                <div class="crop-check-icon text-primary hidden">
                                    <i data-lucide="check-circle-2" style="width: 16px; height: 16px; color: #1a73e8;"></i>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="modal-footer border-top bg-white px-4 py-3 d-flex justify-content-between align-items-center">
                <span class="text-secondary text-xs fw-semibold"><span id="selectedCountText">0</span> crops selected</span>
                <button type="button" class="btn btn-drive-primary rounded-pill px-4" data-bs-dismiss="modal">Done</button>
            </div>
        </div>
    </div>
</div>

<script>
    const selectedCrops = new Map();

    function updateUploadLabel(input) {
        const count = input.files.length;
        const label = document.getElementById('file-count');
        label.textContent = count > 0 ? `${count} photo(s) selected.` : '';
    }

    function toggleCropSelection(val, label, icon, element) {
        const checkIcon = element.querySelector('.crop-check-icon');
        if (selectedCrops.has(val)) {
            selectedCrops.delete(val);
            element.classList.remove('border-primary', 'bg-blue-50/50');
            element.style.borderColor = 'var(--drive-border)';
            if (checkIcon) checkIcon.classList.add('hidden');
        } else {
            selectedCrops.set(val, { value: val, label: label, icon: icon });
            element.classList.add('border-primary', 'bg-blue-50/50');
            element.style.borderColor = '#1a73e8';
            if (checkIcon) checkIcon.classList.remove('hidden');
        }
        renderSelectedCropsSummary();
    }

    function selectAllCrops(select) {
        document.querySelectorAll('.modal-crop-chip').forEach(chip => {
            const val = chip.getAttribute('data-value');
            const label = chip.getAttribute('data-label');
            const icon = chip.getAttribute('data-icon');
            const checkIcon = chip.querySelector('.crop-check-icon');

            if (select) {
                selectedCrops.set(val, { value: val, label: label, icon: icon });
                chip.classList.add('border-primary', 'bg-blue-50/50');
                chip.style.borderColor = '#1a73e8';
                if (checkIcon) checkIcon.classList.remove('hidden');
            } else {
                selectedCrops.delete(val);
                chip.classList.remove('border-primary', 'bg-blue-50/50');
                chip.style.borderColor = 'var(--drive-border)';
                if (checkIcon) checkIcon.classList.add('hidden');
            }
        });
        renderSelectedCropsSummary();
    }

    function filterCropChips() {
        const query = document.getElementById('cropSearchInput').value.toLowerCase().trim();
        document.querySelectorAll('.crop-grid-item').forEach(item => {
            const name = item.getAttribute('data-name');
            if (!query || name.includes(query)) {
                item.classList.remove('d-none');
            } else {
                item.classList.add('d-none');
            }
        });
    }

    function renderSelectedCropsSummary() {
        const summaryBox = document.getElementById('selectedCropsSummary');
        const hiddenInputs = document.getElementById('hiddenSeedInputs');
        const countText = document.getElementById('selectedCountText');

        if (countText) countText.textContent = selectedCrops.size;

        if (selectedCrops.size === 0) {
            summaryBox.innerHTML = `<span class="text-muted text-xs italic" id="emptyCropsNotice">No specific crops selected (All crops permitted by default). Click "Select Crops" to restrict allowed crop types.</span>`;
            hiddenInputs.innerHTML = '';
            return;
        }

        let summaryHtml = '';
        let inputsHtml = '';

        selectedCrops.forEach((crop) => {
            summaryHtml += `
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 border rounded-pill bg-white text-xs fw-semibold text-dark shadow-xs" style="border-color: var(--drive-border) !important;">
                    <img src="${crop.icon}" alt="${crop.label}" class="w-4 h-4 object-contain">
                    <span>${crop.label}</span>
                    <button type="button" class="btn-close text-xs ms-1" style="width: 10px; height: 10px;" onclick="toggleCropSelection('${crop.value}', '${crop.label}', '${crop.icon}', document.querySelector('[data-value=\"${crop.value}\"]'))"></button>
                </div>
            `;
            inputsHtml += `<input type="checkbox" name="allowed_seeds[]" value="${crop.value}" checked class="d-none">`;
        });

        summaryBox.innerHTML = summaryHtml;
        hiddenInputs.innerHTML = inputsHtml;
    }

    // Leaflet click to select coordinates
    document.addEventListener("DOMContentLoaded", function () {
        const defaultLat = 14.5995;
        const defaultLng = 120.9842;

        const map = L.map('pickerMap').setView([defaultLat, defaultLng], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        let marker;

        map.on('click', function (e) {
            const lat = e.latlng.lat.toFixed(6);
            const lng = e.latlng.lng.toFixed(6);

            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;

            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng).addTo(map);
            }
        });
        
        if (window.lucide) {
            lucide.createIcons();
        }
    });
</script>

<?php include '../includes/footer.php'; ?>