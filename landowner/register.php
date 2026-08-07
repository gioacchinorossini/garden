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

    $plant_types = isset($_POST['plant_types']) ? implode(',', array_map('htmlspecialchars', $_POST['plant_types'])) : '';

    // Store in mock session lands
    if (!isset($_SESSION['mock_lands'])) {
        $_SESSION['mock_lands'] = [];
    }
    $new_id = count($_SESSION['mock_lands']) + 1;
    $_SESSION['mock_lands'][] = [
        'id'          => $new_id,
        'title'       => $title,
        'landowner'   => $_SESSION['user_name'] ?? 'John Landowner',
        'address'     => $address,
        'latitude'    => $lat,
        'longitude'   => $lng,
        'area'        => $area,
        'status'      => 'pending',
        'reason'      => '',
        'description' => $desc,
        'plant_types' => $plant_types
    ];
    $success_message = "Land registered successfully! It is now pending Administrator review.";
}
?>

<main class="workspace-surface">
    <!-- Toolbar/Title Bar -->
    <div class="toolbar border-bottom">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">Register Idle Land</h1>
            <p class="text-muted mb-0" style="font-size: 0.75rem;">Submit details about your unused parcel of land to
                open it for community gardeners</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="lands.php" class="btn btn-outline-secondary rounded-pill btn-sm px-3">
                <i class="bi bi-arrow-left"></i> Back to My Lands
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
                            style="font-size: 0.75rem; font-weight:600;">LAND / PROPERTY NAME</label>
                        <input type="text" class="form-control drive-form-control w-100" id="title" name="title"
                            required placeholder="e.g. Sunnyvale Empty Lot">
                    </div>

                    <!-- Address -->
                    <div class="mb-3">
                        <label for="address" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">PHYSICAL ADDRESS</label>
                        <input type="text" class="form-control drive-form-control w-100" id="address" name="address"
                            required placeholder="e.g. 124 Green Ave, Sunnyvale">
                    </div>

                    <!-- Size & Coordinates -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="area" class="form-label text-secondary"
                                style="font-size: 0.75rem; font-weight:600;">TOTAL AREA (m²)</label>
                            <input type="number" step="0.1" class="form-control drive-form-control w-100" id="area"
                                name="area" required placeholder="e.g. 250">
                        </div>
                        <div class="col-md-4">
                            <label for="latitude" class="form-label text-secondary"
                                style="font-size: 0.75rem; font-weight:600;">LATITUDE</label>
                            <input type="text" class="form-control drive-form-control w-100" id="latitude"
                                name="latitude" required readonly placeholder="Click map to pick">
                        </div>
                        <div class="col-md-4">
                            <label for="longitude" class="form-label text-secondary"
                                style="font-size: 0.75rem; font-weight:600;">LONGITUDE</label>
                            <input type="text" class="form-control drive-form-control w-100" id="longitude"
                                name="longitude" required readonly placeholder="Click map to pick">
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">DESCRIPTION / GUIDELINES</label>
                        <textarea class="form-control drive-form-control w-100" id="description" name="description"
                            rows="3"
                            placeholder="Provide information about soil quality, water sources, sun exposure, or rules of your space..."></textarea>
                    </div>

                    <!-- Plant Types Allowed -->
                    <div class="mb-4">
                        <label class="form-label text-secondary d-block" style="font-size: 0.75rem; font-weight:600;">ALLOWED PLANT TYPES</label>
                        <p class="text-muted mb-2" style="font-size: 0.72rem;">Select all crop categories permitted on this land. Gardeners will see these when browsing.</p>
                        <div class="d-flex flex-wrap gap-2" id="plantChipsContainer">
                            <?php
                            $plant_options = [
                                ['value' => 'vegetables',   'label' => 'Vegetables',    'icon' => 'bi-carrot-fill',        'color' => '#e8f5e9', 'border' => '#4caf50'],
                                ['value' => 'herbs',        'label' => 'Herbs',          'icon' => 'bi-flower1',            'color' => '#f3e5f5', 'border' => '#9c27b0'],
                                ['value' => 'fruits',       'label' => 'Fruits',         'icon' => 'bi-apple',              'color' => '#fce4ec', 'border' => '#e91e63'],
                                ['value' => 'leafy_greens', 'label' => 'Leafy Greens',   'icon' => 'bi-tree',               'color' => '#e0f2f1', 'border' => '#009688'],
                                ['value' => 'root_crops',   'label' => 'Root Crops',     'icon' => 'bi-diamond-fill',       'color' => '#fff3e0', 'border' => '#ff9800'],
                                ['value' => 'legumes',      'label' => 'Legumes',        'icon' => 'bi-egg-fill',           'color' => '#e8eaf6', 'border' => '#3f51b5'],
                                ['value' => 'flowers',      'label' => 'Flowers',        'icon' => 'bi-flower3',            'color' => '#fce4ec', 'border' => '#f06292'],
                                ['value' => 'grains',       'label' => 'Grains & Cereals','icon' => 'bi-star-fill',         'color' => '#fffde7', 'border' => '#fbc02d'],
                                ['value' => 'medicinal',    'label' => 'Medicinal',      'icon' => 'bi-heart-pulse-fill',   'color' => '#e8f5e9', 'border' => '#43a047'],
                                ['value' => 'mushrooms',    'label' => 'Mushrooms',      'icon' => 'bi-cloud-fill',         'color' => '#efebe9', 'border' => '#795548'],
                            ];
                            foreach ($plant_options as $opt):
                            ?>
                            <label class="plant-chip" style="cursor:pointer;">
                                <input type="checkbox" name="plant_types[]" value="<?php echo $opt['value']; ?>" class="d-none plant-chip-input">
                                <span class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill border"
                                      style="font-size: 0.78rem; font-weight: 500; background: #f8f9fa; border-color: var(--drive-border) !important; transition: all 0.15s ease; user-select: none;"
                                      data-bg="<?php echo $opt['color']; ?>"
                                      data-border="<?php echo $opt['border']; ?>">
                                    <i class="bi <?php echo $opt['icon']; ?>" style="font-size: 0.9rem;"></i>
                                    <?php echo $opt['label']; ?>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-secondary" style="font-size: 0.75rem; font-weight:600;">LAND
                            PHOTOS</label>
                        <div class="border rounded-4 p-4 text-center bg-light"
                            style="border-style: dashed !important; border-color: var(--drive-border) !important;">
                            <i class="bi bi-cloud-arrow-up fs-2 text-primary mb-2"></i>
                            <p class="mb-1 text-dark fw-medium" style="font-size: 0.85rem;">Upload files by selection
                            </p>
                            <span class="text-secondary d-block mb-3" style="font-size: 0.75rem;">Supported formats:
                                JPEG, PNG. Max file size: 5MB</span>
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
                        <button type="submit" class="btn btn-drive-primary">Submit Registry</button>
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
                            style="font-size: 0.75rem; letter-spacing:0.5px; text-transform:uppercase;">Click Map to
                            Pick GPS Coordinates</span>
                        <i class="bi bi-geo-fill text-primary"></i>
                    </div>
                    <div id="pickerMap" style="height: 350px; background-color: #e9f2ff;"></div>
                    <div class="card-body bg-light" style="font-size: 0.75rem;">
                        <p class="mb-0 text-muted"><i class="bi bi-info-circle-fill me-1 text-primary"></i> Locate your
                            land parcel on the map and click exactly where it is located. The coordinates will
                            automatically load into the form.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    function updateUploadLabel(input) {
        const count = input.files.length;
        const label = document.getElementById('file-count');
        label.textContent = count > 0 ? `${count} photo(s) selected.` : '';
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
    });
</script>

<?php include '../includes/footer.php'; ?>