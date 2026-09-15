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
    $plot_count = max(1, intval($_POST['plot_count'] ?? 4));
    $has_3d = isset($_POST['enable_3d_view']) ? 1 : 0;
    $polygon = isset($_POST['lot_polygon']) ? htmlspecialchars(trim($_POST['lot_polygon'])) : '';

    $seeds = isset($_POST['allowed_seeds']) ? array_map('htmlspecialchars', $_POST['allowed_seeds']) : [];

    // LRA Title Verification inputs
    $title_type = htmlspecialchars(trim($_POST['title_type'] ?? 'TCT'));
    $title_number = htmlspecialchars(trim($_POST['title_number'] ?? ''));
    $rod_name = htmlspecialchars(trim($_POST['rod_name'] ?? ''));
    $epeb_type = htmlspecialchars(trim($_POST['epeb_type'] ?? 'CCV'));
    $epeb_no = htmlspecialchars(trim($_POST['epeb_no'] ?? ''));

    // Handle Title / CTC Document Upload
    $title_doc_path = 'assets/images/sample_title_cert.svg';
    if (isset($_FILES['title_document']) && $_FILES['title_document']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['title_document']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'svg'])) {
            $fileName = 'title_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $uploadTarget = __DIR__ . '/../uploads/titles/' . $fileName;
            if (move_uploaded_file($_FILES['title_document']['tmp_name'], $uploadTarget)) {
                $title_doc_path = 'uploads/titles/' . $fileName;
            }
        }
    }

    // Handle LRA Official Receipt Upload
    $receipt_doc_path = 'assets/images/sample_lra_receipt.svg';
    if (isset($_FILES['lra_receipt']) && $_FILES['lra_receipt']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['lra_receipt']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'svg'])) {
            $fileName = 'receipt_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $uploadTarget = __DIR__ . '/../uploads/receipts/' . $fileName;
            if (move_uploaded_file($_FILES['lra_receipt']['tmp_name'], $uploadTarget)) {
                $receipt_doc_path = 'uploads/receipts/' . $fileName;
            }
        }
    }

    // Store in mock session lands
    if (!isset($_SESSION['mock_lands'])) {
        $_SESSION['mock_lands'] = [];
    }
    $new_id = count($_SESSION['mock_lands']) + 1;
    $_SESSION['mock_lands'][] = [
        'id' => $new_id,
        'title' => $title,
        'title_type' => $title_type,
        'title_number' => $title_number,
        'rod_name' => $rod_name,
        'epeb_type' => $epeb_type,
        'epeb_no' => $epeb_no,
        'title_document_path' => $title_doc_path,
        'lra_receipt_path' => $receipt_doc_path,
        'is_lra_verified' => 0,
        'landowner' => $_SESSION['user_name'] ?? 'John Landowner',
        'address' => $address,
        'latitude' => $lat,
        'longitude' => $lng,
        'area' => $area,
        'status' => 'pending',
        'reason' => '',
        'description' => $desc,
        'allowed_seeds' => $seeds,
        'total_plots' => $plot_count,
        'occupied_plots' => 0,
        'has_3d_view' => $has_3d,
        'polygon' => $polygon
    ];

    // Auto-create partition plots in mock session plots
    if (!isset($_SESSION['mock_plots'])) {
        $_SESSION['mock_plots'] = [];
    }
    $plot_area = round($area / $plot_count, 1);
    for ($i = 1; $i <= $plot_count; $i++) {
        $plot_id = count($_SESSION['mock_plots']) + 1;
        $crop = !empty($seeds) ? $seeds[($i - 1) % count($seeds)] : 'Vegetables';
        $_SESSION['mock_plots'][] = [
            'id' => $plot_id,
            'land_id' => $new_id,
            'plot_number' => 'Plot A-' . $i,
            'area' => $plot_area,
            'status' => 'available',
            'crop' => $crop,
            'crops' => $seeds,
            'farmer_name' => '',
            'lease_end' => ''
        ];
    }

    $success_message = "Land registered successfully with {$plot_count} partition plot(s) and 3D digital twin preview! It is now pending Administrator review.";
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

        <!-- Top: Interactive Map & 3D Digital Twin Card (Positioned Above the Form) -->
        <div class="card border rounded-4 overflow-hidden mb-4 shadow-sm"
            style="border-color: var(--drive-border) !important;">
            <div
                class="card-header bg-white border-bottom py-2.5 px-3 px-md-4 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width: 34px; height: 34px;">
                        <i class="bi bi-geo-alt-fill fs-6"></i>
                    </div>
                    <div>
                        <span class="fw-bold text-dark d-block" style="font-size: 0.88rem;">Property Location &
                            Interactive Map</span>
                        <span class="text-muted text-xs">Search address, pin location on map, or launch the fullscreen
                            layout studio</span>
                    </div>
                </div>

                <!-- Drawing Mode Toolbar & 2D/3D Mode Switcher -->
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <!-- Plot Drawing Tools -->
                    <div class="btn-group btn-group-sm p-0.5 bg-light border rounded-pill shadow-xs" role="group" id="plotDrawToolbar">
                        <button type="button" class="btn btn-xs rounded-pill px-3 py-1.5 fw-semibold text-dark active"
                            id="drawModePanBtn" onclick="setPlotDrawMode('pan')" title="Pin Point or Pan Map">
                            <i class="bi bi-cursor-fill me-1 text-primary"></i>Pin Point
                        </button>
                        <button type="button" class="btn btn-xs rounded-pill px-3 py-1.5 fw-semibold text-secondary"
                            id="drawModePolygonBtn" onclick="setPlotDrawMode('polygon')" title="Click on the map to draw custom plot boundary corners">
                            <i class="bi bi-pentagon-fill me-1 text-success"></i>Draw Plot
                        </button>
                        <button type="button" class="btn btn-xs rounded-pill px-3 py-1.5 fw-semibold text-secondary"
                            id="drawModeBoxBtn" onclick="setPlotDrawMode('box')" title="Click two points to draw a box/rectangle plot">
                            <i class="bi bi-bounding-box-circles me-1 text-success"></i>Draw Box
                        </button>
                    </div>

                    <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2.5 py-1 text-xs d-none"
                        id="btnClearDrawnPlot" onclick="clearDrawnPlot()" title="Clear drawn plot boundary">
                        <i class="bi bi-trash3 me-1"></i>Clear Boundary
                    </button>

                    <!-- 2D / 3D Switcher -->
                    <div class="btn-group btn-group-sm p-0.5 bg-light border rounded-pill shadow-xs" role="group">
                        <button type="button" class="btn btn-xs rounded-pill px-3 py-1 fw-bold text-success active"
                            id="viewMode2DBtn" onclick="switchPickerMode('2d')">
                            <i class="bi bi-map me-1"></i>2D Map
                        </button>
                        <button type="button" class="btn btn-xs rounded-pill px-3 py-1 text-muted" id="viewMode3DBtn"
                            onclick="switchPickerMode('3d')">
                            <i class="bi bi-box-fill me-1 text-primary"></i>3D Preview
                        </button>
                    </div>
                </div>
            </div>

            <div class="map-picker-wrapper position-relative">
                <!-- Floating Location Search Bar (Compact Width) -->
                <div class="map-picker-search-box" style="width: 340px; max-width: calc(100% - 190px);">
                    <div class="map-picker-input-group">
                        <i class="bi bi-search text-muted ms-1" style="font-size:0.85rem;"></i>
                        <input type="text" id="mapSearchInput" placeholder="Search address, landmark, or coordinates..."
                            autocomplete="off">
                        <button type="button" class="map-picker-btn-icon d-none" id="mapSearchClearBtn"
                            title="Clear search" onclick="clearMapSearch()">
                            <i class="bi bi-x-circle-fill" style="font-size:0.85rem;"></i>
                        </button>
                        <button type="button" class="map-picker-btn-icon text-success" id="mapSearchGpsBtn"
                            title="Use my current GPS location" onclick="useCurrentLocation()">
                            <i class="bi bi-crosshair" style="font-size:1rem;"></i>
                        </button>
                    </div>
                    <!-- Autocomplete Dropdown List -->
                    <div class="map-picker-results" id="mapSearchResults"></div>
                </div>

                <!-- Floating Drawing Status & Instruction Helper -->
                <div class="map-drawing-helper-pill d-none" id="mapDrawingHelper" style="position: absolute; top: 68px; left: 14px; z-index: 1000; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(0,0,0,0.14); box-shadow: 0 6px 18px rgba(0,0,0,0.15); border-radius: 50rem; padding: 6px 14px;">
                    <div class="d-flex align-items-center gap-2">
                        <span id="mapDrawingHelperText" class="text-xs fw-semibold text-dark">Click on the map to add boundary corners</span>
                        <button type="button" class="btn btn-xs btn-success rounded-pill px-2.5 py-0.5 text-xs fw-bold d-none" id="btnFinishPolygon" onclick="finishCurrentPolygon()">
                            <i class="bi bi-check-circle me-1"></i>Finish Plot
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-0.5 text-xs" onclick="setPlotDrawMode('pan')">
                            Cancel
                        </button>
                    </div>
                </div>

                <!-- 2D Leaflet Map Canvas (Above the Form - Expanded Size) -->
                <div id="pickerMap" style="height: 960px; min-height: 500px; background-color: #e9f2ff;"></div>

                <!-- 3D City & Garden Visualization Container -->
                <div id="register3DMapContainer" class="map3d-container" style="height: 960px; min-height: 500px;">
                </div>

                <!-- Pinned Address / Coordinates Status Bar -->
                <div class="map-picker-status-bar" id="mapStatusBar">
                    <div class="d-flex align-items-center gap-1 text-truncate">
                        <i class="bi bi-pin-map-fill text-danger flex-shrink-0"></i>
                        <span class="text-truncate" id="pinnedAddressText">No location selected yet</span>
                    </div>
                    <span class="badge bg-success-subtle text-success ms-2 flex-shrink-0" id="coordsBadge"
                        style="font-size:0.68rem; display:none;"></span>
                </div>
            </div>
        </div>

        <!-- Below: Property Registration Form -->
        <div class="card border rounded-4 bg-white p-3 p-md-4 shadow-sm mb-4"
            style="border-color: var(--drive-border) !important;">
            <form action="register.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="lot_polygon" id="lot_polygon">

                <div class="d-flex align-items-center justify-content-between border-bottom pb-2.5 mb-3">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                        <i class="bi bi-card-checklist text-success fs-5"></i>
                        <span>Property Details & Registration Specifications</span>
                    </h6>
                    <span class="text-muted text-xs">Fill in your land details below</span>
                </div>

                <div class="row g-3">
                    <!-- Land Title -->
                    <div class="col-md-6">
                        <label for="title" class="form-label text-secondary fw-semibold text-xs mb-1">PROPERTY
                            NAME</label>
                        <input type="text" class="form-control drive-form-control w-100" id="title" name="title"
                            required placeholder="Sunnyvale Empty Lot">
                    </div>

                    <!-- Address -->
                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label for="address"
                                class="form-label text-secondary m-0 fw-semibold text-xs">ADDRESS</label>
                            <button type="button"
                                class="btn btn-link text-success text-decoration-none p-0 text-xs fw-semibold"
                                onclick="searchAddressFromInput()">
                                <i class="bi bi-geo-alt-fill me-1"></i>Find on map
                            </button>
                        </div>
                        <div class="input-group">
                            <input type="text" class="form-control drive-form-control" id="address" name="address"
                                required placeholder="124 Green Ave, Sunnyvale"
                                onkeydown="if(event.key==='Enter'){event.preventDefault();searchAddressFromInput();}">
                            <button class="btn btn-outline-success px-3" type="button"
                                onclick="searchAddressFromInput()" title="Locate this address on map">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Size & Coordinates -->
                    <div class="col-md-4">
                        <label for="area" class="form-label text-secondary fw-semibold text-xs mb-1">TOTAL AREA
                            (m²)</label>
                        <input type="number" step="0.1" class="form-control drive-form-control w-100" id="area"
                            name="area" required placeholder="250" oninput="updatePlotCalculation()">
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label for="latitude"
                                class="form-label text-secondary m-0 fw-semibold text-xs">LATITUDE</label>
                            <span class="text-success text-xs fw-semibold"><i class="bi bi-pencil me-0.5"></i>Type or
                                click map</span>
                        </div>
                        <input type="number" step="any" class="form-control drive-form-control w-100" id="latitude"
                            name="latitude" required placeholder="e.g. 14.599512">
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label for="longitude"
                                class="form-label text-secondary m-0 fw-semibold text-xs">LONGITUDE</label>
                            <span class="text-success text-xs fw-semibold"><i class="bi bi-pencil me-0.5"></i>Type or
                                click map</span>
                        </div>
                        <input type="number" step="any" class="form-control drive-form-control w-100" id="longitude"
                            name="longitude" required placeholder="e.g. 120.984222">
                    </div>

                    <!-- Partition Plots Setup (col-lg-7) -->
                    <div class="col-lg-7">
                        <div class="p-3 border rounded-4 bg-light shadow-xs h-100 d-flex flex-column justify-content-between"
                            style="border-color: var(--drive-border) !important;">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div>
                                        <label class="form-label text-secondary m-0 fw-bold"
                                            style="font-size: 0.75rem;">
                                            <i class="bi bi-grid-3x3-gap-fill text-success me-1"></i>PARTITION PLOTS TO
                                            REGISTER
                                        </label>
                                        <span class="text-muted text-xs d-block">Subdivide this property into plots for
                                            community gardeners</span>
                                    </div>
                                    <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1"
                                        id="plotCalcBadge" style="font-size:0.75rem;">
                                        4 plots (~62.5 m² each)
                                    </span>
                                </div>

                                <div class="row g-2 align-items-center">
                                    <div class="col-6 col-sm-4">
                                        <label for="plot_count" class="form-label text-secondary m-0 text-xs">Number of
                                            Plots</label>
                                        <select class="form-select drive-form-control text-xs" id="plot_count"
                                            name="plot_count" onchange="updatePlotCalculation()">
                                            <option value="1">1 Single Plot</option>
                                            <option value="2">2 Partition Plots</option>
                                            <option value="4" selected>4 Plots (2×2 Grid)</option>
                                            <option value="6">6 Plots (2×3 Grid)</option>
                                            <option value="8">8 Plots (2×4 Grid)</option>
                                            <option value="12">12 Plots</option>
                                            <option value="16">16 Plots</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-sm-8">
                                        <label class="form-label text-secondary m-0 text-xs">Calculated Area per
                                            Plot</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control drive-form-control text-xs bg-white"
                                                id="singlePlotArea" readonly value="62.5">
                                            <span class="input-group-text bg-white text-muted text-xs">m² per
                                                plot</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mini Plot Grid Preview Badges -->
                                <div class="mt-2.5 pt-2 border-top d-flex align-items-center gap-1.5 flex-wrap"
                                    id="plotGridPreviewBadges"></div>
                            </div>

                            <!-- Plot Boundary Status & Draw Shortcut -->
                            <div class="mt-2.5 p-2.5 rounded-3 bg-light border d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2 overflow-hidden">
                                    <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center flex-shrink-0"
                                        style="width: 32px; height: 32px;">
                                        <i class="bi bi-vector-pen" style="font-size: 15px;"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="fw-bold text-dark text-xs">Plot Boundary Shape</div>
                                        <div class="text-muted text-xs text-truncate" id="drawnBoundarySummary">No custom boundary drawn. Use map tools above to draw.</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
                                    <button type="button" class="btn btn-xs btn-outline-success rounded-pill px-2.5 py-1 fw-semibold"
                                        onclick="setPlotDrawMode('polygon')" title="Draw polygon boundary directly on map">
                                        <i class="bi bi-pencil-fill me-1"></i>Draw Plot
                                    </button>
                                    <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1 d-none"
                                        id="btnFormClearDrawn" onclick="clearDrawnPlot()" title="Clear drawn boundary">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3D Digital Twin Settings (col-lg-5) -->
                    <div class="col-lg-5">
                        <div class="p-3 border rounded-4 bg-light shadow-xs h-100 d-flex flex-column justify-content-between"
                            style="border-color: rgba(56, 189, 248, 0.4) !important;">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width: 36px; height: 36px;">
                                            <i class="bi bi-box-fill text-primary" style="font-size:16px;"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size:0.85rem;">3D Digital
                                                Twin Map View</h6>
                                            <span class="text-muted text-xs">Generate realistic 3D building & plot
                                                view</span>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" id="enable_3d_view"
                                            name="enable_3d_view" value="1" checked
                                            style="cursor:pointer; width: 40px; height: 20px;">
                                    </div>
                                </div>
                                <p class="text-xs text-muted mb-0">
                                    Enables WebGL 3D architectural extrusions, realistic sun lighting, and direct 3D
                                    plot bed dragging for registered parcels.
                                </p>
                            </div>
                            <div class="pt-2 border-top mt-3">
                                <button type="button"
                                    class="btn btn-sm btn-outline-primary rounded-pill w-100 py-1.5 text-xs fw-semibold d-flex align-items-center justify-content-center gap-1.5"
                                    onclick="switchPickerMode('3d')">
                                    <i class="bi bi-box-fill"></i>
                                    <span>Preview in 3D Map View Above</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Description (col-md-6) -->
                    <div class="col-md-6">
                        <label for="description"
                            class="form-label text-secondary fw-semibold text-xs mb-1">DESCRIPTION</label>
                        <textarea class="form-control drive-form-control w-100" id="description" name="description"
                            rows="4"
                            placeholder="Soil quality, water sources, sun exposure, or guidelines..."></textarea>
                    </div>

                    <!-- Allowed Seed Types / Permitted Crops (col-md-6) -->
                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label text-secondary m-0 fw-semibold text-xs">PERMITTED CROPS</label>
                            <button type="button"
                                class="btn btn-sm btn-drive-secondary rounded-pill px-3 py-1 d-flex align-items-center gap-1.5 text-xs"
                                data-bs-toggle="modal" data-bs-target="#permittedCropsModal">
                                <i data-lucide="search" style="width: 14px; height: 14px;"></i>
                                <span>Select Crops</span>
                            </button>
                        </div>

                        <!-- Selected Crops Summary Box -->
                        <div id="selectedCropsSummary"
                            class="p-3 border rounded-4 bg-light d-flex flex-wrap gap-2 align-items-center"
                            style="min-height: 100px; max-height: 120px; overflow-y: auto; border-color: var(--drive-border) !important;">
                            <span class="text-muted text-xs italic" id="emptyCropsNotice">No specific crops selected
                                (All crops permitted by default). Click "Select Crops" to restrict allowed crop
                                types.</span>
                        </div>

                        <!-- Hidden container for checked inputs submitted with form -->
                        <div id="hiddenSeedInputs"></div>
                    </div>

                    <!-- Land Title & LRA Authenticity Verification (col-12) -->
                    <div class="col-12">
                        <div class="card border rounded-4 bg-white p-3 p-md-3.5 shadow-xs" style="border-color: rgba(37, 99, 235, 0.25) !important; background: linear-gradient(180deg, #f8faff 0%, #ffffff 100%);">
                            <div class="d-flex align-items-center justify-content-between border-bottom pb-2.5 mb-3 flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="bi bi-shield-check fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0" style="font-size: 0.92rem;">Land Title & LRA Authenticity Verification</h6>
                                        <span class="text-muted text-xs">Cross-verified via the Land Registration Authority On-line Tracking System (LOTS)</span>
                                    </div>
                                </div>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded-pill text-xs fw-semibold">
                                    <i class="bi bi-patch-check-fill me-1"></i>Official LRA Due Diligence
                                </span>
                            </div>

                            <div class="row g-3">
                                <!-- Title Document Type -->
                                <div class="col-md-3">
                                    <label for="title_type" class="form-label text-secondary fw-semibold text-xs mb-1">TITLE TYPE</label>
                                    <select class="form-select drive-form-control text-xs" id="title_type" name="title_type">
                                        <option value="TCT" selected>TCT (Transfer Certificate of Title)</option>
                                        <option value="OCT">OCT (Original Certificate of Title)</option>
                                        <option value="Tax_Declaration">Tax Declaration (Untitled Land)</option>
                                        <option value="CLOA">CLOA (Certificate of Land Ownership Award)</option>
                                    </select>
                                </div>

                                <!-- Title Number -->
                                <div class="col-md-5">
                                    <label for="title_number" class="form-label text-secondary fw-semibold text-xs mb-1">TITLE / CERTIFICATE NUMBER</label>
                                    <input type="text" class="form-control drive-form-control w-100" id="title_number" name="title_number"
                                        placeholder="e.g. TCT No. 004-2023001234">
                                </div>

                                <!-- Registry of Deeds -->
                                <div class="col-md-4">
                                    <label for="rod_name" class="form-label text-secondary fw-semibold text-xs mb-1">REGISTRY OF DEEDS (ROD)</label>
                                    <select class="form-select drive-form-control text-xs" id="rod_name" name="rod_name">
                                        <option value="">-- Select Registry of Deeds --</option>
                                        <option value="Quezon City" selected>Quezon City (RD 004)</option>
                                        <option value="City of Manila">City of Manila (RD 002)</option>
                                        <option value="Pasig">Pasig (RD 011)</option>
                                        <option value="Taguig City">Taguig City (RD 164)</option>
                                        <option value="Makati City">Makati City (RD 006)</option>
                                        <option value="Caloocan City">Caloocan City (RD 001)</option>
                                        <option value="Province of Cavite">Province of Cavite (RD 057)</option>
                                        <option value="Province of Laguna, Calamba Branch">Province of Laguna, Calamba (RD 060)</option>
                                        <option value="Province of Rizal">Province of Rizal (RD 068)</option>
                                        <option value="Province of Bulacan (Guiguinto)">Province of Bulacan (RD 039)</option>
                                        <option value="Province of Batangas">Province of Batangas (RD 053)</option>
                                        <option value="Province of Pampanga">Province of Pampanga (RD 042)</option>
                                        <option value="Cebu City">Cebu City (RD 107)</option>
                                        <option value="Davao City">Davao City (RD 146)</option>
                                        <option value="Iloilo City">Iloilo City (RD 095)</option>
                                        <option value="Baguio City">Baguio City (RD 018)</option>
                                    </select>
                                </div>

                                <!-- EPEB Type -->
                                <div class="col-md-4">
                                    <label for="epeb_type" class="form-label text-secondary fw-semibold text-xs mb-1">EPEB TRANSACTION TYPE</label>
                                    <select class="form-select drive-form-control text-xs" id="epeb_type" name="epeb_type">
                                        <option value="CCV" selected>CCV - Certified True Copy, Certification, Verification</option>
                                        <option value="Registered Land">Registered Land (RL_496)</option>
                                        <option value="Unregistered Land">Unregistered Land (UL_3344)</option>
                                    </select>
                                </div>

                                <!-- LRA EPEB Number -->
                                <div class="col-md-4">
                                    <label for="epeb_no" class="form-label text-secondary fw-semibold text-xs mb-1 d-flex align-items-center justify-content-between">
                                        <span>LRA EPEB / TRANSACTION NO.</span>
                                        <span class="text-primary text-xs" title="Find this number on your LRA Official Receipt"><i class="bi bi-info-circle"></i> On Receipt</span>
                                    </label>
                                    <input type="text" class="form-control drive-form-control w-100" id="epeb_no" name="epeb_no"
                                        placeholder="e.g. 2023004819" maxlength="25">
                                </div>

                                <!-- LRA Tracking Link Quick Helper -->
                                <div class="col-md-4 d-flex align-items-end">
                                    <a href="https://lots.lra.gov.ph/TransactionStatus/Search.aspx" target="_blank"
                                        class="btn btn-outline-primary btn-sm rounded-pill w-100 py-1.5 text-xs fw-semibold d-flex align-items-center justify-content-center gap-1.5"
                                        style="height: 38px;">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                        <span>Check on LRA LOTS Portal</span>
                                    </a>
                                </div>

                                <!-- Upload Certified Title / Scan -->
                                <div class="col-md-6">
                                    <label for="title_document" class="form-label text-secondary fw-semibold text-xs mb-1">
                                        <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i>UPLOAD TITLE / CERTIFIED TRUE COPY (CTC)
                                    </label>
                                    <div class="border rounded-3 p-2.5 bg-light d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2 overflow-hidden w-100">
                                            <i class="bi bi-file-earmark-check fs-4 text-primary flex-shrink-0"></i>
                                            <div class="overflow-hidden w-100">
                                                <input type="file" class="form-control form-control-sm text-xs" id="title_document" name="title_document" accept=".pdf,.png,.jpg,.jpeg,.svg">
                                                <small class="text-muted text-xs d-block text-truncate">PDF, JPG, or PNG of your latest certified true copy</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Upload LRA Official Receipt -->
                                <div class="col-md-6">
                                    <label for="lra_receipt" class="form-label text-secondary fw-semibold text-xs mb-1">
                                        <i class="bi bi-receipt text-success me-1"></i>UPLOAD LRA OFFICIAL RECEIPT (OR)
                                    </label>
                                    <div class="border rounded-3 p-2.5 bg-light d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2 overflow-hidden w-100">
                                            <i class="bi bi-receipt-cutoff fs-4 text-success flex-shrink-0"></i>
                                            <div class="overflow-hidden w-100">
                                                <input type="file" class="form-control form-control-sm text-xs" id="lra_receipt" name="lra_receipt" accept=".pdf,.png,.jpg,.jpeg,.svg">
                                                <small class="text-muted text-xs d-block text-truncate">Shows the EPEB number and Registry of Deeds branch</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Information Box -->
                                <div class="col-12">
                                    <div class="p-2.5 rounded-3 bg-white border d-flex align-items-center gap-2 text-xs text-muted">
                                        <i class="bi bi-info-circle-fill text-primary fs-6 flex-shrink-0"></i>
                                        <span>Submitting your official LRA receipt and EPEB number allows our administration to verify your land title against the official LRA tracking database. Verified lands receive prioritized placement and the trusted <strong>LRA Verified</strong> shield.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Photos Upload (col-12) -->
                    <div class="col-12">
                        <label class="form-label text-secondary fw-semibold text-xs mb-1">PHOTOS</label>
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

                    <!-- Form Action Buttons -->
                    <div class="col-12 d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="lands.php" class="btn btn-drive-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-drive-primary px-4 fw-semibold">Submit Property</button>
                    </div>
                </div>
            </form>
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
                        <img src="../assets/crop-icons/generic-plant/generic-plant.svg" class="w-6 h-6 object-contain"
                            alt="Crops">
                        Select Permitted Crops
                    </h5>
                    <p class="text-secondary mb-0" style="font-size: 0.75rem;">Choose the crop types gardeners are
                        allowed to cultivate on this land.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <!-- Search & Quick Selection Actions -->
                <div class="d-flex flex-column flex-sm-row gap-3 align-items-center justify-content-between mb-4">
                    <div class="position-relative w-100 me-sm-2">
                        <i data-lucide="search" class="position-absolute text-secondary"
                            style="left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px;"></i>
                        <input type="text" id="cropSearchInput"
                            class="form-control drive-form-control ps-5 py-2 text-xs rounded-pill"
                            placeholder="Search crop types (e.g., Tomato, Lettuce, Carrot)..."
                            onkeyup="filterCropChips()">
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 text-xs"
                            onclick="selectAllCrops(true)">Select All</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 text-xs"
                            onclick="selectAllCrops(false)">Clear All</button>
                    </div>
                </div>

                <!-- Crop Grid Container -->
                <div class="row g-2 overflow-y-auto" id="modalCropGrid" style="max-height: 340px;">
                    <?php foreach ($seedTypes as $seed): ?>
                        <div class="col-6 col-sm-4 col-md-3 crop-grid-item"
                            data-name="<?php echo strtolower($seed['label']); ?>">
                            <div class="modal-crop-chip p-2.5 border rounded-3 bg-white d-flex align-items-center justify-content-between gap-2"
                                data-value="<?php echo $seed['value']; ?>" data-label="<?php echo $seed['label']; ?>"
                                data-icon="<?php echo $seed['icon']; ?>"
                                style="cursor: pointer; transition: all 0.15s; border-color: var(--drive-border) !important; user-select: none;"
                                onclick="toggleCropSelection('<?php echo $seed['value']; ?>', '<?php echo $seed['label']; ?>', '<?php echo $seed['icon']; ?>', this)">
                                <div class="d-flex align-items-center gap-2 text-truncate">
                                    <div
                                        class="w-8 h-8 rounded-full flex items-center justify-center bg-drive-canvas border border-drive-border flex-shrink-0">
                                        <img src="<?php echo $seed['icon']; ?>" alt="<?php echo $seed['label']; ?>"
                                            class="w-5 h-5 object-contain">
                                    </div>
                                    <span
                                        class="fw-semibold text-dark text-xs truncate"><?php echo $seed['label']; ?></span>
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
                <span class="text-secondary text-xs fw-semibold"><span id="selectedCountText">0</span> crops
                    selected</span>
                <button type="button" class="btn btn-drive-primary rounded-pill px-4"
                    data-bs-dismiss="modal">Done</button>
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

    // Leaflet map with geocoding search, coordinate typing & 3D digital twin preview
    document.addEventListener("DOMContentLoaded", function () {
        const defaultLat = 14.5995;
        const defaultLng = 120.9842;

        const map = L.map('pickerMap', {
            zoomControl: false,
            maxZoom: 24
        }).setView([defaultLat, defaultLng], 13);
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 24,
            maxNativeZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 24,
            maxNativeZoom: 19,
            attribution: 'Tiles &copy; Esri &mdash; Source: Esri, Maxar, Earthstar Geographics'
        });

        L.control.layers({
            "Map (OSM)": osmLayer,
            "Satellite (High-Res)": satelliteLayer
        }, null, { position: 'bottomright' }).addTo(map);

        let marker = null;
        let boundaryPoly = null;
        let searchDebounceTimer = null;
        let coordDebounceTimer = null;
        let register3dEngine = null;
        let activeLayoutPlots = [];

        // ─── Direct Plot Drawing Engine Variables ───
        let currentDrawMode = 'pan'; // 'pan', 'polygon', 'box'
        let drawnPoints = []; // Array of L.LatLng
        let drawnPolygonLayer = null;
        let vertexMarkers = [];
        let subdivisionLayers = [];
        let drawingTempLine = null;
        let drawingGuideLine = null;
        let drawingStartMarker = null;
        let boxAnchor = null;
        let boxPreviewLayer = null;

        // Custom green pin icon
        const customPinIcon = L.divIcon({
            className: '',
            html: `<div style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;background:#198754;color:#fff;border-radius:50%;border:3px solid #fff;box-shadow:0 4px 12px rgba(0,0,0,0.3);font-size:16px;cursor:grab;">
                <i class="bi bi-geo-alt-fill"></i>
            </div>`,
            iconSize: [34, 34],
            iconAnchor: [17, 34]
        });

        function updateLotBoundary(lat, lng) {
            if (drawnPolygonLayer) return; // Do not draw square if custom polygon boundary is active
            const area = parseFloat(document.getElementById('area').value) || 200;
            const sideMeters = Math.sqrt(area);
            const halfSide = sideMeters / 2;
            const deltaLat = halfSide / 111320;
            const deltaLng = halfSide / (111320 * Math.cos(lat * Math.PI / 180));

            const bounds = [
                [lat - deltaLat, lng - deltaLng],
                [lat + deltaLat, lng + deltaLng]
            ];

            if (boundaryPoly) {
                boundaryPoly.setBounds(bounds);
            } else {
                boundaryPoly = L.rectangle(bounds, {
                    color: '#198754',
                    weight: 2,
                    fillColor: '#198754',
                    fillOpacity: 0.16,
                    dashArray: '5, 5'
                }).addTo(map);
            }
        }

        function setLocation(lat, lng, addressText = null, zoom = null) {
            lat = parseFloat(lat).toFixed(6);
            lng = parseFloat(lng).toFixed(6);

            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;

            const latlng = [parseFloat(lat), parseFloat(lng)];

            if (marker) {
                marker.setLatLng(latlng);
            } else {
                marker = L.marker(latlng, { draggable: true, icon: customPinIcon }).addTo(map);

                marker.on('dragend', function (e) {
                    const pos = e.target.getLatLng();
                    setLocation(pos.lat, pos.lng);
                    reverseGeocode(pos.lat, pos.lng);
                });
            }

            updateLotBoundary(parseFloat(lat), parseFloat(lng));

            if (zoom) {
                map.flyTo(latlng, zoom, { animate: true, duration: 1.2 });
            }

            // Update status bar
            const coordsBadge = document.getElementById('coordsBadge');
            if (coordsBadge) {
                coordsBadge.textContent = `${lat}, ${lng}`;
                coordsBadge.style.display = 'inline-block';
            }

            if (addressText) {
                const addrInput = document.getElementById('address');
                if (addrInput && (!addrInput.value.trim() || addrInput.value === addressText)) {
                    addrInput.value = addressText;
                }
                const pinnedText = document.getElementById('pinnedAddressText');
                if (pinnedText) pinnedText.textContent = addressText;
            }
        }

        map.on('click', function (e) {
            if (currentDrawMode === 'polygon') {
                handlePolygonClick(e);
                return;
            }
            if (currentDrawMode === 'box') {
                handleBoxClick(e);
                return;
            }
            setLocation(e.latlng.lat, e.latlng.lng);
            reverseGeocode(e.latlng.lat, e.latlng.lng);
        });

        map.on('mousemove', function (e) {
            if (currentDrawMode === 'polygon') {
                handlePolygonMouseMove(e);
            } else if (currentDrawMode === 'box') {
                handleBoxMouseMove(e);
            }
        });

        // Ensure Leaflet map sizes properly in the top full-width container
        setTimeout(() => {
            map.invalidateSize();
        }, 250);
        window.addEventListener('resize', () => {
            map.invalidateSize();
        });

        // ─── Direct Coordinate Typing Listeners ───
        function onCoordinateTyped() {
            const latVal = document.getElementById('latitude').value.trim();
            const lngVal = document.getElementById('longitude').value.trim();
            if (!latVal || !lngVal) return;

            const lat = parseFloat(latVal);
            const lng = parseFloat(lngVal);

            if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                setLocation(lat, lng, null, 16);
                reverseGeocode(lat, lng);
            }
        }

        ['latitude', 'longitude'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', function () {
                    clearTimeout(coordDebounceTimer);
                    coordDebounceTimer = setTimeout(onCoordinateTyped, 400);
                });
            }
        });

        // ─── Nominatim Search & Autocomplete ───
        const searchInput = document.getElementById('mapSearchInput');
        const resultsContainer = document.getElementById('mapSearchResults');
        const clearBtn = document.getElementById('mapSearchClearBtn');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const query = this.value.trim();
                if (clearBtn) clearBtn.classList.toggle('d-none', !query);

                // Check if user typed coordinates like "14.5995, 120.9842"
                const coordMatch = query.match(/^([-+]?\d+(\.\d+)?)[,\s]+([-+]?\d+(\.\d+)?)$/);
                if (coordMatch) {
                    const cLat = parseFloat(coordMatch[1]);
                    const cLng = parseFloat(coordMatch[3]);
                    if (!isNaN(cLat) && !isNaN(cLng) && cLat >= -90 && cLat <= 90 && cLng >= -180 && cLng <= 180) {
                        resultsContainer.classList.remove('active');
                        resultsContainer.innerHTML = '';
                        setLocation(cLat, cLng, `${cLat}, ${cLng}`, 17);
                        reverseGeocode(cLat, cLng);
                        return;
                    }
                }

                clearTimeout(searchDebounceTimer);
                if (query.length < 3) {
                    resultsContainer.classList.remove('active');
                    resultsContainer.innerHTML = '';
                    return;
                }

                searchDebounceTimer = setTimeout(() => {
                    performSearch(query);
                }, 400);
            });

            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchDebounceTimer);
                    performSearch(this.value.trim(), true);
                }
            });
        }

        async function performSearch(query, selectFirst = false) {
            if (!query) return;

            // Direct coordinate check
            const coordMatch = query.match(/^([-+]?\d+(\.\d+)?)[,\s]+([-+]?\d+(\.\d+)?)$/);
            if (coordMatch) {
                const cLat = parseFloat(coordMatch[1]);
                const cLng = parseFloat(coordMatch[3]);
                if (!isNaN(cLat) && !isNaN(cLng) && cLat >= -90 && cLat <= 90 && cLng >= -180 && cLng <= 180) {
                    resultsContainer.classList.remove('active');
                    resultsContainer.innerHTML = '';
                    setLocation(cLat, cLng, `${cLat}, ${cLng}`, 17);
                    reverseGeocode(cLat, cLng);
                    return;
                }
            }

            resultsContainer.innerHTML = `<div class="p-3 text-center text-muted text-xs"><span class="spinner-border spinner-border-sm me-1.5 text-success"></span> Searching locations...</div>`;
            resultsContainer.classList.add('active');

            try {
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=6&addressdetails=1`;
                const res = await fetch(url);
                if (!res.ok) throw new Error('Search failed');
                const data = await res.json();

                if (!data || data.length === 0) {
                    resultsContainer.innerHTML = `<div class="p-3 text-center text-muted text-xs"><i class="bi bi-geo-alt me-1 text-danger"></i> No matching locations found. Try a different term.</div>`;
                    return;
                }

                if (selectFirst && data.length > 0) {
                    chooseResult(data[0]);
                    return;
                }

                resultsContainer.innerHTML = data.map((item, idx) => `
                    <div class="map-picker-result-item" data-idx="${idx}">
                        <i class="bi bi-geo-alt text-success mt-0.5 flex-shrink-0"></i>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="map-picker-result-title text-truncate">${escapeHtml(item.name || item.display_name.split(',')[0])}</div>
                            <div class="map-picker-result-sub text-truncate">${escapeHtml(item.display_name)}</div>
                        </div>
                    </div>
                `).join('');

                resultsContainer.querySelectorAll('.map-picker-result-item').forEach((el, idx) => {
                    el.addEventListener('click', () => {
                        chooseResult(data[idx]);
                    });
                });
            } catch (err) {
                resultsContainer.innerHTML = `<div class="p-3 text-center text-danger text-xs"><i class="bi bi-exclamation-triangle me-1"></i> Unable to connect to location search service.</div>`;
            }
        }

        function chooseResult(item) {
            resultsContainer.classList.remove('active');
            resultsContainer.innerHTML = '';
            if (searchInput) searchInput.value = item.display_name;

            const displayName = item.display_name;
            setLocation(item.lat, item.lon, displayName, 17);

            const addrInput = document.getElementById('address');
            if (addrInput) addrInput.value = displayName;
        }

        async function reverseGeocode(lat, lon) {
            const pinnedText = document.getElementById('pinnedAddressText');
            if (pinnedText) pinnedText.textContent = 'Resolving address...';

            try {
                const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`;
                const res = await fetch(url);
                if (!res.ok) throw new Error();
                const data = await res.json();
                if (data && data.display_name) {
                    if (pinnedText) pinnedText.textContent = data.display_name;
                    const addrInput = document.getElementById('address');
                    if (addrInput && !addrInput.value.trim()) {
                        addrInput.value = data.display_name;
                    }
                    const modalAddr = document.getElementById('modalAddressText');
                    if (modalAddr) modalAddr.textContent = data.display_name;
                } else {
                    if (pinnedText) pinnedText.textContent = `${lat}, ${lon}`;
                }
            } catch (e) {
                if (pinnedText) pinnedText.textContent = `${lat}, ${lon}`;
            }
        }

        window.clearMapSearch = function () {
            if (searchInput) searchInput.value = '';
            if (clearBtn) clearBtn.classList.add('d-none');
            if (resultsContainer) {
                resultsContainer.classList.remove('active');
                resultsContainer.innerHTML = '';
            }
        };

        window.searchAddressFromInput = function () {
            const addr = document.getElementById('address').value.trim();
            if (!addr) {
                alert('Please enter an address first to search on the map.');
                return;
            }
            if (searchInput) searchInput.value = addr;
            performSearch(addr, true);
        };

        window.useCurrentLocation = function () {
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser.');
                return;
            }
            const pinnedText = document.getElementById('pinnedAddressText');
            if (pinnedText) pinnedText.textContent = 'Locating GPS position...';

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    setLocation(lat, lng, null, 17);
                    reverseGeocode(lat, lng);
                },
                (err) => {
                    alert('Unable to retrieve your current location: ' + err.message);
                    if (pinnedText) pinnedText.textContent = 'GPS location unavailable';
                },
                { enableHighAccuracy: true, timeout: 8000 }
            );
        };

        // ─── Direct Interactive Plot Boundary Drawing Engine ───

        window.setPlotDrawMode = function (mode) {
            currentDrawMode = mode;
            cancelTempDrawing();

            const panBtn = document.getElementById('drawModePanBtn');
            const polyBtn = document.getElementById('drawModePolygonBtn');
            const boxBtn = document.getElementById('drawModeBoxBtn');
            const helper = document.getElementById('mapDrawingHelper');
            const helperText = document.getElementById('mapDrawingHelperText');
            const finishBtn = document.getElementById('btnFinishPolygon');

            [panBtn, polyBtn, boxBtn].forEach(btn => {
                if (btn) {
                    btn.classList.remove('active', 'btn-success', 'text-white', 'text-dark');
                    btn.classList.add('text-secondary');
                }
            });

            if (mode === 'pan') {
                if (panBtn) {
                    panBtn.classList.add('active', 'text-dark');
                    panBtn.classList.remove('text-secondary');
                }
                map.getContainer().style.cursor = '';
                if (helper) helper.classList.add('d-none');
            } else if (mode === 'polygon') {
                if (polyBtn) {
                    polyBtn.classList.add('active', 'btn-success', 'text-white');
                    polyBtn.classList.remove('text-secondary');
                }
                map.getContainer().style.cursor = 'crosshair';
                if (helper) helper.classList.remove('d-none');
                if (helperText) helperText.textContent = 'Click on map to place corner 1';
                if (finishBtn) finishBtn.classList.add('d-none');
            } else if (mode === 'box') {
                if (boxBtn) {
                    boxBtn.classList.add('active', 'btn-success', 'text-white');
                    boxBtn.classList.remove('text-secondary');
                }
                map.getContainer().style.cursor = 'crosshair';
                if (helper) helper.classList.remove('d-none');
                if (helperText) helperText.textContent = 'Click on map to anchor corner 1 of the box';
                if (finishBtn) finishBtn.classList.add('d-none');
            }
        };

        function cancelTempDrawing() {
            if (drawingTempLine) { map.removeLayer(drawingTempLine); drawingTempLine = null; }
            if (drawingGuideLine) { map.removeLayer(drawingGuideLine); drawingGuideLine = null; }
            if (drawingStartMarker) { map.removeLayer(drawingStartMarker); drawingStartMarker = null; }
            if (boxPreviewLayer) { map.removeLayer(boxPreviewLayer); boxPreviewLayer = null; }
            boxAnchor = null;
            if (!drawnPolygonLayer) {
                drawnPoints = [];
            }
        }

        function handlePolygonClick(e) {
            drawnPoints.push(e.latlng);
            const count = drawnPoints.length;
            const helperText = document.getElementById('mapDrawingHelperText');
            const finishBtn = document.getElementById('btnFinishPolygon');

            if (count === 1) {
                drawingStartMarker = L.circleMarker(e.latlng, {
                    radius: 7,
                    color: '#ffffff',
                    weight: 2,
                    fillColor: '#198754',
                    fillOpacity: 1
                }).addTo(map);

                drawingStartMarker.on('click', function (ev) {
                    L.DomEvent.stopPropagation(ev);
                    finishCurrentPolygon();
                });
                drawingStartMarker.bindTooltip('Click to close plot', { permanent: false, direction: 'top' });

                drawingTempLine = L.polyline([e.latlng], {
                    color: '#198754',
                    weight: 3
                }).addTo(map);

                if (helperText) helperText.textContent = 'Corner 1 placed. Click to place corner 2';
            } else if (count === 2) {
                if (drawingTempLine) drawingTempLine.setLatLngs(drawnPoints);
                if (helperText) helperText.textContent = 'Corner 2 placed. Click to place corner 3';
            } else {
                if (drawingTempLine) drawingTempLine.setLatLngs(drawnPoints);
                if (finishBtn) finishBtn.classList.remove('d-none');
                if (helperText) helperText.textContent = `Corner ${count} placed. Click next corner or "Finish Plot" to close`;
            }
        }

        function handlePolygonMouseMove(e) {
            if (drawnPoints.length > 0) {
                const lastPt = drawnPoints[drawnPoints.length - 1];
                if (drawingGuideLine) {
                    drawingGuideLine.setLatLngs([lastPt, e.latlng]);
                } else {
                    drawingGuideLine = L.polyline([lastPt, e.latlng], {
                        color: '#198754',
                        weight: 1.5,
                        dashArray: '4, 5'
                    }).addTo(map);
                }
            }
        }

        function handleBoxClick(e) {
            const helperText = document.getElementById('mapDrawingHelperText');
            if (!boxAnchor) {
                boxAnchor = e.latlng;
                if (helperText) helperText.textContent = 'Anchor set! Move mouse and click opposite corner to finish box';
            } else {
                const corner2 = e.latlng;
                const minLat = Math.min(boxAnchor.lat, corner2.lat);
                const maxLat = Math.max(boxAnchor.lat, corner2.lat);
                const minLng = Math.min(boxAnchor.lng, corner2.lng);
                const maxLng = Math.max(boxAnchor.lng, corner2.lng);

                drawnPoints = [
                    L.latLng(maxLat, minLng), // NW
                    L.latLng(maxLat, maxLng), // NE
                    L.latLng(minLat, maxLng), // SE
                    L.latLng(minLat, minLng)  // SW
                ];

                if (boxPreviewLayer) {
                    map.removeLayer(boxPreviewLayer);
                    boxPreviewLayer = null;
                }
                boxAnchor = null;
                finishCurrentPolygon();
            }
        }

        function handleBoxMouseMove(e) {
            if (boxAnchor) {
                const bounds = L.latLngBounds(boxAnchor, e.latlng);
                if (boxPreviewLayer) {
                    boxPreviewLayer.setBounds(bounds);
                } else {
                    boxPreviewLayer = L.rectangle(bounds, {
                        color: '#198754',
                        weight: 2,
                        dashArray: '5, 5',
                        fillColor: '#22c55e',
                        fillOpacity: 0.18
                    }).addTo(map);
                }
            }
        }

        window.finishCurrentPolygon = function () {
            if (drawnPoints.length < 3) {
                alert('Please click at least 3 points on the map to create a closed plot boundary.');
                return;
            }

            cancelTempDrawing();

            if (drawnPolygonLayer) {
                map.removeLayer(drawnPolygonLayer);
            }
            if (boundaryPoly) {
                map.removeLayer(boundaryPoly);
                boundaryPoly = null;
            }

            drawnPolygonLayer = L.polygon(drawnPoints, {
                color: '#198754',
                weight: 3,
                fillColor: '#22c55e',
                fillOpacity: 0.22
            }).addTo(map);

            createVertexMarkers();
            updateDrawnPolygonStats(true);
            renderSubdivisions();

            const clearBtn = document.getElementById('btnClearDrawnPlot');
            if (clearBtn) clearBtn.classList.remove('d-none');
            const formClearBtn = document.getElementById('btnFormClearDrawn');
            if (formClearBtn) formClearBtn.classList.remove('d-none');

            setPlotDrawMode('pan');
        };

        function createVertexMarkers() {
            vertexMarkers.forEach(m => map.removeLayer(m));
            vertexMarkers = [];

            const vertexIcon = L.divIcon({
                className: 'polygon-vertex-handle',
                html: `<div style="width:14px;height:14px;background:#16a34a;border:2.5px solid #ffffff;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,0.35);cursor:move;" title="Drag to adjust corner"></div>`,
                iconSize: [14, 14],
                iconAnchor: [7, 7]
            });

            drawnPoints.forEach((pt, idx) => {
                const vm = L.marker(pt, { icon: vertexIcon, draggable: true }).addTo(map);
                vm.on('drag', function (ev) {
                    drawnPoints[idx] = ev.target.getLatLng();
                    drawnPolygonLayer.setLatLngs(drawnPoints);
                    updateDrawnPolygonStats(false);
                    renderSubdivisions();
                });
                vm.on('dragend', function (ev) {
                    drawnPoints[idx] = ev.target.getLatLng();
                    drawnPolygonLayer.setLatLngs(drawnPoints);
                    updateDrawnPolygonStats(true);
                    renderSubdivisions();
                });
                vertexMarkers.push(vm);
            });
        }

        function calculatePolygonArea(latlngs) {
            if (!latlngs || latlngs.length < 3) return 0;
            const R = 6378137; // Earth's mean radius in meters
            let total = 0;
            const len = latlngs.length;
            for (let i = 0; i < len; i++) {
                const p1 = latlngs[i];
                const p2 = latlngs[(i + 1) % len];
                const lat1 = p1.lat * Math.PI / 180;
                const lat2 = p2.lat * Math.PI / 180;
                const lon1 = p1.lng * Math.PI / 180;
                const lon2 = p2.lng * Math.PI / 180;
                total += (lon2 - lon1) * (2 + Math.sin(lat1) + Math.sin(lat2));
            }
            return Math.abs(total * (R * R) / 2);
        }

        function calculateCentroid(latlngs) {
            let latSum = 0, lngSum = 0;
            latlngs.forEach(p => {
                latSum += p.lat;
                lngSum += p.lng;
            });
            return {
                lat: latSum / latlngs.length,
                lng: lngSum / latlngs.length
            };
        }

        function updateDrawnPolygonStats(updateAddress = false) {
            const area = calculatePolygonArea(drawnPoints);
            const roundedArea = Math.round(area * 10) / 10;

            const areaInput = document.getElementById('area');
            if (areaInput) areaInput.value = roundedArea;

            const polyInput = document.getElementById('lot_polygon');
            if (polyInput) {
                polyInput.value = JSON.stringify(drawnPoints.map(p => [p.lat, p.lng]));
            }

            const summaryEl = document.getElementById('drawnBoundarySummary');
            if (summaryEl) {
                summaryEl.textContent = `Custom Plot (${drawnPoints.length} corners, ${roundedArea.toLocaleString()} m²)`;
            }

            const centroid = calculateCentroid(drawnPoints);
            const latFixed = centroid.lat.toFixed(6);
            const lngFixed = centroid.lng.toFixed(6);

            document.getElementById('latitude').value = latFixed;
            document.getElementById('longitude').value = lngFixed;

            if (marker) {
                marker.setLatLng([centroid.lat, centroid.lng]);
            } else {
                marker = L.marker([centroid.lat, centroid.lng], { draggable: true, icon: customPinIcon }).addTo(map);
                marker.on('dragend', function (e) {
                    const pos = e.target.getLatLng();
                    setLocation(pos.lat, pos.lng);
                    reverseGeocode(pos.lat, pos.lng);
                });
            }

            const coordsBadge = document.getElementById('coordsBadge');
            if (coordsBadge) {
                coordsBadge.textContent = `${latFixed}, ${lngFixed}`;
                coordsBadge.style.display = 'inline-block';
            }

            if (updateAddress) {
                reverseGeocode(centroid.lat, centroid.lng);
            }

            updatePlotCalculation(false);
        }

        function renderSubdivisions() {
            subdivisionLayers.forEach(l => map.removeLayer(l));
            subdivisionLayers = [];

            if (!drawnPolygonLayer || drawnPoints.length < 3) return;

            const count = parseInt(document.getElementById('plot_count').value) || 4;
            if (count <= 1) return;

            const bounds = drawnPolygonLayer.getBounds();
            const cols = Math.ceil(Math.sqrt(count));
            const rows = Math.ceil(count / cols);
            const latStep = (bounds.getNorth() - bounds.getSouth()) / rows;
            const lngStep = (bounds.getEast() - bounds.getWest()) / cols;

            for (let r = 1; r < rows; r++) {
                const lat = bounds.getSouth() + r * latStep;
                const line = L.polyline([[lat, bounds.getWest()], [lat, bounds.getEast()]], {
                    color: '#15803d',
                    weight: 1.5,
                    dashArray: '4, 4',
                    opacity: 0.7
                }).addTo(map);
                subdivisionLayers.push(line);
            }
            for (let c = 1; c < cols; c++) {
                const lng = bounds.getWest() + c * lngStep;
                const line = L.polyline([[bounds.getSouth(), lng], [bounds.getNorth(), lng]], {
                    color: '#15803d',
                    weight: 1.5,
                    dashArray: '4, 4',
                    opacity: 0.7
                }).addTo(map);
                subdivisionLayers.push(line);
            }
        }

        window.clearDrawnPlot = function () {
            cancelTempDrawing();

            if (drawnPolygonLayer) {
                map.removeLayer(drawnPolygonLayer);
                drawnPolygonLayer = null;
            }
            vertexMarkers.forEach(m => map.removeLayer(m));
            vertexMarkers = [];
            subdivisionLayers.forEach(l => map.removeLayer(l));
            subdivisionLayers = [];
            drawnPoints = [];

            const polyInput = document.getElementById('lot_polygon');
            if (polyInput) polyInput.value = '';

            const summaryEl = document.getElementById('drawnBoundarySummary');
            if (summaryEl) {
                summaryEl.textContent = 'No custom boundary drawn. Use map tools above to draw.';
            }

            const clearBtn = document.getElementById('btnClearDrawnPlot');
            if (clearBtn) clearBtn.classList.add('d-none');
            const formClearBtn = document.getElementById('btnFormClearDrawn');
            if (formClearBtn) formClearBtn.classList.add('d-none');

            // Restore standard square boundary
            const latVal = parseFloat(document.getElementById('latitude').value);
            const lngVal = parseFloat(document.getElementById('longitude').value);
            if (!isNaN(latVal) && !isNaN(lngVal)) {
                updateLotBoundary(latVal, lngVal);
            }
            setPlotDrawMode('pan');
        };

        function initLayoutPlots(count) {
            const totalArea = parseFloat(document.getElementById('area').value) || 250;
            const sideMeters = Math.max(16, Math.sqrt(totalArea));

            const cols = Math.ceil(Math.sqrt(count));
            const rows = Math.ceil(count / cols);
            const plotW = Math.max(4, (sideMeters - (cols + 1) * 0.8) / cols);
            const plotH = Math.max(4, (sideMeters - (rows + 1) * 0.8) / rows);
            const singleArea = Math.round((totalArea / count) * 10) / 10;
            const permittedCrops = Array.from(selectedCrops.values()).map(c => c.label);

            activeLayoutPlots = [];
            for (let i = 0; i < count; i++) {
                const c = i % cols;
                const r = Math.floor(i / cols);

                const posX = -sideMeters / 2 + 0.8 + (c + 0.5) * plotW + c * 0.8;
                const posZ = -sideMeters / 2 + 0.8 + (r + 0.5) * plotH + r * 0.8;

                activeLayoutPlots.push({
                    id: i + 1,
                    land_id: 9999,
                    plot_number: `Plot A-${i + 1}`,
                    customX: posX,
                    customZ: posZ,
                    width: plotW,
                    height: plotH,
                    area: singleArea,
                    crop: permittedCrops.length > 0 ? permittedCrops[i % permittedCrops.length] : 'Mixed Vegetables',
                    status: 'available'
                });
            }
        }

        function buildMockLand() {
            const lat = parseFloat(document.getElementById('latitude').value) || 14.5995;
            const lng = parseFloat(document.getElementById('longitude').value) || 120.9842;
            const areaVal = parseFloat(document.getElementById('area').value) || 250;
            const titleVal = document.getElementById('title').value.trim() || 'Community Garden Plot';
            const permittedCrops = Array.from(selectedCrops.values()).map(c => c.label);

            return {
                id: 9999,
                title: titleVal,
                latitude: lat,
                longitude: lng,
                area: areaVal,
                crops: permittedCrops.length > 0 ? permittedCrops : ['Vegetables', 'Herbs'],
                polygon: drawnPoints.length >= 3 ? drawnPoints.map(p => [p.lat, p.lng]) : null
            };
        }

        // ─── Partition Plots Setup & Calculation ───
        window.updatePlotCalculation = function () {
            const areaInput = document.getElementById('area');
            const totalArea = parseFloat(areaInput.value) || 0;
            const plotCountSelect = document.getElementById('plot_count');
            const count = parseInt(plotCountSelect.value) || 4;
            const singleAreaInput = document.getElementById('singlePlotArea');
            const badge = document.getElementById('plotCalcBadge');
            const previewGrid = document.getElementById('plotGridPreviewBadges');

            const singleArea = totalArea > 0 ? (totalArea / count).toFixed(1) : '—';
            if (singleAreaInput) singleAreaInput.value = singleArea;
            if (badge) {
                badge.textContent = totalArea > 0
                    ? `${count} plot${count > 1 ? 's' : ''} (~${singleArea} m² each)`
                    : `${count} plot${count > 1 ? 's' : ''}`;
            }

            if (previewGrid) {
                let badgesHtml = '';
                for (let i = 1; i <= count; i++) {
                    badgesHtml += `
                        <span class="badge bg-white border text-dark rounded-pill px-2 py-1 text-xs d-inline-flex align-items-center gap-1 shadow-xs">
                            <i class="bi bi-grid-3x3-gap-fill text-success" style="font-size:10px;"></i>
                            <span>Plot A-${i}</span>
                            <span class="text-muted" style="font-size:9px;">(${singleArea} m²)</span>
                        </span>
                    `;
                }
                previewGrid.innerHTML = badgesHtml;
            }

            if (drawnPolygonLayer) {
                renderSubdivisions();
            } else {
                const latVal = parseFloat(document.getElementById('latitude').value);
                const lngVal = parseFloat(document.getElementById('longitude').value);
                if (!isNaN(latVal) && !isNaN(lngVal)) {
                    updateLotBoundary(latVal, lngVal);
                }
            }
        };

        // ─── 2D / 3D View Switcher in Card ───
        window.switchPickerMode = function (mode) {
            const btn2D = document.getElementById('viewMode2DBtn');
            const btn3D = document.getElementById('viewMode3DBtn');
            const container3D = document.getElementById('register3DMapContainer');
            const map2D = document.getElementById('pickerMap');
            const searchBox = document.querySelector('.map-picker-search-box');
            const statusBar = document.getElementById('mapStatusBar');
            const drawToolbar = document.getElementById('plotDrawToolbar');
            const helper = document.getElementById('mapDrawingHelper');
            const clearBtn = document.getElementById('btnClearDrawnPlot');

            if (mode === '3d') {
                const latVal = document.getElementById('latitude').value.trim();
                const lngVal = document.getElementById('longitude').value.trim();
                const lat = parseFloat(latVal);
                const lng = parseFloat(lngVal);

                if (isNaN(lat) || isNaN(lng)) {
                    alert('Please select or type location coordinates on the map before viewing in 3D.');
                    switchPickerMode('2d');
                    return;
                }

                btn2D.classList.remove('active', 'text-success', 'fw-bold');
                btn2D.classList.add('text-muted');
                btn3D.classList.add('active', 'text-primary', 'fw-bold');
                btn3D.classList.remove('text-muted');

                if (drawToolbar) drawToolbar.classList.add('d-none');
                if (helper) helper.classList.add('d-none');
                if (clearBtn) clearBtn.classList.add('d-none');
                if (searchBox) searchBox.classList.add('d-none');
                if (statusBar) statusBar.classList.add('d-none');
                if (map2D) map2D.style.display = 'none';
                if (container3D) container3D.classList.add('active');

                const mockLand = buildMockLand();
                if (activeLayoutPlots.length === 0) {
                    initLayoutPlots(parseInt(document.getElementById('plot_count').value) || 4);
                }

                if (!register3dEngine) {
                    register3dEngine = new Map3DEngine({
                        container: container3D,
                        center: { lat: lat, lng: lng },
                        landsData: [mockLand],
                        plotsData: activeLayoutPlots,
                        enablePlotDragging: true,
                        basePath: '<?php echo $base_path; ?>',
                        onExit3D: function () {
                            switchPickerMode('2d');
                        },
                        onPlotRepositioned: function (plot, newX, newZ) {
                            const target = activeLayoutPlots.find(p => p.id === plot.id);
                            if (target) {
                                target.customX = newX;
                                target.customZ = newZ;
                            }
                        }
                    });
                } else {
                    register3dEngine.plotsData = activeLayoutPlots;
                }

                register3dEngine.generateForPlot(mockLand, null);
            } else {
                btn3D.classList.remove('active', 'text-primary', 'fw-bold');
                btn3D.classList.add('text-muted');
                btn2D.classList.add('active', 'text-success', 'fw-bold');
                btn2D.classList.remove('text-muted');

                if (drawToolbar) drawToolbar.classList.remove('d-none');
                if (clearBtn && drawnPolygonLayer) clearBtn.classList.remove('d-none');
                if (searchBox) searchBox.classList.remove('d-none');
                if (statusBar) statusBar.classList.remove('d-none');
                if (container3D) container3D.classList.remove('active');
                if (map2D) {
                    map2D.style.display = 'block';
                    map.invalidateSize();
                }
            }
        };

        // Initialize plot calculation
        updatePlotCalculation();

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            if (resultsContainer && !resultsContainer.contains(e.target) && e.target !== searchInput) {
                resultsContainer.classList.remove('active');
            }
        });

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
        }

        if (window.lucide) {
            lucide.createIcons();
        }
    });
</script>

<script src="<?php echo $base_path; ?>assets/js/map3d_engine.js"></script>

<?php include '../includes/footer.php'; ?>